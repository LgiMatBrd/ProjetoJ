//Define an angular module for our app 
var app = angular.module('seyconelApp', ['ngRoute','ngStorage','ngMaterial','ngMessages', 'material.svgAssetsCache']); 

app.config(function($routeProvider,$mdIconProvider) {
    $routeProvider
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
    .otherwise({
       redirectTo: '/'
    }); 
    
    $mdIconProvider
        .iconSet('social', 'img/icons/sets/social-icons.svg', 24)
        .iconSet('call', 'img/icons/sets/communication-icons.svg', 24)
        .iconSet('device', 'img/icons/sets/device-icons.svg', 24)
        .iconSet('communication', 'img/icons/sets/communication-icons.svg', 24)
        .defaultIconSet('img/icons/sets/core-icons.svg', 24);
});

app.run(function($localStorage) {
    if (typeof $localStorage.clientes === 'undefined' || typeof $localStorage.clientes.db === 'undefined' || $localStorage.clientes.version !== 'v0.2')
    {
        $localStorage.clientes = {
            nextID: 0,
            version: 'v0.2',
            db: {}
        }; 
    }
    if (typeof $localStorage.vistorias === 'undefined' || typeof $localStorage.vistorias.db === 'undefined' || $localStorage.vistorias.version !== 'v0.2')
    {
        $localStorage.vistorias = {
            nextID: 0,
            version: 'v0.2',
            db: {}
        }; 
    } 
    if (typeof $localStorage.itensVistoriados === 'undefined' || typeof $localStorage.itensVistoriados.db === 'undefined' || $localStorage.itensVistoriados.version !== 'v0.1')
    {
        $localStorage.itensVistoriados = {
            nextID: 0,
            version: 'v0.1',
            db: {}
        }; 
    } 
});

app.controller('homeController', function($scope, $http, $localStorage, $location, $mdDialog) {
    
    console.dir($localStorage);
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
            var data_criacao = new Date();
            id = $localStorage.clientes.nextID;

            cliente = new Cliente(); // Definições do objeto estão no arquivo dbObj.js
            cliente.id = id;
            cliente.nome = $valor;
            cliente.data_criacao = data_criacao; 
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
        var data_criacao = new Date();
        nextId = $localStorage.vistorias.nextID;
        
        vistoria = new Vistoria(); 
        vistoria.id = nextId;
        vistoria.id_dono = $id_dono;
        vistoria.nome = $valor;
        vistoria.data_criacao = data_criacao;
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
                if (db[vist_key].id_dono == $id_dono)
                    $scope.vistorias[vist_key] = Object.create(db[vist_key]);
            }
        }
    }
   
    // deletar vistoria 
    $scope.deletarVistoria = function ($id)
    {
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
            var data_criacao = new Date();
            id = $localStorage.vistorias.nextID;

            vistoria = new Vistoria(); 
            vistoria.id = id;
            vistoria.id_dono = id_dono;
            vistoria.nome = $valor;
            vistoria.data_criacao = data_criacao; 
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
            .textContent('Aqui ficarão algumas estátisticas do cliente.')
            .ok('Fechar')
            .targetEvent(event)
        );
    };
    
});

app.controller('vistoriaController', function($scope, $routeParams, $http, $localStorage, $filter, $mdDialog) {
	
    $scope.id_dono = $routeParams.id;
	$scope.id = $routeParams.id;
	$scope.nome = $localStorage.vistorias.db[$scope.id].nome;
    console.log('ID Visotria: '+$scope.id);
    
    // chama a função para preencher a variável que armazena as vistorias desse cliente
	$scope.itensVistoriados = {};
    populaVistorias($scope.id_dono);
    
    $scope.showAdvanced = function(ev) {
        $mdDialog
            .show({
            controller: DialogController,
            templateUrl: 'formulario-vistoria.tmpl.html',
            id_dono: $scope.id_dono,
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

    function DialogController($scope, $mdDialog, id_dono, tiposVistorias) {
        
        $scope.tiposVistorias = tiposVistorias;
        $scope.addItem = function(itemForm) {
            
            var data_criacao = new Date();
            id = $localStorage.itensVistoriados.nextID;

            /* OBJETO
            this.id = 0;
            this.id_dono = '';
            this.data_criacao = '';
            this.dados = '';
            */
            item = new itemVitoriado(); 
            item.id = id;
            item.id_dono = id_dono;
            item.data_criacao = data_criacao; 
            
            item.dados = $scope.item;
            
            $localStorage.itensVistoriados.db[id] = item;

            id = id + 1; 
            $localStorage.itensVistoriados.nextID = id;
            
            $mdDialog.hide();

        };
        
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
                if (db[vist_key].id_dono == $id_dono)
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
                if (db[vist_key].id_dono == $id_dono)
                    resultado[vist_key] = Object.create(db[vist_key]);
            }
        }
        
        return resultado;
    };
   
    // deletar vistoria
    $scope.deletarVistoria = function ()
    {
        delete $localStorage.itensVistoriados.db[$scope.id]; 
        populaVistorias($scope.id);
    };
    
    $scope.tiposVistorias = {
        'linc': 'Linga de corrente (NR-11/NBR 15516 1 e 2/NBR ISO 3076/NBR ISO 1834)',
        'ectu': 'Eslingas, cintas planas e tubulares. (NR-11 NBR 15637 1 e 2)',
        'ectu3': 'Acessórios  (Ganchos, Cadeados, olhais, Manilhas) (NR-11/NBR 13545/NBR 16798)',
        'ectu4': 'Garras de elevação  (NR-11)',
        'ectu5': 'Levantador magnético  (NR 11)',
        'ectu6': 'Dispositivos Especiais: (NR 11)',
        'ectu7': 'Lingas e Laços de cabos de aço'
    };
    
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
                        window.history.back();
                    }
                });
            }
        };
}]);