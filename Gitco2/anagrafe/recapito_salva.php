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



	$error = 0;
	$msg = "";
	//$new_id = 0;

	$invia = $cls_help->getVar('invia_submit');
	$ID = $cls_help->getVar('p');
	$c = $cls_help->getVar('c');
	$a = $cls_help->getVar('a');
	$comune_id = $cls_help->getVar('comune_id');
	$servizio = $cls_help->getVar('servizio');

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

//INDIRIZZO RESIDENZA
	$ID_rec = $cls_help->getVar('ID_rec');
	$ID_via = $cls_help->getVar('ID_via');
	$ID_via_cap = $cls_help->getVar('ID_via_cap');
	$presso_rec = $cls_help->getVar('presso_recapito');

	$CC_recapito = $cls_help->getVar('CC_recapito');
	$paese_recapito = $cls_help->getVar('paese_recapito');
	$comune_recapito = $cls_help->getVar('comune_recapito');
	$provincia_recapito = $cls_help->getVar('provDatiSogg');
	$frazione_recapito = $cls_help->getVar('frazione_recapito');
	$cap_recapito = $cls_help->getVar('cap_recapito');

	$via_recapito = $cls_help->getVar('via_recapito');
	$via_estera_recapito = $cls_help->getVar('via_estera_recapito');

	if($via_recapito == "") $via_recapito = $via_estera_recapito;

	$civico_recapito = $cls_help->getVar('civico_recapito');
	$esponente_recapito = $cls_help->getVar('esponente_recapito');
	$interno_recapito = $cls_help->getVar('interno_recapito');
	$dettagli_recapito = $cls_help->getVar('dettagli_recapito');
	$tel_recapito = $cls_help->getVar('tel_recapito');
	$fax_recapito = $cls_help->getVar('fax_recapito');
    $rec_type = $cls_help->getVar('rec_type');

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	$query_name = "SELECT IF(Genere='D',Ditta,CONCAT(Cognome,' ',Nome)) as U FROM utente WHERE ID = ".$ID;
	$result_name = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_name));
	$msg_name = $result_name['U'];

	//DELETE
	if($invia == "Delete")
	{
		if(!$cls_db->Delete("indirizzo","ID = '".$ID_rec."' AND Tipo='rec'"))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile eliminare i dati.";
		}else{
			$storico->insRow('D',"Eliminato recapito per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati eliminati correttamente";
		}
		/*$query = "DELETE FROM indirizzo WHERE ID = '".$ID_rec."' AND Tipo='rec'";
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


	$a_paramsToponim = array(
	    'table' => 'toponimo',
	    'fields'=> array(
	        array(  'name' => 'CC_Comune',        'type' => 'string', 'value' => $c),
	        array(  'name' => 'Nome',             'type' => 'string', 'value' => $via_recapito),
	        array(  'name' => 'CC_Toponimo',      'type' => 'string', 'value' => $CC_recapito),
	        array(  'name' => 'Paese',            'type' => 'string', 'value' => $paese_recapito),
	        array(  'name' => 'Comune',           'type' => 'string', 'value' => $comune_recapito),
	        array(  'name' => 'Cap',              'type' => 'string', 'value' => $cap_recapito)
	    )
	);

	if($ID_via == 0)
	{
		/*$field_via = array();
		$value_via = array();

		$field_via[] = 'CC_Comune';				$value_via[] = $c;
		$field_via[] = 'Nome'; 					$value_via[] = $via_recapito;
		$field_via[] = 'CC_Toponimo'; 			$value_via[] = $CC_recapito;
		$field_via[] = 'Paese'; 				$value_via[] = $paese_recapito;
		$field_via[] = 'Comune'; 				$value_via[] = $comune_recapito;
		$field_via[] = 'Cap'; 					$value_via[] = $cap_recapito;*/
		$new_ID_via = $cls_db->DbSave($a_paramsToponim);
		if(!$new_ID_via)
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile inserire i dati";
		}else
			$msg = "Dati inseriti correttamente";

	/*	$new_ID_via = table_insert_record('toponimo', $field_via, $value_via);
		if($new_ID_via==0)
		{
			echo "Insert Via";
			die;
		}*/

		$ID_via = $new_ID_via;

	}
	else if($ID_via != 1)
	{
	/*	$field_via = array();
		$value_via = array();

		$field_via[] = 'CC_Comune';				$value_via[] = $c;
		$field_via[] = 'Nome'; 					$value_via[] = $via_recapito;
		$field_via[] = 'CC_Toponimo'; 			$value_via[] = $CC_recapito;
		$field_via[] = 'Paese'; 				$value_via[] = $paese_recapito;
		$field_via[] = 'Comune'; 				$value_via[] = $comune_recapito;
		$field_via[] = 'Cap'; 					$value_via[] = $cap_recapito;*/
		$a_paramsToponim['updateField'] = array("name" => "ID", "type" => "int", "value" => $ID_via);
		if(!$cls_db->DbSave($a_paramsToponim))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile aggiornare i dati";
		}else
			$msg = "Dati aggiornati correttamente";


		/*$control = table_update_record('toponimo', $field_via, $value_via, 'ID' , $ID_via);
		if($control==0)
		{
			echo "Update Via";
			die;
		}*/
	}


	/*$field_recapito = array();
	$value_recapito = array();

	$field_recapito[] = 'Utente_ID'; 			$value_recapito[] = $ID;
	$field_recapito[] = 'Via_ID'; 				$value_recapito[] = $ID_via;
	//$field_recapito[] = 'Via_Cap_ID'; 			$value_recapito[] = $ID_via_cap;
	$field_recapito[] = 'Tipo'; 				$value_recapito[] = 'rec';
	$field_recapito[] = 'CC_Indirizzo'; 		$value_recapito[] = $CC_recapito;
	//$field_recapito[] = 'Presso'; 				$value_recapito[] = $presso_rec;
	$field_recapito[] = 'Paese'; 				$value_recapito[] = $paese_recapito;
	$field_recapito[] = 'Comune'; 				$value_recapito[] = $comune_recapito;
	$field_recapito[] = 'Provincia'; 			$value_recapito[] = $provincia_recapito;
	//$field_recapito[] = 'Frazione'; 			$value_recapito[] = $frazione_recapito;

	if($civico_recapito != null)
	{
		$field_recapito[] = 'Civico'; 			$value_recapito[] = $civico_recapito;
	}

	$field_recapito[] = 'Esponente'; 			$value_recapito[] = $esponente_recapito;

	if($interno_recapito != null)
	{
		$field_recapito[] = 'Interno'; 			$value_recapito[] = $interno_recapito;
	}

	$field_recapito[] = 'Dettagli';				$value_recapito[] = $dettagli_recapito;
	$field_recapito[] = 'Cap'; 					$value_recapito[] = $cap_recapito;
	$field_recapito[] = 'Telefono';		 		$value_recapito[] = $tel_recapito;
	$field_recapito[] = 'Fax';					$value_recapito[] = $fax_recapito;	*/
	if($ID == 0 || $ID == "")
	{
		$query = "SELECT MAX(Utente_ID) as ID FROM indirizzo";
		$resultMaxID = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
		$ID = $resultMaxID["ID"];
	}


	$a_paramsRecapito = array(
	    'table' => 'indirizzo',
	    'fields'=> array(
	        array(  'name' => 'Utente_ID',        'type' => 'int', 'value' => $ID),
	        array(  'name' => 'Via_ID',           'type' => 'int', 'value' => $ID_via),
	        array(  'name' => 'Via_Cap_ID',       'type' => 'int', 'value' => $ID_via_cap),
	        array(  'name' => 'Tipo',             'type' => 'string', 'value' => 'rec'),
	        array(  'name' => 'CC_Indirizzo',     'type' => 'string', 'value' => $CC_recapito),
	        array(  'name' => 'Presso',           'type' => 'string', 'value' => $presso_rec),
	        array(  'name' => 'Paese',            'type' => 'string', 'value' => $paese_recapito),
	        array(  'name' => 'Comune',           'type' => 'string', 'value' => $comune_recapito),
	        array(  'name' => 'Provincia',        'type' => 'string', 'value' => $provincia_recapito),
	        array(  'name' => 'Frazione',         'type' => 'string', 'value' => $frazione_recapito),
	        array(  'name' => 'Civico',           'type' => 'int', 'value' => $civico_recapito),
	        array(  'name' => 'Esponente',        'type' => 'string', 'value' => $esponente_recapito),
	        array(  'name' => 'Interno',          'type' => 'int', 'value' => $interno_recapito),
	        array(  'name' => 'Dettagli',         'type' => 'string', 'value' => $dettagli_recapito),
	        array(  'name' => 'Cap',              'type' => 'string', 'value' => $cap_recapito),
	        array(  'name' => 'Telefono',         'type' => 'string', 'value' => $tel_recapito),
	        array(  'name' => 'Fax',              'type' => 'string', 'value' => $fax_recapito),
			array(  'name' => 'user_rec_type_id', 'type' => 'int', 'value' => $rec_type)
	    )
	);


//INSERT E UPDATE
switch($invia)
{
	case "Insert":

		if(!$cls_db->DbSave($a_paramsRecapito))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile inserire i dati";
		}else{
			$storico->insRow('I',"Inserito recapito per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati inseriti correttamente";
		}

		/*$control = table_insert_record('indirizzo', $field_recapito, $value_recapito);

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


		$a_paramsRecapito['updateField'] = array("name" => "ID", "type" => "int", "value" => $ID_rec);
		if(!$cls_db->DbSave($a_paramsRecapito))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile aggiornare i dati";
		}else{
			$storico->insRow('U',"Modificato recapito per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati aggiornati correttamente";
		}

		//$new_id = $ID_rec;
		/*$control = table_update_record('indirizzo', $field_recapito, $value_recapito, 'ID' , $ID_rec);

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

header("Location: recapito.php?p={$ID}&a={$a}&c={$c}&error={$error}&msg={$msg}");

?>
