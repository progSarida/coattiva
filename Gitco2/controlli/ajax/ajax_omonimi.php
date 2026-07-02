<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_storico.php";



$storico = new storico('storicoControlliGestione','6');
$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();


$auth = $cls_help->getVar('auth');
$status = $cls_help->getVar('stat_process');
$c = $cls_help->getVar('c');
$cod_catastale = $cls_help->getVar('cod_comune');

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];
$storico->insRow('E', "Elaborazione 'Gestione anagrafica' per l'ente ".$nome_ente."[".$c."]");

if (
	intval($auth) === 1 &&
	!(
		(!is_null($cod_catastale) && !empty(trim($cod_catastale))) &&
		(!is_null($status) && !empty($status))
	)
) {
	echo json_encode(['esito' => 'KO', 'message' => 'PARAMETRI_INESISTENTI']);
	return;
}

if (
	intval($auth) > 1 &&
	!(
		(!is_null($status) && !empty($status))
	)
) {
	echo json_encode(['esito' => 'KO', 'message' => 'PARAMETRI_INESISTENTI']);
	return;
}

$status = trim($status);
$cod_catastale = trim($cod_catastale);

$cc_comune = "";

if (intval($auth) === 1) {
	$cc_comune = $cod_catastale;
} else {
	$cc_comune = $c;
}

/**
 * VERIFICO SE ESISTONO DUPLICATI
 */

$query_verifica = 	"	SELECT 'ESISTONO_DUPLICATI' AS VERIFICA  " .
	"	WHERE (	" .
	"	 		EXISTS  (	" .
	"  					SELECT COALESCE(TRIM(Nome), '') AS Nome, COALESCE(TRIM(Cognome), '') AS Cognome, TRIM(Codice_Fiscale) AS Codice_Fiscale   " .
	"  					FROM utente  " .
	"  					WHERE COALESCE(TRIM(Codice_Fiscale), '') <> ''  " .
	"						AND (Genere = 'M' OR Genere = 'F' )  " .
	"						AND CC_Comune=  '" . $cc_comune . "'  " .
	"  					GROUP BY COALESCE (TRIM(Nome),''), COALESCE(TRIM(Cognome), ''), TRIM(Codice_Fiscale)  " .
	"  					HAVING COUNT(*) > 1  " .
	"		  	)	" .
	" 		  OR	" .
	"			EXISTS (	" .
	"					SELECT COALESCE(TRIM(Ditta), '') AS DITTA, TRIM(Partita_Iva) AS Partita_Iva  " .
	"					FROM utente   " .
	"					WHERE COALESCE(TRIM(Partita_Iva), '') <> ''  " .
	"						AND Genere = 'D'  " .
	"						AND CC_Comune=  '" . $cc_comune . "'  " .
	"					GROUP BY COALESCE (TRIM(Ditta),''), TRIM(Partita_Iva ) " .
	"					HAVING COUNT(*) > 1  " .
	"			) " .
	"		  ) ";

$verifica = $cls_db->getResults($cls_db->ExecuteQuery($query_verifica));

if (count($verifica) == 0) {

	goto NO_DUPLICATI;
}

/**
 * QUERY DUPLICATI
 */
$query = "	SELECT	'PERSONA_FISICA' AS TIPO, " .
	" 			u.ID AS UTENTE_ID, " .
	" 			u.Comune_ID AS UTENTE_COMUNE_ID, " .
	"			u.Genere AS GENERE, " .
	"			COALESCE(TRIM(u.Nome), '') AS NOME, " .
	"			COALESCE(TRIM(u.Cognome), '') AS COGNOME,  " .
	"			COALESCE(TRIM(u.Ditta), '') AS DITTA,  " .
	"			TRIM(u.Codice_Fiscale) AS CF,  " .
	"			COALESCE(TRIM(u.Partita_Iva), '') AS VAT, " .
	"			u.CC_Comune AS CC_COMUNE,  " .
	"			pt.ID AS ID_PT, " .
	"			pt.Comune_ID AS PARTITA_COMUNE_ID, " .
	"			top_cap.Odonimo AS CAP_VIA, " .
	"			top_cap.ID AS CAP_ID, " .
	"			t.ID AS TOP_ID, " .
	"			t.Nome AS TOP_VIA,  " .
	"			i.ID AS i_ID,  " .
	"			'' AS PRESENZA  " .
	"	FROM utente AS u  " .
	"			LEFT JOIN partita_tributi AS pt ON pt.Utente_ID = u.ID   " .
	"			LEFT JOIN indirizzo AS i ON i.Utente_ID = u.ID  " .
	"			LEFT JOIN toponimo AS t ON t.ID = i.Via_ID  " .
	"			LEFT JOIN toponimi_cappati AS top_cap ON top_cap.ID = i.Via_Cap_ID  " .
	"			JOIN (  " .
	"  					SELECT COALESCE(TRIM(Nome), '') AS Nome, COALESCE(TRIM(Cognome), '') AS Cognome, TRIM(Codice_Fiscale) AS Codice_Fiscale   " .
	"  					FROM utente  " .
	"  					WHERE COALESCE(TRIM(Codice_Fiscale), '') <> ''  " .
	"						AND (Genere = 'M' OR Genere = 'F' )  " .
	"						AND CC_Comune=  '" . $cc_comune . "'  " .
	"  					GROUP BY COALESCE (TRIM(Nome),''), COALESCE(TRIM(Cognome), ''), TRIM(Codice_Fiscale)  " .
	"  					HAVING COUNT(*) > 1  " .
	"				) b ON COALESCE (TRIM(u.Nome),'') = b.Nome  " .
	"					AND COALESCE (trim(u.Cognome),'') = b.Cognome  " .
	"					AND trim(u.Codice_Fiscale) = b.Codice_Fiscale  " .
	"	WHERE  COALESCE(TRIM(u.Codice_Fiscale), '') <> ''  " .
	"			AND u.CC_Comune= '" . $cc_comune . "'  " .
	"			AND (i.Via_ID > 1 OR i.Via_Cap_ID > 1)  " .
	"			AND (u.Genere = 'M' OR u.Genere = 'F' )  " .
	"	UNION 	" .
	"	SELECT  'PERSONA_GIURIDICA' AS TIPO,  " .
	"			u.ID AS UTENTE_ID,  " .
	" 			u.Comune_ID AS UTENTE_COMUNE_ID, " .
	"        	u.Genere AS GENERE,  " .
	"			COALESCE(TRIM(u.Nome), '') AS NOME,  " .
	"			COALESCE(TRIM(u.Cognome), '') AS COGNOME,  " .
	"			COALESCE(TRIM(u.Ditta), '') AS DITTA,  " .
	"			COALESCE(TRIM(u.Codice_Fiscale), '') AS CF,  " .
	"			TRIM(u.Partita_Iva) AS VAT,  " .
	"			u.CC_Comune AS CC_COMUNE,  " .
	"			pt.ID AS ID_PT,  " .
	"			pt.Comune_ID AS PARTITA_COMUNE_ID,  " .
	"			top_cap.Odonimo AS CAP_VIA,  " .
	"			top_cap.ID AS CAP_ID, " .
	"			t.ID AS TOP_ID, " .
	"			t.Nome AS TOP_VIA,  " .
	"			i.ID AS i_ID,  " .
	"			'' AS PRESENZA  " .
	"	FROM utente AS u  " .
	"			LEFT JOIN partita_tributi AS pt on pt.Utente_ID = u.ID   " .
	"			LEFT JOIN indirizzo AS i on i.Utente_ID = u.ID  " .
	"			LEFT JOIN toponimo AS t on t.ID = i.Via_ID  " .
	"			LEFT JOIN toponimi_cappati AS top_cap on top_cap.ID = i.Via_Cap_ID  " .
	"			JOIN (	" .
	"					SELECT COALESCE(TRIM(Ditta), '') AS DITTA, TRIM(Partita_Iva) AS Partita_Iva  " .
	"					FROM utente   " .
	"					WHERE COALESCE(TRIM(Partita_Iva), '') <> ''  " .
	"						AND Genere = 'D'  " .
	"						AND CC_Comune=  '" . $cc_comune . "'  " .
	"					GROUP BY COALESCE (TRIM(Ditta),''), TRIM(Partita_Iva ) " .
	"					HAVING COUNT(*) > 1  " .
	"				) b ON COALESCE(TRIM(u.Ditta), '') = b.DITTA  " .
	"					AND TRIM(u.Partita_Iva) = b.Partita_Iva   " .
	"	WHERE  COALESCE(TRIM(u.Partita_Iva), '') <> ''  " .
	"		AND u.CC_Comune =  '" . $cc_comune . "'  " .
	"		AND ( i.Via_ID > 1 OR i.Via_Cap_ID > 1	)  " .
	"		 AND u.Genere = 'D'  " .
	"		ORDER BY TIPO, CF, VAT, UTENTE_ID ";


$results = $cls_db->ExecuteQuery($query);

$omonimi_arr = array();
$omonimi = $cls_db->getResults($results);

if (count($omonimi) == 0) {
	echo json_encode(['esito' => 'KO', 'message' => 'DATI_INCOGRUENTI']);
	return;
}

foreach ($omonimi as $omonimo) {

	$chiave = $omonimo["CF"];
	if ($omonimo['TIPO'] == "PERSONA_GIURIDICA") {
		$chiave = $omonimo["VAT"];
	}

	$omonimi_arr[$chiave]["TIPO"] =  $omonimo["TIPO"];
	$omonimi_arr[$chiave]["UTENTE_ID"] = $omonimo["UTENTE_ID"];
	$omonimi_arr[$chiave]["UTENTE_COMUNE_ID"] = $omonimo["UTENTE_COMUNE_ID"];
	$omonimi_arr[$chiave]["GENERE"] = $omonimo["GENERE"];
	$omonimi_arr[$chiave]["NOME"] = $omonimo["NOME"];
	$omonimi_arr[$chiave]["COGNOME"] = $omonimo["COGNOME"];
	$omonimi_arr[$chiave]["DITTA"] = $omonimo["DITTA"];
	$omonimi_arr[$chiave]["CF"] =  $omonimo["CF"];
	$omonimi_arr[$chiave]["VAT"] = $omonimo["VAT"];
	$omonimi_arr[$chiave]["CC_COMUNE"] = $omonimo["CC_COMUNE"];

	$omonimi_arr[$chiave]["PRESENZA"] = $omonimo["PRESENZA"];

	$omonimi_arr[$chiave]["UTENTI"][] = $omonimo["UTENTE_ID"];

	$omonimi_arr[$chiave]["PARTITA_TRIBUTI"][$omonimo["ID_PT"]]["ID_PT"] = $omonimo["ID_PT"];
	$omonimi_arr[$chiave]["PARTITA_TRIBUTI"][$omonimo["ID_PT"]]["PARTITA_COMUNE_ID"] = $omonimo["PARTITA_COMUNE_ID"];
	$omonimi_arr[$chiave]["PARTITA_TRIBUTI"][$omonimo["ID_PT"]]["UTENTE_ID"] = $omonimo["UTENTE_ID"];

	$omonimi_arr[$chiave]["INDIRIZZI"][$omonimo["i_ID"]]["i_ID"] = $omonimo["i_ID"];
	$omonimi_arr[$chiave]["INDIRIZZI"][$omonimo["i_ID"]]["CAP_VIA"] = $omonimo["CAP_VIA"];
	$omonimi_arr[$chiave]["INDIRIZZI"][$omonimo["i_ID"]]["TOP_VIA"] = $omonimo["TOP_VIA"];
	$omonimi_arr[$chiave]["INDIRIZZI"][$omonimo["i_ID"]]["UTENTE_ID"] = $omonimo["UTENTE_ID"];
}


$normalizzati = false;
if (intval($status) === 1) {
	goto GENERA_EXCEL;
} else {

	/**
	 * 	ELIMINA_DUPLICATI SU DB
	 */

	foreach ($omonimi_arr as $soggetto) {

		$utenti_arr = $soggetto["UTENTI"];

		if (($key = array_search($soggetto["UTENTE_ID"], $utenti_arr)) !== false) {
			unset($utenti_arr[$key]);
		}
		$utenti_str = implode(',', $utenti_arr);

		/** INIZIO TRANSAZIONE **/
		$cls_db->Start_Transaction();
		$cls_db->Begin_Transaction();
		try {

			$istr_delete_indirizzi = " DELETE FROM indirizzo WHERE Utente_ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_delete_indirizzi);

			$istr_update_partite =  "UPDATE partita_tributi SET Utente_ID =" . $soggetto["UTENTE_ID"] . "  WHERE Utente_ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_update_partite);

			$istr_delete_utenti = " DELETE FROM utente WHERE ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_delete_utenti);

			
			$istr_update_documento =  "UPDATE documento SET Utente_ID =" . $soggetto["UTENTE_ID"] . "  WHERE Utente_ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_update_documento);

			$istr_update_email_inviate =  "UPDATE email_inviate SET Utente_ID =" . $soggetto["UTENTE_ID"] . "  WHERE Utente_ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_update_email_inviate);

			$istr_update_ispezioni =  "UPDATE ispezioni SET Utente_ID =" . $soggetto["UTENTE_ID"] . "  WHERE Utente_ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_update_ispezioni);

			$istr_delete_storico_residenza = " DELETE FROM storico_residenza WHERE Utente_ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_delete_storico_residenza);

			$istr_update_veicoli =  "UPDATE veicoli SET Utente_ID =" . $soggetto["UTENTE_ID"] . "  WHERE Utente_ID IN (" . $utenti_str . ") ";
			mysqli_query($cls_db->conn, $istr_update_veicoli);

			

			$cls_db->End_Transaction();
		} catch (mysqli_sql_exception $e) {
			$cls_db->Rollback();
			$log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());

			echo json_encode(['esito' => 'KO', 'message' => 'DUPLICATI_NON_ELIMINABILI']);
			return;
		}

		/** FINE TRANSAZIONE **/
	}

	/** NORMALIZZA DATI **/
	$indirizzo_finale = array();

	foreach ($omonimi_arr as $chiave => $soggetto) {

		foreach ($soggetto["INDIRIZZI"] as $key => $indirizzo) {
			if ($indirizzo["UTENTE_ID"] == $soggetto["UTENTE_ID"]) {
				$indirizzo_finale = $indirizzo;
				break;
			}
		}
	}


	foreach ($omonimi as $key => $omonimo) {

		$chiave = $omonimo["CF"];
		if ($omonimo['TIPO'] == "PERSONA_GIURIDICA") {
			$chiave = $omonimo["VAT"];
		}

		$soggetto = $omonimi_arr[$chiave];
		if ($omonimo["UTENTE_ID"] !== $soggetto["UTENTE_ID"]) {
			$omonimi[$key]["PRESENZA"] = 'ELIMINATO';
		} else {
			$omonimi[$key]["PRESENZA"] = 'MANTENUTO';
		}
	}

	$normalizzati = true;
}


GENERA_EXCEL:

$filename = "DATI_DA_NORMALIZZARE.xlsx";
$intestazione_excel = "ELENCO UTENTI DUPLICATI DA NORMALIZZARE";
if ($normalizzati) {
	$filename = "DATI_NORMALIZZATI.xlsx";
	$intestazione_excel = "ELENCO UTENTI DUPLICATI NORMALIZZATI";
}

$duplicati = new PHPExcel();
$duplicati->setActiveSheetIndex(0);
$rowCount = 4;

$duplicati->getActiveSheet()->SetCellValue('A1', $intestazione_excel);
$duplicati->getActiveSheet()->SetCellValue('A2', "PRESENZA");
$duplicati->getActiveSheet()->SetCellValue('B2', "UTENTE_ID");
$duplicati->getActiveSheet()->SetCellValue('C2', "UTENTE_COMUNE_ID");
$duplicati->getActiveSheet()->SetCellValue('D2', "GENERE");
$duplicati->getActiveSheet()->SetCellValue('E2', "NOME");
$duplicati->getActiveSheet()->SetCellValue('F2', "COGNOME");
$duplicati->getActiveSheet()->SetCellValue('G2', "DITTA");
$duplicati->getActiveSheet()->SetCellValue('H2', "CODICE FISCALE");
$duplicati->getActiveSheet()->SetCellValue('I2', "PARTITA IVA");
$duplicati->getActiveSheet()->SetCellValue('J2', "CC_COMUNE");
$duplicati->getActiveSheet()->SetCellValue('K2', "ID_PT");
$duplicati->getActiveSheet()->SetCellValue('L2', "COMUNE_ID");
$duplicati->getActiveSheet()->SetCellValue('M2', "CAP_VIA");
$duplicati->getActiveSheet()->SetCellValue('N2', "TOP_VIA");
$duplicati->getActiveSheet()->SetCellValue('O2', "TIPO");
$duplicati->getActiveSheet()->mergeCells('A1:O1');



foreach ($omonimi as $omonimo) {


	$duplicati->getActiveSheet()->SetCellValue('A' . $rowCount, $omonimo["PRESENZA"]);
	$duplicati->getActiveSheet()->SetCellValue('B' . $rowCount, $omonimo["UTENTE_ID"]);
	if($omonimo["PRESENZA"] == 'MANTENUTO'){
		
		$duplicati->getActiveSheet($rowCount)->SetCellValue('B' . $rowCount, $omonimo["UTENTE_ID"])->getStyle('A'. $rowCount.':B'. $rowCount)->applyFromArray(
			array(
				'font'  => array(
					'bold' => true,
					'color' => array('rgb' => '198754'),
					'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
					
				)
			)
		);
		
	
		
	}
	if($omonimo["PRESENZA"] == 'ELIMINATO'){
		$duplicati->getActiveSheet($rowCount)->SetCellValue('B'. $rowCount, $omonimo["UTENTE_ID"])->getStyle('A'. $rowCount.':B'. $rowCount)->applyFromArray(
			array(
				'font'  => array(
					'bold' => true,
					'color' => array('rgb' => 'FF0000'),
					'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
					
				)
			)
		);
		
	
	}


	$duplicati->getActiveSheet()->SetCellValue('C' . $rowCount, $omonimo["UTENTE_COMUNE_ID"]);
	$duplicati->getActiveSheet()->SetCellValue('D' . $rowCount, $omonimo["GENERE"]);
	$duplicati->getActiveSheet()->SetCellValue('E' . $rowCount, $omonimo["NOME"]);
	$duplicati->getActiveSheet()->SetCellValue('F' . $rowCount, $omonimo["COGNOME"]);
	$duplicati->getActiveSheet()->SetCellValue('G' . $rowCount, $omonimo["DITTA"]);
	$duplicati->getActiveSheet()->SetCellValue('H' . $rowCount, $omonimo["CF"]);
	$duplicati->getActiveSheet()->SetCellValue('I' . $rowCount, $omonimo["VAT"]);
	$duplicati->getActiveSheet()->SetCellValue('J' . $rowCount, $omonimo["CC_COMUNE"]);
	$duplicati->getActiveSheet()->SetCellValue('K' . $rowCount, $omonimo["ID_PT"]);
	$duplicati->getActiveSheet()->SetCellValue('L' . $rowCount, $omonimo["PARTITA_COMUNE_ID"]);
	$duplicati->getActiveSheet()->SetCellValue('M' . $rowCount, $omonimo["CAP_VIA"]);
	$duplicati->getActiveSheet()->SetCellValue('N' . $rowCount, $omonimo["TOP_VIA"]);
	$duplicati->getActiveSheet()->SetCellValue('O' . $rowCount, $omonimo["TIPO"]);
	
	$rowCount++;
}

$objWriter = PHPExcel_IOFactory::createWriter($duplicati, 'Excel2007');

ob_start();
$objWriter->save("php://output");

$xlsData = ob_get_contents();

ob_end_clean();
$obj = array(
	'esito' => 'OK',
	'message' => 'EXCEL_DISPONIBILE',
	'nome_file' => $filename,
	"data" => null
);
$obj["data"] = "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($xlsData);

$json = json_encode($obj);

$storico->insRow('X', "Esportazione omonimi per l'ente ".$nome_ente."[".$c."]");
echo $json;
return;

NO_DUPLICATI:
echo json_encode(['esito' => 'OK', 'message' => 'NO_DUPLICATI']);
return;
