<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class email_inviate
{
	public $ID;
	public $CC;
	public $Partita_ID;
	public $Utente_ID;
	public $Oggetto;
	public $Mail_Sorgente;
	public $Tipo_Sorgente;
	public $Mail_Destinatario;
	public $Tipo_Destinatario;
	public $Data_Invio;
	public $Ricevuta_Accettazione;
	public $Ricevuta_Consegna;
	
	public $Table_Collegata;
	public $ID_Collegato;
	
	public function __construct( $ID )
	{
		
		$query = "SELECT * FROM email_inviate WHERE ID = '".$ID."'";			
		
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
	
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Partita_ID = utf8_decode($val['Partita_ID']);
		$this->Utente_ID = utf8_decode($val['Utente_ID']);
		$this->Oggetto = utf8_decode($val['Oggetto']);
		$this->Mail_Sorgente = utf8_decode($val['Mail_Sorgente']);
		$this->Tipo_Sorgente = utf8_decode($val['Tipo_Sorgente']);
		$this->Mail_Destinatario = utf8_decode($val['Mail_Destinatario']);
		$this->Tipo_Destinatario = utf8_decode($val['Tipo_Destinatario']);
		$this->Data_Invio = utf8_decode($val['Data_Invio']);
		$this->Ricevuta_Accettazione = utf8_decode($val['Ricevuta_Accettazione']);
		$this->Ricevuta_Consegna = utf8_decode($val['Ricevuta_Consegna']);
		
		$this->ID_Collegato = utf8_decode($val['ID_Collegato']);
		$this->Table_Collegata = utf8_decode($val['Table_Collegata']);
	
	}
	
	public function trovaIdMail($oggetto)
	{
		$query = "SELECT ID FROM email_inviate WHERE Oggetto = '".$oggetto."'";
		$id = single_answer_query($query);
		return $id;
	}
	
	public function cartella_PEC()
	{
		$esplodi_oggetto = explode("_", $this->Oggetto);
		
		if($esplodi_oggetto[count($esplodi_oggetto)-1] == "NOT".$this->ID_Collegato)
		{
			$percorso_oggetto = $esplodi_oggetto[0];
				
			for($i=1;$i<count($esplodi_oggetto);$i++)
			{
				if($i==count($esplodi_oggetto)-1)
					break;
		
				$percorso_oggetto.= "_".$esplodi_oggetto[$i];
			}
			
		}
		else
		{
			$percorso_oggetto = $this->Oggetto;
		}
		
		return $percorso_oggetto;
	}
	
	public function percorsi_PEC()
	{
		$filename_inviato = $this->Oggetto.".eml";
		
		$filename_accettazione = "ACCETTAZIONE_".$this->Oggetto.".eml";
		$filename_mancata_accettazione = "AVVISO_DI_MANCATA_ACCETTAZIONE_".$this->Oggetto.".eml";
		$filename_consegna = "CONSEGNA_".$this->Oggetto.".eml";
		$filename_mancata_consegna = "AVVISO_DI_MANCATA_CONSEGNA_".$this->Oggetto.".eml";
		$filename_anomalia = "ANOMALIA_".$this->Oggetto.".eml";
		
		$percorso_oggetto = $this->cartella_PEC();
		
		$path_inviato = $this->percorsoMail($this->CC, $this->Tipo_Sorgente, $percorso_oggetto,"server")."/".$filename_inviato;
		
		$path_accettazione = $this->percorsoMail($this->CC, $this->Tipo_Sorgente, $percorso_oggetto,"server")."/".$filename_accettazione;
		$path_mancata_accettazione = $this->percorsoMail($this->CC, $this->Tipo_Sorgente, $percorso_oggetto,"server")."/".$filename_mancata_accettazione;
		$path_consegna = $this->percorsoMail($this->CC, $this->Tipo_Sorgente, $percorso_oggetto,"server")."/".$filename_consegna;
		$path_mancata_consegna = $this->percorsoMail($this->CC, $this->Tipo_Sorgente, $percorso_oggetto,"server")."/".$filename_mancata_consegna;
		$path_anomalia = $this->percorsoMail($this->CC, $this->Tipo_Sorgente, $percorso_oggetto,"server")."/".$filename_anomalia;
		
		$path = array();
		
		$path['inviato']['path'] = $path_inviato;
		$path['inviato']['filename'] = $filename_inviato;
		$path['accettazione']['path'] = $path_accettazione;
		$path['accettazione']['filename'] = $filename_accettazione;
		$path['mancata_accettazione']['path'] = $path_mancata_accettazione;
		$path['mancata_accettazione']['filename'] = $filename_mancata_accettazione;
		$path['consegna']['path'] = $path_consegna;
		$path['consegna']['filename'] = $filename_consegna;
		$path['mancata_consegna']['path'] = $path_mancata_consegna;
		$path['mancata_consegna']['filename'] = $filename_mancata_consegna;
		$path['anomalia']['path'] = $path_anomalia;
		$path['anomalia']['filename'] = $filename_anomalia;

		return $path;				
	}
		
	
	public function selezionaMailUtente($c, $Utente_ID, $ordinamento='DESC')
	{
		$stringa = "SELECT * FROM email_inviate WHERE CC='".$c."' AND Utente_ID ='".$Utente_ID."' ";
		$stringa.= "ORDER BY ID ".$ordinamento;
		
		$query = mysql_query($stringa);
		
		$results = array();
		
		while($line = mysql_fetch_array($query, MYSQL_ASSOC))
		{
			$results[] = $line;
		}
		
		return $results;
	}
	
	public function selezionaMailPartita($c,$Partita_ID, $ordinamento='DESC')
	{
		
		$stringa = "SELECT * FROM email_inviate WHERE CC='".$c."' AND Partita_ID ='".$Partita_ID."' ";
		$stringa.= "ORDER BY ID ".$ordinamento;
		
		$query = mysql_query($stringa);
		
		$results = array();
		
		while($line = mysql_fetch_array($query, MYSQL_ASSOC))
		{
			$results[] = $line;
		}
		
		return $results;
	}
	
	public function Delete ()
	{
		$query = "DELETE FROM email_inviate WHERE ID = '".$this->ID."' AND CC = '".$this->CC."'";
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
	
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
	
		$query = table_insert_record_query ("email_inviate", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false && is_array($value)===false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
	
		$query = table_update_record_query ("email_inviate", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
	
	/**
	 * 
	 * @param string $c
	 * @param string $tipo_mail
	 * @param string $oggetto
	 * @param string $ritorno (	$ritorno = "web" torna il percorso del file per accedere dal programma
	 *  						$ritorno = "server" torna il percorso completo del server )
	 * @return string
	 */
	public function percorsoMail($c, $tipo_mail,$oggetto, $ritorno = "web")
	{
		$path = $_SERVER['DOCUMENT_ROOT']."/archivio/Posta_Elettronica/".$c."/".$tipo_mail."/";
		$path.= $oggetto;
		
		if($ritorno=="server")
			return $path;
		else if($ritorno=="web")
			return mostra_file_path($path);
	}
		
}

?>