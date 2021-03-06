<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ROOT_DIR'))
    die;

include_once ROOT_DIR.'/config/db_connect.php';
include_once ROOT_DIR.'/config/psl-config.php';
 
$error_msg = "";
 
if (isset($_POST['atributos'], $_POST['username'], $_POST['email'], $_POST['p'], $_POST['pnome'])) {
    // Limpa e valida os dados passados em 
    $atributos = filter_input(INPUT_POST, 'atributos', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Email inválido
        $error_msg .= '<p class="error">O endereço de email digitado não é válido</p>';
    }
 
    $password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
    if (strlen($password) != 128) {
        // A senha com hash deve ter 128 caracteres.
        // Caso contrário, algo muito estranho está acontecendo
        $error_msg .= '<p class="error">Invalid password configuration.</p>';
    }
    
    $pNome = filter_input(INPUT_POST, 'pnome', FILTER_SANITIZE_STRING);
     
    // O nome de usuário e a validade da senha foram conferidas no lado cliente.
    // Não deve haver problemas nesse passo já que ninguém ganha 
    // violando essas regras.
    //
 
    $prep_stmt = 'SELECT id FROM users WHERE email = ? LIMIT 1';
    $stmt = $mysqli->prepare($prep_stmt);
 
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows == 1) {
            // Um usuário com esse email já esixte
            $error_msg .= '<p class="error">A user with this email address already exists.</p>';
        }
    } else {
        $error_msg .= '<p class="error">Database error</p>';
    }
 
    // LISTA DE TAREFAS: 
    // Precisamos bolar soluções para quando o usuário não tem 
    // direito a se registrar, verificando que tipo de usuário está tentando 
    // realizar a operação.
 
    if (empty($error_msg)) {
        // Crie um salt aleatório
        $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));
 
        // Crie uma senha com salt 
        $password = hash('sha512', $password . $random_salt);
        
        $create_time = date('Y-m-d H:i:s');
 
        // Inserir o novo usuário no banco de dados 
        if ($insert_stmt = $mysqli->prepare("INSERT INTO users (username, pass, salt, email, create_time, atributos, pNome) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
            $insert_stmt->bind_param('sssssis', $username, $password, $random_salt, $email, $create_time, $atributos, $pNome);
            // Executar a tarefa pré-estabelecida.
            if (! $insert_stmt->execute()) {
                $error_msg .= 'Falha ao registrar usuário';
            }
        }
    }
}