<?php

/* 
 * Interface com o app para sincronização completa do banco de dados local e 
 * em servidor.
 */

define('ROOT_DIR', dirname(dirname(__FILE__)));

header('Content-Type: application/json; charset=utf-8');
$token = 'dasda'; 
ob_start();

require '../config/global.php';
date_default_timezone_set('UTC');

//$json_string = print_r(file_get_contents("php://input"), true);

$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);

$resposta = array(
    'status'    => '',
    'msg'       => ''
);

try
{
    // Checa o Token de acesso
    include_once ROOT_DIR.'/config/db_connect.php';
    include_once ROOT_DIR.'/lib/login/functions.php';

    // Inicia uma sessão segura
    sec_session_start();
    $logged = 'out';
    if (login_check($mysqli) != true)
    {
        $resposta = [
            'status' => 'ok',
            'logged' => 'out',
            'h2' => 'Necessário logar!',
            'msg' => 'Você está desconectado!'
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode((object)$resposta);
        die;
    } else $logged = 'in';
    
    switch ($request['func'])
    {
        case 'checkServer':
            serverOk();
            break;
        case 'sendData':
            receive();
            break;
        case 'deleteData':
            delete();
            break;
        case 'getData':
            send();
            break;
        default:
            throw new Exception('Solicitação não compreendida!');
    }
} catch (Exception $e)
{
    $resposta['status'] = 'error';
    $resposta['msg'] .= $e->getMessage() . "\n";
}

function serverOk()
{
    global $resposta;
    $resposta['status'] = 'ok';
}

function receive()
{
    global $request, $resposta, $mysqli;
    $keys = array();
    $valores = array();
    $idExt = 0;
    $vistoriaUpdateID = 0;
    if ($request['dbName'] === 'itensVistoriados')
    {
        $dbstr = $request['row']['dados']['nome'];
        $dbstr = 'item'.ucfirst($dbstr);
    }
    else
        $dbstr = $request['dbName'];
    
    foreach ($request['row'] as $key3 => $value3)
    {
        $value4 = array();
        if ($key3 == 'fotos64')
        {
            $value4[$key3] = array();
            foreach ($value3 as $value2)
            {
                $value4[$key3][] = $value2;
            }
            //$value4[$key] = implode(',', $value4[$key]);
            $value4[$key3] = $mysqli->escape_string(json_encode($value4[$key3]));
            //base64_encode($file);
        }
        else if ($key3 == 'dados')
        {
            foreach ($value3 as $key2 => $value2)
            {
                if ($key2 == 'nome')
                    continue;
                $value4[$key2] = $value2;
            }
        } else {
            $value4[$key3] = $value3;
        }
        
        foreach ($value4 as $key => $value)
        {
            if (is_array($value))
                continue;
            if ($key == 'idext')
            {
                $idExt = (int)$value;
                continue;
            }
            else if ($key == 'id')
                continue;
            if (is_string($value))
                $valores[$key] = "'$value'";
            else if (is_bool($value))
                $valores[$key] = ($value)? 1 : 0;
            else
                $valores[$key] = $value;
            $keys[] = $key;
        }
    }
    try
    {
        if (!$date = new DateTime('@'.$valores['data_criacao']))
                throw new Exception ('Não foi possível atribuir a hora de criação.');
        $valores['data_criacao'] = '\''.$date->format('Y-m-d H:i:s').'\'';
        if (!$date = new DateTime('@'.$valores['modificado']))
                throw new Exception ('Não foi possível atribuir a hora de modificação.');
        $valores['modificado'] = '\''.$date->format('Y-m-d H:i:s').'\'';
    } catch (Exception $e)
    {
        $resposta = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
    }
    $resul = $mysqli->query("SELECT modificado FROM `$dbstr` WHERE id = ".$mysqli->escape_string($idExt).' LIMIT 1');
    if ($resul->num_rows > 0)
    {
        $row = $resul->fetch_assoc();
        if (strtotime($row['modificado']) < strtotime(substr($valores['modificado'], 1, -1)))
        {
            if (isset($valores['id_vistoria']))
                    $vistoriaUpdateID = $valores['id_vistoria'];
            foreach ($keys as $ekey)
            {
                $valores[$ekey] = "`$ekey`=$valores[$ekey]";
            }
            $valores = implode(',', $valores);
            $query = "UPDATE `$dbstr` SET $valores WHERE id=".$mysqli->escape_string($idExt);
        }
        else
        {
            $resposta = [
                'status' => 'ok',
                'idext' => $idExt
            ];
            return;
        }
        
    }
    else
    {
        $valores[] = $_SESSION['user_id'];
        $keys[] = 'id_user';
        
        $valores = implode(',',$valores);
        $keys = implode(',',$keys);

        $query = "INSERT INTO $dbstr ($keys) VALUES ($valores)";
    }
    try
    {
        if ($mysqli->query($query))
        {
            $resposta['status'] = 'ok';
            $resposta['idext'] = ($mysqli->insert_id)? $mysqli->insert_id : $idExt;
            if ($vistoriaUpdateID)
                $mysqli->query('UPDATE `vistorias` SET `relatorio`=b\'0\' WHERE id = '.$vistoriaUpdateID);
            
        }
        else
            throw new Exception ('Não foi possível executar o comando do SQL. '.$mysqli->error);
    } catch (Exception $e)
    {
        $resposta = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
    }
}

function delete()
{
    global $request, $resposta, $mysqli;
    
    if ($request['dbName'] === 'itensVistoriados')
    {
        $dbstr = $request['row']['nome'];
        $dbstr = 'item'.ucfirst($dbstr);
    }
    else
        $dbstr = $request['dbName'];
    
    try
    {
        $stmt = $mysqli->prepare("DELETE FROM $dbstr WHERE id = ?");
        
        if ($stmt)
        {
            $stmt->bind_param('i', $request['row']['idext']);
            $stmt->execute();
            $resposta = [
                'status' => 'ok'
            ];
        } else throw new Exception ('Não foi possível executar o statement. '.$mysqli->error);
    } catch (Exception $ex)
    {
        $resposta = [
                'status' => 'error',
                'msg' => $ex->getMessage()
            ];
    }
}

function send()
{
    
    global $request, $resposta, $mysqli;
    $dbstr = array();
    $db = array();
    $id = 0;
    
    $dbsNames = [
        'clientes',
        'vistorias',
        'itensVistoriados'
    ];
    
    set_time_limit(20*count($dbsNames));
    
    try
    {
        foreach ($dbsNames as $dbName)
        {
            $dbstr = array();
            if ($dbName === 'itensVistoriados')
            {
                $dbstr = [
                    'itemAces',
                    'itemDies',
                    'itemEctu',
                    'itemGael',
                    'itemLema',
                    'itemLila',
                    'itemLinc'
                ];
            }
            else
                $dbstr[] = $dbName;
            
            $db[$dbName] = array();
            $id = 0;
            $sendTimestamp = 0;
            foreach ($dbstr as $table)
            {
                $query = "SELECT * FROM `$table` WHERE id_user = ".$mysqli->escape_string($_SESSION['user_id']);
                
                if (!$resul = $mysqli->query($query))
                        throw new Exception ('Houve um erro no select. '.$mysqli->error);
                
                while ($row = $resul->fetch_assoc())
                {
                    //$id = $row['id'];
                    $db[$dbName][$id] = array();
                    $modificado = strtotime($row['modificado']);
                        if ($modificado > $sendTimestamp)
                            $sendTimestamp = $modificado;
                    
                    /*if ($request['dbName'] !== 'itensVistoriados')
                    {
                        $local['idext'] = $row['id'];
                        $local['id'] = $id;
                    }*/
                    if (isset($row['id_user']))
                        unset($row['id_user']);
                    if ($dbName !== 'itensVistoriados')
                    {
                        $local = $row;
                        $local['id'] = $id;
                        $local['idext'] = $row['id'];
                        $local['data_criacao'] = strtotime($row['data_criacao']);
                        $local['modificado'] = $modificado;
                        if ($dbName === 'vistorias')
                        {
                            for ($i = 0; $i < count($db['clientes']); $i++)
                            {
                                if ($row['id_cliente'] != $db['clientes'][$i]['idext'])
                                    continue;
                                $local['id_cliente'] = $db['clientes'][$i]['id'];
                                break;
                            }
                        }
                    }
                    else
                    {
                        $local['id'] = $id;
                        $local['idext'] = $row['id'];
                        $local['data_criacao'] = strtotime($row['data_criacao']);
                        $local['modificado'] = $modificado;
                        $local['fotos64'] = json_decode($row['fotos64']);
                        for ($i = 0; $i < count($db['vistorias']); $i++)
                        {
                            if ($row['id_vistoria'] != $db['vistorias'][$i]['idext'])
                                continue;
                            $local['id_vistoria'] = $db['vistorias'][$i]['id'];
                            break;
                        }
                        unset($row['id'],$row['idext'],$row['data_criacao'],$row['modificado'],$row['fotos64'],$row['id_vistoria']);
                        $local['dados'] = array();
                        $local['dados'] = $row;
                        $finfo = $resul->fetch_fields();
                        foreach ($finfo as $cinfo)
                        {
                            if (!isset($local['dados'][$cinfo->name]))
                                continue;
                            if ($cinfo->type == 16) //BIT
                                if ($local['dados'][$cinfo->name])
                                    $local['dados'][$cinfo->name] = 'true';
                                else
                                    unset($local['dados'][$cinfo->name]);
                            if (isset($local['dados']['ramal']) && $local['dados']['ramal'] == 0)
                                unset($local['dados']['ramal']);
                            if ($cinfo->type == 253) // VARCHAR
                                if (empty($local['dados'][$cinfo->name]))
                                    unset($local['dados'][$cinfo->name]);
                        }
                        
                        $local['dados']['nome'] = strtolower(substr($table, 4));
                        
                    
                    }
                    
                    if (isset($row['relatorio']))
                        unset($local['relatorio']);
                    if (isset($row['id_user']))
                        unset($local['id_user']);
                    $db[$dbName][$id] = $local;
                    $local = null;
                    $id++;
                }
                $resposta[$dbName]['nextID'] = $id;
                $resposta[$dbName]['sendTimestamp'] = $sendTimestamp;
                $resul->close();
            }
            
        }
        foreach ($dbsNames as $dbName)
        {
            $db[$dbName] = (object)$db[$dbName];
        }
        $resposta['dbs'] = $db;
        $resposta['status'] = 'ok';
        
    }
    catch (Exception $e)
    {
        $resposta = [
            'status' => 'error',
            'msg' => $e->getMessage()
        ];
    }
}

$resposta['logged'] = $logged;

$out1 = ob_get_contents();
ob_end_clean();

if (!empty($out1))
{
    $resposta = [
        'status' => 'error',
        'msg' => $out1
    ];
}


echo json_encode((object)$resposta);