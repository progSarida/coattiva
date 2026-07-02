<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$date = new cls_DateTimeI('IT');

    $lock = $cls_help->getVar('lock');                              // su visura_massiva.php gestisce il blocco dellla ricerca del secondo input
$admin = $_POST['admin'];                                           // Ente

$id_proto = $cls_help->getVar('proto');
$id_crono = $cls_help->getVar('chrono');
$anno_crono  = $cls_help->getVar('year');

//var_dump($id_proto." - ".$id_crono." - ".$anno_crono);die;

$query = "SELECT DISTINCT PT.ID, PT.Anno_Riferimento, PT.Comune_ID, PG.Tipo, PG.Tipo_Terzi, PG.ID AS ID_Pigno, PG.ID_Cronologico AS ID_Crono, PG.Anno_Cronologico AS Anno_Crono, ";
$query.= "CONCAT(PG.ID_Cronologico,'/',PG.Anno_Cronologico) AS Ins, U.ID AS ID_Utente, U.Cognome, U.Nome, U.Ditta ";
$query.= "FROM partita_tributi as PT JOIN pignoramento_generale as PG ON PG.Partita_ID = PT.ID AND PT.CC = '".$admin."' ";
$query.= "LEFT JOIN utente AS U ON U.ID = PT.Utente_ID AND PT.CC = '".$admin."' ";
$query.= "WHERE PT.Is_Discharged=0 ";
if($anno_crono!=null)
    $query.= "AND PG.Anno_Cronologico = '".$anno_crono."' ";
if($id_crono!=null)
    $query.= "AND PG.ID_Cronologico = '".$id_crono."' ";
if($id_proto!=null)
    $query.= "AND PG.Protocollo = '".$id_proto."' ";
$query.= "AND PT.CC = '".$admin."' ";
//Gestione blocco di visura_massiva.php
/*if($lock != 'N'){
    if($lock == 'D'){
        $query.= "AND Genere = 'D' ";
    } else {
        $query.= "AND (Genere = 'M' OR Genere = 'F') ";
    }
}*/

$query .= " ORDER BY Anno_Crono ASC, ID_Crono ASC ";

//echo($lock."        ");echo($query);die;

$resultInfo = $cls_db->ExecuteQuery($query);

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    if($result[$i]['Tipo']=='terzi')
        $tableElem[$i]['Tipo_pignoramento'] = strtoupper("Presso ".$result[$i]['Tipo_Terzi']);
    else if ($result[$i]['Tipo']=='veicolo')
        $tableElem[$i]['Tipo_pignoramento'] = "BENI MOBILI REGISTRATI";
    else
        $tableElem[$i]['Tipo_pignoramento'] = strtoupper($result[$i]['Tipo']);
        $tableElem[$i]["action_row"] = 'initialId("fore",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"fore\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);