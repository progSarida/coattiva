<?php
/**
 * PROVA SMTP
 */

require CLASSI.'/email_reader.php';

$emails = New Email_reader();

$emails->setServer('imap.googlemail.com');
$emails->setUser('mirkopas85@gmail.com');
$emails->setPassword('striker20031984');
$emails->setPort('993');

$emails->inbox();

$total = count($emails->inbox);

$y=0;
for($i=$total-1;$i>=$total-5;$i--) 
{
	$email[$y] = $emails->inbox[$i];
	$y++;
}

alert($email[$y]);


?>