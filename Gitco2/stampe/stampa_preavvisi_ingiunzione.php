<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";
include_once FPDI . "/fpdi.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$stampa_select = strtoupper(get_var('stampa_select'));

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
$stemmaComune = $comune->Stemma_1;
$gestore = $comune->Gestore;

$forma = new forma_giuridica();
$array_forma = $forma->array_completo();

$gestore_id = $comune->Gestore_ID;

if ($gestore_id == 0) 
{
	$ente = "COMUNE";
	$gestione_comune = "";
}
else 
{	
	$ente = "CONCESSIONARIO";
	$gestione_comune = "Gestione: Comune di ".$comune->Nome;	
}

$info_com = $comune->Ente;
$comune_nome = $info_com->Nome;
$comune_prov = $info_com->Pro_Nome;

$com_gestore = new comune($gestore->CC);
$provincia_gestore = $com_gestore->Pro_Nome;

$ind_com_1 = "";
if($comune->Gestore->Toponimo!="")
	$ind_com_1 = ucwords(strtolower($comune->Gestore->Toponimo));
if($comune->Gestore->Toponimo!="" && $comune->Gestore->Civico!="" && $comune->Gestore->Civico!=0)
	$ind_com_1.=", ".$comune->Gestore->Civico;
if($comune->Gestore->Toponimo!="" && $comune->Gestore->Esponente)
	$ind_com_1 .= $comune->Gestore->Esponente;
if($comune->Gestore->Toponimo!="" && $comune->Gestore->Interno)
	$ind_com_1.="/".$comune->Gestore->Interno;
if($comune->Gestore->Toponimo!="" && $comune->Gestore->Dettagli)
	$ind_com_1.=", ".$comune->Gestore->Dettagli;

$ind_com_2 = "";
if($comune->Gestore->Comune!="")
	$ind_com_2 = $comune->Gestore->Cap." ".$comune->Gestore->Comune." (".$comune->Gestore->Provincia. ")";

$indirizzoComuneIntestazione = $ind_com_1;
$cittaComuneIntestazione = $ind_com_2;

if($ind_com_1!="" || $ind_com_2 != "")
{
	$sede_gestore = "Sede: ".$indirizzoComuneIntestazione ." - ".$cittaComuneIntestazione;

	if($ind_com_1=="")
		$sede_gestore = "Sede: ".$cittaComuneIntestazione;
	else if($ind_com_2=="")
		"Sede: ".$indirizzoComuneIntestazione;
}
else
	$sede_gestore = "";

if($comune->Gestore->Partita_Iva!="" || $comune->Gestore->Codice_Fiscale!="")
{
	$partitaIvaComuneIntestazione = "P.I.: " . $comune->Gestore->Partita_Iva ."  -  C.F.: ".$comune->Gestore->Codice_Fiscale;

	if($comune->Gestore->Partita_Iva == "")
		$partitaIvaComuneIntestazione = "C.F.: ".$comune->Gestore->Codice_Fiscale;
	else if($comune->Gestore->Codice_Fiscale == "")
		$partitaIvaComuneIntestazione = "P.I.: " . $comune->Gestore->Partita_Iva;
}
else
	$partitaIvaComuneIntestazione = "";

if($comune->Gestore->Telefono!="" || $comune->Gestore->Fax != "")
{
	$telefonoComuneIntestazione = "Tel: " . $comune->Gestore->Telefono . "  -  Fax: " . $comune->Gestore->Fax;

	if($comune->Gestore->Telefono == "")
		$telefonoComuneIntestazione = "Fax: ".$comune->Gestore->Fax;
	else if($comune->Gestore->Fax == "")
		$telefonoComuneIntestazione = "Tel:  " . $comune->Gestore->Telefono;
}
else
	$telefonoComuneIntestazione = "";

if($comune->Gestore->Mail!="" || $comune->Gestore->Sito != "")
{
	$emailComuneIntestazione = "Email: " . $comune->Gestore->Mail." - Sito: " . $comune->Gestore->Sito;

	if($comune->Gestore->Sito=="")
		$emailComuneIntestazione = "Email: " . $comune->Gestore->Mail;
	else if($comune->Gestore->Mail=="")
		$emailComuneIntestazione = "Sito: " . $comune->Gestore->Sito;
}
else
	$emailComuneIntestazione = "";


if ($ente == "CONCESSIONARIO")  // se è sarida
{
	$tipoEnte = "Concessionario " . $comune->Gestore->Denominazione;
	$provinciaIntestazione = "";
	$indirizzoEnte = $comune->Gestore->Toponimo . " " .
			$comune->Gestore->Civico . ", " .
			$comune->Gestore->Cap . " " .
			$comune->Gestore->Comune . " (" . $comune->Gestore->Provincia . ")";
}
else // se è comune
{
	$tipoEnte = "Comune di " . $nome_com;
	$provinciaIntestazione = "Provincia di " . $provincia_gestore;
	$indirizzoEnte = $indirizzoComuneIntestazione . ", " . $cittaComuneIntestazione;
}

$fax = $comune->Gestore->Fax;
$mail = $comune->Gestore->Mail; 
$PEC = $comune->Gestore->PEC;

$ufficio = $comune->Ufficio;
$ufficio_id = $comune->Ufficio_ID;

if($ufficio_id != 0)
{

	$nomeUfficio = $ufficio->Denominazione;


	$ind_uff_1 = "";
	if($ufficio->Toponimo!="")
		$ind_uff_1 = ucwords(strtolower($ufficio->Toponimo));
	if($ufficio->Toponimo!="" && $ufficio->Civico!="" && $ufficio->Civico!=0)
		$ind_uff_1.=", ".$ufficio->Civico;
	if($ufficio->Toponimo!="" && $ufficio->Esponente)
		$ind_uff_1 .= $ufficio->Esponente;
	if($ufficio->Toponimo!="" && $ufficio->Interno)
		$ind_uff_1.="/".$ufficio->Interno;
	if($ufficio->Toponimo!="" && $ufficio->Dettagli)
		$ind_uff_1.=", ".$ufficio->Dettagli;

	$ind_uff_2 = "";
	if($ufficio->Comune!="")
		$ind_uff_2 = $ufficio->Cap." ".$ufficio->Comune." (".$ufficio->Provincia. ")";

	$indirizzoUfficioIntestazione = $ind_uff_1;
	$cittaUfficioIntestazione = $ind_uff_2;

	if($ind_uff_1!="" || $ind_uff_2 != "")
	{
		$sede_ufficio = "Sede: ".$indirizzoUfficioIntestazione ." - ".$cittaUfficioIntestazione;

		if($ind_uff_1=="")
			$sede_ufficio = "Sede: ".$cittaUfficioIntestazione;
		else if($ind_uff_2=="")
			"Sede: ".$indirizzoUfficioIntestazione;
	}
	else
		$sede_ufficio = "";

	if($ufficio->Telefono!="" || $ufficio->Fax != "")
	{
		$telefonoUfficioIntestazione = "Tel: " . $ufficio->Telefono . "  -  Fax: " . $ufficio->Fax;

		if($ufficio->Telefono == "")
			$telefonoUfficioIntestazione = "Fax: ".$ufficio->Fax;
		else if($ufficio->Fax == "")
			$telefonoUfficioIntestazione = "Tel:  " . $ufficio->Telefono;
	}
	else
		$telefonoUfficioIntestazione = "";

	if($ufficio->Mail!="" || $ufficio->PEC != "")
	{
		$emailUfficioIntestazione = "Email: " . $ufficio->Mail." - PEC: " . $ufficio->PEC;

		if($ufficio->PEC=="")
			$emailUfficioIntestazione = "Email: " . $ufficio->Mail;
		else if($ufficio->Mail=="")
			$emailUfficioIntestazione = "PEC: " . $ufficio->PEC;
	}
	else
		$emailUfficioIntestazione = "";

	$orarioUfficio = $ufficio->Orario;

	/////////////////////
	$lunghezza = strlen($orarioUfficio);
	if($lunghezza <= 50)
	{
		$orarioUfficio_1 = $orarioUfficio;
		$orarioUfficio_2 = "";
	}
	else if($lunghezza<=100)
	{
		$pos = 50;
		//echo $pos;
		for( $i=0; $i<$pos; $i++)
		{
			$carattere = substr($orarioUfficio, $pos-$i,1);
			//echo $carattere."*";
			if($carattere==" ")
			{
				//echo $pos-$i;
				$pos = $pos-$i;
				break;
			}
		}

		$orarioUfficio_1 = substr($orarioUfficio, 0 , $pos);
		$orarioUfficio_2 = substr($orarioUfficio, $pos+1);
	}
	///////////////////////

	$fax = $ufficio->Fax;
	$mail = $ufficio->Mail;
	$PEC = $ufficio->PEC;
	
}

class MYPDF extends TCPDF {
	
	public function Header() {
		
	if($this->tipo_stampa == "PROVVISORIA" )
		{
			$this->SetXY(10, 250);
			$this->StartTransform();
			$this->Rotate(50);
			$this->SetFont('Helvetica', '', 32);
			$this->SetTextColor(190);			
			$this->Cell(280,0,'STAMPA PROVVISORIA',0,1,'C',0,'');
			$this->StopTransform();
		}

	}
	
}

//PREPARAZIONE ELENCO
$data_file = date('Y-m-d_H-i-s');

if($stampa_select == "FLUSSO")
{
	$flusso_dir = crea_dir( ATTI . "/Preavvisi_Ingiunzioni/RAR/".$c );
	$file_flusso = "stampe_preavvisi_ingiunzione_".$data_file;
	$file_flusso_rar = $flusso_dir."/".$file_flusso.".rar";
	$file_flusso_txt = $flusso_dir."/".$file_flusso.".txt";
	
	$download = $file_flusso_rar;
	
}
else 
{
	$stampa_dir = crea_dir( ATTI ."/". $c . "/Preavvisi_Ingiunzioni/PDF" );
	$file_stampa = $stampa_dir."/stampe_preavvisi_ingiunzione_".$data_file.".pdf";
	
	$download = $file_stampa;
}

$vedi_file = mostra_file_path($download);

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
	//$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Stampe ingiunzioni' onclick='mostra_file();'>");
}

function mostra_file()
{
	window.name = "Stampa";
	window.open('<?php echo $vedi_file; ?>',"Stampa");
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
		<font class="titolo font18 text_center">Stampa Preavvisi Ingiunzione</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div id=vedi_file></div>
		
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
$campi_stati = array("Stato" , "Stato_Stampa" , "Stato_Esecuzione");
$valori_stati = array ( $stato_notif , $stato_stampa , $stato_esec );

$query_stati = where_campi($campi_stati, $valori_stati);

$campi_array = array ("Data_Elaborazione" , "Data_Notifica" , "Data_Stampa" );
$array_da_data = array( to_mysql_date($da_elab) , to_mysql_date($da_notif) , to_mysql_date($da_stampa) );
$array_a_data = array( to_mysql_date($a_elab) , to_mysql_date($a_notif) , to_mysql_date($a_stampa) );

$query_date = da_a_data_array( $c , "Ingiunzione" , $campi_array , $array_da_data , $array_a_data , $query_stati );
$array_atti = mysql_array($query_date);

$num_atti = count($array_atti);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

$arrayIntestazione = array (
		
						"GESTORE_INTESTAZIONE",
						"PROVINCIA_INTESTAZIONE",
						"SEDE_INTESTAZIONE",
						"IVA_INTESTAZIONE",
						"TELEFONO_INTESTAZIONE",
						"EMAIL_SITO_INTESTAZIONE",
						"SERVIZIO",
						"COMUNE_GESTITO",	
							
						"DESTINATARIO",
						"INDIRIZZO_DESTINATARIO_1",
						"INDIRIZZO_DESTINATARIO_2",
						"CITTA_DESTINATARIO",
							
						"OGGETTO",
						"PRIMO_TESTO" ,
						"SECONDO_TESTO" ,
 						"TITOLO_IMPORTI" ,
						"FINALE_1" ,
						"FINALE_2" ,						
						"SALUTI" ,
						"PRIMO_RESPONSABILE" ,
						"PRIMA_FIRMA" ,
						"SECONDO_RESP" ,
						"SECONDA_FIRMA" ,
						"FIRMA_AUTOGRAFA" ,
						
						"IMPORTANTE",
						"IMPORTANTE TESTO",
						
						"CDS_TITOLO" ,
						"CDS_TESTO_1",
						"CDS_TESTO_2" ,
						"CDS_TESTO_3" ,
						
						"TRIBUTO_TILOLO",
						"TRIBUTO_TESTO" ,
						
						"PAG_MANCATO_1_TITOLO" ,
						"PAG_MANCATO_1_TESTO" ,
						"PAG_MANCATO_2_TITOLO" ,
						"PAG_MANCATO_2_TESTO" ,
						
						"PROVVEDIMENTI",
						"ESITO 1 TITOLO" ,
						"ESITO_1_TESTO",
						"CASO_A" ,
						"CASO_B" ,
						"CASO_C" ,
						"CASO_D" ,
						
						"ESITO_2_TITOLO" ,
						"ESITO_2_TESTO" ,						
						
						"ESITO_3_TITOLO" ,
						"ESITO_3_TESTO" ,
						
						"ESITO_4_TITOLO" ,
						"ESITO_4_TESTO"
		
);




if($stampa_select == "FLUSSO")
{

	$testoRar = fopen ( $file_flusso_txt , "w+");

	foreach ($arrayIntestazione as $campo => $valore)
	{
		fputs ($testoRar, $valore, strlen($valore));
		fputs ($testoRar, Chr(9), 1);   // tabulatore
	}

	fputs ($testoRar, Chr(13) . Chr(10), 2);   // a capo

}
else
{
	/**
	 ///////////////////////////////		PDF	    //////////////////////////////////
	 */

	$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	$pdf->SetMargins(7.0, 10.0, 7.0);
	$width_page = $pdf->getPageWidth() - 7;
	$pdf->setValues(array( "tipo_stampa" , "tipo_gestore" , "stemma_gestore" ) , array( $stampa_select , $ente , $stemmaComune ));
	if($pdf->tipo_gestore == "CONCESSIONARIO")
		$image_file = "/gitco2/immagini/sarida_logo.png";
	else
		$image_file = $pdf->stemma_gestore;

	/**
	 //////////////////////////////////////////////////////////////////////////////
	*/

}
		
	$cont_result = 0;
	for( $l=0; $l < $num_atti; $l++ )//FOR ATTI
	{	
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
						set_time_limit(30);
						
						$num_giorni = 60;
						$utente_ing = "contribuente";
						
						$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
						
						$ID_partita = $partita->Comune_ID;
						$anno_rif = $partita->Anno_Riferimento;
						$settore = $partita->Tipo;
						switch($settore)
						{
							case "CDS":	$tipo_ing = "Riscossione violazioni al codice della strada";
										$num_giorni = 30;
										$utente_ing = "tragressore"; 
										
										break;
						}
						
						$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
						$forma_descr = "";
						if($utente->Forma_Giuridica!='')
						{
							$index_value = $utente->Forma_Giuridica;
							$forma_descr = $array_forma[$index_value]['Sigla'];
						}
							
						$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
						$utente_id = $utente->Comune_ID;
						
						if($utente->Domicilio!=null)
							$indirizzo = $utente->Domicilio;
						else if($utente->Recapito!=null)
							$indirizzo = $utente->Recapito;
						else 
							$indirizzo = $utente->Residenza;
						
						if($indirizzo->Paese=="Italia")
						{			
							$ind_1 = $indirizzo->Toponimo->Nome;
							if($indirizzo->Frazione)			
								$ind_1 = $indirizzo->Frazione.", ".$ind_1;							
							
							if($indirizzo->Civico)
								$ind_1.= ", ".$indirizzo->Civico;
							if($indirizzo->Esponente)
								$ind_1.= $indirizzo->Esponente;
							if($indirizzo->Interno)
								$ind_1.="/".$indirizzo->Interno;
							if($indirizzo->Dettagli)
								$ind_1.=", ".$indirizzo->Dettagli;
						}
						else 
						{
							$ind_1 = $indirizzo->Toponimo->Nome;
							if($indirizzo->Frazione)
								$ind_1 = $indirizzo->Frazione.", ".$ind_1;
						}
								
						
						$ind_2 = $indirizzo->Cap." ".$indirizzo->Comune." ".$indirizzo->Provincia;
						$ind_3 = $indirizzo->Paese;
						
						$residenzaDestinatario = $ind_1; // indirizzo destinatario
						
						/////////////////////
						$lunghezza = strlen($residenzaDestinatario);
						if($lunghezza<50)
						{
							$residenzaDestinatario_1 = $residenzaDestinatario;
							$residenzaDestinatario_2 = "";
						}
						else if($lunghezza<=100)
						{
							$pos = $lunghezza/2;
							//echo $pos;
							for( $i=0; $i<$pos; $i++)
							{
							$carattere = substr($residenzaDestinatario, $pos-$i,1);
							//echo $carattere."*";
							if($carattere==" ")
							{
							//echo $pos-$i;
							$pos = $pos-$i;
							break;
							}
							}
						
							$residenzaDestinatario_1 = substr($residenzaDestinatario, 0 , $pos);
							$residenzaDestinatario_2 = substr($residenzaDestinatario, $pos+1);
						}
						///////////////////////
						
						$comuneDestinatario = $ind_2;
						$statoDestinatario = strtoupper($ind_3);
						
						$comuneDestinatario = strtoupper($comuneDestinatario);
						$statoDestinatario = strtoupper($statoDestinatario);
						
						$ing = new atto( $array_atti[$l]['ID'], $c );
							
						$ID_ing = $ing->Comune_ID;
						$anno_crono = $ing->Anno_Cronologico;
						$id_crono = $ing->ID_Cronologico;
						$rif = $ing->Riferimento;
						$rif_ing = $rif."-".$ID_partita."/".$anno_rif;
							
						$info_cart = $ing->Info_Cartella;
						
						$parametri = new parametri_annuali( $c , date("Y-m-d") , $settore);
						$CAD = $parametri->CAD;
						$spese_postali = $parametri->Spese_Postali;
						$interessi_magg = $parametri->Maggiorazione_Preavviso;
						$importo_minimo = $parametri->Importo_Minimo;
						
						//IMPORTI
						$sanz_originale = conv_num( number_format( $ing->Importo - $ing->Spese_Precedenti, 2 ) );
						$spese_prec = conv_num( number_format( $ing->Spese_Precedenti , 2 ) );
						$maggiorazione = conv_num( number_format( $ing->Interessi + $ing->Interessi_Precedenti , 2 ) );
						$spese_postali = conv_num( number_format( $spese_postali , 2 ) );
						$totale_dovuto = conv_num( number_format( $ing->Totale_Dovuto - $ing->Spese_Notifica + $spese_postali , 2 ) );
						$totale_dovuto_no_magg = conv_num( number_format( $ing->Totale_Dovuto - $ing->Interessi - $ing->Interessi_Precedenti - $ing->Spese_Notifica + $spese_postali , 2 ) );
						
						$pagamenti = $ing->Pagamento;
						$tot_pagamenti = 0.00;
						
						if( $pagamenti != null )
						{
							for($x=0;$x<count($pagamenti);$x++)
							{
								$tot_pagamenti += $pagamenti[$x]->Importo;
							}
						}
							
						$tot_pagamenti = conv_num ( number_format( $tot_pagamenti , 2 ) );
						
						if($interessi_magg=="si")
						{
							$tot_compl = conv_num ( number_format( conv_num( $totale_dovuto ) - conv_num( $tot_pagamenti ) , 2 ) );								
						}
						else
						{
							$tot_compl = conv_num ( number_format( conv_num( $totale_dovuto_no_magg ) - conv_num( $tot_pagamenti ) , 2 ) );
						}
						
						/**
						 * SE IL TOTALE E' INFERIORE ALL'IMPORTO MINIMO IL PREAVVISO NON ESCE
						 */
						if(conv_num($tot_compl) < $importo_minimo)
						{
							break;
						}
						
						$par_generali = new parametri_generali($c, $settore );
						
						$numeroContoCorrente = $par_generali->Num_Conto_1;  // numero conto corrente o conto postale
						$intestatarioConto = $par_generali->Int_Conto_1;  // intestatario conto corrente o conto postale
						
						if($stato_stampa!="Stampato")
							$data_stampa = date("d/m/Y");
						else 
							$data_stampa = from_mysql_date($ing->Data_Stampa);
						
						$para_ing = new parametri_testo_preavviso_ingiunzione(null);
						$myID = $para_ing->CercaParametroData($c, date("Y-m-d"));
						$testo = new parametri_testo_preavviso_ingiunzione($myID);
						
						$oggettoIngiunzione = $testo->Oggetto_Preavviso_Ingiunzione;
						
						SostituisciTestoTraGraffe ($oggettoIngiunzione, "{ENTE}", strtoupper($nome_com) );
						
						$primoTesto = $testo->Primo_Testo;
						
						$secondoTesto = $testo->Secondo_Testo;
 						SostituisciTestoTraGraffe ($secondoTesto, "{GIORNI}", $num_giorni );
 						SostituisciTestoTraGraffe ($secondoTesto, "{SALDO}", $tot_compl );					
						
						$titolo_importi = $testo->Intro_Somma_Testo;

						
						$finalePagina1 = $testo->Terzo_Testo;
						SostituisciTestoTraGraffe ($finalePagina1, "{NUMEROCONTO}", $numeroContoCorrente );
						SostituisciTestoTraGraffe ($finalePagina1, "{INTESTATARIOCONTO}", $intestatarioConto );
						
						$finalePagina2 = $testo->Quarto_Testo;						
						
						$saluti = $testo->Saluti_Testo;
						
						$primoResp = $testo->Ufficiale_Riscossione;
						$primaFirma = $testo->Nome_Ufficiale_Riscossione;
						$secondoResp = $testo->Ufficiale_Riscossione_2;
						$secondaFirma = $testo->Nome_Ufficiale_Riscossione_2;
						
						$firma_autografa = $testo->Stampa_Firma;
						
						$importante = $testo->Info_1_Titolo;
						$importante_testo = $testo->Info_1_Testo;
						
						$cds_titolo = $testo->CDS_Titolo;
						$cds_testo_1 = $testo->CDS_Testo_1;
						$cds_testo_2 = $testo->CDS_Testo_2;
						$cds_testo_3 = $testo->CDS_Testo_3;
						
						$tributo_titolo = $testo->Tributo_Titolo;
						$tributo_testo = $testo->Tributo_Testo;
						
						$pag_mancato_1_titolo = $testo->Info_2_Titolo;
						$pag_mancato_1_testo = $testo->Info_2_Testo;
						$pag_mancato_2_titolo = $testo->Info_3_Titolo;
						$pag_mancato_2_testo = $testo->Info_3_Testo;
						
						$provvedimenti = $testo->Avviso_Titolo;
						$esito_1_titolo = $testo->Esito_1_Titolo;
						$esito_1_testo = $testo->Esito_1_Testo;
						$caso_A = $testo->Caso_A_Testo;
						$caso_B = $testo->Caso_B_Testo;
						$caso_C = $testo->Caso_C_Testo;
						$caso_D = $testo->Caso_D_Testo;
						
						$esito_2_titolo = $testo->Esito_2_Titolo;
						$esito_2_testo = $testo->Esito_2_Testo;						
						
						$esito_3_titolo = $testo->Esito_3_Titolo;
						$esito_3_testo = $testo->Esito_3_Testo;
						
						$esito_4_titolo = $testo->Esito_4_Titolo;
						$esito_4_testo = $testo->Esito_4_Testo;
						
						
						if ($stampa_select == "FLUSSO")
						{
							$arrayTesti = array (
									$tipoEnte,
									$provinciaIntestazione,
									$sede_gestore,
									$partitaIvaComuneIntestazione,
									$telefonoComuneIntestazione,
									"Servizio: Riscossione coattiva",
									$gestione_comune,
							
									$nome_utente,  // destinatario
									$residenzaDestinatario_1,
									$residenzaDestinatario_2,
									$comuneDestinatario,
									$statoDestinatario,
							
									$oggettoIngiunzione ,
									$primoTesto ,
									$secondoTesto ,
			 						$titolo_importi ,
									$finalePagina1 ,
									$finalePagina2 ,						
									$saluti ,
									$primoResp ,
									$primaFirma ,
									$secondoResp ,
									$secondaFirma ,
									$firma_autografa ,
									
									$importante ,
									$importante_testo ,
									
									$cds_titolo ,
									$cds_testo_1 ,
									$cds_testo_2 ,
									$cds_testo_3 ,
									
									$tributo_titolo ,
									$tributo_testo ,
									
									$pag_mancato_1_titolo ,
									$pag_mancato_1_testo ,
									$pag_mancato_2_titolo ,
									$pag_mancato_2_testo ,
									
									$provvedimenti ,
									$esito_1_titolo ,
									$esito_1_testo ,
									$caso_A ,
									$caso_B ,
									$caso_C ,
									$caso_D ,
									
									$esito_2_titolo ,
									$esito_2_testo ,						
									
									$esito_3_titolo ,
									$esito_3_testo ,
									
									$esito_4_titolo ,
									$esito_4_testo 
							
							);
							
							foreach ($arrayTesti as $campo => $valore)
							{
								fputs ($testoRar, $valore, strlen($valore));
								fputs ($testoRar, Chr(9), 1);   // tabulatore
							}
								
							fputs ($testoRar, Chr(13) . Chr(10), 2);   // a capo
						}	//fine FLUSSO
						else
						{
							
/**
 ///////////////////////////////		PDF	    //////////////////////////////////
*/
	
	/**
	 * 		//////////////	PAGINA 1	//////////////
	*/
						
	$pdf->setPrintHeader(true);
	$pdf->SetAutoPageBreak(false);
	$pdf->AddPage();
	$pdf->SetCellPadding(0);
						
//////////////	APERTURA INTESTAZIONE	//////////////

	$pdf->Line(7, 8, $width_page, 8);//Linea di testa
						
	$dim = limita_dim_immagine($image_file, 19, 15);
	$pdf->Image($image_file, 7, 17, $dim[0], $dim[1],'','','C' );//Logo
	
	//GESTORE
	$pdf->SetMargins(27.0, 10.0,7.0);	$pdf->ln(0);
	
	$pdf->SetFont('Arial', 'B', 7);
			$pdf->Cell (83.0, 0, $tipoEnte, 0, 1, "L");
	$pdf->SetFont('Arial', '', 7);
	if($pdf->tipo_gestore != "CONCESSIONARIO")
			$pdf->Cell (83.0, 0, $provinciaIntestazione, 0, 1, "L");		
					
			$pdf->Cell (83.0, 0, $sede_gestore, 0, 1, "L");			
			$pdf->Cell (83.0, 0, $partitaIvaComuneIntestazione, 0, 1, "L");
			$pdf->Cell (83.0, 0, $telefonoComuneIntestazione, 0, 1, "L");
			$pdf->Cell (83.0, 0, $emailComuneIntestazione, 0, 1, "L");
	$pdf->SetFont('Arial', 'B', 7);
			$pdf->Cell (83.0, 0, "Servizio: Riscossione coattiva", 0, 1, "L");			
			$pdf->Cell (83.0, 0, $gestione_comune, 0, 1, "L");
			
	//UFFICIO
	if($ufficio_id != 0)
	{
			
	$pdf->SetMargins(123.0, 10.0,7.0);	$pdf->ln(0);
	$pdf->SetXY( 123 , 10 );
	
	$pdf->SetFont('Arial', 'B', 7);
			$pdf->Cell (83.0, 0, $nomeUfficio, 0, 1, "L");
	$pdf->SetFont('Arial', '', 7);
			$pdf->Cell (83.0, 0, $sede_ufficio, 0, 1, "L");
			$pdf->Cell (83.0, 0, $telefonoUfficioIntestazione, 0, 1, "L");
			$pdf->Cell (83.0, 0, $emailUfficioIntestazione, 0, 1, "L");
	$pdf->SetFont('Arial', '', 7);
			$pdf->Cell (83.0, 0, "Orario: ".$orarioUfficio_1, 0, 1, "L");
			$pdf->Cell (83.0, 0, $orarioUfficio_2, 0, 1, "L");

	}
	
	$pdf->Line(7, 34, $width_page, 34);//Linea di chiusura
	
	
	$pdf->SetFont('Arial', '', 10);
	$pdf->SetXY( 7 , 54 );
			$pdf->Cell ( 30 , 5, "Codice utente:", 0, 0, "L");
			$pdf->Cell ( 65 , 5, $utente_id." / ".$c, 0, 0, "L");
			$pdf->Cell ( 18 , 5, "Spett.le", 0, 0, "R");
	
	//DESTINATARIO 1
	$pdf->SetMargins(123.0, 54.0);	$pdf->Ln(0);
			$pdf->Cell (90, 5, $nome_utente, 0, 1, "L");//Nome Destinatario
	
	$pdf->SetMargins(7.0, 10.0,7.0);	$pdf->Ln(0);
			$pdf->Cell (30, 5, "Partita numero:" ,0, 0, "L");
			$pdf->Cell (65 , 5, $ID_partita." / ".$anno_rif, 0, 0, "L");
	
	//DESTINATARIO 2
	$pdf->SetMargins(123.0, 54.0);	$pdf->ln(0);

			$pdf->Cell (90, 5, $residenzaDestinatario_1 , 0, 1, "L");//Indirizzo 1 Destinatario
	if($residenzaDestinatario_2!="")
			$pdf->Cell (90, 5, $residenzaDestinatario_2 , 0, 1, "L");//Indirizzo 2 Destinatario
	
			$pdf->Cell (90, 5, $comuneDestinatario , 0, 1, "L");//Cap Comune Destinatario
			$pdf->Cell (90, 5, $statoDestinatario , 0, 1, "L");//Stato Destinatario
			$pdf->Ln(15);
	
//************	CHIUSURA INTESTAZIONE	************//


//////////////	CORPO Pagina 1	//////////////							
												
	//OGGETTO
	$pdf->SetMargins(7.0, 10.0, 7.0);	$pdf->Ln(0);
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->Cell(40, 0, "OGGETTO:" , 0, 0, 'L', 0, '', 0);
			$pdf->Cell(150, 0, $oggettoIngiunzione , 0, 1, 'L', 0, '', 0);
	$pdf->SetFont('Arial', 'B', 9);
			$pdf->Cell(40, 0, "" , 0, 0, 'R', 0, '', 0);
			$pdf->MultiCell(150, 0, "Relativo a ".$info_cart , 0, 'L', 0, 1);
			
	$pdf->SetFont('Arial', '', 10);
			$pdf->Ln(10);
			$pdf->MultiCell(0, 0, $primoTesto."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $secondoTesto."\n" , 0, 'J', 0, 1);
			$pdf->Ln(10);
			$pdf->MultiCell(0, 0, $titolo_importi."\n" , 0, 'J', 0, 1);
						
	//IMPORTI INGIUNZIONE
	$array_width = array(155,15,15);
	$array_align = array("R","R","R");
						
	$pdf->SetFont('Arial', '', 10);
			$pdf->Ln(5);
			$array_value = array("per Sanzione Amministrativa Originaria" , "Euro" , $sanz_originale);
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			
			$pdf->Ln(2);
			$array_value = array("per Spese notifica/ricerca dei precedenti atti di accertamento", "Euro" ,$spese_prec);
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);

			if($interessi_magg == "si")
			{
				$pdf->Ln(2);
				$array_value = array("per magg. del 10% semest.(parz./omesso/tardivo pag.to art. 206 C.d.S. - art. 27 L. 689/1981)", "Euro" ,$maggiorazione);
				crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
			}
						
			$pdf->Ln(2);
			$array_value = array("per Spese postali del presente preavviso di ingiunzione di pagamento", "Euro" , $spese_postali );
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
						
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->Ln(2);
			if($interessi_magg == "si")
				$array_value = array("TOTALE", "Euro" , $totale_dovuto );
			else
				$array_value = array("TOTALE", "Euro" , $totale_dovuto_no_magg );
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
						
	$pdf->SetFont('Arial', '', 10);
			$pdf->Ln(2);
			$array_value = array("Pagamenti effettuati", "Euro" , $tot_pagamenti );
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
						
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->Ln(2);
			$array_value = array("TOTALE COMPLESSIVO", "Euro" , $tot_compl );
			crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
						
			$pdf->Ln(10);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $finalePagina1."\n", 0, 'J', 0, 1);
			$pdf->Ln(5);
			$pdf->MultiCell(0, 0, $finalePagina2."\n", 0, 'J', 0, 1);
			$pdf->Ln(2);
	$pdf->SetFont('Arial', 'B', 9);
			if( $fax != "" && $fax != null )
				$pdf->MultiCell(0, 0, "Fax - ".$fax, 0, 'L', 0, 1);
			
			if( $mail != "" && $mail != null )
				$pdf->MultiCell(0, 0, "Email - ".$mail, 0, 'L', 0, 1);
			
			if( $PEC != "" && $PEC != null )
				$pdf->MultiCell(0, 0, "PEC - ".$PEC, 0, 'L', 0, 1);
			
			$pdf->Ln(2);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $saluti."\n", 0, 'J', 0, 1);
			$pdf->Ln(5);
						
	//RESPONSABILI
			$pdf->Cell(95 ,0, $secondoResp , 0, 0,'L',0,'',0 );
			$pdf->Cell(0 , 0, $primoResp , 0, 1, 'R', 0, '', 0);
			$pdf->Cell(95 ,0, $secondaFirma , 0, 0,'L',0,'',0 );
			$pdf->Cell(0 , 0, $primaFirma , 0, 1, 'R', 0, '', 0);
			$pdf->Ln(15);
			$pdf->MultiCell(0, 0, $firma_autografa."\n", 0, 'J', 0, 1);
			

//////////////	FINE CORPO Pagina 1	//////////////			

	//PIE DI PAGINA 1
	$pdf->SetY(-15);
	$pdf->SetFont('helvetica', 'N', 7);
	$pdf->Cell(0, 5, "Pag. 1/2 - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
						
	/**
 	* 		//////////////	PAGINA 2	//////////////
	*/
						
	$pdf->SetMargins(7,10,7);
	$pdf->AddPage();


//////////////	CORPO Pagina 2	//////////////

	//IMPORTANTE
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->Cell(0, 0, $importante , 0, 1, 'C', 0, '', 0);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $importante_testo."\n", 0, 'J', 0, 1);
			$pdf->Ln(4);

if($settore=="CDS")
{

	//CASO CDS
	
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $cds_titolo , 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $cds_testo_1."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $cds_testo_2."\n", 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $cds_testo_3."\n", 0, 'J', 0, 1);
			$pdf->ln(2);

}
else 
{
	
	//CASO TRIBUTO
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $tributo_titolo, 0, 'C', 0, 1);
			$pdf->Ln(1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $tributo_testo."\n", 0, 'J', 0, 1);
			$pdf->ln(2);
	
}
				
	//PAGAMENTI MANCATI
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $pag_mancato_1_titolo , 0, 'C', 0, 1);
			$pdf->ln(1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $pag_mancato_1_testo."\n" , 0, 'J', 0, 1);
			$pdf->ln(2);
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $pag_mancato_2_titolo , 0, 'C', 0, 1);
			$pdf->ln(1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $pag_mancato_2_testo."\n" , 0, 'J', 0, 1);
			$pdf->ln(4);
			
	//PROVVEDIMENTI
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $provvedimenti , 0, 'L', 0, 1);
			$pdf->ln(3);
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $esito_1_titolo , 0, 'L', 0, 1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $esito_1_testo."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $caso_A."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $caso_B."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $caso_C."\n" , 0, 'J', 0, 1);
			$pdf->MultiCell(0, 0, $caso_D."\n" , 0, 'J', 0, 1);
			$pdf->ln(2);
			
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $esito_2_titolo , 0, 'L', 0, 1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $esito_2_testo."\n" , 0, 'J', 0, 1);
			$pdf->ln(2);
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $esito_3_titolo , 0, 'L', 0, 1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $esito_3_testo."\n" , 0, 'J', 0, 1);
			$pdf->ln(2);
	$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell(0, 0, $esito_4_titolo , 0, 'L', 0, 1);
	$pdf->SetFont('Arial', '', 10);
			$pdf->MultiCell(0, 0, $esito_4_testo."\n" , 0, 'J', 0, 1);
			
						
	

//////////////	FINE CORPO Pagina 2	//////////////

	//PIE DI PAGINA 2
	$pdf->SetY(-15);
	$pdf->SetFont('helvetica', 'N', 7);
	$pdf->Cell(0, 5, "Pag. 2/2 - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
						
/**
 //////////////////////////////////////////////////////////////////////////////
*/
						
						if($stampa_select=="DEFINITIVA")
						{
							$salva = new atto($ing->ID, $c);
							
							if($salva->Stato_Stampa == "Da stampare")
							{
								$ing->EmettiPreavvisoIng();
							}								
						}
						
						
						}  // fine PDF
						
							$cont_result++;
							
							break;		//Una partita può avere un solo intestatario per cui una volta trovato si può uscire dal ciclo degli utenti		
									
					}//CHIUSURA IF PARTITA/UTENTE
	
				}//CHIUSURA FOR UTENTI
			
				break;		//Un atto può corrispondere ad una sola partita per cui una volta trovato si può uscire dal ciclo delle partite
				
			}//CHIUSURA IF ATTO/PARTITA
				
		}//CHIUSURA PARTITE
			
	}//CHIUSURA ATTII
	
	if ($stampa_select == "FLUSSO")
	{
		if (isset($testoRar))
		{
			fclose ($testoRar);
	
			crea_file_rar( $file_flusso , $flusso_dir );
	
			if($cont_result == 0)
			{
				unlink($file_flusso_txt);
				unlink($file_flusso_rar);
				echo "<script>nessun_risultato();</script>";
			}
			else
			{
				echo "<script>fine('Elaborazione completata');</script>";
			}
	
		}
	}
	else
	{
	
		$pdf->Output( $file_stampa , 'F');
		
		if($cont_result == 0)
		{
			unlink($file_stampa);
			echo "<script>nessun_risultato();</script>";
		}
		else	echo "<script>fine('Elaborazione completata');</script>";
	
	
	}

?>

</body>
</html>