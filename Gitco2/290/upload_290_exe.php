<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_file.php");
include_once(CLS."/cls_help.php");

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_file = new cls_file();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$params = array(
    "allowedExt" => array('290','txt','001','xlsx'),
    "addDateInFilename" => true
);
$a_files = $cls_file->uploadFiles(DUENOVANTA."/toImport", $params);
foreach ($a_files['uploaded'] as $key=>$a_file){
    $impType = 1;
    if($a_file['extension']=="xlsx")
        $impType = 2;
    $a_imports = array(
        'table' => 'imports',
        'fields'=> array(
            array(  'name' => 'CC',                     'type' => 'string',    'value' => $c),
            array(  'name' => 'Import_Type_Id',         'type' => 'int',   'value' => $impType),
            array(  'name' => 'Name',                   'type' => 'string', 'value' => $a_file['original_filename']),
            array(  'name' => 'Filename',               'type' => 'string', 'value' => $a_file['filename']),
            array(  'name' => 'Upload_Datetime',        'type' => 'string', 'value' => $a_file['date']." ".$a_file['time']),
            array(  'name' => 'Upload_User_Id',         'type' => 'int', 'value' => $_SESSION['aut_progr']),
            array(  'name' => 'Import_Status_Id',       'type' => 'int',   'value' => 1),
        )
    );
    $cls_db->DbSave($a_imports);
}

header("location: upload_290.php?a=".$a."&c=".$c);