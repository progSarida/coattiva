<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include CLASSI. "/comuni.php";
include CLASSI. "/parametri.php";
include CLASSI. "/ruolo.php";
include CLASSI. "/anagrafe.php";
include CLASSI. "/coazione.php";
include CLASSI. "/classe_email.php";
require_once CLASSI. "/php-imap-client-master/Imap.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$c = get_var('c');
$a = get_var('a');
$tipo_partita = get_var('tipo_partita');
$par = new parametri_email($c, $tipo_partita, 'PEC');

$query = "SELECT email_inviate.ID FROM email_inviate JOIN partita_tributi ON partita_tributi.ID = email_inviate.Partita_ID AND partita_tributi.Tipo = '".$tipo_partita."' ";
$query.= "WHERE email_inviate.CC='".$c."' AND email_inviate.Tipo_Sorgente = 'PEC' AND ( email_inviate.Ricevuta_Accettazione='attesa' ";
$query.= "OR email_inviate.Ricevuta_Accettazione='fallita' OR email_inviate.Ricevuta_Consegna='attesa' OR email_inviate.Ricevuta_Consegna='fallita' ) ORDER BY email_inviate.ID ASC";

$result = mysql_query($query) or die(mysql_error());

$imap = new Imap($par);
$imap->selectFolder('INBOX');
// stop on error
if ($imap->isConnected() === false) {
    echo "ERROR " . $imap->getError();
    die;
}
$ANOMALIA = imap_search($imap->imap, 'SUBJECT "ANOMALIA MESSAGGIO:"', SE_UID);
$stringa = "";
while( $row = mysql_fetch_array($result)){

    $stringa.= $row['ID']."***";
    continue;

    $email = new email_inviate($row['ID']);
    $path_mail = $email->percorsoMail($c, 'PEC', $email->Oggetto, "server");

    $notifica_singola = new notifica_atto($email->ID_Collegato, $c);

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

    $SUBJECT = imap_search($imap->imap, 'SUBJECT "' . $email->Oggetto . '"', SE_UID);

    for ($i = 0; $i < count($SUBJECT); $i++) {
        if ($SUBJECT[$i] == "")
            continue;

        $msgno = imap_msgno($imap->imap, $SUBJECT[$i]);

        $header = imap_headerinfo($imap->imap, $msgno);
        $body = imap_body($imap->imap, $SUBJECT[$i], FT_UID);
        $structure = imap_fetchstructure($imap->imap, $SUBJECT[$i], FT_UID);

        $subject_file = str_replace(":", "", $header->Subject);
        $subject_file = str_replace("'", "_", $subject_file);
        $subject_file = str_replace(" ", "_", $subject_file);

        $nome_file = $path_mail . '/' . $subject_file . '.eml';

        $myfile = fopen($nome_file, 'w');
        $testo = imap_fetchbody($imap->imap, $msgno, '');
        fwrite($myfile, $testo);
        fclose($myfile);

        if ($control_accettazione == 0) {
            if (strpos(strtolower($header->Subject), 'avviso di mancata accettazione:') !== false) {
                $accettazione = "mancata";
                $email->Ricevuta_Accettazione = $accettazione;

                if (strtolower($par->Protocollo_Arrivo) == "imap")
                    if (file_exists($nome_file)) ;
                imap_delete($imap->imap, $SUBJECT[$i], FT_UID);//CANCELLA DEFINITIVO
            } else if (strpos(strtolower($header->Subject), 'accettazione:') !== false) {
                $accettazione = "ok";
                $email->Ricevuta_Accettazione = $accettazione;

                if (strtolower($par->Protocollo_Arrivo) == "imap")
                    if (file_exists($nome_file)) ;
                imap_delete($imap->imap, $SUBJECT[$i], FT_UID);//CANCELLA DEFINITIVO
            }
        }

        if ($email->Tipo_Destinatario == "PEC" && $control_consegna == 0) {
            if (strpos(strtolower($header->Subject), 'avviso di mancata consegna:') !== false) {
                $consegna = "mancata";
                $email->Ricevuta_Consegna = $consegna;

                if (strtolower($par->Protocollo_Arrivo) == "imap")
                    if (file_exists($nome_file)) ;
                imap_delete($imap->imap, $SUBJECT[$i], FT_UID);//CANCELLA DEFINITIVO
            } else if (strpos(strtolower($header->Subject), 'consegna:') !== false) {
                $consegna = "ok";
                $email->Ricevuta_Consegna = $consegna;

                if (strtolower($par->Protocollo_Arrivo) == "imap")
                    if (file_exists($nome_file)) ;
                imap_delete($imap->imap, $SUBJECT[$i], FT_UID);//CANCELLA DEFINITIVO

                $notifica_singola->Data_Notifica = date('Y-m-d');
                $control_notifica = $notifica_singola->Update($notifica_singola->ID);
            } else {
                for ($y = 0; $y < count($ANOMALIA); $y++) {
                    if ($ANOMALIA[$y] == "")
                        continue;

                    $body_anomalia = imap_body($imap->imap, $ANOMALIA[$y], FT_UID);

                    if (strpos($body_anomalia, $email->Oggetto) !== false) {
                        $consegna = "anomalia";
                        $email->Ricevuta_Consegna = $consegna;

                        if (strtolower($par->Protocollo_Arrivo) == "imap")
                            if (file_exists($nome_file)) ;
                        imap_delete($imap->imap, $ANOMALIA[$y], FT_UID);//CANCELLA DEFINITIVO

                        break;
                    }
                }
            }
        }
    }

    if ($accettazione != "ok" && $accettazione != "attesa" && $accettazione != "mancata")
        $email->Ricevuta_Accettazione = "fallita";

    if ($consegna != "ok" && $consegna != "attesa" && $consegna != "mancata" && $consegna != "anomalia" && $email->Tipo_Destinatario == "PEC")
        $email->Ricevuta_Consegna = "fallita";

    $control_salva = $email->Update($email->ID);
    if($control_salva){
        imap_expunge($imap->imap);
    }
    else
        continue;

}

echo $stringa;

?>