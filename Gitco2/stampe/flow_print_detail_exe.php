<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");


include_once(CLS."/cls_registry.php");
include_once(CLS."/cls_html.php");
include_once(CLS."/cls_flow.php");
include_once (CLS."/cls_excel.php");
include_once (CLS."/cls_file.php");

$FlowId = $cls_help->getVar("FlowId");
$file = $cls_help->getVar("file");
$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");
$cls_file = new cls_file();

$queryFlow = "SELECT partita_tributi.Comune_ID AS Partita, partita_tributi.Tipo AS Tipo_Partita, partita_tributi.Sottotipo AS Sottotipo_Partita, partita_tributi.Anno_Riferimento, ";
$queryFlow.= "v_atti_pigno.*, STATO.Descrizione AS Stato_Not_Descrizione, MODA.Descrizione AS Modalita_Not_Descrizione, MOT.Descrizione AS Anomalia_Not_Descrizione, ";
$queryFlow.= "notifiche_importate.Ms_Ric_Num, notifiche_importate.Ms_Rac_Num, utente.Cognome, utente.Nome, utente.Ditta, utente.Genere ";
$queryFlow.= "FROM v_atti_pigno ";
$queryFlow.= "JOIN partita_tributi ON partita_tributi.ID=v_atti_pigno.Partita_ID JOIN utente ON utente.ID=partita_tributi.Utente_ID ";
$queryFlow.= "LEFT JOIN parametri_notifica MOT ON MOT.ID=v_atti_pigno.Motivo_Notifica ";
$queryFlow.= "LEFT JOIN parametri_notifica MODA ON MODA.ID=v_atti_pigno.Modalita_Notifica ";
$queryFlow.= "LEFT JOIN parametri_notifica STATO ON STATO.ID=v_atti_pigno.Stato_Notifica ";
$queryFlow.= "LEFT JOIN notifiche_importate ON v_atti_pigno.ID=notifiche_importate.DocumentId ";
$queryFlow.= "AND v_atti_pigno.FlowId=notifiche_importate.FlowId AND v_atti_pigno.DocumentTypeId=notifiche_importate.DocumentTypeId ";
$queryFlow.= "AND v_atti_pigno.CC=notifiche_importate.CC_Comune WHERE v_atti_pigno.FlowId=".$FlowId." ORDER BY Anno_Cronologico ASC, ID_Cronologico ASC";
//echo $queryFlow;
$a_flows = $cls_db->getResults($cls_db->SelectQuery($queryFlow));

if($file=="xls"){



    if(count($a_flows)>0){
        $fileXls = new PHPExcel();
        $fileXls->getProperties()
            ->setCreator("Sarida")
            ->setLastModifiedBy($_SESSION['username'])
            ->setTitle("Notifiche flusso ".$a_flows[0]['Numero_Flusso']."/".$a_flows[0]['Anno_Flusso'])
            ->setDescription("Data creazione flusso ".$cls_help->toItalianDate($a_flows[0]['Data_Flusso'])." Documento ".$a_flows[0]['DocumentType']);
        $fileXls->setActiveSheetIndex(0);
        $col = 0;
        foreach(array_keys($a_flows[0]) as $key) {
            $fileXls->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $key);

            $colString = PHPExcel_Cell::stringFromColumnIndex($col);
            if($key=="Totale_Dovuto")
            {
                $fileXls->getActiveSheet()
                    ->getStyle($colString."2:".$colString.(count($a_flows)+2))
                    ->getNumberFormat()
                    ->setFormatCode('[$€ ]#,##0.00_-');
            }
            else{
                $fileXls->getActiveSheet()
                    ->getStyle($colString . "2:" . $colString . (count($a_flows) + 2))
                    ->getNumberFormat()
                    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }


            $col++;
        }

        $row = 2; // 1-based index
        for($i=0;$i<count($a_flows);$i++) {
            $col = 0;
            foreach($a_flows[$i] as $key=>$value) {
                if($key=="PARTITA_IVA"){
                    $colString = PHPExcel_Cell::stringFromColumnIndex($col);
                    $fileXls->getActiveSheet()->setCellValueExplicit($colString.$row, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                }
                else
                    $fileXls->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
                $col++;
            }
            $row++;
        }

        $writer = PHPExcel_IOFactory::createWriter($fileXls, 'Excel5');
        $nomeFile = ROOT."/Notifiche.xls";
        $fileWebName = WEB_ROOT."/Notifiche.xls";

        $writer->save($nomeFile);

        if(is_file($nomeFile)){
            echo
            '<script>window.name = "Stampa";  window.open("'.$fileWebName.'","Stampa");
            setTimeout(function () {
                history.back();
            }, 2000);</script>';
        }
    }
    else echo "<script>location.href = '".WEB_ROOT."/coattiva/flow_mgmt_detail.php?a=".$a."&c=".$c."&FlowId=".$FlowId."&msg=Nessun flusso trovato!&error=2';</script>"; //header("location: flow_mgmt_detail.php?a". $a .'&c'.$c.'&FlowId='. $FlowId.'&msg=Nessun flusso trovato!&error=2');



}else {
//    include_once TCPDF . "/tcpdf.php";
//
//    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
//
//
//    $pdf->SetCreator(PDF_CREATOR);
//    $pdf->SetAuthor($_SESSION['citytitle']);
//    $pdf->SetTitle('Request');
//    $pdf->SetSubject('Request');
//    $pdf->SetKeywords('');
//
//    $this->setPrintHeader(false);
//    $this->setPrintFooter(false);
//    $pdf->SetAutoPageBreak(false);
//    $pdf->SetMargins(10, 10, 10);
//
//    $r_Payments = $rs->Select('enti_getiti', "CC='" . $c . "'");
//    $r_Payment = mysqli_fetch_array($r_Payments);
//
//    $MangerName = $r_Payment['ManagerName'];
//    $ManagerAddress = $r_Payment['ManagerAddress'];
//    $ManagerCity = $r_Payment['ManagerZIP'] . " " . $r_Payment['ManagerCity'] . " (" . $r_Payment['ManagerProvince'] . ")";
//    $ManagerPhone = $r_Payment['ManagerPhone'];
//
//
//    $pdf->AddPage();
//    $pdf->SetFont('arial', '', 9, '', true);
//
//    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
//    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
//    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//
//    $pdf->SetFillColor(255, 255, 255);
//    $pdf->SetTextColor(0, 0, 0);
//
//    $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);
//
//
//    $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
//    $pdf->LN(4);
//    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
//    $pdf->LN(4);
//    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
//    $pdf->LN(4);
//    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);
//
//    $pdf->LN(10);
//
//
//    $n_Count = 0;
//    $n_Row = 0;
//
//    $n_ChangePage = 35;
//
//
//    while ($r_FineNotification = mysqli_fetch_array($rs_FineNotification)) {
//        if ($n_Count == 0) {
//
//            $str_SendDate = ($r_FineNotification['SendDate'] != "") ? DateOutDB($r_FineNotification['SendDate']) : "";
//
//
//            $pdf->writeHTMLCell(200, 0, 30, '', "Flusso N. ". $r_FineNotification['FlowNumber'] ." Del ". DateOutDB($r_FineNotification['FlowDate']). " Spedito il ". $str_SendDate, 0, 0, 1, true, 'C', true);
//            $pdf->LN(10);
//
//
//            $pdf->AddPage();
//            $pdf->SetFont('arial', '', 7, '', true);
//
//            $pdf->setFooterData(array(0,64,0), array(0,64,128));
//            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
//            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//
//            $pdf->SetFillColor(255, 255, 255);
//            $pdf->SetTextColor(0, 0, 0);
//
//            $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);
//
//
//
//            $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
//            $pdf->LN(4);
//            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
//            $pdf->LN(4);
//            $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
//            $pdf->LN(4);
//            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);
//
//            $pdf->LN(10);
//
//
//            $y = $pdf->getY();
//            $pdf->SetFont('arial', '', 8, '', true);
//            $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(40, 4, 20, $y, "Cronologico", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(30, 4, 60, $y, "Data Notifica", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(50, 4, 90, $y, "Raccomandata", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(50, 4, 140, $y, "Ricevuta ritorno", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(80, 4, 190, $y, "Esito", 1, 0, 1, true, 'C', true);
//
//            $pdf->LN(4);
//
//
//
//
//        }
//
//        $n_Count++;
//        $n_Row++;
//
//        if ($n_Row > $n_ChangePage) {
//
//            $pdf->AddPage();
//
//            $pdf->LN(10);
//
//            $y = $pdf->getY();
//            $pdf->SetFont('arial', '', 8, '', true);
//            $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(40, 4, 20, $y, "Cronologico", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(30, 4, 60, $y, "Data Notifica", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(50, 4, 90, $y, "Raccomandata", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(50, 4, 140, $y, "Ricevuta ritorno", 1, 0, 1, true, 'C', true);
//            $pdf->writeHTMLCell(80, 4, 190, $y, "Esito", 1, 0, 1, true, 'C', true);
//
//            $pdf->LN(4);
//
//            $pdf->SetFont('arial', '', 8, '', true);
//            $n_Row = 0;
//
//
//        }
//
//
//        $y = $pdf->getY();
//        $str_NotificationDate = ($r_FineNotification['NotificationDate']!="") ? DateOutDB($r_FineNotification['NotificationDate']) : "" ;
//
//
//        $pdf->writeHTMLCell(10, 4, 10, $y, $n_Count, 1, 0, 1, true, 'L', true);
//        $pdf->writeHTMLCell(40, 4, 20, $y, $r_FineNotification['ProtocolId'] .'/'. $r_FineNotification['ProtocolYear'], 1, 0, 1, true, 'L', true);
//        $pdf->writeHTMLCell(30, 4, 60, $y, $str_NotificationDate, 1, 0, 1, true, 'C', true);
//        $pdf->writeHTMLCell(50, 4, 90, $y, $r_FineNotification['LetterNumber'], 1, 0, 1, true, 'L', true);
//        $pdf->writeHTMLCell(50, 4, 140, $y, $r_FineNotification['ReceiptNumber'], 1, 0, 1, true, 'L', true);
//        $pdf->writeHTMLCell(80, 4, 190, $y, $r_FineNotification['Title'], 1, 0, 1, true, 'L', true);
//
//
//        $pdf->LN(4);
//    }
//
//
//
//
//    $FileName = $_SESSION['cityid'].'_flusso_'.date("Y-m-d_H-i").'.pdf';
//
//    $pdf->Output(ROOT."/doc/print/flow/".$FileName, "F");
//    $_SESSION['Documentation'] = $MainPath.'/doc/print/flow/'.$FileName;
//
//

}

//header("location: flow_mgmt_detail.php?a". $a .'&c'.$c.'&FlowId='. $FlowId);