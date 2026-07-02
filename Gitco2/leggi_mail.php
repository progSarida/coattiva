<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include CLASSI. "/parametri.php";
require_once CLASSI. "\php-imap-client-master\Imap.php";

$c = get_var('c');
$a = get_var('a');
$p = get_var('p');
 
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

/**
 * PROVA SMTP
 */

set_time_limit(500);

$par = new parametri_email($c, 'CDS', 'PEC');

$username = $par->Nome_Utente;
$password = decryptIt($par->Password);
$mailbox = $par->Server_Posta_Arrivo;
$porta = $par->Porta_Arrivo;
$protocol = $par->Protocollo_Arrivo;
$encryption = $par->Sicurezza_Connessione; // or ssl or ''
$folder = "INBOX";

// $username = "mirkopas85@gmail.com";
// $password = "striker20031984";
// $mailbox = "imap.googlemail.com";
// $porta = "993";
// $protocol = "imap";
// $encryption = 'ssl'; // or ssl or ''
// $folder = "[Gmail]/Speciali";

// apri connessione
$imap = new Imap($mailbox, $username, $password, $protocol, $porta, $encryption);

// select folder Inbox
$imap->selectFolder($folder);

// stop on error
if($imap->isConnected()===false)
	die($imap->getError());

// get all folders as array of strings
$folders = $imap->getFolders();
foreach($folders as $folder)
	echo $folder;

// fetch all messages in the current folder
$oggetto_bubu = '11022015';
$SUBJECT = imap_search($imap->imap, 'SUBJECT "'.$oggetto_bubu.'"');
// $SUBJECT = imap_search($imap->imap, 'UNSEEN');

$path = crea_dir($_SERVER['DOCUMENT_ROOT'] . "/archivio/eMail/");

for($i=0;$i<count($SUBJECT);$i++)
{
	$path_mail = $path."Mail_".$i;
	$body = nl2br(imap_body($imap->imap, $SUBJECT[$i]));
	$structure = imap_fetchstructure($imap->imap, $SUBJECT[$i]);
	$header = imap_headerinfo($imap->imap, $SUBJECT[$i]);
	
	$string_header = $imap->headerString($header);
	
	$parti = $structure->parts;
	
	$email = $imap->getEmailParts($SUBJECT[$i], $parti);
	
	if(isset($email['Mail']))
	{
		$myfile = fopen($path_mail.'mail.html', 'w');
		fwrite($myfile, $string_header."<br>");
		
		for($y=0;$y<count($email['Mail']);$y++)
		{
			$testo = nl2br($email['Mail'][$y]);			
			fwrite($myfile, $testo."<br>");
		}
		fclose($myfile);
	}
	
	for($y=0;$y<count($email['Allegati']['data']);$y++)
	{
		$testo = $email['Allegati']['data'][$y];
		$filename = $email['Allegati']['filename'][$y];
		$myfile = fopen($path_mail.$filename, 'w');
		fwrite($myfile, $testo,strlen($testo));
		fclose($myfile);			
	}
	
}

?>