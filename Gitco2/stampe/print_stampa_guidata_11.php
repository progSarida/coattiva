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
$filter["tipo_entrata"] = $tipoEntrata = $cls_help->getVar("tipo_entrata");

$query = 'SELECT E.Denominazione, PT.Comune_ID AS Partita_ID, U.Comune_ID AS Utente_ID, PT.CC, U.Nome, U.Cognome, U.Ditta, PT.Tipo 
            FROM partita_tributi AS PT
            LEFT JOIN enti_gestiti AS E ON E.CC = PT.CC
            LEFT JOIN utente AS U ON U.ID = PT.Utente_ID
            WHERE PT.Flag_Blocco_Coazione = "si" AND (PT.Note_Blocco IS NULL OR PT.Note_Blocco = "") AND (PT.Motivo_Blocco IS NULL OR PT.Motivo_Blocco = 0) ';

if($CC != "")
    $query .= " AND PT.CC = '".$CC."' ";


if($tipoEntrata != ""){
    $query .= " AND PT.Tipo = '".$tipoEntrata."' ";
}

//var_dump($query);die;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Codice Catastale","Comune","Partita ID","Utente ID","Tipo Entrata","Nome","Cognome","Ditta");
$dataExcel[] = array("<b>Codice Catastale</b>","<b>Comune</b>","<b>Partita ID</b>","<b>Utente ID</b>","<b>Tipo Entrata</b>","<b>Nome</b>","<b>Cognome</b>","<b>Ditta</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent , $percent * 2 , $percent , $percent , $percent , $percent , $percent , $percent * 2 );
$a_align = array( "L" , "L" , "L" , "L" ,"L" ,"L" ,"L" ,"L");
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
            $result[$i]["CC"],
            $result[$i]["Denominazione"],
            $result[$i]["Partita_ID"],
            $result[$i]["Utente_ID"],
            $result[$i]["Tipo"],
            $result[$i]["Nome"],
            $result[$i]["Cognome"],
            $result[$i]["Ditta"]
        );



        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["CC"],$result[$i]["Denominazione"],$result[$i]["Partita_ID"],$result[$i]["Utente_ID"],$result[$i]["Tipo"],$result[$i]["Nome"],$result[$i]["Cognome"],$result[$i]["Ditta"]);
    }
}

$nameFILE = "";
if($fileType == 2) {

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


