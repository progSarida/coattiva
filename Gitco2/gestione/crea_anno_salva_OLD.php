<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_enteUtils.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_utils = new cls_enteUtils();

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$CC_ente = $cls_help->getVar('CC_ente');
$servizio = $cls_help->getVar('servizio');
$error = 0;
$msg = "";

	$campo = "Gestione_Coattiva";
	$valore = "Y";

$anno = $cls_help->getVar('anno');

//CONTROLLO ANNI GESTITI PER SERVIZIO
$query = "SELECT ID FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
$progr_anno = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));// single_answer_query($query);
$invia = $cls_help->getVar('invia_submit');

if($progr_anno!=null && $invia!="Delete")
	$invia = "Update";
else if ($progr_anno==null && $invia!="Delete")
	$invia = "Salva";
else
	$invia = "Delete";

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

if($invia == "Salva")
{

	$a_paramsAnni = array(
			'table' => 'anni_gestiti',
			'fields'=> array(
					array(  'name' => 'CC_Anno',        'type' => 'string', 'value' =>  $CC_ente),
					array(  'name' => 'Anno',             'type' => 'string', 'value' => $anno),
					array(  'name' => 'Gestione_Coattiva',      'type' => 'string', 'value' => $valore)
			)
	);

if(!$cls_db->DbSave($a_paramsAnni))
{
	$cls_db->Rollback();
	$error = 1;
	$msg = "Errore impossibile aggiornare i dati";
}
else
{
	$msg = "Dati aggiornati con successo";

		$query = "SELECT ID FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$anno."-01-01"."' AND Tipo_Riscossione = 'CDS'";


		$par_annuali = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new parametri_annuali( $c, $anno."-01-01", "CDS" );

		$cls_utils->controlloParametri( $c, $anno."-01-01", "*****" ,$par_annuali["ID"]);
}

}
else if( $invia == "Update")
{

	$a_paramsEnte = array(
			'table'=>'anni_gestiti',
			'fields'=> array (
					array(  'name'=>'Gestione_Coattiva',         'type'=>'string',       'value'=>'Y')
			),
			'updateField'=>array(   'name'=>'ID',  'type'=>'string',    'value'=> $progr_anno)
	);

	if(!$cls_db->DbSave($a_paramsEnte)){
			$cls_db->Rollback();
			$msg = "Salvataggio fallito! Errore nell'aggiornamento dell'anno";
			$error = 1;
	}
	else{

			$msg = "Salvataggio avvenuto con successo";
	}
}
else if( $invia == "Delete" )
{
	$query = "SELECT ID FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$progr_anno = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//single_answer_query($query);
	$idAnno = $progr_anno['ID'];
	$query = "UPDATE anni_gestiti SET $campo = 'N' WHERE ID = '$idAnno'";
	$control = $cls_db->ExecuteQuery($query);//mysql_query($query);
	$query = "SELECT Gestione_Coattiva FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$coattiva = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//single_answer_query($query);
	$query = "SELECT Gestione_Targhe_Estere FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$estere = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//single_answer_query($query);
	$query = "SELECT Gestione_Pubblicita FROM anni_gestiti WHERE CC_Anno = '$CC_ente' AND Anno = '$anno'";
	$pubblicita = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//single_answer_query($query);
	if($coattiva["Gestione_Coattiva"]=='N' && $estere["Gestione_Targhe_Estere"]=='N' && $pubblicita["Gestione_Pubblicita"]=='N')
	{
		if(!$cls_db->Delete("anni_gestiti", "CC_Anno = '".$CC_ente."' AND Anno = '".$anno."'"))
		{
			$cls_db->Rollback();
			$msg = "Errore impossibile eliminare i dati";
			$error = 1;
		}else $msg = "Dati eliminati con successo";
		/*$query = "DELETE FROM anni_gestiti WHERE CC_Anno = '".$CC_ente."' AND Anno = '".$anno."'";
		$control = mysql_query($query);*/
	}
	$cls_db->End_Transaction();
	header("Location: elimina_anno.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
	die;
}

$cls_db->End_Transaction();

header("Location: crea_anno.php?c={$c}&a={$a}&error={$error}&msg={$msg}");

?>
