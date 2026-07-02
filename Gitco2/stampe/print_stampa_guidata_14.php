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
$filter["tipo_entrata"] = $tipoEntrata = $cls_help->getVar("tipo_entrata");
$filter["da_partita"] = $da_partita = $cls_help->getVar("partita_da");
$filter["a_partita"] = $a_partita = $cls_help->getVar("partita_a");
$filter["daco"] = $daco = $cls_help->getVar("daco");
$filter["dano"] = $dano = $cls_help->getVar("dano");
$filter["acog"] = $acog = $cls_help->getVar("acog");
$filter["anom"] = $anom = $cls_help->getVar("anom");
$filter["anno_cronologico_da"] = $da_anno_crono = $cls_help->getVar("anno_cronologico_da");
$filter["anno_cronologico_a"] = $a_anno_crono = $cls_help->getVar("anno_cronologico_a");
$filter["id_cronologico_da"] = $da_id_crono = $cls_help->getVar("id_cronologico_da");
$filter["id_cronologico_da"] = $a_id_crono = $cls_help->getVar("id_cronologico_a");

$query =   "SELECT E.Denominazione, PT.Comune_ID AS Partita_ID, U.Comune_ID AS Utente_ID, PT.CC, IF(Genere='D',COALESCE(U.Ditta,''),CONCAT(COALESCE(U.Cognome,''),' ',COALESCE(U.Nome,''))) as Utente, PT.Tipo, PN.Descrizione AS Motivo_Sospensione, COALESCE(DT.Description,DT2.Description) AS Tipo_Doc 
            FROM partita_tributi AS PT 
            LEFT JOIN enti_gestiti AS E ON E.CC = PT.CC 
            LEFT JOIN utente AS U ON U.ID = PT.Utente_ID 
            LEFT JOIN sospensione_atto AS SA ON PT.ID = SA.Partita_ID 
            LEFT JOIN atto AS A ON SA.ID_Atto_Pigno = A.ID 
            LEFT JOIN pignoramento_generale AS PG ON SA.ID_Atto_Pigno = PG.ID
            LEFT JOIN parametri_notifica AS PN ON PN.ID = SA.Motivo_Sospensione_ID 
            LEFT JOIN document_type AS DT ON DT.Id = A.DocumentTypeId 
            LEFT JOIN document_type AS DT2 ON DT2.Id = PG.DocumentTypeId
            WHERE PT.Flag_Sospensione = 'si' ";

if($CC != "")
    $query .= " AND PT.CC = '".$CC."' ";

if($tipoEntrata != ""){
    $query .= " AND PT.Tipo = '".$tipoEntrata."' ";
}

if($da_partita != ""){
    $query .= " AND PT.Comune_ID >= '".$da_partita."' ";
}

if($a_partita != ""){
    $query .= " AND PT.Comune_ID <= '".$a_partita."' ";
}

if($daco != ""){
    $query .= " AND (U.Ditta >= '".$daco."' OR U.Cognome >= '".$daco."' )";
}

if($dano != ""){
    $query .= " AND U.Nome >= '".$dano."' ";
}

if($acog != ""){
    $query .= " AND (U.Ditta <= '".$daco."' OR U.Cognome <= '".$daco."' )";
}

if($anom != ""){
    $query .= " AND U.Nome <= '".$anom."' ";
}

if($da_anno_crono != ""){
    $query .= " AND A.Anno_Cronologico >= '".$da_anno_crono."' ";
}

if($a_anno_crono != ""){
    $query .= " AND A.Anno_Cronologico <= '".$a_anno_crono."' ";
}

if($da_id_crono != ""){
    $query .= " AND A.ID_Cronologico >= '".$da_id_crono."' ";
}

if($a_id_crono != ""){
    $query .= " AND A.ID_Cronologico <= '".$a_id_crono."' ";
}

//var_dump($query);die;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("CC","Comune","Partita ID","Utente ID","Tipo Entrata","Utente","Tipo Doc","Motivo");
$dataExcel[] = array("<b>Codice Catastale</b>","<b>Comune</b>","<b>Partita ID</b>","<b>Utente ID</b>","<b>Tipo Entrata</b>","<b>Utente</b>","<b>Tipo Doc</b>","<b>Motivo</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent , $percent * 2 , $percent , $percent , $percent , $percent * 2, $percent, $percent);
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
            $result[$i]["Utente"],
            $result[$i]["Tipo_Doc"],
            $result[$i]["Motivo_Sospensione"]
        );



        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["CC"],$result[$i]["Denominazione"],$result[$i]["Partita_ID"],$result[$i]["Utente_ID"],$result[$i]["Tipo"],$result[$i]["Utente"],$result[$i]["Motivo_Sospensione"]);
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
    $nameFILE = "ReportStampeGuidatePartiteSospese.pdf";
    $pathFILE .= "/".$nameFILE;

    $pdf->Output($pathFILE, 'F');
}
else {
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidatePartiteSospese.xlsx";
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


