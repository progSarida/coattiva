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

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$anno_gestione = $cls_help->getVar("anno_gestione");
$data_stampa = $cls_help->getVar("data_stampa");
$printType = $cls_help->getVar('printType');

$_SESSION['progress'] = "0.00";
session_write_close();

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );							
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";																	
$adminCityName = $a_enteAdmin['Denominazione'];

$par_annuali = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM parametri_annuali WHERE CC='".$c."' ORDER BY Anno DESC LIMIT 1"));

$query = "SELECT DISTINCT Tipo FROM partita_tributi WHERE CC='".$c."'";
$a_partitaTypes = $cls_db->getResults($cls_db->ExecuteQuery($query));

$query = "SELECT * FROM document_type";
$a_docTypes = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");

$query = "SELECT PT.Comune_ID, A.ID, A.DocumentTypeId, A.CC, A.Data_Notifica, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Stampa, 
A.Totale_Dovuto-IFNULL(SUM(PGPREC.Importo),0)+A.Diritto_Riscossione_Minimo AS TOTALE1, 
A.Totale_Dovuto-IFNULL(SUM(PGPREC.Importo),0)+A.Diritto_Riscossione_Massimo AS TOTALE2, 
A.Totale_Rateizzato, 
SUM(PGDOC.Importo) as TOTALE_PAGAMENTI, PGDOC.Data_Pagamento,
A.Motivo_Notifica, PT.Flag_Blocco_Coazione, PT.Motivo_Blocco, R.ID AS Appeal_ID, A.Rate_Previste, A.Tipo_Totale_Rate

FROM atto A 
JOIN partita_tributi PT ON PT.ID=A.Partita_ID 
LEFT JOIN v_pagamenti_doc PGPREC ON PGPREC.Partita_ID=A.Partita_ID AND PGPREC.Data_Stampa_Doc<A.Data_Stampa 
LEFT JOIN v_pagamenti_doc PGDOC ON PGDOC.Partita_ID=A.Partita_ID AND PGDOC.Data_Stampa_Doc=A.Data_Stampa 
LEFT JOIN appeal R ON A.ID=R.Act_ID
WHERE A.CC='".$c."' AND A.Anno_Cronologico=".$anno_gestione." AND A.Data_Stampa is not null AND (A.DocumentTypeId=2 OR A.DocumentTypeId=4 OR A.DocumentTypeId=12)

GROUP BY A.ID ORDER BY A.ID_Cronologico ASC;";
// echo $query;

$a_docs = $cls_db->getResults($cls_db->ExecuteQuery($query));

// var_dump($a_docs);

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(25/100 ,2);
session_write_close();

$a_count = array("emessi"=>0,"pagati"=>0,"notificati"=>0,"impugnati"=>0,"bloccati"=>0);
$a_tot = array("emessi"=>0,"pagati"=>0,"notificati"=>0,"impugnati"=>0,"bloccati"=>0);
$a_countAct = array();
$a_totAct = array();
foreach($a_docs as $key=>$a_doc){

    if(empty($a_countAct[$a_doc['DocumentTypeId']]['emessi'])){
        $a_countAct[$a_doc['DocumentTypeId']] = array("emessi"=>0,"pagati"=>0,"pagati parzialmente"=>0,"notificati"=>0, "impugnati"=>0,"bloccati"=>0);
        $a_totAct[$a_doc['DocumentTypeId']] = array("emessi"=>0,"pagati"=>0,"pagati parzialmente"=>0,"notificati"=>0, "impugnati"=>0,"bloccati"=>0);
    }

     if($a_doc['Rate_Previste']>0){
        if($a_doc['Totale_Rateizzato']>0)
            $checkTotale = $a_doc['Totale_Rateizzato'];
        else if($a_doc['Tipo_Totale_Rate']==1)
            $checkTotale = $a_doc['TOTALE1'];
        else if($a_doc['Tipo_Totale_Rate']==2)
            $checkTotale = $a_doc['TOTALE2'];

        if($a_doc['TOTALE_PAGAMENTI']>0){
            if($checkTotale <=$a_doc['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']){
                $a_countAct[$a_doc['DocumentTypeId']]['pagati']+=1;
                $a_totAct[$a_doc['DocumentTypeId']]['pagati']+=$checkTotale;
            }
            else{
                $a_countAct[$a_doc['DocumentTypeId']]['pagati parzialmente']+=1;
                $a_totAct[$a_doc['DocumentTypeId']]['pagati parzialmente']+=$checkTotale;
            }
        }
    }
    else{
        if(empty($a_doc['Data_Notifica'])){
            $checkTotale = $a_doc['TOTALE1'];
            if($a_doc['TOTALE_PAGAMENTI']>0){
                if($checkTotale <=$a_doc['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']){
                    $a_countAct[$a_doc['DocumentTypeId']]['pagati']+=1;
                    $a_totAct[$a_doc['DocumentTypeId']]['pagati']+=$checkTotale;
                }
                else{
                    $a_countAct[$a_doc['DocumentTypeId']]['pagati parzialmente']+=1;
                    $a_totAct[$a_doc['DocumentTypeId']]['pagati parzialmente']+=$checkTotale;
                }
            }
        }
        else{
            $checkDate = date('Y-m-d', strtotime($a_doc['Data_Notifica']. ' + 60 days'));
            if($a_doc['TOTALE_PAGAMENTI']>0 && $a_doc['Data_Pagamento']<$checkDate)
                $checkTotale = $a_doc['TOTALE1'];
            else
                $checkTotale = $a_doc['TOTALE2'];

            if($a_doc['TOTALE_PAGAMENTI']>0){
                if($checkTotale <=$a_doc['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']){
                    $a_countAct[$a_doc['DocumentTypeId']]['pagati']+=1;
                    $a_totAct[$a_doc['DocumentTypeId']]['pagati']+=$checkTotale;
                }
                else{
                    if(date('Y-m-d')>$checkDate)
                        $checkTotale = $a_doc['TOTALE2'];
                    $a_countAct[$a_doc['DocumentTypeId']]['pagati parzialmente']+=1;
                    $a_totAct[$a_doc['DocumentTypeId']]['pagati parzialmente']+=$checkTotale;
                }
            }
        }
    }
    
    $a_countAct[$a_doc['DocumentTypeId']]['emessi']+=1;
    $a_totAct[$a_doc['DocumentTypeId']]['emessi']+=$checkTotale;

    if(!empty($a_doc['Data_Notifica'])){
        $a_countAct[$a_doc['DocumentTypeId']]['notificati']+=1;
        $a_totAct[$a_doc['DocumentTypeId']]['notificati']+=$checkTotale;
    }
        
    if(!empty($a_doc['Appeal_ID'])){
        $a_countAct[$a_doc['DocumentTypeId']]['impugnati']+=1;
        $a_totAct[$a_doc['DocumentTypeId']]['impugnati']+=$checkTotale;
    }
        

    //! PROBLEMA: COME FACCIAMO A CAPIRE SE L'ATTO ARCHIVIATO E' QUELLO CHE ABBIAMO RECUPERATO?
    //! IL BLOCCO E' SULLA PARTITA E NON SULL'ATTO
    if($a_doc['Flag_Blocco_Coazione']=="si"){
        $a_countAct[$a_doc['DocumentTypeId']]['bloccati']+=1;
        $a_totAct[$a_doc['DocumentTypeId']]['bloccati']+=$checkTotale;
    }

}

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(50/100 ,2);
session_write_close();


$queryPg = "SELECT PT.Comune_ID, P.ID, P.CC, P.DocumentTypeId, NA.Data_Notifica, P.ID_Cronologico, P.Anno_Cronologico, EL.PrintDate, P.Data_Stampa, P.Totale_Dovuto, 
SUM(PG.Importo) as TOTALE_PAGAMENTI, PG.Data_Pagamento,
NA.Motivo_Notifica, PT.Flag_Blocco_Coazione, PT.Motivo_Blocco, P.Stato_Pignoramento

FROM pignoramento_generale P
JOIN notifica_atto NA ON NA.Atto_Notificato_ID=P.ID AND NA.Tipo_Notifica='debitore'
LEFT JOIN elaboration_lists EL ON EL.ID=NA.Elaboration_List_Id
JOIN partita_tributi PT ON PT.ID=P.Partita_ID 
LEFT JOIN v_pagamenti_doc PG ON PG.Partita_ID=P.Partita_ID AND PG.Data_Stampa_Doc<=P.Data_Stampa
WHERE P.CC='".$c."' AND P.Anno_Cronologico=".$anno_gestione." AND ( EL.PrintDate is not null OR P.Data_Stampa is not null )
GROUP BY P.ID ORDER BY P.ID_Cronologico ASC;";

$a_pgs = $cls_db->getResults($cls_db->ExecuteQuery($queryPg));
$a_countPgType = array();
$a_totPgType = array();
foreach($a_pgs as $key=>$a_pg){

    if(empty($a_countPgType[$a_pg['DocumentTypeId']]['emessi'])){
        $a_countPgType[$a_pg['DocumentTypeId']] = array("emessi"=>0,"pagati"=>0,"pagati parzialmente"=>0,"notificati"=>0, "archiviati"=>0, "annullati"=>0,"bloccati"=>0);
        $a_totPgType[$a_pg['DocumentTypeId']] = array("emessi"=>0,"pagati"=>0,"pagati parzialmente"=>0,"notificati"=>0, "archiviati"=>0, "annullati"=>0,"bloccati"=>0);
    }
    $checkTotale = $a_pg['Totale_Dovuto'];

    $a_countPgType[$a_pg['DocumentTypeId']]['emessi']+=1;
    $a_totPgType[$a_pg['DocumentTypeId']]['emessi']+=$checkTotale;

    if($a_pg['TOTALE_PAGAMENTI']>0){
        if($checkTotale <=$a_pg['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']){

            $a_countPgType[$a_pg['DocumentTypeId']]['pagati']+=1;
            $a_totPgType[$a_pg['DocumentTypeId']]['pagati']+=$checkTotale;
        }
        else{
            $a_countPgType[$a_doc['DocumentTypeId']]['pagati parzialmente']+=1;
            $a_totPgType[$a_doc['DocumentTypeId']]['pagati parzialmente']+=$checkTotale;
        }
    }
    

    if(!empty($a_pg['Data_Notifica'])){

        $a_countPgType[$a_pg['DocumentTypeId']]['notificati']+=1;
        $a_totPgType[$a_pg['DocumentTypeId']]['notificati']+=$checkTotale;
    }
        

    if($a_pg['Flag_Blocco_Coazione']=="si"){

        $a_countPgType[$a_pg['DocumentTypeId']]['bloccati']+=1;
        $a_totPgType[$a_pg['DocumentTypeId']]['bloccati']+=$checkTotale;
    }

    if($a_pg['Stato_Pignoramento']=="Archiviato"){

        $a_countPgType[$a_pg['DocumentTypeId']]['archiviati']+=1;
        $a_totPgType[$a_pg['DocumentTypeId']]['archiviati']+=$checkTotale;
    }
    else if($a_pg['Stato_Pignoramento']=="Annullato"){

        $a_countPgType[$a_pg['DocumentTypeId']]['annullati']+=1;
        $a_totPgType[$a_pg['DocumentTypeId']]['annullati']+=$checkTotale;
    }
    
}


$utils = new cls_Utils();
$procTempPath = $utils->crea_dir(PROCEDURE ."TEMP");
$cls_file = new cls_file();
$cls_file->removeFiles($procTempPath,7);

$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
$managerCity = $cls_ente->getCityManager();

//var_dump($cls_ente->a_header);die;

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(75/100 ,2);
session_write_close();

$err = 0;
$filePath = ARCHIVIO."/firme/".$c."/".$cls_ente->a_ente['Gestore_File_Firma'];
$pdfWebPath = "";
$msg = "";
if(!empty($cls_ente->a_ente['Gestore_File_Firma']) && is_file($filePath)){
    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

    $pdf->setDocParams();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    if ($printType == "temp")
        $pdf->temporaryPrinting();
    $pdf->setManagerHeader($cls_ente->a_header);

    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(10);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(30, 0, "Ente:" , 0, "L", 0, 0);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(110, 0, strtoupper($cls_ente->a_ente['Ente_Denominazione']) , 0, "L", 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(30, 0, "Gestione: " , 0, "L", 0, 0);
    $types = "";
    foreach($a_partitaTypes as $key=>$a_partita){
        if($key>0)
            $types.= " - ".$a_partita['Tipo'];
        else
            $types.= $a_partita['Tipo'];
    }
    $pdf->SetFont('helvetica', 'B', 9);
    //$pdf->MultiCell(150, 0, "COATTIVA (".$types.")" , 0, "L", 0, 1);
    $pdf->MultiCell(150, 0, "RISCOSSIONE COATTIVA" , 0, "L", 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(30, 0, "Stampa:" , 0, "L", 0, 0);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(150, 0, "RENDICONTO DELLA GESTIONE DEL CONCESSIONARIO ".strtoupper($cls_ente->a_ente['Gestore_Denominazione']) , 0, "L", 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(30, 0, "Esercizio:" , 0, "L", 0, 0);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(110, 0, $anno_gestione , 0, "L", 0, 1);
    $pdf->ln(5);
    foreach($a_countAct as $docType=>$count){
        $pdf->SetFont('helvetica', 'B', 9);
        $text = $a_docTypes[$docType]['Description'];
        $pdf->MultiCell(150, 0, $text , 0, "L", 0, 1);
        $pdf->ln(2);
        foreach($count as $key=>$value){
            if(empty($a_totAct[$docType][$key]))
                continue;
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(40, 0, "atti ".$key , 0, "L", 0, 0);
            $pdf->MultiCell(20, 0, "numero" , 0, "C", 0, 0);
            $pdf->MultiCell(15, 0, $a_countAct[$docType][$key] , 0, "R", 0, 0);
            $pdf->MultiCell(30, 0, "importo" , 0, "C", 0, 0);
            $pdf->MultiCell(20, 0, number_format($a_totAct[$docType][$key],2,",",".") , 0, "R", 0, 0);
            $pdf->MultiCell(10, 0, "€" , 0, "C", 0, 1);
        }
        $pdf->ln(2);
    }

    foreach($a_countPgType as $docType=>$count){
        $pdf->SetFont('helvetica', 'B', 9);
        $text = $a_docTypes[$docType]['Description'];
        $pdf->MultiCell(150, 0, $text , 0, "L", 0, 1);
        $pdf->ln(2);
        foreach($count as $key=>$value){
            if(empty($a_totPgType[$docType][$key]))
                continue;
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(40, 0, "atti ".$key , 0, "L", 0, 0);
            $pdf->MultiCell(20, 0, "numero" , 0, "C", 0, 0);
            $pdf->MultiCell(15, 0, $a_countPgType[$docType][$key] , 0, "R", 0, 0);
            $pdf->MultiCell(30, 0, "importo" , 0, "C", 0, 0);
            $pdf->MultiCell(20, 0, number_format($a_totPgType[$docType][$key],2,",",".") , 0, "R", 0, 0);
            $pdf->MultiCell(10, 0, "€" , 0, "C", 0, 1);
        }
        $pdf->ln(2);
    }

    if(count($a_docs)==0){
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell(150, 0, "Nessun atto emesso" , 0, "L", 0, 1);
    }

    $pdf->ln(5);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(80, 0, $cls_ente->a_ente['Gestore_Comune']." lì, ".$cls_help->toItalianDate($data_stampa) , 0, "L", 0, 1);
    $pdf->ln(4);
    $pdf->SetFont('helvetica', 'B', 9);

    $pdf->MultiCell(47, 0, "PER ".$cls_ente->a_ente['Gestore_Denominazione'] , 0, "C", 0, 1);

    $cls_file = new cls_file();
    $a_size = $cls_file->imageSize($filePath, 60, 18);
    $offset = (60-$a_size[0])/2;
    $pdf->Image($filePath, $offset, $pdf->getY(), $a_size[0], $a_size[1],'','','C' );
    $pdf->ln(30);

    $pdf->MultiCell(160, 0, "STAMPA GENERATA AUTOMATICAMENTE DAL SISTEMA INFORMATIVO" , 0, "L", 0, 1);
    $pdf->ln(12);

    $pdf->ln(4);
    $pdf->MultiCell(80, 0, "VISTO DI REGOLARITA'" , 0, "L", 0, 1);
    $pdf->ln(8);
    $pdf->MultiCell(80, 0, "___________________" , 0, "L", 0, 0);
    $pdf->ln(7);
    

    if($printType == "final") {
        $q_exist = "select Id from procedures where Procedure_Type_Id=7 AND CC = '$c' and Anno_Riferimento=".$anno_gestione;
        $exist_Id = $cls_db->getArrayLine($cls_db->ExecuteQuery($q_exist));
        if (!empty($exist_Id["Id"]))
            $procedure_id = $exist_Id;
        else
        {
            $a_dbParams = array(
                'table' => 'procedures',
                'fields' => array(
                    array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => 7),
                    array('name' => 'Datetime', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                    array('name' => 'Procedure_Date', 'type' => 'date', 'value' => $cls_help->toDbDate($data_stampa)),
                    array('name' => 'CC', 'type' => 'string', 'value' => $c),
                    array('name' => 'Anno_Riferimento', 'type' => 'int', 'value' => $anno_gestione),
                    array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr']),
                    array('name' => 'Description', 'type' => 'string', 'value' => "Rendiconto della gestione ".$cls_ente->a_ente['Gestore_Denominazione']." dell'anno ".$anno_gestione),
                )
            );
            $procedure_id = $cls_db->DbSave($a_dbParams);
        }
        
        $path = $utils->crea_dir(PROCEDURE . $procedure_id);
        $webPath = PROCEDURE_WEB.$procedure_id;
        $pdfNameFile = "Rendiconto_gestione_".$procedure_id."_" . $c . "_" . date("d-m-Y_H-i-s") . ".pdf";
        $msg = "Stampa definitiva effettuata!";
    }
    else{
        $path = $utils->crea_dir($procTempPath);
        $webPath = PROCEDURE_WEB."TEMP";
        $pdfNameFile = "Rendiconto_gestione_" . $c . "_" . date("d-m-Y_H-i-s") . ".pdf";
        $msg = "Stampa effettuata!";
    }

    $pdfPath = $path ."/". $pdfNameFile;
    $pdf->Output($pdfPath, "F");
    $pdfWebPath = $webPath."/".$pdfNameFile;

    //var_dump($pdfWebPath);die;

    //echo "<script>window.open('" . $pdfWebPath . "','Merge File','height=700,width=500'); </script>";
}
else{
    $err = 1;
    $msg = "Firma gestore assente!";
}

$cls_db->End_Transaction();

if($err == 1)
    echo json_encode([
        "error" => 1,
        "msg" => $msg
    ]);
else 
    echo json_encode([
        "path" => $pdfWebPath,
        "error" => 0,
        "msg" => $msg
    ]);
//echo "<script>location.href = 'rendiconto_gestione.php?c=".$c."&a=".$a."&anno_gestione=".$anno_gestione."&data_stampa=".$data_stampa."&msg=".$msg."&error=".$err."';</script>";