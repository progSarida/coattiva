<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_enteUtils.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_utils = new cls_enteUtils();





$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$servizio = $cls_help->getVar('servizio');

$invia = $cls_help->getVar('invia_submit');

if($invia == "Salva")
{

$num_enti = $cls_help->getVar('num_enti');

$CC_1 = $cls_help->getVar('CC_1');
$CC_2 = $cls_help->getVar('CC_2');
$CC_3 = $cls_help->getVar('CC_3');
$CC_4 = $cls_help->getVar('CC_4');
$CC_5 = $cls_help->getVar('CC_5');

$comune_1 = $cls_help->getVar('comune_1');
$comune_2 = $cls_help->getVar('comune_2');
$comune_3 = $cls_help->getVar('comune_3');
$comune_4 = $cls_help->getVar('comune_4');
$comune_5 = $cls_help->getVar('comune_5');

$denominazione = $cls_help->getVar('denominazione');

$descrizione = $cls_help->getVar('descrizione');

$control_parametri = 0;
$error = 0;
$msg = "";
$newCC = "";

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if( $num_enti == 1 )
{
	$query = "SELECT CC FROM enti_gestiti WHERE CC LIKE '".$CC_1."%'";
	$control_ente = $cls_db->getNumberRow($cls_db->ExecuteQuery($query));// mysql_query($query);
	//$control_ente = mysql_num_rows($result);

	//$field_crea = array();
	//$value_crea = array();


	if( $control_ente > 0 )
	{
		$newCC = $CC_1 . ($control_ente + 1);

		$a_paramsEnti = array(
				'table' => 'enti_gestiti',
				'fields'=> array(
						array(  'name' => 'CC',        'type' => 'string', 'value' =>  $newCC),
						array(  'name' => 'Denominazione',             'type' => 'string', 'value' => $comune_1 . " " .($control_ente+1)),
						array(  'name' => 'Descrizione',      'type' => 'string', 'value' => $descrizione),
						array(  'name' => 'Info_ID',      'type' => 'string', 'value' => 0),
						array(  'name' => 'Codici_Unione',      'type' => 'string', 'value' => "")
				)
		);

		if(!$cls_db->DbSave($a_paramsEnti))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore inserimento dati non riuscito";
		}
		else
		{
			$control_parametri = 1;
			$msg = "Inserimento dati riuscito";
		}
		/*$field_crea[] = "CC";	      			$value_crea[] = $CC_1 . ($control_ente + 1);
		$field_crea[] = "Denominazione";	$value_crea[] = $comune_1 . " " .($control_ente+1);
		$field_crea[] = "Descrizione";		$value_crea[] = $descrizione;*/
	}
	else
	{
		$newCC = $CC_1;

		$a_paramsEnti = array(
				'table' => 'enti_gestiti',
				'fields'=> array(
						array(  'name' => 'CC',        'type' => 'string', 'value' =>  $newCC),
						array(  'name' => 'Denominazione',             'type' => 'string', 'value' => $comune_1),
						array(  'name' => 'Descrizione',      'type' => 'string', 'value' => $descrizione),
						array(  'name' => 'Info_ID',      'type' => 'string', 'value' => 0),
						array(  'name' => 'Codici_Unione',      'type' => 'string', 'value' => "")
				)
		);
		/*$field_crea[] = "CC";			      	$value_crea[] = $CC_1;
		$field_crea[] = "Denominazione";	$value_crea[] = $comune_1;
		$field_crea[] = "Descrizione";		$value_crea[] = $descrizione;*/
	}

	if(!$cls_db->DbSave($a_paramsEnti))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore inserimento dati non riuscito";
	}
	else
	{
		$control_parametri = 1;
		$msg = "Inserimento dati riuscito";
	}
	/*mysql_query('BEGIN');

	$query = table_insert_record_query( "enti_gestiti" , $field_crea , $value_crea );

	$control = mysql_query($query);

	if($control)
	{
		$control_parametri = 1;
		echo "SAVED";
		mysql_query('COMMIT');
	}
	else
	{
		mysql_query('ROLLBACK');
		echo "ERROR";
	}*/

	$nuovoCodice = $newCC;

	//echo " ".$newCC;

}
else if( $num_enti > 1 )
{
	$field_crea = array();
	$value_crea = array();
	$query = "SELECT MAX(CC) as maxCC FROM enti_gestiti WHERE CC LIKE 'U%'";
	$last_cod = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	//$last_cod = single_answer_query("SELECT MAX(CC) FROM enti_gestiti WHERE CC LIKE 'U%'");
	$num_unione = substr($last_cod["maxCC"], 1) + 1;
	$new_cod = "";

	if(strlen($num_unione)==1) 			$new_cod = "U00".$num_unione;
	else if(strlen($num_unione)==2)		$new_cod = "U0".$num_unione;
	else if(strlen($num_unione)==3)		$new_cod = "U".$num_unione;



	/*$field_crea[] = "CC";				$value_crea[] = $new_cod;
	$field_crea[] = "Denominazione";	$value_crea[] = $denominazione;
	$field_crea[] = "Descrizione";		$value_crea[] = $descrizione;*/

	if( $num_enti > 1 )
	{
		$cod_unione = $CC_1 ."/". $CC_2;
	}
	if( $num_enti > 2 )
	{
		$cod_unione .= "/" . $CC_3;
	}
	if( $num_enti > 3 )
	{
		$cod_unione .= "/" . $CC_4;
	}
	if( $num_enti > 4 )
	{
		$cod_unione .= "/" . $CC_5;
	}


	$a_paramsEnti = array(
			'table' => 'enti_gestiti',
			'fields'=> array(
					array(  'name' => 'CC',        'type' => 'string', 'value' =>  $new_cod),
					array(  'name' => 'Denominazione',             'type' => 'string', 'value' => $denominazione),
					array(  'name' => 'Descrizione',      'type' => 'string', 'value' => $descrizione),
					array(  'name' => 'Codici_Unione',      'type' => 'string', 'value' => $cod_unione)
				//array(  'name' => 'Info_ID',      'type' => 'int', 'value' => "0")
			)
	);

	//$field_crea[] = "Codici_Unione";	$value_crea[] = $cod_unione;

	if(!$cls_db->DbSave($a_paramsEnti))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore inserimento dati non riuscito";
	}
	else
	{
		$control_parametri = 1;
		$msg = "Inserimento dati riuscito";
	}

	/*mysql_query('BEGIN');

	$query = table_insert_record_query( "enti_gestiti" , $field_crea , $value_crea );

	$control = mysql_query($query);

	if($control)
	{
		$control_parametri = 1;
		echo "SAVED";
		mysql_query('COMMIT');
	}
	else
	{
		mysql_query('ROLLBACK');
		echo "ERROR";
	}*/

	$nuovoCodice = $new_cod;

	//echo " ".$new_cod;
}
if($control_parametri==1)
{
	$a_paramsAnno = array(
			'table' => 'anni_gestiti',
			'fields'=> array(
					array(  'name' => 'CC_Anno',        'type' => 'string', 'value' =>  $newCC),
					array(  'name' => 'Anno',             'type' => 'string', 'value' => date('Y')),
					array(  'name' => 'Gestione_Coattiva',      'type' => 'string', 'value' => 'Y'),
					array(  'name' => 'Gestione_Targhe_Estere',      'type' => 'string', 'value' => 'N'),
				  array(  'name' => 'Gestione_Pubblicita',      'type' => 'string', 'value' => 'N')
			)
	);

	//$field_crea[] = "Codici_Unione";	$value_crea[] = $cod_unione;

	if(!$cls_db->DbSave($a_paramsAnno))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore inserimento dati non riuscito";
	}
	else $msg = "Inserimento dati riuscito";
}

}
else if( $invia == "Delete" )
{

	$CC = $cls_help->getVar('CC');

	if(!$cls_db->Delete("enti_gestiti","CC = '".$CC."'"))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore impossibile eliminare i dati";
	}else $msg = "Dati eliminati con sucesso";

	/*mysql_query('BEGIN');

	$query = "DELETE FROM enti_gestiti WHERE CC = '".$CC."'";
	$control = mysql_query($query);

	if($control)
	{
		mysql_query('COMMIT');
		echo "DELETED";
	}
	else
	{
		mysql_query('ROLLBACK');
		echo "ERROR";
	}*/

}

if($control_parametri==1)
{
	$query = "SELECT * FROM parametri_annuali WHERE CC = '".$nuovoCodice."' AND Anno = '".date('Y')."' AND Tipo_Riscossione = '*****'";
	$par_annuali = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"parametri_annuali");
	//$par_annuali = new parametri_annuali( $nuovoCodice, date('Y-m-d'), "*****" );
	//mysql_query('BEGIN');
	//$control = $par_annuali->controlloParametri( $nuovoCodice, date('Y-m-d'), "*****" );
	$control = $cls_utils->controlloParametri( $nuovoCodice, date('Y-m-d'), "*****", $par_annuali["ID"]);
	//echo " annuali".$control;

	if(!$control)
	{
		$error = 1;
		$msg = "Errore inserimento atti non riuscito";
	}




	/*mysql_query('COMMIT');
else
    mysql_query('ROLLBACK');*/

//	$par_pagamento = new parametri_pagamento($nuovoCodice, "CDS");
//	mysql_query('BEGIN');
//	$control = $par_pagamento->controlloParametri( $nuovoCodice, "CDS");
//	echo " pagamento".$control;
//
//	if($control=="NEW")
//		mysql_query('COMMIT');
//	else
//		mysql_query('ROLLBACK');





	////////////////////////////////// DA FARE ///////////////////////////

	$query = "SELECT * FROM parametri_ricorso WHERE CC = '".$nuovoCodice."'";
	$par_annuali = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"parametri_ricorso");
	$control = $cls_utils->controlloParametriRicorso($nuovoCodice,$par_annuali["ID"]);

	if(!$control)
	{
		$error = 1;
		$msg = "Errore inserimento parametri non riuscito";
	}

	/*$par_ricorso = new parametri_ricorso($nuovoCodice);
	mysql_query('BEGIN');
	$control = $par_ricorso->controlloParametri( $nuovoCodice);
	echo " ricorsi".$control;

	if($control=="NEW")
		mysql_query('COMMIT');
	else
		mysql_query('ROLLBACK');*/

	//$query = "SELECT * FROM tariffe_coazione WHERE ID = '".null."' AND CC = '".$nuovoCodice."'";

	//$tariffe_coazione = new tariffe_coazione(null, $nuovoCodice);
	//mysql_query('BEGIN');
	$control = $cls_utils->crea_tariffe_base( $nuovoCodice );
	//echo " tariffe".$control;

	/*if($control=="NEW")
		mysql_query('COMMIT');
	else
		mysql_query('ROLLBACK');*/

	if(!$control)
	{
		$error = 1;
		$msg = "Errore inserimento tariffe non riuscito";
	}

	//////////////////////////////////////////////////////////////////////////
}

$cls_db->End_Transaction();

header("Location: crea_anno.php?c={$c}&a={$a}&servizio={$servizio}&newCC={$newCC}&error={$error}&msg={$msg}");




?>
