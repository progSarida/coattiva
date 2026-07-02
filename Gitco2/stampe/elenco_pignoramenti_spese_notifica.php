<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";

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

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$dati_ente = $comune->Info;
$indirizzo_ente = $dati_ente->righe_indirizzo();

class MYPDF extends TCPDF {
	
	public function Footer() {

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage()) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}



$forma = new forma_giuridica();
$array_forma = $forma->array_completo();

//PREPARAZIONE ELENCO
$elenco_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Elenchi" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_pignoramenti_".$data_file.".pdf";
$download = $file_elenco;

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
	//$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Elenco' onclick='mostra_file();'>");
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
		<font class="titolo font18 text_center">Elenco Pignoramenti</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div id=vedi_file></div>
		
		</td>
	</tr>
</table>

<?php

//COGNOME NOME
$daco  = strtoupper(get_var('daco'));
$acog  = strtoupper(get_var('acog'));
$dano  = strtoupper(get_var('dano'));
$anom  = strtoupper(get_var('anom'));

//PARTITA
$da_partita  = get_var('da_n_elenco');
$a_partita  = get_var('a_n_elenco');

//ANNI RIFERIMENTO
$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

//TIPO_PIGNORAMENTO
$tipo_pignoramento = get_var('tipo_pignoramento');
$tipo_terzo = get_var('presso_terzi');

//DATA ELABORAZIONE
$data_elab = get_var('data_elab');
$da_elab = from_mysql_date(get_var('da_elab'));
$a_elab = from_mysql_date(get_var('a_elab'));

//DATA NOTIFICA
$data_notif = get_var('data_notif');
$da_notif = from_mysql_date(get_var('da_notif'));
$a_notif = from_mysql_date(get_var('a_notif'));

//DATA SPEDIZIONE
$data_spedizione = get_var('data_spedizione');
$da_sped = from_mysql_date(get_var('da_sped'));
$a_sped = from_mysql_date(get_var('a_sped'));

//DATA CONSEGNA
$data_consegna = get_var('data_consegna');
$da_cons = from_mysql_date(get_var('da_cons'));
$a_cons = from_mysql_date(get_var('a_cons'));

//UFFICIALE
$tipo_ufficiale = get_var('tipo_ufficiale');

// //STATI NOTIFICA ( modalita' - giacenza[ind validato] - anomalie )
// $modalita_notif = get_var('modalita');
// $stato_giacenza = get_var('giacenza');
// $indirizzo_validato = get_var('indirizzo_validato');
// $anomalie = get_var('anomalie');

//DATA STAMPA
$data_stampa = get_var('data_stampa');
$da_stampa = from_mysql_date(get_var('da_stampa'));
$a_stampa = from_mysql_date(get_var('a_stampa'));

//STATO STAMPA
$stato_stampa = get_var('stato_stampa');

//BLOCCO COAZIONE
$blocco = get_var('blocco');

//TRIBUNALE
$filtro_tribunale = get_var('tribunale');

//SALTA PAGINA
$filtro_salta = get_var('salta');

//ORDINAMENTO
$ordinamento = get_var('ordinamento');

//ANOMALIA
$select_anomalia = get_var('anomalia');


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
	$where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."'";
	
$query_partita = da_a_partita( $c , $da_partita , $a_partita , $where_anno );
$array_partite = mysql_array( $query_partita );


/** 	SELEZIONE PIGNORAMENTI	*/
$campi_stati = array("PIG_GEN.Stato_Stampa", "PIG_GEN.Tipo_Ufficiale");
$valori_stati = array ($stato_stampa, $tipo_ufficiale);

$query_stati = where_campi($campi_stati, $valori_stati);

$campi_array = array();
$array_da_data = array();
$array_a_data = array();
$date_vuote = array();
if($data_elab!="assente")
{
	$campi_array[] = "Data_Elaborazione";
	$array_da_data[] = to_mysql_date($da_elab);
	$array_a_data[] = to_mysql_date($a_elab);
}
else
	$date_vuote[] = "PIG_GEN.Data_Elaborazione";

if($data_notif!="assente")
{
	$campi_array[] = "Data_Notifica";
	$array_da_data[] = to_mysql_date($da_notif);
	$array_a_data[] = to_mysql_date($a_notif);
}
else
	$date_vuote[] = "PIG_GEN.Data_Notifica";

if($data_consegna!="assente")
{
	$campi_array[] = "Data_Consegna";
	$array_da_data[] = to_mysql_date($da_cons);
	$array_a_data[] = to_mysql_date($a_cons);
}
else
	$date_vuote[] = "PIG_GEN.Data_Consegna";

if($data_spedizione!="assente")
{
	$campi_array[] = "Data_Spedizione";
	$array_da_data[] = to_mysql_date($da_sped);
	$array_a_data[] = to_mysql_date($a_sped);
}
else
	$date_vuote[] = "PIG_GEN.Data_Spedizione";

if($data_stampa!="assente")
{
	$campi_array[] = "Data_Stampa";
	$array_da_data[] = to_mysql_date($da_stampa);
	$array_a_data[] = to_mysql_date($a_stampa);
}
else
	$date_vuote[] = "PIG_GEN.Data_Stampa";

$query_date_vuote = where_date_vuote($date_vuote);

$where_pigno = array();
$where_pigno[0] = selezione_date_query( "PIG_GEN", $campi_array , $array_da_data , $array_a_data );
$where_pigno[1] = $query_stati;

$pignoramento = new pignoramento(null, $c);
$query_pignoramenti = $pignoramento->query_selezione_pignoramenti($c, $tipo_pignoramento, $tipo_terzo, $ordinamento, $where_pigno);

$array_pignoramenti = mysql_array($query_pignoramenti);

$num_pignoramenti = count($array_pignoramenti);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/
$styleDash = array('dash' => '6,6');
$styleRetta = array('dash' => '0');

	$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	$pdf->setPrintHeader(false);
	$pdf->SetAutoPageBreak(false);

	$pdf->SetMargins(10, 10, 10);
	
	$pdf->AddPage('P');
	$pdf->SetFont('Arial', 'B', 11);
	$pdf->MultiCell(0, 0, strtoupper("COMUNE DI ".$nome_comune." ".$indirizzo_ente['Completo']),0,"C");
	
	$pdf->ln(2);
	
	$pdf->SetFont('Arial', '', 10);
	
	$pdf->MultiCell(0, 0, "SPESE ANTICIPATE IN NOME E PER CONTO DEL COMUNE DI ".strtoupper($nome_comune)." DAL GESTORE SARIDA S.R.L.",0,"C");
	$pdf->MultiCell(0, 0, "VIA MONSIGNOR VATTUONE 9/6 - 16039 SESTRI LEVANTE (GE) - P.I. 01338160995",0,"C");
	
	$pdf->ln(5);
	
	$pdf->SetFont('Arial', 'B', 9);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();
	$altezza_pag = $pdf->getPageHeight();	
	
	$array_width = array();
	$array_intestaz_1 = array();
	
	$array_width = array( 20 , 45 , 95 , 30 );
	
	$array_intestaz_1[] = "Crono";				
	$array_intestaz_1[] = "Utente";						
	$array_intestaz_1[] = "Indirizzo";					
	$array_intestaz_1[] = "Spese di notifica";	
	
	$array_align_1 = array("L","L","L","L");
	$array_align_2 = array("L","L","R","C");
	
	$pdf->setCellPaddings(2,1,2,1);
	$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up_down" , $styleRetta, $array_align_1);

	
/**
	//////////////////////////////////////////////////////////////////////////////
*/
	$tribunale_pagina = "";
	$tribunale_pagina_comune = "";
	$cont_result = 0;
	
	$ctrl_linea = "no";
	for( $l=0; $l < $num_pignoramenti; $l++ )//FOR ATTI
	{	
		set_time_limit(100);
		echo "<script>update(".ceil($l*100/$num_pignoramenti).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
		{				
			if( $array_pignoramenti[$l]['Partita_ID'] == $array_partite[$k]['ID'] )//IF ATTO/PARTITA
			{		
				
				if($blocco=="Si")
				{
					if($array_partite[$k]['Flag_Blocco_Coazione']!="si")
						break;
				}
				
				if($blocco=="No")
				{
					if($array_partite[$k]['Flag_Blocco_Coazione']=="si")
						break;
				}
				
				for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
				{
					if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF PARTITA/UTENTE
					{
						set_time_limit(30);
						
						$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
						
						$pignoramento = new pignoramento( $array_pignoramenti[$l]['ID'], $c );
						
						$ID_Com_Pignoramento = $pignoramento->Comune_ID;
						$ID_Com_Partita = $partita->Comune_ID;
						$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
						$tribunale = new ufficio_giudiziario($utente->Residenza->CC_Indirizzo, "tribunale");
						$ufficio_vendite = new ufficio_giudiziario($tribunale->CC_Ufficio, "istituto");
						
						//CONTROLLO TRIBUNALE
						if($filtro_tribunale!=null)
						{
							if($tribunale->CC_Ufficio != $filtro_tribunale)
								break;
						}
						
						$indirizzo_utente = $utente->righe_indirizzo();
						$forma_descr = "";
						if($utente->Forma_Giuridica!='')
						{
							$index_value = $utente->Forma_Giuridica;
							$forma_descr = $array_forma[$index_value]['Sigla'];
						}
							
						$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
						
						if( strlen($nome_utente) > 22 )
							$nome_utente = substr($nome_utente,0,21)."...";
						
						if( $utente->Genere=="D" )
							$CF_PI = $utente->Partita_Iva;
						else 
							$CF_PI = $utente->Codice_Fiscale;
													
						$info_cart = $partita->Tributo[0]->Info_Cartella;
						

						//CONTROLLI
						$anomalia = "";
						$control_anomalia = 0;
						
						$atto = new atto($pignoramento->Atto_ID, $c);
						if(from_mysql_date($atto->Data_Notifica)=="")
						{
							$anomalia.= $atto->Atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." in attesa di verifica!  ;  ";
							$control_anomalia = 1;
						}
						
						if($pignoramento->Tipo=="veicolo")
						{						
							if($ufficio_vendite->Denominazione=="")
							{
								$anomalia.= "Parametri Tribunale / Istituto vendite giudiziarie ASSENTI!  ;  ";
								$control_anomalia = 1;
							}
							else if($ufficio_vendite->PEC=="")
							{
								if($pignoramento->Notifica_Istituto[0]->Modalita_Stampa=="pec")
								{
									$anomalia = "PEC Istituto vendite giudiziarie ASSENTE!  ;  ";
									$control_anomalia = 1;
								}
								else
									$anomalia = "PEC Istituto vendite giudiziarie ASSENTE ( Modalita' di Invio diversa da PEC )  ;  ";
							}
						
							if(!isset($pignoramento->Veicolo[0]))
							{
								$anomalia.= "Nessun VEICOLO presente nel pignoramento!  ;  ";
								$control_anomalia = 1;
							}
							else
							{
								for($y=0;$y<count($pignoramento->Veicolo);$y++)
								{
									if($pignoramento->Veicolo[$y]->Valore_Veicolo==0)
									{
							
										$anomalia.= strtoupper($pignoramento->Veicolo[$y]->Tipo_Veicolo)." ".$pignoramento->Veicolo[$y]->Marca_Veicolo." ";
										$anomalia.= $pignoramento->Veicolo[$y]->Modello_Veicolo." ";
										$anomalia.= "sprovvisto dell'indicazione del valore!  ;  ";
										$control_anomalia = 1;
										
									}
									
									if(from_mysql_date($pignoramento->Veicolo[$y]->Data_Visura)==null)
									{
										$anomalia.= strtoupper($pignoramento->Veicolo[$y]->Tipo_Veicolo)." ".$pignoramento->Veicolo[$y]->Marca_Veicolo." ";
										$anomalia.= $pignoramento->Veicolo[$y]->Modello_Veicolo." ";
										$anomalia.= "sprovvisto della Data della Visura!  ;  ";
										$control_anomalia = 1;
									}
								}
							}

							if($control_anomalia==0 && $select_anomalia=="si")
								break;
						}
						
						if($select_anomalia=="no" && $control_anomalia==1)
						{
							break;
						}		
						
						$array_value_1 = array();
						$array_value_2 = array();
						
						
						$array_value_1[] = $pignoramento->ID_Cronologico."/".$pignoramento->Anno_Cronologico;
						$array_value_1[] = $nome_utente;
						$array_value_1[] = $indirizzo_utente['Completo'];
						$array_value_1[] = "";
						
						$array_value_2[] = "";
						$array_value_2[] = "";
						$array_value_2[] = "TOTALE EURO";
						$array_value_2[] = "______________";
						

						if($tipo_pignoramento == "veicolo")
							$tipo_pigno_visual = "BENI MOBILI REGISTRATI";
						else
							$tipo_pigno_visual = strtoupper($tipo_pignoramento);
						
						if($filtro_salta=="tribunale")
						{
							if($tribunale->CC_Ufficio != $tribunale_pagina && $tribunale_pagina != "")
							{
								$tot = array_sum( $array_width );
								$margine = $pdf->getMargins();
								$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY(), $styleRetta ) ;
								$y2_vert = $pdf->getY();
								crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
								
								//FINE PAGINA
								$pdf->SetFont('Arial', 'B', 9);
								$pdf->setCellPaddings(2,3,2,3);
								$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash , $array_align_2 );
								
								$pdf->setCellPaddings(2,0,2,0);
								$pdf->SetFont('Arial', '', 9);
								$pdf->MultiCell(30, 0, "TIPO ATTO:",0,"L",false,0);
								$pdf->MultiCell(0, 0, "PIGNORAMENTO ".$tipo_pigno_visual,0,"L");
								$pdf->MultiCell(30, 0, "ENTRATA:",0,"L",false,0);
								$pdf->MultiCell(0, 0, $partita->Tipo, 0,"L");
								$pdf->MultiCell(30, 0, "ENTE:",0,"L",false,0);
								$pdf->MultiCell(0, 0, "COMUNE DI ".strtoupper($nome_comune),0,"L");
								$pdf->MultiCell(30, 0, "ANNO:",0,"L",false,0);
								$pdf->MultiCell(0, 0, "DAL ".$da_anno." AL ".$ad_anno,0,"L");
								$pdf->MultiCell(30, 0, "TRIBUNALE DI:",0,"L",false,0);
								$pdf->MultiCell(0, 0, strtoupper($tribunale_pagina_comune),0,"L");
								$pdf->ln(5);
								$pdf->setCellPaddings(2,0,2,2);
								$pdf->MultiCell(120, 0, "",0,"L",false,0);
								$pdf->MultiCell(0, 0, "IL FUNZIONARIO DEL TRIBUNALE",0,"C");
								$pdf->MultiCell(120, 0, "",0,"L",false,0);
								$pdf->MultiCell(0, 0, "___________________________",0,"C");
								
								$pdf->AddPage('P');
								$pdf->SetFont('Arial', 'B', 11);
								$pdf->MultiCell(0, 0, strtoupper("COMUNE DI ".$nome_comune." ".$indirizzo_ente['Completo']),0,"C");
								
								$pdf->ln(2);
								
								$pdf->SetFont('Arial', '', 10);
								$pdf->setCellPaddings(2,0,2,0);
								$pdf->MultiCell(0, 0, "SPESE ANTICIPATE IN NOME E PER CONTO DEL COMUNE DI ".strtoupper($nome_comune)." DAL GESTORE SARIDA S.R.L.",0,"C");
								$pdf->MultiCell(0, 0, "VIA MONSIGNOR VATTUONE 9/6 - 16039 SESTRI LEVANTE (GE) - P.I. 01338160995",0,"C");
								
								$pdf->ln(5);
								
								$pdf->SetFont('Arial', 'B', 9);
								$pdf->setCellPaddings(2,1,2,1);
								$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up_down" , $styleRetta, $array_align_1);

							}
								
							$tribunale_pagina = $tribunale->CC_Ufficio;
							$tribunale_pagina_comune = $tribunale->Comune;
						}
												
						$pdf->SetFont('Arial', '', 8);						

						$pdf->setCellPaddings(2,2,2,2);
						$y = crea_riga($pdf , $array_width, $array_value_1 , $ctrl_linea , $styleDash , $array_align_1 );
							
						if($ctrl_linea == "no")	$ctrl_linea = "up";
						
						if( $y > $altezza_pag - 80)
						{
							
							$tot = array_sum( $array_width );
							$margine = $pdf->getMargins();
							$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY(), $styleRetta ) ;
							$y2_vert = $pdf->getY();
							crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
							
							//FINE PAGINA
							$pdf->SetFont('Arial', 'B', 9);
							$pdf->setCellPaddings(2,3,2,3);
							$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash , $array_align_2 );
							
							$pdf->setCellPaddings(2,0,2,0);
							$pdf->SetFont('Arial', '', 9);
							$pdf->MultiCell(30, 0, "TIPO ATTO:",0,"L",false,0);
							$pdf->MultiCell(0, 0, "PIGNORAMENTO ".$tipo_pigno_visual ,0,"L");
							$pdf->MultiCell(30, 0, "ENTRATA:",0,"L",false,0);
							$pdf->MultiCell(0, 0, $partita->Tipo, 0,"L");
							$pdf->MultiCell(30, 0, "ENTE:",0,"L",false,0);
							$pdf->MultiCell(0, 0, "COMUNE DI ".strtoupper($nome_comune),0,"L");
							$pdf->MultiCell(30, 0, "ANNO:",0,"L",false,0);
							$pdf->MultiCell(0, 0, "DAL ".$da_anno." AL ".$ad_anno,0,"L");
					
							$pdf->MultiCell(30, 0, "TRIBUNALE DI:",0,"L",false,0);
							$pdf->MultiCell(0, 0, strtoupper($tribunale->Comune),0,"L");
							$pdf->ln(5);
							$pdf->setCellPaddings(2,0,2,2);
							$pdf->MultiCell(120, 0, "",0,"L",false,0);
							$pdf->MultiCell(0, 0, "IL FUNZIONARIO DEL TRIBUNALE",0,"C");
							$pdf->MultiCell(120, 0, "",0,"L",false,0);
							$pdf->MultiCell(0, 0, "___________________________",0,"C");
							
							$pdf->AddPage('P');
							$pdf->SetFont('Arial', 'B', 11);
							$pdf->MultiCell(0, 0, strtoupper("COMUNE DI ".$nome_comune." ".$indirizzo_ente['Completo']),0,"C");
							
							$pdf->ln(2);
							
							$pdf->SetFont('Arial', '', 10);
							$pdf->setCellPaddings(2,0,2,0);
							$pdf->MultiCell(0, 0, "SPESE ANTICIPATE IN NOME E PER CONTO DEL COMUNE DI ".strtoupper($nome_comune)." DAL GESTORE SARIDA S.R.L.",0,"C");
							$pdf->MultiCell(0, 0, "VIA MONSIGNOR VATTUONE 9/6 - 16039 SESTRI LEVANTE (GE) - P.I. 01338160995",0,"C");
							
							$pdf->ln(5);
							
							$pdf->SetFont('Arial', 'B', 9);
							$pdf->setCellPaddings(2,1,2,1);
							$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up_down" , $styleRetta, $array_align_1);
				
						}
						else
						{
							
						}
						
						
						
						$cont_result++;
						
						break;		//Una partita può avere un solo intestatario per cui una volta trovato si può uscire dal ciclo degli utenti		
									
					}//CHIUSURA IF PARTITA/UTENTE
	
				}//CHIUSURA FOR UTENTI
			
				break;		//Un atto può corrispondere ad una sola partita per cui una volta trovato si può uscire dal ciclo delle partite
				
			}//CHIUSURA IF ATTO/PARTITA
				
		}//CHIUSURA PARTITE
			
	}//CHIUSURA ATTII
	
	if($cont_result>0)
	{
		$tot = array_sum( $array_width );
		$margine = $pdf->getMargins();
		$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY(), $styleRetta ) ;
		$y2_vert = $pdf->getY();
		crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
		
		//FINE PAGINA
		$pdf->SetFont('Arial', 'B', 9);
		$pdf->setCellPaddings(2,3,2,3);
		$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash , $array_align_2 );
		
		$pdf->setCellPaddings(2,0,2,0);
		$pdf->SetFont('Arial', '', 9);
		$pdf->MultiCell(30, 0, "TIPO ATTO:",0,"L",false,0);
		$pdf->MultiCell(0, 0, "PIGNORAMENTO ".$tipo_pigno_visual ,0,"L");
		$pdf->MultiCell(30, 0, "ENTRATA:",0,"L",false,0);
		$pdf->MultiCell(0, 0, $partita->Tipo, 0,"L");
		$pdf->MultiCell(30, 0, "ENTE:",0,"L",false,0);
		$pdf->MultiCell(0, 0, "COMUNE DI ".strtoupper($nome_comune),0,"L");
		$pdf->MultiCell(30, 0, "ANNO:",0,"L",false,0);
		$pdf->MultiCell(0, 0, "DAL ".$da_anno." AL ".$ad_anno,0,"L");
		
		$pdf->MultiCell(30, 0, "TRIBUNALE DI:",0,"L",false,0);
		$pdf->MultiCell(0, 0, strtoupper($tribunale->Comune),0,"L");
		$pdf->ln(5);
		$pdf->setCellPaddings(2,0,2,2);
		$pdf->MultiCell(120, 0, "",0,"L",false,0);
		$pdf->MultiCell(0, 0, "IL FUNZIONARIO DEL TRIBUNALE",0,"C");
		$pdf->MultiCell(120, 0, "",0,"L",false,0);
		$pdf->MultiCell(0, 0, "___________________________",0,"C");	
			
	
		$pdf->Output( $file_elenco , 'F');
	
	}
	
	if($cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>

</body>
</html>