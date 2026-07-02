<?php
	if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";
	include_once CLS . "/cls_DateTimeInLine.php";
	include_once CLS . "/cls_paramUtils.php";

	$cls_help = new cls_help();
	$cls_db = new cls_db();
	$cls_date = new cls_DateTimeI("DB");
	$cls_param = new cls_param();


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

	$CC = "";
	$Comune_ID = -1;
	$File ="";

if($cancella == "no")
{
	$del_file = $cls_help->getVar('del_file');
	if($del_file=="") $del_file="si";

	$data_stampa = $cls_date->GetDateDB($cls_help->getVar('data_stampa'),"IT");

	$query = "SELECT MAX(Comune_ID) as Com FROM documento_ente WHERE CC = '".$c."'";
	$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	$comune_id = $result["Com"]==null?0:$result["Com"];


	$query = "SELECT * FROM documento_ente WHERE ID = '".$id_doc."' AND CC = '".$c."'";
	$result_2 = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));


	$CC = $c;
	$File = isset($result_2["File"])?$result_2["File"]:null;

	if($id_doc==0) $Comune_ID = $comune_id + 1;
	else $Comune_ID = $result_2["Comune_ID"];

	$upload_dir = $cls_param->crea_dir( ATTI ."/". $c . "/Documenti" );
	if($del_file=="si")
	{
		unlink($upload_dir."/".$File);

		$File = "";
	}

	if( isset($_FILES['file_doc_ente']) && $_FILES['file_doc_ente']['size'] > 0 )
	{

		$data_file = date('Y-m-d_H-i-s');
		$file = $_FILES['file_doc_ente'];
		$file_ext = explode(".",$file['name']);
		$estensione = $file_ext[count($file_ext)-1];

		$nuovo_file = "Doc_Manuale_".$data_file.".".$estensione;
		if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']))
		{
			move_uploaded_file($file['tmp_name'], $upload_dir."/".$nuovo_file);
		}

		$File = $nuovo_file;

	}

	$a_paramsDoc = array(
			'table'=>'documento_ente',
			'fields'=> array (
											array(  'name' => 'CC',                        'type' => 'string', 'value' => $CC),
											array(  'name' => 'Ente_Esterno_ID',           'type' => 'int',    'value' => $cls_help->getVar('id_ente')),
											array(  'name' => 'Tipo',                      'type' => 'string', 'value' => $cls_help->getVar('tipo')),
											array(  'name' => 'Atto',                      'type' => 'string', 'value' => $cls_help->getVar('atto')),
											array(  'name' => 'Data_Creazione',            'type' => 'date',   'value' => date('Y-m-d')),
											array(  'name' => 'Informazioni_Aggiuntive',   'type' => 'string', 'value' => $cls_help->getVar('info')),
											array(  'name' => 'Oggetto',                   'type' => 'string', 'value' => $cls_help->getVar('oggetto')),
											array(  'name' => 'Contenuto',                 'type' => 'string', 'value' => $cls_help->getVar('contenuto')),
											array(  'name' => 'Data_Stampa',               'type' => 'date',   'value' => $data_stampa),
											array(  'name' => 'File',                      'type' => 'string', 'value' => $File),
											array(  'name' => 'Comune_ID',                 'type' => 'int',    'value' => $Comune_ID),
										)
	);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if($id_doc!=0)
	{
		$a_paramsDoc['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $id_doc);

		if(!$cls_db->DbSave($a_paramsDoc))
		{
			echo "ERROR ".$cls_db->GetError();
			$cls_db->Rollback();
		}

	}
	else
	{
		if(!$cls_db->DbSave($a_paramsDoc))
		{
			$cls_db->Rollback();
			echo "ERROR ".$cls_db->GetError();
		}
	}

	$cls_db->End_Transaction();

	echo "OK";


}
else
{
	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->Delete("documento_ente","ID = ".$id_doc))
	{
		$cls_db->Rollback();
		echo "ERROR ".$cls_db->GetError();
	}

	$cls_db->End_Transaction();

	echo "DELETE";
}

?>
