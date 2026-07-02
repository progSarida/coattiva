<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include(ROOT."/_parameter.php");

//include(INC."/headerAjax.php");
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("IT",false);
$cls_elab = new cls_elaborazioniUtils();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$title = $cls_help->getVar("title");
$filter["CC"] = $CC = $cls_help->getVar("CC");
$fileType = $cls_help->getVar("file_type");
$filter["anno_flusso_da"] = $annoFlussoDa = $cls_help->getVar("anno_flusso_da");
$filter["anno_flusso_a"] = $annoFlussoA = $cls_help->getVar("anno_flusso_a");
$filter["range_giorni"] = $rangeGiorni = $cls_help->getVar("range_giorni");
$filter["tipo_entrata"] = $tipoEntrata = $cls_help->getVar("tipo_entrata");
$filter["stampatore"] = $stampatore = $cls_help->getVar("PrinterId");

$query = 'SELECT F.Year, F.Number, F.CityId, count(NI.ID) AS ImportationNumber, F.RecordsNumber ,IF(F.CancelDate IS NOT NULL, "ANNULLATO", IF(F.SendDate IS NOT NULL, "CONSEGNATO", IF(F.PostagePaymentDate IS NOT NULL, "PAGATO", IF(F.ProcessingDate IS NOT NULL, "LAVORATO", IF(F.UploadDate IS NOT NULL, "UPLOAD", "CREATO"))))) AS Stato
        FROM flows as F
        #LEFT JOIN atto AS A ON F.Id = A.FlowId
        #LEFT JOIN partita_tributi AS PT ON PT.ID = A.Partita_ID
        LEFT JOIN notifiche_importate AS NI ON F.Id=NI.FlowId AND F.DocumentTypeId=NI.DocumentTypeId AND F.CityId=NI.CC_Comune
        WHERE ( 
                SELECT COUNT(NIS.ID) 
                FROM notifiche_importate AS NIS 
                WHERE F.Id=NIS.FlowId AND F.DocumentTypeId=NIS.DocumentTypeId AND F.CityId=NIS.CC_Comune
            ) != RecordsNumber AND IF(F.CancelDate IS NOT NULL, "ANNULLATO", IF(F.SendDate IS NOT NULL, "CONSEGNATO", IF(F.PostagePaymentDate IS NOT NULL, "PAGATO", IF(F.ProcessingDate IS NOT NULL, "LAVORATO", IF(F.UploadDate IS NOT NULL, "UPLOAD", "CREATO"))))) != "ANNULLATO" ';

if($CC != "")
    $query .= " AND F.CityId = '".$CC."' ";

if($annoFlussoDa != "" && $annoFlussoA != ""){
    $query .= " AND F.Year >= ".$annoFlussoDa." AND F.Year <= ".$annoFlussoA." ";
}
else if($annoFlussoDa != ""){
    $query .= " AND F.Year >= ".$annoFlussoDa." ";
}
else if($annoFlussoA != ""){
    $query .= " AND F.Year <= ".$annoFlussoA." ";
}

if($rangeGiorni != ""){
    $query .= " AND (NOW() > UploadDate + INTERVAL ".$rangeGiorni." DAY) ";
}

if($tipoEntrata != ""){
    $query .= " AND PT.Tipo = '".$tipoEntrata."' ";
}

if($stampatore != ""){
    $query .= " AND F.PrinterId = ".$stampatore." ";
}

$query .= " GROUP BY F.Id;";



$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Codice Catastale","Denominazione","Anno flusso","Numero flusso","Atti importati","Stato");
$dataExcel[] = array("<b>Codice Catastale</b>","<b>Denominazione</b>","<b>Anno flusso</b>","<b>Numero flusso</b>","<b>Atti importati</b>","<b>Stato</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent*2 , $percent*2 , $percent*2 , $percent*2 , $percent , $percent );
$a_align = array( "L" , "L" , "L" , "L" ,"C","C");
$pdf->setArray($a_width,"a_width");
$pdf->setArray($a_align,"a_align");
$pdf->setHeaderPage();
$pdf->addLines();



//var_dump($result);
$count = count($result);
if($count == 0) {
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();
	
    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
	
    die;
}

for($i=0; $i < $count; $i++){
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    $query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '" . $result[$i]["CityId"] . "'";
    $city = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "enti_gestiti");

    if($fileType == 2) {

        $a_value[0] = array(
            $result[$i]["CityId"],
            $city["Denominazione"],
            $result[$i]["Year"],
            $result[$i]["Number"],
            $result[$i]["ImportationNumber"]."/".$result[$i]["RecordsNumber"],
            $result[$i]["Stato"]
        );



        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["CityId"],$city["Denominazione"],$result[$i]["Year"],$result[$i]["Number"],$result[$i]["ImportationNumber"]."/".$result[$i]["RecordsNumber"],$result[$i]["Stato"]);
    }
}

$nameFILE = "";
if($fileType == 2) {
    $query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '" . $c . "'";
    $city = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "enti_gestiti");

    $a_mainPageParams = array("title" => strtoupper("STAMPA MULTIPLA"), "subtitle" => $title);
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_elab->getFiltersDescription($filter);

    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO FLUSSI";
    $recap[1]['value'] = count($result);
    $pdf->setMainPage($a_filters, $recap);

    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidate.pdf";
    $pathFILE .= "/".$nameFILE;

    $pdf->Output($pathFILE, 'F');
}
else {
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidate.xlsx";
    $pathFILE .= "/".$nameFILE;
    if (count($dataExcel) > 1)
        SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($pathFILE);
}

//$pathWEBFILE = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

$file = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);
