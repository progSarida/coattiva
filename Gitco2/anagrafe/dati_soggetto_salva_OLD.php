<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("DB", false);

$invia = $cls_help->getVar('invia_submit');
$ID = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$servizio = $cls_help->getVar('servizio');
$comune_id = $cls_help->getVar('comune_id');
$old_pec = $cls_help->getVar('pec_old_utente');

$error = 0;
$msg = "";
$new_ID_utente = 0;
//echo "<h1>ID : ".$ID." ----- CC: ".$c."</h1>";
//DELETE
if ($invia == "Delete") {
	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if (!$cls_db->Delete("utente", "ID = '" . $ID . "' AND CC_Comune ='" . $c . "' AND Comune_ID ='" . $comune_id . "'")) {
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore impossibile eliminare i dati.";
	} else $msg = "Dati eliminati con successo.";
}

if ($invia = "Salva") {
	//DATI SOGGETTO

	$data_registrazione = $cls_help->getVar('data_registrazione');
	$data_registrazione = $cls_date->GetDateDB($data_registrazione, "IT");

	$genere = $cls_help->getVar('genere');

	if ($genere == 'D') {
		$ditta = $cls_help->getVar('ditta');
		$forma_giuridica = $cls_help->getVar('forma_giuridica');
		$PI = $cls_help->getVar('PI');
		$prec_den_ditta = $cls_help->getVar('prec_den');
		$anno_cambio_ditta = $cls_help->getVar('anno_cambio');
		$ulteriori_dati = $cls_help->getVar('ult_dati');
		$azienda = $cls_help->getVar('azienda');

		/******************	FORSE VA QUESTO E NON QUELLO SOTTO ********************
		 * $cognome_utente = $cls_help->getVar('cognome_cf');
			$nome_utente = $cls_help->getVar('nome_cf');
			$CC_nascita = "";
			$paese_nascita = $cls_help->getVar('paese_cf');
			$comune_nascita = $cls_help->getVar('comune_cf');
			$provincia_nascita = "";
			$data_nascita = $cls_date->GetDateDB($cls_help->getVar('data_cf'),"IT");
			$data_morte = null;
			$CF = $cls_help->getVar('CF_ditta');*/

		$cognome_utente = $cls_help->getVar('cognome_cf');
		$nome_utente = $cls_help->getVar('nome_cf');
		$CC_nascita = "";
		$paese_nascita = "";
		$comune_nascita = "";
		$provincia_nascita = "";
		$data_nascita = null;
		$data_morte = null;
		$CF = $cls_help->getVar('CF_ditta');
	} else {
		$cognome_utente = $cls_help->getVar('cognome_utente');
		$nome_utente = $cls_help->getVar('nome_utente');

		$CC_nascita = $cls_help->getVar('CC_nascita');
		$paese_nascita = $cls_help->getVar('paese_nascita');
		$comune_nascita = $cls_help->getVar('comune_nascita');
		$provincia_nascita = $cls_help->getVar('provNascDatiSogg');
		$data_nascita = $cls_help->getVar('data_nascita');
		$data_nascita = $cls_date->GetDateDB($data_nascita, "IT");
		$data_morte = $cls_help->getVar('data_morte');
		$data_morte = $cls_date->GetDateDB($data_morte, "IT");
		$CF = $cls_help->getVar('CF');

		$ditta = "";
		$PI = $cls_help->getVar('PI_persona');
		$forma_giuridica = $cls_help->getVar('forma_giuridica_persona');

		$azienda = "";
		$prec_den_ditta = "";
		$anno_cambio_ditta = "";
		$ulteriori_dati = "";
	}

	$cell_utente = $cls_help->getVar('cell_utente');
	$mail_utente = $cls_help->getVar('mail_utente');
	$pec_utente = $cls_help->getVar('pec_utente');

	//CREAZIONE ARRAY CAMPI $field_utente E VALORI $value_utente PER LA TABELLA utente

	$a_paramsUtente = array(
		'table' => 'utente',
		'fields' => array(
			array('name' => 'CC_Comune',             'type' => 'string', 'value' => $c),
			array('name' => 'Genere',                'type' => 'string', 'value' => $genere),
			array('name' => 'Cognome',               'type' => 'string', 'value' => $cognome_utente),
			array('name' => 'Nome',                  'type' => 'string', 'value' => $nome_utente),
			array('name' => 'CC_Nascita',            'type' => 'string', 'value' => $CC_nascita),
			array('name' => 'Paese_Nascita',         'type' => 'string', 'value' => $paese_nascita),
			array('name' => 'Comune_Nascita',        'type' => 'string', 'value' => $comune_nascita),
			array('name' => 'Provincia_Nascita',     'type' => 'string', 'value' => $provincia_nascita),
			array('name' => 'Data_Nascita',          'type' => 'date',   'value' => $data_nascita),
			array('name' => 'Data_Morte',            'type' => 'date',   'value' => $data_morte),
			array('name' => 'Codice_Fiscale',        'type' => 'string', 'value' => $CF),
			array('name' => 'Ditta',                 'type' => 'string', 'value' => $ditta),
			array('name' => 'Forma_Giuridica',       'type' => 'int',    'value' => $forma_giuridica),
			array('name' => 'Partita_Iva',           'type' => 'string', 'value' => $PI),
			array('name' => 'Azienda',               'type' => 'string', 'value' => $azienda),
			array('name' => 'Prec_Denom',            'type' => 'string', 'value' => $prec_den_ditta),
			array('name' => 'Anno_Cambio_Denom',     'type' => 'int',    'value' => $anno_cambio_ditta),
			array('name' => 'Cellulare',             'type' => 'string', 'value' => $cell_utente),
			array('name' => 'Mail',                  'type' => 'string', 'value' => $mail_utente),
			array('name' => 'PEC',                   'type' => 'string', 'value' => $pec_utente),
			array('name' => 'Data_Registrazione',    'type' => 'date',   'value' => $data_registrazione)
		)
	);

	if ($comune_id != null) {
		array_push($a_paramsUtente["fields"], array('name' => 'Comune_ID', 'type' => 'int', 'value' => $comune_id));
	} else {
		array_push($a_paramsUtente["fields"], array('name' => 'Comune_ID', 'type' => 'int', 'value' => "0"));
	}

	//INDIRIZZO RESIDENZA
	$ID_res = $cls_help->getVar('ID_res');
	$ID_via = $cls_help->getVar('ID_via');
	$ID_via_cap = $cls_help->getVar('ID_via_cap');
	$via_residenza = $cls_help->getVar('via_residenza');
	$CC_residenza = $cls_help->getVar('CC_residenza');
	$paese_residenza = $cls_help->getVar('paese_residenza');
	$comune_residenza = $cls_help->getVar('comune_residenza');
	$provincia_residenza = $cls_help->getVar('provDatiSogg');
	$frazione_residenza = $cls_help->getVar('frazione_residenza');
	$cap_residenza = $cls_help->getVar('cap_residenza');

	//$via_residenza = $cls_help->getVar('via_residenza');
	$via_estera_residenza = $cls_help->getVar('via_estera_residenza');

	if ($via_residenza == "") $via_residenza = $via_estera_residenza;

	$civico_residenza = $cls_help->getVar('civico_residenza');
	$esponente_residenza = $cls_help->getVar('esponente_residenza');
	$interno_residenza = $cls_help->getVar('interno_residenza');
	$dettagli_residenza = $cls_help->getVar('dettagli_residenza');
	$tel_residenza = $cls_help->getVar('tel_residenza');
	$fax_residenza = $cls_help->getVar('fax_residenza');
	$data_inizio_residenza = $cls_help->getVar('data_res');
	$data_inizio_residenza = $cls_date->GetDateDB($data_inizio_residenza, "IT");

	//CREAZIONE ARRAY CAMPI $field_residenza E VALORI $value_residenza PER LA TABELLA indirizzo



	$a_paramsToponimo = array(
		'table' => 'toponimo',
		'fields' => array(
			array('name' => 'CC_Comune',       'type' => 'string', 'value' => $c),
			array('name' => 'Nome',            'type' => 'string', 'value' => $via_residenza),
			array('name' => 'CC_Toponimo',     'type' => 'string', 'value' => $CC_residenza),
			array('name' => 'Paese',           'type' => 'string', 'value' => $paese_residenza),
			array('name' => 'Comune',          'type' => 'string', 'value' => $comune_residenza),
			array('name' => 'Cap',             'type' => 'string', 'value' => $cap_residenza)
		)
	);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	$new_ID_via = -1;
	if ($ID_via_cap > 1) {
		$new_ID_via = 1;
	} else {
		if ($ID_via == 0) {
			$new_ID_via = $cls_db->DbSave($a_paramsToponimo);
			if (!$new_ID_via) {
				$cls_db->Rollback();
				$msg = "Errore, inserimento fallito.";
				$error = 1;
			} else $msg = "Dati inseriti correttamente.";

			$ID_via = $new_ID_via;
		} else if ($ID_via != 1) {
			$a_paramsToponimo['updateField'] = array('name' => 'ID', 'type' => 'int', 'value' => $ID_via);

			if (!$cls_db->DbSave($a_paramsToponimo)) {
				$cls_db->Rollback();
				$msg = "Errore, inserimento fallito.";
				$error = 1;
			} else $msg = "Dati inseriti correttamente.";

			$new_ID_via = $ID_via;
		} else $new_ID_via = $ID_via;
	}


	//echo "<h1>Invia --> ".$invia."</h1>";

	$a_paramsIndirizzo = array(
		'table' => 'indirizzo',
		'fields' => array(
			array('name' => 'Utente_ID',             'type' => 'int',    'value' => $ID),
			array('name' => 'Via_ID',                'type' => 'int',    'value' => $new_ID_via),
			array('name' => 'Via_Cap_ID',            'type' => 'int',    'value' => $ID_via_cap),
			array('name' => 'Tipo',                  'type' => 'string', 'value' => 'res'),
			array('name' => 'CC_Indirizzo',          'type' => 'string', 'value' => $CC_residenza),
			array('name' => 'Paese',                 'type' => 'string', 'value' => $paese_residenza),
			array('name' => 'Comune',                'type' => 'string', 'value' => $comune_residenza),
			array('name' => 'Provincia',             'type' => 'string', 'value' => $provincia_residenza),
			array('name' => 'Frazione',              'type' => 'string', 'value' => $frazione_residenza),
			array('name' => 'Dettagli',              'type' => 'string', 'value' => $dettagli_residenza),
			array('name' => 'Cap',                   'type' => 'string', 'value' => $cap_residenza),
			array('name' => 'Telefono',              'type' => 'string', 'value' => $tel_residenza),
			array('name' => 'Fax',                   'type' => 'string', 'value' => $fax_residenza),
			array('name' => 'Data_Inizio_Residenza', 'type' => 'date',   'value' => $data_inizio_residenza),
			array('name' => 'Esponente',             'type' => 'string', 'value' => $esponente_residenza)
		)
	);

	if ($civico_residenza != null) {
		array_push($a_paramsIndirizzo["fields"], array('name' => 'Civico', 'type' => 'int', 'value' => $civico_residenza));
	}
	if ($interno_residenza != null) {
		array_push($a_paramsIndirizzo["fields"], array('name' => 'Interno', 'type' => 'int', 'value' => $interno_residenza));
	}


	if ($ID == 0) $invia = "Insert";
	else $invia = "Update";
	//INSERT E UPDATE
	switch ($invia) {
		case "Insert":

			//$comune_id_array = select_mysql_array("MAX(Comune_ID) AS max_id", "utente", "CC_Comune = '".$c."'");
			//$comune_id = $comune_id_array[0]['max_id'];

			$queryID = "SELECT MAX(Comune_ID) AS max_id FROM utente WHERE CC_Comune = '{$c}'";
			$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($queryID));
			$comune_id = isset($result['max_id']) ? $result['max_id'] : null;
			//echo "<h1>Comune ID: ".$a_paramsUtente['fields'][count($a_paramsUtente['fields'])-1]['value']."</h1>";

			$a_paramsUtente['fields'][count($a_paramsUtente['fields']) - 1]['value'] = $comune_id + 1;

			//echo "<h1>Comune ID: ".$a_paramsUtente['fields'][count($a_paramsUtente['fields'])-1]['value']."</h1>";
			//$field_utente[] = 'Comune_ID'; 	$value_utente[] = $comune_id+1;

			$new_ID_utente = $cls_db->DbSave($a_paramsUtente);
			if (!$new_ID_utente) {
				$cls_db->Rollback();
				$error = 1;
				$msg = "Inserimento dati non riuscito.";
			} else $msg = "Inserimento dati riuscito.";

			$a_paramsIndirizzo['fields'][0]['value'] = $new_ID_utente;

			if (!$cls_db->DbSave($a_paramsIndirizzo)) {
				$cls_db->Rollback();
				$error = 1;
				$msg = "Inserimento dati non riuscito.";
			} else $msg = "Inserimento dati riuscito.";

			/*$new_ID_utente = table_insert_record('utente', $field_utente, $value_utente);
			$value_residenza[0] = $new_ID_utente;//UTENTE ID

			$control = table_insert_record('indirizzo', $field_residenza, $value_residenza);


			if($control!=0 && $new_ID_utente!=0)
			{
				echo "Insert Si ".$new_ID_utente." ".($comune_id+1);
			}
			else
			{
				echo "Insert No ".$new_ID_utente." ".($comune_id+1);
			}*/

			break;

		case "Update":

			$a_paramsUtente['updateField'] = array('name' => 'ID', 'type' => 'int', 'value' => $ID);
			$a_paramsIndirizzo['updateField'] = array('name' => 'ID', 'type' => 'int', 'value' => $ID_res);

			if (!empty($old_pec)) {

				if (!is_null($data_registrazione) && $pec_utente != $old_pec) {
					$query_up_utente = " UPDATE utente SET InipecLoaded = '" . date("Y-m-d") . "' WHERE Id =" . $ID;
					mysqli_query($cls_db->conn, $query_up_utente);

					$a_storico = array(
						'table' => 'storico_pec',
						'fields' => array(
							array('name' => 'Utente_Id',        'type' => 'int',    'value' => $ID),
							array('name' => 'Data_Cambio',      'type' => 'date',   'value' => date('Y-m-d')),
							array('name' => 'Pec',              'type' => 'string', 'value' => $old_pec),
						)
					);

					$last_storico_Id = $cls_db->DbInsert($a_storico);
				}
			}

			if (!$cls_db->DbSave($a_paramsUtente)) {
				$cls_db->Rollback();
				$error = 1;
				$msg = "Aggiornamento non riuscito";
			} else $msg = "Aggiornamento dati riuscito.";

			if (!$cls_db->DbSave($a_paramsIndirizzo)) {
				$cls_db->Rollback();
				$error = 1;
				$msg = "Aggiornamento non riuscito";
			} else $msg = "Aggiornamento dati riuscito.";

			$new_ID_utente = $ID;
			/*$control = table_update_record('utente', $field_utente, $value_utente, 'ID' , $ID);
			$control2 = table_update_record('indirizzo', $field_residenza, $value_residenza, 'ID' , $ID_res);
			if($control==true && $control2==true)
			{
				echo "Update Si ".$ID." ".$comune_id;
			}
			else
			{
				echo "Update No ".$ID." ".$comune_id;
			}*/

			break;
	}
}

$cls_db->End_Transaction();

header("Location: dati_soggetto.php?&p={$new_ID_utente}&c={$c}&a={$a}&msg={$msg}&error={$error}");
