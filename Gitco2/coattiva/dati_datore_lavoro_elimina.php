<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";
include_once(CONTROLLERS."/DatiPignoramento.php");

$ctrl_DatiPignoramento = new DatiPignoramentoController($_REQUEST['Partita_ID']);
$ctrl_DatiPignoramento->deleteDatoreLavoro($_REQUEST['TerzoPvt_ID']);


?>
