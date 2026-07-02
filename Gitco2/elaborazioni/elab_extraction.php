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
include_once CLS . "/cls_dischargeExtraction.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_parameters.php";

$cls_file = new cls_file();
$cls_db = new cls_db();
set_time_limit(-1);

//FILTRI
$filter = array();
$cls_ext = new cls_dischargeExtraction($c);

$filter['printType'] = null;
$filter['city'] = $c;
$checkCity = $filter['city'];
$filter['type'] = "extraction";
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
$filter['dischargeFlag'] = "1";
$filter['extractionFlag'] = "0";

$filter['elabType'] = $cls_help->getVar('elabType');
if($filter['elabType']=="temp")
    $filter['fileType'] = "pdf";
else
    $filter['fileType'] = "excel";
$filter['finalElabDate'] = $cls_help->getVar('finalElabDate');


$a_type['dirName'] = "Estrazioni";
$a_type['tempFileName'] = "extractions";
$a_type['finalFileName'] = "extractions";
$a_type['title'] = "Estrazioni";
$a_type['docType'] = "Estrazione";
$a_type['type'] = "extractions";

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['name'] = $a_type['tempFileName']."_Elaborazione_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['tempPath'] = $cls_file->folderCreation(ATTI ."/". $c . "/".$a_type['dirName']."/temp");
$cls_file->removeFiles($a_fileToSave['tempPath'], 0);
$folderId = null;
if($filter['elabType']=="final"){
    $a_extraction = $cls_db->getArrayLine($cls_db->ExecuteQuery('SELECT MAX(ID) as ID FROM discharge_extractions'));
    if(isset($a_extraction['ID']))
        $folderId = $a_extraction['ID']+1;
    else
        $folderId = 1;
    $a_fileToSave['elabPath'] = $cls_file->folderCreation(ATTI ."/". $c . "/".$a_type['dirName']."/".$folderId);
}
else
    $a_fileToSave['elabPath'] = $a_fileToSave['tempPath'];
$a_fileToSave['webListPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['elabPath']);

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
                link= "<?= WEB_ROOT; ?>/coattiva/modali/force-download.php?file=<?php echo $a_fileToSave['elabPath']."/".$a_fileToSave['name']; ?>&filename=<?php echo $a_fileToSave['name']; ?>";
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

    $a_headerPage[0] = array("Partita ID", "Anno rif.", "Informazioni Cartella");
    $a_headerPage[1] = array("Utente ID", "CF/PI","Nominativo");


    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco ".$a_type['title']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent*1 , $percent*2 , $percent*4 );
    $a_align = array( "L" , "L", "L" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
}
else if($filter['fileType']=="excel"){

    $a_header = array(
        "Partita ID", "Anno rif.", "Informazioni cartella","Utente ID", "CF/PI","Nominativo"
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

$cls_registry = new cls_registry();
for($i=0;$i<count($a_results);$i++){

    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();

    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);

    if($filter['fileType']=="pdf"){
        $a_value[0] = array(
            $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC']." ( ID ".$a_results[$i]['Partita_ID']." )",
            $a_results[$i]['Anno_Riferimento'],
            $a_results[$i]["Info_Cartella"],
        );

        $a_value[1] = array(
            $a_results[$i]['Utente_Comune_ID']." ( ID ".$a_results[$i]['Utente_ID']." )",
            $a_results[$i]['CF_PI'],
            $a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        );

    }
    else{
        $a_valueXls = array(
            $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC']." ( ID ".$a_results[$i]['Partita_ID']." )",
            $a_results[$i]['Anno_Riferimento'],
            $a_results[$i]["Info_Cartella"],
            $a_results[$i]['Utente_Comune_ID']." ( ID ".$a_results[$i]['Utente_ID']." )",
            $a_results[$i]['CF_PI'],
            $a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        );
    }

    $cls_ext->completeDischargeRow($a_results[$i], $a_enteAdmin['Codice_290']);
    $cls_ext->simpleDischargeRow($a_results[$i]['CF_PI']);
    if($filter['elabType']=="final"){
        $cls_db->ExecuteQuery("UPDATE partita_tributi SET Extraction_Date = '".$cls_help->toDbDate($filter['finalElabDate'])."', Is_Extracted = 1, Extraction_ID = ".$folderId." WHERE ID=".$a_results[$i]['Partita_ID']);
    }


    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){
        $xls->addRow($a_valueXls, $xlsRow);
        $xlsRow++;
    }

    $countPositions++;
}



if($countPositions == 0){
    echo "<script>noResultsBar();</script>";
    die;
}
else{

    $cls_ext->saveFile("simple",$a_fileToSave['elabPath']."/EstrazioneDiscaricoSemplice".$folderId."_".date('Y-m-d_H-i'));
    $cls_ext->saveFile("complete",$a_fileToSave['elabPath']."/EstrazioneDiscaricoCompleta".$folderId."_".date('Y-m-d_H-i'));

    if($filter['elabType']=="final"){
        $description = "Estrazione discarichi ";
        if($checkCity==$c)
            $description.= $adminCity;
        else
            $description.= "per tutti gli enti";
        $description.= " effettuata dall'utente ".$_SESSION['username']." il ".date('d/m/Y').".";
        $description.= " Numero posizioni discaricate: ".$countPositions.".";
        $query = 'INSERT INTO discharge_extractions (ID,CC,Date,Positions_Number,Description,Username) ';
        $query.= ' VALUES ('.$folderId.', "'.$c.'", "'.date('Y-m-d H:i').'", '.$countPositions.', "'.$description.'","'.$_SESSION['username'].'")';
        $cls_db->ExecuteQuery($query);
    }

    if($filter['fileType']=="pdf"){
        $pdf->addLines();
        $a_mainPageParams = array("title"=>strtoupper($adminCityName),"subtitle"=>"ELENCO");
        $pdf->setMainPageParams($a_mainPageParams);
        $a_filters = $cls_print->getFiltersDescription($filter);
        $recap[0]['label'] = "NUMERO PAGINE";
        $recap[0]['value'] = $pdf->getPage()+1;
        $recap[1]['label'] = "NUMERO POSIZIONI";
        $recap[1]['value'] = $countPositions;
        $pdf->setMainPage($a_filters, $recap);
        $pdf->Output( $a_fileToSave['elabPath']."/".$a_fileToSave['name'] , 'F');
    }
    else if($filter['fileType']=="excel"){
        $xls->saveFile($a_fileToSave['elabPath']."/".$a_fileToSave['name']);
    }
}

flush();	ob_flush();
echo "<script>endBar('Elaborazione terminata!');</script>";
flush();	ob_flush();		flush();	ob_flush();
