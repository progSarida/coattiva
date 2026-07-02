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

$cls_file = new cls_file();

set_time_limit(-1);


//FILTRI
$filter = array();
$filter['city'] = $c;

$filter['PrinterId'] = $cls_help->getVar('PrinterId');
$filter['PrintTypeId'] = $cls_help->getVar('PrintTypeId');
$filter['officialType'] = $cls_help->getVar('officialType');
$filter['docType'] = $cls_help->getVar('docType');


$filter['printType'] = $cls_help->getVar('printType');
$filter['printStatus'] = $cls_help->getVar('printStatus');
$filter['finalDate'] = $cls_help->toDbDate($cls_help->getVar('finalDate'));
$filter['from_elaborationDate'] = $cls_help->getVar('from_elaborationDate');
$filter['to_elaborationDate'] = $cls_help->getVar('to_elaborationDate');
$filter['from_printDate'] = $cls_help->getVar('from_printDate');
$filter['to_printDate'] = $cls_help->getVar('to_printDate');
$filter['from_notificationDate'] = $cls_help->getVar('from_notificationDate');
$filter['to_notificationDate'] = $cls_help->getVar('to_notificationDate');
$filter['from_flowDate'] = $cls_help->getVar('from_flowDate');
$filter['to_flowDate'] = $cls_help->getVar('to_flowDate');

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

//$cls_help->alert($filter["type"]." --- ".$filter["docType"]);

$cls_st = new cls_Stampe();
$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($filter['docType'],$filter['PrintTypeId'],null, array("PrinterId" => $filter["PrinterId"]));

//var_dump($cls_ruolo->a_docDetails);
//die;
//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . "/".$cls_ruolo->a_docDetails['dirName'] );

$tempFile = $cls_ruolo->a_docDetails['tempFileName']."_Temp_".date('Y-m-d_H-i-s').".pdf";
$a_fileToSave['rootTempPath'] = $cls_file->folderCreation($a_fileToSave['docDir']."/STAMPE PROVVISORIE");
$a_fileToSave['webTempPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootTempPath']);
$a_fileToSave['rootFinalPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/STAMPE DEFINITIVE");
$a_fileToSave['webFinalPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFinalPath']);
$a_fileToSave['rootFlowPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/FLUSSI");
$a_fileToSave['webFlowPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFlowPath']);

if($filter['printType']=="flow" && $filter['officialType']!="diretta"){
    $cls_help->alert("ATTENZIONE!!! Impossibile creare flussi con la selezione 'Tipo riscossione' diversa da 'Diretta'!");
    echo "<script>window.close();</script>";
}

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
    function submitFlow(id){
        //alert($('#flusso_form').attr("action"));
        //$('#flusso_form').attr("action","info_flussi.php");
        //alert($('#flusso_form').attr("action"));
        //$('#flusso_form').submit();

        //if(id===undefined){alert(id+" submit"); $('#flusso_form').submit(); }
        //else {
        var id_json = JSON.stringify(id, null, 2);
        //alert(id);

            window.opener.location.replace("info_flussi.php?a=<?php echo $a; ?>&c=<?php echo $c; ?>&stampatore=<?php echo $filter['PrinterId']; ?>&id_flows="+id_json);
        //}
        //$('#flusso_form').submit();
        window.close();
        //window.close();
    }

    /*window.onunload = refreshParent;
    function refreshParent() {
        window.opener.location.reload();
    }*/

</script>

<table class="table_interna text_center">
	<tr>
        <td><span class="titolo font18 text_center">Stampa <?php echo $cls_ruolo->a_docDetails['title']; ?></span></td>
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

$cls_print = new cls_print("print",$filter['type']);
$where = $cls_print->getWhereFromFilters($filter);
$order = $cls_print->getOrder($filter['sort']);

$fieldSelected = " * ";
$orderBy = " ORDER BY ".$order;
if($filter['printType']=="flow" && $filter['printStatus'] == "printed")
{
    $fieldSelected = " FlowId ";
    $orderBy = " GROUP BY FlowId ORDER BY FlowId ASC ";
}

$query = "SELECT ".$fieldSelected." FROM v_atti ";
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND CC='".$c."' ";
$query.= "AND ".$where." AND DocumentTypeId=".$cls_ruolo->a_docDetails['DocumentTypeId']." ".$orderBy;



//echo $query;
//die;
$a_results = $cls_db->getResults($cls_db->SelectQuery($query));


$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_subtext = $cls_db->getResults($cls_db->SelectQuery($cls_text->getSubParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_switchParams = array(
    "NotificationReport"    =>  $filter['officialType'],
    "SendType"  =>              $filter['PrintTypeId']
);
if($a_text['Content']==null){
    $cls_help->alert("ATTENZIONE!!! Parametri ".$cls_ruolo->a_docDetails['docType']." assenti per questo ente!");
    echo "<script>window.close();</script>";
}


//$cls_text->checkInformations();

$cls_registry = new cls_registry();

$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
$managerCity = $cls_ente->getCityManager();
$managerContacts = $cls_ente->getContactsManager();
$placeDate = $managerCity.", ".$cls_help->toItalianDate($filter['finalDate']);

$cls_authority = new cls_authority();
$a_gdp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("giudice", $c)));
if(!is_array($a_gdp)){
    $cls_help->alert("ATTENZIONE!!! Giudice di Pace non inserito!");
    echo "<script>window.close();</script>";
}
$a_gdpContacts = $cls_authority->getContacts($a_gdp);
$a_ctp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("comm_trib_prov", $c)));
if(!is_array($a_ctp)){
    $cls_help->alert("ATTENZIONE!!! Commissione Tributaria Provinciale non inserita!");
    echo "<script>window.close();</script>";
}
$a_ctpContacts = $cls_authority->getContacts($a_ctp);
$a_tribunale = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("tribunale", $c)));
if(!is_array($a_tribunale)){
    $cls_help->alert("ATTENZIONE!!! Tribunale non inserito!");
    echo "<script>window.close();</script>";
}
$a_tribunaleContacts = $cls_authority->getContacts($a_tribunale);
$a_authority = array("CTP"=>$a_ctpContacts['complete'],"GDP"=>$a_gdpContacts['complete'],"Tribunale"=>$a_tribunaleContacts['complete']);

$cls_params = new cls_parameters();

$a_paymentParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("pagamento", $c, $filter['taxType'])));
if(!is_array($a_paymentParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri di pagamento assenti per ".$filter['taxType']."!");
    echo "<script>window.close();</script>";
}
$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $filter['taxType'])));
if(!is_array($a_responsibleParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri dei responsabili assenti per ".$filter['taxType']."!");
    echo "<script>window.close();</script>";
}

$a_yearParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("annuali", $c)));
if(!is_array($a_yearParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri annuali assenti per l'anno ".date("Y")."!");
    echo "<script>window.close();</script>";
}

$a_generalParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("generali", $c, $filter['taxType'])));
if(!is_array($a_generalParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri generali assenti per ".$filter['taxType']."!");
    echo "<script>window.close();</script>";
}
else if($filter['printType']=="flow"){
    if($filter['PrintTypeId']=="posta" && $a_generalParams['Restituzione1']==""){
        $cls_help->alert("ATTENZIONE!!! Dati Mod23L per Atti giudiziari non inseriti nei parametri generali per ".$filter['taxType']."!");
        echo "<script>window.close();</script>";
    }
    else if($filter['PrintTypeId']=="raccomandata" && $a_generalParams['Restituzione1_Mod23O']==""){
        $cls_help->alert("ATTENZIONE!!! Dati Mod23O per Raccomandata non inseriti nei parametri generali per ".$filter['taxType']."!");
        echo "<script>window.close();</script>";
    }
}
$a_appealParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("ricorso", $c)));
if(!is_array($a_appealParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri del ricorso assenti!");
    echo "<script>window.close();</script>";
}
$cls_params->setArray("responsabili",$a_responsibleParams);
$cls_params->getSignatures($cls_ente->type);
$cls_postal = new cls_postal($a_paymentParams);


//INITIALIZE
if($filter['printType']=="flow"){
    //FLOW
    $cls_flow = new cls_flow($c,$cls_ruolo->a_docDetails,count($a_results),null,$a_fileToSave['rootFlowPath']);
    $cls_flow->setHeader("new");

    //$cls_db->Start_Transaction();
    //$cls_db->Begin_Transaction();
}
else{
    //PDF
    if($filter['printType'] == "temp")
        $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

    $a_recipientVariables = array();
    /*if($filter['docType'] == "AV_INT")
    {$a_ctpContacts['complete'],
            "{RECAPITI_GDP}" =>$a_gdpContacts['complete']

        /*************************************************************** CONTINUARE DA QUI *************************************************************/
        $a_recipientVariables = $cls_st->IndirizzoEnte($a_recipientVariables,$c);
        $a_recipientVariables["ctpContacts"] = $a_ctpContacts['complete'];
        $a_recipientVariables["gdpContacts"] = $a_gdpContacts['complete'];
        //$a_recipientVariables = $cls_st->Giudice($a_recipientVariables,$c);

    //}

    $cls_text->set_varArray($cls_ente, $a_paymentParams, $a_yearParams, $cls_params, $a_appealParams, $a_authority, $a_recipientVariables);
}

//print_r($a_results);

$a_ID = array();
$a_files = array();


for($i=0;$i<count($a_results);$i++){

    //echo $a_results[$i]['Atto_ID']." --- ".$filter['printStatus']."<br>";
    //$cls_help->alert("inizio"." --- ".ceil($i*100/count($a_results)));

    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();
   // $cls_help->alert("qui-4");
    //if(!$filter["printType"] == "flow" && !$filter["printStatus"] == "printed")

    //$cls_help->alert("qui-3". " --- ".$filter['printStatus']);
     if ($filter['printStatus'] == "printed" && $filter['printType'] == "final"){
        $a_ID[] = $a_results[$i]['Atto_ID'];
        $finalFile = $a_fileToSave['rootFinalPath']."/".$cls_ruolo->a_docDetails['finalFileName']."_".$c."_".$a_results[$i]['Anno_Cronologico'];
        $finalFile.= "_".$a_results[$i]['ID_Cronologico']."_".$a_results[$i]['Data_Stampa'].".pdf";
        $a_files[] = $finalFile;

        continue;
    }
    else if($filter['printType']=="flow" && $filter['printStatus'] == "toPrint"){

        $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);
        $fileName = $cls_ruolo->a_docDetails['finalFileName']."_".$c."_".$a_results[$i]['Anno_Cronologico'];
        $fileName.= "_".$a_results[$i]['ID_Cronologico']."_".$a_results[$i]['Data_Stampa'].".pdf";
        $finalFile = $a_fileToSave['rootFinalPath']."/".$fileName;
        $a_files[] = $finalFile;


        $data = array(
            "Id_Table" => $a_results[$i]["Atto_ID"],
            "DocumentTypeId" => $a_results[$i]["DocumentTypeId"],
            "DocumentType" => $a_results[$i]["Atto"],
            "DocumentTableTypeId" => 1,
            "Id_Cronologico" => $a_results[$i]["ID_Cronologico"],
            "Anno_Cronologico" => $a_results[$i]["Anno_Cronologico"],
            "Id_Partita" => $a_results[$i]["Partita_ID"],
            "Id_Utente" => $a_results[$i]["Utente_ID"],
            "PrintTypeId" => $a_results[$i]["PrintTypeId"],
            "PrintType" => $cls_flow->printType,
            "PrinterId" => $a_results[$i]["PrinterId"],
            "Destinatario" => $a_recipientHeader["recipient"],
            "Address" => $a_recipientHeader["address"],
            "FileName" => $fileName
        );

        $cls_flow->SetNewHeader($data);
        $cls_flow->setFlowRow("new");

        //echo $cls_flow->flowFile;

        /*$cls_flow->setNotification($cls_text->a_textReplaced,$cls_params->a_signature,$a_results[$i]['Tipo_Ufficiale']);
        $cls_flow->setRecipient($a_recipientHeader);
        $cls_flow->setDoc(
            array(
                "ID_Cronologico"=>$a_results[$i]["ID_Cronologico"],
                "Anno_Cronologico"=>$a_results[$i]["Anno_Cronologico"],
                "ID"=>$a_results[$i]["Atto_ID"]
            )
        );
        $cls_flow->setPostalBill($a_postal);
        $cls_flow->setAmounts($a_amounts);
        $cls_flow->setTextRow($cls_text->a_textReplaced,$cls_params->a_signature);
        $cls_flow->setFlowRow();*/
        /******************************************************************************************* *****************************************************************/
        //echo $a_results[$i]['Atto_ID']."<br>";


        $query = "UPDATE atto SET Data_Flusso = '".$cls_flow->flowDate."', Anno_Flusso = ".$cls_flow->flowYear.", Numero_Flusso = ".$cls_flow->flowNumber;
        $query.= " WHERE ID=".$a_results[$i]['Atto_ID'];
        $cls_db->ExecuteQuery($query);


        /****************************************************************************** **********************************************************************************/
        $a_ID[] = $a_results[$i]['Atto_ID'];

        continue;
    }else if($filter['printType']=="flow" && $filter['printStatus'] == "printed"){
        $a_ID[] = $a_results[$i]['FlowId'];

        continue;
    }



    if(!isset($a_recipientHeader))
        $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);


    $a_results[$i]['RettificaDetails'] = "";
    if($a_results[$i]['Atto_Rettificato']==1){
        $query = "SELECT * FROM atto WHERE Partita_ID=".$a_results[$i]['Partita_ID']." AND ID<".$a_results[$i]['Atto_ID']." ORDER BY ID DESC LIMIT 1";
        $a_rettifica = $cls_db->getArrayLine($cls_db->SelectQuery($query));
        $a_results[$i]['RettificaDetails'] = " DELL'".strtoupper($a_rettifica['Atto'])." N. ".$a_rettifica['ID_Cronologico']."/".$a_rettifica['Anno_Cronologico'];
    }

    //GET IMPORTI STAMPA
    $cls_ruolo->setResultArray($a_results[$i]);
    $cls_ruolo->splitCodiciTributo();
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
   /* if($filter['docType'] == "AV_INT")
    {
        /*************************************************************** CONTINUARE DA QUI *************************************************************/
        $a_recipientVariablesRow = $cls_st->InfoETotPag($a_recipientVariablesRow,$a_results[$i]["Partita_ID"],$a_results[$i]["Atto_ID"],$a_yearParams);
        $a_recipientVariablesRow = $cls_st->InfoAtto($a_recipientVariablesRow,$cls_ruolo->a_result['ID_Cronologico'],$cls_ruolo->a_result['Anno_Cronologico'],$cls_ruolo->a_result['Atto'],$cls_ruolo->a_result['Protocollo'],$cls_ruolo->a_result['Data_Notifica']);


   // }

    $cls_text->set_varArrayRow($cls_ruolo, $a_recipientHeader,$a_yearParams,$a_recipientVariablesRow);


    $cls_text->html_body = $a_text['Content'];
    $cls_text->replaceSubtext($a_subtext,$a_switchParams);
    $cls_text->replaceVariables($cls_text->a_var);

    //var_dump($cls_text->html_replaced_body);
    //
    //
    //die;

    $a_causal = $cls_ruolo->getReferences();

    $cls_postal->setPostalParams($a_recipientHeader,$a_causal,$cls_ruolo->getPostalClient($a_enteAdmin['ID']));
    $a_postal = array();
    $a_postal[1] = $cls_postal->getPostalArray(1,$cls_ente->logo,$a_results[$i]['Totale_Dovuto']+$a_results[$i]['Diritto_Riscossione_Minimo']);
    $a_postal[2] = $cls_postal->getPostalArray(2,$cls_ente->logo,$a_results[$i]['Totale_Dovuto']+$a_results[$i]['Diritto_Riscossione_Massimo']);


    if($filter['printType']!="flow"){

        if($filter['printType'] == "final")
            $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

        $pdf->setDocParams();
        $pdf->SetAutoPageBreak(true);
        $pdf->AddPage("P");
        if($filter['printType'] == "temp")
            $pdf->temporaryPrinting();
        $pdf->setManagerHeader($cls_ente->a_header);
        $pdf->setRecipientHeader($a_recipientHeader);
        $pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->ln(0);


//$cls_help->alert("qui 2");
//var_dump($cls_text->html_replaced_body);


        $pdf->writeHTML($cls_text->html_replaced_body);
      //  die;
        $pdf->setPostalBill($a_postal,2, $filter['printType']);
        if($filter['printType'] == "final"){
            $finalFile = $a_fileToSave['rootFinalPath']."/".$cls_ruolo->a_docDetails['finalFileName']."_".$c."_".$a_results[$i]['Anno_Cronologico'];
            $finalFile.= "_".$a_results[$i]['ID_Cronologico']."_".$filter['finalDate'].".pdf";


            $query = "UPDATE atto SET Data_Stampa = '".$filter['finalDate']."', Stato_Stampa = 'Stampato' ";
            $query.= "WHERE ID=".$a_results[$i]['Atto_ID'];
            $cls_db->ExecuteQuery($query);
            $pdf->Output( $finalFile , 'F');
            $a_files[] = $finalFile;
        }
    }
   // die;
}

//$cls_help->alert("fuori for");

if(count($a_results) == 0)
    echo "<script>noResultsBar();</script>";
else{
    $cls_file->removeFiles($a_fileToSave['rootTempPath'], 7);

    if($filter['printType']=="temp"){
        //die;
        $pdf->Output( $a_fileToSave['rootTempPath']."/".$tempFile, 'F' );
        echo "<script>endBar('Elaborazione completata',\"".$a_fileToSave['webTempPath']."/".$tempFile."\");</script>";
        flush();	ob_flush();		flush();	ob_flush();
    }
    else if($filter['printType']=="crono"){
        echo "<form name='crono_form' id='crono_form' method='post' action='cronologici.php'>";
        echo "<input type=hidden name=atto_val value='".$cls_ruolo->a_docDetails['type']."'>";
        echo "<input type=hidden name='c' value=".$c.">";
        echo "<input type=hidden name='a' value=".$a.">";
        for($t=0; $t<count($a_ID);$t++){
            echo "<input type=hidden name=array_crono[] value='".$a_ID[$t]."'>";
        }
        echo "</form>";

        echo "<script>endBar('Elaborazione completata','');</script>";
        flush();	ob_flush();		flush();	ob_flush();
        echo "<script>submitCrono();</script>";
    }
    else if($filter['printType']=="final"){
        function getmicrotime(){
            list($usec, $sec) = explode(" ",microtime());
            return ((float)$usec + (float)$sec);
        }
        //echo "<h1>Final Date".$filter['finalDate']."</h1></br>";
        $finalDate = $filter['finalDate'];
        if($finalDate == "" || $finalDate == null)
            $finalDate = $cls_ruolo->a_result['Anno_Cronologico']."_".$cls_ruolo->a_result['ID_Cronologico'];
        $mergeFile = $cls_ruolo->a_docDetails['finalFileName']."_".$c."_".$finalDate."_".date('Y-m-d').".pdf";

        echo "<script>startMerge();</script>";
        flush(); ob_flush(); flush(); ob_flush();
        sleep(1);

        $cls_merge = new cls_merge();
        $cls_merge->setFiles($a_files);

        $time_start = getmicrotime();//sec iniziali
        $cls_merge->concatFiles(true);
        $time_end = getmicrotime();//sec finali
        $time = $time_end - $time_start;//differenza in secondi

        $tempo_previsto_sec = $time * 20;
        if($tempo_previsto_sec<55)
            $tempo_previsto = "1 minuto";
        else
            $tempo_previsto = floor($tempo_previsto_sec/60+1)." minuti";

        echo "<script>endMerge(\"Creazione file in corso... Il tempo previsto per le operazioni e' di circa ".$tempo_previsto.".\",\"\");</script>";
        flush(); ob_flush(); flush(); ob_flush();

        set_time_limit($tempo_previsto_sec+200);
        flush(); ob_flush(); flush(); ob_flush();

        /********************************* decommentare ********************************************/
        //echo "<h1>Path: ".$a_fileToSave['rootFinalPath']."/".$mergeFile."</h1>";
        //die;
        $cls_merge->Output($a_fileToSave['rootFinalPath']."/".$mergeFile, "F");
        /*************************************** ***************************************************/

        echo "<script>endMerge('Elaborazione completata',\"".$a_fileToSave['webFinalPath']."/".$mergeFile."\");</script>";
    }
    else if($filter['printType']=="flow"){
        if($filter['printStatus']=="toPrint"){
            if($filter['PrinterId'] > 1)
            {
                $cls_flow->closeFile();
                //$cls_file->createArchive($cls_flow->flowArchiveFile, $cls_flow->flowFile, $cls_flow->a_flowAttachment);
                //$cls_db->End_Transaction();
                $a_files = array_merge($a_files, array($cls_flow->flowFile));
                //var_dump($a_files);
                //die;
                $zip = new cls_zip();
                $zip->create_zip($a_files,$cls_flow->flowArchiveFile);
            }
            else{
                $cls_flow->addFlowToFlowsTable();
                //$cls_db->End_Transaction();
            }

            echo "<script>endBar('Elaborazione completata','');</script>";
            flush();	ob_flush();		flush();	ob_flush();
            echo "<script>submitFlow(".$cls_flow->IDFlusso.");</script>";
            flush();	ob_flush();		flush();	ob_flush();

        }else{
            //var_dump($a_ID);
            //die;
            //$a_ID[0] = 1816;
            //$a_ID[1] = 1303;

            echo "<script>endBar('Elaborazione completata','');</script>";
            flush();	ob_flush();		flush();	ob_flush();

            /*var_dump(json_encode($a_ID));

            $obj = "["."1816,1303"."]";
            var_dump(json_decode($obj));
            die;*/

            echo "<script>submitFlow(".json_encode($a_ID).");</script>";
            flush();	ob_flush();		flush();	ob_flush();



            /*echo "<form name='flusso_form' id='flusso_form' method='post' target='_parent' action='info_flussi.php'>";
            //echo "<input type=hidden name=id_flows value='".$cls_flow->IDFlusso."'>";
            echo "<input type=hidden name='c' value=".$c.">";
            echo "<input type=hidden name='a' value=".$a.">";

            for($t=0; $t<count($a_ID);$t++)
                echo "<input type=hidden name=id_flows[] value='".$a_ID[$t]."'>";
            echo "</form>";

            echo "<script>submitFlow();</script>";*/
        }



        //$cls_help->alert($cls_db->lastInsertId());
        /********************************* passare id flusso da addFlowToFlowsTable() ******************/
//
//        echo "<script>endBar('Elaborazione completata','');</script>";
//        flush();	ob_flush();		flush();	ob_flush();

//
  //      flush();	ob_flush();		flush();	ob_flush();
//        echo "<script>submitFlow();</script>";
    }
}


include(INC."/footer.php");
//$cls_db->End_Transaction();