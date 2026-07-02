<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

//*****************************************************************
// Classi 290 		                            	          	  *
// 												                  *
//*****************************************************************
class N0N9
{	
	
	public $ID;
	public $Codice_Ente;
	public $Data_Invio_Fornitura;
	public $Record_Totali;
	public $Record_N1;
	public $Record_N2;
	public $Record_N3;
	public $Record_N4;
	public $Record_N5;
	public $n1 = array();
		
	public function __construct( $progr )
	{
		if ($progr == NULL) return;
		$query = "SELECT * FROM 290_n0_n9 WHERE ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_object($result);
		
		$this->ID = utf8_decode($val->ID);
		$this->Codice_Ente = utf8_decode($val->Codice_Ente);
		$this->Data_Invio_Fornitura = utf8_decode($val->Data_Invio_Fornitura);
		$this->Record_Totali = utf8_decode($val->Record_Totali);
		$this->Record_N1 = utf8_decode($val->Record_N1);
		$this->Record_N2 = utf8_decode($val->Record_N2);
		$this->Record_N3 = utf8_decode($val->Record_N3);
		$this->Record_N4 = utf8_decode($val->Record_N4);
		$this->Record_N5 = utf8_decode($val->Record_N5);
		
		$n1id = select_mysql_array("ID", "290_n1_n5","N0_ID = '".$this->ID."'");
				
		for( $i=0; $i<$this->Record_N1; $i++)
		{
			$this->n1[$i] = new N1N5($n1id[$i]['ID']);
		}
		
	}
	
	public function array_scarti( $ID )
	{
		$n2_scarti = select_mysql_array("ID", "290_n2","N0_ID = '".$ID."' AND Flag_Partita = 'no'");
		
		$array = array();
		for( $i=0; $i<count($n2_scarti); $i++)
		{
			$array[$i] = new N2($n2_scarti[$i]['ID']);
		}
		
		return $array;
	}
}

class N1N5
{
	
	public $ID;
	public $Progressivo_Minuta;
	public $Codice_Ente;
	public $Tipo_Ruolo;
	public $Num_Ruolo;
	public $Num_Rate;
	public $Ruolo;
	public $Codice_Sede;
	public $Tipo_Compenso;
	public $Ruolo_ICIAP;
	public $Num_Convenzione;
	public $Flag_Articoli;
	public $Totale_Record_N1_N5;
	public $Record_N2;
	public $Record_N3;
	public $Record_N4;
	public $Totale_Imposta;
	public $n2 = array();
	
		
	public function __construct( $progr )
	{
			
		$query = "SELECT * FROM 290_n1_n5 WHERE ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
	
		$this->ID = utf8_decode($val['ID']);
		$this->Codice_Ente = utf8_decode($val['Codice_Ente']);
		$this->Progressivo_Minuta = utf8_decode($val['Progressivo_Minuta']);
		$this->Tipo_Ruolo = utf8_decode($val['Tipo_Ruolo']);
		$this->Num_Ruolo = utf8_decode($val['Num_Ruolo']);
		$this->Num_Rate = utf8_decode($val['Num_Rate']);
		$this->Ruolo = utf8_decode($val['Ruolo']);
		$this->Codice_Sede = utf8_decode($val['Codice_Sede']);
		$this->Tipo_Compenso = utf8_decode($val['Tipo_Compenso']);
		$this->Ruolo_ICIAP = utf8_decode($val['Ruolo_ICIAP']);
		$this->Num_Convenzione = utf8_decode($val['Num_Convenzione']);
		$this->Flag_Articoli = utf8_decode($val['Flag_Articoli']);
		$this->Totale_Record_N1_N5 = utf8_decode($val['Totale_Record_N1_N5']);
		$this->Record_N2 = utf8_decode($val['Record_N2']);
		$this->Record_N3 = utf8_decode($val['Record_N3']);
		$this->Record_N4 = utf8_decode($val['Record_N4']);
		$this->Totale_Imposta = utf8_decode($val['Totale_Imposta']);
		
		$n2id = select_mysql_array( "ID" , "290_n2" , "N1_ID = '" . $val['ID'] . "' AND N0_ID = '".$val['N0_ID']."'");

		
		for( $i=0; $i<$this->Record_N2; $i++)
		{
			$this->n2[$i] = new N2($n2id[$i]['ID']);
		}
	}
}

class N2
{
	
	public $ID;
	public $Progressivo_Minuta;
	public $Codice_Ente;
	public $Codice_Partita;
	public $Codice_Fiscale;
	public $Numero_Contribuente;
	public $Codice_Controllo;
	public $Codice_Indirizzo_Res;
	public $Indirizzo_Res;
	public $Civico_Res;
	public $Lettera_Civico_Res;
	public $Interno_Res;
	public $Km_Res;
	public $Cap_Res;
	public $CC_Indirizzo_Res;
	public $Frazione_Res;
	public $Codice_Indirizzo_Dom;
	public $Indirizzo_Dom;
	public $Civico_Dom;
	public $Lettera_Civico_Dom;
	public $Interno_Dom;
	public $Km_Dom;
	public $Cap_Dom;
	public $CC_Indirizzo_Dom;
	public $Frazione_Dom;
	public $Natura_Giuridica;
	public $Cognome;
	public $Nome;
	public $Sesso;
	public $Data_Nascita;
	public $CC_Nascita;
	public $Ditta;
	public $Cointestatari;
	public $Flag_Importazione;
	public $Flag_Partita;
	public $n3 = array();
	public $num_n3 = array();
	public $n4 = array();
	public $num_n4 = array();
	
	public function __construct( $progr )
	{
			
		$query = "SELECT * FROM 290_n2 WHERE ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Progressivo_Minuta = utf8_decode($val['Progressivo_Minuta']);
		$this->Codice_Ente = utf8_decode($val['Codice_Ente']);
		$this->Codice_Partita = utf8_decode($val['Codice_Partita']);
		$this->Codice_Fiscale = utf8_decode($val['Codice_Fiscale']);
		$this->Numero_Contribuente = utf8_decode($val['Numero_Contribuente']);
		$this->Codice_Controllo = utf8_decode($val['Codice_Controllo']);
		$this->Codice_Indirizzo_Res = utf8_decode($val['Codice_Indirizzo_Res']);
		$this->Indirizzo_Res = utf8_decode($val['Indirizzo_Res']);
		$this->Civico_Res = utf8_decode($val['Civico_Res']);
		$this->Lettera_Civico_Res = utf8_decode($val['Lettera_Civico_Res']);
		$this->Interno_Res = utf8_decode($val['Interno_Res']);
		$this->Km_Res = utf8_decode($val['Km_Res']);
		$this->Cap_Res = utf8_decode($val['Cap_Res']);
		$this->CC_Indirizzo_Res = utf8_decode($val['CC_Indirizzo_Res']);
		$this->Frazione_Res = utf8_decode($val['Frazione_Res']);
		$this->Codice_Indirizzo_Dom = utf8_decode($val['Codice_Indirizzo_Dom']);
		$this->Indirizzo_Dom = utf8_decode($val['Indirizzo_Dom']);
		$this->Civico_Dom = utf8_decode($val['Civico_Dom']);
		$this->Lettera_Civico_Dom = utf8_decode($val['Lettera_Civico_Dom']);
		$this->Interno_Dom = utf8_decode($val['Interno_Dom']);
		$this->Km_Dom = utf8_decode($val['Km_Dom']);
		$this->Cap_Dom = utf8_decode($val['Cap_Dom']);
		$this->CC_Indirizzo_Dom = utf8_decode($val['CC_Indirizzo_Dom']);
		$this->Frazione_Dom = utf8_decode($val['Frazione_Dom']);
		$this->Natura_Giuridica = utf8_decode($val['Natura_Giuridica']);
		$this->Cognome = utf8_decode($val['Cognome']);
		$this->Nome = utf8_decode($val['Nome']);
		$this->Sesso = utf8_decode($val['Sesso']);
		$this->Data_Nascita = utf8_decode($val['Data_Nascita']);
		$this->CC_Nascita = utf8_decode($val['CC_Nascita']);
		$this->Ditta = utf8_decode($val['Ditta']);
		$this->Cointestatari = utf8_decode($val['Cointestatari']);
		$this->Flag_Importazione = utf8_decode($val['Flag_Importazione']);
		$this->Flag_Partita = utf8_decode($val['Flag_Partita']);

		$n3id = select_mysql_array("ID", "290_n3","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
		$numn3 = count($n3id);
		$this->num_n3 = $numn3;
		
		for( $i=0; $i<$numn3; $i++)
		{
			$this->n3[$i] = new N3($n3id[$i]['ID']);
		}
		
		$n4id = select_mysql_array("ID", "290_n4","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
		$numn4 = count($n4id);
		$this->num_n4 = $numn4;
			
		for( $i=0; $i<$numn4; $i++)
		{
			$this->n4[$i] = new N4($n4id[$i]['ID']);
		}


	}
}

class N3
{

	public $ID;
	public $Progressivo_Minuta;
	public $Codice_Ente;
	public $Codice_Partita;
	public $Codice_Fiscale;
	public $Codice_Indirizzo_Res;
	public $Indirizzo_Res;
	public $Civico_Res;
	public $Lettera_Civico_Res;
	public $Interno_Res;
	public $Km_Res;
	public $Cap_Res;
	public $CC_Indirizzo_Res;
	public $Frazione_Res;
	public $Codice_Indirizzo_Dom;
	public $Indirizzo_Dom;
	public $Civico_Dom;
	public $Lettera_Civico_Dom;
	public $Interno_Dom;
	public $Km_Dom;
	public $Cap_Dom;
	public $CC_Indirizzo_Dom;
	public $Frazione_Dom;
	public $Natura_Giuridica;
	public $Cognome;
	public $Nome;
	public $Sesso;
	public $Data_Nascita;
	public $CC_Nascita;
	public $Ditta;
	public $Flag_Importazione;
		

	public function __construct( $progr )
	{
			
		$query = "SELECT * FROM 290_n3 WHERE ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Progressivo_Minuta = utf8_decode($val['Progressivo_Minuta']);
		$this->Codice_Ente = utf8_decode($val['Codice_Ente']);
		$this->Codice_Partita = utf8_decode($val['Codice_Partita']);
		$this->Codice_Fiscale = utf8_decode($val['Codice_Fiscale']);
		$this->Codice_Indirizzo_Res = utf8_decode($val['Codice_Indirizzo_Res']);
		$this->Indirizzo_Res = utf8_decode($val['Indirizzo_Res']);
		$this->Civico_Res = utf8_decode($val['Civico_Res']);
		$this->Lettera_Civico_Res = utf8_decode($val['Lettera_Civico_Res']);
		$this->Interno_Res = utf8_decode($val['Interno_Res']);
		$this->Km_Res = utf8_decode($val['Km_Res']);
		$this->Cap_Res = utf8_decode($val['Cap_Res']);
		$this->CC_Indirizzo_Res = utf8_decode($val['CC_Indirizzo_Res']);
		$this->Frazione_Res = utf8_decode($val['Frazione_Res']);
		$this->Codice_Indirizzo_Dom = utf8_decode($val['Codice_Indirizzo_Dom']);
		$this->Indirizzo_Dom = utf8_decode($val['Indirizzo_Dom']);
		$this->Civico_Dom = utf8_decode($val['Civico_Dom']);
		$this->Lettera_Civico_Dom = utf8_decode($val['Lettera_Civico_Dom']);
		$this->Interno_Dom = utf8_decode($val['Interno_Dom']);
		$this->Km_Dom = utf8_decode($val['Km_Dom']);
		$this->Cap_Dom = utf8_decode($val['Cap_Dom']);
		$this->CC_Indirizzo_Dom = utf8_decode($val['CC_Indirizzo_Dom']);
		$this->Frazione_Dom = utf8_decode($val['Frazione_Dom']);
		$this->Natura_Giuridica = utf8_decode($val['Natura_Giuridica']);
		$this->Cognome = utf8_decode($val['Cognome']);
		$this->Nome = utf8_decode($val['Nome']);
		$this->Sesso = utf8_decode($val['Sesso']);
		$this->Data_Nascita = utf8_decode($val['Data_Nascita']);
		$this->CC_Nascita = utf8_decode($val['CC_Nascita']);
		$this->Ditta = utf8_decode($val['Ditta']);
		$this->Flag_Importazione = utf8_decode($val['Flag_Importazione']);
	}

}

class N4
{
	
	public $ID;
	public $Codice_Ente;
	public $Progressivo_Minuta;
	public $Codice_Partita;
	public $Anno_Tributo;
	public $Codice_Tributo;
	public $Imponibile;
	public $Imposta;
	public $Num_Semestri_Interessi;
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
	public $Pagante;
	public $Tipo_Pagamento;
	public $Quietanza;
	public $Bollettario;
	public $Data_Notifica;
	public $Data_Emissione;
	public $Data_Calcolo;
	public $Stato_Ingiunzione;
	public $Stato_Stampa;
	public $Data_Stampa;
	public $Data_Registrazione;
	public $Flag_Importazione;
	
	public function __construct( $progr )
	{
			
		$query = "SELECT * FROM 290_n4 WHERE ID = '".$progr."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->Codice_Ente = utf8_decode($val['Codice_Ente']);
		$this->Progressivo_Minuta = utf8_decode($val['Progressivo_Minuta']);
		$this->Codice_Partita = utf8_decode($val['Codice_Partita']);
		$this->Anno_Tributo = utf8_decode($val['Anno_Tributo']);
		$this->Codice_Tributo = utf8_decode($val['Codice_Tributo']);
		$this->Imponibile = utf8_decode($val['Imponibile']);
		$this->Imposta = utf8_decode($val['Imposta']);
		$this->Num_Semestri_Interessi = utf8_decode($val['Num_Semestri_Interessi']);
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
		$this->Data_Calcolo = utf8_decode($val['Data_Calcolo']);
		$this->Stato_Ingiunzione = utf8_decode($val['Stato_Ingiunzione']);
		$this->Stato_Stampa = utf8_decode($val['Stato_Stampa']);
		$this->Data_Stampa = utf8_decode($val['Data_Stampa']);
		$this->Flag_Importazione = utf8_decode($val['Flag_Importazione']);
		
	}	
}

class riepilogo
{
	public $corretti;
	public $omoN2corretti;
	public $omoN3corretti;
	public $scarti;
	public $omoN2scarti;
	public $omoN3scarti;
	public $N3corretti;
	public $N4corretti;	
	
	public function __construct( $progr )
	{
		$corretti = 0;
		$omoN2corretti = 0;
		$omoN3corretti = 0;
		$scarti = 0;
		$omoN2scarti = 0;
		$omoN3scarti = 0;
		$N3corretti = 0;
		$N4corretti = 0;
		
		$query = "SELECT ID FROM 290_n1_n5 WHERE N0_ID = '".$progr."'";
		$n1_id = single_answer_query($query);
		
		$query = "SELECT * FROM 290_n2 WHERE N1_ID = '".$n1_id."'";
		$result = safe_query($query);
		
		$control = select_mysql_array("*", "290_n2", "N1_ID = '".$n1_id."'");
		$num = count($control);
		
		for($x=0;$x<$num;$x++)
		{
			$n2_id = $control[$x]['ID'];
			$import = $control[$x]['Flag_Importazione'];
			$partita = $control[$x]['Flag_Partita'];
			
			if($partita == "si" )
			{
				switch($import)
				{
					case "ok":
						
						$corretti += 1;
						
						break;
						
					case "N2":
						
						$omoN2corretti += 1;
						
						break;
						
					case "N3":
						
						$omoN3corretti += 1;
						
						break;
				}
			}
			else if ($partita == "no")
			{
				switch($import)
				{
					case "Scarto":
			
						$scarti += 1;
			
						break;
			
					case "N2":
			
						$omoN2scarti += 1;
			
						break;
			
					case "N3":
			
						$omoN3scarti += 1;
			
						break;
				}
			}
			
			$N3control = select_mysql_array("*", "290_n3", "N2_ID = '".$n2_id."'");
			$N3num = count($N3control);
						
			for($j=0;$j<$N3num;$j++)
			{
				if($N3control[$j]['Flag_Importazione'] == "ok")
					$N3corretti += 1;
			}
			
			$N4control = select_mysql_array("*", "290_n4", "N2_ID = '".$n2_id."'");
			$N4num = count($N4control);
			
			for($j=0;$j<$N4num;$j++)
			{
				if($N4control[$j]['Flag_Importazione'] == "ok")
					$N4corretti += 1;
			}
			
		}
		
		$this->corretti = $corretti;
		$this->omoN2corretti = $omoN2corretti;
		$this->omoN3corretti = $omoN3corretti;
		$this->scarti = $scarti;
		$this->omoN2scarti = $omoN2scarti;
		$this->omoN3scarti = $omoN3scarti;
		$this->N3corretti = $N3corretti;
		$this->N4corretti = $N4corretti;
		
	}
	
}




?>