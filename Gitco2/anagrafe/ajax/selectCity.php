<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$help = new cls_help();

$city = "";

if(isset($_POST['city']))
    $city = $_POST['city'];

$query = "SELECT CL.Com_Nome AS nome, CL.Com_Cap AS cap, CL.Com_Codice_Catastale AS CC_C, PL.Pro_Sigla AS prov, CL.Com_Codice_Provincia As CC_P";           //
$query.= " FROM comuni_lista as CL LEFT JOIN province_lista AS PL ON PL.Pro_Codice = CL.Com_Codice_Provincia WHERE CL.Com_Nome LIKE '%".$city."%'";
$query.= " ORDER BY Com_Nome";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["action_row"] = 'initialId("city",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"city\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-hand-pointer fa-2x'></i>";
}

echo json_encode($tableElem);