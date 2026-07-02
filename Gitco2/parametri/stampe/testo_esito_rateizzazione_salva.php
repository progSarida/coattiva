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

$oggetto = get_var('Oggetto');
$richiesta = get_var('Richiesta');
$richiesta_negata = get_var('Richiesta_Negata');
$richiesta_accolta = get_var('Richiesta_Accolta');
$testo_accolta = get_var('Testo_Accolta');
$firma = get_var('Firma');

if ($invia == "Salva")
{

	$myParametroAtto = new testo_esito_rateizzazione(null); 
	
	$myParametroAtto->ID = null;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Oggetto = $oggetto;
	$myParametroAtto->Richiesta = $richiesta;
	$myParametroAtto->Richiesta_Accolta = $richiesta_accolta;
	$myParametroAtto->Richiesta_Negata = $richiesta_negata;
	$myParametroAtto->Testo_Richiesta_Accolta = $testo_accolta;
	$myParametroAtto->Firma_Incaricato = $firma;

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