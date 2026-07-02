<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once(CLS."/cls_file.php");
include_once(CLS."/cls_curl.php");

$cls_file = new cls_file();
$uploadFile = $cls_file->multipartFile("prova.zip");

$cls_curl = new cls_curl();
$cls_curl->setCredentials("KSV002","gemosavona18");

//queste due righe per la richiesta
$cls_curl->uploadFile($uploadFile);
$cls_curl->richiestaFornituraPec();
//$cls_curl->soapFile = null;



$cls_curl = new cls_curl();
$cls_curl->setCredentials("KSV002","gemosavona18");
//questa riga per lo scarico
$cls_curl->scaricoFornituraPec("0000000052");

include(INC."/footer.php");

?>