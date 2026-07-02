<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_paramUtils.php";
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

$invia = $cls_help->getVar('invia_submit');

$ID_ufficio = $cls_help->getVar('ID_ufficio');

$comune = $cls_help->getVar('comune');
$denom = $cls_help->getVar('denom');

$error = 0;
$msg = "";
$action = "";
$storico_msg = "";

$tipo = $cls_help->getVar('tipo');

if($invia == "Salva")
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	$a_paramsComune = array(
			'table' => 'ufficio_comune',
			'fields'=> array(
					array(  'name' => 'Tipo',           'type' => 'string', 'value' => $tipo),
					array(  'name' => 'CC',             'type' => 'string', 'value' => $cls_help->getVar('CC')),
					array(  'name' => 'Denominazione',  'type' => 'string', 'value' => $cls_help->getVar('denom')),
					array(  'name' => 'CC_Comune',     	'type' => 'string', 'value' =>  $cls_help->getVar('CC_sede')),
					array(  'name' => 'Comune',         'type' => 'string', 'value' => $cls_help->getVar('comune_sede')),
					array(  'name' => 'Provincia',      'type' => 'string', 'value' => $cls_help->getVar('prov')),
					array(  'name' => 'Paese',          'type' => 'string', 'value' => 'Italia'),
					array(  'name' => 'Cap',            'type' => 'string', 'value' => $cls_help->getVar('cap')),
					array(  'name' => 'Toponimo',       'type' => 'string', 'value' => strtoupper($cls_help->getVar('via'))),
					array(  'name' => 'Civico',         'type' => 'int',    'value' =>  $cls_help->getVar('civico')),
					array(  'name' => 'Interno',        'type' => 'int',    'value' => $cls_help->getVar('interno')),//
					array(  'name' => 'Dettagli',       'type' => 'string', 'value' => $cls_help->getVar('dettagli')),//
					array(  'name' => 'Telefono',       'type' => 'string', 'value' => $cls_help->getVar('tel')),//
					array(  'name' => 'Fax',       		  'type' => 'string', 'value' => $cls_help->getVar('fax')),//
					array(  'name' => 'Mail',           'type' => 'string', 'value' => $cls_help->getVar('email')),//
					array(  'name' => 'Sito',           'type' => 'string', 'value' => $cls_help->getVar('sito')),//
					array(  'name' => 'PEC',            'type' => 'string', 'value' => $cls_help->getVar('PEC')),//
					array(  'name' => 'Orario',         'type' => 'string', 'value' => $cls_help->getVar('orario')),//
					array(  'name' => 'Modalita_Invio', 'type' => 'string', 'value' => $cls_help->getVar('modalita_invio')),//
					array(  'name' => 'Partita_Iva',    'type' => 'string', 'value' => $cls_help->getVar('PI')),//
					array(  'name' => 'Esponente',      'type' => 'string', 'value' => $cls_help->getVar('esponente'))
			)
	);


	if($ID_ufficio!="-1" && $ID_ufficio!="")
	{
		$a_paramsComune['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $ID_ufficio);

		if(!$cls_db->DbSave($a_paramsComune))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile aggiornare i dati";
		}else{
			$msg= "Dati aggiornati correttamente";
			$action = "U";
			$storico_msg = "Modificato";
		}
	}
	else
	{
		$ID_ufficio = $cls_db->DbSave($a_paramsComune);
		if(!$ID_ufficio)
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile inserire i dati";
		}
		else{
			$msg= "Dati inseriti correttamente";
			$action = "I";
			$storico_msg = "Inserito";
		}
	}

		$cls_db->End_Transaction();

}
else if( $invia == "Delete" && $ID_ufficio!="-1")
{
	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->Delete("ufficio_comune","ID = {$ID_ufficio}"))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore impossibile eliminare i dati";
	}else{
		$msg = "Dati eliminati con successo";
		$action = "D";
		$storico_msg = "Eliminato";
	}

	$cls_db->End_Transaction();
}
else{
	if($ID_ufficio=="-1")
	{
		$error = 2;
		$msg = "Ufficio inesistente";
	}
}

if($error == 0){
	if($tipo == 'uff_anagrafico')
		$storico->insRow($action, $storico_msg." ufficio anagrafico ".$comune);
	if($tipo == 'uff_postale')
		$storico->insRow($action, $storico_msg." ufficio postale ".$comune);
}


header("Location: ufficio_comune.php?&c={$c}&a={$a}&tipo={$tipo}&reload={$ID_ufficio}&error={$error}&msg={$msg}");
?>
