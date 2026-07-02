<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');

$CC = get_var('CC');
$CC_uff = get_var('CC_uff');
$ID_uff = get_var('ID_uff');
$invia = get_var('invia_submit');

$tipo = get_var('tipo_uff');

$ufficio_sede = get_var('ufficio_sede');
$prov = get_var('prov');
$cap = get_var('cap');
$sezione = get_var('sezione');

$via = get_var('via');
$civico = get_var('civico');
$esponente = get_var('esponente');
$interno = get_var('interno');
$dettagli = get_var('dettagli');
$tel = get_var('tel');
$fax = get_var('fax');
$email = get_var('email');
$sito = get_var('sito');
$PEC = get_var('PEC');


if( $invia == "Delete" )
{
	mysql_query('BEGIN');
	
	$cancella = new ufficio_giudiziario($CC, $tipo);
	
	$control_cancella = $cancella->Delete();

	if ($control_cancella)
	{
		mysql_query('COMMIT');
		echo "DELETE";
	}
	else
	{
		echo "ERROR ".mysql_error();
	
		mysql_query('ROLLBACK');
	}
}
else if($invia == "Insert" || $invia == "Update")
{
	mysql_query('BEGIN');
	
	$salva = new ufficio_giudiziario($CC, $tipo);
	
	$salva->CC = $CC;
	$salva->CC_Ufficio = $CC_uff;
	$salva->Tipo = $tipo;
	$salva->Comune = $ufficio_sede;
	$salva->Provincia = $prov;
	$salva->Cap = $cap;
	$salva->Sezione = $sezione;
	$salva->Toponimo = $via;
	$salva->Civico = $civico;
	$salva->Esponente = $esponente;
	$salva->Interno = $interno;
	$salva->Dettagli = $dettagli;
	$salva->Telefono = $tel;
	$salva->Fax = $fax;
	$salva->Mail = $email;
	$salva->Sito = $sito;
	$salva->PEC = $PEC;

	
	if($invia == "Insert")
		$control_salva = $salva->Insert(true);
	else 
		$control_salva = $salva->Update($ID_uff,true);
	
	if ($control_salva)
	{
		echo "SAVED";		
		mysql_query('COMMIT');
	}
	else
	{
		echo "ERROR ".mysql_error();
	
		mysql_query('ROLLBACK');	
	}
	
}

?>