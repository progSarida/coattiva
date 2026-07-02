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

$Oggetto = get_var("Oggetto");
$Sottotitolo_Oggetto = get_var("Sottotitolo_Oggetto");
$Scrivente = get_var("Scrivente");
$Premesso = get_var("Premesso");
$Premesso_Testo = get_var("Premesso_Testo");
$Risultanze_Ufficio = get_var("Risultanze_Ufficio");
$Comunica = get_var("Comunica");
$Comunica_Testo = get_var("Comunica_Testo");
$Informazioni = get_var("Informazioni");
$Informazioni_Testo = get_var("Informazioni_Testo");

if ($invia == "Salva")
{

	$myParametroAtto = new testo_archivizione_atto(null); 
	
	$myParametroAtto->ID = null;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Oggetto = $Oggetto;
	$myParametroAtto->Sottotitolo_Oggetto = $Sottotitolo_Oggetto;
	$myParametroAtto->Scrivente = $Scrivente;
	$myParametroAtto->Premesso = $Premesso;
	$myParametroAtto->Premesso_Testo = $Premesso_Testo;
	$myParametroAtto->Risultanze_Ufficio = $Risultanze_Ufficio;
	$myParametroAtto->Comunica = $Comunica;
	$myParametroAtto->Comunica_Testo = $Comunica_Testo;
	$myParametroAtto->Informazioni = $Informazioni;
	$myParametroAtto->Informazioni_Testo = $Informazioni_Testo;

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