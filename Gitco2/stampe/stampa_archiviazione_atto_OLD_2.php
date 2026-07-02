<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_merge.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();
$log = new LOG();
$cls_Utils = new cls_Utils();
$cls_date = new cls_DateTimeI("IT",false);

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$ID_Atto = $cls_help->getVar('ID_Atto');
if($cls_help->getVar('last_act')!=null)
    $ID_Atto = $cls_help->getVar('last_act');
$stampa_select = strtoupper($cls_help->getVar('stampa_select'));
if($stampa_select==null)
	$stampa_select = "PROVVISORIA";

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );

$cls_params = new cls_parameters();
$cls_st = new cls_Stampe();
$cls_Utils = new cls_Utils();
$cls_registry = new cls_registry();
$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
$managerCity = $cls_ente->getCityManager();

if($cls_help->getVar("Partita_ID") != null) {
    $query = "SELECT Sgravio_Activation_Date FROM partita_tributi WHERE ID = " . $cls_help->getVar("Partita_ID");
    $resultDate = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "partita_tributi");
    $Date = $cls_date->Get_DateNewFormat($resultDate["Sgravio_Activation_Date"],"DB");
}
else $Date = "";

$placeDate = $managerCity.", ".$Date;

$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,15)));

$cls_text->html_body = isset($a_text['Content'])?$a_text['Content']:null;
$cls_text->html_replaced_body = $cls_text->html_body;

$query = "SELECT A.*, T.Info_Cartella, PT.Tipo AS Tipo_Entrata, PT.Comune_ID AS Partita_Comune_ID, PT.Anno_Riferimento, T.Tipo_Info , U.Ditta, U.Cognome, U.Nome
            FROM v_atti as A 
            JOIN tributo as T on T.Partita_ID = A.Partita_ID AND A.CC = T.CC
            JOIN partita_tributi as PT on PT.ID = A.Partita_ID AND PT.CC = A.CC
            JOIN utente AS U ON U.ID = PT.Utente_ID
            WHERE A.Atto_ID = ".$ID_Atto." AND A.CC = '".$c."'";
$atto = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));

//var_dump($atto);
//die;

$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $atto->Tipo_Riscossione)));
//var_dump($a_responsibleParams);
if(!is_array($a_responsibleParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri dei responsabili assenti per ".$atto->Tipo_Riscossione."!");
    echo "<script>window.close();</script>";
}

$cls_params->setArray("responsabili",$a_responsibleParams);
$cls_params->getSignatures($cls_ente->type);

$a_recipientHeader = $cls_registry->printHeader((array) $atto,$placeDate);

$query = "SELECT * FROM parametri_notifica WHERE ID = '".$atto->Motivo_Blocco."'";
$parametri_notifica = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_notifica");


switch($cls_ente->type){
    case "Gestore":
        $manager = "Concessionario ".$cls_ente->a_ente[$cls_ente->type.'_Denominazione'];
        break;
    default:
        $manager = $cls_ente->a_ente[$cls_ente->type.'_Denominazione'];
}

if($cls_help->getVar("from")=="annull") $actNot = $cls_st->tutti_gli_atti_notificati($atto->Partita_ID);
else if($cls_help->getVar("from")=="sgravio") $actNot = $atto->Tipo_Entrata." ".$atto->Info_Cartella;
else $actNot = $cls_st->tutti_gli_atti_notificati($atto->Partita_ID);

$entrata = "";
$motivation = "";
$note = "";
switch ($atto->Tipo_Info){
    case "E": $entrata = "Entrate Patrimoniali"; break;
    case "S": $entrata = "Sanzioni Amministrative"; break;
    case "M": $entrata = "Matricola"; break;
    default: break;
}

if($parametri_notifica->Descrizione != null && $parametri_notifica->Descrizione != "")
    $motivation = "MOTIVAZIONE: ".$parametri_notifica->Descrizione;

if($atto->Note_Blocco != null && $atto->Note_Blocco != "")
    $note = "NOTE: ".$atto->Note_Blocco;

$cls_text->a_var = array(
    "{Partita_ID}" => $atto->Partita_Comune_ID,
    "{AnnoRif}" => $atto->Anno_Riferimento,
    "{Entrance}" => $entrata,
    "{CollectionType}" => "Servizio riscossione coattiva",
    "{Ente}" => $cls_ente->getCityDenomination(),
    "{Manager}" => $manager,
    "{User}" => $a_recipientHeader['recipient'],
    "{Motivation}" => $motivation,
    "{Notes}" => $note,
    "{ActsNotified}" => $actNot,
    "{SignUfficiale}" => $cls_params->getHtmlSignature("{SignUfficiale}")
);

//var_dump($cls_text->a_var["{SignUfficiale}"]);
//die;
$cls_text->replaceVariables($cls_text->a_var);

$from = $cls_help->getVar("from");
//$cls_help->alert("inizio");

if($from!=null){


    $file = array();
    if($from == "annull") {
        //$cls_help->alert("annullamento");
        $cls_text_annull = new cls_textParameters();
        $a_text_annull = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text_annull->getParametersQuery($c,38)));

        $cls_text_annull->html_body = isset($a_text_annull['Content'])?$a_text_annull['Content']:null;
        $cls_text_annull->html_replaced_body = $cls_text_annull->html_body;

        $cls_text_annull->a_var = array(
            "{Document}" => strtoupper($atto->Atto),
            "{CronoYear}" => $atto->Anno_Cronologico,
            "{CronoID}" => $atto->ID_Cronologico,
            "{Reference}" => $atto->Comune_ID."/".$atto->Anno_Riferimento,
            "{CollectionType}" => "Servizio riscossione coattiva",
            "{Ente}" => $cls_ente->getCityDenomination(),
            "{Manager}" => $manager,
            "{User}" => $a_recipientHeader['recipient'],
            "{Motivation}" => $parametri_notifica->Descrizione,
            "{Notes}" => $atto->Note_Blocco,
            "{ActsNotified}" => $actNot,
            "{SignUfficiale}" => $cls_params->getHtmlSignature("{SignUfficiale}"),
            "{SignRespProcedimento}" => $cls_params->getHtmlSignature("{SignRespProcedimento}")
        );

        $cls_text_annull->replaceVariables($cls_text_annull->a_var);

        for ($i = 1; $i < 3; $i++) {
            $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

            $a_recipientHeader["ente"] = $cls_ente->a_ente;

            $pdf->setDocParams();
            $pdf->SetAutoPageBreak(true);
            $pdf->AddPage("P");
            if ($stampa_select == "PROVVISORIA")
                $pdf->temporaryPrinting();
            $pdf->setManagerHeader($cls_ente->a_header);
//var_dump($a_recipientHeader);
            $pdf->setRecipientHeaderAnnul($a_recipientHeader, $i, $from);
            $pdf->SetMargins(7.0, 10.0, 7.0);
            $pdf->ln(0);

            $pdf->SetFont('helvetica', '', 9);
            $pdf->writeHTML($cls_text_annull->html_replaced_body, true, 0, true, 0);

            $path = $cls_Utils->crea_dir(ATTI . "/" . $c . "/Documenti");

            if ($i == 1) $identificativo_file = "Copia_Utente_" . $from . "_" . $c . "_" . $atto->ID_Cronologico . "_" . $atto->Anno_Cronologico . "_" . date("Y-m-d_H-i") . "_" . $i;
            else $identificativo_file = "Copia_Ente_" . $from . "_" . $c . "_" . $atto->ID_Cronologico . "_" . $atto->Anno_Cronologico . "_" . date("Y-m-d_H-i") . "_" . $i;
            $nome_file = $identificativo_file . ".pdf";
//die;
            $pdf->Output($path . "/" . $nome_file, 'F');

            $file[] = $path . "/" . $nome_file;
        }

        $query = "UPDATE partita_tributi SET Printed_Annull = 'si', print_annull_date = '" . date("Y-m-d") . "' WHERE ID = " . $atto->Partita_ID;
        if (!$cls_db->ExecuteQuery($query)) {
            $error = 1;
            $msg = "Errore nel salvataggio dei dati.";
            $cls_db->Rollback();
        }

    }
    else if($from == "sgravio"){
        //$cls_help->alert("sgravio");
        $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

        $a_recipientHeader["ente"] = $cls_ente->a_ente;

        $pdf->setDocParams();
        $pdf->SetAutoPageBreak(true);
        $pdf->AddPage("P");
        if ($stampa_select == "PROVVISORIA")
            $pdf->temporaryPrinting();
        $pdf->setManagerHeader($cls_ente->a_header);
//var_dump($a_recipientHeader);
        $pdf->setRecipientHeaderAnnul($a_recipientHeader, 2, $from);
        $pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->ln(0);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);

        /** DA CONTROLLARE **/
        $query = "SELECT * FROM sgravi_documenti AS SD WHERE SD.Partita_ID = ".$atto->Partita_ID;
        $resultDoc = $cls_db->getResults($cls_db->ExecuteQuery($query));

        $tempKey = "";
        $utente = "";
        $dataPdf = array();
        $dataExcel[] = array("<b>CC</b>","<b>UTENTE</b>","<b>Utente_ID</b>","<b>Comune_ID</b>","<b>Partita_ID</b>","<b>TIPO</b>","<b>TESTO</b>");

        $countResDoc = count($resultDoc);
        for($x=0; $x < $countResDoc; $x++)
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
                case 13:
                    $tempKey = "PIGNORAMENTO PRESSO INPS";
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
                case 0:
                    $tempKey = "Firma (L'ufficiale della riscossione)";
                    break;
                default: break; //throw new Exception("DocumentTypeId = ".$atti[$i]["DocumentTypeId"]." non presente nello switch della classe BuildMotivationText");
            }

            if($atto->Ditta==null) $utente = $atto->Cognome." ".$atto->Nome;
            else $utente = $atto->Ditta;

            $Partita_ID = isset($resultDoc[$x]["Partita_ID"])?$resultDoc[$x]["Partita_ID"]:$atto->Partita_ID;

            $data[] = array($c,$utente,$atto->Utente_Comune_ID,$atto->Comune_ID,$Partita_ID,$tempKey,$resultDoc[$x]["Text"]);
            $dataPdf[] = array($tempKey,$resultDoc[$x]["Text"]);
            $dataExcel[] = array($c,$utente,$atto->Utente_Comune_ID,$atto->Comune_ID,$Partita_ID,$tempKey,$resultDoc[$x]["Text"]);
        }



        //$a_recipientHeader = $cls_registry->printHeader($resultPignoAtto);
        //if($filter['printType'] == "final")
        //$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

        //$pdf->setDocParams();
        //$pdf->SetAutoPageBreak(true);
        $pdf->AddPage("P");
        if ($stampa_select == "PROVVISORIA")
            $pdf->temporaryPrinting();
        /*$pdf->setManagerHeader($cls_ente->a_header);
        $pdf->setRecipientHeader($a_recipientHeader);*/
        $pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->ln(10);

        /*$pdf->SetFont("helvetica","B",8);
        $date->changeFormat("IT",false);
        $txt = "Comunicazione di sospensione, discarico totale, scarico parziale, inesigibilità'dell'atto ".$resultPignoAtto["ID_Cronologico"]."/".$resultPignoAtto["Anno_Cronologico"]." notificato in data ".$date->Get_DateNewFormat($resultPignoAtto["Data_Notifica"],"DB").", e relativa a ".$resultPignoAtto["Info_Cartella"]." - ".$a_enteAdmin["Denominazione"].".";

        $maxH = 0;
        if ($pdf->getStringHeight(30, "OGGETTO:") > $pdf->getStringHeight(165, $txt)) $maxH = $pdf->getStringHeight(30, "OGGETTO:");
        else $maxH = $pdf->getStringHeight(165, $txt);

        //$pdf->MultiCell(100,10,"Data stampa: ".date("Y-m-d"),0,'L',0,0,'','',true);
        //$pdf->ln(10);
        $pdf->MultiCell(30, $maxH, "OGGETTO:", 0, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(165, $maxH, $txt, 0, 'L', 0, 1, '', '', true);

        //$pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->ln(10);*/
        $pdf->SetFont("helvetica","",8);
        $txt = "Con la presente si comunica il discarico del titolo indicato, per i seguenti motivi:";
        $pdf->MultiCell(0,0,$txt, 0, 'L', 0, 1, '', '', true);

        //$pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->ln(10);
        $countDataPdf = count($dataPdf);
        for($j=0; $j < $countDataPdf ; $j++) {

            $maxH = 0;
            if ($pdf->getStringHeight(80, $dataPdf[$j][0]) > $pdf->getStringHeight(115, $dataPdf[$j][1])) $maxH = $pdf->getStringHeight(80, $dataPdf[$j][0]);
            else $maxH = $pdf->getStringHeight(115, $dataPdf[$j][1]);

            $pdf->SetFont("helvetica","B",8);
            $pdf->MultiCell(80, $maxH, $dataPdf[$j][0], 0, 'L', 0, 0, '', '', true);
            $pdf->SetFont("helvetica","",7);
            $pdf->MultiCell(115, $maxH, $dataPdf[$j][1], 0, 'L', 0, 1, '', '', true);
        }

        $pdf->SetFont("helvetica","B",10);
        $pdf->MultiCell(0, 50, "L'ufficiale della riscossione", 0, 'L', 0, 0, 150, $pdf->GetY()+15, true);
        //$pdf->ln();
        $pdf->Image($cls_params->a_signature["ufficiale"]['filePath'],159,$pdf->GetY()+5,27,16);
        $pdf->SetFont("helvetica","",10);
        $pdf->MultiCell(45, 50, strtoupper($cls_params->a_signature["ufficiale"]["name"]), 0, 'C', 0, 0, 150, $pdf->GetY()+23, true);

        //$path = $utils->crea_dir(ATTI . "/" . $c . "/Documenti/Temp");
        //$completePath = $path."/Lettera_Accompagnamento_Sgravi_".$c."_".$result[$i]["ID"].".pdf";
        //$path = $utils->crea_dir(PDFSGRAVI."/".$c."/".$result[$i]["ID"]);

        $path = $cls_Utils->crea_dir(ATTI . "/" . $c . "/Documenti/".$atto->Partita_ID);
        $nameFile = "Sgravio_" . $c . "_" . $atto->Partita_ID . ".pdf";
        $completePath = $path ."/". $nameFile;
        //echo "<br><br>".$completePath."<br><br>";
        //$file[] = $completePath;
        $pdf->Output($completePath,"F");
        $file[] = $completePath;

        /*$cls_merge = new cls_merge();
        $cls_merge->setFiles($file);
        $cls_merge->concatFiles(false);
        $path = $utils->crea_dir(ATTI . "/" . $c . "/Documenti");
        $nameFile = "Sgravio_" . $c . "_" . date("d-m-Y_H-i-s") . ".pdf";
        $completePath = $path ."/". $nameFile;

        $cls_merge->Output($completePath, "F");*/
        //$allPDFFile[] = $completePath;
        //$file = array();

        $path = $cls_Utils->crea_dir(XLSSGRAVI."/".$c."/".$atto->Partita_ID);
        $completePath = $path."/Elenco_Sgravi_".$c."_".$atto->Partita_ID.".xlsx";

        if(count($dataExcel) > 1)
            SimpleXLSXGen::fromArray( $dataExcel )
                ->setDefaultFont( 'Courier New' )
                ->setDefaultFontSize( 14 )
                ->saveAs($completePath);

        $file[] = $completePath;
        //$dataExcel = array();
        //$dataPdf = array();

        $query = "UPDATE partita_tributi SET Printed_Sgravio = 'si', print_sgravio_date = '" . date("Y-m-d") . "' WHERE ID = " . $atto->Partita_ID;
        if (!$cls_db->ExecuteQuery($query)) {
            $error = 1;
            $msg = "Errore nel salvataggio dei dati.";
            $cls_db->Rollback();
        }

    }
    $tipo = 0;
    if($from == "sgravio") $tipo = 1;
    else if($from == "annull") $tipo = 2;

    $query = "SELECT ID FROM sgravio WHERE Partita_ID = ".$cls_help->getVar("Partita_ID")." AND Tipo = ".$tipo;
    $result_Sgravio = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

    $save = array();
    $save["Data_Stampa"] = date("Y-m-d");
    $save["Partita_ID"] = $cls_help->getVar("Partita_ID");
    $save["Tipo"] = $tipo;

    if($from == "sgravio"){
        $save["File_1"] = $file[1];
        $save["File_2"] = $file[0];
    }else if($from == "annull"){
        $save["File_1"] = $file[0];
        $save["File_2"] = $file[1];
    }



    if($result_Sgravio["ID"]!=null) $obj = $cls_Utils->GetObjectQuery($save,"sgravio",array("ID" => $result_Sgravio["ID"]));
    else $obj = $cls_Utils->GetObjectQuery($save,"sgravio");

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    $error = 0;
    if(!$cls_db->DbSave($obj)){
        $cls_db->Rollback();
        $error = 1;
        $msg = "Errore impossibile salvare dati a db";
    }else $msg = "Daqti salvati correttamente";

    $cls_db->End_Transaction();

    echo "<script>window.location.href = '".WEB_ROOT."/coattiva/annulamento_sgravi.php?c={$c}&a={$a}&calling_page=".$cls_help->getVar("calling_page")."&last_act=".$cls_help->getVar("last_act")."&partita=".$cls_help->getVar("Partita_ID")."&p={$p}&msg={$msg}&error={$error}&pageCalled=".$cls_help->getVar("pageCalled")."';</script>";
    die;
    /*$cls_merge = new cls_merge();
    $cls_merge->setFiles($file);
    $cls_merge->concatFiles();

    $identificativo_file = "Concat_Archiviazione_Atto_" . $c . "_" . $atto->ID_Cronologico . "_" . $atto->Anno_Cronologico . "_" . date("Y-m-d_H-i");
    $nome_file = $identificativo_file . ".pdf";
    $completePathFile = $path."/".$nome_file;

    $cls_merge->Output($completePathFile, "F");*/
}
else{

    //("archiviazione");

    $cls_text_annull = new cls_textParameters();
    $a_text_annull = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text_annull->getParametersQuery($c,38)));

    $cls_text_annull->html_body = isset($a_text_annull['Content'])?$a_text_annull['Content']:null;
    $cls_text_annull->html_replaced_body = $cls_text_annull->html_body;

    $cls_text_annull->a_var = array(
        "{Document}" => strtoupper($atto->Atto),
        "{CronoYear}" => $atto->Anno_Cronologico,
        "{CronoID}" => $atto->ID_Cronologico,
        "{Reference}" => $atto->Comune_ID."/".$atto->Anno_Riferimento,
        "{CollectionType}" => "Servizio riscossione coattiva",
        "{Ente}" => $cls_ente->getCityDenomination(),
        "{Manager}" => $manager,
        "{User}" => $a_recipientHeader['recipient'],
        "{Motivation}" => $parametri_notifica->Descrizione,
        "{Notes}" => $atto->Note_Blocco,
        "{ActsNotified}" => $actNot,
        "{SignUfficiale}" => $cls_params->getHtmlSignature("{SignUfficiale}"),
        "{SignRespProcedimento}" => $cls_params->getHtmlSignature("{SignRespProcedimento}"),
        "{SignLegale}" => $cls_params->getHtmlSignature("{SignLegale}")
    );

    $cls_text_annull->replaceVariables($cls_text_annull->a_var);

    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

    $pdf->setDocParams();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    if($stampa_select == "PROVVISORIA")
        $pdf->temporaryPrinting();
    $pdf->setManagerHeader($cls_ente->a_header);
//var_dump($a_recipientHeader);
    $pdf->setRecipientHeader($a_recipientHeader);
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(0);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->writeHTML($cls_text_annull->html_replaced_body, true, 0, true, 0);

    $path = $cls_Utils->crea_dir( ATTI ."/". $c . "/Documenti" );

    $identificativo_file = "Archiviazione_Atto_".$c."_".$atto->ID_Cronologico."_".$atto->Anno_Cronologico."_".date("Y-m-d_H-i");
    $nome_file = $identificativo_file.".pdf";
//die;
    if($stampa_select == "PROVVISORIA")
    {
        $pdf->Output( $nome_file , 'I');
        die;
    }else if($stampa_select == "DEFINITIVA")
        $pdf->Output( $path."/".$nome_file , 'F');

    $completePathFile = $path."/".$nome_file;
}


$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$comune_id = isset($result["Com"])?$result["Com"]:0;

$salva = new stdClass();

$salva->CC = $c;
$salva->Comune_ID = $comune_id + 1;
$salva->File = $nome_file;
$salva->Utente_ID = $atto->Utente_ID;
$salva->Tipo = "Inviato";
$salva->Atto = "Archiviazione atto";
$salva->Data_Creazione = date("Y-m-d");
$salva->Oggetto = $identificativo_file;
$salva->Data_Stampa = date("Y-m-d");

$mostra_file_web = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($completePathFile);

$return = $cls_db->DbSave($cls_Utils->GetObjectQuery((array) $salva,"documento"));

if(!$return){
    $log->error("Errore impossibile completare salvataggio dati su tabella documento");
    $cls_help->alert("Salvataggio dati non riuscito");
    die;
}else{
    $log->info("Salvataggio su tabella documento riuscito, ID = ".$return);
}

$cls_help->alert("Documento salvato nella corrispondenza anagrafica!");
	
?>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        // Your code to run since DOM is loaded and ready
        document.getElementById('frame').src = '<?= $mostra_file_web; ?>';
    });
</script>
    <div style="width: 100%; height: 100%">
        <iframe style="width: 100%; height: 100%" id="frame" src=""></iframe>
    </div>

