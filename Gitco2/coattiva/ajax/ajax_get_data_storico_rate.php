<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS."/cls_LOG.php";
include_once CLS . "/cls_DateTimeInLine.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();
$cls_date = new cls_DateTimeI("IT",false);

$c = $cls_help->getVar("cc");
$id_atto = $cls_help->getVar("atto");

$query = "SELECT * FROM storico_rateizzazioni WHERE CC = '".$c."' AND atto_id = ".$id_atto;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));
$count = count($result);
for($i=0; $i < $count; $i++){
    $result[$i]["data_richiesta"] = $cls_date->Get_DateNewFormat($result[$i]["data_richiesta"],"DB");
    $result[$i]["data_prima_rata"] = $cls_date->Get_DateNewFormat($result[$i]["data_prima_rata"],"DB");
    $result[$i]["data_ultima_rata"] = $cls_date->Get_DateNewFormat($result[$i]["data_ultima_rata"],"DB");
    $result[$i]["data_eliminazione"] = $cls_date->Get_DateNewFormat($result[$i]["data_eliminazione"],"DB");
}

echo json_encode($result);
die;