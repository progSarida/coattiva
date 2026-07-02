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
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_storico.php";

$storico = new storico('storicoRuolo','3');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("DB",false);

$invia = $cls_help->getVar('invia_submit');

$p = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$error = 0;
$msg = "";

$id_ispezione = $cls_help->getVar('id_ispezione');

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

//ISPEZIONI
$denominazione = $cls_help->getVar('nome_doc');
$tipo = $cls_help->getVar('tipo');
$note = $cls_help->getVar('note');
//$note = mysqli_set_charset($cls_help->getVar('note'), "utf8");
$contenuto = $cls_help->getVar('contenuto');


$data_inserimento = $cls_date->GetDateDB($cls_help->getVar('data_inserimento'),"IT");
$data_ispezione = $cls_date->GetDateDB($cls_help->getVar('data_ispezione'),"IT");

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );


//DELETE
	if($invia == "Delete")
	{
		
		if(!$cls_db->Delete("ispezioni", "ID = ".$id_ispezione." AND CC = '".$c."'"))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile eliminare i dati";
		}else{
			$storico->insRow('D', "Eliminata ispezione ".$tipo." del ".$data_ispezione." per ente ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati eliminati con successo";
		}

		//mysql_query('BEGIN');

		//$cancella = new ispezioni( $id_ispezione , $c );

		//$control = $cancella->Delete();

		/*if($control)
		{
			mysql_query('COMMIT');
			echo "Delete Si";
			die;
		}
		else
		{
			mysql_query('ROLLBACK');
			echo "Delete ".mysql_error();
			die;
		}*/
	}




//INSERT E UPDATE
switch($invia)
{
	case "Update":

	$a_paramsIspezioni = array(
			'table' => 'ispezioni',
			'fields'=> array(
					array(  'name' => 'Contenuto',         'type' => 'string', 'value' => addslashes($contenuto)),
					array(  'name' => 'Data_Inserimento',  'type' => 'date', 'value' => $data_inserimento),
					array(  'name' => 'Data_Ispezione',    'type' => 'date', 'value' => $data_ispezione),
					array(  'name' => 'Note',              'type' => 'string', 'value' => addslashes($note)),
					array(  'name' => 'Tipo',              'type' => 'string', 'value' => $tipo),
					array(  'name' => 'CC',                'type' => 'string', 'value' => $c),
					array(  'name' => 'Utente_ID',         'type' => 'int', 'value' => $p),
					array(  'name' => 'Denominazione',     'type' => 'string', 'value' => '')
			),
			'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $id_ispezione)
	);

	if(!$cls_db->DbSave($a_paramsIspezioni))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore impossibile aggiornare i dati";
	}else{
		$storico->insRow('U', "Modificata ispezione ".$tipo." del ".$data_ispezione." per ente ".$ente['Denominazione']."[".$c."]");
		$msg = "Dati aggiornati correttamente";
	}
		/*mysql_query('BEGIN');

		$update = new ispezioni( $id_ispezione , $c );

		$update->Contenuto = addslashes($contenuto);
		$update->Data_Inserimento = $data_inserimento;
		$update->Data_Ispezione = $data_ispezione;
		$update->Note = addslashes($note);
		$update->Tipo = $tipo;

		$control = $update->Update( $id_ispezione );

		if($control)
		{
			mysql_query('COMMIT');
			echo "Update Si";
			die;
		}
		else
		{
			mysql_query('ROLLBACK');
			echo "Update ".mysql_error();
			die;
		}*/

		break;

	case "Insert":


	$a_paramsIspezioni = array(
			'table' => 'ispezioni',
			'fields'=> array(
					array(  'name' => 'Contenuto',         'type' => 'string', 'value' => addslashes($contenuto)),
					array(  'name' => 'Data_Inserimento',  'type' => 'date', 'value' => $data_inserimento),
					array(  'name' => 'Data_Ispezione',    'type' => 'date', 'value' => $data_ispezione),
					array(  'name' => 'Note',              'type' => 'string', 'value' => addslashes($note)),
					array(  'name' => 'Tipo',              'type' => 'string', 'value' => $tipo),
					array(  'name' => 'CC',                'type' => 'string', 'value' => $c),
					array(  'name' => 'Utente_ID',         'type' => 'int', 'value' => $p),
					array(  'name' => 'Denominazione',     'type' => 'string', 'value' => '')
			)
	);


	if(!$cls_db->DbSave($a_paramsIspezioni))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore impossibile inserire i dati";
	}else{
		$storico->insRow('I', "Inserita ispezione ".$tipo." del ".$data_ispezione." per ente ".$ente['Denominazione']."[".$c."]");
		$msg = "Dati inseriti correttamente";
	}

	/*mysql_query('BEGIN');

		$insert = new ispezioni( null , $c );

		$insert->CC = $c;
		$insert->Contenuto = addslashes($contenuto);;
		$insert->Data_Inserimento = $data_inserimento;
		$insert->Data_Ispezione = $data_ispezione;
		$insert->Note = addslashes($note);
		$insert->Tipo = $tipo;
		$insert->Utente_ID = $p;

		$control = $insert->Insert();

		if($control)
		{
			mysql_query('COMMIT');
			echo "Insert Si";
			die;
		}
		else
		{
			mysql_query('ROLLBACK');
			echo "Insert ".mysql_error();
			die;
		}*/

		break;
}
	$cls_db->End_Transaction();

	header("Location: ricerche_ispezioni.php?p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
?>
