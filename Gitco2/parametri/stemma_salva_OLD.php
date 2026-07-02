<?php

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}*/

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";


$cls_db = new cls_db();
$cls_help = new cls_help();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');
//$servizio = get_var('servizio');

$percorso_salvataggio = $cls_help->crea_dir( STEMMI."/".$c );
$percorso_stemma_1 = "";
$percorso_stemma_2 = "";
$percorso_stemma_3 = "";
$nome_stemma_1 = "";
$nome_stemma_2 = "";
$nome_stemma_3 = "";

if(isset($_FILES['stemma_1']) && $_FILES['stemma_1']['size'] > 0)
{
	$percorso_stemma_1 = $_FILES['stemma_1']['tmp_name'];
	$nome_stemma_1 = $_FILES['stemma_1']['name'];
}
else
{
	$percorso_stemma_1 = "";
	$nome_stemma_1 = "";
}

if(isset($_FILES['stemma_2']) && $_FILES['stemma_2']['size'] > 0)
{
	$percorso_stemma_2 = $_FILES['stemma_2']['tmp_name'];
	$nome_stemma_2 = $_FILES['stemma_2']['name'];
}
else
{
	$percorso_stemma_2 = "";
	$nome_stemma_2 = "";
}

if(isset($_FILES['stemma_3']) && $_FILES['stemma_3']['size'] > 0)
{
	$percorso_stemma_3 = $_FILES['stemma_3']['tmp_name'];
	$nome_stemma_3 = $_FILES['stemma_3']['name'];
}
else
{
	$percorso_stemma_3 = "";
	$nome_stemma_3 = "";
}

// if(isset($_FILES['stemma_4']) && $_FILES['stemma_4']['size'] > 0)
// {
// 	$percorso_stemma_4 = $_FILES['stemma_4']['tmp_name'];
// 	$nome_stemma_4 = $_FILES['stemma_4']['name'];
// }
// else
// {
// 	$percorso_stemma_4 = "";
// 	$nome_stemma_4 = "";
// }
$msg = "";
$error = 0;
$control_salva = true;

if( $invia == "Salva" )
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	//mysql_query('BEGIN');

	//$salva = new ente_gestito($c);
	$a_paramsEnte = array(
							'table'=>'enti_gestiti',
							'fields'=> array (),
							'updateField'=> array(  'name'=>'CC',            'type' => 'string',      'value'=> $cls_help->getVar('CC'))
					);

	if($percorso_stemma_1 != "")
	{
		$im = new imagick( $percorso_stemma_1 );

		$ext = pathinfo($percorso_stemma_1, PATHINFO_EXTENSION);
		$file_name = "Stemma_1_".$c."_".date("Y-m-d");

		$im->setImageCompression(Imagick::COMPRESSION_JPEG);
		$im->setImageCompressionQuality(100);
		$im->writeImage( $percorso_salvataggio."/".$file_name.'.jpg' );

		$stemma_1 = $file_name.'.jpg';
		$a_paramsEnte['fields'][] = array(  'name' => 'Stemma_1',    'type' => 'string',      'value' => $stemma_1);
		//$a_paramsEnte['fields'][0] = ;
	}
	else
	{
		$stemma_1 = "";
	}

	if($percorso_stemma_2 != "")
	{
		$im = new imagick( $percorso_stemma_2 );

		$ext = pathinfo($percorso_stemma_2, PATHINFO_EXTENSION);
		$file_name = "Stemma_2_".$c."_".date("Y-m-d");

		$im->setImageCompression(Imagick::COMPRESSION_JPEG);
		$im->setImageCompressionQuality(100);
		$im->writeImage( $percorso_salvataggio."/".$file_name.'.jpg' );

		$stemma_2 = $file_name.'.jpg';

		$a_paramsEnte['fields'][] = array(  'name' => 'Stemma_2',    'type' => 'string',      'value' => $stemma_2);
	}
	else
	{
		$stemma_2 = "";
	}

if($stemma_1 != "" || $stemma_2 != "")
{
	print_r($a_paramsEnte);
	$control_salva = $cls_db->DbSave($a_paramsEnte);
	if(!$control_salva){
			$cls_db->Rollback();
			$msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ente";
			$error = 1;
	}
}



	//$control_salva = $salva->Update_Stemmi($stemma_1 , $stemma_2);
	//echo "<h3>- ".$control_salva."</br>- ".$percorso_stemma_3."</br>- ".true."</h3>";
	if($control_salva)
	{
		if($percorso_stemma_3 != "")
		{
			$im = new imagick( $percorso_stemma_3 );

			$ext = pathinfo($percorso_stemma_3, PATHINFO_EXTENSION);
			$file_name = "Stemma_Gestore_".$c."_".date("Y-m-d");

			$im->setImageCompression(Imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(100);
			$im->writeImage( $percorso_salvataggio."/".$file_name.'.jpg' );

			$stemma_3 = $file_name.'.jpg';

			//echo "<h1>".$cls_help->getVar('Gestore_ID')."</h1>";


			if($cls_help->getVar('Gestore_ID')>0)
			{
				$a_paramsGestore = array(
										'table'=>'gestore',
										'fields'=> array (
																		array(  'name' => 'Stemma',    'type' => 'string',      'value' => $stemma_3)
																	),
										'updateField'=> array(  'name'=>'ID',            'type' => 'int',      'value'=> $cls_help->getVar('Gestore_ID'))
								);
				$control_salva = $cls_db->DbSave($a_paramsGestore);
				if(!$control_salva){
						$cls_db->Rollback();
						$msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ente";
						$error = 1;
				}
			}
			else $control_salva = true;
			/*	$control_salva = $salva->Gestore->Update_Stemma( $salva->Gestore_ID , $stemma_3 );
			else
				$control_salva = true;	*/
		}
		else $stemma_3 = "";

	}

	if($error===0){
			$cls_db->End_Transaction();
			$msg = "Salvataggio avvenuto con successo";
	}


}
else if( $invia == "Delete" )
{
	//echo "<h1>Delete".strlen ($cls_help->getVar("pathImg"))."</br>".strrpos($cls_help->getVar("pathImg"), "/")."</h1>";

	//echo "</br>".$cls_help->getVar("pathImg");

	$path = $cls_help->getVar("pathImg");

	if(strrpos($path, "/") < (strlen($path) - 1))
	{
		$cls_db->Start_Transaction();
		$cls_db->Begin_Transaction();

		$a_params = array(
								'table'=>'enti_gestiti',
								'fields'=> array (
																array(  'name' => '',    'type' => 'string',      'value' => '')
															),
								'updateField'=> array(  'name'=>'CC',            'type' => 'string',      'value'=> $cls_help->getVar('c'))
						);
	//echo "</br>".$cls_help->getVar("flagDel");
		switch($cls_help->getVar('flagDel')){
			case 1: $a_params['fields'][0]['name'] = 'Stemma_1'; break;
			case 2: $a_params['fields'][0]['name'] = 'Stemma_2'; break;
			case 3: $a_params = array(
									'table'=>'gestore',
									'fields'=> array (
																	array(  'name' => 'Stemma',    'type' => 'string',      'value' => '')
																),
									'updateField'=> array(  'name'=>'ID',            'type' => 'int',      'value'=> $cls_help->getVar('Gestore_ID'))
							); break;
		}

		//print_r($a_paramsEnte);
		$control_salva = $cls_db->DbSave($a_params);
		if(!$control_salva){
				$cls_db->Rollback();
				$msg = "Aggiornamento fallito! Errore nell'aggiornamento dell'ente";
				$error = 1;
		}
		else {
			if(is_file($cls_help->getVar("pathImg")))
			{
				//echo "c'è!!!!!!!!!!!!!!!!!!!!!!!!";
				if(unlink($cls_help->getVar("pathImg")))
				{
					$cls_db->End_Transaction();
					$msg = "File Eliminato con successo";
					//echo "</br><h1>File Eliminato con successo</h1>";
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

header("Location: stemma.php?a=".$a."&c=".$c."&msg=".$msg."&error=".$error);

?>
