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
$placeDate = $managerCity.", ".date("d/m/Y");

$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,15)));

$cls_text->html_body = $a_text['Content'];
$cls_text->html_replaced_body = $cls_text->html_body;

$query = "SELECT A.*, T.Info_Cartella, PT.Tipo AS Tipo_Entrata FROM v_atti as A 
            JOIN tributo as T on T.Partita_ID = A.Partita_ID AND A.CC = T.CC
            JOIN partita_tributi as PT on PT.ID = A.Partita_ID AND PT.CC = A.CC
            WHERE A.Atto_ID = ".$ID_Atto." AND A.CC = '".$c."'";
$atto = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));

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

$cls_text->a_var = array(
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
    "{SignLegale}" => $cls_params->getHtmlSignature("{SignLegale}"),
    "{SignRespProcedimento}" => $cls_params->getHtmlSignature("{SignRespProcedimento}")
);

$cls_text->replaceVariables($cls_text->a_var);

$from = $cls_help->getVar("from");

if($from!=null){

    $file = array();
    for($i=1; $i<3; $i++) {
        $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

        $a_recipientHeader["ente"] = $cls_ente->a_ente;

        $pdf->setDocParams();
        $pdf->SetAutoPageBreak(true);
        $pdf->AddPage("P");
        if ($stampa_select == "PROVVISORIA")
            $pdf->temporaryPrinting();
        $pdf->setManagerHeader($cls_ente->a_header);
//var_dump($a_recipientHeader);
        $pdf->setRecipientHeaderAnnul($a_recipientHeader, $i);
        $pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->ln(0);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);

        $path = $cls_Utils->crea_dir(ATTI . "/" . $c . "/Documenti");

        if($i==1) $identificativo_file = "Copia_Utente_".$from."_" . $c . "_" . $atto->ID_Cronologico . "_" . $atto->Anno_Cronologico . "_" . date("Y-m-d_H-i")."_".$i;
        else $identificativo_file = "Copia_Ente_".$from."_" . $c . "_" . $atto->ID_Cronologico . "_" . $atto->Anno_Cronologico . "_" . date("Y-m-d_H-i")."_".$i;
        $nome_file = $identificativo_file . ".pdf";
//die;
        $pdf->Output($path . "/" . $nome_file, 'F');

        $file[] = $path . "/" . $nome_file;
    }
    $tipo = 0;
    if($from == "sgravio") $tipo = 1;
    else if($from == "annull") $tipo = 2;

    $query = "SELECT ID FROM sgravio WHERE Partita_ID = ".$cls_help->getVar("Partita_ID")." AND Tipo = ".$tipo;
    $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

    $save = array();
    $save["Data_Stampa"] = date("Y-m-d");
    $save["Partita_ID"] = $cls_help->getVar("Partita_ID");
    $save["Tipo"] = $tipo;
    $save["File_1"] = $file[0];
    $save["File_2"] = $file[1];

    if($result["ID"]!=null) $obj = $cls_Utils->GetObjectQuery($save,"sgravio",array("ID" => $result["ID"]));
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

    echo "<script>window.location.href = '".WEB_ROOT."/coattiva/annulamento_sgravi.php?c={$c}&a={$a}&calling_page=".$cls_help->getVar("calling_page")."&last_act=".$cls_help->getVar("last_act")."&partita=".$cls_help->getVar("Partita_ID")."&p={$p}&msg={$msg}&error={$error}';</script>";
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
    $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);

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

