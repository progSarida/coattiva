<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$cc = $cls_help->getVar('CC');
$partita_id = $cls_help->getVar('partita_id');
$action = $cls_help->getVar('action');


if($action == "TRUE"){

    $query_check = "SELECT * FROM partite_check_stra WHERE Partita_ID = ".$partita_id." AND CC = '".$cc."';";
    $result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_check));

    if($result == null){
        $query = "INSERT INTO partite_check_stra (Partita_ID, CC, flag_check) VALUES (".$partita_id." ,'".$cc."',1)";
        $check = $cls_db->ExecuteQuery($query);
    }
    else{
        $query = "UPDATE partite_check_stra SET flag_check = 1 WHERE Partita_ID = ".$partita_id." AND CC = '".$cc."';";
        $check = $cls_db->ExecuteQuery($query);
    }

    if(!$check) {
        echo json_encode(['esito' => 'KO', 'message' => 'ERRORE NELL\'AGGIORNAMENTO']);
        die;
    }
}
else{
    $query = "UPDATE partite_check_stra SET flag_check = 0 WHERE Partita_ID = ".$partita_id." AND CC = '".$cc."';";
    $check = $cls_db->ExecuteQuery($query);

    if(!$check) {
        echo json_encode(['esito' => 'KO', 'message' => 'ERRORE NELL\'AGGIORNAMENTO']);
        die;
    }
}
echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE È ANDATA A BUON FINE']);
die;