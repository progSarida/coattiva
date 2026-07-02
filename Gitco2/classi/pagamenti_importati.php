<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once LIBRERIE . "/aiuto.php";

/*
 CREATE TABLE `pagamenti_importati` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Tipo_Pagamento` varchar(50) NOT NULL DEFAULT '',
  `Riferimento_Atto` int(11) unsigned NOT NULL,
  `Comune_Riferimento` varchar(5) NOT NULL DEFAULT '',
  `Conto_Corrente` varchar(50) NOT NULL DEFAULT '',
  `Provincia_Posta` varchar(5) NOT NULL DEFAULT '',
  `Data_Caricamento` date DEFAULT '0000-00-00',
  `Data_Pagamento` date DEFAULT '0000-00-00',
  `Codice_Provincia_Posta` varchar(10) NOT NULL DEFAULT '',
  `Codice_Ufficio_Posta` varchar(10) NOT NULL DEFAULT '',
  `Codice_Txt_Composto` varchar(20) NOT NULL DEFAULT '',
  `Telematico` varchar(2) NOT NULL DEFAULT '',
  `Tipo_Bollettino` varchar(5) NOT NULL DEFAULT '',
  `Importo_Pagato` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Data_Preallibramento` date DEFAULT '0000-00-00',
  `Data_Postallibramento` date DEFAULT '0000-00-00',
  `Quinto_Campo` varchar(25) NOT NULL DEFAULT '',
  `Progressivo_Marcaggio` varchar(10) NOT NULL DEFAULT '',
  `Conto_Traente` varchar(10) NOT NULL DEFAULT '',
  `Divisa_Pagamento` varchar(3) NOT NULL DEFAULT '',
  `Divisa_Txt_Pagamento` varchar(4) NOT NULL DEFAULT '',
  `Flag_Insanabili` varchar(2) NOT NULL DEFAULT '',
  `Progressivo_Selezione` varchar(10) NOT NULL DEFAULT '',
  `Report_Version` varchar(50) NOT NULL DEFAULT '',
  `SV` varchar(3) NOT NULL DEFAULT '',
  `Immagine_Fronte` varchar(60) NOT NULL DEFAULT '',
  `Immagine_Retro` varchar(60) NOT NULL DEFAULT '',
  `Codice_Txt_Sconosciuto` varchar(4) NOT NULL DEFAULT '',
  `Tipo_Txt_Bollettino` varchar(4) NOT NULL DEFAULT '',
  `Sostitutivo_Txt` varchar(2) NOT NULL DEFAULT '',
  `Esito` varchar(15) NOT NULL DEFAULT '',
  `Nome_File` varchar(100) NOT NULL DEFAULT '',
  `Data_Importazione` date DEFAULT '0000-00-00',
  `Operatore` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class pagamenti_importati
{
	public $ID;
	public $Tipo_Pagamento;
	public $Riferimento_Atto;
	public $Comune_Riferimento;
	public $Conto_Corrente;
	public $Provincia_Posta;
	public $Data_Caricamento;
	public $Data_Pagamento;
	public $Codice_Provincia_Posta;
	public $Codice_Ufficio_Posta;
	public $Codice_Txt_Composto;
	public $Telematico;
	public $Tipo_Bollettino;
	public $Importo_Pagato;
	public $Data_Preallibramento;
	public $Data_Postallibramento;
	public $Quinto_Campo;
	public $Progressivo_Marcaggio;
	public $Conto_Traente;
	public $Divisa_Pagamento;
	public $Divisa_Txt_Pagamento;
	public $Flag_Insanabili;
	public $Progressivo_Selezione;
	public $Report_Version;
	public $SV;
	public $Immagine_Fronte;
	public $Immagine_Retro;
	public $Codice_Txt_Sconosciuto;
	public $Tipo_Txt_Bollettino;
	public $Sostitutivo_Txt;
	public $Esito;
	public $Nome_File;
	public $Data_Importazione;
	public $Operatore;
	public $DocumentTypeId;
	public $DocumentTableTypeId;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaNotif = mysql_fetch_assoc($result);

		foreach ($rigaNotif as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	public function TipiPagamento ($selected, $uscita)  //  uscita pu� essere:
																// SELECT
																// TIPODASCRITTA
																// SCRITTADATIPO
	{
		// elenco tipi accettati (promemoria)
		// da aggiornare man mano che si hanno nuovi Tipi
		$arrayTipi = array(
//				"VERBALE_CDS",
//				"SOLLECITO_CDS",
				"SOLL_PRE",
                "AVV_MORA",
				"INGIUNZIONE_CDS",
				"SOLLECITO_INGIUNZIONE_CDS",
				"AVVISO_INTIMAZIONE_CDS",
//				"SOLLECITO_AVVISO_INTIMAZIONE_CDS",
				"PIGNORAMENTO_VEICOLO_CDS",
				"PIGNORAMENTO_DATORE_LAVORO_CDS",
				"PIGNORAMENTO_BANCA_CDS"
				);
		$arrayScritte = array(
//				"Verbale",
//				"Sollecito",
                "Sollecito pre ingiunzione",
                "Avviso di messa in mora",
				"Ingiunzione",
				"Sollecito ingiunzione",
				"Avviso di intimazione ad adempiere",
//				"Sollecito di avviso di intimazione ad adempiere",
				"Pignoramento beni mobili registrati",
				"Pignoramento presso datore di lavoro",
				"Pignoramento presso banca"
				);
		$arrayTipiPigno = array(
//				"Verbale",
//				"Sollecito",
                "Sollecito pre ingiunzione",
                "Avviso di messa in mora",
                "Ingiunzione",
				"Sollecito ingiunzione",
				"Avviso di intimazione ad adempiere",
//				"Sollecito di avviso di intimazione ad adempiere",
				"veicolo",
				"terzi",
				"terzi"
		);
		
		switch ($uscita)
		{
			case "SELECT":
				$esitoFunction = "<option value=''></option>\n";
				for ($i = 0; $i < count($arrayTipi); $i++)
				{
					if ($selected == $arrayTipi[$i]) $selTag = " selected ";
					else $selTag = "";
					$esitoFunction .= "<option value='" . $arrayTipi[$i] . "' " . $selTag . ">" . $arrayScritte[$i] . "</option>\n";
				}
				break;
			case "TIPODASCRITTA":
				$esitoFunction = "";
				for ($i = 0; $i < count($arrayScritte); $i++)
				{
					if ($selected == $arrayScritte[$i])
					{
						$esitoFunction = $arrayTipi[$i];
						break;
					}
				}
				break;
			case "SCRITTADATIPO":
				$esitoFunction = "";
				for ($i = 0; $i < count($arrayTipi); $i++)
				{
					if ($selected == $arrayTipi[$i])
					{
						$esitoFunction = $arrayScritte[$i];
						break;
					}
				}
				break;
			case "SCRITTADATIPOPIGNO":
				$esitoFunction = "";
				for ($i = 0; $i < count($arrayTipi); $i++)
				{
					if ($selected == $arrayTipi[$i])
					{
						$esitoFunction = $arrayTipiPigno[$i];
						break;
					}
				}
				break;
			default:
				$esitoFunction = "Errore scelta: " . $uscita;
				break;
		}
		//alert ($esitoFunction . " e " . $selected);
		return $esitoFunction;
	}
	
	public function ListaPagamentiDaBonificare ($telematico)  // telematico: Y o N
	{
		$querySel = "SELECT ID FROM pagamenti_importati ";
		$querySel .= " WHERE Esito = 'DABONIFICARE' AND ";
		$querySel .= " Telematico = '$telematico' ";
		$resCerca = mysql_query($querySel);
		
		$arrayDaBonificare = array();
		while ($rigaCerca = mysql_fetch_assoc($resCerca))
		{
			$arrayDaBonificare[] = $rigaCerca['ID'];
		}
		return $arrayDaBonificare;
	}
	
	function PagamImportatoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM pagamenti_importati ";
		$queryCerca .= "WHERE Quinto_Campo = '" . $this->Quinto_Campo . "' AND ";
		$queryCerca .= "Importo_Pagato = '" . $this->Importo_Pagato . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	/*function CercaRiferimento ($CC_Comune, $tipo_atto, $progrNotifica)
	{
		$queryCerca = "SELECT ID FROM pagamenti_importati ";
		$queryCerca .= "WHERE CC_Comune = '".$CC_Comune."' AND Tipo_Atto = '".$tipo_atto."' AND Riferimento = '" . $progrNotifica . "'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}*/
	
	function InsertUpdatePagamImportato ($forzoInsertUpdate = null)  //  INSERT o UPDATE o null
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		$this->Data_Importazione = date('Y-m-d');
		$this->Operatore = $_SESSION['username'];
		
		if ($forzoInsertUpdate != null)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else
		{
			$this->ID = $this->PagamImportatoGiaPresente();
			if ($this->ID == NULL)
			{
				$insUpd = "INSERT";
			}
			else
			{
				$insUpd = "UPDATE";
			}
		}
	
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "ID")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		
		$questaNot = "";
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_pagam_importato_locale($fields, $values);
			switch ($risposta)
			{
				case true:
					$risposta = "INSERT_OK";
					$questaNot = $this->PagamImportatoGiaPresente();
					break;
				case false:
					$risposta = "INSERT_ERROR";
					break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_pagam_importato_locale($this->ID, $fields, $values);
			switch ($risposta)
			{
				case 0:
					$risposta = "DIMENSIONI_ERRATE";
					break;
				case 1:
					$risposta = "ID_VUOTO";
					break;
				case 2:
					$risposta = "LOG_ANTECEDENTE";
					break;
				case 3:
					$risposta = "CAMPI_UGUALI";
					break;
				case 4:
					$risposta = "UPDATE_OK";
					$questaNot = $this->ID;
					break;
				case 5:
					$risposta = "UPDATE_ERROR";
					break;
				default:
					$risposta = "SCONOSCIUTO_UPDATE";
					break;
			}
		}
		else $risposta = "INSERT_ERROR";
		//$risposta .= "**" . $questaNot;
		return $risposta;
	}
	
	public function insert_pagam_importato_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO pagamenti_importati (" . $clause . ") VALUES (";
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
	
	public function update_pagam_importato_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return 0;
	
		if ($key == 0 || $key == '0' || $key == NULL) return 1;
	
		$myOldPagamImportato = new pagamenti_importati($key);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldPagamImportato->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return 3;  // non updata nulla, perch� sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$daNonEseguire = "UPDATE pagamenti_importati SET Data_Importazione";
	
		$query = "UPDATE pagamenti_importati SET $clause WHERE ID = '" . $key . "'";
		
		if ($daNonEseguire == substr($query, 0, strlen($daNonEseguire))) return 3;
		
		//echo "<br>" . $query;
		
		if (mysql_query($query) != NULL) return 4;
		else return 5;
	
		echo "<br>" . $query;
	
		return 4;
	}
}

