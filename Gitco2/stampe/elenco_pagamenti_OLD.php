<?php

include("../_path.php");
include(ROOT."/_parameter.php");

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";


include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_split_payment.php";

$a_width = array(13,13,13,50,30,14,22,24,26,26,26,21);
$a_align = array("L","L","L","L","L","L","L","R","R","R","R","R");
$a_align_value = array("R","R","R","L","L","L","L","R","R","R","R","R");

$a_width_end = array(89, 44, 22, 24, 26, 26, 26, 21);
$a_align_end = array("L","R","R", "R","R","R","R","R");

$minElements = 5;
function headerArray($a_splitParams){
    $a_header[0] = array("Cron.","Cron.","Cron.","Utente","TipoPag-Rata","Partita","Pagamento");
    $a_header[1] = array("Acc.","Ing.","Pign.","CF / PI","Quietanza","Stato","Data Pag.");
    $a_header[2] = array("","","","","","","");

    $splitElements = 5;

    $minElements = count($a_header[0]);
    $totalElements = $minElements+$splitElements;

    for ($i = 0; $i < count($a_splitParams); $i++) {
        if(strlen($a_splitParams[$i]['header'])>11)
            $headerText = substr($a_splitParams[$i]['header'],0,10).".";
        else
            $headerText = $a_splitParams[$i]['header'];

        if ($i < $splitElements)
            $a_header[0][] = $headerText;
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_header[1][] = $headerText;
        else if ($i >= ($splitElements * 2))
            $a_header[2][] = $headerText;
    }

    if (count($a_header[0]) < $totalElements) {
        for ($i = count($a_header[0]); $i < $totalElements; $i++) {
            $a_header[0][] = "";
        }
    }
    if (count($a_header[1]) < $totalElements) {
        for ($i = count($a_header[1]); $i < $totalElements; $i++) {
            $a_header[1][] = "";
        }
    }
    if (count($a_header[2]) > $minElements && count($a_header[2]) < $totalElements) {
        for ($i = count($a_header[2]); $i < $totalElements; $i++) {
            $a_header[2][] = "";
        }
    }
    return $a_header;
}

function valueArray($a_split, $a_value, $a_splitParams){

    $splitElements = 5;

    $minElements = count($a_value[0]);
    $totalElements = $minElements+$splitElements;


    for ($i = 0; $i < count($a_splitParams); $i++) {
        if($a_split[$a_splitParams[$i]['split_number']]=="")
            $a_split[$a_splitParams[$i]['split_number']] = 0.00;

        if ($i < $splitElements)
            $a_value[0][] = number_format($a_split[$a_splitParams[$i]['split_number']],2,",",".");
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_value[1][] = number_format($a_split[$a_splitParams[$i]['split_number']],2,",",".");
        else if ($i >= ($splitElements * 2))
            $a_value[2][] = number_format($a_split[$a_splitParams[$i]['split_number']],2,",",".");
    }

    if (count($a_value[0]) < $totalElements) {
        for ($i = count($a_value[0]); $i < $totalElements; $i++) {
            $a_value[0][] = "";
        }
    }
    if (count($a_value[1]) < $totalElements) {
        for ($i = count($a_value[1]); $i < $totalElements; $i++) {
            $a_value[1][] = "";
        }
    }
    if (count($a_value[2]) > $minElements && count($a_value[2]) < $totalElements) {
        for ($i = count($a_value[2]); $i < $totalElements; $i++) {
            $a_value[2][] = "";
        }
    }
    return $a_value;
}

function endArray($parzPagato, $a_split, $a_splitParams){
    $a_end[0] = array("PARZIALI DI PAGINA","Totale pagato",number_format($parzPagato,2,",","."));
    $a_end[1] = array("","","");
    $a_end[2] = array("","","");

    $splitElements = 5;

    $minElements = count($a_end[0]);
    $totalElements = $minElements+$splitElements;

    for ($i = 0; $i < count($a_splitParams); $i++) {
        if ($i < $splitElements)
            $a_end[0][] = number_format($a_split[$i],2,",",".");
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_end[1][] = number_format($a_split[$i],2,",",".");
        else if ($i >= ($splitElements * 2))
            $a_end[2][] = number_format($a_split[$i],2,",",".");
    }

    if (count($a_end[0]) < $totalElements) {
        for ($i = count($a_end[0]); $i < $totalElements; $i++) {
            $a_end[0][] = "";
        }
    }
    if (count($a_end[1]) < $totalElements) {
        for ($i = count($a_end[1]); $i < $totalElements; $i++) {
            $a_end[1][] = "";
        }
    }
    if (count($a_end[2]) > $minElements && count($a_end[2]) < $totalElements) {
        for ($i = count($a_end[2]); $i < $totalElements; $i++) {
            $a_end[2][] = "";
        }
    }
    return $a_end;
}

function finalArray($tot_pagato, $a_split, $a_splitParams){
    $a_final[0] = array("TOTALE PAGAMENTI","Totale pagato",number_format($tot_pagato,2,",","."));
    $a_final[1] = array("","","");
    $a_final[2] = array("","","");

    $splitElements = 5;

    $minElements = count($a_final[0]);
    $totalElements = $minElements+$splitElements;

    for ($i = 0; $i < count($a_splitParams); $i++) {
        if ($i < $splitElements)
            $a_final[0][] = number_format($a_split[$i],2,",",".");
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_final[1][] = number_format($a_split[$i],2,",",".");
        else if ($i >= ($splitElements * 2))
            $a_final[2][] = number_format($a_split[$i],2,",",".");
    }

    if (count($a_final[0]) < $totalElements) {
        for ($i = count($a_final[0]); $i < $totalElements; $i++) {
            $a_final[0][] = "";
        }
    }
    if (count($a_final[1]) < $totalElements) {
        for ($i = count($a_final[1]); $i < $totalElements; $i++) {
            $a_final[1][] = "";
        }
    }
    if (count($a_final[2]) > $minElements && count($a_final[2]) < $totalElements) {
        for ($i = count($a_final[2]); $i < $totalElements; $i++) {
            $a_final[2][] = "";
        }
    }
    return $a_final;
}

$cls_db = new cls_db();
$cls_split = new cls_split_payment();

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
		if ($this->tipo == 1) $this->Cell(0, 5, "Elenco Pagamenti Associati a Ingiunzioni/Pignoramenti" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
		if ($this->tipo == 2) $this->Cell(0, 5, "Elenco Pagamenti Da Associare" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
		if ($this->tipo == 3) $this->Cell(0, 5, "bla bla" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
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

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune = ($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];


//PREPARAZIONE ELENCO
$elenco_dir = crea_dir( $archivioPath . "/Targhe_Estere/Pagamenti" );
$data_file = date('Y-m-d_H-i-s');

$prefissoNomeStampa = "elenco_pagamenti_";

delete_files_after_X_days($PathCompletoPagamentiEsteri, $prefissoNomeStampa, 30);

$file_elenco = $elenco_dir . "/" . $prefissoNomeStampa . $data_file . ".pdf";
$download = $file_elenco;

$vedi_file = mostra_file_path($download);

$file_csv = $elenco_dir . "/" . $prefissoNomeStampa . $data_file . ".csv";

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
<title>Elenco Pagamenti</title>

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
	//self.close();
	//$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Elenco' onclick='mostra_file();'>");
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
<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
	<tr>
		<td valign=top>
			<br><br><br>
			<font class="titolo font18 text_center">Elenco Pagamenti</font>
			
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

/*$daco  = strtoupper(get_var('daco'));
$anom  = strtoupper(get_var('anom'));

$dano  = strtoupper(get_var('dano'));
$acog  = strtoupper(get_var('acog'));*/

$da_n_elenco = get_var('da_n_elenco');
$a_n_elenco = get_var('a_n_elenco');

$da_anno = get_var('da_anno');
$a_anno = get_var('a_anno');

$tipoTributo = get_var('tipoTributo');


/*$da_avviso = from_mysql_date(get_var('da_avviso'));
$a_avviso = from_mysql_date(get_var('a_avviso'));*/

$selecttipopagamento = get_var('selecttipopagamento');
$selectmodalitapagamento = get_var('selectmodalitapagamento');
$contoTerzi = get_var('contoTerzi');

$da_pagamento = from_mysql_date(get_var('da_pagamento'));
$a_pagamento = from_mysql_date(get_var('a_pagamento'));

$da_registrazione = from_mysql_date(get_var('da_registrazione'));
$a_registrazione = from_mysql_date(get_var('a_registrazione'));

$da_infrazione = from_mysql_date(get_var('da_infrazione'));
$a_infrazione = from_mysql_date(get_var('a_infrazione'));

$ordinestampa = get_var('ordinestampa');

$sceltaPartita = $sceltaAnni = $sceltaTipoPagamento = $sceltaTributo = "Tutti";
$sceltaDataAvviso = $sceltaDataPagamento = $sceltaDataRegistrazione = "Tutte";

if($da_infrazione!="" && count($tipoTributo)==1 && $tipoTributo[0] == "CDS-") {
    $dataInfrazione = "CONCAT(
    SUBSTRING_INDEX ( ( SUBSTRING_INDEX(SUBSTRING_INDEX(tributo.Info_Cartella,\" \",-5),\" \",1) ), \"/\",-1 ),
    \"-\",
    SUBSTRING_INDEX ( SUBSTRING_INDEX ( ( SUBSTRING_INDEX(SUBSTRING_INDEX(tributo.Info_Cartella,\" \",-5),\" \",1) ), \"/\",2 ),\"/\",-1),
    \"-\",
    SUBSTRING_INDEX ( ( SUBSTRING_INDEX(SUBSTRING_INDEX(tributo.Info_Cartella,\" \",-5),\" \",1) ), \"/\",1 )
)";
}
else
    $dataInfrazione = "\"\"";


$queryDaAss = "SELECT pagamento.ID AS PAGID, ".$dataInfrazione." AS DATAINFRAZIONE, tributo.Info_Cartella AS INFOCARTELLA FROM pagamento ";
$queryDaAss.= "JOIN partita_tributi ON pagamento.Partita_ID = partita_tributi.ID ";
$queryDaAss.= "JOIN tributo ON tributo.Partita_ID = partita_tributi.ID ";
$queryDaAss.= "WHERE pagamento.CC = \"".$c."\" AND pagamento.Atto_ID = 0 AND pagamento.Tipo_Atto !='Precedenti' ";

$query = "SELECT pagamento.ID AS PAGID, ".$dataInfrazione." AS DATAINFRAZIONE, tributo.Info_Cartella AS INFOCARTELLA FROM pagamento ";
$query.= "JOIN partita_tributi ON pagamento.Partita_ID = partita_tributi.ID ";
$query.= "JOIN tributo ON tributo.Partita_ID = partita_tributi.ID ";
$query.= "WHERE pagamento.CC = \"".$c."\" AND pagamento.Atto_ID != 0 ";

if($da_infrazione!="" && count($tipoTributo)==1 && $tipoTributo[0] == "CDS-"){

    $queryDataInfrazione = "AND ".$dataInfrazione." >= '" . to_mysql_date($da_infrazione) . "' AND ";
    $queryDataInfrazione.= $dataInfrazione." <= '" . to_mysql_date($a_infrazione) . "' ";
    $query.= $queryDataInfrazione;
}


for($i=0;$i<count($tipoTributo);$i++) {
    if($i>0)
        $query.= "OR";
    else
        $query.= "AND ( ";

    $exp_tipo = explode("-",$tipoTributo[$i]);
    $query.= " ( partita_tributi.Tipo = '".$exp_tipo[0]."' ";

    if($exp_tipo[1]!=""){
        $query.= "AND partita_tributi.Sottotipo = '".$exp_tipo[1]."' ";
        $sceltaTrib = $exp_tipo[1];
    }
    else
        $sceltaTrib = $exp_tipo[0];

    $query.= " ) ";

    if($i==0)
        $sceltaTributo = $sceltaTrib;
    else
        $sceltaTributo.= " - ".$sceltaTrib;
}
if(count($tipoTributo)>0)
    $query.= " ) ";

if ($da_n_elenco != NULL){
    $query.= "AND partita_tributi.Comune_ID >= '$da_n_elenco' AND partita_tributi.Comune_ID <= '$a_n_elenco' ";
	$sceltaPartita = "Da $da_n_elenco a $a_n_elenco";
}

if ($da_anno != NULL){
    $query.= "AND partita_tributi.Anno_Riferimento >= $da_anno AND partita_tributi.Anno_Riferimento <= $a_anno ";
	$sceltaAnni = "Da $da_anno a $a_anno";
}

if ($selecttipopagamento != NULL){
	switch ($selecttipopagamento)
	{
		case "TELEMATICO":
            $query.= " AND (pagamento.Telematico = 'SI' OR pagamento.Telematico = 'S' OR pagamento.Telematico = 'Y') ";
			break;
		case "BONIFICATO":
            $query.= "AND pagamento.Tipo_Pagamento = 'BONIFICATO' AND ";
            $query.= "(pagamento.Telematico = 'NO' OR pagamento.Telematico = 'N') ";
			break;
		default:
		    $query.= "AND pagamento.Tipo_Pagamento = '" . $selecttipopagamento . "' ";
		    break;
	}
	$sceltaTipoPagamento = "Solo '" . $selecttipopagamento . "'";
}

if ($selectmodalitapagamento != NULL)
    $query.= "AND pagamento.Modalita = '" . $selectmodalitapagamento . "' ";

if($contoTerzi=="Y")
    $query.= "AND pagamento.Conto_Terzi = 'Y' ";
else if($contoTerzi=="N")
    $query.= "AND pagamento.Conto_Terzi != 'Y' ";

if ($da_pagamento != NULL){
    $queryDataPag = "AND pagamento.Data_Pagamento >= '" . to_mysql_date($da_pagamento) . "' AND ";
    $queryDataPag.= "pagamento.Data_Pagamento <= '" . to_mysql_date($a_pagamento) . "' ";
    $query.= $queryDataPag;
    $queryDaAss.= $queryDataPag;
	$sceltaDataPagamento = "Da " . get_var('da_pagamento') . " a " . get_var('a_pagamento');
}

if ($da_registrazione != NULL){
    $queryDataReg = "AND pagamento.Data_Registrazione >= '" . to_mysql_date($da_registrazione) . "' AND ";
    $queryDataReg.= "pagamento.Data_Registrazione <= '" . to_mysql_date($a_registrazione) . "' ";
    $query.= $queryDataReg;
    $queryDaAss.= $queryDataReg;
	$sceltaDataRegistrazione = "Da " . get_var('da_registrazione') . " a " . get_var('a_registrazione');
}

switch ($ordinestampa){
	case "PARTITA": $query.= "GROUP BY pagamento.ID ORDER BY partita_tributi.Split_Parameters_ID, partita_tributi.Comune_ID, pagamento.Rata "; break;
	case "DATAPAG": $query.= "GROUP BY pagamento.ID ORDER BY partita_tributi.Split_Parameters_ID, pagamento.Data_Pagamento, pagamento.Atto_ID, pagamento.Rata "; break;
	default: break;
}

switch ($ordinestampa){
    case "PARTITA": $queryDaAss.= "GROUP BY pagamento.ID ORDER BY partita_tributi.Split_Parameters_ID, partita_tributi.Comune_ID, pagamento.Rata "; break;
    case "DATAPAG": $queryDaAss.= "GROUP BY pagamento.ID ORDER BY partita_tributi.Split_Parameters_ID, pagamento.Data_Pagamento, pagamento.Atto_ID, pagamento.Rata "; break;
    default: break;
}

flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();
sleep(2);

$a_Pagamenti = $cls_db->getResults( $cls_db->SelectQuery( $query ) );
$arrayDaAssPagamenti = $cls_db->getResults( $cls_db->SelectQuery( $queryDaAss ) );

$num_totale_pagamenti = count($a_Pagamenti) + count($arrayDaAssPagamenti);

$arrayPagamenti = array_merge($a_Pagamenti,$arrayDaAssPagamenti);

if ($_SESSION['CC_User'] == "***+")
	echo "<br>" . $query . " --> " . count($arrayPagamenti);

//if ($_SESSION['CC_User'] == "***+")
//	echo "<br>" . $queryDaAss . " --> " . count($arrayDaAssPagamenti);

$fontPrimo = 8.5;
$fontSecondo = 8;
$tot_gen_pagato = 0;
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
	
/**
	//////////////////////////////////////////////////////////////////////////////
			PAGAMENTI ASSOCIATI
*/
	
	$cont_pag_result = 0;
	$cont_pagdaass_result = 0;
	$parzPagato = 0;
	$totalePagato = 0;
	
	$arrayPartite = array();
	$changeParams = "";
	$ctrl_linea = "no";
	for( $l=0; $l < count($arrayPagamenti); $l++ )//FOR ATTI
	{
		set_time_limit(300);
		
		echo "<script>update(".ceil($l*100/count($arrayPagamenti)).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
						
						
		$myPagamento = new pagamento($arrayPagamenti[$l]['PAGID'], $c);
		$info_cartella = $arrayPagamenti[$l]['INFOCARTELLA'];
        $data_infrazione = $arrayPagamenti[$l]['DATAINFRAZIONE'];
		$id_ingiunzione = "";
		$anno_ingiunzione = "";
		$id_pigno = "";
		$anno_pigno = "";
		if(strpos($myPagamento->Tipo_Atto,'Pignoramento')===false)
		{
			$myAtto = new atto($myPagamento->Atto_ID, $myPagamento->CC);
            $mioPigno = new pignoramento(null, $myPagamento->CC);
			$id_ingiunzione = $myAtto->ID_Cronologico;
			$anno_ingiunzione = $myAtto->Anno_Cronologico;

		}
		else
		{
			$mioPigno = new pignoramento($myPagamento->Atto_ID, $myPagamento->CC);
			$id_pigno = $mioPigno->ID_Cronologico;
			$anno_pigno = $mioPigno->Anno_Cronologico;
			$myAtto = new atto($mioPigno->Atto_ID, $myPagamento->CC);
			$id_ingiunzione = $myAtto->ID_Cronologico;
			$anno_ingiunzione = $myAtto->Anno_Cronologico;
		}

		$myPartita = new partita($myPagamento->Partita_ID, $c);
        if (!IsInArray($arrayPartite, $myPartita->ID))
            $arrayPartite[] = $myPartita->ID;


        //echo "<br>" . $myPagamento->Atto_ID;
        if ($myPagamento->Pagante == "")
        {
            if ($myPartita->Utente->Cognome == "")
            {
                $pagante = $myPartita->Utente->Ditta;
                $CF_PI = $myPartita->Utente->Partita_Iva;
            }
            else
            {
                $pagante = $myPartita->Utente->Cognome . " " . $myPartita->Utente->Nome;
                $CF_PI = $myPartita->Utente->Codice_Fiscale;
            }
        }
        else
        {
            $pagante = $myPagamento->Pagante;
            $CF_PI = "";
        }

        $numeroRata = $myPagamento->Rata;
        if ($numeroRata == 0) $numeroRata = "UNICA";

        if ($myPagamento->Quietanza != "")
        {
            if ($myPagamento->Bollettario != "") $quietanza = $myPagamento->Quietanza . "-" . $myPagamento->Bollettario;
            else $quietanza = $myPagamento->Quietanza;
        }
        else $quietanza = $myPagamento->Bollettario;

        $stato_pagamento = substr($myPagamento->Tipo_Pagamento, 0, 3);
        if($myPagamento->Telematico =="SI" || $myPagamento->Telematico =="S" || $myPagamento->Telematico =="Y"){
            $stato_pagamento.= " TELEM";
        }

        $a_params = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersFromIdQuery($myPartita->Split_Parameters_ID) ) );
        if(!$a_params['id']>0)
            $a_params = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersQuery($c) ) );

        $a_splitParams = $cls_split->getLineByPriority($a_params);

        if($changeParams!=$a_params['id']) {
            if($changeParams>0){

                $y2_vert = $pdf->getY();
                crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

                //PARZIALI
                $a_end = endArray($a_splitPartial, $a_splitParams);

                $pdf->SetFont('Arial', 'B', $fontSecondo);
                $pdf->setCellPaddings(2,1,2,0);
                $y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
                }

                //TOTALI
                $a_final = finalArray($a_splitTotal, $a_splitParams);

                $pdf->SetFont('Arial', 'B', $fontSecondo);
                $pdf->setCellPaddings(2,1,2,0);
                $y = crea_riga($pdf , $a_width_end, $a_final[0], "up" , $styleRetta, $a_align_end);

                if(count($a_final[2])==count($a_width_end)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y = crea_riga($pdf , $a_width_end, $a_final[1], $line , $styleRetta, $a_align_end);

                if(count($a_final[2])==count($a_width_end)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y = crea_riga($pdf , $a_width_end, $a_final[2], "down" , $styleRetta, $a_align_end);
                }

                $pdf->AddMyPage(1, 'L');
                $pdf->Ln(5);

                $ctrl_linea = "no";

            }

            $parzPagato = 0;
            $totalePagato = 0;

            for ($i = 0; $i < count($a_splitParams); $i++) {
                $a_splitTotal[$i] = 0;
                $a_splitPartial[$i] = 0;
            }

            $a_header = headerArray($a_splitParams);

            if ($tipofile == "CSV")
            {
                for ($k = 0; $k < count($a_header[0]); $k++)
                {
                    fwrite ($myCsv, $a_header[0][$k], strlen($a_header[0][$k]));
                    fwrite ($myCsv, ";", 1);
                    if($a_header[1][$k]!="") {
                        fwrite($myCsv, $a_header[1][$k], strlen($a_header[1][$k]));
                        fwrite($myCsv, ";", 1);
                    }
                    if(count($a_header[2])==count($a_width)){
                        if($a_header[2][$k]!=""){
                            fwrite ($myCsv, $a_header[2][$k], strlen($a_header[2][$k]));
                            fwrite ($myCsv, ";", 1);
                        }

                    }
                }
                fwrite ($myCsv, $aCapo, strlen($aCapo));
            }
            else{
                $pdf->SetFont('Arial', 'B', $fontPrimo);

                $pdf->setCellPaddings(2,1,2,0);
                $y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

                if(count($a_header[2])==count($a_width)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

                if(count($a_header[2])==count($a_width)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
                }
            }
        }

        $changeParams = $a_params['id'];
        $tot_gen_pagato+= $myPagamento->Importo;
        $parzPagato+= $myPagamento->Importo;
        $totalePagato+= $myPagamento->Importo;


		if($myPagamento->getSplitSum()>0)
		{
		    for($i=1;$i<=16;$i++){
		        $key = "Split_Payment".$i;
		        $a_splitRequest[$i] =  $myPagamento->$key;
            }

            for($i=0;$i<count($a_splitParams);$i++){
                if(isset($a_splitRequest[$a_splitParams[$i]['split_number']])){
                    $a_splitPartial[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                    $a_splitTotal[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                }
            }
		}
		else{
            $a_splitRequest = $myPagamento->splitPayment($myPartita,$myAtto,$mioPigno,$a_splitParams,$a_params['id']);
            for($i=0;$i<count($a_splitParams);$i++){
                if(isset($a_splitRequest[$a_splitParams[$i]['split_number']])){
                    $a_splitPartial[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                    $a_splitTotal[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                }
            }
		}
		
		$accertOrig = $myPartita->estraiTitoloTributo();
        $num_rate = "";
        if($myPagamento->Totale_Rate>0)
            $num_rate = "/".$myPagamento->Totale_Rate;

        $a_value[0] = array($accertOrig,$id_ingiunzione,$id_pigno,$pagante,$myPagamento->Modalita . " - " . $numeroRata.$num_rate,$myPartita->Comune_ID,number_format($myPagamento->Importo,2,",","."));
        $a_value[1] = array($myPartita->Anno_Riferimento,$anno_ingiunzione,$anno_pigno,strtoupper($CF_PI),$quietanza,$stato_pagamento,from_mysql_date($myPagamento->Data_Pagamento));
        $a_value[2] = array("","","","","","","");

        $a_value = valueArray($a_splitRequest,$a_value,$a_splitParams);

        if ($tipofile == "CSV")
        {
            for ($k = 0; $k < count($a_value[0]); $k++)
            {
                fwrite ($myCsv, $a_value[0][$k], strlen($a_value[0][$k]));
                fwrite ($myCsv, ";", 1);
                if($a_header[1][$k]!=""){
                    fwrite ($myCsv, $a_value[1][$k], strlen($a_value[1][$k]));
                    fwrite ($myCsv, ";", 1);
                }
                if(count($a_value[2])==count($a_width)){
                    if($a_header[2][$k]!=""){
                        fwrite ($myCsv, $a_value[2][$k], strlen($a_value[2][$k]));
                        fwrite ($myCsv, ";", 1);
                    }
                }
            }
            fwrite ($myCsv, $aCapo, strlen($aCapo));
        }
        else{
            $pdf->SetFont('Arial', '', $fontSecondo);

            $pdf->setCellPaddings(2,2,2,0);
            $y = crea_riga($pdf , $a_width, $a_value[0], $ctrl_linea , $styleDash, $a_align_value);
            if($ctrl_linea == "no")	$ctrl_linea = "up";

            if(count($a_value[2])==count($a_width)){
                $padding = 0;
            }
            else{
                $padding = 2;
            }

            $pdf->setCellPaddings(2,0,2,$padding);
            $y = crea_riga($pdf , $a_width, $a_value[1], "no" , $styleDash, $a_align_value);

            if(count($a_value[2])==count($a_width)){
                $pdf->setCellPaddings(2,0,2,2);
                $y = crea_riga($pdf , $a_width, $a_value[2], "no" , $styleDash, $a_align_value);
            }

            if( $y > $altezza_pag - 40)
            {
                //$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );

                $y2_vert = $pdf->getY();
                crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

                $a_end = endArray($parzPagato, $a_splitPartial, $a_splitParams);

                $pdf->SetFont('Arial', 'B', $fontSecondo);
                $pdf->setCellPaddings(2,1,2,0);
                $y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
                }

                for($i=0;$i<count($a_splitParams);$i++) {
                    $a_splitPartial[$i] = 0;
                }
                $parzPagato = 0;

                $pdf->AddMyPage(1, 'L');
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', $fontPrimo);

                $pdf->setCellPaddings(2,1,2,0);
                $y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

                if($a_header[2]>$minElements){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

                if($a_header[2]>$minElements){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
                }

                $ctrl_linea = "no";
            }
        }

		$cont_pag_result++;
			
	}//CHIUSURA PAGAMENTI ASSOCIATI

    if($num_totale_pagamenti>0 && $tipofile == "PDF"){
        $y2_vert = $pdf->getY();
        crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

        $a_end = endArray($parzPagato, $a_splitPartial, $a_splitParams);

        $pdf->SetFont('Arial', 'B', $fontSecondo);
        $pdf->setCellPaddings(2,1,2,0);
        $y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);

        if(count($a_end[2])==count($a_width_end)){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        $y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);

        if(count($a_end[2])==count($a_width_end)){
            $pdf->setCellPaddings(2,0,2,1);
            $y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
        }

        if( $y > $altezza_pag - 40)
        {
            $pdf->AddMyPage(1, 'L');
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', $fontPrimo);
            $pdf->setCellPaddings(2,1,2,0);
            $y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

            if($a_header[2]>$minElements){
                $padding = 0;
                $line = "no";
            }
            else{
                $padding = 1;
                $line = "down";
            }

            $pdf->setCellPaddings(2,0,2,$padding);
            $y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

            if($a_header[2]>$minElements){
                $pdf->setCellPaddings(2,0,2,1);
                $y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
            }

            $ctrl_linea = "no";
        }

        //TOTALI
        $a_final = finalArray($totalePagato, $a_splitTotal, $a_splitParams);

        $pdf->SetFont('Arial', 'B', $fontSecondo);
        $pdf->setCellPaddings(2,1,2,0);
        $y = crea_riga($pdf , $a_width_end, $a_final[0], "up" , $styleRetta, $a_align_end);

        if(count($a_final[2])==count($a_width_end)){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        $y = crea_riga($pdf , $a_width_end, $a_final[1], $line , $styleRetta, $a_align_end);

        if(count($a_final[2])==count($a_width_end)){
            $pdf->setCellPaddings(2,0,2,1);
            $y = crea_riga($pdf , $a_width_end, $a_final[2], "down" , $styleRetta, $a_align_end);
        }


        $pdf->setPrintHeader(false);
        $pdf->AddMyPage(3, 'L');
        $pdf->setPrintFooter(false);


        $pdf->setCellPaddings(2,0,2,1);
        $pdf->ln(10);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 0, "COMUNE DI ".strtoupper($nome_com) , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->SetFont('Arial', '', 16);
        $pdf->Cell(0, 0, "ELENCO PAGAMENTI" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->ln(20);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 0, "SELEZIONI" , 0, 1, 'L');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "TRIBUTI:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaTributo , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "PARTITA:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaPartita , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "ANNI GESTIONE ATTI ORIGINARI:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaAnni , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "TIPO DI IMPORTAZIONE:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaTipoPagamento , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "DATA DI INFRAZIONE:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataAvviso , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "DATA DI PAGAMENTO:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataPagamento , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "DATA DI REGISTRAZIONE:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataRegistrazione , 0, 1, "L");

        $pdf->ln(10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 0, "RIEPILOGO" , 0, 1, 'L');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (67, 0, "NUMERO PAGINE:", 0, 0, "L");
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell ( 23.5 , 5, $pdf->PageNo() , 0, 1, "R");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (67, 0, "NUMERO PAGAMENTI:", 0, 0, "L");
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell ( 23.5 , 5, count($arrayPagamenti) , 0, 1, "R");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (67, 0, "NUMERO VERBALI:", 0, 0, "L");
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell ( 23.5 , 5, count($arrayPartite) , 0, 1, "R");

        /*
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (67, 0, "TOTALE DOVUTO:", 0, 0, "L");
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_dovuto,2)) . " Euro" , 0, 1, "R");
        */
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (67, 0, "TOTALE PAGATO:", 0, 0, "L");
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_pagato,2)), 0, 1, "R");

        $pdf->movePage($pdf->PageNo(), 1);
    }

	if ($tipofile == "PDF")
	{
		//echo "<br>" . $file_elenco;
		//return;
		$pdf->Output( $file_elenco , 'F');
		
		if ($cont_pag_result == 0 && $cont_pagdaass_result == 0) 
		{
			unlink($file_elenco);
			echo "<script>nessun_risultato();</script>";
		}
		else	echo "<script>finepdf('Elaborazione completata');</script>";
	}
	else if ($tipofile == "CSV")
	{
		fclose($myCsv);
	
		if ($cont_pag_result == 0 && $cont_pagdaass_result == 0) 
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

<?php 
function IsInArray ($array, $dato)
{
	for ($i = 0; $i < count($array); $i++)
	{
		if ($dato == $array[$i]) return true;
	}
	return false;
}