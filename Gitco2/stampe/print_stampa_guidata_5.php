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
$filter["range_giorni"] = $range_giorni = $cls_help->getVar("range_giorni");
$fileType = $cls_help->getVar("file_type");

$query = 'SELECT A.Data_Stampa, PT.CC, PT.Comune_ID, PT.Utente_ID, A.Anno_Cronologico, A.ID_Cronologico 
FROM partita_tributi AS PT
LEFT JOIN atto AS A ON A.Partita_ID = PT.ID AND A.ID = (SELECT ID FROM atto AS A2 WHERE A2.Partita_ID = PT.ID ORDER BY A2.Data_Elaborazione DESC LIMIT 1)
WHERE A.DocumentTypeId = 3 ';

if($CC!="")
    $query .= ' AND PT.CC = "'.$CC.'" ';

if($range_giorni != ""){
    $query .= " AND (NOW() > A.Data_Stampa + INTERVAL ".$range_giorni." DAY) ";
}

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

//echo($query);


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Partita ID","Codice catastale","Comune","ID Utente","Anno Cronologico","ID Cronologico","Data Stampa");
$dataExcel[] = array("<b>Partita ID</b>","<b>Codice Catastale</b>","<b>Comune</b>","<b>ID Utente</b>","<b>Anno Cronologico</b>","<b>ID Cronologico</b>","<b>Data Stampa</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent , $percent , $percent*2 , $percent , $percent*2 , $percent*2 , $percent );
$a_align = array( "L" , "L" , "L" , "L" , "L" , "L" , "L");
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

    $query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '" . $result[$i]["CC"] . "'";
    $city = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "enti_gestiti");

    if($fileType == 2) {

        $a_value[0] = array(
            $result[$i]["Comune_ID"],
            $result[$i]["CC"],
            $city["Denominazione"],
            $result[$i]["Utente_ID"],
            $result[$i]["Anno_Cronologico"],
            $result[$i]["ID_Cronologico"],
            $cls_date->Get_DateNewFormat($result[$i]["Data_Stampa"],"DB")
        );

        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["Comune_ID"],$result[$i]["CC"],$city["Denominazione"],$result[$i]["Utente_ID"],$result[$i]["Anno_Cronologico"],$result[$i]["ID_Cronologico"],$cls_date->Get_DateNewFormat($result[$i]["Data_Stampa"],"DB"));
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
    $recap[1]['label'] = "NUMERO PARTITE";
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


