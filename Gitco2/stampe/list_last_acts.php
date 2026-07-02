<?php

require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//include(INC."/headerAjax.php");

include_once CLS . "/cls_file.php";
include_once CLS . "/cls_print.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_flow.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_notParameters.php";
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

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM enti_gestiti WHERE CC = '".$c."'") );							 
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	
$adminCityName = $a_enteAdmin['Denominazione'];	

$a_docTypes = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM document_type"),"array","Id");
$cls_notPar = new cls_notParameters();
$arrayParams = $cls_db->getResults($cls_db->SelectQuery($cls_notPar->getParametersQuery()),"array","ID");

//FILTRI
$filter = array();
$filter['city'] = $cls_help->getVar('city');

$filter['printType'] = null;
$filter['fileType'] = $cls_help->getVar('fileType');
$filter['sendType'] = $cls_help->getVar('sendType');
$filter['officialType'] = $cls_help->getVar('officialType');

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
$filter['sort'] = $cls_help->getVar('sort');

$filter['paymentActStatus'] = $cls_help->getVar('paymentActStatus');

$filter['PrinterId'] = $cls_help->getVar('PrinterId');
$filter['from_forniture'] = $cls_help->getVar('from_forniture');
$filter['to_forniture'] = $cls_help->getVar('to_forniture');
$filter['from_elaborationDate'] = $cls_help->getVar('from_elaborationDate');
$filter['to_elaborationDate'] = $cls_help->getVar('to_elaborationDate');
$filter['no_elaborationDate'] = $cls_help->getVar('no_elaborationDate');
$filter['from_printDate'] = $cls_help->getVar('from_printDate');
$filter['to_printDate'] = $cls_help->getVar('to_printDate');
$filter['no_printDate'] = $cls_help->getVar('no_printDate');
$filter['from_cronoYear'] = $cls_help->getVar('from_cronoYear');
$filter['to_cronoYear'] = $cls_help->getVar('to_cronoYear');

$a_type['dirName'] = "last_acts";
$a_type['tempFileName'] = "ultimiatti";
$a_type['title'] = "Ultimi atti";

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$a_type['dirName'] );
$a_fileToSave['name'] = $a_type['tempFileName']."_Elenco_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['listPath'] = $cls_file->folderCreation($a_fileToSave['docDir']);
$a_fileToSave['webListPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['listPath']);

$cls_flow = new cls_flow($c);

$a_city = array("cc"=>$c, "city"=>$adminCity);
$cls_print = new cls_print("list",$filter['type'],$a_city);
$where = $cls_print->getWhereFromFilters($filter);

$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT * FROM v_list_docs ";
$query.= "WHERE 1=1 ";
$query.= "AND ".$where." ORDER BY ".$order;

// echo $query;
// die;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query));
$countPositions=0;
////print_r($a_results);

$par_annuali = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM parametri_annuali WHERE CC='".$c."' ORDER BY Anno DESC LIMIT 1"));

if($filter['fileType']=="pdf"){

    $a_headerPage[0] = array("Informazioni Cartella","Partita","Utente","Totali 1-2-3","Totale");
    $a_headerPage[1] = array("Tipo atto", "Cronologico","CF/PI","Data Stampa - Notifica","Pagato");

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco ".$a_type['title']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent*3 , $percent*2/3 , $percent*4/3 , $percent*4/3 , $percent*2/3 );
    $a_align = array( "L" , "L" , "L" , "L" , "R" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
    
    $a_totalsVar = array(0=>array(4),1=>array(4));
    $a_totalsHeader = array(
        0=>array("{TOTALE}","","","Totale dovuto","{0}"),
        1=>array("","","","Totale pagato","{1}")
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
        'title'=>'Elenco '.$a_type['title'],
        'sheetTitle'=>'Elenco '.$a_type['title']
    );

    $xls = new cls_excel();
    $xls->setParameters($a_params);
    $xls->createFile($a_header);

    $xlsRow = 2;
}

$cls_ruolo = new cls_ruolo();
$cls_registry = new cls_registry();

$cont = 0;
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

    if(!$a_results[$i]['TOTALE_PAGAMENTI']>0)
    $a_results[$i]['TOTALE_PAGAMENTI'] = 0.00;

    if($a_results[$i]['Rate_Previste']>0){
        if($a_results[$i]['Totale_Rateizzato']>0)
            $checkTotale = $a_results[$i]['Totale_Rateizzato'];
        else if($a_results[$i]['Tipo_Totale_Rate']==1)
            $checkTotale = $a_results[$i]['TOTALE1'];
        else if($a_results[$i]['Tipo_Totale_Rate']==2)
            $checkTotale = $a_results[$i]['TOTALE2'];
        else if($a_results[$i]['Tipo_Totale_Rate']==3)
            $checkTotale = $a_results[$i]['TOTALE3'][3];
    }
    else{
        if($a_results[$i]['TableTypeId']==1){
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
        else{
            if($a_results[$i]['TOTALE3']>0)
                $checkTotale = $a_results[$i]['TOTALE3'];
            else if($a_results[$i]['TOTALE2']>0)
                $checkTotale = $a_results[$i]['TOTALE2'];
            else
                $checkTotale = $a_results[$i]['TOTALE1'];
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
        $totDovuto = number_format($checkTotale,2,",","")." Euro";
    else
        $totDovuto = "0,00 Euro";

    $totaliStr = "1: ".$a_results[$i]['TOTALE1'];
    if($a_results[$i]['TOTALE2']>0)
        $totaliStr.= " - 2: ".$a_results[$i]['TOTALE2'];
    if($a_results[$i]['TOTALE3']>0)
        $totaliStr.= " - 3: ".$a_results[$i]['TOTALE3'];

    $a_value[0] = array(
        $a_results[$i]['Info_Cartella'],
        $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC'],
        "(".$a_results[$i]["Utente_Comune_ID"].") ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        $totaliStr,
        $totDovuto
    );
    if(!empty($a_results[$i]['TOTALE_PAGAMENTI']))
        $totPagato = number_format($a_results[$i]['TOTALE_PAGAMENTI'],2,",","")." Euro";
    else
        $totPagato = "0,00 Euro";
    $a_value[1] = array(
        $a_results[$i]['DocumentType'],
        $a_results[$i]['ID_Cronologico']." / ".$a_results[$i]['Anno_Cronologico'],
        $a_results[$i]['CF_PI'],        
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

    $countPositions++;
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

$file = $a_fileToSave['webListPath']."/".$a_fileToSave['name'];

//var_dump($file);

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);