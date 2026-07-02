<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once CLS . "/cls_db.php";


class ruolo
{
	public $ID;
	public $CC;
	public $Comune_ID;
	public $Data_Fornitura;
	public $Progr_Fornitura;
	public $Descrizione;
	public $Ruolo;
	public $Num_Rate;
	public $Num_Ruolo;
	public $Data_Inserimento;

	public $Partita = array();

	public function __construct( $progr , $c , $a = null, $partite = true )
	{
		$query = "SELECT * FROM ruolo WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->Data_Fornitura = utf8_decode($val['Data_Fornitura']);
		$this->Progr_Fornitura = utf8_decode($val['Progr_Fornitura']);
		$this->Descrizione = utf8_decode($val['Descrizione']);
		$this->Ruolo = utf8_decode($val['Ruolo']);
		$this->Num_Rate = utf8_decode($val['Num_Rate']);
		$this->Num_Ruolo = utf8_decode($val['Num_Ruolo']);
		$this->Data_Inserimento = utf8_decode($val['Data_Inserimento']);

		$partita_id = select_mysql_array("ID", "partita_tributi" , "Ruolo_ID = '".$this->ID."' AND CC = '".$c."'");

		if($partite===true)
		{
			for( $i=0; $i<count($partita_id); $i++ )
			{
				$this->Partita[$i] = new partita( $partita_id[$i]['ID'] , $c );
			}
		}
	}

	public function Insert()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && $key != "Partita")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("ruolo", $fields, $values);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update($valoreCampo, $campo = "ID")
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false && $key != "Partita")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_update_record_query ("ruolo", $fields, $values, $campo , $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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
	public $Sottotipo;
	public $Utente_ID;
	public $Coo_ID = array();
	public $Coo_Tipo;
	public $Flag_Blocco_Coazione;
	public $Flag_Blocco_Maggiorazioni;
	public $Flag_Blocco_Diritto_Riscossione;
	public $Motivo_Blocco;
	public $Note_Blocco;
	public $Cancellazione;

	public $File_1;
	public $File_2;
    public $Split_Parameters_ID;

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
    public $Ingiunzione;
	public $Pagamenti_Atti_Precedenti;
	public $Data_Inizio_Interessi;

	public $Somma_Spese_Notifica = 0;
	public $a_tributi;

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
		$this->Sottotipo = utf8_decode($val['Sottotipo']);
		$this->Utente_ID = utf8_decode($val['Utente_ID']);

		$this->File_1 = utf8_decode($val['File_1']);
		$this->File_2 = utf8_decode($val['File_2']);

		$this->Utente = new utente($val['Utente_ID'], $c);

		$this->Flag_Blocco_Coazione = utf8_decode($val['Flag_Blocco_Coazione']);
		$this->Flag_Blocco_Maggiorazioni = utf8_decode($val['Flag_Blocco_Maggiorazioni']);
		$this->Flag_Blocco_Diritto_Riscossione = utf8_decode($val['Flag_Blocco_Diritto_Riscossione']);

		$this->Motivo_Blocco = utf8_decode($val['Motivo_Blocco']);
		$this->Note_Blocco = utf8_decode($val['Note_Blocco']);
		$this->Cancellazione = utf8_decode($val['Cancellazione']);
		$this->Coo_Tipo = utf8_decode($val['Coo_Tipo']);
        $this->Split_Parameters_ID = utf8_decode($val['Split_Parameters_ID']);

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

		$tributo_id = select_mysql_array("ID", "tributo","Partita_ID = '".$this->ID."'","Codice_Tributo");
		$atto_id = select_mysql_array("ID", "atto","Partita_ID = '".$this->ID."'");
		$pagamento_id = select_mysql_array("ID", "pagamento","Partita_ID = '".$this->ID."'");
		$ricorso_id = select_mysql_array("ID", "ricorso_generale", "Partita_ID = '".$this->ID."'");
		$pignoramento_id = select_mysql_array("ID", "pignoramento_generale", "Partita_ID = '".$this->ID."'");

        $contaTributi = 0;
        $codice_tributo = "";

        for( $i=0; $i<count($tributo_id); $i++) {
            $this->Tributo[$i] = new tributo($tributo_id[$i]['ID'], $c);
        }

        for( $i=0; $i<count($tributo_id); $i++)
        {
            $array_tributo = new tributo( $tributo_id[$i]['ID'] , $c );

            if($array_tributo->Tipo_Codice=="IMPORTO" && from_mysql_date($array_tributo->Data_Decorrenza_Interessi)!=""){
                if(from_mysql_date($this->Data_Inizio_Interessi)=="")
                    $this->Data_Inizio_Interessi = $array_tributo->Data_Decorrenza_Interessi;

                $data_decorrenza = $array_tributo->Data_Decorrenza_Interessi;
            }

            if($codice_tributo!=$array_tributo->Codice_Tributo){
                if($i>0)
                    $contaTributi++;
                $this->a_tributi[$contaTributi] = $array_tributo;
            }
            else{
                $this->a_tributi[$contaTributi]->Imposta+= $array_tributo->Imposta;
            }

            $codice_tributo = $array_tributo->Codice_Tributo;
        }

        $temp = "";
        for($i=0;$i<count($this->a_tributi);$i++){
            if($this->a_tributi[$i]->Codice_Tributo=="5243"){
                $temp = $this->a_tributi[$i];
                unset($this->a_tributi[$i]);
                $this->a_tributi = array_values($this->a_tributi);
            }
        }
        if($temp!="")
            $this->a_tributi[]=$temp;

        $testoSemestri = "";
        $countGiri = 0;
        $dataInizioInteressi = $this->Data_Inizio_Interessi;
		for( $i=0; $i<count($atto_id); $i++)
		{
			$this->Atto[$i] = new atto( $atto_id[$i]['ID'] , $c );

			$this->Somma_Spese_Notifica += $this->Atto[$i]->Spese_Notifica;
			$this->Somma_Spese_Notifica += $this->Atto[$i]->CAN;
			$this->Somma_Spese_Notifica += $this->Atto[$i]->CAD;

            $this->Atto[$i]->Semestri = "";
			if($this->Atto[$i]->Atto == "Ingiunzione" || $this->Atto[$i]->Atto == "Avviso di intimazione ad adempiere" || $this->Atto[$i]->Atto == "Avviso di messa in mora")
			{
				$this->ultimo_atto = $atto_id[$i]['ID'];


                $data1 = new DateTime($dataInizioInteressi);
                $data2 = new DateTime($this->Atto[$i]->Data_Calcolo_Interessi);
                $interval = $data1->diff($data2);
                $this->Atto[$i]->Data_Inizio_Calcolo = $dataInizioInteressi;
                $semestri = floor($interval->format('%a')/182.5);
                if($this->Atto[$i]->Interessi>0){
                    if($this->Tipo == "CDS") {
                        if ($semestri <= 1)
                            $semestri = "1 semestre calcolato";
                        else
                            $semestri .= " semestri calcolati";
                    }
                    else{
                        $semestri = "Interesse calcolato";
                    }

                    if($countGiri>0)
                        $testoSemestri.= " + ";

                    $testoSemestri.= $semestri." dal ".from_mysql_date($this->Atto[$i]->Data_Inizio_Calcolo)." al ".from_mysql_date($this->Atto[$i]->Data_Calcolo_Interessi);

                    $countGiri++;
                }

                $this->Atto[$i]->Semestri = $testoSemestri;

                $dataInizioInteressi = $this->Atto[$i]->Data_Calcolo_Interessi;


				if($this->Atto[$i]->Atto == "Ingiunzione")
				{
                    if( $this->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-1 year" )) )
						$this->ultimo_atto_scaduto = $atto_id[$i]['ID'];
				}
				else
				{
					$this->ultimo_avviso = $atto_id[$i]['ID'];

					if( $this->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-180 days" )) )
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
			    if($ing->Atto == "Ingiunzione")
			        $this->Ingiunzione = $ing;

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
		$arrayOriginale = array("","","");

		if(count($this->Atto)>0)
		{
			$ing = $this->Atto[0];

			if($ing->Cronologico_Vecchio == "si")
			{
				$verbale = $this->Atto[0]->ID_Cronologico;
				$anno = $this->Atto[0]->Anno_Cronologico;
				$completo = $verbale . "/" . $anno;
			}
			else if($this->Tipo == "CDS")
			{
				$verbale = $this->cds_verbale();
				$anno = $this->Anno_Riferimento;
				$completo = $verbale . "/" . $anno;
			}
			else
			{
				$verbale = "";
				$anno = "";
				$completo = "";
			}

			$arrayOriginale = array($verbale, $anno, $completo);

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

	public function data_decorrenza_interessi()
	{
		for ($i = 0; $i < count($this->Tributo); $i++)
		{
			$this->Tributo[$i];

			if (to_mysql_date($this->Tributo[$i]->Data_Decorrenza_Interessi) != null)
			{
				return $this->Tributo[$i]->Data_Decorrenza_Interessi;
			}
		}

		return null;
	}

	public function sanzione_originaria()
	{
		for ($i = 0; $i < count($this->Tributo); $i++)
		{
			$this->Tributo[$i];

			if ($this->Tributo[$i]->Codice_Tributo == "5242" && $this->Tipo = "CDS")
			{
				return $this->Tributo[$i]->Imposta;
			}
		}

		return null;
	}

	public function maggiorazione_originaria()
	{
		for ($i = 0; $i < count($this->Tributo); $i++)
		{
		$this->Tributo[$i];

		if ($this->Tributo[$i]->Codice_Tributo == "5243" && $this->Tipo = "CDS")
		{
			return $this->Tributo[$i]->Imposta;
		}
		}

		return null;
	}

	public function importiSplitPayment($due=true){
        $arrayRitorno = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0, 13=>0, 14=>0, 15=>0, 16=>0);
        if($due===true){
            for ($i = 0; $i < count($this->Tributo); $i++)
                $arrayRitorno[$this->Tributo[$i]->Codice_Scorporo]+= $this->Tributo[$i]->Imposta;
        }

        return $arrayRitorno;
    }

    public function getSumCodiciTributo($a_categories){
	    $sum = 0;
        for ($i = 0; $i < count($a_categories); $i++){
            if($i==0)
                $sum-= $a_categories[$i];
            else
                $sum+= $a_categories[$i];
        }

        return $sum;
    }

	public function importiCodiciTributo()
	{
		$arrayRitorno = array("IMPORTO"=>0, "SANZIONE"=>0, "INTERESSI"=>0, "SPESE"=>0, "PAGAMENTO"=>0, "MAGGIORAZIONE"=>0, "SOMMA"=>0, "SOLLECITO"=>0, "ADDIZIONALE"=>0);
		for ($i = 0; $i < count($this->Tributo); $i++)
		{
			if($this->Tributo[$i]->Codice_Tributo == "5242")
				if($this->Tributo[$i]->Pagamenti_Associati>0)
					$arrayRitorno['PAGAMENTO'] += $this->Tributo[$i]->Pagamenti_Associati;

			if($this->Tributo[$i]->Tipo_Codice!="")
				$arrayRitorno[$this->Tributo[$i]->Tipo_Codice] += $this->Tributo[$i]->Imposta;
		}

		$arrayRitorno['SOMMA'] = $arrayRitorno['IMPORTO'] + $arrayRitorno['SANZIONE'] + $arrayRitorno['INTERESSI'] + $arrayRitorno['SPESE'];
		$arrayRitorno['SOMMA']+= $arrayRitorno['MAGGIORAZIONE'] - $arrayRitorno['PAGAMENTO'] + $arrayRitorno['ADDIZIONALE'];

		return $arrayRitorno;
	}

	public function totaleCodici(){
	    $a_codiciTrib = array("TOTALE"=>0,"IMPORTO_INTERESSI"=>0,"PAGAMENTO"=>0,"SPESE_INGIUNZIONE"=>0);
        for ($i = 0; $i < count($this->Tributo); $i++)
        {
            if($this->Tributo[$i]->Tipo_Codice=="PAGAMENTO"){
                $a_codiciTrib["PAGAMENTO"] += $this->Tributo[$i]->Imposta;
                $a_codiciTrib["TOTALE"] -= $this->Tributo[$i]->Imposta;
//                $a_codiciTrib["IMPORTO_INTERESSI"] -= $this->Tributo[$i]->Imposta;
            }
            else{
                $a_codiciTrib["TOTALE"] += $this->Tributo[$i]->Imposta;
                if($this->Tipo=="CDS"){
                    if($this->Tributo[$i]->Tipo_Codice!="INTERESSI")
                        $a_codiciTrib["IMPORTO_INTERESSI"] += $this->Tributo[$i]->Imposta;
                }
                else{
                    if($this->Tributo[$i]->Tipo_Codice=="IMPORTO")
                        $a_codiciTrib["IMPORTO_INTERESSI"] += $this->Tributo[$i]->Imposta;
                }

//                alert($this->Tributo[$i]->Codice_Tributo);
                if($this->Tributo[$i]->Codice_Tributo=="S_03")
                    $a_codiciTrib["SPESE_INGIUNZIONE"] += $this->Tributo[$i]->Imposta;
            }
        }

        if($a_codiciTrib["TOTALE"]<$a_codiciTrib["IMPORTO_INTERESSI"])
            $a_codiciTrib["IMPORTO_INTERESSI"] = $a_codiciTrib["TOTALE"];

        return $a_codiciTrib;
    }

	public function cds_verbale()
	{
		for ($i = 0; $i < count($this->Tributo); $i++)
		{
			$this->Tributo[$i];

			if ($this->Tributo[$i]->Codice_Tributo == "5242" && $this->Tipo = "CDS")
			{
				return $this->Tributo[$i]->Titolo_Sanzione;
			}
		}

		return null;
	}

	public function estraiTitoloTributo()
	{
		for ($i = 0; $i < count($this->Tributo); $i++)
		{
			$this->Tributo[$i];

			if ($this->Tributo[$i]->Tipo_Codice == "IMPORTO")
			{
				switch($this->Tributo[$i]->Tipo_Info)
				{
					case "S": return $this->Tributo[$i]->Titolo_Sanzione; 	break;
					case "E": return $this->Tributo[$i]->Titolo_Entrata; 	break;
					case "M": return $this->Tributo[$i]->Matricola;	 		break;
				}

			}
		}

		return null;
	}

	public function estraiTipoPartita($codice, $array_codici)
	{
		$array_return['Tipo'] = "";
		$array_return['Sottotipo'] = "";

		for($i=0;$i<count($array_codici);$i++){
			if($codice==$array_codici[$i]['Codice_Tributo']){
				$array_return['Tipo'] = $array_codici[$i]['Settore'];
				$array_return['Sottotipo'] = $array_codici[$i]['Sottosettore'];

				return $array_return;
			}
		}

		return null;
	}

	public function sottotipi()
	{
		$array_sottotipi = array();

		$array_sottotipi['RIFIUTI'][] = "TARES";
		$array_sottotipi['RIFIUTI'][] = "TSRSU";

		$array_sottotipi['IMMOBILI'][] = "ICI";
		$array_sottotipi['IMMOBILI'][] = "IMU";

		$array_sottotipi['CDS'] = array();
		$array_sottotipi['IRPEF'] = array();
		$array_sottotipi['PATRIMONIALE'] = array();
		$array_sottotipi['OSAP'] = array("PERMANENTE","TEMPORANEA");
        $array_sottotipi['PUBBLICITA'] = array("PERMANENTE","AFFISSIONI","TEMPORANEA");

		return $array_sottotipi;
	}

	public function option_da_array($array)
	{
		$option = "";
		for($i=0;$i<count($array);$i++)
		{
			$option.="<option value='".$array[$i]."' class='aggiunta_option'>".$array[$i]."</option>";
		}

		return $option;
	}

	public function sottotipi_option()
	{
		$sottotipi = $this->sottotipi();

		$option['RIFIUTI'] = $this->option_da_array($sottotipi['RIFIUTI']);
		$option['IMMOBILI'] = $this->option_da_array($sottotipi['IMMOBILI']);
		$option['CDS'] = $this->option_da_array($sottotipi['CDS']);
		$option['IRPEF'] = $this->option_da_array($sottotipi['IRPEF']);
		$option['PATRIMONIALE'] = $this->option_da_array($sottotipi['PATRIMONIALE']);
		$option['OSAP'] = $this->option_da_array($sottotipi['OSAP']);
        $option['PUBBLICITA'] = $this->option_da_array($sottotipi['PUBBLICITA']);

		return $option;
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
			if ($key != "ID" && isset($value) != false && is_array($value)===false && is_object($value)===false && $key!="Somma_Spese_Notifica" && $key!="Data_Inizio_Interessi")
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
			if ($key != $campo && isset($value) != false && is_array($value)===false && is_object($value)===false && $key!="Somma_Spese_Notifica" && $key!="Data_Inizio_Interessi")
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

	public function getAllPayments(){
        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Partita_ID = ".$this->ID." GROUP BY Partita_ID";
        $results = mysql_query($query);

        $line = mysql_fetch_array($results, MYSQL_ASSOC);
        return $line['TOTALE_PAGAMENTI'];
    }
}

class tributo
{
	public $ID;
	public $Comune_ID;
	public $CC;
	public $Partita_ID;
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
	public $Scorporo_Tributo;
	public $Scorporo_Interessi;
	public $Scorporo_Spese_Ricerca;
	public $Scorporo_Spese_Notifica;
	public $Scorporo_Eca;
	public $Scorporo_Tributo_Provinciale;
	public $Tipo_Codice;
    public $Codice_Scorporo;



	public function __construct( $progr , $c )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM tributo WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Partita_ID = utf8_decode($val['Partita_ID']);
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

		$this->Scorporo_Tributo = utf8_decode($val['Scorporo_Tributo']);
		$this->Scorporo_Interessi = utf8_decode($val['Scorporo_Interessi']);
		$this->Scorporo_Spese_Ricerca = utf8_decode($val['Scorporo_Spese_Ricerca']);
		$this->Scorporo_Spese_Notifica = utf8_decode($val['Scorporo_Spese_Notifica']);
		$this->Scorporo_Eca = utf8_decode($val['Scorporo_Eca']);
		$this->Scorporo_Tributo_Provinciale = utf8_decode($val['Scorporo_Tributo_Provinciale']);

		$this->Tipo_Tributo = single_answer_query("SELECT Descrizione FROM codice_tributo WHERE Codice_Tributo = '".$val['Codice_Tributo']."'");
		$this->Tipo_Codice = single_answer_query("SELECT Tipo_Codice FROM codice_tributo WHERE Codice_Tributo = '".$val['Codice_Tributo']."'");
        $this->Testo_Codice = single_answer_query("SELECT Testo_Codice FROM codice_tributo WHERE Codice_Tributo = '".$val['Codice_Tributo']."'");
        $this->Codice_Scorporo = single_answer_query("SELECT Codice_Scorporo FROM codice_tributo WHERE Codice_Tributo = '".$val['Codice_Tributo']."'");

	}

	public function Insert()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && $key != "Tipo_Tributo" && $key != "Tipo_Codice" && $key != "Codice_Scorporo" && $key != "Testo_Codice")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("tributo", $fields, $values);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update($valoreCampo , $campo = "ID")
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false && $key != "Tipo_Tributo" && $key != "Tipo_Codice" && $key != "Codice_Scorporo" && $key != "Testo_Codice")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_update_record_query("tributo", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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
	public $Pignoramento = array();

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
		$pigno_id = select_mysql_array("ID", "pignoramento_generale","Partita_ID = '".$this->ID[$k]."'");

		for( $i=0; $i<count($tributo_id); $i++)
		{
			$this->Tributo[$k][$i] = new tributo( $tributo_id[$i]['ID'] , $c );
		}

		for( $i=0; $i<count($atto_id); $i++)
		{
			$this->Atto[$k][$i] = new atto( $atto_id[$i]['ID'] , $c );
		}

		for( $i=0; $i<count($pigno_id); $i++)
		{
			$this->Pignoramento[$k][$i] = new pignoramento( $pigno_id[$i]['ID'] , $c );
		}

		$k++;

	}

	}
}

class atto
{
	public $ID;
	public $Comune_ID;
	public $DocumentTypeId;
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
	public $Rettifica_Flag;
	public $Tipo_Ufficiale;
	public $Modalita_Stampa;
    public $PrinterId;
    public $PrintTypeId;
    public $FlowId;
    public $Atto_Rettificato;
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
    public $Spese_Notifica_Precedenti;
	public $Importo;
	public $Sanzione;
	public $Data_Decorrenza_Interessi;
	public $Interessi;
	public $Spese_Notifica;
	public $CAN;
	public $CAD;
	public $Ulteriori_Spese;
	public $Interessi_Precedenti;
    public $Interessi_Codici_Tributo;
    public $Addizionale;
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

	public $Check_Pignoramento;

	public function __construct( $progr , $c )
	{
		if ($progr == null) return;
		$query = "SELECT * FROM atto WHERE ID = ".$progr." AND CC = '".$c."'";
//		if($_SESSION['username']=="mirkop")
//		    echo $query;
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
        $this->DocumentTypeId = utf8_decode($val['DocumentTypeId']);
		$this->CC = utf8_decode($val['CC']);
		$this->Partita_ID = utf8_decode($val['Partita_ID']);
		$this->Tipo_Protocollo = utf8_decode($val['Tipo_Protocollo']);
		$this->Protocollo = utf8_decode($val['Protocollo']);
		$this->Data_Protocollo = utf8_decode($val['Data_Protocollo']);
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
		$this->Rettifica_Flag = utf8_decode($val['Rettifica_Flag']);
		$this->Tipo_Ufficiale = utf8_decode($val['Tipo_Ufficiale']);
		$this->Modalita_Stampa = utf8_decode($val['Modalita_Stampa']);
        $this->PrinterId = utf8_decode($val['PrinterId']);
        $this->PrintTypeId = utf8_decode($val['PrintTypeId']);
        $this->Atto_Rettificato = utf8_decode($val['Atto_Rettificato']);
		$this->Stato_Esecuzione = utf8_decode($val['Stato_Esecuzione']);
		$this->Stato_Stampa = utf8_decode($val['Stato_Stampa']);
		$this->Fase = utf8_decode($val['Fase']);
		$this->Data_Elaborazione = utf8_decode($val['Data_Elaborazione']);
		$this->Data_Calcolo_Interessi = utf8_decode($val['Data_Calcolo_Interessi']);
		$this->Data_Stampa = utf8_decode($val['Data_Stampa']);
		$this->Data_Flusso = utf8_decode($val['Data_Flusso']);
		$this->Numero_Flusso = utf8_decode($val['Numero_Flusso']);
		$this->Anno_Flusso = utf8_decode($val['Anno_Flusso']);
		$this->FlowId = utf8_decode($val['FlowId']);
		$this->Data_Notifica = utf8_decode($val['Data_Notifica']);
		$this->Spese_Precedenti = utf8_decode($val['Spese_Precedenti']);
        $this->Spese_Notifica_Precedenti = utf8_decode($val['Spese_Notifica_Precedenti']);
		$this->Importo = utf8_decode($val['Importo']);
		$this->Sanzione = utf8_decode($val['Sanzione']);
		$this->Data_Decorrenza_Interessi = utf8_decode($val['Data_Decorrenza_Interessi']);
		$this->Interessi = utf8_decode($val['Interessi']);
		$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
		$this->CAN = utf8_decode($val['CAN']);
		$this->CAD = utf8_decode($val['CAD']);
		$this->Ulteriori_Spese = utf8_decode($val['Ulteriori_Spese']);
		$this->Interessi_Precedenti = utf8_decode($val['Interessi_Precedenti']);
        $this->Interessi_Codici_Tributo = utf8_decode($val['Interessi_Codici_Tributo']);
        $this->Addizionale = utf8_decode($val['Addizionale']);
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

		$pagamento_id = select_mysql_array("ID", "pagamento","Atto_ID = '".$this->ID."' AND Partita_ID = '".$this->Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%'", "Rata");

		for( $i=0; $i<count($pagamento_id); $i++)
		{
			$this->Pagamento[$i] = new pagamento( $pagamento_id[$i]['ID'] , $c );
		}
	}

	public function pagamenti_precedenti(){
	    $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID < ".$this->ID." AND Partita_ID = ".$this->Partita_ID." AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";
        $results = mysql_query($query);

        $line = mysql_fetch_array($results, MYSQL_ASSOC);
        return $line['TOTALE_PAGAMENTI'];
    }

    public function pagamenti_completi(){
        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID <= ".$this->ID." AND Partita_ID = ".$this->Partita_ID." AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";
        $results = mysql_query($query);

        $line = mysql_fetch_array($results, MYSQL_ASSOC);
        return $line['TOTALE_PAGAMENTI'];
    }

	public function check_notifica(){
	    $data_notifica = from_mysql_date($this->Data_Notifica);
        $giacenza = $this->Stato_Notifica;
        $ind_validato = $this->Indirizzo_Validato;

        if(($giacenza!=0 && $ind_validato!="si") || $data_notifica==null)
            $return = "n";
        else
            $return = "y";

        return $return;
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
                if($tipo_ultimo_atto=="Avviso di messa in mora")
                {
                    $atto_precedente = "Vista la notifica dell'Avviso di messa in mora n. ".$atto_current->ID_Cronologico;
                    if($atto_current->Protocollo!="")
                        $atto_precedente.= " ".$atto_current->Protocollo;
                    $atto_precedente.= " del ".$atto_current->Anno_Cronologico." effettuata il ".from_mysql_date($atto_current->Data_Notifica).".";
                    break;
                }
				else if($tipo_ultimo_atto=="Ingiunzione")
				{
					$atto_precedente = "Vista la notifica dell'Ingiunzione di pagamento n. ".$atto_current->ID_Cronologico;
					if($atto_current->Protocollo!="")
                        $atto_precedente.= " ".$atto_current->Protocollo;
					$atto_precedente.= " del ".$atto_current->Anno_Cronologico." effettuata il ".from_mysql_date($atto_current->Data_Notifica).".";
					break;
				}
				else if($tipo_ultimo_atto=="Avviso di intimazione ad adempiere")
				{
					$atto_precedente = "Vista la notifica dell'Avviso di intimazione ad adempiere n. ".$atto_current->ID_Cronologico;
                    if($atto_current->Protocollo!="")
                        $atto_precedente.= " ".$atto_current->Protocollo;
					$atto_precedente.= " del ".$atto_current->Anno_Cronologico." effettuata il ".from_mysql_date($atto_current->Data_Notifica).".";
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

	public function controlloDataPrimoPagamento()
	{
		if(isset($this->Pagamento[0]))
		{
			return $this->Pagamento[0]->Data_Pagamento;
		}
		else
			return null;
	}

    public function gestione_totali($flag)
    {
        if($flag == "si")
        {
            $totale[1] = $this->Totale_Dovuto;
            $totale[2] = $this->Totale_Dovuto;
            $totale['diritto_1'] = 0;
            $totale['diritto_2'] = 0;
        }
        else
        {
            $totale[1] = $this->Totale_Dovuto + $this->Diritto_Riscossione_Minimo;
            $totale[2] = $this->Totale_Dovuto + $this->Diritto_Riscossione_Massimo;

            $totale['diritto_1'] = $this->Diritto_Riscossione_Minimo;
            $totale['diritto_2'] = $this->Diritto_Riscossione_Massimo;
        }

        return $totale;

    }

    public function getTotalAmountDue()
    {
        $a_amount['tot'] = $this->Totale_Dovuto;
        if($this->Rate_Previste>0){
            if($this->Tipo_Totale_Rate==1){
                $a_amount['tot'] += $this->Diritto_Riscossione_Minimo;
                $a_amount['diritto'] = $this->Diritto_Riscossione_Minimo;
            }
            else{
                $a_amount['tot'] += $this->Diritto_Riscossione_Massimo;
                $a_amount['diritto'] = $this->Diritto_Riscossione_Massimo;
            }
        }
        else{
            $data_not = new DateTime($this->Data_Notifica);
            $data_not->modify("+2 months");
            if ($this->controlloDataPrimoPagamento() > $data_not->format('Y-m-d')){
                $a_amount['tot'] += $this->Diritto_Riscossione_Massimo;
                $a_amount['diritto'] = $this->Diritto_Riscossione_Massimo;
            }
            else{
                $a_amount['tot'] += $this->Diritto_Riscossione_Minimo;
                $a_amount['diritto'] = $this->Diritto_Riscossione_Minimo;
            }
        }

        $a_amount['tot_residuo'] = $a_amount['tot'] - $this->pagamenti_completi();

        return $a_amount;
    }

    public function getTotalSplit($pagamenti = null)
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

	public function selezioneTotaleDovuto($flag)
	{
		$totali = $this->gestione_totali($flag);
		$data_pag = $this->controlloDataPrimoPagamento();

		$data_not = new DateTime($this->Data_Notifica);
		$data_not->modify("+2 months");
		if($flag=="si")
		{
			$totale_sel['tot'] = $this->Totale_Dovuto;
			$totale_sel['diritto'] = 0;
		}
		else if($data_pag>$data_not->format('Y-m-d'))
		{
			$totale_sel['tot'] = $totali[2];
			$totale_sel['diritto'] = $totali['diritto_2'];
		}
		else
		{
			$totale_sel['tot'] = $totali[1];
			$totale_sel['diritto'] = $totali['diritto_1'];
		}

		return $totale_sel;
	}

	public function controlloAvviso($tipo_partita)
	{
		if($this->Atto!="Avviso di intimazione ad adempiere")
			$stringa = "L'ultimo atto elaborato e' un/a ".$this->Atto."!";
		else if(from_mysql_date($this->Data_Notifica)=="")
			$stringa = "Data di notifica assente per l'Avviso di intimazione ad adempiere!";
		else if($this->Data_Notifica <= date("Y-m-d" , strtotime( date('Y-m-d')." -180 days" )))
			$stringa = "Avviso di intimazione ad adempiere scaduto! Sono passati 180 giorni dalla data di notifica.";
        else if($this->Stato_Notifica>0 && $this->Indirizzo_Validato!="si")
            $stringa = "Nell'".$this->Atto." e' presente uno stato di giacenza. E' necessario validare l'indirizzo per rendere regolare la notifica.";
		else
			$stringa = $this->controlloPagamenti($tipo_partita);

		return $stringa;
	}

    public function controlloIngiunzione($tipo_partita)
    {
        if($this->Atto!="Ingiunzione")
            $stringa = "L'ultimo atto elaborato e' un/a ".$this->Atto."!";
        else if(from_mysql_date($this->Data_Notifica)=="")
            $stringa = "Data di notifica assente per l'Ingiunzione!";
        else if(date("Y-m-d",strtotime( $this->Data_Notifica." +120 days" )) >= date("Y-m-d" ) && date("Y-m-d",strtotime( $this->Data_Notifica." +60 days" )) >= date("Y-m-d" ))
            $stringa = "Devono essere passati 60 giorni dalla notifica dell'ingiunzione, se l'importo dovuto e' superiore ai 1000 euro, altrimenti 120 giorni dalla notifica dell'ingiunzione!";
        else if($this->Data_Notifica <= date("Y-m-d" , strtotime( date('Y-m-d')." -1 year" )))
            $stringa = "Ingiunzione scaduta! E' passato un anno dalla data di notifica.";
        else if($this->Stato_Notifica>0 && $this->Indirizzo_Validato!="si")
            $stringa = "Nell'".$this->Atto." e' presente uno stato di giacenza. E' necessario validare l'indirizzo per rendere regolare la notifica.";
        else
            $stringa = $this->controlloPagamenti($tipo_partita);

        return $stringa;
    }

    public function controlloAttoPignoramento($tipo_partita)
    {
        switch($this->Atto){
            case "Avviso di intimazione ad adempiere":
                $stringa = $this->controlloAvviso($tipo_partita);
                break;
            case "Ingiunzione":
                $stringa = $this->controlloIngiunzione($tipo_partita);
                break;
            default:
                $stringa = "L'ultimo atto elaborato '".$this->Atto."' non permette la creazione del pignoramento.";
                break;
        }

        return $stringa;
    }

	public function controlloPignoramento()
	{
		$pignoramento_id = select_mysql_array("ID", "pignoramento_generale","Atto_ID = '".$this->ID."'");
		$pignoramento = null;
		for( $i=0; $i<count($pignoramento_id); $i++)
		{
			$pignoramento[$i] = new pignoramento( $pignoramento_id[$i]['ID'] , $this->CC );
			if($i==(count($pignoramento_id)-1))
                $this->Check_Pignoramento = $pignoramento[$i];
		}

		return $pignoramento;
	}

	public function checkProcess($processType, array $a_params){
	    if($this->Partita_ID>0){
            $query = "SELECT * FROM appeal WHERE Partita_ID=".$this->Partita_ID." ORDER BY ID DESC LIMIT 1";
            $result = safe_query($query);
            $a_appeal = mysql_fetch_array($result);
            if(count($a_appeal)>0){
                if($a_appeal['ID']>0 && from_mysql_date($a_appeal['End_Date'])==""){
                    return false;
                }
            }
        }

        switch($processType){
            case "ingiunzione":
                return $this->checkIngiunzione($a_params);
                break;
            case "avviso_mora":
                return $this->checkAvvisoMora($a_params);
                break;
            case "sollecito_pre_ingiunzione":
                return $this->checkSollecitoPreIngiunzione($a_params);
                break;
            case "avviso":
                return $this->checkAvviso($a_params);
                break;
            case "sollecito":
                return $this->checkSollecito($a_params);
                break;
            case "pignoramento":
                return $this->checkPignoramento($a_params);
                break;
        }
    }

    public function checkAvvisoMora(array $a_params){
        if($this->checkPagamenti($a_params)===false)
            return false;
        else{
            if($this->Atto=="Avviso di messa in mora" || $this->Atto=="Sollecito pre ingiunzione"){
                if($this->Rielabora_Flag=="si")
                    return true;
                else if($this->checkExpireDate(false)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkSollecitoPreIngiunzione(array $a_params){
        if($this->checkPagamenti($a_params)===false)
            return false;
        else{
            if($this->Atto=="Sollecito pre ingiunzione"){
                if($this->Rielabora_Flag=="si")
                    return true;
                else if($this->checkExpireDate(false)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkSollecito(array $a_params){

        if($this->checkPagamenti($a_params)===false)
            return false;
        else{
            if(($this->Atto=="Ingiunzione" || $this->Atto=="Sollecito di pagamento")){
                if($this->Rielabora_Flag=="si")
                    return true;
                else if($this->controlloPignoramento()!=null)
                    return false;
                else if($this->checkExpireDate(true)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;
        }
    }

    public function checkPignoramento(array $a_params){
        if(to_mysql_date($this->Data_Notifica)==null)
            return false;
        else if($this->controlloPignoramento()!=null){
            return false;
        }
        else if($this->checkPagamenti($a_params)===false)
            return false;
        else if($this->checkPignoramentoDates($this->Totale_Dovuto)===false)
            return false;
        else if($this->Stato_Notifica!=0 && $this->Indirizzo_Validato!="si")
            return false;
        else
            return true;
    }

    public function checkAvviso(array $a_params){
        if($this->checkPagamenti($a_params)===false)
            return false;
        else{
            if(($this->Atto=="Ingiunzione" || $this->Atto=="Avviso di intimazione ad adempiere")){
                if($this->Rielabora_Flag=="si")
                    return true;
                else if($this->controlloPignoramento()!=null){
                    $array_pignoramenti = $this->controlloPignoramento();
                    $pignoramento = $array_pignoramenti[(count($array_pignoramenti)-1)];

                    if($pignoramento->Stato_Stampa == "Stampato" || $pignoramento->ID_Cronologico > 0)
                        return false;
                }
                else if(to_mysql_date($this->Data_Notifica)==null)
                    return false;
                else if($this->Rettifica_Flag=="si") {
                    return false;
                }
                else if($this->checkExpireDate(false)===false){
                    return false;
                }
                else if($this->Stato_Notifica!=0 && $this->Indirizzo_Validato!="si"){
                    return false;
                }
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkIngiunzione(array $a_params){
        if($this->checkPagamenti($a_params)===false)
            return false;
        else{
            if($this->Rielabora_Flag=="si")
                return true;
            else if($this->Rettifica_Flag=="si")
                return true;
            else if(to_mysql_date($this->Data_Notifica)==null)
                return false;
            else if($this->controlloPignoramento()!=null){
                $array_pignoramenti = $this->controlloPignoramento();
                $pignoramento = $array_pignoramenti[(count($array_pignoramenti)-1)];

                if($pignoramento->Stato_Pignoramento!="Annullato")
                    return false;
            }
            else if($this->checkExpireDate(false)===false)
                return false;
            else if($this->Stato_Notifica!=0 && $this->Indirizzo_Validato!="si")
                return false;
            else
                return true;
        }

    }

    public function rateizzazione(array $a_params)
    {
        if ($this->Rate_Previste > 0) {

            if ($this->Totale_Dovuto - $this->pagamenti_completi() > $a_params['importo_minimo']) {

                $tot_dovuto = array_sum($this->Importi_Rate);
                $probRata = $tot_dovuto / $this->Rate_Previste;
                $pagamenti = $this->pagamenti_completi();
                $ratePagate = $pagamenti / $probRata;
                $ratePagate = round($ratePagate, 0);

                $data_rata = new DateTime(to_mysql_date($this->Scadenze_Rate[$ratePagate + 1]));
                $data_rata->modify("+3 months");

                if (date('Y-m-d') > $data_rata->format('Y-m-d'))
                    return array("rateizzazione" => true, "status"=> "expired",
                        "instalment_date" => $this->Scadenze_Rate[$ratePagate + 1], "instalment_amount" => $this->Importi_Rate[$ratePagate + 1]);
                else
                    return array("rateizzazione" => true, "status"=> "ongoing");
            }
            else
                return array("rateizzazione" => true, "status"=> "completed");
        }
        else
            return array("rateizzazione" => false);

    }

    public function checkPagamenti(array $a_params){
        if(!isset($a_params['importo_minimo'])){
            return false;
        }

        $totale_dovuto = $this->Totale_Dovuto;

        $data_not = new DateTime($this->Data_Notifica);
        $data_not->modify("+2 months");
        if($this->controlloDataPrimoPagamento()!=null){
            if ($this->controlloDataPrimoPagamento() > $data_not->format('Y-m-d'))
                $totale_dovuto += $this->Diritto_Riscossione_Massimo;
            else
                $totale_dovuto += $this->Diritto_Riscossione_Minimo;
        }
        else{
            if (date("Y-m-d") > $data_not->format('Y-m-d'))
                $totale_dovuto += $this->Diritto_Riscossione_Massimo;
            else
                $totale_dovuto += $this->Diritto_Riscossione_Minimo;
        }

        if ($totale_dovuto - $this->pagamenti_completi() > $a_params['importo_minimo']) {
            if ($this->Rate_Previste > 0) {
                $data_rata = new DateTime(to_mysql_date($this->Scadenze_Rate[count($this->Scadenze_Rate) - 1]));
                $data_rata->modify("+3 months");
                if (date('Y-m-d') > $data_rata->format('Y-m-d'))
                    return true;
                else
                    return false;
            }
            else
                return true;
        }
        else
            return false;
    }

    public function checkExpireDate($valid){

        switch($this->Atto){
            case "Sollecito pre ingiunzione":
                if(from_mysql_date($this->Data_Stampa)!=null){
                    $expireDate = new DateTime($this->Data_Stampa);
                    $expireDate->modify("+1 month");
                }
                else
                    return false;

                break;
            case "Avviso di messa in mora":
                if(from_mysql_date($this->Data_Notifica)!=null){
                    $expireDate = new DateTime($this->Data_Notifica);
                    $expireDate->modify("+15 days");
                }
                else
                    return false;

                break;
            case "Ingiunzione":
                if(from_mysql_date($this->Data_Notifica)!=null) {
                    $expireDate = new DateTime($this->Data_Notifica);
                    $expireDate->modify("+1 year");

                    $startDate = new DateTime($this->Data_Notifica);
                    $startDate->modify("+1 month");
                }
                else
                    return false;
                break;
            case "Sollecito di pagamento":
                if(from_mysql_date($this->Data_Stampa)!=null) {
                    $expireDate = new DateTime($this->Data_Stampa);
                    $expireDate->modify("+1 month");
                }
                else
                    return false;
                break;
            case "Avviso di intimazione ad adempiere":
                if(from_mysql_date($this->Data_Notifica)!=null) {
                    $expireDate = new DateTime($this->Data_Notifica);
                    $expireDate->modify("+6 months");
                }
                else
                    return false;

                break;
        }

        if($valid===false) {
            if (isset($expireDate)) {
                if (date("Y-m-d") < $expireDate->format("Y-m-d"))
                    return false;
                else
                    return true;
            }
        }
        else{
            if($this->Atto=="Ingiunzione"){
                if(isset($startDate)){
                    if( date("Y-m-d") < $startDate->format("Y-m-d") )
                        return false;
                    else
                        return true;
                }
                if (isset($expireDate)) {
                    if (date("Y-m-d") > $expireDate->format("Y-m-d"))
                        return false;
                    else
                        return true;
                }
            }
            else{
                if (isset($expireDate)) {
                    if (date("Y-m-d") < $expireDate->format("Y-m-d"))
                        return false;
                    else
                        return true;
                }
            }
        }
//        else if($valid===true){
//            if( date("Y-m-d") > $expireDate->format("Y-m-d") && to_mysql_date($expireDate->format("Y-m-d"))!=null)
//                return false;
//            else
//                return true;
//        }

    }

    public function checkPignoramentoDates($totaleDovuto){
        $beginDate = new DateTime($this->Data_Notifica);
        $expireDate = new DateTime($this->Data_Notifica);
        switch($this->Atto)
        {
            case "Ingiunzione":
                if($totaleDovuto>=1000)
                    $beginDate->modify("+2 months");
                else
                    $beginDate->modify("+4 months");

                $expireDate->modify("+1 year");
                break;
            case "Avviso di intimazione ad adempiere":
                $beginDate->modify("+1 month");
                $expireDate->modify("+6 months");
                break;
        }


        if( date("Y-m-d") >= $beginDate->format("Y-m-d") && date("Y-m-d") < $expireDate->format("Y-m-d"))
            return true;
        else
            return false;

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
            case "Sollecito pre ingiunzione":				$quinto_campo.="11";	break;
            case "Avviso di messa in mora":				    $quinto_campo.="12";	break;
            case "Sollecito di pagamento":					$quinto_campo.="03";	break;
			case "Ingiunzione":								$quinto_campo.="02";	break;
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
			case "02": $tipoServizio = "Ingiunzione"; break;
			case "03": $tipoServizio = "Sollecito di pagamento"; break;
			case "04": $tipoServizio = "Avviso di intimazione ad adempiere"; break;
			case "05": $tipoServizio = "Sollecito avviso di intimazione"; break;
			case "06": $tipoServizio = "Pignoramento beni mobili registrati"; break;
			case "07": $tipoServizio = "Pignoramento presso datore di lavoro"; break;
			case "08": $tipoServizio = "Pignoramento presso banca"; break;

            case "12": $tipoServizio = "Avviso di messa in mora"; break;
            case "11": $tipoServizio = "Sollecito pre ingiunzione"; break;
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
        else if($tipo_atto == "Sollecito pre ingiunzione" || $tipo_atto=="SOLL_PRE")
        {
            $cartella = "Solleciti_Pre_Ingiunzione";
            $prefisso = "sollecitoPreIngiunzione_";
        }
        else if($tipo_atto == "Avviso di messa in mora" || $tipo_atto=="AV_MORA")
        {
            $cartella = "Avvisi_Messa_In_Mora";
            $prefisso = "avvisoMessaInMora_";
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
			crea_dir($dir);
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
		}

		$scorporo['somma'] = $scorporo['tributo'] + $scorporo['spese_ricerca'] + $scorporo['spese_precedenti'] + $scorporo['spese_notifica'];
		$scorporo['somma']+= $scorporo['interessi'] + $scorporo['diritto_riscossione'] + $scorporo['eca'] + $scorporo['tributo_provinciale'];

		return $scorporo;
	}

	public function calcola_numero_rate_previste($totale_dovuto)
	{
		return round($totale_dovuto/$this->Pagamento[0]->Importo);
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

		$rimanenza['addizionali'] = $this->Addizionale;

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
        else if($tipoAtto == "AV_MORA")		    $tipoAtto = "Avviso di messa in mora";
        else if($tipoAtto == "SOLL_PRE")		$tipoAtto = "Sollecito pre ingiunzione";
        else if($tipoAtto == "SOLLECITOINGIUNZIONE")		$tipoAtto = "Sollecito di pagamento";

		$query = "SELECT ID ";
		$query.= "FROM atto ";
		$query.= "WHERE CC = '".$CC."' AND Atto = '".$tipoAtto."' AND ID_Cronologico ='".$cronologico[0]."' AND ";
		$query.= "Anno_Cronologico = ".$cronologico[1]." ";

		$return = single_query($query);

		return $return;

	}

    public function getIDFromCrono ( $tipoAtto, $crono, $CC )
    {
        $cronologico = explode("/", $crono);
        if($tipoAtto == "AVVISOINTIMAZIONE")	$tipoAtto = "Avviso di intimazione ad adempiere";
        else if($tipoAtto == "INGIUNZIONE")		$tipoAtto = "Ingiunzione";
        else if($tipoAtto == "AV_MORA")		    $tipoAtto = "Avviso di messa in mora";
        else if($tipoAtto == "SOLL_PRE")		$tipoAtto = "Sollecito pre ingiunzione";
        else if($tipoAtto == "SOLLECITOINGIUNZIONE")		$tipoAtto = "Sollecito di pagamento";

        $query = "SELECT atto.ID, partita_tributi.Tipo AS Tipo_Partita ";
        $query.= "FROM atto JOIN partita_tributi ON partita_tributi.ID = atto.Partita_ID ";
        $query.= "WHERE atto.CC = '".$CC."' AND atto.Atto = '".$tipoAtto."' AND atto.ID_Cronologico=".$cronologico[0]." AND ";
        $query.= "atto.Anno_Cronologico = ".$cronologico[1]." ";

        $result = mysql_query($query);
        $array_result = mysql_fetch_row($result);
        return $array_result;

    }

    public function getDocumentFromCrono ( $documentTypeId, $crono, $CC )
    {
        $cronologico = explode("/", $crono);

        $query = "SELECT atto.*, partita_tributi.Tipo AS Tipo_Partita ";
        $query.= "FROM atto JOIN partita_tributi ON partita_tributi.ID = atto.Partita_ID ";
        $query.= "WHERE atto.CC = '".$CC."' AND atto.DocumentTypeId = ".$documentTypeId." AND atto.ID_Cronologico=".$cronologico[0]." AND ";
        $query.= "atto.Anno_Cronologico = ".$cronologico[1]." ";

        $result = mysql_query($query);
        $array_result = mysql_fetch_array($result, MYSQL_ASSOC);
//        $array_result = mysql_fetch_row($result);
        return $array_result;

    }

	public function info_spedizione()
	{
		switch($this->Atto)
		{
            case "Sollecito pre ingiunzione":               $tipo_atto = "SOLL_PRE"; break;
			case "Ingiunzione": 							$tipo_atto = "INGIUNZIONE"; break;
            case "Sollecito di pagamento": 					$tipo_atto = "SOLLECITOINGIUNZIONE"; break;
			case "Avviso di intimazione ad adempiere": 		$tipo_atto = "AVVISOINTIMAZIONE"; break;
            case "Avviso di messa in mora": 		        $tipo_atto = "AV_MORA"; break;
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
    public $DocumentTypeId;
    public $DocumentTableTypeId;

	public $Scorporo_Tributo;
	public $Scorporo_Interessi;
	public $Scorporo_Spese_Ricerca;
	public $Scorporo_Spese_Notifica;
	public $Scorporo_Eca;
	public $Scorporo_Tributo_Provinciale;
	public $Scorporo_Spese_Precedenti;
	public $Scorporo_Notifica_Pignoramento;
	public $Scorporo_Spese_Accessorie;
	public $Scorporo_Diritto_Riscossione;

    public $Split_Payment1;
    public $Split_Payment2;
    public $Split_Payment3;
    public $Split_Payment4;
    public $Split_Payment5;
    public $Split_Payment6;
    public $Split_Payment7;
    public $Split_Payment8;
    public $Split_Payment9;
    public $Split_Payment10;
    public $Split_Payment11;
    public $Split_Payment12;
    public $Split_Payment13;
    public $Split_Payment14;
    public $Split_Payment15;
    public $Split_Payment16;

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
        $this->DocumentTypeId = utf8_decode($val['DocumentTypeId']);
        $this->DocumentTableTypeId = utf8_decode($val['DocumentTableTypeId']);

		$this->Scorporo_Tributo = utf8_decode($val['Scorporo_Tributo']);
		$this->Scorporo_Interessi = utf8_decode($val['Scorporo_Interessi']);
		$this->Scorporo_Spese_Ricerca = utf8_decode($val['Scorporo_Spese_Ricerca']);
		$this->Scorporo_Spese_Notifica = utf8_decode($val['Scorporo_Spese_Notifica']);
		$this->Scorporo_Eca = utf8_decode($val['Scorporo_Eca']);
		$this->Scorporo_Tributo_Provinciale = utf8_decode($val['Scorporo_Tributo_Provinciale']);
		$this->Scorporo_Spese_Precedenti = utf8_decode($val['Scorporo_Spese_Precedenti']);
		$this->Scorporo_Notifica_Pignoramento = utf8_decode($val['Scorporo_Notifica_Pignoramento']);
		$this->Scorporo_Spese_Accessorie = utf8_decode($val['Scorporo_Spese_Accessorie']);
		$this->Scorporo_Diritto_Riscossione = utf8_decode($val['Scorporo_Diritto_Riscossione']);

        $this->Split_Payment1 = utf8_decode($val['Split_Payment1']);
        $this->Split_Payment2 = utf8_decode($val['Split_Payment2']);
        $this->Split_Payment3 = utf8_decode($val['Split_Payment3']);
        $this->Split_Payment4 = utf8_decode($val['Split_Payment4']);
        $this->Split_Payment5 = utf8_decode($val['Split_Payment5']);
        $this->Split_Payment6 = utf8_decode($val['Split_Payment6']);
        $this->Split_Payment7 = utf8_decode($val['Split_Payment7']);
        $this->Split_Payment8 = utf8_decode($val['Split_Payment8']);
        $this->Split_Payment9 = utf8_decode($val['Split_Payment9']);
        $this->Split_Payment10 = utf8_decode($val['Split_Payment10']);
        $this->Split_Payment11 = utf8_decode($val['Split_Payment11']);
        $this->Split_Payment12 = utf8_decode($val['Split_Payment12']);
        $this->Split_Payment13 = utf8_decode($val['Split_Payment13']);
        $this->Split_Payment14 = utf8_decode($val['Split_Payment14']);
        $this->Split_Payment15 = utf8_decode($val['Split_Payment15']);
        $this->Split_Payment16 = utf8_decode($val['Split_Payment16']);
	}

	public function verifica_scorporo(){
		$somma_scorpori = $this->Scorporo_Tributo + $this->Scorporo_Interessi + $this->Scorporo_Spese_Ricerca + $this->Scorporo_Spese_Precedenti;
		$somma_scorpori+= $this->Scorporo_Spese_Notifica + $this->Scorporo_Spese_Accessorie + $this->Scorporo_Notifica_Pignoramento;
		$somma_scorpori+= $this->Scorporo_Diritto_Riscossione + $this->Scorporo_Eca + $this->Scorporo_Tributo_Provinciale;

		return $somma_scorpori;
	}

    public function getSplitSum(){
	    $sum = 0;
	    for($i=1;$i<=16;$i++){
	        $key = "Split_Payment".$i;
	        $sum+= $this->$key;
        }

        return $sum;
    }

	public function scorpori()
	{
		$scorporo = array();

		$scorporo['tributo'] = $this->Scorporo_Tributo;
		$scorporo['spese_ricerca'] = $this->Scorporo_Spese_Ricerca;
		$scorporo['spese_precedenti'] = $this->Scorporo_Spese_Precedenti;
		$scorporo['spese_notifica'] = $this->Scorporo_Spese_Notifica;
		$scorporo['interessi'] = $this->Scorporo_Interessi;
		$scorporo['diritto_riscossione'] = $this->Scorporo_Diritto_Riscossione;
		$scorporo['eca'] = $this->Scorporo_Eca;
		$scorporo['tributo_provinciale'] = $this->Scorporo_Tributo_Provinciale;
		$scorporo['spese_accessorie'] = $this->Scorporo_Spese_Accessorie;
		$scorporo['notifica_pignoramento'] = $this->Scorporo_Notifica_Pignoramento;

		$scorporo['somma'] = $scorporo['tributo'] + $scorporo['spese_ricerca'] + $scorporo['spese_precedenti'] + $scorporo['spese_notifica'];
		$scorporo['somma']+= $scorporo['interessi'] + $scorporo['diritto_riscossione'] + $scorporo['eca'] + $scorporo['tributo_provinciale'];
		$scorporo['somma']+= $scorporo['spese_accessorie'] + $scorporo['notifica_pignoramento'];

		return $scorporo;
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

    public function ListaModalitaPagamento ()
    {
        $queryModalita = "SELECT DISTINCT Modalita FROM pagamento ORDER BY Tipo_Pagamento";
        $resModalita = mysql_query($queryModalita);
        $optionModalita = "<option value=''></option>";
        while ($rigaModalita = mysql_fetch_assoc($resModalita))
        {
            if($rigaModalita['Modalita']!="")
                $optionModalita .= "<option value='" . $rigaModalita['Modalita'] . "'>" . $rigaModalita['Modalita'] . "</option>";
        }
        return $optionModalita;
    }

	public function crono_atto()
	{
		if(strpos($this->Tipo_Atto,'Pignoramento')===false)
			$atto = new atto($this->Atto_ID, $this->CC);
		else
			$atto = new pignoramento($this->Atto_ID, $this->CC);

		$this->Cronologico_Atto = $atto->ID_Cronologico."/".$atto->Anno_Cronologico;
	}

    public function cutSplitPaymentCat($a_splitCategoryDue, $splitCategoryNumber, &$a_splitCategoryPaid, &$totalPaid)
    {
        $splitDue = $a_splitCategoryDue[$splitCategoryNumber] - $a_splitCategoryPaid[$splitCategoryNumber];
        if($splitDue>0)
            $splitDue = number_format($splitDue,2,".","");

        if($totalPaid>$splitDue){
            $singleSplit = $splitDue;
            $a_splitCategoryPaid[$splitCategoryNumber] += $singleSplit;
            $totalPaid -= $singleSplit;
        }
        else{
            $singleSplit = $totalPaid;
            $a_splitCategoryPaid[$splitCategoryNumber] += $singleSplit;
            $totalPaid = 0;
        }

        return $singleSplit;
    }

    public function cutSplitPayment($a_splitDue, $splitNumber, &$a_splitPaid, &$totalPaid)
    {
        $splitDue = $a_splitDue[$splitNumber] - $a_splitPaid[$splitNumber];
        if($splitDue>0)
            $splitDue = number_format($splitDue,2,".","");

        if($totalPaid>$splitDue){
            $singleSplit = $splitDue;
            $a_splitPaid[$splitNumber] += $singleSplit;
            $totalPaid -= $singleSplit;
        }
        else{
            $singleSplit = $totalPaid;
            $a_splitPaid[$splitNumber] += $singleSplit;
            $totalPaid = 0;
        }

        return $singleSplit;
    }

    public function cutPercentSplitPayment($a_splitDue, $splitNumber, &$a_splitPaid, &$totalPaid, $percent, $singleSplit=0)
    {
        $splitDue = ($a_splitDue[$splitNumber]) * $percent;
        if($splitDue>0)
            $splitDue = number_format($splitDue,2,".","");

        if($splitDue>$a_splitDue[$splitNumber]-$a_splitPaid[$splitNumber])
            $splitDue = $a_splitDue[$splitNumber]-$a_splitPaid[$splitNumber];

        if($splitDue<=0)
            return $singleSplit;

        if($totalPaid>$splitDue){
            $singleSplit+= $splitDue;
            $a_splitPaid[$splitNumber] += $splitDue;
            $totalPaid -= $splitDue;
        }
        else{
            $singleSplit+= $totalPaid;
            $a_splitPaid[$splitNumber] += $totalPaid;
            $totalPaid = 0;
        }

        return $singleSplit;
    }

    public function cutPercentSplitPaymentCat($a_splitCategoryDue, $splitCategoryNumber, &$a_splitCategoryPaid, &$totalPaid, $percent)
    {
        $splitDue = ($a_splitCategoryDue[$splitCategoryNumber]) * $percent;
        if($splitDue>0)
            $splitDue = number_format($splitDue,2,".","");

        if($splitDue>$a_splitCategoryDue[$splitCategoryNumber]-$a_splitCategoryPaid[$splitCategoryNumber])
            $splitDue = $a_splitCategoryDue[$splitCategoryNumber]-$a_splitCategoryPaid[$splitCategoryNumber];

        if($totalPaid>$splitDue){
            $singleSplit = $splitDue;
            $a_splitCategoryPaid[$splitCategoryNumber] += $singleSplit;
            $totalPaid -= $singleSplit;
        }
        else{
            $singleSplit = $totalPaid;
            $a_splitCategoryPaid[$splitCategoryNumber] += $totalPaid;
            $totalPaid = 0;
        }

        return $singleSplit;
    }

    public function splitPayment(partita $miaPartita, atto $mioAtto, pignoramento $mioPigno, array $a_splitParams, $idParams)
    {
        if(!$miaPartita->Split_Parameters_ID>0){
            $miaPartita->Split_Parameters_ID = $idParams;
            $miaPartita->Update($miaPartita->ID);
        }

        //IMPORTI CATEGORIA DOVUTI [CODICI TRIBUTO]
        $a_splitCategoryDue = $miaPartita->importiSplitPayment();
        //IMPORTI CATEGORIA PAGATI [INIZIALIZZAZIONE]
        $a_splitCategoryPaid = $miaPartita->importiSplitPayment(false);

        $sumCodiciTributo = $miaPartita->getSumCodiciTributo($a_splitCategoryDue);


//  0       PAGAMENTI (Non in tabella)
//  1	    Imposta principale
//  2	    Spese accertamento
//  3	    Spese ingiunzione
//  4	    Spese avviso di intimazione
//  5	    Spese pignoramento
//  6	    Spese accessorie pignoramento
//  7	    Sanzione generica
//  8	    Sanzione dichiarazione
//  9	    Sanzione pagamento
//  10	    Interessi
//  11	    Altri diritti e accessori
//  12	    Spese ricerca
//  13	    Addizionale provinciale
//  14	    Oneri riscossione
//  15	    Eca
//  16	    Addizionale Comunale

        //SETTAGGIO SPESE DI RICERCA
        if(!$a_splitCategoryDue[12]>0){
            $parametri_annuale = new gestione_parametri_annuali($this->CC, $miaPartita->Anno_Riferimento, $miaPartita->Tipo);
            if ($parametri_annuale->ID != null){
                $a_splitCategoryDue[12]+= $parametri_annuale->Spese_Ricerca;
                $a_splitCategoryDue[2]-= $parametri_annuale->Spese_Ricerca;
            }
        }

        $a_splitDue = array();
        $a_splitPaid = array();

        //AGGREGO CATEGORIE IN PARAMETRI DI SCORPORO
        for($i=0;$i<count($a_splitParams);$i++){
            $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
            $a_splitPaid[$a_splitParams[$i]['split_number']] = 0;
            $a_splitRequest[$a_splitParams[$i]['split_number']] = 0;

            for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                if($a_splitParams[$i]['categories'][$y]>0)
                    $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
            }
        }

        //PRENDO I PAGAMENTI PRECEDENTI
        $query = "SELECT ID FROM pagamento WHERE Partita_ID = '".$miaPartita->ID."' AND CC='".$miaPartita->CC."' AND Tipo_Atto = 'Precedenti'";
        $id_pagamento_prec = single_answer_query($query);
        $pagamenti_precedenti = new pagamento($id_pagamento_prec, $miaPartita->CC);

        if($pagamenti_precedenti->getSplitSum()>0){
            for($i=0;$i<count($a_splitParams);$i++){
                $key = "Split_Payment".$a_splitParams[$i]['split_number'];
                $a_splitPaid[$a_splitParams[$i]['split_number']] = $pagamenti_precedenti->$key;
            }
        }
        else if($miaPartita->Tipo == "CDS"){
            /**
             * SCORPORO PAGAMENTI DA GITCO VECCHIO
             */
            $maggiorazione_sollecito = 0;
            if($a_splitCategoryDue[1] > $sumCodiciTributo)
                $maggiorazione_sollecito = $a_splitCategoryDue[1] - $sumCodiciTributo;

            $totalPaid = $a_splitCategoryDue[0] - $maggiorazione_sollecito;
            for($i=0;$i<count($a_splitParams);$i++){
                $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
                for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                    if($a_splitParams[$i]['categories'][$y]>0)
                        $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
                }
            }

            if($totalPaid>0){
                $this->cutSplitPaymentCat($a_splitCategoryDue, 12, $a_splitCategoryPaid, $totalPaid);
                $this->cutSplitPaymentCat($a_splitCategoryDue, 2, $a_splitCategoryPaid, $totalPaid);
                $this->cutSplitPaymentCat($a_splitCategoryDue, 1, $a_splitCategoryPaid, $totalPaid);

                for($i=0;$i<count($a_splitParams);$i++){
                    for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                        if($a_splitParams[$i]['categories'][$y]>0)
                            $a_splitPaid[$a_splitParams[$i]['split_number']]+= $a_splitCategoryPaid[$a_splitParams[$i]['categories'][$y]];
                    }
                }

                for($i=1;$i<=16;$i++){
                    $key = "Split_Payment".$i;
                    if(isset($a_splitPaid[$i]))
                        $pagamenti_precedenti->$key = $a_splitPaid[$i];
                }

                if($id_pagamento_prec>0)
                    $pagamenti_precedenti->Update($id_pagamento_prec);
                else{
                    $pagamenti_precedenti->Tipo_Atto = "Precedenti";
                    $pagamenti_precedenti->CC = $miaPartita->CC;
                    $pagamenti_precedenti->Partita_ID = $miaPartita->ID;
                    $pagamenti_precedenti->Data_Registrazione = date('Y-m-d');
                    $pagamenti_precedenti->Insert();
                }
            }
        }

        for($i_atto=0;$i_atto<count($miaPartita->Atto);$i_atto++) {

            $attoID = $miaPartita->Atto[$i_atto]->ID;

            if($miaPartita->Atto[$i_atto]->Atto == "Avviso di intimazione ad adempiere")
                $a_splitCategoryDue[4] += $miaPartita->Atto[$i_atto]->Spese_Notifica + $miaPartita->Atto[$i_atto]->CAN + $miaPartita->Atto[$i_atto]->CAD;
            else
                $a_splitCategoryDue[3] += $miaPartita->Atto[$i_atto]->Spese_Notifica + $miaPartita->Atto[$i_atto]->CAN + $miaPartita->Atto[$i_atto]->CAD;


            $a_splitCategoryDue[10]+= $miaPartita->Atto[$i_atto]->Interessi;

            if($miaPartita->Atto[$i_atto]->ID==$mioAtto->ID && $mioPigno->ID == null){

                $a_amountsAtto = $miaPartita->Atto[$i_atto]->getTotalAmountDue();
                $totalDue = $a_amountsAtto['tot'];
                $a_splitCategoryDue[14] = $a_amountsAtto['diritto'];
                break;
            }

            if(isset($miaPartita->Atto[$i_atto]->Pagamento[0])){
                $a_amountsAtto = $miaPartita->Atto[$i_atto]->getTotalAmountDue();
                $totalDue = $a_amountsAtto['tot'];
                $a_splitCategoryDue[14] = $a_amountsAtto['diritto'];

                $a_split_cur = $miaPartita->Atto[$i_atto]->getTotalSplit();
                if(array_sum($a_split_cur)>0){
                    for($i=0;$i<count($a_splitParams);$i++)
                        $a_splitPaid[$a_splitParams[$i]['split_number']]+= $a_split_cur[$a_splitParams[$i]['split_number']];
                }
                else{

                    for($i=0;$i<count($a_splitParams);$i++){
                        $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
                        for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                            if($a_splitParams[$i]['categories'][$y]>0)
                                $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
                        }
                    }

                    $paymentSumCur = $miaPartita->Atto[$i_atto]->totale_pagamenti();
                    $percent = $paymentSumCur / $totalDue;

                    if($paymentSumCur>0){
                        for($i=0;$i<count($a_splitParams);$i++) {
                            if ($a_splitParams[$i]['type'] == 100){
                                $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent);
                                if($paymentSumCur<=0)
                                    break;
                            }
                        }

                        for($i=0;$i<count($a_splitParams);$i++){
                            if ($a_splitParams[$i]['type'] != 100){
                                $this->cutSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur);
                                if($paymentSumCur<=0)
                                    break;
                            }
                        }
                    }
                }
            }
        }

        $attoSplit = "atto";
        //SE PAGAMENTO RIFERITO A PIGNORAMENTO
        if(strpos($this->Tipo_Atto,'Pignoramento')!==false){
            $attoSplit = "pignoramento";
            $a_amountsPigno = $mioPigno->getTotalAmountDue();

            $totalDue = conv_num($a_amountsPigno['tot']);

            $a_splitCategoryDue[5]+= $a_amountsPigno['spese_pignoramento'];
            $a_splitCategoryDue[6]+= $a_amountsPigno['spese_accessorie'];
        }

        for($i=0;$i<count($a_splitParams);$i++){
            $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
            for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                if($a_splitParams[$i]['categories'][$y]>0)
                    $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
            }
        }

        //SELEZIONO RATE RIFERITE ALL'ATTO/PIGNORAMENTO
        $queryRatePrec = "SELECT * FROM pagamento ";
        $queryRatePrec .= "WHERE Atto_ID = " . $this->Atto_ID . " AND ";
        $queryRatePrec .= "Partita_ID = " . $this->Partita_ID . " AND ";
        $queryRatePrec .= "Rata <= " . $this->Rata." ";
        $queryRatePrec .= "ORDER BY Rata DESC, Data_Pagamento ASC";

        $cls_db = new cls_db();
        $obj_rate = $cls_db->getResults($cls_db->SelectQuery($queryRatePrec),"object");

        $requestPayment = array_shift($obj_rate);

        if($attoSplit=="pignoramento")
            $requestAtto = $mioPigno;
        else
            $requestAtto = $mioAtto;

        $a_split_cur = $requestAtto->getTotalSplit($obj_rate);
        if(array_sum($a_split_cur)>0){
            for($i=0;$i<count($a_splitParams);$i++)
                $a_splitPaid[$a_splitParams[$i]['split_number']]+= $a_split_cur[$a_splitParams[$i]['split_number']];
        }
        else{
            $paymentSumCur = 0;
            for($i_rata=0;$i_rata<count($obj_rate);$i_rata++){
                $paymentSumCur+= $obj_rate[$i_rata]->Importo;
            }

            $percent = $paymentSumCur / $totalDue;
            if($paymentSumCur>0){
                for($i=0;$i<count($a_splitParams);$i++) {
                    if ($a_splitParams[$i]['type'] == 100){
                        $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                for($i=0;$i<count($a_splitParams);$i++){
                    if ($a_splitParams[$i]['type'] != 100){
                        $this->cutSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur);
                        if($paymentSumCur<=0)
                            break;
                    }
                }
            }
        }

        $requestPaymentSum = $this->getSplitSum();

        if($requestPaymentSum>0){
            for($i=0;$i<count($a_splitParams);$i++){
                $key = "Split_Payment".$a_splitParams[$i]['split_number'];
                $a_splitRequest[$a_splitParams[$i]['split_number']]+= $this->$key;
            }
        }
        else{

            $paymentSumCur = $requestPayment->Importo;

            $percent = $paymentSumCur / $totalDue;

            if($paymentSumCur>0){
                for($i=0;$i<count($a_splitParams);$i++) {
                    if ($a_splitParams[$i]['type'] == 100){
                        $a_splitRequest[$a_splitParams[$i]['split_number']] = $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                for($i=0;$i<count($a_splitParams);$i++){
                    if ($a_splitParams[$i]['type'] != 100){
                        $a_splitRequest[$a_splitParams[$i]['split_number']] = $this->cutSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                for($i=0;$i<count($a_splitParams);$i++) {
                    if ($a_splitParams[$i]['type'] == 100){
                        $a_splitRequest[$a_splitParams[$i]['split_number']] = $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent, $a_splitRequest[$a_splitParams[$i]['split_number']]);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                if($paymentSumCur>0){
                    $a_splitRequest[12]+= $paymentSumCur;
                }
            }

//            print_r($a_splitDue);
//            echo "<br><br>";
//            print_r($a_splitPaid);
//            echo "<br><br>";
//            print_r($a_splitRequest);
//            die;

            for($i=1;$i<=16;$i++){
                $key = "Split_Payment".$i;
                if(isset($a_splitRequest[$i]))
                    $this->$key = $a_splitRequest[$i];
            }

            if(number_format(array_sum($a_splitRequest),2,".","")==$this->Importo)
                $this->Update($this->ID);
            else{
                for($i=1;$i<=16;$i++)
                    $a_splitRequest[$i] = 0;
            }
        }

        return $a_splitRequest;
    }

	public function ScorporoPagamento()
	{
		$miaPartita = new partita($this->Partita_ID, $this->CC);

		$arrayVerbaleOriginario = $miaPartita->verbale_originario();
		$arrayImporti = $miaPartita->importiCodiciTributo();

		$tributo_originale = $arrayImporti['IMPORTO'];
		$spese_originale = $arrayImporti['SPESE'];
		$sanzione_originale = $arrayImporti['SANZIONE'];
		$interessi_originale = $arrayImporti['INTERESSI'];
		$pagamento_originale = $arrayImporti['PAGAMENTO'];
		$maggiorazione_originale = $arrayImporti['MAGGIORAZIONE'];
		$spese_sollecito = $arrayImporti['SOLLECITO'];

		$addizionale = $arrayImporti['ADDIZIONALE'];

		//CDS
		if($miaPartita->Tipo == "CDS")
			$importo_originale = $tributo_originale + $maggiorazione_originale + $sanzione_originale;
		else
			$importo_originale = $tributo_originale + $sanzione_originale;

		$parametri_annuale = new gestione_parametri_annuali($this->CC, $miaPartita->Anno_Riferimento, $miaPartita->Tipo);
		if ($parametri_annuale->ID == null) $ricercaTot = 0;
		else $ricercaTot = $parametri_annuale->Spese_Ricerca;

		//SE PAGAMENTO RIFERITO AD ATTO PRECEDENTE A PIGNORAMENTO
		if(strpos($this->Tipo_Atto,'Pignoramento')===false)
		{
			$mioAtto = new atto($this->Atto_ID, $this->CC);

			$totali_mioatto = $mioAtto->gestione_totali($miaPartita->Flag_Blocco_Diritto_Riscossione);
			$numero_rate = $mioAtto->Rate_Previste;

			if($numero_rate==0)
			{
				$totale_sel_mioatto = $mioAtto->selezioneTotaleDovuto($miaPartita->Flag_Blocco_Diritto_Riscossione);
				$totale_dovuto = $totale_sel_mioatto['tot'];
				$diritto = $totale_sel_mioatto['diritto'];
			}
			else
			{
				$totale_dovuto = $totali_mioatto[$mioAtto->Tipo_Totale_Rate];
				$diritto = $totali_mioatto['diritto_'.$mioAtto->Tipo_Totale_Rate];
			}

			$speseNotPigno = 0;
			$speseAccessorie = 0;
		}
		else //SE PAGAMENTO RIFERITO A PIGNORAMENTO
		{
			$mioPigno = new pignoramento($this->Atto_ID, $this->CC);
			$mioAtto = new atto($mioPigno->Atto_ID, $this->CC);
			$numero_rate_mioatto = $mioAtto->Rate_Previste;
			if($numero_rate_mioatto==0)
			{
				$totale_sel_mioatto = $mioAtto->selezioneTotaleDovuto($miaPartita->Flag_Blocco_Diritto_Riscossione);
				$diritto = $totale_sel_mioatto['diritto'];
			}
			else
			{
				$totali_mioatto = $mioAtto->gestione_totali($miaPartita->Flag_Blocco_Diritto_Riscossione);
				$diritto = $totali_mioatto['diritto_'.$mioAtto->Tipo_Totale_Rate];
			}

			$mioPigno->gestione_totali();
			$numero_rate = $mioPigno->Rate_Previste;

			if($numero_rate>0)
			{
				$totale_dovuto = $mioPigno->Totali_Array[$mioPigno->Tipo_Totale_Rate];
				$speseAccessorie = $mioPigno->Parziali_Spese_Accessorie[$mioPigno->Tipo_Totale_Rate];
			}
			else
			{
				$pagamento_pigno = $mioPigno->Pagamento[0]->Importo;

				if($pagamento_pigno == conv_num($mioPigno->Totali_Array[3]))
				{
					$totale_dovuto = $mioPigno->Totali_Array[3];
					$speseAccessorie = $mioPigno->Parziali_Spese_Accessorie[3];
				}
				else if($pagamento_pigno == conv_num($mioPigno->Totali_Array[2]))
				{
					$totale_dovuto = $mioPigno->Totali_Array[2];
					$speseAccessorie = $mioPigno->Parziali_Spese_Accessorie[2];
				}
				else
				{
					$totale_dovuto = $mioPigno->Totali_Array[1];
					$speseAccessorie = $mioPigno->Parziali_Spese_Accessorie[1];
				}
			}

			$speseNotPigno = 0;
			for($i=0;$i<count($miaPartita->Pignoramento);$i++)
			{
				if($miaPartita->Pignoramento[$i]->ID==$mioPigno->ID)
					break;

				$speseNotPigno+= $miaPartita->Pignoramento[$i]->Totale_Spese_Notifica;
			}

			$speseNotPigno+= $mioPigno->Totale_Spese_Notifica;

		}

		$interessiTot = 0;
		$ingSpese = 0;
		$avvSpese = 0;
		for($i=0;$i<count($miaPartita->Atto);$i++)
		{
			if($miaPartita->Atto[$i]->ID==$mioAtto->ID)
				break;

			$interessiTot += $miaPartita->Atto[$i]->Interessi;
			if($miaPartita->Atto[$i]->Atto == "Ingiunzione")
				$ingSpese += $miaPartita->Atto[$i]->Spese_Notifica + $miaPartita->Atto[$i]->CAN + $miaPartita->Atto[$i]->CAD + $miaPartita->Atto[$i]->Ulteriori_Spese;
			else if($miaPartita->Atto[$i]->Atto == "Avviso di intimazione ad adempiere")
				$avvSpese += $miaPartita->Atto[$i]->Spese_Notifica + $miaPartita->Atto[$i]->CAN + $miaPartita->Atto[$i]->CAD + $miaPartita->Atto[$i]->Ulteriori_Spese;
		}

		$notifica_atto_pagato = $mioAtto->Spese_Notifica + $mioAtto->CAN + $mioAtto->CAD + $mioAtto->Ulteriori_Spese;

		$precedentiTot = $ingSpese + $spese_originale - $ricercaTot;
		$notificaTot = $avvSpese;

		if($mioAtto->Atto == "Ingiunzione")
			$precedentiTot += $notifica_atto_pagato;
		else if($mioAtto->Atto == "Avviso di intimazione ad adempiere")
			$notificaTot += $notifica_atto_pagato;

		$testo_totali_scorpori_dovuti = "SPESE PRECEDENTI: ".$precedentiTot." "."SPESE RICERCA: ".$ricercaTot." "."SPESE AVVISO: ".$notificaTot." ";
		$testo_totali_scorpori_dovuti.= "MAGGIORAZIONE: ".$interessiTot." "."SPESE ACCESSORIE: ".$speseAccessorie." "."NOTIFICA PIGNO: ".$speseNotPigno." ";

// 		alert($testo_totali_scorpori_dovuti);

		$interesse_pagato = 0;
		$ricerca_pagato = 0;
		$precedenti_pagato = 0;
		$notifica_pagato = 0;
		$tributo_pagato = 0;
		$rimanenza_pagato = 0;
		$diritto_pagato = 0;
		$maggiorazione_sollecito = 0;

		$query = "SELECT ID FROM pagamento WHERE Partita_ID = '".$miaPartita->ID."' AND CC='".$miaPartita->CC."' AND Tipo_Atto = 'Precedenti'";
		$id_pagamento_prec = single_answer_query($query);
		$pagamenti_precedenti = new pagamento($id_pagamento_prec, $miaPartita->CC);

		if($pagamenti_precedenti->verifica_scorporo()>0){
			$interesse_pagato = $pagamenti_precedenti->Scorporo_Interessi;
			$ricerca_pagato = $pagamenti_precedenti->Scorporo_Spese_Ricerca;
			$precedenti_pagato = $pagamenti_precedenti->Scorporo_Spese_Precedenti;
			$notifica_pagato = $pagamenti_precedenti->Scorporo_Spese_Notifica;
			$tributo_pagato = $pagamenti_precedenti->Scorporo_Tributo;
			$diritto_pagato = $pagamenti_precedenti->Scorporo_Diritto_Riscossione;
		}
		else if($miaPartita->Tipo == "CDS"){

			$importo_partenza_ingiunzione = $mioAtto->Importo - $precedentiTot;

			if($importo_partenza_ingiunzione>$arrayImporti['SOMMA']){
				$maggiorazione_sollecito = $importo_partenza_ingiunzione - $arrayImporti['SOMMA'];

				//SCALO PAGAMENTI VERBALE CDS (se presenti)
				$pagamento_scalato = $pagamento_originale - $maggiorazione_sollecito;
				if($pagamento_scalato>0){

					if($pagamento_scalato>$ricercaTot){
						$ricerca_pagato += $ricercaTot;
						$pagamento_scalato -= $ricerca_pagato;
					}
					else{
						$ricerca_pagato = $pagamento_scalato;
						$pagamento_scalato = 0;
					}

					if($pagamento_scalato>$spese_originale-$ricercaTot){
						$precedenti_pagato += $spese_originale-$ricercaTot;
						$pagamento_scalato -= $precedenti_pagato;
					}
					else{
						$precedenti_pagato = $pagamento_scalato;
						$pagamento_scalato = 0;
					}

					if($pagamento_scalato>0){
						$tributo_pagato = $pagamento_scalato-$spese_sollecito;
						$pagamento_scalato = 0;
					}
				}
			}
			else{
				//SCALO PAGAMENTI VERBALE CDS (se presenti)
				$pagamento_scalato = $pagamento_originale;

				if($pagamento_scalato>0){

					if($pagamento_scalato>$ricercaTot){
						$ricerca_pagato += $ricercaTot;
						$pagamento_scalato -= $ricerca_pagato;
					}
					else{
						$ricerca_pagato = $pagamento_scalato;
						$pagamento_scalato = 0;
					}

					if($pagamento_scalato>$spese_originale-$ricercaTot){
						$precedenti_pagato += $spese_originale-$ricercaTot;
						$pagamento_scalato -= $precedenti_pagato;
					}
					else{
						$precedenti_pagato = $pagamento_scalato;
						$pagamento_scalato = 0;
					}

					if($pagamento_scalato>0){
						$tributo_pagato = $pagamento_scalato-$spese_sollecito;
						$pagamento_scalato = 0;
					}
				}
			}

			$pagamenti_precedenti->Scorporo_Spese_Ricerca = $ricerca_pagato;
			$pagamenti_precedenti->Scorporo_Spese_Precedenti = $precedenti_pagato;
			$pagamenti_precedenti->Scorporo_Tributo = $tributo_pagato;

			if($id_pagamento_prec>0)
				$pagamenti_precedenti->Update($id_pagamento_prec);
			else{
				$pagamenti_precedenti->Tipo_Atto = "Precedenti";
				$pagamenti_precedenti->CC = $miaPartita->CC;
				$pagamenti_precedenti->Partita_ID = $miaPartita->ID;
				$pagamenti_precedenti->Data_Registrazione = date('Y-m-d');
				$pagamenti_precedenti->Insert();
			}

		}

		$interessi_atto_cur = 0;
		//CICLO INGIUNZIONI - AVVISI DI INTIMAZIONE PRECEDENTI
		for($at=0;$at<count($miaPartita->Atto);$at++)
		{
			$atto_cur = $miaPartita->Atto[$at];

			if(strpos($this->Tipo_Atto,'Pignoramento')===false)
				if($atto_cur->ID == $mioAtto->ID)
					break;

			$numero_rate_cur = $atto_cur->Rate_Previste;
			$totali_atto_cur = $atto_cur->gestione_totali($miaPartita->Flag_Blocco_Diritto_Riscossione);

			if(isset($atto_cur->Pagamento[0])){
				if($atto_cur->Pagamento[0]->Tipo_Pagamento=="T290"){
					if($numero_rate_cur==0){

						$totale_sel_mioatto_290 = $atto_cur->selezioneTotaleDovuto($miaPartita->Flag_Blocco_Diritto_Riscossione);
						$totale_dovuto_290 = $totale_sel_mioatto_290['tot'];

						$diritto_cur = $totale_sel_mioatto_290['diritto'];
						$numero_rate_cur = $atto_cur->calcola_numero_rate_previste($totale_dovuto_290);
					}
				}
				else{
					if($numero_rate_cur==0)
					{
						$totale_sel = $atto_cur->selezioneTotaleDovuto($miaPartita->Flag_Blocco_Diritto_Riscossione);
						$dovuto_cur = $totale_sel['tot'];
						$diritto_cur = $totale_sel['diritto'];
					}
					else
					{
						$dovuto_cur = $totali_atto_cur[$atto_cur->Tipo_Totale_Rate];
						$diritto_cur = $totali_atto_cur['diritto_'.$atto_cur->Tipo_Totale_Rate];
					}
				}
			}
			else{
				if($numero_rate_cur==0)
				{
					$totale_sel = $atto_cur->selezioneTotaleDovuto($miaPartita->Flag_Blocco_Diritto_Riscossione);
					$dovuto_cur = $totale_sel['tot'];
					$diritto_cur = $totale_sel['diritto'];
				}
				else
				{
					$dovuto_cur = $totali_atto_cur[$atto_cur->Tipo_Totale_Rate];
					$diritto_cur = $totali_atto_cur['diritto_'.$atto_cur->Tipo_Totale_Rate];
				}
			}

			$interessi_atto_cur+= $atto_cur->Interessi;
			$ingSpeseCur = 0;
			$avvSpeseCur = 0;
			for($i=0;$i<count($miaPartita->Atto);$i++)
			{
				if($miaPartita->Atto[$i]->ID==$atto_cur->ID)
					break;

				if($miaPartita->Atto[$i]->Atto == "Ingiunzione")
					$ingSpeseCur += $miaPartita->Atto[$i]->Spese_Notifica + $miaPartita->Atto[$i]->CAN + $miaPartita->Atto[$i]->CAD + $miaPartita->Atto[$i]->Ulteriori_Spese;
				else if($miaPartita->Atto[$i]->Atto == "Avviso di intimazione ad adempiere")
					$avvSpeseCur += $miaPartita->Atto[$i]->Spese_Notifica + $miaPartita->Atto[$i]->CAN + $miaPartita->Atto[$i]->CAD + $miaPartita->Atto[$i]->Ulteriori_Spese;
			}

			$precedenti_atto_cur = $ingSpeseCur + $spese_originale - $ricercaTot;
			$notifica_CAN_CAD_atto_cur = $avvSpeseCur + $atto_cur->Spese_Notifica + $atto_cur->CAN + $atto_cur->CAD + $atto_cur->Ulteriori_Spese;
			$notifica_atto_cur = 0;

			if($atto_cur->Atto == "Ingiunzione")
				$precedenti_atto_cur+= $notifica_CAN_CAD_atto_cur;
			else if($atto_cur->Atto == "Avviso di intimazione ad adempiere")
				$notifica_atto_cur = $notifica_CAN_CAD_atto_cur;
			else
				alert("OPS! Atto senza tipo!");

			$scorpori_pag_cur = $atto_cur->totale_scorpori();
			if($scorpori_pag_cur['somma']>0){
				$interesse_pagato+= $scorpori_pag_cur['interessi'];
				$ricerca_pagato+= $scorpori_pag_cur['spese_ricerca'];
				$precedenti_pagato+= $scorpori_pag_cur['spese_precedenti'];
				$tributo_pagato+= $scorpori_pag_cur['tributo'];
				$notifica_pagato+= $scorpori_pag_cur['spese_notifica'];
				$diritto_pagato+= $scorpori_pag_cur['diritto_riscossione'];
			}
			else{

			//SOMMA PAGAMENTI ATTO
			$somma_pag_cur = $atto_cur->totale_pagamenti();

			if(isset($atto_cur->Pagamento[0])){
				if($atto_cur->Pagamento[0]->Tipo_Pagamento=="T290"){
					//INTERESSE PAGATO
					$interesse_pagato_cur = round(($interessi_atto_cur - $interesse_pagato) / $numero_rate_cur,2)*count($atto_cur->Pagamento);
				}
				else {
					//INTERESSE PAGATO
					$interesse_pagato_cur = ($interessi_atto_cur - $interesse_pagato) * $somma_pag_cur / $dovuto_cur;
					if($interesse_pagato_cur>0)
						$interesse_pagato_cur = number_format($interesse_pagato_cur,2);
				}
			}
			else{
				//INTERESSE PAGATO
				$interesse_pagato_cur = ($interessi_atto_cur - $interesse_pagato) * $somma_pag_cur / $dovuto_cur;
				if($interesse_pagato_cur>0)
					$interesse_pagato_cur = number_format($interesse_pagato_cur,2);
			}


			$interesse_pagato += $interesse_pagato_cur;
			$pagato_scalato = $somma_pag_cur - $interesse_pagato_cur;

			if($pagato_scalato <= 0)
				continue;

			//SPESE RICERCA PAGATE
			if($ricerca_pagato < $ricercaTot)
			{
				if($pagato_scalato > ($ricercaTot - $ricerca_pagato))
				{
					$pagato_scalato -= ($ricercaTot - $ricerca_pagato);
					$ricerca_pagato += $ricercaTot - $ricerca_pagato;
				}
				else
				{
					$ricerca_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//SPESE PRECEDENTI PAGATE
			if($precedenti_pagato < $precedenti_atto_cur)
			{
				if($pagato_scalato > ($precedenti_atto_cur - $precedenti_pagato))
				{
					$pagato_scalato -= ($precedenti_atto_cur - $precedenti_pagato);
					$precedenti_pagato += $precedenti_atto_cur - $precedenti_pagato;
				}
				else
				{
					$precedenti_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//IMPORTO PAGATO ( IMPORTO ORIGINALE + MAGGIORAZIONI O SANZIONI DELL'IMPORTO )
			if($tributo_pagato < $importo_originale)
			{
				if($pagato_scalato > ($importo_originale - $tributo_pagato))
				{
					$pagato_scalato -= ($importo_originale - $tributo_pagato);
					$tributo_pagato += $importo_originale - $tributo_pagato;
				}
				else
				{
					$tributo_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//SPESE DI NOTIFICA ATTO PAGATE ( COMPRENSIVE DI CAN CAD E ULTERIORI SPESE )
			if($notifica_pagato < $notifica_atto_cur)
			{
				if($pagato_scalato > ($notifica_atto_cur - $notifica_pagato))
				{
					$pagato_scalato -= ($notifica_atto_cur - $notifica_pagato);
					$notifica_pagato += $notifica_atto_cur - $notifica_pagato;
				}
				else
				{
					$notifica_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//DIRITTO DI RISCOSSIONE PAGATO
			if($diritto_pagato < $diritto_cur)
			{
				if($pagato_scalato > ($diritto_cur - $diritto_pagato))
				{
					$pagato_scalato -= ($diritto_cur - $diritto_pagato);
					$diritto_pagato += $diritto_cur - $diritto_pagato;
				}
				else
				{
					$diritto_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//RIMANENZA PAGATA
			if($pagato_scalato > 0)
				$rimanenza_pagato += $pagato_scalato;

			}
		}

		$notifica_pigno_cur = 0;
		$spese_acc_pagato = 0;
		$notifica_pigno_pagato = 0;
		//CICLO PIGNORAMENTI PRECEDENTI
		for($pg=0;$pg<count($miaPartita->Pignoramento);$pg++)
		{
			if(strpos($this->Tipo_Atto,'Pignoramento')===false)
				break;

			$pigno_cur = $miaPartita->Pignoramento[$pg];

			if(strpos($this->Tipo_Atto,'Pignoramento')!==false)
				if($pigno_cur->ID == $mioPigno->ID)
					break;

			$atto_cur = new atto($pigno_cur->Atto_ID, $this->CC);
			$numero_rate_atto_cur = $atto_cur->Rate_Previste;
			if($numero_rate_atto_cur==0)
			{
				$totale_sel = $atto_cur->selezioneTotaleDovuto($miaPartita->Flag_Blocco_Diritto_Riscossione);
				$diritto_cur = $totale_sel['diritto'];
			}
			else
			{
				$totali_atto_cur = $atto_cur->gestione_totali($miaPartita->Flag_Blocco_Diritto_Riscossione);
				$diritto_cur = $totali_atto_cur['diritto_'.$atto_cur->Tipo_Totale_Rate];
			}

			$interessi_atto_cur+= $atto_cur->Interessi;
			$ingSpeseCur = 0;
			$avvSpeseCur = 0;
			for($i=0;$i<count($miaPartita->Atto);$i++)
			{
				if($miaPartita->Atto[$i]->ID==$atto_cur->ID)
					break;

				if($miaPartita->Atto[$i]->Atto == "Ingiunzione")
					$ingSpeseCur += $miaPartita->Atto[$i]->Spese_Notifica + $miaPartita->Atto[$i]->CAN + $miaPartita->Atto[$i]->CAD + $miaPartita->Atto[$i]->Ulteriori_Spese;
				else if($miaPartita->Atto[$i]->Atto == "Avviso di intimazione ad adempiere")
					$avvSpeseCur += $miaPartita->Atto[$i]->Spese_Notifica + $miaPartita->Atto[$i]->CAN + $miaPartita->Atto[$i]->CAD + $miaPartita->Atto[$i]->Ulteriori_Spese;
			}

			$precedenti_atto_cur = $ingSpeseCur + $spese_originale - $ricercaTot;
			$notifica_CAN_CAD_atto_cur = $avvSpeseCur + $atto_cur->Spese_Notifica + $atto_cur->CAN + $atto_cur->CAD + $atto_cur->Ulteriori_Spese;

			if($atto_cur->Atto == "Ingiunzione")
				$precedenti_atto_cur+= $notifica_CAN_CAD_atto_cur;
			else if($atto_cur->Atto == "Avviso di intimazione ad adempiere")
				$notifica_atto_cur = $notifica_CAN_CAD_atto_cur;

			$pigno_cur->gestione_totali();
			$numero_rate_cur = $pigno_cur->Rate_Previste;
			if($numero_rate_cur!=0)
			{
				$dovuto_cur = $pigno_cur->Totali_Array[$pigno_cur->Tipo_Totale_Rate];
				$spese_acc_cur = $pigno_cur->Parziali_Spese_Accessorie[$pigno_cur->Tipo_Totale_Rate];
			}
			else
			{
				$pagamento_pigno = $pigno_cur->Pagamento[0]->Importo;

				if($pagamento_pigno == conv_num($pigno_cur->Totali_Array[3]))
				{
					$dovuto_cur = $pigno_cur->Totali_Array[3];
					$spese_acc_cur = $pigno_cur->Parziali_Spese_Accessorie[3];
				}
				else if($pagamento_pigno == conv_num($pigno_cur->Totali_Array[2]))
				{
					$dovuto_cur = $pigno_cur->Totali_Array[2];
					$spese_acc_cur = $pigno_cur->Parziali_Spese_Accessorie[2];
				}
				else
				{
					$dovuto_cur = $pigno_cur->Totali_Array[1];
					$spese_acc_cur = $pigno_cur->Parziali_Spese_Accessorie[1];
				}
			}

			$notifica_pigno_cur = $speseNotPigno;

			$scorpori_pag_cur = $pigno_cur->totale_scorpori();
			if($scorpori_pag_cur['somma']>0){
				$interesse_pagato+= $scorpori_pag_cur['interessi'];
				$ricerca_pagato+= $scorpori_pag_cur['spese_ricerca'];
				$precedenti_pagato+= $scorpori_pag_cur['spese_precedenti'];
				$tributo_pagato+= $scorpori_pag_cur['tributo'];
				$notifica_pagato+= $scorpori_pag_cur['spese_notifica'];
				$diritto_pagato+= $scorpori_pag_cur['diritto_riscossione'];
				$notifica_pigno_pagato+= $scorpori_pag_cur['notifica_pignoramento'];
				$spese_acc_pagato+= $scorpori_pag_cur['spese_accessorie'];
			}
			else{

			//TOTALE PAGAMENTI PIGNORAMENTO
			$somma_pag_cur = $pigno_cur->totale_pagamenti();

			//INTERESSE PAGATO
			$interesse_pagato_cur = ($interessi_atto_cur - $interesse_pagato) * $somma_pag_cur / $dovuto_cur;
			if($interesse_pagato_cur>0)
				$interesse_pagato_cur = number_format($interesse_pagato_cur,2);

			$interesse_pagato += $interesse_pagato_cur;
			$pagato_scalato = $somma_pag_cur - $interesse_pagato_cur;

			if($pagato_scalato <= 0)
				continue;

			//SPESE ACCESSORIE PAGATE
			$spese_acc_pagato_cur = ($spese_acc_cur - $spese_acc_pagato) * $somma_pag_cur / $dovuto_cur;
			if($spese_acc_pagato_cur>0)
				$spese_acc_pagato_cur = number_format($spese_acc_pagato_cur,2);

			$spese_acc_pagato += $spese_acc_pagato_cur;
			$pagato_scalato -= $spese_acc_pagato_cur;

			if($pagato_scalato <= 0)
				continue;

			//SPESE RICERCA PAGATE
			if($ricerca_pagato < $ricercaTot)
			{
				if($pagato_scalato > ($ricercaTot - $ricerca_pagato))
				{
					$pagato_scalato -= ($ricercaTot - $ricerca_pagato);
					$ricerca_pagato += $ricercaTot - $ricerca_pagato;
				}
				else
				{
					$ricerca_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//SPESE PRECEDENTI PAGATE
			if($precedenti_pagato < $precedenti_atto_cur)
			{
				if($pagato_scalato > ($precedenti_atto_cur - $precedenti_pagato))
				{
					$pagato_scalato -= ($precedenti_atto_cur - $precedenti_pagato);
					$precedenti_pagato += $precedenti_atto_cur - $precedenti_pagato;
				}
				else
				{
					$precedenti_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//IMPORTO PAGATO ( IMPORTO ORIGINALE + MAGGIORAZIONI O SANZIONI DELL'IMPORTO )
			if($tributo_pagato < $importo_originale)
			{
				if($pagato_scalato > ($importo_originale - $tributo_pagato))
				{
					$pagato_scalato -= ($importo_originale - $tributo_pagato);
					$tributo_pagato += $importo_originale - $tributo_pagato;
				}
				else
				{
					$tributo_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//SPESE DI NOTIFICA ATTI PRECEDENTI PAGATE ( COMPRENSIVE DI CAN CAD E ULTERIORI SPESE )
			if($notifica_pagato < $notifica_atto_cur)
			{
				if($pagato_scalato > ($notifica_atto_cur - $notifica_pagato))
				{
					$pagato_scalato -= ($notifica_atto_cur - $notifica_pagato);
					$notifica_pagato += $notifica_atto_cur - $notifica_pagato;
				}
				else
				{
					$notifica_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//SPESE DI NOTIFICA PIGNORAMENTO
			if($notifica_pigno_pagato < $notifica_pigno_cur)
			{
				if($pagato_scalato > ($notifica_pigno_cur - $notifica_pigno_pagato))
				{
					$pagato_scalato -= ($notifica_pigno_cur - $notifica_pigno_pagato);
					$notifica_pigno_pagato += $notifica_pigno_cur - $notifica_pigno_pagato;
				}
				else
				{
					$notifica_pigno_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//DIRITTO DI RISCOSSIONE PAGATO
			if($diritto_pagato < $diritto_cur)
			{
				if($pagato_scalato > ($diritto_cur - $diritto_pagato))
				{
					$pagato_scalato -= ($diritto_cur - $diritto_pagato);
					$diritto_pagato += $diritto_cur - $diritto_pagato;
				}
				else
				{
					$diritto_pagato += $pagato_scalato;
					$pagato_scalato = 0;
					continue;
				}
			}

			//RIMANENZA PAGATA
			if($pagato_scalato > 0)
				$rimanenza_pagato += $pagato_scalato;

			}
		}


		//SELEZIONO RATE RIFERITE ALL'ATTO/PIGNORAMENTO
		$queryRatePrec = "SELECT * FROM pagamento ";
		$queryRatePrec .= "WHERE Atto_ID = " . $this->Atto_ID . " AND ";
		$queryRatePrec .= "Partita_ID = " . $this->Partita_ID . " AND ";
		$queryRatePrec .= "Rata <= " . $this->Rata." ";
		$queryRatePrec .= "ORDER BY Rata ASC, Data_Pagamento ASC";
		$resRatePrec = mysql_query($queryRatePrec);

		$scorpImporto = $this->Importo;

		$scorpInteressi = 0;
		$scorpSpesePrec = 0;
		$scorpNotifica = 0;
		$scorpRicerca = 0;  //  nella coattiva NON ci sono spese di ricerca
		$scorpTributo = 0;
		$scorpNotificaPigno = 0;
		$scorpSpAccPigno = 0;
		$scorpRimanenza = 0;

		$cur_tributo = 0;
		$cur_interesse = 0;
		$cur_spese_precedenti = 0;
		$cur_spese_notifica = 0;
		$cur_spese_ricerca = 0;
		$cur_not_pigno = 0;
		$cur_sp_accessoria = 0;
		$cur_rimanenza = 0;

		$interessiGiaPagati = $interesse_pagato;
		$speseAccGiaPagate = $spese_acc_pagato;
		$somma_tributo_originale = $tributo_pagato;
		$somma_sp_ricerca = $ricerca_pagato;
		$somma_sp_precedenti = $precedenti_pagato + $notifica_pagato;
		$somma_sp_notifica = 0;
		$somma_notifica_pigno = $notifica_pigno_pagato;
		$somma_rimanenza = $rimanenza_pagato;
		$somma_diritto = $diritto_pagato;

		if($somma_sp_precedenti<=$precedentiTot-$spese_sollecito)
			$somma_sp_precedenti+=$spese_sollecito;
		else
			$somma_sp_notifica+=$spese_sollecito;

		$testo_totali_pagati = "SPESE PRECEDENTI: ".$somma_sp_precedenti." "."SPESE RICERCA: ".$somma_sp_ricerca." "."SPESE AVVISO: ".$somma_sp_notifica." ";
		$testo_totali_pagati.= "MAGGIORAZIONE: ".$interessiGiaPagati." "."SPESE ACCESSORIE: ".$speseAccGiaPagate." "."NOTIFICA PIGNO: ".$somma_notifica_pigno." ";

// 		alert($testo_totali_pagati);

		$somma_rate = 0;
		if($resRatePrec!==false)
		{
			$interessiTot += $mioAtto->Interessi;
			$control_ciclo_rate = 0;
			//CICLO DELLE RATE
			while ($rigaRataPrec = mysql_fetch_assoc($resRatePrec))
			{
				$somma_scorpori = $rigaRataPrec['Scorporo_Tributo'] + $rigaRataPrec['Scorporo_Spese_Ricerca'] + $rigaRataPrec['Scorporo_Spese_Precedenti'];
				$somma_scorpori+= $rigaRataPrec['Scorporo_Spese_Notifica'] + $rigaRataPrec['Scorporo_Tributo_Provinciale'];
				$somma_scorpori+= $rigaRataPrec['Scorporo_Interessi'] + $rigaRataPrec['Scorporo_Diritto_Riscossione'] + $rigaRataPrec['Scorporo_Eca'];
				$somma_scorpori+= $rigaRataPrec['Scorporo_Spese_Accessorie'] + $rigaRataPrec['Scorporo_Notifica_Pignoramento'];

				if($somma_scorpori>0){
					$cur_interesse = $rigaRataPrec['Scorporo_Interessi'];
					$cur_sp_accessoria = $rigaRataPrec['Scorporo_Spese_Accessorie'];
					$cur_tributo = $rigaRataPrec['Scorporo_Tributo'];
					$cur_spese_ricerca = $rigaRataPrec['Scorporo_Spese_Ricerca'];
					$cur_spese_precedenti = $rigaRataPrec['Scorporo_Spese_Precedenti'];
					$cur_spese_notifica = $rigaRataPrec['Scorporo_Spese_Notifica'];
					$cur_not_pigno = $rigaRataPrec['Scorporo_Notifica_Pignoramento'];
					$cur_rimanenza = 0;
					$cur_diritto = $rigaRataPrec['Scorporo_Diritto_Riscossione'];
				}
				else{

				$importoRata = $rigaRataPrec['Importo'];
				$somma_rate += $importoRata;

				$num_rata_singola = $rigaRataPrec['Rata'];
				$importo_scalato_rata = $importoRata;

				//CALCOLO INTERESSE RATA IN PROPORZIONE ALL'IMPORTO
				if($interessiTot>0)
				{
					if($somma_rate < $totale_dovuto){
						$questointeresse = number_format($interessiTot * $importoRata / $totale_dovuto, 2);
						if($interessiGiaPagati + $questointeresse > $interessiTot)
							$questointeresse = $interessiTot - $interessiGiaPagati;
					}
					else //SE ULTIMA RATA METTO RIMANENZA INTERESSI DA PAGARE
						$questointeresse = $interessiTot - $interessiGiaPagati;
				}
				else
					$questointeresse = 0;

				//CALCOLO SPESA ACCESSORIA IN PROPORZIONE ALL'IMPORTO
				if($speseAccessorie>0)
				{
					if($somma_rate < $totale_dovuto){
						$questaspesaaccessoria = number_format($speseAccessorie * $importoRata / $totale_dovuto, 2);
						if($speseAccGiaPagate + $questaspesaaccessoria > $speseAccessorie)
							$questaspesaaccessoria = $speseAccessorie - $speseAccGiaPagate;
					}
					else //SE ULTIMA RATA METTO RIMANENZA SPESE ACCESSORIE DA PAGARE
						$questaspesaaccessoria = $speseAccessorie - $speseAccGiaPagate;
				}
				else
					$questaspesaaccessoria = 0;

				//INTERESSE
				$cur_interesse = $questointeresse;
				if($cur_interesse > 0 && $importo_scalato_rata > 0)
				{
					if($importo_scalato_rata < $cur_interesse)
						$cur_interesse = $importo_scalato_rata;

					$importo_scalato_rata -= $cur_interesse;
				}

				//SPESE ACCESSORIE
				$cur_sp_accessoria = $questaspesaaccessoria;
				if($cur_sp_accessoria > 0 && $importo_scalato_rata > 0)
				{
					if($importo_scalato_rata < $cur_sp_accessoria)
						$cur_sp_accessoria = $importo_scalato_rata;

					$importo_scalato_rata -= $cur_sp_accessoria;
				}

				//SPESE RICERCA
				//CONTROLLO SU QUANTO GIA SCORPORATO
				if($somma_sp_ricerca < $ricercaTot && $importo_scalato_rata>0) //DA SCORPORARE
				{
					//CONTROLLO SE IMPORTO RIMANENTE RATA COPRE LE SPESE RIMANENTI
					if($importo_scalato_rata < ($ricercaTot - $somma_sp_ricerca))//NON COPRE COMPLETAMENTE (INSERISCO LA RIMANENZA DELLA RATA)
						$cur_spese_ricerca = $importo_scalato_rata;
					else //COPRE COMPLETAMENTE (INSERISCO TUTTE LE SPESE RIMANENTI)
						$cur_spese_ricerca = $ricercaTot - $somma_sp_ricerca;
				}
				else //RATA ESAURITA O SPESE GIA COPERTE
					$cur_spese_ricerca = 0;
				//SOTTRAGGO ALLA RATA SPESE SCORPORATE
				$importo_scalato_rata -= $cur_spese_ricerca;

				//SPESE PRECEDENTI
				if($somma_sp_precedenti < $precedentiTot && $importo_scalato_rata>0)
				{
					if($importo_scalato_rata < ($precedentiTot - $somma_sp_precedenti))
						$cur_spese_precedenti = $importo_scalato_rata;
					else
						$cur_spese_precedenti = $precedentiTot - $somma_sp_precedenti;
				}
				else
					$cur_spese_precedenti = 0;
				$importo_scalato_rata -= $cur_spese_precedenti;

				//SANZIONE ORIGINARIA
				if($somma_tributo_originale < $importo_originale && $importo_scalato_rata>0)
				{
					if($importo_scalato_rata < ($importo_originale - $somma_tributo_originale))
						$cur_tributo = $importo_scalato_rata;
					else
					$cur_tributo = $importo_originale - $somma_tributo_originale;
				}
				else
					$cur_tributo = 0;
				$importo_scalato_rata -= $cur_tributo;

				//SPESE NOTIFICA ATTO o ATTI PRECEDENTI
				if($somma_sp_notifica < $notificaTot && $importo_scalato_rata>0)
				{
					if($importo_scalato_rata < ($notificaTot - $somma_sp_notifica))
						$cur_spese_notifica = $importo_scalato_rata;
					else
						$cur_spese_notifica = $notificaTot - $somma_sp_notifica;
				}
				else
					$cur_spese_notifica = 0;
				$importo_scalato_rata -= $cur_spese_notifica;

				//SPESE NOTIFICA PIGNO
				if($somma_notifica_pigno < $speseNotPigno && $importo_scalato_rata>0)
				{
					if($importo_scalato_rata < ($speseNotPigno - $somma_notifica_pigno))
						$cur_not_pigno = $importo_scalato_rata;
					else
						$cur_not_pigno = $speseNotPigno - $somma_notifica_pigno;
				}
				else
					$cur_not_pigno = 0;
				$importo_scalato_rata -= $cur_not_pigno;

				//DIRITTO RISCOSSIONE
				if($somma_diritto < $diritto && $importo_scalato_rata>0)
				{
					if($importo_scalato_rata < ($diritto - $somma_diritto))
						$cur_diritto = $importo_scalato_rata;
					else
						$cur_diritto = $diritto - $somma_diritto;
				}
				else
					$cur_diritto = 0;
				$importo_scalato_rata -= $cur_diritto;

				//RIMANENZA
				if($importo_scalato_rata>0)
					$cur_rimanenza = $importo_scalato_rata;
				else
					$cur_rimanenza = 0;

				}

				//SOMME INTERESSI E SP ACCESSORIE
				$interessiGiaPagati += $cur_interesse;
				$speseAccGiaPagate += $cur_sp_accessoria;

				//SOMME ALTRE VOCI SCORPORO
				$somma_tributo_originale += $cur_tributo;
				$somma_sp_ricerca += $cur_spese_ricerca;
				$somma_sp_precedenti += $cur_spese_precedenti;
				$somma_sp_notifica += $cur_spese_notifica;
				$somma_notifica_pigno += $cur_not_pigno;
				$somma_rimanenza += $cur_rimanenza;
				$somma_diritto += $cur_diritto;

			}

		}

		$scorpTributo = $cur_tributo;
		$scorpInteressi = $cur_interesse;
		$scorpSpesePrec = $cur_spese_precedenti;
		$scorpNotifica = $cur_spese_notifica;
		$scorpRicerca = $cur_spese_ricerca;
		$scorpNotificaPigno = $cur_not_pigno;
		$scorpSpAccPigno = $cur_sp_accessoria;
		$scorpRimanenza = 0;
		$scorpDiritto = $cur_diritto;

// 		if(strpos($this->Tipo_Atto,'Pignoramento')!==false)
// 			$scorpSpAccPigno+=$cur_rimanenza;
// 		else
			$scorpTributo+=$cur_rimanenza;

		$scorp_mio_pag = $this->scorpori();
		if($scorp_mio_pag['somma']==0){
			$this->Scorporo_Tributo = $scorpTributo;
			$this->Scorporo_Interessi = $scorpInteressi;
			$this->Scorporo_Spese_Precedenti = $scorpSpesePrec;
			$this->Scorporo_Spese_Notifica = $scorpNotifica;
			$this->Scorporo_Spese_Ricerca = $scorpRicerca;
			$this->Scorporo_Notifica_Pignoramento = $scorpNotificaPigno;
			$this->Scorporo_Spese_Accessorie = $scorpSpAccPigno;
			$this->Scorporo_Diritto_Riscossione = $scorpDiritto;

			$this->Update($this->ID);
		}

		$arrayScorporo = array
		(
				$totale_dovuto,				//  0
				$scorpImporto,  			//  1
				$scorpInteressi,  			//  2
				$scorpSpesePrec,  			//  3
				$scorpNotifica,  			//  4
				$scorpRicerca,  			//  5
				$scorpTributo, 				//  6
				$scorpSpAccPigno,			//	7
				$scorpNotificaPigno,		//	8
				$scorpRimanenza,			//	9
				$scorpDiritto				//	10
		);

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
				if ($fields_to_update[$i] != "Comune_ID")  //  se � UPDATE, il Comune_ID non va modificato
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		if ($clause == "") return 2;  // non updata nulla, perch� sono tutti uguali

		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "

		$query = "UPDATE pagamento SET $clause WHERE ID = '" . $key . "'";

		if (mysql_query($query) != NULL) return 3;
		else return 4;

		echo "<br>" . $query;

		return 3;
	}
}

class ufficio_giudiziario//qui
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

	public function __construct( $c , $tipo = null, $tipo_CC = "comune" )
	{

		$query = "SELECT * FROM ufficio_giudiziario WHERE ";

		if($tipo_CC=="comune")
			$query.= "CC = '".$c."' ";
		else if($tipo_CC=="ufficio")
			$query.= "CC_Ufficio = '".$c."' ";

		if($tipo!=null)
			$query.= "AND Tipo = '".$tipo."' ";

		$query.= "LIMIT 1";

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

			if(isset($this->Denominazione))
				$indirizzo['Destinatario'] = $this->Denominazione;
			else
				$indirizzo['Destinatario'] = "";

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

	public function tipo_tributo($codice_tributo)
	{
		$query = "SELECT Tipo_Codice FROM codice_tributo WHERE Codice_Tributo = '".$codice_tributo."'";
		$result = single_query($query);

		return $result;
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
	public $Data_Riscontro;
	public $Mezzo_Riscontro;
	public $Tipo_Riscontro;
	public $Testo_Riscontro;
	public $Link_Riscontro;
	public $Note_Riscontro;
	public $Importo_Riscontro;
	public $Data_Deposito;
	public $Stato_Deposito;
	public $Data_Vendita;
	public $Stato_Vendita;
	public $Prezzo_Vendita;
	public $Numero_Rate;
	public $Data_Inizio_Rate;
	public $Periodicita_Rate;
	public $Differenza_Importo;

	public $Data_Elaborazione;
	public $Data_Stampa;
	public $Stato_Stampa;

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

		$this->Data_Riscontro = utf8_decode($val['Data_Riscontro']);
		$this->Mezzo_Riscontro = utf8_decode($val['Mezzo_Riscontro']);
		$this->Tipo_Riscontro = utf8_decode($val['Tipo_Riscontro']);
		$this->Testo_Riscontro = utf8_decode($val['Testo_Riscontro']);
		$this->Link_Riscontro = utf8_decode($val['Link_Riscontro']);
		$this->Note_Riscontro = utf8_decode($val['Note_Riscontro']);
		$this->Importo_Riscontro = utf8_decode($val['Importo_Riscontro']);

		$this->Data_Deposito = utf8_decode($val['Data_Deposito']);
		$this->Stato_Deposito = utf8_decode($val['Stato_Deposito']);
		$this->Data_Vendita = utf8_decode($val['Data_Vendita']);
		$this->Stato_Vendita = utf8_decode($val['Stato_Vendita']);
		$this->Prezzo_Vendita = utf8_decode($val['Prezzo_Vendita']);

		$this->Numero_Rate = utf8_decode($val['Numero_Rate']);
		$this->Data_Inizio_Rate = utf8_decode($val['Data_Inizio_Rate']);
		$this->Periodicita_Rate = utf8_decode($val['Periodicita_Rate']);
		$this->Differenza_Importo = utf8_decode($val['Differenza_Importo']);

		$this->Data_Stampa = utf8_decode($val['Data_Stampa']);
		$this->Data_Elaborazione = utf8_decode($val['Data_Elaborazione']);
		$this->Stato_Stampa = utf8_decode($val['Stato_Stampa']);

		$array_email = select_mysql_array("ID", "email_inviate", "CC='".$c."' AND Table_Collegata ='notifica_atto' AND ID_Collegato = $this->ID");
		if(count($array_email)>=1)
		{
			include_once CLASSI . "/classe_email.php";
			$this->Email_Object = new email_inviate($array_email[0]['ID']);
		}

	}

	public function invio_notifica_PEC($notifica_ID, $c, $array_dati_PEC, $par_email, $partita_ID, $utente_ID, $pigno_ID )
	{
	    include_once CLASSI . "/classe_email.php";
		require_once EMAIL  . "/PHPMailerAutoload.php";
		require_once CLASSI . "\php-imap-client-master\Imap.php";

		require_once FPDI . "\FPDI_Protection.php";

		$array_PEC['pigno_ID'] = "";
		$array_PEC['contatore'] = 0;
		if(isset($array_dati_PEC['password_banca']))
			$password_pdf = $array_dati_PEC['password_banca'];
		else
			$password_pdf = null;

		if($this->Modalita_Stampa!="pec")
		{
			return $array_PEC;
		}

		if($array_dati_PEC['pec']!="")
		{
			$mail_destinatario = $array_dati_PEC['pec'];
			$tipo_destinatario = "PEC";
			$ricevuta_consegna = "attesa";
		}
		else if($array_dati_PEC['mail']!="")
		{
			$mail_destinatario = $array_dati_PEC['mail'];
			$tipo_destinatario = "email";
			$ricevuta_consegna = "no";
		}
		else
		{
            echo "<br><br>errore array_dati_PEC<br><br>";
			return $array_PEC;
		}

		$query_mail = "SELECT ID FROM email_inviate WHERE CC = '".$c."' AND Partita_ID = '".$partita_ID."' ";
		$query_mail.= " AND Utente_ID = '".$utente_ID."' AND Table_Collegata = 'notifica_atto' ";
		$query_mail.= " AND ID_Collegato = '".$notifica_ID."'";

		$id_email = single_query($query_mail);

		if($id_email!=null)
		{
			$array_PEC['pigno_ID'] = $pigno_ID;
			$array_PEC['contatore']++;

			return $array_PEC;
		}

		if(is_file($array_dati_PEC['file'])===true || is_file($array_dati_PEC['file_old'])===true)
		{
			$subject = $array_dati_PEC['identificativo']."_".$array_dati_PEC['tipo_destinatario'];

			$body="";
			if(isset($array_dati_PEC['body']))
				$body = $array_dati_PEC['body'];

			if($body=="" || $body==null)
				$body = "Invio copia di Pignoramento. Vedi allegato.";


			set_time_limit(200);

			$mail = new PHPMailer();
			$mail->creaMailCompleta($par_email, $subject."_NOT".$notifica_ID, $body);
//            $mail->SMTPDebug = 2;
			$mail->addAddress($mail_destinatario, $array_dati_PEC['denominazione']);

			if(is_file($array_dati_PEC['file']))
			{
				if($password_pdf!=null)
				{
					$pdf_temp = new FPDI_Protection();
					$pdf_temp->pdfEncrypt($array_dati_PEC['file'], $password_pdf, "protect_pdf.pdf");
                    if(is_file("protect_pdf.pdf"))
						$mail->addAttachment("protect_pdf.pdf");


				}
				else
					$mail->addAttachment($array_dati_PEC['file']);
			}
			else if(is_file($array_dati_PEC['file_old']))
			{

				if($password_pdf!=null)
				{
					$pdf_temp = new FPDI_Protection();
					$pdf_temp->pdfEncrypt($array_dati_PEC['file_old'], $password_pdf, "protect_pdf.pdf");
					if(is_file("protect_pdf.pdf"))
						$mail->addAttachment("protect_pdf.pdf");
				}
				else
					$mail->addAttachment($array_dati_PEC['file_old']);
			}

			if(is_file($array_dati_PEC['file_relata']))
			{
				if($password_pdf!=null)
				{
					$pdf_temp = new FPDI_Protection();
					$pdf_temp->pdfEncrypt($array_dati_PEC['file_relata'], $password_pdf, "protect_relata_pdf.pdf");

					if(is_file("protect_relata_pdf.pdf"))
						$mail->addAttachment("protect_relata_pdf.pdf");
				}
				else
					$mail->addAttachment($array_dati_PEC['file_relata']);
			}

			if($mail->send())
			{
				$salva_email = new email_inviate(null);

				$salva_email->CC = $c;
				$salva_email->Partita_ID = $partita_ID;
				$salva_email->Utente_ID = $utente_ID;
				$salva_email->Oggetto = $subject."_NOT".$notifica_ID;
				$salva_email->Mail_Sorgente = $par_email->Indirizzo_Email;
				$salva_email->Tipo_Sorgente = "PEC";
				$salva_email->Mail_Destinatario = $mail_destinatario;
				$salva_email->Tipo_Destinatario = $tipo_destinatario;
				$salva_email->Data_Invio = date('Y-m-d');

				$salva_email->Ricevuta_Accettazione = "attesa";
				$salva_email->Ricevuta_Consegna = $ricevuta_consegna;

				$salva_email->Table_Collegata = "notifica_atto";
				$salva_email->ID_Collegato = $notifica_ID;

				mysql_query('BEGIN');

				$control_salva = $salva_email->Insert();

				if( $control_salva )
				{
					mysql_query('COMMIT');
					$path_mail = crea_dir($salva_email->percorsoMail($c, "PEC", $subject, 'server'));

					$myfile = fopen($path_mail."/".$subject."_NOT".$notifica_ID.'.eml', 'w');
					$testo = $mail->getMessaggio();
					fwrite($myfile, $testo);

					fclose($myfile);

					$array_PEC['pigno_ID'] = $pigno_ID;
					$array_PEC['contatore']++;
				}
				else
				{
					mysql_query('ROLLBACK');
                    echo "Errore nel salvataggio della email inviata";
					$array_PEC['pigno_ID'] = $pigno_ID;
					$array_PEC['contatore']++;
				}
			}
			else
			{
				echo "PIGNORAMENTO ID ".$pigno_ID.": ".$mail->ErrorInfo;
// 				alert('Errore invio della email! '.$pigno_ID);
				$array_PEC['pigno_ID'] = $pigno_ID;
				$array_PEC['contatore']++;
			}



		}
		else
		{
			echo 'File da inviare via PEC non trovato! Spedizione non riuscita.';
			$array_PEC['pigno_ID'] = $pigno_ID;
		}

		if(is_file("protect_pdf.pdf"))
			unlink("protect_pdf.pdf");
		if(is_file("protect_relata_pdf.pdf"))
			unlink("protect_relata_pdf.pdf");

		return $array_PEC;

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
		$num_digits = strlen($quintoCampo);
		for($i=16;$i>$num_digits;$i--){
			$quintoCampo = '0'.$quintoCampo;
		}

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
			case "02": $tipoServizio = "Ingiunzione"; break;
			case "03": $tipoServizio = "Sollecito di pagamento"; break;
			case "04": $tipoServizio = "Avviso di intimazione ad adempiere"; break;
			case "05": $tipoServizio = "Sollecito avviso di intimazione"; break;
			case "06": $tipoServizio = "Pignoramento beni mobili registrati"; break;
			case "07": $tipoServizio = "Pignoramento presso datore di lavoro"; break;
			case "08": $tipoServizio = "Pignoramento presso banca"; break;

            case "12": $tipoServizio = "Avviso di messa in mora"; break;
            case "11": $tipoServizio = "Sollecito pre ingiunzione"; break;
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
}

?>
