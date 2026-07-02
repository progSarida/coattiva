<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");

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

$cls_file = new cls_file();

set_time_limit(-1);

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

$filter['docType'] = $cls_help->getVar('docType');
$filter['printStatusAtto'] = $cls_help->getVar('printStatusAtto');
$filter['printStatusPignoramento'] = $cls_help->getVar('printStatusPignoramento');
$filter['elaborationStatusAtto'] = $cls_help->getVar('elaborationStatusAtto');
$filter['elaborationStatusPignoramento'] = $cls_help->getVar('elaborationStatusPignoramento');
$filter['paymentStatus'] = $cls_help->getVar('paymentStatus');
$filter['instalmentAtto'] = $cls_help->getVar('instalmentAtto');
$filter['instalmentPignoramento'] = $cls_help->getVar('instalmentPignoramento');
$filter['instalmentStatusAtto'] = $cls_help->getVar('instalmentStatusAtto');
$filter['instalmentStatusPignoramento'] = $cls_help->getVar('instalmentStatusPignoramento');
$filter['dischargeFlag'] = $cls_help->getVar('dischargeFlag');
$filter['from_dischargeDate'] = $cls_help->getVar('from_dischargeDate');
$filter['to_dischargeDate'] = $cls_help->getVar('to_dischargeDate');
$filter['exist_dischargeDate'] = $cls_help->getVar('exist_dischargeDate');
$filter['from_notificationDate'] = $cls_help->getVar('from_notificationDate');
$filter['to_notificationDate'] = $cls_help->getVar('to_notificationDate');
$filter['exist_notificationDate'] = $cls_help->getVar('exist_notificationDate');
$filter['from_pignoNotificationDate'] = $cls_help->getVar('from_pignoNotificationDate');
$filter['to_pignoNotificationDate'] = $cls_help->getVar('to_pignoNotificationDate');
$filter['exist_pignoNotificationDate'] = $cls_help->getVar('exist_pignoNotificationDate');

$filter['notificationAndAnomaly'] = $cls_help->getVar('notificationAndAnomaly');
$filter['pignoNotificationAndAnomaly'] = $cls_help->getVar('pignoNotificationAndAnomaly');
$filter['notificationMode'] = $cls_help->getVar('notificationMode');
$filter['notificationStock'] = $cls_help->getVar('notificationStock');
$filter['notificationAnomaly'] = $cls_help->getVar('notificationAnomaly');
$filter['pignoNotificationMode'] = $cls_help->getVar('pignoNotificationMode');
$filter['pignoNotificationStock'] = $cls_help->getVar('pignoNotificationStock');
$filter['pignoNotificationAnomaly'] = $cls_help->getVar('pignoNotificationAnomaly');
$filter['positionNotificationLimit'] = $cls_help->getVar('positionNotificationLimit');
$filter['expiredPosition'] = $cls_help->getVar('expiredPosition');

$filter['PrinterId'] = $cls_help->getVar('PrinterId');
$filter['chron'] = $cls_help->getVar('chron');

$filter['from_import'] = $cls_help->getVar('from_import');
$filter['to_import'] = $cls_help->getVar('to_import');
$filter['from_forniture'] = $cls_help->getVar('from_forniture');
$filter['to_forniture'] = $cls_help->getVar('to_forniture');


$a_type['dirName'] = "positions";
$a_type['tempFileName'] = "posizioni";
$a_type['finalFileName'] = "posizioni";
$a_type['title'] = "Posizioni";
$a_type['docType'] = "Posizione";
$a_type['type'] = "positions";

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$a_type['dirName'] );
$a_fileToSave['name'] = $a_type['tempFileName']."_Elenco_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['listPath'] = $cls_file->folderCreation($a_fileToSave['docDir']);
$a_fileToSave['webListPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['listPath']);

$cls_flow = new cls_flow($c);
?>

    <script>
        var fileType = "<?=$filter['fileType'];?>";
        function startBar(){
            $('#progressbar').progressbar({
                value: false
            });
            $( "#barlabel" ).text("Inizio elaborazione...");
        }

        function updateBar(valore){
            $( "#progressbar" ).progressbar({value: parseInt(valore) });
            $( "#barlabel" ).text( valore + "%" );
        }

        function noResultsBar(){
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text("Nessun risultato trovato");
        }

        function endBar(value){
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text( value );

            sleep(1000);

            if(fileType=="pdf"){
                window.name = "Elenco";
                window.open('<?php echo $a_fileToSave['webListPath']."/".$a_fileToSave['name']; ?>',"Elenco");
            }
            else if(fileType=="excel"){
                link= "<?= WEB_ROOT; ?>/coattiva/modali/force-download.php?file=<?php echo $a_fileToSave['listPath']."/".$a_fileToSave['name']; ?>&filename=<?php echo $a_fileToSave['name']; ?>";
                window.name="elenco";
                window.open(link,"elenco");
            }
        }

    </script>

    <table class="table_interna text_center">
        <tr>
            <td><span class="titolo font18 text_center">Elenco <?php echo $a_type['title']; ?></span></td>
        </tr>
        <tr>
            <td><div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div></td>
        </tr>
    </table>

<?php

include(INC."/footer.php");

flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();
$a_city = array("cc"=>$c, "city"=>$adminCity);
$cls_print = new cls_print("list",$filter['type'],$a_city);
$where = $cls_print->getWhereFromFilters($filter);

$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT * FROM v_positions ";
$query.= "WHERE 1=1 ";
$query.= "AND ".$where." ORDER BY ".$order;

//echo $query;
//die;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query));
$countPositions=0;
////print_r($a_results);
if(count($a_results) == 0){
    echo "<script>noResultsBar();</script>";
    die;
}

if($filter['fileType']=="pdf"){

//    $a_headerPage[0] = array("Partita","CF/PI","COD-Utente","Indirizzo","Dovuto");
//    $a_headerPage[1] = array("Atto","Rateiz. atto","Stato rateiz. atto","Informazioni cartella","Pagato");
//    $a_headerPage[2] = array("Pignoramento","Rateiz. pigno.","Stato rateiz. pigno.","Note blocco coazione","");

    $a_headerPage[0] = array("Ente - ID partita", "Atto", "Tot.1 < 60", "Pignoramento", "Tot.1");
    $a_headerPage[1] = array("Info. Cartella", "Data Notifica Atto", "Tot.2 > 60", "Data Notifica Pigno.", "Tot.2");
    $a_headerPage[2] = array("ID Utente - Nominativo", "Modalità Notifica Atto", "", "Modalità Notifica Pigno.", "Tot.3");
    $a_headerPage[3] = array("CF / PI Utente", "Stato - Anomalia Notifica Atto", "","Stato - Anomalia Notifica Pigno.", "");
    $a_headerPage[4] = array("Indirizzo Utente", "Rateizzazione Atto", "", "Rateizzazione Pigno.", "Tot. Pagato");

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco ".$a_type['title']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/12*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent*4 , $percent*3 , $percent, $percent*3 , $percent );
    $a_align = array( "L" , "L" , "R", "L" , "R" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
    $a_width = array( $percent*4 , $percent*3, $percent, $percent*3, $percent );
    $a_align = array( "L" , "L" , "R", "L" , "R"  );
    $a_totalsVar = array(0=>array(2,4),1=>array(2,4),2=>array(4),4=>array(4));
    $a_totalsHeader = array(
        0=>array("{TOTALE}","Totale 1 < 60gg","{0}","Totale 1","{1}"),
        1=>array("","Totale 2 > 60gg","{2}","Totale 3","{3}"),
        2=>array("","","","Totale 3","{4}"),
        3=>array("","","","Totale pagato","{5}")
    );
    $pdf->setArray($a_width,"a_width_totals");
    $pdf->setArray($a_align,"a_align_totals");
    $pdf->setArray($a_totalsVar,"a_totalsVar");
    $pdf->setArray($a_totalsHeader,"a_totalsHeader");
}
else if($filter['fileType']=="excel"){

    $a_header = array(
        "CC ente",
        "Denominazione Ente",
        "Tipo riscossione",
        "ID partita",
        "ID partita DB",
        "Informazioni cartella",
        "Motivo blocco coazione",
        "Note blocco coazione",
        "ID utente",
        "ID utente DB",
        "Denominazione utente",
        "CF / PI",
        "Indirizzo utente",
        "Atto",
        "Crono atto",
        "Data elaborazione atto",
        "Data notifica atto",
        "Modalità notifica atto",
        "Stato / Anomalia notifica atto",
        "Rateizzazione atto",
        "Totale Atto 1 < 60gg",
        "Totale Atto 2 > 60gg",
        "Pignoramento",
        "Crono pignoramento",
        "Data elaborazione pignoramento",
        "Data notifica pignoramento",
        "Modalità notifica pignoramento",
        "Stato / Anomalia notifica pignoramento",
        "Rateizzazione pignoramento",
        "Totale 1 pignoramento",
        "Totale 2 pignoramento",
        "Totale 3 pignoramento",
        "Totale pagamenti"
    );

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

$cls_ruolo = new cls_ruolo();
$cls_registry = new cls_registry();
$cls_params = new cls_parameters();
$a_yearParams = $cls_db->getResults($cls_db->SelectQuery($cls_params->getAllYearsParamsQuery($c)),"array","Anno");

$totalDue = 0;
$totalPayed = 0;
for($i=0;$i<count($a_results);$i++){

    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();

    $actInstalment = "";
    $checkActInstalment = "";
    $checkPignoInstalment = "";
    $pignoInstalment = "";
    if($a_results[$i]['Atto_ID']>0){
        if($a_results[$i]['Anno_Cronologico']>0 && isset($a_yearParams[$a_results[$i]['Anno_Cronologico']]['Importo_Minimo']))
            $minAmount = $a_yearParams[$a_results[$i]['Anno_Cronologico']]['Importo_Minimo'];
        else
            $minAmount = 0.00;
        $a_instalment = array(
                "instalmentNumber"=>$a_results[$i]['Rate_Previste'],"instalmentAmounts"=>$a_results[$i]['Importi_Rate'],
                "instalmentExpires"=>$a_results[$i]['Scadenze_Rate'], "totalPayed"=>$a_results[$i]['Totale_Pagamenti'],
                "minAmount"=>$minAmount, "Atto_ID"=>$a_results[$i]['Atto_ID'], "DocumentTypeId"=>$a_results[$i]['DocumentTypeId']
            );

        $a_actInstalment = $cls_ruolo->checkInstalment($cls_help, $a_instalment);
        if($a_actInstalment['instalment'] === false ){
            $checkActInstalment = "Assente";
        }
        else{
            switch ($a_actInstalment['status']) {
                case "ongoing":
                    $checkActInstalment = "In corso";
                    break;
                case "completed":
                    $checkActInstalment = "Completata";
                    break;
                case "expired":
                    $checkActInstalment = "Rata ".$a_actInstalment['last_instalment']."/".$a_instalment['instalmentNumber']." di " . $a_actInstalment['instalment_amount'] . "€ scaduta il " . $cls_help->toItalianDate($a_actInstalment['instalment_date']);
                    break;
            }
        }

        if($filter['instalmentStatusAtto']!="")
            if($filter['instalmentStatusAtto']!=$a_actInstalment['status'])
                continue;

    }
    else
        $checkActInstalment = "Assente";

    if($a_results[$i]['Pignoramento_ID']>0){
        if($a_results[$i]['Anno_Cronologico_Pignoramento']>0 && isset($a_yearParams[$a_results[$i]['Anno_Cronologico_Pignoramento']]['Importo_Minimo']))
            $minAmount = $a_yearParams[$a_results[$i]['Anno_Cronologico_Pignoramento']]['Importo_Minimo'];
        else
            $minAmount = 0.00;
        $a_instalment = array(
            "instalmentNumber"=>$a_results[$i]['Rate_Previste_Pignoramento'],"instalmentAmounts"=>$a_results[$i]['Importi_Rate_Pignoramento'],
            "instalmentExpires"=>$a_results[$i]['Scadenze_Rate_Pignoramento'], "totalPayed"=>$a_results[$i]['Totale_Pagamenti'],
            "minAmount"=>$minAmount, "Atto_ID"=>$a_results[$i]['Pignoramento_ID'], "DocumentTypeId"=>$a_results[$i]['DocumentTypeId']
        );
        $a_pignoInstalment = $cls_ruolo->checkInstalment($cls_help, $a_instalment);
        if($a_pignoInstalment['instalment'] === false ){
            $checkPignoInstalment = "Assente";
        }
        else{
            switch($a_pignoInstalment['status']){
                case "ongoing":
                    $checkPignoInstalment = "In corso";
                    break;
                case "completed":
                    $checkPignoInstalment = "Completata";
                    break;
                case "expired":
                    $checkPignoInstalment = "Rata ".$a_pignoInstalment['last_instalment']."/".$a_instalment['instalmentNumber']." di " . $a_pignoInstalment['instalment_amount'] . "€ scaduta il " . $cls_help->toItalianDate($a_pignoInstalment['instalment_date']);
                    break;
            }
        }

        if($filter['instalmentStatusPignoramento']!="")
            if($filter['instalmentStatusPignoramento']!=$a_pignoInstalment['status'])
                continue;
    }
    else
        $checkPignoInstalment = "Assente";


    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);

    if($a_results[$i]['Atto_ID']>0) {
        if($a_results[$i]['ID_Cronologico'] > 0)
            $actID = $a_results[$i]['ID_Cronologico']." / ".$a_results[$i]['Anno_Cronologico'];
        else if($cls_help->toItalianDate($a_results[$i]['Data_Elaborazione'])!=null)
            $actID = "Crono assente";
    }
    else
        $actID = "Da elaborare";

    if($a_results[$i]['Pignoramento_ID']>0) {
        if($a_results[$i]['ID_Cronologico_Pignoramento'] != null)
            $pignoId = $a_results[$i]['ID_Cronologico_Pignoramento']." / ".$a_results[$i]['Anno_Cronologico_Pignoramento'];
        else if($cls_help->toItalianDate($a_results[$i]['Data_Elaborazione_Pignoramento'])!=null)
            $pignoId = "Crono assente";
    }
    else
        $pignoId = "Nessuno";

    $attoDoc = "";
    if($a_results[$i]['DocumentTypeId']>0)
        $attoDoc = $a_docTypes[$a_results[$i]['DocumentTypeId']]['Description'];
    $pignoDoc = "";
    if($a_results[$i]['DocumentTypeId_Pignoramento']>0)
        $pignoDoc = $a_docTypes[$a_results[$i]['DocumentTypeId_Pignoramento']]['Description'];

    $str_actNotification = "";
    if($a_results[$i]['Stato_Notifica']>0)
        $str_actNotification.= $arrayParams[$a_results[$i]['Stato_Notifica']]['Descrizione'];
    if($str_actNotification!="")
        $str_actNotification.= " - ";
    if($a_results[$i]['Motivo_Notifica']>0)
        $str_actNotification.= $arrayParams[$a_results[$i]['Motivo_Notifica']]['Descrizione'];

    $str_pignoNotification = "";
    if($a_results[$i]['Stato_Notifica_Pignoramento']>0)
        $str_pignoNotification.= $arrayParams[$a_results[$i]['Stato_Notifica_Pignoramento']]['Descrizione'];
    if($str_actNotification!="")
        $str_pignoNotification.= " - ";
    if($a_results[$i]['Motivo_Notifica_Pignoramento']>0)
        $str_pignoNotification.= $arrayParams[$a_results[$i]['Motivo_Notifica_Pignoramento']]['Descrizione'];

    $str_actModeNotification = "";
    if($a_results[$i]['Modalita_Notifica']>0)
        $str_actModeNotification.= $arrayParams[$a_results[$i]['Modalita_Notifica']]['Descrizione'];
    $str_pignoModeNotification = "";
    if($a_results[$i]['Modalita_Notifica_Pignoramento']>0)
        $str_pignoModeNotification.= $arrayParams[$a_results[$i]['Modalita_Notifica_Pignoramento']]['Descrizione'];

    $a_actTotals = array(1=>"",2=>"");
    $a_pignoTotals = array(1=>"",2=>"",3=>"");
    if($a_results[$i]['Atto_ID']>0){
        if($a_results[$i]['Totale_1']>0)
            $a_actTotals[1] = number_format($a_results[$i]['Totale_1'],2,",","");
        if($a_results[$i]['Totale_2']>0)
            $a_actTotals[2] = number_format($a_results[$i]['Totale_2'],2,",","");
    }

    if($a_results[$i]['Pignoramento_ID']>0){
        if($a_results[$i]['Totale_1_Pignoramento']>0)
            $a_pignoTotals[1] = number_format($a_results[$i]['Totale_1_Pignoramento'],2,",","");
        if($a_results[$i]['Totale_2_Pignoramento']>0)
            $a_pignoTotals[2] = number_format($a_results[$i]['Totale_2_Pignoramento'],2,",","");
        if($a_results[$i]['Totale_3_Pignoramento']>0)
            $a_pignoTotals[3] = number_format($a_results[$i]['Totale_3_Pignoramento'],2,",","");
    }

    if($filter['fileType']=="pdf"){

        $a_value[0] = array(
            "[".$a_results[$i]['CC']."] ".$a_results[$i]['Denominazione_Ente']." - ".$a_results[$i]['Comune_ID']." (".$a_results[$i]['Partita_ID'].") ".$a_results[$i]['Tipo_Riscossione'],
            $actID." ".$attoDoc,
            $a_actTotals[1],
            $pignoId." ".$pignoDoc,
            $a_pignoTotals[1]
        );
        $a_value[1] = array(
            substr($a_results[$i]['Info_Cartella'],0,50)." ..",
            $cls_help->toItalianDate($a_results[$i]['Data_Notifica']),
            $a_actTotals[2],
            $cls_help->toItalianDate($a_results[$i]['Data_Notifica_Pignoramento']),
            $a_pignoTotals[2]
        );
        $a_value[2] = array(
            $a_results[$i]["Utente_Comune_ID"]." (".$a_results[$i]['Utente_ID'].") - ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
            $str_actModeNotification,
            "",
            $str_pignoModeNotification,
            $a_pignoTotals[3]
        );
        $a_value[3] = array(
            $a_results[$i]['CF_PI'],
            $str_actNotification,
            "",
            $str_pignoNotification,
            ""
        );
        $a_value[4] = array(
            $a_recipientHeader['address'],
            $checkActInstalment,
            "",
            $checkPignoInstalment,
            number_format($a_results[$i]['Totale_Pagamenti'],2,",","")
        );
    }
    else{

        $a_valueXls = array(
            $a_results[$i]['CC'],
            $a_results[$i]['Denominazione_Ente'],
            $a_results[$i]['Tipo_Riscossione'],
            $a_results[$i]['Comune_ID'],
            $a_results[$i]['Partita_ID'],
            $a_results[$i]['Info_Cartella'],
            $a_results[$i]['Motivo_Blocco'],
            $a_results[$i]['Note_Blocco'],
            $a_results[$i]["Utente_Comune_ID"],
            $a_results[$i]['Utente_ID'],
            $a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
            $a_results[$i]['CF_PI'],
            $a_recipientHeader['address'],
            $attoDoc,
            $actID,
            $cls_help->toItalianDate($a_results[$i]['Data_Elaborazione']),
            $cls_help->toItalianDate($a_results[$i]['Data_Notifica']),
            $str_actModeNotification,
            $str_actNotification,
            $checkActInstalment,
            $a_actTotals[1],
            $a_actTotals[2],
            $pignoDoc,
            $pignoId,
            $cls_help->toItalianDate($a_results[$i]['Data_Elaborazione_Pignoramento']),
            $cls_help->toItalianDate($a_results[$i]['Data_Notifica_Pignoramento']),
            $str_pignoModeNotification,
            $str_pignoNotification,
            $checkPignoInstalment,
            $a_pignoTotals[1],
            $a_pignoTotals[2],
            $a_pignoTotals[3],

            number_format($a_results[$i]['Totale_Pagamenti'],2,",","")
        );

    }


//    $totalDue+=$a_results[$i]['Totale_Dovuto'];
    $totalPayed+=$a_results[$i]['Totale_Pagamenti'];

    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){
        //var_dump($xlsRow);
        $xls->addRow($a_valueXls, $xlsRow);
        $xlsRow++;
    }

    $countPositions++;
}

$cls_file->removeFiles($a_fileToSave['listPath'], 0);

if($countPositions == 0){
    echo "<script>noResultsBar();</script>";
    die;
}
else if($filter['fileType']=="pdf"){
    $pdf->addLines();
    $pdf->setTotalRow("total");
    $a_mainPageParams = array("title"=>strtoupper($adminCityName),"subtitle"=>"ELENCO");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_print->getFiltersDescription($filter);
    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage()+1;
    $recap[1]['label'] = "NUMERO POSIZIONI";
    $recap[1]['value'] = $countPositions;
    $recap[2]['label'] = "TOTALE PAGATO";
    $recap[2]['value'] = $cls_help->floatToString($totalPayed);
    $pdf->setMainPage($a_filters,$recap);
    $pdf->Output( $a_fileToSave['listPath']."/".$a_fileToSave['name'] , 'F');
}
else if($filter['fileType']=="excel"){
    $a_totalXls = array("","","","","","","","","","","","","","","","","","","","",
        $cls_help->floatToString($totalDue),$cls_help->floatToString($totalPayed),$cls_help->floatToString($totalDue-$totalPayed));
    $xls->addRow($a_totalXls, $xlsRow);
    $xlsRow++;
    $xls->saveFile($a_fileToSave['listPath']."/".$a_fileToSave['name']);
}

flush();	ob_flush();
echo "<script>endBar('Elaborazione terminata!');</script>";
flush();	ob_flush();		flush();	ob_flush();
