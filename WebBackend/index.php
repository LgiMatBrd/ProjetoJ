<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('ROOT_DIR', dirname(__FILE__));

if (file_exists(ROOT_DIR.'/install/index.php'))
{
    include ROOT_DIR.'/install/index.php';
    
}

include_once ROOT_DIR.'/config/db_connect.php';
include_once ROOT_DIR.'/lib/login/functions.php';
 
sec_session_start();
 

    
$recurso = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);

if (login_check($mysqli) == true) {
    $logged = 'in';
    
    if (file_exists(ROOT_DIR.'/controllers'.$recurso.'.php'))
            include ROOT_DIR.'/controllers'.$recurso.'.php';
    
    else if ($recurso == '/index.php')
            header('Location: ./home');
    
    else if (file_exists(ROOT_DIR.'/views'.$recurso.'.php'))
            include ROOT_DIR.'/views'.$recurso.'.php';
    
    else if (file_exists(ROOT_DIR.$recurso.'.php'))
            include ROOT_DIR.$recurso.'.php';
    
    else include 'error.php';
    exit;
} else {
    $logged = 'out';
    include ROOT_DIR.'/controllers/login.php';
}

