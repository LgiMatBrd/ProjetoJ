<?php

/*
 * Interface com o app para receber as imagens no servidor.
 */

define('ROOT_DIR', dirname(dirname(__FILE__)));

require '../lib/pdfgenerator/fpdf.php';
require '../config/psl-config.php';
require '../config/db_connect.php';

/* ===== NECESSÁRIO PARA O ACESSO SEGURO E DIFERENCIADO DOS ARQUIVOS ===== */

$userID = -1; // Temporário (poderá ser usado para diferenciar os usuários no futuro)

$keySalt = 'SWdvciBPbGl2ZWlyYSBTb3V6YQ=='; // "Chave de acesso" aos arquivos de usuários

// Caminho para salvar o arquivo recebido. Importante o 'f' no final.
$caminho = ROOT_DIR.'/uploads/imagens/'.md5($userID.$keySalt).'/f';

/* ===== =========================================================== ===== */

header('Content-Type: text/plain; charset=utf-8');

try
{
    // Garante que o arquivo a ser tratado foi mesmo enviado pelo método POST
    // e que não se trata de nenhum arquivo do sistema tentando ser acessado por
    // hackers
    if (!is_uploaded_file($_FILES['upfile']['tmp_name'])) {
        throw new RuntimeException('Nenhum arquivo valido enviado.'); }
    
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (!isset($_FILES['upfile']['error']) ||
        is_array($_FILES['upfile']['error']))
    {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['upfile']['error'])
    {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here.
    if ($_FILES['upfile']['size'] > 1000000){
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search($finfo->file($_FILES['upfile']['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true))
    {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    $nome = filter_var($_FILES['upfile']['name'],FILTER_SANITIZE_STRING);
    if (!move_uploaded_file($_FILES['upfile']['tmp_name'],$caminho.$nome.$ext))
    {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    echo 'File is uploaded successfully.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}