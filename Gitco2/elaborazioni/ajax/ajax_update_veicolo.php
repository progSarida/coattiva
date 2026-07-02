<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

$CC = $cls_help->getVar('CC');
$pignoId = $cls_help->getVar('Pignoramento_ID');
$pignoVeicoloId = $cls_help->getVar('Pignoramento_Veicolo_ID');
$veicolo_ID = $cls_help->getVar('Veicolo_ID');

if(is_null($pignoVeicoloId) || is_null($pignoId) || is_null($veicolo_ID) || is_null($CC)){
    echo json_encode(['esito' => 'KO', 'message' => 'DATI_INESISTENTI']);
	return;
}

try {
    $query = "SELECT * FROM veicoli WHERE ID=".$veicolo_ID;
    $a_veicolo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    if(is_null($a_veicolo)){
        echo json_encode(['esito' => 'KO', 'message' => 'Veicolo non trovato']);
        return;
    }

    $modelloVeicolo = trim($a_veicolo['Tipo']);
    if(!is_null($a_veicolo['Serie']))
        $modelloVeicolo.= " ".trim($a_veicolo['Serie']);
    $a_pigno_veicolo = array(
        "CC" => $CC,
        "Pignoramento_ID"=>$pignoId,
        "Veicolo_ID"=>$a_veicolo['ID'],
        "Tipo_Veicolo"=>strtolower($a_veicolo['SerieTarga']),
        "Telaio_Veicolo"=>$a_veicolo['Telaio'],
        "Targa_Veicolo"=>$a_veicolo['Targa'],
        "Marca_Veicolo"=>$a_veicolo['Fabbrica'],
        "Modello_Veicolo"=>$modelloVeicolo,
        "Data_Visura"=>$a_veicolo['Data_Visura'],
        "Anno_Immatricolazione"=>date("Y",strtotime($a_veicolo['DataPrimaImmatricolazione'])),
        "Fonte_Dati"=>"pra"
    );

    $cls_db->DbSave($cls_db->GetObjectQuery("pignoramento_veicolo",$a_pigno_veicolo,null,array("ID"=>$pignoVeicoloId)));
} 
catch (mysqli_sql_exception $e) {
    $cls_db->Rollback();
    echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE']);
    return;
}

echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE']);
return;