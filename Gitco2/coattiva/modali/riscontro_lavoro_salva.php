<?php
	
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	include CLASSI . "/comuni.php";
	
	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	
	$a = get_var('a');
	$c = get_var('c');
	$p = get_var('p');
	$id_notifica = get_var('id_notifica');
	$pignoramento_id = get_var('pignoramento');
	$upload_dir = crea_dir( ATTI ."/". $c . "/Riscontri" );

	$del_file = get_var('del_file');
	if($del_file=="") $del_file="si";
		
	$pignoramento = new pignoramento($pignoramento_id, $c);
	$partita = new partita($pignoramento->Partita_ID, $c);
	$utente = new utente($partita->Utente_ID, $c);
	if($utente->Genere=="D")
		$nome_utente = $utente->Ditta;
	else
		$nome_utente = $utente->Cognome."_".$utente->Nome;
	
	$salva = new notifica_atto( $id_notifica , $c );	
	
	if($del_file=="si")
	{
		unlink($upload_dir."/".$salva->Link_Riscontro);
	
		$salva->Link_Riscontro = "";
	}
	
	if(isset($_FILES['file_riscontro']) && $_FILES['file_riscontro']['size'] > 0)
	{

		$file = $_FILES['file_riscontro'];
		
		$tipo_pigno = $pignoramento->tipo_pignoramento("sigla");
		$nuovo_file = "Riscontro_".$tipo_pigno."_".$c."_Pigno_".$pignoramento->ID_Cronologico."_".$pignoramento->Anno_Cronologico."_";
		$nuovo_file.= $nome_utente."_NOT_".$id_notifica;
		
		if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']))
		{
			$im = new imagick(  $file['tmp_name'] );
			$im->setImageFormat('pdf');
			
			$im->writeImage( $upload_dir."/".$nuovo_file.".pdf" );
			
			$salva->Link_Riscontro = $nuovo_file.".pdf";
		}
		
		

	} 
	
	$salva->Tipo_Riscontro = get_var('tipo_riscontro');
	$salva->Mezzo_Riscontro = get_var('mezzo_riscontro');
	$salva->Data_Riscontro = to_mysql_date(get_var('data_riscontro'));
	$salva->Note_Riscontro = get_var('note_riscontro');
	$salva->Testo_Riscontro = get_var('testo_riscontro');
	$salva->Importo_Riscontro = conv_num(get_var('importo_trattenuta'));
	$salva->Periodicita_Rate = get_var('periodicita');
	$salva->Differenza_Importo = get_var('valore_trattenute');
	$salva->Numero_Rate = get_var('numero_trattenute');
	$salva->Data_Inizio_Rate = to_mysql_date(get_var('data_inizio_trattenute'));
	
	mysql_query('BEGIN');
		
	$control_salva = $salva->Update($id_notifica);
		
	if( $control_salva )
	{
		mysql_query('COMMIT');

		echo 'OK ';
	}
	else
	{
		echo 'ERROR '.mysql_error();
		mysql_query('ROLLBACK');		
	}
					


?>