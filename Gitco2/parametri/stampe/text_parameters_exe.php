<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_textParameters.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

$c = $cls_help->getVar("c");
$idParams = $cls_help->getVar("idParams");
$today = date("Y-m-d");
$a_params = $cls_help->getVar("field");
$a_direct = $cls_help->getVar("direct");
$a_collector = $cls_help->getVar("collector");
$a_bailiff = $cls_help->getVar("bailiff");
$table = $cls_help->getVar("table");

if($idParams>0){
    $a_bind = array_merge($a_params);
    $query = "UPDATE ".$table." SET cc='".$c."', updated='".$today."'";
    $bindTypes = "";
    for($i=1;$i<=count($a_params);$i++){
        $query.= ", field".$i." = ? ";
        $bindTypes.="s";
    }

    if($a_direct!=null){
        $a_bind = array_merge($a_bind,$a_direct);
        $query.= ", direct_header = ?, direct_subheader = ?, direct_text = ? ";
        $bindTypes.="sss";
    }
    if($a_collector!=null){
        $a_bind = array_merge($a_bind,$a_collector);
        $query.= ", collector_header = ?, collector_subheader = ?, collector_text = ? ";
        $bindTypes.="sss";
    }
    if($a_bailiff!=null){
        $a_bind = array_merge($a_bind,$a_bailiff);
        $query.= ", bailiff_header = ?, bailiff_subheader = ?, bailiff_text = ? ";
        $bindTypes.="sss";
    }

    $query.= "WHERE ID=".$idParams;
}else{
    $a_bind = array_merge(array($c,$today),$a_params);
    $bindTypes = "ss";
    $insertFields = "cc, updated";
    $insertValues = "?, ?";
    for($i=1;$i<=count($a_params);$i++){
        $insertFields.= ", field".$i;
        $insertValues.= ", ?";
        $bindTypes.="s";
    }

    if($a_direct!=null) {
        $a_bind = array_merge($a_bind,$a_direct);
        $insertFields .= ", direct_header, direct_subheader, direct_text";
        $bindTypes.="sss";
        $insertValues.= ", ?, ?, ?";
    }
    if($a_collector!=null){
        $a_bind = array_merge($a_bind,$a_collector);
        $insertFields.= ", collector_header, collector_subheader, collector_text";
        $bindTypes.="sss";
        $insertValues.= ", ?, ?, ?";
    }
    if($a_bailiff!=null){
        $a_bind = array_merge($a_bind,$a_bailiff);
        $insertFields.= ", bailiff_header, bailiff_subheader, bailiff_text";
        $bindTypes.="sss";
        $insertValues.= ", ?, ?, ?";
    }

    $query = "INSERT INTO ".$table." (".$insertFields.") ";
    $query.= "VALUES (".$insertValues.")";
}

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
$checkBind = $cls_db->bind_array($query,$bindTypes,$a_bind);

if($checkBind!==false) {
    $cls_db->End_Transaction();
    echo "SAVED";
}
else{
    echo "ERROR ".mysqli_error($cls_db->conn);
    $cls_db->Rollback();
}

?>