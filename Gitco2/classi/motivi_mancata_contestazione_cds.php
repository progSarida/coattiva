<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once LIBRERIE . "/aiuto.php";

/*
 CREATE TABLE `motivi_mancata_contestazione_cds_2` (
  `Mot_Progr` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Mot_Comune` varchar(4) NOT NULL DEFAULT '',
  `Mot_Descrizione` text,
  `Mot_Commento` text,
  `Mot_Codice` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Mot_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class motivi_mancata_contestazione_cds
{
	public $Mot_Progr;
	public $Mot_Comune;
	public $Mot_Descrizione;
	public $Mot_Commento;
	public $Mot_Codice;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM motivi_mancata_contestazione_cds WHERE Mot_Progr = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaMotivo = mysql_fetch_assoc($result);

		foreach ($rigaMotivo as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}
	
	function MotivoGiaPresente ()
	{
		$queryCerca = "SELECT Mot_Progr FROM motivi_mancata_contestazione_cds ";
		$queryCerca .= "WHERE  ";
		//$queryCerca .= "Mot_Comune = '" . $this->Mot_Comune . "' ";
		$queryCerca .= " Mot_Descrizione = '" . $this->Mot_Descrizione . "' ";
		//$queryCerca .= "AND Mot_Commento = '" . $this->Mot_Commento . "'";
		//$queryCerca .= "AND Mot_Codice = '" . $this->Mot_Codice . "'";
		$resCerca = mysql_query($queryCerca);
		if (mysql_num_rows($resCerca) > 0)
		{
			$rigaCerca = mysql_fetch_assoc($resCerca);
			return $rigaCerca['Mot_Progr'];
		}
		else return NULL;
	}
	
	function InsertUpdateMotivo ()
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		if ($this->Mot_Progr != NULL)
		{
			$this->Mot_Progr = $this->MotivoGiaPresente();
		}
	
		if ($this->Mot_Progr == NULL)
		{
			$insUpd = "INSERT";
		}
		else
		{
			$insUpd = "UPDATE";
		}
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "Mot_Progr")
			{
				$fields[] = $campo;
				$values[] = addslashes($valore);
			}
		}
		
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_motivo_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_motivo_locale($this->Mot_Progr, $fields, $values);
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
	
	public function insert_motivo_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO motivi_mancata_contestazione_cds (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		$verif = mysql_query($query);
		
		if ($verif == true)
		{
			$this->Mot_Progr = mysql_insert_id();
		}
		
		return $verif;
	
		echo $query;
	
		return true;
	}
	
	public function update_motivo_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldMotivo = new motivi_mancata_contestazione_cds($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldMotivo->$fields_to_update[$i] != $values_to_update[$i])
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
	
		$query = "UPDATE motivi_mancata_contestazione_cds SET $clause WHERE Mot_Progr = '" . $key . "'";
		
		$this->Mot_Progr = $key;
	
		if (mysql_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
}

$richiestaMotiviJquery = get_var('richiestaMotiviJquery');

if ($richiestaMotiviJquery == "SI")
{
	$progressivo = get_var('Mot_Progr');
	$myMotivo = new motivi_mancata_contestazione_cds($progressivo);
	
	foreach ($_POST as $key => $value)
	{
		if ($key != "c" &&
			$key != "richiestaMotiviJquery")
		{
			$myMotivo->$key = $value;
		}
		else if ($key == "c") $myMotivo->Mot_Comune = $c;
		else if ($key == "richiestaMotiviJquery") {}
	}
	$myMotivo->Mot_Codice = "0";
	if ($myMotivo->Mot_Comune == "") $myMotivo->Mot_Comune = "A658";
	
	$verif = $myMotivo->InsertUpdateMotivo();
	
	switch ($verif)
	{
		//case "UNAFIRMA": break;
		//case "ERROREFIRMA": break;
		case "INSERT_OK": break;
		case "INSERT_ERROR": break;
		case "UPDATE_OK": break;
		case "UPDATE_ERROR": break;
		//case "MATRICOLAPRESENTE": break;
	}
	
	echo $verif . "**" . $myMotivo->Mot_Progr . "**" . $myMotivo->Mot_Descrizione;
}


/*
 * CREATE TABLE `motivi_traduzioni` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Riferimento_Motivo` int(4) NOT NULL,
  `Linguaggio` int(4) NOT NULL,
  `Traduzione` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 * 
 */

class motivi_traduzioni
{
	public $ID;
	public $Riferimento_Motivo;
	public $Linguaggio;
	public $Traduzione;
	
	public function __construct($motivoIta, $linguaggio)
	{
		if ($motivoIta == NULL) return NULL;
		if ($linguaggio == NULL) return NULL;
		
		if ($motivoIta == 1) return NULL;
		if ($linguaggio == 1) return NULL;
		
		//alert (single_answer_query("SELECT DATABASE()"));
	
		$queryMotiviLingue = "SELECT * FROM motivi_traduzioni ";
		$queryMotiviLingue .= " WHERE Riferimento_Motivo = '" . $motivoIta . "'";
		$queryMotiviLingue .= " AND Linguaggio = '" . $linguaggio . "'";
		$resultMot = mysql_query($queryMotiviLingue);
		
		$numRighe = mysql_num_rows($resultMot);
		//echo "<br>" . $queryMotiviLingue . " -> " . $numRighe;
		if ($numRighe != 1)
		{
			alert ("Attenzione, errore su motivi_traduzione: $numRighe");
			alert ($queryMotiviLingue);
		}
		
		while ($rigaTraduzione = mysql_fetch_assoc($resultMot))
		{
			//echo "<br>" . $queryMotiviLingue . " -> " . $numRighe;
			foreach ($rigaTraduzione as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
}

/*
 * 
 * CREATE TABLE `motivi_annullamento` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Tipo_Motivo` int(4) NOT NULL,
  `Linguaggio` int(4) NOT NULL,
  `Richiesta_Verbale` varchar(1) NOT NULL,
  `Motivo` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

 */

class motivi_annullamento
{
	public $ID;
	public $Tipo_Motivo;
	public $Linguaggio;
	public $Richiesta_Verbale;  //  R richiesta (campo "esecuzione impossibile")
								//  V verbale (campo "motivo annullamento")
								//  E entrambi
	public $Motivo;
	
	public function __construct ($progr)
	{
		if ($progr == NULL) return NULL;
		
		$queryMotivoAnnull = "SELECT * FROM motivi_annullamento ";
		$queryMotivoAnnull .= " WHERE ID = '" . $progr . "'";
		$resultMot = mysql_query($queryMotivoAnnull);
	
		while ($rigaTraduzione = mysql_fetch_assoc($resultMot))
		{
			foreach ($rigaTraduzione as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	public function CercoTipoDaId ($idd)
	{
		$queryMotivoAnnull = "SELECT Tipo_Motivo FROM motivi_annullamento ";
		$queryMotivoAnnull .= " WHERE ID = '" . $idd . "' ";
		$resultMot = mysql_query($queryMotivoAnnull);
	
		$rigaTipo = mysql_fetch_assoc($resultMot);
		return $rigaTipo['Tipo_Motivo'];
	}
	
	public function TrovoIdDaMotivo ($tipo, $lingua)
	{
		$queryMotivoAnnull = "SELECT ID FROM motivi_annullamento ";
		$queryMotivoAnnull .= " WHERE Tipo_Motivo = '" . $tipo . "' AND ";
		$queryMotivoAnnull .= " Linguaggio = '" . $lingua . "'  ";
		$resultMot = mysql_query($queryMotivoAnnull);
	
		$rigaId = mysql_fetch_assoc($resultMot);
		return $rigaId['ID'];
	}
	
	public function ListaRVE ($sceltaRVE)  //  rve R o V o E ;   prendo solo italiani
	{												//  aggiunta serve per selezionare il motivo esistente (se non c'è nella lista lo aggiunge??)
		$queryMotivoAnnull = "SELECT DISTINCT Richiesta_Verbale FROM motivi_annullamento ";
		$queryMotivoAnnull .= " ORDER BY Richiesta_Verbale ASC ";
		//echo "<br>" . $queryMotivoAnnull;
		$resultMot = mysql_query($queryMotivoAnnull);
	
		$options = "";
		while ($rigaTraduzione = mysql_fetch_assoc($resultMot))
		{
			$selezionato = "";
			if ($sceltaRVE == $rigaTraduzione['Richiesta_Verbale'])
			{
				$selezionato = " selected ";
			}
			switch ($rigaTraduzione['Richiesta_Verbale'])
			{
				case "R":
					$options .= "<option value='" . $rigaTraduzione['Richiesta_Verbale'] . "' " . $selezionato . " title='Richiesta'>Richiesta</option>\n";
					break;
				case "V":
					$options .= "<option value='" . $rigaTraduzione['Richiesta_Verbale'] . "' " . $selezionato . " title='Verbale'>Verbale</option>\n";
					break;
				case "E":
					$options .= "<option value='" . $rigaTraduzione['Richiesta_Verbale'] . "' " . $selezionato . " title='Richiesta/Verbale'>Entrambe</option>\n";
					break;
				default:
					$options .= "<option value='" . $rigaTraduzione['Richiesta_Verbale'] . "' " . $selezionato . " title='" . $rigaTraduzione['Richiesta_Verbale'] . "'>" . $rigaTraduzione['Richiesta_Verbale'] . "</option>\n";
					break;
			}
		}
		return $options;
	}
	
	public function ListaMotivi ($rve, $aggiunta)  //  rve R o V o E ;   prendo solo italiani
	{												//  aggiunta serve per selezionare il motivo esistente (se non c'è nella lista lo aggiunge??)
		$queryMotivoAnnull = "SELECT * FROM motivi_annullamento ";
		$queryMotivoAnnull .= " WHERE (Richiesta_Verbale = '" . $rve . "' OR Richiesta_Verbale = 'E') AND ";
		$queryMotivoAnnull .= " Linguaggio = '1'  ";
		$queryMotivoAnnull .= " ORDER BY Tipo_Motivo ASC ";
		//echo "<br>" . $queryMotivoAnnull;
		$resultMot = mysql_query($queryMotivoAnnull);
	
		$daAggiungere = true;
		$options = "";
		while ($rigaTraduzione = mysql_fetch_assoc($resultMot))
		{
			$selezionato = "";
			if ($aggiunta == $rigaTraduzione['Motivo'])
			{
				$daAggiungere = false;
				$selezionato = " selected ";
			}
			$options .= "<option value='" . $rigaTraduzione['ID'] . "' " . $selezionato . " title='" . $rigaTraduzione['Motivo'] . "'>" . $rigaTraduzione['Motivo'] . "</option>\n";
		}
		if ($daAggiungere == true)
		{
			$options .= "<option value='0' selected title='" . $aggiunta . ">" . $aggiunta . "</option>\n";
		}
		return $options;
	}
}

/*
CREATE TABLE `targhe_estere_lettere_annullamento` (
`ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
`Verbale_Progr` int(11) unsigned NOT NULL,
`File_Archiviazione` varchar(70) NOT NULL DEFAULT '',
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class targhe_estere_lettere_annullamento
{
	public $ID;
	public $Verbale_Progr;
	public $File_Archiviazione;
	
	public function __construct ($progr)
	{
		if ($progr == NULL) return NULL;
		
		$queryLettera = "SELECT * FROM targhe_estere_lettere_annullamento ";
		$queryLettera .= " WHERE ID = '" . $progr . "'";
		$resultLettera = mysql_query($queryLettera);
	
		while ($rigaLettera = mysql_fetch_assoc($resultLettera))
		{
			foreach ($rigaLettera as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}
	
	public function CercoTipoDaId ($idd)
	{
		/*$queryMotivoAnnull = "SELECT Tipo_Motivo FROM motivi_annullamento ";
		$queryMotivoAnnull .= " WHERE ID = '" . $idd . "' ";
		$resultMot = mysql_query($queryMotivoAnnull);
	
		$rigaTipo = mysql_fetch_assoc($resultMot);
		return $rigaTipo['Tipo_Motivo'];*/
	}
	
	function LetteraGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM targhe_estere_lettere_annullamento ";
		$queryCerca .= "WHERE Verbale_Progr = '" . $this->Verbale_Progr . "' ";
		$resCerca = mysql_query($queryCerca);
		if (mysql_num_rows($resCerca) > 0)
		{
			$rigaCerca = mysql_fetch_assoc($resCerca);
			return $rigaCerca['ID'];
		}
		else return NULL;
	}
	
	function InsertUpdateLettera ($forzato = NULL)  //  forzato è INSERT o UPDATE
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		if ($forzato == NULL)
		{
			if ($this->ID == NULL)
			{
				$progressivo = $this->LetteraGiaPresente();  //  progr o null
			}
			else $progressivo = $this->ID;
		}
		else if ($forzato == "UPDATE")
		{
			//$progressivo = $this->LetteraGiaPresente();  //  progr o null
			$progressivo = $this->ID;
			if ($progressivo == NULL) { alert ("In UPDATE FORZATO LETTERA, errore ID"); return; }
		}
		else if ($forzato == "INSERT")
		{
			$progressivo = NULL;
		}
		
		if ($progressivo == NULL)
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
			$risposta = $this->insert_lettera_locale($fields, $values);
			switch ($risposta)
			{
				case true: $risposta = "INSERT_OK"; break;
				case false: $risposta = "INSERT_ERROR"; break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_lettera_locale($progressivo, $fields, $values);
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
	
	public function insert_lettera_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_lettere_annullamento (" . $clause . ") VALUES (";
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
	
	public function update_lettera_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
		
		alert ($key);
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldLettera = new targhe_estere_lettere_annullamento($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldLettera->$fields_to_update[$i] != $values_to_update[$i])
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
	
		$query = "UPDATE targhe_estere_lettere_annullamento SET $clause WHERE ID = '" . $key . "'";
	
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
}

