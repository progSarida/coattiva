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

$el_list_Id = intval($cls_help->getVar('el_list_Id'));

if(is_null($el_list_Id)){
    echo json_encode(['esito' => 'KO', 'message' => 'Elaboration_List_Id INESISTENTE']);
	return;
}

    $query_up_el_list = " UPDATE elaboration_lists SET TempFlag = 1 WHERE ID =  ". $el_list_Id;      
    

if(mysqli_query($cls_db->conn, $query_up_el_list))   
{
    echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE']);
    return;
}
else
{
    echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE']);
    return;
}