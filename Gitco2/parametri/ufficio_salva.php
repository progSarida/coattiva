<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

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
$servizio = $cls_help->getVar('servizio');


$msg = "";
$error = 0;

$action = "";
$storico_msg = "";
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];


if($invia == "Salva")
{

	$a_paramsUfficio = array(
			'table'=>'gestore',
			'fields'=> array (
				array(  'name' => 'CC',             'type' => 'string', 'value' => $cls_help->getVar('CC')),//
				array(  'name' => 'Comune',         'type' => 'string', 'value' => $cls_help->getVar('comune')),//
				array(  'name' => 'Comune_SO',      'type' => 'string', 'value' => $cls_help->getVar('comune_so')),//
				array(  'name' => 'Mail',           'type' => 'string', 'value' => $cls_help->getVar('email')),//
				array(  'name' => 'Telefono',       'type' => 'string', 'value' => $cls_help->getVar('tel')),//
				array(  'name' => 'Fax',            'type' => 'string', 'value' => $cls_help->getVar('fax')),//
				array(  'name' => 'PEC',            'type' => 'string', 'value' => $cls_help->getVar('PEC')),//
				array(  'name' => 'Sito',           'type' => 'string', 'value' => $cls_help->getVar('sito')),//
				array(  'name' => 'Partita_Iva',    'type' => 'string', 'value' => $cls_help->getVar('PI')),
				array(  'name' => 'Toponimo',       'type' => 'string', 'value' => $cls_help->getVar('via')),//
				array(  'name' => 'Civico',         'type' => 'int',    'value' => $cls_help->getVar('civico')),//
				array(  'name' => 'Esponente',      'type' => 'string', 'value' => $cls_help->getVar('esponente')),//
				array(  'name' => 'Interno',        'type' => 'int',    'value' => $cls_help->getVar('interno')),//
				array(  'name' => 'Dettagli',       'type' => 'string', 'value' => $cls_help->getVar('dettagli')),//
				array(  'name' => 'Provincia',      'type' => 'string', 'value' => $cls_help->getVar('prov')),//
				array(  'name' => 'Cap',            'type' => 'string', 'value' => $cls_help->getVar('cap')),//
				array(  'name' => 'Toponimo_SO',       'type' => 'string', 'value' => $cls_help->getVar('via_so')),//
				array(  'name' => 'Civico_SO',         'type' => 'int',    'value' => $cls_help->getVar('civico_so')),//
				array(  'name' => 'Esponente_SO',      'type' => 'string', 'value' => $cls_help->getVar('esponente_so')),//
				array(  'name' => 'Interno_SO',        'type' => 'int',    'value' => $cls_help->getVar('interno_so')),//
				array(  'name' => 'Dettagli_SO',       'type' => 'string', 'value' => $cls_help->getVar('dettagli_so')),//
				array(  'name' => 'Provincia_SO',      'type' => 'string', 'value' => $cls_help->getVar('prov_so')),//
				array(  'name' => 'Cap_SO',            'type' => 'string', 'value' => $cls_help->getVar('cap_so')),//
				array(  'name' => 'Tipo',           'type' => 'string', 'value' => 'Ufficio'),//
				array(  'name' => 'Denominazione',  'type' => 'string', 'value' => $cls_help->getVar('denom')),//
				array(  'name' => 'Paese',          'type' => 'string', 'value' => 'Italia'),//
				array(  'name' => 'Orario',         'type' => 'string', 'value' => $cls_help->getVar('orario'))//
			)
	);

	if($ufficio_id > 0)
	{

		$a_paramsUfficio['updateField'] = array(  'name'=>'ID',     'type' => 'int',      'value'=> $ufficio_id);
		$insertId = $cls_db->DbSave($a_paramsUfficio);
		if(!$insertId){
				$cls_db->Rollback();
				$msg = "Salvataggio fallito! Errore nell'aggiornamento del gestore";
				$error = 1;
		}
		$action = "U";
		$storico_msg = "Modificato";
	}
	else
	{
		$insertId = $cls_db->DbSave($a_paramsUfficio);
		if(!$insertId){
				$cls_db->Rollback();
				$msg = "Salvataggio fallito! Errore nell'inserimento del gestore";
				$error = 1;
		}
		else {
			$ufficio_id = $insertId;
		}
		$action = "I";
		$storico_msg = "Inserito";
	}

	if($msg == "")
	{
		$a_paramsEnte = array(
								'table'=>'enti_gestiti',
								'fields'=> array (
																array(  'name' => 'Ufficio_ID',    'type' => 'int',      'value' => $ufficio_id)
															),
								'updateField'=> array(  'name'=>'CC',              'type' => 'string',   'value'=> $c)
						);

						if(!$cls_db->DbSave($a_paramsEnte)){
								$cls_db->Rollback();
								$msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ente";
								$error = 1;
						}
						else {
							$cls_db->End_Transaction();
			        $msg = "Salvataggio avvenuto con successo";
						}
	}

}

if($error == 0)
	$storico->insRow($action, $storico_msg." ufficio ".$cls_help->getVar('denom')." per l'ente ".$nome_ente."[".$c."]");

header("Location: ufficio.php?a=".$a."&c=".$c."&msg=".$msg."&error=".$error);
?>
