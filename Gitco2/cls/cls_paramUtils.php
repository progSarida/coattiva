<?php
include_once CLS . "/cls_db.php";




  class cls_param{

    public $cls_db;

    public function __construct()
    {
      $this->cls_db = new cls_db();
    }

    public function Get_Query_Gen( $c , $tipo )
    {
        return "SELECT * FROM parametri_generali WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."'";
    }

    public function Get_Query_EMail($c,$tipo_email,$tipo_riscossione = null)
    {
      $query = "SELECT * FROM parametri_email WHERE CC = '".$c."' AND Tipo_Email = '".$tipo_email."' ";
      if($tipo_riscossione!=null)
          $query.= "AND Tipo_Riscossione = '".$tipo_riscossione."' LIMIT 1";

      return $query;
    }

    function trovaSedeDaFiliale($codiceCatastale, $cap = null)
    {

      $where = "CC_Sede = '" . $codiceCatastale . "' AND Tipo_Banca = 'filiale' AND ( ( ID_Collegamento > 0 AND Denominazione NOT LIKE 'POSTE ITALIANE%'";
      if($cap!=null)
        $where.= " AND Cap = '" . $cap . "'";
      $where.= " ) OR ( Denominazione LIKE 'POSTE ITALIANE%' ) )";

      //$array_sedi = select_mysql_array("ID_Collegamento" , "banca", $where, 'Denominazione', 'ASC', 'si');
      $QUERY = "SELECT DISTINCT ID_Collegamento FROM banca WHERE ".$where." ORDER BY Denominazione ASC";

      $array_sedi = $this->cls_db->getResults($this->cls_db->ExecuteQuery($QUERY));

      return $array_sedi;

    }

    public function Get_Query_EnteEs($CC, $Tipo, $id_ente = "", $progr = "")
    {
      $query1 = "SELECT * FROM enti_esterni WHERE CC = '{$CC}' AND Tipo = '{$Tipo}'";
      $query2 = "SELECT * FROM documento_ente WHERE Ente_Esterno_ID = {$id_ente}";
	  $query3 = "SELECT * FROM enti_esterni WHERE CC = '{$CC}' AND Tipo = '{$Tipo}' AND progressivo = {$progr}";
	  $query4 = "SELECT progressivo FROM enti_esterni WHERE CC = '{$CC}' AND Tipo = '{$Tipo}' AND progressivo > {$progr} ORDER BY progressivo ASC LIMIT 1";
	  $query5 = "SELECT progressivo FROM enti_esterni WHERE CC = '{$CC}' AND Tipo = '{$Tipo}' AND IF({$progr} = 0 , progressivo > {$progr}, progressivo < {$progr} ) ORDER BY progressivo DESC LIMIT 1";

      return array("EnteE" => $query1, "Doc" => $query2, "EnteEG" => $query3, "Ente_next" => $query4, "Ente_prec" => $query5);
    }

    function Get_Array_Doc_Ente($ID, $CC)
    {
      $query = "SELECT * FROM documento_ente WHERE ID = '".$ID."' AND CC = '".$CC."'";

      $data = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

      /*$data["ID"] = utf8_decode($data['ID']);
      $data["Comune_ID"] = utf8_decode($data['Comune_ID']);
      $data["Ente_Esterno_ID"] = utf8_decode($data['Ente_Esterno_ID']);
      $data["CC"] = utf8_decode($data['CC']);
      $data["Data_Creazione"] = utf8_decode($data['Data_Creazione']);
      $data["Tipo"] = utf8_decode($data['Tipo']);
      $data["Atto"] = utf8_decode($data['Atto']);
      $data["Oggetto"] = utf8_decode($data['Oggetto']);
      $data["Contenuto"] = utf8_decode($data['Contenuto']);
      $data["Data_Stampa"] = utf8_decode($data['Data_Stampa']);
      $data["Informazioni_Aggiuntive"] = utf8_decode($data['Informazioni_Aggiuntive']);
      $data["File"] = utf8_decode($data['File']);*/

      return $data;
    }

    function crea_dir( $path )
    {
      if (!is_dir($path)) {
      	$folder = explode("/",$path);

      	$control_path = $folder[0];

      	for($l=1;$l<count($folder);$l++)
      	{
      		$control_path .= "/".$folder[$l];
      		if( is_dir( $control_path ) == false )
      		{
      			mkdir( $control_path );
      		}
      	}
      }
    	return $path;
    }

    function Get_Query_Tariffe_Gen($CC)
    {
       $Una_Tantum_Group = "SELECT ID, Tipo, Descrizione, Deposito_Portata FROM tariffe_coazione WHERE CC = '".$CC."' AND Tipo = 'UNA TANTUM' GROUP BY Descrizione, Deposito_Portata";
       $A_Giorni_Group =  "SELECT ID, Tipo, Descrizione, Deposito_Portata FROM tariffe_coazione WHERE CC = '".$CC."' AND Tipo = 'A GIORNO' GROUP BY Descrizione, Deposito_Portata";
       $A_Km_Group = "SELECT ID, Tipo, Descrizione, Deposito_Portata FROM tariffe_coazione WHERE CC = '".$CC."' AND Tipo = 'A KM' GROUP BY Descrizione, Deposito_Portata";

       return array("Una_Tantum" => $Una_Tantum_Group, "A_Giorni" => $A_Giorni_Group, "A_Km" => $A_Km_Group);
    }

    function Get_Data_Tariffa_Coazione($CC,$ID)
    {
      $query = "SELECT * FROM tariffe_coazione WHERE CC = '".$CC."' LIMIT 1";

      $results = $this->cls_db->ExecuteQuery($query);

      if(mysqli_num_rows($results)==0){
          try {

              $this->cls_db->Start_Transaction();
            	$this->cls_db->Begin_Transaction();
              // A set of queries; if one fails, an exception should be thrown
              $query = "CREATE TEMPORARY TABLE tmp_tariffe SELECT * from tariffe_coazione WHERE CC='*****'";
              $this->cls_db->ExecuteQuery($query);

              $query = "ALTER TABLE tmp_tariffe drop ID, drop CC";
              $this->cls_db->ExecuteQuery($query);

              $query = "INSERT INTO tariffe_coazione SELECT 0,'".$CC."',tmp_tariffe.* FROM tmp_tariffe";
              $this->cls_db->ExecuteQuery($query);

              $query = "DROP TABLE tmp_tariffe";
              $this->cls_db->ExecuteQuery($query);


              $this->cls_db->End_Transaction();

          } catch (Exception $e) {

              $this->cls_db->Rollback();
          }
      }

      $query2 = "SELECT * FROM tariffe_coazione WHERE ID = '".$ID."' AND CC = '".$CC."'";
      $return = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query2));

      return $return;
    }

    public function Get_Query_Banca($id = null,$CC = null)
    {
      $F_next = "";
      $F_prev = "";
      $S_next = "";
      $S_prev = "";

      $query = "SELECT * FROM banca WHERE ID = '" . $id . "' AND CC = '" . $CC . "'";

      if($id == 0)
      {
		$F_next = "SELECT ID FROM banca WHERE Tipo_Banca = 'filiale' ORDER BY ID ASC LIMIT 1";
		$S_next = "SELECT ID FROM banca WHERE Tipo_Banca = 'sede' ORDER BY ID ASC LIMIT 1";

		$F_prev = "SELECT ID FROM banca WHERE Tipo_Banca = 'filiale' ORDER BY ID DESC LIMIT 1";
		$S_prev = "SELECT ID FROM banca WHERE Tipo_Banca = 'sede' ORDER BY ID DESC LIMIT 1";
      }
      else {
        $F_next = "SELECT ID FROM banca WHERE ID > '" . $id . "' AND Tipo_Banca = 'filiale' ORDER BY ID LIMIT 1";
        $S_next = "SELECT ID FROM banca WHERE ID > '" . $id . "' AND Tipo_Banca = 'sede' ORDER BY ID LIMIT 1";

        $F_prev = "SELECT ID FROM banca WHERE ID < '" . $id . "' AND Tipo_Banca = 'filiale' ORDER BY ID DESC LIMIT 1";
        $S_prev = "SELECT ID FROM banca WHERE ID < '" . $id . "' AND Tipo_Banca = 'sede' ORDER BY ID DESC LIMIT 1";
      }

      $LP = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****' AND (Sigla = 'L.a.' OR Sigla='L.p.')";
      $I = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****' AND Tipo = 'Impresa individuale'";
      $P = 'SELECT * FROM forma_giuridica_societa WHERE CC = "*****" AND Tipo = "Societa\' di persone"';
      $Cap = 'SELECT * FROM forma_giuridica_societa WHERE CC = "*****" AND Tipo = "Societa\' di capitale"';
      $Cons = 'SELECT * FROM forma_giuridica_societa WHERE CC = "*****" AND Tipo = "Societa\' consortile"';
      $Coop = 'SELECT * FROM forma_giuridica_societa WHERE CC = "*****" AND Tipo = "Societa\' cooperativa"';
      $Ente = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****' AND Tipo = 'Ente'";

      $Filiali_Save = "SELECT DISTINCT ID, Denominazione from banca WHERE Tipo_Banca = 'filiale' AND ID_Collegamento = ".$id." ORDER BY Denominazione ASC";

      return array("filiali" => $Filiali_Save,"query" => $query, "next_F" => $F_next , "prev_F" => $F_prev, "next_S" => $S_next, "prev_S" => $S_prev, "LP" => $LP, "I" => $I, "P" => $P, "Cap" => $Cap, "Cons" => $Cons, "Coop" => $Coop, "Ente" => $Ente);
    }

    public function Get_Query_Pagamento($c,$tipo,$tipo_doc = "no")
    {
      $query = "SELECT * FROM parametri_pagamento WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."'";
  		if($tipo_doc!="no")
  			$query.= " AND Tipo_Documento = '".$tipo_doc."'";

      return $query;
    }

    public function Get_Query_Ricorso($c)
    {
      $query = "SELECT * FROM parametri_ricorso WHERE CC = '".$c."'";

      return $query;
    }

    public function Get_Query_Annuali($CC,$anno)
    {
      $query = "SELECT * FROM parametri_annuali WHERE CC = '".$CC."' AND Anno = '".$anno."' AND Tipo_Riscossione = '*****'";

      return $query;
    }

    public function gestione_conto_terzi($data,$CT)
  	{
  		if($data != "")
  		{
  			if($CT == "si")
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


    public function Get_Query_Resp($c,$tipo)
    {
      $query = "SELECT * FROM parametri_responsabili WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."'";
      return $query;
    }

    public static function Get_Query_Tributi( $c)
  	{
  		$query = "SELECT * FROM interessi_tributi WHERE CC = '{$c}' ORDER BY Data_Inizio ASC";
  		return $query;
  	}

    function conv_num($value)
    {

      if($value == null)
		return "";

	$virgola = strpos($value, ",");
	$punto = strpos($value, ".");

	if($virgola != false && $punto != false)
	{
		if($virgola < $punto)
		{
			$value = str_replace(",", "", $value);
			$value = str_replace(".", ",", $value);
		}
		else
		{
			$value = str_replace(".", "", $value);
		}
	}
	else if ($virgola == false && $punto != false)
	{
		$value = number_format($value, 2);
		$value = str_replace(".", ",", $value);
	}
	else if ($virgola != false && $punto == false)
	{
		$value = str_replace(",", ".", $value);
	}

	return $value;

    }

    public function Cerca_Anni_Parametri_Annuali ( $c , $attuale)
  	{

  		$query = "SELECT Anno FROM parametri_annuali WHERE CC = '" . $c . "' ";
  		$query .= " AND Tipo_Riscossione = '*****'";
  		$query .= " ORDER BY Anno ";

      $this->cls_db->Start_Transaction();
      $this->cls_db->Begin_Transaction();

  		$result = $this->cls_db->ExecuteQuery($query);
      $numAnni = 0;
      if(!$result) return false;
      else $numAnni = $this->cls_db->getNumberRow($result);
  		$this->cls_db->End_Transaction();

  		$array = array();
  		$arraySeq = array();
  		$arraySeq['PRECEDENTE'] = "";
  		$arraySeq['ATTUALE'] = "";
  		$arraySeq['SUCCESSIVO'] = "";
  		$precTrovato = false;
  		$succTrovato = false;
  		$memoPrimo = null;
  		$conto = 0;
  		while ($val = mysqli_fetch_assoc($result))
  		{
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

    public function firmaSingola($value,$a_param_Resp)
  	{

  		$firma_path = FIRMEWEB."/".$a_param_Resp["CC"]."/";
		$percorso = FIRME."/".$a_param_Resp["CC"]."/";

  		$temp['intestazione'] = "";
  		$temp['nome'] = "";
  		$temp['firma'] = "";
  		$temp['path_firma'] = "";

  		switch($value)
  		{
  			case "Funzionario_Responsabile":

  				$temp['intestazione'] = "Il Funzionario Responsabile";
  				$temp['nome'] = $a_param_Resp["Funzionario_Responsabile"];

  				if($a_param_Resp["Funzionario_Firma"]!=null)
  				{
  					$temp['firma'] = $firma_path.$a_param_Resp["Funzionario_Firma"];
  					$temp['path_firma'] = $percorso.$a_param_Resp["Funzionario_Firma"];
  				}

  				break;

  			case "Responsabile_Procedimento":

  				$temp['intestazione'] = "Il Responsabile del Procedimento";
  				$temp['nome'] = $a_param_Resp["Responsabile_Procedimento"];

  				if($a_param_Resp["Responsabile_Firma"]!=null)
  				{
  					$temp['firma'] = $firma_path.$a_param_Resp["Responsabile_Firma"];
  					$temp['path_firma'] = $percorso.$a_param_Resp["Responsabile_Firma"];
  				}

  				break;

  			case "Ufficiale_Riscossione":

  				$temp['intestazione'] = "L'Ufficiale della Riscossione";
  				$temp['nome'] = $a_param_Resp["Ufficiale_Riscossione"];

  				if($a_param_Resp["Ufficiale_Firma"]!=null)
  				{
  					$temp['firma'] = $firma_path.$a_param_Resp["Ufficiale_Firma"];
  					$temp['path_firma'] = $percorso.$a_param_Resp["Ufficiale_Firma"];
  				}

  				break;

  			case "Responsabile_Richieste":

  				$temp['intestazione'] = "Il Responsabile della Richiesta";
  				$temp['nome'] = $a_param_Resp["Responsabile_Richieste"];

  				if($a_param_Resp["Responsabile_Richieste_Firma"]!=null)
  				{
  					$temp['firma'] = $firma_path.$a_param_Resp["Responsabile_Richieste_Firma"];
  					$temp['path_firma'] = $percorso.$a_param_Resp["Responsabile_Richieste_Firma"];
  				}

  				break;

  			case "Legale_Rappresentante":

  				$temp['intestazione'] = "Il Legale Rappresentante";
  				$temp['nome'] = $a_param_Resp["Legale_Rappresentante"];

  				if($a_param_Resp["Legale_Rappresentante_Firma"]!=null)
  				{
  					$temp['firma'] = $firma_path.$a_param_Resp["Legale_Rappresentante_Firma"];
  					$temp['path_firma'] = $percorso.$a_param_Resp["Legale_Rappresentante_Firma"];
  				}

  				break;

			case "Funzionario_Riscossione":

			$temp['intestazione'] = "Funzionario della riscossione";
			$temp['nome'] = $a_param_Resp["Funzionario_Riscossione"];

			if($a_param_Resp["Funzionario_Riscossione_Firma"]!=null)
			{
				$temp['firma'] = $firma_path.$a_param_Resp["Funzionario_Riscossione_Firma"];
				$temp['path_firma'] = $percorso.$a_param_Resp["Funzionario_Riscossione_Firma"];
			}

			break;
  		}

  		return $temp;

  	}

    function SaveImage($percorso, $c, $tipo_riscossione,$Tipo){

    	$tipo = "";
    	switch($Tipo){
    		case 1: $tipo = "Funzionario_Firma_"; break;
    		case 2: $tipo = "Responsabile_Firma_"; break;
    		case 3: $tipo = "Ufficiale_Riscossione_Firma_"; break;
    		case 4: $tipo = "Responsabile_Richieste_Firma_"; break;
			case 5: $tipo = "Funzionario_Riscossione_Firma_"; break;
    		default: $tipo = "";
    	}

    	$im = new imagick( $percorso );

    	$ext = pathinfo($percorso , PATHINFO_EXTENSION);
    	$file_name = $tipo.$tipo_riscossione."_".$c."_".date("Y-m-d");

    	$im->setImageCompression(Imagick::COMPRESSION_JPEG);
    	$im->setImageCompressionQuality(100);
    	$im->writeImage( FIRME."/".$c."/".$file_name.'.jpg' );

    	return $file_name.'.jpg';
    }
  }
?>
