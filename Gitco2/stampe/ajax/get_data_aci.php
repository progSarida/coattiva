<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";
include_once CLS."/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("IT",false);

$cc = $cls_help->getVar("cc");
$da_data = $cls_help->getVar("da_data");
$a_data = $cls_help->getVar("a_data");

$query = "SELECT COUNT(VAD.targa) AS Visure_Ricevute, COUNT(VAD.request_id) AS Numero_Richieste, EG.Denominazione, VA.date AS DataRichiesta, VA.IdRichiesta FROM request_visures_aci as VA ";
$query .= " LEFT JOIN request_visures_aci_detail AS VAD ON VAD.request_id = VA.id ";
$query .= " LEFT JOIN enti_gestiti AS EG ON EG.CC = VA.CC ";
$query .= "WHERE 1=1 ";

if($cc != null)
    $query .= " AND VA.CC = '".$cc."' ";
if($da_data != null)
    $query .= " AND VA.date >= '".$da_data."' ";
if($a_data != null)
    $query .= " AND VA.date <= '".$a_data."' ";

$query .= " GROUP BY VA.id ";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

for($i=0; $i<count($result); $i++){
    $result[$i]["DataRichiesta"] = $cls_date->Get_DateNewFormat($result[$i]["DataRichiesta"]);
}

echo json_encode($result);
