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
	include_once(CLS."/cls_CoazioneUtils.php");
	include_once(CLS."/cls_Utils.php");
	include_once(CLS."/cls_math.php");
	include_once(CLS."/pdf_con_bollettino.php");
	include_once CLS . "/cls_storico.php";
	include_once(CLS."/cls_GestionePartita.php");

	$cls_partita = new cls_GP();
	$storico = new storico('storicoRuolo','3');
	$cls_coazione = new cls_Coazione();
	$cls_db = new cls_db();
	$cls_help = new cls_help();
	$cls_date = new cls_DateTimeI("DB",false);
	$cls_Utils = new cls_Utils();
	$cls_math = new cls_math();


	$error = 0;
	$msg = "";

	$invia = $cls_help->getVar('invia_submit');
	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$atto_rif = $cls_help->getVar('atto_rif');

	$partita_ID = $cls_help->getVar('partita');

	$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

	//echo "<h1>Partita: ".$partita_ID."</h1>";

	//$partita = new partita( $partita_ID , $c , $a );

	//$ing = new pignoramento( $atto_rif , $c);

	$query = "SELECT * FROM pignoramento_generale WHERE ID = '".$atto_rif."' AND CC = '".$c."'";
	$ing = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");

	$Atto_ID = $ing["ID"];
	$num_rate = $ing["Rate_Previste"];
	$importi_rate = explode("*",$ing['Importi_Rate']);
	$dovuto = $ing["Totale_Dovuto"];
  $documentTypeId = $ing["DocumentTypeId"];
	$tipo_atto = "Pignoramento";

	$importo = $cls_math->conv_num($cls_help->getVar('importo'));
	$quietanza = $cls_help->getVar('quietanza');
	$bollettario = $cls_help->getVar('bollettario');
	$data_pagamento = $cls_date->GetDateDB($cls_help->getVar('data_pag'),"IT");
	$telematico = $cls_help->getVar('telematico');
	$modalita = $cls_help->getVar('tipo');
	$c_terzi = $cls_help->getVar('terzi');

	if($c_terzi!="Y")
		$c_terzi = "N";

	$note = $cls_help->getVar('note');
	$pagante = $cls_help->getVar('pagante');
	$rata = $cls_help->getVar('num_rata');

	$pagamento_dir = $cls_Utils->crea_dir( ATTI."/". $c . "/Pagamenti" );

	if(isset($_FILES['img_bollettino']) && $_FILES['img_bollettino']['size'] > 0)
	{
		$percorso_file = $_FILES['img_bollettino']['tmp_name'];
		$nome_file = $_FILES['img_bollettino']['name'];
	}
	else
	{
		$percorso_file = "";
		$nome_file = "";
	}

	$estensione_array = explode(".", $nome_file);
	$estensione = $estensione_array[count($estensione_array)-1];
	$nome_file_senza_estensione = substr($nome_file,0,strlen($nome_file)-strlen($estensione)-1);

	$file_pagamento = $pagamento_dir."/".$nome_file_senza_estensione;

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	// Recupero dati partita e utente
	$partita_query = "SELECT PT.Comune_ID AS Rif_P, PT.CC, T.Info_Cartella AS Info, EG.Denominazione AS Ente, ";
	$partita_query.= "IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(Cognome,''),' ',COALESCE(Nome,''))) as Utente, U.Comune_ID AS Rif_U FROM partita_tributi AS PT ";
	$partita_query.= "LEFT JOIN tributo AS T ON PT.ID = T.Partita_ID ";
	$partita_query.= "LEFT JOIN utente AS U ON PT.Utente_ID = U.ID ";
	$partita_query.= "LEFT JOIN enti_gestiti AS EG ON PT.CC = EG.CC ";
	$partita_query.= "WHERE PT.ID = ".$partita_ID;
	
	$info = $cls_db->getResults($cls_db->ExecuteQuery($partita_query));


	switch($invia)
	{
		case "Insert":

			$data_registrazione = date('Y-m-d');

		//	mysql_query('BEGIN');

			$query = "SELECT MAX(Comune_ID) as CI FROM pagamento WHERE CC = '".$c."'";
			$comune_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["CI"];

			//$salva = new pagamento(null, $c);
			//$query = "SELECT * FROM pagamento WHERE ID = 'null' AND CC = 'null'";
			//$salva = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pagamento");

			$salva = array();

			$salva["CC"] = $c;
			$salva["Comune_ID"] = $comune_id + 1;
			$salva["Partita_ID"] = $partita_ID;
			$salva["Atto_ID"] = $Atto_ID;
			$salva["Tipo_Atto"] = $tipo_atto;
			$salva["Data_Registrazione"] = $data_registrazione;
			$salva["Importo"] = $importo;
			$salva["Data_Pagamento"] = $data_pagamento;
			$salva["Telematico"] = $telematico;
			$salva["Modalita"] = $modalita;
			$salva["Conto_Terzi"] = $c_terzi;
			$salva["Pagante"] = $pagante;
			$salva["Note"] = $note;
			$salva["Quietanza"] = $quietanza;
			$salva["Bollettario"] = $bollettario;
			$salva["Tipo_Pagamento"] = "MANUALE";
			$salva["Data_Travaso_A_Gitco"] = null;
    		$salva["DocumentTypeId"] = $documentTypeId;
      		$salva["DocumentTableTypeId"] = 2;

			if($percorso_file != "")
			{
				$im = new imagick( $percorso_file );

				$im->setImageCompression(Imagick::COMPRESSION_JPEG);
				$im->setImageCompressionQuality(10);
				$im->writeImage( $file_pagamento.'.jpg' );

				$salva["Bollettino"] = $nome_file_senza_estensione.'.jpg';
			}

			if($num_rate>0)
			{
				$salva["Rata"] = $rata;
				$salva["Totale_Rate"] = $num_rate;
				$dovuto = number_format($importi_rate[$rata-1],2,".","");
			}
			else {
				if($rata==null){
					$query = "SELECT MAX(Rata) as RT FROM pagamento WHERE CC = '".$c."' AND Partita_ID = '".$partita_ID."'";
					$numero_rata = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["RT"];
					$salva["Rata"] = $numero_rata+1;
				}
				else
					$salva["Rata"] = $rata;
			}

			$salva["Dovuto"] = $dovuto;

			$paramsPag = $cls_Utils->GetObjectQuery($salva,"pagamento");

		//print_r($paramsPag );

			$id_pagamento = $cls_db->DbSave($paramsPag);// forse $partita_ID = ... , non $id_pagamento
			if(!$id_pagamento)
			{
				$error = 1;
				$msg = "Errore, inserimento dati fallito. ".$cls_db->GetError();
				$cls_db->Rollback();
			} else{
				$storico->insRow('I', "Inserito/i pagamento/i partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
				$msg = "Inserimento dati riuscito correttamente";
			}

			/*$control_salva = $salva->Insert(true);

			if($control_salva)
			{
				$id_pagamento = mysql_insert_id();
				mysql_query('COMMIT');


				echo 'OK '.$partita_ID.' '.$id_pagamento;
			}
			else
			{
				mysql_query('ROLLBACK');

				echo 'ERRORNUOVO '.$partita_ID;
			}*/

		break;

		case "Update":

			$id_pagamento = $cls_help->getVar('id_pagamento');

			//mysql_query('BEGIN');

			$query = "SELECT MAX(Comune_ID) as CI FROM pagamento WHERE CC = '".$c."'";
			$comune_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["CI"];

			//$salva = new pagamento(null, $c);
			$query = "SELECT * FROM pagamento WHERE ID = 'null' AND CC = 'null'";
			$salva = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pagamento");

			unset($salva["ID"]);
			$salva["CC"] = $c;
			$salva["Comune_ID"] = $comune_id + 1;
			$salva["Partita_ID"] = $partita_ID;
			$salva["Atto_ID"] = $Atto_ID;
			$salva["Tipo_Atto"] = $tipo_atto;
			$salva["Importo"] = $importo;
			$salva["Data_Pagamento"] = $data_pagamento;
			$salva["Modalita"] = $modalita;
			$salva["Telematico"] = $telematico;
			$salva["Conto_Terzi"] = $c_terzi;
			$salva["pagante"] = $pagante;
			$salva["Note"] = $note;
			$salva["Quietanza"] = $quietanza;
			$salva["Bollettario"] = $bollettario;
			$salva["Tipo_Pagamento"] = "MANUALE";
			$salva["Data_Travaso_A_Gitco"] = null;
      $salva["DocumentTypeId"] = $documentTypeId;
      $salva["DocumentTableTypeId"] = 2;

			if($percorso_file != "")
			{
				$im = new imagick( $percorso_file );

				$im->setImageCompression(Imagick::COMPRESSION_JPEG);
				$im->setImageCompressionQuality(10);
				$im->writeImage( $file_pagamento.'.jpg' );

				$salva["Bollettino"] = $nome_file_senza_estensione.'.jpg';
			}

			if($num_rate>0)
			{
				$salva["Rata"] = $rata;
				$salva["Totale_Rate"] = $num_rate;
				$dovuto = number_format($importi_rate[$rata-1],2,".","");
			}
			else {
				if($rata==null){
					$query = "SELECT MAX(Rata) as RT FROM pagamento WHERE CC = '".$c."' AND Partita_ID = '".$partita_ID."'";
					$numero_rata = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["RT"];
					$salva["Rata"] = $numero_rata+1;
				}
				else
					$salva["Rata"] = $rata;
			}

			$salva["Dovuto"] = $dovuto;

			$paramsPag = $cls_Utils->GetObjectQuery($salva,"pagamento",array("ID" => $id_pagamento));

			//print_r($paramsPag );

			if(!$cls_db->DbSave($paramsPag))
			{
				$error = 1;
				$msg = "Errore, aggiornamento dati fallito. ".$cls_db->GetError();
				$cls_db->Rollback();
			} else{
				$storico->insRow('U', "Modificato/i pagamento/i partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
				$msg = "Aggiornamento dati riuscito correttamente";
			}

			/*$control_update = $salva->Update( $id_pag , true );

			if($control_update)
			{
				$id_pagamento = mysql_insert_id();
				mysql_query('COMMIT');

				echo 'OK '.$partita_ID;
			}
			else
			{
				mysql_query('ROLLBACK');

				echo 'ERROR '.$partita_ID;
			}*/

		break;

		case "Delete":

			/*

			$cancella = new pagamento( $id_pag , $c );
			$cancella->Delete();*/
			$id_pag = $cls_help->getVar('id_pagamento');
			if(!$cls_db->Delete("pagamento","ID = '".$id_pag."'"))
			{
				$error = 1;
				$msg = "Errore, impossibile eliminare i dati. ".$cls_db->GetError();
				$cls_db->Rollback();
			}else{
				$storico->insRow('D', "Eliminato/i pagamento/i partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
				$msg = "Dati eliminati correttamente.";
			}

			//echo 'DELETE '.$partita_ID.' '.$id_pag;
			$cls_db->End_Transaction();
			header("Location: pagamento_pignoramento.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			die;
		break;

	}

	$cls_db->End_Transaction();
//	echo "<h1>EnsP: ".$partita_ID."</h1>";
	header("Location: pagamento_pignoramento.php?partita={$partita_ID}&c={$c}&a={$a}&p={$id_pagamento}&error={$error}&msg={$msg}");

?>
