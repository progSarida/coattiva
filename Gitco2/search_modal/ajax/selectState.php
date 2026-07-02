<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$help = new cls_help();

$state = "";
if(isset($_POST['state']))
    $state = $_POST['state'];

$query = "SELECT CC_Paese_Estero AS CC, Nome AS paese FROM paesi_esteri_lista ";
$query.= "WHERE Nome LIKE '%".$state."%' ORDER BY Nome";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["action_row"] = 'initialId("state",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"state\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);
//var_dump($result);
//var_dump($state);
//echo $query;
//var_dump($help->getVar("state"));
//var_dump($_POST);
//var_dump(array("belin" => "hahagah"));