<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

set_time_limit(0);
ini_set('memory_limit', '-1');

//include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_merge.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_file.php";

$db = new cls_db();
$help = new cls_help();
$cls_file = new cls_file();
$date = new cls_DateTimeI("IT",false);
$utils = new cls_Utils();
$cls_registry = new cls_registry();
$cls_params = new cls_parameters();
$cls_st = new cls_Stampe();

$c = $help->getVar("c");
$a = $help->getVar("a");

$_SESSION['progress'] = "0.00";
session_write_close();

$partita_da = $help->getVar("partita_da");
$partita_a = $help->getVar("partita_a");
$dacognome = $help->getVar('daco');
$acognome = $help->getVar('acog');
$danome = $help->getVar('dano');
$anome = $help->getVar('anom');
$genere_da = $help->getVar('genere_da');
$genere_a = $help->getVar('genere_a');
$ruolo_da = $help->getVar('ruolo_da');
$ruolo_a = $help->getVar('ruolo_a');
$anno_rif_da = $help->getVar("anno_rif_da");
$anno_rif_a = $help->getVar("anno_rif_a");
$data_stampa = $help->getVar("data_stampa");
$printType = $help->getVar("printType");
if(empty($data_stampa) && $printType=="final"){
    $error = 1;
    $msg = "Data di stampa assente!";
    //echo "<script>location.href = 'elenco_sgravi_annull.php?c=".$c."&a=".$a."&partita_id=".$help->getVar("partita_id")."&page_called=".$help->getVar("page_called")."&visualizzaBtnRet=si&msg=".$msg."&error=".$error."';</script>";
    //die;
    echo json_encode([
        "error" => 1,
        "msg" => "Data di stampa assente!"
    ]);
    die;
}
else{
    if(empty($data_stampa))
        $data_stampa = date('Y-m-d');
    else
        $data_stampa = $help->toDbDate($data_stampa);
}

$error = 0;
$msg = "File creati";

$query = "SELECT PT.*, SG.ID AS Sgravio_ID
            FROM sgravio SG 
            JOIN v_check_partite AS PT ON PT.Partita_ID=SG.Partita_ID
            WHERE PT.CC = '".$c."' ";


$formTypeId = 15;
$query .= " AND SG.Tipo = 1 ";
$filtriDescrizione = "Stampa discarichi: ";

if($dacognome != null){
    $strCompareDa = addslashes($dacognome)." ".addslashes($danome);
    $strCompareA = addslashes($acognome)." ".addslashes($anome);

    $query .= " AND ( CONCAT(COALESCE(PT.Ditta,''),COALESCE(PT.Cognome,''),' ',COALESCE(PT.Nome,'')) >= '".$strCompareDa."' AND CONCAT(COALESCE(PT.Ditta,''),COALESCE(PT.Cognome,''),' ',COALESCE(PT.Nome,'')) <= '".$strCompareA."' ) ";
}

if($partita_da != ""){
    $query .= " AND PT.Comune_ID >= ".$partita_da." ";
    $filtriDescrizione .= " - Da partita ".$partita_da;
}
if($partita_a != ""){
    $query .= " AND PT.Comune_ID <= ".$partita_a." ";
    $filtriDescrizione .= " a partita ".$partita_a;
}

if($ruolo_da != ""){
    $query .= " AND PT.Data_Fornitura >= '".$ruolo_da."-01-01' ";
    $filtriDescrizione .= " - Da anno fornitura ".$ruolo_da;
}
if($ruolo_a != ""){
    $query .= " AND PT.Data_Fornitura <= '".$ruolo_a."-12-31' ";
    $filtriDescrizione .= " ad anno fornitura ".$ruolo_a;
}


if($anno_rif_da != ""){
    $query .= " AND PT.Anno_Riferimento >= '".$anno_rif_da."' ";
    $filtriDescrizione .= " - Da anno riferimento ".$anno_rif_da;
}
if($anno_rif_a != ""){
    $query .= " AND PT.Anno_Riferimento <= '".$anno_rif_a."' ";
    $filtriDescrizione .= " ad anno riferimento ".$anno_rif_a;
}

if($help->getVar("printed")!="all") {
    if($help->getVar("printed") == "no")
        $query .= " AND SG.Data_Stampa is null ";
    else
        $query .= " AND SG.Data_Stampa is not null ";
}


$result = $db->getResults($db->ExecuteQuery($query));

$cls_text = new cls_textParameters();
$a_text = $db->getArrayLine($db->SelectQuery($cls_text->getParametersQuery($c,$formTypeId)));
if(empty($a_text)){
    //$help->alert("ATTENZIONE!!! Modello di testo assente (FormTypeId ".$formTypeId.")!");
    //echo "<script>window.close();</script>";
    echo json_encode([
        "error" => 1,
        "msg" => "ATTENZIONE! Modello di testo assente!"
    ]);
    die;
}
$cls_text->html_body = $a_text['Content'];

$a_enteAdmin = $db->getArrayLine( $db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
$managerCity = $cls_ente->getCityManager("Gestore");

$data = array();
$data[] = array("<b>CC</b>","<b>UTENTE</b>","<b>Utente_ID</b>","<b>Comune_ID</b>","<b>Partita_ID</b>","<b>TIPO</b>","<b>TESTO</b>");
$allPDFFile = array();
$dataExcel = array();

$tempPath = $utils->crea_dir(SGRAVI ."TEMP");
$procTempPath = $utils->crea_dir(PROCEDURE ."TEMP");
$cls_file->removeFiles($tempPath,7);
$cls_file->removeFiles($procTempPath,7);

$db->Start_Transaction();
$db->Begin_Transaction();

$count = count($result);
if($count == 0){
	
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();
    
	
    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
	
    die;
}

for($i=0; $i < $count; $i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    $a_responsibleParams = $db->getArrayLine($db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $result[$i]['Tipo_Riscossione'])));
    if(!is_array($a_responsibleParams)){

        echo json_encode([
			"error" => 1,
			"msg" => "ATTENZIONE! Parametri dei responsabili assenti per ".$result[$i]['Tipo']."!"
		]);
        die;
    }

    if(!empty($result[$i]['Motivo_Blocco'])){
        $query = "SELECT * FROM parametri_notifica WHERE ID = '".$result[$i]['Motivo_Blocco']."'";
        $parametri_notifica = $db->getObjectLineNull($db->ExecuteQuery($query),"parametri_notifica");
    } else {
        $parametri_notifica = new stdClass();
    }

    $cls_text->html_replaced_body = $cls_text->html_body;
    $cls_params->setArray("responsabili",$a_responsibleParams);
    $cls_params->getSignatures($cls_ente->type);

    $placeDate = $managerCity.", ".$help->toItalianDate($data_stampa);
    $a_recipientHeader_annull = $cls_registry->printHeader((array) $result[$i], $placeDate);

    $a_references = array(
        "PARTITA NUMERO:  ".$result[$i]['Comune_ID']." / ".$result[$i]['Anno_Riferimento'],
        "CODICE UTENTE:  ".$result[$i]['Utente_Comune_ID']." / ".$result[$i]['CC']
    );

    $a_recipientHeader = $cls_ente->setRecipientHeader("Info", $placeDate, $a_references);

    if(!empty($cls_ente->a_ente['Gestore_Denominazione']))
        $manager = "Concessionario ".$cls_ente->a_ente['Gestore_Denominazione'];
    else
        $manager = $cls_ente->a_ente['Info_Denominazione'];


    $motivation = "";
    $note = "";
    $noteSgravio = "";

    if(!empty($parametri_notifica->Descrizione))
        $motivation = "<strong style='width:50rem'>Motivazioni blocco posizione:</strong> ".$parametri_notifica->Descrizione."<br>";
    if(!empty($result[$i]['Note_Blocco']))
        $note = "<strong style='width:50rem'>Dettaglio blocco posizione:</strong> ".$result[$i]['Note_Blocco']."<br>";
    if(!empty($result[$i]['Note_Sgravio']))
        $noteSgravio = "<strong style='width:50rem'>Note discarico:</strong> ".$result[$i]["Note_Sgravio"];

    //RECUPERO MOTIVAZIONI COMPLETE DA SGRAVI_DOCUMENTI
    $query = "SELECT SD.*, DT.Description AS DocumentType 
            FROM sgravi_documenti AS SD 
            JOIN document_type AS DT ON DT.Id=SD.DocumentTypeId 
            WHERE SD.Partita_ID = ".$result[$i]["Partita_ID"];
    $resultDoc = $db->getResults($db->ExecuteQuery($query));

    //CONTROLLO PRESENZA DATI
    if(count($resultDoc) == 0){
        $help->alert("Discarico non salvato");
        continue;
    }

    $tipo_sgravio = null;
    switch($result[$i]["Tipo_Sgravio"]){
        case "I": $tipo_sgravio = "INFORMATIVO"; break;
        case "D": $tipo_sgravio = "DEFINITIVO"; break;
        case "P": $tipo_sgravio = "PAGATO"; break;
    }
    $cls_text->a_var = array(
        "{Tipo_Sgravio}" => $tipo_sgravio,
        "{Partita_ID}" => $result[$i]["Comune_ID"],
        "{AnnoRif}" => $result[$i]["Anno_Riferimento"],
        "{TaxType}" => $result[$i]["Tipo_Riscossione"],
        "{CollectionType}" => "Servizio riscossione coattiva",
        "{Ente}" => $cls_ente->getCityDenomination(),
        "{Manager}" => $manager,
        "{User}" => $result[$i]['Cognome_Ditta']." ".$result[$i]['Nome'],
        "{MotivoBlocco}" => $motivation,
        "{DettaglioBlocco}" => $note,
        "{NoteSgravio}" => $noteSgravio,
        "{SignUfficiale}" => $cls_params->getHtmlSignature("{SignUfficiale}")
    );

    $cls_text->replaceVariables($cls_text->a_var);

    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);



    $pdf->setDocParams();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    if ($help->getVar("printType") == "temp")
        $pdf->temporaryPrinting();
    $pdf->setManagerHeader($cls_ente->a_header);

    $pdf->setRecipientHeader($a_recipientHeader);
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(0);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);

    $dataPdf = array();
    $dataExcel[] = array("<b>CC</b>","<b>UTENTE</b>","<b>Utente_ID</b>","<b>Comune_ID</b>","<b>Partita_ID</b>","<b>TIPO</b>","<b>TESTO</b>");

    $countResDoc = count($resultDoc);
    for($x=0; $x < $countResDoc; $x++)
    {
        switch($resultDoc[$x]["DocumentTypeId"]){
            case 0:     $tempKey = "Firma (L'ufficiale della riscossione)";
                break;
            case 4:     $tempKey = "AVVISO INTIMAZIONE";
                break;
            case 8:     $tempKey = "PIGNORAMENTO PRESSO BANCA/POSTA";
                break;
            default:
                $tempKey = strtoupper($resultDoc[$x]["DocumentType"]);
                //throw new Exception("DocumentTypeId = ".$atti[$i]["DocumentTypeId"]." non presente nello switch della classe BuildMotivationText");
                break;
        }

        if(empty($result[$i]["Cognome_Ditta"]))
            $utente = $result[$i]["Cognome_Ditta"]." ".$result[$i]["Nome"];
        else
            $utente = $result[$i]["Cognome_Ditta"];

        $Partita_ID = isset($resultDoc[$x]["Partita_ID"])?$resultDoc[$x]["Partita_ID"]:$result[$i]["Partita_ID"];

        $data[] = array($c,$utente,$result[$i]["Utente_Comune_ID"],$result[$i]["Comune_ID"],$Partita_ID,$tempKey,$resultDoc[$x]["Text"]);
        $dataPdf[] = array($tempKey,$resultDoc[$x]["Text"]);
        $dataExcel[] = array($c,$utente,$result[$i]["Utente_Comune_ID"],$result[$i]["Comune_ID"],$Partita_ID,$tempKey,$resultDoc[$x]["Text"]);
    }

    $pdf->AddPage("P");
    if($help->getVar('printType') == "temp")
        $pdf->temporaryPrinting();
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(10);

    $countDataPdf = count($dataPdf);
    for($j=0; $j < $countDataPdf ; $j++) {

        $maxH = 0;
        if ($pdf->getStringHeight(80, $dataPdf[$j][0]) > $pdf->getStringHeight(115, $dataPdf[$j][1])) $maxH = $pdf->getStringHeight(80, $dataPdf[$j][0]);
        else $maxH = $pdf->getStringHeight(115, $dataPdf[$j][1]);

        $pdf->SetFont("helvetica","B",8);
        $pdf->MultiCell(80, $maxH, $dataPdf[$j][0], 0, 'L', 0, 0, '', '', true);
        $pdf->SetFont("helvetica","",7);
        $pdf->MultiCell(115, $maxH, $dataPdf[$j][1], 0, 'L', 0, 1, '', '', true);
    }

    $pdf->SetFont("helvetica","B",10);
    $pdf->MultiCell(0, 50, "L'ufficiale della riscossione", 0, 'L', 0, 0, 150, $pdf->GetY()+15, true);
    $pdf->Image($cls_params->a_signature["ufficiale"]['filePath'],159,$pdf->GetY()+5,27,16);
    $pdf->SetFont("helvetica","",10);
    $pdf->MultiCell(45, 50, strtoupper($cls_params->a_signature["ufficiale"]["name"]), 0, 'C', 0, 0, 150, $pdf->GetY()+23, true);

    if($help->getVar('printType') == "final")
        $path = $utils->crea_dir(SGRAVI .$result[$i]["Sgravio_ID"]);
    else
        $path = $tempPath;
    $nameFile = "Sgravio_" . $result[$i]["Sgravio_ID"];
    $file['pdf'] = $nameFile. ".pdf";
    $pdfPath = $path ."/". $nameFile. ".pdf";

    $pdf->Output($pdfPath,"F");
    if($help->getVar('printType') == "final") {

        $excelPath = $path ."/". $nameFile. ".xlsx";
        $file['excel'] = $nameFile. ".xlsx";
        if(count($dataExcel) > 1)
            SimpleXLSXGen::fromArray( $dataExcel )
                ->setDefaultFont( 'Courier New' )
                ->setDefaultFontSize( 14 )
                ->saveAs($excelPath);

        $query = "UPDATE partita_tributi SET Printed_Sgravio = 'si', print_sgravio_date = '" . $data_stampa . "' WHERE ID = " . $result[$i]["Partita_ID"];
        if (!$db->ExecuteQuery($query)) {
            $error = 1;
            $msg = "Errore nel salvataggio dei dati.";
            $db->Rollback();
        }
    }

    $allPDFFile[] = $pdfPath;
    $dataExcel = array();


    $tipo = 1;
    if($help->getVar('printType') == "final") {

        $save = array();
        $save["Data_Stampa"] = date("Y-m-d");
        $save["Partita_ID"] = $result[$i]['Partita_ID'];
        $save["Tipo"] = $tipo;
        $save["File_1"] = $file['pdf'];
        $save["File_2"] = $file['excel'];


        $obj = $utils->GetObjectQuery($save,"sgravio", array("ID" => $result[$i]["Sgravio_ID"]));

        $db->Start_Transaction();
        $db->Begin_Transaction();

        $error = 0;
        if(!$db->DbSave($obj)){
            $db->Rollback();
            $error = 1;
            $msg = "Errore impossibile salvare dati a db";
        }else $msg = "Dati salvati correttamente";

        $db->End_Transaction();
    }

}

if(count($allPDFFile) > 0) {

    $cls_merge = new cls_merge();
    $cls_merge->setFiles($allPDFFile);
    $cls_merge->concatFiles(false);

    if($help->getVar('printType') == "final") {
        $procedureType = 3;
        $a_dbParams = array(
            'table' => 'procedures',
            'fields' => array(
                array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => $procedureType),
                array('name' => 'Datetime', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('name' => 'Procedure_Date', 'type' => 'date', 'value' => $data_stampa),
                array('name' => 'CC', 'type' => 'string', 'value' => $c),
                array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr']),
                array('name' => 'Description', 'type' => 'string', 'value' => $filtriDescrizione),
            )
        );
        $procedure_id = $db->DbSave($a_dbParams);
        $path = $utils->crea_dir(PROCEDURE . $procedure_id);
        $webPath = PROCEDURE_WEB.$procedure_id;
        $pdfNameFile = "Stampa_Sgravi_".$procedure_id."_" . $c . "_" . date("d-m-Y_H-i-s") . ".pdf";
        $excelNameFile = "Stampa_Sgravi_".$procedure_id."_" . $c . "_" . date("d-m-Y_H-i-s") . ".xlsx";
        $excelPath = $path . "/" . $excelNameFile;
        if(count($data)>1) {
            if (count($data) > 1) {
                SimpleXLSXGen::fromArray($data)
                    ->setDefaultFont('Courier New')
                    ->setDefaultFontSize(14)
                    ->saveAs($excelPath);
            }
        }

    }
    else{
        $msg = "Stampa provvisoria eseguita!";
        $path = $procTempPath;
        $webPath = PROCEDURE_WEB."TEMP";
        $pdfNameFile = "Stampa_Sgravi_" . $c . "_" . date("d-m-Y_H-i-s") . ".pdf";

    }

    $pdfPath = $path ."/". $pdfNameFile;
    $cls_merge->Output($pdfPath, "F");
    $pdfWebPath = $webPath."/".$pdfNameFile;

    //echo "<script>window.open('" . $pdfWebPath . "','Merge File','height=700,width=500'); </script>";

}

$db->End_Transaction();

if($error == 0)
    echo json_encode([
        "path" => $pdfWebPath,
        "error" => 0,
        "msg" => "File stampato correttamente!"
    ]);
else
    echo json_encode([
        "error" => $error,
        "msg" => $msg
    ]);

//echo "<script>location.href = 'elenco_sgravi_annull.php?c=".$c."&a=".$a."&partita_id=".$help->getVar("partita_id")."&page_called=".$help->getVar("page_called")."&visualizzaBtnRet=si&msg=".$msg."&error=".$error."';</script>";