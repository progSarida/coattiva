<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once(CLS."/cls_paramUtils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once CLS . "/cls_storico.php";													// inclusione classe

$storico = new storico('storicoParametri','8');
$cls_date = new cls_DateTimeI("DB",false);
$cls_param = new cls_param();
$cls_db = new cls_db();
$cls_help = new cls_help();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$numRows = $cls_help->getVar("num_Rows");
$data_inizio = $cls_help->getVar('data_inizio');
$data_fine = $cls_help->getVar('data_fine');
$tasso = $cls_help->getVar('tasso');
$ID = $cls_help->getVar('ID');
$countErr = 0;
$error = 0;
$msg = "";

$new = $cls_help->getVar('new_flag');
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();


for($i=0;$i<count($data_inizio);$i++)
{
	if($i < $numRows){

		$a_paramsGestInt = array(
		    'table' => 'interessi_tributi',
		    'fields'=> array(
		        array(  'name' => 'Data_Inizio',             'type' => 'date',   'value' => $cls_date->GetDateDB($data_inizio[$i],"IT")),
        		array(  'name' => 'Data_Fine',               'type' => 'date',   'value' => $cls_date->GetDateDB($data_fine[$i],"IT")),
		        array(  'name' => 'Tasso_Interessi',         'type' => 'int',    'value' => $cls_param->conv_num($tasso[$i]))
		    ),
				'updateField' => array(   'name'=>'ID',  'type'=>'int',    'value'=>$ID[$i])
		);

		if(!$cls_db->DbSave($a_paramsGestInt))
		{
			$cls_db->Rollback();
			$countErr++;
		}
	}
	else {
			if($data_inizio[$i]!="")
			{
				$a_paramsGestInt = array(
			    'table' => 'interessi_tributi',
			    'fields'=> array(
	      			array(  'name' => 'CC',                      'type' => 'string', 'value' => $c),
			        array(  'name' => 'Data_Inizio',             'type' => 'date',   'value' => $cls_date->GetDateDB($data_inizio[$i],"IT")),
	        		array(  'name' => 'Data_Fine',               'type' => 'date',   'value' => $cls_date->GetDateDB($data_fine[$i],"IT")),
			        array(  'name' => 'Tasso_Interessi',         'type' => 'int',    'value' => $cls_param->conv_num($tasso[$i]))
			    )
			);

			if(!$cls_db->DbSave($a_paramsGestInt))
			{
				$cls_db->Rollback();
				$countErr++;
			}
		}
	}
}

if($countErr > 0) { $msg = $countErr." campi non sono stati aggiornati o inseriti correttamente"; $error = 1;}
else{
	$msg = "Dati inseriti/aggiornati correttamente";
	if($new)
		$storico->insRow('I', "Inserito nuovo interesse tibuti per ente ".$nome_ente." [".$c."]");
	else
		$storico->insRow('U', "Modificato interesse tibuti per ente ".$nome_ente." [".$c."]");
}

$cls_db->End_Transaction();

header("Location: gestione_interessi_tributi.php?a={$a}&c={$c}&error={$error}&msg={$msg}");

?>
