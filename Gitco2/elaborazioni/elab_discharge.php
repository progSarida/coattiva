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

$cls_file = new cls_file();
$cls_db = new cls_db();

set_time_limit(-1);


//FILTRI
$filter = array();
$filter['city'] = $c;

$filter['printType'] = null;
$filter['fileType'] = $cls_help->getVar('fileType');
//$filter['city'] = $cls_help->getVar('city');
$filter['type'] = "discharge";
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
$filter['dischargeFlag'] = "0";
$filter['extractionFlag'] = "0";

$filter['elabType'] = $cls_help->getVar('elabType');
$filter['finalElabDate'] = $cls_help->getVar('finalElabDate');
$filter['dischargeLimitDate'] = $cls_help->getVar('dischargeLimitDate');

if($filter['dischargeLimitDate']=="")
{
    echo "ERRORE DATA LIMITE DEL DISCARICO OBBLIGATORIA!!";
    die;
}

$a_type['dirName'] = "Discarichi";
$a_type['tempFileName'] = "discharges";
$a_type['finalFileName'] = "discharges";
$a_type['title'] = "Discarichi";
$a_type['docType'] = "Discarico";
$a_type['type'] = "discharges";

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$a_type['dirName'] );
$a_fileToSave['name'] = $a_type['tempFileName']."_Elaborazione_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
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

            sleep(5000);

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

$cls_print = new cls_print("elab",$filter['type']);
$where = $cls_print->getWhereFromFilters($filter);

$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT * FROM v_positions ";
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";
$query.= "AND ".$where." ORDER BY ".$order;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query));
//echo $query;
//die;
$countPositions=0;
////print_r($a_results);
if(count($a_results) == 0){
    echo "<script>noResultsBar();</script>";
    die;
}

if($filter['fileType']=="pdf"){

    $a_headerPage[0] = array("Partita","CF/PI","Utente","Indirizzo","Dovuto");
    $a_headerPage[1] = array("Atto","","Data decorrenza","Informazioni cartella","Pagato");
    $a_headerPage[2] = array("Pignoramento","","","Note blocco coazione","");

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco ".$a_type['title']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent , $percent , $percent*1.5 , $percent*3 , $percent*0.5 );
    $a_align = array( "L" , "L" , "L" , "L" , "R" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
    $a_width = array( $percent*3.5 , $percent*3, $percent*0.5 );
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



    $a_header = array(
        "Partita","Informazioni cartella","Note blocco coazione",
        "Utente","CF/PI","Indirizzo",
        "Crono Atto", "Tipo Atto", "Data elaborazione atto","Data stampa atto", "Data notifica atto", "","Data decorrenza",
        "Pignoramento", "Tipo pignoramento", "Data elaborazione pignoramento","Data stampa pignoramento", "Data notifica pignoramento", "", "check",
        "Dovuto","Pagato","Differenza"
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
$a_yearParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("annuali", $c)));
$totalDue = 0;
$totalPayed = 0;
for($i=0;$i<count($a_results);$i++){

    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();

    $checkPosition = null;
    if(is_null($a_results[$i]['Partita_Data_Decorrenza'])){
        if(strpos( $a_results[$i]['Info_Cartella'],"200")===false || strpos( $a_results[$i]['Info_Cartella'],substr($filter['dischargeLimitDate'],5))===false)
            continue;
        else if(strpos( $a_results[$i]['Info_Cartella'],substr($filter['dischargeLimitDate'],3))!==false)
            continue;
        else
            $checkPosition = "!!!CHECK!!!";
    }
    else if($a_results[$i]['Partita_Data_Decorrenza']>$cls_help->toDbDate($filter['dischargeLimitDate']))
        continue;



    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);

    if($a_results[$i]['Atto_ID']>0) {
        if($a_results[$i]['ID_Cronologico'] != null)
            $actID = $a_results[$i]['ID_Cronologico']." / ".$a_results[$i]['Anno_Cronologico'];
        else if($cls_help->toItalianDate($a_results[$i]['Data_elaborazione'])!=null)
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

    if($filter['fileType']=="pdf"){
        $a_value[0] = array(
            $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC']." ( ID ".$a_results[$i]['Partita_ID']." )",
            $a_results[$i]['CF_PI'],
            "(".$a_results[$i]["Utente_Comune_ID"].") ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
            $a_recipientHeader['address'],
            number_format($a_results[$i]['Totale_Dovuto'],2,",","")
        );

        $a_value[1] = array(
            $actID,
            "",
            $cls_help->toItalianDate($a_results[$i]['Partita_Data_Decorrenza']),
            $a_results[$i]['Info_Cartella'],
            number_format($a_results[$i]['Totale_Pagamenti'],2,",","")
        );
        $a_value[2] = array(
            $pignoId,
            "",
            $checkPosition,
            strtoupper($a_results[$i]['Note_Blocco']),
            ""
        );
    }
    else{
        $a_valueXls = array(
            $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC']." ( ID ".$a_results[$i]['Partita_ID']." )",
            $a_results[$i]['Info_Cartella'],
            strtoupper($a_results[$i]['Note_Blocco']),
            "(".$a_results[$i]["Utente_Comune_ID"].") ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
            $a_results[$i]['CF_PI'],
            $a_recipientHeader['address'],
            $actID,
            $a_results[$i]['Atto'],
            $a_results[$i]['Data_Elaborazione'],
            $a_results[$i]['Data_Stampa'],
            $a_results[$i]['Data_Notifica'],
            "",
            $cls_help->toItalianDate($a_results[$i]['Partita_Data_Decorrenza']),
            $pignoId,
            $a_results[$i]['Tipo_Pignoramento'],
            $a_results[$i]['Data_Elaborazione_Pignoramento'],
            $a_results[$i]['Data_Stampa_Pignoramento'],
            $a_results[$i]['Data_Notifica_Pignoramento'],
            "",
            $checkPosition,
            number_format($a_results[$i]['Totale_Dovuto'],2,",",""),
            number_format($a_results[$i]['Totale_Pagamenti'],2,",",""),
            number_format($a_results[$i]['Totale_Dovuto']-$a_results[$i]['Totale_Pagamenti'],2,",",""),

        );
    }

    if($filter['elabType']=="final"){

        //SALVATAGGI DA IMPLEMENTARE
        $cls_db->ExecuteQuery("UPDATE partita_tributi SET Discharge_Date = '".$cls_help->toDbDate($filter['finalElabDate'])."', Is_Discharged = 1 WHERE ID=".$a_results[$i]['Partita_ID']);
    }

//    var_dump($a_results[$i]['Partita_Data_Decorrenza']);
//    var_dump($a_results[$i]['Tipo_Riscossione']);
//
//    die;

    $totalDue+=$a_results[$i]['Totale_Dovuto'];
    $totalPayed+=$a_results[$i]['Totale_Pagamenti'];

    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){
        echo "nuova riga";
//        continue;
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
    $recap[2]['label'] = "TOTALE DOVUTO";
    $recap[2]['value'] = $cls_help->floatToString($totalDue);
    $recap[3]['label'] = "TOTALE PAGATO";
    $recap[3]['value'] = $cls_help->floatToString($totalPayed);
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
