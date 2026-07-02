<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_Utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB",false);

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$id = $cls_help->getVar("id");
$cc = $cls_help->getVar("cc");
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$cc."'") );
if($cc != '*****')
    $nome_ente = " per ente ".$ente['Denominazione']."[".$cc."]";
else
    $nome_ente = " per tutti gli enti";
$query = "SELECT * FROM lockup_types WHERE Id = ".$cls_help->getVar("blockType");
$row_type = $cls_db->getResults($cls_db->ExecuteQuery($query));
$type = $interestType[0]["Name"];

$save = new stdClass();
$save->CC = $cls_help->getVar("cc");
$save->Lockup_Type_Id = $cls_help->getVar("blockType");
$save->Start_Date = $cls_date->GetDateDB($cls_help->getVar("start_date"),"IT");
$save->End_Date = $cls_date->GetDateDB($cls_help->getVar("end_date"),"IT");
$save->Name = $cls_help->getVar("name");
$save->Description = $cls_help->getVar("description");

$msg = "Dati aggiornati correttamente!";
$error = 0;

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if(!$cls_db->DbSave($cls_Utils->GetObjectQuery($save,"lockup_periods",array("Id" => $id)))){
    $cls_db->Rollback();
    $msg = "Aggiornamento fallito! Errore nell'inserimento dati";
    $error = 1;
}

$cls_db->End_Transaction();

if($error == 0)
    $storico->insRow('U', "Modificato periodo blocco interessi".$nome_ente);

header("Location: ".WEB_ROOT."/interessi/interessi.php?c=".$c."&a=".$a."&msg=".$msg."&error=".$error);