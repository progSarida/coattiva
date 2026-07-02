<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include_once CLASSI . "/notifiche_importate.php";

//*****************************************************************
// Classe targhe_estere_utenti; 	                           	  *
// Crea un oggetto utente con i dati relativi                     *
//*****************************************************************

/*
 * CREATE TABLE `targhe_estere_utenti` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Comune_ID` int(10) unsigned NOT NULL,
  `CC_Comune` varchar(5) DEFAULT NULL,
  `Genere` enum('D','F','M') NOT NULL COMMENT 'D = DITTA, F = FEMMINA, M = MASCHIO',
  `Cognome` varchar(100) NOT NULL DEFAULT '',
  `Nome` varchar(100) NOT NULL DEFAULT '',
  `Indirizzo1` longtext NOT NULL DEFAULT '',
  `Indirizzo2` longtext NOT NULL DEFAULT '',
  `Indirizzo3` longtext NOT NULL DEFAULT '',
  `Indirizzo4` longtext NOT NULL DEFAULT '',
  `Indirizzo5` longtext NOT NULL DEFAULT '',
  `Indirizzo6` longtext NOT NULL DEFAULT '',
  `CC_Nascita` char(4) DEFAULT '',
  `Paese_Nascita` varchar(100) NOT NULL DEFAULT '',
  `Comune_Nascita` varchar(100) NOT NULL DEFAULT '',
  `Provincia_Nascita` char(2) NOT NULL DEFAULT '',
  `Data_Nascita` date DEFAULT NULL,
  `Data_Morte` date DEFAULT NULL,
  `Codice_Fiscale` varchar(16) NOT NULL DEFAULT '',
  `Ditta` varchar(100) NOT NULL,
  `Partita_Iva` varchar(11) NOT NULL DEFAULT '',
  `Prec_Denom` varchar(100) NOT NULL,
  `Anno_Cambio_Denom` int(4) DEFAULT NULL,
  `Note` varchar(400) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `Cellulare` varchar(20) DEFAULT NULL,
  `Mail` varchar(50) NOT NULL,
  `PEC` varchar(50) NOT NULL DEFAULT '',
  `Zona_Spese_Postali` int(4) DEFAULT NULL,
  `Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
 */

class targhe_estere_utenti
{	
	public $ID;
	public $CC_Comune;
	public $Comune_ID;
	public $Genere;
	public $Cognome;
	public $Nome;
	public $Indirizzo1;
	public $Indirizzo2;
	public $Indirizzo3;
	public $Indirizzo4;
	public $Indirizzo5;
	public $Indirizzo6;
	public $CC_Nascita;
	public $Paese_Nascita;
	public $Comune_Nascita;
	public $Provincia_Nascita;
	public $Data_Nascita;
	public $Data_Morte;
	public $Codice_Fiscale;
	public $Ditta;
	public $Partita_Iva;
	public $Prec_Denom;
	public $Anno_Cambio_Denom;
	public $Note;
	public $Cellulare;
	public $Mail;
	public $PEC;
	public $Zona_Spese_Postali;
	public $Data_Registrazione;
	
	public $next;
	public $prev;
	
	public function __construct( $progr = 0 , $c = NULL )
	{
		if ($progr == NULL) return;
			
		$query = "SELECT * FROM targhe_estere_utenti WHERE ID = '" . $progr . "' "; //AND CC_Comune = '" . $c . "'";
		$result = safe_query($query);		
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->CC_Comune = utf8_decode($val['CC_Comune']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->Genere = utf8_decode($val['Genere']);
		$this->Cognome = utf8_decode($val['Cognome']);
		$this->Nome = utf8_decode($val['Nome']);
		$this->Indirizzo1 = utf8_decode($val['Indirizzo1']);
		$this->Indirizzo2 = utf8_decode($val['Indirizzo2']);
		$this->Indirizzo3 = utf8_decode($val['Indirizzo3']);
		$this->Indirizzo4 = utf8_decode($val['Indirizzo4']);
		$this->Indirizzo5 = utf8_decode($val['Indirizzo5']);
		$this->Indirizzo6 = utf8_decode($val['Indirizzo6']);
		$this->CC_Nascita = utf8_decode($val['CC_Nascita']);
		$this->Paese_Nascita = utf8_decode($val['Paese_Nascita']);
		$this->Comune_Nascita = utf8_decode($val['Comune_Nascita']);
		$this->Provincia_Nascita = utf8_decode($val['Provincia_Nascita']);
		$this->Data_Nascita = utf8_decode($val['Data_Nascita']);
		$this->Data_Morte = utf8_decode($val['Data_Morte']);
		$this->Codice_Fiscale = utf8_decode($val['Codice_Fiscale']);
		$this->Ditta = utf8_decode($val['Ditta']);
		$this->Partita_Iva = utf8_decode($val['Partita_Iva']);
		$this->Prec_Denom = utf8_decode($val['Prec_Denom']);
		$this->Anno_Cambio_Denom = utf8_decode($val['Anno_Cambio_Denom']);
		$this->Note = utf8_decode($val['Note']);
		$this->Cellulare = utf8_decode($val['Cellulare']);
		$this->Mail = utf8_decode($val['Mail']);
		$this->PEC = utf8_decode($val['PEC']);
		$this->Zona_Spese_Postali = utf8_decode($val['Zona_Spese_Postali']);
		$this->Data_Registrazione = utf8_decode($val['Data_Registrazione']);
		
		// assegna un valore ai puntatori $prev e $next:
		// se progr=0 (nuovo inserimento) prev punta all'ultimo e next punta al primo
		if ($progr==0)
		{
			$query = "SELECT * FROM targhe_estere_utenti where CC_Comune='$c' 
						ORDER BY ID ASC LIMIT 1";
			$this->next = single_answer_query($query);
		
			$query = "SELECT * FROM targhe_estere_utenti WHERE CC_Comune='$c'
						ORDER BY ID DESC LIMIT 1";
			$this->prev = single_answer_query($query);
		}
		else
		{
			$query = "SELECT * FROM targhe_estere_utenti
						WHERE ( (ID>'$this->ID') AND (CC_Comune='$c') )
						ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
		
			$query = "SELECT * FROM targhe_estere_utenti
						WHERE ( (ID<'$this->ID') AND (CC_Comune='$c') )
						ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		
	}
	
	function CorreggiUtente ()
	{
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "next" && 
				$campo != "prev")
			{
				$valore = str_replace("'", "", $valore);
				$this->$campo = strtoupper($valore);
			}
		}
	}
	
	function UtenteGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_utenti ";
		if ($this->Genere == "M" || $this->Genere == "F")
		{
			$queryCerca .= "WHERE Cognome = '" . $this->Cognome . "' ";
			$queryCerca .= "AND Nome = '" . $this->Nome . "' ";
			if ($this->Data_Nascita != "" && $this->Data_Nascita != "0000-00-00")
				$queryCerca .= "AND Data_Nascita = '" . $this->Data_Nascita . "' ";
			//$queryCerca .= "AND Indirizzo1 = '" . $this->Indirizzo1 . "'";
		}
		else if ($this->Genere == "D")
		{
			$queryCerca .= "WHERE Cognome = '" . $this->Cognome . "' ";
			$queryCerca .= "AND Indirizzo1 = '" . $this->Indirizzo1 . "'";
		}
		
		$resCerca = mysql_query($queryCerca);
		//echo ("<br>" . $queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function CognomeNomeCompleto ()
	{
		$completo = $this->Cognome;
		if ($this->Nome != "") $completo .= " " . $this->Nome;
		return $completo;
	}
	
	function ResidenzaCompleta ()
	{
		$completo = $this->Indirizzo1;
		if ($this->Indirizzo2 != "") $completo .= " " . $this->Indirizzo2;
		if ($this->Indirizzo3 != "") $completo .= " " . $this->Indirizzo3;
		if ($this->Indirizzo4 != "") $completo .= " " . $this->Indirizzo4;
		if ($this->Indirizzo5 != "") $completo .= " " . $this->Indirizzo5;
		if ($this->Indirizzo6 != "") $completo .= " " . $this->Indirizzo6;
		return $completo;
	}
	
	function ResidenzaCompletaSuDueRighe ()
	{
		$array = array();
		if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "" && 
			$this->Indirizzo4 != "" && $this->Indirizzo5 != "" && $this->Indirizzo6 != "")
		{
			$array[0] = $this->Indirizzo1 . " " . $this->Indirizzo2 . " " . $this->Indirizzo3;
			$array[1] = $this->Indirizzo4 . " " . $this->Indirizzo5 . " " . $this->Indirizzo6;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "" && 
			$this->Indirizzo4 != "" && $this->Indirizzo5 != "")
		{
			$array[0] = $this->Indirizzo1 . " " . $this->Indirizzo2 . " " . $this->Indirizzo3;
			$array[1] = $this->Indirizzo4 . " " . $this->Indirizzo5;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "" && 
			$this->Indirizzo4 != "")
		{
			$array[0] = $this->Indirizzo1 . " " . $this->Indirizzo2;
			$array[1] = $this->Indirizzo3 . " " . $this->Indirizzo4;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "")
		{
			$array[0] = $this->Indirizzo1 . " " . $this->Indirizzo2;
			$array[1] = $this->Indirizzo3;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "")
		{
			$array[0] = $this->Indirizzo1;
			$array[1] = $this->Indirizzo2;
		}
		else
		{
			$array[0] = $this->Indirizzo1;
			$array[1] = "";
		}
		return $array;
	}
	
	function ResidenzaCompletaSuTreRighe ()
	{
		$array = array();
		if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "" && 
			$this->Indirizzo4 != "" && $this->Indirizzo5 != "" && $this->Indirizzo6 != "")
		{
			$array[0] = $this->Indirizzo1 . " " . $this->Indirizzo2;
			$array[1] = $this->Indirizzo3 . " " . $this->Indirizzo4;
			$array[2] = $this->Indirizzo5 . " " . $this->Indirizzo6;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "" && 
			$this->Indirizzo4 != "" && $this->Indirizzo5 != "")
		{
			$array[0] = $this->Indirizzo1 . " " . $this->Indirizzo2;
			$array[1] = $this->Indirizzo3 . " " . $this->Indirizzo4;
			$array[2] = $this->Indirizzo5;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "" && 
			$this->Indirizzo4 != "")
		{
			$array[0] = $this->Indirizzo1 . " " . $this->Indirizzo2;
			$array[1] = $this->Indirizzo3;
			$array[2] = $this->Indirizzo4;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "" && $this->Indirizzo3 != "")
		{
			$array[0] = $this->Indirizzo1;
			$array[1] = $this->Indirizzo2;
			$array[2] = $this->Indirizzo3;
		}
		else if ($this->Indirizzo1 != "" && $this->Indirizzo2 != "")
		{
			$array[0] = $this->Indirizzo1;
			$array[1] = $this->Indirizzo2;
			$array[2] = "";
		}
		else
		{
			$array[0] = $this->Indirizzo1;
			$array[1] = "";
			$array[2] = "";
		}
		return $array;
	}
	
	function InsertUpdateUtenteEstero ($forzoUpdate = NULL)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		$this->CorreggiUtente();
		
		$this->Data_Registrazione = date("Y-m-d");
		
		if ($forzoUpdate != NULL)
		{
			$insUpd = "UPDATE";
		}
		else 
		{
			$this->ID = $this->UtenteGiaPresente();
			if ($this->ID == NULL)
			{
				/*$queryMax = "SELECT MAX(ID) as mioId FROM targhe_estere_utenti";
				$resMax = mysql_query($queryMax);
				$rigaMax = mysql_fetch_assoc($resMax);
				$this->ID = $rigaMax['mioId'];*/
				$insUpd = "INSERT";
			}
			else 
			{
				$queryUpd = "SELECT ID FROM targhe_estere_utenti WHERE ID = '$this->ID'";
				$resUpd = mysql_query($queryUpd);
				$rigaUpd = mysql_fetch_assoc($resUpd);
				if ($rigaUpd['ID'] == NULL)
					$insUpd = "INSERT";
				else
					$insUpd = "UPDATE";
			}
		}
		
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "ID" && 
				$campo != "next" && 
				$campo != "prev")
			{
				$fields[] = $campo;
				$values[] = trim(strtoupper($valore));
			}
		}
		
		$risposta = "";
		if ($insUpd == "INSERT") $risposta = $this->insert_utente_locale($fields, $values);
		else if ($insUpd == "UPDATE") $risposta = $this->update_utente_locale($this->ID, $this->CC_Comune, $fields, $values);
		//alert ($risposta . " e " . $insUpd);
		return $risposta;
	}
	
	public function insert_utente_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_utenti (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return safe_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_utente_locale($key, $c, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldUtente = new targhe_estere_utenti($key, $c);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldUtente->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				//if (isset($fields_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldUtente->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_utenti SET $clause WHERE ID = '" . $key . "'";
	
		if (safe_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
	
	function InsertUpdateProvaUtenteEstero ($forzoUpdate = NULL)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		$this->CorreggiUtente();
		
		$this->Data_Registrazione = date("Y-m-d");
		
		if ($forzoUpdate != NULL)
		{
			$insUpd = "UPDATE";
		}
		else 
		{
			$this->ID = $this->UtenteGiaPresente();
			if ($this->ID == NULL)
			{
				/*$queryMax = "SELECT MAX(ID) as mioId FROM targhe_estere_utenti";
				$resMax = mysql_query($queryMax);
				$rigaMax = mysql_fetch_assoc($resMax);
				$this->ID = $rigaMax['mioId'];*/
				$insUpd = "INSERT";
			}
			else 
			{
				$queryUpd = "SELECT ID FROM targhe_estere_utenti WHERE ID = '$this->ID'";
				$resUpd = mysql_query($queryUpd);
				$rigaUpd = mysql_fetch_assoc($resUpd);
				if ($rigaUpd['ID'] == NULL)
					$insUpd = "INSERT";
				else
					$insUpd = "UPDATE";
			}
		}
		
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && 
				$campo != "ID" && 
				$campo != "next" && 
				$campo != "prev")
			{
				$fields[] = $campo;
				$values[] = trim(strtoupper($valore));
			}
		}
		
		$risposta = "";
		if ($insUpd == "INSERT") $risposta = $this->insert_utente_prova_locale($fields, $values);
		else if ($insUpd == "UPDATE") $risposta = $this->update_utente_prova_locale($this->ID, $this->CC_Comune, $fields, $values);
		//alert ($risposta . " e " . $insUpd);
		return $risposta;
	}
	
	public function insert_utente_prova_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		echo "<br><br>INSERT targhe_estere_utenti";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO targhe_estere_utenti (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
			echo "<br>" . $fields_to_insert[$i] . "='" .$values_to_insert[$i]. "' ";
		}
		$query .= $clause . ")";
	
		/*return safe_query($query);
	
		echo $query;*/
	
		return true;
	}
	
	public function update_utente_prova_locale($key, $c, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldUtente = new targhe_estere_utenti($key, $c);
		
		$clause = "";
		echo "<br><br>UPDATE targhe_estere_utenti";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldUtente->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				//if (isset($fields_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
					echo "<br>" . $fields_to_update[$i] . "='" .$values_to_update[$i] . "'   (" . $myOldUtente->$fields_to_update[$i] . ")";
				}
			}
			//echo ("<br>" . $myOldUtente->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_utenti SET $clause WHERE ID = '" . $key . "'";
	
		/*if (safe_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;*/
	
		return true;
	}
	
}

/*
 * CREATE TABLE `targhe_estere_dati_utenti` (
 		`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 		`ID_Utente` int(10) unsigned NOT NULL,
 		`ID_Notifica` int(10) unsigned NOT NULL,
 		`Numero_Patente` varchar(50) NOT NULL DEFAULT '',
 		`Stato_Patente` varchar(50) NOT NULL DEFAULT '',
 		`Data_Rilascio_Patente` date NOT NULL DEFAULT '0000-00-00',
 		`Categoria_Patente` varchar(50) NOT NULL DEFAULT '',
 		`Autorita_Patente` varchar(50) NOT NULL DEFAULT '',
 		`Note` text,
 		`Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
  		`Operatore` varchar(30) NOT NULL DEFAULT '',
 		PRIMARY KEY (`ID`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8
*/

class targhe_estere_dati_utenti
{
	public $ID;
	public $ID_Utente;
	public $ID_Notifica;
	public $Numero_Patente;
	public $Stato_Patente;
	public $Data_Rilascio_Patente;
	public $Categoria_Patente;
	public $Autorita_Patente;
	public $Note;
	public $Data_Comunicazione_Dati;
	public $Data_Registrazione;
	public $Operatore;
	
	public function __construct($progr, $utenteId = NULL, $notificaId = NULL)
	{
		if ($progr == NULL && $utenteId == NULL && $notificaId == NULL) return;
		
		if ($progr != NULL)
		{
			$querySel = "SELECT * FROM targhe_estere_dati_utenti WHERE ID = $progr";
		}
		else if ($utenteId != NULL && $notificaId != NULL)
		{
			$querySel = "SELECT * FROM targhe_estere_dati_utenti WHERE ID_Utente = $utenteId";
			$querySel .= " AND ID_Notifica = $notificaId";
		}
		else return;
		//echo "<br>$querySel<br>";
		$resSel = mysql_query($querySel);
		
		if (mysql_num_rows($resSel) == 0) return null;
		
		$rigaSel = mysql_fetch_assoc($resSel);
	
		foreach ($rigaSel as $key => $value)
		{
			$this->$key = /*utf8_decode(*/$value;//);
		}
	}
	
	public function DatoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_dati_utenti ";
		$queryCerca .= "WHERE ID_Utente = '" . $this->ID_Utente . "' ";
		$queryCerca .= "AND ID_Notifica = '" . $this->ID_Notifica . "' ";
		//echo "<br>" . $queryCerca;
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	public function DatoUtenteDaNotifica ($notifica)
	{
		$queryCerca = "SELECT ID FROM targhe_estere_dati_utenti ";
		$queryCerca .= "WHERE ID_Notifica = '" . $notifica . "' ";
		//echo "<br>" . $queryCerca;
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	public function InsertUpdateDatiUtente ($forzoInsertUpdate = NULL)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
	
		$this->Data_Registrazione = date("Y-m-d");
		$this->Operatore = $_SESSION['username'];
	
		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else
		{
			$this->ID = $this->DatoGiaPresente();
	
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM targhe_estere_dati_utenti WHERE ID = '$this->ID'";
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
			$risposta = $this->insert_dato_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_dato_locale($this->ID, $fields, $values);
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
	
	public function insert_dato_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_dati_utenti (" . $clause . ") VALUES (";
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
	
	public function update_dato_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldDato = new targhe_estere_dati_utenti($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			//echo "<br>" . $myOldUtente->$fields_to_update[$i] . "         $fields_to_update[$i]  -->  $values_to_update[$i]";
			if ($myOldDato->$fields_to_update[$i] != $values_to_update[$i])
			{
				//echo "<br>" . $myOldUtente->$fields_to_update[$i] . "         $fields_to_update[$i]  -->  $values_to_update[$i]";
				//if ($values_to_update[$i] != NULL)
				//if (isset($fields_to_update[$i]))
				{
					//alert($fields_to_update[$i]);
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE targhe_estere_dati_utenti SET $clause WHERE ID = '" . $key . "'";
		
		// tutti dati uguali: update inutile 8cambia solo la data di oggi!
		$nonUpd = "UPDATE targhe_estere_dati_utenti SET Data_Registrazione";
		if (substr($query, 0, strlen($nonUpd)) == $nonUpd)
		return true;

		if (safe_query($query) != NULL) return TRUE;
		else return FALSE;

		echo "<br>" . $query;

		return true;
	}
}


/*
 * 
 * CREATE TABLE `targhe_estere_notifiche` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Comune_CC` varchar(5) NOT NULL DEFAULT '',
  `Anno` int(5) unsigned NOT NULL DEFAULT '0',
  `Verbale_ID` int(10) unsigned NOT NULL,
  `Utente_ID` int(10) unsigned NOT NULL,
  `Tipo_Trasgressore` varchar(20) NOT NULL DEFAULT '',
  `Spese_Notifiche_Comune` decimal(10, 2) NOT NULL DEFAULT '0',
  `Spese_Ricerche_Comune` decimal(10, 2) NOT NULL DEFAULT '0',
  `Spese_Notifiche_Sarida` decimal(10, 2) NOT NULL DEFAULT '0',
  `Spese_Ricerche_Sarida` decimal(10, 2) NOT NULL DEFAULT '0',
  `Data_Stampa_Notifica` date NOT NULL DEFAULT '0000-00-00',
  `Numero_Stampa_Comune` int(10) unsigned NOT NULL,
  `Data_Creazione_Flusso` date NOT NULL DEFAULT '0000-00-00',
  `Numero_Flusso` int(10) unsigned NOT NULL,
  `Data_Notifica` date NOT NULL DEFAULT '0000-00-00',
  `Esito_Notifica` int(5) unsigned NOT NULL,
  `Esito_Stato_Notifica` int(5) unsigned NOT NULL,
  `Data_Ri_Notifica` date NOT NULL DEFAULT '0000-00-00',
  `Tipo_Ri_Notifica` int(5) unsigned NOT NULL DEFAULT '0',
  `Data_Comunicazione_Noleggio` date NOT NULL DEFAULT '0000-00-00',
  `Data_Registrazione` date NOT NULL DEFAULT '0000-00-00',
  `Operatore` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
 * 
 */

class targhe_estere_notifiche
{
	public $ID;
	public $Comune_CC;  //  lo uso solo come promemoria visiva nella tabella: NON SERVE
	public $Anno;
	public $Verbale_ID;
	public $Utente_ID;
	public $Tipo_Trasgressore;
	public $Spese_Notifiche_Comune;
	public $Spese_Ricerche_Comune;
	public $Spese_Notifiche_Sarida;
	public $Spese_Ricerche_Sarida;
	public $Data_Stampa_Notifica;
	public $Numero_Stampa_Comune;
	public $Data_Creazione_Flusso;
	public $Numero_Flusso;
	public $Data_Notifica;
	public $Esito_Notifica;
	public $Esito_Stato_Notifica;
	public $Data_Ri_Notifica;
	public $Tipo_Ri_Notifica;
	public $Data_Comunicazione_Noleggio;
	public $Data_Registrazione;
	public $Operatore;
	
	public $Coll_Verbale;
	public $Coll_Utente;
	public $Coll_Esito_Notifica;
	public $Coll_Esito_Stato_Notifica;
	public $Coll_Notifica_Importata;
	public $Coll_Tipo_Rinotifica;
	public $Coll_Pagamenti = array();
	public $Coll_Solleciti = array();
	
	public $Coll_Dati_Utente;
	
	public function __construct($progr)
	{
		if ($progr == NULL) return;
		
		$querySel = "SELECT * FROM targhe_estere_notifiche WHERE ID = $progr";
		//echo "<br>$querySel<br>";
		$resSel = mysql_query($querySel);
		$rigaSel = mysql_fetch_assoc($resSel);
		
		foreach ($rigaSel as $key => $value)
		{
			$this->$key = /*utf8_decode(*/$value;//);
		}
		
		$this->Coll_Verbale = new registro_cronologico_cds($this->Verbale_ID);
		$this->Coll_Utente = new targhe_estere_utenti($this->Utente_ID, $this->Coll_Verbale->Reg_Comune_Violazione);
		//$this->Coll_Pagmenti = array();
		
		$this->Coll_Dati_Utente = new targhe_estere_dati_utenti(NULL, $this->Utente_ID, $this->ID);
		
		$this->Coll_Esito_Notifica = new targhe_estere_tipi_ricezione($this->Esito_Notifica);
		$this->Coll_Esito_Stato_Notifica = new targhe_estere_tipi_ricezione($this->Esito_Stato_Notifica);
		
		$temp = new notifiche_importate(null);
		$myImport = $temp->CercaRiferimento($this->Comune_CC, "VERBALIESTERI", $this->ID);
		$this->Coll_Notifica_Importata = new notifiche_importate($myImport);
		
		$this->Coll_Tipo_Rinotifica = new targhe_estere_tipi_rinotifiche($this->Tipo_Ri_Notifica);
		
		$listaPagamenti = new targhe_estere_pagamenti(NULL);
		$arrayPag = $listaPagamenti->PagamentoDaVerbale($this->Verbale_ID);
		//alert (count($arrayPag));
		for ($i = 0; $i < count($arrayPag); $i++)
		{
			$this->Coll_Pagamenti[] = new targhe_estere_pagamenti($arrayPag[$i]);
		}
		
		$listaSolleciti = new targhe_estere_solleciti(NULL);
		$listaSolleciti->AutoGenerazioneTable();
		$arraySol = $listaSolleciti->SollecitoDaVerbale($this->Verbale_ID, "CDS_ESTERO");
		//alert (count($arrayPag));
		$this->Coll_Solleciti = array();
		if (count($arraySol) != 0)
		{
			for ($i = 0; $i < count($arraySol); $i++)
			{
				$this->Coll_Solleciti[] = new targhe_estere_solleciti($arraySol[$i]);
			}
		}
		else $this->Coll_Solleciti[0] = new targhe_estere_solleciti(null);
	}
	
	public function StatiNotifica ()
	{
		switch ($this->Tipo_Trasgressore)
		{
			case "COINCIDENTE": break;  //  dopo immissione dati
			case "OBBLIGATO": break;  //  dopo doppia notifica
			case "TRASGRESSORE": break;  //  dopo doppia notifica
			case "NOLEGGIO": break;  //  dopo doppia notifica ?
			case "INATTESA": break;  //  dopo importazione da web
		}
	}
	
	public function NotificaCoincTrasgDaVerbale ($numProgrRegistro, $anno, $comune)
	{

		$queryCerca = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryCerca .= "WHERE Verbale_ID = Reg_Progr AND ";
		$queryCerca .= "(Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') AND ";
		$queryCerca .= "Comune_CC = '" . $comune . "' AND ";
		$queryCerca .= "Reg_Progr_Registro = '" . $numProgrRegistro . "' AND ";
		$queryCerca .= "Reg_Anno = '" . $anno . "' ";

		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		
		/*if ($_SESSION['CC_User'] == "***+")
			echo "<br>" . $queryCerca;*/


		return $rigaCerca['ID'];
	}
	
	public function NotificaCoincTrasgDaProgrVerbale ($numProgr, $comune)
	{
		$queryCerca = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryCerca .= "WHERE Verbale_ID = Reg_Progr AND ";
		$queryCerca .= "(Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') AND ";
		$queryCerca .= "Comune_CC = '" . $comune . "' AND ";
		$queryCerca .= "Reg_Progr = '" . $numProgr . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		
		/*if ($_SESSION['CC_User'] == "***+")
			echo "<br>" . $queryCerca;*/
		return $rigaCerca['ID'];
	}
	
	public function NotificaInAttesaDaProgrVerbale ($numProgr, $comune)
	{
		$queryCerca = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryCerca .= "WHERE Verbale_ID = Reg_Progr AND ";
		$queryCerca .= "Tipo_Trasgressore = 'INATTESA' AND ";
		$queryCerca .= "Comune_CC = '" . $comune . "' AND ";
		$queryCerca .= "Reg_Progr = '" . $numProgr . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		
		/*if ($_SESSION['CC_User'] == "***+")
			echo "<br>" . $queryCerca;*/
		return $rigaCerca['ID'];
	}
	
	public function NotificaGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_notifiche ";
		$queryCerca .= "WHERE Verbale_ID = '" . $this->Verbale_ID . "' ";
		$queryCerca .= "AND Utente_ID = '" . $this->Utente_ID . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	public function CalcoloNotImportoTotale ()
	{
		$totaleImporto = $this->Coll_Verbale->Reg_Importo_Amministrativo;
		$totaleImporto += $this->Spese_Notifiche_Comune;
		$totaleImporto += $this->Spese_Ricerche_Comune;
		$totaleImporto += $this->Spese_Notifiche_Sarida;
		$totaleImporto += $this->Spese_Ricerche_Sarida;
		return $totaleImporto;
	}
	
	public function CalcoloNotImportoTotaleRidotto ()
	{
		$totaleImporto = $this->Coll_Verbale->Coll_Articoli_Infranti[0]->Tar_Tariffa_Sanz;
		$totaleImporto += $this->Spese_Notifiche_Comune;
		$totaleImporto += $this->Spese_Ricerche_Comune;
		$totaleImporto += $this->Spese_Notifiche_Sarida;
		$totaleImporto += $this->Spese_Ricerche_Sarida;
		return $totaleImporto;
	}
	
	public function CalcoloNotImportoTotaleNonRidotto ()
	{
		$totaleImporto = $this->Coll_Verbale->Coll_Articoli_Infranti[0]->Tar_Tariffa_Sanz_Max;
		$totaleImporto += $this->Spese_Notifiche_Comune;
		$totaleImporto += $this->Spese_Ricerche_Comune;
		$totaleImporto += $this->Spese_Notifiche_Sarida;
		$totaleImporto += $this->Spese_Ricerche_Sarida;
		return $totaleImporto;
	}
	
	public function CalcoloNotImportoEntro5Giorni ()
	{
		$totale70Importo = $this->Coll_Verbale->CalcoloSanzioneEntro5Giorni();
		$totale70Importo += $this->Spese_Notifiche_Comune;
		$totale70Importo += $this->Spese_Ricerche_Comune;
		$totale70Importo += $this->Spese_Notifiche_Sarida;
		$totale70Importo += $this->Spese_Ricerche_Sarida;
		return $totale70Importo;
	}
	
	public function CalcoloNotGiustoImporto ($diffGiorni)
	{
		if ($diffGiorni == -4)
			$importoRichiesto = $this->CalcoloNotImportoEntro5Giorni();
		else if ($diffGiorni < 0)
			$importoRichiesto = $this->CalcoloNotImportoTotaleNonRidotto();
		else if ($diffGiorni <= 5)
			$importoRichiesto = $this->CalcoloNotImportoEntro5Giorni();
		else if ($diffGiorni > 60)
			$importoRichiesto = $this->CalcoloNotImportoTotaleNonRidotto();
		else
			$importoRichiesto = $this->CalcoloNotImportoTotale();
		return $importoRichiesto;
	}
	
	public function DifferenzaNotRidotto5Giorni ()
	{
		return ($this->CalcoloNotImportoTotale() - $this->CalcoloNotImportoEntro5Giorni());
	}
	
	public function CalcoloTutteSpese ()
	{
		$totaleSpese = $this->Spese_Notifiche_Comune;
		$totaleSpese += $this->Spese_Ricerche_Comune;
		$totaleSpese += $this->Spese_Notifiche_Sarida;
		$totaleSpese += $this->Spese_Ricerche_Sarida;
		return $totaleSpese;
	}
	
	public function CercaUtenteDaTarga ($targa)
	{
		$queryTarga = "SELECT Reg_Progr FROM registro_cronologico_cds ";
		$queryTarga .= " WHERE Reg_Targa_Veicolo = '" . $targa . "' ";
		$resTarga = mysql_query($queryTarga);
		//if ($_SESSION['CC_User'] == "***+") echo "<br>" . $queryTarga;
		$arrayTempIDUtenti = array();
		while ($rigaTarga = mysql_fetch_assoc($resTarga))
		{
			$queryNot = "SELECT DISTINCT Utente_ID FROM targhe_estere_notifiche ";
			$queryNot .= " WHERE Verbale_ID = '" . $rigaTarga['Reg_Progr'] . "' ";
			$resNot = mysql_query($queryNot);
			//if ($_SESSION['CC_User'] == "***+") echo "<br>" . $queryNot;
			while ($rigaNot = mysql_fetch_assoc($resNot))
			{
				if ($rigaNot['Utente_ID'] != 0)
				{
					$arrayTempIDUtenti[] = $rigaNot['Utente_ID'];
				}
			}
		}
		$arrayIDUtenti = array();
		for ($i = 0; $i < count($arrayTempIDUtenti); $i++)
		{
			$idPresente = false;
			for ($j = 0; $j < count($arrayIDUtenti); $j++)
			{
				if ($arrayTempIDUtenti[$i] == $arrayIDUtenti[$j])
				{
					$idPresente = true;
					break;
				}
			}
			if ($idPresente == false)
			{
				$arrayIDUtenti[] = $arrayTempIDUtenti[$i];
			}
		}
		
		return $arrayIDUtenti;
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
	
	public function TrovoSollecitoInArray ($numeroProgrSollecito)
	{
		for ($kkk = 0; $kkk < count($this->Coll_Solleciti); $kkk++)
		{
			//echo "<br>" . $this->Coll_Solleciti[$kkk]->Numero_Progressivo_Sollecito . " == " . $numeroProgrSollecito;
			if ($this->Coll_Solleciti[$kkk]->Numero_Progressivo_Sollecito == $numeroProgrSollecito)
			{
				return $kkk;
			}
		}
		return -1;
	}
	
	public function InsertUpdateNotifica ($forzoInsertUpdate = NULL)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		/*if ($_SESSION['CC_User'] == "***+")
		{
			echo "<br>dentro5 " . $this->Spese_Notifiche_Sarida;
		}*/
		$this->Spese_Notifiche_Comune = $this->NumeroPerDB($this->Spese_Notifiche_Comune);
		$this->Spese_Ricerche_Comune = $this->NumeroPerDB($this->Spese_Ricerche_Comune);
		$this->Spese_Notifiche_Sarida = $this->NumeroPerDB($this->Spese_Notifiche_Sarida);
		$this->Spese_Ricerche_Sarida = $this->NumeroPerDB($this->Spese_Ricerche_Sarida);
		
		/*if ($_SESSION['CC_User'] == "***+")
		{
			echo "<br>dentro6 " . $this->Spese_Notifiche_Sarida;
		}*/
		
		$mySpeseTotale = new targhe_estere_spese_per_gestore(null);
		$esitoSpese = $mySpeseTotale->SettaSpesaTotale($this->Comune_CC);
		if ($esitoSpese == false)
		{
			return "ERRORE_SPESEPOSTALI";
		}
		
		if (!isset($this->Coll_Utente))
		{
			$this->Coll_Utente = new targhe_estere_utenti($this->Utente_ID, $this->Comune_CC);
		}
		/*if ($_SESSION['CC_User'] == "***+")
		{
			echo "<br>dentro77 " . $this->Spese_Notifiche_Sarida . " zona " . $this->Coll_Utente->Zona_Spese_Postali;
		}*/
		
		if ($this->Coll_Utente->Zona_Spese_Postali != "" && $this->Coll_Utente->Zona_Spese_Postali != 0)
		{
			$mySpesaZone = new spese_notifica_postali_estere(null);
			$spesaZone = $mySpesaZone->ZonaGiaPresente($this->Coll_Utente->Zona_Spese_Postali);
			$mySpesaZone = new spese_notifica_postali_estere($spesaZone);
			
			/*if ($_SESSION['CC_User'] == "***+")
			{
				echo "<br>dentro1 " . $this->Spese_Notifiche_Sarida . " != " . $mySpesaZone->Spesa;
			}*/
			if ($this->Spese_Notifiche_Sarida != $mySpesaZone->Spesa)
			{
				$this->Spese_Notifiche_Sarida = $mySpesaZone->Spesa;
				$this->Spese_Ricerche_Sarida = $mySpeseTotale->Spesa_Totale - $mySpesaZone->Spesa;
			}
			/*if ($_SESSION['CC_User'] == "***+")
			{
				echo "<br>dentro2 " . $this->Spese_Notifiche_Sarida;
			}*/
		}
		
		
		$this->Data_Registrazione = date("Y-m-d");
		$this->Operatore = $_SESSION['username'];
		
		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else 
		{
			$this->ID = $this->NotificaGiaPresente();
		
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM targhe_estere_notifiche WHERE ID = '$this->ID'";
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
			if (isset($campo) && $campo != "ID" && 
				$campo != "Coll_Verbale" && 
				$campo != "Coll_Utente" && 
				$campo != "Coll_Esito_Notifica" && 
				$campo != "Coll_Esito_Stato_Notifica" &&
				$campo != "Coll_Notifica_Importata" &&
				$campo != "Coll_Tipo_Rinotifica" && 
				$campo != "Coll_Pagamenti" &&
				$campo != "Coll_Solleciti" &&
				$campo != "Coll_Dati_Utente")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		
		$risposta = "";
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_notifica_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_notifica_locale($this->ID, $fields, $values);
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
	
	public function insert_notifica_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_notifiche (" . $clause . ") VALUES (";
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
			return safe_query($query);
		}
		else 
		{
			echo "<br>" . $query;
			return true;
		}
	}
	
	public function update_notifica_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldUtente = new targhe_estere_notifiche($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			//echo "<br>" . $myOldUtente->$fields_to_update[$i] . "         $fields_to_update[$i]  -->  $values_to_update[$i]";
			if ($myOldUtente->$fields_to_update[$i] != $values_to_update[$i])
			{
				//echo "<br>" . $myOldUtente->$fields_to_update[$i] . "         $fields_to_update[$i]  -->  $values_to_update[$i]";
				//if ($values_to_update[$i] != NULL)
				//if (isset($fields_to_update[$i]))
				{
					//alert($fields_to_update[$i]);
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_notifiche SET $clause WHERE ID = '" . $key . "'";
		
		// tutti dati uguali: update inutile 8cambia solo la data di oggi!
		$nonUpd = "UPDATE targhe_estere_notifiche SET Data_Registrazione";
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
	
	public function InsertUpdateProvaNotifica ($forzoInsertUpdate = NULL)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		$this->Spese_Notifiche_Comune = $this->NumeroPerDB($this->Spese_Notifiche_Comune);
		$this->Spese_Ricerche_Comune = $this->NumeroPerDB($this->Spese_Ricerche_Comune);
		$this->Spese_Notifiche_Sarida = $this->NumeroPerDB($this->Spese_Notifiche_Sarida);
		$this->Spese_Ricerche_Sarida = $this->NumeroPerDB($this->Spese_Ricerche_Sarida);
		
		$this->Data_Registrazione = date("Y-m-d");
		$this->Operatore = $_SESSION['username'];
		
		if ($forzoInsertUpdate != NULL)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else 
		{
			$this->ID = $this->NotificaGiaPresente();
		
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM targhe_estere_notifiche WHERE ID = '$this->ID'";
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
			if (isset($campo) && $campo != "ID" && 
				$campo != "Coll_Verbale" && 
				$campo != "Coll_Utente" && 
				$campo != "Coll_Esito_Notifica" && 
				$campo != "Coll_Esito_Stato_Notifica" &&
				$campo != "Coll_Notifica_Importata" &&
				$campo != "Coll_Tipo_Rinotifica"&& 
				$campo != "Coll_Pagamenti" &&
				$campo != "Coll_Dati_Utente")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		
		$risposta = "";
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_notifica_prova_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_notifica_prova_locale($this->ID, $fields, $values);
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
	
	public function insert_notifica_prova_locale($fields_to_insert, $values_to_insert)
	{
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
		if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;
	
		$clause = "";
		echo "<br><br>INSERT targhe_estere_notifiche";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= $fields_to_insert[$i];
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query = "INSERT INTO targhe_estere_notifiche (" . $clause . ") VALUES (";
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
	
	public function update_notifica_prova_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldUtente = new targhe_estere_notifiche($key);
	
		$clause = "";
		echo "<br><br>UPDATE targhe_estere_notifiche WHERE ID = '" . $key . "'";
		for ($i = 0; $i < $dim1; $i++)
		{
			//echo "<br>" . $myOldUtente->$fields_to_update[$i] . "         $fields_to_update[$i]  -->  $values_to_update[$i]";
			if ($myOldUtente->$fields_to_update[$i] != $values_to_update[$i])
			{
				//echo "<br>" . $myOldUtente->$fields_to_update[$i] . "         $fields_to_update[$i]  -->  $values_to_update[$i]";
				//if ($values_to_update[$i] != NULL)
				//if (isset($fields_to_update[$i]))
				{
					//alert($fields_to_update[$i]);
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
					echo "<br>" . $fields_to_update[$i] . "='" .$values_to_update[$i] . "'   (" . $myOldUtente->$fields_to_update[$i] . ")";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_notifiche SET $clause WHERE ID = '" . $key . "'";
		
		// tutti dati uguali: update inutile 8cambia solo la data di oggi!
		$nonUpd = "UPDATE targhe_estere_notifiche SET Data_Registrazione";
		if (substr($query, 0, strlen($nonUpd)) == $nonUpd)
			return true;
		
		//echo "<br>" . $query;
		return true;
	}
	/*
	function NotificaDatiImmessi (  //  arriva da ritorno_dati
			$progressivoregistro,
			$comune,
			$anno,
			$cognome,
			$nome,
			$genere,
			$datanascita,
			$luogonascita,
			$indirizzo1,
			$indirizzo2,
			$indirizzo3,
			$indirizzo4,
			$indirizzo5,
			$indirizzo6,
			$marcaauto,
			$modelloauto
			$bloccorichiesta
	)
	{
		$myUtente = new targhe_estere_utenti(NULL, $comune);
		$myUtente->CC_Comune = $comune;
		$myUtente->Cognome = $cognome;
		$myUtente->Nome = $nome;
		$myUtente->Genere = $genere;
		$myUtente->Data_Nascita = to_mysql_date($datanascita);
		$myUtente->Comune_Nascita = $luogonascita;
		$myUtente->Indirizzo1 = $indirizzo1;
		$myUtente->Indirizzo2 = $indirizzo2;
		$myUtente->Indirizzo3 = $indirizzo3;
		$myUtente->Indirizzo4 = $indirizzo4;
		$myUtente->Indirizzo5 = $indirizzo5;
		$myUtente->Indirizzo6 = $indirizzo6;
		
		$myVerbale = new registro_cronologico_cds($progressivoregistro);
		
		$myVerbale->Reg_Anno = $anno;
		//$myVerbale->Reg_Progr_Registro = $myVerbale->SettaProssimoRegistroCrono();
		$myVerbale->Reg_Progr_Registro = 0;
		//alert ($myVerbale->Reg_Progr_Registro);
		//return;
		$myVerbale->Reg_Stato_erbale = "INSEITO";
		$myVerbale->Reg_Marca_Veicolo = $marcaauto;
		$myVerbale->Reg_Tipologia_Vecolo = $modelloauto;  //  modello
		//$myVerbale->Reg_Immagini = $campofoto;
		$myVerbale->Reg_Motivi_Esecuzione_Impossibile = $bloccorichiesta;
		if ($bloccorichiesta != "") $myVerbale->Reg_Data_Esecuzione_Impossibile = date("d/m/Y");
		
		$myVerbale->InsertUpdateRegistroCronologico();
		
		$myUtente->InsertUpdateUtenteEstero();
		
		$questoUtente = $myUtente->UtenteGiaPresente();
		
		$this->Comune_CC = $myVerbale->Reg_Comune_Violazione;
		$this->Anno = $myVerbale->Reg_Anno;
		$this->Verbale_ID = $myVerbale->Reg_Progr;
		$this->Utente_ID = $questoUtente;
		$this->Tipo_Trasgressore = "COINCIDENTE";
		$this->Spese_Notifica = $myVerbale->CalcoloSoloSpese();
		
		return $this->InsertUpeNotifica();
	}*/
	/*
	function NotificaDatiImportati2 (  //  arriva da setta_web_1
			//$progressivo,
			$comune,
			$anno,
			$cognome,
			$nome,
			$genere,
			$indirizzo1,
			$indirizzo2,
			$indirizzo3,
			$indirizzo4,
			$indirizzo5,
			$indirizzo6,
			$oldnumero
	)
	{
		$myUtente = new targhe_estere_utenti(NULL, $comune);
		$myUtente->CC_Comune = $comune;
		$myUtente->Cognome = $cognome;
		$myUtente->Nome = $nome;
		$myUtente->Genere = $genere;
		$myUtente->Indirizzo1 = $indirizzo1;
		$myUtente->Indirizzo2 = $indirizzo2;
		$myUtente->Indirizzo3 = $indirizzo3;
		$myUtente->Indirizzo4 = $indirizzo4;
		$myUtente->Indirizzo5 = $indirizzo5;
		$myUtente->Indirizzo6 = $indirizzo6;
		
		$myVerbale = new registro_cronologico_cds($this->Verbale_ID);
		
		$myVerbale->Reg_Anno = $anno;
		//$myVerbale->Reg_Progr_Registro = $myVerbale->SettaProssimoRegistroCrono();
		$myVerbale->Reg_Progr_Registro = 0;
		$myVerbale->Reg_Stato_erbale = "AUTOMPORTATO";
		$myVerbale->Reg_Provenienza = $oldnumero;
		//$myVerbale->Reg_Immagini = $campofoto;
		
		$myVerbale->InsertUpdateRegistroCronologico();
		
		$myUtente->InsertUpdateUtenteEstero();
		
		$questoUtente = $myUtente->UtenteGiaPresente();
		
		$this->Comune_CC = $myVerbale->Reg_Comune_Violazione;
		$this->Anno = $myVerbale->Reg_Anno;
		$this->Verbale_ID = $myVerbale->Reg_Progr;
		$this->Utente_ID = $questoUtente;
		//$this->Tipo_Trasgressore = "COINCIDENTE";  INATTESA
		$this->Spese_Notifica = $myVerbale->CalcoloSoloSpese();
		
		return $this->InsertUpeNotifica();
	}*/
	
	function NotificaDatiImportati1 (
			$progressivo,
			$comune,
			$anno,
			$cognome,
			$nome,
			$genere,
			$datanascita,
			$indirizzo1,
			$indirizzo2,
			$indirizzo3,
			$indirizzo4,
			$indirizzo5,
			$indirizzo6
	)
	{
		$myUtente = new targhe_estere_utenti(NULL, $comune);
		$myUtente->CC_Comune = $comune;
		$myUtente->Cognome = $cognome;
		$myUtente->Nome = $nome;
		$myUtente->Genere = $genere;
		$myUtente->Data_Nascita = $datanascita;
		$myUtente->Indirizzo1 = $indirizzo1;
		$myUtente->Indirizzo2 = $indirizzo2;
		$myUtente->Indirizzo3 = $indirizzo3;
		$myUtente->Indirizzo4 = $indirizzo4;
		$myUtente->Indirizzo5 = $indirizzo5;
		$myUtente->Indirizzo6 = $indirizzo6;
		
		$myUtente->InsertUpdateUtenteEstero();
		
		$questoUtente = $myUtente->UtenteGiaPresente();
		
		$myVerbale = new registro_cronologico_cds($progressivo);
		$this->Comune_CC = $myVerbale->Reg_Comune_Violazione;
		$this->Anno = $anno;
		$this->Verbale_ID = $myVerbale->Reg_Progr;
		$this->Utente_ID = $questoUtente;
		$this->Tipo_Trasgressore = "INATTESA";
		
		return $this->InsertUpdateNotifica();
	}
	
	function QueryPreinserimenti ($comune, $anno)
	{
		$queryNotifiche = "SELECT * FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryNotifiche .= "WHERE Verbale_ID = Reg_Progr AND ";
		//$queryNotifiche .= "Tipo_Trasgressore != 'INATTESA' AND ";
		$queryNotifiche .= "(Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') AND ";
		$queryNotifiche .= "Reg_Comune_Violazione = '" . $comune . "' AND ";
		$queryNotifiche .= "Reg_Progr_Registro = 0 AND ";
		$queryNotifiche .= "Reg_Anno = '" . $anno . "' AND ";
		$queryNotifiche .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$queryNotifiche .= "ORDER BY Reg_Progr";
		return $queryNotifiche;
	}
	
	function QueryVerbali ($comune, $anno)
	{
		$queryNotifiche = "SELECT * FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryNotifiche .= "WHERE Verbale_ID = Reg_Progr AND ";
		//$queryNotifiche .= "Tipo_Trasgressore != 'INATTESA' AND ";
		$queryNotifiche .= "(Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') AND ";
		$queryNotifiche .= "Reg_Comune_Violazione = '" . $comune . "' AND ";
		$queryNotifiche .= "Reg_Progr_Registro != 0 AND ";
		//$queryNotifiche .= "Reg_Data_Annullamento = '0000-00-00' AND ";
		$queryNotifiche .= "Reg_Anno = '" . $anno . "' ";
		$queryNotifiche .= "ORDER BY Reg_Progr_Registro";
		return $queryNotifiche;
	}
	
	function QueryAnnullatiVerbali ($comune, $anno)
	{
		$queryNotifiche = "SELECT * FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryNotifiche .= "WHERE Verbale_ID = Reg_Progr AND ";
		//$queryNotifiche .= "Tipo_Trasgressore != 'INATTESA' AND ";
		$queryNotifiche .= "Reg_Comune_Violazione = '" . $comune . "' AND ";
		//$queryNotifiche .= "Reg_Progr_Registro != 0 AND ";
		$queryNotifiche .= "Reg_Anno = '" . $anno . "' AND ";
		$queryNotifiche .= "Reg_Data_Annullamento != '0000-00-00' ";
		$queryNotifiche .= "ORDER BY Reg_Progr_Registro";
		return $queryNotifiche;
	}
	
	function QuerySenzaRegione ($comune, $anno, $esclusione = "")
	{
		$queryNotificheSenzaRegione = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryNotificheSenzaRegione .= "WHERE Verbale_ID = Reg_Progr AND ";
		$queryNotificheSenzaRegione .= " Utente_ID != 0 AND ";
		$queryNotificheSenzaRegione .= "Tipo_Trasgressore = 'COINCIDENTE' AND ";
		$queryNotificheSenzaRegione .= "Reg_Comune_Violazione = '" . $comune . "' AND ";
		$queryNotificheSenzaRegione .= "Reg_Progr_Registro = 0 AND ";
		$queryNotificheSenzaRegione .= "Reg_Anno = '" . $anno . "' AND ";
		$queryNotificheSenzaRegione .= " Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
		$queryNotificheSenzaRegione .= " Reg_Data_Annullamento = '0000-00-00' AND ";
		if ($esclusione != "") $queryNotificheSenzaRegione .= $esclusione;
		$queryNotificheSenzaRegione .= "Reg_Ente_Per_Richiesta = 1 ";
		return $queryNotificheSenzaRegione;
	}
	
	function QueryDaCreareSolleciti ($comune, $dataInfr, $dataStampa, $dataNotifica, $esclusione = "")
	{
		$queryDaSollecito = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryDaSollecito .= "WHERE Verbale_ID = Reg_Progr AND ";
		
		if ($esclusione != "") $queryDaSollecito .= $esclusione;

		$queryDaSollecito .= "Reg_Progr_Registro != 0 AND ";
		$queryDaSollecito .= "Reg_Comune_Violazione = '" . $comune . "' AND ";
		//$queryDaSollecito .= "Reg_Anno = '" . $anno . "' AND ";
		$queryDaSollecito .= "Reg_Data_Avviso >= '" . $dataInfr . "' AND ";
		$queryDaSollecito .= "Data_Stampa_Notifica <= '" . $dataStampa . "' AND ";
		$queryDaSollecito .= "Data_Notifica <= '" . $dataNotifica . "' AND ";
		$queryDaSollecito .= "Data_Creazione_Flusso != '0000-00-00' AND ";
		$queryDaSollecito .= "(Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') AND ";
		
		$queryDaSollecito .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
		$queryDaSollecito .= "Reg_Data_Annullamento = '0000-00-00' ";
		
		$queryDaSollecito .= "ORDER BY Reg_Anno, Reg_Progr_Registro";
		return $queryDaSollecito;
	}
	
	function QueryDaCreareVerbali ($comune, $anno, $esclusione = "")
	{
		$queryDaCreare = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryDaCreare .= "WHERE Verbale_ID = Reg_Progr AND ";
		$queryDaCreare .= " Utente_ID != 0 AND ";
		
		// modifica 24/2/2015
		//$queryDaCreare .= "Tipo_Trasgressore != 'INATTESA' AND ";
		$queryDaCreare .= "Tipo_Trasgressore = 'COINCIDENTE' AND ";
		// fine modifica
		
		if ($esclusione != "") $queryDaCreare .= $esclusione;
		
		$queryDaCreare .= "Reg_Comune_Violazione = '" . $comune . "' AND ";
		$queryDaCreare .= "Reg_Progr_Registro = 0 AND ";
		$queryDaCreare .= "Reg_Anno = '" . $anno . "' AND ";
		$queryDaCreare .= "Reg_Ente_Per_Richiesta != 1 AND ";
		$queryDaCreare .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
		$queryDaCreare .= "Reg_Data_Annullamento = '0000-00-00' ";
		
		$queryDaCreare .= "ORDER BY Reg_Data_Avviso";
		return $queryDaCreare;
	}
	
	function QueryDaStampareVerbali ($comune, $anno)
	{
		$queryDaStampare = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryDaStampare .= "WHERE Verbale_ID = Reg_Progr AND ";
		$queryDaStampare .= " Utente_ID != 0 AND ";
		$queryDaStampare .= "(Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') AND ";
		$queryDaStampare .= "Comune_CC = '" . $comune . "' AND ";
		$queryDaStampare .= "Reg_Anno = '" . $anno . "' AND ";
		$queryDaStampare .= "Data_Stampa_Notifica = '0000-00-00' AND ";
		$queryDaStampare .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
		$queryDaStampare .= "Reg_Data_Annullamento = '0000-00-00' AND ";
		$queryDaStampare .= "Reg_Data_Verbalizzazione != '0000-00-00' ";
		$queryDaStampare .= "ORDER BY Reg_Progr_Registro";
		
		return $queryDaStampare;
	}
	
	function QueryDaFareFlussoVerbali ($comune, $anno)
	{
		$queryDaFlussare = "SELECT ID FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$queryDaFlussare .= "WHERE Verbale_ID = Reg_Progr AND ";
		$queryDaFlussare .= " Utente_ID != 0 AND ";
		$queryDaFlussare .= "(Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') AND ";
		$queryDaFlussare .= "Comune_CC = '" . $comune . "' AND ";
		$queryDaFlussare .= "Reg_Anno = '" . $anno . "' AND ";
		$queryDaFlussare .= "Data_Stampa_Notifica != '0000-00-00' AND ";
		$queryDaFlussare .= "Data_Creazione_Flusso = '0000-00-00' AND ";
		$queryDaFlussare .= "Reg_Data_Esecuzione_Impossibile = '0000-00-00' AND ";
		$queryDaFlussare .= "Reg_Data_Annullamento = '0000-00-00' AND ";
		$queryDaFlussare .= "Reg_Data_Verbalizzazione != '0000-00-00' ";
		$queryDaFlussare .= "ORDER BY Reg_Progr_Registro";
		
		return $queryDaFlussare;
	}
	
	function ScrittaStatoStampa ()
	{
		$statoStampa = "INSERITO in data " . from_mysql_date($this->Data_Registrazione);
		if ($this->Coll_Verbale->Reg_Data_Verbalizzazione != "0000-00-00")
			$statoStampa = "INSERITO in data " . from_mysql_date($this->Coll_Verbale->Reg_Data_Verbalizzazione);
		if ($this->Data_Stampa_Notifica != "0000-00-00")
			$statoStampa = "STAMPATO in data " . from_mysql_date($this->Data_Stampa_Notifica);
		if ($this->Data_Creazione_Flusso != "0000-00-00")
		{
			if ($this->Numero_Flusso != 0)
			{
				if ($this->Coll_Notifica_Importata->ID == null)
				{
					$statoStampa = "FLUSSO in data " . from_mysql_date($this->Data_Creazione_Flusso);
				}
				else
				{
					$statoStampa = "POSTALIZZATO in data " . from_mysql_date($this->Coll_Notifica_Importata->Data_Spedizione);
				}
			}
			else $statoStampa = "SPEDITO in data " . from_mysql_date($this->Data_Creazione_Flusso);
		}
		if ($this->Data_Notifica != "0000-00-00")
		{
			if ($this->Esito_Notifica == 0)
			{
				$statoStampa = "NOTIFICATO in data " . from_mysql_date($this->Data_Notifica);
			}
			else if ($this->Esito_Stato_Notifica != 0)
			{
				$statoStampa = strtoupper($this->Coll_Esito_Stato_Notifica->Tipo);
			}
			else
			{
				$statoStampa = "NOTIFICATO in data " . from_mysql_date($this->Data_Notifica);
			}
		}
		if ($this->Coll_Verbale->Reg_Data_Esecuzione_Impossibile != "0000-00-00")
			$statoStampa = "ANNULLATO in data " . from_mysql_date($this->Coll_Verbale->Reg_Data_Esecuzione_Impossibile);
		if ($this->Coll_Verbale->Reg_Data_Annullamento != "0000-00-00")
			$statoStampa = "ANNULLATO in data " . from_mysql_date($this->Coll_Verbale->Reg_Data_Annullamento);
		return $statoStampa;
	}
	
	function NotificaObbligatoCollegata ()
	{
		$queryVerb = "SELECT ID FROM targhe_estere_notifiche ";
		$queryVerb .= "WHERE Verbale_ID = '" . $this->Verbale_ID . "' AND ";
		$queryVerb .= "Tipo_Trasgressore = 'OBBLIGATO' ";
		$resVerb = mysql_query($queryVerb);
		$rigaVerb = mysql_fetch_assoc($resVerb);
		//echo "<br>" . $queryVerb;
		return $rigaVerb['ID'];
	}
	
	function NotificaNoleggioCollegata ()
	{
		$queryVerb = "SELECT ID FROM targhe_estere_notifiche ";
		$queryVerb .= "WHERE Verbale_ID = '" . $this->Verbale_ID . "' AND ";
		$queryVerb .= "Tipo_Trasgressore = 'NOLEGGIO' ";
		$resVerb = mysql_query($queryVerb);
		$rigaVerb = mysql_fetch_assoc($resVerb);
		//echo "<br>" . $queryVerb;
		return $rigaVerb['ID'];
	}
	
	function ListaPerStampeVerbali ($arrayPost)
	{
		/*foreach ($arrayPost as $key => $value)
		{
			echo "<br>$key -> $value";
		}*/
		
		/*$c = NULL;
		$a = NULL;
		
		$da_n_elenco = NULL;
		$a_n_elenco = NULL;
		$selectstato = NULL;
		//$selectcomune = NULL;
		$da_avviso = NULL;
		$da_insert = NULL;
		$da_stampa = NULL;
		$a_avviso = NULL;
		$a_insert = NULL;
		$a_stampa = NULL;
		$giastampate = NULL;
		
		$stampa_select = NULL;  //  provvisoria o definitiva o flusso*/
		
		if (isset($arrayPost['c'])) $c = $arrayPost['c']; else $c = NULL;
		if (isset($arrayPost['a'])) $a = $arrayPost['a']; else $a = NULL;
		
		if (isset($arrayPost['da_n_elenco'])) $da_n_elenco = $arrayPost['da_n_elenco']; else $da_n_elenco = NULL;
		if (isset($arrayPost['a_n_elenco'])) $a_n_elenco = $arrayPost['a_n_elenco']; else $a_n_elenco = NULL;
		if (isset($arrayPost['da_anno'])) $da_anno = $arrayPost['da_anno']; else $da_anno = NULL;
		if (isset($arrayPost['ad_anno'])) $ad_anno = $arrayPost['ad_anno']; else $ad_anno = NULL;
		if (isset($arrayPost['selectstato'])) $selectstato = $arrayPost['selectstato']; else $selectstato = NULL;
		//if (isset($arrayPost['selectcomune'])) $selectcomune = $arrayPost['selectcomune'];
		if (isset($arrayPost['da_avviso'])) $da_avviso = $arrayPost['da_avviso']; else $da_avviso = NULL;
		if (isset($arrayPost['da_insert'])) $da_insert = $arrayPost['da_insert']; else $da_insert = NULL;
		if (isset($arrayPost['da_stampa'])) $da_stampa = $arrayPost['da_stampa']; else $da_stampa = NULL;
		if (isset($arrayPost['a_avviso'])) $a_avviso = $arrayPost['a_avviso']; else $a_avviso = NULL;
		if (isset($arrayPost['a_insert'])) $a_insert = $arrayPost['a_insert']; else $a_insert = NULL;
		if (isset($arrayPost['a_stampa'])) $a_stampa = $arrayPost['a_stampa']; else $a_stampa = NULL;
		if (isset($arrayPost['giastampate'])) $giastampate = $arrayPost['giastampate']; else $giastampate = NULL;
		if (isset($arrayPost['tiposelezione'])) $tiposelezione = $arrayPost['tiposelezione']; else $tiposelezione = NULL;
		
		if (isset($arrayPost['stampa_select'])) $stampa_select = $arrayPost['stampa_select'];  //  provvisoria o definitiva o flusso
		else $stampa_select = NULL;
	
		$aggiuntaElenco = "";
		if ($da_n_elenco != NULL)
			$aggiuntaElenco .= " AND Reg_Progr_Registro >= " . $da_n_elenco . " ";
		if ($a_n_elenco != NULL)
			$aggiuntaElenco .= " AND Reg_Progr_Registro <= " . $a_n_elenco . " ";
		
		$aggiuntaAnno = "";
		if ($da_anno != NULL)
			$aggiuntaAnno .= " AND Reg_Anno >= '" . $da_anno . "' ";
		if ($ad_anno != NULL)
			$aggiuntaAnno .= " AND Reg_Anno <= '" . $ad_anno . "' ";
		
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
		$aggiuntaDataNonStampa = "";
		if ($da_stampa != NULL)  // equivale a GIA STAMPATO
		{
			$aggiuntaDataStampa .= " AND Data_Stampa_Notifica >= '" . to_mysql_date($da_stampa) . "' ";
			$aggiuntaDataStampa .= " AND Data_Stampa_Notifica != '0000-00-00' ";
		}
		if ($a_stampa != NULL)  // equivale a GIA STAMPATO
		{
			$aggiuntaDataStampa .= " AND Data_Stampa_Notifica <= '" . to_mysql_date($a_stampa) . "' ";
			$aggiuntaDataStampa .= " AND Data_Stampa_Notifica != '0000-00-00' ";
		}
		
		$aggiuntaDataFlusso = "";
		$aggiuntaTipoStampa = "";
		if ($stampa_select != "Flusso")
		{
			if ($giastampate != NULL)  //  GIA STAMPATO
			{
				$aggiuntaDataStampa .= " AND Data_Stampa_Notifica != '0000-00-00' ";
			}
			else 
			{
				$aggiuntaDataNonStampa = " AND Data_Stampa_Notifica = '0000-00-00' ";
			}
		}
		else if ($stampa_select == "Flusso")
		{
			$aggiuntaDataFlusso = " AND Data_Creazione_Flusso = '0000-00-00' ";
			$aggiuntaDataStampa .= " AND Data_Stampa_Notifica != '0000-00-00' ";
			//$aggiuntaTipoStampa = " AND (Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') ";
		}
		
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
		
		$aggiuntaNonNotificati = "";
		switch ($tiposelezione)
		{
			case "NON_NOTIFICATI":
				$aggiuntaNonNotificati = " AND Esito_Stato_Notifica != 0 ";
				break;
		}

		$aggiuntaTipoStampa = " AND (Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') ";
		
		$query = "SELECT ID ";
		$query .= " FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$query .= " WHERE Verbale_ID = Reg_Progr ";
		$query .= " AND Comune_CC = '" . $c . "' ";
		//$query .= " AND Data_Stampa_Notifica = '0000-00-00' ";
		$query .= " AND Utente_ID != 0 ";
		$query .= " AND Reg_Data_Annullamento = '0000-00-00' ";
		$query .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$query .= $aggiuntaElenco;
		$query .= $aggiuntaAnno;
		$query .= $aggiuntaDataAvviso;
		$query .= $aggiuntaDataInserimento;
		$query .= $aggiuntaDataStampa;
		$query .= $aggiuntaDataNonStampa;
		$query .= $aggiuntaDataFlusso;
		$query .= $aggiuntaTipoStampa;
		$query .= $aggiuntaNazione;
		$query .= $aggiuntaNonNotificati;
		//$query .= " AND ID = 3 ";  // da togliere
		$query .= " AND Reg_Data_Verbalizzazione != '0000-00-00' ";
		
		$query .= " ORDER BY Reg_Progr_Registro ASC ";
		$resultVerbali = safe_query($query);
		
		if ($_SESSION['CC_User'] == "***+")
		{
			echo "<br>$query -> " . mysql_num_rows($resultVerbali);
		}
		
		$listaVerbali = array();
		
		while ($rigaVerbali = mysql_fetch_assoc($resultVerbali))
		{
			//for ($k = 0; $k < 15; $k++)
			{
				$listaVerbali[] = $rigaVerbali['ID'];
			}
		}
		
		return $listaVerbali;
	}
	
	function ListaPerStampeSolleciti ($arrayPost)
	{
		/*foreach ($arrayPost as $key => $value)
		{
			echo "<br>$key -> $value";
		}*/
		
		/*$c = NULL;
		$a = NULL;
		
		$da_n_elenco = NULL;
		$a_n_elenco = NULL;
		$selectstato = NULL;
		//$selectcomune = NULL;
		$da_avviso = NULL;
		$da_insert = NULL;
		$da_stampa = NULL;
		$a_avviso = NULL;
		$a_insert = NULL;
		$a_stampa = NULL;
		$giastampate = NULL;
		
		$stampa_select = NULL;  //  provvisoria o definitiva o flusso*/
		
		if (isset($arrayPost['c'])) $c = $arrayPost['c']; else $c = NULL;
		if (isset($arrayPost['a'])) $a = $arrayPost['a']; else $a = NULL;
		
		if (isset($arrayPost['da_n_elenco'])) $da_n_elenco = $arrayPost['da_n_elenco']; else $da_n_elenco = NULL;
		if (isset($arrayPost['a_n_elenco'])) $a_n_elenco = $arrayPost['a_n_elenco']; else $a_n_elenco = NULL;
		if (isset($arrayPost['da_anno'])) $da_anno = $arrayPost['da_anno']; else $da_anno = NULL;
		if (isset($arrayPost['ad_anno'])) $ad_anno = $arrayPost['ad_anno']; else $ad_anno = NULL;
		if (isset($arrayPost['selectstato'])) $selectstato = $arrayPost['selectstato']; else $selectstato = NULL;
		//if (isset($arrayPost['selectcomune'])) $selectcomune = $arrayPost['selectcomune'];
		if (isset($arrayPost['da_avviso'])) $da_avviso = $arrayPost['da_avviso']; else $da_avviso = NULL;
		if (isset($arrayPost['da_insert'])) $da_insert = $arrayPost['da_insert']; else $da_insert = NULL;
		if (isset($arrayPost['da_stampa'])) $da_stampa = $arrayPost['da_stampa']; else $da_stampa = NULL;
		if (isset($arrayPost['a_avviso'])) $a_avviso = $arrayPost['a_avviso']; else $a_avviso = NULL;
		if (isset($arrayPost['a_insert'])) $a_insert = $arrayPost['a_insert']; else $a_insert = NULL;
		if (isset($arrayPost['a_stampa'])) $a_stampa = $arrayPost['a_stampa']; else $a_stampa = NULL;
		if (isset($arrayPost['giastampate'])) $giastampate = $arrayPost['giastampate']; else $giastampate = NULL;
		if (isset($arrayPost['tiposelezione'])) $tiposelezione = $arrayPost['tiposelezione']; else $tiposelezione = NULL;
		
		if (isset($arrayPost['stampa_select'])) $stampa_select = $arrayPost['stampa_select'];  //  provvisoria o definitiva o flusso
		else $stampa_select = NULL;
	
		$aggiuntaElenco = "";
		if ($da_n_elenco != NULL)
			$aggiuntaElenco .= " AND Reg_Progr_Registro >= " . $da_n_elenco . " ";
		if ($a_n_elenco != NULL)
			$aggiuntaElenco .= " AND Reg_Progr_Registro <= " . $a_n_elenco . " ";
		
		$aggiuntaAnno = "";
		if ($da_anno != NULL)
			$aggiuntaAnno .= " AND Reg_Anno >= '" . $da_anno . "' ";
		if ($ad_anno != NULL)
			$aggiuntaAnno .= " AND Reg_Anno <= '" . $ad_anno . "' ";
		
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
		$aggiuntaDataNonStampa = "";
		if ($da_stampa != NULL)  // equivale a GIA STAMPATO
		{
			$aggiuntaDataStampa .= " AND Data_Stampa_Sollecito >= '" . to_mysql_date($da_stampa) . "' ";
			$aggiuntaDataStampa .= " AND Data_Stampa_Sollecito != '0000-00-00' ";
		}
		if ($a_stampa != NULL)  // equivale a GIA STAMPATO
		{
			$aggiuntaDataStampa .= " AND Data_Stampa_Sollecito <= '" . to_mysql_date($a_stampa) . "' ";
			$aggiuntaDataStampa .= " AND Data_Stampa_Sollecito != '0000-00-00' ";
		}
		
		$aggiuntaDataFlusso = "";
		$aggiuntaTipoStampa = "";
		if ($stampa_select != "Flusso")
		{
			if ($giastampate != NULL)  //  GIA STAMPATO
			{
				$aggiuntaDataStampa .= " AND Data_Stampa_Sollecito != '0000-00-00' ";
			}
			else 
			{
				$aggiuntaDataNonStampa = " AND Data_Stampa_Sollecito = '0000-00-00' ";
			}
		}
		else if ($stampa_select == "Flusso")
		{
			$aggiuntaDataFlusso = " AND Data_Flusso_Sollecito = '0000-00-00' ";
			$aggiuntaDataStampa .= " AND Data_Stampa_Sollecito != '0000-00-00' ";
			//$aggiuntaTipoStampa = " AND (Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') ";
		}
		
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
		
		$aggiuntaNonNotificati = "";
		switch ($tiposelezione)
		{
			case "NON_NOTIFICATI":
				$aggiuntaNonNotificati = " AND Esito_Stato_Notifica != 0 ";
				break;
		}

		$aggiuntaTipoStampa = " AND (Tipo_Trasgressore = 'COINCIDENTE' OR Tipo_Trasgressore = 'TRASGRESSORE') ";
		
		$query = "SELECT targhe_estere_notifiche.ID as IDNOTIFICA ";
		$query .= " FROM targhe_estere_notifiche, registro_cronologico_cds, targhe_estere_solleciti ";
		$query .= " WHERE Verbale_ID = Reg_Progr ";
		$query .= " AND Registro_Provenienza = Reg_Progr ";
		$query .= " AND Comune_CC = '" . $c . "' ";
		//$query .= " AND Data_Stampa_Notifica = '0000-00-00' ";
		$query .= " AND Utente_ID != 0 ";
		$query .= " AND Reg_Data_Annullamento = '0000-00-00' ";
		$query .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$query .= " AND Tipo_Sollecito = 'CDS_ESTERO' ";
		$query .= $aggiuntaElenco;
		$query .= $aggiuntaAnno;
		$query .= $aggiuntaDataAvviso;
		$query .= $aggiuntaDataInserimento;
		$query .= $aggiuntaDataStampa;
		$query .= $aggiuntaDataNonStampa;
		$query .= $aggiuntaDataFlusso;
		$query .= $aggiuntaTipoStampa;
		$query .= $aggiuntaNazione;
		$query .= $aggiuntaNonNotificati;
		//$query .= " AND ID = 3 ";  // da togliere
		//$query .= " AND Reg_Data_Verbalizzazione != '0000-00-00' ";
		
		$query .= " ORDER BY Reg_Progr_Registro ASC ";
		$resultSoll = safe_query($query);
		
		if ($_SESSION['CC_User'] == "***+")
		{
			echo "<br>$query -> " . mysql_num_rows($resultSoll);
		}
		
		$listaSolleciti = array();
		
		while ($rigaSollecito = mysql_fetch_assoc($resultSoll))
		{
			//for ($k = 0; $k < 15; $k++)
			{
				$listaSolleciti[] = $rigaSollecito['IDNOTIFICA'];
			}
		}
		
		return $listaSolleciti;
	}
	
	function LinkVerbalePdf ($PathVerbaliEsteri)
	{
		$linkVerbale = $PathVerbaliEsteri . "Definitivi/";
		$linkVerbale .= "Verbale_Definitivo_";
		$linkVerbale .= $this->Coll_Verbale->Reg_Comune_Violazione . "_";
		$linkVerbale .= $this->Coll_Verbale->Reg_Anno . "_";
		$linkVerbale .= $this->Coll_Verbale->Reg_Progr_Registro . "_";
		$linkVerbale .= $this->Data_Stampa_Notifica . ".pdf";
		return $linkVerbale;
	}
	
	function LinkVerbaliComunePdf ($PathVerbaliComuneEsteri)
	{
		$linkVerbale = $PathVerbaliComuneEsteri;
		$linkVerbale .= "Verbali_Definitivi_";
		$linkVerbale .= $this->Coll_Verbale->Reg_Comune_Violazione . "_";
		$linkVerbale .= $this->Coll_Verbale->Reg_Anno . "_";
		$linkVerbale .= $this->Numero_Stampa_Comune . "_";
		$linkVerbale .= $this->Data_Stampa_Notifica . ".pdf";
		return $linkVerbale;
	}
	
	function LinkCartolinaPdf ($PathCartolineEstere)
	{
		$linkCartolina = $PathCartolineEstere . "Definitive/";
		$linkCartolina .= "pdf_cartolina_";
		$linkCartolina .= $this->Coll_Verbale->Reg_Comune_Violazione . "_";
		$linkCartolina .= $this->ID . "_";
		$linkCartolina .= $this->Data_Stampa_Notifica . ".pdf";
		return $linkCartolina;
	}
	
	public function TrovaProssimoNumStampaTargheEstere ($anno)
	{
		$queryNumStampa = "SELECT max(Numero_Stampa_Comune) as MAX FROM targhe_estere_notifiche";
		$queryNumStampa .= " WHERE Anno = " . $anno;
		$resNumStampa = mysql_query($queryNumStampa);
		$rigaNumStampa = mysql_fetch_assoc($resNumStampa);
		$prossimo = $rigaNumStampa['MAX'] + 1;
		$this->Numero_Stampa_Comune = $prossimo;
		return $prossimo;
	}
	
	function PrimoUltimoVerbaleInStampaComune ()
	{
		$query = "SELECT MAX(Reg_Progr_Registro) as MAX, MIN(Reg_Progr_Registro) as MIN ";
		$query .= "FROM targhe_estere_notifiche, registro_cronologico_cds ";
		$query .= "WHERE Verbale_ID = Reg_Progr AND ";
		$query .= "Numero_Stampa_Comune = " . $this->Numero_Stampa_Comune . " AND ";
		$query .= "Reg_Anno = " . $this->Coll_Verbale->Reg_Anno;
		$resMaxMin = mysql_query($query);
		$rigaMaxMin = mysql_fetch_assoc($resMaxMin);
		$arrayMaxMin = array($rigaMaxMin['MIN'] . "/" . $this->Coll_Verbale->Reg_Anno, $rigaMaxMin['MAX'] . "/" . $this->Coll_Verbale->Reg_Anno);
		return $arrayMaxMin;
	}
}


/*
 * CREATE TABLE `targhe_estere_tipi_rinotifiche` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Tipo` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */


class targhe_estere_tipi_rinotifiche
{
	public $ID;
	public $Tipo;
	
	public function __construct( $progr = 0 )
	{
		if ($progr == NULL) return;
			
		$query = "SELECT * FROM targhe_estere_tipi_rinotifiche WHERE ID = '" . $progr . "'";
		$result = safe_query($query);		
		$rigaTipi = mysql_fetch_assoc($result);
		
		if (mysql_num_rows($result) > 0)
		{
			foreach ($rigaTipi as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	public function SelectTipiRinotifiche ($scelta)
	{
		$query = "SELECT * FROM targhe_estere_tipi_rinotifiche";
		$query .= " ORDER BY ID";
		$result = safe_query($query);
		
		$select = "<option value=''></option>";
		while ($rigaTipi = mysql_fetch_assoc($result))
		{
			if ($scelta == $rigaTipi['ID']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaTipi['ID'] . "' $selScelta>" . $rigaTipi['Tipo'] . "</option>";
		}
		return $select;
	}
}

/*
 * CREATE TABLE `targhe_estere_tipi_ricezione` (
 		`ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 		`Modo` varchar(30) NOT NULL DEFAULT '',
 		`Tipo` varchar(100) NOT NULL DEFAULT '',
 		`Codice` varchar(5) NOT NULL DEFAULT '',
 		PRIMARY KEY (`ID`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/


class targhe_estere_tipi_ricezione
{
	public $ID;
	public $Modo;
	public $Tipo;
	public $Codice;

	public function __construct( $progr = 0 )
	{
		if ($progr == NULL) return;
			
		$query = "SELECT * FROM targhe_estere_tipi_ricezione WHERE ID = '" . $progr . "'";
		$result = safe_query($query);
		$rigaTipi = mysql_fetch_assoc($result);
		
		if (mysql_num_rows($result) > 0)
		{
			foreach ($rigaTipi as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}

	public function SelectTipiManuali ($scelta)
	{
		$query = "SELECT * FROM targhe_estere_tipi_ricezione";
		$query .= " WHERE Modo = 'manuale' ";
		$query .= " ORDER BY ID";
		$result = safe_query($query);
		$select = "<select id='tiporicezione' name='tiporicezione'>";
		$select .= "<option value=''></option>";
		while ($rigaTipi = mysql_fetch_assoc($result))
		{
			if ($scelta == $rigaTipi['ID']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaTipi['ID'] . "' $selScelta>" . $rigaTipi['Tipo'] . "</option>";
		}
		$select .= "</select>";
		return $select;
	}

	public function CercaTipiImportati ($codice)
	{
		$query = "SELECT ID FROM targhe_estere_tipi_ricezione";
		$query .= " WHERE Modo = 'tipo_importato' AND ";
		$query .= " Codice = '" . $codice . "' ";
		$result = mysql_query($query);
		if (mysql_num_rows($result) != 0)
		{
			$rigaTipi = mysql_fetch_assoc($result);
			return $rigaTipi['ID'];
		}
		return null;
	}

	public function CercaStatiImportati ($codice)
	{
		if ($codice == "") return "0";
		$query = "SELECT ID FROM targhe_estere_tipi_ricezione";
		$query .= " WHERE Modo = 'stato_importato' AND ";
		$query .= " Codice = '" . $codice . "' ";
		$result = mysql_query($query);
		//echo "<br>" . $query . " -- ";
		if (mysql_num_rows($result) != 0)
		{
			$rigaTipi = mysql_fetch_assoc($result);
			//echo $rigaTipi['ID'];
			return $rigaTipi['ID'];
		}
		return null;
	}
}

/*
 * CREATE TABLE `targhe_estere_memorizza_stampe_richieste` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Data_Apertura_Pdf` date NOT NULL DEFAULT '0000-00-00',
  `Nome_File_Definitivo` varchar(100) NOT NULL DEFAULT '',
  `Comune` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class targhe_estere_memorizza_stampe_richieste
{
	public $ID;
	public $Data_Apertura_Pdf;
	public $Nome_File_Definitivo;
	public $Comune;
	
	public function __construct( $progr = 0 )
	{
		if ($progr == NULL) return;
			
		$query = "SELECT * FROM targhe_estere_memorizza_stampe_richieste WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaMemo = mysql_fetch_assoc($result);
	
		if (mysql_num_rows($result) > 0)
		{
			foreach ($rigaMemo as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	public function NomeFileGiaPresente ($nomeFile)
	{
		$queryCerca = "SELECT ID FROM targhe_estere_memorizza_stampe_richieste ";
		$queryCerca .= "WHERE Nome_File_Definitivo = '" . $nomeFile . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	public function InsertUpdateMemoStampa ($forzoInsertUpdate = NULL)
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
			$this->ID = $this->NomeFileGiaPresente($this->Nome_File_Definitivo);
	
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM targhe_estere_memorizza_stampe_richieste WHERE ID = '$this->ID'";
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
			$risposta = $this->insert_memo_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_memo_locale($this->ID, $fields, $values);
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
	
	public function insert_memo_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_memorizza_stampe_richieste (" . $clause . ") VALUES (";
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
	
	public function update_memo_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldMemo = new targhe_estere_memorizza_stampe_richieste($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldMemo->$fields_to_update[$i] != $values_to_update[$i])
			{
				$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
			}
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE targhe_estere_memorizza_stampe_richieste SET $clause WHERE ID = '" . $key . "'";
		
		if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
	
		echo "<br>" . $query;
	
		return true;
	}
}

/*
 * CREATE TABLE `spese_notifica_postali_estere` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Valido_Da_Data` date DEFAULT NULL DEFAULT '0000-00-00',
  `Zona` varchar(10) NOT NULL DEFAULT '',
  `Nazione` varchar(100) NOT NULL DEFAULT '',
  `Spesa` decimal(10, 2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class spese_notifica_postali_estere
{
	public $ID;
	public $Valido_Da_Data;
	public $Zona;
	public $Nazione;
	public $Spesa;
	
	public function __construct( $progr = 0 )
	{
		if ($progr == NULL) return;
		
		$query = "SELECT * FROM spese_notifica_postali_estere WHERE ID = '" . $progr . "'";
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
	
	public function ListaZone ($scelta)
	{
		$queryCerca = "SELECT * FROM spese_notifica_postali_estere ";
		$queryCerca .= "ORDER BY Zona ";
		$resCerca = mysql_query($queryCerca);
		$select = "<option value=''></option>";
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			if ($scelta == $rigaCerca['ID']) $selScelta = " selected ";
			else $selScelta = "";
			$select .= "<option value='" . $rigaCerca['ID'] . "' $selScelta>Zona " . $rigaCerca['Zona'] . "</option>\n";
		}
		return $select;
	}
	
	public function InfoZone ()
	{
		$input = "Zona 1: Europa e Bacino del Mediterraneo\n";
		$input .= "Zona 2: Altri Paesi dell'Africa, Asia, Americhe\n";
		$input .= "Zona 3: Oceania";
		$image = '<input type="image" class="pwidth20 phwight20" ';
		$image .= 'src="/gitco2/immagini/puntointerrogativo.jpg" title="';
		$image .= $input;
		$image .= '" onclick="return false;">';
		//echo $image;
		return $image;
	}
	
	public function ZonaGiaPresente ($zona)
	{
		$queryCerca = "SELECT ID FROM spese_notifica_postali_estere ";
		$queryCerca .= "WHERE Zona = " . $zona . " ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		//echo "<br>" . $queryCerca . " -> " . $rigaCerca['ID'];
		return $rigaCerca['ID'];
	}
	
	public function NazioneGiaPresente ($nazione)
	{
		$queryCerca = "SELECT ID FROM spese_notifica_postali_estere ";
		$queryCerca .= "WHERE Nazione = '" . $nazione . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	public function InsertUpdateSpesePostali ($forzoInsertUpdate = NULL)
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
			$this->ID = $this->ZonaGiaPresente($this->Nazione);
	
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM spese_notifica_postali_estere WHERE ID = '$this->ID'";
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
			$risposta = $this->insert_postali_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_postali_locale($this->ID, $fields, $values);
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
	
	public function insert_postali_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO spese_notifica_postali_estere (" . $clause . ") VALUES (";
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
	
	public function update_postali_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldSpesa = new spese_notifica_postali_estere($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldSpesa->$fields_to_update[$i] != $values_to_update[$i])
			{
				$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
			}
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE spese_notifica_postali_estere SET $clause WHERE ID = '" . $key . "'";
		
		if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;
	
		echo "<br>" . $query;
	
		return true;
	}
}

/*
 * CREATE TABLE `targhe_estere_spese_per_gestore` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Data_Validita` date NOT NULL DEFAULT '0000-00-00',
  `Spesa_Totale` decimal(10, 2) NOT NULL DEFAULT '0',
  `Comune` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */

class targhe_estere_spese_per_gestore
{
	public $ID;
	public $Data_Validita;
	public $Spesa_Totale;
	public $Comune;
	
	public function __construct( $progr = 0 )
	{
		if ($progr == NULL) return;
	
		$query = "SELECT * FROM targhe_estere_spese_per_gestore WHERE ID = '" . $progr . "'";
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
	
	public function SettaSpesaTotale ($comune)
	{
		$progr = $this->ComuneGiaPresente($comune);
		
		$query = "SELECT * FROM targhe_estere_spese_per_gestore WHERE ID = '" . $progr . "'";
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
		else $this->ID = null;
		
		$esito = true;
		if ($this->ID == null)
		{
			$myComune = new ente_gestito($comune);
			alert ("E' necessario inserire la spesa di notifica fissa per il comune di " . $myComune->Nome);
			$esito = false;
		}
		return $esito;
	}
	
	public function ComuneGiaPresente ($comune)
	{
		$queryCerca = "SELECT ID FROM targhe_estere_spese_per_gestore ";
		$queryCerca .= "WHERE Comune = '" . $comune . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		//echo "<br>" . $queryCerca . " -> " . $rigaCerca['ID'];
		return $rigaCerca['ID'];
	}
	
	public function InsertUpdateSpeseComune ($forzoInsertUpdate = NULL)
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
			$this->ID = $this->ComuneGiaPresente($this->Comune);
	
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$queryUpd = "SELECT ID FROM targhe_estere_spese_per_gestore WHERE ID = '$this->ID'";
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
			$risposta = $this->insert_spesetotali_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_spesetotali_locale($this->ID, $fields, $values);
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
	
	public function insert_spesetotali_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_spese_per_gestore (" . $clause . ") VALUES (";
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
	
	public function update_spesetotali_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
		
		$myOldSpesa = new targhe_estere_spese_per_gestore($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldSpesa->$fields_to_update[$i] != $values_to_update[$i])
			{
				$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
			}
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE targhe_estere_spese_per_gestore SET $clause WHERE ID = '" . $key . "'";
		
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
		
		echo "<br>" . $query;
		
		return true;
	}
}

?>