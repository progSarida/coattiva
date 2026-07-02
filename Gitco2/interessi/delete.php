<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');
$cls_help = new cls_help();
$cls_db = new cls_db();

$id = $cls_help->getVar("id");
$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

$msg = "Dati eliminati correttamente!";
$error = 0;
$nome_ente = "per tutti gli enti ";
$storico_query = "SELECT LP.*, EG.Denominazione AS Denominazione FROM lockup_periods AS LP LEFT JOIN enti_gestiti AS EG ON EG.CC = LP.CC WHERE LP.Id = ".$id;
$result = $cls_db->getResults($cls_db->ExecuteQuery($storico_query));
if($result[0]['CC'] != '*****'){
    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$result[0]['CC']."'"));
    $nome_ente = " per ente ".$ente['Denominazione']."[".$result[0]['CC']."]";
}


$query = "DELETE FROM lockup_periods WHERE Id = ".$id;

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if(!$cls_db->ExecuteQuery($query)){
    $cls_db->Rollback();
    $msg = "Cancellazzione fallita! Errore impossibile eliminare i dati";
    $error = 1;
}

$cls_db->End_Transaction();

if($error == 0)
    $storico->insRow('D', "Eliminato periodo blocco interessi".$nome_ente);

header("Location: ".WEB_ROOT."/interessi/interessi.php?c=".$c."&a=".$a."&msg=".$msg."&error=".$error);