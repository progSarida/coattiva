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

$el_id = $cls_help->getVar('el');


if(is_null($el_id)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}

try {

        // DELETE ELABORATIONS 
		$query_delete_el = "DELETE FROM `elaborations` WHERE Id =".$el_id;
		 mysqli_query($cls_db->conn, $query_delete_el);
		 // DELETE ESTRAZIONE 
         $query_delete_estrazione = "DELETE FROM `estrazione_pvt` WHERE Elaboration_Id =".$el_id;
		 mysqli_query($cls_db->conn, $query_delete_estrazione);

		
         

    } catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());

        echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE  NON È ANDATA A BUON FINE']);
        return;
    }

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE È ANDATA A BUON FINE']);
return;