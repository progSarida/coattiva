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

$queryIngiunzioni = " SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC ";
$a_resIngiunzioni = $db->getResults($db->ExecuteQuery($queryIngiunzioni));

if (count($a_resIngiunzioni)>0 ) {
    echo json_encode(['esito' => 'OK', 'message' => $a_resIngiunzioni]);
    return;
} else {
    echo json_encode(['esito' => 'KO', 'message' => 'DATI ASSENTI']);
    return;
}

