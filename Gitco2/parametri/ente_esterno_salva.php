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
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$progr = $cls_help->getVar('progr');

$ID_ente = $cls_help->getVar('ID_ente');
$invia = $cls_help->getVar('invia_submit');
$tipo = $cls_help->getVar('tipo_ente');
$nome = $cls_help->getVar('denominazione');

$comune = $cls_help->getVar('comune');
$frazione = $cls_help->getVar('sezione');

if($frazione == null) $frazione = "";

$msg = "";
$action = "";
$storico_msg = "";
$error = 0;


$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if( $invia == "Delete" )
{

	if(!$cls_db->Delete("enti_esterni", "CC = '".$c."' AND Tipo = '".$tipo."'" ))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore, impossibile eliminare idati.";
	}else{
		$msg = "Dati eliminati correttamente.";
		$action = "D";
		$storico_msg = "Eliminato istituto previdenziale ".$nome;
	}

	$cls_db->End_Transaction();

}
else if($invia == "Insert" || $invia == "Update")
{

	$a_paramsEntiEst = array(
	    'table' => 'enti_esterni',
	    'fields'=> array(
	        array(  'name' => 'CC',            		 'type' => 'string', 'value' => '*****'),
	        array(  'name' => 'Denominazione',     'type' => 'string', 'value' => $cls_help->getVar('denominazione')),
	        array(  'name' => 'Partita_Iva', 	  	 'type' => 'string', 'value' => $cls_help->getVar('PI')),
	        array(  'name' => 'CC_Ente',    			 'type' => 'string', 'value' => $cls_help->getVar('CC_ente')),
	        array(  'name' => 'Tipo',              'type' => 'string', 'value' => $tipo),
	        array(  'name' => 'Comune',            'type' => 'string', 'value' => $cls_help->getVar('comune')),
	        array(  'name' => 'Provincia',         'type' => 'string', 'value' => $cls_help->getVar('prov')),
	        array(  'name' => 'Cap',               'type' => 'string', 'value' => $cls_help->getVar('cap')),
	        array(  'name' => 'Frazione',          'type' => 'string', 'value' => $frazione),
	        array(  'name' => 'Toponimo',          'type' => 'string', 'value' => $cls_help->getVar('via')),
	        array(  'name' => 'Civico',            'type' => 'int',    'value' => $cls_help->getVar('civico')),
	        array(  'name' => 'Esponente',      	 'type' => 'string', 'value' => $cls_help->getVar('esponente')),
	        array(  'name' => 'Interno',        	 'type' => 'int',    'value' => $cls_help->getVar('interno')),
	        array(  'name' => 'Dettagli',       	 'type' => 'string', 'value' => $cls_help->getVar('dettagli')),
	        array(  'name' => 'Telefono',      		 'type' => 'string', 'value' => $cls_help->getVar('tel')),
	        array(  'name' => 'Fax',            	 'type' => 'string', 'value' => $cls_help->getVar('fax')),
	        array(  'name' => 'Mail',           	 'type' => 'string', 'value' => $cls_help->getVar('email')),
	        array(  'name' => 'Sito',  						 'type' => 'string', 'value' => $cls_help->getVar('sito')),
	        array(  'name' => 'PEC',          		 'type' => 'string', 'value' => $cls_help->getVar('PEC')),
	        array(  'name' => 'Note',        			 'type' => 'string', 'value' => $cls_help->getVar('note')),
	        array(  'name' => 'Paese',        		 'type' => 'string', 'value' => 'Italia')
	    )
	);

	if($invia == "Insert")
	{
		
		$query = "SELECT MAX(progressivo) AS Progr FROM  enti_esterni WHERE CC = '*****' AND Tipo = '{$tipo}'";
		$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
		
		if($result == null) $result = 1;
		else $result = (int) $result["Progr"] + 1;
		$progr = $result;

		array_push($a_paramsEntiEst['fields'],array(  'name' => 'progressivo', 'type' => 'int', 'value' => $result));

		if(!$cls_db->DbSave($a_paramsEntiEst))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, inserimento fallito.";
		}else{
			$msg = "Dati inseriti con successo.";
			$action = "I";
			$storico_msg = "Inserito istituto previdenziale ".$nome;
		}
	}
	else
	{
		$a_paramsEntiEst['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $ID_ente);
		if(!$cls_db->DbSave($a_paramsEntiEst))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, aggiornamento fallito.";
		}else{
			$msg = "Dati aggiornati con successo.";
			$action = "U";
			$storico_msg = "Modificato istituto previdenziale ".$nome;
		}
	}

	$cls_db->End_Transaction();

}

if($error == 0)
	$storico->insRow($action, $storico_msg);
header("Location: ente_esterno.php?&c={$c}&a={$a}&tipo_ente={$tipo}&progr={$progr}&msg={$msg}&error={$error}");

?>
