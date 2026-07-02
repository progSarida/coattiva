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
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_storico.php";
include_once(CLS."/cls_GestionePartita.php");

$cls_partita = new cls_GP();
$storico = new storico('storicoRuolo','3');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_Utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB",false);

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$partita_ID = $cls_help->getVar('partita');

$flag_blocco = $cls_help->getVar('flag_blocco');
$motivo_blocco = $cls_help->getVar('motivo_blocco');
$note_blocco = $cls_help->getVar('note_blocco');
$flag_maggiorazione = $cls_help->getVar('flag_maggiorazione');
$flag_diritto_riscossione = $cls_help->getVar('flag_diritto_riscossione');
//$flag_annullamento = $cls_help->getVar('flag_annullamento');
$flag_sgravio = $cls_help->getVar('flag_sgravio');
$sgravio_date = $cls_help->getVar('sgravio_activation_date');
$annullamento_date = $cls_date->GetDateDB($cls_help->getVar('annullamento_activation_date'),"IT");
$arrCountEl = $cls_help->getVar('CountGlobal');
$pageCalled = $cls_help->getVar('pageCalled');
$SalvataggioCancellazione = $cls_help->getVar("invia_submit");

// Recupero dati ente
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];	

// Recupero dati partita e utente
$partita_query = "SELECT PT.Comune_ID AS Rif_P, PT.CC, T.Info_Cartella AS Info, EG.Denominazione AS Ente, ";
$partita_query.= "IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(Cognome,''),' ',COALESCE(Nome,''))) as Utente, U.Comune_ID AS Rif_U FROM partita_tributi AS PT ";
$partita_query.= "LEFT JOIN tributo AS T ON PT.ID = T.Partita_ID ";
$partita_query.= "LEFT JOIN utente AS U ON PT.Utente_ID = U.ID ";
$partita_query.= "LEFT JOIN enti_gestiti AS EG ON PT.CC = EG.CC ";
$partita_query.= "WHERE PT.ID = ".$partita_ID;

$info = $cls_db->getResults($cls_db->ExecuteQuery($partita_query));

/*
$partita = $cls_partita->getDataPartita($partita_ID, $c, $a);							//
$tributo = isset($partita["Tributo"])?$partita["Tributo"]:null;							//

// Recupero dati utente
$user_query = "SELECT IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(COGNOME,''),' ',COALESCE(Nome,''))) as U, Comune_ID FROM utente where ID = '".$partita["Utente_ID"]."'";
$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($user_query),"utente");
$msg_utente = $utente["U"];
*/

// Controllo nuovo inserimento
if ($pageCalled == "sgravi" || $pageCalled == "sgravi_1"){
    $check_query = "SELECT * FROM sgravio WHERE Partita_ID = " . $partita_ID. " AND Tipo = 1";
    $check_result = $cls_db->ExecuteQuery($check_query);
}
if ($pageCalled == "annullamento" || $pageCalled == "annullamento_1"){
    $check_query = "SELECT * FROM sgravio WHERE Partita_ID = " . $partita_ID. " AND Tipo = 2";
    $check_result = $cls_db->ExecuteQuery($check_query);
}

if($SalvataggioCancellazione == "Delete_Annullamento"){

    $error = 0;
    $msg = "";

    $save = new stdClass();
    $save->Flag_Blocco_Coazione = null;
    $save->Flag_Annullamento = null;
    $save->Annullamento_Activation_Date = null;
    $save->Note_Blocco = null;
    $save->Motivo_Blocco = null;
    $save->Printed_Annull = "no";
    $save->print_annull_date = null;

    $a_paramsPartitaTr = $cls_Utils->GetObjectQuery($save,"partita_tributi",array("ID" => $partita_ID));
    if(!$cls_db->DbSave($a_paramsPartitaTr))
    {

        $error = 1;
        $msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
        //$cls_db->Rollback();
        header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
        die;
    }else $msg = "Dati partita aggiornati correttamente. Annullamento eliminato.";


    $query = "SELECT File_1, File_2 FROM sgravio WHERE Partita_ID = " . $partita_ID. " AND Tipo = 2";
    $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "sgravio");

    if($result["File_1"] != null && $result["File_2"] != null){
        if (file_exists($result["File_1"])) {
            unlink($result["File_1"]);
        }
        else {
            $error = 2;
            $msg = "Impossibile eliminare file 1";
        }
        if (file_exists($result["File_2"])) {
            unlink($result["File_2"]);
        }
        else {
            $error = 2;
            $msg = "Impossibile eliminare file 2";
        }
    }
    

    $query = "DELETE FROM sgravio WHERE Partita_ID = ". $partita_ID ." AND Tipo = 2";
    if(!$cls_db->ExecuteQuery($query)){
        $error = 1;
        $msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
    }
    if($error == 0)
        $storico->insRow('D', "Eliminato annullamento partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
    header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
    die;
}
else if($SalvataggioCancellazione == "Delete_Sgravio"){

    $error = 0;
    $msg = "";

    $save = new stdClass();
    $save->Flag_Sgravio = null;
    $save->Sgravio_Activation_Date = null;
    $save->Note_Blocco_Sgravio = null;
    $save->Printed_Sgravio = "no";
    $save->print_sgravio_date = null;

    $a_paramsPartitaTr = $cls_Utils->GetObjectQuery($save,"partita_tributi",array("ID" => $partita_ID));
    if(!$cls_db->DbSave($a_paramsPartitaTr))
    {

        $error = 1;
        $msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
        //$cls_db->Rollback();
        header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
        die;
    }else $msg = "Dati partita aggiornati correttamente. Sgravio eliminato.";

    $query = "SELECT File_1, File_2 FROM sgravio WHERE Partita_ID = " . $partita_ID. " AND Tipo = 1";
    $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "sgravio");

    if($result["File_1"] != null && $result["File_2"] != null){
        if (file_exists($result["File_1"])) {
            unlink($result["File_1"]);
        }
        else {
            $error = 2;
            $msg = "Impossibile eliminare file 1";
        }
        if (file_exists($result["File_2"])) {
            unlink($result["File_2"]);
        }
        else {
            $error = 2;
            $msg = "Impossibile eliminare file 2";
        }
    }
    

    $query = "DELETE FROM sgravio WHERE Partita_ID = ". $partita_ID ." AND Tipo = 1";
    if(!$cls_db->ExecuteQuery($query)){
        $error = 1;
        $msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
    }

    $query = "DELETE FROM sgravi_documenti WHERE Partita_ID = " . $partita_ID;
    if (!$cls_db->ExecuteQuery($query)) {

        $error = 1;
        $msg = "Errore impossibile eliminare i dati. " . $cls_db->GetError();
        die;
    }
    if($error == 0)
        $storico->insRow('D', "Eliminato sgravio partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
    header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
    die;
}

$query = "SELECT PT.*, R.Data_Fornitura FROM partita_tributi AS PT LEFT JOIN ruolo AS R ON R.ID = PT.Ruolo_ID WHERE PT.ID = ".$partita_ID;
$RES = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi");

$flagSgravioAttivazioneAnnull = false;

if($motivo_blocco!="0" && $motivo_blocco!="") {

    $flagSgravioAttivazioneAnnull = true;
    $partitaArray["Sgravio_Activation_Date"] = $RES["Data_Fornitura"];
    $partitaArray["Sgravio_Save_Activation_Date"] = date("Y-m-d");
    $flag_blocco = "si";
    $flag_sgravio = "si";
    $flag_annullamento = "si";
}
else {
    $flag_annullamento = "no";
    //$motivo_blocco = null;
    $partitaArray["Note_Blocco"] = "";
    $annullamento_date = null;
    $flag_blocco = null;
}

$partitaArray["Flag_Blocco_Coazione"] = $flag_blocco;

if($pageCalled == "annullamento" || $pageCalled == "annullamento_1") {
    if ($motivo_blocco == "0" || $motivo_blocco == ""){
        $query = "DELETE FROM sgravio WHERE Partita_ID=".$partita_ID." AND Tipo=2";
        $cls_db->ExecuteQuery($query);
        $flag_annullamento = "no";
    }
    else {

        if($RES["Flag_Sgravio"]==null || $RES["Flag_Sgravio"]=="no") {
            $partitaArray["Sgravio_Activation_Date"] = $RES["Data_Fornitura"];
            $partitaArray["Sgravio_Save_Activation_Date"] = date("Y-m-d");
            $flag_sgravio = "si";
        }

        $partitaArray["Note_Blocco"] = $cls_help->getVar("note_blocco_annull");
        $flag_annullamento = "si";

        $query = "SELECT * FROM sgravio WHERE Partita_ID=".$partita_ID." AND Tipo=2";
        $a_annullamento = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
        if(is_null($a_annullamento)){
            $a_dbParams = array(
                'table' => 'sgravio',
                'fields'=> array(
                    array(  'name' => 'Partita_ID',             'type' => 'int',        'value' => $partita_ID                  ),
                    array(  'name' => 'CC',                     'type' => 'string',     'value' => $c                           ),
                    array(  'name' => 'Tipo',                   'type' => 'int',        'value' => 2                            ),
                )
            );
            $annullamento_id = $cls_db->DbSave($a_dbParams);
        }
    }
}



if($pageCalled == "sgravi" || $pageCalled == "sgravi_1") {
    if ($flag_sgravio == "" || $flag_sgravio == null || $flag_sgravio == "no") {
        $flag_sgravio = "no";
        $partitaArray["Note_Blocco_Sgravio"] = "";
    }
    else $partitaArray["Note_Blocco_Sgravio"] = $cls_help->getVar("note_blocco");
}

$partitaArray["Flag_Annullamento"] = $flag_annullamento;
$partitaArray["Flag_Sgravio"] = $flag_sgravio;

if($pageCalled == "sgravi" || $pageCalled == "sgravi_1")
{
    if($flag_sgravio == "no"){
        $partitaArray["Sgravio_Activation_Date"] = null;
        $partitaArray["Sgravio_Save_Activation_Date"] = null;
    }
    else{
        if($sgravio_date == null || $sgravio_date == "") {
            $partitaArray["Sgravio_Activation_Date"] = $RES["Data_Fornitura"];
            $partitaArray["Sgravio_Save_Activation_Date"] = date("Y-m-d");
        }
        else $partitaArray["Sgravio_Activation_Date"] = $sgravio_date;
    }
}

if($pageCalled == "annullamento" || $pageCalled == "annullamento_1")
{
    if($motivo_blocco == "0" || $motivo_blocco == ""){
        $partitaArray["Annullamento_Activation_Date"] = null;
        $partitaArray["Motivo_Blocco"] = null;
    }
    else{
        if($annullamento_date == null || $annullamento_date == ""){
            $partitaArray["Annullamento_Activation_Date"] = date("Y-m-d");
        }
        else $partitaArray["Annullamento_Activation_Date"] = $annullamento_date;
        $partitaArray["Motivo_Blocco"] = $motivo_blocco;
    }
}



$error = 0;
$msg = "";

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if((($pageCalled == "sgravi" || $pageCalled == "sgravi_1") && $flag_sgravio == "si") || $flagSgravioAttivazioneAnnull) {
    // Sgravi manuali Tipo=1 disabilitati: tutti gli sgravi nascono dalla
    // elaborazione massiva (Gitco2/sgravi/elaborazione_sgravi.php).
    // Anche il flusso "annullamento con motivo_blocco" che attiverebbe
    // $flagSgravioAttivazioneAnnull viene bloccato perche' creerebbe un
    // sgravio Tipo=1. Per gestire blocchi/note utilizzare l'elaborazione
    // massiva o la cancellazione massiva (cls_Cancellazione_Sgravi).
    $error = 1;
    $msg = "Discarichi manuali disabilitati. Usare l'elaborazione massiva.";
    $cls_db->Rollback();
    header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg=" . urlencode($msg) . "&pageCalled=" . urlencode($pageCalled));
    die;

    // CODICE MORTO sotto (mantenuto per riferimento porting Laravel)
    $query = "SELECT * FROM sgravio WHERE Partita_ID=".$partita_ID." AND Tipo=1";
    $a_sgravio = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    if(is_null($a_sgravio)){
        $a_dbParams = array(
            'table' => 'sgravio',
            'fields'=> array(
                array(  'name' => 'Partita_ID',             'type' => 'int',        'value' => $partita_ID   ),
                array(  'name' => 'CC',                     'type' => 'string',     'value' => $c                          ),
                array(  'name' => 'Tipo',                   'type' => 'int',        'value' => 1                           ),
            )
        );
        $sgravio_id = $cls_db->DbSave($a_dbParams);
    }
    else
        $sgravio_id = $a_sgravio['ID'];

    $query = "DELETE FROM sgravi_documenti WHERE Partita_ID = " . $partita_ID;

    if (!$cls_db->ExecuteQuery($query)) {

        $error = 1;
        $msg = "Errore impossibile eliminare i dati. " . $cls_db->GetError();
        $cls_db->Rollback();
        header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
        die;
    }

    for ($i = 0; $i < count($arrCountEl); $i++) {

        $save = array();
        $save["DocumentTypeId"] = $cls_help->getVar('DocumentTypeId_' . $arrCountEl[$i]);
        $save["Text"] = $cls_help->getVar('TextValue_' . $arrCountEl[$i]);
        $save["DocumentId"] = $cls_help->getVar('DocumentId_' . $arrCountEl[$i]);
        $save["Partita_ID"] = $partita_ID;
        $save["Sgravio_ID"] = $sgravio_id;
        //var_dump($save);

        $a_paramsSgraviDoc = $cls_Utils->GetObjectQuery($save, "sgravi_documenti");
        if (!$cls_db->DbSave($a_paramsSgraviDoc)) {

            $error = 1;
            $msg = "Errore impossibile aggiornare i dati. " . $cls_db->GetError();
            $cls_db->Rollback();
            header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
            die;
        } else $msg = "Dati aggiornati correttamente";
    }
}
else if($pageCalled == "sgravi" || $pageCalled == "sgravi_1"){
    $query = "DELETE FROM sgravio WHERE Partita_ID=".$partita_ID." AND Tipo=1";
    $cls_db->ExecuteQuery($query);

    $query = "DELETE FROM sgravi_documenti WHERE Partita_ID = " . $partita_ID;

    if (!$cls_db->ExecuteQuery($query)) {

        $error = 1;
        $msg = "Errore impossibile eliminare i dati. " . $cls_db->GetError();
        $cls_db->Rollback();
        header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
        die;
    }

}


$a_paramsPartitaTr = $cls_Utils->GetObjectQuery($partitaArray,"partita_tributi",array("ID" => $partita_ID));

if(!$cls_db->DbSave($a_paramsPartitaTr)){

    $error = 1;
    $msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
    $cls_db->Rollback();
    header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
    die;
}else{
    $msg = "Dati aggiornati correttamente";
}



$cls_db->End_Transaction();
//die;
if($error == 0) {
    if($check_result->num_rows == 0){
        if ($pageCalled == "sgravi" || $pageCalled == "sgravi_1")
            $storico->insRow('I', "Inserito sgravio partita " .$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
        if ($pageCalled == "annullamento" || $pageCalled == "annullamento_1")
            $storico->insRow('I', "Inserito annullamento partita " .$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
    }
    else{
        if ($pageCalled == "sgravi" || $pageCalled == "sgravi_1")
            $storico->insRow('U', "Modificato sgravio partita " .$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
        if ($pageCalled == "annullamento" || $pageCalled == "annullamento_1")
            $storico->insRow('U', "Modificato annullamento partita " .$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
    }
}

header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}&pageCalled={$cls_help->getVar("pageCalled")}");
die;