<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_crypt.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_crypt = new cls_crypt();


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');


$error = 0;
$msg = "Salvataggio avvenuto con successo";

$params = $cls_help->getVar('Params');

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();
$check = true;

foreach($params as $key=>$a_email){
	if(empty($a_email['Address']) && !empty($a_email['Id'])){
		$query = "DELETE FROM user_emails WHERE Id=".$a_email['Id'];
		$cls_db->ExecuteQuery($query);
		continue;
	}
	else if(empty($a_email['Address'])){
		continue;
	}

	if(empty($a_email['Email_Config_Id']))
		continue;

	if(!empty($a_email['ID']))
		$a_where = array("ID" => $a_email['ID']);
	else
		$a_where = null;	

	if($a_email['Password']!="")
		$a_email['Password'] = $cls_crypt->encryptIt($a_email['Password']);
	$a_email['OutPassword'] = $a_email['Password'];
	$a_email['OutUsername'] = $a_email['Username'];
	$a_email['MailType'] = $key;

	$a_save = $cls_db->GetObjectQuery("user_emails", $a_email ,null,$a_where);
	$checkSave = $cls_db->DbSave($a_save);
	if($check && $checkSave===false)
		$check = false;
}

if($check){
	$cls_db->End_Transaction();
}
else{
	$cls_db->Rollback();
	$error = 1;
	$msg = "Salvataggio fallito!";
}

header("Location: par_user_emails.php?a={$a}&c={$c}&error={$error}&msg={$msg}");
?>
