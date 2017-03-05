<?php
$time = date(Ymd);
$concreteBackgroundWallPaper = 'http://backgroundimages.concrete5.org/wallpaper/'.$time.'.jpg';
$concreteBackgroundDesc = 'http://backgroundimages.concrete5.org/get_image_data.php?image='.$time.'.jpg';
?>
<!DOCTYPE html>
<html class="uk-height-1-1">
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
    <body class="uk-height-1-1">
        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error">Erro ao fazer o login!</p>';
        }
        ?> 
        <div class="uk-vertical-align uk-text-center uk-height-1-1">
            <div class="uk-vertical-align-middle uk-width-9-10 uk-width-medium-1-3 uk-margin-small uk-margin-small-top">
                <div class="uk-panel uk-panel-box uk-panel-header">
                    <h3 class="uk-panel-title">Faça login para continuar</h3>
                    <form id="form1" class="uk-form uk-form-stacked uk-text-left" action="login/process_login.php" method="post" name="login_form">                      
                        <div class="uk-form-row">
                            <label class="uk-form-label" for="email">Login</label>
                            <div class="uk-form-controls">
                                <input class="uk-width-1-1 uk-form-large" type="text" id="email" name="email" />
                            </div>
                        </div>
                        <div class="uk-form-row">
                            <label class="uk-form-label" for="password">Senha</label>
                            <div class="uk-form-controls">
                                <div class="uk-form-password uk-width-1-1">
                                    <input class="uk-width-1-1 uk-form-large"
                                                     type="password" 
                                                     name="password" 
                                                     id="password"/>
                                    <a class="uk-form-password-toggle" data-uk-form-password="{lblShow:'Mostrar', lblHide:'Esconder'}">Mostrar</a>
                                </div>
                            </div>
                        </div>
                        <div class="uk-form-row">
                            <a class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="button" 
                               onclick="formhash(form1, form1.password);">Entrar</a>
                        </div>
                    </form>
                    <p>If you don't have a login, please <a href="register.php">register</a></p>
                    <p>If you are done, please <a href="includes/logout.php">log out</a>.</p>
                    <p>You are currently logged <?php echo $logged ?>.</p>
                </div>
            </div>
        </div>
        
        <div class="backstretch" style="left: 0px; top: 0px; overflow: hidden; margin: 0px; padding: 0px; height: 100%; width: 100%; z-index: -999999; position: fixed;">
            <img style="position: absolute; margin: 0px; padding: 0px; border: medium none; width: 100%; height: 100%; max-height: none; max-width: none; z-index: -999999; left: 0px; top: 0px;" src="<? echo $concreteBackgroundWallPaper; ?>">
        </div>
    </body>
</html>