<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

//Costruzione query

$ric_CDF = $cls_help->getVar('company_cf');
$allCities = $cls_help->getVar('all_city');
$admin = $cls_help->getVar('admin');

$query = "SELECT ID, Comune_ID, Partita_Iva AS CF, Ditta AS Utente, Genere , Forma_Giuridica, CC_Comune FROM utente ";
$query.= "WHERE Genere = 'D' AND Partita_Iva like '%".$ric_CDF."%' ";
if($allCities!="y")
    $query.= "and CC_Comune='".$admin."' ";
$query.= " ORDER BY CF";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["Genere"] = "Ditta";
    $tableElem[$i]["action_row"] = 'initialId("company_cf",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"company_cf\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);
