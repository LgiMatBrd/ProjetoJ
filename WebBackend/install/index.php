<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ROOT_DIR'))
    die('Diretório raiz não foi previamente definido!');

// Verifica se a instalação está atualizada (conforme arquivo version.php)
$installerVersion = '0.1';
include_once ROOT_DIR.'/config/version.php';

if ($version !== $installerVersion)
{

$time = date(Ymd);
$concreteBackgroundWallPaper = 'http://backgroundimages.concrete5.org/wallpaper/'.$time.'.jpg';
$concreteBackgroundDesc = 'http://backgroundimages.concrete5.org/get_image_data.php?image='.$time.'.jpg';

// Checa se foram recebidos dados de formulário
if (isset($_POST['instalar']))
{
    include ROOT_DIR.'/install/install.php';
} 
else
{
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Secure Login: Log In</title>
        <title>Login layout example - UIkit documentation</title>
        <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
        <link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon.png">
        <link rel="stylesheet" href="css/uikit.gradient.min.css" />
        <link rel="stylesheet" href="css/components/form-password.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/uikit.min.js"></script>
        <script src="js/components/form-password.min.js"></script>
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script> 
    </head>
    <body>
        <div class="uk-width-1-1">
            <div class="uk-width-9-10 uk-container-center uk-margin-large uk-margin-large-top">
                <div class="uk-panel uk-panel-space uk-panel-box uk-panel-header">
                    <h2 class="uk-text-center">Instalação e configuração</h2>
                    <div class="uk-panel-title"></div>
                    <form id="form1" class="uk-form uk-form-horizontal" action="index.php" method="post" name="login_form">                      
                        <input type="hidden" name="instalar" value="true" />
                        <input type="hidden" name="atributos" value="1" />
                        <fieldset>
                            <legend>Informações do banco de dados</legend>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Servidor:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" value="localhost" type="text" name="db-server" />
                                </div>
                            </div>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Usuário MySQL:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="text" name="db-user" />
                                </div>
                            </div>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Senha MySQL:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="password" name="db-pass" />
                                </div>
                            </div>
                            <div class="uk-form-row uk-margin-large-bottom">
                                <label class="uk-form-label">Nome do banco de dados:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="text" name="db-name" />
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>Informações do usuário administrador</legend>
                            <div class="uk-form-row">
                                <label class="uk-form-label">Usuário:</label>
                                <div class="uk-form-controls">
                                    <input class="uk-width-1-1" type="text" name="username" />
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
                               onclick="formhash(form1, form1.password);">Instalar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="backstretch" style="left: 0px; top: 0px; overflow: hidden; margin: 0px; padding: 0px; height: 100%; width: 100%; z-index: -999999; position: fixed;">
            <img style="position: absolute; margin: 0px; padding: 0px; border: medium none; width: 100%; height: 100%; max-height: none; max-width: none; z-index: -999999; left: 0px; top: 0px;" src="<? echo $concreteBackgroundWallPaper; ?>">
        </div>
    </body>
</html>
<?php
}
exit;
}
?>