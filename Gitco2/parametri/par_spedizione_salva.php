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
$tipo_riscossione = get_var('tipo_riscossione');

$invia = get_var('invia_submit');

$Invio_Terzi = get_var('terzi_invio');
$Invio_Pignorato = get_var('pignorato_invio');
$Invio_Richiesta_Validazione = get_var('validazione_invio');

$par = new parametri_spedizione($c, $tipo_riscossione);

if($invia == "Salva")
{	
	
	$par->CC = $c;
	$par->Tipo_Riscossione = $tipo_riscossione;	
	$par->Invio_Pignorato = $Invio_Pignorato;
	$par->Invio_Terzi = $Invio_Terzi;
	$par->Invio_Richieste_Validazione = $Invio_Richiesta_Validazione;
	
	mysql_query('BEGIN');
	
	if($par->ID == null)
		$control_salva = $par->Insert();
	else
		$control_salva = $par->Update($par->ID);
	
	if($control_salva)
	{
		mysql_query('COMMIT');
		echo "SAVED insert";
	}
	else
	{
		echo "ERROR ".mysql_error();
		mysql_query('ROLLBACK');

	}
	
}
else if( $invia == "Delete" )
{
	mysql_query('BEGIN');
	
	$control = $par->Delete();
	
	if($control)
	{
		mysql_query('COMMIT');
		echo "DELETED";
	}
	else
	{
		echo "ERROR ".mysql_error();
		mysql_query('ROLLBACK');		
	}	
}
?>