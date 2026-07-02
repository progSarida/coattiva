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
$check = $cls_help->getVar('check');

if(is_null($partita) || is_null($check)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}

try {

        $query_up_par = " UPDATE partita_tributi SET  flag_elaboration = ".$check."  WHERE ID =  " . $partita;
        mysqli_query($cls_db->conn, $query_up_par);

    } catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());

        echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE']);
        return;
    }

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE']);
return;
