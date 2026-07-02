<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_help.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_query.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_excel.php";

include(INC."/header.php");

//FILTRI
$filter = array();

$filter['city'] = $cls_help->getVar('city');

$filter['fileType'] = $cls_help->getVar('fileType');

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
$filter['notification'] = $cls_help->getVar('notification');
$filter['dischargeFlag'] = $cls_help->getVar('dischargeFlag');
$filter['from_dischargeDate'] = $cls_help->getVar('from_dischargeDate');
$filter['to_dischargeDate'] = $cls_help->getVar('to_dischargeDate');
$filter['exist_dischargeDate'] = $cls_help->getVar('exist_dischargeDate');

$filter['sort'] = $cls_help->getVar('sort');

$cls_file = new cls_file();

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['ext'] = $cls_file->getExtension($filter['fileType']);
$a_fileToSave['folder'] = $cls_file->folderCreation( ATTI ."/". $c . "/Elenchi" );
$a_fileToSave['name'] = "elenco_dettaglioPartita_".date('Y-m-d_H-i-s').".".$a_fileToSave['ext'];
$a_fileToSave['rootPath'] = $a_fileToSave['folder']."/".$a_fileToSave['name'];
$a_fileToSave['webPath'] = $cls_file->getWebPath($a_fileToSave['rootPath']);

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
            $( "#barlabel" ).text( value );

            sleep(1000);

            if(fileType=="pdf"){
                window.name = "Elenco";
                window.open('<?php echo $a_fileToSave['webPath']; ?>',"Elenco");
            }
            else if(fileType=="excel"){
                link="<?= WEB_ROOT; ?>/coattiva/modali/force-download.php?file=<?php echo $a_fileToSave['rootPath']; ?>&filename=<?php echo $a_fileToSave['name']; ?>";
                window.name="elenco";
                window.open(link,"elenco");
            }
        }

    </script>

    <table class="table_interna text_center">
        <tr>
            <td><span class="titolo font18 text_center">Elenco Posizioni</span></td>
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

$cls_query = new cls_query();
$where = $cls_query->getWhereFromFilters($filter);
$order = $cls_query->getOrder($filter['sort']);

$query = "SELECT * FROM v_dettaglio_partita ";
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";
$query.= "AND ".$where." ORDER BY ".$order;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query));

$a_headerPage[0] = array("Partita","Tipo atto","Utente","Info partita","Cronologico","CF/PI");
$a_headerPage[1] = array("Importo 290","Spese atto precedente","Spese atto","Interessi prec.","Interessi","Spese pignoramento");
$a_headerPage[2] = array("Spese accessorie","Totale dovuto", "Totale pagato","","","");

if($filter['fileType']=="pdf"){

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("Elenco dettaglio partite");

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/7*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent , $percent , $percent , $percent*3 , $percent );
    $a_align = array( "L" , "L" , "L" , "L" , "L" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();

}
else if($filter['fileType']=="excel"){

    $a_header = array_merge($a_headerPage[0],$a_headerPage[1],$a_headerPage[2]);

    $a_params = array(  'creator'=>'sarida',
        'lastModifiedBy'=>$_SESSION['username'],
        'title'=>'Elenco dettaglio partite',
        'subject'=>'Elenco dettaglio partite',
        'description'=>'Elenco dettaglio partite',
        'sheetTitle'=>'Elenco dettaglio partite'
    );

    $xls = new cls_excel();
    $xls->setParameters($a_params);
    $xls->createFile($a_header);

    $xlsRow = 2;
}

$idPartita = 0;
for($i=0;$i<count($a_results);$i++){

    flush();	ob_flush();
    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();

    if($idPartita!=$a_results[$i]['Partita_ID']){

        $query = "SELECT * FROM tributo WHERE Partita_ID=".$a_results[$i]['Partita_ID'];
        $a_tributi = $cls_db->getResults($cls_db->SelectQuery($query));
        $a_value[0] = array();
        for($k=0;$k<count($a_tributi);$k++){

            $a_value[0][] = "CODICE TRIBUTO: ".$a_tributi[$k]['Codice_Tributo'];
            $a_value[0][] = "IMPORTO: ".$a_tributi[$k]['Imposta'];
        }
        if($filter['fileType']=="excel"){
            $a_valueXls = $a_value[0];
            $xls->addRow($a_valueXls, $xlsRow);
            $xlsRow++;
        }

        $idPartita = $a_results[$i]['Partita_ID'];

    }

    $a_value[0] = array(
        $a_results[$i]['Comune_ID']." / ".$a_results[$i]['CC'],
        $a_results[$i]['Tipo_Doc'],
        $a_results[$i]['Cognome_Ditta']." ".$a_results[$i]['Nome'],
        $a_results[$i]['CF_PI'],
        $a_results[$i]['Info_Cartella'],
        $a_results[$i]['ID_Cronologico']." / ".$a_results[$i]['Anno_Cronologico']
    );

    $a_value[1] = array(
        number_format($a_results[$i]['Importo_Ruolo'],2,",","."),
        number_format($a_results[$i]['Spese_Atto'],2,",","."),
        number_format($a_results[$i]['Spese_Precedenti_Atto'],2,",","."),
        number_format($a_results[$i]['Interessi_Precedenti'],2,",","."),
        number_format($a_results[$i]['Interessi'],2,",","."),
        number_format($a_results[$i]['Spese_Pignoramento'],2,",",".")
    );

    $a_value[2] = array(
        number_format($a_results[$i]['Spese_Accessorie'],2,",","."),
        number_format($a_results[$i]['Totale_Dovuto'],2,",","."),
        number_format($a_results[$i]['Totale_Pagato'],2,",","."),
        "",
        "",
        ""
    );
    if($filter['fileType']=="pdf")
        $pdf->setRowPage($a_value);
    else if($filter['fileType']=="excel"){
        $a_valueXls = array_merge($a_value[0],$a_value[1],$a_value[2]);
        $xls->addRow($a_valueXls, $xlsRow);
        $xlsRow++;
    }
}

if($filter['fileType']=="pdf"){
    $pdf->addLines();

    $a_mainPageParams = array("title"=>strtoupper($adminCityName),"subtitle"=>"ELENCO DETTAGLIO PARTITE");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_query->getFiltersDescription($filter);
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

