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

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_Utils = new cls_Utils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$partita_ID = $cls_help->getVar('partita');

$flag_blocco = $cls_help->getVar('flag_blocco');
$motivo_blocco = $cls_help->getVar('motivo_blocco');
$note_blocco = $cls_help->getVar('note_blocco');
$flag_maggiorazione = $cls_help->getVar('flag_maggiorazione');
$flag_diritto_riscossione = $cls_help->getVar('flag_diritto_riscossione');
$flag_annullamento = $cls_help->getVar('flag_annullamento');
$flag_sgravio = $cls_help->getVar('flag_sgravio');

//$arrDocTypeId = $cls_help->getVar('DocumentTypeId');
//$arrText = $cls_help->getVar('TextValue');
$arrCountEl = $cls_help->getVar('CountGlobal');

if($flag_annullamento=="si" || $flag_sgravio == "si") $flag_blocco = "si";

if($flag_blocco=="si")
{
    $partitaArray["Motivo_Blocco"] = $motivo_blocco;
    $partitaArray["Note_Blocco"] = $note_blocco;
}
else
{
    $flag_blocco = "";
    $partitaArray["Motivo_Blocco"] = null;
    $partitaArray["Note_Blocco"] = "";
}

$partitaArray["Flag_Blocco_Coazione"] = $flag_blocco;

if($flag_maggiorazione!="si")
    $flag_maggiorazione = "";

if($flag_diritto_riscossione!="si")
    $flag_diritto_riscossione = "";

if($flag_annullamento == "") $flag_annullamento = "no";
if($flag_sgravio == "") $flag_sgravio = "no";

$partitaArray["Flag_Annullamento"] = $flag_annullamento;
$partitaArray["Flag_Sgravio"] = $flag_sgravio;
$partitaArray["Flag_Blocco_Maggiorazioni"] = $flag_maggiorazione;
$partitaArray["Flag_Blocco_Diritto_Riscossione"] = $flag_diritto_riscossione;


if($flag_sgravio=="si") $partitaArray["Sgravio_Activation_Date"] = date("Y-m-d");
else $partitaArray["Sgravio_Activation_Date"] = null;
if($flag_annullamento=="si") $partitaArray["Annullamento_Activation_Date"] = date("Y-m-d");
else $partitaArray["Annullamento_Activation_Date"] = null;

$error = 0;
$msg = "";

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

$query = "DELETE FROM sgravi_documenti WHERE Partita_ID = ".$partita_ID;
if(!$cls_db->ExecuteQuery($query))
{

    $error = 1;
    $msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
    $cls_db->Rollback();
    header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
    die;
}

for($i=0; $i<count($arrCountEl); $i++){

    $save = array();
    $save["DocumentTypeId"] = $cls_help->getVar('DocumentTypeId_'.$arrCountEl[$i]);
    $save["Text"] = $cls_help->getVar('TextValue_'.$arrCountEl[$i]);
    $save["Partita_ID"] = $partita_ID;

    $a_paramsSgraviDoc = $cls_Utils->GetObjectQuery($save,"sgravi_documenti");
    if(!$cls_db->DbSave($a_paramsSgraviDoc))
    {

        $error = 1;
        $msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
        $cls_db->Rollback();
        header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
        die;
    }else $msg = "Dati aggiornati correttamente";
}

$a_paramsPartitaTr = $cls_Utils->GetObjectQuery($partitaArray,"partita_tributi",array("ID" => $partita_ID));

if(!$cls_db->DbSave($a_paramsPartitaTr))
{

    $error = 1;
    $msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
    $cls_db->Rollback();
    header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
    die;
}else $msg = "Dati aggiornati correttamente";

$cls_db->End_Transaction();

header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
die;