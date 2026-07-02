<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");

include_once CLS . "/cls_file.php";
include_once CLS . "/cls_print.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_merge.php";
include_once CLS . "/cls_flow.php";
include_once CLS . "/cls_postal.php";
include_once CLS . "/cls_authority.php";
include_once CLS . "/cls_zip.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_elaborazioniUtils.php";

$cls_file = new cls_file();

set_time_limit(-1);
ini_set('memory_limit', '-1');

//FILTRI
$filter = array();
//$filter['city'] = $c;
//
//$filter['PrinterId'] = $cls_help->getVar('PrinterId');
//$filter['PrintTypeId'] = $cls_help->getVar('PrintTypeId');
//$filter['officialType'] = $cls_help->getVar('officialType');
//$filter['docType'] = $cls_help->getVar('docType');
//
//
//$filter['printType'] = $cls_help->getVar('printType');
//$filter['printStatus'] = $cls_help->getVar('printStatus');
$filter['printType'] = "final";
//$filter['from_elaborationDate'] = $cls_help->getVar('from_elaborationDate');
//$filter['to_elaborationDate'] = $cls_help->getVar('to_elaborationDate');
//$filter['from_printDate'] = $cls_help->getVar('from_printDate');
//$filter['to_printDate'] = $cls_help->getVar('to_printDate');
//$filter['from_notificationDate'] = $cls_help->getVar('from_notificationDate');
//$filter['to_notificationDate'] = $cls_help->getVar('to_notificationDate');
//$filter['from_flowDate'] = $cls_help->getVar('from_flowDate');
//$filter['to_flowDate'] = $cls_help->getVar('to_flowDate');
//
//$filter['type'] = $cls_help->getVar('type');
//$filter['from_surname'] = $cls_help->getVar('from_surname');
//$filter['to_surname'] = $cls_help->getVar('to_surname');
//$filter['from_name'] = $cls_help->getVar('from_name');
//$filter['to_name'] = $cls_help->getVar('to_name');
//$filter['from_taxRecord'] = $cls_help->getVar('from_taxRecord');
//$filter['to_taxRecord'] = $cls_help->getVar('to_taxRecord');
//$filter['from_taxYear'] = $cls_help->getVar('from_taxYear');
//$filter['to_taxYear'] = $cls_help->getVar('to_taxYear');
//$filter['taxType'] = $cls_help->getVar('taxType');
//$filter['taxStopFlag'] = "no";
//$filter['dischargeFlag'] = "0";
//$filter['sort'] = $cls_help->getVar('sort');

//$cls_help->alert($filter["type"]." --- ".$filter["docType"]);

$cls_st = new cls_Stampe();
$cls_ruolo = new cls_ruolo();
$cls_elab = new cls_elaborazioniUtils();

//var_dump($cls_ruolo->a_docDetails);
//die;
//FILE DA SALVARE


//print_r($cls_ruolo->a_docDetails);

?>

<script>

    function startBar(){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function updateBar(valore){
        //alert(valore);
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function noResultsBar(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function endBar(value, value2){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( value );

        if(value2!=""){
            sleep(1000);

            window.name = "Stampa";
            window.open(value2,"Stampa");
        }
    }

    function startMerge(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( "Elaborazione completata!" );

        $('#progressbar2').progressbar({
            value: false
        });
        $( "#barlabel2" ).text("Inizio creazione file di stampa...");
    }

    function updateMerge(valore){
        $( "#progressbar2" ).progressbar({value: parseInt(valore) });
        $( "#barlabel2" ).text( valore + "%" );
    }

    function endMerge(value, value2)
    {
        $( "#progressbar2" ).progressbar({value: 100 });
        $( "#barlabel2" ).text( value );

        if(value2!=""){
            sleep(1000);

            window.name = "Stampa";
            window.open(value2,"Stampa");
        }
    }

    function submitCrono(){
        $('#crono_form').submit();
    }

    /*window.onunload = refreshParent;
    function refreshParent() {
        window.opener.location.reload();
    }*/

</script>

<table class="table_interna text_center">
	<tr>
        <td><span class="titolo font18 text_center">Ristampa per interessi</span></td>
    </tr>
    <tr>
        <td><div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div></td>
    </tr>
    <tr><td><br></td></tr>
    <tr>
        <td><div class="table_interna text_center" id="progressbar2" style="height:55px;"><div class="text_center" id="barlabel2"></div></div></td>
    </tr>
</table>

<?php



flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();

//$cls_print = new cls_print("print",$filter['type']);
//$where = $cls_print->getWhereFromFilters($filter);
//$order = $cls_print->getOrder($filter['sort']);
//
//$fieldSelected = " * ";
//$orderBy = " ORDER BY ".$order;
//if($filter['printType']=="flow" && $filter['printStatus'] == "printed")
//{
//    $fieldSelected = " FlowId ";
//    $orderBy = " GROUP BY FlowId ORDER BY FlowId ASC ";
//}

$query = "SELECT A.*, PT.Flag_Blocco_Diritto_Riscossione FROM v_atti A JOIN partita_tributi PT ON PT.ID=A.Partita_ID ";

//$query.= 'WHERE A.Tipo_Riscossione!="CDS" AND A.DocumentTypeId=2 AND A.Data_Elaborazione>="2022-01-01" AND A.Data_Stampa is not null ';
//$query.= 'ORDER BY A.CC ASC, A.FlowId ASC, A.Anno_Cronologico ASC, A.ID_Cronologico ASC, A.Data_Elaborazione ASC';

$query.= 'WHERE A.Tipo_Riscossione!="CDS" AND A.DocumentTypeId=2 AND A.Data_Elaborazione>="2022-01-01" AND A.Data_Stampa is not null ';
$query.= 'AND FlowId IN (1861, 1887, 1889, 1893, 1895, 1903, 1911, 1923, 1920, 1921, 1926, 1931, 1933, 1936) ';
$query.= 'ORDER BY A.CC ASC, A.FlowId ASC, A.Anno_Cronologico ASC, A.ID_Cronologico ASC, A.Data_Elaborazione ASC';



$a_results = $cls_db->getResults($cls_db->SelectQuery($query));

$query = "SELECT * FROM lockup_periods WHERE CC='*****' AND Lockup_Type_Id<=3 ORDER BY Start_Date ASC";
$a_blockPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");//new ente_gestito($c);
$cls_text = new cls_textParameters();



//$cls_text->checkInformations();

$cls_registry = new cls_registry();
$cls_authority = new cls_authority();
$cls_params = new cls_parameters();

//INITIALIZE

//PDF
//if($filter['printType'] == "temp")
//    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

//print_r($a_results);

$a_ID = array();
$a_files = array();
$cont = 0;
$contFlusso = 0;
$contNostampa = 0;
$contStampa = 0;

for($i=0;$i<count($a_results);$i++){

    //echo $a_results[$i]['Atto_ID']." --- ".$filter['printStatus']."<br>";
    //$cls_help->alert("inizio"." --- ".ceil($i*100/count($a_results)));

    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();




    $query = "SELECT * FROM enti_gestiti WHERE CC = '".$a_results[$i]['CC']."'";
    $enteGestito = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new ente_gestito($c);

    $query = "SELECT * FROM parametri_annuali WHERE CC = '".$a_results[$i]['CC']."' AND Anno = '2022' AND Tipo_Riscossione = '*****'";
    $parametri = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

//    $query = "SELECT * FROM interessi_tributi WHERE CC = '".$a_results[$i]['CC']."' ORDER BY Data_Inizio ASC";
//    $a_interessiTributi = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");//new interessi_tributi($c);

//    $a_codiciTrib = array("TOTALE"=>0,"IMPORTO_INTERESSI"=>0,"PAGAMENTO"=>0,"SPESE_INGIUNZIONE"=>0);
//    $a_tributi = $cls_db->getResults($cls_db->ExecuteQuery("SELECT CT.Tipo_Codice, T.* FROM tributo T JOIN codice_tributo CT ON CT.Codice_Tributo=T.Codice_Tributo WHERE Partita_ID=".$a_results[$i]['Partita_ID']));
//    foreach($a_tributi as $a_tributo)
//    {
//        if($a_tributo['Tipo_Codice']=="PAGAMENTO"){
//            $a_codiciTrib["PAGAMENTO"] += $a_tributo['Imposta'];
//            $a_codiciTrib["TOTALE"] -= $a_tributo['Imposta'];
//        }
//        else{
//            $a_codiciTrib["TOTALE"] += $a_tributo['Imposta'];
//
//            if($a_results[$i]['Tipo_Riscossione']=="CDS" && $a_tributo['Tipo_Codice']!="INTERESSI")
//                $a_codiciTrib["IMPORTO_INTERESSI"] += $a_tributo['Imposta'];
//            else if($a_tributo['Tipo_Codice']=="IMPORTO")
//                $a_codiciTrib["IMPORTO_INTERESSI"] += $a_tributo['Imposta'];
//
//            if($a_tributo['Codice_Tributo']=="S_03")
//                $a_codiciTrib["SPESE_INGIUNZIONE"] += $a_tributo['Imposta'];
//        }
//    }
//    if($a_codiciTrib["TOTALE"]<$a_codiciTrib["IMPORTO_INTERESSI"])
//        $a_codiciTrib["IMPORTO_INTERESSI"] = $a_codiciTrib["TOTALE"];
//
//    $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento ";
//    $query.= "WHERE Atto_ID < ".$a_results[$i]['Atto_ID']." AND Partita_ID = ".$a_results[$i]['Partita_ID']." ";
//    $query.= "AND DocumentTableTypeId!=2 GROUP BY Partita_ID";
//    $a_pagamento = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
//    if(empty($a_pagamento))
//        $totalePag = 0;
//    else
//        $totalePag = $a_pagamento["TOTALE_PAGAMENTI"];
//
//    echo "CC: ".$a_results[$i]['CC']."<br>";
//    echo "ID: ".$a_results[$i]['Atto_ID']." - PartitaID: ".$a_results[$i]['Partita_ID']." - ".strtoupper($a_results[$i]['Atto'])." n.".$a_results[$i]['ID_Cronologico']."/".$a_results[$i]['Anno_Cronologico']."<br>";
//    echo "DATA ELABORAZIONE: ".$cls_help->toItalianDate($a_results[$i]['Data_Elaborazione'])."<br>";
//    echo "DATA STAMPA: ".$cls_help->toItalianDate($a_results[$i]['Data_Stampa'])."<br>";
//    echo "FLUSSO: ID ".$a_results[$i]['FlowId']." - NUMERO ".$a_results[$i]['Numero_Flusso']." - ANNO ".$a_results[$i]['Anno_Flusso']."<br>";
//    echo "DATA FLUSSO: ".$cls_help->toItalianDate($a_results[$i]['Data_Flusso'])."<br><br>";
//    echo "CODICI TRIBUTO: ".$a_codiciTrib["TOTALE"]."<br><br>";
//    echo "PAGAMENTI PRECEDENTI: ".$totalePag."<br>";
//    echo "INTERESSI PRECEDENTI: ".$a_results[$i]['Interessi_Precedenti']."<br>";
//    echo "SPESE NOTIFICA PRECEDENTI: ".$a_results[$i]['Spese_Notifica_Precedenti']."<br><br>";
//    echo "SPESE NOTIFICA: ".$a_results[$i]['Spese_Notifica']."<br>";
//    echo "INTERESSE: ".$a_results[$i]['Interessi']."<br>";
//    echo "TOTALE: ".$a_results[$i]['Totale_Dovuto']."<br>";
//    echo "DIR. RISCOSSIONE 1: ".$a_results[$i]['Diritto_Riscossione_Minimo']."<br>";
//    echo "DIR. RISCOSSIONE 2: ".$a_results[$i]['Diritto_Riscossione_Massimo']."<br><br>";
//
//
//    $check = $a_results[$i]['Interessi_Precedenti']+$a_results[$i]['Spese_Notifica_Precedenti']+$a_codiciTrib["TOTALE"]-$totalePag;
//    if($check<$a_codiciTrib["IMPORTO_INTERESSI"])
//        $importoBaseInteressi = $check;
//    else
//        $importoBaseInteressi = $a_codiciTrib["IMPORTO_INTERESSI"];
//
//    $a_params = array(
//        "CalcType" => $a_results[$i]['Tipo_Riscossione'],
//        "StartDate" => $cls_help->toDbDate($a_results[$i]['Data_Decorrenza_Interessi']),
//        "EndDate" => $cls_help->toDbDate($a_results[$i]['Data_Calcolo_Interessi']),
//        "BaseAmount" => $importoBaseInteressi,
//        "a_blocks" => $a_blockPeriods,
//        "a_interessiTributi" => $a_interessiTributi
//    );
//
//    $interesseRicalcolato = $cls_elab->calcInterests($a_params);
//    echo "IMPORTO BASE PER CALCOLO: ".$importoBaseInteressi." Dal ".$a_params['StartDate']." al ".$a_params['EndDate']."<br>";
//    echo "INTERESSE RICALCOLATO: ".$interesseRicalcolato."<br>";
//
//    $totDovutoRicalcolato = $a_results[$i]['Totale_Dovuto']-$a_results[$i]['Interessi']+$interesseRicalcolato;
//    $dirittoMinRicalc = $a_results[$i]['Diritto_Riscossione_Minimo'];
//    $dirittoMaxRicalc = $a_results[$i]['Diritto_Riscossione_Massimo'];
//
//    if(($a_results[$i]['Flag_Blocco_Diritto_Riscossione']!="si" || is_null($a_results[$i]['Flag_Blocco_Diritto_Riscossione'])) && !is_null($enteGestito['Gestore_ID']))
//    {
//        $importo_calcolo_diritto = $totDovutoRicalcolato - $totalePag;
//        $dirittoMinRicalc = $importo_calcolo_diritto * $parametri['Diritto_Riscossione_Minimo'] / 100;
//        $dirittoMaxRicalc = $importo_calcolo_diritto * $parametri['Diritto_Riscossione_Massimo'] / 100;
//    }
//    echo "TOTALE RIC: ".$totDovutoRicalcolato."<br>";
//    echo "DIR. RISCOSSIONE 1 RIC: ".$dirittoMinRicalc."<br>";
//    echo "DIR. RISCOSSIONE 2 RIC: ".$dirittoMaxRicalc."<br><br><br><br>";
//
//    $status = "";
//    if($a_results[$i]['Interessi']!=$interesseRicalcolato)
//        $status.= "!= IP ".$a_results[$i]['Interessi']." - RI ".$interesseRicalcolato;
//    else{
//        $status.= "\n= IP ".$a_results[$i]['Interessi']." - RI ".$interesseRicalcolato;
//        continue;
//    }
//
//    if(!is_null($a_results[$i]['Data_Flusso'])){
//        if($a_results[$i]['Interessi']-$interesseRicalcolato>10.1){
//            $contFlusso++;
//            $status.= "\nA ".$a_results[$i]['ID_Cronologico']."/".$a_results[$i]['Anno_Cronologico']." - F ".$a_results[$i]['Numero_Flusso']."/".$a_results[$i]['Anno_Flusso'];
//            $query = "UPDATE atto SET Totale_Dovuto=".$totDovutoRicalcolato.", Interessi=".$interesseRicalcolato.", ";
//            $query.= "Diritto_Riscossione_Minimo=".round($dirittoMinRicalc,2).", Diritto_Riscossione_Massimo=".round($dirittoMaxRicalc,2)." ";
//            $query.= "WHERE ID=".$a_results[$i]['Atto_ID'];
//
//            echo $cls_db->ExecuteQuery($query)."<br><br>";
//        }
//        else
//            continue;
//    }
//
//    else if(!is_null($a_results[$i]['Data_Stampa'])){
//        if($a_results[$i]['Interessi']-$interesseRicalcolato>10.1){
//            $contStampa++;
//            $status.= "\nA ".$a_results[$i]['ID_Cronologico']."/".$a_results[$i]['Anno_Cronologico']." - S ".$cls_help->toItalianDate($a_results[$i]['Data_Stampa']);
//        }
//        else
//            continue;
//    }
//    else{
//        $contNostampa++;
//        $status.= "\nNS";
////        $query = "UPDATE atto SET Totale_Dovuto=".$totDovutoRicalcolato.", Interessi=".$interesseRicalcolato.", ";
////        $query.= "Diritto_Riscossione_Minimo=".round($dirittoMinRicalc,2).", Diritto_Riscossione_Massimo=".round($dirittoMaxRicalc,2)." ";
////        $query.= "WHERE ID=".$a_results[$i]['ID'];
////        echo $query."<br><br>";
////        $cls_db->ExecuteQuery($query);
//    }
//
//    $cont++;
//
//
//    $a_results[$i]['Interessi'] = $interesseRicalcolato;
//    $a_results[$i]['Totale_Dovuto'] = $totDovutoRicalcolato;
//    $a_results[$i]['Diritto_Riscossione_Minimo'] = round($dirittoMinRicalc,2);
//    $a_results[$i]['Diritto_Riscossione_Massimo'] = round($dirittoMaxRicalc,2);











    $cls_ruolo->getTypeDetails("ING",$a_results[$i]['PrintTypeId'],null, array("PrinterId" => $a_results[$i]["PrinterId"]));

    $a_fileToSave = array();
    $a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $a_results[$i]['CC'] . "/".$cls_ruolo->a_docDetails['dirName'] );

    $tempFile = $cls_ruolo->a_docDetails['tempFileName']."_Temp_".date('Y-m-d_H-i-s').".pdf";
    $a_fileToSave['rootTempPath'] = $cls_file->folderCreation($a_fileToSave['docDir']."/STAMPE PROVVISORIE");
    $a_fileToSave['webTempPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootTempPath']);
    $a_fileToSave['rootFinalPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/STAMPE DEFINITIVE");
    $a_fileToSave['webFinalPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFinalPath']);
    $a_fileToSave['rootFlowPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/FLUSSI");
    $a_fileToSave['webFlowPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFlowPath']);

    $a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($a_results[$i]['CC'],$cls_ruolo->a_docDetails['DocumentTypeId'])));
    $a_subtext = $cls_db->getResults($cls_db->SelectQuery($cls_text->getSubParametersQuery($a_results[$i]['CC'],$cls_ruolo->a_docDetails['DocumentTypeId'])));
    $a_switchParams = array(
        "NotificationReport"    =>  $a_results[$i]['Tipo_Ufficiale'],
        "SendType"  =>              $a_results[$i]['PrintTypeId']
    );
    if($a_text['Content']==null){
        $cls_help->alert("ATTENZIONE!!! Modello di testo ".$cls_ruolo->a_docDetails['docType']." assente per questo ente! ( Testi->Html->Testi ) ");
        echo "<script>window.close();</script>";
    }


    $a_gdp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("giudice", $a_results[$i]['CC'])));
    if(!is_array($a_gdp)){
        $cls_help->alert("ATTENZIONE!!! Giudice di Pace non inserito!");
        echo "<script>window.close();</script>";
    }
    $a_gdpContacts = $cls_authority->getContacts($a_gdp);
    $a_ctp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("comm_trib_prov", $a_results[$i]['CC'])));
    if(!is_array($a_ctp)){
        $cls_help->alert("ATTENZIONE!!! Commissione Tributaria Provinciale non inserita!");
        echo "<script>window.close();</script>";
    }
    $a_ctpContacts = $cls_authority->getContacts($a_ctp);
    $a_tribunale = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("tribunale", $a_results[$i]['CC'])));
    if(!is_array($a_tribunale)){
        $cls_help->alert("ATTENZIONE!!! Tribunale non inserito!");
        echo "<script>window.close();</script>";
    }
    $a_tribunaleContacts = $cls_authority->getContacts($a_tribunale);
    $a_authority = array("CTP"=>$a_ctpContacts['complete'],"GDP"=>$a_gdpContacts['complete'],"Tribunale"=>$a_tribunaleContacts['complete']);

    $a_paymentParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("pagamento", $a_results[$i]['CC'], $a_results[$i]['Tipo_Riscossione'])));
    if(!is_array($a_paymentParams)){
        $cls_help->alert("ATTENZIONE!!! Parametri di pagamento assenti per ".$a_results[$i]['Tipo_Riscossione']."!");
        echo "<script>window.close();</script>";
    }
    $a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $a_results[$i]['CC'], $a_results[$i]['Tipo_Riscossione'])));
    if(!is_array($a_responsibleParams)){
        $cls_help->alert("ATTENZIONE!!! Parametri dei responsabili assenti per ".$a_results[$i]['Tipo_Riscossione']."!");
        echo "<script>window.close();</script>";
    }

    $a_yearParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("annuali", $a_results[$i]['CC'])));
    if(!is_array($a_yearParams)){
        $cls_help->alert("ATTENZIONE!!! Parametri annuali assenti per l'anno ".date("Y")."!");
        echo "<script>window.close();</script>";
    }

    $a_generalParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("generali", $a_results[$i]['CC'], $a_results[$i]['Tipo_Riscossione'])));
    if(!is_array($a_generalParams)){
        $cls_help->alert("ATTENZIONE!!! Parametri generali assenti per ".$a_results[$i]['Tipo_Riscossione']."!");
        echo "<script>window.close();</script>";
    }
    else if($filter['printType']=="flow"){
        if($filter['PrintTypeId']=="posta" && $a_generalParams['Restituzione1']==""){
            $cls_help->alert("ATTENZIONE!!! Dati Mod23L per Atti giudiziari non inseriti nei parametri generali per ".$a_results[$i]['Tipo_Riscossione']."!");
            echo "<script>window.close();</script>";
        }
        else if($filter['PrintTypeId']=="raccomandata" && $a_generalParams['Restituzione1_Mod23O']==""){
            $cls_help->alert("ATTENZIONE!!! Dati Mod23O per Raccomandata non inseriti nei parametri generali per ".$a_results[$i]['Tipo_Riscossione']."!");
            echo "<script>window.close();</script>";
        }
    }
    $a_appealParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("ricorso", $a_results[$i]['CC'])));
    if(!is_array($a_appealParams)){
        $cls_help->alert("ATTENZIONE!!! Parametri del ricorso assenti!");
        echo "<script>window.close();</script>";
    }

    $a_enteRistampa = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$a_results[$i]['CC']."'") );
    $cls_ente = new cls_ente($a_enteRistampa);
    $cls_ente->setPrintHeader($filter['printType'],$a_generalParams);

    $managerCity = $cls_ente->getCityManager();
    $managerContacts = $cls_ente->getContactsManager();
    $placeDate = $managerCity.", ".$cls_help->toItalianDate($a_results[$i]['Data_Stampa']);

    $cls_params->setArray("responsabili",$a_responsibleParams);
    $cls_params->getSignatures($cls_ente->type);
    $cls_postal = new cls_postal($a_paymentParams);

    $a_recipientVariables = array();

    /*************************************************************** CONTINUARE DA QUI *************************************************************/
    $a_recipientVariables = $cls_st->IndirizzoEnte($a_recipientVariables,$a_results[$i]['CC']);
    $a_recipientVariables["ctpContacts"] = $a_ctpContacts['complete'];
    $a_recipientVariables["gdpContacts"] = $a_gdpContacts['complete'];
    $a_recipientVariables = $cls_st->DataGestore($a_recipientVariables,$a_results[$i]['CC']);
    $a_recipientVariables = $cls_st->spese_notifica_pigno($a_recipientVariables,$a_results[$i]['CC'],date('Y'));
    $a_recipientVariables["ExpenditureEstimateAssets"] = $cls_st->getStimaBeni($a_results[$i]['CC']);
    $a_recipientVariables["ManagerPec"] = $cls_st->GetPecGestore($a_results[$i]['CC']);

    $cls_text->set_varArray($cls_ente, $a_paymentParams, $a_yearParams, $cls_params, $a_appealParams, $a_authority, $a_recipientVariables);

    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);


    $a_results[$i]['RettificaDetails'] = "";
    if($a_results[$i]['Atto_Rettificato']==1){
        $query = "SELECT * FROM atto WHERE Partita_ID=".$a_results[$i]['Partita_ID']." AND ID<".$a_results[$i]['Atto_ID']." ORDER BY ID DESC LIMIT 1";
        $a_rettifica = $cls_db->getArrayLine($cls_db->SelectQuery($query));
        $a_results[$i]['RettificaDetails'] = " DELL'".strtoupper($a_rettifica['Atto'])." N. ".$a_rettifica['ID_Cronologico']."/".$a_rettifica['Anno_Cronologico'];
    }

    //GET IMPORTI STAMPA
    $cls_ruolo->setResultArray($a_results[$i]);
    $cls_ruolo->setPrintAmounts($cls_ruolo->a_docDetails['docType'],$a_yearParams);

    $a_recipientHeader['references'][0] = "PARTITA NUMERO:  ".$a_results[$i]['Comune_ID']." / ".$a_results[$i]['Anno_Riferimento'];
    $a_recipientHeader['references'][1] = "CODICE UTENTE:  ".$a_results[$i]['Utente_Comune_ID']." / ".$a_results[$i]['CC'];
    if($a_results[$i]['Protocollo']!=""){
        $a_recipientHeader['references'][2] = "PROTOCOLLO:  ".$a_results[$i]['Protocollo'];
        $a_recipientHeader['references'][3] = "DEL:  ".$cls_help->toItalianDate($a_results[$i]['Data_Protocollo']);
    }
    else{
        $a_recipientHeader['references'][2] = "";
        $a_recipientHeader['references'][3] = "";
    }
    $a_recipientHeader['placeDate'] = $placeDate;

    $a_recipientVariablesRow = array();

    $a_recipientVariablesRow = $cls_st->InfoETotPag($a_recipientVariablesRow,$a_results[$i]["Partita_ID"],$a_results[$i]["Atto_ID"],$a_yearParams);
    $a_recipientVariablesRow = $cls_st->InfoAtto($a_recipientVariablesRow,$cls_ruolo->a_result['ID_Cronologico'],$cls_ruolo->a_result['Anno_Cronologico'],$cls_ruolo->a_result['Atto'],$cls_ruolo->a_result['Protocollo'],$cls_ruolo->a_result['Data_Notifica']);

    $cls_text->set_varArrayRow($cls_ruolo, $a_recipientHeader,$a_yearParams,$a_recipientVariablesRow);
    $cls_text->html_body = $a_text['Content'];
    $cls_text->replaceSubtext($a_subtext,$a_switchParams);
    $cls_text->replaceVariables($cls_text->a_var);

    $a_causal = $cls_ruolo->getReferences();

    $cls_postal->setPostalParams($a_recipientHeader,$a_causal,$cls_ruolo->getPostalClient($a_enteAdmin['ID']));
    $a_postal = array();
    $a_postal[1] = $cls_postal->getPostalArray(1,$cls_ente->logo,$a_results[$i]['Totale_Dovuto']+$a_results[$i]['Diritto_Riscossione_Minimo']);
    $a_postal[2] = $cls_postal->getPostalArray(2,$cls_ente->logo,$a_results[$i]['Totale_Dovuto']+$a_results[$i]['Diritto_Riscossione_Massimo']);



    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

    $pdf->setDocParams();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    $pdf->setManagerHeader($cls_ente->a_header);
    $pdf->setRecipientHeader($a_recipientHeader);
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(0);

    $pdf->writeHTML($cls_text->html_replaced_body);
    $pdf->setPostalBill($a_postal,2, $filter['printType']);

//    $toSendFile = ATTI."/daPostalizzare/".$cls_ruolo->a_docDetails['finalFileName']."_".$a_results[$i]['CC']."_".$a_results[$i]['Anno_Cronologico'];
//    $toSendFile.= "_".$a_results[$i]['ID_Cronologico']."_".$a_results[$i]['Data_Stampa'].".pdf";
//    $pdf->Output( $toSendFile , 'F');

    $finalFile = $a_fileToSave['rootFinalPath']."/".$cls_ruolo->a_docDetails['finalFileName']."_".$a_results[$i]['CC']."_".$a_results[$i]['Anno_Cronologico'];
    $finalFile.= "_".$a_results[$i]['ID_Cronologico']."_".$a_results[$i]['Data_Stampa'].".pdf";
//        $query = "UPDATE atto SET Data_Stampa = '".$filter['finalDate']."', Stato_Stampa = 'Stampato' ";
//        $query.= "WHERE ID=".$a_results[$i]['Atto_ID'];
//        $cls_db->ExecuteQuery($query);

    $pdf->Output( $finalFile , 'F');
    $a_files[] = $finalFile;


}

//$cls_help->alert("fuori for");

if(count($a_results) == 0)
    echo "<script>noResultsBar();</script>";
else{
    $cls_file->removeFiles($a_fileToSave['rootTempPath'], 7);

//    if($filter['printType']=="final"){
//
//        $a_files;
//
//    }
}


include(INC."/footer.php");
//$cls_db->End_Transaction();