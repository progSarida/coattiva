<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
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
$table = $cls_help->getVar("table");

if($idParams>0){
    $a_bind = $a_params;
    $query = "UPDATE ".$table." SET cc='".$c."', updated='".$today."'";
    $bindTypes = "";
    for($i=1;$i<=count($a_params);$i++){
        $query.= ", field".$i." = ? ";
        $bindTypes.="s";
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