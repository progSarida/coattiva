<?php

class FtpAruba
{
	var $myConnessione = NULL;
	var $mySito = "/www.poliziamunicipale-online.it/";
	var $myRegistroFolder = "FileRegistro/";
	var $myRegDocsFolder = "FileDocumenti/";

	function FtpAruba ()
	{
		// passaggio delle foto in FTP
		$ftp_server = "62.149.141.10"; //esempio indirizzo ip del server
		$ftp_username = "4011990@aruba.it";
		$ftp_password = "sarida2016";// stabilisco la connessione al server ftp
		
		$this->myConnessione = ftp_connect($ftp_server); // effetto login sul server
		//alert ($this->myConnessione);
		if ($this->myConnessione == NULL)
		{
			alert ("Errore sulla connessione");
			echo "<br>Connessione FTP fallita!<br>";
			$this->myConnessione = NULL;
		}
		else 
		{
			$login = ftp_login($this->myConnessione, $ftp_username, $ftp_password); // controllo se la connessione ha avuto buon fine
			if ($login == NULL)
			{
				alert ("Errore sul login della connessione");
				echo "<br>Login della connessione FTP fallita!<br>";
				$this->myConnessione = NULL;
			}
			else echo "<br>Connessione FTP riuscita!<br>";
		}
	}
	
	function FileEsisteSuFtp ($nomefile, $comune)
	{
		if ($comune == "")
			$cerca_file_su_aruba = $this->mySito . $nomefile;
		else 
			$cerca_file_su_aruba = $this->mySito . $comune . "/" . $nomefile;
		//alert ($cerca_file_su_aruba);
		$file1EsisteSuFtp = ftp_size($this->myConnessione, $cerca_file_su_aruba);
		if ($file1EsisteSuFtp != -1) // c'è foto sull'FTP
		{
			return "http:/" . $cerca_file_su_aruba;
		}
		else return "";
	}
	
	function CartellaEsisteSuFtp ($comune)
	{
		$cerca_cartella_su_aruba = $this->mySito . $comune;
		if ($this->ftp_is_dir ($this->myConnessione, $cerca_cartella_su_aruba)) // c'è cartella sull'FTP
		{
			return $cerca_cartella_su_aruba;
		}
		else return "";
	}
	
	function TravasaFileSuFtp ($nomefile, $percorsofile, $nomeDestFtp, $comune, $data, $fotoDoc)  //  FOTO  o  DOC
	{
		if ($fotoDoc == "FOTO")  //  foto
		{
			//$dividi_valori = explode ("_", $nomefile);
			//$matricola = $dividi_valori[0];
			//$datatemp = $dividi_valori[1];
			//$ora = $dividi_valori[2];
			//$data = "20" . substr($datatemp, 0, 2) . "-" . substr($datatemp, 2, 2) . "-" . substr($datatemp, 4, 2);
			
			$linkFotCds = /*$_SERVER['DOCUMENT_ROOT'] .*/ "/FotoTargheEstere/" . $comune . "/" . $data . "/" . $nomefile;
		}
		else   //  documento
		{
			$linkFotCds = /*$_SERVER['DOCUMENT_ROOT'] .*/ "/DocsTargheEstere/" . $comune . "/" . $data . "/" . $nomefile;
		}
		
		if ($this->myConnessione == NULL) return "";
		$dove_mettere_su_aruba = $this->mySito . $comune . "/" . $nomeDestFtp;
		$fileEsistenteSuFtp = $this->FileEsisteSuFtp ($nomeDestFtp, $comune);
		if ($fileEsistenteSuFtp == "")  // non c'è ancora
		{
			//echo "<br> = ftp_put ($dove_mettere_su_aruba, $percorsofile, FTP_BINARY);";
			$upFtpload = ftp_put ($this->myConnessione, $dove_mettere_su_aruba, $percorsofile, FTP_BINARY);
			if (!$upFtpload) {
				echo "Si è verificato un errore durante il caricamento di $nomefile!<br>";
			}
			else
			{
				$this->CancellaFotoInLocale ($linkFotCds, 1);
				return $dove_mettere_su_aruba;
			}
		}
		else
		{
			echo "La foto $nomefile è già presente sul server esterno!<br>";
			return "OK";
		}
		return "";
	}
	
	function TravasaRegistroSuFtp ($nomefile, $percorsofile)
	{
		if ($this->myConnessione == NULL) return "";
		$dove_mettere_su_aruba = $this->mySito . $this->myRegistroFolder . $nomefile;
		$fileEsistenteSuFtp = $this->FileEsisteSuFtp ($nomefile, "");
		$upFtpload = ftp_put ($this->myConnessione, $dove_mettere_su_aruba, $percorsofile, FTP_BINARY);
		if (!$upFtpload) {
			echo "Si è verificato un errore durante il caricamento di $nomefile!<br>";
			return false;
		}
		return true;
	}
	
	function TravasaRegDocsSuFtp ($nomefile, $percorsofile)
	{
		if ($this->myConnessione == NULL) return "";
		$dove_mettere_su_aruba = $this->mySito . $this->myRegDocsFolder . $nomefile;
		$fileEsistenteSuFtp = $this->FileEsisteSuFtp ($nomefile, "");
		$upFtpload = ftp_put ($this->myConnessione, $dove_mettere_su_aruba, $percorsofile, FTP_BINARY);
		if (!$upFtpload) {
			echo "Si è verificato un errore durante il caricamento di $nomefile!<br>";
			return false;
		}
		return true;
	}
	/*
	function LinkFotoFtpOppureFotocds ($nomefile, $comune, $data, $fotoDoc)  //  FOTO  o  DOC
	{
		if ($nomefile == "") return "";
		
		if ($fotoDoc == "FOTO")  //  foto
		{
			//$dividi_valori = explode ("_", $nomefile);
			//$matricola = $dividi_valori[0];
			//$datatemp = $dividi_valori[1];
			//$ora = $dividi_valori[2];
			//$data = "20" . substr($datatemp, 0, 2) . "-" . substr($datatemp, 2, 2) . "-" . substr($datatemp, 4, 2);
				
			$linkFotCds =  "/FotoTarghEstere/" . $comune . "/" . $data . "/" . $nomefile;
		}
		else   //  documento
		{
			$linkFotCds =  "/DocsTargeEstere/" . $comune . "/" . $data . "/" . $nomefile;
		}
		
		if ($this->myConnessione != NULL)
		{
			if ($this->CartellaEsisteSuFtp($comune) != "")
			{
				$linkFtp = $this->FileEsisteSuFtp($nomefile, $comune);
				if ($linkFtp != "")
				{
					// se arrivo qui vuol dire che ho trovato la foto sull'ftp
					// se la foto è ANCHE in locale, cancello in LOCALE
					$this->CancellaFotoInLocale ($linkFotCds, 0);
					return $linkFtp;
				}
			}
		}
		
		// se arriva qui vuol dire che non ha trovato la foto sull'ftp: la cerco in fotocds
		//if ($_SESSION['CC_User'] == "***+")
			//alert ($linkFotCds);
		return $linkFotCds;
	}
	*/
	
	function CancellaFotoInLocale ($linkCds, $prova)  // prova = 1 evita l'alert per OGNI foto durante il travaso!
	{
		// se arrivo qui vuol dire che ho trovato la foto sull'ftp
		// se la foto è ANCHE in locale, cancello in LOCALE
		$linkCanc = $_SERVER['DOCUMENT_ROOT'] . $linkCds;
		if (file_exists($linkCanc))
		{
			if ($_SESSION['CC_User'] == "***+" && $prova == 0)
				alert ("vorrei cancellare $linkCds (perchè è su FTP)");
			//unlink ($linkCanc);
		}
		else if ($_SESSION['CC_User'] == "***+" && $prova == 0)
		{
			alert ("vorrei cancellare $linkCds (perchè è su FTP) ma non c'è in locale");
		}
	}

	function ftp_is_dir($ftp, $dir)
	{
		$pushd = ftp_pwd($ftp);
		if ($pushd !== false && @ftp_chdir($ftp, $dir))
		{
			ftp_chdir($ftp, $pushd);
			return true;
		}
		return false;
	}

	function CreaNuovaCartellaComuneSeNonEsiste ($cartellaComune)
	{
		// procedura per controllare se la cartella COMUNE esiste: se NON esiste, viene creata
		$nuova_cartella_su_aruba = $this->mySito . $cartellaComune;
		$risp = $this->ftp_is_dir($this->myConnessione, $nuova_cartella_su_aruba);
		if ($risp != true) ftp_mkdir($this->myConnessione, $nuova_cartella_su_aruba);
		// fine procedura nuova cartella
	}
	
	function CloseFtp ()
	{
		if ($this->myConnessione != NULL)
			ftp_close($this->myConnessione);
		echo "<br>Connessione FTP chiusa!<br>";
	}
}

?>