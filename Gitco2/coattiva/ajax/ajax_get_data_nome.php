<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS."/cls_LOG.php";
include_once CLS . "/cls_DateTimeInLine.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();
$cls_date = new cls_DateTimeI("IT",false);

$c = $cls_help->getVar("cc");
$id_atto = $cls_help->getVar("atto");

$query = "select U.Cognome, U.Nome,U.Ditta,U.Genere,U.ID as Utente_ID,PT.Comune_ID
from atto as A
join partita_tributi as PT on A.Partita_ID = PT.ID
join utente as U on U.ID = PT.Utente_ID
Where A.CC= '".$c."' 
and A.Modalita_Notifica in (11,12) 
#and A.Stato_Notifica <> 28
group by U.Cognome,U.Nome,U.Ditta,U.ID order by U.Cognome ";


$result = $cls_db->getResults($cls_db->ExecuteQuery($query));


echo json_encode($result);
die;