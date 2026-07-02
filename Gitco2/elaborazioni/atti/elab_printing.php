<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");

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
include_once CLS . "/cls_storico.php";	
include_once CLS . "/cls_DateTimeInLine.php";													

$storico = new storico('storicoElaborazioni','5');	
$cls_file = new cls_file();
$cls_date = new cls_DateTimeI("IT",false);

set_time_limit(-1);
ini_set('memory_limit', '-1');

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$filter = array();

$filter['el_list_id'] = intval($cls_help->getVar('el_list_id'));

$query = "SELECT EL.*, E.CC, TT.Name AS Tipo_Riscossione ";
$query.= "FROM elaboration_lists EL ";
$query.= "JOIN elaborations E ON E.Id=EL.Elaboration_Id ";
$query.= "JOIN tax_type TT ON TT.Id = EL.TaxTypeId ";
$query.= "WHERE EL.ID=".$filter['el_list_id'];

$a_elab_list = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

if($a_elab_list['PrintFlag']==1)
    $filter['printType'] = "final";
else
    $filter['printType'] = "temp";
$filter['city'] = $a_elab_list['CC'];
$filter['last_el_id']  = $a_elab_list['Elaboration_Id'];
$filter['PrinterId'] = $a_elab_list['PrinterId'];
$filter['PrintTypeId'] = $a_elab_list['PrintTypeId'];
$filter['doc_type_id'] = $a_elab_list['DocumentTypeId'];
$filter['officialType'] = $a_elab_list['NotificationType'];
$filter['taxType'] = $a_elab_list['Tipo_Riscossione'];

$filter['finalDate'] = date('Y-m-d');

$queryInteressiTributi = "SELECT DATE_FORMAT(Data_Inizio, '%d/%m/%Y') as Data_Inizio, DATE_FORMAT(Data_Fine, '%d/%m/%Y') as Data_Fine, CONCAT(REPLACE(Tasso_Interessi,'.',','),' &#x25;') as Tasso_Interessi FROM interessi_tributi WHERE CC = '****' ORDER BY Data_Inizio ASC;";
$interessiTributi = $cls_db->getResults($cls_db->ExecuteQuery($queryInteressiTributi));

//var_dump($interessiTributi);die;

$cls_st = new cls_Stampe();
$cls_ruolo = new cls_ruolo();
$cls_ruolo->getDocumentDetails($filter['doc_type_id'], $filter['PrintTypeId'], null, array("PrinterId" => $filter["PrinterId"]));
$cls_ruolo->setTributeInterest($interessiTributi);

//FILE DA SALVARE
$a_fileToSave = array();
$a_fileToSave['docDir'] = $cls_file->folderCreation(ATTI . "/" . $filter['city'] . "/" . $cls_ruolo->a_docDetails['dirName']);

$tempFile = $cls_ruolo->a_docDetails['tempFileName'] . "_Temp_" . date('Y-m-d_H-i-s') . ".pdf";
$a_fileToSave['rootTempPath'] = $cls_file->folderCreation($a_fileToSave['docDir'] . "/STAMPE PROVVISORIE");
$a_fileToSave['webTempPath'] = SUPER_WEB_ROOT . $cls_file->getWebPath($a_fileToSave['rootTempPath']);
$a_fileToSave['rootFinalPath'] =  $cls_file->folderCreation($a_fileToSave['docDir'] . "/STAMPE DEFINITIVE");
$a_fileToSave['webFinalPath'] = SUPER_WEB_ROOT . $cls_file->getWebPath($a_fileToSave['rootFinalPath']);

//$a_fileToSave['rootUnionPath'] = $cls_file->folderCreation($a_fileToSave['docDir'] . "/STAMPE UNITE");
//$a_fileToSave['webUnionPath'] = SUPER_WEB_ROOT . $cls_file->getWebPath($a_fileToSave['rootUnionPath']);

$redirectPage = ELAB_ATTI_WEB."/mgmt_elaboration.php?c=".$c."&a=".$a."&el=".$a_elab_list['Elaboration_Id'];
?>

<script>
    var redirectPage = "<?= $redirectPage; ?>";
    function redirect(){
        if (redirectPage != "") {
            sleep(1000);

            window.name = "Stampa";
            window.open(redirectPage, "Stampa");
            window.close();
        }
    }

    function startBar() {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text("Inizio elaborazione...");
    }

    function waitBar(text) {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text(text);
    }

    function updateBar(valore) {
        //alert(valore);
        $("#progressbar").progressbar({
            value: parseInt(valore)
        });
        $("#barlabel").text(valore + "%");
    }

    function noResultsBar() {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text("Nessun risultato trovato");
    }

    function endBar(value, webFile) {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text(value);

        if (webFile !== undefined) {
            sleep(1000);

            window.name = "Stampa";
            window.open(webFile, "_blank");
        }

    }

    function startMerge() {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text("Elaborazione completata!");

        $('#progressbar2').progressbar({
            value: false
        });
        $("#barlabel2").text("Inizio creazione file di stampa...");
    }

    function updateMerge(valore) {
        $("#progressbar2").progressbar({
            value: parseInt(valore)
        });
        $("#barlabel2").text(valore + "%");
    }

    function endMerge(value, value2) {
        $("#progressbar2").progressbar({
            value: 100
        });
        $("#barlabel2").text(value);

        if (value2 != "") {
            sleep(1000);

            window.name = "Stampa";
            window.open(value2, "_blank");
        }

    }

    function submitCrono() {
        $('#crono_form').submit();
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
        <td>
            <div class="table_interna text_center" id="progressbar" style="height:55px;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </td>
    </tr>
    <tr>
        <td><br></td>
    </tr>
    <tr>
        <td>
            <div class="table_interna text_center" id="progressbar2" style="height:55px;">
                <div class="text_center" id="barlabel2"></div>
            </div>
        </td>
    </tr>
</table>
<br /><br />
<div class="col col-md-auto text_center">
    <span class="titolo font16 under_decor" style="color:red;">Non chiudere la finestra prima del termine della procedura</span>
</div>
<?php

flush();ob_flush();flush();ob_flush();
echo "<script>startBar();</script>";
flush();ob_flush();flush();ob_flush();

$query_v_atti = "SELECT * FROM v_atti WHERE Atto_Elaboration_List_Id = ".$filter['el_list_id']." AND CC = '" . $filter['city'] . "' ";
if ($filter['printType'] == "temp")
    $query_v_atti.= "LIMIT 5";

$a_results = $cls_db->getResults($cls_db->SelectQuery($query_v_atti));
$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$filter['city']."'") );
$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($filter['city'], $cls_ruolo->a_docDetails['DocumentTypeId'])));
$cls_text->setHtmlBody($a_text['Content']);
$a_subtext = $cls_db->getResults($cls_db->SelectQuery($cls_text->getSubParametersQuery($filter['city'], $cls_ruolo->a_docDetails['DocumentTypeId'])));
$a_subtextParams = array(
    "NotificationType"      =>  $filter['officialType'],
    "PrintTypeId"           =>  $filter['PrintTypeId']
);

$a_entePrinting = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '" . $filter['city'] . "'"));

//var_dump($a_entePrinting);die;

$cls_ente = new cls_ente($a_entePrinting);
$cls_text->setParamsArray($cls_ente->a_ente,'ente');

$cls_params = new cls_parameters();

$a_yearParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("annuali", $filter['city'])));
$cls_text->setParamsArray($a_yearParams,'year');

$a_appealParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("ricorso", $filter['city'])));
$cls_text->setParamsArray($a_appealParams,'appeal');

$a_paymentParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("pagamento", $filter['city'], $filter['taxType'])));
$cls_text->setParamsArray($a_paymentParams,'payment');
$cls_postal = new cls_postal($a_paymentParams);

//PARAMETRI RESPONSABILI
$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $filter['city'], $filter['taxType'])));
$cls_params->setArray("responsabili", $a_responsibleParams);
$cls_params->getSignatures($cls_ente->type);
$cls_text->setParamsArray($cls_params->a_signature,'responsibles');

//PARAMETRI UFFICIO
$a_officeParams = $cls_db->getArrayLineNull($cls_db->SelectQuery($cls_params->getRecordsQuery("ufficio", $filter['city'], $filter['taxType'])),"parametri_ufficio");

//PARAMETRI AUTORITA'
$cls_authority = new cls_authority();

$a_gdp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("giudice", $filter['city'])));
$a_gdpContacts = $cls_authority->getContacts($a_gdp);
$a_cgt = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("cort_giust_trib", $filter['city'])));
$a_cgtContacts = $cls_authority->getContacts($a_cgt);
$a_tribunale = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("tribunale", $filter['city'])));
$a_tribunaleContacts = $cls_authority->getContacts($a_tribunale);
$a_authority = array("CGT" => $a_cgtContacts['complete'], "GDP" => $a_gdpContacts['complete'], "Tribunale" => $a_tribunaleContacts['complete']);
$cls_text->setParamsArray($a_authority,'authority');

$cls_registry = new cls_registry();

$a_generalParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("generali", $filter['city'], $filter['taxType'])));
$cls_ente->setPrintHeaderLAB($filter['printType'], $a_generalParams);
$placeDate = $cls_ente->getCityManager() . ", " . $cls_help->toItalianDate(date('Y-m-d'));

$queryCorteTrib = "SELECT * FROM ufficio_giudiziario WHERE CC=\"".$filter['city']."\" AND Tipo=\"cort_giust_trib\"";
$a_corteTrib = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($queryCorteTrib),"ufficio_giudiziario");

$cls_text->setParamsVarLAB();

$stampati = array();

$a_ID = array();
$a_files = array();

if ($filter['printType'] == "temp")
    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

for ($i = 0; $i < count($a_results); $i++) {
    flush();ob_flush();flush();ob_flush();
    echo "<script>updateBar(" . ceil($i * 100 / count($a_results)) . ");</script>";
    flush();ob_flush();flush();ob_flush();

    if ($filter['printType'] == "unitaria") {
        $a_docPath = $cls_ruolo->getDocCompletePath($filter['city'], $a_results[$i]);
        $a_files[] = $a_docPath['root'];
        continue;
    }

    $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);

    if($a_results[$i]['Flag_Blocco_Diritto_Riscossione']=="si" || $a_results[$i]['Diritto_Riscossione_Minimo']==0)
        $a_subtextParams['DirittoRiscossione'] = 0;
    else
        $a_subtextParams['DirittoRiscossione'] = 1;

    if($a_results[$i]['Tipo_Riscossione'] == "CDS"){
        $a_subtextParams['CDS_Altri'] = 1;
    }
    else{
        $a_subtextParams['CDS_Altri'] = 2;

    }

    $a_results[$i]['RettificaDetails'] = "";
    if ($a_results[$i]['Atto_Rettificato'] == 1) {
        $query = "SELECT * FROM atto WHERE Partita_ID=" . $a_results[$i]['Partita_ID'] . " AND ID<" . $a_results[$i]['Atto_ID'] . " ORDER BY ID DESC LIMIT 1";
        $a_rettifica = $cls_db->getArrayLine($cls_db->SelectQuery($query));
        $a_results[$i]['RettificaDetails'] = " DELL'" . strtoupper($a_rettifica['Atto']) . " N. " . $a_rettifica['ID_Cronologico'] . "/" . $a_rettifica['Anno_Cronologico'];
    }

    //GET IMPORTI STAMPA
    $cls_ruolo->setResultArray($a_results[$i]);
    $cls_ruolo->setDocAmountsLAB($cls_ruolo->a_docDetails['DocumentTypeId'], $a_yearParams, "atto");

    $a_recipientHeader['references'][0] = "PARTITA NUMERO:  " . $a_results[$i]['Comune_ID'] . " / " . $a_results[$i]['Anno_Riferimento'];
    $a_recipientHeader['references'][1] = "CODICE UTENTE:  " . $a_results[$i]['Utente_Comune_ID'] . " / " . $a_results[$i]['CC'];
    if ($a_results[$i]['Protocollo'] != "") {
        $a_recipientHeader['references'][2] = "PROTOCOLLO:  " . $a_results[$i]['Protocollo'];
        $a_recipientHeader['references'][3] = "DEL:  " . $cls_help->toItalianDate($a_results[$i]['Data_Protocollo']);
    } else {
        $a_recipientHeader['references'][2] = "";
        $a_recipientHeader['references'][3] = "";
    }
    $a_recipientHeader['placeDate'] = $placeDate;

    $nomeUtente = "";
    if(isset($a_results[$i]['Cognome_Ditta'])) $nomeUtente .= $a_results[$i]['Cognome_Ditta'];
    if(isset($a_results[$i]["Nome"])) $nomeUtente .= " ".$a_results[$i]["Nome"];
    $CF_PI = "";
    if(isset($a_results[$i]["CF_PI"])) $CF_PI .= is_numeric($a_results[$i]["CF_PI"]) == true ? "PI: ".$a_results[$i]["CF_PI"] : "CF: ".$a_results[$i]["CF_PI"];

    $datiNascitaUtente = "";
    if(isset($a_results[$i]['Comune_Nascita'])) $datiNascitaUtente .= "nato a ".$a_results[$i]['Comune_Nascita'];
    if(isset($a_results[$i]['Provincia_Nascita'])) $datiNascitaUtente .= " ".$a_results[$i]['Provincia_Nascita'];
    if(isset($a_results[$i]['Data_Nascita'])) $datiNascitaUtente .= " il ".$cls_date->Get_DateNewFormat($a_results[$i]['Data_Nascita']);

    $a_recipientHeader['Cognome_Ditta'] = $nomeUtente;
    $a_recipientHeader['CF_PI'] = $CF_PI;
    $a_recipientHeader['CF_PI_VAR'] = is_numeric($a_results[$i]["CF_PI"]) == true ? "Partita IVA ".$a_results[$i]["CF_PI"] : "Codice Fiscale ".$a_results[$i]["CF_PI"];
    
    
    
    $via_sedeLegale_utente = ""; 
    
    if(isset($a_results[$i]["Dom_Comune"])){
        $via_sedeLegale_utente .= isset($a_results[$i]["Genere"])? $a_results[$i]["Genere"]== "D" ? "con sede legale a" : "con domicilio a" : "";
        if(isset($a_results[$i]["Dom_Comune"])) $via_sedeLegale_utente .= " ".$a_results[$i]["Dom_Comune"];
        if(isset($a_results[$i]["Dom_Via"])) $via_sedeLegale_utente .= " ".$a_results[$i]["Dom_Via"];
        if(isset($a_results[$i]["Dom_Civico"])) $via_sedeLegale_utente .= " N ".$a_results[$i]["Dom_Civico"];
    }
    else if(isset($a_results[$i]["Res_Comune"])){
        $via_sedeLegale_utente .= isset($a_results[$i]["Genere"])? $a_results[$i]["Genere"]== "D" ? "con sede legale a" : "con residenza a" : "";
        if(isset($a_results[$i]["Res_Comune"])) $via_sedeLegale_utente .= " ".$a_results[$i]["Res_Comune"];
        if(isset($a_results[$i]["Res_Via"])) $via_sedeLegale_utente .= " ".$a_results[$i]["Res_Via"];
        if(isset($a_results[$i]["Res_Civico"])) $via_sedeLegale_utente .= " N ".$a_results[$i]["Res_Civico"];
    }
    else if(isset($a_results[$i]["Rec_Comune"])){
        $via_sedeLegale_utente .= isset($a_results[$i]["Genere"])? $a_results[$i]["Genere"]== "D" ? "con sede legale a" : "con recapito a" : "";
        if(isset($a_results[$i]["Rec_Comune"])) $via_sedeLegale_utente .= " ".$a_results[$i]["Rec_Comune"];
        if(isset($a_results[$i]["Rec_Via"])) $via_sedeLegale_utente .= " ".$a_results[$i]["Rec_Via"];
        if(isset($a_results[$i]["Rec_Civico"])) $via_sedeLegale_utente .= " N ".$a_results[$i]["Rec_Civico"];
    }

    $ComonAddressData = "";
    if(isset($cls_ente->a_ente["Info_Via"])) $ComonAddressData .= $cls_ente->a_ente["Info_Via"];
    if(isset($cls_ente->a_ente["Info_Civico"])) $ComonAddressData .= ", ".$cls_ente->a_ente["Info_Civico"];
    if(isset($cls_ente->a_ente["Info_Cap"])) $ComonAddressData .= " - ".$cls_ente->a_ente["Info_Cap"];
    if(isset($cls_ente->a_ente["Info_Comune"])) $ComonAddressData .= " - ".$cls_ente->a_ente["Info_Comune"];
    if(isset($cls_ente->a_ente["Info_Provincia"])) $ComonAddressData .= " (".$cls_ente->a_ente["Info_Provincia"].")";

    $a_recipientHeader['Indirizzo_Comune'] = $ComonAddressData;
    $a_recipientHeader['Via_SedeLegale'] = $via_sedeLegale_utente;
    $a_recipientHeader['Dati_Nascita_Utente'] = $datiNascitaUtente;
    //$a_recipientHeader['Comune_Nascita_Utente'] = $prov_comune_nascita;

    if(!empty($cls_ente->a_ente['Ufficio_ID']))
        $type = "Ufficio";
    else if(!empty($cls_ente->a_ente['Gestore_ID']))
        $type = "Gestore";
    else
        $type = "Info";

    $DataLAB = "";
    if(isset($cls_ente->a_ente[$type."_PI"])) $DataLAB .= "P.IVA: ".$cls_ente->a_ente[$type."_PI"];
    if(isset($cls_ente->a_ente[$type."_Via"])) $DataLAB .= " con sede legale in ".$cls_ente->a_ente[$type."_Via"];
    if(isset($cls_ente->a_ente[$type."_Civico"])) $DataLAB .= ", ".$cls_ente->a_ente[$type."_Civico"];
    if(isset($cls_ente->a_ente[$type."_Cap"])) $DataLAB .= " - ".$cls_ente->a_ente[$type."_Cap"];
    if(isset($cls_ente->a_ente[$type."_Comune"])) $DataLAB .= " - ".$cls_ente->a_ente[$type."_Comune"];
    if(isset($cls_ente->a_ente[$type."_Provincia"])) $DataLAB .= " (".$cls_ente->a_ente[$type."_Provincia"].")";
    if(isset($cls_ente->a_ente[$type."_Via_SO"])) $DataLAB .= " e Sede Operativa in ".$cls_ente->a_ente[$type."_Via_SO"];
    if(isset($cls_ente->a_ente[$type."_Civico_SO"])) $DataLAB .= ", ".$cls_ente->a_ente[$type."_Civico_SO"];
    if(isset($cls_ente->a_ente[$type."_Cap_SO"])) $DataLAB .= " - ".$cls_ente->a_ente[$type."_Cap_SO"];
    if(isset($cls_ente->a_ente[$type."_Comune_SO"])) $DataLAB .= " - ".$cls_ente->a_ente[$type."_Comune_SO"];
    if(isset($cls_ente->a_ente[$type."_Provincia_SO"])) $DataLAB .= " (".$cls_ente->a_ente[$type."_Provincia_SO"].")";
    if(isset($cls_ente->a_ente[$type."_Telefono"])) $DataLAB .= " - Tel. :".$cls_ente->a_ente[$type."_Telefono"];
    if(isset($cls_ente->a_ente[$type."_Mail"])) $DataLAB .= " - E-mail: ".$cls_ente->a_ente[$type."_Mail"];

    $a_recipientHeader['data_lab'] = $DataLAB;
    $a_recipientHeader['manager_com'] = isset($cls_ente->a_ente[$type."_Comune"]) ? $cls_ente->a_ente[$type."_Comune"] : "";

    //$a_recipientHeader['email_com'] = $cls_ente->a_ente["Info_Mail"];

    $a_recipientHeader['email_lab'] = $cls_ente->a_ente[$type."_Mail"];
    $a_recipientHeader['pec_lab'] = $cls_ente->a_ente[$type."_PEC"];
    $a_recipientHeader['telefono_lab'] = $cls_ente->a_ente[$type."_Telefono"];
    $a_recipientHeader['email_com'] = $cls_ente->a_ente["Info_Mail"];
    $a_recipientHeader['pec_com'] = $cls_ente->a_ente["Info_PEC"];
    $a_recipientHeader['telefono_com'] = $cls_ente->a_ente["Info_Telefono"];
    
    $a_recipientHeader['email_uff_trib'] = $a_officeParams["Mail"];//parametri nuovi
    $a_recipientHeader['pec_uff_trib'] = $a_officeParams["PEC"];//parametri nuovi
    $a_recipientHeader['telefono_uff_trib'] = $a_officeParams["Telefono"];//parametri nuovi

    $a_recipientHeader['denom_ufficio_tributi'] = $a_officeParams["Denominazione"];
    $via_ufficio_comune = "";
    if($a_officeParams["Via"] != null) $via_ufficio_comune .= $a_officeParams["Via"];
    if($a_officeParams["Civico"] != null) $via_ufficio_comune .= ", ".$a_officeParams["Civico"];
    if($a_officeParams["Cap"] != null) $via_ufficio_comune .= " - ".$a_officeParams["Cap"];
    if($a_officeParams["Comune"] != null) $via_ufficio_comune .= " - ".$a_officeParams["Comune"];
    if($a_officeParams["Provincia"] != null) $via_ufficio_comune .= " (".$a_officeParams["Provincia"].")";
    
    $a_recipientHeader['via_ufficio_tributi'] = $via_ufficio_comune;

    $a_recipientHeader['corte_trib']  = !empty($a_corteTrib["Comune"])?$a_corteTrib["Comune"]:"";
    $a_recipientHeader['telefono_lab'] = $cls_ente->a_ente[$type."_Telefono"];


    $cls_text->setRowVars($cls_ruolo, $a_recipientHeader);
    $cls_text->filterSubtexts($a_subtext, $a_subtextParams);
    $cls_text->replaceSubtexts();
    $cls_text->replaceVariables($cls_text->a_var);
  
    if ($filter['printType'] == "final")
        $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
    $pdf->setDocParamsLAB();
    //$pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    if ($filter['printType'] == "temp")
        $pdf->temporaryPrinting();
    $pdf->setManagerHeaderLAB($cls_ente->a_header,null,$filter['printType']);
    $pdf->setRecipientHeaderLAB($a_recipientHeader);
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(0);
    $pdf->writeHTML($cls_text->html_replaced_body);
    if($cls_ruolo->a_docDetails['DocumentTypeId'] == 4){
        $pdf->AddPage("P");
        if ($filter['printType'] == "temp")
            $pdf->temporaryPrinting();
    }
    
    $cls_postal->setPostalParams($a_recipientHeader, $cls_ruolo->getReferences(), $cls_ruolo->getPostalClient($a_enteAdmin['ID']));
    $a_postal = array();
    for($k=1;$k<=2;$k++){

        /** CORREZZIONE FATTA DA ME ($k-1 COME INDICE), L'INDICE INIZIALMENTE ERA A 0 NEL CONTROLLO DELL'IF **/

        if(!empty($cls_ruolo->a_amounts['total'][$k-1]['amount']))
            $postalAmount = str_replace(",",".",str_replace(".","",$cls_ruolo->a_amounts['total'][$k-1]['amount']));
        else
            $postalAmount = null;
        $a_postal[$k] = $cls_postal->getPostalArray($k, $cls_ente->logo, $postalAmount);
    }
    $pdf->setPostalBill($a_postal, 2, $filter['printType']);
   
    if ($filter['printType'] == "final") {

        $a_docPath = $cls_ruolo->getDocCompletePath($filter['city'], $a_results[$i], $filter['finalDate']);
        $finalFile = $a_docPath['root'];

        $pdf->Output($finalFile, 'F');
    }
//    else if ($filter['printType'] == "temp") {
//
//        $tempFile = $a_fileToSave['rootTempPath'] . "/" . $cls_ruolo->a_docDetails['tempFileName'] . "_" . $filter['city'] . "_" . $a_results[$i]['Anno_Cronologico'];
//        $tempFile .= "_" . $a_results[$i]['ID_Cronologico'] . "_" . $a_results[$i]['Data_Stampa'] . ".pdf";
//        $pdf->Output($tempFile, 'F');
//        $a_files[] = $tempFile;
//    }
}

if (count($a_results) == 0)
    echo "<script>noResultsBar();</script>";
else {
    $cls_file->removeFiles($a_fileToSave['rootTempPath'], 7);

    if($filter['printType'] == "temp"){
        $tempFile = $a_fileToSave['rootTempPath'] . "/" . $cls_ruolo->a_docDetails['tempFileName'] . "_" . $filter['city'] . "_" . date('Y-m-d').".pdf";
        $webTempFile = $a_fileToSave['webTempPath'] . "/" . $cls_ruolo->a_docDetails['tempFileName'] . "_" . $filter['city'] . "_" . date('Y-m-d').".pdf";
        $pdf->Output($tempFile, 'F');

        flush();ob_flush();flush();ob_flush();
        echo "<script>endBar('Elaborazione completata','".$webTempFile."');</script>";
        flush();ob_flush();flush();ob_flush();
    }
    else if ($filter['printType'] == "unitaria") {
        $root_path = 'rootTempPath';
        $web_path = 'webTempPath';

        function getmicrotime()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }
        //echo "<h1>Final Date".$filter['finalDate']."</h1></br>";
        $finalDate = $filter['finalDate'];
        if ($finalDate == "" || $finalDate == null)
            $finalDate = $cls_ruolo->a_result['Anno_Cronologico'] . "_" . $cls_ruolo->a_result['ID_Cronologico'];
        $mergeFile = $cls_ruolo->a_docDetails['tempFileName'] . "_" . $filter['city'] . "_" . $finalDate . "_" . date('i-m-s') . ".pdf";

        flush();ob_flush();flush();ob_flush();
        echo "<script>startMerge();</script>";
        flush();ob_flush();flush();ob_flush();
        sleep(1);

        $cls_merge = new cls_merge();
        $cls_merge->setFiles($a_files);

        $time_start = getmicrotime(); //sec iniziali
        $cls_merge->concatFiles(true);
        $time_end = getmicrotime(); //sec finali
        $time = $time_end - $time_start; //differenza in secondi

        $tempo_previsto_sec = $time * 20;
        if ($tempo_previsto_sec < 55)
            $tempo_previsto = "1 minuto";
        else
            $tempo_previsto = floor($tempo_previsto_sec / 60 + 1) . " minuti";

        set_time_limit($tempo_previsto_sec + 200);

        flush();ob_flush();flush();ob_flush();
        echo "<script>endMerge(\"Creazione file in corso... Il tempo previsto per le operazioni e' di circa " . $tempo_previsto . ".\",\"\");</script>";
        flush();ob_flush();flush();ob_flush();

        $cls_merge->Output($a_fileToSave[$root_path] . "/" . $mergeFile, "F");

        echo "<script>endMerge('Elaborazione completata',\"" . $a_fileToSave[$web_path] . "/" . $mergeFile . "\");</script>";
    }
    else if ($filter['printType'] == "final") {

        $a_dbParams_elab_list = array(
            'table' => 'elaboration_lists',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $filter['el_list_id']),
            ),
            'fields' => array(
                array('name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => 5),
                array('name' => 'PrintDate',   'type' => 'date', 'value' => date('Y-m-d')),
            )
        );
        $cls_db->DbSave($a_dbParams_elab_list);

        $contList = $cls_db->getNumberRow($cls_db->ExecuteQuery("SELECT Id FROM elaboration_lists WHERE Elaboration_Id=" . $filter['last_el_id'] . " AND Elaboration_Status_Id>=5"));

        $query_elab = " SELECT * FROM  elaborations WHERE Id =" . $filter['last_el_id'];
        $results_elab = $cls_db->ExecuteQuery($query_elab);
        $elabs = $cls_db->getArrayLine($results_elab);

        $a_dbParams_elab = array(
            'table' => 'elaborations',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $filter['last_el_id']),
            ),
            'fields' => array(
                array('name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                array('name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
            )
        );

        if ((int)$elabs['ListNumber'] == (int)$contList)
            $a_dbParams_elab['fields'][] = array('name' => 'Elaboration_Status_Id',        'type' => 'int', 'value' => 5);

        $cls_db->DbSave($a_dbParams_elab);

        $query = "UPDATE atto SET Data_Stampa = '" . $filter['finalDate'] . "', Stato_Stampa = 'Stampato'";
        $query .= "WHERE Elaboration_List_Id=" . $filter['el_list_id'];
        $cls_db->ExecuteQuery($query);


        $fullURL = SUPER_WEB_ROOT . '/Gitco2/controlli/lista_elaborazioni.php?c=' . $c . '&a=' . $a;

        $storico_query_1 = "SELECT * FROM elaborations WHERE Id = ".$a_elab_list['Elaboration_Id'];
        $elab = $cls_db->getArrayLine($cls_db->ExecuteQuery($storico_query_1));
        $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$elab['CC']."'") );
        $nome_ente = $ente['Denominazione'];

        $atto_ = "";

        switch ($elab['Document_Type_Id']){
            case 2:
                $atto_ = "Ingiunzioni";
                break;
            case 3:
                $atto_ = "Solleciti di pagamento";
                break;
            case 4:
                $atto_ = "Avvisi d'intimazione";
                break;
            case 11:
                $atto_ = "Solleciti pre ingiunzione";
                break;
            case 12:
                $atto_ = "Avvisi di messa in mora";
                break;
            default:
                break;
        }
        
        $storico->insRow('E', "Stampa definitiva elemento ".$cls_help->getVar('el_list_id')." elaborazione ".$elab['Description'].": ".$atto_." ".$nome_ente."[".$elab['CC']."]");

        flush();ob_flush();flush();ob_flush();
        echo "<script>endBar('Elaborazione completata');</script>";
        flush();ob_flush();flush();ob_flush();
    }
}


?>
<script>
    redirect();
</script>
<?php

include(INC . "/footer.php");

