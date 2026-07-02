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
$sottoscritto = get_var('Sottoscritto');
$testoAtto = get_var('Atto_Testo');
$chiedo = get_var('Chiedo');
$chiedoTesto = get_var('Chiedo_Testo');
$condizioni_disagiate = get_var('Condizioni_Disagiate');
$condizione_1 = get_var('Condizione_1');
$condizione_2 = get_var('Condizione_2');
$condizione_3 = get_var('Condizione_3');
$condizione_4 = get_var('Condizione_4');
$condizione_5 = get_var('Condizione_5');

if ($invia == "Salva")
{

	$myParametroAtto = new testo_richiesta_rateizzazione(null); 
	
	$myParametroAtto->ID = null;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Oggetto = $oggetto;
	$myParametroAtto->Sottoscritto = $sottoscritto;
	$myParametroAtto->Atto_Testo = $testoAtto;
	$myParametroAtto->Chiedo = $chiedo;
	$myParametroAtto->Chiedo_Testo = $chiedoTesto;
	$myParametroAtto->Condizioni_Disagiate = $condizioni_disagiate;
	$myParametroAtto->Condizione_1 = $condizione_1;
	$myParametroAtto->Condizione_2 = $condizione_2;
	$myParametroAtto->Condizione_3 = $condizione_3;
	$myParametroAtto->Condizione_4 = $condizione_4;
	$myParametroAtto->Condizione_5 = $condizione_5;

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