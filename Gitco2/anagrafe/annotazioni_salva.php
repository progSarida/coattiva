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
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_storico.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$storico = new storico('storicoAnagrafe','2');





	$invia = $cls_help->getVar('invia_submit');
	$ID = $cls_help->getVar('p');
	$c = $cls_help->getVar('c');
	$a = $cls_help->getVar('a');
	$comune_id = $cls_help->getVar('comune_id');
	$servizio = $cls_help->getVar('servizio');
	$error = 0;
	$msg = "";

	$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();
	$query_name = "SELECT IF(Genere='D',Ditta,CONCAT(Cognome,' ',Nome)) as U FROM utente WHERE ID = ".$ID;
	$result_name = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_name));
	$msg_name = $result_name['U'];

//DELETE
	if($invia == "Delete")
	{
		$query = "Update utente SET Note = \"\" WHERE ID = '".$ID."' AND CC_Comune ='".$c."' AND Comune_ID ='".$comune_id."'";
		if(!$cls_db->ExecuteQuery($query))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile eliminare i dati.";
		}else{
			$storico->insRow('D',"Eliminate annotazioni per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati eliminati con successo";
		}

		/*$control = safe_query($query);

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

//NOTE

	$note = addslashes($cls_help->getVar('annotazioni'));

	//$field_utente = array();
	//$value_utente = array();

	//$field_utente[0] = 'Note'; 				$value_utente[0] = addslashes($note);

	if($invia == "Update"){

		$query = "Update utente SET Note = '".$note."' WHERE ID = '".$ID."' AND CC_Comune ='".$c."' AND Comune_ID ='".$comune_id."'";
		if(!$cls_db->ExecuteQuery($query))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile aggiornare i dati.";
		}else{
			$storico->insRow('U',"Modificate annotazioni per l'utente ".$msg_name." (".$comune_id.") per ".$ente['Denominazione']."[".$c."]");
			$msg = "Dati aggiornati con successo";
		}

	}

//INSERT E UPDATE
/*switch($invia)
{
	case "Update":

		$control = table_update_record('utente', $field_utente, $value_utente, 'ID' , $ID);

		if($control==true)
		{
			echo "Update Si ".$ID." ".$comune_id;
		}
		else
		{
			echo "Update No ".$ID." ".$comune_id;
		}

		break;
}*/

$cls_db->End_Transaction();

header("Location: annotazioni.php?p={$ID}&c={$c}&a={$a}&msg={$msg}&error={$error}");

?>
