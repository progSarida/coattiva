<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_paramUtils.php";
include_once CLS . "/cls_storico.php";	

$storico = new storico('storicoParametri','8');
$cls_help = new cls_help();
$cls_db = new cls_db();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');

$ID_magg = $cls_help->getVar('magg_id');

$error = 0;
$msg = "";
$action = "";
$storico_msg = "";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();


if($invia == "Insert")
{
    $a_magg = array(
        "Percentuale" => $cls_help->getVar('percentuale'),
        "CC" => $c,
        "Credito_Minimo" => str_replace(",",".",$cls_help->getVar("credito_minimo")),
        "Credito_Massimo" => str_replace(",",".",$cls_help->getVar("credito_massimo")),
    );

    $ID_magg = $cls_db->DbSave($cls_db->GetObjectQuery("coefficiente_coazione", $a_magg, $cls_db->getColumnDataTypes("coefficiente_coazione")));

    if(!$ID_magg)
    {
        $cls_db->Rollback();
        $error = 1;
        $msg = "Errore, impossibile inserire i dati";
    }
    else{
        $msg= "Dati inseriti correttamente";
        $action = "I";
        $storico_msg = "Inseriti paramentri gestione maggiorazione da ".$cls_help->getVar('credito_minimo')." a ".$cls_help->getVar('credito_massimo');
    }

    $cls_db->End_Transaction();

}

if($invia == "Update")
{
    $a_magg = array(
        "Percentuale" => $cls_help->getVar('percentuale'),
        "CC" => $c,
        "Credito_Minimo" => str_replace(",",".",$cls_help->getVar("credito_minimo")),
        "Credito_Massimo" => str_replace(",",".",$cls_help->getVar("credito_massimo")),
    );

    $a_where = array(
        "ID" => $ID_magg
    );

    $check = $cls_db->DbSave($cls_db->GetObjectQuery("coefficiente_coazione", $a_magg, $cls_db->getColumnDataTypes("coefficiente_coazione"),$a_where));

    if(!$check)
    {
        $cls_db->Rollback();
        $error = 1;
        $msg = "Errore, impossibile aggiornare i dati";
    }else{
        $msg= "Dati aggiornati correttamente";
        $action = "U";
        $storico_msg = "Modificati paramentri gestione maggiorazione da ".$cls_help->getVar('credito_minimo')." a ".$cls_help->getVar('credito_massimo');
    }

    $cls_db->End_Transaction();

}

if( $invia == "Delete" && $ID_magg!="0")
{

    if(!$cls_db->Delete("coefficiente_coazione","ID = {$ID_magg}"))
    {
        $cls_db->Rollback();
        $error = 1;
        $msg = "Errore impossibile eliminare i dati";
    }
    else
    {
        $ID_magg = 0;
        $msg = "Dati eliminati con successo";
        $action = "D";
        $storico_msg = "Eliminati paramentri gestione maggiorazione da ".$cls_help->getVar('credito_minimo')." a ".$cls_help->getVar('credito_massimo');
    }

    $cls_db->End_Transaction();

}
else{
    if($ID_magg=="0")
    {
        $error = 2;
        $msg = "Ufficio inesistente";
    }
}

if($error == 0)
    $storico->insRow($action, $storico_msg." ente ".$nome_ente."[".$c."]");

header("Location: par_maggiorazioni_coazione.php?&c={$c}&a={$a}&id_magg={$ID_magg}&error={$error}&msg={$msg}");
?>

