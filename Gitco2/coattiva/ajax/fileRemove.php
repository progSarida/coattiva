<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");
include_once(CLS."/cls_help.php");

$cls_help = new cls_help();
$filePath = $cls_help->getVar("file");
// First Check if file exists
$response = array('status'=>false);

if( file_exists($filePath) ) {
unlink($filePath);
$response['status'] = true;
}

// Send JSON Data to AJAX Request
echo json_encode($response);

?>