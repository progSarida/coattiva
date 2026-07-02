<?php 
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS."/cls_db.php"; 

$db = new cls_db();

if($_POST["save_type"] == "aggiorna"){

    $arrayImport = array();
    foreach($_POST["split_import"] as $key => $value){
        $arrayImport[] = str_replace(".",",",$value);
    }

    $arrayDate = array();
    foreach($_POST["split_date"] as $key => $value){
        $date = explode("-",$value);
        $arrayDate[] = $date[2]."/".$date[1]."/".$date[0];
    }

    $update = new stdClass();
    $update->Rate_Previste = $_POST["number_sel"];
    $update->Importi_Rate = implode("*",$arrayImport) != "" ? implode("*",$arrayImport) : null;
    $update->Scadenze_Rate = implode("*",$arrayDate) != "" ? implode("*",$arrayDate) : null;
    $update->Data_Richiesta_Rate = $_POST["request_date"] != "" ? $_POST["request_date"] : null;
    $update->Tipo_Totale_Rate = $_POST["total_type"];
    $update->Nominativo_Gestore_Rateizzazione = $_POST["nominative"] != "" ? $_POST["nominative"] : null;
    $update->Posizione_Gestore_Rateizzazione = $_POST["position"] != "" ? $_POST["position"] : null;
    $update->Esito_Richiesta_Rateizzazione = $_POST["request_outcome"] == "1" ? "accolta" : "respinta";
    $update->Motivazione_Respinta_Rateizzazione = $_POST["motivation"] != "" ? $_POST["motivation"] : null;
    $update->Tipo_Scadenza_Rate_ID = $_POST["installment_type"];
    $update->Operatore_Rateizzazione = $_POST["operator"] != "" ? $_POST["operator"] : null;

    $check = $db->DbSave($db->GetObjectQuery("pignoramento_generale",$update,null,array("ID" => $_POST["Pigno_ID"])));

    if(!$check){
        echo json_encode([
            "msg" => "Errore! Impossibile aggiornare i dati!",
            "error" => 1,
            "action" => $_POST["save_type"]
        ]);
        die;
    }
    else {
        echo json_encode([
            "msg" => "Dati aggiornati correttamente!",
            "error" => 0,
            "action" => $_POST["save_type"]
        ]);
    }
}
else if($_POST["save_type"] == "elimina"){
    $update = new stdClass();
    $update->Rate_Previste = null;
    $update->Importi_Rate = null;
    $update->Scadenze_Rate = null;
    $update->Data_Richiesta_Rate = null;
    $update->Tipo_Totale_Rate = null;
    $update->Nominativo_Gestore_Rateizzazione = null;
    $update->Posizione_Gestore_Rateizzazione = null;
    $update->Esito_Richiesta_Rateizzazione = null;
    $update->Motivazione_Respinta_Rateizzazione = null;
    $update->Tipo_Scadenza_Rate_ID = null;
    $update->Operatore_Rateizzazione = null;

    $check = $db->DbSave($db->GetObjectQuery("pignoramento_generale",$update,null,array("ID" => $_POST["Pigno_ID"])));

    if(!$check){
        echo json_encode([
            "msg" => "Errore! Impossibile eliminare i dati!",
            "error" => 1,
            "action" => $_POST["save_type"]
        ]);
        die;
    }
    else {
        echo json_encode([
            "msg" => "Dati eliminati correttamente!",
            "error" => 0,
            "action" => $_POST["save_type"]
        ]);
    }
}
else{
    echo json_encode([
        "msg" => "Azione, (aggiornamento/cancellazione), sconosciuta!",
        "error" => 1
    ]);
}

