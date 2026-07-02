<?php
include("../_path.php");
include(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_html.php";
include_once CLS . "/cls_help.php";

$cls_help = new cls_help();
$cls_db = new cls_db();

$q = $cls_help->getVar("term");
$searchType = $cls_help->getVar("searchType");

switch($searchType){
    case "comune":

        $query = "SELECT comuni_lista.Com_Nome AS value, comuni_lista.Com_Cap AS details, ";
        $query.= "province_lista.Pro_Nome AS category, comuni_lista.Com_Codice_Catastale AS parameter ";
        $query.= "FROM comuni_lista JOIN province_lista ON province_lista.Pro_Codice = comuni_lista.Com_Codice_Provincia ";
        $query.= "WHERE comuni_lista.Com_Nome LIKE \"".$q."%\" ";
        $query.= "ORDER BY comuni_lista.Com_Nome LIMIT 0,10 ";

        break;
}

if($query!=""){
    $result = $cls_db->SelectQuery($query);
    $a_json = array();

    while ($row = $cls_db->getArrayLine($result)) {
        array_push($a_json,array('label'=>$row['value']." ( ".$row['parameter']." )",'value'=>$row['value'], 'parameter'=>$row['parameter']));
    }

    echo json_encode($a_json);
}



 ?>