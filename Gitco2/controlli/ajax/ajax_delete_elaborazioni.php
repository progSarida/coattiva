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

        $query_delete_el = "DELETE FROM `elaboration_lists` WHERE Elaboration_Id =".$el_id;
        mysqli_query($cls_db->conn, $query_delete_el);
		 
		 // DELETE ATTO 
         $query_delete_act = "DELETE FROM `atto` WHERE Elaboration_Id =".$el_id;
		 mysqli_query($cls_db->conn, $query_delete_act);

		 $query_delete_pvt = "DELETE FROM `banche_pvt` WHERE Elaboration_Id =".$el_id;
		 mysqli_query($cls_db->conn, $query_delete_pvt);

		 // DELETE PIGNO 
         $query_delete_pigno = "DELETE PG, PS, PV, PT, NA FROM `pignoramento_generale` PG "
		 ." LEFT JOIN pignoramento_veicolo PV ON PV.Pignoramento_ID=PG.ID "
		 ." LEFT JOIN pignoramento_presso_terzi PT ON PT.Pignoramento_ID=PG.ID "
		 ." LEFT JOIN pignoramento_spese PS ON PS.Pignoramento_ID=PG.ID "
		 ." LEFT JOIN notifica_atto NA ON NA.Atto_Notificato_ID=PG.ID "
		 ." WHERE PG.Elaboration_Id =".$el_id;
		 mysqli_query($cls_db->conn, $query_delete_pigno);


		// UPDATE PARTITE_TRIBUTI ROWS

		$a_dbParams_trib = array(
			'table' => 'partita_tributi',
			'updateField' => array(
				array('name' => 'Elaboration_Id',       'type' => 'int', 'value' => $el_id),
			  
			),
			
			'fields'=> array(
								array(  'name' => 'Elaboration_Id',  	'type' => 'int', 'value' => NULL),
								array(  'name' => 'Position_Status_Id',	'type' => 'int', 'value' => NULL),
								array(  'name' => 'flag_elaboration',   'type' => 'int', 'value' => NULL),
							 )
		);

		$cls_db->DbSave($a_dbParams_trib);

    } catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());

        echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE  NON È ANDATA A BUON FINE']);
        return;
    }

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE È ANDATA A BUON FINE']);
return;