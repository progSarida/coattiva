<?php

if (!session_id()) session_start();

include("_path.php");
include("_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_phpmailer.php");

$cls_db = new cls_db();
//$_SESSION['username']
$a_sender = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM user_emails WHERE ID=1"));
$a_recipient = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM printer WHERE ID=2"));

$cls_mail = new cls_phpmailer($a_sender);
//$cls_mail->SMTPDebug = 2;
$a_params = array(
    "subject" => "OGGETTO TEST",
    "body" => "CORPO EMAIL TEST"
);

$cls_mail->mailCreation($a_params);
$cls_mail->addAddress($a_recipient['Email']);
$cls_mail->addAttachment(ROOT."/stampefattura.pdf");

if($cls_mail->send()){
    echo "Mail spedita con successo";
}
else{
    echo "<br>ERRORE SPEDIZIONE";
}


?>





<?php include(INC."/footer.php"); ?>
