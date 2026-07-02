<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//include(INC."/headerAjax.php");
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_split_payment.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_CoazioneUtils.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";

$a_width = array(13,13,13,50,30,14,22,24,26,26,26,21);
$a_align = array("L","L","L","L","L","L","L","R","R","R","R","R");
$a_align_value = array("R","R","R","L","L","L","L","R","R","R","R","R");

$a_width_end = array(89, 44, 22, 24, 26, 26, 26, 21);
$a_align_end = array("L","R","R", "R","R","R","R","R");

$minElements = 5;
function headerArray($a_splitParams){
    $a_header[0] = array("Cron.","Cron.","Cron.","COD-Utente","TipoPag-Rata","Partita","Pagamento");
    $a_header[1] = array("Acc.","Ing.","Pign.","CF / PI","Quietanza","Stato","Data Pag.");
    $a_header[2] = array("","","","","","","");

    $splitElements = 5;

    $minElements = count($a_header[0]);
    $totalElements = $minElements+$splitElements;

    for ($i = 0; $i < count($a_splitParams); $i++) {
        if(strlen($a_splitParams[$i]['header'])>11)
            $headerText = substr($a_splitParams[$i]['header'],0,10).".";
        else
            $headerText = $a_splitParams[$i]['header'];

        if ($i < $splitElements)
            $a_header[0][] = $headerText;
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_header[1][] = $headerText;
        else if ($i >= ($splitElements * 2))
            $a_header[2][] = $headerText;
    }

    if (count($a_header[0]) < $totalElements) {
        for ($i = count($a_header[0]); $i < $totalElements; $i++) {
            $a_header[0][] = "";
        }
    }
    if (count($a_header[1]) < $totalElements) {
        for ($i = count($a_header[1]); $i < $totalElements; $i++) {
            $a_header[1][] = "";
        }
    }
    if (count($a_header[2]) > $minElements && count($a_header[2]) < $totalElements) {
        for ($i = count($a_header[2]); $i < $totalElements; $i++) {
            $a_header[2][] = "";
        }
    }
    return $a_header;
}

function IsInArray ($array, $dato)
{
    for ($i = 0; $i < count($array); $i++)
    {
        if ($dato == $array[$i]) return true;
    }
    return false;
}

function valueArray($a_split, $a_value, $a_splitParams){

    $splitElements = 5;

    $minElements = count($a_value[0]);
    $totalElements = $minElements+$splitElements;


    for ($i = 0; $i < count($a_splitParams); $i++) {
        if($a_split[$a_splitParams[$i]['split_number']]=="")
            $a_split[$a_splitParams[$i]['split_number']] = 0.00;

        if ($i < $splitElements)
            $a_value[0][] = number_format($a_split[$a_splitParams[$i]['split_number']],2,",",".");
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_value[1][] = number_format($a_split[$a_splitParams[$i]['split_number']],2,",",".");
        else if ($i >= ($splitElements * 2))
            $a_value[2][] = number_format($a_split[$a_splitParams[$i]['split_number']],2,",",".");
    }

    if (count($a_value[0]) < $totalElements) {
        for ($i = count($a_value[0]); $i < $totalElements; $i++) {
            $a_value[0][] = "";
        }
    }
    if (count($a_value[1]) < $totalElements) {
        for ($i = count($a_value[1]); $i < $totalElements; $i++) {
            $a_value[1][] = "";
        }
    }
    if (count($a_value[2]) > $minElements && count($a_value[2]) < $totalElements) {
        for ($i = count($a_value[2]); $i < $totalElements; $i++) {
            $a_value[2][] = "";
        }
    }
    return $a_value;
}

function endArray($parzPagato, $a_split, $a_splitParams){
    $a_end[0] = array("PARZIALI DI PAGINA","Totale pagato",number_format($parzPagato,2,",","."));
    $a_end[1] = array("","","");
    $a_end[2] = array("","","");

    $splitElements = 5;

    $minElements = count($a_end[0]);
    $totalElements = $minElements+$splitElements;

    for ($i = 0; $i < count($a_splitParams); $i++) {
        if ($i < $splitElements)
            $a_end[0][] = number_format($a_split[$i],2,",",".");
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_end[1][] = number_format($a_split[$i],2,",",".");
        else if ($i >= ($splitElements * 2))
            $a_end[2][] = number_format($a_split[$i],2,",",".");
    }

    if (count($a_end[0]) < $totalElements) {
        for ($i = count($a_end[0]); $i < $totalElements; $i++) {
            $a_end[0][] = "";
        }
    }
    if (count($a_end[1]) < $totalElements) {
        for ($i = count($a_end[1]); $i < $totalElements; $i++) {
            $a_end[1][] = "";
        }
    }
    if (count($a_end[2]) > $minElements && count($a_end[2]) < $totalElements) {
        for ($i = count($a_end[2]); $i < $totalElements; $i++) {
            $a_end[2][] = "";
        }
    }
    return $a_end;
}

function finalArray($tot_pagato, $a_split, $a_splitParams){
    $a_final[0] = array("TOTALE PAGAMENTI","Totale pagato",number_format($tot_pagato,2,",","."));
    $a_final[1] = array("","","");
    $a_final[2] = array("","","");

    $splitElements = 5;

    $minElements = count($a_final[0]);
    $totalElements = $minElements+$splitElements;

    for ($i = 0; $i < count($a_splitParams); $i++) {
        if ($i < $splitElements)
            $a_final[0][] = number_format($a_split[$i],2,",",".");
        else if ($i >= $splitElements && $i < ($splitElements * 2))
            $a_final[1][] = number_format($a_split[$i],2,",",".");
        else if ($i >= ($splitElements * 2))
            $a_final[2][] = number_format($a_split[$i],2,",",".");
    }

    if (count($a_final[0]) < $totalElements) {
        for ($i = count($a_final[0]); $i < $totalElements; $i++) {
            $a_final[0][] = "";
        }
    }
    if (count($a_final[1]) < $totalElements) {
        for ($i = count($a_final[1]); $i < $totalElements; $i++) {
            $a_final[1][] = "";
        }
    }
    if (count($a_final[2]) > $minElements && count($a_final[2]) < $totalElements) {
        for ($i = count($a_final[2]); $i < $totalElements; $i++) {
            $a_final[2][] = "";
        }
    }
    return $a_final;
}

function imageSize($imgPath, $maxWidth, $maxHeight, $setMaxDim = false)
{
    $a_size = array();
    if (file_exists($imgPath)){
        $dim = getimagesize($imgPath);

        if($dim[0] > $dim[1]){
            $width = $maxWidth;
            $height = $dim[1]*($width/$dim[0]);

            if($height>$maxHeight){
                $height = $maxHeight;
                $width = $dim[0]*($height/$dim[1]);
            }
        }
        else if($dim[0] < $dim[1]){
            $height = $maxHeight;
            $width = $dim[0]*($height/$dim[1]);

            if($width>$maxWidth){
                $width = $maxWidth;
                $height = $dim[1]*($width/$dim[0]);
            }
        }
        else if($dim[0] == $dim[1]){
            if($maxWidth<$maxHeight){
                $width = $maxWidth;
                $height = $maxWidth;
            }
            else{
                $width = $maxHeight;
                $height = $maxHeight;
            }
        }

        if($setMaxDim){
            if($width>$height){
                if($width<$maxWidth){
                    $width = $maxWidth;
                    $height = $dim[1]*($width/$dim[0]);
                }
            }
            else{
                if($width<$maxWidth){
                    $width = $maxWidth;
                    $height = $dim[1]*($width/$dim[0]);
                }
            }
        }

        $a_size = array(
            0=>$width,
            1=>$height
        );

    }
    else{
        //$cls_help = new cls_help();
        //$cls_help->alert("Il file ".$imgPath." non esiste (/Gitco2/cls/cls_file.php)");
        $a_size[0] = 0;
        $a_size[1] = 0;
    }
    return $a_size;

}

$cls_db = new cls_db();
$cls_help = new cls_help();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$anno = $cls_help->getVar('anno');
$type = $cls_help->getVar("printType");

$cls_date = new cls_DateTimeI("IT");

$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_help = new cls_help();
$cls_split = new cls_split_payment();
$cls_coaz = new cls_Coazione();
$cls_stampe = new cls_Stampe();

$_SESSION['progress'] = "0.00";
session_write_close();

$query = "SELECT Id FROM procedures WHERE CC = '".$c."' AND Anno_Riferimento=$anno AND Procedure_Type_Id = 8";
$res = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

if($res !== null && $type == "final"){
    echo "<script>location.href = \"filtri_conto_giudiziale.php?c={$c}&a={$a}&error=2&msg=Elaborazione conto giudiziale già presente! Elaborazione anno {$anno}!\"</script>";
    //header("Location: filtri_conto_giudiziale.php?c={$c}&a={$a}&error=2&msg=Elaborazione conto giudiziale già presente per l\'anno scelto!");
    echo json_encode([
        "error" => 1,
        "msg" => "Elaborazione conto giudiziale già presente! Elaborazione anno ".$anno."!"
    ]);
    die;
}


$query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'";
$ente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"enti_gestiti")["Denominazione"];

if($ente == null){
    //echo "<script>location.href = 'filtri_conto_giudiziale.php?c={$c}&a={$a}&error=1&msg=Ente {$c} non presente a Database!'</script>";
    //header("Location: filtri_conto_giudiziale.php?c={$c}&a={$a}&error=1&msg=Ente {$c} non presente a Database!");
    echo json_encode([
        "error" => 1,
        "msg" => "Ente ".$c." non presente a Database!"
    ]);
    die;
}


$query = "SELECT GEST.Denominazione,GEST.Comune,GEST.File_Firma FROM enti_gestiti AS EG LEFT JOIN gestore AS GEST ON GEST.ID=EG.Gestore_ID WHERE EG.CC = '".$c."'";
$resultGest = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"gestore");

//var_dump($resultGest["File_Firma"]);die;

if($resultGest["File_Firma"] == null){
    //echo "<script>location.href = 'filtri_conto_giudiziale.php?c={$c}&a={$a}&error=2&msg=Firma del gestore assente, inserirla nei parametri!'</script>";
    //header("Location: filtri_conto_giudiziale.php?c={$c}&a={$a}&error=2&msg=Firma del gestore assente, inserirla nei parametri!");
    echo json_encode([
        "error" => 1,
        "msg" => "Firma del gestore assente, inserirla nei parametri!"
    ]);
    die;
}


$gestore = $resultGest["Denominazione"];
$com_gestore = $resultGest["Comune"];
$completePathFile = SUPER_ROOT."/archivio/firme/".$c."/".$resultGest["File_Firma"];

//echo $completePathFile;die;

if($gestore == null)
    $gestore = $ente;

$query = "SELECT importo, data_versamento, canale FROM riversamenti WHERE CC = '".$c."' AND year = '".$anno."' ORDER BY data_versamento";
$resultRiv = $cls_db->getResults($cls_db->ExecuteQuery($query));

//var_dump($resultRiv);die;
//$query = "SELECT P.Pro_Sigla FROM comuni_lista AS C JOIN province_lista AS P ON C.Com_Codice_Provincia = P.Pro_Codice AND C.Com_Codice_Catastale = '".$c."'";
//$sigla = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["Pro_Sigla"];

/*$nameFILE = "Conto_Giudiziale_[".$c."]_".date("d-m-Y").".pdf";
$pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/giudiziale/".$c);
$pathFILE .= "/".$nameFILE;
$pathWEBFILE = SUPER_WEB_ROOT."/archivio/giudiziale/".$c."/".$nameFILE;*/
/**
 * Procedure_Type_Id = 8 è il conto giudiziale che è però ancora da inserire, quindi quando devo caricarla controllare la tabella  "procedure_types"
 * ed aggiungerci il tipo "Conto giudiziale", se poi l'id è diverso non sarà 8 ma ...
 */
if($type == "final") {
    $q_exist = "select Id from procedures where CC = '$c' and Anno_Riferimento=$anno";
    $exist_Id = $cls_db->getResults($cls_db->ExecuteQuery($q_exist));
    //$exist_Id = $exist_Id[0]["Id"];
    if (isset($exist_Id[0]["Id"]))
    {

        $procedure_id = $exist_Id[0]["Id"];
    }
    else
    {
        $a_dbParams = array(
            'table' => 'procedures',
            'fields' => array(
                array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => 8),
                array('name' => 'Datetime', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('name' => 'Procedure_Date', 'type' => 'date', 'value' => $cls_help->getVar("data_stampa")),
                array('name' => 'CC', 'type' => 'string', 'value' => $c),
                array('name' => 'Anno_Riferimento', 'type' => 'int', 'value' => $anno),
                array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr']),
                array('name' => 'Description', 'type' => 'string', 'value' => $anno),
            )
        );
        $procedure_id = $cls_db->DbSave($a_dbParams);
    }



    $pathFILE = $cls_utils->crea_dir(PROCEDURE . $procedure_id);
    $pathWEBFILE = PROCEDURE_WEB.$procedure_id;
    $pdfFileName = "Conto_Giudiziale_".$procedure_id."_" . $c . "_" . date("H-i-s") . ".pdf";
    $msg = "Stampa definitiva effettuata!";
}
else{
    $pathFILE = $cls_utils->crea_dir(PROCEDURE."TEMP");
    $pathWEBFILE = PROCEDURE_WEB."TEMP";
    $pdfFileName = "Conto_Giudiziale_Temp_" . $c . "_" . date("d-m-Y_H-i-s") . ".pdf";
    $msg = "Stampa provvisoria effettuata!";
}


$pathFILE = $pathFILE ."/". $pdfFileName;
//$pdf->Output($pdfPath, "F");
$pathWEBFILE = $pathWEBFILE."/".$pdfFileName;

/** NUOVO INSERITO **/

$queryDaAss = "SELECT pagamento.ID AS PAGID, tributo.Info_Cartella AS INFOCARTELLA FROM pagamento ";
$queryDaAss.= "JOIN partita_tributi ON pagamento.Partita_ID = partita_tributi.ID ";
$queryDaAss.= "JOIN tributo ON tributo.Partita_ID = partita_tributi.ID ";
$queryDaAss.= "WHERE pagamento.CC = \"".$c."\" AND pagamento.Atto_ID = 0 AND pagamento.Tipo_Atto !='Precedenti' ";

$query = "SELECT U.Ditta, U.Nome, U.Cognome ,pagamento.ID AS PAGID, tributo.Info_Cartella AS INFOCARTELLA FROM pagamento ";
$query.= "JOIN partita_tributi ON pagamento.Partita_ID = partita_tributi.ID ";
$query.= "JOIN tributo ON tributo.Partita_ID = partita_tributi.ID ";
$query.= "LEFT JOIN utente AS U ON U.ID = partita_tributi.Utente_ID ";
$query.= "WHERE pagamento.CC = \"".$c."\" AND pagamento.Atto_ID != 0 AND pagamento.Conto_Terzi != 'Y' ";


$queryDataPag = "AND pagamento.Data_Pagamento >= '" . $anno . "-01-01' AND ";
$queryDataPag.= "pagamento.Data_Pagamento <= '" . $anno . "-12-31' ";
$query.= $queryDataPag;
$queryDaAss.= $queryDataPag;

$query.= "GROUP BY pagamento.ID ORDER BY partita_tributi.Split_Parameters_ID, pagamento.Data_Pagamento, pagamento.Atto_ID, pagamento.Rata ";
$queryDaAss.= "GROUP BY pagamento.ID ORDER BY partita_tributi.Split_Parameters_ID, pagamento.Data_Pagamento, pagamento.Atto_ID, pagamento.Rata ";

$sceltaDataPagamento = "Da " . "01/01/" . $anno . " a " . "31/12/" . $anno ;

$a_Pagamenti = $cls_db->getResults( $cls_db->SelectQuery( $query ) );
$arrayDaAssPagamenti = $cls_db->getResults( $cls_db->SelectQuery( $queryDaAss ) );

$num_totale_pagamenti = count($a_Pagamenti) + count($arrayDaAssPagamenti);

$arrayPagamenti = array_merge($a_Pagamenti,$arrayDaAssPagamenti);

$fontPrimo = 8.5;
$fontSecondo = 8;
$tot_gen_pagato = 0;

$cont_pag_result = 0;
$cont_pagdaass_result = 0;
$parzPagato = 0;
$totalePagato = 0;

$indexProgress = 0;
$countPagamenti = count($arrayPagamenti);
$maxCountProgress = $countPagamenti*2;

$countTotGen = $countTotFebr = $countTotMar = $countTotApr = $countTotMagg = $countTotGiugn = $countTotLugl = $countTotAgost = $countTotSett = $countTotOtt = $countTotNov = $countTotDic = 0;
$TotGen = $TotFebr = $TotMar = $TotApr = $TotMagg = $TotGiugn = $TotLugl = $TotAgost = $TotSett = $TotOtt = $TotNov = $TotDic = 0;
$TotalAllMonth = 0;

if($countPagamenti == 0){
	
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();
	
    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
	
    die;
}

for( $l=0; $l < $countPagamenti; $l++,$indexProgress++ )//FOR ATTI
{
    ini_set('max_execution_time', 0);

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($indexProgress*100)/$maxCountProgress ,2);
    session_write_close();

    $query = "SELECT * FROM pagamento WHERE ID = '".$arrayPagamenti[$l]['PAGID']."' AND CC = '".$c."'";
    $myPagamento = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pagamento");

    if($myPagamento != null) {
        $numberMonth = isset($myPagamento["Data_Pagamento"])?(int)explode("-",$myPagamento["Data_Pagamento"])[1]:null;

        switch ($numberMonth){
            case 1:
                $countTotGen++;
                $TotGen+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 2:
                $countTotFebr++;
                $TotFebr+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 3:
                $countTotMar++;
                $TotMar+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 4:
                $countTotApr++;
                $TotApr+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 5:
                $countTotMagg++;
                $TotMagg+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 6:
                $countTotGiugn++;
                $TotGiugn+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 7:
                $countTotLugl++;
                $TotLugl+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 8:
                $countTotAgost++;
                $TotAgost+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 9:
                $countTotSett++;
                $TotSett+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 10:
                $countTotOtt++;
                $TotOtt+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 11:
                $countTotNov++;
                $TotNov+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            case 12:
                $countTotDic++;
                $TotDic+=$myPagamento["Importo"];
                $TotalAllMonth+=$myPagamento["Importo"];
                break;
            default: break;
        }
    }
}

if($countTotGen == 0){
    $countTotGen = "";
    $TotGen = "";
}
else $TotGen = number_format($TotGen,2,",",".");
if($countTotFebr == 0){
    $countTotFebr = "";
    $TotFebr = "";
}
else $TotFebr = number_format($TotFebr,2,",",".");
if($countTotMar == 0){
    $countTotMar = "";
    $TotMar = "";
}
else $TotMar = number_format($TotMar,2,",",".");
if($countTotApr == 0){
    $countTotApr = "";
    $TotApr = "";
}
else $TotApr = number_format($TotApr,2,",",".");
if($countTotMagg == 0){
    $countTotMagg = "";
    $TotMagg = "";
}
else $TotMagg = number_format($TotMagg,2,",",".");
if($countTotGiugn == 0){
    $countTotGiugn = "";
    $TotGiugn = "";
}
else $TotGiugn = number_format($TotGiugn,2,",",".");
if($countTotLugl == 0){
    $countTotLugl = "";
    $TotLugl = "";
}
else $TotLugl = number_format($TotLugl,2,",",".");
if($countTotAgost == 0){
    $countTotAgost = "";
    $TotAgost = "";
}
else $TotAgost = number_format($TotAgost,2,",",".");
if($countTotSett == 0){
    $countTotSett = "";
    $TotSett = "";
}
else $TotSett = number_format($TotSett,2,",",".");
if($countTotOtt == 0){
    $countTotOtt = "";
    $TotOtt = "";
}
else $TotOtt = number_format($TotOtt,2,",",".");
if($countTotNov == 0){
    $countTotNov = "";
    $TotNov = "";
}
else $TotNov = number_format($TotNov,2,",",".");
if($countTotDic == 0){
    $countTotDic = "";
    $TotDic = "";
}
else $TotDic = number_format($TotDic,2,",",".");

/** FINE NUOVO INSERITO **/

$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("Conto giudiziale");

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);

$pdf->setTimeFooter(false);

$pdf->AddPage("P");
$pdf->SetFont('Times','',12);



$totWidth = $pdf->getPageWidth();
$margin = $pdf->getMargins();

$totWidthNomargin = $totWidth - ($margin["left"] + $margin["right"]);

$comune = "";
if(substr($c,0,1)=="U")
    $comune.= "Unione di comuni di ";
else if(substr($c,0,1)=="P")
    $comune .= "Provincia di ";
else $comune .= "Comune di ";
$comune.= $ente;

$pdf->MultiCell($totWidthNomargin,8,"ENTE: ".strtoupper($comune),0,"L",false,1);
$pdf->MultiCell($totWidthNomargin,8,"GESTIONE: RISCOSSIONE COATTIVA",0,"L",false,1);

/*$complex_cell_border = array(
    'T' => array('width' => 1, 'color' => array(0,255,0), 'dash' => 4, 'cap' => 'butt'),
    'R' => array('width' => 2, 'color' => array(255,0,255), 'dash' => '1,3', 'cap' => 'round'),
    'B' => array('width' => 3, 'color' => array(0,0,255), 'dash' => 0, 'cap' => 'square'),
    'L' => array('width' => 4, 'color' => array(255,0,255), 'dash' => '3,1,0.5,2', 'cap' => 'butt'),
);*/
$pdf->SetY($pdf->GetY() + 25);
$complex_cell_border = array("TRBL" => array('width' => 0.1, 'dash' => 0, 'phase' => 0, 'color' => array(0, 0, 0)));

$str = "CONTO DELLA GESTIONE DELL’AGENTE CONTABILE: ".strtoupper($gestore)."\nESERCIZIO: ".$anno;

$pdf->MultiCell($totWidthNomargin,"",$str,$complex_cell_border,"L",false,1);

$pdf->SetY($pdf->GetY() + 25);
$pdf->SetFont('Times','B',12);
$pdf->MultiCell($totWidthNomargin,"","Modello n° 21",0,"C",false,1);
$pdf->SetFont('Times','',12);
$pdf->MultiCell($totWidthNomargin,"","per province, comuni, comunità montane,\nunioni di comuni e città metropolitane",0,"C",false,1);

if($type == "temp")
    $pdf->temporaryPrinting();

$pdf->AddPage("L");
if($type == "temp")
    $pdf->temporaryPrinting();

$totWidth = $pdf->getPageWidth();
$margin = $pdf->getMargins();

$totWidthNomargin = $totWidth - ($margin["left"] + $margin["right"]);

//$pdf->MultiCell($totWidthNomargin,"","Ente: ".$ente." - Gestione: ".$gestore,0,"C",false,1);
$pdf->SetFont('Times','B',12);
$pdf->MultiCell($totWidthNomargin,"","CONTO DELLA GESTIONE DELL’AGENTE CONTABILE: ".strtoupper($gestore)." - ".$anno,0,"C",false,1);
$pdf->SetFont('Times','',12);

$gennaio = '<tr>
            <td style="border: 1px solid black;">1</td>
            <td style="border: 1px solid black;">GENNAIO</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotGen.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotGen.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
            <td rowspan="12" style="border: 1px solid black;"></td>
        </tr>';

$febbraio = '<tr>
            <td style="border: 1px solid black;">2</td>
            <td style="border: 1px solid black;">FEBBRAIO</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotFebr.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotFebr.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$marzo = '<tr>
            <td style="border: 1px solid black;">3</td>
            <td style="border: 1px solid black;">MARZO</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotMar.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotMar.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$aprile = '<tr>
            <td style="border: 1px solid black;">4</td>
            <td style="border: 1px solid black;">APRILE</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotApr.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotApr.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$maggio = '<tr>
            <td style="border: 1px solid black;">5</td>
            <td style="border: 1px solid black;">MAGGIO</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotMagg.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotMagg.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$giugno = '<tr>
            <td style="border: 1px solid black;">6</td>
            <td style="border: 1px solid black;">GIUGNO</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotGiugn.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotGiugn.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$luglio = '<tr>
            <td style="border: 1px solid black;">7</td>
            <td style="border: 1px solid black;">LUGLIO</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotLugl.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotLugl.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$agosto = '<tr>
            <td style="border: 1px solid black;">8</td>
            <td style="border: 1px solid black;">AGOSTO</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotAgost.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotAgost.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$settembre = '<tr>
            <td style="border: 1px solid black;">9</td>
            <td style="border: 1px solid black;">SETTEMBRE</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotSett.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotSett.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$ottobre = '<tr>
            <td style="border: 1px solid black;">10</td>
            <td style="border: 1px solid black;">OTTOBRE</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotOtt.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotOtt.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$novembre = '<tr>
            <td style="border: 1px solid black;">11</td>
            <td style="border: 1px solid black;">NOVEMBRE</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotNov.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotNov.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$dicembre = '<tr>
            <td style="border: 1px solid black;">12</td>
            <td style="border: 1px solid black;">DICEMBRE</td>
            <td style="border: 1px solid black;text-align: center;">'.$countTotDic.'</td>
            <td style="border: 1px solid black;text-align: right;">'.$TotDic.'</td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';

$totale = 0;

$countGen = $countFebbr = $countMar = $countApr = $countMagg = $countGiugn = $countLugl = $countAgost = $countSett = $countOtt = $countNov = $countDic = 0;
$countGenPrev = $countFebbrPrev = $countMarPrev = $countAprPrev = $countMaggPrev = $countGiugnPrev = $countLuglPrev = $countAgostPrev = $countSettPrev = $countOttPrev = $countNovPrev = $countDicPrev = 0;
$countRivv = count($resultRiv);

for($i = 0; $i < $countRivv; $i++){

    $month = (int)explode("-",$resultRiv[$i]["data_versamento"])[1];
    switch($month){
        case 1: $countGenPrev++; break;
        case 2: $countFebbrPrev++; break;
        case 3: $countMarPrev++; break;
        case 4: $countAprPrev++; break;
        case 5: $countMaggPrev++; break;
        case 6: $countGiugnPrev++; break;
        case 7: $countLuglPrev++; break;
        case 8: $countAgostPrev++; break;
        case 9: $countSettPrev++; break;
        case 10: $countOttPrev++; break;
        case 11: $countNovPrev++; break;
        case 12: $countDicPrev++; break;
        default:
            break;
    }
}

//echo $countRivv;die;
for($i = 0; $i < $countRivv; $i++){

    $month = (int)explode("-",$resultRiv[$i]["data_versamento"])[1];
    //echo $month;
    switch($month){
        case 1:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countGen == 0){
                if($countGenPrev > 1){
                    $gennaio = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">1</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;">GENNAIO</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">'.$countTotGen.'</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">'.$TotGen.'</td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                                <td rowspan="12" style="border: 1px solid black;"></td>
                            </tr>';
                }
                else{
                    $gennaio = '<tr>
                                <td style="border: 1px solid black;">1</td>
                                <td style="border: 1px solid black;">GENNAIO</td>
                                <td style="border: 1px solid black;text-align: center;">'.$countTotGen.'</td>
                                <td style="border: 1px solid black;text-align: right;">'.$TotGen.'</td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                                <td rowspan="12" style="border: 1px solid black;"></td>
                            </tr>';
                }

            }
            else{
                $gennaio .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countGen++;

            break;

        case 2:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countFebbr == 0){
                if($countFebbrPrev > 1) {
                    $febbraio = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">2</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">FEBBARIO</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotFebr . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotFebr . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $febbraio = '<tr>
                                <td style="border: 1px solid black;">2</td>
                                <td style="border: 1px solid black;">FEBBARIO</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotFebr . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotFebr . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $febbraio .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countFebbr++;

            break;

        case 3:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countMar == 0){
                if($countMarPrev > 1) {
                    $marzo = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">3</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">MARZO</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotMar . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotMar . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $marzo = '<tr>
                                <td style="border: 1px solid black;">3</td>
                                <td style="border: 1px solid black;">MARZO</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotMar . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotMar . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $marzo .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countMar++;

            break;

        case 4:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countApr == 0){
                if($countAprPrev > 1) {
                    $aprile = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">4</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">APRILE</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotApr . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotApr . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $aprile = '<tr>
                                <td style="border: 1px solid black;">4</td>
                                <td style="border: 1px solid black;">APRILE</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotApr . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotApr . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $aprile .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countApr++;

            break;

        case 5:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countMagg == 0){
                if($countMaggPrev > 1) {
                    $maggio = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">5</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">MAGGIO</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotMagg . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotMagg . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $maggio = '<tr>
                                <td style="border: 1px solid black;">5</td>
                                <td style="border: 1px solid black;">MAGGIO</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotMagg . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotMagg . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $maggio .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countMagg++;

            break;

        case 6:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countGiugn == 0){
                if($countGiugnPrev > 1) {
                    $giugno = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">6</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">GIUGNO</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotGiugn . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotGiugn . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $giugno = '<tr>
                                <td style="border: 1px solid black;">6</td>
                                <td style="border: 1px solid black;">GIUGNO</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotGiugn . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotGiugn . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $giugno .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countGiugn++;

            break;

        case 7:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countLugl == 0){
                if($countLuglPrev > 1) {
                    $luglio = '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">7</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;">LUGLIO</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">' . $countTotLugl . '</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">' . $TotLugl . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $luglio = '<tr>
                                <td style="border: 1px solid black;">7</td>
                                <td style="border: 1px solid black;">LUGLIO</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotLugl . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotLugl . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $luglio .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countLugl++;

            break;

        case 8:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countAgost == 0){
                if($countAgostPrev > 1) {
                    $agosto = '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">8</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;">AGOSTO</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">' . $countTotAgost . '</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">' . $TotAgost . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $agosto = '<tr>
                                <td style="border: 1px solid black;">8</td>
                                <td style="border: 1px solid black;">AGOSTO</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotAgost . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotAgost . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $agosto .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countAgost++;

            break;

        case 9:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countSett == 0){
                if($countSettPrev > 1) {
                    $settembre = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">9</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">SETTEMBRE</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotSett . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotSett . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $settembre = '<tr>
                                <td style="border: 1px solid black;">9</td>
                                <td style="border: 1px solid black;">SETTEMBRE</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotSett . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotSett . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $settembre .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countSett++;

            break;

        case 10:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countOtt == 0){
                if($countOttPrev > 1) {
                    $ottobre = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">10</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">OTTOBRE</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotOtt . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotOtt . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $ottobre = '<tr>
                                <td style="border: 1px solid black;">10</td>
                                <td style="border: 1px solid black;">OTTOBRE</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotOtt . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotOtt . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $ottobre .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countOtt++;

            break;

        case 11:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countNov == 0){
                if($countNovPrev > 1) {
                    $novembre = '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">11</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;">NOVEMBRE</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">' . $countTotNov . '</td>
                                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">' . $TotNov . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $novembre = '<tr>
                                <td style="border: 1px solid black;">11</td>
                                <td style="border: 1px solid black;">NOVEMBRE</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotNov . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotNov . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{
                $novembre .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
            }
            $countNov++;

            break;

        case 12:
            $totale += $resultRiv[$i]["importo"];

            $dataIt = $cls_date->Get_DateNewFormat($resultRiv[$i]["data_versamento"]);
            //importo, data_versamento, canale
            if($countDic == 0){
                if($countDicPrev > 1) {
                    $dicembre = '<tr>
                                <td style="border-left: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;">12</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;">DICEMBRE</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: center;">' . $countTotDic . '</td>
                                <td style="border-top: 1px solid black;border-right: 1px solid black;text-align: right;">' . $TotDic . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
                else{
                    $dicembre = '<tr>
                                <td style="border: 1px solid black;">12</td>
                                <td style="border: 1px solid black;">DICEMBRE</td>
                                <td style="border: 1px solid black;text-align: center;">' . $countTotDic . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . $TotDic . '</td>
                                <td style="border: 1px solid black;">' . $dataIt . ' - ' . $resultRiv[$i]["canale"] . '</td>
                                <td style="border: 1px solid black;text-align: right;">' . number_format($resultRiv[$i]["importo"], 2, ",", ".") . '</td>
                            </tr>';
                }
            }
            else{

                if($i == $countRivv-1){
                    $dicembre .= '<tr>
                                <td style="border-left: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-bottom: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-bottom: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;border-bottom: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
                }
                else{
                    $dicembre .= '<tr>
                                <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;"></td>
                                <td style="border-right: 1px solid black;text-align: right;"></td>
                                <td style="border: 1px solid black;">'.$dataIt.' - '.$resultRiv[$i]["canale"].'</td>
                                <td style="border: 1px solid black;text-align: right;">'.number_format($resultRiv[$i]["importo"],2,",",".").'</td>
                            </tr>';
                }
            }
            $countDic++;

            break;

        default: break;
    }

}

//echo "fuori";die;

$html = '
<table style="border-color:gray;">
    <thead>
        <tr>
            <th rowspan="2" style="border: 1px solid black;text-align: center;">N. ORD.</th>
            <td rowspan="2" style="border: 1px solid black;text-align: center;">PERIODO E OGGETTO DELLA RISCOSSIONE</td>
            <td colspan="2" style="border: 1px solid black;text-align: center;">ESTREMI	RISCOSSIONE</td>
            <td colspan="2" style="border: 1px solid black;text-align: center;">VERSAMENTO IN TESORERIA</td>
            <td rowspan="2" style="border: 1px solid black;text-align: center;">NOTE</td>
        </tr>
        <tr>
            <td style="border: 1px solid black;text-align: center;">RICEV. NN.</td>
            <td style="border: 1px solid black;text-align: center;">IMPORTO</td>
            <td style="border: 1px solid black;text-align: center;">QUIET. NN.</td>
            <td style="border: 1px solid black;text-align: center;">IMPORTO</td>
        </tr>
    </thead>
    <tbody>';
if($countGen > 0) $countGen--;
if($countFebbr > 0) $countFebbr--;
if($countMar > 0) $countMar--;
if($countApr > 0) $countApr--;
if($countMagg > 0) $countMagg--;
if($countGiugn > 0) $countGiugn--;
if($countLugl > 0) $countLugl--;
if($countAgost > 0) $countAgost--;
if($countSett > 0) $countSett--;
if($countOtt > 0) $countOtt--;
if($countNov > 0) $countNov--;
if($countDic > 0) $countDic--;

$rousp = 12 + $countGen + $countFebbr + $countMar + $countApr + $countMagg + $countGiugn + $countLugl + $countAgost + $countSett + $countOtt + $countNov + $countDic;
$gennaio = str_replace('rowspan="12"','rowspan="'.$rousp.'"',$gennaio);

$html .= $gennaio.$febbraio.$marzo.$aprile.$maggio.$giugno.$luglio.$agosto.$settembre.$ottobre.$novembre.$dicembre;

$html .='         
        <tr>
            <td colspan="3" style="text-align: right;">TOTALE</td>
            <td style="border: 1px solid black;text-align: right;">'.number_format($TotalAllMonth,2,",",".").'</td>
            <td style="text-align: right;">TOTALE</td>
            <td style="border: 1px solid black;text-align: right;">'.number_format($totale,2,",",".").'</td>
            <td></td>
        </tr>
    
    </tbody>
</table>';

//echo $html; die;

//echo $html;die;
$pdf->writeHTML($html, true, false, false, false, '');


/** INIZIO MODIFICHE **/

$cls_date->changeFormat("DB");

if($countPagamenti > 0) {
    $pdf->AddPage("L");

    if ($type == "temp")
        $pdf->temporaryPrinting();
}

$lastNumberPage = $pdf->getNumPages();

$pdf->SetMargins(10, 10, 10);


$styleDash = array('dash' => '6,6');
$styleRetta = array('dash' => '0');

//$pdf->AddPage("L"); //AddMyPage(1, 'L');
$pdf->SetFont('Arial', 'B', $fontPrimo);

$dim_pag = $pdf->getPageDimensions();
$larghezza_pag = $pdf->getPageWidth();  //  297
$altezza_pag = $pdf->getPageHeight();

$pdf->SetAutoPageBreak(false);
$pdf->Ln(5);

$cont_pag_result = 0;
$cont_pagdaass_result = 0;
$parzPagato = 0;
$totalePagato = 0;

$arrayPartite = array();
$changeParams = "";
$ctrl_linea = "no";

for( $l=0; $l < $countPagamenti; $l++,$indexProgress++ ){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($indexProgress*100)/$maxCountProgress ,2);
    session_write_close();

    $query = "SELECT * FROM pagamento WHERE ID = '".$arrayPagamenti[$l]['PAGID']."' AND CC = '".$c."'";
    $myPagamento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamento");
    //$myPagamento = new pagamento($arrayPagamenti[$l]['PAGID'], $c);
    $info_cartella = $arrayPagamenti[$l]['INFOCARTELLA'];
    //$data_infrazione = $arrayPagamenti[$l]['DATAINFRAZIONE'];
    $id_ingiunzione = "";
    $anno_ingiunzione = "";
    $id_pigno = "";
    $anno_pigno = "";
    if(strpos($myPagamento->Tipo_Atto,'Pignoramento')===false)
    {
        $query = "SELECT * FROM atto WHERE ID = ".$myPagamento->Atto_ID." AND CC = '".$myPagamento->CC."'";
        $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($myPagamento->Atto_ID, $myPagamento->CC);
        $query = "SELECT * FROM pignoramento_generale WHERE 1=2";
        $mioPigno = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");//new pignoramento(null, $myPagamento->CC);

        $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = '".$mioPigno->ID."' AND CC = '".$c."'";
        $mioPigno->Spese_Pignoramento = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));
        $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$mioPigno->ID."' AND Partita_ID = '".$mioPigno->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
        $mioPigno->Pagamento = $cls_db->getResults($cls_db->ExecuteQuery($query),"object");

        $id_ingiunzione = $myAtto->ID_Cronologico;
        $anno_ingiunzione = $myAtto->Anno_Cronologico;

    }
    else
    {
        $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$myPagamento->Atto_ID." AND CC = '".$myPagamento->CC."'";
        $mioPigno = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");//new pignoramento($myPagamento->Atto_ID, $myPagamento->CC);

        $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$mioPigno->ID." AND CC = '".$c."'";
        $mioPigno->Spese_Pignoramento = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));
        $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$mioPigno->ID."' AND Partita_ID = '".$mioPigno->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
        $mioPigno->Pagamento = $cls_db->getResults($cls_db->ExecuteQuery($query),"object");

        $id_pigno = $mioPigno->ID_Cronologico;
        $anno_pigno = $mioPigno->Anno_Cronologico;
        $query = "SELECT * FROM atto WHERE ID = ".$mioPigno->Atto_ID." AND CC = '".$myPagamento->CC."'";
        $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($mioPigno->Atto_ID, $myPagamento->CC);
        $id_ingiunzione = $myAtto->ID_Cronologico;
        $anno_ingiunzione = $myAtto->Anno_Cronologico;
    }

    $myPartita = $cls_stampe->getDataPartita($myPagamento->Partita_ID,$c);// $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");//new partita($myPagamento->Partita_ID, $c);
    //var_dump($myPartita);
    if (!IsInArray($arrayPartite, $myPartita->ID))
        $arrayPartite[] = $myPartita->ID;

    if ($myPartita->Utente->Cognome == "")
    {
        $pagante = $myPartita->Utente->Ditta;
        $CF_PI = $myPartita->Utente->Partita_Iva;
    }
    else
    {
        $pagante = $myPartita->Utente->Cognome . " " . $myPartita->Utente->Nome;
        $CF_PI = $myPartita->Utente->Codice_Fiscale;
    }

    if($myPagamento->Pagante != ""){
        $pagante .= " (".$myPagamento->Pagante.")";
    }

    $numeroRata = $myPagamento->Rata;
    if ($numeroRata == 0) $numeroRata = "UNICA";

    if ($myPagamento->Quietanza != "")
    {
        if ($myPagamento->Bollettario != "") $quietanza = $myPagamento->Quietanza . "-" . $myPagamento->Bollettario;
        else $quietanza = $myPagamento->Quietanza;
    }
    else $quietanza = $myPagamento->Bollettario;

    $stato_pagamento = substr($myPagamento->Tipo_Pagamento, 0, 3);
    if($myPagamento->Telematico =="SI" || $myPagamento->Telematico =="S" || $myPagamento->Telematico =="Y"){
        $stato_pagamento.= " TELEM";
    }

    $a_params = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersFromIdQuery($myPartita->Split_Parameters_ID) ) );
    if(!$a_params['id']>0)
        $a_params = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersQuery($c) ) );

    $a_splitParams = $cls_split->getLineByPriority($a_params);

    if($changeParams!=$a_params['id']) {
        if($changeParams>0){

            $y2_vert = $pdf->getY();
            $margine = $pdf->getMargins();
            $x_marg = $margine['left'];
            for($k=0 ; $k < count($a_width)-1 ; $k++ )
            {
                $x_marg += $a_width[$k];
                $pdf->Line( $x_marg , $y1_vert , $x_marg , $y2_vert , $styleDash );
            }
            //crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

            /**         $a_splitPartial CHE VALORE DOVREBBE AVERE?              **/
            //PARZIALI
            $a_end = endArray($parzPagato, $a_splitPartial, $a_splitParams);

            $pdf->SetFont('Arial', 'B', $fontSecondo);
            $pdf->setCellPaddings(2,1,2,0);
            $y = $pdf->setRow($a_end[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
            //$y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);

            if(count($a_end[2])==count($a_width_end)){
                $padding = 0;
                $line = "no";
            }
            else{
                $padding = 1;
                $line = "down";
            }

            $pdf->setCellPaddings(2,0,2,$padding);
            $y = $pdf->setRow($a_end[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
            //$y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);

            if(count($a_end[2])==count($a_width_end)){
                $pdf->setCellPaddings(2,0,2,1);
                $y = $pdf->setRow($a_end[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
            }

            /**                 QUI COSA CI VA? --> $a_splitTotal                    **/
            //TOTALI
            $a_final = finalArray($totalePagato, $a_splitTotal, $a_splitParams);

            $pdf->SetFont('Arial', 'B', $fontSecondo);
            $pdf->setCellPaddings(2,1,2,0);
            $y = $pdf->setRow($a_final[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
            //$y = crea_riga($pdf , $a_width_end, $a_final[0], "up" , $styleRetta, $a_align_end);

            if(count($a_final[2])==count($a_width_end)){
                $padding = 0;
                $line = "no";
            }
            else{
                $padding = 1;
                $line = "down";
            }

            $pdf->setCellPaddings(2,0,2,$padding);
            $y = $pdf->setRow($a_final[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
            //$y = crea_riga($pdf , $a_width_end, $a_final[1], $line , $styleRetta, $a_align_end);

            if(count($a_final[2])==count($a_width_end)){
                $pdf->setCellPaddings(2,0,2,1);
                $y = $pdf->setRow($a_final[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
                //$y = crea_riga($pdf , $a_width_end, $a_final[2], "down" , $styleRetta, $a_align_end);
            }

            $pdf->AddPage('L');
            $pdf->Ln(5);

            if($type == "temp")
                $pdf->temporaryPrinting();

            $ctrl_linea = "no";

        }

        $parzPagato = 0;
        $totalePagato = 0;

        for ($i = 0; $i < count($a_splitParams); $i++) {
            $a_splitTotal[$i] = 0;
            $a_splitPartial[$i] = 0;
        }

        $a_header = headerArray($a_splitParams);

        $pdf->SetFont('Arial', 'B', $fontPrimo);

        $pdf->setCellPaddings(2,1,2,0);
        $y1_vert = $pdf->setRow($a_header[0],"up",$styleRetta,$a_align,0,$a_width);
        //$y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

        if(count($a_header[2])==count($a_width)){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        $y1_vert = $pdf->setRow($a_header[1],$line,$styleRetta,$a_align,0,$a_width);
        //$y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

        if(count($a_header[2])==count($a_width)){
            $pdf->setCellPaddings(2,0,2,1);
            $y1_vert = $pdf->setRow($a_header[2],"down",$styleRetta,$a_align,0,$a_width);
            //$y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
        }
    }

    $changeParams = $a_params['id'];
    $tot_gen_pagato+= $myPagamento->Importo;
    $parzPagato+= $myPagamento->Importo;
    $totalePagato+= $myPagamento->Importo;

    $sum = 0;
    for($i=1;$i<=16;$i++){
        $key = "Split_Payment".$i;
        $sum+= $myPagamento->$key;
    }

    if($sum>0)
    {
        for($i=1;$i<=16;$i++){
            $key = "Split_Payment".$i;
            $a_splitRequest[$i] =  $myPagamento->$key;
        }

        for($i=0;$i<count($a_splitParams);$i++){
            if(isset($a_splitRequest[$a_splitParams[$i]['split_number']])){
                $a_splitPartial[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                $a_splitTotal[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
            }
        }
    }
    else{
        $a_splitRequest = $cls_stampe->splitPayment($myPartita,$myAtto,$mioPigno,$a_splitParams,$a_params['id'],$myPagamento->CC,$myPagamento);
        for($i=0;$i<count($a_splitParams);$i++){
            if(isset($a_splitRequest[$a_splitParams[$i]['split_number']])){
                $a_splitPartial[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
                $a_splitTotal[$i]+= $a_splitRequest[$a_splitParams[$i]['split_number']];
            }
        }
    }

    $accertOrig = $cls_stampe->estraiTitoloTributo($myPartita);
    $num_rate = "";
    if($myPagamento->Totale_Rate>0)
        $num_rate = "/".$myPagamento->Totale_Rate;

    $cls_date->changeFormat("IT",false);

    $a_value[0] = array($accertOrig,$id_ingiunzione,$id_pigno,"(".$myPartita->Utente->Comune_ID.") ".$pagante,$myPagamento->Modalita . " - " . $numeroRata.$num_rate,$myPartita->Comune_ID,number_format($myPagamento->Importo,2,",","."));
    $a_value[1] = array($myPartita->Anno_Riferimento,$anno_ingiunzione,$anno_pigno,strtoupper($CF_PI),$quietanza,$stato_pagamento,$cls_date->Get_DateNewFormat($myPagamento->Data_Pagamento,"DB"));
    $a_value[2] = array("","","","","","","");

    $a_value = valueArray($a_splitRequest,$a_value,$a_splitParams);

    $pdf->SetFont('Arial', '', $fontSecondo);

    $pdf->setCellPaddings(2,2,2,0);
    $y = $pdf->setRow($a_value[0],$ctrl_linea,$styleRetta,$a_align_value,0,$a_width);
    //$y = crea_riga($pdf , $a_width, $a_value[0], $ctrl_linea , $styleDash, $a_align_value);
    if($ctrl_linea == "no")	$ctrl_linea = "up";

    if(count($a_value[2])==count($a_width)){
        $padding = 0;
    }
    else{
        $padding = 2;
    }

    $pdf->setCellPaddings(2,0,2,$padding);
    $y = $pdf->setRow($a_value[1],"no",$styleDash,$a_align_value,0,$a_width);
    //$y = crea_riga($pdf , $a_width, $a_value[1], "no" , $styleDash, $a_align_value);

    if(count($a_value[2])==count($a_width)){
        $pdf->setCellPaddings(2,0,2,2);
        $y = $pdf->setRow($a_value[2],"no",$styleDash,$a_align_value,0,$a_width);
        //$y = crea_riga($pdf , $a_width, $a_value[2], "no" , $styleDash, $a_align_value);
    }

    if( $y > $altezza_pag - 40)
    {
        //$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );

        $y2_vert = $pdf->getY();
        $margine = $pdf->getMargins();
        $x = $margine['left'];
        for($k=0 ; $k < count($a_width)-1 ; $k++ )
        {
            $x += $a_width[$k];
            $pdf->Line( $x , $y1_vert , $x , $y2_vert , $styleDash );
        }
        //crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

        $a_end = endArray($parzPagato, $a_splitPartial, $a_splitParams);

        $pdf->SetFont('Arial', 'B', $fontSecondo);
        $pdf->setCellPaddings(2,1,2,0);
        $y = $pdf->setRow($a_end[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
        //$y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);

        if(count($a_end[2])==count($a_width_end)){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        $y = $pdf->setRow($a_end[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
        //$y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);

        if(count($a_end[2])==count($a_width_end)){
            $pdf->setCellPaddings(2,0,2,1);
            $y = $pdf->setRow($a_end[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
            //$y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
        }

        for($i=0;$i<count($a_splitParams);$i++) {
            $a_splitPartial[$i] = 0;
        }
        $parzPagato = 0;

        $pdf->AddPage( 'L');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', $fontPrimo);

        if($type == "temp")
            $pdf->temporaryPrinting();

        $pdf->setCellPaddings(2,1,2,0);
        $y1_vert = $pdf->setRow($a_header[0],"up",$styleRetta,$a_align,0,$a_width);
        //$y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

        if($a_header[2]>$minElements){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        $y1_vert = $pdf->setRow($a_header[1],$line,$styleRetta,$a_align,0,$a_width);
        //$y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

        if($a_header[2]>$minElements){
            $pdf->setCellPaddings(2,0,2,1);
            $y1_vert = $pdf->setRow($a_header[2],"down",$styleRetta,$a_align,0,$a_width);
            //$y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
        }

        $ctrl_linea = "no";
    }

    $cont_pag_result++;
}

if($num_totale_pagamenti>0){
    $y2_vert = $pdf->getY();
    $margine = $pdf->getMargins();
    $x = $margine['left'];
    for($k=0 ; $k < count($a_width)-1 ; $k++ )
    {
        $x += $a_width[$k];
        $pdf->Line( $x , $y1_vert , $x , $y2_vert , $styleDash );
    }
    //crea_linee ($pdf, $a_width, $y1_vert , $y2_vert, $styleDash);

    $a_end = endArray($parzPagato, $a_splitPartial, $a_splitParams);

    $pdf->SetFont('Arial', 'B', $fontSecondo);
    $pdf->setCellPaddings(2,1,2,0);
    //$y = crea_riga($pdf , $a_width_end, $a_end[0], "up" , $styleRetta, $a_align_end);
    $y = $pdf->setRow($a_end[0],"up",$styleRetta,$a_align_end,0,$a_width_end);

    if(count($a_end[2])==count($a_width_end)){
        $padding = 0;
        $line = "no";
    }
    else{
        $padding = 1;
        $line = "down";
    }

    $pdf->setCellPaddings(2,0,2,$padding);
    //$y = crea_riga($pdf , $a_width_end, $a_end[1], $line , $styleRetta, $a_align_end);
    $y = $pdf->setRow($a_end[1],$line,$styleRetta,$a_align_end,0,$a_width_end);

    if(count($a_end[2])==count($a_width_end)){
        $pdf->setCellPaddings(2,0,2,1);
        //$y = crea_riga($pdf , $a_width_end, $a_end[2], "down" , $styleRetta, $a_align_end);
        $y = $pdf->setRow($a_end[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
    }

    if( $y > $altezza_pag - 40)
    {
        $pdf->AddPage('L');
        $pdf->Ln(5);

        if($type == "temp")
            $pdf->temporaryPrinting();

        $pdf->SetFont('Arial', 'B', $fontPrimo);
        $pdf->setCellPaddings(2,1,2,0);
        $y1_vert = $pdf->setRow($a_header[0],"up",$styleRetta,$a_align,0,$a_width);
        //$y1_vert = crea_riga($pdf , $a_width, $a_header[0], "up" , $styleRetta, $a_align);

        if($a_header[2]>$minElements){
            $padding = 0;
            $line = "no";
        }
        else{
            $padding = 1;
            $line = "down";
        }

        $pdf->setCellPaddings(2,0,2,$padding);
        $y1_vert = $pdf->setRow($a_header[1],$line,$styleRetta,$a_align,0,$a_width);
        //$y1_vert = crea_riga($pdf , $a_width, $a_header[1], $line , $styleRetta, $a_align);

        if($a_header[2]>$minElements){
            $pdf->setCellPaddings(2,0,2,1);
            $y1_vert = $pdf->setRow($a_header[2],"down",$styleRetta,$a_align,0,$a_width);
            //$y1_vert = crea_riga($pdf , $a_width, $a_header[2], "down" , $styleRetta, $a_align);
        }

        $ctrl_linea = "no";
    }

    //TOTALI
    $a_final = finalArray($totalePagato, $a_splitTotal, $a_splitParams);

    $pdf->SetFont('Arial', 'B', $fontSecondo);
    $pdf->setCellPaddings(2,1,2,0);
    $y = $pdf->setRow($a_final[0],"up",$styleRetta,$a_align_end,0,$a_width_end);
    //$y = crea_riga($pdf , $a_width_end, $a_final[0], "up" , $styleRetta, $a_align_end);

    if(count($a_final[2])==count($a_width_end)){
        $padding = 0;
        $line = "no";
    }
    else{
        $padding = 1;
        $line = "down";
    }

    $pdf->setCellPaddings(2,0,2,$padding);
    $y = $pdf->setRow($a_final[1],$line,$styleRetta,$a_align_end,0,$a_width_end);
    //$y = crea_riga($pdf , $a_width_end, $a_final[1], $line , $styleRetta, $a_align_end);

    if(count($a_final[2])==count($a_width_end)){
        $pdf->setCellPaddings(2,0,2,1);
        $y = $pdf->setRow($a_final[2],"down",$styleRetta,$a_align_end,0,$a_width_end);
        //$y = crea_riga($pdf , $a_width_end, $a_final[2], "down" , $styleRetta, $a_align_end);
    }


    $pdf->setPrintHeader(false);
    $pdf->AddPage('L');
    $pdf->setPrintFooter(true);

    if($type == "temp")
        $pdf->temporaryPrinting();

    $pdf->setCellPaddings(2,0,2,1);
    $pdf->ln(10);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 0, "COMUNE DI ".strtoupper($ente) , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    $pdf->SetFont('Arial', '', 16);
    $pdf->Cell(0, 0, "ELENCO PAGAMENTI" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    $pdf->ln(20);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 0, "SELEZIONI" , 0, 1, 'L');

    /*$pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "TRIBUTI:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaTributo , 0, 1, "L");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "PARTITA:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaPartita , 0, 1, "L");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "ANNI GESTIONE ATTI ORIGINARI:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaAnni , 0, 1, "L");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "COGNOME/DITTA NOME:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $cognome_nome_ditta , 0, 1, "L");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "TIPO DI IMPORTAZIONE:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaTipoPagamento , 0, 1, "L");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "DATA DI INFRAZIONE:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataAvviso , 0, 1, "L");*/

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "DATA DI PAGAMENTO:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataPagamento , 0, 1, "L");

    /*$pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "DATA DI REGISTRAZIONE:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-70 , 5, $sceltaDataRegistrazione , 0, 1, "L");*/

    $pdf->ln(10);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 0, "RIEPILOGO" , 0, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (67, 0, "NUMERO PAGINE:", 0, 0, "L");
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell ( 23.5 , 5, $pdf->PageNo() + 1 - $lastNumberPage , 0, 1, "R");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (67, 0, "NUMERO PAGAMENTI:", 0, 0, "L");
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell ( 23.5 , 5, count($arrayPagamenti) , 0, 1, "R");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (67, 0, "NUMERO VERBALI:", 0, 0, "L");
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell ( 23.5 , 5, count($arrayPartite) , 0, 1, "R");

    /*
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (67, 0, "TOTALE DOVUTO:", 0, 0, "L");
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_dovuto,2)) . " Euro" , 0, 1, "R");
    */
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (67, 0, "TOTALE PAGATO:", 0, 0, "L");
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell ( 40 , 5, number_format($tot_gen_pagato,2,",","."), 0, 1, "R");

    $pdf->movePage($pdf->PageNo(), $lastNumberPage);
}

$pdf->AddPage("L");
$pdf->SetFont('Times','',12);

//echo "OK";die;

$pdf->MultiCell(($totWidthNomargin/10)*5,"",$com_gestore." li,: ".$cls_date->Get_DateNewFormat($cls_help->getVar("data_stampa")),0,"L",false,0);
$pdf->MultiCell(($totWidthNomargin/10)*5,"","L'AGENTE CONTABILE\n".strtoupper($gestore),0,"C",false,1);
$pdf->SetY($pdf->GetY() + 5);

$size = imageSize($completePathFile, 30, 20);
$xAdding = 15-$size[0]/2;
if($cls_help->getVar("num_registr") == "0" && $cls_help->getVar("num_page") == "0") $str = "";
else $str = "Il presente conto contiene n° ".count($arrayPagamenti)." registrazioni in ".($pdf->PageNo() - $lastNumberPage)." pagine.";
$pdf->MultiCell(($totWidthNomargin/10)*5,"",$str,0,"L",false,0);
$pdf->Image($completePathFile,$pdf->GetX()+55+$xAdding,$pdf->GetY(),$size[0],$size[1],'JPG',false,'',false,300,'', false, false, 0, "", false,false);

$pdf->SetY($pdf->GetY() + 25);
$pdf->MultiCell($totWidthNomargin,"","STAMPA GENERATA AUTOMATICAMENTE DAL SISTEMA INFORMATIVO",0,"L",false,1,'',$pdf->GetY());

$pdf->SetY($pdf->GetY() + 10);
$pdf->MultiCell(($totWidthNomargin/10)*5,"","VISTO DI REGOLARITA’",0,"L",false,0);
$pdf->MultiCell(($totWidthNomargin/10)*5,"","IL RESPONSABILE DEL SERVIZIO FINANZIARIO",0,"C",false,1);

$pdf->SetY($pdf->GetY() + 5);
$pdf->MultiCell(($totWidthNomargin/10)*5,"","__________________ li, ______________",0,"L",false,0);
$pdf->MultiCell(($totWidthNomargin/10)*5,"","______________________________________________",0,"C",false,1);

$pdf->SetY($pdf->GetY() + 5);
$pdf->MultiCell($totWidthNomargin,15,$cls_help->getVar("note"),$complex_cell_border,"L",false,1);

if($type == "temp")
    $pdf->temporaryPrinting();

$pdf->movePage($pdf->PageNo(), $lastNumberPage);

/** FINE MODIFICHE **/

$pdf->Output($pathFILE, 'F');

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $pathWEBFILE,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);

//echo "<script>finepdf('Elaborazione completata');</script>";

//echo "<script>location.href = 'filtri_conto_giudiziale.php?c={$c}&a={$a}&urlFile={$pathWEBFILE}&error=0&msg=File generato correttamente!'</script>";
//header("Location: filtri_conto_giudiziale.php?c={$c}&a={$a}&urlFile={$pathWEBFILE}&error=0&msg=File generato correttamente!");
