<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$help = new cls_help();

$addr = " ";
$city_cc = $_POST['city_cc'];                                       // Inserisco sempre perchè ho sempre il nome del comune
$admin = $_POST['admin'];                                           // Ente?

if(isset($_POST['addr_g']))
    $addr = $_POST['addr_g'];                                       // Setto l'input dell'utente come parametro di ricerca se non vuoto

$query = "SELECT Cap AS cap, Paese AS paese, Comune AS comune, Nome AS nome_via, ID AS id ";
$query.= "FROM toponimo	WHERE CC_Toponimo = '".$city_cc."' AND ID != 1 AND CC_Comune ='".$admin."'";
$query.= "AND Nome LIKE '%".$addr."%' ORDER BY Nome";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["action_row"] = 'initialId("addr_gen",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"addr_gen\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-hand-pointer fa-2x'></i>";
}

echo json_encode($tableElem);