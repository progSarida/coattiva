<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_storico.php";

$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_file = new cls_file();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$check = $cls_help->getVar('flagDel');

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$invia = $cls_help->getVar('invia_submit');

$dirPath = $cls_file->folderCreation( STEMMI."/".$c );
$a_fields = array(1=>"Stemma_1",2=>"Stemma_2",3=>"Stemma_Gestore");
for($i=1;$i<=3;$i++){
	$a_img[$i] = array(
		'path'=>'',
		'name'=>'',
		'filename'=>''
    );

	if(isset($_FILES[$a_fields[$i]]) && $_FILES[$a_fields[$i]]['size'] > 0){
        $a_img[$i] = array(
            'path'=>$_FILES[$a_fields[$i]]['tmp_name'],
            'name'=>$_FILES[$a_fields[$i]]['name'],
            'filename'=>''
        );
	}
}

$msg = "";
$error = 0;
$control_salva = true;

if( $invia == "Salva" )
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	$a_paramsEnte = array(
		'table'=>'enti_gestiti',
		'updateField'=> array('name'=>'CC', 'type' => 'string',	'value'=> $cls_help->getVar('CC'))
	);
	for($i=1;$i<=2;$i++){
        if($a_img[$i]['path'] != "")
        {
            $im = new imagick( $a_img[$i]['path'] );
            $file_name = $a_fields[$i]."_".$c."_".date("Y-m-d");

            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            $im->setImageCompressionQuality(100);
            $a_img[$i]['filename'] = $file_name.'.jpg';
            $im->writeImage( $dirPath."/".$a_img[$i]['filename'] );

            $a_paramsEnte['fields'][] = array('name' => $a_fields[$i],	'type' => 'string',	'value' => $a_img[$i]['filename']);
        }
    }

	if($a_img[1]['filename'] != "" || $a_img[2]['filename'] != "")
	{
		if(!$cls_db->DbSave($a_paramsEnte)){
			$cls_db->Rollback();
			$msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ente";
			$error = 1;
		}
	}
	if($error==0){
		if($a_img[3]['path'] != "") {
            if ($cls_help->getVar('Gestore_ID') > 0) {
                $im = new imagick($a_img[3]['path']);
                $file_name = $a_fields[3] . "_" . $c . "_" . date("Y-m-d");

                $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                $im->setImageCompressionQuality(100);
                $a_img[3]['filename'] = $file_name . '.jpg';
                $im->writeImage($dirPath . "/" . $a_img[3]['filename']);

                $a_paramsGestore = array(
                    'table' => 'gestore',
                    'fields' => array(
                        array('name' => 'Stemma', 'type' => 'string', 'value' => $a_img[3]['filename'])
                    ),
                    'updateField' => array('name' => 'ID', 'type' => 'int', 'value' => $cls_help->getVar('Gestore_ID'))
                );
                if (!$cls_db->DbSave($a_paramsGestore)) {
                    $cls_db->Rollback();
                    $msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ente";
                    $error = 1;
                }
            }
        }
	}

	if($error===0){
		$cls_db->End_Transaction();
		$check = 0;
		$msg = "Salvataggio avvenuto con successo";
	}
}
else if( $invia == "Delete" )
{
	$path = $dirPath."/".$cls_help->getVar("imgName");
	if(is_file($path))
	{
		$cls_db->Start_Transaction();
		$cls_db->Begin_Transaction();

		$a_params = array(
			'table'=>'enti_gestiti',
			'fields'=> array (
				array('name' => '',	'type' => 'string',	'value' => '')
			),
			'updateField'=> array(  'name'=>'CC',	'type' => 'string',	'value'=> $cls_help->getVar('c'))
		);

		switch($cls_help->getVar('flagDel')){
			case 1: $a_params['fields'][0]['name'] = 'Stemma_1'; break;
			case 2: $a_params['fields'][0]['name'] = 'Stemma_2'; break;
			case 3: $a_params = array(
				'table'=>'gestore',
				'fields'=> array (
					array(  'name' => 'Stemma',    'type' => 'string',      'value' => '')
				),
				'updateField'=> array(  'name'=>'ID',	'type' => 'int',	'value'=> $cls_help->getVar('Gestore_ID'))
			); break;
		}

		if(!$cls_db->DbSave($a_params)){
			$cls_db->Rollback();
			$msg = "Aggiornamento fallito! Errore nell'aggiornamento dell'ente";
			$error = 1;
		}
		else {
			if(is_file($path))
			{
				if(unlink($path))
				{
					$cls_db->End_Transaction();
					$msg = "File Eliminato con successo";
				}
				else {
					$msg = "Impossibile eliminare il file, Errore";
					$cls_db->Rollback();
					$error = 1;
				}
			}
		}
	}
	else {
		$msg = "Warning: Il file è già stato eliminato, o non è mai stato inserito.";
		$error = 2;
	}
}

if($error == 0){
	switch ($check){
		case 1:
			$storico->insRow('D', "Eliminato file immagine stemma primario ente ".$nome_ente."[".$c."]");
			break;
		case 2:
			$storico->insRow('D', "Eliminato file immagine stemma secondario ente ".$nome_ente."[".$c."]");
			break;
		case 3:
			$storico->insRow('D', "Eliminato file immagine stemma primario gestore per ente ".$nome_ente."[".$c."]");
			break;
		default:
			$storico->insRow('U', "Modificati file stemmi ente ".$nome_ente."[".$c."]");
			break;
	}
}

header("Location: stemma.php?a=".$a."&c=".$c."&msg=".$msg."&error=".$error);

?>
