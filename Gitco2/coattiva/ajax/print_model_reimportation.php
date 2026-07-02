<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_Utils.php";


$cls_utils = new cls_Utils();

$error = 0;
$msg = "Dati inseriti correttamente!";

$dataExcel[] = array("<b>Atto_ID</b>","<b>Flusso_ID</b>","<b>Partita_ID</b>","<b>ID_Cronologico</b>","<b>Anno_Cronologico</b>",
    "<b>Totale con diritto riscossione minimo (facoltativo)</b>","<b>Totale con diritto riscossione massimo (facoltativo)</b>","<b>Totale_Dovuto</b>",
    "<b>Diritto_Riscossione_Minimo</b>","<b>Diritto_Riscossione_Massimo</b>","<b>Interessi</b>","<b>Spese_Notifica</b>","<b>Spese_Notifica_Precedenti</b>",
    "<b>Interessi_Precedenti</b>","<b>PrinterId (ID_Stampatore)</b>","<b>PrintTypeId (ID Tipo Invio)</b>","<b>Data_Flusso</b>","<b>Codice Catastale</b>",
    "<b>DocumentTypeId (ID tipo di atto)</b>","<b>DocumentType (Nome Tipo atto)</b>");

$pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
$nameFILE = "ModelloReimportazione.xlsx";
$pathFILE .= "/".$nameFILE;

SimpleXLSXGen::fromArray($dataExcel)
    ->setDefaultFont('Courier New')
    ->setDefaultFontSize(10)
    ->saveAs($pathFILE);

$pathWEBFILE = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

echo json_encode([
    "status" => "s",
    "response" => "File creato correttamente!",
    "urlFile" => $pathWEBFILE
])
?>