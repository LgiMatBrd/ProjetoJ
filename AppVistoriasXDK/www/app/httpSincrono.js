(function() {
  'use strict';
function HttpSincrono($http, $q, $localStorage) {
    var _this = this;
    
    var i = 0;
    var x = 0;
    var last = -1;
    var dbs = [
            $localStorage.clientes,
            $localStorage.vistorias,
            $localStorage.itensVistoriados
        ];
        
    function close()
    {
        i = 0;
        last = -1;
        x = 0;
    }
    
    function enviar(Url, token, scope, sendTimestamp, incrementoProg, sortable)
    {
        if (last == i)
            i++;
        last = i;
        var row;
        var recent = new Recent();
        var deferred = $q.defer();
        
        var db = sortable[i][2];
        var id = sortable[i][0];
        var dbName = '';
        
        
        switch (db)
        {
            case 0:
                dbName = 'clientes';
                break;
            case 1:
                dbName = 'vistorias';
                break;
            case 2:
                dbName = 'itensVistoriados';
                break;
            default:
                scope.h2 = 'Erro!';
                scope.msg = 'Não foi possível identificar a DB!';
                deferred.reject();
                return deferred.promise;
                break;
        }
        // Atualiza a row para corresponder à DB externa
        try
        {
            row = dbClone(dbs[db].db[id]);
            
            switch (db)
            {
                case 1:
                    row.id_cliente = dbs[0].db[row.id_cliente].idext;
                    if (row.id_cliente == 0)
                        throw new Error("Problema de dependência da DB. Ordem de sincronização falhou!\n\n"+row);
                    break;
                case 2:
                    row.id_vistoria = dbs[1].db[row.id_vistoria].idext;
                    if (row.id_vistoria == 0)
                        throw new Error("Problema de dependência da DB. Ordem de sincronização falhou!\n\n"+row);
                    break;
            }
        }
        catch (err)
        {
            scope.h2 = 'Erro!';
            scope.msg = err.message;
            deferred.reject();
            return deferred.promise;
        }
      
        try
        {
            $http({
                    method: 'POST',
                    url: Url,
                    data: {
                        token: token,
                        func: 'sendData',
                        dbName: dbName,
                        row: row
                    }
                })
            .then(function (response) {
                if (response.data.status == "ok" && response.data.idext)
                {
                    sendTimestamp[db] = row.modificado;
                    dbs[db].sendTimestamp = row.modificado;
                    dbs[db].db[id].idext = response.data.idext;
                    
                    scope.porcentagem += incrementoProg;
                    scope.msg = "Enviando registros (" + (scope.porcentagem | 0) + "%)";
                    deferred.resolve();
                } else {
                    if (response.data.msg != undefined)
                        throw new Error(response.data.msg);
                    else
                        throw new Error(response.data);
                }
                            
                
            }, function (response){
                deferred.reject(response);
                return deferred.promise;
            });
        }
        catch (err)
        {
            scope.h2 = 'Erro!';
            scope.msg = err.message;
            deferred.reject();
            return deferred.promise;
        }
        return deferred.promise;
    }
    
    function deletar(Url, token, scope, incrementoProg)
    {
        while (dbs[x].remoteDelete.length == 0 && x < dbs.length)
        {
            x++;
            i = 0;
            last = -1;
        }
        if (last == i)
            i++;
        last = i;
        
        var deferred = $q.defer();
        var db = x;
        var dbName = '';
        
        switch (db)
        {
            case 0:
                dbName = 'clientes';
                break;
            case 1:
                dbName = 'vistorias';
                break;
            case 2:
                dbName = 'itensVistoriados';
                break;
            default:
                scope.h2 = 'Erro!';
                scope.msg = 'Não foi possível identificar a DB!';
                deferred.reject();
                return deferred.promise;
                break;
        }
        
        try
        {
            $http({
                method: 'POST',
                url: Url,
                data: {
                    token: token,
                    func: 'deleteData',
                    dbName: dbName,
                    row: dbs[x].remoteDelete[0]
                }
            })
            .then(function (response) {
                if (response.data.status == "ok")
                {
                    dbs[x].remoteDelete.shift();
                    scope.porcentagem += incrementoProg;
                    scope.msg = "Atualizando registros (" + (scope.porcentagem | 0) + "%)";
                    deferred.resolve();
                }
                else
                {
                    if (response.data.msg != undefined)
                        throw new Error(response.data.msg);
                    else
                        throw new Error(response.data);
                }
            }, function (response) {
                deferred.reject();
                return deferred.promise;
            })
        }
        catch (err)
        {
            scope.h2 = 'Erro!';
            scope.msg = err.message;
            deferred.reject();
            return deferred.promise;
        }
        return deferred.promise;
    }
    
    function receber(Url, token)
    {
        if (last == i)
            i++;
        last = i;
        
        var deferred = $q.defer();
        var dbName = '';
        
        switch (i)
        {
            case 0:
                dbName = 'clientes';
                break;
            case 1:
                dbName = 'vistorias';
                break;
            case 2:
                dbName = 'itensVistoriados';
                break;
            default:
                scope.h2 = 'Erro!';
                scope.msg = 'Não foi possível identificar a DB!';
                deferred.reject();
                return deferred.promise;
                break;
        }
        
        try
        {
            $http({
                method: 'POST',
                url: Url,
                data: {
                    token: token,
                    func: 'getData',
                    dbName: dbName
                }
            })
            .then(function (response) {
                if (response.data.status == "ok" && response.data.dbName == dbName)
                {
                    dbs[i].db = response.data.db;
                    deferred.resolve();
                }
                else
                {
                    if (response.data.msg != undefined)
                        throw new Error(response.data.msg);
                    else
                        throw new Error(response.data);
                }
            }, function (response) {
                deferred.reject();
                return deferred.promise;
            })
        }
        catch (err)
        {
            scope.h2 = 'Erro!';
            scope.msg = err.message;
            deferred.reject();
            return deferred.promise;
        }
        return deferred.promise;
    }
    
    
    _this.enviar = enviar;
    //_this.next = next;
    _this.close = close;
    _this.receber = receber;
    _this.deletar = deletar;
}

HttpSincrono.$inject = ['$http', '$q', '$localStorage'];
    
/*function ChainedPromiseCtrl(httpSincrono) {
    var _this = this;
    
    var items = [{
      name: 'Item-1'
    }, {
      name: 'Item-2'
    }, {
      name: 'Item-3'
    }, {
      name: 'Item-4'
    }];

    function callServiceForEachItem() {
      var promise;

      angular.forEach(items, function(item) {
        if (!promise) {
          promise = fakeService.doSomething(item);
        } else {
          promise = promise.then(function() {

            return fakeService.doSomething(item);
          });
        }
      });
    }

    _this.items = items;
    _this.callServiceForEachItem = callServiceForEachItem;
}
    
ChainedPromiseCtrl.$inject = ['fakeService'];*/
angular.module('seyconelApp')
    .service('httpSincrono', HttpSincrono);
}());