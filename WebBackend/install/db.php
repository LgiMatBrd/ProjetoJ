<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ROOT_DIR'))
    die;

$error_msg = '';

$host = filter_input(INPUT_POST, 'db-server', FILTER_SANITIZE_URL);
$user = filter_input(INPUT_POST, 'db-user', FILTER_SANITIZE_STRING);
$pass = filter_input(INPUT_POST, 'db-pass', FILTER_SANITIZE_STRING);
$dbname = filter_input(INPUT_POST, 'db-name', FILTER_SANITIZE_STRING);

$mysqli_install = new mysqli($host, $user, $pass, $dbname);

if ($mysqli_install->connect_error)
{
    $error_msg = 'Problema de conexão ('.$mysqli_install->connect_errno.'): '.$mysqli_install->connect_error;
}
else
{
    // Checa se a db está limpa
    $prep_stmt = 'SHOW TABLES';
    $stmt = $mysqli_install->prepare($prep_stmt);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows >= 1)
    {
        $error_msg = 'Já existem tabelas no banco de dados. O instalador saltou a criação de tabelas.';;
    }
    else
    {
        
    }
    
    if (file_exists(ROOT_DIR.'/config/psl-config.php'))
    {
        if (!unlink(ROOT_DIR.'/config/psl-config.php'))
                $error_msg = 'Já existe um arquivo de configuração do banco de dados e o instalador não conseguiu apagá-lo.';
    }
    if (!file_exists(ROOT_DIR.'/config/psl-config.php'))
    {
        if (!$myfile = fopen(ROOT_DIR.'/config/psl-config.php', 'w'))
        {
            $error_msg = 'Não foi possível criar o arquivo de configuração do banco de dados.';
        }
        else
        {
            $strconf = <<< EOF
<?php

if (!defined('ROOT_DIR'))
    die;
/**
 * Seguem os detalhes para login para o banco de dados
 */  

define('HOST', '$host');     // Para o host com o qual você quer se conectar.
define('USER', '$user');    // O nome de usuário para o banco de dados. 
define('PASSWORD', '$pass');    // A senha do banco de dados. 
define('DATABASE', '$dbname');    // O nome do banco de dados. 
EOF;
            fwrite($myfile, $strconf);
            fclose($myfile);
        }
        
    }
    
            
    $mysqli_install->close();
}

