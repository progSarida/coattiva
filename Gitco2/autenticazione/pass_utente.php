<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(CLS."/cls_help.php");
include(CLS."/cls_db.php");

$cls_help = new cls_help();
$cls_db = new cls_db();

$username = $cls_help->getVar('user');
$password = md5($cls_help->getVar('pass'));

$new_pass = md5($cls_help->getVar('new_pass'));
$servizio = $cls_help->getVar('servizio');

$query = "SELECT ID FROM autenticazione WHERE User = '".$username."' AND Password = '".$password."'";
$authArray = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
//$authArray = select_mysql_array("ID", "autenticazione", "User = '".$username."' AND Password = '".$password."'");

if( $authArray == null )
{
	
	echo "no";

}
else if ( $authArray != null )
{
	
	$query = "UPDATE autenticazione SET Password = '".$new_pass."' , Data = '".date('Y-m-d')."' WHERE ID = '".$authArray['ID']."'";
	$control = $cls_db->ExecuteQuery($query);// mysql_query($query);
	
	if($control)
	{
		echo "ok";
	}
	else 
	{
		echo "fail";
	}
	
}
else 
{
	echo "fail";
}

?>