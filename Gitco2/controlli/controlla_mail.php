<?php
/*
 *
 * TODO riattivare cancellazione imap
 *
 * */

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC . "/headerAjax.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_crypt.php");
include_once(CLS."/php-imap-client-master/Imap.php");
include_once(CLS."/cls_mail_imap.php");
include_once(CLS."/cls_LOG.php");
include_once CLS . "/cls_storico.php";													// inclusione classe

$storico = new storico('storicoControlliGestione','6');
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$log = new LOG();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$DocumentType = $cls_help->getVar("docType");

$error = 0;
$msg = "Mail elaborate correttamente";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];
?>

    <script>
        function inizio()
        {
            $('#progressbar').progressbar({
                value: false
            });
            $( "#barlabel" ).text("Inizio controllo...");
        }

        function anomalie()
        {
            $('#progressbar').progressbar({
                value: false
            });
            $( "#barlabel" ).text("Ricerca anomalie...");
        }

        function update(valore)
        {
            $( "#progressbar" ).progressbar({value: parseInt(valore) });
            $( "#barlabel" ).text( valore + "%" );
        }

        function nessun_risultato()
        {
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text("Nessun risultato trovato");
        }

        function fine(value)
        {
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text( value );

            sleep(1000);
        }

        function gestione_email()
        {
            $('#pec_form').submit();
        }

    </script>

<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Controllo ricevute PEC</span>
    </div>
</div>
<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>

<?php

set_time_limit(100);

flush();	ob_flush();		flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();

$query = "SELECT * FROM parametri_email WHERE CC = '".$c."' AND Tipo_Email = 'PEC' AND Tipo_Riscossione = 'GENERALE' LIMIT 1";//USARE GENERALE AL POSTO DI CDS
$par = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_email");// new parametri_email($c, 'CDS', 'PEC');

/*
 *
$query = "SELECT email_inviate.*, partita_tributi.Tipo FROM email_inviate JOIN partita_tributi ON partita_tributi.ID = email_inviate.Partita_ID ";
$query.= "WHERE email_inviate.CC='".$c."' AND email_inviate.Tipo_Sorgente = 'PEC' AND ( email_inviate.Ricevuta_Accettazione='attesa' ";
$query.= "OR email_inviate.Ricevuta_Accettazione='fallita' OR email_inviate.Ricevuta_Consegna='attesa' OR email_inviate.Ricevuta_Consegna='fallita' ) ";
 */
$query = "SELECT * FROM email_inviate ";
$query.= "WHERE CC='".$c."' AND Tipo_Sorgente = 'PEC' AND ( Ricevuta_Accettazione='attesa' ";
$query.= "OR Ricevuta_Accettazione='fallita' OR Ricevuta_Consegna='attesa' OR Ricevuta_Consegna='fallita' ) ";

//$query = "SELECT ID, 'CDS' as Tipo FROM email_inviate WHERE ID = 33851";
//echo $query;
//die;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query),"object");// or die($cls_db->GetError());
$totPEC = count($result);
if($totPEC == 0)
{
    echo "<script>nessun_risultato();</script>";
    $error = 2;
    $msg = "Nessun risultato trovato";
    //echo "<script>window.location ='".WEB_ROOT."/stampe/gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$msg}';</script>";
    echo "<script>window.opener.location.replace(document.referrer+'&error={$error}&msg={$msg}');window.close();</script>";
    die;
}

$query = "SELECT * FROM parametri_email WHERE CC = '".$c."' AND Tipo_Email = 'PEC'";
$par = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_email");//new parametri_email($c, null, 'PEC');


try {
    $Reader = new Email_reader($par);
    $Reader->selectFolder('INBOX');

    if ($Reader->isConnected() === false) {
        $log->error("Errore di connessione reader: ".$Reader->getError());
        $error = 1;
        $msg = "Errore di connessione reader";
        //echo "<script>window.location ='".WEB_ROOT."/stampe/gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$msg}';</script>";
        echo "<script>window.opener.location.replace(document.referrer+'&error={$error}&msg={$msg}');window.close();</script>";
        die;
    }
}
catch(Exception $ex)
{
    $log->error($ex->getMessage());
    $error = 1;
    $msg = "READER: ".$ex->getMessage();
    // echo "<script>window.location ='gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$msg}';</script>";
}

try {

$ReaderAn = new Email_reader($par);
$ReaderAn->selectFolder('INBOX');

if ($ReaderAn->isConnected() === false) {
    $log->error("Errore di connessione reader Anomalia: ".$ReaderAn->getError());
    $error = 2;
    $msg = "Errore di connessione reader Anomalia";
    //echo "<script>window.location ='".WEB_ROOT."/stampe/gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$msg}';</script>";
    echo "<script>window.opener.location.replace(document.referrer+'&error={$error}&msg={$msg}');window.close();</script>";
    die;
}


flush();	ob_flush();flush();	ob_flush();

echo "<script>anomalie();</script>";

flush();	ob_flush();		flush();	ob_flush();



    $ReaderAn->inboxSelected('SUBJECT "ANOMALIA MESSAGGIO:"'); //imap_search($imap->imap, 'SUBJECT "ANOMALIA MESSAGGIO:"', SE_UID);
}
catch(Exception $ex)
{
    $log->error($ex->getMessage());
    if($ex->getMessage() == "Mail non trovata, nessun id mail ritornato") $error = 2;
    else $error = 1;
    $msg = "READER ANOMALIE: ".$ex->getMessage();
   // echo "<script>window.location ='gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$msg}';</script>";
}

$ReaderAn = new Email_reader($par);

for($x = 0; $x < count($result); $x++){

    set_time_limit(500);

    flush();
    ob_flush();
    flush();
    ob_flush();

    echo "<script>update('".ceil($x*100/$totPEC)."');</script>";

    flush();
    ob_flush();
    flush();
    ob_flush();

    //$query = "SELECT * FROM email_inviate WHERE ID = '".$result[$x]['ID']."'";
    $email = $result[$x];//= $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"email_inviate");//new email_inviate($row['ID']);

    $path_mail = $ReaderAn->CreateDirMailFile($email,$c, 'PEC', "server");

    $cls_utils->crea_dir($path_mail);



    $query = "SELECT * FROM notifica_atto WHERE ID = '".$email->ID_Collegato."' AND CC = '".$c."'";
    $notifica_singola = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"notifica_atto");// new notifica_atto($email->ID_Collegato, $c);

    $control_accettazione = 0;
    $control_consegna = 0;

    $accettazione = $email->Ricevuta_Accettazione;
    $consegna = $email->Ricevuta_Consegna;

    if ($accettazione != "attesa" && $accettazione != "fallita")
        $control_accettazione = 1;

    if ($consegna != "attesa" && $consegna != "fallita")
        $control_consegna = 1;

    if ($control_accettazione == 1 && $control_consegna == 1)
        continue;


    set_time_limit(500);

    try {
        $Reader->inboxSelected('SUBJECT "'.$email->Oggetto.'"');
    }
    catch(Exception $ex)
    {
        $log->info("Nessuna corrispondenza con l'oggetto della mail trovata, mail non considerata");
        continue;
    }

    for ($i = 0; $i < $Reader->length(); $i++) {

        $mailDownloaded = $Reader->get($i);

        if(substr($mailDownloaded["header"]->Subject,0,2)=="=?")
            $subject_header = iconv_mime_decode($mailDownloaded["header"]->Subject,0,"UTF-8");
        else
            $subject_header = $mailDownloaded["header"]->Subject;

        $subject_file = str_replace(" =?ISO-8859-1?Q?", "", $subject_header);
        $subject_file = str_replace("?=", "", $subject_file);
        $subject_file = str_replace("=5F", "_", $subject_file);

        $control_oggetto = explode("_",$email->Oggetto);
        $control_mail_oggetto = explode("_", $subject_file);

        /*var_dump($control_oggetto);
        echo "<br><br>";
        var_dump($control_mail_oggetto);
        die;*/

        if($control_oggetto[count($control_oggetto)-1] != $control_mail_oggetto[count($control_mail_oggetto)-1])
        {
            $log->info("Oggetto diverso, mail con oggetto, ".$subject_file.",  non considerata");
            continue;
        }

        $subject_file = str_replace(":","_",$subject_file);
        $nome_file = $path_mail."/".$subject_file.".eml";

        $Reader->CreateMailFile($i,$nome_file);

        if($control_accettazione==0)
        {
            if(strpos(strtolower($subject_header), 'avviso di mancata accettazione:') !== false)
            {
                $log->info("Mail trovata e classificata come Mancata Accettazione!\n(Oggetto mail: ".$email->Oggetto.")");
                $accettazione = "mancata";
                $email->Ricevuta_Accettazione = $accettazione;

                if(strtolower($par->Protocollo_Arrivo) == "imap")
                    if(file_exists($nome_file)){
                        /******************************* da decommentare ***************************************/
                     //   $Reader->markDisposable($i);
                    }
                        /******************************* da decommentare ***************************************/
                        //imap_delete($imap->imap, $uid, FT_UID);//CANCELLA DEFINITIVO -- questo NON è da decommentare
            }
            else if(strpos(strtolower($subject_header), 'accettazione:') !== false)
            {
                $log->info("Mail trovata e classificata come Accettazione!\n(Oggetto mail: ".$email->Oggetto.")");
                $accettazione = "ok";
                $email->Ricevuta_Accettazione = $accettazione;

                if(strtolower($par->Protocollo_Arrivo) == "imap")
                    if(file_exists($nome_file)){
                        /******************************* da decommentare ***************************************/
                      //  $Reader->markDisposable($i);
                    }
                        /******************************* da decommentare ***************************************/
                        //imap_delete($imap->imap, $uid, FT_UID);//CANCELLA DEFINITIVO -- questo NON è da decommentare
            }
        }

        if($email->Tipo_Destinatario=="PEC" && $control_consegna == 0) {
            if (strpos(strtolower($subject_header), 'avviso di mancata consegna:') !== false) {

                $log->info("Mail trovata e classificata come Mancata Consegna!\n(Oggetto mail: ".$email->Oggetto.")");
                $consegna = "mancata";
                $email->Ricevuta_Consegna = $consegna;

                if (strtolower($par->Protocollo_Arrivo) == "imap")
                    if (file_exists($nome_file)){
                        /******************************* da decommentare ***************************************/
                       // $Reader->markDisposable($i);
                    }
                        /******************************* da decommentare ***************************************/
                        //imap_delete($imap->imap, $uid, FT_UID);//CANCELLA DEFINITIVO -- questo NON è da decommentare
            } else if (strpos(strtolower($subject_header), 'consegna:') !== false) {

                $log->info("Mail trovata e classificata come Consegna!\n(Oggetto mail: ".$email->Oggetto.")");
                $consegna = "ok";
                $email->Ricevuta_Consegna = $consegna;

                if (strtolower($par->Protocollo_Arrivo) == "imap")
                    if (file_exists($nome_file)){
                        /******************************* da decommentare ***************************************/
                       // $Reader->markDisposable($i);
                    }
                        /******************************* da decommentare ***************************************/
                        //imap_delete($imap->imap, $uid, FT_UID);//CANCELLA DEFINITIVO -- questo NON è da decommentare

                $notifica_singola->Data_Notifica = date('Y-m-d');
                $control_notifica = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $notifica_singola,"notifica_atto",array("ID" => $notifica_singola->ID)));

                if(!$control_notifica) $log->error("Aggiornamento tabella notifica_atto, colonna Data_Notifica, ID = ".$notifica_singola->ID." fallita!");
            }
        }
    }

    for($y=0;$y<$ReaderAn->length();$y++)
    {
        if($consegna== "ok")
            break;

        $valueAnomalia = $ReaderAn->get($y);

        if(strpos($valueAnomalia["body"], $email->Oggetto) !== false)
        {
            $nome_file_anomalia = $path_mail."/ANOMALIA_".$email->Oggetto.".eml";

            $ReaderAn->CreateMailFile($y,$nome_file_anomalia);

            $consegna = "anomalia";
            $email->Ricevuta_Consegna = $consegna;

            if(strtolower($par->Protocollo_Arrivo) == "imap")
                if(file_exists($nome_file_anomalia)){
                    /******************************* da decommentare ***************************************/
                   // $ReaderAn->markDisposable($y);
                }
                    /******************************* da decommentare ***************************************/
                    //imap_delete($imap->imap, $ReaderAn->get($y)/*$ANOMALIA[$y]*/, FT_UID);//CANCELLA DEFINITIVO -- questo NON è da decommentare

            break;
        }
    }

    switch($accettazione){
        case "ok":
            break;
        case "attesa":
            break;
        case "mancata":
            break;
        default:
            $email->Ricevuta_Accettazione = "fallita";
            break;
    }

    if($email->Tipo_Destinatario=="PEC"){
        switch($consegna){
            case "ok":
                break;
            case "attesa":
                break;
            case "mancata":
                break;
            case "anomalia":
                break;
            default:
                $email->Ricevuta_Consegna = "fallita";
                break;
        }
    }

    $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $email,"email_inviate",array( "ID" => $email->ID)));

    if($control_salva){
        /******************************* da decommentare ***************************************/
        //$Reader->Delete();
        //$ReaderAn->Delete();
        //echo $email->ID."<br>";
    }
    else{
        $log->error("Aggiornamento tabella email_inviate, ID = ".$email->ID." fallita!". $control_salva);
        continue;
    }
}

echo "<script>fine('Controllo PEC effettuato!');</script>";

;


//echo "<script>window.location ='".WEB_ROOT."/stampe/gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$msg}';</script>";
//echo "<script>
//
// = document.referrer+'&error={$error}&msg={$msg}';</script>";
//echo "<script>window.onunload = window.opener.location.reload() +'&error={$error}&msg={$msg}';window.close();</script>";
if($error == 0)
    $storico->insRow('P', "Scaricate PEC ricevute per ente ".$nome_ente."[".$c."]");

echo "<script>window.opener.location.replace(document.referrer+'&error={$error}&msg={$msg}');window.close();</script>";


?>

<?php include(INC."/footer.php"); ?>
