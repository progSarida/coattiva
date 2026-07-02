<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

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

$_SESSION['progress'] = "0.00";
session_write_close();

$tipo_file = $cls_help->getVar("tipo_file");
$filter["importo_da"] = $importo_da = $cls_help->getVar("importo_da");
$filter["importo_a"] = $importo_a = $cls_help->getVar("importo_a");

$query = "SELECT * FROM coefficiente_coazione WHERE CC = '".$c."' ";

if($importo_da != "" && $importo_a != ""){
    $query .= " AND Credito_Minimo >= ".$importo_da." AND Credito_Massimo <= ".$importo_a." ";
}
else if($importo_da != ""){
    $query .= " AND Credito_Minimo >= ".$importo_da." ";
}
else if($importo_a != ""){
    $query .= " AND Credito_Massimo <= ".$importo_a." ";
}

$query .= " ORDER BY Credito_Minimo ASC";


$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Credito Minimo","Credito Massimo","Percentuale");
$dataExcel[] = array("<b>Codice Catastale</b>","<b>Nome Comune</b>","<b>Credito Minimo</b>","<b>Credito Massimo</b>","<b>Percentuale</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent * 3, $percent * 3, $percent * 4 );
$a_align = array( "L" , "L" , "L" );
$pdf->setArray($a_width,"a_width");
$pdf->setArray($a_align,"a_align");
$pdf->setHeaderPage();
$pdf->addLines();

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

$query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '" . $c . "'";
$city = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "enti_gestiti");

for($i=0; $i < $count; $i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    if($tipo_file == "pdf") {

        $a_value[0] = array(
            $result[$i]["Credito_Minimo"],
            $result[$i]["Credito_Massimo"],
            $result[$i]["Percentuale"]
        );



        $check = $pdf->setRowPage($a_value);
        if($check == "noAddPage")
            $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($city["Denominazione"],$c,$result[$i]["Credito_Minimo"],$result[$i]["Credito_Massimo"],$result[$i]["Percentuale"]);
    }
}

$nameFILE = "";
if($tipo_file == "pdf") {

    $a_mainPageParams = array("title" => strtoupper("STAMPA MAGGIORAZIONI PIGNORAMENTO"), "subtitle" => "COMUNE DI ".$city["Denominazione"]." [".$c."]");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_elab->getFiltersDescription($filter);

    //var_dump($filter);

    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO MAGGIORAZIONI";
    $recap[1]['value'] = count($result);
    $pdf->setMainPage($a_filters, $recap);

    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "MaggiorazioniPignoramento.pdf";
    $pathFILE .= "/".$nameFILE;

    $pdf->Output($pathFILE, 'F');
}
else {
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "MaggiorazioniPignoramento.xlsx";
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
