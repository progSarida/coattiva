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
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_flow.php";
include_once CLS . "/cls_notParameters.php";
//include(INC."/header.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$_SESSION['progress'] = "0.00";
session_write_close();

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );							 
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	
$adminCityName = $a_enteAdmin['Denominazione'];

ini_set("memory_limit", "-1");
$cls_notPar = new cls_notParameters();
$arrayParams = $cls_db->getResults($cls_db->SelectQuery($cls_notPar->getParametersQuery()));
$a_notParams = array();
for($i=0;$i<count($arrayParams);$i++){
    $a_notParams[$arrayParams[$i]['ID']] = $arrayParams[$i];
}
//var_dump($a);
//FILTRI
$filter = array();

$filter['city'] = $cls_help->getVar('city');

$filter['listType'] = $cls_help->getVar('listType');
$filter['fileType'] = $cls_help->getVar('fileType');
$filter['type'] = "notifiche";
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

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = "pdf";//$cls_file->getExtension($filter['fileType']);
$a_fileToSave['folder'] = $cls_file->folderCreation( ATTI ."/". $c . "/Elenchi" );
$a_fileToSave['name'] = "elenco_notifiche_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['rootPath'] = $a_fileToSave['folder']."/".$a_fileToSave['name'];
$a_fileToSave['webPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootPath']);
//var_dump($a_fileToSave['webPath']);
$cls_print = new cls_print("list",$filter['type']);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT v_notifiche.*, SUM(v_notifiche.Pagamenti_Atto) AS Totale_Pagamenti FROM v_notifiche ";
$query.= "WHERE 1=1 ";
$query.= "AND ".$where." AND Cronologico_Vecchio!='si' ";

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

$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

$pdf->setDocParams();
$pdf->SetAutoPageBreak(false);


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

        if(($filter['notificationImg']=="n" || $filter['notificationImg']=="db") && is_file($a_img['path_front']) && is_file($a_img['path_rear']))
            continue;
        if($filter['notificationImg']=="y" && !is_file($a_img['path_front']) && !is_file($a_img['path_rear']))
            continue;

        $contaNotif++;
        $pdf->AddPage("P");
        $pdf->setManagerHeader($cls_ente->a_header);
        $pdf->SetMargins(25, 45, 25);
        $pdf->SetXY(7, 45);
        $msg = "<br>DESTINATARIO - ID: ".$Utente->Comune_ID." - Nominativo: ".$a_notifications[$i]['Cognome_Ditta']." ".$a_notifications[$i]['Nome'];
        if(strlen($a_notifications[$i]['CF_PI'])==16)
            $msg .= " - CF: ".$a_notifications[$i]['CF_PI'];
        else if(strlen($a_notifications[$i]['CF_PI'])==11 && $a_notifications[$i]['CF_PI']>0)
            $msg .= " - PI: ".$a_notifications[$i]['CF_PI'];
        $msg .= "<br>PARTITA CONTABILE - ID: ".$a_notifications[$i]['Comune_ID']." - Info: ".$a_notifications[$i]['Info_Cartella'];
        $msg .= "<br><br>".strtoupper($a_notifications[$i]['DocumentType'])." ".$a_notifications[$i]['ID_Cronologico']." / ".$a_notifications[$i]['Anno_Cronologico'];
        $msg .= "<br>Data Notifica: ".$cls_help->toItalianDate($a_notifications[$i]['Data_Notifica']);
        $msg .= "<br>Tipo Notifica: ".$a_notifications[$i]['Not_Tipo_Notifica'];
        $msg .= "<br>Stato Notifica: ".$a_notifications[$i]['Not_Stato_Notifica'];
        $pdf->writeHTML($msg);
        $pdf->writeHTML("<br><br>".$a_img['front_name']);
        if ($a_img['front_name'] != "" && is_file($a_img['path_front'])) {
            $dim = $cls_file->setImgSize($a_img['path_front'], 160, 95, true);
            if ($dim[0] < $dim[1]) {
                $pdf->StartTransform();
                $pdf->SetXY(((210 - (int)$dim[1]) / 2) + (int)$dim[1], 75);
                $pdf->Rotate(-90);
                $pdf->Image($a_img['front'], "", "", 0, (int)$dim[1], '', '', 'C', false, 300, '', false, false, 1);//Logo
                $pdf->StopTransform();
            } else {
                $pdf->SetXY((210 - (int)$dim[0]) / 2, 75);
                $pdf->Image($a_img['front'], "", "", (int)$dim[0], 0, '', '', 'C', false, 300, '', false, false, 1);//Logo
            }
        }


        if ($a_img['rear_name'] != "" && is_file($a_img['path_rear'])) {
            $dim = $cls_file->setImgSize($a_img['path_rear'], 160, 95, true);

            if ($dim[0] < $dim[1]) {
                $pdf->StartTransform();
                $pdf->SetXY(((210 - (int)$dim[1]) / 2) + (int)$dim[1], 80+(int)$dim[0]);
                $pdf->Rotate(-90);
                $pdf->Image($a_img['rear'], "", "", 0, (int)$dim[1], '', '', 'C', false, 300, '', false, false, 1);//Logo
                $pdf->StopTransform();
                $pdf->ln($dim[0]/2);
            } else {
                $pdf->SetXY((210 - (int)$dim[0]) / 2, 80 + (int)$dim[1]);
                $pdf->Image($a_img['rear'], "", "", (int)$dim[0], 0, '', '', 'C', false, 300, '', false, false, 1);//Logo
                $pdf->ln($dim[1]/2+5);
            }
        }

        $pdf->writeHTML("<br>".$a_img['rear_name']);
    }
    else{
        if($filter['notificationImg']=="y")
            continue;
        if($filter['notificationImg']=="db")
            continue;

        $contaNotif++;
        $pdf->AddPage("P");
        $pdf->setManagerHeader($cls_ente->a_header);
        $pdf->SetMargins(25, 45, 25);
        $pdf->SetXY(7, 45);
        $msg = "<br>DESTINATARIO - ID: ".$Utente->Comune_ID." - Nominativo: ".$a_notifications[$i]['Cognome_Ditta']." ".$a_notifications[$i]['Nome'];
        if(strlen($a_notifications[$i]['CF_PI'])==16)
            $msg .= " - CF: ".$a_notifications[$i]['CF_PI'];
        else if(strlen($a_notifications[$i]['CF_PI'])==11 && $a_notifications[$i]['CF_PI']>0)
            $msg .= " - PI: ".$a_notifications[$i]['CF_PI'];
        $msg .= "<br>PARTITA CONTABILE - ID: ".$a_notifications[$i]['Comune_ID']." - Info: ".$a_notifications[$i]['Info_Cartella'];
        $msg .= "<br><br>".strtoupper($a_notifications[$i]['DocumentType'])." ".$a_notifications[$i]['ID_Cronologico']." / ".$a_notifications[$i]['Anno_Cronologico'];
        $msg .= "<br>Data Notifica: ".$cls_help->toItalianDate($a_notifications[$i]['Data_Notifica']);
        $msg .= "<br>Tipo Notifica: ".$a_notifications[$i]['Not_Tipo_Notifica'];
        $msg .= "<br>Stato Notifica: ".$a_notifications[$i]['Not_Stato_Notifica'];
        $pdf->writeHTML($msg);
    }
}


$pdf->addLines();
$a_mainPageParams = array("title"=>"ELENCO NOTIFICHE");
$pdf->setMainPageParams($a_mainPageParams);
$a_filters = $cls_print->getFiltersDescription($filter);
$recap[0]['label'] = "NUMERO PAGINE";
$recap[0]['value'] = $pdf->getPage()+1;
$recap[1]['label'] = "NUMERO NOTIFICHE";
$recap[1]['value'] = $contaNotif;
$pdf->setMainPage($a_filters,$recap,"P");
$pdf->Output( $a_fileToSave['rootPath'] , 'F');

$file = $a_fileToSave['webPath'];

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);







