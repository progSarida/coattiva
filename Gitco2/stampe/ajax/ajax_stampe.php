<?php
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
		
	include CLASSI . "/comuni.php";
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/290.php";
	include CLASSI . "/ruolo.php";

if (!session_id()) session_start();

$c = get_var('c');

$ajax = get_var('ajax');

switch($ajax)
{
	case "elimina_file":
		
	$atto_id = get_var('atto_id');
	$tipo_stampa = get_var('tipo_stampa');
	
	$file = get_var('file');
	$file_rar = get_var('file_rar');
	
	if($tipo_stampa=="DEFINITIVA")
	{
		$explodeCartelle = explode("/",$file);
		$nome_file = $explodeCartelle[count($explodeCartelle)-1];
		
		$cartella = "";
		for($i=0;$i<count($explodeCartelle)-1;$i++)
		{
		$cartella .= $explodeCartelle[$i]."/";
		}
		
		$atto = new atto($atto_id, $c);
		
		if($atto->Numero_Flusso!=null && $atto->Numero_Flusso!="" && $atto->Numero_Flusso!=0)
		{
			echo "FLUSSO ".$atto->Numero_Flusso."/".$atto->Anno_Flusso;
			die;
		}
		
		$atto->Data_Stampa = "0000-00-00";
		$atto->Stato_Stampa = "Da stampare";
				
		mysql_query('BEGIN');
		
		$control_update = $atto->Update($atto_id);
		
		if($control_update)
		{
			mysql_query('COMMIT');
			$dir = crea_dir($cartella."/ELIMINATI");
			copy($file,$dir."/Del_".$nome_file);
			unlink($file);
			
			echo "OK";
		}
		else 
		{
			echo "ERROR";
		}		
	}
	else if($tipo_stampa=="FLUSSO")
	{
		$expRar = explode("/",$file_rar);
		$nome_rar = $expRar[count($expRar)-1];
		
		$explodeCartelle = explode("/",$file);
		$nome_file = $explodeCartelle[count($explodeCartelle)-1];
		
		$cartella = "";
		for($i=0;$i<count($explodeCartelle)-1;$i++)
		{
			$cartella .= $explodeCartelle[$i]."/";
		}
		
		
		$explodePunto = explode (".", $nome_file);
		$estensione = $explodePunto[1];
			
		$explode = explode ("_", $explodePunto[0]);
		$control_comune = $explode[2];
		$control_anno = $explode[3];
		$control_numero = $explode[4];
		$control_data = $explode[5];
		
		$query = "UPDATE atto SET Numero_Flusso = '' , Anno_Flusso = '' , Data_Flusso = '0000-00-00' WHERE CC = '".$control_comune."' AND Numero_Flusso = '".$control_numero."' AND Anno_Flusso = '".$control_anno."' AND Data_Flusso = '".$control_data."'";
		
		$result = mysql_query($query);
		
		$dir = crea_dir($cartella."/ELIMINATI");
		copy($file,$dir."/Del_".$nome_file);
		unlink($file);
		unlink($file_rar);
		
		echo "OK";
		
	}

	
		break;
		
		case "nome":
		
		$ID = get_var('ID');
	
		$utente = new utente($ID, $c);
	
		if($utente->Genere!="D")
			$ritorno = $utente->Cognome."*".$utente->Nome;
		else
			$ritorno = $utente->Ditta." ".$utente->Forma_Giuridica_Oggetto->Sigla;
			
		echo $ritorno;
		
		break;
		
}


?>