<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include CLASSI . "/ruolo.php";
include CLASSI . "/anagrafe.php";

$p = get_var('p');
$c = get_var('c');
$a = get_var('a');
$invia = get_var('invia_submit');

$id_ufficio = get_var('ID');

$tipo = get_var('giurisdizione');
$sezione = get_var('sezione');
$comune = get_var('comune');
$prov = get_var('prov');
$cap = get_var('cap');
$CC = get_var('CC');
$toponimo = get_var('via');
$interno = get_var('interno');
$civico = get_var('civico');
$esponente = get_var('esponente');
$dettagli = get_var('dettagli');
$tel = get_var('tel');
$fax = get_var('fax');


$field_sede = array();
$value_sede = array();

$field_sede[] = "Tipo";							$value_sede[] = $tipo;
$field_sede[] = "CC";							$value_sede[] = $CC;
$field_sede[] = "Sezione";						$value_sede[] = $sezione;
$field_sede[] = "Comune";						$value_sede[] = $comune;
$field_sede[] = "Provincia";					$value_sede[] = $prov;
$field_sede[] = "Cap";							$value_sede[] = $cap;
$field_sede[] = "Toponimo";						$value_sede[] = $toponimo;

if($interno!=null)
{	$field_sede[] = "Interno";						$value_sede[] = $interno;	}

if($civico!=null)
{	$field_sede[] = "Civico";						$value_sede[] = $civico;	}

$field_sede[] = "Esponente";					$value_sede[] = $esponente;
$field_sede[] = "Dettagli";						$value_sede[] = $dettagli;
$field_sede[] = "Telefono";						$value_sede[] = $tel;
$field_sede[] = "Fax";							$value_sede[] = $fax;

switch($invia)
{
	case "Insert":
		
		$control = table_insert_record("ufficio_giudiziario", $field_sede, $value_sede);
		
		if($control != 0)
			echo "OK";
		else 
			echo "FAIL";		
		
		break;
	
	case "Update":
		
		$control = table_update_record("ufficio_giudiziario", $field_sede, $value_sede, "ID", $id_ufficio);
		
		if($control == true)
			echo "OK";
		else
			echo "FAIL";	
		
		break;	
}

?>
