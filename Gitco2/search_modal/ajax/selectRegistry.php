<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

//Inizializzazione valori da chiamata Ajax

$city_name = $cls_help->getVar('city_name');                    // input

//Costruzione query

$query = "SELECT *, CONCAT(Toponimo,' ',Civico, COALESCE(Esponente,'')) AS Ins ";
$query.= "FROM ufficio_comune JOIN comuni_lista ON CC = Com_Codice_Catastale ";
$query.= "WHERE Comune LIKE '%".$city_name."%' AND Tipo = 'uff_anagrafico' ";
$query.= "ORDER BY Comune ";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    if($tableElem[$i]["Esponente"] != "" || $tableElem[$i]["Esponente"] != null ){
        $tableElem[$i]["Ins"] .= $tableElem[$i]["Esponente"];
    }
    else if($tableElem[$i]["Interno"] != "" || $tableElem[$i]["Interno"] != null ){
        $tableElem[$i]["Ins"] .= "/".$tableElem[$i]["Interno"];
    }
    $tableElem[$i]["action_row"] = 'initialId("registry",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"registry\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);