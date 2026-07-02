<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_InserimentoNelDB.php";

include_once ELAB_PIGNORAMENTI_BANCA_CLS . "/cls_PignoramentoBanche.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

$utente_id = $cls_help->getvar('utente_id');
$terzo_id = $cls_help->getvar('terzo_id');
$elab_id = $cls_help->getvar('elab_id');
$c = $cls_help->getvar('c');
$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
try {
    $AssegnazioneTerzi = new AssegnazioneBanchePvt($cls_db);
    $AssegnazioneTerzi->Utente_ID = $utente_id;
    $AssegnazioneTerzi->Terzo_ID = $terzo_id;
    $AssegnazioneTerzi->CC = $c;
    $AssegnazioneTerzi->Elaboration_Id = $elab_id;

    $id = $AssegnazioneTerzi->Exist();
    $AssegnazioneTerzi->Cancella($id);
    
}
catch(Exception $e)
{
    $cls_db->Rollback();
    echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI CANCELLAZIONE NON È ANDATA A BUON FINE']);
    return;
}
$cls_db->End_Transaction();
echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI CANCELLAZIONE È ANDATA A BUON FINE']);
return;
?>