<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class indirizzo_enti
{
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
	
		$indirizzo = array();
		$indirizzo['Riga1'] = $ind_1; // indirizzo destinatario
	
		/////////////////////
		$lunghezza = strlen($ind_1);
		if($lunghezza<50)
		{
			$indirizzo['Riga1'] = strtoupper($ind_1);
			$indirizzo['Riga2'] = strtoupper($ind_2)." ".strtoupper($ind_3);
			$indirizzo['Riga3'] = $fax;
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
			$indirizzo['Riga3'] = strtoupper($ind_2)." ".strtoupper($ind_3);
			$indirizzo['Riga4'] = $fax;
		}
		///////////////////////
	
		$indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
		if($ind_3!="")
			$indirizzo['Completo'].= ", ".strtoupper($ind_3);
									
		$indirizzo['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
		if($ind_3!="")
			$indirizzo['Senza_Provincia'].= ", ".strtoupper($ind_3);
		
		$indirizzo['Destinatario'] = $this->Denominazione;
	
		return $indirizzo;
	}
	
}

?>