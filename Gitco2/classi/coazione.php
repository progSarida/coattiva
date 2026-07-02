<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

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
    public $DocumentTypeId;
	public $Comune_ID;
	public $CC;
	public $Partita_ID;
	public $Atto_ID;
	public $Pignoramento_ID;
	public $Anno_Cronologico;
	public $ID_Cronologico;
	public $Data_Elaborazione;
	public $Tipo;
	public $Tipo_Terzi;
	public $Comune_Banca;
	
	public $Stato_Pignoramento;
	public $Data_Stato_Pignoramento;
	
	public $Data_Stampa;
	public $Stato_Stampa;
	public $Data_Flusso;
	public $Numero_Flusso;
	public $Anno_Flusso;
	
	public $Data_Consegna;
	public $Tipo_Ufficiale;
	
	public $Data_Spedizione;
	public $Data_Iscrizione_Fermo;
	
	public $Importo_Dovuto;
	public $Spese_Notifica_Debitore;
	public $Spese_Notifica_Terzi;
	public $Totale_Spese_Notifica;
	public $Totale_Spese_Accessorie;
	public $Totale_Dovuto;
	public $Totali_Array = array();
	public $Parziali_Spese_Accessorie = array();
	
	public $Note;
	public $Rate_Previste;
	public $Importi_Rate;
	public $Scadenze_Rate;
	public $Data_Richiesta_Rate;
	public $Tipo_Totale_Rate;
	public $Nominativo_Gestore_Rateizzazione;
	public $Posizione_Gestore_Rateizzazione;
	public $Esito_Richiesta_Rateizzazione;
	public $Motivazione_Respinta_Rateizzazione;
	public $Operatore_Rateizzazione;
	public $ID_Richiesta_Rateizzazione;
	public $ID_Esito_Rateizzazione;
	public $ID_Bollettini_Rateizzazione;
	public $PrinterId;
	
	public $Notifica_Debitore;
	public $Notifiche_Debitore = array();
	public $Spese_Pignoramento;
	
	public $Presso_Terzi = array();
	
	public $Veicolo = array();
	public $Notifica_Istituto = array();
	
	public $Notifica_Sollecito = array();
	
	public $Fermo = array();
	public $Preavviso_Fermo = array();
	
	public $Immobiliare = array();
	
	public $Pagamento = array();
	
	public function __construct( $progr , $c )
	{
        if ($progr == null) return;
		$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$progr." AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->DocumentTypeId = utf8_decode($val['DocumentTypeId']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Partita_ID = utf8_decode($val['Partita_ID']);
		$this->Atto_ID = utf8_decode($val['Atto_ID']);
		$this->Pignoramento_ID = utf8_decode($val['Pignoramento_ID']);
		$this->Anno_Cronologico = utf8_decode($val['Anno_Cronologico']);
		$this->ID_Cronologico = utf8_decode($val['ID_Cronologico']);
		$this->Data_Elaborazione = utf8_decode($val['Data_Elaborazione']);

		$this->Data_Stato_Pignoramento = utf8_decode($val['Data_Stato_Pignoramento']);
		$this->Stato_Pignoramento = utf8_decode($val['Stato_Pignoramento']);

		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Tipo_Terzi = utf8_decode($val['Tipo_Terzi']);
		$this->Comune_Banca = utf8_decode($val['Comune_Banca']);
		$this->Data_Stampa = utf8_decode($val['Data_Stampa']);
		$this->Stato_Stampa = utf8_decode($val['Stato_Stampa']);
		$this->Data_Flusso = utf8_decode($val['Data_Flusso']);
		$this->Numero_Flusso = utf8_decode($val['Numero_Flusso']);
		$this->Anno_Flusso = utf8_decode($val['Anno_Flusso']);

		$this->Data_Consegna = utf8_decode($val['Data_Consegna']);
		$this->Tipo_Ufficiale = utf8_decode($val['Tipo_Ufficiale']);
		$this->Data_Spedizione = utf8_decode($val['Data_Spedizione']);

		$this->Data_Iscrizione_Fermo = utf8_decode($val['Data_Iscrizione_Fermo']);

		$this->Importo_Dovuto = utf8_decode($val['Importo_Dovuto']);
		$this->Spese_Notifica_Debitore = utf8_decode($val['Spese_Notifica_Debitore']);
		$this->Spese_Notifica_Terzi = utf8_decode($val['Spese_Notifica_Terzi']);
		$this->Totale_Spese_Notifica = utf8_decode($val['Totale_Spese_Notifica']);
		$this->Totale_Spese_Accessorie = utf8_decode($val['Totale_Spese_Accessorie']);
		$this->Totale_Dovuto = utf8_decode($val['Totale_Dovuto']);
		$this->Note = utf8_decode($val['Note']);
		$this->Rate_Previste = utf8_decode($val['Rate_Previste']);
		$this->Importi_Rate = explode("*",utf8_decode($val['Importi_Rate']));
		$this->Scadenze_Rate = explode("*",utf8_decode($val['Scadenze_Rate']));
		$this->Data_Richiesta_Rate = utf8_decode($val['Data_Richiesta_Rate']);
		$this->Tipo_Totale_Rate = utf8_decode($val['Tipo_Totale_Rate']);
		$this->Nominativo_Gestore_Rateizzazione = utf8_decode($val['Nominativo_Gestore_Rateizzazione']);
		$this->Posizione_Gestore_Rateizzazione = utf8_decode($val['Posizione_Gestore_Rateizzazione']);
		$this->Esito_Richiesta_Rateizzazione = utf8_decode($val['Esito_Richiesta_Rateizzazione']);
		$this->Motivazione_Respinta_Rateizzazione = utf8_decode($val['Motivazione_Respinta_Rateizzazione']);
		$this->Operatore_Rateizzazione = utf8_decode($val['Operatore_Rateizzazione']);
		$this->ID_Richiesta_Rateizzazione = utf8_decode($val['ID_Richiesta_Rateizzazione']);
		$this->ID_Esito_Rateizzazione = utf8_decode($val['ID_Esito_Rateizzazione']);
		$this->ID_Bollettini_Rateizzazione = utf8_decode($val['ID_Bollettini_Rateizzazione']);

		$this->PrinterId = utf8_decode($val['PrinterId']);

		$this->Spese_Pignoramento = new spese_pignoramento($progr, $c);

		$where_notifica_debitore = "CC = '".$c."' AND Atto_Notificato_ID = '".$progr."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'debitore'";
		$notifica_debitore_id = select_mysql_array("ID", "notifica_atto" , $where_notifica_debitore);
		for($i=0;$i<count($notifica_debitore_id);$i++)
		{
			$this->Notifiche_Debitore[$i] = new notifica_atto( $notifica_debitore_id[$i]['ID'] , $c );
		}

		if(isset($notifica_debitore_id[0]))
			$this->Notifica_Debitore = new notifica_atto( $notifica_debitore_id[0]['ID'] , $c );

		$where_notifica_sollecito = "CC = '".$c."' AND Atto_Notificato_ID = '".$progr."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'sollecito'";
		$notifica_sollecito_id = select_mysql_array("ID", "notifica_atto" , $where_notifica_sollecito);

		for( $i=0; $i<count($notifica_sollecito_id); $i++ )
		{
			$this->Notifica_Sollecito[$i] = new notifica_atto( $notifica_sollecito_id[$i]['ID'] , $c );
		}

		switch($this->Tipo)
		{
			case 'terzi':

				$terzi_id = select_mysql_array("ID", "pignoramento_presso_terzi" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'","ID");

				for( $i=0; $i<count($terzi_id); $i++ )
				{
					$this->Presso_Terzi[$i] = new pignoramento_presso_terzi( $terzi_id[$i]['ID'] , $c );
				}

				break;

			case 'mobiliare':

				$this->Mobiliare = null;

				break;

			case 'immobiliare':

				$immobiliare_id = select_mysql_array("ID", "pignoramento_immobiliare" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'");

				for( $i=0; $i<count($immobiliare_id); $i++ )
				{
					$this->Immobiliare[$i] = new pignoramento_immobiliare( $immobiliare_id[$i]['ID'] , $c );
				}

				break;

			case 'preav_fermo':

				$preav_fermo_id = select_mysql_array("ID", "pignoramento_veicolo" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'");

				for( $i=0; $i<count($preav_fermo_id); $i++ )
				{
					$this->Preavviso_Fermo[$i] = new pignoramento_veicolo( $preav_fermo_id[$i]['ID'] , $c );
				}

				break;

			case 'fermo':

				$fermo_id = select_mysql_array("ID", "pignoramento_veicolo" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'");

				for( $i=0; $i<count($fermo_id); $i++ )
				{
					$this->Fermo[$i] = new pignoramento_veicolo( $fermo_id[$i]['ID'] , $c );
				}

				break;

			case 'veicolo':

				$veicolo_id = select_mysql_array("ID", "pignoramento_veicolo" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'");

				for( $i=0; $i<count($veicolo_id); $i++ )
				{
					$this->Veicolo[$i] = new pignoramento_veicolo( $veicolo_id[$i]['ID'] , $c );
				}

				$where_notifica_istituto = "CC = '".$c."' AND Atto_Notificato_ID = '".$progr."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'veicolo'";
				$notifica_istituto_id = select_mysql_array("ID", "notifica_atto" , $where_notifica_istituto);

				for( $i=0; $i<count($notifica_istituto_id); $i++ )
				{
					$this->Notifica_Istituto[$i] = new notifica_atto( $notifica_istituto_id[$i]['ID'] , $c );
				}


				break;
		}

		$pagamento_id = select_mysql_array("ID", "pagamento","Atto_ID = '".$this->ID."' AND Partita_ID = '".$this->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%'","Rata");

		for( $i=0; $i<count($pagamento_id); $i++)
		{
			$this->Pagamento[$i] = new pagamento( $pagamento_id[$i]['ID'] , $c );
		}

	}

	public function importiRiscontri()
	{
		$sommaImporti = 0;
		switch ($this->Tipo)
		{
			case "terzi":
				
			if($this->Tipo_Terzi=="banca"){
				for( $i=0; $i<count($this->Presso_Terzi); $i++ )
				{
					for( $y=0; $y<count($this->Presso_Terzi[$i]->Notifiche_Terzo); $y++ )
					{
						$sommaImporti += $this->Presso_Terzi[$i]->Notifiche_Terzo[$y]->Importo_Riscontro;
					}
				}
			}			
				
				break;
				
// 			case "veicolo":
				
// 			for( $i=0; $i<count($this->Notifica_Istituto); $i++ )
// 			{
// 				$sommaImporti += $this->Notifica_Istituto[$i]->Importo_Riscontro;
// 			}
			
// 				break;
		}
		
		return $sommaImporti;
	}

	public function controlloDataPrimoPagamento()
	{
		if(isset($this->Pagamento[0]))
		{
			return $this->Pagamento[0]->Data_Pagamento;
		}
		else
			return null;
	}
	
	public function array_pignoramenti( $partita_ID , $tipo = null )
	{
		if( $tipo == null )
			$val = select_mysql_array( "*" , "pignoramento_generale" , "Partita_ID = '".$partita_ID."'" );
		else
			$val = select_mysql_array( "*" , "pignoramento_generale" , "Partita_ID = '".$partita_ID."' AND Tipo = '".$tipo."'" );

		return $val;
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
	
	public function totale_scorpori()
	{
		$pagamenti = $this->Pagamento;
		$scorporo = array();
		$scorporo['tributo'] = 0;
		$scorporo['spese_ricerca'] = 0;
		$scorporo['spese_precedenti'] = 0;
		$scorporo['spese_notifica'] = 0;
		$scorporo['interessi'] = 0;
		$scorporo['diritto_riscossione'] = 0;
		$scorporo['eca'] = 0;
		$scorporo['tributo_provinciale'] = 0;
		$scorporo['spese_accessorie'] = 0;
		$scorporo['notifica_pignoramento'] = 0;
		$scorporo['somma'] = 0;
		for($q=0;$q<count($pagamenti);$q++)
		{
			$scorporo['tributo']+=$pagamenti[$q]->Scorporo_Tributo;
			$scorporo['spese_ricerca']+=$pagamenti[$q]->Scorporo_Spese_Ricerca;
			$scorporo['spese_precedenti']+=$pagamenti[$q]->Scorporo_Spese_Precedenti;
			$scorporo['spese_notifica']+=$pagamenti[$q]->Scorporo_Spese_Notifica;
			$scorporo['interessi']+=$pagamenti[$q]->Scorporo_Interessi;
			$scorporo['diritto_riscossione']+=$pagamenti[$q]->Scorporo_Diritto_Riscossione;
			$scorporo['eca']+=$pagamenti[$q]->Scorporo_Eca;
			$scorporo['tributo_provinciale']+=$pagamenti[$q]->Scorporo_Tributo_Provinciale;
			$scorporo['spese_accessorie']+=$pagamenti[$q]->Scorporo_Spese_Accessorie;
			$scorporo['notifica_pignoramento']+=$pagamenti[$q]->Scorporo_Notifica_Pignoramento;
		}
	
		$scorporo['somma'] = $scorporo['tributo'] + $scorporo['spese_ricerca'] + $scorporo['spese_precedenti'] + $scorporo['spese_notifica'];
		$scorporo['somma']+= $scorporo['interessi'] + $scorporo['diritto_riscossione'] + $scorporo['eca'] + $scorporo['tributo_provinciale'];
		$scorporo['somma']+= $scorporo['spese_accessorie'] + $scorporo['notifica_pignoramento'];
		
		return $scorporo;
	}
	
	public function numero_pagamenti()
	{
		$pagamenti = count($this->Pagamento);
		return $pagamenti;
	}

    public function getTotalAmountDue()
    {
        $this->gestione_totali();
        $a_amount['tot'] = 0;
        $a_amount['spese_accessorie'] = 0;
        if($this->Rate_Previste>0){
            $a_amount['tot'] = $this->Totali_Array[$this->Tipo_Totale_Rate];
            $a_amount['spese_accessorie'] = $this->Parziali_Spese_Accessorie[$this->Tipo_Totale_Rate];
        }
        else{
            $pagamento_pigno = $this->Pagamento[0]->Importo;

            if($pagamento_pigno == conv_num($this->Totali_Array[3]))
            {
                $a_amount['tot'] = conv_num($this->Totali_Array[3]);
                $a_amount['spese_accessorie'] = $this->Parziali_Spese_Accessorie[3];
            }
            else if($pagamento_pigno == conv_num($this->Totali_Array[2]))
            {
                $a_amount['tot'] = conv_num($this->Totali_Array[2]);
                $a_amount['spese_accessorie'] = $this->Parziali_Spese_Accessorie[2];
            }
            else
            {
                $a_amount['tot'] = conv_num($this->Totali_Array[1]);
                $a_amount['spese_accessorie'] = $this->Parziali_Spese_Accessorie[1];
            }
        }

        $a_amount['spese_pignoramento'] = $this->Totale_Spese_Notifica;

        return $a_amount;
    }

    public function getTotalSplit($pagamenti=null)
    {
        if($pagamenti==null)
            $pagamenti = $this->Pagamento;

        $a_split = array();
        for($i=1;$i<16;$i++){
            $a_split[$i] = 0;
        }

        for($q=0;$q<count($pagamenti);$q++)
        {
            for($i=1;$i<16;$i++){
                $key = "Split_Payment".$i;
                $a_split[$i] += $pagamenti[$q]->$key;
            }
        }

        return $a_split;
    }

	public function gestione_totali()
	{
	    if($this->Spese_Pignoramento!=null)
		    $totali_spese = $this->Spese_Pignoramento->totali_spese();
	    else{
            $totali_spese = array('totale_1'=>0,'totale_2'=>0,'totale_3'=>0);
        }

		$this->Parziali_Spese_Accessorie[1] = $totali_spese['totale_1'];
		$this->Parziali_Spese_Accessorie[2] = $totali_spese['totale_1']+$totali_spese['totale_2'];
		$this->Parziali_Spese_Accessorie[3] = $totali_spese['totale_1']+$totali_spese['totale_2']+$totali_spese['totale_3'];
		$totale_1 = 0;
		$totale_2 = 0;
		$totale_3 = 0;
		
		if($totali_spese['totale_3']!=0)
		{
			$totale_3 = $this->Totale_Dovuto;
			$totale_2 = $totale_3 - $totali_spese['totale_3'];
			$totale_1 = $totale_2 - $totali_spese['totale_2'];
		}
		else
		{
			if($totali_spese['totale_2']!=0)
			{
				$totale_2 = $this->Totale_Dovuto;
				$totale_1 = $totale_2 - $totali_spese['totale_2'];
			}
			else 
			{
				if($this->Totale_Dovuto!="")
					$totale_1 = $this->Totale_Dovuto;
			}
		}
		
		$this->Totali_Array[1] = conv_num(number_format($totale_1,2));
		$this->Totali_Array[2] = conv_num(number_format($totale_2,2));
		$this->Totali_Array[3] = conv_num(number_format($totale_3,2));
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
		switch($this->Tipo)
		{
			case "veicolo":	$quinto_campo.="06";	break;
			case "terzi":	
				
				switch($this->Tipo_Terzi)
				{
					case "lavoro":		$quinto_campo.="07";	break;
					case "banca":		$quinto_campo.="08";	break;
				}

				break;
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
	
	function tipo_pignoramento($tipo=null)
	{
		switch($this->Tipo)
		{
			case "veicolo":
				if($tipo==null)
					$tipo_pignoramento = "Pignoramento beni mobili registrati";
				else if($tipo == "sigla" )
					$tipo_pignoramento = "IVG";
				break;
			case "terzi":
				switch($this->Tipo_Terzi)
				{
					case "lavoro":
						if($tipo==null)
							$tipo_pignoramento = "Pignoramento presso datore di lavoro";
						else if($tipo == "sigla" )
							$tipo_pignoramento = "DatoreLavoro";
						break;
					case "banca":
						if($tipo==null)
							$tipo_pignoramento = "Pignoramento presso banca";
						else if($tipo == "sigla" )
							$tipo_pignoramento = "Banca";
						break;
				}
				
				break;
			default:
				$tipo_pignoramento = "sconosciuto";
				break;
		}
		
		return $tipo_pignoramento;
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
				$codiceId .= $temp;  //  � 0 alla fine: va tenuto
			}
			else
			{
				// � 0 iniziale: va tolto
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
				$numeroRata .= $temp;  //  � 0 alla fine: va tenuto
			}
			else
			{
				// � 0 iniziale: va tolto
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
				$numeroAtto .= $temp;  //  � 0 alla fine: va tenuto
			}
			else
			{
				// � 0 iniziale: va tolto
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
	
	public function cercaIDdaCrono ( $tipoPignoramento, $crono, $CC )
	{
		$cronologico = explode("/", $crono);
	
		$query = "SELECT ID ";
		$query.= "FROM pignoramento_generale ";
		$query.= "WHERE CC = '".$CC."' AND Tipo = '".$tipoPignoramento."' AND ID_Cronologico ='".$cronologico[0]."' AND ";
		$query.= "Anno_Cronologico = ".$cronologico[1]." ";

		$return = single_query($query);
	
		return $return;
	
	}

    public function getDocumentFromCrono ( $documentTypeId, $crono, $CC )
    {
        $cronologico = explode("/", $crono);

        $query = "SELECT * ";
        $query.= "FROM pignoramento_generale ";
        $query.= "WHERE CC = '".$CC."' AND DocumentTypeId = ".$documentTypeId." AND ID_Cronologico ='".$cronologico[0]."' AND ";
        $query.= "Anno_Cronologico = ".$cronologico[1]." ";

        $result = mysql_query($query);
        $array_result = mysql_fetch_array($result, MYSQL_ASSOC);

        return $array_result;

    }

    public function getIDFromCrono ( $tipoPignoramento, $crono, $CC )
    {
        $cronologico = explode("/", $crono);

        $query = "SELECT pignoramento_generale.ID, partita_tributi.Tipo AS Tipo_Partita ";
        $query.= "FROM pignoramento_generale ";
        $query.= "JOIN partita_tributi ON partita_tributi.ID=pignoramento_generale.Partita_ID ";
        $query.= "WHERE pignoramento_generale.CC = '".$CC."' AND pignoramento_generale.Tipo = '".$tipoPignoramento."' ";
        $query.= "AND pignoramento_generale.ID_Cronologico=".$cronologico[0]." AND pignoramento_generale.Anno_Cronologico = ".$cronologico[1]." ";

        $result = mysql_query($query);
        $array_result = mysql_fetch_row($result);

        return $array_result;

    }
	
	public function Delete ()
	{
		$query = "DELETE FROM pignoramento_generale WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}
	
	public function Delete_All ()
	{
		$query = "DELETE FROM pignoramento_generale WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);
	
		if($ctrl_query===false)
			return $ctrl_query;
		
		$query = "DELETE FROM pignoramento_spese WHERE Pignoramento_ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);
		
		if($ctrl_query===false)
			return $ctrl_query;
		
		switch($this->Tipo)
		{
			case "terzi":
				
				$query = "DELETE FROM pignoramento_presso_terzi WHERE Pignoramento_ID = '".$this->ID."' AND CC = '".$this->CC."' ";
				$ctrl_query = mysql_query($query);
				
				break;
				
			case "veicolo":
				
				$query = "DELETE FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$this->ID."' AND CC = '".$this->CC."' ";
				$ctrl_query = mysql_query($query);
				
				break;
				
			case "fermo":
			
				$query = "DELETE FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$this->ID."' AND CC = '".$this->CC."' ";
				$ctrl_query = mysql_query($query);
			
				break;
				
			case "preav_fermo":
					
				$query = "DELETE FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$this->ID."' AND CC = '".$this->CC."' ";
				$ctrl_query = mysql_query($query);
					
				break;
					
			case "immobiliare":
			
				$query = "DELETE FROM pignoramento_immobiliare WHERE Pignoramento_ID = '".$this->ID."' AND CC = '".$this->CC."' ";
				$ctrl_query = mysql_query($query);
			
				break;
		}
		
		if($ctrl_query===false)
			return $ctrl_query;
		
		$query = "DELETE FROM notifica_atto WHERE Atto_Notificato_ID = '".$this->ID."' AND CC = '".$this->CC."' AND Tipo_Atto_Notificato = 'pignoramento'";
		$ctrl_query = mysql_query($query);		
		
		return $ctrl_query;
	}
	
	public function ultimo_id ($anno_in_corso)
	{
	
		$query = "SELECT MAX(ID_Cronologico) + 1 ";
		$query.= "FROM pignoramento_generale ";
		$query.= "WHERE CC = '".$this->CC."' AND ";
		$query.= "Anno_Cronologico = ".$anno_in_corso." ";
		$query.= "ORDER BY ID_Cronologico LIMIT 1";
	
		$return = single_query($query);
		if($return == "" || $return == 0) $return = 1;
	
		return $return;
	
	}
	
	public function ultimo_proto ($anno_in_corso)
	{
	
		$query = "SELECT MAX(Protocollo) + 1 ";
		$query.= "FROM pignoramento_generale ";
		$query.= "WHERE CC = '".$this->CC."' AND Tipo_Protocollo = 'progressivo' AND ";
		$query.= "Anno_Cronologico = ".$anno_in_corso." ";
		$query.= "ORDER BY ID_Cronologico LIMIT 1";
	
		$return = single_query($query);
		if($return == "" || $return == 0) $return = 1;
	
		return $return;
	
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
				if ($key != "ID" && isset($value) != false && $key!="Spese_Pignoramento" && $key!="Veicolo" && $key!="Notifica_Debitore" && $key!="Notifica_Preavviso_Fermo")
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
			if ($key != $campo && isset($value) != false && $key!="Spese_Pignoramento" && $key!="Veicolo" && $key!="Notifica_Debitore" && $key!="Notifica_Preavviso_Fermo")
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
	    if($this->Tipo!="terzi")
		    $tipo_atto = "PIGNO".strtoupper($this->Tipo);
	    else
            $tipo_atto = "PIGNO".strtoupper($this->Tipo_Terzi);
	
		$notifiche = new notifiche_importate(null);
		if(isset($this->Notifiche_Debitore[count($this->Notifiche_Debitore)-1]))
            $dataNot = $this->Notifiche_Debitore[count($this->Notifiche_Debitore)-1]->Data_Notifica;
		else
		    $dataNot = null;
		$id_notifica = $notifiche->CercaRiferimento($this->CC, $tipo_atto, $this->ID, $dataNot);
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
		$query.= "FROM v_pignoramento AS PIG_GEN, tributo AS TR, partita_tributi AS PAR ";
		
		if($order=="alfabetico")
		{
		
			$query.= ", ";
			$query.="(	( SELECT utente.ID, utente.Cognome AS NOME_UTENTE, utente.Nome FROM utente  ";
			$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere != 'D' ) ";
			$query.="UNION ";
			$query.="( SELECT utente.ID, utente.Ditta AS NOME_UTENTE, utente.Nome FROM utente  ";
			$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere = 'D' )	) ";
			$query.="AS UTENTE ";
		
		}
		
		if($order=="tribunale")
		{
			$query.= ", utente AS UTENTE, indirizzo AS IND, ufficio_giudiziario AS TRIB ";
		}
		
		$query.= "WHERE PAR.ID = PIG_GEN.partita_ID AND PIG_GEN.CC = '".$c."' AND TR.Partita_ID = PIG_GEN.Partita_ID";
		
		if($tipo_pigno!=null)
			$query.= " AND PIG_GEN.Tipo = '".$tipo_pigno."' ";
		
		if($tipo_pigno=="terzi" && $tipo_terzi!=null)
		{
			$query.= " AND PIG_GEN.Tipo_Terzi = '".$tipo_terzi."' ";
		}
		
		if($order=="alfabetico")
			$query.= " AND UTENTE.ID = PAR.Utente_ID ";
		
		if($order=="tribunale")
		{
			$query.= " AND UTENTE.ID = PAR.Utente_ID AND";
			$query.= " IND.Utente_ID = UTENTE.ID AND TRIB.Tipo = 'tribunale' AND TRIB.CC = IND.CC_Indirizzo";
		}
		
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
			$query.= "UTENTE.NOME_UTENTE ASC , UTENTE.Nome ASC, ";
		if($order=="tribunale")
			$query.= "TRIB.CC_Ufficio ASC, ";
		
		$query.= "PAR.Comune_ID ASC, PIG_GEN.Anno_Cronologico ASC, PIG_GEN.ID_Cronologico ASC, PIG_GEN.Comune_ID ASC";
		
		return $query;
	}
	
	function pignoramento_stampato ( $tipo_pignoramento , $tipo_stampa , $tipo_terzi = null )
	{
		$cartella = "Pignoramenti";
		if($tipo_pignoramento == "veicolo")
		{
			$cartella.= "/Veicolo";
			$prefisso = "Pignoramento_veicolo_";
		}
		else if($tipo_pignoramento == "terzi")
		{
			if($tipo_terzi == "banca")
			{
				$cartella.= "/Presso_Terzi/Banca";
				$prefisso = "Pignoramento_presso_banca_";
			}
			else if($tipo_terzi == "lavoro")
			{
				$cartella.= "/Presso_Terzi/Datore_di_Lavoro";
				$prefisso = "Pignoramento_presso_lavoro_";
			}
			else 
				return "notFound";
		}
		else
			return "notFound";
	
		if($tipo_stampa=="DEFINITIVA")
		{
				
			$sottoCartella = "STAMPE DEFINITIVE";
			$link = ATTI . "/" . $this->CC . "/" . $cartella . "/" . $sottoCartella . "/";
			$link .= $prefisso;
			$link .= $this->CC . "_";
			$link .= $this->Anno_Cronologico . "_";
			$link .= $this->ID_Cronologico . "_";
			
			
			$file = array();
					
			$file['originale'] = "";
			$link_originale = $link.$this->Data_Stampa . "_originale.pdf";
			if(is_file($link_originale))
				$file['originale'] = $link_originale;		

			$file['rel_originale'] = "";
			$link_rel_originale = $link.$this->Data_Stampa . "_rel_originale.pdf";
			if(is_file($link_rel_originale))
				$file['rel_originale'] = $link_rel_originale;
				
			$file['stampa_originale'][] = $link_originale;
			$file['stampa_originale'][] = $link_rel_originale;
			
			$file['rel_debitore_0'] = "";
			$file['debitore'] = "";
			for($rel_deb = 0;$rel_deb<count($this->Notifiche_Debitore);$rel_deb++)
			{
				$link_rel_debitore = $link.$this->Data_Stampa . "_rel_debitore_".$rel_deb.".pdf";
				$link_debitore = $link.$this->Data_Stampa . "_debitore.pdf";
				if(is_file($link_rel_debitore))
				{
					$file['rel_debitore_'.$rel_deb] = $link_rel_debitore;
					if($rel_deb>0)
						$file['stampa_originale'][] = $link_rel_debitore;
				}
				else if(is_file($link_debitore))
				{
					$file['debitore'] = $link_debitore;
				}
			}
			
			$file['sollecito_debitore_0'] = "";
			$file['sollecito_carabinieri_0'] = "";
			
			for($soll = 0;$soll<count($this->Notifica_Sollecito);$soll++)
			{
				$link_sollecito_debitore = $link.$this->Data_Stampa . "_sollecito_debitore_".$soll.".pdf";
				$link_sollecito_carabinieri = $link.$this->Data_Stampa . "_sollecito_carabinieri_".$soll.".pdf";
				
				if(is_file($link_sollecito_debitore))
					$file['sollecito_debitore_'.$soll] = $link_sollecito_debitore;
				
				if(is_file($link_sollecito_carabinieri))
					$file['sollecito_carabinieri_'.$soll] = $link_sollecito_carabinieri;
			}
			
			if($tipo_pignoramento == "veicolo")
			{
				$file['rel_istituto_0'] = "";
				for($rel_ist = 0;$rel_ist<count($this->Notifica_Istituto);$rel_ist++)
				{
					$file['rel_istituto_'.$rel_ist] = "";
					$link_rel_istituto = $link.$this->Data_Stampa . "_rel_istituto_".$rel_ist.".pdf";
					if(is_file($link_rel_istituto))
					{
						$file['rel_istituto_'.$rel_ist] = $link_rel_istituto;
						if($rel_ist>0)
							$file['stampa_originale'][] = $link_rel_istituto;
					}
				}
			}
			
			if($tipo_pignoramento == "terzi")
			{
				for($terzo_x=0;$terzo_x<count($this->Presso_Terzi);$terzo_x++)
				{
					$file['rel_terzo_'.$terzo_x.'_0'] = "";
					for($rel_terzo = 0; $rel_terzo<count($this->Presso_Terzi[$terzo_x]->Notifiche_Terzo);$rel_terzo++)
					{
						$file['rel_terzo_'.$terzo_x.'_'.$rel_terzo] = "";
						$link_terzo = $link.$this->Data_Stampa . "_rel_terzo_".$terzo_x."_".$rel_terzo.".pdf";
						if(is_file($link_terzo))
						{
							$file['rel_terzo_'.$terzo_x.'_'.$rel_terzo] = $link_terzo;
							if($rel_terzo>0)
								$file['stampa_originale'][] = $link_terzo;
						}
					}
				}
			}
			
			return $file;			
				
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
					$control_comune = $explode[3];
					$control_anno = $explode[4];
					$control_numero = $explode[5];
					$control_data = $explode[6];
						
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
	
	function controlli_scadenze()
	{
		switch($this->Tipo)
		{
			case "veicolo":
				
				scadenza_veicolo();
				
				break;
				
			case "terzi":
				
				switch($this->Tipo_Terzi)
				{
					case "lavoro":
					
						break;
						
					case "banca":
						
						break;
				}
				
				break;
				
			default:
				
				break;
		}
	}
	
	function scadenza_veicolo()
	{
		 
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
	
	public $Notifica;
	public $Notifiche_Terzo = array();

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
		
		$this->Tipo_Terzi = utf8_decode($val['Tipo_Terzi']);
		if($this->Tipo_Terzi!="banca")
			$this->Dati_Terzo = new utente($this->Terzo_ID, $c);
		else
			$this->Dati_Terzo = new banca($this->Terzo_ID, "*****");
		
		$this->Fonte_Dati = utf8_decode($val['Fonte_Dati']);
		$this->Note = utf8_decode($val['Note']);
		
		
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

		//NOTIFICA TERZO
		$where_notifica_terzo = "CC = '".$c."' AND Atto_Notificato_ID = '".$this->Pignoramento_ID."' AND ID_Collegamento = '".$this->ID."'";
		$where_notifica_terzo.= "AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'terzi'";
		
		$notifica_terzo_id = select_mysql_array("ID", "notifica_atto" , $where_notifica_terzo,"ID","ASC");
		for($i=0;$i<count($notifica_terzo_id);$i++)
		{
			$this->Notifiche_Terzo[$i] = new notifica_atto( $notifica_terzo_id[$i]['ID'] , $c );
		}
		
		if(isset($notifica_terzo_id[0]))
			$this->Notifica = new notifica_atto( $notifica_terzo_id[0]['ID'] , $c );
		
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
			if ($key != "ID" && isset($value) != false && $key!="Dati_Terzo" && $key!="Notifica")
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
			if ($key != $campo && isset($value) != false && $key!="Dati_Terzo" && $key!="Notifica")
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
	
	public $Tipo_Totale_1;
	public $Spesa_1_ID;
	public $Tipo_Spesa_1;
	public $Extra_Spesa_1;
	public $Rimborso_1;
	
	public $Tipo_Totale_2;
	public $Spesa_2_ID;
	public $Tipo_Spesa_2;
	public $Extra_Spesa_2;
	public $Rimborso_2;
	
	public $Tipo_Totale_3;
	public $Spesa_3_ID;
	public $Tipo_Spesa_3;
	public $Extra_Spesa_3;
	public $Rimborso_3;
	
	public $Tipo_Totale_4;
	public $Spesa_4_ID;
	public $Tipo_Spesa_4;
	public $Extra_Spesa_4;
	public $Rimborso_4;
	
	public $Tipo_Totale_5;
	public $Spesa_5_ID;
	public $Tipo_Spesa_5;
	public $Extra_Spesa_5;
	public $Rimborso_5;
	
	public $Tipo_Totale_6;
	public $Spesa_6_ID;
	public $Tipo_Spesa_6;
	public $Extra_Spesa_6;
	public $Rimborso_6;
	
	public $Tipo_Totale_7;
	public $Spesa_7_ID;
	public $Tipo_Spesa_7;
	public $Extra_Spesa_7;
	public $Rimborso_7;
	
	public $Tipo_Totale_8;
	public $Spesa_8_ID;
	public $Tipo_Spesa_8;
	public $Extra_Spesa_8;
	public $Rimborso_8;
	
	public $Tipo_Totale_9;
	public $Spesa_9_ID;
	public $Tipo_Spesa_9;
	public $Extra_Spesa_9;
	public $Rimborso_9;
	
	public $Tipo_Totale_10;
	public $Spesa_10_ID;
	public $Tipo_Spesa_10;
	public $Extra_Spesa_10;
	public $Rimborso_10;
	
	public $Totale_Rimborso;
	public $Totali_Array;
	
	public function __construct( $pignoramento_id , $c )
	{
		
		$query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$pignoramento_id." AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Pignoramento_ID = utf8_decode($val['Pignoramento_ID']);
		$this->Incremento_Percentuale = utf8_decode($val['Incremento_Percentuale']);
		
		
		$this->Tipo_Totale_1 = utf8_decode($val['Tipo_Totale_1']);
		$this->Spesa_1_ID = utf8_decode($val['Spesa_1_ID']);
		$this->Tipo_Spesa_1 = utf8_decode($val['Tipo_Spesa_1']);
		$this->Extra_Spesa_1 = utf8_decode($val['Extra_Spesa_1']);
		$this->Rimborso_1 = utf8_decode($val['Rimborso_1']);
		
		$this->Tipo_Totale_2 = utf8_decode($val['Tipo_Totale_2']);
		$this->Spesa_2_ID = utf8_decode($val['Spesa_2_ID']);
		$this->Tipo_Spesa_2 = utf8_decode($val['Tipo_Spesa_2']);
		$this->Extra_Spesa_2 = utf8_decode($val['Extra_Spesa_2']);
		$this->Rimborso_2 = utf8_decode($val['Rimborso_2']);
		
		$this->Tipo_Totale_3 = utf8_decode($val['Tipo_Totale_3']);
		$this->Spesa_3_ID = utf8_decode($val['Spesa_3_ID']);
		$this->Tipo_Spesa_3 = utf8_decode($val['Tipo_Spesa_3']);
		$this->Extra_Spesa_3 = utf8_decode($val['Extra_Spesa_3']);
		$this->Rimborso_3 = utf8_decode($val['Rimborso_3']);
		
		$this->Tipo_Totale_4 = utf8_decode($val['Tipo_Totale_4']);
		$this->Spesa_4_ID = utf8_decode($val['Spesa_4_ID']);
		$this->Tipo_Spesa_4 = utf8_decode($val['Tipo_Spesa_4']);
		$this->Extra_Spesa_4 = utf8_decode($val['Extra_Spesa_4']);
		$this->Rimborso_4 = utf8_decode($val['Rimborso_4']);
		
		$this->Tipo_Totale_5 = utf8_decode($val['Tipo_Totale_5']);
		$this->Spesa_5_ID = utf8_decode($val['Spesa_5_ID']);
		$this->Tipo_Spesa_5 = utf8_decode($val['Tipo_Spesa_5']);
		$this->Extra_Spesa_5 = utf8_decode($val['Extra_Spesa_5']);
		$this->Rimborso_5 = utf8_decode($val['Rimborso_5']);
		
		$this->Tipo_Totale_6 = utf8_decode($val['Tipo_Totale_6']);
		$this->Spesa_6_ID = utf8_decode($val['Spesa_6_ID']);
		$this->Tipo_Spesa_6 = utf8_decode($val['Tipo_Spesa_6']);
		$this->Extra_Spesa_6 = utf8_decode($val['Extra_Spesa_6']);
		$this->Rimborso_6 = utf8_decode($val['Rimborso_6']);
		
		$this->Tipo_Totale_7 = utf8_decode($val['Tipo_Totale_7']);
		$this->Spesa_7_ID = utf8_decode($val['Spesa_7_ID']);
		$this->Tipo_Spesa_7 = utf8_decode($val['Tipo_Spesa_7']);
		$this->Extra_Spesa_7 = utf8_decode($val['Extra_Spesa_7']);
		$this->Rimborso_7 = utf8_decode($val['Rimborso_7']);
		
		$this->Tipo_Totale_8 = utf8_decode($val['Tipo_Totale_8']);
		$this->Spesa_8_ID = utf8_decode($val['Spesa_8_ID']);
		$this->Tipo_Spesa_8 = utf8_decode($val['Tipo_Spesa_8']);
		$this->Extra_Spesa_8 = utf8_decode($val['Extra_Spesa_8']);
		$this->Rimborso_8 = utf8_decode($val['Rimborso_8']);
		
		$this->Tipo_Totale_9 = utf8_decode($val['Tipo_Totale_9']);
		$this->Spesa_9_ID = utf8_decode($val['Spesa_9_ID']);
		$this->Tipo_Spesa_9 = utf8_decode($val['Tipo_Spesa_9']);
		$this->Extra_Spesa_9 = utf8_decode($val['Extra_Spesa_9']);
		$this->Rimborso_9 = utf8_decode($val['Rimborso_9']);
		
		$this->Tipo_Totale_10 = utf8_decode($val['Tipo_Totale_10']);
		$this->Spesa_10_ID = utf8_decode($val['Spesa_10_ID']);
		$this->Tipo_Spesa_10 = utf8_decode($val['Tipo_Spesa_10']);
		$this->Extra_Spesa_10 = utf8_decode($val['Extra_Spesa_10']);
		$this->Rimborso_10 = utf8_decode($val['Rimborso_10']);
		
		$this->Totale_Rimborso = utf8_decode($val['Totale_Rimborso']);

	}
	
	public function totali_spese()
	{
		$totale['totale_1'] = 0;
		$totale['totale_2'] = 0;
		$totale['totale_3'] = 0;
		switch($this->Tipo_Totale_1)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_1;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_1;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_1;		break;
		}
		switch($this->Tipo_Totale_2)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_2;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_2;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_2;		break;
		}
		switch($this->Tipo_Totale_3)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_3;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_3;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_3;		break;
		}
		switch($this->Tipo_Totale_4)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_4;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_4;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_4;		break;
		}
		switch($this->Tipo_Totale_5)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_5;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_5;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_5;		break;
		}
		switch($this->Tipo_Totale_6)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_6;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_6;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_6;		break;
		}
		switch($this->Tipo_Totale_7)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_7;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_7;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_7;		break;
		}
		switch($this->Tipo_Totale_8)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_8;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_8;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_8;		break;
		}
		switch($this->Tipo_Totale_9)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_9;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_9;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_9;		break;
		}
		switch($this->Tipo_Totale_10)
		{
			case 1:		$totale['totale_1']	+=	$this->Rimborso_10;		break;
			case 2:		$totale['totale_2']	+=	$this->Rimborso_10;		break;
			case 3:		$totale['totale_3']	+=	$this->Rimborso_10;		break;
		}
		
		$this->Totali_Array[1] = conv_num(number_format($totale['totale_1'],2));
		$this->Totali_Array[2] = conv_num(number_format($totale['totale_1']+$totale['totale_2'],2));
		$this->Totali_Array[3] = conv_num(number_format($totale['totale_1']+$totale['totale_2']+$totale['totale_3'],2));
		
		return $totale;
	}
	
	public function spese_array()
	{
		$spese = array();
		
		$spese[1]['ID'] 			= 	$this->Spesa_1_ID;
		$spese[2]['ID'] 			= 	$this->Spesa_2_ID;
		$spese[3]['ID'] 			= 	$this->Spesa_3_ID;
		$spese[4]['ID'] 			= 	$this->Spesa_4_ID;
		$spese[5]['ID'] 			= 	$this->Spesa_5_ID;
		$spese[6]['ID'] 			= 	$this->Spesa_6_ID;
		$spese[7]['ID'] 			= 	$this->Spesa_7_ID;
		$spese[8]['ID'] 			= 	$this->Spesa_8_ID;
		$spese[9]['ID'] 			= 	$this->Spesa_9_ID;
		$spese[10]['ID'] 			= 	$this->Spesa_10_ID;
		
		$spese[1]['tipo_spesa']		= 	$this->Tipo_Spesa_1;
		$spese[2]['tipo_spesa'] 	= 	$this->Tipo_Spesa_2;
		$spese[3]['tipo_spesa'] 	= 	$this->Tipo_Spesa_3;
		$spese[4]['tipo_spesa'] 	= 	$this->Tipo_Spesa_4;
		$spese[5]['tipo_spesa'] 	= 	$this->Tipo_Spesa_5;
		$spese[6]['tipo_spesa'] 	= 	$this->Tipo_Spesa_6;
		$spese[7]['tipo_spesa'] 	= 	$this->Tipo_Spesa_7;
		$spese[8]['tipo_spesa'] 	= 	$this->Tipo_Spesa_8;
		$spese[9]['tipo_spesa'] 	= 	$this->Tipo_Spesa_9;
		$spese[10]['tipo_spesa'] 	= 	$this->Tipo_Spesa_10;
		
		$spese[1]['extra_spesa']	= 	$this->Extra_Spesa_1;
		$spese[2]['extra_spesa'] 	= 	$this->Extra_Spesa_2;
		$spese[3]['extra_spesa'] 	= 	$this->Extra_Spesa_3;
		$spese[4]['extra_spesa'] 	= 	$this->Extra_Spesa_4;
		$spese[5]['extra_spesa'] 	= 	$this->Extra_Spesa_5;
		$spese[6]['extra_spesa'] 	= 	$this->Extra_Spesa_6;
		$spese[7]['extra_spesa'] 	= 	$this->Extra_Spesa_7;
		$spese[8]['extra_spesa'] 	= 	$this->Extra_Spesa_8;
		$spese[9]['extra_spesa'] 	= 	$this->Extra_Spesa_9;
		$spese[10]['extra_spesa'] 	= 	$this->Extra_Spesa_10;
		
		$spese[1]['rimborso'] 		= 	$this->Rimborso_1;
		$spese[2]['rimborso'] 		= 	$this->Rimborso_2;
		$spese[3]['rimborso'] 		= 	$this->Rimborso_3;
		$spese[4]['rimborso'] 		= 	$this->Rimborso_4;
		$spese[5]['rimborso'] 		= 	$this->Rimborso_5;
		$spese[6]['rimborso'] 		= 	$this->Rimborso_6;
		$spese[7]['rimborso'] 		= 	$this->Rimborso_7;
		$spese[8]['rimborso'] 		= 	$this->Rimborso_8;
		$spese[9]['rimborso'] 		= 	$this->Rimborso_9;
		$spese[10]['rimborso'] 		= 	$this->Rimborso_10;
		
		$spese[1]['tipo_totale'] 	= 	$this->Tipo_Totale_1;
		$spese[2]['tipo_totale'] 	= 	$this->Tipo_Totale_2;
		$spese[3]['tipo_totale'] 	= 	$this->Tipo_Totale_3;
		$spese[4]['tipo_totale'] 	= 	$this->Tipo_Totale_4;
		$spese[5]['tipo_totale'] 	= 	$this->Tipo_Totale_5;
		$spese[6]['tipo_totale'] 	= 	$this->Tipo_Totale_6;
		$spese[7]['tipo_totale'] 	= 	$this->Tipo_Totale_7;
		$spese[8]['tipo_totale'] 	= 	$this->Tipo_Totale_8;
		$spese[9]['tipo_totale'] 	= 	$this->Tipo_Totale_9;
		$spese[10]['tipo_totale'] 	= 	$this->Tipo_Totale_10;
		
		return $spese;
		
	}
	
	public function inserisci_spese_array($spese)
	{
		
		$this->Tipo_Totale_1 = $spese[1]['ID'];
		$this->Spesa_1_ID = $spese[1]['tipo_totale'];
		$this->Tipo_Spesa_1 = $spese[1]['tipo_spesa'];
		$this->Extra_Spesa_1 = $spese[1]['extra_spesa'];
		$this->Rimborso_1 = $spese[1]['rimborso'];
		
		$this->Tipo_Totale_2 = $spese[2]['ID'];
		$this->Spesa_2_ID = $spese[2]['tipo_totale'];
		$this->Tipo_Spesa_2 = $spese[2]['tipo_spesa'];
		$this->Extra_Spesa_2 = $spese[2]['extra_spesa'];
		$this->Rimborso_2 = $spese[2]['rimborso'];
		
		$this->Tipo_Totale_3 = $spese[3]['ID'];
		$this->Spesa_3_ID = $spese[3]['tipo_totale'];
		$this->Tipo_Spesa_3 = $spese[3]['tipo_spesa'];
		$this->Extra_Spesa_3 = $spese[3]['extra_spesa'];
		$this->Rimborso_3 = $spese[3]['rimborso'];
		
		$this->Tipo_Totale_4 = $spese[4]['ID'];
		$this->Spesa_4_ID = $spese[4]['tipo_totale'];
		$this->Tipo_Spesa_4 = $spese[4]['tipo_spesa'];
		$this->Extra_Spesa_4 = $spese[4]['extra_spesa'];
		$this->Rimborso_4 = $spese[4]['rimborso'];
		
		$this->Tipo_Totale_5 = $spese[5]['ID'];
		$this->Spesa_5_ID = $spese[5]['tipo_totale'];
		$this->Tipo_Spesa_5 = $spese[5]['tipo_spesa'];
		$this->Extra_Spesa_5 = $spese[5]['extra_spesa'];
		$this->Rimborso_5 = $spese[5]['rimborso'];
		
		$this->Tipo_Totale_6 = $spese[6]['ID'];
		$this->Spesa_6_ID = $spese[6]['tipo_totale'];
		$this->Tipo_Spesa_6 = $spese[6]['tipo_spesa'];
		$this->Extra_Spesa_6 = $spese[6]['extra_spesa'];
		$this->Rimborso_6 = $spese[6]['rimborso'];
		
		$this->Tipo_Totale_7 = $spese[7]['ID'];
		$this->Spesa_7_ID = $spese[7]['tipo_totale'];
		$this->Tipo_Spesa_7 = $spese[7]['tipo_spesa'];
		$this->Extra_Spesa_7 = $spese[7]['extra_spesa'];
		$this->Rimborso_7 = $spese[7]['rimborso'];
	
		$this->Tipo_Totale_8 = $spese[8]['ID'];
		$this->Spesa_8_ID = $spese[8]['tipo_totale'];
		$this->Tipo_Spesa_8 = $spese[8]['tipo_spesa'];
		$this->Extra_Spesa_8 = $spese[8]['extra_spesa'];
		$this->Rimborso_8 = $spese[8]['rimborso'];
		
		$this->Tipo_Totale_9 = $spese[9]['ID'];
		$this->Spesa_9_ID = $spese[9]['tipo_totale'];
		$this->Tipo_Spesa_9 = $spese[9]['tipo_spesa'];
		$this->Extra_Spesa_9 = $spese[9]['extra_spesa'];
		$this->Rimborso_9 = $spese[9]['rimborso'];
		
		$this->Tipo_Totale_10 = $spese[10]['ID'];
		$this->Spesa_10_ID = $spese[10]['tipo_totale'];
		$this->Tipo_Spesa_10 = $spese[10]['tipo_spesa'];
		$this->Extra_Spesa_10 = $spese[10]['extra_spesa'];
		$this->Rimborso_10 = $spese[10]['rimborso'];
		
		$totale_rimborso = 0;
		for($x=1; $x<11; $x++)
			$totale_rimborso+= $spese[$x]['rimborso'];
		
		$this->Totale_Rimborso = $totale_rimborso;
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
	public $Fonte_Dati;
    public $Data_Iscrizione_Fermo;

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
		$this->Fonte_Dati = utf8_decode($val['Fonte_Dati']);
        $this->Data_Iscrizione_Fermo = utf8_decode($val['Data_Iscrizione_Fermo']);
		
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

class pignoramento_immobiliare
{

	public $ID;
	public $CC;
	public $Pignoramento_ID;
	
	public $Tipo_Immobiliare;
	public $Situazione;
	public $Foglio;
	public $Particella;
	public $Subalterno;
	public $Classe;
	public $Annotazioni;
	
	public $Efficacia;
	public $Efficacia_Registrazione;
	public $Efficacia_Tipo_Numero_Nota;
	public $Termine;
	public $Termine_Registrazione;
	public $Termine_Tipo_Numero_Nota;
	
	//FABBRICATO
	public $Sezione_Fabbricato;
	public $Zona_Censuaria_Fabbricato;
	public $Categoria_Fabbricato;
	public $Consistenza_Fabbricato;
	public $Superficie_Fabbricato;
	public $Rendita_Fabbricato;
	public $Indirizzo_Fabbricato;
	public $Protocollo_Notifica_Fabbricato;
	
	//TERRENO
	public $Porzione_Terreno;
	public $Qualita_Terreno;
	public $Descrizione_Qualita_Terreno;
	public $HA_Ettari_Terreno;
	public $A_Are_Terreno;
	public $C_Centiare_Terreno;
	public $Dominicale_Terreno;
	public $Agrario_Terreno;
	public $Deduzioni_Terreno;	
	
	//PROPRIETARIO
	public $Parte_Proprietario;
	public $Totale_Proprietario;
	
	public $Efficacia_Proprietario;
	public $Efficacia_Registrazione_Proprietario;
	public $Efficacia_Tipo_Numero_Nota_Proprietario;
	public $Termine_Proprietario;
	public $Termine_Registrazione_Proprietario;
	public $Termine_Tipo_Numero_Nota_Proprietario;
	

	public function __construct( $progr , $c )
	{
		$query = "SELECT * FROM pignoramento_immobiliare WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Pignoramento_ID = utf8_decode($val['Pignoramento_ID']);
		
		$this->Tipo_Immobiliare = utf8_decode($val['Tipo_Immobiliare']);
		$this->Situazione = utf8_decode($val['Situazione']);
		$this->Foglio = utf8_decode($val['Foglio']);
		$this->Particella = utf8_decode($val['Particella']);
		$this->Subalterno = utf8_decode($val['Subalterno']);
		$this->Classe = utf8_decode($val['Classe']);
		$this->Annotazioni = utf8_decode($val['Annotazioni']);
		
		$this->Efficacia = utf8_decode($val['Efficacia']);
		$this->Efficacia_Registrazione = utf8_decode($val['Efficacia_Registrazione']);
		$this->Efficacia_Tipo_Numero_Nota = utf8_decode($val['Efficacia_Tipo_Numero_Nota']);
		$this->Termine = utf8_decode($val['Termine']);
		$this->Termine_Registrazione = utf8_decode($val['Termine_Registrazione']);
		$this->Termine_Tipo_Numero_Nota = utf8_decode($val['Termine_Tipo_Numero_Nota']);
		
		//FABBRICATO
		$this->Sezione_Fabbricato = utf8_decode($val['Sezione_Fabbricato']);
		$this->Zona_Censuaria_Fabbricato = utf8_decode($val['Zona_Censuaria_Fabbricato']);
		$this->Categoria_Fabbricato = utf8_decode($val['Categoria_Fabbricato']);
		$this->Consistenza_Fabbricato = utf8_decode($val['Consistenza_Fabbricato']);
		$this->Superficie_Fabbricato = utf8_decode($val['Superficie_Fabbricato']);
		$this->Rendita_Fabbricato = utf8_decode($val['Rendita_Fabbricato']);
		$this->Indirizzo_Fabbricato = utf8_decode($val['Indirizzo_Fabbricato']);
		$this->Protocollo_Notifica_Fabbricato = utf8_decode($val['Protocollo_Notifica_Fabbricato']);
		
		//TERRENO
		$this->Porzione_Terreno = utf8_decode($val['Porzione_Terreno']);
		$this->Qualita_Terreno = utf8_decode($val['Qualita_Terreno']);
		$this->Descrizione_Qualita_Terreno = utf8_decode($val['Descrizione_Qualita_Terreno']);
		$this->HA_Ettari_Terreno = utf8_decode($val['HA_Ettari_Terreno']);
		$this->A_Are_Terreno = utf8_decode($val['A_Are_Terreno']);
		$this->C_Centiare_Terreno = utf8_decode($val['C_Centiare_Terreno']);
		$this->Dominicale_Terreno = utf8_decode($val['Dominicale_Terreno']);
		$this->Agrario_Terreno = utf8_decode($val['Agrario_Terreno']);
		$this->Deduzioni_Terreno = utf8_decode($val['Deduzioni_Terreno']);
				
		//PROPRIETARIO
		$this->Parte_Proprietario = utf8_decode($val['Parte_Proprietario']);
		$this->Totale_Proprietario = utf8_decode($val['Totale_Proprietario']);
		
		$this->Efficacia_Proprietario = utf8_decode($val['Efficacia_Proprietario']);
		$this->Efficacia_Registrazione_Proprietario = utf8_decode($val['Efficacia_Registrazione_Proprietario']);
		$this->Efficacia_Tipo_Numero_Nota_Proprietario = utf8_decode($val['Efficacia_Tipo_Numero_Nota_Proprietario']);
		$this->Termine_Proprietario = utf8_decode($val['Termine_Proprietario']);
		$this->Termine_Registrazione_Proprietario = utf8_decode($val['Termine_Registrazione_Proprietario']);
		$this->Termine_Tipo_Numero_Nota_Proprietario = utf8_decode($val['Termine_Tipo_Numero_Nota_Proprietario']);


	}

	public function Delete ()
	{
		$query = "DELETE FROM pignoramento_immobiliare WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
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

		$query = table_insert_record_query ("pignoramento_immobiliare", $fields, $values);
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

		$query = table_update_record_query ("pignoramento_immobiliare", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

?>