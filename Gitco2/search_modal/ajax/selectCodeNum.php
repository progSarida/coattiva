<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$help = new cls_help();

$query = "SELECT * FROM codice_tributo WHERE ( ( Settore = '".$_POST['area']."' ";

if($_POST['sub_area']!="")
    $query.= " AND ( Sottosettore = '".$_POST['sub_area']."' OR Sottosettore = '' ) ";
$query.=" ) OR Settore='SARIDA' AND Disabled!='Y' ";

$query.= " ) AND Codice_Tributo LIKE '%".$_POST['code']."%' ORDER BY Codice_Tributo";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["action_row"] = 'initialId("code_n",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"code_n\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);