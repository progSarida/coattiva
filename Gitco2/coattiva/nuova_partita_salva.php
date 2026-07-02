<?php
	
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/ruolo.php";
	
	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
		
	$a = get_var('a');
	$c = get_var('c');
	
	$id_ruolo = get_var('id_ruolo');
	if($id_ruolo==0)
	{
		$desc_ruolo = get_var('ruolo');
		
		$tipo_ruolo = get_var('tipo_ruolo');
		if ($tipo_ruolo == "Coa") $tipo_ruolo = "Coattivo";
		else $tipo_ruolo = "Ordinario";
		
		$data_fornitura = get_var('data');
		$progr_ruolo = get_var('progr_ruolo');
		$num_rate = get_var('num_rate');
		
		$field_ruolo = array();
		$value_ruolo = array();
		
		$field_ruolo[] = "CC";					$value_ruolo[] = $c; 
		
		$comune_id = single_answer_query("SELECT MAX(Comune_ID) FROM ruolo WHERE CC = '".$c."'");
		
		$field_ruolo[] = "Comune_ID";			$value_ruolo[] = $comune_id+1;
		$field_ruolo[] = "Descrizione";			$value_ruolo[] = $desc_ruolo;
		$field_ruolo[] = "Data_Fornitura";		$value_ruolo[] = to_mysql_date($data_fornitura);
		$field_ruolo[] = "Ruolo";				$value_ruolo[] = $tipo_ruolo;
		$field_ruolo[] = "Progr_Fornitura";		$value_ruolo[] = $progr_ruolo;
		$field_ruolo[] = "Num_Rate";			$value_ruolo[] = $num_rate;
				
		$id_ruolo = table_insert_record('ruolo', $field_ruolo, $value_ruolo);
		
	}
	
	$num = get_var('num');	
	
	$tipo_partita = get_var('tipo_partita');
	$anno_rif = get_var('anno_rif');
	$utente = get_var('utente');
	$tipo_coo = get_var('tipo_coo');
	
	$coo_ID = "";
	for($i=0;$i<$num;$i++)
	{
		$id_coo = get_var('coo'.($i+1));
		$coo_ID .= "*".$id_coo;
	}
	
	$field_partita = array();
	$value_partita = array();
	
	$field_partita[] = "CC";					$value_partita[] = $c;
	
	$comune_id = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$c."'");
	
	$field_partita[] = "Comune_ID";				$value_partita[] = $comune_id+1;
	$field_partita[] = "Ruolo_ID";				$value_partita[] = $id_ruolo;
	$field_partita[] = "Anno_Riferimento";		$value_partita[] = $anno_rif;
	$field_partita[] = "Tipo";					$value_partita[] = $tipo_partita;
	$field_partita[] = "Utente_ID";				$value_partita[] = $utente;
	$field_partita[] = "Coo_Tipo";				$value_partita[] = $tipo_coo;
	$field_partita[] = "Coo_ID";				$value_partita[] = $coo_ID;
	
	$partita_id = table_insert_record('partita_tributi', $field_partita, $value_partita);

	if($partita_id!=0)
	{
		echo "OK ".$partita_id;
	}
	else echo "error";
		
?>