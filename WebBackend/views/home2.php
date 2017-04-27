<!DOCTYPE html>
<html dir="ltr">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Frontpage layout example - UIkit documentation</title>
        <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
        <link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon.png">
        <link rel="stylesheet" href="css/uikit.gradient.min.css">
        <script src="js/jquery.min.js"></script>
        <script src="js/uikit.min.js"></script>
        <script src="js/angular.min.js"></script>
    </head>

    <body ng-app="app">

        <div ng-controller="ctrl" class="uk-container uk-container-center uk-margin-top uk-margin-large-bottom">

            <nav class="uk-navbar uk-margin-large-bottom">
                <a class="uk-navbar-brand uk-hidden-small" href="layouts_frontpage.html">Brand</a>
                <ul class="uk-navbar-nav uk-hidden-small">
                    <li class="uk-active">
                        <a href="layouts_frontpage.html">Frontpage</a>
                    </li>
                    <li>
                        <a href="layouts_portfolio.html">Portfolio</a>
                    </li>
                    <li>
                        <a href="layouts_blog.html">Blog</a>
                    </li>
                    <li>
                        <a href="layouts_documentation.html">Documentation</a>
                    </li>
                    <li>
                        <a href="layouts_contact.html">Contact</a>
                    </li>
                    <li>
                        <a href="layouts_login.html">Login</a>
                    </li>
                </ul>
                <a href="#offcanvas" class="uk-navbar-toggle uk-visible-small" data-uk-offcanvas></a>
                <div class="uk-navbar-brand uk-navbar-center uk-visible-small">Brand</div>
            </nav>

            <div class="uk-grid" data-uk-grid-margin>
                <div class="uk-width-medium-1-1">

                    <form ng-model="form1" class="uk-form uk-form-stacked uk-text-left" ng-submit="sendForm()">                      
                        <input type="hidden" name="oi" value="eita" />
                        
                        <input type="submit" value="Enviar" name="submit" />
                    </form>

                </div>
            </div>

        </div>

        <div id="offcanvas" class="uk-offcanvas">
            <div class="uk-offcanvas-bar">
                <ul class="uk-nav uk-nav-offcanvas">
                    <li class="uk-active">
                        <a href="layouts_frontpage.html">Frontpage</a>
                    </li>
                    <li>
                        <a href="layouts_portfolio.html">Portfolio</a>
                    </li>
                    <li>
                        <a href="layouts_blog.html">Blog</a>
                    </li>
                    <li>
                        <a href="layouts_documentation.html">Documentation</a>
                    </li>
                    <li>
                        <a href="layouts_contact.html">Contact</a>
                    </li>
                    <li>
                        <a href="layouts_login.html">Login</a>
                    </li>
                </ul>
            </div>
        </div>
        <script>
            var app = angular.module("app", []);
            
            app.controller("ctrl", function($scope, $http) {
                $scope.sendForm = function ()
                {
                    alert('oi');
                    var meuForm = new FormData();
                    meuForm.append('files[]', 'file:///C:/Users/igoro_000/Pictures/banner_whatsapp.jpg.png');
                    alert('eeeeiii');
                    $http({
                        method: 'POST',
                        url: '/home',
                        data: meuForm,
                        withCredentials: true,
                        headers: {'Content-Type': undefined },
                        transformRequest: angular.identity
                    }).success(function() {
                        console.log("Uploaded");
                    }).error(function() {
                        console.log("Error");
                    });
                };
            });
        </script>
    </body>
</html>