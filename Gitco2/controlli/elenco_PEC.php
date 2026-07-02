<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC . "/headerAjax.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_pdf.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_DateTimeInLine.php");

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("IT",false);

class MYPDF extends TCPDF {
	
	public function Header() {
		
		$this->SetFont('Arial', 'B', 11);
		$this->ln(5);
		$this->Cell(0, 5, "Elenco Ricevute PEC" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage() + 1) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");//new ente_gestito($c);
$nome_com = $comune->Denominazione;

//PREPARAZIONE ELENCO
$elenco_dir = $cls_utils->crea_dir( $_SERVER['DOCUMENT_ROOT']."/archivio/atti/". $c . "/RicevutePec/Elenchi" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_pec_".$data_file.".pdf";
$download = $file_elenco;

$vedi_file = $cls_utils->mostra_file_path($download);

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

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);

	mostra_file();
}

function mostra_file()
{
	window.name = "Stampa";
	window.open('<?php echo $vedi_file; ?>',"Stampa");
}

</script>

<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Elenco Ricevute PEC</span>
    </div>
</div>
<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>

<?php

//COGNOME NOME
$daco  = strtoupper($cls_help->getVar('daco'));
$acog  = strtoupper($cls_help->getVar('acog'));
$dano  = strtoupper($cls_help->getVar('dano'));
$anom  = strtoupper($cls_help->getVar('anom'));

//PARTITA
$da_partita  = $cls_help->getVar('da_n_elenco');
$a_partita  = $cls_help->getVar('a_n_elenco');

//ANNI RIFERIMENTO
$da_anno = $cls_help->getVar('da_anno');
$ad_anno = $cls_help->getVar('ad_anno');

$da_n_elenco = $cls_help->getVar('da_n_elenco');
$a_n_elenco = $cls_help->getVar('a_n_elenco');

$tipo_partita = $cls_help->getVar('tipo_partita');

$accettazione = $cls_help->getVar('accettazione');
$consegna = $cls_help->getVar('consegna');

//ORDINAMENTO
$ordinamento = $cls_help->getVar('ordinamento');

flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();
sleep(2);

$query = "SELECT EMAIL.*, PAR.Comune_ID AS noPartita, PAR.Tipo, PAR.Sottotipo, PAR.Anno_Riferimento, PAR.Info_Cartella, UTENTE.Comune_ID AS noUtente, ";
$query.= "UTENTE.Denominazione, UTENTE.Nome, UTENTE.CFPI, PIG.Anno_Cronologico, PIG.ID_Cronologico, PIG.Tipo AS TipoPigno, PIG.Tipo_Terzi, ";
$query.= "PIG.Data_Stampa, PIG.Data_Flusso, PIG.Anno_Flusso, PIG.Numero_Flusso, ";
$query.= "BAN.Denominazione AS NomeBanca, BAN.PEC AS Mail_BANCA, BAN.Comune AS Comune_BANCA, UTENTE.Comune_IVG AS Comune_IVG, UTENTE.Mail_IVG AS Mail_IVG, UTENTE.Denominazione_IVG AS Denominazione_IVG ";
$query.= "FROM email_inviate AS EMAIL JOIN v_utente_res AS UTENTE ON UTENTE.ID = EMAIL.Utente_ID ";
if($daco!="" && $acog!=""){
    $query.= "AND ( ( UTENTE.Denominazione > '".addslashes($daco)."' ) ";
    $query.= "AND ( UTENTE.Denominazione < '".addslashes($acog)."' ) ";

    $query.= "OR ( UTENTE.Denominazione = '".addslashes($daco)."' ";
    if($dano != null)
        $query.= "AND UTENTE.Nome >= '".addslashes($dano)."' ";

    $query.= ") OR ( UTENTE.Denominazione = '".addslashes($acog)."' ";
    if($anom != null)
        $query.= "AND UTENTE.Nome <= '".addslashes($anom)."' ";
    $query.= ") ) ";
}

$query.= "JOIN view_partitainfo AS PAR ON PAR.ID = EMAIL.Partita_ID ";
if( $da_anno != null && $ad_anno != null )
    $query.= "AND PAR.Anno_Riferimento >= '".$da_anno."' AND PAR.Anno_Riferimento <= '".$ad_anno."' ";
if($tipo_partita!="")
    $query.= "AND PAR.Tipo = '".$tipo_partita."' ";
if($da_n_elenco>0)
    $query.= "AND PAR.Comune_ID >= '".$da_n_elenco."' ";
if($a_n_elenco>0)
    $query.= "AND PAR.Comune_ID <= '".$a_n_elenco."' ";

$query.= "JOIN notifica_atto AS NOTIF ON NOTIF.ID = EMAIL.ID_Collegato AND NOTIF.Tipo_Atto_Notificato = 'pignoramento' AND EMAIL.Table_Collegata='notifica_atto' AND NOTIF.Modalita_Stampa='pec' ";
$query.= "JOIN pignoramento_generale AS PIG ON PIG.ID = NOTIF.Atto_Notificato_ID ";
$query.= "LEFT JOIN pignoramento_presso_terzi AS TER ON TER.ID = NOTIF.ID_Collegamento ";
$query.= "LEFT JOIN banca AS BAN ON TER.Terzo_ID = BAN.ID ";
$query.= "WHERE EMAIL.CC='".$c."' AND EMAIL.Tipo_Sorgente = 'PEC' ";

if($accettazione!="")
    $query.= "AND EMAIL.Ricevuta_Accettazione='".$accettazione."' ";
if($consegna!="")
    $query.= "AND EMAIL.Ricevuta_Consegna='".$consegna."' ";
$query.= "GROUP BY EMAIL.ID ";
$query.= "ORDER BY ";

switch($ordinamento){
    case 'partita': $query.= "PAR.Comune_ID ASC, EMAIL.ID ASC ";
        break;
    case 'utente':  $query.= "UTENTE.Denominazione ASC, UTENTE.Nome ASC, PAR.Comune_ID ASC, EMAIL.ID ASC ";
        break;
    default:        $query.= "PAR.Comune_ID ASC, EMAIL.ID ASC ";
        break;
}

/*echo $query;
flush();	ob_flush();
die;*/
$a_PEC = $cls_db->getResults($cls_db->ExecuteQuery($query));//mysql_array($query);

/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/
	$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf->setPrintHeader(false);
	$pdf->SetMargins(10, 10, 10);	
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');
	
	$pdf->AddPage('P');
	$pdf->SetFont('Arial', 'B', 10);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();
	$altezza_pag = $pdf->getPageHeight();	

	$pdf->SetAutoPageBreak(false);
	$pdf->Ln(5);

    $widths = array(10,20,16,54);
    $a_width = array();
    for($i=0;$i<count($widths); $i++)
        $a_width[$i] = number_format($widths[$i]*($larghezza_pag-20)/100,0);

    $widths2 = array(30,36,17,17);
    $a_width2 = array();
    for($i=0;$i<count($widths2); $i++)
        $a_width2[$i] = number_format($widths2[$i]*($larghezza_pag-20)/100,0);


	$a_header_1 = array('Partita', 'Utente', 'CF/PI', 'Informazioni');
    $a_header_2 = array('Crono.', 'Pignoramento', 'Data Stampa', 'Flusso');

	$pdf->setCellPaddings(1,1,1,0);
    $y = $pdf->setRow($a_header_1,"up",$styleRetta,null,0,$a_width);// crea_riga($pdf , $a_width, $a_header_1, "up" , $styleRetta);
	
	$pdf->setCellPaddings(1,0,1,1);
	$y = $pdf->setRow($a_header_2,"down",$styleRetta,null,0,$a_width);//crea_riga($pdf , $a_width, $a_header_2, "down" , $styleRetta);
	
/**
	//////////////////////////////////////////////////////////////////////////////
*/
		
	$cont_result = 0;
    $temp = "";
	$ctrl_linea = "no";
    echo "<h1>Count --> </h1>".count($a_PEC);
	for( $l=0; $l < count($a_PEC); $l++ )//FOR PEC
	{
        $row = $a_PEC[$l];
		set_time_limit(100);
		echo "<script>update(".ceil($l*100/count($a_PEC)).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();

        $pdf->SetFont('Arial', '', 7.5);

        if($temp!=$row['noPartita'] || $temp==""){

            $y1 = $pdf->getY();

            $utenteStr = "(".$row['noUtente'].") ".$row['Denominazione']." ".$row['Nome'];
            if(strlen($utenteStr)>23)
                $utente = substr($utenteStr,0,22)."...";
            else
                $utente = $utenteStr;
            $a_data_1 = array(
                    $row['noPartita']."/".$row['Anno_Riferimento'],
                    $utente,
                    $row['CFPI'],
                    $row['Info_Cartella']
                );
            if($row['Numero_Flusso']>0)
                $flusso = $row['Numero_Flusso']."/".$row['Anno_Flusso']." del ".$cls_date->Get_DateNewFormat($row['Data_Flusso'],"DB");//from_mysql_date($row['Data_Flusso']);
            else
                $flusso = "Assente";

            $a_data_2 = array(
                $row['ID_Cronologico']."/".$row['Anno_Cronologico'],
                "Pignoramento ".$row['TipoPigno']." ".$row['Tipo_Terzi'],
                $cls_date->Get_DateNewFormat($row['Data_Stampa'],"DB")/*from_mysql_date($row['Data_Stampa'])*/,
                $flusso
            );

            $a_align_1 = array("L","L","L","L");

            $pdf->setCellPaddings(1,2,1,0);
            $pdf->setRow($a_data_1,"up",$styleRetta,$a_align_1,0,$a_width);//crea_riga($pdf , $a_width, $a_data_1 , "up" , $styleRetta , $a_align_1 );
            $pdf->setCellPaddings(1,0,1,2);
            $y2 = $pdf->setRow($a_data_2,"down",$styleRetta,$a_align_1,0,$a_width);//crea_riga($pdf , $a_width, $a_data_2 , "down" , $styleRetta, $a_align_1 );

            $pdf->addLines();// crea_linee ($pdf, $a_width, $y1 , $y2, $styleRetta);
        }

            $linePEC = "down";

        switch($row['Ricevuta_Accettazione']){
            case "ok":
                $accettazione = "Accettazione Ricevuta";
                break;
            case "attesa":
                $accettazione = "Accettazione In Attesa";
                break;
            case "mancata":
                $accettazione = "Accettazione Mancata";
                break;
            case "fallita":
                $accettazione = "Accettazione Fallita";
                break;
        }

        switch($row['Ricevuta_Consegna']){
            case "ok":
                $consegna = "Consegna Ricevuta";
                break;
            case "attesa":
                $consegna = "Consegna In Attesa";
                break;
            case "mancata":
                $consegna = "Consegna Mancata";
                break;
            case "anomalia":
                $consegna = "Anomalia Consegna";
                break;
            case "fallita":
                $consegna = "Consegna Fallita";
                break;
        }


        $a_pec = array('','','','');
        switch($row['TipoPigno']){
            case 'terzi':
                $a_pec = array($row['NomeBanca'], $row['Mail_Destinatario'], $accettazione, $consegna );
                break;
            case 'veicolo':
                $a_pec = array($row['Denominazione_IVG'], $row['Mail_Destinatario'], $accettazione, $consegna);
                break;
        }



        $pdf->setCellPaddings(1,1,1,1);
        $y = $pdf->setRow($a_pec,$linePEC,$styleDash,$a_align_1,0,$a_width2);// crea_riga($pdf , $a_width2, $a_pec , $linePEC , $styleDash, $a_align_1 );

        $y1 = $pdf->getY();

        if( $y1 > $altezza_pag - 30)
        {
            $pdf->AddPage('P');
            $pdf->Ln(5);

            $pdf->SetFont('Arial', 'B', 10);

            $pdf->setCellPaddings(1,1,1,0);
            $y = $pdf->setRow($a_header_1,"up",$styleRetta,null,0,$a_width);//crea_riga($pdf , $a_width, $a_header_1, "up" , $styleRetta);

            $pdf->setCellPaddings(1,0,1,1);
            $y = $pdf->setRow($a_header_2,"down",$styleRetta,null,0,$a_width);//crea_riga($pdf , $a_width, $a_header_2, "down" , $styleRetta);
        }

        $temp = $row['noPartita'];

        
        $cont_result++;
			
	}
	
	$pdf->setPrintHeader(false);
	$pdf->addPage();	
	$pdf->setPrintFooter(false);
	
	if($daco != "")
		$sel_utente = "Da ".$daco." ".$dano." a ".$acog." ".$anom;
	else
		$sel_utente = "Nessun filtro";
	
	if($da_partita != "")
		$sel_partita = "Dalla partita contabile numero ".$da_partita." alla partita contabile numero ".$a_partita;
	else
		$sel_partita = "Nessun filtro";
	
	
	$pdf->setCellPaddings(2,0,2,1);
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 18);
	$pdf->Cell(0, 0, "COMUNE DI ".strtoupper($nome_com) , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->SetFont('Arial', '', 16);
	$pdf->Cell(0, 0, "ELENCO RICEVUTE PEC" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->ln(10);
	
	$y = $pdf->getY();
	
	function tablePdf($label, $text){
		$stringa = "<tr>";
		$stringa.= "<td class='text_left'>".strtoupper($label).":</td>";
		$stringa.= "<td class='text_left'><i>".$text."</i></td>";
		$stringa.= "</tr>";
		
		return $stringa;
	}
	
	$pdf->SetFont('Arial', '', 12);
	$left_column = "<h3><b>SELEZIONI</b></h3><br><table>";
	$left_column.= tablePdf("UTENTE",$sel_utente);
	$left_column.= tablePdf("PARTITA",$sel_partita);
	$left_column.= "</table>";
	$left_column.= "<br><h3><b>RIEPILOGO</b></h3><br><table>";
	$left_column.= tablePdf("NUMERO PAGINE",$pdf->PageNo());
	$left_column.= tablePdf("NUMERO PEC",$cont_result);
	$left_column.= "</table>";

    $right_column = "";
	
	$pdf->writeHTMLCell(150, '', '', $y, $left_column, 0, 0, 0, true, 'J', true);
	$pdf->writeHTMLCell(130, '', '', '', $right_column, 0, 1, 0, true, 'J', true);
	
	$pdf->movePage($pdf->PageNo(), 1);
	
	$pdf->Output( $file_elenco , 'F');
	
	
	if($cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>
<?php include(INC."/footer.php"); ?>
