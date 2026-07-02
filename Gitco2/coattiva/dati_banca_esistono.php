<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(CONTROLLERS."/DatiPignoramento.php");

$ctrl_DatiPignoramento = new DatiPignoramentoController($_REQUEST['Partita_ID']);
$a_return = $ctrl_DatiPignoramento->existingRows($_REQUEST['Utente_ID']);

$result =  $a_return ? "true" : "false";
echo json_encode(['esito' => 'OK', 'esistono' => $result]);
return;

?>
