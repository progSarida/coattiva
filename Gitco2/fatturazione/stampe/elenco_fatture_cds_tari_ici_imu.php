<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";

include CLASSI . "/comuni.php";
include CLASSI . "/fatture.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

class MYPDF extends TCPDF
{
	public $tipo;
	
	public function AddMyPage ($tip, $formato = null)
	{
		$this->tipo = $tip;
		$this->AddPage ($formato);
	}
	
	public function Header()
	{
		
		$this->SetFont('Arial', 'B', 11);
		$this->ln(5);
		/*if ($this->tipo == 1) $this->Cell(0, 5, "Elenco Pagamenti Associati a Verbali" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
		if ($this->tipo == 2) $this->Cell(0, 5, "Elenco Pagamenti Da Associare" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
		if ($this->tipo == 3) $this->Cell(0, 5, "bla bla" , 0, false, 'C', 0, '', 0, false, 'T', 'M');*/
		$this->Cell(0, 5, "Elenco Fatture" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer()
	{

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage() + 1) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}

/*if ($_SESSION['CC_User'] == "***+")
{
	alertAllGlobalVariables();
	return;
}*/

$a = get_var('a');
$c = get_var('c');

$tipofile = get_var('tipofile');
$tipofile = "PDF";

$comune = new ente_gestito($c);
//$nome_com = $comune->Nome;
//$nome_comune = ($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];


//PREPARAZIONE ELENCO
$elenco_dir = crea_dir( $PathCompletoFatture . "/Elenchi/" );
$data_file = date('Y-m-d_H-i-s');

$prefissoNomeStampa = "elenco_fatture_";

delete_files_after_X_days($PathCompletoFatture . "/Elenchi/", $prefissoNomeStampa, 30);

$file_elenco = $elenco_dir . $prefissoNomeStampa . $data_file . ".pdf";
$download = $file_elenco;

$vedi_file = mostra_file_path($download);

$file_csv = $elenco_dir . $prefissoNomeStampa . $data_file . ".txt";

if ($tipofile == "CSV")
{
	$aCapo = Chr(13) . Chr(10);
	$myCsv = fopen ($file_csv, "w+");
	$download = $file_csv;
	$vedi_csv = mostra_file_path($download);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Elenco Fatture</title>

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

function finepdf(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);

	mostra_file();
	//$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Elenco' onclick='mostra_file();'>");
	//self.close();
}

function finecsv(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);
}

function mostra_file()
{
	window.name = "Stampa";
	if ("<?=$_SESSION['CC_User']?>" == "***+") window.open('<?php echo $vedi_file; ?>',"ALTRA");
	else window.open('<?php echo $vedi_file; ?>',"Stampa");
}


</script>

</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
<tr>
<td width=1%><br></td>
<td class="text_left"><font class="comune" >SARIDA</font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
	<tr>
		<td valign=top>
			<br><br><br>
			<font class="titolo font18 text_center">Elenco Fatture</font>
			
			<br><br>
			
			<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
			
			<br>
			
			<div id="vedi_file"></div>
		
			<br>
			
			<div class="titolo" id="vedi_csv">
				
			</div>
		</td>
	</tr>
</table>

<?php

$dacompetenza = get_var('dacompetenza');
$acompetenza = get_var('acompetenza');

$dabilancio = get_var('dabilancio');
$abilancio = get_var('abilancio');

$dafatturazione = get_var('dafatturazione');
$afatturazione = get_var('afatturazione');

$dariscossione = get_var('dariscossione');
$ariscossione = get_var('ariscossione');

$ordinestampa = get_var('ordinestampa');

$tipofattura = get_var('tipofattura');
$tipogestione = get_var('tipogestione');

$sceltacomuni = get_var('sceltacomuni');



$limiteTipoFattura = $limiteTipoGestione = $limiteCompetenza = $limiteBilancio = "";
$limiteDataFatturazione = $limiteDataRiscossione = $limiteDeiComuni = "";
$sceltaTipoFattura = $sceltaCompetenza = $sceltaBilancio = "Tutti";
$sceltaDataFatturazione = $sceltaDataRiscossione = "Tutte";
$sceltaDeiComuni = "Tutti";
$sceltaTipoGestione = "";

if ($tipofattura != NULL)
{
	$limiteTipoFattura = " Fat_Tributo = '$tipofattura' AND ";
	switch ($tipofattura)
	{
		case "CDS": $sceltaTipoFattura = "Solo 'CODICE DELLA STRADA'"; break;
		case "TARI": $sceltaTipoFattura = "Solo 'TARSU/TARI'"; break;
		case "ICI": $sceltaTipoFattura = "Solo 'ICI'"; break;
		case "IMU": $sceltaTipoFattura = "Solo 'IMU/TASI'"; break;
		default: $sceltaTipoFattura = "Solo '$tipofattura'"; break;
	}
}
if ($tipogestione != NULL)
{
	if ($tipogestione == "ALTRO")
	{
		$limiteTipoGestione = " (Tipo_Gestione = 'PAGATA_AD_AGGIO' OR ";
		$limiteTipoGestione .= " Tipo_Gestione = 'PAGATA_A_CANONE') AND ";
	}
	else
	{
		$limiteTipoGestione = " Tipo_Gestione = '$tipogestione' AND ";
	}
	switch ($tipogestione)
	{
		case "PAGATA_A_CANONE": $sceltaTipoGestione = " - CANONE"; break;
		case "PAGATA_AD_AGGIO": $sceltaTipoGestione = " - AGGIO"; break;
		case "SERVIZIO": $sceltaTipoGestione = " - SERVIZIO"; break;
		case "ALTRO": $sceltaTipoGestione = " - CANONE + AGGIO"; break;
		default: $sceltaTipoGestione = "Solo '$tipofattura'"; break;
	}
}

if ($dacompetenza != NULL)
{
	$limiteCompetenza = " Fat_Anno_Competenza >= $dacompetenza AND Fat_Anno_Competenza <= $acompetenza AND ";
	$sceltaCompetenza = "Da anno $dacompetenza a anno $acompetenza";
}

if ($dabilancio != NULL)
{
	$limiteBilancio = " Fat_Anno_Bilancio >= $dabilancio AND Fat_Anno_Bilancio <= $abilancio AND ";
	$sceltaBilancio = "Da anno $dabilancio a anno $abilancio";
}

if ($dafatturazione != NULL)
{
	$limiteDataFatturazione = " Fat_Data >= '" . to_mysql_date($dafatturazione) . "' AND ";
	$limiteDataFatturazione .= "Fat_Data <= '" . to_mysql_date($afatturazione) . "' AND ";
	$sceltaDataFatturazione = "Da " . $dafatturazione . " a " . $afatturazione;
}

if ($dariscossione != NULL)
{
	$limiteDataRiscossione = " Fat_Da_Data_Periodo >= '" . to_mysql_date($dariscossione) . "' AND ";
	$limiteDataRiscossione .= "Fat_A_Data_Periodo <= '" . to_mysql_date($ariscossione) . "' AND ";
	$sceltaDataRiscossione = "Da " . $dariscossione . " a " . $ariscossione;
}

$nomeComuneSarida = "SARIDA";
if ($sceltacomuni != NULL)
{
	$myTmpFat = new fatture_generali(null);
	$solocom = strtoupper($myTmpFat->NomeComuneDaCCFattura($sceltacomuni));
	
	$limiteDeiComuni = " Fat_Comune = '" . $sceltacomuni . "' AND ";
	$sceltaDeiComuni = "Solo " . $solocom;
	$nomeComuneSarida = "COMUNE DI " . $solocom;
}

flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();
//sleep(2);

$queryFatture = "SELECT fatture_generali.ID as ID_FAT FROM fatture_generali , fatture_dati_cig ";
$queryFatture .= "WHERE Fat_Dati_Cig = fatture_dati_cig.ID AND ";
$queryFatture .= $limiteTipoFattura;
$queryFatture .= $limiteTipoGestione;
$queryFatture .= $limiteCompetenza;
$queryFatture .= $limiteBilancio;
$queryFatture .= $limiteDataFatturazione;
$queryFatture .= $limiteDataRiscossione;
$queryFatture .= $limiteDeiComuni;
$queryFatture .= " 1 ";
$queryFatture .= "ORDER BY Fat_Numero ";
//$queryFlussi .= "LIMIT 5 ";


$resFatture = esegui_query($queryFatture);

$arrayFatture = array();
while ($rigaFatt = risultati_query($resFatture))
{
	$arrayFatture[] = $rigaFatt['ID_FAT'];
}

$num_Fatture = count($arrayFatture);


if ($_SESSION['CC_User'] == "***+")
	echo "<br>" . $queryFatture . " --> " . $num_Fatture;

//echoAllGlobalVariables();

//return;


$fontPrimo = 9;
$fontSecondo = 8;

/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/

	$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf->setPrintHeader(false);
	$pdf->SetMargins(10, 10, 10);
	
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');
	
	$pdf->AddMyPage(1, 'L');
	$pdf->SetFont('Arial', 'B', $fontPrimo);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();  //  297
	$altezza_pag = $pdf->getPageHeight();	
	
	$pdf->SetAutoPageBreak(false);
	$pdf->Ln(5);
	
	$array_width = array();
	$array_intestaz_1 = array();
	$array_intestaz_2 = array();
	$array_align = array("L","C","C","C","C","R","R","R","R","R","R","R");
	
	$array_width[] = 25;
	$array_width[] = 25;
	$array_width[] = 40;
	$array_width[] = 25;
	$array_width[] = 25;
	
	$array_width[] = 20;
	$array_width[] = 20;
	$array_width[] = 20;
	$array_width[] = 20;
	$array_width[] = 20;
	$array_width[] = 20;
	$array_width[] = 20;
	
	$array_intestaz_1[] = "Numero";					$array_intestaz_2[] = "Tipo";
	$array_intestaz_1[] = "Competenza";				$array_intestaz_2[] = "Bilancio";
	$array_intestaz_1[] = "Comune";					$array_intestaz_2[] = "";
	$array_intestaz_1[] = "Data Fatt.";				$array_intestaz_2[] = "Descr.";
	$array_intestaz_1[] = "Data Accr.";				$array_intestaz_2[] = "";
	
	$array_intestaz_1[] = "Importo";				$array_intestaz_2[] = "";
	$array_intestaz_1[] = "Spese";					$array_intestaz_2[] = "";
	$array_intestaz_1[] = "I.V.A.";					$array_intestaz_2[] = "";
	$array_intestaz_1[] = "Rimborsi";				$array_intestaz_2[] = "";
	$array_intestaz_1[] = "Imposta";				$array_intestaz_2[] = "";
	$array_intestaz_1[] = "Totale";					$array_intestaz_2[] = "";
	$array_intestaz_1[] = "Accredito";				$array_intestaz_2[] = "";
	
	if ($tipofile == "CSV")
	{
		for ($k = 0; $k < count($array_intestaz_1); $k++)
		{
			fwrite ($myCsv, $array_intestaz_1[$k], strlen($array_intestaz_1[$k]));
			fwrite ($myCsv, ";", 1);
			fwrite ($myCsv, $array_intestaz_2[$k], strlen($array_intestaz_2[$k]));
			fwrite ($myCsv, ";", 1);
		}
		fwrite ($myCsv, $aCapo, strlen($aCapo));
	}
	
	
	$cont_pag_result = 0;
	
	$parz_dovuto = 0.00;
	
	$tot_gen_dovuto = 0.00;
	
	$intestazione = 1;
	
	$arrayPerRiepilogo = array();
	
	$ctrl_linea = "no";
	$iArray = 0;
	$aFatturaAnnoCompetenza = array();
	for( $l=0; $l < $num_Fatture; $l++ )//FOR FATTURE
	{
		echo "<script>update(".ceil($l*100/$num_Fatture).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		if ($intestazione == 1)
		{
			$intestazione = 0;
			
			$array_align = array("L","C","C","C","C","R","R","R","R","R","R","R");
			$array_width = array();
	
			$array_width[] = 25;
			$array_width[] = 25;
			$array_width[] = 40;
			$array_width[] = 25;
			$array_width[] = 25;
			
			$array_width[] = 20;
			$array_width[] = 20;
			$array_width[] = 20;
			$array_width[] = 20;
			$array_width[] = 20;
			$array_width[] = 20;
			$array_width[] = 20;
	
			$pdf->setCellPaddings(2,1,2,0);
			$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta, $array_align);
			
			$pdf->setCellPaddings(2,0,2,1);
			$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta, $array_align);
		}
						
		$myFattura = new fatture_generali($arrayFatture[$l]);
		
		set_time_limit(300);
		
		$pdf->SetFont('Arial', '', $fontSecondo);
								
		$array_align = array("L","C","C","C","C","R","R","R","R","R","R","R");
		$array_width = array();

		$array_width[] = 25;
		$array_width[] = 25;
		$array_width[] = 40;
		$array_width[] = 25;
		$array_width[] = 25;
		
		$array_width[] = 20;
		$array_width[] = 20;
		$array_width[] = 20;
		$array_width[] = 20;
		$array_width[] = 20;
		$array_width[] = 20;
		$array_width[] = 20;
		
		$array_value_1 = array();





		$nomeQuestoComune = strtoupper($myFattura->NomeComuneDaCCFattura($myFattura->Fat_Comune));
		if (strlen($nomeQuestoComune) > 18)
		{
			$nomeQuestoComune = substr($nomeQuestoComune, 0, 16) . "..";
		}
		
		if ($myFattura->Fat_Totale_A_Doversi != 0) $mioTotale = $myFattura->Fat_Totale_A_Doversi;
		else $mioTotale = $myFattura->Fat_Totale;
		
		$array_value_1[] = $myFattura->Fat_Numero;	
		$array_value_1[] = $myFattura->Fat_Anno_Competenza;
		$array_value_1[] = $nomeQuestoComune;
		$array_value_1[] = from_mysql_date($myFattura->Fat_Data);				
		$array_value_1[] = from_mysql_date($myFattura->Fat_Data_Accredito);

		$year=$myFattura->Fat_Anno_Competenza;

		if (isset($aFatturaAnnoCompetenza[$year])) {
			$aFatturaAnnoCompetenza[$year][0] += $myFattura->Fat_Importo;
			$aFatturaAnnoCompetenza[$year][1] += $myFattura->Fat_Spese;
			$aFatturaAnnoCompetenza[$year][2] += $myFattura->Fat_Iva;
			$aFatturaAnnoCompetenza[$year][3] += $myFattura->Fat_Rimborsi;
			$aFatturaAnnoCompetenza[$year][4] += $myFattura->Fat_Bollo;
			$aFatturaAnnoCompetenza[$year][5] += $mioTotale;
			$aFatturaAnnoCompetenza[$year][6] += $myFattura->Fat_Accredito;
		}else{
			$aFatturaAnnoCompetenza[$year][0] = $myFattura->Fat_Importo;
			$aFatturaAnnoCompetenza[$year][1] = $myFattura->Fat_Spese;
			$aFatturaAnnoCompetenza[$year][2] = $myFattura->Fat_Iva;
			$aFatturaAnnoCompetenza[$year][3] = $myFattura->Fat_Rimborsi;
			$aFatturaAnnoCompetenza[$year][4] = $myFattura->Fat_Bollo;
			$aFatturaAnnoCompetenza[$year][5] = $mioTotale;
			$aFatturaAnnoCompetenza[$year][6] = $myFattura->Fat_Accredito;

		}

		$array_value_1[] = conv_num(number_format($myFattura->Fat_Importo, 2, ",", "."));
		$array_value_1[] = conv_num(number_format($myFattura->Fat_Spese, 2, ",", "."));
		$array_value_1[] = conv_num(number_format($myFattura->Fat_Iva, 2, ",", "."));
		$array_value_1[] = conv_num(number_format($myFattura->Fat_Rimborsi, 2, ",", "."));
		$array_value_1[] = conv_num(number_format($myFattura->Fat_Bollo, 2, ",", "."));
		$array_value_1[] = conv_num(number_format($mioTotale, 2, ",", "."));
		$array_value_1[] = conv_num(number_format($myFattura->Fat_Accredito, 2, ",", "."));
		
		$pdf->setCellPaddings(2,2,2,0);
		$y = crea_riga($pdf , $array_width, $array_value_1 , $ctrl_linea , $styleDash , $array_align );
		
		$array_align = array("R","C","C","L");
		$array_value_2 = array();
		
		$array_width = array();

		$array_width[] = 25;
		$array_width[] = 25;
		$array_width[] = 40;
		$array_width[] = 195;
		
		$array_value_2[] = $myFattura->Fat_Tipo;
		$array_value_2[] = $myFattura->Fat_Anno_Bilancio;
		$array_value_2[] = $myFattura->Fat_Comune;
		$array_value_2[] = $myFattura->Fat_Testo_Da_A_Periodo;
			
		if($ctrl_linea == "no")	$ctrl_linea = "up";
			
		$pdf->setCellPaddings(2,0,2,2);
		$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );
		
		if ($tipofile == "CSV")
		{
			for ($k = 0; $k < count($array_value_1); $k++)
			{
				fwrite ($myCsv, $array_value_1[$k], strlen($array_value_1[$k]));
				fwrite ($myCsv, ";", 1);
			}
			for ($k = 0; $k < count($array_value_2); $k++)
			{
				fwrite ($myCsv, $array_value_2[$k], strlen($array_value_2[$k]));
				fwrite ($myCsv, ";", 1);
			}
			fwrite ($myCsv, $aCapo, strlen($aCapo));
		}
		
		$cont_pag_result++;
		
		if ($cont_pag_result % 15 == 0)
		{
			$intestazione = 1;
			
			$pdf->AddMyPage(1, 'L');
			$pdf->Ln(5);
			
			$pdf->SetFont('Arial', 'B', $fontPrimo);
		}
		
		$annoTrovato = false;
		for ($zzz = 0; $zzz < count($arrayPerRiepilogo); $zzz++)
		{
			if ($arrayPerRiepilogo[$zzz][0] == $myFattura->Fat_Anno_Bilancio)
			{
				$annoTrovato = true;
				$memoTrovato = $zzz;
				break;
			}
		}
		if ($annoTrovato == true)
		{
			$arrayPerRiepilogo[$memoTrovato][0] = $myFattura->Fat_Anno_Bilancio;  //  anno
			if($myFattura->Fat_Tipo=='notacredito') $arrayPerRiepilogo[$memoTrovato][1] -= $myFattura->Fat_Importo;  //  ordinarie
			else $arrayPerRiepilogo[$memoTrovato][1] += $myFattura->Fat_Importo;  //  ordinarie

			if($myFattura->Fat_Tipo=='notacredito') $arrayPerRiepilogo[$memoTrovato][2] -= $myFattura->Fat_Spese;  //  temporanee
			else $arrayPerRiepilogo[$memoTrovato][2] += $myFattura->Fat_Spese;  //  temporanee

			if($myFattura->Fat_Tipo=='notacredito') $arrayPerRiepilogo[$memoTrovato][3] -= $myFattura->Fat_Iva;  //  affissioni
			else $arrayPerRiepilogo[$memoTrovato][3] += $myFattura->Fat_Iva;  //  affissioni

			if($myFattura->Fat_Tipo=='notacredito') $arrayPerRiepilogo[$memoTrovato][4] -= $myFattura->Fat_Rimborsi;  //  iva
			else $arrayPerRiepilogo[$memoTrovato][4] += $myFattura->Fat_Rimborsi;  //  iva

			if($myFattura->Fat_Tipo=='notacredito') $arrayPerRiepilogo[$memoTrovato][5] -= $mioTotale;  //  totale
			else $arrayPerRiepilogo[$memoTrovato][5] += $mioTotale;  //  totale

			if($myFattura->Fat_Tipo=='notacredito') $arrayPerRiepilogo[$memoTrovato][6] -= $myFattura->Fat_Accredito;  //  accredito
			else $arrayPerRiepilogo[$memoTrovato][6] += $myFattura->Fat_Accredito;  // accredito

			if($myFattura->Fat_Tipo=='notacredito') $arrayPerRiepilogo[$memoTrovato][7] -= $myFattura->Fat_Bollo;
			else $arrayPerRiepilogo[$memoTrovato][7] += $myFattura->Fat_Bollo;  // accredito



		}
		else
		{
			$memoNuovo = count($arrayPerRiepilogo);
			$arrayPerRiepilogo[$memoNuovo][0] = $myFattura->Fat_Anno_Bilancio;  //  anno
			$arrayPerRiepilogo[$memoNuovo][1] = $myFattura->Fat_Importo;  //  ordinarie
			$arrayPerRiepilogo[$memoNuovo][2] = $myFattura->Fat_Spese;  //  affissioni
			$arrayPerRiepilogo[$memoNuovo][3] = $myFattura->Fat_Iva;  //  tosap
			$arrayPerRiepilogo[$memoNuovo][4] = $myFattura->Fat_Rimborsi;  //  iva
			$arrayPerRiepilogo[$memoNuovo][5] = $mioTotale;  //  totale
			$arrayPerRiepilogo[$memoNuovo][6] = $myFattura->Fat_Accredito;  //  accredito
			$arrayPerRiepilogo[$memoNuovo][7] = $myFattura->Fat_Bollo;
		}
			
	}//CHIUSURA FATTURE
	
//-------------------- FINE FATTURE ----------------------------------

//-------------------- INIZIO RIEPILOGO ----------------------------------

	$intestazione = 1;

	$pdf->AddMyPage(1, 'L');
	$pdf->Ln(5);
	
	$pdf->Ln(5);
	
	$pdf->SetFont('Arial', 'B', $fontPrimo);
	$pdf->Cell(0, 5, "RIEPILOGO", 0, 0, "C");
	
	$pdf->Ln(5);
	$pdf->Ln(5);
		
	$pdf->SetFont('Arial', 'B', $fontPrimo);
	
	$ctrl_linea = "no";
	ksort($aFatturaAnnoCompetenza);







	$array_totali_riepilogo = array();
	$array_totali_riepilogo[] = "TOTALE";
	$array_totali_riepilogo[] = 0;
	$array_totali_riepilogo[] = 0;
	$array_totali_riepilogo[] = 0;
	$array_totali_riepilogo[] = 0;
	$array_totali_riepilogo[] = 0;
	$array_totali_riepilogo[] = 0;
	$array_totali_riepilogo[] = 0;

	for( $l=0; $l < count($arrayPerRiepilogo); $l++ )//FOR FATTURE
	{
		if ($intestazione == 1)
		{
			$intestazione = 0;
				
			$array_width = array();
				
			$array_width[] = 25;
			$array_width[] = 35;
			$array_width[] = 35;
			$array_width[] = 35;
			$array_width[] = 35;
			$array_width[] = 35;
			$array_width[] = 35;
			$array_width[] = 35;
	
			$array_intest_riep = array();
			$array_align = array("C","R","R","R","R","R","R","R");
			
			$array_intest_riep[] = "Anno";
			$array_intest_riep[] = "Importo";
			$array_intest_riep[] = "Spese";
			$array_intest_riep[] = "I.V.A.";
			$array_intest_riep[] = "Rimborsi";
			$array_intest_riep[] = "Imposta";
			$array_intest_riep[] = "Totale";
			$array_intest_riep[] = "Accredito";
		
			$pdf->setCellPaddings(2,1,2,0);
			$pdf->Ln(5);
			$y1_vert = crea_riga($pdf , $array_width, $array_intest_riep, "up" , $styleRetta, $array_align);
		}
	
		$myFattura = new fatture_generali($arrayFatture[$l]);
	
		set_time_limit(300);
		
		$pdf->SetFont('Arial', '', $fontPrimo);

		//$array_riepilogo = array();
			
		//$array_riepilogo[] = $arrayPerRiepilogo[$l][0];
		//$array_riepilogo[] = number_format($arrayPerRiepilogo[$l][1], 2, ",", ".");
		//$array_riepilogo[] = number_format($arrayPerRiepilogo[$l][2], 2, ",", ".");
		//$array_riepilogo[] = number_format($arrayPerRiepilogo[$l][3], 2, ",", ".");
		//$array_riepilogo[] = number_format($arrayPerRiepilogo[$l][4], 2, ",", ".");
		//$array_riepilogo[] = number_format($arrayPerRiepilogo[$l][7], 2, ",", ".");
		//$array_riepilogo[] = number_format($arrayPerRiepilogo[$l][5], 2, ",", ".");
		//$array_riepilogo[] = number_format($arrayPerRiepilogo[$l][6], 2, ",", ".");


		foreach ($aFatturaAnnoCompetenza as $iArray=>$value) {
			$a_riepilogo = array();
			//$pdf->ln(10);
			//$pdf->Cell(0, 0, $iArray.": ".$value);
			$pdf->setCellPaddings(2, 1, 2, 0);
			$pdf->Ln(5);

			$a_riepilogo[] = $iArray;
			$a_riepilogo[] = number_format($value[0], 2, ",", ".");
			$a_riepilogo[] = number_format($value[1], 2, ",", ".");
			$a_riepilogo[] = number_format($value[2], 2, ",", ".");
			$a_riepilogo[] = number_format($value[3], 2, ",", ".");
			$a_riepilogo[] = number_format($value[4], 2, ",", ".");
			$a_riepilogo[] = number_format($value[5], 2, ",", ".");
			$a_riepilogo[] = number_format($value[6], 2, ",", ".");
			$y1_vert = crea_riga($pdf , $array_width, $a_riepilogo, "up" , $styleRetta, $array_align);


			$array_totali_riepilogo[1] += $value[0];
			$array_totali_riepilogo[2] += $value[1];
			$array_totali_riepilogo[3] += $value[2];
			$array_totali_riepilogo[4] += $value[3];
			$array_totali_riepilogo[5] += $value[4];
			$array_totali_riepilogo[6] += $value[5];
			$array_totali_riepilogo[7] += $value[6];




		}









		
		//$pdf->setCellPaddings(2,1,2,0);
		//$pdf->Ln(5);
		//$y1_vert = crea_riga($pdf , $array_width, $array_riepilogo, "up" , $styleRetta, $array_align);
	}
	
	$array_totali_riepilogo[1] = number_format($array_totali_riepilogo[1], 2, ",", ".");
	$array_totali_riepilogo[2] = number_format($array_totali_riepilogo[2], 2, ",", ".");
	$array_totali_riepilogo[3] = number_format($array_totali_riepilogo[3], 2, ",", ".");
	$array_totali_riepilogo[4] = number_format($array_totali_riepilogo[4], 2, ",", ".");
	$array_totali_riepilogo[5] = number_format($array_totali_riepilogo[5], 2, ",", ".");
	$array_totali_riepilogo[6] = number_format($array_totali_riepilogo[6], 2, ",", ".");
	$array_totali_riepilogo[7] = number_format($array_totali_riepilogo[7], 2, ",", ".");


	$pdf->SetFont('Arial', 'B', $fontPrimo);
	
	$pdf->setCellPaddings(2,1,2,0);
	$pdf->Ln(5);
	$y1_vert = crea_riga($pdf , $array_width, $array_totali_riepilogo, "up" , $styleRetta, $array_align);
	
//-------------------- FINE RIEPILOGO ----------------------------------
	
	
	$pdf->setPrintHeader(false);
	$pdf->AddMyPage(3, 'L');
	$pdf->setPrintFooter(false);
	
	
	$pdf->setCellPaddings(2,0,2,1);
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 18);
	$pdf->Cell(0, 0, $nomeComuneSarida , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->SetFont('Arial', '', 16);
	$pdf->Cell(0, 0, "ELENCO FATTURE" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->ln(20);
	$pdf->SetFont('Arial', 'B', 14);
	$pdf->Cell(0, 0, "SELEZIONI" , 0, 1, 'L');
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "TIPO TRIBUTO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sceltaTipoFattura . $sceltaTipoGestione , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "COMUNE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDeiComuni , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "ANNI COMPETENZA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sceltaCompetenza , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "ANNI BILANCIO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sceltaBilancio , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI FATTURAZIONE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataFatturazione , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI RISCOSSIONE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataRiscossione , 0, 1, "L");
	
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 14);
	$pdf->Cell(0, 0, "RIEPILOGO" , 0, 1, 'L');
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "NUMERO PAGINE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 23.5 , 5, $pdf->PageNo() , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "NUMERO FATTURE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 23.5 , 5, $num_Fatture , 0, 1, "R");
	
	
	/*$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "TOTALE DOVUTO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_dovuto,2)) . " Euro" , 0, 1, "R");*/
	
	/*$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "TOTALE PAGATO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_pagato,2)) . " Euro" , 0, 1, "R");*/
	
	$pdf->movePage($pdf->PageNo(), 1);
	
	if ($tipofile == "PDF")
	{
		$pdf->Output( $file_elenco , 'F');
		
		if ($cont_pag_result == 0) 
		{
			unlink($file_elenco);
			echo "<script>nessun_risultato();</script>";
		}
		else	echo "<script>finepdf('Elaborazione completata');</script>";
	}
	else if ($tipofile == "CSV")
	{
		fclose($myCsv);
	
		if ($cont_pag_result == 0) 
		{
			unlink($file_csv);
			echo "<script>nessun_risultato();</script>";
		}
		else
		{
			echo <<< FINECSV
			
				<script>
					finecsv('Elaborazione completata');
					$("#vedi_csv").html("<a href='$vedi_csv'>Scaricare file</a>");
				</script>
			
FINECSV;
		}
		
	}

?>

</body>
</html>