<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

$denominazione = $cls_help->getVar('denominazione');
$comune_banca = $cls_help->getVar('comune_banca');
$cap_banca = $cls_help->getVar('cap_banca');
$PI_CF_banca = $cls_help->getVar('PI_CF_banca');
$admin = $cls_help->getVar('admin');
$c = $cls_help->getVar('c');
$disabled = $cls_help->getVar('disabled');
$tipo_banca = 'filiale';



$query = "SELECT * FROM banca WHERE CC = '".$c."' AND Denominazione LIKE '%".$denominazione."%' ";
if($disabled == 0)
    $query.= " AND banca.disabled != 1 ";
if($tipo_banca != null)
    $query.= " AND Tipo_Banca = '".$tipo_banca."'";
if($comune_banca != null)
    $query.= " AND Comune LIKE \"%".$comune_banca."%\"";
if($cap_banca != null)
    $query.= " AND Cap LIKE \"%".$cap_banca."%\"";
if($PI_CF_banca != null)
    $query.= " AND ( Codice_Fiscale = '".$PI_CF_banca."' OR Partita_Iva = '".$PI_CF_banca."') ";

//var_dump($query);die;

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]['Tipo_Banca'] = 'Filiale';
    $tableElem[$i]["action_row"] = 'initialId("bank_branch",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"bank_branch\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);