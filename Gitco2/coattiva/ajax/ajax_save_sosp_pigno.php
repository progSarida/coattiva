<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS."/cls_db.php";
include_once CLS."/cls_DateTimeInLine.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_storico.php";

$storico = new storico('storicoRuolo','3');
$cls_db = new cls_db();
$cls_help = new cls_help();
$date = new cls_DateTimeI("DB",false);

$flag_sosp = $cls_help->getVar('flag_sosp');
$motivo_sosp = $cls_help->getVar('motivo_sosp');
$note_sosp = $cls_help->getVar('note_sosp');
$data_sosp = $cls_help->getVar('data_sosp');
$atto_ID = $cls_help->getVar('Atto_ID');
$partita_ID = $cls_help->getVar('Partita_ID');
$sospensione = "";
$partitaArray["ID_Sospensione_Atto"] = null;
$partitaArray["Flag_Sospensione"] = null;

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

$mode = $cls_help->getVar('mode');

$query_doc = "SELECT * FROM pignoramento_generale WHERE ID = '".$atto_ID."'";
$doc_type = $cls_db->getArrayLine($cls_db->SelectQuery($query_doc))["DocumentTypeId"];
//var_dump($partita_ID);die;


if($mode == "salva"){
    $a_paramsSospensione = array(
        'table' => 'sospensione_atto',
        'fields'=> array(
            array(  'name' => 'Partita_ID',                     		'type' => 'int',    'value' => $partita_ID),
            array(  'name' => 'Tipo_Atto',                       		'type' => 'string', 'value' => $doc_type),
            array(  'name' => 'ID_Atto_Pigno',              			'type' => 'string', 'value' => $atto_ID),
            array(  'name' => 'Motivo_sospensione_ID',         			'type' => 'string', 'value' => $motivo_sosp),
            array(  'name' => 'Note_Sospensione',   					'type' => 'string', 'value' => $note_sosp),
            array(  'name' => 'Data_Sospensione',  						'type' => 'date',   'value' => date('Y-m-d')),
        ),
    );

    if(!$cls_db->DbSave($a_paramsSospensione) )
    {
        $cls_db->Rollback();
        $cls_db->End_Transaction();
        echo json_encode([
            "msg" => "Inserimento dati sospensione non riuscito!",
            "error" => 1
        ]);
        die;
    }
    else
        $sospensione = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM sospensione_atto WHERE Partita_ID = '".$partita_ID."'") );

    $partitaArray["ID_Sospensione_Atto"] = $sospensione["ID"];

    $partitaArray["Flag_Sospensione"] = $flag_sosp;

    $a_paramsPartita = array(
        'table' => 'partita_tributi',
        'fields'=> array(
            array(  'name' => 'Flag_Sospensione',              			'type' => 'string', 'value' => $partitaArray["Flag_Sospensione"]),
            array(  'name' => 'ID_Sospensione_Atto',              		'type' => 'string', 'value' => $partitaArray["ID_Sospensione_Atto"]),
        ),
        'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $partita_ID)
    );

    if(!$cls_db->DbSave($a_paramsPartita) )
    {
        $cls_db->Rollback();
            $cls_db->End_Transaction();
            echo json_encode([
                "msg" => "Aggiornamento dati partita non riuscito!",
                "error" => 1
            ]);
            die;
    }
    
    $cls_db->End_Transaction();
    echo json_encode([
        "msg" => "Sospensione partita avvenuta con successo",
        "error" => 0
    ]);
    die;
}
else if($mode == "aggiorna"){

    $a_paramsSospensione = array(
        'table' => 'sospensione_atto',
        'fields'=> array(
            array(  'name' => 'Partita_ID',                     		'type' => 'int',    'value' => $partita_ID),
            array(  'name' => 'Tipo_Atto',                       		'type' => 'string', 'value' => $doc_type),
            array(  'name' => 'ID_Atto_Pigno',              			'type' => 'string', 'value' => $atto_ID),
            array(  'name' => 'Motivo_sospensione_ID',         			'type' => 'string', 'value' => $motivo_sosp),
            array(  'name' => 'Note_Sospensione',   					'type' => 'string', 'value' => $note_sosp),
            array(  'name' => 'Data_Sospensione',  						'type' => 'date',   'value' => $data_sosp),
        ),
        'updateField' => array(  'name'=>'Partita_ID', 'type' => 'int', 'value'=> $partita_ID)
    );

    if(!$cls_db->DbSave($a_paramsSospensione) )
    {
        $cls_db->Rollback();
        $cls_db->End_Transaction();
        echo json_encode([
            "msg" => "Aggiornamento dati sospensione non riuscito!",
            "error" => 1
        ]);
        die;
    }
    
    $cls_db->End_Transaction();
    echo json_encode([
        "msg" => "Aggiornamento dati sospensione avvenuto con successo",
        "error" => 0
    ]);
    die;
}
else if($mode == "elimina"){

    if(!$cls_db->Delete("sospensione_atto","Partita_ID = '".$partita_ID."'"))
    {
        $cls_db->Rollback();
        $cls_db->End_Transaction();
        echo json_encode([
            "msg" => "Eliminazione dati sospensione non riuscita!",
            "error" => 1
        ]);
        die;
    }
    
    $a_paramsPartita = array(
        'table' => 'partita_tributi',
        'fields'=> array(
            array(  'name' => 'Flag_Sospensione',              			'type' => 'int', 'value' => null),
            array(  'name' => 'ID_Sospensione_Atto',              		'type' => 'int', 'value' => null),
        ),
        'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $partita_ID)
    );

    if(!$cls_db->DbSave($a_paramsPartita) )
    {
        $cls_db->Rollback();
        $cls_db->End_Transaction();
        echo json_encode([
            "msg" => "Aggiornamento dati partita non riuscito!",
            "error" => 1
        ]);
        die;
    }

    $cls_db->End_Transaction();

    echo json_encode([
        "msg" => "Sospensione eliminata correttamente",
        "error" => 0
    ]);
}