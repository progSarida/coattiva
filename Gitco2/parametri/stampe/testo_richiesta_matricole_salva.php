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

$Luogo_Data = get_var('Luogo_Data');
$Oggetto = get_var('Oggetto');
$Richiesta = get_var('Richiesta');
$Descrizione = get_var('Descrizione');
$Legge = get_var('Legge');
$PEC = get_var('PEC');
$Saluti = get_var('Saluti');
$Intestazione_Firma = get_var('Intestazione_Firma');
$Firma = get_var('Firma');

if ($invia == "Salva")
{

	$myParametroAtto = new testo_richiesta_matricole(null); 
	
	$myParametroAtto->ID = null;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Luogo_Data = $Luogo_Data;
	$myParametroAtto->Oggetto = $Oggetto;
	$myParametroAtto->Richiesta = $Richiesta;
	$myParametroAtto->Descrizione = $Descrizione;
	$myParametroAtto->Legge = $Legge;
	$myParametroAtto->PEC = $PEC;
	$myParametroAtto->Saluti = $Saluti;
	$myParametroAtto->Intestazione_Firma = $Intestazione_Firma;
	$myParametroAtto->Firma = $Firma;

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