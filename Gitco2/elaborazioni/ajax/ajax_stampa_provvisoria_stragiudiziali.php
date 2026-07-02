<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_merge.php";

include_once ELAB_STRAGIUDIZIALI . "/cls/cls_GeneraDocumenti.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

//parametri

$procedure_id = $cls_help->getVar('procedure_id');
$tipo_partita = $cls_help->getVar('tipo_partita');
$tipo = $cls_help->getVar('tipo');
$c = $cls_help->getVar('c');


if(is_null($procedure_id) || is_null($tipo_partita) || is_null($c)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}
try {
    $genera_documenti = new GeneraDocumenti($cls_db);
    $genera_documenti
    ->Inizializzazione($procedure_id,$tipo,$tipo_partita,$c,40)
    ->Genera("temp");

    $a_file = $genera_documenti->a_lastCreated;
   
    $tempPdf = $a_file["pdf"];
    $tempExcel = $a_file["excel"];

    unset($genera_documenti);
}
catch (Exception $e) {
    echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE $e->g'.$e->getMessage()]);
    return;
}

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE','filePdf'=>$tempPdf,'fileExcel'=>$tempExcel]);
return;