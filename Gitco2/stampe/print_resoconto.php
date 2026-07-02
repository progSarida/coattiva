<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//include(INC."/headerAjax.php");
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_help.php";

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_date = new cls_DateTimeI("IT");
$cls_elab = new cls_elaborazioniUtils();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

$_SESSION['progress'] = "0.00";
session_write_close();

$printType = $cls_help->getVar("printType");
$cc = $cls_help->getVar("cc");
$UserName = $cls_help->getVar("UserName");
$da_data = $cls_help->getVar("da_data");
$a_data = $cls_help->getVar("a_data");

$filter["data_richiesta_da"] = $cls_date->Get_DateNewFormat($da_data);
$filter["data_richiesta_a"] = $cls_date->Get_DateNewFormat($a_data);

$query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '".$cc."'";
$res = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

if($res != null) $filter["comune"] = $res["Denominazione"]." [".$cc."]";
else $filter["comune"] = null;

$query = "SELECT COUNT(RP.Pec) AS Pec_Ricevute, COUNT(RP.IdRichiesta) AS Numero_Richieste, EG.Denominazione, R.DataRichiesta, R.IdRichiesta, R.UserName FROM ini_pec_request as R ";
$query .= " LEFT JOIN ini_pec_request_pec AS RP ON RP.IdRichiesta = R.IdRichiesta ";
$query .= " LEFT JOIN enti_gestiti AS EG ON EG.CC = R.CC ";
$query .= "WHERE R.UserName = '".$UserName."' AND R.EsitoRichiesta = 'OK' AND R.EsitoFornitura = 'OK' ";

if($cc != null)
    $query .= " AND R.CC = '".$cc."' ";
if($da_data != null)
    $query .= " AND DATE_FORMAT(R.DataRichiesta, '%Y-%m-%d') >= '".$da_data."' ";
if($a_data != null)
    $query .= " AND DATE_FORMAT(R.DataRichiesta, '%Y-%m-%d') <= '".$a_data."' ";

$query .= " GROUP BY R.IdRichiesta ";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Codice utente infocamere","Ente","Data","IdRichiesta","Numero richieste","Pec ricevute");
$dataExcel[] = array("<b>Codice utente infocamere</b>","<b>Ente</b>","<b>Data</b>","<b>IdRichiesta</b>","<b>Numero richieste</b>","<b>Pec ricevute</b>");

$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent , $percent * 4 , $percent * 2, $percent , $percent , $percent );
$a_align = array( "L" , "L" , "L" , "L" ,"R" ,"R" );
$pdf->setArray($a_width,"a_width");
$pdf->setArray($a_align,"a_align");
$pdf->setHeaderPage();
$pdf->addLines();

$count = count($result);
if($count == 0){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();

    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
    
    die;
}

$totRichiesteEffettuate = 0;
$totResocontiRestutuiti = 0;

for($i=0; $i < $count; $i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    $data = isset($result[$i]["DataRichiesta"])?$cls_date->Get_DateNewFormat($result[$i]["DataRichiesta"]):"";

    if($printType == "pdf") {

        $a_value[0] = array(
            $result[$i]["UserName"],
            $result[$i]["Denominazione"],
            $data,
            $result[$i]["IdRichiesta"],
            $result[$i]["Numero_Richieste"],
            $result[$i]["Pec_Ricevute"]
        );

        $totRichiesteEffettuate += (int) $result[$i]["Numero_Richieste"];
        $totResocontiRestutuiti += (int) $result[$i]["Pec_Ricevute"];

        $return = $pdf->setRowPage($a_value);
        if($return == "addPage")
            $pdf->addLines();
        else if($i < $count-1)
            $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["UserName"],$result[$i]["Denominazione"],$data,$result[$i]["IdRichiesta"],$result[$i]["Numero_Richieste"],$result[$i]["Pec_Ricevute"]);
    }
}

$nameFILE = "";
if($printType == "pdf") {

    $a_width = array( $percent * 8 , $percent , $percent );
    $a_align = array( "L" ,"R" ,"R" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");

    $a_value[0] = array(
        "TOTALI",
        $totRichiesteEffettuate,
        $totResocontiRestutuiti
    );

    $pdf->addLines();
    $pdf->setRowPage($a_value);
    $pdf->addLines();

    $a_mainPageParams = array("title" => strtoupper("STAMPA INIPEC"), "subtitle" => "RESOCONTO");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_elab->getFiltersDescription($filter);

    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO RIGHE";
    $recap[1]['value'] = count($result);
    $pdf->setMainPage($a_filters, $recap);

    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "Resoconto_INIPEC.pdf";
    $pathFILE .= "/".$nameFILE;

    //die;
    $pdf->Output($pathFILE, 'F');
}
else {
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "Resoconto_INIPEC.xlsx";
    $pathFILE .= "/".$nameFILE;
    if (count($dataExcel) > 1)
        SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($pathFILE);
}

$pathWEBFILE = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;
$file = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;
//$prev = STAMPE_WEB."/resoconto_inipec.php?&p=&c=".$c."&a=".$a;

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);