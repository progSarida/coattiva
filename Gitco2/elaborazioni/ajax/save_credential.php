<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_DateTime.php");

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();

$old_user = $cls_help->getVar("old_user");
$old_passw = $cls_help->getVar("old_passw");
$user = $cls_help->getVar("user");
$passw_1 = $cls_help->getVar("passw_1");
$passw_2 = $cls_help->getVar("passw_2");

$return = new stdClass();

$query = "SELECT UserId FROM ini_pec_processing WHERE UserName = '".$old_user."' AND Password = '".$old_passw."'";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

if(count($result) == 0){
    $return->status = "ERROR";
    $return->message = "Le vecchie credenziali sono errate";
    echo json_encode($return);
    die;
}
else{
    if($passw_1 != $passw_2){
        $return->status = "ERROR";
        $return->message = "Le password non coincidono";
        echo json_encode($return);
        die;
    }
    else{
        $dataScadenza = new cls_DateTime(date("Y-m-d"),"DB");
        $dataScadenza->AddDay(180);
        $query = "UPDATE ini_pec_processing SET UserName = '".$user."', Password = '".$passw_1."', INIPECPasswordExpiration = '".$dataScadenza->GetDateDB()."' WHERE UserName = '".$old_user."' AND Password = '".$old_passw."'";
        if(!$cls_db->ExecuteQuery($query)){
            $return->status = "ERROR";
            $return->message = "Impossibile aggiornare i dati";
            echo json_encode($return);
            die;
        }
        else {
            $return->status = "OK";
            $return->message = "Dati aggiornati correttamente";
            echo json_encode($return);
            die;
        }
    }
}