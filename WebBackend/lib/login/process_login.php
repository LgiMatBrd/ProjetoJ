<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ROOT_DIR'))
    die;

include_once ROOT_DIR.'/config/db_connect.php';
include_once ROOT_DIR.'/lib/login/functions.php';

sec_session_start(); // Nossa segurança personalizada para iniciar uma sessão php.

if (isset($_POST['login'], $_POST['p'])) {
    $login = $_POST['login'];
    $password = $_POST['p'];
 
    if (login($login, $password, $mysqli) == true) {
        // Login com sucesso 
        header('Location: ../protected_page.php');
    } else {
        // Falha de login 
        header('Location: ../index.php?error=1');
    }
} else {
    // As variáveis POST corretas não foram enviadas para esta página. 
    echo 'Requisição Inválida';
}