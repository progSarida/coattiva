<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";


$db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$c = $cls_help->getVar('cod_cat');

if(is_null($c) ){
    echo json_encode(['esito' => 'KO', 'message' => 'Inserisci ente']);
	return;
}

$queryRuolo = " SELECT * from ruolo WHERE CC = '" . $c . "' ORDER by Data_Inserimento DESC ";
$a_resRuoli = $db->getResults($db->ExecuteQuery($queryRuolo));

if (count($a_resRuoli)>0 ) {
    echo json_encode(['esito' => 'OK', 'message' => $a_resRuoli]);
    return;
} else {
    echo json_encode(['esito' => 'KO', 'message' => 'DATI ASSENTI']);
    return;
}

