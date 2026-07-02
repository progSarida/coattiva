<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

$denominazione_ente_prev = $cls_help->getVar('denominazione_ente_prev');
$comune_ente_prev = $cls_help->getVar('comune_ente_prev');
$cap_ente_prev = $cls_help->getVar('cap_ente_prev');
$admin = $cls_help->getVar('admin');
$c = $cls_help->getVar('c');

$tipo_ente = $cls_help->getVar('tipo_ente');                                            // inutile

if($denominazione_ente_prev == null)
    $denominazione_ente_prev="";

$query = "SELECT * FROM enti_esterni WHERE  CC = '".$c."' AND Denominazione LIKE '%".$denominazione_ente_prev."%' ";
if($tipo_ente != null)                                                                  //
    $query.= " AND Tipo = '".$tipo_ente."'";                                            // inutile
if($comune_ente_prev != null)
    $query.= " AND Comune LIKE \"%".$comune_ente_prev."%\"";
if($cap_ente_prev != null)
    $query.= " AND Cap LIKE \"%".$cap_ente_prev."%\"";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    if($result[$i]['Tipo'] == 'previdenza')
        $tableElem[$i]['Tipo'] = 'Previdenza';
    $tableElem[$i]["action_row"] = 'initialId("welfare",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"welfare\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);