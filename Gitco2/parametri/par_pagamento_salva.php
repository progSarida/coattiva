<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();

$dataAutorizzazione1 = new cls_DateTime($cls_help->getVar('data_aut'),"IT");
$dataAutorizzazione2 = new cls_DateTime($cls_help->getVar('data_aut_2'),"IT");
$dataCambio = new cls_DateTime($cls_help->getVar('data_cambio'),"IT");

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$par_id = $cls_help->getVar('par_id');

$invia = $cls_help->getVar('invia_submit');
$error = 0;
$msg = "";

$tipo_riscossione = $cls_help->getVar('tipo_riscossione');
$titolo_riscossione = $cls_help->getVar('titolo_riscossione');

$action = "";
$storico_msg = "";

$operatore = "=";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];	

if($tipo_riscossione == 'TUTTE')
{
	$operatore = "<>";
}

$query_par_pagamento = 	" 	SELECT DISTINCT  Tipo_Riscossione ". 
						"	FROM parametri_pagamento	". 
						"	WHERE CC = '".$c."'".
						"	AND Tipo_Riscossione ".$operatore." 'TUTTE'";
$a_results = $cls_db->getArrayLine($cls_db->SelectQuery($query_par_pagamento));


$msg_par_pay = "";

if($invia == "Salva")
{
	$contoTerzi = "";
	if(!is_null($cls_help->getVar('conto_terzi')))
		$contoTerzi = $cls_help->getVar('conto_terzi');

	$a_paramsPag = array(
	    'table' => 'parametri_pagamento',
	    'fields'=> array(
	        array(  'name' => 'CC',                           'type' => 'string', 'value' => $cls_help->getVar('c')),
	        array(  'name' => 'Tipo_Riscossione',             'type' => 'string', 'value' => $cls_help->getVar('tipo_riscossione')),
	        array(  'name' => 'Tipo_Conto',                   'type' => 'string', 'value' => $cls_help->getVar('tipo_conto')),
					array(  'name' => 'Tipo_Documento',               'type' => 'string', 'value' => $cls_help->getVar('tipo_documento')),
	        array(  'name' => 'Intestatario_Conto',           'type' => 'string', 'value' => $cls_help->getVar('int_conto')),
	        array(  'name' => 'Numero_Conto',                 'type' => 'string', 'value' => $cls_help->getVar('num_conto')),
	        array(  'name' => 'IBAN',                         'type' => 'string', 'value' => $cls_help->getVar('iban_conto')),
					array(  'name' => 'BICSWIFT',                     'type' => 'string', 'value' => $cls_help->getVar('bic_conto')),
					array(  'name' => 'Bollettino_1',                 'type' => 'string', 'value' => $cls_help->getVar('tipo_bollettino')),
					array(  'name' => 'Bollettino_2',                 'type' => 'string', 'value' => $cls_help->getVar('tipo_bollettino_2')),
					array(  'name' => 'Autorizzazione_1',             'type' => 'string', 'value' => $cls_help->getVar('aut')),
					array(  'name' => 'Autorizzazione_2',             'type' => 'string', 'value' => $cls_help->getVar('aut_2')),
					array(  'name' => 'Data_Autorizzazione_1',        'type' => 'date',   'value' => $dataAutorizzazione1->GetDateDB()),
					array(  'name' => 'Data_Autorizzazione_2',        'type' => 'date',   'value' => $dataAutorizzazione2->GetDateDB()),
					array(  'name' => 'Importo_1',                    'type' => 'string', 'value' => $cls_help->getVar('importo')),
					array(  'name' => 'Importo_2',                    'type' => 'string', 'value' => $cls_help->getVar('importo_2')),
					array(  'name' => 'Stemma',                       'type' => 'string', 'value' => $cls_help->getVar('stemma')),
					array(  'name' => 'Stemma_2',                     'type' => 'string', 'value' => $cls_help->getVar('stemma_2')),
					array(  'name' => 'Importo_1_Pignoramento',       'type' => 'string', 'value' => $cls_help->getVar('importo_pigno')),
					array(  'name' => 'Importo_2_Pignoramento',       'type' => 'string', 'value' => $cls_help->getVar('importo_2_pigno')),
					array(  'name' => 'Scadenza_Sanzione',            'type' => 'int',    'value' => $cls_help->getVar('scadenza_sanzione')),
					array(  'name' => 'Scadenza_Ingiunzione',         'type' => 'int',    'value' => $cls_help->getVar('scadenza_ingiunzione')),
					array(  'name' => 'Scadenza_Avviso',              'type' => 'int',    'value' => $cls_help->getVar('scadenza_avviso')),
					array(  'name' => 'Scadenza_Pignoramento',        'type' => 'int',    'value' => $cls_help->getVar('scadenza_pignoramento')),
					array(  'name' => 'Scadenza_Cautelari	',        'type' => 'int',    'value' => $cls_help->getVar('scadenza_cautelari')),
					array(  'name' => 'Conto_Terzi',                  'type' => 'string', 'value' => $contoTerzi),
					array(  'name' => 'Data_Cambio_Conto',            'type' => 'date',   'value' => $dataCambio->GetDateDB())
	    )
	);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();
	if($par_id == 0)
	{
		if(!$cls_db->DbSave($a_paramsPag))
		{
			$cls_db->Rollback();
			$msg = "Errore, impossibile inserire i dati";
			$error = 1;
		}
		else{
			$msg = "Dati inseriti correttamente";
			$action = "I";
			$storico_msg = "Inseriti parametri pagamento riscossione ";
		}

	}
	else
	{
		$a_paramsPag['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $par_id);

		if(!$cls_db->DbSave($a_paramsPag))
		{
			$cls_db->Rollback();
			$msg = "Errore, impossibile aggiornare i dati";
			$error = 1;
		}
		else{
			$msg = "Dati aggiornati correttamente";
			$action = "U";
			$storico_msg = "Modificati parametri pagamento riscossione ";
		}

	}
	$cls_db->End_Transaction();
}
else if( $invia == "Delete" )
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->Delete("parametri_pagamento","ID = ".$par_id))
	{
			$cls_db->Rollback();
			$msg = "Errore, cancellazione dei dati non riuscita";
			$error = 1;
	}
	else{
		$msg = "Dati eliminati con successo";
		$action = "D";
		$storico_msg = "Eliminati parametri pagamento riscossione ";
	}

	$cls_db->End_Transaction();

}

if($error == 0)
	$storico->insRow($action, $storico_msg.$titolo_riscossione." ente ".$nome_ente."[".$c."]");

header("Location: par_pagamento.php?tipo_riscossione={$cls_help->getVar('tipo_riscossione')}&c={$c}&a={$a}&msg={$msg}&error={$error}");

?>
