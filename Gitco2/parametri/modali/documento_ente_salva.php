<?php
	
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/enti_esterni.php";
	
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
	$id_ente = get_var('id_ente');
	$cancella = get_var('cancella');
	
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
		
	$query = "SELECT MAX(Comune_ID) as Com FROM documento_ente WHERE CC = '".$c."'";
	$comune_id = single_query($query);
	
	$salva = new documento_ente($id_doc, $c);
		
	$salva->CC = $c;
	
	if($id_doc==0)
		$salva->Comune_ID = $comune_id + 1;
	
	$upload_dir = crea_dir( ATTI ."/". $c . "/Documenti" );
	if($del_file=="si")
	{
		unlink($upload_dir."/".$salva->File);
	
		$salva->File = "";
	}
	
	if( isset($_FILES['file_doc_ente']) && $_FILES['file_doc_ente']['size'] > 0 )
	{
		
		$data_file = date('Y-m-d_H-i-s');		
		$file = $_FILES['file_doc_ente'];
		$file_ext = explode(".",$file['name']);
		$estensione = $file_ext[count($file_ext)-1];
		
		$nuovo_file = "Doc_Manuale_".$data_file.".".$estensione;
		if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']))
		{
			move_uploaded_file($file['tmp_name'], $upload_dir."/".$nuovo_file);
		}
		
		$salva->File = $nuovo_file;

	} 
	
	$salva->Ente_Esterno_ID = $id_ente;
	$salva->Tipo = $tipo;
	$salva->Atto = $atto;
	$salva->Data_Creazione = $data_creazione;
	$salva->Informazioni_Aggiuntive = $info;
	$salva->Oggetto = $oggetto;
	$salva->Contenuto = $contenuto;
	$salva->Data_Stampa = $data_stampa;

	mysql_query('BEGIN');
		
	if($id_doc!=0)
	{
		$control_salva = $salva->Update( $id_doc , true );
	}
	else
	{
		$control_salva = $salva->Insert( true );
	}
		
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
	$cancella = new documento_ente( $id_doc , $c );
	
	$control_cancella = $cancella->Delete();
	
	if( $control_cancella )
	{
		mysql_query('COMMIT');
	
		echo 'DELETE';
	}
	else
	{
		mysql_query('ROLLBACK');
	
		echo 'ERROR '.mysql_error();
	}
}

?>