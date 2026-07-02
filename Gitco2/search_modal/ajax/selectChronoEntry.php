<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$date = new cls_DateTimeI('IT');

$lock = $cls_help->getVar('lock');                                  // su visura_massiva.php gestisce il blocco dellla ricerca del secondo input
$admin = $_POST['admin'];                                           // Ente

$id_proto = $cls_help->getVar('proto');
$id_crono = $cls_help->getVar('chrono');
$anno_crono  = $cls_help->getVar('year');
/*
$query = "SELECT DISTINCT PA.ID, PA.Anno_Riferimento, PA.Comune_ID, AT.Info_Cartella, AT.ID AS ID_Atto ";
$query.= "FROM partita_tributi as PA , atto as AT ";
$query.= "WHERE AT.Partita_ID = PA.ID AND PA.CC = '".$admin."' AND PA.Is_Discharged=0 ORDER BY PA.ID";

var_dump($query);die;
*/


$query = "SELECT DISTINCT A.Anno_Cronologico AS Anno_Crono, A.ID_Cronologico AS ID_Crono, PT.ID, PT.Anno_Riferimento, PT.Comune_ID, A.Info_Cartella, A.ID AS ID_Atto, U.ID AS ID_Utente, U.Cognome, U.Nome, U.Ditta ";
$query.= "FROM partita_tributi as PT JOIN atto as A ON A.Partita_ID = PT.ID AND PT.CC = '".$admin."' ";
$query.= "LEFT JOIN utente AS U ON U.ID = PT.Utente_ID AND PT.CC = '".$admin."' ";
$query.= "WHERE PT.CC = '".$admin."' AND PT.Is_Discharged=0 ";

if($anno_crono!=null)
    $query.= "AND A.Anno_Cronologico = '".$anno_crono."' ";
if($id_crono!=null)
    $query.= "AND A.ID_Cronologico = '".$id_crono."' ";
if($id_proto!=null)
    $query.= "AND A.Protocollo = '".$id_proto."' ";
//Gestione blocco di visura_massiva.php
/*if($lock != 'N'){
    if($lock == 'D'){
        $query.= "AND Genere = 'D' ";
    } else {
        $query.= "AND (Genere = 'M' OR Genere = 'F') ";
    }
}*/

$query .= " ORDER BY PT.ID ";

//echo($lock."        ");echo($query);die;

$resultInfo = $cls_db->ExecuteQuery($query);

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["action_row"] = 'initialId("entry",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"entry\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);