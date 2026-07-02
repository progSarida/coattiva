<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");

include_once CLS . "/cls_file.php";
//include_once CLS . "/cls_print.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_parameters.php";
//include_once CLS . "/cls_merge.php";
include_once CLS . "/cls_flow.php";
//include_once CLS . "/cls_postal.php";
//include_once CLS . "/cls_authority.php";
include_once CLS . "/cls_zip.php";
include_once CLS . "/cls_Stampe.php";

$cls_file = new cls_file();

set_time_limit(-1);
ini_set('memory_limit', '-1');

//FILTRI
$filter = array();
$filter['city'] = $c;

$cls_params = new cls_parameters();

$filter = array();

$filter['el_list_id'] = intval($cls_help->getVar('el_list_id'));

$query = "SELECT EL.*, E.CC, TT.Name AS Tipo_Riscossione ";
$query.= "FROM elaboration_lists EL ";
$query.= "JOIN elaborations E ON E.Id=EL.Elaboration_Id ";
$query.= "JOIN tax_type TT ON TT.Id = EL.TaxTypeId ";
$query.= "WHERE EL.ID=".$filter['el_list_id'];

$a_elab_list = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));


$filter['city'] = $a_elab_list['CC'];
$filter['last_el_id']  = $a_elab_list['Elaboration_Id'];
$filter['PrinterId'] = $a_elab_list['PrinterId'];
$filter['PrintTypeId'] = $a_elab_list['PrintTypeId'];
$filter['doc_type_id'] = $a_elab_list['DocumentTypeId'];
$filter['officialType'] = $a_elab_list['NotificationType'];
$filter['taxType'] = $a_elab_list['Tipo_Riscossione'];

$filter['finalDate'] = date('Y-m-d');



$cls_st = new cls_Stampe();
$cls_ruolo = new cls_ruolo();
$cls_ruolo->getDocumentDetails($filter['doc_type_id'], $filter['PrintTypeId'], null, array("PrinterId" => $filter["PrinterId"]));

//var_dump($cls_ruolo->a_docDetails);
//die;
//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['docDir'] = $cls_file->folderCreation(ATTI . "/" . $filter['city'] . "/" . $cls_ruolo->a_docDetails['dirName']);

$a_fileToSave['rootFinalPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/STAMPE DEFINITIVE");
$a_fileToSave['webFinalPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFinalPath']);
$a_fileToSave['rootFlowPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/FLUSSI");
$a_fileToSave['webFlowPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFlowPath']);


/* if($filter['printType']=="flow" && $filter['officialType']!="diretta"){
    $cls_help->alert("ATTENZIONE!!! Impossibile creare flussi con la selezione 'Tipo riscossione' diversa da 'Diretta'!");
    echo "<script>window.close();</script>";
} */

//print_r($cls_ruolo->a_docDetails);

?>

<script>

    function startBar(){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function waitBar(text){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text(text);
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

    function endBar(value, webFile) {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text(value);
        sleep(1000);
        window.opener.location.href ="<?= WEB_ROOT ?>/elaborazioni/mgmt_elaboration.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$filter['last_el_id'];?>";

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

            window.opener.location.replace("info_flussi.php?a=<?php echo $a; ?>&c=<?php echo $c; ?>&docType=<?php echo $filter['doc_type_id']; ?>&stampatore=<?php echo $filter['PrinterId']; ?>&id_flows="+id_json);
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

<br/><br/>
<div class="col col-md-auto text_center">
    <span class="titolo font16 under_decor" style="color:red;">Non chiudere la finestra prima del termine della procedura</span>
</div> 
    

<?php



flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();

$query_v_atti = "   SELECT * FROM v_atti " .
                "   WHERE Atto_Elaboration_List_Id = ".$filter['el_list_id'] .
                "   AND   CC = '" . $filter['city'] . "' ORDER BY Anno_Cronologico ASC, ID_Cronologico ASC";
//echo $query;
//die;
//echo $query;
//die;

$a_results = $cls_db->getResults($cls_db->SelectQuery($query_v_atti));


$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_subtext = $cls_db->getResults($cls_db->SelectQuery($cls_text->getSubParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_switchParams = array(
    "NotificationReport"    =>  $filter['officialType'],
    "SendType"  =>              $filter['PrintTypeId']
);


//$cls_text->checkInformations();

$cls_registry = new cls_registry();

/* $cls_authority = new cls_authority();
$a_gdp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("giudice", $c)));

$a_gdpContacts = $cls_authority->getContacts($a_gdp);
$a_ctp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("comm_trib_prov", $c)));

$a_ctpContacts = $cls_authority->getContacts($a_ctp);
$a_tribunale = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("tribunale", $c)));

$a_tribunaleContacts = $cls_authority->getContacts($a_tribunale);
$a_authority = array("CTP"=>$a_ctpContacts['complete'],"GDP"=>$a_gdpContacts['complete'],"Tribunale"=>$a_tribunaleContacts['complete']);

$cls_params = new cls_parameters();

$a_yearParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("annuali", $c))); */

$a_paymentParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("pagamento", $c, $filter['taxType'])));
$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $filter['city'], $filter['taxType'])));
$a_generalParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("generali", $filter['city'], $filter['taxType'])));


/* $a_appealParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("ricorso", $c))); */

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$filter['city']."'") );
// $cls_ente = new cls_ente($a_enteAdmin);
// $cls_ente->setPrintHeader($filter['printType'],$a_generalParams);
// $managerCity = $cls_ente->getCityManager();
// $managerContacts = $cls_ente->getContactsManager();
// $placeDate = $managerCity.", ".$cls_help->toItalianDate($filter['finalDate']);

// $cls_params->setArray("responsabili",$a_responsibleParams);
// $cls_params->getSignatures($cls_ente->type);
//$cls_postal = new cls_postal($a_paymentParams);


//INITIALIZE

    //FLOW
    $cls_flow = new cls_flow($filter['city'],$cls_ruolo->a_docDetails,count($a_results),null,$a_fileToSave['rootFlowPath']);   
   
    $cls_flow->setHeader("new");

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
   /*   if ($filter['printStatus'] == "printed" && $filter['printType'] == "final"){
        $a_ID[] = $a_results[$i]['Atto_ID'];
        $finalFile = $a_fileToSave['rootFinalPath']."/".$cls_ruolo->a_docDetails['finalFileName']."_".$c."_".$a_results[$i]['Anno_Cronologico'];
        $finalFile.= "_".$a_results[$i]['ID_Cronologico']."_".$a_results[$i]['Data_Stampa'].".pdf";
        $a_files[] = $finalFile;

        continue;
    }
    else if( $filter['printType']=="flow" && $filter['printStatus'] == "toPrint"){*/

        $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);
        $a_filePath = $cls_ruolo->getDocCompletePath($filter['city'],$a_results[$i]);
        /* $fileName = $cls_ruolo->a_docDetails['finalFileName']."_".$c."_".$a_results[$i]['Anno_Cronologico'];
        $fileName.= "_".$a_results[$i]['ID_Cronologico']."_".$a_results[$i]['Data_Stampa'].".pdf"; 
        $finalFile = $a_fileToSave['rootFinalPath']."/".$fileName;*/
        $a_files[] = $a_filePath['root'];
       
        $enteName = $a_enteAdmin['Denominazione'];
        if(strtoupper(substr($a_results[$i]["CC"],0,1))!="U")
            $enteName = "Comune di ".$a_enteAdmin['Denominazione'];

        $data = array(
            "DocumentId" => $a_results[$i]["Atto_ID"],
            "TableId" => 1,
            "CC" => $a_results[$i]["CC"],
            "DocumentTypeId" => $a_results[$i]["DocumentTypeId"],
            "DocumentType" => $a_results[$i]["Atto"],
            "Partita_ID" => $a_results[$i]["Partita_ID"],
            "Utente_ID" => $a_results[$i]["Utente_ID"],
            "Id_Cronologico" => $a_results[$i]["ID_Cronologico"],
            "Anno_Cronologico" => $a_results[$i]["Anno_Cronologico"],
            "PrinterId" => $a_results[$i]["PrinterId"],
            "PrintTypeId" => $a_results[$i]["PrintTypeId"],
            "PrintType" => $cls_flow->printType,
            "CF_PI" => $a_results[$i]["CF_PI"],
            "Destinatario" => $a_recipientHeader["recipient"],
            "AddressName" => $a_recipientHeader["addressName"],
            "AddressCap" => $a_recipientHeader["addressCap"],
            "AddressCity" => $a_recipientHeader["addressCity"],
            "AddressProvince" => $a_recipientHeader["addressProvince"],
            "AddressCountry" => $a_recipientHeader["addressCountry"],
            "FileName" => $a_filePath['name'],
            "Ente" => $enteName,

            "SMA_Intestatario" => $a_generalParams['Intestatario_SMA'],
            "SMA_Numero" => $a_generalParams['Numero_SMA'],
            "SMA_TestoSpese" => $a_generalParams['Testo_Spese_Anticipate'],
            "SMA_Restituzione1" => $a_generalParams['Restituzione1'],
            "SMA_Restituzione2" => $a_generalParams['Restituzione2'],
            "SMA_Restituzione3" => $a_generalParams['Restituzione3'],
            "SMA_Restituzione4" => $a_generalParams['Restituzione4'],
            "SMA_Restituzione5" => $a_generalParams['Restituzione5']
        );

        $cls_flow->SetNewHeader($data);
        $cls_flow->setFlowRow("new");

        /****************************************************************************** **********************************************************************************/
        $a_ID[] = $a_results[$i]['Atto_ID'];

      /*   continue;
    }else if($filter['printType']=="flow" && $filter['printStatus'] == "printed"){
        $a_ID[] = $a_results[$i]['FlowId'];

        continue;
    } */
   
    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);
    
  
}

//$cls_help->alert("fuori for");

if(count($a_results) == 0)
    echo "<script>noResultsBar();</script>";
else{
   
            $cls_help->alert("ATTENZIONE!!! Creazione del file compresso in corso. Attendere fino al completamento!");
      //  if($filter['printStatus']=="toPrint"){
            if($filter['PrinterId']>1){
                if($_SESSION['username']=="emanuela")
                    $cls_help->alert("ATTENZIONE!!! Mi raccomando Emanuela non chiudere la pagina fino al completamento della procedura!\nLa procedura sta creando il file zip del flusso.\nAspetta che si chiuda da sola e che si carichi la pagina per uploadare il flusso.\nPoi puoi chiudere");
                    

                flush();	ob_flush();		flush();	ob_flush();
                echo "<script>waitBar('Creazione del file compresso in corso. Attendere fino al completamento!');</script>";
                flush();	ob_flush();		flush();	ob_flush();
            }
            
            $a_dbParams_elab_list = array(
                'table' => 'elaboration_lists',
                'updateField' => array(
                    array('name' => 'Id',  'type' => 'int', 'value' =>$filter['el_list_id']),
                ),
                'fields'=> array(
                    array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => 6),                    
                    array(  'name' => 'FlowDate',   'type' => 'date', 'value' => date('Y-m-d')),
                )
            );
            $cls_db->DbSave($a_dbParams_elab_list);
    
            $contList = $cls_db->getNumberRow($cls_db->ExecuteQuery("SELECT Id FROM elaboration_lists WHERE Elaboration_Id=".$filter['last_el_id']." AND Elaboration_Status_Id >= 6"));
    
            $query_elab = " SELECT * FROM  elaborations WHERE Id =" . $filter['last_el_id'];
            $results_elab = $cls_db->ExecuteQuery($query_elab);
            $elabs = $cls_db->getArrayLine($results_elab);
           
    
            $a_dbParams_elab = array(
                'table' => 'elaborations',
                'updateField' => array(
                    array('name' => 'Id',  'type' => 'int', 'value' => $filter['last_el_id']),
                ),
                'fields'=> array(
                    array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                    array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
    
                )
            );   
    
            if((int)$elabs['ListNumber']==(int)$contList)
                $a_dbParams_elab['fields'][] = array(  'name' => 'Elaboration_Status_Id', 'type' => 'int', 'value' => 6);
    
            $cls_db->DbSave($a_dbParams_elab);    
            $cls_flow->closeFile();
            $zip = new cls_zip();
            if($filter['PrinterId'] > 1)
                $zip->create_zip(array_merge($a_files, array($cls_flow->flowFile)),$cls_flow->flowArchiveFile);
            else
                $zip->create_zip($a_files,$cls_flow->flowArchiveFile);
               
            $query_act = " UPDATE atto SET Data_Flusso = '".$filter['finalDate']."', Numero_Flusso = ".$cls_flow->flowNumber." , FlowId =".$cls_flow->IDFlusso." , Anno_Flusso =".date('Y');
            $query_act.= " WHERE Elaboration_List_Id=".$filter['el_list_id'];
            $cls_db->ExecuteQuery($query_act);

            $query_list = " UPDATE elaboration_lists SET  FlowNumber = ".$cls_flow->flowNumber." , FlowId =".$cls_flow->IDFlusso." , FlowYear =".date('Y');
            $query_list.= " WHERE Id=".$filter['el_list_id'];
            $cls_db->ExecuteQuery($query_list);
           

            echo "<script>endBar('Elaborazione completata','".$paginaCorrente."');</script>";  
            flush();	ob_flush();		flush();	ob_flush();

            // echo "<script>submitFlow(".json_encode($a_ID).");</script>";
            flush();	ob_flush();		flush();	ob_flush();

        }      
   
//}


include(INC."/footer.php");
//$cls_db->End_Transaction();