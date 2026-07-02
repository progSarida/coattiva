<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

//*****************************************************************
// Classe targhe_estere_pagamenti; 	                           	  *
// Crea un oggetto utente con i dati relativi                     *
//*****************************************************************

/*
 * CREATE TABLE `targhe_estere_pagamenti` (
  `Pag_Progr` int(9) unsigned NOT NULL auto_increment,
  `Pag_Comune_CC` varchar(5) NOT NULL default '',
  `Pag_Notifica` int(9) unsigned NOT NULL default '0',
  `Pag_Registro` int(9) unsigned NOT NULL default '0',
  `Pag_Anno` year(4) NOT NULL default '0000',
  `Pag_Trasgressore` varchar(40) NOT NULL default '',
  `Pag_Tipo_Pag` varchar(10) NOT NULL default '',
  `Pag_Metodo_Importazione` varchar(100) NOT NULL default '',
  `Pag_Sollecito` enum('Y','N') NOT NULL default 'N',
  `Pag_Data_Pag` date NOT NULL default '0000-00-00',
  `Pag_Importo_Pag` float unsigned NOT NULL default '0',
  `Pag_Numero_Rata` varchar(10) NOT NULL default '',
  
  `Pag_Quietanza` varchar(20) NOT NULL default '',
  `Pag_Bollettario` varchar(20) NOT NULL default '',
  `Pag_Note` text,
  
  `Pag_Immagine` varchar(100) NOT NULL default '',
  `Pag_Quinto_Campo` varchar(50) NOT NULL default '',
  `Pag_Blocco_Riscossione` text,
  
  `Pag_Data_Reg` date NOT NULL default '0000-00-00',
  `Pag_Ora_Registrazione` time NOT NULL default '00:00:00',
  `Pag_Operatore` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`Pag_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
  
  
  `Pag_Identificazione` enum('True','False') NOT NULL default 'True',
  `Pag_Spese_Notifica` float unsigned NOT NULL default '0',
  `Pag_Spese_Ricerca` float unsigned NOT NULL default '0',
  `Pag_Interessi` float NOT NULL default '0',
  `Pag_Arrotondamento` int(9) NOT NULL default '0',
  `Pag_Differenza` float unsigned NOT NULL default '0',
  `Pag_Imp_Verbale` float unsigned NOT NULL default '0',
  `Pag_Imp_Calcolato` float unsigned NOT NULL default '0',
  `Pag_Situazione` int(9) NOT NULL default '0',
  `Pag_Controllo` int(9) NOT NULL default '0',
  `Pag_Tipo_Versamento` enum('volontario','contenzioso','ruolo') NOT NULL default 'volontario',
  `Pag_Libero` enum('Y','N') NOT NULL default 'N',
  `Pag_Fonte_Dati` enum('M','A') NOT NULL default 'M',
  `Pag_Canale_Telematico` enum('N','Y') NOT NULL default 'N',
  `Pag_Targa_Veicolo` varchar(20) NOT NULL default '',
  `Pag_Numero_Avviso` int(9) NOT NULL default '0',
  `Pag_Numero_Preavviso` int(9) NOT NULL default '0',
  `Pag_Inserimento_Libero_Pagamento` char(2) NOT NULL default '',
  `Pag_Veicolo_Intestato` varchar(50) NOT NULL default '',
  `Pag_Data_Accertamento` date NOT NULL default '0000-00-00',
  `Pag_Progr_Ricevuta` int(9) NOT NULL default '0',
  `Pag_Stampa_Ricevuta` varchar(15) NOT NULL default '',
  `Pag_Numero_Cronologico` int(9) NOT NULL default '0',
  `Pag_Numero` int(9) NOT NULL default '0',
  `Pag_Quinto_Campo` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`Pag_Progr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 */
/*
CREATE TABLE `pagamenti_volontari_cds` (
`Pag_Progr` int(9) unsigned NOT NULL auto_increment,
`Pag_Registro` int(9) unsigned NOT NULL default '0',
`Pag_Anno` year(4) NOT NULL default '0000',
`Pag_Trasgressore` varchar(40) NOT NULL default '',
`Pag_Data_Reg` date NOT NULL default '0000-00-00',
`Pag_Tipo_Pag` enum('BPL','BOL','CC','NUM','TER','BAN','POS','VAG','CON','BRE','BGSG') default NULL,
`Pag_Quietanza` varchar(20) NOT NULL default '',
`Pag_Data_Pag` date NOT NULL default '0000-00-00',
`Pag_Importo_Pag` float unsigned NOT NULL default '0',
`Pag_Identificazione` enum('True','False') NOT NULL default 'True',
`Pag_Spese_Notifica` float unsigned NOT NULL default '0',
`Pag_Interessi` float NOT NULL default '0',
`Pag_Arrotondamento` int(9) NOT NULL default '0',
`Pag_Differenza` float unsigned NOT NULL default '0',
`Pag_Imp_Verbale` float unsigned NOT NULL default '0',
`Pag_Spese_Ricerca` float unsigned NOT NULL default '0',
`Pag_Bollettario` varchar(20) NOT NULL default '',
`Pag_Imp_Calcolato` float unsigned NOT NULL default '0',
`Pag_Situazione` int(9) NOT NULL default '0',
`Pag_Controllo` int(9) NOT NULL default '0',
`Pag_Note` text,
`Pag_Tipo_Versamento` enum('volontario','contenzioso','ruolo') NOT NULL default 'volontario',
`Pag_Libero` enum('Y','N') NOT NULL default 'N',
`Pag_Ora_Registrazione` time NOT NULL default '00:00:00',
`Pag_Fonte_Dati` enum('M','A') NOT NULL default 'M',
`Pag_Operatore` varchar(30) NOT NULL default '',
`Pag_Canale_Telematico` enum('N','Y') NOT NULL default 'N',
`Pag_Immagine` varchar(100) NOT NULL default '',
`Pag_Sollecito` enum('Y','N') NOT NULL default 'N',
`Pag_Targa_Veicolo` varchar(20) NOT NULL default '',
`Pag_Numero_Avviso` int(9) NOT NULL default '0',
`Pag_Numero_Preavviso` int(9) NOT NULL default '0',
`Pag_Inserimento_Libero_Pagamento` char(2) NOT NULL default '',
`Pag_Veicolo_Intestato` varchar(50) NOT NULL default '',
`Pag_Data_Accertamento` date NOT NULL default '0000-00-00',
`Pag_Progr_Ricevuta` int(9) NOT NULL default '0',
`Pag_Stampa_Ricevuta` varchar(15) NOT NULL default '',
`Pag_Numero_Cronologico` int(9) NOT NULL default '0',
`Pag_Numero` int(9) NOT NULL default '0',
`Pag_Quinto_Campo` varchar(50) NOT NULL default '',
PRIMARY KEY  (`Pag_Progr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
*/
class targhe_estere_pagamenti
{
	public $Pag_Progr;
	public $Pag_Comune_CC;
	public $Pag_Notifica;
	public $Pag_Registro;
	public $Pag_Anno;
	public $Pag_Trasgressore;
	public $Pag_Riscossore;
	public $Pag_Tipo_Pag;  //  nel vecchio era enum('BPL','BOL','CC','NUM','TER','BAN','POS','VAG','CON','BRE','BGSG') default NULL,
	public $Pag_Categoria_Pag;  //  VOLONTARIO o COATTIVO
	public $Pag_Metodo_Importazione;  //  MANUALE, AUTOMATICO, BONIFICA
	public $Pag_Sollecito;  //  Y o N
	public $Pag_Data_Pag;
	public $Pag_Importo_Pag;
	public $Pag_Numero_Rata;
	  
	public $Pag_Quietanza;
	public $Pag_Bollettario;
	public $Pag_Note;
	  
	public $Pag_Immagine;
	public $Pag_Quinto_Campo;
	
	public $Pag_Blocco_Riscossione;
	  
	public $Pag_Data_Reg;
	public $Pag_Ora_Registrazione;
	public $Pag_Operatore;
	
	// campi NON DA TABELLA
	public $Pag_Numero_Verbale;
	public $Pag_Anno_Verbale;
	public $Pag_Verbale;
	public $Pag_Nome_Comune;
	
	
	public function __construct( $progr = NULL )
	{
		if ($progr == NULL) return;
		
		$queryPag = "SELECT * FROM targhe_estere_pagamenti WHERE Pag_Progr = '" . $progr . "' ";
		$resultPag = safe_query($queryPag);		
		$rigaPag = mysql_fetch_assoc($resultPag);
		
		foreach ($rigaPag as $key => $value)
		{
			$this->$key = $value;
		}
		
		/*$myNotifica = new targhe_estere_notifiche($this->Pag_Notifica);
		//$this->Pag_Anno = $myVerbale->Reg_Anno;
		//$this->Pag_Registro = $myNotifica->Verbale_ID;
		$this->Pag_Numero_Verbale = $myNotifica->Coll_Verbale->Reg_Progr_Registro;
		$this->Pag_Anno_Verbale = $myNotifica->Coll_Verbale->Reg_Anno;
		$this->Pag_Verbale = $myNotifica->Coll_Verbale->Reg_Progr_Registro . "/" . $myNotifica->Coll_Verbale->Reg_Anno;
			
		if ($this->Pag_Anno == NULL) $this->Pag_Anno = $this->Pag_Anno_Verbale;
			
		$myComune = new ente_gestito($myNotifica->Coll_Verbale->Reg_Comune_Violazione);
		$this->Pag_Comune_CC = $myComune->CC;
		$this->Pag_Nome_Comune = $myComune->Nome;*/
		
		$myRegistro = new registro_cronologico_cds($this->Pag_Registro);
		//$this->Pag_Anno = $myVerbale->Reg_Anno;
		//$this->Pag_Registro = $myNotifica->Verbale_ID;
		$this->Pag_Numero_Verbale = $myRegistro->Reg_Progr_Registro;
		$this->Pag_Anno_Verbale = $myRegistro->Reg_Anno;
		$this->Pag_Verbale = $myRegistro->Reg_Progr_Registro . "/" . $myRegistro->Reg_Anno;
			
		if ($this->Pag_Anno == NULL) $this->Pag_Anno = $this->Pag_Anno_Verbale;
			
		$myComune = new ente_gestito($myRegistro->Reg_Comune_Violazione);
		$this->Pag_Comune_CC = $myComune->CC;
		$this->Pag_Nome_Comune = $myComune->Nome;
	}
	
	function PagamentoGiaPresente ()
	{
		if ($this->Pag_Notifica == 0 && $this->Pag_Registro == 0)
		{
			// caso di pagamento da associare
			return null;
			/*$queryCerca = "SELECT Pag_Progr FROM targhe_estere_pagamenti ";
			$queryCerca .= "WHERE Pag_Comune_CC = '" . $this->Pag_Comune_CC . "' ";
			$queryCerca .= "AND Pag_Data_Pag = '" . $this->Pag_Data_Pag . "' ";
			$queryCerca .= "AND Pag_Importo_Pag = '" . $this->Pag_Importo_Pag . "' ";
			$queryCerca .= "AND Pag_Numero_Rata = '" . $this->Pag_Numero_Rata . "' ";*/
		}
		else 
		{
			// caso di pagamento associato a verbale
			$queryCerca = "SELECT Pag_Progr FROM targhe_estere_pagamenti ";
			$queryCerca .= "WHERE Pag_Comune_CC = '" . $this->Pag_Comune_CC . "' ";
			$queryCerca .= "AND Pag_Notifica = '" . $this->Pag_Notifica . "' ";
			$queryCerca .= "AND Pag_Registro = '" . $this->Pag_Registro . "' ";
			$queryCerca .= "AND Pag_Numero_Rata = '" . $this->Pag_Numero_Rata . "' ";
		}
		
		$resCerca = mysql_query($queryCerca);
		//echo ("<br>" . $queryCerca);
		$rigaCerca = mysql_fetch_assoc($resCerca);
		return $rigaCerca['Pag_Progr'];
	}
	
	function PagamentoDaVerbale ($progrVerbale)
	{
		$queryCerca = "SELECT Pag_Progr FROM targhe_estere_pagamenti ";
		$queryCerca .= "WHERE Pag_Registro = '" . $progrVerbale . "' ";
		
		$resCerca = mysql_query($queryCerca);
		//echo ("<br>" . $queryCerca);
		$numCerca = mysql_num_rows($resCerca);
		
		if ($numCerca != 0)
		{
			$arrayRisposta = array();
			while ($rigaCerca = mysql_fetch_assoc($resCerca))
			{
				$arrayRisposta[] = $rigaCerca['Pag_Progr'];
			}
			return $arrayRisposta;
		}
		
		return null;
	}
	
	function PagamentoBloccato ($progrVerbale)
	{
		$queryCerca = "SELECT Pag_Blocco_Riscossione FROM targhe_estere_pagamenti ";
		$queryCerca .= "WHERE Pag_Registro = '" . $progrVerbale . "' ";
		
		$resCerca = mysql_query($queryCerca);
		//echo ("<br>" . $queryCerca);
		$numCerca = mysql_num_rows($resCerca);
		
		$pagamentoBloccato = false;
		
		if ($numCerca != 0)
		{
			while ($rigaCerca = mysql_fetch_assoc($resCerca))
			{
				if ($rigaCerca['Pag_Blocco_Riscossione'] != "")
				{
					$pagamentoBloccato = true;
					return $pagamentoBloccato;
				}
			}
		}
		
		return $pagamentoBloccato;
	}
	
	/*function AssociaPagamento ()
	{
		if ($this->Pag_Notifica != NULL)
		{
			$myNotifica = new targhe_estere_notifiche($this->Pag_Notifica);
			//$this->Pag_Anno = $myVerbale->Reg_Anno;
			$this->Pag_Registro = $myNotifica->Verbale_ID;
			$this->Pag_Numero_Verbale = $myNotifica->Coll_Verbale->Reg_Progr_Registro;
			$this->Pag_Anno_Verbale = $myNotifica->Coll_Verbale->Reg_Anno;
			$this->Pag_Verbale = $myNotifica->Coll_Verbale->Reg_Progr_Registro . "/" . $myNotifica->Coll_Verbale->Reg_Anno;
			
			if ($this->Pag_Anno == NULL) $this->Pag_Anno = $this->Pag_Anno_Verbale;
			
			$myComune = new ente_gestito($myNotifica->Coll_Verbale->Reg_Comune_Violazione);
			$this->Pag_Comune_CC = $myComune->CC;
			$this->Pag_Nome_Comune = $myComune->Nome;
		}
	}*/
	
	function ImportoGlobaleGiaPagato ()
	{
		if ($this->Pag_Registro == null) return 0;
		$queryTutti = "SELECT SUM(Pag_Importo_Pag) as SOMMA FROM targhe_estere_pagamenti ";
		$queryTutti .= "WHERE Pag_Comune_CC = '" . $this->Pag_Comune_CC . "' ";
		$queryTutti .= "AND Pag_Registro = '" . $this->Pag_Registro . "' ";
		
		$resTutti = mysql_query($queryTutti);
		//echo ("<br>" . $queryCerca);
		$rigaTutti = mysql_fetch_assoc($resTutti);
		return $rigaTutti['SOMMA'];
	}
	
	function TrovoDataPrimoPagamento ()
	{
		if ($this->Pag_Registro == null) return "0000-00-00";
		$queryTutti = "SELECT MIN(Pag_Data_Pag) as PRIMO FROM targhe_estere_pagamenti ";
		$queryTutti .= "WHERE Pag_Comune_CC = '" . $this->Pag_Comune_CC . "' ";
		$queryTutti .= "AND Pag_Registro = '" . $this->Pag_Registro . "' ";
		
		$resTutti = mysql_query($queryTutti);
		//echo ("<br>" . $queryCerca);
		$rigaTutti = mysql_fetch_assoc($resTutti);
		return $rigaTutti['PRIMO'];
	}
	
	function TrovoDataUltimoPagamento ()
	{
		if ($this->Pag_Registro == null) return "0000-00-00";
		$queryTutti = "SELECT MAX(Pag_Data_Pag) as ULTIMO FROM targhe_estere_pagamenti ";
		$queryTutti .= "WHERE Pag_Comune_CC = '" . $this->Pag_Comune_CC . "' ";
		$queryTutti .= "AND Pag_Registro = '" . $this->Pag_Registro . "' ";
		
		$resTutti = mysql_query($queryTutti);
		//echo ("<br>" . $queryCerca);
		$rigaTutti = mysql_fetch_assoc($resTutti);
		return $rigaTutti['ULTIMO'];
	}
	
	function SelectTipiPag ($optionRisc, $optionSel)
	{
		$selBPL = $selBGSG = $selCC = $selASSEGNO = $selBOLLETTA = "";
		$selBANCOMAT = $selPOS = $selVAGLIA = $selPAYPAL = $selCONTANTI = "";
		switch ($optionSel)
		{
			case "BPL": $selBPL = " selected "; break;
			case "BGSG": $selBGSG = " selected "; break;
			case "CC": $selCC = " selected "; break;
			case "ASSEGNO": $selASSEGNO = " selected "; break;
			case "BOLLETTA": $selBOLLETTA = " selected "; break;
			case "BANCOMAT": $selBANCOMAT = " selected "; break;
			case "POS": $selPOS = " selected "; break;
			case "VAGLIA": $selVAGLIA = " selected "; break;
			case "PAYPAL": $selPAYPAL = " selected "; break;
			case "CONTANTI": $selCONTANTI = " selected "; break;
		}
		//switch ($this->Pag_Tipo_Pag;  //  nel vecchio era enum('BPL','BOL','CC','NUM','TER','BAN','POS','VAG','CON','BRE','BGSG') default NULL,
		$select = "<select id='pag_tipo_pag'>";
		$select .= "<option value=''></option>";
		switch ($optionRisc)
		{
			case "SARIDA":
				$select .= "<option value='BPL' $selBPL>BPL</option>";
				$select .= "<option value='BGSG' $selBGSG>BGSG</option>";
				$select .= "<option value='CC' $selCC>C/C POST</option>";
				$select .= "<option value='ASSEGNO' $selASSEGNO>ASSEGNO</option>";
				$select .= "<option value='BOLLETTA' $selBOLLETTA>BOLLETTA</option>";
				$select .= "<option value='BANCOMAT' $selBANCOMAT>BANCOMAT</option>";
				$select .= "<option value='POS' $selPOS>POS</option>";
				$select .= "<option value='VAGLIA' $selVAGLIA>VAGLIA</option>";
				$select .= "<option value='PAYPAL' $selPAYPAL>PAYPAL</option>";
				break;
			case "COMUNE":
				$select .= "<option value='CC' $selCC>C/C POST</option>";
				$select .= "<option value='ASSEGNO' $selASSEGNO>ASSEGNO</option>";
				$select .= "<option value='BOLLETTA' $selBOLLETTA>BOLLETTA</option>";
				$select .= "<option value='BANCOMAT' $selBANCOMAT>BANCOMAT</option>";
				$select .= "<option value='POS' $selPOS>POS</option>";
				$select .= "<option value='VAGLIA' $selVAGLIA>VAGLIA</option>";
				$select .= "<option value='PAYPAL' $selPAYPAL>PAYPAL</option>";
				$select .= "<option value='CONTANTI' $selCONTANTI>CONTANTI</option>";
				break;
		}
		$select .= "</select>";
		return $select;
	}
	
	function LettereMaiuscole ($testo)
	{
		$nuovotesto = "";
		$diffMaiusMinus = 97 - 65;// - 1;
		//alert ($diffMaiusMinus);
		for ($i = 0; $i < strlen($testo); $i++)
		{
			$carattere = substr($testo, $i, 1);
			if ($carattere >= 'a' && $carattere <= 'z') $carattere = strtoupper($carattere);
			$nuovotesto .= $carattere;
		}
		return $nuovotesto;
	}
	
	public function ScorporoPagamento ()
	{
		$mioVerbale = new registro_cronologico_cds($this->Pag_Registro);
		
		$scorpImporto = $this->Pag_Importo_Pag;
		$scorpRicercaComune = 0;
		$scorpNotificaComune = 0;
		$scorpRicercaSarida = 0;
		$scorpNotificaSarida = 0;
	
		// dall'importo pagato: prima "copro" le spese di ricerca comune
		// poi copro le spese di notifica comune
		// poi copro le spese di ricerca sarida
		// poi copro le spese di notifica sarida
		// infine il restante lo uso per coprire il tributo
	
		$queryRatePrec = "SELECT * FROM targhe_estere_pagamenti ";
		$queryRatePrec .= "WHERE Pag_Registro = " . $this->Pag_Registro . " AND ";
		//$queryRatePrec .= "Partita_ID = " . $this->Partita_ID . " AND ";
		$queryRatePrec .= "Pag_Numero_Rata <= " . $this->Pag_Numero_Rata;
		$queryRatePrec .= " ORDER BY Pag_Data_Pag ASC, Pag_Progr ASC";
		$resRatePrec = mysql_query($queryRatePrec);
		//echo "<br>" . $queryRatePrec . " ---- " . mysql_num_rows($resRatePrec);
		
		//$interessiTot = $mioAtto->Interessi + $mioAtto->Interessi_Precedenti;
		$ricercaTotComune = $mioVerbale->Reg_Spese_Ricerca_Comune;
		$notificaTotComune = $mioVerbale->Reg_Spese_Notifica_Comune;
		$ricercaTotSarida = $mioVerbale->Reg_Spese_Ricerca_Sarida;
		$notificaTotSarida = $mioVerbale->Reg_Spese_Notifica_Sarida;
		
		$pagatoConQuestaRata = 0;
		$rcPagata = 0;
		$ncPagata = 0;
		$rsPagata = 0;
		$nsPagata = 0;
		$rcPrimaPagata = 0;
		$ncPrimaPagata = 0;
		$rsPrimaPagata = 0;
		$nsPrimaPagata = 0;
		$cfrtRC = 0;
		$cfrtNC = 0;
		$cfrtRS = 0;
		$cfrtNS = 0;
		$ccccc = 0;
		
		while ($rigaRataPrec = mysql_fetch_assoc($resRatePrec))
		{
			$ccccc++;
			$rcPrimaPagata = $rcPagata;
			$ncPrimaPagata = $ncPagata;
			$rsPrimaPagata = $rsPagata;
			$nsPrimaPagata = $nsPagata;
			$importoRata = $rigaRataPrec['Pag_Importo_Pag'];
			$pagatoConQuestaRata += $importoRata;
			
			echo "<br><br>RATA $ccccc";
			
			echo "<br>RIC COM PAGATA $rcPagata";
			echo "<br>NOT COM PAGATA $ncPagata";
			echo "<br>RIC SAR PAGATA $rsPagata";
			echo "<br>NOT SAR PAGATA $nsPagata";
			
			$cfrtRC = $ricercaTotComune - $rcPrimaPagata;
			$cfrtNC = $notificaTotComune - $ncPrimaPagata;
			$cfrtRS = $ricercaTotSarida - $rsPrimaPagata;
			$cfrtNS = $notificaTotSarida - $nsPrimaPagata;
			
			/*if ($_SESSION['CC_User'] == "***+")
			{
				echo "<br><br>" . $queryRatePrec . " ---- " . mysql_num_rows($resRatePrec);
				echo "<br>" . $rsPrimaPagata . " e " . $nsPrimaPagata;
			}*/
			
			if ($rcPagata < $cfrtRC)
			{
				$rcPagata += $importoRata;
				if ($rcPagata > $cfrtRC)
				{
					$rcPagata = $cfrtRC;
					$ncPagata = $importoRata - $cfrtRC;
				}
			}
			else
			{
				$rcPagata = $cfrtRC;
				$ncPagata = $importoRata - $cfrtRC;
			}
			
			if ($ncPagata < $cfrtNC)
			{
				if ($ncPagata > $cfrtNC)
				{
					$ncPagata = $cfrtNC;
					$rsPagata = $importoRata - $cfrtRC - $cfrtNC;
				}
			}
			else
			{
				$ncPagata = $cfrtNC;
				$rsPagata = $importoRata - $cfrtRC - $cfrtNC;
			}

			/*if ($_SESSION['CC_User'] == "***+")
			{
				echo "<br>rspagata $rsPagata < $cfrtRS";
			}*/
			if ($rsPagata < $cfrtRS)
			{
				if ($rsPagata > $cfrtRS)
				{
					$rsPagata = $cfrtRS;
					$nsPagata = $importoRata - $cfrtRC - $cfrtNC - $cfrtRS;
				}
			}
			else
			{
				$rsPagata = $cfrtRS;
				$nsPagata = $importoRata - $cfrtRC - $cfrtNC - $cfrtRS;
			}
			/*if ($_SESSION['CC_User'] == "***+")
			{
				echo "<br>rspagata $rsPagata   ,  nspagata $nsPagata < $cfrtNS";
			}*/
			if ($nsPagata < $cfrtNS)
			{
				if ($nsPagata > $cfrtNS)
				{
					$nsPagata = $cfrtNS;
					$residuo = $importoRata - $cfrtRC - $cfrtNC - $cfrtRS - $cfrtNS;
				}
			}
			else
			{
				$nsPagata = $cfrtNS;
				$residuo = $importoRata - $cfrtRC - $cfrtNC - $cfrtRS - $cfrtNS;
			}
			/*if ($_SESSION['CC_User'] == "***+")
			{
				echo "<br>2rspagata $rsPagata   ,  nspagata $nsPagata < $cfrtNS    e residuo $residuo";
			}*/
		}

		if ($_SESSION['CC_User'] == "***+")
		{
			echo "<br><br>RATA $ccccc FINE";
				
			echo "<br>RIC COM PAGATA $rcPagata";
			echo "<br>NOT COM PAGATA $ncPagata";
			echo "<br>RIC SAR PAGATA $rsPagata";
			echo "<br>NOT SAR PAGATA $nsPagata";
			echo "<br>RESIDUO ______ $residuo";
		}
		
		//$pagatoPrimaDiQuestaRata = $pagatoConQuestaRata - $importoRata;
		
		/*$somma1 = $ricercaTotComune;
		$somma2 = $ricercaTotComune + $notificaTotComune;
		$somma3 = $ricercaTotComune + $notificaTotComune + $ricercaTotSarida;
		$somma4 = $ricercaTotComune + $notificaTotComune + $ricercaTotSarida + $notificaTotSarida;*/ 
		
		/*$sommaDif1 = $somma1 - $pagatoPrimaDiQuestaRata;
		$sommaDif2 = $somma2 - $pagatoPrimaDiQuestaRata;
		$sommaDif3 = $somma3 - $pagatoPrimaDiQuestaRata;
		$sommaDif4 = $somma4 - $pagatoPrimaDiQuestaRata;*/
		
		/*if ($pagatoPrimaDiQuestaRata == 0)
		{
			if ($pagatoConQuestaRata < $somma1)
			{
				//echo "<br>1 " . $pagatoConQuestaRata . " < " . $somma1;
				$scorpRicercaComune = $importoRata;
			}
			else if ($pagatoConQuestaRata < $somma2)
			{
				//echo "<br>2 " . $pagatoConQuestaRata . " < " . $somma2;
				$scorpRicercaComune = $ricercaTotComune;
				$scorpNotificaComune = $pagatoConQuestaRata - $somma1;
			}
			else if ($pagatoConQuestaRata < $somma3)
			{
				//echo "<br>3 " . $pagatoConQuestaRata . " < " . $somma3;
				$scorpRicercaComune = $ricercaTotComune;
				$scorpNotificaComune = $notificaTotComune;
				$scorpRicercaSarida = $pagatoConQuestaRata - $somma2;
			}
			else if ($pagatoConQuestaRata < $somma4)
			{
				//echo "<br>4 " . $pagatoConQuestaRata . " < " . $somma4;
				$scorpRicercaComune = $ricercaTotComune;
				$scorpNotificaComune = $notificaTotComune;
				$scorpRicercaSarida = $ricercaTotSarida;
				$scorpNotificaSarida = $pagatoConQuestaRata - $somma3;
			}
			else 
			{
				//echo "<br>8 " . $pagatoConQuestaRata . " > " . $somma7;
				$scorpRicercaComune = $ricercaTotComune;
				$scorpNotificaComune = $notificaTotComune;
				$scorpRicercaSarida = $ricercaTotSarida;
				$scorpNotificaSarida = $notificaTotSarida;
			}
		}
		else*/
		{
			$scorpRicercaComune = $rcPagata;// - $rcPrimaPagata;
			$scorpNotificaComune = $ncPagata;// - $ncPrimaPagata;
			$scorpRicercaSarida = $rsPagata;// - $rsPrimaPagata;
			$scorpNotificaSarida = $nsPagata;// - $nsPrimaPagata;
		}
		//if ($_SESSION['CC_User'] == "***+") alert ($scorpRicerca);
		
		$scorpTributo = $importoRata - $scorpRicercaComune - $scorpNotificaComune - $scorpRicercaSarida - $scorpNotificaSarida;
		
		
		
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
				$mioVerbale->CalcoloImportoTotale(),  //  0
				$scorpImporto,  //  1
				$scorpRicercaComune,  //  2
				$scorpNotificaComune,  //  3
				$scorpRicercaSarida,  //  4
				$scorpNotificaSarida,  //  5
				$scorpTributo  //  6
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
				" (" . $pagatoConQuestaRata . ")",
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
	
	/*public function ScorporoExPagamento ()
	{
		if ($this->Pag_Registro == null) return;
		
		//echo "<br><br>$this->Pag_Registro   $this->Pag_Progr<br><br>";
		$mioVerbale = new registro_cronologico_cds($this->Pag_Registro);
		
		$scorpImporto = $this->Pag_Importo_Pag;
		$scorpRicercaComune = 0;
		$scorpNotificaComune = 0;
		$scorpRicercaSarida = 0;
		$scorpNotificaSarida = 0;
	
		// dall'importo pagato: prima "copro" gli interessi
		// poi copro le spese
		// infine il restante lo uso per coprire il tributo
	
		$queryRatePrec = "SELECT * FROM targhe_estere_pagamenti ";
		$queryRatePrec .= "WHERE Pag_Registro = " . $this->Pag_Registro;  //   . " AND ";
		//$queryRatePrec .= "Partita_ID = " . $this->Partita_ID . " AND ";
		//$queryRatePrec .= "Rata <= " . $this->Rata;
		$queryRatePrec .= " ORDER BY Pag_Data_Pag ASC, Pag_Progr ASC";
		$resRatePrec = mysql_query($queryRatePrec);
		//echo "<br>" . $queryRatePrec . " ---- " . mysql_num_rows($resRatePrec);
		$giaPagato = 0;
		//return;
		
		//$interessiTot = $mioAtto->Interessi + $mioAtto->Interessi_Precedenti;
		$ricercaTotComune = $mioVerbale->Reg_Spese_Ricerca_Comune;
		$notificaTotComune = $mioVerbale->Reg_Spese_Notifica_Comune;
		$ricercaTotSarida = $mioVerbale->Reg_Spese_Ricerca_Sarida;
		$notificaTotSarida = $mioVerbale->Reg_Spese_Notifica_Sarida;
		
		
		// arrivano i pagamenti dello stesso verbale in ordine dal più vecchio al più recente
		// e li sommo fino all'attuale, tralasciando i successivi
		while ($rigaRataPrec = mysql_fetch_assoc($resRatePrec))
		{
			//echo "<br>paggg  " . $rigaRataPrec['Pag_Progr'] . " --> " . $rigaRataPrec['Pag_Importo_Pag'];
			if ($rigaRataPrec['Pag_Progr'] != $this->Pag_Progr)
			{
				$importoRata = $rigaRataPrec['Pag_Importo_Pag'];
				$giaPagato += $importoRata;
			}
			else
			{
				$importoRata = $rigaRataPrec['Pag_Importo_Pag'];
				$giaPagato += $importoRata;
				break;  //  esco dal while quando trovo il pagamento in questione
			}
		}
		$pagatoPrimaDiQuestaRata = $giaPagato - $importoRata;
		
		//echo "<br><br>$pagatoPrimaDiQuestaRata = $giaPagato - $importoRata;<br><br>";
		
		$tempPrima = $pagatoPrimaDiQuestaRata;
		$tempOra = $giaPagato;
		
		$sommo = $notificaTotComune;
		if ($sommo > $tempPrima) $casoMio = "NOTIFICACOMUNE";
		else 
		{
			$sommo += $ricercaTotComune;
			if ($sommo > $tempPrima) $casoMio = "RICERCACOMUNE";
			else 
			{
				$sommo += $notificaTotSarida;
				if ($sommo > $tempPrima) $casoMio = "NOTIFICASARIDA";
				else 
				{
					$sommo += $ricercaTotSarida;
					if ($sommo > $tempPrima) $casoMio = "RICERCASARIDA";
					else 
					{
						$casoMio = "SANZIONE";
					}
				}
			}
		}
		
		//echo "<br>" . $sommo . " contro totale prima " . $tempPrima . " nel caso " . $casoMio;
		
		while ($casoMio != "SANZIONE")
		{
			switch ($casoMio)
			{
				case "NOTIFICACOMUNE":
					if ($sommo < $importoRata)
					{
						$scorpNotificaComune = $sommo;
						$casoMio = "SANZIONE";
					}
					else 
					{
						$scorpNotificaComune = $notificaTotComune;
						$casoMio = "RICERCACOMUNE";
					}
					break;
				case "RICERCACOMUNE":
					if ($sommo < $importoRata)
					{
						$scorpRicercaComune = $sommo;
						$casoMio = "SANZIONE";
					}
					else 
					{
						$scorpRicercaComune = $ricercaTotComune;
						$casoMio = "NOTIFICASARIDA";
					}
					break;
				case "NOTIFICASARIDA":
					if ($sommo < $importoRata)
					{
						$scorpNotificaSarida = $sommo;
						$casoMio = "SANZIONE";
					}
					else 
					{
						$scorpNotificaSarida = $notificaTotSarida;
						$casoMio = "NOTIFICASARIDA";
					}
					break;
				case "RICERCASARIDA":
					if ($sommo < $importoRata)
					{
						$scorpRicercaSarida = $sommo;
						$casoMio = "SANZIONE";
					}
					else 
					{
						$scorpRicercaSarida = $ricercaTotSarida;
						$casoMio = "NOTIFICASARIDA";
					}
					break;
				case "SANZIONE":
					break;
			}
		}

		//echo "<br>residuo dopo ricerca sarida (tutta sanz) " . $residuo;
		
		$scorpTributo = $importoRata - $scorpRicercaComune - $scorpNotificaComune - $scorpRicercaSarida - $scorpNotificaSarida;
		
		$tempDovuto = $mioVerbale->Reg_Importo_Amministrativo;
		
		$arrayScorporo = array
		(
				$tempDovuto,
				$scorpImporto,
				$scorpRicercaComune,
				$scorpNotificaComune,
				$scorpRicercaSarida,
				$scorpNotificaSarida,
				$scorpTributo
		);
		
		if ($_SESSION['CC_User'] == "***+")
		{
			$arrayTesti = array
			(
					'Dovuto     ',
					'Importo    ',
					'RicCom     ',
					'notCom     ',
					'ricsar     ',
					'notcom     ',
					'Tributo    '
			);
				
			$arrayTotali = array
			(
					" (" . $giaPagato . ")",
					"",
					$ricercaTotComune,
					$notificaTotComune,
					$ricercaTotSarida,
					$notificaTotSarida,
					""
			);
				
			echo "<br>";
				
			for ($kkk = 0; $kkk < count($arrayScorporo); $kkk++)
			{
				echo "<br>" . $arrayTesti[$kkk] . " - " . $arrayScorporo[$kkk] . " ( " . $arrayTotali[$kkk] . " )";
			}
		}
		return $arrayScorporo;
	}*/
	
	function InsertUpdatePagamentoEstero ($forzoUpdate = NULL)
	{
		$insUpd = "";
		$fields = array();
		$values = array();
		
		$this->Pag_Data_Reg = date("Y-m-d");
		$this->Pag_Ora_Registrazione = date("H:i:s");
		$this->Pag_Operatore = $_SESSION['username'];
		
		if ($forzoUpdate != NULL)
		{
			$insUpd = $forzoUpdate;
		}
		else 
		{
			//$this->Pag_Progr = $this->PagamentoGiaPresente();
			if ($this->Pag_Progr == NULL)
			{
				/*$queryMax = "SELECT MAX(ID) as mioId FROM targhe_estere_utenti";
				$resMax = mysql_query($queryMax);
				$rigaMax = mysql_fetch_assoc($resMax);
				$this->ID = $rigaMax['mioId'];*/
				if ($this->PagamentoGiaPresente() != "")
					$insUpd = "UPDATE";
				else
					$insUpd = "INSERT";
			}
			else 
			{
				$queryUpd = "SELECT Pag_Progr FROM targhe_estere_pagamenti WHERE Pag_Progr = '$this->Pag_Progr'";
				$resUpd = mysql_query($queryUpd);
				$rigaUpd = mysql_fetch_assoc($resUpd);
				if ($rigaUpd['Pag_Progr'] == NULL)
					$insUpd = "INSERT";
				else
					$insUpd = "UPDATE";
			}
		}
		
		foreach ($this as $campo => $valore)
		{
			if (isset($campo) && $campo != "Pag_Progr" &&
					$campo != "Pag_Numero_Verbale" &&
					$campo != "Pag_Anno_Verbale" &&
					$campo != "Pag_Verbale" &&
					$campo != "Pag_Nome_Comune")
			{
				$fields[] = $campo;
				$values[] = $this->LettereMaiuscole($valore);
			}
		}
		
		$risposta = "";
		if ($insUpd == "INSERT")
		{
			$risposta = $this->insert_pagamento_locale($fields, $values);
			$arrayPag = $this->PagamentoDaVerbale($this->Pag_Registro);
			if ($risposta == true) return "INSERT_OK" . "**" . $arrayPag[0];
			else return "INSERT_ERROR" . "**" . $arrayPag[0];
		}
		else if ($insUpd == "UPDATE")
		{
			$risposta = $this->update_pagamento_locale($this->Pag_Progr, $fields, $values);
			if ($risposta == true) return "UPDATE_OK" . "**" . $this->Pag_Progr;
			else return "UPDATE_ERROR" . "**" . $this->Pag_Progr;
		}
		//alert ($risposta . " e " . $insUpd);
		return $risposta;
	}
	
	public function insert_pagamento_locale($fields_to_insert, $values_to_insert)
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
		$query = "INSERT INTO targhe_estere_pagamenti (" . $clause . ") VALUES (";
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			$clause .= "'" . $values_to_insert[$i] . "'";
			if ($i < $dim1-1) $clause = $clause . ", ";
		}
		$query .= $clause . ")";
	
		return safe_query($query);
	
		echo $query;
	
		return true;
	}
	
	public function update_pagamento_locale($key, $fields_to_update, $values_to_update)
	{
		$dim1 = count($fields_to_update);
		$dim2 = count($values_to_update);
		
		if ($dim1 != $dim2 || $dim1 == 0) return FALSE;
	
		if ($key == 0 || $key == '0' || $key == NULL) return FALSE;
	
		$myOldPag = new targhe_estere_pagamenti($key);
		
		$clause = "";
		for ($i = 0; $i < $dim1; $i++)
		{
			if ($myOldPag->$fields_to_update[$i] != $values_to_update[$i])
			{
				//if ($values_to_update[$i] != NULL)
				//if (isset($fields_to_update[$i]))
				{
					$clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
				}
			}
			//echo ("<br>" . $myOldUtente->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
		}
		//alert ($clause);
		if ($clause == "") return TRUE;  // non updata nulla, perchè sono tutti uguali
	
		$clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "
	
		$query = "UPDATE targhe_estere_pagamenti SET $clause WHERE Pag_Progr = '" . $key . "'";
		//echo "<br>" . $query;
	
		if (safe_query($query) != NULL) return TRUE;
		else return FALSE;
	
		echo $query;
	
		return true;
	}
	
}

?>