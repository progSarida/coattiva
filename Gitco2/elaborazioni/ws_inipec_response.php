<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/headerAjax.php");

include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_Utils.php";

include_once ELAB_PIGNORAMENTI_LAVORO_CLS . "/cls_DefaultTipoUfficiale.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_utils = new cls_Utils();
$cls_db = new cls_db();

$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");
$elaborationId =  $cls_help->getVar('el');

$query = "SELECT UserName, Password, INIPECPasswordExpiration from ini_pec_processing where UserId=".$_SESSION['aut_progr'];
$a_credentials = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$msg="";
$err=null;
if(empty($a_credentials)){
    $msg="Utente inipec non trovato!";
    $err = 1;
}
else if(empty($a_credentials['INIPECPasswordExpiration'])){
    $msg="Scadenza password non settata!";
    $err = 1;
}
else if(date('Y-m-d')>$a_credentials['INIPECPasswordExpiration']) {
    $msg = "Password scaduta, per modificarla premere il lucchetto verde!";
    $err = 1;
}

if(!empty($err)){
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$elaborationId."&msg=".$msg."&error=".$err."' </script>";
    die;
}

$query = "SELECT * FROM ini_pec_request WHERE (EsitoRichiesta='true' OR EsitoRichiesta='OK') "
."AND EsitoFornitura is null AND UserName='".$a_credentials['UserName']."' AND Elaboration_Id=".$elaborationId;
$a_request = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

include_once CLS."/cls_inipec.php";
$ws_inipec = new cls_inipec($a_credentials["UserName"], $a_credentials["Password"]);
$dirPath = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");


$query = "SELECT * FROM elaborations WHERE Id = ".$a_request['Elaboration_Id'];
$a_elaboration = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if(empty($a_elaboration)) {
    $msg = "ELABORAZIONE ".$elaborationId." NON TROVATA!";
    $err = 1;
}

if(!empty($err)){
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=".$err."' </script>";
    die;
}

$query_par =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $a_elaboration['CC'] . "' AND Anno=" . date('Y');
$params_arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par));
if(empty($params_arr)) {
    $msg = "PARAMETRI ANNUALI ".date('Y')." PER IL CODICE CATASTALE ".$a_elaboration['CC']." ASSENTI";
    $err = 1;
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=".$err."' </script>";
    die;
}

$ws_inipec->scarico($dirPath, $a_request['IdRichiesta']);

$response = $ws_inipec->a_check;
$msg = $response['code']." - ".$response['msg'];
if($response['esito']===false){
    $err = 1;
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=".$err."' </script>";
    die;
}
    
$a_pecs = $ws_inipec->getPecList($dirPath);

$whereUtenti = "";
$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
foreach($a_pecs as $a_pec){

    if(empty($a_pec['PEC']))
        continue;

    $query = "UPDATE ini_pec_request_pec SET Pec = '" . $a_pec['PEC'] . "' "
    ."WHERE CodiceFiscale = '" . $a_pec['CF_PI'] . "' AND IdRichiesta='".$a_request['IdRichiesta']."'";
    if (!$cls_db->ExecuteQuery($query)) {
        $cls_db->Rollback();
        $msg = "Errore, impossibile salvare la PEC sulla tabella ini_pec_request_pec!";
        $err = 1;
        echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=".$err."' </script>";
    }

    $query = "SELECT ID, Comune_ID, CC_Comune, PEC FROM utente WHERE Codice_Fiscale = '".$a_pec['CF_PI']."' OR Partita_Iva = '".$a_pec['CF_PI']."'";
    $a_utenti = $cls_db->getResults($cls_db->ExecuteQuery($query));
    foreach($a_utenti as $a_utente){
        if(!empty($a_utente['PEC']) && $a_utente['PEC']!=$a_pec['PEC']){
            $query = "INSERT INTO storico_pec (Utente_Id,Pec,Data_Cambio) 
                    VALUES (".$a_utente['ID'].",'".$a_utente['PEC']."','".date('Y-m-d')."')";

            if (!$cls_db->ExecuteQuery($query)) {
                $cls_db->Rollback();
                $msg = "Errore, impossibile aggiornare lo storico della pec dell'utente ".$a_pec['Comune_ID']."/".$a_pec['CC_Comune']."!";
                $err = 1;
                echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=".$err."' </script>";
                die;
            }
        }

        $query = "UPDATE utente SET PEC = '" . $a_pec['PEC'] . "', InipecLoaded='".date('Y-m-d')."' WHERE ID = " . $a_utente["ID"];
        if (!$cls_db->ExecuteQuery($query)) {
            $cls_db->Rollback();
            $msg = "Errore, impossibile salvare la PEC sulla tabella utente!";
            $err = 1;
            echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=".$err."' </script>";
            die;
        }

        if($whereUtenti=="")
            $whereUtenti.= $a_utente["ID"];
        else
            $whereUtenti.= ", ".$a_utente["ID"];
    }
}



$query = "UPDATE ini_pec_request SET EsitoFornitura = '".$response['code']."' WHERE IdRichiesta='".$a_request['IdRichiesta']."'";
if(!$cls_db->ExecuteQuery($query)) {
    $cls_db->Rollback();
    $msg = "Errore, impossibile aggiornare esito sulla tabella ini_pec_request!";
    $err = 1;
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=".$err."' </script>";
}



if(!empty($whereUtenti)){
    if ($a_elaboration['Document_Type_Id']==22 OR $a_elaboration['Document_Type_Id']==6 OR 
    $a_elaboration['Document_Type_Id']==7 OR $a_elaboration['Document_Type_Id']==8)
    {
        //Prendo il default della PEC per questa elaborazione
        $a_tipo_ufficiale = $cls_db->getArrayLine($cls_db->ExecuteQuery(DefaultTipoUfficiale::ReadQuery($elaborationId)));
        $tipoufficiale = $a_tipo_ufficiale["DefaultPecTipoUfficiale"];
        $tipoufficiale = is_null($tipoufficiale) ? "riscossione" : $tipoufficiale;
        
        //Aggiorna spese atto PEC
        $query = "UPDATE notifica_atto AS NA 
        JOIN pignoramento_generale as PG ON NA.Atto_Notificato_ID=PG.ID
        JOIN partita_tributi as PT ON PG.Partita_ID = PT.ID
        SET PG.Spese_Notifica_Debitore= PG.Spese_Notifica_Debitore-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
        PG.Totale_Spese_Notifica= PG.Totale_Spese_Notifica-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
        PG.Totale_Dovuto= PG.Totale_Dovuto-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
        NA.Spese_Notifica = ".$params_arr['Spese_Pec'].", NA.Printer_Id=1, NA.PrintTypeId=4, NA.Tipo_Ufficiale='$tipoufficiale'
        where PT.Utente_ID IN(".$whereUtenti.") and NA.Tipo_Notifica=\"debitore\" and PG.Elaboration_Id = ".$a_request['Elaboration_Id'];
        $cls_db->ExecuteQuery($query);
        
        $query = "UPDATE notifica_atto AS NA JOIN pignoramento_generale as PG ON NA.Atto_Notificato_ID=PG.ID 
        JOIN pignoramento_presso_terzi as PPT ON PPT.Pignoramento_ID=PG.ID and NA.Utente_ID = PPT.Terzo_ID and (NA.Tipo_Notifica = 'terzo' OR NA.Tipo_Notifica = 'terzi')
        SET PG.Spese_Notifica_Terzi= PG.Spese_Notifica_Terzi-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
        PG.Totale_Spese_Notifica= PG.Totale_Spese_Notifica-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
        PG.Totale_Dovuto= PG.Totale_Dovuto-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
        NA.Spese_Notifica = ".$params_arr['Spese_Pec'].", NA.Printer_Id=1, NA.PrintTypeId=4, NA.Tipo_Ufficiale='$tipoufficiale'
        where PPT.Terzo_ID IN(".$whereUtenti.")  and PG.Elaboration_Id = ".$a_request['Elaboration_Id'];
        
        $cls_db->ExecuteQuery($query);
        

    }
    else
    {
        $query = "UPDATE atto A JOIN elaborations E ON A.Elaboration_Id=E.Id 
        JOIN partita_tributi PT ON PT.ID=A.Partita_ID 
        JOIN utente U ON U.ID=PT.Utente_ID 
        SET A.PrinterId=1, A.PrintTypeId=4, A.Modalita_Stampa='pec', A.Tipo_Ufficiale='riscossione' 
        , Totale_Dovuto=Totale_Dovuto-Spese_Notifica+".$params_arr['Spese_Pec']." , Spese_Notifica=".$params_arr['Spese_Pec'] . "
        WHERE E.Id = ".$a_request['Elaboration_Id']." AND U.ID IN (".$whereUtenti.")";
        $cls_db->ExecuteQuery($query);
    }
}

if(empty($err)){
    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$a_elaboration['CC']."'") );
    $nome_ente = $ente['Denominazione'];

    $doc_type = "";
    
    switch($a_elaboration['Document_Type_Id']){
        case 2:
            $doc_type = "Ingiunzioni";
            break;
        case 3:
            $doc_type = "Solleciti di pagamento";
            break;
        case 4:
            $doc_type = "Avvisi d'intimazione";
            break;
        case 7:
            $doc_type = "Pignoramenti presso datore di lavoro";
            break;
        case 8:
            $doc_type = "Pignoramenti presso banca";
            break;
        case 11:
            $doc_type = "Solleciti pre ingiunzione";
            break;
        case 12:
            $doc_type = "Avvisi di messa in mora";
            break;
        case 22:
            $doc_type = "Preavvisi fermo amministrativo";
            break;
    }

    $storico->insRow('E', "Scaricate richieste INIPEC elaborazione ".$a_elaboration['Description'].": ".$doc_type." ".$ente['Denominazione']."[".$a_elaboration['CC']."]");
}

$cls_db->End_Transaction();

echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$a_request['Elaboration_Id']."&msg=".$msg."&error=0' </script>";