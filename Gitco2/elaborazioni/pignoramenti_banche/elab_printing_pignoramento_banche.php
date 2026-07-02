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
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_storico.php";
include_once ELABORAZIONI . "/cls/cls_StampaPignoramenti.php";												

$storico = new storico('storicoElaborazioni','5');
$log = new LOG();



$cls_file = new cls_file();

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

$cls_st = new cls_Stampe();
$cls_ruolo = new cls_ruolo();
$cls_ruolo->getDocumentDetails($filter['doc_type_id'], $filter['PrintTypeId'], null, array("PrinterId" => $filter["PrinterId"]));


$redirectPage = ELAB_PIGNORAMENTI_BANCA_WEB."/mgmt_pignoramenti_banche.php?c=".$c."&a=".$a."&el=".$a_elab_list['Elaboration_Id'];

$cls_registry = new cls_registry();

//FUNZIONI FABRIZIO
$aggiorna_data_stampa_pignoramento_generale=function($id) use($filter,$cls_db)
{
    $query = "UPDATE pignoramento_generale SET Data_Stampa = '" . $filter['finalDate'] . "', Stato_Stampa = 'Stampato'";
    $query .= " WHERE ID=" . $id;
    $cls_db->ExecuteQuery($query);
};

$crea_file_name=function($a_results,$suffix="Copia") use($a_elab_list){

        $pignoId = $a_results["ID"];

        if( is_dir( PIGNORAMENTI."/".$pignoId ) == false )
        {
            mkdir(PIGNORAMENTI."/".$pignoId);
        }
        $prefix=$a_results["PrefixName"];
        $cc=$a_elab_list['CC'];
        $anno= $a_results["Anno_Cronologico"];
        $id=$a_results["ID_Cronologico"];
        $notifica_id=$a_results["Notifica_ID"];

        $path=$pignoId."/";
        $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_".$suffix.".pdf";
        if(($suffix=="Originale") || ($suffix=="Relata_originale"))$filename=$prefix."_".$cc."_".$anno."_".$id."_".$suffix.".pdf";
        $path_completo =  PIGNORAMENTI."/".$path.$filename;
        return $path_completo;
};

$crea_file_name_temp=function($suffix="Copia") use($a_elab_list){

    $pignoId = "temp";

    if( is_dir( PIGNORAMENTI."/".$pignoId ) == false )
    {
        mkdir(PIGNORAMENTI."/".$pignoId);
    }
    
    $path=$pignoId."/";
    $filename="TEMP_".$suffix.".pdf";
    $path_completo =  PIGNORAMENTI."/".$path.$filename;
    return $path_completo;
};

$prendi_path_temp_web=fn($Suffix="Copia")=>PIGNORAMENTI_WEB."/"."temp"."/TEMP_".$Suffix.".pdf";

$prendi_dati_terzi = function ($a_results,$i) use ($cls_db)
{
    $utente_id = ($a_results[$i]["Utente_ID"]);
    $query_pignoramento_terzo = " select 
    BAN.Denominazione,
    BAN.ID
    from pignoramento_generale as PG 
    join partita_tributi as PT on PG.Partita_ID = PT.ID
    join pignoramento_presso_terzi as PPT on PG.ID = PPT.Pignoramento_ID
    join banca as BAN on BAN.ID = PPT.Terzo_ID
    join notifica_atto as NA on NA.Atto_Notificato_ID = PG.ID
    where  PT.Utente_ID = $utente_id
    AND NA.Tipo_Notifica = 'banca'
    group by Denominazione
    ";

    return $cls_db->getResults($cls_db->SelectQuery($query_pignoramento_terzo));

};

$prendiTerzi = function($a_terzi) use ($cls_db)
{
    $ret = "";
    foreach($a_terzi as $item)
    {
        $ret.=$item["Denominazione"].", ";
    }
    $ret = substr($ret,0,strlen($ret)-2);
    return $ret;
};

//! INUTILE ABBIAMO GIA TIPO NOTIFICA CARICATA
// $isDebitore = function ($a_results,$i) use ($cls_db)
// {
//     $notifica_id = $a_results[$i]["Notifica_ID"];
//     $query = "select Tipo_Notifica from notifica_atto where ID = $notifica_id";
//     $result = $cls_db->getResults($cls_db->SelectQuery($query));
//     return $result[0]["Tipo_Notifica"]=="debitore" ? true:false;
// };

// $prendiTerzoHeader = function ($a_results_terzo,&$lista_terzi_fatti,&$Terzo) use ($cls_db,$cls_registry)
// {
//     $id = 0;
//     foreach($a_results_terzo as $terzo)
//     {
//         if(!in_array($terzo["ID"], $lista_terzi_fatti)) 
//         {
//             $id = $terzo["ID"];
//             $Terzo = $terzo['Denominazione'];
//             $lista_terzi_fatti[]=$id;
//             break;
//         }
        

//     }
//     $query = "select * from banca where ID = $id";
//     $a_banca = $cls_db->getArrayLine($cls_db->SelectQuery($query));
//     if(empty($a_banca))
//         $a_banca = array();
//     $return =  $cls_registry->printHeaderBanca($a_banca);

//     return $return;
// };


$getHeaderBanca = function ($id_banca) use ($cls_db,$cls_registry)
{
    $query = "select * from banca where ID = ".$id_banca;
    $a_banca = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    if(empty($a_banca))
        $a_banca = array();

    return $cls_registry->printHeaderBanca($a_banca);
};


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

$query_v_pignoramenti = "SELECT * FROM v_pignoramento_banche_stampa WHERE Elaboration_List_Id = ".$filter['el_list_id']." AND CC = '" . $filter['city'] . "' ".
"ORDER BY Notifica_ID ASC";

if($_SESSION['username']!="emanuela" && $_SESSION['username']!="robertop")
    if ($filter['printType'] == "temp")
        $query_v_pignoramenti.= " LIMIT 5";

$a_results = $cls_db->getResults($cls_db->SelectQuery($query_v_pignoramenti));

$a_subtextParams = array(
    "NotificationType"      =>  $filter['officialType'],
    "PrintTypeId"           =>  $filter['PrintTypeId']
);

$cls_params = new cls_parameters();

$stampa_pigno = new cls_StampaPignoramenti($a_elab_list["DocumentTypeId"], $cls_db, $cls_params);
$cls_text = $stampa_pigno->getText($filter, $a_subtextParams);
$cls_textRelata = $stampa_pigno->getNotificationText($a_subtextParams);
$officialTypeId = $stampa_pigno->a_officialType[$a_elab_list["NotificationType"]];
$cls_ente = $stampa_pigno->ente;

$cls_postal = new cls_postal($stampa_pigno->text->a_params['payment']);

$placeDate = $cls_ente->getCityManager() . ", " . $cls_help->toItalianDate(date('Y-m-d'));

$a_ID = array();
$a_files = array();
$testo_relata_originale = "";
if ($filter['printType'] == "temp")
    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

// $lista_terzi_fatti = array();
$ultima_partita = 0;
$originale = 0;
$sono_terzo = true; // l'originale viene generato dal debitore se nella lista c'è solo il terzo allora no ( vuol dire verrà prodotta nella lista corrispondente)
$stampa_bill = false;
try{
    for ($i = 0; $i < count($a_results); $i++) {

        flush();ob_flush();flush();ob_flush();
        echo "<script>updateBar(" . ceil($i * 100 / count($a_results)) . ");</script>";
        flush();ob_flush();flush();ob_flush();

        if($a_results[$i]["Partita_ID"]!=$ultima_partita)
        {
            $sono_terzo = true;
            $ultima_partita = $a_results[$i]["Partita_ID"];
            // $lista_terzi_fatti = array();
            $originale = 0;
            
        }


        $suffisso_file_pdf = "";
        $suffisso_file_relata_pdf = "";
        // $a_results_terzo = $prendi_dati_terzi($a_results,$i);
        $a_terzi = $stampa_pigno->getBanche($a_results[$i]["ID"]);
        if ($a_results[$i]['Tipo_Notifica']=="debitore")//$isDebitore($a_results,$i))
        {
            $sono_terzo = false;
            $a_results[$i]['Terzi'] = $prendiTerzi($a_terzi);
            $a_recipientHeader = $cls_registry->printHeader($a_results[$i]);
            
            if($originale==0)
            {
                $a_results[$i]['PrintType'] = "ORIGINALE";
                $suffisso_file_pdf = "Originale";

                //? RELATA ORIGINALE
                $testo_relata_originale = $stampa_pigno->getOriginalNotificationText(
                    array(  
                            'Recipient'=>$a_results[$i]['User'],
                            'RecipientAddress'=>$a_results[$i]['Indirizzo_Debitore'],
                            'RecipientPEC'=>$a_results[$i]['Utente_PEC']
                        ), 
                    $a_terzi
                );

                if ($filter['printType']=="final")
                    $stampa_pigno->createPdf($testo_relata_originale, $crea_file_name($a_results[$i],"Relata_originale"));

            }
            else{
                $stampa_bill = true;
                $a_results[$i]['PrintType'] = "COPIA DEBITORE";
                $suffisso_file_pdf = "Copia_debitore";
                $suffisso_file_relata_pdf = "Relata_debitore";
            }
        }
        else
        {   
            $stampa_bill = false;
            if($sono_terzo) $originale++;
            // $a_recipientHeader = $prendiTerzoHeader($a_results_terzo,$lista_terzi_fatti,$Terzo);
            $a_recipientHeader = $getHeaderBanca($a_results[$i]['Banca_ID']);
            $a_results[$i]['Terzi'] = $a_results[$i]['Banca_Denominazione'];
            $a_results[$i]['PrintType'] = "COPIA TERZO";
            $suffisso_file_pdf = "Copia_banca";
            $suffisso_file_relata_pdf = "Relata_banca";
        }

        //$a_recipientHeader = $cls_registry->printHeader($a_results[$i]);

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
        $a_recipientHeader['PrintType'] = $a_results[$i]['PrintType'];
        $a_recipientHeader['Tipo'] = "banca";

        //GET IMPORTI STAMPA
        $cls_ruolo->setResultArray($a_results[$i]);
        $cls_ruolo->setDocAmounts($cls_ruolo->a_docDetails['DocumentTypeId'], $cls_text->a_params['year'], "pignoramento");
        
        $cls_text->setRowVarsPignoramento($cls_ruolo,$a_recipientHeader);
        // $cls_text->filterSubtexts($stampa_pigno->a_subTextPar, $a_subtextParams);
        // $cls_text->replaceSubtexts();
        $cls_text->replaceVariables($cls_text->a_var);

        $cls_textRelata->filterSubtexts($stampa_pigno->a_subTextPar, $a_subtextParams);
        $cls_textRelata->replaceSubtexts();
        $cls_textRelata->replaceVariables($cls_text->a_var);
        
        if ($filter['printType'] == "final")
            $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
        $pdf->setDocParams();
        $pdf->SetAutoPageBreak(true);
        $pdf->AddPage("P");
        if ($filter['printType'] == "temp")
            $pdf->temporaryPrinting();
        $pdf->setManagerHeader($cls_ente->a_header);
        if ($originale>0) 
            $pdf->setRecipientHeader($a_recipientHeader);
        else
            $pdf->setRecipientHeaderOriginale($a_recipientHeader);
        $pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->ln(0);
        $pdf->writeHTML($cls_text->html_replaced_body);

        if ($originale>0)
        {
            $cls_postal->setPostalParams($a_recipientHeader, $cls_ruolo->getReferences("pigno"), $cls_ruolo->getPostalClient($a_enteAdmin['ID'],1,"pigno"));
            $a_postal = array();
            for($k=1;$k<=2;$k++){
                if(!empty($cls_ruolo->a_amounts['total'][$k][0]['amount']))
                    $postalAmount = str_replace(",",".",str_replace(".","",$cls_ruolo->a_amounts['total'][$k][0]['amount']));
                else
                    $postalAmount = null;
                $a_postal[$k] = $cls_postal->getPostalArray($k, $cls_ente->logo, $postalAmount);
            }
            if($stampa_bill) 
            {
                $pdf->setPostalBill($a_postal, 2, $filter['printType']);
                
            }
        }
        if ($filter['printType'] == "final") {
        
            $finalFile = $crea_file_name($a_results[$i],$suffisso_file_pdf);
            $pdf->Output($finalFile, 'F');
            $aggiorna_data_stampa_pignoramento_generale($a_results[$i]["ID"]);

        }

        //CREARE PDF RELATA

        // if($officialTypeId==1) continue; //se è diretta non fare relata
        // if($a_elab_list['PrintTypeId']==1) continue; // se lo stampatore è 1 non fare la relata

        if(($officialTypeId==1) || ($a_elab_list['PrintTypeId']==1))
        {
            // if (!$sono_terzo)
            //     if($originale==0) 
            //     {
            //         $i--;
            //         $originale++;
            //     }
            // else
            //     continue;

            if ($sono_terzo)
                continue;
        }

        if (($filter['printType'] == "final"))
        {
            $pdfRelata = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
            $pdfRelata->setDocParams();
            $pdfRelata->SetAutoPageBreak(true);
            $pdfRelata->AddPage("P");
            $pdfRelata->SetMargins(7.0, 10.0, 7.0);
            $pdfRelata->ln(0);
            $pdfRelata->writeHTML($cls_textRelata->html_replaced_body);
            $finalFileRelata = $crea_file_name($a_results[$i],$suffisso_file_relata_pdf);
            $pdfRelata->Output($finalFileRelata, 'F');

        }
        else{
            
            $pdf->AddPage("P");
            $pdf->SetFont('Arial', '', 7.8);
            $pdf->SetMargins(7.0, 10.0, 7.0);
            $pdf->SetAutoPageBreak(true,10);
            $pdf->ln(5);
            if ($testo_relata_originale!= ""){
                $pdf->writeHTML($testo_relata_originale);
                $testo_relata_originale = "";
            } 
            else
                $pdf->writeHTML($cls_textRelata->html_replaced_body);

        }
        if(!$sono_terzo)
            if($originale==0)
            {
                $i--;
                $originale++;
            }

    } //end for
    
}
catch(Exception $e)
{
    var_dump($e->getTraceAsString());
    die;
    throw $e;
}

if (count($a_results) == 0)
    echo "<script>noResultsBar();</script>";
else {
    

    if($filter['printType'] == "temp"){
        $tempFile = $crea_file_name_temp();
        $log->Info("File:".$tempFile);
        $webTempFile = $prendi_path_temp_web();
        $pdf->Output($tempFile, 'F');

        flush();ob_flush();flush();ob_flush();
        echo "<script>endBar('Elaborazione completata','".$webTempFile."');</script>";
        flush();ob_flush();flush();ob_flush();
    }
    else if ($filter['printType'] == "final") {

        $a_dbParams_elab_list = array(
            'table' => 'elaboration_lists',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $filter['el_list_id']),
            ),
            'fields' => array(
                array('name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => ElaborationStatus::PDF_CREATI),
                array('name' => 'PrintDate',   'type' => 'date', 'value' => date('Y-m-d')),
            )
        );
        $cls_db->DbSave($a_dbParams_elab_list);
            
        $query = "UPDATE notifica_atto SET Data_Stampa='".date('Y-m-d')."' WHERE Elaboration_List_Id=".$filter['el_list_id'];
        $cls_db->ExecuteQuery($query);

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
        {
            $a_dbParams_elab['fields'][] = array('name' => 'Elaboration_Status_Id',        'type' => 'int', 'value' => ElaborationStatus::PDF_CREATI);
            
        }
        $cls_db->DbSave($a_dbParams_elab);

        $fullURL = SUPER_WEB_ROOT . '/Gitco2/controlli/lista_elaborazioni.php?c=' . $c . '&a=' . $a;

        $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$elabs['CC']."'") );

        $storico->insRow('E', "Stampa definitiva elemento ".$cls_help->getVar('el_list_id')." elaborazione ".$elabs['Description'].": Pignoramenti presso banca ".$ente['Denominazione']."[".$elabs['CC']."]");

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

