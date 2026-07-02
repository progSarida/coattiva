<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_storico.php";


$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();

if(!isset($_SESSION['username']))
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');
$ufficio_id = $cls_help->getVar('ufficio_id');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');


$msg = "";
$error = 0;

$action = "";
$storico_msg = "";
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];


if($invia == "Salva")
{

	$a_paramsUfficio = array(
			'table'=>'parametri_ufficio',
			'fields'=> array (
				array(  'name' => 'CC',                'type' => 'string', 'value' => $c),//
				array(  'name' => 'Comune',            'type' => 'string', 'value' => $cls_help->getVar('comune')),//
				array(  'name' => 'Mail',              'type' => 'string', 'value' => $cls_help->getVar('email')),//
				array(  'name' => 'Telefono',          'type' => 'string', 'value' => $cls_help->getVar('tel')),//
				array(  'name' => 'Fax',               'type' => 'string', 'value' => $cls_help->getVar('fax')),//
				array(  'name' => 'PEC',               'type' => 'string', 'value' => $cls_help->getVar('PEC')),//
				array(  'name' => 'Sito',              'type' => 'string', 'value' => $cls_help->getVar('sito')),//
				array(  'name' => 'Partita_Iva',       'type' => 'string', 'value' => $cls_help->getVar('PI')),
				array(  'name' => 'Via',          'type' => 'string', 'value' => $cls_help->getVar('via')),//
				array(  'name' => 'Civico',            'type' => 'int',    'value' => $cls_help->getVar('civico')),//
				array(  'name' => 'Esponente',         'type' => 'string', 'value' => $cls_help->getVar('esponente')),//
				array(  'name' => 'Interno',           'type' => 'int',    'value' => $cls_help->getVar('interno')),//
				array(  'name' => 'Dettagli',          'type' => 'string', 'value' => $cls_help->getVar('dettagli')),//
				array(  'name' => 'Provincia',         'type' => 'string', 'value' => $cls_help->getVar('prov')),//
				array(  'name' => 'Cap',               'type' => 'string', 'value' => $cls_help->getVar('cap')),//
				array(  'name' => 'Tipo_Riscossione',  'type' => 'string', 'value' => $tipo_riscossione),//
				array(  'name' => 'Denominazione',     'type' => 'string', 'value' => $cls_help->getVar('denom')),//
				array(  'name' => 'Orario',            'type' => 'string', 'value' => $cls_help->getVar('orario'))//
			)
	);

	if($ufficio_id > 0)
	{
        $msg = "Dati aggiornati correttamente!";
		$a_paramsUfficio['updateField'] = array(  'name'=>'ID',     'type' => 'int',      'value'=> $ufficio_id);
		$insertId = $cls_db->DbSave($a_paramsUfficio);
		if(!$insertId){
				$cls_db->Rollback();
				$msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ufficio";
				$error = 1;
		}
		$action = "U";
		$storico_msg = "Modificato";
	}
	else
	{
        $msg = "Dati inseriti correttamente!";
		$insertId = $cls_db->DbSave($a_paramsUfficio);
		if(!$insertId){
				$cls_db->Rollback();
				$msg = "Salvataggio fallito! Errore nell'inserimento dell'ufficio";
				$error = 1;
		}
		else {
			$ufficio_id = $insertId;
		}
		$action = "I";
		$storico_msg = "Inserito";
	}

    $cls_db->End_Transaction();

}

if($error == 0)
	$storico->insRow($action, $storico_msg." ufficio ".$cls_help->getVar('denom')." per l'ente ".$nome_ente."[".$c."]");

header("Location: par_ufficio.php?a=".$a."&c=".$c."&tipo_riscossione=".$tipo_riscossione."&msg=".$msg."&error=".$error);
?>
