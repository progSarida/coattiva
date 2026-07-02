<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class interessi_tributi
{
	public $arrayInteressi = array();
	
	public $ID = array();
	public $CC = array();
	public $Data_Inizio = array();
	public $Data_Fine = array();
	public $Tasso_Interessi = array();
	
	public function __construct( $c )
	{	
		$query = "SELECT * FROM interessi_tributi WHERE CC = '".$c."' ORDER BY Data_Inizio ASC";
		$array_classe = mysql_array($query);
		
		for($i=0;$i<count($array_classe);$i++)
		{
			$this->ID[$i] = utf8_decode($array_classe[$i]['ID']);
			$this->CC[$i] = utf8_decode($array_classe[$i]['CC']);
			$this->Data_Inizio[$i] = utf8_decode($array_classe[$i]['Data_Inizio']);
			$this->Data_Fine[$i] = utf8_decode($array_classe[$i]['Data_Fine']);
			$this->Tasso_Interessi[$i] = utf8_decode($array_classe[$i]['Tasso_Interessi']);
		}

	}
	
	public function setArray(array $storearray)
	{
		$this->array = $storearray;
	}
	
	public static function getRows( $c, Database $db)
	{
		$db->query("SELECT * FROM interessi_tributi WHERE CC = :CC ORDER BY Data_Inizio ASC");
		$db->bind(':CC', $c);
		return $db->results();
	}
	
	public static function rowCount( $c, Database $db)
	{
		$db->query("SELECT * FROM interessi_tributi WHERE CC = :CC ORDER BY Data_Inizio ASC");
		$db->bind(':CC', $c);
		$db->execute();
		return $db->rowCount();
	}
	
	
	public static function getSingleRow( $id, Database $db)
	{
		$db->query("SELECT * FROM interessi_tributi WHERE ID = :ID ORDER BY Data_Inizio ASC");
		$db->bind(':ID', $id);
		return $db->single();
	}
	
	public function calcola_interessi_tributi( $data_inizio, $data_fine, $importo )
	{
		$data_inizio = to_mysql_date($data_inizio);
		$data_fine = to_mysql_date($data_fine);

		$interessi_array = array();
		
		if( $data_inizio!=null && $data_inizio!="0000-00-00" && $data_inizio!="" )
		{
			$j=0;

			for($i=0;$i<count($this->ID);$i++)
			{
				if($data_inizio >= $this->Data_Inizio[$i] && ($data_inizio <= $this->Data_Fine[$i] || $this->Data_Fine[$i]==null || $this->Data_Fine[$i]=="0000-00-00"))
				{
					$interessi_array[$j]['Data_Partenza'] = $data_inizio;
					$interessi_array[$j]['Tasso'] = $this->Tasso_Interessi[$i];
					
					if($data_fine <= $this->Data_Fine[$i] || $this->Data_Fine[$i]==null || $this->Data_Fine[$i]=="0000-00-00")
					{
						$interessi_array[$j]['Data_Termine'] = $data_fine;
						break;
					}
					else 
					{
						$interessi_array[$j]['Data_Termine'] = $this->Data_Fine[$i];
					}		

				}
				else if($data_inizio < $this->Data_Inizio[$i] && $data_fine >= $this->Data_Inizio[$i])
				{
					$j++;

					$interessi_array[$j]['Data_Partenza'] = $this->Data_Inizio[$i];
					$interessi_array[$j]['Tasso'] = $this->Tasso_Interessi[$i];
					
					if($data_fine <= $this->Data_Fine[$i] || $this->Data_Fine[$i]==null || $this->Data_Fine[$i]=="0000-00-00")
					{
						$interessi_array[$j]['Data_Termine'] = $data_fine;
						break;
					}
					else
					{
						$interessi_array[$j]['Data_Termine'] = $this->Data_Fine[$i];
					}
				}
				
			}

			for($j=0;$j<count($interessi_array);$j++)
			{
				$interessi_array[$j]['Interesse_Giornaliero'] = $importo * $interessi_array[$j]['Tasso'] / 100 / 365;
				$interessi_array[$j]['Numero_Giorni'] = calcola_giorni( from_mysql_date($interessi_array[$j]['Data_Partenza']) , from_mysql_date($interessi_array[$j]['Data_Termine']) );
					
				$interessi_array[$j]['Interesse_Parziale'] = number_format($interessi_array[$j]['Interesse_Giornaliero'] * $interessi_array[$j]['Numero_Giorni'],2);
			}
		}
		
		return $interessi_array;
	}
	
	public function totale_interessi_tributi($interessi_array)
	{
		$tot_interessi = 0;
		for($k=0;$k<count($interessi_array);$k++)
		{
			$tot_interessi+=$interessi_array[$k]['Interesse_Parziale'];
		}
		
		return $tot_interessi;
	}
}

class parametri_annuali
{	
	
	public $ID;
	public $CC;
	public $Tipo_Riscossione;
	public $Anno;
	public $Spese_Notifica;
	public $Spese_Notifica_Pignoramento;
	public $Spese_Ricerca;
	public $Spese_Postali;
    public $Spese_Raccomandata;
	public $Spese_Postali_AG;
	public $CAN;
	public $CAD;
	public $A_Mani;
	public $A_Mani_Pignoramento;
	public $IVA;
	public $Maggiorazione_Preavviso;
	public $Maggiorazione_Ingiunzione;
	public $Diritto_Riscossione_Minimo;
	public $Diritto_Riscossione_Massimo;
	public $Importo_Minimo;
	public $Giorni_Diritto;
	
	public function __construct( $c, $data , $tipo )
	{

		if( substr($data,2,1) == "/" )
			$data = to_mysql_date($data);
		
		$a = substr($data, 0,4);
		
		$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$a."' AND Tipo_Riscossione = '*****'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
	
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
		$this->Anno = utf8_decode($val['Anno']);
		
		$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
		if( $data >= $val['Spese_Notifica_Data'] && $val['Spese_Notifica_Data'] != null )
		{
			$this->Spese_Notifica = utf8_decode($val['Spese_Notifica_New']);
		}
		
		$this->Spese_Notifica_Pignoramento = utf8_decode($val['Spese_Notifica_Pignoramento']);
		if( $data >= $val['Spese_Notifica_Pignoramento_Data'] && $val['Spese_Notifica_Pignoramento_Data'] != null )
		{
			$this->Spese_Notifica_Pignoramento = utf8_decode($val['Spese_Notifica_Pignoramento_New']);
		}
		
		$this->Spese_Ricerca = utf8_decode($val['Spese_Ricerca']);
		if( $data >= $val['Spese_Ricerca_Data'] && $val['Spese_Ricerca_Data'] != null )
		{
			$this->Spese_Ricerca = utf8_decode($val['Spese_Ricerca_New']);
		}
		
		$this->Spese_Postali = utf8_decode($val['Spese_Postali']);
		if( $data >= $val['Spese_Postali_Data'] && $val['Spese_Postali_Data'] != null )
		{
			$this->Spese_Postali = utf8_decode($val['Spese_Postali_New']);
		}

        $this->Spese_Raccomandata = utf8_decode($val['Spese_Raccomandata']);
        if( $data >= $val['Spese_Raccomandata_Data'] && $val['Spese_Raccomandata_Data'] != null )
        {
            $this->Spese_Raccomandata = utf8_decode($val['Spese_Raccomandata_New']);
        }
		
		$this->Spese_Postali_AG = utf8_decode($val['Spese_Postali_AG']);
		if( $data >= $val['Spese_Postali_Data'] && $val['Spese_Postali_AG_Data'] != null )
		{
			$this->Spese_Postali_AG = utf8_decode($val['Spese_Postali_AG_New']);
		}
		
		$this->CAN = utf8_decode($val['CAN']);
		if( $data >= $val['CAN_Data'] && $val['CAN_Data'])
		{
			$this->CAN = utf8_decode($val['CAN_New'] != null );
		}
		
		$this->CAD = utf8_decode($val['CAD']);
		if( $data >= $val['CAD_Data'] && $val['CAD_Data'] != null )
		{
			$this->CAD = utf8_decode($val['CAD_New']);
		}
		
		$this->A_Mani = utf8_decode($val['A_Mani']);
		if( $data >= $val['A_Mani_Data'] && $val['A_Mani_Data'] != null )
		{
			$this->A_Mani = utf8_decode($val['A_Mani_New']);
		}
		
		$this->A_Mani_Pignoramento = utf8_decode($val['A_Mani_Pignoramento']);
		if( $data >= $val['A_Mani_Pignoramento_Data'] && $val['A_Mani_Pignoramento_Data'] != null )
		{
			$this->A_Mani_Pignoramento = utf8_decode($val['A_Mani_Pignoramento_New']);
		}
		
		$this->IVA = utf8_decode($val['IVA']);
		if( $data >= $val['IVA_Data'] && $val['IVA_Data'] != null )
		{
			$this->IVA = utf8_decode($val['IVA_New']);
		}
		
		$this->Maggiorazione_Preavviso = utf8_decode($val['Maggiorazione_Preavviso']);
		$this->Maggiorazione_Ingiunzione = utf8_decode($val['Maggiorazione_Ingiunzione']);
		
		$this->Diritto_Riscossione_Minimo = utf8_decode($val['Diritto_Riscossione_Minimo']);
		$this->Diritto_Riscossione_Massimo = utf8_decode($val['Diritto_Riscossione_Massimo']);
		
		$this->Giorni_Diritto = utf8_decode($val['Giorni_Diritto']);
		
		$this->Importo_Minimo = utf8_decode($val['Importo_Minimo']);

	}
	
	public function verificaPresenzaParametri()
	{
		$array_campi = array();
		$stringa = "I seguenti parametri annuali sono mancanti: ";
		
		if($this->Spese_Notifica!=null && $this->Spese_Notifica!=0)
			$array_campi['Spese_Notifica'] = "ok";
		else 
		{
			$stringa.= " 'Spese notifica ingiunzione' ";
			$array_campi['Spese_Notifica'] = "";
		}
		
		if($this->Spese_Notifica_Pignoramento!=null && $this->Spese_Notifica_Pignoramento!=0)
			$array_campi['Spese_Notifica_Pignoramento'] = "ok";
		else
		{
			$stringa.= " 'Spese notifica pignoramento' ";
			$array_campi['Spese_Notifica_Pignoramento'] = "";
		}
		
		if($this->Spese_Ricerca!=null && $this->Spese_Ricerca!=0)
			$array_campi['Spese_Ricerca'] = "ok";
		else
		{
			$stringa.= " 'Spese di ricerca' ";
			$array_campi['Spese_Ricerca'] = "";
		}
		
		if($this->Spese_Postali!=null && $this->Spese_Postali!=0)
			$array_campi['Spese_Postali'] = "ok";
		else
		{
			$stringa.= " 'Spese posta ordinaria' ";
			$array_campi['Spese_Postali'] = "";
		}

        if($this->Spese_Raccomandata!=null && $this->Spese_Raccomandata!=0)
            $array_campi['Spese_Raccomandata'] = "ok";
        else
        {
            $stringa.= " 'Spese raccomandata ordinaria' ";
            $array_campi['Spese_Raccomandata'] = "";
        }
		
		if($this->Spese_Postali_AG!=null && $this->Spese_Postali_AG!=0)
			$array_campi['Spese_Postali_AG'] = "ok";
		else
		{
			$stringa.= " 'Spese raccomandata AG (Atti Giudiziari)' ";
			$array_campi['Spese_Postali_AG'] = "";
		}
		
		if($this->CAN!=null)
			$array_campi['CAN'] = "ok";
		else
		{
			$stringa.= " 'CAN' ";
			$array_campi['CAN'] = "";
		}
		
		if($this->CAD!=null)
			$array_campi['CAD'] = "ok";
		else
		{
			$stringa.= " 'CAD' ";
			$array_campi['CAD'] = "";
		}
		
		if($this->A_Mani!=null && $this->A_Mani!=0)
			$array_campi['A_Mani'] = "ok";
		else
		{
			$stringa.= " 'A mani ingiunzione' ";
			$array_campi['A_Mani'] = "";
		}
		
		if($this->A_Mani_Pignoramento!=null && $this->A_Mani_Pignoramento!=0)
			$array_campi['A_Mani_Pignoramento'] = "ok";
		else
		{
			$stringa.= " 'A mani pignoramento' ";
			$array_campi['A_Mani_Pignoramento'] = "";
		}
		
		if($this->IVA!=null && $this->IVA!=0)
			$array_campi['IVA'] = "ok";
		else
		{
			$stringa.= " 'IVA' ";
			$array_campi['IVA'] = "";
		}
		
		if($this->Importo_Minimo!=null && $this->Importo_Minimo!=0)
			$array_campi['Importo_Minimo'] = "ok";
		else
		{
			$stringa.= " 'Importo minimo' ";
			$array_campi['Importo_Minimo'] = "";
		}
		
		if($this->Diritto_Riscossione_Minimo!=null && $this->Diritto_Riscossione_Minimo!=0)
			$array_campi['Diritto_Riscossione_Minimo'] = "ok";
		else
		{
			$stringa.= " 'Diritto riscossione minimo' ";
			$array_campi['Diritto_Riscossione_Minimo'] = "";
		}
		
		if($this->Diritto_Riscossione_Massimo!=null && $this->Diritto_Riscossione_Massimo!=0)
			$array_campi['Diritto_Riscossione_Massimo'] = "ok";
		else
		{
			$stringa.= " 'Diritto riscossione massimo' ";
			$array_campi['Diritto_Riscossione_Massimo'] = "";
		}
		
		if($this->Giorni_Diritto!=null && $this->Giorni_Diritto!=0)
			$array_campi['Giorni_Diritto'] = "ok";
		else
		{
			$stringa.= " 'Giorni diritto (con pagamento oltre i 60 giorni)' ";
			$array_campi['Giorni_Diritto'] = "";
		}
		
		
		$array_campi['Stringa'] = $stringa;
		
		return $array_campi;
		
	}
	
	public function controlloParametri( $c, $data , $tipo )
	{
		$data = to_mysql_date($data);
		if($this->ID ==	null)
		{
			$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
			$result = safe_query($query);
			if(mysql_num_rows($result)>0)
				$val = mysql_fetch_array($result);
			else 
			{
				$query = "SELECT * FROM parametri_annuali WHERE Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
				$result = safe_query($query);
				if(mysql_num_rows($result)>0)
					$val = mysql_fetch_array($result);
				else 
					return "ERROR";
			}
			
			$this->CC = $c;
			$this->Anno = substr($data, 0,4);
			$this->Tipo_Riscossione = $tipo;
			
			$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
			if( $data >= $val['Spese_Notifica_Data'] && $val['Spese_Notifica_Data'] != null )
			{
				$this->Spese_Notifica = utf8_decode($val['Spese_Notifica_New']);
			}
			
			$this->Spese_Notifica_Pignoramento = utf8_decode($val['Spese_Notifica_Pignoramento']);
			if( $data >= $val['Spese_Notifica_Pignoramento_Data'] && $val['Spese_Notifica_Pignoramento_Data'] != null )
			{
				$this->Spese_Notifica_Pignoramento = utf8_decode($val['Spese_Notifica_Pignoramento_New']);
			}
			
			$this->Spese_Ricerca = utf8_decode($val['Spese_Ricerca']);
			if( $data >= $val['Spese_Ricerca_Data'] && $val['Spese_Ricerca_Data'] != null )
			{
				$this->Spese_Ricerca = utf8_decode($val['Spese_Ricerca_New']);
			}
			
			$this->Spese_Postali = utf8_decode($val['Spese_Postali']);
			if( $data >= $val['Spese_Postali_Data'] && $val['Spese_Postali_Data'] != null )
			{
				$this->Spese_Postali = utf8_decode($val['Spese_Postali_New']);
			}

            $this->Spese_Raccomandata = utf8_decode($val['Spese_Raccomandata']);
            if( $data >= $val['Spese_Raccomandata_Data'] && $val['Spese_Raccomandata_Data'] != null )
            {
                $this->Spese_Raccomandata = utf8_decode($val['Spese_Raccomandata_New']);
            }
			
			$this->Spese_Postali_AG = utf8_decode($val['Spese_Postali_AG']);
			if( $data >= $val['Spese_Postali_AG_Data'] && $val['Spese_Postali_AG_Data'] != null )
			{
				$this->Spese_Postali_AG = utf8_decode($val['Spese_Postali_AG_New']);
			}
			
			$this->CAN = utf8_decode($val['CAN']);
			if( $data >= $val['CAN_Data'] && $val['CAN_Data'])
			{
				$this->CAN = utf8_decode($val['CAN_New'] != null );
			}
			
			$this->CAD = utf8_decode($val['CAD']);
			if( $data >= $val['CAD_Data'] && $val['CAD_Data'] != null )
			{
				$this->CAD = utf8_decode($val['CAD_New']);
			}
			
			$this->A_Mani = utf8_decode($val['A_Mani']);
			if( $data >= $val['A_Mani_Data'] && $val['A_Mani_Data'] != null )
			{
				$this->A_Mani = utf8_decode($val['A_Mani_New']);
			}
			
			$this->A_Mani_Pignoramento = utf8_decode($val['A_Mani_Pignoramento']);
			if( $data >= $val['A_Mani_Pignoramento_Data'] && $val['A_Mani_Pignoramento_Data'] != null )
			{
				$this->A_Mani_Pignoramento_New = utf8_decode($val['A_Mani_Pignoramento_New']);
			}
			
			$this->IVA = utf8_decode($val['IVA']);
			if( $data >= $val['IVA_Data'] && $val['IVA_Data'] != null )
			{
				$this->IVA = utf8_decode($val['IVA_New']);
			}
			
			$this->Maggiorazione_Preavviso = utf8_decode($val['Maggiorazione_Preavviso']);
			$this->Maggiorazione_Ingiunzione = utf8_decode($val['Maggiorazione_Ingiunzione']);
			
			$this->Diritto_Riscossione_Minimo = utf8_decode($val['Diritto_Riscossione_Minimo']);
			$this->Diritto_Riscossione_Massimo = utf8_decode($val['Diritto_Riscossione_Massimo']);
			$this->Giorni_Diritto = utf8_decode($val['Giorni_Diritto']);
			
			$this->Importo_Minimo = utf8_decode($val['Importo_Minimo']);
			
			$controlInsert = $this->Insert();
			if($controlInsert)
				return "NEW";
			else 
				return "ERROR";
			
		}
		else
		{
			return "OK";
		}
	}
	
	public function Delete ($anno)
	{
		$query = "DELETE FROM parametri_annuali WHERE CC = '".$this->CC."' AND Tipo_Riscossione = '".$this->Tipo_Riscossione."' AND Anno = '".$anno."'";
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
	
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
	
		$query = table_insert_record_query ("parametri_annuali", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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
	
		$query = table_update_record_query ("parametri_annuali", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class gestione_parametri_annuali
{
	public $ID;
	public $CC;
	public $Anno;
	public $Spese_Notifica;
	public $Spese_Notifica_Pignoramento;
	public $Spese_Ricerca;
	public $Spese_Postali;
	public $Spese_Raccomandata;
    public $Spese_Raccomandata_Data;
    public $Spese_Raccomandata_New;
	public $Spese_Postali_AG;
	public $CAN;
	public $CAD;
	public $A_Mani;
	public $A_Mani_Pignoramento;
	public $IVA;
	public $Spese_Notifica_Data;
	public $Spese_Notifica_Pignoramento_Data;
	public $Spese_Ricerca_Data;
	public $Spese_Postali_Data;
	public $Spese_Postali_AG_Data;
	public $CAN_Data;
	public $CAD_Data;
	public $A_Mani_Data;
	public $A_Mani_Pignoramento_Data;
	public $IVA_Data;
	public $Spese_Notifica_New;
	public $Spese_Notifica_Pignoramento_New;
	public $Spese_Ricerca_New;
	public $Spese_Postali_New;
	public $Spese_Postali_AG_New;
	public $CAN_New;
	public $CAD_New;
	public $A_Mani_New;
	public $A_Mani_Pignoramento_New;
	public $IVA_New;
	public $Maggiorazione_Preavviso;
	public $Maggiorazione_Ingiunzione;
	public $Diritto_Riscossione_Minimo;
	public $Diritto_Riscossione_Massimo;
	public $Importo_Minimo;
	public $Giorni_Diritto;

	public function __construct( $c, $a , $tipo )
	{

		$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$a."' AND Tipo_Riscossione = '*****'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Anno = utf8_decode($val['Anno']);

		$this->Spese_Notifica = utf8_decode($val['Spese_Notifica']);
		$this->Spese_Notifica_Data = utf8_decode($val['Spese_Notifica_Data']);
		$this->Spese_Notifica_New = utf8_decode($val['Spese_Notifica_New']);
		
		$this->Spese_Notifica_Pignoramento = utf8_decode($val['Spese_Notifica_Pignoramento']);
		$this->Spese_Notifica_Pignoramento_Data = utf8_decode($val['Spese_Notifica_Pignoramento_Data']);
		$this->Spese_Notifica_Pignoramento_New = utf8_decode($val['Spese_Notifica_Pignoramento_New']);
		
		$this->Spese_Ricerca = utf8_decode($val['Spese_Ricerca']);
		$this->Spese_Ricerca_Data = utf8_decode($val['Spese_Ricerca_Data']);
		$this->Spese_Ricerca_New = utf8_decode($val['Spese_Ricerca_New']);
		
		$this->Spese_Postali = utf8_decode($val['Spese_Postali']);
		$this->Spese_Postali_Data = utf8_decode($val['Spese_Postali_Data']);
		$this->Spese_Postali_New = utf8_decode($val['Spese_Postali_New']);

        $this->Spese_Raccomandata = utf8_decode($val['Spese_Raccomandata']);
        $this->Spese_Raccomandata_Data = utf8_decode($val['Spese_Raccomandata_Data']);
        $this->Spese_Raccomandata_New = utf8_decode($val['Spese_Raccomandata_New']);
		
		$this->Spese_Postali_AG = utf8_decode($val['Spese_Postali_AG']);
		$this->Spese_Postali_AG_Data = utf8_decode($val['Spese_Postali_AG_Data']);
		$this->Spese_Postali_AG_New = utf8_decode($val['Spese_Postali_AG_New']);


		$this->CAN = utf8_decode($val['CAN']);
		$this->CAN_Data = utf8_decode($val['CAN_Data']);
		$this->CAN_New = utf8_decode($val['CAN_New']);	

		$this->CAD = utf8_decode($val['CAD']);
		$this->CAD_Data = utf8_decode($val['CAD_Data']);
		$this->CAD_New = utf8_decode($val['CAD_New']);
		
		$this->A_Mani = utf8_decode($val['A_Mani']);
		$this->A_Mani_Data = utf8_decode($val['A_Mani_Data']);
		$this->A_Mani_New = utf8_decode($val['A_Mani_New']);
		
		$this->A_Mani_Pignoramento = utf8_decode($val['A_Mani_Pignoramento']);
		$this->A_Mani_Pignoramento_Data = utf8_decode($val['A_Mani_Pignoramento_Data']);
		$this->A_Mani_Pignoramento_New = utf8_decode($val['A_Mani_Pignoramento_New']);

		$this->IVA = utf8_decode($val['IVA']);
		$this->IVA_Data = utf8_decode($val['IVA_Data']);
		$this->IVA_New = utf8_decode($val['IVA_New']);
		
		$this->Maggiorazione_Preavviso = utf8_decode($val['Maggiorazione_Preavviso']);
		$this->Maggiorazione_Ingiunzione = utf8_decode($val['Maggiorazione_Ingiunzione']);
		
		$this->Diritto_Riscossione_Minimo = utf8_decode($val['Diritto_Riscossione_Minimo']);
		$this->Diritto_Riscossione_Massimo = utf8_decode($val['Diritto_Riscossione_Massimo']);
		$this->Giorni_Diritto = utf8_decode($val['Giorni_Diritto']);
		
		$this->Importo_Minimo = utf8_decode($val['Importo_Minimo']);

	}
	
	public function Cerca_Anni_Parametri_Annuali ( $c, $tipo, $attuale )
	{

		$query = "SELECT Anno FROM parametri_annuali WHERE CC = '" . $c . "' ";
		$query .= " AND Tipo_Riscossione = '*****'";
		$query .= " ORDER BY Anno ";
		$result = safe_query($query);
		$numAnni = mysql_num_rows($result);
		$array = array();
		$arraySeq = array();
		$arraySeq['PRECEDENTE'] = "";
		$arraySeq['ATTUALE'] = "";
		$arraySeq['SUCCESSIVO'] = "";
		$precTrovato = false;
		$succTrovato = false;
		$memoPrimo = null;
		$conto = 0;
		while ($val = mysql_fetch_assoc($result))
		{
			/*if ($_SESSION['username'] == "marcom")
			{
				//echo "<br>" . $val['Anno'];
				alert ("dentro " . $val['Anno']);
			}*/
			$array[$conto++] = $val['Anno'];
			if ($memoPrimo == null) $memoPrimo = $val['Anno'];
			if ($numAnni == 1)
			{
				$arraySeq['ATTUALE'] = $val['Anno'];
			}
			else if ($numAnni == 2)
			{
				if ($val['Anno'] == $attuale)
				{
					$arraySeq['ATTUALE'] = $val['Anno'];
				}
				else
				{
					$arraySeq['PRECEDENTE'] = $val['Anno'];
					$arraySeq['SUCCESSIVO'] = $val['Anno'];
				}
			}
			else
			{
				if ($val['Anno'] == $attuale)
				{
					$precTrovato = true;
					$succTrovato = true;
					$arraySeq['ATTUALE'] = $val['Anno'];
				}
				else if ($succTrovato == true)
				{
					$succTrovato = false;
					$arraySeq['SUCCESSIVO'] = $val['Anno'];
				}
				if ($precTrovato == false) $arraySeq['PRECEDENTE'] = $val['Anno'];
			}
		}
		if ($succTrovato == true)
		{
			$succTrovato = false;
			$arraySeq['SUCCESSIVO'] = $memoPrimo;
		}
		$arrayEsc = array($array, $arraySeq);
		return $arrayEsc;
	}
}

class parametri_responsabili
{	
	public $Testo_Sostitutivo;
	
	public $ID;
	public $CC;
	public $Tipo_Riscossione;
	public $Funzionario_Responsabile;
	public $Funzionario_Telefono;
	public $Funzionario_Firma;
	public $Funzionario_Testo;
	public $Responsabile_Procedimento;
	public $Responsabile_Telefono;
	public $Responsabile_Firma;
	public $Responsabile_Testo;
	public $Ufficiale_Riscossione;
	public $Ufficiale_Telefono;
	public $Ufficiale_Firma;
	public $Ufficiale_Testo;
	public $Responsabile_Richieste;
	public $Responsabile_Richieste_Telefono;
	public $Responsabile_Richieste_Firma;
	public $Responsabile_Richieste_Testo;
	public $Legale_Rappresentante;
	public $Legale_Rappresentante_Telefono;
	public $Legale_Rappresentante_Firma;
	public $Legale_Rappresentante_Testo;
	
	public function __construct( $c, $tipo )
	{
		
		$query = "SELECT * FROM parametri_responsabili WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
		
		$this->Funzionario_Responsabile = utf8_decode($val['Funzionario_Responsabile']);
		$this->Funzionario_Telefono = utf8_decode($val['Funzionario_Telefono']);
		$this->Funzionario_Firma = utf8_decode($val['Funzionario_Firma']);
		$this->Funzionario_Testo = utf8_decode($val['Funzionario_Testo']);
		
		$this->Responsabile_Procedimento = utf8_decode($val['Responsabile_Procedimento']);
		$this->Responsabile_Telefono = utf8_decode($val['Responsabile_Telefono']);
		$this->Responsabile_Firma = utf8_decode($val['Responsabile_Firma']);
		$this->Responsabile_Testo = utf8_decode($val['Responsabile_Testo']);
		
		$this->Ufficiale_Riscossione = utf8_decode($val['Ufficiale_Riscossione']);
		$this->Ufficiale_Telefono = utf8_decode($val['Ufficiale_Telefono']);
		$this->Ufficiale_Firma = utf8_decode($val['Ufficiale_Firma']);
		$this->Ufficiale_Testo = utf8_decode($val['Ufficiale_Testo']);
		
		$this->Responsabile_Richieste = utf8_decode($val['Responsabile_Richieste']);
		$this->Responsabile_Richieste_Telefono = utf8_decode($val['Responsabile_Richieste_Telefono']);
		$this->Responsabile_Richieste_Firma = utf8_decode($val['Responsabile_Richieste_Firma']);
		$this->Responsabile_Richieste_Testo = utf8_decode($val['Responsabile_Richieste_Testo']);
		
		$this->Legale_Rappresentante = utf8_decode($val['Legale_Rappresentante']);
		$this->Legale_Rappresentante_Telefono = utf8_decode($val['Legale_Rappresentante_Telefono']);
		$this->Legale_Rappresentante_Firma = utf8_decode($val['Legale_Rappresentante_Firma']);
		$this->Legale_Rappresentante_Testo = utf8_decode($val['Legale_Rappresentante_Testo']);

        $this->Testo_Sostitutivo = utf8_decode($val['Testo_Sostitutivo']);
		
	}
	
	public function carica_parametri($c, $tipo)
	{
		$query = "SELECT * FROM parametri_responsabili WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."'";
	
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
			
		return $val;
		
	}
	
	public function parametri_pignoramento($c, $tipo)
	{
		$val = $this->carica_parametri($c, $tipo);
		if($val['Funzionario_Firma'] == "")
			return false;
	
		if($val['Responsabile_Firma'] == "")
			return false;
		
		if($val['Ufficiale_Firma'] == "")
			return false;
	
		return true;
	}
	
	public function parametri_tipo_atto($c, $tipo_tributo, $tipo_atto = null)
	{
		$val = $this->carica_parametri($c, $tipo_tributo);
		
		if($val['Funzionario_Firma'] == "" && $val['Funzionario_Testo']!="si")
			return false;
		
		if($val['Responsabile_Firma'] == "" && $val['Responsabile_Testo']!="si")
			return false;
		
		if($tipo_atto == "pignoramento")
			if($val['Ufficiale_Firma'] == "" && $val['Ufficiale_Testo']!="si")
				return false;
		
		return true;
	}
	
	public function controllo_parametri($c, $tipo_tributo = null, $tipo_atto = null)
	{
		$tributi = array("CDS","RIFIUTI","IMMOBILI","PATRIMONIALE");
						
		if($tipo_tributo==null)
		{
			for($i=0;$i<count($tributi);$i++){
				$control = $this->parametri_tipo_atto($c, $tributi[$i], $tipo_atto);
				if($control === false)
					return $tributi[$i];
			}
		}
		else 
		{
			$control = $this->parametri_tipo_atto($c, $tipo_tributo, $tipo_atto);
			
			if($control === false)
				return $tipo_tributo;
		}
	
		return true;
	}
	
	public function firmaSingola($value)
	{
		
		$firma_path = "/archivio/Firme/".$this->CC."/";
		$percorso = FIRME."/".$this->CC."/";
		

		$temp['intestazione'] = "";
		$temp['nome'] = "";
		$temp['firma'] = "";
		$temp['path_firma'] = "";
		
		switch($value)
		{
			case "Funzionario_Responsabile":
				
				$temp['intestazione'] = "Il Funzionario Responsabile";
				$temp['nome'] = $this->Funzionario_Responsabile;
				
				if($this->Funzionario_Firma!=null)
				{
					$temp['firma'] = $firma_path.$this->Funzionario_Firma;
					$temp['path_firma'] = $percorso.$this->Funzionario_Firma;
				}
				
				break;
				
			case "Responsabile_Procedimento":
			
				$temp['intestazione'] = "Il Responsabile del Procedimento";
				$temp['nome'] = $this->Responsabile_Procedimento;
				
				if($this->Responsabile_Firma!=null)
				{
					$temp['firma'] = $firma_path.$this->Responsabile_Firma;
					$temp['path_firma'] = $percorso.$this->Responsabile_Firma;
				}
			
				break;
				
			case "Ufficiale_Riscossione":
					
				$temp['intestazione'] = "L'Ufficiale della Riscossione";
				$temp['nome'] = $this->Ufficiale_Riscossione;
				
				if($this->Ufficiale_Firma!=null)
				{
					$temp['firma'] = $firma_path.$this->Ufficiale_Firma;
					$temp['path_firma'] = $percorso.$this->Ufficiale_Firma;
				}
					
				break;
				
			case "Responsabile_Richieste":
					
				$temp['intestazione'] = "Il Responsabile della Richiesta";
				$temp['nome'] = $this->Responsabile_Richieste;
				
				if($this->Responsabile_Richieste_Firma!=null)
				{
					$temp['firma'] = $firma_path.$this->Responsabile_Richieste_Firma;
					$temp['path_firma'] = $percorso.$this->Responsabile_Richieste_Firma;
				}	
					
				break;
				
			case "Legale_Rappresentante":
					
				$temp['intestazione'] = "Il Legale Rappresentante";
				$temp['nome'] = $this->Legale_Rappresentante;
				
				if($this->Legale_Rappresentante_Firma!=null)
				{
					$temp['firma'] = $firma_path.$this->Legale_Rappresentante_Firma;
					$temp['path_firma'] = $percorso.$this->Legale_Rappresentante_Firma;
				}
					
				break;
		}
		
		return $temp;
		
	}
	
	public function firme_responsabili()
	{
		$firma_path = "/archivio/Firme/".$this->CC."/";
		$percorso = FIRME."/".$this->CC."/";
		
		$firma = array();
		$firma['Funzionario'] = $firma_path.$this->Funzionario_Firma;
		$firma['Funzionario_Path'] = $percorso.$this->Funzionario_Firma;
		$firma['Funzionario_Nome'] = $this->Funzionario_Responsabile;
		$firma['Funzionario_Intestazione'] = "Il Funzionario responsabile";
		$firma['Funzionario_Testo'] = $this->Funzionario_Testo;

		if($this->Funzionario_Firma=="" && $this->Funzionario_Testo!="si")
		{
			$firma['Funzionario'] = "";
			$firma['Funzionario_Path'] = "";
			$firma['Funzionario_Nome'] = "";
			$firma['Funzionario_Intestazione'] = "";
			$firma['Funzionario_Testo'] = "";
		}
		
		$firma['Responsabile'] = $firma_path.$this->Responsabile_Firma;
		$firma['Responsabile_Path'] = $percorso.$this->Responsabile_Firma;
		$firma['Responsabile_Nome'] = $this->Responsabile_Procedimento;
		$firma['Responsabile_Intestazione'] = "Il Responsabile del procedimento";
		$firma['Responsabile_Testo'] = $this->Responsabile_Testo;
		
		if($this->Responsabile_Firma=="" && $this->Responsabile_Testo!="si")
		{
			$firma['Responsabile'] = "";
			$firma['Responsabile_Path'] = "";
			$firma['Responsabile_Nome'] = "";
			$firma['Responsabile_Intestazione'] = "";
			$firma['Responsabile_Testo'] = "";
		}
		
		
		$firma['Ufficiale'] = $firma_path.$this->Ufficiale_Firma;
		$firma['Ufficiale_Path'] = $percorso.$this->Ufficiale_Firma;
		$firma['Ufficiale_Nome'] = $this->Ufficiale_Riscossione;
		$firma['Ufficiale_Intestazione'] = "L'Ufficiale della riscossione";
		$firma['Ufficiale_Testo'] = $this->Ufficiale_Testo;
		if($this->Ufficiale_Firma=="" && $this->Ufficiale_Testo!="si")
		{
			$firma['Ufficiale'] = "";
			$firma['Ufficiale_Path'] = "";
			$firma['Ufficiale_Nome'] = "";
			$firma['Ufficiale_Intestazione'] = "";
			$firma['Ufficiale_Testo'] = "";
		}
		
		$firma['Responsabile_Richieste'] = $firma_path.$this->Responsabile_Richieste_Firma;
		$firma['Responsabile_Richieste_Path'] = $percorso.$this->Responsabile_Richieste_Firma;
		$firma['Responsabile_Richieste_Nome'] = $this->Responsabile_Richieste;
		$firma['Responsabile_Richieste_Intestazione'] = "Responsabile della richiesta";
		$firma['Responsabile_Richieste_Testo'] = $this->Responsabile_Richieste_Testo;
		if($this->Responsabile_Richieste_Firma=="" && $this->Responsabile_Richieste_Testo!="si")
		{
			$firma['Responsabile_Richieste'] = "";
			$firma['Responsabile_Richieste_Path'] = "";
			$firma['Responsabile_Richieste_Nome'] = "";
			$firma['Responsabile_Richieste_Intestazione'] = "";
			$firma['Responsabile_Richieste_Testo'] = "";
		}
		
		return $firma;
	}
	
	public function carica_firme($firma1, $firma2, $firma3, $firma4 = null)
	{
		$firma = $this->firme_responsabili();
		
		if($firma1!="")
		{
			$temp[1]['intestazione'] = $firma[$firma1."_Intestazione"];
			$temp[1]['nome'] = $firma[$firma1."_Nome"];
			if($firma[$firma1."_Testo"]=="si")
				$temp[1]['firma'] = $this->Testo_Sostitutivo;
			else
				$temp[1]['firma'] = $firma[$firma1];
		}
		else 
		{
			$temp[1]['intestazione'] = "";
			$temp[1]['nome'] = "";
			$temp[1]['firma'] = "";
		}
		
		if($firma2!="")
		{
			$temp[2]['intestazione'] = $firma[$firma2."_Intestazione"];
			$temp[2]['nome'] = $firma[$firma2."_Nome"];
			if($firma[$firma2."_Testo"]=="si")
				$temp[2]['firma'] = $this->Testo_Sostitutivo;
			else
				$temp[2]['firma'] = $firma[$firma2];
		}
		else 
		{
			$temp[2]['intestazione'] = "";
			$temp[2]['nome'] = "";
			$temp[2]['firma'] = "";
		}
		
		if($firma3!="")
		{
			$temp[3]['intestazione'] = $firma[$firma3."_Intestazione"];
			$temp[3]['nome'] = $firma[$firma3."_Nome"];
			if($firma[$firma3."_Testo"]=="si")
				$temp[3]['firma'] = $this->Testo_Sostitutivo;
			else
				$temp[3]['firma'] = $firma[$firma3];
		}
		else
		{
			$temp[3]['intestazione'] = "";
			$temp[3]['nome'] = "";
			$temp[3]['firma'] = "";
		}
		
		if($firma4!="")
		{
			$temp[4]['intestazione'] = $firma[$firma4."_Intestazione"];
			$temp[4]['nome'] = $firma[$firma4."_Nome"];
			if($firma[$firma4."_Testo"]=="si")
				$temp[4]['firma'] = $this->Testo_Sostitutivo;
			else
				$temp[4]['firma'] = $firma[$firma4];
		}
		else
		{
			$temp[4]['intestazione'] = "";
			$temp[4]['nome'] = "";
			$temp[4]['firma'] = "";
		}

		return $temp;
	}
	
	public function Delete ()
	{
		$query = "DELETE FROM parametri_responsabili WHERE CC = '".$this->CC."' AND Tipo_Riscossione = '".$this->Tipo_Riscossione."'";
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
	
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
	
		$query = table_insert_record_query ("parametri_responsabili", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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
	
		$query = table_update_record_query ("parametri_responsabili", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class parametri_pagamento
{
	public $ID;
	public $CC;
	public $Tipo_Riscossione;
	public $Tipo_Documento;
	public $Intestatario_Conto;
	public $Tipo_Conto;
	public $Numero_Conto;
	public $IBAN;
	public $BICSWIFT;
	public $Bollettino_1;
	public $Bollettino_2;
	public $Importo_1;
	public $Importo_1_Pignoramento;
	public $Importo_2;
	public $Importo_2_Pignoramento;
	public $Stemma;
	public $Stemma_2;
	public $Autorizzazione_1;
	public $Autorizzazione_2;
	public $Data_Autorizzazione_1;
	public $Data_Autorizzazione_2;
	public $Scadenza_Sanzione;
	public $Scadenza_Ingiunzione;
	public $Scadenza_Avviso;
	public $Scadenza_Pignoramento;
	public $Conto_Terzi;
	public $Data_Cambio_Conto;
	
	public function __construct( $c, $tipo, $tipo_doc = "no" )
	{
		$query = "SELECT * FROM parametri_pagamento WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."'";
		if($tipo_doc!="no")
			$query.= " AND Tipo_Documento = '".$tipo_doc."'";
		
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
	
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
		$this->Tipo_Documento = utf8_decode($val['Tipo_Documento']);
		$this->Intestatario_Conto = utf8_decode($val['Intestatario_Conto']);
		$this->Tipo_Conto = utf8_decode($val['Tipo_Conto']);
		$this->Numero_Conto = utf8_decode($val['Numero_Conto']);
		$this->IBAN = utf8_decode($val['IBAN']);
		$this->BICSWIFT = utf8_decode($val['BICSWIFT']);
		$this->Bollettino_1 = utf8_decode($val['Bollettino_1']);
		$this->Bollettino_2 = utf8_decode($val['Bollettino_2']);		
		$this->Importo_1 = utf8_decode($val['Importo_1']);
		$this->Importo_1_Pignoramento = utf8_decode($val['Importo_1_Pignoramento']);
		$this->Importo_2 = utf8_decode($val['Importo_2']);		
		$this->Importo_2_Pignoramento = utf8_decode($val['Importo_2_Pignoramento']);
		$this->Stemma = utf8_decode($val['Stemma']);
		$this->Stemma_2 = utf8_decode($val['Stemma_2']);
		$this->Autorizzazione_1 = utf8_decode($val['Autorizzazione_1']);
		$this->Autorizzazione_2 = utf8_decode($val['Autorizzazione_2']);
		$this->Data_Autorizzazione_1 = utf8_decode($val['Data_Autorizzazione_1']);
		$this->Data_Autorizzazione_2 = utf8_decode($val['Data_Autorizzazione_2']);
		$this->Scadenza_Sanzione = utf8_decode($val['Scadenza_Sanzione']);
		$this->Scadenza_Ingiunzione = utf8_decode($val['Scadenza_Ingiunzione']);
		$this->Scadenza_Avviso = utf8_decode($val['Scadenza_Avviso']);
		$this->Scadenza_Pignoramento = utf8_decode($val['Scadenza_Pignoramento']);
		$this->Conto_Terzi = utf8_decode($val['Conto_Terzi']);
		$this->Data_Cambio_Conto = utf8_decode($val['Data_Cambio_Conto']);
	}

    public function controlloParametri( $c, $tipo, $tipo_doc = "no" )
    {
        if($this->ID ==	null)
        {
            $query = "SELECT * FROM parametri_pagamento";

            if($tipo_doc!="no")
                $query.= " AND Tipo_Documento = '".$tipo_doc."'";

            $query.= " ORDER BY ID DESC LIMIT 1";

            $result = safe_query($query);
            if(mysql_num_rows($result)>0)
                $val = mysql_fetch_array($result);
            else
                return "ERROR";

            $this->CC = $c;
            $this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
            $this->Tipo_Documento = utf8_decode($val['Tipo_Documento']);
            $this->Intestatario_Conto = utf8_decode($val['Intestatario_Conto']);
            $this->Tipo_Conto = utf8_decode($val['Tipo_Conto']);
            $this->Numero_Conto = utf8_decode($val['Numero_Conto']);
            $this->IBAN = utf8_decode($val['IBAN']);
            $this->BICSWIFT = utf8_decode($val['BICSWIFT']);
            $this->Bollettino_1 = utf8_decode($val['Bollettino_1']);
            $this->Bollettino_2 = utf8_decode($val['Bollettino_2']);
            $this->Importo_1 = utf8_decode($val['Importo_1']);
            $this->Importo_1_Pignoramento = utf8_decode($val['Importo_1_Pignoramento']);
            $this->Importo_2 = utf8_decode($val['Importo_2']);
            $this->Importo_2_Pignoramento = utf8_decode($val['Importo_2_Pignoramento']);
            $this->Autorizzazione_1 = utf8_decode($val['Autorizzazione_1']);
            $this->Autorizzazione_2 = utf8_decode($val['Autorizzazione_2']);
            $this->Data_Autorizzazione_1 = utf8_decode($val['Data_Autorizzazione_1']);
            $this->Data_Autorizzazione_2 = utf8_decode($val['Data_Autorizzazione_2']);
            $this->Scadenza_Sanzione = utf8_decode($val['Scadenza_Sanzione']);
            $this->Scadenza_Ingiunzione = utf8_decode($val['Scadenza_Ingiunzione']);
            $this->Scadenza_Avviso = utf8_decode($val['Scadenza_Avviso']);

            $controlInsert = $this->Insert();
            if($controlInsert)
                return "NEW";
            else
                return "ERROR";

        }
        else
        {
            return "OK";
        }
    }
	
	/*public function AttribuisciConto ($comune, $tipo)
	{
		$queryConto = "SELECT ID, CC, Tipo_Riscossione from conto_corrente ";
		$queryConto .= " WHERE CC = '" . $comune . "' AND ";
		$queryConto .= " Tipo_Riscossione = '" . $tipo . "' ";
		//echo "<br>" . $queryConto;
		$resConto = mysql_query($queryConto);
		$numConti = mysql_num_rows($resConto);
		if ($numConti == 0)
		{
			alert ("Errore: non esiste conto per il comune $comune e tipo $tipo");
			return NULL;
		}
		else if ($numConti > 1)
		{
			alert ("Errore: i sono $numConti conti per il comune $comune e tipo $tipo");
			return NULL;
		}
		else
		{
			$rigaConto = mysql_fetch_assoc($resConto);
			$myContoCor = new parametri_pagamento($rigaConto['ID']);
			foreach ($myContoCor as $key => $value)
				$this->$key = $value;
		}
	}*/
	
	public function PossibiliTipiRiscossione ()  //  promemoria
	{
		switch ($this->Tipo_Riscossione)
		{
			case "CDS": break;
			case "CDSESTERO": break;
		}
	}
	
	public function gestione_conto_terzi()
	{
		if(from_mysql_date($this->Data_Cambio_Conto)!="")
		{
			if($this->Conto_Terzi=="si")
			{
				$stringa = "Non Conto Terzi prima della data di cambio ";
			}
			else 
			{
				$stringa = "Conto Terzi prima della data di cambio ";
			}
		}
		else 
			$stringa = "";
		
		return $stringa;
	}
	
	public function data_conto_terzi($data)  //  data in formato YYYY-mm-dd
	{
		if (from_mysql_date($this->Data_Cambio_Conto) != "")
		{
			if ($data < $this->Data_Cambio_Conto)
			{
				if ($this->Conto_Terzi == "si") $esito = "N";  //  "Non Conto Terzi prima della data di cambio ";
				else $esito = "Y";  //  "Conto Terzi prima della data di cambio ";
			}
			else
			{
				if ($this->Conto_Terzi == "si") $esito = "Y";  //  "Conto Terzi dopo la data di cambio ";
				else $esito = "N";  //  "Non Conto Terzi dopo la data di cambio ";
			}
		}
		else
		{
			if ($this->Conto_Terzi == "si") $esito = "Y";  //  "sempre Conto Terzi ";
			else $esito = "N";  //  "mai Conto Terzi ";
		}
		
		return $esito;
	}
	
	public function testo_autorizzazione($selezione)
	{
		if($selezione==1)
		{
			$data_auto = from_mysql_date($this->Data_Autorizzazione_1);
			$auto = $this->Autorizzazione_1;
			if($auto!="" && $data_auto!="")
			{
				return "AUT. N. ".$auto." DEL ".$data_auto;
			}
		}
		else if($selezione==2)
		{
			$data_auto = from_mysql_date($this->Data_Autorizzazione_2);
			$auto = $this->Autorizzazione_2;
			if($auto!="" && $data_auto!="")
			{
				return "AUT. N. ".$auto." DEL ".$data_auto;
			}
		}
		
		return false;
	}

	public function Delete ()
	{
		$query = "DELETE FROM parametri_pagamento WHERE ID = '".$this->ID."'";
			
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
	
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
	
		$query = table_insert_record_query ("parametri_pagamento", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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
	
		$query = table_update_record_query ("parametri_pagamento", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class parametri_generali
{
    public $ID;
    public $CC;
    public $Tipo_Riscossione;
    public $Spese_Anticipate;
    public $Testo_Spese_Anticipate;
    public $SMA;
    public $Intestatario_SMA;
    public $Numero_SMA;
    public $Restituzione1;
    public $Restituzione2;
    public $Restituzione3;
    public $Restituzione4;
    public $Restituzione5;
    public $Restituzione1_Mod23O;
    public $Restituzione2_Mod23O;
    public $Restituzione3_Mod23O;
    public $Restituzione4_Mod23O;
    public $Restituzione5_Mod23O;

    public function __construct( $c, $tipo )
    {
        $query = "SELECT * FROM parametri_generali WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."'";

        $result = safe_query($query);
        $val = mysql_fetch_array($result);

        $this->ID = utf8_decode($val['ID']);
        $this->CC = utf8_decode($val['CC']);
        $this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
        $this->Spese_Anticipate = utf8_decode($val['Spese_Anticipate']);
        $this->Testo_Spese_Anticipate = utf8_decode($val['Testo_Spese_Anticipate']);
        $this->SMA = utf8_decode($val['SMA']);
        $this->Intestatario_SMA = utf8_decode($val['Intestatario_SMA']);
        $this->Numero_SMA = utf8_decode($val['Numero_SMA']);
        $this->Restituzione1 = utf8_decode($val['Restituzione1']);
        $this->Restituzione2 = utf8_decode($val['Restituzione2']);
        $this->Restituzione3 = utf8_decode($val['Restituzione3']);
        $this->Restituzione4 = utf8_decode($val['Restituzione4']);
        $this->Restituzione5 = utf8_decode($val['Restituzione5']);
        $this->Restituzione1_Mod23O = utf8_decode($val['Restituzione1_Mod23O']);
        $this->Restituzione2_Mod23O = utf8_decode($val['Restituzione2_Mod23O']);
        $this->Restituzione3_Mod23O = utf8_decode($val['Restituzione3_Mod23O']);
        $this->Restituzione4_Mod23O = utf8_decode($val['Restituzione4_Mod23O']);
        $this->Restituzione5_Mod23O = utf8_decode($val['Restituzione5_Mod23O']);
    }

    public function Delete ()
    {
        $query = "DELETE FROM parametri_generali WHERE ID = '".$this->ID."'";

        $ctrl_query = mysql_query($query);

        return $ctrl_query;
    }

    public function Insert ()
    {
        $fields = array();
        $values = array();

        foreach ($this as $key => $value)
        {
            if ($key != "ID" && isset($value) !== false && is_array($value)===false )
            {
                $fields[] = $key;
                $values[] = $value;
            }
        }

        $query = table_insert_record_query ("parametri_generali", $fields, $values);
        $ctrl_query = mysql_query($query);

        return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
    }

    public function Update ( $valoreCampo, $campo = "ID" )
    {
        $fields = array();
        $values = array();
        foreach ($this as $key => $value)
        {
            if ($key != $campo && isset($value) !== false && is_array($value)===false)
            {
                $fields[] = $key;
                $values[] = $value;
            }
        }

        $query = table_update_record_query ("parametri_generali", $fields, $values, $campo, $valoreCampo);
        $ctrl_query = mysql_query($query);

        return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
    }
}

class parametri_atto
{
	public $ID;
	public $CC;
	public $Tipo_Protocollo;
	public $Fisso_Protocollo;
	
	public function __construct( $c )
	{
		$query = "SELECT * FROM parametri_atto WHERE CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Protocollo = utf8_decode($val['Tipo_Protocollo']);
		$this->Fisso_Protocollo = utf8_decode($val['Fisso_Protocollo']);
        $this->Data_Protocollo = utf8_decode($val['Data_Protocollo']);

	}

	public function Delete ()
	{
		$query = "DELETE FROM parametri_atto WHERE CC = '".$this->CC."'";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("parametri_atto", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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

		$query = table_update_record_query ("parametri_atto", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class parametri_ricorso
{
	public $ID;
	public $CC;
	public $Termini_Commissione_Tributaria_Provinciale;
	public $Termini_Giustizia_Ordinaria;

	public function __construct( $c )
	{
		$query = "SELECT * FROM parametri_ricorso WHERE CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Termini_Commissione_Tributaria_Provinciale = utf8_decode($val['Termini_Commissione_Tributaria_Provinciale']);
		$this->Termini_Giustizia_Ordinaria = utf8_decode($val['Termini_Giustizia_Ordinaria']);

	}

	public function Delete ()
	{
		$query = "DELETE FROM parametri_ricorso WHERE CC = '".$this->CC."'";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("parametri_ricorso", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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

		$query = table_update_record_query ("parametri_ricorso", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
	public function controlloParametri( $c )
	{
		if($this->ID ==	null)
		{
			$query = "SELECT * FROM parametri_ricorso";
				
			$query.= " ORDER BY ID DESC LIMIT 1";
				
			$result = safe_query($query);
			if(mysql_num_rows($result)>0)
				$val = mysql_fetch_array($result);
			else
				return "ERROR";
				
			$this->CC = $c;
			$this->Termini_Commissione_Tributaria_Provinciale = utf8_decode($val['Termini_Commissione_Tributaria_Provinciale']);
			$this->Termini_Giustizia_Ordinaria = utf8_decode($val['Termini_Giustizia_Ordinaria']);
				
			$controlInsert = $this->Insert();
			if($controlInsert)
				return "NEW";
			else
				return "ERROR";
		}
		else
		{
			return "OK";
		}
	}
}

class parametri_pignoramento
{
	public $ID;
	public $CC;
	public $Tipo_Protocollo;
	public $Fisso_Protocollo;

	public function __construct( $c )
	{
		$query = "SELECT * FROM parametri_pignoramento WHERE CC = '".$c."'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Protocollo = utf8_decode($val['Tipo_Protocollo']);
		$this->Fisso_Protocollo = utf8_decode($val['Fisso_Protocollo']);

	}

	public function Delete ()
	{
		$query = "DELETE FROM parametri_pignoramento WHERE CC = '".$this->CC."'";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("parametri_pignoramento", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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

		$query = table_update_record_query ("parametri_pignoramento", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class parametri_atto_intimazione_ingiunzione
{	
	public $ID = NULL;
	public $CC = NULL;
	public $Data_Creazione_Parametri = NULL;
	public $Titolo_Ingiunzione = NULL;
	public $Sottotitolo_Ingiunzione = NULL;
	public $Primo_Testo = NULL;
	public $Premesso_Testo = NULL;
	public $Secondo_Testo = NULL;
	public $Terzo_Testo = NULL;
	public $Intima = NULL;
	public $Intima_Testo = NULL;
	public $Intima_Caso_1 = NULL;
	public $Intima_Caso_2 = NULL;
	public $Intima_Caso_3 = NULL;
	public $Intima_Versamento = NULL;
	public $Info_Testo = NULL;
	public $Finale_Testo = NULL;
	public $Opposizione = NULL;
	public $Opposizione_Testo = NULL;
	public $Qualifica_Firma_Sinistra = NULL;
	public $Firma_Sinistra = NULL;
	public $Qualifica_Firma_Destra = NULL;
	public $Firma_Destra = NULL;
	public $Modalita_Stampa_Firma = NULL;
	
	public $Relazione_Titolo_Testo = NULL;
	public $Relazione_Testo_Posta = NULL;
	public $Relazione_Testo_Mani = NULL;
	public $Relazione_Testo_PEC = NULL;
	
	public $Luogo_Lettera = NULL;	
	
	public $Intestazione_Relata_Ufficiale_Riscossione = NULL;
	public $Relata_Ufficiale_Riscossione = NULL;
	
	public $Intestazione_Relata_Ufficiale_Giudiziario = NULL;
	public $Sottointestazione_Relata_Ufficiale_Giudiziario = NULL;
	public $Relata_Ufficiale_Giudiziario = NULL;
	
	public $Qualifica_Firma_Notifica = NULL;
	public $Firma_Notifica = NULL;
	
	public function __construct($myId)
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM parametri_atto_intimazione_ingiunzione WHERE ID = '" . $myId."' ";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);
		
		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}
	
	public function CercaParametroData ($CCcomune, $dataConfronto,$blocco_stampa="no")
	{
		$query = "SELECT ID FROM parametri_atto_intimazione_ingiunzione 
					WHERE CC = '" . $CCcomune . "' AND 
					Data_Creazione_Parametri <= '". $dataConfronto . "'  
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);
		
		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			alert("Il testo non e' stato personalizzato per questo comune!");
			
			if($blocco_stampa!="no")
			{
				return null;
			}
			// se non sono MAI stati inseriti parametri per questo comune, 
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM parametri_atto_intimazione_ingiunzione
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);
			
			$id = $rigaParametro['ID'];
			return $id;
		}
	}
	
	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("parametri_atto_intimazione_ingiunzione", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else 
		{
			$ret = table_insert_record_query ("parametri_atto_intimazione_ingiunzione", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("parametri_atto_intimazione_ingiunzione", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else 
		{
			$ret = table_update_record_query ("parametri_atto_intimazione_ingiunzione", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}
	
	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM parametri_atto_intimazione_ingiunzione 
						WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "' ";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);
		
		$id = $rigaId['ID'];
		
		if ($transaction == false)
		{	
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{	
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}
	
	/*
	 * CREATE TABLE `parametri_atto_intimazione_ingiunzione` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CC` varchar(4) NOT NULL DEFAULT '',
  `Data_Creazione_Parametri` date NOT NULL DEFAULT '0000-00-00',
  `Titolo_Ingiunzione` longtext NOT NULL,
  `Sottotitolo_Ingiunzione` longtext NOT NULL,
  `Primo_Testo` longtext NOT NULL,
  `Premesso_Testo` longtext NOT NULL,
  `Secondo_Testo` longtext NOT NULL,
  `Terzo_Testo` longtext NOT NULL,
  `Intima_Testo` longtext NOT NULL,
  `Quarto_Testo` longtext NOT NULL,
  `Info_Testo` longtext NOT NULL,
  `Finale_Testo` longtext NOT NULL,
  `Ufficiale_Riscossione` longtext NOT NULL,
  `Nome_Ufficiale_Riscossione` longtext NOT NULL,
  `Relazione_Titolo_Testo` longtext NOT NULL,
  `Relazione_Testo` longtext NOT NULL,
  `Luogo_Lettera` longtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
	 * */
	
}

class parametri_testo_ingiunzione
{
	public $ID = NULL;
	public $CC = NULL;
	public $Data_Creazione_Parametri = NULL;
	public $Titolo_Ingiunzione = NULL;
	public $Sottotitolo_Ingiunzione = NULL;
	public $Primo_Testo = NULL;
	public $Premesso = NULL;
	public $Premesso_Testo = NULL;
	public $Secondo_Testo = NULL;
	public $Terzo_Testo = NULL;
	public $Ingiunge = NULL;
	public $Ingiunge_Testo = NULL;
	public $Finale_Pagina_1 = NULL;
	public $Qualifica_Firma_Sinistra = NULL;
	public $Firma_Sinistra = NULL;
	public $Qualifica_Firma_Destra = NULL;
	public $Firma_Destra = NULL;
	
	public $Informazioni = NULL;
	public $Informazioni_Testo = NULL;
	
	public $Totale_1 = NULL;
	public $Testo_Totale_1 = NULL;
	public $Totale_2 = NULL;
	public $Testo_Totale_2 = NULL;
	
	public $Totale_Complessivo = NULL;
	public $Totale_Complessivo_Testo = NULL;
	public $Diritto_Riscossione = NULL;
	public $Diritto_Riscossione_Testo = NULL;
	
	public $Opposizione = NULL;
	public $Opposizione_Testo = NULL;
	public $Crediti_Tributari = NULL;
	public $Crediti_Non_Tributari = NULL;
	public $Provvedimento = NULL;
	public $Provvedimento_Testo = NULL;
	public $Esecutivita = NULL;
	public $Esecutivita_Testo = NULL;
	public $Pagamento_Primo_Testo = NULL;
	public $Pagamento_Secondo_Testo = NULL;
	public $Avvertenza_Primo_Testo = NULL;
	public $Avvertenza_Secondo_Testo = NULL;
	public $Avvertenza_Terzo_Testo = NULL;
	public $Tribunale_Primo_Testo = NULL;
	public $Relazione_Testo_Posta = NULL;
	public $Relazione_Testo_Mani = NULL;
	public $Relazione_Testo_PEC = NULL;
    public $Intestazione_Riscossione_Diretta = NULL;
    public $Riscossione_Diretta = NULL;
	public $Intestazione_Relata_Ufficiale_Riscossione = NULL;
	public $Relata_Ufficiale_Riscossione = NULL;
	public $Intestazione_Relata_Ufficiale_Giudiziario = NULL;
	public $Sottointestazione_Relata_Ufficiale_Giudiziario = NULL;
	public $Relata_Ufficiale_Giudiziario = NULL;
	public $Qualifica_Firma_Notifica = NULL;
	public $Firma_Notifica = NULL;
	public $Luogo_Lettera = NULL;
	
	public function __construct($myId)
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM parametri_testo_ingiunzione WHERE ID = '" . $myId."' ";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto, $blocco_stampa="no")
	{
		$query = "SELECT ID FROM parametri_testo_ingiunzione
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{				
			alert("Il testo non e' stato personalizzato per questo comune!");
			
			if($blocco_stampa!="no")
			{
				return null;
			}
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM parametri_testo_ingiunzione
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);
				
			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("parametri_testo_ingiunzione", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("parametri_testo_ingiunzione", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("parametri_testo_ingiunzione", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("parametri_testo_ingiunzione", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM parametri_testo_ingiunzione
						WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "' ";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class parametri_testo_preavviso_ingiunzione
{
	public $ID = NULL;
	public $CC = NULL;
	public $Data_Creazione_Parametri = NULL;
	public $Oggetto_Preavviso_Ingiunzione = NULL;
	public $Primo_Testo = NULL;
	public $Secondo_Testo = NULL;
	public $Intro_Somma_Testo = NULL;
	public $Terzo_Testo = NULL;
	public $Quarto_Testo = NULL;
	public $Quinto_Testo = NULL;
	public $Saluti_Testo = NULL;
	public $Ufficiale_Riscossione = NULL;
	public $Nome_Ufficiale_Riscossione = NULL;
	public $Ufficiale_Riscossione_2 = NULL;
	public $Nome_Ufficiale_Riscossione_2 = NULL;
	public $Stampa_Firma = NULL;
	public $Info_1_Titolo = NULL;
	public $Info_1_Testo = NULL;
	public $CDS_Titolo = NULL;
	public $CDS_Testo_1 = NULL;
	public $CDS_Testo_2 = NULL;
	public $CDS_Testo_3 = NULL;
	public $Tributo_Titolo = NULL;
	public $Tributo_Testo = NULL;
	public $Info_2_Titolo = NULL;
	public $Info_2_Testo = NULL;
	public $Info_3_Titolo = NULL;
	public $Info_3_Testo = NULL;
	
	public $Avviso_Titolo = NULL;
	public $Esito_1_Titolo = NULL;
	public $Esito_1_Testo = NULL;
	public $Caso_A_Testo = NULL;
	public $Caso_B_Testo = NULL;
	public $Caso_C_Testo = NULL;
	public $Caso_D_Testo = NULL;
	public $Esito_2_Titolo = NULL;
	public $Esito_2_Testo = NULL;
	public $Esito_3_Titolo = NULL;
	public $Esito_3_Testo = NULL;
	public $Esito_4_Titolo = NULL;
	public $Esito_4_Testo = NULL;
	
/*
 * CREATE TABLE `parametri_testo_preavviso_ingiunzione` (
 		`ID` int(11) NOT NULL AUTO_INCREMENT,
 		`CC` varchar(4) NOT NULL DEFAULT '',
 		`Data_Creazione_Parametri` date NOT NULL DEFAULT '0000-00-00',
 		`Oggetto_Preavviso_Ingiunzione` longtext NOT NULL,
 		`Primo_Testo` longtext NOT NULL,
 		`Secondo_Testo` longtext NOT NULL,
 		`Terzo_Testo` longtext NOT NULL,
 		`Intro_Somma_Testo` longtext NOT NULL,
 		`Importo_1_Testo` longtext NOT NULL,
 		`Importo_2_Testo` longtext NOT NULL,
 		`Importo_3_Testo` longtext NOT NULL,
 		`Importo_4_Testo` longtext NOT NULL,
 		`Importo_5_Testo` longtext NOT NULL,
 		`Importo_6_Testo` longtext NOT NULL,
 		`Importo_7_Testo` longtext NOT NULL,
 		`Totale_Testo` longtext NOT NULL,
 		`Quarto_Testo` longtext NOT NULL,
 		`Quinto_Testo` longtext NOT NULL,
 		`Sesto_Testo` longtext NOT NULL,
 		`Saluti_Testo` longtext NOT NULL,
 		`Ufficiale_Riscossione` longtext NOT NULL,
 		`Nome_Ufficiale_Riscossione` longtext NOT NULL,
 		`Stampa_Firma` longtext NOT NULL,
 		`Info_1_Titolo` longtext NOT NULL,
 		`Info_1_Testo` longtext NOT NULL,
 		`Info_2_Titolo` longtext NOT NULL,
 		`Info_2_Testo` longtext NOT NULL,
 		`Info_3_Titolo` longtext NOT NULL,
 		`Info_3_Testo` longtext NOT NULL,
 		`Info_4_Titolo` longtext NOT NULL,
 		`Info_4_Testo` longtext NOT NULL,
 		`Info_5_Titolo` longtext NOT NULL,
 		`Info_5_Testo` longtext NOT NULL,
 		`Avviso_Titolo` longtext NOT NULL,
 		`Esito_1_Testo` longtext NOT NULL,
 		`Caso_A_Testo` longtext NOT NULL,
 		`Caso_B_Testo` longtext NOT NULL,
 		`Caso_C_Testo` longtext NOT NULL,
 		`Caso_D_Testo` longtext NOT NULL,
 		`Caso_E_Testo` longtext NOT NULL,
 		`Esito_2_Testo` longtext NOT NULL,
 		`Esito_3_Testo` longtext NOT NULL,
 		`Esito_4_Testo` longtext NOT NULL,
 		`Esito_5_Testo` longtext NOT NULL,
 		PRIMARY KEY (`ID`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1
* */

	public function __construct($myId)
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM parametri_testo_preavviso_ingiunzione WHERE ID = '" . $myId."' ";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM parametri_testo_preavviso_ingiunzione
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM parametri_testo_preavviso_ingiunzione
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroPreavviso ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("parametri_testo_preavviso_ingiunzione", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("parametri_testo_preavviso_ingiunzione", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroPreavviso ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("parametri_testo_preavviso_ingiunzione", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("parametri_testo_preavviso_ingiunzione", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdateParametroPreavviso ($transaction = false)
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
		$queryCerca = "SELECT ID FROM parametri_testo_preavviso_ingiunzione
						WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "' ";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroPreavviso();
			else $res = $this->UpdateParametroPreavviso($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroPreavviso(true);
			else $res = $this->UpdateParametroPreavviso($id, true);
		}
		return $res;
	}

}

class parametri_testo_sollecito_ingiunzione
{
	public $ID = NULL;
	public $CC = NULL;
	public $Data_Creazione_Parametri = NULL;
	public $Oggetto = NULL;
	public $Sottotitolo = NULL;
	public $Primo_Testo = NULL;
	public $Pagamento= NULL;
	public $Coazione= NULL;
	public $Coazione_Caso_1= NULL;
	public $Coazione_Caso_2= NULL;
	public $Coazione_Caso_3= NULL;
	public $Coazione_Caso_4= NULL;
	public $Dati_Gestore= NULL;
	public $Rateizzazione= NULL;
	public $Alternativa= NULL;
	public $Informativa= NULL;
	public $Saluti= NULL;
	public $Primo_Responsabile= NULL;
	public $Nome_Primo_Responsabile= NULL;
	public $Secondo_Responsabile= NULL;
	public $Nome_Secondo_Responsabile= NULL;
	public $Firma_Autografa= NULL;

	public function __construct($myId)
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM parametri_testo_sollecito_ingiunzione WHERE ID = '" . $myId."' ";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM parametri_testo_sollecito_ingiunzione
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM parametri_testo_sollecito_ingiunzione
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("parametri_testo_sollecito_ingiunzione", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("parametri_testo_sollecito_ingiunzione", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("parametri_testo_sollecito_ingiunzione", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("parametri_testo_sollecito_ingiunzione", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM parametri_testo_sollecito_ingiunzione
						WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "' ";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class tariffe_coazione
{
	public $ID;
	public $CC;
	public $Data_Inizio;
	public $Tipo;
	public $Descrizione;
	public $Importo;
	public $Note;
	public $Deposito_Portata;
	public $Importo_Fisso;
	public $Km_Giorni_Importo_Fisso;
	public $Coefficiente;
	public $Pignoramenti;
	
	public $Una_Tantum_Group = array();
	public $A_Giorni_Group = array();
	public $A_Km_Group = array();
	
	public $Una_Tantum = array();
	public $Una_Tantum_Lavoro = array();
	public $Una_Tantum_Banca = array();
	public $Una_Tantum_Inps = array();
	public $Una_Tantum_Altro = array();
	public $Una_Tantum_Mobiliare = array();
	public $Una_Tantum_Beni = array();
	public $Una_Tantum_Immobiliare = array();
	public $Una_Tantum_Fermo = array();
	public $Una_Tantum_Veicolo = array();
	
	public $A_Giorni = array();
	public $A_Giorni_Lavoro = array();
	public $A_Giorni_Banca = array();
	public $A_Giorni_Inps = array();
	public $A_Giorni_Altro = array();
	public $A_Giorni_Mobiliare = array();
	public $A_Giorni_Beni = array();
	public $A_Giorni_Immobiliare = array();
	public $A_Giorni_Fermo = array();
	public $A_Giorni_Veicolo = array();
	
	public $A_Km = array();
	public $A_Km_Lavoro = array();
	public $A_Km_Banca = array();
	public $A_Km_Inps = array();
	public $A_Km_Altro = array();
	public $A_Km_Mobiliare = array();
	public $A_Km_Beni = array();
	public $A_Km_Immobiliare = array();
	public $A_Km_Fermo = array();
	public $A_Km_Veicolo = array();
	
	public $Array_Descrizione = array();
	
	public $Data_Tariffa;
	
	public function __construct( $ID , $c , $date = null )
	{
		$this->Data_Tariffa = $date;
		
		$query = "SELECT * FROM tariffe_coazione WHERE ID = '".$ID."' AND CC = '".$c."'";
				
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Data_Inizio = utf8_decode($val['Data_Inizio']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Descrizione = utf8_decode($val['Descrizione']);
		$this->Importo = utf8_decode($val['Importo']);
		$this->Note = utf8_decode($val['Note']);
		$this->Deposito_Portata = utf8_decode($val['Deposito_Portata']);
		$this->Importo_Fisso = utf8_decode($val['Importo_Fisso']);
		$this->Km_Giorni_Importo_Fisso = utf8_decode($val['Km_Giorni_Importo_Fisso']);
		$this->Coefficiente = utf8_decode($val['Coefficiente']);
		$this->Pignoramenti = utf8_decode($val['Pignoramenti']);
		
	}
	
	private function date_compare($a, $b)
	{
		$t1 = strtotime($a['Data_Inizio']);
		$t2 = strtotime($b['Data_Inizio']);
		return $t1 - $t2;
	}
	
	function trova_ID($products, $needle)
	{
		foreach($products as $key => $product)
		{
			if ( $product['ID'] === $needle )
				return $key;
		}
	
		return false;
	}
	
	public function array_tariffe( $c )
	{
        $query = "SELECT * FROM tariffe_coazione WHERE CC = '".$c."' LIMIT 1";
        $results = mysql_query($query);
        if(mysql_num_rows($results)==0){
            try {
                // First of all, let's begin a transaction
                mysql_query('BEGIN');

                // A set of queries; if one fails, an exception should be thrown
                $query = "CREATE TEMPORARY TABLE tmp_tariffe SELECT * from tariffe_coazione WHERE CC='*****'";
                mysql_query($query);
                $query = "ALTER TABLE tmp_tariffe drop ID, drop CC";
                mysql_query($query);
                $query = "INSERT INTO tariffe_coazione SELECT 0,'".$c."',tmp_tariffe.* FROM tmp_tariffe";
                mysql_query($query);
                $query = "DROP TABLE tmp_tariffe";
                mysql_query($query);

                // If we arrive here, it means that no exception was thrown
                // i.e. no query has failed, and we can commit the transaction
                mysql_query('COMMIT');
            } catch (Exception $e) {
                // An exception has been thrown
                // We must rollback the transaction
                mysql_query('ROLLBACK');
            }
        }


        $temp_Una_Tantum = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM'", "Descrizione" );
		$una_tantum_lavoro = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('lavoro',Pignoramenti)", "Descrizione" );
		$una_tantum_banca = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('banca',Pignoramenti)", "Descrizione" );
		$una_tantum_inps = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('inps',Pignoramenti)", "Descrizione" );
		$una_tantum_altro = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('altro',Pignoramenti)", "Descrizione" );
		$una_tantum_mobiliare = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('mobiliare',Pignoramenti)", "Descrizione" );
		$una_tantum_beni = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('beni',Pignoramenti)", "Descrizione" );
		$una_tantum_immobiliare = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('immobiliare',Pignoramenti)", "Descrizione" );
		$una_tantum_fermo = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('fermo',Pignoramenti)", "Descrizione" );
		$una_tantum_veicolo = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('veicolo',Pignoramenti)", "Descrizione" );
		$this->Una_Tantum_Group = select_mysql_array( "ID, Tipo, Descrizione, Deposito_Portata" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' GROUP BY Descrizione, Deposito_Portata", "Descrizione" );
		
		$this->Una_Tantum = $this->seleziona_tariffe($temp_Una_Tantum, $this->Data_Tariffa);
		$this->Una_Tantum_Lavoro = $this->seleziona_tariffe($una_tantum_lavoro, $this->Data_Tariffa);
		$this->Una_Tantum_Banca = $this->seleziona_tariffe($una_tantum_banca, $this->Data_Tariffa);
		$this->Una_Tantum_Inps = $this->seleziona_tariffe($una_tantum_inps, $this->Data_Tariffa);
		$this->Una_Tantum_Altro = $this->seleziona_tariffe($una_tantum_altro, $this->Data_Tariffa);
		$this->Una_Tantum_Mobiliare = $this->seleziona_tariffe($una_tantum_mobiliare, $this->Data_Tariffa);
		$this->Una_Tantum_Beni = $this->seleziona_tariffe($una_tantum_beni, $this->Data_Tariffa);
		$this->Una_Tantum_Immobiliare = $this->seleziona_tariffe($una_tantum_immobiliare, $this->Data_Tariffa);
		$this->Una_Tantum_Fermo = $this->seleziona_tariffe($una_tantum_fermo, $this->Data_Tariffa);
		$this->Una_Tantum_Veicolo = $this->seleziona_tariffe($una_tantum_veicolo, $this->Data_Tariffa);
				
		$temp_A_Giorni = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO'", "Descrizione, Deposito_Portata" );
		$a_giorni_lavoro = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('lavoro',Pignoramenti)", "Descrizione" );
		$a_giorni_banca = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('banca',Pignoramenti)", "Descrizione" );
		$a_giorni_inps = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('inps',Pignoramenti)", "Descrizione" );
		$a_giorni_altro = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('altro',Pignoramenti)", "Descrizione" );
		$a_giorni_mobiliare = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('mobiliare',Pignoramenti)", "Descrizione" );
		$a_giorni_beni = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('beni',Pignoramenti)", "Descrizione" );
		$a_giorni_immobiliare = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('immobiliare',Pignoramenti)", "Descrizione" );
		$a_giorni_fermo = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('fermo',Pignoramenti)", "Descrizione" );
		$a_giorni_veicolo = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('veicolo',Pignoramenti)", "Descrizione" );
		$this->A_Giorni_Group = select_mysql_array( "ID, Tipo, Descrizione, Deposito_Portata" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' GROUP BY Descrizione, Deposito_Portata", "Descrizione, Deposito_Portata" );;
		
		$this->A_Giorni = $this->seleziona_tariffe($temp_A_Giorni, $this->Data_Tariffa);
		$this->A_Giorni_Lavoro = $this->seleziona_tariffe($a_giorni_lavoro, $this->Data_Tariffa);
		$this->A_Giorni_Banca = $this->seleziona_tariffe($a_giorni_banca, $this->Data_Tariffa);
		$this->A_Giorni_Inps = $this->seleziona_tariffe($a_giorni_inps, $this->Data_Tariffa);
		$this->A_Giorni_Altro = $this->seleziona_tariffe($a_giorni_altro, $this->Data_Tariffa);
		$this->A_Giorni_Mobiliare = $this->seleziona_tariffe($a_giorni_mobiliare, $this->Data_Tariffa);
		$this->A_Giorni_Beni = $this->seleziona_tariffe($a_giorni_beni, $this->Data_Tariffa);
		$this->A_Giorni_Immobiliare = $this->seleziona_tariffe($a_giorni_immobiliare, $this->Data_Tariffa);
		$this->A_Giorni_Fermo = $this->seleziona_tariffe($a_giorni_fermo, $this->Data_Tariffa);
		$this->A_Giorni_Veicolo = $this->seleziona_tariffe($a_giorni_veicolo, $this->Data_Tariffa);
		
		$temp_A_Km = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM'", "Descrizione" );
		$a_km_lavoro = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('lavoro',Pignoramenti)", "Descrizione" );
		$a_km_banca = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('banca',Pignoramenti)", "Descrizione" );
		$a_km_inps = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('inps',Pignoramenti)", "Descrizione" );
		$a_km_altro = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('altro',Pignoramenti)", "Descrizione" );
		$a_km_mobiliare = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('mobiliare',Pignoramenti)", "Descrizione" );
		$a_km_beni = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('beni',Pignoramenti)", "Descrizione" );
		$a_km_immobiliare = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('immobiliare',Pignoramenti)", "Descrizione" );
		$a_km_fermo = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('fermo',Pignoramenti)", "Descrizione" );
		$a_km_veicolo = select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('veicolo',Pignoramenti)", "Descrizione" );
		$this->A_Km_Group = select_mysql_array( "ID, Tipo, Descrizione, Deposito_Portata" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' GROUP BY Descrizione, Deposito_Portata", "Descrizione" );
		
		$this->A_Km = $this->seleziona_tariffe($temp_A_Km, $this->Data_Tariffa);
		$this->A_Km_Lavoro = $this->seleziona_tariffe($a_km_lavoro, $this->Data_Tariffa);
		$this->A_Km_Banca = $this->seleziona_tariffe($a_km_banca, $this->Data_Tariffa);
		$this->A_Km_Inps = $this->seleziona_tariffe($a_km_inps, $this->Data_Tariffa);
		$this->A_Km_Altro = $this->seleziona_tariffe($a_km_altro, $this->Data_Tariffa);
		$this->A_Km_Mobiliare = $this->seleziona_tariffe($a_km_mobiliare, $this->Data_Tariffa);
		$this->A_Km_Beni = $this->seleziona_tariffe($a_km_beni, $this->Data_Tariffa);
		$this->A_Km_Immobiliare = $this->seleziona_tariffe($a_km_immobiliare, $this->Data_Tariffa);
		$this->A_Km_Fermo = $this->seleziona_tariffe($a_km_fermo, $this->Data_Tariffa);
		$this->A_Km_Veicolo = $this->seleziona_tariffe($a_km_veicolo, $this->Data_Tariffa);
		
		$this->Array_Descrizione = select_mysql_array("*" , "tariffe_coazione" , "CC = '".$c."' AND Descrizione = '".addslashes($this->Descrizione)."' AND Deposito_Portata = '".addslashes($this->Deposito_Portata)."'", "Data_Inizio", "DESC");
		
	}
	
	public function seleziona_tariffe( $array , $date )
	{		
		$array_di_selezione = array();
		
		for( $i=0; $i<count($array); $i++)
		{
			$k=0;
			$array_controllo = array();
			$array_controllo[$k] = $array[$i];
			
			for($y = $i+1; $y<count($array); $y++)
			{
				if($array[$i]['Descrizione'] == $array[$y]['Descrizione'] && $array[$i]['Deposito_Portata'] == $array[$y]['Deposito_Portata'])
				{
					$k++;
					$array_controllo[$k] = $array[$y];
				}
			}
			
			usort($array_controllo, array($this, "date_compare"));

			$control_index = "no";
			
			if( $date != null)
			{				
				for($j = 0; $j<count($array_controllo); $j++)
				{
					if( $j == count($array_controllo) - 1 )
					{
						if( $date >= $array_controllo[$j]['Data_Inizio'] )
						{
							$index_giusto = $j;
							$control_index = "si";
						}
					}
					else 
					{
						if( $array_controllo[$j+1]['Data_Inizio'] > $date && $date >= $array_controllo[$j]['Data_Inizio'] )
						{
							$index_giusto = $j;
							$control_index = "si";
							break;
						}
					}				
				}
			}
			else	
			{
				$index_giusto = count($array_controllo)-1;
				$control_index = "si";
			}
			
			
			if( $control_index == "si" )
			{
				$control_element = $this->trova_ID( $array_di_selezione , $array_controllo[$index_giusto]['ID'] );
				if($control_element === false )
				{
					$array_di_selezione[] = $array_controllo[$index_giusto];
				}
			}
		
		}
		
		return $array_di_selezione;
				
	}
	
	public function options_select_array ( $array )
	{
		$options = "";
		for($i=0;$i<count($array);$i++)
		{
			$portata = "";
			$coeff = "";
			if($array[$i]['Deposito_Portata']!="")
				$portata = " - ".$array[$i]['Deposito_Portata'];
			if($array[$i]['Coefficiente']=="no")
				$coeff = " [NO INCREMENTO]";
						
			$options.= "<option value='".$array[$i]['ID']."'>".$array[$i]['Descrizione'].$portata.$coeff."</option>";
		}
		
		return $options;
	}
	
	public function crea_tariffe_base($c)
	{
		$query = "SELECT * FROM tariffe_coazione WHERE CC = '".$c."'";
		$result = safe_query($query);
		if(mysql_num_rows($result)>0)
			return "OK";
		
		$query = "SELECT * FROM tariffe_coazione WHERE CC = '*****'";
		$result = safe_query($query);
		
		$controlInsert = false;
		while ($val = mysql_fetch_array($result)) {
			
			$this->CC = $c;
			$this->Data_Inizio = utf8_decode($val['Data_Inizio']);
			$this->Tipo = utf8_decode($val['Tipo']);
			$this->Descrizione = utf8_decode($val['Descrizione']);
			$this->Importo = utf8_decode($val['Importo']);
			$this->Note = utf8_decode($val['Note']);
			$this->Deposito_Portata = utf8_decode($val['Deposito_Portata']);
			$this->Importo_Fisso = utf8_decode($val['Importo_Fisso']);
			$this->Km_Giorni_Importo_Fisso = utf8_decode($val['Km_Giorni_Importo_Fisso']);
			$this->Coefficiente = utf8_decode($val['Coefficiente']);
			$this->Pignoramenti = utf8_decode($val['Pignoramenti']);
			
			$controlInsert = $this->Insert();
			if($controlInsert)
			{
				
			}
			else 
				return "ERROR";
			
		}
		
		if($controlInsert)
			return "NEW";
		else
			return "ERROR";		
		
		
	}
	
	public function Delete ()
	{
		$query = "DELETE FROM tariffe_coazione WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;
	}
	
	public function Insert ()
	{
		$fields = array();
		$values = array();
	
		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
	
		$query = table_insert_record_query ("tariffe_coazione", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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
	
		$query = table_update_record_query ("tariffe_coazione", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);
	
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
	
}

class coefficiente_coazione
{
	public $ID;
	public $CC;
	public $Percentuale;
	public $Credito_Minimo;
	public $Credito_Massimo;
	
	public function __construct( $c = "*****" , $credito )
	{
	
		$query = "SELECT * FROM coefficiente_coazione WHERE CC = '".$c."' AND ( ( Credito_Minimo <= ".$credito." AND Credito_Massimo >= ".$credito." ) OR ( Credito_Massimo = 0 AND Credito_Minimo <= ".$credito." ))";
	
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
	
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Percentuale = utf8_decode($val['Percentuale']);
		$this->Credito_Minimo = utf8_decode($val['Credito_Minimo']);
		$this->Credito_Massimo = utf8_decode($val['Credito_Massimo']);
	
	}
	
}

class parametri_notifica
{
	public $ID;
	public $CC;
	public $Tipo_Dato;
	public $Tipo;
	public $Descrizione;
	public $Articolo;
	
	public $Mode_A_Mani;
	public $Mode_Per_Posta;
	public $Mode_Eccezionali;
	
	public $Stati;
	
	public $Motivi;
	
	public $Tipo_Mercurio;
	public $Stato_Mercurio;
	
	public $BloccoCoattiva;

	public function __construct( $ID , $c = "*****" )
	{
	
		$query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND ID = '".$ID."'";
	
		$result = safe_query($query);
		$val = mysql_fetch_array($result);
		
		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Dato = utf8_decode($val['Tipo_Dato']);
		$this->Tipo = utf8_decode($val['Tipo']);
		$this->Descrizione = utf8_decode($val['Descrizione']);
		$this->Articolo = utf8_decode($val['Articolo']);
		$this->Collegamento = utf8_decode($val['Collegamento']);
	}
	
	public function array_notifica( $c = "*****" )
	{
		
		$this->Mode_A_Mani = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'A mani'", "Descrizione" );
		$this->Mode_Per_Posta = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Per posta'", "Descrizione" );
		$this->Mode_Eccezionali = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Eccezionali'", "Descrizione" );
		
		$this->Stati = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'stato'", "Descrizione" );
		
		$this->Motivi = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'motivo'", "Descrizione" );
		
		$this->Tipo_Mercurio = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'tipo_importato'", "ID" );
		$this->Stato_Mercurio = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'stato_importato'", "ID" );
		
		$this->BloccoCoattiva = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'blocco'", "Descrizione" );
		
	}
	
	function searchForId($id, $array, $index) {
		foreach ($array as $key => $val) {
			if ($val[$index] === $id) {
				return $key;
			}
		}
		return null;
	}
	
	public function cerca_stati_mercurio ($codice_tipo, $codice_stato)
	{
		$return = null; 
		$array_tipo = $this->Tipo_Mercurio;
		$array_stato = $this->Stato_Mercurio;
		
		if($codice_tipo!=null)
		{
			$id_tipo = $this->searchForId($codice_tipo, $array_tipo, 'Articolo');
			if(array_key_exists($id_tipo, $array_tipo))
			{
				$tipo_mercurio = new parametri_notifica($array_tipo[$id_tipo]['Collegamento']);
				$return['Tipo'] = $tipo_mercurio->Tipo_Dato;
				$return['ID_Tipo'] = $tipo_mercurio->ID;
			}
			else $return['Tipo'] = "ERRORE";
		}
		else $return['Tipo'] = "";
		
		if($codice_stato!=null)
		{
			$id_stato = $this->searchForId($codice_stato, $array_stato, 'Articolo');
			
			if(array_key_exists($id_stato, $array_stato))
			{
				$stato_mercurio = new parametri_notifica($array_stato[$id_stato]['Collegamento']);
				$return['Stato'] = $stato_mercurio->Tipo_Dato;
				$return['ID_Stato'] = $stato_mercurio->ID;
			}
			else $return['Stato'] = "ERRORE";
		}
		else $return['Stato'] = "";
		
		return $return;
		
	}
	
}

/*
CREATE TABLE `conto_corrente` (
`ID` int(10) NOT NULL AUTO_INCREMENT,
`CC` varchar(5) NOT NULL,
`Tipo_Riscossione` varchar(50) NOT NULL,
`Descrizione` varchar(100) NOT NULL,
`Tipo_CC` varchar(50) NOT NULL,
`Numero_CC` varchar(30) NOT NULL,
`IBAN` varchar(40) NOT NULL,
`BICSWIFT` varchar(30) NOT NULL,
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
*/
/*
class conto_corrente
{
	public $ID;
	public $CC;
	public $Tipo_Riscossione;
	public $Descrizione;
	public $Tipo_CC;
	public $Numero_CC;
	public $IBAN;
	public $BICSWIFT;

	public function __construct( $progr )
	{
		if ($progr == NULL) return;

		$query = "SELECT * FROM conto_corrente WHERE ID = '" . $progr . "'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
		$this->Descrizione = utf8_decode($val['Descrizione']);
		$this->Tipo_CC = utf8_decode($val['Tipo_CC']);
		$this->Numero_CC = utf8_decode($val['Numero_CC']);
		$this->IBAN = utf8_decode($val['IBAN']);
		$this->BICSWIFT = utf8_decode($val['BICSWIFT']);
	}
	
	public function AttribuisciConto ($comune, $tipo)
	{
		$queryConto = "SELECT ID from conto_corrente ";
		$queryConto .= " WHERE CC = '" . $comune . "' AND ";
		$queryConto .= " Tipo_Riscossione = '" . $tipo . "' ";
		//echo "<br>" . $queryConto;
		$resConto = mysql_query($queryConto);
		$numConti = mysql_num_rows($resConto);
		if ($numConti == 0)
		{
			alert ("Errore: non esiste conto per il comune $comune e tipo $tipo");
			return NULL;
		}
		else if ($numConti > 1)
		{
			alert ("Errore: i sono $numConti conti per il comune $comune e tipo $tipo");
			return NULL;
		}
		else
		{
			$rigaConto = mysql_fetch_assoc($resConto);
			$myContoCor = new conto_corrente($rigaConto['ID']);
			foreach ($myContoCor as $key => $value)
				$this->$key = $value;
		}
	}
	
	public function PossibiliTipiRiscossione ()
	{
		switch ($this->Tipo_Riscossione)
		{
			case "CDSESTERO": break;
		}
	}
}
*/
class tipo_riscossione
{
	public $ID;
	public $Riscossione;
	
	public function __construct( $riscossione )
	{
		$query = "SELECT * FROM riscossioni WHERE Riscossione = '" . $riscossione . "'";
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->Riscossione = utf8_decode($val['Riscossione']);
	}
	
}

class testo_pignoramento_presso_banca
{
	//TABELLA 1

	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Titolo_Oggetto = null;
	public $Sottotitolo_Oggetto = null;
	public $Ufficiale_Responsabile = null;
	public $Abilitazione = null;

	public $Premesso = null;
	public $Premesso_Testo = null;

	public $Atti_Notificati = null;
	public $Modalita_Pagamento = null;
	public $Modalita_Pagamento_Testo = null;

	public $Informazioni = null;
	public $Informazioni_Testo = null;

	public $Visto = null;
	public $Ingiunzione_Fiscale = null;
	public $Legislatore = null;

	public $Considerato = null;
	public $Terzo = null;
	public $Somme_Dovute = null;
	public $Ordine_Pagamento = null;

	public $Opposizione = null;
	public $Opposizione_Testo = null;
	public $Autotutela = null;
	public $Autotutela_Testo = null;
	public $Luogo = null;
	public $Intestazione_Firma_Sinistra = null;
	public $Firma_Sinistra = null;
	public $Intestazione_Firma_Destra = null;
	public $Firma_Destra = null;

	//TABELLA 2

	public $Ufficiale_Pignoramento = null;
	public $Assoggetto_Pignoramento = null;
	public $Assoggetto_Pignoramento_Testo = null;
	public $Ordina = null;
	public $Ordina_Testo = null;
	public $Informo = null;
	public $Informo_Testo = null;
	public $Informo_Notifica = null;
	public $Intimo = null;
	public $Intimo_Testo = null;
	public $Informo_2 = null;
	public $Informo_Testo_2 = null;
	public $Invito = null;
	public $Invito_Testo = null;
	public $Invito_Testo_2 = null;
	public $Notifica_Pignoramento = null;

	public $Intestazione_Relata_Ufficiale_Giudiziario = null;
	public $Sottointestazione_Relata_Ufficiale_Giudiziario = null;

	public $Intestazione_Relata_Ufficiale_Riscossione = null;
	public $Sottointestazione_Relata_Ufficiale_Riscossione = null;

	public $Relata_Notifica = null;
	public $Relata_Debitore = null;
	public $Relata_Terzo = null;

	public $Intestazione_Firma_Notifica = null;
	public $Firma_Notifica = null;


	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_pignoramento_presso_banca WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}

		$query = "SELECT * FROM testo_pignoramento_presso_banca_2 WHERE Collegamento_ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			if($key!="ID" && $key!="Collegamento_ID")
				$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_pignoramento_presso_banca
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_pignoramento_presso_banca
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ()
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento")
				break;
				
			if ($key != "ID" && isset($value) != false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$fields2 = array();
		$values2 = array();
		$control_key = 0;
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento" && $control_key == 0)
			{
				$control_key = 1;
			}

			if($control_key==1)
			{
				if ($key != "ID" && isset($value) != false )
				{
					$fields2[] = $key;
					$values2[] = $value;
				}
			}
		}

		$ret = table_insert_record ("testo_pignoramento_presso_banca", $fields, $values);
		$fields2[] = "Collegamento_ID";
		$values2[] = $ret;
		$ret2 = table_insert_record ("testo_pignoramento_presso_banca_2", $fields2, $values2);

		return $ret2;  // ritorna l'id inserito

	}

	public function UpdateParametroAtto ($valoreCampo, $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento")
				break;
				
			if ($key != $campo && isset($value) != false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$fields2 = array();
		$values2 = array();
		$control_key = 0;
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento" && $control_key == 0)
			{
				$control_key = 1;
				$fields2[] = "Collegamento_ID";
				$values2[] = $valoreCampo;
			}

			if($control_key==1)
			{
				if ($key != $campo && isset($value) != false )
				{
					$fields2[] = $key;
					$values2[] = $value;
				}
			}
		}

		$ret = table_update_record ("testo_pignoramento_presso_banca", $fields, $values, $campo, $valoreCampo);

		$query = "SELECT ID FROM testo_pignoramento_presso_banca_2 WHERE Collegamento_ID = '".$valoreCampo."'";
		$ID2 = single_query($query);

		$ret2 = table_update_record ("testo_pignoramento_presso_banca_2", $fields2, $values2, $campo, $ID2);

		return $ret2;  // ritorna l'id inserito

	}

	public function InsertOrUpdatesParametroAtto ()
	{
		$queryCerca = "SELECT ID FROM testo_pignoramento_presso_banca
						WHERE CC = '".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($id == NULL) $res = $this->InsertParametroAtto();
		else $res = $this->UpdateParametroAtto($id);

		return $res;
	}

}

class testo_pignoramento_presso_lavoro
{
	//TABELLA 1
	
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Titolo_Oggetto = null;
	public $Sottotitolo_Oggetto = null;
	public $Ufficiale_Responsabile = null;
	public $Abilitazione = null;

	public $Premesso = null;
	public $Premesso_Testo = null;

	public $Atti_Notificati = null;
	public $Modalita_Pagamento = null;
	public $Modalita_Pagamento_Testo = null;
	
	public $Informazioni = null;
	public $Informazioni_Testo = null;

	public $Visto = null;
	public $Ingiunzione_Fiscale = null;
	public $Legislatore = null;
	
	public $Considerato = null;	
	public $Terzo = null;
	public $Somme_Dovute = null;
	public $Ordine_Pagamento = null;
	
	public $Opposizione = null;
	public $Opposizione_Testo = null;
	public $Autotutela = null;
	public $Autotutela_Testo = null;
	public $Luogo = null;
	public $Intestazione_Firma_Sinistra = null;
	public $Firma_Sinistra = null;
	public $Intestazione_Firma_Destra = null;
	public $Firma_Destra = null;

	//TABELLA 2
	
	public $Ufficiale_Pignoramento = null;
	public $Assoggetto_Pignoramento = null;
	public $Assoggetto_Pignoramento_Testo = null;
	public $Ordina = null;
	public $Ordina_Testo = null;
	public $Informo = null;
	public $Informo_Testo = null;
	public $Informo_Notifica = null;
	public $Intimo = null;
	public $Intimo_Testo = null;
	public $Informo_2 = null;
	public $Informo_Testo_2 = null;
	public $Invito = null;
	public $Invito_Testo = null;
	public $Notifica_Pignoramento = null;
	
	public $Intestazione_Relata_Ufficiale_Giudiziario = null;
	public $Sottointestazione_Relata_Ufficiale_Giudiziario = null;
	
	public $Intestazione_Relata_Ufficiale_Riscossione = null;
	public $Sottointestazione_Relata_Ufficiale_Riscossione = null;
	
	public $Relata_Notifica = null;
	public $Relata_Debitore = null;
	public $Relata_Terzo = null;
	
	public $Intestazione_Firma_Notifica = null;
	public $Firma_Notifica = null;
	
	
public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_pignoramento_presso_lavoro WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
		
		$query = "SELECT * FROM testo_pignoramento_presso_lavoro_2 WHERE Collegamento_ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);
		
		foreach ($rigaAtto as $key => $value)
		{
			if($key!="ID" && $key!="Collegamento_ID")
				$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto, $blocco_stampa = "no")
	{
		$query = "SELECT ID FROM testo_pignoramento_presso_lavoro
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			alert("Il testo non e' stato personalizzato per questo comune!");
			
			if($blocco_stampa!="no")
			{
				return null;
			}
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_pignoramento_presso_lavoro
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ()
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento")
				break;
			
			if ($key != "ID" && isset($value) != false )
			{				
				$fields[] = $key;
				$values[] = $value;
			}
		}
		
		$fields2 = array();
		$values2 = array();
		$control_key = 0;
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento" && $control_key == 0)
			{
				$control_key = 1;
			}
				
			if($control_key==1)
			{
				if ($key != "ID" && isset($value) != false )
				{
					$fields2[] = $key;
					$values2[] = $value;
				}
			}
		}
		
		$ret = table_insert_record ("testo_pignoramento_presso_lavoro", $fields, $values);
		$fields2[] = "Collegamento_ID";
		$values2[] = $ret;
		$ret2 = table_insert_record ("testo_pignoramento_presso_lavoro_2", $fields2, $values2);
		
		return $ret2;  // ritorna l'id inserito

	}

	public function UpdateParametroAtto ($valoreCampo, $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento")
				break;
			
			if ($key != $campo && isset($value) != false )
			{				
				$fields[] = $key;
				$values[] = $value;
			}
		}
		
		$fields2 = array();
		$values2 = array();
		$control_key = 0;
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento" && $control_key == 0)
			{
				$control_key = 1;
				$fields2[] = "Collegamento_ID";
				$values2[] = $valoreCampo;
			}
				
			if($control_key==1)
			{
				if ($key != $campo && isset($value) != false )
				{
					$fields2[] = $key;
					$values2[] = $value;
				}
			}
		}

		$ret = table_update_record ("testo_pignoramento_presso_lavoro", $fields, $values, $campo, $valoreCampo);
		
		$query = "SELECT ID FROM testo_pignoramento_presso_lavoro_2 WHERE Collegamento_ID = '".$valoreCampo."'";
		$ID2 = single_query($query);
		
		$ret2 = table_update_record ("testo_pignoramento_presso_lavoro_2", $fields2, $values2, $campo, $ID2);
		
		return $ret2;  // ritorna l'id inserito

	}

	public function InsertOrUpdatesParametroAtto ()
	{
		$queryCerca = "SELECT ID FROM testo_pignoramento_presso_lavoro
						WHERE CC = '".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($id == NULL) $res = $this->InsertParametroAtto();
		else $res = $this->UpdateParametroAtto($id);

		return $res;
	}
	

}

class testo_richiesta_rateizzazione
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Oggetto = null;
	public $Sottoscritto = null;
	public $Atto_Testo = null;
	public $Chiedo = null;
	public $Chiedo_Testo = null;
	public $Condizioni_Disagiate = null;
	public $Condizione_1 = null;
	public $Condizione_2 = null;
	public $Condizione_3 = null;
	public $Condizione_4 = null;
	public $Condizione_5 = null;

	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_richiesta_rateizzazione WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_richiesta_rateizzazione WHERE CC = '" . $CCcomune . "' ";
		$query.= "AND Data_Creazione_Parametri <= '". $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_richiesta_rateizzazione WHERE Data_Creazione_Parametri <= '". $dataConfronto . "' ";
			$query.= "ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_richiesta_rateizzazione", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_richiesta_rateizzazione", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_richiesta_rateizzazione", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_richiesta_rateizzazione", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_richiesta_rateizzazione	WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class testo_esito_rateizzazione
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Oggetto = null;
	public $Richiesta = null;
	public $Richiesta_Negata = null;
	public $Richiesta_Accolta = null;
	public $Testo_Richiesta_Accolta = null;
	public $Firma_Incaricato = null;

	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_esito_rateizzazione WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_esito_rateizzazione WHERE CC = '" . $CCcomune . "' ";
		$query.= "AND Data_Creazione_Parametri <= '". $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_esito_rateizzazione WHERE Data_Creazione_Parametri <= '". $dataConfronto . "' ";
			$query.= "ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_esito_rateizzazione", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_esito_rateizzazione", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_esito_rateizzazione", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_esito_rateizzazione", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_esito_rateizzazione	WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class testo_richiesta_matricole	
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Luogo_Data = null;
	public $Oggetto = null;
	public $Descrizione = null;
	public $Richiesta = null;
	public $Legge = null;
	public $PEC = null;
	public $Saluti = null;
	public $Intestazione_Firma = null;
	public $Firma = null;

	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_richiesta_matricole WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_richiesta_matricole WHERE CC = '" . $CCcomune . "' ";
		$query.= "AND Data_Creazione_Parametri <= '". $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_richiesta_matricole WHERE Data_Creazione_Parametri <= '". $dataConfronto . "' ";
			$query.= "ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_richiesta_matricole", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_richiesta_matricole", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_richiesta_matricole", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_richiesta_matricole", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_richiesta_matricole	WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class testo_richiesta_indirizzo
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Oggetto = null;
	public $Premessa = null;
	public $Informazioni = null;
	public $Motivazione = null;
	public $Richiesta_Utente = null;
	public $Richiesta_Siatel = null;
	public $Contatti = null;
	public $Informativa_Richiesta = null;
	public $Saluti = null;
	public $Avvertenze = null;
	public $Intestatario_Firma = null;
	public $Firma = null;

	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_richiesta_indirizzo WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_richiesta_indirizzo WHERE CC = '" . $CCcomune . "' ";
		$query.= "AND Data_Creazione_Parametri <= '". $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_richiesta_indirizzo WHERE Data_Creazione_Parametri <= '". $dataConfronto . "' ";
			$query.= "ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_richiesta_indirizzo", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_richiesta_indirizzo", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_richiesta_indirizzo", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_richiesta_indirizzo", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_richiesta_indirizzo	WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class testo_richiesta_decesso
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Oggetto = null;
	public $Premessa = null;
	public $Informazioni = null;
	public $Richiesta_Certificato = null;
	public $Contatti = null;
	public $Informativa_Richiesta = null;
	public $Saluti = null;
	public $Avvertenze = null;
	public $Intestatario_Firma = null;
	public $Firma = null;
	
	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_richiesta_decesso WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_richiesta_decesso WHERE CC = '" . $CCcomune . "' ";
		$query.= "AND Data_Creazione_Parametri <= '". $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_richiesta_decesso WHERE Data_Creazione_Parametri <= '". $dataConfronto . "' ";
			$query.= "ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_richiesta_decesso", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_richiesta_decesso", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_richiesta_decesso", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_richiesta_decesso", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_richiesta_decesso	WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}


class testo_richiesta_duplicato_AR
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Oggetto = null;
	public $Premessa = null;
	public $Informazioni = null;
	public $Richiesta_Duplicato = null;
	public $Urgenza_Richiesta = null;
	public $Contatti = null;
	public $Saluti = null;
	public $Avvertenze = null;
	public $Intestatario_Firma = null;
	public $Firma = null;

	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_richiesta_duplicato_AR WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_richiesta_duplicato_AR WHERE CC = '" . $CCcomune . "' ";
		$query.= "AND Data_Creazione_Parametri <= '". $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_richiesta_duplicato_AR WHERE Data_Creazione_Parametri <= '". $dataConfronto . "' ";
			$query.= "ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_richiesta_duplicato_AR", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_richiesta_duplicato_AR", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_richiesta_duplicato_AR", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_richiesta_duplicato_AR", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_richiesta_duplicato_AR	WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class parametri_email
{
	
	public $ID;
	public $CC;
	public $Tipo_Riscossione;	
	public $Tipo_Email;
	public $Indirizzo_Email;
	public $Nome_Utente;
	public $Password;
	public $Nome_Visualizzato;
	public $Sicurezza_Connessione;
	public $Server_Posta_Arrivo;
	public $Protocollo_Arrivo;
	public $Porta_Arrivo;
	public $Server_Posta_Uscita;
	public $Protocollo_Uscita;
	public $Porta_Uscita;
	public $Autenticazione_Uscita;
	public $Nome_Utente_Uscita;
	public $Password_Uscita;
	
	public function __construct( $c, $tipo_riscossione=null, $tipo_email )
	{
		$query = "SELECT * FROM parametri_email WHERE CC = '".$c."' AND Tipo_Email = '".$tipo_email."' ";
		if($tipo_riscossione!=null)
		    $query.= "AND Tipo_Riscossione = '".$tipo_riscossione."' LIMIT 1";
		
		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
		$this->Tipo_Email = utf8_decode($val['Tipo_Email']);
		$this->Indirizzo_Email = utf8_decode($val['Indirizzo_Email']);
		$this->Nome_Utente = utf8_decode($val['Nome_Utente']);
		$this->Password = utf8_decode($val['Password']);
		$this->Nome_Visualizzato = utf8_decode($val['Nome_Visualizzato']);
		$this->Sicurezza_Connessione = utf8_decode($val['Sicurezza_Connessione']);
		$this->Server_Posta_Arrivo = utf8_decode($val['Server_Posta_Arrivo']);
		$this->Protocollo_Arrivo = utf8_decode($val['Protocollo_Arrivo']);
		$this->Porta_Arrivo = utf8_decode($val['Porta_Arrivo']);
		$this->Server_Posta_Uscita = utf8_decode($val['Server_Posta_Uscita']);
		$this->Protocollo_Uscita = utf8_decode($val['Protocollo_Uscita']);
		$this->Porta_Uscita = utf8_decode($val['Porta_Uscita']);
		$this->Autenticazione_Uscita = utf8_decode($val['Autenticazione_Uscita']);
		$this->Nome_Utente_Uscita = utf8_decode($val['Nome_Utente_Uscita']);
		$this->Password_Uscita = utf8_decode($val['Password_Uscita']);
	
	}

	public function Delete ()
	{
		$query = "DELETE FROM parametri_email WHERE CC = '".$this->CC."' AND Tipo_Riscossione = '".$this->Tipo_Riscossione."'";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("parametri_email", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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

		$query = table_update_record_query ("parametri_email", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class parametri_spedizione
{

	public $ID;
	public $CC;
	public $Tipo_Riscossione;
	public $Invio_Terzi;
	public $Invio_Pignorato;
	public $Invio_Richieste_Validazione;

	public function __construct( $c, $tipo_riscossione )
	{
		$query = "SELECT * FROM parametri_spedizione WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo_riscossione."' ";

		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC = utf8_decode($val['CC']);
		$this->Tipo_Riscossione = utf8_decode($val['Tipo_Riscossione']);
		$this->Invio_Terzi = utf8_decode($val['Invio_Terzi']);
		$this->Invio_Pignorato = utf8_decode($val['Invio_Pignorato']);
		$this->Invio_Richieste_Validazione = utf8_decode($val['Invio_Richieste_Validazione']);
	}

	public function Delete ()
	{
		$query = "DELETE FROM parametri_spedizione WHERE CC = '".$this->CC."' AND Tipo_Riscossione = '".$this->Tipo_Riscossione."'";
		$ctrl_query = mysql_query($query);

		return $ctrl_query;
	}

	public function Insert ()
	{
		$fields = array();
		$values = array();

		foreach ($this as $key => $value)
		{
			if ($key != "ID" && isset($value) != false && is_array($value)===false )
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}

		$query = table_insert_record_query ("parametri_spedizione", $fields, $values);
		$ctrl_query = mysql_query($query);
			
		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
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

		$query = table_update_record_query ("parametri_spedizione", $fields, $values, $campo, $valoreCampo);
		$ctrl_query = mysql_query($query);

		return $ctrl_query;  // ritorna true o false (se va a buon fine la prova o meno)
	}
}

class testo_pignoramento_veicolo
{
	//TABELLA 1
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;
	
	public $Titolo_Oggetto = null;
	public $Sottotitolo_Oggetto = null;
	public $Intestazione_Pignoramento = null;
	
	public $Ufficiale_Responsabile = null;
	public $Legale_Rappresentante_Comune = null;
	public $Legale_Rappresentante_Concessionario = null;
	
	public $Premesso = null;
	public $Atti_Notificati = null;
	public $Premesso_Testo = null;

	public $Informazioni = null;
	public $Informazioni_Testo = null;
	public $Informo = null;
	public $Conto_Corrente = null;
	public $Informo_Testo = null;
	public $Informo_Testo_2 = null;
	public $Informo_Testo_3 = null;
	public $Informo_Testo_4 = null;
	
	public $Considerato = null;
	public $Ingiunzione_Fiscale = null;
	public $Legislatore = null;
	public $Dati_Veicolo = null;

	
	public $Premesso_Considerato = null;
	public $Opposizione_Testo = null;
	public $Beni_Strumentali_Testo = null;
	public $Valutazione_Strumentale = null;
	public $Autotutela_Testo = null;
	public $Recupero_Somme = null;
	public $Notifica_Istituto = null;
	
	public $Luogo = null;
	
	//TABELLA 2
	
	public $Ufficiale_Pignoramento = null;
	
	public $Assoggetto_Pignoramento = null;
	public $Assoggetto_Testo = null;
	
	public $Ingiungo = null;
	public $Ingiungo_Testo = null;
	
	public $Invito = null;
	public $Invito_Testo = null;
	
	public $Avverto = null;
	public $Avverto_Testo = null;
	
	public $Intimo = null;
	public $Intimo_Testo = null;

	public $Comunico = null;
	public $Comunico_Testo_1 = null;
	public $Comunico_Testo_2 = null;
	
	public $Intestazione_Relata_Ufficiale_Giudiziario = NULL;
	public $Sottointestazione_Relata_Ufficiale_Giudiziario = NULL;
	
	public $Intestazione_Relata_Ufficiale_Riscossione = NULL;
	public $Sottointestazione_Relata_Ufficiale_Riscossione = NULL;
	
	public $Relata_Ufficiale = NULL;
	public $Relata_Notifica = NULL;
	public $Relata_Debitore = NULL;
	public $Relata_Terzo = NULL;
	
	public $Qualifica_Firma_Notifica = NULL;
	public $Firma_Notifica = NULL;	
	
	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_pignoramento_veicolo WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
		
		$query = "SELECT * FROM testo_pignoramento_veicolo_2 WHERE Collegamento_ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);
		
		foreach ($rigaAtto as $key => $value)
		{
			if($key!="ID" && $key!="Collegamento_ID")
				$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto, $blocco_stampa = "no")
	{
		$query = "SELECT ID FROM testo_pignoramento_veicolo
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			alert("Il testo non e' stato personalizzato per questo comune!");
			
			if($blocco_stampa!="no")
			{
				return null;
			}
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_pignoramento_veicolo
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ()
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento")
				break;
			
			if ($key != "ID" && isset($value) != false )
			{				
				$fields[] = $key;
				$values[] = $value;
			}
		}
		
		$fields2 = array();
		$values2 = array();
		$control_key = 0;
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento" && $control_key == 0)
			{
				$control_key = 1;
			}
				
			if($control_key==1)
			{
				if ($key != "ID" && isset($value) != false )
				{
					$fields2[] = $key;
					$values2[] = $value;
				}
			}
		}
		
		$ret = table_insert_record ("testo_pignoramento_veicolo", $fields, $values);
		$fields2[] = "Collegamento_ID";
		$values2[] = $ret;
		$ret2 = table_insert_record ("testo_pignoramento_veicolo_2", $fields2, $values2);
		
		return $ret2;  // ritorna l'id inserito

	}

	public function UpdateParametroAtto ($valoreCampo, $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento")
				break;
			
			if ($key != $campo && isset($value) != false )
			{				
				$fields[] = $key;
				$values[] = $value;
			}
		}
		
		$fields2 = array();
		$values2 = array();
		$control_key = 0;
		foreach ($this as $key => $value)
		{
			if($key=="Ufficiale_Pignoramento" && $control_key == 0)
			{
				$control_key = 1;
				$fields2[] = "Collegamento_ID";
				$values2[] = $valoreCampo;
			}
				
			if($control_key==1)
			{
				if ($key != $campo && isset($value) != false )
				{
					$fields2[] = $key;
					$values2[] = $value;
				}
			}
		}

		$ret = table_update_record ("testo_pignoramento_veicolo", $fields, $values, $campo, $valoreCampo);
		
		$query = "SELECT ID FROM testo_pignoramento_veicolo_2 WHERE Collegamento_ID = '".$valoreCampo."'";
		$ID2 = single_query($query);
		
		$ret2 = table_update_record ("testo_pignoramento_veicolo_2", $fields2, $values2, $campo, $ID2);
		
		return $ret2;  // ritorna l'id inserito

	}

	public function InsertOrUpdatesParametroAtto ()
	{
		$queryCerca = "SELECT ID FROM testo_pignoramento_veicolo
						WHERE CC = '".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($id == NULL) $res = $this->InsertParametroAtto();
		else $res = $this->UpdateParametroAtto($id);

		return $res;
	}

}

class testo_preavviso_fermo
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Titolo_Oggetto = null;
	public $Sottotitolo_Oggetto = null;
	
	public $Atti_Notificati = null;
	
	public $Sensi_Legge = null;
	
	public $Comunica = null;
	public $Comunica_Testo = null;
	
	public $Legale_Rappresentante_Comune = null;
	public $Legale_Rappresentante_Concessionario = null;

	public $Premesso = null;
	public $Premesso_Testo = null;
	
	public $Veicoli = null;
	public $Iscrizione = null;
	public $Sanzioni = null;
	public $Cancellazione = null;
	

	public $Opposizione = null;
	public $Opposizione_Testo = null;

	public $Beni_Strumentali = null;
	public $Beni_Strumentali_Testo = null;

	public $Autotutela = null;
	public $Autotutela_Testo = null;
	
	public $Qualifica_Firma_Notifica = NULL;
	public $Firma_Notifica = NULL;

	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_preavviso_fermo WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_preavviso_fermo
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_preavviso_fermo
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_preavviso_fermo", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_preavviso_fermo", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_preavviso_fermo", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_preavviso_fermo", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_preavviso_fermo
						WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class testo_sollecito_pignoramento_veicolo
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Titolo_Oggetto = null;
	public $Testo_Principale = null;
	public $Articolo_388 = null;
	public $Importo_Dovuto = null;
	public $Informazioni_Ufficio = null;
	public $Atto_Informale = null;
	public $Testo_Finale = null;
	public $Sistema_Automatizzato = null;	
	
	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_sollecito_pignoramento_veicolo WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_sollecito_pignoramento_veicolo
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_sollecito_pignoramento_veicolo
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_sollecito_pignoramento_veicolo", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_sollecito_pignoramento_veicolo", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_sollecito_pignoramento_veicolo", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_sollecito_pignoramento_veicolo", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_sollecito_pignoramento_veicolo
						WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}

class testo_archiviazione_atto
{
	public $ID = null;
	public $CC = null;
	public $Data_Creazione_Parametri = null;

	public $Oggetto = null;
	public $Sottotitolo_Oggetto = null;
	public $Scrivente = null;
	public $Premesso = null;
	public $Premesso_Testo = null;
	public $Risultanze_Ufficio = null;
	public $Comunica = null;
	public $Comunica_Testo = null;
	public $Informazioni = null;
	public $Informazioni_Testo = null;

	public function __construct( $myId )
	{
		if ($myId == NULL) return;
		$query = "SELECT * FROM testo_archiviazione_atto WHERE ID = '" . $myId."'";
		$result = safe_query($query);
		$rigaAtto = mysql_fetch_assoc($result);

		foreach ($rigaAtto as $key => $value)
		{
			$this->$key = utf8_decode($value);
		}
	}

	public function CercaParametroData ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM testo_archiviazione_atto WHERE CC = '" . $CCcomune . "' ";
		$query.= "AND Data_Creazione_Parametri <= '". $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
		$result = safe_query($query);
		$rigaParametro = mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != "")
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_archiviazione_atto WHERE Data_Creazione_Parametri <= '". $dataConfronto . "' ";
			$query.= "ORDER BY Data_Creazione_Parametri DESC";
			$result = safe_query($query);
			$rigaParametro = mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function InsertParametroAtto ($transaction = false)
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
		if ($transaction == false)
		{
			$ret = table_insert_record ("testo_archiviazione_atto", $fields, $values);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_insert_record_query ("testo_archiviazione_atto", $fields, $values);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function UpdateParametroAtto ($valoreCampo, $transaction = false , $campo = "ID")
	{
		$fields = array();
		$values = array();
		foreach ($this as $key => $value)
		{
			if ($key != $campo && isset($value) != false)
			{
				$fields[] = $key;
				$values[] = $value;
			}
		}
		if ($transaction == false)
		{
			$ret = table_update_record ("testo_archiviazione_atto", $fields, $values, $campo, $valoreCampo);
			return $ret;  // ritorna l'id inserito
		}
		else
		{
			$ret = table_update_record_query ("testo_archiviazione_atto", $fields, $values, $campo, $valoreCampo);
			$ctrlRet = mysql_query($ret);
			return $ctrlRet;  // ritorna true o false (se va a buon fine la prova o meno)
		}
	}

	public function InsertOrUpdatesParametroAtto ($transaction = false)
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
		$queryCerca = "SELECT ID FROM testo_archiviazione_atto WHERE CC='".$this->CC."' AND Data_Creazione_Parametri = '" . $this->Data_Creazione_Parametri . "'";
		$result = mysql_query($queryCerca);
		$rigaId = mysql_fetch_assoc($result);

		$id = $rigaId['ID'];

		if ($transaction == false)
		{
			if ($id == NULL) $res = $this->InsertParametroAtto();
			else $res = $this->UpdateParametroAtto($id);
		}
		else
		{
			if ($id == NULL) $res = $this->InsertParametroAtto(true);
			else $res = $this->UpdateParametroAtto($id, true);
		}
		return $res;
	}

}
?>