 <?php
 require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

  include_once(CLS."/cls_CoazioneUtils.php");
  include_once(CLS."/cls_DateTimeInLine.php");
  include_once(INC."/header.php");
  include_once(INC."/menu.php");
  $submenuPageNo = 6;
  include_once(INC."/submenu_partita.php");
  include_once(CLS."/cls_html.php");
  include_once(CLS."/cls_math.php");
  include_once(CLS."/cls_Utils.php");
 //include_once CLS."/cls_db.php";

	set_time_limit(120);

	/*$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');*/

  $cls_coazione = new cls_Coazione();
  $cls_date = new cls_DateTimeI("IT",false);
  $cls_math = new cls_math();
  $cls_Utils = new cls_utils();

	$partita_ID = $cls_help->getVar('partita');
	$pignoramento_ID = $cls_help->getVar('pignoramento');

	if($cls_help->getVar("flagInsert")) $control_submit = "Insert";
	else if($pignoramento_ID!=null)	$control_submit = "Update";
	else echo "<script>location.href='coazione.php?partita=".$partita_ID."&c=".$c."&a=".$a."';</script>";


//CARICAMENTO ENTE ED OPERATORE
	/*$comune = new ente_gestito($c);
	$nome_comune = $comune->Nome;
	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];*/

	$layout = "<script>";

	//$anni_gestiti = new anni_gestiti($c, null);

	if($c==null)
		$options_anni = null;
	else
	{
		//$options_anni = $anni_gestiti->Options_Anni_Veloci($c, "COATTIVA", "pignoramento");

		if($a!=null)
			$layout.="$('#select_anno_veloce option[value=".$a."]').attr('selected',true);";
	}

	$layout.= "</script>";

//CARICAMENTO PARTITA

	//$partita = new partita($partita_ID, $c, $a);
  $partita = $cls_coazione->GetDataPartita($partita_ID, $c, $a);

 $noSaveDelete = false;
 if($partita["Flag_Annullamento"] == "si")
     $noSaveDelete = true;

	$blocco_partita = $partita["Flag_Blocco_Coazione"];
	if($blocco_partita=="si")
	{
		$motivo_blocco = $partita["Motivo_Blocco"];
		$layout.= "<script>$('#flag_blocco').prop('checked',true);</script>";
		$layout.= "<script>$('#motivo_blocco').val('".$motivo_blocco."');cambia_title('motivo_blocco');</script>";
		$note_blocco = $partita["Note_Blocco"];
	}
	else
		$note_blocco = "";
	$ID_Partita = $partita["Comune_ID"];
	$anno_riferimento = $partita["Anno_Riferimento"];
	$utente_ID = $partita["Utente_ID"];

	$prev = $partita["prev"];
	$next = $partita["next"];

//CARICAMENTO DATI INTESTATARIO PARTITA
  $query = "SELECT * FROM utente WHERE ID = '".$utente_ID."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
  $utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");

  $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente["ID"]."' AND Tipo = 'res'";
  $utente["Residenza"] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$utente = new utente($utente_ID,$c);
	$id_utente 				= 	$utente["ID"];
	$genere_utente 			= 	$utente["Genere"];
	$comune_id 				=	$utente["Comune_ID"];
	$cognome_utente 		=	$utente["Cognome"];
	$nome_utente 			=	$utente["Nome"];
	$ditta					=	$utente["Ditta"];

	$indirizzo_utente = $utente["Residenza"];
	$comune_ric_banche = $indirizzo_utente["Comune"];
	$cap_banche = $indirizzo_utente["Cap"];

//CARICAMENTO PARAMETRI
$anno = date('Y');
$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$anno."' AND Tipo_Riscossione = '*****'";
$parametri = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$parametri = new parametri_annuali($c,date('Y-m-d'),$partita["Tipo"]);
	$para_CAD = $parametri["CAD"];
	$para_CAN = $parametri["CAN"];
	$para_Spese_Postali = $parametri["Spese_Postali"];
	if($para_Spese_Postali==null)	$para_Spese_Postali = 0.00;
	$para_Spese_Notifica = $parametri["Spese_Notifica_Pignoramento"];
	if($para_Spese_Notifica==null)	$para_Spese_Notifica = 0.00;

//PARAMETRI ISTITUTO VENDITE
	//$tribunale = new ufficio_giudiziario($utente["Residenza"]["CC_Indirizzo"], "tribunale");

  $query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$utente["Residenza"]["CC_Indirizzo"]."' AND Tipo = 'tribunale' LIMIT 1";
  $tribunale = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ufficio_giudiziario");

  $query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$tribunale["CC_Ufficio"]."' AND Tipo = 'istituto' LIMIT 1";
	$ufficio_vendite = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ufficio_giudiziario");

	if($ufficio_vendite["Denominazione"]=="")
		$denom_istituto = "PARAMETRO ASSENTE! Inserire i parametri di Tribunale / Istituto vendite per il comune di ".$utente["Residenza"]["Comune"];
	else
  {

    if(!isset($ufficio_vendite["Sigla_Forma_Giuridica"])) $ufficio_vendite["Sigla_Forma_Giuridica"] = "";
    $denom_istituto = strtoupper($ufficio_vendite["Denominazione"]." ".$ufficio_vendite["Sigla_Forma_Giuridica"]);
  }


//CARICAMENTO TARIFFE
	//$tariffe_coazione = new tariffe_coazione(null, $c);/////////////////////////////////////ARRIVATO QUI //////////////////////////////////////////////////////////////////
	$tariffe_coazione = $cls_coazione->array_tariffe($c);
	$tariffe_una_tantum = $tariffe_coazione["Una_Tantum"];

	$sp_ac_pigno_immobiliare = "";
	$sp_ac_stima_beni = "";
	$sp_ac_spese_ispezione = "";
	$sp_ac_progetto_attribuzione = "";
	$sp_ac_richiesta_copia = "";
	$sp_ac_iscrizione_fermo = "";
	$sp_ac_revoca_fermo = "";
	$sp_ac_pigno_presso_terzi = "";
	for($i=0;$i<count($tariffe_una_tantum);$i++)
	{
		switch($tariffe_una_tantum[$i]['Descrizione'])
		{
			case "Pignoramento immobiliare o di beni mobili registrati":
				$sp_ac_pigno_immobiliare = $tariffe_una_tantum[$i]['ID'];
				break;
			case "Valutazione/Stima dei beni pignorati e formazione fascicolo":
				$sp_ac_stima_beni = $tariffe_una_tantum[$i]['ID'];
				break;
			case "Ispezione nel registro veicoli":
				$sp_ac_spese_ispezione = $tariffe_una_tantum[$i]['ID'];
				break;
			case "Progetto di attribuzione del ricavato":
				$sp_ac_progetto_attribuzione = $tariffe_una_tantum[$i]['ID'];
				break;
			case "Richiesta di copia autentica dell'atto di pignoramento notificato per la trascrizione nei pubblici registri":
				$sp_ac_richiesta_copia = $tariffe_una_tantum[$i]['ID'];
				break;
			case "Iscrizione del fermo/pignoramento di beni mobili registrati nei pubblici registri":
				$sp_ac_iscrizione_fermo = $tariffe_una_tantum[$i]['ID'];
				break;
			case "Revoca del fermo amministrativo/pignoramento di beni mobili registrati":
				$sp_ac_revoca_fermo = $tariffe_una_tantum[$i]['ID'];
				break;
			case "Pignoramento presso terzi (compresi fitti e pigioni)":
				$sp_ac_pigno_presso_terzi = $tariffe_una_tantum[$i]['ID'];
				break;

		}

	}

	$options_una_tantum = $cls_coazione->options_select_array($tariffe_una_tantum);
	$options_una_tantum_lavoro = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Lavoro"]);
	$options_una_tantum_banca = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Banca"]);
	$options_una_tantum_inps = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Inps"]);
	$options_una_tantum_altro = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Altro"]);
	$options_una_tantum_mobiliare = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Mobiliare"]);
	$options_una_tantum_beni = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Beni"]);
	$options_una_tantum_immobiliare = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Immobiliare"]);
	$options_una_tantum_fermo = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Fermo"]);
	$options_una_tantum_veicolo = $cls_coazione->options_select_array($tariffe_coazione["Una_Tantum_Veicolo"]);

	$tariffe_a_km = $tariffe_coazione["A_Km"];
	$options_a_km = $cls_coazione->options_select_array($tariffe_a_km);
	$options_a_km_lavoro = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Lavoro"]);
	$options_a_km_banca = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Banca"]);
	$options_a_km_inps = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Inps"]);
	$options_a_km_altro = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Altro"]);
	$options_a_km_mobiliare = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Mobiliare"]);
	$options_a_km_beni = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Beni"]);
	$options_a_km_immobiliare = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Immobiliare"]);
	$options_a_km_fermo = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Fermo"]);
	$options_a_km_veicolo = $cls_coazione->options_select_array($tariffe_coazione["A_Km_Veicolo"]);

	$tariffe_a_giorni = $tariffe_coazione["A_Giorni"];
	$options_a_giorni = $cls_coazione->options_select_array($tariffe_a_giorni);
	$options_a_giorni_lavoro = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Lavoro"]);
	$options_a_giorni_banca = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Banca"]);
	$options_a_giorni_inps = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Inps"]);
	$options_a_giorni_altro = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Altro"]);
	$options_a_giorni_mobiliare = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Mobiliare"]);
	$options_a_giorni_beni = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Beni"]);
	$options_a_giorni_immobiliare = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Immobiliare"]);
	$options_a_giorni_fermo = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Fermo"]);
	$options_a_giorni_veicolo = $cls_coazione->options_select_array($tariffe_coazione["A_Giorni_Veicolo"]);


	/**
	 * DATI GENERALI PIGNORAMENTO
	 */

	//$pignoramento = new pignoramento($pignoramento_ID, $c);
  //$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_ID." AND CC = '".$c."'";
  $pignoramento = $cls_coazione->GetDataPigno($pignoramento_ID,$c);// $cls_db->getArrayLine($cls_db->ExecuteQuery($query));


//if($pignoramento["PrinterId"])
 //{
   $cls_html = new cls_html();
   $a_printer = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM printer"));
   $a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>$pignoramento["PrinterId"], "text"=>array("[Name]"));
   $optPrinter = $cls_html->getOptions($a_printer,$a_selection);
 //}


	//ATTO RIFERIMENTO PIGNORAMENTO
	$Atto_ID = isset($pignoramento["Atto_ID"])?$pignoramento["Atto_ID"]:null;
	if($Atto_ID==null){
      $Atto_ID = isset($partita["ultimo_atto"])?$partita["ultimo_atto"]:null;
    }

//////////////////////////////////////////////////////////////////////    ERRORE QUI /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//CARICAMENTO ATTO DI RIFERIMENTO DEL PIGNORAMENTO
	//$atto_pignoramento = new atto($Atto_ID, $c);
 $Atto_ID = $Atto_ID==null?'null':$Atto_ID;

  $query = "SELECT * FROM atto WHERE ID = ".$Atto_ID." AND CC = '".$c."'";
  $atto_pignoramento = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");//getArrayLineNull

  //echo "<h1>qui 1</h1><br>";
  //print_r($atto_pignoramento);
  //echo "<h1>qui 2</h1>";
//echo "<h1>".$query."</h1><br>";
//print_r($atto_pignoramento);
  $atto_pignoramento["Pagamento"] = null;
  if(isset($atto_pignoramento["ID"]) && isset($atto_pignoramento["Partita_ID"]))
  {
    $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$atto_pignoramento["ID"]."' AND Partita_ID = '".$atto_pignoramento["Partita_ID"]."' AND Tipo_Atto NOT LIKE 'Pignoramento%' AND CC = '".$c."' ORDER BY Rata ASC";
    $atto_pignoramento["Pagamento"] = $cls_db->getResults($cls_db->ExecuteQuery($query));
  }


//echo "<br><h1>".$query."</h1>";

 $totali_atto_pignoramento = $cls_coazione->getTotalAmountDue($atto_pignoramento);

if($atto_pignoramento["Pagamento"]!=null)
{
    $checkPigno = $cls_coazione->controlloAttoPignoramento($partita["Tipo"],$atto_pignoramento);
	if($cls_date->Get_DateNewFormat($pignoramento["Data_Stampa"],"DB")==null && $checkPigno!="ok")
		$cls_help->alert("ATTENZIONE! In riferimento all'".$atto_pignoramento["Atto"]." n. ".$atto_pignoramento["ID_Cronologico"]." del ".$atto_pignoramento["Anno_Cronologico"].": ".$checkPigno);
}
//echo "<h1>tot residuo -> ".$totali_atto_pignoramento['tot_residuo']."</h1>";

  //////////////////////////////////////////////////////////////////////////    FINO QUI /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//CRONO PIGNORAMENTO
	$Comune_ID_pignoramento = $pignoramento["Comune_ID"];
	$Anno_Cronologico = $pignoramento["Anno_Cronologico"];
	$ID_Cronologico = $pignoramento["ID_Cronologico"];
	//var_dump($pignoramento);

	//STATO PIGNORAMENTO
	$Stato_Pignoramento = $pignoramento["Stato_Pignoramento"];
	$layout .= "<script>$('#stato_pignoramento').val('".$Stato_Pignoramento."');cambia_title('stato_pignoramento');</script>";
	$Data_Stato_Pignoramento = $cls_date->Get_DateNewFormat($pignoramento["Data_Stato_Pignoramento"],"DB");

	//TIPO PIGNORAMENTO
	$tipo_pignoramento = $pignoramento["Tipo"];
	$tipo_terzi_generale = $pignoramento["Tipo_Terzi"];
	if($pignoramento["Comune_Banca"]!="")
		$comune_ric_banche = $pignoramento["Comune_Banca"];

	//DATE E STATI
	$Data_Elaborazione = $cls_date->Get_DateNewFormat($pignoramento["Data_Elaborazione"],"DB");
	if($Data_Elaborazione==null)	$Data_Elaborazione = date('d/m/Y');

	$Data_Stampa = $cls_date->Get_DateNewFormat($pignoramento["Data_Stampa"],"DB");
    $Data_Flusso = $cls_date->Get_DateNewFormat($pignoramento["Data_Flusso"],"DB");
    if($pignoramento["Numero_Flusso"]>0)
        $numero_flusso = $pignoramento["Numero_Flusso"]."/".$pignoramento["Anno_Flusso"];
    else
        $numero_flusso = "";


/**
 * 	GESTIONE RELATE E STAMPE
 */

	$pdf_originale = "";
	$pdf_rel_originale = "";

	$pdf_rel_debitore = array();

	$pdf_rel_istituto = array();

	$pdf_rel_terzo = array();



	$layout.= "<script>$('#pdf_originale').hide();$('#pdf_rel_originale').hide();</script>";
	$control_originale = "no";
	$pdf_stampato = $cls_coazione->pignoramento_stampato( $pignoramento["Tipo"] , "DEFINITIVA", $pignoramento["Tipo_Terzi"] ,$pignoramento);
	if($pdf_stampato!="notFound")
	{
		if($pdf_stampato['originale']!="")
		{
			$pdf_originale = $cls_Utils->mostra_file_path($pdf_stampato['originale']);
			$layout.= "<script>$('#pdf_originale').show();</script>";
		}

		if($pdf_stampato['rel_originale']!="")
		{
			$control_originale="si";
			$pdf_rel_originale = $cls_Utils->mostra_file_path($pdf_stampato['rel_originale']);
			$layout.= "<script>$('#pdf_rel_originale').show();</script>";
		}
		else
			$control_originale="no";

		for($i=0;$i<count($pignoramento["Notifiche_Debitore"]);$i++)
		{

			$control_debitore[$i] = "no";
			if($pdf_stampato['rel_debitore_'.$i]!=""&&$pdf_stampato['rel_debitore_'.$i]!=null)
			{
				$control_debitore[$i] = "si";
				$pdf_rel_debitore[$i] = $cls_Utils->mostra_file_path($pdf_stampato['rel_debitore_'.$i]);
				$layout.= "<script>$('#pdf_rel_debitore_".$i."').show();</script>";
			}
			else if($pdf_stampato['debitore'])
			{
				$pdf_rel_debitore[$i] = $cls_Utils->mostra_file_path($pdf_stampato['debitore']);
				if($i==0)
					$layout.= "<script>$('#pdf_rel_debitore_".$i."').show();$('#file_pdf_debitore_".$i."').attr('title','Copia debitore');</script>";
			}
			else
				$pdf_rel_debitore[$i] = "";
		}

    $countNS = isset($pignoramento["Notifica_Sollecito"])?count($pignoramento["Notifica_Sollecito"]):0;
		for($i=0;$i<$countNS;$i++)
		{
			$pdf_sollecito_debitore[$i] = "";
			$pdf_sollecito_carabinieri[$i] = "";
			if(isset($pdf_stampato['sollecito_'.$i]))
			{
				if($pdf_stampato['sollecito_'.$i]!="")
				{
					$pdf_sollecito_debitore[$i] = $cls_Utils->mostra_file_path($pdf_stampato['sollecito_debitore'.$i]);
					$pdf_sollecito_carabinieri[$i] = $cls_Utils->mostra_file_path($pdf_stampato['sollecito_carabinieri_'.$i]);
					$layout.= "<script>$('#pdf_sollecito_debitore_".$i."').show();</script>";
					$layout.= "<script>$('#pdf_sollecito_carabinieri_".$i."').show();</script>";
				}
			}
		}

    $countNI = isset($pignoramento["Notifica_Istituto"])?count($pignoramento["Notifica_Istituto"]):0;

		for($i=0;$i<$countNI;$i++)
		{
			$control_istituto[$i] = "no";
			if($pdf_stampato['rel_istituto_'.$i]!="")
			{
				$control_istituto[$i] = "si";
				$pdf_rel_istituto[$i] = $cls_Utils->mostra_file_path($pdf_stampato['rel_istituto_'.$i]);
				$layout.= "<script>$('#pdf_rel_veicolo_".$i."').show();</script>";
			}
			else
				$pdf_rel_istituto[$i] = "";
		}

    $countPT = isset($pignoramento["Presso_Terzi"])?count($pignoramento["Presso_Terzi"]):0;
		for($i=0;$i<$countPT;$i++)
		{
      $countNT = isset($pignoramento["Presso_Terzi"][$i]["Notifiche_Terzo"])?count($pignoramento["Presso_Terzi"][$i]["Notifiche_Terzo"]):0;
			for($y=0;$y<$countNT;$y++)
			{
				$control_terzo[$i][$y] = "no";
				if($pdf_stampato['rel_terzo_'.$i.'_'.$y]!="")
				{
					$control_terzo[$i][$y] = "si";
					$pdf_rel_terzo[$i][$y] = $cls_Utils->mostra_file_path($pdf_stampato['rel_terzo_'.$i.'_'.$y]);
					$layout.= "<script>$('#pdf_rel_terzo_".$i."_".$y."').show();</script>";
				}
				else
					$pdf_rel_terzo[$i][$y] = "";
			}
		}
	}

	$Data_Spedizione = $cls_date->Get_DateNewFormat($pignoramento["Data_Spedizione"],"DB");
	$Data_Consegna = $cls_date->Get_DateNewFormat($pignoramento["Data_Consegna"],"DB");
	$Tipo_Ufficiale = $pignoramento["Tipo_Ufficiale"];
	$layout .= "<script>$('#tipo_ufficiale').val('".$Tipo_Ufficiale."');cambia_title('tipo_ufficiale');</script>";

	$Data_Iscrizione_Fermo = $cls_date->Get_DateNewFormat($pignoramento["Data_Iscrizione_Fermo"],"DB");

	//NOTIFICA DEBITORE
	if(isset($pignoramento["Notifica_Debitore"]))
	{
		$notifiche_debitore = $pignoramento["Notifiche_Debitore"];

		$Spese_Notifica_Debitore = 0;
		for($y=0;$y<count($notifiche_debitore);$y++)
		{
			$ID_Notifica_Debitore[$y] = $notifiche_debitore[$y]["ID"];
			$Tipo_Riscontro_Debitore[$y] = $notifiche_debitore[$y]["Tipo_Riscontro"];
			$Data_Notifica_Debitore[$y] = $cls_date->Get_DateNewFormat($notifiche_debitore[$y]["Data_Notifica"],"DB");

			$Stato_Notifica_Debitore[$y] = $notifiche_debitore[$y]["Stato_Notifica"];
			$Motivo_Notifica_Debitore[$y] = $notifiche_debitore[$y]["Motivo_Notifica"];
			$Modalita_Notifica_Debitore[$y] = $notifiche_debitore[$y]["Modalita_Notifica"];
			$layout.="<script>$('#stato_not_debitore_".$y."').val('".$Stato_Notifica_Debitore[$y]."');</script>";
			$layout.="<script>$('#motivo_not_debitore_".$y."').val('".$Motivo_Notifica_Debitore[$y]."');</script>";
			$layout.="<script>$('#modalita_not_debitore_".$y."').val('".$Modalita_Notifica_Debitore[$y]."');</script>";

			$Modalita_Stampa_Debitore[$y] = $notifiche_debitore[$y]["Modalita_Stampa"];
			$layout.="<script>$('#modalita_stampa_debitore_".$y."').val('".$Modalita_Stampa_Debitore[$y]."');</script>";
			if($Modalita_Stampa_Debitore[$y]=="pec")
				$layout.="<script>$('.tr_validato_debitore_".$y."').hide();</script>";

			$Note_Notifica_Debitore[$y] = $notifiche_debitore[$y]["Note_Notifica"];
			$Ind_Validato_Debitore[$y] = $notifiche_debitore[$y]["Indirizzo_Validato"];
			if($Ind_Validato_Debitore[$y]=="si")
				$layout.="<script>$('#ind_validato_debitore_".$y."').prop('checked',true);</script>";
			$Spese_Notifica_Debitore += $notifiche_debitore[$y]["Spese_Notifica"];
		}

		if($Spese_Notifica_Debitore!=null)	$Spese_Notifica_Debitore = number_format($Spese_Notifica_Debitore,2,",","");
	}
	else
	{
		$Data_Notifica_Debitore[0] = "";
		$ID_Notifica_Debitore[0] = "";
		$Tipo_Riscontro_Debitore[0] = "";
		$Spese_Notifica_Debitore = number_format($para_Spese_Notifica,2,",","");
	}

  $notifiche_sollecito = isset($pignoramento["Notifica_Sollecito"])?$pignoramento["Notifica_Sollecito"]:array();
	//NOTIFICA SOLLECITO
	if(isset($pignoramento["Notifica_Sollecito"]))
	{
		//$notifiche_sollecito = $pignoramento["Notifica_Sollecito"];

		for($y=0;$y<count($notifiche_sollecito);$y++)
		{
			$ID_Notifica_Sollecito[$y] = $notifiche_sollecito[$y]["ID"];

			$Spese_Posta_Sollecito[$y] = number_format($notifiche_sollecito[$y]["Spese_Notifica"],2,",","");

			$Data_Stampa_Sollecito[$y] = $cls_date->Get_DateNewFormat($notifiche_sollecito[$y]["Data_Stampa"],"DB");
			$Data_Elaborazione_Sollecito[$y] = $cls_date->Get_DateNewFormat($notifiche_sollecito[$y]["Data_Elaborazione"],"DB");

			$Modalita_Stampa_Sollecito[$y] = $notifiche_sollecito[$y]["Modalita_Stampa"];
			$layout.="<script>$('#modalita_stampa_sollecito_".$y."').val('".$Modalita_Stampa_Sollecito[$y]."');</script>";
		}
	}


	//TOTALI GENERALI
	$Importo_Dovuto = $pignoramento["Importo_Dovuto"];
	if($Importo_Dovuto==null)	$Importo_Dovuto = $totali_atto_pignoramento['tot_residuo'];

	if(isset($partita["Pignoramento"][0]) && $pignoramento_ID==null){
		$pignoramento_precedente = $partita["Pignoramento"][count($partita["Pignoramento"])-1];
		$pagamenti_pigno = $cls_coazione->totale_pagamenti($pignoramento_precedente);
		$Importo_Dovuto = $pignoramento_precedente["Importo_Dovuto"] + $pignoramento_precedente["Totale_Spese_Notifica"] - $pagamenti_pigno;
	}

	//COEFFICIENTE DI APPLICAZIONE
	//$coeff = new coefficiente_coazione("*****", $Importo_Dovuto );
  if($Importo_Dovuto!=null)
  {
    $query = "SELECT Percentuale FROM coefficiente_coazione WHERE CC = '*****' AND ( ( Credito_Minimo <= ".$Importo_Dovuto." AND Credito_Massimo >= ".$Importo_Dovuto." ) OR ( Credito_Massimo = 0 AND Credito_Minimo <= ".$Importo_Dovuto." ))";
    $coeff = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
  }
  $percentuale = isset($coeff["Percentuale"])?$coeff["Percentuale"]:0;//  $coeff->Percentuale;
  $percentuale = 100;
	//if($percentuale==null)	$percentuale = 0;

	$Tot_Spese_Notifica_Debitore = $pignoramento["Spese_Notifica_Debitore"];
	if($Tot_Spese_Notifica_Debitore==null)	$Tot_Spese_Notifica_Debitore = number_format((double) $Spese_Notifica_Debitore,2,",","");

	$Tot_Spese_Notifica_Terzi = $pignoramento["Spese_Notifica_Terzi"];
	$Totale_Spese_Notifica = $pignoramento["Totale_Spese_Notifica"];
	if($Totale_Spese_Notifica==null)	$Totale_Spese_Notifica = (double) $Tot_Spese_Notifica_Debitore + (double) $Tot_Spese_Notifica_Terzi;

	$Totale_Spese_Accessorie = $pignoramento["Totale_Spese_Accessorie"];
	$Totale_Parziale = $Importo_Dovuto + $Totale_Spese_Notifica;

	$Totale_Dovuto = $pignoramento["Totale_Dovuto"];
	if($Totale_Dovuto==null)	$Totale_Dovuto = (double) $Totale_Parziale + (double) $Totale_Spese_Accessorie;



	if($Importo_Dovuto!=null)				$Importo_Dovuto = number_format((double) $Importo_Dovuto,2,",","");
	if($Tot_Spese_Notifica_Debitore!=null)	$Tot_Spese_Notifica_Debitore = number_format((double) $Tot_Spese_Notifica_Debitore,2,",","");
	if($Tot_Spese_Notifica_Terzi!=null)		$Tot_Spese_Notifica_Terzi = number_format((double) $Tot_Spese_Notifica_Terzi,2,",","");
	if($Totale_Spese_Notifica!=null)		$Totale_Spese_Notifica = number_format((double) $Totale_Spese_Notifica,2,",","");
	if($Totale_Parziale!=null)				$Totale_Parziale = number_format((double) $Totale_Parziale,2,",","");
	if($Totale_Spese_Accessorie!=null)		$Totale_Spese_Accessorie = number_format((double) $Totale_Spese_Accessorie,2,",","");
	if($Totale_Dovuto!=null)				$Totale_Dovuto = number_format((double) $Totale_Dovuto,2,",","");

	$totali_Array_temp = $cls_coazione->gestione_totali($pignoramento);
	$TOTALI_ARRAY = $totali_Array_temp["Totali_Array"];

	//NOTE
	$Note = $pignoramento["Note"];

	//RATE
	$Rate_Previste = $pignoramento["Rate_Previste"];
	$Importi_Rate = $pignoramento["Importi_Rate"];
	$Scadenze_Rate = $pignoramento["Scadenze_Rate"];
	$Data_Richiesta_Rate = $pignoramento["Data_Richiesta_Rate"];
	$Tipo_Totale_Rate = $pignoramento["Tipo_Totale_Rate"];

	$disable_radio_1 = "";
	$disable_radio_2 = "";
	$disable_radio_3 = "";

	$checked_radio_1 = " checked ";
	$checked_radio_2 = "";
	$checked_radio_3 = "";
	if($Rate_Previste==0)
	{
		$Rate_Previste = null;
		$disable = ' disabled ';
		$rateizza = '';
	}
	else
	{
		$rateizza = ' checked ';
		$disable = '';

		switch($Tipo_Totale_Rate)
		{
			case "1":
				$checked_radio_2 = "";
				$disable_radio_2 = " disabled ";
				$checked_radio_1 = " checked ";
				$disable_radio_1 = "";
				$checked_radio_3 = " disabled ";
				$disable_radio_3 = "";
				break;
			case "2":

				$checked_radio_1 = "";
				$disable_radio_1 = " disabled ";
				$checked_radio_2 = " checked ";
				$disable_radio_2 = "";
				$checked_radio_3 = " disabled ";
				$disable_radio_3 = "";
				break;
			case "3":
				$checked_radio_1 = "";
				$disable_radio_1 = " disabled ";
				$checked_radio_3 = " checked ";
				$disable_radio_3 = "";
				$checked_radio_2 = " disabled ";
				$disable_radio_2 = "";
				break;
		}
	}


	$layout.="<script>$('#AR_fronte').hide();</script>";
	$layout.="<script>$('#AR_retro').hide();</script>";
    $PATH_SISTEMA = str_replace("Program Files (x86)", "progra~2", $_SERVER['DOCUMENT_ROOT']);
    $PathImmaginiNotifiche = "/archivio/Notifiche/";
    $PathCompletoImmaginiNotifiche = $PATH_SISTEMA . $PathImmaginiNotifiche;
	$pathAR = IMMAGINI_NOTIFICHE_WEB."/".$c."/";
	$pathcompletoAR = IMMAGINI_NOTIFICHE."/".$c."/";
	$info_spedizione = $cls_coazione->info_spedizione($pignoramento);
	if($info_spedizione["ID"]!=null)
	{
		if(file_exists($pathcompletoAR.$info_spedizione["Immagine_Fronte"]))
		{
			$layout.="<script>$('#AR_fronte').show();</script>";
			$layout.="<script>$('#AR_fronte').attr('onclick','apri_notifica(\"".$pathcompletoAR.$info_spedizione["Immagine_Fronte"]."\")')</script>";
		}

		if(file_exists($pathcompletoAR.$info_spedizione["Immagine_Retro"]))
		{
			$layout.="<script>$('#AR_retro').show();</script>";
			$layout.="<script>$('#AR_retro').attr('onclick','apri_notifica(\"".$pathcompletoAR.$info_spedizione["Immagine_Retro"]."\")')</script>";
		}
	}

 /**
  * SPESE PIGNORAMENTO
  */
	$Spese_Pignoramento = $pignoramento["Spese_Pignoramento"];

	if(!empty($Spese_Pignoramento)){
        $TOTALI_SPESE_ARRAY = $Spese_Pignoramento["Totali_Array"];
        $Incremento_Percentuale = $Spese_Pignoramento["Incremento_Percentuale"];
        $Spese_Accessorie = $cls_coazione->spese_array($Spese_Pignoramento);
        $Totale_Rimborso = $Spese_Pignoramento["Totale_Rimborso"];
	}
	else{
        $TOTALI_SPESE_ARRAY = array(1=>0,2=>0,3=>0);
        $Incremento_Percentuale = 0;
        for($k=1;$k<=10;$k++){
            $Spese_Accessorie[$k]['ID'] = 0;
            $Spese_Accessorie[$k]['tipo_spesa'] = 0;
            $Spese_Accessorie[$k]['extra_spesa'] = 0;
            $Spese_Accessorie[$k]['rimborso'] = 0;
            $Spese_Accessorie[$k]['tipo_totale'] = 0;
        }
        $Totale_Rimborso = 0;
    }

	$layout.="<script>hide_spese();</script>";

	for($k=1;$k<11;$k++)
	{
		if($Spese_Accessorie[$k]['tipo_totale']!=0)
			$layout.="<script>$('#tot_parziale_".$k."').val('".$Spese_Accessorie[$k]['tipo_totale']."');</script>";
		if($Spese_Accessorie[$k]['extra_spesa']==null)
			$Spese_Accessorie[$k]['extra_spesa'] = "0";
		if($Spese_Accessorie[$k]['ID']!=null)
			$layout.="<script>scelta_spesa(".$k.",".$Spese_Accessorie[$k]['ID'].");</script>";
	}


	if($Totale_Rimborso == null)	$Totale_Rimborso = "0,00";

	$layout .= "<script>$('#tipo_pignoramento').val('".$tipo_pignoramento."');</script>";
	$layout .= "<script>scelta_pignoramento('ingresso');</script>";
	$layout .= "<script>$('.pignoramento_2').hide();</script>";
	$layout .= "<script>$('.pignoramento_3').hide();</script>";
	$layout .= "<script>$('.pignoramento_4').hide();</script>";
	$layout .= "<script>$('.pignoramento_5').hide();</script>";

	/**
	 * PRESSO TERZI
	 */
	$count_terzi = 0;
	//DEFINIZIONE VARIABILI PRESSO TERZI
	$terzo_ID = array(0,0,0);
	$nome_cognome_terzo = array('','','');
	$nome_cognome_lavoro = array('','','');
	$nome_cognome_banca = array('','','');
	$nome_cognome_inps = array('','','');
	$nome_cognome_altro = array('','','');

	$Fonte_Dati = array('','','');
	$Fonte_Dati_lavoro = array('','','');
	$Fonte_Dati_banca = array('','','');
	$Fonte_Dati_inps = array('','','');
	$Fonte_Dati_altro = array('','','');

	$azienda_lavoro = array('','','');

	$Note_Terzi = array('','','');
	$Note_Terzi_lavoro = array('','','');
	$Note_Terzi_banca = array('','','');
	$Note_Terzi_inps = array('','','');
	$Note_Terzi_altro = array('','','');

	$ID_Notifica_Terzo = array(array(),array(),array());
	$Tipo_Riscontro_Terzo = array(array(),array(),array());
	$Data_Notifica = array(array(),array(),array());

	$Stato_Notifica = array(array(),array(),array());

	$Motivo_Notifica = array(array(),array(),array());

	$Modalita_Notifica = array(array(),array(),array());

	$Note_Notifica = array(array(),array(),array());

	$Spese_Notifica = array('','','');

	$CAN = array('','','');
	$CAD = array('','','');
	$CAN_CAD = array('','','');

	$Somma_Spese_Notifica_Terzo = array('','','');

	$Tipo_Terzi = array('','','');
	$Tipo_Contratto_Lavoro = array('','','');
	$Data_Costituzione_Ditta_Lavoro = array('','','');
	$Data_Ditta_Operativa_Lavoro = array('','','');
	$Data_Dipendenze_Lavoro = array('','','');
	$Tipo_Titolo_Banca = array('','','');
	$Titolo_Banca = array('','','');
	$Intestatario_Banca = array('','','');
	$Coointestatari_Banca = array('','','');
	$Tipo_Pensione_Inps = array('','','');
	$Libretto_Inps = array('','','');
	$Tipo_Titolo_Altro = array('','','');
	$Titolo_Altro = array('','','');
	$Tipo_Credito_Altro = array('','','');
	$Data_Emissione_Altro = array('','','');
	$Data_Scadenza_Altro = array('','','');

	//VEICOLO
	$marca_veicolo = array('','','');
	$modello_veicolo = array('','','');
	$targa_veicolo = array('','','');
  $telaio_veicolo = array('','','');
    $data_fermo_veicolo = array('','','');
	$data_visura_veicolo = array('','','');
	$portata_veicolo = array('','','');
	$valore_veicolo = array('','','');
	$anno_immatricolazione_veicolo = array('','','');

	$ID_Notifica_Veicolo = "";
	$Tipo_Riscontro_Veicolo = array();
	$Data_Notifica_Veicolo = array();
	$CAN_CAD_Veicolo = "";
	$Spese_Notifica_Veicolo = number_format($para_Spese_Notifica,2,",","");;

	//PREAVVISO FERMO
	$marca_preav_fermo = array('','','');
	$modello_preav_fermo = array('','','');
	$targa_preav_fermo = array('','','');
	$data_visura_preav_fermo = array('','','');
	$portata_preav_fermo = array('','','');
	$valore_preav_fermo = array('','','');
	$anno_immatricolazione_preav_fermo = array('','','');

	//FERMO
	$marca_fermo = array('','','');
	$modello_fermo = array('','','');
	$targa_fermo = array('','','');
	$data_visura_fermo = array('','','');
	$portata_fermo = array('','','');
	$valore_fermo = array('','','');
	$anno_immatricolazione_fermo = array('','','');

	//IMMOBILIARE
	$Tipo_Immobiliare = array('','','');
	$Situazione_Immobiliare = array('','','');
	$Foglio_Immobiliare = array('','','');
	$Particella_Immobiliare = array('','','');
	$Subalterno_Immobiliare = array('','','');
	$Classe_Immobiliare_Fabbricato = array('','','');
	$Classe_Immobiliare_Terreno = array('','','');
	$Annotazioni_Immobiliare = array('','','');
	$Sezione_Fabbricato_Immobiliare = array('','','');
	$Zona_Censuaria_Fabbricato_Immobiliare = array('','','');
	$Categoria_Fabbricato_Immobiliare = array('','','');
	$Consistenza_Fabbricato_Immobiliare = array('','','');
	$Superficie_Fabbricato_Immobiliare = array('','','');
	$Rendita_Fabbricato_Immobiliare = array('','','');
	$Indirizzo_Fabbricato_Immobiliare = array('','','');
	$Protocollo_Notifica_Fabbricato_Immobiliare = array('','','');
	$Porzione_Terreno_Immobiliare = array('','','');
	$Qualita_Terreno_Immobiliare = array('','','');
	$Descrizione_Qualita_Terreno_Immobiliare = array('','','');
	$HA_Ettari_Terreno_Immobiliare = array('','','');
	$A_Are_Terreno_Immobiliare = array('','','');
	$C_Centiare_Terreno_Immobiliare = array('','','');
	$Dominicale_Terreno_Immobiliare = array('','','');
	$Agrario_Terreno_Immobiliare = array('','','');
	$Deduzioni_Terreno_Immobiliare = array('','','');
	$Efficacia_Immobiliare = array('','','');
	$Efficacia_Registrazione_Immobiliare = array('','','');
	$Efficacia_Tipo_Numero_Nota_Immobiliare = array('','','');
	$Termine_Immobiliare = array('','','');
	$Termine_Registrazione_Immobiliare = array('','','');
	$Termine_Tipo_Numero_Nota_Immobiliare = array('','','');
	$Parte_Proprietario_Immobiliare = array('1','1','1');
	$Totale_Proprietario_Immobiliare = array('1','1','1');
	$Efficacia_Proprietario_Immobiliare = array('','','');
	$Efficacia_Registrazione_Proprietario_Immobiliare = array('','','');
	$Efficacia_Tipo_Numero_Nota_Proprietario_Immobiliare = array('','','');
	$Termine_Proprietario_Immobiliare = array('','','');
	$Termine_Registrazione_Proprietario_Immobiliare = array('','','');
	$Termine_Tipo_Numero_Nota_Proprietario_Immobiliare = array('','','');

	$options_una_tantum_cur = "";
	$options_a_giorni_cur = "";
	$options_a_km_cur = "";

	//var_dump($tipo_pignoramento);
	switch($tipo_pignoramento)
	{
		case "terzi":

			$layout .= "<script>$('.tr_terzi').show();</script>";
			$layout .= "<script>$('.tr_not_terzi').show();</script>";
			$layout .= "<script>$('#presso_terzi').val('".$tipo_terzi_generale."');</script>";
			switch($tipo_terzi_generale)
			{
				case "lavoro":

					$layout .= "<script>$('.tr_lavoro').show();</script>";
					$layout .= "<script>$('.td_banca').hide();</script>";
					$layout .= "<script>$('.td_terzi').show();</script>";
					$options_una_tantum_cur = $options_una_tantum_lavoro;
					$options_a_giorni_cur = $options_a_giorni_lavoro;
					$options_a_km_cur = $options_a_km_lavoro;

					break;

				case "banca":

					$layout .= "<script>$('.tr_banca').show();</script>";
					$layout .= "<script>$('.td_banca').show();</script>";
					$layout .= "<script>$('.td_terzi').hide();</script>";
					$options_una_tantum_cur = $options_una_tantum_banca;
					$options_a_giorni_cur = $options_a_giorni_banca;
					$options_a_km_cur = $options_a_km_banca;

					break;

				case "inps":

					$layout .= "<script>$('.tr_inps').show();</script>";
					$layout .= "<script>$('.td_banca').hide();</script>";
					$layout .= "<script>$('.td_terzi').show();</script>";
					$options_una_tantum_cur = $options_una_tantum_inps;
					$options_a_giorni_cur = $options_a_giorni_inps;
					$options_a_km_cur = $options_a_km_inps;

					break;

				case "altro":

					$layout .= "<script>$('.tr_altro').show();</script>";
					$layout .= "<script>$('.td_banca').hide();</script>";
					$layout .= "<script>$('.td_terzi').show();</script>";
					$options_una_tantum_cur = $options_una_tantum_altro;
					$options_a_giorni_cur = $options_a_giorni_altro;
					$options_a_km_cur = $options_a_km_altro;

					break;
			}

			$presso_terzi = isset($pignoramento["Presso_Terzi"])?$pignoramento["Presso_Terzi"]:null;
      $ID_Notifica_Veicolo = array(0 => 0);
      //echo "<h1>presso_terzi: ".count($presso_terzi)."</h1>";
            //var_dump($presso_terzi);
if($presso_terzi!=null)
{
			for($i=0;$i<count($presso_terzi);$i++)
			{
				$terzo_utente[$i] = $presso_terzi[$i]["Dati_Terzo"];
				$terzo_ID[$i] = $presso_terzi[$i]["Terzo_ID"];
				$layout.="<script>$('#pignorato_id_".$tipo_terzi_generale."_".$i."').val('".$terzo_ID[$i]."');</script>";

				$Tipo_Terzi[$i] = $presso_terzi[$i]["Tipo_Terzi"];

				if($Tipo_Terzi[$i]!="banca")
					$nome_cognome_terzo[$i] = "(".$terzo_utente[$i]["Comune_ID"].") ".$terzo_utente[$i]["Cognome"] . $terzo_utente[$i]["Ditta"] ." ". $terzo_utente[$i]["Nome"];
				else
					$nome_cognome_terzo[$i] = $terzo_utente[$i]["Denominazione"];

				$Fonte_Dati[$i] = $presso_terzi[$i]["Fonte_Dati"];
				$Note_Terzi[$i] = $presso_terzi[$i]["Note"];


				$Tipo_Contratto_Lavoro[$i] = $presso_terzi[$i]["Tipo_Contratto_Lavoro"];
				$layout.="<script>$('#tipo_contratto_".($i)."').val('".$Tipo_Contratto_Lavoro[$i]."');</script>";

				$Data_Costituzione_Ditta_Lavoro[$i] = $cls_date->Get_DateNewFormat($presso_terzi[$i]["Data_Costituzione_Ditta_Lavoro"],"DB");
				$Data_Ditta_Operativa_Lavoro[$i] = $cls_date->Get_DateNewFormat($presso_terzi[$i]["Data_Ditta_Operativa_Lavoro"],"DB");
				$Data_Dipendenze_Lavoro[$i] = $cls_date->Get_DateNewFormat($presso_terzi[$i]["Data_Dipendenze_Lavoro"],"DB");

				$Tipo_Titolo_Banca[$i] = $presso_terzi[$i]["Tipo_Titolo_Banca"];
				$layout.="<script>$('#tipo_titolo_".($i)."').val('".$Tipo_Titolo_Banca[$i]."');</script>";

				$Titolo_Banca[$i] = $presso_terzi[$i]["Titolo_Banca"];
				$Intestatario_Banca[$i] = $presso_terzi[$i]["Intestatario_Banca"];
				$Coointestatari_Banca[$i] = $presso_terzi[$i]["Coointestatari_Banca"];

				$Tipo_Pensione_Inps[$i] = $presso_terzi[$i]["Tipo_Pensione_Inps"];
				$layout.="<script>$('#tipo_libretto_".($i)."').val('".$Tipo_Pensione_Inps[$i]."');</script>";
				$Libretto_Inps[$i] = $presso_terzi[$i]["Libretto_Inps"];

				$Tipo_Titolo_Altro[$i] = $presso_terzi[$i]["Tipo_Titolo_Altro"];
				$layout.="<script>$('#tipo_titolo_credito_".($i)."').val('".$Tipo_Titolo_Altro[$i]."');</script>";

				$Titolo_Altro[$i] = $presso_terzi[$i]["Titolo_Altro"];
				$Tipo_Credito_Altro[$i] = $presso_terzi[$i]["Tipo_Credito_Altro"];

				$Data_Emissione_Altro[$i] = $cls_date->Get_DateNewFormat($presso_terzi[$i]["Data_Emissione_Altro"],"DB");
				$Data_Scadenza_Altro[$i] = $cls_date->Get_DateNewFormat($presso_terzi[$i]["Data_Scadenza_Altro"],"DB");

				//var_dump($tipo_terzi_generale);

				switch($tipo_terzi_generale)
				{
					case "lavoro":

          //echo "<h1>count: ".count($nome_cognome_terzo)." ".$nome_cognome_terzo[$i]."</h1>";
						$count_terzi = count($presso_terzi);
						$nome_cognome_lavoro[$i] = $nome_cognome_terzo[$i];
						$Fonte_Dati_lavoro[$i] = $Fonte_Dati[$i];
						$azienda_lavoro[$i] = $presso_terzi[$i]["Azienda"];
						$Note_Terzi_lavoro[$i] = $Note_Terzi[$i];

						break;

					case "banca":

						$count_terzi = count($presso_terzi);
						$nome_cognome_banca[$i] = $nome_cognome_terzo[$i];
						$Fonte_Dati_banca[$i] = $Fonte_Dati[$i];
						$Note_Terzi_banca[$i] = $Note_Terzi[$i];

						break;

					case "inps":

						$count_terzi = count($presso_terzi);
						$nome_cognome_inps[$i] = $nome_cognome_terzo[$i];
						$Fonte_Dati_inps[$i] = $Fonte_Dati[$i];
						$Note_Terzi_inps[$i] = $Note_Terzi[$i];

						break;

					case "altro":

						$count_terzi = count($presso_terzi);
						$nome_cognome_altro[$i] = $nome_cognome_terzo[$i];
						$Fonte_Dati_altro[$i] = $Fonte_Dati[$i];
						$Note_Terzi_altro[$i] = $Note_Terzi[$i];

						break;
				}


				//NOTIFICHE TERZO
				$notifiche_terzo[$i] = $presso_terzi[$i]["Notifiche_Terzo"];

				if(isset($presso_terzi[$i]["Notifica"]))
				{
					$Spese_Notifica[$i] = 0;
					for($y=0;$y<count($notifiche_terzo[$i]);$y++)
					{
						$ID_Notifica_Terzo[$i][$y] = $notifiche_terzo[$i][$y]["ID"];
						$Tipo_Riscontro_Terzo[$i][$y] = $notifiche_terzo[$i][$y]["Tipo_Riscontro"];
						$Data_Notifica[$i][$y] = $cls_date->Get_DateNewFormat($notifiche_terzo[$i][$y]["Data_Notifica"],"DB");

						$Stato_Notifica[$i][$y] = $notifiche_terzo[$i][$y]["Stato_Notifica"];
						$Motivo_Notifica[$i][$y] = $notifiche_terzo[$i][$y]["Motivo_Notifica"];
						$Modalita_Notifica[$i][$y] = $notifiche_terzo[$i][$y]["Modalita_Notifica"];
						$layout.="<script>$('#stato_not_terzo_".$i."_".$y."').val('".$Stato_Notifica[$i][$y]."');</script>";
						$layout.="<script>$('#motivo_not_terzo_".$i."_".$y."').val('".$Motivo_Notifica[$i][$y]."');</script>";
						$layout.="<script>$('#modalita_not_terzo_".$i."_".$y."').val('".$Modalita_Notifica[$i][$y]."');</script>";

						$Modalita_Stampa_Terzo[$i][$y] = $notifiche_terzo[$i][$y]["Modalita_Stampa"];
						$layout.="<script>$('#modalita_stampa_terzo_".$i."_".$y."').val('".$Modalita_Stampa_Terzo[$i][$y]."');</script>";
						if($Modalita_Stampa_Terzo[$i][$y]=="pec")
							$layout.="<script>$('.tr_validato_terzo_".$i."_".$y."').hide();</script>";

						$Note_Notifica[$i][$y] = $notifiche_terzo[$i][$y]["Note_Notifica"];
						$Ind_Validato[$i][$y] = $notifiche_terzo[$i][$y]["Indirizzo_Validato"];
						if($Ind_Validato[$i][$y]=="si")
							$layout.="<script>$('#ind_validato_terzo_".$i."_".$y."').prop('checked',true);</script>";
						$Spese_Notifica[$i] += $notifiche_terzo[$i][$y]["Spese_Notifica"];

					}

					if($Spese_Notifica[$i]!=null)	$Spese_Notifica[$i] = number_format($Spese_Notifica[$i],2,",","");

				}
				else
				{
                    $Data_Notifica[$i] = array();
					$Data_Notifica[$i][0] = "";
					$Spese_Notifica[$i] = "";
				}



				$layout.="<script>scelta_default();</script>";
			}
    }


			break;

		case "mobiliare":

			$options_una_tantum_cur = $options_una_tantum_mobiliare;
			$options_a_giorni_cur = $options_a_giorni_mobiliare;
			$options_a_km_cur = $options_a_km_mobiliare;

			break;

		case "beni":

			$options_una_tantum_cur = $options_una_tantum_beni;
			$options_a_giorni_cur = $options_a_giorni_beni;
			$options_a_km_cur = $options_a_km_beni;

			break;

		case "immobiliare":

			$options_una_tantum_cur = $options_una_tantum_immobiliare;
			$options_a_giorni_cur = $options_a_giorni_immobiliare;
			$options_a_km_cur = $options_a_km_immobiliare;

			for($i=0;$i<count($pignoramento["Immobiliare"]);$i++)
			{

				$pigno_immobiliare = $pignoramento["Immobiliare"][$i];

				$Tipo_Immobiliare[$i] = $pigno_immobiliare["Tipo_Immobiliare"];
				$layout.="<script>$('#tipo_immobiliare_".$i."').val('".$Tipo_Immobiliare[$i]."');scelta_immobile('".$i."');</script>";
				$Situazione_Immobiliare[$i] = $pigno_immobiliare["Situazione"];
				$Foglio_Immobiliare[$i] = $pigno_immobiliare["Foglio"];
				$Particella_Immobiliare[$i] = $pigno_immobiliare["Particella"];
				$Subalterno_Immobiliare[$i] = $pigno_immobiliare["Subalterno"];
				if($Subalterno_Immobiliare[$i]==0)	$Subalterno_Immobiliare[$i]="";

				if($Tipo_Immobiliare[$i]=="fabbricato")
					$Classe_Immobiliare_Fabbricato[$i] = $pigno_immobiliare["Classe"];
				else
					$Classe_Immobiliare_Terreno[$i] = $pigno_immobiliare["Classe"];

				$Annotazioni_Immobiliare[$i] = $pigno_immobiliare["Annotazioni"];

				$Efficacia_Immobiliare[$i] = $pigno_immobiliare["Efficacia"];
				$Efficacia_Registrazione_Immobiliare[$i] = $pigno_immobiliare["Efficacia_Registrazione"];
				$Efficacia_Tipo_Numero_Nota_Immobiliare[$i] = $pigno_immobiliare["Efficacia_Tipo_Numero_Nota"];
				$Termine_Immobiliare[$i] = $pigno_immobiliare["Termine"];
				$Termine_Registrazione_Immobiliare[$i] = $pigno_immobiliare["Termine_Registrazione"];
				$Termine_Tipo_Numero_Nota_Immobiliare[$i] = $pigno_immobiliare["Termine_Tipo_Numero_Nota"];

				$Sezione_Fabbricato_Immobiliare[$i] = $pigno_immobiliare["Sezione_Fabbricato"];
				$Zona_Censuaria_Fabbricato_Immobiliare[$i] = $pigno_immobiliare["Zona_Censuaria_Fabbricato"];
				$Categoria_Fabbricato_Immobiliare[$i] = $pigno_immobiliare["Categoria_Fabbricato"];
				$layout.="<script>$('#categoria_fabbricato_".$i."').val('".$Categoria_Fabbricato_Immobiliare[$i]."');</script>";
				$Consistenza_Fabbricato_Immobiliare[$i] = number_format($pigno_immobiliare["Consistenza_Fabbricato"],2,",","");
				if($Consistenza_Fabbricato_Immobiliare[$i]=="0,00")	$Consistenza_Fabbricato_Immobiliare[$i]="";
				$Superficie_Fabbricato_Immobiliare[$i] = number_format($pigno_immobiliare["Superficie_Fabbricato"],2,",","");
				if($Superficie_Fabbricato_Immobiliare[$i]=="0,00")	$Superficie_Fabbricato_Immobiliare[$i]="";
				$Rendita_Fabbricato_Immobiliare[$i] = number_format($pigno_immobiliare["Rendita_Fabbricato"],2,",","");
				if($Rendita_Fabbricato_Immobiliare[$i]=="0,00")	$Rendita_Fabbricato_Immobiliare[$i]="";
				$Indirizzo_Fabbricato_Immobiliare[$i] = $pigno_immobiliare["Indirizzo_Fabbricato"];
				$Protocollo_Notifica_Fabbricato_Immobiliare[$i] = $pigno_immobiliare["Protocollo_Notifica_Fabbricato"];

				$Porzione_Terreno_Immobiliare[$i] = $pigno_immobiliare["Porzione_Terreno"];
				if($Porzione_Terreno_Immobiliare[$i]==0)	$Porzione_Terreno_Immobiliare[$i]="";
				$Qualita_Terreno_Immobiliare[$i] = $pigno_immobiliare["Qualita_Terreno"];
				if($Qualita_Terreno_Immobiliare[$i]==0)	$Qualita_Terreno_Immobiliare[$i]="";
				$Descrizione_Qualita_Terreno_Immobiliare[$i] = $pigno_immobiliare["Descrizione_Qualita_Terreno"];
				$HA_Ettari_Terreno_Immobiliare[$i] = $pigno_immobiliare["HA_Ettari_Terreno"];
				if($HA_Ettari_Terreno_Immobiliare[$i]==0)	$HA_Ettari_Terreno_Immobiliare[$i]="";
				$A_Are_Terreno_Immobiliare[$i] = $pigno_immobiliare["A_Are_Terreno"];
				if($A_Are_Terreno_Immobiliare[$i]==0)	$A_Are_Terreno_Immobiliare[$i]="";
				$C_Centiare_Terreno_Immobiliare[$i] = $pigno_immobiliare["C_Centiare_Terreno"];
				if($C_Centiare_Terreno_Immobiliare[$i]==0)	$C_Centiare_Terreno_Immobiliare[$i]="";

				$Dominicale_Terreno_Immobiliare[$i] = number_format($pigno_immobiliare["Dominicale_Terreno"],2,",","");
				if($Dominicale_Terreno_Immobiliare[$i]=="0,00")	$Dominicale_Terreno_Immobiliare[$i]="";
				$Agrario_Terreno_Immobiliare[$i] = number_format($pigno_immobiliare["Agrario_Terreno"],2,",","");
				if($Agrario_Terreno_Immobiliare[$i]=="0,00")	$Agrario_Terreno_Immobiliare[$i]="";
				$Deduzioni_Terreno_Immobiliare[$i] = number_format($pigno_immobiliare["Deduzioni_Terreno"],2,",","");
				if($Deduzioni_Terreno_Immobiliare[$i]=="0,00")	$Deduzioni_Terreno_Immobiliare[$i]="";

				$Parte_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Parte_Proprietario"];
				$Totale_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Totale_Proprietario"];
				$Efficacia_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Efficacia_Proprietario"];
				$Efficacia_Registrazione_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Efficacia_Registrazione_Proprietario"];
				$Efficacia_Tipo_Numero_Nota_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Efficacia_Tipo_Numero_Nota_Proprietario"];
				$Termine_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Termine_Proprietario"];
				$Termine_Registrazione_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Termine_Registrazione_Proprietario"];
				$Termine_Tipo_Numero_Nota_Proprietario_Immobiliare[$i] = $pigno_immobiliare["Termine_Tipo_Numero_Nota_Proprietario"];

			}

			break;

		case "fermo":

			$layout .= "<script>$('.tr_fermo').show();</script>";
			$options_una_tantum_cur = $options_una_tantum_fermo;
			$options_a_giorni_cur = $options_a_giorni_fermo;
			$options_a_km_cur = $options_a_km_fermo;

			for($i=0;$i<count($pignoramento["Fermo"]);$i++)
			{

				$marca_fermo[$i] = $pignoramento["Fermo"][$i]["Marca_Veicolo"];
				$modello_fermo[$i] = $pignoramento["Fermo"][$i]["Modello_Veicolo"];
				$targa_fermo[$i] = $pignoramento["Fermo"][$i]["Targa_Veicolo"];
				$data_visura_fermo[$i] = $cls_date->Get_DateNewFormat($pignoramento["Fermo"][$i]["Data_Visura"],"DB");
				$portata_fermo[$i] = number_format($pignoramento["Fermo"][$i]["Portata_Veicolo"],2,",","");
				if($portata_fermo[$i] == "0,00")	$portata_fermo[$i] = "";
				$valore_fermo[$i] = number_format($pignoramento["Fermo"][$i]["Valore_Veicolo"],2,",","");
				if($valore_fermo[$i] == "0,00")	$valore_fermo[$i] = "";
				$anno_immatricolazione_fermo[$i] = $pignoramento["Fermo"][$i]["Anno_Immatricolazione"];
				if($anno_immatricolazione_fermo[$i] == "0")	$anno_immatricolazione_fermo[$i] = "";
				$layout.= "<script>$('#tipo_fermo_".$i."').val('".$pignoramento["Fermo"][$i]["Tipo_Veicolo"]."');</script>";
				$fonte_dati_fermo[$i] = $pignoramento["Fermo"][$i]["Fonte_Dati"];
				$layout.= "<script>$('#fonte_dati_fermo_".($i)."').val('".$fonte_dati_fermo[$i]."');</script>";

			}

			break;

		case "preav_fermo":

				$layout .= "<script>$('.tr_preav_fermo').show();</script>";
				$options_una_tantum_cur = "";
				$options_a_giorni_cur = "";
				$options_a_km_cur = "";

				for($i=0;$i<count($pignoramento["Preavviso_Fermo"]);$i++)
				{

					$marca_preav_fermo[$i] = $pignoramento["Preavviso_Fermo"][$i]["Marca_Veicolo"];
					$modello_preav_fermo[$i] = $pignoramento["Preavviso_Fermo"][$i]["Modello_Veicolo"];
					$targa_preav_fermo[$i] = $pignoramento["Preavviso_Fermo"][$i]["Targa_Veicolo"];
					$data_visura_preav_fermo[$i] = $cls_date->Get_DateNewFormat($pignoramento["Preavviso_Fermo"][$i]["Data_Visura"],"DB");
					$portata_preav_fermo[$i] = number_format($pignoramento["Preavviso_Fermo"][$i]["Portata_Veicolo"],2,",","");
					if($portata_preav_fermo[$i] == "0,00")	$portata_preav_fermo[$i] = "";
					$valore_preav_fermo[$i] = number_format($pignoramento["Preavviso_Fermo"][$i]["Valore_Veicolo"],2,",","");
					if($valore_preav_fermo[$i] == "0,00")	$valore_preav_fermo[$i] = "";
					$anno_immatricolazione_preav_fermo[$i] = $pignoramento["Preavviso_Fermo"][$i]["Anno_Immatricolazione"];
					if($anno_immatricolazione_preav_fermo[$i] == "0")	$anno_immatricolazione_preav_fermo[$i] = "";
					$layout.= "<script>$('#tipo_preav_fermo_".$i."').val('".$pignoramento["Preavviso_Fermo"][$i]["Tipo_Veicolo"]."');</script>";
					$fonte_dati_preav_fermo[$i] = $pignoramento["Preavviso_Fermo"][$i]["Fonte_Dati"];
					$layout.= "<script>$('#fonte_dati_preav_fermo_".($i)."').val('".$fonte_dati_preav_fermo[$i]."');</script>";
				}

			break;

		case "veicolo":

			$layout .= "<script>$('.tr_veicolo').show();</script>";
			$options_una_tantum_cur = $options_una_tantum_veicolo;
			$options_a_giorni_cur = $options_a_giorni_veicolo;
			$options_a_km_cur = $options_a_km_veicolo;

			$count = isset($pignoramento["Veicolo"])?count($pignoramento["Veicolo"]):0;

			//var_dump($pignoramento["Veicolo"]);
			for($i=0;$i<$count;$i++)
			{

				$layout.= "<script>$('#tipo_veicolo_".($i)."').val('".$pignoramento["Veicolo"][$i]["Tipo_Veicolo"]."');</script>";

				$marca_veicolo[$i] = $pignoramento["Veicolo"][$i]["Marca_Veicolo"];
				$modello_veicolo[$i] = $pignoramento["Veicolo"][$i]["Modello_Veicolo"];
				$targa_veicolo[$i] = $pignoramento["Veicolo"][$i]["Targa_Veicolo"];
        $telaio_veicolo[$i] = $pignoramento["Veicolo"][$i]["Telaio_Veicolo"];
                $data_fermo_veicolo[$i] = $cls_date->Get_DateNewFormat($pignoramento["Veicolo"][$i]["Data_Iscrizione_Fermo"]);
				$data_visura_veicolo[$i] = $cls_date->Get_DateNewFormat($pignoramento["Veicolo"][$i]["Data_Visura"]);
				$portata_veicolo[$i] = number_format($pignoramento["Veicolo"][$i]["Portata_Veicolo"],2,",","");
				if($portata_veicolo[$i] == "0,00")	$portata_veicolo[$i] = "";
				$valore_veicolo[$i] = number_format($pignoramento["Veicolo"][$i]["Valore_Veicolo"],2,",","");
				if($valore_veicolo[$i] == "0,00")	$valore_veicolo[$i] = "";
				$anno_immatricolazione_veicolo[$i] = $pignoramento["Veicolo"][$i]["Anno_Immatricolazione"];
				if($anno_immatricolazione_veicolo[$i] == "0")	$anno_immatricolazione_veicolo[$i] = "";
				$fonte_dati_veicolo[$i] = $pignoramento["Veicolo"][$i]["Fonte_Dati"];
				$layout.= "<script>$('#fonte_dati_veicolo_".($i)."').val('".$fonte_dati_veicolo[$i]."');</script>";

			}


			//NOTIFICHE ISTITUTO
			if(isset($pignoramento["Notifica_Istituto"]))
			{
				$notifiche_veicolo = $pignoramento["Notifica_Istituto"];
                //var_dump($pignoramento["Notifica_Istituto"]);
				$Spese_Notifica_Veicolo = 0;

				for($y=0;$y<count($notifiche_veicolo);$y++)
				{
					$avvenuta_consegna = "";
					$avvenuta_accettazione = "";
					if($notifiche_veicolo[$y]["Modalita_Stampa"]=="pec")
					{
						if($notifiche_veicolo[$y]["Email_Object"]!=null)
						{
							$avvenuta_consegna = $notifiche_veicolo[$y]["Email_Object"]["Ricevuta_Consegna"];
							$avvenuta_accettazione = $notifiche_veicolo[$y]["Email_Object"]["Ricevuta_Accettazione"];

							if($avvenuta_consegna=="ok" || ($avvenuta_consegna =="no" && $avvenuta_accettazione=="ok"))
								$testo_PEC = "Inviata il ".$cls_date->Get_DateNewFormat($notifiche_veicolo[$y]["Email_Object"]["Data_Invio"],"DB")." - CONSEGNATA";
							else
								$testo_PEC = "Inviata il ".$cls_date->Get_DateNewFormat($notifiche_veicolo[$y]["Email_Object"]["Data_Invio"],"DB")." - DA VERIFICARE";
						}
						else
							$testo_PEC = "Da inviare";
					}

					$ID_Notifica_Veicolo[$y] = isset($notifiche_veicolo[$y]["ID"])?$notifiche_veicolo[$y]["ID"]:0;
          //var_dump($notifiche_veicolo[$y]["Tipo_Riscontro"]);
                    //var_dump($cls_date->Get_DateNewFormat($notifiche_veicolo[$y]["Data_Notifica"],"DB"));
					$Tipo_Riscontro_Veicolo[$y] = $notifiche_veicolo[$y]["Tipo_Riscontro"]==""?null:$notifiche_veicolo[$y]["Tipo_Riscontro"];
					$Data_Notifica_Veicolo[$y] = $cls_date->Get_DateNewFormat($notifiche_veicolo[$y]["Data_Notifica"],"DB");

					//echo "<br>".$Data_Notifica_Veicolo[$y]."<br>";

					$Stato_Notifica_Veicolo[$y] = $notifiche_veicolo[$y]["Stato_Notifica"];
					$layout .= "<script>$('#stato_not_veicolo_".$y."').val('".$Stato_Notifica_Veicolo[$y]."');cambia_title('stato_not_veicolo');</script>";

					$motivo_notifica_veicolo[$y] = $notifiche_veicolo[$y]["Motivo_Notifica"];
					$layout .= "<script>$('#motivo_not_veicolo_".$y."').val('".$motivo_notifica_veicolo[$y]."');cambia_title('motivo_not_veicolo');</script>";

					$modalita_notifica_veicolo[$y] = $notifiche_veicolo[$y]["Modalita_Notifica"];
					$layout .= "<script>$('#modalita_not_veicolo_".$y."').val('".$modalita_notifica_veicolo[$y]."');cambia_title('modalita_not_veicolo');</script>";

					$modalita_stampa_veicolo[$y] = $notifiche_veicolo[$y]["Modalita_Stampa"];
					$layout .= "<script>$('#modalita_stampa_veicolo_".$y."').val('".$modalita_stampa_veicolo[$y]."');cambia_title('modalita_stampa_veicolo');</script>";
					if($modalita_stampa_veicolo[$y]=="pec")
						$layout.="<script>$('.tr_validato_veicolo_".$y."').hide();</script>";

					$Note_Notifica_Veicolo[$y] = $notifiche_veicolo[$y]["Note_Notifica"];
					$Ind_Validato_Veicolo[$y] = $notifiche_veicolo[$y]["Indirizzo_Validato"];
					if($Ind_Validato_Veicolo[$y]=="si")
						$layout.="<script>$('#ind_validato_veicolo_".$y."').prop('checked',true);</script>";

					//SPESE ISTITUTO
					$Spese_Notifica_Veicolo+= $notifiche_veicolo[$y]["Spese_Notifica"];
				}
                //var_dump($Data_Notifica_Veicolo);
				if($Spese_Notifica_Veicolo!=null)	$Spese_Notifica_Veicolo = number_format($Spese_Notifica_Veicolo,2,",","");


			}


			$layout.="<script>update_notifica_veicolo();scelta_default();</script>";

			break;

		default:

			break;
	}

	//$parametri_notifica = new parametri_notifica(null);
	$resultArrayParam = $cls_coazione->array_notifica();

	$options_stati = $cls_coazione->options_select_array_1($resultArrayParam["Stati"]);
	$options_motivi = $cls_coazione->options_select_array_1($resultArrayParam["Motivi"]);
	$options_a_mani = $cls_coazione->options_select_array_1($resultArrayParam["Mode_A_Mani"], "Descrizione" , "Articolo");
	$options_per_posta = $cls_coazione->options_select_array_1($resultArrayParam["Mode_Per_Posta"], "Descrizione" , "Articolo");
	$options_eccezionali = $cls_coazione->options_select_array_1($resultArrayParam["Mode_Eccezionali"], "Descrizione" , "Articolo");

	$options_blocco = $cls_coazione->options_select_array_1($resultArrayParam["BloccoCoattiva"]);

if($cls_help->getVar("partita")!= null){
  //$query = "SELECT Utente_ID FROM partita_tributi WHERE ID = ".$cls_help->getVar("partita");
  //$ID = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    $data_check = new DateTime(date("Y-m-d"));
    $data_check->modify("+30 days");

  $query = "SELECT ID, Targa, Fabbrica, Tipo, Pignoramento_ID FROM veicoli WHERE Utente_ID = ".$p." AND CC_Comune = '".$c."' AND (TRIM(StatoVeicolo) = '' OR StatoVeicolo is null OR TRIM(StatoVeicolo) = 'Targa Attuale') AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE Utente_ID = ".$p.") AND Data_Visura <= '".$data_check->format("Y-m-d")."'";
  //$query = "SELECT ID, Targa, Fabbrica, Tipo, Pignoramento_ID FROM veicoli WHERE Utente_ID = ".$p." AND CC_Comune = '".$c."' AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE Utente_ID = ".$p.") AND Data_Visura < '".$data_check->format("Y-m-d")."'";
  //echo $query;

  $elencoVeicoli = $cls_db->getResults($cls_db->ExecuteQuery($query));

  $optionVeicoli = "<option value=''></option>";
  for($i=0; $i<count($elencoVeicoli); $i++)
  {
    $disColor = "";
    if($elencoVeicoli[$i]["Pignoramento_ID"]!=null)
      $disColor = " disabled style='color: red;' ";

    $optionVeicoli .= "<option ".$disColor." value='".$elencoVeicoli[$i]["ID"]."'>".$elencoVeicoli[$i]["Targa"]." ".$elencoVeicoli[$i]["Fabbrica"]." ".$elencoVeicoli[$i]["Tipo"]."</option>";
  }

    $optTipoPigno="";
  if(count($elencoVeicoli) == 0){
      $optTipoPigno = '<option></option>
     				<option value="terzi"		>Presso terzi</option>
            <option value="veicolo" style="color: red;" disabled title="visura non effettuata o effettuata oltre 30 giorni fa"      >Beni mobili registrati</option>
     				<option value="immobiliare"	>Immobiliare</option>
     				<option value="mobiliare"	>Mobiliare</option>
     				<option value="preav_fermo" style="color: red;" disabled title="visura non effettuata o effettuata oltre 30 giorni fa"  >Preavviso fermo amministrativo</option>
     				<option value="fermo" style="color: red;" disabled title="visura non effettuata o effettuata oltre 30 giorni fa"        >Fermo amministrativo</option>';
  }
  else{
      $optTipoPigno = '<option></option>
     				<option value="terzi"		    >Presso terzi</option>
     				<option value="veicolo"         >Beni mobili registrati</option>
     				<option value="immobiliare"	    >Immobiliare</option>
     				<option value="mobiliare"	    >Mobiliare</option>
     				<option value="preav_fermo"	    >Preavviso fermo amministrativo</option>
     				<option value="fermo"           >Fermo amministrativo</option>';
  }

  $tipo_pignoramento_get = $cls_help->getVar("tipo_pignoramento_get");
  $id_veicolo_get = $cls_help->getVar("ID_Veicolo_get");

  if($tipo_pignoramento_get==null)
    $tipo_pignoramento_get="";
  if($id_veicolo_get==null)
    $id_veicolo_get="";
}


?>


<!-- ********** GESTIONE LINK MENU ********** -->
<script>
var flag_blocco = "<?php echo $blocco_partita; ?>";

<?php if(!$noSaveDelete) {?>
//F3
switchMenuImg("F3");
F3_button = function(){
  if(flag_blocco=="si")
  {
    ritorno = confirm("ATTENZIONE! Coazione bloccata!\nProcedere con il salvataggio?");
    if(!ritorno)
      return false;
  }

  if($('#rimborso_totale').val()=="" || $('#rimborso_totale').val()=="0,00")
    alert('Non sono state inserite spese accessorie!');

  tipo_terzi = $('#presso_terzi').val();
  if($('#tipo_pignoramento').val() == "terzi")
  {
    if( tipo_terzi == "lavoro" )
    {
      if( $('#pignorato_id_lavoro_1').val() == "0" && $('#pignorato_id_lavoro_2').val() == "0" && $('#pignorato_id_lavoro_0').val() == "0" )
        if( $('#azienda_lavoro_1').val() == "" && $('#azienda_lavoro_2').val() == "" && $('#azienda_lavoro_0').val() == "" )
        {
          alert("Dati dei pignorati incompleti. Non e' possibile completare il salvataggio.");
          return false;
        }
    }
    else
    {
      if( $('#pignorato_id_'+tipo_terzi+'_1').val() == "0" && $('#pignorato_id_'+tipo_terzi+'_2').val() == "0" && $('#pignorato_id_'+tipo_terzi+'_0').val() == "0" )
      {
        alert("Dati dei pignorati incompleti. Non e' possibile completare il salvataggio.");
        return false;
      }
    }
  }

    if($("#PrinterId").val()!=""){
        control = submit_buttons('<?php echo $control_submit; ?>');
        if(control && validateForm())
        {
          //alert($("#submitButton").val());
          $("#submitButton").trigger("click");
          //$("#form_pignoramento").submit();
        }

    }
    else
        alert("Inserire lo stampatore!");
}

//F4
switchMenuImg("F4");
F4_button = function(){
  control = submit_buttons('Delete');
  if(control)
    $("#submitButton").trigger("click");
}
<?php } ?>
//F5
switchMenuImg("F5");
F5_button = function(){
  location.href="coazione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
/*switchMenuImg("F6");
F6_button = function(){
  if( modifica == 0 )
	{
		crea_pignoramento();
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}*/

//F7
//switchMenuImg("F7");
F7_button = function(){
  if( modifica == 0 )
	{
		value = "<?php echo $prev; ?>";
		location.href="coazione.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F8
//switchMenuImg("F8");
F8_button = function(){
  if( modifica == 0 )
	{
		value = "<?php echo $next; ?>";
		location.href="coazione.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//PAG GIU
//switchMenuImg("pagedown");
pagedown_button = function(){
  if( modifica == 0 )
	{
		location.href="pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
//switchMenuImg("pageup");
pageup_button = function(){
  if( modifica == 0 )
	{
		location.href="ricorso.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F9
function ricerca_F9()
{
	if( modifica == 0 )
	{
		RicercheDaId('utente',0);
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function ruolo (value)
{
	location.href="gestione_ruolo.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function crea_pignoramento()
{
	top.location.href = "pignoramento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>

<!-- ********** CALENDARIO ********** -->
<script>
$( function() {

	 $( ".picker" ).datepicker();

	 } );

</script>

<!-- ********** SPESE ACCESSORIE ********** -->
<script>
var una_tantum_lavoro = "<?php echo $options_una_tantum_lavoro; ?>";
var a_giorni_lavoro = "<?php echo $options_a_giorni_lavoro; ?>";
var a_km_lavoro = "<?php echo $options_a_km_lavoro; ?>";

var una_tantum_banca = "<?php echo $options_una_tantum_banca; ?>";
var a_giorni_banca = "<?php echo $options_a_giorni_banca; ?>";
var a_km_banca = "<?php echo $options_a_km_banca; ?>";

var una_tantum_inps = "<?php echo $options_una_tantum_inps; ?>";
var a_giorni_inps = "<?php echo $options_a_giorni_inps; ?>";
var a_km_inps = "<?php echo $options_a_km_inps; ?>";

var una_tantum_altro = "<?php echo $options_una_tantum_altro; ?>";
var a_giorni_altro = "<?php echo $options_a_giorni_altro; ?>";
var a_km_altro = "<?php echo $options_a_km_altro; ?>";

var una_tantum_mobiliare = "<?php echo $options_una_tantum_mobiliare; ?>";
var a_giorni_mobiliare = "<?php echo $options_a_giorni_mobiliare; ?>";
var a_km_mobiliare = "<?php echo $options_a_km_mobiliare; ?>";

var una_tantum_beni = "<?php echo $options_una_tantum_beni; ?>";
var a_giorni_beni = "<?php echo $options_a_giorni_beni; ?>";
var a_km_beni = "<?php echo $options_a_km_beni; ?>";

var una_tantum_immobiliare = "<?php echo $options_una_tantum_immobiliare; ?>";
var a_giorni_immobiliare = "<?php echo $options_a_giorni_immobiliare; ?>";
var a_km_immobiliare = "<?php echo $options_a_km_immobiliare; ?>";

var una_tantum_fermo = "<?php echo $options_una_tantum_fermo; ?>";
var a_giorni_fermo = "<?php echo $options_a_giorni_fermo; ?>";
var a_km_fermo = "<?php echo $options_a_km_fermo; ?>";

var una_tantum_preav_fermo = "";
var a_giorni_preav_fermo = "";
var a_km_preav_fermo = "";

var una_tantum_veicolo = "<?php echo $options_una_tantum_veicolo; ?>";
var a_giorni_veicolo = "<?php echo $options_a_giorni_veicolo; ?>";
var a_km_veicolo = "<?php echo $options_a_km_veicolo; ?>";

var Tipo = new Array();
var Descrizione = new Array();
var Importo = new Array();
var Note = new Array();
var Deposito_Portata = new Array();
var Importo_Fisso = new Array();
var Km_Giorni_Importo_Fisso = new Array();
var Coefficiente = new Array();

var sp_ac_pigno_immobiliare = "<?php echo $sp_ac_pigno_immobiliare; ?>";
var sp_ac_stima_beni = "<?php echo $sp_ac_stima_beni; ?>";
var sp_ac_spese_ispezione = "<?php echo $sp_ac_spese_ispezione; ?>";
var sp_ac_progetto_attribuzione = "<?php echo $sp_ac_progetto_attribuzione; ?>";
var sp_ac_richiesta_copia = "<?php echo $sp_ac_richiesta_copia; ?>";
var sp_ac_iscrizione_fermo = "<?php echo $sp_ac_iscrizione_fermo; ?>";
var sp_ac_revoca_fermo = "<?php echo $sp_ac_revoca_fermo; ?>";
var sp_ac_pigno_presso_terzi = "<?php echo $sp_ac_pigno_presso_terzi; ?>";

<?php
for($y=0; $y<count($tariffe_una_tantum); $y++)
{
?>

	Tipo[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Tipo']; ?>";
	Descrizione[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Descrizione']; ?>";
	Importo[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Importo']; ?>";
	Note[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Note']; ?>";
	Deposito_Portata[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Deposito_Portata']; ?>";

	Importo_Fisso[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Importo_Fisso']; ?>";
	Km_Giorni_Importo_Fisso[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Km_Giorni_Importo_Fisso']; ?>";

	if(Importo_Fisso[<?php echo $tariffe_una_tantum[$y]['ID']; ?>]=="") 	Importo_Fisso[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "0";
	else	Importo_Fisso[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Importo_Fisso']; ?>";

	if(Km_Giorni_Importo_Fisso[<?php echo $tariffe_una_tantum[$y]['ID']; ?>]=="") Km_Giorni_Importo_Fisso[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "0";


	Coefficiente[<?php echo $tariffe_una_tantum[$y]['ID']; ?>] = "<?php echo $tariffe_una_tantum[$y]['Coefficiente']; ?>";

<?php
}
?>

<?php
for($y=0; $y<count($tariffe_a_giorni); $y++)
{
?>

	Tipo[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Tipo']; ?>";
	Descrizione[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Descrizione']; ?>";
	Importo[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Importo']; ?>";
	Note[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Note']; ?>";
	Deposito_Portata[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Deposito_Portata']; ?>";

	Importo_Fisso[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Importo_Fisso']; ?>";
	Km_Giorni_Importo_Fisso[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Km_Giorni_Importo_Fisso']; ?>";

	if(Importo_Fisso[<?php echo $tariffe_a_giorni[$y]['ID']; ?>]=="") Importo_Fisso[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "0";
	else	Importo_Fisso[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Importo_Fisso']; ?>";

	if(Km_Giorni_Importo_Fisso[<?php echo $tariffe_a_giorni[$y]['ID']; ?>]=="") Km_Giorni_Importo_Fisso[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "0";

	Coefficiente[<?php echo $tariffe_a_giorni[$y]['ID']; ?>] = "<?php echo $tariffe_a_giorni[$y]['Coefficiente']; ?>";

<?php
}
?>

<?php
for($y=0; $y<count($tariffe_a_km); $y++)
{
?>

	Tipo[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Tipo']; ?>";
	Descrizione[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Descrizione']; ?>";
	Importo[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Importo']; ?>";
	Note[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Note']; ?>";
	Deposito_Portata[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Deposito_Portata']; ?>";

	Importo_Fisso[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Importo_Fisso']; ?>";
	Km_Giorni_Importo_Fisso[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Km_Giorni_Importo_Fisso']; ?>";
	if(Importo_Fisso[<?php echo $tariffe_a_km[$y]['ID']; ?>]=="") Importo_Fisso[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "0";
	else	Importo_Fisso[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Importo_Fisso']; ?>";

	if(Km_Giorni_Importo_Fisso[<?php echo $tariffe_a_km[$y]['ID']; ?>] == "") Km_Giorni_Importo_Fisso[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "0";

	Coefficiente[<?php echo $tariffe_a_km[$y]['ID']; ?>] = "<?php echo $tariffe_a_km[$y]['Coefficiente']; ?>";

<?php
}
?>

function set_options()
{

	$('.una_tantum_class').empty();
	$('.a_giorni_class').empty();
	$('.a_km_class').empty();

	scelta_spesa(1,0);
	scelta_spesa(2,0);
	scelta_spesa(3,0);
	scelta_spesa(4,0);
	scelta_spesa(5,0);
	scelta_spesa(6,0);
	scelta_spesa(7,0);
	scelta_spesa(8,0);
	scelta_spesa(9,0);
	scelta_spesa(10,0);

	pignoramento = $('#tipo_pignoramento').val();

	switch(pignoramento)
	{
		case "terzi":
			scelta = $('#presso_terzi').val();

			switch(scelta)
			{
				case "lavoro":
					$('.una_tantum_class').append(una_tantum_lavoro);
					$('.a_giorni_class').append(a_giorni_lavoro);
					$('.a_km_class').append(a_km_lavoro);

					break;
				case "banca":
					$('.una_tantum_class').append(una_tantum_banca);
					$('.a_giorni_class').append(a_giorni_banca);
					$('.a_km_class').append(a_km_banca);
					break;
				case "inps":
					$('.una_tantum_class').append(una_tantum_inps);
					$('.a_giorni_class').append(a_giorni_inps);
					$('.a_km_class').append(a_km_inps);
					break;
				case "altro":
					$('.una_tantum_class').append(una_tantum_altro);
					$('.a_giorni_class').append(a_giorni_altro);
					$('.a_km_class').append(a_km_altro);
					break;
			}

			break;

		case "mobiliare":
			$('.una_tantum_class').append(una_tantum_mobiliare);
			$('.a_giorni_class').append(a_giorni_mobiliare);
			$('.a_km_class').append(a_km_mobiliare);
			break;

		case "beni":
			$('.una_tantum_class').append(una_tantum_beni);
			$('.a_giorni_class').append(a_giorni_beni);
			$('.a_km_class').append(a_km_beni);
			break;

		case "immobiliare":
			$('.una_tantum_class').append(una_tantum_immobiliare);
			$('.a_giorni_class').append(a_giorni_immobiliare);
			$('.a_km_class').append(a_km_immobiliare);
			break;

		case "fermo":
			$('.una_tantum_class').append(una_tantum_fermo);
			$('.a_giorni_class').append(a_giorni_fermo);
			$('.a_km_class').append(a_km_fermo);
			break;

		case "preav_fermo":
			$('.una_tantum_class').append(una_tantum_preav_fermo);
			$('.a_giorni_class').append(a_giorni_preav_fermo);
			$('.a_km_class').append(a_km_preav_fermo);
			break;

		case "veicolo":
			$('.una_tantum_class').append(una_tantum_veicolo);
			$('.a_giorni_class').append(a_giorni_veicolo);
			$('.a_km_class').append(a_km_veicolo);

			break;

	}

	scelta_default();

}

function hide_spese()
{
	for(var conta_spesa=1;conta_spesa<11;conta_spesa++)
	{
		$('.diverso_una_tantum_'+conta_spesa).hide();
		$('.una_tantum_'+conta_spesa).hide();
	}
}

function lista_mail()
{

	//strDim = Dim_Alert(850, 600);
	var stringa = "<?= WEB_ROOT;?>/search/coattiva/info_email.php?c=<?php echo $c; ?>&partita=<?php echo $partita_ID; ?>";
  openWindowSearch(stringa,{width:850, height:600, left:(window.screen.width/2)-425, top:(window.screen.height/2)-300});
	//valorediritorno = window.showModalDialog(stringa,"", strDim);
}

function scelta_spesa(value, ID)
{
	switch(value)
	{
		case 1:
			ID_old = "<?php echo $Spese_Accessorie[1]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[1]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[1]['tipo_totale']; ?>";
		break;
		case 2:
			ID_old = "<?php echo $Spese_Accessorie[2]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[2]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[2]['tipo_totale']; ?>";
		break;
		case 3:
			ID_old = "<?php echo $Spese_Accessorie[3]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[3]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[3]['tipo_totale']; ?>";

		break;
		case 4:
			ID_old = "<?php echo $Spese_Accessorie[4]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[4]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[4]['tipo_totale']; ?>";
		break;
		case 5:
			ID_old = "<?php echo $Spese_Accessorie[5]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[5]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[5]['tipo_totale']; ?>";
		break;
		case 6:
			ID_old = "<?php echo $Spese_Accessorie[6]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[6]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[6]['tipo_totale']; ?>";
		break;
		case 7:
			ID_old = "<?php echo $Spese_Accessorie[7]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[7]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[7]['tipo_totale']; ?>";
		break;
		case 8:
			ID_old = "<?php echo $Spese_Accessorie[8]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[8]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[8]['tipo_totale']; ?>";
		break;
		case 9:
			ID_old = "<?php echo $Spese_Accessorie[9]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[9]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[9]['tipo_totale']; ?>";
		break;
		case 10:
			ID_old = "<?php echo $Spese_Accessorie[10]['ID']; ?>";
			scelta = "<?php echo $Spese_Accessorie[10]['tipo_spesa']; ?>";
			scelta_totale = "<?php echo $Spese_Accessorie[10]['tipo_totale']; ?>";
		break;
	}

	if($('#data_stampa').val()!="")
	{
		$('#spesa_'+value).val(ID_old);
	}
	else if(ID == '')
	{
		ID = $('#spesa_'+value+' :selected').val();
		scelta = $('#spesa_'+value+' :selected').parent().attr('label');
		scelta_totale = $('#tot_parziale_'+value).val();

		opzione_scelta = $('#spesa_'+value+' :selected').val();

		if(ID!="" && scelta_totale == 0)
			scelta_totale = 1;
		else if(opzione_scelta == "")
			scelta_totale = 0;
	}
	else
	{
		$('#spesa_'+value).val(ID);
	}

	$('#tot_parziale_'+value).val(scelta_totale);

	if($('#percentuale').val()!="")
		percentuale = parseFloat($('#percentuale').val().replace(",","."));
	else
		percentuale = 0;

	if(scelta == "UNA TANTUM")
	{
		$('.una_tantum_'+value).show();
		$('.diverso_una_tantum_'+value).hide();

		$('#tariffa_'+value).val(number_format(Importo[ID],2,",",""));

		tot_spesa_singola = parseFloat(Importo[ID].replace(",","."));
		if(Coefficiente[ID]=="si")
			tot_spesa_singola += parseFloat(Importo[ID].replace(",",".")) * ( percentuale / 100 );

		$('#rimborso_tantum_'+value).val(number_format(tot_spesa_singola,2,",",""));
	}
	else if(scelta == "A GIORNO" || scelta == "A KM")
	{
		$('.diverso_una_tantum_'+value).show();
		$('.una_tantum_'+value).hide();

		$('#extra_'+value).val(number_format(Importo[ID],2,",",""));

		$('#fisso_'+value).val(number_format(Importo_Fisso[ID],2,",",""));
		$('#fisso_durata_'+value).text(Km_Giorni_Importo_Fisso[ID]);

		fisso = parseFloat($('#fisso_'+value).val().replace(",","."));
		extra = parseFloat($('#extra_'+value).val().replace(",","."));
		durata_extra = parseFloat($('#durata_extra_'+value).val().replace(",","."));

		tot_spesa_singola = fisso + (extra * durata_extra);
		if(Coefficiente[ID]=="si")
			tot_spesa_singola +=  tot_spesa_singola * ( percentuale / 100 );

		$('#rimborso_'+value).val(number_format(tot_spesa_singola,2,",",""));

	}
	else
	{
		$('#extra_'+value).val('');
		$('#durata_extra_'+value).val('');
		$('#fisso_'+value).val('');
		$('#rimborso_'+value).val('');
		$('#rimborso_tantum_'+value).val('');
		$('#tariffa_'+value).val('');
		$('#fisso_durata_'+value).text('');

		$('.diverso_una_tantum_'+value).hide();
		$('.una_tantum_'+value).hide();
	}

	totale_spese_accessorie();
}

function aggiorna_spesa(value)
{
	scelta = $('#spesa_'+value+' :selected').parent().attr('label');
	ID = $('#spesa_'+value+' :selected').val();
	percentuale = parseFloat($('#percentuale').val().replace(",","."));

	if(scelta != "UNA TANTUM")
	{
		extra = parseFloat($('#extra_'+value).val().replace(",","."));
		durata_extra = parseFloat($('#durata_extra_'+value).val().replace(",","."));

		fisso = parseFloat($('#fisso_'+value).val().replace(",","."));

		tot_spesa_singola = fisso + (extra * durata_extra);
		if(Coefficiente[ID]=="si")
			tot_spesa_singola +=  tot_spesa_singola * ( percentuale / 100 );

		$('#rimborso_'+value).val(number_format(tot_spesa_singola,2,",",""));
	}

	totale_spese_accessorie();
}

function totale_spese_accessorie()
{
	rimborso_totale = 0;
	rimborso_1 = 0;
	rimborso_2 = 0;
	rimborso_3 = 0;

	for(var j=1;j<11;j++)
	{
		rimborso_singolo = $('#rimborso_'+j).val();
		tipo_totale = $('#tot_parziale_'+j).val();
		if(rimborso_singolo == "")	rimborso_singolo = $('#rimborso_tantum_'+j).val();
		if(rimborso_singolo == "")	rimborso_singolo = "0,00";

		rimborso_totale += parseFloat(rimborso_singolo.replace(",","."));

		if(tipo_totale=="1")
			rimborso_1 += parseFloat(rimborso_singolo.replace(",","."));
		if(tipo_totale=="2")
			rimborso_2 += parseFloat(rimborso_singolo.replace(",","."));
		if(tipo_totale=="3")
			rimborso_3 += parseFloat(rimborso_singolo.replace(",","."));


	}

	$('#rimborso_totale').val(number_format(rimborso_totale,2,",",""));


	$('#spese_accessorie_1').val(number_format(rimborso_1,2,",",""));

	if(rimborso_2!=0)
		$('#spese_accessorie_2').val(number_format(rimborso_1+rimborso_2,2,",",""));
	else
		$('#spese_accessorie_2').val("0,00");

	if(rimborso_3!=0)
		$('#spese_accessorie_3').val(number_format(rimborso_1+rimborso_2+rimborso_3,2,",",""));
	else
		$('#spese_accessorie_3').val("0,00");

	update_totali();
}

</script>

<!-- ********** GESTIONE PAGINA ********** -->
<script>
var id_selezione = null;

function pagina_pignoramento(value)
{
	if(value==1)
	{
		$('.pignoramento_4').hide();
		$('.pignoramento_2').hide();
		$('.pignoramento_3').hide();
		$('.pignoramento_1').show();
		$('.pignoramento_5').hide();
	}
	else if(value==2)
	{
		$('.pignoramento_4').hide();
		$('.pignoramento_1').hide();
		$('.pignoramento_3').hide();
		$('.pignoramento_2').show();
		$('.pignoramento_5').hide();
	}
	else if(value==3)
	{
		$('.pignoramento_4').hide();
		$('.pignoramento_1').hide();
		$('.pignoramento_2').hide();
		$('.pignoramento_3').show();
		$('.pignoramento_5').hide();
	}
	else if(value==4)
	{
		$('.pignoramento_4').show();
		$('.pignoramento_1').hide();
		$('.pignoramento_2').hide();
		$('.pignoramento_3').hide();
		$('.pignoramento_5').hide();
	}
	else if(value==5)
	{
		$('.pignoramento_5').show();
		$('.pignoramento_4').hide();
		$('.pignoramento_1').hide();
		$('.pignoramento_2').hide();
		$('.pignoramento_3').hide();
	}
}

function scelta_pignoramento(value)
{

	if($('#data_stampa').val()!="" && value!='ingresso')
	{
		alert("Stampa definitiva gia effettuata! Impossibile modificare il pignoramento!");
		$('#tipo_pignoramento').val('<?php echo $tipo_pignoramento; ?>');
	}
// 	else if($('.tr_terzo_0_0:last').length > 0)
// 	{
// 		alert("Attenzione Terzo aggiunto! Impossibile modificare il tipo di pignoramento! L'operazione verra' annullata.");
// 		annulla();
// 	}

	scelta = $('#tipo_pignoramento').val();

	switch(scelta)
	{
		case "terzi":

			$('.tr_terzi').show();
			$('.td_terzi').show();
			$('.td_banca').hide();
			$('.tr_not_terzi').show();
			$('.tr_veicolo').hide();
			$('.tr_fermo').hide();
			$('.tr_preav_fermo').hide();
			$('.tr_immobiliare').hide();

			scelta_terzi();

		break;

		case "mobiliare":

			$('.tr_terzi').hide();
			$('.tr_not_terzi').hide();
			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();
			$('.tr_veicolo').hide();
			$('.tr_fermo').hide();
			$('.tr_preav_fermo').hide();
			$('.tr_immobiliare').hide();

		break;

		case "beni":

			$('.tr_terzi').hide();
			$('.tr_not_terzi').hide();
			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();
			$('.tr_veicolo').hide();
			$('.tr_fermo').hide();
			$('.tr_preav_fermo').hide();
			$('.tr_immobiliare').hide();

		break;

		case "immobiliare":

			$('.tr_terzi').hide();
			$('.tr_not_terzi').hide();
			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();
			$('.tr_veicolo').hide();
			$('.tr_fermo').hide();
			$('.tr_preav_fermo').hide();
			$('.tr_immobiliare').show();

			scelta_immobile(0);
			scelta_immobile(1);
			scelta_immobile(2);


		break;

		case "veicolo":

			$('.tr_terzi').hide();
			$('.tr_not_terzi').hide();

			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();

			$('.tr_veicolo').show();
			$('.tr_fermo').hide();
			$('.tr_preav_fermo').hide();
			$('.tr_immobiliare').hide();

			update_notifica_veicolo();

		break;

		case "fermo":

			$('.tr_terzi').hide();
			$('.tr_not_terzi').hide();

			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();

			$('.tr_veicolo').hide();
			$('.tr_fermo').show();
			$('.tr_preav_fermo').hide();
			$('.tr_immobiliare').hide();

			scelta_default();

		break;

		case "preav_fermo":

			$('.tr_terzi').hide();
			$('.tr_not_terzi').hide();

			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();

			$('.tr_veicolo').hide();
			$('.tr_fermo').hide();
			$('.tr_preav_fermo').show();
			$('.tr_immobiliare').hide();

			scelta_default();

		break;

		default:

			$('.tr_terzi').hide();
			$('.tr_not_terzi').hide();
			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();
			$('.tr_veicolo').hide();
			$('.tr_fermo').hide();
			$('.tr_preav_fermo').hide();
			$('.tr_immobiliare').hide();

		break;
	}
}

function scelta_terzi ()
{
	if($('#data_stampa').val()!="")
	{
		$('#presso_terzi').val('<?php echo $tipo_terzi_generale; ?>');
	}
// 	else if($('.tr_terzo_0_0:last').length > 0)
// 	{
// 		alert("Attezione Terzo aggiunto! Impossibile modificare il tipo di pignoramento! L'operazione verra' annullata.");
// 		annulla();
// 	}

	scelta = $('#presso_terzi').val();

	switch(scelta)
	{
		case "lavoro":

			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').show();
			$('.tr_dipendente').hide();
			$('.tr_titolare').hide();
			$('.tr_not_terzi').show();
			$('.td_terzi').show();
			$('.td_banca').hide();

			update_notifica_terzi();

		break;

		case "banca":

			$('.tr_lavoro').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_banca').show();
			$('.tr_not_terzi').show();
			$('.td_terzi').hide();
			$('.td_banca').show();

			update_notifica_terzi();

		break;

		case "inps":

			$('.tr_lavoro').hide();
			$('.tr_banca').hide();
			$('.tr_altro').hide();
			$('.tr_inps').show();
			$('.tr_not_terzi').show();
			$('.td_terzi').show();
			$('.td_banca').hide();

			update_notifica_terzi();

		break;

		case "altro":

			$('.tr_lavoro').hide();
			$('.tr_inps').hide();
			$('.tr_banca').hide();
			$('.tr_altro').show();
			$('.tr_not_terzi').show();
			$('.td_terzi').show();
			$('.td_banca').hide();

			update_notifica_terzi();

		break;

		default:

			$('.tr_banca').hide();
			$('.tr_inps').hide();
			$('.tr_altro').hide();
			$('.tr_lavoro').hide();
			$('.tr_not_terzi').hide();
			$('.td_terzi').hide();
			$('.td_banca').hide();

		break;
	}

}

function scelta_contratto(value)
{
	scelta = $('#tipo_contratto_'+value).val();

	switch(scelta)
	{
		case "titolare":

			$('.tit_'+value).show();
			$('.dip_'+value).hide();

			break;

		default:

			$('.dip_'+value).show();
			$('.tit_'+value).hide();

			break;

	}
}

function scelta_immobile (value)
{
	scelta = $('#tipo_immobiliare_'+value).val();

	switch(scelta)
	{
		case "fabbricato":

			$('.tr_fabbricato_'+value).show();
			$('.tr_terreno_'+value).hide();

			break;

		case "terreno":

			$('.tr_fabbricato_'+value).hide();
			$('.tr_terreno_'+value).show();

			break;

		default:

			$('.tr_fabbricato_'+value).hide();
			$('.tr_terreno_'+value).hide();

			break;

	}
}

function update_notifica_veicolo ()
{
	if($('#data_stampa').val()!="")
	{
		$('#spese_not_veicolo').val('<?php echo $Spese_Notifica_Veicolo; ?>');
		return;
	}

	notifica = $('#spese_not_veicolo').val();
	if(notifica == "")	notifica = "0,00";

	$('#spese_terzi').val(notifica);

	//TOTALI
	spese_debitore = $('#spese_not_debitore').val();

	if(spese_debitore=="")	spese_debitore = "0,00";
	spese_debitore = parseFloat(spese_debitore.replace(",","."));

	spese_terzi = parseFloat(notifica.replace(",","."));

	tot_spese_notifica = number_format((spese_debitore + spese_terzi),2,",","");
	$('#spese_totali').val(tot_spese_notifica);

	update_totali();
}

function update_notifica_terzi()
{
	if($('#data_stampa').val()!="")
	{
		<?php
		for($i=0;$i<$count_terzi;$i++)
		{?>
			$('#spese_not_terzo_<?php echo $i; ?>').val('<?php echo $Spese_Notifica[$i]; ?>');
		<?php }
		?>

		return;
	}

	val=0;
	n = $('#spese_not_terzo_0').length;
	while(n>0)
	{
		val++;
		n = $('#spese_not_terzo_'+val).length;
	}

	somma_spese_terzi = 0;
	for(var conta=0;conta<val;conta++)
	{
		notifica = $('#spese_not_terzo_'+conta).val();

		if(notifica=="" || notifica==undefined)
			notifica = "0,00";

		somma_spese_terzi+= parseFloat(notifica.replace(",","."));
	}

	spese_terzi = number_format(somma_spese_terzi,2,",","");

	$('#spese_terzi').val(spese_terzi);


	//TOTALI
	spese_debitore = $('#spese_not_debitore').val();
	if(spese_debitore=="")	spese_debitore = "0,00";
	spese_debitore = parseFloat(spese_debitore.replace(",","."));

	spese_terzi = parseFloat(spese_terzi.replace(",","."));

	tot_spese_notifica = number_format((spese_debitore + spese_terzi),2,",","");
	$('#spese_totali').val(tot_spese_notifica);

	update_totali();

}

function update_notifica_debitore()
{
	if($('#data_stampa').val()!="")
	{
		$('#spese_not_debitore').val('<?php echo $Spese_Notifica_Debitore; ?>');
		return;
	}

	//DEBITORE
	notifica_debitore = $('#spese_not_debitore').val();
	if(notifica_debitore == "")	notifica_debitore = "0,00";

	if(notifica_debitore == "0,00")	$('#spese_debitore').val('');
	else							$('#spese_debitore').val(notifica_debitore);

	//TOTALI
	spese_terzi = $('#spese_terzi').val();
	if(spese_terzi=="")	spese_terzi = "0,00";
	spese_terzi = parseFloat(spese_terzi.replace(",","."));

	spese_debitore = parseFloat(notifica_debitore.replace(",","."));

	tot_spese_notifica = number_format((spese_debitore + spese_terzi),2,",","");
	$('#spese_totali').val(tot_spese_notifica);

	update_totali();
}

function update_totali()
{
	//TOTALI
	importo_ingiunzione = $('#importo_atto').val();
	if(importo_ingiunzione=="")	importo_ingiunzione = "0,00";
	importo_ingiunzione = parseFloat(importo_ingiunzione.replace(",","."));

	tot_spese_notifica = $('#spese_totali').val();
	if(tot_spese_notifica=="")	tot_spese_notifica = "0,00";
	tot_spese_notifica = parseFloat(tot_spese_notifica.replace(",","."));

  tot_parziali = tot_spese_notifica+importo_ingiunzione;
  $('#totale_parziale').val(number_format((tot_parziali),2,",",""));

	spese_accessorie_1 = $('#spese_accessorie_1').val();
	if(spese_accessorie_1=="")	spese_accessorie_1 = "0,00";
	spese_accessorie_1 = parseFloat(spese_accessorie_1.replace(",","."));

	spese_accessorie_2 = $('#spese_accessorie_2').val();
	if(spese_accessorie_2=="")	spese_accessorie_2 = "0,00";
	spese_accessorie_2 = parseFloat(spese_accessorie_2.replace(",","."));

	spese_accessorie_3 = $('#spese_accessorie_3').val();
	if(spese_accessorie_3=="")	spese_accessorie_3 = "0,00";
	spese_accessorie_3 = parseFloat(spese_accessorie_3.replace(",","."));

	totale_1 = number_format((importo_ingiunzione + tot_spese_notifica + spese_accessorie_1),2,",","");
	totale_2 = number_format((importo_ingiunzione + tot_spese_notifica + spese_accessorie_2),2,",","");
	totale_3 = number_format((importo_ingiunzione + tot_spese_notifica + spese_accessorie_3),2,",","");

	$('#totale_pignoramento_1').val(totale_1);
	if(spese_accessorie_2!="0.00")
		$('#totale_pignoramento_2').val(totale_2);
	else
		$('#totale_pignoramento_2').val("0,00");

	if(spese_accessorie_3!="0.00")
		$('#totale_pignoramento_3').val(totale_3);
	else
		$('#totale_pignoramento_3').val("0,00");
}

function selezione_pec(value)
{
	spedizione_pec = $('#pec_'+value).val();

	if(spedizione_pec=="si")
		$('#testo_pec_'+value).show();
	else
		$('#testo_pec_'+value).hide();
}

function scelta_default()
{
	scelta = $('#tipo_pignoramento').val();
	id_pignoramento = $('#pignoramento_ID').val();

	$("#tipo_ufficiale option[value=riscossione]").prop('disabled',false);
	$("#tipo_ufficiale option[value=giudiziario]").prop('disabled',false);

	if(id_pignoramento=="" || id_pignoramento == undefined)
	{
		$('#modalita_stampa_debitore_0').val('posta');
	}

	if( scelta=="veicolo" )
	{

		if(id_pignoramento=="" || id_pignoramento == undefined)
		{
			$("#tipo_ufficiale").val('riscossione');
			$('#modalita_stampa_veicolo_0').val('pec');
			$('.tr_validato_veicolo_0').hide();

			$('#spesa_1').val('<?php echo $sp_ac_pigno_immobiliare; ?>');
			scelta_spesa(1,'');
			$('#spesa_2').val('<?php echo $sp_ac_stima_beni; ?>');
			scelta_spesa(2,'');
			$('#spesa_3').val('<?php echo $sp_ac_spese_ispezione; ?>');
			scelta_spesa(3,'');
			$('#spesa_4').val('<?php echo $sp_ac_progetto_attribuzione; ?>');
			scelta_spesa(4,'');
			$('#spesa_5').val('<?php echo $sp_ac_richiesta_copia; ?>');
			scelta_spesa(5,'');
			$('#tot_parziale_5').val(2);
			$('#spesa_6').val('<?php echo $sp_ac_iscrizione_fermo; ?>');
			scelta_spesa(6,'');
			$('#tot_parziale_6').val(2);
			$('#spesa_7').val('<?php echo $sp_ac_revoca_fermo; ?>');
			scelta_spesa(7,'');
			$('#tot_parziale_7').val(2);
		}
	}
	else if(scelta == "terzi")
	{
		tipo_terzi = $("#presso_terzi").val();

		if(tipo_terzi=="inps")
		{
			$("#tipo_ufficiale option[value=riscossione]").prop('disabled',true);
			$("#tipo_ufficiale").val('giudiziario');
		}
		else if(tipo_terzi=="lavoro")
		{
			if(id_pignoramento=="" || id_pignoramento == undefined)
			{
				$("#tipo_ufficiale").val('riscossione');

				$('#spesa_1').val(sp_ac_pigno_presso_terzi);
				scelta_spesa(1,'');
				$('#spesa_2').val(sp_ac_stima_beni);
				scelta_spesa(2,'');
				$('#spesa_3').val(sp_ac_progetto_attribuzione);
				scelta_spesa(3,'');
			}
		}
		else if(tipo_terzi=="banca")
		{
			if(id_pignoramento=="" || id_pignoramento == undefined)
			{
				$("#tipo_ufficiale").val('riscossione');

				$('#spesa_1').val(sp_ac_pigno_presso_terzi);
				scelta_spesa(1,'');
				$('#spesa_2').val(sp_ac_stima_beni);
				scelta_spesa(2,'');
				$('#spesa_3').val(sp_ac_progetto_attribuzione);
				scelta_spesa(3,'');
			}
		}

		if($('#pignorato_id_'+tipo_terzi+'_0').val() == "0")
		{
			$('#modalita_stampa_terzo_0_0').val('pec');
			$('.tr_validato_veicolo_0_0').hide();
		}
		if($('#pignorato_id_'+tipo_terzi+'_1').val() == "0")
		{
			$('#modalita_stampa_terzo_1_0').val('pec');
			$('.tr_validato_terzo_1_0').hide();
		}
		if($('#pignorato_id_'+tipo_terzi+'_2').val() == "0")
		{
			$('#modalita_stampa_terzo_2_0').val('pec');
			$('.tr_validato_terzo_2_0').hide();
		}

	}
	else if(scelta == "preav_fermo" || scelta == "fermo")
	{
		$("#tipo_ufficiale option[value=riscossione]").prop('disabled',true);
		$("#tipo_ufficiale option[value=giudiziario]").prop('disabled',true);
		$("#tipo_ufficiale").val('');
	}
}

function control_azienda(value_terzo)
{

	$.ajax({
		  type: "POST",
		  async: false,
		  url: "ajax/ajax_partita.php?c=<?php echo $c; ?>",
		  data: {
			  		ajax: "azienda",
			  		Azienda: $('#azienda_lavoro_'+value_terzo).val(),
				},

		  success: function(value_azienda) {

		  		if(value_azienda!="")
		  		{
			  		alert("Ditta presente in archivio. Dopo la conferma avverra' il caricamento.");
			  		$.ajax({
		  			  type: "POST",
		  			  async: false,
		  			  url: "ajax/ajax_partita.php?c=<?php echo $c; ?>",
		  			  data: {
		  				  		ajax: "nome",
		  				  		ID: value_azienda,
		  					},

		  			  success: function(value) {

		  			  		nome = value;
		  			  }
		  		});

		  			$('#pignorato_lavoro_'+value_terzo).val(nome);
		  			$('#pignorato_id_lavoro_'+value_terzo).val(value_azienda);
		  			$('#spese_not_terzo_'+value_terzo).val("<?php echo number_format($para_Spese_Notifica,2,",",""); ?>");
		  			update_notifica_terzi();

		  		}
		  		else
		  		{
		  			alert("Ditta non presente in archivio.");
		  		}
		  }
	});
}

function func_ricerca_banche()
{

    selectParent = "sede";
	if($('#data_stampa').val()!="")
	{
		alert("Stampa definitiva gia effettuata!");
		return false;
	}

	comune_da_cercare = $('#ricerca_banche').val();
	cap_da_cercare = $('#cap_banche').val();

	//strDim = Dim_Alert(800, 500);
	var link = "<?= WEB_ROOT; ?>/search/banche/ricerca_banche.php?richiesta=comuneBanca&c=<?php echo $c; ?>&a=<?php echo $a; ?>&Comune_nome="+comune_da_cercare+"&cap_banca="+cap_da_cercare;
  openWindowSearch(link,{width:800, height:500, left:(window.screen.width/2)-400, top:(window.screen.height/2)-250});
	//sede = window.showModalDialog(stringa,"", strDim);
}

function carica_banca(numero)
{
    //alert("window.open");
    selectParent = "banca";
    num = numero;
	if($('#data_stampa').val()!="")
	{
		alert("Stampa definitiva gia effettuata!");
		return false;
	}

	//strDim = Dim_Alert(800, 500);
	var link = "<?= WEB_ROOT; ?>/search/banche/ricerca_banche.php?richiesta=singola&c=*****&a=<?php echo $a; ?>";
	//window.open(link);
    openWindowSearch(link,{width:800, height:500, left:(window.screen.width/2)-400, top:(window.screen.height/2)-250});
	//banca = window.showModalDialog(stringa,"", strDim);
}

var num = "";
var tipo = "";
function carica_utente(numero, tipoTerzo)
{

    selectParent = "carica_utente";
    num = numero;
    tipo = tipoTerzo;
    if($('#data_stampa').val()!="")
    {
        alert("Stampa definitiva gia effettuata!");
        return false;
    }

    var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

    openWindowSearch(stringa,{width:700, height:500, left:(window.screen.width/2)-350, top:(window.screen.height/2)-250});

}

function carica_veicolo(numero,table)
{
    //alert("Procedura di caricamento da implementare tramite webservice ACI/PRA");
    var ID_Veicolo_here = $( "#lista_veicoli" ).val();

    if('<?= $tipo_pignoramento_get; ?>'!="" && '<?= $id_veicolo_get; ?>'!="")
    {
      numero = 0;
      table = '<?= $tipo_pignoramento_get; ?>';
      ID_Veicolo_here = '<?= $id_veicolo_get; ?>'
    }

    $.ajax({
        type: "POST",
        url: "ajax/ajax_veicoli.php",
        dataType: 'json',
        data: {
          "ID": ID_Veicolo_here
        },
        success: function (data){
            console.log(data);
          //  $("#tipo_veicolo_"+numero).val(data.SerieTarga.toLowerCase());
            $('#tipo_'+table+'_'+numero+' option[value="' + data.SerieTarga.toLowerCase() +'"]').prop('selected', true);
            $("#targa_"+table+"_"+numero).val(data.Targa);
            $("#data_visura_"+table+"_"+numero).val(data.Data_Visura);
            $("#marca_"+table+"_"+numero).val(data.Fabbrica);
            $("#modello_"+table+"_"+numero).val(data.Tipo);
            $("#fonte_dati_"+table+"_"+numero).val("pra");
            $("#id_veicolo_"+table+"_"+numero).val(data.ID);
            //manca portata
            //manca valore
            $("#anno_immatricolazione_"+table+"_"+numero).val(data.DataPrimaImmatricolazione);
        }

    });
    //selectParent = "veicolo";
    //num = numero;
    //if($('#data_stampa').val()!="")
    //{
    //    alert("Stampa definitiva gia effettuata!");
    //    return false;
    //}
    //
    //strDim = Dim_Alert(700, 500);
    //var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=veicolo&c=<?php //echo $c; ?>//&a=<?php //echo $a; ?>//&p=<?php //echo $p; ?>//";
    //
    //valorediritorno = window.showModalDialog(stringa,"", strDim);
}


function aggiungi_terzo(tipo_terzo)
{
  //return false;
  //alert("function aggiungi terzo");
  //
	if($('#data_stampa').val()!="")
	{
		alert("Stampa definitiva gia effettuata!");
		return false;
	}

	val=0;
	n = $('#pignorato_id_'+tipo_terzo+'_0').length;
	while(n>0)
	{
		val++;
		n = $('#pignorato_id_'+tipo_terzo+'_'+val).length;
	}

	switch(tipo_terzo)
	{
		case "banca":	aggiungi_banca(val); aggiungi_notifica_terzo(val);	break;
		case "lavoro":	aggiungi_lavoro(val); aggiungi_notifica_terzo(val);	break;
	}
}

function elimina_terzo(tipo_terzo,val)
{
	if($('#data_stampa').val()!="")
	{
		alert("Stampa definitiva gia effettuata!");
		return false;
	}

	$('.'+tipo_terzo+'_'+val).remove();
	$('.ctrl_terzo_'+val).remove();
}

function popup_riscontro(tipo_terzi, ID_notifica)
{
   //alert(ID_notifica);
  selectParent = "riscontro";
	if(tipo_terzi=="lavoro")
		tipo_terzi = "riscontro_lavoro";
	else
		tipo_terzi = "riscontro_notifica";

	var link="<?= WEB_ROOT; ?>/search/coattiva/"+tipo_terzi+".php?c=<?php echo $c?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&id_notifica="+ID_notifica+"&pignoramento=<?php echo $pignoramento_ID; ?>";
	//strDim = Dim_Alert(1200, 800);

	//window.showModalDialog(link,"", strDim);
  openWindowSearch(link,{width:1200, height:800, left:(window.screen.width/2)-600, top:(window.screen.height/2)-400});
	//location.href="pignoramento.php?pignoramento=<?php echo $pignoramento_ID; ?>&partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function popup_ivg(ID_notifica)
{
  if(ID_notifica!=undefined)
  {
    selectParent = "ivg";

  	var link="<?= WEB_ROOT; ?>/search/coattiva/riscontro_ivg.php?c=<?php echo $c?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&id_notifica="+ID_notifica+"&pignoramento=<?php echo $pignoramento_ID; ?>";
    openWindowSearch(link,{width:1200, height:800, left:(window.screen.width/2)-600, top:(window.screen.height/2)-400});
  }
  else {
    alert("Prima inserire i dati del pignoramento.");
  }

	//strDim = Dim_Alert(1200, 800);

	//window.showModalDialog(link,"", strDim);
	//location.href="pignoramento.php?pignoramento=<?php echo $pignoramento_ID; ?>&partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function aggiungi_banca(val)
{
  //alert("val banca --> "+val);

  stringa = "";
  stringa+= "<div class='tr_banca banca_"+val+"'>";
  stringa+= "<input type=hidden name='pignorato_id_banca_"+val+"' id='pignorato_id_banca_"+val+"' value='0'>";
  stringa+= "</div>";

  stringa+= "<div class='tr_banca banca_"+val+"' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>";
  stringa+= "<div class='row tr_banca banca_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Terzo</label>";
  stringa+=	   		"<div class='col-lg-6 '>";
  stringa+=	  			"<input class='form-control resize' style='background-color: rgb(153, 204, 255); border: 2px solid black;' readonly name='pignorato_banca_"+val+"' id='pignorato_banca_"+val+"' value='' ondblclick='carica_banca("+val+");'>";
  stringa+=	  		"</div>";
  stringa+=       "<div class='col-lg-2'>";
  stringa+=         "<a onMouseover=\"title='Elimina terzo'\" href='#' style='text-decoration:none;' onClick=\"elimina_terzo('banca',"+val+");\" >";
  stringa+=            "<i class='fas fa-trash' style='color: red;'></i>";
  stringa+=          "</a>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Fonte dati</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name='fonte_banca_"+val+"' id='fonte_banca_"+val+"' value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_banca banca_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Tipo titolo</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=          "<select name=tipo_titolo_"+val+" id=tipo_titolo_"+val+" class='form-control resize'>";
	stringa+=          "<option></option><option value='conto'>Conto corrente</option><option value='libretto'>Libretto</option><option value='altro'>Altro</option></select></td>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Note</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=note_banca_"+val+" id=note_banca_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_banca banca_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Titolo</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=         "<input class='form-control resize' name=titolo_"+val+" id=titolo_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Intestatario</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=intestatario_"+val+" id=intestatario_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_banca banca_"+val+"' id='tr_banca_finale_"+val+"'>";
  stringa+= 	"<div class='col col-lg-10 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-2 control-label resize' style='text-align: left;'>Data costituz. ditta</label>";
  stringa+=	   		"<div class='col-lg-10 '>";
  stringa+=	  			"<input class='form-control resize' name=coointestatari_"+val+" id=coointestatari_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";


	if(val==0)
		$('#tr_banca_iniziale').after(stringa);
	else
  {
    //$('#tr_banca_finale_'+(val)).remove();
    $('#tr_banca_finale_'+(val-1)).after(stringa);
  }
}

function aggiungi_lavoro(val)
{
  //alert("function aggiungi_lavoro");
  //alert("val lavoro --> "+val);
	stringa = "";
	stringa+= "<div class='tr_lavoro lavoro_"+val+"'>";
	stringa+= "<input type=hidden name='pignorato_id_lavoro_"+val+"' id='pignorato_id_lavoro_"+val+"'	value='0' 	>";
	stringa+= "</div>";

  stringa+= "<div class='tr_lavoro lavoro_"+val+"' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>";
  stringa+= "<div class='row tr_lavoro lavoro_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Terzo</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' style='background-color: rgb(153, 204, 255); border: 2px solid black;' readonly name=pignorato_lavoro_"+val+" id=pignorato_lavoro_"+val+" value='' ondblclick=\"carica_utente( "+val+" , 'lavoro');\">";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+=   "<div class='col col-lg-1'>";
  stringa+=     "<div class='form-group'>"
  stringa+=       "<div class='col-lg-12'>";
  stringa+=         "<a onMouseover=\"title='Elimina terzo'\" href='#' style='text-decoration:none;' onClick=\"elimina_terzo('lavoro',"+val+");\" >";
	stringa+=            "<i class='fas fa-trash' style='color: red;'></i>";
	stringa+=          "</a>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_lavoro lavoro_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Azienda</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=azienda_lavoro_"+val+" id=azienda_lavoro_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Fonte dati</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=fonte_lavoro_"+val+" id=fonte_lavoro_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_lavoro lavoro_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Tipo contratto</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=         "<select name=tipo_contratto_"+val+" id='tipo_contratto_"+val+"' class='form-control resize' onchange='scelta_contratto("+val+");'>";
	stringa+=           "<option></option><option value='titolare'>Titolare</option><option value='accessorio'>Accessorio</option>";
	stringa+=           "<option value='apprendistato'>Apprendistato</option><option value='chiamata'>Chiamata</option>";
	stringa+=           "<option value='collaborazione'>Collaborazione</option><option value='determinato'>Determinato</option>";
	stringa+=           "<option value='indeterminato'>Indeterminato</option><option value='inserimento'>Inserimento</option>";
	stringa+=           "<option value='interinale'>Interinale</option><option value='occasionale'>Occasionale</option>";
	stringa+=           "<option value='progetto'>Progetto</option><option value='ripartito'>Ripartito</option>";
	stringa+=           "<option value='somministrazione'>Somministrazione</option><option value='parziale'>Tempo parziale</option>";
	stringa+=           "<option value='altro'>Altro</option></select>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Note</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=note_lavoro_"+val+" id=note_lavoro_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_lavoro lavoro_"+val+"' id='tr_lavoro_finale_"+val+"'>";
  stringa+= 	"<div class='col col-lg-4 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Data costituz. ditta</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='picker text_center form-control resize validateCustom vld_Custom_date' style='width: 50%;' name=data_costituzione_"+val+" id=data_costituzione_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-3'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-6 control-label resize' style='text-align: left;'>Data ditta operativa</label>";
  stringa+=	   		"<div class='col-lg-6'>";
  stringa+=	  			"<input class='picker text_center form-control resize validateCustom vld_Custom_date' name=data_operativa_"+val+" id=data_operativa_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-3'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-6 control-label resize' style='text-align: left;'>Data dipendenze</label>";
  stringa+=	   		"<div class='col-lg-6 '>";
  stringa+=	  			"<input class='picker text_center form-control resize validateCustom vld_Custom_date' name=data_dipendenze_"+val+" id=data_dipendenze_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";


	if(val==0)
  {
    $('#tr_lavoro_iniziale').after(stringa);
    //InizializzaAttributi();
  }
	else
  {
    //$('#tr_lavoro_finale_'+(val)).remove();
    $('#tr_lavoro_finale_'+(val-1)).after(stringa);
    //InizializzaAttributi();
  }
  InizializzaAttributi();

}

function aggiungi_notifica_terzo(val)
{
  //alert("function aggiungi_notifica_terzo");
  //console.log("val "+val);
  stringa = "";
  stringa+= "<div class='tr_not_terzi ctrl_terzo_"+val+"' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%;margin-bottom: 2%;'></div>\n";
  stringa+= "<div class='row tr_not_terzi ctrl_terzo_"+val+"'>\n";
  stringa+=   "<div class='col col-lg-3 col-lg-offset-1' >\n";
  stringa+=     "<div class='form-group'>\n";
  stringa+=       "<div class='col-lg-12'>\n";
  stringa+=         "<font id='denom_terzo_"+val+"' class='titolo resize'></font>\n";
  stringa+=       "</div>\n";
  stringa+=     "</div>\n";
  stringa+=   "</div>\n";
  stringa+=   "<div class='col col-lg-3' >\n";
  stringa+=     "<div class='form-group'>\n";
  stringa+=       "<div class='col-lg-12 resize'>\n";
  stringa+=         "<a href='#' style='text-decoration:none;'>\n";
  stringa+=           "<img src='<?= IMMAGINIWEB; ?>/plus.png' style='text-decoration:none; border:none' width='20' height='20' onclick=\"aggiungi_notifica('terzo','"+val+"','1');\" title='Aggiungi notifica'>\n";
  stringa+=         "</a>\n";
  stringa+=       "NOTIFICA\n";
  stringa+=       "</div>\n";
  stringa+=     "</div>\n";
  stringa+=   "</div>\n";
  stringa+=   "<div class='col col-lg-3 col-lg-offset-1' >\n";
  stringa+=     "<div class='form-group'>\n";
  stringa+=       "<label class='col-lg-6 control-label resize' style='text-align: left;'>Spese notifica</label>\n";
  stringa+=       "<div class='col-lg-5'>\n";
  stringa+=         "<input id='spese_not_terzo_"+val+"' name='spese_not_terzo_"+val+"' class='text_right form-control resize validateCustom vld_Custom_d' value='0' size=6 onchange='update_notifica_terzi();'>\n";
  stringa+=       "</div>\n";
  stringa+=       "<label class='col-lg-1 control-label resize' style='text-align: left;'>&euro;</label>\n";
  stringa+=     "</div>\n";
  stringa+=   "</div>\n";
  stringa+= "</div>\n";

  stringa+="<div class='row tr_not_terzi tr_terzo_"+val+"_0 ctrl_terzo_"+val+"' >\n";
  stringa+=  "<div class='col col-lg-4 col-lg-offset-1' >\n";
  stringa+=    "<div class='form-group'>\n";
  stringa+=      "<label class='col-lg-3 control-label resize'  style='text-align: left;'><font class='titolo'>Notifica 1</font></label>\n";
  stringa+=      "<div class='col-lg-8'>\n";
  stringa+=         "<select id='modalita_stampa_terzo_"+val+"_0' name='modalita_stampa_terzo_"+val+"_0' class='form-control resize' onchange=\"cambio_modalita('terzo_"+val+"','0');\">\n";
	stringa+=            "<option value=''></option>\n";
	stringa+=            "<option value='posta'>Tramite posta</option>\n";
	stringa+=            "<option value='mani'>A mani</option>\n";
	stringa+=            "<option value='pec' selected>Via PEC</option>\n";
	stringa+=         "</select>\n";
  stringa+=      "</div>\n";
  stringa+=    "</div>\n";
  stringa+=  "</div>\n";
  stringa+="</div>\n";

  stringa+="<div class='row tr_not_terzi tr_terzo_"+val+"_0 ctrl_terzo_"+val+"' >\n";
  stringa+=  "<div class='col col-lg-3 col-lg-offset-1' >\n";
  stringa+=    "<div class='form-group'>\n";
  stringa+=      "<label class='col-lg-4 control-label resize' style='text-align: left;'>Data notifica</label>\n";
  stringa+=      "<div class='col-lg-8'>\n";
  stringa+=        "<input id='data_not_terzo_"+val+"_0' class='text_center picker form-control resize validateCustom vld_Custom_date' name='data_not_terzo_"+val+"_0' type=text value='' size=9 onchange =''>\n";
  stringa+=      "</div>\n";
  stringa+=    "</div>\n";
  stringa+=  "</div>\n";
  stringa+=  "<div class='col col-lg-7' >\n";
  stringa+=    "<div class='form-group'>\n";
  stringa+=      "<label class='col-lg-3 control-label resize' style='text-align: left;'>Modalita'</label>\n";
  stringa+=      "<div class='col-lg-9'>\n";
  stringa+=       "<select id='modalita_not_terzo_"+val+"_0' name='modalita_not_terzo_"+val+"_0' class='form-control resize' onchange=\"cambia_title('modalita_not_terzo_"+val+"_0');\">\n";
	stringa+=           "<option></option>\n";
	stringa+=           "<optgroup label='Tramite soggetto preposto'><?php echo $options_a_mani; ?></optgroup>\n";
	stringa+=           "<optgroup label='Per posta'><?php echo $options_per_posta; ?></optgroup>\n";
	stringa+=           "<optgroup label='Eccezionali'><?php echo $options_eccezionali; ?></optgroup>\n";
	stringa+=       "</select>\n";
  stringa+=      "</div>\n";
  stringa+=    "</div>\n";
  stringa+=  "</div>\n";
  stringa+="</div>\n";

  stringa+="<div class='tr_not_terzi tr_terzo_"+val+"_0 ctrl_terzo_"+val+"' >\n";
  stringa+=  "<div class='col col-lg-5 col-lg-offset-1' >\n";
  stringa+=    "<div class='form-group'>\n";
  stringa+=      "<label class='col-lg-2 control-label resize' style='text-align: left;'>Giacenza</label>\n";
  stringa+=      "<div class='col-lg-10'>\n";
  stringa+=          "<select id='stato_not_terzo_"+val+"_0' name='stato_not_terzo_"+val+"_0' class='form-control resize' onchange=\"cambia_title('stato_not_terzo_"+val+"_0');\" >\n";
	stringa+=            "<option></option>\n";
	stringa+=            "<?php echo $options_stati; ?>\n";
	stringa+=          "</select>\n";
  stringa+=      "</div>\n";
  stringa+=    "</div>\n";
  stringa+=  "</div>\n";
  stringa+=  "<div class='col col-lg-5' >\n";
  stringa+=    "<div class='form-group'>\n";
  stringa+=      "<label class='col-lg-2 control-label resize' style='text-align: left;'>Anomalie</label>\n";
  stringa+=      "<div class='col-lg-10'>\n";
  stringa+=        "<select id='motivo_not_terzo_"+val+"_0' name='motivo_not_terzo_"+val+"_0' class='form-control resize' onchange=\"cambia_title('motivo_not_terzo_"+val+"_0');\">\n";
	stringa+=          "<option ></option>\n";
	stringa+=          "<?php echo $options_motivi; ?>\n";
	stringa+=        "</select>\n";
  stringa+=      "</div>\n";
  stringa+=    "</div>\n";
  stringa+=  "</div>\n";
  stringa+="</div>\n";

  stringa+="<div id='' class='tr_not_terzi tr_terzo_"+val+"_0 tr_validato_terzo_"+val+"_0 ctrl_terzo_"+val+"' >\n";
  stringa+=  "<div class='col col-lg-2 col-lg-offset-1' >\n";
  stringa+=    "<div class='form-group'>\n";
  stringa+=      "<label class='col-lg-8 control-label resize' style='text-align: left;'>Ind. validato</label>\n";
  stringa+=      "<div class='col-lg-4'>\n";
  stringa+=        "<input type=checkbox id=ind_validato_terzo_"+val+"_0 name=ind_validato_terzo_"+val+"_0 value='si' title=\"Ind. validato - Flag di verifica dell'indirizzo del destinatario. E' necessaria la verifica nel caso sia selezionato uno Stato di Giacenza\">\n";
  stringa+=      "</div>\n";
  stringa+=    "</div>\n";
  stringa+=  "</div>\n";
  stringa+=  "<div class='col col-lg-8' >\n";
  stringa+=    "<div class='form-group'>\n";
  stringa+=      "<label class='col-lg-3 control-label resize' style='text-align: right;'>Note</label>\n";
  stringa+=      "<div class='col-lg-9'>\n";
  stringa+=        "<input id=note_not_terzo_"+val+"_0 class='form-control resize' name=note_not_terzo_"+val+"_0 type=text value=''>\n";
  stringa+=      "</div>\n";
  stringa+=    "</div>\n";
  stringa+=  "</div>\n";
  stringa+="</div>\n";

  stringa+= "<div class='row' id='tr_not_terzo_finale_"+val+"'></div>";

  //console.log(stringa);
	if(val==0)
		$('.tr_not_debitore_ultimo:last').after(stringa);
	else
    $('#tr_not_terzo_finale_'+(val-1)).after(stringa);


  InizializzaAttributi();

}


</script>


<!-- ********** MODALI ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px; ";
	setupPagina += "dialogHeight:" + sHeight + "px; ";
	setupPagina += "dialogLeft:80px; dialogTop:80px;";

	return setupPagina;
}

function callParent(valorediritorno) {

        switch(selectParent){
            case "ivg":

              location.href="<?= WEB_ROOT; ?>/coattiva/pignoramento.php?pignoramento=<?php echo $pignoramento_ID; ?>&partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

              break;
            case "riscontro":

              location.href="<?= WEB_ROOT; ?>/coattiva/pignoramento.php?pignoramento=<?php echo $pignoramento_ID; ?>&partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

              break;
            case "veicolo":
                if(valorediritorno!=null) {

                }
                break;

            case "utente":
                if(valorediritorno!=null) {
                    if (typeof valorediritorno !== 'string')
                        reopen('obj', valorediritorno);
                    else
                        reopen('str', valorediritorno);
                }
                break;

            case "sede":

                if(valorediritorno.Comune_ricerca != null && valorediritorno.Comune_ricerca != undefined && valorediritorno.Comune_ricerca != "")
                {
                    oggetto_banca = valorediritorno;
                    $('#ricerca_banche').val(oggetto_banca.Comune_ricerca);
                    $('#cap_banche').val(oggetto_banca.Cap_ricerca);

                    var conta_banche = 0;
                    var banche_no_pass = 0;
                    var banche_pass = 0;
                    while(oggetto_banca['ID_'+conta_banche]>0)
                    {
                        if(oggetto_banca['password_'+conta_banche]!="")
                            banche_pass++;
                        else
                            banche_no_pass++;

                        conta_banche++;
                    }



                    alert("Sono state rilevate "+banche_pass+" filiali con password inserita e "+banche_no_pass+" filiali sprovviste di password.");
                    var k_banca = 0;
                    for(var j=0;j<conta_banche;j++)
                    {
                        if(oggetto_banca['password_'+j]!="")
                        {
                            if($('#pignorato_id_banca_'+k_banca).length==0)
                                aggiungi_terzo("banca");

                            $('#pignorato_banca_'+k_banca).val(oggetto_banca['denominazione_'+j]);
                            $('#pignorato_id_banca_'+k_banca).val(oggetto_banca['ID_'+j]);
                            if(j==0 && $('#modalita_stampa_terzo_'+k_banca+'_0').val()!="pec")
                                $('#spese_not_terzo_'+k_banca).val("<?php echo number_format($para_Spese_Notifica,2,",",""); ?>");
                            $('#denom_terzo_'+k_banca).text(oggetto_banca['denominazione_'+j]);

                            k_banca++;
                        }
                        else
                        {
                            alert("Filiale "+oggetto_banca['denominazione_'+j]+" [ID "+oggetto_banca['ID_'+j]+"] sprovvista di password!");
                        }
                    }

                    update_notifica_terzi();

                    return;
                }

                break;

            case "banca":

                if( valorediritorno.ID != null && valorediritorno.ID != undefined && valorediritorno.ID != "")
                {
                    if(valorediritorno.Password!="")
                    {
                        if($('#spese_not_terzo_'+num).length==0)
                            aggiungi_notifica_terzo(num);

                        if(valorediritorno.Tipo_banca == "sede")
                            $('#pignorato_id_banca_'+num).val(valorediritorno.ID);
                        else if(valorediritorno.Tipo_banca == "filiale")
                            $('#pignorato_id_banca_'+num).val(valorediritorno.ID_Collegamento);

                        $('#pignorato_banca_'+num).val(valorediritorno.Denominazione);
                        if(num==0 && $('#modalita_stampa_terzo_'+num+'_0').val()!="pec")
                            $('#spese_not_terzo_'+num).val("<?php echo number_format($para_Spese_Notifica,2,",",""); ?>");
                        $('#denom_terzo_'+num).text(valorediritorno.Denominazione);

                        update_notifica_terzi();
                    }
                    else
                        alert("Filiale "+valorediritorno.Denominazione+" [ID "+valorediritorno.ID+"] sprovvista di password!");

                }

                break;

            case "carica_utente":

                if(valorediritorno.p == "<?php echo $utente_ID; ?>")
                {
                    alert("Impossibile caricare il pignorato come terzo!");
                    $('#pignorato_'+tipo+'_'+num).val('');
                    $('#pignorato_id_'+tipo+'_'+num).val('0');
                    $('#denom_terzo_'+num).text('');

                    if($('#spese_not_terzo_'+num).length>0)
                    {
                        $('#spese_not_terzo_'+num).val('');
                        $('#scelta_can_cad_terzo_'+num).val('');
                        update_notifica_terzi();
                    }
                    return;
                }

                if( valorediritorno.p != null && valorediritorno.p != undefined && valorediritorno.p != "")
                {
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "ajax/ajax_partita.php?c=<?php echo $c; ?>",
                        data: {
                            ajax: "nome",
                            ID: valorediritorno.p,
                        },
                        success: function(nome) {

                            if($('#spese_not_terzo_'+num).length==0)
                                aggiungi_notifica_terzo(num);

                            $('#pignorato_'+tipo+'_'+num).val(nome);
                            $('#pignorato_id_'+tipo+'_'+num).val(valorediritorno.p);
                            if(num==0 && $('#modalita_stampa_terzo_'+num+'_0').val()!="pec")
                                $('#spese_not_terzo_'+num).val("<?php echo number_format($para_Spese_Notifica,2,",",""); ?>");
                            $('#denom_terzo_'+num).text(nome);
                            update_notifica_terzi();
                        }
                    });

                }
                else
                {
                    alert("Errore nel caricamento dell'utente! \n\nPer inserire un nuovo utente utilizzare l'Anagrafe\n ");
                    $('#pignorato_'+tipo+'_'+num).val('');
                    $('#pignorato_id_'+tipo+'_'+num).val('0');
                    $('#denom_terzo_'+num).text('');

                    if($('#spese_not_terzo_'+num).length>0)
                    {
                        $('#spese_not_terzo_'+num).val('');
                        $('#scelta_can_cad_terzo_'+num).val('');
                        update_notifica_terzi();
                    }
                }

                break;
        }
}

function reopen(type, value){
    if(type == 'obj')
        top.location.href="../coazione.php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
    else if(type == 'str')
        top.location.href="../gestione_ruolo.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

var selectParent = "";
var selectRif = "";

function RicercheDaId (value, rif)
{
    selectParent = value;
    selectRif = rif;
	var valorediritorno = 0;
	var strDim = Dim_Alert(600, 300);

	switch(value)
	{
		case "utente":
            alert("utente-RicercheDaId-Dim_Alert");
			//strDim = Dim_Alert(800, 400);
			var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			//valorediritorno = window.showModalDialog(stringa,"", strDim);
            openWindowSearch(stringa,{width:800, height:400, left:($(window).width()/2)-400, top:($(window).height()/2)-200});

			break;
	}
}

function stampa_copia_singola(value,num_terzo,num_not)
{
	link = "/gitco2/stampe/stampa_copia_singola.php?tipo_copia="+value+"&num_terzo="+num_terzo+"&num_not="+num_not+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>&pignoramento=<?php echo $pignoramento_ID; ?>";
	window.open(link,"Copia_Pignoramento","width=900,height=600");
}


$(document).ready(function(){

  if('<?= $tipo_pignoramento_get; ?>'!="" && '<?= $id_veicolo_get; ?>'!="")
  {
    $("#tipo_pignoramento").val('<?= $tipo_pignoramento_get; ?>');
    document.getElementById("tipo_pignoramento").dispatchEvent(new Event('change'));
    carica_veicolo("0",'<?= $tipo_pignoramento_get; ?>');
  }

$('#cerca_id').ajaxForm(

	        function(value) {
	            var array_ritorno = value.split(' ');

		if(array_ritorno[0]=='NO')
		{
			alert('Codice partita non trovato!');
			annulla();
		}
		else
		{
			top.location.href = "coazione.php?partita="+array_ritorno[0]+"&c=<?php echo $c; ?>&a="+array_ritorno[1];
		}
	});

  if("<?= $tipo_pignoramento; ?>"=="fermo" || "<?= $tipo_pignoramento; ?>"=="veicolo" || "<?= $tipo_pignoramento; ?>" == "preav_fermo")
  {
    $("#div_lista_veicoli").css("display","block");
  }
  else
  {
    $("#div_lista_veicoli").css("display","none");
  }

/*$('#form_pignoramento').ajaxForm(

	    function(value) {

	        var array_ritorno = value.split(' ');

		if(array_ritorno[0]=='OK')
		{
			alert('Salvataggio effettuato correttamente!');
			top.location.href = "coazione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		}
		else if(array_ritorno[0]=='DELETE')
		{
			alert("Eliminazione effettuata correttamente!");
			top.location.href = "coazione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		}
		else if(array_ritorno[0]=='ERR_DELETE')
		{
			alert("Errore nell'eliminazione del pignoramento! "+value);
		}
		else
		{
			alert("Errore nel salvataggio del pignoramento. "+value);
		}
});*/


});

function visNascDropVeicoli()
{
  if($('#tipo_pignoramento').val()=="fermo" || $('#tipo_pignoramento').val()=="veicolo" || $('#tipo_pignoramento').val()=="preav_fermo")
  {
    $("#div_lista_veicoli").css("display","block");
  }
  else
  {
    $("#div_lista_veicoli").css("display","none");
  }
}

function cambia_title(value)
{
	testo = $('#'+value+ ' option:selected').text();
	$('#'+value).attr('title',testo);
}

function info_sped()
{

	strDim = Dim_Alert(600, 500);
	var stringa = "modali/info_spedizione_pignoramento.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>&pignoramento=<?php echo $pignoramento_ID; ?>";
	valorediritorno = window.showModalDialog(stringa,"", strDim);
}

function mod_rate()
{
	if($('#num_rate').val()!="" && $('#data_richiesta').val() != "" )
	{
		if("<?php echo $Rate_Previste ?>" == "")
		{
			alert("Prima di accedere alla gestione rate salvare il pignoramento.");
		}
		else if( "<?php echo $Rate_Previste ?>" != "" )
		{

			//strDim = Dim_Alert(900, 800);
			var stringa = "modali/gestione_rate_pignoramento.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>&pignoramento=<?php echo $pignoramento_ID; ?>";
			//valorediritorno = window.showModalDialog(stringa,"", strDim);
            openWindowSearch(stringa,{width:1200, height:900, left:($(window).width()/2)-600, top:($(window).height()/2)-450});
		}
	}
	else
	{
		alert("E' necessario selezionare la Rateizzazione e salvare sia il numero di rate sia la data di richiesta rateizzazione prima di poter accedere alla gestione.");
	}

}

function change_num_rate()
{
	if($('#num_rate').val()!="" && $('#data_richiesta').val() != "" && "<?php echo $Rate_Previste ?>" != "")
	{
		if( $('#num_rate').val() != "<?php echo $Rate_Previste ?>" )
		{
			alert('Eliminare la rateizzazione per modificare il numero di rate.');
			$('#num_rate').val("<?php echo $Rate_Previste ?>");
		}
	}
}

function change_data_rate()
{
	if($('#num_rate').val()!="" && $('#data_richiesta').val() != "" && "<?php echo $Rate_Previste ?>" != "")
	{
		if( $('#data_richiesta').val() != "<?php echo $cls_date->Get_DateNewFormat($Data_Richiesta_Rate,"DB"); ?>" )
		{
			alert('Eliminare la rateizzazione per modificare la data di richiesta rateizzazione.');
			$('#data_richiesta').val("<?php echo $cls_date->Get_DateNewFormat($Data_Richiesta_Rate,"DB"); ?>");
		}
	}
}

function rateo()
{
	check = $( "#rate_id:checked" ).attr('name');

	if(check=='rateizza')
	{
		$('#num_rate').prop('disabled',false);
		$('#data_richiesta').prop('disabled',false);
	}
	else
	{
		$('#num_rate').val('').prop('disabled',true);
		$('#data_richiesta').val('').prop('disabled',true);
	}
}

function apri_notifica(link)
{
	link = "apri_notifica.php?link="+link+"";
	window.open(link,"Notifica_AR","width=900,height=600");
}

function apri(link)
{
	window.open(link);
}

function cambio_modalita(notifica, num_not)
{
	if($('#data_stampa').val()!="")
	{
		alert("Attenzione! Si sta modificando la modalita' di invio della notifica!");
	}

	if($('#modalita_stampa_'+notifica+'_'+num_not).val()=="pec")
	{
		if(num_not==0 && $('#data_stampa').val()=="")
			$('#spese_not_'+notifica).val('');

		$('.tr_validato_'+notifica+'_'+num_not).hide();
	}
	else
	{
		if(num_not==0 && $('#data_stampa').val()=="")
			$('#spese_not_'+notifica).val('<?php echo number_format($para_Spese_Notifica,2,",",""); ?>');
		$('.tr_validato_'+notifica+'_'+num_not).show();
	}

	update_notifica_terzi();
}

function aggiungi_notifica(notifica, num_terzo, num_not)
{
	if($('#data_stampa').val()=="")
	{
		alert('Il pignoramento deve essere stampato e notificato prima di aggiungere una nuova notifica!');
		return;
	}
	else if("no" == "<?php echo $control_originale; ?>")
	{
		alert("Vecchia versione del pignoramento! Impossibile creare una nuova notifica.");
		return;
	}

	if(num_not>0)
		ultima_not = (num_not-1);
	else
		ultima_not = null

	if(notifica=="terzo")
	{
		if($('#denom_'+notifica+'_'+num_terzo).text()=="")
		{
			alert('Inserire il terzo da pignorare prima di aggiungere la notifica!');
			return;
		}

		testo_id = notifica+"_"+num_terzo;
	}
	else
		testo_id = notifica;

	if(ultima_not!=null)
	{

		if($('#data_not_'+testo_id+'_'+ultima_not).val()=="")
		{
			if($('#motivo_not_'+testo_id+'_'+ultima_not).val()==null)
			{
				alert("ATTENZIONE! Per inserire una nuova notifica aggiungere la data di notifica o selezionare un'anomalia.");
				return;
			}
		}
		else
		{
			if($('#stato_not_'+testo_id+'_'+ultima_not).val()!=null)
			{
				if($('#ind_validato_'+testo_id+'_'+ultima_not).prop('checked')===false)
				{
					alert("ATTENZIONE! Per inserire una nuova notifica validare l'indirizzo.");
					return;
				}
			}
		}
	}
	else
		return;

	valore = "tr_"+testo_id+"_"+num_not;
		$('.'+valore).show();

}
</script>

 <?php
    include_once(INC."/pages_authorization.php");
 ?>

<form id=form_pignoramento name=form_pignoramento class="form-horizontal validate" action="pignoramento_salva.php" method=post>
<input name=invia_submit  id=invia_submit	type=hidden	value="<?php echo $control_submit; ?>" >

<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
<input type=hidden name=partita_ID value="<?php echo $partita_ID; ?>" >
<input type=hidden name=pignoramento_ID value="<?php echo $pignoramento_ID; ?>"  id=pignoramento_ID>
<input type=hidden name=atto_ID value="<?php echo $Atto_ID; ?>" >

<!-- TABELLA DATI PIGNORAMENTO (PAGINA 1)-->

<div class="pignoramento_1">
  <div class="row justify-content-md-center ">
  	<div class="col col-md-auto text_center">
  			<a href="#" onclick="pagina_pignoramento(5);" tabindex=2><img title="Pagina precedente" src="<?= IMMAGINIWEB; ?>/prev.png" style="width:20px; height:14px; border:0;"></a> <span class="titolo font14">Dati pignoramento 1/5</span> <a href="#" onclick="pagina_pignoramento(2);" tabindex=2><img title="Pagina successiva" src="<?= IMMAGINIWEB; ?>/next.png" style="width:20px; height:14px; border:0;"></a>
  	</div>
  </div>

  <div class="row" style="margin-top: 1%;">
    <div class="col col-lg-5 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo procedimento</label>
        <div class="col-lg-8">
           <select name=tipo_pignoramento id=tipo_pignoramento class="form-control resize" onchange="scelta_pignoramento();set_options();visNascDropVeicoli();">
                <?php echo $optTipoPigno; ?>
           </select>
        </div>
      </div>
    </div>
    <div class="col col-lg-5">
      <div class="form-group" id="div_lista_veicoli" style="display: none;">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Veicoli</label>
        <div class="col-lg-8">
           <select name=lista_veicoli id=lista_veicoli class="form-control resize">
     				<?php echo $optionVeicoli; ?>
     			</select>
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_terzi">
    <div class="col col-lg-5 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Presso</label>
        <div class="col-lg-8">
          <select name=presso_terzi id=presso_terzi class="form-control resize" onchange="scelta_terzi();set_options();">
  					<option></option>
  					<option value="lavoro"	>Datore di lavoro</option>
  					<option value="banca"	>Banca / Posta</option>
  					<option value="inps"	>Istituti previdenziali</option>
  					<option value="altro"	>Altri terzi</option>
  				</select>
        </div>
      </div>
    </div>
    <div class="col col-lg-5 tr_banca" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Ric. banche</label>
        <div class="col-lg-8">
          <input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=ricerca_banche id=ricerca_banche value="<?php echo $comune_ric_banche; ?>" ondblclick="func_ricerca_banche();">
          <input type=hidden name=cap_banche id=cap_banche value="<?php echo $cap_banche; ?>" >
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_lavoro" id="tr_lavoro_iniziale" style="margin-top: 2%;">
    <div class="col col-lg-2 col-lg-offset-1" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Presso</label>-->
        <div class="col-lg-12">
          <input type=button onclick="aggiungi_terzo('lavoro');" class="btn btn-primary form-control resize" value="Nuovo Terzo">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Ric. banche</label>-->
        <div class="col-lg-12">
          <p class="resize" >Clicca per poter inserire un nuovo terzo da pignorare</p>
        </div>
      </div>
    </div>
  </div>

  <?php //class=tr_lavoro
  for($i=0;$i<$count_terzi;$i++)
  {
  ?>
<!-- DA QUI  CLASS tr_lavoro -->
<div class="tr_lavoro lavoro_<?php echo $i; ?>">
  <input type=hidden name="pignorato_id_lavoro_<?php echo $i; ?>" id="pignorato_id_lavoro_<?php echo $i; ?>"	value="0" 	>
</div>

<div class='tr_lavoro lavoro_<?php echo $i; ?>' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> " style="margin-top: 2%;">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Terzo</label>
      <div class="col-lg-8">
        <input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=pignorato_lavoro_<?php echo $i; ?> id=pignorato_lavoro_<?php echo $i; ?> value="<?php echo isset($nome_cognome_lavoro[$i])?$nome_cognome_lavoro[$i]:null; ?>" ondblclick="carica_utente( <?php echo $i; ?> , 'lavoro');">
      </div>
    </div>
  </div>
  <div class="col col-lg-1" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Ric. banche</label>-->
      <div class="col-lg-12">
        <a onMouseover="title='Elimina terzo'" href='#' style='text-decoration:none;' onClick="elimina_terzo('lavoro',<?php echo $i; ?>);" >
				<i class="fa fa-trash" style="color: red;" aria-hidden="true"></i>
				</a>
      </div>
    </div>
  </div>
</div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> ">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Azienda</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=azienda_lavoro_<?php echo $i; ?> id=azienda_lavoro_<?php echo $i; ?> value="<?php echo isset($azienda_lavoro[$i])?$azienda_lavoro[$i]:null; ?>" >
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=fonte_lavoro_<?php echo $i; ?> id=fonte_lavoro_<?php echo $i; ?> value="<?php echo isset($Fonte_Dati_lavoro[$i])?$Fonte_Dati_lavoro[$i]:null; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> ">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo contratto</label>
      <div class="col-lg-8">
        <select name=tipo_contratto_<?php echo $i; ?> id="tipo_contratto_<?php echo $i; ?>" class="form-control resize" onchange="scelta_contratto(<?php echo $i; ?>);">
					<option></option>
					<option value="titolare"		>Titolare</option>
					<option value="accessorio"		>Accessorio</option>
					<option value="apprendistato"	>Apprendistato</option>
					<option value="chiamata"		>Chiamata</option>
					<option value="collaborazione"	>Collaborazione</option>
					<option value="determinato"		>Determinato</option>
					<option value="indeterminato"	>Indeterminato</option>
					<option value="inserimento"		>Inserimento</option>
					<option value="interinale"		>Interinale</option>
					<option value="occasionale"		>Occasionale</option>
					<option value="progetto"		>Progetto</option>
					<option value="ripartito"		>Ripartito</option>
					<option value="somministrazione">Somministrazione</option>
					<option value="parziale"		>Tempo parziale</option>
					<option value="altro"			>Altro</option>
				</select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Note</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=note_lavoro_<?php echo $i; ?> id=note_lavoro_<?php echo $i; ?> value="<?php echo isset($Note_Terzi_lavoro[$i])?$Note_Terzi_lavoro[$i]:null; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> " id='tr_lavoro_finale_<?php echo $i; ?>'>
  <div class="col col-lg-4 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Data costituz. ditta</label>
      <div class="col-lg-8">
        <input class="picker text_center form-control resize validateCustom vld_Custom_date" style="width: 50%;" name=data_costituzione_<?php echo $i; ?> id=data_costituzione_<?php echo $i; ?> value="<?php echo $Data_Costituzione_Ditta_Lavoro[$i]; ?>" size=10>
      </div>
    </div>
  </div>
  <div class="col col-lg-3" >
    <div class="form-group">
      <label class="col-lg-6 control-label resize " style="text-align: left;">Data ditta operativa</label>
      <div class="col-lg-6">
        <input class="picker text_center form-control resize validateCustom vld_Custom_date" name=data_operativa_<?php echo $i; ?> id=data_operativa_<?php echo $i; ?> value="<?php echo $Data_Ditta_Operativa_Lavoro[$i]; ?>" size=10>
      </div>
    </div>
  </div>
  <div class="col col-lg-3" >
    <div class="form-group">
      <label class="col-lg-6 control-label resize " style="text-align: left;">Data dipendenze</label>
      <div class="col-lg-6">
        <input class="picker text_center form-control resize validateCustom vld_Custom_date" name=data_dipendenze_<?php echo $i; ?> id=data_dipendenze_<?php echo $i; ?> value="<?php echo $Data_Dipendenze_Lavoro[$i]; ?>" size=10>
      </div>
    </div>
  </div>
</div>

<?php
}
?>

<div class="row tr_banca" id="tr_banca_iniziale" style="margin-top: 2%;">
  <div class="col col-lg-2 col-lg-offset-1" >
    <div class="form-group">
      <div class="col-lg-12">
        <input type=button onclick="aggiungi_terzo('banca');" class="btn btn-primary form-control resize" value="Nuovo Terzo">
      </div>
    </div>
  </div>
  <div class="col col-lg-3" >
    <div class="form-group">
      <div class="col-lg-12">
        <p class="">Clicca per poter inserire un nuovo terzo da pignorare</p>
      </div>
    </div>
  </div>
</div>


<?php
// da qui class=tr_banca
for($i=0;$i<$count_terzi;$i++)
{
?>

<div class="tr_banca banca_<?php echo $i; ?>">
  <input type=hidden name="pignorato_id_banca_<?php echo $i; ?>"  id="pignorato_id_banca_<?php echo $i; ?>" value="0">
</div>

<div class='tr_banca banca_<?php echo $i; ?>' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>

<div class="row banca_<?php echo $i; ?> tr_banca" style="margin-top: 2%;">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 resize control-label" style="text-align: left;">Terzo</label>
      <div class="col-lg-6">
        <input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=pignorato_banca_<?php echo $i; ?> id=pignorato_banca_<?php echo $i; ?> value="<?php echo $nome_cognome_banca[$i]; ?>" ondblclick="carica_banca(<?php echo $i; ?>);">
      </div>
      <div class="col-lg-2">
        <a onMouseover="title='Elimina terzo'" href='#' style='text-decoration:none;' onClick="elimina_terzo('lavoro',<?php echo $i; ?>);" >
			      <i class="fa fa-trash" style="color: red;" aria-hidden="true"></i>
				</a>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=fonte_banca_<?php echo $i; ?> id=fonte_banca_<?php echo $i; ?> value="<?php echo $Fonte_Dati_banca[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row banca_<?php echo $i; ?> tr_banca">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo titolo</label>
      <div class="col-lg-8">
        <select name=tipo_titolo_<?php echo $i; ?> id=tipo_titolo_<?php echo $i; ?> class="form-control resize">
          <option></option>
          <option value="conto"		>Conto corrente</option>
          <option value="libretto"	>Libretto</option>
          <option value="altro"		>Altro</option>
        </select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Note</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=note_banca_<?php echo $i; ?> id=note_banca_<?php echo $i; ?> value="<?php echo $Note_Terzi_banca[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row banca_<?php echo $i; ?> tr_banca">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Titolo</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=titolo_<?php echo $i; ?> id=titolo_<?php echo $i; ?> value="<?php echo $Titolo_Banca[$i]; ?>">
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Intestatario</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=intestatario_<?php echo $i; ?> id=intestatario_<?php echo $i; ?> value="<?php echo $Intestatario_Banca[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row banca_<?php echo $i; ?> tr_banca" id='tr_banca_finale_<?php echo $i; ?>'>
  <div class="col col-lg-10 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-2 control-label resize " style="text-align: left;">Coointestatari (*)</label>
      <div class="col-lg-10">
        <input class="form-control resize" name=coointestatari_<?php echo $i; ?> id=coointestatari_<?php echo $i; ?> value="<?php echo $Coointestatari_Banca[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<?php
}
?>
	                               <!-- INPS -->
<?php
for($i=0;$i<3;$i++)
{
?>

<input type=hidden name="pignorato_id_inps_<?php echo $i; ?>"  id="pignorato_id_inps_<?php echo $i; ?>"	value="0" 	>

<div class='tr_inps' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>

<div class="row tr_inps">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Terzo</label>
      <div class="col-lg-8">
        <input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=pignorato_inps_<?php echo $i; ?> id=pignorato_inps_<?php echo $i; ?> value="<?php echo $nome_cognome_inps[$i]; ?>" ondblclick="carica_utente( <?php echo $i; ?> , 'inps');">
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=fonte_inps_<?php echo $i; ?> id=fonte_inps_<?php echo $i; ?> value="<?php echo $Fonte_Dati_inps[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_inps">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Categoria pensione</label>
      <div class="col-lg-8">
        <select name=tipo_libretto_<?php echo $i; ?> id=tipo_libretto_<?php echo $i; ?> class="form-control resize">
          <option></option>
          <option value="anticipata"		>Anticipata</option>
          <option value="anzianita"		>Anzianita'</option>
          <option value="assegno"			>Assegno sociale</option>
          <option value="inabilita"		>Inabilita'</option>
          <option value="invalidita"		>Invalidita'</option>
          <option value="inv_civile"		>Invalidita' civile</option>
          <option value="vecchiaia"		>Vecchiaia</option>
          <option value="privilegiata"	>Privilegiata</option>
          <option value="sociale"			>Sociale</option>
          <option value="reversibilita"	>Reversibilita'</option>
          <option value="superstiti"		>Superstiti</option>
          <option value="supplementare"	>Supplementare</option>
          <option value="inpdap"			>INPDAP</option>
          <option value="altro"			>Altro</option>
        </select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Note</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=note_inps_<?php echo $i; ?> id=note_inps_<?php echo $i; ?> value="<?php echo $Note_Terzi_inps[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_inps">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Libretto pensione</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=libretto_<?php echo $i; ?> id=libretto_<?php echo $i; ?> value="<?php echo $Libretto_Inps[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<?php
}
?>

          	<!-- ALTRO -->
<?php
for($i=0;$i<3;$i++)
{
?>

<input type=hidden name="pignorato_id_altro_<?php echo $i; ?>"  id="pignorato_id_altro_<?php echo $i; ?>"	value="0" 	>

<div class='tr_altro' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>

<div class="row tr_altro">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Terzo</label>
      <div class="col-lg-8">
        <input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=pignorato_altro_<?php echo $i; ?> id=pignorato_altro_<?php echo $i; ?> value="<?php echo $nome_cognome_altro[$i]; ?>" ondblclick="carica_utente( <?php echo $i; ?> , 'altro');">
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=fonte_altro_<?php echo $i; ?> id=fonte_altro_<?php echo $i; ?> value="<?php echo $Fonte_Dati_altro[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_altro">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo credito</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=tipo_credito_<?php echo $i; ?> id=tipo_credito_<?php echo $i; ?> value="<?php echo $Tipo_Credito_Altro[$i]; ?>">
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Note</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=note_altro_<?php echo $i; ?> id=note_altro_<?php echo $i; ?> value="<?php echo $Note_Terzi_altro[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_altro">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo titolo</label>
      <div class="col-lg-8">
        <select name=tipo_titolo_credito_<?php echo $i; ?> id=tipo_titolo_credito_<?php echo $i; ?> class="form-control resize">
          <option></option>
          <option value="cambiale"		>Cambiale</option>
          <option value="ingiunzione"		>Decreto ingiuntivo</option>
          <option value="protesto"		>Protesto</option>
          <option value="altro"			>Altro</option>
        </select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Titolo</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=titolo_credito_<?php echo $i; ?> id=titolo_credito_<?php echo $i; ?> value="<?php echo $Titolo_Altro[$i]; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_altro">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Data emissione</label>
      <div class="col-lg-8">
        <input class="picker form-control resize text_center validateCustom vld_Custom_date" style="width: 40%;" name=data_emissione_<?php echo $i; ?> id=data_emissione_<?php echo $i; ?> value="<?php echo $Data_Emissione_Altro[$i]; ?>" size=10>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Data scadenza</label>
      <div class="col-lg-8">
        <input class="picker form-control resize text_center validateCustom vld_Custom_date" style="width: 40%;" name=data_scadenza_<?php echo $i; ?> id=data_scadenza_<?php echo $i; ?> value="<?php echo $Data_Scadenza_Altro[$i]; ?>" size=10>
      </div>
    </div>
  </div>
</div>

<?php
}
?>

<div class="row justify-content-md-center tr_banca">
  <div class="col col-md-auto text_center">
      <p style="color: red;">(*) ATTENZIONE : Separare gli eventuali coointestatari con un * ES. ( Mario Rossi*Fabio Bianchi )</p>
  </div>
</div>


                            <!-- ******* -->
                            <!-- VEICOLO -->
                            <!-- ******* -->
  <?php
  for($i=0;$i<3;$i++)
  {
  ?>
  <input type="hidden" name=id_veicolo_veicolo_<?php echo $i; ?> id=id_veicolo_veicolo_<?php echo $i; ?> />
  <div class="tr_veicolo" style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

  <div class="row tr_veicolo" style="margin-top: 2%;">
    <div class="col col-lg-2 col-lg-offset-1" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Terzo</label>-->
        <div class="col-lg-8">
          <input type=button value="Seleziona veicolo" class="btn btn-primary resize" onclick="carica_veicolo(<?php echo $i; ?>,'veicolo');">
        </div>
      </div>
    </div>
    <div class="col col-lg-5" >
      <div class="form-group">
        <label class="col-lg-8 control-label resize " style="text-align: left;">Data iscrizione fermo/pignoramento</label>
        <div class="col-lg-4">
          <input class="text_center picker form-control resize validateCustom vld_Custom_date" name=data_fermo_veicolo_<?php echo $i; ?> id=data_fermo_veicolo_<?php echo $i; ?> value="<?php echo $data_fermo_veicolo[$i]; ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_veicolo">
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo bene</label>
        <div class="col-lg-8">
          <select class="form-control resize" name=tipo_veicolo_<?php echo $i; ?> id=tipo_veicolo_<?php echo $i; ?>>
    				<optgroup label="Veicolo">
    					<option value=autoveicolo	>Autoveicolo</option>
    					<option value=motoveicolo	>Motoveicolo</option>
    					<option value=ciclomotore	>Ciclomotore</option>
    					<option value=rimorchio		>Rimorchio</option>
    				</optgroup>
    				<optgroup label="Barca">
    					<option value=barca_motore	>Barca a motore</option>
    				</optgroup>
    				<optgroup label="Aeromobile">
    					<option value=aeromobile	>Aeromobile</option>
    				</optgroup>
    			</select>
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Targa</label>
        <div class="col-lg-8">
          <input class="form-control resize" name=targa_veicolo_<?php echo $i; ?> id=targa_veicolo_<?php echo $i; ?> value="<?php echo $targa_veicolo[$i]; ?>">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Data visura</label>
        <div class="col-lg-8">
          <input class="text_center picker form-control resize validateCustom vld_Custom_date" name=data_visura_veicolo_<?php echo $i; ?> id=data_visura_veicolo_<?php echo $i; ?> value="<?php echo $data_visura_veicolo[$i]; ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_veicolo">
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Marca</label>
        <div class="col-lg-8">
          <input class="form-control resize" name=marca_veicolo_<?php echo $i; ?> id=marca_veicolo_<?php echo $i; ?> value="<?php echo $marca_veicolo[$i]; ?>">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Modello</label>
        <div class="col-lg-8">
          <input class="form-control resize" name=modello_veicolo_<?php echo $i; ?> id=modello_veicolo_<?php echo $i; ?> value="<?php echo $modello_veicolo[$i]; ?>">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
        <div class="col-lg-8">
          <select class="form-control resize" name=fonte_dati_veicolo_<?php echo $i; ?> id=fonte_dati_veicolo_<?php echo $i; ?>>
    					<option></option>
    					<option value=mctc	>MCTC</option>
    					<option value=pra	>ACI / PRA</option>
    			</select>
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_veicolo">
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Telaio</label>
        <div class="col-lg-8">
          <input class="form-control resize" name=telaio_veicolo_<?php echo $i; ?> id=telaio_veicolo_<?php echo $i; ?> value="<?php echo $telaio_veicolo[$i]; ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_veicolo">
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Portata</label>
        <div class="col-lg-6">
          <input class="form-control resize validateCustom vld_Custom_d" size="7" name=portata_veicolo_<?php echo $i; ?> id=portata_veicolo_<?php echo $i; ?> placeholder="Quintali" value="<?php echo $portata_veicolo[$i]; ?>">
        </div>
        <label class="col-lg-2 control-label resize" style="text-align: left;">q.li</label>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Valore</label>
        <div class="col-lg-6">
          <input class="form-control resize validateCustom vld_Custom_d" size="7" name=valore_veicolo_<?php echo $i; ?> id=valore_veicolo_<?php echo $i; ?> value="<?php echo $valore_veicolo[$i]; ?>">
        </div>
        <label class="control-label col-lg-2 resize" style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-8 control-label resize " style="text-align: left;">Anno immatricolazione</label>
        <div class="col-lg-4">
          <input class="form-control resize validateCustom vld_Custom_anno" size=5 name=anno_immatricolazione_veicolo_<?php echo $i; ?> id=anno_immatricolazione_veicolo_<?php echo $i; ?> placeholder="Anno" value="<?php echo $anno_immatricolazione_veicolo[$i]; ?>">
        </div>
      </div>
    </div>
  </div>

  <?php
  }
  ?>

  <!-- ******* 			-->
  <!-- PREAVVISO FERMO   	-->
  <!-- ******* 			-->


  <?php
  for($i=0;$i<3;$i++)
  {
  ?>
<input type="hidden" name=id_veicolo_preav_fermo_<?php echo $i; ?> id=id_veicolo_preav_fermo_<?php echo $i; ?> />
  <div class="tr_preav_fermo" style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 2%;margin-bottom:2%;"></div>

      <div class="row tr_preav_fermo" style="margin-top: 2%;">
          <div class="col col-lg-2 col-lg-offset-1" >
              <div class="form-group">
                  <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Terzo</label>-->
                  <div class="col-lg-8">
                      <input type=button value="Seleziona veicolo" class="btn btn-primary resize" onclick="carica_veicolo(<?php echo $i; ?>,'preav_fermo');">
                  </div>
              </div>
          </div>
      </div>

  <div class="row tr_preav_fermo" >
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo bene</label>
        <div class="col-lg-8">
          <select class="form-control resize" name=tipo_preav_fermo_<?php echo $i; ?> id=tipo_preav_fermo_<?php echo $i; ?>>
    				<optgroup label="Veicolo">
    					<option value=autoveicolo	>Autoveicolo</option>
    					<option value=motoveicolo	>Motoveicolo</option>
    					<option value=ciclomotore	>Ciclomotore</option>
    					<option value=rimorchio		>Rimorchio</option>
    				</optgroup>
    				<optgroup label="Barca">
    					<option value=barca_motore	>Barca a motore</option>
    				</optgroup>
    				<optgroup label="Aeromobile">
    					<option value=aeromobile	>Aeromobile</option>
    				</optgroup>
    			</select>
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Targa</label>
        <div class="col-lg-8">
          <input class="text_left form-control resize" name=targa_preav_fermo_<?php echo $i; ?> id=targa_preav_fermo_<?php echo $i; ?> value="<?php echo $targa_preav_fermo[$i]; ?>">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Data visura</label>
        <div class="col-lg-8">
          <input class="text_center picker form-control resize validateCustom vld_Custom_date" name=data_visura_preav_fermo_<?php echo $i; ?> id=data_visura_preav_fermo_<?php echo $i; ?> value="<?php echo $data_visura_preav_fermo[$i]; ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_preav_fermo">
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Marca</label>
        <div class="col-lg-8">
          <input class="form-control resize" name=marca_preav_fermo_<?php echo $i; ?> id=marca_preav_fermo_<?php echo $i; ?> value="<?php echo $marca_preav_fermo[$i]; ?>">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Modello</label>
        <div class="col-lg-8">
          <input class="text_left form-control resize" name=modello_preav_fermo_<?php echo $i; ?> id=modello_preav_fermo_<?php echo $i; ?> value="<?php echo $modello_preav_fermo[$i]; ?>">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
        <div class="col-lg-8">
          <select class="form-control resize" name=fonte_dati_preav_fermo_<?php echo $i; ?> id=fonte_dati_preav_fermo_<?php echo $i; ?>>
    					<option></option>
    					<option value=mctc	>MCTC</option>
    					<option value=pra	>PRA</option>
    			</select>
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_preav_fermo">
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Portata</label>
        <div class="col-lg-6">
          <input class="form-control resize validateCustom vld_Custom_d" size="7" name=portata_preav_fermo_<?php echo $i; ?> id=portata_preav_fermo_<?php echo $i; ?> placeholder="Quintali" value="<?php echo $portata_preav_fermo[$i]; ?>">
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">q.li</label>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Valore</label>
        <div class="col-lg-6">
          <input class="form-control resize validateCustom vld_Custom_d" size="7" name=valore_preav_fermo_<?php echo $i; ?> id=valore_preav_fermo_<?php echo $i; ?> value="<?php echo $valore_preav_fermo[$i]; ?>">
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Anno immatricolazione</label>
        <div class="col-lg-8">
          <input class="form-control resize validateCustom vld_Custom_anno" size=5 name=anno_immatricolazione_preav_fermo_<?php echo $i; ?> id=anno_immatricolazione_preav_fermo_<?php echo $i; ?> placeholder="Anno" value="<?php echo $anno_immatricolazione_preav_fermo[$i]; ?>">
        </div>
      </div>
    </div>
  </div>
  <?php
  }
   ?>

                                   <!-- ******* -->
                                   <!-- FERMO   -->
                                   <!-- ******* -->

   <?php
   //class tr_fermo
   for($i=0;$i<3;$i++)
   {
   ?>
   <input type="hidden" name=id_veicolo_fermo_<?php echo $i; ?> id=id_veicolo_fermo_<?php echo $i; ?> />
     <div class="tr_fermo" style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 2%;margin-bottom:2%;"></div>

    <div class="row tr_fermo" style="margin-top: 2%;">
        <div class="col col-lg-2 col-lg-offset-1" >
            <div class="form-group">
                <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Terzo</label>-->
                <div class="col-lg-8">
                    <input type=button value="Seleziona veicolo" class="btn btn-primary resize" onclick="carica_veicolo(<?php echo $i; ?>,'fermo');">
                </div>
            </div>
        </div>
    </div>

   <div class="row tr_fermo">
     <div class="col col-lg-4 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo bene</label>
         <div class="col-lg-8">
           <select class="form-control resize" name=tipo_fermo_<?php echo $i; ?> id=tipo_fermo_<?php echo $i; ?>>
     				<optgroup label="Veicolo">
     					<option value=autoveicolo	>Autoveicolo</option>
     					<option value=motoveicolo	>Motoveicolo</option>
     					<option value=ciclomotore	>Ciclomotore</option>
     					<option value=rimorchio		>Rimorchio</option>
     				</optgroup>
     				<optgroup label="Barca">
     					<option value=barca_motore	>Barca a motore</option>
     				</optgroup>
     				<optgroup label="Aeromobile">
     					<option value=aeromobile	>Aeromobile</option>
     				</optgroup>
     			</select>
         </div>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Targa</label>
         <div class="col-lg-8">
           <input class="form-control resize" name=targa_fermo_<?php echo $i; ?> id=targa_fermo_<?php echo $i; ?> value="<?php echo $targa_fermo[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Data visura</label>
         <div class="col-lg-8">
           <input class="text_center picker form-control resize validateCustom vld_Custom_date" name=data_visura_fermo_<?php echo $i; ?> id=data_visura_fermo_<?php echo $i; ?> value="<?php echo $data_visura_fermo[$i]; ?>">
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_fermo">
     <div class="col col-lg-4 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Marca</label>
         <div class="col-lg-8">
          <input class="form-control resize" name=marca_fermo_<?php echo $i; ?> id=marca_fermo_<?php echo $i; ?> value="<?php echo $marca_fermo[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Modello</label>
         <div class="col-lg-8">
           <input class="form-control resize" name=modello_fermo_<?php echo $i; ?> id=modello_fermo_<?php echo $i; ?> value="<?php echo $modello_fermo[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
         <div class="col-lg-8">
           <select class="form-control resize" name=fonte_dati_fermo_<?php echo $i; ?> id=fonte_dati_fermo_<?php echo $i; ?>>
     					<option></option>
     					<option value=mctc >MCTC</option>
     					<option value=pra	>PRA</option>
     			</select>
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_fermo">
     <div class="col col-lg-4 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Portata</label>
         <div class="col-lg-6">
           <input class="form-control resize validateCustom vld_Custom_d" size="7" name=portata_fermo_<?php echo $i; ?> id=portata_fermo_<?php echo $i; ?> placeholder="Quintali" value="<?php echo $portata_fermo[$i]; ?>">
         </div>
         <label class="col-lg-2 control-label resize " style="text-align: left;">q.li</label>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Valore</label>
         <div class="col-lg-6">
           <input class="form-control resize validateCustom vld_Custom_d" size="7" name=valore_fermo_<?php echo $i; ?> id=valore_fermo_<?php echo $i; ?> value="<?php echo $valore_fermo[$i]; ?>">
         </div>
         <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-8 control-label resize " style="text-align: left;">Anno immatricolazione</label>
         <div class="col-lg-4">
           <input class="form-control resize validateCustom vld_Custom_anno" size=5 name=anno_immatricolazione_fermo_<?php echo $i; ?> id=anno_immatricolazione_fermo_<?php echo $i; ?> placeholder="Anno" value="<?php echo $anno_immatricolazione_fermo[$i]; ?>">
         </div>
       </div>
     </div>
   </div>

   <?php
    }
   ?>


   <?php
   for($i=0;$i<3;$i++)
   {
   ?>
   <div class="tr_immobiliare" style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 2%;"></div>

   <div class="row tr_immobiliare" style="margin-top: 2%;">
     <div class="col col-lg-3 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-6 control-label resize " style="text-align: left;">Tipo</label>
         <div class="col-lg-6">
           <select class="form-control resize" name=tipo_immobiliare_<?php echo $i; ?> id=tipo_immobiliare_<?php echo $i; ?> onchange="scelta_immobile(<?php echo $i; ?>);">
             <option></option>
             <option value=fabbricato	>Fabbricato</option>
             <option value=terreno		>Terreno</option>
           </select>
         </div>
       </div>
     </div>
     <div class="col col-lg-7" >
       <div class="col-lg-3">
         <div class="form-group">
           <label class="col-lg-4 control-label resize " style="text-align: left; font-size: 12px;">Situazione</label>
           <div class="col-lg-8">
             	<input class="text_right form-control resize validateCustom vld_Custom_n" name=situazione_immobiliare_<?php echo $i; ?> id=situazione_immobiliare_<?php echo $i; ?> value="<?php echo $Situazione_Immobiliare[$i]; ?>">
           </div>
         </div>
       </div>
       <div class="col-lg-3">
         <div class="form-group">
           <label class="col-lg-4 control-label resize " style="text-align: left; font-size: 12px;">Foglio</label>
           <div class="col-lg-8">
             <input class="form-control resize validateCustom vld_Custom_n" name=foglio_immobiliare_<?php echo $i; ?> id=foglio_immobiliare_<?php echo $i; ?> value="<?php echo $Foglio_Immobiliare[$i]; ?>">
           </div>
         </div>
       </div>
       <div class="col-lg-3">
         <div class="form-group">
           <label class="col-lg-4 control-label resize " style="text-align: left; font-size: 12px;">Particella</label>
           <div class="col-lg-8">
             <input class="form-control resize validateCustom vld_Custom_n" name=particella_immobiliare_<?php echo $i; ?> id=particella_immobiliare_<?php echo $i; ?> value="<?php echo $Particella_Immobiliare[$i]; ?>">
           </div>
         </div>
       </div>
       <div class="col-lg-3">
         <div class="form-group">
           <label class="col-lg-4 control-label resize " style="text-align: left; font-size: 12px;">Subalterno</label>
           <div class="col-lg-8">
             <input class="form-control resize validateCustom vld_Custom_n" name=subalterno_immobiliare_<?php echo $i; ?> id=subalterno_immobiliare_<?php echo $i; ?> value="<?php echo $Subalterno_Immobiliare[$i]; ?>">
           </div>
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_immobiliare">
     <div class="col col-lg-10 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-2 control-label resize " style="text-align: left;">Annotazioni</label>
         <div class="col-lg-10">
           <input class="form-control resize" name=annotazioni_immobiliare_<?php echo $i; ?> id=annotazioni_immobiliare_<?php echo $i; ?> value="<?php echo $Annotazioni_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_immobiliare">
     <div class="col col-lg-6 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-3 control-label resize " style="text-align: left;">Possesso intestatario</label>
         <div class="col-lg-3">
           <input class="form-control resize validateCustom vld_Custom_n" name=parte_proprietario_<?php echo $i; ?> id=parte_proprietario_<?php echo $i; ?> value="<?php echo $Parte_Proprietario_Immobiliare[$i]; ?>">
         </div>
         <label class="col-lg-1 control-label resize" style="text-align: center;" >/</label>
         <div class="col-lg-3">
           <input class="form-control resize validateCustom vld_Custom_n" name=totale_proprietario_<?php echo $i; ?> id=totale_proprietario_<?php echo $i; ?> value="<?php echo $Totale_Proprietario_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
   </div>

   <div class="tr_immobiliare" style="border-top: 2px solid #B0BBE8; width: 30%; margin-left: 35%; margin-top: 1%; margin-bottom: 1%;"></div>

   <div class="row tr_immobiliare tr_fabbricato_<?php echo $i; ?>">
     <div class="col col-lg-2 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-5 control-label resize " style="text-align: left;">Sezione</label>
         <div class="col-lg-7">
           <input class="form-control resize" size="4" name=sezione_fabbricato_<?php echo $i; ?> id=sezione_fabbricato_<?php echo $i; ?> value="<?php echo $Sezione_Fabbricato_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-5 control-label resize " style="text-align: left;">Zona censura</label>
         <div class="col-lg-7">
           <input class="form-control resize" size="5" name=zona_censuaria_fabbricato_<?php echo $i; ?> id=zona_censuaria_fabbricato_<?php echo $i; ?> value="<?php echo $Zona_Censuaria_Fabbricato_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-5 control-label resize " style="text-align: left;">Categoria</label>
         <div class="col-lg-7">
           <select class="form-control resize" name=categoria_fabbricato_<?php echo $i; ?> id=categoria_fabbricato_<?php echo $i; ?> >
     				<option></option>
     				<optgroup label="Immobili a destinazione ordinaria">
     					<option>A/1</option><option>A/2</option><option>A/3</option><option>A/4</option><option>A/5</option>
     					<option>A/6</option><option>A/7</option><option>A/8</option><option>A/9</option><option>A/10</option>
     					<option>A/11</option>

     					<option>B/1</option><option>B/2</option><option>B/3</option><option>B/4</option><option>B/5</option>
     					<option>B/6</option><option>B/7</option><option>B/8</option>

     					<option>C/1</option><option>C/2</option><option>C/3</option><option>C/4</option><option>C/5</option>
     					<option>C/6</option><option>C/7</option>
     				</optgroup>
     				<optgroup label="Immobili a destinazione speciale">
     					<option>D/1</option><option>D/2</option><option>D/3</option><option>D/4</option><option>D/5</option>
     					<option>D/6</option><option>D/7</option><option>D/8</option><option>D/9</option><option>D/10</option>
     				</optgroup>
     				<optgroup label="Immobili a destinazione particolare">
     					<option>E/1</option><option>E/2</option><option>E/3</option><option>E/4</option><option>E/5</option>
     					<option>E/6</option><option>E/7</option><option>E/8</option><option>E/9</option>
     				</optgroup>
     				<optgroup label="Entita' urbane">
     					<option>F/1</option><option>F/2</option><option>F/3</option><option>F/4</option><option>F/5</option>
     					<option>F/6</option>
     				</optgroup>
     			</select>
         </div>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-5 control-label resize " style="text-align: left;">Classe</label>
         <div class="col-lg-7">
           <input class="form-control resize" size="3" name=classe_fabbricato_<?php echo $i; ?> id=classe_fabbricato_<?php echo $i; ?> value="<?php echo $Classe_Immobiliare_Fabbricato[$i]; ?>">
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_immobiliare tr_fabbricato_<?php echo $i; ?>" >
     <div class="col col-lg-2 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-5 control-label resize " style="text-align: left;">Consistenza</label>
         <div class="col-lg-7">
           <input class="form-control resize vld_dec" size="4" name=consistenza_fabbricato_<?php echo $i; ?> id=consistenza_fabbricato_<?php echo $i; ?> value="<?php echo $Consistenza_Fabbricato_Immobiliare[$i] ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Superficie</label>
         <div class="col-lg-6" style="margin-right: 0; padding-right: 0;">
           <input class="form-control resize validateCustom vld_Custom_d" size="5" name=superficie_fabbricato_<?php echo $i; ?> id=superficie_fabbricato_<?php echo $i; ?> value="<?php echo $Superficie_Fabbricato_Immobiliare[$i] ?>">
         </div>
         <label class="col-lg-2 control-label resize vld_dec" style="text-align: left;margin-left: 0; padding-left: 0;">&nbsp;mq</label>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize" style="text-align: left;">Rendita</label>
         <div class="col-lg-6" style="margin-right: 0; padding-right: 0;">
           <input class="form-control resize validateCustom vld_Custom_d" size="6" name=rendita_fabbricato_<?php echo $i; ?> id=rendita_fabbricato_<?php echo $i; ?> value="<?php echo $Rendita_Fabbricato_Immobiliare[$i]; ?>">
         </div>
         <label class="col-lg-2 control-label resize vld_dec" style="text-align: left; margin-left: 0; padding-left: 0;">&nbsp;&euro;</label>
       </div>
     </div>
     <div class="col col-lg-3" >
       <div class="form-group">
         <label class="col-lg-5 control-label resize " style="text-align: left;">Prot. notifica</label>
         <div class="col-lg-7">
           <input class="form-control resize" size=3 name=protocollo_notifica_fabbricato_<?php echo $i; ?> id=protocollo_notifica_fabbricato_<?php echo $i; ?> value="<?php echo $Protocollo_Notifica_Fabbricato_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_immobiliare tr_fabbricato_<?php echo $i; ?>">
     <div class="col col-lg-10 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-2 control-label resize " style="text-align: left;">Indirizzo fabbricato</label>
         <div class="col-lg-10">
           <input id=via_<?= $i; ?> class="form-control resize" name=indirizzo_fabbricato_<?php echo $i; ?> id=indirizzo_fabbricato_<?php echo $i; ?> type=text value="<?php echo $Indirizzo_Fabbricato_Immobiliare[$i]; ?>" >
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_immobiliare tr_terreno_<?php echo $i; ?>" >
     <div class="col col-lg-2 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Porzione</label>
         <div class="col-lg-8">
           <input class="form-control resize" size="3" name=porzione_terreno_<?php echo $i; ?> id=porzione_terreno_<?php echo $i; ?> value="<?php echo $Porzione_Terreno_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Qualita'</label>
         <div class="col-lg-8">
           <input class="form-control resize validateCustom vld_Custom_n" size="2" name=qualita_terreno_<?php echo $i; ?> id=qualita_terreno_<?php echo $i; ?> value="<?php echo $Qualita_Terreno_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-4" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Descrizione</label>
         <div class="col-lg-8">
           <input class="form-control resize" name=descrizione_qualita_terreno_<?php echo $i; ?> id=descrizione_qualita_terreno_<?php echo $i; ?> value="<?php echo $Descrizione_Qualita_Terreno_Immobiliare[$i]; ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Classe</label>
         <div class="col-lg-8">
           <input class="form-control resize" size="3" name=classe_terreno_<?php echo $i; ?> id=classe_terreno_<?php echo $i; ?> value="<?php echo $Classe_Immobiliare_Terreno[$i]; ?>">
         </div>
       </div>
     </div>
   </div>

   <div class="row tr_immobiliare tr_terreno_<?php echo $i; ?>">
     <div class="col col-lg-1 col-lg-offset-1" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">HA</label>
         <div class="col-lg-8">
           <input class="form-control resize" name=HA_terreno_<?php echo $i; ?> id=HA_terreno_<?php echo $i; ?> value="<?php echo $HA_Ettari_Terreno_Immobiliare[$i] ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-1" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">A</label>
         <div class="col-lg-8">
           <input class="form-control resize" name=A_terreno_<?php echo $i; ?> id=A_terreno_<?php echo $i; ?> value="<?php echo $A_Are_Terreno_Immobiliare[$i] ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-1" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">C</label>
         <div class="col-lg-8">
           <input class="form-control resize" name=C_terreno_<?php echo $i; ?> id=C_terreno_<?php echo $i; ?> value="<?php echo $C_Centiare_Terreno_Immobiliare[$i] ?>">
         </div>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Dominicale</label>
         <div class="col-lg-6" style="margin-right: 0; padding-right: 0;">
           <input class="form-control resize validateCustom vld_Custom_d" size="5" name=dominicale_terreno_<?php echo $i; ?> id=dominicale_terreno_<?php echo $i; ?> value="<?php echo $Dominicale_Terreno_Immobiliare[$i] ?>">
         </div>
         <label class="col-lg-2 control-label resize " style="text-align: left; margin-left: 0; padding-left: 0;">&nbsp;&euro;</label>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Agrario</label>
         <div class="col-lg-6" style="margin-right: 0; padding-right: 0;">
           <input class="form-control resize validateCustom vld_Custom_d" size="5" name=agrario_terreno_<?php echo $i; ?> id=agrario_terreno_<?php echo $i; ?> value="<?php echo $Agrario_Terreno_Immobiliare[$i] ?>">
         </div>
         <label class="col-lg-2 control-label resize " style="text-align: left;margin-left: 0; padding-left: 0;">&nbsp;&euro;</label>
       </div>
     </div>
     <div class="col col-lg-2" >
       <div class="form-group">
         <label class="col-lg-4 control-label resize " style="text-align: left;">Deduzioni</label>
         <div class="col-lg-6" style="margin-right: 0; padding-right: 0;">
           <input class="form-control resize validateCustom vld_Custom_d" size="5" name=deduzioni_terreno_<?php echo $i; ?> id=deduzioni_terreno_<?php echo $i; ?> value="<?php echo $Deduzioni_Terreno_Immobiliare[$i] ?>">
         </div>
         <label class="col-lg-2 control-label resize " style="text-align: left;margin-left: 0; padding-left: 0;">&nbsp;&euro;</label>
       </div>
     </div>
   </div>

<?php
}
 ?>
</div>


<!-- TABELLA NOTIFICHE(PAGINA 2) -->

<div class="pignoramento_2">

  <div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
      <a href="#" onclick="pagina_pignoramento(1);" tabindex=2><img title="Pagina precedente" src="<?= IMMAGINIWEB; ?>/prev.png" style="width:20px; height:14px; border:0;"></a>
			<span class="titolo font14">Dati pignoramento 2/5</span>
			<a href="#" onclick="pagina_pignoramento(3);" tabindex=2><img title="Pagina successiva" src="<?= IMMAGINIWEB; ?>/next.png" style="width:20px; height:14px; border:0;"></a>

			<a onMouseover="title='Controllo email'" href="#" style="text-decoration:none;" onClick="lista_mail();" >
				<img src="<?= IMMAGINIWEB; ?>/email_mini.png" style="width:20px; height:14px; border:0;" >
			</a>
    </div>
  </div>

  <?php
    if(isset($notifiche_debitore))
    {
      $index_new = count($notifiche_debitore);
      $count_notif = count($notifiche_debitore)+1;
    }
    else
    {
      $index_new = 0;
      $count_notif = 1;
    }
  ?>
<div style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%'></div>

  <div class="row" style="margin-top: 2%;">
    <div class="col col-lg-3 col-lg-offset-1" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Porzione</label>-->
        <div class="col-lg-12">
          <font id="denom_debitore" class="titolo resize">DEBITORE</font>
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Qualita'</label>-->
        <div class="col-lg-12 resize">
          <a href="#" style="text-decoration:none;">
    				<img src="<?= IMMAGINIWEB; ?>/plus.png" style="text-decoration:none; border:none" width="20" height="20" onclick="aggiungi_notifica('debitore','','<?php echo $index_new; ?>');" title="Aggiungi notifica">
    			</a>
    		NOTIFICA
        </div>
      </div>
    </div>
    <div class="col col-lg-3 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;">Spese notifica</label>
        <div class="col-lg-5">
          <input id="spese_not_debitore" name="spese_not_debitore" class="form-control resize validateCustom vld_Custom_d" value="<?php echo $Spese_Notifica_Debitore; ?>" size=6 onchange="update_notifica_debitore();">
        </div>
        <label class="col-lg-1 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>


  <?php

  for( $num_not = 0; $num_not < $count_notif; $num_not++)
  {
  	$esito_riscontro = "";
  	if($num_not==$count_notif-1)
  	{
  		if($num_not!=0)
  			$layout.="<script>$('.tr_debitore_".$num_not."').hide();</script>";

  		$data_notifica_ciclo = "";
  		$stampa_copia_debitore = "";
  		$relata_singola_debitore = "";
  		$note_not_debitore = "";
  	}
  	else
  	{

  		$data_notifica_ciclo = $Data_Notifica_Debitore[$num_not];
  		$relata_singola_debitore = isset($pdf_rel_debitore[$num_not])?$pdf_rel_debitore[$num_not]:"";
  		$note_not_debitore = $Note_Notifica_Debitore[$num_not];
  		if($Data_Stampa!=null && $control_debitore[$num_not]!="no")
  		{
  			$stampa_copia_debitore = "<input type=button name='stampa_copia_debitore_".$num_not."' value='Stampa copia' ";
  			$stampa_copia_debitore.= "onclick=\"stampa_copia_singola('rel_debitore','',".$num_not.")\">";
  		}
  		else
  			$stampa_copia_debitore = "";

  		if($ID_Notifica_Debitore[$num_not]!=0)
  		{
  			if($Tipo_Riscontro_Debitore[$num_not]=="Positivo")
  			{
  				$esito_riscontro = "ESITO ";
  				$esito_riscontro.="<img src='".IMMAGINIWEB."/pollicesu.png' style='text-decoration:none; border:none' width='20' height='20'>";
  			}
  			else if($Tipo_Riscontro_Debitore[$num_not]=="Negativo")
  			{
  				$esito_riscontro = "ESITO ";
  				$esito_riscontro.="<img src='".IMMAGINIWEB."/pollicegiu.png' style='text-decoration:none; border:none' width='20' height='20'>";
  			}
  			else if($Tipo_Riscontro_Debitore[$num_not]=="Parziale")
  			{
  				$esito_riscontro = "ESITO PARZIALE";
  			}
  		}
  	}
  	?>

  <div class="row tr_debitore_<?php echo $num_not; ?>" >
    <div class="col col-lg-4 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-3 control-label resize " style="text-align: left;"><font class="titolo">Notifica <?php echo ($num_not+1); ?></font></label>
        <div class="col-lg-8">
          <select id="modalita_stampa_debitore_<?php echo $num_not; ?>" name="modalita_stampa_debitore_<?php echo $num_not; ?>" class="form-control resize" onchange="cambio_modalita('debitore','<?php echo $num_not; ?>');">
    				<option value=""></option>
    				<option value="posta">Tramite posta</option>
    				<option value="mani">A mani</option>
    				<option value="pec">Via PEC</option>
    			</select>
        </div>
      </div>
    </div>
    <div class="col col-lg-2" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">Notifica <?php echo ($num_not+1); ?></font></label>-->
        <div class="col-lg-12">
          <?php echo $esito_riscontro; ?>
        </div>
      </div>
    </div>
    <div class="col col-lg-2" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">Notifica <?php echo ($num_not+1); ?></font></label>-->
        <div class="col-lg-6">
          <a id="AR_fronte" href="#" onMouseover="title='AR Fronte'" style="text-decoration:none;">
              <font class="color_titolo font16 font_bold under_decor">AR Fronte</font>
          </a>
        </div>
        <div class="col-lg-6">
          <a id="AR_retro" href="#" onMouseover="title='AR Retro'" style="text-decoration:none;">
              <font class="color_titolo font16 font_bold under_decor">AR Retro</font>
          </a>
        </div>
      </div>
    </div>
    <div class="col col-lg-2" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">Notifica <?php echo ($num_not+1); ?></font></label>-->
        <div class="col-lg-12">
          <?php echo $stampa_copia_debitore; ?>
        </div>
      </div>
    </div>
    <div class="col col-lg-1" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">Notifica <?php echo ($num_not+1); ?></font></label>-->
        <div class="col-lg-12">
          <a id=pdf_rel_debitore_<?php echo $num_not; ?> href="#" style="text-decoration:none;display:none;">
    				<img id="file_pdf_debitore_<?php echo $num_not; ?>" src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none" width="20" height="20" onclick="apri('<?php echo $relata_singola_debitore; ?>');" title="Relata singola debitore">
    			</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_debitore_<?php echo $num_not; ?>" >
    <div class="col col-lg-3 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Data notifica</label>
        <div class="col-lg-8">
          <input id="data_not_debitore_<?php echo $num_not; ?>" class="text_center picker form-control resize validateCustom vld_Custom_date" name="data_not_debitore_<?php echo $num_not; ?>" type=text value="<?php echo $data_notifica_ciclo; ?>" size=9 onchange = "">
        </div>
      </div>
    </div>
    <div class="col col-lg-7" >
      <div class="form-group">
        <label class="col-lg-3 control-label resize " style="text-align: left;">Modalita'</label>
        <div class="col-lg-9">
          <select id="modalita_not_debitore_<?php echo $num_not; ?>" name="modalita_not_debitore_<?php echo $num_not; ?>" class="form-control resize" onchange="cambia_title('modalita_not_debitore_<?php echo $num_not; ?>');">
            <option></option>
            <optgroup label="Tramite soggetto preposto"><?php echo $options_a_mani; ?></optgroup>
            <optgroup label="Per posta"><?php echo $options_per_posta; ?></optgroup>
            <optgroup label="Eccezionali"><?php echo $options_eccezionali; ?></optgroup>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_debitore_<?php echo $num_not; ?>" >
    <div class="col col-lg-5 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-2 control-label resize " style="text-align: left;">Giacenza</label>
        <div class="col-lg-10">
          <select id="stato_not_debitore_<?php echo $num_not; ?>" name="stato_not_debitore_<?php echo $num_not; ?>" class="form-control resize" onchange="cambia_title('stato_not_debitore_<?php echo $num_not; ?>');" >
    				<option></option>
    				<?php echo $options_stati; ?>
    			</select>
        </div>
      </div>
    </div>
    <div class="col col-lg-5" >
      <div class="form-group">
        <label class="col-lg-2 control-label resize " style="text-align: left;">Anomalie</label>
        <div class="col-lg-10">
          <select id="motivo_not_debitore_<?php echo $num_not; ?>" name="motivo_not_debitore_<?php echo $num_not; ?>" class="form-control resize" onchange="cambia_title('motivo_not_debitore_<?php echo $num_not; ?>');">
    				<option ></option>
    				<?php echo $options_motivi; ?>
    			</select>
        </div>
      </div>
    </div>
  </div>

  <div class="row tr_debitore_<?php echo $num_not; ?> tr_validato_debitore_<?php echo $num_not; ?> tr_not_debitore_ultimo" >
    <div class="col col-lg-2 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-8 control-label resize " style="text-align: left;">Ind. validato</label>
        <div class="col-lg-4">
          <input type=checkbox class="resize" id=ind_validato_debitore_<?php echo $num_not; ?> name=ind_validato_debitore_<?php echo $num_not; ?> value="si" title="Ind. validato - Flag di verifica dell'indirizzo del destinatario. E' necessaria la verifica nel caso sia selezionato uno Stato di Giacenza">
        </div>
      </div>
    </div>
    <div class="col col-lg-8" >
      <div class="form-group">
        <label class="col-lg-3 control-label resize " style="text-align: right;">Note</label>
        <div class="col-lg-9">
          <input id=note_not_debitore_<?php echo $num_not; ?> class="form-control resize" name=note_not_debitore_<?php echo $num_not; ?> type=text value='<?php echo $note_not_debitore; ?>'>
        </div>
      </div>
    </div>
  </div>


<?php }

for($num_terzo=0;$num_terzo<$count_terzi;$num_terzo++)
{
	if(isset($notifiche_terzo[$num_terzo]))
	{
		$index_new = count($notifiche_terzo[$num_terzo]);
		$count_notif = count($notifiche_terzo[$num_terzo])+1;
	}
	else
	{
		$index_new = 0;
		$count_notif = 1;
	}

?>

<div class="tr_not_terzi ctrl_terzo_<?php echo $num_terzo; ?>" style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%;margin-bottom:2%;'></div>

<div class="row tr_not_terzi ctrl_terzo_<?php echo $num_terzo; ?>">
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Porzione</label>-->
      <div class="col-lg-12">
        <font id="denom_terzo_<?php echo $num_terzo; ?>" class="titolo resize"><?php echo $nome_cognome_terzo[$num_terzo]; ?></font>
      </div>
    </div>
  </div>
  <div class="col col-lg-3" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Qualita'</label>-->
      <div class="col-lg-12 resize">
        <a href="#" style="text-decoration:none;">
  				<img src="<?= IMMAGINIWEB; ?>/plus.png" style="text-decoration:none; border:none" width="20" height="20" onclick="aggiungi_notifica('terzo','<?php echo $num_terzo; ?>','<?php echo $index_new; ?>');" title="Aggiungi notifica">
  			</a>
      NOTIFICA
      </div>
    </div>
  </div>
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-6 control-label resize " style="text-align: left;">Spese notifica</label>
      <div class="col-lg-5">
        <input id="spese_not_terzo_<?php echo $num_terzo; ?>" name="spese_not_terzo_<?php echo $num_terzo; ?>" class="form-control resize validateCustom vld_Custom_d" value="<?php echo $Spese_Notifica[$num_terzo]; ?>" size=6 onchange="update_notifica_terzi();">
      </div>
      <label class="col-lg-1 control-label resize " style="text-align: left;">&euro;</label>
    </div>
  </div>
</div>


<?php

for($num_not=0;$num_not < $count_notif;$num_not++)
{
	$id_tr_not_terzo = "";
	$esito_riscontro = "";
	if($num_not==$count_notif-1)
	{
		if($num_terzo==$count_terzi-1)
			$id_tr_not_terzo = "id='tr_not_terzo_finale_".$num_terzo."'";

		if($num_not!=0)
			$layout.="<script>$('.tr_terzo_".$num_terzo."_".$num_not."').hide();</script>";

		$data_notifica_ciclo = "";
		$stampa_copia_terzo = "";
		$relata_singola_terzo = "";
		$note_not_terzo = "";
	}
	else
	{

        //print_r($Data_Notifica);
		$data_notifica_ciclo = isset($Data_Notifica[$num_terzo][$num_not])?$Data_Notifica[$num_terzo][$num_not]:null;
		$relata_singola_terzo = isset($pdf_rel_terzo[$num_terzo][$num_not])?$pdf_rel_terzo[$num_terzo][$num_not]:null;
		$note_not_terzo = isset($Note_Notifica[$num_terzo][$num_not])?$Note_Notifica[$num_terzo][$num_not]:null;
		if($Data_Stampa!=null && $control_terzo[$num_terzo][$num_not]!="no")
		{
			$stampa_copia_terzo = "<input type=button name='stampa_copia_terzo_".$num_terzo."_".$num_not."' value='Stampa copia' ";
			$stampa_copia_terzo.= "onclick=\"stampa_copia_singola('rel_terzo',".$num_terzo.",".$num_not.")\">";
		}
		else
			$stampa_copia_terzo = "";


		$idNTerzo = isset($ID_Notifica_Terzo[$num_terzo][$num_not])?$ID_Notifica_Terzo[$num_terzo][$num_not]:0;

		if($idNTerzo!=0)
		{
			if($Tipo_Riscontro_Terzo[$num_terzo][$num_not]=="Positivo")
			{
				$esito_riscontro = "ESITO ";
				$esito_riscontro.="<img src='".IMMAGINIWEB."/pollicesu.png' style='text-decoration:none; border:none' width='20' height='20'>";
			}
			else if($Tipo_Riscontro_Terzo[$num_terzo][$num_not]=="Negativo")
			{
				$esito_riscontro = "ESITO ";
				$esito_riscontro.="<img src='".IMMAGINIWEB."/pollicegiu.png' style='text-decoration:none; border:none' width='20' height='20'>";
			}
			else if($Tipo_Riscontro_Terzo[$num_terzo][$num_not]=="Parziale")
			{
				$esito_riscontro = "<span class='color_red'>ESITO PARZIALE</span>";
			}
		}
	}

//echo "<h1>111".$pignoramento["Tipo_Terzi"]."</h1>";
	?>



<div class="row tr_not_terzi tr_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> ctrl_terzo_<?php echo $num_terzo; ?>">
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">Notifica <?php echo ($num_not+1); ?></font></label>
      <div class="col-lg-8">
        <select id="modalita_stampa_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" name="modalita_stampa_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" class="form-control resize" onchange="cambio_modalita('terzo_<?php echo $num_terzo; ?>','<?php echo $num_not; ?>');">
          <option value=""></option>
          <option value="posta">Tramite posta</option>
          <option value="mani">A mani</option>
          <option value="pec">Via PEC</option>
        </select>
      </div>
    </div>
  </div>
  <div class="col col-lg-2" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Qualita'</label>-->
      <div class="col-lg-12">
        <?php echo $esito_riscontro; ?>
      </div>
    </div>
  </div>
  <div class="col col-lg-2" >
    <div class="form-group">

      <div class="col-lg-12">
        <input type=button id=riscontro name=riscontro class="btn btn-primary resize" onclick="popup_riscontro('<?php echo $pignoramento["Tipo_Terzi"]; ?>',<?php echo isset($ID_Notifica_Terzo[$num_terzo][$num_not])?$ID_Notifica_Terzo[$num_terzo][$num_not]:null; ?>);" value="Riscontro" />
      </div>
    </div>
  </div>
  <div class="col col-lg-2" >
    <div class="form-group">
      <div class="col-lg-12">
        <?php echo $stampa_copia_terzo; ?>
      </div>
    </div>
  </div>
  <div class="col col-lg-1" >
    <div class="form-group">
      <div class="col-lg-12">
        <a id=pdf_rel_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> href="#" style="text-decoration:none;display:none;">
          <img id="file_pdf_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none" width="20" height="20" onclick="apri('<?php echo $relata_singola_terzo; ?>');" title="Relata singola terzo">
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row tr_not_terzi tr_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> ctrl_terzo_<?php echo $num_terzo; ?>" >
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Data notifica</label>
      <div class="col-lg-8">
        <input id="data_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" style=" text-align: center;" class="resize form-control picker text_center validateCustom vld_Custom_date" name="data_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" type=text value="<?php echo $data_notifica_ciclo; ?>" size=9 onchange = "">
      </div>
    </div>
  </div>
  <div class="col col-lg-7" >
    <div class="form-group">
      <label class="col-lg-3 control-label resize " style="text-align: left;">Modalita'</label>
      <div class="col-lg-9">
        <select id="modalita_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" name="modalita_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" class="resize form-control" onchange="cambia_title('modalita_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>');">
          <option></option>
          <optgroup label="Tramite soggetto preposto"><?php echo $options_a_mani; ?></optgroup>
          <optgroup label="Per posta"><?php echo $options_per_posta; ?></optgroup>
          <optgroup label="Eccezionali"><?php echo $options_eccezionali; ?></optgroup>
        </select>
      </div>
    </div>
  </div>
</div>

<div class="row tr_not_terzi tr_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> ctrl_terzo_<?php echo $num_terzo; ?>" >
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-3 control-label resize " style="text-align: left;">Giacenza</label>
      <div class="col-lg-9">
        <select id="stato_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" name="stato_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" class="resize form-control" onchange="cambia_title('stato_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>');" >
          <option></option>
          <?php echo $options_stati; ?>
        </select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Anomalie</label>
      <div class="col-lg-8">
        <select id="motivo_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" name="motivo_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>" class="resize form-control" onchange="cambia_title('motivo_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?>');">
          <option ></option>
          <?php echo $options_motivi; ?>
        </select>
      </div>
    </div>
  </div>
</div>

<!--class="tr_not_terzi tr_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> tr_validato_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> ctrl_terzo_<?php echo $num_terzo; ?>"-->

<div <?php echo $id_tr_not_terzo; ?> class="row tr_not_terzi tr_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> tr_validato_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> ctrl_terzo_<?php echo $num_terzo; ?>" >
  <div class="col col-lg-2 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-8 control-label resize " style="text-align: left;">Ind. validato</label>
      <div class="col-lg-4">
        <input type=checkbox class="resize" id=ind_validato_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> name=ind_validato_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> value="si" title="Ind. validato - Flag di verifica dell'indirizzo del destinatario. E' necessaria la verifica nel caso sia selezionato uno Stato di Giacenza">
      </div>
    </div>
  </div>
  <div class="col col-lg-8" >
    <div class="form-group">
      <label class="col-lg-3 control-label resize " style="text-align: right;">Note</label>
      <div class="col-lg-9">
        <input id=note_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> class="form-control resize" name=note_not_terzo_<?php echo $num_terzo; ?>_<?php echo $num_not; ?> type=text value='<?php echo $note_not_terzo; ?>'>
      </div>
    </div>
  </div>
</div>

<?php
 }
} ?>

<?php
if(isset($notifiche_veicolo))
{
	$index_new = count($notifiche_veicolo);
	$count_notif = count($notifiche_veicolo)+1;
}
else
{
	$index_new = 0;
	$count_notif = 1;
}
?>

<!--class=tr_veicolo-->
<div class="tr_veicolo" style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%'></div>

<div class="row tr_veicolo" style="margin-top: 2%;">
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Porzione</label>-->
      <div class="col-lg-12">
        <font id="denom_istituto" class="titolo resize"><?php echo $denom_istituto; ?></font>
      </div>
    </div>
  </div>
  <div class="col col-lg-3" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Qualita'</label>-->
      <div class="col-lg-12 resize">
        <a href="#" style="text-decoration:none;">
  				<img src="<?= IMMAGINIWEB; ?>/plus.png" style="text-decoration:none; border:none" width="20" height="20" onclick="aggiungi_notifica('veicolo','','<?php echo $index_new; ?>');" title="Aggiungi notifica">
  			</a>
  		NOTIFICA
      </div>
    </div>
  </div>
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-6 control-label resize " style="text-align: left;">Spese notifica</label>
      <div class="col-lg-5">
        <input id="spese_not_veicolo" name="spese_not_veicolo" class="resize form-control validateCustom vld_Custom_d" value="<?php echo $Spese_Notifica_Veicolo; ?>" size=6 onchange="update_notifica_veicolo();">
      </div>
      <label class="col-lg-1 control-label resize " style="text-align: left;">&euro;</label>
    </div>
  </div>
</div>

<?php

for( $num_not = 0; $num_not < $count_notif; $num_not++)
{
$esito_riscontro = "";
if($num_not==$count_notif-1)
{
  if($num_not!=0)
    $layout.="<script>$('.tr_veicolo_".$num_not."').hide();</script>";

  $data_notifica_ciclo = "";
  $stampa_copia_veicolo = "";
  $relata_singola_veicolo = "";
  $note_not_veicolo = "";
}
else
{
  $data_notifica_ciclo = $Data_Notifica_Veicolo[$num_not];
  $relata_singola_veicolo = $pdf_rel_istituto[$num_not];
  $note_not_veicolo = $Note_Notifica_Veicolo[$num_not];
  if($Data_Stampa!=null && $control_istituto[$num_not]!="no")
  {
    $stampa_copia_veicolo = "<input type=button name='stampa_copia_veicolo_".$num_not."' value='Stampa copia' ";
    $stampa_copia_veicolo.= "onclick=\"stampa_copia_singola('rel_istituto','',".$num_not.")\">";
  }
  else
    $stampa_copia_veicolo = "";

  if($ID_Notifica_Veicolo[$num_not]!=0)
  {
    if($Tipo_Riscontro_Veicolo[$num_not]=="Positivo")
    {
      $esito_riscontro = "ESITO ";
      $esito_riscontro.="<img src='".IMMAGINIWEB."/pollicesu.png' style='text-decoration:none; border:none' width='20' height='20'>";
    }
    else if($Tipo_Riscontro_Veicolo[$num_not]=="Negativo")
    {
      $esito_riscontro = "ESITO ";
      $esito_riscontro.="<img src='".IMMAGINIWEB."/pollicegiu.png' style='text-decoration:none; border:none' width='20' height='20'>";
    }
    else if($Tipo_Riscontro_Veicolo[$num_not]=="Parziale")
    {
      $esito_riscontro = "<span class='color_red'>ESITO PARZIALE</span>";
    }
  }
}

?>

<div class="row tr_veicolo tr_veicolo_<?php echo $num_not; ?>">
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">Notifica <?php echo ($num_not+1); ?></font></label>
      <div class="col-lg-8">
        <select id="modalita_stampa_veicolo_<?php echo $num_not; ?>" name="modalita_stampa_veicolo_<?php echo $num_not; ?>" class="form-control resize" onchange="cambio_modalita('veicolo','<?php echo $num_not; ?>');">
  				<option value=""></option>
  				<option value="posta">Tramite posta</option>
  				<option value="mani">A mani</option>
  				<option value="pec">Via PEC</option>
  			</select>
      </div>
    </div>
  </div>
  <div class="col col-lg-2" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Qualita'</label>-->
      <div class="col-lg-12">
        <?php echo $esito_riscontro; ?>
      </div>
    </div>
  </div>
  <div class="col col-lg-2" >
    <div class="form-group">
      <div class="col-lg-12">
        <input type=button value="Riscontro" name=riscontro class="btn btn-primary resize" onclick="popup_ivg(<?php echo isset($ID_Notifica_Veicolo[$num_not])?$ID_Notifica_Veicolo[$num_not]:null; ?>);">
      </div>
    </div>
  </div>
  <div class="col col-lg-2" >
    <div class="form-group">
      <div class="col-lg-12">
        <?php echo $stampa_copia_veicolo; ?>
      </div>
    </div>
  </div>
  <div class="col col-lg-1" >
    <div class="form-group">
      <div class="col-lg-12">
        <a id=pdf_rel_veicolo_<?php echo $num_not; ?> href="#" style="text-decoration:none;display:none;">
  				<img id="file_pdf_veicolo_<?php echo $num_not; ?>" src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none" width="20" height="20" onclick="apri('<?php echo $relata_singola_veicolo; ?>');" title="Relata singola IVG">
  			</a>
      </div>
    </div>
  </div>
</div>

<div class="row tr_veicolo tr_veicolo_<?php echo $num_not; ?>" >
  <div class="col col-lg-3 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Data notifica</label>
      <div class="col-lg-8">
        <input id="data_not_veicolo_<?php echo $num_not; ?>" class="resize form-control picker text_center validateCustom vld_Custom_date" name="data_not_veicolo_<?php echo $num_not; ?>" type=text value="<?php echo $data_notifica_ciclo; ?>" size=9 onchange = "">
      </div>
    </div>
  </div>
  <div class="col col-lg-7">
    <div class="form-group">
      <label class="col-lg-3 control-label resize " style="text-align: left;">Modalita'</label>
      <div class="col-lg-9">
        <select id="modalita_not_veicolo_<?php echo $num_not; ?>" name="modalita_not_veicolo_<?php echo $num_not; ?>" class="resize form-control" onchange="cambia_title('modalita_not_veicolo_<?php echo $num_not; ?>');">
  				<option></option>
  				<optgroup label="Tramite soggetto preposto"><?php echo $options_a_mani; ?></optgroup>
  				<optgroup label="Per posta"><?php echo $options_per_posta; ?></optgroup>
  				<optgroup label="Eccezionali"><?php echo $options_eccezionali; ?></optgroup>
  			</select>
      </div>
    </div>
  </div>
</div>

<div class="row tr_veicolo tr_veicolo_<?php echo $num_not; ?>" >
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-3 control-label resize " style="text-align: left;">Giacenza</label>
      <div class="col-lg-9">
        <select id="stato_not_veicolo_<?php echo $num_not; ?>" name="stato_not_veicolo_<?php echo $num_not; ?>" class="resize form-control" onchange="cambia_title('stato_not_veicolo_<?php echo $num_not; ?>');" >
  				<option></option>
  				<?php echo $options_stati; ?>
  			</select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-2 control-label resize " style="text-align: left;">Anomalie</label>
      <div class="col-lg-10">
        <select id="motivo_not_veicolo_<?php echo $num_not; ?>" name="motivo_not_veicolo_<?php echo $num_not; ?>" class="resize form-control" onchange="cambia_title('motivo_not_veicolo_<?php echo $num_not; ?>');">
  				<option ></option>
  				<?php echo $options_motivi; ?>
  			</select>
      </div>
    </div>
  </div>
</div>

<div class="row tr_veicolo tr_veicolo_<?php echo $num_not; ?> tr_validato_veicolo_<?php echo $num_not; ?>" >
  <div class="col col-lg-2 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-8 control-label resize " style="text-align: left;">Ind. validato</label>
      <div class="col-lg-4">
        	<input type=checkbox class="resize" id=ind_validato_veicolo_<?php echo $num_not; ?> name=ind_validato_veicolo_<?php echo $num_not; ?> value="si" title="Ind. validato - Flag di verifica dell'indirizzo del destinatario. E' necessaria la verifica nel caso sia selezionato uno Stato di Giacenza">
      </div>
    </div>
  </div>
  <div class="col col-lg-8" >
    <div class="form-group">
      <label class="col-lg-3 control-label resize " style="text-align: right;">Note</label>
      <div class="col-lg-9">
        <input id=note_not_veicolo_<?php echo $num_not; ?> class="form-control resize" name=note_not_veicolo_<?php echo $num_not; ?> type=text value='<?php echo $note_not_veicolo; ?>'>
      </div>
    </div>
  </div>
</div>



<?php
} ?>

</div>
<!-- TABELLA SPESE ACCESSORIE(PAGINA 3) -->

<div class="pignoramento_3">

  <div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
      <a href="#" onclick="pagina_pignoramento(2);" tabindex=2><img title="Pagina precedente" src="<?= IMMAGINIWEB; ?>/prev.png" style="width:20px; height:14px; border:0;"></a> <span class="titolo font14">Dati pignoramento 3/5</span> <a href="#" onclick="pagina_pignoramento(4);" tabindex=2><img title="Pagina precedente" src="<?= IMMAGINIWEB; ?>/next.png" style="width:20px; height:14px; border:0;"></a>
    </div>
  </div>

  <div class="row" style="margin-top: 2%;">
    <div class="col-lg-10 col-lg-offset-1 resize"><b>SPESE ACCESSORIE</b></div>
  </div>

  <div class="row" >
    <div class="col col-lg-3 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;">Incremento del</label>
        <div class="col-lg-4" style="margin-right:0;padding-right:0;">
            <input readonly id=percentuale name=percentuale style="text-align: right;background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize " value="<?php echo $percentuale; ?>" size=3 >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;margin-left:0;padding-left:0;">&nbsp;%</label>
      </div>
    </div>
    <div class="col col-lg-7" >
      <div class="form-group">
        <label class="col-lg-7 control-label resize " style="text-align: right;">delle spese con coefficiente di applicazione per il credito di</label>
        <div class="col-lg-3" style="margin-right:0;padding-right:0;">
          <input readonly id=credito_ingiunzione style="text-align: right;background-color: rgb(153, 204, 255); border: 2px solid black;" name=credito_ingiunzione class="form-control resize " value="<?php echo $Importo_Dovuto; ?>" size=6 >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;margin-left:0;padding-left:0;">&nbsp;&euro;</label>
      </div>
    </div>
  </div>

  <div class="row" >
    <div class="col col-lg-3 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;">TOTALE RIMBORSO</label>
        <div class="col-lg-4" style="margin-right:0;padding-right:0;">
          <input readonly id=rimborso_totale name=rimborso_totale class="form-control resize" style="text-align: right; background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $Totale_Rimborso; ?>" size=7 >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;margin-left:0;padding-left:0;">&nbsp;&euro;</label>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top: 2%;"></div>
  <?php
  for($i=1;$i<11;$i++)
  {
  ?>

  <div class="row" style="margin-top:0;padding-top:0;margin-bottom:0;padding-bottom:0;">
    <div class="col col-lg-8 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-2 control-label resize " style="text-align: left;">Descrizione</label>
        <div class="col-lg-10">
          <select id=spesa_<?php echo $i; ?> name=spesa_<?php echo $i; ?> class="form-control resize" onchange="scelta_spesa(<?php echo $i; ?>,'');cambia_title('spesa_<?php echo $i; ?>');">
    				<option></option>
    				<optgroup class="una_tantum_class" label="UNA TANTUM"><?php echo $options_una_tantum_cur; ?></optgroup>
    				<optgroup class="a_giorni_class" label="A GIORNO"><?php echo $options_a_giorni_cur; ?></optgroup>
    				<optgroup class="a_km_class" label="A KM"><?php echo $options_a_km_cur; ?></optgroup>
    			</select>
        </div>
      </div>
    </div>
    <div class="col col-lg-2" >
      <div class="form-group">
        <!--<label class="col-lg-6 control-label resize " style="text-align: left;">TOTALE RIMBORSO</label>-->
        <div class="col-lg-12">
          <select id=tot_parziale_<?php echo $i; ?> name=tot_parziale_<?php echo $i; ?> class="form-control resize" onchange="scelta_spesa(<?php echo $i; ?>,'');">
    				<option value="0"></option>
    				<option value="1">Totale 1</option>
    				<option value="2">Totale 2</option>
    				<option value="3">Totale 3</option>
    			</select>
        </div>
      </div>
    </div>
  </div>

  <div class="row spese diverso_una_tantum_<?php echo $i; ?>" style="margin-top:0;padding-top:0;margin-bottom:0;padding-bottom:0;">
    <div class="col col-lg-7 col-lg-offset-1">
      <div class="form-group">
        <div class="col-lg-4">
          <label class="col-lg-6 control-label resize " style="text-align: left;">Tariffa fissa di</label>
          <div class="col-lg-4">
            <input readonly id=fisso_<?php echo $i; ?> name=fisso_<?php echo $i; ?> style="text-align: right; background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize" value="" size=5 >
          </div>
          <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
        </div>
        <div class="col-lg-5">
          <label class="col-lg-6 control-label resize " style="text-align: left;font-size: 12px;">( per i primi <span id="fisso_durata_<?php echo $i; ?>" ></span> gg/km )  +</label>
          <div class="col-lg-4">
            <input readonly id=extra_<?php echo $i; ?> name=extra_<?php echo $i; ?> style=" background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize" value="" size=5 >
          </div>
          <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
        </div>
        <div class="col-lg-3">
          <label class="col-lg-3 control-label resize " style="text-align: right;">x</label>
          <div class="col-lg-7">
            <input id=durata_extra_<?php echo $i; ?> name=durata_extra_<?php echo $i; ?> class="form-control resize vld_dec" value="<?php echo $Spese_Accessorie[$i]['extra_spesa']; ?>" size=2 onchange="aggiorna_spesa(<?php echo $i; ?>)">
          </div>
          <label class="col-lg-2 control-label resize " style="text-align: left;">gg/km</label>
        </div>

      </div>
    </div>
    <div class="col col-lg-2 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-5 control-label resize " style="text-align: left;">Rimborso</label>
        <div class="col-lg-5">
          <input readonly id=rimborso_<?php echo $i; ?> name=rimborso_<?php echo $i; ?> style="text-align: right; background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize" value="" size=6 >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>

  <div class="row spese una_tantum_<?php echo $i; ?>" style="margin-top:0;padding-top:0;margin-bottom:0;padding-bottom:0;">
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Tariffa fissa di</label>
        <div class="col-lg-6">
          <input readonly id=tariffa_<?php echo $i; ?> name=tariffa_<?php echo $i; ?> style="text-align: right; background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize" value="" size=5 >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-3 col-lg-offset-3" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Rimborso</label>
        <div class="col-lg-6">
          <input readonly id=rimborso_tantum_<?php echo $i; ?> name=rimborso_tantum_<?php echo $i; ?> style="text-align: right; background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize" value="" size=6 >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>

  <?php
  }?>


</div>


<?php
if($Data_Stampa!=null && $control_originale!="no")
{
	$stampa_originale = "<input type=button name='stampa_originale' value='Stampa originale' ";
	$stampa_originale.= "onclick=\"stampa_copia_singola('originale','','')\">";
}
else
	$stampa_originale = "";
?>


<!-- TABELLA ATTO PIGNORAMENTO (PAGINA 4)-->
<div class="pignoramento_4">

  <div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
      <a href="#" onclick="pagina_pignoramento(3);" tabindex=2><img title="Pagina precedente" src="<?= IMMAGINIWEB; ?>/prev.png" style="width:20px; height:14px; border:0;"></a> <span class="titolo font14">Dati pignoramento 4/5</span> <a href="#" onclick="pagina_pignoramento(5);" tabindex=2><img title="Pagina successiva" src="<?= IMMAGINIWEB; ?>/next.png" style="width:20px; height:14px; border:0;"></a>
    </div>
  </div>

  <div class="row" style="margin-top: 2%;">
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-5 control-label resize " style="text-align: left;">Stato pignoramento</label>
        <div class="col-lg-7">
          <select id="stato_pignoramento" name="stato_pignoramento" class="form-control resize">
            <option></option>
            <option>Archiviato</option>
            <option>Annullato</option>
          </select>
        </div>
      </div>
    </div>
    <div class="col col-lg-3 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;">Data inserimento</label>
        <div class="col-lg-6">
          <input class="picker text_center form-control resize validateCustom vld_Custom_date" name=data_stato_pignoramento id=data_stato_pignoramento value="<?php echo $Data_Stato_Pignoramento; ?>" size=9>
        </div>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top: 2%;">
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-5 control-label resize " style="text-align: left;">Stampatore</label>
        <div class="col-lg-7">
          <select class="form-control resize" name=PrinterId id=PrinterId>
              <?=isset($optPrinter)?$optPrinter:"<option></option>";?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Data elaborazione</label>
        <div class="col-lg-8">
          <input readonly class="text_center form-control resize"  style=" background-color: rgb(153, 204, 255); border: 2px solid black; width: 55%;" name=data_elaborazione id=data_elaborazione value="<?php echo $Data_Elaborazione; ?>" size=9>
        </div>
      </div>
    </div>
    <div class="col col-lg-4">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Data stampa</label>
        <div class="col-lg-8">
          <input readonly class="text_center form-control resize"  style=" background-color: rgb(153, 204, 255); border: 2px solid black; width: 55%;" name=data_stampa id=data_stampa value="<?php echo $Data_Stampa; ?>" size=9>
        </div>
      </div>
    </div>
    <div class="col col-lg-2">
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Data stampa</label>-->
        <div class="col-lg-8">
          <a id=pdf_originale href="#" style="text-decoration:none;">
    				<img id=file_pdf_originale src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none" width="20" height="20" onclick="apri('<?php echo $pdf_originale; ?>');" title="Atto originale">
    			</a>
    			<a id=pdf_rel_originale href="#" style="text-decoration:none;">
    				<img id=file_pdf_rel_originale src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none" width="20" height="20" onclick="apri('<?php echo $pdf_rel_originale; ?>');" title="Relata originale">
    			</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Data Flusso</label>
        <div class="col-lg-8">
          <input readonly class="text_center form-control resize"  style=" background-color: rgb(153, 204, 255); border: 2px solid black; width: 55%;" name=data_flusso id=data_flusso value="<?php echo $Data_Flusso; ?>" size=9>
        </div>
      </div>
    </div>
    <div class="col col-lg-4">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Numero Flusso</label>
        <div class="col-lg-8">
          <input readonly class="text_center form-control resize"  style=" background-color: rgb(153, 204, 255); border: 2px solid black; width: 55%;" name=numero_flusso id=numero_flusso value="<?php echo $numero_flusso; ?>" size=9>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Data consegna</label>
        <div class="col-lg-8">
          <input class="picker text_center form-control resize validateCustom vld_Custom_date" style="width:55%;" name=data_consegna id=data_consegna value="<?php echo $Data_Consegna; ?>" size=9>
        </div>
      </div>
    </div>
    <div class="col col-lg-4">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Notifica tramite</label>
        <div class="col-lg-8">
          <select id="tipo_ufficiale" name="tipo_ufficiale" class="form-control resize">
    				<option value="riscossione">Ufficiale della Riscossione</option>
    				<option value="giudiziario">Ufficiale Giudiziario</option>
    			</select>
        </div>
      </div>
    </div>
  </div>

  <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 1%;margin-bottom:2%;"></div>

  <div class="row">
    <div class="col col-lg-3 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-5 control-label resize " style="text-align: left;">Importo dovuto</label>
        <div class="col-lg-5">
          <input readonly id=importo_atto name=importo_atto class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $Importo_Dovuto; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col col-lg-3 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-5 control-label resize " style="text-align: left;">Spese notifica</label>
        <div class="col-lg-5">
          <input readonly id=spese_totali name=spese_totali class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $Totale_Spese_Notifica; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-4">
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;"> =&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Debitore</label>
        <div class="col-lg-4">
          <input id=spese_debitore class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" name=spese_debitore value="<?php echo $Tot_Spese_Notifica_Debitore; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;"> +&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Terzi</label>
        <div class="col-lg-6">
          <input id=spese_terzi class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" name=spese_terzi value="<?php echo $Tot_Spese_Notifica_Terzi; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top: 2%;">
    <div class="col col-lg-3 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-5 control-label resize " style="text-align: left;">Tot. parziale</label>
        <div class="col-lg-5">
          <input readonly id=totale_parziale name=totale_parziale class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $Totale_Parziale; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-4">
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Spese acc. (1)</label>
        <div class="col-lg-4">
          <input readonly id=spese_accessorie_1 name=spese_accessorie_1 class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $TOTALI_SPESE_ARRAY[1]; ?>" >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;"> &nbsp;&nbsp;&nbsp;&nbsp;Totale 1</label>
        <div class="col-lg-6">
          <input readonly id=totale_pignoramento_1 name=totale_pignoramento_1 class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $TOTALI_ARRAY[1]; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>

  <div class="row" >
    <div class="col col-lg-4 col-lg-offset-4">
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Spese acc. (1+2)</label>
        <div class="col-lg-4">
          <input readonly id=spese_accessorie_2 name=spese_accessorie_2 class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $TOTALI_SPESE_ARRAY[2]; ?>" >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;"> &nbsp;&nbsp;&nbsp;&nbsp;Totale 2</label>
        <div class="col-lg-6">
          <input readonly id=totale_pignoramento_2 name=totale_pignoramento_2 class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $TOTALI_ARRAY[2]; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>

  <div class="row" >
    <div class="col col-lg-4 col-lg-offset-4">
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Spese acc. (1+2+3)</label>
        <div class="col-lg-4">
          <input readonly id=spese_accessorie_3 name=spese_accessorie_3 class="text_right form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $TOTALI_SPESE_ARRAY[3]; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;"> &nbsp;&nbsp;&nbsp;&nbsp;Totale 3</label>
        <div class="col-lg-6">
          <input readonly id=totale_pignoramento_3 name=totale_pignoramento_3 class="text_right form-control resize" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" value="<?php echo $TOTALI_ARRAY[3]; ?>"  >
        </div>
        <label class="col-lg-2 control-label resize " style="text-align: left;">&euro;</label>
      </div>
    </div>
  </div>

  <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 1%;margin-bottom:2%;"></div>

  <div class="row">
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <div class="col-lg-6">
          <label class="col-lg-6 control-label resize " style="text-align: left;"> Rateizzazione</label>
          <div class="col-lg-4">
            <input type="checkbox" class="resize" id=rate_id name=rateizza value=rateizza onclick="rateo();" <?php echo $rateizza; ?>>
          </div>
        </div>
        <div class="col-lg-6 resize">
          <!--<label class="col-lg-6 control-label resize " style="text-align: left;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Spese acc. (1+2+3)</label>-->

            Tot 1<input <?php echo $disable_radio_1; ?> <?php echo $checked_radio_1; ?> type=radio id=importo_rateizzazione_1 name=importo_rateizzazione value="1" onclick="click_rate();">
            Tot 2<input <?php echo $disable_radio_2; ?> <?php echo $checked_radio_2; ?> type=radio id=importo_rateizzazione_2 name=importo_rateizzazione value="2" onclick="click_rate();">
            Tot 3<input <?php echo $disable_radio_3; ?> <?php echo $checked_radio_3; ?> type=radio id=importo_rateizzazione_3 name=importo_rateizzazione value="3" onclick="click_rate();">

        </div>

      </div>
    </div>
    <div class="col col-lg-2">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;"> Data richiesta</label>
        <div class="col-lg-8">
          <input id=data_richiesta class="text_center picker form-control resize validateCustom vld_Custom_date" name=data_richiesta type=text value='<?php echo $cls_date->Get_DateNewFormat($Data_Richiesta_Rate,"DB"); ?>' size=9 <?php echo $disable; ?> onchange="change_data_rate();">
        </div>
      </div>
    </div>
    <div class="col col-lg-2">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;"> Numero rate</label>
        <div class="col-lg-8">
          <input class="text_right form-control resize" type="text" style=" background-color: rgb(153, 204, 255); border: 2px solid black;" id=num_rate name=num_rate value="<?php echo $Rate_Previste; ?>" size=2 <?php echo $disable; ?> onchange="change_num_rate();">
        </div>
      </div>
    </div>
    <div class="col col-lg-2">
      <div class="form-group">
        <div class="col-lg-6">
          <input type=button value="Gestione" id=importi_rate name=importi_rate class="btn btn-primary resize" onclick="mod_rate();">
        </div>
      </div>
    </div>
  </div>

  <div class="row" >
    <div class="col col-lg-4 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-6 control-label resize " style="text-align: left;"> FLAG blocco coazione</label>
        <div class="col-lg-6 resize">
          <input type="checkbox" name=flag_blocco id=flag_blocco value="si">
        </div>
      </div>
    </div>
    <div class="col col-lg-6">
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;"> Motivi blocco</label>
        <div class="col-lg-8">
          <select id=motivo_blocco name=motivo_blocco class="form-control resize" onchange="cambia_title('motivo_blocco');">
            <option ></option>
            <?php echo $options_blocco; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="row" >
    <div class="col col-lg-10 col-lg-offset-1">
      <div class="form-group">
        <label class="col-lg-2 control-label resize " style="text-align: left;"> Note</label>
        <div class="col-lg-10">
          <input class="form-control resize" name="note_blocco" id="note_blocco" value="<?php echo $note_blocco; ?>" >
        </div>
      </div>
    </div>
  </div>


</div>


<!-- TABELLA ATTO PIGNORAMENTO (PAGINA 5)-->

<div class="pignoramento_5">

  <div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
      <a href="#" onclick="pagina_pignoramento(4);" tabindex=2><img title="Pagina precedente" src="<?= IMMAGINIWEB; ?>/prev.png" style="width:20px; height:14px; border:0;"></a> <span class="titolo font14">Dati pignoramento 5/5</span> <a href="#" onclick="pagina_pignoramento(1);" tabindex=2><img title="Pagina successiva" src="<?= IMMAGINIWEB; ?>/next.png" style="width:20px; height:14px; border:0;"></a>
    </div>
  </div>

  <?php

  if(count($notifiche_sollecito)==0)
  {
  	?>

    <div class="row justify-content-md-center " style="margin-top: 2%;">
      <div class="col col-md-auto text_center">
        <p style="color: red;"><b>Nessun sollecito inviato</b></p>
      </div>
    </div>

  	<?php
  }

  for( $num_not = 0; $num_not < count($notifiche_sollecito); $num_not++)
  {
  		$pdf_sollecito_debitore = $pdf_sollecito_debitore[$num_not];
  		$pdf_sollecito_carabinieri = $pdf_sollecito_carabinieri[$num_not];

  	?>

    <div class="row tr_sollecito_<?php echo $num_not; ?>">
      <div class="col col-lg-3 col-lg-offset-1">
        <div class="form-group">
          <label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">SOLLECITO <?php echo ($num_not+1); ?></font></label>
          <div class="col-lg-8">
            <select id="modalita_stampa_sollecito_<?php echo $num_not; ?>" name="modalita_stampa_sollecito_<?php echo $num_not; ?>" class="form-control resize">
      				<option value="posta" selected>Tramite posta</option>
      				<option value="mani">A mani</option>
      			</select>
          </div>
        </div>
      </div>
      <div class="col col-lg-2 col-lg-offset-4">
        <div class="form-group">
          <!--<label class="col-lg-4 control-label resize " style="text-align: left;"><font class="titolo">SOLLECITO <?php echo ($num_not+1); ?></font></label>-->
          <div class="col-lg-8">
            <a id=pdf_sollecito_debitore_<?php echo $num_not; ?> href="#" style="text-decoration:none;display:none;">
              <img id="file_pdf_sollecito_debitore_<?php echo $num_not; ?>" src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none" width="20" height="20" onclick="apri('<?php echo $pdf_sollecito_debitore; ?>');" title="Sollecito debitore">
            </a>
            <a id=pdf_sollecito_carabinieri_<?php echo $num_not; ?> href="#" style="text-decoration:none;display:none;">
              <img id="file_pdf_sollecito_carabinieri_<?php echo $num_not; ?>" src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none" width="20" height="20" onclick="apri('<?php echo $pdf_sollecito_carabinieri; ?>');" title="Sollecito carabinieri">
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row tr_sollecito_<?php echo $num_not; ?>">
      <div class="col col-lg-3 col-lg-offset-1">
        <div class="form-group">
          <label class="col-lg-4 control-label resize " style="text-align: left;">Data elaborazione</label>
          <div class="col-lg-8">
            <input id="data_elaborazione_sollecito_<?php echo $num_not; ?>" class="text_center picker form-control resize validateCustom vld_Custom_date" name="data_elaborazione_sollecito_<?php echo $num_not; ?>" type=text value="<?php echo $Data_Elaborazione_Sollecito[$num_not]; ?>" size=9 onchange = "">
          </div>
        </div>
      </div>
      <div class="col col-lg-3">
        <div class="form-group">
          <label class="col-lg-4 control-label resize " style="text-align: left;">Data stampa</label>
          <div class="col-lg-8">
            <input id="data_stampa_sollecito_<?php echo $num_not; ?>" class="text_center picker form-control resize validateCustom vld_Custom_date" name="data_stampa_sollecito_<?php echo $num_not; ?>" type=text value="<?php echo $Data_Stampa_Sollecito[$num_not]; ?>" size=9 onchange = "">
          </div>
        </div>
      </div>
      <div class="col col-lg-4">
        <div class="form-group">
          <label class="col-lg-4 control-label resize " style="text-align: right;">Spese</label>
          <div class="col-lg-6" style="margin-right: 0;padding-right: 0;">
            <input id="spese_sollecito_<?php echo $num_not; ?>" name="spese_sollecito_<?php echo $num_not; ?>" class="form-control resize validateCustom vld_Custom_d" value="<?php echo $Spese_Posta_Sollecito[$num_not]; ?>" size=6 onchange="update_sollecito();">
          </div>
          <label class="col-lg-2 control-label resize " style="text-align: left; margin-left: 0;padding-left: 0;">&nbsp;&euro;</label>
        </div>
      </div>
    </div>

<?php }?>


</div>

<!--<input type="submit" class="submit" class="form-control" id="submitButton" style="display: none;">-->
<div class="form-group">
	<button type="submit" id="submitButton" class="btn btn-primary" style="display: none;" value="Submit"></button>
</div>

</form>


<?php echo $layout; ?>

<?php include(INC."/footer.php"); ?>
