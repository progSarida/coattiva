<?php
	
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/ruolo.php";
	
	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	
	$a = get_var('a');
	$c = get_var('c');
	$p = get_var('p');
	$id_doc = get_var('id_doc');
	$cancella = get_var('cancella');
	$upload_dir = crea_dir( ATTI ."/". $c . "/Documenti" );

if($cancella == "no")
{
				
	$del_file = get_var('del_file');
	if($del_file=="") $del_file="si";
		
	$atto = get_var('atto');
	$tipo = get_var('tipo');
	$data_creazione = date('Y-m-d');
	$info = get_var('info');
	$oggetto = get_var('oggetto');
	$contenuto = get_var('contenuto');			
	$data_stampa = to_mysql_date(get_var('data_stampa'));
	$utente = $p;
		
	$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
	$comune_id = single_query($query);
	
	$salva = new documento( $id_doc , $c );
		
	$salva->CC = $c;
	
	if($id_doc==0)
		$salva->Comune_ID = $comune_id + 1;
	
	
	if($del_file=="si")
	{
		unlink($upload_dir."/".$salva->File);
	
		$salva->File = "";
	}
	
	if(isset($_FILES['file_doc']) && $_FILES['file_doc']['size'] > 0)
	{
		
		$data_file = date('Y-m-d_H-i-s');		
		$file = $_FILES['file_doc'];
		$file_ext = explode(".",$file['name']);
		$estensione = $file_ext[count($file_ext)-1];
		
		$nuovo_file = "Doc_Manuale_".$data_file.".".$estensione;
		if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']))
		{
			move_uploaded_file($file['tmp_name'], $upload_dir."/".$nuovo_file);
		}
		
		$salva->File = $nuovo_file;

	} 
	
	$salva->Utente_ID = $p;
	$salva->Tipo = $tipo;
	$salva->Atto = $atto;
	$salva->Data_Creazione = $data_creazione;
	$salva->Informazioni_Aggiuntive = $info;
	$salva->Oggetto = $oggetto;
	$salva->Contenuto = $contenuto;
	$salva->Data_Stampa = $data_stampa;

	mysql_query('BEGIN');
		
	if($id_doc!=0)
		$control_salva = $salva->Update( $id_doc , true );
	else
		$control_salva = $salva->Insert( true );
		
	if( $control_salva )
	{
		mysql_query('COMMIT');

		echo 'OK ';
	}
	else
	{
		mysql_query('ROLLBACK');

		echo 'ERROR '.mysql_error();
	}
					
}
else
{
	$cancella = new documento( $id_doc , $c );
	
	$control_cancella = $cancella->Delete();
	$atto = new atto(null, $c);
	$control_atto = $atto->atto_collegato_al_documento($id_doc,$c);
	
	if( $control_cancella && $control_atto )
	{
		mysql_query('COMMIT');
		if(is_file($upload_dir."/".$cancella->File))
			unlink($upload_dir."/".$cancella->File);
	
		echo 'DELETE';
	}
	else
	{
		mysql_query('ROLLBACK');
	
		echo 'ERROR '.mysql_error();
	}
}
?>