<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_storico.php";

$storico = new storico('storicoComunicazioniEnte','9');
$cls_db = new cls_db();
$cls_help = new cls_help();

$invia_submit = $cls_help->getVar("invia_submit");
$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");

$arrData = $cls_help->getVar("date");
$arrImp = $cls_help->getVar("import");
$arrCan = $cls_help->getVar("canal");

$ente_ = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente_ = $ente_['Denominazione'];

$error = 0;
$msg = "";

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if($invia_submit == "Delete"){
    $query = "DELETE FROM riversamenti WHERE CC = '".$c."' AND year = '".$a."'";
    $check = $cls_db->ExecuteQuery($query);

    if($check===false){
        $cls_db->Rollback();
        $error = 1;
        $msg = "Errore! Impossibile eliminare i dati!";
    }
    else{
        $storico->insRow('D', "Eliminati tutti riversamenti ente ".$nome_ente_);
        $msg = "Dati eliminati correttamente!";
    }
}
else{

    $count = count($arrData);

    if(count($arrCan) != $count || count($arrImp) != $count)
    {
        $error = 1;
        $msg = "Errore! dati compilati erroneamente! gli array di ritorno non coincidono!";
    }
    else{
        $query = "DELETE FROM riversamenti WHERE CC = '".$c."' AND year = '".$a."'";
        $check = $cls_db->ExecuteQuery($query);

        if($check===false){
            $cls_db->Rollback();
            $error = 1;
            $msg = "Errore! Impossibile eliminare i dati prima dell'aggiornamento!";
        }

        $query = "INSERT INTO riversamenti (CC,year,importo,data_versamento,canale) VALUES ";

        for($i=0; $i < $count; $i++){
            if($i==0) $query .= " ('".$c."','".$a."','".str_replace(",",".",$arrImp[$i])."','".$arrData[$i]."','".$arrCan[$i]."') ";
            else $query .= ", ('".$c."','".$a."','".str_replace(",",".",$arrImp[$i])."','".$arrData[$i]."','".$arrCan[$i]."') ";
        }

        $check = $cls_db->ExecuteQuery($query);

        if($check === false){
            $error = 1;
            $msg = "Errore! Impossibile aggiornare/inserire dati!";
            $cls_db->Rollback();
        }
        else{
            $storico->insRow('U', "Aggiornati riversamenti ente ".$nome_ente_."[".$c."]");
            $msg = "Dati inseriti/aggiornati correttamente!";
        }
    }

}

$cls_db->End_Transaction();

header("Location: riversamenti.php?c=".$c."&a=".$a."&error=".$error."&msg=".$msg);