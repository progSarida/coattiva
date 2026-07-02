<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

//include_once INC . "/header.php";
//include_once INC . "/menu.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$anno_fornitura = $cls_help->getVar("anno_fornitura");
$printType = $cls_help->getVar('printType');
$partita_da = $cls_help->getVar("partita_da");
$partita_a = $cls_help->getVar("partita_a");
$anno_rif_da = $cls_help->getVar("anno_rif_da");
$anno_rif_a = $cls_help->getVar("anno_rif_a");
$data_stampa = $cls_help->getVar("data_stampa");
if(!empty($data_stampa))
    $data_stampa = $cls_help->toDbDate($data_stampa);
else
    $data_stampa = date("Y-m-d");
$anno_riferimento = $anno_fornitura;

$_SESSION['progress'] = "0.00";
session_write_close();

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );							
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	
$adminCityName = $a_enteAdmin['Denominazione'];

//controllo presenza modello
$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,39)));
if(empty($a_text)){
    //$msg = "ATTENZIONE!!! Modello di testo assente (FormTypeId 39)!";
    //echo "<script>location.href = 'rimborso_spese.php?c=".$c."&a=".$a."&msg=".$msg."&error=1';</script>";
    echo json_encode([
        "error" => 1,
        "msg" => "ATTENZIONE!!! Modello di testo assente!"
    ]);
    die;
}
//controllo presenza Intestatario, Conto_Corrente,IBAN per variabile {DatiPagamento}

$controllo_gestore = 0;
array_map(function($item)use ($a_enteAdmin,&$controllo_gestore){ $controllo_gestore+= is_null($a_enteAdmin["Gestore_$item"])? 0 : 1;},array("Intestatario","Conto_Corrente","IBAN","Scadenza_Giorno","Scadenza_Mese","File_Firma"));
if($controllo_gestore<6){
    //$msg = "ATTENZIONE!!! Mancano Intestatario e/o Conto Corrente e/o IBAN  e/o Giorno Scadenza e/o Mese Scadenza e/o Mese File firma nella pagina del GESTORE ".$a_enteAdmin['Gestore_Denominazione']."!";
    //echo "<script>location.href = 'rimborso_spese.php?c=".$c."&a=".$a."&msg=".$msg."&error=1';</script>";
    echo json_encode([
        "error" => 1,
        "msg" => "ATTENZIONE!!! Mancano Intestatario e/o Conto Corrente e/o IBAN  e/o Giorno Scadenza e/o Mese Scadenza e/o Mese File firma nella pagina del GESTORE ".$a_enteAdmin['Gestore_Denominazione']."!"
    ]);
    die;
}
//c
$cls_text->html_body = $a_text['Content'];
$query = "SELECT * FROM tariffe_coazione";
$a_tariffe = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");


$query = "SELECT PS.*, PT.Tipo AS Tipo_Riscossione, PT.Comune_ID AS Partita_Comune_ID
            FROM sgravio S
            JOIN partita_tributi PT ON PT.ID=S.Partita_ID    
            JOIN sgravi_documenti SD ON SD.Sgravio_ID=S.ID AND SD.DocumentId is not null
            JOIN document_type DT ON DT.Id=SD.DocumentTypeId AND DT.TableTypeId=2    
            JOIN pignoramento_generale AS PG ON PG.Partita_ID=S.Partita_ID
            JOIN pignoramento_spese as PS ON PG.ID=PS.Pignoramento_ID
            WHERE S.CC = '".$c."' AND PS.Is_Remitted=0 AND PG.Anno_Cronologico=".$anno_fornitura;

$procedureDescription = "Rimborso spese esecutive Art17 ".$a_enteAdmin['Info_Denominazione']." per l'anno ".$anno_fornitura;

if($partita_da != ""){
    $query .= " AND PT.Comune_ID >= ".$partita_da." ";
    $procedureDescription .= " - Da partita ".$partita_da;
}
if($partita_a != ""){
    $query .= " AND PT.Comune_ID <= ".$partita_a." ";
    $procedureDescription .= " a partita ".$partita_a;
}

if($anno_rif_da != ""){
    $query .= " AND PT.Anno_Riferimento >= '".$anno_rif_da."' ";
    $procedureDescription .= " - Da anno riferimento ".$anno_rif_da;
}
if($anno_rif_a != ""){
    $query .= " AND PT.Anno_Riferimento <= '".$anno_rif_a."' ";
    $procedureDescription .= " ad anno riferimento ".$anno_rif_a;
}


//echo $query;die;
$a_pg = $cls_db->getResults($cls_db->ExecuteQuery($query));
$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
$managerCity = $cls_ente->getCityManager();

$utils = new cls_Utils();
$procTempPath = $utils->crea_dir(PROCEDURE ."TEMP");
$cls_file = new cls_file();
$cls_file->removeFiles($procTempPath,7);
$cls_params = new cls_parameters();





set_time_limit(500);
ini_set('memory_limit', '-1');
$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
$totCredito = 0;
$a_crediti = array();

$count = count($a_pg);

for($i=0; $i < $count; $i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    $a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $a_pg[$i]['Tipo_Riscossione'])));
    if(!is_array($a_responsibleParams)){
        //$cls_help->alert("ATTENZIONE!!! Parametri dei responsabili assenti per ".$a_pg[$i]['Tipo_Riscossione']."!");
        //echo "<script>window.close();</script>";
        echo json_encode([
			"error" => 1,
			"msg" => "ATTENZIONE! Parametri dei responsabili assenti per ".$result[$i]['Tipo']."!"
		]);
        die;
    }

    for($y=1;$y<=10;$y++){
        if(!empty($a_pg[$i]['Spesa_'.$y.'_ID'])){
            $index = $a_pg[$i]['Spesa_'.$y.'_ID'];
            $tariffa = $a_tariffe[$a_pg[$i]['Spesa_'.$y.'_ID']];

            if($anno_fornitura<=2022)
            {
                if($tariffa['Descrizione']=="Progetto di attribuzione del ricavato" ||
                $tariffa['Descrizione']=="Richiesta di copia autentica dell'atto di pignoramento notificato per la trascrizione nei pubblici registri" ||
                $tariffa['Descrizione']=="Iscrizione del fermo/pignoramento di beni mobili registrati nei pubblici registri" ||
                $tariffa['Descrizione']=="Revoca del fermo amministrativo/pignoramento di beni mobili registrati" ||
                $tariffa['Descrizione']=="Stima dei beni pignorati e formazione fascicolo" )
                    continue;
            }
            if(!isset($a_crediti[$index]))
                $a_crediti[$index] = array("Descrizione"=>$tariffa['Descrizione'], "Totale"=>0);

            $a_crediti[$index]['Totale']+= $a_pg[$i]['Rimborso_'.$y];
            $totCredito+= $a_pg[$i]['Rimborso_'.$y];
        }
    }

    if($printType=="final"){
        $query = "UPDATE pignoramento_spese SET Is_Remitted = 1 WHERE Pignoramento_ID = " . $a_pg[$i]["Pignoramento_ID"];
        $cls_db->ExecuteQuery($query);
    }
}

function FaiSignatureAgenteContabile() 
{
    global $a_enteAdmin,$c;
    $signature = array();
    $signature['type'] = "file";
    $signature['header'] = "L'Agente Contabile";
    $signature['file'] = $a_enteAdmin['Gestore_File_Firma'];
    $signature['filePath'] = FIRME."/".$c."/".$a_enteAdmin['Gestore_File_Firma'];
    $signature['fileWebPath'] = FIRMEWEB."/".$c."/".$a_enteAdmin['Gestore_File_Firma'];
    $signature['name'] = "";
    return $signature;
}

$empty = false;
$pdfWebPath = "";
if($count> 0) {
    $cls_text->html_replaced_body = $cls_text->html_body;
    $cls_params->setArray("responsabili",$a_responsibleParams);
    $cls_params->getSignatures($cls_ente->type);

    $placeDate = $managerCity.", ".date('d/m/Y');
    $MettiZero = fn($x) => $x<10 ? "0$x" : "$x";
    $anno = $anno_fornitura + 1;
    $cls_text->a_var = array(
        "{AnnoCredito}" => $anno_fornitura,
        "{Credito}" => number_format($totCredito,2,",","."),
        "{intestazione}" => $a_enteAdmin["Gestore_Intestatario"],
        "{conto corrente}"=>$a_enteAdmin["Gestore_Conto_Corrente"],
        "{iban}" => $a_enteAdmin["Gestore_IBAN"],
        "{pec}" => $a_enteAdmin["Gestore_PEC"],
        "{mail}" => $a_enteAdmin["Gestore_Mail"],
        "{telefono}" => $a_enteAdmin["Gestore_Telefono"],
        "{ScadenzaPagamento}" => $MettiZero($a_enteAdmin["Gestore_Scadenza_Giorno"])."/".$MettiZero($a_enteAdmin["Gestore_Scadenza_Mese"])."/".$anno,
        "{Causale}" => "'codice ente ".$c." – rimborso spese art. 17 – anno ".$anno_fornitura."'",
        "{SignRespProcedimento}" => $cls_params->getHtmlSignature("{SignRespProcedimento}"),
        "{SignRespRichieste}" => $cls_params->getHtmlSignature("{SignRespRichieste}"),
        "{Luogo}" => "Sestri Levante",
        "{Data}" =>  date("d/m/Y", strtotime($data_stampa)),
        "{SignAgenteContabile}" =>$cls_params->GetGenericSignature(FaiSignatureAgenteContabile())
    );
    $cls_text->replaceVariables($cls_text->a_var);

    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

    $pdf->setDocParams();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    if ($printType == "temp")
        $pdf->temporaryPrinting();
    $pdf->setManagerHeader($cls_ente->a_header,39);

    $pdf->setRecipientHeader($cls_ente->setRecipientHeader("Info"));
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(0);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);

    $pdf->setRimborsiEnte($a_crediti, $totCredito);

    if($printType == "final") {
        $q_exist = "select Id from procedures where CC = '$c' and Anno_Riferimento=$anno_riferimento and Procedure_Type_Id = 5";
       
        $exist_Id = $cls_db->getResults($cls_db->ExecuteQuery($q_exist));
        if (isset($exist_Id[0]["Id"]))
        {
    
            $procedure_id = $exist_Id[0]["Id"];
        }
        else
        {
            $a_dbParams = array(
                'table' => 'procedures',
                'fields' => array(
                    array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => 5),
                    array('name' => 'Datetime', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                    array('name' => 'Procedure_Date', 'type' => 'date', 'value' => $data_stampa),
                    array('name' => 'CC', 'type' => 'string', 'value' => $c),
                    array('name' => 'Anno_Riferimento', 'type' => 'int', 'value' => $anno_riferimento),
                    array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr']),
                    array('name' => 'Description', 'type' => 'string', 'value' => $procedureDescription),
                )
            );
            $procedure_id = $cls_db->DbSave($a_dbParams);
        }
        
        $path = $utils->crea_dir(PROCEDURE . $procedure_id);
        $webPath = PROCEDURE_WEB.$procedure_id;
        $pdfNameFile = "Rimborso_Spese_Art17_".$procedure_id."_" . $c . "_" . date("H-i-s") . ".pdf";
        $msg = "Stampa definitiva effettuata!";
    }
    else{
        $path = $procTempPath;
        $webPath = PROCEDURE_WEB."TEMP";
        $pdfNameFile = "Rimborso_Spese_Art17_" . $c . "_" . date("H-i-s") . ".pdf";
        $msg = "Stampa provvisoria effettuata!";
    }

    $pdfPath = $path ."/". $pdfNameFile;
    $pdf->Output($pdfPath, "F");
    $pdfWebPath = $webPath."/".$pdfNameFile;

    //echo "<script>window.open('" . $pdfWebPath . "','Merge File','height=700,width=500'); </script>";

}
else{
        //$msg = "Nessuna spesa da rimborsare!";
        if(session_status() == PHP_SESSION_NONE)session_start();
        $_SESSION['progress'] = "100";
        session_write_close();
        $empty = true;
}



$cls_db->End_Transaction();

//echo "<script>location.href = 'rimborso_spese.php?c=".$c."&a=".$a."&msg=".$msg."&error=0';</script>";
if($empty)
    echo json_encode([
        "error" => 2,
        "msg" => "Nessuna spesa da rimborsare!"
    ]);
else
    echo json_encode([
        "path" => $pdfWebPath,
        "error" => 0,
        "msg" => "File stampato correttamente!"
    ]);