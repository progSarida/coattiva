<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

class posizioni_pubblicita
{
	public $ID; 
	public $CC_Comune;
	public $Anno; 
	public $Contribuente;
	public $Denunciante;
	public $Cointestatario;
	public $Concessione;
	public $Toponimo_Impianto;
	public $Civico_Impianto; 
	public $Interno_Impianto;
	public $Scala_Impianto;
	public $Piano_Impianto;
	public $Descrizione_Impianto;
	public $Note_Impianto;
	public $Insegna_Esercizio;
	public $Riduz_Speciale;
	public $Codice_Mec_Impianto;
	public $Data_Dic;
	public $Data_Inizio_Dic;
	public $Data_Fine_Dic;
	public $Durata_Dic;
	public $Tariffa_Dic;
	public $Dimensioni_Dic;
	public $Num_Impianti_Dic;
	public $Data_Inf;
	public $Data_Inizio_Inf;
	public $Data_Fine_Inf;
	public $Durata_Inf;
	public $Tariffa_Inf;
	public $Dimensioni_Inf;
	public $Num_Impianti_Inf;
	public $Data_Ome;
	public $Data_Inizio_Ome;
	public $Data_Fine_Ome;
	public $Durata_Ome;
	public $Tariffa_Ome;
	public $Dimensioni_Ome;
	public $Num_Impianti_Ome;
	public $prev;
	public $next;

	
	public function __construct($p,$c,$a,$pr)
	{
		$query = "SELECT * FROM pubblicita_posizioni WHERE Contribuente = '$p' AND ID = '$pr' AND (Data_Inizio_Dic BETWEEN '2015-01-01' AND '$a-12-30' AND  
		(Data_Fine_Dic BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Dic = '0000-00-00')) OR (Data_Inizio_Inf BETWEEN '2015-01-01' AND '$a-12-30' AND  
		(Data_Fine_Inf BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Inf = '0000-00-00')) OR (Data_Inizio_Ome BETWEEN '2015-01-01' AND '$a-12-30' AND  
		(Data_Fine_Ome BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Ome = '0000-00-00'))";
		
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Contribuente = $val['Contribuente'];
		$this->Denunciante = $val['Denunciante'];
		$this->Cointestatario = $val['Cointestatario'];
		$this->Concessione = $val['Concessione'];
		$this->Toponimo_Impianto = $val['Toponimo_Impianto'];
		$this->Civico_Impianto = $val['Civico_Impianto'];
		$this->Interno_Impianto = $val['Interno_Impianto'];
		$this->Scala_Impianto = $val['Scala_Impianto'];
		$this->Piano_Impianto = $val['Piano_Impianto'];
		$this->Descrizione_Impianto = $val['Descrizione_Impianto'];
		$this->Note_Impianto = $val['Note_Impianto'];
		$this->Insegna_Esercizio = $val['Insegna_Esercizio'];
		$this->Riduz_Speciale = $val['Riduz_Speciale'];
		$this->Codice_Mec_Impianto = $val['Codice_Mec_Impianto'];
		$this->Data_Dic = $val['Data_Dic'];
		$this->Data_Inizio_Dic = $val['Data_Inizio_Dic'];
		$this->Data_Fine_Dic = $val['Data_Fine_Dic'];
		$this->Durata_Dic = $val['Durata_Dic'];
		$this->Tariffa_Dic = $val['Tariffa_Dic'];
		$this->Dimensioni_Dic = $val['Dimensioni_Dic'];
		$this->Num_Impianti_Dic = $val['Num_Impianti_Dic'];
		$this->Data_Inf = $val['Data_Inf'];
		$this->Data_Inizio_Inf = $val['Data_Inizio_Inf'];
		$this->Data_Fine_Inf = $val['Data_Fine_Inf'];
		$this->Durata_Inf = $val['Durata_Inf'];
		$this->Tariffa_Inf = $val['Tariffa_Inf'];
		$this->Dimensioni_Inf = $val['Dimensioni_Inf'];
		$this->Num_Impianti_Inf = $val['Num_Impianti_Inf'];
		$this->Data_Ome = $val['Data_Ome'];
		$this->Data_Inizio_Ome = $val['Data_Inizio_Ome'];
		$this->Data_Fine_Ome = $val['Data_Fine_Ome'];
		$this->Durata_Ome = $val['Durata_Ome'];
		$this->Tariffa_Ome = $val['Tariffa_Ome'];
		$this->Dimensioni_Ome = $val['Dimensioni_Ome'];
		$this->Num_Impianti_Ome = $val['Num_Impianti_Ome'];
		
		if($pr==0)
		{
			$query = "SELECT * FROM pubblicita_posizioni WHERE Contribuente = '$p' AND (Data_Inizio_Dic BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Dic BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Dic = '0000-00-00')) OR (Data_Inizio_Inf BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Inf BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Inf = '0000-00-00')) OR (Data_Inizio_Ome BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Ome BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Ome = '0000-00-00')) ORDER BY Codice_Mec_Impianto ASC LIMIT 1";
			
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			
			$this->next = $array_result['Codice_Mec_Impianto'];
		
			$query = "SELECT * FROM pubblicita_posizioni WHERE Contribuente = '$p' AND (Data_Inizio_Dic BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Dic BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Dic = '0000-00-00')) OR (Data_Inizio_Inf BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Inf BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Inf = '0000-00-00')) OR (Data_Inizio_Ome BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Ome BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Ome = '0000-00-00')) ORDER BY Codice_Mec_Impianto DESC LIMIT 1";
			
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			
			$this->prev = $array_result['Codice_Mec_Impianto'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_posizioni WHERE Contribuente = '$p' AND ID = '$pr' AND (Data_Inizio_Dic BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Dic BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Dic = '0000-00-00')) OR (Data_Inizio_Inf BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Inf BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Inf = '0000-00-00')) OR (Data_Inizio_Ome BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Ome BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Ome = '0000-00-00')) ORDER BY Codice_Mec_Impianto ASC LIMIT 1";
			
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			
			$this->next = $array_result['Codice_Mec_Impianto'];
		
			$query = "SELECT * FROM pubblicita_posizioni WHERE Contribuente = '$p' AND ID = '$pr' AND (Data_Inizio_Dic BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Dic BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Dic = '0000-00-00')) OR (Data_Inizio_Inf BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Inf BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Inf = '0000-00-00')) OR (Data_Inizio_Ome BETWEEN '2015-01-01' AND '$a-12-30' AND  
			(Data_Fine_Ome BETWEEN '$a-01-02' AND '$a-12-31' OR Data_Fine_Ome = '0000-00-00')) ORDER BY Codice_Mec_Impianto ASC LIMIT 1";
			
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			
			$this->prev = $array_result['Codice_Mec_Impianto'];
		}
	}
}

class stradario_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Toponimo_ID;
	public $Pari;
	public $Dispari;
	public $Da_Num;
	public $A_Num;
	public $Da_Km;
	public $A_Km;
	public $prev;
	public $next;

	public function __construct($progr=0,$c)
	{
		$query = "SELECT * FROM pubblicita_frazionamento WHERE ID = '$progr' AND CC_Comune='$c'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Toponimo_ID = $val['Toponimo_ID'];
		$this->Pari = $val['Pari'];
		$this->Dispari = $val['Dispari'];
		$this->Da_Num = $val['Da_Num'];
		$this->A_Num = $val['A_Num'];
		$this->Da_Km = $val['Da_Km'];
		$this->A_Km = $val['A_Km'];		

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_frazionamento WHERE CC_Comune='$c' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_frazionamento WHERE CC_Comune='$c' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_frazionamento WHERE CC_Comune='$this->CC_Comune' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_frazionamento WHERE CC_Comune='$this->CC_Comune' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class parametri_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Tipo_Pubblicita;
	public $Tipo_Bollettino;
	public $Aut_Spedizione;
	public $Aut_Spedizione_Data;
	public $Aut_Stampa;
	public $Aut_Stampa_Data;
	public $Aut_Stampa_Documenti;
	public $Categoria_Speciale;
	public $Categoria_Stagionale;
	public $Da_Periodo;
	public $A_Periodo;
	public $prev;
	public $next;
	
	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_parametri WHERE CC_Comune='$c' AND Anno = '$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Tipo_Pubblicita = $val['Tipo_Pubblicita'];
		$this->Tipo_Bollettino = $val['Tipo_Bollettino'];
		$this->Aut_Spedizione = $val['Aut_Spedizione'];
		$this->Aut_Spedizione_Data = $val['Aut_Spedizione_Data'];
		$this->Aut_Stampa = $val['Aut_Stampa'];
		$this->Aut_Stampa_Data = $val['Aut_Stampa_Data'];
		$this->Aut_Stampa_Documenti = $val['Aut_Stampa_Documenti'];
		$this->Categoria_Speciale = $val['Categoria_Speciale'];
		$this->Categoria_Stagionale = $val['Categoria_Stagionale'];
		$this->Da_Periodo = $val['Da_Periodo'];
		$this->A_Periodo = $val['A_Periodo'];
		
		
		
		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_parametri WHERE CC_Comune='$c' AND Anno='$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
			
			$query = "SELECT * FROM pubblicita_parametri WHERE CC_Comune='$c' AND Anno='$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else 
		{
			$query = "SELECT * FROM pubblicita_parametri	WHERE CC_Comune='$this->CC_Comune' AND Anno='$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
			
			$query = "SELECT * FROM pubblicita_parametri	WHERE CC_Comune='$this->CC_Comune' AND Anno='$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class scadenze
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Par_Data_Denuncia;
	public $Par_Data_Denuncia_Cess;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_scadenze WHERE CC_Comune='$c' AND Anno = '$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Par_Data_Denuncia = $val['Par_Data_Denuncia'];
		$this->Par_Data_Denuncia_Cess = $val['Par_Data_Denuncia_Cess'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_scadenze WHERE CC_Comune='$c' AND Anno='$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_scadenze WHERE CC_Comune='$c' AND Anno='$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_scadenze	WHERE CC_Comune='$this->CC_Comune' AND Anno='$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_scadenze	WHERE CC_Comune='$this->CC_Comune' AND Anno='$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class ccp
{
	public $ID;
	public $CC_Comune;
	public $Intestazione;
	public $Numero_Ccp;
	public $Iban;
	public $Num_Convenzione;
	public $Data_Convenzione;
	public $Intestazione2;
	public $Numero_Ccp2;
	public $Iban2;
	public $prev;
	public $next;

	public function __construct($progr=0,$c)
	{
		$query = "SELECT * FROM pubblicita_ccp WHERE CC_Comune = '$c'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Intestazione = $val['Intestazione'];
		$this->Numero_Ccp = $val['Numero_Ccp'];
		$this->Iban = $val['Iban'];
		$this->Num_Convenzione = $val['Num_Convenzione'];
		$this->Data_Convenzione = $val['Data_Convenzione'];
		$this->Intestazione2 = $val['Intestazione2'];
		$this->Numero_Ccp2 = $val['Numero_Ccp2'];
		$this->Iban2 = $val['Iban2'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_ccp WHERE CC_Comune = '$c' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_ccp WHERE CC_Comune = '$c' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_ccp	WHERE CC_Comune = '$this->CC_Comune' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_ccp	WHERE CC_Comune = '$this->CC_Comune' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class mag_rid_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Maggiorazione_Speciale;
	public $Maggiorazione_Stagionale;
	public $Riduzione;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_mag_rid WHERE CC_Comune = '$c' AND Anno='$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Maggiorazione_Speciale = $val['Maggiorazione_Speciale'];
		$this->Maggiorazione_Stagionale = $val['Maggiorazione_Stagionale'];
		$this->Riduzione = $val['Riduzione'];
		
		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_mag_rid WHERE CC_Comune = '$c' AND Anno='$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_mag_rid WHERE CC_Comune = '$c' AND Anno='$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_mag_rid	WHERE CC_Comune = '$this->CC_Comune' AND Anno='$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_mag_rid	WHERE CC_Comune = '$this->CC_Comune' AND Anno='$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class canoni_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Canone_Fisso_Garantito;
	public $Aggio_Tributi;
	public $Aggio_Diritti_Urgenza;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_canoni WHERE CC_Comune = '$c' AND Anno='$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Canone_Fisso_Garantito = $val['Canone_Fisso_Garantito'];
		$this->Aggio_Tributi = $val['Aggio_Tributi'];
		$this->Aggio_Diritti_Urgenza = $val['Aggio_Diritti_Urgenza'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_canoni WHERE CC_Comune = '$c' AND Anno='$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_canoni WHERE CC_Comune = '$c' AND Anno='$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_canoni	WHERE CC_Comune = '$this->CC_Comune' AND Anno='$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_canoni	WHERE CC_Comune = '$this->CC_Comune' AND Anno='$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class parametri_preavvisi_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Par_Anno;
	public $Par_Num_Elenco;
	public $Par_Data_Scadenza;
	public $Par_Spese;
	public $Par_Importo_Max;
	public $Par_Testo;
	public $Par_Num_Rate;
	public $Par_Rata1;
	public $Par_Rata2;
	public $Par_Rata3;
	public $Par_Rata4;
	public $Par_Rata5;
	public $Par_Rata6;
	public $Par_Rata_Unica;
	public $Par_Arrotondamento;
	public $Par_Quinto_Campo;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_par_preavvisi WHERE ID = '$progr' AND CC_Comune='$c' AND Par_Anno='$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Par_Anno = $val['Par_Anno'];
		$this->Par_Num_Elenco = $val['Par_Num_Elenco'];
		$this->Par_Data_Scadenza = $val['Par_Data_Scadenza'];
		$this->Par_Spese = $val['Par_Spese'];
		$this->Par_Importo_Max = $val['Par_Importo_Max'];
		$this->Par_Testo = $val['Par_Testo'];
		$this->Par_Num_Rate = $val['Par_Num_Rate'];
		$this->Par_Rata1 = $val['Par_Rata1'];
		$this->Par_Rata2 = $val['Par_Rata2'];
		$this->Par_Rata3 = $val['Par_Rata3'];
		$this->Par_Rata4 = $val['Par_Rata4'];
		$this->Par_Rata5 = $val['Par_Rata5'];
		$this->Par_Rata6 = $val['Par_Rata6'];
		$this->Par_Rata_Unica = $val['Par_Rata_Unica'];
		$this->Par_Arrotondamento = $val['Par_Arrotondamento'];
		$this->Par_Quinto_Campo = $val['Par_Quinto_Campo'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_par_preavvisi WHERE CC_Comune='$c' AND Par_Anno='$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_par_preavvisi WHERE CC_Comune='$c' AND Par_Anno='$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_par_preavvisi WHERE CC_Comune='$this->CC_Comune' AND Par_Anno='$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];
				
			$query = "SELECT * FROM pubblicita_par_preavvisi WHERE CC_Comune='$this->CC_Comune' AND Par_Anno='$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class tariffe_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Codice;
	public $Descrizione;
	public $Importo;
	public $Periodo;
	public $Maggiorazione_Superficie;
	public $Maggiorazione_Luminosa;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_tariffe WHERE ID = '$progr' AND CC_Comune = '$c' AND Anno = '$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Codice = $val['Codice'];
		$this->Descrizione = $val['Descrizione'];
		$this->Importo = $val['Importo'];
		$this->Periodo = $val['Periodo'];
		$this->Maggiorazione_Superficie = $val['Maggiorazione_Superficie'];
		$this->Maggiorazione_Luminosa = $val['Maggiorazione_Luminosa'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_tariffe WHERE CC_Comune='$c' AND Anno='$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_tariffe WHERE CC_Comune='$c' AND Anno='$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_tariffe WHERE CC_Comune='$this->CC_Comune' AND Anno='$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_tariffe WHERE CC_Comune='$this->CC_Comune' AND Anno='$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
class voci_tariffe_pubblicita
{
	public $ID;
	public $Codice;
	public $Descrizione;
	public $Bifacciale;
	public $Codice_Tariffa;
	public $prev;
	public $next;

	public function __construct($progr=0)
	{
		$query = "SELECT * FROM pubblicita_voci_tariffe WHERE ID = '$progr'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->Codice = $val['Codice'];
		$this->Descrizione = $val['Descrizione'];
		$this->Bifacciale = $val['Bifacciale'];
		$this->Codice_Tariffa = $val['Codice_Tariffa'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_voci_tariffe ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_voci_tariffe ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_voci_tariffe WHERE ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_voci_tariffe WHERE ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}

class contenzioso_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Tipo_Sanzione;
	public $Perc_Sanzione;
	public $Importo_Sanzione;
	public $Minimo;
	public $Riduzione_S;
	public $Riduzione_T;
	public $Riduzione_A;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_contenzioso WHERE ID = '$progr' AND CC_Comune = '$c' AND Anno = '$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Tipo_Sanzione = $val['Tipo_Sanzione'];
		$this->Perc_Sanzione = $val['Perc_Sanzione'];
		$this->Importo_Sanzione = $val['Importo_Sanzione'];
		$this->Minimo = $val['Minimo'];
		$this->Riduzione_S = $val['Riduzione_S'];
		$this->Riduzione_T = $val['Riduzione_T'];
		$this->Riduzione_A = $val['Riduzione_A'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_contenzioso WHERE CC_Comune='$c' AND Anno = '$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_contenzioso WHERE CC_Comune='$c' AND Anno = '$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_contenzioso WHERE CC_Comune='$this->CC_Comune' AND Anno = '$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_contenzioso WHERE CC_Comune='$this->CC_Comune' AND Anno = '$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}

class interessi_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Da;
	public $A;
	public $Percentuale_Da;
	public $Percentuale_A;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_interessi WHERE ID = '$progr' AND CC_Comune = '$c' AND Anno = '$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Da = $val['Da'];
		$this->A = $val['A'];
		$this->Percentuale_Da = $val['Percentuale_Da'];
		$this->Percentuale_A = $val['Percentuale_A'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_interessi WHERE CC_Comune='$c' AND Anno = '$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_interessi WHERE CC_Comune='$c' AND Anno = '$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_interessi WHERE CC_Comune='$this->CC_Comune' AND Anno = '$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_interessi WHERE CC_Comune='$this->CC_Comune' AND Anno = '$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}

class riduzioni_pubblicita
{
	public $ID;
	public $CC_Comune;
	public $Anno;
	public $Riduzione_S_Perc;
	public $Riduzione_S_Giorni;
	public $Riduzione_T_Perc;
	public $Riduzione_T_Giorni;
	public $Riduzione_A_Perc;
	public $Riduzione_A_Giorni;
	public $prev;
	public $next;

	public function __construct($progr=0,$c,$a)
	{
		$query = "SELECT * FROM pubblicita_riduzioni WHERE CC_Comune = '$c' AND Anno = '$a'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		$this->ID = $val['ID'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Anno = $val['Anno'];
		$this->Riduzione_S_Perc = $val['Riduzione_S_Perc'];
		$this->Riduzione_S_Giorni = $val['Riduzione_S_Giorni'];
		$this->Riduzione_T_Perc = $val['Riduzione_S_Perc'];
		$this->Riduzione_T_Giorni = $val['Riduzione_S_Giorni'];
		$this->Riduzione_A_Perc = $val['Riduzione_S_Perc'];
		$this->Riduzione_A_Giorni = $val['Riduzione_S_Giorni'];

		if($progr==0)
		{
			$query = "SELECT * FROM pubblicita_riduzioni WHERE CC_Comune='$c' AND Anno = '$a' ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_riduzioni WHERE CC_Comune='$c' AND Anno = '$a' ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
		else
		{
			$query = "SELECT * FROM pubblicita_riduzioni WHERE CC_Comune='$this->CC_Comune' AND Anno = '$a' AND ( (ID>'$this->ID') ) ORDER BY ID ASC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];

			$query = "SELECT * FROM pubblicita_riduzioni WHERE CC_Comune='$this->CC_Comune' AND Anno = '$a' AND ( (ID<'$this->ID') ) ORDER BY ID DESC LIMIT 1";
			$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];
		}
	}
}
?>