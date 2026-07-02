<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

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

$_SESSION['progress'] = "0.00";
session_write_close();

$cls_help = new cls_help();
$cls_db = new cls_db();
$log = new LOG();
$cls_Utils = new cls_Utils();
$cls_date = new cls_DateTimeI("IT",false);

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('Partita_ID');

$ID_Atto = $cls_help->getVar('ID_Atto');
//var_dump($ID_Atto);die;
if($cls_help->getVar('last_act')!=null)                                     // last_act non è presente nel form inviato
    $ID_Atto = $cls_help->getVar('last_act');                               // 

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
    //$Date = $cls_date->Get_DateNewFormat($resultDate["Sgravio_Activation_Date"],"DB");
}
//else $Date = "";

$placeDate = $managerCity.", ".date("d/m/Y");

$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,15)));

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(20 ,2);
session_write_close();

$cls_text->html_body = isset($a_text['Content'])?$a_text['Content']:null;
$cls_text->html_replaced_body = $cls_text->html_body;

$query = "SELECT P.*, PT.Tipo AS Tipo_Entrata, PT.Comune_ID AS Partita_Comune_ID, PT.Anno_Riferimento, U.Ditta, T.Tipo_Info , U.Cognome, U.Nome, SA.ID AS ID_Sospensione, 
            SA.Motivo_Sospensione_ID, SA.Note_Sospensione, SA.Data_Sospensione 
            FROM v_pignoramento as P 
            JOIN tributo as T on T.Partita_ID = P.Partita_ID AND P.CC = T.CC
            JOIN partita_tributi as PT on PT.ID = P.Partita_ID AND PT.CC = P.CC
            JOIN sospensione_atto as SA on SA.Partita_ID = P.Partita_ID 
            JOIN utente AS U ON U.ID = PT.Utente_ID
            WHERE P.ID = ".$ID_Atto." ";
$atto = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));

//var_dump($atto->Partita_ID);die;

$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $atto->Tipo_Riscossione)));
//var_dump($a_responsibleParams);
if(!is_array($a_responsibleParams)){
    //$cls_help->alert("ATTENZIONE!!! Parametri dei responsabili assenti per ".$atto->Tipo_Riscossione."!");
    //echo "<script>window.close();</script>";
    echo json_encode([
        "error" => 2,
        "msg" => "ATTENZIONE!!! Parametri dei responsabili assenti per ".$atto->Tipo_Riscossione."!"
    ]);
    die;
}

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(40 ,2);
session_write_close();

$cls_params->setArray("responsabili",$a_responsibleParams);
$cls_params->getSignatures($cls_ente->type);

$a_recipientHeader = $cls_registry->printHeader((array) $atto,$placeDate);

$query = "SELECT * FROM parametri_notifica WHERE ID = '".$atto->Motivo_Sospensione_ID."'";
$parametri_notifica = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_notifica");


switch($cls_ente->type){
    case "Gestore":
        $manager = "Concessionario ".$cls_ente->a_ente[$cls_ente->type.'_Denominazione'];
        break;
    default:
        $manager = $cls_ente->a_ente[$cls_ente->type.'_Denominazione'];
}


$query_pigno = "SELECT D.Description AS Descrizione_Pigno ,N.Data_Notifica ,P.* 
                FROM pignoramento_generale as P 
                JOIN document_type as D on D.Id = P.DocumentTypeId 
                LEFT JOIN notifica_atto as N on P.ID = N.Atto_Notificato_ID
                WHERE N.Data_Notifica IS NOT NULL AND N.Tipo_Notifica = 'debitore' AND P.Partita_ID = ".$p."
                ORDER BY N.Data_Notifica DESC
                LIMIT 1";

$resultPigno = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_pigno));

if($resultPigno == null){
    echo json_encode([
        "error" => 2,
        "msg" => "Impossibile stampare sospensione pignoramento: non è stata inserita la data di notifica al debitore!"
    ]);
    die;
}


//var_dump($resultPigno);die;

$actNot = strtoupper($resultPigno["Descrizione_Pigno"]." N. ".$resultPigno["ID_Cronologico"]." DEL ".$resultPigno["Anno_Cronologico"]." NOTIFICATO IL ".$cls_date->Get_DateNewFormat($resultPigno["Data_Notifica"],"DB"))."<br>";


//var_dump($actNot);die;
if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(60 ,2);
session_write_close();

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

if($atto->Note_Sospensione != null && $atto->Note_Sospensione != "")
    $note = "NOTE: ".$atto->Note_Sospensione;

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

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(80 ,2);
session_write_close();

/* ----------------------------------------------------------------------------------- */

$cls_text_annull = new cls_textParameters();
$a_text_annull = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text_annull->getParametersQuery($c,46)));

$cls_text_annull->html_body = isset($a_text_annull['Content'])?$a_text_annull['Content']:null;
$cls_text_annull->html_replaced_body = $cls_text_annull->html_body;

$cls_text_annull->a_var = array(
    "{Document}" => strtoupper($atto->Nome_Pignoramento),
    "{CronoYear}" => $atto->Anno_Cronologico,
    "{CronoID}" => $atto->ID_Cronologico,
    "{Reference}" => $atto->Comune_ID."/".$atto->Anno_Riferimento,
    "{CollectionType}" => "Servizio riscossione coattiva",
    "{Ente}" => $cls_ente->getCityDenomination(),
    "{Manager}" => $manager,
    "{User}" => $a_recipientHeader['recipient'],
    "{Motivation}" => $parametri_notifica->Descrizione,
    "{Notes}" => $atto->Note_Sospensione,
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
//var_dump($a_recipientHeader);die;
$pdf->setRecipientHeader($a_recipientHeader);
$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->ln(0);

$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($cls_text_annull->html_replaced_body, true, 0, true, 0);

$path = $cls_Utils->crea_dir( ATTI ."/". $c . "/Documenti" );
$path_temp = $cls_Utils->crea_dir(ARCHIVIO ."/temp");
$path_temp_web = ARCHIVIO_WEB ."/temp";

//var_dump($path_temp_web);die;

$identificativo_file = "Sospensione_Pignoramento_".$c."_".$atto->ID_Cronologico."_".$atto->Anno_Cronologico."_".date("Y-m-d_H-i");
$nome_file = $identificativo_file.".pdf";
//die;
if($stampa_select == "PROVVISORIA")
{
    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(100 ,2);
    session_write_close();

    $pdf->Output( $path_temp."/".$nome_file , 'F');
    echo json_encode([
        "path" => $path_temp_web."/".$nome_file,
        "error" => 0,
        "msg" => "File stampato correttamente!"
    ]);
    die;
}else if($stampa_select == "DEFINITIVA")
    $pdf->Output( $path."/".$nome_file , 'F');

$completePathFile = $path."/".$nome_file;

//var_dump($completePathFile);die;

/* ----------------------------------------------------------------------------- */

$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$comune_id = isset($result["Com"])?$result["Com"]:0;

$salva = new stdClass();

$salva->CC = $c;
$salva->Atto_ID = $atto->Atto_ID;
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

if(session_status() == PHP_SESSION_NONE)session_start();
$_SESSION['progress'] = number_format(100 ,2);
session_write_close();

if(!$return){
    $log->error("Errore impossibile completare salvataggio dati su tabella documento");
    //$cls_help->alert("Salvataggio dati non riuscito");
    echo json_encode([
        "error" => 2,
        "msg" => "Salvataggio dati su tabella documento non riuscito!"
    ]);
    die;
}else{
    $log->info("Salvataggio su tabella documento riuscito, ID = ".$return);
}

//$cls_help->alert("Documento salvato nella corrispondenza anagrafica!");
echo json_encode([
    "path" => $mostra_file_web,
    "error" => 0,
    "msg" => "File stampato correttamente! Documento salvato nella corrispondenza anagrafica!"
]);