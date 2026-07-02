<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

$type_db ='';
$type_table = '';
$type = $cls_help->getVar('type');
$city_name = $cls_help->getVar('city_name');
$c = $cls_help->getVar('c');


// Selezione tipo di ufficio giudiziario
switch($type){
    case 'judge':
        $type_db ='giudice';
        $type_table = "Giudice di Pace";
        break;
    case 'court':
        $type_db ='tribunale';
        $type_table = "Tribunale";
        break;
    case 'tax_prov':
        $type_db ='comm_trib_prov';
        $type_table = "Commissione Tributaria Provinciale";
        break;
    case 'tax_reg':
        $type_db ='comm_trib_reg';
        $type_table = "Commissione Tributaria Regionale";
        break;
    case 'appeal':
        $type_db ='appello';
        $type_table = "Corte d'Appello";
        break;
    case 'scoi':
        $type_db ='cassazione';
        $type_table = "Corte di Cassazione";
        break;
    default:
        $type_table = "??????";
}

$query = "SELECT *, CONCAT(Toponimo,' ') AS Ins ";
$query.= "FROM ufficio_giudiziario ";
$query.= "WHERE Tipo = '".$type_db."' AND Comune LIKE '%".$city_name."%' AND CC='".$c."'";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    if($tableElem[$i]["Civico"] != "" || $tableElem[$i]["Civico"] != null ){
        if ($tableElem[$i]["Civico"] > 0)
            $tableElem[$i]["Ins"] .= $tableElem[$i]["Civico"];
    }
    if($tableElem[$i]["Esponente"] != "" || $tableElem[$i]["Esponente"] != null ){
        if ($tableElem[$i]["Esponente"] > 0)
            $tableElem[$i]["Ins"] .= $tableElem[$i]["Esponente"];
    }
    else if($tableElem[$i]["Interno"] != "" || $tableElem[$i]["Interno"] != null ){
        if ($tableElem[$i]["Interno"] > 0)
            $tableElem[$i]["Ins"] .= "/".$tableElem[$i]["Interno"];
    }
    $tableElem[$i]["action_row"] = 'initialId("authority",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"authority\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);