<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');	
$cls_help = new cls_help();
$cls_db = new cls_db();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$par_id = $cls_help->getVar('par_id');

$invia = $cls_help->getVar('invia_submit');

$msg = "";
$error = 0;
$action = "";
$storico_msg = "";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

if($invia == "Salva")
{

	$a_paramsRic = array(
			'table' => 'parametri_ricorso',
			'fields'=> array(
					array(  'name' => 'CC',                           									'type' => 'string', 'value' => $cls_help->getVar('c')),//
					array(  'name' => 'Termini_Corte_Giustizia_Tributaria',     'type' => 'int',    'value' => $cls_help->getVar('termini_cgt')),//
					array(  'name' => 'Termini_Giustizia_Ordinaria',           					'type' => 'int',    'value' => $cls_help->getVar('termini_giust_ord'))
			)
	);


	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if($par_id == 0)
	{
		if(!$cls_db->DbSave($a_paramsRic))
		{
			$cls_db->Rollback();
			$msg = "Errore inserimento dati fallito.";
			$error = 1;
		}
		else{
			$msg = "Inserimento riuscito correttamente.";
			$action = "I";
			$storico_msg = "Inseriti";
		}

	}
	else
	{

		$a_paramsRic['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $par_id);

		if(!$cls_db->DbSave($a_paramsRic))
		{
			$cls_db->Rollback();
			$msg = "Errore aggiornamento dati fallito.";
			$error = 1;
		}
		else{
			$msg = "Aggiornamento riuscito correttamente.";
			$action = "U";
			$storico_msg = "Modificati";
		}

	}
}
else if( $invia == "Delete" )
{

	if(!$cls_db->Delete("parametri_ricorso", "ID = {$par_id}"))
	{
		$cls_db->Rollback();
		$msg = "Errore impossibile eliminare i dati.";
		$error = 1;
	}else{
		$msg = "Dati eliminati correttamente.";
		$action = "D";
		$storico_msg = "Eliminati";
	}

}
	$cls_db->End_Transaction();
if($error == 0)
	$storico->insRow($action, $storico_msg." parametri ricorsi ente ".$nome_ente."[".$c."]");

	header("Location: par_ricorso.php?c={$c}&a={$a}&msg={$msg}&error={$error}");
?>
