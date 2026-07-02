<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once INC . "/header.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_pdf.php";

$cls_utils = new cls_Utils();
$cls_elab = new cls_elaborazioniUtils();

$elenco_dir = $cls_utils->crea_dir( ATTI ."/RicalcoloInteressi" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_ricalcolo_interessi_".$data_file.".pdf";

$vedi_file = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($file_elenco);

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
//$pdf->setPrintHeader(false);
$pdf->SetMargins(10, 10, 10);
$pdf->setCellPaddings(2,1,2,1);

$styleDash = array('dash' => '6,6');
$styleRetta = array('dash' => '0');

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 11);

$pdf->SetAutoPageBreak(false);
$pdf->Ln(10);

$array_width = array();
$array_intestaz = array();

$array_width[] = 20;	$array_intestaz[] = "CC";
$array_width[] = 20;	$array_intestaz[] = "Partita";
$array_width[] = 70;	$array_intestaz[] = "Informazioni";
$array_width[] = 24;	$array_intestaz[] = "Dovuto";
$array_width[] = 60;	$array_intestaz[] = "Status";

$y1_vert = $pdf->setRow($array_intestaz,"up_down",$styleRetta,null,0,$array_width);

$query = 'SELECT PA.Flag_Blocco_Diritto_Riscossione, PA.Tipo as Tipo_Riscossione, PA.Comune_ID AS Partita_ID_Comune, A.* FROM atto A JOIN partita_tributi PA ON PA.ID=A.Partita_ID ';
$query.= 'WHERE PA.Tipo!="CDS" AND A.DocumentTypeId=2 AND A.Data_Elaborazione>="2022-01-01" ';
$query.= 'ORDER BY A.CC ASC, A.FlowId ASC, A.Anno_Cronologico ASC, A.ID_Cronologico ASC, A.Data_Elaborazione ASC';

$a_atti = $cls_db->getResults($cls_db->ExecuteQuery($query));

$query = "SELECT * FROM lockup_periods WHERE CC='*****' AND Lockup_Type_Id<=3 ORDER BY Start_Date ASC";
$a_blockPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");//new ente_gestito($c);
$cont = 0;
$contFlusso = 0;
$contNostampa = 0;
$contStampa = 0;
foreach($a_atti as $a_atto){

    $query = "SELECT * FROM enti_gestiti WHERE CC = '".$a_atto['CC']."'";
    $enteGestito = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new ente_gestito($c);

    $query = "SELECT * FROM parametri_annuali WHERE CC = '".$a_atto['CC']."' AND Anno = '2022' AND Tipo_Riscossione = '*****'";
    $parametri = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

    $query = "SELECT * FROM interessi_tributi WHERE CC = '".$a_atto['CC']."' ORDER BY Data_Inizio ASC";
    $a_interessiTributi = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");//new interessi_tributi($c);

    $a_codiciTrib = array("TOTALE"=>0,"IMPORTO_INTERESSI"=>0,"PAGAMENTO"=>0,"SPESE_INGIUNZIONE"=>0);
    $a_tributi = $cls_db->getResults($cls_db->ExecuteQuery("SELECT CT.Tipo_Codice, T.* FROM tributo T JOIN codice_tributo CT ON CT.Codice_Tributo=T.Codice_Tributo WHERE Partita_ID=".$a_atto['Partita_ID']));
    foreach($a_tributi as $a_tributo)
    {
        if($a_tributo['Tipo_Codice']=="PAGAMENTO"){
            $a_codiciTrib["PAGAMENTO"] += $a_tributo['Imposta'];
            $a_codiciTrib["TOTALE"] -= $a_tributo['Imposta'];
        }
        else{
            $a_codiciTrib["TOTALE"] += $a_tributo['Imposta'];

            if($a_atto['Tipo_Riscossione']=="CDS" && $a_tributo['Tipo_Codice']!="INTERESSI")
                $a_codiciTrib["IMPORTO_INTERESSI"] += $a_tributo['Imposta'];
            else if($a_tributo['Tipo_Codice']=="IMPORTO")
                $a_codiciTrib["IMPORTO_INTERESSI"] += $a_tributo['Imposta'];

            if($a_tributo['Codice_Tributo']=="S_03")
                $a_codiciTrib["SPESE_INGIUNZIONE"] += $a_tributo['Imposta'];
        }
    }
    if($a_codiciTrib["TOTALE"]<$a_codiciTrib["IMPORTO_INTERESSI"])
        $a_codiciTrib["IMPORTO_INTERESSI"] = $a_codiciTrib["TOTALE"];

    $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento ";
    $query.= "WHERE Atto_ID < ".$a_atto['ID']." AND Partita_ID = ".$a_atto['Partita_ID']." ";
    $query.= "AND DocumentTableTypeId!=2 GROUP BY Partita_ID";
    $a_pagamento = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    if(empty($a_pagamento))
        $totalePag = 0;
    else
        $totalePag = $a_pagamento["TOTALE_PAGAMENTI"];


    echo "ID: ".$a_atto['ID']." - PartitaID: ".$a_atto['Partita_ID']." - ".strtoupper($a_atto['Atto'])." n.".$a_atto['ID_Cronologico']."/".$a_atto['Anno_Cronologico']."<br>";
    echo "DATA ELABORAZIONE: ".$cls_help->toItalianDate($a_atto['Data_Elaborazione'])."<br>";
    echo "DATA STAMPA: ".$cls_help->toItalianDate($a_atto['Data_Stampa'])."<br>";
    echo "FLUSSO: ID ".$a_atto['FlowId']." - NUMERO ".$a_atto['Numero_Flusso']." - ANNO ".$a_atto['Anno_Flusso']."<br>";
    echo "DATA FLUSSO: ".$cls_help->toItalianDate($a_atto['Data_Flusso'])."<br><br>";
    echo "CODICI TRIBUTO: ".$a_codiciTrib["TOTALE"]."<br><br>";
    echo "PAGAMENTI PRECEDENTI: ".$totalePag."<br>";
    echo "INTERESSI PRECEDENTI: ".$a_atto['Interessi_Precedenti']."<br>";
    echo "SPESE NOTIFICA PRECEDENTI: ".$a_atto['Spese_Notifica_Precedenti']."<br><br>";
    echo "SPESE NOTIFICA: ".$a_atto['Spese_Notifica']."<br>";
    echo "INTERESSE: ".$a_atto['Interessi']."<br>";
    echo "TOTALE: ".$a_atto['Totale_Dovuto']."<br>";
    echo "DIR. RISCOSSIONE 1: ".$a_atto['Diritto_Riscossione_Minimo']."<br>";
    echo "DIR. RISCOSSIONE 2: ".$a_atto['Diritto_Riscossione_Massimo']."<br><br>";


    $check = $a_atto['Interessi_Precedenti']+$a_atto['Spese_Notifica_Precedenti']+$a_codiciTrib["TOTALE"]-$totalePag;
    if($check<$a_codiciTrib["IMPORTO_INTERESSI"])
        $importoBaseInteressi = $check;
    else
        $importoBaseInteressi = $a_codiciTrib["IMPORTO_INTERESSI"];

    $a_params = array(
        "CalcType" => $a_atto['Tipo_Riscossione'],
        "StartDate" => $cls_help->toDbDate($a_atto['Data_Decorrenza_Interessi']),
        "EndDate" => $cls_help->toDbDate($a_atto['Data_Calcolo_Interessi']),
        "BaseAmount" => $importoBaseInteressi,
        "a_blocks" => $a_blockPeriods,
        "a_interessiTributi" => $a_interessiTributi
    );

    $interesseRicalcolato = $cls_elab->calcInterests($a_params);
    echo "IMPORTO BASE PER CALCOLO: ".$importoBaseInteressi." Dal ".$a_params['StartDate']." al ".$a_params['EndDate']."<br>";
    echo "INTERESSE RICALCOLATO: ".$interesseRicalcolato."<br>";

    $totDovutoRicalcolato = $a_atto['Totale_Dovuto']-$a_atto['Interessi']+$interesseRicalcolato;
    $dirittoMinRicalc = 0.00;
    $dirittoMaxRicalc = 0.00;

    if(($a_atto['Flag_Blocco_Diritto_Riscossione']!="si" || is_null($a_atto['Flag_Blocco_Diritto_Riscossione'])) && !is_null($enteGestito['Gestore_ID']))
    {
        $importo_calcolo_diritto = $totDovutoRicalcolato - $totalePag;
        $dirittoMinRicalc = $importo_calcolo_diritto * $parametri['Diritto_Riscossione_Minimo'] / 100;
        $dirittoMaxRicalc = $importo_calcolo_diritto * $parametri['Diritto_Riscossione_Massimo'] / 100;
    }
    echo "TOTALE RIC: ".$totDovutoRicalcolato."<br>";
    echo "DIR. RISCOSSIONE 1 RIC: ".$dirittoMinRicalc."<br>";
    echo "DIR. RISCOSSIONE 2 RIC: ".$dirittoMaxRicalc."<br><br><br><br>";

    $status = "";
    if($a_atto['Interessi']!=$interesseRicalcolato)
        $status.= "!= IP ".$a_atto['Interessi']." - RI ".$interesseRicalcolato;
    else{
        $status.= "\n= IP ".$a_atto['Interessi']." - RI ".$interesseRicalcolato;
        continue;
    }

    if(!is_null($a_atto['Data_Flusso'])){
        if($a_atto['Interessi']-$interesseRicalcolato>10.1){
            $contFlusso++;
            $status.= "\nA ".$a_atto['ID_Cronologico']."/".$a_atto['Anno_Cronologico']." - F ".$a_atto['Numero_Flusso']."/".$a_atto['Anno_Flusso'];
        }
        else
            continue;
    }

    else if(!is_null($a_atto['Data_Stampa'])){
        if($a_atto['Interessi']-$interesseRicalcolato>10.1){
            $contStampa++;
            $status.= "\nA ".$a_atto['ID_Cronologico']."/".$a_atto['Anno_Cronologico']." - S ".$cls_help->toItalianDate($a_atto['Data_Stampa']);
        }
        else
            continue;
    }
    else{
        $contNostampa++;
        $status.= "\nNS";
        $query = "UPDATE atto SET Totale_Dovuto=".$totDovutoRicalcolato.", Interessi=".$interesseRicalcolato.", ";
        $query.= "Diritto_Riscossione_Minimo=".round($dirittoMinRicalc,2).", Diritto_Riscossione_Massimo=".round($dirittoMaxRicalc,2)." ";
        $query.= "WHERE ID=".$a_atto['ID'];
//        echo $query."<br><br>";
//        $cls_db->ExecuteQuery($query);
    }

    $cont++;
    $pdf->SetFont('Arial', '', 10);

    $array_value = array();

    $array_value[] = $a_atto['CC'];
    $array_value[] = $a_atto['Partita_ID_Comune'];
    $array_value[] = $a_atto['Info_Cartella'];
    $array_value[] = number_format($a_atto['Totale_Dovuto'],2,",",".");
    $array_value[] = $status;

    $y = $pdf->setRow($array_value,"down",$styleDash,null,0,$array_width);
    //$y = crea_riga($pdf , $array_width, $array_value, "down" , $styleDash);

    if( $y > 266 )
    {

        $y2_vert = $pdf->getY();

        $pdf->verticalLines($y1_vert , $y2_vert, $styleDash);
        //crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);

        $pdf->AddPage();
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 11);

        $y1_vert = $pdf->setRow($array_intestaz,"up_down",$styleRetta,null,0,$array_width);
        //$y1_vert = crea_riga($pdf , $array_width, $array_intestaz , "up_down" , $styleRetta);

    }


    echo "<br><br>";
}

$y2_vert = $pdf->getY();

$pdf->verticalLines($y1_vert , $y2_vert, $styleDash);
//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
$pdf->Ln(10);
$pdf->Cell(50,0,"TOTALE ATTI ".$cont,"",2);
$pdf->Cell(50,0,"TOTALE ATTI CON FLUSSO ".$contFlusso,"",2);
$pdf->Cell(50,0,"TOTALE ATTI STAMPATI ".$contStampa,"",2);
$pdf->Cell(50,0,"TOTALE ATTI DA STAMPARE ".$contNostampa,"",2);
$pdf->Output( $file_elenco , 'F');

?>
<script>
    window.open('<?php echo $vedi_file; ?>','_blank');
</script>
<?php
include(INC."/footer.php");