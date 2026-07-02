<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once ELAB_PIGNORAMENTI ."/cls_PignoramentoNotificaAtto.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_PignoramentoNotificaAtto = new cls_PignoramentoNotificaAtto($cls_db);

$notificaAtto = $cls_help->getVar('notificaAtto');
$field = $cls_help->getVar('field');
$value = $cls_help->getVar('value');
$cc = $cls_help->getVar('cc');
$pignoramento_id = $cls_help->getVar('pignoramento_id');

$query_par =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $cc . "' AND Anno=" . date('Y');
$params_arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par));

$SelezionaTipoPigno = function() use($cls_db,$pignoramento_id)
{ 
    $key = "";
    $q =  "   SELECT DocumentTypeId FROM pignoramento_generale WHERE ID = " . $pignoramento_id;
    $arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($q));
    $docId = $arr["DocumentTypeId"];
    switch($docId)
    {
        case 22 : $key = "Cautelari";
                  break;
        default : $key = "Pignoramento";
    }
    return $key;

};
if( is_null($pignoramento_id) || is_null($notificaAtto) || is_null($field) || is_null($value) || empty($params_arr)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}

if($field!="Tipo_Ufficiale")
    $setfield = $field.' = '.$value;
else
    $setfield = $field.' = "'.$value.'"';
    

try {
        $query_up_atto = "UPDATE notifica_atto SET ". $setfield;
        $key = $SelezionaTipoPigno();
        if($field=="PrintTypeId"){
            switch ($value) {
                case 1:
                case 2:
                    $spe_not = $params_arr['Spese_Notifica_'.$key];
                    break;
                /*case 2:
                    $spe_not = $params_arr['Spese_Raccomandata'];
                    //$spe_not = $params_arr['Spese_Notifica_'.$key];
                    break;*/
                case 3:
                    $spe_not = $params_arr['Spese_Postali'];
                    break;
                case 4:
                    $spe_not = $params_arr['Spese_Pec'];
                    break;
                case 6:
                    $spe_not = $params_arr['A_Mani_'.$key];
                    break;    
            }
            $query_up_atto.= ", Spese_Notifica=".$spe_not." ";

            //var_dump($spe_not);die;
            
            $cls_PignoramentoNotificaAtto->AggiornaPignoramentoGenerale($spe_not,$pignoramento_id);
        }
        $query_up_atto.=" WHERE ID =  " . $notificaAtto;
        $cls_db->ExecuteQuery($query_up_atto);
        

    } catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE']);
        return;
    }

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE']);
return;