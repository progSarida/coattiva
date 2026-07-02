<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";


$cls_db = new cls_db();
$cls_help = new cls_help();

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

header("Location: gestore.php?a=".$a."&c=".$c."&msg=".$msg."&error=".$error);

?>
