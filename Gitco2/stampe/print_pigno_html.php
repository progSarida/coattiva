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
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_phpmailer.php";
//require_once DOMPDF . "/src/Autoloader.php";
//include_once SUPER_ROOT . "/cls/mpdf/src/Mpdf.php";
//require_once SUPER_ROOT . '/vendor/autoload.php';

$cls_file = new cls_file();
$cls_date = new cls_DateTimeI("IT",false);
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
$filter['taxStopFlag'] = "no";
$filter['dischargeFlag'] = "0";
$filter['sort'] = $cls_help->getVar('sort');

$filter['debitorNotificatonDateF'] = $cls_help->getVar('from_debitorNotificaton_Date');
$filter['debitorNotificatonDateT'] = $cls_help->getVar('to_debitorNotificaton_Date');
$filter['debitorNotificatonDateN'] = $cls_help->getVar('no_debitorNotificaton_Date');
$filter['shipmentDateF'] = $cls_help->getVar('from_shipment_date');
$filter['shipmentDateT'] = $cls_help->getVar('to_shipment_date');
$filter['shipmentDateN'] = $cls_help->getVar('no_shipment_date');
$filter['deliveryDateF'] = $cls_help->getVar('from_deliveryDate');
$filter['deliveryDateT'] = $cls_help->getVar('to_deliveryDate');
$filter['deliveryDateN'] = $cls_help->getVar('no_deliveryDate');
$filter['delivered'] = $cls_help->getVar('delivered');

//$cls_help->alert($filter["type"]." --- ".$filter["docType"]);

/*if($filter['printType']=="flow"&&$filter['PrinterId'] == 1){
    echo "<script>window.location = document.referrer + '&error=2&msg=Con stampatore Sarida non esiste il flusso.';</script>";
}*/

$cls_st = new cls_Stampe();
$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($filter['docType'],$filter['PrintTypeId'],null, array("PrinterId" => $filter["PrinterId"]));

//var_dump($cls_ruolo->a_docDetails);
//die;
//FILE DA SALVARE
$folder = "";

switch($filter['docType'])
{
    case "fermo":
    case "preav_fermo":
    case "veicolo": $folder = "/Pignoramenti/"; break;
    case "banca": $folder = "/Pignoramenti/Presso_Terzi/"; break;
    case "lavoro": $folder = "/Pignoramenti/Presso_Terzi/"; break;
    default: $folder = "/Pignoramenti/"; break;
}

$a_fileToSave = array();
$a_fileToSave['docDir'] = $cls_file->folderCreation( ATTI ."/". $c . $folder .$cls_ruolo->a_docDetails['dirName'] );

$tempFile = $cls_ruolo->a_docDetails['tempFileName']."_Temp_".date('Y-m-d_H-i-s').".pdf";
$a_fileToSave['rootTempPath'] = $cls_file->folderCreation($a_fileToSave['docDir']."/STAMPE PROVVISORIE");
$a_fileToSave['webTempPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootTempPath']);
$a_fileToSave['rootFinalPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/STAMPE DEFINITIVE");
$a_fileToSave['webFinalPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFinalPath']);
$a_fileToSave['rootFlowPath'] =  $cls_file->folderCreation($a_fileToSave['docDir']."/FLUSSI");
$a_fileToSave['webFlowPath'] = SUPER_WEB_ROOT.$cls_file->getWebPath($a_fileToSave['rootFlowPath']);

/*if($filter['printType']=="flow" && $filter['officialType']!="diretta"){
    $cls_help->alert("ATTENZIONE!!! Impossibile creare flussi con la selezione 'Tipo riscossione' diversa da 'Diretta'!");
    echo "<script>window.close();</script>";
}*/

//print_r($cls_ruolo->a_docDetails);
//echo $a_fileToSave['rootTempPath'];
//echo $a_fileToSave['rootTempPath'];
//die;
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

            window.opener.location.replace("info_flussi.php?a=<?php echo $a; ?>&c=<?php echo $c; ?>&docType=<?php echo $filter['docType']; ?>&stampatore=<?php echo $filter['PrinterId']; ?>&id_flows="+id_json);
            //}
            //$('#flusso_form').submit();
            window.close();
            //window.close();
        }

        function submitPEC(arr){


            var arr_json = JSON.stringify(arr, null, 0);

            //$("#sendMail").submit();

            window.opener.location.replace("invia_PEC.php?a=<?php echo $a; ?>&c=<?php echo $c; ?>&tipo=<?php echo $filter['docType']; ?>&stampatore=<?php echo $filter['PrinterId']; ?>&arrayTerzi="+arr_json);

            window.close();
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
$where = $cls_print->getWhereFromFilters($filter,null,"pigno");
$order = $cls_print->getOrder($filter['sort'],"pigno");

//$query = "SELECT * FROM v_pignoramento JOIN v_partita ON v_partita.Partita_ID = v_pignoramento.Partita_ID";

$fieldSelected = " * ";
$orderBy = " ORDER BY ".$order;
if($filter['printType']=="flow" && $filter['printStatus'] == "printed")
{
    $fieldSelected = " FlowId ";
    $orderBy = " GROUP BY FlowId ORDER BY FlowId ASC ";
}

$query = "SELECT v_pignoramento.".$fieldSelected."  FROM v_pignoramento ";
//$query .= " JOIN utente on v_partita.Utente_ID = utente.ID JOIN forma_giuridica_societa ON utente.Forma_Giuridica = forma_giuridica_societa.ID "; ******* , forma_giuridica_societa.Sigla as Sigla_Forma_Giuridica ******* JOIN document_type ON v_pignoramento.DocumentTypeId = document_type.Id
$query.= "WHERE 1=1 ";
if($filter['city']==$c)
    $query.= "AND v_pignoramento.CC='".$c."' ";
$query.= "AND ".$where." AND v_pignoramento.DocumentTypeId=".$cls_ruolo->a_docDetails['DocumentTypeId']." ".$orderBy;



//echo $query;
//die;
$a_results = $cls_db->getResults($cls_db->SelectQuery($query));


$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_subtext = $cls_db->getResults($cls_db->SelectQuery($cls_text->getSubParametersQuery($c,$cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_switchParams = array(
    "NotificationReport"    =>  $filter['officialType'],
    "SendType"  =>              $filter['PrintTypeId'],
    "Relata" => $filter["delivered"]
);


if($a_text['Content']==null){
    $cls_help->alert("ATTENZIONE!!! Parametri ".$cls_ruolo->a_docDetails['docType']." assenti per questo ente!");
    echo "<script>window.close();</script>";
}


//$cls_text->checkInformations();

$cls_registry = new cls_registry();


$cls_ente = new cls_ente($a_enteAdmin);

//var_dump($cls_ente->a_ente);

$cls_ente->setPrintHeader();
$managerCity = $cls_ente->getCityManager();
$managerContacts = $cls_ente->getContactsManager();
$placeDate = $managerCity.", ".$cls_help->toItalianDate($filter['finalDate']);

if($filter['printType']=="flow" && $filter['PrintTypeId'] != 1){
    $cls_help->alert("ATTENZIONE!!! Non è possibile inviare il flusso se il tipo di spedizione non è Raccomandata AG!");
    echo "<script>window.close();</script>";
}

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

$a_ivg = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("istituto", $a_tribunale["CC_Ufficio"])));
if(!is_array($a_ivg)){
    $cls_help->alert("ATTENZIONE!!! IVG non inserito!");
    echo "<script>window.close();</script>";
}

$a_ivgContacts = $cls_authority->getContacts($a_ivg);
$a_authority = array("CTP"=>$a_ctpContacts['complete'],"GDP"=>$a_gdpContacts['complete'],"Tribunale"=>$a_tribunaleContacts['complete'],"IVG" => $a_ivgContacts["complete"]);

$cls_params = new cls_parameters();

$a_paymentParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("pagamento", $c, $filter['taxType'])));
if(!is_array($a_paymentParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri di pagamento assenti per ".$filter['taxType']."!");
    echo "<script>window.close();</script>";
}
$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $filter['taxType'])));
//var_dump($a_responsibleParams);
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
$a_files = array();

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");

//$comune->Gestore_ID;
if( $comune->Gestore_ID != 0 ) {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
    $gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");
}
else {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
    $gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");
}

if($gestore->Tipo == "Concessionario")
    $firma_PEC = "Il gestore del servizio riscossione ".$gestore->Denominazione;
else
    $firma_PEC = "Il ".$gestore->Denominazione;


//INITIALIZE
if($filter['printType']=="flow"){
    //FLOW
    $cls_flow = new cls_flow($c,$cls_ruolo->a_docDetails,count($a_results),null,$a_fileToSave['rootFlowPath']);
    $cls_flow->setHeader("new");

    //$cls_db->Start_Transaction();
    //$cls_db->Begin_Transaction();
}
else{


    $a_recipientVariables = array();
    /*if($filter['docType'] == "AV_INT")
    {$a_ctpContacts['complete'],
            "{RECAPITI_GDP}" =>$a_gdpContacts['complete']

        /*************************************************************** CONTINUARE DA QUI *************************************************************/
    $a_recipientVariables = $cls_st->IndirizzoEnte($a_recipientVariables,$c);
    $a_recipientVariables["ctpContacts"] = $a_ctpContacts['complete'];
    $a_recipientVariables["gdpContacts"] = $a_gdpContacts['complete'];
    $a_recipientVariables = $cls_st->DataGestore($a_recipientVariables,$c);
    $a_recipientVariables = $cls_st->spese_notifica_pigno($a_recipientVariables,$c,$a);
    $a_recipientVariables["ExpenditureEstimateAssets"] = $cls_st->getStimaBeni($c);
    $a_recipientVariables["ManagerPec"] = $cls_st->GetPecGestore($c);



    //}

    $cls_text->set_varArray($cls_ente, $a_paymentParams, $a_yearParams, $cls_params, $a_appealParams, $a_authority, $a_recipientVariables);
}

//print_r($a_results);

    $a_ID = array();

    $arrayMail = array();

    for($i=0;$i<count($a_results);$i++){

        //echo $a_results[$i]['Atto_ID']." --- ".$filter['printStatus']."<br>";
        //$cls_help->alert("inizio"." --- ".ceil($i*100/count($a_results)));

        echo "<script>updateBar(".ceil($i*100/count($a_results)).");</script>";
        flush();	ob_flush();		flush();	ob_flush();
        // $cls_help->alert("qui-4");
        //if(!$filter["printType"] == "flow" && !$filter["printStatus"] == "printed")

        //$cls_help->alert("qui-3". " --- ".$filter['printStatus']);
        if ($filter['printStatus'] == "printed" && $filter['printType'] == "final"){
            $a_ID[] = $a_results[$i]['ID'];
            $finalFile = $a_fileToSave['rootFinalPath']."/".$cls_ruolo->a_docDetails['finalFileName']."_".$tipo_copia."_".$c."_".$a_results[$i]['Anno_Cronologico'];
            $finalFile.= "_".$a_results[$i]['ID_Cronologico']."_".$a_results[$i]['Data_Stampa'].".pdf";
            $a_files[] = $finalFile;



            continue;
        }
        else if($filter['printType']=="flow" && $filter['printStatus'] == "toPrint"){

            switch($filter['docType'])
            {
                case "veicolo": $tipoNotifica = "veicolo"; break;
                default: $tipoNotifica = "terzi"; break;
            }

            $query = "select * from notifica_atto where Atto_Notificato_ID = ".$a_results[$i]['ID']." AND Tipo_Notifica = '".$tipoNotifica."'";
            $all_terzi = $cls_db->getResults($cls_db->ExecuteQuery($query));
            //$count = isset($count_temp["Num_Record_Terzi"])?$count_temp["Num_Record_Terzi"]:0;
            $query = "select ID from notifica_atto where Atto_Notificato_ID = ".$a_results[$i]['ID']." AND Tipo_Notifica = 'debitore'";
            $ID_Debitore = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"notifica_atto")["ID"];
           /* $fileName="";
            for($z=0;$z<count($a_files);$z++)
            {
                $separator = "**";
                if($z==count($a_files)-1) $separator="";
                $fileName .= $a_files[$z].$separator;
            }*/

            $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);




            $flag = false;
            for($y=0,$x=0;$y < count($all_terzi)+2;$y++,$x++) {
                if ($y == count($all_terzi))
                    $tipo_copia = "originale";
                else if ($y == count($all_terzi) + 1)
                    $tipo_copia = "copia_debitore";
                else
                    $tipo_copia = "copia_terzo_" . $y;

                if($y>=count($all_terzi)) {
                    $x = count($all_terzi) - 1;
                    $flag = true;
                }

                if(($all_terzi[$x]["Modalita_Stampa"] == "posta" && !$flag) || $y > count($all_terzi) ) {

                    $fileName = $cls_ruolo->a_docDetails['finalFileName'] . $c . "_" . $a_results[$i]['Anno_Cronologico'];
                    $fileName .= "_" . $a_results[$i]['ID_Cronologico'] . "_" . $a_results[$i]['Data_Stampa'] . "_" . $tipo_copia . ".pdf";
                    $finalFile = $a_fileToSave['rootFinalPath'] . "/" . $fileName;
                    $a_files[] = $finalFile;

                    $data = array(
                        "Id_Table" => $a_results[$i]["ID"],
                        "DocumentTypeId" => $a_results[$i]["DocumentTypeId"],
                        "DocumentType" => $a_results[$i]["Nome_Pignoramento"],
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
                        "FileName" => $fileName,
                        "ID" => $flag==false?$all_terzi[$x]["ID"]:$ID_Debitore
                    );

                    $cls_flow->SetNewHeader($data);

                    $cls_flow->setFlowRow("new");
                }

            }




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


            $query = "UPDATE pignoramento_generale SET Data_Flusso = '".$cls_flow->flowDate."', Anno_Flusso = ".$cls_flow->flowYear.", Numero_Flusso = ".$cls_flow->flowNumber;
            $query.= " WHERE ID=".$a_results[$i]['ID'];
            $cls_db->ExecuteQuery($query);


            /****************************************************************************** **********************************************************************************/
            $a_ID[] = $a_results[$i]['ID'];

            continue;
        }else if($filter['printType']=="flow" && $filter['printStatus'] == "printed"){
            $a_ID[] = $a_results[$i]['FlowId'];

            continue;
        }



        if(!isset($a_recipientHeader))
            $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);


        $a_results[$i]['RettificaDetails'] = "";
        if(isset($a_results[$i]['Atto_Rettificato']))
            if($a_results[$i]['Atto_Rettificato']==1){
                $query = "SELECT * FROM pignoramento_generale JOIN document_type ON pignoramento_generale.DocumentTypeId = document_type.Id WHERE Partita_ID=".$a_results[$i]['Partita_ID']." AND ID<".$a_results[$i]['ID']." ORDER BY ID DESC LIMIT 1";
                $a_rettifica = $cls_db->getArrayLine($cls_db->SelectQuery($query));
                $a_results[$i]['RettificaDetails'] = " DELL'".strtoupper($a_rettifica['Description'])." N. ".$a_rettifica['ID_Cronologico']."/".$a_rettifica['Anno_Cronologico'];
            }

        //GET IMPORTI STAMPA
        $cls_ruolo->setResultArray($a_results[$i]);
        $cls_ruolo->splitCodiciTributo();
        $cls_ruolo->setPrintAmounts($cls_ruolo->a_docDetails['docType'],$a_yearParams,"pigno");

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
        $a_recipientVariablesRow = $cls_st->InfoETotPag($a_recipientVariablesRow,$a_results[$i]["Partita_ID"],$a_results[$i]["ID"],$a_yearParams);
        $a_recipientVariablesRow = $cls_st->InfoAtto($a_recipientVariablesRow,$cls_ruolo->a_result['ID_Cronologico'],$cls_ruolo->a_result['Anno_Cronologico'],$cls_ruolo->a_result['Nome_Pignoramento'],$cls_ruolo->a_result['Protocollo'],$cls_ruolo->a_result['Data_Notifica']);
        $a_recipientVariablesRow["User"] = $a_results[$i]["Cognome_Ditta"]." ".$a_results[$i]["Nome"];
        $a_recipientVariablesRow["UserCode"] = $a_results[$i]["Utente_ID"]."/".$c;
        $a_recipientVariablesRow = $cls_st->getDataVisura($a_recipientVariablesRow,$a_results[$i]["ID"],$c);
        $a_recipientVariablesRow["attiNot"] = $cls_st->tutti_gli_atti_notificati($a_results[$i]["Partita_ID"]);
        $a_recipientVariablesRow["SalesInstitute"] = strtoupper(implode(" ",$a_recipientHeader['denomination'])." ".$a_results[$i]["Cognome_Ditta"]);
        $a_recipientVariablesRow["CommonCourt"] = $cls_st->GetTribunaleUtente($a_results[$i]["Res_CC"]);
        $a_recipientVariablesRow["AttoPrec"] = $cls_st->GetAttoPrec($a_results[$i]["Atto_ID"],$c);
        $a_recipientVariablesRow["MinMaxPigno"] = $cls_st->GetRiscossioneMinMax($a_results[$i]["Atto_ID"]);
        $a_recipientVariablesRow["ForeclosedVehicles"] = $cls_st->GetVehicle($a_results[$i],$c);
        $a_recipientVariablesRow["NomeComune"] = $comune->Denominazione;

        $nomiTerzi = $cls_st->GetTerziPigno($a_results[$i]["ID"],$a_results[$i]["Tipo"],$c,$filter["docType"]);
        $Arr_Terzi = $nomiTerzi["Arr_Terzi"];
        $falgDebit = $nomiTerzi["ID_Debit"];

        //var_dump($Arr_Terzi);
        //die;

        $a_recipientVariablesRow["Terzi"] = $nomiTerzi["terzi"];
        $a_recipientVariablesRow["TerziProTempore"] = $nomiTerzi["terzi_pro_tempore"];

        /*if($a_results[$i]["Genere"] == "D")
            $a_recipientVariablesRow["CF_PI"] = "Partita Iva: ".$a_results[$i]["CF_PI"];
        else*/
        $a_recipientVariablesRow["CF_PI"] = "Codice Fiscale: ".$a_results[$i]["CF_PI"];

        $indirizzo_tribunale = $cls_st->indirizzoUtente($a_results[$i]["Utente_ID"],$c);
        $indirizzo = $indirizzo_tribunale["indirizzo_utente"];
        $tribunale = $indirizzo_tribunale["tribunale_utente"];

        $a_recipientVariablesRow["OfficialText"] = $cls_st->testoUfficiale($a_results[$i],$tribunale,$c);
        $a_recipientVariablesRow["UserResidence"] = isset($indirizzo['Senza_Provincia'])?$indirizzo['Senza_Provincia']:null;
        $a_recipientVariablesRow["HeadquartersSalesInstitute"] = $indirizzo_tribunale["indirizzo_istituto"]['Completo'];
        $a_recipientVariablesRow["ContactInstitution"] = $indirizzo_tribunale["recapiti_istituto"];
        if($a_results[$i]["Modalita_Stampa"]=="posta")
            $a_recipientVariablesRow["SendType"] = "tramite posta";
        else if($a_results[$i]["Modalita_Stampa"]=="mani")
            $a_recipientVariablesRow["SendType"] = "mediante consegna a mani";
        else if($a_results[$i]["Modalita_Stampa"]=="pec")
            $a_recipientVariablesRow["SendType"] = "al seguente indirizzo di posta elettronica certificata ".$indirizzo_tribunale["istituto_vendite"]->PEC;
        else $a_recipientVariablesRow["SendType"] = null;
        // }



        if($filter['printType']!="flow"){

            $flag = false;
            $contatoreArrayMail = 0;

            for($y=0,$x=0;$y < count($Arr_Terzi)+2;$y++,$x++) {

                if ($y == count($Arr_Terzi)) {
                    $tipo_copia_titolo = "ORIGINALE";
                    $tipo_copia = "originale";
                }
                else if ($y == count($Arr_Terzi)+1) {
                    $tipo_copia_titolo = "COPIA DEBITORE";
                    $tipo_copia = "copia_debitore";
                }
                else {
                    $tipo_copia_titolo = "COPIA TERZO";
                    $tipo_copia = "copia_terzo_" . $y;
                }

                if($y>=count($Arr_Terzi)) {
                    $x = count($Arr_Terzi) - 1;
                    $flag = true;
                }

                $a_recipientVariablesRow["PrintType"] = $tipo_copia_titolo;

                $cls_text->set_varArrayRow($cls_ruolo, $a_recipientHeader,$a_yearParams,$a_recipientVariablesRow,"pigno");


                $cls_text->html_body = $a_text['Content'];
                $cls_text->replaceSubtext($a_subtext,$a_switchParams);
                $cls_text->replaceVariables($cls_text->a_var);

                $a_causal = $cls_ruolo->getReferences("pigno");

                if($tipo_copia == "copia_debitore") {
                    $cls_postal->setPostalParams($a_recipientHeader, $a_causal, $cls_ruolo->getPostalClient($a_enteAdmin['ID'], 1, "pigno"));
                    $a_postal = array();
                    $a_postal[1] = $cls_postal->getPostalArray(1, $cls_ente->logo, $a_results[$i]['Totale_Dovuto'] + $a_recipientVariablesRow["MinMaxPigno"]['Riscossione_Min']);
                    $a_postal[2] = $cls_postal->getPostalArray(2, $cls_ente->logo, $a_results[$i]['Totale_Dovuto'] + $a_recipientVariablesRow["MinMaxPigno"]['Riscossione_Max']);
                }

                $tempFile = $cls_ruolo->a_docDetails['tempFileName'] . "_" . $tipo_copia . "_Temp_" . date('Y-m-d_H-i-s') . ".pdf";



              /*  if(($Arr_Terzi[$x]["Modalita_Stampa"] == "posta" && !$flag) || $y >= count($Arr_Terzi) )
                {*/
                    //if ($filter['printType'] == "final") {
                       // $cls_help->alert("qui");
                        $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
                    //}

                    $pdf->setDocParams();
                    $pdf->SetAutoPageBreak(true);
                    $pdf->AddPage("P");
                    if ($filter['printType'] == "temp")
                        $pdf->temporaryPrinting();
                    $pdf->setManagerHeader($cls_ente->a_header);
                    $pdf->setRecipientHeader($a_recipientHeader);
                    $pdf->SetMargins(7.0, 10.0, 7.0);
                    $pdf->ln(0);


                    //$cls_help->alert("qui 2");
                    //var_dump($cls_text->html_replaced_body);
                    /********************************************* *****************************************************************/
                    //$mpdf = new \Mpdf\Mpdf();

                    //$mpdf->Bookmark('Start of the document');
                    //$mpdf->WriteHTML($cls_text->html_replaced_body);


                    /********************************************* DA RIATTIVARE QUI SOTTO *****************************************************************/
                    //$pdf->setPageMark();
                    $pdf->SetFont('helvetica', '', 9);
                   // $pdf->SetFont('courier', '', 9);
                    $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0,'');

                    /************************************************** ****************************************************************/
                    // die;
                    if($tipo_copia == "copia_debitore")
                        $pdf->setPostalBill($a_postal, 2, $filter['printType']);

                    if ($filter['printType'] == "final") {
                        $pdf->SetProtection(array('modify'),$a_results[$i]["CF_PI"],"test01",0);

                        $finalFile = $a_fileToSave['rootFinalPath'] . "/" . $cls_ruolo->a_docDetails['finalFileName'] . $c . "_" . $a_results[$i]['Anno_Cronologico'];
                        $finalFile .= "_" . $a_results[$i]['ID_Cronologico'] . "_" . $filter['finalDate'] . "_" . $tipo_copia . ".pdf";

                        $query = "UPDATE pignoramento_generale SET Data_Stampa = '" . $filter['finalDate'] . "', Stato_Stampa = 'Stampato' ";
                        $query .= "WHERE ID=" . $a_results[$i]['ID'];
                        $cls_db->ExecuteQuery($query);
                        $pdf->Output($finalFile, 'F');
                        //if($y != count($Arr_Terzi))
                        $a_files[] = $finalFile;

                    } else if ($filter['printType'] == "temp") {
                        //$mpdf->Output($a_fileToSave['rootTempPath']."/".$tempFile , 'F');
                        $pdf->Output($a_fileToSave['rootTempPath'] . "/" . $tempFile, 'F');
                        $a_files[] = $a_fileToSave['rootTempPath'] . "/" . $tempFile;
                    }
                    else if($filter['printType'] == "pec")
                    {
                        /*if($tipo_copia_titolo == "COPIA TERZO")
                            $a_files[$i][] = $cls_ruolo->a_docDetails['finalFileName'] . $c . "_" . $a_results[$i]['Anno_Cronologico']."_" . $a_results[$i]['ID_Cronologico'] . "_" . $filter['finalDate'] . "_" . $tipo_copia . ".pdf";
                        else if($falgDebit > 0 && $tipo_copia_titolo == "COPIA DEBITORE")
                            $a_files[$i][] = $cls_ruolo->a_docDetails['finalFileName'] . $c . "_" . $a_results[$i]['Anno_Cronologico']."_" . $a_results[$i]['ID_Cronologico'] . "_" . $filter['finalDate'] . "_" . $tipo_copia . ".pdf";*/

                        //$AllTerzi[$i] = $Arr_Terzi;

                        /*if($tipo_copia_titolo != "ORIGINALE")
                        {
                            if(($Arr_Terzi[$x]["Modalita_Stampa"] == "pec" && !$flag) || $tipo_copia_titolo == "COPIA DEBITORE" ) {

                                $key = "";
                                switch($filter["docType"])
                                {
                                    case "banca":
                                        if($Arr_Terzi[$i]["Dati_Terzo"]["PEC"]!=null && $Arr_Terzi[$i]["Dati_Terzo"]["PEC"] != "") {$tipoMail = "PEC"; $PEC = $Arr_Terzi[$i]["Dati_Terzo"]["PEC"];}
                                        else if($Arr_Terzi[$i]["Dati_Terzo"]["Mail"]!=null && $Arr_Terzi[$i]["Dati_Terzo"]["Mail"] != ""){$tipoMail = "Mail"; $PEC = $Arr_Terzi[$i]["Dati_Terzo"]["Mail"];}
                                        else {$tipoMail = "NotFound"; $PEC = "";}
                                        break;
                                    case "lavoro":
                                        if($Arr_Terzi[$i]["Dati_Terzo"]["Utente_PEC"]!=null && $Arr_Terzi[$i]["Dati_Terzo"]["Utente_PEC"] != "") {$tipoMail = "PEC"; $PEC = $Arr_Terzi[$i]["Dati_Terzo"]["Utente_PEC"];}
                                        else if($Arr_Terzi[$i]["Dati_Terzo"]["Utente_Email"]!=null && $Arr_Terzi[$i]["Dati_Terzo"]["Utente_Email"] != ""){$tipoMail = "Mail"; $PEC = $Arr_Terzi[$i]["Dati_Terzo"]["Utente_Email"];}
                                        else {$tipoMail = "NotFound"; $PEC = "";}
                                        break;
                                    case "veicolo":
                                        if($a_ivg["PEC"]!=null && $a_ivg["PEC"] != "") {$tipoMail = "PEC"; $PEC = $a_ivg["PEC"];}
                                        else if($a_ivg["Mail"]!=null && $a_ivg["Mail"] != ""){$tipoMail = "Mail"; $PEC = $a_ivg["Mail"];}
                                        else {$tipoMail = "NotFound"; $PEC = "";}
                                        break;
                                }

                                if($tipoMail=="PEC") $ricevuta_consegna = "attesa";
                                else if($tipoMail=="Mail") $ricevuta_consegna = "no";


                                //var_dump($a_ivg);
                                //die;
                                $PEC_Utente = $a_results[$i]["Utente_PEC"];
                                if($a_results[$i]["Utente_PEC"] == null || $a_results[$i]["Utente_PEC"] == "")
                                    $PEC_Utente = $a_results[$i]["Utente_Email"];

                                $arrayMail[$i][$contatoreArrayMail]["Nome_Ditta"] = $a_results[$i]["Cognome_Ditta"]." ".$a_results[$i]["Nome"];
                                $arrayMail[$i][$contatoreArrayMail]["CF_PI"] = $a_results[$i]["CF_PI"];
                                $arrayMail[$i][$contatoreArrayMail]["Password"] = "*****";
                                $arrayMail[$i][$contatoreArrayMail]["firma_PEC"] = $firma_PEC;
                                $arrayMail[$i][$contatoreArrayMail]["Tipo"] = $tipo_copia_titolo;
                                $arrayMail[$i][$contatoreArrayMail]["TipoCopia"] = $tipo_copia;
                                $arrayMail[$i][$contatoreArrayMail]["Crono_ID"] = $a_results[$i]['ID_Cronologico'];
                                $arrayMail[$i][$contatoreArrayMail]["Crono_Year"] = $a_results[$i]['Anno_Cronologico'];
                                $arrayMail[$i][$contatoreArrayMail]["Tipo_Mail_Dest"] = $tipoMail;
                                $arrayMail[$i][$contatoreArrayMail]["Partita_ID"] = $a_results[$i]["Partita_ID"];
                                $arrayMail[$i][$contatoreArrayMail]["Utente_ID"] = $a_results[$i]["Utente_ID"];
                                $arrayMail[$i][$contatoreArrayMail]["Ricevuta_Consegna"] = $ricevuta_consegna;
                                $arrayMail[$i][$contatoreArrayMail]["Data_Stampa"] = $filter['finalDate'];//solo se null se no da result*/

                                $arrayMail[$i]["ID_Pigno"] = $a_results[$i]['ID'];

                                /*if ($tipo_copia_titolo == "COPIA TERZO") {
                                    $arrayMail[$i][$contatoreArrayMail]["ID_Collegato"] = $Arr_Terzi[$i]["ID_Notifica_Atto"];
                                    $arrayMail[$i][$contatoreArrayMail]["PEC"] = $PEC;
                                }
                                else if (count($falgDebit) > 0 && $tipo_copia_titolo == "COPIA DEBITORE") {
                                    $arrayMail[$i][$contatoreArrayMail]["ID_Collegato"] = $falgDebit[0]["ID"];
                                    $arrayMail[$i][$contatoreArrayMail]["PEC"] = $PEC_Utente;
                                }

                                if ($tipo_copia_titolo == "COPIA TERZO" || (count($falgDebit) > 0 && $tipo_copia_titolo == "COPIA DEBITORE")) {
                                    $arrayMail[$i][$contatoreArrayMail]["FileName"] = $cls_ruolo->a_docDetails['finalFileName'] . $c . "_" . $a_results[$i]['Anno_Cronologico'] . "_" . $a_results[$i]['ID_Cronologico'] . "_" . $a_results[$i]['Data_Stampa'] . "_" . $tipo_copia . ".pdf";
                                    $contatoreArrayMail++;
                                }
                            }
                        }*/
                    }
               // }
            }

        }
    }
//var_dump($a_files);
//die;
//}
//$cls_help->alert("fuori for");

if(count($a_results) == 0)
    echo "<script>noResultsBar();</script>";
else{
    $cls_file->removeFiles($a_fileToSave['rootTempPath'], 7);

    if($filter['printType']=="temp"){
        //die;
        function getmicrotime(){
            list($usec, $sec) = explode(" ",microtime());
            return ((float)$usec + (float)$sec);
        }
        echo "<script>startMerge();</script>";
        flush(); ob_flush(); flush(); ob_flush();
        sleep(1);

        $cls_merge = new cls_merge();
        $cls_merge->setFiles($a_files);

        $time_start = getmicrotime();//sec iniziali
        $cls_merge->concatFiles(true);
        $time_end = getmicrotime();//sec finali
        $time = $time_end - $time_start;//differenza in secondi

        $mergeFileName = "Merge_Temp_".date('Y-m-d_H-i-s').".pdf";
        $mergeFile = $a_fileToSave['rootTempPath']."/".$mergeFileName;

        $tempo_previsto_sec = $time * 20;
        if($tempo_previsto_sec<55)
            $tempo_previsto = "1 minuto";
        else
            $tempo_previsto = floor($tempo_previsto_sec/60+1)." minuti";

        echo "<script>endMerge(\"Creazione file in corso... Il tempo previsto per le operazioni e' di circa ".$tempo_previsto.".\",\"\");</script>";
        flush(); ob_flush(); flush(); ob_flush();

        set_time_limit($tempo_previsto_sec+200);
        flush(); ob_flush(); flush(); ob_flush();

        //$pdf->Output( $a_fileToSave['rootTempPath']."/".$tempFile, 'F' );
        //echo "<script>endBar('Elaborazione completata',\"".$a_fileToSave['webTempPath']."/".$tempFile."\");</script>";
        //$tempFile = $cls_ruolo->a_docDetails['tempFileName']."_".$tipo_copia."_Temp_".date('Y-m-d_H-i-s').".pdf";
        flush();	ob_flush();		flush();	ob_flush();

        //var_dump($a_ivg);
        //die;
        $cls_merge->Output($mergeFile, "F");
        /*************************************** ***************************************************/

        echo "<script>endMerge('Elaborazione completata',\"".$a_fileToSave['webTempPath']."/".$mergeFileName."\");</script>";
    }
    else if($filter['printType']=="final"){
        /*function getmicrotime(){
            list($usec, $sec) = explode(" ",microtime());
            return ((float)$usec + (float)$sec);
        }
        //echo "<h1>Final Date".$filter['finalDate']."</h1></br>";
        //$finalDate = $filter['finalDate'];
        //if($finalDate == "" || $finalDate == null)
        //$finalDate = $cls_ruolo->a_result['Anno_Cronologico']."_".$cls_ruolo->a_result['ID_Cronologico'];
        $finalDate = $cls_ruolo->a_result['Anno_Cronologico']."_".$cls_ruolo->a_result['ID_Cronologico'];
        $mergeFile = $cls_ruolo->a_docDetails['finalFileName'].$c."_".$finalDate."_".date('Y-m-d')."_originale.pdf";

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

        /********************************* decommentare *******************************************
        //echo "<h1>Path: ".$a_fileToSave['rootFinalPath']."/".$mergeFile."</h1>";
        //die;
        $cls_merge->Output($a_fileToSave['rootFinalPath']."/".$mergeFile, "F");

        //$a_files[] = $a_fileToSave['rootFinalPath']."/".$mergeFile;
        /*************************************** ***************************************************/
        $finalDate = $cls_ruolo->a_result['Anno_Cronologico']."_".$cls_ruolo->a_result['ID_Cronologico'];
        $finalFile = $cls_ruolo->a_docDetails['finalFileName'].$c."_".$finalDate."_".date('Y-m-d')."_originale.pdf";

        echo "<script>endBar('Elaborazione completata',\"".$a_fileToSave['webFinalPath']."/".$finalFile."\");</script>";
        //echo "<script>endMerge('Elaborazione completata',\"".$a_fileToSave['webFinalPath']."/".$mergeFile."\");</script>";
    }
    else if($filter['printType']=="flow"){
        if($filter['printStatus']=="toPrint"){
            if($filter['PrinterId'] > 1)
            {
                $cls_flow->closeFile("pigno");

                $a_files = array_merge($a_files, array($cls_flow->flowFile));
                $zip = new cls_zip();
                $zip->create_zip($a_files,$cls_flow->flowArchiveFile);

                /****************************** FORSE QUESTO DA RIMUOVERE NON C'ERA **********************************************/
                //$cls_flow->addFlowToFlowsTable(null,"pigno");
            }
            else{
                $cls_flow->addFlowToFlowsTable(null,"pigno");
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

        }

    }
    else if ($filter['printType'] == "pec"){


        /*$arrayMail["LBV"]['FileName'] = $a_files;
        $arrayMail["LBV"]["Terzi"] = $AllTerzi;
        if($filter['docType'] == "veicolo")
            $arrayMail["LBV"]["Terzi"][0]["Dati_Terzo"] = $a_ivg;*/

        echo "<script>endBar('Elaborazione completata','');</script>";
        flush();	ob_flush();		flush();	ob_flush();
        echo "<script>submitPEC(".json_encode($arrayMail).");</script>";
        flush();	ob_flush();		flush();	ob_flush();

    }
}


include(INC."/footer.php");
//$cls_db->End_Transaction();