<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

$atto_ID = $cls_help->getVar('atto_ID');
$Stato_Notifica = $cls_help->getVar('Stato_Notifica');

try{

    $query = "update atto set Stato_Notifica = ".$Stato_Notifica." where ID=".$atto_ID;
    $cls_db->ExecuteQuery($query);
}
catch(Exception $e) {

	$errmsg = "Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage();
	echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE : '.$errmsg]);
	?>

	<?php
	return;
}
echo json_encode(['esito' => 'OK', 'message' => "STATO NOTIFICA AGGIORNATO"]);
?>