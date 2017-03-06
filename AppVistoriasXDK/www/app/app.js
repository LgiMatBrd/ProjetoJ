//Define an angular module for our app 
var app = angular.module('seyconelApp', ['ngRoute']);

app.config(function($routeProvider) {
    $routeProvider
    .when("/", {
        templateUrl : "paginas/clientes.html",
		controller  : 'homeController'
    })
    .when("/page2", {
        templateUrl : "paginas/page2.html",
		controller  : 'shopController'
    });
});

app.controller('shopController', function($scope, $http) {
	
  getItem(); // Load all available items 
  function getItem(){  
  $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/getItem.php").success(function(data){
        $scope.items = data;
       });
  };
  
  $scope.addItem = function (item) {
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/addItem.php?item="+item).success(function(data){
        getItem();
        $scope.itemInput = "";
      });
  };
  
  $scope.deleteItem = function (item) {
    if(confirm("Are you sure to delete this item?")){
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/deleteItem.php?itemID="+item).success(function(data){
        getItem();
      });
    }
  };

  $scope.clearItem = function () {
    if(confirm("Delete all checked items?")){
    $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/clearItem.php").success(function(data){
        getItem();
      });
    }
  };  

  $scope.changeStatus = function(item, status, task) {
    if(status=='2'){status='0';}else{status='2';}
      $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/updateItem.php?itemID="+item+"&status="+status).success(function(data){
        getItem();
      });
  };
 
});

app.controller('homeController', function($scope, $http) {
	
  getItem(); // Load all available items 
  function getItem(){
  $http.post("http://hom.agenciageld.com.br/app_seyconel/ajax/clientes/getClientes.php").success(function(data){
        $scope.items = data;
       });
  };
  
  $scope.addItem = function (item) {
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
  };

});