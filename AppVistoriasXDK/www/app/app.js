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
    });
});

app.controller('homeController', function($scope, $http, $localStorage) {
    
    /* Luigi, se souber como faz, coloca esse pequeno trecho na parte de app.config */
    if ($localStorage.clientes.version !== 'v0.1')
    {
        $localStorage.clientes = {
            nextID: 0,
            version: 'v0.1',
            db: {}
        }; 
    }
    /* Luigi, o pequeno trecho vai até aqui */
    
    $scope.addItem = function ($valor)
    {
        
        id = $localStorage.clientes.nextID;
        
        cliente = new ClienteObj(); // Definições do objeto estão no arquivo dbObj.js
        cliente.id = id;
        cliente.nome = $valor;
        $localStorage.clientes.db[id] = cliente;
        
        id = id + 1;
        $localStorage.clientes.nextID = id;
        console.log($localStorage);
    };
    
    $scope.readClientes = function ()
    {
        return $localStorage.clientes.db;
    };
    
    $scope.deleteItem = function ($id)
    {
        delete $localStorage.clientes.db[$id];
    };
    
  /*$scope.addItem = function (item) {
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/clientes/addClientes.php?item="+item).success(function(data){
        getItem();
        $scope.itemInput = "";
      });
  };
  
  $scope.deleteItem = function (item) {
    if(confirm("Are you sure to delete this item?")){
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/clientes/deleteClientes.php?itemID="+item).success(function(data){
        getItem();
      });
    }
  };

  $scope.clearItem = function () {
    if(confirm("Delete all checked items?")){
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/clientes/clearClientes.php").success(function(data){
        getItem();
      });
    }
  };  

  $scope.changeStatus = function(item, status, task) {
    if(status=='2'){status='0';}else{status='2';}
      $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/clientes/updateClientes.php?itemID="+item+"&status="+status).success(function(data){
        getItem();
      });
  };*/

});

app.controller('vistoriasController', function($scope, $routeParams, $http) {
	$scope.id= $routeParams.id;
    $scope.goBack = function() {
        window.history.back();
    };
    
    getItem(); // Load all available items 
    function getItem(){  
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/vistorias/getVistorias.php?id_cliente="+$scope.id).success(function(data){
        $scope.items = data;
       });
    }

    $scope.addItem = function (item) {
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/vistorias/addVistorias.php?item="+item).success(function(data){
        getItem();
        $scope.itemInput = "";
      });
    };
 
});