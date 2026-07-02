<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS."/cls_LOG.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB",false);

$tipo_totale = $cls_help->getVar("tipo_totale");
$data_richiesta = $cls_help->getVar("data_richiesta");
$num_rate = $cls_help->getVar("num_rate");
$note = $cls_help->getVar("note");
$atto = $cls_help->getVar("atto");
$c = $cls_help->getVar("cc");

$query = "SELECT tipo_scadenza_rate, Scadenze_Rate FROM atto WHERE ID = ".$atto;
$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

$all_rate = explode("*",$result["Scadenze_Rate"]);

switch($tipo_totale){
    case 1: $tipo_tot_value = "Tot 1"; break;
    case 2: $tipo_tot_value = "Tot 2"; break;
    case 3: $tipo_tot_value = "Personalizzato"; break;
    default: $tipo_tot_value = ""; break;
}

switch($result["tipo_scadenza_rate"]){
    case 1: $tipo_scad_rate = "Mensile"; break;
    case 2: $tipo_scad_rate = "Bimestrale"; break;
    case 3: $tipo_scad_rate = "Semestrale"; break;
    default: $tipo_scad_rate = ""; break;
}

$save = array();
$save["CC"] = $c;
$save["atto_id"] = $atto;
$save["data_richiesta"] = $data_richiesta;
$save["numero_rate"] = $num_rate;
$save["note"] = $note;
$save["tipo_totale"] = $tipo_tot_value;
$save["data_prima_rata"] = $cls_date->GetDateDB($all_rate[0],"IT");
$save["data_ultima_rata"] = $cls_date->GetDateDB($all_rate[count($all_rate)-1],"IT");
$save["tipo_scadenza_rate"] = $tipo_scad_rate;
$save["data_eliminazione"] = date("Y-m-d");

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if(!$cls_db->DbSave( $cls_utils->GetObjectQuery($save,"storico_rateizzazioni")))
{
    $cls_db->Rollback();
    $error = 1;
    $msg = "Salvataggio storico non riuscito";
    $cls_db->End_Transaction();

    echo json_encode(array("error" => $error,"msg" => $msg));
    die;
}
else{
    $elimina = array();

    $elimina["Importi_Rate"] = "";
    $elimina["Scadenze_Rate"] = "";
    $elimina["Rate_Previste"] = 0;
    $elimina["Totale_Rateizzato"] = null;
    $elimina["Tipo_Totale_Rate"] = null;
    $elimina["Data_Richiesta_Rate"] = null;
    $elimina["ID_Richiesta_Rateizzazione"] = null;
    $elimina["ID_Esito_Rateizzazione"] = null;
    $elimina["ID_Bollettini_Rateizzazione"] = null;
    $elimina["Operatore_Rateizzazione"] = "";
    $elimina["Nominativo_Gestore_Rateizzazione"] = "";
    $elimina["Posizione_Gestore_Rateizzazione"] = "";
    $elimina["Operatore_Rateizzazione"] = "";
    $elimina["tipo_scadenza_rate"] = "";
    $elimina["Motivazione_Respinta_Rateizzazione"] = "";


    if(!$cls_db->DbSave( $cls_utils->GetObjectQuery($elimina,"atto",array("ID" => $atto))))
    {
        $cls_db->Rollback();
        $error = 1;
        $msg = "Aggiornamento tabella atto non riuscito";
        $cls_db->End_Transaction();
        echo json_encode(array("error" => $error,"msg" => $msg));
        die;
    }
    else
    {
        $error = 0;
        $msg = "rateizzazione eliminata correttamente";
        $cls_db->End_Transaction();
        echo json_encode(array("error" => $error,"msg" => $msg));
        die;
    }
}


