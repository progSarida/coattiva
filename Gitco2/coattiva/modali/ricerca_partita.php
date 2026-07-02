<?php
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";

if(!session_id())session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

	$ric_cod_contr = get_var('ric_cod_contr');
	$old_cod_contr = get_var('old_cod_contr');
	$c = get_var('c');
	$a = get_var('a');
	
	if((!is_numeric($ric_cod_contr)) || $ric_cod_contr==null) 
	{
		$cod_result = "NO ".$old_cod_contr;
	}
	else
	{
    	$query = "SELECT ID, Anno_Riferimento FROM partita_tributi WHERE Comune_ID ='$ric_cod_contr' and CC='$c'";
    	$result = safe_query($query);
		$val = mysql_fetch_array($result);
    	
    	if($val['ID'] == null)
    		$cod_result = "NO ".$old_cod_contr;
    	else 
    		$cod_result = $val['ID']." ".$val['Anno_Riferimento'];
	}
		    
    echo $cod_result;
    
    
 ?>

