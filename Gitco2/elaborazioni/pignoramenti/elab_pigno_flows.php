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
include_once CLS . "/cls_flowPigno.php";
//include_once CLS . "/cls_postal.php";
//include_once CLS . "/cls_authority.php";
include_once CLS . "/cls_zip.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
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

$terzo = $cls_help->getVar('terzo');
$lavoro= false;$banca = false;
if($terzo=="lavoro") { $lavoro = true;}
if($terzo=="banca") { $banca = true;}

$cls_st = new cls_Stampe();
$cls_ruolo = new cls_ruolo();
$cls_ruolo->getDocumentDetails($filter['doc_type_id'], $filter['PrintTypeId'], null, array("PrinterId" => $filter["PrinterId"]));

$flowId = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT max(Id) AS flowNumber FROM flows WHERE Year = ".date('Y')))["flowNumber"]+1;

//FILE DA SALVARE
$a_fileToSave = array();
//$a_fileToSave['docDir'] = $cls_file->folderCreation(PIGNORAMENTI . "/" . $filter['city'] . "/" . $cls_ruolo->a_docDetails['dirName']);
$a_fileToSave['docDir'] = $cls_file->folderCreation(FLUSSI);
$a_fileToSave['rootFlowPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/".$flowId);


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

    function endBar(value) {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text(value);
        sleep(1000);
        <?php if($lavoro)
        {
            ?>
            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_LAVORO_WEB ?>/mgmt_pignoramenti_lavoro.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$filter['last_el_id'];?>";
            <?php
        }
        else if($banca)
        {
            ?>
            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$filter['last_el_id'];?>";
			<?php
        }
        else
        {
            ?>
            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$filter['last_el_id'];?>";
            <?php
        }
        ?>

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
        var id_json = JSON.stringify(id, null, 2);
        window.opener.location.replace("info_flussi.php?a=<?php echo $a; ?>&c=<?php echo $c; ?>&docType=<?php echo $filter['doc_type_id']; ?>&stampatore=<?php echo $filter['PrinterId']; ?>&id_flows="+id_json);
       
        window.close();
        
    }

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

$query_v_atti = "   SELECT * FROM v_manage_acts_pignoramenti_flusso " .
                "   WHERE Elaboration_List_Id = ".$filter['el_list_id'] .
                "   AND   CC = '" . $filter['city'] . "' ORDER BY Anno_Cronologico ASC, ID_Cronologico ASC";


$a_results = $cls_db->getResults($cls_db->SelectQuery($query_v_atti));


$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_subtext = $cls_db->getResults($cls_db->SelectQuery($cls_text->getSubParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_switchParams = array(
    "NotificationReport"    =>  $filter['officialType'],
    "SendType"  =>              $filter['PrintTypeId']
);


$cls_registry = new cls_registry();

$a_paymentParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("pagamento", $c, $filter['taxType'])));
$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $filter['city'], $filter['taxType'])));
$a_generalParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("generali", $filter['city'], $filter['taxType'])));



$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$filter['city']."'") );

$crea_file_name=function($a_results,$suffix="Copia") use($a_elab_list,$lavoro,$banca){

    $pignoId = $a_results["PignoID"];

    if( is_dir( PIGNORAMENTI."/".$pignoId ) == false )
    {
        mkdir(PIGNORAMENTI."/".$pignoId);
    }
    $prefix=$a_results["PrefixName"];
    $cc=$a_elab_list['CC'];
    $anno= $a_results["Anno_Cronologico"];
    $id=$a_results["ID_Cronologico"];
    $notifica_id=$a_results["ID"];

    $path=$pignoId."/";
    $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_".$suffix.".pdf";
    if ($lavoro)
    {
        $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_Copia_".$suffix.".pdf";
        $filename_Relata=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata_".$suffix.".pdf";
    }
    else if($banca)
    {
        $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_Copia_".$suffix.".pdf";
        $filename_Relata=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata_".$suffix.".pdf";
    }
    else
    {
        $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_".$suffix.".pdf";
        $filename_Relata=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata".".pdf";
    }
    $path_completo =  PIGNORAMENTI."/".$path.$filename;
    $path_completo_Relata =  PIGNORAMENTI."/".$path.$filename_Relata;
    $result=array();
    $result["PathCompleto"] = $path_completo;
    $result["PathCompleto_Relata"] = $path_completo_Relata;
    $result["FileName"] = $filename;
    $result["FileName_Relata"] = $filename_Relata;
    return $result;
};
//INITIALIZE

//FLOW
$cls_flow = new cls_flowPigno($filter['city'],$cls_ruolo->a_docDetails,count($a_results),null,$a_fileToSave['rootFlowPath']);   

$cls_flow->setHeader();

$a_ID = array();
$a_files = array();


for($i=0;$i<count($a_results);$i++){

    if ($lavoro)
    {
        $is_debitore = $a_results[$i]["Tipo_Notifica"] == "debitore" ? true :false;
        if ($is_debitore)
        {
            $a_filename = $crea_file_name($a_results[$i],"debitore");
        }
        else
        {
            $a_filename = $crea_file_name($a_results[$i],"terzo");
        }
    }
    else if ($banca)
    {
        $is_debitore = $a_results[$i]["Tipo_Notifica"] == "debitore" ? true :false;
        if ($is_debitore)
        {
            $a_filename = $crea_file_name($a_results[$i],"debitore");
        }
        else
        {
            $a_filename = $crea_file_name($a_results[$i],"banca");
        }
    }
    else
        $a_filename = $crea_file_name($a_results[$i]);
    echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
    flush();	ob_flush();		flush();	ob_flush();
    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);
    $filename = $a_filename["PathCompleto"];        
    $a_files[] = $filename; 
    if (file_exists($a_filename["PathCompleto_Relata"])) $a_files[]=$a_filename["PathCompleto_Relata"];
    $enteName = $a_enteAdmin['Denominazione'];
    if(strtoupper(substr($a_results[$i]["CC"],0,1))!="U")
        $enteName = "Comune di ".$a_enteAdmin['Denominazione'];

    $data = array(
        "DocumentId" => $a_results[$i]["PignoID"],
        "TableId" => 2,
        "CC" => $a_results[$i]["CC"],
        "DocumentTypeId" => $a_results[$i]["DocumentTypeId"],
        "DocumentType" => $a_results[$i]["DocumentType"],
        "Partita_ID" => $a_results[$i]["Partita_ID"],
        "Utente_ID" => $a_results[$i]["Utente_ID"],
        "Id_Cronologico" => $a_results[$i]["ID_Cronologico"],
        "Anno_Cronologico" => $a_results[$i]["Anno_Cronologico"],
        "PrinterId" => $a_results[$i]["Printer_Id"],
        "PrintTypeId" => $a_results[$i]["PrintTypeId"],
        "PrintType" => $cls_flow->printType,
        "CF_PI" => is_null($a_results[$i]["CF_PI"]),
        "Destinatario" => $a_recipientHeader["recipient"],
        "AddressName" => $a_recipientHeader["addressName"],
        "AddressCap" => $a_recipientHeader["addressCap"],
        "AddressCity" => $a_recipientHeader["addressCity"],
        "AddressProvince" => $a_recipientHeader["addressProvince"],
        "AddressCountry" => $a_recipientHeader["addressCountry"],
        "FileName" => $a_filename["FileName"],
        "Ente" => $enteName,
        "NotificationId" =>$a_results[$i]["ID"],

        "SMA_Intestatario" => $a_generalParams['Intestatario_SMA'],
        "SMA_Numero" => $a_generalParams['Numero_SMA'],
        "SMA_TestoSpese" => $a_generalParams['Testo_Spese_Anticipate'],
        "SMA_Restituzione1" => $a_generalParams['Restituzione1'],
        "SMA_Restituzione2" => $a_generalParams['Restituzione2'],
        "SMA_Restituzione3" => $a_generalParams['Restituzione3'],
        "SMA_Restituzione4" => $a_generalParams['Restituzione4'],
        "SMA_Restituzione5" => $a_generalParams['Restituzione5']
    );

    $return = $cls_flow->SetNewHeader($data);
    $cls_flow->setFlowRow("new");
  
}



if(count($a_results) == 0)
    echo "<script>noResultsBar();</script>";
else{
   
            $cls_help->alert("ATTENZIONE!!! Creazione del file compresso in corso. Attendere fino al completamento!");
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
                    array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => ElaborationStatus::FLUSSI_CREATI),                    
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
                $a_dbParams_elab['fields'][] = array(  'name' => 'Elaboration_Status_Id', 'type' => 'int', 'value' => ElaborationStatus::FLUSSI_CREATI);
    
            $cls_db->DbSave($a_dbParams_elab);    
            $cls_flow->closeFile();
            $zip = new cls_zip();
            if($filter['PrinterId'] > 1)
                $zip->create_zip(array_merge($a_files, array($cls_flow->flowFile)),$cls_flow->flowArchiveFile);
            else
                $zip->create_zip($a_files,$cls_flow->flowArchiveFile);
               
            $query_list = " UPDATE elaboration_lists SET  FlowNumber = ".$cls_flow->flowNumber." , FlowId =".$cls_flow->IDFlusso." , FlowYear =".date('Y');
            $query_list.= " WHERE Id=".$filter['el_list_id'];
            $cls_db->ExecuteQuery($query_list);

            $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$elabs['CC']."'") );

            $atto_ = "";

            //if($terzo=="lavoro") { $atto_ = "Pignoramenti presso datore di lavoro";}
            //if($terzo=="banca") { $atto_ = "Pignoramenti presso banca";}

            switch($elabs['Document_Type_Id']){
                case 7:
                    $atto_ = "Pignoramenti presso datore di lavoro";
                    break;
                case 8:
                    $atto_ = "Pignoramenti presso banca";
                    break;
                case 22:
                    $atto_ = "Preavvisi fermi amministrativi";
                    break;
                default:
                    break;
            }

            $storico->insRow('E', "Creato flusso numero ".$cls_flow->flowNumber." elemento ".$cls_help->getVar('el_list_id')." elaborazione '".$elabs['Description']."': ".$atto_." ".$ente['Denominazione']."[".$elabs['CC']."]");
           
            
            echo "<script>endBar('Elaborazione completata');</script>";  
            flush();	ob_flush();		flush();	ob_flush();

            flush();	ob_flush();		flush();	ob_flush();

        }    

include(INC."/footer.php");
//$cls_db->End_Transaction();