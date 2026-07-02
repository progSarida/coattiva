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
include_once CLS . "/cls_anagrafeUtils.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("DB",false);
$cls_anagr = new cls_anagr();



	$invia = $cls_help->getVar('invia_submit');

	$ID = $cls_help->getVar('p');
	$c = $cls_help->getVar('c');
	$a = $cls_help->getVar('a');
	$comune_id = $cls_help->getVar('comune_id');
	$ID_res = $cls_help->getVar('ID_res');
	$servizio = $cls_help->getVar('servizio');
	$error = 0;
	$msg = "";

	$data_inizio = $cls_help->getVar('data_res');//DATA INIZIO RES NUOVA COINCIDE CON DATA FINE RES VECCHIA

	//$cls_anagr->Get_Indirizzo_Cambia_Residenza
	print_r( $cls_anagr->Get_Indirizzo_Cambia_Residenza($ID,"res",$c));

	$indirizzo_res =  $cls_anagr->Get_Indirizzo_Cambia_Residenza($ID,"res",$c);//new indirizzo( $ID , 'res' , $c);

	$ID_res		 			= 	$indirizzo_res["ID"];


	$a_paramsStorico = array(
			'table' => 'storico_residenza',
			'fields'=> array(
					array(  'name' => 'Utente_ID',        'type' => 'int',    'value' => $ID),
					array(  'name' => 'Via_ID',           'type' => 'int',    'value' => $indirizzo_res["Via_ID"]),
					array(  'name' => 'Via_Cap_ID',       'type' => 'int',    'value' => $indirizzo_res["Via_Cap_ID"]),
					array(  'name' => 'CC_Indirizzo',     'type' => 'string', 'value' => $indirizzo_res["CC_Indirizzo"]),
					array(  'name' => 'Paese',            'type' => 'string', 'value' => $indirizzo_res["Paese"]),
					array(  'name' => 'Comune',           'type' => 'string', 'value' => $indirizzo_res["Comune"]),
					array(  'name' => 'Provincia',        'type' => 'string', 'value' => $indirizzo_res["Provincia"]),
					array(  'name' => 'Frazione',         'type' => 'string', 'value' => $indirizzo_res["Frazione"]),
					array(  'name' => 'Esponente',        'type' => 'string', 'value' => $indirizzo_res["Esponente"]),
					array(  'name' => 'Cap',              'type' => 'string', 'value' => $indirizzo_res["Cap"]),
					array(  'name' => 'Dettagli',         'type' => 'string', 'value' => $indirizzo_res["Dettagli"]),
					array(  'name' => 'Telefono',         'type' => 'string', 'value' => $indirizzo_res["Telefono"]),
					array(  'name' => 'Fax',              'type' => 'string', 'value' => $indirizzo_res["Fax"]),
					array(  'name' => 'Data_Inizio',      'type' => 'date',   'value' => $indirizzo_res["Data_Inizio_Residenza"]),
					array(  'name' => 'Data_Fine',        'type' => 'date',   'value' => $cls_date->GetDateDB($data_inizio,"IT"))
			)
	);

	if($indirizzo_res["Civico"] != null && $indirizzo_res["Civico"] != "")
	{
		array_push($a_paramsStorico["fields"],array('name' => 'Civico', 'type' => 'int', 'value' => $indirizzo_res["Civico"]));
	}
	else array_push($a_paramsStorico["fields"],array('name' => 'Civico', 'type' => 'int', 'value' => null));

	if($indirizzo_res["Interno"] != null && $indirizzo_res["Interno"] != "")
	{
		array_push($a_paramsStorico["fields"],array('name' => 'Interno', 'type' => 'int', 'value' => $indirizzo_res["Interno"]));
	}else array_push($a_paramsStorico["fields"],array('name' => 'Interno', 'type' => 'int', 'value' => null));

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->DbSave($a_paramsStorico))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore, impossibile inserire i dati dello storico";
		header("Location: cambia_residenza.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}&mode=consulta");
	}else $msg = "Dati inseriti correttamente";

	//$new_ID_storico = table_insert_record('storico_residenza', $field_storico, $value_storico);

//INDIRIZZO RESIDENZA NUOVO
	$ID_via = $cls_help->getVar('ID_via');
	//$ID_via_cap = $cls_help->getVar('ID_via_cap');

	//$CC_residenza = $cls_help->getVar('CC_residenza');
	$paese_residenza = $cls_help->getVar('paese_residenza');
	//$comune_residenza = $cls_help->getVar('comune_residenza');
	//$provincia_residenza = $cls_help->getVar('provDatiSogg');
	//$frazione_residenza = $cls_help->getVar('frazione_residenza');
	//$cap_residenza = $cls_help->getVar('cap_residenza');

	$via_residenza = $cls_help->getVar('via_residenza');
	$civico_residenza = $cls_help->getVar('civico_residenza');
	$esponente_residenza = $cls_help->getVar('esponente_residenza');
	$interno_residenza = $cls_help->getVar('interno_residenza');

//echo "<h1>First ".$civico_residenza." - ".$interno_residenza."</h1>";

	$via_estera_residenza = $cls_help->getVar('via_estera_residenza');
	//echo "<h1>Via estera ".$via_estera_residenza." -- Paese ".$paese_residenza."</h1>";
	if($paese_residenza != "Italia")
	{
		$via_residenza = $via_estera_residenza;
		$civico_residenza = null;
		$esponente_residenza = "";
		$interno_residenza = null;
	}

	//$dettagli_residenza = $cls_help->getVar('dettagli_residenza');
	//$tel_residenza = $cls_help->getVar('tel_residenza');
	//$fax_residenza = $cls_help->getVar('fax_residenza');
	//$data_inizio_residenza = $cls_date->GetDateDB($cls_help->getVar('data_res'),"IT");
	//$data_inizio_residenza = to_mysql_date($data_inizio_residenza);

	$a_paramsToponimo = array(
			'table' => 'toponimo',
			'fields'=> array(
					array(  'name' => 'Nome',          'type' => 'string', 'value' => $via_residenza),
					array(  'name' => 'CC_Comune',     'type' => 'string', 'value' => $c),
					array(  'name' => 'CC_Toponimo',   'type' => 'string', 'value' => $cls_help->getVar('CC_residenza')),
					array(  'name' => 'Paese',         'type' => 'string', 'value' => $cls_help->getVar('paese_residenza')),
					array(  'name' => 'Comune',        'type' => 'string', 'value' => $cls_help->getVar('comune_residenza')),
					array(  'name' => 'Cap',           'type' => 'string', 'value' => $cls_help->getVar('cap_residenza'))
			)
	);

//echo "<h1>ID: ".$ID_via."</h1>";

	if($ID_via == 0)
	{

		$new_ID_via = $cls_db->DbSave($a_paramsToponimo);
		if(!$new_ID_via)
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile inserire i dati relativi al toponimo";
			header("Location: cambia_residenza.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}&mode=consulta");
		}else $msg = "Dati inseriti correttamente";

		$ID_via = $new_ID_via;
	}
	else if($ID_via != 1)
	{
		$a_paramsToponimo['updateField'] = array("name" => "ID", "type" => "int", "value" => $ID_via);

		if(!$cls_db->DbSave($a_paramsToponimo))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile aggiornare i dati relativi al toponimo";
			header("Location: cambia_residenza.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}&mode=consulta");
		}else $msg = "Dati inseriti correttamente";
	}

	$a_paramsIndirizzo = array(
			'table' => 'indirizzo',
			'fields'=> array(
					array(  'name' => 'Via_ID',                 'type' => 'int',    'value' => $ID_via),
					array(  'name' => 'Via_Cap_ID',             'type' => 'int',    'value' => $cls_help->getVar('ID_via_cap')),
					array(  'name' => 'Tipo',                   'type' => 'string', 'value' => 'res'),
					array(  'name' => 'CC_Indirizzo',           'type' => 'string', 'value' => $cls_help->getVar('CC_residenza')),
					array(  'name' => 'Paese',                  'type' => 'string', 'value' => $cls_help->getVar('paese_residenza')),
					array(  'name' => 'Comune',                 'type' => 'string', 'value' => $cls_help->getVar('comune_residenza')),
					array(  'name' => 'Provincia',              'type' => 'string', 'value' => $cls_help->getVar('provDatiSogg')),
					array(  'name' => 'Frazione',               'type' => 'string', 'value' => $cls_help->getVar('frazione_residenza')),
					array(  'name' => 'Esponente',              'type' => 'string', 'value' => $esponente_residenza),
					array(  'name' => 'Dettagli',               'type' => 'string', 'value' => $cls_help->getVar('dettagli_residenza')),
					array(  'name' => 'Cap',                    'type' => 'string', 'value' => $cls_help->getVar('cap_residenza')),
					array(  'name' => 'Telefono',               'type' => 'string', 'value' => $cls_help->getVar('tel_residenza')),
					array(  'name' => 'Fax',                    'type' => 'string', 'value' => $cls_help->getVar('fax_residenza')),
					array(  'name' => 'Data_Inizio_Residenza',  'type' => 'date',   'value' => $cls_date->GetDateDB($cls_help->getVar('data_res'),"IT")),
					array(  'name' => 'Utente_ID',              'type' => 'int',    'value' => $ID)
			),
		'updateField' => array ("name" => "ID", "type" => "int", "value" => $ID_res)
	);

	//echo "<h1>First ".$civico_residenza." - ".$interno_residenza."</h1>";

	//if($civico_residenza=="") echo "<h1>virgolette</h1>";

	if($civico_residenza != null && $civico_residenza != "")
	{
		array_push($a_paramsIndirizzo["fields"],array('name' => 'Civico', 'type' => 'int', 'value' => $civico_residenza));
	}else array_push($a_paramsIndirizzo["fields"],array('name' => 'Civico', 'type' => 'int', 'value' => null));
	if($interno_residenza != null && $interno_residenza != "")
	{
		array_push($a_paramsIndirizzo["fields"],array('name' => 'Interno', 'type' => 'int', 'value' => $interno_residenza));
	}else array_push($a_paramsIndirizzo["fields"],array('name' => 'Interno', 'type' => 'int', 'value' => null));

	//UPDATE
	switch($invia)
	{
		case "Update":

			if(!$cls_db->DbSave($a_paramsIndirizzo))
			{
				$cls_db->Rollback();
				$error = 1;
				$msg = "Errore, impossibile aggiornare i dati relativi all'indirizzo";
			}else $msg = "Dati inseriti correttamente";

			break;

	}

	$cls_db->End_Transaction();

	header("Location: cambia_residenza.php?p={$ID}&c={$c}&a={$a}&error={$error}&msg={$msg}&mode=consulta");
?>
