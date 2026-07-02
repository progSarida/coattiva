<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_merge.php";

$db = new cls_db();
$help = new cls_help();
$date = new cls_DateTimeI("DB",false);
$utils = new cls_Utils();
$cls_registry = new cls_registry();

$c = $help->getVar("c");
$a = $help->getVar("a");

$sgravio = $help->getVar("sgravio");
$annull = $help->getVar("annull");
$partita_da = $help->getVar("partita_da");
$partita_a = $help->getVar("partita_a");
$data_sg_da = $date->GetDateDB($help->getVar("sgravio_da"),"IT");
$data_sg_a = $date->GetDateDB($help->getVar("sgravio_a"),"IT");
$data_an_da = $date->GetDateDB($help->getVar("annull_da"),"IT");
$data_an_a = $date->GetDateDB($help->getVar("annull_a"),"IT");

$error = 0;
$msg = "File creati.";

$query = "SELECT PT.Utente_ID, PT.ID, U.Nome, U.Cognome, U.Ditta, PT.Comune_ID, PT.Printed FROM partita_tributi AS PT LEFT JOIN utente AS U ON PT.Utente_ID = U.ID WHERE PT.CC = '".$c."' ";

if($sgravio != "")
    $query .= " AND Flag_Sgravio = '".$sgravio."' ";
if($annull != "")
    $query .= " AND Flag_Annullamento = '".$annull."' ";

if($data_sg_da != "" && $data_sg_a != ""){
    $query .= " AND (Sgravio_Activation_Date >= '".$data_sg_da."' AND Sgravio_Activation_Date <= '".$data_sg_a."') ";
}else if($data_sg_da != ""){
    $query .= " AND Sgravio_Activation_Date >= '".$data_sg_da."' ";
}else if($data_sg_a != ""){
    $query .= " AND Sgravio_Activation_Date <= '".$data_sg_a."' ";
}

if($data_an_da != "" && $data_an_a != ""){
    $query .= " AND (Annullamento_Activation_Date >= '".$data_an_da."' AND Annullamento_Activation_Date <= '".$data_an_a."') ";
}else if($data_an_da != ""){
    $query .= " AND Annullamento_Activation_Date >= '".$data_an_da."' ";
}else if($data_an_a != ""){
    $query .= " AND Annullamento_Activation_Date <= '".$data_an_a."' ";
}

if($partita_da != "" && $partita_a != ""){
    $query .= " AND (PT.ID >= ".$partita_da." AND PT.ID <= ".$partita_a.") ";
}else if($partita_da != ""){
    $query .= " AND PT.ID >= ".$partita_da." ";
}else if($data_an_a != ""){
    $query .= " AND PT.ID <= ".$partita_a." ";
}

if($help->getVar("printed")!="all" && $help->getVar("printType") == "final")
    $query .= " AND Printed = '".$help->getVar("printed")."' ";

//echo $query."<br><br>";

$result = $db->getResults($db->ExecuteQuery($query));

$a_enteAdmin = $db->getArrayLine( $db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();

$data[] = array("<b>CC</b>","<b>UTENTE</b>","<b>Utente_ID</b>","<b>Comune_ID</b>","<b>Partita_ID</b>","<b>TIPO</b>","<b>TESTO</b>");
$allPDFFile = array();
$dataExcel = array();

$db->Start_Transaction();
$db->Begin_Transaction();

for($i=0; $i < count($result); $i++){

    $query = "SELECT * FROM sgravi_documenti AS SD WHERE SD.Partita_ID = ".$result[$i]["ID"];
    $resultDoc = $db->getResults($db->ExecuteQuery($query));

    $query = "SELECT * FROM `v_pignoramento` where Partita_ID = ".$result[$i]["ID"]." ORDER BY ID DESC LIMIT 1";
    $resultPignoAtto = $db->getArrayLine($db->ExecuteQuery($query));

    //var_dump($resultPignoAtto);
    //echo "<br><br><br>";
    if($resultPignoAtto == null){
        $query = "SELECT * FROM `v_atti` where Partita_ID = ".$result[$i]["ID"]." ORDER BY Atto_ID DESC LIMIT 1";
        $resultPignoAtto = $db->getArrayLine($db->ExecuteQuery($query));
       // var_dump($resultPignoAtto);
        //echo "<br><br><br>";
    }

    $tempKey = "";
    $utente = "";
    $dataPdf = array();
    $dataExcel[] = array("<b>CC</b>","<b>UTENTE</b>","<b>Utente_ID</b>","<b>Comune_ID</b>","<b>Partita_ID</b>","<b>TIPO</b>","<b>TESTO</b>");

    for($x=0; $x < count($resultDoc); $x++)
    {
        switch($resultDoc[$x]["DocumentTypeId"]){
            case 23:
                $tempKey = "ACCERTAMENTO";
                break;
            case 2:
                $tempKey = "INGIUNZIONE";
                break;
            case 4:
                $tempKey = "AVVISO INTIMAZIONE";
                break;
            case 12:
                $tempKey = "AVVISO MESSA IN MORA";
                break;
            case 8:
                $tempKey = "PIGNORAMENTO PRESSO BANCA/POSTA";
                break;
            case 7:
                $tempKey = "PIGNORAMENTO PRESSO DATORE DI LAVORO";
                break;
            case 24:
                $tempKey = "PIGNORAMENTO PRESSO ISTITUTI PREVIDENZIALI";
                break;
            case 14:
                $tempKey = "PIGNORAMENTO PRESSO TERZI";
                break;
            case 22:
                $tempKey = "PREAVVISO DI FERMO AMMINISTRATIVO";
                break;
            case 25:
                $tempKey = "PREAVVISO DI FERMO";
                break;
            case 26:
                $tempKey = "PREAVVISO DI ISCRIZIONE DI IPOTECA";
                break;
            case 27:
                $tempKey = "IPOTECA";
                break;
            case 6:
                $tempKey = "PIGNORAMENTO BENI MOBILI REGISTRATI";
                break;
            case 28:
                $tempKey = "IPOTECA IMMOBILIARE";
                break;
            case 29:
                $tempKey = "PIGNORAMENTO IMMOBILIARE";
                break;
            case 37:
                $tempKey = "PIGNORAMENTO MOBILIARE";
                break;
            case 30:
                $tempKey = "PEGNO MOBILIARE";
                break;
            case 31:
                $tempKey = "PIGNORAMENTO NATANTI";
                break;
            case 32:
                $tempKey = "SEQUESTRO CONSERVATIVO NATANTI";
                break;
            case 33:
                $tempKey = "IPOTECA NATANTI";
                break;
            case 34:
                $tempKey = "PIGNORAMENTO AEROMOBILI";
                break;
            case 35:
                $tempKey = "SEQUESTRO CONSERVATIVO AEROMOBILI";
                break;
            case 36:
                $tempKey = "IPOTECA AEROMOBILI";
                break;
            default: break; //throw new Exception("DocumentTypeId = ".$atti[$i]["DocumentTypeId"]." non presente nello switch della classe BuildMotivationText");
        }

        if($result[$i]["Ditta"]==null) $utente = $result[$i]["Cognome"]." ".$result[$i]["Nome"];
        else $utente = $result[$i]["Ditta"];

        $data[] = array($c,$utente,$result[$i]["Utente_ID"],$result[$i]["Comune_ID"],$resultDoc[$x]["Partita_ID"],$tempKey,$resultDoc[$x]["Text"]);
        $dataPdf[] = array($tempKey,$resultDoc[$x]["Text"]);
        $dataExcel[] = array($c,$utente,$result[$i]["Utente_ID"],$result[$i]["Comune_ID"],$resultDoc[$x]["Partita_ID"],$tempKey,$resultDoc[$x]["Text"]);
    }



    $a_recipientHeader = $cls_registry->printHeader($resultPignoAtto);
    //if($filter['printType'] == "final")
    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

    $pdf->setDocParams();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    if($help->getVar('printType') == "temp")
        $pdf->temporaryPrinting();
    $pdf->setManagerHeader($cls_ente->a_header);
    $pdf->setRecipientHeader($a_recipientHeader);
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(0);

    $pdf->SetFont("helvetica","B",8);
    $date->changeFormat("IT",false);
    $txt = "Comunicazione di sospensione, discarico totale, scarico parziale, inesigibilità'dell'atto ".$resultPignoAtto["ID_Cronologico"]."/".$resultPignoAtto["Anno_Cronologico"]." notificato in data ".$date->Get_DateNewFormat($resultPignoAtto["Data_Notifica"],"DB").", e relativa a ".$resultPignoAtto["Info_Cartella"]." - ".$a_enteAdmin["Denominazione"].".";

    $maxH = 0;
    if ($pdf->getStringHeight(30, "OGGETTO:") > $pdf->getStringHeight(165, $txt)) $maxH = $pdf->getStringHeight(30, "OGGETTO:");
    else $maxH = $pdf->getStringHeight(165, $txt);

    $pdf->MultiCell(30, $maxH, "OGGETTO:", 0, 'L', 0, 0, '', '', true);
    $pdf->MultiCell(165, $maxH, $txt, 0, 'L', 0, 1, '', '', true);

    //$pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(10);
    $pdf->SetFont("helvetica","",8);
    $txt = "Con la presente si comunica il discarico del titolo indicato, per i seguenti motivi:";
    $pdf->MultiCell(0,0,$txt, 0, 'L', 0, 1, '', '', true);

    //$pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(10);
    for($j=0; $j < count($dataPdf); $j++) {

        $maxH = 0;
        if ($pdf->getStringHeight(80, $dataPdf[$j][0]) > $pdf->getStringHeight(115, $dataPdf[$j][1])) $maxH = $pdf->getStringHeight(80, $dataPdf[$j][0]);
        else $maxH = $pdf->getStringHeight(115, $dataPdf[$j][1]);

        $pdf->SetFont("helvetica","B",8);
        $pdf->MultiCell(80, $maxH, $dataPdf[$j][0], 0, 'L', 0, 0, '', '', true);
        $pdf->SetFont("helvetica","",7);
        $pdf->MultiCell(115, $maxH, $dataPdf[$j][1], 0, 'L', 0, 1, '', '', true);
    }

    $path = $utils->crea_dir(PDFSGRAVI."/".$c."/".$result[$i]["ID"]);
    $completePath = $path."/Lettera_Accompagnamento_SgraviAnnullamenti_".$c."_".$result[$i]["ID"].".pdf";
    //echo "<br><br>".$completePath."<br><br>";
    $allPDFFile[] = $completePath;
    $pdf->Output($completePath,"F");

    $path = $utils->crea_dir(XLSSGRAVI."/".$c."/".$result[$i]["ID"]);
    $completePath = $path."/Elenco_SgraviAnnullamenti_".$c."_".$result[$i]["ID"].".xlsx";

    if(count($dataExcel) > 1)
        SimpleXLSXGen::fromArray( $dataExcel )
            ->setDefaultFont( 'Courier New' )
            ->setDefaultFontSize( 14 )
            ->saveAs($completePath);


    $dataExcel = array();

    $query = "UPDATE partita_tributi SET Printed = 'si' WHERE ID = ".$result[$i]["ID"];
    if(!$db->ExecuteQuery($query))
    {
        $error = 1;
        $msg = "Errore nel salvataggio dei dati.";
        $db->Rollback();
    }
}

$path = $utils->crea_dir(XLSSGRAVI."/".$c);
$nameFile = "Elenco_Merge_SgraviAnnullamenti_".$c."_".date("d-m-Y_H-i-s").".xlsx";
$completePath = $path."/".$nameFile;

if(count($data) > 1) {
    SimpleXLSXGen::fromArray($data)
        ->setDefaultFont('Courier New')
        ->setDefaultFontSize(14)
        ->saveAs($completePath);

    $save = new stdClass();
    $save->CC = $c;
    $save->ListType = 1;
    $save->FileType = 1;
    $save->Name = $nameFile;
    $save->Date = date("Y-m-d");
    $save->Description = $help->getVar("note");
    $save->Path = $completePath;

    if(!$db->DbSave($utils->GetObjectQuery($save,"file_list"))){
        $error = 1;
        $msg = "Errore nel salvataggio dei dati.";
        $db->Rollback();
    }
}
/*$books = [
    ['ISBN', 'title', 'author', 'publisher', 'ctry' ],
    [618260307, 'The Hobbit', 'J. R. R. Tolkien', 'Houghton Mifflin', 'USA'],
    [908606664, 'Slinky Malinki', 'Lynley Dodd', 'Mallinson Rendel', 'NZ']
];
$xlsx = SimpleXLSXGen::fromArray( $books );*/

//die;

//$xlsx->saveAs($completePath); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx

if(count($allPDFFile) > 0) {
    $cls_merge = new cls_merge();
    $cls_merge->setFiles($allPDFFile);
    $cls_merge->concatFiles(false);
    $path = $utils->crea_dir(PDFSGRAVI . "/" . $c);
    $nameFile = "Lettera_Accompagnamento_SgraviAnnullamenti_Merge_" . $c . "_" . date("d-m-Y_H-i-s") . ".pdf";
    $completePath = $path ."/". $nameFile;

    $cls_merge->Output($completePath, "F");
    $completeWebPath = SUPER_WEB_ROOT . $utils->mostra_file_path($completePath);

    $save = new stdClass();
    $save->CC = $c;
    $save->ListType = 1;
    $save->FileType = 2;
    $save->Name = $nameFile;
    $save->Date = date("Y-m-d");
    $save->Description = $help->getVar("note");
    $save->Path = $completePath;

    if(!$db->DbSave($utils->GetObjectQuery($save,"file_list"))){
        $error = 1;
        $msg = "Errore nel salvataggio dei dati.";
        $db->Rollback();
    }

//echo $completeWebPath;
    echo "<script>window.open('" . $completeWebPath . "','Merge File','height=700,width=500'); </script>";
}

$db->End_Transaction();

echo "<script>location.href = 'elenco_sgravi_annull.php?c=".$c."&a=".$a."&msg=".$msg."&error=".$error."';</script>";
//header("Location: elenco_sgravi_annull.php?c=".$c."&a=".$a."&msg=File creati&error=0");
