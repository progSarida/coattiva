<?php

	if(!session_id())session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
		die;
	}
	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");

	include_once CLS."/cls_db.php";
	include_once CLS."/cls_help.php";

	$cls_db = new cls_db();
	$cls_help = new cls_help();


	$ric_cod_contr = $cls_help->getVar('ric_cod_contr');
	$old_cod_contr = $cls_help->getVar('old_cod_contr');
	$c = $cls_help->getVar('c');
	$a = $cls_help->getVar('a');
	
	if((!is_numeric($ric_cod_contr)) || $ric_cod_contr==null) 
	{
		$cod_result = "NO".$old_cod_contr;
	}
	else
	{
    	$query = "SELECT ID FROM utente WHERE Comune_ID ='$ric_cod_contr' and CC_Comune='$c'";
    	$cod_result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query))["ID"];// single_answer_query($query);
	}
	
	if($cod_result==null)$cod_result = "NO ".$old_cod_contr;
    
    echo $cod_result;
    
    
 ?>

