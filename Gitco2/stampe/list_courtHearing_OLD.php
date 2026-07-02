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

$cls_file = new cls_file();

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
$filter['sort'] = $cls_help->getVar('sort');

$a_type['dirName'] = "appeal";
$a_type['tempFileName'] = "udienze";
$a_type['finalFileName'] = "udienze";
$a_type['title'] = "Udienze";
$a_type['docType'] = "Udienza";
$a_type['type'] = "court_hearing";

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$a_type['dirName'] );
$a_fileToSave['name'] = $a_type['tempFileName']."_Elenco_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['listPath'] = $cls_file->folderCreation($a_fileToSave['docDir']."/prints");
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
            link="/gitco2/coattiva/modali/force-download.php?file=<?php echo $a_fileToSave['listPath']."/".$a_fileToSave['name']; ?>&filename=<?php echo $a_fileToSave['name']; ?>";
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

$cls_print = new cls_print("print",$filter['type']);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT * FROM v_court_hearing ";
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";
$query.= "AND ".$where." ORDER BY ".$order;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query));
echo $query;
////print_r($a_results);
if(count($a_results) == 0){
    echo "<script>noResultsBar();</script>";
    die;
}

$a_headerPage[0] = array("Grado Ricorso","Riferimento ricorso","Tipo ricorso","Data udienza");
$a_headerPage[1] = array("Partita ID","Autorita'","Giudice", "Tipo udienza");
$a_headerPage[2] = array("Utente ID","Contribuente / trasgressore","Indirizzo","CF/PI");

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
for($i=0;$i<count($a_results);$i++){

    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();

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
        $a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        $a_recipientHeader['address'],
        $a_results[$i]['CF_PI']
    );


    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){
        $a_valueXls = array_merge($a_value[0],$a_value[1],$a_value[2]);
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
    $recap[1]['label'] = "NUMERO UDIENZE";
    $recap[1]['value'] = count($a_results);
    $pdf->setMainPage($a_filters,$recap);

    $pdf->Output( $a_fileToSave['listPath']."/".$a_fileToSave['name'] , 'F');
}
else if($filter['fileType']=="excel"){
    $xls->saveFile($a_fileToSave['listPath']."/".$a_fileToSave['name']);
}

flush();	ob_flush();
echo "<script>endBar('Elaborazione terminata!');</script>";
flush();	ob_flush();		flush();	ob_flush();
