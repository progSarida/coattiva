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

$par_id = get_var('par_id');

$invia = get_var('invia_submit');

$tipo_protocollo = get_var('tipo_protocollo');
$fisso_protocollo = get_var('fisso_protocollo');

$par_atto = new parametri_pignoramento($c);

if($invia == "Salva")
{	
	$par_atto->CC = $c;
	$par_atto->Tipo_Protocollo = $tipo_protocollo;
	$par_atto->Fisso_Protocollo = $fisso_protocollo;
	
	if($par_id == 0)
	{		
		mysql_query('BEGIN');
			
		$control_salva = $par_atto->Insert();
		
		if($control_salva)
		{
			mysql_query('COMMIT');
			echo "SAVED insert";
		}
		else 
		{
			mysql_query('ROLLBACK');
			echo "ERROR";
		}
	
	}
	else 
	{
		mysql_query('BEGIN');
			
		$control_salva = $par_atto->Update($par_id);
		
		if($control_salva)
		{
			mysql_query('COMMIT');
			echo "SAVED update";
		}
		else
		{
			mysql_query('ROLLBACK');
			echo "ERROR";
		}
	}
}
else if( $invia == "Delete" )
{
	
	mysql_query('BEGIN');
	
	$control = $par_atto->Delete();
	
	if($control)
	{
		mysql_query('COMMIT');
		echo "DELETED";
	}
	else
	{
		mysql_query('ROLLBACK');
		echo "ERROR";
	}
	
}
?>