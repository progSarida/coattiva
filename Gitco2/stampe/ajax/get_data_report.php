<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";
include_once CLS."/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("IT",false);

$cc = $cls_help->getVar("cc");
$UserName = $cls_help->getVar("UserName");
$da_data = $cls_help->getVar("da_data");
$a_data = $cls_help->getVar("a_data");

$query = "SELECT COUNT(RP.Pec) AS Pec_Ricevute, COUNT(RP.IdRichiesta) AS Numero_Richieste, EG.Denominazione, R.DataRichiesta, R.IdRichiesta, R.UserName FROM ini_pec_request as R ";
$query .= " LEFT JOIN ini_pec_request_pec AS RP ON RP.IdRichiesta = R.IdRichiesta ";
$query .= " LEFT JOIN enti_gestiti AS EG ON EG.CC = R.CC ";
$query .= "WHERE R.UserName = '".$UserName."' AND R.EsitoRichiesta = 'OK' AND R.EsitoFornitura = 'OK' ";

if($cc != null)
    $query .= " AND R.CC = '".$cc."' ";
if($da_data != null)
    $query .= " AND DATE_FORMAT(R.DataRichiesta, '%Y-%m-%d') >= '".$da_data."' ";
if($a_data != null)
    $query .= " AND DATE_FORMAT(R.DataRichiesta, '%Y-%m-%d') <= '".$a_data."' ";

$query .= " GROUP BY R.IdRichiesta ";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

for($i=0; $i<count($result); $i++){
    $result[$i]["DataRichiesta"] = $cls_date->Get_DateNewFormat($result[$i]["DataRichiesta"]);
    $result[$i]["IdRichiesta"] = (int)$result[$i]["IdRichiesta"];
}

echo json_encode($result);
