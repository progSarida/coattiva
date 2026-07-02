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
include CLASSI . "/numero_letterale.php";

include CLASSI . "/classe_email.php";

require EMAIL.'/PHPMailerAutoload.php';
require_once CLASSI. "\php-imap-client-master\Imap.php";

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

$chiudi="";
//PARAMETRI RESPONSABILI
$par_responsabili = new parametri_responsabili($c, "CDS");
$firme_responsabili = $par_responsabili->firme_responsabili();
$firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");

if($firma_resp[1]['firma']=="" || $firma_resp[2]['firma']=="" || $firma_resp[3]['firma']=="")
{
	alert('Parametri Responsabili CDS incompleti!');
	$chiudi = "chiudi_finestra()";
}

//CONTROLLO TESTO
$para_pigno = new testo_pignoramento_veicolo(NULL);
$myId = $para_pigno->CercaParametroData($c, date("Y-m-d"),"si");

if($myId==null)
	$chiudi = "chiudi_finestra()";

$testo = new testo_pignoramento_veicolo($myId);


$data_file = date('Y-m-d');
$ora_file = date('H-i-s');
$vedi_file = "";

if($stampa_select == "PROVVISORIA")
{

	$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Veicolo/STAMPE PROVVISORIE" );

	$file_stampa = $stampa_dir."/Pignoramenti_Veicolo_Provvisori_".$c."_".$data_file."_".$ora_file.".pdf";
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
	atto_val = "Pignoramento veicolo";	
	location.href="atti_stampati.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+atto_val+"&tipo_stampa=<?php echo $stampa_select; ?>&cluster="+value;
}

function cronologici(value)
{
	$('#crono_form').submit();
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
		<font class="titolo font18 text_center">Stampa Pignoramenti veicolo</font>
		
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

$da_sped = from_mysql_date(get_var('da_sped'));
$a_sped = from_mysql_date(get_var('a_sped'));

$da_cons = from_mysql_date(get_var('da_cons'));
$a_cons = from_mysql_date(get_var('a_cons'));

$consegnato_a = get_var('consegnato_a');

$da_stampa = from_mysql_date(get_var('da_stampa'));
$a_stampa = from_mysql_date(get_var('a_stampa'));

$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

$stato_stampa_filtro = get_var('stato_stampa');

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

/** 	SELEZIONE PIGNORAMENTI	*/
$campi_stati = array("PIG_GEN.Stato_Stampa");
$valori_stati = array ($stato_stampa_filtro);

$campi_array = array ("Data_Elaborazione" , "Data_Notifica" , "Data_Stampa" );
$array_da_data = array( to_mysql_date($da_elab) , to_mysql_date($da_notif) , to_mysql_date($da_stampa) );
$array_a_data = array( to_mysql_date($a_elab) , to_mysql_date($a_notif) , to_mysql_date($a_stampa) );

$where_pigno = array();
$where_pigno[0] = selezione_date_query( "PIG_GEN", $campi_array , $array_da_data , $array_a_data );
$where_pigno[1] = where_campi($campi_stati, $valori_stati);

$pignoramento = new pignoramento(null, $c);
$query_pignoramenti = $pignoramento->query_selezione_pignoramenti($c, 'veicolo', null, $ordinamento, $where_pigno);

$array_pignoramenti = mysql_array($query_pignoramenti);

$num_pignoramenti = count($array_pignoramenti);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

// alert($num_pignoramenti." ".$num_utenti." ".$num_partite);

$anno_current = date("Y");
$array_cronologici = array();

if($stampa_select == "PROVVISORIA")
{
	
	/**
	 ///////////////////////////////		PDF	    //////////////////////////////////
	*/

	$pdf = new clsPdf_("P", "mm", "A4", true, 'UTF-8', false);
	
	/**
	 //////////////////////////////////////////////////////////////////////////////
	 */
}
else if($stampa_select == "DEFINITIVA")
{
	$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Veicolo/STAMPE DEFINITIVE" );
	$concat_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Veicolo/STAMPE CONCATENATE" );
	$arrayConcat = Array();
}
else if($stampa_select == "PEC")
{
	$pdf = new clsPdf_("P", "mm", "A4", true, 'UTF-8', false);
	$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Veicolo/STAMPE DEFINITIVE" );
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
						
						//PIGNORAMENTO
						$pignoramento = new pignoramento( $array_pignoramenti[$l]['ID'], $c );
						if($stato_stampa_filtro!="Stampato")
							$data_stampa = date("d/m/Y");
						else
							$data_stampa = from_mysql_date($pignoramento->Data_Stampa);
						
						//ESCLUSIONI
						if($stampa_select == "PROVVISORIA")
						{
							if($pignoramento->ID_Cronologico == "0" || $pignoramento->Anno_Cronologico == "0")
								break;
							
							if($pignoramento->Stato_Stampa=="Stampato")
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
							if($pignoramento->ID_Cronologico == "0" || $pignoramento->Anno_Cronologico == "0")
								break;
								
							$file_stampa_originale = $stampa_dir."/Pignoramento_veicolo_".$c."_".$pignoramento->Anno_Cronologico."_".$pignoramento->ID_Cronologico."_".to_mysql_date($data_stampa)."_originale.pdf";
							$file_stampa_debitore = $stampa_dir."/Pignoramento_veicolo_".$c."_".$pignoramento->Anno_Cronologico."_".$pignoramento->ID_Cronologico."_".to_mysql_date($data_stampa)."_debitore.pdf";
														
// 							if($stato_stampa_filtro == "Stampato")
// 							{
// 								$arrayConcat[] = $file_stampa_originale;
// 								$arrayConcat[] = $file_stampa_debitore;
										
// 								$cont_result++;
									
// 								break;
// 							}
						}
						else if($stampa_select == "FLUSSO")
						{
							if($pignoramento->Ufficiale_Consegna != "uff_riscossioni" && $avviso->Modalita_Stampa != "posta")
								break;
						}
						
						
						//PARTITA
						$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
						$ID_partita = $partita->Comune_ID;
						$anno_rif = $partita->Anno_Riferimento;
						$settore = $partita->Tipo;
						$partita->atti_notificati();
						$atti_notificati = $partita->tutti_gli_atti_notificati();
						$riferimento_partita = $ID_partita."/".$anno_rif;
						$ultima_ing = $partita->Ultima_ING;
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
							break;
						
						//CONTROLLO TRIBUNALE
						$tribunale = new ufficio_giudiziario($utente->Residenza->CC_Indirizzo, "tribunale");
// 						if($tribunale->ID==null)
// 							break;
						
						//CONTROLLO ISTITUTO VENDITE GIUDIZIARIE
						$istituto_vendite = new ufficio_giudiziario($tribunale->CC_Ufficio, "istituto");
// 						if($istituto_vendite->ID==null)
// 							break;
						
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
						$avviso = new atto($Atto_ID, $c);
						
						if(from_mysql_date($avviso->Data_Notifica)=="")
							continue;
						
						if($avviso->Motivo_Notifica!=0)
							continue;
						
						$info_cartella = $avviso->Info_Cartella;
						$info_avviso = $avviso->Atto." n.".$avviso->ID_Cronologico." del ".$avviso->Anno_Cronologico;
						
						/**
						 * DATI GENERALI PIGNORAMENTO
						*/
												
						//CRONO PIGNORAMENTO
						$Comune_ID_pignoramento = $pignoramento->Comune_ID;
						$Anno_Cronologico = $pignoramento->Anno_Cronologico;
						$ID_Cronologico = $pignoramento->ID_Cronologico;
						
						$riga1causale = "Pignoramento beni mobili registrati n.".$ID_Cronologico." del ".$Anno_Cronologico." Rif.".$riferimento_partita;
						$quinto_campo = $pignoramento->quinto_campo();
						
						//TIPO PIGNORAMENTO
						$tipo_pignoramento = $pignoramento->Tipo;
						$tipo_terzi_generale = $pignoramento->Tipo_Terzi;
						
						//DATE E STATI
						$Data_Elaborazione = from_mysql_date($pignoramento->Data_Elaborazione);						
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
							$Stato_Notifica_Debitore = $notifica_debitore->Stato_Notifica;
							
							//SPESE DEBITORE
							$Spese_Notifica_Debitore = $notifica_debitore->Spese_Notifica;
							$CAN_Debitore = $notifica_debitore->CAN;
							$CAD_Debitore = $notifica_debitore->CAD;
							$CAN_CAD_Debitore = $CAN_Debitore + $CAD_Debitore;
							
							if($Spese_Notifica_Debitore!=null)
								$Spese_Notifica_Debitore = conv_num(number_format($Spese_Notifica_Debitore,2));
							if($CAN_CAD_Debitore!=null)
								$CAN_CAD_Debitore = conv_num(number_format($CAN_CAD_Debitore,2));
							
						}

						if(isset($pignoramento->Notifica_Istituto))
						{
							$notifica_istituto = $pignoramento->Notifica_Istituto[0];
								
							$Tipo_Invio_Istituto = $notifica_istituto->Modalita_Stampa;
							$Data_Notifica_Istituto = from_mysql_date($notifica_istituto->Data_Notifica);
							$Stato_Notifica_Istituto = $notifica_istituto->Stato_Notifica;
								
							//SPESE DEBITORE
							$Spese_Notifica_Istituto = $notifica_istituto->Spese_Notifica;
							$CAN_Istituto = $notifica_istituto->CAN;
							$CAD_Istituto = $notifica_istituto->CAD;
							$CAN_CAD_Istituto = $CAN_Istituto + $CAD_Istituto;
								
							if($Spese_Notifica_Istituto!=null)
								$Spese_Notifica_Istituto = conv_num(number_format($Spese_Notifica_Istituto,2));
							if($CAN_CAD_Istituto!=null)
								$CAN_CAD_Istituto = conv_num(number_format($CAN_CAD_Istituto,2));
								
						}
						
						
						if($stampa_select == "PEC")
						{						
							
							$par_email = new parametri_email($c, $settore, "pec");
						
							if($par_email->Indirizzo_Email=="")
							{
								alert("INVIO TRAMITE PEC: L'indirizzo PEC da cui deve essere spedita la richiesta non e' stato inserito! Impossibile procedere con la stampa.");
								echo "<script>window.close();</script>";
							}
						
							if($PEC_Istituto!="")
							{
								$mail_destinatario = $PEC_Istituto;
								$tipo_destinatario = "PEC";
								$ricevuta_consegna = "attesa";
							}
							else if($Mail_Istituto!="")
							{
								$mail_destinatario = $Mail_Istituto;
								$tipo_destinatario = "email";
								$ricevuta_consegna = "no";
							}
							else 
								continue;
							
							if($Tipo_Invio_Istituto!="pec")
								continue;
							else 
							{
								$query_mail = "SELECT ID FROM email_inviate WHERE CC = '".$c."' AND Partita_ID = '".$partita->ID."' ";
								$query_mail.= " AND Utente_ID = '".$utente->ID."' AND Table_Collegata = 'notifica_atto' ";
								$query_mail.= " AND ID_Collegato = '".$notifica_istituto->ID."'";
								
								$id_email = single_query($query_mail);
							
								if($id_email!=null)
								{
									$array_PEC[] = $pignoramento->ID;
									$cont_result++;
									continue;
								}
							}
							
							if($pignoramento->Stato_Stampa != "Stampato" || from_mysql_date($pignoramento->Data_Stampa)==null)
								continue;
							
							if($pignoramento->ID_Cronologico==0 || $pignoramento->Anno_Cronologico==0)
								continue;
							
							$identificativo_file = "Pignoramento_veicolo_".$c."_".$pignoramento->Anno_Cronologico."_".$pignoramento->ID_Cronologico."_".$pignoramento->Data_Stampa."_debitore";
							$file_stampa_debitore = $stampa_dir."/".$identificativo_file.".pdf";
				
							if(is_file($file_stampa_debitore))
							{
								$subject = $identificativo_file;
								$body = "Invio copia di Pignoramento beni mobili registrati. Vedi allegato.";
									
								set_time_limit(200);
									
								$mail = new PHPMailer();
									
								$mail->creaMailCompleta($par_email, $subject, $body);
								$mail->addAddress($mail_destinatario, $istituto_vendite->Denominazione);
								$mail->addAttachment($file_stampa_debitore);
				
								if($mail->send())
								{
									$salva_email = new email_inviate(null);
				
									$salva_email->CC = $c;
									$salva_email->Partita_ID = $partita->ID;
									$salva_email->Utente_ID = $utente->ID;
									$salva_email->Oggetto = $identificativo_file;
									$salva_email->Mail_Sorgente = $par_email->Indirizzo_Email;
									$salva_email->Tipo_Sorgente = "PEC";			
									$salva_email->Mail_Destinatario = $mail_destinatario;
									$salva_email->Tipo_Destinatario = $tipo_destinatario;
									$salva_email->Data_Invio = date('Y-m-d');
				
									$salva_email->Ricevuta_Accettazione = "attesa";
									$salva_email->Ricevuta_Consegna = $ricevuta_consegna;
				
									$salva_email->Table_Collegata = "notifica_atto";
									$salva_email->ID_Collegato = $notifica_istituto->ID;
										
									mysql_query('BEGIN');
				
									$control_salva = $salva_email->Insert();
				
									if( $control_salva )
									{
										mysql_query('COMMIT');
										$path_mail = crea_dir($salva_email->percorsoMail($c, "PEC", $identificativo_file,'server'));
										
										$myfile = fopen($path_mail."/".$identificativo_file.'.eml', 'w');
										$testo = $mail->getMessaggio();
										fwrite($myfile, $testo);
										
										fclose($myfile);
										
										$array_PEC[] = $pignoramento->ID;
										
										$cont_result++;
										break;
									}
									else
									{
										mysql_query('ROLLBACK');
										alert('Errore nel salvataggio della email inviata!');
									}									
								}
								else 
								{
									$array_PEC[] = $pignoramento->ID;
								}
												
								
							}
							else 
							{
								$array_PEC[] = $pignoramento->ID;
								continue;
							}
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
						
						//VEICOLO
						$pigno_veicolo = $pignoramento->Veicolo[0];
						$data_visura = from_mysql_date($pigno_veicolo->Data_Visura);
						$marca_veicolo = strtoupper($pigno_veicolo->Marca_Veicolo);
						$modello_veicolo = strtoupper($pigno_veicolo->Modello_Veicolo);
						$targa_veicolo = strtoupper($pigno_veicolo->Targa_Veicolo);
						$tipo_veicolo = $pigno_veicolo->Tipo_Veicolo;
						$fonte_dati = strtoupper($pigno_veicolo->Fonte_Dati);
						
						$pre_tipo_veicolo = "il";
						if($tipo_veicolo == "autoveicolo") $pre_tipo_veicolo = "l'";	
						
						
						//TARIFFE COAZIONE
						$tariffe_coazione = new tariffe_coazione(null, $c);
						$tariffe_coazione->array_tariffe($c);
						$tariffe_una_tantum = $tariffe_coazione->Una_Tantum;
						for($i=0;$i<count($tariffe_una_tantum);$i++)
						{
							if($tariffe_una_tantum[$i]['Descrizione']=="Valutazione/Stima dei beni pignorati e formazione fascicolo")
								$stima_beni = conv_num(number_format($tariffe_una_tantum[$i]['Importo'],2));
						}
						
						
						$Titolo_Oggetto = $testo->Titolo_Oggetto;
						SostituisciTestoTraGraffe ($Titolo_Oggetto, "{TRIBUNALE}", strtoupper($tribunale->Comune));
												
						$Sottotitolo_Oggetto = $testo->Sottotitolo_Oggetto;

						$Intestazione_Pignoramento = $testo->Intestazione_Pignoramento;
						SostituisciTestoTraGraffe ($Intestazione_Pignoramento, "{IDCRONOLOGICO}", $ID_Cronologico);
						SostituisciTestoTraGraffe ($Intestazione_Pignoramento, "{ANNOCRONOLOGICO}", $Anno_Cronologico);
						
						$riferimenti_atto = "( Utente: ".$codice_utente." - Partita: ".$riferimento_partita." - ".$riferimento_ingiunzione." - COMUNE DI ".strtoupper($comune->Nome)." )";
						

						$Ufficiale_Responsabile = $testo->Ufficiale_Responsabile;
						SostituisciTestoTraGraffe ($Ufficiale_Responsabile, "{GESTORE}", strtoupper($gestore->Denominazione));
						SostituisciTestoTraGraffe ($Ufficiale_Responsabile, "{SEDEGESTORE}", $righe_gestore['Senza_Provincia']);

						$Legale_Rappresentante_Comune = $testo->Legale_Rappresentante_Comune;
						$Legale_Rappresentante_Concessionario = $testo->Legale_Rappresentante_Concessionario;
						
						if($gestore->Tipo == "Concessionario")
						{
							$Legale_Rappresentante = $Legale_Rappresentante_Concessionario;
							SostituisciTestoTraGraffe ($Legale_Rappresentante, "{ENTE}", strtoupper("COMUNE DI ".$comune->Nome));
						}
						else
						{
							$Legale_Rappresentante = $Legale_Rappresentante_Comune;
							SostituisciTestoTraGraffe ($Legale_Rappresentante, "{FUNZIONARIORESPONSABILE}", $firma_resp[2]['nome']);
						}
						
						$Premesso = $testo->Premesso;
						
						$Atti_Notificati = $testo->Atti_Notificati;
						SostituisciTestoTraGraffe ($Atti_Notificati, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Atti_Notificati, "{CFPI}", $CF_PI);
						if($utente->Genere=="D")
							$indirizzo_pignorato = "sito in ";
						else
							$indirizzo_pignorato = "residente in ";
						SostituisciTestoTraGraffe ($Atti_Notificati, "{RESIDENZAUTENTE}", $indirizzo_pignorato.$indirizzo_senza_provincia);
						
						$Premesso_Testo = $testo->Premesso_Testo;
						SostituisciTestoTraGraffe ($Premesso_Testo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Premesso_Testo, "{ENTE}", strtoupper("Comune di ".$comune->Nome));
						SostituisciTestoTraGraffe ($Premesso_Testo, "{DATACALCOLO}", $Data_Elaborazione);

						$Informazioni = $testo->Informazioni;
						$Informazioni_Testo = $testo->Informazioni_Testo;
						$Informo = $testo->Informo;
						$Conto_Corrente = $testo->Conto_Corrente;
						SostituisciTestoTraGraffe ($Conto_Corrente, "{NUMEROCONTO}", $numeroContoCorrente);
						SostituisciTestoTraGraffe ($Conto_Corrente, "{INTESTATARIOCONTO}", $intestatarioConto);
						SostituisciTestoTraGraffe ($Conto_Corrente, "{IBAN}", " (IBAN ".$iban.")");
						SostituisciTestoTraGraffe ($Conto_Corrente, "{CODICEUTENTE}", $codice_utente);
						SostituisciTestoTraGraffe ($Conto_Corrente, "{CRONOLOGICO}", $ID_Cronologico."/".$Anno_Cronologico);
						SostituisciTestoTraGraffe ($Conto_Corrente, "{RIFERIMENTO}", "PARTITA ".$riferimento_partita);
						SostituisciTestoTraGraffe ($Conto_Corrente, "{ENTE}", strtoupper("Comune di ".$comune->Nome));
						
						$Informo_Testo = $testo->Informo_Testo;						
						$Informo_Testo_2 = $testo->Informo_Testo_2;
						$Informo_Testo_3 = $testo->Informo_Testo_3;
						SostituisciTestoTraGraffe ($Informo_Testo_3, "{SPESENOTIFICA}", conv_num($spese_notifica));
						SostituisciTestoTraGraffe ($Informo_Testo_3, "{SPESEATTIGIUDIZIARI}", conv_num($spese_postali_ag));
						SostituisciTestoTraGraffe ($Informo_Testo_3, "{CAN}", conv_num($CAN));
						SostituisciTestoTraGraffe ($Informo_Testo_3, "{CAD}", conv_num($CAD));
						$Informo_Testo_4 = $testo->Informo_Testo_4;
						
						$Considerato = $testo->Considerato;
						
						$Ingiunzione_Fiscale = $testo->Ingiunzione_Fiscale;
						
						$Legislatore = $testo->Legislatore;
						$Dati_Veicolo = $testo->Dati_Veicolo;
						SostituisciTestoTraGraffe ($Dati_Veicolo, "{UTENTE}", $nome_utente);
						if($data_visura!="")
							SostituisciTestoTraGraffe ($Dati_Veicolo, "{DATAVISURA}", $data_visura);
						if($fonte_dati!="")
							SostituisciTestoTraGraffe ($Dati_Veicolo, "{FONTEDATI}", $fonte_dati);
						SostituisciTestoTraGraffe ($Dati_Veicolo, "{TIPOVEICOLO}", $tipo_veicolo);
						SostituisciTestoTraGraffe ($Dati_Veicolo, "{MARCAVEICOLO}", $marca_veicolo);
						SostituisciTestoTraGraffe ($Dati_Veicolo, "{MODELLOVEICOLO}", $modello_veicolo);
						SostituisciTestoTraGraffe ($Dati_Veicolo, "{TARGAVEICOLO}", $targa_veicolo);

						$Premesso_Considerato = $testo->Premesso_Considerato;
						SostituisciTestoTraGraffe ($Premesso_Considerato, "{UTENTE}", $nome_utente);
						$Opposizione_Testo = $testo->Opposizione_Testo;
						
						$Beni_Strumentali_Testo = $testo->Beni_Strumentali_Testo;
						SostituisciTestoTraGraffe ($Beni_Strumentali_Testo, "{GESTORE}", strtoupper($gestore->Denominazione));
						SostituisciTestoTraGraffe ($Beni_Strumentali_Testo, "{SEDEGESTORE}", $righe_gestore['Senza_Provincia']);
						SostituisciTestoTraGraffe ($Beni_Strumentali_Testo, "{RECAPITIGESTORE}", $recapiti_gestore);
						
						$Valutazione_Strumentale = $testo->Valutazione_Strumentale;
						SostituisciTestoTraGraffe ($Valutazione_Strumentale, "{SPESASTIMABENI}", $stima_beni);
						
						$Autotutela_Testo = $testo->Autotutela_Testo;
						SostituisciTestoTraGraffe ($Autotutela_Testo, "{UTENTE}", $nome_utente);
						SostituisciTestoTraGraffe ($Autotutela_Testo, "{GESTORE}", strtoupper($gestore->Denominazione));
						SostituisciTestoTraGraffe ($Autotutela_Testo, "{SEDEGESTORE}", $righe_gestore['Senza_Provincia']);
						SostituisciTestoTraGraffe ($Autotutela_Testo, "{RECAPITIGESTORE}", $recapiti_gestore);
						
						$Recupero_Somme = $testo->Recupero_Somme;
						SostituisciTestoTraGraffe ($Recupero_Somme, "{GESTORE}", strtoupper($gestore->Denominazione));
						
						$funz_resp = "Funzionario Responsabile ".$firma_resp[2]['nome'];
						if($gestore->Tipo == "Concessionario")
							$funz_resp = "Legale Rappresentante";
						SostituisciTestoTraGraffe ($Recupero_Somme, "{FUNZIONARIORESPONSABILE}",$funz_resp);
						
						$Notifica_IVG = $testo->Notifica_Istituto;
						SostituisciTestoTraGraffe ($Notifica_IVG, "{ISTITUTOVENDITE}", strtoupper($istituto_vendite->Denominazione." ".$istituto_vendite->Sigla_Forma_Giuridica));
						SostituisciTestoTraGraffe ($Notifica_IVG, "{SEDEISTITUTOVENDITE}", $sede_istituto['Senza_Provincia']);
						SostituisciTestoTraGraffe ($Notifica_IVG, "{RECAPITIISTITUTO}", $recapiti_istituto);
						
						if($Tipo_Invio_Istituto=="posta")
							SostituisciTestoTraGraffe ($Notifica_IVG, "{TIPOINVIO}", "tramite posta");
						else if($Tipo_Invio_Istituto=="mani")
							SostituisciTestoTraGraffe ($Notifica_IVG, "{TIPOINVIO}", "mediante consegna a mani");
						else if($Tipo_Invio_Istituto=="pec")
							SostituisciTestoTraGraffe ($Notifica_IVG, "{TIPOINVIO}", "al seguente indirizzo di posta elettronica certificata ".$PEC_Istituto );
						
						$Luogo = $testo->Luogo;
						SostituisciTestoTraGraffe ($Luogo, "{DATASTAMPA}", from_mysql_date($data_file));
						
						//IMPOSTAZIONI FIRMA FUNZIONARIO						
						$prime_firme[1]['intestazione'] 	= $firma_resp[2]['intestazione'];
						$prime_firme[1]['nome'] 			= $firma_resp[2]['nome'];
						$prime_firme[1]['firma'] 			= $firma_resp[2]['firma'];
							
						if( ucfirst($gestore->Tipo) == "Concessionario")
							$firma_1 = "Il rappresentante Legale";
						else
							$firma_1 = $firma_resp[1]['intestazione'];
						
						$prime_firme[2]['intestazione'] 	= $firma_1;
						$prime_firme[2]['nome'] 			= $firma_resp[1]['nome'];
						$prime_firme[2]['firma'] 			= $firma_resp[1]['firma'];
						
						
						//UFFICIALE
						$Intestazione_Relata_Ufficiale_Giudiziario = $testo->Intestazione_Relata_Ufficiale_Giudiziario;
						SostituisciTestoTraGraffe ($Intestazione_Relata_Ufficiale_Giudiziario, "{SEDETRIBUNALE}", strtoupper($tribunale->Comune));
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
						SostituisciTestoTraGraffe ($Ufficiale_Pignoramento, "{GESTORE}", strtoupper($gestore->Denominazione));
						SostituisciTestoTraGraffe ($Ufficiale_Pignoramento, "{UFFICIALE}", $testo_ufficiale);
						SostituisciTestoTraGraffe ($Ufficiale_Pignoramento, "{INGIUNZIONE}", $ing_completa);
						
						$Assoggetto_Pignoramento = $testo->Assoggetto_Pignoramento;
						$Assoggetto_Testo = $testo->Assoggetto_Testo;
						SostituisciTestoTraGraffe ($Assoggetto_Testo, "{TIPOVEICOLO}", $pre_tipo_veicolo." ".$tipo_veicolo);
						SostituisciTestoTraGraffe ($Assoggetto_Testo, "{MARCAVEICOLO}", $marca_veicolo);
						if($fonte_dati!="")
							SostituisciTestoTraGraffe ($Assoggetto_Testo, "{FONTEDATI}", $fonte_dati);
						SostituisciTestoTraGraffe ($Assoggetto_Testo, "{MODELLOVEICOLO}", $modello_veicolo);
						SostituisciTestoTraGraffe ($Assoggetto_Testo, "{TARGAVEICOLO}", $targa_veicolo);
						if($data_visura!="")
							SostituisciTestoTraGraffe ($Assoggetto_Testo, "{DATAVISURA}", $data_visura);
						SostituisciTestoTraGraffe ($Assoggetto_Testo, "{UTENTE}", $nome_utente);
						
						$Ingiungo = $testo->Ingiungo;
						$Ingiungo_Testo = $testo->Ingiungo_Testo;
						SostituisciTestoTraGraffe ($Ingiungo_Testo, "{UTENTE}", $nome_utente);
						
						$Invito = $testo->Invito;
						$Invito_Testo = $testo->Invito_Testo;

						$Avverto = $testo->Avverto;
						$Avverto_Testo = $testo->Avverto_Testo;
						
						$Intimo = $testo->Intimo;
						$Intimo_Testo = $testo->Intimo_Testo;
						SostituisciTestoTraGraffe ($Intimo_Testo, "{ISTITUTOVENDITE}", $istituto_vendite->Denominazione." ".$istituto_vendite->Sigla_Forma_Giuridica);
						SostituisciTestoTraGraffe ($Intimo_Testo, "{SEDEISTITUTOVENDITE}", $sede_istituto['Senza_Provincia']);
						SostituisciTestoTraGraffe ($Intimo_Testo, "{RECAPITIISTITUTO}", $recapiti_istituto);
						
						$Comunico = $testo->Comunico;
						$Comunico_Testo_1 = $testo->Comunico_Testo_1;
						$Comunico_Testo_2 = $testo->Comunico_Testo_2;
						
						$relata_notifica = $testo->Relata_Ufficiale;
						SostituisciTestoTraGraffe ($relata_notifica, "{UFFICIALE}", $testo_ufficiale);
						SostituisciTestoTraGraffe ($relata_notifica, "{UTENTE}", $nome_utente);						
						SostituisciTestoTraGraffe ($relata_notifica, "{RESIDENZAUTENTE}", $indirizzo_senza_provincia);
						SostituisciTestoTraGraffe ($relata_notifica, "{TIPOINVIO}", "consegna a mani / servizio postale ai sensi di legge");
												
						//FIRMA FINALE
						$qual_firma_notifica = stripslashes($testo->Qualifica_Firma_Notifica);
						$firma_notifica = stripslashes($testo->Firma_Notifica);
						
						//IMPOSTAZIONI FIRMA FINALE
						if($Ufficiale_Consegna == "giudiziario")
							$testo_firma['intestazione'] 	= "L'Ufficiale Giudiziario";
						else
							$testo_firma['intestazione']	= "L'Ufficiale della riscossione";
						
						$testo_firma['nome'] 			= "_____________________";
						$testo_firma['firma'] 			= "";
						
						
						
						
/**
 ///////////////////////////////		PDF	    //////////////////////////////////
*/
	
							
	for($copia=0;$copia<2;$copia++)
	{
		if($stampa_select=="DEFINITIVA")
		{
			$pdf = new clsPdf_("P", "mm", "A4", true, 'UTF-8', false);
		}
		
		$pdf->SetLineWidth(0.2);
		$pdf->SetMargins(7.0, 7.0, 7.0);
		$width_page = $pdf->getPageWidth() - 7;
		
		if($copia==0)
			$tipo_copia = "ORIGINALE";
		else if($copia==1)
			$tipo_copia = "COPIA";
			
		
	/**
	 * 		//////////////	PAGINA 1	//////////////
	*/
						
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	$pdf->SetAutoPageBreak(false);
	$pdf->SetCellPadding(0);
	$pdf->AddPage('P');

//////////////	CORPO Pagina 1	//////////////					
	
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->MultiCell(0, 0, $tipo_copia , 0, 'L', 0, 1);
	
	$pdf->Ln(2);
	$pdf->SetFont('Arial', 'B', 15);
	if($Ufficiale_Consegna=="giudiziario")
		$pdf->MultiCell(0, 0, $Titolo_Oggetto , 0, 'C', 0, 1);
	$pdf->SetFont('Arial', 'B', 9);
	$pdf->MultiCell(0, 0, $Sottotitolo_Oggetto , 0, 'C', 0, 1);
	$pdf->MultiCell(0, 0, $Intestazione_Pignoramento , 0, 'C', 0, 1);
	$pdf->SetFont('Arial', 'B', 9);
	$pdf->MultiCell(0, 0, $riferimenti_atto , 0, 'C', 0, 1);
	
	//PREMESSO
			$pdf->Ln(2);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Ufficiale_Responsabile." ".$Legale_Rappresentante."\n" , 0, 'J', 0, 1);
			$pdf->Ln(2);
			
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Premesso , 0, 'C', 0, 1);
			
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Atti_Notificati."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', 'B', 8.5);
			for($i=0;$i<count($atti_notificati);$i++)
				$pdf->MultiCell(0, 0, ($i+1).") ".$atti_notificati[$i]."\n" , 0, 'J', 0, 1);
				$pdf->Ln(1);
			
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Premesso_Testo."\n" , 0, 'J', 0, 1);
			$pdf->Ln(2);
						
			//IMPORTI INGIUNZIONE
			$array_width = array(160,10,10,15);
			$array_align = array("R","C","R","R");
			$tot = array_sum( $array_width );
			$margine = $pdf->getMargins();

			$array_value = array("Ripresa totale debito precedente" , "","Euro" , $dovuto_atto);
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			$pdf->Ln(1);
			$array_value = array("Eventuale importo pagato successivamente alla notifica degli atti ingiuntivi e intimativi" , "-","Euro" , $pagamenti_atto);
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			$pdf->Ln(1);
			$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY() ) ;
			$pdf->Ln(1);
			$pdf->SetFont('Arial', 'B', 8.5);
			$array_value = array("Totale debito precedente" , "=","Euro" , $Importo_Dovuto);
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			
			for($x_spesa=1;$x_spesa<count($Spese_Array)+1;$x_spesa++)
			{
				$pdf->SetFont('Arial', '', 8.5);
				if($Spese_Array[$x_spesa]['tipo_totale']==1)
				{
					$query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$Spese_Array[$x_spesa]['ID']."'";
					$descrizione_tariffa = single_query($query_tariffa);
					
					$pdf->Ln(1);
					$array_value = array( $descrizione_tariffa, "+","Euro" , conv_num($Spese_Array[$x_spesa]['rimborso']) );
					crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
				}
			}
			
			$pdf->Ln(1);
			$array_value = array("Spese postali/diritti di notifica", "+","Euro" , $Totale_Spese_Notifica );
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			
			$pdf->Ln(1);
			$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY() ) ;
			$pdf->Ln(1);
			$pdf->SetFont('Arial', 'B', 8.5);
			$array_value = array( "TOTALE 1", "=","Euro" , $TOTALI_ARRAY[1] );
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			
			if($TOTALI_ARRAY[2]!=0)
			{
				
				$pdf->SetFont('Arial', '', 8.5);
				for($x_spesa=1;$x_spesa<count($Spese_Array)+1;$x_spesa++)
				{
					if($Spese_Array[$x_spesa]['tipo_totale']==2)
					{
							$query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$Spese_Array[$x_spesa]['ID']."'";
							$descrizione_tariffa = single_query($query_tariffa);
								
							$pdf->Ln(1);
							$array_value = array( $descrizione_tariffa, "+","Euro" , conv_num($Spese_Array[$x_spesa]['rimborso']) );
							crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
					}
				}
				
				$pdf->Ln(1);
				$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY() ) ;
				$pdf->Ln(1);
				$pdf->SetFont('Arial', 'B', 8.5);
				$array_value = array( "TOTALE 2", "=","Euro" , $TOTALI_ARRAY[2] );
				crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			}
				
			if($TOTALI_ARRAY[3]!=0)
			{
				
				$pdf->SetFont('Arial', '', 8.5);
				for($x_spesa=1;$x_spesa<count($Spese_Array)+1;$x_spesa++)
				{
					if($Spese_Array[$x_spesa]['tipo_totale']==3)
					{
							$query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$Spese_Array[$x_spesa]['ID']."'";
							$descrizione_tariffa = single_query($query_tariffa);
								
							$pdf->Ln(1);
							$array_value = array( $descrizione_tariffa, "+","Euro" , conv_num($Spese_Array[$x_spesa]['rimborso']) );
							crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
					}
				}
				
				$pdf->Ln(1);
				$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY() ) ;
				$pdf->Ln(1);
				$pdf->SetFont('Arial', 'B', 8.5);
				$array_value = array( "TOTALE 3", "=","Euro" , $TOTALI_ARRAY[3] );
				crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			
			}
			
			$pdf->Ln(1);
			$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Informazioni, 0, 'C', 0, 1);
			$pdf->Ln(1);
			$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Informazioni_Testo."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
			
			$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Informo, 0, 'C', 0, 1);
			$pdf->Ln(1);
			$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Conto_Corrente."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Informo_Testo."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Informo_Testo_2."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Informo_Testo_3."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Informo_Testo_4."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
			
			$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Considerato, 0, 'C', 0, 1);
			$pdf->Ln(1);

			$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Ingiunzione_Fiscale."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Legislatore."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Dati_Veicolo."\n" , 0, 'J', 0, 1);			
		
			
//////////////	FINE CORPO Pagina 1	//////////////
			
	//PIE DI PAGINA 1
	$pdf->SetY(-7);
	$pdf->SetFont('helvetica', 'N', 7);
	$pdf->Cell(0, 5, "Pag. 1/2 - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			
	if($stampa_select == "PROVVISORIA")
		$pdf->stampa_provvisoria();
	
	/**
	 * 		//////////////	PAGINA 2	//////////////
	 */
	
	$pdf->SetCellPadding(0);
	$pdf->AddPage('P');
	
	//////////////	CORPO Pagina 2	//////////////

	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Premesso_Considerato , 0, 'C', 0, 1);
	$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Opposizione_Testo."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Beni_Strumentali_Testo."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $Valutazione_Strumentale."\n", 0, 'J', 0, 1);
			$pdf->Ln(1);
			$pdf->MultiCell(0, 0, $Autotutela_Testo."\n", 0, 'J', 0, 1);
			$pdf->Ln(1);
			$pdf->MultiCell(0, 0, $Recupero_Somme."\n", 0, 'J', 0, 1);
			$pdf->Ln(1);
			$pdf->MultiCell(0, 0, $Notifica_IVG."\n", 0, 'J', 0, 1);
			$pdf->Ln(2);
			$pdf->Cell(60, 0, $Luogo , 0, 1, 'C', 0, '', 0);
			$pdf->firma_pdf($prime_firme);
	
			$pdf->Ln(2);
			
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Ufficiale_Pignoramento."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
		
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Assoggetto_Pignoramento, 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Assoggetto_Testo."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
		
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Ingiungo, 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Ingiungo_Testo."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
		
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Invito, 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Invito_Testo."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
	
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Avverto, 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Avverto_Testo."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
	
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Intimo, 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $Intimo_Testo."\n" , 0, 'J', 0, 1);
			$pdf->Ln(1);
			
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Comunico , 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, "1) ".$Comunico_Testo_1."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, "2) ".$Comunico_Testo_2."\n", 0, 'J', 0, 1);
			$pdf->Ln(2);
			
	
			
	//RELAZIONE
	$pdf->SetFont('Arial', 'B', 8.5);
			$pdf->MultiCell(0, 0, $Intestazione_Relata , 0, 'C', 0, 1);
		if($Sottointestazione_Relata!="")
			$pdf->MultiCell(0, 0, $Sottointestazione_Relata , 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 8.5);
			$pdf->MultiCell(0, 0, $relata_notifica."\n", 0, 'J', 0, 1);
			$pdf->Ln(1);
			
			$pdf->Cell(122, 0, '' , 0, 0, 'C', 0, '', 0);
			$pdf->Cell(60,0, $testo_firma['intestazione'] , 0, 1,'C',0,'',0 );
			$pdf->Ln(5);
			$pdf->Cell(122, 0, '' , 0, 0, 'C', 0, '', 0);
			$pdf->Cell(60,0, $testo_firma['nome'] , 0, 1,'C',0,'',0 );	
			


//////////////	FINE CORPO Pagina 2	//////////////		

	//PIE DI PAGINA 2
	$pdf->SetY(-7);
	$pdf->SetFont('helvetica', 'N', 7);
	$pdf->Cell(0, 5, "Pag. 2/2 - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	if($stampa_select == "PROVVISORIA")
		$pdf->stampa_provvisoria();
	
/**
 //////////////////////////////////////////////////////////////////////////////
*/		

	if($copia==1)
	{
	
		/**
		 * 		//////////////	PAGINA BOLLETTINO	//////////////
		 */
		if(($autorizzazione_1!=false || $td_1=="123") || ($autorizzazione_2!=false || $td_2=="123"))
		{
		
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->AddPage('L');
		
			$pdf->SetMargins(0, 0, 0);
		
			//////////////	CORPO Pagina 3	//////////////
		
			if($autorizzazione_1!=false || $td_1=="123")
			{
				$pdf->crea_bollettino();
				$pdf->logo_bollettino($image_file);
				$pdf->scelta_td_bollettino($td_1, $quinto_campo , $TOTALI_ARRAY[1] , $ctrl_importo_1 , $numeroContoCorrente );
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
				$pdf->scelta_td_bollettino($td_2, $quinto_campo , $TOTALI_ARRAY[2] , $ctrl_importo_2 , $numeroContoCorrente, 'due');
				$pdf->iban_bollettino($iban,'due');
				$pdf->intestatario_bollettino($intestatarioConto,'due');
				$pdf->causale_bollettino($riga1causale, $riga2causale,'due');
				$pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario,'due');
				$pdf->autorizzazione_bollettino($autorizzazione_2,'due');
			}
			
			if($stampa_select == "PROVVISORIA")
				$pdf->stampa_provvisoria();
			
			/**
			 * 		//////////////	PAGINA VUOTA	//////////////
			 */
			
			$pdf->setPrintHeader(false);
			$pdf->SetAutoPageBreak(false);
			$pdf->SetCellPadding(0);
			$pdf->AddPage('L');
			
			if($stampa_select == "PROVVISORIA")
				$pdf->stampa_provvisoria();
		
		}
	
	}
		
	if($stampa_select=="DEFINITIVA")
	{
		if($copia==0)
		{
			$pdf_originale = $pdf;
		}
		else if($copia==1)
		{
			$pdf_copia = $pdf;
		}
	}
	
	
	/**
	 //////////////////////////////////////////////////////////////////////////////
	 */
	
	}//CHIUSURA FOR ORIGINALE/COPIA
	
	if($stampa_select=="DEFINITIVA")
	{
		mysql_query('BEGIN');
			
		$salva = new pignoramento($pignoramento->ID, $c);
			
		if($salva->Stato_Stampa != "Stampato")
		{
			$salva->Data_Stampa = to_mysql_date($data_stampa);;
			$salva->Stato_Stampa = "Stampato";
	
			$control_salva = $salva->Update($pignoramento->ID);
				
			if( $control_salva )
			{
				mysql_query('COMMIT');
				
				$pdf_originale->Output( $file_stampa_originale , 'F');
				$pdf_copia->Output( $file_stampa_debitore , 'F');
				
				$arrayConcat[] = $file_stampa_originale;
				$arrayConcat[] = $file_stampa_debitore;
			}
			else
			{
				mysql_query('ROLLBACK');
			}
		}
		else if($salva->Stato_Stampa == "Stampato")
		{
			$pdf_originale->Output( $file_stampa_originale , 'F');
			$pdf_copia->Output( $file_stampa_debitore , 'F');

			$arrayConcat[] = $file_stampa_originale;
			$arrayConcat[] = $file_stampa_debitore;
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

	if($stampa_select=="PROVVISORIA")
	{
		cancella_files($stampa_dir, 7);
		if($cont_result == 0)
		{
			echo "<script>nessun_risultato();</script>";
		}
		else
		{
			
			$pdf->Output( $file_stampa , 'F');
			
			flush(); ob_flush(); flush(); ob_flush();
			echo "<script>fine('Elaborazione completata');</script>";
			flush(); ob_flush(); flush(); ob_flush();
						
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
			
			$fileCompletoUnito = $concat_dir."/Pignoramento_Veicolo_Merge_".$c."_".$data_file."_".$ora_file.".pdf";
			
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

			echo "<script>fine_merge('Creazione file in corso... Il tempo previsto per le operazioni � di circa ".$tempo_previsto.".');</script>";
			flush(); ob_flush(); flush(); ob_flush();
			
			set_time_limit($tempo_previsto_sec);
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
			echo "<input type=hidden name=pigno_val value='veicolo'>";
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
		{?>
	
			<form name='pec_form' id='pec_form' method='post' action='pec_pignoramento.php'>
			<input type=hidden name=pigno_val value='veicolo'>
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