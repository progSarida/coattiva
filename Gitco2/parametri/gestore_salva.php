<?php
//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include_once(ROOT."/_parameter.php");//dati database
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_file = new cls_file();

if(!isset($_SESSION['username']))
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');
$gestore_id = $cls_help->getVar('gestore_id');

$servizio = $cls_help->getVar('servizio');

$msg = "";
$error = 0;

$action = "";
$storico_msg = "";
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$a_img = array(
	'path'=>'',
	'name'=>'',
	'filename'=>''
);

if(isset($_FILES["File_Firma"]) && $_FILES["File_Firma"]['size'] > 0){
	$a_img = array(
		'path'=>$_FILES["File_Firma"]['tmp_name'],
		'name'=>$_FILES["File_Firma"]['name'],
		'filename'=>''
	);
}

	$a_paramsGestore = array(
							'table'=>'gestore',
							'fields'=> array (
										array(  'name' => 'CC',             'type' => 'string', 'value' => $cls_help->getVar('CC')),//
										array(  'name' => 'Comune',         'type' => 'string', 'value' => $cls_help->getVar('comune')),//
										array(  'name' => 'Codice_Fiscale', 'type' => 'string', 'value' => $cls_help->getVar('CF')),//
										array(  'name' => 'Partita_Iva',    'type' => 'string', 'value' => $cls_help->getVar('PI')),//
										array(  'name' => 'Mail',           'type' => 'string', 'value' => $cls_help->getVar('email')),//
										array(  'name' => 'Telefono',       'type' => 'string', 'value' => $cls_help->getVar('tel')),//
										array(  'name' => 'Fax',            'type' => 'string', 'value' => $cls_help->getVar('fax')),//
										array(  'name' => 'PEC',            'type' => 'string', 'value' => $cls_help->getVar('PEC')),//
										array(  'name' => 'Sito',           'type' => 'string', 'value' => $cls_help->getVar('sito')),//
										array(  'name' => 'Toponimo',       'type' => 'string', 'value' => $cls_help->getVar('via')),//
										array(  'name' => 'Civico',         'type' => 'int',    'value' => $cls_help->getVar('civico')),//
										array(  'name' => 'Esponente',      'type' => 'string', 'value' => $cls_help->getVar('esponente')),//
										array(  'name' => 'Interno',        'type' => 'int',    'value' => $cls_help->getVar('interno')),//
										array(  'name' => 'Dettagli',       'type' => 'string', 'value' => $cls_help->getVar('dettagli')),//
										array(  'name' => 'Provincia',      'type' => 'string', 'value' => $cls_help->getVar('prov')),//
										array(  'name' => 'Cap',            'type' => 'string', 'value' => $cls_help->getVar('cap')),//
										array(  'name' => 'Tipo',           'type' => 'string', 'value' => 'Concessionario'),//
										array(  'name' => 'Denominazione',  'type' => 'string', 'value' => $cls_help->getVar('denom')),//
										array(  'name' => 'Paese',          'type' => 'string', 'value' => 'Italia'),//
										array(  'name' => 'Orario',          'type' => 'string', 'value' => ''),//
										array(  'name' => 'Abilitazione',          'type' => 'string', 'value' => $cls_help->getVar('abilitazione')),
										array(  'name' => 'Intestatario',          'type' => 'string', 'value' => $cls_help->getVar('Intestatario')),
										array(  'name' => 'Conto_Corrente',          'type' => 'string', 'value' => $cls_help->getVar('Conto_Corrente')),
										array(  'name' => 'IBAN',          'type' => 'string', 'value' => $cls_help->getVar('IBAN')),
										array(  'name' => 'Scadenza_Giorno',          'type' => 'int', 'value' => $cls_help->getVar('Scadenza_Giorno')),
										array(  'name' => 'Scadenza_Mese',          'type' => 'string', 'value' => $cls_help->getVar('Scadenza_Mese'))//
									)
					);


if($a_img['path'] != "")
{
	$dirPath = $cls_file->folderCreation( SUPER_ROOT."/archivio/firme/".$c );

	$im = new imagick( $a_img['path'] );
	$file_name = "file_gestore_".$c."_".date("Y-m-d");

	$im->setImageCompression(Imagick::COMPRESSION_JPEG);
	$im->setImageCompressionQuality(100);
	$a_img['filename'] = $file_name.'.jpg';
	$im->writeImage( $dirPath."/".$a_img['filename'] );
	$a_paramsGestore['fields'][] = array('name' => "File_Firma",	'type' => 'string',	'value' => $a_img['filename']);
}
	

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if($gestore_id > 0)
{
	$a_paramsGestore['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=>$gestore_id);
	$insertId = $cls_db->DbSave($a_paramsGestore);
	if(!$insertId){
		$cls_db->Rollback();
		$msg = "Salvataggio fallito! Errore nell'aggiornamento del gestore";
				$error = 1;
	}
	$action = "U";
	$storico_msg = "Modificati dati gestore ente ".$nome_ente."[".$c."]";
}
else
{
	$insertId = $cls_db->DbSave($a_paramsGestore);
	if(!$insertId){
		$cls_db->Rollback();
		$msg = "Salvataggio fallito! Errore nell'inserimento del gestore";
				$error = 1;
	}
	else
		$gestore_id = $insertId;

	$action = "I";
	$storico_msg = "Inseriti  dati gestore ente ".$nome_ente."[".$c."]";
}

if($msg=="")
{
	$a_paramsEnte = array(
							'table'=>'enti_gestiti',
							'fields'=> array (
															array(  'name' => 'Gestore_ID',    'type' => 'int',      'value' => $gestore_id)
														),
							'updateField'=> array(  'name'=>'CC',              'type' => 'string',   'value'=> $c)
					);
	if(!$cls_db->DbSave($a_paramsEnte)){
	$cls_db->Rollback();
	$msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ente";
			$error = 1;
	}
	else{
		$cls_db->End_Transaction();
		$msg = "Salvataggio avvenuto con successo";
	}
}

if($error == 0)
	$storico->insRow($action, $storico_msg);

header("Location: gestore.php?a=".$a."&c=".$c."&msg=".$msg."&error=".$error);

?>
