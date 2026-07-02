<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

class ispezioni
{
	public $ID;
	public $CC;
	public $Utente_ID;
	public $Data_Inserimento;
	public $Data_Ispezione;
	public $Tipo;
	public $Denominazione;
	public $Contenuto;
	public $Note;

	public function __construct( $progr , $c )
	{
		$query = "SELECT * FROM ispezioni WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Utente_ID = utf8_decode($val['Utente_ID']);
		$this->Denominazione = utf8_decode($val['Denominazione']);
		$this->Data_Inserimento = utf8_decode($val['Data_Inserimento']);
		$this->Data_Ispezione = utf8_decode($val['Data_Ispezione']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Contenuto = utf8_decode($val['Contenuto']);
		$this->Note = utf8_decode($val['Note']);

	}

	public function array_ispezioni( $utente_id , $tipo = null )
	{
		if( $tipo == null )
			$val = select_mysql_array( "*" , "ispezioni" , "Utente_ID = '".$utente_id."'" );
		else
			$val = select_mysql_array( "*" , "ispezioni" , "Utente_ID = '".$utente_id."' AND Tipo = '".$tipo."'" );

		return $val;
	}
	
	public function Delete ()
	{				
		$query = "DELETE FROM ispezioni WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);
		
		return $ctrl_query;
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
		
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		
		$query = table_insert_record_query ("ispezioni", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_update_record_query ("ispezioni", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);
		
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class pignoramento
{
	public $ID;
	public $Comune_ID;
	public $CC;
	public $Partita_ID;
	public $Atto_ID;
	public $Anno_Cronologico;
	public $ID_Cronologico;
	public $Data_Elaborazione;
	public $Tipo;
	public $Tipo_Terzi;
	
	public $Data_Stampa;
	public $Stato_Stampa;
	public $Data_Flusso;
	public $Numero_Flusso;
	public $Anno_Flusso;
	
	public $Data_Consegna;
	public $Tipo_Ufficiale;
	public $Modalita_Stampa;
	public $Modalita_Stampa_Debitore;
	public $Data_Spedizione;
	
	public $Data_Notifica;
	public $Stato_Notifica;
	public $Indirizzo_Validato;
	public $Motivo_Notifica;
	public $Modalita_Notifica;
	public $Note_Notifica;
	public $Rielabora_Flag;
		
	public $Spese_Notifica;
	public $CAN;
	public $CAD;
	public $Importo_Dovuto;
	public $Spese_Notifica_Debitore;
	public $Spese_Notifica_Terzi;
	public $Totale_Spese_Notifica;
	public $Totale_Spese_Accessorie;
	public $Totale_Dovuto;
	public $Note;
	public $Rate_Previste;
	public $Importi_Rate;
	public $Scadenze_Rate;
	
	public $Spese_Pignoramento;
	
	public $Presso_Terzi = array();
	public $Mobiliare = array();
	public $Immobiliare = array();
	public $Beni = array();
	
	public $Veicolo;

	public function __construct( $progr , $c )
	{
		$query = "SELECT * FROM pignoramento_generale WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Partita_ID = utf8_decode($val['Partita_ID']);
		$this->Atto_ID = utf8_decode($val['Atto_ID']);
		$this->Anno_Cronologico = utf8_decode($val['Anno_Cronologico']);
		$this->ID_Cronologico = utf8_decode($val['ID_Cronologico']);
		$this->Data_Elaborazione = utf8_decode($val['Data_Elaborazione']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Tipo_Terzi = utf8_decode($val['Tipo_Terzi']);
		$this->Data_Stampa = utf8_decode($val['Data_Stampa']);
		$this->Stato_Stampa = utf8_decode($val['Stato_Stampa']);
		$this->Data_Flusso = utf8_decode($val['Data_Flusso']);
		$this->Numero_Flusso = utf8_decode($val['Numero_Flusso']);
		$this->Anno_Flusso = utf8_decode($val['Anno_Flusso']);
		
		$this->Data_Consegna = utf8_decode($val['Data_Consegna']);
		$this->Tipo_Ufficiale = utf8_decode($val['Tipo_Ufficiale']);
		$this->Modalita_Stampa = utf8_decode($val['Modalita_Stampa']);
		$this->Modalita_Stampa_Debitore = utf8_decode($val['Modalita_Stampa_Debitore']);
		$this->Data_Spedizione = utf8_decode($val['Data_Spedizione']);
		
		$this->Data_Notifica = utf8_decode($val['Data_Notifica']);
		$this->Stato_Notifica = utf8_decode($val['Stato_Notifica']);
		$this->Indirizzo_Validato = utf8_decode($val['Indirizzo_Validato']);
		$this->Motivo_Notifica = utf8_decode($val['Motivo_Notifica']);
		$this->Modalita_Notifica = utf8_decode($val['Modalita_Notifica']);
		$this->Note_Notifica = utf8_decode($val['Note_Notifica']);
		$this->Rielabora_Flag = utf8_decode($val['Rielabora_Flag']);
		
		$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
		$this->CAN = utf8_decode($val['CAN']);
		$this->CAD = utf8_decode($val['CAD']);
		$this->Importo_Dovuto = utf8_decode($val['Importo_Dovuto']);
		$this->Spese_Notifica_Debitore = utf8_decode($val['Spese_Notifica_Debitore']);
		$this->Spese_Notifica_Terzi = utf8_decode($val['Spese_Notifica_Terzi']);
		$this->Totale_Spese_Notifica = utf8_decode($val['Totale_Spese_Notifica']);
		$this->Totale_Spese_Accessorie = utf8_decode($val['Totale_Spese_Accessorie']);
		$this->Totale_Dovuto = utf8_decode($val['Totale_Dovuto']);
		$this->Note = utf8_decode($val['Note']);
		$this->Rate_Previste = utf8_decode($val['Rate_Previste']);
		$this->Importi_Rate = utf8_decode($val['Importi_Rate']);
		$this->Scadenze_Rate = utf8_decode($val['Scadenze_Rate']);
		
		$this->Spese_Pignoramento = new spese_pignoramento($progr, $c);
				
		switch($this->Tipo)
		{
			case 'terzi':
				
				$terzi_id = select_mysql_array("ID", "pignoramento_presso_terzi" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'");
				
				for( $i=0; $i<count($terzi_id); $i++ )
				{
					$this->Presso_Terzi[$i] = new pignoramento_presso_terzi( $terzi_id[$i]['ID'] , $c );
				}				
				
				break;
				
			case 'mobiliare':
				
				$this->Mobiliare = null;
				
				break;
				
			case 'immobiliare':
				
				$this->Immobiliare = null;
				
				break;
				
			case 'beni':
				
				$this->Beni = null;
			
				break;
				
			case 'veicolo':
				
				$veicolo_id = select_mysql_array("ID", "pignoramento_veicolo" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'");
				
				$this->Veicolo = new pignoramento_veicolo( $veicolo_id[0]['ID'] , $c );
						
				break;
		}
		
	}	

	public function array_pignoramenti( $partita_ID , $tipo = null )
	{
		if( $tipo == null )
			$val = select_mysql_array( "*" , "pignoramento_generale" , "Partita_ID = '".$partita_ID."'" );
		else
			$val = select_mysql_array( "*" , "pignoramento_generale" , "Partita_ID = '".$partita_ID."' AND Tipo = '".$tipo."'" );

		return $val;
	}

	public function Delete ()
	{
		$query = "DELETE FROM pignoramento_generale WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
				if ($key != "ID" && isset($value) != false && $key!="Spese_Pignoramento" && $key!="Veicolo")
				{
					$fields[] = $key;
					$values[] = utf8_decode($value);
				}
			}
		}

		$query = table_insert_record_query ("pignoramento_generale", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
			if ($key != $campo && isset($value) != false && $key!="Spese_Pignoramento" && $key!="Veicolo")
			{
				$fields[] = $key;
				$values[] = utf8_decode($value);
			}
			}
		}

		$query = table_update_record_query ("pignoramento_generale", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
	public function info_spedizione()
	{
		$tipo_atto = "PIGNORAMENTO";
	
		$notifiche = new notifiche_importate(null);
		$id_notifica = $notifiche->CercaRiferimento($this->CC, $tipo_atto, $this->ID);
		unset($notifiche);
	
		$spedizione = new notifiche_importate($id_notifica);
		
	
		return $spedizione;
	}
	
	public function query_selezione_pignoramenti( $c, $tipo_pigno = null, $tipo_terzi = null, $order = null, $where = null )
	{
		/**
			Query selezione pignoramenti
			
			@param $c(string) = Codice catastale;
			@param $tipo_pigno(string) = Tipo di pignoramento;
			@param $where(array) = lista di where (facoltativo);
		 */

		
		$query = "SELECT DISTINCT PIG_GEN.ID, PIG_GEN.Partita_ID ";
		$query.= "FROM pignoramento_generale AS PIG_GEN, tributo AS TR ";
		
		if($order=="alfabetico")
		{
		
			$query.= ", partita_tributi AS PAR, ";
			$query.="(	( SELECT utente.ID, utente.Cognome AS NOME_UTENTE, utente.Nome FROM utente  ";
			$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere != 'D' ) ";
			$query.="UNION ";
			$query.="( SELECT utente.ID, utente.Ditta AS NOME_UTENTE, utente.Nome FROM utente  ";
			$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere = 'D' )	) ";
			$query.="AS UNIONE_UTENTE ";
		
		}
		
		$query.= "WHERE PIG_GEN.CC = '".$c."' AND TR.Partita_ID = PIG_GEN.Partita_ID";
		
		if($tipo_pigno!=null)
			$query.= " AND PIG_GEN.Tipo = '".$tipo_pigno."' ";
		
		if($tipo_pigno=="terzi" && $tipo_terzi!=null)
		{
			$query.= " AND PIG_GEN.Tipo_Terzi = '".$tipo_terzi."' ";
		}
		
		if($order=="alfabetico")
			$query.= " AND PAR.ID = PIG_GEN.partita_ID AND UNIONE_UTENTE.ID = PAR.Utente_ID ";	
		
		if($where != null)
			for($i=0;$i<count($where);$i++)
				if($where[$i]!=null)
					$query.= " AND (".$where[$i].")";
		
		$query.= " ORDER BY ";
		
		if($order=="verbale")
			$query.= "ABS(tributo.Titolo_Sanzione) ASC, ";
		if($order=="info")
			$query.= "atto.Info_Cartella ASC, ";
		if($order=="alfabetico")
			$query.= "UNIONE_UTENTE.NOME_UTENTE ASC , UNIONE_UTENTE.Nome ASC, ";
		
		$query.= "PIG_GEN.Anno_Cronologico ASC, PIG_GEN.ID_Cronologico ASC, PIG_GEN.Comune_ID ASC";
		
		return $query;
	}
}

class pignoramento_presso_terzi
{
	
	public $ID;
	public $CC;
	public $Pignoramento_ID;
	public $Terzo_ID;
	public $Azienda;
	public $Dati_Terzo;
	public $Fonte_Dati;
	public $Note;
	
	public $Data_Notifica;
	public $Stato_Notifica;
	public $Motivo_Notifica;
	public $Modalita_Notifica;
	public $Modalita_Stampa;
	public $Note_Notifica;
	
	public $Spese_Notifica;
	public $CAN;
	public $CAD;
	public $Tipo_Terzi;
	public $Tipo_Contratto_Lavoro;
	public $Data_Costituzione_Ditta_Lavoro;
	public $Data_Ditta_Operativa_Lavoro;
	public $Data_Dipendenze_Lavoro;
	public $Tipo_Titolo_Banca;
	public $Titolo_Banca;
	public $Intestatario_Banca;
	public $Coointestatari_Banca;
	public $Tipo_Pensione_Inps;
	public $Libretto_Inps;
	public $Tipo_Titolo_Altro;
	public $Titolo_Altro;
	public $Tipo_Credito_Altro;
	public $Data_Emissione_Altro;
	public $Data_Scadenza_Altro;

	public function __construct( $progr , $c )
	{
		$query = "SELECT * FROM pignoramento_presso_terzi WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Pignoramento_ID = utf8_decode($val['Pignoramento_ID']);
		$this->Terzo_ID = utf8_decode($val['Terzo_ID']);
		$this->Azienda = utf8_decode($val['Azienda']);
		
		$this->Dati_Terzo = new utente($this->Terzo_ID, $c);		
		
		$this->Fonte_Dati = utf8_decode($val['Fonte_Dati']);
		$this->Note = utf8_decode($val['Note']);
		
		$this->Data_Notifica = utf8_decode($val['Data_Notifica']);
		$this->Stato_Notifica = utf8_decode($val['Stato_Notifica']);
		$this->Motivo_Notifica = utf8_decode($val['Motivo_Notifica']);
		$this->Modalita_Notifica = utf8_decode($val['Modalita_Notifica']);
		$this->Modalita_Stampa = utf8_decode($val['Modalita_Stampa']);
		$this->Note_Notifica = utf8_decode($val['Note_Notifica']);
		
		$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
		$this->CAN = utf8_decode($val['CAN']);
		$this->CAD = utf8_decode($val['CAD']);
		$this->Tipo_Terzi = utf8_decode($val['Tipo_Terzi']);
		$this->Tipo_Contratto_Lavoro = utf8_decode($val['Tipo_Contratto_Lavoro']);
		$this->Data_Costituzione_Ditta_Lavoro = utf8_decode($val['Data_Costituzione_Ditta_Lavoro']);
		$this->Data_Ditta_Operativa_Lavoro = utf8_decode($val['Data_Ditta_Operativa_Lavoro']);
		$this->Data_Dipendenze_Lavoro = utf8_decode($val['Data_Dipendenze_Lavoro']);
		$this->Tipo_Titolo_Banca = utf8_decode($val['Tipo_Titolo_Banca']);
		$this->Titolo_Banca = utf8_decode($val['Titolo_Banca']);
		$this->Intestatario_Banca = utf8_decode($val['Intestatario_Banca']);
		$this->Coointestatari_Banca = utf8_decode($val['Coointestatari_Banca']);
		$this->Tipo_Pensione_Inps = utf8_decode($val['Tipo_Pensione_Inps']);
		$this->Libretto_Inps = utf8_decode($val['Libretto_Inps']);
		$this->Tipo_Titolo_Altro = utf8_decode($val['Tipo_Titolo_Altro']);
		$this->Titolo_Altro = utf8_decode($val['Titolo_Altro']);
		$this->Tipo_Credito_Altro = utf8_decode($val['Tipo_Credito_Altro']);
		$this->Data_Emissione_Altro = utf8_decode($val['Data_Emissione_Altro']);
		$this->Data_Scadenza_Altro = utf8_decode($val['Data_Scadenza_Altro']);

	}

	public function Delete ()
	{
		$query = "DELETE FROM pignoramento_presso_terzi WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
			if ($key != "ID" && isset($value) != false && $key!="Dati_Terzo")
			{
				$fields[] = $key;
				$values[] = utf8_decode($value);
			}
			}
		}

		$query = table_insert_record_query ("pignoramento_presso_terzi", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
			if ($key != $campo && isset($value) != false && $key!="Dati_Terzo")
			{
				$fields[] = $key;
				$values[] = utf8_decode($value);
			}
			}
		}

		$query = table_update_record_query ("pignoramento_presso_terzi", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class spese_pignoramento
{

	public $ID;
	public $CC;
	public $Pignoramento_ID;
	public $Incremento_Percentuale;
	public $Spesa_1_ID;
	public $Tipo_Spesa_1;
	public $Extra_Spesa_1;
	public $Rimborso_1;
	public $Spesa_2_ID;
	public $Tipo_Spesa_2;
	public $Extra_Spesa_2;
	public $Rimborso_2;
	public $Spesa_3_ID;
	public $Tipo_Spesa_3;
	public $Extra_Spesa_3;
	public $Rimborso_3;
	public $Spesa_4_ID;
	public $Tipo_Spesa_4;
	public $Extra_Spesa_4;
	public $Rimborso_4;
	public $Spesa_5_ID;
	public $Tipo_Spesa_5;
	public $Extra_Spesa_5;
	public $Rimborso_5;
	public $Totale_Rimborso;
	
	public function __construct( $pignoramento_id , $c )
	{
		
		$query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = '".$pignoramento_id."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Pignoramento_ID = utf8_decode($val['Pignoramento_ID']);
		$this->Incremento_Percentuale = utf8_decode($val['Incremento_Percentuale']);
		$this->Spesa_1_ID = utf8_decode($val['Spesa_1_ID']);
		$this->Tipo_Spesa_1 = utf8_decode($val['Tipo_Spesa_1']);
		$this->Extra_Spesa_1 = utf8_decode($val['Extra_Spesa_1']);
		$this->Rimborso_1 = utf8_decode($val['Rimborso_1']);
		$this->Spesa_2_ID = utf8_decode($val['Spesa_2_ID']);
		$this->Tipo_Spesa_2 = utf8_decode($val['Tipo_Spesa_2']);
		$this->Extra_Spesa_2 = utf8_decode($val['Extra_Spesa_2']);
		$this->Rimborso_2 = utf8_decode($val['Rimborso_2']);
		$this->Spesa_3_ID = utf8_decode($val['Spesa_3_ID']);
		$this->Tipo_Spesa_3 = utf8_decode($val['Tipo_Spesa_3']);
		$this->Extra_Spesa_3 = utf8_decode($val['Extra_Spesa_3']);
		$this->Rimborso_3 = utf8_decode($val['Rimborso_3']);
		$this->Spesa_4_ID = utf8_decode($val['Spesa_4_ID']);
		$this->Tipo_Spesa_4 = utf8_decode($val['Tipo_Spesa_4']);
		$this->Extra_Spesa_4 = utf8_decode($val['Extra_Spesa_4']);
		$this->Rimborso_4 = utf8_decode($val['Rimborso_4']);
		$this->Spesa_5_ID = utf8_decode($val['Spesa_5_ID']);
		$this->Tipo_Spesa_5 = utf8_decode($val['Tipo_Spesa_5']);
		$this->Extra_Spesa_5 = utf8_decode($val['Extra_Spesa_5']);
		$this->Rimborso_5 = utf8_decode($val['Rimborso_5']);
		$this->Totale_Rimborso = utf8_decode($val['Totale_Rimborso']);

	}

	public function Delete ()
	{
		$query = "DELETE FROM pignoramento_spese WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false )
			{
				$fields[] = $key;
				$values[] = utf8_decode($value);
			}
		}

		$query = table_insert_record_query ("pignoramento_spese", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = utf8_decode($value);
			}
		}

		$query = table_update_record_query ("pignoramento_spese", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}


class pignoramento_veicolo
{

	public $ID;
	public $CC;
	public $Pignoramento_ID;
	public $Marca_Veicolo;
	public $Modello_Veicolo;
	public $Targa_Veicolo;
	public $Tipo_Veicolo;
	public $Data_Visura;
	public $Portata_Veicolo;
	public $Valore_Veicolo;
	public $Anno_Immatricolazione;
	public $Data_Notifica_Istituto;
	public $Stato_Notifica_Istituto;
	public $Motivo_Notifica_Istituto;
	public $Modalita_Notifica_Istituto;
	public $Spese_Notifica_Istituto;
	public $CAN_Istituto;
	public $CAD_Istituto;
	public $Modalita_Stampa_Istituto;

	public function __construct( $progr , $c )
	{
		$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Pignoramento_ID = utf8_decode($val['Pignoramento_ID']);
		$this->Marca_Veicolo = utf8_decode($val['Marca_Veicolo']);
		$this->Modello_Veicolo = utf8_decode($val['Modello_Veicolo']);
		$this->Targa_Veicolo = utf8_decode($val['Targa_Veicolo']);
		$this->Tipo_Veicolo = utf8_decode($val['Tipo_Veicolo']);
		$this->Data_Visura = utf8_decode($val['Data_Visura']);
		$this->Portata_Veicolo = utf8_decode($val['Portata_Veicolo']);
		$this->Valore_Veicolo = utf8_decode($val['Valore_Veicolo']);
		$this->Anno_Immatricolazione = utf8_decode($val['Anno_Immatricolazione']);
		$this->Data_Notifica_Istituto = utf8_decode($val['Data_Notifica_Istituto']);
		$this->Stato_Notifica_Istituto = utf8_decode($val['Stato_Notifica_Istituto']);
		$this->Motivo_Notifica_Istituto = utf8_decode($val['Motivo_Notifica_Istituto']);
		$this->Modalita_Notifica_Istituto = utf8_decode($val['Modalita_Notifica_Istituto']);
		$this->Spese_Notifica_Istituto = utf8_decode($val['Spese_Notifica_Istituto']);
		$this->CAN_Istituto = utf8_decode($val['CAN_Istituto']);
		$this->CAD_Istituto = utf8_decode($val['CAD_Istituto']);		
		$this->Modalita_Stampa_Istituto = utf8_decode($val['Modalita_Stampa_Istituto']);
		
	}

	public function Delete ()
	{
		$query = "DELETE FROM pignoramento_veicolo WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
				if ($key != "ID" && isset($value) != false)
				{
					$fields[] = $key;
					$values[] = utf8_decode($value);
				}
			}
		}

		$query = table_insert_record_query ("pignoramento_veicolo", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
				if ($key != $campo && isset($value) != false)
				{
					$fields[] = $key;
					$values[] = utf8_decode($value);
				}
			}
		}

		$query = table_update_record_query ("pignoramento_veicolo", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}


?>