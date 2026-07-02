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


$submit = $cls_help->getVar("submitInfo");
$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$form_type = $cls_help->getVar("form_type");
$subtext_variable = $cls_help->getVar("subtext_variable");
$subtext_id = $cls_help->getVar("subtext_id");
$html_text = $cls_help->getVar("html_text");

$testo_id = $cls_help->getVar("form_type");
$query_testo = "SELECT * FROM document_type WHERE EnabledHtml=1 AND Id = ".$testo_id." ORDER BY Description";
$testo = $cls_db->getArrayLine($cls_db->SelectQuery($query_testo));
$variabile = $cls_help->getVar("subtext_variable");
$sottotesto_id = $cls_help->getVar("subtext_id");
$query_sottotesto = "SELECT * FROM subtext_parameters WHERE CC = '*****' AND Form_Type_ID = ".$testo_id." AND Variable = '".$variabile."' AND Type_ID = ".$sottotesto_id." AND Disabled=0";
$sottotesto = $cls_db->getArrayLine($cls_db->SelectQuery($query_sottotesto));

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];	

$query="";
if($submit=="Update"){

    $query = "UPDATE subtext_parameters SET Content='".stripslashes($html_text)."', User=\"".$_SESSION['username']."\", Date=\"".date("Y-m-d")."\"";
    $query.= "WHERE CC=\"".$c."\" AND Form_Type_ID=".$form_type." AND Variable=\"".$subtext_variable."\" AND Type_ID=".$subtext_id;
    $action = "U";
    $storico_msg = "Modificato sottotesto '".$sottotesto['Type_Description']."' di testo '".$testo['Description']."' per ente ".$nome_ente."[".$c."]";

}else if($submit=="Insert"){
    $row = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM subtext_parameters where Form_Type_ID=".$form_type." AND CC = '*****' AND Variable='".$subtext_variable."' AND Type_ID=".$subtext_id));

    $query = "INSERT INTO subtext_parameters (Form_Type_ID, CC, Variable, Type_ID, Type_Description, Content, User, Date) ";
    $query.= "VALUES (".$form_type.",\"".$c."\",\"".$subtext_variable."\",".$subtext_id.",\"".$row['Type_Description']."\",'".stripslashes($html_text)."',\"".$_SESSION['username']."\",\"".date("Y-m-d")."\")";
    $action = "I";
    $storico_msg = "Inserito sottotesto '".$sottotesto['Type_Description']."' di testo '".$testo['Description']."' per ente ".$nome_ente."[".$c."]";
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
    header("Location: subtextParameters.php?form_type=".$form_type."&subtext_variable=".$subtext_variable."&subtext_id=".$subtext_id."&c=".$c."&a=".$a."&msg=".$msg."&error=".$err);
}
else
{
    $error = 2;
    $msg = "Warning, tipo salvataggio non conforme, query non eseguita";
}

if($error == 0)
    $storico->insRow($action, $storico_msg);

header("Location: subtextParameters.php?form_type=".$form_type."&subtext_variable=".$subtext_variable."&subtext_id=".$subtext_id."&c=".$c."&a=".$a."&msg=".$msg."&error=".$err);


?>