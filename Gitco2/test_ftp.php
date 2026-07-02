<?php

include("_path.php");
include("_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_ftp.php");

define('FTP_HOST', 'ftp.mercurioservice.it');
define('FTP_USER', 'sarida');
define('FTP_PASS', '1ftp4sarida');
$ftp = new cls_ftp(FTP_HOST, FTP_USER, FTP_PASS,true);

$file = ROOT."/stampefattura.pdf";
$ftp->loadFile($file,"/SARIDA_TEST/test.pdf");



?>

<script>


</script>





<?php include(INC."/footer.php"); ?>
