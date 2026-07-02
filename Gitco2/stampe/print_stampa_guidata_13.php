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
$filter["anno_cronologico_da"] = $annoCronologicoDa = $cls_help->getVar("anno_cronologico_da");
$filter["anno_cronologico_a"] = $annoCronologicoA = $cls_help->getVar("anno_cronologico_a");
$filter["id_cronologico_da"] = $idCronologicoDa = $cls_help->getVar("id_cronologico_da");
$filter["id_cronologico_a"] = $idCronologicoA = $cls_help->getVar("id_cronologico_a");
$filter["data_udienza_da"] = $dataUdienzaDa = $cls_help->getVar("data_udienza_da");
$filter["data_udienza_a"] = $dataUdienzaA = $cls_help->getVar("data_udienza_a");
$filter["autority_type"] = $autorityType = $cls_help->getVar("autority_type");
$filter["Court_Level"] = $courtLevel = $cls_help->getVar("Court_Level");


$query = "SELECT Description FROM authority_type WHERE Type = '".$filter["autority_type"]."'";
$auth_name = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"authority_type")["Description"];

$filter["autority_type"] = $auth_name;

$query = "SELECT PT.Comune_ID AS ID_Partita_Com, CONCAT('[',E.CC,']',' ', E.Denominazione) AS Ente, CONCAT(COALESCE(U.Cognome,''),COALESCE(U.Ditta,''),' ',COALESCE(U.Nome,'')) AS Nominativo,
            CONCAT(ATT.ID_Cronologico,'/',ATT.Anno_Cronologico) AS Cron_Ing, AT.Description AS Autorita, APT.Description AS Esito, A.Total, 
            U.Comune_ID AS Utente_Com_ID, IF(A.Court_Level IS NOT NULL, CONCAT(A.Court_Level,'° Grado'),'Dato assente') AS Grado
        
        FROM appeal AS A
        JOIN partita_tributi AS PT ON PT.ID = A.Partita_ID
        JOIN atto AS ATT ON ATT.ID = A.Act_ID
        LEFT JOIN enti_gestiti AS E ON E.CC = A.CC
        LEFT JOIN utente AS U ON PT.Utente_ID = U.ID
        LEFT JOIN ufficio_giudiziario AS UFF ON UFF.ID = A.Authority_ID
        LEFT JOIN authority_type AS AT ON AT.Type = UFF.Tipo
        LEFT JOIN appeal_proceedings_status AS APS ON APS.Appeal_ID = A.ID
        LEFT JOIN appeal_proceedings_type AS APT ON APT.ID = APS.Outcome
        LEFT JOIN appeal_court_hearing AS CH ON CH.ID = (SELECT CH2.ID FROM appeal_court_hearing AS CH2 WHERE CH2.Appeal_ID = A.ID ORDER BY CH2.Date DESC LIMIT 1)
        WHERE 1 = 1 ";

if($CC != "")
    $query .= " AND A.CC = '".$CC."' ";


if($annoCronologicoDa != ""){
    $query .= " AND ATT.Anno_Cronologico >= '".$annoCronologicoDa."' ";
}
if($annoCronologicoA != ""){
    $query .= " AND ATT.Anno_Cronologico <= '".$annoCronologicoA."' ";
}

if($idCronologicoDa != ""){
    $query .= " AND ATT.ID_Cronologico >= '".$idCronologicoDa."' ";
}
if($idCronologicoA != ""){
    $query .= " AND ATT.ID_Cronologico <= '".$idCronologicoA."' ";
}

if($dataUdienzaDa != ""){
    $query .= " AND CH.Date >= '".$dataUdienzaDa."' ";
}
if($dataUdienzaA != ""){
    $query .= " AND CH.Date <= '".$dataUdienzaA."' ";
}

if($courtLevel != ""){
    $query .= " AND A.Court_Level = '".$courtLevel."' ";
}

if($autorityType != ""){
    $query .= " AND AT.Type = '".$autorityType."' ";
}

$query .= " GROUP BY A.Act_ID ORDER BY A.CC";

//echo $query;die;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


$pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
$pdf->setHeaderTitle("");

$a_headerPage[0] = array("Grado","Ente","ID Partita","ID Utente","Nominativo","Cronologico","Autorità","Esito","Totale");
$dataExcel[] = array("<b>Grado</b>","<b>Ente</b>","<b>ID Partita</b>","<b>ID Utente</b>","<b>Nominativo</b>","<b>Cronologico</b>","<b>Autorità</b>","<b>Esito</b>","<b>Totale</b>");


$pdf->setArray($a_headerPage,"a_headerPage");
$percent = 100/11*($pdf->getPageWidth()-20)/100;
$a_width = array( $percent , $percent , $percent , $percent , $percent * 2 , $percent , $percent * 2 , $percent , $percent );
$a_align = array( "L" , "L" , "L" , "L" , "L" ,"L" ,"L" ,"L" ,"R");
$pdf->setArray($a_width,"a_width");
$pdf->setArray($a_align,"a_align");
$pdf->setHeaderPage();
$pdf->addLines();



//var_dump($result);
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

for($i=0; $i < $count; $i++){
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    if($fileType == 2) {

        $a_value[0] = array(
            $result[$i]["Grado"],
            $result[$i]["Ente"],
            $result[$i]["ID_Partita_Com"],
            $result[$i]["Utente_Com_ID"],
            $result[$i]["Nominativo"],
            $result[$i]["Cron_Ing"],
            $result[$i]["Autorita"],
            $result[$i]["Esito"],
            number_format($result[$i]["Total"],2,",",".")
        );



        $pdf->setRowPage($a_value);
        $pdf->addLines("dash");
    }
    else {
        $dataExcel[] = array($result[$i]["Grado"],$result[$i]["Ente"],$result[$i]["ID_Partita_Com"],$result[$i]["Utente_Com_ID"],$result[$i]["Nominativo"],$result[$i]["Cron_Ing"],$result[$i]["Autorita"],$result[$i]["Esito"],$result[$i]["Total"]);
    }
}

$nameFILE = "";
if($fileType == 2) {

    $a_mainPageParams = array("title" => strtoupper("STAMPA MULTIPLA"), "subtitle" => $title);
    $pdf->setMainPageParams($a_mainPageParams);

    //var_dump($filter);//die;
    $a_filters = $cls_elab->getFiltersDescription($filter);

    //var_dump($a_filters);//die;

    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO POSIZIONI";
    $recap[1]['value'] = count($result);
    $pdf->setMainPage($a_filters, $recap);

    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
    $nameFILE = "ReportStampeGuidate.pdf";
    $pathFILE .= "/".$nameFILE;

    //die;
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


