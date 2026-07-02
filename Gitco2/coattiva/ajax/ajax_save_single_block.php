<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS."/cls_db.php";
include_once CLS."/cls_DateTimeInLine.php";

$db = new cls_db();
$date = new cls_DateTimeI("DB",false);


$msg = "";
$error = 0;
$mode = $_POST["mode"];

if($mode == "salva"){
    $save = new stdClass();
    $save->archived = $_POST["archived"];
    $save->archived_parametri_notifica_id = $_POST["archived_parametri_notifica_id"];
    $save->text_motivation_archived = $_POST["text_motivation_archived"];
    $save->data_start_archived_act = $_POST["data_start_archived_act"] != null ? $date->GetDateDB($_POST["data_start_archived_act"],"IT") : date("Y-m-d");

    $check = $db->DbSave($db->GetObjectQuery("atto",(array)$save,null,array("ID" => $_POST["Atto_ID"])));

    if($check === false){
        $error = 1;
        $msg = "Errore! Impossibile salvare i dati";
    }
    else{
        $error = 0;
        $msg = "Dati salvati correttamente!"; 
    }
}
else if($mode == "aggiorna"){
    $save = new stdClass();
    $save->archived = $_POST["archived"];
    $save->archived_parametri_notifica_id = $_POST["archived_parametri_notifica_id"];
    $save->text_motivation_archived = $_POST["text_motivation_archived"];
    //$save->data_start_archived_act = $_POST["data_start_archived_act"] != null ? $date->GetDateDB($_POST["data_start_archived_act"],"IT") : null;

    $check = $db->DbSave($db->GetObjectQuery("atto",$save,null,array("ID" => $_POST["Atto_ID"])));

    if($check === false){
        $error = 1;
        $msg = "Errore! Impossibile aggiornare i dati";
    }
    else{
        $error = 0;
        $msg = "Dati aggiornati correttamente!"; 
    }
}
else if($mode == "elimina"){
    $save = new stdClass();
    $save->archived = null;
    $save->archived_parametri_notifica_id = null;
    $save->text_motivation_archived = null;
    $save->data_start_archived_act = null;

    $check = $db->DbSave($db->GetObjectQuery("atto",$save,null,array("ID" => $_POST["Atto_ID"])));

    if($check === false){
        $error = 1;
        $msg = "Errore! Impossibile eliminare i dati";
    }
    else{
        $error = 0;
        $msg = "Dati eliminati correttamente!"; 
    }
}

echo json_encode([
    "msg" => $msg,
    "error" => $error
]);