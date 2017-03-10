//Define an angular module for our app 
var app = angular.module('seyconelApp', ['ngRoute','ngStorage']); 

app.config(function($routeProvider) {
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
});

app.run(function($localStorage) {
    if (typeof $localStorage.clientes === 'undefined' || typeof $localStorage.clientes.version === 'undefined' || $localStorage.clientes.version !== 'v0.2')
    {
        $localStorage.clientes = {
            nextID: 0,
            version: 'v0.2',
            db: {}
        }; 
    }
    if (typeof $localStorage.vistorias === 'undefined' || typeof $localStorage.vistorias.version === 'undefined' || $localStorage.vistorias.version !== 'v0.2')
    {
        $localStorage.vistorias = {
            nextID: 0,
            version: 'v0.2',
            db: {}
        }; 
    } 
});

app.controller('homeController', function($scope, $http, $localStorage, $location) {
    
    $scope.verVistorias = function (id) {
        $location.path('/vistorias/' + id);
    };

    $scope.addCliente = function($valor)
    {
        var data_criacao = new Date();
        id = $localStorage.clientes.nextID;
        
        cliente = new Cliente(); // Definições do objeto estão no arquivo dbObj.js
        cliente.id = id;
        cliente.nome = $valor;
        cliente.data_criacao = data_criacao; 
        $localStorage.clientes.db[id] = cliente;
        
        id = id + 1;
        $localStorage.clientes.nextID = id;
        
    };
    
    $scope.lerClientes = function ()
    {
        return $localStorage.clientes.db;
    }; 
    
    $scope.deletarCliente = function ($id)
    {
        window.history.back();
        delete $localStorage.clientes.db[$id];
    };     
    console.dir($localStorage);
});

app.controller('vistoriasController', function($scope, $routeParams, $http, $localStorage, $filter) {
	
    // id do cliente
    $scope.id = $routeParams.id;
    $scope.id_dono = $routeParams.id;
    
    // botão de voltar
    $scope.goBack = function() {
        window.history.back();
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