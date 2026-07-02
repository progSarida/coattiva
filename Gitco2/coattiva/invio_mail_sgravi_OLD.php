<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

if($_SESSION['username']==NULL)
{
    header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";
include_once CLS."/cls_LOG.php";
include_once CLS."/cls_Utils.php";
include_once CLS."/cls_phpmailer.php";

$log = new LOG();
$cls_db = new cls_db();
$cls_help = new cls_help();
$Utils = new cls_Utils();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$p = $cls_help->getVar("p");

$last_act = $cls_help->getVar("last_act");
$calling_page = $cls_help->getVar("calling_page");
$modInvio = $cls_help->getVar("modalita_invio");
$Partita_ID = $cls_help->getVar("Partita_ID");
$Tipo = $cls_help->getVar("tipo");
$TipoPDF = $cls_help->getVar("tipoPdf");

$query = "SELECT Indirizzo_Email AS Address, Nome_Visualizzato AS PublicName, Server_Posta_Uscita AS OutMailServer, Porta_Uscita AS OutMailPort, 
                    Protocollo_Uscita AS OutMailProtocol, Sicurezza_Connessione AS ConnectionSafety, Autenticazione_Uscita AS OutAuthentication, 
                    Nome_Utente_Uscita AS OutUsername, Password_Uscita AS OutPassword
                    FROM parametri_email WHERE CC = '".$c."' AND Tipo_Email = 'PEC' AND Tipo_Riscossione = 'GENERALE' LIMIT 1";
$par_email = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_email");

if($par_email->Address=="")
{
    $cls_help->alert("INVIO TRAMITE PEC: L'indirizzo PEC da cui deve essere spedita la richiesta non e' stato inserito! Impossibile procedere con la stampa.");
    //echo "<script>window.close();</script>";
}

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$query = "SELECT Utente_ID FROM partita_tributi WHERE ID = ".$Partita_ID;
$result_PT = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi");

$a_utente = array();
if($result_PT["Utente_ID"] != null) {
    $query = "SELECT * FROM utente WHERE ID = '" . $result_PT["Utente_ID"] . "' AND CC_Comune = '" . $c . "'";
    $a_utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
}else
{
    $log->error("Errore dati utente non trovati");
}

$query = "SELECT Anno_Cronologico, ID_Cronologico FROM atto WHERE ID = ".$last_act;
$atto = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");


$msg = "Dati inviati correttamente";
$error = 0;

if ($modInvio == "posta"){
    $tipo_EU = "";
    if($TipoPDF=='ente') $tipo_EU = "Ente";
    else if($TipoPDF=='utente') $tipo_EU = "Utente";

    $query = "UPDATE sgravio SET Tipo_Spedizione_".$tipo_EU." = '".$modInvio."', Data_Spedizione_".$tipo_EU." = '".date("Y-m-d")."' WHERE Partita_ID = ".$Partita_ID." AND Tipo = ".$Tipo;
    if(!$cls_db->ExecuteQuery($query)){
        $log->error("Errore impossibile aggiornare i dati a DB");
        $msg = "Mail Inviata, ma impossibile aggiornare i dati a DB";
        $error = 1;
    }

}
else{
    //for($i=0;$i<2;$i++) {

        $subject = "Chiusura pratica per ";
        if($Tipo == 1) $subject .= "sgravio";
        else $subject .= "annullamento";

        $subject .= " RIF_".$c;
        if($TipoPDF=="ente") $subject .= "-ENTE";
        else $subject .= "-UT".$result_PT["Utente_ID"];

        $subject .= "-PA".$Partita_ID;
        if($Tipo == 1) $subject .= "-SG";
        else $subject .= "-AN";

        $body = "Chiusura pratica Numero: ".$atto["ID_Cronologico"]."/".$atto["Anno_Cronologico"];

        $cls_mail = new cls_phpmailer((array) $par_email);

//Variabile aggiunta da me in phpMailer, (nelle versioni nuove della libreria ci dovrebbe già essere)
        $cls_mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $a_params = array(
            "subject" => $subject,
            "body" => $body
        );

        $cls_mail->mailCreation($a_params);

        if ($modInvio == "PEC") {

            /*
             * if($i==0) $cls_mail->addAddress($a_enteAdmin["Info_PEC"]);
            else $cls_mail->addAddress($a_utente["PEC"]);//OLD


            if($TipoPDF=='ente') $cls_mail->addAddress($a_enteAdmin["Info_PEC"]);
            else if($TipoPDF=='utente') $cls_mail->addAddress($a_utente["PEC"]);
            else $log->error("Tipo ente/utente non presente");
            */
            $PEC = "";
            if($TipoPDF=='ente') {
                $PEC = $a_enteAdmin["Info_PEC"];
                //$Test = null;
                //$cls_mail->addAddress($Test);
                $cls_mail->addAddress("ufficiale.riscossione@pec.it");
            }
            else if($TipoPDF=='utente') {
                $PEC = $a_utente["PEC"];
                //$Test = null;
                //$cls_mail->addAddress($Test);
                $cls_mail->addAddress("ufficiale.riscossione@pec.it");
            }
            else $log->error("Tipo ente/utente non presente");


        } else if ($modInvio = "email") {

            /*if($i==0) $cls_mail->addAddress($a_enteAdmin["Info_Mail"]);
            else $cls_mail->addAddress($a_utente["Mail"]);//OLD

            if($TipoPDF=='ente') $cls_mail->addAddress($a_enteAdmin["Info_Mail"]);
            else if($TipoPDF=='utente') $cls_mail->addAddress($a_utente["Mail"]);
            else $log->error("Tipo ente/utente non presente");
            */
            $mail = "";
            if($TipoPDF=='ente') {
                $mail = $a_enteAdmin["Info_Mail"];
                $cls_mail->addAddress("virdis.gianluca1989@gmail.com");
            }
            else if($TipoPDF=='utente') {
                $mail = $a_utente["Mail"];
                $cls_mail->addAddress("virdis.gianluca.89@gmail.com");
            }
            else $log->error("Tipo ente/utente non presente");
        }

        $query = "SELECT * FROM sgravio WHERE Partita_ID = ".$Partita_ID." AND Tipo = ".$Tipo;
        $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

        if($TipoPDF=='ente') $cls_mail->addAttachment($result["File_2"]);
        else if($TipoPDF=='utente') $cls_mail->addAttachment($result["File_1"]);
        else $log->error("Tipo ente/utente non presente");

        $cls_mail->preSend();
        $message = $cls_mail->getSentMIMEMessage();


        if ($cls_mail->send()) {
            //$log->warning("Mail inviata.");
            $log->info("Mail inviata. \nIndirizzo: " . $par_email->Address . "\nOutMailServer: " . $par_email->OutMailServer . "\nOutMailPort: " . $par_email->OutMailPort . "\nOutMailProtocol: " . $par_email->OutMailProtocol . "\nOutUsername: " . $par_email->OutUsername . "\nPassword: " . $par_email->OutPassword);
            //die;

            $tipo_EU = "";
            if($TipoPDF=='ente') $tipo_EU = "Ente";
            else if($TipoPDF=='utente') $tipo_EU = "Utente";

            $query = "UPDATE sgravio SET Tipo_Spedizione_".$tipo_EU." = '".$modInvio."', Data_Spedizione_".$tipo_EU." = '".date("Y-m-d")."' WHERE Partita_ID = ".$Partita_ID." AND Tipo = ".$Tipo;
            if(!$cls_db->ExecuteQuery($query)){
                $log->error("Errore impossibile aggiornare i dati a DB");
                $msg = "Mail Inviata, ma impossibile aggiornare i dati a DB";
                $error = 1;
            }

            if ($modInvio == "PEC")
            {
                $save = new stdClass();

                $save->Partita_ID = $Partita_ID;
                $save->Utente_ID = $result_PT["Utente_ID"];
                $save->Oggetto = $subject;
                $save->CC = $c;
                $save->Mail_Sorgente = $par_email->Address;
                $save->Tipo_Sorgente = 'PEC';
                $save->Mail_Destinatario = $PEC;
                $save->Tipo_Destinatario = $modInvio;
                $save->Data_Invio = date("Y-m-d");
                $save->Ricevuta_Accettazione = 'attesa';
                $save->Ricevuta_Consegna = 'attesa';
                $save->Table_Collegata = 'sgravio';
                $save->ID_Collegato = $result["ID"];

                $newID = $cls_db->DbSave($Utils->GetObjectQuery($save,"email_inviate"));

                if(!$newID)
                {
                    $error = 1;
                    $log->error("Errore salvataggio dati della mail ({$subject}).");
                    $msg = "Errore salvataggio dati della mail. ";
                }
                else{
                    if($TipoPDF=='ente') {
                        $query = "UPDATE sgravio SET ID_Invio_Ente = ".$newID." WHERE Partita_ID = ".$Partita_ID." AND Tipo = ".$Tipo;
                        $cls_db->ExecuteQuery($query);
                    }
                    else if($TipoPDF=='utente') {
                        $query = "UPDATE sgravio SET ID_Invio_Utente = ".$newID." WHERE Partita_ID = ".$Partita_ID." AND Tipo = ".$Tipo;
                        $cls_db->ExecuteQuery($query);
                    }
                }
            }
        } else {

            $msg = "Errore impossibile inviare la mail.";
            $error = 1;

            if ($modInvio = "email"){
                if($mail == "" || $mail == null) $msg .= " Mail inesistente";
                else $msg .= " Mail errata";
            }else if($modInvio == "PEC"){
                if($PEC == "" || $PEC == null) $msg .= " PEC inesistente!";
                else $msg .= " PEC errata!";
            }

            $log->error("Errore, mail non inviata. \nIndirizzo: " . $par_email->Address . "\nOutMailServer: " . $par_email->OutMailServer . "\nOutMailPort: " . $par_email->OutMailPort . "\nOutMailProtocol: " . $par_email->OutMailProtocol . "\nOutUsername: " . $par_email->OutUsername . "\nPassword: " . $par_email->OutPassword . "\nMail ricevente: " . $PEC . "\n" . $cls_mail->mailboxGetErrors());
            //die;
        }
    //}
}


header("Location: annulamento_sgravi.php?c={$c}&a={$a}&calling_page={$calling_page}&last_act={$last_act}&partita={$Partita_ID}&p={$p}&msg={$msg}&error={$error}");