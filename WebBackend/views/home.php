<?php

// Esta view possui um controller, por este motivo, este arquivo não deve ser 
// acessado de forma direta ou através de outro arquivo não previsto.
// 
// Testa se este arquivo foi incluído pelo seu controller
if (!defined('HOME_CONTROLLER'))
    die;

$time = date('Ymd');
$concreteBackgroundWallPaper = 'http://backgroundimages.concrete5.org/wallpaper/'.$time.'.jpg';
$concreteBackgroundDesc = 'http://backgroundimages.concrete5.org/get_image_data.php?image='.$time.'.jpg';

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Cadastro de usuários</title>
        <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
        <link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon.png">
        <link rel="stylesheet" href="css/uikit.gradient.min.css" />
        <link rel="stylesheet" href="css/components/form-password.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/uikit.min.js"></script>
        <script src="js/components/form-password.min.js"></script>
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script> 
        <script type="text/javascript" src="js/angular.min.js"></script>
        
    </head>
    <body ng-app="app">
        <div class="uk-width-1-1">
            <div class="uk-width-9-10 uk-container-center uk-margin-large uk-margin-large-top">
                <div class="uk-panel uk-panel-space uk-panel-box uk-panel-header">
                    <h2 class="uk-text-center">Cadastrar novo usuário:</h2>
                    <div class="uk-panel-title"></div>
                    <form id="form1" class="uk-form uk-form-horizontal" action="/home" method="post" name="form">                      
                        <fieldset>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Usuário:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="text" ng-model="u" name="username" required />
                                    <div ng-if="u.$touched && u.$invalid" class="uk-alert uk-alert-danger">...</div>
                                </div>
                            </div>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Senha:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="password" id="user-pass1" />
                                </div>
                            </div>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Confirmação da senha:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="password" id="user-pass2" name="password" />
                                </div>
                            </div>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Email:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="email" id="user-email1" />
                                </div>
                            </div>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Confirmação de email:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="email" id="user-email2" name="email" />
                                </div>
                            </div>
                            <div class="uk-form-row uk-margin-large-bottom">
                                <label class="uk-form-label">Primeiro nome:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="text" name="pnome" />
                                </div>
                            </div>
                        </fieldset>
                        <div class="uk-form-row">
                            <a class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="button" 
                               onclick="formhash(form1, form1.password);">Cadastrar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="backstretch" style="left: 0px; top: 0px; overflow: hidden; margin: 0px; padding: 0px; height: 100%; width: 100%; z-index: -999999; position: fixed;">
            <img style="position: absolute; margin: 0px; padding: 0px; border: medium none; width: 100%; height: 100%; max-height: none; max-width: none; z-index: -999999; left: 0px; top: 0px;" src="<? echo $concreteBackgroundWallPaper; ?>">
        </div>
        <script>
            angular.module('app', ['ngMessages']);
        </script>
    </body>
</html>