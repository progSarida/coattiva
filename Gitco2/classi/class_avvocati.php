<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once CLASSI. "/class_indirizzo_enti.php";

class avvocati extends indirizzo_enti
{

	public $ID;
	public $CC;
	
	public $Tipo;
	
	public $Cognome;
	public $Nome;
	public $Comune_Nascita;
	public $Provincia_Nascita;
	public $Data_Nascita;
	
	public $Codice_Fiscale;
	public $Partita_Iva;
		
	public $CC_Studio;
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
	public $Sito;
	public $PEC;
	public $Data_PEC;
	public $Ordine_Avvocati;
	
	public $Foro;

	public function __construct($progr, $c)
	{

		$query = "SELECT * FROM avvocato WHERE ID = '" . $progr . "' AND CC = '" . $c . "'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = $val['ID'];
		$this->CC = $val['CC'];

		$this->Tipo = $val['Tipo'];
		
		$this->Cognome = $val['Cognome'];
		$this->Nome = $val['Nome'];
		$this->Comune_Nascita = $val['Comune_Nascita'];
		$this->Provincia_Nascita = $val['Provincia_Nascita'];
		$this->Data_Nascita = $val['Data_Nascita'];
		
		$this->Codice_Fiscale = $val['Codice_Fiscale'];
		$this->Partita_Iva = $val['Partita_Iva'];
		
		$this->Paese = $val['Paese'];
		$this->Comune = $val['Comune'];
		$this->CC_Studio = $val['CC_Studio'];
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
		
		$this->Data_PEC = $val['Data_PEC'];
		$this->Ordine_Avvocati = $val['Ordine_Avvocati'];
		
		$this->Foro = $val['Foro'];

	}
}

?>