<?php


if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_split_payment.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_CoazioneUtils.php";
include_once CLS . "/cls_Stampe.php";


/*include("../_path.php");
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
include_once CLS . "/cls_split_payment.php";*/

$a_width = array(13,13,13,50,30,14,22,24,26,26,26,21);
$a_align = array("L","L","L","L","L","L","L","R","R","R","R","R");
$a_align_value = array("R","R","R","L","L","L","L","R","R","R","R","R");

$a_width_end = array(89, 44, 22, 24, 26, 26, 26, 21);
$a_align_end = array("L","R","R", "R","R","R","R","R");

$minElements = 5;
function headerArray($a_splitParams){
    $a_header[0] = array("Cron.","Cron.","Cron.","COD-Utente","TipoPag-Rata","Partita","Pagamento");
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
$cls_help = new cls_help();
$cls_split = new cls_split_payment();
$cls_utils = new cls_Utils();
$cls_coaz = new cls_Coazione();
$cls_stampe = new cls_Stampe();
$cls_date = new cls_DateTimeI("DB",false);

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

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$tipofile = $cls_help->getVar('tipofile');

$query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));//new ente_gestito($c);
$nome_com = $comune->Denominazione;



//PREPARAZIONE ELENCO
$elenco_dir = $cls_utils->crea_dir( ARCHIVIO . "/Targhe_Estere/Pagamenti" );
$data_file = date('Y-m-d_H-i-s');

$prefissoNomeStampa = "elenco_pagamenti_";

$cls_utils->delete_files_after_X_days(PAGAMENTI_ESTERI, $prefissoNomeStampa, 30);

$file_elenco = $elenco_dir . "/" . $prefissoNomeStampa . $data_file . ".pdf";
$download = $file_elenco;

$vedi_file = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($download);

$file_csv = $elenco_dir . "/" . $prefissoNomeStampa . $data_file . ".csv";

if ($tipofile == "CSV")
{
	$aCapo = Chr(13) . Chr(10);
	$myCsv = fopen ($file_csv, "w+");
	$download = $file_csv;
	$vedi_csv = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($download);
}

?>

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


    <div class="row justify-content-md-center " style="margin-bottom: 3%;margin-top: 5%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font18 under_decor">Elenco Pagamenti</span>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
        </div>
    </div>

    <div id="vedi_file"></div>

    <div class="titolo" id="vedi_csv"></div>

<?php

/*$daco  = strtoupper($cls_help->getVar('daco'));
$anom  = strtoupper($cls_help->getVar('anom'));

$dano  = strtoupper($cls_help->getVar('dano'));
$acog  = strtoupper($cls_help->getVar('acog'));*/

$da_n_elenco = $cls_help->getVar('da_n_elenco');
$a_n_elenco = $cls_help->getVar('a_n_elenco');

$da_anno = $cls_help->getVar('da_anno');
$a_anno = $cls_help->getVar('a_anno');

$tipoTributo = $cls_help->getVar('tipoTributo');

$genere_da = $cls_help->getVar('genere_da');
$genere_a = $cls_help->getVar('genere_a');
$dacognome = $cls_help->getVar('daco');
$acognome = $cls_help->getVar('acog');
$danome = $cls_help->getVar('dano');
$anome = $cls_help->getVar('anom');
/*$da_avviso = from_mysql_date($cls_help->getVar('da_avviso'));
$a_avviso = from_mysql_date($cls_help->getVar('a_avviso'));*/

$selecttipopagamento = $cls_help->getVar('selecttipopagamento');
$selectmodalitapagamento = $cls_help->getVar('selectmodalitapagamento');
$contoTerzi = $cls_help->getVar('contoTerzi');

$da_pagamento = $cls_help->getVar('da_pagamento');
$a_pagamento = $cls_help->getVar('a_pagamento');

$da_registrazione = $cls_help->getVar('da_registrazione');
$a_registrazione = $cls_help->getVar('a_registrazione');

$da_infrazione = $cls_help->getVar('da_infrazione');
$a_infrazione = $cls_help->getVar('a_infrazione');

$ordinestampa = $cls_help->getVar('ordinestampa');

$cognome_nome_ditta = $sceltaPartita = $sceltaAnni = $sceltaTipoPagamento = $sceltaTributo = "Tutti";
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

$query = "SELECT U.Ditta, U.Nome, U.Cognome ,pagamento.ID AS PAGID, ".$dataInfrazione." AS DATAINFRAZIONE, tributo.Info_Cartella AS INFOCARTELLA FROM pagamento ";
$query.= "JOIN partita_tributi ON pagamento.Partita_ID = partita_tributi.ID ";
$query.= "JOIN tributo ON tributo.Partita_ID = partita_tributi.ID ";
$query.= "LEFT JOIN utente AS U ON U.ID = partita_tributi.Utente_ID ";
$query.= "WHERE pagamento.CC = \"".$c."\" AND pagamento.Atto_ID != 0 ";

if($da_infrazione!="" && count($tipoTributo)==1 && $tipoTributo[0] == "CDS-"){

    $queryDataInfrazione = "AND ".$dataInfrazione." >= '" . $cls_date->GetDateDB($da_infrazione,"IT") . "' AND ";
    $queryDataInfrazione.= $dataInfrazione." <= '" . $cls_date->GetDateDB($a_infrazione,"IT") . "' ";
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

if ($da_n_elenco != NULL && $a_n_elenco != NULL){
    $query.= "AND partita_tributi.Comune_ID >= '$da_n_elenco' AND partita_tributi.Comune_ID <= '$a_n_elenco' ";
	$sceltaPartita = "Da $da_n_elenco a $a_n_elenco";
}else if($da_n_elenco != NULL){
    $query.= "AND partita_tributi.Comune_ID >= '$da_n_elenco' ";
    $sceltaPartita = "Da $da_n_elenco ";
}else if($a_n_elenco != NULL){
    $query.= "AND partita_tributi.Comune_ID <= '$a_n_elenco' ";
    $sceltaPartita = "A $a_n_elenco";
}

if ($da_anno != NULL && $a_anno != NULL){
    $query.= "AND partita_tributi.Anno_Riferimento >= $da_anno AND partita_tributi.Anno_Riferimento <= $a_anno ";
	$sceltaAnni = "Da $da_anno a $a_anno";
}else if($da_anno != NULL){
    $query.= "AND partita_tributi.Anno_Riferimento >= $da_anno ";
    $sceltaAnni = "Da $da_anno ";
}else if($a_anno != NULL){
    $query.= "AND partita_tributi.Anno_Riferimento <= $a_anno ";
    $sceltaAnni = "A $a_anno";
}

if ($genere_da != "D" && $genere_a != "D") {
    if ($dacognome != null) {

        $query .= " AND ( ( U.Cognome > '" . addslashes($dacognome) . "' ) ";
        $query .= "AND ( U.Cognome < '" . addslashes($acognome) . "' ) ";
        $query .= "OR ( U.Cognome = '" . addslashes($dacognome) . "' ";
        if ($danome != null) {
            $query .= "AND U.Nome >= '" . addslashes($danome) . "' ";
        }

        $query .= ") OR ( U.Cognome = '" . addslashes($acognome) . "' ";
        if ($anome != null) {
            $query .= "AND U.Nome <= '" . addslashes($anome) . "' ";
        }
        $query .= ") ) ";

        $cognome_nome_ditta = "Da cognome/nome ".addslashes($dacognome)." ".addslashes($danome)." a cognome/nome ".addslashes($acognome)." ".addslashes($anome);
    }

} else if($genere_da == "D" && $genere_a == "D") {
    if ($dacognome != null)
        $query .= " AND ( U.Ditta >= '" . addslashes($dacognome) . "' ";
    if ($acognome != null) $query .= " AND U.Ditta <= '" . addslashes($acognome) . "' ) ";
    else $query .= ") ";

    $cognome_nome_ditta = "Da ditta " . addslashes($dacognome) . " a ditta " . addslashes($acognome);
}
else{
    if(($genere_da == "D" || $genere_da == "M" || $genere_da == "F") && $genere_a == null){
        if($genere_da == "D") {
            $query .= " AND U.Ditta >= '" . addslashes($dacognome) . "' ";
            $cognome_nome_ditta = "Da ditta " . addslashes($dacognome);
        }
        else {
            $query .= " AND U.Cognome >= '" . addslashes($dacognome) . "' ";
            $cognome_nome_ditta = "Da cognome " . addslashes($dacognome);
        }
    }
    else if(($genere_a == "D" || $genere_a == "M" || $genere_a == "F") && $genere_da == null){
        if($genere_a == "D") {
            $query .= " AND U.Ditta <= '" . addslashes($acognome) . "' ";
            $cognome_nome_ditta = "A ditta " . addslashes($acognome);
        }
        else {
            $query .= " AND U.Cognome <= '" . addslashes($acognome) . "' ";
            $cognome_nome_ditta = "A cognome " . addslashes($acognome);
        }
    }
    else {
        $cls_help->alert("Non selezionare ditte e utenti insieme nella selezione utenti!! (Da Cognome/Nome - A Cognome/Nome). Selezionare entrambe imprese o entrambi utenti!");
        flush();	ob_flush();
        echo "<script>endBar();</script>";
        flush();	ob_flush();		flush();	ob_flush();
        die;
    }
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
    $queryDataPag = "AND pagamento.Data_Pagamento >= '" . $cls_date->GetDateDB($da_pagamento,"IT") . "' AND ";
    $queryDataPag.= "pagamento.Data_Pagamento <= '" . $cls_date->GetDateDB($a_pagamento,"IT") . "' ";
    $query.= $queryDataPag;
    $queryDaAss.= $queryDataPag;
	$sceltaDataPagamento = "Da " . $cls_help->getVar('da_pagamento') . " a " . $cls_help->getVar('a_pagamento');
}

if ($da_registrazione != NULL){
    $queryDataReg = "AND pagamento.Data_Registrazione >= '" . $cls_date->GetDateDB($da_registrazione,"IT") . "' AND ";
    $queryDataReg.= "pagamento.Data_Registrazione <= '" . $cls_date->GetDateDB($a_registrazione,"IT") . "' ";
    $query.= $queryDataReg;
    $queryDaAss.= $queryDataReg;
	$sceltaDataRegistrazione = "Da " . $cls_help->getVar('da_registrazione') . " a " . $cls_help->getVar('a_registrazione');
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
//echo $query."<br><br><br>";
//echo $queryDaAss;
//die;
$a_Pagamenti = $cls_db->getResults( $cls_db->SelectQuery( $query ) );
$arrayDaAssPagamenti = $cls_db->getResults( $cls_db->SelectQuery( $queryDaAss ) );

$num_totale_pagamenti = count($a_Pagamenti) + count($arrayDaAssPagamenti);

$arrayPagamenti = array_merge($a_Pagamenti,$arrayDaAssPagamenti);

if ($_SESSION['CC_User'] == "***+")
	echo "<br>" . $query . " --> " . count($arrayPagamenti);
//die;
//if ($_SESSION['CC_User'] == "***+")
//	echo "<br>" . $queryDaAss . " --> " . count($arrayDaAssPagamenti);

$fontPrimo = 8.5;
$fontSecondo = 8;
$tot_gen_pagato = 0;
/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/

    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf->setPrintHeader(false);
	$pdf->SetMargins(10, 10, 10);
	
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');
	
	$pdf->AddPage("L"); //AddMyPage(1, 'L');
	$pdf->SetFont('Arial', 'B', $fontPrimo);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();  //  297
	$altezza_pag = $pdf->getPageHeight();	
	
	$pdf->SetAutoPageBreak(false);
	$pdf->Ln(5);

/**
 *                      ARRIVATO QUI!!!!!!!!!!!!!!!
 */
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

        $query = "SELECT * FROM pagamento WHERE ID = '".$arrayPagamenti[$l]['PAGID']."' AND CC = '".$c."'";
		$myPagamento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamento");
        //$myPagamento = new pagamento($arrayPagamenti[$l]['PAGID'], $c);
		$info_cartella = $arrayPagamenti[$l]['INFOCARTELLA'];
        $data_infrazione = $arrayPagamenti[$l]['DATAINFRAZIONE'];
		$id_ingiunzione = "";
		$anno_ingiunzione = "";
		$id_pigno = "";
		$anno_pigno = "";
		if(strpos($myPagamento->Tipo_Atto,'Pignoramento')===false)
		{
            $query = "SELECT * FROM atto WHERE ID = ".$myPagamento->Atto_ID." AND CC = '".$myPagamento->CC."'";
			$myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($myPagamento->Atto_ID, $myPagamento->CC);
            $query = "SELECT * FROM pignoramento_generale WHERE 1=2";
            $mioPigno = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");//new pignoramento(null, $myPagamento->CC);

            $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = '".$mioPigno->ID."' AND CC = '".$c."'";
            $mioPigno->Spese_Pignoramento = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));
            $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$mioPigno->ID."' AND Partita_ID = '".$mioPigno->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
            $mioPigno->Pagamento = $cls_db->getResults($cls_db->ExecuteQuery($query),"object");

			$id_ingiunzione = $myAtto->ID_Cronologico;
			$anno_ingiunzione = $myAtto->Anno_Cronologico;

		}
		else
		{
            $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$myPagamento->Atto_ID." AND CC = '".$myPagamento->CC."'";
			$mioPigno = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");//new pignoramento($myPagamento->Atto_ID, $myPagamento->CC);

            $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$mioPigno->ID." AND CC = '".$c."'";
            $mioPigno->Spese_Pignoramento = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));
            $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$mioPigno->ID."' AND Partita_ID = '".$mioPigno->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
            $mioPigno->Pagamento = $cls_db->getResults($cls_db->ExecuteQuery($query),"object");

            $id_pigno = $mioPigno->ID_Cronologico;
			$anno_pigno = $mioPigno->Anno_Cronologico;
            $query = "SELECT * FROM atto WHERE ID = ".$mioPigno->Atto_ID." AND CC = '".$myPagamento->CC."'";
			$myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($mioPigno->Atto_ID, $myPagamento->CC);
			$id_ingiunzione = $myAtto->ID_Cronologico;
			$anno_ingiunzione = $myAtto->Anno_Cronologico;
		}

        //$query = "SELECT * FROM partita_tributi WHERE ID = '".$myPagamento->Partita_ID."' AND CC = '".$c."'";
        //$query = "SELECT ID FROM partita_tributi WHERE Utente_ID = '".$p."' AND CC = '".$c."'";
		$myPartita = $cls_stampe->getDataPartita($myPagamento->Partita_ID,$c);// $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");//new partita($myPagamento->Partita_ID, $c);
        //var_dump($myPartita);
        if (!IsInArray($arrayPartite, $myPartita->ID))
            $arrayPartite[] = $myPartita->ID;

        //$myPartita = new partita($myPagamento->Partita_ID, $c);
        //echo "<br>" . $myPagamento->Atto_ID;

        /** FARSI SPIEGARE CHE VUOL DIRE, SE SERVE E AL LIMITE DECOMMENTARE*/
        /*if ($myPagamento->Pagante == "")
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
        }*/
        /** ***********FINO A QUI*************** */

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

        if($myPagamento->Pagante != ""){
            $pagante .= " (".$myPagamento->Pagante.")";
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

                /**         $a_splitPartial CHE VALORE DOVREBBE AVERE?              **/
                //PARZIALI
                $a_end = endArray($a_splitPartial, $a_splitParams);

                $pdf->SetFont('Arial', 'B', $fontSecondo);
                $pdf->setCellPaddings(2,1,2,0);
                $y = $pdf->setRow($a_end[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y = $pdf->setRow($a_end[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y = $pdf->setRow($a_end[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
                    //$y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
                }

                /**                 QUI COSA CI VA? --> $a_splitTotal                    **/
                //TOTALI
                $a_final = finalArray($a_splitTotal, $a_splitParams);

                $pdf->SetFont('Arial', 'B', $fontSecondo);
                $pdf->setCellPaddings(2,1,2,0);
                $y = $pdf->setRow($a_final[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_final[0], "up" , $styleRetta, $a_align_end);

                if(count($a_final[2])==count($a_width_end)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y = $pdf->setRow($a_final[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_final[1], $line , $styleRetta, $a_align_end);

                if(count($a_final[2])==count($a_width_end)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y = $pdf->setRow($a_final[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
                    //$y = crea_riga($pdf , $a_width_end, $a_final[2], "down" , $styleRetta, $a_align_end);
                }

                $pdf->AddPage('L');
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
                $y1_vert = $pdf->setRow($a_header[0],"up",$styleRetta,$a_align,0,$a_width);
                //$y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

                if(count($a_header[2])==count($a_width)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y1_vert = $pdf->setRow($a_header[1],$line,$styleRetta,$a_align,0,$a_width);
                //$y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

                if(count($a_header[2])==count($a_width)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y1_vert = $pdf->setRow($a_header[2],"down",$styleRetta,$a_align,0,$a_width);
                    //$y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
                }
            }
        }

        $changeParams = $a_params['id'];
        $tot_gen_pagato+= $myPagamento->Importo;
        $parzPagato+= $myPagamento->Importo;
        $totalePagato+= $myPagamento->Importo;

        $sum = 0;
        for($i=1;$i<=16;$i++){
            $key = "Split_Payment".$i;
            $sum+= $myPagamento->$key;
        }

		if($sum>0)
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
            $a_splitRequest = $cls_stampe->splitPayment($myPartita,$myAtto,$mioPigno,$a_splitParams,$a_params['id'],$myPagamento->CC,$myPagamento);
            for($i=0;$i<count($a_splitParams);$i++){
                if(isset($a_splitRequest[$a_splitParams[$i]['split_number']])){
                    $a_splitPartial[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                    $a_splitTotal[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                }
            }
		}
		
		$accertOrig = $cls_stampe->estraiTitoloTributo($myPartita);
        $num_rate = "";
        if($myPagamento->Totale_Rate>0)
            $num_rate = "/".$myPagamento->Totale_Rate;

        $cls_date->changeFormat("IT",false);

        $a_value[0] = array($accertOrig,$id_ingiunzione,$id_pigno,"(".$myPartita->Utente->Comune_ID.") ".$pagante,$myPagamento->Modalita . " - " . $numeroRata.$num_rate,$myPartita->Comune_ID,number_format($myPagamento->Importo,2,",","."));
        $a_value[1] = array($myPartita->Anno_Riferimento,$anno_ingiunzione,$anno_pigno,strtoupper($CF_PI),$quietanza,$stato_pagamento,$cls_date->Get_DateNewFormat($myPagamento->Data_Pagamento,"DB"));
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
            $y = $pdf->setRow($a_value[0],$ctrl_linea,$styleRetta,$a_align_value,0,$a_width);
            //$y = crea_riga($pdf , $a_width, $a_value[0], $ctrl_linea , $styleDash, $a_align_value);
            if($ctrl_linea == "no")	$ctrl_linea = "up";

            if(count($a_value[2])==count($a_width)){
                $padding = 0;
            }
            else{
                $padding = 2;
            }

            $pdf->setCellPaddings(2,0,2,$padding);
            $y = $pdf->setRow($a_value[1],"no",$styleDash,$a_align_value,0,$a_width);
            //$y = crea_riga($pdf , $a_width, $a_value[1], "no" , $styleDash, $a_align_value);

            if(count($a_value[2])==count($a_width)){
                $pdf->setCellPaddings(2,0,2,2);
                $y = $pdf->setRow($a_value[2],"no",$styleDash,$a_align_value,0,$a_width);
                //$y = crea_riga($pdf , $a_width, $a_value[2], "no" , $styleDash, $a_align_value);
            }

            if( $y > $altezza_pag - 40)
            {
                //$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );

                $y2_vert = $pdf->getY();
                $margine = $pdf->getMargins();
                $x = $margine['left'];
                for($k=0 ; $k < count($a_width)-1 ; $k++ )
                {
                    $x += $a_width[$k];
                    $pdf->Line( $x , $y1_vert , $x , $y2_vert , $styleDash );
                }
                //crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

                $a_end = endArray($parzPagato, $a_splitPartial, $a_splitParams);

                $pdf->SetFont('Arial', 'B', $fontSecondo);
                $pdf->setCellPaddings(2,1,2,0);
                $y = $pdf->setRow($a_end[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y = $pdf->setRow($a_end[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);

                if(count($a_end[2])==count($a_width_end)){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y = $pdf->setRow($a_end[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
                    //$y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
                }

                for($i=0;$i<count($a_splitParams);$i++) {
                    $a_splitPartial[$i] = 0;
                }
                $parzPagato = 0;

                $pdf->AddPage( 'L');
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', $fontPrimo);

                $pdf->setCellPaddings(2,1,2,0);
                $y1_vert = $pdf->setRow($a_header[0],"up",$styleRetta,$a_align,0,$a_width);
                //$y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

                if($a_header[2]>$minElements){
                    $padding = 0;
                    $line = "no";
                }
                else{
                    $padding = 1;
                    $line = "down";
                }

                $pdf->setCellPaddings(2,0,2,$padding);
                $y1_vert = $pdf->setRow($a_header[1],$line,$styleRetta,$a_align,0,$a_width);
                //$y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

                if($a_header[2]>$minElements){
                    $pdf->setCellPaddings(2,0,2,1);
                    $y1_vert = $pdf->setRow($a_header[2],"down",$styleRetta,$a_align,0,$a_width);
                    //$y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
                }

                $ctrl_linea = "no";
            }
        }

		$cont_pag_result++;
			
	}//CHIUSURA PAGAMENTI ASSOCIATI

    if($num_totale_pagamenti>0 && $tipofile == "PDF"){
        $y2_vert = $pdf->getY();
        $margine = $pdf->getMargins();
        $x = $margine['left'];
        for($k=0 ; $k < count($a_width)-1 ; $k++ )
        {
            $x += $a_width[$k];
            $pdf->Line( $x , $y1_vert , $x , $y2_vert , $styleDash );
        }
        //crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

        $a_end = endArray($parzPagato, $a_splitPartial, $a_splitParams);

        $pdf->SetFont('Arial', 'B', $fontSecondo);
        $pdf->setCellPaddings(2,1,2,0);
        //$y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);
        $y = $pdf->setRow($a_end[0],"up",$styleRetta,$a_align_end,0,$a_width_end);

        if(count($a_end[2])==count($a_width_end)){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        //$y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);
        $y = $pdf->setRow($a_end[1],$line,$styleRetta,$a_align_end,0,$a_width_end);

        if(count($a_end[2])==count($a_width_end)){
            $pdf->setCellPaddings(2,0,2,1);
            //$y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
            $y = $pdf->setRow($a_end[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
        }

        if( $y > $altezza_pag - 40)
        {
            $pdf->AddPage('L');
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', $fontPrimo);
            $pdf->setCellPaddings(2,1,2,0);
            $y1_vert = $pdf->setRow($a_header[0],"up",$styleRetta,$a_align,0,$a_width);
            //$y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

            if($a_header[2]>$minElements){
                $padding = 0;
                $line = "no";
            }
            else{
                $padding = 1;
                $line = "down";
            }

            $pdf->setCellPaddings(2,0,2,$padding);
            $y1_vert = $pdf->setRow($a_header[1],$line,$styleRetta,$a_align,0,$a_width);
            //$y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

            if($a_header[2]>$minElements){
                $pdf->setCellPaddings(2,0,2,1);
                $y1_vert = $pdf->setRow($a_header[2],"down",$styleRetta,$a_align,0,$a_width);
                //$y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
            }

            $ctrl_linea = "no";
        }

        //TOTALI
        $a_final = finalArray($totalePagato, $a_splitTotal, $a_splitParams);

        $pdf->SetFont('Arial', 'B', $fontSecondo);
        $pdf->setCellPaddings(2,1,2,0);
        $y = $pdf->setRow($a_final[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
        //$y = crea_riga($pdf , $a_width_end, $a_final[0], "up" , $styleRetta, $a_align_end);

        if(count($a_final[2])==count($a_width_end)){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        $y = $pdf->setRow($a_final[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
        //$y = crea_riga($pdf , $a_width_end, $a_final[1], $line , $styleRetta, $a_align_end);

        if(count($a_final[2])==count($a_width_end)){
            $pdf->setCellPaddings(2,0,2,1);
            $y = $pdf->setRow($a_final[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
            //$y = crea_riga($pdf , $a_width_end, $a_final[2], "down" , $styleRetta, $a_align_end);
        }


        $pdf->setPrintHeader(false);
        $pdf->AddPage('L');
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
        $pdf->Cell (80, 0, "COGNOME/DITTA NOME:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-70 , 5, $cognome_nome_ditta , 0, 1, "L");

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
        $pdf->Cell ( 40 , 5, number_format($tot_gen_pagato,2,",","."), 0, 1, "R");

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