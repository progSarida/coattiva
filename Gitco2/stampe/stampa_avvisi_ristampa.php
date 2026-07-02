<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/flussi.php";
include CLASSI . "/pdf_con_bollettino.php";
include TCPDF . "/fpdi.php";
include CLASSI . "/numero_letterale.php";

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

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
$stemmaComune = $comune->Stemma_1;

$gestore = $comune->Gestore;
$tipo_gestore = $gestore->Tipo;

if($tipo_gestore == "Concessionario")
	$image_file = "/gitco2/immagini/sarida_logo.png";
else
	$image_file = $stemmaComune;

$percorso_image_file = $_SERVER['DOCUMENT_ROOT'].$image_file;

$intest_gestore = $gestore->intestazione_gestore("Riscossione coattiva", $nome_com);

$ufficio = $comune->Ufficio;
$intest_ufficio = $ufficio->intestazione_ufficio();

$data_file = date('Y-m-d');
$ora_file = date('H-i-s');
$vedi_file = "";

//PARAMETRI RESPONSABILI
$par_responsabili = new parametri_responsabili($c, "CDS");
$firme_responsabili = $par_responsabili->firme_responsabili();
$firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");
if($firma_resp[1]['firma']=="")
{
	alert('Parametri Responsabili CDS incompleti!');
	die;
}
if($firma_resp[2]['firma']=="")
{
	alert('Parametri Responsabili CDS incompleti!');
	die;
}
// if($firma_resp[3]['firma']=="")
// {
// 	alert('Parametri Responsabili CDS incompleti!');
// 	die;
// }

if($stampa_select == "PROVVISORIA")
{

	$stampa_dir = crea_dir( ATTI ."/". $c . "/Avvisi_di_intimazione/STAMPE PROVVISORIE" );

	$file_stampa = $stampa_dir."/Avvisi_di_intimazione_Provvisori_".$c."_".$data_file."_".$ora_file.".pdf";
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


function atti_stampati(value)
{
	atto_val = "Avviso di intimazione ad adempiere";	
	location.href="atti_stampati.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+atto_val+"&tipo_stampa=<?php echo $stampa_select; ?>&cluster="+value;
}

function cronologici(value)
{
	$('#crono_form').submit();
}

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
		<font class="titolo font18 text_center">Stampa Avvisi di intimazione ad adempiere</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div class="table_interna text_center" id="progressbar2" style="height:55px;"><div class="text_center" id="barlabel2"></div></div>
		
		</td>
	</tr>
</table>

<?php

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

$da_stampa = from_mysql_date(get_var('da_stampa'));
$a_stampa = from_mysql_date(get_var('a_stampa'));

$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

$stato_esec = get_var('stato_esec');
$stato_notif = get_var('stato_notif');
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
	$where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."' AND Flag_Blocco_Coazione != 'si' ";
	
$query_partita = da_a_partita( $c , $da_partita , $a_partita , $where_anno );
$array_partite = mysql_array( $query_partita );

/** 	SELEZIONE ATTI	*/
if( $stampa_select != "FLUSSO" )
{
	$campi_stati = array("atto.Stato" , "atto.Stato_Stampa" , "atto.Stato_Esecuzione");
	$valori_stati = array ( $stato_notif , $stato_stampa , $stato_esec );

	$query_stati = where_campi($campi_stati, $valori_stati);
}
else
{
	$campi_stati = array("atto.Stato" , "atto.Stato_Stampa" , "atto.Stato_Esecuzione");
	$valori_stati = array ( $stato_notif , "Stampato" , $stato_esec );

	$query_stati = where_campi($campi_stati, $valori_stati);

	if($stato_stampa == "Da stampare")
		$query_stati.= " AND ( atto.Data_Flusso = '0000-00-00' OR atto.Data_Flusso is null ) ";
	else if($stato_stampa == "Stampato")
		$query_stati.= " AND ( atto.Data_Flusso != '0000-00-00' AND atto.Data_Flusso is not null ) ";

}

$campi_array = array ("Data_Elaborazione" , "Data_Notifica" , "Data_Stampa" );
$array_da_data = array( to_mysql_date($da_elab) , to_mysql_date($da_notif) , to_mysql_date($da_stampa) );
$array_a_data = array( to_mysql_date($a_elab) , to_mysql_date($a_notif) , to_mysql_date($a_stampa) );

$query_date = da_a_data_array_order( $c , "Avviso di intimazione ad adempiere" , $campi_array , $array_da_data , $array_a_data , $query_stati, $ordinamento );
$array_atti = mysql_array($query_date);

$num_atti = count($array_atti);

$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

$anno_current = date("Y");
$array_stampati = array();
$array_cronologici = array();

if($stampa_select == "FLUSSO")
{
	if($stato_stampa=="Da stampare")
	{
		
	mysql_query('BEGIN');
	
	$flusso_dir = crea_dir( ATTI ."/". $c . "/Avvisi_di_intimazione/FLUSSI/" );

	//INTESTAZIONE FLUSSO
	$pdf = new clsPdf_("P", "mm", "A4", true, 'UTF-8', false);
	$array_intestazione = array();
	
	$array_intestazione[] = "CODICE_CATASTALE";
	$array_intestazione[] = "TIPOLOGIA_STAMPA";
	$array_intestazione[] = "TIPOLOGIA_ATTO";
	$array_intestazione[] = "ID_CRONOLOGICO";
	$array_intestazione[] = "ANNO_CRONOLOGICO";
	
	$array_intestazione[] = "QUINTO_CAMPO";
	
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
	
	$array_intestazione[] = "LUOGO_DATA";
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
	$array_intestazione[] = "INFO_CARTELLA";
	$array_intestazione[] = "TESTO_2";
	
	$array_intestazione[] = "INTIMA";
	$array_intestazione[] = "INTIMA_TESTO";
	$array_intestazione[] = "INTIMA_CASO_1";
	$array_intestazione[] = "INTIMA_CASO_2";
	$array_intestazione[] = "INTIMA_CASO_3";
	$array_intestazione[] = "INTIMA_VERSAMENTO";
	
	$array_intestazione[] = "INFORMAZIONI";
	$array_intestazione[] = "INFORMAZIONI_TESTO";
	$array_intestazione[] = "FINALE";
	
	$array_intestazione[] = "OPPOSIZIONE";
	$array_intestazione[] = "OPPOSIZIONE_TESTO";
	
	$array_intestazione[] = "FIRMA_INTESTAZIONE_SINISTRA";
	$array_intestazione[] = "FIRMA_SINISTRA";
	$array_intestazione[] = "FIRMA_NOME_SINISTRA";
	$array_intestazione[] = "FIRMA_INTESTAZIONE_DESTRA";
	$array_intestazione[] = "FIRMA_DESTRA";
	$array_intestazione[] = "FIRMA_NOME_DESTRA";
	$array_intestazione[] = "MODALITA_FIRMA";
	
	$array_intestazione[] = "RELAZIONE";
	$array_intestazione[] = "RELAZIONE_TESTO";
	$array_intestazione[] = "FIRMA_INTESTAZIONE_NOTIFICA";
	$array_intestazione[] = "FIRMA_NOTIFICA";
	$array_intestazione[] = "FIRMA_NOME_NOTIFICA";
	
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
	
	
	$myFlusso = new flussi ($flusso_dir, "flusso", "avvisi", $c, $anno_current, "ultimoFlusso atto", $data_file, $ora_file, "txt");
	
	$nomefiletxt = $myFlusso->GetNomeFlusso();
	
	$myFlusso->AggiungiIntestazioneFlusso($array_intestazione);
	
	}
}
else
{
	if($stampa_select == "PROVVISORIA")
	{
		
		/**
		 ///////////////////////////////		PDF	    //////////////////////////////////
		*/

		$pdf = new clsPdf_("P", "mm", "A4", true, 'UTF-8', false);
		$pdf->SetLineWidth(0.2);
		$pdf->SetMargins(7.0, 10.0, 7.0);
		$width_page = $pdf->getPageWidth() - 7;
		
		/**
		 //////////////////////////////////////////////////////////////////////////////
		 */
	}
	else if($stampa_select == "DEFINITIVA")
	{
		$stampa_dir = crea_dir( ATTI ."/". $c . "/Avvisi_di_intimazione/STAMPE DEFINITIVE" );
		$concat_dir = crea_dir( ATTI ."/". $c . "/Avvisi_di_intimazione/STAMPE CONCATENATE" );
		$arrayConcat = Array();
	}

}
		
	$cont_result = 0;
	for( $l=0; $l < $num_atti; $l++ )//FOR ATTI
	{	
		set_time_limit(60);
		echo "<script>update(".ceil($l*100/$num_atti).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
		{			
			if( $array_atti[$l]['Partita_ID'] == $array_partite[$k]['ID'] )//IF ATTO/PARTITA
			{				
				for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
				{
					if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF PARTITA/UTENTE
					{
						set_time_limit(60);
						
						//AVVISO
						$avviso = new atto( $array_atti[$l]['ID'], $c );
						
						//ESCLUSIONI
						if($stampa_select == "PROVVISORIA")
						{
							if($avviso->Data_Notifica != null && $avviso->Data_Notifica != '0000-00-00')
								break;
						}
						else if($stampa_select == "CRONOLOGICI")
						{
							if($avviso->ID_Cronologico == "0" && $avviso->Anno_Cronologico == "0" && $avviso->Cronologico_Vecchio != "si")
							{
								$array_cronologici[] = $avviso->ID;
								$cont_result++;
							}
						
							break;
						}
						else if($stampa_select == "DEFINITIVA")
						{
							if($avviso->ID_Cronologico == "0" || $avviso->Anno_Cronologico == "0" || $avviso->Cronologico_Vecchio == "si")							
								break;
							
// 							if($stato_stampa == "Stampato")
// 							{
// 								if($avviso->Cronologico_Vecchio!="si")
// 								{
// 									$file_stampa_singola = $stampa_dir."/Avviso_di_intimazione_".$c."_".$anno_crono."_".$id_crono."_".to_mysql_date($data_stampa).".pdf";
// 									$arrayConcat[] = $file_stampa_singola;
							
// 									$array_stampati[] = $avviso->ID;
// 									$cont_result++;
// 								}
							
// 								break;
// 							}
						}
						else if($stampa_select == "FLUSSO")
						{
							if($avviso->Tipo_Ufficiale != "diretta" || $avviso->Modalita_Stampa != "posta")
								break;
						}
						
						
						//PARTITA
						$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
						$ID_partita = $partita->Comune_ID;
						$anno_rif = $partita->Anno_Riferimento;
						$settore = $partita->Tipo;
						
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
						$A_Mani = $parametri->A_Mani;
						$spese_ufficiale = conv_num(number_format($A_Mani,2));
						
						//PARAMETRI PAGAMENTO
						$par_pagamento = new parametri_pagamento( $c, $settore);
						$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
						$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
						$iban = $par_pagamento->IBAN;	//IBAN
						$autorizzazione_1 = $par_pagamento->testo_autorizzazione(1);//AUTORIZZAZIONE BOLLETTINO 1
						$autorizzazione_2 = $par_pagamento->testo_autorizzazione(2);//AUTORIZZAZIONE BOLLETTINO 2
						$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
						$td_2 = $par_pagamento->Bollettino_2;//TD BOLLETTINO 2
						$ctrl_importo_1 = $par_pagamento->Importo_1;
						$ctrl_importo_2 = $par_pagamento->Importo_2;
						$giorni_ing = $par_pagamento->Scadenza_Ingiunzione;
						$giorni_avv = $par_pagamento->Scadenza_Avviso;
						$riga2causale = "SCADENZA PAGAMENTO ENTRO ".$giorni_avv." GIORNI DALLA DATA DI NOTIFICA";
						
						//PARAMETRI GIUDICE DI PACE
						$giudice = new ufficio_giudiziario($c, 'giudice');
						$testo_giudice = $giudice->sede();
						
						//UTENTE
						$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
						$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$utente->Sigla_Forma_Giuridica;
						$utente_id = $utente->Comune_ID;
						$PEC = $utente->PEC;
						$indirizzo_destinatario = $utente->righe_indirizzo();
						$indirizzo_completo = $indirizzo_destinatario['Completo'];
						$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];

						//AVVISO
						$tipoUfficiale = $avviso->Tipo_Ufficiale;
						$modalitaStampa = $avviso->Modalita_Stampa;
												
						$explode_note = explode(" " , $avviso->Note);
						$id_comune_ing = $explode_note[0];
						$query = "SELECT ID FROM atto WHERE Comune_ID = '".$id_comune_ing."' AND CC = '".$c."'";
						$ID_note = single_query($query);						
						
						$ingiunzione = new atto($ID_note,$c);
						$dataNotificaIngiunzione = from_mysql_date($ingiunzione->Data_Notifica);
						$numeroIngiunzione = $ingiunzione->ID_Cronologico." del ".$ingiunzione->Anno_Cronologico;
						$importoIngiunzione = conv_num(number_format($ingiunzione->Totale_Dovuto,2));
						
						$ID_ing = $avviso->Comune_ID;
						$anno_crono = $avviso->Anno_Cronologico;
						$id_crono = $avviso->ID_Cronologico;
						$rif = $avviso->Riferimento;
						$info_cart = strtoupper($avviso->Info_Cartella);
										
						$atti_notificati = $partita->atti_notificati();
						$pagamenti_totati_atti = $partita->Pagamenti_Atti_Precedenti;
						if($pagamenti_totati_atti>0.00)	
							$pagamenti_testo = "meno i pagamenti effettuati, di ".conv_num(number_format($pagamenti_totati_atti,2))." Euro,";
						else 
							$pagamenti_testo = "";				
						
						$info_avviso = $info_cart;
						for($q=count($atti_notificati)-1;$q>=0;$q--)
						{
							$info_avviso.=", ".$atti_notificati[$q];
						}
						$info_avviso.=".";
						
						$rif_ing = $ID_partita."/".$anno_rif;
						
						$quinto_campo = $avviso->quinto_campo();//CODICE CLIENTE (QUINTO CAMPO)
						$atto_precedente = $avviso->info_atto_precedente($flag , $partita);
						
						if($stato_stampa!="Stampato")
							$data_stampa = date("d/m/Y");
						else
							$data_stampa = from_mysql_date($avviso->Data_Stampa);
						
						//IMPORTI
						$sanz_originale = conv_num( number_format( $avviso->Importo - $avviso->Spese_Precedenti, 2 ) );
						$tot_spese = $avviso->Spese_Precedenti + $avviso->Spese_Notifica;
						$tot_spese = conv_num( number_format($tot_spese,2) );
						$spese_prec = conv_num( number_format( $avviso->Spese_Precedenti , 2 ) );
						$maggiorazione = conv_num( number_format( $avviso->Interessi + $avviso->Interessi_Precedenti , 2 ) );
						$spese_not = conv_num( number_format( $avviso->Spese_Notifica , 2 ) );
						$totale_dovuto = conv_num( number_format( $avviso->Totale_Dovuto , 2 ) );
						
						//ULTIMA INGIUNZIONE
						$ultima_ing = $partita->Ultima_ING;
						$ingsenzaspese = $ultima_ing->Totale_Dovuto;
						$ingsenzaspese = conv_num( number_format($ingsenzaspese,2) );
						$tot_spese = $avviso->Spese_Precedenti + $avviso->Spese_Notifica - $ultima_ing->Spese_Notifica;
						$tot_spese = conv_num( number_format($tot_spese,2) );
						
						//PAGAMENTI
						$pagamenti = $avviso->Pagamento;
						$tot_pagamenti = 0.00;						
						if( $pagamenti != null )
						{
							for($x=0;$x<count($pagamenti);$x++)
							{
								$tot_pagamenti += $pagamenti[$x]->Importo;
							}
						}
							
						$tot_pagamenti = conv_num ( number_format( $tot_pagamenti , 2 ) );
						
						//TOTALI
						$tot_compl_1 = conv_num ( number_format( conv_num( $totale_dovuto ) - conv_num( $tot_pagamenti ) , 2 ) );
						$tot_compl_2 = conv_num ( number_format((conv_num( $tot_compl_1 ) + $CAD ) ,2));
						$tot_compl_3 = conv_num ( number_format((conv_num( $tot_compl_1 ) + $A_Mani) ,2));
						
						$dovuto_totale_1 = number_format(conv_num( $tot_compl_1 ),2);
						$dovuto_totale_2 = number_format(conv_num( $tot_compl_2 ),2);
						$dovuto_totale_3 = number_format(conv_num( $tot_compl_3 ),2);
						
						$NW = new numero_letterale();
						$numeroLetterale_1 = $NW->converti_numero_bollettino($dovuto_totale_1);
						$numeroLetterale_2 = $NW->converti_numero_bollettino($dovuto_totale_2);
						$numeroLetterale_3 = $NW->converti_numero_bollettino($dovuto_totale_3);
						
						/**
						 PARAMETRI TESTO INGIUNZIONE
						 */
						
						$luogo_data = $gestore->Comune.", li ".$data_stampa;
						
						$para_avviso = new parametri_atto_intimazione_ingiunzione(NULL);
						$myId = $para_avviso->CercaParametroData($c, date("Y-m-d"));
						$testo = new parametri_atto_intimazione_ingiunzione($myId);
						
						$titoloIngiunzione = stripslashes($testo->Titolo_Ingiunzione);
						SostituisciTestoTraGraffe ($titoloIngiunzione, "{IDCRONOLOGICO}", $id_crono );
						SostituisciTestoTraGraffe ($titoloIngiunzione, "{ANNOCRONOLOGICO}", $anno_crono );
						SostituisciTestoTraGraffe ($titoloIngiunzione, "{RIFERIMENTO}", $rif_ing );
						
						$riga1causale = "Avv. Intimazione n.".$id_crono." del ".$anno_crono." Rif.".$rif_ing;
						
						$sottotitoloIngiunzione = stripslashes($testo->Sottotitolo_Ingiunzione);
						
						$primoTesto = stripslashes($testo->Primo_Testo);
						SostituisciTestoTraGraffe ($primoTesto, "{TIPOENTE}", $intest_gestore['Riga1']);
						SostituisciTestoTraGraffe ($primoTesto, "{INDIRIZZOENTE}", $gestore->riga_indirizzo());
						
						$premesso = stripslashes($testo->Premesso_Testo);
						$premessoTesto = stripslashes($testo->Secondo_Testo);
// 						SostituisciTestoTraGraffe ($premessoTesto, "{DATANOTIFICA}", $dataNotificaIngiunzione);
// 						SostituisciTestoTraGraffe ($premessoTesto, "{NUMEROINGIUNZIONE}", $numeroIngiunzione);
						SostituisciTestoTraGraffe ($premessoTesto, "{NOMEDESTINATARIO}", $nome_utente);
						SostituisciTestoTraGraffe ($premessoTesto, "{RESIDENZADESTINATARIO}", $indirizzo_senza_provincia);
// 						SostituisciTestoTraGraffe ($premessoTesto, "{IMPORTOINGSENZASPESE}", $importoIngiunzione." Euro");
						
						$secondoTesto = stripslashes($testo->Terzo_Testo);
						$intima = stripslashes($testo->Intima);
						$intimaTesto = stripslashes($testo->Intima_Testo);
						
						SostituisciTestoTraGraffe ($intimaTesto, "{NOMEDESTINATARIO}", $nome_utente);
						SostituisciTestoTraGraffe ($intimaTesto, "{RESIDENZADESTINATARIO}", $indirizzo_senza_provincia);
						SostituisciTestoTraGraffe ($intimaTesto, "{IMPORTOINGSENZASPESE}", $ingsenzaspese." Euro");
						SostituisciTestoTraGraffe ($intimaTesto, "{SPESE}", $tot_spese." Euro");
						SostituisciTestoTraGraffe ($intimaTesto, "{PAGAMENTI}", $pagamenti_testo);
						
						$intimaCaso1 = stripslashes($testo->Intima_Caso_1);
						$intimaCaso2 = stripslashes($testo->Intima_Caso_2);
						$intimaCaso3 = stripslashes($testo->Intima_Caso_3);
						
						SostituisciTestoTraGraffe ($intimaCaso1, "{DOVUTOCASO1}", $tot_compl_1." Euro");
						SostituisciTestoTraGraffe ($intimaCaso2, "{DOVUTOCASO2}", $tot_compl_2." Euro");
						SostituisciTestoTraGraffe ($intimaCaso3, "{DOVUTOCASO3}", $tot_compl_3." Euro");
						SostituisciTestoTraGraffe ($intimaCaso3, "{SPESEUFFICIALE}", $spese_ufficiale." Euro");
						
						$intimaVersamento = stripslashes($testo->Intima_Versamento);
						
						SostituisciTestoTraGraffe ($intimaVersamento, "{NUMEROCONTO}", $numeroContoCorrente);
						SostituisciTestoTraGraffe ($intimaVersamento, "{INTESTATARIOCONTO}", $intestatarioConto);
						SostituisciTestoTraGraffe ($intimaVersamento, "{CAUSALE}", $riga1causale );				
						
						$informazioni = "INFORMAZIONI";
						$informazioniTesto = stripslashes($testo->Info_Testo);
						$finaleTesto = stripslashes($testo->Finale_Testo);						
						$opposizione = stripslashes($testo->Opposizione);
						$opposizioneTesto = stripslashes($testo->Opposizione_Testo);
						SostituisciTestoTraGraffe ($opposizioneTesto, "{GIUDICEDIPACE}",  $testo_giudice);
						
						//FIRME
						$qual_firma1 = stripslashes($testo->Qualifica_Firma_Sinistra);
						$qual_firma2 = stripslashes($testo->Qualifica_Firma_Destra);
						$firma1 = stripslashes($testo->Firma_Sinistra);
						$firma2 = stripslashes($testo->Firma_Destra);
						
						//PARAMETRI RESPONSABILI
						$par_responsabili = new parametri_responsabili($c, $settore);
						$firme_responsabili = $par_responsabili->firme_responsabili();
						$firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");
						
						//IMPOSTAZIONI FIRME
						$testo_firma = array();
						
						$array_variabili = array('{FUNZIONARIORESPONSABILE}','{RESPONSABILEPROCEDIMENTO}');
						
						$variabile = estraiVariabile($qual_firma1, $array_variabili);
						if($variabile == "{FUNZIONARIORESPONSABILE}")		
						{
							if($gestore->Tipo == "Concessionario")
								$testo_firma[1]['intestazione'] = "Il Legale Rappresentante";
							else
								$testo_firma[1]['intestazione'] = "Il Funzionario Responsabile";
						}
						else if($variabile == "{RESPONSABILEPROCEDIMENTO}")		$testo_firma[1]['intestazione'] = $firma_resp[2]['intestazione'];
						else	$testo_firma[1]['intestazione'] = "";
						
						$variabile = estraiVariabile($firma1, $array_variabili);
						if($variabile == "{FUNZIONARIORESPONSABILE}")
						{
							$testo_firma[1]['nome'] = $firma_resp[1]['nome'];
							$testo_firma[1]['firma'] = $firma_resp[1]['firma'];
						}
						else if($variabile == "{RESPONSABILEPROCEDIMENTO}")
						{
							$testo_firma[1]['nome'] = $firma_resp[2]['nome'];
							$testo_firma[1]['firma'] = $firma_resp[2]['firma'];
						}
						else
						{
							$testo_firma[1]['nome'] = "";
							$testo_firma[1]['firma'] = "";
						}
						
						$variabile = estraiVariabile($qual_firma2, $array_variabili);
						if($variabile == "{FUNZIONARIORESPONSABILE}")
						{
							if($gestore->Tipo == "Concessionario")
								$testo_firma[2]['intestazione'] = "Il Legale Rappresentante";
							else
								$testo_firma[2]['intestazione'] = "Il Funzionario Responsabile";
						}
						else if($variabile == "{RESPONSABILEPROCEDIMENTO}")		$testo_firma[2]['intestazione'] = $firma_resp[2]['intestazione'];
						else	$testo_firma[2]['intestazione'] = "";
						
						$variabile = estraiVariabile($firma2, $array_variabili);
						if($variabile == "{FUNZIONARIORESPONSABILE}")
						{
							$testo_firma[2]['nome'] = $firma_resp[1]['nome'];
							$testo_firma[2]['firma'] = $firma_resp[1]['firma'];
						}
						else if($variabile == "{RESPONSABILEPROCEDIMENTO}")
						{
							$testo_firma[2]['nome'] = $firma_resp[2]['nome'];
							$testo_firma[2]['firma'] = $firma_resp[2]['firma'];
						}
						else
						{
							$testo_firma[2]['nome'] = "";
							$testo_firma[2]['firma'] = "";
						}
						
						$modalitaFirma = stripslashes($testo->Modalita_Stampa_Firma);
						
						//RELAZIONE DI NOTIFICAZIONE
						if($avviso->Tipo_Ufficiale == "giudiziario")
						{
							$intestazioneRelata = $testo->Intestazione_Relata_Ufficiale_Giudiziario;
							$sottointestazioneRelata = $testo->Sottointestazione_Relata_Ufficiale_Giudiziario;
							$relata = $testo->Relata_Ufficiale_Giudiziario;
								
							SostituisciTestoTraGraffe ($relata, "{DESTINATARIO}", $nome_utente);
						}
						else if($avviso->Tipo_Ufficiale == "riscossione")
						{
							$intestazioneRelata = $testo->Intestazione_Relata_Ufficiale_Riscossione;
							$sottointestazioneRelata = "";
							$relata = $testo->Relata_Ufficiale_Riscossione;
						
							SostituisciTestoTraGraffe ($relata, "{TIPOENTE}",  $intest_gestore['Riga1']);
							SostituisciTestoTraGraffe ($relata, "{DESTINATARIO}", $nome_utente);
						}
						else if($avviso->Tipo_Ufficiale == "diretta")
						{
							$intestazioneRelata = "";
							$sottointestazioneRelata = "";
							$relata = "";
						}
						
						if($modalitaStampa=="posta")
							SostituisciTestoTraGraffe ($relata, "{TIPOINVIO}", "in ".$indirizzo_senza_provincia." tramite posta.");
						else if($modalitaStampa=="mani")
							SostituisciTestoTraGraffe ($relata, "{TIPOINVIO}", "in ".$indirizzo_senza_provincia." mediante consegna a mani.");
						else if($modalitaStampa=="PEC")
							SostituisciTestoTraGraffe ($relata, "{TIPOINVIO}", "al seguente indirizzo di posta elettronica certificata ".$PEC." ai sensi di legge." );
						
						//FIRMA FINALE
						$qual_firma_notifica = stripslashes($testo->Qualifica_Firma_Notifica);
						$firma_notifica = stripslashes($testo->Firma_Notifica);
						
						//IMPOSTAZIONI FIRMA FINALE
						if($avviso->Tipo_Ufficiale == "giudiziario")
						{
							$testo_firma[3]['intestazione'] = "Ufficiale Giudiziario";
							$testo_firma[3]['nome'] = "";
							$testo_firma[3]['firma'] = "";
						}
						else if($avviso->Tipo_Ufficiale == "riscossione")
						{
							$testo_firma[3]['intestazione'] = "Ufficiale della Riscossione";
							$testo_firma[3]['nome'] = "";
							$testo_firma[3]['firma'] = "";
						}
						else if($avviso->Tipo_Ufficiale == "diretta")
						{
							$testo_firma[3]['intestazione'] = "";
							$testo_firma[3]['nome'] = "";
							$testo_firma[3]['firma'] = "";
						}
						
						
						if ($stampa_select == "FLUSSO")
						{
							if($stato_stampa == "Da stampare")
							{
								$array_flusso = array();
								
								//FLUSSO GENERALE
								$array_flusso[] = $c;
								$array_flusso[] = "attoGiudiziario";
								$array_flusso[] = "avvisoIntimazione";
								$array_flusso[] = $id_crono;
								$array_flusso[] = $anno_crono;
								
								$array_flusso[] = $quinto_campo;
								
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
							
								$array_flusso[] = $luogo_data;
								$array_flusso[] = "Codice utente: ".$utente_id;
								$array_flusso[] = "Partita numero: ".$ID_partita." / ".$anno_rif;
								$array_flusso[] = "Spett.le ".$nome_utente;
								$array_flusso[] = $indirizzo_destinatario['Riga1'];
								$array_flusso[] = $indirizzo_destinatario['Riga2'];
								$array_flusso[] = $indirizzo_destinatario['Riga3'];
								$array_flusso[] = $indirizzo_destinatario['Riga4'];
								
								//FLUSSO OGGETTO
								
								$array_flusso[] = "OGGETTO: ".$titoloIngiunzione;
								$array_flusso[] = $sottotitoloIngiunzione;
								$array_flusso[] = $primoTesto;
								
								//FLUSSO PREMESSO
							
								$array_flusso[] = $premesso;
								$array_flusso[] = $premessoTesto;
								$array_flusso[] = $info_avviso;
								$array_flusso[] = $secondoTesto;
							
								//FLUSSO INTIMA
							
								$array_flusso[] = $intima;
								$array_flusso[] = $intimaTesto;
								$array_flusso[] = $intimaCaso1;
								$array_flusso[] = $intimaCaso2;
								$array_flusso[] = $intimaCaso3;
								$array_flusso[] = $intimaVersamento;							
							
								//FLUSSO INFORMAZIONI
								
								$array_flusso[] = $informazioni;
								$array_flusso[] = $informazioniTesto;
								$array_flusso[] = $finaleTesto;
								
								//FLUSSO OPPOSIZIONE
								
								$array_flusso[] = $opposizione;
								$array_flusso[] = $opposizioneTesto;
								
								//FLUSSO RESPONSABILI
							
								$array_flusso[] = $testo_firma[1]['intestazione'];
								$array_flusso[] = $testo_firma[1]['firma'];
								$array_flusso[] = $testo_firma[1]['nome'];
								$array_flusso[] = $testo_firma[2]['intestazione'];
								$array_flusso[] = $testo_firma[2]['firma'];
								$array_flusso[] = $testo_firma[2]['nome'];
								$array_flusso[] = $modalitaFirma;
													
								//FLUSSO RELAZIONE
							
								$array_flusso[] = $intestazioneRelata;
								$array_flusso[] = $relata;
								$array_flusso[] = $testo_firma[3]['intestazione'];
								$array_flusso[] = $testo_firma[3]['firma'];
								$array_flusso[] = $testo_firma[3]['nome'];

								//FLUSSO BOLLETTINO 1
								if($autorizzazione_1!=false || $td_1=="123")
								{											
									if($td_1=="896")
									{
										$dovuto_bollettino = $tot_compl_1;
										$dovuto_letterale = "";
									}
									else if($ctrl_importo_1=="si")
									{
										$dovuto_bollettino = $tot_compl_1;
										$dovuto_letterale = $numeroLetterale_1;
									}
									else
									{
										$dovuto_bollettino = "";
										$dovuto_letterale = "";
									}
									
									$array_flusso[] = $td_1;
									$array_flusso[] = $autorizzazione_1;
									$array_flusso[] = $numeroContoCorrente;
									$array_flusso[] = $intestatarioConto;
									$array_flusso[] = $iban;
									$array_flusso[] = $dovuto_bollettino;
									$array_flusso[] = $dovuto_letterale;
									$array_flusso[] = $nome_utente;
									
									$riga_flusso2 = strtoupper($indirizzo_destinatario['Riga1']." - ".$indirizzo_destinatario['Riga2']);
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
									$array_flusso[] = $pdf->barcode_importo_bollettino($td_1, $tot_compl_1);
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
								}
								
								//FLUSSO BOLLETTINO 2
								if($autorizzazione_2!=false || $td_2=="123")
								{
									if($td_2=="896")
									{
										$dovuto_bollettino = $tot_compl_2;
										$dovuto_letterale = "";
									}
									else if($ctrl_importo_2=="si")
									{
										$dovuto_bollettino = $tot_compl_2;
										$dovuto_letterale = $numeroLetterale_2;
									}
									else
									{
										$dovuto_bollettino = "";
										$dovuto_letterale = "";
									}
									
									$array_flusso[] = $td_2;
									$array_flusso[] = $autorizzazione_2;
									$array_flusso[] = $numeroContoCorrente;
									$array_flusso[] = $intestatarioConto;
									$array_flusso[] = $iban;
									$array_flusso[] = $dovuto_bollettino;
									$array_flusso[] = $dovuto_letterale;
									$array_flusso[] = $nome_utente;
									
									$riga_flusso2 = strtoupper($indirizzo_destinatario['Riga1']." - ".$indirizzo_destinatario['Riga2']);
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
									$array_flusso[] = $pdf->barcode_importo_bollettino($td_2, $tot_compl_2);
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
								}
								
								$myFlusso->AggiungiRigaFlusso($array_flusso);
								
								$salva = new atto($avviso->ID, $c);
									
								$salva->Data_Flusso = $myFlusso->myData;
								$salva->Anno_Flusso = $myFlusso->myAnno;
								$salva->Numero_Flusso = $myFlusso->myNumero;
															
								$salva->Update($avviso->ID, true);
							
							}
							
						}	//fine FLUSSO
						else
						{
							if ($stampa_select == "DEFINITIVA")
							{
								$file_stampa = $stampa_dir."/Avviso_di_intimazione_".$c."_".$anno_crono."_".$id_crono."_".to_mysql_date($data_stampa).".pdf";
								
								/**
								 ///////////////////////////////		PDF	    //////////////////////////////////
								 */
								
								$pdf = new clsPdf_("P", "mm", "A4", true, 'UTF-8', false);
								$pdf->SetLineWidth(0.2);
								$pdf->SetMargins(7.0, 10.0, 7.0);
								$width_page = $pdf->getPageWidth() - 7;
								
								
								/**
								 //////////////////////////////////////////////////////////////////////////////
								 */
								
							}
/**
 ///////////////////////////////		PDF	    //////////////////////////////////
*/
	
	/**
	 * 		//////////////	PAGINA 1	//////////////
	*/
						
	$pdf->setPrintHeader(false);
	$pdf->SetAutoPageBreak(false);
	$pdf->SetCellPadding(0);
	$pdf->AddPage('P');
	
	if($stampa_select == "PROVVISORIA")
		$pdf->stampa_provvisoria();

//////////////	CORPO Pagina 1	//////////////					
	
	$pdf->intestazione_pdf($tipo_gestore, $image_file, $intest_gestore, $intest_ufficio);
	$pdf->destinatario_intestazione_pdf($utente_id, $c, $nome_utente, $ID_partita, $anno_rif, $indirizzo_destinatario, $luogo_data );
	$pdf->oggetto_pdf($titoloIngiunzione, $sottotitoloIngiunzione, $primoTesto);	
	
	//PREMESSO
	$pdf->SetFont('Arial', 'B', 9);
			$pdf->MultiCell(0, 0, $premesso , 0, 'C', 0, 1);
	$pdf->SetFont('Arial', '', 9);
			$pdf->MultiCell(0, 0, $premessoTesto."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', 'B', 8);
			$pdf->MultiCell(0, 0, $info_avviso."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 9);
if( $atto_precedente!="" )		$pdf->MultiCell(0, 0, $atto_precedente , 0, 'L', 0, 1);
			$pdf->MultiCell(0, 0, $secondoTesto."\n", 0, 'J', 0, 1);	
			$pdf->Ln(2);
			
	//INTIMA
	$pdf->SetFont('Arial', 'B', 9);
			$pdf->MultiCell(0, 0, $intima , 0, 'C', 0, 1);
	$pdf->SetFont('Arial', '', 9);
			$pdf->MultiCell(0, 0, $intimaTesto."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $intimaCaso1."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $intimaCaso2."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $intimaCaso3."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $intimaVersamento."\n", 0, 'J', 0, 1);
			$pdf->Ln(2);
			
	//INFORMAZIONI
	$pdf->SetFont('Arial', 'B', 9);
			$pdf->MultiCell(0, 0, $informazioni , 0, 'C', 0, 1);
	$pdf->SetFont('Arial', '', 9);
			$pdf->MultiCell(0, 0, $informazioniTesto." ".$finaleTesto."\n", 0, 'J', 0, 1);	
// 			$pdf->MultiCell(0, 0, $finaleTesto."\n", 0, 'J', 0, 1);
			$pdf->Ln(2);
			
	//OPPOSIZIONE
	$pdf->SetFont('Arial', 'B', 9);
			$pdf->MultiCell(0, 0, $opposizione, 0, 'C', 0, 1);
	$pdf->SetFont('Arial', '', 9);
			$pdf->MultiCell(0, 0, $opposizioneTesto."\n", 0, 'J', 0, 1);
			$pdf->Ln(2);
			
	//RESPONSABILI	
			$pdf->firma_pdf($testo_firma);
			
			$pdf->Ln(2);
			$pdf->SetFont('Arial', '', 7);
			$pdf->MultiCell(0, 0, $modalitaFirma."\n", 0, 'J', 0, 1);
			$pdf->Ln(1);
			
	//RELAZIONE
	$pdf->SetFont('Arial', 'B', 9);
			$pdf->MultiCell(0, 0, $intestazioneRelata , 0, 'C', 0, 1);
		if($sottointestazioneRelata!="")
			$pdf->MultiCell(0, 0, $sottointestazioneRelata , 0, 'C', 0, 1);
	$pdf->SetFont('Arial', '', 9);
			$pdf->MultiCell(0, 0, $relata."\n", 0, 'J', 0, 1);
			$pdf->Ln(2);
			$pdf->Cell(122, 0, '' , 0, 0, 'C', 0, '', 0);
			$pdf->Cell(60,0, $testo_firma[3]['intestazione'] , 0, 1,'C',0,'',0 );
			$pdf->Ln(5);
			$pdf->Cell(122, 0, '' , 0, 0, 'C', 0, '', 0);
			$pdf->Cell(60,0, $testo_firma[3]['nome'] , 0, 1,'C',0,'',0 );

//////////////	FINE CORPO Pagina 1	//////////////			

	//PIE DI PAGINA 1
	$pdf->SetY(-10);
	$pdf->SetFont('helvetica', 'N', 7);
	$pdf->Cell(0, 5, "Pag. 1/1 - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	/**
	 * 		//////////////	PAGINA 2 BOLLETTINO	//////////////
	 */
	if(($autorizzazione_1!=false || $td_1=="123") || ($autorizzazione_2!=false || $td_2=="123"))
	{
		
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	$pdf->AddPage('L');
	if($stampa_select == "PROVVISORIA")
		$pdf->stampa_provvisoria();
	
	$pdf->SetMargins(0, 0, 0);
	
//////////////	CORPO Pagina 3	//////////////

	if($autorizzazione_1!=false || $td_1=="123")
	{
		$pdf->crea_bollettino();
		$pdf->logo_bollettino($image_file);		
		$pdf->scelta_td_bollettino($td_1, $quinto_campo , $tot_compl_1 , $ctrl_importo_1 , $numeroContoCorrente );	
		$pdf->iban_bollettino($iban);
		$pdf->intestatario_bollettino($intestatarioConto);
		$pdf->causale_bollettino($riga1causale, $riga2causale);
		$pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario);
		$pdf->autorizzazione_bollettino($autorizzazione_1);
	}
	
	if($autorizzazione_2!=false || $td_2=="123")
	{
		$pdf->crea_bollettino_inverso();
		$pdf->logo_bollettino($image_file,'due');
		$pdf->scelta_td_bollettino($td_2, $quinto_campo , $tot_compl_2 , $ctrl_importo_2 , $numeroContoCorrente, 'due');
		$pdf->iban_bollettino($iban,'due');
		$pdf->intestatario_bollettino($intestatarioConto,'due');
		$pdf->causale_bollettino($riga1causale, $riga2causale,'due');
		$pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario,'due');
		$pdf->autorizzazione_bollettino($autorizzazione_2,'due');
	}
	
	}
	
/**
 //////////////////////////////////////////////////////////////////////////////
*/
						
						if($stampa_select=="DEFINITIVA")
						{
							mysql_query('BEGIN');
							
							$salva = new atto($avviso->ID, $c);
							
							if($salva->Stato_Stampa == "Stampato")
							{
								$pdf->Output( $file_stampa , 'F');
									
								$arrayConcat[] = $file_stampa;
							}
							
// 							if($salva->Stato_Stampa == "Da stampare")
// 							{
// 								$salva->Data_Stampa = to_mysql_date($data_stampa);
// 								$salva->Stato_Stampa = "Stampato";
								
// 								$control_salva = $salva->Update($avviso->ID, true);
									
// 								if( $control_salva )
// 								{
// 									mysql_query('COMMIT');
									
// 									$pdf->Output( $file_stampa , 'F');
									
// 									$arrayConcat[] = $file_stampa;
// 								}
// 								else
// 								{
// 									mysql_query('ROLLBACK');
// 								}		
// 							}
							 
						}						
						
						}  // fine PDF
						
							$array_stampati[] = $array_atti[$l]['ID'];
							$cont_result++;
							
							break;		//Una partita pu� avere un solo intestatario per cui una volta trovato si pu� uscire dal ciclo degli utenti		
									
					}//CHIUSURA IF PARTITA/UTENTE
	
				}//CHIUSURA FOR UTENTI
			
				break;		//Un atto pu� corrispondere ad una sola partita per cui una volta trovato si pu� uscire dal ciclo delle partite
				
			}//CHIUSURA IF ATTO/PARTITA
				
		}//CHIUSURA PARTITE
			
	}//CHIUSURA ATTII

	if ($stampa_select == "FLUSSO")
	{
		if($stato_stampa == "Da stampare")
		{
			if($cont_result == 0)
			{
				echo "<script>nessun_risultato();</script>";
			}
			else 
			{
				mysql_query('COMMIT');		
			
				$myFlusso->AllegaImmagine($percorso_image_file);
				$myFlusso->AllegaImmagine($firme_responsabili['Funzionario_Path']);
				$myFlusso->AllegaImmagine($firme_responsabili['Responsabile_Path']);
				$myFlusso->AllegaImmagine($firme_responsabili['Ufficiale_Path']);
	
				$myFlusso->ChiudiFlusso();
	
				$Text = json_encode($array_stampati);
				$RequestText = urlencode($Text);
				
				echo "<script>fine2('Elaborazione completata');</script>";
				echo "<script>atti_stampati('".$RequestText."');</script>";
			}
			
		}	
		else if($stato_stampa == "Stampato")
		{			
			if($cont_result == 0)
			{
				echo "<script>nessun_risultato();</script>";
			}
			else
			{
				$Text = json_encode($array_stampati);
				$RequestText = urlencode($Text);	
				
				echo "<script>fine2('Elaborazione completata');</script>";
				echo "<script>atti_stampati('".$RequestText."');</script>";
			}
		}
	}
	else if($stampa_select=="PROVVISORIA")
	{
		if($cont_result == 0)
		{
			echo "<script>nessun_risultato();</script>";
		}
		else
		{
			$pdf->Output( $file_stampa , 'F');
			echo "<script>fine('Elaborazione completata');</script>";
		}	
	
	}
	else if($stampa_select == "DEFINITIVA")
	{
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
			
			$fileCompletoUnito = $concat_dir."/Avvisi_di_intimazione_Merge_".$c."_".$data_file."_".$ora_file.".pdf";
			
			echo "<script>merge();</script>";
			flush(); ob_flush(); flush(); ob_flush();
			sleep(1);
			
			$mergepdf = new Concat_Pdf();
			$mergepdf->setFiles($arrayConcat);
						
			$time_start = getmicrotime();//sec iniziali
			$mergepdf->Concat(true);
			$time_end = getmicrotime();//sec finali
			$time = $time_end - $time_start;//differenza in secondi
			
			$tempo_previsto_sec = $time * 20;
			if($tempo_previsto_sec<55) 
				$tempo_previsto = "1 minuto";
			else	
				$tempo_previsto = floor($tempo_previsto_sec/60+1)." minuti";

			echo "<script>fine_merge('Creazione file in corso... Il tempo previsto per le operazioni � di circa ".$tempo_previsto.".');</script>";
			flush(); ob_flush(); flush(); ob_flush();
			
			set_time_limit($tempo_previsto_sec);
			flush(); ob_flush(); flush(); ob_flush();
			$mergepdf->Output($fileCompletoUnito, "F");
			
			$vedi_file = mostra_file_path($fileCompletoUnito);
			
			echo "<script>fine_e_apri('Elaborazione completata',\"".$vedi_file."\");</script>";
			
// 		$Text = json_encode($array_stampati);
// 		$RequestText = urlencode($Text);
// 		echo "<script>fine2('Elaborazione completata');</script>";
// 		echo "<script>atti_stampati('".$RequestText."');</script>";
		
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

			echo "<form name='crono_form' id='crono_form' method='post' action='cronologici.php'>";
			echo "<input type=hidden name=atto_val value='avvisoIntimazione'>";
			echo "<input type=hidden name='c' value=".$c.">";
			echo "<input type=hidden name='a' value=".$a.">";
			for($t=0; $t<count($array_cronologici);$t++)
			{
				echo "<input type=hidden name=array_crono[] value='".$array_cronologici[$t]."'>";
			}
			
			echo "</form>";
			
// 			$Text = json_encode($array_cronologici);
// 			$RequestText = urlencode($Text);

			echo "<script>fine2('Elaborazione completata');</script>";
			echo "<script>cronologici('');</script>";
		}
	}

	
?>

</body>
</html>