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
include_once CLS . "/cls_split_payment.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";


$cls_file = new cls_file();
$cls_db = new cls_db();
$cls_help = new cls_help();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

set_time_limit(-1);

$_SESSION['progress'] = "0.00";
session_write_close();

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM enti_gestiti WHERE CC = '".$c."'") );							
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	
$adminCityName = $a_enteAdmin['Denominazione'];	


//FILTRI
$filter = array();
$filter['city'] = $c;

$filter['printType'] = null;
$filter['fileType'] = $cls_help->getVar('fileType');
$filter['sendType'] = $cls_help->getVar('sendType');
$filter['officialType'] = $cls_help->getVar('officialType');

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
$filter['no_notificationDate'] = $cls_help->getVar('no_notificationDate');
$filter['from_flowDate'] = $cls_help->getVar('from_flowDate');
$filter['to_flowDate'] = $cls_help->getVar('to_flowDate');
$filter['no_flowDate'] = $cls_help->getVar('no_flowDate');
$filter['flowNumber'] = $cls_help->getVar('flowNumber');
$filter['flowYear'] = $cls_help->getVar('flowYear');

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
$filter['sort'] = $cls_help->getVar('sort');

$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($filter['docType'],$filter['sendType']);

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$cls_ruolo->a_docDetails['dirName'] );
$a_fileToSave['name'] = $cls_ruolo->a_docDetails['tempFileName']."_Elenco_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['listPath'] = $cls_file->folderCreation($a_fileToSave['docDir']."/Elenchi");
$a_fileToSave['webListPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['listPath']);

$cls_flow = new cls_flow($c);

$cls_print = new cls_print("list",$filter['type']);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);
$query = "SELECT * FROM v_atti ";
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";
$query.= "AND ".$where." AND Atto='".$cls_ruolo->a_docDetails['docType']."' ORDER BY ".$order;


$a_results = $cls_db->getResults($cls_db->SelectQuery($query));

$a_headerPage[0] = array("Cronologico","COD-Utente","Imp. Princ.","Sp. Not. Prec.","Interessi","Oneri Risc.","Dovuto");
$a_headerPage[1] = array("Partita","Indirizzo","Sp. Ric.","Sp. Not.","Altri Dir.","Add. Com.","Pagato");
$a_headerPage[2] = array("CF/PI","Informazioni Cartella","Sp. Accert.","Sanzioni","ECA","Add. Prov.","");

if($filter['fileType']=="pdf"){

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco ".$cls_ruolo->a_docDetails['dirName']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/13*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent*2 , $percent*5 , $percent*1.2 , $percent*1.2 , $percent*1.2, $percent*1.2 , $percent*1.2 );
    $a_align = array( "L" , "L" , "R" , "R" , "R" ,"R" ,"R" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage(9);
    $a_width = array( $percent*7 , $percent*1.2 , $percent*1.2 , $percent*1.2, $percent*1.2 , $percent*1.2 );
    $a_align = array( "L" , "R" , "R", "R", "R", "R" );
    $a_totalsVar = array(
            0=>array(2,3,4,5,6),
            1=>array(2,3,4,5,6),
            2=>array(2,3,4,5,6)
        );
    $a_totalsHeader = array(
        0=>array("{TOTALE}","{0}","{1}","{2}","{3}","{4}"),
        1=>array("","{5}","{6}","{7}","{8}","{9}"),
        2=>array("","{10}","{11}","{12}","{13}","{14}")
    );
    $pdf->setArray($a_width,"a_width_totals");
    $pdf->setArray($a_align,"a_align_totals");
    $pdf->setArray($a_totalsVar,"a_totalsVar");
    $pdf->setArray($a_totalsHeader,"a_totalsHeader");
}
else if($filter['fileType']=="excel"){
    $a_headerPage[3] = array("Flusso","Nome Flusso","Data Flusso","Codice Catastale","","","");
    $a_header = array_merge($a_headerPage[0],$a_headerPage[1],$a_headerPage[2],$a_headerPage[3]);
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

$cls_registry = new cls_registry();
$cls_splitPayment = new cls_split_payment();
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
for($i=0;$i<count($a_results);$i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count_res ,2);
    session_write_close();

    $cls_ruolo->setResultArray($a_results[$i]);

    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);
    $references = $cls_ruolo->getReferences();

    $exp_datePag = explode("*",$a_results[$i]['Date_Pagamenti']);
    if($cls_help->toDbDate($a_results[$i]['Data_Notifica'])==null){
        $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Minimo'];
        $oneri_riscossione = $a_results[$i]['Diritto_Riscossione_Minimo'];
    }
    else{
        $data_not = new DateTime($a_results[$i]['Data_Notifica']);
        $data_not->modify("+2 months");
        if($exp_datePag[0]!=null){
            if ($exp_datePag[0] > $data_not->format('Y-m-d')){
                $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Massimo'];
                $oneri_riscossione = $a_results[$i]['Diritto_Riscossione_Massimo'];
            }
            else{
                $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Minimo'];
                $oneri_riscossione = $a_results[$i]['Diritto_Riscossione_Minimo'];
            }
        }
        else{
            if (date("Y-m-d") > $data_not->format('Y-m-d')){
                $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Massimo'];
                $oneri_riscossione = $a_results[$i]['Diritto_Riscossione_Massimo'];
            }
            else{
                $a_results[$i]['Totale_Dovuto'] += $a_results[$i]['Diritto_Riscossione_Minimo'];
                $oneri_riscossione = $a_results[$i]['Diritto_Riscossione_Minimo'];
            }
        }
    }
    if(!$a_results[$i]['Totale_Pagato']>0)
        $a_results[$i]['Totale_Pagato'] = 0.00;
    if(!$a_results[$i]['Totale_Dovuto']>0)
        $a_results[$i]['Totale_Dovuto'] = 0.00;

    $a_splitAmount = array();
    $a_codiciTributo['Codici'] = explode('*', $a_results[$i]['Codici_Scorporo']);
    $a_codiciTributo['Importi'] = explode('*', $a_results[$i]['Importi_Codici_Tributo']);
    $a_splitAmount = $cls_splitPayment->splitAmount($a_codiciTributo);

    $a_amounts = array(
        "Importo_Principale"=>$cls_help->floatToString($a_splitAmount[1]),
        "Spese_Ricerca"=>$cls_help->floatToString($a_splitAmount[12]),
        "Spese_Accertamento"=>$cls_help->floatToString($a_splitAmount[2]),
        "Spese_Not_Precedenti"=>$cls_help->floatToString($a_splitAmount[3]+$a_splitAmount[4]+$a_results[$i]['Spese_Notifica_Precedenti']),
        "Spese_Not"=>$cls_help->floatToString($a_results[$i]['Spese_Notifica']),
        "Sanzione"=>$cls_help->floatToString($a_splitAmount[7]+$a_splitAmount[8]+$a_splitAmount[9]),
        "Interessi"=>$cls_help->floatToString($a_splitAmount[10]+$a_results[$i]['Interessi']+$a_results[$i]['Interessi_Precedenti']),
        "Altri_diritti"=>$cls_help->floatToString($a_splitAmount[11]),
        "ECA"=>$cls_help->floatToString($a_splitAmount[15]),
        "Oneri_Riscossione"=>$cls_help->floatToString($a_splitAmount[14]+$oneri_riscossione),
        "Addizionale_Comunale"=>$cls_help->floatToString($a_splitAmount[16]),
        "Addizionale_Provinciale"=>$cls_help->floatToString($a_splitAmount[13])
    );

    $a_value[0] = array(
        $a_results[$i]['ID_Cronologico']." / ".$a_results[$i]['Anno_Cronologico'],
        "(".$a_results[$i]["Utente_Comune_ID"].") ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        $a_amounts['Importo_Principale'],
        $a_amounts['Spese_Not_Precedenti'],
        $a_amounts['Interessi'],
        $a_amounts['Oneri_Riscossione'],
        $cls_help->floatToString($a_results[$i]['Totale_Dovuto'])
    );
    $a_value[1] = array(
        $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC'],
        $a_recipientHeader['address'],
        $a_amounts['Spese_Ricerca'],
        $a_amounts['Spese_Not'],
        $a_amounts['Altri_diritti'],
        $a_amounts['Addizionale_Comunale'],
        $cls_help->floatToString($a_results[$i]['Totale_Pagato'])
    );
    $a_value[2] = array(
        $a_results[$i]['CF_PI'],
        $a_results[$i]['Info_Cartella'],
        $a_amounts['Spese_Accertamento'],
        $a_amounts['Sanzione'],
        $a_amounts['ECA'],
        $a_amounts['Addizionale_Provinciale'],
        ""
    );

    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value,7.5,9);
    else if($filter['fileType']=="excel"){
        for($y=0;$y<count($a_value);$y++){
            for($z=2;$z<count($a_value[$y]);$z++){
                if($a_value[$y][$z]!="")
                    $a_value[$y][$z] = $cls_help->stringToFloat($a_value[$y][$z]);
            }
        }

        $a_params = array(
            "flowNumber"=>$a_results[$i]['Numero_Flusso'],
            "flowYear"=>$a_results[$i]['Anno_Flusso'],
            "flowCC"=>$a_results[$i]['CC'],
            "flowDate"=>$cls_help->toItalianDate($a_results[$i]['Data_Flusso']),
            "docType"=>$a_results[$i]['Atto']
        );
        $a_value[3] = array(
            $a_results[$i]['Numero_Flusso']."/".$a_results[$i]['Anno_Flusso'],
            $cls_flow->getFlowName($a_params),
            $a_results[$i]['Data_Flusso'],
            $a_results[$i]['CC'],
            "",
            "",
            ""
        );
        $a_valueXls = array_merge($a_value[0],$a_value[1],$a_value[2],$a_value[3]);
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
    $recap[1]['value'] = count($a_results);
    $pdf->setMainPage($a_filters,$recap);

    $pdf->Output( $a_fileToSave['listPath']."/".$a_fileToSave['name'] , 'F');
}
else if($filter['fileType']=="excel"){
    $xls->saveFile($a_fileToSave['listPath']."/".$a_fileToSave['name']);
}

$prev = $_SERVER['HTTP_REFERER'];
//var_dump($prev);die;
$file = $a_fileToSave['webListPath']."/".$a_fileToSave['name'];
//var_dump($file);die;

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);