<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_help.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_query.php";
include_once CLS . "/cls_print.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_flow.php";
include_once CLS . "/cls_notParameters.php";
include(INC."/header.php");

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
$filter['notificationAndAnomaly'] = $cls_help->getVar('notificationAndAnomaly');
$filter['notificationMode'] = $cls_help->getVar('notificationMode');
$filter['notificationStock'] = $cls_help->getVar('notificationStock');
$filter['notificationAnomaly'] = $cls_help->getVar('notificationAnomaly');
$filter['importNotification'] = $cls_help->getVar('importNotification');
//$filter['notificationAnomalyAtto'] = $cls_help->getVar('notificationAnomalyAtto');
$filter['flow'] = $cls_help->getVar('flow');
$filter['sort'] = $cls_help->getVar('sort');

$cls_file = new cls_file();
$cls_flow = new cls_flow($c);

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['folder'] = $cls_file->folderCreation( ATTI ."/". $c . "/Elenchi" );
$a_fileToSave['name'] = "elenco_esiti_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['rootPath'] = $a_fileToSave['folder']."/".$a_fileToSave['name'];
$a_fileToSave['webPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootPath']);

?>

<script>
    var fileType = "<?php echo $filter['fileType']; ?>";

    function startBar()
    {
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function updateBar(valore)
    {
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function noResultsBar()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function endBar(value){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( "100%" );

        sleep(1000);

        if(fileType=="pdf"){
            window.name = "Elenco";
            window.open('<?php echo $a_fileToSave['webPath']; ?>',"Elenco");
        }
        else if(fileType=="excel"){
            link="/gitco2/coattiva/modali/force-download.php?file=<?php echo $a_fileToSave['rootPath']; ?>&filename=<?php echo $a_fileToSave['name']; ?>";
            window.name="elenco";
            window.open(link,"elenco");
        }
    }

</script>

<table class="table_interna text_center">
	<tr>
        <td><span class="titolo font18 text_center">Elenco Esiti</span></td>
    </tr>
    <tr>
        <td><div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div></td>
    </tr>
    <tr>
        <td><div id=vedi_file></div></td>
    </tr>
	<tr>
		<td>
		<p>ATTENZIONE: Potrebbe accadere che il download del file venga bloccato dal browser.
        In tal caso sara' necessario sbloccare il download, seguendo le istruzioni che il browser visualizzera' sulla pagina,
        e, successivamente, riavviare l'elaborazione dell'elenco.</p>
		</td>
	</tr>
</table>

<?php

include(INC."/footer.php");

flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();
sleep(1);

$cls_print = new cls_print("list",$filter['type']);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);

//$cls_query = new cls_query();
//$where = $cls_query->getWhereFromFilters($filter);
//$order = $cls_query->getOrder($filter['sort']);


$query = "SELECT v_notifiche.*, SUM(v_notifiche.Pagamenti_Atto) AS Totale_Pagamenti FROM v_notifiche ";
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";
$query.= "AND ".$where." ";

if($filter['lastAct']=="last")
    $query.= "GROUP BY Partita_ID ";
else
    $query.= "GROUP BY ID, DocumentTypeId ";

$query.= "ORDER BY ".$order;

if($_SESSION['username']=="mirkop"){
    echo $query;
//    die;
}


$a_notifications = $cls_db->getResults($cls_db->SelectQuery($query));
if(!count($a_notifications)>0){
    echo "<script>noResultsBar();</script>";
    die;
}

$a_headerPage[0] = array("Tipo atto","Utente","Partita","Info partita","Img fronte");
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

for($i=0;$i<count($a_notifications);$i++){

    flush();	ob_flush();
    echo "<script>updateBar(".ceil($i*100/count($a_notifications)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();

    $notificationMode = $notificationStock = $notificationAnomaly = "";
    if($a_notifications[$i]['Modalita_Notifica']>0)
        $notificationMode = $a_notifications[$i]['Modalita_Notifica']." - ".$a_notParams[$a_notifications[$i]['Modalita_Notifica']]['Descrizione'];
    if($a_notifications[$i]['Stato_Notifica']>0)
        $notificationStock = $a_notifications[$i]['Stato_Notifica']." - ".$a_notParams[$a_notifications[$i]['Stato_Notifica']]['Descrizione'];
    if($a_notifications[$i]['Motivo_Notifica']>0)
        $notificationAnomaly = $a_notifications[$i]['Motivo_Notifica']." - ".$a_notParams[$a_notifications[$i]['Motivo_Notifica']]['Descrizione'];
    $a_value[0] = array(    $a_notifications[$i]['DocumentType'],
                            $a_notifications[$i]['Cognome_Ditta']." ".$a_notifications[$i]['Nome'],
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
    $recap[1]['value'] = count($a_notifications);
    $pdf->setMainPage($a_filters,$recap);
    $pdf->Output( $a_fileToSave['rootPath'] , 'F');
}
else if($filter['fileType']=="excel"){
    $xls->saveFile($a_fileToSave['rootPath']);
}

flush();	ob_flush();
echo "<script>endBar();</script>";
flush();	ob_flush();		flush();	ob_flush();

