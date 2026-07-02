<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_ftp.php");
include(CLS."/cls_phpmailer.php");

define('FTP_HOST', 'ftp.mercurioservice.it');
define('FTP_USER', 'sarida');
define('FTP_PASS', '1ftp4sarida');
$ftp = new cls_ftp(FTP_HOST, FTP_USER, FTP_PASS,true);

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$DocumentType = $cls_help->getVar("docType");
$FileName = $cls_help->getVar("fileName");
$IDFlusso = $cls_help->getVar("idFlusso");
$stampatore = $cls_help->getVar("stampatore");


$a_sender = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM user_emails WHERE User_Id=".$_SESSION['aut_progr']));//1
$a_recipient = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM printer WHERE ID=".$stampatore));
$a_publicReceiving = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM gestore WHERE CC='".$c."'"));

//echo $_SESSION['aut_progr'];
//die;

try{
    $cls_mail = new cls_phpmailer($a_sender);
//$cls_mail->SMTPDebug = 2;
    $a_params = array(
        "subject" => "OGGETTO TEST",
        "body" => "CORPO EMAIL TEST"
    );

    $cls_mail->mailCreation($a_params);

    $cls_mail->addAddress($a_recipient['Email']);

    $mailComune = $a_publicReceiving['Mail'];
    if($mailComune == null)
        $mailComune = $a_publicReceiving['PEC'];
    $cls_mail->addAddress($mailComune);

    //$cls_mail->addAttachment(ROOT."/stampefattura.pdf");

    if($DocumentType == "Ingiunzione")
    {
        $cartella = "Ingiunzioni";
        $prefisso = "Ingiunzione_";
    }
    else if($DocumentType == "Avviso di intimazione ad adempiere")
    {
        $cartella = "Avvisi_di_intimazione";
        $prefisso = "Avviso_di_intimazione_";
    }
    else if($DocumentType == "Sollecito di pagamento")
    {
        $cartella = "Solleciti";
        $prefisso = "Sollecito_";
    }
    else if($DocumentType == "Sollecito pre ingiunzione" || $DocumentType =="SOLL_PRE")
    {
        $cartella = "Solleciti_Pre_Ingiunzione";
        $prefisso = "sollecitoPreIngiunzione_";
    }
    else if($DocumentType == "Avviso di messa in mora" || $DocumentType =="AV_MORA")
    {
        $cartella = "Avvisi_Messa_In_Mora";
        $prefisso = "avvisoMessaInMora_";
    }
    else if($DocumentType == "Pignoramento di beni mobili registrati" || $DocumentType == "veicolo")
    {
        $cartella = "Pignoramenti/Veicolo";
        $prefisso = "PignoramentoVeicolo_";
    }
    else if($DocumentType == "Pignoramento presso banca" || $DocumentType == "banca")
    {
        $cartella = "Pignoramenti/Presso_Terzi/Banca";
        $prefisso = "PignoramentoBanca_";
    }
    else if($DocumentType == "Pignoramento presso datore di lavoro" || $DocumentType=="lavoro")
    {
        $cartella = "Pignoramenti/Presso_Terzi/Datore_di_Lavoro";
        $prefisso = "PignoramentoLavoro_";
    }

    $file = ATTI . "/" . $c . "/" . $cartella . "/FLUSSI/" . $FileName;

//echo $file."</br>";
    $flag = $ftp->loadFile($file,"/SARIDA_TEST/".$FileName);

    $msg = "File caricati correttamente";
    $error = 0;
    if(!$flag) $error = 1;


//header("Location: info_flussi.php?c={$c}&a={$a}&error={$error}&msg={$msg}&id_flows={$IDFlusso}");
    if($flag)
    {
        $query = "UPDATE flows SET UploadDate = '".date("Y-m-d")."' WHERE Id = ".$IDFlusso;
        $result = $cls_db->ExecuteQuery($query);
        if($result)
        {
            if(!$cls_mail->send()){

                $error = 2;
                $msg = "File caricato, ma errore di spedizione delle mail";
                throw new Exception("File caricato, ma errore di spedizione delle mail");
                //echo "<script>window.location = document.referrer + '&error={$error}&msg={$msg}';</script>";
            }
            //echo "<script>window.location = document.referrer + '&error={$error}&msg={$msg}'; /*window.location.href = 'gestione_stampe.php?c={$c}&a={$a}&printType=html&docType=ING&error={$error}&msg={$msg}';*/</script>";
        }
        else{
            $error = 2;
            $msg = "File caricato, ma aggiornamento data di caricamento non salvata";
            throw new Exception("File caricato, ma aggiornamento data di caricamento non salvata");
            //echo "<script>window.location = document.referrer + '&error={$error}&msg={$msg}';/*window.location.href = 'gestione_stampe.php?c={$c}&a={$a}&printType=html&docType=ING&error={$error}&msg={$msg}';*/</script>";
        }

    }
    else{
        $msg="Errore file non caricato";
        $error = 1;

        throw new Exception("Errore file non caricato");
        //echo "<script>window.location = document.referrer + '&error={$error}&msg={$msg}';/*window.location.href = 'info_flussi.php?c={$c}&a={$a}&error={$error}&msg={$msg}&id_flows={$IDFlusso}';*/</script>";
    }
}
catch(Exception $ex)
{
    if($error == 0) $error = 1;
    echo "<script>window.location = document.referrer + '&error={$error}&msg={$ex->getMessage()}';</script>";
}

echo "<script>window.location = document.referrer + '&error={$error}&msg={$msg}';</script>";

?>