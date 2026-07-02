<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/Gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_Utils.php");

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_utils = new cls_Utils();

$err = 0;
$msg = "";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');

$titoloIngiunzione = $cls_help->getVar('titoloIngiunzione');
$sottotitoloIngiunzione = $cls_help->getVar('sottotitoloIngiunzione');
$primoTesto = $cls_help->getVar('primoTesto');
$premesso = $cls_help->getVar('premessoTitolo');
$premessoTesto = $cls_help->getVar('premessoTesto');
$secondoTesto = $cls_help->getVar('secondoTesto');
$terzoTesto = $cls_help->getVar('terzoTesto');
$ingiunge = $cls_help->getVar('ingiunge');
$ingiungeTesto = $cls_help->getVar('ingiungeTesto');
$finalePagina1 = $cls_help->getVar('finalePagina1');
$qual_firma1 = $cls_help->getVar('qualifica_firma_1');
$firma1 = $cls_help->getVar('firma_1');
$qual_firma2 = $cls_help->getVar('qualifica_firma_2');
$firma2 = $cls_help->getVar('firma_2');
$informazioni = $cls_help->getVar('informazioni');
$informazioniTesto = $cls_help->getVar('informazioniTesto');

$totaleComplex1 = $cls_help->getVar('primoTotale');
$testoTotaleComplex1 = $cls_help->getVar('testoPrimoTotale');
$totaleComplex2 = $cls_help->getVar('secondoTotale');
$testoTotaleComplex2 = $cls_help->getVar('testoSecondoTotale');
$TotComplessivo = $cls_help->getVar('totaleComplessivo');
$testoTotComplessivo = $cls_help->getVar('totaleComplessivoTesto');
$Diritto_Riscossione = $cls_help->getVar('dirittoRiscossione');
$Diritto_Riscossione_Testo = $cls_help->getVar('dirittoRiscossioneTesto');
$opposizione = $cls_help->getVar('opposizioneTitolo');
$testOpposizione = $cls_help->getVar('opposizioneTesto');
$creditiTributari = $cls_help->getVar('creditiTributari');
$creditiNonTributari = $cls_help->getVar('creditiNonTributari');
$provvedimento = $cls_help->getVar('provvedimentoTitolo');
$testoProvvedimento = $cls_help->getVar('provvedimentoTesto');
$esecutivita = $cls_help->getVar('esecutivitaTitolo');
$testoEsecutivita = $cls_help->getVar('esecutivitaTesto');
$primoTestoPagamento = $cls_help->getVar('primoTestoPagamento');
$secondoTestoPagamento = $cls_help->getVar('secondoTestoPagamento');
$primoTestoAvvertenza = $cls_help->getVar('primoTestoAvvertenza');
$secondoTestoAvvertenza = $cls_help->getVar('secondoTestoAvvertenza');
$terzoTestoAvvertenza = $cls_help->getVar('terzoTestoAvvertenza');
$IntestazioneRiscossioneDiretta = $cls_help->getVar('IntestazioneRiscossioneDiretta');
$RiscossioneDiretta = $cls_help->getVar('RiscossioneDiretta');
$IntestazioneUffRiscossione = $cls_help->getVar('IntestazioneUffRiscossione');
$UffRiscossione = $cls_help->getVar('UffRiscossione');
$IntestazioneUffGiudiziario = $cls_help->getVar('IntestazioneUffGiudiziario');
$SottoIntestazioneUffGiudiziario = $cls_help->getVar('SottoIntestazioneUffGiudiziario');
$UffGiudiziario = $cls_help->getVar('UffGiudiziario');

if ($invia == "Salva")
{

    $query = "SELECT * FROM parametri_testo_ingiunzione WHERE 1=2";
	$myParametroAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_testo_ingiunzione");//new parametri_testo_ingiunzione(null);

    //print_r($myParametroAtto);

	$myParametroAtto->ID = null;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Titolo_Ingiunzione = $titoloIngiunzione;
	$myParametroAtto->Sottotitolo_Ingiunzione = $sottotitoloIngiunzione;
	$myParametroAtto->Primo_Testo = $primoTesto;
	$myParametroAtto->Premesso = $premesso;
	$myParametroAtto->Premesso_Testo = $premessoTesto;
	$myParametroAtto->Secondo_Testo = $secondoTesto;
	$myParametroAtto->Terzo_Testo = $terzoTesto;
	$myParametroAtto->Ingiunge = $ingiunge;
	$myParametroAtto->Ingiunge_Testo = $ingiungeTesto;
	$myParametroAtto->Finale_Pagina_1 = $finalePagina1;
	$myParametroAtto->Qualifica_Firma_Sinistra = $qual_firma1;
	$myParametroAtto->Firma_Sinistra = $firma1;
	$myParametroAtto->Qualifica_Firma_Destra = $qual_firma2;
	$myParametroAtto->Firma_Destra = $firma2;
	$myParametroAtto->Informazioni = $informazioni;
	$myParametroAtto->Informazioni_Testo = $informazioniTesto;
	
	$myParametroAtto->Totale_1 = $totaleComplex1;
	$myParametroAtto->Testo_Totale_1 = $testoTotaleComplex1;
	$myParametroAtto->Totale_2 = $totaleComplex2;
	$myParametroAtto->Testo_Totale_2 = $testoTotaleComplex2;
	$myParametroAtto->Totale_Complessivo = $TotComplessivo;
	$myParametroAtto->Totale_Complessivo_Testo = $testoTotComplessivo;
	$myParametroAtto->Diritto_Riscossione = $Diritto_Riscossione;
	$myParametroAtto->Diritto_Riscossione_Testo = $Diritto_Riscossione_Testo;
	$myParametroAtto->Opposizione = $opposizione;
	$myParametroAtto->Opposizione_Testo = $testOpposizione;
	$myParametroAtto->Crediti_Tributari = $creditiTributari;
	$myParametroAtto->Crediti_Non_Tributari = $creditiNonTributari;
	$myParametroAtto->Provvedimento = $provvedimento;
	$myParametroAtto->Provvedimento_Testo = $testoProvvedimento;
	$myParametroAtto->Esecutivita = $esecutivita;
	$myParametroAtto->Esecutivita_Testo = $testoEsecutivita;
	$myParametroAtto->Pagamento_Primo_Testo = $primoTestoPagamento;
	$myParametroAtto->Pagamento_Secondo_Testo = $secondoTestoPagamento;
	$myParametroAtto->Avvertenza_Primo_Testo = $primoTestoAvvertenza;
	$myParametroAtto->Avvertenza_Secondo_Testo = $secondoTestoAvvertenza;
	$myParametroAtto->Avvertenza_Terzo_Testo = $terzoTestoAvvertenza;
    $myParametroAtto->Intestazione_Riscossione_Diretta = $IntestazioneRiscossioneDiretta;
    $myParametroAtto->Riscossione_Diretta = $RiscossioneDiretta;
	$myParametroAtto->Intestazione_Relata_Ufficiale_Riscossione = $IntestazioneUffRiscossione;
	$myParametroAtto->Relata_Ufficiale_Riscossione = $UffRiscossione;
	$myParametroAtto->Intestazione_Relata_Ufficiale_Giudiziario = $IntestazioneUffGiudiziario;
	$myParametroAtto->Sottointestazione_Relata_Ufficiale_Giudiziario = $SottoIntestazioneUffGiudiziario;
	$myParametroAtto->Relata_Ufficiale_Giudiziario = $UffGiudiziario;


    $queryCerca = "SELECT ID FROM parametri_testo_ingiunzione WHERE CC='".$myParametroAtto->CC."' AND Data_Creazione_Parametri = '" . $myParametroAtto->Data_Creazione_Parametri . "' ";
    $result = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_testo_ingiunzione");

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    if($result->ID == null) {
        $obj = $cls_utils->GetObjectQuery((array)$myParametroAtto,"parametri_testo_ingiunzione");
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
        $obj = $cls_utils->GetObjectQuery((array)$myParametroAtto,"parametri_testo_ingiunzione",array("ID" => $result->ID));
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
    header("Location: testo_ingiunzione.php?c={$c}&a={$a}&error={$err}&msg={$msg}");

}
else
{
    $err = 2;
    $msg = "Warning, tipo salvataggio non conforme. Query non eseguita";
    header("Location: testo_ingiunzione.php?c={$c}&a={$a}&error={$err}&msg={$msg}");
}
?>