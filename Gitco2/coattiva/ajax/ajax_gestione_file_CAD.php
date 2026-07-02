<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_CaricaImmaginiCAD.php";
include_once CLS . "/cls_paramUtils.php";
include_once CLS . "/cls_LOGImmaginiCAD.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$LogImmagini = new LOGImmaginiCAD($cls_db);

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$stato = $cls_help->getVar('stato');
$flag = $cls_help->getVar('flag');
$path = $cls_help->getVar('path');
$filename = $cls_help->getVar('filename');
$filenameR = $cls_help->getVar('filenameR');
$filenameF= $cls_help->getVar('filenameF');
$Data_Elaborazione = date("Y-m-d H:i:s");
$Operatore = $_SESSION['username'];

$evaluate = function($stato,$path,$filename) use ($LogImmagini,$Data_Elaborazione,$Operatore){

	$fun=array(1=>"SetLavorato",2=>"SetNonProcessato",3=>"SetScartato",4=>"SetCDS",5=>"SetDuplicato");
	$operazione=array(1=>"lavorato",2=>"non processato",3=>"scartato",4=>"CDS",5=>"duplicato");
	call_user_func("CaricamentoImmaginiCAD::".$fun[$stato],$path,$filename);
	$LogImmagini
		->Set("operatore",$Operatore)
		->Set("data_elaborazione",$Data_Elaborazione)
		->Set("nome_file",$filename)
		->Set("operazione",$operazione[$stato])
		->InserimentoDati();
};

try{

	if($flag) {
		$evaluate($stato,$path,$filenameF);
		$evaluate($stato,$path,$filenameR);
	}
	else
		$evaluate($stato,$path,$filename);

}
catch(Exception $e) {

	$errmsg = "Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage();
	echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE : '.$errmsg]);
	return;
}
echo json_encode(['esito' => 'OK', 'message' => "OPERAZIONE COMPLETATA"]);
?>

