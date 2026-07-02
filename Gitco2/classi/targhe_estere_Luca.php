<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once LIBRERIE . "/aiuto.php";
include_once CLASSI . "/motivi_mancata_contestazione_cds.php";
include_once CLASSI . "/testo_modulo_126_bis.php";

/*
 CREATE TABLE `targhe_estere_modelli` (
 		`ID` int(11) unsigned NOT NULL auto_increment,
 		`Comune` varchar(20) NOT NULL default '',
 		`Accertatore_ID` int(11) unsigned default '0',
 		`Genere_Infrazione` varchar(30) default '',
 		`Rilevatore_ID` int(11) unsigned default '0',
 		`Localita_Violazione` longtext DEFAULT '',
 		`Spese` int(11) unsigned default '0',
 		PRIMARY KEY  (`ID`)
 ) ENGINE=innoDb DEFAULT CHARSET=latin1
*/

class targhe_estere_modelli
{
	public $ID;
	public $Comune;
	public $Accertatore_ID;
	public $Genere_Infrazione;
	public $Rilevatore_ID;
	public $Localita_Violazione;
	public $Spese;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM targhe_estere_modelli WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaModello = mysql_fetch_assoc($result);

		foreach ($rigaModello as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}
	
	function ModelloGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_modelli ";
		$queryCerca .= "WHERE Comune = '" . $this->Comune . "' ";
		$queryCerca .= "AND Accertatore_ID = '" . $this->Accertatore_ID . "' ";
		$queryCerca .= "AND Genere_Infrazione = '" . $this->Genere_Infrazione . "'";
		$queryCerca .= "AND Rilevatore_ID = '" . $this->Rilevatore_ID . "'";
		$queryCerca .= "AND Localita_Violazione = '" . $this->Localita_Violazione . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateModello ()
	{
		$insUpd = "";
		$fields = array();
		$values = array();
	
		$this->ID = $this->ModelloGiaPresente();
		$this->Spese = conv_num($this->Spese);
	
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
			if (isset($campo) && $campo != "ID")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_modello_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_modello_locale($this->ID, $fields, $values);
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
	
	public function insert_modello_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_modelli (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_modello_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldModello = new targhe_estere_modelli($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldModello->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_modelli SET $clause WHERE ID = '" . $key . "'";
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
}


/*
CREATE TABLE `targhe_estere_lista_lingue` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Linguaggio` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class targhe_estere_lista_lingue
{
	public $ID;
	public $Linguaggio;
	
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM targhe_estere_lista_lingue WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaLingua = mysql_fetch_assoc($result);
		
		foreach ($rigaLingua as $key => $value)
		{
			$this->$key = $value;
		}
	}
}


/*
 * CREATE TABLE `testo_richieste_dati` (
  `Lin_Progr` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Lin_ID_Lingua` int(11) unsigned NOT NULL,
  `Lin_Linguaggio` varchar(20) NOT NULL DEFAULT '',
  `Lin_Comune` varchar(5) NOT NULL DEFAULT '',
  `Lin_Anno` int(5) NOT NULL,
  `Lin_Richiesta_Numero` varchar(50) DEFAULT '',
  `Lin_Spettabile` varchar(50) DEFAULT '',
  `Lin_Oggetto_Titolo` varchar(20) DEFAULT '',
  `Lin_Oggetto_Lettera` text CHARACTER SET utf8,
  `Lin_Intestazione_Lettera` varchar(200) DEFAULT '',
  `Lin_Testo_Lettera` longtext CHARACTER SET utf8,
  `Lin_Targa_Tabella` varchar(20) DEFAULT '',
  `Lin_Data_Tabella` varchar(20) DEFAULT '',
  `Lin_Ora_Tabella` varchar(20) DEFAULT '',
  `Lin_Luogo_Tabella` varchar(20) DEFAULT '',
  `Lin_Limite_Tabella` varchar(20) DEFAULT '',
  `Lin_Articolo_Tabella` varchar(20) DEFAULT '',
  `Lin_Tipo_Tabella` varchar(20) DEFAULT '',
  `Lin_Testo_Chiusura` text CHARACTER SET utf8,
  `Lin_Testo_Sarida` text CHARACTER SET utf8,
  `Lin_Firma_Riga1` text CHARACTER SET utf8,
  `Lin_Firma_Riga2` text CHARACTER SET utf8,
  `Lin_Stazione_Polizia` text,
  `Lin_Data_Validita_Da` date DEFAULT '0000-00-00',
  `Lin_Data_Validita_A` date DEFAULT '0000-00-00',
  PRIMARY KEY (`Lin_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class testo_richieste_dati
{
	public $Lin_Progr;
	public $Lin_ID_Lingua;
	public $Lin_Linguaggio;
	public $Lin_Comune;
	
	public $Lin_Richiesta_Numero;
	public $Lin_Spettabile;
	public $Lin_Oggetto_Titolo;
	public $Lin_Oggetto_Lettera;
	public $Lin_Intestazione_Lettera;
	public $Lin_Testo_Lettera;
	
	public $Lin_Targa_Tabella;
	public $Lin_Data_Tabella;
	public $Lin_Ora_Tabella;
	public $Lin_Luogo_Tabella;
	public $Lin_Limite_Tabella;
	public $Lin_Articolo_Tabella;
	public $Lin_Tipo_Tabella;
	
	public $Lin_Testo_Chiusura;
	public $Lin_Testo_Sarida;
	public $Lin_Firma_Riga1;
	public $Lin_Firma_Riga2;
	public $Lin_Stazione_Polizia;
	
	public $Lin_Data_Validita_Da;
	public $Lin_Data_Validita_A;
	
	public $Coll_Lista_Lingua = NULL;
	
	public function __construct( $progr )  //  , $lingua = null, $comune = null, $anno = null)
	{
		if ($progr != NULL)
		{
			$query = "SELECT * FROM testo_richieste_dati WHERE Lin_Progr = '" . $progr . "'";
		}
		/*else if ($lingua != NULL && $comune != null && $anno != null)
		{
			$query = "SELECT * FROM testo_richieste_dati ";
			$query .= " WHERE Lin_ID_Lingua = " . $lingua;
			//$query .= " AND Lin_Comune = '" . $comune . "' ";
			//$query .= " AND Lin_Anno = '" . $anno . "' ";
		}*/
		else return;
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) != 0)
		{
			$rigaLingua = mysql_fetch_assoc($result);
			foreach ($rigaLingua as $key => $value)
			{
				$this->$key = $value;
			}
			$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue($this->Lin_ID_Lingua);
		}
		/*else 
		{
			$queryAltroAnno = "SELECT * FROM testo_richieste_dati ";
			$queryAltroAnno .= " WHERE Lin_ID_Lingua = " . $lingua;
			$queryAltroAnno .= " AND Lin_Comune = '" . $comune . "' ";
			$queryAltroAnno .= " ORDER BY Lin_Anno DESC ";
			$result = mysql_query($queryAltroAnno);
			
			if (mysql_num_rows($result) != 0)
			{
				$rigaLingua = mysql_fetch_assoc($result);
				$altroAnno = new testo_richiest_dati($rigaLingua['Lin_Progr']);
				$altroAnno->Lin_Progr = "";
				$altroAnno->Lin_Anno = $anno;
				$altroAnno->Lin_Data_Inserimento = date("d/m/Y");
				$altroAnno->InsertUpdateLingua();
				$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
			}
			else
			{
				$queryAltroComune = "SELECT * FROM testo_richieste_dati ";
				$queryAltroComune .= " WHERE Lin_ID_Lingua = " . $lingua;
				$queryAltroComune .= " AND Lin_Anno = '" . $anno . "' ";
				$queryAltroComune .= " ORDER BY Lin_Anno DESC ";
				$result = mysql_query($queryAltroComune);
					
				if (mysql_num_rows($result) != 0)
				{
					$rigaLingua = mysql_fetch_assoc($result);
					$altroComune = new testo_richiest_dati($rigaLingua['Lin_Progr']);
					$altroComune->Lin_Progr = "";
					$altroComune->Lin_Comune = $comune;
					$altroComune->Lin_Anno = $anno;
					$altroComune->Lin_Data_Inserimento = date("d/m/Y");
					$altroComune->InsertUpdateLingua();
					$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
				}
			}
		}*/
	}
	
	public function CercaLinguaComune ($lingua = null, $comune = null, $anno = null)
	{
		$queryAltroAnno = "SELECT * FROM testo_richieste_dati ";
		$queryAltroAnno .= " WHERE Lin_ID_Lingua = " . $lingua;
		$queryAltroAnno .= " AND Lin_Comune = '" . $comune . "' ";
		$queryAltroAnno .= " ORDER BY Lin_Data_Validita_Da DESC ";
		$result = mysql_query($queryAltroAnno);
			
		if (mysql_num_rows($result) != 0)
		{
			$rigaLingua = mysql_fetch_assoc($result);
			return $rigaLingua['Lin_Progr'];
			//$altroAnno = new testo_richiest_dati($rigaLingua['Lin_Progr']);
			//$altroAnno->Lin_Progr = "";
			//$altroAnno->Lin_Anno = $anno;
			//$altroAnno->Lin_Data_Inserimento = date("d/m/Y");
			//$altroAnno->InsertUpdateLingua();
			//$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
		}
		return null;
		/*else
		{
			$queryAltroComune = "SELECT * FROM testo_richieste_dati ";
			$queryAltroComune .= " WHERE Lin_ID_Lingua = " . $lingua;
			$queryAltroComune .= " AND Lin_Anno = '" . $anno . "' ";
			$queryAltroComune .= " ORDER BY Lin_Anno DESC ";
			$result = mysql_query($queryAltroComune);
				
			if (mysql_num_rows($result) != 0)
			{
				$rigaLingua = mysql_fetch_assoc($result);
				$altroComune = new testo_richiest_dati($rigaLingua['Lin_Progr']);
				$altroComune->Lin_Progr = "";
				$altroComune->Lin_Comune = $comune;
				$altroComune->Lin_Anno = $anno;
				$altroComune->Lin_Data_Inserimento = date("d/m/Y");
				$altroComune->InsertUpdateLingua();
				$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
			}
		}*/
	}
	
	public function ListaOptionLingue ($linguaSelect, $nonItaliano = null)
	{
		$queryLingue = "SELECT DISTINCT Lin_Progr, Lin_ID_Lingua, ID, Linguaggio ";
		$queryLingue .= " FROM testo_richieste_dati, targhe_estere_lista_lingue ";
		$queryLingue .= " WHERE ID = Lin_ID_Lingua ";
		$queryLingue .= " GROUP BY ID ";
		
		$resLingue = mysql_query($queryLingue);
		$optionLingue = "";
		while ($rigaLingua = mysql_fetch_assoc($resLingue))
		{
			if ($linguaSelect == $rigaLingua['Lin_Progr']) $selLingua = " selected ";
			else $selLingua = "";
			if ($nonItaliano != "" && $rigaLingua['Linguaggio'] == "italiano") {}
			else $optionLingue .= "<option $selLingua value='" . $rigaLingua['ID'] . "'>" . $rigaLingua['Linguaggio'] . "</option>\n";
		}
		return $optionLingue;
	}
	
	public function TutteRichiesteDiComune ($comune, $anno)
	{
		$queryTutti = "SELECT Lin_Progr ";
		$queryTutti .= " FROM testo_richieste_dati ";
		$queryTutti .= " WHERE Lin_Comune = '" . $comune . "' AND ";
		
		if ($anno != "")
		{
			$queryTutti .= " ( ";
			$queryTutti .= "       (SUBSTR(Lin_Data_Validita_Da FROM 1 FOR 4) != '0000' AND SUBSTR(Lin_Data_Validita_Da FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Lin_Data_Validita_A FROM 1 FOR 4) != '0000-00-00' AND SUBSTR(Lin_Data_Validita_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Lin_Data_Validita_Da FROM 1 FOR 4) = '0000' AND SUBSTR(Lin_Data_Validita_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Lin_Data_Validita_Da FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Lin_Data_Validita_A FROM 1 FOR 4) = '0000') ";
			$queryTutti .= " ) AND ";
		}
		$queryTutti .= " 1 ";
		//echo "<br>" . $queryTutti;
		$resTutti = mysql_query($queryTutti);
		$arrayTutti = array();
		while ($rigaLingua = mysql_fetch_assoc($resTutti))
		{
			$arrayTutti[] = $rigaLingua['Lin_Progr'];
		}
		return $arrayTutti;
	}
	
	public function InsertUpdateLingua ()
	{
		/*if ($this->Lin_Data_Inserimento == '0000-00-00' || $this->Lin_Data_Inserimento == "")
			$this->Lin_Data_Inserimento = date ("Y-m-d");
		else $this->Lin_Data_Inserimento = to_mysql_date($this->Lin_Data_Inserimento);*/
		
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "Lin_Progr" &&
				$campo != "Coll_Lista_Lingua")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
		
		if ($this->Lin_Progr != NULL)  //  solo update!
		{
			$risposta = $this->update_lingua_locale($this->Lin_Progr, $fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "UPDATE_OK"; break;
				case false: $risposta = "UPDATE_ERROR"; break;
				default: break;
			}
			return $risposta;
		}
		else
		{
			$risposta = $this->insert_lingua_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
			return $risposta;
		}
	}
	
	public function insert_lingua_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO testo_richieste_dati (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_lingua_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldLing = new testo_richieste_dati($key);
	
		$newImmagini = "";
		$newDocumenti = "";
		$newData = "";
		$newOra = "";
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldLing->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" . addslashes($values_to_update[$i]) . "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
	
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE testo_richieste_dati SET $clause WHERE Lin_Progr = '" . $key . "'";
		
		//echo $query;
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
}

/*
 * CREATE TABLE `testo_verbali_esteri` (
  `Lin_Progr` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Lin_ID_Lingua` int(11) unsigned NOT NULL,
  `Lin_Linguaggio` varchar(20) NOT NULL DEFAULT '',
  `Lin_Comune` varchar(5) NOT NULL DEFAULT '',
  `Lin_Richiesta_Numero` varchar(50) DEFAULT '',
  `Lin_Spettabile` varchar(50) DEFAULT '',
  `Lin_Targa_Tabella` varchar(20) DEFAULT '',
  `Lin_Marca_Lunga_Tabella` varchar(50) DEFAULT '',
  `Lin_Data_Lunga_Tabella` varchar(50) DEFAULT '',
  `Lin_Ora_Tabella` varchar(20) DEFAULT '',
  `Lin_Luogo_Tabella` varchar(20) DEFAULT '',
  `Lin_Tipo_Articolo_Tabella` varchar(50) DEFAULT '',
  `Lin_Rilevatore_Tabella` varchar(50) DEFAULT '',
  `Lin_Motivo_Mancata_Tabella` varchar(50) DEFAULT '',
  `Lin_Trasgressore_Tabella` varchar(50) DEFAULT '',
  `Lin_Noleggio_Tabella` varchar(50) DEFAULT '',
  `Lin_Firma_Riga1` text CHARACTER SET utf8,
  `Lin_Firma_Riga2` text CHARACTER SET utf8,
  `Lin_Stazione_Polizia` text,
  `Lin_Verbale_Proprietario` text,
  `Lin_Verbale_Data_Nascita` text,
  `Lin_Verbale_Residenza_Proprietario` text,
  `Lin_Verbale_Numero` varchar(50) DEFAULT '',
  `Lin_Titolo_Notifica_Verbale` text,
  `Lin_Decreto_Notifica_Verbale` text,
  `Lin_Titolo_Verbale` text,
  `Lin_Testo_1_Verbale` text,
  `Lin_Testo_2_Verbale` text,
  `Lin_Testo_3_Verbale` text,
  `Lin_Testo_4_Verbale` text,
  `Lin_Testo_5_Verbale` text,
  `Lin_Testo_6_Verbale` text,
  `Lin_Testo_126bis_Verbale` text,
  `Lin_Testo_Informazioni_Verbale` text,
  `Lin_Testo_Attenzione_Verbale` text,
  `Lin_Validita_Data_Da` date DEFAULT '0000-00-00',
  `Lin_Validita_Data_A` date DEFAULT '0000-00-00',
  PRIMARY KEY (`Lin_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class testo_verbali_esteri
{

	public $Lin_Progr;
	public $Lin_ID_Lingua;
	public $Lin_Linguaggio;
	public $Lin_Comune;
	public $Lin_Richiesta_Numero;
	public $Lin_Spettabile;
	public $Lin_Targa_Tabella;
	public $Lin_Marca_Lunga_Tabella;
	public $Lin_Data_Lunga_Tabella;
	public $Lin_Ora_Tabella;
	public $Lin_Luogo_Tabella;
	public $Lin_Tipo_Articolo_Tabella;
	public $Lin_Rilevatore_Tabella;
	public $Lin_Motivo_Mancata_Tabella;
	public $Lin_Trasgressore_Tabella;
	public $Lin_Noleggio_Tabella;
	public $Lin_Firma_Riga1;
	public $Lin_Firma_Riga2;
	public $Lin_Stazione_Polizia;
	public $Lin_Verbale_Proprietario;
	public $Lin_Verbale_Data_Nascita;
	public $Lin_Verbale_Residenza_Proprietario;
	public $Lin_Verbale_Numero;
	public $Lin_Titolo_Notifica_Verbale;
	public $Lin_Decreto_Notifica_Verbale;
	public $Lin_Titolo_Verbale;
	public $Lin_Testo_1_Verbale;
	public $Lin_Testo_2_Verbale;
	public $Lin_Testo_3_Verbale;
	public $Lin_Testo_4_Verbale;
	public $Lin_Testo_5_Verbale;
	public $Lin_Testo_6_Verbale;
	public $Lin_Testo_126bis_Verbale;
	public $Lin_Testo_Informazioni_Verbale;
	public $Lin_Testo_Attenzione_Verbale;
	public $Lin_Validita_Data_Da;
	public $Lin_Validita_Data_A;
	
	public $Coll_Lista_Lingua = NULL;
	
	public function __construct( $progr )//, $lingua = null, $comune = null, $anno = null)
	{
		if ($progr != NULL)
		{
			$query = "SELECT * FROM testo_verbali_esteri WHERE Lin_Progr = '" . $progr . "'";
		}
		/*else if ($lingua != NULL && $comune != null && $anno != null)
		{
			$query = "SELECT * FROM testo_verbali_esteri ";
			$query .= " WHERE Lin_ID_Lingua = " . $lingua;
			//$query .= " AND Lin_Comune = '" . $comune . "' ";
			//$query .= " AND Lin_Anno = '" . $anno . "' ";
		}*/
		else return;
		$result = mysql_query($query);
	
		if (mysql_num_rows($result) != 0)
		{
			$rigaLingua = mysql_fetch_assoc($result);
			foreach ($rigaLingua as $key => $value)
			{
				$this->$key = $value;
			}
			$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue($this->Lin_ID_Lingua);
		}
		/*else
		{
			$queryAltroAnno = "SELECT * FROM testo_verbali_esteri ";
			$queryAltroAnno .= " WHERE Lin_ID_Lingua = " . $lingua;
			$queryAltroAnno .= " AND Lin_Comune = '" . $comune . "' ";
			$queryAltroAnno .= " ORDER BY Lin_Validita_Data_Da DESC ";
			$result = mysql_query($queryAltroAnno);
				
				alert ("da controllare nella classe testo verbale");
				return;
				
			if (mysql_num_rows($result) != 0)
			{
				$rigaLingua = mysql_fetch_assoc($result);
				$altroAnno = new testo_verbali_esteri($rigaLingua['Lin_Progr']);
				$altroAnno->Lin_Progr = "";
				//$altroAnno->Lin_Anno = $anno;
				$altroAnno->Lin_Data_Inserimento = date("d/m/Y");
				$altroAnno->InsertUpdateLingua();
				$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
			}
			else
			{
				$queryAltroComune = "SELECT * FROM testo_verbali_esteri ";
				$queryAltroComune .= " WHERE Lin_ID_Lingua = " . $lingua;
				//$queryAltroComune .= " AND Lin_Anno = '" . $anno . "' ";
				$queryAltroComune .= " ORDER BY Lin_Anno DESC ";
				$result = mysql_query($queryAltroComune);
					
				if (mysql_num_rows($result) != 0)
				{
					$rigaLingua = mysql_fetch_assoc($result);
					$altroComune = new testo_verbali_esteri($rigaLingua['Lin_Progr']);
					$altroComune->Lin_Progr = "";
					$altroComune->Lin_Comune = $comune;
					$altroComune->Lin_Anno = $anno;
					$altroComune->Lin_Data_Inserimento = date("d/m/Y");
					$altroComune->InsertUpdateLingua();
					$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
				}
			}
		}*/
	}
	
	public function CercaLinguaComune ($lingua = null, $comune = null, $anno = null)
	{
		$queryAltroAnno = "SELECT * FROM testo_verbali_esteri ";
		$queryAltroAnno .= " WHERE Lin_ID_Lingua = " . $lingua;
		$queryAltroAnno .= " AND Lin_Comune = '" . $comune . "' ";
		$queryAltroAnno .= " ORDER BY Lin_Validita_Data_Da DESC ";
		$result = mysql_query($queryAltroAnno);
			
		if (mysql_num_rows($result) != 0)
		{
			$rigaLingua = mysql_fetch_assoc($result);
			return $rigaLingua['Lin_Progr'];
			//$altroAnno = new testo_richiest_dati($rigaLingua['Lin_Progr']);
			//$altroAnno->Lin_Progr = "";
			//$altroAnno->Lin_Anno = $anno;
			//$altroAnno->Lin_Data_Inserimento = date("d/m/Y");
			//$altroAnno->InsertUpdateLingua();
			//$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
		}
		return null;
		/*else
		{
			$queryAltroComune = "SELECT * FROM testo_richieste_dati ";
			$queryAltroComune .= " WHERE Lin_ID_Lingua = " . $lingua;
			$queryAltroComune .= " AND Lin_Anno = '" . $anno . "' ";
			$queryAltroComune .= " ORDER BY Lin_Anno DESC ";
			$result = mysql_query($queryAltroComune);
				
			if (mysql_num_rows($result) != 0)
			{
				$rigaLingua = mysql_fetch_assoc($result);
				$altroComune = new testo_richiest_dati($rigaLingua['Lin_Progr']);
				$altroComune->Lin_Progr = "";
				$altroComune->Lin_Comune = $comune;
				$altroComune->Lin_Anno = $anno;
				$altroComune->Lin_Data_Inserimento = date("d/m/Y");
				$altroComune->InsertUpdateLingua();
				$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
			}
		}*/
	}
	
	public function ListaOptionLingue ($linguaSelect, $nonItaliano = null)
	{
		$queryLingue = "SELECT DISTINCT Lin_Progr, Lin_ID_Lingua, ID, Linguaggio ";
		$queryLingue .= " FROM testo_verbali_esteri, targhe_estere_lista_lingue ";
		$queryLingue .= " WHERE ID = Lin_ID_Lingua ";
	
		$resLingue = mysql_query($queryLingue);
		$optionLingue = "";
		$arrayLingueMesse = array();
		while ($rigaLingua = mysql_fetch_assoc($resLingue))
		{
			$presente = false;
			for ($k = 0; $k < count($arrayLingueMesse); $k++)
			{
				if ($rigaLingua['ID'] == $arrayLingueMesse[$k])
				{
					$presente = true;
					break;
				}
			}
			if ($presente == false)
			{
				$arrayLingueMesse[] = $rigaLingua['ID'];
				if ($linguaSelect == $rigaLingua['Lin_Progr']) $selLingua = " selected ";
				else $selLingua = "";
				if ($nonItaliano != "" && $rigaLingua['Linguaggio'] == "italiano") {}
				else $optionLingue .= "<option $selLingua value='" . $rigaLingua['ID'] . "'>" . $rigaLingua['Linguaggio'] . "</option>\n";
			}
		}
		return $optionLingue;
	}
	
	public function TuttiVerbaliDiComune ($comune, $anno)
	{
		$queryTutti = "SELECT Lin_Progr ";
		$queryTutti .= " FROM testo_verbali_esteri ";
		$queryTutti .= " WHERE Lin_Comune = '" . $comune . "' AND ";
		if ($anno != "")
		{
			$queryTutti .= " ( ";
			$queryTutti .= "       (SUBSTR(Lin_Validita_Data_Da FROM 1 FOR 4) != '0000' AND SUBSTR(Lin_Validita_Data_Da FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Lin_Validita_Data_A FROM 1 FOR 4) != '0000-00-00' AND SUBSTR(Lin_Validita_Data_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Lin_Validita_Data_Da FROM 1 FOR 4) = '0000' AND SUBSTR(Lin_Validita_Data_A FROM 1 FOR 4) >= '" . $anno . "') OR ";
			$queryTutti .= "       (SUBSTR(Lin_Validita_Data_DA FROM 1 FOR 4) <= '" . $anno . "' AND SUBSTR(Lin_Validita_Data_A FROM 1 FOR 4) = '0000') ";
			$queryTutti .= " ) AND ";
		}
		$queryTutti .= " 1 ";
		//echo "<br>" . $queryTutti;
		$resTutti = mysql_query($queryTutti);
		$arrayTutti = array();
		while ($rigaLingua = mysql_fetch_assoc($resTutti))
		{
			$arrayTutti[] = $rigaLingua['Lin_Progr'];
		}
		return $arrayTutti;
	}
	
	public function InsertUpdateLingua ()
	{
		/*if ($this->Lin_Data_Inserimento == '0000-00-00' || $this->Lin_Data_Inserimento == "")
			$this->Lin_Data_Inserimento = date ("Y-m-d");
		else $this->Lin_Data_Inserimento = to_mysql_date($this->Lin_Data_Inserimento);*/
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) &&
			$campo != "Lin_Progr" &&
			$campo != "Coll_Lista_Lingua")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
	
		if ($this->Lin_Progr != NULL)  //  solo update!
		{
			$risposta = $this->update_lingua_locale($this->Lin_Progr, $fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "UPDATE_OK"; break;
				case false: $risposta = "UPDATE_ERROR"; break;
				default: break;
			}
			return $risposta;
		}
		else
		{
			$risposta = $this->insert_lingua_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
			return $risposta;
		}
	}
	
	public function insert_lingua_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO testo_verbali_esteri (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		return mysql_query($query);
		
		echo $query;
		
		return true;
	}
	
	public function update_lingua_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldLing = new testo_verbali_esteri($key);
		
		$newImmagini = "";
		$newDocumenti = "";
		$newData = "";
		$newOra = "";
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldLing->$fields_to_update[$i] != $values_to_update[$i])
			{
			//if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" . $values_to_update[$i] . "' , ";
				}
			}
					//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE testo_verbali_esteri SET $clause WHERE Lin_Progr = '" . $key . "'";
		
		//echo $query;
		
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
		
		echo $query;
		
		return true;
	}
}

/*
CREATE TABLE `targhe_estere_lingue` (
  `Lin_Progr` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Lin_ID_Lingua` int NOT NULL DEFAULT '',
  `Lin_Linguaggio` varchar(20) NOT NULL DEFAULT '',
  `Lin_Comune` varchar(5) NOT NULL DEFAULT '',
  `Lin_Anno` int(5) NOT NULL DEFAULT '0',
  `Lin_Data_Inserimento` date NOT NULL DEFAULT '0000-00-00',
  
  `Lin_Richiesta_Numero` varchar(50) DEFAULT '',
  `Lin_Spettabile` varchar(50) DEFAULT '',
  `Lin_Oggetto_Titolo` varchar(20) DEFAULT '',
  `Lin_Oggetto_Lettera` text CHARACTER SET utf8,
  `Lin_Intestazione_Lettera` varchar(200) DEFAULT '',
  `Lin_Testo_Lettera` longtext CHARACTER SET utf8,
  
  `Lin_Targa_Tabella` varchar(20) DEFAULT '',
  `Lin_Marca_Tabella` varchar(20) DEFAULT '',
  `Lin_Marca_Lunga_Tabella` varchar(50) DEFAULT '',
  `Lin_Data_Tabella` varchar(20) DEFAULT '',
  `Lin_Data_Lunga_Tabella` varchar(50) DEFAULT '',
  `Lin_Ora_Tabella` varchar(20) DEFAULT '',
  `Lin_Luogo_Tabella` varchar(20) DEFAULT '',
  `Lin_Limite_Tabella` varchar(20) DEFAULT '',
  `Lin_Articolo_Tabella` varchar(20) DEFAULT '',
  `Lin_Tipo_Articolo_Tabella` varchar(50) DEFAULT '',
  `Lin_Tipo_Tabella` varchar(20) DEFAULT '',
  
  `Lin_Rilevatore_Tabella` varchar(50) DEFAULT '',
  `Lin_Motivo_Mancata_Tabella` varchar(50) DEFAULT '',
  `Lin_Trasgressore_Tabella` varchar(50) DEFAULT '',
  `Lin_Noleggio_Tabella` varchar(50) DEFAULT '',
  
  `Lin_Testo_Chiusura` text CHARACTER SET utf8,
  `Lin_Testo_Sarida` text CHARACTER SET utf8,
  `Lin_Firma_Riga1` text CHARACTER SET utf8,
  `Lin_Firma_Riga2` text CHARACTER SET utf8,
  `Lin_Stazione_Polizia` text,
  
  `Lin_Verbale_Proprietario` text,
  `Lin_Verbale_Data_Nascita` text,
  `Lin_Verbale_Residenza_Proprietario` text,
  
  `Lin_Verbale_Numero` varchar(50) DEFAULT '',
  `Lin_Titolo_Notifica_Verbale` text,
  `Lin_Decreto_Notifica_Verbale` text,
  `Lin_Titolo_Verbale` text,
  `Lin_Testo_1_Verbale` text,
  `Lin_Testo_2_Verbale` text,
  `Lin_Testo_3_Verbale` text,
  `Lin_Testo_4_Verbale` text,
  `Lin_Testo_5_Verbale` text,
  `Lin_Testo_6_Verbale` text,
  `Lin_Testo_126bis_Verbale` text,
  `Lin_Testo_Informazioni_Verbale` text,
  `Lin_Testo_Attenzione_Verbale` text,
  PRIMARY KEY (`Lin_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class targhe_estere_lingue
{
	public $Lin_Progr = NULL;
	public $Lin_ID_Lingua = NULL;
	public $Lin_Linguaggio = NULL;
	public $Lin_Comune = NULL;
	public $Lin_Anno = NULL;
	public $Lin_Data_Inserimento = NULL;
	
	public $Lin_Richiesta_Numero = NULL;
	public $Lin_Spettabile = NULL;
	public $Lin_Oggetto_Titolo = NULL;
	public $Lin_Oggetto_Lettera = NULL;
	public $Lin_Intestazione_Lettera = NULL;
	public $Lin_Testo_Lettera = NULL;
	
	public $Lin_Targa_Tabella = NULL;
	public $Lin_Marca_Tabella = NULL;
	public $Lin_Marca_Lunga_Tabella = NULL;
	public $Lin_Data_Tabella = NULL;
	public $Lin_Data_Lunga_Tabella = NULL;
	public $Lin_Ora_Tabella = NULL;
	public $Lin_Luogo_Tabella = NULL;
	public $Lin_Limite_Tabella = NULL;
	public $Lin_Articolo_Tabella = NULL;
	public $Lin_Tipo_Articolo_Tabella = NULL;
	public $Lin_Tipo_Tabella = NULL;
	
	public $Lin_Rilevatore_Tabella = NULL;
	public $Lin_Motivo_Mancata_Tabella = NULL;
	public $Lin_Trasgressore_Tabella = NULL;
	public $Lin_Noleggio_Tabella = NULL;
	
	public $Lin_Testo_Chiusura = NULL;
	public $Lin_Testo_Sarida = NULL;
	public $Lin_Firma_Riga1 = NULL;
	public $Lin_Firma_Riga2 = NULL;
	public $Lin_Stazione_Polizia = NULL;
	
	public $Lin_Verbale_Proprietario = NULL;
	public $Lin_Verbale_Residenza_Proprietario = NULL;
	public $Lin_Verbale_Data_Nascita = NULL;
	
	public $Lin_Verbale_Numero = NULL;
	public $Lin_Titolo_Notifica_Verbale = NULL;
	public $Lin_Decreto_Notifica_Verbale = NULL;
	public $Lin_Titolo_Verbale = NULL;
	public $Lin_Testo_1_Verbale = NULL;
	public $Lin_Testo_2_Verbale = NULL;
	public $Lin_Testo_3_Verbale = NULL;
	public $Lin_Testo_4_Verbale = NULL;
	public $Lin_Testo_5_Verbale = NULL;
	public $Lin_Testo_6_Verbale = NULL;
	public $Lin_Testo_126bis_Verbale = NULL;
	public $Lin_Testo_Informazioni_Verbale = NULL;
	public $Lin_Testo_Attenzione_Verbale = NULL;
	
	public $Coll_Lista_Lingua = NULL;
	
	public function __construct( $progr , $lingua = null, $comune = null, $anno = null)
	{
		if ($progr != NULL)
		{
			$query = "SELECT * FROM targhe_estere_lingue WHERE Lin_Progr = '" . $progr . "'";
		}
		else if ($lingua != NULL && $comune != null && $anno != null)
		{
			$query = "SELECT * FROM targhe_estere_lingue ";
			$query .= " WHERE Lin_ID_Lingua = " . $lingua;
			//$query .= " AND Lin_Comune = '" . $comune . "' ";
			//$query .= " AND Lin_Anno = '" . $anno . "' ";
		}
		else return;
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) != 0)
		{
			$rigaLingua = mysql_fetch_assoc($result);
			foreach ($rigaLingua as $key => $value)
			{
				$this->$key = $value;
			}
			$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue($this->Lin_ID_Lingua);
		}
		else 
		{
			$queryAltroAnno = "SELECT * FROM targhe_estere_lingue ";
			$queryAltroAnno .= " WHERE Lin_ID_Lingua = " . $lingua;
			$queryAltroAnno .= " AND Lin_Comune = '" . $comune . "' ";
			$queryAltroAnno .= " ORDER BY Lin_Anno DESC ";
			$result = mysql_query($queryAltroAnno);
			
			if (mysql_num_rows($result) != 0)
			{
				$rigaLingua = mysql_fetch_assoc($result);
				$altroAnno = new targhe_estere_lingue($rigaLingua['Lin_Progr']);
				$altroAnno->Lin_Progr = "";
				$altroAnno->Lin_Anno = $anno;
				$altroAnno->Lin_Data_Inserimento = date("d/m/Y");
				$altroAnno->InsertUpdateLingua();
				$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
			}
			else
			{
				$queryAltroComune = "SELECT * FROM targhe_estere_lingue ";
				$queryAltroComune .= " WHERE Lin_ID_Lingua = " . $lingua;
				$queryAltroComune .= " AND Lin_Anno = '" . $anno . "' ";
				$queryAltroComune .= " ORDER BY Lin_Anno DESC ";
				$result = mysql_query($queryAltroComune);
					
				if (mysql_num_rows($result) != 0)
				{
					$rigaLingua = mysql_fetch_assoc($result);
					$altroComune = new targhe_estere_lingue($rigaLingua['Lin_Progr']);
					$altroComune->Lin_Progr = "";
					$altroComune->Lin_Comune = $comune;
					$altroComune->Lin_Anno = $anno;
					$altroComune->Lin_Data_Inserimento = date("d/m/Y");
					$altroComune->InsertUpdateLingua();
					$this->Coll_Lista_Lingua = new targhe_estere_lista_lingue(null, $lingua, $comune, $anno);
				}
			}
		}
	}
	
	public function ListaOptionLingue ($linguaSelect, $nonItaliano = null)
	{
		$queryLingue = "SELECT DISTINCT Lin_Progr, Lin_ID_Lingua, ID, Linguaggio ";
		$queryLingue .= " FROM targhe_estere_lingue, targhe_estere_lista_lingue ";
		$queryLingue .= " WHERE ID = Lin_ID_Lingua ";
		
		$resLingue = mysql_query($queryLingue);
		$optionLingue = "";
		while ($rigaLingua = mysql_fetch_assoc($resLingue))
		{
			if ($linguaSelect == $rigaLingua['Lin_Progr']) $selLingua = " selected ";
			else $selLingua = "";
			if ($nonItaliano != "" && $rigaLingua['Linguaggio'] == "italiano") {}
			else $optionLingue .= "<option $selLingua value='" . $rigaLingua['ID'] . "'>" . $rigaLingua['Linguaggio'] . "</option>\n";
		}
		return $optionLingue;
	}
	
	public function InsertUpdateLingua ()
	{
		if ($this->Lin_Data_Inserimento == '0000-00-00' || $this->Lin_Data_Inserimento == "")
			$this->Lin_Data_Inserimento = date ("Y-m-d");
		else $this->Lin_Data_Inserimento = to_mysql_date($this->Lin_Data_Inserimento);
		
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "Lin_Progr" &&
				$campo != "Coll_Lista_Lingua")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		
		if ($this->Lin_Progr != NULL)  //  solo update!
		{
			$risposta = $this->update_lingua_locale($this->Lin_Progr, $fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "UPDATE_OK"; break;
				case false: $risposta = "UPDATE_ERROR"; break;
				default: break;
			}
			return $risposta;
		}
		else
		{
			$risposta = $this->insert_lingua_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
			return $risposta;
		}
	}
	
	public function insert_lingua_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_lingue (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_lingua_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldLing = new targhe_estere_lingue($key);
	
		$newImmagini = "";
		$newDocumenti = "";
		$newData = "";
		$newOra = "";
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldLing->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" . $values_to_update[$i] . "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
	
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_lingue SET $clause WHERE Lin_Progr = '" . $key . "'";
		
		//echo $query;
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
}

/*
CREATE TABLE `registro_cronologico_cds` (
  `Reg_Progr` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Reg_Progr_Registro` int(9) NOT NULL DEFAULT '0',
  `Reg_Anno` year(4) NOT NULL DEFAULT '0000',
  `Reg_Genere_Infrazione` varchar(50) NOT NULL DEFAULT '',
  `Reg_Stato_Verbale` varchar(50) NOT NULL DEFAULT '',
  `Reg_Progr_Provenienza` int(9) NOT NULL DEFAULT '0',
  `Reg_Nuova_Procedura_126bis` enum('Y','N') NOT NULL DEFAULT 'N',
  `Reg_Data_Avviso` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Ora_Avviso` time NOT NULL DEFAULT '00:00:00',
  `Reg_Data_Verbalizzazione` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Ora_Verbalizzazione` time NOT NULL DEFAULT '00:00:00',
  `Reg_Verbalizzante` int(9) NOT NULL DEFAULT '0',
  `Reg_Comune_Violazione` varchar(4) NOT NULL DEFAULT '',
  `Reg_Localita_Violazione` text,
  `Reg_Spese_Notifica_Comune` decimal( 10, 2 ) NOT NULL DEFAULT '0',
  `Reg_Spese_Ricerca_Comune` decimal( 10, 2 ) NOT NULL DEFAULT '0',
  `Reg_Importo_Amministrativo` decimal( 10, 2 ) NOT NULL DEFAULT '0',
  `Reg_Spese_Notifica_Sarida` decimal( 10, 2 ) NOT NULL DEFAULT '0',
  `Reg_Spese_Ricerca_Sarida` decimal( 10, 2 ) NOT NULL DEFAULT '0',
  `Reg_Articoli_Infrazione` text,
  `Reg_Accertatori` text,
  `Reg_Sanzioni_Accessorie` text,
  `Reg_Rilevatore_Elettronico` int(11) unsigned NOT NULL DEFAULT '0',
  `Reg_Dati_Infrazione` varchar(100) NOT NULL DEFAULT '0',
  `Reg_Velocita_Effettiva` decimal( 10, 2 ) NOT NULL DEFAULT '0',
  `Reg_Immagini` varchar(200) NOT NULL DEFAULT '',
  `Reg_Data_Travaso_Su_Server` date DEFAULT '0000-00-00',
  `Reg_Documentazione` varchar(500) NOT NULL DEFAULT '',
  `Reg_Data_Travaso_Doc_Su_Server` date DEFAULT '0000-00-00',
  `Reg_Ente_Per_Richiesta` int(11) unsigned NOT NULL DEFAULT '0',
  `Reg_Data_Stampa_Richiesta` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Nazionalita_Targa` varchar(20) NOT NULL DEFAULT 'I',
  `Reg_Targa_Veicolo` varchar(20) NOT NULL DEFAULT '',
  `Reg_Tipologia_Veicolo` varchar(30) NOT NULL DEFAULT '',
  `Reg_Marca_Veicolo` varchar(200) NOT NULL DEFAULT '',
  `Reg_Tipo_Veicolo` varchar(200) NOT NULL DEFAULT '',
  `Reg_Colore_Veicolo` varchar(200) NOT NULL DEFAULT '',
  `Reg_Telaio_Veicolo` varchar(20) NOT NULL DEFAULT '',
  `Reg_Portata_Veicolo` varchar(8) NOT NULL DEFAULT '',
  `Reg_Massa_Veicolo` varchar(8) NOT NULL DEFAULT '',
  `Reg_Carrozzeria` varchar(10) NOT NULL DEFAULT '',
  `Reg_Categoria_Euro_Veicolo` varchar(10) NOT NULL DEFAULT '',
  `Reg_Alimentazione_Veicolo` varchar(40) NOT NULL DEFAULT '',
  `Reg_Emissioni_Nox_Veicolo` varchar(10) NOT NULL DEFAULT '',
  `Reg_Emissioni_Particolato_Veicolo` varchar(10) NOT NULL DEFAULT '',
  `Reg_Emissioni_Co2_Veicolo` varchar(10) NOT NULL DEFAULT '',
  `Reg_Coefficiente_Assorbimento_Veicolo` varchar(10) NOT NULL DEFAULT '',
  `Reg_Codice_Direttiva_Cee` varchar(10) NOT NULL DEFAULT '',
  `Reg_Data_Ultima_Revisione` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Esito_Ultima_Revisione` varchar(10) NOT NULL DEFAULT '',
  `Reg_Codice_Antifalsificazione` varchar(20) NOT NULL DEFAULT '',
  `Reg_Marca_Modello` varchar(50) NOT NULL DEFAULT '',
  `Reg_Denominazione_Commerciale` varchar(50) NOT NULL DEFAULT '',
  `Reg_Tipo_Modello` varchar(50) NOT NULL DEFAULT '',
  `Reg_Variante_Modello` varchar(50) NOT NULL DEFAULT '',
  `Reg_Versione_Modello` varchar(50) NOT NULL DEFAULT '',
  `Reg_Note_Interne` text NOT NULL,
  `Reg_Motivi_Mancata_Contestazione` int(5) NOT NULL DEFAULT '0',,
  `Reg_Motivi_Liberi` longtext NOT NULL,
  `Reg_Data_Esecuzione_Impossibile` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Motivi_Esecuzione_Impossibile` text NOT NULL,
  `Reg_Veicolo_Rimosso` enum('Y','N') NOT NULL DEFAULT 'N',
  `Reg_Deposito_Veicolo` longtext NOT NULL,
  `Reg_Danni` longtext NOT NULL,
  `Reg_Responsabile_Dati` int(9) NOT NULL DEFAULT '0',
  `Reg_Note` longtext NOT NULL,
  `Reg_Data_Annullamento` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Motivo_Annullamento` longtext NOT NULL,
  `Reg_Pagato_Su_Strada` enum('Y','N') NOT NULL DEFAULT 'N',
  `Reg_Provenienza` varchar(20) NOT NULL DEFAULT '',
  `Reg_Protocollo` int(9) NOT NULL DEFAULT '0',
  `Reg_Numero_Protocollo` varchar(10) NOT NULL DEFAULT '',
  `Reg_Numero_Protocollo_Uscita` varchar(10) NOT NULL DEFAULT '',
  `Reg_Numero_Archivio` varchar(10) NOT NULL DEFAULT '',
  `Reg_Data_Archiviazione` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Data_Stampa_Archiviazione` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Annullamento_Ufficio` enum('Y','N') NOT NULL DEFAULT 'N',
  `Reg_Allegati` longtext NOT NULL,
  `Reg_Data_Emissione_Provvedimento` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Descrizione_Provvedimento` text NOT NULL,
  `Reg_Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
  `Reg_Ora_Registrazione` time NOT NULL DEFAULT '00:00:00',
  `Reg_Operatore` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`Reg_Progr`),
  KEY `Chiave reg targhe` (`Reg_Ente_Per_Richiesta`),
  KEY `Chiave reg velocita` (`Reg_Rilevatore_Elettronico`),
  CONSTRAINT `Chiave reg velocita` FOREIGN KEY (`Reg_Rilevatore_Elettronico`) REFERENCES `targhe_estere_rilevatori_velocita` (`Ril_Progr`) ON UPDATE CASCADE,
  CONSTRAINT `Chiave reg targhe` FOREIGN KEY (`Reg_Ente_Per_Richiesta`) REFERENCES `targhe_estere_zone_competenza` (`Tar_Progr`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1

*/

class registro_cronologico_cds
{
	public $Reg_Progr = NULL;
	public $Reg_Progr_Registro = NULL;
	public $Reg_Anno = NULL;
	
	public $Reg_Genere_Infrazione = NULL;
	public $Reg_Stato_Verbale = NULL;

	public $Reg_Progr_Provenienza = NULL;
	public $Reg_Nuova_Procedura_126bis = NULL;

	public $Reg_Data_Avviso = NULL;
	public $Reg_Ora_Avviso = NULL;

	public $Reg_Data_Verbalizzazione = NULL;
	public $Reg_Ora_Verbalizzazione = NULL;
	public $Reg_Verbalizzante = NULL;

	public $Reg_Comune_Violazione = NULL;
	public $Reg_Comune_Localita = NULL;
	public $Reg_Localita_Violazione = NULL;
	
	public $Reg_Spese_Notifica_Comune = NULL;
	public $Reg_Spese_Ricerca_Comune = NULL;
	public $Reg_Importi_Separati = NULL;
	public $Reg_Importo_Amministrativo = NULL;
	public $Reg_Importo_Sanzione_Massima = NULL;
	public $Reg_Spese_Notifica_Sarida = NULL;
	public $Reg_Spese_Ricerca_Sarida = NULL;

	public $Reg_Articoli_Infrazione = NULL;
	public $Reg_Accertatori = NULL;
	public $Reg_Data_Accertamento = NULL;
	public $Reg_Ora_Accertamento = NULL;
	public $Reg_Sanzioni_Accessorie = NULL;
	public $Reg_Rilevatore_Elettronico = NULL;

	public $Reg_Dati_Infrazione = NULL;
	public $Reg_Velocita_Effettiva = NULL;

	public $Reg_Immagini = NULL;
	public $Reg_Data_Travaso_Su_Server = NULL;
	public $Reg_Documentazione = NULL;
	public $Reg_Data_Travaso_Doc_Su_Server = NULL;

	public $Reg_Ente_Per_Richiesta = NULL;
	public $Reg_Data_Stampa_Richiesta = NULL;
	public $Reg_Nazionalita_Targa = NULL;
	public $Reg_Targa_Veicolo = NULL;

	public $Reg_Tipologia_Veicolo = NULL;  //  modello
	public $Reg_Marca_Veicolo = NULL;
	public $Reg_Tipo_Veicolo = NULL;  //  auto, moto...
	public $Reg_Colore_Veicolo = NULL;
	public $Reg_Telaio_Veicolo = NULL;
	public $Reg_Portata_Veicolo = NULL;
	public $Reg_Massa_Veicolo = NULL;
	public $Reg_Carrozzeria = NULL;
	public $Reg_Categoria_Euro_Veicolo = NULL;
	public $Reg_Alimentazione_Veicolo = NULL;
	public $Reg_Emissioni_Nox_Veicolo = NULL;
	public $Reg_Emissioni_Particolato_Veicolo = NULL;
	public $Reg_Emissioni_Co2_Veicolo = NULL;
	public $Reg_Coefficiente_Assorbimento_Veicolo = NULL;
	public $Reg_Codice_Direttiva_Cee = NULL;
	public $Reg_Data_Ultima_Revisione = NULL;
	public $Reg_Esito_Ultima_Revisione = NULL;
	public $Reg_Codice_Antifalsificazione = NULL;
	public $Reg_Marca_Modello = NULL;
	public $Reg_Denominazione_Commerciale = NULL;
	public $Reg_Tipo_Modello = NULL;
	public $Reg_Variante_Modello = NULL;
	public $Reg_Versione_Modello = NULL;

	public $Reg_Note_Interne = NULL;

	public $Reg_Motivi_Mancata_Contestazione = NULL;
	public $Reg_Motivi_Liberi = NULL;

	public $Reg_Data_Esecuzione_Impossibile = NULL;
	public $Reg_Motivi_Esecuzione_Impossibile = NULL;
	public $Reg_Veicolo_Rimosso = NULL;
	public $Reg_Deposito_Veicolo = NULL;
	public $Reg_Danni = NULL;
	public $Reg_Responsabile_Dati = NULL;
	public $Reg_Note = NULL;

	public $Reg_Data_Annullamento = NULL;
	public $Reg_Motivo_Annullamento = NULL;

	public $Reg_Pagato_Su_Strada = NULL;
	public $Reg_Provenienza = NULL;

	public $Reg_Protocollo = NULL;
	public $Reg_Numero_Protocollo = NULL;
	public $Reg_Numero_Protocollo_Uscita = NULL;
	public $Reg_Numero_Archivio = NULL;
	public $Reg_Data_Archiviazione = NULL;
	public $Reg_Data_Stampa_Archiviazione = NULL;
	public $Reg_Annullamento_Ufficio = NULL;
	public $Reg_Allegati = NULL;
	public $Reg_Data_Emissione_Provvedimento = NULL;
	public $Reg_Descrizione_Provvedimento = NULL;

	public $Reg_Data_Registrazione = NULL;
	public $Reg_Ora_Registrazione = NULL;
	public $Reg_Operatore = NULL;

	public $Coll_Ente_Richiesta = NULL;
	public $Coll_Rilevatore_Elettronico = NULL;
	public $Coll_Articoli_Infranti = array();
	public $Coll_Accertatori = NULL;
	public $Coll_Motivo_Mancata_Contestazione = NULL;
	
	public function __construct( $progr )
	{
		if ($progr == NULL || $progr == '0') return;
	
		$query = "SELECT * FROM registro_cronologico_cds WHERE Reg_Progr = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaVerbale = mysql_fetch_assoc($result);
		//echo "<br>$query";
	
		foreach ($rigaVerbale as $key => $value)
		{
			$this->$key = /*utf8_decode*/($value);
		}
		
		$this->Coll_Ente_Richiesta = new targhe_estere_zone_competenza($this->Reg_Ente_Per_Richiesta);
		$this->Coll_Rilevatore_Elettronico = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
		$this->Coll_Motivo_Mancata_Contestazione = new motivi_mancata_contestazione_cds($this->Reg_Motivi_Mancata_Contestazione);
		
		$this->Coll_Articoli_Infranti = array();		
		$temp = $this->Reg_Articoli_Infrazione;
		$splittoArticoli = explode("**", $temp);
		if ($temp != "")
		{
			for ($i = 0; $i < count($splittoArticoli); $i++)
			{
				$this->Coll_Articoli_Infranti[$i] = new targhe_estere_articoli($splittoArticoli[$i]);
			}
		}
		
		$this->Coll_Accertatori = array();
		$temp = $this->Reg_Accertatori;
		$splittoAccertatori = explode("**", $temp);
		if ($temp != "")
		{
			for ($i = 0; $i < count($splittoAccertatori); $i++)
			{
				$this->Coll_Accertatori[$i] = new targhe_estere_accertatori($splittoAccertatori[$i]);
			}
		}
	}
	
	public function StatiVerbale ()
	{
		switch ($this->Reg_Stato_Verbale)
		{
			//case "PRIMIDATI": break;  //  inserimento manuale da operatore (prima parte dei dati)
			case "MANUALE": break;  //  generato manualmente da operatore (file richiesta_manuale)
			case "AUTOMATICO": break;  //  importato da gitcovecchio o file di comuni (file analisi_importati)
			case "AUTOIMPORTATO": break;  //  importato da file olandese via web (file setta_web_1)
			case "WEBIMPORTATO": break;  //  importato da file olandese via web (file setta_web_2)
			case "INSERITO_M": break;  //  dati completi da manuale
			case "INSERITO_A": break;  //  dati completi da automatico
			case "INSERITO_W": break;  //  dati completi da web (olanda)
			default: alert ("errore stato verbale sconosciuto: $this->Reg_Stato_Verbale"); break;
		}
	}
	
	public function SelectLocalita ($scelta, $comune)
	{
		$query = "SELECT DISTINCT Reg_Localita_Violazione FROM registro_cronologico_cds ";
		$query .= "WHERE Reg_Comune_Violazione = '$comune' ";
		$query .= "ORDER BY Reg_Localita_Violazione";
		$resultlocalita = mysql_query($query);
		$numerorighelocalita = mysql_num_rows($resultlocalita);
		
		$select = "";
		while ($rigaLocalita = mysql_fetch_assoc($resultlocalita))
		{
			if ($scelta == $rigaLocalita['Reg_Localita_Violazione']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaLocalita['Reg_Localita_Violazione'] . "' title='" . $rigaLocalita['Reg_Localita_Violazione'] . "' $selScelta>" . $rigaLocalita['Reg_Localita_Violazione'] . "</option>";
		}
		return $select;
	}
	
	public function SelectComuneLocalita ($scelta, $comune)
	{
		$query = "SELECT DISTINCT Reg_Comune_Localita FROM registro_cronologico_cds ";
		$query .= "WHERE Reg_Comune_Violazione = '$comune' ";
		$query .= "ORDER BY Reg_Comune_Localita";
		$resultlocalita = mysql_query($query);
		$numerorighelocalita = mysql_num_rows($resultlocalita);
		
		$select = "";
		while ($rigaLocalita = mysql_fetch_assoc($resultlocalita))
		{
			if ($scelta == $rigaLocalita['Reg_Comune_Localita']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaLocalita['Reg_Comune_Localita'] . "' title='" . $rigaLocalita['Reg_Comune_Localita'] . "' $selScelta>" . $rigaLocalita['Reg_Comune_Localita'] . "</option>";
		}
		return $select;
	}
	
	public function NumeroPerDB ($value)
	{
		if ($value == null)
			return 0;
		
		$virgola = strpos($value, ",");
		$punto = strpos($value, ".");
		
		if ($virgola != false && $punto != false)
		{
			if($virgola < $punto)
			{
				$value = str_replace(",", "", $value);
				//$value = str_replace(".", ",", $value);
			}
			else
			{
				$value = str_replace(".", "", $value);
				$value = str_replace(",", ".", $value);
			}
		}
		else if ($virgola == false && $punto != false)
		{
			//$value = number_format($value, 2);
			//$value = str_replace(".", ",", $value);
		}
		else if ($virgola != false && $punto == false)
		{
			$value = str_replace(",", ".", $value);
		}
		
		return $value;
		
	}
	
	public function CorreggiDati ()
	{
		if ($this->Reg_Ente_Per_Richiesta == NULL) $this->Reg_Ente_Per_Richiesta = 1;
		if ($this->Reg_Motivi_Mancata_Contestazione == NULL) $this->Reg_Motivi_Mancata_Contestazione = 1;
		
		if ($this->Reg_Data_Avviso == "") $this->Reg_Data_Avviso = "0000-00-00";
		else if ($this->Reg_Data_Avviso == "0000-00-00") $this->Reg_Data_Avviso = "0000-00-00";
		else $this->Reg_Data_Avviso = to_mysql_date($this->Reg_Data_Avviso);
		if ($this->Reg_Data_Accertamento == "") $this->Reg_Data_Accertamento = "0000-00-00";
		else if ($this->Reg_Data_Accertamento == "0000-00-00") $this->Reg_Data_Accertamento = "0000-00-00";
		else $this->Reg_Data_Accertamento = to_mysql_date($this->Reg_Data_Accertamento);
		if ($this->Reg_Data_Verbalizzazione == "") $this->Reg_Data_Verbalizzazione = "0000-00-00";
		else if ($this->Reg_Data_Verbalizzazione == "0000-00-00") $this->Reg_Data_Verbalizzazione = "0000-00-00";
		else $this->Reg_Data_Verbalizzazione = to_mysql_date($this->Reg_Data_Verbalizzazione);
		if ($this->Reg_Data_Archiviazione == "") $this->Reg_Data_Archiviazione = "0000-00-00";
		else if ($this->Reg_Data_Archiviazione == "0000-00-00") $this->Reg_Data_Archiviazione = "0000-00-00";
		else $this->Reg_Data_Archiviazione = to_mysql_date($this->Reg_Data_Archiviazione);
		if ($this->Reg_Data_Stampa_Archiviazione == "") $this->Reg_Data_Stampa_Archiviazione = "0000-00-00";
		else if ($this->Reg_Data_Stampa_Archiviazione == "0000-00-00") $this->Reg_Data_Stampa_Archiviazione = "0000-00-00";
		else $this->Reg_Data_Stampa_Archiviazione = to_mysql_date($this->Reg_Data_Stampa_Archiviazione);
		if ($this->Reg_Data_Emissione_Provvedimento == "") $this->Reg_Data_Emissione_Provvedimento = "0000-00-00";
		else if ($this->Reg_Data_Emissione_Provvedimento == "0000-00-00") $this->Reg_Data_Emissione_Provvedimento = "0000-00-00";
		else $this->Reg_Data_Emissione_Provvedimento = to_mysql_date($this->Reg_Data_Emissione_Provvedimento);
		if ($this->Reg_Data_Esecuzione_Impossibile == "") $this->Reg_Data_Esecuzione_Impossibile = "0000-00-00";
		else if ($this->Reg_Data_Esecuzione_Impossibile == "0000-00-00") $this->Reg_Data_Esecuzione_Impossibile = "0000-00-00";
		else $this->Reg_Data_Esecuzione_Impossibile = to_mysql_date($this->Reg_Data_Esecuzione_Impossibile);
		
		//$this->Reg_Localita_Violazione = addslashes($this->Reg_Localita_Violazione);
		//$this->Reg_Motivo_Annullamento = addslashes($this->Reg_Motivo_Annullamento);
		//$this->Reg_Motivi_Esecuzione_Impossibile = addslashes($this->Reg_Motivi_Esecuzione_Impossibile);
		
		$this->Coll_Articoli_Infranti = array();
		$temp = $this->Reg_Articoli_Infrazione;
		$splittoArticoli = explode("**", $temp);
		
		$sommaImpAmministrative = 0;
		$sommaImpMaxAmministrative = 0;
		if ($temp != "")
		{
			for ($i = 0; $i < count($splittoArticoli); $i++)
			{
				$this->Coll_Articoli_Infranti[$i] = new targhe_estere_articoli($splittoArticoli[$i]);
				$this->Coll_Articoli_Infranti[$i]->DiurnoNotturno($this->Reg_Ora_Avviso);
				$sommaImpAmministrative += $this->Coll_Articoli_Infranti[$i]->Tar_Tariffa_Sanz;
				$sommaImpMaxAmministrative += $this->Coll_Articoli_Infranti[$i]->Tar_Tariffa_Sanz_Max;
			}
			$sommaImpAmministrative = $this->NumeroPerDB($sommaImpAmministrative);
			$sommaImpMaxAmministrative = $this->NumeroPerDB($sommaImpMaxAmministrative);
		}
		//alert ($this->Reg_Importo_Amministrativo);
		$this->Reg_Spese_Notifica_Comune = $this->NumeroPerDB($this->Reg_Spese_Notifica_Comune);
		$this->Reg_Spese_Ricerca_Comune = $this->NumeroPerDB($this->Reg_Spese_Ricerca_Comune);
		if ($this->Reg_Importo_Amministrativo == NULL)
			$this->Reg_Importo_Amministrativo = $sommaImpAmministrative;
		if ($this->Reg_Importo_Sanzione_Massima == NULL)
			$this->Reg_Importo_Sanzione_Massima = $sommaImpMaxAmministrative;
		$this->Reg_Importo_Amministrativo = $this->NumeroPerDB($this->Reg_Importo_Amministrativo);
		$this->Reg_Importo_Sanzione_Massima = $this->NumeroPerDB($this->Reg_Importo_Sanzione_Massima);
		$this->Reg_Spese_Notifica_Sarida = $this->NumeroPerDB($this->Reg_Spese_Notifica_Sarida);
		$this->Reg_Spese_Ricerca_Sarida = $this->NumeroPerDB($this->Reg_Spese_Ricerca_Sarida);
		
		$this->Coll_Accertatori = array();
		$temp = $this->Reg_Accertatori;
		$splittoAccertatori = explode("**", $temp);
		if ($temp != "")
		{
			for ($i = 0; $i < count($splittoAccertatori); $i++)
			{
				$this->Coll_Accertatori[$i] = new targhe_estere_accertatori($splittoAccertatori[$i]);
			}
		}
	}
	
	public function SettaProssimoRegistroCrono ()
	{
		//if (!isset($this->Reg_Progr_Registro)) return -1;
		if ($this->Reg_Anno == 0) return -1;
		$queryMax = "SELECT Reg_Progr, Reg_Progr_Registro FROM registro_cronologico_cds ";
		$queryMax .= "WHERE Reg_Anno = " . $this->Reg_Anno;
		$queryMax .= " AND Reg_Comune_Violazione = '" . $this->Reg_Comune_Violazione ."'";
		$queryMax .= " ORDER BY Reg_Progr_Registro DESC";
		$res = mysql_query($queryMax);
		if (mysql_num_rows($res) == 0)
		{
			$maxProgr = 1;
			return $maxProgr;
		}
		$rigaProgr = mysql_fetch_assoc($res);  //  prendo solo il primo (il numero più alto)
		$maxProgrReg = $rigaProgr['Reg_Progr_Registro'];
		
		if ($this->Reg_Progr == $rigaProgr['Reg_Progr'])  //  questo progr ha il max progr_registro
		{
			if ($this->Reg_Progr_Registro == 0) $maxProgrReg = 1;  //  non ho ancora il progr_registro
			else $maxProgrReg = $this->Reg_Progr_Registro;
		}
		else
		{
			if ($this->Reg_Progr_Registro == 0) $maxProgrReg ++;
			else $maxProgrReg = $this->Reg_Progr_Registro;
		}
		return $maxProgrReg;
	}
	
	public function ProgressivoGiaPresente ()
	{
		if ($this->Reg_Progr == NULL) return false;
		$queryPresente = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryPresente .= "WHERE Reg_Progr = " . $this->Reg_Progr;
		$res = mysql_query($queryPresente);
		$rigaProgr = mysql_fetch_assoc($res);
		if ($rigaProgr['Reg_Progr'] != NULL) return true;
		else return false;
	}
	
	public function RegistroGiaPresente ()
	{
		if ($this->Reg_Progr_Registro == NULL || $this->Reg_Anno == NULL) return false;
		if ($this->Reg_Progr_Registro == 0) return false;
		$queryPresente = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryPresente .= "WHERE Reg_Progr_Registro = " . $this->Reg_Progr_Registro . " AND ";
		//$queryPresente .= "Reg_Comune_Violazione = '" . $this->Reg_Comune_Violazione . " AND ";
		$queryPresente .= "Reg_Anno = " . $this->Reg_Anno;
		$res = mysql_query($queryPresente);
		$rigaProgr = mysql_fetch_assoc($res);
		if ($rigaProgr['Reg_Progr'] != NULL) return true;
		else return false;
	}
	
	public function ViolazioneGiaPresente ()
	{
		$risposta = $this->ViolazioneNonAnnullataGiaPresente();
		if ($risposta == "")
		{
			$risposta = $this->ViolazioneAnnullataGiaPresente();
		}
		return $risposta;
	}
	
	public function ViolazioneAnnullataGiaPresente ()
	{
		if (strlen($this->Reg_Ora_Avviso) == 5)  //  arriva 11:23
		{
			$ctrlOra = "Reg_Ora_Avviso LIKE '" . $this->Reg_Ora_Avviso . "%' AND ";
		}
		else  //  arriva 11:23:45 (con i secondi)
		{
			$ctrlOra = "Reg_Ora_Avviso = '" . $this->Reg_Ora_Avviso . "' AND ";
		}
		$queryPresente = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryPresente .= "WHERE Reg_Comune_Violazione = '" . $this->Reg_Comune_Violazione . "' AND ";
		$queryPresente .= "Reg_Targa_Veicolo = '" . $this->Reg_Targa_Veicolo . "' AND ";
		/*$_SESSION['CC_User'] != "***+"*/ $queryPresente .= $ctrlOra;
		$queryPresente .= "Reg_Data_Avviso = '" . $this->Reg_Data_Avviso . "' ";
		$res = mysql_query($queryPresente);
		$rigaProgr = mysql_fetch_assoc($res);
		
		//if ($_SESSION['CC_User'] == "***+") echo "<br>" . $queryPresente . " --> " . $rigaProgr['Reg_Progr'];
		//alert ($queryPresente);
		return $rigaProgr['Reg_Progr'];
	}
	
	public function ViolazioneNonAnnullataGiaPresente ()
	{
		if (strlen($this->Reg_Ora_Avviso) == 5)  //  arriva 11:23
		{
			$ctrlOra = "Reg_Ora_Avviso LIKE '" . $this->Reg_Ora_Avviso . "%' AND ";
		}
		else  //  arriva 11:23:45 (con i secondi)
		{
			$ctrlOra = "Reg_Ora_Avviso = '" . $this->Reg_Ora_Avviso . "' AND ";
		}
		$queryPresente = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryPresente .= "WHERE Reg_Comune_Violazione = '" . $this->Reg_Comune_Violazione . "' AND ";
		$queryPresente .= "Reg_Targa_Veicolo = '" . $this->Reg_Targa_Veicolo . "' AND ";
		/*$_SESSION['CC_User'] != "***+"*/ $queryPresente .= $ctrlOra;
		$queryPresente .= "Reg_Data_Avviso = '" . $this->Reg_Data_Avviso . "' AND ";
		$queryPresente .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
		$queryPresente .= "Reg_Data_Annullamento = '0000-00-00'  ";
		$res = mysql_query($queryPresente);
		$rigaProgr = mysql_fetch_assoc($res);
		
		//if ($_SESSION['CC_User'] == "***+") echo "<br>" . $queryPresente . " --> " . $rigaProgr['Reg_Progr'];
		//alert ($queryPresente);
		return $rigaProgr['Reg_Progr'];
	}
	
	public function TargaItaliana ()
	{
		if ($this->Reg_Nazionalita_Targa == "I") return true;
		else return false;
	}
	
	public function CercaRegDaImmagine ($strPicture)
	{
		$queryImg = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryImg .= " WHERE Reg_Immagini like '%" . $strPicture . "%' ";
		$resImg = mysql_query($queryImg);
		$rigaImg = mysql_fetch_assoc($resImg);
		return $rigaImg['Reg_Progr'];
	}
	
	public function CercaRegDaTargaDataRichiesta ($targa, $data, $comune)
	{
		$queryTarga = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryTarga .= " WHERE Reg_Data_Stampa_Richiesta = '" . $data . "' ";
		$queryTarga .= " AND Reg_Comune_Violazione = '" . $comune . "' ";
		$queryTarga .= " AND Reg_Targa_Veicolo = '" . $targa . "' ";
		//$queryTarga .= " AND Reg_Stato_erbale = 'AUTOMPORTATO' ";
		$resTarga = mysql_query($queryTarga);
		$rigaTarga = mysql_fetch_assoc($resTarga);
		return $rigaTarga['Reg_Progr'];
	}
	
	public function CercaRegDaTargaOlandese ($targa, $comune)
	{
		$queryTarga = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryTarga .= " WHERE Reg_Comune_Violazione = '" . $comune . "' ";
		$queryTarga .= " AND Reg_Targa_Veicolo = '" . $targa . "' ";
		$queryTarga .= " AND Reg_Ente_Per_Richiesta = '3' ";
		$resTarga = mysql_query($queryTarga);
		$rigaTarga = mysql_fetch_assoc($resTarga);
		//echo "<br>" . $queryTarga . " --> " . mysql_num_rows($resTarga);
		return $rigaTarga['Reg_Progr'];
	}
	
	public function QueryAnnullatiSvizzeri ($comune, $dueLettere, $esclusione = "")
	{
		$queryTargheSviz = "SELECT Tar_Progr FROM targhe_estere_zone_competenza ";
		$queryTargheSviz .= " WHERE Tar_Nazione_Nome = 'Svizzera' ";
		
		if ($dueLettere != "") $queryTargheSviz .= " AND Tar_Regione = '$dueLettere' ";
		
		$resTargheSviz = mysql_query($queryTargheSviz);
		$sceltaProgr = "";
		while ($rigaTarga = mysql_fetch_assoc($resTargheSviz))
		{
			$sceltaProgr .= " Reg_Ente_Per_Richiesta = " . $rigaTarga['Tar_Progr'] . " OR ";
		}
		if ($sceltaProgr != "")
		{
			$sceltaProgr = substr($sceltaProgr, 0, -3);  //  tolgo ultimo OR
			$sceltaProgr = " AND ( " . $sceltaProgr . " ) ";
		}
		
		$querySvizzera = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$querySvizzera .= " WHERE Reg_Comune_Violazione = '" . $comune . "' ";
		$querySvizzera .= $sceltaProgr;
		$querySvizzera .= " AND (Reg_Data_Annullamento != '0000-00-00' OR Reg_Data_Esecuzione_Impossibile != '0000-00-00' ) ";
		if ($esclusione != "") $querySvizzera .= $esclusione;
		$resSvizzera = mysql_query($querySvizzera);
		
		$arraySvizzere = array();
		while ($rigaTarga = mysql_fetch_assoc($resSvizzera))
		{
			$arraySvizzere[] = $rigaTarga['Reg_Progr'];
		}
		return $arraySvizzere;
	}
	
	public function DataImmagineDaDataAvviso ()
	{
		$myData = substr($this->Reg_Data_Avviso, 2, 2) .
					substr($this->Reg_Data_Avviso, 5, 2) .
					substr($this->Reg_Data_Avviso, 8, 2);
		return $myData;
	}
	
	public function OraImmagineDaOraAvviso ()
	{
		$myOra = substr($this->Reg_Ora_Avviso, 0, 2);
		$myOra .= substr($this->Reg_Ora_Avviso, 3, 2);
		$temp = substr($this->Reg_Ora_Avviso, 6, 2);
		if ($temp == "") $myOra .= "00";
		else $myOra .= $temp;
		return $myOra;
	}
	
	public function DataStampaRichiestaEsistente ($dataYYYYmmdd)
	{
		$queryCercaData = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryCercaData .= " WHERE Reg_Data_Stampa_Richiesta = '" . $dataYYYYmmdd . "'";
		$resCercaData = mysql_query($queryCercaData);
		$rigaCercaData = mysql_fetch_assoc($resCercaData);
		return $rigaCercaData['Reg_Progr'];
	}
	
	public function DataStampaVerbaleEsistente ($comuneReg, $numeroReg, $annoReg, $dataYYYYmmdd)
	{
		$queryCercaData = "SELECT Reg_Progr FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryCercaData .= " WHERE Verbale_ID = Reg_Progr AND ";
		$queryCercaData .= " Data_Stampa_Notifica = '" . $dataYYYYmmdd . "' AND ";
		$queryCercaData .= " Reg_Comune_Violazione = '" . $comuneReg . "' AND ";
		$queryCercaData .= " Reg_Progr_Registro = '" . $numeroReg . "' AND ";
		$queryCercaData .= " Reg_Anno = '" . $annoReg . "' ";
		$resCercaData = mysql_query($queryCercaData);
		//$rigaCercaData = mysql_fetch_assoc($resCercaData);
		$numeroCercaData = mysql_num_rows($resCercaData);
		//echo "<br>" . $queryCercaData;// . " --> " . mysql_num_rows($resCercaData);
		//return $rigaCercaData['Reg_Progr'];
		return $numeroCercaData;
	}
	
	public function DataStampaVerbaleComuniEsistente ($comuneReg, $numeroStampa, $annoReg, $dataYYYYmmdd)
	{
		$queryCercaData = "SELECT Reg_Progr FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryCercaData .= " WHERE Verbale_ID = Reg_Progr AND ";
		$queryCercaData .= " Data_Stampa_Notifica = '" . $dataYYYYmmdd . "' AND ";
		$queryCercaData .= " Reg_Comune_Violazione = '" . $comuneReg . "' AND ";
		$queryCercaData .= " Numero_Stampa_Comune = '" . $numeroStampa . "' AND ";
		$queryCercaData .= " Reg_Anno = '" . $annoReg . "' ";
		$resCercaData = mysql_query($queryCercaData);
		//$rigaCercaData = mysql_fetch_assoc($resCercaData);
		$numeroCercaData = mysql_num_rows($resCercaData);
		//echo "<br>" . $queryCercaData;// . " --> " . mysql_num_rows($resCercaData);
		//return $rigaCercaData['Reg_Progr'];
		return $numeroCercaData;
	}
	
	public function CercaCartellaFoto ()
	{
		$stringa = $this->Reg_Immagini;
		$esplodo = explode ("**", $stringa);
		$esplodoSingolo = explode("_", $esplodo[0]);  // prendo il primo: 111111_141231_121212_....
		$anno = "20" . substr($esplodoSingolo[1], 0, 2);
		$mese = substr($esplodoSingolo[1], 2, 2);
		$giorno = substr($esplodoSingolo[1], 4, 2);
		$cartella = $anno . "-" . $mese . "-" . $giorno;
		return $cartella;
	}
	
	public function SettaNomeImmagine ()  //  1 per la prima foto, 2 per la seconda, ecc
	{
		switch ($this->Reg_Genere_Infrazione)
		{
			case "SEMAFORO": break;
			case "AUTOVELOX": break;
			case "ZTL": return ""; break;
			case "SOSTA": return ""; break;
			case "126BIS": return ""; break;
			case "ALTRO": return ""; break;
			default:
				alert ("tipo non gestito");
				return "";
				break;
		}
		
		if ($this->Reg_Immagini == "")
		{
			//alert ("ATTENZIONE: infrazione senza immagini allegate");
			return "";
		}
		
		if ($this->Reg_Rilevatore_Elettronico == "") return "";
		if ($this->Reg_Articoli_Infrazione == "") return "";
		
		$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
		/*$myArticolo = new targhe_estere_rilevatori_velocita($this->Reg_Articoli_Infrazione);
		if ($myRilevatore->Ril_Velocita != "N")
			alert ("Errore: sto analizzando tempi semaforo in select146");*/
		
		
		$img = $this->Reg_Immagini;
		$esplodoImg = explode ("**", $img);
		$nuovaStringaImg = "";
		
		$myData = $this->DataImmagineDaDataAvviso();
		
		$myOra = $this->OraImmagineDaOraAvviso();
		
		$percorsoOrigine = $_SERVER['DOCUMENT_ROOT'] . "/FotoTargheEstere/" . $this->Reg_Comune_Violazione . "/DaAssociare/";
		$percorsoCtrl = $_SERVER['DOCUMENT_ROOT'] . "/FotoTargheEstere/" .
						$this->Reg_Comune_Violazione . "/" . $this->Reg_Data_Avviso . "/";
		crea_dir($percorsoCtrl);
		
		for ($z = 1; $z <= count($esplodoImg); $z++)
		{
			$cifre6 = "1";
			$avanti = false;
			
			switch ($this->Reg_Genere_Infrazione)
			{
				case "SEMAFORO":
					if ($z == 1) $ultimalettera = "F";
					else if ($z == 2) $ultimalettera = "G";
					break;
				case "AUTOVELOX":
					if ($z == 1) $ultimalettera = "A";
					break;
				case "ZTL":
					return "noimage";
					break;
				case "SOSTA":
					return "noimage";
					break;
				case "126BIS":
					return "noimage";
					break;
				case "ALTRO":
					return "noimage";
					break;
				default:
					$ultimalettera = "A";
					alert ("tipo non gestito");
					break;
			}
			while ($avanti == false)
			{
				$scrivoCifre6 = "";
				for ($k = strlen($cifre6); $k < 6; $k++)
					$scrivoCifre6 = "0" . $scrivoCifre6;
				$scrivoCifre6 .= $cifre6;
				
				$nomeImg = $myRilevatore->Ril_Matricola_Sistema . "_" .
						$myData . "_" .
						$myOra . "_" .
						$scrivoCifre6 . 
						$ultimalettera . 
						".JPG";
				
				$fileOrig = $percorsoOrigine . $esplodoImg[$z-1];
				$fileCtrl = $percorsoCtrl . $nomeImg;
				if (!file_exists($fileOrig))
				{
					if (!file_exists($fileCtrl))
					{
						return "";  //  errore: non c'è nè DA DOVE prendere, nè A DOVE mettere 
					}
					else  //  OK: non c'è DA DOVE prendere, perchè è già al suo posto
					{
						$avanti = true;
					}
				}
				else 
				{
					if (!file_exists($fileCtrl))
					{
						rename ($fileOrig, $fileCtrl);  //  OK: c'è DA DOVE prendere e non A DOVE mettere
						$avanti = true;
					}
					else
					{
						unlink ($fileOrig); // OK: c'è sia DA DOVE prendere, sia A DOVE mettere (non so come mai, ma va bene)
						$avanti = true;
					}
				}
				//else alert ("SI " . $fileCtrl);
				$cifre6++;
			}
			
			$nuovaStringaImg .= $nomeImg . "**";
		}
		$nuovaStringaImg = substr($nuovaStringaImg, 0, -2);  //  tolgo l'ultimo **
		return $nuovaStringaImg;
	}
	
	public function SettaNomeNoleggioImmagine ()  //  1 per la prima foto, 2 per la seconda, ecc
	{
		switch ($this->Reg_Genere_Infrazione)
		{
			case "SEMAFORO": break;
			case "AUTOVELOX": break;
			case "ZTL": return ""; break;
			case "SOSTA": return ""; break;
			case "126BIS": return ""; break;
			case "ALTRO": return ""; break;
			default:
				alert ("tipo non gestito");
				return "";
				break;
		}
		
		if ($this->Reg_Immagini == "")
		{
			//alert ("ATTENZIONE: infrazione senza immagini allegate");
			return "";
		}
		
		if ($this->Reg_Rilevatore_Elettronico == "") return "";
		if ($this->Reg_Articoli_Infrazione == "") return "";
		
		$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
		/*$myArticolo = new targhe_estere_rilevatori_velocita($this->Reg_Articoli_Infrazione);
		if ($myRilevatore->Ril_Velocita != "N")
			alert ("Errore: sto analizzando tempi semaforo in select146");*/
		
		
		$img = $this->Reg_Immagini;
		$esplodoImg = explode ("**", $img);
		$nuovaStringaImg = "";
		
		$myData = $this->DataImmagineDaDataAvviso();
		
		$myOra = $this->OraImmagineDaOraAvviso();
		
		$percorsoOrigine = $_SERVER['DOCUMENT_ROOT'] . "/FotoTargheEstere/" . $this->Reg_Comune_Violazione . "/DaAssociareNoleggi/";
		$percorsoCtrl = $_SERVER['DOCUMENT_ROOT'] . "/FotoTargheEstere/" .
						$this->Reg_Comune_Violazione . "/" . $this->Reg_Data_Avviso . "/";
		crea_dir($percorsoCtrl);
		
		for ($z = 1; $z <= count($esplodoImg); $z++)
		{
			$cifre6 = "1";
			$avanti = false;
			
			switch ($this->Reg_Genere_Infrazione)
			{
				case "SEMAFORO":
					if ($z == 1) $ultimalettera = "F";
					else if ($z == 2) $ultimalettera = "G";
					break;
				case "AUTOVELOX":
					if ($z == 1) $ultimalettera = "A";
					break;
				case "ZTL":
					return "noimage";
					break;
				case "SOSTA":
					return "noimage";
					break;
				case "126BIS":
					return "noimage";
					break;
				case "ALTRO":
					return "noimage";
					break;
				default:
					$ultimalettera = "A";
					alert ("tipo non gestito");
					break;
			}
			while ($avanti == false)
			{
				$scrivoCifre6 = "";
				for ($k = strlen($cifre6); $k < 6; $k++)
					$scrivoCifre6 = "0" . $scrivoCifre6;
				$scrivoCifre6 .= $cifre6;
				
				$nomeImg = $myRilevatore->Ril_Matricola_Sistema . "_" .
						$myData . "_" .
						$myOra . "_" .
						$scrivoCifre6 . 
						$ultimalettera . 
						".JPG";
				
				$fileOrig = $percorsoOrigine . $esplodoImg[$z-1];
				$fileCtrl = $percorsoCtrl . $nomeImg;
				if (!file_exists($fileOrig))
				{
					if (!file_exists($fileCtrl))
					{
						return "";  //  errore: non c'è nè DA DOVE prendere, nè A DOVE mettere 
					}
					else  //  OK: non c'è DA DOVE prendere, perchè è già al suo posto
					{
						$avanti = true;
					}
				}
				else 
				{
					if (!file_exists($fileCtrl))
					{
						rename ($fileOrig, $fileCtrl);  //  OK: c'è DA DOVE prendere e non A DOVE mettere
						$avanti = true;
					}
					else
					{
						unlink ($fileOrig); // OK: c'è sia DA DOVE prendere, sia A DOVE mettere (non so come mai, ma va bene)
						$avanti = true;
					}
				}
				//else alert ("SI " . $fileCtrl);
				$cifre6++;
			}
			
			$nuovaStringaImg .= $nomeImg . "**";
		}
		$nuovaStringaImg = substr($nuovaStringaImg, 0, -2);  //  tolgo l'ultimo **
		return $nuovaStringaImg;
	}
	
	public function ModificaNomeImmagine ()
	{
		switch ($this->Reg_Genere_Infrazione)
		{
			case "SEMAFORO": break;
			case "AUTOVELOX": break;
			case "ZTL": return ""; break;
			case "SOSTA": return ""; break;
			case "126BIS": return ""; break;
			case "ALTRO": return ""; break;
			default:
				alert ("tipo non gestito");
				return "";
				break;
		}
		
		if ($this->Reg_Rilevatore_Elettronico == "") return "";
		if ($this->Reg_Articoli_Infrazione == "") return "";
		
		//$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
		/*$myArticolo = new targhe_estere_rilevatori_velocita($this->Reg_Articoli_Infrazione);
		 if ($myRilevatore->Ril_Velocita != "N")
			alert ("Errore: sto analizzando tempi semaforo in select146");*/
		
		$myData = $this->DataImmagineDaDataAvviso();
		
		$myOra = $this->OraImmagineDaOraAvviso();
		
		if ($this->Reg_Immagini != "")
		{
			$img = $this->Reg_Immagini;
			$esplodoImg = explode ("**", $img);
			$nuovaStringaImg = "";
			
			for ($z = 1; $z <= count($esplodoImg); $z++)
			{
				//echo $esplodoImg[$z-1]. ";";
				$dividoParti = explode ("_", $esplodoImg[$z-1]);
				$matricola = $dividoParti[0];
				$data = $dividoParti[1];
				$ora = $dividoParti[2];
				$numero = $dividoParti[3];
				
				$dataYmd = "20" . substr($data, 0, 2) . "-" .
						substr($data, 2, 2) . "-" .
						substr($data, 4, 2);
				
				$percorsoOrigine = $_SERVER['DOCUMENT_ROOT'] . "/FotoTargheEstere/" . 
						$this->Reg_Comune_Violazione . "/" . $dataYmd . "/";
				$percorsoCtrl = $_SERVER['DOCUMENT_ROOT'] . "/FotoTargheEstere/" .
						$this->Reg_Comune_Violazione . "/" . $this->Reg_Data_Avviso . "/";
				crea_dir($percorsoCtrl);
				
				$cifre6 = "1";
				$avanti = false;
					
				switch ($this->Reg_Genere_Infrazione)
				{
					case "SEMAFORO":
							if ($z == 1) $ultimalettera = "F";
							else if ($z == 2) $ultimalettera = "G";
							break;
					case "AUTOVELOX":
							if ($z == 1) $ultimalettera = "A";
							break;
					case "ZTL":
							return "noimage";
							break;
					case "SOSTA":
							return "noimage";
							break;
					case "126BIS":
							return "noimage";
							break;
					case "ALTRO":
							return "noimage";
					break;
						default:
						$ultimalettera = "A";
						alert ("tipo non gestito");
					break;
				}
				while ($avanti == false)
				{
					$scrivoCifre6 = "";
					for ($k = strlen($cifre6); $k < 6; $k++)
						$scrivoCifre6 = "0" . $scrivoCifre6;
					$scrivoCifre6 .= $cifre6;
			
					$nomeImg =  $matricola . "_" .
								$myData . "_" .
								$myOra . "_" .
								$scrivoCifre6 .
								$ultimalettera .
								".JPG";
			
					$fileOrig = $percorsoOrigine . $esplodoImg[$z-1];
					$fileCtrl = $percorsoCtrl . $nomeImg;
					if (!file_exists($fileOrig))
					{
						if (!file_exists($fileCtrl))
						{
							return "";  //  errore: non c'è nè DA DOVE prendere, nè A DOVE mettere
						}
						else  //  OK: non c'è DA DOVE prendere, perchè è già al suo posto
							$avanti = true;
					}
					else
					{
						if (!file_exists($fileCtrl))
						{
							rename ($fileOrig, $fileCtrl);  //  OK: c'è DA DOVE prendere e non A DOVE mettere
							//echo "rename ($fileOrig, $fileCtrl);";
							$avanti = true;
						}
						else
						{
							//unlink ($fileOrig); // OK: c'è sia DA DOVE prendere, sia A DOVE mettere (non è cambiato nome)
							//echo "unlink ($fileOrig);";
							$avanti = true;
						}
					}
						//else alert ("SI " . $fileCtrl);
					$cifre6++;
				}
						
				$nuovaStringaImg .= $nomeImg . "**";
			}
			$nuovaStringaImg = substr($nuovaStringaImg, 0, -2);  //  tolgo l'ultimo **
			return $nuovaStringaImg;
		}
		else return "";
	}
	
	
	public function SettaDocumentazione ($stringa)  //  stringa =   verbale**doc1.jpg**notifica**not1.jpg
	{
		switch ($this->Reg_Genere_Infrazione)
		{
			case "SEMAFORO": break;
			case "AUTOVELOX": break;
			case "ZTL": break;
			case "SOSTA": break;
			case "126BIS": break;
			case "ALTRO": break;
			default:
				alert ("tipo non gestito");
				return "";
				break;
		}
		$doc = $stringa;
		$esplodoDoc = explode ("**", $doc);
		$nuovaStringaDoc = "";
		
		$myData = $this->DataImmagineDaDataAvviso();
		
		$myOra = $this->OraImmagineDaOraAvviso();
		
		$percorsoOrigine = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" . 
				$this->Reg_Comune_Violazione . "/DaAssociare/";
		$percorsoCtrl = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" .
				$this->Reg_Comune_Violazione . "/" . $this->Reg_Data_Avviso . "/";
		crea_dir($percorsoCtrl);
		
		for ($z = 0; $z < count($esplodoDoc); $z++)
		{
			if ($z % 2 == 1)
			{
				$fileOrig = $percorsoOrigine . $esplodoDoc[$z];
				$fileCtrl = $percorsoCtrl . $esplodoDoc[$z];
				if (!file_exists($fileOrig))
				{
					if (!file_exists($fileCtrl))
					{
						return "";  //  errore: non c'è nè DA DOVE prendere, nè A DOVE mettere 
					}
					//else  OK: non c'è DA DOVE prendere, perchè è già al suo posto
				}
				else 
				{
					if (!file_exists($fileCtrl))
					{
						rename ($fileOrig, $fileCtrl);  //  OK: c'è DA DOVE prendere e non A DOVE mettere
					}
					else
					{
						unlink ($fileOrig); // OK: c'è sia DA DOVE prendere, sia A DOVE mettere (non so come mai, ma va bene)
					}
				}
			}
		}
		return $stringa;
		
	}
	
	
	public function SettaImportDocumentazione ($cartellaCompleta, $stringa)  	//  cartellaCompleta = $_SERVER['DOCUMENT_ROOT'] /Importazioni/cartellacomune/IMG/";
																				//  stringa =   verbale**doc1.jpg**notifica**not1.jpg
	{
		switch ($this->Reg_Genere_Infrazione)
		{
			case "SEMAFORO": break;
			case "AUTOVELOX": break;
			case "ZTL": break;
			case "SOSTA": break;
			case "126BIS": break;
			case "ALTRO": break;
			default:
				alert ("tipo non gestito");
				return "";
				break;
		}
		$doc = $stringa;
		$esplodoDoc = explode ("**", $doc);
		$nuovaStringaDoc = "";
		
		$myData = $this->DataImmagineDaDataAvviso();
		
		$myOra = $this->OraImmagineDaOraAvviso();
		
		$percorsoOrigine = $cartellaCompleta;
		$percorsoCtrl = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" .
				$this->Reg_Comune_Violazione . "/" . $this->Reg_Data_Avviso . "/";
		crea_dir($percorsoCtrl);
		
		for ($z = 0; $z < count($esplodoDoc); $z++)
		{
			if ($z % 2 == 1)
			{
				$fileOrig = $percorsoOrigine . $esplodoDoc[$z];
				$fileCtrl = $percorsoCtrl . $esplodoDoc[$z];
				if (!file_exists($fileOrig))
				{
					if (!file_exists($fileCtrl))
					{
						return "";  //  errore: non c'è nè DA DOVE prendere, nè A DOVE mettere 
					}
					//else  OK: non c'è DA DOVE prendere, perchè è già al suo posto
				}
				else 
				{
					if (!file_exists($fileCtrl))
					{
						rename ($fileOrig, $fileCtrl);  //  OK: c'è DA DOVE prendere e non A DOVE mettere
					}
					else
					{
						unlink ($fileOrig); // OK: c'è sia DA DOVE prendere, sia A DOVE mettere (non so come mai, ma va bene)
					}
				}
			}
		}
		return $stringa;
		
	}
	
	
	public function SettaNoleggioDocumentazione ($stringa)  //  stringa =   verbale**doc1.jpg**notifica**not1.jpg
	{
		switch ($this->Reg_Genere_Infrazione)
		{
			case "SEMAFORO": break;
			case "AUTOVELOX": break;
			case "ZTL": break;
			case "SOSTA": break;
			case "126BIS": break;
			case "ALTRO": break;
			default:
				alert ("tipo non gestito");
				return "";
				break;
		}
		$doc = $stringa;
		$esplodoDoc = explode ("**", $doc);
		$nuovaStringaDoc = "";
		
		$myData = $this->DataImmagineDaDataAvviso();
		
		$myOra = $this->OraImmagineDaOraAvviso();
		
		$percorsoOrigine = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" . 
				$this->Reg_Comune_Violazione . "/DaAssociareNoleggi/";
		$percorsoCtrl = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" .
				$this->Reg_Comune_Violazione . "/" . $this->Reg_Data_Avviso . "/";
		crea_dir($percorsoCtrl);
		
		for ($z = 0; $z < count($esplodoDoc); $z++)
		{
			if ($z % 2 == 1)
			{
				$fileOrig = $percorsoOrigine . $esplodoDoc[$z];
				$fileCtrl = $percorsoCtrl . $esplodoDoc[$z];
				if (!file_exists($fileOrig))
				{
					if (!file_exists($fileCtrl))
					{
						return "";  //  errore: non c'è nè DA DOVE prendere, nè A DOVE mettere 
					}
					//else  OK: non c'è DA DOVE prendere, perchè è già al suo posto
				}
				else 
				{
					if (!file_exists($fileCtrl))
					{
						rename ($fileOrig, $fileCtrl);  //  OK: c'è DA DOVE prendere e non A DOVE mettere
					}
					else
					{
						unlink ($fileOrig); // OK: c'è sia DA DOVE prendere, sia A DOVE mettere (non so come mai, ma va bene)
					}
				}
			}
		}
		return $stringa;
		
	}
	
	public function ModificaDocumentazione 
			($stringa,  //  stringa =   verbale**doc1.jpg**notifica**not1.jpg
			$oldData)  //  formato YYYY-mm-dd ($stringa)
	{
		switch ($this->Reg_Genere_Infrazione)
		{
			case "SEMAFORO": break;
			case "AUTOVELOX": break;
			case "ZTL": break;
			case "SOSTA": break;
			case "126BIS": break;
			case "ALTRO": break;
			default:
				alert ("tipo non gestito");
				return "";
				break;
		}
		$doc = $stringa;
		$esplodoDoc = explode ("**", $doc);
		$nuovaStringaDoc = "";
		
		$myData = $this->DataImmagineDaDataAvviso();
	
		$myOra = $this->OraImmagineDaOraAvviso();
	
		for ($z = 0; $z < count($esplodoDoc); $z++)
		{
			$percorsoOrigine = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" .
					$this->Reg_Comune_Violazione . "/" . $oldData . "/";
			$percorsoCtrl = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" .
					$this->Reg_Comune_Violazione . "/" . $this->Reg_Data_Avviso . "/";
			$percorsoDaAss = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" .
					$this->Reg_Comune_Violazione . "/DaAssociare/";
			crea_dir($percorsoCtrl);
			
			if ($z % 2 == 1)
			{
				$fileOrig = $percorsoOrigine . $esplodoDoc[$z];
				$fileCtrl = $percorsoCtrl . $esplodoDoc[$z];
				$fileAss = $percorsoDaAss . $esplodoDoc[$z];
				if (!file_exists($fileOrig))
				{
					if (!file_exists($fileCtrl))
					{
						if (!file_exists($fileAss))
						{
							alert ("non so perchè  $fileOrig --- $fileCtrl");
							return "";  //  errore: non c'è nè DA DOVE prendere, nè A DOVE mettere
						}
						else
						{
							rename ($fileAss, $fileCtrl);
						}
					}
					//else  OK: non c'è DA DOVE prendere, perchè è già al suo posto
				}
				else
				{
					if (!file_exists($fileCtrl))
					{
						rename ($fileOrig, $fileCtrl);  //  OK: c'è DA DOVE prendere e non A DOVE mettere
						//echo "rename ($fileOrig, $fileCtrl);";
					}
					else
					{
						//unlink ($fileOrig); // OK: c'è sia DA DOVE prendere, sia A DOVE mettere (non è cambiato nome)
						//echo "unlink ($fileOrig);";
					}
				}
			}
		}
		return $stringa;
	
	}
	
	public function SpostaTutteFotoGiaLavorate ($stringaImg,  //  111111_130412_010203_111111.jpg**111111_130412_010203_111112.jpg
			$vecchiaData,  //  formato YYYY-mm-dd
			$vecchiaOra,  //  formato 12:23:01
			$nuovaData,  //  formato YYYY-mm-dd o DaAssociare
			$nuovaOra)  //  formato 12:23:01 o NULL
	{
		$temp = explode ("**", $stringaImg);
		for ($i = 0; $i < count($temp); $i++)
		{
			$this->SpostaFotoGiaLavorate ($temp[$i],  //  nome foto (111111_130412_010203_111111.jpg
									$vecchiaData,  //  formato YYYY-mm-dd
									$vecchiaOra,  //  formato 12:23:01
									$nuovaData,  //  formato YYYY-mm-dd o DaAssociare
									$nuovaOra);  //  formato 12:24:01
		}
	}
	
	public function SpostaFotoGiaLavorate ($daDovePrendere,  //  nome foto (111111_130412_010203_111111.jpg
			$vecchiaData,  //  formato YYYY-mm-dd
			$vecchiaOra,  //  formato 12:23:01
			$nuovaData,  //  formato YYYY-mm-dd o DaAssociare
			$nuovaOra)  //  formato 12:23:01 o NULL
	{
		if ($vecchiaData == $nuovaData && $vecchiaOra == $nuovaOra)
		{
			alert ("stessa data: errore ($vecchiaData -> $nuovaData)");
			return false;
		}
		alert("dadove " . $daDovePrendere);
		$dividoNomeFoto = explode (".", $daDovePrendere);
		$nomeFile = $dividoNomeFoto[0];
		$estensione = $dividoNomeFoto[1];
		$dividoParti = explode ("_", $nomeFile);
		$matricola = $dividoParti[0];
		$data = $dividoParti[1];
		$ora = $dividoParti[2];
		$numero = $dividoParti[3];
		
		$cartellaComune = "/FotoTargheEstere/" . $this->Reg_Comune_Violazione . "/";
		$cartellaDaDovePrendere = $cartellaComune . $vecchiaData . "/";
		$fileDaDovePrendere = $cartellaDaDovePrendere . $daDovePrendere;
		$avanzo = false;
		if (file_exists($fileDaDovePrendere)) $avanzo = true;
		else
		{
			$cartellaComune = $_SERVER['DOCUMENT_ROOT'] . "/FotoTargheEstere/" . $this->Reg_Comune_Violazione . "/";
			$cartellaDaDovePrendere = $cartellaComune . $vecchiaData . "/";
			$fileDaDovePrendere = $cartellaDaDovePrendere . $daDovePrendere;
			if (file_exists($fileDaDovePrendere)) $avanzo = true;
		}
		if ($avanzo == true)
		{
			if ($nuovaData == "DaAssociare")
			{
				$cartellaDoveSpostare = $cartellaComune . $nuovaData . "/";
				crea_dir($cartellaDoveSpostare);
				$fileDoveSpostare = $cartellaDoveSpostare . $daDovePrendere;
				if (file_exists($fileDoveSpostare))
					$fileDoveSpostare = $cartellaDoveSpostare . $nomeFile . "bis." . $estensione;
			}
			else 
			{
				$dataCorta = substr($nuovaData, 2, 2) . substr($nuovaData, 5, 2) . substr($nuovaData, 8, 2);
				$oraCorta = substr($nuovaOra, 0, 2) . substr($nuovaOra, 3, 2) . substr($nuovaOra, 6, 2);
				$cartellaDoveSpostare = $cartellaComune . $nuovaData . "/";
				crea_dir($cartellaDoveSpostare);
				$nuovoNome = $matricola . "_" . $dataCorta . "_" . $oraCorta . "_" . $numero . "." . $estensione;
				$fileDoveSpostare = $cartellaDoveSpostare . $nuovoNome;
				if (file_exists($fileDoveSpostare))
				{
					alert ("come faccio?");
					return false;
				}
			}
		}
		else
		{
			echo "<br>non esiste " . $fileDaDovePrendere . "<br>";
			return false;
		}
		rename ($fileDaDovePrendere, $fileDoveSpostare);
		//echo "faccio2:   <br>copy (<br>$fileDaDovePrendere, <br>$fileDoveSpostare);";
		return true;
	}
	
	public function SpostaTuttiDocumentiGiaLavorati ($stringaDocs,  //  Verbale**aaaa.jpg**Notifica**bbb.jpg**Pagamento**ccccc.jpg
			$vecchiaData,  //  formato YYYY-mm-dd
			$nuovaData)  //  formato YYYY-mm-dd o DaAssociare
	{
		$temp = explode ("**", $stringaDocs);
		for ($i = 0; $i < count($temp); $i++)
		{
			if ($i % 2 != 0)  //  solo i pari
			{
				$this->SpostaDocumentiGiaLavorati ($temp[$i],  //  nome documento aaaa.jpg
										$vecchiaData,  //  formato YYYY-mm-dd
										$nuovaData);  //  formato YYYY-mm-dd o DaAssociare
			}
		}
	}
	
	public function SpostaDocumentiGiaLavorati ($daDovePrendere,  //  nome documento aaaa.jpg
			$vecchiaData,  //  formato YYYY-mm-dd
			$nuovaData)  //  formato YYYY-mm-dd o DaAssociare
	{
		if ($vecchiaData == $nuovaData)
		{
			alert ("stessa data: errore ($vecchiaData -> $nuovaData)");
			return false;
		}
		$dividoNomeFoto = explode (".", $daDovePrendere);
		$nomeFile = $dividoNomeFoto[0];
		$estensione = $dividoNomeFoto[1];
	
		$cartellaComune = "/DocsTargheEstere/" . $this->Reg_Comune_Violazione . "/";
		$cartellaDaDovePrendere = $cartellaComune . $vecchiaData . "/";
		$fileDaDovePrendere = $cartellaDaDovePrendere . $daDovePrendere;
		$avanzo = false;
		if (file_exists($fileDaDovePrendere)) $avanzo = true;
		else
		{
			$cartellaComune = $_SERVER['DOCUMENT_ROOT'] . "/DocsTargheEstere/" . $this->Reg_Comune_Violazione . "/";
			$cartellaDaDovePrendere = $cartellaComune . $vecchiaData . "/";
			$fileDaDovePrendere = $cartellaDaDovePrendere . $daDovePrendere;
			if (file_exists($fileDaDovePrendere)) $avanzo = true;
		}
		if ($avanzo == true)
		{
			$cartellaDoveSpostare = $cartellaComune . $nuovaData . "/";
			$fileDoveSpostare = $cartellaDoveSpostare . $daDovePrendere;
			if (file_exists($fileDoveSpostare))
				$fileDoveSpostare = $cartellaDoveSpostare . $nomeFile . "bis." . $estensione;
		}
		else
		{
			echo "<br>non esiste " . $fileDaDovePrendere . "<br>";
			return false;
		}
		rename ($fileDaDovePrendere, $fileDoveSpostare);
		//echo "<br>faccio2:   <br>copy (<br>$fileDaDovePrendere, <br>$fileDoveSpostare);";
		return true;
	}
	
	public function InserisciArticolo ($art)
	{
		$countArt = count($this->Coll_Articoli_Infranti);
		
		/*if ($_SESSION['CC_User'] == "***+")
		{
			alert ("cccccooo " . $this->Coll_Articoli_Infranti[0]->Tar_Progr . "  mmm  " . $art);
			alert ("cccccooo " . $this->Coll_Articoli_Infranti[1]->Tar_Progr);
		}
				
				if ($_SESSION['CC_User'] == "***+")
					alert ($countArt);*/
		
		if ($countArt == 0 || ($countArt == 1 && $this->Coll_Articoli_Infranti[0]->Tar_Progr == ""))
		{
			$this->Reg_Articoli_Infrazione = $art;
			$tempArt = new targhe_estere_articoli($art);
			$tempArt->DiurnoNotturno($this->Reg_Ora_Avviso);
			$this->Coll_Articoli_Infranti[0] = $tempArt;
			$this->Reg_Importo_Amministrativo = $tempArt->Tar_Tariffa_Sanz;
			$this->Reg_Importo_Sanzione_Massima = $tempArt->Tar_Tariffa_Sanz_Max;
		}
		else
		{
			$tempArt = new targhe_estere_articoli($art);
			$tempArt->DiurnoNotturno($this->Reg_Ora_Avviso);
			
			/*if ($tempArt->Tar_Articolo == '142' &&   //  VELOCITA!!
					 ($tempArt->Tar_Comma == '7' || $tempArt->Tar_Comma == '8' || $tempArt->Tar_Comma == '9')
			)*/
			if (0) //if ($tempArt->Tar_Genere_Infrazione == "AUTOVELOX")
			{
				$this->Reg_Articoli_Infrazione = $art;  //  non possono essere più articolo di velocita!
				$this->Coll_Articoli_Infranti[0] = $tempArt;
				$this->Reg_Importo_Amministrativo = $tempArt->Tar_Tariffa_Sanz;
				$this->Reg_Importo_Sanzione_Massima = $tempArt->Tar_Tariffa_Sanz_Max;
			}
			else 
			{
				$sommaImporto = 0;
				$sommaMaxImporto = 0;
				$stringaImportiSeparati = "";
				for ($i = 0; $i < count($this->Coll_Articoli_Infranti); $i++)
				{
					$sommaImporto += $this->Coll_Articoli_Infranti[$i]->Tar_Tariffa_Sanz;
					$sommaMaxImporto += $this->Coll_Articoli_Infranti[$i]->Tar_Tariffa_Sanz_Max;
					$stringaImportiSeparati .= number_format ($tempArt->Tar_Tariffa_Sanz, 2, ",", "") . "**";
					if ($this->Coll_Articoli_Infranti[$i]->Tar_Progr == $art)
					{
						$this->Reg_Importo_Amministrativo = $sommaImporto;
						$this->Reg_Importo_Sanzione_Massima = $sommaMaxImporto;
						return;
					}
				}
				$this->Reg_Articoli_Infrazione .= "**" . $art;
				$tempArt = new targhe_estere_articoli($art);
				$tempArt->DiurnoNotturno($this->Reg_Ora_Avviso);
				$this->Coll_Articoli_Infranti[] = $tempArt;  //  aggiungo all'array
				$sommaImporto += $tempArt->Tar_Tariffa_Sanz;
				if ($stringaImportiSeparati != "") $stringaImportiSeparati = substr($stringaImportiSeparati, 0, -2);  // tolgo gli ultimi asterischi
				$this->Reg_Importi_Separati = $stringaImportiSeparati;
				
				$this->Reg_Importo_Amministrativo = $sommaImporto;
				$sommaMaxImporto += $tempArt->Tar_Tariffa_Sanz_Max;
				$this->Reg_Importo_Sanzione_Massima = $sommaMaxImporto;
			}
		}
	}
	
	public function ControllaArticoloLimitiRilevatore ($tipo)  //  $tipo = INS per insert/update
	{
		$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
		if ($myRilevatore->Ril_Progr == NULL) return "ERRORE_VELOCITA";
		if ($myRilevatore->Ril_Velocita != "Y") return "ERRORE_VELOCITA";
			//alert ("Errore: sto analizzando limite di velocità");
		else
		{
			$sanzAcc = "";
			
			$articoloRilevato = $this->SelectArticoloInfrazione142($sanzAcc);
			//if ($articoloRilevato != $articoloInfranto)
				//alert ("errore articolo non combaciante : $articoloRilevato != $articoloInfranto");
			if ($tipo == "INS")
			{
				$this->InserisciArticolo($articoloRilevato);
				return "OK_ARTICOLO";
			}
			else // per un controllo
			{
				for ($i = 0; $i < count ($this->Coll_Articoli_Infranti); $i++)
				{
					if ($articoloRilevato == $this->Coll_Articoli_Infranti[$i]->Tar_Progr)
					{
						//alert ("ok, articolo combaciante: $articoloRilevato ");
						return "OK_ARTICOLO";
					}
				}
			}
		}
		return "ERRORE_ARTICOLO";
	}
	
	public function ControllaArticoloTempiRilevatore ($tipo)  //  $tipo = INS per insert/update
	{
		$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
		if ($myRilevatore->Ril_Progr == NULL) return "ERRORE_VELOCITA";
		if ($myRilevatore->Ril_Velocita != "N") return "ERRORE_VELOCITA";
			//alert ("Errore: sto analizzando tempi del semaforo");
		else
		{
			$articoloRilevato = $this->SelectArticoloInfrazione146Comma3();
			//if ($articoloRilevato != $articoloInfranto)
				//alert ("errore articolo non combaciante : $articoloRilevato != $articoloInfranto");
			if ($tipo == "INS")
			{
				$this->InserisciArticolo($articoloRilevato);
				return "OK_ARTICOLO";
			}
			else // per un controllo
			{
				for ($i = 0; $i < count ($this->Coll_Articoli_Infranti); $i++)
				{
					if ($articoloRilevato == $this->Coll_Articoli_Infranti[$i]->Tar_Progr)
					{
						//alert ("ok, articolo combaciante: $articoloRilevato ");
						return "OK_ARTICOLO";
					}
				}
			}
		}
		return "ERRORE_ARTICOLO";
	}
	
	public function ControllaArticoloZtl ($tipo)  //  $tipo = INS per insert/update
	{
		/*if ($this->Reg_Rilevatore_Elettronico != NULL &&
			$this->Reg_Rilevatore_Elettronico != "0") return "ERRORE_ZTL";
		//alert ("Errore: sto analizzando tempi del semaforo");
		else
		{*/
			$articoloRilevato = $this->SelectArticoloInfrazione7Comma9();
			//if ($articoloRilevato != $articoloInfranto)
			//alert ("errore articolo non combaciante : $articoloRilevato != $articoloInfranto");
			if ($tipo == "INS")
			{
				$this->InserisciArticolo($articoloRilevato);
				return "OK_ARTICOLO";
			}
			else // per un controllo
			{
				for ($i = 0; $i < count ($this->Coll_Articoli_Infranti); $i++)
				{
					if ($articoloRilevato == $this->Coll_Articoli_Infranti[$i]->Tar_Progr)
					{
						//alert ("ok, articolo combaciante: $articoloRilevato ");
						return "OK_ARTICOLO";
					}
				}
			}
		//}
		return "ERRORE_ARTICOLO";
	}
	
	public function ControllaArticoloSosta ($tipo)  //  $tipo = INS per insert/update
	{
		if ($this->Reg_Rilevatore_Elettronico != NULL &&
			$this->Reg_Rilevatore_Elettronico != "0") return "ERRORE_SOSTA";
		//alert ("Errore: sto analizzando tempi del semaforo");
		else
		{
			$articoloRilevato = $this->SelectArticoloInfrazione7e157e158();
			//if ($articoloRilevato != $articoloInfranto)
			//alert ("errore articolo non combaciante : $articoloRilevato != $articoloInfranto");
			if ($tipo == "INS")
			{
				$this->InserisciArticolo($articoloRilevato);
				return "OK_ARTICOLO";
			}
			else // per un controllo
			{
				for ($i = 0; $i < count ($this->Coll_Articoli_Infranti); $i++)
				{
					if ($articoloRilevato == $this->Coll_Articoli_Infranti[$i]->Tar_Progr)
					{
						//alert ("ok, articolo combaciante: $articoloRilevato ");
						return "OK_ARTICOLO";
					}
				}
			}
		}
		return "ERRORE_ARTICOLO";
	}
	
	public function ControllaArticolo126Bis ($tipo)  //  $tipo = INS per insert/update
	{
		if ($this->Reg_Rilevatore_Elettronico != NULL &&
		$this->Reg_Rilevatore_Elettronico != "0") return "ERRORE_126BIS";
		//alert ("Errore: sto analizzando tempi del semaforo");
		else
		{
			$articoloRilevato = $this->SelectArticoloInfrazione126bis();
			//if ($articoloRilevato != $articoloInfranto)
			//alert ("errore articolo non combaciante : $articoloRilevato != $articoloInfranto");
			if ($tipo == "INS")
			{
				$this->InserisciArticolo($articoloRilevato);
				return "OK_ARTICOLO";
			}
			else // per un controllo
			{
				for ($i = 0; $i < count ($this->Coll_Articoli_Infranti); $i++)
				{
					if ($articoloRilevato == $this->Coll_Articoli_Infranti[$i]->Tar_Progr)
					{
						//alert ("ok, articolo combaciante: $articoloRilevato ");
						return "OK_ARTICOLO";
					}
				}
			}
		}
		return "ERRORE_ARTICOLO";
	}
	
	public function ControllaArticoloAltro ($tipo)  //  $tipo = INS per insert/update
	{
		if ($this->Reg_Rilevatore_Elettronico != NULL &&
			$this->Reg_Rilevatore_Elettronico != "0") return "ERRORE_ALTRO";
		//alert ("Errore: sto analizzando tempi del semaforo");
		else
		{
			alert ("Non so quale articolo ci sia");
			$articoloRilevato = $this->SelectArticoloInfrazione7Comma9();
			//if ($articoloRilevato != $articoloInfranto)
			//alert ("errore articolo non combaciante : $articoloRilevato != $articoloInfranto");
			if ($tipo == "INS")
			{
				$this->InserisciArticolo($articoloRilevato);
				return "OK_ARTICOLO";
			}
			else // per un controllo
			{
				for ($i = 0; $i < count ($this->Coll_Articoli_Infranti); $i++)
				{
					if ($articoloRilevato == $this->Coll_Articoli_Infranti[$i]->Tar_Progr)
					{
						//alert ("ok, articolo combaciante: $articoloRilevato ");
						return "OK_ARTICOLO";
					}
				}
			}
		}
		return "ERRORE_ARTICOLO";
	}
	
	
	// deve essere settato
	//  - Reg_Velocita_Effettiva
	//  - Reg_Dati_Infrazione
	public function CalcolaDifferenzaVelocita ()
	{
		$tempDatiInfrazione = explode ("**", $this->Reg_Dati_Infrazione);
		$reg_VelocitaRilevata = $tempDatiInfrazione[0];
		$reg_LimiteVelocita = $tempDatiInfrazione[1];
		$differenzaVelocita = $this->Reg_Velocita_Effettiva - $reg_LimiteVelocita;
		return $differenzaVelocita;
	}
	
	public function SelectArticoloInfrazione142 (&$sanzioneAccessoria)
	{
		$velocitaEffettiva = 0;
		$tempDatiInfrazione = explode ("**", $this->Reg_Dati_Infrazione);
		$reg_VelocitaRilevata = $tempDatiInfrazione[0];
		$reg_LimiteVelocita = $tempDatiInfrazione[1];
		if ($this->Reg_Rilevatore_Elettronico != "")
		{
			$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
			$velocitaEffettiva = $myRilevatore->TolleranzaRilevatore($reg_VelocitaRilevata);
			if ($myRilevatore->Ril_Velocita != "Y")
				alert ("Errore: sto analizzando limite di velocità in select142");
		}
		else alert ("non c'è rilevatore in selectarticolo142");
		$this->Reg_Velocita_Effettiva = $velocitaEffettiva;
		$differenzaVelocita = $velocitaEffettiva - $reg_LimiteVelocita;
		//alert ("$differenzaVelocita  =  $velocitaEffettiva - $this->Reg_Limite_Velocita         $this->Reg_Velocita_Rilevata");
		if ($differenzaVelocita <= 0)
		{
			alert ("Errore differenza velocità inserite: $differenzaVelocita = $velocitaEffettiva - $reg_LimiteVelocita;");
			return NULL;
		}
		/*else if ($differenzaVelocita <= 10)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '7' AND Tar_Lettera = ''";
		else if ($differenzaVelocita > 10 && $differenzaVelocita <= 40)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '8' AND Tar_Lettera = ''";
		else if ($differenzaVelocita > 40 && $differenzaVelocita <= 60)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '9' AND Tar_Lettera != 'bis'";
		else if ($differenzaVelocita > 60)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '9' AND Tar_Lettera = 'bis'";
		
		//alert ($queryDiff);
	
		$query = "SELECT Tar_Progr, Tar_Codice_Sanz_Acc FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno'  AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= $queryDiff;
		$resQuery = mysql_query($query);
		$risp = mysql_fetch_assoc($resQuery);
		$progressivo = $risp['Tar_Progr'];
		if ($progressivo == NULL)
		{
			$query = "SELECT max(Tar_Anno) FROM targhe_estere_articoli";
			$ultimoanno = single_answer_query($query);
			$query = "SELECT Tar_Progr, Tar_Codice_Sanz_Acc FROM targhe_estere_articoli ";
			$query .= "WHERE Tar_Anno = '$ultimoanno' ";//AND Tar_Com = '$comune' ";
			$query .= $queryDiff;
			$resQuery = mysql_query($query);
			$risp = mysql_fetch_assoc($resQuery);
			$progressivo = $risp['Tar_Progr'];
			//echo "$query -> $progressivo";
		}
		$sanzioneAccessoria = $risp['Tar_Codice_Sanz_Acc'];
		return $progressivo;*/
		$sanzioneAccessoria = "";
		$xplodeArt = explode ("**", $this->Reg_Articoli_Infrazione);
		return $xplodeArt[0];
	}
	
	function ScegliArticoloVelocita ($diffVel)
	{
		if ($diffVel <= 10)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '7' AND Tar_Lettera = ''";
		else if ($diffVel > 10 && $diffVel <= 40)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '8' AND Tar_Lettera = ''";
		else if ($diffVel > 40 && $diffVel <= 60)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '9' AND Tar_Lettera != 'bis'";
		else if ($diffVel > 60)
			$queryDiff = "AND Tar_Articolo = '142' AND Tar_Comma = '9' AND Tar_Lettera = 'bis'";
		
		$query = "SELECT Tar_Progr, Tar_Codice_Sanz_Acc FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno'  AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= $queryDiff;
		$resQuery = mysql_query($query);
		$risp = mysql_fetch_assoc($resQuery);
		$progressivo = $risp['Tar_Progr'];
		return $progressivo;
	}
	
	function SelectArticoloInfrazione146Comma3 ()
	{
		if ($this->Reg_Rilevatore_Elettronico != "")
		{
			$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
			if ($myRilevatore->Ril_Velocita != "N")
				alert ("Errore: sto analizzando tempi semaforo in select146");
		}
		else alert ("non c'è rilevatore in selectarticolo146");
		$query = "SELECT Tar_Progr FROM targhe_estere_articoli "; 
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno' AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= "AND Tar_Articolo = '146' AND Tar_Comma = '3'";
		//alert ($query);
		$progressivo = single_answer_query($query);
		//alert ($risp);
		if ($progressivo == NULL)
		{
			$query = "SELECT max(Tar_Anno) FROM targhe_estere_articoli";
			$ultimoanno = single_answer_query($query);
			$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
			$query .= "WHERE Tar_Anno = '$ultimoanno'  AND Tar_Com = '$this->Reg_Comune_Violazione' ";
			$query .= "AND Tar_Articolo = '146' AND Tar_Comma = '3'";
			$progressivo = single_answer_query($query);
			//alert ($query);
			//alert (" 2 " . $risp);
			//echo "$query -> $progressivo";
		}
		return $progressivo;
	}
	
	function SelectArticoloInfrazione7Comma9 ()
	{
		$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno' AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= "AND Tar_Articolo = '7' AND Tar_Comma = '9'";
		//alert ($query);
		$progressivo = single_answer_query($query);
		//alert ($risp);
		if ($progressivo == NULL)
		{
			$query = "SELECT max(Tar_Anno) FROM targhe_estere_articoli";
			$ultimoanno = single_answer_query($query);
			$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
			$query .= "WHERE Tar_Anno = '$ultimoanno'  AND Tar_Com = '$this->Reg_Comune_Violazione' ";
			$query .= "AND Tar_Articolo = '7' AND Tar_Comma = '9'";
			$progressivo = single_answer_query($query);
			//alert ($query);
			//alert (" 2 " . $risp);
			//echo "$query -> $progressivo";
		}
		return $progressivo;
	}
	
	public function SelectArticoloInfrazione7e157e158 ()  //  sosta
	{
		if ($this->Reg_Articoli_Infrazione == "")  //  devo già sapere se è 7 o 158 o 159
			return NULL;
		
		if ($this->Coll_Articoli_Infranti[0]->Tar_Progr != "")
		{
			if ($this->Coll_Articoli_Infranti[0]->Tar_Genere_Infrazione != "SOSTA") return NULL;
			else if ($this->Coll_Articoli_Infranti[0]->Tar_Progr != "") return $this->Coll_Articoli_Infranti[0]->Tar_Progr;
			else return $this->Reg_Articoli_Infrazione;
		}
		else
		{
			return $this->Reg_Articoli_Infrazione;
		}
		
		/*alert ("atenzione " . $this->Reg_Articoli_Infrazione);
		$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno' AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= "AND Tar_Articolo = '7' AND Tar_Comma = '0'";
		$progressivo = single_answer_query($query);
		if ($progressivo == $this->Reg_Articoli_Infrazione) return $progressivo;
		
		$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno' AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= "AND Tar_Articolo = '7' AND Tar_Comma = '14'";
		$progressivo = single_answer_query($query);
		if ($progressivo == $this->Reg_Articoli_Infrazione) return $progressivo;
		
		$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno' AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= "AND Tar_Articolo = '157' AND Tar_Comma = '0'";
		$progressivo = single_answer_query($query);
		if ($progressivo == $this->Reg_Articoli_Infrazione) return $progressivo;
		
		$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno' AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= "AND Tar_Articolo = '158' AND Tar_Comma = '0'";
		$progressivo = single_answer_query($query);
		if ($progressivo == $this->Reg_Articoli_Infrazione) return $progressivo;*/
		
		return NULL;
	}
	
	public function SelectArticoloInfrazione126bis ()  //  126bis
	{
		if ($this->Reg_Articoli_Infrazione == "")  //  devo già sapere se è 7 o 158 o 159
			return NULL;
		
		$query = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$query .= "WHERE Tar_Anno = '$this->Reg_Anno' AND Tar_Com = '$this->Reg_Comune_Violazione' ";
		$query .= "AND Tar_Articolo = '126' AND Tar_Comma = '0' AND Tar_Lettera = 'bis'";
		$progressivo = single_answer_query($query);
		if ($progressivo == $this->Reg_Articoli_Infrazione) return $progressivo;
		
		return NULL;
	}
	
	public function InsertUpdateImmagine ()
	{
		$queryImg = "UPDATE registro_cronologico_cds ";
		$queryImg .= " SET Reg_Immagini = '" . $this->Reg_Immagini . "'";
		$queryImg .= " WHERE Reg_Progr = '" . $this->Reg_Progr . "'";
		$resImg = mysql_query($queryImg);
		return $resImg;
	}
	
	public function CalcoloImportoTotale ()
	{
		$totaleImporto = $this->Reg_Importo_Amministrativo;
		$totaleImporto += $this->Reg_Spese_Notifica_Comune;
		$totaleImporto += $this->Reg_Spese_Ricerca_Comune;
		$totaleImporto += $this->Reg_Spese_Notifica_Sarida;
		$totaleImporto += $this->Reg_Spese_Ricerca_Sarida;
		return $totaleImporto;
	}
	
	public function CalcoloImportoTotaleNonRidotto ()
	{
		$totaleImporto = $this->Coll_Articoli_Infranti[0]->Tar_Tariffa_Sanz_Max;
		$totaleImporto += $this->Reg_Spese_Notifica_Comune;
		$totaleImporto += $this->Reg_Spese_Ricerca_Comune;
		$totaleImporto += $this->Reg_Spese_Notifica_Sarida;
		$totaleImporto += $this->Reg_Spese_Ricerca_Sarida;
		return $totaleImporto;
	}
	
	public function CalcoloImportoEntro5Giorni ()
	{
		$totale70Importo = $this->CalcoloSanzioneEntro5Giorni();
		$totale70Importo += $this->Reg_Spese_Notifica_Comune;
		$totale70Importo += $this->Reg_Spese_Ricerca_Comune;
		$totale70Importo += $this->Reg_Spese_Notifica_Sarida;
		$totale70Importo += $this->Reg_Spese_Ricerca_Sarida;
		return $totale70Importo;
	}
	
	public function CalcoloSanzioneEntro5Giorni ()
	{
		$totale5giorniImporto = $this->Reg_Importo_Amministrativo * 70 / 100;
		return $totale5giorniImporto;
	}
	
	public function CalcoloSoloSpese ()
	{
		$totaleSpese = $this->Reg_Spese_Notifica_Comune;
		$totaleSpese += $this->Reg_Spese_Ricerca_Comune;
		$totaleSpese += $this->Reg_Spese_Notifica_Sarida;
		$totaleSpese += $this->Reg_Spese_Ricerca_Sarida;
		return $totaleSpese;
	}
	
	//  $dataNotifica e $dataPagamento  in formato YYYY-mm-dd
	public function CalcoloDifferenzaGiorni ($dataNotifica, $dataPagamento)
	{
		if (($dataNotifica == "" || $dataNotifica == "0000-00-00") && ($dataPagamento == "" || $dataPagamento == "0000-00-00")) return -1;
		if ($dataNotifica == "" || $dataNotifica == "0000-00-00") return -2;
		if ($dataPagamento == "" || $dataPagamento == "0000-00-00") return -3;
		if ($dataNotifica == $dataPagamento) return -4;
		$diff = strtotime($dataPagamento) - strtotime($dataNotifica);
		//alert ("$diff = strtotime($dataPagamento) - strtotime($dataNotifica)");
		$diffGiorni = $diff / (60 * 60 * 24);
		
		$giornoNotifica = date("l", strtotime($dataNotifica));
		$giornoPagamento = date("l", strtotime($dataPagamento));
		//alert ($giornoPagamento);
		if ($giornoNotifica == "Tuesday" && $giornoPagamento == "Monday") $diffGiorni--;
		//else if 
		
		if ($diffGiorni < 0) return -5;
		return number_format($diffGiorni, 0);
	}
	
	public function CalcoloGiustoImporto ($diffGiorni)
	{
		if ($diffGiorni < 0)
			$importoRichiesto = $this->CalcoloImportoTotaleNonRidotto();
		else if ($diffGiorni <= 5)
			$importoRichiesto = $this->CalcoloImportoEntro5Giorni();
		else if ($diffGiorni > 60)
			$importoRichiesto = $this->CalcoloImportoTotaleNonRidotto();
		else
			$importoRichiesto = $this->CalcoloImportoTotale();
		return $importoRichiesto;
	}
	
	public function DifferenzaRidotto5Giorni ()
	{
		return ($this->CalcoloImportoTotale() - $this->CalcoloImportoEntro5Giorni());
	}
	
	public function SelectTipoVeicolo ($nomeId, $nomeName, $selected)
	{
		$checkedAuto = "";
		$checkedMoto = "";
		$checkedCiclo = "";
		$checkedBus = "";
		$checkedCarro = "";
		$checkedRimorchio = "";
		$checkedFurgone = "";
		$checkedAutocaravan = "";
		$checkedVeicoloAltro = "";
		switch ($selected)  //  auto, moto
		{
			case "": break;
			case "autoveicolo": $checkedAuto = " selected "; break;
			case "motoveicolo": $checkedMoto = " selected "; break;
			case "ciclomotore": $checkedCiclo = " selected "; break;
			case "autobus": $checkedBus = " selected "; break;
			case "autocarro": $checkedCarro = " selected "; break;
			case "rimorchio": $checkedRimorchio = " selected "; break;
			case "furgone": $checkedFurgone = " selected "; break;
			case "autocaravan": $checkedAutocaravan = " selected "; break;
			case "altro": $checkedVeicoloAltro = " selected "; break;
			default: alert ("tipo veicolo non gestito : " . $selected);  //  auto, moto
				break;
		}
		echo <<< SELECTTIPO
			<select id="$nomeId" name="$nomeName">
				<option value=""></option>
				<option $checkedAuto value="autoveicolo">autoveicolo</option>
				<option $checkedMoto value="motoveicolo">motoveicolo</option>
				<option $checkedCiclo value="ciclomotore">ciclomotore</option>
				<option $checkedBus value="autobus">autobus</option>
				<option $checkedCarro value="autocarro">autocarro</option>
				<option $checkedRimorchio value="rimorchio">rimorchio</option>
				<option $checkedFurgone value="furgone">furgone</option>
				<option $checkedAutocaravan value="autocaravan">autocaravan</option>
				<option $checkedVeicoloAltro value="altro">altro</option>
			</select>
		
SELECTTIPO;
	}
	
	
	// L'obiettivo di DeterminaArticoloInfranto
	// è di avere SETTATI all'uscita i campi
	//  - Reg_Articoli_Infrazione
	//  ( - Coll_Articoli_Infranti[0] )
	//  - Reg_Importo_Amministrativo
	//  - Reg_Importo_Sanzione_Massima
	//  - Reg_Anno (se già non settato precedentemente)
	
	// per funzionare nella classe devono già essere settati:
	//    -  Reg_Genere_Infrazione
	//    -  Reg_Immagini (se c'è)
	//    -  Reg_Stato_Verbale
	//    -  Reg_Dati_Infrazione (se ci sono)
	//    -  Reg_Rilevatore_Elettronico (se c'è)
	public function DeterminaArticoloInfranto ()
	{
		$tipoReg = $this->Reg_Genere_Infrazione;
		if ($this->Reg_Immagini != "" && $tipoReg != "")
		{
			//alert ($this->Reg_Immagini);
			$stringaFoto = $this->Reg_Immagini;
			$esplodoFoto = explode("**", $stringaFoto);
			$tipoReg = $this->Reg_Genere_Infrazione;
				
			switch ($tipoReg)
			{
				case "AUTOVELOX": if (count($esplodoFoto) != 1) $tipoReg = ""; break;
				case "SEMAFORO": if (count($esplodoFoto) != 2) $tipoReg = ""; break;
				case "ZTL": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				case "SOSTA": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				case "126BIS": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				case "ALTRO": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				default: $tipoReg = ""; break;
			}
		}
		if ($this->Reg_Immagini != "" && $this->Reg_Anno == "")
		{
			$stringaFoto = $this->Reg_Immagini;
			$esplodoFoto = explode("**", $stringaFoto);
			$cercoAnno = explode ("_", $esplodoFoto[0]);
			//alert ($esplodoFoto[0]);
			$anno = "20" . substr($cercoAnno[1], 0, 2);
			$this->Reg_Anno = $anno;
		}
		else if ($tipoReg == "AUTOVELOX") {}
		else if ($tipoReg == "SEMAFORO") {}
		else if ($tipoReg == "ZTL") {}
		else if ($tipoReg == "SOSTA") {}
		else if ($tipoReg == "126BIS") {}
		else if ($tipoReg == "ALTRO") {}
		else $tipoReg = "";
		
		$explodeArticoli = explode ("**", $this->Reg_Articoli_Infrazione);
		if (count($explodeArticoli) == 0)
		{
			if (!isset($this->Coll_Articoli_Infranti[0]))
			{
				$this->Coll_Articoli_Infranti[0] = new targhe_estere_articoli(null);
			}
		}
		/*else if (count($explodeArticoli) == 1)
		{
			if (!isset($this->Coll_Articoli_Infranti[0]))
			{
				$this->Coll_Articoli_Infranti[0] = new targhe_estere_articoli($explodeArticoli[0]);
			}
		}*/
		else if (count($explodeArticoli) > 1)
		{
			for ($ppp = 0; $ppp < count($explodeArticoli); $ppp++)
			{
				//if (!isset($this->Coll_Articoli_Infranti[0]))
				{
					$this->Coll_Articoli_Infranti[$ppp] = new targhe_estere_articoli($explodeArticoli[$ppp]);
				}
			}
		}
		
		$this->Reg_Dati_Infrazione = str_replace(",", ".", $this->Reg_Dati_Infrazione); 
		$tempDatiInfrazione = explode ("**", $this->Reg_Dati_Infrazione);
		
		$rispDet = "";
		
		if ($tipoReg == "AUTOVELOX")
		{
			$reg_VelocitaRilevata = $tempDatiInfrazione[0];
			$reg_LimiteVelocita = $tempDatiInfrazione[1];
			//alert ("vel " . $this->Reg_Velocita_Rilevata);
			if ($reg_VelocitaRilevata != NULL)  //  devo calcolare l'articolo
				$rispDet = $this->ControllaArticoloLimitiRilevatore("INS");
			else  //  vengo dall'importazione del file: non ho ancora i dati dell'infrazione!
				$rispDet = "OK_ARTICOLO";
		}
		else if ($tipoReg == "SEMAFORO")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			if ($this->Reg_Dati_Infrazione != NULL)  //  devo calcolare l'articolo
				$rispDet = $this->ControllaArticoloTempiRilevatore("INS");
			else  //  vengo dall'importazione del file: non ho ancora i dati dell'infrazione!
			{
				$this->Reg_Dati_Infrazione = "0**0";
				$rispDet = "OK_ARTICOLO";
			}
		}
		else if ($tipoReg == "ZTL")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			//alert ($this->Reg_Rilevatore_Elettronico);
			$rispDet = $this->ControllaArticoloZtl("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			//$rispDet = "OK_ARTICOLO";
		}
		else if ($tipoReg == "SOSTA")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			//alert ($this->Reg_Rilevatore_Elettronico);
			$rispDet = $this->ControllaArticoloSosta("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			//$rispDet = "OK_ARTICOLO";
		}
		else if ($tipoReg == "126BIS")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			//alert ($this->Reg_Rilevatore_Elettronico);
			$rispDet = $this->ControllaArticolo126Bis("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			//$rispDet = "OK_ARTICOLO";
		}
		else if ($tipoReg == "ALTRO")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			$rispDet = $this->ControllaArticoloAltro("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			$rispDet = "OK_ARTICOLO";
		}
		else
		{
			//alert ($tipoReg . "  e  " . $this->Reg_Immagini . " e " . $this->Reg_Progr);
			$rispDet = "SCONOSCIUTO";
		}
		//alert ($rispDet . " eeeee  " . $this->Reg_Articoli_Infrazione . " fine");
		//return;
		//echo "cippa$rispDet";
		
		if ($rispDet == "SCONOSCIUTO")
		{
			if ($this->Reg_Stato_Verbale == "WEBIMPORTATO")
				$rispDet = "OK_ARTICOLO"; //  nell'importazione via file olandese non ho articolo!
		}
		
		return $rispDet;
	}
	
	public function NewDeterminaArticolo ()
	{
		$tipoReg = $this->Reg_Genere_Infrazione;
		if ($this->Reg_Immagini != "" && $tipoReg != "")
		{
			//alert ($this->Reg_Immagini);
			$stringaFoto = $this->Reg_Immagini;
			$esplodoFoto = explode("**", $stringaFoto);
			$tipoReg = $this->Reg_Genere_Infrazione;
				
			switch ($tipoReg)
			{
				case "AUTOVELOX": if (count($esplodoFoto) != 1) $tipoReg = ""; break;
				case "SEMAFORO": if (count($esplodoFoto) != 2) $tipoReg = ""; break;
				case "ZTL": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				case "SOSTA": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				case "126BIS": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				case "ALTRO": if (count($esplodoFoto) != 0) $tipoReg = ""; break;
				default: $tipoReg = ""; break;
			}
		}
		if ($this->Reg_Immagini != "" && $this->Reg_Anno == "")
		{
			$stringaFoto = $this->Reg_Immagini;
			$esplodoFoto = explode("**", $stringaFoto);
			$cercoAnno = explode ("_", $esplodoFoto[0]);
			//alert ($esplodoFoto[0]);
			$anno = "20" . substr($cercoAnno[1], 0, 2);
			$this->Reg_Anno = $anno;
		}
		else if ($tipoReg == "AUTOVELOX") {}
		else if ($tipoReg == "SEMAFORO") {}
		else if ($tipoReg == "ZTL") {}
		else if ($tipoReg == "SOSTA") {}
		else if ($tipoReg == "126BIS") {}
		else if ($tipoReg == "ALTRO") {}
		else $tipoReg = "";

		$sommaImporto = 0;
		$sommaMaxImporto = 0;
		$stringaImportiSeparati = "";
		$explodeArticoli = explode ("**", $this->Reg_Articoli_Infrazione);
		if (count($explodeArticoli) == 0)
		{
			if (!isset($this->Coll_Articoli_Infranti[0]))
			{
				$this->Coll_Articoli_Infranti[0] = new targhe_estere_articoli(null);
			}
		}
		else if (count($explodeArticoli) > 1)
		{
			for ($ppp = 0; $ppp < count($explodeArticoli); $ppp++)
			{
				//if (!isset($this->Coll_Articoli_Infranti[0]))
				{
					$tempArt = new targhe_estere_articoli($explodeArticoli[$ppp]);
					$tempArt->DiurnoNotturno($this->Reg_Ora_Avviso);
					$sommaImporto += $tempArt->Tar_Tariffa_Sanz;
					$sommaMaxImporto += $tempArt->Tar_Tariffa_Sanz_Max;
					$stringaImportiSeparati .= number_format ($tempArt->Tar_Tariffa_Sanz, 2, ",", "") . "**";
					
					$this->Coll_Articoli_Infranti[$ppp] = $tempArt;
				}
			}
		}
		$this->Reg_Importo_Amministrativo = $sommaImporto;
		$this->Reg_Importo_Sanzione_Massima = $sommaMaxImporto;
		if ($stringaImportiSeparati != "") $stringaImportiSeparati = substr($stringaImportiSeparati, 0, -2);  // tolgo gli ultimi asterischi
		//$this->Reg_Importi_Separati = $stringaImportiSeparati;
		
		$this->Reg_Dati_Infrazione = str_replace(",", ".", $this->Reg_Dati_Infrazione); 
		$tempDatiInfrazione = explode ("**", $this->Reg_Dati_Infrazione);
		
		$rispDet = "";
		
		if ($tipoReg == "AUTOVELOX")
		{
			$reg_VelocitaRilevata = $tempDatiInfrazione[0];
			$reg_LimiteVelocita = $tempDatiInfrazione[1];
			//alert ("vel " . $this->Reg_Velocita_Rilevata);
			if ($reg_VelocitaRilevata != NULL)  //  devo calcolare l'articolo
			{
				$myRilevatore = new targhe_estere_rilevatori_velocita($this->Reg_Rilevatore_Elettronico);
				if ($myRilevatore->Ril_Progr == NULL) $rispDet = "ERRORE_VELOCITA";
				else if ($myRilevatore->Ril_Velocita != "Y") $rispDet = "ERRORE_VELOCITA";
					//alert ("Errore: sto analizzando limite di velocità");
				else
				{
					$articoloRilevato = $this->SelectArticoloInfrazione142($sanzAcc);
					$rispDet = "OK_ARTICOLO";
				}
			}
			else $rispDet = "OK_ARTICOLO"; //  vengo dall'importazione del file: non ho ancora i dati dell'infrazione!
		}
		else if ($tipoReg == "SEMAFORO")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			if ($this->Reg_Dati_Infrazione != NULL)  //  devo calcolare l'articolo
				$rispDet = $this->ControllaArticoloTempiRilevatore("INS");
			else  //  vengo dall'importazione del file: non ho ancora i dati dell'infrazione!
			{
				$this->Reg_Dati_Infrazione = "0**0";
				$rispDet = "OK_ARTICOLO";
			}
		}
		else if ($tipoReg == "ZTL")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			//alert ($this->Reg_Rilevatore_Elettronico);
			$rispDet = $this->ControllaArticoloZtl("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			//$rispDet = "OK_ARTICOLO";
		}
		else if ($tipoReg == "SOSTA")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			//alert ($this->Reg_Rilevatore_Elettronico);
			//$rispDet = $this->ControllaArticoloSosta("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			$rispDet = "OK_ARTICOLO";
		}
		else if ($tipoReg == "126BIS")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			//alert ($this->Reg_Rilevatore_Elettronico);
			//$rispDet = $this->ControllaArticolo126Bis("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			$rispDet = "OK_ARTICOLO";
		}
		else if ($tipoReg == "ALTRO")
		{
			//alert ("sem " . $this->Reg_Tempo_Semaforo);
			$this->Reg_Dati_Infrazione = "";
			//$rispDet = $this->ControllaArticoloAltro("INS");
			//$this->Reg_Tempo_Semaforo = "0**0";
			$rispDet = "OK_ARTICOLO";
		}
		else
		{
			//alert ($tipoReg . "  e  " . $this->Reg_Immagini . " e " . $this->Reg_Progr);
			$rispDet = "SCONOSCIUTO";
		}
		//alert ($rispDet . " eeeee  " . $this->Reg_Articoli_Infrazione . " fine");
		//return;
		//echo "cippa$rispDet";
		
		if ($rispDet == "SCONOSCIUTO")
		{
			if ($this->Reg_Stato_Verbale == "WEBIMPORTATO")
				$rispDet = "OK_ARTICOLO"; //  nell'importazione via file olandese non ho articolo!
		}
		
		return $rispDet;
	}
	
	public function InsertUpdateRegistroCronologico ($forzato = NULL)  //  forzato è INSERT o UPDATE
	{
		//return "articolo numero $this->Reg_Articoli_Infrazione";
		$this->CorreggiDati();
		
		/*$risposta = $this->DeterminaArticoloInfranto ();
		
		if ($risposta == "ERRORE_VELOCITA") return "INSERT_ERRORE_VELOCITA";
		else if ($risposta == "ERRORE_ARTICOLO") return "INSERT_ARTICOLI_DIVERSI";
		else if ($risposta != "OK_ARTICOLO") return "ERRORE_SCONOSCIUTO";*/
		
		$progressivo = null;
		
		if ($forzato == NULL)
		{
			//$progressivo = $this->ProgressivoGiaPresente();  // true o false
			if ($this->Reg_Progr == NULL)
			{
				$progressivo = $this->ViolazioneGiaPresente();  //  progr o null
			}
			else $progressivo = $this->Reg_Progr;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->Reg_Progr;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO REGISTRO, errore PROGR"); return; }
		}
		//alert ($progressivo);
		//return "CICCIO";
		//return "articolo numero $this->Reg_Articoli_Infrazione";
		
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "Reg_Progr" &&
				$key != "Coll_Ente_Richiesta" &&
				$key != "Coll_Rilevatore_Elettronico" &&
				$key != "Coll_Articoli_Infranti" &&
				$key != "Coll_Accertatori" &&
				$key != "Coll_Motivo_Mancata_Contestazione" &&
				$key != "Reg_Data_Registrazione" &&
				$key != "Reg_Ora_Registrazione" &&
				$key != "Reg_Operatore")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		$fields[] = "Reg_Data_Registrazione";
		$values[] = date("Y-m-d");
		$fields[] = "Reg_Ora_Registrazione";
		$values[] = date("H:i:s");
		$fields[] = "Reg_Operatore";
		$values[] = $_SESSION['username'];
		
		if ($progressivo == NULL)  // non è presente
		{
			$verif = $this->insert_richieste_locale($fields, $values);
			if ($verif == true)
				return "INSERT_RICHIESTA_OK";
			else
				return "INSERT_RICHIESTA_ERRORE";
		}
		else  //  è già presente
		{
			$verif = $this->update_richieste_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_RICHIESTA_OK";
			else
				return "UPDATE_RICHIESTA_ERRORE";
		}
	}
	
	public function insert_richieste_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO registro_cronologico_cds (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		//$_SESSION['CC_User'] != "***+"
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
	
	public function update_richieste_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldReg = new registro_cronologico_cds($key);
		
		$newImmagini = "";
		$newDocumenti = "";
		$newData = "";
		$newOra = "";
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			/*switch ($fields_to_update[$i])
			{
				case "Reg_Immagini": $newImmagini = $values_to_update[$i]; break;
				case "Reg_Documentazione": $newDocumenti = $values_to_update[$i]; break;
				case "Reg_Data_Avviso": $newData = $values_to_update[$i]; break;
				case "Reg_Ora_Avviso": $newOra = $values_to_update[$i]; break;
			}*/
			if ($myOldReg->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		
		/*if (($myOldReg->Reg_Data_Avviso != $newData) ||
			($myOldReg->Reg_Ora_Avviso != $newOra))
		{
			$this->SpostaTutteFotoGiaLavorate($newImmagini, 
					$myOldReg->Reg_Data_Avviso, $myOldReg->Reg_Ora_Avviso,
					$newData, $newOra);
		}
		if ($myOldReg->Reg_Data_Avviso != $newData)
		{
			$this->SpostaTuttiDocumentiGiaLavorati($newDocumenti,
					$myOldReg->Reg_Data_Avviso,
					$newData);
		}*/
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", " 
	
		$query = "UPDATE registro_cronologico_cds SET $clause WHERE Reg_Progr = '" . $key . "'";
	
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
	
	public function InsertUpdateProvaRegistroCronologico ($forzato = NULL)  //  forzato è INSERT o UPDATE
	{
		//return "articolo numero $this->Reg_Articoli_Infrazione";
		$this->CorreggiDati();
		
		/*$risposta = $this->DeterminaArticoloInfranto ();
		
		if ($risposta == "ERRORE_VELOCITA") return "INSERT_ERRORE_VELOCITA";
		else if ($risposta == "ERRORE_ARTICOLO") return "INSERT_ARTICOLI_DIVERSI";
		else if ($risposta != "OK_ARTICOLO") return "ERRORE_SCONOSCIUTO";*/
		
		$progressivo = null;
		
		if ($forzato == NULL)
		{
			//$progressivo = $this->ProgressivoGiaPresente();  // true o false
			if ($this->Reg_Progr == NULL)
			{
				$progressivo = $this->ViolazioneGiaPresente();  //  progr o null
			}
			else $progressivo = $this->Reg_Progr;
		}
		else if ($forzato == "UPDATE")
		{
			$progressivo = $this->Reg_Progr;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO REGISTRO, errore PROGR"); return; }
		}
		//alert ($progressivo);
		//return "CICCIO";
		//return "articolo numero $this->Reg_Articoli_Infrazione";
		
		foreach ($this as $key => $value)
		{
			if (isset($key) && 
				$key != "Reg_Progr" &&
				$key != "Coll_Ente_Richiesta" &&
				$key != "Coll_Rilevatore_Elettronico" &&
				$key != "Coll_Articoli_Infranti" &&
				$key != "Coll_Accertatori" &&
				$key != "Coll_Motivo_Mancata_Contestazione" &&
				$key != "Reg_Data_Registrazione" &&
				$key != "Reg_Ora_Registrazione" &&
				$key != "Reg_Operatore")
			{
				$fields[] = $key;
				$values[] = addslashes($value);
			}
		}
		$fields[] = "Reg_Data_Registrazione";
		$values[] = date("Y-m-d");
		$fields[] = "Reg_Ora_Registrazione";
		$values[] = date("H:i:s");
		$fields[] = "Reg_Operatore";
		$values[] = $_SESSION['username'];
		
		if ($progressivo == NULL)  // non è presente
		{
			$verif = $this->insert_richieste_prova_locale($fields, $values);
			if ($verif == true)
				return "INSERT_RICHIESTA_OK";
			else
				return "INSERT_RICHIESTA_ERRORE";
		}
		else  //  è già presente
		{
			$verif = $this->update_richieste_prova_locale($progressivo, $fields, $values);
			if ($verif == true)
				return "UPDATE_RICHIESTA_OK";
			else
				return "UPDATE_RICHIESTA_ERRORE";
		}
	}
	
	public function insert_richieste_prova_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		echo "<br><br>INSERT registro_cronologico_cds";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO registro_cronologico_cds (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
			echo "<br>" . $fields_to_insert[$i] . "='" .$values_to_insert[$i]. "' ";
		}
		$query .= $clause . ")";
		
		//echo "<br>" . $query;
		return true;
	}
	
	public function update_richieste_prova_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldReg = new registro_cronologico_cds($key);
		
		$newImmagini = "";
		$newDocumenti = "";
		$newData = "";
		$newOra = "";
	
		$clause = "";
		echo "<br><br>UPDATE registro_cronologico_cds : WHERE Reg_Progr = '" . $key . "'";
		for ($i = 0; $i < $dim1; $i++)
		{
			/*switch ($fields_to_update[$i])
			{
				case "Reg_Immagini": $newImmagini = $values_to_update[$i]; break;
				case "Reg_Documentazione": $newDocumenti = $values_to_update[$i]; break;
				case "Reg_Data_Avviso": $newData = $values_to_update[$i]; break;
				case "Reg_Ora_Avviso": $newOra = $values_to_update[$i]; break;
			}*/
			if ($myOldReg->$fields_to_update[$i] != $values_to_update[$i])
			{
				if (/*$values_to_update[$i] != NULL*/ isset($values_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
					echo "<br>" . $fields_to_update[$i] . "='" .$values_to_update[$i] . "'   (" . $myOldReg->$fields_to_update[$i] . ")";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		
		/*if (($myOldReg->Reg_Data_Avviso != $newData) ||
			($myOldReg->Reg_Ora_Avviso != $newOra))
		{
			$this->SpostaTutteFotoGiaLavorate($newImmagini, 
					$myOldReg->Reg_Data_Avviso, $myOldReg->Reg_Ora_Avviso,
					$newData, $newOra);
		}
		if ($myOldReg->Reg_Data_Avviso != $newData)
		{
			$this->SpostaTuttiDocumentiGiaLavorati($newDocumenti,
					$myOldReg->Reg_Data_Avviso,
					$newData);
		}*/
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", " 
	
		$query = "UPDATE registro_cronologico_cds SET $clause WHERE Reg_Progr = '" . $key . "'";
	
		//echo "<br>" . $query;
		return true;
	}
	
	function QueryRichiesteDaStampare ($comune)
	{
		$queryRichiesteDaStampare = "SELECT Reg_Progr ";
		$queryRichiesteDaStampare .= "FROM registro_cronologico_cds ";
		$queryRichiesteDaStampare .= "WHERE Reg_Comune_Violazione = '$comune' AND ";
		$queryRichiesteDaStampare .= "Reg_Data_Stampa_Richiesta = '0000-00-00' AND ";
		$queryRichiesteDaStampare .= "(Reg_Stato_Verbale = 'MANUALE' OR Reg_Stato_Verbale = 'AUTOMATICO') AND ";
		$queryRichiesteDaStampare .= "Reg_Ente_Per_Richiesta != 1 AND ";
		$queryRichiesteDaStampare .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
		$queryRichiesteDaStampare .= "Reg_Data_Annullamento = '0000-00-00'  ";
		return $queryRichiesteDaStampare;
	}
	
	function ListaPerStampeRichiesteDati ($arrayPost)
	{
		/*foreach ($arrayPost as $key => $value)
		{
			echo "<br>$key -> $value";
		}*/
		
		$c = NULL;
		$a = NULL;
		
		$da_n_elenco = NULL;
		$a_n_elenco = NULL;
		$selectstato = NULL;
		$selectcomune = NULL;
		$da_avviso = NULL;
		$da_insert = NULL;
		$da_stampa = NULL;
		$a_avviso = NULL;
		$a_insert = NULL;
		$a_stampa = NULL;
		$giastampate = NULL;
		
		$svizzere = NULL;
		
		$stampa_select = NULL;  //  provvisoria o definitiva
		
		if (isset($arrayPost['c'])) $c = $arrayPost['c'];
		if (isset($arrayPost['a'])) $a = $arrayPost['a'];
		
		if (isset($arrayPost['da_n_elenco'])) $da_n_elenco = $arrayPost['da_n_elenco'];
		if (isset($arrayPost['a_n_elenco'])) $a_n_elenco = $arrayPost['a_n_elenco'];
		if (isset($arrayPost['selectstato'])) $selectstato = $arrayPost['selectstato'];
		if (isset($arrayPost['selectcomune'])) $selectcomune = $arrayPost['selectcomune'];
		if (isset($arrayPost['da_avviso'])) $da_avviso = $arrayPost['da_avviso'];
		if (isset($arrayPost['da_insert'])) $da_insert = $arrayPost['da_insert'];
		if (isset($arrayPost['da_stampa'])) $da_stampa = $arrayPost['da_stampa'];
		if (isset($arrayPost['a_avviso'])) $a_avviso = $arrayPost['a_avviso'];
		if (isset($arrayPost['a_insert'])) $a_insert = $arrayPost['a_insert'];
		if (isset($arrayPost['a_stampa'])) $a_stampa = $arrayPost['a_stampa'];
		if (isset($arrayPost['giastampate'])) $giastampate = $arrayPost['giastampate'];
		
		if (isset($arrayPost['svizzere'])) $svizzere = $arrayPost['svizzere'];
		
		if (isset($arrayPost['stampa_select'])) $stampa_select = $arrayPost['stampa_select'];  //  provvisoria o definitiva
		
		$aggiuntaElenco = "";
		if ($da_n_elenco != NULL)
			$aggiuntaElenco .= " AND Reg_Progr >= " . $da_n_elenco . " ";
		if ($a_n_elenco != NULL)
			$aggiuntaElenco .= " AND Reg_Progr <= " . $a_n_elenco . " ";
		
		$aggiuntaDataAvviso = "";
		if ($da_avviso != NULL)
			$aggiuntaDataAvviso .= " AND Reg_Data_Avviso >= '" . to_mysql_date($da_avviso) . "' ";
		if ($a_avviso != NULL)
			$aggiuntaDataAvviso .= " AND Reg_Data_Avviso <= '" . to_mysql_date($a_avviso) . "' ";
		
		$aggiuntaDataInserimento = "";
		if ($da_insert != NULL)
			$aggiuntaDataInserimento .= " AND Reg_Data_Registrazione >= '" . to_mysql_date($da_insert) . "' ";
		if ($a_insert != NULL)
			$aggiuntaDataInserimento .= " AND Reg_Data_Registrazione <= '" . to_mysql_date($a_insert) . "' ";
		
		$aggiuntaDataStampa = "";
		if ($da_stampa != NULL)  // equivale a GIA STAMPATO
		{
			$aggiuntaDataStampa .= " AND Reg_Data_Stampa_Richiesta >= '" . to_mysql_date($da_stampa) . "' ";
			$aggiuntaDataStampa .= " AND Reg_Data_Stampa_Richiesta != '0000-00-00' ";
		}
		if ($a_stampa != NULL)  // equivale a GIA STAMPATO
		{
			$aggiuntaDataStampa .= " AND Reg_Data_Stampa_Richiesta <= '" . to_mysql_date($a_stampa) . "' ";
			$aggiuntaDataStampa .= " AND Reg_Data_Stampa_Richiesta != '0000-00-00' ";
		}
		
		$aggiuntaStatoVerb = "";
		if ($giastampate != NULL)  //  GIA STAMPATO
		{
			$scrittaStampa = "GIA' STAMPATE";
			$aggiuntaDataStampa .= " AND Reg_Data_Stampa_Richiesta != '0000-00-00' ";
		}
		else 
		{
			$scrittaStampa = "DA STAMPARE";
			$aggiuntaDataStampa .= " AND Reg_Data_Stampa_Richiesta = '0000-00-00' ";
			$aggiuntaStatoVerb .= " AND (Reg_Stato_Verbale = 'MANUALE' ";
			$aggiuntaStatoVerb .= " OR Reg_Stato_Verbale = 'AUTOMATICO') AND ";
			$aggiuntaStatoVerb .= " Reg_Ente_Per_Richiesta != 1 ";
		}
		
		$aggiuntaFrom = "";
		$aggiuntaUtente = "";
		$aggiuntaNonAnnullate = "";
		$aggiuntaSvizzere = "";
		if ($svizzere != "")
		{
			$arrayNaz = array();
			$objZona = new targhe_estere_zone_competenza(null);
			$arrayNaz = $objZona->ArrayQueryPerNazione("Svizzera", "TI");
			for ($ppp = 0; $ppp < count($arrayNaz); $ppp++)
			{
				$aggiuntaSvizzere .= " Reg_Ente_Per_Richiesta = " . $arrayNaz[$ppp] . " OR ";
			}
			if ($aggiuntaSvizzere != "")  //  se ce n'è almeno uno!
			{
				$aggiuntaSvizzere = substr($aggiuntaSvizzere, 0, -3);  //  tolgo l'ultimo OR
				$aggiuntaSvizzere = " AND ( " . $aggiuntaSvizzere . " ) ";
			}
			//echo "<br>" . $aggiuntaSvizzere . "<br>";
			
			// STAMPO SOLO QUELLE GIA STAMPATE DEFINITIVAMENTE PER TICINO
			// COSI LE GIRO A CHIASSO
			$aggiuntaDataStampa = " AND Reg_Data_Stampa_Richiesta != '0000-00-00' ";
			
			// devo trovare le richieste che non hanno un utente già associato 
			$aggiuntaFrom = " LEFT JOIN targhe_estere_notifiche on Verbale_ID = Reg_Progr ";
			$aggiuntaUtente = " AND (Utente_ID is null OR Utente_ID = 0) ";
			
			// escludo quelle ANNULLATE DALL'OPERATORE
			// perchè una volta spedite a CHIASSO, se non ritornano, vengono bloccate
			$aggiuntaNonAnnullate = " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND Reg_Data_Annullamento = '0000-00-00'";
		}
		
		/*if ($aggiuntaDataStampa == "")  // equivale a DA STAMPARE
			$aggiuntaDataStampa = " AND Reg_Data_Stampa_Richiesta = '0000-00-00' ";*/
		
		$aggiuntaNazione = "";
		if ($selectstato != NULL)  //  scelta sulla Nazione
		{
			$queryNazione = "SELECT Tar_Progr FROM targhe_estere_zone_competenza";
			$queryNazione .= " WHERE Tar_Nazione_Num = $selectstato ";
			$resNazione = esegui_query($queryNazione);
			$aggiuntaNazione = " AND ( ";
			while ($rigaNazione = risultati_query($resNazione))
			{
				$aggiuntaNazione .= "Reg_Ente_Per_Richiesta = " . $rigaNazione['Tar_Progr'] . " OR ";
			}
			if ($aggiuntaNazione != " AND ( ")  //  se ce n'è almeno uno!
			{
				$aggiuntaNazione = substr($aggiuntaNazione, 0, -3);  //  tolgo l'ultimo OR
				$aggiuntaNazione .= " ) ";
			}
			else $aggiuntaNazione = "";
		}
		
		$aggiuntaComune = "";
		if ($selectcomune != NULL)  //  limitato per comune
			$aggiuntaComune = " AND Reg_Comune_Violazione = '" . $selectcomune . "' ";
		
		$listaRichieste = array();
		
		//for ($k = 0; $k < 30; $k++)
		{
			$query = "SELECT DISTINCT Reg_Comune_Violazione ";
			$query .= " FROM registro_cronologico_cds ";
			$query .= " WHERE 1 ";
			$query .= $aggiuntaComune;
			$query .= " ORDER BY Reg_Comune_Violazione ASC";
			$result = mysql_query($query);
			
			while ($rigacomuni = mysql_fetch_assoc($result))
			{
				$ComuneAttuale = new ente_gestito($rigacomuni['Reg_Comune_Violazione']);
				
				$query = "SELECT Reg_Progr ";
				$query .= "FROM registro_cronologico_cds ";
				$query .= $aggiuntaFrom;
				$query .= "WHERE Reg_Comune_Violazione = '$ComuneAttuale->CC' ";
				//$query .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
				//$query .= "Reg_Data_Annullamento = '0000-00-00'  ";
				//$query .= " AND Reg_Anno = " . $a;
				$query .= $aggiuntaElenco;
				$query .= $aggiuntaDataAvviso;
				$query .= $aggiuntaDataInserimento;
				$query .= $aggiuntaDataStampa;
				$query .= $aggiuntaComune;
				$query .= $aggiuntaStatoVerb;
				$query .= $aggiuntaNazione;
				$query .= $aggiuntaSvizzere;
				$query .= $aggiuntaUtente;
				$query .= $aggiuntaNonAnnullate;
				//$query .= " AND Reg_Stato_erbale = 'INDIRIZATO' ";
				$query .= " GROUP BY Reg_Targa_Veicolo ";
				$query .= " ORDER BY Reg_Ente_Per_Richiesta ASC";
				$resultRichieste = mysql_query($query);
				$OldEnteRichiesta = "0";
				
				if ($_SESSION['CC_User'] == "***+")
				{
					echo "<br><br>" . $query . " --> " . numero_risposte_query($resultRichieste);
				}
				
				$tempquery = "";
				
				while ($rigaRichieste = mysql_fetch_assoc($resultRichieste))
				{
					//for ($k = 0; $k < 15; $k++)
					//$preTemp = new registro_cronologico_cds($rigaRichieste['Reg_Progr']);
					//if ($preTemp->Coll_Ente_Richiesta->Tar_Tipo_Richiesta_AutoManual == "manu")
					{
						$listaRichieste[] = $rigaRichieste['Reg_Progr'];
						$tempquery = $query;
					}
				}
			}
		}
		
		return $listaRichieste;
	}
}

/*

CREATE TABLE `targhe_estere_zone_competenza` (
`Tar_Progr` int(11) unsigned NOT NULL auto_increment,
`Tar_Nazione_Num` int(11) NOT NULL default '0',
`Tar_Nazione_Nome` varchar(50) NOT NULL default '',
`Tar_Regione` varchar(100) NOT NULL default '',
`Tar_Citta` varchar(100) NOT NULL default '',
`Tar_Indirizzo_Prima_Riga` varchar(200) NOT NULL default '',
`Tar_Indirizzo_Seconda_Riga` varchar(200) NOT NULL default '',
`Tar_Indirizzo_Terza_Riga` varchar(200) NOT NULL default '',
`Tar_Indirizzo_Quarta_Riga` varchar(200) NOT NULL default '',
`Tar_Indirizzo_Quinta_Riga` varchar(200) NOT NULL default '',
`Tar_Linguaggio` int(11) unsigned NOT NULL default '1',
`Tar_Tipo_Richiesta_AutoManual` enum('auto','manu') NOT NULL default 'manu',
`Tar_Telefono_Fax` varchar(200) NOT NULL default '',
`Tar_Email` varchar(200) NOT NULL default '',
PRIMARY KEY  (`Tar_Progr`),
CONSTRAINT `Lingua` FOREIGN KEY (`Tar_Linguaggio`) REFERENCES `targhe_estere_lingue` (`Lin_Progr`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1

*/

class targhe_estere_zone_competenza
{
	public $Tar_Progr = NULL;
	public $Tar_Nazione_Num = NULL;
	public $Tar_Nazione_Nome = NULL;
	public $Tar_Regione = NULL;
	public $Tar_Citta = NULL;
	public $Tar_Indirizzo_Prima_Riga = NULL;
	public $Tar_Indirizzo_Seconda_Riga = NULL;
	public $Tar_Indirizzo_Terza_Riga = NULL;
	public $Tar_Indirizzo_Quarta_Riga = NULL;
	public $Tar_Indirizzo_Quinta_Riga = NULL;
	public $Tar_Linguaggio = NULL;
	public $Tar_Tipo_Richiesta_AutoManual = NULL;
	public $Tar_Telefono_Fax = NULL;
	public $Tar_Email = NULL;
	
	public $Coll_Lingua = NULL;
	
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
		
		$query = "SELECT * FROM targhe_estere_zone_competenza WHERE Tar_Progr = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaZone = mysql_fetch_assoc($result);
		
		if (mysql_num_rows($result) != 0)
		{
			foreach ($rigaZone as $key => $value)
			{
				$this->$key = /*utf8_decode*/($value);
			}
			
			$this->Coll_Lingua = new targhe_estere_lista_lingue($this->Tar_Linguaggio);
		}
		else $this->Coll_Lingua = new targhe_estere_lista_lingue(null);
	}
	
	function CercaZonaNazione ($nazione)  //  prendo la prima (se ce ne sono di più)
	{
		$nazione = strtolower($nazione);
		$nazioneMaiuscolo = strtoupper($nazione);
		$nazionePrimaMaiuscola = ucfirst($nazione);
		$queryNazione = "SELECT Tar_Progr FROM targhe_estere_zone_competenza ";
		$queryNazione .= "WHERE Tar_Nazione_Nome LIKE '%" . $nazione . "%' ";
		$queryNazione .= "OR Tar_Nazione_Nome LIKE '%" . $nazioneMaiuscolo . "%' ";
		$queryNazione .= "OR Tar_Nazione_Nome LIKE '%" . $nazionePrimaMaiuscola . "%' ";
		$resNazione = mysql_query($queryNazione);
		$rigaNazione = mysql_fetch_assoc($resNazione);
		//echo "<br>$queryNazione     $rigaNazione[Tar_Progr]";
		return $rigaNazione['Tar_Progr'];
	}
	
	function CercaNomeNazioneDaNumero ($numero)
	{
		$queryNazione = "SELECT Tar_Nazione_Nome FROM targhe_estere_zone_competenza ";
		$queryNazione .= "WHERE Tar_Nazione_Num = '$numero' ";
		$resNazione = mysql_query($queryNazione);
		$rigaNazione = mysql_fetch_assoc($resNazione);
		//echo "<br>$queryNazione     $rigaNazione[Tar_Progr]";
		return $rigaNazione['Tar_Nazione_Nome'];
	}
	
	function SettaStatoNazione ()
	{
		$queryCerca = "SELECT Tar_Nazione_Num FROM targhe_estere_zone_competenza ";
		$queryCerca .= "WHERE Tar_Nazione_Nome = '" . $this->Tar_Nazione_Nome . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		if ($rigaCerca['Tar_Nazione_Num'] != NULL)
			return $rigaCerca['Tar_Nazione_Num'];
		else 
		{
			$query2Cerca = "SELECT MAX(Tar_Nazione_Num) as MAX FROM targhe_estere_zone_competenza ";
			$res2Cerca = mysql_query($query2Cerca);
			$riga2Cerca = mysql_fetch_assoc($res2Cerca);
			return ($riga2Cerca['MAX'] + 1);
		}
	}
	
	function ArrayQueryPerNazione ($nomeStato, $dueLettere)
	{
		$queryNazione = "SELECT Tar_Progr FROM targhe_estere_zone_competenza ";
		$queryNazione .= " WHERE Tar_Nazione_Nome = '" . $nomeStato . "' ";
		
		if ($dueLettere != "") $queryNazione .= " AND Tar_Regione = '$dueLettere' ";
		
		$resNazione = mysql_query($queryNazione);
		$arrayProgr = array();
		while ($rigaNazione = mysql_fetch_assoc($resNazione))
		{
			$arrayProgr[] = $rigaNazione['Tar_Progr'];
		}
		return $arrayProgr;
	}
	
	function ZonaGiaPresente ()
	{
		$queryCerca = "SELECT Tar_Progr FROM targhe_estere_zone_competenza ";
		$queryCerca .= "WHERE Tar_Nazione_Nome = '" . $this->Tar_Nazione_Nome . "' ";
		$queryCerca .= "AND Tar_Regione = '" . $this->Tar_Regione . "' ";
		$queryCerca .= "AND Tar_Indirizzo_Prima_Riga = '" . $this->Tar_Indirizzo_Prima_Riga . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Tar_Progr'];
	}
	
	function InsertUpdateZonaCompetenza ()
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		if ($this->Tar_Progr == NULL)
		{
			$insUpd = "INSERT";
			$this->Tar_Progr = $this->ZonaGiaPresente();
			$this->Tar_Nazione_Num = $this->SettaStatoNazione();
			if ($this->Tar_Progr == NULL)
			{
				$queryUpd = "SELECT Tar_Progr FROM targhe_estere_zone_competenza WHERE Tar_Progr = '$this->Tar_Progr'";
				$resUpd = mysql_query($queryUpd);
				$rigaUpd = mysql_fetch_assoc($resUpd);
				if ($rigaUpd['Tar_Progr'] == NULL)
					$insUpd = "INSERT";
				else
					$insUpd = "UPDATE";
			}
		}
		else
		{
			$this->Tar_Nazione_Num = $this->SettaStatoNazione();
			$insUpd = "UPDATE";
		}
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "Tar_Progr" && $campo != "Coll_Lingua")
			{
				$fields[] = $campo;
				$values[] = trim($valore);
			}
		}
		
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_zona_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_zona_locale($this->Tar_Progr, $fields, $values);
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
	
	public function insert_zona_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_zone_competenza (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_zona_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldZona = new targhe_estere_zone_competenza($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldZona->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_zone_competenza SET $clause WHERE Tar_Progr = '" . $key . "'";
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
}

/*
CREATE TABLE `targhe_estere_articoli` (
`Id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`Comune` varchar(5) NOT NULL,
`Anno` int(6) NOT NULL,
`Articolo` int(6) NOT NULL,
`Comma` int(6) DEFAULT NULL,
`Lettera` varchar(4) DEFAULT NULL,
`Descrizione` longtext,
PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

/*
class targhe_estere_articoli
{
	public $Id = NULL;
	public $Comune = NULL;
	public $Anno = NULL;
	public $Articolo = NULL;
	public $Comma = NULL;
	public $Lettera = NULL;
	public $Descrizione = NULL;
	
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
		
		$query = "SELECT * FROM targhe_estere_articoli WHERE Id = '" . $progr . "'";
		$result = safe_query($query);
		$rigaArticoli = mysql_fetch_assoc($result);
	
		foreach ($rigaArticoli as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}
}*/


/*
 * CREATE TABLE `targhe_estere_rilevatori_velocita` (
  `Ril_Progr` int(11) unsigned NOT NULL auto_increment,
  `Ril_Comune` varchar(4) NOT NULL default '',
  `Ril_Tipo` text NOT NULL,
  `Ril_Marca` text NOT NULL,
  `Ril_Modello` text NOT NULL,
  `Ril_Omologazione` text NOT NULL,
  `Ril_Tolleranza` float NOT NULL default '5',
  `Ril_Velocita` enum('Y','N') NOT NULL default 'N',
  `Ril_Testo` text NOT NULL,
  `Ril_Matricola_Sistema` varchar(10) NOT NULL default '',
  `Ril_Postazione_Fissa` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`Ril_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class targhe_estere_rilevatori_velocita
{
	public $Ril_Progr = NULL;
	public $Ril_Comune = NULL;
	public $Ril_Tipo = NULL;
	public $Ril_Marca = NULL;
	public $Ril_Omologazione = NULL;
	public $Ril_Modello = NULL;
	public $Ril_Tolleranza = NULL;
	public $Ril_Velocita = NULL;
	public $Ril_Testo = NULL;
	public $Ril_Matricola_Sistema = NULL;
	public $Ril_Postazione_Fissa = NULL;
	
	public $Coll_Traduzione_Inglese = NULL;
	public $Coll_Traduzione_Tedesco = NULL;
	public $Coll_Traduzione_Spagnolo = NULL;
	public $Coll_Traduzione_Francese = NULL;
	
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
		if ($progr == "0") return;
		
		$query = "SELECT * FROM targhe_estere_rilevatori_velocita WHERE Ril_Progr = '" . $progr . "'";
		//echo "<br>$query";
		$result = mysql_query($query);
		$rigaRilevatori = mysql_fetch_assoc($result);
	
		foreach ($rigaRilevatori as $key => $value)
		{
			$this->$key = /*utf8_decode(*/$value;//);
		}
		
		$myTraduzione = new rilevatori_traduzioni(NULL);
		$idInglese = $myTraduzione->IdTraduzioneDaRilELingua($this->Ril_Progr, 2);
		$this->Coll_Traduzione_Inglese = new rilevatori_traduzioni($idInglese);
		$idTedesco = $myTraduzione->IdTraduzioneDaRilELingua($this->Ril_Progr, 3);
		$this->Coll_Traduzione_Tedesco = new rilevatori_traduzioni($idTedesco);
		$idSpagnolo = $myTraduzione->IdTraduzioneDaRilELingua($this->Ril_Progr, 4);
		$this->Coll_Traduzione_Spagnolo = new rilevatori_traduzioni($idSpagnolo);
		$idFrancese = $myTraduzione->IdTraduzioneDaRilELingua($this->Ril_Progr, 5);
		$this->Coll_Traduzione_Francese = new rilevatori_traduzioni($idFrancese);
	}
	
	public function DescrizioneRilevatore ()
	{
		$scrittaRilevatore = $this->Ril_Tipo;
		if ($this->Ril_Marca != "") $scrittaRilevatore .= " " . $this->Ril_Marca;
		if ($this->Ril_Modello != "") $scrittaRilevatore .= " " . $this->Ril_Modello;
		return $scrittaRilevatore;
	}
	
	public function DescrizionePerVerbaleRilevatore ()
	{
		$scrittaRilevatore = $this->Ril_Tipo;
		if ($this->Ril_Marca != "") $scrittaRilevatore .= " " . $this->Ril_Marca;
		if ($this->Ril_Omologazione != "") $scrittaRilevatore .= " " . $this->Ril_Omologazione;
		if ($this->Ril_Modello != "") $scrittaRilevatore .= " " . $this->Ril_Modello;
		return $scrittaRilevatore;
	}
	
	public function InsertRilevatore (
			$myComune,
			$myTipo,
			$myMarca,
			$myModello,
			$myOmologazione,
			$myTolleranza,
			$myVelocita,
			$myTesto,
			$myMatricola_Sistema,
			$myPostazione_Fissa
		)
	{
		//$campi = "";
		
		$clause = "
			Ril_Comune, 
			Ril_Tipo,
			Ril_Marca,
			Ril_Modello,
			Ril_Omologazione,
			Ril_Tolleranza,
			Ril_Velocita,
			Ril_Testo,
			Ril_Matricola_Sistema,
			Ril_Postazione_Fissa ";
		$valori = "
			'$myComune', 
			'$myTipo',
			'$myMarca',
			'$myModello',
			'$myOmologazione',
			'$myTolleranza',
			'$myVelocita',
			'$myTesto',
			'$myMatricola_Sistema',
			'$myPostazione_Fissa'";
		
		//$clause = "";
		
		/*foreach ($this as $key => $value)
		{
			if ($key != "Ril_Progr" && 
				$key != "Coll_Traduzione_Inglese" && 
				$key != "Coll_Traduzione_Tedesco" && 
				$key != "Coll_Traduzione_Spagnolo" && 
				$key != "Coll_Traduzione_Francese")
			{
				$clause .= $key . ", ";
				//$valori .= '"' . $value . '", ';
			}
		}*/
		//$clause = substr ($clause, 0, -2);  // tolgo l'ultima virgola
		//$valori = substr ($valori, 0, -2);  // tolgo l'ultima virgola
		
		$queryInsert = "INSERT INTO targhe_estere_rilevatori_velocita (" . $clause . ") 
						values (" . $valori . ")";
		//echo "<br>$queryInsert";
		$progr = mysql_query($queryInsert);
		$progr = mysql_insert_id();
		return $progr;
	}
	
	/*function UpdateRilevatore (
			$myProgr,
			$myComune,
			$myTipo,
			$myMarca,
			$myModello,
			$myOmologazione,
			$myTolleranza,
			$myVelocita,
			$myTesto,
			$myMatricola_Sistema,
			$myPostazione_Fissa
		)
	{
		$campi = "";
		$valori = array (
			$myComune,
			$myTipo,
			$myMarca,
			$myModello,
			$myOmologazione,
			$myTolleranza,
			$myVelocita,
			$myTesto,
			$myMatricola_Sistema,
			$myPostazione_Fissa);
		
		$z = 0;
		foreach ($this as $key => $value)
		{
			if ($key != "Ril_Progr")
			{
				$clause .= $key . ' = "' . $value[$z++] . '", ';
			}
		}
		$clause = substr ($clause, 0, -2);  // tolgo l'ultima virgola
	
		$queryUpdate = "UPDATE targhe_estere_rilevatori_velocita SET " . $clause . "
						WHERE Ril_Progr = '". $myProgr . "'";
		//echo "<br>$queryUpdate";
		$verif = safe_query($queryUpdate);
		//$progr = mysql_insert_id();
		return $verif;
	}*/
	
	public function TolleranzaRilevatore ($velocitaRegistrata)
	{
		$tolleranza = $velocitaRegistrata * $this->Ril_Tolleranza / 100;
		if ($tolleranza < 5) $tolleranza = 5;
		//$tolleranza = round($tolleranza, 0);
		$velocitaMenoToll = $velocitaRegistrata - $tolleranza;
		
		return $velocitaMenoToll; 
	}
	
	public function CercaRilevatoreDaMatricola ($matricola, $comune)
	{
		$queryRil = "SELECT Ril_Progr FROM targhe_estere_rilevatori_velocita ";
		$queryRil .= " WHERE Ril_Matricola_Sistema = '" . $matricola . "' AND ";
		$queryRil .= " Ril_Comune = '" . $comune . "' ";
		//echo "<br>$queryRil";
		$resRil = mysql_query($queryRil);
		$rigaRil = mysql_fetch_assoc($resRil);
		
		if ($rigaRil['Ril_Progr'] == NULL) return NULL;
		
		$query = "SELECT * FROM targhe_estere_rilevatori_velocita WHERE Ril_Progr = '" . $rigaRil['Ril_Progr'] . "'";
		$result = mysql_query($query);
		$rigaRilevatori = mysql_fetch_assoc($result);
		
		foreach ($rigaRilevatori as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
		return $this->Ril_Progr;
	}
	
	public function RilevatoreGiaPresente ()
	{
		$queryPresente = "SELECT Ril_Progr FROM targhe_estere_rilevatori_velocita ";
		$queryPresente .= " WHERE Ril_Tipo = '" . $this->Ril_Tipo . "' AND ";
		$queryPresente .= " Ril_Modello = '" . $this->Ril_Modello . "' AND ";
		$queryPresente .= " Ril_Matricola_Sistema = '" . $this->Ril_Matricola_Sistema . "' AND ";
		$queryPresente .= " Ril_Comune = '" . $this->Ril_Comune . "' ";
		$resRil = mysql_query($queryPresente);
		$rigaRil = mysql_fetch_assoc($resRil);
		return $rigaRil['Ril_Progr'];
	}
	
	public function MatricolaSistemaGiaPresente ()
	{
		$queryPresente = "SELECT Ril_Progr FROM targhe_estere_rilevatori_velocita ";
		$queryPresente .= " WHERE Ril_Matricola_Sistema = '" . $this->Ril_Matricola_Sistema . "' AND ";
		$queryPresente .= " Ril_Comune = '" . $this->Ril_Comune . "' AND ";
		$queryPresente .= " Ril_Progr != '" . $this->Ril_Progr . "'  ";
		$resRil = mysql_query($queryPresente);
		$rigaRil = mysql_fetch_assoc($resRil);
		return $rigaRil['Ril_Progr'];
	}
	
	public function InsertUpdateRilevatore ()
	{
		if ($this->Ril_Velocita == "")
			$this->Ril_Velocita = "N";
		if ($this->Ril_Postazione_Fissa == "")
			$this->Ril_Postazione_Fissa = "N";
		
		if ($this->Ril_Velocita == "Y" && ($this->Ril_Tolleranza == "" || $this->Ril_Tolleranza == "0"))
		{
			return "ERRORETOLLERANZA1";
		}
		if ($this->Ril_Velocita == "N" && $this->Ril_Tolleranza != "" && $this->Ril_Tolleranza != "0")
		{
			return "ERRORETOLLERANZA2";
		}
		
		$clause = "";
		$values = "";
		
		if ($this->MatricolaSistemaGiaPresente() != NULL)
		{
			return "MATRICOLASISTEMAPRESENTE";
		}
		
		if ($this->Ril_Progr == NULL)  //  se è pieno, faccio update
		{
			$this->Ril_Progr = $this->RilevatoreGiaPresente();
		}
		
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "Ril_Progr" && 
				$campo != "Coll_Traduzione_Inglese" && 
				$campo != "Coll_Traduzione_Tedesco" && 
				$campo != "Coll_Traduzione_Spagnolo" && 
				$campo != "Coll_Traduzione_Francese")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
		//alert ($this->Acc_Progr);
		
		if ($this->Ril_Progr == NULL)
		{
			$risposta = $this->insert_rilevatore_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; $this->Ril_Progr = mysql_insert_id(); break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else
		{
			$risposta = $this->update_rilevatore_locale($this->Ril_Progr, $fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "UPDATE_OK"; break;
				case false: $risposta = "UPDATE_ERROR"; break;
				default: break;
			}
		}
		return $risposta;
	}
	
	public function insert_rilevatore_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_rilevatori_velocita (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		//echo $query;
	
		return true;
	}
	
	public function update_rilevatore_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldRilevatore = new targhe_estere_rilevatori_velocita($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldRilevatore->$fields_to_update[$i] != $values_to_update[$i])
			{
				if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
			
	
		$query = "UPDATE targhe_estere_rilevatori_velocita SET $clause WHERE Ril_Progr = '" . $key . "'";
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		//echo $query;
	
		return true;
	}
}

/*
CREATE TABLE `targhe_estere_accertatori` (
`Acc_Progr` int(11) unsigned NOT NULL auto_increment,
`Acc_Comune` varchar(4) default NULL,
`Acc_Accertatore` varchar(200) default NULL,
`Acc_Matricola` varchar(20) default NULL,
`Acc_Tipo_Accertatore` enum('E','A') NOT NULL default 'E',
`Acc_Poteri_Accertatore` text NOT NULL,
`Acc_Firma_Digitale` varchar(100) NOT NULL default '',
PRIMARY KEY  (`Acc_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/
 
class targhe_estere_accertatori
{
	public $Acc_Progr = NULL;
	public $Acc_Comune = NULL;
	public $Acc_Accertatore = NULL;
	public $Acc_Matricola = NULL;
	public $Acc_Tipo_Accertatore = NULL;
	public $Acc_Poteri_Accertatore = NULL;
	public $Acc_Firma_Digitale = NULL;
	
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM targhe_estere_accertatori WHERE Acc_Progr = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaAccertatori = mysql_fetch_assoc($result);
		//echo "<br>$query";
		
		foreach ($rigaAccertatori as $key => $value)
		{
			$this->$key = /*utf8_decode*/($value);
		}
	}
	
	public function CercaAccertatoreDaMatricola ($nomecognome, $comune)
	{
		$queryAcc = "SELECT Acc_Progr FROM targhe_estere_accertatori ";
		$queryAcc .= " WHERE Acc_Accertatore = '" . $nomecognome . "' AND ";
		$queryAcc .= " Acc_Comune = '" . $comune . "' ";
		//echo "<br>$queryAcc";
		$resAcc = mysql_query($queryAcc);
		$rigaAcc = mysql_fetch_assoc($resAcc);
		
		if ($rigaAcc['Acc_Progr'] == NULL) return NULL;
		
		$query = "SELECT * FROM targhe_estere_accertatori WHERE Acc_Progr = '" . $rigaAcc['Acc_Progr'] . "'";
		$result = mysql_query($query);
		$rigaAccertatori = mysql_fetch_assoc($result);
		
		foreach ($rigaAccertatori as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
		return $this->Acc_Progr;
	}
	
	public function AccertatoreGiaPresente ()
	{
		$queryPresente = "SELECT Acc_Progr FROM targhe_estere_accertatori ";
		$queryPresente .= " WHERE Acc_Accertatore = '" . $this->Acc_Accertatore . "' AND ";
		$queryPresente .= " Acc_Matricola = '" . $this->Acc_Matricola . "' AND ";
		$queryPresente .= " Acc_Comune = '" . $this->Acc_Comune . "' ";
		$resAcc = mysql_query($queryPresente);
		$rigaAcc = mysql_fetch_assoc($resAcc);
		return $rigaAcc['Acc_Progr'];
	}
	
	public function MatricolaGiaPresente ()
	{
		$queryPresente = "SELECT Acc_Progr FROM targhe_estere_accertatori ";
		$queryPresente .= " WHERE Acc_Matricola = '" . $this->Acc_Matricola . "' AND ";
		$queryPresente .= " Acc_Comune = '" . $this->Acc_Comune . "' ";
		$resAcc = mysql_query($queryPresente);
		$rigaAcc = mysql_fetch_assoc($resAcc);
		return $rigaAcc['Acc_Progr'];
	}
	
	public function InsertUpdateAccertatore ()
	{
		$clause = "";
		$values = "";
		
		if ($this->Acc_Progr == NULL)  //  se è pieno, faccio update
		{
			if ($this->MatricolaGiaPresente() != NULL)
			{
				return "MATRICOLAPRESENTE";
			}
			$this->Acc_Progr = $this->AccertatoreGiaPresente();
		}
		
		//if ($this->Acc_Firma_Digitale != "")
		{
			$temp = $this->UnicoFirmaDigitale();
			switch ($temp)
			{
				case "OK_UNAFIRMA": break;
				case "OK_NESSUNAFIRMA_ALERT": break;
				case "ERRORE_TROPPEFIRME": return "ERRORE_TROPPEFIRME"; break;
				default: return "NONSOCOSASIA"; break;
			}
		}
		
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "Acc_Progr")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		//alert ($this->Acc_Progr);
		
		if ($this->Acc_Progr == NULL)
		{
			$risposta = $this->insert_accertatore_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; $this->Acc_Progr = mysql_insert_id(); break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else
		{
			$risposta = $this->update_accertatore_locale($this->Acc_Progr, $fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "UPDATE_OK"; break;
				case false: $risposta = "UPDATE_ERROR"; break;
				default: break;
			}
		}
		return $risposta;
	}
	
	public function insert_accertatore_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_accertatori (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		//echo $query;
	
		return true;
	}
	
	public function update_accertatore_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldAccertatore = new targhe_estere_accertatori($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldAccertatore->$fields_to_update[$i] != $values_to_update[$i])
			{
				if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
			
	
		$query = "UPDATE targhe_estere_accertatori SET $clause WHERE Acc_Progr = '" . $key . "'";
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		//echo $query;
	
		return true;
	}
	
	public function UnicoFirmaDigitale ()
	//  per ogni comune è necessario un unico accertatore con il diritto di firma
	{
		$queryUnico = "SELECT Acc_Progr, Acc_Firma_Digitale FROM targhe_estere_accertatori ";
		$queryUnico .= " WHERE Acc_Comune = '" . $this->Acc_Comune . "' AND ";
		$queryUnico .= " Acc_Firma_Digitale != '' ";
		//$queryUnico .= " Acc_Progr != '" . $this->Acc_Progr . "' ";
		$resUnico = mysql_query($queryUnico);
		$numRighe = mysql_num_rows($resUnico);
		if ($numRighe == 0)
		{
			if ($this->Acc_Firma_Digitale != NULL)
				return "OK_UNAFIRMA";
			else
				return "OK_NESSUNAFIRMA_ALERT";
		}
		else if ($numRighe == 1)
		{
			$rigaFirma = mysql_fetch_assoc($resUnico);
			if ($this->Acc_Firma_Digitale != "")
			{
				if ($rigaFirma['Acc_Progr'] == $this->Acc_Progr)
					return "OK_UNAFIRMA";
				else 
					return "ERRORE_TROPPEFIRME";
			}
			else
			{
				if ($rigaFirma['Acc_Progr'] == $this->Acc_Progr)
					return "OK_NESSUNAFIRMA_ALERT";
				else 
					return "OK_UNAFIRMA";
			}
		}
		else
		{
			return "ERRORE_TROPPEFIRME";
		}
	}
}

/*
CREATE TABLE `targhe_estere_articoli` (
  `Tar_Progr` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Tar_Com` varchar(4) NOT NULL DEFAULT '',
  `Tar_Descrizione` longtext NOT NULL,
  `Tar_Descrizione_Agg` longtext NOT NULL,
  `Tar_Codice_Tributo_Cne` varchar(4) NOT NULL DEFAULT '',
  `Tar_Tariffa_Sanz` float NOT NULL DEFAULT '0',
  `Tar_Tariffa_Sanz_Max` float NOT NULL DEFAULT '0',
  `Tar_Articolo` int(9) NOT NULL DEFAULT '0',
  `Tar_Comma` int(9) NOT NULL DEFAULT '0',
  `Tar_Lettera` varchar(10) NOT NULL DEFAULT '',
  `Tar_Punti` int(9) NOT NULL DEFAULT '0',
  `Tar_Punti_Neop` int(9) NOT NULL DEFAULT '0',
  `Tar_Sanz_Acc` text NOT NULL,
  `Tar_Sanz_Penale` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Sanzione_Accessoria` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Codice_Sanz_Acc` varchar(10) NOT NULL DEFAULT '',
  `Tar_Documenti` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Ritiro_Patente` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Casi_Particolari` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Autorita_Competente` varchar(100) NOT NULL DEFAULT '',
  `Tar_Codice_Art` varchar(9) NOT NULL DEFAULT '',
  `Tar_Tipo_Art` varchar(40) NOT NULL DEFAULT '',
  `Tar_Num_Tipo` int(9) NOT NULL DEFAULT '0',
  `Tar_Comma_Due` int(9) NOT NULL DEFAULT '0',
  `Tar_Comma_Tre` int(9) NOT NULL DEFAULT '0',
  `Tar_Lettera_Due` varchar(10) NOT NULL DEFAULT '',
  `Tar_Lettera_Tre` varchar(10) NOT NULL DEFAULT '',
  `Tar_Raddoppio_Sanz_Bus` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Raddoppio_Sanz_Camion` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_UnTerzo_Notturno` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Tar_Testo_Gen` text NOT NULL,
  `Tar_Anno` year(4) NOT NULL DEFAULT '0000',
  `Tar_Art_126` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Recidivo` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Codice_Ipotesi` varchar(4) NOT NULL DEFAULT '',
  `Tar_Pagamento_Ridotto` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Tar_Sanz_Acc_Due` text NOT NULL,
  `Tar_Sanzione_Accessoria_Due` enum('N','Y') NOT NULL DEFAULT 'N',
  `Tar_Codice_Sanz_Acc_Due` varchar(10) NOT NULL DEFAULT '',
  `Tar_Sanz_Acc_Tre` text NOT NULL,
  `Tar_Sanzione_Accessoria_Tre` enum('N','Y') NOT NULL DEFAULT 'N',
  `Tar_Codice_Sanz_Acc_Tre` varchar(10) NOT NULL DEFAULT '',
  `Tar_Riduzione_5_Giorni` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Tar_Genere_Infrazione` varchar(30) NOT NULL DEFAULT '',
  `Tar_Sospensione_Patente` enum('Y','N') NOT NULL DEFAULT 'N',
  `Tar_Codice_1` varchar(3) NOT NULL DEFAULT '',
  `Tar_Codice_2` varchar(3) NOT NULL DEFAULT '',
  `Tar_Codice_3` varchar(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`Tar_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/


class targhe_estere_articoli
{
	public $Tar_Progr = NULL;
	public $Tar_Com = NULL;
	public $Tar_Descrizione = NULL;
	public $Tar_Descrizione_Agg = NULL;
	public $Tar_Codice_Tributo_Cne = NULL;
	public $Tar_Tariffa_Sanz = NULL;
	public $Tar_Tariffa_Sanz_Max = NULL;
	public $Tar_Articolo = NULL;
	public $Tar_Comma = NULL;
	public $Tar_Lettera = NULL;
	public $Tar_Punti = NULL;
	public $Tar_Punti_Neop = NULL;
	public $Tar_Sanz_Acc = NULL;
	public $Tar_Sanz_Penale = NULL;
	public $Tar_Sanzione_Accessoria = NULL;
	public $Tar_Codice_Sanz_Acc = NULL;
	public $Tar_Documenti = NULL;
	public $Tar_Ritiro_Patente = NULL;
	public $Tar_Casi_Particolari = NULL;
	public $Tar_Autorita_Competente = NULL;
	public $Tar_Codice_Art = NULL;
	public $Tar_Tipo_Art = NULL;
	public $Tar_Num_Tipo = NULL;
	public $Tar_Comma_Due = NULL;
	public $Tar_Comma_Tre = NULL;
	public $Tar_Lettera_Due = NULL;
	public $Tar_Lettera_Tre = NULL;
	public $Tar_Raddoppio_Sanz_Bus = NULL;
	public $Tar_Raddoppio_Sanz_Camion = NULL;
	public $Tar_UnTerzo_Notturno = NULL;
	public $Tar_Testo_Gen = NULL;
	public $Tar_Anno = NULL;
	public $Tar_Art_126 = NULL;
	public $Tar_Recidivo = NULL;
	public $Tar_Codice_Ipotesi = NULL;
	public $Tar_Pagamento_Ridotto = NULL;
	public $Tar_Sanz_Acc_Due = NULL;
	public $Tar_Sanzione_Accessoria_Due = NULL;
	public $Tar_Codice_Sanz_Acc_Due = NULL;
	public $Tar_Sanz_Acc_Tre = NULL;
	public $Tar_Sanzione_Accessoria_Tre = NULL;
	public $Tar_Codice_Sanz_Acc_Tre = NULL;
	public $Tar_Riduzione_5_Giorni = NULL;
	public $Tar_Genere_Infrazione = NULL;
	public $Tar_Sospensione_Patente = NULL;
	public $Tar_Codice_1 = NULL;
	public $Tar_Codice_2 = NULL;
	public $Tar_Codice_3 = NULL;
	
	public $Coll_Traduzione_Italiano;
	public $Coll_Traduzione_Inglese;
	public $Coll_Traduzione_Tedesco;
	public $Coll_Traduzione_Spagnolo;
	public $Coll_Traduzione_Francese;
	
	public function __construct( $progr )
	{
		if ($progr == NULL || $progr == 0) return;
	
		$query = "SELECT * FROM targhe_estere_articoli WHERE Tar_Progr = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaArticoli = mysql_fetch_assoc($result);
		
		foreach ($rigaArticoli as $key => $value)
		{
			if ($key == "Tar_Tariffa_Sanz") $this->$key = number_format($value, 2, ".", "");
			else if ($key == "Tar_Tariffa_Sanz_Max") $this->$key = number_format($value, 2, ".", "");
			else $this->$key = /*utf8_decode(*/$value;//);
		}
		
		$this->Coll_Traduzione_Italiano = new articoli_traduzioni(null, $progr, 1);
		$this->Coll_Traduzione_Inglese = new articoli_traduzioni(null, $progr, 2);
		$this->Coll_Traduzione_Tedesco = new articoli_traduzioni(null, $progr, 3);
		$this->Coll_Traduzione_Spagnolo = new articoli_traduzioni(null, $progr, 4);
		$this->Coll_Traduzione_Francese = new articoli_traduzioni(null, $progr, 5);
	}
	
	public function ScriviArticoloCompleto ()
	{
		$art = $this->Tar_Articolo;
		$com = $this->Tar_Comma;
		$let = $this->Tar_Lettera;
		$tes = $this->Tar_Testo_Gen;
		
		$stringa = $art;
		if ($com != "" && $com != "0")
		{
			$stringa .= " c." . $com;
			//$stringa .= "/" . $com;
		}
		if ($tes != "" && $tes != "-")
			$stringa .= " e " . $tes;
		if ($let != "")
			$stringa .= "/" . $let;
		return $stringa;
	}
	
	public function GenereArticolo ()
	{
		switch ($this->Tar_Genere_Infrazione)
		{
			case "AUTOVELOX": break;
			case "SEMAFORO": break;
			case "ZTL": break;
			case "SOSTA": break;
			case "126BIS": break;
			case "ALTRO": break;
			default: alert ("errore tipologia in classi/targhe_estere.php"); break;
		}
	}
	
	public function ListaGenereArticolo ($selezionato)
	{
		$stringaSelect = "<option value=''></option>\n";
		$arrayGeneri = array ("AUTOVELOX", "SEMAFORO", "ZTL", "SOSTA", "ALTRO", "126BIS");
		for ($i = 0; $i < count($arrayGeneri); $i++)
		{
			$questo = $arrayGeneri[$i];
			$stringaSelect .= "<option value='$questo'";
			if ($selezionato == $questo) $stringaSelect .= " selected";
			$stringaSelect .= ">$questo</option>\n";
		}
		return $stringaSelect;
	}
	
	public function DiurnoNotturno ($oraInfrazione)  //  deve arrivare in formato hh:mm oppure hh:mm:ss
	{
		if ($oraInfrazione == NULL) return;
		if ($this->Tar_UnTerzo_Notturno == 'N') return;
		if ($this->Tar_Genere_Infrazione == "AUTOVELOX" || $this->Tar_Genere_Infrazione == "SEMAFORO")
		{
			$esplodoPunti = explode(":", $oraInfrazione);
			if ($esplodoPunti[0] >= 22 || $esplodoPunti[0] < 7 || ($esplodoPunti[0] == 7 && $esplodoPunti[1] == 0))
			{
				$this->Tar_Tariffa_Sanz = number_format(($this->Tar_Tariffa_Sanz + $this->Tar_Tariffa_Sanz / 3), 2, ".", "");
				$this->Tar_Tariffa_Sanz_Max = number_format(($this->Tar_Tariffa_Sanz_Max + $this->Tar_Tariffa_Sanz_Max / 3), 2, ".", "");
			}
		}
	}
	
	public function DuppppppppplicaTuttiArticoli (/*$daComune, $destComune*/$comune, $anno)   //  copia tutti gli articoli del comune DACOMUNE al comune DESTCOMUNE
	{
		$queryNuovoComune = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$queryNuovoComune .= " WHERE Tar_Com = '" . $comune . "' ";
		$resNuovo = mysql_query($queryNuovoComune);
		$numComune = mysql_num_rows($resNuovo);
		if ($numComune == 0) $comuneNuovo = true;
		else $comuneNuovo = false;
		
		$queryComuni = "SELECT DISTINCT Tar_Com FROM targhe_estere_articoli ";
		$queryComuni .= " WHERE Tar_Anno = " . $anno;
		$resComuni = mysql_query($queryComuni);
		$resDestComuni = mysql_query($queryComuni);
		$resPerNuovoComune = mysql_query($queryComuni);
		
		while ($rigaComuni = mysql_fetch_assoc($resComuni))
		
		/*$queryArticoli = "SELECT * FROM targhe_estere_articoli WHERE Tar_Com = '$destComune'";
		$resArticoli = mysql_query($queryArticoli);
		
		if (mysql_num_rows($resArticoli) == 0)  //  se non ci sono articoli inseriti per il comune DESTCOMUNE*/
		{
			$daComune = $rigaComuni['Tar_Com'];
			echo "<br>INIZIO DA COMUNE " . $daComune;
			
			while ($rigaDestComuni = mysql_fetch_assoc($resDestComuni))
			{
				$destComune = $rigaDestComuni['Tar_Com'];
				if ($daComune != $destComune)
				{
					echo "<br>INIZIO DEST COMUNE " . $destComune;
				
					/*$queryCopiaArticoli = "SELECT Tar_Com FROM targhe_estere_articoli";
					$resCopiaArticoli = safe_query($queryCopiaArticoli);
				
					$rigaCopiaArticoli = mysql_fetch_assoc($resCopiaArticoli);  //  prendo il primo
					$myCopiaComune = $rigaCopiaArticoli ['Tar_Com'];*/
					
					//alert (single_answer_query("select DATABASE()"));
				
					$queryCopiaComuneArticoli = "SELECT * FROM targhe_estere_articoli ";
					$queryComuni .= " WHERE Tar_Com = '$daComune' AND ";
					$queryComuni .= " Tar_Anno = " . $anno;	
					$resCopiaComuneArticoli = mysql_query($queryCopiaComuneArticoli);
				
					while ($rigaCopiaComuneArticoli = mysql_fetch_assoc($resCopiaComuneArticoli))
					{
						echo "<br>INIZIO TAR PROGR " . $rigaCopiaComuneArticoli ['Tar_Progr'] . "(" . $rigaCopiaComuneArticoli ['Tar_Com'] . ")";
						$myCopiaArticolo = new targhe_estere_articoli($rigaCopiaComuneArticoli ['Tar_Progr']);
						$myNewArticolo = new targhe_estere_articoli(NULL);
						$myNewArticolo->Tar_Com = $destComune;
						$myNewArticolo->Tar_Anno = $anno;
						foreach ($myCopiaArticolo as $key => $value)
						{
							if ($key != 'Tar_Progr' && 
								$key != 'Tar_Com' && 
								$key != 'Tar_Anno' &&
								$key != 'Coll_Traduzione_Italiano' &&
								$key != 'Coll_Traduzione_Inglese' &&
								$key != 'Coll_Traduzione_Tedesco' &&
								$key != 'Coll_Traduzione_Spagnolo' &&
								$key != 'Coll_Traduzione_Francese')
							{
								//echo "<br>$key => $value";
								$myNewArticolo->$key = $value;
							}
						}
						echo "<br>insert " . $myCopiaArticolo->Tar_Anno . " e " . $myCopiaArticolo->Tar_Com . " --- " . $myNewArticolo->Tar_Com . " e " . $myNewArticolo->Tar_Anno . " e " . $myNewArticolo->Tar_Articolo . " e " . $myNewArticolo->Tar_Comma . " e " . $myNewArticolo->Tar_Lettera;
						//$myNewArticolo->InsertUpdateArticolo();
					}
				}
			}
		}
		
		if ($comuneNuovo == true)
		{
			$destComune = $comune;
			
			$rigaPrimoComune = mysql_fetch_assoc($resPerNuovoComune);  //  è sufficiente 1 comune!
			if (1)
			{
				$daComune = $rigaPrimoComune['Tar_Com'];
			
				$queryCopiaComuneArticoli = "SELECT * FROM targhe_estere_articoli WHERE Tar_Com = '$daComune'";
				$resCopiaComuneArticoli = mysql_query($queryCopiaComuneArticoli);
				//alert (mysql_num_rows($resCopiaComuneArticoli));
			
				while ($rigaCopiaComuneArticoli = mysql_fetch_assoc($resCopiaComuneArticoli))
				{
					$myCopiaArticolo = new targhe_estere_articoli($rigaCopiaComuneArticoli ['Tar_Progr']);
					$myNewArticolo = new targhe_estere_articoli(NULL);
					$myNewArticolo->Tar_Com = $destComune;
					$myNewArticolo->Tar_Anno = $anno;
					foreach ($myCopiaArticolo as $key => $value)
					{
						if ($key != 'Tar_Progr' && 
							$key != 'Tar_Com' && 
							$key != 'Tar_Anno'&&
							$key != 'Coll_Traduzione_Italiano' &&
							$key != 'Coll_Traduzione_Inglese' &&
							$key != 'Coll_Traduzione_Tedesco' &&
							$key != 'Coll_Traduzione_Spagnolo' &&
							$key != 'Coll_Traduzione_Francese')
						{
							//echo "<br>$key => $value";
							$myNewArticolo->$key = $value;
						}
					}
					//echo "<br>insert " . $myCopiaArticolo->Tar_Progr . " -> " . $destComune;
					$myNewArticolo->InsertUpdateArticolo();
				}
			}
		}
	}
	
	public function DuplicaSingoloArticoloTraduzioni ($daProgr, $aComune, $aAnno)
	{
		$myCopiaArt = new targhe_estere_articoli($daProgr);
		$myCopiaArt->Tar_Progr = null;
		$myCopiaArt->Tar_Anno = $aAnno;
		$myCopiaArt->Tar_Com = $aComune;
		$myCopiaArt->InsertUpdateArticolo();
	
		$myNuovoProgr = $myCopiaArt->ArticoloGiaPresente();
	
		$myNuovoArt = new articoli_traduzioni(null);
		$myNuovoArt->CopiaTraduzioniDaArticolo($daProgr, $myNuovoProgr);
	}
	
	public function DuplicaAutomaticoArticoliTraduzioni ($daComune, $daAnno, $aComune, $aAnno)
	{
		$queryCerca = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$queryCerca .= " WHERE Tar_Com != '" . $daComune . "' AND ";
		$queryCerca .= " Tar_Anno = '" . $daAnno . "'  ";
		$resCerca = mysql_query($queryCerca);
		
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			$this->DuplicaSingoloArticoloTraduzioni($rigaCerca['Tar_Progr'], $aComune, $aAnno);
		}
	}
	
	public function DuplicaTuttiArticoli ($comune, $anno)
	{
		$queryCerca = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$queryCerca .= " WHERE Tar_Com != '" . $comune . "' AND ";
		$queryCerca .= " Tar_Anno = '" . $anno . "'  ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>$queryCerca --> " . mysql_num_rows($resCerca) ;
		
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			$myAltroArticolo = new targhe_estere_articoli($rigaCerca['Tar_Progr']);
			
			$queryArt = "SELECT Tar_Progr FROM targhe_estere_articoli ";
			$queryArt .= " WHERE Tar_Com = '" . $comune . "' AND ";
			$queryArt .= " Tar_Anno = '" . $anno . "' AND ";
			$queryArt .= " Tar_Articolo = '" . $myAltroArticolo->Tar_Articolo . "' AND ";
			$queryArt .= " Tar_Comma = '" . $myAltroArticolo->Tar_Comma . "' AND ";
			$queryArt .= " Tar_Lettera = '" . $myAltroArticolo->Tar_Lettera . "'  ";
			$resArt = mysql_query($queryArt);
			$rigaArticolo = mysql_fetch_assoc($resArt);
			if ($rigaArticolo['Tar_Progr'] == NULL)
			{
				//echo "<br>$queryArt  ->  " . $rigaArticolo['Tar_Progr'];
				$myNuovoArticolo = new targhe_estere_articoli($myAltroArticolo->Tar_Progr);
				$myNuovoArticolo->Tar_Progr = NULL;
				$myNuovoArticolo->Tar_Com = $comune;
				$myNuovoArticolo->Tar_Anno = $anno;
				$myNuovoArticolo->InsertUpdateArticolo();
			}
		}
	}
	
	public function ArticoloGiaPresente ()
	{
		$queryCerca = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$queryCerca .= " WHERE Tar_Articolo = '$this->Tar_Articolo' AND ";
		$queryCerca .= " Tar_Comma = '$this->Tar_Comma' AND ";
		$queryCerca .= " Tar_Lettera = '$this->Tar_Lettera' AND ";
		$queryCerca .= " Tar_Com = '$this->Tar_Com' AND ";
		$queryCerca .= " Tar_Anno = '$this->Tar_Anno'  ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>$queryCerca --> " . mysql_num_rows($resCerca) ;
		$rigaCerca = mysql_fetch_assoc($resCerca);
		//echo "<br>" . $rigaCerca['Tar_Progr'];
		return $rigaCerca['Tar_Progr'];
	}
	
	public function Articolo126BisDiComuneAnno ($comune, $anno)
	{
		$queryCerca = "SELECT Tar_Progr FROM targhe_estere_articoli ";
		$queryCerca .= " WHERE Tar_Articolo = '126' AND ";
		$queryCerca .= " Tar_Comma = '0' AND ";
		$queryCerca .= " Tar_Lettera = 'bis' AND ";
		$queryCerca .= " Tar_Com = '$comune' AND ";
		$queryCerca .= " Tar_Anno = '$anno'  ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>$queryCerca --> " . mysql_num_rows($resCerca) ;
		if (mysql_num_rows($resCerca) != 1)
		{
			alert ("Errore: articolo 126 bis mancante nel comune $comune e nell'anno $anno");
			return 0;
		}
		$rigaCerca = mysql_fetch_assoc($resCerca);
		//echo "<br>" . $rigaCerca['Tar_Progr'];
		return $rigaCerca['Tar_Progr'];
	}
	
	public function CopiaSingoloArticoloDaAnnoAdAnno ($progrArt, $nuovoAnno)
	{
		if ($progrArt == "") return;
		$tempArt = new targhe_estere_articoli($progrArt);
		if ($tempArt->Tar_Anno == $nuovoAnno) return;
		
		$queryArt = "SELECT Tar_Progr ";
		$queryArt .= " FROM targhe_estere_articoli ";
		$queryArt .= " WHERE Tar_Com = '" . $tempArt->Tar_Com . "' AND ";
		$queryArt .= " Tar_Articolo = '" . $tempArt->Tar_Articolo . "' AND ";
		$queryArt .= " Tar_Comma = '" . $tempArt->Tar_Comma . "' AND ";
		$queryArt .= " Tar_Lettera = '" . $tempArt->Tar_Lettera . "' AND ";
		$queryArt .= " Tar_Testo_Gen = '" . $tempArt->Tar_Testo_Gen . "' AND ";
		$queryArt .= " Tar_Anno = '" . $nuovoAnno . "'  ";
		$resCerca = mysql_query($queryArt);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		//echo "<br>" . $rigaCerca['Tar_Progr'];
		echo "<br>" . $queryArt;
		return $rigaCerca['Tar_Progr'];
	}
	
	public function InsertUpdateArticolo ($forzoInsertUpdate = NULL)  //  INSERT o UPDATE
	{
		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else if ($this->Tar_Progr == NULL)
		{
			$temp = $this->ArticoloGiaPresente();
			if ($temp == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$this->Tar_Progr = $temp;
				$insUpd = "UPDATE";
			}
		}
		else $insUpd = "UPDATE";
		
		$fields = array();
		$values = array();

		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "Tar_Progr" &&
				$campo != 'Coll_Traduzione_Italiano' &&
				$campo != 'Coll_Traduzione_Inglese' &&
				$campo != 'Coll_Traduzione_Tedesco' &&
				$campo != 'Coll_Traduzione_Spagnolo' &&
				$campo != 'Coll_Traduzione_Francese')
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
		
		if ($insUpd == "INSERT")
		{
			$verif = $this->insert_articolo_locale($fields, $values);
			$this->Tar_Progr = mysql_insert_id();
			if ($verif == true)
				$risp = "INSERT**OK**" . $this->Tar_Progr;
			else
				$risp = "INSERT**ERRORE**" . $this->Tar_Progr;
		}
		else if ($insUpd == "UPDATE")
		{
			$verif = $this->update_articolo_locale($this->Tar_Progr, $fields, $values);
			if ($verif == true)
				$risp = "UPDATE**OK**" . $this->Tar_Progr;
			else
				$risp = "UPDATE**ERRORE**" . $this->Tar_Progr;
		}
		
		return $risp;
	}
	


	public function InsertProvaUpdateArticolo ($forzoInsertUpdate = NULL)  //  INSERT o UPDATE
	{
		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else if ($this->Tar_Progr == NULL)
		{
			$temp = $this->ArticoloGiaPresente();
			if ($temp == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$this->Tar_Progr = $temp;
				$insUpd = "UPDATE";
			}
		}
		else $insUpd = "UPDATE";
	
		$fields = array();
		$values = array();
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) &&
			$campo != "Tar_Progr" &&
			$campo != 'Coll_Traduzione_Italiano' &&
			$campo != 'Coll_Traduzione_Inglese' &&
			$campo != 'Coll_Traduzione_Tedesco' &&
			$campo != 'Coll_Traduzione_Spagnolo' &&
			$campo != 'Coll_Traduzione_Francese')
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
	
		if ($insUpd == "INSERT")
		{
			$verif = $this->insert_articolo_prova_locale($fields, $values);
			$this->Tar_Progr = mysql_insert_id();
			if ($verif == true)
				$risp = "INSERT**OK**" . $this->Tar_Progr;
			else
				$risp = "INSERT**ERRORE**" . $this->Tar_Progr;
		}
		else if ($insUpd == "UPDATE")
		{
			$verif = $this->update_articolo_prova_locale($this->Tar_Progr, $fields, $values);
			if ($verif == true)
				$risp = "UPDATE**OK**" . $this->Tar_Progr;
			else
				$risp = "UPDATE**ERRORE**" . $this->Tar_Progr;
		}
	
		return $risp;
	}
	
	public function insert_articolo_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_articoli (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return mysql_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_articolo_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldArticolo = new targhe_estere_articoli($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if (stripslashes($myOldArticolo->$fields_to_update[$i]) != stripslashes($values_to_update[$i]))
			{
				if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_articoli SET $clause WHERE Tar_Progr = '" . $key . "'";
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
	
	public function insert_articolo_prova_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_articoli (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		//return mysql_query($query);
	
		echo "<br>" . $query;
	
		return true;
	}
	
	public function update_articolo_prova_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldArticolo = new targhe_estere_articoli($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if (stripslashes($myOldArticolo->$fields_to_update[$i]) != stripslashes($values_to_update[$i]))
			{
				if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_articoli SET $clause WHERE Tar_Progr = '" . $key . "'";
	
		/*if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;*/
	
		echo "<br>" . $query;
	
		return true;
	}
}

/*
 * CREATE TABLE `articoli_traduzioni` (
 		`ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
 		`Riferimento_Articolo` int(10) NOT NULL,
 		`Linguaggio` int(4) NOT NULL,
 		`Traduzione` text NOT NULL,
 		PRIMARY KEY (`ID`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1
*
*/

class articoli_traduzioni
{
	public $ID;
	public $Riferimento_Articolo;
	public $Linguaggio;
	public $Traduzione;

	public function __construct($id, $articoloIta = null, $linguaggio = null)
	{
		if ($id != null)
		{
			$queryArticoliLingue = "SELECT * FROM articoli_traduzioni ";
			$queryArticoliLingue .= " WHERE ID = '" . $id . "'";
		}
		else
		{
			if ($articoloIta == NULL) return NULL;
			if ($linguaggio == NULL) return NULL;
			//if ($linguaggio == 1) return NULL;
				
			$queryArticoliLingue = "SELECT * FROM articoli_traduzioni ";
			$queryArticoliLingue .= " WHERE Riferimento_Articolo = '" . $articoloIta . "'";
			$queryArticoliLingue .= " AND Linguaggio = '" . $linguaggio . "'";
		}
		//if ($_SESSION['CC_User'] == "***+") echo "<br>" . $queryArticoliLingue;
		$resultArt = mysql_query($queryArticoliLingue);

		$numRighe = mysql_num_rows($resultArt);
		//echo "<br>" . $queryMotiviLingue . " -> " . $numRighe;
		if ($numRighe > 1)
		{
			alert ("Attenzione, errore su articoli_traduzione: $numRighe");
			alert ($queryArticoliLingue);
		}

		while ($rigaTraduzione = mysql_fetch_assoc($resultArt))
		{
			//echo "<br>" . $queryMotiviLingue . " -> " . $numRighe;
			foreach ($rigaTraduzione as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}

	public function TraduzioneGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM articoli_traduzioni ";
		$queryCerca .= "WHERE Riferimento_Articolo = '" . $this->Riferimento_Articolo . "' ";
		$queryCerca .= "AND Linguaggio = '" . $this->Linguaggio . "' ";
		//echo "<br>" . $queryCerca;
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	public function CopiaTraduzioniDaArticolo ($riferimentoArt, $nuovoRiferimento)
	{
		$query = "SELECT * FROM articoli_traduzioni WHERE Riferimento_Articolo = $riferimentoArt";
		$resQuery = mysql_query($query);
		while ($rigaTrad = mysql_fetch_assoc($resQuery))
		{
			$this->ID = null;
			$this->Riferimento_Articolo = $nuovoRiferimento;
			$this->Linguaggio = $rigaTrad['Linguaggio'];
			$this->Traduzione = $rigaTrad['Traduzione'];
			$this->InsertUpdateTradArticolo();
			//echo "<br>" . $this->Linguaggio . " : " . $riferimentoArt . " - " . $nuovoRiferimento;
		}
	}

	public function InsertUpdateTradArticolo ($forzoInsertUpdate = NULL)  //  INSERT o UPDATE
	{
		$insUpd = "";
		$fields = array();
		$values = array();

		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else
		{
			$this->ID = $this->TraduzioneGiaPresente();

			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM articoli_traduzioni WHERE ID = '$this->ID'";
				$resUpd = mysql_query($queryUpd);
				$rigaUpd = mysql_fetch_assoc($resUpd);
				//alert ($queryUpd);
				if ($rigaUpd['ID'] == NULL)
					$insUpd = "INSERT";
				else
					$insUpd = "UPDATE";
			}
		}

		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "ID")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}

		$risposta = "";
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_trad_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_trad_locale($this->ID, $fields, $values);
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

	public function insert_trad_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO articoli_traduzioni (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";

		return safe_query($query);

		echo "<br>" . $query;

		return true;
	}

	public function update_trad_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);

		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;

		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;

		$myOldTrad = new articoli_traduzioni($key);

		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldTrad->$fields_to_update[$i] != $values_to_update[$i])
			{
				$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
			}
		}
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali

		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "

		$query = "UPDATE articoli_traduzioni SET $clause WHERE ID = '" . $key . "'";

		if (safe_query($query) != NULL) return TRUE;
		else return FALSE;

		echo "<br>" . $query;

		return true;
	}
}


/*
CREATE TABLE `rilevatori_traduzioni` (
`ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
`Riferimento_Rilevatore` int(4) NOT NULL,
`Linguaggio` int(4) NOT NULL,
`Traduzione` text NOT NULL,
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class rilevatori_traduzioni
{
	public $ID;
	public $Riferimento_Rilevatore;
	public $Linguaggio;
	public $Traduzione;
	
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM rilevatori_traduzioni WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaArticoli = mysql_fetch_assoc($result);
	
		foreach ($rigaArticoli as $key => $value)
		{
			$this->$key = /*utf8_decode(*/$value;//);
		}
	}
	
	function RilTradGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM rilevatori_traduzioni ";
		$queryCerca .= "WHERE Riferimento_Rilevatore = '" . $this->Riferimento_Rilevatore . "' ";
		$queryCerca .= "AND Linguaggio = '" . $this->Linguaggio . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function IdTraduzioneDaRilELingua ($id, $numLingua)
	{
		$queryCerca = "SELECT ID FROM rilevatori_traduzioni ";
		$queryCerca .= "WHERE Riferimento_Rilevatore = '" . $id . "' ";
		$queryCerca .= "AND Linguaggio = '" . $numLingua . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateRilevTraduzioni ($forzo = null)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
	
		$this->ID = $this->RilTradGiaPresente();
		
		if ($forzo != null)
		{
			$insUpd = $forzo;
		}
		else if ($this->ID == NULL)
		{
			$insUpd = "INSERT";
		}
		else
		{
			$insUpd = "UPDATE";
		}
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "ID")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
	
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_trad_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_trad_locale($this->ID, $fields, $values);
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
	
	public function insert_trad_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO rilevatori_traduzioni (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		return mysql_query($query);
		
		echo $query;
		
		return true;
	}
	
	public function update_trad_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldTraduz = new rilevatori_traduzioni($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldTraduz->$fields_to_update[$i] != $values_to_update[$i])
			{
			//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE rilevatori_traduzioni SET $clause WHERE ID = '" . $key . "'";
		
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
		
		echo $query;
		
		return true;
	}
}

$richiestaAccertatoriJquery = get_var('richiestaAccertatoriJquery');

if ($richiestaAccertatoriJquery)
{
	$progressivo = get_var('Acc_Progr');
	$myAccert = new targhe_estere_accertatori($progressivo);
	foreach ($_POST as $key => $value)
	{
		if ($key != "c" && $key != "richiestaAccertatoriJquery")
		{
			$myAccert->$key = $value;
		}
		else if ($key == "c") $myAccert->Acc_Comune = $value;
		else if ($key == "richiestaAccertatoriJquery") {}
	}
	
	$verif = $myAccert->InsertUpdateAccertatore();
	
	switch ($verif)
	{
		case "OK_UNAFIRMA": break;
		case "OK_NESSUNAFIRMA_ALERT": break;
		case "ERRORE_TROPPEFIRME": break;
		case "INSERT_OK": break;
		case "INSERT_ERROR": break;
		case "UPDATE_OK": break;
		case "UPDATE_ERROR": break;
		case "MATRICOLAPRESENTE": break;
	}
	
	echo $verif . "**" . $myAccert->Acc_Progr . "**" . $myAccert->Acc_Accertatore;
}

$richiestaRilevatoriJquery = get_var('richiestaRilevatoriJquery');

if ($richiestaRilevatoriJquery)
{
	$progressivo = get_var('Ril_Progr');
	
	$text2rilevatoreINGLESE = get_var('text2rilevatoreINGLESE');
	$text2rilevatoreTEDESCO = get_var('text2rilevatoreTEDESCO');
	$text2rilevatoreSPAGNOLO = get_var('text2rilevatoreSPAGNOLO');
	$text2rilevatoreFRANCESE = get_var('text2rilevatoreFRANCESE');
	
	$myRilev = new targhe_estere_rilevatori_velocita($progressivo);
	
	foreach ($_POST as $key => $value)
	{
		if ($key != "c" && 
			$key != "richiestaRilevatoriJquery" && 
			$key != "text2rilevatoreINGLESE" && 
			$key != "text2rilevatoreTEDESCO" && 
			$key != "text2rilevatoreSPAGNOLO" && 
			$key != "text2rilevatoreFRANCESE")
		{
			$myRilev->$key = $value;
		}
		else if ($key == "c") $myRilev->Ril_Comune = $value;
		else if ($key == "richiestaAccertatoriJquery") {}
		else if ($key == "text2rilevatoreINGLESE") {}
		else if ($key == "text2rilevatoreTEDESCO") {}
		else if ($key == "text2rilevatoreSPAGNOLO") {}
		else if ($key == "text2rilevatoreFRANCESE") {}
	}

	$verif = $myRilev->InsertUpdateRilevatore();

	switch ($verif)
	{
		//case "UNAFIRMA": break;
		//case "ERRORFIRMA": break;
		case "INSERT_OK": break;
		case "INSERT_ERROR": break;
		case "UPDATE_OK": break;
		case "UPDATE_ERROR": break;
		case "MATRICOLASISTEMAPRESENTE": break;
		case "ERRORETOLLERANZA1": break;
		case "ERRORETOLLERANZA2": break;
	}
	
	$myTempTrad = new rilevatori_traduzioni(null); 
	if (!isset($myRilev->Coll_Traduzione_Inglese))
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 2;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else if ($myRilev->Coll_Traduzione_Inglese->ID == "")
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 2;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else $myTempTrad = $myRilev->Coll_Traduzione_Inglese;
	$myTempTrad->Traduzione = $text2rilevatoreINGLESE;
	$myTempTrad->InsertUpdateRilevTraduzioni();
	
	if (!isset($myRilev->Coll_Traduzione_Tedesco))
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 3;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else if ($myRilev->Coll_Traduzione_Tedesco->ID == "")
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 3;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else $myTempTrad = $myRilev->Coll_Traduzione_Tedesco;
	$myTempTrad->Traduzione = $text2rilevatoreTEDESCO;
	$myTempTrad->InsertUpdateRilevTraduzioni();
	
	if (!isset($myRilev->Coll_Traduzione_Spagnolo))
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 4;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else if ($myRilev->Coll_Traduzione_Spagnolo->ID == "")
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 4;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else $myTempTrad = $myRilev->Coll_Traduzione_Spagnolo;
	$myTempTrad->Traduzione = $text2rilevatoreSPAGNOLO;
	$myTempTrad->InsertUpdateRilevTraduzioni();
	
	if (!isset($myRilev->Coll_Traduzione_Francese))
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 5;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else if ($myRilev->Coll_Traduzione_Francese->ID == "")
	{
		$myTempTrad = new rilevatori_traduzioni(null);
		$myTempTrad->Linguaggio = 5;
		$myTempTrad->Riferimento_Rilevatore = $myRilev->Ril_Progr;
	}
	else $myTempTrad = $myRilev->Coll_Traduzione_Francese;
	$myTempTrad->Traduzione = $text2rilevatoreFRANCESE;
	$myTempTrad->InsertUpdateRilevTraduzioni();
	
	$scrittaRilevatore = $myRilev->DescrizioneRilevatore();
	
	echo $verif . "**" . $myRilev->Ril_Progr . "**" . $scrittaRilevatore . "**" . $myRilev->Ril_Velocita;
}

$richiestaTargaJquery = get_var('richiestaTargaJquery');

if ($richiestaTargaJquery)
{
	$targa = get_var('targa');
	$ente = get_var('ente');
	
	$queryNotTarga = "SELECT Utente_ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
	$queryNotTarga .= "WHERE Verbale_ID = Reg_Progr AND ";
	$queryNotTarga .= "Reg_Targa_Veicolo = '$targa' AND ";
	$queryNotTarga .= "Reg_Ente_Per_Richiesta = '$ente' ";
	$queryNotTarga .= "ORDER BY Data_Registrazione DESC ";  //  prendo l'ultimo inserito (in caso di più verbali)
	$resNotTarga = mysql_query($queryNotTarga);
	$rigaNotTarga = mysql_fetch_assoc($resNotTarga);
	if ($rigaNotTarga['Utente_ID'] != "")
	{
		$queryUtente = "SELECT * FROM targhe_estere_utenti WHERE ID = '" . $rigaNotTarga['Utente_ID'] . "' "; //AND CC_Comune = '" . $c . "'";
		$resultUt = mysql_query($queryUtente);		
		$risultato = mysql_fetch_assoc($resultUt);
		$risposta = $risultato['ID'] . "**";
		$risposta .= $risultato['Cognome'] . "**";
		$risposta .= $risultato['Nome'] . "**";
		$risposta .= $risultato['Indirizzo1'] . "**";
		$risposta .= $risultato['Indirizzo2'] . "**";
		$risposta .= $risultato['Indirizzo3'] . "**";
		$risposta .= $risultato['Indirizzo4'] . "**";
		$risposta .= $risultato['Indirizzo5'] . "**";
		$risposta .= $risultato['Indirizzo6'] . "**";
		$risposta .= $risultato['Genere'] . "**";
		$risposta .= from_mysql_date($risultato['Data_Nascita']) . "**";
		$risposta .= $risultato['Comune_Nascita'] . "**";
		echo $risposta;
	}
	
}


/*

CREATE TABLE `targhe_estere_enti_distinte` (
		`Dis_Progr` int(11) unsigned NOT NULL auto_increment,
		`Dis_Nazione_Num` int(11) NOT NULL default '0',
		`Dis_Nazione_Nome` varchar(50) NOT NULL default '',
		`Dis_Indirizzo_Prima_Riga` varchar(200) NOT NULL default '',
		`Dis_Indirizzo_Seconda_Riga` varchar(200) NOT NULL default '',
		`Dis_Indirizzo_Terza_Riga` varchar(200) NOT NULL default '',
		`Dis_Indirizzo_Quarta_Riga` varchar(200) NOT NULL default '',
		`Dis_Linguaggio` int(11) unsigned NOT NULL default '1',
		PRIMARY KEY  (`Dis_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

*/

class targhe_estere_enti_distinte
{
	public $Dis_Progr = NULL;
	public $Dis_Nazione_Num = NULL;
	public $Dis_Nazione_Nome = NULL;
	public $Dis_Indirizzo_Prima_Riga = NULL;
	public $Dis_Indirizzo_Seconda_Riga = NULL;
	public $Dis_Indirizzo_Terza_Riga = NULL;
	public $Dis_Indirizzo_Quarta_Riga = NULL;
	public $Dis_Linguaggio = NULL;

	public $Coll_Distinta_Lingua = NULL;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM targhe_estere_enti_distinte WHERE Dis_Progr = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaZone = mysql_fetch_assoc($result);

		foreach ($rigaZone as $key => $value)
		{
			$this->$key = /*utf8_decode*/($value);
		}

		$this->Coll_Distinta_Lingua = new targhe_estere_lista_lingue($this->Dis_Linguaggio);
	}
	
	public function IndirizzoUnicaRiga ()
	{
		$indirizzo = $this->Dis_Indirizzo_Prima_Riga;
		if ($this->Dis_Indirizzo_Seconda_Riga != "") $indirizzo .= " " . $this->Dis_Indirizzo_Seconda_Riga;
		if ($this->Dis_Indirizzo_Terza_Riga != "") $indirizzo .= " " . $this->Dis_Indirizzo_Terza_Riga;
		if ($this->Dis_Indirizzo_Quarta_Riga != "") $indirizzo .= " " . $this->Dis_Indirizzo_Quarta_Riga;
		return $indirizzo;
	}

	/*function CercaZonaNazione ($nazione)  //  prendo la prima (se ce ne sono di più)
	{
		$nazione = strtolower($nazione);
		$nazioneMaiuscolo = strtoupper($nazione);
		$nazionePrimaMaiuscola = ucfirst($nazione);
		$queryNazione = "SELECT Dis_Progr FROM targhe_estere_enti_distinte ";
		$queryNazione .= "WHERE Dis_Nazione_Nome LIKE '%" . $nazione . "%' ";
		$queryNazione .= "OR Dis_Nazione_Nome LIKE '%" . $nazioneMaiuscolo . "%' ";
		$queryNazione .= "OR Dis_Nazione_Nome LIKE '%" . $nazionePrimaMaiuscola . "%' ";
		$resNazione = mysql_query($queryNazione);
		$rigaNazione = mysql_fetch_assoc($resNazione);
		//echo "<br>$queryNazione     $rigaNazione[Dis_Progr]";
		return $rigaNazione['Dis_Progr'];
	}*/

	function SettaStatoNazione ()
	{
		$queryCerca = "SELECT Dis_Nazione_Num FROM targhe_estere_enti_distinte ";
		$queryCerca .= "WHERE Dis_Nazione_Nome = '" . $this->Dis_Nazione_Nome . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		if ($rigaCerca['Dis_Nazione_Num'] != NULL)
			return $rigaCerca['Dis_Nazione_Num'];
		else
		{
			$query2Cerca = "SELECT MAX(Dis_Nazione_Num) as MAX FROM targhe_estere_enti_distinte ";
			$res2Cerca = mysql_query($query2Cerca);
			$riga2Cerca = mysql_fetch_assoc($res2Cerca);
			return ($riga2Cerca['MAX'] + 1);
		}
	}

	function EnteGiaPresente ()
	{
		$queryCerca = "SELECT Dis_Progr FROM targhe_estere_enti_distinte ";
		$queryCerca .= "WHERE Dis_Nazione_Nome = '" . $this->Dis_Nazione_Nome . "' ";
		$queryCerca .= "AND Dis_Regione = '" . $this->Dis_Regione . "' ";
		$queryCerca .= "AND Dis_Indirizzo_Prima_Riga = '" . $this->Dis_Indirizzo_Prima_Riga . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Dis_Progr'];
	}
	

	function InsertUpdateEnteDistinta ($forzoInUpd = null)  //  INSERT o UPDATE
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		if ($forzoInUpd != "")
		{
			$insUpd = $forzoInUpd;
		}
		else if ($this->Dis_Progr == NULL)
		{
			$insUpd = "INSERT";
			$this->Dis_Progr = $this->EnteGiaPresente();
			$this->Dis_Nazione_Num = $this->SettaStatoNazione();
			if ($this->Dis_Progr == NULL)
			{
				$queryUpd = "SELECT Dis_Progr FROM targhe_estere_enti_distinte WHERE Dis_Progr = '$this->Dis_Progr'";
				$resUpd = mysql_query($queryUpd);
				$rigaUpd = mysql_fetch_assoc($resUpd);
				if ($rigaUpd['Dis_Progr'] == NULL)
					$insUpd = "INSERT";
				else
					$insUpd = "UPDATE";
			}
		}
		else
		{
			$this->Dis_Nazione_Num = $this->SettaStatoNazione();
			$insUpd = "UPDATE";
		}

		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "Dis_Progr" && 
				$campo != "Coll_Distinta_Lingua")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}

		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_distinta_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_distinta_locale($this->Dis_Progr, $fields, $values);
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

	public function insert_distinta_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_enti_distinte (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";

		return mysql_query($query);

		echo $query;

		return true;
	}

	public function update_distinta_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);

		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;

		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;

		$myOldDist = new targhe_estere_enti_distinte($key);

		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldDist->$fields_to_update[$i] != $values_to_update[$i])
			{
				if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali

		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "

		$query = "UPDATE targhe_estere_enti_distinte SET $clause WHERE Dis_Progr = '" . $key . "'";

		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;

		echo $query;

		return true;
	}
}

/*
 * CREATE TABLE `targhe_estere_codici_ente_comune` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Data_Validita` date NOT NULL DEFAULT '0000-00-00',
  `Ente` int(11) NOT NULL DEFAULT '0',
  `Comune` varchar(5) NOT NULL DEFAULT '',
  `Codice` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class targhe_estere_codici_ente_comune
{
	public $ID;
	public $Data_Validita;
	public $Ente;
	public $Comune;
	public $Codice;
	
	public function __construct( $progr = 0 )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM targhe_estere_codici_ente_comune WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaSpese = mysql_fetch_assoc($result);
		//echo "<br>" . $query;
	
		if (mysql_num_rows($result) > 0)
		{
			foreach ($rigaSpese as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	public function CodiceGiaPresente ($comune, $ente)
	{
		$queryCerca = "SELECT ID FROM targhe_estere_codici_ente_comune ";
		$queryCerca .= "WHERE Comune = '" . $comune . "' ";
		$queryCerca .= "AND Ente = '" . $ente . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		//echo "<br>" . $queryCerca . " -> " . $rigaCerca['ID'];
		return $rigaCerca['ID'];
	}
	
	public function InsertUpdateCodiceComune ($forzoInsertUpdate = NULL)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
	
		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else
		{
			$this->ID = $this->CodiceGiaPresente($this->Comune, $this->Ente);
	
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM targhe_estere_codici_ente_comune WHERE ID = '$this->ID'";
				$resUpd = mysql_query($queryUpd);
				$rigaUpd = mysql_fetch_assoc($resUpd);
				//alert ($queryUpd);
				if ($rigaUpd['ID'] == NULL)
					$insUpd = "INSERT";
				else
					$insUpd = "UPDATE";
			}
		}
		/*alert ($insUpd);
		 return;*/
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "ID")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
	
		$risposta = "";
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_codici_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_codici_locale($this->ID, $fields, $values);
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
	
	public function insert_codici_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_codici_ente_comune (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
		
		return mysql_query($query);
		
		echo "<br>" . $query;
		
		return true;
	}
	
	public function update_codici_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldCodice = new targhe_estere_codici_ente_comune($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldCodice->$fields_to_update[$i] != $values_to_update[$i])
			{
				$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
			}
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE targhe_estere_codici_ente_comune SET $clause WHERE ID = '" . $key . "'";
		
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
		
		echo "<br>" . $query;
		
		return true;
	}
}


/*
 * 
 * CREATE TABLE `targhe_estere_solleciti` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Registro_Provenienza` int(11) unsigned NOT NULL,
  `Tipo_Sollecito` varchar(20) DEFAULT NULL,
  `Numero_Progressivo_Sollecito` int(5) unsigned NOT NULL,
  `Data_Elaborazione_Sollecito` date DEFAULT '0000-00-00',
  `Data_Stampa_Sollecito` date DEFAULT '0000-00-00',
  `Numero_Stampa_Comune` int(10) unsigned NOT NULL,
  `Data_Flusso_Sollecito` date DEFAULT '0000-00-00',
  `Numero_Flusso` int(10) unsigned NOT NULL,
  `Aumento_Spese_Sollecito` decimal( 10, 2 ) NOT NULL DEFAULT '0',
  `Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
  `Operatore` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class targhe_estere_solleciti
{
	public $ID;
	public $Registro_Provenienza;
	public $Tipo_Sollecito;
	public $Numero_Progressivo_Sollecito;
	public $Data_Elaborazione_Sollecito;
	public $Data_Stampa_Sollecito;
	public $Numero_Stampa_Comune;
	public $Data_Flusso_Sollecito;
	public $Numero_Flusso;
	public $Aumento_Spese_Sollecito;
	public $Data_Registrazione;
	public $Operatore;
	
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM targhe_estere_solleciti WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaSollecito = mysql_fetch_assoc($result);
	
		foreach ($rigaSollecito as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	function AutoGenerazioneTable ()
	{
		$tables = mysql_list_tables ("gitco2");
		while (list ($temp) = mysql_fetch_array ($tables))
		{
			//echo "<br>" . $temp;
			if ($temp == "targhe_estere_solleciti")
			{
				//echo " c'è " . $temp;
				return;
			}
		}
		
		$queryCreate = "
			CREATE TABLE `targhe_estere_solleciti` (
			  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `Registro_Provenienza` int(11) unsigned NOT NULL,
			  `Tipo_Sollecito` varchar(20) DEFAULT NULL,
			  `Numero_Progressivo_Sollecito` int(5) unsigned NOT NULL,
			  `Data_Elaborazione_Sollecito` date DEFAULT '0000-00-00',
			  `Data_Stampa_Sollecito` date DEFAULT '0000-00-00',
			  `Numero_Stampa_Comune` int(10) unsigned NOT NULL,
			  `Data_Flusso_Sollecito` date DEFAULT '0000-00-00',
			  `Numero_Flusso` int(10) unsigned NOT NULL,
			  `Aumento_Spese_Sollecito` decimal( 10, 2 ) NOT NULL DEFAULT '0',
			  `Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
			  `Operatore` varchar(30) NOT NULL DEFAULT '',
			  PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		";
		safe_query($queryCreate);
	}
	
	public function TrovaProssimoNumStampaSollecitoEstero ($anno)
	{
		$queryNumStampa = "SELECT max(Numero_Stampa_Comune) as MAX ";
		$queryNumStampa .= " FROM targhe_estere_solleciti, registro_cronologico_cds ";
		$queryNumStampa .= " WHERE Reg_Anno = " . $anno;
		$queryNumStampa .= " AND Reg_Progr = Registro_Provenienza ";
		$resNumStampa = mysql_query($queryNumStampa);
		$rigaNumStampa = mysql_fetch_assoc($resNumStampa);
		$prossimo = $rigaNumStampa['MAX'] + 1;
		$this->Numero_Stampa_Comune = $prossimo;
		return $prossimo;
	}
	
	function SollecitoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_solleciti ";
		$queryCerca .= "WHERE Registro_Provenienza = '" . $this->Registro_Provenienza . "' ";
		$queryCerca .= "AND Tipo_Sollecito = '" . $this->Tipo_Sollecito . "' ";
		$queryCerca .= "AND Numero_Progressivo_Sollecito = '" . $this->Numero_Progressivo_Sollecito . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function TrovaUltimoSollecito ($registroProvenienza, $tipoSollecito)
	{
		$queryCerca = "SELECT MAX(Numero_Progressivo_Sollecito) as ULTIMO FROM targhe_estere_solleciti ";
		$queryCerca .= "WHERE Registro_Provenienza = '" . $registroProvenienza . "' ";
		$queryCerca .= "AND Tipo_Sollecito = '" . $tipoSollecito . "' ";
		$resCerca = mysql_query($queryCerca);
		//echo "<br>" . $queryCerca;
		if (numero_risposte_query($resCerca) != 0)
		{
			$rigaCerca = mysql_fetch_assoc($resCerca);
			$ultimo = $rigaCerca['ULTIMO'];
		}
		else 
		{
			$ultimo = 0;
		}
		return $ultimo;
	}
	
	function NumeroProssimoSollecito ($registroProvenienza, $tipoSollecito)
	{
		$attuale = $this->TrovaUltimoSollecito($registroProvenienza, $tipoSollecito);
		return ($attuale+1);
	}
	
	function SollecitoDaVerbale ($registroProvenienza, $tipoSollecito)
	{
		$queryCerca = "SELECT ID FROM targhe_estere_solleciti ";
		$queryCerca .= "WHERE Registro_Provenienza = '" . $registroProvenienza . "' ";
		$queryCerca .= "AND Tipo_Sollecito = '" . $tipoSollecito . "' ";
		$resCerca = mysql_query($queryCerca);
		$numCerca = mysql_num_rows($resCerca);
		
		if ($numCerca != 0)
		{
			$arrayRisposta = array();
			while ($rigaCerca = mysql_fetch_assoc($resCerca))
			{
				$arrayRisposta[] = $rigaCerca['ID'];
			}
			return $arrayRisposta;
		}
		
		return null;
	}
	
	function LinkSollecitoPdf ($PathSollecitoEsteri, $comune, $anno, $progressivo)
	{
		$linkVerbale = $PathSollecitoEsteri . "Definitivi/";
		$linkVerbale .= "Sollecito_Definitivo_";
		$linkVerbale .= $comune . "_";
		$linkVerbale .= $anno . "_";
		$linkVerbale .= $progressivo . "_";
		$linkVerbale .= $this->Data_Stampa_Sollecito . ".pdf";
		return $linkVerbale;
	}
	
	function LinkSollecitoComunePdf ($PathSollecitoComuneEsteri, $comune, $anno, $progressivo)
	{
		$linkVerbale = $PathSollecitoComuneEsteri;
		$linkVerbale .= "Solleciti_Definitivi_";
		$linkVerbale .= $comune . "_";
		$linkVerbale .= $anno . "_";
		$linkVerbale .= $this->Numero_Stampa_Comune . "_";
		$linkVerbale .= $this->Data_Stampa_Sollecito . ".pdf";
		return $linkVerbale;
	}
	
	function PrimoUltimoSollecitoInStampaComune ($anno)
	{
		$query = "SELECT MAX(Reg_Progr_Registro) as MAX, MIN(Reg_Progr_Registro) as MIN ";
		$query .= "FROM targhe_estere_solleciti, registro_cronologico_cds ";
		$query .= "WHERE Registro_Provenienza = Reg_Progr AND ";
		$query .= "Numero_Stampa_Comune = " . $this->Numero_Stampa_Comune . " AND ";
		$query .= "Reg_Anno = " . $anno;
		$resMaxMin = mysql_query($query);
		$rigaMaxMin = mysql_fetch_assoc($resMaxMin);
		$arrayMaxMin = array($rigaMaxMin['MIN'] . "/" . $anno, $rigaMaxMin['MAX'] . "/" . $anno);
		return $arrayMaxMin;
	}
	
	public function InsertUpdateSollecito ($forzoInsertUpdate = NULL)  //  INSERT o UPDATE
	{
		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else if ($this->ID == NULL)
		{
			$temp = $this->SollecitoGiaPresente();
			if ($temp == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$this->ID = $temp;
				$insUpd = "UPDATE";
			}
		}
		else $insUpd = "UPDATE";

		$this->Data_Registrazione = date("Y-m-d");
		$this->Operatore = $_SESSION['username'];
		
		$fields = array();
		$values = array();

		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "ID")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		
		if ($insUpd == "INSERT")
		{
			$verif = $this->insert_sollecito_locale($fields, $values);
			$this->ID = $this->SollecitoGiaPresente();
			if ($verif == true)
				$risp = "INSERT**OK**" . $this->ID;
			else
				$risp = "INSERT**ERRORE**" . $this->ID;
		}
		else if ($insUpd == "UPDATE")
		{
			$verif = $this->update_sollecito_locale($this->ID, $fields, $values);
			if ($verif == true)
				$risp = "UPDATE**OK**" . $this->ID;
			else
				$risp = "UPDATE**ERRORE**" . $this->ID;
		}
		
		return $risp;
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
		$query = "INSERT INTO targhe_estere_solleciti (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";		
	
		//$_SESSION['CC_User'] != "***+"
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
	
	public function update_sollecito_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldSollecito = new targhe_estere_solleciti($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldSollecito->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_solleciti SET $clause WHERE ID = '" . $key . "'";
	
	// tutti dati uguali: update inutile 8cambia solo la data di oggi!
		$nonUpd = "UPDATE targhe_estere_solleciti SET Data_Registrazione";
		if (substr($query, 0, strlen($nonUpd)) == $nonUpd)
			return true;
		
		//$_SESSION['CC_User'] != "***+"
		if (1)
		{
			if (safe_query($query) != NULL) return TRUE;
			else return FALSE;
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
}