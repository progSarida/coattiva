<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class ruolo
{
	public $ID;
	public $Comune_ID;
	public $Data_Fornitura;
	public $Progr_Fornitura;
	public $Descrizione;
	public $Ruolo;
	public $Num_Rate;
	public $Num_Ruolo;
	public $Data_Inserimento;
	public $Partita = array();
		
	public function __construct( $progr , $c , $a )
	{
		$query = "SELECT * FROM ruolo WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->Data_Fornitura = utf8_decode($val['Data_Fornitura']);
		$this->Progr_Fornitura = utf8_decode($val['Progr_Fornitura']);
		$this->Descrizione = utf8_decode($val['Descrizione']);
		$this->Ruolo = utf8_decode($val['Ruolo']);
		$this->Num_Rate = utf8_decode($val['Num_Rate']);
		$this->Num_Ruolo = utf8_decode($val['Num_Ruolo']);
		$this->Data_Inserimento = utf8_decode($val['Data_Inserimento']);
		
		$partita_id = select_mysql_array("ID", "partita_tributi" , "Ruolo_ID = '".$this->ID."' AND CC = '".$c."'");
		
		for( $i=0; $i<count($partita_id); $i++ )
		{
			$this->Partita[$i] = new partita( $partita_id[$i]['ID'] , $c , $a );
		}
	}
}

class partita
{	
	public $ID;
	public $CC;
	public $Comune_ID;
	public $Ruolo_ID;	
	public $Anno_Riferimento;
	public $Tipo;
	public $Utente_ID;
	public $Coo_ID = array();
	public $Coo_Tipo;
	public $Flag_Blocco_Coazione;
	public $Flag_Blocco_Maggiorazioni;
	public $Flag_Blocco_Diritto_Riscossione;
	public $Motivo_Blocco;
	public $Note_Blocco;
	public $Cancellazione;
	public $Tributo = array();
	public $Atto = array();
	public $Ricorso = array();
	public $Pignoramento = array();
	
	public $ultimo_atto_scaduto;
	public $ultimo_atto;
	public $ultimo_avviso;
	
	public $Atto_Not;
	public $Atto_Calc;
	public $prev;
	public $next;
	
	public $Utente;
	public $Ultima_ING;
	public $Pagamenti_Atti_Precedenti;
		
	public function __construct( $progr = 0 , $c , $a = null )
	{
		
		$query = "SELECT * FROM partita_tributi WHERE ID = '".$progr."' AND CC = '".$c."'";
		if($a!=null)	$query.=" AND Anno_Riferimento = '".$a."'";
		
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->Ruolo_ID = utf8_decode($val['Ruolo_ID']);
		$this->Anno_Riferimento = utf8_decode($val['Anno_Riferimento']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Utente_ID = utf8_decode($val['Utente_ID']);
		
		$this->Utente = new utente($val['Utente_ID'], $c);
		
		$this->Flag_Blocco_Coazione = utf8_decode($val['Flag_Blocco_Coazione']);
		$this->Flag_Blocco_Maggiorazioni = utf8_decode($val['Flag_Blocco_Maggiorazioni']);
		$this->Flag_Blocco_Diritto_Riscossione = utf8_decode($val['Flag_Blocco_Diritto_Riscossione']);
		
		$this->Motivo_Blocco = utf8_decode($val['Motivo_Blocco']);
		$this->Note_Blocco = utf8_decode($val['Note_Blocco']);
		$this->Cancellazione = utf8_decode($val['Cancellazione']);
		$this->Coo_Tipo = utf8_decode($val['Coo_Tipo']);
		$coo = utf8_decode($val['Coo_ID']);
		if($coo!="")
		{
			$coo = explode("*", $coo);
			
			for($i=1;$i<count($coo);$i++)
			{
				$this->Coo_ID[$i-1] = $coo[$i];
			}
		}
		else 
		{
			$this->Coo_ID = null;
		}
		
		$tributo_id = select_mysql_array("ID", "tributo","Partita_ID = '".$this->ID."'");
		$atto_id = select_mysql_array("ID", "atto","Partita_ID = '".$this->ID."'");
		$pagamento_id = select_mysql_array("ID", "pagamento","Partita_ID = '".$this->ID."'");
		$ricorso_id = select_mysql_array("ID", "ricorso_generale", "Partita_ID = '".$this->ID."'");
		$pignoramento_id = select_mysql_array("ID", "pignoramento_generale", "Partita_ID = '".$this->ID."'");
		
		for( $i=0; $i<count($tributo_id); $i++)
		{
			$this->Tributo[$i] = new tributo( $tributo_id[$i]['ID'] , $c );
		}
		
		for( $i=0; $i<count($atto_id); $i++)
		{
			$this->Atto[$i] = new atto( $atto_id[$i]['ID'] , $c );
			if($this->Atto[$i]->Atto == "Ingiunzione" || $this->Atto[$i]->Atto == "Avviso di intimazione ad adempiere")
			{
				$this->ultimo_atto = $atto_id[$i]['ID'];
				
				if($this->Atto[$i]->Atto == "Ingiunzione")
				{
					if( $this->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-1 year" )) )
						$this->ultimo_atto_scaduto = $atto_id[$i]['ID'];					
				}
				else 
				{
					$this->ultimo_avviso = $atto_id[$i]['ID'];
					
					if( $this->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-6 month" )) )
						$this->ultimo_atto_scaduto = $atto_id[$i]['ID'];
				}
			}

			$this->Atto_Not = $i+1;
			
			
			if($this->Atto[$i]->Stato != "Annullata")
			{
				$this->Atto_Calc = $i+1;
			}
		}
		
		for( $i=0; $i<count($ricorso_id); $i++)
		{
			$this->Ricorso[$i] = new ricorso_generale( $ricorso_id[$i]['ID'] , $c );
		}
		
		for( $i=0; $i<count($pignoramento_id); $i++)
		{
			$this->Pignoramento[$i] = new pignoramento( $pignoramento_id[$i]['ID'] , $c );
		}
		
		
		if ($progr==0)
		{
			$query = "SELECT * FROM partita_tributi WHERE CC='".$c."' AND Anno_Riferimento = '".$a."' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
		
			$query = "SELECT * FROM partita_tributi WHERE CC='".$c."' AND Anno_Riferimento = '".$a."' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
				
		}
		else
		{
			$query = "SELECT * FROM partita_tributi	WHERE ( (ID>'$this->ID') AND (CC='$c') AND Anno_Riferimento = '$a') ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
		
			$query = "SELECT * FROM partita_tributi	WHERE ( (ID<'$this->ID') AND (CC='$c') AND Anno_Riferimento = '$a') ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
	
		}
	}
	
	public function atti_notificati()
	{
		$atti_notificati = array();
		$tot = 0.00;
		for($i=count($this->Atto)-2; $i>=0; $i--)
		{
			$ing = $this->Atto[$i];
			
			if($ing->Atto == "Ingiunzione" || $ing->Atto == "Avviso di intimazione ad adempiere")
			{
				if($ing->Data_Notifica!=null && $ing->Data_Notifica!='0000-00-00')
				{
						if($ing->Atto == "Ingiunzione")
						{
							$this->Ultima_ING = $ing;
							$tot+= $ing->totale_pagamenti();						
							
							$numeroatto = $ing->ID_Cronologico." del ".$ing->Anno_Cronologico;
							if($ing->Cronologico_Vecchio!="si")
								$atti_notificati[] = strtoupper("di cui all'".$ing->Atto." N. ".$numeroatto." NOTIFICATA IL ".from_mysql_date($ing->Data_Notifica));
							else
								$atti_notificati[] = strtoupper("di cui all'".$ing->Atto." con pari num. cronologico notificata il ".from_mysql_date($ing->Data_Notifica));	

							break;
						}
						else if($ing->Atto == "Avviso di intimazione ad adempiere")
						{
							$tot+= $ing->totale_pagamenti();
							
							$numeroatto = $ing->ID_Cronologico." del ".$ing->Anno_Cronologico;
							if($ing->Cronologico_Vecchio!="si")
								$atti_notificati[] = strtoupper("di cui all'".$ing->Atto." N. ".$numeroatto." NOTIFICATO IL ".from_mysql_date($ing->Data_Notifica));
							else
								$atti_notificati[] = strtoupper("di cui all'".$ing->Atto." con pari num. cronologico notificato il ".from_mysql_date($ing->Data_Notifica));							 
						}
				}
			}
		}
		
		$this->Pagamenti_Atti_Precedenti = $tot;
		return $atti_notificati;
	}
	
	public function tutti_gli_atti_notificati()
	{
		$atti_notificati = array();
		$tot = 0.00;
		for($i=count($this->Atto)-1; $i>=0; $i--)
		{
			$ing = $this->Atto[$i];
			
			if($ing->Atto == "Ingiunzione" || $ing->Atto == "Avviso di intimazione ad adempiere")
			{
				$numeroatto = $ing->ID_Cronologico." DEL ".$ing->Anno_Cronologico;
				if($ing->Data_Notifica!=null && $ing->Data_Notifica!='0000-00-00')
				{
					$atti_notificati[] = strtoupper($ing->Atto." N. ".$numeroatto." NOTIFICATO IL ".from_mysql_date($ing->Data_Notifica));
				}
				else
				{
					if($ing->Motivo_Notifica!=0)
					{
// 						$query = "SELECT Descrizione FROM parametri_notifica WHERE ID = ".$ing->Motivo_Notifica;
// 						$anomalia = single_query($query);
						
// 						$atti_notificati[] = strtoupper($ing->Atto." N. ".$numeroatto." - ".strtoupper($anomalia));
					}
					else 
					{
						if($ing->Rielabora_Flag!="si")
							$atti_notificati[] = strtoupper($ing->Atto." N. ".$numeroatto." IN ATTESA DI VERIFICA");
					}
				}
			}
		}
	
		return $atti_notificati;
	}
	
	public function verbale_originario()
	{
		$arrayOriginale = array();
		//echo "<br>3232323   --  " . count($this->Atto);
		for ($i = 0; $i < count($this->Atto); $i++)
		{
			$ing = $this->Atto[$i];
			
			//echo "<br>ATTTTT  " . $this->Atto[$i]->ID;
			
			if ($ing->Cronologico_Vecchio == "si")
			{
				$verbale = $this->Atto[$i]->ID_Cronologico;
				$anno = $this->Atto[$i]->Anno_Cronologico;
				$completo = $verbale . "/" . $anno;
				$arrayOriginale = array($verbale, $anno, $completo);
				return $arrayOriginale;
			}
		}
		return $arrayOriginale;
	}
	
	public function spese_originarie()
	{
		$arrayOriginale = array();
		//echo "<br>3232323   --  " . count($this->Atto);
		for ($i = 0; $i < count($this->Tributo); $i++)
		{
			$ing = $this->Tributo[$i];
			
			//echo "<br>ATTTTT  " . $this->Atto[$i]->ID;
			
			if ($ing->Codice_Tributo == "5354" && $this->Tipo = "CDS")
			{
				return $ing->Imposta;
			}
		}
		return null;
	}
	
	public function Delete ()
	{
		$query = "DELETE FROM partita_tributi WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;
	}
	
	public function Delete_Tributi ()
	{
		$query = "DELETE FROM tributo WHERE Partita_ID = '" . $this->ID . "' ";
		$result = mysql_query($query);
		return $result;
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
	
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false && is_array($value)===false && is_object($value)===false)
			{
				if($key != "Atto_Not" && $key != "Atto_Calc" && $key != "prev" && $key != "next" && $key != "ultimo_atto_scaduto" && $key != "ultimo_avviso" && $key != "ultimo_atto" && $key != "Ultima_ING" && $key != "Pagamenti_Atti_Precedenti")
				{
					$fields[] = $key;
					$values[] = $value;
				}
			}
		}
	
		$query = table_insert_record_query ("partita_tributi", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false && is_array($value)===false && is_object($value)===false)
			{
				if($key != "Atto_Not" && $key != "Atto_Calc" && $key != "prev" && $key != "next" && $key != "ultimo_atto_scaduto" && $key != "ultimo_avviso" && $key != "ultimo_atto" && $key != "Ultima_ING" && $key != "Pagamenti_Atti_Precedenti")
				{
					$fields[] = $key;
					$values[] = $value;
				}
			}
		}
	
		$query = table_update_record_query ("partita_tributi", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class tributo
{
	public $ID;
	public $Anno_Tributo;
	public $Codice_Tributo;
	public $Imponibile;
	public $Imposta;
	public $Data_Decorrenza_Interessi;
	public $Codice_Reparto;
	public $Info_Cartella;
	public $Tipo_Info;
	public $Titolo_Entrata;
	public $Descrizione_Entrata;
	public $Tipo_Sanzione;
	public $Titolo_Sanzione;
	public $Data_Sanzione;
	public $Targa_Sanzione;
	public $Matricola;
	public $Rateizzazione;
	public $Tipo_Tributo;
	public $Pagante;
	public $Tipo_Pagamento;
	public $Quietanza;
	public $Bollettario;
	public $Data_Registrazione;
	public $Data_Notifica;
	public $Data_Emissione;
	public $Stato_Ingiunzione;
	public $Stato_Stampa;
	public $Pagamenti_Associati;
		
	public function __construct( $progr , $c )
	{
		if ($progr == NULL) return;
		
		$query = "SELECT * FROM tributo WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->Anno_Tributo = utf8_decode($val['Anno_Tributo']);
		$this->Codice_Tributo = utf8_decode($val['Codice_Tributo']);
		$this->Imposta = utf8_decode($val['Imposta']);
		$this->Data_Decorrenza_Interessi = utf8_decode($val['Data_Decorrenza_Interessi']);
		$this->Codice_Reparto = utf8_decode($val['Codice_Reparto']);
		$this->Info_Cartella = utf8_decode($val['Info_Cartella']);
		$this->Tipo_Info = utf8_decode($val['Tipo_Info']);
		$this->Titolo_Entrata = utf8_decode($val['Titolo_Entrata']);
		$this->Descrizione_Entrata = utf8_decode($val['Descrizione_Entrata']);
		$this->Tipo_Sanzione = utf8_decode($val['Tipo_Sanzione']);
		$this->Titolo_Sanzione = utf8_decode($val['Titolo_Sanzione']);
		$this->Data_Sanzione = utf8_decode($val['Data_Sanzione']);
		$this->Targa_Sanzione = utf8_decode($val['Targa_Sanzione']);
		$this->Matricola = utf8_decode($val['Matricola']);
		$this->Rateizzazione = utf8_decode($val['Rateizzazione']);
		$this->Pagante = utf8_decode($val['Pagante']);
		$this->Tipo_Pagamento = utf8_decode($val['Tipo_Pagamento']);
		$this->Quietanza = utf8_decode($val['Quietanza']);
		$this->Bollettario = utf8_decode($val['Bollettario']);
		$this->Data_Registrazione = utf8_decode($val['Data_Registrazione']);
		$this->Data_Notifica = utf8_decode($val['Data_Notifica']);
		$this->Data_Emissione = utf8_decode($val['Data_Emissione']);
		$this->Stato_Ingiunzione = utf8_decode($val['Stato_Ingiunzione']);
		$this->Stato_Stampa = utf8_decode($val['Stato_Stampa']);
		$this->Pagamenti_Associati = utf8_decode($val['Pagamenti_Associati']);
		
		$this->Tipo_Tributo = single_answer_query("SELECT Descrizione FROM codice_tributo WHERE Codice_Tributo = '".$val['Codice_Tributo']."'");
		
		
	}
	
	public function Delete()
	{
		$query = "DELETE FROM tributo WHERE ID = '" . $this->ID . "' ";
		$result = mysql_query($query);
		return $result;
	}
}

class partite_utente
{
	public $ID = array();
	public $Ruolo_ID = array();
	public $Ruolo_Comune_ID = array();
	public $Descrizione_Ruolo = array();
	public $Comune_ID = array();
	public $Anno_Riferimento = array();
	public $Tipo = array();
	public $Utente_ID = array();
	public $Coo_ID = array();
	public $Coo_Tipo = array();
	public $Cancellazione = array();
	public $Tributo = array();
	public $Atto = array();
	public $Pagamento = array();

	public function __construct( $progr_utente , $c, $a = null )
	{
		
		$query = "SELECT * FROM partita_tributi WHERE Utente_ID = '".$progr_utente."' AND CC = '".$c."'";
		if($a!=null)	$query.=" AND Anno_Riferimento = '".$a."'";
		
		$result = mysql_query($query);
		
		$k=0;
		
	while( $val = mysql_fetch_array($result, MYSQL_ASSOC) )
	{
		
		$this->ID[$k] = utf8_decode($val['ID']);
		$this->Comune_ID[$k] = utf8_decode($val['Comune_ID']);
		$this->Ruolo_ID[$k] = utf8_decode($val['Ruolo_ID']);
		
		$query = "SELECT Descrizione, Comune_ID FROM ruolo WHERE ID = '".$this->Ruolo_ID[$k]."' AND CC = '".$c."'";
		$result_ruolo = safe_query($query);
		$val_ruolo = mysql_fetch_array($result_ruolo);
		$this->Ruolo_Comune_ID[$k] = utf8_decode($val_ruolo['Comune_ID']);
		$this->Descrizione_Ruolo[$k] = utf8_decode($val_ruolo['Descrizione']);
		
		$this->Anno_Riferimento[$k] = utf8_decode($val['Anno_Riferimento']);
		$this->Tipo[$k] = utf8_decode($val['Tipo']);
		$this->Utente_ID[$k] = utf8_decode($val['Utente_ID']);
		$this->Coo_Tipo[$k] = utf8_decode($val['Coo_Tipo']);
		$this->Cancellazione[$k] = utf8_decode($val['Cancellazione']);
		$coo = utf8_decode($val['Coo_ID']);
		if($coo!="")
		{
			$coo = explode("*", $coo);
				
			for($i=1;$i<count($coo);$i++)
			{
				$this->Coo_ID[$k][$i-1] = $coo[$i];
			}
		}
		else
		{
			$this->Coo_ID[$k] = null;
		}

		$tributo_id = select_mysql_array("ID", "tributo","Partita_ID = '".$this->ID[$k]."'");
		$atto_id = select_mysql_array("ID", "atto","Partita_ID = '".$this->ID[$k]."'");
		
		for( $i=0; $i<count($tributo_id); $i++)
		{
			$this->Tributo[$k][$i] = new tributo( $tributo_id[$i]['ID'] , $c );
		}
		
		for( $i=0; $i<count($atto_id); $i++)
		{
			$this->Atto[$k][$i] = new atto( $atto_id[$i]['ID'] , $c );
		}
		
		$k++;
		
	}
		
	}
}

class atto
{	
	public $ID;
	public $Comune_ID;
	public $CC;
	public $Partita_ID;
	public $Tipo_Protocollo;
	public $Protocollo;
	public $Anno_Cronologico;
	public $ID_Cronologico;
	public $Cronologico_Vecchio;
	public $Atto;
	public $Info_Cartella;
	public $Stato;
	public $Stato_Notifica;
	public $Indirizzo_Validato;
	public $Motivo_Notifica;
	public $Modalita_Notifica;
	public $Note_Notifica;
	public $Rielabora_Flag;
	public $Tipo_Ufficiale;
	public $Modalita_Stampa;
	public $Stato_Esecuzione;
	public $Stato_Stampa;
	public $Fase;
	public $Data_Elaborazione;
	public $Data_Calcolo_Interessi;
	public $Data_Stampa;
	public $Data_Flusso;
	public $Numero_Flusso;
	public $Anno_Flusso;
	public $Data_Notifica;
	public $Spese_Precedenti;
	public $Importo;
	public $Data_Decorrenza_Interessi;
	public $Interessi;
	public $Spese_Notifica;
	public $CAN;
	public $CAD;
	public $Ulteriori_Spese;
	public $Interessi_Precedenti;
	public $Totale_Dovuto;
	public $Note;
	public $Riferimento;
	public $Rate_Previste;
	public $Importi_Rate;
	public $Data_Richiesta_Rate;
	public $Scadenze_Rate;
	public $Tipo_Totale_Rate;
	
	public $Nominativo_Gestore_Rateizzazione;
	public $Posizione_Gestore_Rateizzazione;
	public $Esito_Richiesta_Rateizzazione;
	public $Motivazione_Respinta_Rateizzazione;
	public $Operatore_Rateizzazione;
	public $ID_Richiesta_Rateizzazione;
	public $ID_Esito_Rateizzazione;
	public $ID_Bollettini_Rateizzazione;
	
	public $Diritto_Riscossione_Minimo;
	public $Diritto_Riscossione_Massimo;
	
	public $Num_Flusso;
	public $Data_Spedizione;
	public $Estremi_Spedizione;
	public $Estremi_AR;
	public $Data_LOG;
	public $Scatola;
	public $Lotto;
	public $Posizione;
	public $Data_Importazione;
	public $Date_Stampe_Preavvisi_Ing;
	
	public $Pagamento = array();
	public $Date_Preavvisi = array();
	
	public function __construct( $progr , $c )
	{
		if ($progr == null) return;
		$query = "SELECT * FROM atto WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Partita_ID = utf8_decode($val['Partita_ID']);
		$this->Tipo_Protocollo = utf8_decode($val['Tipo_Protocollo']);
		$this->Protocollo = utf8_decode($val['Protocollo']);
		$this->ID_Cronologico = utf8_decode($val['ID_Cronologico']);
		$this->Anno_Cronologico = utf8_decode($val['Anno_Cronologico']);
		$this->Cronologico_Vecchio = utf8_decode($val['Cronologico_Vecchio']);
		$this->Atto = utf8_decode($val['Atto']);
		$this->Riferimento = utf8_decode($val['Riferimento']);
		$this->Info_Cartella = utf8_decode($val['Info_Cartella']);
		$this->Stato = utf8_decode($val['Stato']);
		$this->Stato_Notifica = utf8_decode($val['Stato_Notifica']);
		$this->Indirizzo_Validato = utf8_decode($val['Indirizzo_Validato']);
		$this->Motivo_Notifica = utf8_decode($val['Motivo_Notifica']);
		$this->Modalita_Notifica = utf8_decode($val['Modalita_Notifica']);		
		$this->Note_Notifica = utf8_decode($val['Note_Notifica']);
		$this->Rielabora_Flag = utf8_decode($val['Rielabora_Flag']);
		$this->Tipo_Ufficiale = utf8_decode($val['Tipo_Ufficiale']);
		$this->Modalita_Stampa = utf8_decode($val['Modalita_Stampa']);
		$this->Stato_Esecuzione = utf8_decode($val['Stato_Esecuzione']);
		$this->Stato_Stampa = utf8_decode($val['Stato_Stampa']);
		$this->Fase = utf8_decode($val['Fase']);
		$this->Data_Elaborazione = utf8_decode($val['Data_Elaborazione']);
		$this->Data_Calcolo_Interessi = utf8_decode($val['Data_Calcolo_Interessi']);
		$this->Data_Stampa = utf8_decode($val['Data_Stampa']);
		$this->Data_Flusso = utf8_decode($val['Data_Flusso']);
		$this->Numero_Flusso = utf8_decode($val['Numero_Flusso']);
		$this->Anno_Flusso = utf8_decode($val['Anno_Flusso']);
		$this->Data_Notifica = utf8_decode($val['Data_Notifica']);
		$this->Spese_Precedenti = utf8_decode($val['Spese_Precedenti']);
		$this->Importo = utf8_decode($val['Importo']);
		$this->Data_Decorrenza_Interessi = utf8_decode($val['Data_Decorrenza_Interessi']);
		$this->Interessi = utf8_decode($val['Interessi']);
		$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
		$this->CAN = utf8_decode($val['CAN']);
		$this->CAD = utf8_decode($val['CAD']);
		$this->Ulteriori_Spese = utf8_decode($val['Ulteriori_Spese']);
		$this->Interessi_Precedenti = utf8_decode($val['Interessi_Precedenti']);
		$this->Totale_Dovuto = utf8_decode($val['Totale_Dovuto']);
		$this->Note = utf8_decode($val['Note']);
		$this->Rate_Previste = utf8_decode($val['Rate_Previste']);
		$this->Importi_Rate = explode("*",utf8_decode($val['Importi_Rate']));
		$this->Data_Richiesta_Rate = utf8_decode($val['Data_Richiesta_Rate']);
		$this->Scadenze_Rate = explode("*",utf8_decode($val['Scadenze_Rate']));
		$this->Tipo_Totale_Rate = utf8_decode($val['Tipo_Totale_Rate']);
		$this->Nominativo_Gestore_Rateizzazione = utf8_decode($val['Nominativo_Gestore_Rateizzazione']);
		$this->Posizione_Gestore_Rateizzazione = utf8_decode($val['Posizione_Gestore_Rateizzazione']);
		$this->Esito_Richiesta_Rateizzazione = utf8_decode($val['Esito_Richiesta_Rateizzazione']);
		$this->Motivazione_Respinta_Rateizzazione = utf8_decode($val['Motivazione_Respinta_Rateizzazione']);		
		$this->Operatore_Rateizzazione = utf8_decode($val['Operatore_Rateizzazione']);	
		$this->ID_Richiesta_Rateizzazione = utf8_decode($val['ID_Richiesta_Rateizzazione']);	
		$this->ID_Esito_Rateizzazione = utf8_decode($val['ID_Esito_Rateizzazione']);	
		$this->ID_Bollettini_Rateizzazione = utf8_decode($val['ID_Bollettini_Rateizzazione']);
		$this->Diritto_Riscossione_Minimo = utf8_decode($val['Diritto_Riscossione_Minimo']);
		$this->Diritto_Riscossione_Massimo = utf8_decode($val['Diritto_Riscossione_Massimo']);
		
		$this->Num_Flusso = utf8_decode($val['Num_Flusso']);
		$this->Data_Spedizione = utf8_decode($val['Data_Spedizione']);
		$this->Estremi_Spedizione = utf8_decode($val['Estremi_Spedizione']);
		$this->Estremi_AR = utf8_decode($val['Estremi_AR']);
		$this->Data_LOG = utf8_decode($val['Data_LOG']);
		$this->Scatola = utf8_decode($val['Scatola']);
		$this->Lotto = utf8_decode($val['Lotto']);
		$this->Posizione = utf8_decode($val['Posizione']);
		$this->Data_Importazione = utf8_decode($val['Data_Importazione']);
		$this->Date_Stampe_Preavvisi_Ing = utf8_decode($val['Date_Stampe_Preavvisi_Ing']);
		
		$data_preav = explode("**", $this->Date_Stampe_Preavvisi_Ing );
		for( $i=0; $i<count($data_preav); $i++)
		{
			$this->Date_Preavvisi[$i] = from_mysql_date($data_preav[$i]);
		}
		
		$pagamento_id = select_mysql_array("ID", "pagamento","Atto_ID = '".$this->ID."' AND Tipo_Atto != 'Pignoramento'");
		
		for( $i=0; $i<count($pagamento_id); $i++)
		{
			$this->Pagamento[$i] = new pagamento( $pagamento_id[$i]['ID'] , $c );
		}
	}
	
	public function info_atto_precedente( $flag , $partita )
	{
		$atto_precedente = "";
		if($flag == "no")
			return $atto_precedente;
		
		$control_flag = 0;
		for( $i = count($partita->Atto); $i>0; $i-- )
		{
			$atto_current = $partita->Atto[$i-1];
			$tipo_ultimo_atto = $atto_current->Atto;
	
			if($control_flag==0)
			{
				if( $this->ID == $atto_current->ID )
					$control_flag = 1;
			}
			else if($control_flag==1)
			{
				if($tipo_ultimo_atto=="Ingiunzione")
				{
					$atto_precedente = "Vista la notifica dell'Ingiunzione di pagamento n. ".$atto_current->ID_Cronologico." del ";
					$atto_precedente.= $atto_current->Anno_Cronologico." effettuata il ".from_mysql_date($atto_current->Data_Notifica).".";
					break;
				}
				else if($tipo_ultimo_atto=="Avviso di intimazione ad adempiere")
				{
					$atto_precedente = "Vista la notifica dell'Avviso di intimazione ad adempiere n. ".$atto_current->ID_Cronologico." del ";
					$atto_precedente.= $atto_current->Anno_Cronologico." effettuata il ".from_mysql_date($atto_current->Data_Notifica).".";
					break;
				}
				else
				{
					$atto_precedente = "";
					continue;
				}
	
			}
		}
	
		return $atto_precedente;
	}
	
	public function controlloAvviso($tipo_partita)
	{
		if($this->Atto!="Avviso di intimazione ad adempiere")	
			$stringa = "L'ultimo atto elaborato e' un/a ".$this->Atto."!";
		else if(from_mysql_date($this->Data_Notifica)=="")
			$stringa = "Data di notifica assente per l'Avviso di intimazione ad adempiere!";
		else if($this->Data_Notifica <= date("Y-m-d" , strtotime( date('Y-m-d')."-6 month" )))
			$stringa = "Avviso di intimazione ad adempiere scaduto!";
		else
			$stringa = $this->controlloPagamenti($tipo_partita);
		
		return $stringa;
	}
	
	public function controlloPignoramento()
	{
		$pignoramento_id = select_mysql_array("ID", "pignoramento_generale","Atto_ID = '".$this->ID."'");
		$pignoramento = null;
		for( $i=0; $i<count($pignoramento_id); $i++)
		{
			$pignoramento[$i] = new pignoramento( $pignoramento_id[$i]['ID'] , $this->CC );
		}
	
		return $pignoramento;
	}
	
	public function controlloPagamenti($tipo_partita)
	{
		$para = new parametri_annuali($this->CC, date("Y-m-d"), $tipo_partita);
		$importo_min = $para->Importo_Minimo;
		$rimanenza = $this->dovuto_senza_pagamenti();
		
		//CONTROLLO SE PAGAMENTI RICEVUTI
		if($rimanenza['totale'] - $importo_min <= 0)
			return $stringa = "Impossibile procedere. Pagamenti ricevuti totalmente o debito residuo inferiore ad importo minimo necessario per procedere alle fasi successive della Riscossione Coattiva.";
		else
		{
			//CONTROLLO SE E' PRESENTE RATEIZZAZIONE
			$numero_rate = $this->Rate_Previste;
			if($numero_rate==null || $numero_rate==0)
				return "ok";
			
			$scadenza_rate = $this->Scadenze_Rate;
			$importo_rate = $this->Importi_Rate;
			
			
			$scadenza = to_mysql_date($scadenza_rate[count($scadenza_rate)-1]);
			
			if( $scadenza!=null && date("Y-m-d" , strtotime( $scadenza."+3 month" )) < date("Y-m-d") )
				return $stringa = "ok";
			else 
				return $stringa = "Rateizzazione ancora in corso (Per tempistiche lavorative alla scadenza dell'ultima rata e' necessario un tempo tecnico di tre mesi per procedere alle fasi successive). Scadenza ultima rata: ".from_mysql_date($scadenza).".";
			
		}		
		
		return "ok";
		
	}
	
	function quinto_campo($rata = 1)
	{
		$quinto_campo = "";
		
		//ID COMUNE 3 CIFRE
		$comune = new ente_gestito($this->CC);
		$id_comune = $comune->ID;
		unset($comune);
		
		for($i=0; $i< 3-strlen($id_comune) ;$i++)
			$quinto_campo .= "0";
			$quinto_campo .= $id_comune;
		
		//TIPO SERVIZIO + RISCOSSIONE 2 CIFRE
		switch($this->Atto)
		{
			case "Ingiunzione":								$quinto_campo.="02";	break;
			case "Sollecito di pagamento":					$quinto_campo.="03";	break;
			case "Avviso di intimazione ad adempiere":		$quinto_campo.="04";	break;
			case "Sollecito avviso di intimazione":			$quinto_campo.="05";	break;
		}		
		
		//NUMERO RATA 2 CIFRE
		for($i=0; $i< 2-strlen($rata) ;$i++)
			$quinto_campo .= "0";
			$quinto_campo .= $rata;
		
		//ANNO 2 CIFRE
		$cfr_anno = str_split($this->Anno_Cronologico);
		if(count($cfr_anno)>2)
			$anno = $cfr_anno[2].$cfr_anno[3];
		else 
			$anno = "00";
			$quinto_campo .= $anno;
		
		//ATTO 7 CIFRE
		for($i=0; $i< 7-strlen($this->ID_Cronologico) ;$i++)
			$quinto_campo .= "0";				
			$quinto_campo .= $this->ID_Cronologico;		
			
		//COD POSTA 2 CIFRE
		$cod_posta = fmod($quinto_campo,93);
		for($i=0; $i< 2-strlen($cod_posta) ;$i++)
			$quinto_campo .= "0";				
			$quinto_campo .= $cod_posta;
		
			return $quinto_campo;
	}
	
	function estrai_quinto_campo($quintoCampo)
	{
		//ID COMUNE 3 CIFRE
		$posizioneDa = 0;
		$posizioneA = 3;
		$codiceTemp = substr($quintoCampo, $posizioneDa, $posizioneA);
		
		$codiceId = "";
		$numTrovato = false;
		// arriva 013 : devo ottenere 13
		for ($i = 0; $i < strlen($codiceTemp); $i++)
		{
			$temp = substr($codiceTemp, $i, 1);
			if ($temp >= '1' && $temp <= '9')
			{
				$numTrovato = true;
				$codiceId .= $temp;
			}
			else if ($numTrovato == true)
			{
				$codiceId .= $temp;  //  è 0 alla fine: va tenuto
			}
			else 
			{
				// è 0 iniziale: va tolto
			}
		}
		
		$queryComune = "SELECT CC FROM enti_gestiti WHERE ID = '$codiceId'";
		//echo "<br>" . $queryComune;
		$resComune = mysql_query($queryComune);
		
		if (mysql_num_rows($resComune) == 0) $ccComune = "";
		else 
		{
			$rigaComune = mysql_fetch_assoc($resComune);
			$ccComune = $rigaComune['CC'];
		}
		
		
		//TIPO SERVIZIO + RISCOSSIONE 2 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 2;
		$numeroServizio = substr($quintoCampo, $posizioneDa, $posizioneA);
		
		switch ($numeroServizio)
		{
			case "02": $tipoServizio = "Ingiunzione"; break;
			case "03": $tipoServizio = "Sollecito di pagamento"; break;
			case "04": $tipoServizio = "Avviso di intimazione ad adempiere"; break;
			case "05": $tipoServizio = "Sollecito avviso di intimazione"; break;
			case "06": $tipoServizio = "Pignoramento beni mobili registrati"; break;
			case "07": $tipoServizio = "Pignoramento presso datore di lavoro"; break;
			case "08": $tipoServizio = "Pignoramento presso banca"; break;
		}
		
		//NUMERO RATA 2 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 2;
		$numeroTempRata = substr($quintoCampo, $posizioneDa, $posizioneA);
		
		$numeroRata = "";
		$numTrovato = false;
		// arriva 03 : devo ottenere 3
		for ($i = 0; $i < strlen($numeroTempRata); $i++)
		{
			$temp = substr($numeroTempRata, $i, 1);
			if ($temp >= '1' && $temp <= '9')
			{
				$numTrovato = true;
				$numeroRata .= $temp;
			}
			else if ($numTrovato == true)
			{
				$numeroRata .= $temp;  //  è 0 alla fine: va tenuto
			}
			else
			{
				// è 0 iniziale: va tolto
			}
		}
		if ($numeroRata == "") $numeroRata = 0;
		
		//ANNO 2 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 2;
		$annoGestione = substr($quintoCampo, $posizioneDa, $posizioneA);
		
		//ATTO 7 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 7;
		$numTempAtto = substr($quintoCampo, $posizioneDa, $posizioneA);
		
		$numeroAtto = "";
		$numTrovato = false;
		// arriva 00001230 : devo ottenere 1230
		for ($i = 0; $i < strlen($numTempAtto); $i++)
		{
			$temp = substr($numTempAtto, $i, 1);
			if ($temp >= '1' && $temp <= '9')
			{
				$numTrovato = true;
				$numeroAtto .= $temp;
			}
			else if ($numTrovato == true)
			{
				$numeroAtto .= $temp;  //  è 0 alla fine: va tenuto
			}
			else 
			{
				// è 0 iniziale: va tolto
			}
		}
		
		$oggetto = array(
			$ccComune,
			$numeroServizio,
			$numeroRata,
			$annoGestione,
			$numeroAtto
		);
	
		return $oggetto;
	}
	
	function attoStampato ( $tipo_atto , $tipo_stampa )
	{
		if($tipo_atto == "Ingiunzione")
		{
			$cartella = "Ingiunzioni";
			$prefisso = "Ingiunzione_";
		}
		else if($tipo_atto == "Avviso di intimazione ad adempiere")
		{
			$cartella = "Avvisi_di_intimazione";
			$prefisso = "Avviso_di_intimazione_";
		}
		else if($tipo_atto == "Sollecito di pagamento")
		{
			$cartella = "Solleciti";
			$prefisso = "Sollecito_";
		}
		
		if($tipo_stampa=="DEFINITIVA")
		{
			
			$sottoCartella = "STAMPE DEFINITIVE";
			if( $this->Data_Stampa == "0000-00-00" || $this->Data_Stampa == null )
				return "notFound";
			
			$file = array();
			
			$link = ATTI . "/" . $this->CC . "/" . $cartella . "/" . $sottoCartella . "/";
			$link .= $prefisso;
			$link .= $this->CC . "_";
			$link .= $this->Anno_Cronologico . "_";
			$link .= $this->ID_Cronologico . "_";
			$link .= $this->Data_Stampa . ".pdf";
			
			$file[0] = $link;
			if(is_file($link))
				return $file;
			else 
				return "notFound";
			
			
		}
		else if($tipo_stampa=="FLUSSO")
		{
			
			$sottoCartella = "FLUSSI";
			if( $this->Data_Flusso == "0000-00-00" || $this->Data_Flusso == null )
				return "notFound";
			
			$file = array();
			
			$dir = ATTI . "/" . $this->CC . "/" . $cartella . "/" . $sottoCartella;
			
			$handle = opendir($dir);
			while (($link = readdir($handle)) != false)
			{
				if ($link != "." && $link != ".." && $link != "thumbs.db" && $link != "ELIMINATI")
				{
					$explodePunto = explode (".", $link);
					$estensione = $explodePunto[1];
					
					$explode = explode ("_", $explodePunto[0]);
					$control_comune = $explode[2];
					$control_anno = $explode[3];
					$control_numero = $explode[4];
					$control_data = $explode[5];
					
					if (strtoupper($estensione) == "RAR" &&
					$this->CC == $control_comune &&
					$this->Anno_Flusso == $control_anno &&
					$this->Numero_Flusso == $control_numero)
					{
						$file[1] = $dir."/".$link;
					}
					
					if (strtoupper($estensione) == "TXT" &&
					$this->CC == $control_comune &&
					$this->Anno_Flusso == $control_anno &&
					$this->Numero_Flusso == $control_numero &&
					$this->Data_Flusso == $control_data )
					{
						$file[0] = $dir."/".$link;
					}
				}
			}
			
			closedir($handle);
			if(count($file)!=2)
				return "notFound";
			else 
				return $file;
		}		
		
	}
	
	public function totale_pagamenti()
	{
		$pagamenti = $this->Pagamento;
		$tot_pagamenti = 0;
		for($q=0;$q<count($pagamenti);$q++)
		{
			$tot_pagamenti+=$pagamenti[$q]->Importo;
		}
		
		return $tot_pagamenti;
	}
	
	public function numero_pagamenti()
	{
		$pagamenti = count($this->Pagamento);
		return $pagamenti;
	}
	
	public function dovuto_senza_pagamenti()
	{
		$rimanenza['tot_pagamenti'] = $this->totale_pagamenti();
		$rimanenza['totale'] = $this->Totale_Dovuto - $rimanenza['tot_pagamenti'];
		
		$interessi_ing = $this->Interessi_Precedenti + $this->Interessi;
		$importo_ing = $this->Importo + $this->Spese_Notifica + $this->CAD + $this->CAN;
			
		if($rimanenza['tot_pagamenti']<$interessi_ing)
		{
			$rimanenza['interessi'] = $interessi_ing - $rimanenza['tot_pagamenti'];
			$rimanenza['importo'] = $importo_ing;
		}
		else
		{
			$rimanenza['interessi'] = 0.00;
			$rimanenza['importo'] = $importo_ing - ( $rimanenza['tot_pagamenti'] - $interessi_ing );
		}

		return $rimanenza;
	}
	
	public function cercaIDdaCrono ( $tipoAtto, $crono, $CC )
	{
		$cronologico = explode("/", $crono);
		if($tipoAtto == "AVVISOINTIMAZIONE")	$tipoAtto = "Avviso di intimazione ad adempiere";
		else if($tipoAtto == "INGIUNZIONE")		$tipoAtto = "Ingiunzione";
		
		$query = "SELECT ID ";
		$query.= "FROM atto ";
		$query.= "WHERE CC = '".$CC."' AND Atto = '".$tipoAtto."' AND ID_Cronologico ='".$cronologico[0]."' AND ";
		$query.= "Anno_Cronologico = ".$cronologico[1]." ";
		
		$return = single_query($query);
		
		return $return;
		
	}
	
	public function info_spedizione()
	{
		switch($this->Atto)
		{
			case "Ingiunzione": 							$tipo_atto = "INGIUNZIONE"; break;
			case "Avviso di intimazione ad adempiere": 		$tipo_atto = "AVVISOINTIMAZIONE"; break;
			case "Sollecito di pagamento": 					$tipo_atto = "SOLLECITOINGIUNZIONE"; break;
		}
		
		$notifiche = new notifiche_importate(null);
		$id_notifica = $notifiche->CercaRiferimento($this->CC, $tipo_atto, $this->ID);
		unset($notifiche);
		
		if($id_notifica!="")
			$spedizione = new notifiche_importate($id_notifica);
		else 
			$spedizione = null;
		
		return $spedizione;
	}
	
	public function inserisciNotifica ($array)
	{
		switch($array['Tipo'])
		{
			case "modalita":
				
				$this->Modalita_Notifica = $array['ID_Tipo'];
				
				break;
				
			case "stato":
			
				$this->Stato_Notifica = $array['ID_Tipo'];
				
				break;
				
			case "motivo":
				
				$this->Motivo_Notifica = $array['ID_Tipo'];
			
				break;
		}
		
		switch($array['Stato'])
		{
			case "modalita":
		
				$this->Modalita_Notifica = $array['ID_Stato'];
		
				break;
		
			case "stato":
					
				$this->Stato_Notifica = $array['ID_Stato'];
		
				break;
		
			case "motivo":
		
				$this->Motivo_Notifica = $array['ID_Stato'];
					
				break;
		}
	}
	
	public function ultimo_id ($anno_in_corso)
	{
	
		$query = "SELECT MAX(ID_Cronologico) + 1 ";
		$query.= "FROM atto ";
		$query.= "WHERE CC = '".$this->CC."' AND Cronologico_Vecchio !='si' AND ";
		$query.= "Anno_Cronologico = ".$anno_in_corso." ";
		$query.= "ORDER BY ID_Cronologico LIMIT 1";
	
		$return = single_query($query);
		if($return == "" || $return == 0) $return = 1;
		
		return $return;
	
	}
	
	public function ultimo_proto ($anno_in_corso)
	{
	
		$query = "SELECT MAX(Protocollo) + 1 ";
		$query.= "FROM atto ";
		$query.= "WHERE CC = '".$this->CC."' AND Tipo_Protocollo = 'progressivo' AND ";
		$query.= "Anno_Cronologico = ".$anno_in_corso." AND Cronologico_Vecchio !='si' ";
		$query.= "ORDER BY ID_Cronologico LIMIT 1";
	
		$return = single_query($query);
		if($return == "" || $return == 0) $return = 1;
		
		return $return;
	
	}
	
	public function non_sbagliare_buco ($anno_in_corso)
	{
		
		$query = "SELECT ID_Cronologico + 1 ";
		$query.= "FROM atto AS t ";
		$query.= "WHERE NOT EXISTS ";
		$query.= "( SELECT * FROM atto ";
		$query.= "WHERE ID_Cronologico = t.ID_Cronologico + 1 AND CC = '".$this->CC."' AND ";
		$query.= "Anno_Cronologico = ".$anno_in_corso." AND Atto = '".addslashes($this->Atto)."' ) ";
		$query.= "ORDER BY ID_Cronologico LIMIT 1";

		$return = single_query($query);
		if($return == "" || $return == 0) $return = 1;
		
		return $return;
		
	}
	
	public function atto_collegato_al_documento($id_doc, $c)
	{
		$query = "UPDATE atto SET ID_Richiesta_Rateizzazione = null WHERE ID_Richiesta_Rateizzazione = '$id_doc' ";
		$query.= "AND CC = '".$c."'";
		
		$control_query = mysql_query($query);
		if($control_query===false)
			return $control_query;
		
		$query = "UPDATE atto SET ID_Esito_Rateizzazione = null WHERE ID_Esito_Rateizzazione = '$id_doc' ";
		$query.= "AND CC = '".$c."'";
		
		$control_query = mysql_query($query);
		if($control_query===false)
			return $control_query;

		$query = "UPDATE atto SET ID_Bollettini_Rateizzazione = null WHERE ID_Bollettini_Rateizzazione = '$id_doc' ";
		$query.= "AND CC = '".$c."'";
		
		$control_query = mysql_query($query);
		if($control_query===false)
			return $control_query;
		
		return $control_query;
		
		
	}
	
	public function Insert ($transaction = false)
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
					$values[] = $value;
				}
			
			}
		}
		if ($transaction == false)
		{
			$ret = table_insert_record ("atto", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("atto", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function InsertProvaMarco ($transaction = false)
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
				$values[] = $value;
			}
			
			}
		}
		if ($transaction == false)
		{
			$ret = table_insert_record ("atto", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = $this->table_marco_insert_record ("atto", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	function table_marco_insert_record($table, $fields_to_insert, $values_to_insert)
	{
		//------------------------------------------------------------------------------
		// id table_insert_record($table, $fields_to_insert, $values_to_insert)
		//
		// Funzione di autocomposizione query per l'inserimento di nuovi record in una
		// tabella $table.
		// Restituisce 0 in caso di richiesta errata e l'identificatore del nuovo record
		// in caso di inserimento avvenuto correttamente.
		//------------------------------------------------------------------------------
	
		$dim1 = count($fields_to_insert);
		$dim2 = count($values_to_insert);
	
		if($dim1!=$dim2 || $dim1==0 || $dim2==0) return 0;
	
		$i = 0;
		$clause = "";
		for($i; $i<$dim1; $i++)
		{
			$clause = $clause.$fields_to_insert[$i];
			if($i<$dim1-1) $clause = $clause.", ";
		}
	
		$query = "insert into $table (".$clause.") values (";
		$clause = "";
   		 $i = 0;
		for($i; $i<$dim1; $i++)
		{
			$clause = $clause."\"".$values_to_insert[$i]."\"";
			if($i<$dim1-1) $clause = $clause.", ";
		}
	
		$query = $query.$clause.")";
		//safe_query($query);
		
		echo "<br>$query";
	
		$progr = mysql_insert_id();
	
		return $progr;
	}
	
	public function Update ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		
		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
				
				if ($key != $campo && isset($value) != false )
				{
					$fields[] = $key;
					$values[] = $value;
				}
			
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("atto", $fields, $values, $campo , $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("atto", $fields, $values, $campo , $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function EmettiPreavvisoIng ()
	{
		$myDate = date ("Y-m-d");
		//$tempAtto = new atto(NULL, NULL);
		if ($this->Date_Stampe_Preavvisi_Ing == "")
			$this->Date_Stampe_Preavvisi_Ing = $myDate;
		else 
			$this->Date_Stampe_Preavvisi_Ing = "**" . $myDate;
		
		//$tempAtto->Update($this->ID, true, "ID");
		$queryDateStampe = "UPDATE atto ";
		$queryDateStampe .= " SET Date_Stampe_Preavvisi_Ing = '" . $this->Date_Stampe_Preavvisi_Ing . "' ";
		$queryDateStampe .= " WHERE ID = '" . $this->ID . "' ";
		$result = mysql_query($queryDateStampe);
		echo "<br>$queryDateStampe";
		return $result;
	}
	
	public function Delete ()
	{
		
		$query = "DELETE FROM atto WHERE ID = '" . $this->ID . "' ";
		$result = mysql_query($query);
		return $result;
				
	}
	
	public function ReturnStato ()
	{
		$temp = $this->Stato;
		$possoProcedere = true;
		switch ($temp)
		{
			case "Emessa": break;
			case "Spedita": break;
			case "Notificata": break;
			case "Compiuta giacenza": break;
			case "Scaduta": break;
			case "Annullata": break;
			case "Irreperibile": break;
		}
		return $possoProcedere;
	}
	
	public function ReturnStatoStampa ()
	{
		$temp = $this->Stato_Stampa;
		$possoProcedere = true;
		switch ($temp)
		{
			case "Da stampare": break;
			case "Stampata": break;
			case "Sospesa": break;
		}
		return $possoProcedere;
	}
	
	public function ultimoAttoPartita ( $crono_atto, $CC , $crono_vecchio = "si" )
	{
		$cronologico = explode("/", $crono_atto); 
		
		$query = "SELECT Partita_ID ";
		$query .= "FROM atto ";
		$query .= "WHERE CC = '".$CC."' AND ID_Cronologico ='".$cronologico[0]."' AND ";
		$query .= "Anno_Cronologico = ".$cronologico[1]." ";
		
		if ($crono_vecchio == "si")
			$query .= "AND Cronologico_Vecchio = 'si'";
		else if ($crono_vecchio != "si")
			$query .= "AND Cronologico_Vecchio != 'si'";
		
		$partita_ID = single_query($query);
		
		$partita = new partita($partita_ID, $CC);
		
		return $partita->ultimo_atto;
	}
}

class documento
{
	public $ID;
	public $Comune_ID;
	public $Utente_ID;
	public $CC;
	public $Data_Creazione;
	public $Tipo;
	public $Atto;
	public $Oggetto;
	public $Contenuto;
	public $Data_Stampa;
	public $Informazioni_Aggiuntive;
	public $File;

	public function __construct( $progr , $c )
	{
		if ($progr == null) return;
		$query = "SELECT * FROM documento WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->Utente_ID = utf8_decode($val['Utente_ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Data_Creazione = utf8_decode($val['Data_Creazione']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Atto = utf8_decode($val['Atto']);
		$this->Oggetto = utf8_decode($val['Oggetto']);
		$this->Contenuto = utf8_decode($val['Contenuto']);
		$this->Data_Stampa = utf8_decode($val['Data_Stampa']);
		$this->Informazioni_Aggiuntive = utf8_decode($val['Informazioni_Aggiuntive']);
		$this->File = utf8_decode($val['File']);

	}

	public function Insert ($transaction = false)
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
					$values[] = $value;
				}
					
			}
		}
		if ($transaction == false)
		{
			$ret = table_insert_record ("documento", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("documento", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function Update ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{

				if ($key != $campo && isset($value) != false )
				{
					$fields[] = $key;
					$values[] = $value;
				}
					
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("documento", $fields, $values, $campo , $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("documento", $fields, $values, $campo , $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function Delete ()
	{

		$query = "DELETE FROM documento WHERE ID = '" . $this->ID . "' ";
		$result = mysql_query($query);
		return $result;

	}
	
	public function Docs_ID ($utente_id)
	{
	
		$query = "SELECT * FROM documento WHERE Utente_ID = '" . $utente_id . "' ";
		$val = mysql_array($query);
		return $val;
	
	}
}




class pagamento
{
	
	public $ID;
	public $Comune_ID;
	public $CC;
	public $Partita_ID;
	public $Atto_ID;
	public $Riferimento_Atto;
	public $Tipo_Atto;
	public $Pagante;
	public $Conto_Terzi;
	public $Data_Pagamento;
	public $Data_Registrazione;
	public $Modalita;
	public $Importo;
	public $Dovuto;
	public $Quietanza;
	public $Bollettario;
	public $Telematico;
	public $Tipo_Pagamento;
	public $Rata;
	public $Totale_Rate;
	public $Note;
	public $Bollettino;	
	public $Data_Travaso_A_Gitco;
	
	public $Cronologico_Atto;
	
	public function __construct( $progr , $c )
	{
		if ($progr == null) return;
		$query = "SELECT * FROM pagamento WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Partita_ID = utf8_decode($val['Partita_ID']);
		$this->Atto_ID = utf8_decode($val['Atto_ID']);
		$this->Riferimento_Atto = utf8_decode($val['Riferimento_Atto']);
		$this->Tipo_Atto = utf8_decode($val['Tipo_Atto']);
		$this->Pagante = utf8_decode($val['Pagante']);
		$this->Conto_Terzi = utf8_decode($val['Conto_Terzi']);
		$this->Data_Pagamento = utf8_decode($val['Data_Pagamento']);
		$this->Data_Registrazione = utf8_decode($val['Data_Registrazione']);
		$this->Modalita = utf8_decode($val['Modalita']);
		$this->Importo = utf8_decode($val['Importo']);
		$this->Dovuto = utf8_decode($val['Dovuto']);
		$this->Quietanza = utf8_decode($val['Quietanza']);
		$this->Bollettario = utf8_decode($val['Bollettario']);
		$this->Telematico = utf8_decode($val['Telematico']);
		$this->Tipo_Pagamento = utf8_decode($val['Tipo_Pagamento']);
		$this->Rata = utf8_decode($val['Rata']);
		$this->Totale_Rate = utf8_decode($val['Totale_Rate']);
		$this->Note = utf8_decode($val['Note']);
		$this->Bollettino = utf8_decode($val['Bollettino']);
		$this->Data_Travaso_A_Gitco = utf8_decode($val['Data_Travaso_A_Gitco']);
		
	}
	
	public function TipiPagamento ()  //  serve come promemoria dei tipi possibili
	{
		switch ($this->Tipo_Pagamento)
		{
			case "T290": break;  //  importato da tracciato 290
			case "MANUALE": break;  //  inserito manualmente da operatore
			case "AUTOMATICO": break;  //  inserito da procedura importazione
			case "BONIFICATO": break;  //  bonificato da operatore dopo procedura automatica
		}
	}
	
	public function ListaTipiPagamento ()
	{
		$queryTipi = "SELECT DISTINCT Tipo_Pagamento FROM pagamento ORDER BY Tipo_Pagamento";
		$resTipi = mysql_query($queryTipi);
		$optionTipi = "<option value=''></option>\n";
		while ($rigaTipi = mysql_fetch_assoc($resTipi))
		{
			$optionTipi .= "<option value='" . $rigaTipi['Tipo_Pagamento'] . "'>" . $rigaTipi['Tipo_Pagamento'] . "</option>\n";
		}
		$optionTipi .= "<option value='TELEMATICO'>TELEMATICO</option>\n";
		return $optionTipi;
	}
	
	public function crono_atto()
	{
		if(strpos($this->Tipo_Atto,'Pignoramento')===false)
			$atto = new atto($this->Atto_ID, $this->CC);
		else 
			$atto = new pignoramento($this->Atto_ID, $this->CC);
		
		$this->Cronologico_Atto = $atto->ID_Cronologico."/".$atto->Anno_Cronologico;
	}
	
	public function ScorporoPagamento ()
	{
		$miaPartita = new partita($this->Partita_ID, $this->CC);
		$mioAtto = new atto($this->Atto_ID, $this->CC);
		$arrayVerbaleOriginario = $miaPartita->verbale_originario();
		$parametri_annuale = new gestione_parametri_annuali($this->CC, $arrayVerbaleOriginario[1], "CDS");
		
		$scorpImporto = $this->Importo;
		$scorpInteressi = 0;
		$scorpSpesePrec = 0;
		$scorpNotifica = 0;
		$scorpRicerca = 0;  //  nella coattiva NON ci sono spese ricerca
		$scorpCAN = 0;
		$scorpCAD = 0;
		$scorpUlterioriSpese = 0;
		$scorpTributo = 0;
	
		// dall'importo pagato: prima "copro" gli interessi
		// poi copro le spese
		// infine il restante lo uso per coprire il tributo
	
		$queryRatePrec = "SELECT * FROM pagamento ";
		$queryRatePrec .= "WHERE Atto_ID = " . $this->Atto_ID . " AND ";
		$queryRatePrec .= "Partita_ID = " . $this->Partita_ID . " AND ";
		$queryRatePrec .= "Rata <= " . $this->Rata;
		$queryRatePrec .= " ORDER BY Rata ASC";
		$resRatePrec = mysql_query($queryRatePrec);
		//echo "<br>" . $queryRatePrec . " ---- " . mysql_num_rows($resRatePrec);
		
		$giaPagato = 0;
		$interessiGiaPagati = 0;
		
		$interessiTot = $mioAtto->Interessi + $mioAtto->Interessi_Precedenti;
		if ($parametri_annuale->ID == null) $ricercaTot = 0;
		else $ricercaTot = $parametri_annuale->Spese_Ricerca;
		$precedentiTot = $mioAtto->Spese_Precedenti + $miaPartita->spese_originarie() - $parametri_annuale->Spese_Ricerca;
		$notificaTot = $mioAtto->Spese_Notifica;
		$canTot = $mioAtto->CAN;
		$cadTot = $mioAtto->CAD;
		$ulterioriTot = $mioAtto->Ulteriori_Spese;
		
		/*if ($_SESSION['CC_User'] == "***+")
		{
			if ($mioAtto->Rate_Previste < $this->Rata) $mioAtto->Rate_Previste = $this->Rata;
		}*/
		//if ($_SESSION['CC_User'] == "***+") alert ($this->Importo . " / " . $mioAtto->Totale_Dovuto);
		
		if ($mioAtto->Rate_Previste != 0)
		{
			//$interesseInRata = $interessiTot / $mioAtto->Rate_Previste;
			$interesseInRata = $interessiTot * $this->Importo / $mioAtto->Totale_Dovuto;
			$interesseInRata = number_format($interesseInRata, 2);
		}
		else $interesseInRata = $interessiTot;
		
		while ($rigaRataPrec = mysql_fetch_assoc($resRatePrec))
		{
			$importoRata = $rigaRataPrec['Importo'];
			$giaPagato += $importoRata;
			//if ($_SESSION['CC_User'] == "***+") alert ($importoRata . " e " . $mioAtto->Totale_Dovuto . " e " . $importoRata / $mioAtto->Totale_Dovuto);
			$questointeresse = number_format($interessiTot * $importoRata / $mioAtto->Totale_Dovuto, 2);
			$interessiGiaPagati += $questointeresse;
			//if ($_SESSION['CC_User'] == "***+") alert ($interessiTot . " e " . $questointeresse . " e " . $interessiGiaPagati);
		}
		$interessiGiaPagati -= $questointeresse;
		if ($this->Rata == $mioAtto->Rate_Previste)
		{
			$interesseInRata = $interessiTot - $interessiGiaPagati;
			//if ($_SESSION['CC_User'] == "***+") alert ($interesseInRata . " dentro " . $interessiTot . " dentro " . $interessiGiaPagati);
		}
		//if 
		
		/*for ($kkk = 0; $kkk < $nummmmm; $kkk++)
		{
			$importoRata = 53;
			$giaPagato += $importoRata;
			$interessiGiaPagati += $interesseInRata;
		}*/
		$pagatoPrimaDiQuestaRata = $giaPagato - $importoRata;
		
		
		$somma1 = $interesseInRata;
		$somma2 = $interesseInRata + $ricercaTot;
		$somma3 = $interesseInRata + $ricercaTot + $precedentiTot;
		$somma4 = $interesseInRata + $ricercaTot + $precedentiTot + $notificaTot;
		$somma5 = $interesseInRata + $ricercaTot + $precedentiTot + $notificaTot + $canTot;
		$somma6 = $interesseInRata + $ricercaTot + $precedentiTot + $notificaTot + $canTot + $cadTot;
		$somma7 = $interesseInRata + $ricercaTot + $precedentiTot + $notificaTot + $canTot + $cadTot + $ulterioriTot;
		
		//echo "<br><br>$somma1, $somma2, $somma3, $somma4, $somma5, $somma6, $somma7"; 
		
		if ($pagatoPrimaDiQuestaRata == 0)
		{
			if ($giaPagato < $somma1)
			{
				//echo "<br>1 " . $giaPagato . " < " . $somma1;
				$scorpInteressi = $giaPagato;
			}
			else if ($giaPagato < $somma2)
			{
				//echo "<br>2 " . $giaPagato . " < " . $somma2;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $giaPagato - $somma1;
			}
			else if ($giaPagato < $somma3)
			{
				//echo "<br>3 " . $giaPagato . " < " . $somma3;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $ricercaTot;
				$scorpSpesePrec = $giaPagato - $somma2;
			}
			else if ($giaPagato < $somma4)
			{
				//echo "<br>4 " . $giaPagato . " < " . $somma4;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $ricercaTot;
				$scorpSpesePrec = $precedentiTot;
				$scorpNotifica = $giaPagato - $somma3;
			}
			else if ($giaPagato < $somma5)
			{
				//echo "<br>5 " . $giaPagato . " < " . $somma5;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $ricercaTot;
				$scorpSpesePrec = $precedentiTot;
				$scorpNotifica = $notificaTot;
				$scorpCAN = $giaPagato - $somma4;
			}
			else if ($giaPagato < $somma6)
			{
				//echo "<br>6 " . $giaPagato . " < " . $somma6;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $ricercaTot;
				$scorpSpesePrec = $precedentiTot;
				$scorpNotifica = $notificaTot;
				$scorpCAN = $canTot;
				$scorpCAD = $giaPagato - $somma5;
			}
			else if ($giaPagato < $somma7)
			{
				//echo "<br>7 " . $giaPagato . " < " . $somma7;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $ricercaTot;
				$scorpSpesePrec = $precedentiTot;
				$scorpNotifica = $notificaTot;
				$scorpCAN = $canTot;
				$scorpCAD = $cadTot;
				$scorpUlterioriSpese = $giaPagato - $somma6;
			}
			else 
			{
				//echo "<br>8 " . $giaPagato . " > " . $somma7;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $ricercaTot;
				$scorpSpesePrec = $precedentiTot;
				$scorpNotifica = $notificaTot;
				$scorpCAN = $canTot;
				$scorpCAD = $cadTot;
				$scorpUlterioriSpese = $ulterioriTot;
			}
		}
		else
		{
			$attuale = $pagatoPrimaDiQuestaRata;// - $interessiGiaPagati;
			//echo "<br>diff $attuale = $pagatoPrimaDiQuestaRata - $interessiGiaPagati";
			
			if ($attuale < $interesseInRata)
			{
				//echo "<br>1 " . $importoRata . " < " . $somma1;
				$scorpInteressi = $importoRata;
			}
			else if ($attuale < $somma2)
			{
				//echo "<br>2 " . $attuale . " < " . $ricercaTot;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = $somma2 - $importoRata;
			}
			else if ($attuale < $somma3)
			{
				//echo "<br>3 " . $attuale . " < " . $somma3;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = 0;
				$scorpSpesePrec = $somma3 - $importoRata;
			}
			else if ($attuale < $somma4)
			{
				//echo "<br>4 " . $attuale . " < " . $somma4;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = 0;
				$scorpSpesePrec = 0;
				$scorpNotifica = $somma4 - $importoRata;
			}
			else if ($attuale < $somma5)
			{
				//echo "<br>5 " . $attuale . " < " . $somma5;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = 0;
				$scorpSpesePrec = 0;
				$scorpNotifica = 0;
				$scorpCAN = $somma5 - $importoRata;
			}
			else if ($attuale < $somma6)
			{
				//echo "<br>6 " . $attuale . " < " . $somma6;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = 0;
				$scorpSpesePrec = 0;
				$scorpNotifica = 0;
				$scorpCAN = 0;
				$scorpCAD = $somma6 - $importoRata;
			}
			else if ($attuale < $somma7)
			{
				//echo "<br>7 " . $attuale . " < " . $somma7;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = 0;
				$scorpSpesePrec = 0;
				$scorpNotifica = 0;
				$scorpCAN = 0;
				$scorpCAD = 0;
				$scorpUlterioriSpese = $somma7 - $importoRata;
			}
			else 
			{
				//echo "<br>8 else " . $pagatoPrimaDiQuestaRata . " - " . $attuale;
				$scorpInteressi = $interesseInRata;
				$scorpRicerca = 0;
				$scorpSpesePrec = 0;
				$scorpNotifica = 0;
				$scorpCAN = 0;
				$scorpCAD = 0;
				$scorpUlterioriSpese = 0;
			}
		}
		//if ($_SESSION['CC_User'] == "***+") alert ($scorpRicerca);
		
		$scorpTributo = $importoRata - $scorpInteressi - $scorpSpesePrec - $scorpRicerca - $scorpNotifica;
		$scorpTributo += - $scorpCAN - $scorpCAD - $scorpUlterioriSpese;
		
		
		
		/*$tempDovuto = number_format(floatval($mioAtto->Totale_Dovuto), 2, ",", "");
		$scorpImporto = number_format(floatval($scorpImporto), 2, ",", "");
		$scorpInteressi = number_format(floatval($scorpInteressi), 2, ",", "");
		$scorpSpesePrec = number_format(floatval($scorpSpesePrec), 2, ",", "");
		$scorpNotifica = number_format(floatval($scorpNotifica), 2, ",", "");
		$scorpRicerca = number_format(floatval($scorpRicerca), 2, ",", "");  //  nella coattiva NON ci sono spese ricerca
		$scorpCAN = number_format(floatval($scorpCAN), 2, ",", "");
		$scorpCAD = number_format(floatval($scorpCAD), 2, ",", "");
		$scorpUlterioriSpese = number_format(floatval($scorpUlterioriSpese), 2, ",", "");
		$scorpTributo = number_format(floatval($scorpTributo), 2, ",", "");*/
		
		/*$arrayScorporo = array
		(
				$tempDovuto,
				$scorpImporto,
				$scorpInteressi,
				$scorpSpesePrec,
				$scorpNotifica,
				$scorpRicerca,
				$scorpCAN,
				$scorpCAD,
				$scorpUlterioriSpese,
				$scorpTributo
		);*/
		$arrayScorporo = array
		(
				$mioAtto->Totale_Dovuto,  //  0
				$scorpImporto,  //  1
				$scorpInteressi,  //  2
				$scorpSpesePrec,  //  3
				$scorpNotifica,  //  4
				$scorpRicerca,  //  5
				$scorpCAN,  //  6
				$scorpCAD,  //  7
				$scorpUlterioriSpese,  //  8
				$scorpTributo  //  9
		);
		/*if ($_SESSION['CC_User'] == "***+")
		{
			$arrayTesti = array
			(
					'Dovuto     ',
					'Importo    ',
					'Interessi  ',
					'SpesePrec  ',
					'Notifica   ',
					'Ricerca    ',
					'CAN        ',
					'CAD        ',
					'UlterSpese ',
					'Tributo    '
			);
			
			$aaaa = $miaPartita->spese_originarie();
			$bbbb = $parametri_annuale->Spese_Ricerca;
			
			$arrayTotali = array
			(
				" (" . $giaPagato . ")",
					"",
				$interesseInRata . "  -" . $interessiTot . "-" . $mioAtto->Rate_Previste,
				"$precedentiTot = $mioAtto->Spese_Precedenti + $aaaa - $bbbb",
				$notificaTot,
				$ricercaTot,
				$canTot,
				$cadTot,
				$ulterioriTot,
					""
			);
			
			echo "<br>";
			
			for ($kkk = 0; $kkk < count($arrayScorporo); $kkk++)
			{
				echo "<br>" . $arrayTesti[$kkk] . " - " . $arrayScorporo[$kkk] . " ( " . $arrayTotali[$kkk] . " )";
			}
		}*/
		return $arrayScorporo;
	}

	public function Insert ($transaction = false)
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && $key!="Cronologico_Atto")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_insert_record ("pagamento", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("pagamento", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function Update ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo  && isset($value) != false && $key!="Cronologico_Atto")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("pagamento", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("pagamento", $fields, $values, $campo , $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function Delete ()
	{
	
		$query = "DELETE FROM pagamento WHERE ID = '" . $this->ID . "' ";
		$result = mysql_query($query);
		
		return $result;
	
	}
	
	public function Pagamenti_Partita ( $partita_ID , $c )
	{
	
		$pagam_partita = select_mysql_array("*", "pagamento" , "Partita_ID = '". $partita_ID ."' AND CC = '".$c."'");
				
		return $pagam_partita;
	
	}
	
	public function ProssimoComuneId ($ccComune)
	{
		$queryMaxId = "SELECT MAX(Comune_ID) as maxx FROM pagamento ";
		$queryMaxId .= "WHERE CC = '" . $ccComune . "' ";
		$resMaxId = mysql_query($queryMaxId);
		if (mysql_num_rows($resMaxId) == 0)
		{
			$prossimo = 1;
		}
		else
		{
			$rigaMaxID = mysql_fetch_assoc($resMaxId);
			$prossimo = $rigaMaxID['maxx'] + 1;
		}
		return $prossimo;
	}
	
	function PagamentoGiaPresente ()
	{
		$queryCerca = "SELECT ID FROM pagamento ";
		$queryCerca .= "WHERE Data_Pagamento = '" . $this->Data_Pagamento . "' ";
		$queryCerca .= "AND Importo = '" . $this->Importo . "' ";
		$queryCerca .= "AND CC = '" . $this->CC . "' ";
		$queryCerca .= "AND Partita_ID = '" . $this->Partita_ID . "' ";
		$queryCerca .= "AND Atto_ID = '" . $this->Atto_ID . "' ";
		$queryCerca .= "AND Rata = '" . $this->Rata . "' ";
		$queryCerca .= "AND Bollettino = '" . $this->Bollettino . "' ";
		$resCerca = mysql_query($queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['ID'];
	}
	
	function InsertUpdatePagamento ($forzoInsertUpdate = null)  //  INSERT o UPDATE o null
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		$this->Comune_ID = $this->ProssimoComuneId($this->CC);
		
		if ($forzoInsertUpdate != null)
		{
			$insUpd = $forzoInsertUpdate;
		}
		else
		{
			$this->ID = $this->PagamentoGiaPresente();
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
			if (isset($campo) && 
				$campo != "ID" &&
				$campo != "Cronologico_Atto")
			{
				$fields[] = $campo;
				$values[] = $valore;
			}
		}
		
		$questoPag = "";
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_pagam_locale($fields, $values);
			switch ($risposta)
			{
				case true:
					$risposta = "INSERT_OK";
					$questoPag = $this->PagamentoGiaPresente();
					break;
				case false:
					$risposta = "INSERT_ERROR";
					break;
				default: break;
			}
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_pagam_locale($this->ID, $fields, $values);
			switch ($risposta)
			{
				case 0:
					$risposta = "DIMENSIONI_ERRATE";
					break;
				case 1:
					$risposta = "ID_VUOTO";
					break;
				case 2:
					$risposta = "CAMPI_UGUALI";
					break;
				case 3:
					$risposta = "UPDATE_OK";
					$questoPag = $this->ID;
					break;
				case 4:
					$risposta = "UPDATE_ERROR";
					break;
				default:
					$risposta = "SCONOSCIUTO_UPDATE";
					break;
			}
		}
		else $risposta = "INSERT_ERROR_2";
		$risposta .= "**" . $questoPag;
		return $risposta;
	}
	
	public function insert_pagam_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO pagamento (" . $clause . ") VALUES (";
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
	
	public function update_pagam_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
	
		if ($dim1 != $dim2 || $dim1 == 0) return 0;
	
		if ($key == 0 || $key == '0' || $key == NULL) return 1;
	
		$query = "SELECT CC FROM pagamento WHERE ID = '" . $key . "'";
		$resultCC = single_answer_query($query);
		
		$myOldPag = new pagamento($key, $resultCC);
	
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldPag->$fields_to_update[$i] != $values_to_update[$i])
			{
				if ($fields_to_update[$i] != "Comune_ID")  //  se è UPDATE, il Comune_ID non va modificato
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return 2;  // non updata nulla, perchè sono tutti uguali
		
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
		
		$query = "UPDATE pagamento SET $clause WHERE ID = '" . $key . "'";
		
		if (mysql_query($query) != NULL) return 3;
		else return 4;
	
		echo "<br>" . $query;
	
		return 3;
	}
}

class ufficio_giudiziario
{
	public $ID;
	public $CC;
	public $Tipo;
	
	public $Denominazione;
	public $Forma_Giuridica;
	public $Sigla_Forma_Giuridica;
	
	public $Sezione;
	public $CC_Ufficio;
	public $Comune;
	public $Provincia;
	public $Toponimo;
	public $Civico;
	public $Esponente;
	public $Interno;
	public $Dettagli;
	public $Cap;
	public $Telefono;
	public $Fax;
	public $Mail;
	public $PEC;
	public $Sito;
	public $Responsabile_1;
	public $Nome_Responsabile_1;
	public $Telefono_Responsabile_1;
	public $Fax_Responsabile_1;
	public $Mail_Responsabile_1;
	public $Responsabile_2;
	public $Nome_Responsabile_2;
	public $Telefono_Responsabile_2;
	public $Fax_Responsabile_2;
	public $Mail_Responsabile_2;
	public $Responsabile_3;
	public $Nome_Responsabile_3;
	public $Telefono_Responsabile_3;
	public $Fax_Responsabile_3;
	public $Mail_Responsabile_3;	
	
	public function __construct( $c , $tipo, $tipo_CC = "comune" )
	{
		
		$query = "SELECT * FROM ufficio_giudiziario WHERE ";
		
		if($tipo_CC=="comune")
			$query.= "CC = '".$c."' ";
		else if($tipo_CC=="ufficio")
			$query.= "CC_Ufficio = '".$c."' ";
		
		$query.= "AND Tipo = '".$tipo."' LIMIT 1";
				
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo = utf8_decode($val['Tipo']);
		
		$this->Denominazione = utf8_decode($val['Denominazione']);
		$this->Forma_Giuridica = utf8_decode($val['Forma_Giuridica']);
		$this->Forma_Giuridica_Oggetto = new forma_giuridica($val['Forma_Giuridica']);
		$this->Sigla_Forma_Giuridica = $this->Forma_Giuridica_Oggetto->Sigla;
		
		$this->Sezione = utf8_decode($val['Sezione']);
		$this->CC_Ufficio = utf8_decode($val['CC_Ufficio']);
		$this->Comune = utf8_decode($val['Comune']);
		$this->Provincia = utf8_decode($val['Provincia']);
		$this->Toponimo = utf8_decode($val['Toponimo']);
		$this->Civico = utf8_decode($val['Civico']);
		$this->Esponente = utf8_decode($val['Esponente']);
		$this->Interno = utf8_decode($val['Interno']);
		$this->Dettagli = utf8_decode($val['Dettagli']);
		$this->Cap = utf8_decode($val['Cap']);
		$this->Telefono = utf8_decode($val['Telefono']);
		$this->Fax = utf8_decode($val['Fax']);
		$this->Mail = utf8_decode($val['Mail']);
		$this->PEC = utf8_decode($val['PEC']);
		$this->Sito = utf8_decode($val['Sito']);
		$this->Responsabile_1 = utf8_decode($val['Responsabile_1']);
		$this->Nome_Responsabile_1 = utf8_decode($val['Nome_Responsabile_1']);
		$this->Telefono_Responsabile_1 = utf8_decode($val['Telefono_Responsabile_1']);
		$this->Fax_Responsabile_1 = utf8_decode($val['Fax_Responsabile_1']);
		$this->Mail_Responsabile_1 = utf8_decode($val['Mail_Responsabile_1']);
		$this->Responsabile_2 = utf8_decode($val['Responsabile_2']);
		$this->Nome_Responsabile_2 = utf8_decode($val['Nome_Responsabile_2']);
		$this->Telefono_Responsabile_2 = utf8_decode($val['Telefono_Responsabile_2']);
		$this->Fax_Responsabile_2 = utf8_decode($val['Fax_Responsabile_2']);
		$this->Mail_Responsabile_2 = utf8_decode($val['Mail_Responsabile_2']);
		$this->Responsabile_3 = utf8_decode($val['Responsabile_3']);
		$this->Nome_Responsabile_3 = utf8_decode($val['Nome_Responsabile_3']);
		$this->Telefono_Responsabile_3 = utf8_decode($val['Telefono_Responsabile_3']);
		$this->Fax_Responsabile_3 = utf8_decode($val['Fax_Responsabile_3']);
		$this->Mail_Responsabile_3 = utf8_decode($val['Mail_Responsabile_3']);
	
	}
	
	public function lista_tribunali( $tipo = 'array')
	{
		$array_tribunali = select_mysql_array('CC_Ufficio, Comune', 'ufficio_giudiziario', "Tipo = 'tribunale'","Comune", "ASC", "si");
		if($tipo=="array")
			return $array_tribunali;
		else if($tipo=="options")
		{
			$stringa = "";
			for($i=0;$i<count($array_tribunali);$i++)
			{
				$stringa.= "<option value='".$array_tribunali[$i]['CC_Ufficio']."'>";
				$stringa.= $array_tribunali[$i]['Comune']." - ".$array_tribunali[$i]['CC_Ufficio'];
				$stringa.= "</option>";
			}
			
			return $stringa;
		}
	}
	
	public function sede()
	{
		if($this->CC == null)	return "";
		
		$testo = "Giudice di Pace di ".$this->Comune." sito in ".$this->Toponimo;
		
		if($this->Civico!="" && $this->Civico!=null && $this->Civico!=0)
		{
			$testo.= " ".$this->Civico;
			
			if($this->Esponente!="" && $this->Esponente!=null && $this->Esponente!=0)
				$testo.= $this->Esponente;
				
				if($this->Interno!="" && $this->Interno!=null && $this->Interno!=0)
					$testo.= "/".$this->Interno;
		}
		
		$testo.= " - ".$this->Cap." ".$this->Comune;
				
		return $testo;
	}
	
	public function righe_indirizzo()
	{
		$ind_1 = $this->Toponimo;
	
		if($this->Civico)
			$ind_1.= ", ".$this->Civico;
		if($this->Esponente)
			$ind_1.= $this->Esponente;
		if($this->Interno)
			$ind_1.="/".$this->Interno;
		if($this->Dettagli)
			$ind_1.=", ".$this->Dettagli;

		$ind_3 = "";
	
		$ind_2 = $this->Cap." ".$this->Comune;
		$ind_2_senza_prov = $ind_2;
		if($this->Provincia!=null)
			$ind_2.= " ".$this->Provincia;
	
		$indirizzo_destinatario = array();
		$indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario
	
		/////////////////////
		$lunghezza = strlen($ind_1);
		if($lunghezza<50)
		{
			$indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
			$indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
			$indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
			$indirizzo_destinatario['Riga4'] = "";
		}
		else if($lunghezza<=100)
		{
			$pos = $lunghezza/2;
			//echo $pos;
			for( $i=0; $i<$pos; $i++)
			{
			$carattere = substr(strtoupper($ind_1), $pos-$i,1);
			//echo $carattere."*";
			if($carattere==" ")
			{
			//echo $pos-$i;
				$pos = $pos-$i;
				break;
			}
			}
	
			$indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0 , $pos);
			$indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos+1);
			$indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
			$indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
		}
		///////////////////////
	
			$indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
			if($ind_3!="")
				$indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);
					
			$indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
			
			if($ind_3!="")
				$indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);
	
			return $indirizzo_destinatario;
	}
	
	public function Insert ($transaction = false)
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && $key!="Sigla_Forma_Giuridica" && $key!="Forma_Giuridica_Oggetto")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_insert_record ("ufficio_giudiziario", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("ufficio_giudiziario", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function Update ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo  && isset($value) != false && $key!="Sigla_Forma_Giuridica" && $key!="Forma_Giuridica_Oggetto")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("ufficio_giudiziario", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("ufficio_giudiziario", $fields, $values, $campo , $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function Delete ()
	{
	
		$query = "DELETE FROM ufficio_giudiziario WHERE CC = '" . $this->CC . "' AND Tipo = '".$this->Tipo."'";
		$result = mysql_query($query);
	
		return $result;
	
	}
}

class ricorso_generale
{
	public $ID;
	public $CC;
	public $Ufficio;
	public $Tipo_Ricorso;
	public $Grado;
	public $Data_Registrazione;
	public $Data_Chiusura;
	public $Sospensiva;
	public $Num_Sosp;
	public $Data_Sosp;
	public $Data_Dep_Sosp;
	public $Esito_Sosp;
	public $Data_Not_Esito_Sosp;
	public $Merito;
	public $Num_Merito;
	public $Data_Merito;
	public $Data_Dep_Merito;
	public $Esito_Merito;
	public $Data_Not_Esito_Merito;
	public $Data_Richiesta_Sentenza;
	public $Data_Impugnazione_Sentenza;
	public $Totale_Da_Pagare;
	public $RG_Pagato;
	public $Soccombenza_Pagata;
	public $Importo;
	public $Data_Pagamento;
	public $Descrizione_Pagamento;
	public $Note;
	public $Udienze = array();
	public $Atto_Citazione;
		
	public function __construct( $progr , $c )
	{
	
		$query = "SELECT * FROM ricorso_generale WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
	
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Ufficio = new ufficio_giudiziario($val['Ufficio_ID']);
		$this->Tipo_Ricorso = utf8_decode($val['Tipo_Ricorso']);
		$this->Grado = utf8_decode($val['Grado']);
		$this->Data_Registrazione = utf8_decode($val['Data_Registrazione']);
		$this->Data_Chiusura = utf8_decode($val['Data_Chiusura']);
		$this->Sospensiva = utf8_decode($val['Sospensiva']);
		$this->Num_Sosp = utf8_decode($val['Num_Sosp']);
		$this->Data_Sosp = utf8_decode($val['Data_Sosp']);
		$this->Data_Dep_Sosp = utf8_decode($val['Data_Dep_Sosp']);
		$this->Esito_Sosp = utf8_decode($val['Esito_Sosp']);
		$this->Data_Not_Esito_Sosp = utf8_decode($val['Data_Not_Esito_Sosp']);
		$this->Merito = utf8_decode($val['Merito']);
		$this->Num_Merito = utf8_decode($val['Num_Merito']);
		$this->Data_Merito = utf8_decode($val['Data_Merito']);
		$this->Data_Dep_Merito = utf8_decode($val['Data_Dep_Merito']);
		$this->Esito_Merito = utf8_decode($val['Esito_Merito']);
		$this->Data_Not_Esito_Merito = utf8_decode($val['Data_Not_Esito_Merito']);
		$this->Data_Richiesta_Sentenza = utf8_decode($val['Data_Richiesta_Sentenza']);
		$this->Data_Impugnazione_Sentenza = utf8_decode($val['Data_Impugnazione_Sentenza']);
		$this->Totale_Da_Pagare = utf8_decode($val['Totale_Da_Pagare']);
		$this->RG_Pagato = utf8_decode($val['RG_Pagato']);
		$this->Soccombenza_Pagata = utf8_decode($val['Soccombenza_Pagata']);
		$this->Importo = utf8_decode($val['Importo']);
		$this->Data_Pagamento = utf8_decode($val['Data_Pagamento']);
		$this->Descrizione_Pagamento = utf8_decode($val['Descrizione_Pagamento']);
		$this->Note = utf8_decode($val['Note']);
		
		$udienza_id = select_mysql_array("ID", "iter_udienze" , "Ricorso_ID = '".$this->ID."'", "Data_Udienza" , "DESC");
		
		for( $i=0; $i<count($udienza_id); $i++)
		{
			$this->Udienze[$i] = new iter_udienze( $udienza_id[$i]['ID'] );
		}
				
		if($val['Tipo_Ricorso']=="atto_citazione")
			$this->Atto_Citazione = new atto_citazione ( $val['ID'] );
		else if($val['Tipo_Ricorso']=="ricorso")
			{}
		
	}	
}

class atto_citazione
{
	
	public $ID;
	public $Ricorso_ID;
	public $Attore_1_ID;
	public $Attore_2_ID;
	public $Attore_3_ID;
	public $Attore_4_ID;
	public $Data_Iscrizione_Ruolo;
	public $RGN;
	public $Data_Dep_Fascicolo;
	public $Avvocato_A;
	public $Giudice;
	public $Convenuto_1_ID;
	public $Convenuto_2_ID;
	public $Convenuto_3_ID;
	public $Convenuto_4_ID;
	public $Avvocato_C;
	public $Data_Mem_Int_A;
	public $Data_Mem_Int_C;
	public $Data_Replica_Mem_Int_A;
	public $Data_Replica_Mem_Int_C;
	public $Data_Mem_Istr_A;
	public $Data_Mem_Istr_C;
	public $Data_Replica_Mem_Istr_A;
	public $Data_Replica_Mem_Istr_C;
	public $Data_Comparsa_Concl_A;
	public $Data_Comparsa_Concl_C;
	public $Data_Note_Replica_Concl_A;
	public $Data_Note_Replica_Concl_C;
	public $Data_Istanza_A;
	public $Data_Istanza_C;
	public $Data_Memorie_A;
	public $Data_Memorie_C;
	public $Data_Sottoscriz_Atto_1;
	public $Data_Sottoscriz_Atto_2;
	public $Data_Sottoscriz_Atto_3;
	public $Data_Sottoscriz_Atto_4;
	public $Data_Notifica_Atto_1;
	public $Data_Notifica_Atto_2;
	public $Data_Notifica_Atto_3;
	public $Data_Notifica_Atto_4;
	public $Data_Sottoscriz_Comparsa_1;
	public $Data_Sottoscriz_Comparsa_2;
	public $Data_Sottoscriz_Comparsa_3;
	public $Data_Sottoscriz_Comparsa_4;
	public $Data_Dep_Comparsa_1;
	public $Data_Dep_Comparsa_2;
	public $Data_Dep_Comparsa_3;
	public $Data_Dep_Comparsa_4;
		
	public function __construct( $progr )
	{
		
		$query = "SELECT * FROM atto_citazione WHERE Ricorso_ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->Ricorso_ID = utf8_decode($val['Ricorso_ID']);
		$this->Attore_1_ID = utf8_decode($val['Attore_1_ID']);
		$this->Attore_2_ID = utf8_decode($val['Attore_2_ID']);
		$this->Attore_3_ID = utf8_decode($val['Attore_3_ID']);
		$this->Attore_4_ID = utf8_decode($val['Attore_4_ID']);
		$this->Data_Iscrizione_Ruolo = utf8_decode($val['Data_Iscrizione_Ruolo']);
		$this->RGN = utf8_decode($val['RGN']);
		$this->Data_Dep_Fascicolo = utf8_decode($val['Data_Dep_Fascicolo']);
		$this->Avvocato_A = utf8_decode($val['Avvocato_A']);
		$this->Giudice = utf8_decode($val['Giudice']);
		$this->Convenuto_1_ID = utf8_decode($val['Convenuto_1_ID']);
		$this->Convenuto_2_ID = utf8_decode($val['Convenuto_2_ID']);
		$this->Convenuto_3_ID = utf8_decode($val['Convenuto_3_ID']);
		$this->Convenuto_4_ID = utf8_decode($val['Convenuto_4_ID']);
		$this->Avvocato_C = utf8_decode($val['Avvocato_C']);
		$this->Data_Mem_Int_A = utf8_decode($val['Data_Mem_Int_A']);
		$this->Data_Mem_Int_C = utf8_decode($val['Data_Mem_Int_C']);
		$this->Data_Replica_Mem_Int_A = utf8_decode($val['Data_Replica_Mem_Int_A']);
		$this->Data_Replica_Mem_Int_C = utf8_decode($val['Data_Replica_Mem_Int_C']);
		$this->Data_Mem_Istr_A = utf8_decode($val['Data_Mem_Istr_A']);
		$this->Data_Mem_Istr_C = utf8_decode($val['Data_Mem_Istr_C']);
		$this->Data_Replica_Mem_Istr_A = utf8_decode($val['Data_Replica_Mem_Istr_A']);
		$this->Data_Replica_Mem_Istr_C = utf8_decode($val['Data_Replica_Mem_Istr_C']);
		$this->Data_Comparsa_Concl_A = utf8_decode($val['Data_Comparsa_Concl_A']);
		$this->Data_Comparsa_Concl_C = utf8_decode($val['Data_Comparsa_Concl_C']);
		$this->Data_Note_Replica_Concl_A = utf8_decode($val['Data_Note_Replica_Concl_A']);
		$this->Data_Note_Replica_Concl_C = utf8_decode($val['Data_Note_Replica_Concl_C']);
		$this->Data_Istanza_A = utf8_decode($val['Data_Istanza_A']);
		$this->Data_Istanza_C = utf8_decode($val['Data_Istanza_C']);
		$this->Data_Memorie_A = utf8_decode($val['Data_Memorie_A']);
		$this->Data_Memorie_C = utf8_decode($val['Data_Memorie_C']);
		$this->Data_Sottoscriz_Atto_1 = utf8_decode($val['Data_Sottoscriz_Atto_1']);
		$this->Data_Sottoscriz_Atto_2 = utf8_decode($val['Data_Sottoscriz_Atto_2']);
		$this->Data_Sottoscriz_Atto_3 = utf8_decode($val['Data_Sottoscriz_Atto_3']);
		$this->Data_Sottoscriz_Atto_4 = utf8_decode($val['Data_Sottoscriz_Atto_4']);
		$this->Data_Notifica_Atto_1 = utf8_decode($val['Data_Notifica_Atto_1']);
		$this->Data_Notifica_Atto_2 = utf8_decode($val['Data_Notifica_Atto_2']);
		$this->Data_Notifica_Atto_3 = utf8_decode($val['Data_Notifica_Atto_3']);
		$this->Data_Notifica_Atto_4 = utf8_decode($val['Data_Notifica_Atto_4']);
		$this->Data_Sottoscriz_Comparsa_1 = utf8_decode($val['Data_Sottoscriz_Comparsa_1']);
		$this->Data_Sottoscriz_Comparsa_2 = utf8_decode($val['Data_Sottoscriz_Comparsa_2']);
		$this->Data_Sottoscriz_Comparsa_3 = utf8_decode($val['Data_Sottoscriz_Comparsa_3']);
		$this->Data_Sottoscriz_Comparsa_4 = utf8_decode($val['Data_Sottoscriz_Comparsa_4']);
		$this->Data_Dep_Comparsa_1 = utf8_decode($val['Data_Dep_Comparsa_1']);
		$this->Data_Dep_Comparsa_2 = utf8_decode($val['Data_Dep_Comparsa_2']);
		$this->Data_Dep_Comparsa_3 = utf8_decode($val['Data_Dep_Comparsa_3']);
		$this->Data_Dep_Comparsa_4 = utf8_decode($val['Data_Dep_Comparsa_4']);	
		
	}		
}

class iter_udienze
{
		
	public $ID;
	public $Data_Udienza;
	public $Ora_Udienza;
	public $Grado;
	public $Tipo;
	public $Trattazione;
	public $Esito;
	
	public function __construct( $progr )
	{
		$query = "SELECT * FROM iter_udienze WHERE ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->Data_Udienza = utf8_decode($val['Data_Udienza']);
		$this->Ora_Udienza = utf8_decode($val['Ora_Udienza']);
		$this->Grado = utf8_decode($val['Grado']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Trattazione = utf8_decode($val['Trattazione']);
		$this->Esito = utf8_decode($val['Esito']);
	}

}

class codice_tributo
{

	public $ID;
	public $Codice_Tributo;
	public $Settore;
	public $Descrizione;
	public $Autorita_Ricorso;
	
	public function __construct( $progr )
	{
		
		if ($progr == null) return;
		$query = "SELECT * FROM codice_tributo WHERE ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Codice_Tributo = utf8_decode($val['Codice_Tributo']);
		$this->Settore = utf8_decode($val['Settore']);
		$this->Descrizione = utf8_decode($val['Descrizione']);
		$this->Autorita_Ricorso = utf8_decode($val['Autorita_Ricorso']);

	}
	
	public function array_ordinato ( $ordine )
	{
	
		$array_codici = select_mysql_array("*", "codice_tributo" , " " , $ordine );
	
		return $array_codici;
	
	}
	
	public function settori ( $select = false )
	{
		$array_settori = select_mysql_array("DISTINCT Settore", "codice_tributo" );
		
		if($select==true)
		{
			$option = "";
			for($i=0;$i<count($array_settori);$i++)
			{
				$option .= "<option id='".($i+1)."'>".$array_settori[$i]['Settore']."</option>\n";			
			}
			return $option;
		}
		else 
			return $array_settori;
		
	}
		
}

class notifica_atto
{
	public $ID;
	public $CC;
	public $Atto_Notificato_ID;
	public $Tipo_Atto_Notificato;
	public $Tipo_Notifica;
	public $ID_Collegamento;
	public $Data_Notifica;
	public $Stato_Notifica;
	public $Indirizzo_Validato;
	public $Motivo_Notifica;
	public $Modalita_Notifica;
	public $Note_Notifica;
	public $Spese_Notifica;
	public $CAN;
	public $CAD;
	public $Modalita_Stampa;
	public $Spedizione_PEC;
	
	public $Email_Object;
		
	public function __construct( $progr, $c )
	{
	
		if ($progr == null) return;
		$query = "SELECT * FROM notifica_atto WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
	
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Atto_Notificato_ID = utf8_decode($val['Atto_Notificato_ID']);
		$this->Tipo_Atto_Notificato = utf8_decode($val['Tipo_Atto_Notificato']);
		$this->Tipo_Notifica = utf8_decode($val['Tipo_Notifica']);
		$this->ID_Collegamento = utf8_decode($val['ID_Collegamento']);
		$this->Data_Notifica = utf8_decode($val['Data_Notifica']);
		$this->Stato_Notifica = utf8_decode($val['Stato_Notifica']);
		$this->Indirizzo_Validato = utf8_decode($val['Indirizzo_Validato']);
		$this->Motivo_Notifica = utf8_decode($val['Motivo_Notifica']);
		$this->Modalita_Notifica = utf8_decode($val['Modalita_Notifica']);
		$this->Note_Notifica = utf8_decode($val['Note_Notifica']);
		$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
		$this->CAN = utf8_decode($val['CAN']);
		$this->CAD = utf8_decode($val['CAD']);
		$this->Modalita_Stampa = utf8_decode($val['Modalita_Stampa']);
		$this->Spedizione_PEC = utf8_decode($val['Spedizione_PEC']);
		
		$array_email = select_mysql_array("ID", "email_inviate", "CC='".$c."' AND Table_Collegata ='notifica_atto' AND ID_Collegato = $this->ID");
		if(count($array_email)>=1)
		{
			include_once CLASSI . "/classe_email.php";
			$this->Email_Object = new email_inviate($array_email[0]['ID']);
		}
		
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && $key!="Email_Object")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$ret = table_insert_record_query ("notifica_atto", $fields, $values);
		$ctrlRet = mysql_query($ret);
		return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)

	}
	
	public function Update ($valoreCampo, $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo  && isset($value) != false && $key!="Email_Object")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$ret = table_update_record_query ("notifica_atto", $fields, $values, $campo , $valoreCampo);
		$ctrlRet = mysql_query($ret);
		return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
	public function Delete ()
	{
	
		$query = "DELETE FROM notifica_atto WHERE ID = '" . $this->ID . "'";
		$result = mysql_query($query);
	
		return $result;
	
	}
}

class gestione_quinto_campo
{
	function estrai_quinto_campo($quintoCampo)
	{
		//ID COMUNE 3 CIFRE
		$posizioneDa = 0;
		$posizioneA = 3;
		$codiceTemp = substr($quintoCampo, $posizioneDa, $posizioneA);
	
		$codiceId = "";
		$numTrovato = false;
		// arriva 013 : devo ottenere 13
		for ($i = 0; $i < strlen($codiceTemp); $i++)
		{
			$temp = substr($codiceTemp, $i, 1);
			if ($temp >= '1' && $temp <= '9')
			{
				$numTrovato = true;
				$codiceId .= $temp;
			}
			else if ($numTrovato == true)
			{
				$codiceId .= $temp;  //  è 0 alla fine: va tenuto
			}
			else
			{
				// è 0 iniziale: va tolto
			}
		}
	
		$queryComune = "SELECT CC FROM enti_gestiti WHERE ID = '$codiceId'";
		//echo "<br>" . $queryComune;
		$resComune = mysql_query($queryComune);
	
		if (mysql_num_rows($resComune) == 0) $ccComune = "";
		else
		{
			$rigaComune = mysql_fetch_assoc($resComune);
			$ccComune = $rigaComune['CC'];
		}
	
	
		//TIPO SERVIZIO + RISCOSSIONE 2 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 2;
		$numeroServizio = substr($quintoCampo, $posizioneDa, $posizioneA);
	
		switch ($numeroServizio)
		{
			case "02": $tipoServizio = "Ingiunzione"; break;
			case "03": $tipoServizio = "Sollecito di pagamento"; break;
			case "04": $tipoServizio = "Avviso di intimazione ad adempiere"; break;
			case "05": $tipoServizio = "Sollecito avviso di intimazione"; break;
			case "06": $tipoServizio = "Pignoramento beni mobili registrati"; break;
			case "07": $tipoServizio = "Pignoramento presso datore di lavoro"; break;
			case "08": $tipoServizio = "Pignoramento presso banca"; break;
		}
	
		//NUMERO RATA 2 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 2;
		$numeroTempRata = substr($quintoCampo, $posizioneDa, $posizioneA);
	
		$numeroRata = "";
		$numTrovato = false;
		// arriva 03 : devo ottenere 3
		for ($i = 0; $i < strlen($numeroTempRata); $i++)
		{
			$temp = substr($numeroTempRata, $i, 1);
			if ($temp >= '1' && $temp <= '9')
			{
				$numTrovato = true;
				$numeroRata .= $temp;
			}
			else if ($numTrovato == true)
			{
				$numeroRata .= $temp;  //  è 0 alla fine: va tenuto
			}
			else
			{
				// è 0 iniziale: va tolto
			}
		}
				
		if ($numeroRata == "") $numeroRata = 0;
	
		//ANNO 2 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 2;
		$annoGestione = substr($quintoCampo, $posizioneDa, $posizioneA);

		//ATTO 7 CIFRE
		$posizioneDa += $posizioneA;
		$posizioneA = 7;
		$numTempAtto = substr($quintoCampo, $posizioneDa, $posizioneA);

		$numeroAtto = "";
		$numTrovato = false;
		// arriva 00001230 : devo ottenere 1230
		for ($i = 0; $i < strlen($numTempAtto); $i++)
		{
			$temp = substr($numTempAtto, $i, 1);
			if ($temp >= '1' && $temp <= '9')
			{
				$numTrovato = true;
				$numeroAtto .= $temp;
			}
			else if ($numTrovato == true)
			{
				$numeroAtto .= $temp;  //  è 0 alla fine: va tenuto
			}
			else
			{
				// è 0 iniziale: va tolto
			}
		}
	
		$oggetto = array(
		$ccComune,
		$numeroServizio,
		$numeroRata,
		$annoGestione,
		$numeroAtto
		);
	
		return $oggetto;
	}
}

?>