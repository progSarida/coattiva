<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once LIBRERIE . "/aiuto.php";
include_once CLASSI . "/motivi_mancata_contestazione_cds.php";

class enti_esterni
{

	public $ID;
	public $Denominazione;
	public $Partita_Iva;
	public $CC;
	public $CC_Ente;
	public $Paese;
	public $Comune;
	public $Frazione;
	public $Provincia;
	public $Cap;
	public $Toponimo;
	public $Civico;
	public $Esponente;
	public $Interno;
	public $Dettagli;
	public $Telefono;
	public $Fax;
	public $Mail;
	public $PEC;
	public $Sito;
	public $Note;

	public function __construct( $c , $tipo )
	{

		$query = "SELECT * FROM enti_esterni WHERE CC = '" . $c . "' AND Tipo = '" . $tipo . "'";
			
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = $val['ID'];
		$this->CC = $val['CC'];

		$this->Tipo = $val['Tipo'];
		$this->Denominazione = $val['Denominazione'];
		$this->Partita_Iva = $val['Partita_Iva'];
		$this->Paese = $val['Paese'];
		$this->Comune = $val['Comune'];
		$this->CC_Ente = $val['CC_Ente'];
		$this->Frazione = $val['Frazione'];
		$this->Provincia = $val['Provincia'];
		$this->Cap = $val['Cap'];
		$this->Toponimo = $val['Toponimo'];
		$this->Civico = $val['Civico'];
		$this->Esponente = $val['Esponente'];
		$this->Interno = $val['Interno'];
		$this->Dettagli = $val['Dettagli'];
		$this->Telefono = $val['Telefono'];
		$this->Fax = $val['Fax'];
		$this->Mail = $val['Mail'];
		$this->PEC = $val['PEC'];
		$this->Sito = $val['Sito'];
		$this->Note = $val['Note'];

	}
	
	public function righe_indirizzo()
	{
	
		if($this->Paese=="Italia")
		{
			$ind_1 = $this->Toponimo;
			if($this->Frazione)
				$ind_1 = $this->Frazione.", ".$ind_1;
	
			if($this->Civico)
				$ind_1.= ", ".$this->Civico;
			if($this->Esponente)
				$ind_1.= $this->Esponente;
			if($this->Interno)
				$ind_1.="/".$this->Interno;
			if($this->Dettagli)
				$ind_1.=", ".$this->Dettagli;
				
			$ind_3 = "";
		}
		else
		{
			$ind_1 = $this->Toponimo;
			if($this->Frazione)
				$ind_1 = $this->Frazione.", ".$ind_1;
				
			$ind_3 = $this->Paese;
		}
	
		$ind_2 = $this->Cap." ".$this->Comune;
		$ind_2_senza_prov = $ind_2;
		if($this->Provincia!=null)
			$ind_2.= " ".$this->Provincia;
	
		$indirizzo = array();
		$indirizzo['Riga1'] = $ind_1; // indirizzo destinatario
	
		/////////////////////
		$lunghezza = strlen($ind_1);
		if($lunghezza<50)
		{
			$indirizzo['Riga1'] = strtoupper($ind_1);
			$indirizzo['Riga2'] = strtoupper($ind_2);
			$indirizzo['Riga3'] = strtoupper($ind_3);
			$indirizzo['Riga4'] = "";
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
	
			$indirizzo['Riga1'] = substr(strtoupper($ind_1), 0 , $pos);
			$indirizzo['Riga2'] = substr(strtoupper($ind_1), $pos+1);
			$indirizzo['Riga3'] = strtoupper($ind_2);
			$indirizzo['Riga4'] = strtoupper($ind_3);
		}
		///////////////////////
	
		$indirizzo['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
		if($ind_3!="")
			$indirizzo['Completo'].= ", ".strtoupper($ind_3);
								
		$indirizzo['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
			if($ind_3!="")
		$indirizzo['Senza_Provincia'].= ", ".strtoupper($ind_3);


		return $indirizzo;
		
		}
		
		public function Insert ($transaction = false)
		{
			$fields = array();
			$values = array();
			foreach ($this as $key => $value)
			{
				if ($key != "ID" && isset($value) != false)
				{
					$fields[] = $key;
					$values[] = $value;
				}
			}
			if ($transaction == false)
			{
				$ret = table_insert_record ("enti_esterni", $fields, $values);
				return $ret;  // ritorna l'id inserito
			}
			else
			{
				$ret = table_insert_record_query ("enti_esterni", $fields, $values);
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
				if ($key != $campo  && isset($value) != false)
				{
					$fields[] = $key;
					$values[] = $value;
				}
			}
			if ($transaction == false)
			{
				$ret = table_update_record ("enti_esterni", $fields, $values, $campo, $valoreCampo);
				return $ret;  // ritorna l'id inserito
			}
			else
			{
				$ret = table_update_record_query ("enti_esterni", $fields, $values, $campo , $valoreCampo);
				$ctrlRet = mysql_query($ret);
				return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
			}
		}
		
		public function Delete ()
		{
		
			$query = "DELETE FROM enti_esterni WHERE CC = '" . $this->CC . "' AND Tipo = '".$this->Tipo."'";
			$result = mysql_query($query);
		
			return $result;
		
		}
}

class documento_ente
{
	public $ID;
	public $Comune_ID;
	public $Ente_Esterno_ID;
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
		$query = "SELECT * FROM documento_ente WHERE ID = '".$progr."' AND CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Comune_ID = utf8_decode($val['Comune_ID']);
		$this->Ente_Esterno_ID = utf8_decode($val['Ente_Esterno_ID']);
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
			$ret = table_insert_record ("documento_ente", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("documento_ente", $fields, $values);
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
			$ret = table_update_record ("documento_ente", $fields, $values, $campo , $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("documento_ente", $fields, $values, $campo , $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function Delete ()
	{

		$query = "DELETE FROM documento_ente WHERE ID = '" . $this->ID . "' ";
		$result = mysql_query($query);
		return $result;

	}

	public function Docs_ID ($ente_id)
	{

		$query = "SELECT * FROM documento_ente WHERE Ente_Esterno_ID = '" . $ente_id . "' ";
		$val = mysql_array($query);
		return $val;

	}
}

?>