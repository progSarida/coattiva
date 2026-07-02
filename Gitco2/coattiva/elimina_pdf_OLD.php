<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_LOG.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_Utils = new cls_Utils();
$log = new LOG();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$Partita_ID = $cls_help->getVar('Partita_ID');
$Tipo = $cls_help->getVar('tipo');
$calling_page = $cls_help->getVar('calling_page');
$last_act = $cls_help->getVar("last_act");

$tipoEst = "";
if($Tipo == 1) $tipoEst = "sgravio";
else $tipoEst = "annullamento";

$error = 0;
$msg = "File eliminati e DB aggiornato correttamente";

try{
    $query = "SELECT File_1, File_2 FROM sgravio WHERE Partita_ID = ".$Partita_ID." AND Tipo = ".$Tipo;
    $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

    if(file_exists($result["File_1"])) if($result["File_1"] != null) unlink($result["File_1"]);
    else {
        $msg = "Il file File_1 di " . $tipoEst . " non trovato";
        $error = 2;
        $log->warning("Il file File_1 di " . $tipoEst . " non trovato");
    }
    if(file_exists($result["File_2"])) if($result["File_2"] != null) unlink($result["File_2"]);
    else {
        $msg = "Il file File_2 di ".$tipoEst." non trovato";
        $error = 2;
        $log->warning("Il file File_2 di ".$tipoEst." non trovato");
    }

    $query = "UPDATE sgravio SET File_1 = NULL, File_2 = NULL, Data_Stampa = NULL WHERE Partita_ID = ".$Partita_ID." AND Tipo = ".$Tipo;

    if(!$cls_db->ExecuteQuery($query)){
        $error = 1;
        $msg = "Aggiornamento DB fallito";
        $log->error("Aggiornamento DB fallito");
    }
}
catch(Exception $ex){
    $log->error($ex->getMessage());
}

echo "<script>window.location.href = '".WEB_ROOT."/coattiva/annulamento_sgravi.php?c={$c}&a={$a}&calling_page={$calling_page}&last_act={$last_act}&partita={$Partita_ID}&p={$p}&msg={$msg}&error={$error}';</script>";
die;
