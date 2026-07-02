<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once LIBRERIE . "/aiuto.php";
include_once CLASSI . "/targhe_estere.php";

/*
 CREATE TABLE `testo_modulo_126_bis` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Lingua_ID` int(4) unsigned NOT NULL,
  `Comune` varchar(5) ,
  `Testo_1` text CHARACTER SET utf8,
  `Testo_2` text CHARACTER SET utf8,
  `Testo_3` text CHARACTER SET utf8,
  `Testo_4` text CHARACTER SET utf8,
  `Testo_5` text CHARACTER SET utf8,
  `Testo_6` text CHARACTER SET utf8,
  `Testo_7` text CHARACTER SET utf8,
  `Testo_8` text CHARACTER SET utf8,
  `Testo_9` text CHARACTER SET utf8,
  `Testo_10` text CHARACTER SET utf8,
  `Testo_11` text CHARACTER SET utf8,
  `Testo_12` text CHARACTER SET utf8,
  `Testo_13` text CHARACTER SET utf8,
  `Testo_14` text CHARACTER SET utf8,
  `Testo_15` text CHARACTER SET utf8,
  `Testo_16` text CHARACTER SET utf8,
  `Data_Valida_Da` date DEFAULT '0000-00-00',
  `Data_Valida_A` date DEFAULT '0000-00-00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class testo_modulo_126_bis
{
	public $ID;
	public $Lingua_ID;
	public $Comune;
	public $Testo_1;
	public $Testo_2;
	public $Testo_3;
	public $Testo_4;
	public $Testo_5;
	public $Testo_6;
	public $Testo_7;
	public $Testo_8;
	public $Testo_9;
	public $Testo_10;
	public $Testo_11;
	public $Testo_12;
	public $Testo_13;
	public $Testo_14;
	public $Testo_15;
	public $Testo_16;
	public $Testo_17;
	public $Testo_18;
	public $Testo_19;
	public $Testo_20;
	
	public $Data_Valida_Da;
	public $Data_Valida_A;
	
	public $Coll_LLingua;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM testo_modulo_126_bis WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaModulo = mysql_fetch_assoc($result);

		foreach ($rigaModulo as $key => $value)
		{
			$this->$key = $value;
		}
		
		$lingua = new targhe_estere_lista_lingue($this->Lingua_ID);
		$this->Coll_LLingua = $lingua->Linguaggio;
	}
	
	/*function TestoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_modelli ";
		$queryCerca .= "WHERE Comune = '" . $this->Comune . "' ";
		$queryCerca .= "AND Accertatore_ID = '" . $this->Accertatore_ID . "' ";
		$queryCerca .= "AND Genere_Infrazione = '" . $this->Genere_Infrazione . "'";
		$queryCerca .= "AND Rilevatore_ID = '" . $this->Rilevatore_ID . "'";
		$queryCerca .= "AND Localita_Violazione = '" . $this->Localita_Violazione . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Tar_Progr'];
	}*/
	
	public function TuttiModuliDiComune ($comune, $anno)
	{
		$queryTutti = "SELECT ID ";
		$queryTutti .= " FROM testo_modulo_126_bis ";
		$queryTutti .= " WHERE Comune = '" . $comune . "' AND ";
		
		if ($anno != "")
		{
			$queryTutti .= " ( ";
			$queryTutti .= "       (SUBSTR(Data_Valida_Da FROM 1 FOR 4) != '0000' AND SUBSTR(Data_Valida_Da FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) != '0000-00-00' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Data_Valida_Da FROM 1 FOR 4) = '0000' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Data_Valida_Da FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) = '0000') ";
			$queryTutti .= " ) AND ";
		}
		$queryTutti .= " 1 ";
		//echo "<br>" . $queryTutti;
		$resTutti = mysql_query($queryTutti);
		$arrayTutti = array();
		while ($rigaLingua = mysql_fetch_assoc($resTutti))
		{
			$arrayTutti[] = $rigaLingua['ID'];
		}
		return $arrayTutti;
	}
	
	public function ModuloInLingua ($comune, $linguaggio)  //  1, 2, 3, 4, 5...
	{
		$queryInLingua = "SELECT ID ";
		$queryInLingua .= " FROM testo_modulo_126_bis ";
		$queryInLingua .= " WHERE Comune = '" . $comune . "' AND ";
		$queryInLingua .= " Lingua_ID = '" . $linguaggio . "'  ";
		//echo "<br>" . $queryTutti;
		$resInLingua = mysql_query($queryInLingua);
		while ($rigaLingua = mysql_fetch_assoc($resInLingua))
		{
			return $rigaLingua['ID'];  //  dovrebbe essere solo 1!!!
		}
		return null;
	}
	
	function InsertUpdateModulo126Bis ()
	{
		$insUpd = "";
		$fields = array();
		$values = array();
	
		//$this->ID = $this->ModelloGiaPresente();
		
		if ($this->ID == NULL)
		{
			$insUpd = "INSERT";
		}
		else
		{
			$insUpd = "UPDATE";
		}
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "ID"
					&& $campo != "Coll_LLingua")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
		
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_testo_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_testo_locale($this->ID, $fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "UPDATE_OK"; break;
				case false: $risposta = "UPDATE_ERROR"; break;
				default: break;
			}
		}
		else $risposta = "INSERT_ERROR";
		return $risposta;
	}
	
	public function insert_testo_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO testo_modulo_126_bis (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= '"' . $values_to_insert[$i] . '"';
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_testo_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldTesto = new testo_modulo_126_bis($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldTesto->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . '="' .$values_to_update[$i]. '" , ';
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE testo_modulo_126_bis SET $clause WHERE ID = '" . $key . "'";
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
}



/*
 * CREATE TABLE `testo_esteri_solleciti` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Lingua_ID` int(4) unsigned NOT NULL,
  `Comune` varchar(5) ,
  `Testo_1` text CHARACTER SET utf8,
  `Testo_2` text CHARACTER SET utf8,
  `Testo_3` text CHARACTER SET utf8,
  `Testo_4` text CHARACTER SET utf8,
  `Testo_5` text CHARACTER SET utf8,
  `Testo_6` text CHARACTER SET utf8,
  `Testo_7` text CHARACTER SET utf8,
  `Testo_8` text CHARACTER SET utf8,
  `Testo_9` text CHARACTER SET utf8,
  `Testo_10` text CHARACTER SET utf8,
  `Testo_11` text CHARACTER SET utf8,
  `Testo_12` text CHARACTER SET utf8,
  `Testo_13` text CHARACTER SET utf8,
  `Testo_14` text CHARACTER SET utf8,
  `Testo_15` text CHARACTER SET utf8,
  `Testo_16` text CHARACTER SET utf8,
  `Data_Valida_Da` date DEFAULT '0000-00-00',
  `Data_Valida_A` date DEFAULT '0000-00-00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class testo_esteri_solleciti
{
	public $ID;
	public $Lingua_ID;
	public $Comune;
	public $Testo_1;
	public $Testo_2;
	public $Testo_3;
	public $Testo_4;
	public $Testo_5;
	public $Testo_6;
	public $Testo_7;
	public $Testo_8;
	public $Testo_9;
	public $Testo_10;
	public $Testo_11;
	public $Testo_12;
	public $Testo_13;
	public $Testo_14;
	public $Testo_15;
	public $Testo_16;
	
	public $Data_Valida_Da;
	public $Data_Valida_A;
	
	public $Coll_LLingua;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM testo_esteri_solleciti WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaModulo = mysql_fetch_assoc($result);

		foreach ($rigaModulo as $key => $value)
		{
			$this->$key = $value;
		}
		
		$lingua = new targhe_estere_lista_lingue($this->Lingua_ID);
		$this->Coll_LLingua = $lingua->Linguaggio;
	}
	
	/*function TestoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_modelli ";
		$queryCerca .= "WHERE Comune = '" . $this->Comune . "' ";
		$queryCerca .= "AND Accertatore_ID = '" . $this->Accertatore_ID . "' ";
		$queryCerca .= "AND Genere_Infrazione = '" . $this->Genere_Infrazione . "'";
		$queryCerca .= "AND Rilevatore_ID = '" . $this->Rilevatore_ID . "'";
		$queryCerca .= "AND Localita_Violazione = '" . $this->Localita_Violazione . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Tar_Progr'];
	}*/
	
	public function TuttiModuliDiComune ($comune, $anno)
	{
		$queryTutti = "SELECT ID ";
		$queryTutti .= " FROM testo_esteri_solleciti ";
		$queryTutti .= " WHERE Comune = '" . $comune . "' AND ";
		
		if ($anno != "")
		{
			$queryTutti .= " ( ";
			$queryTutti .= "       (SUBSTR(Data_Valida_Da FROM 1 FOR 4) != '0000' AND SUBSTR(Data_Valida_Da FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) != '0000-00-00' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Data_Valida_Da FROM 1 FOR 4) = '0000' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Data_Valida_Da FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Data_Valida_A FROM 1 FOR 4) = '0000') ";
			$queryTutti .= " ) AND ";
		}
		$queryTutti .= " 1 ";
		//echo "<br>" . $queryTutti;
		$resTutti = mysql_query($queryTutti);
		$arrayTutti = array();
		while ($rigaLingua = mysql_fetch_assoc($resTutti))
		{
			$arrayTutti[] = $rigaLingua['ID'];
		}
		return $arrayTutti;
	}
	
	public function ModuloInLingua ($comune, $linguaggio)  //  1, 2, 3, 4, 5...
	{
		$queryInLingua = "SELECT ID ";
		$queryInLingua .= " FROM testo_esteri_solleciti ";
		$queryInLingua .= " WHERE Comune = '" . $comune . "' AND ";
		$queryInLingua .= " Lingua_ID = '" . $linguaggio . "'  ";
		//echo "<br>" . $queryTutti;
		$resInLingua = mysql_query($queryInLingua);
		while ($rigaLingua = mysql_fetch_assoc($resInLingua))
		{
			return $rigaLingua['ID'];  //  dovrebbe essere solo 1!!!
		}
		return null;
	}
	
	function InsertUpdateSollecito ()
	{
		$insUpd = "";
		$fields = array();
		$values = array();
	
		//$this->ID = $this->ModelloGiaPresente();
		
		if ($this->ID == NULL)
		{
			$insUpd = "INSERT";
		}
		else
		{
			$insUpd = "UPDATE";
		}
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "ID"
					&& $campo != "Coll_LLingua")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
		
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_sollecito_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_sollecito_locale($this->ID, $fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "UPDATE_OK"; break;
				case false: $risposta = "UPDATE_ERROR"; break;
				default: break;
			}
		}
		else $risposta = "INSERT_ERROR";
		return $risposta;
	}
	
	public function insert_sollecito_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO testo_esteri_solleciti (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= '"' . $values_to_insert[$i] . '"';
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		//$_SESSION['CC_User'] != "***+"
		if (1)
		{
			if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
	
	public function update_sollecito_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldSollecito = new testo_esteri_solleciti($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldSollecito->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . '="' .$values_to_update[$i]. '" , ';
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE testo_esteri_solleciti SET $clause WHERE ID = '" . $key . "'";
		
		//$_SESSION['CC_User'] != "***+"
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
				echo "<br>" . $fields_to_update[$i] . " = '" . $values_to_update[$i] . "' (" . $myOldSollecito->$fields_to_update[$i] . ")";
			}
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return true;
		}
	}
}