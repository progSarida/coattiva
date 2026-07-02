<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_paramUtils.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_param = new cls_param();

$error = 0;
$msg = "";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');
$titolo_riscossione = $cls_help->getVar('titolo_riscossione');

$par_id = $cls_help->getVar('par_id');
$invia = $cls_help->getVar('invia_submit');

$action = "";
$storico_msg = "";

$operatore = "=";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];	

if($tipo_riscossione == 'TUTTE')
{
	$operatore = "<>";
}

$query_par_responsabili = 	" 	SELECT DISTINCT  Tipo_Riscossione ". 
							"	FROM parametri_responsabili	". 
							"	WHERE CC = '".$c."'	".
							"	AND Tipo_Riscossione ".$operatore." 'TUTTE'	";
$a_results = $cls_db->getResults($cls_db->SelectQuery($query_par_responsabili));

$msg_par_res = "";

if(count($a_results) > 0)
{	
	foreach($a_results as $responsabile)
		{
			$msg_par_res .= "- ".$responsabile.'. ';
		}
		
		$msg_final = 'È necessario cancellare le seguenti tipologie di parametro responsabile: '.$msg_par_res. ' per inserire il parametro richiesto';
		header("Location: par_responsabili.php?tipo_riscossione={$tipo_riscossione}&c={$c}&a={$a}&msg={$msg_final}&error=1");
	
}else{

$percorso_funzionario = "";
$percorso_responsabile = "";
$percorso_ufficiale = "";
$percorso_richieste = "";
$percorso_funz_risc = "";
$PathFile = FIRME."/".$c."/";

$Scelta_Firma_Responsabile_Richieste = $cls_help->getVar('scelta_firma_responsabile_richieste');
$Scelta_Firma_Responsabile = $cls_help->getVar('scelta_firma_responsabile');
$Scelta_Firma_Funzionario = $cls_help->getVar('scelta_firma_funzionario');
$Scelta_Firma_Ufficiale = $cls_help->getVar('scelta_firma_ufficiale');
$Scelta_Firma_Funzionario_Riscossione = $cls_help->getVar('scelta_firma_funzionario_riscossione');


$percorso_salvataggio = $cls_help->crea_dir( FIRME."/".$c );

if(isset($_FILES['funzionario_firma']) && $_FILES['funzionario_firma']['size'] > 0)
{
	$percorso_funzionario = $_FILES['funzionario_firma']['tmp_name'];

	if(is_file($PathFile.$cls_help->getVar("funzionario_firma")))
		unlink($PathFile.$cls_help->getVar("funzionario_firma"));
}

if(isset($_FILES['responsabile_firma']) && $_FILES['responsabile_firma']['size'] > 0)
{
	$percorso_responsabile = $_FILES['responsabile_firma']['tmp_name'];

	if(is_file($PathFile.$cls_help->getVar("responsabile_firma")))
		unlink($PathFile.$cls_help->getVar("responsabile_firma"));
}


if(isset($_FILES['ufficiale_firma']) && $_FILES['ufficiale_firma']['size'] > 0)
{
	$percorso_ufficiale = $_FILES['ufficiale_firma']['tmp_name'];

	if(is_file($PathFile.$cls_help->getVar("ufficiale_firma")))
		unlink($PathFile.$cls_help->getVar("ufficiale_firma"));
}

if(isset($_FILES['richieste_firma']) && $_FILES['richieste_firma']['size'] > 0)
{
	$percorso_richieste = $_FILES['richieste_firma']['tmp_name'];

	if(is_file($PathFile.$cls_help->getVar("richieste_firma")))
		unlink($PathFile.$cls_help->getVar("richieste_firma"));
}

if(isset($_FILES['funz_risc_firma']) && $_FILES['funz_risc_firma']['size'] > 0)
{
	$percorso_funz_risc = $_FILES['funz_risc_firma']['tmp_name'];

	if(is_file($PathFile.$cls_help->getVar("funz_risc_firma")))
		unlink($PathFile.$cls_help->getVar("funz_risc_firma"));
}

if($invia == "Salva")
{

	$Funzionario_Firma = "";
	$Funzionario_Testo = "";
	$Responsabile_Firma = "";
	$Responsabile_Testo = "";
	$Ufficiale_Firma = "";
	$Ufficiale_Testo = "";
	$Responsabile_Richieste_Firma = "";
	$Responsabile_Richieste_Testo = "";
	$Funz_Risc_Firma = "";
	$Funz_Risc_Testo = "";

	if($percorso_funzionario != "" && $cls_help->getVar('scelta_firma_funzionario')=="firma")
		$Funzionario_Firma = $cls_param->SaveImage($percorso_funzionario, $c, $tipo_riscossione,1);
	else
		$Funzionario_Firma = $cls_help->getVar('funzionario_firma');

	if($Scelta_Firma_Funzionario=="testo")
	{
		if(file_exists($PathFile.$cls_help->getVar("funzionario_firma")))
			unlink($PathFile.$cls_help->getVar("funzionario_firma"));
		$Funzionario_Firma = "";
		$Funzionario_Testo = "si";
	}

	if($percorso_responsabile != "" && $Scelta_Firma_Responsabile=="firma")
		$Responsabile_Firma = $cls_param->SaveImage($percorso_responsabile, $c, $tipo_riscossione,2);
	else
		$Responsabile_Firma = $cls_help->getVar('responsabile_firma');

	if($Scelta_Firma_Responsabile=="testo")
	{
		if(is_file($PathFile.$cls_help->getVar("responsabile_firma")))
			unlink($PathFile.$cls_help->getVar("responsabile_firma"));
		$Responsabile_Firma = "";
		$Responsabile_Testo = "si";
	}

	if($percorso_ufficiale != "" && $Scelta_Firma_Ufficiale=="firma")
		$Ufficiale_Firma = $cls_param->SaveImage($percorso_ufficiale, $c, $tipo_riscossione,3);
	else
		$Ufficiale_Firma = $cls_help->getVar("ufficiale_firma");

	if($Scelta_Firma_Ufficiale=="testo")
	{
		if(is_file($PathFile.$cls_help->getVar("ufficiale_firma")))
			unlink($PathFile.$cls_help->getVar("ufficiale_firma"));
		$Ufficiale_Firma = "";
		$Ufficiale_Testo = "si";
	}

	if($percorso_richieste != "" && $Scelta_Firma_Responsabile_Richieste=="firma")
		$Responsabile_Richieste_Firma = $cls_param->SaveImage($percorso_richieste, $c, $tipo_riscossione,4);
	else
		$Responsabile_Richieste_Firma = $cls_help->getVar("richieste_firma");

	if($Scelta_Firma_Responsabile_Richieste=="testo")
	{
		if(is_file($PathFile.$cls_help->getVar("richieste_firma")))
			unlink($PathFile.$cls_help->getVar("richieste_firma"));
		$Responsabile_Richieste_Firma = "";
		$Responsabile_Richieste_Testo = "si";
	}
	// var_dump($percorso_richieste,$Scelta_Firma_Responsabile_Richieste,$cls_help->getVar('richieste_firma'));
	// var_dump($percorso_funz_risc,$Scelta_Firma_Funzionario_Riscossione,$cls_help->getVar('funz_risc_firma'));
	// die;
	if($percorso_funz_risc != "" && $Scelta_Firma_Funzionario_Riscossione=="firma")
		$Funz_Risc_Firma = $cls_param->SaveImage($percorso_funz_risc, $c, $tipo_riscossione,5);
	else
		$Funz_Risc_Firma = $cls_help->getVar('funz_risc_firma');

	if($Scelta_Firma_Funzionario_Riscossione=="testo")
	{
		if(file_exists($PathFile.$cls_help->getVar("funz_risc_firma")))
			unlink($PathFile.$cls_help->getVar("funz_risc_firma"));
		$Funz_Risc_Firma = "";
		$Funz_Risc_Testo = "si";
	}

		$a_paramsResp = array(
		    'table' => 'parametri_responsabili',
		    'fields'=> array(
		        array(  'name' => 'CC',                           		'type' => 'string', 'value' => $cls_help->getVar('c')),//
		        array(  'name' => 'Tipo_Riscossione',             		'type' => 'string', 'value' => $cls_help->getVar('tipo_riscossione')),//
		        array(  'name' => 'Testo_Sostitutivo',           		'type' => 'string', 'value' => $cls_help->getVar('testo_sostitutivo')),//
				array(  'name' => 'Funzionario_Responsabile',     		'type' => 'string', 'value' => $cls_help->getVar('funzionario')),//
		        array(  'name' => 'Funzionario_Telefono',         		'type' => 'string', 'value' => $cls_help->getVar('tel_funzionario')),//
		        array(  'name' => 'Responsabile_Procedimento',    		'type' => 'string', 'value' => $cls_help->getVar('responsabile')),//
		        array(  'name' => 'Responsabile_Telefono',        		'type' => 'string', 'value' => $cls_help->getVar('tel_responsabile')),//
				array(  'name' => 'Ufficiale_Riscossione',            'type' => 'string', 'value' => $cls_help->getVar('ufficiale')),
				array(  'name' => 'Ufficiale_Telefono',               'type' => 'string', 'value' => $cls_help->getVar('tel_ufficiale')),
				array(  'name' => 'Responsabile_Richieste',           'type' => 'string', 'value' => $cls_help->getVar('richieste')),//
				array(  'name' => 'Responsabile_Richieste_Telefono',  'type' => 'string', 'value' => $cls_help->getVar('tel_richieste')),//
				array(  'name' => 'Funzionario_Firma',             		'type' => 'string', 'value' => $Funzionario_Firma),//
				array(  'name' => 'Funzionario_Testo',      				  'type' => 'string', 'value' => $Funzionario_Testo),//
				array(  'name' => 'Responsabile_Firma',       				'type' => 'string', 'value' => $Responsabile_Firma),//
				array(  'name' => 'Responsabile_Testo',               'type' => 'string', 'value' => $Responsabile_Testo),//
				array(  'name' => 'Ufficiale_Firma',                  'type' => 'string', 'value' => $Ufficiale_Firma),//
				array(  'name' => 'Ufficiale_Testo',                  'type' => 'string', 'value' => $Ufficiale_Testo),//
				array(  'name' => 'Responsabile_Richieste_Firma',     'type' => 'string', 'value' => $Responsabile_Richieste_Firma),//
				array(  'name' => 'Responsabile_Richieste_Testo',     'type' => 'string', 'value' => $Responsabile_Richieste_Testo),
				array(  'name' => 'Legale_Rappresentante',            'type' => 'string', 'value' => $cls_help->getVar('legale_rappresentante')),//
				array(  'name' => 'Legale_Rappresentante_Telefono',   'type' => 'string', 'value' => $cls_help->getVar('legale_rappresentante_telefono')),//
				array(  'name' => 'Legale_Rappresentante_Firma',      'type' => 'string', 'value' => $cls_help->getVar('legale_rappresentante_firma')),//
				array(  'name' => 'Legale_Rappresentante_Testo',      'type' => 'string', 'value' => $cls_help->getVar('legale_rappresentante_testo')),//
				array(  'name' => 'Funzionario_Riscossione',            'type' => 'string', 'value' => $cls_help->getVar('funz_risc')),//
				array(  'name' => 'Funzionario_Riscossione_Telefono',   'type' => 'string', 'value' => $cls_help->getVar('tel_funz_risc')),//
				array(  'name' => 'Funzionario_Riscossione_Firma',      'type' => 'string', 'value' => $Funz_Risc_Firma),//
				array(  'name' => 'Funzionario_Riscossione_Testo',      'type' => 'string', 'value' => $Funz_Risc_Testo)//

		    )
		);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if($par_id == 0)
	{
		if(!$cls_db->DbSave($a_paramsResp))
		{
			$cls_db->Rollback();
			$msg = "Errore inserimento dati fallito.";
			$error = 1;
		}
		else{
			$msg = "Inserimento riuscito correttamente.";
			$action = "I";
			$storico_msg = "Inseriti parametri responsabili riscossione ";
		}
	}
	else
	{
		$a_paramsResp['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $par_id);

		if(!$cls_db->DbSave($a_paramsResp))
		{
			$cls_db->Rollback();
			$msg = "Errore nell'aggiornamento dei dati.";
			$error = 1;
		}
		else{
			$msg = "Aggiornamento riuscito.";
			$action = "U";
			$storico_msg = "Modificati parametri responsabili riscossione ";
		}
	}

	$cls_db->End_Transaction();
}
else if( $invia == "Delete" )
{

	if(is_file($PathFile.$cls_help->getVar("funzionario_firma")))
		unlink($PathFile.$cls_help->getVar("funzionario_firma"));
	if(is_file($PathFile.$cls_help->getVar("responsabile_firma")))
		unlink($PathFile.$cls_help->getVar("responsabile_firma"));
	if(is_file($PathFile.$cls_help->getVar("ufficiale_firma")))
		unlink($PathFile.$cls_help->getVar("ufficiale_firma"));
	if(is_file($PathFile.$cls_help->getVar("richieste_firma")))
		unlink($PathFile.$cls_help->getVar("richieste_firma"));
	if(is_file($PathFile.$cls_help->getVar("funz_risc_firma")))
		unlink($PathFile.$cls_help->getVar("funz_risc_firma"));

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->Delete("parametri_responsabili","CC = '".$c."' and tipo_riscossione = '".$tipo_riscossione."'"))
	{
		$cls_db->Rollback();
		$msg = "Errore impossibile eliminare i dati.";
		$error = 1;
	}else{
		$msg = "Dati eliminati con successo";
		$action = "D";
		$storico_msg = "Eliminati parametri responsabili riscossione ";
	}

	$cls_db->End_Transaction();

}

if($error == 0)
	$storico->insRow($action, $storico_msg.$titolo_riscossione." ente ".$nome_ente."[".$c."]");

header("Location: par_responsabili.php?tipo_riscossione={$tipo_riscossione}&c={$c}&a={$a}&msg={$msg}&error={$error}");
}
?>
