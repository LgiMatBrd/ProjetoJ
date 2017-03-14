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
    if (typeof $localStorage.vistoria === 'undefined' || typeof $localStorage.vistoria.db === 'undefined' || $localStorage.vistoria.version !== 'v0.2')
    {
        $localStorage.vistoria = {
            nextID: 0,
            version: 'v0.2',
            db: {}
        }; 
    } 
});

app.controller('homeController', function($scope, $http, $localStorage, $location, $mdDialog) {

  $scope.people = [
    { name: 'Janet Perkins', img: 'img/100-0.jpeg', newMessage: true },
    { name: 'Mary Johnson', img: 'img/100-1.jpeg', newMessage: false },
    { name: 'Peter Carlsson', img: 'img/100-2.jpeg', newMessage: false }
  ];


    
    // Ver vistorias
    $scope.verVistorias = function (id) {
        $location.path('/vistorias/' + id);
    };

    $scope.navigateTo = function(to, event) {
        $mdDialog.show(
          $mdDialog.alert()
            .title('Navigating')
            .textContent('Imagine being taken to ' + to)
            .ariaLabel('Navigation demo')
            .ok('Neat!')
            .targetEvent(event)
        );
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

app.controller('vistoriasController', function($scope, $routeParams, $http, $localStorage, $location) {
	$scope.vistorias = {};
    // chama a função para preencher a variável que armazena as vistorias desse cliente
    populaVistorias($routeParams.id);
    
    // id do cliente
    $scope.id = $routeParams.id;
    $scope.id_dono = $routeParams.id;
    
    // botão de voltar
    $scope.goBack = function() {
        window.history.back();
    }; 
    
    // ver vistoria
    $scope.verVistoria = function (id) {
        $location.path('/vistoria/' + id);
    };
    
    // adicionar vistoria
    $scope.addVistoria = function($valor)
    {
        var data_criacao = new Date();
        id = $localStorage.vistorias.nextID;
        
        vistoria = new Vistoria(); 
        vistoria.id = id;
        vistoria.id_dono = $scope.id_dono; 
        vistoria.nome = $valor;
        vistoria.data_criacao = data_criacao; 
        $localStorage.vistorias.db[id] = vistoria;
        
        id = id + 1;
        $localStorage.vistorias.nextID = id;
        // Repopula a variavel de escopo $scope.vistorias
        populaVistorias($scope.id_dono);
         
    }; 

    // ler vistorias
    // popula a variavel $scope.vistorias
    function populaVistorias ($id_dono) 
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
    };
   
    // deletar vistoria
    $scope.deletarVistoria = function ($id)
    {
        window.history.back();
        delete $localStorage.vistorias.db[$id]; 
        // Repopula a variavel de escopo $scope.vistorias
        populaVistorias($scope.id_dono);
    };
    
});

/*app.controller('vistoriaController', function($scope, $routeParams, $http, $localStorage, $filter) {
	
    // id do cliente
    $scope.id = $routeParams.id;
    $scope.id_dono = $routeParams.id;
    
    // botão de voltar
    $scope.goBack = function() {
        window.history.back();
    };
    
    // ver vistoria
    $scope.verVistoria = function (id) {
        $location.path('/vistoria/' + id);
    };
    
    // adicionar vistoria
    $scope.addVistoria = function($valor)
    {
        var data_criacao = new Date();
        id = $localStorage.vistorias.nextID;
        
        vistoria = new Vistoria(); 
        vistoria.id = id;
        vistoria.id_dono = $scope.id_dono; 
        vistoria.nome = $valor;
        vistoria.data_criacao = data_criacao; 
        $localStorage.vistorias.db[id] = vistoria;
        
        id = id + 1;
        $localStorage.vistorias.nextID = id;
         
    }; 

    // ler vistorias
    $scope.lerVistorias = function ($id_dono) 
    {
        var resultado = new Object;
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
    $scope.deletarVistoria = function ($id)
    {
        delete $localStorage.vistorias.db[$id]; 
    };
 
});*/ 
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
    }
});

app.directive('ngConfirmClick', [
    function(){
        return {
            link: function (scope, element, attr) {
                var msg = attr.ngConfirmClick || "Você tem certeza?";
                var clickAction = attr.confirmedClick;
                element.bind('click',function (event) {
                    if ( window.confirm(msg) ) {
                        scope.$eval(clickAction)
                    } else {
                        window.history.back();
                    }
                });
            }
        };
}])