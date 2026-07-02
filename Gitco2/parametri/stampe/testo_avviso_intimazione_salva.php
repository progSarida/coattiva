<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include CLS."/cls_help.php";
include CLS."/cls_db.php";
include CLS."/cls_Utils.php";

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();

$error = 0;
$msg = "";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');

$titoloIngiunzione = $cls_help->getVar('titoloIngiunzione');
$sottotitoloIngiunzione = $cls_help->getVar('sottotitoloIngiunzione');
$primoTesto = $cls_help->getVar('primoTesto');
$premessoTesto = $cls_help->getVar('premessoTesto');
$secondoTesto = $cls_help->getVar('secondoTesto');
$terzoTesto = $cls_help->getVar('terzoTesto');
$intima = $cls_help->getVar('intima');
$intimaTesto = $cls_help->getVar('intimaTesto');
$intimaCaso1 = $cls_help->getVar('intimaCaso1');
$intimaCaso2 = $cls_help->getVar('intimaCaso2');
$intimaCaso3 = $cls_help->getVar('intimaCaso3');
$intimaVersamento = $cls_help->getVar('intimaVersamento');
$infoTesto = $cls_help->getVar('infoTesto');
$finaleTesto = $cls_help->getVar('finaleTesto');
$opposizione = $cls_help->getVar('opposizione');
$opposizioneTesto = $cls_help->getVar('opposizioneTesto');
$qual_firma1 = $cls_help->getVar('qualifica_firma_1');
$firma1 = $cls_help->getVar('firma_1');
$qual_firma2 = $cls_help->getVar('qualifica_firma_2');
$firma2 = $cls_help->getVar('firma_2');
$modalitaFirma = $cls_help->getVar('modalitaFirma');

$IntestazioneUffRiscossione = $cls_help->getVar('IntestazioneUffRiscossione');
$UffRiscossione = $cls_help->getVar('UffRiscossione');
$IntestazioneUffGiudiziario = $cls_help->getVar('IntestazioneUffGiudiziario');
$SottoIntestazioneUffGiudiziario = $cls_help->getVar('SottoIntestazioneUffGiudiziario');
$UffGiudiziario = $cls_help->getVar('UffGiudiziario');

if ($invia == "Salva")
{

    $query = "SELECT ID FROM parametri_atto_intimazione_ingiunzione 
					WHERE CC = '" . $c . "' AND 
					Data_Creazione_Parametri = '". date("Y-m-d") . "'  
					ORDER BY Data_Creazione_Parametri, ID DESC";

    $ID = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"parametri_atto_intimazione_ingiunzione")["ID"];
	
	$myParametriAtto = new stdClass();
	$myParametriAtto->CC = $c;
	$myParametriAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametriAtto->Titolo_Ingiunzione = $titoloIngiunzione;
	$myParametriAtto->Sottotitolo_Ingiunzione = $sottotitoloIngiunzione;
	$myParametriAtto->Primo_Testo = $primoTesto;
	$myParametriAtto->Premesso_Testo = $premessoTesto;
	$myParametriAtto->Secondo_Testo = $secondoTesto;
	$myParametriAtto->Terzo_Testo = $terzoTesto;
	$myParametriAtto->Intima = $intima;
	$myParametriAtto->Intima_Testo = $intimaTesto;
	$myParametriAtto->Intima_Caso_1 = $intimaCaso1;
	$myParametriAtto->Intima_Caso_2 = $intimaCaso2;
	$myParametriAtto->Intima_Caso_3 = $intimaCaso3;
	$myParametriAtto->Intima_Versamento = $intimaVersamento;
	$myParametriAtto->Info_Testo = $infoTesto;
	$myParametriAtto->Finale_Testo = $finaleTesto;
	$myParametriAtto->Opposizione = $opposizione;
	$myParametriAtto->Opposizione_Testo = $opposizioneTesto;
	$myParametriAtto->Qualifica_Firma_Sinistra = $qual_firma1;
	$myParametriAtto->Firma_Sinistra = $firma1;
	$myParametriAtto->Qualifica_Firma_Destra = $qual_firma2;
	$myParametriAtto->Firma_Destra = $firma2;
	$myParametriAtto->Modalita_Stampa_Firma = $modalitaFirma;
	
	$myParametriAtto->Intestazione_Relata_Ufficiale_Riscossione = $IntestazioneUffRiscossione;
	$myParametriAtto->Relata_Ufficiale_Riscossione = $UffRiscossione;
	
	$myParametriAtto->Intestazione_Relata_Ufficiale_Giudiziario = $IntestazioneUffGiudiziario;
	$myParametriAtto->SottoIntestazione_Relata_Ufficiale_Giudiziario = $SottoIntestazioneUffGiudiziario;
	$myParametriAtto->Relata_Ufficiale_Giudiziario = $UffGiudiziario;

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    $result = false;

    if($ID != null) $result = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $myParametriAtto,"parametri_atto_intimazione_ingiunzione",array("ID"=>$ID)));
    else $result = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $myParametriAtto,"parametri_atto_intimazione_ingiunzione"));

	if ($result)
	{
		$cls_db->End_Transaction();
		$msg = "Salvataggio riuscito correttamente";

		//echo "</br>OK";
	}
	else 
	{
	    $cls_db->Rollback();
	    $cls_db->End_Transaction();
		$error = 1;
		$msg = "Errore, salvataggio non riuscito";
        //echo "</br>ERRORE";
	}
}
else{
    $error = 2;
    $msg = "Warning. Tipo di salvataggio non conforme";
    header("Location: testo_avviso_intimazione.php?c={$c}&error={$error}&msg={$msg}");
}

header("Location: testo_avviso_intimazione.php?c={$c}&error={$error}&msg={$msg}");
?>