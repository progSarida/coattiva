<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_help.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_query.php";
include_once CLS . "/cls_print.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_flow.php";
include_once CLS . "/cls_notParameters.php";
//include(INC."/header.php");

include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$_SESSION['progress'] = "0.00";
session_write_close();

/*$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM enti_gestiti WHERE CC = '".$c."'") );							 
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	
$adminCityName = $a_enteAdmin['Denominazione'];	*/

$cls_notPar = new cls_notParameters();
$arrayParams = $cls_db->getResults($cls_db->SelectQuery($cls_notPar->getParametersQuery()));
$a_notParams = array();
for($i=0;$i<count($arrayParams);$i++){
    $a_notParams[$arrayParams[$i]['ID']] = $arrayParams[$i];
}

//FILTRI
$filter = array();

$filter['city'] = $cls_help->getVar('city');

$filter['listType'] = $cls_help->getVar('listType');
$filter['fileType'] = $cls_help->getVar('fileType');
$filter['type'] = "esiti";
$filter['actType'] = $cls_help->getVar('actType');
$filter['lastAct'] = $cls_help->getVar('lastAct');
$filter['payment'] = $cls_help->getVar('payment');
$filter['from_surname'] = $cls_help->getVar('from_surname');
$filter['to_surname'] = $cls_help->getVar('to_surname');
$filter['from_name'] = $cls_help->getVar('from_name');
$filter['to_name'] = $cls_help->getVar('to_name');
$filter['from_taxRecord'] = $cls_help->getVar('from_taxRecord');
$filter['to_taxRecord'] = $cls_help->getVar('to_taxRecord');
$filter['from_taxYear'] = $cls_help->getVar('from_taxYear');
$filter['to_taxYear'] = $cls_help->getVar('to_taxYear');
$filter['from_flowDate'] = $cls_help->getVar('from_flowDate');
$filter['to_flowDate'] = $cls_help->getVar('to_flowDate');
$filter['exist_flowDate'] = $cls_help->getVar('exist_flowDate');
$filter['flowNumber'] = $cls_help->getVar('flowNumber');
$filter['flowYear'] = $cls_help->getVar('flowYear');
$filter['from_notificationDate'] = $cls_help->getVar('from_notificationDate');
$filter['to_notificationDate'] = $cls_help->getVar('to_notificationDate');
$filter['exist_notificationDate'] = $cls_help->getVar('exist_notificationDate');
$filter['taxType'] = $cls_help->getVar('taxType');
$filter['taxStopFlag'] = $cls_help->getVar('taxStopFlag');
$filter['dischargeFlag'] = $cls_help->getVar('dischargeFlag');
$filter['from_dischargeDate'] = $cls_help->getVar('from_dischargeDate');
$filter['to_dischargeDate'] = $cls_help->getVar('to_dischargeDate');
$filter['exist_dischargeDate'] = $cls_help->getVar('exist_dischargeDate');
$filter['notificationAndAnomaly'] = $cls_help->getVar('notificationAndAnomaly');
$filter['notificationMode'] = $cls_help->getVar('notificationMode');
$filter['notificationStock'] = $cls_help->getVar('notificationStock');
$filter['notificationAnomaly'] = $cls_help->getVar('notificationAnomaly');
$filter['importNotification'] = $cls_help->getVar('importNotification');
$filter['notificationImg'] = $cls_help->getVar('notificationImg');
//$filter['notificationAnomalyAtto'] = $cls_help->getVar('notificationAnomalyAtto');
$filter['flow'] = $cls_help->getVar('flow');
$filter['sort'] = $cls_help->getVar('sort');

$cls_file = new cls_file();
$cls_flow = new cls_flow($c);

$_SESSION['progress'] = "0.00";
session_write_close();

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM enti_gestiti WHERE CC = '".$c."'") );							// 
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	// se serve
$adminCityName = $a_enteAdmin['Denominazione'];

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['folder'] = $cls_file->folderCreation( ATTI ."/". $c . "/Elenchi" );
$a_fileToSave['name'] = "elenco_esiti_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['rootPath'] = $a_fileToSave['folder']."/".$a_fileToSave['name'];
$a_fileToSave['webPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootPath']);

$a_city = array("cc"=>$c, "city"=>$adminCity);
$cls_print = new cls_print("list",$filter['type'],$a_city);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);

//$cls_query = new cls_query();
//$where = $cls_query->getWhereFromFilters($filter);
//$order = $cls_query->getOrder($filter['sort']);


$query = "SELECT v_notifiche.*, SUM(v_notifiche.Pagamenti_Atto) AS Totale_Pagamenti FROM v_notifiche ";
$query.= "WHERE 1=1 ";
$query.= "AND ".$where." AND (Cronologico_Vecchio!='si' OR Cronologico_Vecchio is null) ";

if($filter['lastAct']=="last")
    $query.= "GROUP BY Partita_ID ";
else
    $query.= "GROUP BY ID, DocumentTypeId ";

$query.= "ORDER BY ".$order;

if($_SESSION['username']=="mirkop" || $_SESSION['username']=="gianluca"){
    echo $query;
    //die;
}


$a_notifications = $cls_db->getResults($cls_db->SelectQuery($query));
if(!count($a_notifications)>0){
    echo "<script>noResultsBar();</script>";
    die;
}

$a_headerPage[0] = array("Tipo atto","COD-Utente","Partita","Info partita","Img fronte");
$a_headerPage[1] = array("Cronologico","CF/PI","Data Notifica","Tipo / Stato notifica","Img retro");

if($filter['fileType']=="pdf"){

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco esiti");

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent , $percent , $percent , $percent*3 , $percent );
    $a_align = array( "L" , "L" , "L" , "L" , "L" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();

}
else if($filter['fileType']=="excel"){


    $a_headerPage[2] = array("Flusso","Nome Flusso","Data Flusso","Codice Catastale","Modalita Notifica");
    $a_headerPage[3] = array("Stato giacenza","Anomalia notifica","Totale MAX Dovuto","Anno flusso","Pagamenti atto");
    $a_headerPage[4] = array("Pagamenti totali partita","","","","");
    if($filter['lastAct']=="last")
        $a_header = array_merge($a_headerPage[0],$a_headerPage[1],$a_headerPage[2],$a_headerPage[3],$a_headerPage[4]);
    else
        $a_header = array_merge($a_headerPage[0],$a_headerPage[1],$a_headerPage[2],$a_headerPage[3]);
    $a_params = array(  'creator'=>'sarida',
                        'lastModifiedBy'=>$_SESSION['username'],
                        'title'=>'Elenco esiti',
                        'subject'=>'Elenco esiti',
                        'description'=>'Elenco notifiche',
                        'sheetTitle'=>'Elenco esiti'
                    );

    $xls = new cls_excel();
    $xls->setParameters($a_params);
    $xls->createFile($a_header);

    $xlsRow = 2;
}
$contaNotif = 0;
$count = count($a_notifications);
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
for($i=0;$i<$count;$i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    $notificationMode = $notificationStock = $notificationAnomaly = "";
    if($a_notifications[$i]['Modalita_Notifica']>0)
        $notificationMode = $a_notifications[$i]['Modalita_Notifica']." - ".$a_notParams[$a_notifications[$i]['Modalita_Notifica']]['Descrizione'];
    if($a_notifications[$i]['Stato_Notifica']>0)
        $notificationStock = $a_notifications[$i]['Stato_Notifica']." - ".$a_notParams[$a_notifications[$i]['Stato_Notifica']]['Descrizione'];
    if($a_notifications[$i]['Motivo_Notifica']>0)
        $notificationAnomaly = $a_notifications[$i]['Motivo_Notifica']." - ".$a_notParams[$a_notifications[$i]['Motivo_Notifica']]['Descrizione'];

    $query = "SELECT Comune_ID FROM utente WHERE ID = '".$a_notifications[$i]["Utente_ID"]."' AND CC_Comune = '".$a_notifications[$i]["CC"]."'";
    $Utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");

    $a_img = array(
        "front_name" => $a_notifications[$i]['Not_Front_Image'],
        "path_front"=>IMMAGINI_NOTIFICHE."/".$a_notifications[$i]['CC']."/".$a_notifications[$i]['Not_Front_Image'],
        "front"=>IMMAGINI_NOTIFICHE_WEB."/".$a_notifications[$i]['CC']."/".$a_notifications[$i]['Not_Front_Image'],
        "rear_name" => $a_notifications[$i]['Not_Rear_Image'],
        "path_rear"=>IMMAGINI_NOTIFICHE."/".$a_notifications[$i]['CC']."/".$a_notifications[$i]['Not_Rear_Image'],
        "rear"=>IMMAGINI_NOTIFICHE_WEB."/".$a_notifications[$i]['CC']."/".$a_notifications[$i]['Not_Rear_Image']
    );

    if(($a_img['front_name']!="") || ($a_img['rear_name']!="")) {
        if (($filter['notificationImg'] == "n" || $filter['notificationImg'] == "db") && is_file($a_img['path_front']) && is_file($a_img['path_rear']))
            continue;
        if ($filter['notificationImg'] == "y" && !is_file($a_img['path_front']) && !is_file($a_img['path_rear']))
            continue;
    }
    else{
        if($filter['notificationImg']=="y")
            continue;
        if($filter['notificationImg']=="db")
            continue;
    }

    $a_value[0] = array(    $a_notifications[$i]['DocumentType'],
                            "(".$Utente->Comune_ID.") ".$a_notifications[$i]['Cognome_Ditta']." ".$a_notifications[$i]['Nome'],
                            $a_notifications[$i]['Comune_ID']." / ".$a_notifications[$i]['CC'],
                            $a_notifications[$i]['Info_Cartella'],
                            $a_notifications[$i]['Not_Front_Image']
                        );
    $a_value[1] = array(    $a_notifications[$i]['ID_Cronologico']." / ".$a_notifications[$i]['Anno_Cronologico'],
                            $a_notifications[$i]['CF_PI'],
                            $cls_help->toItalianDate($a_notifications[$i]['Data_Notifica']),
                            $a_notifications[$i]['Not_Tipo_Notifica']." / ".$a_notifications[$i]['Not_Stato_Notifica'],
                            $a_notifications[$i]['Not_Rear_Image']
                        );
    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){

        $a_params = array(
                            "flowNumber"=>$a_notifications[$i]['Numero_Flusso'],
                            "flowYear"=>$a_notifications[$i]['Anno_Flusso'],
                            "flowCC"=>$a_notifications[$i]['CC'],
                            "flowDate"=>$a_notifications[$i]['Data_Flusso'],
                            "docType"=>$a_notifications[$i]['DocumentType']
        );
        $a_value[2] = array(
                            $a_notifications[$i]['Numero_Flusso']."/".$a_notifications[$i]['Anno_Flusso'],
                            $cls_flow->getFlowName($a_params),
                            $a_notifications[$i]['Data_Flusso'],
                            $a_notifications[$i]['CC'],
                            $notificationMode
                        );
        $a_value[3] = array(
                            $notificationStock,
                            $notificationAnomaly,
                            $a_notifications[$i]['Totale_Dovuto'],
                            $a_notifications[$i]['Anno_Flusso'],
                            $a_notifications[$i]['Pagamenti_Atto']
                        );
        if($filter['lastAct']=="last"){
            $a_value[4] = array(
                $a_notifications[$i]['Totale_Pagamenti'],
                "",
                "",
                "",
                ""
            );
            $a_valueXls = array_merge($a_value[0],$a_value[1],$a_value[2],$a_value[3],$a_value[4]);
        }
        else
            $a_valueXls = array_merge($a_value[0],$a_value[1],$a_value[2],$a_value[3]);

        $xls->addRow($a_valueXls, $xlsRow);
        $xlsRow++;
    }
    $contaNotif++;
}

if($filter['fileType']=="pdf"){
    $pdf->addLines();

    if($filter['city']!='')
        $title = strtoupper($adminCityName);
    else
        $title="";

    $a_mainPageParams = array("title"=>$title,"subtitle"=>"ELENCO ESITI NOTIFICHE");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_print->getFiltersDescription($filter);
    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage()+1;
    $recap[1]['label'] = "NUMERO ATTI";
    $recap[1]['value'] = $contaNotif;
    $pdf->setMainPage($a_filters,$recap);
    $pdf->Output( $a_fileToSave['rootPath'] , 'F');
}
else if($filter['fileType']=="excel"){
    $xls->saveFile($a_fileToSave['rootPath']);
}

$file = $a_fileToSave['webPath'];

//var_dump($file);

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);