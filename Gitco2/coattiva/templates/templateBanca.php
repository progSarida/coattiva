<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(CONTROLLERS."/DatiPignoramento.php");

$ctrl_DatiPignoramento = new DatiPignoramentoController($_REQUEST['Partita_ID']);
$template = $ctrl_DatiPignoramento->showBanca($_REQUEST['rowKey']);

echo json_encode(array('status'=>true, 'data'=>$template));
