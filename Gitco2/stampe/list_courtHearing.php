<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include_once(ROOT."/_parameter.php");

//include(INC."/headerAjax.php");

include_once CLS . "/cls_file.php";
include_once CLS . "/cls_print.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_flow.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_file = new cls_file();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$_SESSION['progress'] = "0.00";
session_write_close();

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM enti_gestiti WHERE CC = '".$c."'") );							// 
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	// se serve
$adminCityName = $a_enteAdmin['Denominazione'];	

set_time_limit(-1);


//FILTRI
$filter = array();
$filter['city'] = $cls_help->getVar('city');

$filter['printType'] = null;
$filter['fileType'] = $cls_help->getVar('fileType');

$filter['from_courtHearingDate'] = $cls_help->getVar('from_courtHearingDate');
$filter['to_courtHearingDate'] = $cls_help->getVar('to_courtHearingDate');

$filter['type'] = $cls_help->getVar('type');
$filter['from_surname'] = $cls_help->getVar('from_surname');
$filter['to_surname'] = $cls_help->getVar('to_surname');
$filter['from_name'] = $cls_help->getVar('from_name');
$filter['to_name'] = $cls_help->getVar('to_name');
$filter['from_taxRecord'] = $cls_help->getVar('from_taxRecord');
$filter['to_taxRecord'] = $cls_help->getVar('to_taxRecord');
$filter['from_taxYear'] = $cls_help->getVar('from_taxYear');
$filter['to_taxYear'] = $cls_help->getVar('to_taxYear');
$filter['taxType'] = $cls_help->getVar('taxType');
$filter['taxStopFlag'] = $cls_help->getVar('taxStopFlag');
$filter['dischargeFlag'] = $cls_help->getVar('dischargeFlag');
$filter['from_dischargeDate'] = $cls_help->getVar('from_dischargeDate');
$filter['to_dischargeDate'] = $cls_help->getVar('to_dischargeDate');
$filter['exist_dischargeDate'] = $cls_help->getVar('exist_dischargeDate');

$filter['sort'] = $cls_help->getVar('sort');

$a_type['dirName'] = "appeal";
$a_type['tempFileName'] = "udienze";
$a_type['finalFileName'] = "udienze";
$a_type['title'] = "Udienze";
$a_type['docType'] = "Udienza";
$a_type['type'] = "court_hearing";

//FILE DA SALVARE
$a_fileToSave = array();
if($cls_file->getExtension($filter['fileType']) == 'xls')
    $a_fileToSave['ext'] = 'xlsx';
else
    $a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$a_type['dirName'] );
$a_fileToSave['name'] = $a_type['tempFileName']."_Elenco_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['listPath'] = $cls_file->folderCreation($a_fileToSave['docDir']."/prints");
$a_fileToSave['webListPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['listPath']);

$cls_flow = new cls_flow($c);

$cls_print = new cls_print("print",$filter['type']);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT * FROM v_court_hearing ";
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";
$query.= "AND ".$where." ORDER BY ".$order;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query));
//echo $query;
////print_r($a_results);


$a_headerPage[0] = array("Grado Ricorso","Riferimento ricorso","Tipo ricorso","Data udienza");
$a_headerPage[1] = array("Partita ID","Autorita'","Giudice", "Tipo udienza");
$a_headerPage[2] = array("Utente ID","COD - Contribuente / trasgressore","Indirizzo","CF/PI");

$dataExcel[] = array("<b>Grado ricorso</b>","<b>Riferimento ricorso</b>","<b>Tipo ricorso</b>","<b>Data udienza</b>","<b>Partita ID</b>","<b>Autorità</b>","<b>Giudice</b>","<b>Tipo udienza</b>","<b>Utente ID</b>","<b>COD - Contribuente / Trasgressone</b>","<b>Indirizzo</b>","<b>CF/PI</b>");

if($filter['fileType']=="pdf"){

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco ".$a_type['title']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent , $percent*2.3 , $percent*2.2 , $percent*1.5 );
    $a_align = array( "L" , "L" , "L" , "L" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
}
else if($filter['fileType']=="excel"){

    $a_header = array_merge($a_headerPage[0],$a_headerPage[1],$a_headerPage[2] );
    $a_params = array(
        'creator'=>'sarida',
        'lastModifiedBy'=>$_SESSION['username'],
        'title'=>'Elenco '.$a_type['dirName'],
        'subject'=>'Elenco '.$a_type['dirName'],
        'description'=>'Elenco '.$a_type['dirName'],
        'sheetTitle'=>'Elenco '.$a_type['dirName']
    );

    $xls = new cls_excel();
    $xls->setParameters($a_params);
    $xls->createFile($a_header);

    $xlsRow = 2;
}

$cls_registry = new cls_registry();
$count_res = count($a_results);
if($count_res == 0){
	
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();
	
    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
	
    die;
}
for($i=0;$i<$count_res;$i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count_res ,2);
    session_write_close();

    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);

    switch($a_results[$i]['Authority_Type']){
        case "giudice":         $a_results[$i]['Authority_Type'] = "Giudice di Pace"; break;
        case "tribunale":       $a_results[$i]['Authority_Type'] = "Tribunale";   break;
        case "comm_trib_prov":  $a_results[$i]['Authority_Type'] = "Comm. Trib. Prov.";  break;
        case "comm_trib_reg":   $a_results[$i]['Authority_Type'] = "Comm. Trib. Reg.";    break;
        case "appello":         $a_results[$i]['Authority_Type'] = "Corte d'Appello"; break;
        case "cassazione":      $a_results[$i]['Authority_Type'] = "Corte di Cassazione"; break;
    }
    if($a_results[$i]['Authority_City']!="")
        $a_results[$i]['Authority_Type'].= " - ".$a_results[$i]['Authority_City'];
    if($a_results[$i]['Authority_Section']!="")
        $a_results[$i]['Authority_Type'].= " sez. ".$a_results[$i]['Authority_Section'];

    $a_value[0] = array(
        $a_results[$i]['Court_Level'],
        $a_results[$i]['Atto']." n.".$a_results[$i]['ID_Cronologico']." / ".$a_results[$i]['Anno_Cronologico'],
        $a_results[$i]['Appeal_Type'],
        $cls_help->toItalianDate($a_results[$i]['Court_Hearing_Date'])." ".substr($a_results[$i]['Court_Hearing_Time'],0,5)
    );
    $a_value[1] = array(
        $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC'],
        $a_results[$i]['Authority_Type'],
        $a_results[$i]['Judge'],
        $a_results[$i]['Court_Hearing_Type']
    );

    $a_value[2] = array(
        $a_results[$i]['Utente_Comune_ID'],
        "(".$a_results[$i]['Utente_Comune_ID'].") ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        $a_recipientHeader['address'],
        $a_results[$i]['CF_PI']
    );


    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){
        $a_valueXls = array_merge($a_value[0],$a_value[1],$a_value[2]);
        $dataExcel[] = array_merge($a_value[0],$a_value[1],$a_value[2]);
        $xls->addRow($a_valueXls, $xlsRow);
        $xlsRow++;
    }

}

$cls_file->removeFiles($a_fileToSave['listPath'], 0);

if($filter['fileType']=="pdf"){
    $pdf->addLines();
    //$pdf->setTotalRow("total");
    $a_mainPageParams = array("title"=>strtoupper($adminCityName),"subtitle"=>"ELENCO");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_print->getFiltersDescription($filter);
    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage()+1;
    $recap[1]['label'] = "NUMERO UDIENZE";
    $recap[1]['value'] = count($a_results);
    $pdf->setMainPage($a_filters,$recap);

    $pdf->Output( $a_fileToSave['listPath']."/".$a_fileToSave['name'] , 'F');
}
else if($filter['fileType']=="excel"){
    //$xls->saveFile($a_fileToSave['listPath']."/".$a_fileToSave['name']);
    SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($a_fileToSave['listPath']."/".$a_fileToSave['name']);
}

$file = $a_fileToSave['webListPath']."/".$a_fileToSave['name'];

//var_dump($file);

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);