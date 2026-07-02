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

$Oggetto = get_var('Oggetto');
$Premessa = get_var('Premessa');
$Informazioni = get_var('Informazioni');
$Richiesta_Certificato = get_var('Richiesta_Certificato');
$Contatti = get_var('Contatti');
$Informativa_Richiesta = get_var('Informativa_Richiesta');
$Saluti = get_var('Saluti');
$Avvertenze = get_var('Avvertenze');
$Intestatario = get_var('Intestatario_Firma');
$Firma = get_var('Firma');

if ($invia == "Salva")
{

	$myParametroAtto = new testo_richiesta_decesso(null); 
	
	$myParametroAtto->ID = null;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	
	$myParametroAtto->Oggetto = $Oggetto;
	$myParametroAtto->Premessa = $Premessa;
	$myParametroAtto->Informazioni = $Informazioni;
	$myParametroAtto->Richiesta_Certificato = $Richiesta_Certificato;
	$myParametroAtto->Contatti = $Contatti;
	$myParametroAtto->Informativa_Richiesta = $Informativa_Richiesta;
	$myParametroAtto->Saluti = $Saluti;
	$myParametroAtto->Avvertenze = $Avvertenze;
	$myParametroAtto->Intestatario_Firma = $Intestatario;
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