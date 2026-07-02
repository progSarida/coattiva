<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS."/cls_LOG.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$idImport = $cls_help->getVar('idImport');
$path = $cls_help->getVar('path');

if (is_null($idImport) || is_null($path))
{
    echo json_encode([ 'message' => 'KO'
                    ]);
    return;
}

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

$query = "DELETE FROM imports WHERE ID = " . $idImport;

if (!$cls_db->ExecuteQuery($query)) {
    goto KO;
}

/*
try
{
    return mysqli_query($cls_db->conn, $query);
}
catch (mysqli_sql_exception $e){
    goto KO;
}
*/

if (!@unlink($path)) 
{
    $log->error("Impossibile eliminare il file <".$path.">");
    goto KO;
}

$cls_db->End_Transaction();
echo json_encode(['message' => 'OK']);
return;

KO:
$cls_db->Rollback();
echo json_encode(['message' => 'KO']);
return; 


