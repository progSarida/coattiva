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

class MYPDF extends TCPDF {
	
	public function Header() {
		
		$this->SetFont('Arial', 'B', 11);
		$this->ln(5);
		$this->Cell(0, 5, "Elenco Preavvisi Ingiunzione" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage() + 1) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}

$a = get_var('a');
$c = get_var('c');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$forma = new forma_giuridica();
$array_forma = $forma->array_completo();

//PREPARAZIONE ELENCO
$elenco_dir = crea_dir( ATTI ."/". $c . "/Preavvisi_Ingiunzioni/Elenchi" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_preav_ing_".$data_file.".pdf";
$download = $file_elenco;

$vedi_file = mostra_file_path($download);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Stampa Preavvisi Ingiunzione</title>

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
		<font class="titolo font18 text_center">Elenco Ingiunzioni</font>
		
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
	$where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."'";
	
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


/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/
	
	$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf->setPrintHeader(false);
	$pdf->SetMargins(10, 10, 10);
	
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');
	
	$pdf->AddPage('L');
	$pdf->SetFont('Arial', 'B', 10);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();
	$altezza_pag = $pdf->getPageHeight();	
	
	
	$pdf->SetAutoPageBreak(false);
	$pdf->Ln(5);
	
	$array_width = array();
	$array_intestaz_1 = array();
	$array_intestaz_2 = array();
	
	$array_width[] = 25;						$array_intestaz_1[] = "Cronologico";				$array_intestaz_2[] = "Riferimento";
	$array_width[] = 50;						$array_intestaz_1[] = "Utente";						$array_intestaz_2[] = "CF / PI";
	$array_width[] = 120;						$array_intestaz_1[] = "Indirizzo";					$array_intestaz_2[] = "Informazioni Cartella";
	$array_width[] = $larghezza_pag - 225 - 20;	$array_intestaz_1[] = "Data Elaborazione - Calcolo";$array_intestaz_2[] = "Data Stampa - Notifica";
	$array_width[] = 30;						$array_intestaz_1[] = "Dovuto";						$array_intestaz_2[] = "Pagato";
	
	$pdf->setCellPaddings(2,1,2,0);
	$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
	
	$pdf->setCellPaddings(2,0,2,1);
	$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);
	
/**
	//////////////////////////////////////////////////////////////////////////////
*/
		
	$cont_result = 0;
	
	$parz_dovuto = 0.00;
	$parz_pagato = 0.00;
	
	$tot_gen_dovuto = 0.00;
	$tot_gen_pagato = 0.00;
	
	$ctrl_linea = "no";
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
						
						$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
						
						$ing = new atto( $array_atti[$l]['ID'], $c );
						
						$ID_ing = $ing->Comune_ID;
						$ID_partita = $partita->Comune_ID;
						$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
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
													
						$info_cart = $ing->Info_Cartella;
						$totale_dovuto = $ing->Totale_Dovuto;
						$pagamenti = $ing->Pagamento;
						$tot_pagamenti = 0.00;
						
						if( $pagamenti != null )
						{
							for($x=0;$x<count($pagamenti);$x++)
							{
								$tot_pagamenti += $pagamenti[$x]->Importo;
							}
						}
						
						$parz_dovuto += $totale_dovuto;
						$parz_pagato += $tot_pagamenti;
						
						$stati_atto = $ing->Stato_Esecuzione." / ".$ing->Stato_Stampa;
						if($ing->Stato != "")
							$stati_atto.=" / ".$ing->Stato;
						
						$pdf->SetFont('Arial', '', 8);

						$array_value_1 = array();
						$array_value_2 = array();
						
						$array_value_1[] = $ing->ID_Cronologico."/".$ing->Anno_Cronologico;	
						$array_value_1[] = $nome_utente;					
						$array_value_1[] = $indirizzo_utente['Completo'];						
						$array_value_1[] = from_mysql_date($ing->Data_Elaborazione)." - ".from_mysql_date($ing->Data_Calcolo_Interessi);
						$array_value_1[] = conv_num(number_format($totale_dovuto,2))." Euro";
						
						$array_value_2[] = $ID_ing."-".$ID_partita."/".$partita->Anno_Riferimento;
						$array_value_2[] = strtoupper($CF_PI);
						$array_value_2[] = $info_cart;
						$array_value_2[] = ($ing->Data_Stampa != null ? from_mysql_date($ing->Data_Stampa) : 'Assente')." - ".($ing->Data_Notifica != null ? from_mysql_date($ing->Data_Notifica) : 'Assente');
						$array_value_2[] = conv_num(number_format($tot_pagamenti,2))." Euro";
												
						$array_align = array("L","L","L","L","R");
						
						$pdf->setCellPaddings(2,2,2,0);
						$y = crea_riga($pdf , $array_width, $array_value_1 , $ctrl_linea , $styleDash , $array_align );
						
						if($ctrl_linea == "no")	$ctrl_linea = "up";
						
						$pdf->setCellPaddings(2,0,2,2);
						
						if( $y > $altezza_pag - 40)
						{
							$y2_vert = $pdf->getY();
							
							crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
							
							$tot_gen_dovuto += $parz_dovuto;
							$tot_gen_pagato += $parz_pagato;
							
							$array_width_fine = array();
							$array_fine_1 = array();
							$array_fine_2 = array();
							
							$array_width_fine[] = 195;						
							$array_width_fine[] = $larghezza_pag - 225 - 20;
							$array_width_fine[] = 30;						
							
							$array_fine_1[] = "PARZIALI DI PAGINA";			
							$array_fine_2[] = "";
							$array_fine_1[] = "Totali di pagina dovuti";	
							$array_fine_2[] = "Totali di pagina pagati";
							$array_fine_1[] = conv_num(number_format($parz_dovuto,2))." Euro";
							$array_fine_2[] = conv_num(number_format($parz_pagato,2))." Euro";
							
							$parz_dovuto = 0.00;
							$parz_pagato = 0.00;
							
							$array_align_fine = array("L","L","R");
								
							$pdf->SetFont('Arial', 'B', 8);
							
							$pdf->setCellPaddings(2,1,2,0);
							$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
							$pdf->setCellPaddings(2,0,2,1);
							$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
							
							
							
							$pdf->AddPage();
							$pdf->Ln(5);
							
							$pdf->SetFont('Arial', 'B', 10);
							
							$pdf->setCellPaddings(2,1,2,0);
							$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
							
							$pdf->setCellPaddings(2,0,2,1);
							$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);			

							$ctrl_linea = "no";
							
						}
						else 
							$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );
						
						$cont_result++;
						
						break;		//Una partita pu� avere un solo intestatario per cui una volta trovato si pu� uscire dal ciclo degli utenti		
									
					}//CHIUSURA IF PARTITA/UTENTE
	
				}//CHIUSURA FOR UTENTI
			
				break;		//Un atto pu� corrispondere ad una sola partita per cui una volta trovato si pu� uscire dal ciclo delle partite
				
			}//CHIUSURA IF ATTO/PARTITA
				
		}//CHIUSURA PARTITE
			
	}//CHIUSURA ATTII
	
	
	$y2_vert = $pdf->getY();
	
	crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
	
	$tot_gen_dovuto += $parz_dovuto;
	$tot_gen_pagato += $parz_pagato;
	
	$array_width_fine = array();
	$array_fine_1 = array();
	$array_fine_2 = array();
	
	$array_width_fine[] = 195;
	$array_width_fine[] = $larghezza_pag - 225 - 20;
	$array_width_fine[] = 30;
	
	$array_fine_1[] = "PARZIALI DI PAGINA";
	$array_fine_2[] = "";
	$array_fine_1[] = "Totali di pagina dovuti";
	$array_fine_2[] = "Totali di pagina pagati";
	$array_fine_1[] = conv_num(number_format($parz_dovuto,2))." Euro";
	$array_fine_2[] = conv_num(number_format($parz_pagato,2))." Euro";
	
	$parz_dovuto = 0.00;
	$parz_pagato = 0.00;
	
	$array_align_fine = array("L","L","R");
		
	$pdf->SetFont('Arial', 'B', 9);
	
	$pdf->setCellPaddings(2,2,2,0);
	$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
	$pdf->setCellPaddings(2,0,2,2);
	$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
	
	$pdf->setPrintHeader(false);
	$pdf->addPage();	
	$pdf->setPrintFooter(false);
	
	if($daco != "")
		$sel_utente = "Da ".$daco." ".$dano." a ".$acog." ".$anom;
	else 
		$sel_utente = "Tutti";
	if($da_partita != "")
		$sel_partita = "Dalla partita contabile numero ".$da_partita." alla partita contabile numero ".$a_partita;
	else 
		$sel_partita = "Tutte";
	if($da_elab != "")
		$sel_elab = "Dal ".$da_elab." al ".$a_elab;
	else
		$sel_elab = "Tutte";
	if($da_notif != "")
		$sel_notif = "Dal ".$da_notif." al ".$a_notif;
	else
		$sel_notif = "Tutte";
	if($da_stampa != "")
		$sel_stampa = "Dal ".$da_stampa." al ".$a_stampa;
	else
		$sel_stampa = "Tutte";
	
	if ($stato_esec != "" )	
		$sel_stato_esec = $stato_esec;
	else
		$sel_stato_esec = "Tutti";
	if ($stato_stampa != "" )
		$sel_stato_stampa = $stato_stampa;
	else
		$sel_stato_stampa = "Tutti";
	if ($stato_notif != "" )
		$sel_stato_notif = $stato_notif;
	else
		$sel_stato_notif = "Tutti";

	
	$pdf->setCellPaddings(2,0,2,1);
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 18);
	$pdf->Cell(0, 0, "COMUNE DI ".strtoupper($nome_com) , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->SetFont('Arial', '', 16);
	$pdf->Cell(0, 0, "ELENCO INGIUNZIONI" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->ln(20);
	$pdf->SetFont('Arial', 'B', 14);
	$pdf->Cell(0, 0, "SELEZIONI" , 0, 1, 'L');

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "UTENTE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_utente , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "PARTITA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_partita , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI ELABORAZIONE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_elab , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI STAMPA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_stampa , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI NOTIFICA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_notif , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "STATO DI ESECUZIONE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_stato_esec , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "STATO DI STAMPA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_stato_stampa , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "STATO DI NOTIFICA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sel_stato_notif , 0, 1, "L");

	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 14);
	$pdf->Cell(0, 0, "RIEPILOGO" , 0, 1, 'L');
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "NUMERO PAGINE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 23.5 , 5, $pdf->PageNo() , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "NUMERO ATTI:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 23.5 , 5, $cont_result , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "TOTALE DOVUTO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_dovuto,2))." Euro" , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "TOTALE PAGATO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_pagato,2))." Euro" , 0, 1, "R");
	
	$pdf->movePage($pdf->PageNo(), 1);
	
	$pdf->Output( $file_elenco , 'F');
	
	
	if($cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>

</body>
</html>