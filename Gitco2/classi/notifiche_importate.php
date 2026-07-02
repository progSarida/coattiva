<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once LIBRERIE . "/aiuto.php";

/*
 CREATE TABLE `notifiche_importate` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Tipo_Spedizione` varchar(30) NOT NULL DEFAULT '',
  `Tipo_Atto` varchar(30) NOT NULL DEFAULT '',
  `Riferimento` int(11) unsigned NOT NULL,
  `CC_Comune` varchar(5) NOT NULL DEFAULT '',
  `Num_Viol` varchar(20) NOT NULL DEFAULT '',
  `Rec_Nome` text,
  `Progressivo_Notifica` varchar(20) NOT NULL DEFAULT '',
  `Ms_Lotto` varchar(50) NOT NULL DEFAULT '',
  `Data_Notifica` date DEFAULT '0000-00-00',
  `Ms_Ric_Num` varchar(20) NOT NULL DEFAULT '',
  `Tipo_Notifica` varchar(50) NOT NULL DEFAULT '',
  `Stato_Notifica` varchar(50) NOT NULL DEFAULT '',
  `Note` varchar(50) NOT NULL DEFAULT '',
  `Immagine_Fronte` varchar(30) NOT NULL DEFAULT '',
  `Immagine_Retro` varchar(30) NOT NULL DEFAULT '',
  `Log_Modificato_Data` date DEFAULT '0000-00-00',
  `Log_Modificato_Ora` time DEFAULT '00:00:00',
  `Ms_Rac_Num` varchar(20) NOT NULL DEFAULT '',
  `Data_Spedizione` date DEFAULT '0000-00-00',
  `Scatola` varchar(5) NOT NULL DEFAULT '',
  `Lotto` varchar(5) NOT NULL DEFAULT '',
  `Posizione` varchar(5) NOT NULL DEFAULT '',
  `Nome_File` varchar(20) NOT NULL DEFAULT '',
  `Data_Importazione` date DEFAULT NULL,
  `Operatore` varchar(50) NOT NULL,
  
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/

class notifiche_importate
{
	public $ID;
	public $Tipo_Spedizione;
	public $Tipo_Atto;
	public $Riferimento;
	public $CC_Comune;
	public $Num_Viol;
	public $Rec_Nome;
	public $Progressivo_Notifica;
	public $Ms_Lotto;
	public $Data_Notifica;
	public $Ms_Ric_Num;
	public $Tipo_Notifica;
	public $Stato_Notifica;
	public $Note;
	public $Immagine_Fronte;
	public $Immagine_Retro;
	public $Log_Modificato_Data;
	public $Log_Modificato_Ora;
	public $Ms_Rac_Num;
	public $Data_Spedizione;
	public $Scatola;
	public $Lotto;
	public $Posizione;
	public $Nome_File;
	public $Data_Importazione;
	public $Operatore;

    public $FlowId;
    public $DocumentId;
    public $PrintTypeId;
    public $DocumentTypeId;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM notifiche_importate WHERE ID = '" . $progr . "'";
		$result = mysql_query($query);
		$rigaNotif = mysql_fetch_assoc($result);

		foreach ($rigaNotif as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	function NotifImportataGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM notifiche_importate ";
		$queryCerca .= "WHERE DocumentId = ".$this->DocumentId." AND DocumentTypeId = ".$this->DocumentTypeId." ";
		$queryCerca .= "AND CC_Comune = '" . $this->CC_Comune . "' AND Num_Viol = '" . $this->Num_Viol . "'";
		if($this->DocumentId>0){
            $resCerca = mysql_query($queryCerca);
            $rigaCerca = mysql_fetch_assoc($resCerca);
            return $rigaCerca['ID'];
        }
        else
            return null;

	}
	
	function CercaRiferimento ($CC_Comune, $tipo_atto, $progrNotifica, $dataNotifica = null)
	{
		$queryCerca = "SELECT ID FROM notifiche_importate ";
		$queryCerca .= "WHERE CC_Comune = '".$CC_Comune."' AND Tipo_Atto = '".$tipo_atto."' AND Riferimento = '" . $progrNotifica . "' ";
		if(to_mysql_date($dataNotifica)!=null)
		    $queryCerca .= "AND Data_Notifica = '".to_mysql_date($dataNotifica)."'";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdateNotifImportata ($forzoInsertUpdate = null)  //  INSERT o UPDATE o null
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		if ($this->Data_Notifica == "") $this->Data_Notifica = "0000-00-00";
		if ($this->Data_Spedizione == "") $this->Data_Spedizione = "0000-00-00";
		
		
		$this->Data_Importazione = date('Y-m-d');
		$this->Operatore = $_SESSION['username'];
		
		if ($forzoInsertUpdate != null)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else
		{
			$this->ID = $this->NotifImportataGiaPresente();
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
			$risposta = $this->insert_notif_importata_locale($fields, $values);
			switch ($risposta)
			{
				case true:
					$risposta = "INSERT_OK";
					$questaNot = $this->NotifImportataGiaPresente();
					break;
				case false:
					$risposta = "INSERT_ERROR";
					break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_notif_importata_locale($this->ID, $fields, $values);
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
	
	public function insert_notif_importata_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO notifiche_importate (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "\"" . $values_to_insert[$i] . "\"";
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
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_insert[$i] . " = \"" . $values_to_insert[$i] . "\"";
			}
			echo "<br>" . $query . "<br>";
			return true;
		}
		return true;
	}
	
	public function update_notif_importata_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return 0;
	
		if ($key == 0 || $key == '0' || $key == NULL) return 1;
	
		$myOldNotifImportata = new notifiche_importate($key);
		
		$vaiAvanti = false;
		if ($myOldNotifImportata->Log_Modificato_Data == "0000-00-00")
		{
			if ($this->Log_Modificato_Data == "0000-00-00" || $this->Log_Modificato_Data == "")
				$vaiAvanti = true;
		}
		
		if ($vaiAvanti == false)
		{
			// faccio UPDATE solo se � pi� recente di quello memorizzato!
			if ($myOldNotifImportata->Log_Modificato_Data > $this->Log_Modificato_Data) return 2;
			if (
				$myOldNotifImportata->Log_Modificato_Data == $this->Log_Modificato_Data &&
				$myOldNotifImportata->Log_Modificato_Ora > $this->Log_Modificato_Ora
				)
			{
				return 2;
			}
		}
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldNotifImportata->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				{
					$clause .= $fields_to_update[$i] . "=\"" .$values_to_update[$i]. "\" , ";
				}
			}
			//if ($_SESSION['CC_User'] == "***+")
				//echo ("<br>" . $myOldNotifImportata->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return 3;  // non updata nulla, perch� sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE notifiche_importate SET $clause WHERE ID = " . $key . "";
		
		//$_SESSION['CC_User'] != "***+"
		if (1)
		{
			if (mysql_query($query) != NULL) return 4;
			else return 5;
		}
		else
		{
			for ($i = 0; $i < $dim1; $i++)
			{
				echo "<br>" . $fields_to_update[$i] . " = \"" . $values_to_update[$i] . "\" (" . $myOldNotifImportata->$fields_to_update[$i] . ")";
			}
			echo "<br>" . $query . "<br>";
			/*if (mysql_query($query) != NULL) return TRUE;
			else return FALSE;*/
			return 4;
		}
	}
}

