<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_storico.php";

$storico = new storico('storicoParametri','8');
$cls_help = new cls_help();
$cls_db = new cls_db();

$a_fields = array(
    "Tipo", "CC", "CC_Ufficio", "Comune", "Provincia", "Sezione", "Toponimo", "Dettagli", "Cap",
    "Civico", "Esponente", "Interno", "Telefono", "Fax", "Mail", "PEC", "Sito", "Denominazione"
);

$a_values = array(
    $_POST['Authority_Type'], "*****", $_POST['CC_Ufficio'], $_POST["Comune"], $_POST["Provincia"], $_POST["Sezione"], $_POST["Toponimo"], $_POST["Dettagli"], $_POST["Cap"],
    $_POST["Civico"], $_POST["Esponente"], $_POST["Interno"], $_POST["Telefono"], $_POST["Fax"], $_POST["Mail"], $_POST["PEC"], $_POST["Sito"], ""
);

$bindTypes = "sssssssssisissssss";
$error = 0;
$msg = "";
$action = "";
$storico_msg = "";
$tipo = $cls_help->getVar("Authority_Type");


$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if($_POST['Authority_ID']>0){
    $query = "UPDATE ufficio_giudiziario SET ";
    for($i=0;$i<count($a_fields);$i++){
        if($i>0)
            $query.= ", ";
        $query.= $a_fields[$i]."=?";
    }

    $query.= " WHERE ID=".$_POST['Authority_ID'];
    $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);

    $msg = "aggiornamento";
    $action = "U";
    $storico_msg = "Modificato ";


}else{
    $insertFields = "";
    for($i=0;$i<count($a_fields);$i++){
        if($i>0)
            $insertFields.= ", ";
        $insertFields.= $a_fields[$i];
    }
    $insertValues = "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";

    $query = "INSERT INTO ufficio_giudiziario (".$insertFields.") ";
    $query.= "VALUES (".$insertValues.")";

    $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);

    $msg = "inserimento";
    $action = "I";
    $storico_msg = "Inserito ";
}

switch ($tipo){
    case 'giudice':
        $storico_msg_ = "'Giudice di pace' ";
        break;
    case 'tribunale':
        $storico_msg_ = "'Tribunale' ";
        break;
    case 'comm_trib_prov':
        $storico_msg_ = "'Commissione Tributaria Provinciale' ";
        break;
    case 'comm_trib_reg':
        $storico_msg_ = "'Commissione Tributaria Regionale' ";
        break;
    case 'appello':
        $storico_msg_ = "'Corte d'Appello' ";
        break;
    case 'cassazione':
        $storico_msg_ = "'Corte di Cassazione' ";
        break;
}

if($checkBind===false) {
    echo "ERROR ".mysqli_error($cls_db->conn);
    $cls_db->Rollback();
    $msg = "Errore ".$msg." non riuscito";
    $error = 1;
}
else
{
    $cls_db->End_Transaction();
    $msg = $msg." avvenuto con successo";
}

if($error == 0)
    $storico->insRow($action, $storico_msg."ufficio giudiziario ".$storico_msg_ ."in ".$cls_help->getVar('Comune'));

header("Location: authorityOffice.php?c={$_POST['c']}&a={$_POST['a']}&error={$error}&msg={$msg}&Authority_ID={$_POST['Authority_ID']}");

?>
