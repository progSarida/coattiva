<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');

$invia = get_var('invia_submit');

$Titolo_Oggetto = get_var('titolo_oggetto');
$Sottotitolo_Oggetto = get_var('sottotitolo_oggetto');

$Atti_Notificati = get_var('atti_notificati');
$Sensi_Legge = get_var('sensi_legge');

$Comunica = get_var('comunica');
$Comunica_Testo = get_var('comunica_testo');

$Legale_Rappresentante_Comune = get_var('rappresentante_comune');
$Legale_Rappresentante_Concessionario = get_var('rappresentante_concessionario');

$Veicoli = get_var('veicoli');
$Iscrizione = get_var('iscrizione');
$Sanzioni = get_var('sanzioni');
$Cancellazione = get_var('cancellazione');

$Opposizione = get_var('opposizione');
$Opposizione_Testo = get_var('opposizione_testo');

$Autotutela = get_var('autotutela');
$Autotutela_Testo = get_var('autotutela_testo');

$firma_notifica = get_var('firma_notifica');
$qual_firma_notifica = get_var('qualifica_firma_notifica');

if ($invia == "Salva")
{

	$myParametroAtto = new testo_fermo_amministrativo(NULL); 
	
	$myParametroAtto->ID = NULL;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	
	$myParametroAtto->Titolo_Oggetto = $Titolo_Oggetto;
	$myParametroAtto->Sottotitolo_Oggetto = $Sottotitolo_Oggetto;
	
	$myParametroAtto->Atti_Notificati = $Atti_Notificati;
	$myParametroAtto->Sensi_Legge = $Sensi_Legge;
	
	$myParametroAtto->Comunica = $Comunica;
	$myParametroAtto->Comunica_Testo = $Comunica_Testo;
	
	$myParametroAtto->Legale_Rappresentante_Comune = $Legale_Rappresentante_Comune;
	$myParametroAtto->Legale_Rappresentante_Concessionario = $Legale_Rappresentante_Concessionario;
	
	$myParametroAtto->Veicoli = $Veicoli;
	$myParametroAtto->Iscrizione = $Iscrizione;
	$myParametroAtto->Sanzioni = $Sanzioni;
	$myParametroAtto->Cancellazione = $Cancellazione;
	
	$myParametroAtto->Opposizione = $Opposizione;
	$myParametroAtto->Opposizione_Testo = $Opposizione_Testo;

	$myParametroAtto->Autotutela = $Autotutela;
	$myParametroAtto->Autotutela_Testo = $Autotutela_Testo;
	
	$myParametroAtto->Firma_Notifica = $firma_notifica;
	$myParametroAtto->Qualifica_Firma_Notifica = $qual_firma_notifica;
	
	mysql_query('BEGIN');
	
	
	$risultato = $myParametroAtto->InsertOrUpdatesParametroAtto(true);
	
	if ($risultato)
	{
		mysql_query('COMMIT');
		echo "SAVED";
	}
	else 
	{
		echo "ERROR ".mysql_error();
		
		mysql_query('ROLLBACK');
		
		
	}
}
else echo "ambaraba";
?>