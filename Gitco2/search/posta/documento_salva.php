<?php

	if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";
	include_once CLS . "/cls_anagrafeUtils.php";
	include_once CLS . "/cls_DateTimeInLine.php";

	$cls_help = new cls_help();
	$cls_db = new cls_db();
	$cls_anagrUtl = new cls_anagr();
	$cls_date = new cls_DateTimeI("IT",false);

	//if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$id_doc = $cls_help->getVar('id_doc');
	$cancella = $cls_help->getVar('cancella');
	$upload_dir = $cls_anagrUtl->crea_dir( ATTI ."/". $c . "/Documenti" );

	$nuovo_file = "";
	$Comune_ID_DB = -1;

if($cancella == "no")
{

	$del_file = $cls_help->getVar('del_file');
	if($del_file=="") $del_file="si";

	//$atto = $cls_help->getVar('atto');
	//$tipo = $cls_help->getVar('tipo');
	//$data_creazione = date('Y-m-d');
	//$info = $cls_help->getVar('info');
//	$oggetto = $cls_help->getVar('oggetto');
//	$contenuto = $cls_help->getVar('contenuto');
	//$data_stampa = $cls_date->GetDateDB($cls_help->getVar('data_stampa'),"IT");
	$utente = $p;

	$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
	$resultID = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	$comune_id = $resultID["Com"];

	//$salva = new documento( $id_doc , $c );

	//$salva->CC = $c;

	if($id_doc==0) $Comune_ID_DB = $comune_id + 1;
	else $Comune_ID_DB = $id_doc;

	$query = "SELECT File FROM documento WHERE ID = '".$id_doc."' AND CC = '".$c."'";
	$res = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"documento")["File"];

	if($del_file=="si")
	{
		unlink($upload_dir."/".$res);

		//$salva->File = "";
	}

	if(isset($_FILES['file_doc']) && $_FILES['file_doc']['size'] > 0)
	{

		$data_file = date('Y-m-d_H-i-s');
		$file = $_FILES['file_doc'];
		$file_ext = explode(".",$file['name']);
		$estensione = $file_ext[count($file_ext)-1];

		$nuovo_file = "Doc_Manuale_".$data_file.".".$estensione;
		if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']))
		{
			move_uploaded_file($file['tmp_name'], $upload_dir."/".$nuovo_file);
		}

		//$salva->File = $nuovo_file;

	}

	$a_paramsDoc = array(
	    'table' => 'documento',
	    'fields'=> array(
	        array(  'name' => 'CC',             'type' => 'string', 'value' => $c),
	        array(  'name' => 'Utente_ID',         'type' => 'string', 'value' => $p),
	        array(  'name' => 'Tipo', 'type' => 'string', 'value' => $cls_help->getVar('tipo')),
	        array(  'name' => 'Atto',    'type' => 'string', 'value' => $cls_help->getVar('atto')),
	        array(  'name' => 'Data_Creazione',           'type' => 'string', 'value' => date('Y-m-d')),
	        array(  'name' => 'Informazioni_Aggiuntive',       'type' => 'string', 'value' => $cls_help->getVar('info')),
	        array(  'name' => 'Oggetto',            'type' => 'string', 'value' => $cls_help->getVar('oggetto')),
	        array(  'name' => 'Contenuto',            'type' => 'string', 'value' => $cls_help->getVar('contenuto')),
	        array(  'name' => 'Data_Stampa',           'type' => 'string', 'value' => $cls_date->GetDateDB($cls_help->getVar('data_stampa'),"IT")),
	        array(  'name' => 'File',            'type' => 'string', 'value' => $nuovo_file),
	        array(  'name' => 'Comune_ID',            'type' => 'string', 'value' => $Comune_ID_DB)
	    )
	);


	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

//	$salva->Utente_ID = $p;
//	$salva->Tipo = $tipo;
	//$salva->Atto = $atto;
	//$salva->Data_Creazione = $data_creazione;
	//$salva->Informazioni_Aggiuntive = $info;
	//$salva->Oggetto = $oggetto;
	//$salva->Contenuto = $contenuto;
	//$salva->Data_Stampa = $data_stampa;

	//mysql_query('BEGIN');
	$error = true;

	if($id_doc!=0)
	{
		$a_paramsDoc['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $id_doc);
		$error = $cls_db->DbSave($a_paramsDoc);
		if(!$error)
		{
			$cls_db->Rollback();
		}
		//$control_salva = $salva->Update( $id_doc , true );
	}
	else
	{
		$error = $cls_db->DbSave($a_paramsDoc);
		if(!$error)
		{
			$cls_db->Rollback();
		}
		//$control_salva = $salva->Insert( true );
	}

	if(!$error) echo 'ERROR '.$cls_db->GetError();
	else echo "OK";

	$cls_db->End_Transaction();
	/*if( $control_salva )
	{
		mysql_query('COMMIT');

		echo 'OK ';
	}
	else
	{
		mysql_query('ROLLBACK');

		echo 'ERROR '.mysql_error();
	}*/

}
else
{
	//$query = "DELETE FROM documento WHERE ID = '" . $this->ID . "' ";
	//$error = false;

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();
	//$cancella = new documento( $id_doc , $c );
	$query = "SELECT * FROM documento WHERE ID = '".$id_doc."' AND CC = '".$c."'";
	$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$control_cancella = $cancella->Delete();
	//$atto = new atto(null, $c);
	$control_atto = atto_collegato_al_documento($id_doc,$c);
	$control_doc = $cls_db->Delete("documento" ,"ID = ".$id_doc);

	if( $control_doc && $control_atto )
	{
		//mysql_query('COMMIT');
		if(is_file($upload_dir."/".$result["File"]))
			unlink($upload_dir."/".$result["File"]);

		echo 'DELETE';
	}
	else
	{
		//mysql_query('ROLLBACK');

		echo 'ERROR '.$cls_db->GetError();
	}
	$cls_db->End_Transaction();
}

function atto_collegato_al_documento($id_doc, $c)
{
		$cls_db = new cls_db();

	$query = "UPDATE atto SET ID_Richiesta_Rateizzazione = null WHERE ID_Richiesta_Rateizzazione = '$id_doc' ";
	$query.= "AND CC = '".$c."'";

	$control_query = $cls_db->ExecuteQuery($query);// mysql_query($query);
	if($control_query===false)
		return $control_query;

	$query = "UPDATE atto SET ID_Esito_Rateizzazione = null WHERE ID_Esito_Rateizzazione = '$id_doc' ";
	$query.= "AND CC = '".$c."'";

	$control_query = $cls_db->ExecuteQuery($query);// mysql_query($query);
	if($control_query===false)
		return $control_query;

	$query = "UPDATE atto SET ID_Bollettini_Rateizzazione = null WHERE ID_Bollettini_Rateizzazione = '$id_doc' ";
	$query.= "AND CC = '".$c."'";

	$control_query = $cls_db->ExecuteQuery($query);//mysql_query($query);
	if($control_query===false)
		return $control_query;

	return $control_query;


}
?>
