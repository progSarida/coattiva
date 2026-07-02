<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";


include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTime.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("IT");
$cls_elab = new cls_elaborazioniUtils();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$file = "";
//include(INC."/header.php");

$_SESSION['progress'] = "0.00";
session_write_close();

$printType = $cls_help->getVar("printType");
$cc = $cls_help->getVar("cc");
$ambito_id = $cls_help->getVar("ambito");
$anno = $cls_help->getVar("anno");
$azione_id = $cls_help->getVar("azione");
$utente_id = $cls_help->getVar("utente");
$da_data = $cls_help->getVar("da_data")." 00:00:00";
$a_data = $cls_help->getVar("a_data")." 23:59:59";

$data_form_da = intval("00000000000000");
$data_form_a = intval("99999999999999");

//var_dump($ambito_id);
//var_dump($anno);
//var_dump($utente_id);
//var_dump($da_data);
//var_dump($a_data);
//var_dump($a);
//die;

//// Filtri
// Ambito
$ambito_ = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM history_pages WHERE id = $ambito_id"));
$filter["ambito"] = $ambito_[0]['name'];
// Anno
$year = "*";
if($anno != ""){
    $filter["anno"] = $anno;
    $year = $anno;
}
// Azione
if($azione_id != '_'){
    $azione_ = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM history_actions WHERE action = '$azione_id'"));
    $filter["azione"] = $azione_[0]['name'];
}
// Utente
if($utente_id != 0){
    $utente_ = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM autenticazione WHERE ID = $utente_id"));
    $filter["utente"] = $utente_[0]['User'];
}
// Intervallo date
if($da_data != " 00:00:00"){
    $filter["da_data"] = $cls_date->Get_DateNewFormat($da_data);
    //$da_data_cls = new cls_DateTime($da_data,"DB",true);

    $data_form_da = strtotime($da_data);
}
if($a_data != " 23:59:59"){
    $filter["a_data"] = $cls_date->Get_DateNewFormat($a_data);
    //$a_data_cls = new cls_DateTime($a_data,"DB",true);

    $data_form_a = strtotime($a_data);
}

// Selezione file
$files = glob(ARCHIVIO."/storico/".$year."_".$ambito_id."_*.csv");

// Ricerca
$result = [];
for($i = 0; $i < count($files); $i++){

    if (($open = fopen($files[$i], "r")) !== FALSE){
        // Scarta la prima riga del file che contiene il nome dei campi
        $dump = fgetcsv($open,";");
        // Carica log filtrato
        while (($log = fgetcsv($open,0,";")) !== FALSE){

            $data_log = strtotime($log[6]);                                             

            if($azione_id != '_' && $log[0] != $azione_id)                                              // filtro azione
                continue;
            if($utente_id != 0 && $log[3] != $utente_id)                                                // filtro utente
                continue;
            //if($cls_help->getVar("da_data") != "" && $da_data_cls->CompareDate("DB","<",$log[6]))       // filtro da data
            if($cls_help->getVar("da_data") != "" && $data_log < $data_form_da)                         // filtro da data
                continue;
            //if($cls_help->getVar("a_data") != "" && $a_data_cls->CompareDate("DB",">",$log[6]))         // filtro a data
            if($cls_help->getVar("a_data") != "" && $data_log > $data_form_a)                           // filtro a data
                continue;
                
            array_push($result, $log);                                                                  // se passa i filtri viene inserito nel risultato
        }
        fclose($open);
    }
}

array_push($result, array("","","","","","","",));                                                      // Elemento aggiunto per estetica stampa pdf (non contato nel riepilogo)
/*
// Pulisce cartella temporanea
$files = glob(SUPER_ROOT."/archivio/temp/Storico_*");                                                   // trova tutti file 'Storico_*'
foreach($files as $file){                                                                               // 
  if(is_file($file)) {                                                                                  // cancella tutti i file trovati
    unlink($file);                                                                                      // 
  }                                                                                                     //
}
*/
// Preparazione file pdf e excel
$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("ID Utente", "Utente", "Tipo Azione", "Azione", "Data");
$dataExcel[] = array("<b>ID Utente</b>","<b>Utente</b>","<b>Tipo Azione</b>","<b>Azione</b>","<b>Data</b>");

$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent * 0.7 , $percent * 0.7, $percent * 1, $percent * 6.3, $percent * 1.3);
$a_align = array( "L" , "L" , "L" ,"L" ,"L" );
$pdf->setArray($a_width,"a_width");
$pdf->setArray($a_align,"a_align");
$pdf->setHeaderPage();
$pdf->addLines();

$count = count($result)-1;
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

    if($printType == "pdf") {

        $a_value[0] = array(
            $result[$i][3],
            $result[$i][2],
            $result[$i][1],
            $result[$i][5],
            $result[$i][6]
        );

        $return = $pdf->setRowPage($a_value);
        if($return == "addPage")
            $pdf->addLines();
        else if($i < $count-1)
            $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array(
            $result[$i][3],
            $result[$i][2],
            $result[$i][1],
            $result[$i][5],
            $result[$i][6]
        );
    }
    $_SESSION['progress'] = number_format(($i*100)/$count ,0);
    //var_dump($_SESSION['progress']);
}

//die;

$nameFILE = "";
if($printType == "pdf") {

    $a_mainPageParams = array("title" => strtoupper("STAMPA STORICO AZIONI"), "subtitle" => "RICERCA");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_elab->getFiltersDescription($filter);

    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO RIGHE";
    $recap[1]['value'] = count($result)-1;                                                      // Elimino dal conteggio la riga finale che inserisco per estetica
    $pdf->setMainPage($a_filters, $recap);

    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "Storico_Modifiche.pdf";
    $pathFILE .= "/".$nameFILE;

    //die;
    $pdf->Output($pathFILE, 'F');
}
else {
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "Storico_Modifiche.xlsx";
    $pathFILE .= "/".$nameFILE;
    if (count($dataExcel) > 1)
        SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($pathFILE);
}

$pathWEBFILE = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

$file = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;
//var_dump($file);die;
//$prev = STAMPE_WEB."/storico_azioni.php?&p=&c=".$c."&a=".$a;
/*
flush();	ob_flush();
//echo "<script>endBar('Operazione terminata!','".$pathWEBFILE."');$('#btnRet').css('display','block');</script>";
echo "<script>endBar('Operazione terminata!','');$('#btnRet').css('display','block');</script>";
flush();	ob_flush();		flush();	ob_flush();
die;
echo '<script> setTimeout(()=> {back("'.$file.'","'.$prev.'",'.$count.');},1000);</script>';
*/
//include_once INC."/footer.php"; 
//var_dump($file);die;

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);


?>