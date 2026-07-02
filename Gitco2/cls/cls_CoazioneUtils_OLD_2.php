<?php
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_math.php";

class cls_Coazione
{
  public $cls_db;
  public $cls_date;
  public $cls_math;

  public function __construct()
  {
    $this->cls_db = new cls_db();
    $this->cls_date = new cls_DateTimeI("IT",false);
    $this->cls_math = new cls_math();
  }

  public function importiRiscontri($val,$Tipo)
	{
		$sommaImporti = 0;
		switch ($Tipo)
		{
			case "terzi":

			if($val["Tipo_Terzi"]=="banca"){
				for( $i=0; $i<count($val["Presso_Terzi"]); $i++ )
				{
					for( $y=0; $y<count($val["Presso_Terzi"][$i]["Notifiche_Terzo"]); $y++ )
					{
						$sommaImporti += $val["Presso_Terzi"][$i]["Notifiche_Terzo"][$y]["Importo_Riscontro"];
					}
				}
			}

				break;

// 			case "veicolo":

// 			for( $i=0; $i<count($this->Notifica_Istituto); $i++ )
// 			{
// 				$sommaImporti += $this->Notifica_Istituto[$i]->Importo_Riscontro;
// 			}

// 				break;
		}

		return $sommaImporti;
	}

  public function getDataPartita_1($p,$c,$return_type = "array")
  {
    $query = "SELECT ID FROM partita_tributi WHERE Utente_ID = '".$p."' AND CC = '".$c."' AND Is_Discharged=0";
	  if($return_type == "object") $results = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");
	  else $results = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

  	$partita = array();
  	for($i=0;$i<count($results);$i++){


		if($return_type == "object") {
			$query = "SELECT * FROM partita_tributi WHERE ID = '".$results[$i]->ID."' AND CC = '".$c."'";
			$partita[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "partita_tributi");
		}
		else {
			$query = "SELECT * FROM partita_tributi WHERE ID = '".$results[$i]['ID']."' AND CC = '".$c."'";
			$partita[$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "partita_tributi");
		}
  		//$partita[$i] = new partita($results[$i]['ID'],$c);


		if($return_type == "object") {
			$query = "SELECT ID FROM tributo WHERE Partita_ID = '".$results[$i]->ID."' ORDER BY Codice_Tributo ASC";
			$tributo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query), "object");
		}
		else {
			$query = "SELECT ID FROM tributo WHERE Partita_ID = '".$results[$i]['ID']."' ORDER BY Codice_Tributo ASC";
			$tributo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		}

  		for( $y=0; $y<count($tributo_id); $y++) {

  				//$this->Tributo[$i] = new tributo($tributo_id[$i]['ID'], $c);

			if($return_type == "object") {
				$query = "SELECT * FROM tributo WHERE ID = '".$tributo_id[$y]->ID."' AND CC = '".$c."'";
				$partita[$i]->Tributo[$y] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "tributo");
			}
			else {
				$query = "SELECT * FROM tributo WHERE ID = '".$tributo_id[$y]['ID']."' AND CC = '".$c."'";
				$partita[$i]["Tributo"][$y] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "tributo");
			}
  		}


		if($return_type == "object") {
			$query = "SELECT ID FROM pignoramento_generale WHERE Partita_ID = '".$results[$i]->ID."'";
			$pignoramento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query), "object");
		}
		else {
			$query = "SELECT ID FROM pignoramento_generale WHERE Partita_ID = '".$results[$i]['ID']."'";
			$pignoramento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		}

      for( $k=0; $k<count($pignoramento_id); $k++)
  		{

			if($return_type == "object") {
				$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_id[$k]->ID." AND CC = '".$c."'";
				$partita[$i]->Pignoramento[$k] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "pignoramento_generale");
			}
			else {
				$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_id[$k]['ID']." AND CC = '".$c."'";
				$partita[$i]["Pignoramento"][$k] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "pignoramento_generale");
			}

			if($return_type == "object") {
				$where_notifica_debitore = "CC = '".$c."' AND Atto_Notificato_ID = '".$pignoramento_id[$k]->ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'debitore'";
				$query = "SELECT ID FROM notifica_atto WHERE ".$where_notifica_debitore;
				$notifica_debitore_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query), "object");
			}
			else {
				$where_notifica_debitore = "CC = '".$c."' AND Atto_Notificato_ID = '".$pignoramento_id[$k]['ID']."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'debitore'";
				$query = "SELECT ID FROM notifica_atto WHERE ".$where_notifica_debitore;
				$notifica_debitore_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
			}

    		for($m=0;$m<count($notifica_debitore_id);$m++)
    		{
				if($return_type == "object") {
					$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_debitore_id[$m]->ID."' AND CC = '".$c."'";
					$partita[$i]->Pignoramento[$k]->Notifiche_Debitore[$m] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "notifica_atto");
				}
				else {
					$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_debitore_id[$m]['ID']."' AND CC = '".$c."'";
					$partita[$i]["Pignoramento"][$k]["Notifiche_Debitore"][$m] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "notifica_atto");
				}
    		}


			if($return_type == "object") {
				$query = "SELECT * FROM pagamento WHERE Atto_ID = '".$partita[$i]->Pignoramento[$k]->ID."' AND Partita_ID = '".$partita[$i]->Pignoramento[$k]->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
				$partita[$i]->Pignoramento[$k]->Pagamento = $this->cls_db->getResultsNull($this->cls_db->ExecuteQuery($query), "pagamento", "object");
			}
			else {
				$query = "SELECT * FROM pagamento WHERE Atto_ID = '".$partita[$i]["Pignoramento"][$k]["ID"]."' AND Partita_ID = '".$partita[$i]["Pignoramento"][$k]["Partita_ID"]."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
				$partita[$i]["Pignoramento"][$k]["Pagamento"] = $this->cls_db->getResultsNull($this->cls_db->ExecuteQuery($query), "pagamento");
			}

  		}

      $atto_id = array();

		if($return_type == "object") {
			$query = "SELECT ID FROM atto WHERE Partita_ID = '".$results[$i]->ID."'";
			$atto_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query), "object");
		}
		else {
			$query = "SELECT ID FROM atto WHERE Partita_ID = '".$results[$i]['ID']."'";
			$atto_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		}

      for( $x=0; $x<count($atto_id); $x++)
      {
		  if($return_type == "object") {
			  $query = "SELECT * FROM atto WHERE ID = " . $atto_id[$x]->ID . " AND CC = '" . $c . "'";
			  $partita[$i]->Atto[$x] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "atto");
		  }
		  else{
			  $query = "SELECT * FROM atto WHERE ID = " . $atto_id[$x]['ID'] . " AND CC = '" . $c . "'";
			  $partita[$i]["Atto"][$x] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "atto");
		  }

		  if($return_type == "object") {
			  $query = "SELECT ID FROM pagamento WHERE Atto_ID = '".$atto_id[$x]->ID."' AND Partita_ID = '".$partita[$i]->Atto[$x]->Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
			  $pagamento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");
		  }
		  else{
			  $query = "SELECT ID FROM pagamento WHERE Atto_ID = '".$atto_id[$x]['ID']."' AND Partita_ID = '".$partita[$i]["Atto"][$x]["Partita_ID"]."' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
			  $pagamento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		  }

        //select_mysql_array("ID", "pagamento","Atto_ID = '".$this->ID."' AND Partita_ID = '".$this->Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%'", "Rata");

    		for( $z=0; $z<count($pagamento_id); $z++)
    		{
				if($return_type == "object") {
					$query = "SELECT * FROM pagamento WHERE ID = '".$pagamento_id[$z]->ID."' AND CC = '".$c."'";
					$partita[$i]->Atto[$x]->Pagamento[$z] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pagamento");
				}
				else{
					$query = "SELECT * FROM pagamento WHERE ID = '".$pagamento_id[$z]['ID']."' AND CC = '".$c."'";
					$partita[$i]["Atto"][$x]["Pagamento"][$z] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pagamento");
				}
    		}
      }
  	}

    return $partita;
  }

  public function selezionaMailPartita($c,$Partita_ID, $ordinamento='DESC')
	{

		$query = "SELECT * FROM email_inviate WHERE CC='".$c."' AND Partita_ID ='".$Partita_ID."' ";
		$query.= "ORDER BY ID ".$ordinamento;

    return $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
	}

  public function percorsi_PEC($val)
	{
		$val = (array) $val;
		$filename_inviato = $val["Oggetto"].".eml";

		$filename_accettazione = "ACCETTAZIONE_".$val["Oggetto"].".eml";
		$filename_mancata_accettazione = "AVVISO_DI_MANCATA_ACCETTAZIONE_".$val["Oggetto"].".eml";
		$filename_consegna = "CONSEGNA_".$val["Oggetto"].".eml";
		$filename_mancata_consegna = "AVVISO_DI_MANCATA_CONSEGNA_".$val["Oggetto"].".eml";
		$filename_anomalia = "ANOMALIA_".$val["Oggetto"].".eml";

		$percorso_oggetto = $this->cartella_PEC($val);

		$path_inviato = $this->percorsoMail($val["CC"], $val["Tipo_Sorgente"], $percorso_oggetto,"server")."/".$filename_inviato;

		$path_accettazione = $this->percorsoMail($val["CC"], $val["Tipo_Sorgente"], $percorso_oggetto,"server")."/".$filename_accettazione;
		$path_mancata_accettazione = $this->percorsoMail($val["CC"], $val["Tipo_Sorgente"], $percorso_oggetto,"server")."/".$filename_mancata_accettazione;
		$path_consegna = $this->percorsoMail($val["CC"], $val["Tipo_Sorgente"], $percorso_oggetto,"server")."/".$filename_consegna;
		$path_mancata_consegna = $this->percorsoMail($val["CC"], $val["Tipo_Sorgente"], $percorso_oggetto,"server")."/".$filename_mancata_consegna;
		$path_anomalia = $this->percorsoMail($val["CC"], $val["Tipo_Sorgente"], $percorso_oggetto,"server")."/".$filename_anomalia;

		$path = array();

		$path['inviato']['path'] = $path_inviato;
		$path['inviato']['filename'] = $filename_inviato;
		$path['accettazione']['path'] = $path_accettazione;
		$path['accettazione']['filename'] = $filename_accettazione;
		$path['mancata_accettazione']['path'] = $path_mancata_accettazione;
		$path['mancata_accettazione']['filename'] = $filename_mancata_accettazione;
		$path['consegna']['path'] = $path_consegna;
		$path['consegna']['filename'] = $filename_consegna;
		$path['mancata_consegna']['path'] = $path_mancata_consegna;
		$path['mancata_consegna']['filename'] = $filename_mancata_consegna;
		$path['anomalia']['path'] = $path_anomalia;
		$path['anomalia']['filename'] = $filename_anomalia;

		return $path;
	}

  public function cartella_PEC($val)
	{
		$esplodi_oggetto = explode("_", $val["Oggetto"]);

		if($esplodi_oggetto[count($esplodi_oggetto)-1] == "NOT".$val["ID_Collegato"])
		{
			$percorso_oggetto = $esplodi_oggetto[0];

			for($i=1;$i<count($esplodi_oggetto);$i++)
			{
				if($i==count($esplodi_oggetto)-1)
					break;

				$percorso_oggetto.= "_".$esplodi_oggetto[$i];
			}

		}
		else
		{
			$percorso_oggetto = $val["Oggetto"];
		}

		return $percorso_oggetto;
	}

  public function percorsoMail($c, $tipo_mail,$oggetto, $ritorno = "web")
  {
    $path = $_SERVER['DOCUMENT_ROOT']."/archivio/Posta_Elettronica/".$c."/".$tipo_mail."/";
    $path.= $oggetto;

    if($ritorno=="server")
      return $path;
    else if($ritorno=="web")
      return substr( $path , strpos( $path , "/archivio/" ));//mostra_file_path($path);
  }

  public function GetDataPartita($p,$c,$a=null)
  {
    $query = "SELECT * FROM partita_tributi WHERE ID = '".$p."' AND CC = '".$c."' AND Is_Discharged=0";
    if($a!=null) $query .= " AND Anno_Riferimento = '".$a."'";

    $val = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"partita_tributi");
    $nav = $this->GetNavigation($p,$c,$a);

    $val["next"] = $nav["next"];
    $val["prev"] = $nav["prev"];

    $query = "SELECT * FROM atto WHERE Partita_ID = '".$p."' AND CC = '".$c."'";
    $val["Atto"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    for( $i=0; $i<count($val["Atto"]); $i++)
    {
      //$query = "SELECT * FROM atto WHERE ID = ".$atto_id[$i]['ID']." AND CC = '".$c."'";
      //$val["Atto"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto");
      //$this->Atto[$i] = new atto( $atto_id[$i]['ID'] , $c );

      $val["Somma_Spese_Notifica"] = $val["Atto"][$i]["Spese_Notifica"];
      $val["Somma_Spese_Notifica"] += $val["Atto"][$i]["CAN"];
      $val["Somma_Spese_Notifica"] += $val["Atto"][$i]["CAD"];

      $val["Atto"][$i]["Semestri"] = "";
      if($val["Atto"][$i]["Atto"] == "Ingiunzione" || $val["Atto"][$i]["Atto"] == "Avviso di intimazione ad adempiere" || $val["Atto"][$i]["Atto"] == "Avviso di messa in mora")
      {
        $val["ultimo_atto"] = $val["Atto"][$i]['ID'];
      }

    }

    //echo "<h1>ID = ".$val["ID"]."</h1>";

    $query = "SELECT * FROM pignoramento_generale WHERE Partita_ID = '".$val["ID"]."' AND CC = '".$c."'";
    $val["Pignoramento"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

		for( $i=0; $i<count($val["Pignoramento"]); $i++)
		{
      //$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_id[$i]['ID']." AND CC = '".$c."'";
			//$val["Pignoramento"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_generale");


      //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$val["Pignoramento"][$i]["ID"]."' AND Partita_ID = '".$val["Pignoramento"][$i]["Partita_ID"]."' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
//
//                                                 QUERY MODIFICATA CON QELLA QUA SOTTO LEVATO NOT LIKE PIGNORAMENTO
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


      $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$val["Pignoramento"][$i]["ID"]."' AND Partita_ID = '".$val["Pignoramento"][$i]["Partita_ID"]."' ORDER BY Rata ASC";
      //echo "<h1>".$query."</h1>";
      //$pagamento_id = select_mysql_array("ID", "pagamento","Atto_ID = '".$this->ID."' AND Partita_ID = '".$this->Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%'", "Rata");
      $val["Pignoramento"][$i]["Pagamento"] = $this->cls_db->GetResults($this->cls_db->ExecuteQuery($query));



      for($x=0;$x<count($val["Pignoramento"][$i]["Pagamento"]);$x++)
      {
        if(strpos($val["Pignoramento"][$i]["Pagamento"][$x]["Tipo_Atto"],'Pignoramento')===false)
        {
          //echo "qui<br>";
          $query = "SELECT * FROM atto WHERE ID = ".$val["Pignoramento"][$i]["Pagamento"][$x]["Atto_ID"]." AND CC = '".$val["Pignoramento"][$i]["Pagamento"][$x]["CC"]."'";
          $atto = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto");//new atto($this->Atto_ID, $this->CC);
        }
        else
        {

          $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$val["Pignoramento"][$i]["Pagamento"][$x]["Atto_ID"]." AND CC = '".$val["Pignoramento"][$i]["Pagamento"][$x]["CC"]."'";
          //echo "<h1>".$query."</h1><br>";
          $atto = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_generale");//new pignoramento($this->Atto_ID, $this->CC);
        }


    		$val["Pignoramento"][$i]["Pagamento"][$x]["Cronologico_Atto"] = $atto["ID_Cronologico"]."/".$atto["Anno_Cronologico"];
      }
		}

    $query = "SELECT ID FROM ricorso_generale WHERE Partita_ID = '".$p."'";
    $ricorso_id = $this->cls_db->GetResults($this->cls_db->ExecuteQuery($query));//select_mysql_array("ID", "ricorso_generale", "Partita_ID = '".$this->ID."'");
//print_r($ricorso_id);
    $countRic = count($ricorso_id);
    if($countRic == 0) {$countRic = 1; $ricorso_id[0]['ID'] = 'null'; }
		for( $i=0; $i<$countRic; $i++)
		{
      $query = "SELECT * FROM ricorso_generale WHERE ID = '".$ricorso_id[$i]['ID']."' AND CC = '".$c."'";
      //echo "<h1>".$query."</h1><br>";
			$val["Ricorso"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"ricorso_generale");//new ricorso_generale( $ricorso_id[$i]['ID'] , $c );

      if($ricorso_id[$i]['ID'] == 'null') $val['Ricorso'][$i]['Ufficio_ID'] = 'null';
      $query = "SELECT * FROM ufficio_giudiziario WHERE ID = ".$val['Ricorso'][$i]['Ufficio_ID'];
      $val['Ricorso'][$i]['Ufficio'] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"ufficio_giudiziario");

      $query = "SELECT ID FROM iter_udienze WHERE Ricorso_ID = '".$ricorso_id[$i]['ID']."' ORDER BY Data_Udienza DESC";
      $udienza_id = $this->cls_db->GetResults($this->cls_db->ExecuteQuery($query));//select_mysql_array("ID", "iter_udienze" , "Ricorso_ID = '".$this->ID."'", "Data_Udienza" , "DESC");

      $countUdienza = count($udienza_id);
      if($countUdienza==0) {$countUdienza = 1; $udienza_id[0]['ID'] = 'null';}
  		for( $y=0; $y<count($udienza_id); $y++)
  		{
        $query = "SELECT * FROM iter_udienze WHERE ID = '".$udienza_id[$y]['ID']."'";
  			$val['Ricorso'][$i]["Udienze"][$y] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"iter_udienze");//new iter_udienze( $udienza_id[$i]['ID'] );
  		}

      if($val['Ricorso'][$i]['Tipo_Ricorso']=="atto_citazione")
      {
        $query = "SELECT * FROM atto_citazione WHERE Ricorso_ID = '".$val['Ricorso'][$i]['ID']."'";
        $val['Ricorso'][$i]["Atto_Citazione"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto_citazione");//new atto_citazione ( $val['ID'] );
      }
      else {
        $query = "SELECT * FROM atto_citazione WHERE Ricorso_ID = 'null'";
        $val['Ricorso'][$i]["Atto_Citazione"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto_citazione");
      }

		}

    if(!isset($val["Pignoramento"])) $val["Pignoramento"] = null;


    return $val;

  }

  private function date_compare($a, $b)
	{
		$t1 = strtotime($a['Data_Inizio']);
		$t2 = strtotime($b['Data_Inizio']);
		return $t1 - $t2;
	}

	public function Array_Selezione_Comuni ($autorizzazione,$cc)
	{
		$where = " CC_Anno = CC AND Gestione_Coattiva = 'Y' ";

		if($autorizzazione==2)
			$where .= " AND CC = '" . $cc. "' ";
		else if($autorizzazione>2)
			$where .= " AND Autorizzazione = '" . $autorizzazione. "' ";

		$array_comuni = $this->select_mysql_array("DISTINCT enti_gestiti.ID, CC, Denominazione", " enti_gestiti, anni_gestiti ", $where, "Denominazione");

		return $array_comuni;
	}

  public function select_mysql_array( $item , $table , $where, $order , $direction = "ASC")
  {
    $query = "SELECT ".$item." FROM ".$table." WHERE ".$where." ORDER BY ".$order." ".$direction;

    return $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
  }

	public function array_scarti( $ID )
	{
		//$n2_scarti = select_mysql_array("ID", "290_n2","N0_ID = '".$ID."' AND Flag_Partita = 'no'");
		$query = "SELECT ID FROM 290_n2 WHERE N0_ID = '".$ID."' AND Flag_Partita = 'no'";
		$n2_scarti = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

		$array = array();
		for( $i=0; $i<count($n2_scarti); $i++)
		{
			$query = "SELECT * FROM 290_n2 WHERE ID = '".$n2_scarti[$i]['ID']."'";
			$array[$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"290_n2");// new N2($n2_scarti[$i]['ID']);

			$query = "SELECT ID FROM 290_n4 WHERE Codice_Partita = '".$array[$i]['Codice_Partita']."' AND N0_ID = '".$array[$i]['N0_ID']."'";
			$n4id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));// select_mysql_array("ID", "290_n4","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");

			$array[$i]["num_n4"] = count($n4id);

			for( $y=0; $y<count($n4id); $y++)
			{
				$query = "SELECT * FROM 290_n4 WHERE ID = '".$n4id[$y]['ID']."'";
				$array[$i]["n4"][$y] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"290_n4");//new N4($n4id[$y]['ID']);
			}
		}

		return $array;
	}

	public function estraiTipoPartita($codice, $array_codici)
	{
		$array_return['Tipo'] = "";
		$array_return['Sottotipo'] = "";

		for($i=0;$i<count($array_codici);$i++){
			if($codice==$array_codici[$i]['Codice_Tributo']){
				$array_return['Tipo'] = $array_codici[$i]['Settore'];
				$array_return['Sottotipo'] = $array_codici[$i]['Sottosettore'];

				return $array_return;
			}
		}

		return null;
	}

	public function getData_290($progr)
	{
		$query = "SELECT * FROM 290_n0_n9 WHERE ID = '".$progr."'";
		$duenovanta = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"290_n0_n9");

		$query = "SELECT ID FROM 290_n1_n5 WHERE N0_ID = '".$duenovanta["ID"]."'";
		$n1id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

//var_dump($duenovanta);

//echo "<h1>n1 --> ".$duenovanta["Record_N1"]."</h1>";
		for( $i=0; $i<(int)$duenovanta["Record_N1"]; $i++)
		{
			$query = "SELECT * FROM 290_n1_n5 WHERE ID = '".$n1id[$i]['ID']."'";
			$duenovanta["n1"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"290_n1_n5");//new N1N5($n1id[$i]['ID']);

			$query = "SELECT ID FROM 290_n2 WHERE N1_ID = '" . $duenovanta["n1"][$i]['ID'] . "' AND N0_ID = '".$duenovanta["n1"][$i]['N0_ID']."'";
			$n2id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));


			/*echo "<h1>".$query."</h1>";
			die;*/
			for( $x=0; $x<$duenovanta["n1"][$i]["Record_N2"]; $x++)
			{
				$query = "SELECT * FROM 290_n2 WHERE ID = '".$n2id[$x]['ID']."'";
				$duenovanta["n1"][$i]["n2"][$x] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"290_n2");//new N2($n2id[$i]['ID']);


				$query = "SELECT * FROM 290_n3 WHERE Codice_Partita = '".$duenovanta["n1"][$i]["n2"][$x]['Codice_Partita']."' AND N0_ID = '".$duenovanta["n1"][$i]["n2"][$x]['N0_ID']."'";
				$a_n3 = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array("ID", "290_n3","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
				$duenovanta["n1"][$i]["n2"][$x]["num_n3"] = count($a_n3);


				for( $y=0; $y<count($a_n3); $y++)
					$duenovanta["n1"][$i]["n2"][$x]["n3"][$y] = $a_n3[$y];


				$query = "SELECT * FROM 290_n4 WHERE Codice_Partita = '".$duenovanta["n1"][$i]["n2"][$x]['Codice_Partita']."' AND N0_ID = '".$duenovanta["n1"][$i]["n2"][$x]['N0_ID']."'";
				$a_n4 = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array("ID", "290_n4","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
				$duenovanta["n1"][$i]["n2"][$x]["num_n4"] = count($a_n4);


				for( $z=0; $z<count($a_n4); $z++)
					$duenovanta["n1"][$i]["n2"][$x]["n4"][$z] = $a_n4[$z];
				//var_dump($a_n4);
				//die;
			}
		}

		return $duenovanta;
	}

	public function getLineByPriority(array $a_line){
		$a_return = array();
		asort($a_line);
		$count = 0;
		foreach ($a_line as $key=>$value){
			if(strpos($key,"type")!==false && $value>0){
				preg_match_all('!\d+!', $key, $matches);
				$splitNumber = $matches[0][0];
				$a_return[$count]['header'] = $a_line['split'.$splitNumber];
				$a_return[$count]['type'] = $a_line['split'.$splitNumber.'_type'];
				$a_return[$count]['categories'] = unserialize($a_line['split'.$splitNumber.'_categories']);
				$a_return[$count]['split_number'] = $splitNumber;
				$count++;
			}
		}
		return $a_return;
	}

  public function seleziona_tariffe( $array )
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

		//	$control_index = "no";

			/*if( $date != null)
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
			{*/
				$index_giusto = count($array_controllo)-1;
				//$control_index = "si";
			//}


		//	if( $control_index == "si" )
		//	{
				$control_element = $this->trova_ID( $array_di_selezione , $array_controllo[$index_giusto]['ID'] );
				if($control_element === false )
				{
					$array_di_selezione[] = $array_controllo[$index_giusto];
				}
		//	}

		}

		return $array_di_selezione;

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
        $results = $this->cls_db->ExecuteQuery($query);// mysql_query($query);
        if( $this->cls_db->getNumberRow($results)==0){
            try {
                // First of all, let's begin a transaction
                $this->cls_db->Start_Transaction();
              	$this->cls_db->Begin_Transaction();

                // A set of queries; if one fails, an exception should be thrown
                $query = "CREATE TEMPORARY TABLE tmp_tariffe SELECT * from tariffe_coazione WHERE CC='*****'";
                $this->cls_db->ExecuteQuery($query);
                $query = "ALTER TABLE tmp_tariffe drop ID, drop CC";
                $this->cls_db->ExecuteQuery($query);
                $query = "INSERT INTO tariffe_coazione SELECT 0,'".$c."',tmp_tariffe.* FROM tmp_tariffe";
                $this->cls_db->ExecuteQuery($query);
                $query = "DROP TABLE tmp_tariffe";
                $this->cls_db->ExecuteQuery($query);

                $this->cls_db->End_Transaction();
                // If we arrive here, it means that no exception was thrown
                // i.e. no query has failed, and we can commit the transaction
              //mysql_query('COMMIT');
            } catch (Exception $e) {
                // An exception has been thrown
                // We must rollback the transaction
                $this->cls_db->Rollback();
                //mysql_query('ROLLBACK');
            }
        }


        $temp_Una_Tantum = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM'", "Descrizione" );
		$una_tantum_lavoro = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('lavoro',Pignoramenti)", "Descrizione" );
		$una_tantum_banca = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('banca',Pignoramenti)", "Descrizione" );
		$una_tantum_inps = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('inps',Pignoramenti)", "Descrizione" );
		$una_tantum_altro = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('altro',Pignoramenti)", "Descrizione" );
		$una_tantum_mobiliare = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('mobiliare',Pignoramenti)", "Descrizione" );
		$una_tantum_beni = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('beni',Pignoramenti)", "Descrizione" );
		$una_tantum_immobiliare = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('immobiliare',Pignoramenti)", "Descrizione" );
		$una_tantum_fermo = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('fermo',Pignoramenti)", "Descrizione" );
		$una_tantum_veicolo = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' AND LOCATE('veicolo',Pignoramenti)", "Descrizione" );
		$Una_Tantum_Group = $this->select_mysql_array( "ID, Tipo, Descrizione, Deposito_Portata" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'UNA TANTUM' GROUP BY Descrizione, ID, Tipo, Deposito_Portata", "Descrizione" );

		$val["Una_Tantum"] = $this->seleziona_tariffe($temp_Una_Tantum);
		$val["Una_Tantum_Lavoro"] = $this->seleziona_tariffe($una_tantum_lavoro);
		$val["Una_Tantum_Banca"] = $this->seleziona_tariffe($una_tantum_banca);
		$val["Una_Tantum_Inps"] = $this->seleziona_tariffe($una_tantum_inps);
		$val["Una_Tantum_Altro"] = $this->seleziona_tariffe($una_tantum_altro);
		$val["Una_Tantum_Mobiliare"] = $this->seleziona_tariffe($una_tantum_mobiliare);
		$val["Una_Tantum_Beni"] = $this->seleziona_tariffe($una_tantum_beni);
		$val["Una_Tantum_Immobiliare"] = $this->seleziona_tariffe($una_tantum_immobiliare);
		$val["Una_Tantum_Fermo"] = $this->seleziona_tariffe($una_tantum_fermo);
		$val["Una_Tantum_Veicolo"] = $this->seleziona_tariffe($una_tantum_veicolo);

		$temp_A_Giorni = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO'", "Descrizione, Deposito_Portata" );
		$a_giorni_lavoro = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('lavoro',Pignoramenti)", "Descrizione" );
		$a_giorni_banca = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('banca',Pignoramenti)", "Descrizione" );
		$a_giorni_inps = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('inps',Pignoramenti)", "Descrizione" );
		$a_giorni_altro = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('altro',Pignoramenti)", "Descrizione" );
		$a_giorni_mobiliare = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('mobiliare',Pignoramenti)", "Descrizione" );
		$a_giorni_beni = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('beni',Pignoramenti)", "Descrizione" );
		$a_giorni_immobiliare = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('immobiliare',Pignoramenti)", "Descrizione" );
		$a_giorni_fermo = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('fermo',Pignoramenti)", "Descrizione" );
		$a_giorni_veicolo = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' AND LOCATE('veicolo',Pignoramenti)", "Descrizione" );
		$A_Giorni_Group = $this->select_mysql_array( "ID, Tipo, Descrizione, Deposito_Portata" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A GIORNO' GROUP BY Descrizione, Deposito_Portata,ID, Tipo", "Descrizione, Deposito_Portata" );;

		$val["A_Giorni"] = $this->seleziona_tariffe($temp_A_Giorni);
		$val["A_Giorni_Lavoro"] = $this->seleziona_tariffe($a_giorni_lavoro);
		$val["A_Giorni_Banca"] = $this->seleziona_tariffe($a_giorni_banca);
		$val["A_Giorni_Inps"] = $this->seleziona_tariffe($a_giorni_inps);
		$val["A_Giorni_Altro"] = $this->seleziona_tariffe($a_giorni_altro);
		$val["A_Giorni_Mobiliare"] = $this->seleziona_tariffe($a_giorni_mobiliare);
		$val["A_Giorni_Beni"] = $this->seleziona_tariffe($a_giorni_beni);
		$val["A_Giorni_Immobiliare"] = $this->seleziona_tariffe($a_giorni_immobiliare);
		$val["A_Giorni_Fermo"] = $this->seleziona_tariffe($a_giorni_fermo);
		$val["A_Giorni_Veicolo"] = $this->seleziona_tariffe($a_giorni_veicolo);

		$temp_A_Km = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM'", "Descrizione" );
		$a_km_lavoro = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('lavoro',Pignoramenti)", "Descrizione" );
		$a_km_banca = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('banca',Pignoramenti)", "Descrizione" );
		$a_km_inps = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('inps',Pignoramenti)", "Descrizione" );
		$a_km_altro = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('altro',Pignoramenti)", "Descrizione" );
		$a_km_mobiliare = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('mobiliare',Pignoramenti)", "Descrizione" );
		$a_km_beni = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('beni',Pignoramenti)", "Descrizione" );
		$a_km_immobiliare = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('immobiliare',Pignoramenti)", "Descrizione" );
		$a_km_fermo = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('fermo',Pignoramenti)", "Descrizione" );
		$a_km_veicolo = $this->select_mysql_array( "*" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' AND LOCATE('veicolo',Pignoramenti)", "Descrizione" );
		$A_Km_Group = $this->select_mysql_array( "ID, Tipo, Descrizione, Deposito_Portata" , "tariffe_coazione" , "CC = '".$c."' AND Tipo = 'A KM' GROUP BY Descrizione, Deposito_Portata, ID, Tipo", "Descrizione" );

		$val["A_Km"] = $this->seleziona_tariffe($temp_A_Km);
		$val["A_Km_Lavoro"] = $this->seleziona_tariffe($a_km_lavoro);
		$val["A_Km_Banca"] = $this->seleziona_tariffe($a_km_banca);
		$val["A_Km_Inps"] = $this->seleziona_tariffe($a_km_inps);
		$val["A_Km_Altro"] = $this->seleziona_tariffe($a_km_altro);
		$val["A_Km_Mobiliare"] = $this->seleziona_tariffe($a_km_mobiliare);
		$val["A_Km_Beni"] = $this->seleziona_tariffe($a_km_beni);
		$val["A_Km_Immobiliare"] = $this->seleziona_tariffe($a_km_immobiliare);
		$val["A_Km_Fermo"] = $this->seleziona_tariffe($a_km_fermo);
		$val["A_Km_Veicolo"] = $this->seleziona_tariffe($a_km_veicolo);

		//$this->Array_Descrizione = $this->select_mysql_array("*" , "tariffe_coazione" , "CC = '".$c."' AND Descrizione = '".addslashes($this->Descrizione)."' AND Deposito_Portata = '".addslashes($this->Deposito_Portata)."'", "Data_Inizio", "DESC");
    return $val;
	}

  public function GetDataPigno($ID,$c,$type="array")
  {
	  //echo "<h1>aaa".$ID."</h1>";
    $ID = $ID==""?'null':$ID;

    $query = "SELECT p.*, dt.Notes as DocumentNotes FROM pignoramento_generale p JOIN document_type dt ON dt.Id=p.DocumentTypeId WHERE p.ID = ".$ID." AND p.CC = '".$c."'";
    if($type=="object") $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_generale");
    else{
		$result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_generale");
		$result->DocumentNotes = "";
	}

    $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$ID." AND CC = '".$c."'";
	  if($type=="object"){
		  $temp = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_spese");//new spese_pignoramento($progr, $c);
		  $temp["Totali_Array"] = $this->totali_spese($temp)["Totali_Array"];
		  $result->Spese_Pignoramento = (object) $temp;
		  //$result->Spese_Pignoramento = (object) $result->Spese_Pignoramento;
	  }else{
		  $result["Spese_Pignoramento"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_spese");//new spese_pignoramento($progr, $c);
		  $result["Spese_Pignoramento"]["Totali_Array"] = $this->totali_spese($result["Spese_Pignoramento"])["Totali_Array"];
	  }



    $query = "SELECT * FROM notifica_atto WHERE CC = '".$c."' AND Atto_Notificato_ID = '".$ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'debitore'";
	  if($type=="object") $result->Notifiche_Debitore = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");
	  else $result["Notifiche_Debitore"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

	  if($type=="object") {
		  if (isset($result->Notifiche_Debitore[0]))
			  $result->Notifica_Debitore = $result->Notifiche_Debitore[0];
	  }
	  else{
		  if (isset($result["Notifiche_Debitore"][0]))
			  $result["Notifica_Debitore"] = $result["Notifiche_Debitore"][0];
	  }


    $query = "SELECT * FROM notifica_atto WHERE CC = '".$c."' AND Atto_Notificato_ID = '".$ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'sollecito'";

	  if($type=="object") $result->Notifica_Sollecito = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");
	  else $result["Notifica_Sollecito"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

	  if($type=="object") $Tipo_switch = $result->Tipo;
	  else $Tipo_switch = $result["Tipo"];

      switch($Tipo_switch)
  		{
  			case 'terzi':

          $query = "SELECT ID FROM pignoramento_presso_terzi WHERE Pignoramento_ID = '".$ID."' AND CC = '".$c."' ORDER BY ID ASC";
				if($type=="object") $terzi_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");
				else $terzi_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

				if(count($terzi_id)==0 && $type=="object") $result->Presso_Terzi = array();
				else if(count($terzi_id)==0 && $type=="array") $result["Presso_Terzi"] = array();

  				for( $i=0; $i<count($terzi_id); $i++ )
  				{
					if($type=="object"){
						$query = "SELECT * FROM pignoramento_presso_terzi WHERE ID = '".$terzi_id[$i]->ID."' AND CC = '".$c."'";
						$result->Presso_Terzi[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_presso_terzi");

						if($result->Presso_Terzi[$i]->Tipo_Terzi!="banca")
						{
							$query = "SELECT * FROM utente WHERE ID = '".$result->Presso_Terzi[$i]->Terzo_ID."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
							$result->Presso_Terzi[$i]->Dati_Terzo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"utente");
						}
						else
						{
							$query = "SELECT * FROM banca WHERE ID = '" . $result->Presso_Terzi[$i]->Terzo_ID . "' AND CC = '*****'";
							$result->Presso_Terzi[$i]->Dati_Terzo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"banca");
						}

						$where_notifica_terzo = "CC = '".$c."' AND Atto_Notificato_ID = '".$result->Presso_Terzi[$i]->Pignoramento_ID."' AND ID_Collegamento = '".$result->Presso_Terzi[$i]->ID."'";
						$where_notifica_terzo.= "AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'terzi'";

						$query = "SELECT ID FROM notifica_atto WHERE ".$where_notifica_terzo." ORDER BY ID ASC";
						$notifica_terzo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");
						for($y=0;$y<count($notifica_terzo_id);$y++)
						{
							$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_terzo_id[$y]->ID."' AND CC = '".$c."'";
							$result->Presso_Terzi[$i]->Notifiche_Terzo[$y] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");
						}

						if(isset($notifica_terzo_id[0]))
						{
							$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_terzo_id[0]->ID."' AND CC = '".$c."'";
							$result->Presso_Terzi[$i]->Notifica = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");
						}

					}
					else{
						$query = "SELECT * FROM pignoramento_presso_terzi WHERE ID = '".$terzi_id[$i]['ID']."' AND CC = '".$c."'";
						$result["Presso_Terzi"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_presso_terzi");

						if($result["Presso_Terzi"][$i]["Tipo_Terzi"]!="banca")
						{
							$query = "SELECT * FROM utente WHERE ID = '".$result["Presso_Terzi"][$i]["Terzo_ID"]."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
							$result["Presso_Terzi"][$i]["Dati_Terzo"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente"); //new utente($result["Presso_Terzi"][$i]["Terzo_ID"], $c);
						}
						else
						{
							$query = "SELECT * FROM banca WHERE ID = '" . $result["Presso_Terzi"][$i]["Terzo_ID"] . "' AND CC = '*****'";
							$result["Presso_Terzi"][$i]["Dati_Terzo"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"banca"); //new banca($result["Presso_Terzi"][$i]["Terzo_ID"], "*****");
						}

						$where_notifica_terzo = "CC = '".$c."' AND Atto_Notificato_ID = '".$result["Presso_Terzi"][$i]["Pignoramento_ID"]."' AND ID_Collegamento = '".$result["Presso_Terzi"][$i]["ID"]."'";
						$where_notifica_terzo.= "AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'terzi'";

						$query = "SELECT ID FROM notifica_atto WHERE ".$where_notifica_terzo." ORDER BY ID ASC";
						$notifica_terzo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array("ID", "notifica_atto" , $where_notifica_terzo,"ID","ASC");
						for($y=0;$y<count($notifica_terzo_id);$y++)
						{
							$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_terzo_id[$y]['ID']."' AND CC = '".$c."'";
							$result["Presso_Terzi"][$i]["Notifiche_Terzo"][$y] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");//new notifica_atto( $notifica_terzo_id[$i]['ID'] , $c );
						}

						if(isset($notifica_terzo_id[0]))
						{
							$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_terzo_id[0]['ID']."' AND CC = '".$c."'";
							$result["Presso_Terzi"][$i]["Notifica"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");//new notifica_atto( $notifica_terzo_id[0]['ID'] , $c );
						}
					}



  				}

  				break;

  			case 'mobiliare':

				if($type=="object") $result->Mobiliare = null;
				else $result["Mobiliare"] = null;

  				break;

  			case 'immobiliare':

          		$query = "SELECT ID FROM pignoramento_immobiliare WHERE Pignoramento_ID = '".$ID."' AND CC = '".$c."'";
				if($type=="object"){
					$immobiliare_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");

					for( $i=0; $i<count($immobiliare_id); $i++ )
					{
						$query = "SELECT * FROM pignoramento_immobiliare WHERE ID = '".$immobiliare_id[$i]->ID."' AND CC = '".$c."'";
						$result->Immobiliare[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_immobiliare");
					}
				}
				else{
					$immobiliare_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

					for( $i=0; $i<count($immobiliare_id); $i++ )
					{
						$query = "SELECT * FROM pignoramento_immobiliare WHERE ID = '".$immobiliare_id[$i]['ID']."' AND CC = '".$c."'";
						$result["Immobiliare"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_immobiliare"); //new pignoramento_immobiliare( $immobiliare_id[$i]['ID'] , $c );
					}
				}


  				break;

  			case 'preav_fermo':

          		$query = "SELECT ID FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$ID."' AND CC = '".$c."'";
				if($type=="object"){
					$preav_fermo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");

					if(count($preav_fermo_id)==0)
					{
						$query = "SELECT * FROM pignoramento_veicolo WHERE ID = 'null' AND CC = '".$c."'";
						$result->Preavviso_Fermo[0] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo");
					}

					for( $i=0; $i<count($preav_fermo_id); $i++ )
					{
						$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$preav_fermo_id[$i]->ID."' AND CC = '".$c."'";
						$result->Preavviso_Fermo[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo");
					}
				}
				else{
					$preav_fermo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

					if(count($preav_fermo_id)==0)
					{
						$query = "SELECT * FROM pignoramento_veicolo WHERE ID = 'null' AND CC = '".$c."'";
						$result["Preavviso_Fermo"][0] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo");
					}

					//echo "<h1>".$query." - ".count($preav_fermo_id)."</h1>";
					for( $i=0; $i<count($preav_fermo_id); $i++ )
					{
						$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$preav_fermo_id[$i]['ID']."' AND CC = '".$c."'";
						$result["Preavviso_Fermo"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo"); //new pignoramento_veicolo( $preav_fermo_id[$i]['ID'] , $c );
					}
				}
  				break;

  			case 'fermo':

				  $query = "SELECT * FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$ID."' AND CC = '".$c."'";
				  if($type = "object") $result->Fermo = $this->cls_db->getResultsNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo","object");
				  else $result["Fermo"] = $this->cls_db->getResultsNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo");

  				break;

  			case 'veicolo':


			  	$query = "SELECT ID FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$ID."' AND CC = '".$c."'";
				if($type=="object"){
					$veicolo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");

					for( $i=0; $i<count($veicolo_id); $i++ )
					{
						//$this->Veicolo[$i] = new pignoramento_veicolo( $veicolo_id[$i]['ID'] , $c );
						$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$veicolo_id[$i]->ID ."' AND CC = '".$c."'";
						$result->Veicolo[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo");
					}

					$where_notifica_istituto = "CC = '".$c."' AND Atto_Notificato_ID = '".$ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'veicolo'";
					$query = "SELECT ID FROM notifica_atto WHERE ".$where_notifica_istituto;

					$notifica_istituto_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");
					if(count($notifica_istituto_id)==0)
					{
						$query = "SELECT * FROM notifica_atto WHERE ID = 'null' AND CC = '".$c."'";
						$result->Notifica_Istituto[0] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");

						$query = "SELECT * FROM email_inviate WHERE ID = '".$result->Notifica_Istituto[0]->ID."'";
						$result->Notifica_Istituto[0]->Email_Object = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"email_inviate");
					}

					for( $i=0; $i<count($notifica_istituto_id); $i++ )
					{
						$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_istituto_id[$i]->ID."' AND CC = '".$c."'";
						$result->Notifica_Istituto[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");

						$query = "SELECT ID FROM email_inviate WHERE CC='".$c."' AND Table_Collegata ='notifica_atto' AND ID_Collegato = ".$result->Notifica_Istituto[$i]->ID;
						$array_email = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
						if(count($array_email)>=1)
						{
							$query = "SELECT * FROM email_inviate WHERE ID = '".$result->Notifica_Istituto[$i]->ID."'";
							$result->Notifica_Istituto[$i]->Email_Object = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"email_inviate");
						}
						else $result->Notifica_Istituto[$i]->Email_Object = null;
					}
				}
				else{
					$veicolo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//

					for( $i=0; $i<count($veicolo_id); $i++ )
					{
						$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$veicolo_id[$i]['ID'] ."' AND CC = '".$c."'";
						$result["Veicolo"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_veicolo");
					}

					$where_notifica_istituto = "CC = '".$c."' AND Atto_Notificato_ID = '".$ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'veicolo'";
					$query = "SELECT ID FROM notifica_atto WHERE ".$where_notifica_istituto;

					$notifica_istituto_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
					if(count($notifica_istituto_id)==0)
					{
						$query = "SELECT * FROM notifica_atto WHERE ID = 'null' AND CC = '".$c."'";
						$result["Notifica_Istituto"][0] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");

						$query = "SELECT * FROM email_inviate WHERE ID = '".$result["Notifica_Istituto"][0]["ID"]."'";
						$result["Notifica_Istituto"][0]["Email_Object"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"email_inviate");
					}

					for( $i=0; $i<count($notifica_istituto_id); $i++ )
					{
						$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_istituto_id[$i]['ID']."' AND CC = '".$c."'";
						$result["Notifica_Istituto"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"notifica_atto");

						$query = "SELECT ID FROM email_inviate WHERE CC='".$c."' AND Table_Collegata ='notifica_atto' AND ID_Collegato = ".$result["Notifica_Istituto"][$i]["ID"];
						$array_email = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
						if(count($array_email)>=1)
						{
							$query = "SELECT * FROM email_inviate WHERE ID = '".$result["Notifica_Istituto"][$i]["ID"]."'";
							//include_once CLASSI . "/classe_email.php";
							$result["Notifica_Istituto"][$i]["Email_Object"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"email_inviate");
						}
						else $result["Notifica_Istituto"][$i]["Email_Object"] = null;
					}
				}



			break;
  		}

	  if($type=="object"){
		  $query = "SELECT ID FROM pagamento WHERE Atto_ID = '".$result->ID."' AND Partita_ID = '".$result->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
		  $pagamento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");

		  for( $i=0; $i<count($pagamento_id); $i++)
		  {
			  $query = "SELECT * FROM pagamento WHERE ID = '".$pagamento_id[$i]->ID."' AND CC = '".$c."'";
			  $result->Pagamento[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pagamento");
		  }
	  }
	  else{
		  $query = "SELECT ID FROM pagamento WHERE Atto_ID = '".$result["ID"]."' AND Partita_ID = '".$result["Partita_ID"]."' AND Tipo_Atto LIKE 'Pignoramento%' ORDER BY Rata ASC";
		  $pagamento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

		  for( $i=0; $i<count($pagamento_id); $i++)
		  {
			  $query = "SELECT * FROM pagamento WHERE ID = '".$pagamento_id[$i]['ID']."' AND CC = '".$c."'";
			  $result["Pagamento"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pagamento");
		  }
	  }

    return $result;

  }


  function pignoramento_stampato ( $tipo_pignoramento , $tipo_stampa , $tipo_terzi = null ,$pigno)
	{
		$cartella = "Pignoramenti";
		if($tipo_pignoramento == "veicolo")
		{
			$cartella.= "/Veicolo";
			$prefisso = "Pignoramento_veicolo_";
		}
		else if($tipo_pignoramento == "terzi")
		{
			if($tipo_terzi == "banca")
			{
				$cartella.= "/Presso_Terzi/Banca";
				$prefisso = "Pignoramento_presso_banca_";
			}
			else if($tipo_terzi == "lavoro")
			{
				$cartella.= "/Presso_Terzi/Datore_di_Lavoro";
				$prefisso = "Pignoramento_presso_lavoro_";
			}
			else
				return "notFound";
		}
		else
			return "notFound";

		if($tipo_stampa=="DEFINITIVA")
		{

			$sottoCartella = "STAMPE DEFINITIVE";
			$link = ATTI . "/" . $pigno["CC"] . "/" . $cartella . "/" . $sottoCartella . "/";
			$link .= $prefisso;
			$link .= $pigno["CC"] . "_";
			$link .= $pigno["Anno_Cronologico"] . "_";
			$link .= $pigno["ID_Cronologico"] . "_";


			$file = array();

			$file['originale'] = "";
			$link_originale = $link.$pigno["Data_Stampa"] . "_originale.pdf";
			if(is_file($link_originale))
				$file['originale'] = $link_originale;

			$file['rel_originale'] = "";
			$link_rel_originale = $link.$pigno["Data_Stampa"] . "_rel_originale.pdf";
			if(is_file($link_rel_originale))
				$file['rel_originale'] = $link_rel_originale;

			$file['stampa_originale'][] = $link_originale;
			$file['stampa_originale'][] = $link_rel_originale;

			$file['rel_debitore_0'] = "";
			$file['debitore'] = "";
			for($rel_deb = 0;$rel_deb<count($pigno["Notifiche_Debitore"]);$rel_deb++)
			{
				$link_rel_debitore = $link.$pigno["Data_Stampa"] . "_rel_debitore_".$rel_deb.".pdf";
				$link_debitore = $link.$pigno["Data_Stampa"] . "_debitore.pdf";
				if(is_file($link_rel_debitore))
				{
					$file['rel_debitore_'.$rel_deb] = $link_rel_debitore;
					if($rel_deb>0)
						$file['stampa_originale'][] = $link_rel_debitore;
				}
				else if(is_file($link_debitore))
				{
					$file['debitore'] = $link_debitore;
				}
			}

			$file['sollecito_debitore_0'] = "";
			$file['sollecito_carabinieri_0'] = "";
      $count = isset($pigno["Notifica_Sollecito"])?count($pigno["Notifica_Sollecito"]):0;
			for($soll = 0;$soll<$count;$soll++)
			{
				$link_sollecito_debitore = $link.$pigno["Data_Stampa"] . "_sollecito_debitore_".$soll.".pdf";
				$link_sollecito_carabinieri = $link.$pigno["Data_Stampa"] . "_sollecito_carabinieri_".$soll.".pdf";

				if(is_file($link_sollecito_debitore))
					$file['sollecito_debitore_'.$soll] = $link_sollecito_debitore;

				if(is_file($link_sollecito_carabinieri))
					$file['sollecito_carabinieri_'.$soll] = $link_sollecito_carabinieri;
			}

			if($tipo_pignoramento == "veicolo")
			{
				$file['rel_istituto_0'] = "";
				for($rel_ist = 0;$rel_ist<count($pigno["Notifica_Istituto"]);$rel_ist++)
				{
					$file['rel_istituto_'.$rel_ist] = "";
					$link_rel_istituto = $link.$pigno["Data_Stampa"] . "_rel_istituto_".$rel_ist.".pdf";
					if(is_file($link_rel_istituto))
					{
						$file['rel_istituto_'.$rel_ist] = $link_rel_istituto;
						if($rel_ist>0)
							$file['stampa_originale'][] = $link_rel_istituto;
					}
				}
			}

			if($tipo_pignoramento == "terzi" && isset($pigno["Presso_Terzi"]))
			{
				for($terzo_x=0;$terzo_x<count($pigno["Presso_Terzi"]);$terzo_x++)
				{
					$file['rel_terzo_'.$terzo_x.'_0'] = "";
          if(!isset($pigno["Presso_Terzi"][$terzo_x]["Notifiche_Terzo"])) return null;

          $countNT = isset($pigno["Presso_Terzi"][$terzo_x]["Notifiche_Terzo"])?count($pigno["Presso_Terzi"][$terzo_x]["Notifiche_Terzo"]):0;

					for($rel_terzo = 0; $rel_terzo<$countNT;$rel_terzo++)
					{
						$file['rel_terzo_'.$terzo_x.'_'.$rel_terzo] = "";
						$link_terzo = $link.$pigno["Data_Stampa"] . "_rel_terzo_".$terzo_x."_".$rel_terzo.".pdf";
						if(is_file($link_terzo))
						{
							$file['rel_terzo_'.$terzo_x.'_'.$rel_terzo] = $link_terzo;
							if($rel_terzo>0)
								$file['stampa_originale'][] = $link_terzo;
						}
					}
				}
			}

			return $file;

		}
		else if($tipo_stampa=="FLUSSO")
		{

			$sottoCartella = "FLUSSI";
			if( $pigno["Data_Flusso"] == "0000-00-00" || $pigno["Data_Flusso"] == null )
				return "notFound";

			$file = array();

			$dir = ATTI . "/" . $this->CC . "/" . $cartella . "/" . $sottoCartella;

			$handle = opendir($dir);
			while (($link = readdir($handle)) != false)
			{
				if ($link != "." && $link != ".." && $link != "thumbs.db" && $link != "ELIMINATI")
				{
					$explodePunto = explode (".", $link);
					$estensione = $explodePunto[1];

					$explode = explode ("_", $explodePunto[0]);
					$control_comune = $explode[3];
					$control_anno = $explode[4];
					$control_numero = $explode[5];
					$control_data = $explode[6];

					if (strtoupper($estensione) == "RAR" &&
							$this->CC == $control_comune &&
							$this->Anno_Flusso == $control_anno &&
							$this->Numero_Flusso == $control_numero)
					{
						$file[1] = $dir."/".$link;
					}

					if (strtoupper($estensione) == "TXT" &&
							$this->CC == $control_comune &&
							$this->Anno_Flusso == $control_anno &&
							$this->Numero_Flusso == $control_numero &&
							$this->Data_Flusso == $control_data )
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

  public function options_select_array_1 ( $array , $campo = "Descrizione" , $campo_trailer = null )
  {
  	$options = "";
  	for($i=0;$i<count($array);$i++)
  	{
  		$options.= "<option value='".$array[$i]['ID']."'>".$array[$i][$campo];

  		if($campo_trailer!=null)
  			$options.= " - ".$array[$i][$campo_trailer];

  		$options.= "</option>";
  	}

  	return $options;
  }

  public function array_notifica( $c = "*****" )
	{
		$query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'A mani' ORDER BY Descrizione ASC";
    $result["Mode_A_Mani"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->Mode_A_Mani = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'A mani'", "Descrizione" );

    $query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Per posta' ORDER BY Descrizione ASC";
    $result["Mode_Per_Posta"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->Mode_Per_Posta = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Per posta'", "Descrizione" );

    $query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Eccezionali' ORDER BY Descrizione ASC";
    $result["Mode_Eccezionali"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->Mode_Eccezionali = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Eccezionali'", "Descrizione" );

    $query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'stato' ORDER BY Descrizione ASC";
    $result["Stati"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->Stati = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'stato'", "Descrizione" );

    $query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'motivo' ORDER BY Descrizione ASC";
    $result["Motivi"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->Motivi = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'motivo'", "Descrizione" );

    $query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'tipo_importato' ORDER BY ID ASC";
    $result["Tipo_Mercurio"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->Tipo_Mercurio = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'tipo_importato'", "ID" );

    $query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'stato_importato' ORDER BY ID ASC";
    $result["Stato_Mercurio"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->Stato_Mercurio = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'stato_importato'", "ID" );

    $query = "SELECT * FROM parametri_notifica WHERE CC = '".$c."' AND Tipo_Dato = 'blocco' ORDER BY Descrizione ASC";
    $result["BloccoCoattiva"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		//$this->BloccoCoattiva = select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'blocco'", "Descrizione" );

    return $result;
	}

  public function carica_firme($firma1, $firma2, $firma3, $parResp)
	{
		$firma = $this->firme_responsabili($parResp);

		if($firma1!="")
		{
			$temp[1]['intestazione'] = $firma[$firma1."_Intestazione"];
			$temp[1]['nome'] = $firma[$firma1."_Nome"];
			if($firma[$firma1."_Testo"]=="si")
				$temp[1]['firma'] = $parResp["Testo_Sostitutivo"];
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
				$temp[2]['firma'] = $parResp["Testo_Sostitutivo"];
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
				$temp[3]['firma'] = $parResp["Testo_Sostitutivo"];
			else
				$temp[3]['firma'] = $firma[$firma3];
		}
		else
		{
			$temp[3]['intestazione'] = "";
			$temp[3]['nome'] = "";
			$temp[3]['firma'] = "";
		}

		return $temp;
	}

  public function firme_responsabili($parResp)
	{
		$firma_path = "/Archivio/Firme/".$parResp["CC"]."/";
		$percorso = FIRME."/".$parResp["CC"]."/";

		$firma = array();
		$firma['Funzionario'] = $firma_path.$parResp["Funzionario_Firma"];
		$firma['Funzionario_Path'] = $percorso.$parResp["Funzionario_Firma"];
		$firma['Funzionario_Nome'] = $parResp["Funzionario_Responsabile"];
		$firma['Funzionario_Intestazione'] = "Il Funzionario responsabile";
		$firma['Funzionario_Testo'] = $parResp["Funzionario_Testo"];

		if($parResp["Funzionario_Firma"]=="" && $parResp["Funzionario_Testo"]!="si")
		{
			$firma['Funzionario'] = "";
			$firma['Funzionario_Path'] = "";
			$firma['Funzionario_Nome'] = "";
			$firma['Funzionario_Intestazione'] = "";
			$firma['Funzionario_Testo'] = "";
		}

		$firma['Responsabile'] = $firma_path.$parResp["Responsabile_Firma"];
		$firma['Responsabile_Path'] = $percorso.$parResp["Responsabile_Firma"];
		$firma['Responsabile_Nome'] = $parResp["Responsabile_Procedimento"];
		$firma['Responsabile_Intestazione'] = "Il Responsabile del procedimento";
		$firma['Responsabile_Testo'] = $parResp["Responsabile_Testo"];

		if($parResp["Responsabile_Firma"]=="" && $parResp["Responsabile_Testo"]!="si")
		{
			$firma['Responsabile'] = "";
			$firma['Responsabile_Path'] = "";
			$firma['Responsabile_Nome'] = "";
			$firma['Responsabile_Intestazione'] = "";
			$firma['Responsabile_Testo'] = "";
		}


		$firma['Ufficiale'] = $firma_path.$parResp["Ufficiale_Firma"];
		$firma['Ufficiale_Path'] = $percorso.$parResp["Ufficiale_Firma"];
		$firma['Ufficiale_Nome'] = $parResp["Ufficiale_Riscossione"];
		$firma['Ufficiale_Intestazione'] = "L'Ufficiale della riscossione";
		$firma['Ufficiale_Testo'] = $parResp["Ufficiale_Testo"];
		if($parResp["Ufficiale_Firma"]=="" && $parResp["Ufficiale_Testo"]!="si")
		{
			$firma['Ufficiale'] = "";
			$firma['Ufficiale_Path'] = "";
			$firma['Ufficiale_Nome'] = "";
			$firma['Ufficiale_Intestazione'] = "";
			$firma['Ufficiale_Testo'] = "";
		}

		$firma['Responsabile_Richieste'] = $firma_path.$parResp["Responsabile_Richieste_Firma"];
		$firma['Responsabile_Richieste_Path'] = $percorso.$parResp["Responsabile_Richieste_Firma"];
		$firma['Responsabile_Richieste_Nome'] = $parResp["Responsabile_Richieste"];
		$firma['Responsabile_Richieste_Intestazione'] = "Responsabile della richiesta";
		$firma['Responsabile_Richieste_Testo'] = $parResp["Responsabile_Richieste_Testo"];
		if($parResp["Responsabile_Richieste_Firma"]=="" && $parResp["Responsabile_Richieste_Testo"]!="si")
		{
			$firma['Responsabile_Richieste'] = "";
			$firma['Responsabile_Richieste_Path'] = "";
			$firma['Responsabile_Richieste_Nome'] = "";
			$firma['Responsabile_Richieste_Intestazione'] = "";
			$firma['Responsabile_Richieste_Testo'] = "";
		}

		return $firma;
	}

  public function value_TestoPignoramento($myId,$tipo_pignoramento,$presso_terzi=null)
  {
    if ($myId == NULL) return null;


    switch($tipo_pignoramento)
  	{
  		case "terzi":

  			switch($presso_terzi)
  			{
  				case "lavoro":
              $query = "SELECT * FROM testo_pignoramento_presso_lavoro WHERE ID = '" . $myId."'";
              $rigaAtto = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_presso_lavoro");// mysql_fetch_assoc($result);


              $query = "SELECT * FROM testo_pignoramento_presso_lavoro_2 WHERE Collegamento_ID = '" . $myId."'";
              $rigaAtto_2 = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_presso_lavoro_2");//mysql_fetch_assoc($result);

              return array_merge($rigaAtto, $rigaAtto_2);
  					break;

  				case "banca":
              $query = "SELECT * FROM testo_pignoramento_presso_banca WHERE ID = '" . $myId."'";
              $rigaAtto = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_presso_banca");// mysql_fetch_assoc($result);


              $query = "SELECT * FROM testo_pignoramento_presso_banca_2 WHERE Collegamento_ID = '" . $myId."'";
              $rigaAtto_2 = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_presso_banca_2");//mysql_fetch_assoc($result);

              return array_merge($rigaAtto, $rigaAtto_2);
  					break;
  			}

  		break;

  		case "veicolo":
          $query = "SELECT * FROM testo_pignoramento_veicolo WHERE ID = '" . $myId."'";
          $rigaAtto = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_veicolo");// mysql_fetch_assoc($result);


          $query = "SELECT * FROM testo_pignoramento_veicolo_2 WHERE Collegamento_ID = '" . $myId."'";
          $rigaAtto_2 = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_veicolo_2");//mysql_fetch_assoc($result);

          return array_merge($rigaAtto, $rigaAtto_2);
  			break;

  			case "preav_fermo" :
            $query = "SELECT * FROM testo_preavviso_fermo WHERE ID = '" . $myId."'";
            return $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_preavviso_fermo");
            break;
  			case "immobiliare" : return null; break;
  			case "fermo" :
            $query = "SELECT * FROM testo_fermo_amministrativo WHERE ID = '" . $myId."'";
            return $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_fermo_amministrativo");
         break;
  	}
  }

  public function AddIndirizzo($progr,$val,$c)
  {
    $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr."' AND Tipo = 'res'";
    $val["Residenza"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new indirizzo( $progr , 'res' , $c );

    if($val["Residenza"]["Via_ID"]!=1)
    {
      $query = "SELECT * FROM toponimo WHERE ID = '".$val["Residenza"]["Via_ID"]."' AND CC_Comune = '".$c."'";
      $val["Residenza"]["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo( $this->Via_ID , $c );
    }
		else if($val["Residenza"]["Via_Cap_ID"]!=1)
    {
      $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$val["Residenza"]["Via_Cap_ID"]."'";
      $val["Residenza"]["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo_cap( $val["Residenza"]["Via_Cap_ID"] );
    }
		else
		$val["Residenza"]["Toponimo"] = null;

    $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr."' AND Tipo = 'dom'";
		$val["Domicilio"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new indirizzo( $progr , 'dom' , $c );
		if($val["Domicilio"]["ID"]==null){$val["Domicilio"] = null;}

    if($val["Domicilio"]["Via_ID"]!=1)
    {
      $query = "SELECT * FROM toponimo WHERE ID = '".$val["Domicilio"]["Via_ID"]."' AND CC_Comune = '".$c."'";
      $val["Domicilio"]["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo( $this->Via_ID , $c );
    }
		else if($val["Domicilio"]["Via_Cap_ID"]!=1)
    {
      $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$val["Domicilio"]["Via_Cap_ID"]."'";
      $val["Domicilio"]["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo_cap( $val["Residenza"]["Via_Cap_ID"] );
    }
		else
		$val["Domicilio"]["Toponimo"] = null;

    $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$progr."' AND Tipo = 'rec'";
		$val["Recapito"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new indirizzo( $progr , 'rec' , $c );
		if($val["Recapito"]["ID"]==null) {$val["Recapito"] = null;}

    if($val["Recapito"]["Via_ID"]!=1)
    {
      $query = "SELECT * FROM toponimo WHERE ID = '".$val["Recapito"]["Via_ID"]."' AND CC_Comune = '".$c."'";
      $val["Recapito"]["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo( $this->Via_ID , $c );
    }
		else if($val["Recapito"]["Via_Cap_ID"]!=1)
    {
      $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$val["Recapito"]["Via_Cap_ID"]."'";
      $val["Recapito"]["Toponimo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//new toponimo_cap( $val["Residenza"]["Via_Cap_ID"] );
    }
		else
		$val["Recapito"]["Toponimo"] = null;

    return $val;
  }

  function tipo_pignoramento($TipoSwitch_1,$TipoSwitch_2,$tipo=null)
	{
		switch($TipoSwitch_1)
		{
			case "veicolo":
				if($tipo==null)
					$tipo_pignoramento = "Pignoramento beni mobili registrati";
				else if($tipo == "sigla" )
					$tipo_pignoramento = "IVG";
				break;
			case "terzi":
				switch($TipoSwitch_2)
				{
					case "lavoro":
						if($tipo==null)
							$tipo_pignoramento = "Pignoramento presso datore di lavoro";
						else if($tipo == "sigla" )
							$tipo_pignoramento = "DatoreLavoro";
						break;
					case "banca":
						if($tipo==null)
							$tipo_pignoramento = "Pignoramento presso banca";
						else if($tipo == "sigla" )
							$tipo_pignoramento = "Banca";
						break;
				}

				break;
			default:
				$tipo_pignoramento = "sconosciuto";
				break;
		}

		return $tipo_pignoramento;
	}

  public function righe_indirizzo($utente)
  {
    if($utente["ID"]==null) return null;
	  //var_dump($utente);
    //echo "<h1>1: ".count($utente["Recapito"])." --- 2: ".count($utente["Domicilio"])."</h1>";

	  if(gettype($utente["Recapito"]) == "object")
		  $utente["Recapito"] = (array) $utente["Recapito"];
	  if(gettype($utente["Domicilio"]) == "object")
		  $utente["Domicilio"] = (array) $utente["Domicilio"];


	  if(isset($utente["Recapito"]["Toponimo"]))
      $indirizzo = (array) $utente["Recapito"];
    else if(isset($utente["Domicilio"]["Toponimo"]))
      $indirizzo = (array) $utente["Domicilio"];
    else
      $indirizzo = (array) $utente["Residenza"];



    if($indirizzo == null) return false;

    if(gettype($indirizzo["Toponimo"]) == "object")
		$indirizzo["Toponimo"] = (array) $indirizzo["Toponimo"];

    if(strtoupper($indirizzo["Paese"])=="ITALIA")
    {
      $ind_1 = isset($indirizzo["Toponimo"]["Nome"])?$indirizzo["Toponimo"]["Nome"]:$indirizzo["Toponimo"]["Odonimo"];
      if($indirizzo["Frazione"])
        $ind_1 = $indirizzo["Frazione"].", ".$ind_1;

      if($indirizzo["Civico"])
        $ind_1.= ", ".$indirizzo["Civico"];
      if($indirizzo["Esponente"])
        $ind_1.= " ".$indirizzo["Esponente"];
      if($indirizzo["Interno"])
        $ind_1.="/".$indirizzo["Interno"];
      if($indirizzo["Dettagli"])
        $ind_1.=", ".$indirizzo["Dettagli"];

      $ind_3 = "";
    }
    else
    {
		$ind_1 = isset($indirizzo["Toponimo"]["Nome"])?$indirizzo["Toponimo"]["Nome"]:$indirizzo["Toponimo"]["Odonimo"];
      if($indirizzo["Frazione"])
        $ind_1 = $indirizzo["Frazione"].", ".$ind_1;

      $ind_3 = $indirizzo["Paese"];
    }

    $ind_2 = $indirizzo["Cap"]." ".$indirizzo["Comune"];
    $ind_2_senza_prov = $ind_2;
    if($indirizzo["Provincia"]!=null)
      $ind_2.= " ".$indirizzo["Provincia"];

    $indirizzo_destinatario = array();
    $indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

    /////////////////////
    $lunghezza = strlen($ind_1);
    if($lunghezza<50)
    {
      $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
      $indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
      $indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
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
        $indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
        $indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
    }
    ///////////////////////

    $indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
    if($ind_3!="")
      $indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);

    $indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
    if($ind_3!="")
      $indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);

    if($utente["Genere"] == "D")
    {
      $indirizzo_destinatario['Destinatario'] = $utente["Ditta"];
      if($utente["Sigla_Forma_Giuridica"]!=null)
        $indirizzo_destinatario['Destinatario'].= " ".$utente["Sigla_Forma_Giuridica"];
    }
    else
    {
      $indirizzo_destinatario['Destinatario'] = $utente["Cognome"]." ".$utente["Nome"];
    }

    if(isset($utente["Recapito"]["Toponimo"]))
      if($utente["Recapito"]["ID"]>0)
        $indirizzo_destinatario['Destinatario'].= " C/O ".strtoupper($utente["Recapito"]["Presso"]);

    if(strlen($indirizzo_destinatario['Destinatario'])>45){
            $a_destinatario = array();
            $a_destinatario[0] = substr($indirizzo_destinatario['Destinatario'], 0, strrpos(substr($indirizzo_destinatario['Destinatario'], 0, 40), ' '));
            $a_destinatario[1] = substr($indirizzo_destinatario['Destinatario'], strlen($a_destinatario[0])+1, 40);
            $indirizzo_destinatario['a_destinatario'] = $a_destinatario;
        }

    return $indirizzo_destinatario;
  }

  public function righe_indirizzo_s($val)
	{
		$ind_1 = $val["Toponimo"];

		if($val["Civico"])
			$ind_1.= ", ".$val["Civico"];
		if($val["Esponente"])
			$ind_1.= $val["Esponente"];
		if($val["Interno"])
			$ind_1.="/".$val["Interno"];
		if($val["Dettagli"])
			$ind_1.=", ".$val["Dettagli"];

		$ind_3 = "";

		$ind_2 = $val["Cap"]." ".$val["Comune"];
		$ind_2_senza_prov = $ind_2;
		if($val["Provincia"]!=null)
			$ind_2.= " ".$val["Provincia"];

		$indirizzo_destinatario = array();
		$indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

		/////////////////////
		$lunghezza = strlen($ind_1);
		if($lunghezza<50)
		{
			$indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
			$indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
			$indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
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
			$indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
			$indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
		}
		///////////////////////

			$indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
			if($ind_3!="")
				$indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);

			$indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);

			if($ind_3!="")
				$indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);

			if(isset($this->Denominazione))
				$indirizzo['Destinatario'] = $this->Denominazione;
			else
				$indirizzo['Destinatario'] = "";

			return $indirizzo_destinatario;
	}

  public function righe_indirizzo_s1($val)
	{
		if($val["Paese"]=="Italia")
		{
			$ind_1 = $val["Toponimo"];
			if($val["Frazione"])
				$ind_1 = $val["Frazione"].", ".$ind_1;

			if($val["Civico"])
				$ind_1.= ", ".$val["Civico"];
			if($val["Esponente"])
				$ind_1.= $val["Esponente"];
			if($val["Interno"])
				$ind_1.="/".$val["Interno"];
			if($val["Dettagli"])
				$ind_1.=", ".$val["Dettagli"];

			$ind_3 = "";
		}
		else
		{
			$ind_1 = $val["Toponimo"];
			if($val["Frazione"])
				$ind_1 = $val["Frazione"].", ".$ind_1;

			$ind_3 = $val["Paese"];
		}

		$ind_2 = $val["Cap"]." ".$val["Comune"];
		$ind_2_senza_prov = $ind_2;
		if($val["Provincia"]!=null)
			$ind_2.= " ".$val["Provincia"];

		$fax = "FAX ".$val["Fax"];
		if($val["Fax"]=="")
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

		$indirizzo['Destinatario'] = $val["Denominazione"];

		return $indirizzo;
	}

  public function CercaParametroData ($CCcomune, $dataConfronto, $blocco_stampa = "no")
	{
		$query = "SELECT ID FROM testo_pignoramento_presso_lavoro
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		//$result = safe_query($query);
		$rigaParametro = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//mysql_fetch_assoc($result);

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
			//$result = safe_query($query);
			$rigaParametro = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
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

    //$array_anni = $this->Array_Selezione_Anni($c, $gestione);

    $select = "<select id='select_anno_veloce' onchange='conferma_anno_js(\"".$pagina."\",\"".$c."\")'>";

    for($i=0;$i<count($array_anni);$i++)
      $select.= "<option value='".$array_anni[$i]['Anno']."'>".$array_anni[$i]['Anno']."</option>";

      $select.="</select>";

      return $select;

  }

  public function getDataUtente($progr,$c)
  {
    $query = "SELECT * FROM utente WHERE ID = '".$progr."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
    $val = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente");

    if ($progr==0)
		{
			$query = "SELECT ID FROM utente where CC_Comune='$c' ORDER BY ID ASC LIMIT 1";
			$val["next"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente")["ID"];

			$query = "SELECT ID FROM utente WHERE CC_Comune='$c' ORDER BY ID DESC LIMIT 1";
			$val["prev"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente")["ID"];
		}
		else
		{

			$query = "SELECT ID FROM utente WHERE ( (ID>'$progr') AND (CC_Comune='$c') ) ORDER BY ID ASC LIMIT 1";
			$val["next"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente")["ID"];

			$query = "SELECT ID FROM utente WHERE ( (ID<'$progr') AND (CC_Comune='$c') ) ORDER BY ID DESC LIMIT 1";
			$val["prev"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"utente")["ID"];
		}

    return $val;
  }

  public function GetNavigation($p,$c,$a)
  {
    $nav = array();
    if ($p==0)
		{
			$query = "SELECT ID FROM partita_tributi WHERE CC='".$c."' AND Anno_Riferimento = '".$a."' AND Is_Discharged=0 ORDER BY ID ASC LIMIT 1";
			//$result = safe_query($query);
			//$array_result = mysql_fetch_array($result);
			$nav["next"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"partita_tributi")["ID"];// $array_result['ID'];

			$query = "SELECT ID FROM partita_tributi WHERE CC='".$c."' AND Anno_Riferimento = '".$a."' AND Is_Discharged=0 ORDER BY ID DESC LIMIT 1";
			//$result = safe_query($query);
			//$array_result = mysql_fetch_array($result);
			$nav["prev"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "partita_tributi")["ID"];//$array_result['ID'];

		}
		else
		{
			$query = "SELECT ID FROM partita_tributi	WHERE ( (ID>'$p') AND (CC='$c') AND Anno_Riferimento = '$a') AND Is_Discharged=0 ORDER BY ID ASC LIMIT 1";
			/*$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->next = $array_result['ID'];*/
      $nav["next"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"partita_tributi")["ID"];

			$query = "SELECT ID FROM partita_tributi	WHERE ( (ID<'$p') AND (CC='$c') AND Anno_Riferimento = '$a') AND Is_Discharged=0 ORDER BY ID DESC LIMIT 1";
			/*$result = safe_query($query);
			$array_result = mysql_fetch_array($result);
			$this->prev = $array_result['ID'];*/
      $nav["prev"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"partita_tributi")["ID"];

		}

    return $nav;
  }

  public function verificaPresenzaParametri($val)
	{
		$array_campi = array();
		$stringa = "I seguenti parametri annuali sono mancanti: ";

		if($val["Spese_Notifica"]!=null && $val["Spese_Notifica"]!=0)
			$array_campi['Spese_Notifica'] = "ok";
		else
		{
			$stringa.= " 'Spese notifica ingiunzione' ";
			$array_campi['Spese_Notifica'] = "";
		}

		if($val["Spese_Notifica_Pignoramento"]!=null && $val["Spese_Notifica_Pignoramento"]!=0)
			$array_campi['Spese_Notifica_Pignoramento'] = "ok";
		else
		{
			$stringa.= " 'Spese notifica pignoramento' ";
			$array_campi['Spese_Notifica_Pignoramento'] = "";
		}

		if($val["Spese_Ricerca"]!=null && $val["Spese_Ricerca"]!=0)
			$array_campi['Spese_Ricerca'] = "ok";
		else
		{
			$stringa.= " 'Spese di ricerca' ";
			$array_campi['Spese_Ricerca'] = "";
		}

		if($val["Spese_Postali"]!=null && $val["Spese_Postali"]!=0)
			$array_campi['Spese_Postali'] = "ok";
		else
		{
			$stringa.= " 'Spese posta ordinaria' ";
			$array_campi['Spese_Postali'] = "";
		}

        if($val["Spese_Raccomandata"]!=null && $val["Spese_Raccomandata"]!=0)
            $array_campi['Spese_Raccomandata'] = "ok";
        else
        {
            $stringa.= " 'Spese raccomandata ordinaria' ";
            $array_campi['Spese_Raccomandata'] = "";
        }

		if($val["Spese_Postali_AG"]!=null && $val["Spese_Postali_AG"]!=0)
			$array_campi['Spese_Postali_AG'] = "ok";
		else
		{
			$stringa.= " 'Spese raccomandata AG (Atti Giudiziari)' ";
			$array_campi['Spese_Postali_AG'] = "";
		}

		if($val["CAN"]!=null)
			$array_campi['CAN'] = "ok";
		else
		{
			$stringa.= " 'CAN' ";
			$array_campi['CAN'] = "";
		}

		if($val["CAD"]!=null)
			$array_campi['CAD'] = "ok";
		else
		{
			$stringa.= " 'CAD' ";
			$array_campi['CAD'] = "";
		}

		if($val["A_Mani"]!=null && $val["A_Mani"]!=0)
			$array_campi['A_Mani'] = "ok";
		else
		{
			$stringa.= " 'A mani ingiunzione' ";
			$array_campi['A_Mani'] = "";
		}

		if($val["A_Mani_Pignoramento"]!=null && $val["A_Mani_Pignoramento"]!=0)
			$array_campi['A_Mani_Pignoramento'] = "ok";
		else
		{
			$stringa.= " 'A mani pignoramento' ";
			$array_campi['A_Mani_Pignoramento'] = "";
		}

		if($val["IVA"]!=null && $val["IVA"]!=0)
			$array_campi['IVA'] = "ok";
		else
		{
			$stringa.= " 'IVA' ";
			$array_campi['IVA'] = "";
		}

		if($val["Importo_Minimo"]!=null && $val["Importo_Minimo"]!=0)
			$array_campi['Importo_Minimo'] = "ok";
		else
		{
			$stringa.= " 'Importo minimo' ";
			$array_campi['Importo_Minimo'] = "";
		}

		if($val["Diritto_Riscossione_Minimo"]!=null && $val["Diritto_Riscossione_Minimo"]!=0)
			$array_campi['Diritto_Riscossione_Minimo'] = "ok";
		else
		{
			$stringa.= " 'Diritto riscossione minimo' ";
			$array_campi['Diritto_Riscossione_Minimo'] = "";
		}

		if($val["Diritto_Riscossione_Massimo"]!=null && $val["Diritto_Riscossione_Massimo"]!=0)
			$array_campi['Diritto_Riscossione_Massimo'] = "ok";
		else
		{
			$stringa.= " 'Diritto riscossione massimo' ";
			$array_campi['Diritto_Riscossione_Massimo'] = "";
		}

		if($val["Giorni_Diritto"]!=null && $val["Giorni_Diritto"]!=0)
			$array_campi['Giorni_Diritto'] = "ok";
		else
		{
			$stringa.= " 'Giorni diritto (con pagamento oltre i 60 giorni)' ";
			$array_campi['Giorni_Diritto'] = "";
		}


		$array_campi['Stringa'] = $stringa;

		return $array_campi;

	}

  public function gestione_totali($data,$type="array")
	{
		if($type == "object") {
			if (isset($data->Spese_Pignoramento))
				$totali_spese = $this->totali_spese((array)$data->Spese_Pignoramento);
			else {
				$totali_spese = array('totale_1' => 0, 'totale_2' => 0, 'totale_3' => 0);
			}

			$data->Parziali_Spese_Accessorie[1] = $totali_spese['totale_1'];
			$data->Parziali_Spese_Accessorie[2] = $totali_spese['totale_1'] + $totali_spese['totale_2'];
			$data->Parziali_Spese_Accessorie[3] = $totali_spese['totale_1'] + $totali_spese['totale_2'] + $totali_spese['totale_3'];
			$totale_1 = 0;
			$totale_2 = 0;
			$totale_3 = 0;

			if ($totali_spese['totale_3'] != 0) {
				$totale_3 = $data->Totale_Dovuto;
				$totale_2 = $totale_3 - $totali_spese['totale_3'];
				$totale_1 = $totale_2 - $totali_spese['totale_2'];
			} else {
				if ($totali_spese['totale_2'] != 0) {
					$totale_2 = $data->Totale_Dovuto;
					$totale_1 = $totale_2 - $totali_spese['totale_2'];
				} else {
					if ($data->Totale_Dovuto != "")
						$totale_1 = $data->Totale_Dovuto;
				}
			}

			$data->Totali_Array[1] = number_format($totale_1, 2,",",".");
			$data->Totali_Array[2] = number_format($totale_2, 2,",",".");
			$data->Totali_Array[3] = number_format($totale_3, 2,",",".");

			return $data;
		}
		else{
			if (isset($data["Spese_Pignoramento"]))
				$totali_spese = $this->totali_spese($data["Spese_Pignoramento"]);
			else {
				$totali_spese = array('totale_1' => 0, 'totale_2' => 0, 'totale_3' => 0);
			}

			$dataArray["Parziali_Spese_Accessorie"][1] = $totali_spese['totale_1'];
			$dataArray["Parziali_Spese_Accessorie"][2] = $totali_spese['totale_1'] + $totali_spese['totale_2'];
			$dataArray["Parziali_Spese_Accessorie"][3] = $totali_spese['totale_1'] + $totali_spese['totale_2'] + $totali_spese['totale_3'];
			$totale_1 = 0;
			$totale_2 = 0;
			$totale_3 = 0;

			if ($totali_spese['totale_3'] != 0) {
				$totale_3 = $data["Totale_Dovuto"];
				$totale_2 = $totale_3 - $totali_spese['totale_3'];
				$totale_1 = $totale_2 - $totali_spese['totale_2'];
			} else {
				if ($totali_spese['totale_2'] != 0) {
					$totale_2 = $data["Totale_Dovuto"];
					$totale_1 = $totale_2 - $totali_spese['totale_2'];
				} else {
					if ($data["Totale_Dovuto"] != "")
						$totale_1 = $data["Totale_Dovuto"];
				}
			}

			$dataArray["Totali_Array"][1] = number_format($totale_1, 2,",",".");
			$dataArray["Totali_Array"][2] = number_format($totale_2, 2,",",".");
			$dataArray["Totali_Array"][3] = number_format($totale_3, 2,",",".");

			return $dataArray;
		}

    	return null;
	}

  public function totali_spese($data)
	{
		$totale['totale_1'] = 0;
		$totale['totale_2'] = 0;
		$totale['totale_3'] = 0;
		switch($data["Tipo_Totale_1"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_1"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_1"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_1"];		break;
		}
		switch($data["Tipo_Totale_2"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_2"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_2"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_2"];		break;
		}
		switch($data["Tipo_Totale_3"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_3"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_3"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_3"];		break;
		}
		switch($data["Tipo_Totale_4"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_4"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_4"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_4"];		break;
		}
		switch($data["Tipo_Totale_5"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_5"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_5"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_5"];		break;
		}
		switch($data["Tipo_Totale_6"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_6"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_6"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_6"];		break;
		}
		switch($data["Tipo_Totale_7"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_7"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_7"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_7"];		break;
		}
		switch($data["Tipo_Totale_8"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_8"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_8"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_8"];		break;
		}
		switch($data["Tipo_Totale_9"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_9"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_9"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_9"];		break;
		}
		switch($data["Tipo_Totale_10"])
		{
			case 1:		$totale['totale_1']	+=	$data["Rimborso_10"];		break;
			case 2:		$totale['totale_2']	+=	$data["Rimborso_10"];		break;
			case 3:		$totale['totale_3']	+=	$data["Rimborso_10"];		break;
		}

		$totale["Totali_Array"][1] = number_format($totale['totale_1'],2,",",".");
		$totale["Totali_Array"][2] = number_format($totale['totale_1']+$totale['totale_2'],2,",",".");
		$totale["Totali_Array"][3] = number_format($totale['totale_1']+$totale['totale_2']+$totale['totale_3'],2,",",".");

		return $totale;
	}

  public function spese_array($data)
	{
		$spese = array();

		$spese[1]['ID'] 			= 	$data["Spesa_1_ID"];
		$spese[2]['ID'] 			= 	$data["Spesa_2_ID"];
		$spese[3]['ID'] 			= 	$data["Spesa_3_ID"];
		$spese[4]['ID'] 			= 	$data["Spesa_4_ID"];
		$spese[5]['ID'] 			= 	$data["Spesa_5_ID"];
		$spese[6]['ID'] 			= 	$data["Spesa_6_ID"];
		$spese[7]['ID'] 			= 	$data["Spesa_7_ID"];
		$spese[8]['ID'] 			= 	$data["Spesa_8_ID"];
		$spese[9]['ID'] 			= 	$data["Spesa_9_ID"];
		$spese[10]['ID'] 			= 	$data["Spesa_10_ID"];

		$spese[1]['tipo_spesa']		= 	$data["Tipo_Spesa_1"];
		$spese[2]['tipo_spesa'] 	= 	$data["Tipo_Spesa_2"];
		$spese[3]['tipo_spesa'] 	= 	$data["Tipo_Spesa_3"];
		$spese[4]['tipo_spesa'] 	= 	$data["Tipo_Spesa_4"];
		$spese[5]['tipo_spesa'] 	= 	$data["Tipo_Spesa_5"];
		$spese[6]['tipo_spesa'] 	= 	$data["Tipo_Spesa_6"];
		$spese[7]['tipo_spesa'] 	= 	$data["Tipo_Spesa_7"];
		$spese[8]['tipo_spesa'] 	= 	$data["Tipo_Spesa_8"];
		$spese[9]['tipo_spesa'] 	= 	$data["Tipo_Spesa_9"];
		$spese[10]['tipo_spesa'] 	= 	$data["Tipo_Spesa_10"];

		$spese[1]['extra_spesa']	= 	$data["Extra_Spesa_1"];
		$spese[2]['extra_spesa'] 	= 	$data["Extra_Spesa_2"];
		$spese[3]['extra_spesa'] 	= 	$data["Extra_Spesa_3"];
		$spese[4]['extra_spesa'] 	= 	$data["Extra_Spesa_4"];
		$spese[5]['extra_spesa'] 	= 	$data["Extra_Spesa_5"];
		$spese[6]['extra_spesa'] 	= 	$data["Extra_Spesa_6"];
		$spese[7]['extra_spesa'] 	= 	$data["Extra_Spesa_7"];
		$spese[8]['extra_spesa'] 	= 	$data["Extra_Spesa_8"];
		$spese[9]['extra_spesa'] 	= 	$data["Extra_Spesa_9"];
		$spese[10]['extra_spesa'] 	= 	$data["Extra_Spesa_10"];

		$spese[1]['rimborso'] 		= 	$data["Rimborso_1"];
		$spese[2]['rimborso'] 		= 	$data["Rimborso_2"];
		$spese[3]['rimborso'] 		= 	$data["Rimborso_3"];
		$spese[4]['rimborso'] 		= 	$data["Rimborso_4"];
		$spese[5]['rimborso'] 		= 	$data["Rimborso_5"];
		$spese[6]['rimborso'] 		= 	$data["Rimborso_6"];
		$spese[7]['rimborso'] 		= 	$data["Rimborso_7"];
		$spese[8]['rimborso'] 		= 	$data["Rimborso_8"];
		$spese[9]['rimborso'] 		= 	$data["Rimborso_9"];
		$spese[10]['rimborso'] 		= 	$data["Rimborso_10"];

		$spese[1]['tipo_totale'] 	= 	$data["Tipo_Totale_1"];
		$spese[2]['tipo_totale'] 	= 	$data["Tipo_Totale_2"];
		$spese[3]['tipo_totale'] 	= 	$data["Tipo_Totale_3"];
		$spese[4]['tipo_totale'] 	= 	$data["Tipo_Totale_4"];
		$spese[5]['tipo_totale'] 	= 	$data["Tipo_Totale_5"];
		$spese[6]['tipo_totale'] 	= 	$data["Tipo_Totale_6"];
		$spese[7]['tipo_totale'] 	= 	$data["Tipo_Totale_7"];
		$spese[8]['tipo_totale'] 	= 	$data["Tipo_Totale_8"];
		$spese[9]['tipo_totale'] 	= 	$data["Tipo_Totale_9"];
		$spese[10]['tipo_totale'] 	= 	$data["Tipo_Totale_10"];

		return $spese;

	}

  public function totale_pagamenti($val)
	{
    if(!isset($val["Pagamento"])) return 0;
		$pagamenti = $val["Pagamento"];
		$tot_pagamenti = 0;
		for($q=0;$q<count($pagamenti);$q++)
		{
			$tot_pagamenti+=$pagamenti[$q]["Importo"];
		}

		return $tot_pagamenti;
	}

  function CercaRiferimento ($CC_Comune, $tipo_atto, $progrNotifica, $dataNotifica = null)
  {
    $queryCerca = "SELECT ID FROM notifiche_importate ";
    $queryCerca .= "WHERE CC_Comune = '".$CC_Comune."' AND Tipo_Atto = '".$tipo_atto."' AND Riferimento = '" . $progrNotifica . "' ";
    if($dataNotifica!=null)
        $queryCerca .= "AND Data_Notifica = '".$dataNotifica."'";

    return $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($queryCerca), "notifiche_importate")["ID"];
    /*$resCerca = mysql_query($queryCerca);
    $rigaCerca = mysql_fetch_assoc($resCerca);
    return $rigaCerca['ID'];*/
  }

  public function info_spedizione($val)
	{
	    if($val["Tipo"]!="terzi")
		    $tipo_atto = "PIGNO".strtoupper($val["Tipo"]);
	    else
            $tipo_atto = "PIGNO".strtoupper($val["Tipo_Terzi"]);

		//$notifiche = new notifiche_importate(null);
		if(isset($val["Notifiche_Debitore"][count($val["Notifiche_Debitore"])-1]))
            $dataNot = $val["Notifiche_Debitore"][count($val["Notifiche_Debitore"])-1]["Data_Notifica"];
		else
		    $dataNot = null;

		$id_notifica = $this->CercaRiferimento($val["CC"], $tipo_atto, $val["ID"], $dataNot);
		//unset($notifiche);

     $query = "SELECT * FROM notifiche_importate WHERE ID = '" . $id_notifica . "'";
		$spedizione = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"notifiche_importate");//new notifiche_importate($id_notifica);


		return $spedizione;
	}

  public function checkProcess($processType, array $a_params, $atto){
  	if(isset($atto["Partita_ID"]))
	    if($atto["Partita_ID"]>0){
            $query = "SELECT * FROM appeal WHERE Partita_ID=".$atto["Partita_ID"]." ORDER BY ID DESC LIMIT 1";
            //$result = safe_query($query);
            $a_appeal = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));// mysql_fetch_array($result);
            if(isset($a_appeal)){//era count($a_appeal)>0
                if($a_appeal['ID']>0 && $this->cls_date->Get_DateNewFormat($a_appeal['End_Date'],"DB")==""){
                    return false;
                }
            }
        }

        switch($processType){
            case "ingiunzione":
                return $this->checkIngiunzione($a_params,$atto);
                break;
            case "avviso_mora":
                return $this->checkAvvisoMora($a_params,$atto);
                break;
            case "sollecito_pre_ingiunzione":
                return $this->checkSollecitoPreIngiunzione($a_params,$atto);
                break;
            case "avviso":
                return $this->checkAvviso($a_params,$atto);
                break;
            case "sollecito":
                return $this->checkSollecito($a_params,$atto);
                break;
            case "pignoramento":
                return $this->checkPignoramento($a_params,$atto);
                break;
        }
    }

    public function checkPignoramento(array $a_params,$atto){
  	if(isset($atto["Data_Notifica"]))
        if($atto["Data_Notifica"]==null)
            return false;
        else if($this->controlloPignoramento($atto["ID"],$atto["CC"])!=null){
            return false;
        }
        else if($this->checkPagamenti($a_params,$atto)===false)
            return false;
        else if($this->checkPignoramentoDates($atto)===false)
            return false;
        else if($atto["Stato_Notifica"]!=0 && $atto["Indirizzo_Validato"]!="si")
            return false;
        else
            return true;
    }

    public function checkSollecito(array $a_params,$atto){

        if($this->checkPagamenti($a_params,$atto)===false)
            return false;
        else{
            if(($atto["Atto"]=="Ingiunzione" || $atto["Atto"]=="Sollecito di pagamento")){
                if($atto["Rielabora_Flag"]=="si")
                    return true;
                else if($this->controlloPignoramento($atto["ID"])!=null)
                    return false;
                else if($this->checkExpireDate(true,$atto)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;
        }
    }

    public function checkAvviso(array $a_params,$atto){
        if($this->checkPagamenti($a_params,$atto)===false)
            return false;
        else{
            if(($atto["Atto"]=="Ingiunzione" || $atto["Atto"]=="Avviso di intimazione ad adempiere")){
                if($atto["Rielabora_Flag"]=="si")
                    return true;
                else if($this->controlloPignoramento($atto["ID"])!=null){
                    $array_pignoramenti = $this->controlloPignoramento($atto["ID"]);
                    $pignoramento = $array_pignoramenti[(count($array_pignoramenti)-1)];

                    if($pignoramento["Stato_Stampa"] == "Stampato" || $pignoramento["ID_Cronologico"] > 0)
                        return false;
                }
                else if($atto["Data_Notifica"]==null)
                    return false;
                else if($atto["Rettifica_Flag"]=="si") {
                    return false;
                }
                else if($this->checkExpireDate(false,$atto)===false){
                    return false;
                }
                else if($atto["Stato_Notifica"]!=0 && $atto["Indirizzo_Validato"]!="si"){
                    return false;
                }
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkSollecitoPreIngiunzione(array $a_params, $atto){
        if($this->checkPagamenti($a_params,$atto)===false)
            return false;
        else{
            if($atto["Atto"]=="Sollecito pre ingiunzione"){
                if($atto["Rielabora_Flag"]=="si")
                    return true;
                else if($this->checkExpireDate(false,$atto)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkAvvisoMora(array $a_params,$atto){
        if($this->checkPagamenti($a_params,$atto)===false)
            return false;
        else{
            if($atto["Atto"]=="Avviso di messa in mora" || $atto["Atto"]=="Sollecito pre ingiunzione"){
                if($atto["Rielabora_Flag"]=="si")
                    return true;
                else if($this->checkExpireDate(false,$atto)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkIngiunzione(array $a_params,$atto){
        if($this->checkPagamenti($a_params,$atto)===false)
            return false;
        else{
            if($atto["Rielabora_Flag"]=="si")
                return true;
            else if($atto["Rettifica_Flag"]=="si")
                return true;
            else if( $atto["Data_Notifica"] ==null)//to_mysql_date($this->Data_Notifica)
                return false;
            else if($this->controlloPignoramento($atto["ID"])!=null){
                $array_pignoramenti = $this->controlloPignoramento($atto["ID"]);
                $pignoramento = $array_pignoramenti[(count($array_pignoramenti)-1)];

                if($pignoramento["Stato_Pignoramento"]!="Annullato")
                    return false;
            }
            else if($this->checkExpireDate(false,$atto)===false)
                return false;
            else if($this->Stato_Notifica!=0 && $this->Indirizzo_Validato!="si")
                return false;
            else
                return true;
        }

    }

    public function checkPignoramentoDates($atto){
        $beginDate = new DateTime($atto["Data_Notifica"]);
        $expireDate = new DateTime($atto["Data_Notifica"]);
        switch($atto["Atto"])
        {
            case "Ingiunzione":
                if($atto["Totale_Dovuto"]>=1000)
                    $beginDate->modify("+2 months");
                else
                    $beginDate->modify("+4 months");

                $expireDate->modify("+1 year");
                break;
            case "Avviso di intimazione ad adempiere":
                $beginDate->modify("+1 month");
                $expireDate->modify("+6 months");
                break;
        }


        if( date("Y-m-d") >= $beginDate->format("Y-m-d") && date("Y-m-d") < $expireDate->format("Y-m-d"))
            return true;
        else
            return false;

    }

    public function checkExpireDate($valid,$atto){

        switch($atto["Atto"]){
            case "Sollecito pre ingiunzione":
                if($atto["Data_Stampa"]!=null){
                    $expireDate = new DateTime($atto["Data_Stampa"]);
                    $expireDate->modify("+1 month");
                }
                else
                    return false;

                break;
            case "Avviso di messa in mora":
                if($atto["Data_Notifica"]!=null){
                    $expireDate = new DateTime($atto["Data_Notifica"]);
                    $expireDate->modify("+15 days");
                }
                else
                    return false;

                break;
            case "Ingiunzione":
                if($atto["Data_Notifica"]!=null) {
                    $expireDate = new DateTime($atto["Data_Notifica"]);
                    $expireDate->modify("+1 year");

                    $startDate = new DateTime($atto["Data_Notifica"]);
                    $startDate->modify("+1 month");
                }
                else
                    return false;
                break;
            case "Sollecito di pagamento":
                if($atto["Data_Stampa"]!=null) {
                    $expireDate = new DateTime($atto["Data_Stampa"]);
                    $expireDate->modify("+1 month");
                }
                else
                    return false;
                break;
            case "Avviso di intimazione ad adempiere":
                if($atto["Data_Notifica"]!=null) {
                    $expireDate = new DateTime($atto["Data_Notifica"]);
                    $expireDate->modify("+6 months");
                }
                else
                    return false;

                break;
        }

        if($valid===false) {
            if (isset($expireDate)) {
                if (date("Y-m-d") < $expireDate->format("Y-m-d"))
                    return false;
                else
                    return true;
            }
        }
        else{
            if($atto["Atto"]=="Ingiunzione"){
                if(isset($startDate)){
                    if( date("Y-m-d") < $startDate->format("Y-m-d") )
                        return false;
                    else
                        return true;
                }
                if (isset($expireDate)) {
                    if (date("Y-m-d") > $expireDate->format("Y-m-d"))
                        return false;
                    else
                        return true;
                }
            }
            else{
                if (isset($expireDate)) {
                    if (date("Y-m-d") < $expireDate->format("Y-m-d"))
                        return false;
                    else
                        return true;
                }
            }
        }
//        else if($valid===true){
//            if( date("Y-m-d") > $expireDate->format("Y-m-d") && to_mysql_date($expireDate->format("Y-m-d"))!=null)
//                return false;
//            else
//                return true;
//        }

    }

    public function checkPagamenti(array $a_params,$atto){
        if(!isset($a_params['importo_minimo'])){
            return false;
        }

        $totale_dovuto = $atto["Totale_Dovuto"];

        $data_not = new DateTime($atto["Data_Notifica"]);
        $data_not->modify("+2 months");
        if($this->controlloDataPrimoPagamento($atto)!=null){
            if ($this->controlloDataPrimoPagamento($atto) > $data_not->format('Y-m-d'))
                $totale_dovuto += $atto["Diritto_Riscossione_Massimo"];
            else
                $totale_dovuto += $atto["Diritto_Riscossione_Minimo"];
        }
        else{
            if (date("Y-m-d") > $data_not->format('Y-m-d'))
                $totale_dovuto += $atto["Diritto_Riscossione_Massimo"];
            else
                $totale_dovuto += $atto["Diritto_Riscossione_Minimo"];
        }

        if ($totale_dovuto - $this->pagamenti_completi($atto["ID"],$atto["Partita_ID"]) > $a_params['importo_minimo']) {
            if ($atto["Rate_Previste"] > 0) {
                $scadRate = explode("*",utf8_decode($atto['Scadenze_Rate']));
                $data_rata = new DateTime($this->cls_date->GetDateDB($scadRate[count($scadRate) - 1],"IT"));
                $data_rata->modify("+3 months");
                if (date('Y-m-d') > $data_rata->format('Y-m-d'))
                    return true;
                else
                    return false;
            }
            else
                return true;
        }
        else
            return false;
    }

    public function pagamenti_completi($ID, $Partita_ID){
        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID <= ".$ID." AND Partita_ID = ".$Partita_ID." AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";

        $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
		return isset($result['TOTALE_PAGAMENTI'])?$result['TOTALE_PAGAMENTI']:null;
    }


    public function getTotalAmountDue($atto)
    {
        $a_amount['tot'] = $atto["Totale_Dovuto"];
        if($atto["Rate_Previste"]>0){
            if($atto["Tipo_Totale_Rate"]==1){
                $a_amount['tot'] += $atto["Diritto_Riscossione_Minimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Minimo"];
            }
            else{
                $a_amount['tot'] += $atto["Diritto_Riscossione_Massimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Massimo"];
            }
        }
        else{
            $data_not = new DateTime($atto["Data_Notifica"]);
            $data_not->modify("+2 months");
            if ($this->controlloDataPrimoPagamento($atto) > $data_not->format('Y-m-d')){
                $a_amount['tot'] += $atto["Diritto_Riscossione_Massimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Massimo"];
            }
            else{
                $a_amount['tot'] += $atto["Diritto_Riscossione_Minimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Minimo"];
            }
        }

        $a_amount['tot_residuo'] = $a_amount['tot'] - $this->pagamenti_completi($atto["ID"],$atto["Partita_ID"]);

        return $a_amount;
    }

    public function controlloAttoPignoramento($tipo_partita,$atto)
    {
        switch($atto["Atto"]){
            case "Avviso di intimazione ad adempiere":
                $stringa = $this->controlloAvviso($tipo_partita,$atto);
                break;
            case "Ingiunzione":
                $stringa = $this->controlloIngiunzione($tipo_partita,$atto);
                break;
            default:
                $stringa = "L'ultimo atto elaborato '".$atto["Atto"]."' non permette la creazione del pignoramento.";
                break;
        }

        return $stringa;
    }

    public function controlloIngiunzione($tipo_partita,$atto)
    {
        if($atto["Atto"]!="Ingiunzione")
            $stringa = "L'ultimo atto elaborato e' un/a ".$atto["Atto"]."!";
        else if($this->cls_date->Get_DateNewFormat($atto["Data_Notifica"],"DB")=="")
            $stringa = "Data di notifica assente per l'Ingiunzione!";
        else if(date("Y-m-d",strtotime( $atto["Data_Notifica"]." +120 days" )) >= date("Y-m-d" ) && date("Y-m-d",strtotime( $atto["Data_Notifica"]." +60 days" )) >= date("Y-m-d" ))
            $stringa = "Devono essere passati 60 giorni dalla notifica dell'ingiunzione, se l'importo dovuto e' superiore ai 1000 euro, altrimenti 120 giorni dalla notifica dell'ingiunzione!";
        else if($atto["Data_Notifica"] <= date("Y-m-d" , strtotime( date('Y-m-d')." -1 year" )))
            $stringa = "Ingiunzione scaduta! E' passato un anno dalla data di notifica.";
        else if($atto["Stato_Notifica"]>0 && $atto["Indirizzo_Validato"]!="si")
            $stringa = "Nell'".$atto["Atto"]." e' presente uno stato di giacenza. E' necessario validare l'indirizzo per rendere regolare la notifica.";
        else
            $stringa = $this->controlloPagamenti($tipo_partita,$atto);

        return $stringa;
    }

    public function controlloAvviso($tipo_partita,$atto)
  	{
  		if($atto["Atto"]!="Avviso di intimazione ad adempiere")
  			$stringa = "L'ultimo atto elaborato e' un/a ".$atto["Atto"]."!";
  		else if($this->cls_date->Get_DateNewFormat($atto["Data_Notifica"],"DB")=="")
  			$stringa = "Data di notifica assente per l'Avviso di intimazione ad adempiere!";
  		else if($atto["Data_Notifica"] <= date("Y-m-d" , strtotime( date('Y-m-d')." -180 days" )))
  			$stringa = "Avviso di intimazione ad adempiere scaduto! Sono passati 180 giorni dalla data di notifica.";
          else if($atto["Stato_Notifica"]>0 && $atto["Indirizzo_Validato"]!="si")
              $stringa = "Nell'".$atto["Atto"]." e' presente uno stato di giacenza. E' necessario validare l'indirizzo per rendere regolare la notifica.";
  		else
  			$stringa = $this->controlloPagamenti($tipo_partita,$atto);

  		return $stringa;
  	}

    public function controlloPagamenti($tipo_partita,$atto)
  	{
      $anno = date("Y");
      $query = "SELECT * FROM parametri_annuali WHERE CC = '".$atto["CC"]."' AND Anno = '".$anno."' AND Tipo_Riscossione = '*****'";
      $para = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
  		//$para = new parametri_annuali($atto["CC"], date("Y-m-d"), $tipo_partita);
  		$importo_min = $para["Importo_Minimo"];
  		$rimanenza = $this->dovuto_senza_pagamenti($atto);

  		//CONTROLLO SE PAGAMENTI RICEVUTI
  		if($rimanenza['totale'] - $importo_min <= 0)
  			return $stringa = "Impossibile procedere. Pagamenti ricevuti totalmente o debito residuo inferiore ad importo minimo necessario per procedere alle fasi successive della Riscossione Coattiva.";
  		else
  		{
  			//CONTROLLO SE E' PRESENTE RATEIZZAZIONE
  			$numero_rate = $atto["Rate_Previste"];
  			if($numero_rate==null || $numero_rate==0)
  				return "ok";

  			$scadenza_rate = $atto["Scadenze_Rate"];
  			$importo_rate = $atto["Importi_Rate"];


  			$scadenza = to_mysql_date($scadenza_rate[count($scadenza_rate)-1]);

  			if( $scadenza!=null && date("Y-m-d" , strtotime( $scadenza."+3 month" )) < date("Y-m-d") )
  				return $stringa = "ok";
  			else
  				return $stringa = "Rateizzazione ancora in corso (Per tempistiche lavorative alla scadenza dell'ultima rata e' necessario un tempo tecnico di tre mesi per procedere alle fasi successive). Scadenza ultima rata: ".from_mysql_date($scadenza).".";

  		}

  		return "ok";

  	}

    public function dovuto_senza_pagamenti($atto)
    {
      $rimanenza['tot_pagamenti'] = $this->totale_pagamenti($atto);
      $rimanenza['totale'] = $atto["Totale_Dovuto"] - $rimanenza['tot_pagamenti'];

      $interessi_ing = $atto["Interessi_Precedenti"] + $atto["Interessi"];
      $importo_ing = $atto["Importo"] + $atto["Spese_Notifica"] + $atto["CAD"] + $atto["CAN"];

      $rimanenza['addizionali'] = $atto["Addizionale"];

      if($rimanenza['tot_pagamenti']<$interessi_ing)
      {
        $rimanenza['interessi'] = $interessi_ing - $rimanenza['tot_pagamenti'];
        $rimanenza['importo'] = $importo_ing;
      }
      else
      {
        $rimanenza['interessi'] = 0.00;
        $rimanenza['importo'] = $importo_ing - ( $rimanenza['tot_pagamenti'] - $interessi_ing );
      }

      return $rimanenza;
    }

    public function controlloDataPrimoPagamento($atto)
  	{
  		if(isset($atto["Pagamento"][0]))
  		{
  			return $atto["Pagamento"][0]["Data_Pagamento"];
  		}
  		else
  			return null;
  	}

    public function controlloPignoramento($ID,$c)
  	{
      $query = "SELECT ID FROM pignoramento_generale WHERE Atto_ID = '".$ID."'";
  		$pignoramento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array("ID", "pignoramento_generale","Atto_ID = '".$ID."'");
  		$pignoramento = null;
  		for( $i=0; $i<count($pignoramento_id); $i++)
  		{
        $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_id[$i]['ID']." AND CC = '".$c."'";
        $pignoramento[$i] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        //$pignoramento[$i] = new pignoramento( $pignoramento_id[$i]['ID'] , $this->CC );
  			//if($i==(count($pignoramento_id)-1))
          //        $this->Check_Pignoramento = $pignoramento[$i];
  		}

  		return $pignoramento;
  	}

	public function array_ordinato ( $ordine )
	{
		$query = "SELECT * FROM codice_tributo ORDER BY ".$ordine;
		return $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
		/*$array_codici = select_mysql_array("*", "codice_tributo" , " " , $ordine );

		return $array_codici;*/

	}
	public function settori ( $select = false )
	{
		$array_settori = $this->cls_db->getResults($this->cls_db->ExecuteQuery("SELECT DISTINCT Settore FROM codice_tributo"));// select_mysql_array("DISTINCT Settore", "codice_tributo" );

		if($select==true)
		{
			$option = "";
			for($i=0;$i<count($array_settori);$i++)
			{
				$option .= "<option id='".($i+1)."'>".$array_settori[$i]['Settore']."</option>\n";
			}
			return $option;
		}
		else
			return $array_settori;

	}



}
 ?>
