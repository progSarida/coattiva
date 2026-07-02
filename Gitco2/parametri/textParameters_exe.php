<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/Gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoTesti','7');
$cls_db = new cls_db();
$cls_help = new cls_help();

$err = 0;
$msg = "";
$action = "";
$storico_msg = "";

//$cls_help->alert("EHI!");

$submit = $cls_help->getVar("submitInfo");
$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$form_type = $cls_help->getVar("form_type");
$html_text = $cls_help->getVar("html_text");

$storico_query = "SELECT * FROM document_type AS DT LEFT JOIN text_parameters AS TP ON DT.Id = TP.Form_Type_ID AND TP.CC = '".$c."' WHERE EnabledHtml=1 AND Id = '".$form_type."'";
$tipo = $cls_db->getArrayLine($cls_db->SelectQuery($storico_query) );
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$query = "";
if($submit=="Update")
{
    $query = "UPDATE text_parameters SET Content='".stripslashes($html_text)."', User=\"".$_SESSION['username']."\", Date=\"".date("Y-m-d")."\"";
    $query.= "WHERE CC=\"".$c."\" AND Form_Type_ID=".$form_type;
    $action = "U";
    $storico_msg = "Modificato testo '".$tipo['Description']."' ";
}
else if($submit=="Insert")
{
    $query = "INSERT INTO text_parameters (Form_Type_ID, CC, Content, User, Date) ";
    $query.= "VALUES (".$form_type.",\"".$c."\",'".stripslashes($html_text)."',\"".$_SESSION['username']."\",\"".date("Y-m-d")."\")";
    $action = "I";
    $storico_msg = "Inserito testo '".$tipo['Description']."' ";
}

if($query!=""){
    $checkQuery = $cls_db->ExecuteQuery($query);

    if($checkQuery!==false) {
        $msg = "Salvataggio riuscito correttamente";

    }
    else{
        $error = 1;
        $msg = "Errore salvataggio non riuscito. ".mysqli_error($cls_db->conn);
    }
    header("Location: textParameters.php?c=".$c."&a=".$a."&form_type=".$form_type."&msg=".$msg."&error=".$err);
}
else
{
    $error = 2;
    $msg = "Warning, tipo salvataggio non conforme. Query non eseguita";
}

if($error == 0)
    $storico->insRow($action, $storico_msg."ente ".$nome_ente."[".$c."]");

header("Location: textParameters.php?c=".$c."&a=".$a."&form_type=".$form_type."&msg=".$msg."&error=".$err);


?>