<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

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
$ID_Banca = $cls_help->getVar("ID_Banca");
$filter["da_data_notifica"] = $dataNotificaDa = $cls_help->getVar("da_data_notifica");
$filter["a_data_notifica"] = $dataNotificaA = $cls_help->getVar("a_data_notifica");

if($ID_Banca != ""){
    $queryBank = "SELECT * FROM banca WHERE ID = ".$ID_Banca." ";
    $bank = $cls_db->getArrayLine($cls_db->ExecuteQuery($queryBank));

    $filter["banca"] = $bank["Denominazione"];
}

$query =   "SELECT PIG_GEN.*, B.Denominazione, CONCAT(PIG_GEN.Cognome_Ditta,' ',PIG_GEN.Nome) AS Contribuente 
            FROM v_list_pignoramento AS PIG_GEN 
            JOIN pignoramento_presso_terzi AS PT ON PT.Pignoramento_ID = PIG_GEN.ID 
            JOIN banca AS B ON PT.Terzo_ID = B.ID 
            WHERE PIG_GEN.DocumentTypeId = 8 AND PT.Tipo_Terzi = 'banca' AND PIG_GEN.Data_Notifica IS NOT NULL";

if($ID_Banca != "")
    $query .= " AND B.ID = '".$ID_Banca."' ";

if($dataNotificaDa != ""){
    $query .= " AND PIG_GEN.Data_Notifica >= '".$dataNotificaDa."' ";
}

if($dataNotificaA != ""){
    $query .= " AND PIG_GEN.Data_Notifica <= '".$dataNotificaA."' ";
}

if($CC != "")
    $query .= " AND PIG_GEN.CC = '".$CC."' ";

$query .= " ORDER BY PIG_GEN.CC ASC ";

//var_dump($query);die;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Ente","Banca","Crono ID","Crono Anno","Utente");
$dataExcel[] = array("<b>Ente</b>","<b>Banca</b>","<b>Crono ID</b>","<b>Crono Anno</b>","<b>Utente</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent * 2 , $percent * 3 , $percent , $percent , $percent * 3);
$a_align = array( "L" , "L" , "L" , "L" ,"L");
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

    if($fileType == 2) {

        $a_value[0] = array(
            $result[$i]["Denominazione_Ente"],
            $result[$i]["Denominazione"],
            $result[$i]["ID_Cronologico"],
            $result[$i]["Anno_Cronologico"],
            $result[$i]["Contribuente"]
        );



        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array(
            $result[$i]["Denominazione_Ente"],
            $result[$i]["Denominazione"],
            $result[$i]["ID_Cronologico"],
            $result[$i]["Anno_Cronologico"],
            $result[$i]["Contribuente"]);
    }
}

$nameFILE = "";
if($fileType == 2) {

    $a_mainPageParams = array("title" => strtoupper("STAMPA MULTIPLA"), "subtitle" => $title);
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_elab->getFiltersDescription($filter);

    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO ELEMENTI";
    $recap[1]['value'] = count($result);
    $pdf->setMainPage($a_filters, $recap);

    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidatePignoBanche.pdf";
    $pathFILE .= "/".$nameFILE;

    $pdf->Output($pathFILE, 'F');
}
else {
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidatePignoBanche.xlsx";
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

header_remove('Set-Cookie');

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);


