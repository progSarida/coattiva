<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

//include_once INC . "/header.php";
//include_once INC . "/menu.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_ente.php";
include_once ELABORAZIONI_CLS. "/cls_DettaglioRimborsoSpese.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$php_excel = new PHPExcel();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$anno_fornitura = $cls_help->getVar("anno_fornitura");
$printType = $cls_help->getVar('printType');
$partita_da = $cls_help->getVar("partita_da");
$partita_a = $cls_help->getVar("partita_a");
$anno_rif_da = $cls_help->getVar("anno_rif_da");
$anno_rif_a = $cls_help->getVar("anno_rif_a");
$data_stampa = $cls_help->getVar("data_stampa");

$_SESSION['progress'] = "0.00";
session_write_close();

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );

$anno_riferimento = $anno_fornitura;

if(!empty($data_stampa))
    $data_stampa = $cls_help->toDbDate($data_stampa);
else
    $data_stampa = date("Y-m-d");

$procedureDescription = "Rimborso spese esecutive Art17 EXCEL".$a_enteAdmin['Info_Denominazione']." per l'anno ".$anno_fornitura;

$query = "SELECT 
            if(U.Genere='D',CONCAT(U.Ditta,IF(SRL.ID>0,CONCAT(' ',SRL.Sigla),'')),CONCAT(U.Cognome,' ',U.Nome)) As Denominazione, 
            PT.Comune_ID AS Partita_Comune_ID,
            if(U.Genere='D',U.Partita_Iva,U.Codice_Fiscale) AS CF_PI,
            concat(IV.Indirizzo,',',IV.Civico,' - ', IV.CAP, ' ' , IV.Comune) as Indirizzo,
            PS.*, PT.Tipo AS Tipo_Riscossione,
            PG.Anno_Cronologico,
            EG.Denominazione as Ente_Affidatario,
            PT.Tipo as Tipo
          FROM sgravio S
          JOIN partita_tributi PT ON PT.ID=S.Partita_ID    
          JOIN utente U ON PT.Utente_ID = U.ID
          LEFT JOIN forma_giuridica_societa AS SRL ON SRL.ID = U.Forma_Giuridica
          join v_indirizzo_con_via as IV on U.ID = IV.Utente_ID 
          JOIN sgravi_documenti SD ON SD.Sgravio_ID=S.ID AND SD.DocumentId is not null
          JOIN document_type DT ON DT.Id=SD.DocumentTypeId AND DT.TableTypeId=2    
          JOIN pignoramento_generale AS PG ON PG.Partita_ID=S.Partita_ID
          JOIN enti_gestiti as EG on EG.CC = PG.CC
          JOIN pignoramento_spese as PS ON PG.ID=PS.Pignoramento_ID
          WHERE S.CC = '".$c."' AND PG.Anno_Cronologico=".$anno_riferimento;

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

$query .= " ORDER BY Denominazione";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");
//var_dump($result);die;
$count = count($result);
if($count == 0) {
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();
	
    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
	
    die;
}

$query_t = "SELECT * FROM tariffe_coazione";
$tariffe = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");

// Header file
// Prima riga
$dataExcel[] = array("<b>ELENCO RIMBORSI RELATIVI A PROCEDURE CAUTELARI/DI COAZIONE ATTIVATE E NON PAGATE DAI DEBITORI SUCCESSIVAMENTE ALL'ANNO DELLA RICHIESTA</b>","","","","","","","","","","");   
// Riga vuota               
$dataExcel[] = array("","","","","","","","","","","");                  
// Nomi colonne                       
$dataExcel[] = array("<b>CODICE ENTE</b>","<b>ENTE AFFIDATARIO DEL SERVIZIO</b>","<b>ANNO</b>","<b>PARTITA ID</b>","<b>ENTRATA</b>","<b>DEBITORE</b>","<b>C.F./P.IVA</b>","<b>RESIDENZA/SEDE</b>","<b>TIPO PROCEDURA</b>","<b>IMPORTO</b>","<b>TOTALE PER POSIZIONE</b>");
// 
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
$i = 0;
$tot_imp = 0;
$tot_imp_pos = 0;
foreach($result as $row){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    $spese = new Spese($cls_db,$anno_riferimento);
    $spese->CreaTotali($row);
    //var_dump($spese->a_crediti);die;
    foreach($spese->a_crediti as $s){
        $dataExcel[] = array(   $row["CC"],
                                $row["Ente_Affidatario"],
                                $row["Anno_Cronologico"],
                                $row["Partita_Comune_ID"],
                                $row["Tipo"],
                                $row["Denominazione"],
                                $row["CF_PI"],
                                $row["Indirizzo"],
                                $s["Descrizione"],
                                $s["Totale"],
                                $spese->totPerPosizione
                            );
        $tot_imp+= $s["Totale"];
        $tot_imp_pos+= $spese->totPerPosizione;                    
    }
    $i++;
}
$select_total = $count+3;
// Riga totali 
$dataExcel[] = array("","","","","","","","","<b>TOTALE GENERALE</b>","<b>$tot_imp</b>","<b>$tot_imp_pos</b>");  
               
/*
$callbackProgress = function($i,$tot)
{

    flush();
    ob_flush();
    echo "<script>$( \"#progressbar\" ).progressbar({value: " . intval($i * 100 /$tot) . " }); $(\"#barlabel\").text( '" . intval($i * 100 / $tot) . "'+'%');</script>";
    flush();
    ob_flush();

};
*/
$utils = new cls_Utils();
$procTempPath = $utils->crea_dir(PROCEDURE ."TEMP");
$cls_file = new cls_file();
$cls_file->removeFiles($procTempPath,7);
$cls_params = new cls_parameters();

set_time_limit(0);
ini_set('memory_limit', '-1');

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

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
    $excelFileName = "Rimborso_Spese_Art17_Dettaglio_".$procedure_id."_" . $c . "_" . date("H-i-s") . ".xlsx";
    $msg = "Stampa definitiva effettuata!";
}
else{
    $path = $procTempPath;
    $webPath = PROCEDURE_WEB."TEMP";
    $excelFileName = "Rimborso_Spese_Art17_Dettaglio_" . $c . "_" . date("H-i-s") . ".xlsx";
    $msg = "Stampa provvisoria effettuata!";
}

$filename = $path ."/". $excelFileName;
$filenameWeb = $webPath ."/". $excelFileName;

//Rimuovo il vecchio XLS
$files = glob($path . '/*.xls*');
foreach ($files as $file) {
     unlink($file);
}

// Salvo file Excel
SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($filename);

/*
$rimborsoSpeseStruttura = new RimborsoSpeseStruttura($cls_db,$c,$anno_riferimento);
$rimborsoSpeseStruttura->clausole_query = $query;

$rimborsoSpeseStruttura->CreaStruttura();
if (!$rimborsoSpeseStruttura->Noresult)
{
    $creaExcelArt17 =new CreaArt17Excel($cls_db,$filename);
    $creaExcelArt17->callback = $callbackProgress;
    $creaExcelArt17->CreaExcel($rimborsoSpeseStruttura);
    $creaExcelArt17->Salva();
    //echo "<script>window.open('$filenameWeb', '_blank');</script>";
}
else
{
    $error = 2;
    $msg = "Nessun dato presente!";
}
*/
$cls_db->End_Transaction();


echo json_encode([
    "path" => $filenameWeb,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);



die;

//echo "<script>location.href = 'rimborso_spese_excel.php?c=".$c."&a=".$a."&msg=".$msg."&error=0';</script>";