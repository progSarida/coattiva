<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include_once CLS . "/cls_db.php";
include_once CLS . "/cls_html.php";
include_once CLS . "/cls_help.php";

$cls_help = new cls_help();
$cls_db = new cls_db();

$searchType = $cls_help->getVar('searchType');

$a_return = array();
switch($searchType){
    case "partitaID":

        $partitaID = $cls_help->getVar('partitaID');
        $c = $cls_help->getVar('c');

        if((!is_numeric($partitaID)) || $partitaID==null)
            $a_return['response'] = false;
        else{
            $query = "SELECT ID, Anno_Riferimento, CC FROM partita_tributi WHERE Comune_ID =".$partitaID." and CC='".$c."' AND Is_Discharged=0";
            $obj_search = $cls_db->getObjectLine( $cls_db->SelectQuery($query) );

            if(isset($obj_search->ID)){
                if($obj_search->ID == null)
                    $a_return['response'] = false;
                else{
                    $a_return['response'] = true;
                    $a_return['c'] = $obj_search->CC;
                    $a_return['a'] = $obj_search->Anno_Riferimento;
                    $a_return['ID'] = $obj_search->ID;
                }
            }
            else{
                 $a_return['response'] = false;
            }
        }

        break;

    case "banche":

        $codiceCatastale = $cls_help->getVar('cc');

        $query = "SELECT DISTINCT sede.* FROM banca AS sede JOIN banca AS filiale ON filiale.ID_Collegamento = sede.ID ";
        $query.= "WHERE filiale.CC_Sede = '" . $codiceCatastale . "' AND filiale.Tipo_Banca = 'filiale' ";
        $query.= "AND ( ( filiale.ID_Collegamento > 0 AND filiale.Denominazione NOT LIKE 'POSTE ITALIANE%' ) ";
        $query.= "OR ( filiale.Denominazione LIKE 'POSTE ITALIANE%' ) ) ORDER BY sede.Denominazione ASC";

        $a_return = $cls_db->getResults( $cls_db->SelectQuery($query), "object" );

        break;
}

    echo json_encode($a_return);

 ?>
