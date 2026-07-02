<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

$elab_id = $cls_help->getVar('elaboration_id');
$partita = $cls_help->getVar('partita');
$field = $cls_help->getVar('field');
$value = $cls_help->getVar('value');
$cc = $cls_help->getVar('cc');

$query_par =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $cc . "' AND Anno=" . date('Y');
$params_arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par));

if(is_null($elab_id) || is_null($partita) || is_null($field) || is_null($value) || empty($params_arr)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}

if($field!="Tipo_Ufficiale")
    $setfield = $field.' = '.$value;
else
    $setfield = $field.' = "'.$value.'"';

try {
        

        $query_up_atto = "UPDATE atto SET ". $setfield ." ";
        if($field=="PrintTypeId"){
            switch ($value) {
                case 1:
                    $spe_not = $params_arr['Spese_Notifica'];
                    break;
                case 2:
                    $spe_not = $params_arr['Spese_Raccomandata'];
                    break;
                case 3:
                    $spe_not = $params_arr['Spese_Postali'];
                    break;
                case 4:
                    $spe_not = $params_arr['Spese_Pec'];
                    break;
                case 6:
                    $spe_not = $params_arr['A_Mani'];
                    break;    
            }
            $query_up_atto.= ", Totale_Dovuto=Totale_Dovuto-Spese_Notifica+".$spe_not." , Spese_Notifica=".$spe_not." ";
        }
        $query_up_atto.= "WHERE Partita_ID =  " . $partita." AND Elaboration_Id = ". $elab_id;
        $cls_db->ExecuteQuery($query_up_atto);

    } catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE']);
        return;
    }

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE']);
return;