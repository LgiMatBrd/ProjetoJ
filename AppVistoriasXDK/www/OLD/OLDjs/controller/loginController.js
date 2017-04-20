
/*jslint browser: true*/
/*global console, MyApp*/

MyApp.angular.controller('loginController', ['$scope', '$http', 'InitService', function ($scope, $http, InitService) {
  'use strict';
  
  InitService.addEventListener('ready', function () {
      // DOM ready
    // You can access angular like this:
    // MyApp.angular
    
   
    // deletar vistoria
    $scope.loga = function ()
    {
        console.log('logado');
        MyApp.fw7.app.closeModal('.login-screen');
    };
    // And you can access Framework7 like this:
    // MyApp.fw7.app
  });
  
}]); 