<?php
include_once CLS . "/cls_db.php";

class cls_anagr{

  public $cls_db;

  public function __construct()
  {
    $this->cls_db = new cls_db();
  }

  function get_Query_Dati_Soggetto($progr,$c)
  {
    $query = "SELECT * FROM utente WHERE ID = '".$progr."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
    $query_1 = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr."' AND Tipo = 'res'";
    $query_2 = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr."' AND Tipo = 'dom'";
    $query_3 = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr."' AND Tipo = 'rec'";

    return array("Soggetto" => $query, "Indirizzo_R" =>$query_1, "Indirizzo_D" =>$query_2, "Indirizzo_Rec" =>$query_3);
  }

  function Get_Drop_Dettagli()
  {

    $query = "SELECT Descrizione, ID FROM dettagli_utente_lista WHERE Tipo = 'Esenz' ORDER BY Descrizione";
    $result["Esenz"] = $this->build_Drop($this->cls_db->getResults($this->cls_db->ExecuteQuery($query)));;

    $query = "SELECT Descrizione, ID FROM dettagli_utente_lista WHERE Tipo = 'Situaz' ORDER BY Descrizione";
    $result["Situaz"] = $this->build_Drop($this->cls_db->getResults($this->cls_db->ExecuteQuery($query)));

    $query = "SELECT Descrizione, ID FROM dettagli_utente_lista WHERE Tipo = 'Control' ORDER BY Descrizione";
    $result["Control"] = $this->build_Drop($this->cls_db->getResults($this->cls_db->ExecuteQuery($query)));

    $query = "SELECT Descrizione, ID FROM dettagli_utente_lista WHERE Tipo = 'Raggr' ORDER BY Descrizione";
    $result["Raggr"] = $this->build_Drop($this->cls_db->getResults($this->cls_db->ExecuteQuery($query)));

    $query = "SELECT Descrizione, ID FROM dettagli_utente_lista WHERE Tipo = 'Sotto_Raggr' ORDER BY Descrizione";
    $result["Sotto_Raggr"] = $this->build_Drop($this->cls_db->getResults($this->cls_db->ExecuteQuery($query)));

    return $result;
  }

  function build_Drop( $array )
  {
    $itemDrop = "";
    for($i=0;$i<count($array);$i++)
    {
      $itemDrop .= "<option value='".$array[$i]["ID"]."'>".$array[$i]["Descrizione"]."</option>";
    }

    return $itemDrop;
  }

  function get_Data_Dettagli($progr)
  {
    $array_return;

    $query = "SELECT * FROM dettagli_utente WHERE Utente_ID = '".$progr."'";
    $val = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"dettagli_utente");
    //$result = safe_query($query);
		//$val = mysql_fetch_array($result);

		$ID = $val['ID'];
		$Utente_ID = $val['Utente_ID'];

    $array_return['ID'] = $val['ID'];
    $array_return['Utente_ID'] = $val['Utente_ID'];

		$Esenzione_ID = $val['Esenzione_ID'];
    $array_return['Esenzione_ID'] = $val['Esenzione_ID'];
		if($Esenzione_ID == 1)
		{
			$Esenzione = null;
      $array_return['Esenzione'] = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$Esenzione_ID."' AND Tipo = 'Esenz'";
			$Esenzione = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"dettagli_utente_lista");
      $array_return['Esenzione'] = $Esenzione["Descrizione"];
		}

		$Situazione_ID = $val['Situazione_ID'];
    $array_return['Situazione_ID'] = $val['Situazione_ID'];
		if($Situazione_ID == 1)
		{
			$Situazione = null;
      $array_return['Situazione'] = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$Situazione_ID."' AND Tipo = 'Situaz'";
			$Situazione = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"dettagli_utente_lista");//single_answer_query($query);
      $array_return['Situazione'] = $Situazione["Descrizione"];
		}

		$Controllo_ID = $val['Controllo_ID'];
    $array_return['Controllo_ID'] = $val['Controllo_ID'];
		if($Controllo_ID == 1)
		{
			$Controllo = null;
      $array_return['Controllo'] = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$Controllo_ID."' AND Tipo = 'Control'";
			$Controllo = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"dettagli_utente_lista");//single_answer_query($query);
      $array_return['Controllo'] = $Controllo["Descrizione"];
		}

		$Raggruppamento_ID = $val['Raggruppamento_ID'];
    $array_return['Raggruppamento_ID'] = $val['Raggruppamento_ID'];
		if($Raggruppamento_ID == 1)
		{
			$Raggruppamento = null;
      $array_return['Raggruppamento'] = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$Raggruppamento_ID."' AND Tipo = 'Raggr'";
			$Raggruppamento = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"dettagli_utente_lista");//single_answer_query($query);
      $array_return['Raggruppamento'] = $Raggruppamento["Descrizione"];
		}
//array("ID" => $ID, "Utente_ID" => $Utente_ID, "Esenzione_ID" => $Esenzione_ID, "Esenzione" => $Esenzione, "Situazione_ID" => $Situazione_ID, "Situazione" => $Situazione, "Controllo_ID" => $Controllo_ID, )
		$Sottoraggruppamento_ID = $val['Sottoraggruppamento_ID'];
    $array_return['Sottoraggruppamento_ID'] = $val['Sottoraggruppamento_ID'];
		if($Sottoraggruppamento_ID == 1)
		{
			$Sottoraggruppamento = null;
      $array_return['Sottoraggruppamento'] = null;
		}
		else
		{
			$query = "SELECT Descrizione FROM dettagli_utente_lista WHERE ID = '".$Sottoraggruppamento_ID."' AND Tipo = 'Sotto_Raggr'";
			$Sottoraggruppamento = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"dettagli_utente_lista");//single_answer_query($query);
      $array_return['Sottoraggruppamento'] = $Sottoraggruppamento["Descrizione"];
		}

		$array_return['Pubblicita'] = $val['Pubblicita'];//$Pubblicita = $val['Pubblicita'];
		$array_return['Osap'] = $val['Osap'];//$Osap = $val['Osap'];
		$array_return['Trsu'] = $val['Trsu'];//$Trsu = $val['Trsu'];
		$array_return['Ici'] = $val['Ici'];//$Ici = $val['Ici'];

    return $array_return;
  }

  function Get_Storico_Residenza($progr,$c)
  {
    $array_Return = array();

    $query = "SELECT * FROM storico_residenza WHERE Utente_ID = '".$progr."' ORDER BY Data_Inizio DESC";
    $val = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
    //$val = select_mysql_array( "*" , "storico_residenza" , "Utente_ID = '".$progr."'" , "Data_Inizio", "Desc");
		$num = count($val);
		$array_Return["Num_Storico"] = $num;

		for($i=0; $i<$num; $i++)
		{
			$ID[$i] = $val[$i]['ID'];
			$Utente_ID[$i] = $progr;
			$Data_Inizio[$i] = $val[$i]['Data_Inizio'];
			$Data_Fine[$i] = $val[$i]['Data_Fine'];
			$Via_ID[$i] = $val[$i]['Via_ID'];
			$Via_Cap_ID[$i] = $val[$i]['Via_Cap_ID'];
			$CC_Indirizzo[$i] = $val[$i]['CC_Indirizzo'];
			$Paese[$i] = $val[$i]['Paese'];
			$Comune[$i] = $val[$i]['Comune'];
			$Provincia[$i] = $val[$i]['Provincia'];
			$Frazione[$i] = $val[$i]['Frazione'];
			$Civico[$i] = $val[$i]['Civico'];
			$Esponente[$i] = $val[$i]['Esponente'];
			$Interno[$i] = $val[$i]['Interno'];
			$Dettagli[$i] = $val[$i]['Dettagli'];
			$Cap[$i] = $val[$i]['Cap'];
			$Telefono[$i] = $val[$i]['Telefono'];
			$Fax[$i] = $val[$i]['Fax'];
            $DataMod[$i] = $val[$i]["Data_Ultima_Modifica"];

			if($Via_ID[$i]!=1)
            {
              $query = "SELECT * FROM toponimo WHERE ID = '".$Via_ID[$i]."' AND CC_Comune = '".$c."'";
              $Toponimo[$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"toponimo");//new toponimo( $Via_ID[$i], $c );
            }
            else if($Via_Cap_ID!=1)
            {
              $query = "SELECT *, Odonimo as Nome FROM toponimi_cappati WHERE ID = '".$Via_Cap_ID[$i]."'";
              $Toponimo[$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"toponimi_cappati");//new toponimo_cap( $Via_Cap_ID[$i], $c );
            }
            else
				$Toponimo[$i] = null;
		}

    if($num > 0)
    {
      $array_Return["ID"] = $ID;
      $array_Return["Utente_ID"] = $Utente_ID;
      $array_Return["Data_Inizio"] = $Data_Inizio;
      $array_Return["Data_Fine"] = $Data_Fine;
      $array_Return["Via_ID"] = $Via_ID;
      $array_Return["Via_Cap_ID"] = $Via_Cap_ID;
      $array_Return["CC_Indirizzo"] = $CC_Indirizzo;
      $array_Return["Paese"] = $Paese;
      $array_Return["Comune"] = $Comune;
      $array_Return["Provincia"] = $Provincia;
      $array_Return["Frazione"] = $Frazione;
      $array_Return["Civico"] = $Civico;
      $array_Return["Esponente"] = $Esponente;
      $array_Return["Interno"] = $Interno;
      $array_Return["Dettagli"] = $Dettagli;
      $array_Return["Cap"] = $Cap;
      $array_Return["Telefono"] = $Telefono;
      $array_Return["Fax"] = $Fax;
      $array_Return["Toponimo"] = $Toponimo;
      $array_Return["Data_Ultima_Modifica"] = $DataMod;
    }
    else return null;

    return $array_Return;
  }

    function Get_Indirizzo_Cambia_Residenza($progr,$tipo,$c)
    {
      $array_Return = array();

      $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr."' AND Tipo = '".$tipo."'";
      $val = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
  		//$result = safe_query($query);
  		//$val = mysql_fetch_array($result);
        if($val == null)
            return null;

  		$array_Return["ID"] = $val['ID'];
  		$array_Return["Tipo"] = $val['Tipo'];
  		$array_Return["Utente_ID"] = $val['Utente_ID'];
  		$array_Return["Via_ID"] = $val['Via_ID'];
  		$array_Return["Via_Cap_ID"] = $val['Via_Cap_ID'];
  		$array_Return["CC_Indirizzo"] = $val['CC_Indirizzo'];
  		$array_Return["Presso"] = $val['Presso'];
  		$array_Return["Paese"] = $val['Paese'];
  		$array_Return["Comune"] = $val['Comune'];
  		$array_Return["Provincia"] = $val['Provincia'];
  		$array_Return["Frazione"] = $val['Frazione'];
  		$array_Return["Civico"] = $val['Civico'];
  		$array_Return["Esponente"] = $val['Esponente'];
  		$array_Return["Interno"] = $val['Interno'];
  		$array_Return["Dettagli"] = $val['Dettagli'];
  		$array_Return["Cap"] = $val['Cap'];
  		$array_Return["Telefono"] = $val['Telefono'];
  		$array_Return["Fax"] = $val['Fax'];
  		$array_Return["Data_Inizio_Residenza"] = $val['Data_Inizio_Residenza'];

  		/*if($this->Via_ID!=1)
  		$this->Toponimo = new toponimo( $this->Via_ID , $c );
  		else if($this->Via_Cap_ID!=1)
  		$this->Toponimo = new toponimo_cap( $this->Via_Cap_ID );
  		else
  		$this->Toponimo = null;*/
      if($array_Return["Via_ID"]!=1)
      {
      	$query = "SELECT * FROM toponimo WHERE ID = '".$array_Return["Via_ID"]."' AND CC_Comune = '".$c."'";
        $array_Return["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo( $Via_ID[$i], $c );
      }
			else if($array_Return["Via_Cap_ID"]!=1)
      {
        $query = "SELECT *, Odonimo as Nome FROM toponimi_cappati WHERE ID = '".$array_Return["Via_Cap_ID"]."'";
        $array_Return["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo_cap( $Via_Cap_ID[$i], $c );
      }
			else
				$array_Return["Toponimo"] = null;

  		$array_Return["IndirizzoCompleto"] = isset($array_Return["Toponimo"]["Nome"])?$array_Return["Toponimo"]["Nome"]:"";
  		if ($array_Return["Civico"] != "") $array_Return["IndirizzoCompleto"] .= " " . $array_Return["Civico"];
  		if ($array_Return["Esponente"] != "") $array_Return["IndirizzoCompleto"] .= " " . $array_Return["Esponente"];
  		if ($array_Return["Interno"] != "") $array_Return["IndirizzoCompleto"] .= " /" . $array_Return["Interno"];

      return $array_Return;
    }



  function Get_Array_Forma_Giuridica($c = "*****")
  {
    $return = null;

    $query = "SELECT * FROM forma_giuridica_societa WHERE CC = '".$c."' AND (Sigla = 'L.a.' OR Sigla='L.p.')";
    $return["LiberoProfessionista"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    $query = "SELECT * FROM forma_giuridica_societa WHERE CC = '".$c."' AND Tipo = 'Impresa individuale'";
    $return["Individuale"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    $query = 'SELECT * FROM forma_giuridica_societa WHERE CC = "'.$c.'" AND Tipo = "Societa\' di persone"';
    $return["Persone"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    $query = 'SELECT * FROM forma_giuridica_societa WHERE CC = "'.$c.'" AND Tipo = "Societa\' di capitale"';
    $return["Capitale"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    $query = 'SELECT * FROM forma_giuridica_societa WHERE CC = "'.$c.'" AND Tipo = "Societa\' consortile"';
    $return["Consortile"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    $query = 'SELECT * FROM forma_giuridica_societa WHERE CC = "'.$c.'" AND Tipo = "Societa\' cooperativa"';
    $return["Cooperativa"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    $query = 'SELECT * FROM forma_giuridica_societa WHERE CC = "'.$c.'" AND Tipo = "Ente"';
    $return["Ente"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    return $return;
  }

  function get_ID_Move_Page($p, $a,$c,$Cognome, $Ditta, $ID)
  {
    $return = null;

    if ($p==0)
		{
			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" ) ";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" )";
			$query.= "ORDER BY utente_nome ASC, nome ASC LIMIT 1";
			$result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			//$array_result = mysql_fetch_array($result);
			$return["next_alfa"] = isset($result['ID'])?$result['ID']:null;

			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" ) ";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" )";
			$query.= "ORDER BY utente_nome DESC, nome DESC LIMIT 1";
			$result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//$result = safe_query($query);
		//	$array_result = mysql_fetch_array($result);
			$return["prev_alfa"] = isset($result['ID'])?$result['ID']:null;

			$query = "SELECT * FROM utente where CC_Comune='$c' ORDER BY ID ASC LIMIT 1";
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			$return["next"] = isset($result['ID'])?$result['ID']:null;//single_answer_query($query);

			$query = "SELECT * FROM utente WHERE CC_Comune='$c' ORDER BY ID DESC LIMIT 1";
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			$return["prev"] = isset($result['ID'])?$result['ID']:null;//single_answer_query($query);

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID DESC LIMIT 1";
      $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente");
			$return["prev_ruolo"] = $result["ID"];//single_answer_query($query);

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID  AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID ASC LIMIT 1";
      $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente");
			$return["next_ruolo"] = $result["ID"];//single_answer_query($query);
		}
		else
		{
			if($Cognome!='')
				$utente_nome = $Cognome;
			else if($Ditta!='')
				$utente_nome =$Ditta;
			else
				$utente_nome="";

			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" AND Cognome > \"".$utente_nome."\" )";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" AND Ditta > \"".$utente_nome."\" )";
			$query.= "ORDER BY utente_nome ASC, Nome ASC LIMIT 1";
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			//$array_result = mysql_fetch_array($result);
			$return["next_alfa"] = isset($result["ID"])?$result["ID"]:null;
			/*$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next_alfa = $array_result['ID'];*/

			$query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
			$query.= "WHERE Cognome != \"\" AND CC_Comune = \"".$c."\" AND Cognome < \"".$utente_nome."\" ) ";
			$query.= "UNION ";
			$query.= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
			$query.= "WHERE Ditta != \"\" AND CC_Comune = \"".$c."\" AND Ditta < \"".$utente_nome."\" )";
			$query.= "ORDER BY utente_nome DESC , Nome DESC LIMIT 1";
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//$result = safe_query($query);
		//	$array_result = mysql_fetch_array($result);
			$return["prev_alfa"] = isset($result["ID"])?$result["ID"]:null;
			/*$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev_alfa = $array_result['ID'];*/


			$query = "SELECT ID FROM utente WHERE ( (ID>'$ID') AND (CC_Comune='$c') ) ORDER BY ID ASC LIMIT 1";
			//$result = safe_query($query);
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			$return["next"] = isset($result["ID"])?$result["ID"]:null;//single_answer_query($query);
			//$array_result = mysql_fetch_array($result);
			//$this->next = $array_result['ID'];

			$query = "SELECT * FROM utente WHERE ( (ID<'$ID') AND (CC_Comune='$c') ) ORDER BY ID DESC LIMIT 1";
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			$return["prev"] = isset($result["ID"])?$result["ID"]:null;//single_answer_query($query);
			/*$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];*/

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID AND (u.ID<'$ID')  AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID DESC LIMIT 1";
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			$return["prev_ruolo"] = isset($result["ID"])?$result["ID"]:null;//single_answer_query($query);
			//$this->prev_ruolo = single_answer_query($query);

			$query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
			$query.= "WHERE pa.Utente_ID = u.ID AND (u.ID>'$ID')  AND pa.Anno_Riferimento = '".$a."' AND pa.CC = '".$c."' ORDER BY u.ID ASC LIMIT 1";
      $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
			$return["next_ruolo"] = isset($result["ID"])?$result["ID"]:null;//single_answer_query($query);
			//$this->next_ruolo = single_answer_query($query);
		}

    return $return;
  }

  function attoStampato ( $tipo_atto , $tipo_stampa ,$Atto)
  {
    if($tipo_atto == "Ingiunzione")
    {
      $cartella = "Ingiunzioni";
      $prefisso = "Ingiunzione_";
    }
    else if($tipo_atto == "Avviso di intimazione ad adempiere")
    {
      $cartella = "Avvisi_di_intimazione";
      $prefisso = "Avviso_di_intimazione_";
    }
    else if($tipo_atto == "Sollecito di pagamento")
    {
      $cartella = "Solleciti";
      $prefisso = "Sollecito_";
    }
        else if($tipo_atto == "Sollecito pre ingiunzione" || $tipo_atto=="SOLL_PRE")
        {
            $cartella = "Solleciti_Pre_Ingiunzione";
            $prefisso = "sollecitoPreIngiunzione_";
        }
        else if($tipo_atto == "Avviso di messa in mora" || $tipo_atto=="AV_MORA")
        {
            $cartella = "Avvisi_Messa_In_Mora";
            $prefisso = "avvisoMessaInMora_";
        }

    if($tipo_stampa=="DEFINITIVA")
    {
      $sottoCartella = "STAMPE DEFINITIVE";
      if( $Atto["Data_Stampa"] == "0000-00-00" || $Atto["Data_Stampa"] == null )
        return "notFound";

      $file = array();

      $link = ATTI . "/" . $Atto["CC"] . "/" . $cartella . "/" . $sottoCartella . "/";

      $link .= $prefisso;
      $link .= $Atto["CC"] . "_";
      $link .= $Atto["Anno_Cronologico"] . "_";
      $link .= $Atto["ID_Cronologico"] . "_";
      $link .= $Atto["Data_Stampa"] . ".pdf";
      //echo "<h1>Link file su cls_anagrafeUtils (function attoStampato): ".$link."</h1>";
      $file[0] = $link;
      if(is_file($link))
        return $file;
      else
        return "notFound";


    }
    else if($tipo_stampa=="FLUSSO")
    {

      $sottoCartella = "FLUSSI";
      if( $Atto["Data_Flusso"] == "0000-00-00" || $Atto["Data_Flusso"] == null )
        return "notFound";

      $file = array();

      $dir = ATTI . "/" . $Atto["CC"] . "/" . $cartella . "/" . $sottoCartella;
      $this->crea_dir($dir);
      $handle = opendir($dir);
      while (($link = readdir($handle)) != false)
      {
        if ($link != "." && $link != ".." && $link != "thumbs.db" && $link != "ELIMINATI")
        {
          $explodePunto = explode (".", $link);
          $estensione = $explodePunto[1];

          $explode = explode ("_", $explodePunto[0]);
          $control_comune = $explode[2];
          $control_anno = $explode[3];
          $control_numero = $explode[4];
          $control_data = $explode[5];

          if (strtoupper($estensione) == "RAR" &&
          $Atto["CC"] == $control_comune &&
          $Atto["Anno_Flusso"] == $control_anno &&
          $Atto["Numero_Flusso"] == $control_numero)
          {
            $file[1] = $dir."/".$link;
          }

          if (strtoupper($estensione) == "TXT" &&
          $Atto["CC"] == $control_comune &&
          $Atto["Anno_Flusso"] == $control_anno &&
          $Atto["Numero_Flusso"] == $control_numero &&
          $Atto["Data_Flusso"] == $control_data )
          {
            $file[0] = $dir."/".$link;
          }
        }
      }

      closedir($handle);
      if(count($file)!=2)
        return "notFound";
      else
        return $file;
    }

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

  function get_Query_Dati_Soggetto_Via($ID,$c)
  {
    $query = "";
    if($ID["ViaID"]!=1)
		$query = "SELECT * FROM toponimo WHERE ID = '".$ID["ViaID"]."' AND CC_Comune = '".$c."'";
		else if($ID["CapID"]!=1)
		$query = "SELECT *, Odonimo as Nome,'Italia' as Paese FROM toponimi_cappati WHERE ID = '".$ID["CapID"]."'";
		else
		$query = "";

    return $query;
  }

  public function Options_Anni_Veloci($c, $gestione, $pagina)
	{
    $where = "CC_Anno = '".$c."' ";

    switch ($gestione)
    {
      case "COATTIVA": 		$where.= " AND Gestione_Coattiva = 'Y' "; 		break;
      case "TARGHEESTERE": 	$where.= " AND Gestione_Targhe_Estere = 'Y' "; 	break;
      case "PUBBLICITA": 		$where.= " AND Gestione_Pubblicita = 'Y' ";		break;
      default: 				alert ("Parametro assente!"); 					break;
    }

    $query = "SELECT * FROM anni_gestiti WHERE ".$where." ORDER BY Anno DESC";

    $array_anni = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

		$select = "<select id='select_anno_veloce' onchange='conferma_anno_js(\"".$pagina."\",\"".$c."\")'>";

		for($i=0;$i<count($array_anni);$i++)
			$select.= "<option value='".$array_anni[$i]['Anno']."'>".$array_anni[$i]['Anno']."</option>";

			$select.="</select>";

			return $select;

	}

  //DECODIFICA CODICE FISCALE
  /*function decode_CF( $CF )
  {
      $array_CF = Array();




      $alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      $alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
      $numeri = "0123456789";
      $numeri_disp = "10   2 3 4   5 6 7 8 9";

      $lettere_mesi = "ABCDEHLMPRST";
      $lettere_omocodia = "LMNPQRSTUV";
      $checkOmocodia = 0;

      $cognome = substr($CF,0,3);
      $array_CF['COGNOME'] = $cognome;
      $nome = substr($CF,3,3);
      $array_CF['NOME'] = $nome;

      $annoStr = substr($CF,6,2);
      $anno = "";
      for($i=0;$i<strlen($annoStr);$i++){
          if (preg_match("/^\d+$/", substr($annoStr,$i,1)))
              $anno.= substr($annoStr,$i,1);
          else{
              $checkOmocodia = 1;
              $anno.= strpos($lettere_omocodia, substr($annoStr,$i,1));
          }
      }

      $mese = substr($CF,8,1);
      $mese = strpos($lettere_mesi, $mese)+1;
      if(strlen($mese)<2)		$mese_nascita = "0".$mese;
      else					$mese_nascita = $mese;

      $giornoStr = substr($CF,9,2);
      $giorno = "";
      for($i=0;$i<strlen($giornoStr);$i++){
          if (preg_match("/^\d+$/", substr($giornoStr,$i,1)))
              $giorno.= substr($giornoStr,$i,1);
          else{
              $checkOmocodia = 1;
              $giorno.= strpos($lettere_omocodia, substr($giornoStr,$i,1));
          }
      }

      if(intval($giorno) > 40){
          $array_CF['SESSO'] = "F";
          $giorno = intval($giorno) - 40;
          $giorno = strval($giorno);
      }
      else{
          $array_CF['SESSO'] = "M";
      }

      if(strlen($giorno)<2)	$giorno_nascita = "0".$giorno;
      else					$giorno_nascita = $giorno;

      $anno_odierno = date('Y');
      $pref_anno = substr($anno_odierno,0,2);
      $pref_anno_int = intval($pref_anno);
      $post_anno = substr($anno_odierno,2,2);
      $post_anno_int = intval($post_anno);

      if( $anno - $post_anno_int >= -5 )
          $pref_anno = strval( $pref_anno_int - 1 );

      $anno_nascita = $pref_anno . $anno;
      $array_CF['DATA_NASCITA'] = $anno_nascita."-".$mese_nascita."-".$giorno_nascita;

      $ccStr = substr($CF,12,3);
      $CC = substr($CF,11,1);

      for($i=0;$i<strlen($ccStr);$i++){
          if (preg_match("/^\d+$/", substr($ccStr,$i,1))){
              $CC.= substr($ccStr,$i,1);
          }
          else {
              $checkOmocodia = 1;
              $CC.= strpos($lettere_omocodia, substr($ccStr, $i, 1));
          }
      }

      $array_CF['CC_NASCITA'] = $CC;

  	if($CC != null)
  	{
  		$verifica_stato = substr($CC,0,1);
  		if($verifica_stato=="Z")
  		{
  			$stato_control = new stato_estero($CC);
  			$array_CF['STATO_NASCITA'] = $stato_control->Nome;
  			$array_CF['COMUNE_NASCITA'] = "";
  		}
  		else
  		{
  			$comune_control = new comune($CC);
  			$array_CF['STATO_NASCITA'] = "Italia";
  			$array_CF['COMUNE_NASCITA'] = $comune_control->Nome;
  		}
  	}
  	else
  	{
  		$array_CF['STATO_NASCITA'] = "";
  		$array_CF['COMUNE_NASCITA'] = "";
  	}

      $array_CF['OMOCODIA'] = $checkOmocodia;

      $sommaCod = 0;
      for($i=0;$i<strlen($CF)-1;$i++){
          $char = substr($CF,$i,1);
          if(($i%2)==0)
              $sommaCod+= strrpos($numeri_disp,$char) + strrpos($alfabeto_disp,$char);
          else
              $sommaCod+= strrpos($numeri,$char) + strrpos($alfabeto,$char);
      }

      $array_CF['CODICE_CONTROLLO'] = substr($alfabeto,($sommaCod%26),1);
      if($array_CF['CODICE_CONTROLLO']!=substr($CF,15,1))
          return false;
      else
          return $array_CF;

  }*/

}




 ?>
