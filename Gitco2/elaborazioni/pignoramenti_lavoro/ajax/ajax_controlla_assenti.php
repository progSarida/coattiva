<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_InserimentoNelDB.php";

include_once ELAB_PIGNORAMENTI_LAVORO_CLS . "/cls_PignoramentoLavoro.php";

$cls_db = new cls_db();
$cls_help = new cls_help();


$elab_id = $cls_help->getvar('elab_id');
$c = $cls_help->getvar('c');
$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
$numero_assenti = 0;
try {
    
    $query = "select count(Flag_Terzo) as Assenti from v_assegna_terzo_lavoro where Elaboration_Id = $elab_id
    and Flag_Terzo = 'Assente'";
    $a_numero_assenti = $cls_db->getResults($cls_db->ExecuteQuery($query));
    $numero_assenti = $a_numero_assenti[0]['Assenti'];
}
catch(Exception $e)
{
    $cls_db->Rollback();
    echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI CANCELLAZIONE NON È ANDATA A BUON FINE']);
    return;
}
$cls_db->End_Transaction();
echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI CANCELLAZIONE È ANDATA A BUON FINE','numero_assenti' => $numero_assenti]);
return;
?>