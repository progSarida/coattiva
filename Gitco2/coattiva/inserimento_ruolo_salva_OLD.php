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
	include_once CLS . "/cls_Utils.php";
	//include_once CLS . "/cls_anagrafeUtils.php";

	$cls_db = new cls_db();
	$cls_help = new cls_help();
	$cls_date = new cls_DateTimeI("DB",false);
	$cls_utils = new cls_Utils();
	//$cls_anagr = new cls_anagr();

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');

	$id_ruolo = $cls_help->getVar('id_ruolo');
	$desc_ruolo = $cls_help->getVar('ruolo');

	$tipo_ruolo = $cls_help->getVar('tipo_ruolo');

	$data_fornitura = $cls_help->getVar('data');
	$num_rate = $cls_help->getVar('num_rate');
	$num_ruolo = $cls_help->getVar('num_ruolo');

	$error = 0;
	$msg = "";
	$newID = 0;


	$query = "SELECT MAX(Comune_ID) AS max_id FROM Ruolo WHERE CC = '".$c."'";
	//$ruolo_id_array =   select_mysql_array("MAX(Comune_ID) AS max_id", "ruolo", "CC = '".$c."'");
	$comune_id_ruolo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["max_id"]; //$ruolo_id_array[0]['max_id'] + 1;

	$query = "SELECT * FROM ruolo WHERE ID = '".$id_ruolo."' AND CC = '".$c."'";
	$ruolo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ruolo");

	$ruolo->CC = $c;

	if($id_ruolo==null)
		$ruolo->Comune_ID =  $comune_id_ruolo + 1;

	$ruolo->Data_Fornitura = $cls_date->GetDateDB($data_fornitura,"IT");
	$ruolo->Data_Inserimento = date('Y-m-d');
	$ruolo->Ruolo = $tipo_ruolo;
	$ruolo->Descrizione = $desc_ruolo;
	$ruolo->Num_Ruolo = $num_ruolo;
	$ruolo->Num_Rate = $num_rate;


	if($num_ruolo == "") $num_ruolo = null;
	if($num_rate == "") $num_rate = "0";

	/*$a_paramsRuolo = array(
			'table' => 'ruolo',
			'fields'=> array(
					array(  'name' => 'CC',                   'type' => 'string', 'value' => $c),
					array(  'name' => 'Data_Fornitura',       'type' => 'date',   'value' => $cls_date->GetDateDB($data_fornitura,"IT")),
					array(  'name' => 'Data_Inserimento',     'type' => 'date',   'value' => date('Y-m-d')),
					array(  'name' => 'Ruolo',                'type' => 'string', 'value' => $tipo_ruolo),
					array(  'name' => 'Descrizione',          'type' => 'string', 'value' => $desc_ruolo),
					array(  'name' => 'Num_Ruolo',            'type' => 'int',    'value' => $num_ruolo),
					array(  'name' => 'Num_Rate',             'type' => 'int',    'value' => $num_rate)
			)
	);*/


	//$ruolo = new ruolo($id_ruolo, $c,null,false);
	//$ruolo->CC = $c;

	//if($id_ruolo==null)
	//	array_push($a_paramsRuolo["fields"],array('name' => 'Comune_ID', 'type' => 'int', 'value' => $comune_id_ruolo + 1));//$ruolo->Comune_ID =  $comune_id_ruolo + 1;

	//$ruolo->Data_Fornitura = to_mysql_date($data_fornitura);
	//$ruolo->Data_Inserimento = date('Y-m-d');
	/*$ruolo->Ruolo = $tipo_ruolo;
	$ruolo->Descrizione = $desc_ruolo;
	$ruolo->Num_Ruolo = $num_ruolo;
	$ruolo->Num_Rate = $num_rate;*/

	//$query = "SELECT ID FROM ruolo WHERE ID = '{$id_ruolo}'";
	//$id_ruolo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo")["ID"];

	//mysql_query('BEGIN');
	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if($id_ruolo!=null)
	{
		//$a_paramsRuolo['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $id_ruolo);
		//$control_ruolo = $ruolo->Update($id_ruolo);//ID = $id_ruolo

		//echo $id_ruolo;
		if(!$cls_db->DbSave($cls_utils->GetObjectQuery((array) $ruolo,"ruolo",array("ID" => $id_ruolo))))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore aggiornamento non riuscito";
		}
		else $msg = "Dati aggiornati correttamente";
		$newID = $id_ruolo;
	}
	else
	{
		$newID = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $ruolo,"ruolo"));
		if(!$newID)
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore inserimento non riuscito";
		}
		else $msg = "Dati inseriti correttamente";
	//	$control_ruolo = $ruolo->Insert();
	}

	$cls_db->End_Transaction();

	header("Location: inserimento_ruolo.php?c={$c}&a={$a}&ruolo={$newID}&error={$error}&msg={$msg}");

	/*if($control_ruolo === false)
	{
		echo 'ERROR '.mysql_error();
		mysql_query('ROLLBACK');
		die;
	}
	else
	{
		if($id_ruolo==null)
			$id_ruolo = mysql_insert_id();

		echo "OK ".$id_ruolo;
		mysql_query('COMMIT');
		die;
	}*/

?>
