<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";

include_once ELABORAZIONI . "/cls/cls_EliminaArt17.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$proc_id = $cls_help->getVar('proc_id');
$comune = $cls_help->getVar('comune');
$anno_riferimento = $cls_help->getVar('anno_riferimento');


if(is_null($proc_id)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}

try {

        $EliminaArt17 = new EliminaArt17($cls_db);
		$EliminaArt17
		->Set("proc_id",$proc_id)
		->Set("comune",$comune)
		->Set("anno_riferimento",$anno_riferimento)
		->ResettaPignoramenti()
		->SvuotaDirectory()
		->CancellaProcedura();


    } catch (Exception $e) {
        $cls_db->Rollback();
        echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE  NON È ANDATA A BUON FINE']);
        return;
    }

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE È ANDATA A BUON FINE']);
return;