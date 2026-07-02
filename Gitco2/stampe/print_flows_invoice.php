<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

//include(INC."/header.php");

include_once CLS . "/cls_file.php";
include_once CLS . "/cls_print.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_textParameters.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_flow.php";
include_once CLS . "/cls_postal.php";
include_once CLS . "/cls_authority.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";

$cls_help = new cls_help();
$cls_file = new cls_file();
$cls_db = new cls_db();

set_time_limit(-1);

//FILTRI

$invoice['Id'] = $cls_help->getVar('InvoiceId');

//include(INC."/footer.php");

$invoice['query'] = "SELECT * FROM flow_invoices WHERE Id=".$invoice['Id'];
$a_invoice = $cls_db->getArrayLine($cls_db->SelectQuery($invoice['query']));
$flow['query'] = "SELECT * FROM flows WHERE PostageInvoiceId=".$invoice['Id']." OR PrintInvoiceId=".$invoice['Id'];
$a_flows = $cls_db->getResults($cls_db->SelectQuery($flow['query']));

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
$pdf->setDocParams();
$pdf->AddPage("P");
$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0, 0, "FATTURA NUMERO ".$a_invoice['Number']."/".$a_invoice['Year']." del ".$cls_help->toItalianDate($a_invoice['Date']) , 0, "C", 0,1);
$pdf->Ln(10);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+195,$y);
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 0, "Codice Ente" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(20, 0, "Flussi" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(25, 0, "Data upload" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(15, 0, "Records" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(35, 0, "Stampa e imbus." , 0, 0, 'C', 0, '', 0);
$pdf->Cell(35, 0, "Spese postali" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(30, 0, "Totale" , 0, 1 , 'C', 0, '', 0);
$pdf->Ln(2);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+195,$y);
$pdf->Ln(2);
$totalPrint = 0;
$totalPostage = 0;
$totalRecords = 0;
for($i=0;$i<count($a_flows);$i++){
    $pdf->SetFont('Arial', '', 9);
    $totalRecords+=$a_flows[$i]['RecordsNumber'];
    $pdf->Cell(30, 0, $a_flows[$i]['CityId'] , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(20, 0, $a_flows[$i]['Number']."/".$a_flows[$i]['Year'] , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(25, 0, $cls_help->toItalianDate($a_flows[$i]['CreationDate']) , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(15, 0, $a_flows[$i]['RecordsNumber'] , 0, 0, 'C', 0, '', 0);
    $printCost = 0;
    if($a_flows[$i]['PrintInvoiceId']==$invoice['Id']){
        $printCost = $a_flows[$i]['PrintCost']*$a_flows[$i]['RecordsNumber'];
        $totalPrint+= $printCost;
    }
    $pdf->Cell(35, 0, $cls_help->floatToString($printCost)." Euro" , 0, 0, 'C', 0, '', 0);

    $postage = 0;
    if($a_flows[$i]['PostageInvoiceId']==$invoice['Id']){
        $postageZone0 = $a_flows[$i]['Zone0Postage']*$a_flows[$i]['Zone0Number'];
        $postageZone1 = $a_flows[$i]['Zone1Postage']*$a_flows[$i]['Zone1Number'];
        $postageZone2 = $a_flows[$i]['Zone2Postage']*$a_flows[$i]['Zone2Number'];
        $postageZone3 = $a_flows[$i]['Zone3Postage']*$a_flows[$i]['Zone3Number'];
        $postage = $postageZone0+$postageZone1+$postageZone2+$postageZone3;
        $totalPostage+= $postage;
    }
    $pdf->Cell(35, 0, $cls_help->floatToString($postage)." Euro" , 0, 0, 'C', 0, '', 0);

    $pdf->Cell(35, 0, $cls_help->floatToString($printCost+$postage)." Euro" , 0, 1, 'C', 0, '', 0);

    $pdf->Ln(2);

}

if(count($a_flows)>0){
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Line($x,$y,$x+195,$y);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(75, 0, "TOTALI FATTURA" , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(15, 0, $totalRecords , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(35, 0, $cls_help->floatToString($totalPrint)." Euro" , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(35, 0, $cls_help->floatToString($totalPostage)." Euro" , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(35, 0, $cls_help->floatToString($totalPrint+$totalPostage)." Euro" , 0, 1, 'C', 0, '', 0);

    $pdf->Ln(2);
    $pdf->Output( "fattura.pdf" );
}

