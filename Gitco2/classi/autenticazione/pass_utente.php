<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

$username = get_var('user');
$password = md5(get_var('pass'));

$new_pass = md5(get_var('new_pass'));
$servizio = get_var('servizio');

$authArray = select_mysql_array("ID", "autenticazione", "User = '".$username."' AND Password = '".$password."'");

if(count( $authArray)==0 )
{
	
	echo "no";

}
else if ( count($authArray)==1 )
{
	
	$query = "UPDATE autenticazione SET Password = '".$new_pass."' , Data = '".date('Y-m-d')."' WHERE ID = '".$authArray[0]['ID']."'";
	$control = mysql_query($query);
	
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