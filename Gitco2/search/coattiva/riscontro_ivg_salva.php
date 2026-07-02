<?php

	if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";
	include_once CLS . "/cls_DateTimeInLine.php";
	include_once CLS . "/cls_math.php";
	include_once CLS . "/cls_Utils.php";
	include_once CLS . "/cls_CoazioneUtils.php";

	if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}


	$cls_db = new cls_db();
	$cls_help = new cls_help();
	$cls_date = new cls_DateTimeI("IT",false);
	$cls_math = new cls_math();
	$cls_Utils = new cls_Utils();
	$cls_coazione = new cls_Coazione();

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$id_notifica = $cls_help->getVar('id_notifica');
	$pignoramento_id = $cls_help->getVar('pignoramento');
	$upload_dir = $cls_Utils->crea_dir( ATTI ."/". $c . "/Riscontri" );

	$del_file = $cls_help->getVar('del_file');
	if($del_file=="") $del_file="si";

	$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_id." AND CC = '".$c."'";
	$pignoramento = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$pignoramento = new pignoramento($pignoramento_id, $c);
	$query = "SELECT * FROM partita_tributi WHERE ID = '".$pignoramento["Partita_ID"]."' AND CC = '".$c."'";
	$partita = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$partita = new partita($pignoramento->Partita_ID, $c);
	$query = "SELECT * FROM utente WHERE ID = '".$partita["Utente_ID"]."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
	$utente = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$utente = new utente($partita->Utente_ID, $c);
	if($utente["Genere"]=="D")
		$nome_utente = $utente["Ditta"];
	else
		$nome_utente = $utente["Cognome"]."_".$utente["Nome"];

	$query = "SELECT * FROM notifica_atto WHERE ID = '".$id_notifica."' AND CC = '".$c."'";
	$salva = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$salva = new notifica_atto( $id_notifica , $c );

	if($del_file=="si")
	{
		unlink($upload_dir."/".$salva["Link_Riscontro"]);

		$salva["Link_Riscontro"] = "";
	}

	if(isset($_FILES['file_riscontro']) && $_FILES['file_riscontro']['size'] > 0)
	{

		$file = $_FILES['file_riscontro'];

		$tipo_pigno = $cls_coazione->tipo_pignoramento($pignoramento["Tipo"],$pignoramento["Tipo_Terzi"],"sigla");
		$nuovo_file = "Riscontro_".$tipo_pigno."_".$c."_Pigno_".$pignoramento["ID_Cronologico"]."_".$pignoramento["Anno_Cronologico"]."_";
		$nuovo_file.= $nome_utente."_NOT_".$id_notifica;

		if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']))
		{
			$im = new imagick(  $file['tmp_name'] );
			$im->setImageFormat('pdf');

			$im->writeImage( $upload_dir."/".$nuovo_file.".pdf" );

			$salva["Link_Riscontro"] = $nuovo_file.".pdf";
		}



	}

	$salva["Tipo_Riscontro"] = $cls_help->getVar('tipo_riscontro');
	$salva["Mezzo_Riscontro"] = $cls_help->getVar('mezzo_riscontro');
	$salva["Data_Riscontro"] = $cls_date->GetDateDB($cls_help->getVar('data_riscontro'),"IT");
	$salva["Note_Riscontro"] = $cls_help->getVar('note_riscontro');
	$salva["Testo_Riscontro"] = $cls_help->getVar('testo_riscontro');
	$salva["Importo_Riscontro"] = $cls_math->conv_num($cls_help->getVar('importo_riscontro'));

	$salva["Data_Deposito"] = $cls_date->GetDateDB($cls_help->getVar('data_deposito'),"IT");
	$salva["Stato_Deposito"] = $cls_help->getVar('stato_deposito');
	$salva["Data_Vendita"] = $cls_date->GetDateDB($cls_help->getVar('data_vendita'),"IT");
	$salva["Stato_Vendita"] = $cls_help->getVar('stato_vendita');
	$salva["Prezzo_Vendita"] = $cls_math->conv_num($cls_help->getVar('prezzo_vendita'));


	/*$a_params = array(
			'table' => 'notifica_atto',
			'fields'=> array(
			),
			'updateField' => array('name'=>'ID',  'type'=>'int',  'value'=> $id_notifica)
	);

	foreach($salva as $key => $val) {

		$Type = is_numeric($val)?"int":"string";
		if($val=='null'){$Type = "int"; $val="";}

		array_push($a_params['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
	}*/
	$a_params = $cls_Utils->GetObjectQuery($salva,"notifica_atto",array("ID" => $id_notifica));

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->DbSave($a_params))
	{
		$cls_db->Rollback();
		//$error = 1;
		echo 'ERROR '.$cls_db->GetError();
	}
	else
	{
		$cls_db->End_Transaction();
		echo 'OK ';
	}



	//header("Location: riscontro_ivg.php?id_notifica={$id_notifica}&pignoramento={$pignoramento_id}&c={$c}&a={$a}&error={$error}&msg={$msg}");

	/*mysql_query('BEGIN');

	$control_salva = $salva->Update($id_notifica);

	if( $control_salva )
	{
		mysql_query('COMMIT');

		echo 'OK ';
	}
	else
	{
		echo 'ERROR '.mysql_error();
		mysql_query('ROLLBACK');
	}*/



?>
