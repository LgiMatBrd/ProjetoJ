/*function onAppReady() {
    if( navigator.splashscreen && navigator.splashscreen.hide ) {   // Cordova API detected
        navigator.splashscreen.hide() ;
    }
}
document.addEventListener("app.Ready", onAppReady, false);
*/
//Define an angular module for our app 
var app = angular.module('seyconelApp', ['ngRoute','ngStorage','ngMaterial','ngMessages', 'material.svgAssetsCache', 'ngCordova']);


app.config(function($routeProvider,$mdIconProvider) {
    $routeProvider
    .when("/login", {
        templateUrl : "paginas/login.html",
		controller  : 'loginController'
    })
    .when("/", {
        templateUrl : "paginas/clientes.html",
		controller  : 'homeController'
    })
    .when("/vistorias/:id?", { 
        templateUrl : "paginas/vistorias.html",
		controller  : 'vistoriasController'
    })
    .when("/vistoria/:id?", {
        templateUrl : "paginas/vistoria.html",
		controller  : 'vistoriaController'
    })
    .when("/sincronizar", {
        templateUrl : "paginas/sincronizar.html",
        controller  : 'sincronizarController'
    })
    .otherwise({
       redirectTo: '/'
    }); 
    
    $mdIconProvider
        .iconSet('social', 'img/icons/sets/social-icons.svg', 24)
        .iconSet('call', 'img/icons/sets/communication-icons.svg', 24)
        .iconSet('device', 'img/icons/sets/device-icons.svg', 24)
        .iconSet('communication', 'img/icons/sets/communication-icons.svg', 24)
        .icon('synced', 'icons/synced.svg')
        .defaultIconSet('img/icons/sets/core-icons.svg', 24);
});




app.run(function($localStorage) {
    
    if (typeof $localStorage.clientes === 'undefined' || typeof $localStorage.clientes.db === 'undefined' || $localStorage.clientes.version !== 'v0.4')
    {
        $localStorage.clientes = {
            nextID: 0,
            version: 'v0.4',
            sendTimestamp: 0, // Armazena a timestamp UTC de modificação do último registro enviado
            recvTimestamp: 0, // Armazena a timestamp UTC de modificação do último registro recebido
            remoteDelete: [], // Armazena as IDs externas que devem ser apagadas
            db: {}
        }; 
    }
    if (typeof $localStorage.vistorias === 'undefined' || typeof $localStorage.vistorias.db === 'undefined' || $localStorage.vistorias.version !== 'v0.4')
    {
        $localStorage.vistorias = {
            nextID: 0,
            version: 'v0.4',
            sendTimestamp: 0,
            recvTimestamp: 0,
            remoteDelete: [],
            db: {}
        }; 
    }
    if (typeof $localStorage.itensVistoriados === 'undefined' || typeof $localStorage.itensVistoriados.db === 'undefined' || $localStorage.itensVistoriados.version !== 'v0.3')
    {
        $localStorage.itensVistoriados = {
            nextID: 0,
            version: 'v0.3',
            sendTimestamp: 0,
            recvTimestamp: 0,
            remoteDelete: [],
            db: {}
        }; 
    }
});

 /*app.directive('camera', function() {
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, elm, attrs, ctrl) {
            elm.on('click', function() {
                navigator.camera.getPicture(
                    function(imageURI) {
                        console.log(imageURI);
                        console.log(moveFile(imageURI));
                        //var imgView = dsad;
                        scope.$apply(function() {
                            //ctrl.$setViewValue(imgView);
                        });
                    },
                    function(err) {
                        ctrl.$setValidity('error', false);
                    }, {
                        quality: 50, 
                        destinationType: Camera.DestinationType.FILE_URI
                    });
            });
        }
    }; 
})*/

app.controller('loginController', function($scope, $http, $localStorage, $location, $mdDialog) {
    $scope.user = {
        email: '',
    };
    
    $scope.user.submit = function(user)
    {
        var p = hex_sha512(user.password);
        $http({
            method: 'POST',
            url: 'http://app.seyconel.com.br/apps/makelogin.php',
            data: {
                makelogin: 'true',
                username: user.username,
                password: '',
                p: p
            }
        })
        .then(function successCallback(response)
        {
            if (response.data.status == "ok")
            {
                if (response.data.logged === 'in')
                    $location.path('/sincronizar').replace();
                else
                    $scope.msg = response.data.msg;
            }
            else if (response.data.status == "error")
            {
                $scope.msg = "Não foi possível logar! "+response.data.msg;
            }
            
        }, function errorCallback(response){
            $scope.msg = "Ocorreu um problema ao efetuar login: "+response.statusText;
        });
    }
});

app.controller('homeController', function($scope, $http, $localStorage, $location, $mdDialog) {
    /*delete $localStorage.vistoria;
    delete $localStorage.vistorias;
    delete $localStorage.clientes;
    delete $localStorage.itensVistoriados;*/ 
    
    $scope.showAdvanced = function(ev) {
        $mdDialog.show({
            controller: DialogController,
            templateUrl: 'formulario-add-cliente.tmpl.html',
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose:true,
            fullscreen: $scope.customFullscreen
        });
    }; 

    function DialogController($scope, $mdDialog) {
        $scope.addCliente = function($valor) {
            
            id = $localStorage.clientes.nextID;

            cliente = new Cliente(); // Definições do objeto estão no arquivo dbObj.js
            cliente.id = id;
            cliente.nome = $valor;
            cliente.data_criacao = timestampUTC();
            cliente.modificado = cliente.data_criacao;
            $localStorage.clientes.db[id] = cliente;

            id = id + 1;
            $localStorage.clientes.nextID = id;
            $mdDialog.hide();

        };
        
        $scope.hide = function() {
            $mdDialog.hide();
        };

        $scope.salvar = function() {
            $scope.resultadoAdicionar =  'fwewefwef';
        };

        $scope.cancel = function() {
            $mdDialog.cancel();
        };

        $scope.answer = function(answer) {
            $mdDialog.hide(answer);
        };
    }

    $scope.lerClientes = function ()
    {
        return $localStorage.clientes.db;
    }; 

    // Ver vistorias
    $scope.verVistorias = function (id) {
        $location.path('/vistorias/' + id);
    };

    $scope.deletarCliente = function ($id)
    {
        // Verifica se a row local já foi sincronizada alguma vez
        if ($localStorage.clientes.db[$id].idext)
        {
            // Já foi sincronizada, marca a row externa para ser apagada!
            var deleteSync = {
                        idext: $localStorage.clientes.db[$id].idext
                    };
            if ($localStorage.clientes.db[$id].dados)
                deleteSync.nome = $localStorage.clientes.db[$id].dados.nome;
            $localStorage.clientes.remoteDelete.push(deleteSync);
        }
        // Deleta todas as rows dependentes desta
        angular.forEach($localStorage.vistorias.db, function (valor, key)
        {
            if (valor['id_cliente'] == $id)
            {
                angular.forEach($localStorage.itensVistoriados.db, function (valor2, key2)
                {
                    if (valor2['id_vistoria'] == valor['id'])
                    {
                        if (valor2.idext)
                        {
                            // Já foi sincronizada, marca a row externa para ser apagada!
                            var deleteSync = {
                                        idext: valor2.idext
                                    };
                            if (valor2.dados && valor2.dados.nome)
                                deleteSync.nome = valor2.dados.nome;
                            $localStorage.itensVistoriados.remoteDelete.push(deleteSync);
                        }
                        delete $localStorage.itensVistoriados.db[key2];
                    }
                });
                if (valor.idext)
                {
                    var deleteSync = {
                        idext: valor.idext
                    }
                    $localStorage.vistorias.remoteDelete.push(deleteSync);
                }
                delete $localStorage.vistorias.db[key];
            }
        });
            
        delete $localStorage.clientes.db[$id];
    };

    $scope.verDadosCliente = function(person, event) {
        $mdDialog.show(
          $mdDialog.alert()
            .title(person.name)
            .textContent('Aqui ficarão algumas estátisticas do cliente.')
            .ok('Fechar')
            .targetEvent(event)
        );
    };
    
});




app.controller('vistoriasController', function($scope, $routeParams, $http, $localStorage, $location, $mdDialog) {

	$scope.vistorias = {};
    
    // id do cliente
    $scope.id = $routeParams.id;
    $scope.nome = $localStorage.clientes.db[$scope.id].nome;
    $scope.id_dono = $routeParams.id;
    
    // chama a função para preencher a variável que armazena as vistorias desse cliente
    populaVistorias($scope.id);
    
    // botão de voltar
    $scope.goBack = function() { 
        window.history.back();
    };
    
    // ver vistoria
    $scope.verVistoria = function (id) {
        $location.path('/vistoria/' + id);
    };
    
    // adicionar vistoria
    $scope.addVistoria = function($valor,$id_dono)
    {
        nextId = $localStorage.vistorias.nextID;
        
        vistoria = new Vistoria(); 
        vistoria.id = nextId;
        vistoria.id_cliente = $id_dono;
        vistoria.nome = $valor;
        vistoria.data_criacao = timestampUTC();
        vistoria.modificado = vistoria.data_criacao;
        $localStorage.vistorias.db[nextId] = vistoria;
        
        nextId = nextId + 1;
        $localStorage.vistorias.nextID = nextId;
        // Repopula a variavel de escopo $scope.vistorias
        //populaVistorias($id_dono);
        $location.path('/vistorias/' + $id_dono);
    };

    // ler vistorias
    // popula a variavel $scope.vistorias
    function populaVistorias($id_dono)
    {  
        var db = $localStorage.vistorias.db;
        $scope.vistorias = {}; 
        
        for (var vist_key in db)
        {
            if (db.hasOwnProperty(vist_key))
            {
                if (db[vist_key].id_cliente == $id_dono)
                    $scope.vistorias[vist_key] = Object.create(db[vist_key]);
            }
        }
    }
   
    // deletar vistoria 
    $scope.deletarVistoria = function ($id)
    {
        // Verifica se a row local já foi sincronizada alguma vez
        if ($localStorage.vistorias.db[$id].idext)
        {
            // Já foi sincronizada, marca a row externa para ser apagada!
            var deleteSync = {
                        idext: $localStorage.vistorias.db[$id].idext
                    };
            $localStorage.vistorias.remoteDelete.push(deleteSync);
        }
        
        // Remove todas as rows dependentes desta
        angular.forEach($localStorage.itensVistoriados, function (valor, key)
        {
            if (valor['id_vistoria'] == $id)
            {
                if (valor.idext)
                {
                    // Já foi sincronizada, marca a row externa para ser apagada!
                    var deleteSync = {
                                idext: valor.idext
                            };
                    if (valor.dados && valor.dados.nome)
                        deleteSync.nome = valor.dados.nome;
                    $localStorage.itensVistoriados.remoteDelete.push(deleteSync);
                }
                delete $localStorage.itensVistoriados.db[key];
            }
        });
        
        delete $localStorage.vistorias.db[$id];
        // Repopula a variavel de escopo $scope.vistorias
        populaVistorias($scope.id_dono);
    };
    
    $scope.showAdvanced = function(ev) {
        $mdDialog.show({
            controller: DialogController,
            templateUrl: 'formulario-add-vistoria.tmpl.html',
            id_dono: $scope.id_dono,
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose:true,
            fullscreen: $scope.customFullscreen
        }); 
    }; 

    function DialogController($scope, $mdDialog, id_dono) {
        
        $scope.addVistoria = function($valor) {
            
            id = $localStorage.vistorias.nextID;

            vistoria = new Vistoria(); 
            vistoria.id = id;
            vistoria.id_cliente = id_dono;
            vistoria.nome = $valor;
            vistoria.data_criacao = timestampUTC(); 
            vistoria.modificado = vistoria.data_criacao;
            $localStorage.vistorias.db[id] = vistoria;

            id = id + 1;
            $localStorage.vistorias.nextID = id;
            // Repopula a variavel de escopo $scope.vistorias
            populaVistorias(id_dono);
            $mdDialog.hide();

        };
        
        $scope.cancel = function() {
            $mdDialog.cancel();
        };
    }

    $scope.verDadosVistorias = function(person, event) {
        $mdDialog.show(
          $mdDialog.alert()
            .title(person.name)
            .textContent('Aqui ficarão algumas estatísticas do cliente.')
            .ok('Fechar')
            .targetEvent(event)
        );
    };
    
});




app.controller('vistoriaController', function($scope, $routeParams, $http, $localStorage, $filter, $mdDialog) {    
    $scope.id_dono = $routeParams.id;
	$scope.id = $routeParams.id;
	$scope.nomeVistoria = $localStorage.vistorias.db[$scope.id].nome;
	$scope.idVistoria = $localStorage.vistorias.db[$scope.id].id;
	$scope.idDonoVistoria = $localStorage.vistorias.db[$scope.id].id_cliente;
    
	//$scope.idDono = $localStorage.vistorias.db[$scope.id].id_dono;
	//$scope.nomeCliente = $localStorage.clientes.db[$scope.idDono].nome;

	$scope.nomeClienteDono = $localStorage.clientes.db[$scope.idDonoVistoria].nome;
	$scope.NextID = $localStorage.itensVistoriados.nextID;
    
    // chama a função para preencher a variável que armazena as vistorias desse cliente
	$scope.itensVistoriados = {};
    populaVistorias($scope.id_dono);

    // Foto principal vistoria
    //$scope.fotoPrincipal = $localStorage.itensVistoriados.db;
    console.log($localStorage);
    
    $scope.showAdvanced = function(ev,id_click) {
        $mdDialog
            .show({
            controller: DialogController,
            templateUrl: 'formulario-vistoria.tmpl.html',
            id_dono: $scope.id_dono,
            id_click: id_click,
            locals: {
                tiposVistorias: $scope.tiposVistorias
            },
            bindToController: true,
            onRemoving: function() { populaVistorias($scope.id_dono); },
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose:true,
            fullscreen: $scope.customFullscreen
            });
    };

    function DialogController($scope, $mdDialog, id_dono, tiposVistorias, id_click, $cordovaCamera) {
        $scope.myPictures = [];
        // Verifica se o usuário quer editar o item.
        if (id_click > -1)
        {
            $scope.item = {};
            $scope.item = $localStorage.itensVistoriados.db[id_click].dados;
            $scope.myPictures = $localStorage.itensVistoriados.db[id_click].fotos64;
            
            // Pega os valores booleanos que estão em string e coverte novamente.
            angular.forEach($scope.item, function(value, key) {
                //console.log(key + ': ' + value);
                if (value == "true") {
                    $scope.item[key] = true;
                }
            });
            
        } else {
            console.log('Nenhum item para ser editado, abrindo tela de adiconar novo item...');
        }
        
        $scope.$watch('myPicture', function(value) {
            if (value) {
                $scope.myPictures.push(value);
            }
        }, true);
        
        $scope.takePicture = function()
        {
            
            var options = {
              quality: 50,
              destinationType: Camera.DestinationType.DATA_URL,
              sourceType: Camera.PictureSourceType.CAMERA,
              allowEdit: false,
              encodingType: Camera.EncodingType.JPEG,
              mediaType: Camera.MediaType.PICTURE,
              targetWidth: 1024,
              targetHeight: 768,
              popoverOptions: CameraPopoverOptions,
              saveToPhotoAlbum: false,
              correctOrientation: false
            };
            $cordovaCamera.getPicture(options).then(function(data) {
                 $scope.myPicture = data;

            }, function(err) {
                 console.log(err);
            });
        
        }

        $scope.tiposVistorias = tiposVistorias;
        $scope.addItem = function(itemForm) {
            
            // Verifica se os Form é de edição ou de adição de novo Item
            if (id_click > -1) {
                // Edita o item
                id = id_click;
                $localStorage.itensVistoriados.db[id].dados = $scope.item;
                $localStorage.itensVistoriados.db[id].fotos64 = $scope.myPictures;
                $localStorage.itensVistoriados.db[id].modificado = timestampUTC();
                
                $mdDialog.hide();
            } else {
                id = $localStorage.itensVistoriados.nextID;

                /* OBJETO
                this.id = 0;
                this.id_dono = '';
                this.data_criacao = '';
                this.dados = '';
                */
                item = new itemVitoriado(); 
                item.id = id;
                item.id_vistoria = id_dono;
                item.data_criacao = timestampUTC();
                item.modificado = item.data_criacao;
                
                item.fotos64 = $scope.myPictures;
                item.dados = $scope.item;

                $localStorage.itensVistoriados.db[id] = item;

                id = id + 1; 
                $localStorage.itensVistoriados.nextID = id;
                
                $mdDialog.hide();
            }

        };
        
        $scope.capturePhotoWithFile = function ()
        {
            navigator.camera.getPicture(function (imageData) {
                imgView = imageData;
            }, function (msg) {
                console.log(msg);
            }, { quality: 50, destinationType: Camera.DestinationType.DATA_URL });
        }
        
        $scope.cancel = function() {
            $mdDialog.cancel();
        };
    }

    // ler vistorias
    // popula a variavel $scope.vistorias
    function populaVistorias($id_dono)
    {  
        var db = $localStorage.itensVistoriados.db;
        $scope.itensVistoriados = {};
        
        for (var vist_key in db)
        {
            if (db.hasOwnProperty(vist_key))
            {
                if (db[vist_key].id_vistoria == $id_dono)
                    $scope.itensVistoriados[vist_key] = Object.create(db[vist_key]);
            }
        }
    }
        
    // botão de voltar
    $scope.goBack = function() {
        window.history.back();
    };
    
    // ver vistoria
    $scope.verVistoria = function (id) {
        $location.path('/vistoria/' + id);
    };

    // ler vistorias
    $scope.lerVistorias = function ($id_dono) 
    {
        var resultado = {};
        var db = $localStorage.vistorias.db; 
        
        for (var vist_key in db)
        {
            if (db.hasOwnProperty(vist_key))
            {
                if (db[vist_key].id_cliente == $id_dono)
                    resultado[vist_key] = Object.create(db[vist_key]);
            }
        }
        
        return resultado;
    };
   
    // deletar vistoria
    $scope.deletarVistoria = function ($id)
    {
        // Verifica se a row local já foi sincronizada alguma vez
        if ($localStorage.itensVistoriados.db[$id].idext)
        {
            // Já foi sincronizada, marca a row externa para ser apagada!
            var deleteSync = {
                        idext: $localStorage.itensVistoriados.db[$id].idext
                    };
            if ($localStorage.itensVistoriados.db[$id].dados && $localStorage.itensVistoriados.db[$id].dados.nome)
                deleteSync.nome = $localStorage.itensVistoriados.db[$id].dados.nome;
            $localStorage.itensVistoriados.remoteDelete.push(deleteSync);
        }
        
        delete $localStorage.itensVistoriados.db[$id]; 
        populaVistorias($scope.id);
    };
    
    $scope.tiposVistorias = {
        'linc': 'Linga de corrente (NR-11/NBR 15516 1 e 2/NBR ISO 3076/NBR ISO 1834)',
        'ectu': 'Eslingas, cintas planas e tubulares. (NR-11 NBR 15637 1 e 2)',
        'aces': 'Acessórios  (Ganchos, Cadeados, olhais, Manilhas) (NR-11/NBR 13545/NBR 16798)',
        'gael': 'Garras de elevação  (NR-11)',
        'lema': 'Levantador magnético  (NR 11)',
        'dies': 'Dispositivos Especiais: (NR 11)',
        'lila': 'Lingas e Laços de cabos de aço'
    };
    
});




app.controller('sincronizarController', function($scope, $http, $localStorage, $timeout, $interval, $location, httpSincrono) {
    
    var UrlSync = 'http://app.seyconel.com.br/apps/dbsync.php'; // URL do arquivo PHP de sincronização
    var UrlRel = 'http://app.seyconel.com.br/apps/reportgenerator.php'; // URL do arquivo PHP de emissão de relatórios
    var token = 'asda';
    var httpBusy = 0;
    var dbs = [
            $localStorage.clientes,
            $localStorage.vistorias,
            $localStorage.itensVistoriados
        ];
    $scope.barraProgresso = false;
    $scope.porcentagem = 0;
    $scope.h2 = '';
    $scope.msg = '';
    $scope.btnRelatorios = true;
    $scope.btn = 'Emitir relatórios';
    
    function checkConnection()
    {
        var networkState = navigator.connection.type;

        if (networkState !== Connection.NONE)
        {
            checkServer();
        }
        else
        {
            $scope.h2 = "Sem conexão!";
            $scope.msg = "Conecte-se à internet.";
            $timeout(checkConnection, 1000);
        }
    }
    
    function checkServer()
    {
        $scope.h2 = "Conectando...";
        $http({
            method: 'POST',
            url: UrlSync,
            data: {
                token: token,
                func: 'checkServer'
            }
        })
        .then(function successCallback(response){
            if (response.data.status == "ok")
            {
                if (response.data.logged != "in")
                    $location.path('/login').replace();
                else
                {
                    $scope.barraProgresso = true;
                    $scope.h2 = "Aguarde!";
                    sendData();
                }
            }
            else if (response.data.status == "error")
            {
                console.log(response);
                $scope.h2 = "Falha no script do servidor!";
                $scope.msg = "";
            }
            
        }, function errorCallback(response){
            $scope.h2 = "Não foi possível contactar o servidor!";
            $scope.msg = "Verifique se você possui uma conexão estável com a internet e tente novamente.\nErro: "+response.statusText;
        });
    }

    function sendData()
    {
        $scope.porcentagem = 0;
        
        /*var dbs = [
            $localStorage.clientes,
            $localStorage.vistorias,
            $localStorage.itensVistoriados
        ];*/
        
        var novosRegistros = 0;
        $scope.sortable = [];
        $scope.sendTimestamp = {};
        $scope.incrementoProg = 0;
        var flag = 0;
        $scope.promise = 0;
        //var row;
        
        var idLocal = 0; 
        
        for (var i = 0; i < dbs.length; i++)
        {
            $scope.sendTimestamp[i] = dbs[i].sendTimestamp;
            for (var x in dbs[i].db)
            {
                if (!dbs[i].db.hasOwnProperty(x))
                    continue;
                
                if (dbs[i].db[x].modificado > $scope.sendTimestamp[i])
                {
                    novosRegistros++;
                    $scope.sortable.push([dbs[i].db[x].id, dbs[i].db[x].modificado, i]);
                }
            }
        }
        
        if (novosRegistros != 0)
        {
            $scope.h2 = "Sincronizando...";
            $scope.msg = "Enviando registros...";
        }
        else deleteData();
        
        
        // Organiza os elementos em ordem de modificação
        $scope.sortable.sort(function(a, b){
            return a[1] - b[1];
        });
        
        $scope.incrementoProg = 100/novosRegistros;
        $scope.sendTimestamp = {};
        for (var i = 0; i < $scope.sortable.length; i++)
        {
            try
            {
                if (!$scope.promise)
                    $scope.promise = httpSincrono.enviar(UrlSync, token, $scope, $scope.sendTimestamp, $scope.incrementoProg, $scope.sortable);
                else
                {
                    $scope.promise = $scope.promise.then(function () {
                        return httpSincrono.enviar(UrlSync, token, $scope, $scope.sendTimestamp, $scope.incrementoProg, $scope.sortable);
                    }, function (response) {
                        httpSincrono.close();
                        $scope.h2 = response.data.msg;
                    });
                }
            }
            catch (err)
            {
                $scope.h2 = "Falha ao sincronizar!";
                $scope.msg = "A sincronização foi interrompida.\nErro: "+err.message;
            }
            
        }
        if ($scope.promise)
        {
            $scope.promise = $scope.promise.then(function () {
                httpSincrono.close();
                deleteData();
            }, function () { httpSincrono.close(); });
        }
        
        /*for (var dbKey in sendTimestamp)
        {
            if (sendTimestamp.hasOwnProperty(dbKey)) {
                dbs[dbKey].sendTimestamp = sendTimestamp[dbKey];
            }
        }*/
        
        
    }

    function deleteData()
    {
        $scope.porcentagem = 0;
        var deleteRegistros = 0;
        $scope.incrementoProg = 0;
        $scope.promise = 0;
        
        var idLocal = 0; 
        
        for (var i = 0; i < dbs.length; i++)
        {
            deleteRegistros += dbs[i].remoteDelete.length;
        }
        if (deleteRegistros == 0)
        {
            getData();
            return;
        } else {
            $scope.msg = "Sincronizando registros apagados...";
            $scope.h2 = "Sincronizando...";
        }
        
        
        $scope.incrementoProg = 100/deleteRegistros;
        for (var x = 0; x < dbs.length; x++)
        {
            if (dbs[x].remoteDelete.length == 0)
                continue;
            for (var i = 0; i < dbs[x].remoteDelete.length; i++)
            {
                try
                {
                    if (!$scope.promise)
                        $scope.promise = httpSincrono.deletar(UrlSync, token, $scope, $scope.incrementoProg);
                    else
                    {
                        $scope.promise = $scope.promise.then(function () {
                            return httpSincrono.deletar(UrlSync, token, $scope, $scope.incrementoProg);
                        });
                    }
                }
                catch (err)
                {
                    $scope.h2 = "Falha ao sincronizar!";
                    $scope.msg = "A sincronização foi interrompida.\nErro: "+err.message;
                }
            }
            
        }
        if ($scope.promise)
        {
            $scope.promise = $scope.promise.then(function () {
                httpSincrono.close();
                getData();
            }, function () { httpSincrono.close(); });
        }
    }

    function getData()
    {
        $scope.porcentagem = 0;
        $scope.incrementoProg = 0;
        var task = 0;
        
        
        $scope.msg = "Recebendo registros atualizados...";
        $scope.h2 = "Sincronizando...";
        var incrementoProg = 100/((20*dbs.length)*30);
        task = $interval(function () {
            //$scope.porcentagem = $scope.porcentagem + $scope.incrementoProg;
            $scope.porcentagem += incrementoProg;
            //$scope.porcentagem += 0.0556;
            $scope.msg = "Recebendo registros (" + ($scope.porcentagem | 0) + "%)";
        }, 33, 20*dbs.length*30);
        
        try
        {
            $http({
                method: 'POST',
                url: UrlSync,
                data: {
                    token: token,
                    func: 'getData'
                }
            })
            .then(function (response) {
                if (response.data.status == "ok" && response.data.dbs != undefined)
                {
                    $localStorage.clientes.db = response.data.dbs.clientes;
                    $localStorage.clientes.sendTimestamp = response.data.clientes.sendTimestamp;
                    $localStorage.clientes.nextID = response.data.clientes.nextID;
                    $localStorage.vistorias.db = response.data.dbs.vistorias;
                    $localStorage.vistorias.sendTimestamp = response.data.vistorias.sendTimestamp;
                    $localStorage.vistorias.nextID = response.data.vistorias.nextID;
                    $localStorage.itensVistoriados.db = response.data.dbs.itensVistoriados;
                    $localStorage.itensVistoriados.sendTimestamp = response.data.itensVistoriados.sendTimestamp;
                    $localStorage.itensVistoriados.nextID = response.data.itensVistoriados.nextID;
                    $interval.cancel(task);
                    $scope.porcentagem = 100;
                    $scope.msg = "Recebendo registros (" + ($scope.porcentagem | 0) + "%)";
                    $scope.btnRelatorios = false;
                }
                else
                {
                    if (response.data.msg != undefined)
                        throw new Error(response.data.msg);
                    else
                        throw new Error('O servidor não retornou os dados no tempo previsto!');
                }
            });
        }
        catch (err)
        {
            $interval.cancel(task);
            $scope.h2 = 'Erro!';
            $scope.msg = err.message;
        }
        
        $scope.$on('$destroy', function() {
            $interval.cancel(task);
            httpSincrono.close();
            task = undefined;
        });
    }
    
    $scope.emitirRelatorios = function()
    {
        $scope.btnRelatorios = true;
        $scope.btn = 'Emitindo...';
        try
        {
            $http({
                method: 'GET',
                url: UrlRel,
            })
            .then(function (response) {
                if (response.data.status == "ok" && response.data.msg != undefined)
                {
                    $scope.h2 = response.data.h2;
                    $scope.msg = response.data.msg;
                    $scope.btn = 'Concluído!';
                }
                else
                {
                    if (response.data.msg != undefined)
                        throw new Error(response.data.msg);
                    else
                        throw new Error('O servidor não retornou informação legível!');
                }
            });
        }
        catch (err)
        {
            $interval.cancel(task);
            $scope.h2 = 'Erro!';
            $scope.msg = err.message;
        }
    }
    
    // botão de voltar
    $scope.goBack = function() {
        window.history.back();
    };
    
    $scope.$on('$destroy', function() {
            httpSincrono.close();
        });
    
    checkConnection();
});




app.directive('backButton', function(){
    return {
      restrict: 'A',

      link: function(scope, element, attrs) {
        element.bind('click', goBack);

        function goBack() {
          history.back();
          scope.$apply();
        }
      }
    };
});

/*
app.directive('ngConfirmClick', [function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			element.bind('click', function() {
				var condition = scope.$eval(attrs.ngConfirmCondition);
				if(condition){
					var message = attrs.ngConfirmMessage;
					if (message && confirm(message)) {
						scope.$apply(attrs.ngConfirmClick);
					}
				}
				else{
					scope.$apply(attrs.ngConfirmClick);
				}
			});
		}
	}
}]);
*/

app.directive('ngConfirmClick', [
    function(){
        return {
            link: function (scope, element, attr) {
                var msg = attr.ngConfirmClick || "Você tem certeza?";
                var clickAction = attr.confirmedClick;
                element.bind('click',function (event) {
                    if ( window.confirm(msg) ) {
                        scope.$eval(clickAction);
                    } else {
                        //window.history.back();
                    }
                });
            }
        };
}]);



app.filter('iif', function () {
   return function(input, trueValue, falseValue) {
        return input ? trueValue : falseValue;
   };
});