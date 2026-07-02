<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_storico.php";

$storico = new storico('storicoAnagrafe','2');
$cls_db = new cls_db();
$cls_help = new cls_help();

	$invia = $cls_help->getVar('invia_submit');
	$ID = $cls_help->getVar('p');
	$c = $cls_help->getVar('c');
	$a = $cls_help->getVar('a');
	$comune_id = $cls_help->getVar('comune_id');
	$ese = $cls_help->getVar('ese');
	$sit = $cls_help->getVar('sit');
	$con = $cls_help->getVar('con');
	$rag = $cls_help->getVar('rag');
	$sot = $cls_help->getVar('sot');
	$servizio = $cls_help->getVar('servizio');

	$error = 0;
	$msg = "";

	$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	// Recupero dati utente
	$query = "SELECT IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(COGNOME,''),' ',COALESCE(Nome,''))) as U, Comune_ID FROM utente where ID = '".$ID."'";
	$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
	$msg_utente = $utente["U"];

	//DELETE
	if($invia == "Delete")
	{
		if(!$cls_db->Delete("dettagli_utente", "Utente_ID = '".$ID."'"))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile cancellare i dati";
		}else{
			$storico->insRow('D', "Eliminati dettagli anagrafe utente ".$msg_utente." (".$utente['Comune_ID'].") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati cancellati con successo";
		}
		/*$query = "DELETE FROM dettagli_utente WHERE Utente_ID = '".$ID."'";
		$control = safe_query($query);

		if($control)
		{
			echo "Delete Si ".$comune_id;
			die;
		}
		else
		{
			echo "Delete No ".$comune_id;
			die;
		}*/
	}

	//CREAZIONE ARRAY CAMPI $field_residenza E VALORI $value_residenza PER LA TABELLA indirizzo
	/*$field_dettagli = array();
	$value_dettagli = array();

	$field_dettagli[] = 'Utente_ID';				$value_dettagli[] = $ID;
	$field_dettagli[] = 'Esenzione_ID';				$value_dettagli[] = $ese;
	$field_dettagli[] = 'Situazione_ID';			$value_dettagli[] = $sit;
	$field_dettagli[] = 'Controllo_ID';				$value_dettagli[] = $con;
	$field_dettagli[] = 'Raggruppamento_ID';		$value_dettagli[] = $rag;
	$field_dettagli[] = 'Sottoraggruppamento_ID';	$value_dettagli[] = $sot;*/

	$a_paramsDett = array(
	    'table' => 'dettagli_utente',
	    'fields'=> array(
	        array(  'name' => 'Utente_ID',               'type' => 'int', 'value' => $ID),
	        array(  'name' => 'Esenzione_ID',            'type' => 'int', 'value' => $ese),
	        array(  'name' => 'Situazione_ID',           'type' => 'int', 'value' => $sit),
	        array(  'name' => 'Controllo_ID',            'type' => 'int', 'value' => $con),
	        array(  'name' => 'Raggruppamento_ID',       'type' => 'int', 'value' => $rag),
	        array(  'name' => 'Sottoraggruppamento_ID',  'type' => 'int', 'value' => $sot)
	    )
	);


//INSERT E UPDATE
switch($invia)
{
	case "Insert":

		if(!$cls_db->DbSave($a_paramsDett))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile inserire i dati";
		}else{
			$storico->insRow('I', "Inseriti dettagli anagrafe utente ".$msg_utente." (".$utente['Comune_ID'].") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati inseriti corettamente";
		}
		/*$control = table_insert_record('dettagli_utente', $field_dettagli, $value_dettagli);

		if($control!=0)
		{
			echo "Insert Si ".$ID." ".$comune_id;
		}
		else
		{
			echo "Insert No ".$ID." ".$comune_id;
		}*/

		break;

	case "Update":

		$a_paramsDett['updateField'] = array("name" => "Utente_ID", "type" => "int", "value" => $ID);

		if(!$cls_db->DbSave($a_paramsDett))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile aggiornare i dati";
		}else{
			$storico->insRow('U', "Modificati dettagli anagrafe utente ".$msg_utente." (".$utente['Comune_ID'].") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati aggiornati corettamente";
		}

	/*	$control = table_update_record('dettagli_utente', $field_dettagli, $value_dettagli, 'Utente_ID' , $ID);

		if($control==true)
		{
			echo "Update Si ".$ID." ".$comune_id;
		}
		else
		{
			echo "Update No ".$ID." ".$comune_id;
		}*/

		break;
}

$cls_db->End_Transaction();

header("Location: dettagli.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");

?>
