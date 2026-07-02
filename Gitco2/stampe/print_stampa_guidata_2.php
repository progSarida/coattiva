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

$title = $cls_help->getVar("title");
$filter["CC"] = $CC = $cls_help->getVar("CC");
$fileType = $cls_help->getVar("file_type");
$filter["anno_flusso_da"] = $annoFlussoDa = $cls_help->getVar("anno_flusso_da");
$filter["anno_flusso_a"] = $annoFlussoA = $cls_help->getVar("anno_flusso_a");
$filter["range_giorni"] = $rangeGiorni = $cls_help->getVar("range_giorni");
$filter["tipo_entrata"] = $tipoEntrata = $cls_help->getVar("tipo_entrata");

$query = 'SELECT F.Year, F.Number, F.CityId, F.CreationDate FROM flows as F
        LEFT JOIN atto AS A ON F.Id = A.FlowId
        LEFT JOIN partita_tributi AS PT ON PT.ID = A.Partita_ID
        WHERE IF(CancelDate IS NOT NULL, "NO", IF(SendDate IS NOT NULL, "NO", IF(PostagePaymentDate IS NOT NULL, "NO", IF(ProcessingDate IS NOT NULL, "NO", IF(UploadDate IS NOT NULL, "NO", IF(CreationDate IS NOT NULL, "OK", "NO")))))) = "OK" AND ((
            SELECT SUM(TableNotification.DataNotifica)
            FROM (
                SELECT COUNT(atto.Data_Notifica) AS DataNotifica 
                FROM atto 
                WHERE atto.FlowId = F.Id AND atto.CC = F.CityId AND atto.DocumentTypeId = F.DocumentTypeId AND atto.Data_Notifica is not null
                
                UNION 
                
                SELECT COUNT(NA.Data_Notifica) AS DataNotifica
                FROM pignoramento_generale AS PG
                left join notifica_atto as NA on NA.Atto_Notificato_ID = PG.ID and NA.CC = PG.CC and NA.Tipo_Atto_Notificato = "pignoramento" and NA.Tipo_Notifica = "debitore"
                WHERE PG.FlowId = F.Id AND PG.CC = F.CityId AND PG.DocumentTypeId = F.DocumentTypeId AND NA.Data_Notifica is not null
            ) AS TableNotification
        ) > 0 OR (
            SELECT SUM(TableAnomaly.ID)
            FROM(
                SELECT COUNT(atto.ID) AS ID 
                FROM atto 
                WHERE atto.FlowId = F.Id AND atto.CC = F.CityId AND atto.DocumentTypeId = F.DocumentTypeId AND atto.Data_Notifica is null AND atto.Motivo_Notifica > 0
        
                UNION 
        
                SELECT COUNT(PG.ID) AS ID
                FROM pignoramento_generale AS PG
                left join notifica_atto as NA on NA.Atto_Notificato_ID = PG.ID and NA.CC = PG.CC and NA.Tipo_Atto_Notificato = "pignoramento" and NA.Tipo_Notifica = "debitore"
                WHERE PG.FlowId = F.Id AND PG.CC = F.CityId AND PG.DocumentTypeId = F.DocumentTypeId AND NA.Data_Notifica is null AND NA.Motivo_Notifica > 0
            ) AS TableAnomaly
        ) > 0) ';

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
    $query .= " AND (NOW() > CreationDate + INTERVAL ".$rangeGiorni." DAY) ";
}

if($tipoEntrata != ""){
    $query .= " AND PT.Tipo = '".$tipoEntrata."' ";
}

$query .= " GROUP BY F.Id;";




$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Codice Catastale","Denominazione","Anno","Numero","Data Creazione");
$dataExcel[] = array("<b>Codice Catastale</b>","<b>Denominazione</b>","<b>Anno</b>","<b>Numero</b>","<b>Data Creazione</b>");



$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent*2 , $percent*2 , $percent*2 , $percent*2 , $percent*2 );
$a_align = array( "L" , "L" , "L" , "L" ,"C");
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
            $cls_date->Get_DateNewFormat($result[$i]["CreationDate"], "DB"),
        );

        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["CityId"],$city["Denominazione"],$result[$i]["Year"],$result[$i]["Number"],$cls_date->Get_DateNewFormat($result[$i]["CreationDate"], "DB"));
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