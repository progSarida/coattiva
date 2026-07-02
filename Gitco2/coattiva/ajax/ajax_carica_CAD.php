<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_InserimentoManualeCAD.php";
include_once CLS . "/cls_paramUtils.php";


$cls_db = new cls_db();
$cls_help = new cls_help();

$p = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');


$atto_ID = $cls_help->getVar('atto_corrente');
$tipo_atto = $cls_help->getVar('tipo_atto');
$flag = $cls_help->getVar('flag');
$comune_scelto = $cls_help->getVar('comune_scelto');

$blnFilePresenteFronte = $flag ? $cls_help->getVar('filenameF')!=null : $cls_help->getVar('filename')!=null;
$blnFilePresenteRetro =  $cls_help->getVar('filenameR')!=null;
$filenameCADFronte = $flag ?$cls_help->getVar('filenameF') : $cls_help->getVar('filename');
$filenameCADRetro = $cls_help->getVar('filenameR');
$percorsoFisicoCADRetro =$cls_help->getVar('pathcompletoR');
$percorsoFisicoCADFronte =$flag ? $cls_help->getVar('pathcompletoF') : $cls_help->getVar('pathcompleto');

if($blnFilePresenteFronte)
try{
	$fai_nome = function($file_name,$atto_id,$suffix){
		$ext = pathinfo($file_name , PATHINFO_EXTENSION);
		//$file_name = pathinfo($file_name , PATHINFO_BASENAME);
		if($ext=='pdf')
		return "ATTOID_".$atto_id.".".$ext;
		else
		return "ATTOID_".$atto_id.$suffix.".".$ext;
	};
	$Immagine_Fronte = $fai_nome($filenameCADFronte,$atto_ID,"_F");
	$Immagine_Retro = null;
	if ($blnFilePresenteRetro)
		$Immagine_Retro = $fai_nome($filenameCADRetro,$atto_ID,"_R");
	$Data_Importazione = date("Y-m-d");
	$Operatore = $_SESSION['username'];
	

	switch ($tipo_atto) {
		case "Sollecito pre ingiunzione":
			$tipo_atto = "SOLL_PRE";
			break;
		case "Ingiunzione":
			$tipo_atto = "INGIUNZIONE";
			break;
		case "Sollecito di pagamento":
			$tipo_atto = "SOLLECITOINGIUNZIONE";
			break;
		case "Avviso di intimazione ad adempiere":
			$tipo_atto = "AVVISOINTIMAZIONE";
			break;
		case "Avviso di messa in mora":
			$tipo_atto = "AV_MORA";
			break;
		default :$tipo_atto = null;
	}

	$inserimento = new InserimentoManualeCAD($cls_db);
	$valoreRetro = $blnFilePresenteRetro ? $percorsoFisicoCADRetro : null;
	$path = CAD."/";


	$inserimento
	->Set("Atto_Id",$atto_ID)
	->Set("CAD_Fronte",$Immagine_Fronte)
	->Set("CAD_Retro",$Immagine_Retro)
	->Set("Data_Importazione",$Data_Importazione)
	->Set("Operatore",$Operatore)
	->Set("Tipo_Atto",$tipo_atto)
	->Set("cc",$comune_scelto)
	->PreparaRiga()
	->InserimentoDati()
	->Set("path_fronte",$percorsoFisicoCADFronte)
	->Set("path_retro",$valoreRetro)
	->Set("cls_param",new cls_paramNotifiche())
	->Set("cls_help",$cls_help)
	->SalvaImmagini($path)
	->AggiornaStatoNotificaAtto();


}
catch(Exception $e) {

	$errmsg = "Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage();
	echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE : '.$errmsg]);
	?>

	<?php
	return;
}
echo json_encode(['esito' => 'OK', 'message' => "OPERAZIONE COMPLETATA"]);
?>

