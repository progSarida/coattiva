<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//include_once WEB_ROOT."/search_modal/startAjax.php";
//Classe per cambio formato data
//include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$help = new cls_help();

$query = "SELECT * FROM codice_tributo ORDER BY Codice_Tributo";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

echo json_encode($result);

?>
