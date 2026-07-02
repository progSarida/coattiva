<?php
include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/flussi.php";
include CLASSI . "/pdf_con_bollettino.php";
include CLASSI . "/numero_letterale.php";
include_once FPDI . "/fpdi.php";

include CLASSI . "/classe_email.php";

require EMAIL.'/PHPMailerAutoload.php';
require_once CLASSI. "\php-imap-client-master\Imap.php";

include_once CLS."/cls_printer_params.php";
include_once CLS."/cls_db.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

ini_set('memory_limit', '512M');

$a = get_var('a');
$c = get_var('c');
$stampa_select = strtoupper(get_var('stampa_select'));
$tipo_partita = get_var("tipo_partita");
$a_taxType = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT Id FROM tax_type WHERE Name=\"".$tipo_partita."\""));
$TaxTypeId = $a_taxType['Id'];

$PrinterId = get_var("PrinterId");
$cls_db = new cls_db();
$cls_params = new cls_printer_params();
$a_printerParams = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_params->getPrinterChargeQuery($PrinterId,1)));

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
$stemmaComune = $comune->Stemma_1;

$gestore = $comune->Gestore;
$tipo_gestore = $gestore->Tipo;
$stemmaGestore = $gestore->Stemma;

if($tipo_gestore == "Concessionario")
{
	if($stemmaGestore!="")
		$image_file = $stemmaGestore;
	else
		$image_file = "/gitco2/immagini/sarida_logo.png";
}
else
	$image_file = $stemmaComune;

$percorso_image_file = $_SERVER['DOCUMENT_ROOT'].$image_file;

$intest_gestore = $gestore->intestazione_gestore("Riscossione coattiva", $nome_com);
$righe_gestore = $gestore->righe_indirizzo();

$recapiti_gestore = "";
if($gestore->Telefono!="")
	$recapiti_gestore.= "Tel: ".$gestore->Telefono;
if($gestore->Fax!="")
	$recapiti_gestore.= " - Fax: ".$gestore->Fax;
if($gestore->Mail!="")
	$recapiti_gestore.= " - Mail: ".$gestore->Mail;
if($gestore->PEC!="")
	$recapiti_gestore.= " - PEC: ".$gestore->PEC;


$ufficio = $comune->Ufficio;
$intest_ufficio = $ufficio->intestazione_ufficio();

$chiudi = "";
//PARAMETRI RESPONSABILI
$par_responsabili = new parametri_responsabili($c, $tipo_partita);
$firme_responsabili = $par_responsabili->firme_responsabili();
$firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");
$testo_firma = $par_responsabili->Testo_Sostitutivo;
if($firma_resp[1]['firma']=="" || $firma_resp[2]['firma']=="" || $firma_resp[3]['firma']=="")
{
	alert('Parametri Responsabili CDS incompleti!');
	$chiudi = "chiudi_finestra()";
}

$par_generali = new parametri_generali($c, $tipo_partita);
if($stampa_select=="FLUSSO"){
    if($par_generali->Spese_Anticipate==""){
        alert('Le Spese anticipate devono essere impostate nei parametri generali '.$tipo_partita.'!');
        $chiudi = "chiudi_finestra()";
    }
    if($par_generali->SMA==""){
        alert('La distinta SMA deve essere impostata nei parametri generali '.$tipo_partita.'!');
        $chiudi = "chiudi_finestra()";
    }
    if($par_generali->Restituzione1=="" || $par_generali->Restituzione2=="" || $par_generali->Restituzione3=="" || $par_generali->Restituzione4=="" || $par_generali->Restituzione5==""){
        alert('I campi della restituzione devono essere impostati nei parametri generali '.$tipo_partita.'!');
        $chiudi = "chiudi_finestra()";
    }
}

//CONTROLLO TESTO
$para_pigno = new testo_pignoramento_presso_lavoro(NULL);
$myId = $para_pigno->CercaParametroData($c, date("Y-m-d"),"si");

if($myId==null)
	$chiudi = "chiudi_finestra()";

$testo = new testo_pignoramento_presso_lavoro($myId);

$informazioni = $testo->Informazioni_Testo;
$data_testo = $testo->Data_Creazione_Parametri;

if($stampa_select=="PROVVISORIA" || $stampa_select == "DEFINITIVA")
{
	if($informazioni=="")
	{
		alert("Il campo Informazioni non e' stato compilato! Stampa annullata!");
		echo "<script>window.close();</script>";
	}

	if( date('Y-m-d') > date( "Y-m-d" , strtotime( $data_testo."+1 month" )) ){
		alert("Il salvataggio del testo e' stato effettuato da piu' di 30 giorni. Ricontrollare il campo Informazioni e salvare!");
		echo "<script>window.close();</script>";
	}
}

$data_file = date('Y-m-d');
$ora_file = date('H-i-s');
$vedi_file = "";

$stampa_dir = "";
if($stampa_select == "PROVVISORIA")
{

	$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Datore_di_Lavoro/STAMPE PROVVISORIE" );

	$file_stampa = $stampa_dir."/Pignoramenti_Presso_Lavoro_Provvisori_".$c."_".$data_file."_".$ora_file.".pdf";
	$vedi_file = mostra_file_path($file_stampa);
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Stampa Avviso</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

<script>
function inizio()
{
	$('#progressbar').progressbar({
		value: false
	});
	$( "#barlabel" ).text("Inizio elaborazione...");
}

function update(valore)
{
	$( "#progressbar" ).progressbar({value: parseInt(valore) });
	$( "#barlabel" ).text( valore + "%" );
}

function update_mail(valore, mail)
{
    $( "#progressbar" ).progressbar({value: parseInt(valore) });
    $( "#barlabel" ).text( valore + "% ( "+mail+" )" );
}

function nessun_risultato()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Nessun risultato trovato");
}

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);

	mostra_file();
}

function fine2(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);
}

function merge()
{
	$('#progressbar2').progressbar({
		value: false
	});
	$( "#barlabel2" ).text("Inizio creazione file di stampa...");
}

function update_merge(valore)
{
	$( "#progressbar2" ).progressbar({value: parseInt(valore) });
	$( "#barlabel2" ).text( valore + "%" );
}

function fine_merge(value)
{
	$( "#progressbar2" ).progressbar({value: false });
	$( "#barlabel2" ).text( value );
}

function fine_e_apri(value, value2)
{
	$( "#progressbar2" ).progressbar({value: 100 });
	$( "#barlabel2" ).text( value );
	
	sleep(1000);

	window.name = "Stampa";
	window.open(value2,"Stampa");
	
}

function mostra_file()
{
	window.name = "Stampa";
	window.open('<?php echo $vedi_file; ?>',"Stampa");
}

function cronologici(value)
{
	$('#crono_form').submit();
}

function atti_stampati(value)
{
    $('#flusso_form').submit();
}

function gestione_email()
{
	$('#pec_form').submit();
}

function chiudi_finestra()
{
	window.close();
}

<?php echo $chiudi; ?>

</script>

</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
<tr>
<td width=1%><br></td>
<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
	<tr>
		<td valign=top>
		<br><br><br>
		<font class="titolo font18 text_center">Stampa Pignoramenti presso datore di lavoro</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div class="table_interna text_center" id="progressbar2" style="height:55px;"><div class="text_center" id="barlabel2"></div></div>
		
		</td>
	</tr>
</table>

<?php

$data_definitiva = from_mysql_date(get_var('data_definitiva'));

$sel_originale = get_var('sel_originale');
$sel_debitore = get_var('sel_debitore');
$sel_terzi = get_var('sel_terzi');
$sel_bollettino = get_var('sel_bollettino');

$daco  = strtoupper(get_var('daco'));
$anom  = strtoupper(get_var('anom'));

$dano  = strtoupper(get_var('dano'));
$acog  = strtoupper(get_var('acog'));

$da_partita  = get_var('da_n_elenco');
$a_partita  = get_var('a_n_elenco');

$da_elab = from_mysql_date(get_var('da_elab'));
$a_elab = from_mysql_date(get_var('a_elab'));

$da_notif = from_mysql_date(get_var('da_notif'));
$a_notif = from_mysql_date(get_var('a_notif'));

$da_sped = from_mysql_date(get_var('da_sped'));
$a_sped = from_mysql_date(get_var('a_sped'));

$da_cons = from_mysql_date(get_var('da_cons'));
$a_cons = from_mysql_date(get_var('a_cons'));

$consegnato_a = get_var('consegnato_a');

$da_stampa = from_mysql_date(get_var('da_stampa'));
$a_stampa = from_mysql_date(get_var('a_stampa'));

$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

$stato_stampa = get_var('stato_stampa');

$ordinamento = get_var('ordinamento');

$flag = get_var('atto_precedente');
if($flag!="si")	$flag="no";

flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();
sleep(2);


/**		SELEZIONE UTENTI 			*/
$query_utente = da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
$where_anno = null;
if( $da_anno != null && $ad_anno != null )
	$where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."' AND Flag_Blocco_Coazione != 'si' AND Tipo = '".$tipo_partita."' ";
	
$query_partita = da_a_partita( $c , $da_partita , $a_partita , $where_anno );
$array_partite = mysql_array( $query_partita );

/** 	SELEZIONE PIGNORAMENTI	*/
$campi_stati = array("PIG_GEN.Stato_Stampa");
if($stampa_select!="FLUSSO")
    $valori_stati = array ($stato_stampa);
else
    $valori_stati = array ("Stampato");


$campi_array = array ("Data_Elaborazione" , "Data_Notifica" , "Data_Stampa" );
$array_da_data = array( to_mysql_date($da_elab) , to_mysql_date($da_notif) , to_mysql_date($da_stampa) );
$array_a_data = array( to_mysql_date($a_elab) , to_mysql_date($a_notif) , to_mysql_date($a_stampa) );

$where_pigno = array();
$where_pigno[0] = selezione_date_query( "PIG_GEN", $campi_array , $array_da_data , $array_a_data );
$where_pigno[1] = where_campi($campi_stati, $valori_stati);

$pignoramento = new pignoramento(null, $c);
$query_pignoramenti = $pignoramento->query_selezione_pignoramenti($c, 'terzi', 'lavoro', $ordinamento, $where_pigno);

$array_pignoramenti = mysql_array($query_pignoramenti);

$num_pignoramenti = count($array_pignoramenti);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

//print_r($array_pignoramenti);
//print_r($array_utenti);
//print_r($array_partite);
//
//die;
// alert($num_pignoramenti." ".$num_utenti." ".$num_partite);

$anno_current = date("Y");
$array_cronologici = array();
$array_stampati = array();
//alert($stampa_select);
if($stampa_select == "FLUSSO")
{
    if($stato_stampa=="Da stampare")
    {
        mysql_query('BEGIN');

        $flusso_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Datore_di_Lavoro/FLUSSI/" );

        //INTESTAZIONE FLUSSO
        $pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
        $array_intestazione = array();

        $array_intestazione[] = "CODICE_CATASTALE";
        $array_intestazione[] = "TIPOLOGIA_STAMPA";
        $array_intestazione[] = "TIPOLOGIA_ATTO";
        $array_intestazione[] = "ID_CRONOLOGICO";
        $array_intestazione[] = "ANNO_CRONOLOGICO";

        $array_intestazione[] = "QUINTO_CAMPO";
        $array_intestazione[] = "STEMMA";

        $array_intestazione[] = "GESTORE_1";
        $array_intestazione[] = "GESTORE_2";
        $array_intestazione[] = "GESTORE_3";
        $array_intestazione[] = "GESTORE_4";
        $array_intestazione[] = "GESTORE_5";
        $array_intestazione[] = "GESTORE_6";
        $array_intestazione[] = "GESTORE_7";

        $array_intestazione[] = "UFFICIO_1";
        $array_intestazione[] = "UFFICIO_2";
        $array_intestazione[] = "UFFICIO_3";
        $array_intestazione[] = "UFFICIO_4";
        $array_intestazione[] = "UFFICIO_5";
        $array_intestazione[] = "UFFICIO_6";

        $array_intestazione[] = "CODICE_UTENTE";
        $array_intestazione[] = "NUMERO_PARTITA";
        $array_intestazione[] = "DESTINATARIO";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_1";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_2";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_3";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_4";

        $array_intestazione[] = "OGGETTO";
        $array_intestazione[] = "SOTTOTITOLO_OGGETTO";

        $array_intestazione[] = "TESTO_1";
        $array_intestazione[] = "PREMESSO";
        $array_intestazione[] = "PREMESSO_TESTO";
        $array_intestazione[] = "ATTI_PRECEDENTI";
        $array_intestazione[] = "TESTO_2";

        $array_intestazione[] = "TESTO_IMPORTO_DEBITO";
        $array_intestazione[] = "IMPORTO_DEBITO";
        $array_intestazione[] = "TESTO_IMPORTO_DEBITO_PAGATO";
        $array_intestazione[] = "IMPORTO_DEBITO_PAGATO";
        $array_intestazione[] = "TESTO_IMPORTO_PARZIALE";
        $array_intestazione[] = "IMPORTO_PARZIALE";

        $array_intestazione[] = "TESTO_IMPORTO_1";
        $array_intestazione[] = "IMPORTO_1";
        $array_intestazione[] = "TESTO_IMPORTO_2";
        $array_intestazione[] = "IMPORTO_2";
        $array_intestazione[] = "TESTO_IMPORTO_3";
        $array_intestazione[] = "IMPORTO_3";
        $array_intestazione[] = "TESTO_IMPORTO_4";
        $array_intestazione[] = "IMPORTO_4";
        $array_intestazione[] = "TESTO_TOTALE_1";
        $array_intestazione[] = "IMPORTO_TOTALE_1";

        $array_intestazione[] = "INFORMAZIONI";
        $array_intestazione[] = "INFORMAZIONI_TESTO";

        $array_intestazione[] = "MODALITA_PAGAMENTO";
        $array_intestazione[] = "MODALITA_PAGAMENTO_TESTO";

        $array_intestazione[] = "VISTO";
        $array_intestazione[] = "VISTO_TESTO_1";
        $array_intestazione[] = "VISTO_TESTO_2";

        $array_intestazione[] = "CONSIDERATO";
        $array_intestazione[] = "CONSIDERATO_TESTO_1";
        $array_intestazione[] = "CONSIDERATO_TESTO_2";
        $array_intestazione[] = "CONSIDERATO_TESTO_3";

        $array_intestazione[] = "OPPOSIZIONE";
        $array_intestazione[] = "OPPOSIZIONE_TESTO";

        $array_intestazione[] = "AUTOTUTELA";
        $array_intestazione[] = "AUTOTUTELA_TESTO";

        $array_intestazione[] = "LUOGO_DATA";

        $array_intestazione[] = "FIRMA_INTESTAZIONE_SINISTRA";
        $array_intestazione[] = "FIRMA_NOME_SINISTRA";
        $array_intestazione[] = "FIRMA_SINISTRA";
        $array_intestazione[] = "TESTO_FIRMA_SINISTRA";

        $array_intestazione[] = "FIRMA_INTESTAZIONE_DESTRA";
        $array_intestazione[] = "FIRMA_NOME_DESTRA";
        $array_intestazione[] = "FIRMA_DESTRA";
        $array_intestazione[] = "TESTO_FIRMA_DESTRA";

        $array_intestazione[] = "UFFICIALE_TESTO";

        $array_intestazione[] = "ASSOGGETTO";
        $array_intestazione[] = "ASSOGGETTO_TESTO";

        $array_intestazione[] = "ORDINATO";
        $array_intestazione[] = "ORDINATO_TESTO";

        $array_intestazione[] = "INFORMATO_LE_PARTI";
        $array_intestazione[] = "INFORMATO_LE_PARTI_TESTO_1";
        $array_intestazione[] = "INFORMATO_LE_PARTI_TESTO_2";

        $array_intestazione[] = "INTIMA";
        $array_intestazione[] = "INTIMA_TESTO";

        $array_intestazione[] = "INFORMATO";
        $array_intestazione[] = "INFORMATO_TESTO";

        $array_intestazione[] = "INVITATO";
        $array_intestazione[] = "INVITATO_TESTO_1";
//        $array_intestazione[] = "INVITATO_TESTO_2";

        $array_intestazione[] = "NOTIFICA_PIGNORAMENTO";
        $array_intestazione[] = "INTESTAZIONE_RELATA";
        $array_intestazione[] = "SOTTOINTESTAZIONE_RELATA";

        $array_intestazione[] = "RELATA_NOTIFICA";
        $array_intestazione[] = "RELATA_DEBITORE";

        $array_intestazione[] = "FIRMA_INTESTAZIONE_UFFICIALE";
        $array_intestazione[] = "FIRMA_NOME_UFFICIALE";
        $array_intestazione[] = "FIRMA_UFFICIALE";
        $array_intestazione[] = "TESTO_FIRMA_UFFICIALE";

        $array_intestazione[] = "BOLL_1_STEMMA";
        $array_intestazione[] = "BOLL_1_TD";
        $array_intestazione[] = "BOLL_1_AUTORIZZAZIONE";
        $array_intestazione[] = "BOLL_1_CONTO";
        $array_intestazione[] = "BOLL_1_INTESTATARIO";
        $array_intestazione[] = "BOLL_1_IBAN";
        $array_intestazione[] = "BOLL_1_IMPORTO";
        $array_intestazione[] = "BOLL_1_IMPORTO_LETTERE";
        $array_intestazione[] = "BOLL_1_PAGANTE_RIGA_1";
        $array_intestazione[] = "BOLL_1_PAGANTE_RIGA_2";
        $array_intestazione[] = "BOLL_1_PAGANTE_RIGA_3";
        $array_intestazione[] = "BOLL_1_CAUSALE_RIGA_1";
        $array_intestazione[] = "BOLL_1_CAUSALE_RIGA_2";
        $array_intestazione[] = "BOLL_1_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_1_BC_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_1_BC_IMPORTO";
        $array_intestazione[] = "BOLL_1_BC_CONTO";
        $array_intestazione[] = "BOLL_1_BC_TD";

        $array_intestazione[] = "BOLL_2_STEMMA";
        $array_intestazione[] = "BOLL_2_TD";
        $array_intestazione[] = "BOLL_2_AUTORIZZAZIONE";
        $array_intestazione[] = "BOLL_2_CONTO";
        $array_intestazione[] = "BOLL_2_INTESTATARIO";
        $array_intestazione[] = "BOLL_2_IBAN";
        $array_intestazione[] = "BOLL_2_IMPORTO";
        $array_intestazione[] = "BOLL_2_IMPORTO_LETTERE";
        $array_intestazione[] = "BOLL_2_PAGANTE_RIGA_1";
        $array_intestazione[] = "BOLL_2_PAGANTE_RIGA_2";
        $array_intestazione[] = "BOLL_2_PAGANTE_RIGA_3";
        $array_intestazione[] = "BOLL_2_CAUSALE_RIGA_1";
        $array_intestazione[] = "BOLL_2_CAUSALE_RIGA_2";
        $array_intestazione[] = "BOLL_2_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_2_BC_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_2_BC_IMPORTO";
        $array_intestazione[] = "BOLL_2_BC_CONTO";
        $array_intestazione[] = "BOLL_2_BC_TD";

        $array_intestazione[] = "INTESTATARIO_SMA";
        $array_intestazione[] = "NUMERO_SMA";
        $array_intestazione[] = "SPESE_ANTICIPATE";

        $array_intestazione[] = "MOD23_SOGGETTO_MITTENTE";
        $array_intestazione[] = "MOD23_ENTE_GESTITO";
        $array_intestazione[] = "MOD23_RECAPITO_SOGGETTO";
        $array_intestazione[] = "MOD23_INDIRIZZO_SOGGETTO";
        $array_intestazione[] = "MOD23_CITTA_SOGGETTO";

        $myFlusso = new flussi ($flusso_dir, "flusso", "pigno_lavoro", $c, $anno_current, "ultimoFlusso pignoramento_generale", $data_file, $ora_file, "txt");

        $nomefiletxt = $myFlusso->GetNomeFlusso();

        $myFlusso->AggiungiIntestazioneFlusso($array_intestazione);

    }
}
else if($stampa_select == "PROVVISORIA")
{
	
	/**
	 ///////////////////////////////		PDF	    //////////////////////////////////
	*/

	$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
	
	/**
	 //////////////////////////////////////////////////////////////////////////////
	 */
}
else if($stampa_select == "DEFINITIVA")
{
	$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Datore_di_lavoro/STAMPE DEFINITIVE" );
	$concat_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Datore_di_lavoro/STAMPE CONCATENATE" );
	$arrayConcat = Array();
}
else if($stampa_select == "PEC")
{
	$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
	$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Datore_di_lavoro/STAMPE DEFINITIVE" );
}

		
	$cont_result = 0;
	for( $l=0; $l < $num_pignoramenti; $l++ )//FOR PIGNORAMENTI
	{	
		set_time_limit(30);
		echo "<script>update(".ceil($l*100/$num_pignoramenti).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
		{			
			if( $array_pignoramenti[$l]['Partita_ID'] == $array_partite[$k]['ID'] )//IF ATTO/PARTITA
			{				
				for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
				{
					if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF PARTITA/UTENTE
					{
						set_time_limit(60);

						//PARTITA
						$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
						if($partita->Tipo == "CDS")
						{
							if($partita->Utente->Data_Morte!=null && $partita->Utente->Data_Morte!="0000-00-00")
								break;
						}
						
						//PIGNORAMENTO
						$pignoramento = new pignoramento( $array_pignoramenti[$l]['ID'], $c );
						$data_stampa_file = to_mysql_date($pignoramento->Data_Stampa);
						if($data_stampa_file==null)
							$data_stampa_file = to_mysql_date($data_definitiva);
						
						$identificativo_file = "Pignoramento_presso_lavoro_".$c."_".$pignoramento->Anno_Cronologico."_".$pignoramento->ID_Cronologico."_".$data_stampa_file;
						$file_base = $stampa_dir."/".$identificativo_file;
						
						$file_stampa_originale = $file_base."_originale.pdf";
						$file_relata_originale = $file_base."_rel_originale.pdf";
						
						$file_stampa_copia_debitore = $file_base."_copia_debitore.pdf";
						$file_relata_debitore = $file_base."_rel_debitore_0.pdf";
							
						for($i=0;$i<count($pignoramento->Presso_Terzi);$i++)
						{
							$file_copia_terzo[$i] = $file_base."_copia_terzo_".$i.".pdf";
							$file_relata_terzo[$i] = $file_base."_rel_terzo_".$i."_0.pdf";
						}
									
						$file_bollettino = $file_base."_bollettino.pdf";
						
						
						//ESCLUSIONI
						if($stampa_select == "PROVVISORIA")
						{
							if($pignoramento->ID_Cronologico == "0" || $pignoramento->Anno_Cronologico == "0")
								break;
							
							if(from_mysql_date($pignoramento->Data_Stampa)!=null)
								break;
						}
						else if($stampa_select == "CRONOLOGICI")
						{
							if($pignoramento->ID_Cronologico == "0" && $pignoramento->Anno_Cronologico == "0")
							{
								
								$array_cronologici[] = $pignoramento->ID;
								$cont_result++;
							}
						
							break;
						}
						else if($stampa_select == "DEFINITIVA")
						{
							$par_email = new parametri_email($c, $partita->Tipo, "pec");
							
							if($par_email->Indirizzo_Email=="")
							{
								alert("INVIO TRAMITE PEC: L'indirizzo PEC da cui deve essere spedita la richiesta non e' stato inserito! Impossibile procedere con la stampa.");
								
								echo "<script>window.close();</script>";
								die;
							}
							
							if($pignoramento->ID_Cronologico == "0" || $pignoramento->Anno_Cronologico == "0")
								break;
								
							if($stato_stampa == "Stampato")
							{
								if($sel_originale == "si")
								{
									$arrayConcat[] = $file_stampa_originale;
									$arrayConcat[] = $file_relata_originale;
								}
									
								if($sel_debitore == "si")
								{
									$arrayConcat[] = $file_stampa_copia_debitore;
									$arrayConcat[] = $file_relata_debitore;
								}
									
								if($sel_bollettino == "si")
									$arrayConcat[] = $file_bollettino;
									
								if($sel_terzi == "si")
								{
									for($i=0;$i<count($pignoramento->Presso_Terzi);$i++)
									{
										$arrayConcat[] = $file_copia_terzo[$i];
										$arrayConcat[] = $file_relata_terzo[$i];
									}
								}								
								
								$cont_result++;
									
								break;
							}
						}
                        else if($stampa_select == "FLUSSO")
                        {

                            if($stato_stampa == "Stampato")
                            {
                                $array_stampati[] = $pignoramento->ID;
                                $cont_result++;

                                break;
                            }
                            else if(from_mysql_date($pignoramento->Data_Flusso) != null){
//                                alert(from_mysql_date($pignoramento->Data_Flusso));
                                break;
                            }


                            $array_stampati[] = $pignoramento->ID;

                        }
						else if($stampa_select == "PEC")
						{
							if($pignoramento->ID_Cronologico == "0" || $pignoramento->Anno_Cronologico == "0")
								break;
								
							if($pignoramento->Stato_Stampa != "Stampato" || from_mysql_date($pignoramento->Data_Stampa)==null)
								break;
						}
						
						$ID_partita = $partita->Comune_ID;
						$anno_rif = $partita->Anno_Riferimento;
						$settore = $partita->Tipo;

						$atti_notificati = $partita->tutti_gli_atti_notificati();
						$riferimento_partita = $ID_partita."/".$anno_rif;
						$ultima_ing = $partita->Ingiunzione;
						$riferimento_ingiunzione = "ING. ".$ultima_ing->ID_Cronologico."/".$ultima_ing->Anno_Cronologico;
						
						$ing_completa = "Ingiunzione n.".$ultima_ing->ID_Cronologico." del ".$ultima_ing->Anno_Cronologico;
						$ing_completa.= " notificata il ".from_mysql_date($ultima_ing->Data_Notifica);
						
						switch($settore)
						{
							case "CDS":		$tipo_ing = "Riscossione violazioni al codice della strada";
											$utente_ing = "trasgressore";
						
							break;
						
							default:		$tipo_ing = "Servizio di default";
											$utente_ing = "default";
						
							break;
							
						}
						
						//PARAMETRI ANNUALI
						$parametri = new parametri_annuali( $c , date("Y-m-d") , $settore);
						$CAD = $parametri->CAD;//CAD
						$CAN = $parametri->CAN;
						$spese_notifica = $parametri->Spese_Notifica_Pignoramento;
						$spese_postali_ag = $parametri->Spese_Postali_AG;
						
						$A_Mani = $parametri->A_Mani_Pignoramento;
						$spese_ufficiale = conv_num(number_format($A_Mani,2));
						
						//PARAMETRI RESPONSABILI
						$par_responsabili = new parametri_responsabili($c, $settore);
						$firme_responsabili = $par_responsabili->firme_responsabili();
						$firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");
						
						//PARAMETRI PAGAMENTO
						$par_pagamento = new parametri_pagamento( $c, $settore);
						$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
						$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
						$iban = $par_pagamento->IBAN;	//IBAN
						$autorizzazione_1 = $par_pagamento->testo_autorizzazione(1);//AUTORIZZAZIONE BOLLETTINO 1
						$autorizzazione_2 = $par_pagamento->testo_autorizzazione(2);//AUTORIZZAZIONE BOLLETTINO 2
						$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
						$td_2 = $par_pagamento->Bollettino_2;//TD BOLLETTINO 2
						$stemma = $par_pagamento->Stemma;
						$stemma_2 = $par_pagamento->Stemma_2;
						$ctrl_importo_1 = $par_pagamento->Importo_1_Pignoramento;
						$ctrl_importo_2 = $par_pagamento->Importo_2_Pignoramento;
						$giorni_pigno = $par_pagamento->Scadenza_Pignoramento;
						$riga2causale = "SCADENZA PAGAMENTO ENTRO ".$giorni_pigno." GIORNI DALLA DATA DI NOTIFICA";
						
						//UTENTE
						$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
						$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$utente->Sigla_Forma_Giuridica;
						$utente_id = $utente->Comune_ID;
						$codice_utente = $utente_id."/".$c;
						if($utente->Genere == "D")
							$CF_PI = "Partita Iva: ".$utente->Partita_Iva;
						else 
							$CF_PI = "Codice Fiscale: ".$utente->Codice_Fiscale;
						$PEC_Utente = $utente->PEC;
						$indirizzo_destinatario = $utente->righe_indirizzo();
						$indirizzo_completo = $indirizzo_destinatario['Completo'];
						$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];
						
						if($utente->Residenza->CC_Indirizzo=="")
                        {
//                            alert($utente->Residenza->CC_Indirizzo);
                            break;
                        }
						
						//CONTROLLO TRIBUNALE
						$tribunale = new ufficio_giudiziario($utente->Residenza->CC_Indirizzo, "tribunale");
						
						//CONTROLLO ISTITUTO VENDITE GIUDIZIARIE
						$istituto_vendite = new ufficio_giudiziario($tribunale->CC_Ufficio, "istituto");
						
						$sede_istituto = $istituto_vendite->righe_indirizzo();
						$PEC_Istituto = $istituto_vendite->PEC;
						$Mail_Istituto = $istituto_vendite->Mail;
						$recapiti_istituto = "";
						if($istituto_vendite->Telefono!="")
							$recapiti_istituto.= "Tel: ".$istituto_vendite->Telefono;
						if($istituto_vendite->Fax!="")
							$recapiti_istituto.= " - Fax: ".$istituto_vendite->Fax;
						if($istituto_vendite->Mail!="")
							$recapiti_istituto.= " - Mail: ".$istituto_vendite->Mail;
						if($istituto_vendite->PEC!="")
							$recapiti_istituto.= " - PEC: ".$istituto_vendite->PEC;
						
						//RIFERIMENTO AVVISO
						$Atto_ID = $pignoramento->Atto_ID;
						$attoPrec = new atto($Atto_ID, $c);
						
						if(from_mysql_date($attoPrec->Data_Notifica)=="" || $attoPrec->controlloAttoPignoramento($partita->Tipo)!="ok")
                        {
//                            alert($attoPrec->Data_Notifica);
                            continue;
                        }
						
						if($attoPrec->Motivo_Notifica!=0)
                        {
//                            alert($attoPrec->Motivo_Notifica);
                            continue;
                        }
						
						$info_cartella = $attoPrec->Info_Cartella;
						$info_avviso = $attoPrec->Atto." n.".$attoPrec->ID_Cronologico." del ".$attoPrec->Anno_Cronologico;
						
						/**
						 * DATI GENERALI PIGNORAMENTO
						*/
												
						//CRONO PIGNORAMENTO
						$Comune_ID_pignoramento = $pignoramento->Comune_ID;
						$Anno_Cronologico = $pignoramento->Anno_Cronologico;
						$ID_Cronologico = $pignoramento->ID_Cronologico;
						
						$riga1causale = "Pignoramento presso Datore di Lavoro n.".$ID_Cronologico." del ".$Anno_Cronologico." Rif.".$riferimento_partita;
						$quinto_campo = $pignoramento->quinto_campo();
						
						//TIPO PIGNORAMENTO
						$tipo_pignoramento = $pignoramento->Tipo;
						$tipo_terzi_generale = $pignoramento->Tipo_Terzi;
						
						//DATE E STATI
						$Data_Elaborazione = from_mysql_date($pignoramento->Data_Elaborazione);
						$Data_Stampa = from_mysql_date($pignoramento->Data_Stampa);
						$Stato_Stampa = $pignoramento->Stato_Stampa;
						$Data_Spedizione = from_mysql_date($pignoramento->Data_Spedizione);
						$Data_Consegna = from_mysql_date($pignoramento->Data_Consegna);
						$Ufficiale_Consegna = $pignoramento->Tipo_Ufficiale;
						if($Ufficiale_Consegna == "riscossione")
							$testo_ufficiale = "Ufficiale della Riscossione ( Atto di nomina n.___ del __/__/____ effettuato da _________________ )";
						else 
							$testo_ufficiale = "Ufficiale Giudiziario addetto U.N.E.P.";
						
						
						//NOTIFICA DEBITORE
						if(isset($pignoramento->Notifica_Debitore))
						{
							$notifica_debitore = $pignoramento->Notifica_Debitore;
							
							$Tipo_Invio_Debitore = $notifica_debitore->Modalita_Stampa;
							$Data_Notifica_Debitore = from_mysql_date($notifica_debitore->Data_Notifica);

                            if($stampa_select=='FLUSSO' && $Data_Notifica_Debitore!=null)
                                break;

							$Stato_Notifica_Debitore = $notifica_debitore->Stato_Notifica;
							
							//SPESE DEBITORE
							$Spese_Notifica_Debitore = $notifica_debitore->Spese_Notifica;
							if($Spese_Notifica_Debitore!=null)
								$Spese_Notifica_Debitore = conv_num(number_format($Spese_Notifica_Debitore,2));
						}

						$nomi_terzi = "";
						$terzi_pro_tempore = "";
						
						if(isset($pignoramento->Presso_Terzi))
						{								
							//TERZI
							$presso_terzi = $pignoramento->Presso_Terzi;
							for($x_terzo=0;$x_terzo<count($presso_terzi);$x_terzo++)
							{
								$terzo_utente[$x_terzo] = $presso_terzi[$x_terzo]->Dati_Terzo;
								$PEC_terzo[$x_terzo] = $terzo_utente[$x_terzo]->PEC;
								$indirizzo_terzo[$x_terzo] = $terzo_utente[$x_terzo]->righe_indirizzo();
									
								$terzo_ID[$x_terzo] = $presso_terzi[$x_terzo]->Terzo_ID;
								$nome_cognome_terzo[$x_terzo] = $terzo_utente[$x_terzo]->Cognome . $terzo_utente[$x_terzo]->Ditta ." ". $terzo_utente[$x_terzo]->Nome;
									
								if($x_terzo>0)
								{
									$nomi_terzi.= ", nonche' ";
									$terzi_pro_tempore.= ", nonche' ";
								}
										
								$nomi_terzi.= strtoupper($nome_cognome_terzo[$x_terzo]);
									
								$terzi_pro_tempore.= strtoupper($nome_cognome_terzo[$x_terzo]);
									
								$terzo_singolo[$x_terzo] = $nome_cognome_terzo[$x_terzo];
								if($terzo_utente[$x_terzo]->Genere=="D")
								{
									$terzi_pro_tempore.= ", in persona del legale rappresentante pro tempore";
									$terzo_singolo[$x_terzo].= ", in persona del legale rappresentante pro tempore";
								}
					
								$Fonte_Dati[$x_terzo] = $presso_terzi[$x_terzo]->Fonte_Dati;
								$Note_Terzi[$x_terzo] = $presso_terzi[$x_terzo]->Note;
								$Tipo_Contratto_Lavoro[$x_terzo] = $presso_terzi[$x_terzo]->Tipo_Contratto_Lavoro;
								$Data_Costituzione_Ditta_Lavoro[$x_terzo] = from_mysql_date($presso_terzi[$x_terzo]->Data_Costituzione_Ditta_Lavoro);
								$Data_Ditta_Operativa_Lavoro[$x_terzo] = from_mysql_date($presso_terzi[$x_terzo]->Data_Ditta_Operativa_Lavoro);
								$Data_Dipendenze_Lavoro[$x_terzo] = from_mysql_date($presso_terzi[$x_terzo]->Data_Dipendenze_Lavoro);
								$azienda_lavoro[$x_terzo] = $presso_terzi[$x_terzo]->Azienda;
								
								$notifica_terzo = $pignoramento->Presso_Terzi[$x_terzo]->Notifica;
								
								$Spedizione_PEC_terzo[$x_terzo] = $notifica_terzo->Spedizione_PEC;
								$Tipo_Invio_Terzo[$x_terzo] = $notifica_terzo->Modalita_Stampa;
								$Data_Notifica_Terzo[$x_terzo] = from_mysql_date($notifica_terzo->Data_Notifica);
								$Stato_Notifica_Terzo[$x_terzo] = $notifica_terzo->Stato_Notifica;
									
								//SPESE TERZO
								$Spese_Notifica_Terzo[$x_terzo] = $notifica_terzo->Spese_Notifica;
								$CAN_Terzo[$x_terzo] = $notifica_terzo->CAN;
								$CAD_Terzo[$x_terzo] = $notifica_terzo->CAD;
								$CAN_CAD_Terzo[$x_terzo] = $CAN_Terzo[$x_terzo] + $CAD_Terzo[$x_terzo];
									
								if($Spese_Notifica_Terzo[$x_terzo]!=null)
									$Spese_Notifica_Terzo[$x_terzo] = conv_num(number_format($Spese_Notifica_Terzo[$x_terzo],2));
								if($CAN_CAD_Terzo[$x_terzo]!=null)
									$CAN_CAD_Terzo[$x_terzo] = conv_num(number_format($CAN_CAD_Terzo[$x_terzo],2));

							}
								
						}
												
						if($stampa_select == "PEC")
						{
							$aggiungi_pec_pigno = "";
							$par_email = new parametri_email($c, $settore, "pec");
						
							if($par_email->Indirizzo_Email=="")
							{
								alert("INVIO TRAMITE PEC: L'indirizzo PEC da cui deve essere spedita la richiesta non e' stato inserito! Impossibile procedere con la stampa.");
								echo "<script>window.close();</script>";
							}
						
							for($y=0;$y<count($pignoramento->Notifiche_Debitore);$y++)
							{
								$notifica_debitore = $pignoramento->Notifiche_Debitore[$y];
									
								$array_dati_PEC['pec'] = $PEC_Utente;
								$array_dati_PEC['mail'] = "";
								$array_dati_PEC['file'] = $file_stampa_copia_debitore;
								$array_dati_PEC['file_old'] = "";
								$array_dati_PEC['file_relata'] = $file_base."_rel_debitore_".$y;
								$array_dati_PEC['identificativo'] = $identificativo_file;
								$array_dati_PEC['tipo_destinatario'] = "debitore";
								$array_dati_PEC['denominazione'] = $nome_utente;

echo "<script>update_mail(".ceil($l*100/$num_pignoramenti).",'PIGNORAMENTO N. ".$pignoramento->ID_Cronologico."/".$pignoramento->Anno_Cronologico." - Invio Debitore');</script>";
flush();
ob_flush();
flush();
ob_flush();

								$ritorno_notifica = $notifica_debitore->invio_notifica_PEC($notifica_debitore->ID, $c, $array_dati_PEC, $par_email, $partita->ID, $utente->ID, $pignoramento->ID);
									
								if($ritorno_notifica['contatore']>0)
									$cont_result += $ritorno_notifica['contatore'];
								if($ritorno_notifica['pigno_ID']!="" && $aggiungi_pec_pigno=="")
									$aggiungi_pec_pigno = $ritorno_notifica['pigno_ID'];
							}
						
							if($pignoramento->Tipo =="terzi")
							{
								for($z=0;$z<count($presso_terzi);$z++)
								{
									for($y=0;$y<count($presso_terzi[$z]->Notifiche_Terzo);$y++)
									{
										$notifica_terzo = $presso_terzi[$z]->Notifiche_Terzo[$y];
			
										$array_dati_PEC['pec'] = $PEC_terzo[$z];
										$array_dati_PEC['mail'] = "";
										$array_dati_PEC['file'] = $file_copia_terzo[$z];
										$array_dati_PEC['file_old'] = "";
										$array_dati_PEC['file_relata'] = $file_base."_rel_terzo_".$z."_".$y.".pdf";
										$array_dati_PEC['identificativo'] = $identificativo_file;
										$array_dati_PEC['tipo_destinatario'] = "terzo_".$z;
										$array_dati_PEC['denominazione'] = $nome_cognome_terzo[$z];
		
                                        $array_dati_PEC['body'] = "Allegato alla presente comunicazione trovate un documento esecutivo riferito ";
                                        $array_dati_PEC['body'].= "a ".$nome_utente." - ".$CF_PI."\n\n";
                                        $array_dati_PEC['body'].= "Qualora il soggetto suindicato non intrattenesse con Voi ";
                                        $array_dati_PEC['body'].= "rapporti di lavoro, ";
                                        $array_dati_PEC['body'].= "Vi preghiamo di comunicarcelo, stesso mezzo e di ritenere ";
                                        $array_dati_PEC['body'].= "nulla la comunicazione inviatavi in allegato e quindi provvedere a cestinarla, ";
                                        $array_dati_PEC['body'].= "senza aprirla, nel rispetto della privacy.";

echo "<script>update_mail(".ceil($l*100/$num_pignoramenti).",'PIGNORAMENTO N. ".$pignoramento->ID_Cronologico."/".$pignoramento->Anno_Cronologico." - Invio Terzo ".($z+1)."/".(count($presso_terzi))."');</script>";

flush();
ob_flush();
flush();
ob_flush();
										$ritorno_notifica = $notifica_terzo->invio_notifica_PEC($notifica_terzo->ID, $c, $array_dati_PEC, $par_email, $partita->ID, $utente->ID, $pignoramento->ID);
											
										if($ritorno_notifica['contatore']>0)
											$cont_result += $ritorno_notifica['contatore'];
										if($ritorno_notifica['pigno_ID']!="" && $aggiungi_pec_pigno=="")
											$aggiungi_pec_pigno = $ritorno_notifica['pigno_ID'];
									}
								}
							}
	
							if($aggiungi_pec_pigno!="")
								$array_PEC[] = $aggiungi_pec_pigno;
							
							$cont_result++;
							
							break;
						}
												
						//CARICAMENTO ATTO DI RIFERIMENTO DEL PIGNORAMENTO
						$atto_pignoramento = new atto($pignoramento->Atto_ID, $c);
						$pagamenti_atto = conv_num(number_format($atto_pignoramento->totale_pagamenti(),2));
						$dovuto_atto = conv_num(number_format($atto_pignoramento->Totale_Dovuto,2));						
						
						//TOTALI GENERALI
						$Importo_Dovuto = $pignoramento->Importo_Dovuto;
						$pignoramento->gestione_totali();
						$TOTALI_ARRAY = $pignoramento->Totali_Array;
						
						//COEFFICIENTE DI APPLICAZIONE
						$coeff = new coefficiente_coazione("*****", $Importo_Dovuto );
						$percentuale = $coeff->Percentuale;
						if($percentuale==null)	$percentuale = 0;
						
						$Tot_Spese_Notifica_Debitore = $pignoramento->Spese_Notifica_Debitore;
						if($Tot_Spese_Notifica_Debitore==null)	
							$Tot_Spese_Notifica_Debitore = conv_num($Spese_Notifica_Debitore);
						
						$Tot_Spese_Notifica_Terzi = $pignoramento->Spese_Notifica_Terzi;
						$Totale_Spese_Notifica = $pignoramento->Totale_Spese_Notifica;
						if($Totale_Spese_Notifica==null)	
							$Totale_Spese_Notifica = $Tot_Spese_Notifica_Debitore;
						
						$Totale_Spese_Accessorie = $pignoramento->Totale_Spese_Accessorie;
						$Totale_Dovuto = $pignoramento->Totale_Dovuto;
						if($Totale_Dovuto==null)	
							$Totale_Dovuto = $Importo_Dovuto + $Tot_Spese_Notifica_Debitore;
						
if($Importo_Dovuto!=null)				$Importo_Dovuto = conv_num(number_format($Importo_Dovuto,2));
if($Tot_Spese_Notifica_Debitore!=null)	$Tot_Spese_Notifica_Debitore = conv_num(number_format($Tot_Spese_Notifica_Debitore,2));
if($Tot_Spese_Notifica_Terzi!=null)		$Tot_Spese_Notifica_Terzi = conv_num(number_format($Tot_Spese_Notifica_Terzi,2));
if($Totale_Spese_Notifica!=null)		$Totale_Spese_Notifica = conv_num(number_format($Totale_Spese_Notifica,2));
if($Totale_Spese_Accessorie!=null)		$Totale_Spese_Accessorie = conv_num(number_format($Totale_Spese_Accessorie,2));
if($Totale_Dovuto!=null)				$Totale_Dovuto = conv_num(number_format($Totale_Dovuto,2));
						
						//SPESE PIGNORAMENTO
						$Spese_Pignoramento = $pignoramento->Spese_Pignoramento;
						$Spese_Array = $Spese_Pignoramento->spese_array();
						
						//TARIFFE COAZIONE
						$tariffe_coazione = new tariffe_coazione(null, $c);
						$tariffe_coazione->array_tariffe($c);
						$tariffe_una_tantum = $tariffe_coazione->Una_Tantum;
						for($i=0;$i<count($tariffe_una_tantum);$i++)
						{
							if($tariffe_una_tantum[$i]['Descrizione']=="Valutazione/Stima dei beni pignorati e formazione fascicolo")
								$stima_beni = conv_num(number_format($tariffe_una_tantum[$i]['Importo'],2));
						}
						
						/**
						 PARAMETRI TESTO PIGNORAMENTO
						 */
						
						$Titolo_Oggetto = $testo->Titolo_Oggetto;
						SostituisciTestoTraGraffe ($Titolo_Oggetto, "{IDCRONOLOGICO}", $ID_Cronologico);
						SostituisciTestoTraGraffe ($Titolo_Oggetto, "{ANNOCRONOLOGICO}", $Anno_Cronologico);
						
						$Sottotitolo_Oggetto = $testo->Sottotitolo_Oggetto;
						SostituisciTestoTraGraffe ($Sottotitolo_Oggetto, "{ATTO}", $info_avviso);
						SostituisciTestoTraGraffe ($Sottotitolo_Oggetto, "{INFOCARTELLA}", $info_cartella);
						
						$Ufficiale_Responsabile = $testo->Ufficiale_Responsabile;
						SostituisciTestoTraGraffe ($Ufficiale_Responsabile, "{GESTORE}", $gestore->Denominazione);
						SostituisciTestoTraGraffe ($Ufficiale_Responsabile, "{SEDEGESTORE}", $righe_gestore['Senza_Provincia']);
						
						$Abilitazione = $testo->Abilitazione;
						if($gestore->Tipo == "Concessionario")
							$Ufficiale_Responsabile.= $Abilitazione;
						
						$Premesso = $testo->Premesso;
						
						$Atti_Notificati = $testo->Atti_Notificati;
						SostituisciTestoTraGraffe ($Atti_Notificati, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Atti_Notificati, "{CFPI}", $CF_PI);
						if($utente->Genere=="D")
							$indirizzo_pignorato = "con sede in ".$indirizzo_senza_provincia;
						else
							$indirizzo_pignorato = "residente in ".$indirizzo_senza_provincia;
						
						SostituisciTestoTraGraffe ($Atti_Notificati, "{RESIDENZAUTENTE}", $indirizzo_pignorato);
						
						$Premesso_Testo = $testo->Premesso_Testo;
						SostituisciTestoTraGraffe ($Premesso_Testo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Premesso_Testo, "{ENTE}", "Comune di ".$comune->Nome);
						SostituisciTestoTraGraffe ($Premesso_Testo, "{DATACALCOLO}", $Data_Elaborazione);

						$Informazioni = $testo->Informazioni;
						$Informazioni_Testo = $testo->Informazioni_Testo;
						
						$Modalita_Pagamento = $testo->Modalita_Pagamento;
						$Modalita_Pagamento_Testo = $testo->Modalita_Pagamento_Testo;
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{NUMEROCONTO}", $numeroContoCorrente);
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{INTESTATARIOCONTO}", $intestatarioConto);
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{IBAN}", " (IBAN ".$iban.")");
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{CODICEUTENTE}", $codice_utente);
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{CRONOLOGICO}", $ID_Cronologico."/".$Anno_Cronologico);
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{RIFERIMENTO}", $partita->Comune_ID."/".$partita->Anno_Riferimento);
						SostituisciTestoTraGraffe ($Modalita_Pagamento_Testo, "{ENTE}", strtoupper("Comune di ".$comune->Nome));

						$Visto = $testo->Visto;
						$Ingiunzione_Fiscale = $testo->Ingiunzione_Fiscale;
						$Legislatore = $testo->Legislatore;
						
						$Considerato = $testo->Considerato;
						
						$Terzo = $testo->Terzo;
						SostituisciTestoTraGraffe ($Terzo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Terzo, "{TERZI}", $nomi_terzi);
						
						$Somme_Dovute = $testo->Somme_Dovute;
						SostituisciTestoTraGraffe ($Somme_Dovute, "{GESTORE}", $gestore->Denominazione);
						SostituisciTestoTraGraffe ($Somme_Dovute, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Somme_Dovute, "{TERZI}", $nomi_terzi);
						
						$Ordine_Pagamento = $testo->Ordine_Pagamento;
						
						$Opposizione = $testo->Opposizione;
						$Opposizione_Testo = $testo->Opposizione_Testo;
						
						$Autotutela = $testo->Autotutela;
						$Autotutela_Testo = $testo->Autotutela_Testo;
						SostituisciTestoTraGraffe ($Autotutela_Testo, "{UTENTE}", $nome_utente);
						
						$Luogo = $testo->Luogo;
						SostituisciTestoTraGraffe ($Luogo, "{DATASTAMPA}", from_mysql_date($data_stampa_file));
						
						$Intestazione_Firma_Sinistra = $testo->Intestazione_Firma_Sinistra;
						$Firma_Sinistra = $testo->Firma_Sinistra;
						
						$Intestazione_Firma_Destra = $testo->Intestazione_Firma_Destra;
						$Firma_Destra = $testo->Firma_Destra;
						
						//IMPOSTAZIONI FIRMA FUNZIONARIO
						$prime_firme[1]['intestazione'] 	= $firma_resp[2]['intestazione'];
						$prime_firme[1]['nome'] 			= $firma_resp[2]['nome'];
						$prime_firme[1]['firma'] 			= $firma_resp[2]['firma'];
							
						if(ucfirst($gestore->Tipo) == "Concessionario")
							$firma_1 = "Il rappresentante Legale";
						else
							$firma_1 = $firma_resp[1]['intestazione'];
						
						$prime_firme[2]['intestazione'] 	= $firma_1;
						$prime_firme[2]['nome'] 			= $firma_resp[1]['nome'];
						$prime_firme[2]['firma'] 			= $firma_resp[1]['firma'];
						
						//UFFICIALE
						$Intestazione_Relata_Ufficiale_Giudiziario = $testo->Intestazione_Relata_Ufficiale_Giudiziario;
						SostituisciTestoTraGraffe ($Intestazione_Relata_Ufficiale_Giudiziario, "{TRIBUNALE}", ucfirst($tribunale->Comune));
						$Sottointestazione_Relata_Ufficiale_Giudiziario = $testo->Sottointestazione_Relata_Ufficiale_Giudiziario;
						
						$Intestazione_Relata_Ufficiale_Riscossione = $testo->Intestazione_Relata_Ufficiale_Riscossione;
						$Sottointestazione_Relata_Ufficiale_Riscossione = $testo->Sottointestazione_Relata_Ufficiale_Riscossione;
						if($Ufficiale_Consegna == "giudiziario")
						{
							$Intestazione_Relata = $Intestazione_Relata_Ufficiale_Giudiziario;
							$Sottointestazione_Relata = $Sottointestazione_Relata_Ufficiale_Giudiziario;
							$testo_ufficiale = "Ufficiale Giudiziario addetto all'U.N.E.P. del Circondario del Tribunale di ".ucfirst($tribunale->Comune);
						}
						else if($Ufficiale_Consegna == "riscossione")
						{
							$Intestazione_Relata = $Intestazione_Relata_Ufficiale_Riscossione;
							$Sottointestazione_Relata = $Sottointestazione_Relata_Ufficiale_Riscossione;
							if($gestore->Tipo == "Concessionario")
								$denom_gestore = $gestore->Tipo." ".$gestore->Denominazione;
							else
								$denom_gestore = $gestore->Denominazione;
								
							$testo_ufficiale = "Ufficiale della Riscossione, su delega del ".$denom_gestore;
						}
						
						$Ufficiale_Pignoramento = $testo->Ufficiale_Pignoramento;
						SostituisciTestoTraGraffe ($Ufficiale_Pignoramento, "{GESTORE}", $gestore->Denominazione);
						SostituisciTestoTraGraffe ($Ufficiale_Pignoramento, "{UFFICIALE}", $testo_ufficiale);
						SostituisciTestoTraGraffe ($Ufficiale_Pignoramento, "{INGIUNZIONE}", $ing_completa);
						
						$Assoggetto_Pignoramento = $testo->Assoggetto_Pignoramento;
						$Assoggetto_Pignoramento_Testo = $testo->Assoggetto_Pignoramento_Testo;
						SostituisciTestoTraGraffe ($Assoggetto_Pignoramento_Testo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Assoggetto_Pignoramento_Testo, "{TERZI}", $terzi_pro_tempore);
												
						$Ordina = $testo->Ordina;
						$Ordina_Testo = $testo->Ordina_Testo;
						SostituisciTestoTraGraffe ($Ordina_Testo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{TERZI}", $nomi_terzi);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{UTENTE2}", $nome_utente);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{NUMEROCONTO}", $numeroContoCorrente);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{INTESTATARIOCONTO}", $intestatarioConto);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{IBAN}", " (IBAN ".$iban.")");
						SostituisciTestoTraGraffe ($Ordina_Testo, "{CODICEUTENTE}", $codice_utente);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{CRONOLOGICO}", $ID_Cronologico."/".$Anno_Cronologico);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{RIFERIMENTO}", $partita->Comune_ID."/".$partita->Anno_Riferimento);
						SostituisciTestoTraGraffe ($Ordina_Testo, "{ENTE}", strtoupper("Comune di ".$comune->Nome));
						
						$Informo = $testo->Informo;
						$Informo_Testo = $testo->Informo_Testo;
						SostituisciTestoTraGraffe ($Informo_Testo, "{UTENTE}", $nome_utente);
						
						$Informo_Notifica = $testo->Informo_Notifica;
						SostituisciTestoTraGraffe ($Informo_Notifica, "{SPESENOTIFICA}", conv_num($spese_notifica));
						SostituisciTestoTraGraffe ($Informo_Notifica, "{SPESEATTIGIUDIZIARI}", conv_num($spese_postali_ag));
						SostituisciTestoTraGraffe ($Informo_Notifica, "{CAN}", conv_num($CAN));
						SostituisciTestoTraGraffe ($Informo_Notifica, "{CAD}", conv_num($CAD));
						
						$Intimo = $testo->Intimo;
						$Intimo_Testo = $testo->Intimo_Testo;
						SostituisciTestoTraGraffe ($Intimo_Testo, "{TERZI}", $terzi_pro_tempore);
						SostituisciTestoTraGraffe ($Intimo_Testo, "{UTENTE}", $terzi_pro_tempore);
						
						$Informo_2 = $testo->Informo_2;
						$Informo_Testo_2 = $testo->Informo_Testo_2;
						SostituisciTestoTraGraffe ($Informo_Testo_2, "{TERZI}", $terzi_pro_tempore);						
						
						$Invito = $testo->Invito;
						$Invito_Testo = $testo->Invito_Testo;
						SostituisciTestoTraGraffe ($Invito_Testo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Invito_Testo, "{PECGESTORE}", $gestore->PEC);
						
						$Notifica_Pignoramento = $testo->Notifica_Pignoramento;

						$Relata_Notifica = $testo->Relata_Notifica;
						SostituisciTestoTraGraffe ($Relata_Notifica, "{UFFICIALE}", $testo_ufficiale);
						SostituisciTestoTraGraffe ($Relata_Notifica, "{NOTIFICATO}", "notificato");
						
						$Relata_Debitore = $testo->Relata_Debitore;
						SostituisciTestoTraGraffe ($Relata_Debitore, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Relata_Debitore, "{RESIDENZAUTENTE}", $indirizzo_senza_provincia);
						
						if($Ufficiale_Consegna == "giudiziario")
							SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "consegna a mani / servizio postale ai sensi di legge");
						else if($Tipo_Invio_Debitore=="posta")
							SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "servizio postale ai sensi di legge");
						else if($Tipo_Invio_Debitore=="mani")
							SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "consegna a mani");
						else if($Tipo_Invio_Debitore=="pec")
							SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "Posta Elettronica Certificata al seguente indirizzo ".$PEC_Utente." ai sensi di legge" );
						
						for($i=0;$i<count($presso_terzi);$i++)
						{
							$Relata_Terzo[$i] = $testo->Relata_Terzo;
							SostituisciTestoTraGraffe ($Relata_Terzo[$i], "{TERZO}", 	 $terzo_singolo[$i] );
							SostituisciTestoTraGraffe ($Relata_Terzo[$i], "{SEDETERZO}", $indirizzo_terzo[$i]['Senza_Provincia'] );
							
							if($Ufficiale_Consegna == "giudiziario")
								SostituisciTestoTraGraffe ($Relata_Terzo[$i], "{TIPOINVIO}", "consegna a mani / servizio postale ai sensi di legge");
							else if($Tipo_Invio_Terzo[$i]=="posta")
								SostituisciTestoTraGraffe ($Relata_Terzo[$i], "{TIPOINVIO}", "servizio postale ai sensi di legge");
							else if($Tipo_Invio_Terzo[$i]=="mani")
								SostituisciTestoTraGraffe ($Relata_Terzo[$i], "{TIPOINVIO}", "consegna a mani");
							else if($Tipo_Invio_Terzo[$i]=="pec")
								SostituisciTestoTraGraffe ($Relata_Terzo[$i], "{TIPOINVIO}", "Posta Elettronica Certificata al seguente indirizzo ".$PEC_terzo[$i]." ai sensi di legge" );
						}
						
						$Intestazione_Firma_Notifica = $testo->Intestazione_Firma_Notifica;
						$Firma_Notifica = $testo->Firma_Notifica;
						
						if($Ufficiale_Consegna == "giudiziario")
						{
							$firma_ufficiale['intestazione'] 	= "L'Ufficiale Giudiziario";
							$firma_ufficiale['nome'] 			= "";
							$firma_ufficiale['firma'] 			= "";
							
							$firma_ufficiale_copia['intestazione'] 	= "L'Ufficiale Giudiziario";
							$firma_ufficiale_copia['nome'] 			= "";
							$firma_ufficiale_copia['firma'] 		= "";
						}
						else if($Ufficiale_Consegna == "riscossione")
						{
							$firma_ufficiale['intestazione'] 	= "L'Ufficiale della Riscossione";
							$firma_ufficiale['nome'] 			= "";
							$firma_ufficiale['firma'] 			= "";
							
							$firma_ufficiale_copia['intestazione'] = $firma_resp[3]['intestazione'];
							$firma_ufficiale_copia['nome'] = $firma_resp[3]['nome'];
							$firma_ufficiale_copia['firma'] = $firma_resp[3]['firma'];
							
						}

if($stampa_select=="FLUSSO"){


    if($stato_stampa == "Da stampare")
    {

        $array_flusso = array();

        //FLUSSO GENERALE
        $array_flusso[] = $c;
        $array_flusso[] = "attoGiudiziario";
        $array_flusso[] = "pignoLavoro";
        $array_flusso[] = $pignoramento->ID_Cronologico;
        $array_flusso[] = $pignoramento->Anno_Cronologico;

        $array_flusso[] = $quinto_campo;
        $array_flusso[] = $stemma_image_file;

        //FLUSSO GESTORE

        $array_flusso[] = $intest_gestore['Riga1'];
        $array_flusso[] = $intest_gestore['Riga2'];
        $array_flusso[] = $intest_gestore['Riga3'];
        $array_flusso[] = $intest_gestore['Riga4'];
        $array_flusso[] = $intest_gestore['Riga5'];
        $array_flusso[] = $intest_gestore['Riga6'];
        $array_flusso[] = $intest_gestore['Riga7'];

        //FLUSSO UFFICIO

        $array_flusso[] = $intest_ufficio['Riga1'];
        $array_flusso[] = $intest_ufficio['Riga2'];
        $array_flusso[] = $intest_ufficio['Riga3'];
        $array_flusso[] = $intest_ufficio['Riga4'];
        $array_flusso[] = $intest_ufficio['Riga5'];
        $array_flusso[] = $intest_ufficio['Riga6'];

        //FLUSSO DESTINATARIO
        $array_flusso[] = "Partita numero: ".$ID_partita." / ".$anno_rif;
        $array_flusso[] = "Codice utente: ".$utente_id;

        $array_flusso[] = "Spett.le ".$indirizzo_destinatario['Destinatario'];
        $array_flusso[] = $indirizzo_destinatario['Riga1'];
        $array_flusso[] = $indirizzo_destinatario['Riga2'];
        $array_flusso[] = $indirizzo_destinatario['Riga3'];
        $array_flusso[] = $indirizzo_destinatario['Riga4'];


        $array_flusso[] = "OGGETTO: ".$Titolo_Oggetto;
        $array_flusso[] = $Sottotitolo_Oggetto;

        $array_flusso[] = $Ufficiale_Responsabile;
        $array_flusso[] = $Premesso;
        $array_flusso[] = $Atti_Notificati;

        $atti_precedenti = "";
        for($i=0;$i<count($atti_notificati);$i++){
            $atti_precedenti.= ($i+1).") ".$atti_notificati[$i];
            if($i<count($atti_notificati)-1)
                $atti_precedenti.= " - ";
        }

        $array_flusso[] = $atti_precedenti;
        $array_flusso[] = $Premesso_Testo;

        $array_flusso[] = "Ripresa totale debito precedente";
        $array_flusso[] = $dovuto_atto;
        $array_flusso[] = "Eventuale importo pagato succ. alla notifica degli atti ingiuntivi e intimativi";
        $array_flusso[] = $pagamenti_atto;
        $array_flusso[] = "Totale debito precedente";
        $array_flusso[] = $Importo_Dovuto;

        //AL MOMENTO SONO 3 VOCI DI SPESA
        for($x_spesa=1;$x_spesa<count($Spese_Array)+1;$x_spesa++)
        {
            if($Spese_Array[$x_spesa]['tipo_totale']==1)
            {
                $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$Spese_Array[$x_spesa]['ID']."'";
                $descrizione_tariffa = single_query($query_tariffa);

                $array_flusso[] = $descrizione_tariffa;
                $array_flusso[] = number_format($Spese_Array[$x_spesa]['rimborso'],2,",",".");
            }
        }

        $array_flusso[] = "Spese postali/diritti di notifica";
        $array_flusso[] = $Totale_Spese_Notifica;
        $array_flusso[] = "TOTALE 1";
        $array_flusso[] = $TOTALI_ARRAY[1];

        $array_flusso[] = $Informazioni;
        $array_flusso[] = $Informazioni_Testo;

        $array_flusso[] = $Modalita_Pagamento;
        $array_flusso[] = $Modalita_Pagamento_Testo;

        $array_flusso[] = $Visto;
        $array_flusso[] = $Ingiunzione_Fiscale;
        $array_flusso[] = $Legislatore;

        $array_flusso[] = $Considerato;
        $array_flusso[] = $Terzo;
        $array_flusso[] = $Somme_Dovute;
        $array_flusso[] = $Ordine_Pagamento;

        $array_flusso[] = $Opposizione;
        $array_flusso[] = $Opposizione_Testo;

        $array_flusso[] = $Autotutela;
        $array_flusso[] = $Autotutela_Testo;

        $array_flusso[] = $Luogo;
        $array_flusso[] = $prime_firme[1]['intestazione'];
        $array_flusso[] = $prime_firme[1]['nome'];

        if($prime_firme[1]['firma']==$testo_firma){
            $array_flusso[] = "";
            $array_flusso[] = $prime_firme[1]['firma'];
        }
        else{
            $array_flusso[] = $prime_firme[1]['firma'];
            $array_flusso[] = "";
        }

        $array_flusso[] = $prime_firme[2]['intestazione'];
        $array_flusso[] = $prime_firme[2]['nome'];

        if($prime_firme[2]['firma']==$testo_firma){
            $array_flusso[] = "";
            $array_flusso[] = $prime_firme[2]['firma'];
        }
        else{
            $array_flusso[] = $prime_firme[2]['firma'];
            $array_flusso[] = "";
        }

        $array_flusso[] = $Ufficiale_Pignoramento;

        $array_flusso[] = $Assoggetto_Pignoramento;
        $array_flusso[] = $Assoggetto_Pignoramento_Testo;

        $array_flusso[] = $Ordina;
        $array_flusso[] = $Ordina_Testo;

        $array_flusso[] = $Informo;
        $array_flusso[] = $Informo_Testo;
        $array_flusso[] = $Informo_Notifica;

        $array_flusso[] = $Intimo;
        $array_flusso[] = $Intimo_Testo;

        $array_flusso[] = $Informo_2;
        $array_flusso[] = $Informo_Testo_2;

        $array_flusso[] = $Invito;
        $array_flusso[] = $Invito_Testo;
//        $array_flusso[] = $Invito_Testo_2;

        $array_flusso[] = $Notifica_Pignoramento;
        $array_flusso[] = $Intestazione_Relata;
        $array_flusso[] = $Sottointestazione_Relata;

        $array_flusso[] = $Relata_Notifica;
        $array_flusso[] = $Relata_Debitore;

        $array_flusso[] = $firma_ufficiale_copia['intestazione'];
        $array_flusso[] = $firma_ufficiale_copia['nome'];

        if($firma_ufficiale_copia['firma']==$testo_firma){
            $array_flusso[] = "";
            $array_flusso[] = $firma_ufficiale_copia['firma'];
        }
        else{
            $array_flusso[] = $firma_ufficiale_copia['firma'];
            $array_flusso[] = "";
        }

        //FLUSSO BOLLETTINO 1
        if($autorizzazione_1!=false || $td_1=="123")
        {
            if($stemma == "")
                $stemma_bol_1 = $image_file;
            else if($stemma == "ente")
                $stemma_bol_1 = $stemmaComune;
            else if($stemma == "gestore")
                $stemma_bol_1 = $stemmaGestore;

            if($td_1=="896")
            {
                $dovuto_bollettino = $TOTALI_ARRAY[1];
                $dovuto_letterale = "";
            }
            else if($ctrl_importo_1=="si")
            {
                $dovuto_bollettino = $TOTALI_ARRAY[1];
                $dovuto_letterale = $numeroLetterale_1;
            }
            else
            {
                $dovuto_bollettino = "";
                $dovuto_letterale = "";
            }

            $array_flusso[] = $stemma_bol_1;
            $array_flusso[] = $td_1;
            $array_flusso[] = $autorizzazione_1;
            $array_flusso[] = $numeroContoCorrente;
            $array_flusso[] = $intestatarioConto;
            $array_flusso[] = $iban;
            $array_flusso[] = $dovuto_bollettino;
            $array_flusso[] = $dovuto_letterale;
            $array_flusso[] = $indirizzo_destinatario['Destinatario'];

            $riga_flusso2 = strtoupper($indirizzo_destinatario['Riga1'].$indirizzo_destinatario['Riga2']);
            if($indirizzo_destinatario['Riga4']!="")
                $riga_flusso3 = strtoupper($indirizzo_destinatario['Riga3'].", ".$indirizzo_destinatario['Riga4']);
            else
                $riga_flusso3 = strtoupper($indirizzo_destinatario['Riga3']);

            $array_flusso[] = $riga_flusso2;
            $array_flusso[] = $riga_flusso3;
            $array_flusso[] = $riga1causale;
            $array_flusso[] = $riga2causale;
            $array_flusso[] = $pdf->set_quinto_campo( $td_1, $quinto_campo);
            $array_flusso[] = $pdf->set_quinto_campo( $td_1, $quinto_campo, true);
            $array_flusso[] = $pdf->barcode_importo_bollettino($td_1, $TOTALI_ARRAY[1]);
            $array_flusso[] = $pdf->barcode_conto_bollettino($td_1, $numeroContoCorrente);
            $array_flusso[] = $td_1.">";
        }
        else
        {
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
        }

        //FLUSSO BOLLETTINO 2
        if($autorizzazione_2!=false || $td_2=="123")
        {
            if($stemma_2 == "")
                $stemma_bol_2 = $image_file;
            else if($stemma_2 == "ente")
                $stemma_bol_2 = $stemmaComune;
            else if($stemma_2 == "gestore")
                $stemma_bol_2 = $stemmaGestore;

            if($td_2=="896")
            {
                $dovuto_bollettino = $TOTALI_ARRAY[2];
                $dovuto_letterale = "";
            }
            else if($ctrl_importo_2=="si")
            {
                $dovuto_bollettino = $TOTALI_ARRAY[2];
                $dovuto_letterale = $numeroLetterale_2;
            }
            else
            {
                $dovuto_bollettino = "";
                $dovuto_letterale = "";
            }

            $array_flusso[] = $stemma_bol_2;
            $array_flusso[] = $td_2;
            $array_flusso[] = $autorizzazione_2;
            $array_flusso[] = $numeroContoCorrente;
            $array_flusso[] = $intestatarioConto;
            $array_flusso[] = $iban;
            $array_flusso[] = $dovuto_bollettino;
            $array_flusso[] = $dovuto_letterale;
            $array_flusso[] = $indirizzo_destinatario['Destinatario'];

            $riga_flusso2 = strtoupper($indirizzo_destinatario['Riga1'].$indirizzo_destinatario['Riga2']);
            if($indirizzo_destinatario['Riga4']!="")
                $riga_flusso3 = strtoupper($indirizzo_destinatario['Riga3'].", ".$indirizzo_destinatario['Riga4']);
            else
                $riga_flusso3 = strtoupper($indirizzo_destinatario['Riga3']);

            $array_flusso[] = $riga_flusso2;
            $array_flusso[] = $riga_flusso3;
            $array_flusso[] = $riga1causale;
            $array_flusso[] = $riga2causale;
            $array_flusso[] = $pdf->set_quinto_campo( $td_2, $quinto_campo);
            $array_flusso[] = $pdf->set_quinto_campo( $td_2, $quinto_campo, true);
            $array_flusso[] = $pdf->barcode_importo_bollettino($td_2, $TOTALI_ARRAY[2]);
            $array_flusso[] = $pdf->barcode_conto_bollettino($td_2, $numeroContoCorrente);
            $array_flusso[] = $td_2.">";
        }
        else
        {
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
            $array_flusso[] = "";
        }

        $array_flusso[] = $par_generali->Intestatario_SMA;
        $array_flusso[] = $par_generali->Numero_SMA;
        $array_flusso[] = $par_generali->Testo_Spese_Anticipate;

        $array_flusso[] = $par_generali->Restituzione1;
        $array_flusso[] = $par_generali->Restituzione2;
        $array_flusso[] = $par_generali->Restituzione3;
        $array_flusso[] = $par_generali->Restituzione4;
        $array_flusso[] = $par_generali->Restituzione5;

        $myFlusso->AggiungiRigaFlusso($array_flusso);

        $salva = new pignoramento($pignoramento->ID, $c);

        $salva->Data_Flusso = $myFlusso->myData;
        $salva->Anno_Flusso = $myFlusso->myAnno;
        $salva->Numero_Flusso = $myFlusso->myNumero;

        $control_salva = $salva->Update($pignoramento->ID);

    }
}
else {

    $pdf_copia_terzo = array();
    $pdf_relata_terzo = array();
    for ($ncop = 0; $ncop < count($presso_terzi) + 2; $ncop++) {
        if ($ncop == 0)
            $tipo_copia = "ORIGINALE";
        else if ($ncop == 1)
            $tipo_copia = "COPIA DEBITORE";
        else
            $tipo_copia = "COPIA TERZO";

        /**
         * ///////////////////////////////        PDF        //////////////////////////////////
         */
        if ($stampa_select == "DEFINITIVA")
            $pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetLineWidth(0.2);
        $pdf->SetMargins(7.0, 7.0, 7.0);
        $width_page = $pdf->getPageWidth() - 7;

        /**
         *        //////////////    PAGINA 1    //////////////
         */

        $pdf->SetCellPadding(0);
        $pdf->AddPage('P');

//////////////	CORPO Pagina 1	//////////////					
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(7, 36);
        $pdf->MultiCell(0, 0, $tipo_copia, 0, 'L', 0, 1);
        $pdf->Line(7, 84, 90, 84);//Linea di chiusura
        $pdf->SetXY(7, 81.5);
        $pdf->Cell(0, 5, "INIZIO ATTO", 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->Line(120, 84, 203, 84);//Linea di chiusura

        $pdf->intestazione_pdf($tipo_gestore, $image_file, $intest_gestore, $intest_ufficio);

        if ($ncop == 0)
            $pdf->destinatario_intestazione_pdf($utente_id, $c, "", $ID_partita, $anno_rif, "", "");
        else if ($ncop == 1)
            $pdf->destinatario_intestazione_pdf($utente_id, $c, $nome_utente, $ID_partita, $anno_rif, $indirizzo_destinatario, "");
        else if ($ncop > 1) {
            $pdf->destinatario_intestazione_pdf($utente_id, $c, $nome_cognome_terzo[$ncop - 2], $ID_partita, $anno_rif, $indirizzo_terzo[$ncop - 2], "");
        }

        $pdf->oggetto_pdf($Titolo_Oggetto, $Sottotitolo_Oggetto, "");

        //PREMESSO
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Ufficiale_Responsabile . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Premesso, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Atti_Notificati . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 8.5);
        for ($i = 0; $i < count($atti_notificati); $i++)
            $pdf->MultiCell(0, 0, ($i + 1) . ") " . $atti_notificati[$i] . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Premesso_Testo . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        //IMPORTI INGIUNZIONE
        $array_width = array(160, 10, 10, 15);
        $array_align = array("R", "C", "R", "R");
        $tot = array_sum($array_width);
        $margine = $pdf->getMargins();

        $array_value = array("Ripresa totale debito precedente", "", "Euro", $dovuto_atto);
        crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
        $pdf->Ln(1);
        $array_value = array("Eventuale importo pagato successivamente alla notifica degli atti ingiuntivi e intimativi", "-", "Euro", $pagamenti_atto);
        crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
        $pdf->Ln(1);
        $pdf->Line($pdf->getX(), $pdf->getY(), ($tot + $margine['left']), $pdf->getY());
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 8.5);
        $array_value = array("Totale debito precedente", "=", "Euro", $Importo_Dovuto);
        crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);

        for ($x_spesa = 1; $x_spesa < count($Spese_Array) + 1; $x_spesa++) {
            $pdf->SetFont('Arial', '', 8.5);
            if ($Spese_Array[$x_spesa]['tipo_totale'] == 1) {
                $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '" . $Spese_Array[$x_spesa]['ID'] . "'";
                $descrizione_tariffa = single_query($query_tariffa);

                $pdf->Ln(1);
                $array_value = array($descrizione_tariffa, "+", "Euro", conv_num($Spese_Array[$x_spesa]['rimborso']));
                crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
            }
        }

        $pdf->Ln(1);
        $array_value = array("Spese postali/diritti di notifica", "+", "Euro", $Totale_Spese_Notifica);
        crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);

        $pdf->Ln(1);
        $pdf->Line($pdf->getX(), $pdf->getY(), ($tot + $margine['left']), $pdf->getY());
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 8.5);
        $array_value = array("TOTALE 1", "=", "Euro", $TOTALI_ARRAY[1]);
        crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);

        if ($TOTALI_ARRAY[2] != 0) {

            $pdf->SetFont('Arial', '', 8.5);
            for ($x_spesa = 1; $x_spesa < count($Spese_Array) + 1; $x_spesa++) {
                if ($Spese_Array[$x_spesa]['tipo_totale'] == 2) {
                    $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '" . $Spese_Array[$x_spesa]['ID'] . "'";
                    $descrizione_tariffa = single_query($query_tariffa);

                    $pdf->Ln(1);
                    $array_value = array($descrizione_tariffa, "+", "Euro", conv_num($Spese_Array[$x_spesa]['rimborso']));
                    crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
                }
            }

            $pdf->Ln(1);
            $pdf->Line($pdf->getX(), $pdf->getY(), ($tot + $margine['left']), $pdf->getY());
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 8.5);
            $array_value = array("TOTALE 2", "=", "Euro", $TOTALI_ARRAY[2]);
            crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
        }

        if ($TOTALI_ARRAY[3] != 0) {

            $pdf->SetFont('Arial', '', 8.5);
            for ($x_spesa = 1; $x_spesa < count($Spese_Array) + 1; $x_spesa++) {
                if ($Spese_Array[$x_spesa]['tipo_totale'] == 3) {
                    $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '" . $Spese_Array[$x_spesa]['ID'] . "'";
                    $descrizione_tariffa = single_query($query_tariffa);

                    $pdf->Ln(1);
                    $array_value = array($descrizione_tariffa, "+", "Euro", conv_num($Spese_Array[$x_spesa]['rimborso']));
                    crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
                }
            }

            $pdf->Ln(1);
            $pdf->Line($pdf->getX(), $pdf->getY(), ($tot + $margine['left']), $pdf->getY());
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 8.5);
            $array_value = array("TOTALE 3", "=", "Euro", $TOTALI_ARRAY[3]);
            crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);

        }

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Informazioni, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Informazioni_Testo . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Modalita_Pagamento, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Modalita_Pagamento_Testo . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Visto, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Ingiunzione_Fiscale . "\n", 0, 'J', 0, 1);
        $pdf->MultiCell(0, 0, $Legislatore . "\n", 0, 'J', 0, 1);

//////////////	FINE CORPO Pagina 1	//////////////

        //PIE DI PAGINA 1
        $pdf->SetY(-10);
        $pdf->SetFont('helvetica', 'N', 7);
        $pdf->Cell(0, 5, "Pag. 1/2 - " . date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        if ($stampa_select == "PROVVISORIA")
            $pdf->stampa_provvisoria();

        /**
         *        //////////////    PAGINA 2    //////////////
         */

        $pdf->SetCellPadding(0);
        $pdf->AddPage('P');

        //////////////	CORPO Pagina 2	//////////////

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Considerato, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Terzo . "\n", 0, 'J', 0, 1);
        $pdf->MultiCell(0, 0, $Somme_Dovute . "\n", 0, 'J', 0, 1);
        $pdf->MultiCell(0, 0, $Ordine_Pagamento . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Opposizione, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Opposizione_Testo . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Autotutela, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Autotutela_Testo . "\n", 0, 'J', 0, 1);

        $pdf->Ln(1);
        $pdf->Cell(60, 0, $Luogo, 0, 1, 'C', 0, '', 0);

        if ($ncop == 0)
            $pdf->firma_pdf($prime_firme, "no");
        else
            $pdf->firma_pdf($prime_firme);

        $pdf->Ln(1);

        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Ufficiale_Pignoramento . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Assoggetto_Pignoramento, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Assoggetto_Pignoramento_Testo . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Ordina, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Ordina_Testo . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Informo, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Informo_Testo . "\n", 0, 'J', 0, 1);
        $pdf->MultiCell(0, 0, $Informo_Notifica . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Intimo, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Intimo_Testo . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Informo_2, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Informo_Testo_2 . "\n", 0, 'J', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Invito, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Invito_Testo . "\n", 0, 'J', 0, 1);


//////////////	FINE CORPO Pagina 2	//////////////		

        //PIE DI PAGINA 2
        $pdf->SetY(-10);
        $pdf->SetFont('helvetica', 'N', 7);
        $pdf->Cell(0, 5, "Pag. 2/2 - " . date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        if ($stampa_select == "PROVVISORIA") {
            $pdf->stampa_provvisoria();
        } else if ($stampa_select == "DEFINITIVA") {
            if ($ncop == 0)
                $pdf_originale = $pdf;
            else if ($ncop == 1)
                $pdf_copia_debitore = $pdf;
            else
                $pdf_copia_terzo[] = $pdf;
        }
    }

    for ($num_copia = 0; $num_copia < count($presso_terzi) + 2; $num_copia++) {
        if ($stampa_select == "DEFINITIVA")
            $pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCellPadding(0);

        $pdf->SetLineWidth(0.2);
        $pdf->SetMargins(7.0, 7.0, 7.0);
        $width_page = $pdf->getPageWidth() - 7;


        $pdf->SetCellPadding(0);
        $pdf->AddPage('P');

        if ($num_copia < count($presso_terzi) + 1) {
            $pdf->SetFont('Arial', '', 8.5);
            $pdf->MultiCell(0, 0, $Notifica_Pignoramento . "\n", 0, 'J', 0, 1);
            $pdf->Ln(2);
        }

        //RELAZIONE
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(0, 0, $Intestazione_Relata, 0, 'C', 0, 1);
        if ($Sottointestazione_Relata != "")
            $pdf->MultiCell(0, 0, $Sottointestazione_Relata, 0, 'C', 0, 1);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(0, 0, $Relata_Notifica . "\n", 0, 'J', 0, 1);
        $pdf->Ln(2);

        if ($num_copia < count($presso_terzi)) {
            $pdf->MultiCell(0, 0, $Relata_Terzo[$num_copia] . "\n", 0, 'J', 0, 1);
            $pdf->Ln(2);

            $pdf->firma_destra($firma_ufficiale_copia);
        } else if ($num_copia < count($presso_terzi) + 1) {
            $pdf->MultiCell(0, 0, $Relata_Debitore . "\n", 0, 'J', 0, 1);
            $pdf->Ln(2);

            $pdf->firma_destra($firma_ufficiale_copia);
        } else {
            $pdf->MultiCell(0, 0, $Relata_Debitore . "\n", 0, 'J', 0, 1);
            $pdf->Ln(2);

            for ($i = 0; $i < count($presso_terzi); $i++) {
                $pdf->MultiCell(0, 0, $Relata_Terzo[$i] . "\n", 0, 'J', 0, 1);
                $pdf->Ln(2);
            }

            $pdf->firma_destra_senza_img($firma_ufficiale_copia);
        }

        if ($stampa_select == "PROVVISORIA")
            $pdf->stampa_provvisoria();

        $pdf->SetCellPadding(0);
        $pdf->AddPage('P');

        if ($stampa_select == "PROVVISORIA")
            $pdf->stampa_provvisoria();
        else if ($stampa_select == "DEFINITIVA") {
            if ($num_copia < count($presso_terzi)) {
                $pdf_relata_terzo[$num_copia] = $pdf;
            } else if ($num_copia < count($presso_terzi) + 1) {
                $pdf_relata_debitore = $pdf;
            } else {
                $pdf_relata_originale = $pdf;
            }
        }
    }

    /**
     *        //////////////    PAGINA BOLLETTINO    //////////////
     */
    if (($autorizzazione_1 != false || $td_1 == "123") || ($autorizzazione_2 != false || $td_2 == "123")) {
        if ($stampa_select == "DEFINITIVA")
            $pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCellPadding(0);
        $pdf->SetMargins(0, 0, 0);
        $pdf->AddPage('L');

        if ($autorizzazione_1 != false || $td_1 == "123") {
            $pdf->crea_bollettino();
            if ($stemma == "")
                $pdf->logo_bollettino($image_file);
            else if ($stemma == "ente")
                $pdf->logo_bollettino($stemmaComune);
            else if ($stemma == "gestore")
                $pdf->logo_bollettino($stemmaGestore);
            $pdf->scelta_td_bollettino($td_1, $quinto_campo, $TOTALI_ARRAY[1], $ctrl_importo_1, $numeroContoCorrente);
            $pdf->iban_bollettino($iban);
            $pdf->intestatario_bollettino($intestatarioConto);
            $pdf->causale_bollettino($riga1causale, $riga2causale);
            $pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario);
            $pdf->autorizzazione_bollettino($autorizzazione_1);
        }

        if ($autorizzazione_2 != false || $td_2 == "123") {
            $pdf->crea_bollettino_inverso();
            if ($stemma_2 == "")
                $pdf->logo_bollettino($image_file, 'due');
            else if ($stemma_2 == "ente")
                $pdf->logo_bollettino($stemmaComune, 'due');
            else if ($stemma_2 == "gestore")
                $pdf->logo_bollettino($stemmaGestore, 'due');
            $pdf->scelta_td_bollettino($td_2, $quinto_campo, $TOTALI_ARRAY[2], $ctrl_importo_2, $numeroContoCorrente, 'due');
            $pdf->iban_bollettino($iban, 'due');
            $pdf->intestatario_bollettino($intestatarioConto, 'due');
            $pdf->causale_bollettino($riga1causale, $riga2causale, 'due');
            $pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario, 'due');
            $pdf->autorizzazione_bollettino($autorizzazione_2, 'due');
        }

        if ($stampa_select == "PROVVISORIA")
            $pdf->stampa_provvisoria();

        /**
         *        //////////////    PAGINA VUOTA    //////////////
         */

        $pdf->setPrintHeader(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCellPadding(0);
        $pdf->AddPage('L');

        if ($stampa_select == "PROVVISORIA")
            $pdf->stampa_provvisoria();
        else if ($stampa_select == "DEFINITIVA")
            $pdf_bollettino = $pdf;
    }

    /**
     * //////////////////////////////////////////////////////////////////////////////
     */
}

	if($stampa_select=="DEFINITIVA")
	{
		mysql_query('BEGIN');
			
		$salva = new pignoramento($pignoramento->ID, $c);
			
		if($salva->Stato_Stampa != "Stampato")
		{
			$salva->Data_Stampa = $data_stampa_file;
			$salva->Stato_Stampa = "Stampato";
	
			$control_salva = $salva->Update($pignoramento->ID);
				
			if( $control_salva )
			{
				mysql_query('COMMIT');
				
				$pdf_originale->Output( $file_stampa_originale , 'F');
				$pdf_relata_originale->Output( $file_relata_originale , 'F');
				
				$pdf_copia_debitore->Output( $file_stampa_copia_debitore , 'F');
				$pdf_relata_debitore->Output( $file_relata_debitore , 'F');
				$pdf_bollettino->Output( $file_bollettino , 'F');
				
				
				if($sel_originale == "si")
				{
					$arrayConcat[] = $file_stampa_originale;
					$arrayConcat[] = $file_relata_originale;
				}
					
				if($sel_debitore == "si")
				{
					$arrayConcat[] = $file_stampa_copia_debitore;
					$arrayConcat[] = $file_relata_debitore;
				}
					
				if($sel_bollettino == "si")
					$arrayConcat[] = $file_bollettino;
					
				
				for($i=0;$i<count($presso_terzi);$i++)
				{
					$pdf_copia_terzo[$i]->Output( $file_copia_terzo[$i] , 'F');
					$pdf_relata_terzo[$i]->Output( $file_relata_terzo[$i] , 'F');
						
					if($sel_terzi == "si")
					{
						$arrayConcat[] = $file_copia_terzo[$i];
						$arrayConcat[] = $file_relata_terzo[$i];
					}
				}
						
			}
			else
			{
				mysql_query('ROLLBACK');
			}
		}
	
	}
	
// fine PDF
						
							$cont_result++;
							
							break;		//Una partita pu� avere un solo intestatario per cui una volta trovato si pu� uscire dal ciclo degli utenti		
									
					}//CHIUSURA IF PARTITA/UTENTE
	
				}//CHIUSURA FOR UTENTI
			
				break;		//Un atto pu� corrispondere ad una sola partita per cui una volta trovato si pu� uscire dal ciclo delle partite
				
			}//CHIUSURA IF ATTO/PARTITA
				
		}//CHIUSURA PARTITE
			
	}//CHIUSURA ATTI

    if ($stampa_select == "FLUSSO")
    {
        if($cont_result == 0)
        {
            echo "<script>nessun_risultato();</script>";
        }
        else
        {

            if($stato_stampa == "Da stampare")
            {

                mysql_query('COMMIT');

                if($PrinterId>1){
                    $myFlusso->AllegaImmagine($percorso_stemma_comune);
                    $myFlusso->AllegaImmagine($percorso_stemma_gestore);
                    if($firme_responsabili['Funzionario_Testo']!="si")
                        $myFlusso->AllegaImmagine($firme_responsabili['Funzionario_Path']);
                    if($firme_responsabili['Responsabile_Testo']!="si")
                        $myFlusso->AllegaImmagine($firme_responsabili['Responsabile_Path']);
                    if($firme_responsabili['Ufficiale_Testo']!="si")
                        $myFlusso->AllegaImmagine($firme_responsabili['Ufficiale_Path']);
                }

                $myFlusso->PrinterId = $a_printerParams['PrinterId'];
                $myFlusso->PrintTypeId = $a_printerParams['MailTypeId'];
                $myFlusso->PrintCost = $a_printerParams['PrintCost'];
                $myFlusso->Zone0Postage = $a_printerParams['Zone0Postage'];
                $myFlusso->Zone1Postage = $a_printerParams['Zone1Postage'];
                $myFlusso->Zone2Postage = $a_printerParams['Zone2Postage'];
                $myFlusso->Zone3Postage = $a_printerParams['Zone3Postage'];
                $myFlusso->TaxType = $TaxTypeId;

                $myFlusso->ChiudiFlusso($PrinterId);

            }


            echo "<form name='flusso_form' id='flusso_form' method='post' action='gestione_flussi.php'>";
            echo "<input type=hidden name=tipo_atto value='Pignoramento'>";
            echo "<input type=hidden name='c' value=".$c.">";
            echo "<input type=hidden name='a' value=".$a.">";
            echo "<input type=hidden name='control_pigno' value='si'>";

            for($t=0; $t<count($array_stampati);$t++)
            {
                echo "<input type=hidden name=array_flussi[] value='".$array_stampati[$t]."'>";
            }

            echo "</form>";

            echo "<script>fine2('Elaborazione completata');</script>";
            echo "<script>atti_stampati('');</script>";

        }
    }
	else if($stampa_select=="PROVVISORIA")
	{
		cancella_files($stampa_dir, 7);
		if($cont_result == 0)
		{
			echo "<script>nessun_risultato();</script>";
		}
		else
		{
			flush(); ob_flush(); flush(); ob_flush();
			echo "<script>fine('Elaborazione completata');</script>";
			flush(); ob_flush(); flush(); ob_flush();
			
			
			$pdf->Output( $file_stampa , 'F');
		}	
	
	}
	else if($stampa_select == "DEFINITIVA")
	{
		cancella_files($concat_dir, 7);
		if($cont_result == 0)
		{
			echo "<script>nessun_risultato();</script>";
		}
		else
		{
			function getmicrotime(){
				list($usec, $sec) = explode(" ",microtime());
				return ((float)$usec + (float)$sec);
			}
			
			$fileCompletoUnito = $concat_dir."/Pignoramento_presso_lavoro_Merge_".$c."_".$data_file."_".$ora_file.".pdf";
			
			echo "<script>fine2('Elaborazione completata');merge();</script>";
			flush(); ob_flush(); flush(); ob_flush();
			sleep(1);

			$mergepdf = new Concat_Pdf();
			$mergepdf->setFiles($arrayConcat);
						
			$time_start = getmicrotime();//sec iniziali
			$mergepdf->Concat(true);
			$time_end = getmicrotime();//sec finali
			$time = $time_end - $time_start;//differenza in secondi
			
			$tempo_previsto_sec = $time * 13;
			if($tempo_previsto_sec<55) 
				$tempo_previsto = "1 minuto";
			else	
				$tempo_previsto = floor($tempo_previsto_sec/60+1)." minuti";

			echo "<script>fine_merge(\"Creazione file in corso... Il tempo previsto per le operazioni e' di circa ".$tempo_previsto.".\");</script>";
			flush(); ob_flush(); flush(); ob_flush();
			
			set_time_limit(0);
			flush(); ob_flush(); flush(); ob_flush();
			$mergepdf->Output($fileCompletoUnito, "F");
			
			$vedi_file = mostra_file_path($fileCompletoUnito);
			
			echo "<script>fine_e_apri('Elaborazione completata',\"".$vedi_file."\");</script>";
		
		}
		
	}
	else if($stampa_select == "CRONOLOGICI")
	{
		if($cont_result == 0)
		{
			echo "<script>nessun_risultato();</script>";
		}
		else
		{
	
			echo "<form name='crono_form' id='crono_form' method='post' action='cronologici_pignoramento.php'>";
			echo "<input type=hidden name=pigno_val value='lavoro'>";
			echo "<input type=hidden name='c' value=".$c.">";
			echo "<input type=hidden name='a' value=".$a.">";
			
			for($t=0; $t<count($array_cronologici);$t++)
			{
				echo "<input type=hidden name=array_crono[] value='".$array_cronologici[$t]."'>";
			}
				
			echo "</form>";
	
			echo "<script>fine2('Elaborazione completata');</script>";
			echo "<script>cronologici('');</script>";
			
		}
	}
	else if($stampa_select == "PEC")
	{
		if($cont_result == 0)
		{
			echo "<script>nessun_risultato();</script>";
		}
		else
		{
// 			die;
		?>
			
			<form name='pec_form' id='pec_form' method='post' action='pec_pignoramento.php'>
			<input type=hidden name=pigno_val value='lavoro'>
			<input type=hidden name='c' value="<?php echo $c; ?>">
			<input type=hidden name='a' value="<?php echo $a; ?>">
			
			
	<?php 	for($t=0; $t<count($array_PEC);$t++)
			{?>
				<input type=hidden name=array_pec[] value="<?php echo $array_PEC[$t]; ?>">
	<?php 	}?>
					
			</form>
			
			<script>fine2('Elaborazione PEC effettuata!');</script>
			<script>gestione_email('');</script>
		
<?php 	}
	}?>

</body>
</html>