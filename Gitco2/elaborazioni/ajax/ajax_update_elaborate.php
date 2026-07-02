<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$partita = $cls_help->getVar('partita');
$identificatore = $cls_help->getVar('identificatore');
$valore = $cls_help->getVar('valore');

if(is_null($partita) || is_null($identificatore) || is_null($valore)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}

$setfield = "";

switch ($identificatore) {
    case 'printer_':
        $setfield = " PrinterId = ".$valore;
        break;
    case 'tipo_spedizione_':
            $setfield = " PrintTypeId = ".$valore;
    break;
    case 'tipo_notifica_':
        $setfield = " Tipo_Ufficiale= '".$valore."'";        
        
    break;
}
try {

        $query_up_atto = " UPDATE atto SET ". $setfield ." WHERE Partita_ID =  " . $partita;       
        mysqli_query($cls_db->conn, $query_up_atto);

    } catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());

        echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE']);
        return;
    }

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE']);
return;