<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$help = new cls_help();
$date = new cls_DateTimeI('IT');

$desc = "";
$admin = $_POST['admin'];                                           // Ente

if(isset($_POST['desc']))
    $desc = $_POST['desc'];                                         // Setto l'input dell'utente come parametro di ricerca se non vuoto

$query = "SELECT * FROM ruolo WHERE Descrizione LIKE '%".$desc."%' AND CC = '".$admin."'";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["Data_Fornitura"] = $date->Get_DateNewFormat($tableElem[$i]["Data_Fornitura"]);
    $tableElem[$i]["action_row"] = 'initialId("role_d",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"role_d\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);