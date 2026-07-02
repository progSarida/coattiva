<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_InserimentoNelDB.php";

include_once ELAB_PIGNORAMENTI_LAVORO_CLS . "/cls_PignoramentoLavoro.php";
$a_post = array();
foreach($_POST as $key => $value) {
    //echo "POST parameter '$key' has '$value' <br>";
    
        $a_keys = explode ("_",$key);
        if(is_numeric($a_keys[count($a_keys)-1]))
        {
            $a_post[$key] = $value;
        }

}

$utente_id = $cls_help->getVar('utente_id');
$elab_id = $cls_help->getVar('elab_id');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$conta_terzi = $cls_help->getVar('conta_terzi');

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
try
{
    $AssegnazioneTerzi = new AssegnazioneTerzoPvt($cls_db);
    for($i=0;$i<$conta_terzi;$i++)
    {   
            if (!isset($a_post["pignorato_id_lavoro_".$i])) continue;
            $AssegnazioneTerzi->Utente_ID = $utente_id;
            $AssegnazioneTerzi->CC = $c;
            $AssegnazioneTerzi->Terzo_ID = $a_post["pignorato_id_lavoro_".$i];
            $AssegnazioneTerzi->Azienda = $a_post["azienda_lavoro_".$i];
            $AssegnazioneTerzi->Fonte_Dati =  $a_post["fonte_lavoro_".$i];
            $AssegnazioneTerzi->Tipo_Contratto_Lavoro =$a_post["tipo_contratto_".$i];
            $AssegnazioneTerzi->Note = $a_post["note_lavoro_".$i];
            $AssegnazioneTerzi->Data_Costituzione_Ditta_Lavoro = $a_post["data_costituzione_".$i];
            $AssegnazioneTerzi->Data_Ditta_Operativa_Lavoro = $a_post["data_operativa_".$i];
            $AssegnazioneTerzi->Data_Dipendenze_Lavoro =  $a_post["data_dipendenze_".$i];
            $AssegnazioneTerzi->Elaboration_Id = $elab_id;
            $id = $AssegnazioneTerzi->Exist();
            if($id==0) 
                $AssegnazioneTerzi->Insert();
            else
                $AssegnazioneTerzi->Update($id);
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
    $cls_db->Rollback();
    die;
}

$cls_db->End_Transaction();
?>

<script>
    location.href = "assegnazione_terzi.php?utente_id=<?=$utente_id ?>&c=<?=$c?>&a=<?=$a?>&el=<?=$elab_id?>&salvato=ok";
</script>