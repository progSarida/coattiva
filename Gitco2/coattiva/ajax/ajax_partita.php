<?php
if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";

	$cls_db = new cls_db();
	$cls_help = new cls_help();



$c = $cls_help->getVar('c');

$ajax = $cls_help->getVar('ajax');

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

switch($ajax)
{
	case "elimina_tributo":

	$ID_tributo = $cls_help->getVar('ID_tributo');

	//mysql_query('BEGIN');
	if(!$cls_db->Delete("tributo","ID = ".$ID_tributo." AND CC = '".$c."'"))
	{
		$cls_db->Rollback();
		echo "ERROR ".$cls_db->GetError();
		$cls_db->End_Transaction();
		die;
	}

	/*$query = "DELETE FROM tributo WHERE ID = ".$ID_tributo." AND CC = '".$c."'";
	$control = mysql_query($query);

	if($control===false)
	{
		echo "ERROR ".mysql_error();
		mysql_query('ROLLBACK');
		die;
	}*/

	$ID_partita = $cls_help->getVar('ID_partita');

	$query = "SELECT * FROM partita_tributi WHERE ID = '".$ID_partita."' AND CC = '".$c."'";
	$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");
	//$partita = new partita($ID_partita, $c);

		$query ="SELECT * FROM tributo WHERE Partita_ID = '".$ID_partita."' AND CC = '".$c."' ORDER BY Codice_Tributo ASC";
		$partita->Tributo = $cls_db->getResults($cls_db->ExecuteQuery($query));


	if(count($partita->Tributo)==0)
	{

		$a_paramsPartita = array(
				'table' => 'partita_tributi',
				'fields'=> array(
						array(  'name' => 'Cancellazione',   'type' => 'string', 'value' => 'si')
				),
				'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_partita)
		);

		if(!$cls_db->DbSave($a_paramsPartita))
		{
			$cls_db->Rollback();
			echo "ERROR ".$cls_db->GetError();
			$cls_db->End_Transaction();
			die;
		}
		else {
			echo "OK";
			$cls_db->End_Transaction();
			die;
		}
		/*$partita->Cancellazione = "si";
		$control_partita = $partita->Update($ID_partita);
		if($control_partita===false)
		{
			echo "ERROR ".$partita_ID." ".mysql_error();
			mysql_query('ROLLBACK');
			die;
		}
		else
		{
			echo "OK";
			mysql_query('COMMIT');
			die;
		}*/
	}
	else
	{
		echo "OK";
		$cls_db->End_Transaction();
		die;
	}

		break;


	case "crea_partita":


		if($control)
			echo "OK";
		else
			echo "error";

		break;

	case "nome":

		$ID = $cls_help->getVar('ID');

		$query = "SELECT * FROM utente WHERE ID = '".$ID."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
		$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");

		$query = "SELECT Sigla FROM forma_giuridica_societa WHERE ID = '".$utente['Forma_Giuridica']."' AND CC = '*****'";
		$Sigla = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa");
		//$utente = new utente($ID, $c);

		if($utente["Genere"]!="D")
			$ritorno = "(".$utente["Comune_ID"].") ".$utente["Cognome"]." ".$utente["Nome"];
		else
		{
			$ritorno = "(".$utente["Comune_ID"].") ".$utente["Ditta"]." ".$Sigla["Sigla"];
		}

		echo $ritorno;

		break;

	case "azienda":

		$azienda = $cls_help->getVar('Azienda');
		$query = "SELECT ID FROM utente WHERE CC_Comune = '".$c."' AND Azienda = '".$azienda."' AND Genere = 'D'";
		$utente = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
		//$utente = select_mysql_array("*", "utente", "CC_Comune = '".$c."' AND Azienda = '".$azienda."' AND Genere = 'D'");

		if($utente == null)	$ritorno = null;
		else $ritorno = $utente['ID'];

			echo $ritorno;

		break;
}


?>
