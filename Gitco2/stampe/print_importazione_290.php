<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/cls_pdf2.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("IT",false);
$cls_elab = new cls_elaborazioniUtils();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$importId = $cls_help->getVar("id");
$type = $cls_help->getVar("type");
$printType = $cls_help->getVar("printType");

$now = date("d/m/Y");                                   // data stampata
$now_db  = date("Y-m-d");                               // data inserimento in 'procedures'
$now_time = date('Y-m-d H:i:s');                        // data e ora inserimento in 'procedures'
$now_time_ = date("d-m-Y_H-i-s");                       // data nome file definitivo

//var_dump($printType);die;

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(0 ,2);
session_write_close();

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$nome_ente = $ente['Info_Comune'];

$import = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM imports WHERE Id = ".$importId) );
$nome_file = $import['Name'];

// tebelle interessate
$query_tables = ' FROM imports AS I
                    JOIN ruolo AS R ON R.ID = I.Ruolo_ID
                    JOIN partita_tributi AS PT ON R.ID = PT.Ruolo_ID
                    JOIN utente AS U ON U.ID = PT.Utente_ID
                    JOIN indirizzo AS A ON U.ID = A.Utente_ID
                    JOIN toponimo AS T ON T.ID = A.Via_ID
                    JOIN tributo AS TR ON PT.ID = TR.Partita_ID
                    JOIN codice_tributo AS CT ON CT.Codice_Tributo = TR.Codice_Tributo 
                    WHERE I.Id = '.$importId;

// select dati partite
$select_print = 'SELECT TR.Partita_ID, I.Total_Positions, I.Filename, I.Import_Datetime, TR.Anno_Tributo, TR.Codice_Tributo, TR.Imposta, TR.Info_Cartella, CT.Testo_Codice, CT.Descrizione, PT.Tipo, CT.Tipo_Codice, 
                    COALESCE(NULLIF(U.Ditta,""),CONCAT(U.Cognome," ",U.Nome)) AS Utente, 
                    COALESCE(NULLIF(U.Partita_Iva,""),U.Codice_Fiscale) AS CF, 
                    CONCAT(T.Nome," ",A.Civico,", ", A.Cap," ",A.Comune," (",A.Provincia,") ") AS Indirizzo ';

// select codici
$select_codes = 'SELECT TR.Codice_Tributo, CT.Testo_Codice';

// select imposte
$select_levy = 'SELECT DISTINCT(PT.Tipo) ';

$query_print = $select_print.$query_tables;

$query_codes = $select_codes.$query_tables." GROUP BY TR.Codice_Tributo";

$query_levy = $select_levy.$query_tables;

//var_dump($query_levy);die;                                        // query prima pagina "Tributo"
//var_dump($query_codes);die;                                       // query prima pagina "Elenco Codici"
//var_dump($query_print);die;                                       // query stampa

$result_levy = $cls_db->getResults($cls_db->ExecuteQuery($query_levy));
$result_codes = $cls_db->getResults($cls_db->ExecuteQuery($query_codes));
$result_print = $cls_db->getResults($cls_db->ExecuteQuery($query_print));

//var_dump($result[0]);die;

// array stampa subtotali
$subTot = array();
foreach($result_codes as $code){
    $subTot[$code["Codice_Tributo"]] = array($code["Testo_Codice"],0);
}

//var_dump($subTot);die;


///////////////////////////////		PDF	    //////////////////////////////////
///////////////////////////////		PAGINE ELENCO	    //////////////////////////////////

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
$pdf_check = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
//$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);


$styleDash = array('dash' => '6,6');
$styleRetta = array('dash' => '0');

$pdf->AddPage('L');
$pdf->SetFont('Arial', 'B', 10);
$pdf_check->AddPage('L');
$pdf_check->SetFont('Arial', 'B', 10);

$dim_pag = $pdf->getPageDimensions();
$larghezza_pag = $pdf->getPageWidth();
$altezza_pag = $pdf->getPageHeight();	

//var_dump($larghezza_pag);die;
$totale_compl_pdf = 0;
$totale_compl_excel = 0;

$pdf->SetAutoPageBreak(false);
$pdf->Ln(5);
$pdf_check->SetAutoPageBreak(false);
$pdf_check->Ln(5);

$array_width = array();
$array_intestaz_1 = array();
$array_intestaz_2 = array();
    
$array_width_1 = array( 100 , 50 , ($larghezza_pag-150-20));                    // riga dati utente
$array_width_4 = array( $larghezza_pag-20);                                     // riga info
$array_width_2 = array( 20 , 30 , ($larghezza_pag-80-20) , 30 );                // riga tributo
$array_width_3 = array( ($larghezza_pag-30-20) , 30 );                          // riga totale

$array_intestaz_1[] = "Utente";				
$array_intestaz_1[] = "CF/P.IVA";
$array_intestaz_1[] = "Indirizzo";		

$array_intestaz_3[] = "Informazioni Cartella";

$array_intestaz_2[] = "Anno";
$array_intestaz_2[] = "Cod. Tributo";
$array_intestaz_2[] = "Descrizione";
$array_intestaz_2[] = "Dettaglio";

$array_align_1 = array("L","L","L");
$array_align_2 = array("L","L","L","R");
$array_align_3 = array("R","R");
$array_align_4 = array("L");

$pdf->SetFont('Arial',"", 8);
$pdf->Cell (0, 0, $ente['Info_Denominazione'], 0, 1, "L");

$pdf->ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf_check->SetFont('Arial', 'B', 10);

$pdf->setCellPaddings(2,1,2,0);
$y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,$array_align_1,0,$array_width_1);
$pdf->setCellPaddings(2,0,2,0);
$y1_vert = $pdf->setRow($array_intestaz_3,"no",$styleRetta,$array_align_4,0,$array_width_4);
$pdf->setCellPaddings(2,0,2,0);
$y1_vert = $pdf->setRow($array_intestaz_2,"down",$styleRetta,$array_align_2,0,$array_width_2);

$pdf_check->SetFont('Arial',"", 8);
$pdf_check->Cell (0, 0, $ente['Info_Denominazione'], 0, 1, "L");

$pdf_check->ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf_check->SetFont('Arial', 'B', 10);

$pdf_check->setCellPaddings(2,1,2,0);
$y2_vert = $pdf_check->setRow($array_intestaz_1,"up",$styleRetta,$array_align_1,0,$array_width_1);
$pdf->setCellPaddings(2,0,2,0);
$y2_vert = $pdf_check->setRow($array_intestaz_3,"no",$styleRetta,$array_align_4,0,$array_width_4);
$pdf_check->setCellPaddings(2,0,2,0);
$y2_vert = $pdf_check->setRow($array_intestaz_2,"down",$styleRetta,$array_align_2,0,$array_width_2);

//var_dump($y1_vert);die;

//$y1_vert = $pdf->setRow($array_intestaz_3,"no",$styleRetta,$array_align_2,0,$array_width);

$count_print = count($result_print);

if($count_print == 0) {
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();
	
    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
	
    die;
}

$check_pdf = array();
$check_excel = array();

// for pdf
for($i=0; $i < $count_print; $i++){
    
    $pdf_check->SetFont('Arial',"", 10);
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/($count_print/2) ,2);
    session_write_close();

    $totale = 0;
    if(in_array($result_print[$i]["Partita_ID"],$check_pdf)){
    //if(in_array($result_print[$i]["Partita_ID"]."_".$result_print[$i]["Anno_Tributo"],$check_pdf)){
        continue;
    }
    else{
        $check_pdf[] = $result_print[$i]["Partita_ID"];
        //$check_pdf[] = $result_print[$i]["Partita_ID"]."_".$result_print[$i]["Anno_Tributo"];
    }


    //var_dump($result_print[$i]["Utente"]);die;

    $pdf_check->SetFont('Arial',"", 10);

    $pdf->ln(1);

    $a_value_i_1 = array(
        strtoupper($result_print[$i]["Utente"]),
        $result_print[$i]["CF"],
        $result_print[$i]["Indirizzo"]
    );

    $y2_vert = $pdf_check->setRow($a_value_i_1,"no",$styleRetta,$array_align_1,0,$array_width_1);

    $pdf_check->SetFont('Arial',"", 10);

    $a_value_i_3 = array(
        $result_print[$i]["Info_Cartella"],
    );

    $y2_vert = $pdf_check->setRow($a_value_i_3,"no",$styleRetta,$array_align_4,0,$array_width_4);

    $pdf_check->SetFont('Arial',"", 10);

    $a_value_i_2 = array(
        $result_print[$i]["Anno_Tributo"],
        $result_print[$i]["Codice_Tributo"],
        $result_print[$i]["Descrizione"],
        $result_print[$i]["Imposta"]." €"
    );

    $y2_vert = $pdf_check->setRow($a_value_i_2,"no",$styleRetta,$array_align_2,0,$array_width_2);

    $subTot[$result_print[$i]["Codice_Tributo"]][1]+=$result_print[$i]["Imposta"];

    $a_value_k = array();

    for($k=$i+1; $k< $count_print; $k++){
        if($result_print[$i]["Partita_ID"] == $result_print[$k]["Partita_ID"]){
        //if($result_print[$i]["Partita_ID"] == $result_print[$k]["Partita_ID"] && $result_print[$i]["Anno_Tributo"] == $result_print[$k]["Anno_Tributo"]){
            $a_value_k_temp = $a_value_k[] = array(
                $result_print[$k]["Anno_Tributo"],
                $result_print[$k]["Codice_Tributo"],
                $result_print[$k]["Descrizione"],
                $result_print[$k]["Imposta"]." €"
            );

            $y2_vert = $pdf_check->setRow($a_value_k_temp,"no",$styleRetta,$array_align_2,0,$array_width_2);

            $subTot[$result_print[$k]["Codice_Tributo"]][1]+=$result_print[$k]["Imposta"];

            $totale+= $result_print[$k]["Imposta"];
        }
    }
    
    $totale+= $result_print[$i]["Imposta"];

    $pdf_check->SetFont('Arial', 'B', 10);

    $a_value_tot = array(
        "TOTALE",
        number_format($totale, 2, ',', '.')." €"
    );

    $y2_vert = $pdf_check->setRow($a_value_tot,"down",$styleRetta,$array_align_3,0,$array_width_3);

    if($y2_vert < $altezza_pag-10){
        $pdf->SetFont('Arial',"", 10);

        $pdf->ln(1);

        $y1_vert = $pdf->setRow($a_value_i_1,"no",$styleRetta,$array_align_1,0,$array_width_1);

        $pdf->SetFont('Arial',"", 10);

        $y1_vert = $pdf->setRow($a_value_i_3,"no",$styleRetta,$array_align_4,0,$array_width_4);

        $pdf->SetFont('Arial',"", 10);

        $y1_vert = $pdf->setRow($a_value_i_2,"no",$styleRetta,$array_align_2,0,$array_width_2);

        foreach($a_value_k as $value_k){
            $y1_vert = $pdf->setRow($value_k,"no",$styleRetta,$array_align_2,0,$array_width_2);
        }

        $pdf->SetFont('Arial',"O", 10);
        $y1_vert = $pdf->setRow($a_value_tot,"down",$styleRetta,$array_align_3,0,$array_width_3);
    }
    else{
        $pdf->AddPage('L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf_check->AddPage('L');
        $pdf_check->SetFont('Arial', 'B', 10);

        $pdf->SetFont('Arial',"", 8);
        $pdf->Cell (0, 0, $ente['Info_Denominazione'], 0, 1, "L");
        $pdf_check->SetFont('Arial',"", 8);
        $pdf_check->Cell (0, 0, $ente['Info_Denominazione'], 0, 1, "L");

        $pdf->ln(5);
        $pdf_check->ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf_check->SetFont('Arial', 'B', 10);

        $pdf->setCellPaddings(2,1,2,0);
        $y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,$array_align_1,0,$array_width_1);
        $pdf->setCellPaddings(2,0,2,0);
        $y1_vert = $pdf->setRow($array_intestaz_3,"no",$styleRetta,$array_align_4,0,$array_width_4);
        $pdf->setCellPaddings(2,0,2,0);
        $y1_vert = $pdf->setRow($array_intestaz_2,"down",$styleRetta,$array_align_2,0,$array_width_2);

        $pdf_check->setCellPaddings(2,1,2,0);
        $y2_vert = $pdf_check->setRow($array_intestaz_1,"up",$styleRetta,$array_align_1,0,$array_width_1);
        $pdf->setCellPaddings(2,0,2,0);
        $y2_vert = $pdf_check->setRow($array_intestaz_3,"no",$styleRetta,$array_align_4,0,$array_width_4);
        $pdf_check->setCellPaddings(2,0,2,0);
        $y2_vert = $pdf_check->setRow($array_intestaz_2,"down",$styleRetta,$array_align_2,0,$array_width_2);
        
        $pdf->SetFont('Arial',"", 10);
        $pdf_check->SetFont('Arial',"", 10);

        $pdf->ln(1);
        $pdf_check->ln(1);

        $y1_vert = $pdf->setRow($a_value_i_1,"no",$styleRetta,$array_align_1,0,$array_width_1);
        $y2_vert = $pdf_check->setRow($a_value_i_1,"no",$styleRetta,$array_align_1,0,$array_width_1);

        $pdf->SetFont('Arial',"", 10);
        $pdf_check->SetFont('Arial',"", 10);

        $y1_vert = $pdf->setRow($a_value_i_3,"no",$styleRetta,$array_align_4,0,$array_width_4);
        $y2_vert = $pdf_check->setRow($a_value_i_3,"no",$styleRetta,$array_align_4,0,$array_width_4);

        $pdf->SetFont('Arial',"", 10);
        $pdf_check->SetFont('Arial',"", 10);

        $y1_vert = $pdf->setRow($a_value_i_2,"no",$styleRetta,$array_align_2,0,$array_width_2);
        $y2_vert = $pdf_check->setRow($a_value_i_2,"no",$styleRetta,$array_align_2,0,$array_width_2);

        foreach($a_value_k as $value_k){
            $y1_vert = $pdf->setRow($value_k,"no",$styleRetta,$array_align_2,0,$array_width_2);
            $y2_vert = $pdf_check->setRow($value_k,"no",$styleRetta,$array_align_2,0,$array_width_2);
        }

        $pdf->SetFont('Arial',"O", 10);
        $pdf_check->SetFont('Arial',"O", 10);

        $y1_vert = $pdf->setRow($a_value_tot,"down",$styleRetta,$array_align_3,0,$array_width_3);
        $y2_vert = $pdf_check->setRow($a_value_tot,"down",$styleRetta,$array_align_3,0,$array_width_3);
    }

    $totale_compl_pdf+= $totale;

}

//var_dump($subTot);die;

///////////////////////////////		PAGINA RIEPILOGO	    //////////////////////////////////

$pdf->addPage();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->setCellPaddings(2,0,2,1);

$pdf->SetFont('Arial',"", 8);
$pdf->Cell (0, 0, $ente['Info_Denominazione'], 0, 1, "L");

$pdf_check->ln(5);

$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 0, "TOTALE" , "B", 1, 'C', 0, '', 0, false, 'T', 'M');

foreach($subTot as $s){
    $pdf->ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(($larghezza_pag-20)/2, 0, "Totale  ".$s[0] , 0, 0, 'L', 0, '', 0, false, 'T', 'M');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(($larghezza_pag-20)/2, 0, number_format($s[1], 2, ',', '.')." €" , 0, 1, 'R', 0, '', 0, false, 'T', 'M');
}

$pdf->ln(5);

$style = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(0, 0, 0));
$pdf->Line($pdf->getX(), $pdf->getY(), $pdf->getPageWidth()-10, $pdf->getY(), $style);

$pdf->ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(($larghezza_pag-20)/2, 0, "TOTALE " , 0, 0, 'L', 0, '', 0, false, 'T', 'M');
$pdf->Cell(($larghezza_pag-20)/2, 0, number_format($totale_compl_pdf, 2, ',', '.')." €" , 0, 1, 'R', 0, '', 0, false, 'T', 'M');

///////////////////////////////		PRIMA PAGINA	    //////////////////////////////////

$nameFILE = "";
$nameFILEX = "";

$pdf->addPage();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->setCellPaddings(2,0,2,1);
//$pdf->ln(2);
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 0, "FRONTESPIZIO DELLA MINUTA DI RUOLO" , "B", 1, 'L', 0, '', 0, false, 'T', 'M');

$pdf->ln(4);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Codice/Descrizione Ente: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell (0, 0, $ente['Info_Comune']." (".$ente['Info_Provincia'].")", 0, 1, "L");

$pdf->ln(2);

$createDate = new DateTime($result_print[0]['Import_Datetime']);
$import_date = $createDate->format('d/m/Y');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Data importazione: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell (0, 0, $import_date, 0, 1, "L");   

$pdf->ln(2);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Data stampa: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell (0, 0, $now, 0, 1, "L");   

$pdf->ln(2);

$resp = array();
$levy_temp = array();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Tributo: " , 0, 0, 'L');
for($i=0;$i<count($result_levy);$i++){
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell (0, 0, $result_levy[$i]['Tipo'], 0, 1, "L");
    $levy_temp[] = $result_levy[$i]['Tipo'];
    $pdf->Cell(105, 0, "" , 0, 0, 'L');
    $resp[] = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM `parametri_responsabili` WHERE CC = '".$c."' AND Tipo_Riscossione LIKE '%".$result_levy[$i]['Tipo']."%'") );
} 

$pdf->ln(2);

$code_temp = array();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Elenco codici utilizzati: " , 0, 0, 'L');
for($i=0;$i<count($result_codes);$i++){
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell (15, 0, "[".$result_codes[$i]["Codice_Tributo"]."] ".": ", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell ( 0 , 5, $result_codes[$i]["Testo_Codice"] , 0, 1, "L");
    $code_temp[] = array(
        $result_codes[$i]["Codice_Tributo"],
        $result_codes[$i]["Testo_Codice"]
    );
    if($i != count($result_codes)-1)
        $pdf->Cell(105, 0, "" , 0, 0, 'L');
}

$pdf->ln(2);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Ente impositore: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell ( 0 , 5, $ente["Info_Denominazione"] , 0, 1, "L");

$pdf->ln(2);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Beneficiario: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell ( 0 , 5, $ente["Info_Denominazione"].", ".$ente["Info_Via"].", ".$ente["Info_Civico"].", ".$ente['Info_Cap'].", ".$ente['Info_Comune']." (".$ente['Info_Provincia'].")" , 0, 1, "L");

$pdf->ln(3);

$pdf->Line($pdf->getX(), $pdf->getY(), $pdf->getPageWidth()-10, $pdf->getY(), array('width' => 0.2, 'color' => array(0, 0, 0))); 

$pdf->ln(3);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Tipo ruolo: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell ( 0 , 5, "Coattivo" , 0, 1, "L");

$pdf->ln(2);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Numero Posizioni Importate: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell (0, 0, $result_print[0]['Total_Positions'], 0, 1, "L");   

$pdf->ln(2);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Importo totale da riscuotere: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell (0, 0, "€ ".number_format($totale_compl_pdf, 2, ',', '.'), 0, 1, "L");   

$pdf->ln(2);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Numero di Rate: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell ( 0 , 5, "Unica" , 0, 1, "L");

$pdf->ln(2);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(105, 0, "Scadenza prima rata: " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell ( 0 , 5, "a 30 giorni dalla ricezione" , 0, 1, "L");

$pdf->ln(3);

$pdf->Line($pdf->getX(), $pdf->getY(), $pdf->getPageWidth()-10, $pdf->getY(), array('width' => 0.2, 'color' => array(0, 0, 0)));

$pdf->ln(3);

for($i=0;$i<count($result_levy);$i++){
    $pdf->SetFont('Arial', 'B', 12);
    if(!is_null($result_levy[$i]['Tipo']))
        $pdf->Cell(105, 0, "Responsabile del procedimento ".$result_levy[$i]['Tipo'].": " , 0, 0, 'L');
    else
        $pdf->Cell(105, 0, "Responsabile del procedimento : " , 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    if(!is_null($resp[$i]))
        $pdf->Cell (0, 0, $resp[$i]["Responsabile_Procedimento"], 0, 1, "L");
    else
        $pdf->Cell (0, 0, "........................................", 0, 1, "L");
}

$pdf->ln(2);

$pdf->setY($altezza_pag-15);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 0, "Data di inoltro al Concessionario " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell ( 80 , 5, "........................................" , 0, 0, "L");
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 0, "Timbro e firma " , 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell ( 80 , 5, "........................................" , 0, 0, "L");

$pdf->movePage($pdf->PageNo(), 1);

// creazione excel
// Intestazione
$dataExcel[] = array(
    "<b>Codice/Descrizione Ente</b>","<b>Data Importazione</b>","<b>Data Stampa</b>","<b>Tributo</b>","<b>Elenco Codici Utilizzati</b>",
    "<b>Ente Impositore</b>","<b>Beneficiario</b>",
    "<b>Tipo Ruolo</b>","<b>Numero Posizioni Importate</b>","<b>Importo totale da riscuotere</b>","<b>Numero Rate</b>","<b>Scadenza Prima Rata</b>",
    "<b>Responsabile del Procedimento</b>"
);

for($n=0;$n<count($result_levy);$n++){
    for($m=0;$m<count($result_codes);$m++){
        if($m == 0)
            $dataExcel[] = array(
                $ente["Info_Comune"]." (".$ente["Info_Provincia"].")",$import_date,$now,$result_levy[$n]["Tipo"],$result_codes[$m]["Codice_Tributo"].": ".$result_codes[$m]["Testo_Codice"],
                $ente["Info_Denominazione"],$ente["Info_Denominazione"].", ".$ente["Info_Via"].", ".$ente["Info_Civico"].", ".$ente['Info_Cap'].", ".$ente['Info_Comune']." (".$ente['Info_Provincia'].")",
                "Coattivo",$result_print[0]["Total_Positions"],number_format($totale_compl_excel,2,',','.'),"Unica","a 30 giorni dalla ricezione",
                $resp[$n]["Responsabile_Procedimento"]
            );
        else
            $dataExcel[] = array(
                "","","","",$result_codes[$m]["Codice_Tributo"].": ".$result_codes[$m]["Testo_Codice"],"",
                "",
                "","","","",
                ""
            );
    }
}

$dataExcel[] = array();

$dataExcel[] = array(
    "<b>Utente</b>","<b>CF/P.IVA</b>","<b>Indirizzo</b>","<b>Informazioni Cartella</b>","<b>Anno</b>","<b>Cod. Tributo</b>","<b>Descrizione</b>",
    "<b>Importo</b>","<b>Spese</b>","<b>Interessi</b>","<b>Pagamento</b>","<b>Sanzione</b>","<b>Addizionale</b>","<b>Sanzioni</b>","<b>Maggiorazione</b>","<b>Sollecito</b>","<b>Diritti Accessori</b>","<b>Oneri</b>",
    "<b>Totale</b>"
);

// for excel
for($i=0; $i < $count_print; $i++){
    
    $pdf_check->SetFont('Arial',"", 10);
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/($count_print/2) ,2);
    session_write_close();

    $totale = 0;
    //if(in_array($result_print[$i]["Partita_ID"],$check_excel)){
    if(in_array($result_print[$i]["Partita_ID"]."_".$result_print[$i]["Anno_Tributo"],$check_excel)){
        continue;
    }
    else{
        //$check_excel[] = $result_print[$i]["Partita_ID"];
        $check_excel[] = $result_print[$i]["Partita_ID"]."_".$result_print[$i]["Anno_Tributo"];
    }


    //var_dump($result_print[$i]["Utente"]);die;

    //$subTot[$result_print[$i]["Codice_Tributo"]][1]+=$result_print[$i]["Imposta"];

    $row = array(
        "IMPORTO" => "",
        "SPESE" => "",
        "INTERESSI" => "",
        "PAGAMENTO" => "",
        "SANZIONE" => "",
        "ADDIZIONALE" => "",
        "SANZIONI" => "",
        "MAGGIORAZIONE" => "",
        "SOLLECITO" => "",
        "DIRITTI ACCESSORI" => "",
        "ONERI" => ""
      );

    for($k=$i; $k< $count_print; $k++){
        //if($result_print[$i]["Partita_ID"] == $result_print[$k]["Partita_ID"]){
        if($result_print[$i]["Partita_ID"] == $result_print[$k]["Partita_ID"] && $result_print[$i]["Anno_Tributo"] == $result_print[$k]["Anno_Tributo"]){

            $row[$result_print[$k]["Tipo_Codice"]] = $result_print[$k]["Imposta"];

            $totale+= $result_print[$k]["Imposta"];
        }
    }
    
    //$totale+= $result_print[$i]["Imposta"];

    $dataExcel[] = array(
        strtoupper($result_print[$i]["Utente"]),$result_print[$i]["CF"],$result_print[$i]["Indirizzo"],$result_print[$i]["Info_Cartella"],$result_print[$i]["Anno_Tributo"],$result_print[$i]["Codice_Tributo"],$result_print[$i]["Descrizione"],
        $row["IMPORTO"],$row["SPESE"],$row["INTERESSI"],$row["PAGAMENTO"],$row["SANZIONE"],$row["ADDIZIONALE"],$row["SANZIONI"],$row["MAGGIORAZIONE"],$row["SOLLECITO"],$row["DIRITTI ACCESSORI"],$row["ONERI"],
        $totale
    );

    $totale_compl_excel+= $totale;

}

$dataExcel[] = array();
$dataExcel[] = array();

foreach($subTot as $s){
    $dataExcel[] = array(
        "<b>Totale ".$s[0]."</b>","",number_format($s[1], 2, ',', '.'),"","","","","","","","","","","","","","","",""
    );
}

$dataExcel[] = array();

$dataExcel[] = array(
    "<b>TOTALE</b>","",$totale_compl_excel,"","","","","","","","","","","","","","","",""
);

if($type == 'prov'){

    $pathFILE = $cls_utils->crea_dir(PROCEDURE."TEMP");

    if($printType == 'pdf'){
        $nameFILE = "Minuta_di_ruolo_".$nome_ente."_".$nome_file.".pdf";
        $pathFILE .= "/".$nameFILE;
        $pdf->Output($pathFILE, 'F');
    }
    else{
        $nameFILE = "Minuta_di_ruolo_".$nome_ente."_".$nome_file.".xlsx";
        $pathFILE .= "/".$nameFILE;
        if (count($dataExcel) > 1)
            SimpleXLSXGen::fromArray($dataExcel)
                ->setDefaultFont('Courier New')
                ->setDefaultFontSize(14)
                ->saveAs($pathFILE);
    }

    $file = PROCEDURE_WEB."TEMP"."/".$nameFILE;

    if(session_status() == PHP_SESSION_NONE)session_start();

    echo json_encode([
        "path" => $file,
        "error" => '0',
        "msg" => "Stampa provvisoria eseguita correttamente"
    ]);
}
else{
    // inserimento procedura
    $a_dbParams = array(
        'table' => 'procedures',
        'fields' => array(
            array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => 9),
            array('name' => 'Datetime', 'type' => 'date', 'value' => $now_time),
            array('name' => 'Procedure_Date', 'type' => 'date', 'value' => $now_db),
            array('name' => 'CC', 'type' => 'string', 'value' => $c),
            array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr']),
            array('name' => 'Description', 'type' => 'string', 'value' => "Minuta di ruolo ".$ente['Info_Comune']." da ".$result_print[0]["Filename"]),
        )
    );
    $procedure_id = $cls_db->DbSave($a_dbParams);

    // salvataggio file definitivi
    $path = $cls_utils->crea_dir(PROCEDURE . $procedure_id);
    $webPath = PROCEDURE_WEB.$procedure_id;

    //var_dump($path);die;

    $pdfNameFile = "Minuta_di_ruolo_".$procedure_id."_" . $c . "_" . $now_time_ . ".pdf";
    $pdfPath = $path ."/". $pdfNameFile;
    $pdf->Output($pdfPath, "F");

    $excelNameFile = "Minuta_di_ruolo_".$procedure_id."_" . $c . "_" . $now_time_ . ".xlsx";
    $excelPath = $path . "/" . $excelNameFile;
    if (count($dataExcel) > 1)
        SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($excelPath);

    // aggiornamento flag stampa definitiva importazione
    $a_dbParams_elab_list = array(
        'table' => 'imports',
        'updateField' => array(
            array('name' => 'Id',  'type' => 'int', 'value' => $importId),
        ),
        'fields' => array(
            array('name' => 'Flag_Print_Def',  'type' => 'int', 'value' => 1),
        )
    );
    $cls_db->DbSave($a_dbParams_elab_list);

    echo json_encode([
        "path" => $webPath ."/". $pdfNameFile,
        "error" => '0',
        "msg" => "Stampa definitiva eseguita correttamente"
    ]);
}
