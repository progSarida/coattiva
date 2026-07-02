<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_excel.php";
include (ELABORAZIONI."/cls/cls_EstrazionePosizioni.php");


set_time_limit(300);

$db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$last_el_id = $cls_help->getVar('last_el_id');

$jsonFilters = json_decode($cls_help->getVar('filters'), JSON_OBJECT_AS_ARRAY);
$a_filterField = array(
    "Tributo" => "Tipo_Riscossione",
    "Anomalia" => "Anomalia_ATTO",
    "Stato" => "PS_NOME"
);

$filterMsg = "";

$query_elenco_part = " SELECT vcp.* 
                        FROM `v_check_partite_estrazione` AS vcp 
                        WHERE Elaboration_Estrazione_Id = $last_el_id 
                        GROUP BY Utente_ID ORDER BY Cognome_Ditta" ;


$results = $db->ExecuteQuery($query_elenco_part);
$utenti = $db->getResults($results);


if (isset($utenti)) {

    $a_comune_result = $db->ExecuteQuery("SELECT g.Denominazione
    FROM enti_gestiti g join elaborations e on e.CC = g.CC
    where e.Id = $last_el_id");
    $a_comuni = $db->getResults($a_comune_result);
    
    $comune = $a_comuni[0]["Denominazione"];

    $filename = "Lista utenti elaborabili_".$comune."_" . $last_el_id . ".xlsx";
    
    $lista_utenti = "(";
    $lista_utente_processati=[];
    foreach ($utenti as $utente)
    {
        if (!in_array($utente["Utente_ID"],$lista_utente_processati))
        {
            $lista_utenti.=$utente["Utente_ID"].",";
            $lista_utente_processati[] = $utente["Utente_ID"];

        }
        
    }
    $lista_utenti.= "0)";

    $query = "SELECT *
    from v_stragiudiziali_banche_prendi_excel
    where Utente_ID IN ".$lista_utenti;

    $result_stra = $db->ExecuteQuery($query);
    $a_atti = $db->getResults($result_stra);
    

    $estrazionePosizioni = new EstrazionePosizioni($cls_help);
    $estrazionePosizioni->filename = $filename;
    $estrazionePosizioni->cls_db = $db;
    $estrazionePosizioni->Intestazione();
    $i = 0;
    
    foreach ($utenti as $utente) {
        $estrazionePosizioni->SalvaUtente($utente,$i,$a_atti);
        $i++;
    }
    
    $estrazionePosizioni->rowCount = $i;
    $estrazionePosizioni->Istruzioni();
    $estrazionePosizioni->Stile();
    
    $objWriter = PHPExcel_IOFactory::createWriter($estrazionePosizioni->mod_st, 'Excel2007');

    ob_start();
    $objWriter->save("php://output");
    $xlsData = ob_get_contents();

    ob_end_clean();

    $obj = array(
        'esito' => 'OK',
        'message' => 'File Excel creato'.$filterMsg,
        'nome_file' => $filename,
        "data" => null
    );
    $obj["data"] = "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($xlsData);

    $json = json_encode($obj);

    echo $json;
    return;
} else {
    echo json_encode(['esito' => 'KO', 'message' => 'DATI ASSENTI']);
    return;
}
