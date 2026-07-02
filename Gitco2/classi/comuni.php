<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include_once CLASSI. "/class_indirizzo_enti.php";

//*****************************************************************
// 				Classi regione, provincia e comuni                *
// 						Classe toponimi cappati					  *
//*****************************************************************
class regione
{
	public $Reg_Codice;
	public $Reg_Nome;

	public function __construct($nomeReg)
	{
		$query = "SELECT * FROM regioni_lista WHERE Reg_Nome='".$nomeReg."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Reg_Codice = $val['Reg_Codice'];
		$this->Reg_Nome = $val['Reg_Nome'];
	}
}

class provincia extends regione
{
	public $Pro_Codice;
	public $Pro_Nome;
	public $Pro_Sigla;

	public function __construct ($nomeProv)
	{
		$query = "SELECT * FROM province_lista WHERE Pro_Nome='".$nomeProv."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Pro_Codice = $val['Pro_Codice'];
		$this->Pro_Nome = $val['Pro_Nome'];
		$this->Pro_Sigla = $val['Pro_Sigla'];

		$query = "SELECT * FROM regioni_lista WHERE Reg_Codice='".$val['Pro_Codice_Regione']."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Reg_Codice = $val['Reg_Codice'];
		$this->Reg_Nome = $val['Reg_Nome'];
	}
}

class comune extends provincia
{
	public $Cap;
	public $Capoluogo;
	public $Codice_Catastale;
	public $Litoraneo;
	public $Montano;
	public $Nome;
	public $Nome_Ted;
	public $ISTAT;
	public $Stemma_1;
	public $dim_stemma_1;
	public $Stemma_2;
	public $dim_stemma_2;

	public function __construct ($CodiceCatastale)
	{
		$query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$CodiceCatastale."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Cap = $val['Com_Cap'];
		$this->Capoluogo = $val['Com_Capoluogo'];
		$this->Codice_Catastale = $val['Com_Codice_Catastale'];
		$this->Litoraneo = $val['Com_Litoraneo'];
		$this->Montano = $val['Com_Montano'];
		$this->Nome = $val['Com_Nome'];
		$this->Nome_Ted = $val['Com_Nome_Ted'];
		$this->ISTAT = $val['Com_Codice'];

		if( $val['Com_Stemma_1'] != "" || $val['Com_Stemma_2']!= "")
		{
			if( $val['Com_Stemma_1']!= "" )
			{
				$this->Stemma_1 = "/gitco2/stemmi/".$CodiceCatastale."/".$val['Com_Stemma_1'];
				$path_1 = STEMMI."/".$CodiceCatastale."/".$val['Com_Stemma_1'];

				if($val['Com_Stemma_2']!= "")
				{
					$this->Stemma_2 = "/gitco2/stemmi/".$CodiceCatastale."/".$val['Com_Stemma_2'];
					$path_2 = STEMMI."/".$CodiceCatastale."/".$val['Com_Stemma_2'];
				}
				else
				{
					$this->Stemma_2 = "/gitco2/stemmi/".$CodiceCatastale."/".$val['Com_Stemma_1'];
					$path_2 = STEMMI."/".$CodiceCatastale."/".$val['Com_Stemma_1'];
				}
			}
			else
			{
				$this->Stemma_1 = "/gitco2/stemmi/".$CodiceCatastale."/".$val['Com_Stemma_2'];
				$path_1 = STEMMI."/".$CodiceCatastale."/".$val['Com_Stemma_2'];
				$this->Stemma_2 = "/gitco2/stemmi/".$CodiceCatastale."/".$val['Com_Stemma_2'];
				$path_2 = STEMMI."/".$CodiceCatastale."/".$val['Com_Stemma_2'];
			}

			$this->dim_stemma_1 = getimagesize($path_1);
			$this->dim_stemma_2 = getimagesize($path_2);
		}
		else
		{
			$this->Stemma_1 = "";
			$path_1 = "";
			$this->Stemma_2 = "";
			$path_2 = "";

			$this->dim_stemma_1 = "";
			$this->dim_stemma_2 = "";
		}



		$query = "SELECT * FROM province_lista WHERE Pro_Codice='".$val['Com_Codice_Provincia']."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Pro_Codice = $val['Pro_Codice'];
		$this->Pro_Nome = $val['Pro_Nome'];
		$this->Pro_Sigla = $val['Pro_Sigla'];

		$query = "SELECT * FROM regioni_lista WHERE Reg_Codice='".$val['Pro_Codice_Regione']."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Reg_Codice = $val['Reg_Codice'];
		$this->Reg_Nome = $val['Reg_Nome'];
	}

	public function trovaCC($nome_com, $cap)
	{
		$query = "SELECT Com_Nome, Com_Codice_Catastale, Com_Cap, Pro_Sigla FROM comuni_lista INNER JOIN province_lista ON Pro_Codice = Com_Codice_Provincia WHERE Com_Nome = \"".ucwords($nome_com)."\" ";
		$result = safe_query($query);
		$array_com = mysql_fetch_array($result);

		if($array_com['Com_Codice_Catastale']=="")
		{
			$query = "SELECT Com_Nome, Com_Codice_Catastale, Com_Cap, Pro_Sigla FROM comuni_lista INNER JOIN province_lista ON Pro_Codice = Com_Codice_Provincia WHERE Com_Cap = '".$cap."'";
			$result = safe_query($query);
			$array_com = mysql_fetch_array($result);
		}

		return $array_com;
	}
}

class toponimi_cappati extends comune
{
	public $ID;
	public $CC_Toponimo;
	public $Comune;
	public $Odonimo;
	public $DUG_Odonimo;
	public $DUF_Odonimo;
	public $Num_Civici;
	public $Cap;
	public $Odonimo_Secondario;
	public $Frazione;
	public $vie = array();

	public function __construct ( $CodiceCatastale, $progrVia = 0, $odonimo = "" )
	{
		if( $progrVia != 0 )
		{
			$query = "SELECT * FROM toponimi_cappati WHERE ID = '".$ProgrVia."'";
			$result = safe_query($query);
			$val = mysql_fetch_array($result);

			$this->ID = $val['ID'];
			$this->CC_Toponimo = $val['CC_Toponimo'];
			$this->Comune = $val['Comune'];
			$this->Odonimo = $val['Odonimo'];
			$this->DUG_Odonimo = $val['DUG_Odonimo'];
			$this->DUF_Odonimo = $val['DUF_Odonimo'];
			$this->Num_Civici = $val['Num_Civici'];
			$this->Odonimo_Secondario = $val['Odonimo_Secondario'];
			$this->Cap = $val['Cap'];
			$this->Frazione = $val['Frazione'];

		}
		else
		{
			$where = "CC_Toponimo = '".$CodiceCatastale."'";
			$where .= "AND Odonimo LIKE '%".$odonimo."%'";

			$order = "DUF_Odonimo";

			$this->vie = select_mysql_array('*', 'toponimi_cappati', $where , $order );
		}

		$query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$CodiceCatastale."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Cap = $val['Com_Cap'];
		$this->Capoluogo = $val['Com_Capoluogo'];
		$this->Codice_Catastale = $val['Com_Codice_Catastale'];
		$this->Litoraneo = $val['Com_Litoraneo'];
		$this->Montano = $val['Com_Montano'];
		$this->Nome = $val['Com_Nome'];
		$this->Nome_Ted = $val['Com_Nome_Ted'];

		$query = "SELECT * FROM province_lista WHERE Pro_Codice='".$val['Com_Codice_Provincia']."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Pro_Codice = $val['Pro_Codice'];
		$this->Pro_Nome = $val['Pro_Nome'];
		$this->Pro_Sigla = $val['Pro_Sigla'];

		$query = "SELECT * FROM regioni_lista WHERE Reg_Codice='".$val['Pro_Codice_Regione']."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->Reg_Codice = $val['Reg_Codice'];
		$this->Reg_Nome = $val['Reg_Nome'];
	}
}

class ente_gestito
{
	public $ID;
	public $CC;
	public $Nome;
	public $Descrizione;
	public $Codici_Unione = array();
	public $Comune = array();
	public $Stemma_1;
	public $Stemma_Principale;
	public $dim_stemma_1;
	public $Path_Stemma_1;
	public $Stemma_2;
	public $Stemma_Secondario;
	public $dim_stemma_2;
	public $Path_Stemma_2;
	public $Stemma_3;
	public $Stemma_Targhe_Estere;
	public $dim_stemma_3;
	public $Path_Stemma_3;
	public $Ente;
	public $Info_ID;
	public $Info = null;
	public $Gestore_ID;
	public $Gestore = null;
	public $Ufficio_ID;
	public $Ufficio = null;
	public $Codice_290;
	public $Classe_Demo;
    public $Autorizzazione;
    public $Select_Tax;

	public function __construct ($CodiceCatastale)
	{
		$query = "SELECT * FROM enti_gestiti WHERE CC = '".$CodiceCatastale."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = $val['ID'];
		$this->Nome = $val['Denominazione'];
		$this->CC = $val['CC'];
		$this->Descrizione = $val['Descrizione'];
		$this->Codice_290 = $val['Codice_290'];
		$this->Classe_Demo = $val['Classe_Demo'];
		$this->Autorizzazione = $val['Autorizzazione'];
        $this->Select_Tax = $val['Select_Tax'];

		$cod = explode("/", $val['Codici_Unione']);

		for($k=0;$k<count($cod);$k++)
		{
			$this->Codici_Unione[$k] = $cod[$k];
			$this->Comune[$k] = new comune($cod[$k]);
		}

		if($val['Codici_Unione']=="")
		{
			$this->Ente = new comune(substr($CodiceCatastale,0,4));//substr nel caso avessi comune bis
		}

		$this->Info_ID = $val['Info_ID'];
		$this->Info = new gestore($val['Info_ID']);

		$this->Gestore_ID = $val['Gestore_ID'];
		if( $val['Gestore_ID'] != 0 )
			$this->Gestore = new gestore($val['Gestore_ID']);
		else
			$this->Gestore = new gestore($val['Info_ID']);

		$this->Ufficio_ID = $val['Ufficio_ID'];
		$this->Ufficio = new gestore($val['Ufficio_ID']);

		if( $val['Stemma_1'] != "" || $val['Stemma_2']!= "")
		{
			if( $val['Stemma_1']!= "" )
			{
				$this->Stemma_Principale = "si";
				$this->Stemma_1 = "/gitco2/stemmi/".$this->CC."/".$val['Stemma_1'];
				$path_1 = STEMMI."/".$this->CC."/".$val['Stemma_1'];

				if($val['Stemma_2']!= "")
				{
					$this->Stemma_Secondario = "si";
					$this->Stemma_2 = "/gitco2/stemmi/".$this->CC."/".$val['Stemma_2'];
					$path_2 = STEMMI."/".$this->CC."/".$val['Stemma_2'];
				}
				else
				{
					$this->Stemma_Secondario = "no";
					$this->Stemma_2 = "/gitco2/stemmi/".$this->CC."/".$val['Stemma_1'];
					$path_2 = STEMMI."/".$this->CC."/".$val['Stemma_1'];
				}
			}
			else
			{
				$this->Stemma_Principale = "no";
				$this->Stemma_Secondario = "si";

				$this->Stemma_1 = "/gitco2/stemmi/".$this->CC."/".$val['Stemma_2'];
				$path_1 = STEMMI."/".$this->CC."/".$val['Stemma_2'];
				$this->Stemma_2 = "/gitco2/stemmi/".$this->CC."/".$val['Stemma_2'];
				$path_2 = STEMMI."/".$this->CC."/".$val['Stemma_2'];
			}

			$this->Path_Stemma_1 = $path_1;
			$this->Path_Stemma_2 = $path_2;

			$this->dim_stemma_1 = getimagesize($path_1);
			$this->dim_stemma_2 = getimagesize($path_2);
		}
		else
		{
			$this->Stemma_Principale = "no";
			$this->Stemma_Secondario = "no";

			$this->Stemma_1 = "";
			$path_1 = "";
			$this->Stemma_2 = "";
			$path_2 = "";

			$this->Path_Stemma_1 = $path_1;
			$this->Path_Stemma_2 = $path_2;

			$this->dim_stemma_1 = "";
			$this->dim_stemma_2 = "";
		}

		if( $val['Stemma_3'] != "")
		{
			$this->Stemma_Targhe_Estere = "si";
			$this->Stemma_3 = "/gitco2/stemmi/".$this->CC."/".$val['Stemma_3'];
			$path_3 = STEMMI."/".$this->CC."/".$val['Stemma_3'];

			$this->Path_Stemma_3 = $path_3;

			$this->dim_stemma_3 = getimagesize($path_3);
		}
		else
		{
			$this->Stemma_Targhe_Estere = "no";

			$this->Stemma_3 = "";
			$path_3 = "";

			$this->Path_Stemma_3 = $path_3;

			$this->dim_stemma_3 = "";
		}
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

		$query = table_update_record_query ("enti_gestiti", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Delete_Stemmi ()
	{
		$query = "UPDATE enti_gestiti SET Stemma_1 = '', Stemma_2 = ''  WHERE CC = '".$this->CC."'";
		$ctrl_query = mysql_query($query);

		if($ctrl_query===true)
		{
			if($this->Path_Stemma_1!="")
				unlink($this->Path_Stemma_1);
			if($this->Path_Stemma_2!="")
				unlink($this->Path_Stemma_2);
		}

		return $ctrl_query;
	}

	public function Update_Stemmi ( $stemma_1 , $stemma_2 )
	{
		$query = "UPDATE enti_gestiti ";
		if($stemma_1!="" && $stemma_2!="")
			$query.= "SET Stemma_1 = '".$stemma_1."', Stemma_2 = '".$stemma_2."' ";
		else if($stemma_1!="")
			$query.= "SET Stemma_1 = '".$stemma_1."' ";
		else if($stemma_2!="")
			$query.= "SET Stemma_2 = '".$stemma_2."' ";
		else
			return true;

		$query.= "WHERE CC = '".$this->CC."'";

		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Array_Selezione_Comuni ($autorizzazione,$cc)
	{
		$where = " CC_Anno = CC AND Gestione_Coattiva = 'Y' ";

		if($autorizzazione==2)
            $where .= " AND CC = '" . $cc. "' ";
		else if($autorizzazione>2)
            $where .= " AND Autorizzazione = '" . $autorizzazione. "' ";

		$array_comuni = select_mysql_array("DISTINCT enti_gestiti.ID, CC, Denominazione", " enti_gestiti, anni_gestiti ", $where, "Denominazione");

		return $array_comuni;
	}

	public function Options_Comuni($autorizzazione, $pagina, $cc)
	{
		/**
			OPZIONI DI SELEZIONE DEL COMUNE
			@param autorizzazione (Stringa) = autorizzazione utente;
			@param pagina (Stringa) = nome pagina da ricaricare dopo la scelta del comune;
		 */

		$array_comuni = $this->Array_Selezione_Comuni($autorizzazione, $cc);

		$select = "<select id='select_comune' size=1 onchange='cambio_comune_js(\"".$pagina."\");'>";
		$select.= "<option></option>";
		$select.= "<optgroup label='Ente di gestione'>";

		for($i=0;$i<count($array_comuni);$i++)
			$select.= "<option value='".$array_comuni[$i]['CC']."'>".$array_comuni[$i]['Denominazione']." - ".$array_comuni[$i]['CC']." (".$array_comuni[$i]['ID'].")</option>";

		$select.="</optgroup>";
		$select.="</select>";

		return $select;

	}
}

class gestore
{

	public $ID;
	public $Denominazione;
	public $Codice_Fiscale;
	public $Partita_Iva;
	public $CC;
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
	public $Orario;
	public $Tipo;
	public $Stemma;
	public $Path_Stemma;

	public $IndirizzoCompleto;

	public function __construct($progr)
	{

		$query = "SELECT * FROM gestore WHERE ID = '" . $progr . "'";

		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = $val['ID'];
		$this->CC = $val['CC'];
		$this->Tipo = $val['Tipo'];
		$this->Denominazione = $val['Denominazione'];
		$this->Codice_Fiscale = $val['Codice_Fiscale'];
		$this->Partita_Iva = $val['Partita_Iva'];
		$this->Paese = $val['Paese'];
		$this->Comune = $val['Comune'];
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
		$this->Orario = $val['Orario'];

		if( $val['Stemma'] != "")
		{
			$query = "SELECT CC FROM enti_gestiti WHERE Gestore_ID = '" . $progr . "'";
			$cc = single_query($query);
			$this->Stemma = "/gitco2/stemmi/".$cc."/".$val['Stemma'];
			$path = STEMMI."/".$cc."/".$val['Stemma'];
			$this->Path_Stemma = $path;
		}
		else
		{
			$this->Stemma = "";
			$this->Path_Stemma = "";
		}


		$this->IndirizzoCompleto = $this->Toponimo;
		if ($this->Civico != "") $this->IndirizzoCompleto .= " " . $this->Civico;
		if ($this->Esponente != "") $this->IndirizzoCompleto .= $this->Esponente;
		if ($this->Interno != "0") $this->IndirizzoCompleto .= "/" . $this->Interno;

	}

	public function Update_Stemma ( $ID_gestore, $stemma )
	{
		$query = "UPDATE gestore ";
		$query.= "SET Stemma = '".$stemma."' ";
		$query.= "WHERE ID = '".$ID_gestore."' ";

		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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

		$fax = "FAX ".$this->Fax;
		if($this->Fax=="")
			$fax = "";

		$indirizzo_destinatario = array();
		$indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

		/////////////////////
		$lunghezza = strlen($ind_1);
		if($lunghezza<50)
		{
			$indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
			$indirizzo_destinatario['Riga2'] = strtoupper($ind_2)." ".strtoupper($ind_3);
			$indirizzo_destinatario['Riga3'] = $fax;
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
				$indirizzo_destinatario['Riga3'] = strtoupper($ind_2)." ".strtoupper($ind_3);
				$indirizzo_destinatario['Riga4'] = $fax;
		}
				///////////////////////

				$indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
				if($ind_3!="")
					$indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);

				$indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
				if($ind_3!="")
					$indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);

				$indirizzo_destinatario['Destinatario'] = $this->Denominazione;

				return $indirizzo_destinatario;

	}

	public function riga_indirizzo()
	{
		$riga_indirizzo = "";
		if($this->Toponimo!="")
		{
			$riga_indirizzo = ucwords(strtolower($this->Toponimo));

			if($this->Civico!="" && $this->Civico!=0)
				$riga_indirizzo.=", ".$this->Civico;
			if($this->Esponente)
				$riga_indirizzo .= $this->Esponente;
			if($this->Interno)
				$riga_indirizzo.="/".$this->Interno;
			if($this->Dettagli)
				$riga_indirizzo.=", ".$this->Dettagli;

			if($this->Comune != "")
				$riga_indirizzo.= " - ".$this->Cap." ".$this->Comune." (".$this->Provincia. ")";
		}

		return $riga_indirizzo;
	}

	public function riga_PI_CF()
	{
		$riga_CF_PI = "";
		if($this->Partita_Iva!="" || $this->Codice_Fiscale!="")
		{
			$riga_CF_PI = "P.I.: " . $this->Partita_Iva ."  -  C.F.: ".$this->Codice_Fiscale;

			if($this->Partita_Iva == "")
				$riga_CF_PI = "C.F.: ".$this->Codice_Fiscale;
			else if($this->Codice_Fiscale == "")
				$riga_CF_PI = "P.I.: " . $this->Partita_Iva;
		}

		return $riga_CF_PI;
	}

	public function riga_Tel_Fax()
	{
		$riga_tel_fax = "";
		if($this->Telefono!="" || $this->Fax!="")
		{
			$riga_tel_fax = "Tel: " . $this->Telefono ."  -  Fax: ".$this->Fax;

			if($this->Telefono == "")
				$riga_tel_fax = "Fax: ".$this->Fax;
			else if($this->Fax == "")
				$riga_tel_fax = "Tel: " . $this->Telefono;
		}

		return $riga_tel_fax;
	}

	public function riga_Mail_Sito()
	{
		$riga_mail_sito = "";
		if($this->Mail!="" || $this->Sito!="")
		{
			$riga_mail_sito = "eMail: " . $this->Mail ."  -  Sito: ".$this->Sito;

			if($this->Mail == "")
				$riga_mail_sito = "Sito: ".$this->Sito;
			else if($this->Sito == "")
				$riga_mail_sito = "eMail: " . $this->Mail;
		}
		return $riga_mail_sito;
	}

	public function riga_Mail_PEC()
	{
		$riga_mail_sito = "";
		if($this->Mail!="" || $this->PEC!="")
		{
			$riga_mail_sito = "eMail: " . $this->Mail ."  -  PEC: ".$this->PEC;

			if($this->Mail == "")
				$riga_mail_sito = "PEC: ".$this->PEC;
			else if($this->PEC == "")
				$riga_mail_sito = "eMail: " . $this->Mail;
		}
		return $riga_mail_sito;
	}

	public function righe_orario()
	{
		$orario = $this->Orario;
		$array_orario['Riga1'] = "";
		$array_orario['Riga2'] = "";

		if($orario!="")
		{
			$lunghezza = strlen($orario);
			if($lunghezza <= 50)
			{
				$array_orario['Riga1'] = $orario;
				$array_orario['Riga2'] = "";
			}
			else
			{
				$pos = 50;
				//echo $pos;
				for( $i=0; $i<$pos; $i++)
				{
				$carattere = substr($orario, $pos-$i,1);
				//echo $carattere."*";
				if($carattere==" ")
				{
				//echo $pos-$i;
					$pos = $pos-$i;
					break;
				}
				}

				$array_orario['Riga1'] = substr($orario, 0 , $pos);
				$array_orario['Riga2'] = substr($orario, $pos+1);
			}
		}

		return $array_orario;
	}

	public function intestazione_gestore( $servizio , $nome_comune )
	{
		$tipo = $this->Tipo;
		if($tipo=="Ufficio")	return false;
		$intestazione = array();

		$comune_gestore = new comune($this->CC);
		$provincia = $comune_gestore->Pro_Nome;
		unset($comune_gestore);

		if($tipo == "Comune")
		{
			//RIGA 1
			$intestazione['Riga1'] = $this->Denominazione;

			//RIGA 2
			$intestazione['Riga2'] = "Provincia di ".$provincia;

			//RIGA 3
			$intestazione['Riga3'] = $this->riga_indirizzo();

			//RIGA 4
			$intestazione['Riga4'] = $this->riga_PI_CF();

			//RIGA 5
			$intestazione['Riga5'] = $this->riga_Tel_Fax();

			//RIGA 6
			$intestazione['Riga6'] = $this->riga_Mail_Sito();

			//RIGA 7
			$intestazione['Riga7'] = "Servizio: ".$servizio;
		}
		else if($tipo == "Concessionario")
		{
			//RIGA 1
			$intestazione['Riga1'] = $tipo." ".$this->Denominazione;

			//RIGA 2
			$intestazione['Riga2'] = $this->riga_indirizzo();

			//RIGA 3
			$intestazione['Riga3'] = $this->riga_PI_CF();

			//RIGA 4
			$intestazione['Riga4'] = $this->riga_Tel_Fax();

			//RIGA 5
			$intestazione['Riga5'] = $this->riga_Mail_Sito();

			//RIGA 6
			$intestazione['Riga6'] = "Servizio: ".$servizio;

			//RIGA 7
			$intestazione['Riga7'] = "Gestione: ".$nome_comune;
		}

		return $intestazione;
	}

	public function intestazione_ufficio()
	{
		if($this->Tipo!="Ufficio")	return false;
		$intestazione = array();

		//RIGA 1
		$intestazione['Riga1'] = $this->Denominazione;

		//RIGA 2
		$intestazione['Riga2'] = $this->riga_indirizzo();

		//RIGA 3
		$intestazione['Riga3'] = $this->riga_Tel_Fax();

		//RIGA 4
		$intestazione['Riga4'] = $this->riga_Mail_PEC();

		//RIGA 5-6
		$orario = $this->righe_orario();
		$intestazione['Riga5'] = "Orario: ".$orario['Riga1'];
		$intestazione['Riga6'] = $orario['Riga2'];

		return $intestazione;
	}

}

class banca extends indirizzo_enti
{
	public $ID;
	public $Denominazione;
	public $Codice_Fiscale;
	public $Partita_Iva;
	public $CC;
	public $CC_Sede;
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
	public $Orario;
	public $Password;
	public $Forma_Giuridica;

	public $Sigla_Forma_Giuridica;

	public $Tipo_Banca;
	public $ID_Collegamento;

	public $Sede_Collegamento;
	public $Prev_Sede;
	public $Next_Sede;
	public $Prev_Filiale;
	public $Next_Filiale;

	public function __construct($progr, $c)
	{
		//echo "DENTRO BANCA P--> ".$progr." --- CC --> ".$c;
		$query = "SELECT * FROM banca WHERE ID = '" . $progr . "' AND CC = '" . $c . "'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = $val['ID'];
		$this->CC = $val['CC'];
		$this->Denominazione = $val['Denominazione'];
		$this->Codice_Fiscale = $val['Codice_Fiscale'];
		$this->Partita_Iva = $val['Partita_Iva'];
		$this->Paese = $val['Paese'];
		$this->Comune = $val['Comune'];
		$this->CC_Sede = $val['CC_Sede'];
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
		$this->Orario = $val['Orario'];
		$this->Tipo_Banca = $val['Tipo_Banca'];
		$this->Password = $val['Password'];
		$this->Forma_Giuridica = utf8_decode($val['Forma_Giuridica']);

		$forma_giuridica = new forma_giuridica($val['Forma_Giuridica']);
		$this->Sigla_Forma_Giuridica = $forma_giuridica->Sigla;

		$this->ID_Collegamento = $val['ID_Collegamento'];
		if ($this->ID_Collegamento != null) {
			$this->Sede_Collegamento = new banca($this->ID_Collegamento, $c);
		}

		if ($progr==0)
		{
			$query_next = "SELECT ID FROM banca WHERE Tipo_Banca = 'filiale' ORDER BY ID ASC LIMIT 1";
			$this->Next_Filiale = single_answer_query($query_next);
			$query_next = "SELECT ID FROM banca WHERE Tipo_Banca = 'sede' ORDER BY ID ASC LIMIT 1";
			$this->Next_Sede = single_answer_query($query_next);

			$query_prev = "SELECT ID FROM banca WHERE Tipo_Banca = 'filiale' ORDER BY ID DESC LIMIT 1";
			$this->Prev_Filiale = single_answer_query($query_prev);
			$query_prev = "SELECT ID FROM banca WHERE Tipo_Banca = 'sede' ORDER BY ID DESC LIMIT 1";
			$this->Prev_Sede = single_answer_query($query_prev);

		}
		else
		{
			$query_next = "SELECT ID FROM banca WHERE ID > '" . $progr . "' AND Tipo_Banca = 'filiale' ORDER BY ID";
			$this->Next_Filiale = single_answer_query($query_next);
			$query_next = "SELECT ID FROM banca WHERE ID > '" . $progr . "' AND Tipo_Banca = 'sede' ORDER BY ID";
			$this->Next_Sede = single_answer_query($query_next);

			$query_prev = "SELECT ID FROM banca WHERE ID < '" . $progr . "' AND Tipo_Banca = 'filiale' ORDER BY ID DESC";
			$this->Prev_Filiale = single_answer_query($query_prev);
			$query_prev = "SELECT ID FROM banca WHERE ID < '" . $progr . "' AND Tipo_Banca = 'sede' ORDER BY ID DESC";
			$this->Prev_Sede = single_answer_query($query_prev);
		}
	}

	public function trovaSedeDaFiliale($codiceCatastale, $cap = null)
	{
		$where = "CC_Sede = '" . $codiceCatastale . "' AND Tipo_Banca = 'filiale' AND ( ( ID_Collegamento > 0 AND Denominazione NOT LIKE 'POSTE ITALIANE%'";
		if($cap!=null)
			$where.= " AND Cap = '" . $cap . "'";
		$where.= " ) OR ( Denominazione LIKE 'POSTE ITALIANE%' ) )";

		$array_sedi = select_mysql_array("ID_Collegamento" , "banca", $where, 'Denominazione', 'ASC', 'si');

		return $array_sedi;

	}

	public function trovaFilialiDaSede($id_sede = null)
	{
		if($id_sede == null)
			$id_sede = $this->ID;

		$where = "Tipo_Banca = 'filiale' AND ID_Collegamento = ".$id_sede;

		$array_filiali = select_mysql_array("ID" , "banca", $where, 'Denominazione', 'ASC', 'si');

		return $array_filiali;

	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if(gettype($value)!= "array")
			{
				if ($key != "ID" && isset($value) != false && $key != "Sede_Collegamento" && $key != "Sigla_Forma_Giuridica")
				{
					if ($key != "Next_Filiale" && $key != "Prev_Filiale" && $key != "Next_Sede" && $key != "Prev_Sede")
					{
						$fields[] = $key;
						$values[] = utf8_decode($value);
					}
				}
			}
		}

		$query = table_insert_record_query ("banca", $fields, $values);
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
				if ($key != $campo && isset($value) != false && $key != "Sede_Collegamento" && $key != "Sigla_Forma_Giuridica")
				{
					if ($key != "Next_Filiale" && $key != "Prev_Filiale" && $key != "Next_Sede" && $key != "Prev_Sede")
					{
						$fields[] = $key;
						$values[] = utf8_decode($value);
					}
				}
			}
		}

		$query = table_update_record_query ("banca", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

// class banca
// {
// 	public $ID;
// 	public $Tipo;
// 	public $Denominazione;
// 	public $Codice_Fiscale;
// 	public $Partita_Iva;
// 	public $CC;
// 	public $CC_Sede;
// 	public $Paese;
// 	public $Comune;
// 	public $Frazione;
// 	public $Provincia;
// 	public $Cap;
// 	public $Toponimo;
// 	public $Civico;
// 	public $Esponente;
// 	public $Interno;
// 	public $Dettagli;
// 	public $Telefono;
// 	public $Fax;
// 	public $Mail;
// 	public $PEC;
// 	public $Sito;
// 	public $Orario;

// 	public $Tipo_Banca;
// 	public $ID_Collegamento;
// 	public $Sede_Collegamento;

// 	public function __construct($db_user='root',$db_password= '', $db_database='gitco2', $db_host='localhost')
// 	{
// 		$this->user = $db_user;
// 		$this->password = $db_password;
// 		$this->database = $db_database;
// 		$this->host = $db_host;
// 	}

// 	protected function connect(){
// 			return new mysqli($this->host,$this->user,$this->password,$this->database);
// 	}


// 	public function getRows($parent = Null){
// 		$DB = $this->connect();

// 		if($parent) $query = "SELECT * FROM banca WHERE Id_Collegamento = '" . $parent . "';";
// 		else $query = "SELECT * FROM banca WHERE Id_Collegamento IS NULL";

// 		return $DB->query($query);

// 	}
// }

class stato_estero
{
	public $CC_Paese_Estero;
	public $Nome;

	public function __construct ($CodiceCatastale)
	{
		$query = "SELECT * FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$CodiceCatastale."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->CC_Paese_Estero = $val['CC_Paese_Estero'];
		$this->Nome = $val['Nome'];
	}

    public function trovaCC($nome_stato)
    {
        $query = "SELECT * FROM paesi_esteri_lista WHERE Nome = \"".ucwords($nome_stato)."\" ";
        $result = safe_query($query);
        $a_stati = mysql_fetch_array($result);

        return $a_stati;
    }
}

class ufficio_comune extends indirizzo_enti
{
	public $ID;
	public $CC;
	public $CC_Comune;
	public $Tipo;
	public $Denominazione;
	public $Partita_Iva;
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
	public $Orario;
	public $Mail;
	public $PEC;
	public $Sito;
	public $Modalita_Invio;

	public $IndirizzoCompleto;

	public function __construct( $progr )
	{

		$query = "SELECT * FROM ufficio_comune WHERE ID = '" . $progr . "' ";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = $val['ID'];
		$this->CC = $val['CC'];
		$this->CC_Comune = $val['CC_Comune'];
		$this->Tipo = $val['Tipo'];
		$this->Denominazione = $val['Denominazione'];
		$this->Partita_Iva = $val['Partita_Iva'];
		$this->Paese = $val['Paese'];
		$this->Comune = $val['Comune'];
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
		$this->Orario = $val['Orario'];
		$this->Mail = $val['Mail'];
		$this->PEC = $val['PEC'];
		$this->Sito = $val['Sito'];
		$this->Modalita_Invio = $val['Modalita_Invio'];

		$this->IndirizzoCompleto = $this->Toponimo;
		if ($this->Civico != "") $this->IndirizzoCompleto .= " " . $this->Civico;
		if ($this->Esponente != "") $this->IndirizzoCompleto .= " " . $this->Esponente;
		if ($this->Interno != "") $this->IndirizzoCompleto .= " /" . $this->Interno;

	}

	public function trova_ufficio($c , $tipo_ufficio)
	{
		$query = "SELECT ID FROM ufficio_comune WHERE Tipo ='".$tipo_ufficio."' AND CC = '".$c."'";
		$result = safe_query($query);
		$num_record = mysql_num_rows($result);
		$val = mysql_fetch_array($result);

		if($num_record!=1)
			return $num_record." ";
		else
			return "ID ".$val['ID'];

	}

	public function Delete ()
	{
		$query = "DELETE FROM ufficio_comune WHERE ID = '".$this->ID."' ";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false && $key != "IndirizzoCompleto")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("ufficio_comune", $fields, $values);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}

	public function Update ( $valoreCampo, $campo = "ID" )
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false && is_array($value)===false && $key != "IndirizzoCompleto")
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_update_record_query ("ufficio_comune", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}
