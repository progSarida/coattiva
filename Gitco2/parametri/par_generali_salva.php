<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";

$cls_db = new cls_db();
$cls_help = new cls_help();


if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$tipo_riscossione = $cls_help->getVar('tipo_riscossione');
$titolo_riscossione = $cls_help->getVar('titolo_riscossione');

$action = "";
$storico_msg = "";

$operatore = "=";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];													// esempio

if($tipo_riscossione == 'TUTTE')
{
	$operatore = "<>";
}

$query_par_generali = 	" 	SELECT DISTINCT  Tipo_Riscossione ". 
						"	FROM parametri_generali	". 
						"	WHERE CC = '".$c."'".
						"	AND Tipo_Riscossione ".$operatore." 'TUTTE'";
$a_results = $cls_db->getArrayLine($cls_db->SelectQuery($query_par_generali));

$msg_par_gen = "";

if(count($a_results) > 0)
{	
	foreach($a_results as $generale)
		{
			$msg_par_gen .= "- ".$generale.'. ';
			
		}
		
		$msg_final = 'È necessario cancellare le seguenti tipologie di parametro generale: '.$msg_par_gen. ' per inserire il parametro richiesto';
		header("Location: par_generali.php?tipo_riscossione={$cls_help->getVar('tipo_riscossione')}&c={$c}&a={$a}&msg={$msg_final}&error=1");
	
}else{


$par_id = $cls_help->getVar('par_id');
$error = 0;
$msg = "";
$Restituzione = $cls_help->getVar('restituzione');
$Restituzione_Mod23O = $cls_help->getVar('restituzione_Mod23O');

$a_paramsParGen = array(
    'table' => 'parametri_generali',
    'fields'=> array(
        array(  'name' => 'Tipo_Riscossione',             'type' => 'string', 'value' => $cls_help->getVar('tipo_riscossione')),
        array(  'name' => 'Spese_Anticipate',             'type' => 'string', 'value' => $cls_help->getVar('spese_anticipate')==="" ? null:$cls_help->getVar('spese_anticipate') ),
        array(  'name' => 'Testo_Spese_Anticipate',       'type' => 'string', 'value' => $cls_help->getVar('testo_spese')),
        array(  'name' => 'SMA',                          'type' => 'string', 'value' => $cls_help->getVar('SMA')),
        array(  'name' => 'Intestatario_SMA',             'type' => 'string', 'value' => $cls_help->getVar('intestatario_SMA')),
        array(  'name' => 'Numero_SMA',                   'type' => 'string', 'value' => $cls_help->getVar('numero_SMA')),
				array(  'name' => 'CC',                           'type' => 'string', 'value' => $c)
    )
);


for($i=1;$i<=count($Restituzione);$i++){
    $key = "Restituzione".$i;
		$a_paramsParGen['fields'][] = array('name' => $key,  'type' => 'string', 'value' => $Restituzione[$i]);
}
for($i=1;$i<=count($Restituzione_Mod23O);$i++){
    $key = "Restituzione".$i."_Mod23O";
		$a_paramsParGen['fields'][] = array('name' => $key,  'type' => 'string', 'value' => $Restituzione_Mod23O[$i]);
}

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if($par_id == 0)
{
	$insertId = $cls_db->DbSave($a_paramsParGen);
	if(!$insertId){
			$cls_db->Rollback();
			$msg = "Salvataggio fallito! Errore nell'inserimento";
			$error = 1;
	}
	else{
		$msg = "Inserimento riuscito, salvataggio avvenuto con successo";
		$action = "I";
		$storico_msg = "Inseriti parametri generali riscossione ";
	}
}
else
{
	$a_paramsParGen['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=>$par_id);
	$insertId = $cls_db->DbSave($a_paramsParGen);
	if(!$insertId){
			$cls_db->Rollback();
			$msg = "Salvataggio fallito! Errore nell'aggiornamento";
			$error = 1;
	}
	else{
		$msg = "Aggiornamento riuscito, salvataggio avvenuto con successo.";
		$action = "U";
		$storico_msg = "Modificati parametri generali riscossione ";
	}
}

$cls_db->End_Transaction();

if($error == 0)
	$storico->insRow($action, $storico_msg.$titolo_riscossione." ente ".$nome_ente."[".$c."]");

header("Location: par_generali.php?tipo_riscossione={$cls_help->getVar('tipo_riscossione')}&c={$c}&a={$a}&msg={$msg}&error={$error}");
}
?>
