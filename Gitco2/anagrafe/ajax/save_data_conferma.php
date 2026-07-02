<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_date = new cls_DateTimeI("DB",false);

$tipo = $cls_help->getVar("tipo");
$utente_id = $cls_help->getVar("utente_id");
$data = $cls_date->GetDateDB($cls_help->getVar("data_conferma"),"IT");

//echo $data." ".$utente_id." ".$tipo;

$query = "UPDATE indirizzo SET Data_Conferma_Indirizzo = '".$data."' WHERE Utente_ID = '".$utente_id."' AND Tipo = '".$tipo."'";
if($cls_db->ExecuteQuery($query)) echo "OK";
else echo "ERROR";
die;