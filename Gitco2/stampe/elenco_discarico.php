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
include_once CLS . "/cls_parameters.php";

$cls_file = new cls_file();
$cls_db = new cls_db();

set_time_limit(-1);


//FILTRI
$filter = array();
$filter['city'] = $cls_help->getVar('city');

$filter['printType'] = null;
$filter['fileType'] = $cls_help->getVar('fileType');
//$city = $cls_help->getVar('city');
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


$filter['from_elaborationDate'] = $cls_help->getVar('from_elaborationDate');
$filter['to_elaborationDate'] = $cls_help->getVar('to_elaborationDate');

$filter['from_dischargeDate'] = $cls_help->getVar('from_dischargeDate');
$filter['to_dischargeDate'] = $cls_help->getVar('to_dischargeDate');

$filter['exist_dischargeDate'] = $cls_help->getVar('exist_dischargeDate');

$filter['from_extractionDate'] = $cls_help->getVar('from_extractionDate');
$filter['to_extractionDate'] = $cls_help->getVar('to_extractionDate');

$filter['exist_extractionDate'] = $cls_help->getVar('exist_extractionDate');


$filter['sort'] = $cls_help->getVar('sort');

$filter['elabType'] = $cls_help->getVar('elabType');
$filter['finalElabDate'] = $cls_help->getVar('finalElabDate');

$filter['dischargeLimitDate'] = $cls_help->getVar('dischargeLimitDate');

$a_type['dirName'] = "Discarichi";
$a_type['tempFileName'] = "discharges";
$a_type['finalFileName'] = "list_discharges";
$a_type['title'] = " Discarichi";
$a_type['docType'] = "Discarico";
$a_type['type'] = "discharge";

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$a_type['dirName'] );
$a_fileToSave['name'] = $a_type['tempFileName']."_Elaborazione_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['listPath'] = $cls_file->folderCreation($a_fileToSave['docDir']);
$a_fileToSave['webListPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['listPath']);

//$cls_flow = new cls_flow($c);
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
$a_city = array("cc"=>$c, "city"=>$adminCity);
$cls_print = new cls_print("list",$filter['type'], $a_city);
$where = $cls_print->getWhereFromFilters($filter);

$order = $cls_print->getOrder($filter['sort']);

$query = "SELECT * FROM  v_discarichi_partite ";
$query .= " WHERE 1 = 1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";

$query.= " AND ".$where;

$query.= " ORDER BY ".$order;


$a_results = $cls_db->getResults($cls_db->SelectQuery($query));


$countPositions=0;

if(count($a_results) == 0){
    echo "<script>noResultsBar();</script>";
    die;
}

if($filter['fileType']=="pdf"){

   
    $a_headerPage[0] =	array("Partita ID",  "Id Utente Partita", "Cronologico", "Crono Pignoramento", "Data Discarico"); 									
    $a_headerPage[1] =	array("Ente", "Nome Cognome / Ditta", "Data Not.", "Data Notifica Pignoramento", "Data Estrazione");								
    $a_headerPage[2] =	array("Den. Ente", "CF_PI", "Tipo Atto", "Tipo Pignoramento", "Estrazione");									
    $a_headerPage[3] =	array("Comune ID", "Indirizzo",	"", "Spese Pignoramento",	 "");													
    $a_headerPage[4] =	array("Tipo Riscossione", "Info. Cartella", "",  "", "");			


    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle($a_type['title']);

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent , $percent*2 , $percent , $percent *1.2 , $percent*1.5);
    $a_align = array( "L" , "L" , "L" , "L" , "R" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
    $a_width = array( $percent*3.5 , $percent*3, $percent*0.5 );
    $a_align = array( "L" , "L" , "R" );
    $a_totalsVar = array(0=>array(4),1=>array(4));
    
}
else if($filter['fileType']=="excel"){



    $a_header = array(
        "Partita - Ente",
        "Denominazione Ente",
        "Comune ID",
        "Tipo Riscossione",
        "Utente ID Comune/ Partita",
        "Nome Cognome / Ditta",
        "CF/PI",
        "Indirizzo",
        "Informazioni cartella",
		"Crono Atto", 
        "Data notifica atto",
        "Tipo Atto",  
        "Crono Pignoramento", 
        "Data notifica pignoramento",
        "Tipo Pignoramento",
        "Totale_Spese_Notifica",
        "Totale_Spese_Accessorie",
        "Spese Pignoramento",
        "Id Estrazione", 
        "Data Estrazione", 
        "Data Discarico", 
        "Operatore",
         
        
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

$cls_params = new cls_parameters();

for($i=0;$i<count($a_results);$i++){

    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();

    $checkPosition = null;

    if($a_results[$i]['Atto_Last_ID']>0) {
        if($a_results[$i]['ID_CRONOLOGICO'] != null)
            $actID = $a_results[$i]['ID_CRONOLOGICO']." / ".$a_results[$i]['ANNO_CRONOLOGICO'];
        else if($cls_help->toItalianDate($a_results[$i]['Data_elaborazione'])!=null)
            $actID = "Crono assente";
    }
    else
        $actID = "Da elaborare";

    
        if($a_results[$i]['Pignoramento_Last_ID']>0) {
            if($a_results[$i]['ID_CRONOLOGICO_PG'] != null)
                $pignoId = $a_results[$i]['ID_CRONOLOGICO_PG']." / ".$a_results[$i]['ANNO_CRONOLOGICO_PG'];
            else if($cls_help->toItalianDate($a_results[$i]['Data_Elaborazione_Pignoramento'])!=null)
            $pignoId = "Crono assente";
        }
        else
            $pignoId = "Nessuno";
     

 
    if($filter['fileType']=="pdf"){
        
        
        
        $a_value[0] = array( $a_results[$i]['Partita_ID'],$a_results[$i]['Utente_ID_Partita'], $actID,  $pignoId, $cls_help->toItalianDate($a_results[$i]['Discharge_Date']));
        $a_value[1] = array( $a_results[$i]['CC'],$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'], $cls_help->toItalianDate($a_results[$i]['Data_Notifica']), $cls_help->toItalianDate($a_results[$i]['DATA_NOTIFICA_PG']), $a_results[$i]['ID_ESTRAZIONE']); 					
        $a_value[2] = array( $a_results[$i]['Denominazione_Ente'], $a_results[$i]['CF_PI'], $a_results[$i]['TIPO_ATTO'],$a_results[$i]['PIGNORAMENTO'],$a_results[$i]['CC']."-".$a_results[$i]['Extraction_ID']."  del ". date("d/m/Y", strtotime($a_results[$i]['DATA_ESTRAZIONE']))."-".$a_results[$i]['Operatore']);																										
        $a_value[3] = array( $a_results[$i]['Comune_ID'], $a_results[$i]['Res_Via']." Nr: ". $a_results[$i]['Res_Civico'], ""	, $a_results[$i]['Totale_Spese_Notifica']+$a_results[$i]['Totale_Spese_Accessorie'],"");   
        $a_value[4] = array( $a_results[$i]['Tipo_Riscossione'], $a_results[$i]['Info_Cartella'], "","","");
    }
    else{
        $a_valueXls = array(
            $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC']." ( ID ".$a_results[$i]['Partita_ID']." )",
            $a_results[$i]['Denominazione_Ente'],
            $a_results[$i]['Comune_ID'],
            $a_results[$i]['Tipo_Riscossione'],
            $a_results[$i]['Utente_Comune_ID']." / ".$a_results[$i]['Utente_ID_Partita'],
            "(".$a_results[$i]["Utente_Comune_ID"].") ".$a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
            $a_results[$i]['CF_PI'],
             $a_results[$i]['Res_Via'],
            $a_results[$i]['Info_Cartella'],
           
            $actID,
            $cls_help->toItalianDate($a_results[$i]['Data_Notifica']),
            $a_results[$i]['TIPO_ATTO'],

            $pignoId,
            $cls_help->toItalianDate($a_results[$i]['DATA_NOTIFICA_PG']),
            $a_results[$i]['PIGNORAMENTO'],
            $a_results[$i]['Totale_Spese_Notifica'],
            $a_results[$i]['Totale_Spese_Accessorie'],
            $a_results[$i]['Totale_Spese_Notifica']+$a_results[$i]['Totale_Spese_Accessorie'],

            $a_results[$i]['ID_ESTRAZIONE'],
            date("d/m/Y", strtotime($a_results[$i]['DATA_ESTRAZIONE'])),
            $cls_help->toItalianDate($a_results[$i]['Discharge_Date']),
            $a_results[$i]['Operatore'],
            "",
           
            $checkPosition,
            

        );
    }

    

    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){
        //echo "nuova riga";
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
    $a_mainPageParams = array("title"=>strtoupper($adminCityName));
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_print->getFiltersDescription($filter);
    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage()+1;
    $recap[1]['label'] = "NUMERO POSIZIONI";
    $recap[1]['value'] = $countPositions;
    
    $pdf->setMainPage($a_filters,$recap);
    $pdf->Output( $a_fileToSave['listPath']."/".$a_fileToSave['name'] , 'F');
}
else if($filter['fileType']=="excel"){
    $a_totalXls = array("","","","","","","","","","","","","","","","","","","","");
    
    $xls->addRow($a_totalXls, $xlsRow);
    $xlsRow++;
    $xls->saveFile($a_fileToSave['listPath']."/".$a_fileToSave['name']);
}

flush();	ob_flush();
echo "<script>endBar('Elaborazione terminata!');</script>";
flush();	ob_flush();		flush();	ob_flush();
