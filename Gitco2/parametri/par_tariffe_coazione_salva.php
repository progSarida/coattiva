<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(CONTROLLERS. "/TariffeCoazione.php");
include_once(CLS. "/cls_db.php");
include_once(CLS. "/cls_help.php");// inclusione classe
include_once CLS . "/cls_storico.php";	

$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$ID_tariffa = $cls_help->getVar('tariffa_id');
$getTariffa = $ID_tariffa;

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$TariffeCoazione = new TariffeCoazioneController($c, $ID_tariffa);
$a_tariff = $TariffeCoazione->getTariff();
$a_pignoLocked = $TariffeCoazione->checkTariffInPignoramento();

$action = $cls_help->getVar('invia_submit');
//? CANCELLAZIONE TARIFFE
if($action=="Delete"){

	//?COMMENTO CANCELLAZIONE DI TUTTE LE TARIFFE
	// $check = $TariffeCoazione->deleteTariffs($a_tariff['Descrizione'], $a_tariff['Deposito_Portata']);
	//?INSERITA CANCELLAZIONE SINGOLA TARIFFA
	if(count($a_pignoLocked)>0){
		$error = 1;
		$msg = "Pignoramenti collegati alla tariffa. Cancellazione disabilitata";
		header("Location: par_tariffe_coazione.php?&c={$c}&a={$a}&id_tariffa={$ID_tariffa}&error={$error}&msg={$msg}");
	}
	else{
		$check = $TariffeCoazione->delete($ID_tariffa);

		if($check){
			$error = 0;
			$msg = "Cancellazione avvenuta con successo!";
			$storico->insRow('D', "Eliminati parametri gestione tariffa ".$cls_help->getVar('descrizione_tariffa')." ente ".$nome_ente."[".$c."]");
		}
		else{
			$error = 1;
			$msg = "Cancellazione fallita!";
		}
		header("Location: lista_tariffe_generali.php?&c={$c}&a={$a}&error={$error}&msg={$msg}");
	
	}
	die;
	
}

//? SALVATAGGIO TARIFFE
$coef = "no";
if($cls_help->getVar('coefficiente')!= null) 
	$coef = $cls_help->getVar('coefficiente');

$a_json = $cls_help->getVar("DefaultJSON");
$jsonString = $TariffeCoazione->DefaultJSONUpdateField($a_json);

if($ID_tariffa>0){

	//?COMMENTO MODIFICA DI TUTTE LE TARIFFE CON STESSA DESCRIZIONE E DEPOSITO
	// $a_tariffs = $TariffeCoazione->getUpdateTariffs($a_tariff['Descrizione'], $a_tariff['Deposito_Portata']);
	//?MODIFICA SINGOLA TARIFFA
	$a_tariffs[0] = $a_tariff; 

}
else{
	$a_tariffs = $TariffeCoazione->getInsertCC();
	$a_where = null;
}

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
$a_tariffeDT = $cls_db->getColumnDataTypes("tariffe_coazione");
try{
	foreach($a_tariffs as $tariff){
		$a_save = array(
			"Tipo" => $cls_help->getVar("tipo_tariffa"),
			"CC" => $tariff['CC'],
			"Descrizione"=>$cls_help->getVar("descrizione_tariffa"),
			"Importo" => str_replace(",",".",$cls_help->getVar('importo_tariffa')),
			"Deposito_Portata" => $cls_help->getVar('specifiche_tariffa'),
			"Coefficiente" => $coef,
			"DefaultJSON" => $jsonString,
			"Importo_Fisso" => str_replace(",",".",$cls_help->getVar('importo_fisso')),
			"Km_Giorni_Importo_Fisso" => $cls_help->getVar('durata_fisso')
		);
	
		if($ID_tariffa>0)
			$a_where = array("ID"=>(!empty($a_tariff['ID']))?$a_tariff['ID']:null);
		
		$id = $cls_db->DbSave($cls_db->GetObjectQuery("tariffe_coazione", $a_save, $a_tariffeDT, $a_where));
		if($c == $tariff['CC']){
			if($ID_tariffa==0)
				$getTariffa = $id;
		}
	}
	$cls_db->End_Transaction();
	$error = 0;
	$msg = "Salvataggio avvenuto con successo!";
	if($ID_tariffa == 0)
		$storico->insRow('I', "Inseriti parametri gestione tariffa ".$cls_help->getVar('descrizione_tariffa')." ente ".$nome_ente."[".$c."]");
	else
		$storico->insRow('U', "Modificati parametri gestione tariffa ".$cls_help->getVar('descrizione_tariffa')." ente ".$nome_ente."[".$c."]");
}
catch(Exception $e){
	$cls_db->Rollback();
	$error = 1;
	$msg = "Salvataggio fallito!";
}

header("Location: par_tariffe_coazione.php?&c={$c}&a={$a}&id_tariffa={$getTariffa}&error={$error}&msg={$msg}");

?>