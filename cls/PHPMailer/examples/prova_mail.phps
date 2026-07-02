<?php
/**
 * PROVA SMTP
 */

require EMAIL.'/PHPMailerAutoload.php';

$mail = new PHPMailer();

$mail->IsSMTP();  // telling the class to use SMTP
$mail->Host     = "smtps.pec.aruba.it"; // SMTP server
$mail->SMTPAuth = true;
$mail->Username = "corrispondenza@pec.sarida.it";
$mail->Password = "daniela12111965";

$mail->Port = 465;
$mail->SMTPSecure = 'ssl';

$mail->FromName = "PEC Sarida";
$mail->From     = "corrispondenza@pec.sarida.it";
$mail->AddAddress("mirkopas85@gmail.com");

$mail->Subject  = "PROVA PEC SARIDA";
$mail->Body     = "Hola amigos!";
$mail->WordWrap = 50;

$path_file = FIRME."/".$c."/Funzionario_Firma_C826_2014-09-04.jpg";
$mail->addAttachment($path_file,"ALLEGATO");

if(!$mail->Send()) {
	echo 'Message was not sent.';
	echo 'Mailer error: ' . $mail->ErrorInfo;
} else {
	echo 'Message has been sent.';
}

?>