<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once INC."/headerAjax.php";
include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";
include_once(CONTROLLERS."/DatiPignoramento.php");

$ctrl_DatiPignoramento = new DatiPignoramentoController($_REQUEST['Partita_ID']);
$a_return = $ctrl_DatiPignoramento->saveDocumentTypePignoramento($_REQUEST['DocumentTypeId']);

header("Location: dati_pignoramento.php?partita={$_REQUEST['Partita_ID']}&c={$c}&a={$a}&error={$a_return['error']}&msg={$a_return['msg']}");

?>
