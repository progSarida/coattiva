<?php

if (!session_id()) session_start();

if ($_SESSION['username'] == NULL) {
	header("Location:" . WEB_ROOT . "/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_math.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_storico.php";
include_once(CLS."/cls_GestionePartita.php");

$cls_partita = new cls_GP();
$storico = new storico('storicoRuolo','3');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("DB", false);
$cls_math = new cls_math();
$cls_Utils = new cls_Utils();

$invia = $cls_help->getVar('invia_submit');
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$atto_rif = $cls_help->getVar('atto_rif');

$partita_ID = $cls_help->getVar('partita');

$error = 0;
$msg = "";
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

//$partita = new partita( $partita_ID , $c , $a );

//$ing = new atto( $atto_rif , $c);
$query = "SELECT * FROM atto WHERE ID = " . $atto_rif . " AND CC = '" . $c . "'";
$ing = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "atto");

echo $query;

$Atto_ID = $ing["ID"];
//echo "<h1>aa".$query."</h1>";
$num_rate = $ing["Rate_Previste"];
$importi_rate = explode("*", $ing["Importi_Rate"]);
$dovuto = $ing["Totale_Dovuto"];
$tipo_atto = $ing["Atto"];
$rif_atto = $ing["Riferimento"];
$documentTypeId = $ing["DocumentTypeId"];

$telematico = $cls_help->getVar('telematico');
//$cls_help->alert($cls_help->getVar('importo'));
$importo = number_format(str_replace(",", ".", $cls_help->getVar('importo')), 2, ".", "");
//$cls_help->alert($importo);
$quietanza = $cls_help->getVar('quietanza');
$bollettario = $cls_help->getVar('bollettario');
$data_pagamento = $cls_date->GetDateDB($cls_help->getVar('data_pag'), "IT");
$modalita = $cls_help->getVar('tipo');
$c_terzi = $cls_help->getVar('terzi');

if ($c_terzi != "Y")
	$c_terzi = "N";

$note = $cls_help->getVar('note');
$pagante = $cls_help->getVar('pagante');
$rata = $cls_help->getVar('num_rata');


$data_pag = $cls_help->getVar('data_pag');

$c_terzi = $cls_help->getVar('terzi');


$atto_rif_2 = "";
$pagante_2 = "";

$data_pag_2 = "";


$c_terzi_1 = "";
$c_terzi_2 = "";

$modalita_2 = "";

$importo_1 = "";
$importo_2 = "";

$quietanza_2 = "";

$bollettario_2 = "";

$rata_2 = "";

$telematico_2 = "";

$note_2 = "";

$img_bollettino_2 = "";

$update_data = "N";

if ($invia == "Update")
{
	$atto_rif_2 = $cls_help->getVar('atto_rif_2');
	$pagante_2 = $cls_help->getVar('pagante_2');

	$data_pag_2 = $cls_help->getVar('data_pag_2');


	$c_terzi_1 = (is_null($c_terzi) ? "": $c_terzi);
	$c_terzi_2 = $cls_help->getVar('terzi_2');

	$modalita_2 = $cls_help->getVar('tipo_g');

	$importo_1 = $cls_help->getVar('importo');
	$importo_2 = $cls_help->getVar('importo_2');

	$quietanza_2 = $cls_help->getVar('quietanza_2');

	$bollettario_2 = $cls_help->getVar('bollettario_2');

	$rata_2 = $cls_help->getVar('num_rata_2');

	$telematico_2 = $cls_help->getVar('telematico_2');

	$note_2 = $cls_help->getVar('note_2');

	$img_bollettino_2 = $cls_help->getVar('img_bollettino_2');

	if (
		($atto_rif_2 !== $atto_rif) ||
		($pagante_2 !== trim($pagante, ' ')) ||
		($data_pag_2 !== $data_pag) ||
		($c_terzi_2 !== $c_terzi_1) ||
		($modalita_2 !== $modalita) ||
		($importo_2 !== $importo_1) ||
		($quietanza_2 !== trim($quietanza, ' ')) ||
		($bollettario_2 !== trim($bollettario, ' ')) ||
		($rata_2 !== trim($rata,' ')) ||
		($telematico_2 !== $telematico) ||
		($note_2 !== trim($note,' ')) ||
		($img_bollettino_2 === 'Y')
	) {
		$update_data = "Y";
	}
}
//echo ATTI ."/". $c . "/Pagamenti";

$pagamento_dir = $cls_Utils->crea_dir(ATTI . "/" . $c . "/Pagamenti");

if (isset($_FILES['img_bollettino']) && $_FILES['img_bollettino']['size'] > 0) {
	$percorso_file = $_FILES['img_bollettino']['tmp_name'];
	$nome_file = $_FILES['img_bollettino']['name'];
} else {
	$percorso_file = "";
	$nome_file = "";
}
//echo "<h1>".$nome_file."</h1>";
$estensione_array = explode(".", $nome_file);
$estensione = $estensione_array[count($estensione_array) - 1];
$nome_file_senza_estensione = substr($nome_file, 0, strlen($nome_file) - strlen($estensione) - 1);
//echo "<h1>".$percorso_file." ".$nome_file_senza_estensione."</h1>";
$file_pagamento = $pagamento_dir . "/" . $nome_file_senza_estensione;
//echo "<h1>".$file_pagamento."</h1>";

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

switch ($invia) {
	case "Insert":

		$data_registrazione = date('Y-m-d');

		//	mysql_query('BEGIN');

		$query = "SELECT MAX(Comune_ID) as ID FROM pagamento WHERE CC = '" . $c . "'";
		$comune_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["ID"];

		//$salva = new pagamento(null, $c);

		//$salva->CC = $c;
		/*$salva->Comune_ID = $comune_id + 1;
			$salva->Partita_ID = $partita_ID;
			$salva->Atto_ID = $Atto_ID;
		//	$salva->Riferimento_Atto = $rif_atto;
			$salva->Tipo_Atto = $tipo_atto;
			$salva->Data_Registrazione = $data_registrazione;
		//$salva->Importo = $importo;
			$salva->Data_Pagamento = $data_pagamento;
			$salva->Telematico = $telematico;
			$salva->Modalita = $modalita;
			$salva->Conto_Terzi = $c_terzi;
			$salva->pagante = $pagante;
			//$salva->Note = $note;
			$salva->Quietanza = $quietanza;
			$salva->Bollettario = $bollettario;
			$salva->Tipo_Pagamento = "MANUALE";
			$salva->Data_Travaso_A_Gitco = "0000-00-00";
			$salva->DocumentTypeId = $documentTypeId;
			$salva->DocumentTableTypeId = 1;*/



		$a_paramsPagamento = array(
			'table' => 'pagamento',
			'fields' => array(
				array('name' => 'CC',   'type' => 'string', 'value' => $c),
				array('name' => 'Comune_ID',   'type' => 'int', 'value' => $comune_id + 1),
				array('name' => 'Partita_ID',   'type' => 'int', 'value' => $partita_ID),
				array('name' => 'Atto_ID',   'type' => 'int', 'value' => $Atto_ID),
				array('name' => 'Riferimento_Atto',   'type' => 'int', 'value' => $rif_atto),
				array('name' => 'Tipo_Atto',   'type' => 'string', 'value' => $tipo_atto),
				array('name' => 'Data_Registrazione',   'type' => 'date', 'value' => $data_registrazione),
				array('name' => 'Importo',   'type' => 'int', 'value' => $importo),
				array('name' => 'Data_Pagamento',   'type' => 'date', 'value' => $data_pagamento),
				array('name' => 'Telematico',   'type' => 'string', 'value' => $telematico),
				array('name' => 'Modalita',   'type' => 'string', 'value' => $modalita),
				array('name' => 'Conto_Terzi',   'type' => 'string', 'value' => $c_terzi),
				array('name' => 'pagante',   'type' => 'string', 'value' => $pagante),
				array('name' => 'Note',   'type' => 'string', 'value' => $note),
				array('name' => 'Quietanza',   'type' => 'string', 'value' => $quietanza),
				array('name' => 'Bollettario',   'type' => 'string', 'value' => $bollettario),
				array('name' => 'Tipo_Pagamento',   'type' => 'string', 'value' => "MANUALE"),
				array('name' => 'Data_Travaso_A_Gitco',   'type' => 'date', 'value' => null),
				array('name' => 'DocumentTypeId',   'type' => 'int', 'value' => $documentTypeId),
				array('name' => 'DocumentTableTypeId',   'type' => 'int', 'value' => "1")				
			)
		);
		

		if ($percorso_file != "") {
			$im = new imagick($percorso_file);

			$im->setImageCompression(Imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(10);
			$im->writeImage($file_pagamento . '.jpg');

			//$salva->Bollettino = $nome_file_senza_estensione.'.jpg';
			array_push($a_paramsPagamento['fields'], array('name' => 'Bollettino',   'type' => 'string', 'value' => $nome_file_senza_estensione . '.jpg'));
		}

		if ($num_rate != 0) {
			//$salva->Rata = $rata;
			//$salva->Totale_Rate = $num_rate;
			array_push($a_paramsPagamento['fields'], array('name' => 'Rata',   'type' => 'int', 'value' => $rata));
			array_push($a_paramsPagamento['fields'], array('name' => 'Totale_Rate',   'type' => 'int', 'value' => $num_rate));
			//$cls_help->alert($importi_rate[$rata-1]);
			$dovuto = number_format(str_replace(",", ".", $importi_rate[$rata - 1]), 2, ".", "");
			//$cls_help->alert($dovuto);
			//die;
		} else {
			if ($rata == null) {
				$query = "SELECT MAX(Rata) as MaxR FROM pagamento WHERE CC = '" . $c . "' AND Partita_ID = '" . $partita_ID . "'";
				$numero_rata = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["MaxR"];
				//$salva->Rata = $numero_rata+1;
				array_push($a_paramsPagamento['fields'], array('name' => 'Rata',   'type' => 'int', 'value' => $numero_rata + 1));
			} else
				array_push($a_paramsPagamento['fields'], array('name' => 'Rata',   'type' => 'int', 'value' => $rata));
			//$salva->Rata = $rata;
		}
		array_push($a_paramsPagamento['fields'], array('name' => 'Dovuto',   'type' => 'int', 'value' => $dovuto));
		//$salva->Dovuto = $dovuto;

		if (!$cls_db->DbSave($a_paramsPagamento)) {
			$error = 1;
			$msg = "Errore impossibile inserire i dati \n" . $cls_db->GetError();
			$cls_db->Rollback();
		} else{
            $storico->insRow('I', "Inserito/i pagamento/i partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
            $msg = "Dati inseriti correttamente";
        }

		$cls_db->End_Transaction();
		header("Location: pagamento.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
		die;
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

		$id_pag = $cls_help->getVar('id_pagamento');

		//mysql_query('BEGIN');

		$query = "SELECT MAX(Comune_ID) as MaxID FROM pagamento WHERE CC = '" . $c . "'";
		$comune_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["MaxID"];

		/*$salva = new pagamento(null, $c);

			$salva->CC = $c;
			$salva->Comune_ID = $comune_id + 1;
			$salva->Partita_ID = $partita_ID;
			$salva->Atto_ID = $Atto_ID;
			$salva->Riferimento_Atto = $rif_atto;
			$salva->Tipo_Atto = $tipo_atto;
			$salva->Importo = $importo;
			$salva->Data_Pagamento = $data_pagamento;
			$salva->Telematico = $telematico;
			$salva->Modalita = $modalita;
			$salva->Conto_Terzi = $c_terzi;
			$salva->pagante = $pagante;
			$salva->Note = $note;
			$salva->Quietanza = $quietanza;
			$salva->Bollettario = $bollettario;
			$salva->Tipo_Pagamento = "MANUALE";
			$salva->Data_Travaso_A_Gitco = "0000-00-00";
            $salva->DocumentTypeId = $documentTypeId;
            $salva->DocumentTableTypeId = 1;*/

		//echo $importo;
		//die;
		$a_paramsPagamento = array(
			'table' => 'pagamento',
			'fields' => array(
				array('name' => 'CC',   'type' => 'string', 'value' => $c),
				array('name' => 'Comune_ID',   'type' => 'int', 'value' => $comune_id + 1),
				array('name' => 'Partita_ID',   'type' => 'int', 'value' => $partita_ID),
				array('name' => 'Atto_ID',   'type' => 'int', 'value' => $Atto_ID),
				array('name' => 'Riferimento_Atto',   'type' => 'int', 'value' => $rif_atto),
				array('name' => 'Tipo_Atto',   'type' => 'string', 'value' => $tipo_atto),
				array('name' => 'Importo',   'type' => 'int', 'value' => $importo),
				array('name' => 'Data_Pagamento',   'type' => 'date', 'value' => $data_pagamento),
				array('name' => 'Telematico',   'type' => 'string', 'value' => $telematico),
				array('name' => 'Modalita',   'type' => 'string', 'value' => $modalita),
				array('name' => 'Conto_Terzi',   'type' => 'string', 'value' => $c_terzi),
				array('name' => 'pagante',   'type' => 'string', 'value' => $pagante),
				array('name' => 'Note',   'type' => 'string', 'value' => $note),
				array('name' => 'Quietanza',   'type' => 'string', 'value' => $quietanza),
				array('name' => 'Bollettario',   'type' => 'string', 'value' => $bollettario),
				array('name' => 'Tipo_Pagamento',   'type' => 'string', 'value' => "MANUALE"),
				array('name' => 'Data_Travaso_A_Gitco',   'type' => 'date', 'value' => null),
				array('name' => 'DocumentTypeId',   'type' => 'int', 'value' => $documentTypeId),
				array('name' => 'DocumentTableTypeId',   'type' => 'int', 'value' => "1")
			),
			'updateField' => array('name' => 'ID',   'type' => 'int', 'value' => $id_pag)
		);

		if ($percorso_file != "") {
			$im = new imagick($percorso_file);

			$im->setImageCompression(Imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(10);
			$im->writeImage($file_pagamento . '.jpg');

			//$salva->Bollettino = $nome_file_senza_estensione.'.jpg';
			array_push($a_paramsPagamento['fields'], array('name' => 'Bollettino',   'type' => 'string', 'value' => $nome_file_senza_estensione . '.jpg'));
		}

		if ($num_rate != 0) {
			//$salva->Rata = $rata;
			//$salva->Totale_Rate = $num_rate;
			array_push($a_paramsPagamento['fields'], array('name' => 'Rata',   'type' => 'int', 'value' => $rata));
			array_push($a_paramsPagamento['fields'], array('name' => 'Totale_Rate',   'type' => 'int', 'value' => $num_rate));

			//$cls_help->alert($importi_rate[$rata-1]);
			$dovuto = number_format(str_replace(",", ".", $importi_rate[$rata - 1]), 2, ".", "");
			//$cls_help->alert($dovuto);
			//die;
		} else {
			if ($rata == null) {
				$query = "SELECT MAX(Rata) as MaxRata FROM pagamento WHERE CC = '" . $c . "' AND Partita_ID = '" . $partita_ID . "'";
				$numero_rata = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["MaxRata"];
				//$salva->Rata = $numero_rata+1;
				array_push($a_paramsPagamento['fields'], array('name' => 'Rata',   'type' => 'int', 'value' => $numero_rata + 1));
			} else
				array_push($a_paramsPagamento['fields'], array('name' => 'Rata',   'type' => 'int', 'value' => $rata));
			//$salva->Rata = $rata;
		}

		//$salva->Dovuto = $dovuto;
		array_push($a_paramsPagamento['fields'], array('name' => 'Dovuto',   'type' => 'int', 'value' => $dovuto));
		/*
		if ($update_data == 'Y')
		{
			$Data_Update = $cls_date->GetDateDB( date("d/m/Y"), "IT");
			array_push($a_paramsPagamento['fields'], array('name' => 'Data_Update',   'type' => 'date', 'value' => $Data_Update));			
		}
		*/
		if (!$cls_db->DbSave($a_paramsPagamento)) {
			$error = 1;
			$msg = "Errore impossibile aggiornare i dati \n" . $cls_db->GetError();
			$cls_db->Rollback();
		} else{
            $storico->insRow('U', "Modificato/i pagamento/i partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
            $msg = "Dati aggiornati correttamente";
        }

		$cls_db->End_Transaction();
		header("Location: pagamento.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
		die;


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

		$id_pag = $cls_help->getVar('id_pagamento');

		//$cls_help->alert($id_pag);

		if (!$cls_db->Delete("pagamento", "ID = " . $id_pag)) {
			$error = 1;
			$msg = "Errore impossibile eliminare i dati. " . $cls_db->GetError();
			$cls_db->Rollback();
		} else{
            $storico->insRow('D', "Eliminato/i pagamento/i partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
            $msg = "Dati eliminati correttamente";
        }

		$cls_db->End_Transaction();
		header("Location: pagamento.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");

		/*$cancella = new pagamento( $id_pag , $c );
			$cancella->Delete();

			echo 'DELETE '.$partita_ID.' '.$id_pag;*/

		break;
}
