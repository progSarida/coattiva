<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class fatture_generali
{
	var $ID;
	var $Fat_Societa;
	var $Fat_Numero;
	var $Fat_Comune;
	var $Fat_Dati_Comune;
	var $Fat_Dati_Cig;
	var $Fat_Anno;
	var $Fat_Tributo;
	var $Fat_Data;
	var $Fat_Tipo;
	var $Fat_Testo_Spettabile;
	var $Fat_Importo;
	var $Fat_Spese;
	var $Fat_Iva_Percentuale;
	var $Fat_Iva;
	var $Fat_Testo_Iva;
	var $Fat_Rimborsi;
	var $Fat_Ordinario;
	var $Fat_Temporaneo;
	var $Fat_Affissioni;
	var $Fat_Bollo;
	var $Fat_Totale;
	var $Fat_Totale_A_Doversi;
	//var $Fat_Data_Accredito;
	//var $Fat_Importo_Accredito;
	//var $Fat_Reversale_Comune;
	//var $Fat_Reversale_Provincia;
	var $Fat_Tipo_Versamento;
	var $Fat_Da_Data_Periodo;
	var $Fat_A_Data_Periodo;
	var $Fat_Testo_Da_A_Periodo;
	var $Fat_Collegata;
	var $Fat_Data_Collegata;
	var $Fat_Pagata;
	var $Fat_Anno_Bilancio;
	var $Fat_Anno_Competenza;
	var $Fat_Tipo_Contratto;
	var $Fat_Data_Contratto;
	var $Fat_Numero_Contratto;
	var $Fat_Tipo_2_Contratto;
	var $Fat_Data_2_Contratto;
	var $Fat_Numero_2_Contratto;
	var $Fat_Tipo_3_Contratto;
	var $Fat_Data_3_Contratto;
	var $Fat_Numero_3_Contratto;
	var $Fat_Testo_Contratto;
	var $Fat_Giorni_Pagamento;
	var $Fat_Testo_Pagamento;
	var $Fat_Imposta_Da_Versare;
	var $Fat_Nome_File_Pdf;
	var $Fat_Nome_File_Xml;
	var $Fat_Data_Accredito;
	var $Fat_Accredito;
	var $Fat_Data_Registrazione;
	var $Fat_Ora_Registrazione;
	var $Fat_Operatore;
	

	function fatture_generali( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM fatture_generali WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaFattura = mysql_fetch_assoc($result);
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaFattura as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "fatture_generali")
			{
				//echo " c'� " . $temp;
				return;
			}
		}
		
		$queryCreate = "
			CREATE TABLE `fatture_generali` (
			  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
			  `Fat_Societa` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Numero` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Comune` varchar(4) NOT NULL DEFAULT '',
			  `Fat_Dati_Comune` int(9) NOT NULL DEFAULT '0',
			  `Fat_Dati_Cig` int(9) NOT NULL DEFAULT '0',
			  `Fat_Anno` int(4) NOT NULL DEFAULT '0',
			  `Fat_Tributo` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Data` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Tipo` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Testo_Spettabile` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Importo` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Spese` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Iva_Percentuale` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Iva` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Testo_Iva` varchar(200) NOT NULL DEFAULT '',
			  `Fat_Rimborsi` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Ordinario` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Temporaneo` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Affissioni` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Bollo` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Totale` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Totale_A_Doversi` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Tipo_Versamento` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Da_Data_Periodo` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_A_Data_Periodo` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Testo_Da_A_Periodo` varchar(200) NOT NULL DEFAULT '',
			  `Fat_Collegata` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Data_Collegata` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Pagata` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Anno_Bilancio` year(4) NOT NULL DEFAULT '0000',
			  `Fat_Anno_Competenza` year(4) NOT NULL DEFAULT '0000',
			  `Fat_Tipo_Contratto` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_Contratto` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Numero_Contratto` varchar(10) NOT NULL DEFAULT '',
			  `Fat_Tipo_2_Contratto` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_2_Contratto` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Numero_2_Contratto` varchar(10) NOT NULL DEFAULT '',
			  `Fat_Tipo_3_Contratto` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_3_Contratto` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Numero_3_Contratto` varchar(10) NOT NULL DEFAULT '',
			  `Fat_Testo_Contratto` varchar(400) NOT NULL DEFAULT '',
			  `Fat_Giorni_Pagamento` int(3) NOT NULL DEFAULT '0',
			  `Fat_Testo_Pagamento` varchar(200) NOT NULL DEFAULT '',
			  `Fat_Imposta_Da_Versare` varchar(5) NOT NULL DEFAULT '',
			  `Fat_Nome_File_Pdf` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Nome_File_Xml` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_Accredito` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Accredito` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Ora_Registrazione` time NOT NULL DEFAULT '00:00:00',
			  `Fat_Operatore` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	function ListaTutteFatture ()
	{
		$queryTutte = "SELECT Fat_Numero FROM fatture_generali ORDER BY Fat_Numero";
		$resTutte = mysql_query($queryTutte);
		$arrayTutte = array();
		if (mysql_num_rows($resTutte) != 0)
		{
			while ($rigaTutte = mysql_fetch_assoc($resTutte))
			{
				$arrayTutte[] = $rigaTutte['Fat_Numero'];
			}
		}
		return $arrayTutte;
	}
	
	function CercaFatturaDaNumero ($numeroconbarre)
	{
		$queryCerca = "SELECT ID FROM fatture_generali ";
		$queryCerca .= "WHERE Fat_Numero = '" . $numeroconbarre . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function FatturaSuccessiva ()
	{
		$queryCerca = "SELECT ID FROM fatture_generali ";
		$queryCerca .= "WHERE Fat_Numero > '" . $this->Fat_Numero . "' ";
		$queryCerca .= "ORDER BY Fat_Numero ASC";
		$resCerca = mysql_query($queryCerca);
		//if ($_SESSION['CC_User'] == "***+") echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);  //  prendo il primo
		return $rigaCerca['ID'];
	}
	
	function FatturaPrecedente ()
	{
		$queryCerca = "SELECT ID FROM fatture_generali ";
		$queryCerca .= "WHERE Fat_Numero < '" . $this->Fat_Numero . "' ";
		$queryCerca .= "ORDER BY Fat_Numero DESC";
		$resCerca = mysql_query($queryCerca);
		//if ($_SESSION['CC_User'] == "***+") echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);  //  prendo il primo
		return $rigaCerca['ID'];
	}
	
	/*function OptionTipiFatture ($scelta)
	{
		$queryTutte = "SELECT DISTINCT Fat_Tributo FROM fatture_generali ORDER BY Fat_Tributo";
		$resTutte = mysql_query($queryTutte);
		$select = "<option value=''></option>";
		while ($rigaDati = mysql_fetch_assoc($resTutte))
		{
			if ($scelta == $rigaDati['Fat_Tributo']) $selScelta = " selected ";
			else $selScelta = "";
			switch ($rigaDati['Fat_Tributo'])
			{
				case "TOSAP": $valore = "TOSAP"; break;
				case "PUB": $valore = "PUBBLICITA'"; break;
				default: $valore = $rigaDati['Fat_Tributo']; break;
			}
			$select .= "<option value='" . $rigaDati['Fat_Tributo'] . "' $selScelta>";
			$select .= $valore;
			$select .= "</option>";
		}
		return $select;
	}*/

	function OptionTipiFatture ($scelta)
	{
		$selectPark = $selectLibera = $selectCds = $selectPub = $selectTosap = $selectTari = $selectIci = $selectImu = "";
		switch ($scelta)
		{
			case "CDS": $selectCds = " selected "; break;
			case "PUB": $selectPub = " selected "; break;
			case "TOSAP": $selectTosap = " selected "; break;
			case "TARI": $selectTari = " selected "; break;
			case "ICI": $selectIci = " selected "; break;
			case "IMU": $selectImu = " selected "; break;
			case "PARK": $selectPark = " selected "; break;
			case "LIBERA": $selectLibera = " selected "; break;

		}
	
		$select = "<option value=''></option>";
		$select .= "<option value='CDS' $selectCds>CODICE DELLA STRADA</option>\n";
		$select .= "<option value='PUB' $selectPub>PUBBLICITA</option>\n";
		$select .= "<option value='TOSAP' $selectTosap>TOSAP</option>\n";
		$select .= "<option value='TARI' $selectTari>TARSU/TARI</option>\n";
		$select .= "<option value='ICI' $selectIci>ICI</option>\n";
		$select .= "<option value='IMU' $selectImu>IMU/TASI</option>\n";
		$select .= "<option value='PARK' $selectPark>PARK</option>\n";
		$select .= "<option value='LIBERA' $selectLibera>LIBERA</option>\n";
		return $select;
	}

	function OptionTipiGestione ($scelta)
	{
		$selectCanone = $selectAggio = $selectServizio = "";
		switch ($scelta)
		{
			case "PAGATA_A_CANONE": $selectCanone = " selected "; break;
			case "PAGATA_AD_AGGIO": $selectAggio = " selected "; break;
			case "SERVIZIO": $selectServizio = " selected "; break;
		}
	
		$select = "<option value=''></option>";
		$select .= "<option value='PAGATA_A_CANONE' $selectCanone>CANONE</option>\n";
		$select .= "<option value='PAGATA_AD_AGGIO' $selectAggio>AGGIO</option>\n";
		$select .= "<option value='SERVIZIO' $selectServizio>SERVIZIO</option>\n";
	
		return $select;
	}
	
	function NomeComuneDaCCFattura ($cc)
	{
		$queryCerca = "SELECT Indirizzo2 FROM fatture_dati_sedi_comuni ";
		$queryCerca .= "WHERE CC = '" . $cc . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Indirizzo2'];
	}
	
	function FatturaGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM fatture_generali ";
		$queryCerca .= "WHERE Fat_Numero = '" . $this->Fat_Numero . "' ";
		$queryCerca .= "AND Fat_Comune = '" . $this->Fat_Comune . "' ";
		$queryCerca .= "AND Fat_Anno = '" . $this->Fat_Anno . "' ";
		$queryCerca .= "AND Fat_Data = '" . $this->Fat_Data . "' ";
		$queryCerca .= "AND Fat_Tributo = '" . $this->Fat_Tributo . "'";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateFattura ($forzato = NULL)  //  forzato � INSERT o UPDATE
	{
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->FatturaGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->ID;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO FATTURA, errore ID"); return; }
		}
		
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "ID" &&
				$key != "Fat_Data_Registrazione" &&
				$key != "Fat_Ora_Registrazione" &&
				$key != "Fat_Operatore")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		$fields[] = "Fat_Data_Registrazione";
		$values[] = date("Y-m-d");
		$fields[] = "Fat_Ora_Registrazione";
		$values[] = date("H:i:s");
		$fields[] = "Fat_Operatore";
		$values[] = $_SESSION['username'];
		
		if ($progressivo == NULL)  // non � presente
		{
			$verif = $this->insert_fattura_locale($fields, $values);
			if ($verif == true)
				return "INSERT_FATTURA_OK";
			else
				return "INSERT_FATTURA_ERRORE";
		}
		else  //  � gi� presente
		{
			$verif = $this->update_fattura_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_FATTURA_OK";
			else
				return "UPDATE_FATTURA_ERRORE";
		}
	}
	
	function insert_fattura_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO fatture_generali (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			return mysql_query($query);
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
	
	function update_fattura_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldFat = new fatture_generali($key);
		
		$newImmagini = "";
		$newDocumenti = "";
		$newData = "";
		$newOra = "";
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldFat->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return TRUE;  // non updata nulla, perch� sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", " 
	
		$query = "UPDATE fatture_generali SET $clause WHERE ID = '" . $key . "'";
		
		$nonUpd = "UPDATE fatture_generali SET Fat_Data_Registrazione";
		if (substr($query, 0, strlen($nonUpd)) == $nonUpd)  //  tutti i campi uguali!
			return true;
	
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else 
		{
			echo "<br><br>" . $query;
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldFat->$fields_to_update[$i] . ")";
			}
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
}


class fatture_stc
{
	var $ID;
	var $Fat_Societa;
	var $Fat_Numero;
	var $Fat_Comune;
	var $Fat_Dati_Comune;
	var $Fat_Dati_Cig;
	var $Fat_Anno;
	var $Fat_Tributo;
	var $Fat_Data;
	var $Fat_Tipo;
	var $Fat_Testo_Spettabile;
	var $Fat_Importo;
	var $Fat_Spese;
	var $Fat_Iva_Percentuale;
	var $Fat_Iva;
	var $Fat_Testo_Iva;
	var $Fat_Rimborsi;
	var $Fat_Ordinario;
	var $Fat_Temporaneo;
	var $Fat_Affissioni;
	var $Fat_Bollo;
	var $Fat_Totale;
	var $Fat_Totale_A_Doversi;
	//var $Fat_Data_Accredito;
	//var $Fat_Importo_Accredito;
	//var $Fat_Reversale_Comune;
	//var $Fat_Reversale_Provincia;
	var $Fat_Tipo_Versamento;
	var $Fat_Da_Data_Periodo;
	var $Fat_A_Data_Periodo;
	var $Fat_Testo_Da_A_Periodo;
	var $Fat_Collegata;
	var $Fat_Data_Collegata;
	var $Fat_Pagata;
	var $Fat_Anno_Bilancio;
	var $Fat_Anno_Competenza;
	var $Fat_Tipo_Contratto;
	var $Fat_Data_Contratto;
	var $Fat_Numero_Contratto;
	var $Fat_Tipo_2_Contratto;
	var $Fat_Data_2_Contratto;
	var $Fat_Numero_2_Contratto;
	var $Fat_Tipo_3_Contratto;
	var $Fat_Data_3_Contratto;
	var $Fat_Numero_3_Contratto;
	var $Fat_Testo_Contratto;
	var $Fat_Giorni_Pagamento;
	var $Fat_Testo_Pagamento;
	var $Fat_Imposta_Da_Versare;
	var $Fat_Nome_File_Pdf;
	var $Fat_Nome_File_Xml;
	var $Fat_Data_Accredito;
	var $Fat_Accredito;
	var $Fat_Data_Registrazione;
	var $Fat_Ora_Registrazione;
	var $Fat_Operatore;
	

	function fatture_stc( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM fatture_stc WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaFattura = mysql_fetch_assoc($result);
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaFattura as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "fatture_stc")
			{
				//echo " c'� " . $temp;
				return;
			}
		}
		
		$queryCreate = "
			CREATE TABLE `fatture_stc` (
			  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
			  `Fat_Societa` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Numero` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Comune` varchar(4) NOT NULL DEFAULT '',
			  `Fat_Dati_Comune` int(9) NOT NULL DEFAULT '0',
			  `Fat_Dati_Cig` int(9) NOT NULL DEFAULT '0',
			  `Fat_Anno` int(4) NOT NULL DEFAULT '0',
			  `Fat_Tributo` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Data` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Tipo` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Testo_Spettabile` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Importo` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Spese` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Iva_Percentuale` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Iva` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Testo_Iva` varchar(200) NOT NULL DEFAULT '',
			  `Fat_Rimborsi` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Ordinario` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Temporaneo` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Affissioni` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Bollo` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Totale` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Totale_A_Doversi` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Tipo_Versamento` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Da_Data_Periodo` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_A_Data_Periodo` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Testo_Da_A_Periodo` varchar(200) NOT NULL DEFAULT '',
			  `Fat_Collegata` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Data_Collegata` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Pagata` varchar(20) NOT NULL DEFAULT '',
			  `Fat_Anno_Bilancio` year(4) NOT NULL DEFAULT '0000',
			  `Fat_Anno_Competenza` year(4) NOT NULL DEFAULT '0000',
			  `Fat_Tipo_Contratto` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_Contratto` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Numero_Contratto` varchar(10) NOT NULL DEFAULT '',
			  `Fat_Tipo_2_Contratto` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_2_Contratto` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Numero_2_Contratto` varchar(10) NOT NULL DEFAULT '',
			  `Fat_Tipo_3_Contratto` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_3_Contratto` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Numero_3_Contratto` varchar(10) NOT NULL DEFAULT '',
			  `Fat_Testo_Contratto` varchar(400) NOT NULL DEFAULT '',
			  `Fat_Giorni_Pagamento` int(3) NOT NULL DEFAULT '0',
			  `Fat_Testo_Pagamento` varchar(200) NOT NULL DEFAULT '',
			  `Fat_Imposta_Da_Versare` varchar(5) NOT NULL DEFAULT '',
			  `Fat_Nome_File_Pdf` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Nome_File_Xml` varchar(50) NOT NULL DEFAULT '',
			  `Fat_Data_Accredito` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Accredito` decimal(10,2) NOT NULL DEFAULT '0.00',
			  `Fat_Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
			  `Fat_Ora_Registrazione` time NOT NULL DEFAULT '00:00:00',
			  `Fat_Operatore` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	function ListaTutteFatture ()
	{
		$queryTutte = "SELECT Fat_Numero FROM fatture_stc ORDER BY Fat_Numero";
		$resTutte = mysql_query($queryTutte);
		$arrayTutte = array();
		if (mysql_num_rows($resTutte) != 0)
		{
			while ($rigaTutte = mysql_fetch_assoc($resTutte))
			{
				$arrayTutte[] = $rigaTutte['Fat_Numero'];
			}
		}
		return $arrayTutte;
	}
	
	function CercaFatturaDaNumero ($numeroconbarre)
	{
		$queryCerca = "SELECT ID FROM fatture_stc ";
		$queryCerca .= "WHERE Fat_Numero = '" . $numeroconbarre . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	/*function OptionTipiFatture ($scelta)
	{
		$queryTutte = "SELECT DISTINCT Fat_Tributo FROM fatture_stc ORDER BY Fat_Tributo";
		$resTutte = mysql_query($queryTutte);
		$select = "<option value=''></option>";
		while ($rigaDati = mysql_fetch_assoc($resTutte))
		{
			if ($scelta == $rigaDati['Fat_Tributo']) $selScelta = " selected ";
			else $selScelta = "";
			switch ($rigaDati['Fat_Tributo'])
			{
				case "TOSAP": $valore = "TOSAP"; break;
				case "PUB": $valore = "PUBBLICITA'"; break;
				default: $valore = $rigaDati['Fat_Tributo']; break;
			}
			$select .= "<option value='" . $rigaDati['Fat_Tributo'] . "' $selScelta>";
			$select .= $valore;
			$select .= "</option>";
		}
		return $select;
	}*/

	function OptionTipiFatture ($scelta)
	{
		$selectCds = $selectPub = $selectTosap = "";
		switch ($scelta)
		{
			case "CDS": $selectCds = " selected "; break;
			case "PUB": $selectPub = " selected "; break;
			case "TOSAP": $selectTosap = " selected "; break;
		}
	
		$select = "<option value=''></option>";
		$select .= "<option value='CDS' $selectCds>CODICE DELLA STRADA</option>\n";
		$select .= "<option value='PUB' $selectPub>PUBBLICITA</option>\n";
		$select .= "<option value='TOSAP' $selectTosap>TOSAP</option>\n";
	
		return $select;
	}
	
	function NomeComuneDaCCFattura ($cc)
	{
		$queryCerca = "SELECT Indirizzo2 FROM fatture_dati_sedi_comuni ";
		$queryCerca .= "WHERE CC = '" . $cc . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Indirizzo2'];
	}
	
	function FatturaGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM fatture_stc ";
		$queryCerca .= "WHERE Fat_Numero = '" . $this->Fat_Numero . "' ";
		$queryCerca .= "AND Fat_Comune = '" . $this->Fat_Comune . "' ";
		$queryCerca .= "AND Fat_Anno = '" . $this->Fat_Anno . "' ";
		$queryCerca .= "AND Fat_Data = '" . $this->Fat_Data . "' ";
		$queryCerca .= "AND Fat_Tributo = '" . $this->Fat_Tributo . "'";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateFattura ($forzato = NULL)  //  forzato � INSERT o UPDATE
	{
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->FatturaGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->ID;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO FATTURA, errore ID"); return; }
		}
		
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "ID" &&
				$key != "Fat_Data_Registrazione" &&
				$key != "Fat_Ora_Registrazione" &&
				$key != "Fat_Operatore")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		$fields[] = "Fat_Data_Registrazione";
		$values[] = date("Y-m-d");
		$fields[] = "Fat_Ora_Registrazione";
		$values[] = date("H:i:s");
		$fields[] = "Fat_Operatore";
		$values[] = $_SESSION['username'];
		
		if ($progressivo == NULL)  // non � presente
		{
			$verif = $this->insert_fattura_locale($fields, $values);
			if ($verif == true)
				return "INSERT_FATTURA_OK";
			else
				return "INSERT_FATTURA_ERRORE";
		}
		else  //  � gi� presente
		{
			$verif = $this->update_fattura_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_FATTURA_OK";
			else
				return "UPDATE_FATTURA_ERRORE";
		}
	}
	
	function insert_fattura_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO fatture_stc (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			return mysql_query($query);
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
	
	function update_fattura_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldFat = new fatture_stc($key);
		
		$newImmagini = "";
		$newDocumenti = "";
		$newData = "";
		$newOra = "";
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldFat->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return TRUE;  // non updata nulla, perch� sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", " 
	
		$query = "UPDATE fatture_stc SET $clause WHERE ID = '" . $key . "'";
		
		$nonUpd = "UPDATE fatture_stc SET Fat_Data_Registrazione";
		if (substr($query, 0, strlen($nonUpd)) == $nonUpd)  //  tutti i campi uguali!
			return true;
	
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else 
		{
			echo "<br><br>" . $query;
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldFat->$fields_to_update[$i] . ")";
			}
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
}




class fatture_dati_cig
{
	var $ID;
	var $Tipo_Gestione;
	var $Tipo_Tributo;
	var $Data;
	var $Comune;
	var $ID_Ufficio;
	var $Nome_Ufficio;
	var $CIG;
	var $CUP;
	var $Riferimento_Numero;
	
	function fatture_dati_cig( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM fatture_dati_cig WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaDati = mysql_fetch_assoc($result);
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaDati as $key => $value)
			{
				$this->$key = $value;
				//echo "<br>" . $key . " -" . $value . "-";
			}
		}
	}
	
	function ListaCigs ($comune, $tributo, $scelta)
	{
		$query = "SELECT * FROM fatture_dati_cig ";
		$query .= " WHERE Comune = '$comune' ";
		$query .= " AND Tipo_Tributo = '$tributo' ";
		$query .= " ORDER BY ID";
		$result = safe_query($query);
		
		$select = "<option value=''></option>";
		while ($rigaDati = mysql_fetch_assoc($result))
		{
			if ($scelta == $rigaDati['ID']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaDati['ID'] . "' $selScelta title='" . $rigaDati['Nome_Ufficio'] . "'>";
			$select .= $rigaDati['Tipo_Tributo'] . " - " . $rigaDati['ID_Ufficio'];
			if ($rigaDati['CIG'] != "") $select .= " - CIG " . $rigaDati['CIG'];
			if ($rigaDati['CUP'] != "") $select .= " - CUP " . $rigaDati['CUP'];
			$select .= "</option>";
		}
		return $select;
	}
	
	function ListaDatiCigs ($comune, $tributo)
	{
		$query = "SELECT * FROM fatture_dati_cig ";
		$query .= " WHERE Comune = '$comune' ";
		$query .= " AND Tipo_Tributo = '$tributo' ";
		$query .= " ORDER BY ID";
		$result = safe_query($query);
		
		$arrayID = array();
		$arrayTipiGest = array();
		$arrayTipiTributi = array();
		if (mysql_num_rows($result))
		{
			while ($rigaDati = mysql_fetch_assoc($result))
			{
				$arrayID[] = $rigaDati['ID'];
				$arrayTipiGest[] = $rigaDati['Tipo_Gestione'];
				$arrayTipiTributi[] = $rigaDati['Tipo_Tributo'];
				//echo "<br>" . $rigaDati['ID'] . " - " . $rigaDati['Tipo_Gestione'] . "<br>";
			}
		}
		else 
		{
			$arrayID[] = "";
			$arrayTipiGest[] = "";
			$arrayTipiTributi[] = "";
		}
		$arrayGlob = array ();
		$arrayGlob[0] = $arrayID;
		$arrayGlob[1] = $arrayTipiGest;
		$arrayGlob[2] = $arrayTipiTributi;
		return $arrayGlob;
	}
	
	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "fatture_dati_cig")
			{
				//echo " c'� " . $temp;
				return;
			}
		}
		
		$queryCreate = "
			CREATE TABLE fatture_dati_cig (
			`ID` int(9) unsigned NOT NULL auto_increment,
			`Tipo_Gestione` varchar(20) NOT NULL default '',
			`Tipo_Tributo` varchar(20) NOT NULL default '',
			`Data` date NOT NULL default '0000-00-00',
			`Comune` varchar(5) NOT NULL default '',
			`ID_Ufficio` varchar(20) NOT NULL default '',
			`Nome_Ufficio` varchar(100) NOT NULL default '',
			`CIG` varchar(20) NOT NULL default '',
			`CUP` varchar(20) NOT NULL default '',
			`Riferimento_Numero` varchar(100) NOT NULL default '',
			
			PRIMARY KEY  (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	function NomeComuneDaComuneCig ($cc)
	{
		$queryCerca = "SELECT Indirizzo2 FROM fatture_dati_sedi_comuni ";
		$queryCerca .= "WHERE CC = '" . $cc . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Indirizzo2'];
	}
	
	function CigGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM fatture_dati_cig ";
		$queryCerca .= "WHERE Tipo_Gestione = '" . $this->Tipo_Gestione . "' ";
		$queryCerca .= "AND Tipo_Tributo = '" . $this->Tipo_Tributo . "' ";
		$queryCerca .= "AND Comune = '" . $this->Comune . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateCig ($forzato = NULL)  //  forzato � INSERT o UPDATE
	{
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->CigGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->ID;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO CIG, errore ID"); return; }
		}
		else if ($forzato == "INSERT")
		{
			$progressivo = "";
		}
		
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "ID")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		
		if ($progressivo == NULL)  // non � presente
		{
			$verif = $this->insert_cig_locale($fields, $values);
			if ($verif == true)
				return "INSERT_DATO_OK";
			else
				return "INSERT_DATO_ERRORE";
		}
		else  //  � gi� presente
		{
			$verif = $this->update_cig_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_DATO_OK";
			else
				return "UPDATE_DATO_ERRORE";
		}
	}
	
	function insert_cig_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO fatture_dati_cig (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			return mysql_query($query);
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
	
	function update_cig_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldDato = new fatture_dati_cig($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldDato->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return TRUE;  // non updata nulla, perch� sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", " 
	
		$query = "UPDATE fatture_dati_cig SET $clause WHERE ID = '" . $key . "'";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else 
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldDato->$fields_to_update[$i] . ")";
			}
			echo "<br>" . $query;
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
}

class fatture_dati_sedi_comuni
{
	var $ID;
	var $CC;
	var $Data;
	var $Indirizzo1;
	var $Indirizzo2;
	var $Indirizzo3;
	var $Indirizzo4;
	var $Indirizzo5;
	var $Indirizzo6;
	var $Indirizzo7;
	var $Cod_Fisc;
	var $P_IVA;
	
	function fatture_dati_sedi_comuni( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM fatture_dati_sedi_comuni WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaDati = mysql_fetch_assoc($result);
		
		//echo "<br>" . $query;
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaDati as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "fatture_dati_sedi_comuni")
			{
				//echo " c'� " . $temp;
				return;
			}
		}
		
		$queryCreate = "
			CREATE TABLE `fatture_dati_sedi_comuni` (
			  `ID` int(9) unsigned NOT NULL auto_increment,
			  `CC` varchar(50) NOT NULL default '',
			  `Data` date NOT NULL default '0000-00-00',
			  `Indirizzo1` varchar(50) NOT NULL default '',
			  `Indirizzo2` varchar(50) NOT NULL default '',
			  `Indirizzo3` varchar(50) NOT NULL default '',
			  `Indirizzo4` varchar(8) NOT NULL default '',
			  `Indirizzo5` varchar(8) NOT NULL default '',
			  `Indirizzo6` varchar(20) NOT NULL default '',
			  `Indirizzo7` varchar(8) NOT NULL default '',
			  `Cod_Fisc` varchar(20) NOT NULL default '',
			  `P_IVA` varchar(20) NOT NULL default '',
			
			PRIMARY KEY  (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	function CercaDatiComune( $comune )
	{
		if ($comune == NULL) return;
	
		$query = "SELECT * FROM fatture_dati_sedi_comuni WHERE CC = '" . $comune . "' ORDER BY Data";
		$result = mysql_query($query);
		$rigaDati = mysql_fetch_assoc($result);
		
		//echo "<br>" . $query . " --> " . $rigaDati['ID'];
	
		return $rigaDati['ID'];
	}
	
	function ListaDatiComune ($scelta)
	{
		$query = "SELECT DISTINCT CC, Indirizzo2 FROM fatture_dati_sedi_comuni ";
		$query .= " ORDER BY CC";
		$result = safe_query($query);
		
		$select = "<option value=''></option>";
		while ($rigaDati = mysql_fetch_assoc($result))
		{
			//echo "<br>if $scelta == $rigaDati[CC]";
			if ($scelta == $rigaDati['CC']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaDati['CC'] . "' $selScelta title=''>";
			$select .= $rigaDati['Indirizzo2'] . "</option>";
		}
		return $select;
	}
	
	function ListaDatiTributoComune ($sceltacomune, $sceltatributo)
	{
		$query = "SELECT DISTINCT CC, Indirizzo2 FROM fatture_dati_sedi_comuni, fatture_dati_cig ";
		$query .= " WHERE CC = Comune AND ";
		$query .= " Tipo_Tributo = '" . $sceltatributo . "' ";
		$query .= " ORDER BY CC";
		$result = safe_query($query);
		
		$select = "<option value=''></option>";
		while ($rigaDati = mysql_fetch_assoc($result))
		{
			//echo "<br>if $scelta == $rigaDati[CC]";
			if ($sceltacomune == $rigaDati['CC']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaDati['CC'] . "' $selScelta title=''>";
			$select .= $rigaDati['Indirizzo2'] . "</option>";
		}
		return $select;
	}
	
	function NomeComuneDaCCFatture ($cc)
	{
		$queryCerca = "SELECT Indirizzo2 FROM fatture_dati_sedi_comuni ";
		$queryCerca .= "WHERE CC = '" . $cc . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Indirizzo2'];
	}
	
	function DatoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM fatture_dati_sedi_comuni ";
		$queryCerca .= "WHERE CC = '" . $this->CC . "' ";
		$queryCerca .= "AND Data = '" . $this->Data . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateDati ($forzato = NULL)  //  forzato � INSERT o UPDATE
	{
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->DatoGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->ID;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO FATTURA, errore ID"); return; }
		}
		else if ($forzato == "INSERT")
		{
			$progressivo = "";
		}
		
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "ID")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		
		if ($progressivo == NULL)  // non � presente
		{
			$verif = $this->insert_dato_locale($fields, $values);
			if ($verif == true)
				return "INSERT_DATO_OK";
			else
				return "INSERT_DATO_ERRORE";
		}
		else  //  � gi� presente
		{
			$verif = $this->update_dato_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_DATO_OK";
			else
				return "UPDATE_DATO_ERRORE";
		}
	}
	
	function insert_dato_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO fatture_dati_sedi_comuni (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			return mysql_query($query);
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
	
	function update_dato_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldDato = new fatture_dati_sedi_comuni($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldDato->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return TRUE;  // non updata nulla, perch� sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", " 
	
		$query = "UPDATE fatture_dati_sedi_comuni SET $clause WHERE ID = '" . $key . "'";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else 
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldDato->$fields_to_update[$i] . ")";
			}
			echo "<br>" . $query;
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
}

class fatture_dati_contratti
{
	var $ID;
	var $CC;
	var $Tributo;
	var $Data_Validita;
	var $Tipo;
	var $Numero;
	var $Data_Contratto;

	function fatture_dati_contratti( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM fatture_dati_contratti WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaDati = mysql_fetch_assoc($result);

		//echo "<br>" . $query;
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaDati as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}

	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "fatture_dati_contratti")
			{
				//echo " c'� " . $temp;
				return;
			}
		}

		$queryCreate = "
			CREATE TABLE `fatture_dati_contratti` (
			  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
			  `CC` varchar(5) NOT NULL DEFAULT '',
			  `Tributo` varchar(10) NOT NULL DEFAULT '',
			  `Data_Validita` date NOT NULL DEFAULT '0000-00-00',
			  `Tipo` varchar(10) NOT NULL DEFAULT '',
			  `Numero` varchar(20) NOT NULL DEFAULT '',
			  `Data_Contratto` date NOT NULL DEFAULT '0000-00-00',
			  PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	function NomeComuneDaCCcontratti ($cc)
	{
		$queryCerca = "SELECT Indirizzo2 FROM fatture_dati_sedi_comuni ";
		$queryCerca .= "WHERE CC = '" . $cc . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Indirizzo2'];
	}

	/*function CercaDatiContratto( $comune )
	{
		if ($comune == NULL) return;

		$query = "SELECT * FROM fatture_dati_contratti WHERE CC = '" . $comune . "' ORDER BY Data_Contratto";
		$result = mysql_query($query);
		$rigaDati = mysql_fetch_assoc($result);

		//echo "<br>" . $query . " --> " . $rigaDati['ID'];

		return $rigaDati['ID'];
	}*/
	
	function ListaTipiContratto ($scelta)
	{
		$selectContratto = $selectDeliberaGC = $selectDeliberaGM = $selectDetermina = "";
		$selectConvenzione = $selectDisciplinare = "";
		switch ($scelta)
		{
			case "CONTR": $selectContratto = " selected "; break;
			case "DELGC": $selectDeliberaGC = " selected "; break;
			case "DELGM": $selectDeliberaGM = " selected "; break;
			case "DETER": $selectDetermina = " selected "; break;
			case "CONVE": $selectConvenzione = " selected "; break;
			case "DISCI": $selectDisciplinare = " selected "; break;
		}
		
		$select = "<option value=''></option>";
		$select .= "<option value='CONTR' $selectContratto>Contratto</option>\n";
		$select .= "<option value='DELGC' $selectDeliberaGC>Delibera GC</option>\n";
		$select .= "<option value='DELGM' $selectDeliberaGM>Delibera GM</option>\n";
		$select .= "<option value='DETER' $selectDetermina>Determina</option>\n";
		$select .= "<option value='CONVE' $selectConvenzione>Convenzione</option>\n";
		$select .= "<option value='DISCI' $selectDisciplinare>Disciplinare</option>\n";
		
		return $select;
	}

	function ListaDatiContratti ($comune, $tributo, $scelta)
	{
		if ($comune == "")
		{
			return "";
		}
		$query = "SELECT ID, Tipo, Numero, Data_Contratto FROM fatture_dati_contratti ";
		$query .= " WHERE CC = '$comune' ";
		$query .= " AND Tributo = '$tributo' ";
		$query .= " ORDER BY CC";
		$result = safe_query($query);

		$select = "<option value=''></option>";
		while ($rigaDati = mysql_fetch_assoc($result))
		{
			//echo "<br>if $scelta == $rigaDati[CC]";
			if ($scelta == $rigaDati['ID']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaDati['ID'] . "' $selScelta title=''>";
			$select .= $rigaDati['Tipo'] . " n." . $rigaDati['Numero'] . " del " . from_mysql_date($rigaDati['Data_Contratto']) . "</option>";
		}
		return $select;
	}
	
	function ArrayDatiContratti ($comune, $tributo)
	{
		$query = "SELECT * FROM fatture_dati_contratti ";
		$query .= " WHERE CC = '$comune' ";
		$query .= " AND Tributo = '$tributo' ";
		$query .= " ORDER BY ID";
		$result = safe_query($query);
		
		$arrayID = array();
		$arrayTipiTributi = array();
		$arrayTipiContratto = array();
		$arrayNumeri = array();
		$arrayDate = array();
		if (mysql_num_rows($result))
		{
			while ($rigaDati = mysql_fetch_assoc($result))
			{
				$arrayID[] = $rigaDati['ID'];
				$arrayTipiTributi[] = $rigaDati['Tributo'];
				$arrayTipiContratto[] = $rigaDati['Tipo'];
				$arrayNumeri[] = $rigaDati['Numero'];
				$arrayDate[] = from_mysql_date($rigaDati['Data_Contratto']);
			}
		}
		else 
		{
			$arrayID[] = "";
			$arrayTipiTributi[] = "";
			$arrayTipiContratto[] = "";
			$arrayNumeri[] = "";
			$arrayDate[] = "";
		}
		$arrayGlob = array ();
		$arrayGlob[0] = $arrayID;
		$arrayGlob[1] = $arrayTipiTributi;
		$arrayGlob[2] = $arrayTipiContratto;
		$arrayGlob[3] = $arrayNumeri;
		$arrayGlob[4] = $arrayDate;
		return $arrayGlob;
	}
	
	function ContrattoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM fatture_dati_contratti ";
		$queryCerca .= "WHERE Tipo = '" . $this->Tipo . "' ";
		$queryCerca .= "AND Numero = '" . $this->Numero . "' ";
		$queryCerca .= "AND Tributo = '" . $this->Tributo . "' ";
		$queryCerca .= "AND CC = '" . $this->CC . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateContratto ($forzato = NULL)  //  forzato � INSERT o UPDATE
	{
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->ContrattoGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->ID;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO CIG, errore ID"); return; }
		}
		else if ($forzato == "INSERT")
		{
			$progressivo = "";
		}
		
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "ID")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		
		if ($progressivo == NULL)  // non � presente
		{
			$verif = $this->insert_contratto_locale($fields, $values);
			if ($verif == true)
				return "INSERT_DATO_OK";
			else
				return "INSERT_DATO_ERRORE";
		}
		else  //  � gi� presente
		{
			$verif = $this->update_contratto_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_DATO_OK";
			else
				return "UPDATE_DATO_ERRORE";
		}
	}
	
	function insert_contratto_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO fatture_dati_contratti (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			return mysql_query($query);
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
	
	function update_contratto_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldDato = new fatture_dati_contratti($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldDato->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return TRUE;  // non updata nulla, perch� sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", " 
	
		$query = "UPDATE fatture_dati_contratti SET $clause WHERE ID = '" . $key . "'";
		
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else 
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldDato->$fields_to_update[$i] . ")";
			}
			echo "<br>" . $query;
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
}

class fatture_invii
{
	var $ID;
	var $Fattura_ID;
	var $Identificativo_SDI;
	var $Data_Invio;
	
	function fatture_invii( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM fatture_invii WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaDati = mysql_fetch_assoc($result);
	
		//echo "<br>" . $query;
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaDati as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "fatture_invii")
			{
				//echo " c'� " . $temp;
				return;
			}
		}
	
		$queryCreate = "
			CREATE TABLE `fatture_invii` (
			  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
			  `Fattura_ID` int(9) unsigned NOT NULL,
			  `Identificativo_SDI` varchar(20) NOT NULL DEFAULT '',
			  `Data_Invio` date NOT NULL DEFAULT '0000-00-00',
			  PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	function InvioGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM fatture_invii ";
		$queryCerca .= "WHERE Fattura_ID = '" . $this->Fattura_ID . "' ";
		$queryCerca .= "AND Identificativo_SDI = '" . $this->Identificativo_SDI . "' ";
		$queryCerca .= "AND Data_Invio = '" . $this->Data_Invio . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}

    function CercaInvioDaNumFattura ($numFattura)
    {
        $queryCerca = "SELECT fatture_invii.ID FROM fatture_invii ";
        $queryCerca .= "JOIN fatture_generali ON fatture_invii.Fattura_ID = fatture_generali.ID ";
        $queryCerca .= "WHERE fatture_generali.Fat_Numero = '" . $numFattura . "' ";
        $resCerca = mysql_query($queryCerca);
        //echo "<br>" . $queryCerca;
        $rigaCerca = mysql_fetch_assoc($resCerca);
        return $rigaCerca['ID'];
    }

	function CercaInvioDaFattura ($idFattura)
	{
		$queryCerca = "SELECT ID FROM fatture_invii ";
		$queryCerca .= "WHERE Fattura_ID = '" . $idFattura . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function CercaInvioDaSDI ($numSdi)
	{
		$queryCerca = "SELECT ID FROM fatture_invii ";
		$queryCerca .= "WHERE Identificativo_SDI = '" . $numSdi . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateInvio ($forzato = NULL)  //  forzato � INSERT o UPDATE
	{
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->InvioGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->ID;
			if ($progressivo == NULL) { /*alert ("In UPDATE FORZATO INVIO, errore ID");*/ return; }
		}
		else if ($forzato == "INSERT")
		{
			$progressivo = "";
		}
	
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "ID")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		
		if ($progressivo == NULL)  // non � presente
		{
			$verif = $this->insert_invio_locale($fields, $values);
			if ($verif == true)
				return "INSERT_DATO_OK";
			else
				return "INSERT_DATO_ERRORE";
		}
		else  //  � gi� presente
		{
			$verif = $this->update_invio_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_DATO_OK";
			else
				return "UPDATE_DATO_ERRORE";
		}
	}
	
	function insert_invio_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO fatture_invii (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			return mysql_query($query);
		}
		else
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_insert[$i] . " = '" . $values_to_insert[$i] . "'";
			}
			echo "<br>" . $query;
			return true;
		}
	}
	
	function update_invio_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);

		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;

		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;

		$myOldDato = new fatture_invii($key);

		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldDato->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return TRUE;  // non updata nulla, perch� sono tutti uguali

		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "

		$query = "UPDATE fatture_invii SET $clause WHERE ID = '" . $key . "'";

		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldDato->$fields_to_update[$i] . ")";
			}
			echo "<br>" . $query;
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
	
}

class fatture_email
{
	var $ID;
	//var $Fattura_ID;
	var $Identificativo_SDI;
	var $Data_Ricezione;
	var $Esito;
	var $Tipo_Messaggio;
	var $Nome_File_Email;
	var $Nome_Fattura;
	
	function fatture_email( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM fatture_email WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaDati = mysql_fetch_assoc($result);
	
		//echo "<br>" . $query;
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaDati as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "fatture_email")
			{
				//echo " c'� " . $temp;
				return;
			}
		}
	
		$queryCreate = "
			CREATE TABLE `fatture_email` (
			  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
			  `Identificativo_SDI` varchar(20) NOT NULL DEFAULT '',
			  `Data_Ricezione` date NOT NULL DEFAULT '0000-00-00',
			  `Esito` varchar(20) NOT NULL DEFAULT '',
			  `Tipo_Messaggio` varchar(50) NOT NULL DEFAULT '',
			  `Nome_File_Email` varchar(100) NOT NULL DEFAULT '',
			  `Nome_Fattura` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	function EmailGiaPresente ()
	{
		if ($this->Data_Ricezione == "") $checkData = "0000-00-00";
		else $checkData = $this->Data_Ricezione;
		
		/*$queryCerca = "SELECT ID FROM fatture_email ";
		$queryCerca .= "WHERE Fattura_ID = '" . $checkId . "' ";
		$queryCerca .= "AND Identificativo_SDI = '" . $this->Identificativo_SDI . "' ";
		$queryCerca .= "AND Data_Ricezione = '" . $checkData . "' ";
		$queryCerca .= "AND Tipo_Messaggio = '" . $this->Tipo_Messaggio . "' ";*/
		$queryCerca = "SELECT ID FROM fatture_email ";
		$queryCerca .= "WHERE Identificativo_SDI = '" . $this->Identificativo_SDI . "' ";
		$queryCerca .= "AND Data_Ricezione = '" . $checkData . "' ";
		$queryCerca .= "AND Tipo_Messaggio = '" . $this->Tipo_Messaggio . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		/*if ($_SESSION['CC_User'] == "***+")
		{
			if ($rigaCerca['ID'] == "") echo "<br><br>" . $queryCerca;
		}*/
		return $rigaCerca['ID'];
	}
	
	function ArrayTutteEmail ()
	{
		$queryCerca = "SELECT ID FROM fatture_email ";
		$queryCerca .= " ORDER BY Identificativo_SDI ";
		$resCerca = mysql_query($queryCerca);
		$arrayEmail = array();
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			$arrayEmail[] = $rigaCerca['ID'];
		}
		return $arrayEmail;
	}
	
	/*function CercaEmailDaFattura ($idFattura)
	{
		$queryCerca = "SELECT ID FROM fatture_email ";
		$queryCerca .= "WHERE Fattura_ID = '" . $idFattura . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$arrayEmail = array();
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			$arrayEmail[] = $rigaCerca['ID'];
		}
		return $arrayEmail;
	}*/
	
	function CercaListaEmailDaSDI ($fatSdi)
	{
		$queryCerca = "SELECT Tipo_Messaggio, Data_Ricezione, Esito FROM fatture_email ";
		$queryCerca .= "WHERE Identificativo_SDI = '" . $fatSdi . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		//$optionEmail = "<option value=''></option>\n";
		$optionEmail = "";
		$optionNotifica = "";
		$optionDecorrenza = "";
		$optionMancata = "";
		$optionConsegna = "";
		$optionScarto = "";
		$optionErrore = "";
		$statoFinale = "";
		$icona = "/gitco2/immagini/puntointerrogativo.jpg";
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			switch ($rigaCerca['Tipo_Messaggio'])
			{
				case "Ricevuta_di_consegna":
					$optionConsegna .= "<option value='RDC'>";
					$optionConsegna .= "RICEVUTA in data " . from_mysql_date($rigaCerca['Data_Ricezione']);
					$optionConsegna .= "</option>\n";
					break;
				case "Notifica_esito":
					$optionNotifica .= "<option value='NE'>";
					$optionNotifica .= $rigaCerca['Esito'] . " in data " . from_mysql_date($rigaCerca['Data_Ricezione']);
					$optionNotifica .= "</option>\n";
					$statoFinale = $rigaCerca['Esito'];
					break;
				case "Notifica_di_mancata_consegna":
					$optionMancata .= "<option value='NDMC'>";
					$optionMancata .= "MANCATA CONSEGNA";
					$optionMancata .= "</option>\n";
					break;
				case "Notifica_di_decorrenza_termini":
					$optionDecorrenza .= "<option value='NDDT'>";
					$optionDecorrenza .= "DECORRENZA TERMINI in data " . from_mysql_date($rigaCerca['Data_Ricezione']);
					$optionDecorrenza .= "</option>\n";
					break;
				case "Notifica_di_scarto":
					$optionScarto .= "<option value='NDS'>";
					$optionScarto .= "SCARTATA in data " . from_mysql_date($rigaCerca['Data_Ricezione']);
					$optionScarto .= "</option>\n";
					break;
				default:
					$optionErrore .= "<option value='ALTRO'>";
					$optionErrore .= "ERRORE NON SO";
					$optionErrore .= "</option>\n";
					break;
			}
		}
		if ($optionErrore != "") $optionEmail .= $optionErrore;
		if ($optionScarto != "") $optionEmail .= $optionScarto;
		if ($optionNotifica != "") $optionEmail .= $optionNotifica;
		if ($optionDecorrenza != "") $optionEmail .= $optionDecorrenza;
		if ($optionMancata != "") $optionEmail .= $optionMancata;
		if ($optionConsegna != "") $optionEmail .= $optionConsegna;
		
		if ($optionErrore != "") { $icona = "/gitco2/immagini/rossotempjpg.JPG"; $statoFinale = "ERRORE"; }
		else if ($optionScarto != "") { $icona = "/gitco2/immagini/rossotempjpg.JPG"; $statoFinale = "SCARTATA"; }
		else if ($optionNotifica != "")
		{
			if ($statoFinale == "ACCETTATA") $icona = "/gitco2/immagini/verdejpg.JPG";
			else if ($statoFinale == "RIFIUTATA") $icona = "/gitco2/immagini/rossojpg.JPG";
			else $icona = "/gitco2/immagini/rossojpg.JPG";
		}
		else if ($optionDecorrenza != "") { $icona = "/gitco2/immagini/verdetempojpg.JPG"; $statoFinale = "DECORRENZA TERMINI"; }
		else if ($optionMancata != "") { $icona = "/gitco2/immagini/rossojpg.JPG"; $statoFinale = "MANCATA CONSEGNA"; }
		else if ($optionConsegna != "") { $icona = "/gitco2/immagini/giallojpg.JPG"; $statoFinale = "CONSEGNATA"; }
		
		return array($optionEmail, $icona, $statoFinale);
	}
	
	function StatoEmail ()
	{
		$arrayStato = array();
		$arrayStato['STATO_EMAIL'] = "";
		$arrayStato['ICONA_EMAIL'] = "";
		switch ($this->Tipo_Messaggio)
		{
			case "Ricevuta_di_consegna":
				$arrayStato['STATO_EMAIL'] = "RICEVUTA";
				$arrayStato['ICONA_EMAIL'] = "/gitco2/immagini/giallojpg.JPG";
				break;
			case "Notifica_esito":
				if ($this->Esito == "ACCETTATA")
				{
					$arrayStato['STATO_EMAIL'] = "ACCETTATA";
					$arrayStato['ICONA_EMAIL'] = "/gitco2/immagini/verdejpg.JPG";
				}
				else if ($this->Esito == "RIFIUTATA")
				{
					$arrayStato['STATO_EMAIL'] = "RIFIUTATA";
					$arrayStato['ICONA_EMAIL'] = "/gitco2/immagini/rossojpg.JPG";
				}
				break;
			case "Notifica_di_mancata_consegna":
				$arrayStato['STATO_EMAIL'] = "NON CONSEGNATA";
				$arrayStato['ICONA_EMAIL'] = "/gitco2/immagini/rossotempojpg.JPG";
				break;
			case "Notifica_di_decorrenza_termini":
				$arrayStato['STATO_EMAIL'] = "DECORRENZA TERMINI";
				$arrayStato['ICONA_EMAIL'] = "/gitco2/immagini/verdetempojpg.JPG";
				break;
			case "Notifica_di_scarto":
				$arrayStato['STATO_EMAIL'] = "SCARTATA";
				$arrayStato['ICONA_EMAIL'] = "/gitco2/immagini/rossojpg.JPG";
				break;
			default:
				$arrayStato['STATO_EMAIL'] = "SCARTATA";
				$arrayStato['ICONA_EMAIL'] = "/gitco2/immagini/puntointerrogativo.jpg";
				break;
		}
		
		return $arrayStato;
	}
	
	function CercaEmailDaSDI ($numSdi)
	{
		$queryCerca = "SELECT ID FROM fatture_email ";
		$queryCerca .= "WHERE Identificativo_SDI = '" . $numSdi . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		$arrayEmail = array();
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			$arrayEmail[] = $rigaCerca['ID'];
		}
		return $arrayEmail;
	}
	
	function InsertUpdateEmail ($forzato = NULL)  //  forzato � INSERT o UPDATE
	{
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->EmailGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->ID;
			if ($progressivo == NULL) { /*alert ("In UPDATE FORZATO INVIO, errore ID");*/ return; }
		}
		else if ($forzato == "INSERT")
		{
			$progressivo = "";
		}
	
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "ID")
			{
				//if ($key == "Fattura_ID" && $value == "") $value = "0";
				if ($key == "Data_Ricezione" && $value == "") $value = "0000-00-00";
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		
		if ($progressivo == NULL)  // non � presente
		{
			$verif = $this->insert_email_locale($fields, $values);
			if ($verif == true)
				return "INSERT_DATO_OK";
			else
				return "INSERT_DATO_ERRORE";
		}
		else  //  � gi� presente
		{
			$verif = $this->update_email_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_DATO_OK";
			else
				return "UPDATE_DATO_ERRORE";
		}
	}
	
	function insert_email_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO fatture_email (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			return mysql_query($query);
		}
		else
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_insert[$i] . " = '" . $values_to_insert[$i] . "'";
			}
			echo "<br>" . $query . "<br>";
			return true;
		}
	}
	
	function update_email_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);

		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;

		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;

		$myOldDato = new fatture_email($key);

		$clause = "";
		/*$PathFatture = "/archivio/Fatture";
		$PathCompletoFatture = $_SERVER['DOCUMENT_ROOT'] . $PathFatture;*/
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldDato->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
					/*if ($fields_to_update[$i] == "Nome_File_Email")
					{
						echo "<br><br>rename $PathCompletoFatture/PEC/" . $myOldDato->$fields_to_update[$i] . "  -> " . $values_to_update[$i] . "<br><br>";
						rename ($PathCompletoFatture . "/PEC/" . $myOldDato->$fields_to_update[$i], $PathCompletoFatture . "/PEC/" . $values_to_update[$i]);
					}*/
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return;
			
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE fatture_email SET $clause WHERE ID = '" . $key . "'";

		//if ($_SESSION['CC_User'] != "***+")
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldDato->$fields_to_update[$i] . ")";
			}
			echo "<br>" . $query . "<br>";
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
	
}
?>