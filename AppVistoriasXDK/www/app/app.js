//Define an angular module for our app
var app = angular.module('shopApp', []);

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
