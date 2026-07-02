<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_Utils.php");

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();

$err = 0;
$msg = "";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');

$oggetto = $cls_help->getVar('oggetto');
$sottotitolo = $cls_help->getVar('sottotitolo');
$primoTesto = $cls_help->getVar('primoTesto');
$pagamento = $cls_help->getVar('pagamento');
$coazione = $cls_help->getVar('coazione');
$coazione1 = $cls_help->getVar('primoCasoCoazione');
$coazione2 = $cls_help->getVar('secondoCasoCoazione');
$coazione3 = $cls_help->getVar('terzoCasoCoazione');
$coazione4 = $cls_help->getVar('quartoCasoCoazione');
$datiGestore = $cls_help->getVar('datiGestore');
$rateizzazione = $cls_help->getVar('rateizzazione');
$alternativa = $cls_help->getVar('alternativa');
$informativa = $cls_help->getVar('informativa');
$saluti = $cls_help->getVar('saluti');
$primoResp = $cls_help->getVar('primoResponsabile');
$primaFirma = $cls_help->getVar('primaFirma');
$secondoResp = $cls_help->getVar('secondoResponsabile');
$secondaFirma = $cls_help->getVar('secondaFirma');


if ($invia == "Salva")
{

    $query = "SELECT * FROM parametri_testo_sollecito_ingiunzione WHERE 1=2";
    $myParametroAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_testo_sollecito_ingiunzione");
	//$myParametroAtto = new parametri_testo_sollecito_ingiunzione(NULL);
	
	$myParametroAtto->ID = NULL;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Oggetto = $oggetto;
	$myParametroAtto->Sottotitolo = $sottotitolo;
	$myParametroAtto->Primo_Testo = $primoTesto;
	$myParametroAtto->Pagamento= $pagamento;
	$myParametroAtto->Coazione= $coazione;
	$myParametroAtto->Coazione_Caso_1= $coazione1;
	$myParametroAtto->Coazione_Caso_2= $coazione2;
	$myParametroAtto->Coazione_Caso_3= $coazione3;
	$myParametroAtto->Coazione_Caso_4= $coazione4;
	$myParametroAtto->Dati_Gestore= $datiGestore;
	$myParametroAtto->Rateizzazione= $rateizzazione;
	$myParametroAtto->Alternativa= $alternativa;
	$myParametroAtto->Informativa= $informativa;
	$myParametroAtto->Saluti= $saluti;
	$myParametroAtto->Primo_Responsabile= $primoResp;
	$myParametroAtto->Nome_Primo_Responsabile= $primaFirma;
	$myParametroAtto->Secondo_Responsabile= $secondoResp;
	$myParametroAtto->Nome_Secondo_Responsabile= $secondaFirma;


    $queryCerca = "SELECT ID FROM parametri_testo_sollecito_ingiunzione WHERE CC='".$myParametroAtto->CC."' AND Data_Creazione_Parametri = '" . $myParametroAtto->Data_Creazione_Parametri . "' ";
    $result = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_testo_sollecito_ingiunzione");

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    if($result->ID == null) {
        $obj = $cls_utils->GetObjectQuery((array)$myParametroAtto,"parametri_testo_sollecito_ingiunzione");
        if($cls_db->DbSave($obj))
        {
            $msg = "Dati inseriti correttamente";
        }
        else{
            $cls_db->Rollback();
            $msg = "Errore nell'inserimento. Dati non inseriti";
            $err = 1;
        }
    }
    else{
        $obj = $cls_utils->GetObjectQuery((array)$myParametroAtto,"parametri_testo_sollecito_ingiunzione",array("ID" => $result->ID));
        if($cls_db->DbSave($obj))
        {
            $msg = "Dati aggiornati correttamente";
        }
        else{
            $cls_db->Rollback();
            $msg = "Errore nell'aggiornamento. Dati non aggiornarti";
            $err = 1;
        }
    }

    $cls_db->End_Transaction();
    header("Location: testo_sollecito_ingiunzione.php?c={$c}&a={$a}&error={$err}&msg={$msg}");
}
else
{
    $err = 2;
    $msg = "Warning, tipo salvataggio non conforme. Query non eseguita";
    header("Location: testo_sollecito_ingiunzione.php?c={$c}&a={$a}&error={$err}&msg={$msg}");
}
?>