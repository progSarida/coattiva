<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/headerAjax.php");
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_utils = new cls_Utils();
$cls_db = new cls_db();

$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");


$elaborationId =  $cls_help->getVar('el');

$query = "SELECT UserName, Password, INIPECPasswordExpiration from ini_pec_processing where UserId=".$_SESSION['aut_progr'];
$a_credentials = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$msg="";
$err=null;
if(empty($a_credentials)){
    $msg="Utente inipec non trovato!";
    $err = 1;
}
else if(empty($a_credentials['INIPECPasswordExpiration'])){
    $msg="Scadenza password non settata!";
    $err = 1;
}
else if(date('Y-m-d')>$a_credentials['INIPECPasswordExpiration']) {
    $msg = "Password scaduta, per modificarla premere il lucchetto verde!";
    $err = 1;
}

if(!empty($err)){
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$elaborationId."&msg=".$msg."&error=".$err."' </script>";
    die;
}

$query = "SELECT * FROM elaborations WHERE Id = ".$elaborationId;
$a_elaboration = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if(empty($a_elaboration)){
    $msg = "Elaborazione ".$elaborationId." non trovata";
    $err = 1;
}
else{
    $query = "SELECT DISTINCT P.CF_PI FROM v_partita P "
    . "WHERE P.CF_PI!='00000000000' AND P.CF_PI!='' AND P.CF_PI is not null "
    . "AND (DATEDIFF('".date('Y-m-d')."', P.InipecLoaded)>=15 OR P.InipecLoaded is null) "
    . "AND P.Elaboration_Id = ".$elaborationId
    . " GROUP BY P.CC, P.CF_PI ORDER BY P.CC, P.CF_PI";

    if($a_elaboration['Document_Type_Id']==7) // sovrascrio query in caso di "pignoramento terzo lavoro" per poter prendere anche i terzi
    {
        $query = "
        Select DISTINCT
        if(U.Genere=\"D\",U.Partita_Iva,U.Codice_Fiscale) AS CF_PI
        FROM pignoramento_generale AS PG
        JOIN notifica_atto as NA ON NA.Atto_Notificato_ID=PG.ID
        JOIN utente AS U ON U.ID=NA.Utente_ID
        Where (DATEDIFF('".date('Y-m-d')."', U.InipecLoaded)>=15 OR U.InipecLoaded is null)  AND 
        PG.Elaboration_Id = $elaborationId
        having  CF_PI!='00000000000' AND CF_PI!='' AND CF_PI is not null 
        ";
    }
    $a_inipec = $cls_db->getResults($cls_db->ExecuteQuery($query));

    if(count($a_inipec)==0){
        $msg = "Nessun utente valido trovato per la richiesta a IniPec. Contattare la assistenza per la verifica dei dati";
        $err = 1;
    }
}

if(!empty($err)){
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$elaborationId."&msg=".$msg."&error=".$err."' </script>";
    die;
}


$fileName = $a_credentials['UserName'] . date('Ymdhis', time());
$dirPath = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
$file['txt'] = $dirPath . "/" . $fileName . ".txt";
$file['zip'] = $dirPath . "/" . $fileName . ".zip";


$txt_string = "";
foreach ($a_inipec as $key=>$a_cf){
    $txt_string .= $a_cf['CF_PI']."\n";
}

$txtFile = fopen($file['txt'], "w");
fwrite($txtFile, $txt_string);
fclose($txtFile);
if(!is_file($file['txt'])){
    $msg = "Errore nella creazione del file txt";
    $err = 1;
}
else{
    $zipFile = new ZipArchive();
    if ($zipFile->open($file['zip'], ZipArchive::CREATE)) {
        $zipFile->addFile($file['txt'], $fileName . ".txt");
        $zipFile->close();
    }
    if(!is_file($file['zip'])){
        $msg = "Errore nella creazione del file zip";
        $err = 1;
    }
}

if(!empty($err)){
    echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$elaborationId."&msg=".$msg."&error=".$err."' </script>";
    die;
}

include_once CLS."/cls_inipec.php";
$ws_inipec = new cls_inipec($a_credentials["UserName"], $a_credentials["Password"]);
$ws_inipec->richiesta($file['zip']);
$response = $ws_inipec->a_check;
$msg = $response['code']." - ".$response['msg'];
if($response['esito']){
    $err = 0;

    $a_save = array(
        "UserName" => $a_credentials["UserName"],
        "CC" => $a_elaboration["CC"],
        "Elaboration_Id" => $elaborationId,
        "IdRichiesta" => $response['richiesta']->idRichiesta,
        "EsitoRichiesta" => $response['code'],
        "DataRichiesta" => (new DateTime())->format("Y-m-d H:i:s")
    );

    $cls_db->DbSave($cls_db->GetObjectQuery("ini_pec_request",$a_save));

    foreach ($a_inipec as $key=>$a_cf){
        $a_saveRequest = array(
            "CodiceFiscale" => $a_cf['CF_PI'],
            "IdRichiesta" => $response['richiesta']->idRichiesta,
            "UserName" => $a_credentials["UserName"]
        );

        $cls_db->DbSave($cls_db->GetObjectQuery("ini_pec_request_pec",$a_saveRequest));

        $query = "UPDATE utente SET InipecLoaded = '".date('Y-m-d')."' ";
        $query.= "WHERE Codice_Fiscale = '".$a_saveRequest["CodiceFiscale"]."' OR Partita_Iva = '".$a_saveRequest["CodiceFiscale"]."'";
        $cls_db->ExecuteQuery($query);

    }

    unlink($file['txt']);
    unlink($file['zip']);

}
else
    $err = 1;

    if($err == 0){
        $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$a_elaboration['CC']."'") );
        $nome_ente = $ente['Denominazione'];
    
        $doc_type = "";
        
        switch($a_elaboration['Document_Type_Id']){
            case 2:
                $doc_type = "Ingiunzioni";
                break;
            case 3:
                $doc_type = "Solleciti di pagamento";
                break;
            case 4:
                $doc_type = "Avvisi d'intimazione";
                break;
            case 7:
                $doc_type = "Pignoramenti presso datore di lavoro";
                break;
            case 8:
                $doc_type = "Pignoramenti presso banca";
                break;
            case 11:
                $doc_type = "Solleciti pre ingiunzione";
                break;
            case 12:
                $doc_type = "Avvisi di messa in mora";
                break;
            case 22:
                $doc_type = "Preavvisi fermo amministrativo";
                break;
        }
    
        $storico->insRow('E', "Inviate richieste INIPEC elaborazione ".$a_elaboration['Description'].": ".$doc_type." ".$ente['Denominazione']."[".$a_elaboration['CC']."]");
    }

echo "<script>location.href = 'ws_inipec.php?c=".$c."&a=".$a."&el=".$elaborationId."&msg=".$msg."&error=".$err."' </script>";