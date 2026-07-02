<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include(ROOT."/_parameter.php");

//include(INC."/headerAjax.php");
include_once CLS . "/cls_pdf.php";
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
$title = $cls_help->getVar("title");

$filter["CC"] = $CC = $cls_help->getVar("CC");
$fileType = $cls_help->getVar("file_type");
$filter["data_inserimento_da"] = $dataInserimentoDa = $cls_help->getVar("data_inserimento_da");
$filter["data_inserimento_a"] = $dataInserimentoA = $cls_help->getVar("data_inserimento_a");

//var_dump("<szdf//");die;

$query = "SELECT A.Info_Cartella, CONCAT(PT.Comune_ID,' ','(', PT.ID ,')') AS ID_Partita, CONCAT(U.Comune_ID,' ','(', U.ID ,')',' ',PT.Tipo) AS ID_Utente , CONCAT('[',E.CC,']',' ', E.Denominazione) AS Ente, CONCAT(U.Cognome,U.Ditta,' ',U.Nome) AS Nominativo ,CONCAT(U.Codice_Fiscale,U.Partita_Iva) AS CF_PI,E.CC,E.Denominazione, 
	  
            IF(I.Via_ID = 1,
               CONCAT(TC.Odonimo,', ',COALESCE(I.Civico,''),COALESCE(UPPER(I.Esponente),''),IF(COALESCE(I.Interno,'')='','','/'),COALESCE(I.Interno,''),' - ',TC.Cap,' ',UPPER(TC.Comune),' ',I.Provincia),
               CONCAT(T.Nome,', ',COALESCE(I.Civico,''),COALESCE(UPPER(I.Esponente),''),IF(COALESCE(I.Interno,'')='','','/'),COALESCE(I.Interno,''),' - ',T.Cap,' ',UPPER(T.Comune),' ',I.Provincia)
              ) AS Indirizzo_Utente
        
        FROM `ruolo` AS R
        JOIN partita_tributi AS PT ON PT.Ruolo_ID = R.ID
        LEFT JOIN atto AS A ON A.ID = (SELECT MAX(ID) FROM atto AS A1 WHERE A1.DocumentTypeId!=3 AND A1.DocumentTypeId!=11 AND A1.Partita_ID = PT.ID)
        LEFT JOIN enti_gestiti AS E ON E.CC = R.CC
        LEFT JOIN utente AS U ON PT.Utente_ID = U.ID
        LEFT JOIN indirizzo AS I ON I.Utente_ID = U.ID AND I.Tipo = 'res'
        LEFT JOIN toponimo AS T ON I.Via_ID = T.ID
        LEFT JOIN toponimi_cappati AS TC ON I.Via_Cap_ID = TC.ID
        WHERE R.Data_Inserimento IS NOT NULL ";

if($CC != "")
    $query .= " AND R.CC = '".$CC."' ";


if($dataInserimentoDa != "" && $dataInserimentoA != ""){
    $query .= " AND R.Data_Inserimento >= '".$dataInserimentoDa."' AND R.Data_Inserimento <= '".$dataInserimentoA."' ";
}
else if($dataInserimentoDa != ""){
    $query .= " AND R.Data_Inserimento >= '".$dataInserimentoDa."' ";
}
else if($dataInserimentoA != ""){
    $query .= " AND R.Data_Inserimento <= '".$dataInserimentoA."' ";
}

$query .= " ORDER BY R.CC";

//echo $query;die;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));
//var_dump($result);die;

$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->AddPage();
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Ente","ID Partita","Info cartella","ID Utente","Nominativo","CF/PI Utente","Indirizo Utente");
$dataExcel[] = array("<b>Ente</b>","<b>ID Partita</b>","<b>Info cartella</b>","<b>ID Utente</b>","<b>Nominativo</b>","<b>CF/PI Utente</b>","<b>Indirizo Utente</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/10*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent , $percent , $percent * 3, $percent , $percent , $percent , $percent * 2 );
$a_align = array( "L" , "L" , "L" , "L" ,"L" ,"L" ,"L");
$pdf->setArray($a_width,"a_width");
$pdf->setArray($a_align,"a_align");
$pdf->setHeaderPage();
$pdf->addLines();



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
$arrayNumPos = null;
if($count > 0) {
    /*$arrayNumPos[0]["CC"] = $result[0]["CC"];
    $arrayNumPos[0]["Denom"] = $result[0]["Denominazione"];
    $arrayNumPos[0]["Num"] = 0;*/

    $arrayNumPos[] = array("CC" => $result[0]["CC"], "Denom" => $result[0]["Denominazione"], "Num" => 0);
}
$contatorePos = 0;

for($i=0; $i < $count; $i++){
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    if($arrayNumPos[count($arrayNumPos)-1]["CC"] != $result[$i]["CC"] && $i != 0){
        $arrayNumPos[count($arrayNumPos)-1]["Num"] = $contatorePos;
        //if(count($arrayNumPos) == 1) $arrayNumPos[0]["Num"] =
        if($i < $count) {
            if($i == $count -1) array_push($arrayNumPos, array("CC" => $result[$i]["CC"], "Denom" => $result[$i]["Denominazione"], "Num" => 1));
            else array_push($arrayNumPos, array("CC" => $result[$i]["CC"], "Denom" => $result[$i]["Denominazione"], "Num" => 0));
        }
        $contatorePos = 0;
    }
    else if($arrayNumPos[count($arrayNumPos)-1]["CC"] == $result[$i]["CC"]  && $i == ($count - 1) && $i != 0){
        $arrayNumPos[count($arrayNumPos)-1]["Num"] = $contatorePos+1;
    }

    $contatorePos++;

    if($fileType == 2) {

        $a_value[0] = array(
            $result[$i]["Ente"],
            $result[$i]["ID_Partita"],
            $result[$i]["Info_Cartella"],
            $result[$i]["ID_Utente"],
            $result[$i]["Nominativo"],
            $result[$i]["CF_PI"],
            $result[$i]["Indirizzo_Utente"]
        );



        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["Ente"],$result[$i]["ID_Partita"],$result[$i]["Info_Cartella"],$result[$i]["ID_Utente"],$result[$i]["Nominativo"],$result[$i]["CF_PI"],$result[$i]["Indirizzo_Utente"]);
    }
}

$nameFILE = "";
if($fileType == 2) {

    
    $a_headerPage[0] = array("Codice Catastale","Denominazione","Numero posizioni");


    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/10*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent *2  , $percent * 2 , $percent * 2,$percent * 5 );
    $a_align = array( "L" , "L" , "L" , "L");
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
    $pdf->addLines();

    $countArrayNumPos = count($arrayNumPos);

    for($x=0; $x < $countArrayNumPos; $x++){
        $a_value[0] = array(
            $arrayNumPos[$x]["CC"],
            $arrayNumPos[$x]["Denom"],
            $arrayNumPos[$x]["Num"]
        );



        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }


    $a_mainPageParams = array("title" => strtoupper("STAMPA MULTIPLA"), "subtitle" => $title);
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_elab->getFiltersDescription($filter);

    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO POSIZIONI";
    $recap[1]['value'] = count($result);
    $pdf->setMainPage($a_filters, $recap);

    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidate.pdf";
    $pathFILE .= "/".$nameFILE;

    $pdf->Output($pathFILE, 'F');
}
else {
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidate.xlsx";
    $pathFILE .= "/".$nameFILE;
    if (count($dataExcel) > 1)
        SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($pathFILE);
}

//$pathWEBFILE = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

$file = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

if(session_status() == PHP_SESSION_NONE)session_start();

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);


