<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS."/cls_db.php";

$cls_db = new cls_db();

if((int)$_POST["gestore_id"] > 0)
    $result = $cls_db->ExecuteQuery("UPDATE gestore SET File_Firma = NULL WHERE ID = ".$_POST["gestore_id"]);

if(!$result) {
    echo "ERROR";
    die;
}

unlink($_POST["path"]);

echo "OK";