<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

//*****************************************************************
// Classe utente; 		                                      	  *
// Crea un oggetto utente con i dati relativi                     *
//*****************************************************************
class utente
{
	public $ID;
	public $CC_Comune;
	public $Comune_ID;
	public $Genere;
	public $Cognome;
	public $Nome;
	public $CC_Nascita;
	public $Paese_Nascita;
	public $Comune_Nascita;
	public $Provincia_Nascita;
	public $Data_Nascita;
	public $Data_Morte;
	public $Codice_Fiscale;
	public $Ditta;
	public $Forma_Giuridica;
	public $Partita_Iva;
	public $Azienda;
	public $Prec_Denom;
	public $Anno_Cambio_Denom;
	public $Note;
	public $Cellulare;
	public $Mail;
	public $PEC;
	public $Data_Registrazione;

	public $Residenza;
	public $Domicilio;
	public $Recapito;

	public $Dettagli_Utente;

	public $prev;
	public $next;
	public $prev_alfa;
	public $next_alfa;
	public $prev_ruolo;
	public $next_ruolo;

	public $Forma_Giuridica_Oggetto;
	public $Sigla_Forma_Giuridica;

	public function __construct( $progr = 0 , $c , $a=0 )
	{
		//if ($progr == NULL) return;

		$query = "SELECT * FROM utente WHERE ID = '".$progr."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC_Comune = utf8_decode($val['CC_Comune']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->Genere = utf8_decode($val['Genere']);
		$this->Cognome = utf8_decode($val['Cognome']);
		$this->Nome = utf8_decode($val['Nome']);
		$this->CC_Nascita = utf8_decode($val['CC_Nascita']);
		$this->Paese_Nascita = utf8_decode($val['Paese_Nascita']);
		$this->Comune_Nascita = utf8_decode($val['Comune_Nascita']);
		$this->Provincia_Nascita = utf8_decode($val['Provincia_Nascita']);
		$this->Data_Nascita = utf8_decode($val['Data_Nascita']);
		$this->Data_Morte = utf8_decode($val['Data_Morte']);
		$this->Codice_Fiscale = utf8_decode($val['Codice_Fiscale']);
		$this->Ditta = utf8_decode($val['Ditta']);
		$this->Forma_Giuridica = utf8_decode($val['Forma_Giuridica']);

		$this->Forma_Giuridica_Oggetto = new forma_giuridica($val['Forma_Giuridica']);
		$this->Sigla_Forma_Giuridica = $this->Forma_Giuridica_Oggetto->Sigla;

		$this->Partita_Iva = utf8_decode($val['Partita_Iva']);
		$this->Azienda = utf8_decode($val['Azienda']);
		$this->Prec_Denom = utf8_decode($val['Prec_Denom']);
		$this->Anno_Cambio_Denom = utf8_decode($val['Anno_Cambio_Denom']);
		$this->Note = utf8_decode($val['Note']);
		$this->Cellulare = utf8_decode($val['Cellulare']);
		$this->Mail = utf8_decode($val['Mail']);
		$this->PEC = utf8_decode($val['PEC']);
		$this->Data_Registrazione = utf8_decode($val['Data_Registrazione']);

		$this->Residenza = new indirizzo( $progr , 'res' , $c );

		$this->Domicilio = new indirizzo( $progr , 'dom' , $c );
		if($this->Domicilio->ID==null){$this->Domicilio = null;}

		$this->Recapito = new indirizzo( $progr , 'rec' , $c );
		if($this->Recapito->ID==null) {$this->Recapito = null;}

		$this->Dettagli_Utente = new dettagli_utente( $progr );
		if($this->Dettagli_Utente->ID==null) {$this->Dettagli_Utente = null;}

		// assegna un valore ai puntatori $prev e $next:
		// se progr=0 (nuovo inserimento) prev punta all'ultimo e next punta al primo
		if ($progr==0)
		{
			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" ) ";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" )";
			$query.= "ORDER BY utente_nome ASC, nome ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next_alfa = $array_result['ID'];

			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" ) ";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" )";
			$query.= "ORDER BY utente_nome DESC, nome DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev_alfa = $array_result['ID'];

			$query = "SELECT * FROM utente where CC_Comune='$c' ORDER BY ID ASC LIMIT 1";
			$this->next = single_answer_query($query);

			$query = "SELECT * FROM utente WHERE CC_Comune='$c' ORDER BY ID DESC LIMIT 1";
			$this->prev = single_answer_query($query);

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID DESC LIMIT 1";
			$this->prev_ruolo = single_answer_query($query);

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID  AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID ASC LIMIT 1";
			$this->next_ruolo = single_answer_query($query);
		}
		else
		{
			if($this->Cognome!='')
				$utente_nome = $this->Cognome;
			else if($this->Ditta!='')
				$utente_nome =$this->Ditta;
			else
				$utente_nome="";

			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" AND Cognome > \"".$utente_nome."\" )";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" AND Ditta > \"".$utente_nome."\" )";
			$query.= "ORDER BY utente_nome ASC, Nome ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next_alfa = $array_result['ID'];

			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" AND Cognome < \"".$utente_nome."\" ) ";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" AND Ditta < \"".$utente_nome."\" )";
			$query.= "ORDER BY utente_nome DESC , Nome DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev_alfa = $array_result['ID'];


			$query = "SELECT * FROM utente WHERE ( (ID>'$this->ID') AND (CC_Comune='$c') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM utente WHERE ( (ID<'$this->ID') AND (CC_Comune='$c') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID AND (u.ID<'$this->ID')  AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID DESC LIMIT 1";
			$this->prev_ruolo = single_answer_query($query);

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID AND (u.ID>'$this->ID')  AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID ASC LIMIT 1";
			$this->next_ruolo = single_answer_query($query);
		}

	}

	public function postalAddressRows(){
        if($this->Recapito!=null)
            $indirizzo = $this->Recapito;
        else if($this->Domicilio!=null)
            $indirizzo = $this->Domicilio;
        else
            $indirizzo = $this->Residenza;

        if($indirizzo->Paese=="Italia"){
            $ind_1 = $indirizzo->Toponimo->Nome;
            if($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            if($indirizzo->Civico>0)
                $ind_1.= ", ".$indirizzo->Civico;
            if($indirizzo->Esponente!="")
                $ind_1.= " ".$indirizzo->Esponente;
            if($indirizzo->Interno>0)
                $ind_1.="/".$indirizzo->Interno;
            if($indirizzo->Dettagli!="")
                $ind_1.=", ".$indirizzo->Dettagli;

            $ind_3 = "";
        }
        else{
            $ind_1 = $indirizzo->Toponimo->Nome;
            if($indirizzo->Frazione!="")
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            $ind_3 = $indirizzo->Paese;
        }

        $ind_2 = $indirizzo->Cap." ".$indirizzo->Comune;
        if($indirizzo->Provincia!=null)
            $ind_2.= " ".$indirizzo->Provincia;

        $a_rows = array();

        $a_rows[0] = strtoupper($ind_1);
        $a_rows[1] = strtoupper($ind_2);
        if($ind_3!="")
            $a_rows[1].= " ".strtoupper($ind_3);

        return $a_rows;
    }

	public function righe_indirizzo()
	{
		if($this->Recapito!=null)
			$indirizzo = $this->Recapito;
		else if($this->Domicilio!=null)
			$indirizzo = $this->Domicilio;
		else
			$indirizzo = $this->Residenza;

		if(strtoupper($indirizzo->Paese)=="ITALIA")
		{
			$ind_1 = $indirizzo->Toponimo->Nome;
			if($indirizzo->Frazione)
				$ind_1 = $indirizzo->Frazione.", ".$ind_1;

			if($indirizzo->Civico)
				$ind_1.= ", ".$indirizzo->Civico;
			if($indirizzo->Esponente)
				$ind_1.= " ".$indirizzo->Esponente;
			if($indirizzo->Interno)
				$ind_1.="/".$indirizzo->Interno;
			if($indirizzo->Dettagli)
				$ind_1.=", ".$indirizzo->Dettagli;

			$ind_3 = "";
		}
		else
		{
			$ind_1 = $indirizzo->Toponimo->Nome;
			if($indirizzo->Frazione)
				$ind_1 = $indirizzo->Frazione.", ".$ind_1;

			$ind_3 = $indirizzo->Paese;
		}

		$ind_2 = $indirizzo->Cap." ".$indirizzo->Comune;
		$ind_2_senza_prov = $ind_2;
		if($indirizzo->Provincia!=null)
			$ind_2.= " ".$indirizzo->Provincia;

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

		if($this->Genere == "D")
		{
			$indirizzo_destinatario['Destinatario'] = $this->Ditta;
			if($this->Sigla_Forma_Giuridica!=null)
				$indirizzo_destinatario['Destinatario'].= " ".$this->Sigla_Forma_Giuridica;
		}
		else
		{
			$indirizzo_destinatario['Destinatario'] = $this->Cognome." ".$this->Nome;
		}

		if(isset($this->Recapito))
			if($this->Recapito->ID>0)
				$indirizzo_destinatario['Destinatario'].= " C/O ".strtoupper($this->Recapito->Presso);

		if(strlen($indirizzo_destinatario['Destinatario'])>45){
            $a_destinatario = array();
            $a_destinatario[0] = substr($indirizzo_destinatario['Destinatario'], 0, strrpos(substr($indirizzo_destinatario['Destinatario'], 0, 40), ' '));
            $a_destinatario[1] = substr($indirizzo_destinatario['Destinatario'], strlen($a_destinatario[0])+1, 40);
            $indirizzo_destinatario['a_destinatario'] = $a_destinatario;
        }

		return $indirizzo_destinatario;
	}

	public function info_utente()
	{
		$righe_indirizzo = $this->righe_indirizzo();
		if($this->Genere=="D")
		{
			$informazioni['riga1'] = $this->Ditta." ".$this->Sigla_Forma_Giuridica;
			$informazioni['riga2'] = "Partita Iva: ".$this->Partita_Iva;
			$informazioni['riga3'] = "Codice INPS: ".$this->Azienda;
			$informazioni['riga4'] = "";
			$informazioni['riga5'] = "Indirizzo: ".$righe_indirizzo['Completo'];
		}
		else
		{
			$informazioni['riga1'] = $this->Cognome." ".$this->Nome;
			$informazioni['riga2'] = "Codice fiscale: ".$this->Codice_Fiscale;
			$informazioni['riga3'] = "Comune di nascita: ".$this->Comune_Nascita." (".$this->Provincia_Nascita.") ".$this->Paese_Nascita;
			$informazioni['riga4'] = "Data di nascita: ".from_mysql_date($this->Data_Nascita);
			$informazioni['riga5'] = "Indirizzo: ".$righe_indirizzo['Completo'];
		}

		return $informazioni;

	}

	public function control_omonimia($array_utente)
	{
		return check_omonimi($array_utente['SESSO'], $array_utente['CF_PI'], $array_utente['COGNOME'], $array_utente['CF_PI'], $array_utente['NOME'], $array_utente['COGNOME'], $array_utente['CC_NASCITA'], $array_utente['DATA_NASCITA'], $array_utente['CC_IMPORTAZIONE']);
	}

	public function IdUtenteFromIdComune($comune_id,$c)
	{
		$query = "SELECT ID FROM utente WHERE Comune_ID = '".$comune_id."' AND CC_Comune = '".$c."'";

		return single_answer_query($query);
	}

	public function Insert()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false )
			{
				if($key != "Residenza" && $key != "Domicilio" && $key != "Recapito" && $key != "Dettagli_Utente" && $key != "prev" && $key != "next")
				{
					if($key != "prev_alfa" && $key != "next_alfa" && $key != "prev_ruolo" && $key != "next_ruolo" && $key != "Forma_Giuridica_Oggetto" && $key != "Sigla_Forma_Giuridica")
					{
						$fields[] = $key;
						$values[] = $value;
					}
				}
			}
		}

		$query = table_insert_record_query ("utente", $fields, $values);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false )
			{
				if($key != "Residenza" && $key != "Domicilio" && $key != "Recapito" && $key != "Dettagli_Utente" && $key != "prev" && $key != "next")
				{
					if($key != "prev_alfa" && $key != "next_alfa" && $key != "prev_ruolo" && $key != "next_ruolo" && $key != "Forma_Giuridica_Oggetto" && $key != "Sigla_Forma_Giuridica")
					{
						$fields[] = $key;
						$values[] = $value;
					}
				}
			}
		}

		$query = table_update_record_query ("utente", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

}

//*****************************************************************
// Classe indirizzo; 		                                  	  *
// Crea un oggetto indirizzo con i relativi dati                  *
//*****************************************************************
class indirizzo
{

	public $ID;
	public $Tipo;
	public $Utente_ID;
	public $Via_ID;
	public $Via_Cap_ID;
	public $CC_Indirizzo;
	public $Presso;
	public $Paese;
	public $Comune;
	public $Provincia;
	public $Frazione;
	public $Civico;
	public $Esponente;
	public $Interno;
	public $Dettagli;
	public $Cap;
	public $Telefono;
	public $Fax;
	public $Data_Inizio_Residenza;

	public $Toponimo;
	public $IndirizzoCompleto;

	public function __construct( $progr_utente = 0, $tipo , $c )
	{

		$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr_utente."' AND Tipo = '".$tipo."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Utente_ID = utf8_decode($val['Utente_ID']);
		$this->Via_ID = utf8_decode($val['Via_ID']);
		$this->Via_Cap_ID = utf8_decode($val['Via_Cap_ID']);
		$this->CC_Indirizzo = utf8_decode($val['CC_Indirizzo']);
		$this->Presso = utf8_decode($val['Presso']);
		$this->Paese = utf8_decode($val['Paese']);
		$this->Comune = utf8_decode($val['Comune']);
		$this->Provincia = utf8_decode($val['Provincia']);
		$this->Frazione = utf8_decode($val['Frazione']);
		$this->Civico = utf8_decode($val['Civico']);
		$this->Esponente = utf8_decode($val['Esponente']);
		$this->Interno = utf8_decode($val['Interno']);
		$this->Dettagli = utf8_decode($val['Dettagli']);
		$this->Cap = utf8_decode($val['Cap']);
		$this->Telefono = utf8_decode($val['Telefono']);
		$this->Fax = utf8_decode($val['Fax']);
		$this->Data_Inizio_Residenza = utf8_decode($val['Data_Inizio_Residenza']);

		if($this->Via_ID!=1)
		$this->Toponimo = new toponimo( $this->Via_ID , $c );
		else if($this->Via_Cap_ID!=1)
		$this->Toponimo = new toponimo_cap( $this->Via_Cap_ID );
		else
		$this->Toponimo = null;

		$this->IndirizzoCompleto = $this->Toponimo->Nome;
		if ($this->Civico != "") $this->IndirizzoCompleto = $this->IndirizzoCompleto . " " . $this->Civico;
		if ($this->Esponente != "") $this->IndirizzoCompleto = $this->IndirizzoCompleto . " " . $this->Esponente;
		if ($this->Interno != "") $this->IndirizzoCompleto = $this->IndirizzoCompleto . " /" . $this->Interno;

	}

	public function Insert()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false )
			{
				if($key != "Toponimo" && $key != "IndirizzoCompleto")
				{
						$fields[] = $key;
						$values[] = $value;
				}
			}
		}

		$query = table_insert_record_query ("indirizzo", $fields, $values);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

}

//********************************************************************
// Classe toponimo; 		                                  	     *
// Crea un oggetto toponimo relativo a Via_ID della classe indirizzo *
//********************************************************************
class toponimo
{
	public $ID;
	public $CC_Comune;
	public $Nome;
	public $CC_Toponimo;
	public $Paese;
	public $Comune;
	public $Cap;

	public function __construct( $progr_via = 0, $c )
	{

		$query = "SELECT * FROM toponimo WHERE ID = '".$progr_via."' AND CC_Comune = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->CC_Comune = utf8_decode($val['CC_Comune']);
		$this->Nome = utf8_decode($val['Nome']);
		$this->CC_Toponimo = utf8_decode($val['CC_Toponimo']);
		$this->Paese = utf8_decode($val['Paese']);
		$this->Comune = utf8_decode($val['Comune']);
		$this->Cap = utf8_decode($val['Cap']);
	}

	public function Insert()
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

		$query = table_insert_record_query ("toponimo", $fields, $values);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

}

//********************************************************************
// Classe toponimo; 		                                  	     *
// Crea un oggetto toponimo relativo a Via_ID della classe indirizzo *
//********************************************************************
class toponimo_cap
{
	public $ID;
	public $Nome;
	public $CC_Toponimo;
	public $Paese;
	public $Comune;
	public $Cap;

	public function __construct( $progr_via = 0 )
	{

		$query = "SELECT * FROM toponimi_cappati WHERE ID = '".$progr_via."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);


		$this->Nome = utf8_decode($val['Odonimo']);
		$this->CC_Toponimo = utf8_decode($val['CC_Toponimo']);
		$this->Paese = "Italia";
		$this->Comune = utf8_decode($val['Comune']);
		$this->Cap = utf8_decode($val['Cap']);
	}
}

//***************************************************************************
// Classe dettagli_utente; 		                                  	        *
// Crea un oggetto dettagli_utente relativo a Utente_ID della classe utente *
//***************************************************************************
class dettagli_utente
{
	public $ID;
	public $Utente_ID;
	public $Esenzione_ID;
	public $Situazione_ID;
	public $Controllo_ID;
	public $Raggruppamento_ID;
	public $Sottoraggruppamento_ID;
	public $Esenzione;
	public $Situazione;
	public $Controllo;
	public $Raggruppamento;
	public $Sottoraggruppamento;
	public $Pubblicita;
	public $Osap;
	public $Trsu;
	public $Ici;


	public function __construct( $progr_utente = 0 )
	{

		$query = "SELECT * FROM dettagli_utente WHERE Utente_ID = '".$progr_utente."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = $val['ID'];
		$this->Utente_ID = $val['Utente_ID'];

		$this->Esenzione_ID = $val['Esenzione_ID'];
		if($this->Esenzione_ID == 1)
		{
			$this->Esenzione = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$this->Esenzione_ID."' AND Tipo = 'Esenz'";
			$this->Esenzione = single_answer_query($query);
		}

		$this->Situazione_ID = $val['Situazione_ID'];
		if($this->Situazione_ID == 1)
		{
			$this->Situazione = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$this->Situazione_ID."' AND Tipo = 'Situaz'";
			$this->Situazione = single_answer_query($query);
		}

		$this->Controllo_ID = $val['Controllo_ID'];
		if($this->Controllo_ID == 1)
		{
			$this->Controllo = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$this->Controllo_ID."' AND Tipo = 'Control'";
			$this->Controllo = single_answer_query($query);
		}

		$this->Raggruppamento_ID = $val['Raggruppamento_ID'];
		if($this->Raggruppamento_ID == 1)
		{
			$this->Raggruppamento = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$this->Raggruppamento_ID."' AND Tipo = 'Raggr'";
			$this->Raggruppamento = single_answer_query($query);
		}

		$this->Sottoraggruppamento_ID = $val['Sottoraggruppamento_ID'];
		if($this->Sottoraggruppamento_ID == 1)
		{
			$this->Sottoraggruppamento = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$this->Sottoraggruppamento_ID."' AND Tipo = 'Sotto_Raggr'";
			$this->Sottoraggruppamento = single_answer_query($query);
		}

		$this->Pubblicita = $val['Pubblicita'];
		$this->Osap = $val['Osap'];
		$this->Trsu = $val['Trsu'];
		$this->Ici = $val['Ici'];

	}
}

class storico_residenza
{
	public $ID = array();
	public $Utente_ID;
	public $Data_Inizio = array();
	public $Data_Fine = array();
	public $Via_ID = array();
	public $Via_Cap_ID = array();
	public $CC_Indirizzo = array();
	public $Paese = array();
	public $Comune = array();
	public $Provincia = array();
	public $Frazione = array();
	public $Civico = array();
	public $Esponente = array();
	public $Interno = array();
	public $Dettagli = array();
	public $Cap = array();
	public $Telefono = array();
	public $Fax = array();
	public $Toponimo = array();
	public $Num_Storico;

	public function __construct( $progr_utente = 0, $c )
	{

		$val = select_mysql_array( "*" , "storico_residenza" , "Utente_ID = '".$progr_utente."'" , "Data_Inizio", "Desc");
		$num = count($val);
		$this->Num_Storico = $num;

		for($i=0; $i<$num; $i++)
		{
			$this->ID[$i] = utf8_decode($val[$i]['ID']);
			$this->Utente_ID[$i] = $progr_utente;
			$this->Data_Inizio[$i] = utf8_decode($val[$i]['Data_Inizio']);
			$this->Data_Fine[$i] = utf8_decode($val[$i]['Data_Fine']);
			$this->Via_ID[$i] = utf8_decode($val[$i]['Via_ID']);
			$this->Via_Cap_ID[$i] = utf8_decode($val[$i]['Via_Cap_ID']);
			$this->CC_Indirizzo[$i] = utf8_decode($val[$i]['CC_Indirizzo']);
			$this->Paese[$i] = utf8_decode($val[$i]['Paese']);
			$this->Comune[$i] = utf8_decode($val[$i]['Comune']);
			$this->Provincia[$i] = utf8_decode($val[$i]['Provincia']);
			$this->Frazione[$i] = utf8_decode($val[$i]['Frazione']);
			$this->Civico[$i] = utf8_decode($val[$i]['Civico']);
			$this->Esponente[$i] = utf8_decode($val[$i]['Esponente']);
			$this->Interno[$i] = utf8_decode($val[$i]['Interno']);
			$this->Dettagli[$i] = utf8_decode($val[$i]['Dettagli']);
			$this->Cap[$i] = utf8_decode($val[$i]['Cap']);
			$this->Telefono[$i] = utf8_decode($val[$i]['Telefono']);
			$this->Fax[$i] = utf8_decode($val[$i]['Fax']);

			if($this->Via_ID[$i]!=1)
				$this->Toponimo[$i] = new toponimo( $this->Via_ID[$i], $c );
			else if($this->Via_Cap_ID!=1)
				$this->Toponimo[$i] = new toponimo_cap( $this->Via_Cap_ID[$i], $c );
			else
				$this->Toponimo[$i] = null;
		}
	}
}

//*****************************************************************
// Classe locker_utente;                                       	  *
// Blocca i record relativi all'utente.                           *
//*****************************************************************
class locker_utente
{
	public function lock( $progr )
	{
		$where = "ID = '".$progr."'";
		$lock = select_mysql_array('Lock_Time, Lock_User', 'utente' , $where);

		if ($lock[0]['Lock_Time']==null || $lock[0]['Lock_Time']=='0000-00-00 00:00:00')
		{
			$query = "UPDATE utente SET Lock_Time = '".date('Y-m-d H:i:s')."' WHERE ID = '".$progr."' ";
			safe_query($query);

			$query = "UPDATE utente SET Lock_User = '".$_SESSION['username']."' WHERE ID = '".$progr."' ";
			safe_query($query);

			$query = "UPDATE indirizzo SET Lock_Time = '".date('Y-m-d H:i:s')."' WHERE Utente_ID = '".$progr."' ";
			safe_query($query);

			$query = "UPDATE indirizzo SET Lock_User = '".$_SESSION['username']."' WHERE Utente_ID = '".$progr."' ";
			safe_query($query);

// 			$where = "Utente_ID = '".$progr."'";
// 			$vie_id = select_mysql_array('Via_ID', 'indirizzo', $where);
// 			$num = count($vie_id);

// 			for($i=0; $i<$num; $i++)
// 			{
// 				$query = "UPDATE toponimo SET Lock_Time = '".date('Y-m-d H:i:s')."' ";
// 				$query.= "AND Lock_User = '".$_SESSION['username']."' WHERE ID = ".$vie_id[$i]['Via_ID']."'";
// 				safe_query($query);
// 			}



			return true;
		}
		else
		{
			$locktimestamp = strtotime($lock[0]['Lock_Time']);

			$currenttimestamp = strtotime(date('Y-m-d H:i:s'));

			$diff = $currenttimestamp - $locktimestamp;

			if($diff > 300)
			{
				$query = "UPDATE utente SET Lock_Time = '".date('Y-m-d H:i:s')."' WHERE ID = '".$progr."' ";
				safe_query($query);
				$query = "UPDATE utente SET Lock_User = '".$_SESSION['username']."' WHERE ID = '".$progr."' ";
				safe_query($query);

				$query = "UPDATE indirizzo SET Lock_Time = '".date('Y-m-d H:i:s')."' WHERE Utente_ID = '".$progr."' ";
				safe_query($query);
				$query = "UPDATE indirizzo SET Lock_User = '".$_SESSION['username']."' WHERE Utente_ID = '".$progr."' ";
				safe_query($query);

// 				$where = "Utente_ID = '".$progr."'";
// 				$vie_id = select_mysql_array('Via_ID', 'indirizzo', $where);
// 				$num = count($vie_id);

// 				for($i=0; $i<$num; $i++)
// 				{
// 					$query = "UPDATE toponimo SET Lock_Time = '".date('Y-m-d H:i:s')."' ";
// 					$query.= "AND Lock_User = '".$_SESSION['username']."' WHERE ID = ".$vie_id[$i]['Via_ID']."'";
// 					safe_query($query);
// 				}

				return true;
			}
			else
			{
				if($_SESSION['username'] == $lock[0]['Lock_User'])
				{
					$query = "UPDATE utente SET Lock_Time = '".date('Y-m-d H:i:s')."' WHERE ID = '".$progr."' ";
					safe_query($query);
					$query = "UPDATE utente SET Lock_User = '".$_SESSION['username']."' WHERE ID = '".$progr."' ";
					safe_query($query);

					$query = "UPDATE indirizzo SET Lock_Time = '".date('Y-m-d H:i:s')."' WHERE Utente_ID = '".$progr."' ";
					safe_query($query);
					$query = "UPDATE indirizzo SET Lock_User = '".$_SESSION['username']."' WHERE Utente_ID = '".$progr."' ";
					safe_query($query);

// 					$where = "Utente_ID = '".$progr."'";
// 					$vie_id = select_mysql_array('Via_ID', 'indirizzo', $where);
// 					$num = count($vie_id);

// 					for($i=0; $i<$num; $i++)
// 					{
// 						$query = "UPDATE toponimo SET Lock_Time = '".date('Y-m-d H:i:s')."' ";
// 						$query.= "AND Lock_User = '".$_SESSION['username']."' WHERE ID = ".$vie_id[$i]['Via_ID']."'";
// 						safe_query($query);
// 					}

					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}

	public function unlock( $progr )
	{
		$query = "UPDATE utente SET Lock_Time = null WHERE ID = '".$progr."'";
		safe_query($query);
		$query = "UPDATE utente SET Lock_User = '' WHERE ID = '".$progr."'";
		safe_query($query);

		$query = "UPDATE indirizzo SET Lock_Time = null WHERE Utente_ID = '".$progr."'";
		safe_query($query);
		$query = "UPDATE indirizzo SET Lock_User = '' WHERE Utente_ID = '".$progr."'";
		safe_query($query);

		return true;

// 		$where = "Utente_ID = '".$progr."'";
// 		$vie_id = select_mysql_array('Via_ID', 'indirizzo', $where);
// 		$num = count($vie_id);

// 		for($i=0; $i<$num; $i++)
// 		{
// 			$query = "UPDATE toponimo SET Lock_Time = null ";
// 			$query.= "AND Lock_User = '' WHERE ID = ".$vie_id[$i]['Via_ID']."'";
// 			safe_query($query);
// 		}
	}
}

class forma_giuridica
{
	public $ID;
	public $CC;
	public $Tipo;
	public $Sigla;
	public $Descrizione;

    public $LiberoProfessionista;
	public $Individuale;
	public $Persone;
	public $Capitale;
	public $Consortile;
	public $Cooperativa;
	public $Ente;

	public $Completo;


	public function __construct( $progr = null , $c = "*****" )
	{
		if ($progr == null) return;
		$query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Sigla = utf8_decode($val['Sigla']);
		$this->Descrizione = utf8_decode($val['Descrizione']);

	}

	public function array_forma( $c = "*****" )
	{
        $this->LiberoProfessionista = select_mysql_array( "*" , "forma_giuridica_societa" , "CC = '".$c."' AND (Sigla = 'L.a.' OR Sigla='L.p.')" );
		$this->Individuale = select_mysql_array( "*" , "forma_giuridica_societa" , "CC = '".$c."' AND Tipo = 'Impresa individuale'" );
		$this->Persone = select_mysql_array( "*" , "forma_giuridica_societa" , "CC = '".$c."' AND Tipo = 'Societa\' di persone'" );
		$this->Capitale = select_mysql_array( "*" , "forma_giuridica_societa" , "CC = '".$c."' AND Tipo = 'Societa\' di capitale'" );
		$this->Consortile = select_mysql_array( "*" , "forma_giuridica_societa" , "CC = '".$c."' AND Tipo = 'Societa\' consortile'" );
		$this->Cooperativa = select_mysql_array( "*" , "forma_giuridica_societa" , "CC = '".$c."' AND Tipo = 'Societa\' cooperativa'" );
		$this->Ente = select_mysql_array( "*" , "forma_giuridica_societa" , "CC = '".$c."' AND Tipo = 'Ente'" );
	}

	public function array_completo( $c = "*****" )
	{
		$stringa = "SELECT * FROM forma_giuridica_societa WHERE CC = '".$c."'";
		$query = mysql_query($stringa);
		$results = array();

		while($line = mysql_fetch_array($query, MYSQL_ASSOC))
		{
			$results[$line['ID']] = $line;
		}

		return $results;

	}
}

?>
