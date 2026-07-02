<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";
	include_once CLS . "/cls_DateTimeInLine.php";
	include_once CLS . "/cls_Utils.php";
	include_once CLS . "/cls_storico.php";
	include_once(CLS."/cls_GestionePartita.php");

	$storico = new storico('storicoRuolo','3');
	$cls_partita = new cls_GP();
	$cls_db = new cls_db();
	$cls_help = new cls_help();
	$cls_utils = new cls_Utils();
	$cls_date = new cls_DateTimeI("DB",false);

	$note_interne = $cls_help->getVar('note_interne'); 


	$invia = $cls_help->getVar('invia_submit');
	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');

	$error = 0;
	$msg = "";

	$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
	$nome_ente = $ente['Denominazione'];

	$ruolo_ID = $cls_help->getVar('ruolo');
	$partita_ID = $cls_help->getVar('partita');
	$tipo_partita = $cls_help->getVar('tipo_partita');
	$sottotipo_partita = $cls_help->getVar('sottotipo_partita')==null?"":$cls_help->getVar('sottotipo_partita');
	$anno_rif = $cls_help->getVar('anno_rif');
	$intestatario = $cls_help->getVar('utente');
	$File_1 = $cls_help->getVar('File_1');
	$File_2 = $cls_help->getVar('File_2');
	$ID_PT = $cls_help->getVar('ID_PT');

	$info_cartella = $cls_help->getVar('info_cartella');
	$data_interessi = $cls_date->GetDateDB($cls_help->getVar('data_interessi'),"IT");
	$select_info = $cls_help->getVar('select_info');

	$titolo_sanz = $cls_help->getVar('titolo_sanz');
	$data_sanz = $cls_date->GetDateDB($cls_help->getVar('data_sanz'),"IT");
	$targa_sanz = $cls_help->getVar('targa_sanz');

	$titolo_ent = $cls_help->getVar('titolo_ent');
	$desc_ent = $cls_help->getVar('desc_ent');

	$matri = $cls_help->getVar('matri');

	$ID_Tributo = $cls_help->getVar('progr_tributo');
	$select_atto = $cls_help->getVar('select_atto');

	$anno_tributo = $cls_help->getVar('anno_tributo');
	$cod_tributo = $cls_help->getVar('cod_tributo');
	$importo = str_replace(",", ".", $cls_help->getVar('importo'));

	$del_file_1 = $cls_help->getVar('del_file_1');
	if($del_file_1=="") $del_file_1="si";

	$del_file_2 = $cls_help->getVar('del_file_2');
	if($del_file_2=="") $del_file_2="si";

	$data_file = date('Y-m-d_H-i-s');
	$document_dir = crea_dir( ATTI ."/". $c . "/Documenti" );

	if(isset($_FILES['img_1']) && $_FILES['img_1']['size'] > 0)
	{
		$percorso_file = $_FILES['img_1']['tmp_name'];
		$nome_file = $_FILES['img_1']['name'];
	}
	else
	{
		$percorso_file = "";
		$nome_file = "";
	}

	$estensione_array = explode(".", $nome_file);
	$estensione = $estensione_array[count($estensione_array)-1];
	$nome_file_senza_estensione = substr($nome_file,0,strlen($nome_file)-strlen($estensione)-1);

	$file_1 = $document_dir."/".$nome_file_senza_estensione."_".$data_file;

	if(isset($_FILES['img_2']) && $_FILES['img_2']['size'] > 0)
	{
		$percorso_file_2 = $_FILES['img_2']['tmp_name'];
		$nome_file_2 = $_FILES['img_2']['name'];
	}
	else
	{
		$percorso_file_2 = "";
		$nome_file_2 = "";
	}

	$estensione_array_2 = explode(".", $nome_file_2);
	$estensione_2 = $estensione_array_2[count($estensione_array_2)-1];
	$nome_file_senza_estensione_2 = substr($nome_file_2,0,strlen($nome_file_2)-strlen($estensione_2)-1);

	$file_2 = $document_dir."/".$nome_file_senza_estensione_2."_".$data_file;

		//$atto_id = select_mysql_array("ID", "atto","Partita_ID = '".$this->ID."'");
	//$query = "SELECT COUNT(ID) as NumAtti FROM atto WHERE Partita_ID = '".$partita_ID."'";
	//$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$partita = new partita( $partita_ID, $c );
	$numero_atti = $cls_help->getVar("NumAtti");//$result["NumAtti"];//count($partita->Atto);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if($invia == "Delete")
	{
		if($partita_ID==null)
		{
			//echo "ERROR_DELETED ".$partita_ID." PARTITA INESISTENTE!";
			$error = 1;
			$msg = "Errore partita ".$partita_ID." inesistente";
			header("Location: gestione_partita.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			die;
		}

		if($numero_atti>0)
		{
			echo "ERROR_DELETED ".$partita_ID." IMPOSSIBILE CANCELLARE I CODICI TRIBUTO! ATTO SUCCESSIVO PRESENTE IN ARCHIVIO!";
			$error = 2;
			$msg = "Impossibile cancellare i codici tributo! Ato successivo presente in archivio per la partita ".$partita_ID;
			header("Location: gestione_partita.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			die;
		}

		//$query = "DELETE FROM tributo WHERE Partita_ID = '" . $partita_ID . "' ";
		//$result = mysql_query($query);
		//return $result;

		if(!$cls_db->Delete("tributo","Partita_ID = '" . $partita_ID . "'"))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore imposibile cancellare i dati";
		}
		else
		{
				$a_paramsPartita = array(
				    'table' => 'partita_tributi',
				    'fields'=> array(
				        array(  'name' => 'Cancellazione',   'type' => 'string', 'value' => 'si')
				    ),
						'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $partita_ID)
				);

				if(!$cls_db->DbSave($a_paramsPartita))
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Errore imposibile cancellare i dati";
				}else{
					$partita = $cls_partita->getDataPartita($partita_ID, $c, $a);
					$query = "SELECT IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(COGNOME,''),' ',COALESCE(Nome,''))) as U, Comune_ID FROM utente where ID = '".$intestatario."'";
					$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
					$msg_utente = $utente["U"];
					$storico->insRow('D',"Eliminata partita ".$info_cartella."(".$partita['Comune_ID'].") dell'utente ".$msg_utente." (".$utente['Comune_ID'].") per ente ".$nome_ente."[".$c."]");
					$msg = "Dati eliminati con successo";
				}
		}


		/*mysql_query('BEGIN');
		$control = $partita->Delete_Tributi();
		if($control===false)
		{
			echo "ERROR_DELETED ".$partita_ID." ".mysql_error();
			mysql_query('ROLLBACK');
			die;
		}
		else
		{
			$partita->Cancellazione = "si";
			$control_partita = $partita->Update($partita_ID);
			if($control_partita===false)
			{
				echo "ERROR_DELETED ".$partita_ID." ".mysql_error();
				mysql_query('ROLLBACK');
				die;
			}
			else
			{
				echo "DELETED ".$partita_ID;
				mysql_query('COMMIT');
				die;
			}
		}*/

		header("Location: gestione_partita.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
		die;
	}

//	mysql_query('BEGIN');
//if(!isset($ID_PT)||$ID_PT=="") $ID_PT = "0";



	$result = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT MAX(Comune_ID) as MaxID FROM partita_tributi WHERE CC = '".$c."'"));
	$comune_id_partita = $result["MaxID"]+1;

	if($partita_ID=="" || $partita_ID==null) $partita_ID = "NULL";

	$query = "SELECT * FROM partita_tributi WHERE ID = ".$partita_ID." AND CC = '".$c."'";
	$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");

	if($partita_ID == "NULL") $partita_ID = null;

	$flag_blocco = $cls_help->getVar('flag_blocco');
	$motivo_blocco = $cls_help->getVar('motivo_blocco');
	$note_blocco = $cls_help->getVar('note_blocco');
	
	

	/** questa parte serve per salvare il blocco con le sue motivazioni, per ora in questa pagina è bloccata **/
	/*if($flag_blocco=="si")
	{
		$partita->Motivo_Blocco = $motivo_blocco;
		$partita->Note_Blocco = $note_blocco;
		if($partita->Data_Attivazione_Flag_Blocco_Coazione == null)
			$partita->Data_Attivazione_Flag_Blocco_Coazione = date("Y-m-d");
	}
	else
	{
		$flag_blocco = "";
		$partita->Motivo_Blocco = null;
		$partita->Note_Blocco = "";
		$partita->Data_Attivazione_Flag_Blocco_Coazione = null;
	}

	$partita->Flag_Blocco_Coazione = $flag_blocco;*/

	$partita->Ruolo_ID = $ruolo_ID;
	$partita->CC = $c;
	$partita->Anno_Riferimento = $anno_rif;
	$partita->Tipo = $tipo_partita;
	$partita->Sottotipo = $sottotipo_partita;
	$partita->Utente_ID = $intestatario;

	//echo "comune_id_partita ".$comune_id_partita;
	/*$a_paramsPartita = array(
			'table' => 'partita_tributi',
			'fields'=> array(
					array(  'name' => 'Ruolo_ID',   'type' => 'int', 'value' => $ruolo_ID),
					array(  'name' => 'CC',   'type' => 'string', 'value' => $c),
					array(  'name' => 'Anno_Riferimento',   'type' => 'int', 'value' => $anno_rif),
					array(  'name' => 'Tipo',   'type' => 'string', 'value' => $tipo_partita),
					array(  'name' => 'Sottotipo',   'type' => 'string', 'value' => $sottotipo_partita),
					array(  'name' => 'Utente_ID',   'type' => 'int', 'value' => $intestatario)
			)
	);*/

	if($del_file_1=="si")
	{
		unlink($document_dir."/".$partita->File_1);


		$partita->File_1 = "";
	}

	if($percorso_file != "")
	{
		$im = new imagick( $percorso_file );

		$im->setImageCompression(Imagick::COMPRESSION_JPEG);
		$im->setImageCompressionQuality(10);
		$im->writeImage( $file_1.'.jpg' );

		$partita->File_1 = $nome_file_senza_estensione."_".$data_file.'.jpg';
	}

	if($del_file_2=="si")
	{
		unlink($document_dir."/".$partita->File_2);

		$partita->File_2 = "";
	}

	if($percorso_file_2 != "")
	{
		$im = new imagick( $percorso_file_2 );

		$im->setImageCompression(Imagick::COMPRESSION_JPEG);
		$im->setImageCompressionQuality(10);
		$im->writeImage( $file_2.'.jpg' );

		$partita->File_2 = $nome_file_senza_estensione_2."_".$data_file.'.jpg';
	}
	if($partita_ID == null) echo "NULL";
	else if($partita_ID == "") echo "VUOTA";
	else echo "ALTRO";

	$partita->Note_Interne =  $note_interne;

	if($partita_ID==null)
	{
		//$partita->Cancellazione = "";
		//$partita->Comune_ID = $comune_id_partita + 1;
		/*array_push($a_paramsPartita['fields'], array(  'name' => 'Cancellazione',   'type' => 'string', 'value' => ''));
		array_push($a_paramsPartita['fields'], array(  'name' => 'Comune_ID',   'type' => 'int', 'value' => $comune_id_partita));
		array_push($a_paramsPartita['fields'], array(  'name' => 'File_1',   'type' => 'string', 'value' => $File_1));
		array_push($a_paramsPartita['fields'], array(  'name' => 'File_2',   'type' => 'string', 'value' => $File_2));*/

		$partita->Cancellazione = "";
		$partita->Comune_ID = $comune_id_partita + 1;
		

		if(is_null($partita->Is_Discharged))
			$partita->Is_Discharged = 0;
		if(is_null($partita->Is_Extracted))
			$partita->Is_Extracted = 0;
			if(is_null($partita->Is_Expired))
			$partita->Is_Expired = 0;

		$partita_ID = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $partita,"partita_tributi"));

		$partita_query = "SELECT PT.Comune_ID AS Rif_P, PT.CC, T.Info_Cartella AS Info, EG.Denominazione AS Ente, ";
		$partita_query.= "IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(Cognome,''),' ',COALESCE(Nome,''))) as Utente, U.Comune_ID AS Rif_U FROM partita_tributi AS PT ";
		$partita_query.= "LEFT JOIN tributo AS T ON PT.ID = T.Partita_ID ";
		$partita_query.= "LEFT JOIN utente AS U ON PT.Utente_ID = U.ID ";
		$partita_query.= "LEFT JOIN enti_gestiti AS EG ON PT.CC = EG.CC ";
		$partita_query.= "WHERE PT.ID = ".$partita_ID;
		
		$info = $cls_db->getResults($cls_db->ExecuteQuery($partita_query));

		/*
		$partita = $cls_partita->getDataPartita($partita_ID, $c, $a);
		$query = "SELECT Nome, Cognome, Ditta, Genere FROM utente where ID = '".$intestatario."'";
		$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
		$msg_utente = "";
		if($utente["Genere"] == "D")
			$msg_utente = $utente["Ditta"];
		else
			$msg_utente = $utente["Cognome"]." ". $utente["Nome"];
		*/

		if(!$partita_ID)
		{
			$error = 1;
			$msg = "Errore impossibile inserire i dati ".$cls_db->GetError();
			$cls_db->Rollback();
		}else{
			$storico->insRow('I',"Inserita partita ".$info_cartella."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
			$msg = "Dati inseriti con successo";
		}
		//$control_partita = $partita->Insert();
		echo "PARTITA ID --> ".$partita_ID;
	}
	else
	{
		$partita_query = "SELECT PT.Comune_ID AS Rif_P, PT.CC, T.Info_Cartella AS Info, EG.Denominazione AS Ente, ";
		$partita_query.= "IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(Cognome,''),' ',COALESCE(Nome,''))) as Utente, U.Comune_ID AS Rif_U FROM partita_tributi AS PT ";
		$partita_query.= "LEFT JOIN tributo AS T ON PT.ID = T.Partita_ID ";
		$partita_query.= "LEFT JOIN utente AS U ON PT.Utente_ID = U.ID ";
		$partita_query.= "LEFT JOIN enti_gestiti AS EG ON PT.CC = EG.CC ";
		$partita_query.= "WHERE PT.ID = ".$partita_ID;
		
		$info = $cls_db->getResults($cls_db->ExecuteQuery($partita_query));

		/*array_push($a_paramsPartita['fields'], array(  'name' => 'File_1',   'type' => 'string', 'value' => $File_1));
		array_push($a_paramsPartita['fields'], array(  'name' => 'File_2',   'type' => 'string', 'value' => $File_2));
		$a_paramsPartita['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $partita_ID);*/
		if(is_null($partita->Is_Discharged))
			$partita->Is_Discharged = 0;
		if(is_null($partita->Is_Extracted))
			$partita->Is_Extracted = 0;
			if(is_null($partita->Is_Expired))
			$partita->Is_Expired = 0;
		if(!$cls_db->DbSave($cls_utils->GetObjectQuery((array) $partita,"partita_tributi", array("ID" => $partita_ID))))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile aggiornare i dati";
		}else{
			if($cls_help->getVar('tipo_partita_f') == 't')
				$storico->insRow('U',"Modificato tipo entrata partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('ruolo_desc_f') == 't')
				$storico->insRow('U',"Modificato ruolo partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('utente_nome_f') == 't')
				$storico->insRow('U',"Modificato intestatario partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('anno_rif_f') == 't')
				$storico->insRow('U',"Modificato anno di riferimento partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('info_cartella_f') == 't')
				$storico->insRow('U',"Modificato riferimento accertamento partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('data_interessi_f') == 't')
				$storico->insRow('U',"Modificata data decorso interessi partita ".$info_cartella."(".$partita['Comune_ID']);
			//if($cls_help->getVar('select_info_f') == 't')																// non può essere modificato dopo salvataggio
				//$storico->insRow('U',"Modificato tipo sanzione partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('titolo_sanz_f') == 't')
				$storico->insRow('U',"Modificato riferimento atto partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('data_sanz_f') == 't')
				$storico->insRow('U',"Modificata data sanzione partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('targa_sanz_f') == 't')
				$storico->insRow('U',"Modificata targa partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('titolo_ent_f') == 't')
				$storico->insRow('U',"Modificato titolo entrata partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('desc_ent_f') == 't')
				$storico->insRow('U',"Modificata descrizione entrata partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('matri_f') == 't')
				$storico->insRow('U',"Modificata matricola partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('nuovo_tributo_f') != 0)
				$storico->insRow('I',"Inserito nuovo tributo partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('elab_ing_f') == 't')
				$storico->insRow('E',"Elaborata ingiunzione partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('cod_tributo_f') == 't')
				$storico->insRow('U',"Modificato codice tributo partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('anno_tributo_f') == 't')
				$storico->insRow('U',"Modificato anno tributo partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('select_atto_f') == 't')
				$storico->insRow('U',"Modificato atto tributo partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('importo_f') == 't')
				$storico->insRow('U',"Modificato importo tributo partita ".$info_cartella."(".$partita['Comune_ID']);
			if($cls_help->getVar('elimina_f') == 't')
				$storico->insRow('D',"Eliminato tributo partita ".$info_cartella."(".$partita['Comune_ID']);
			$storico->insRow('U',"Modificata partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
			$msg = "Dati aggiornati con successo";
		}
		//$control_partita = $partita->Update($partita_ID);
	}


	/*if($error === 1)
	{
		echo "ERROR PARTITA ".$partita_ID." ".mysql_error();
		mysql_query('ROLLBACK');
		die;
	}
	else if($partita_ID==null)
		$partita_ID = mysql_insert_id();*/

//var_dump($ID_Tributo);
	if($ID_Tributo == null) $numero_tributi = 0;
	else $numero_tributi = count($ID_Tributo);
echo "NUmero tributi ".$numero_tributi;
	$tot_importi = 0;
	for($y=0;$y<$numero_tributi;$y++)
	{
// 		$tot_importi+= $importo[$y];
echo "<br>DENTRO<br>";
$a_paramsTributo = array(
		'table' => 'tributo',
		'fields'=> array(
				array(  'name' => 'Partita_ID',   'type' => 'int', 'value' => $partita_ID),
				array(  'name' => 'CC',   'type' => 'string', 'value' => $c),
				array(  'name' => 'Info_Cartella',   'type' => 'string', 'value' => $info_cartella),
				array(  'name' => 'Data_Decorrenza_Interessi',   'type' => 'date', 'value' => $data_interessi),
				array(  'name' => 'Tipo_Info',   'type' => 'string', 'value' => $select_info),
				array(  'name' => 'Titolo_Entrata',   'type' => 'string', 'value' => $titolo_ent),
				array(  'name' => 'Descrizione_Entrata',   'type' => 'string', 'value' => $desc_ent),
				array(  'name' => 'Tipo_Sanzione',   'type' => 'string', 'value' => $select_atto[$y]),
				array(  'name' => 'Titolo_Sanzione',   'type' => 'string', 'value' => $titolo_sanz),
				array(  'name' => 'Data_Sanzione',   'type' => 'date', 'value' => $data_sanz),
				array(  'name' => 'Targa_Sanzione',   'type' => 'string', 'value' => $targa_sanz),
				array(  'name' => 'Matricola',   'type' => 'string', 'value' => $matri),
				array(  'name' => 'Codice_Tributo',   'type' => 'string', 'value' => $cod_tributo[$y]),
				array(  'name' => 'Anno_Tributo',   'type' => 'year', 'value' => $anno_tributo[$y]),
				array(  'name' => 'Imposta',   'type' => 'int', 'value' => $importo[$y])
		),
		'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_Tributo[$y])
);

		if(!$cls_db->DbSave($a_paramsTributo))
		{
			$error = 1;
			$msg = "Errore impossibile aggiornare i dati ".$cls_db->GetError();
			$cls_db->Rollback();
		}else $msg = "Dati aggiornati correttamente";

		/*if($control_tributo===false)
		{
			echo "ERROR TRIBUTO ".$partita_ID." ".mysql_error();
			mysql_query('ROLLBACK');
			break;
		}*/
	}

	$cod_tributo_new = $cls_help->getVar('cod_tributo_new');
	$anno_tributo_new = $cls_help->getVar('anno_tributo_new');
	$select_atto_new = $cls_help->getVar('select_atto_new')!=null?$cls_help->getVar('select_atto_new'):"";
	$importo_new = str_replace(",", ".", $cls_help->getVar('importo_new'));

	//echo "<br>cod_tributo_new ".$cod_tributo_new." importo_new ".$importo_new;

	if($cod_tributo_new!="" && $importo_new!="")
	{
// 		$tot_importi+= $importo_new;
		/*$result = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT MAX(Comune_ID) as MaxID FROM tributo WHERE CC = '".$c."'"));
		$comune_id_tributo = $result["MaxID"];//single_answer_query("SELECT MAX(Comune_ID) FROM tributo WHERE CC = '".$c."'");*/

		//$tributo = new tributo(null, $c);
		$a_paramsTributo = array(
				'table' => 'tributo',
				'fields'=> array(
						array(  'name' => 'Partita_ID',   'type' => 'int', 'value' => $partita_ID),
						array(  'name' => 'CC',   'type' => 'string', 'value' => $c),
						array(  'name' => 'Info_Cartella',   'type' => 'string', 'value' => $info_cartella),
						array(  'name' => 'Data_Decorrenza_Interessi',   'type' => 'date', 'value' => $data_interessi),
						array(  'name' => 'Tipo_Info',   'type' => 'string', 'value' => $select_info),
						array(  'name' => 'Titolo_Entrata',   'type' => 'string', 'value' => $titolo_ent),
						array(  'name' => 'Descrizione_Entrata',   'type' => 'string', 'value' => $desc_ent),
						array(  'name' => 'Tipo_Sanzione',   'type' => 'string', 'value' => $select_atto_new),
						array(  'name' => 'Titolo_Sanzione',   'type' => 'string', 'value' => $titolo_sanz),
						array(  'name' => 'Data_Sanzione',   'type' => 'date', 'value' => $data_sanz),
						array(  'name' => 'Targa_Sanzione',   'type' => 'string', 'value' => $targa_sanz),
						array(  'name' => 'Matricola',   'type' => 'string', 'value' => $matri),
						array(  'name' => 'Codice_Tributo',   'type' => 'string', 'value' => $cod_tributo_new),
						array(  'name' => 'Anno_Tributo',   'type' => 'year', 'value' => $anno_tributo_new),
						array(  'name' => 'Imposta',   'type' => 'int', 'value' => $importo_new)
				)
		);

		if(!$cls_db->DbSave($a_paramsTributo))
		{
			$error = 1;
			$msg = "Errore impossibile inserire i dati ".$cls_db->GetError();
			$cls_db->Rollback();
		}else $msg = "Dati inseriti correttamente";

	}

	$cls_db->End_Transaction();

	header("Location: gestione_partita.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");

	function crea_dir( $path )
	{
		if (!is_dir($path)) {
			$folder = explode("/",$path);

			$control_path = $folder[0];

			for($l=1;$l<count($folder);$l++)
			{
				$control_path .= "/".$folder[$l];
				if( is_dir( $control_path ) == false )
				{
					mkdir( $control_path );
				}
			}
		}
		return $path;
	}

?>
