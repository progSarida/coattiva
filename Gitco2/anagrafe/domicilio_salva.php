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
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_storico.php";


$storico = new storico('storicoAnagrafe','2');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("DB",false);

	$invia = $cls_help->getVar('invia_submit');
	$ID = $cls_help->getVar('p');
	$c = $cls_help->getVar('c');
	$a = $cls_help->getVar('a');
	$comune_id = $cls_help->getVar('comune_id');
	$servizio = $cls_help->getVar('servizio');

	$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

//INDIRIZZO RESIDENZA
	$ID_dom = $cls_help->getVar('ID_dom');
	$ID_via = $cls_help->getVar('ID_via');
	$ID_via_cap = $cls_help->getVar('ID_via_cap');

	$CC_domicilio = $cls_help->getVar('CC_domicilio');
	$paese_domicilio = $cls_help->getVar('paese_domicilio');
	$comune_domicilio = $cls_help->getVar('comune_domicilio');
	$provincia_domicilio = $cls_help->getVar('provDatiSogg');
	$frazione_domicilio = $cls_help->getVar('frazione_domicilio');
	$cap_domicilio = $cls_help->getVar('cap_domicilio');

	$via_domicilio = $cls_help->getVar('via_domicilio');
	$via_estera_domicilio = $cls_help->getVar('via_estera_domicilio');

	if($via_domicilio == "") $via_domicilio = $via_estera_domicilio;

	$civico_domicilio = $cls_help->getVar('civico_domicilio');
	$esponente_domicilio = $cls_help->getVar('esponente_domicilio');
	$interno_domicilio = $cls_help->getVar('interno_domicilio');
	$dettagli_domicilio = $cls_help->getVar('dettagli_domicilio');
	$tel_domicilio = $cls_help->getVar('tel_domicilio');
	$fax_domicilio = $cls_help->getVar('fax_domicilio');

	echo "<br>".$esponente_domicilio."<br>";

	$error = 0;
	$msg = "";

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();
	$query_name = "SELECT IF(Genere='D',Ditta,CONCAT(Cognome,' ',Nome)) as U FROM utente WHERE ID = ".$ID;
	$result_name = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_name));
	$msg_name = $result_name['U'];

	//DELETE
	if($invia == "Delete")
	{
		/*$query = "DELETE FROM indirizzo WHERE ID = '".$ID_dom."' AND Tipo='dom'";
		$control = safe_query($query);*/

		if(!$cls_db->Delete("indirizzo","ID = '".$ID_dom."' AND Tipo='dom'"))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile eliminare i dati";
		}else{
			$storico->insRow('D',"Eliminato domicilio per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati eliminati correttamente";
		}

		/*if($control)
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

$a_paramsToponimo = array(
		'table' => 'toponimo',
		'fields'=> array(
				array(  'name' => 'CC_Comune',       'type' => 'string', 'value' => $c),
				array(  'name' => 'Nome',            'type' => 'string', 'value' => $via_domicilio),
				array(  'name' => 'CC_Toponimo',     'type' => 'string', 'value' => $CC_domicilio),
				array(  'name' => 'Paese',           'type' => 'string', 'value' => $paese_domicilio),
				array(  'name' => 'Comune',          'type' => 'string', 'value' => $comune_domicilio),
				array(  'name' => 'Cap',             'type' => 'string', 'value' => $cap_domicilio)
		)
);



	if($ID_via == 0)
	{
		/*$field_via = array();
		$value_via = array();

		$field_via[] = 'CC_Comune';				$value_via[] = $c;
		$field_via[] = 'Nome'; 					$value_via[] = $via_domicilio;
		$field_via[] = 'CC_Toponimo'; 			$value_via[] = $CC_domicilio;
		$field_via[] = 'Paese'; 				$value_via[] = $paese_domicilio;
		$field_via[] = 'Comune'; 				$value_via[] = $comune_domicilio;
		$field_via[] = 'Cap'; 					$value_via[] = $cap_domicilio;*/

		$new_ID_via = $cls_db->DbSave($a_paramsToponimo);
		if(!$new_ID_via)
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile inserire i dati";
		}else $msg = "Dati inseriti con successo";

	//	$new_ID_via = table_insert_record('toponimo', $field_via, $value_via);
		if($new_ID_via==0)
		{
			$error = 1;
			$msg = "Errore nel tentativo di inserimento dell'indirizzo.";
			//header("Location: domicilio.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			//echo "Insert Via";
		}

		$ID_via = $new_ID_via;

	}
	else if($ID_via != 1)
	{
		/*$field_via = array();
		$value_via = array();

		$field_via[] = 'CC_Comune';				$value_via[] = $c;
		$field_via[] = 'Nome'; 					$value_via[] = $via_domicilio;
		$field_via[] = 'CC_Toponimo'; 			$value_via[] = $CC_domicilio;
		$field_via[] = 'Paese'; 				$value_via[] = $paese_domicilio;
		$field_via[] = 'Comune'; 				$value_via[] = $comune_domicilio;
		$field_via[] = 'Cap'; 					$value_via[] = $cap_domicilio;*/

		$a_paramsToponimo['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_via);

		if(!$cls_db->DbSave($a_paramsToponimo))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore nel tentativo di aggiornamento dell'indirizzo.";
			//header("Location: domicilio.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
		}else $msg = "Dati inseriti con successo";

	}

	//$field_domicilio = array();
	//$value_domicilio = array();

	$a_paramsIndirizzo = array(
	    'table' => 'indirizzo',
	    'fields'=> array(
	        array(  'name' => 'Utente_ID',             'type' => 'int',    'value' => $ID),
	        array(  'name' => 'Via_ID',                'type' => 'int',    'value' => $ID_via),
	        array(  'name' => 'Via_Cap_ID',            'type' => 'int',    'value' => $ID_via_cap),
	        array(  'name' => 'Tipo',                  'type' => 'string', 'value' => 'dom'),
	        array(  'name' => 'CC_Indirizzo',          'type' => 'string', 'value' => $CC_domicilio),
	        array(  'name' => 'Paese',                 'type' => 'string', 'value' => $paese_domicilio),
	        array(  'name' => 'Comune',                'type' => 'string', 'value' => $comune_domicilio),
	        array(  'name' => 'Provincia',             'type' => 'string', 'value' => $provincia_domicilio),
	        array(  'name' => 'Frazione',              'type' => 'string', 'value' => $frazione_domicilio),
	        array(  'name' => 'Dettagli',              'type' => 'string', 'value' => $dettagli_domicilio),
	        array(  'name' => 'Cap',                   'type' => 'string', 'value' => $cap_domicilio),
	        array(  'name' => 'Telefono',              'type' => 'string', 'value' => $tel_domicilio),
	        array(  'name' => 'Fax',                   'type' => 'string', 'value' => $fax_domicilio),
	        array(  'name' => 'Esponente',             'type' => 'string', 'value' => $esponente_domicilio)
	    )
	);

	if($civico_domicilio != null)
	{
		array_push($a_paramsIndirizzo["fields"], array(  'name' => 'Civico', 'type' => 'int', 'value' => $civico_domicilio));
	}
	if($interno_domicilio != null)
	{
		array_push($a_paramsIndirizzo["fields"], array(  'name' => 'Interno', 'type' => 'int', 'value' => $interno_domicilio));
	}


	/*$field_domicilio[] = 'Utente_ID'; 			$value_domicilio[] = $ID;
	$field_domicilio[] = 'Via_ID'; 				$value_domicilio[] = $ID_via;
	$field_domicilio[] = 'Via_Cap_ID'; 			$value_domicilio[] = $ID_via_cap;
	$field_domicilio[] = 'Tipo'; 				$value_domicilio[] = 'dom';
	$field_domicilio[] = 'CC_Indirizzo'; 		$value_domicilio[] = $CC_domicilio;
	$field_domicilio[] = 'Paese'; 				$value_domicilio[] = $paese_domicilio;
	$field_domicilio[] = 'Comune'; 				$value_domicilio[] = $comune_domicilio;
	$field_domicilio[] = 'Provincia'; 			$value_domicilio[] = $provincia_domicilio;
	$field_domicilio[] = 'Frazione'; 			$value_domicilio[] = $frazione_domicilio;

	if($civico_domicilio != null)
	{
		$field_domicilio[] = 'Civico'; 			$value_domicilio[] = $civico_domicilio;
	}

	$field_domicilio[] = 'Esponente'; 			$value_domicilio[] = $esponente_domicilio;

	if($interno_domicilio != null)
	{
		$field_domicilio[] = 'Interno'; 		$value_domicilio[] = $interno_domicilio;
	}

	$field_domicilio[] = 'Dettagli';			$value_domicilio[] = $dettagli_domicilio;
	$field_domicilio[] = 'Cap'; 				$value_domicilio[] = $cap_domicilio;
	$field_domicilio[] = 'Telefono';		 	$value_domicilio[] = $tel_domicilio;
	$field_domicilio[] = 'Fax';					$value_domicilio[] = $fax_domicilio;*/

//INSERT E UPDATE
switch($invia)
{
	case "Insert":

		/*$control = table_insert_record('indirizzo', $field_domicilio, $value_domicilio);

		if($control!=0)
		{
			echo "Insert Si ".$ID." ".$comune_id;
		}
		else
		{
			echo "Insert No ".$ID." ".$comune_id;
		}*/

		if(!$cls_db->DbSave($a_paramsIndirizzo))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile inserire i dati";
		}else {
			$storico->insRow('I',"Inserito domicilio per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati inseriti correttamente";
		}
		
		break;

	case "Update":

		/*$control = table_update_record('indirizzo', $field_domicilio, $value_domicilio, 'ID' , $ID_dom);

		if($control==true)
		{
			echo "Update Si ".$ID." ".$comune_id;
		}
		else
		{
			echo "Update No ".$ID." ".$comune_id;
		}*/
		$a_paramsIndirizzo['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_dom);

		if(!$cls_db->DbSave($a_paramsIndirizzo))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile aggiornare i dati";
		}else {
			$storico->insRow('U',"Modificato domicilio per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati aggiornati correttamente";
		}

		break;
}

$cls_db->End_Transaction();

header("Location: domicilio.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
?>
