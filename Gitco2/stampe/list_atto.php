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

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_file = new cls_file();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

set_time_limit(-1);


$_SESSION['progress'] = "0.00";
session_write_close();


//FILTRI
$filter = array();
$filter['city'] = $cls_help->getVar('city');

$filter['printType'] = null;
$filter['from_cronoYear'] = $cls_help->getVar('from_cronoYear');
$filter['to_cronoYear'] = $cls_help->getVar('to_cronoYear');
$filter['fileType'] = $cls_help->getVar('fileType');
$filter['PrintTypeId'] = $cls_help->getVar('PrintTypeId');
$filter['officialType'] = $cls_help->getVar('officialType');
$filter['PrinterId'] = $cls_help->getVar('PrinterId');

$filter['docType'] = $cls_help->getVar('docType');
$filter['printStatus'] = $cls_help->getVar('printStatus');
$filter['from_elaborationDate'] = $cls_help->getVar('from_elaborationDate');
$filter['to_elaborationDate'] = $cls_help->getVar('to_elaborationDate');
$filter['no_elaborationDate'] = $cls_help->getVar('no_elaborationDate');
$filter['from_printDate'] = $cls_help->getVar('from_printDate');
$filter['to_printDate'] = $cls_help->getVar('to_printDate');
$filter['no_printDate'] = $cls_help->getVar('no_printDate');
$filter['from_notificationDate'] = $cls_help->getVar('from_notificationDate');
$filter['to_notificationDate'] = $cls_help->getVar('to_notificationDate');
$filter['exist_notificationDate'] = $cls_help->getVar('exist_notificationDate');
$filter['from_flowDate'] = $cls_help->getVar('from_flowDate');
$filter['to_flowDate'] = $cls_help->getVar('to_flowDate');
$filter['exist_flowDate'] = $cls_help->getVar('exist_flowDate');
$filter['flowNumber'] = $cls_help->getVar('flowNumber');
$filter['flowYear'] = $cls_help->getVar('flowYear');

$filter['TrafficLaw'] = $cls_help->getVar('TrafficLaw');
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
$filter['blockSingleAct'] = $cls_help->getVar('blockSingleAct');
$filter['from_dischargeDate'] = $cls_help->getVar('from_dischargeDate');
$filter['to_dischargeDate'] = $cls_help->getVar('to_dischargeDate');
$filter['exist_dischargeDate'] = $cls_help->getVar('exist_dischargeDate');
$filter['paymentActStatus'] = $cls_help->getVar('paymentActStatus');

$filter['sort'] = $cls_help->getVar('sort');

//print_r($filter);
//die;

$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($filter['docType'],$filter['PrintTypeId']);

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$cls_ruolo->a_docDetails['dirName'] );
$a_fileToSave['name'] = $cls_ruolo->a_docDetails['tempFileName']."_Elenco_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['listPath'] = $cls_file->folderCreation($a_fileToSave['docDir']."/Elenchi");
$a_fileToSave['webListPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['listPath']);

$cls_flow = new cls_flow($c);
//$cls_db = new cls_db();
$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM enti_gestiti WHERE CC = '".$c."'") );
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";
$adminCityName = $a_enteAdmin['Denominazione'];

$a_city = array("cc"=>$c, "city"=>$adminCity);
$cls_print = new cls_print("list",$filter['type'],$a_city);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT * FROM v_list_atto ";
$query.= "WHERE 1=1 ";
$query.= "AND ".$where." AND DocumentTypeId=".$cls_ruolo->a_docDetails['DocumentTypeId']." ORDER BY ".$order;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query));
// echo $query;
// die;
//print_r($a_results);

$par_annuali = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM parametri_annuali WHERE CC='".$c."' ORDER BY Anno DESC LIMIT 1"));

$a_headerPage[0] = array("Cronologico","Utente","Indirizzo","Data Elab. - Calcolo","Dovuto");
$a_headerPage[1] = array("Partita","CF/PI","Informazioni Cartella","Data Stampa - Notif.","Pagato");

if($filter['fileType']=="pdf"){

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco ".$cls_ruolo->a_docDetails['dirName']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent , $percent , $percent*3 , $percent , $percent );
    $a_align = array( "L" , "L" , "L" , "L" , "R" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
    $a_width = array( $percent*5 , $percent, $percent );
    $a_align = array( "L" , "L" , "R" );
    $a_totalsVar = array(0=>array(4),1=>array(4));
    $a_totalsHeader = array(
        0=>array("{TOTALE}","Totale dovuto","{0}"),
        1=>array("","Totale pagato","{1}")
    );
    $pdf->setArray($a_width,"a_width_totals");
    $pdf->setArray($a_align,"a_align_totals");
    $pdf->setArray($a_totalsVar,"a_totalsVar");
    $pdf->setArray($a_totalsHeader,"a_totalsHeader");
}
else if($filter['fileType']=="excel"){

    $a_headerPage[2] = array("Flusso","Nome Flusso","Data Flusso","Codice Catastale","");
    $a_header = array_merge(array("Ente"),$a_headerPage[0],$a_headerPage[1],$a_headerPage[2] );
    $a_params = array(
        'creator'=>'sarida',
        'lastModifiedBy'=>$_SESSION['username'],
        'title'=>'Elenco '.$cls_ruolo->a_docDetails['dirName'],
        'subject'=>'Elenco '.$cls_ruolo->a_docDetails['dirName'],
        'description'=>'Elenco '.$cls_ruolo->a_docDetails['dirName'],
        'sheetTitle'=>'Elenco '.$cls_ruolo->a_docDetails['dirName']
    );

    $xls = new cls_excel();
    $xls->setParameters($a_params);
    $xls->createFile($a_header);

    $xlsRow = 2;
}
$cont = 0;
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
//var_dump($a_results);die;
for($i=0;$i<$count_res;$i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count_res ,2);
    session_write_close();
    //session_start();
    /*if($i==15)
    {
        var_dump($_SESSION["progress"]);die;
    }*/

    $cls_ruolo->setResultArray($a_results[$i]);

    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);
    $references = $cls_ruolo->getReferences();

    // $exp_datePag = explode("*",$a_results[$i]['Date_Pagamenti']);
    // if($cls_help->toDbDate($a_results[$i]['Data_Notifica'])==null)
    //     $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Minimo'];
    // else{
    //     $data_not = new DateTime($a_results[$i]['Data_Notifica']);
    //     $data_not->modify("+2 months");
    //     if($exp_datePag[0]!=null){
    //         if ($exp_datePag[0] > $data_not->format('Y-m-d'))
    //             $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Massimo'];
    //         else
    //             $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Minimo'];
    //     }
    //     else{
    //         if (date("Y-m-d") > $data_not->format('Y-m-d'))
    //             $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Massimo'];
    //         else
    //             $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Minimo'];
    //     }
    // }

    if(!$a_results[$i]['TOTALE_PAGAMENTI']>0)
        $a_results[$i]['TOTALE_PAGAMENTI'] = 0.00;
    if(!$a_results[$i]['Totale_Dovuto']>0)
        $checkTotale = 0.00;

    if($a_results[$i]['Rate_Previste']>0){
        if($a_results[$i]['Totale_Rateizzato']>0)
            $checkTotale = $a_results[$i]['Totale_Rateizzato'];
        else if($a_results[$i]['Tipo_Totale_Rate']==1)
            $checkTotale = $a_results[$i]['TOTALE1'];
        else if($a_results[$i]['Tipo_Totale_Rate']==2)
            $checkTotale = $a_results[$i]['TOTALE2'];
    }
    else{
        if(empty($a_results[$i]['Data_Notifica']))
            $checkTotale = $a_results[$i]['TOTALE1'];
        else{
            $checkDate = date('Y-m-d', strtotime($a_results[$i]['Data_Notifica']. ' + 60 days'));
            if($a_results[$i]['TOTALE_PAGAMENTI']>0 && $a_results[$i]['Data_Pagamento']<$checkDate)
                $checkTotale = $a_results[$i]['TOTALE1'];
            else
                $checkTotale = $a_results[$i]['TOTALE2'];
    
            if($a_results[$i]['TOTALE_PAGAMENTI']>0){
                if($checkTotale >$a_results[$i]['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']){
                    if(date('Y-m-d')>$checkDate)
                        $checkTotale = $a_results[$i]['TOTALE2'];
                }
            }
        }
    }

    switch($filter['paymentActStatus']){
        case "incompleted":
            if(!($a_results[$i]['TOTALE_PAGAMENTI']<=0) && 
            !($a_results[$i]['TOTALE_PAGAMENTI']>0 && $checkTotale > $a_results[$i]['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']))
                continue 2;
            break;
        case "partial":
            if(!($a_results[$i]['TOTALE_PAGAMENTI']>0 && $checkTotale > $a_results[$i]['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']))
                continue 2;
            break;
        case "completed":
            if($checkTotale > $a_results[$i]['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo'])
                continue 2;
            break;
    }

    $cont++;

    if(!empty($checkTotale))
        $totDovuto = number_format($checkTotale,2,",","");
    else
        $totDovuto = "0,00";

    $a_value[0] = array(
        $a_results[$i]['ID_Cronologico']." / ".$a_results[$i]['Anno_Cronologico'],
        "(".$a_results[$i]["Utente_Comune_ID"].") ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        $a_recipientHeader['address'],
        $cls_help->toItalianDate($a_results[$i]['Data_Elaborazione'])." - ".$cls_help->toItalianDate($a_results[$i]['Data_Calcolo_Interessi']),
        $totDovuto
    );
    if(!empty($a_results[$i]['TOTALE_PAGAMENTI']))
        $totPagato = number_format($a_results[$i]['TOTALE_PAGAMENTI'],2,",","");
    else
        $totPagato = "0,00";
    $a_value[1] = array(
        $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC'],
        $a_results[$i]['CF_PI'],
        $a_results[$i]['Info_Cartella'],
        $cls_help->toItalianDate($a_results[$i]['Data_Stampa'])." - ".$cls_help->toItalianDate($a_results[$i]['Data_Notifica']),
        $totPagato
    );

    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value,8,10,50);
    else if($filter['fileType']=="excel"){
        $a_value[0][4] = $checkTotale;
        $a_value[1][4] = $a_results[$i]['TOTALE_PAGAMENTI'];
        $a_params = array(
            "flowNumber"=>$a_results[$i]['Numero_Flusso'],
            "flowYear"=>$a_results[$i]['Anno_Flusso'],
            "flowCC"=>$a_results[$i]['CC'],
            "flowDate"=>$cls_help->toItalianDate($a_results[$i]['Data_Flusso']),
            "docType"=>$a_results[$i]['Atto']
        );
        $a_value[2] = array(
            $a_results[$i]['Numero_Flusso']."/".$a_results[$i]['Anno_Flusso'],
            $cls_flow->getFlowName($a_params),
            $a_results[$i]['Data_Flusso'],
            $a_results[$i]['CC'],
            ""
        );
        $a_valueXls = array_merge(array($a_results[$i]['Denominazione_Ente']), $a_value[0],$a_value[1],$a_value[2]);
        $xls->addRow($a_valueXls, $xlsRow);
        $xlsRow++;
    }
    
}

$cls_file->removeFiles($a_fileToSave['listPath'], 0);

if($filter['fileType']=="pdf"){
    $pdf->addLines();
    $pdf->setTotalRow("total");
    $a_mainPageParams = array("title"=>strtoupper($adminCityName),"subtitle"=>"ELENCO");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_print->getFiltersDescription($filter);
    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage()+1;
    $recap[1]['label'] = "NUMERO ATTI";
    $recap[1]['value'] = $cont;
    $pdf->setMainPage($a_filters,$recap);

    $pdf->Output( $a_fileToSave['listPath']."/".$a_fileToSave['name'] , 'F');
}
else if($filter['fileType']=="excel"){
    $xls->saveFile($a_fileToSave['listPath']."/".$a_fileToSave['name']);
}

//$prev = $_SERVER['HTTP_REFERER'];
//var_dump($prev);die;
$file = $a_fileToSave['webListPath']."/".$a_fileToSave['name'];
//var_dump($file);die;

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);