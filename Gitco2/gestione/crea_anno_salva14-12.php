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
$CC_ente = get_var('CC_ente');
$servizio = get_var('servizio');
if($servizio == "COATTIVA" || $servizio == null)
{
	$campo = "Gestione_Coattiva";
	$valore = "Y";
}
else if($servizio == "TARGHEESTERE")
{
	$campo = "Gestione_Targhe_Estere";
	$valore = "Y";
}
else if($servizio == "PUBBLICITA")
{
	$campo = "Gestione_Pubblicita";
	$valore = "Y";
}
$anno = get_var('anno');

//CONTROLLO ANNI GESTITI PER SERVIZIO
$query = "SELECT ID FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
$progr_anno = single_answer_query($query);
$invia = get_var('invia_submit');
if($progr_anno!=null && $invia!="Delete")
	$invia = "Update";
else if ($progr_anno==null && $invia!="Delete")		
	$invia = "Salva";
else 
	$invia = "Delete";

if($invia == "Salva")
{
	
$field_crea = array();
$value_crea = array();
	
$field_crea[] = "CC_Anno";					$value_crea[] = $CC_ente;
$field_crea[] = "Anno";						$value_crea[] = $anno;
$field_crea[] = "$campo";					$value_crea[] = $valore;



mysql_query('BEGIN');
	
$query = table_insert_record_query( "anni_gestiti" , $field_crea , $value_crea );
$control = mysql_query($query);
	
if($control)
{
	$par_annuali = new parametri_annuali( $c, $anno."-01-01", "CDS" );
	
	$control = $par_annuali->controlloParametri( $c, $anno."-01-01", "*****" );

	
	if($control)
	{
		mysql_query('COMMIT');
		echo "SAVED";
	}
	else
	{
		mysql_query('ROLLBACK');
		echo "ERRORPARAMETRI";
	}
}
else
{
	mysql_query('ROLLBACK');
	echo "ERROR";
}

}
else if( $invia == "Update")
{
	$field_update = array();
	$value_update = array();
	
	$field_update[] = "$campo";				$value_update[] = $valore;
	mysql_query('BEGIN');
	
	$query = table_update_record_query( "anni_gestiti" , $field_update , $value_update , 'ID' , $progr_anno );
	$control = mysql_query($query);
	
	if($control)
	{
		mysql_query('COMMIT');
		echo "UPDATED";
	}
	else
	{
		mysql_query('ROLLBACK');
		echo "ERRORUPDATE";
	}
}
else if( $invia == "Delete" )
{
	$query = "SELECT ID FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$progr_anno = single_answer_query($query);
	mysql_query('BEGIN');
	$query = "UPDATE anni_gestiti SET $campo = 'N' WHERE ID = '$progr_anno'";
	$control = mysql_query($query);
	$query = "SELECT Gestione_Coattiva FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$coattiva = single_answer_query($query);
	$query = "SELECT Gestione_Targhe_Estere FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$estere = single_answer_query($query);
	$query = "SELECT Gestione_Pubblicita FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$pubblicita = single_answer_query($query);
	if($coattiva=='N' && $estere=='N' && $pubblicita=='N')
	{
		$query = "DELETE FROM anni_gestiti WHERE CC_Anno = '".$CC_ente."' AND Anno = '".$anno."'";
		$control = mysql_query($query);
	}

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