<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";


if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');

$invia = get_var('invia_submit');
$sede_id = get_var('sede_id');
$tipo_sede = get_var('tipo_sede');

$CF = get_var('CF');
$PI = get_var('PI');
$CC = get_var('CC');
$comune = get_var('comune');

$denominazione = get_var('denom');
$email = get_var('email');
$sito = get_var('sito');
$PEC = get_var('PEC');
$tel = get_var('tel');
$fax = get_var('fax');
$via = get_var('via');
$civico = get_var('civico');
$esponente = get_var('esponente');
$interno = get_var('interno');
$dettagli = get_var('dettagli');
$prov = get_var('prov');
$cap = get_var('cap');
$orario = get_var('orario');


if($invia == "Salva")
{

	$field_ente = array();
	$value_ente = array();
	
	$field_ente[] = "CC";				$value_ente[] = $c;
	$field_ente[] = "CC_Sede";			$value_ente[] = $CC;
	$field_ente[] = "Tipo";				$value_ente[] = $tipo_sede;
	$field_ente[] = "Denominazione";	$value_ente[] = $denominazione;
	$field_ente[] = "Codice_Fiscale";	$value_ente[] = $CF;
	$field_ente[] = "Partita_Iva";		$value_ente[] = $PI;
	$field_ente[] = "Comune";			$value_ente[] = $comune;
	$field_ente[] = "Paese";			$value_ente[] = "Italia";
	$field_ente[] = "Provincia";		$value_ente[] = $prov;
	$field_ente[] = "Mail";				$value_ente[] = $email;
	$field_ente[] = "PEC";				$value_ente[] = $PEC;
	$field_ente[] = "Sito";				$value_ente[] = $sito;
	$field_ente[] = "Telefono";			$value_ente[] = $tel;
	$field_ente[] = "Fax";				$value_ente[] = $fax;
	$field_ente[] = "Toponimo";			$value_ente[] = $via;
	$field_ente[] = "Civico";			$value_ente[] = $civico;
	$field_ente[] = "Esponente";		$value_ente[] = $esponente;
	$field_ente[] = "Interno";			$value_ente[] = $interno;
	$field_ente[] = "Dettagli";			$value_ente[] = $dettagli;
	$field_ente[] = "Cap";				$value_ente[] = $cap;
	$field_ente[] = "Orario";			$value_ente[] = $orario;

	if( $sede_id == 0)
	{		
		mysql_query('BEGIN');
			
		$query = table_insert_record_query( "sede_legale" , $field_ente , $value_ente );
		
		$control = mysql_query($query);
		
		$id_ritorno = mysql_insert_id();
		
		if($control)
		{
			echo "SAVED ".$id_ritorno." insert";
			mysql_query('COMMIT');			
		}
		else 
		{
			echo "ERROR ".mysql_error();
			mysql_query('ROLLBACK');			
		}
	
	}
	else 
	{
		mysql_query('BEGIN');
			
		$query = table_update_record_query( "sede_legale" , $field_ente , $value_ente , "ID" , $sede_id );
		
		$control = mysql_query($query);
		
		if($control)
		{
			echo "SAVED ".$sede_id." update";
			mysql_query('COMMIT');			
		}
		else
		{
			echo "ERROR ".mysql_error();
			mysql_query('ROLLBACK');
		}
	}
}
else if( $invia == "Delete" )
{
	
	mysql_query('BEGIN');
	
	$query = "DELETE FROM sede_legale WHERE ID = '".$sede_id."' AND CC = '".$c."' AND Tipo = '".$tipo_sede."'";
	$control = mysql_query($query);
	
	
	
	if($control)
	{
		echo "DELETED";
		mysql_query('COMMIT');
	}
	else
	{
		echo "ERROR ".mysql_error();
		mysql_query('ROLLBACK');
	}
	
}
?>