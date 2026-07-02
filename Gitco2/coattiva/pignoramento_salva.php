<?php

	if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";
	include_once CLS . "/cls_DateTimeInLine.php";
	include_once(CLS."/cls_CoazioneUtils.php");
	include_once(CLS."/cls_Utils.php");
	include_once(CLS."/cls_math.php");
	include_once(CLS."/pdf_con_bollettino.php");

	$cls_coazione = new cls_Coazione();
	$cls_db = new cls_db();
	$cls_help = new cls_help();
	$cls_date = new cls_DateTimeI("DB",false);
	$cls_Utils = new cls_Utils();
	$cls_math = new cls_math();

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');

	$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
	$comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$this->Gestore_ID = $val['Gestore_ID'];
	if(!isset($comune['Gestore_ID'])) $comune['Gestore_ID'] = 0;
	if(!isset($comune['Info_ID'])) $comune['Info_ID'] = 0;

	if( $comune['Gestore_ID'] != 0 )
	{
		$query = "SELECT * FROM gestore WHERE ID = '" . $comune['Gestore_ID'] . "'";
		//$this->Gestore = new gestore($result['Gestore_ID']);
		$gestore = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	}
	else
	{
		$query = "SELECT * FROM gestore WHERE ID = '" . $comune['Info_ID'] . "'";
		//$this->Gestore = new gestore($result['Info_ID']);
		$gestore = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	}


	//$comune = new ente_gestito($c);
	//$gestore = $comune->Gestore;

	$partita_ID = $cls_help->getVar('partita_ID');
	$pignoramento_ID = $cls_help->getVar('pignoramento_ID');
	$atto_ID = $cls_help->getVar('atto_ID');
  $PrinterId = $cls_help->getVar('PrinterId');
	$invia_submit = $cls_help->getVar('invia_submit');
	$error = 0;
	$msg = "";

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if($cls_help->getVar('pignoramento_ID')==null) $pignoramento_ID = "null";
	//echo "<h1>pignoramento_ID ---> ".$pignoramento_ID."</h1>";
	//if($cls_help->getVar('pignoramento_ID')=="") echo "<h1>not set</h1>";

	if($invia_submit == "Delete")
	{
		$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_ID." AND CC = '".$c."'";
		$cancella_pignoramento = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new pignoramento( $pignoramento_ID , $c );
		if($cls_date->Get_DateNewFormat($cancella_pignoramento["Data_Flusso"],"DB")!=null)
		{
			$error = 1;
			$msg = "ERR_DELETE Partita ".$partita_ID.": E' presente il flusso n.".$cancella_pignoramento["Numero_Flusso"]." del ".$cls_date->Get_DateNewFormat($cancella_pignoramento["Data_Flusso"],"DB").". Impossibile cancellare il pignoramento!";
      header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
      die;
    }



				if($cls_db->Delete("pignoramento_generale","ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'"))
				{

					if($cls_db->Delete("pignoramento_spese","Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'"))
					{
						switch($cancella_pignoramento["Tipo"])
						{
							case "terzi":
								$ctrl_query = $cls_db->Delete("pignoramento_presso_terzi","Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'");
								break;

							case "veicolo":
								$query = "SELECT Veicolo_ID FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'";
								$veicolo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["Veicolo_ID"];
								if($veicolo!=null){
									$query = "UPDATE veicoli SET Pignoramento_ID = NULL WHERE ID = ".$veicolo;
									$cls_db->ExecuteQuery($query);
								}
								$ctrl_query = $cls_db->Delete("pignoramento_veicolo","Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'");

								break;

							case "fermo":
								$query = "SELECT Veicolo_ID FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'";
								$veicolo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["Veicolo_ID"];
								if($veicolo!=null){
									$query = "UPDATE veicoli SET Pignoramento_ID = NULL WHERE ID = ".$veicolo;
									$cls_db->ExecuteQuery($query);
								}
								$ctrl_query = $cls_db->Delete("pignoramento_veicolo","Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'");
								break;

							case "preav_fermo":
								$query = "SELECT Veicolo_ID FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'";
								$veicolo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["Veicolo_ID"];
								if($veicolo!=null){
									$query = "UPDATE veicoli SET Pignoramento_ID = NULL WHERE ID = ".$veicolo;
									$cls_db->ExecuteQuery($query);
								}
								$ctrl_query = $cls_db->Delete("pignoramento_veicolo","Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'");
								break;

							case "immobiliare":
								$ctrl_query = $cls_db->Delete("pignoramento_immobiliare","Pignoramento_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."'");
								break;
						}

						if($ctrl_query)
						{
							if($cls_db->Delete("notifica_atto","Atto_Notificato_ID = '".$cancella_pignoramento["ID"]."' AND CC = '".$cancella_pignoramento["CC"]."' AND Tipo_Atto_Notificato = 'pignoramento'"))
							{
								$msg = "Dati eliminati correttamente";
								$cls_db->End_Transaction();
								header("Location: coazione.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
								die;
							}
							else
							{
								$error = 1;
								$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
								$cls_db->Rollback();
								header("Location: coazione.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
								die;
							}
						}
						else
						{
							$error = 1;
							$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
							$cls_db->Rollback();
							header("Location: coazione.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
							die;
						}

					}
					else
					{
						$error = 1;
						$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: coazione.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}
				}
				else
				{
					$error = 1;
					$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
					$cls_db->Rollback();
					header("Location: coazione.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
					die;
				}

	}
	else if($invia_submit == "rate")
	{
		$scadenza = $cls_help->getVar('scadenza');
		$importo_rata = $cls_help->getVar('importo');
		$num_rate = count($scadenza);

		$nominativo_gestore_rateizzazione = $cls_help->getVar('nome_gestore');
		$posizione_gestore_rateizzazione = $cls_help->getVar('posizione_gestore');
		$esito_richiesta = $cls_help->getVar('esito_richiesta');
		$motivazione = utf8_decode($cls_help->getVar('richiesta_respinta'));
		$operatore = $cls_help->getVar('operatore');

		$ID = $cls_help->getVar('pignoramento');

		$scadenze = $scadenza[0];
		$importi_rate = $importo_rata[0];
		for($i=1;$i<$num_rate;$i++)
		{
			$scadenze .= "*".$scadenza[$i];
			$importi_rate .= "*".$importo_rata[$i];
		}


		$a_paramsPigno = array(
				'table' => 'pignoramento_generale',
				'fields'=> array(
						array(  'name' => 'Rate_Previste',               'type' => 'int', 'value' => $num_rate),
						array(  'name' => 'Importi_Rate',            'type' => 'string', 'value' => $importi_rate),
						array(  'name' => 'Scadenze_Rate',           'type' => 'string', 'value' => $scadenze),
						array(  'name' => 'Nominativo_Gestore_Rateizzazione',            'type' => 'string', 'value' => $nominativo_gestore_rateizzazione),
						array(  'name' => 'Posizione_Gestore_Rateizzazione',       'type' => 'string', 'value' => $posizione_gestore_rateizzazione),
						array(  'name' => 'Esito_Richiesta_Rateizzazione',  'type' => 'string', 'value' => $esito_richiesta),
						array(  'name' => 'Operatore_Rateizzazione',  'type' => 'string', 'value' => $operatore)
				),
				'updateField' => array("name" => "ID", "type" => "int", "value" => $ID)
		);
		//$a_paramsDett['updateField'] = array("name" => "Utente_ID", "type" => "int", "value" => $ID);
	/*	$salva = new pignoramento($ID,$c);

		$salva->Rate_Previste = $num_rate;
		$salva->Importi_Rate = $importi_rate;
		$salva->Scadenze_Rate = $scadenze;
		$salva->Nominativo_Gestore_Rateizzazione = $nominativo_gestore_rateizzazione;
		$salva->Posizione_Gestore_Rateizzazione = $posizione_gestore_rateizzazione;
		$salva->Esito_Richiesta_Rateizzazione = $esito_richiesta;*/

		if($esito_richiesta=='respinta')
			array_push($a_paramsPigno['fields'],array("name" => "Motivazione_Respinta_Rateizzazione", "type" => "string", "value" => $motivazione));
			//$salva->Motivazione_Respinta_Rateizzazione = $motivazione;

		//$salva->Operatore_Rateizzazione = $operatore;

		if(!$cls_db->DbSave($a_paramsPigno))
		{
			$cls_db->Rollback();
			$cls_db->End_Transaction();
			echo 'ERROR '.$cls_db->GetError();
			die;
		}
		else
		{
			$cls_db->End_Transaction();
			echo 'OK '.$partita_ID;
			die;
		}

		/*mysql_query('BEGIN');

		$control_salva = $salva->Update( $ID );

		if( $control_salva )
		{
			mysql_query('COMMIT');

			echo 'OK '.$partita_ID;
			die;
		}
		else
		{
			echo 'ERROR '.mysql_error();
			mysql_query('ROLLBACK');
			die;
		}*/



	}

	//PIGNORAMENTO GENERALE
	$data_elaborazione 			= 	$cls_help->getVar('data_elaborazione');
	$anno_elab = explode("/", $data_elaborazione);
	$anno_elab = isset($anno_elab[2])?$anno_elab[2]:null;

	$stato_stampa 				= 	$cls_help->getVar('stato_stampa');
	$data_stampa 				= 	$cls_help->getVar('data_stampa');
	$data_spedizione 			= 	$cls_help->getVar('data_spedizione');
	$data_consegna 				= 	$cls_help->getVar('data_consegna');

	$data_iscrizione_fermo		=	$cls_help->getVar('data_iscrizione_fermo');

	$stato_pignoramento 		=	$cls_help->getVar('stato_pignoramento');
	$data_stato_pignoramento	=	$cls_help->getVar('data_stato_pignoramento');

	$tipo_ufficiale 			= 	$cls_help->getVar('tipo_ufficiale');

	//RATEIZZAZIONE
	$num_rate = $cls_help->getVar('num_rate');
	$rateizza = $cls_help->getVar('rateizza');
	$data_richiesta_rate = $cls_help->getVar('data_richiesta');
	$tipo_tot_rate = $cls_help->getVar('importo_rateizzazione');

	//NOTIFICHE DEBITORE
	$spese_not_debitore					= 	$cls_help->getVar('spese_not_debitore');
	$y=0;
	$control_while=0;
	while($control_while==0)
	{
		$data_not_debitore[$y]				= 	$cls_help->getVar('data_not_debitore_'.$y);
		$stato_not_debitore[$y] 			= 	$cls_help->getVar('stato_not_debitore_'.$y);
		$motivo_not_debitore[$y] 			= 	$cls_help->getVar('motivo_not_debitore_'.$y);
		$modalita_not_debitore[$y] 			= 	$cls_help->getVar('modalita_not_debitore_'.$y);
		$invio_debitore[$y]					= 	$cls_help->getVar('modalita_stampa_debitore_'.$y);
		$ind_validato_debitore[$y] 			= 	$cls_help->getVar('ind_validato_debitore_'.$y);
		$note_not_debitore[$y]				= 	$cls_help->getVar('note_not_debitore_'.$y);

		if($cls_help->getVar('modalita_stampa_debitore_'.($y+1))=="")
			$control_while=1;
		else
			$y++;
	}

	$importo_atto 				= 	$cls_help->getVar('importo_atto');
	$spese_totali_notifica		= 	$cls_help->getVar('spese_totali');
	$spese_debitore 			= 	$cls_help->getVar('spese_debitore');
	$spese_terzi 				= 	$cls_help->getVar('spese_terzi');

	if($cls_help->getVar('spese_accessorie_3')!="0,00")
		$spese_accessorie			=	$cls_help->getVar('spese_accessorie_3');
	else if($cls_help->getVar('spese_accessorie_2')!="0,00")
		$spese_accessorie			=	$cls_help->getVar('spese_accessorie_2');
	else
		$spese_accessorie			=	$cls_help->getVar('spese_accessorie_1');

	if($cls_help->getVar('totale_pignoramento_3')!="0,00")
		$totale_pignoramento			=	$cls_help->getVar('totale_pignoramento_3');
	else if($cls_help->getVar('totale_pignoramento_2')!="0,00")
		$totale_pignoramento			=	$cls_help->getVar('totale_pignoramento_2');
	else
		$totale_pignoramento			=	$cls_help->getVar('totale_pignoramento_1');

	//SPESE ACCESSORIE
	$percentuale 				= 	$cls_help->getVar('percentuale');
	$credito_ingiunzione		= 	$cls_help->getVar('credito_ingiunzione');
	$rimborso_totale 			= 	$cls_help->getVar('rimborso_totale');

	for($i=1;$i<11;$i++)
	{

	$tipo_totale_spesa[$i]	=	$cls_help->getVar('tot_parziale_'.$i);
	$spesa_ID[$i] 			=	$cls_help->getVar('spesa_'.$i);
	$extra_spesa[$i] 		=	$cls_help->getVar('durata_extra_'.$i);
	$rimborso_spesa[$i] 	=	$cls_help->getVar('rimborso_'.$i);
	if($rimborso_spesa[$i]=="")
	$rimborso_spesa[$i] 	=	$cls_help->getVar('rimborso_tantum_'.$i);

	}

	//TIPO PIGNORAMENTO
	$tipo_pignoramento 			= 	$cls_help->getVar('tipo_pignoramento');

	if($tipo_pignoramento!="terzi" && $tipo_pignoramento!="veicolo")
	{
		$spese_terzi = "0,00";
	}

	//PRESSO TERZI
	$presso_terzi 				= 	$cls_help->getVar('presso_terzi');

	if($presso_terzi=="banca")
		$comune_banca			=	$cls_help->getVar('ricerca_banche');
	else
		$comune_banca = "";

	switch($tipo_pignoramento)
	{
		case "terzi":

			$cont_terzi = 0;
			while($cls_help->getVar('pignorato_id_'.$presso_terzi.'_'.$cont_terzi)!=null)
			{
				$cont_terzi++;
			}
			//echo "<h1>count_terzi --> ".$cont_terzi."</h1>";

			if($cont_terzi<30)
				$cont_terzi = 30;

		for($i=0;$i<$cont_terzi;$i++)
		{

			//GENERALI
			$pignorato_id[$i] 			= 	$cls_help->getVar('pignorato_id_'.$presso_terzi.'_'.$i);
			$azienda[$i]				=	$cls_help->getVar('azienda_'.$presso_terzi.'_'.$i);
			$pignorato[$i] 				= 	$cls_help->getVar('pignorato_'.$presso_terzi.'_'.$i);
			$fonte[$i] 					= 	$cls_help->getVar('fonte_'.$presso_terzi.'_'.$i);
			$note[$i] 					= 	$cls_help->getVar('note_'.$presso_terzi.'_'.$i);

			//LAVORO
			$tipo_contratto[$i] 		= 	"";
			$data_costituzione[$i] 		= 	null;
			$data_operativa[$i] 		= 	null;
			$data_dipendenze[$i] 		= 	null;

			//BANCA
			$tipo_titolo[$i] 			= 	"";
			$titolo[$i]					= 	"";
			$intestatario[$i] 			= 	"";
			$coointestatari[$i] 		= 	"";

			//INPS
			$tipo_libretto[$i] 			= 	"";
			$libretto[$i] 				= 	"";

			//ALTRO
			$tipo_credito[$i] 			= 	"";
			$tipo_titolo_credito[$i]	= 	"";
			$titolo_credito[$i]			= 	"";
			$data_emissione[$i] 		= 	null;
			$data_scadenza[$i] 			= 	null;

			switch($presso_terzi)
			{
				case "lavoro":

					$tipo_contratto[$i] 		= 	$cls_help->getVar('tipo_contratto_'.$i);
					$data_costituzione[$i] 		= 	$cls_help->getVar('data_costituzione_'.$i);
					$data_operativa[$i] 		= 	$cls_help->getVar('data_operativa_'.$i);
					$data_dipendenze[$i] 		= 	$cls_help->getVar('data_dipendenze_'.$i);

					break;

				case "banca":

					$tipo_titolo[$i] 			= 	$cls_help->getVar('tipo_titolo_'.$i);
					$titolo[$i]					= 	$cls_help->getVar('titolo_'.$i);
					$intestatario[$i] 			= 	$cls_help->getVar('intestatario_'.$i);
					$coointestatari[$i] 		= 	$cls_help->getVar('coointestatari_'.$i);

					break;

				case "inps":

					$tipo_libretto[$i] 			= 	$cls_help->getVar('tipo_libretto_'.$i);
					$libretto[$i] 				= 	$cls_help->getVar('libretto_'.$i);

					break;

				case "altro":

					$tipo_credito[$i] 			= 	$cls_help->getVar('tipo_credito_'.$i);
					$tipo_titolo_credito[$i]	= 	$cls_help->getVar('tipo_titolo_credito_'.$i);
					$titolo_credito[$i]			= 	$cls_help->getVar('titolo_credito_'.$i);
					$data_emissione[$i] 		= 	$cls_help->getVar('data_emissione_'.$i);
					$data_scadenza[$i] 			= 	$cls_help->getVar('data_scadenza_'.$i);

					break;
			}

			//NOTIFICHE TERZO
			$spese_not[$i]	 				= 	$cls_help->getVar('spese_not_terzo_'.$i);
			$y=0;
			$control_while=0;
			while($control_while==0)
			{
				$data_not[$i][$y]				= 	$cls_help->getVar('data_not_terzo_'.$i.'_'.$y);
				$stato_not[$i][$y] 				= 	$cls_help->getVar('stato_not_terzo_'.$i.'_'.$y);
				$motivo_not[$i][$y] 			= 	$cls_help->getVar('motivo_not_terzo_'.$i.'_'.$y);
				$modalita_not[$i][$y] 			= 	$cls_help->getVar('modalita_not_terzo_'.$i.'_'.$y);
				$modalita_stampa_terzo[$i][$y]	= 	$cls_help->getVar('modalita_stampa_terzo_'.$i.'_'.$y);
				$ind_validato_terzo[$i][$y] 	= 	$cls_help->getVar('ind_validato_terzo_'.$i.'_'.$y);
				$note_not_terzo[$i][$y]			= 	$cls_help->getVar('note_not_terzo_'.$i.'_'.$y);

				if($cls_help->getVar('modalita_stampa_terzo_'.$i.'_'.($y+1))=="")
					$control_while=1;
				else
					$y++;
			}

		}

		break;

		case "preav_fermo":

			for($i=0;$i<3;$i++)
			{
				$marca_preav_fermo[$i]					= 	$cls_help->getVar('marca_preav_fermo_'.$i);
				$modello_preav_fermo[$i] 				= 	$cls_help->getVar('modello_preav_fermo_'.$i);
				$tipo_preav_fermo[$i]					= 	$cls_help->getVar('tipo_preav_fermo_'.$i);
				$targa_preav_fermo[$i] 					= 	$cls_help->getVar('targa_preav_fermo_'.$i);
				$data_visura_preav_fermo[$i]			= 	$cls_date->GetDateDB($cls_help->getVar('data_visura_preav_fermo_'.$i),"IT");
				$portata_preav_fermo[$i] 				= 	$cls_help->getVar('portata_preav_fermo_'.$i);
				$valore_preav_fermo[$i] 				= 	$cls_help->getVar('valore_preav_fermo_'.$i);
				$anno_immatricolazione_preav_fermo[$i]	= 	$cls_help->getVar('anno_immatricolazione_preav_fermo_'.$i);
				$fonte_dati_preav_fermo[$i]				= 	$cls_help->getVar('fonte_dati_preav_fermo_'.$i);
				$id_veicolo_preav_fermo[$i]				= 	$cls_help->getVar('id_veicolo_preav_fermo_'.$i);
			}

			break;

		case "fermo":

			for($i=0;$i<3;$i++)
			{
				$marca_fermo[$i]					= 	$cls_help->getVar('marca_fermo_'.$i);
				$modello_fermo[$i] 					= 	$cls_help->getVar('modello_fermo_'.$i);
				$tipo_fermo[$i]						= 	$cls_help->getVar('tipo_fermo_'.$i);
				$targa_fermo[$i] 					= 	$cls_help->getVar('targa_fermo_'.$i);
				$data_visura_fermo[$i]				= 	$cls_date->GetDateDB($cls_help->getVar('data_visura_fermo_'.$i),"IT");
				$portata_fermo[$i] 					= 	$cls_help->getVar('portata_fermo_'.$i);
				$valore_fermo[$i] 					= 	$cls_help->getVar('valore_fermo_'.$i);
				$anno_immatricolazione_fermo[$i]	= 	$cls_help->getVar('anno_immatricolazione_fermo_'.$i);
				$fonte_dati_fermo[$i]				= 	$cls_help->getVar('fonte_dati_fermo_'.$i);
				$id_veicolo_fermo[$i]				= 	$cls_help->getVar('id_veicolo_fermo_'.$i);
			}

			break;


		case "veicolo":

			for($i=0;$i<3;$i++)
			{
				$marca_veicolo[$i]					= 	$cls_help->getVar('marca_veicolo_'.$i);
				$modello_veicolo[$i] 				= 	$cls_help->getVar('modello_veicolo_'.$i);
				$data_fermo_veicolo[$i]             = 	$cls_help->getVar('data_fermo_veicolo_'.$i);
				$tipo_veicolo[$i]					= 	$cls_help->getVar('tipo_veicolo_'.$i);
				$targa_veicolo[$i] 					= 	$cls_help->getVar('targa_veicolo_'.$i);
				$telaio_veicolo[$i] 				= 	$cls_help->getVar('telaio_veicolo_'.$i);
				$data_visura_veicolo[$i]			= 	$cls_date->GetDateDB($cls_help->getVar('data_visura_veicolo_'.$i),"IT");
				$portata_veicolo[$i] 				= 	$cls_help->getVar('portata_veicolo_'.$i);
				$valore_veicolo[$i] 				= 	$cls_help->getVar('valore_veicolo_'.$i);
				$anno_immatricolazione_veicolo[$i]	= 	$cls_help->getVar('anno_immatricolazione_veicolo_'.$i);
				$fonte_dati_veicolo[$i]				= 	$cls_help->getVar('fonte_dati_veicolo_'.$i);
				$id_veicolo_veicolo[$i]				= 	$cls_help->getVar('id_veicolo_veicolo_'.$i);
			}


			//NOTIFICHE VEICOLO
			$spese_not_veicolo 				= 	$cls_help->getVar('spese_not_veicolo');
			$y=0;
			$control_while=0;
			while($control_while==0)
			{
				$data_not_veicolo[$y]				= 	$cls_help->getVar('data_not_veicolo_'.$y);
				$stato_not_veicolo[$y] 				= 	$cls_help->getVar('stato_not_veicolo_'.$y);
				$motivo_not_veicolo[$y] 			= 	$cls_help->getVar('motivo_not_veicolo_'.$y);
				$modalita_not_veicolo[$y] 			= 	$cls_help->getVar('modalita_not_veicolo_'.$y);
				$invio_veicolo[$y]					= 	$cls_help->getVar('modalita_stampa_veicolo_'.$y);
				$ind_validato_veicolo[$y] 			= 	$cls_help->getVar('ind_validato_veicolo_'.$y);
				$note_not_veicolo[$y]				= 	$cls_help->getVar('note_not_veicolo_'.$y);

				if($cls_help->getVar('modalita_stampa_veicolo_'.($y+1))=="")
					$control_while=1;
				else
					$y++;
			}

			break;

		case "immobiliare":

			for($i=0;$i<3;$i++)
			{
				$Tipo_Immobiliare[$i] 										= 	$cls_help->getVar('tipo_immobiliare_'.$i);
				$Situazione_Immobiliare[$i] 								= 	$cls_help->getVar('situazione_immobiliare_'.$i);
				$Foglio_Immobiliare[$i] 									= 	$cls_help->getVar('foglio_immobiliare_'.$i);
				$Particella_Immobiliare[$i] 								= 	$cls_help->getVar('particella_immobiliare_'.$i);
				$Subalterno_Immobiliare[$i] 								= 	$cls_help->getVar('subalterno_immobiliare_'.$i);
			if($Tipo_Immobiliare[$i]=="fabbricato")
				$Classe_Immobiliare[$i] 									= 	$cls_help->getVar('classe_fabbricato_'.$i);
			else
				$Classe_Immobiliare[$i] 									= 	$cls_help->getVar('classe_terreno_'.$i);
				$Annotazioni_Immobiliare[$i] 								= 	$cls_help->getVar('annotazioni_immobiliare_'.$i);

				$Sezione_Fabbricato_Immobiliare[$i] 						= 	"";
				$Zona_Censuaria_Fabbricato_Immobiliare[$i] 					= 	"";
				$Categoria_Fabbricato_Immobiliare[$i] 						= 	"";
				$Consistenza_Fabbricato_Immobiliare[$i] 					= 	"";
				$Superficie_Fabbricato_Immobiliare[$i] 						= 	"";
				$Rendita_Fabbricato_Immobiliare[$i] 						= 	"";
				$Indirizzo_Fabbricato_Immobiliare[$i] 						= 	"";
				$Protocollo_Notifica_Fabbricato_Immobiliare[$i] 			= 	"";
				$Porzione_Terreno_Immobiliare[$i] 							= 	"";
				$Qualita_Terreno_Immobiliare[$i] 							= 	"";
				$Descrizione_Qualita_Terreno_Immobiliare[$i] 				= 	"";
				$HA_Ettari_Terreno_Immobiliare[$i] 							= 	"";
				$A_Are_Terreno_Immobiliare[$i] 								= 	"";
				$C_Centiare_Terreno_Immobiliare[$i] 						= 	"";
				$Dominicale_Terreno_Immobiliare[$i] 						= 	"";
				$Agrario_Terreno_Immobiliare[$i] 							= 	"";
				$Deduzioni_Terreno_Immobiliare[$i] 							= 	"";

			if($Tipo_Immobiliare[$i]=="fabbricato")
			{
				$Sezione_Fabbricato_Immobiliare[$i] 						= 	$cls_help->getVar('sezione_fabbricato_'.$i);
				$Zona_Censuaria_Fabbricato_Immobiliare[$i] 					= 	$cls_help->getVar('zona_censuaria_fabbricato_'.$i);
				$Categoria_Fabbricato_Immobiliare[$i] 						= 	$cls_help->getVar('categoria_fabbricato_'.$i);
				$Consistenza_Fabbricato_Immobiliare[$i] 					= 	$cls_help->getVar('consistenza_fabbricato_'.$i);
				$Superficie_Fabbricato_Immobiliare[$i] 						= 	$cls_help->getVar('superficie_fabbricato_'.$i);
				$Rendita_Fabbricato_Immobiliare[$i] 						= 	$cls_help->getVar('rendita_fabbricato_'.$i);
				$Indirizzo_Fabbricato_Immobiliare[$i] 						= 	$cls_help->getVar('indirizzo_fabbricato_'.$i);
				$Protocollo_Notifica_Fabbricato_Immobiliare[$i] 			= 	$cls_help->getVar('protocollo_notifica_fabbricato_'.$i);
			}
			else if($Tipo_Immobiliare[$i]=="terreno")
			{
				$Porzione_Terreno_Immobiliare[$i] 							= 	$cls_help->getVar('porzione_terreno_'.$i);
				$Qualita_Terreno_Immobiliare[$i] 							= 	$cls_help->getVar('qualita_terreno_'.$i);
				$Descrizione_Qualita_Terreno_Immobiliare[$i] 				= 	$cls_help->getVar('descrizione_qualita_terreno_'.$i);
				$HA_Ettari_Terreno_Immobiliare[$i] 							= 	$cls_help->getVar('HA_terreno_'.$i);
				$A_Are_Terreno_Immobiliare[$i] 								= 	$cls_help->getVar('A_terreno_'.$i);
				$C_Centiare_Terreno_Immobiliare[$i] 						= 	$cls_help->getVar('C_terreno_'.$i);
				$Dominicale_Terreno_Immobiliare[$i] 						= 	$cls_help->getVar('dominicale_terreno_'.$i);
				$Agrario_Terreno_Immobiliare[$i] 							= 	$cls_help->getVar('agrario_terreno_'.$i);
				$Deduzioni_Terreno_Immobiliare[$i] 							= 	$cls_help->getVar('deduzioni_terreno_'.$i);
			}

				$Parte_Proprietario_Immobiliare[$i] 						= 	$cls_help->getVar('parte_proprietario_'.$i);
				$Totale_Proprietario_Immobiliare[$i] 						= 	$cls_help->getVar('totale_proprietario_'.$i);

			}

			break;
	}

	$query = "SELECT * FROM partita_tributi WHERE ID = '".$partita_ID."' AND CC = '".$c."'";
	$partita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi");

	//$partita = new partita($partita_ID,$c);
	$query = "SELECT * FROM utente WHERE ID = '".$partita["Utente_ID"]."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
	$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");

	$query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente["Forma_Giuridica"]."' AND CC = '".$c."'";
	$utente["Object_Forma_Giuridica"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa");

	$utente["Sigla_Forma_Giuridica"] = $utente["Object_Forma_Giuridica"]["Sigla"];


	$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$partita["Utente_ID"]."' AND Tipo = 'res'";
	$utente["Residenza"] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	if(isset($utente["Residenza"]["Via_ID"])?$utente["Residenza"]["Via_ID"]:1!=1)
	{
		$query = "SELECT * FROM toponimo WHERE ID = '".$utente["Residenza"]["Via_ID"]."' AND CC_Comune = '".$c."'";
		$temp = $cls_db->ExecuteQuery($query);
		if($cls_db->getNumberRow($temp)>0)
			$utente["Residenza"]["Toponimo"] = $cls_db->getArrayLine($temp);//new toponimo( $utente["Residenza"]["Via_ID"] , $c );
	}
	else if(isset($utente["Residenza"]["Via_Cap_ID"])?$utente["Residenza"]["Via_Cap_ID"]:1!=1)
	{
		$query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente["Residenza"]["Via_Cap_ID"]."'";
		$temp = $cls_db->ExecuteQuery($query);
		if($cls_db->getNumberRow($temp)>0)
			$utente["Residenza"]["Toponimo"] = $cls_db->getArrayLine($temp);//new toponimo_cap( $utente["Residenza"]["Via_Cap_ID"] );
	}
	else
	  $utente["Residenza"]["Toponimo"] = null;



	$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$partita["Utente_ID"]."' AND Tipo = 'dom'";
	$utente["Domicilio"] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));


	if(isset($utente["Domicilio"]["Via_ID"])?$utente["Domicilio"]["Via_ID"]:1!=1)
	{

		$query = "SELECT * FROM toponimo WHERE ID = '".$utente["Domicilio"]["Via_ID"]."' AND CC_Comune = '".$c."'";
		$temp = $cls_db->ExecuteQuery($query);
		if($cls_db->getNumberRow($temp)>0)
			$utente["Domicilio"]["Toponimo"] = $cls_db->getArrayLine($temp);//new toponimo( $utente["Residenza"]["Via_ID"] , $c );
	}
	else if(isset($utente["Domicilio"]["Via_Cap_ID"])?$utente["Domicilio"]["Via_Cap_ID"]:1!=1)
	{
		$query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente["Domicilio"]["Via_Cap_ID"]."'";
		$temp = $cls_db->ExecuteQuery($query);
		if($cls_db->getNumberRow($temp)>0)
			$utente["Domicilio"]["Toponimo"] = $cls_db->getArrayLine($temp);//new toponimo_cap( $utente["Residenza"]["Via_Cap_ID"] );
	}
	else
		$utente["Domicilio"]["Toponimo"] = null;



	$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$partita["Utente_ID"]."' AND Tipo = 'rec'";
	$utente["Recapito"] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$utente = new utente($partita["Utente_ID"],$c);

	if(isset($utente["Recapito"]["Via_ID"])?$utente["Recapito"]["Via_ID"]:1!=1)
	{

		$query = "SELECT * FROM toponimo WHERE ID = '".$utente["Recapito"]["Via_ID"]."' AND CC_Comune = '".$c."'";
		$temp = $cls_db->ExecuteQuery($query);
		if($cls_db->getNumberRow($temp)>0)
			$utente["Recapito"]["Toponimo"] = $cls_db->getArrayLine($temp);//new toponimo( $utente["Residenza"]["Via_ID"] , $c );
	}
	else if(isset($utente["Recapito"]["Via_Cap_ID"])?$utente["Recapito"]["Via_Cap_ID"]:1!=1)
	{
		$query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente["Recapito"]["Via_Cap_ID"]."'";
		$temp = $cls_db->ExecuteQuery($query);
		if($cls_db->getNumberRow($temp)>0)
			$utente["Recapito"]["Toponimo"] = $cls_db->getArrayLine($temp);//new toponimo_cap( $utente["Residenza"]["Via_Cap_ID"] );
	}
	else
		$utente["Recapito"]["Toponimo"] = null;

		$query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$utente["Residenza"]["CC_Indirizzo"]."' AND Tipo = 'tribunale' LIMIT 1";
		$tribunale = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new ufficio_giudiziario($utente["Residenza"]["CC_Indirizzo"], "tribunale");

	//PARAMETRI RESPONSABILI
	$query = "SELECT * FROM parametri_responsabili WHERE CC = '".$c."' AND Tipo_Riscossione = 'CDS'";
	$ParametriResp = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	//$par_responsabili = new parametri_responsabili($c, "CDS");
	$firma_resp = $cls_coazione->carica_firme("Funzionario", "Responsabile", "Ufficiale",$ParametriResp);

	//CARICAMENTO TESTO RELATA

	switch($tipo_pignoramento)
	{
		case "terzi":

			switch($presso_terzi)
			{
				case "lavoro":
					$stampa_dir = $cls_Utils->crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Datore_di_Lavoro/STAMPE DEFINITIVE" );
					$tipo_pigno_nome_file = "presso_lavoro";
					//$para_pigno = new testo_pignoramento_presso_lavoro(NULL);
					$myId = $cls_coazione->CercaParametroData($c, date("Y-m-d"),"si");
					$testo = $cls_coazione->value_TestoPignoramento($myId,$tipo_pignoramento,$presso_terzi);//new testo_pignoramento_presso_lavoro($myId);
					break;

				case "banca":
					$stampa_dir = $cls_Utils->crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Banca/STAMPE DEFINITIVE" );
					$tipo_pigno_nome_file = "presso_banca";
					//$para_pigno = new testo_pignoramento_presso_banca(NULL);
					$myId = $cls_coazione->CercaParametroData($c, date("Y-m-d"),"si");
					$testo = $cls_coazione->value_TestoPignoramento($myId,$tipo_pignoramento,$presso_terzi);//new testo_pignoramento_presso_banca($myId);
					break;
					default : $testo = array("Intestazione_Relata_Ufficiale_Giudiziario"=>null,"Sottointestazione_Relata_Ufficiale_Giudiziario"=>null,"Intestazione_Relata_Ufficiale_Riscossione"=>null,"Sottointestazione_Relata_Ufficiale_Riscossione"=>null,"Relata_Debitore"=>null,"Relata_Notifica"=>null,"Relata_Terzo"=>null); break;
			}

		break;

		case "veicolo":
			$stampa_dir = $cls_Utils->crea_dir( ATTI ."/". $c . "/Pignoramenti/Veicolo/STAMPE DEFINITIVE" );
			$tipo_pigno_nome_file = "veicolo";
			//$para_pigno = new testo_pignoramento_veicolo(NULL);
			$myId = $cls_coazione->CercaParametroData($c, date("Y-m-d"),"si");
			$testo = $cls_coazione->value_TestoPignoramento($myId,$tipo_pignoramento);//new testo_pignoramento_veicolo($myId);
			break;

			case "preav_fermo" :
				$stampa_dir = $cls_Utils->crea_dir( ATTI ."/". $c . "/Pignoramenti/preav_fermo/STAMPE DEFINITIVE" );
				$tipo_pigno_nome_file = "Preavviso_fermo";

				$myId = $cls_coazione->CercaParametroData($c, date("Y-m-d"),"si");
				$testo = $cls_coazione->value_TestoPignoramento($myId,$tipo_pignoramento);
			 break;
			 case "fermo" :
				 $stampa_dir = $cls_Utils->crea_dir( ATTI ."/". $c . "/Pignoramenti/fermo/STAMPE DEFINITIVE" );
				 $tipo_pigno_nome_file = "fermo";

				 $myId = $cls_coazione->CercaParametroData($c, date("Y-m-d"),"si");
				 $testo = $cls_coazione->value_TestoPignoramento($myId,$tipo_pignoramento);
			 break;
			case "immobiliare" : /*new_testo_preavviso_fermo*/// break;
			default : $testo = array("Intestazione_Relata_Ufficiale_Giudiziario"=>null,"Sottointestazione_Relata_Ufficiale_Giudiziario"=>null,"Intestazione_Relata_Ufficiale_Riscossione"=>null,"Sottointestazione_Relata_Ufficiale_Riscossione"=>null,"Relata_Debitore"=>null,"Relata_Notifica"=>null,"Relata_Terzo"=>null); break;
	}

	$Intestazione_Relata_Ufficiale_Giudiziario = isset($testo["Intestazione_Relata_Ufficiale_Giudiziario"])?$testo["Intestazione_Relata_Ufficiale_Giudiziario"]:null;
	$cls_Utils->SostituisciTestoTraGraffe ($Intestazione_Relata_Ufficiale_Giudiziario, "{TRIBUNALE}", ucfirst(isset($tribunale["Comune"])?$tribunale["Comune"]:null));
	$Sottointestazione_Relata_Ufficiale_Giudiziario = isset($testo["Sottointestazione_Relata_Ufficiale_Giudiziario"])?$testo["Sottointestazione_Relata_Ufficiale_Giudiziario"]:null;

	$Intestazione_Relata_Ufficiale_Riscossione = isset($testo["Intestazione_Relata_Ufficiale_Riscossione"])?$testo["Intestazione_Relata_Ufficiale_Riscossione"]:null;
	$Sottointestazione_Relata_Ufficiale_Riscossione = isset($testo["Sottointestazione_Relata_Ufficiale_Riscossione"])?$testo["Sottointestazione_Relata_Ufficiale_Riscossione"]:null;


	if($tipo_ufficiale == "giudiziario")
	{
		$Intestazione_Relata = $Intestazione_Relata_Ufficiale_Giudiziario;
		$Sottointestazione_Relata = $Sottointestazione_Relata_Ufficiale_Giudiziario;
		$testo_ufficiale = "Ufficiale Giudiziario addetto all'U.N.E.P. del Circondario del Tribunale di ".ucfirst($tribunale["Comune"]);
	}
	else if($tipo_ufficiale == "riscossione")
	{
		$Intestazione_Relata = $Intestazione_Relata_Ufficiale_Riscossione;
		$Sottointestazione_Relata = $Sottointestazione_Relata_Ufficiale_Riscossione;
		if($gestore["Tipo"] == "Concessionario")
			$denom_gestore = $gestore["Tipo"]." ".$gestore["Denominazione"];
		else
			$denom_gestore = $gestore["Denominazione"];

		$testo_ufficiale = "Ufficiale della Riscossione, su delega del ".$denom_gestore;
	}

	$Relata_Notifica = isset($testo["Relata_Notifica"])?$testo["Relata_Notifica"]:null;
	$cls_Utils->SostituisciTestoTraGraffe ($Relata_Notifica, "{UFFICIALE}", isset($testo_ufficiale)?$testo_ufficiale:null);
	$cls_Utils->SostituisciTestoTraGraffe ($Relata_Notifica, "{NOTIFICATO}", "rinotificato");


	$nome_utente = $utente["Cognome"].$utente["Ditta"]." ".$utente["Nome"].$utente["Object_Forma_Giuridica"]["Sigla"];

	$indirizzo_destinatario = $cls_coazione->righe_indirizzo($utente);
	$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];
	$Relata_Debitore = isset($testo["Relata_Debitore"])?$testo["Relata_Debitore"]:null;
	$cls_Utils->SostituisciTestoTraGraffe ($Relata_Debitore, "{UTENTE}", $nome_utente);
	$cls_Utils->SostituisciTestoTraGraffe ($Relata_Debitore, "{RESIDENZAUTENTE}", $indirizzo_senza_provincia);

	if($tipo_ufficiale == "giudiziario")
	{
		$firma_ufficiale['intestazione'] 	= "L'Ufficiale Giudiziario";
		$firma_ufficiale['nome'] 			= "";
		$firma_ufficiale['firma'] 			= "";

		$firma_ufficiale_copia['intestazione'] 	= "L'Ufficiale Giudiziario";
		$firma_ufficiale_copia['nome'] 			= "";
		$firma_ufficiale_copia['firma'] 		= "";
	}
	else if($tipo_ufficiale == "riscossione")
	{
		$firma_ufficiale['intestazione'] 	= "L'Ufficiale della Riscossione";
		$firma_ufficiale['nome'] 			= "";
		$firma_ufficiale['firma'] 			= "";

		$firma_ufficiale_copia['intestazione'] = $firma_resp[3]['intestazione'];
		$firma_ufficiale_copia['nome'] = $firma_resp[3]['nome'];
		$firma_ufficiale_copia['firma'] = $firma_resp[3]['firma'];
	}

	//INIZIO TRANSACTION
	//mysql_query('BEGIN');
	$salva_pigno_gen_for_funz = array();

	$query = "SELECT MAX(Comune_ID) as Com FROM pignoramento_generale WHERE CC = '".$c."'";
	$comune_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	$query = "SELECT MAX(ID_Cronologico) as Com FROM pignoramento_generale WHERE CC = '".$c."' AND Anno_Cronologico = '".$anno_elab."'";
	$crono_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	//SALVATAGGIO PIGNORAMENTO GENERALE
	$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_ID." AND CC = '".$c."'";
	$salva_pigno_gen = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");

	$query = "SELECT * FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$pignoramento_ID."' AND CC = '".$c."'";
	$salva_pigno_gen_for_funz["Veicolo"] = $cls_db->GetResults($cls_db->ExecuteQuery($query));

	$where_notifica_istituto = "CC = '".$c."' AND Atto_Notificato_ID = '".$pignoramento_ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'veicolo'";
	$Notifica_Istituto = $cls_db->GetResults($cls_db->ExecuteQuery("SELECT * FROM notifica_atto WHERE ".$where_notifica_istituto));// select_mysql_array("ID", "notifica_atto" , $where_notifica_istituto);

	$query = "SELECT * FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$pignoramento_ID."' AND CC = '".$c."'";
	$Preavviso_Fermo = $cls_db->GetResults($cls_db->ExecuteQuery($query));

	//$fermo_id = select_mysql_array("ID", "pignoramento_veicolo" , "Pignoramento_ID = '".$pignoramento_ID."' AND CC = '".$c."'");
	$query = "SELECT * FROM pignoramento_veicolo WHERE Pignoramento_ID = '".$pignoramento_ID."' AND CC = '".$c."'";
	$Fermo = $cls_db->GetResults($cls_db->ExecuteQuery($query));

	$query = "SELECT * FROM pignoramento_immobiliare WHERE Pignoramento_ID = '".$pignoramento_ID."' AND CC = '".$c."'";
	$Immobiliare = $cls_db->GetResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "pignoramento_immobiliare" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'");

	/*for( $i=0; $i<count($immobiliare_id); $i++ )
	{
		$this->Immobiliare[$i] = new pignoramento_immobiliare( $immobiliare_id[$i]['ID'] , $c );
	}*/
	/*for( $i=0; $i<count($fermo_id); $i++ )
	{
		$this->Fermo[$i] = new pignoramento_veicolo( $fermo_id[$i]['ID'] , $c );
	}*/


	$where_notifica_sollecito = "CC = '".$c."' AND Atto_Notificato_ID = '".$pignoramento_ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'sollecito'";
	$notifica_sollecito_id = $cls_db->GetResults($cls_db->ExecuteQuery("SELECT ID FROM notifica_atto WHERE ".$where_notifica_sollecito));//select_mysql_array("ID", "notifica_atto" , $where_notifica_sollecito);

	for( $i=0; $i<count($notifica_sollecito_id); $i++ )
	{
		$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_sollecito_id[$i]['ID']."' AND CC = '".$c."'";
		$Notifica_Sollecito[$i] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new notifica_atto( $notifica_sollecito_id[$i]['ID'] , $c );
	}

	$query = "SELECT ID FROM pignoramento_presso_terzi WHERE Pignoramento_ID = '".$pignoramento_ID."' AND CC = '".$c."' ORDER BY ID ASC";
	$terzi_id = $cls_db->GetResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "pignoramento_presso_terzi" , "Pignoramento_ID = '".$progr."' AND CC = '".$c."'","ID");


	for( $i=0; $i<count($terzi_id); $i++ )
	{
		//echo "<h1>count ".count($terzi_id)."</h1></br>";
		$query = "SELECT * FROM pignoramento_presso_terzi WHERE ID = '".$terzi_id[$i]['ID']."' AND CC = '".$c."'";
		$salva_pigno_gen_for_funz["Presso_Terzi"][$i] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new pignoramento_presso_terzi( $terzi_id[$i]['ID'] , $c );

		if(isset($salva_pigno_gen["Presso_Terzi"][$i]["ID"]))
		{
			$where_notifica_terzo = "CC = '".$c."' AND Atto_Notificato_ID = '".$salva_pigno_gen["Presso_Terzi"][$i]["Pignoramento_ID"]."' AND ID_Collegamento = '".$salva_pigno_gen["Presso_Terzi"][$i]["ID"]."'";
			$where_notifica_terzo.= "AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'terzi'";

			$notifica_terzo_id = $cls_db->GetResults($cls_db->ExecuteQuery("SELECT ID FROM notifica_atto WHERE ".$where_notifica_terzo." ORDER BY ID ASC"));//select_mysql_array("ID", "notifica_atto" , $where_notifica_terzo,"ID","ASC");

			//echo "<h1>count atto ".count($notifica_terzo_id)." - ".$i."</h1><br>";
			for($y=0;$y<count($notifica_terzo_id);$y++)
			{
				$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_terzo_id[$y]['ID']."' AND CC = '".$c."'";
				$salva_pigno_gen_for_funz["Presso_Terzi"][$y]["Notifiche_Terzo"][$y] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new notifica_atto( $notifica_terzo_id[$i]['ID'] , $c );
			}
		}
}

$where_notifica_debitore = "CC = '".$c."' AND Atto_Notificato_ID = '".$pignoramento_ID."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'debitore'";
$notifica_debitore_id = $cls_db->GetResults($cls_db->ExecuteQuery("SELECT ID FROM notifica_atto WHERE ".$where_notifica_debitore)); //select_mysql_array("ID", "notifica_atto" , $where_notifica_debitore);
for($i=0;$i<count($notifica_debitore_id);$i++)
{
	$query = "SELECT * FROM notifica_atto WHERE ID = '".$notifica_debitore_id[$i]['ID']."' AND CC = '".$c."'";
	$Notifiche_Debitore[$i] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new notifica_atto( $notifica_debitore_id[$i]['ID'] , $c );
}

	//$salva_pigno_gen = new pignoramento( $pignoramento_ID , $c );

	$salva_pigno_gen["CC"] = $c;
	$salva_pigno_gen["Partita_ID"] = $partita_ID;
	$salva_pigno_gen["Atto_ID"] = $atto_ID;
	$salva_pigno_gen["Data_Elaborazione"] = $cls_date->GetDateDB($data_elaborazione,"IT");// to_mysql_date($data_elaborazione);
	$salva_pigno_gen["Tipo_Terzi"] = $presso_terzi;
	$salva_pigno_gen["Comune_Banca"] = $comune_banca;
	$salva_pigno_gen["Tipo"] = $tipo_pignoramento;
	$salva_pigno_gen["Data_Stampa"] = $cls_date->GetDateDB($data_stampa,"IT");// to_mysql_date($data_stampa);
	$salva_pigno_gen["Data_Consegna"] = $cls_date->GetDateDB($data_consegna,"IT");// to_mysql_date($data_consegna);
	$salva_pigno_gen["Tipo_Ufficiale"] = $tipo_ufficiale;

	$salva_pigno_gen["Data_Iscrizione_Fermo"] = $cls_date->GetDateDB($data_iscrizione_fermo,"IT");// to_mysql_date($data_iscrizione_fermo);

	$salva_pigno_gen["Stato_Pignoramento"] = $stato_pignoramento;
	$salva_pigno_gen["Data_Stato_Pignoramento"] = $cls_date->GetDateDB($data_stato_pignoramento,"IT");// to_mysql_date($data_stato_pignoramento);

	$salva_pigno_gen["Importo_Dovuto"] = $cls_math->conv_num($importo_atto);
	$salva_pigno_gen["Spese_Notifica_Debitore"] = $cls_math->conv_num($spese_debitore);
	$salva_pigno_gen["Spese_Notifica_Terzi"] = $cls_math->conv_num($spese_terzi);
	$salva_pigno_gen["Totale_Spese_Notifica"] =  $cls_math->conv_num( $spese_debitore) +  $cls_math->conv_num( $spese_terzi);
	$salva_pigno_gen["Totale_Spese_Accessorie"] = $cls_math->conv_num($spese_accessorie);
	$salva_pigno_gen["Totale_Dovuto"] = $cls_math->conv_num($totale_pignoramento);
    $salva_pigno_gen["PrinterId"] = $PrinterId;
	$control_rate = $salva_pigno_gen["Rate_Previste"];
	if($rateizza == "rateizza")
	{
		$salva_pigno_gen["Data_Richiesta_Rate"] = $cls_date->GetDateDB($data_richiesta_rate,"IT");// to_mysql_date($data_richiesta_rate);
		if( $num_rate!=null && $num_rate != $control_rate )
		{
			$importo_bloccato = $cls_coazione->importiRiscontri($salva_pigno_gen_for_funz,$salva_pigno_gen["Tipo"]);

			$not_sollecito = isset($Notifica_Sollecito)?$Notifica_Sollecito:null;
			$spese_solleciti = 0;
			for($i=0;$i<count($not_sollecito);$i++)
				$spese_solleciti+=$not_sollecito[$i]["Spese_Notifica"];

			$totale_dovuto_rate = $salva_pigno_gen["Totale_Dovuto"];

			$salva_pigno_gen["Tipo_Totale_Rate"] = $tipo_tot_rate;
			if($tipo_tot_rate == 1)
				$totale_dovuto_rate = $cls_math->conv_num($cls_help->getVar('totale_pignoramento_1'));
			else if($tipo_tot_rate == 2)
				$totale_dovuto_rate = $cls_math->conv_num($cls_help->getVar('totale_pignoramento_2'));
			else
				$totale_dovuto_rate = $cls_math->conv_num($cls_help->getVar('totale_pignoramento_3'));

			$totale_dovuto_rate+= $spese_solleciti - $importo_bloccato;

			$importo = round ($totale_dovuto_rate / $num_rate, 2 );
			$prima_rata = floatval( $importo + ( $totale_dovuto_rate - $importo * $num_rate ) );

			$importi_rate = number_format($prima_rata,2,",","");
			$importo = number_format($importo,2,",","");
			for($i=1;$i<$num_rate;$i++)
				$importi_rate .= "*".$importo;

			$data_partenza = date('Y-m-d', strtotime("-1 months +10 days"));
			$scadenze = $cls_Utils->next_months( $data_partenza , $num_rate);

			$salva_pigno_gen["Rate_Previste"] = $num_rate;
			$salva_pigno_gen["Importi_Rate"] = $importi_rate;
			$salva_pigno_gen["Scadenze_Rate"] = $scadenze;

		}

	}
	else
	{
		$salva_pigno_gen["Tipo_Totale_Rate"] = 0;
		$salva_pigno_gen["Rate_Previste"] = "";
		$salva_pigno_gen["Importi_Rate"] = "";
		$salva_pigno_gen["Scadenze_Rate"] = "";
		$salva_pigno_gen["Data_Richiesta_Rate"] = null;
		$salva_pigno_gen["ID_Richiesta_Rateizzazione"] = 0;
		$salva_pigno_gen["ID_Esito_Rateizzazione"] = 0;
		$salva_pigno_gen["ID_Bollettini_Rateizzazione"] = 0;
	}


	if($pignoramento_ID=='null')
	{

		$pigno_nuovo = "si";
		$control_salvataggio = "insert";
		$salva_pigno_gen["Comune_ID"] = $comune_id["Com"] + 1;
		$salva_pigno_gen["Anno_Cronologico"] = "0";
		$salva_pigno_gen["ID_Cronologico"] = "0";
		$salva_pigno_gen["Stato_Stampa"] = "Da stampare";

		/*$allIDVeicolo = "";
		for($z=0; $z<3; $z++){


			switch($tipo_pignoramento)
			{
				case "veicolo":	if($id_veicolo_veicolo[$z]!=null && $id_veicolo_veicolo[$z]!="") {
					if($z>0) $allIDVeicolo .= "*";
				  $allIDVeicolo .= $id_veicolo_veicolo[$z];
				}
					break;
				case "preav_fermo": if($id_veicolo_preav_fermo[$z]!=null && $id_veicolo_preav_fermo[$z]!=""){
					if($z>0) $allIDVeicolo .= "*";
					  $allIDVeicolo .= $id_veicolo_preav_fermo[$z];
					} break;
				case "fermo": if($id_veicolo_fermo[$z]!=null && $id_veicolo_fermo[$z]!="") {
					if($z>0) $allIDVeicolo .= "*";
					$allIDVeicolo .= $id_veicolo_fermo[$z];
				} break;
			}
		}
		$salva_pigno_gen["Veicolo_ID"] = $allIDVeicolo;*/

		$id_notifiche_debitore = null;

		/*$a_paramsPignoGen = array(
				'table' => 'pignoramento_generale',
				'fields'=> array(
				)
		);
		foreach($salva_pigno_gen as $key => $val) {
		  $Type = is_numeric($val)?"int":"string";
		  if($val=='null'){$Type = "int"; $val="";}
		  //echo $Type.": $key = $val<br>";
			array_push($a_paramsPignoGen['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
		}*/
		$a_paramsPignoGen = $cls_Utils->GetObjectQuery($salva_pigno_gen,"pignoramento_generale");

		$pignoramento_ID = $cls_db->DbSave($a_paramsPignoGen);


		if(!$pignoramento_ID)
		{
			$error = 1;
			$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
			$cls_db->Rollback();
			header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			die;
		}else {
			for($z=0; $z<3; $z++){
				//if($z>0) $allIDVeicolo .= "*";

				switch($tipo_pignoramento)
				{
					case "veicolo":
					if($id_veicolo_veicolo[$z]!=null&&$id_veicolo_veicolo[$z]!=""){
						$query = "UPDATE veicoli SET Pignoramento_ID = ".$pignoramento_ID." WHERE ID = ".$id_veicolo_veicolo[$z];
						$cls_db->ExecuteQuery($query);
					}
					break;
					case "preav_fermo":
					if($id_veicolo_preav_fermo[$z]!=null&&$id_veicolo_preav_fermo[$z]!=""){
						$query = "UPDATE veicoli SET Pignoramento_ID = ".$pignoramento_ID." WHERE ID = ".$id_veicolo_preav_fermo[$z];
						$cls_db->ExecuteQuery($query);
					}
					break;
					case "fermo":
					if($id_veicolo_fermo[$z]!=null&&$id_veicolo_fermo[$z]!=""){
						$query = "UPDATE veicoli SET Pignoramento_ID = ".$pignoramento_ID." WHERE ID = ".$id_veicolo_fermo[$z];
						$cls_db->ExecuteQuery($query);
					}
					break;
				}
			}
			$msg = "Dati inseriri correttamente";
		}

		//$control_pigno_gen = $salva_pigno_gen->Insert();
		//$pignoramento_ID = mysql_insert_id();
	}
	else
	{
		$pigno_nuovo = "no";
		$control_salvataggio = "update";
		$id_notifiche_debitore = $Notifiche_Debitore;

		/*$a_paramsPignoGen = array(
				'table' => 'pignoramento_generale',
				'fields'=> array(
				),
				'updateField'=>array(   'name'=>'ID',  'type'=>'int',    'value'=> $pignoramento_ID)
		);
		foreach($salva_pigno_gen as $key => $val) {
		  $Type = is_numeric($val)?"int":"string";
		  if($val=='null'){$Type = "int"; $val="";}
		  //echo $Type.": $key = $val<br>";
			array_push($a_paramsPignoGen['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
		}*/

		for($z=0; $z<3; $z++){
			//if($z>0) $allIDVeicolo .= "*";

			switch($tipo_pignoramento)
			{
				case "veicolo":
				if($id_veicolo_veicolo[$z]!=null&&$id_veicolo_veicolo[$z]!=""){
					$query = "UPDATE veicoli SET Pignoramento_ID = ".$pignoramento_ID." WHERE ID = ".$id_veicolo_veicolo[$z];
					$cls_db->ExecuteQuery($query);
				}
				break;
				case "preav_fermo":
				if($id_veicolo_preav_fermo[$z]!=null&&$id_veicolo_preav_fermo[$z]!=""){
					$query = "UPDATE veicoli SET Pignoramento_ID = ".$pignoramento_ID." WHERE ID = ".$id_veicolo_preav_fermo[$z];
					$cls_db->ExecuteQuery($query);
				}
				break;
				case "fermo":
				if($id_veicolo_fermo[$z]!=null&&$id_veicolo_fermo[$z]!=""){
					$query = "UPDATE veicoli SET Pignoramento_ID = ".$pignoramento_ID." WHERE ID = ".$id_veicolo_fermo[$z];
					$cls_db->ExecuteQuery($query);
				}
				break;
			}
		}

//$allIDVeicolo = "";

		/*for($z=0; $z<3; $z++){
			//if($z>0) $allIDVeicolo .= "*";

			switch($tipo_pignoramento)
			{
				case "veicolo":	if($id_veicolo_veicolo[$z]!=null && $id_veicolo_veicolo[$z]!="") {
					if($z>0) $allIDVeicolo .= "*";
				  $allIDVeicolo .= $id_veicolo_veicolo[$z];
				}
					break;
				case "preav_fermo": if($id_veicolo_preav_fermo[$z]!=null && $id_veicolo_preav_fermo[$z]!=""){
					if($z>0) $allIDVeicolo .= "*";
					  $allIDVeicolo .= $id_veicolo_preav_fermo[$z];
					} break;
				case "fermo": if($id_veicolo_fermo[$z]!=null && $id_veicolo_fermo[$z]!="") {
					if($z>0) $allIDVeicolo .= "*";
					$allIDVeicolo .= $id_veicolo_fermo[$z];
				} break;
			}
		}
		$salva_pigno_gen["Veicolo_ID"] = $allIDVeicolo;*/

		$a_paramsPignoGen = $cls_Utils->GetObjectQuery($salva_pigno_gen,"pignoramento_generale",array("ID" => $pignoramento_ID));

		if(!$cls_db->DbSave($a_paramsPignoGen))
		{
			$error = 1;
			$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
			$cls_db->Rollback();
			header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			die;
		}else $msg = "Dati aggiornati correttamente";
		//$control_pigno_gen = $salva_pigno_gen->Update( $pignoramento_ID );
	}

	/*if($control_pigno_gen===1)
	{
		echo 'ERROR Partita '.$partita_ID.': '.mysql_error();
		mysql_query('ROLLBACK');
		die;
	}*/

	for($i=0;$i<count($data_not_debitore);$i++)
	{
		if(isset($id_notifiche_debitore[$i]))
			$id_not_debitore = $id_notifiche_debitore[$i]["ID"];
		else
		{
			$id_not_debitore = null;

			if($pigno_nuovo=="no" && $data_stampa!=null)
			{

				if($tipo_ufficiale == "giudiziario")
					$cls_Utils->SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "consegna a mani / servizio postale ai sensi di legge");
				else if($invio_debitore[$i]=="posta")
					$cls_Utils->SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "servizio postale ai sensi di legge");
				else if($invio_debitore[$i]=="mani")
					$cls_Utils->SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "consegna a mani");
				else
					$cls_Utils->SostituisciTestoTraGraffe ($Relata_Debitore, "{TIPOINVIO}", "Posta Elettronica Certificata al seguente indirizzo ".$utente["PEC"]." ai sensi di legge" );

				$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);

				$pdf->setPrintHeader(false);
				$pdf->SetAutoPageBreak(false);
				$pdf->SetCellPadding(0);

				$pdf->SetLineWidth(0.2);
				$pdf->SetMargins(7.0, 7.0, 7.0);
				$width_page = $pdf->getPageWidth() - 7;

				$pdf->SetCellPadding(0);
				$pdf->AddPage('P');

				//RELAZIONE
				$pdf->SetFont('Arial', 'B', 8.5);
				$pdf->MultiCell(0, 0, $Intestazione_Relata , 0, 'C', 0, 1);
				if($Sottointestazione_Relata!="")
					$pdf->MultiCell(0, 0, $Sottointestazione_Relata , 0, 'C', 0, 1);
				$pdf->Ln(1);
				$pdf->SetFont('Arial', '', 8.5);
				$pdf->MultiCell(0, 0, $Relata_Notifica."\n", 0, 'J', 0, 1);
				$pdf->Ln(2);

				$pdf->MultiCell(0, 0, $Relata_Debitore."\n", 0, 'J', 0, 1);
				$pdf->Ln(2);

				$pdf->firma_destra($firma_ufficiale);

				$pdf->SetCellPadding(0);
				$pdf->AddPage('P');

				$nome_file = $stampa_dir."/Pignoramento_".$tipo_pigno_nome_file."_".$c."_".$salva_pigno_gen["Anno_Cronologico"]."_".$salva_pigno_gen["ID_Cronologico"]."_".$salva_pigno_gen["Data_Stampa"]."_rel_debitore_".$i.".pdf";

				if(is_file($nome_file)===false)
				{
					$pdf->Output( $nome_file , 'F');
				}
			}

		}

		//$notifica_debitore = new notifica_atto($id_not_debitore, $c);
		$query = "SELECT * FROM notifica_atto WHERE ID = '".$id_not_debitore."' AND CC = '".$c."'";
		$notifica_debitore = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"notifica_atto");


		$notifica_debitore["CC"] = $c;
		$notifica_debitore["Atto_Notificato_ID"] = $pignoramento_ID;
		$notifica_debitore["Tipo_Atto_Notificato"] = "pignoramento";
		$notifica_debitore["Tipo_Notifica"] = "debitore";
		$notifica_debitore["Data_Notifica"] =  $cls_date->GetDateDB($data_not_debitore[$i],"IT");
		$notifica_debitore["Stato_Notifica"] = $stato_not_debitore[$i];
		$notifica_debitore["Motivo_Notifica"] = $motivo_not_debitore[$i];
		$notifica_debitore["Modalita_Notifica"] = $modalita_not_debitore[$i];
		$notifica_debitore["Modalita_Stampa"] = $invio_debitore[$i];
		$notifica_debitore["Indirizzo_Validato"] = $ind_validato_debitore[$i];
		$notifica_debitore["Note_Notifica"] = $note_not_debitore[$i];

		if($i==0)
			$notifica_debitore["Spese_Notifica"] = $cls_math->conv_num($spese_not_debitore);

			/*$a_paramsNotifDeb = array(
					'table' => 'notifica_atto',
					'fields'=> array(
					)
			);
			foreach($notifica_debitore as $key => $val) {

				$Type = is_numeric($val)?"int":"string";
				if($val=='null'){$Type = "int"; $val="";}

				array_push($a_paramsNotifDeb['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
			}*/

			$a_paramsNotifDeb = $cls_Utils->GetObjectQuery($notifica_debitore,"notifica_atto");

		if(isset($id_notifiche_debitore[$i]))
		{
			if($id_notifiche_debitore[$i]!=null)
			{

				$a_paramsNotifDeb['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $id_notifiche_debitore[$i]["ID"]);

				if(!$cls_db->DbSave($a_paramsNotifDeb))
				{
					$error = 1;
					$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
					$cls_db->Rollback();
					header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
					die;
				}else $msg = "Dati aggiornati correttamente";

				//$control_notifica_debitore = $notifica_debitore->Update($id_notifiche_debitore[$i]["ID"]);
			}

		}
		else if($invio_debitore[$i]!=null)
		{
			if(!$cls_db->DbSave($a_paramsNotifDeb))
			{
				$error = 1;
				$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
				$cls_db->Rollback();
				header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
				die;
			}else $msg = "Dati inseriti correttamente";
			//$control_notifica_debitore = $notifica_debitore->Insert();
		}
		else
			continue;

		/*if($control_notifica_debitore===false)
		{
			echo 'ERROR  Partita '.$partita_ID.': '.mysql_error();
			mysql_query('ROLLBACK');
			die;
		}*/


	}


	switch($tipo_pignoramento)
	{
		case "terzi":

		for($i=0;$i<$cont_terzi;$i++)
			$terzo_ID[] = null;

		$array_terzi = isset($salva_pigno_gen_for_funz["Presso_Terzi"])?$salva_pigno_gen_for_funz["Presso_Terzi"]:array();

		for($j=0;$j<count($array_terzi);$j++)
			$terzo_ID[$j] = $array_terzi[$j]["ID"];


		//SALVATAGGIO PRESSO TERZI
		for($i=0;$i<$cont_terzi;$i++)
		{
			if( $pignorato_id[$i] != 0 || $azienda[$i]!="")
			{
				//echo "<h1>az ". $azienda[$i]." Pigno: ".$pignoramento_ID."</h1></br>";
				//$salva_terzo = new pignoramento_presso_terzi($terzo_ID[$i], $c);
				$query = "SELECT * FROM pignoramento_presso_terzi WHERE ID = '".$terzo_ID[$i]."' AND CC = '".$c."'";
				$salva_terzo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));


				$where_notifica_terzo = "CC = '".$c."' AND Atto_Notificato_ID = '".$salva_terzo["Pignoramento_ID"]."' AND ID_Collegamento = '".$salva_terzo["ID"]."'";
				$where_notifica_terzo.= "AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'terzi'";

				$query = "SELECT * FROM notifica_atto WHERE ".$where_notifica_terzo." ORDER BY ID ASC";
				$Notifiche_Terzo = $cls_db->GetResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "notifica_atto" , $where_notifica_terzo,"ID","ASC");
				/*for($i=0;$i<count($notifica_terzo_id);$i++)
				{
					$Notifiche_Terzo[$i] = new notifica_atto( $notifica_terzo_id[$i]['ID'] , $c );
				}*/


				$salva_terzo["CC"] = $c;
				$salva_terzo["Pignoramento_ID"] = $pignoramento_ID;
				$salva_terzo["Tipo_Terzi"] = $presso_terzi;
				$salva_terzo["Terzo_ID"] = $pignorato_id[$i];
				$salva_terzo["Azienda"] = $azienda[$i];
				$salva_terzo["Fonte_Dati"] = $fonte[$i];
				$salva_terzo["Note"] = $note[$i];

				$salva_terzo["Tipo_Contratto_Lavoro"] = $tipo_contratto[$i];
				$salva_terzo["Data_Costituzione_Ditta_Lavoro"] = $cls_date->GetDateDB($data_costituzione[$i],"IT");
				$salva_terzo["Data_Ditta_Operativa_Lavoro"] = $cls_date->GetDateDB($data_operativa[$i],"IT");
				$salva_terzo["Data_Dipendenze_Lavoro"] = $cls_date->GetDateDB($data_dipendenze[$i],"IT");
				$salva_terzo["Tipo_Titolo_Banca"] = $tipo_titolo[$i];
				$salva_terzo["Titolo_Banca"] = $titolo[$i];
				$salva_terzo["Intestatario_Banca"] = $intestatario[$i];
				$salva_terzo["Coointestatari_Banca"] = $coointestatari[$i];
				$salva_terzo["Tipo_Pensione_Inps"] = $tipo_libretto[$i];
				$salva_terzo["Libretto_Inps"] = $libretto[$i];
				$salva_terzo["Tipo_Titolo_Altro"] = $tipo_titolo_credito[$i];
				$salva_terzo["Titolo_Altro"] = $titolo_credito[$i];
				$salva_terzo["Tipo_Credito_Altro"] = $tipo_credito[$i];
				$salva_terzo["Data_Emissione_Altro"] = $cls_date->GetDateDB($data_emissione[$i],"IT");
				$salva_terzo["Data_Scadenza_Altro"] = $cls_date->GetDateDB($data_scadenza[$i],"IT");

				/*$a_paramsSalvaTerzo = array(
						'table' => 'pignoramento_presso_terzi',
						'fields'=> array(
						)
				);
				foreach($salva_terzo as $key => $val) {

					$Type = is_numeric($val)?"int":"string";
					if($val=='null'){$Type = "int"; $val="";}

					array_push($a_paramsSalvaTerzo['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
				}*/

				$a_paramsSalvaTerzo = $cls_Utils->GetObjectQuery($salva_terzo,"pignoramento_presso_terzi");

				if($terzo_ID[$i]==null)
				{
					$id_collegamento_terzo = $cls_db->DbSave($a_paramsSalvaTerzo);

					if(!$id_collegamento_terzo)
					{
						$error = 1;
						$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati inseriti correttamente";

					//$control_terzo = $salva_terzo->Insert();
					$id_notifiche_terzo = null;
					//$id_collegamento_terzo = mysql_insert_id();

				}
				else
				{
					$a_paramsSalvaTerzo['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $terzo_ID[$i]);

					if(!$cls_db->DbSave($a_paramsSalvaTerzo))
					{
						$error = 1;
						$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati aggiornati correttamente";

					//$control_terzo = $salva_terzo->Update($terzo_ID[$i]);
					$id_notifiche_terzo = $Notifiche_Terzo;// $salva_terzo->Notifiche_Terzo;
					$id_collegamento_terzo = $terzo_ID[$i];
				}

				/*if( $control_terzo === false )
				{
					echo 'ERROR '.$partita_ID.': '.mysql_error();
					mysql_query('ROLLBACK');
					die;
				}*/


				for($y=0;$y<count($data_not[$i]);$y++)
				{
					if(isset($id_notifiche_terzo[$y]))
						$id_not_terzo = $id_notifiche_terzo[$y]["ID"];
					else
					{
						$query = "SELECT * FROM pignoramento_presso_terzi WHERE ID = '".$id_collegamento_terzo."' AND CC = '".$c."'";
						$terzo_pignorato = $cls_db->getArrayLineNUll($cls_db->ExecuteQuery($query),"pignoramento_presso_terzi");

						if($terzo_pignorato["Tipo_Terzi"]!="banca")
						{

							$query = "SELECT * FROM utente WHERE ID = '".$terzo_pignorato["Terzo_ID"]."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
							$terzo_pignorato["Dati_Terzo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($terzo_pignorato["Terzo_ID"], $c);
							//print_r($terzo_pignorato["Dati_Terzo"]);
							$terzo_pignorato["Dati_Terzo"] = $cls_coazione->AddIndirizzo($terzo_pignorato["Terzo_ID"],$terzo_pignorato["Dati_Terzo"],$c);

							$indirizzo_terzo = $cls_coazione->righe_indirizzo($terzo_pignorato["Dati_Terzo"]);
						}
						else
						{
							$query = "SELECT * FROM banca WHERE ID = '" . $terzo_pignorato["Terzo_ID"] . "' AND CC = '*****'";
							$terzo_pignorato["Dati_Terzo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"banca");//new banca($terzo_pignorato["Terzo_ID"], "*****");

								$indirizzo_terzo = $cls_coazione->righe_indirizzo_s1($terzo_pignorato["Dati_Terzo"]);
						}

						//$terzo_pignorato = new pignoramento_presso_terzi($id_collegamento_terzo, $c);
						$terzo_utente = $terzo_pignorato["Dati_Terzo"];
						$PEC_terzo = $terzo_utente["PEC"];
						//$indirizzo_terzo = $terzo_utente->righe_indirizzo();

						if($presso_terzi!="banca")
							$terzo_singolo = "(".$terzo_utente["Comune_ID"].") ".$terzo_utente["Cognome"] . $terzo_utente["Ditta"] ." ". $terzo_utente["Nome"];
						else
							$terzo_singolo = $terzo_utente["Denominazione"];

						$terzo_singolo.= ", in persona del legale rappresentante pro tempore";

						$id_not_terzo = null;

						if($pigno_nuovo=="no" && $data_stampa!=null)
						{
							$Relata_Terzo = $testo["Relata_Terzo"];
							$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TERZO}", 	 $terzo_singolo );
							$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{SEDETERZO}", $indirizzo_terzo['Senza_Provincia'] );

							if($tipo_ufficiale == "giudiziario")
								$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "consegna a mani / servizio postale ai sensi di legge");
							else if($modalita_stampa_terzo[$i][$y]=="posta")
								$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "servizio postale ai sensi di legge");
							else if($modalita_stampa_terzo[$i][$y]=="mani")
								$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "consegna a mani");
							else
								$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "Posta Elettronica Certificata al seguente indirizzo ".$PEC_terzo." ai sensi di legge" );

							$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);

							$pdf->setPrintHeader(false);
							$pdf->SetAutoPageBreak(false);
							$pdf->SetCellPadding(0);

							$pdf->SetLineWidth(0.2);
							$pdf->SetMargins(7.0, 7.0, 7.0);
							$width_page = $pdf->getPageWidth() - 7;

							$pdf->SetCellPadding(0);
							$pdf->AddPage('P');

							//RELAZIONE
							$pdf->SetFont('Arial', 'B', 8.5);
							$pdf->MultiCell(0, 0, $Intestazione_Relata , 0, 'C', 0, 1);
							if($Sottointestazione_Relata!="")
								$pdf->MultiCell(0, 0, $Sottointestazione_Relata , 0, 'C', 0, 1);
							$pdf->Ln(1);
							$pdf->SetFont('Arial', '', 8.5);
							$pdf->MultiCell(0, 0, $Relata_Notifica."\n", 0, 'J', 0, 1);
							$pdf->Ln(2);

							$pdf->MultiCell(0, 0, $Relata_Terzo."\n", 0, 'J', 0, 1);
							$pdf->Ln(2);

							$pdf->firma_destra($firma_ufficiale_copia);

							$pdf->SetCellPadding(0);
							$pdf->AddPage('P');

							$nome_file = $stampa_dir."/Pignoramento_".$tipo_pigno_nome_file."_".$c."_".$salva_pigno_gen["Anno_Cronologico"]."_".$salva_pigno_gen["ID_Cronologico"]."_".$salva_pigno_gen["Data_Stampa"]."_rel_terzo_".$i."_".$y.".pdf";

							if(is_file($nome_file)===false)
							{
								$pdf->Output( $nome_file , 'F');
							}
						}
					}


					$query = "SELECT * FROM notifica_atto WHERE ID = '".$id_not_terzo."' AND CC = '".$c."'";
					$notifica_terzo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
					//$notifica_terzo = new notifica_atto($id_not_terzo, $c);

					$notifica_terzo["CC"] = $c;
					$notifica_terzo["Atto_Notificato_ID"] = $pignoramento_ID;
					$notifica_terzo["ID_Collegamento"] = $id_collegamento_terzo;
					$notifica_terzo["Tipo_Atto_Notificato"] = "pignoramento";
					$notifica_terzo["Tipo_Notifica"] = "terzi";
					$notifica_terzo["Data_Notifica"] = $cls_date->GetDateDB($data_not[$i][$y],"IT");
					$notifica_terzo["Stato_Notifica"] = $stato_not[$i][$y];
					$notifica_terzo["Motivo_Notifica"] = $motivo_not[$i][$y];
					$notifica_terzo["Modalita_Notifica"] = $modalita_not[$i][$y];
					$notifica_terzo["Modalita_Stampa"] = $modalita_stampa_terzo[$i][$y];
					$notifica_terzo["Indirizzo_Validato"] = $ind_validato_terzo[$i][$y];
					$notifica_terzo["Note_Notifica"] = $note_not_terzo[$i][$y];

					if($y==0)
						$notifica_terzo["Spese_Notifica"] = $cls_math->conv_num($spese_not[$i]);


						/*$a_paramsNotificaTerzo = array(
								'table' => 'notifica_atto',
								'fields'=> array(
								)
						);

						foreach($notifica_terzo as $key => $val) {

							$Type = is_numeric($val)?"int":"string";
							if($val=='null'){$Type = "int"; $val="";}

							array_push($a_paramsNotificaTerzo['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
						}*/

						$a_paramsNotificaTerzo = $cls_Utils->GetObjectQuery($notifica_terzo,"notifica_atto");


					if($id_not_terzo!=null)
					{

						$a_paramsNotificaTerzo['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $id_notifiche_terzo[$y]["ID"]);

						if(!$cls_db->DbSave($a_paramsNotificaTerzo))
						{

							$error = 1;
							$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
							$cls_db->Rollback();
							header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
							die;
						}else $msg = "Dati aggiornati correttamente";

					//$control_notifica_terzo = $notifica_terzo->Update($id_notifiche_terzo[$y]->ID);
					}
					else if($modalita_stampa_terzo[$i][$y]!=null)
					{

						if(!$cls_db->DbSave($a_paramsNotificaTerzo))
						{

							$error = 1;
							$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
							$cls_db->Rollback();
							header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
							die;
						}else $msg = "Dati aggiornati correttamente";
						//$control_notifica_terzo = $notifica_terzo->Insert();
					}
					else
						continue;

					/*if($control_notifica_terzo===false)
					{
						echo 'ERROR  Partita '.$partita_ID.': '.mysql_error();
						mysql_query('ROLLBACK');
						die;
					}*/
				}

			}
			else
			{
				if(isset($terzo_ID[$i]))
				{
					//$query = "DELETE FROM pignoramento_presso_terzi WHERE ID = '".$terzo_ID[$i]."' AND CC = '".$c."' ";
					if(!$cls_db->Delete("pignoramento_presso_terzi","ID = '".$terzo_ID[$i]."' AND CC = '".$c."'"))
					{

						$error = 1;
						$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati eliminati correttamente";

					//$cancella_terzo = new pignoramento_presso_terzi($terzo_ID[$i], $c);
					//$cancella_terzo->Delete();
				}
			}
		}

			break;

		case "veicolo":

		for($i=0;$i<3;$i++)
		{
			if(isset($salva_pigno_gen_for_funz["Veicolo"][$i]/*$salva_pigno_gen->Veicolo[$i]*/))
				$id_pigno_veicolo = $salva_pigno_gen_for_funz["Veicolo"][$i]["ID"];// $salva_pigno_gen->Veicolo[$i]->ID;
			else
			{
				$id_pigno_veicolo = null;
			}

			//$pignoramento_veicolo = new pignoramento_veicolo($id_pigno_veicolo, $c);
			$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$id_pigno_veicolo."' AND CC = '".$c."'";
			$pignoramento_veicolo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

			$pignoramento_veicolo["CC"] = $c;
			$pignoramento_veicolo["Pignoramento_ID"] = $pignoramento_ID;
			$pignoramento_veicolo["Marca_Veicolo"] = $marca_veicolo[$i];
			$pignoramento_veicolo["Modello_Veicolo"] = $modello_veicolo[$i];
			$pignoramento_veicolo["Targa_Veicolo"] = $targa_veicolo[$i];
			$pignoramento_veicolo["Telaio_Veicolo"] = $telaio_veicolo[$i];
			$pignoramento_veicolo["Tipo_Veicolo"] = $tipo_veicolo[$i];
      		$pignoramento_veicolo["Data_Iscrizione_Fermo"] = $cls_date->GetDateDB($data_fermo_veicolo[$i],"IT");
			$pignoramento_veicolo["Data_Visura"] = $data_visura_veicolo[$i];
			$pignoramento_veicolo["Portata_Veicolo"] = $cls_math->conv_num($portata_veicolo[$i]);
			$pignoramento_veicolo["Valore_Veicolo"] = $cls_math->conv_num($valore_veicolo[$i]);
			$pignoramento_veicolo["Anno_Immatricolazione"] = $anno_immatricolazione_veicolo[$i];
			$pignoramento_veicolo["Fonte_Dati"] = $fonte_dati_veicolo[$i];
			$pignoramento_veicolo["Veicolo_ID"] = $id_veicolo_veicolo[$i];

			if($targa_veicolo[$i]!=null)
			{

				/*$a_paramsPignoVeicolo = array(
						'table' => 'pignoramento_veicolo',
						'fields'=> array(
						)
				);

				foreach($pignoramento_veicolo as $key => $val) {

					$Type = is_numeric($val)?"int":"string";
					if($val=='null'){$Type = "int"; $val="";}

					array_push($a_paramsPignoVeicolo['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
				}*/

				$a_paramsPignoVeicolo = $cls_Utils->GetObjectQuery($pignoramento_veicolo,"pignoramento_veicolo");

				if($id_pigno_veicolo==null)
				{
					if(!$cls_db->DbSave($a_paramsPignoVeicolo))
					{

						$error = 1;
						$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati aggiornati correttamente";
					//$control_veicolo = $pignoramento_veicolo->Insert();
				}
				else
				{
					$a_paramsPignoVeicolo['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $salva_pigno_gen_for_funz["Veicolo"][$i]["ID"]);

					if(!$cls_db->DbSave($a_paramsPignoVeicolo))
					{

						$error = 1;
						$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati aggiornati correttamente";
				//$control_veicolo = $pignoramento_veicolo->Update($salva_pigno_gen_for_funz["Veicolo"][$i]["ID"]/*$salva_pigno_gen->Veicolo[$i]->ID*/);
				}

			}
			else if($id_pigno_veicolo!=null)
			{
				//$query = "DELETE FROM pignoramento_veicolo WHERE ID = '".$id_pigno_veicolo."' AND CC = '".$c."' ";
				if(!$cls_db->Delete("pignoramento_veicolo","ID = '".$id_pigno_veicolo."' AND CC = '".$c."'"))
				{

					$error = 1;
					$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
					$cls_db->Rollback();
					header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
					die;
				}else $msg = "Dati eliminati correttamente";

				//$control_veicolo = $pignoramento_veicolo->Delete();
			}
			else
				continue;

			/*if( $control_veicolo === false )
			{
				echo 'ERROR '.$partita_ID.': '.mysql_error();
				mysql_query('ROLLBACK');
				die;
			}*/

		}

		if($Notifica_Istituto!=null)
			$id_notifiche_veicolo = $Notifica_Istituto;// $salva_pigno_gen->Notifica_Istituto;
		else
			$id_notifiche_veicolo = null;

		for($i=0;$i<count($data_not_veicolo);$i++)
		{
			if(isset($id_notifiche_veicolo[$i]))
				$id_not_veicolo = $id_notifiche_veicolo[$i]["ID"];
			else
			{
				$id_not_veicolo = null;
/**************************************************************************************************************************************************************************************************************/
												/* FORSE QUESTA QUERY è SBAGLIATA AL POSTO DI WHERE CC MI SA CHE CI VA WHERE CC_Ufficio */
				$query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$tribunale["CC_Ufficio"]."' AND Tipo = 'istituto' LIMIT 1";
/****************************************************************************************************************************************************************************************************************/


				$istituto_vendite = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new ufficio_giudiziario($tribunale["CC_Ufficio"], "istituto");
				$sede_istituto = $cls_coazione->righe_indirizzo_s($istituto_vendite);
				$PEC_Istituto = $istituto_vendite["PEC"];
				$Mail_Istituto = $istituto_vendite["Mail"];

				if($pigno_nuovo=="no" && $data_stampa!=null)
				{
					$Relata_Terzo = $testo["Relata_Terzo"];
					$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TERZO}", 	 $istituto_vendite["Denominazione"] );
					$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{SEDETERZO}", $sede_istituto['Senza_Provincia'] );

					if($tipo_ufficiale == "giudiziario")
						$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "consegna a mani / servizio postale ai sensi di legge");
					else if($invio_veicolo[$i]=="posta")
						$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "servizio postale ai sensi di legge");
					else if($invio_veicolo[$i]=="mani")
						$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "consegna a mani");
					else
						$cls_Utils->SostituisciTestoTraGraffe ($Relata_Terzo, "{TIPOINVIO}", "Posta Elettronica Certificata al seguente indirizzo ".$PEC_Istituto." ai sensi di legge" );

					$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);

					$pdf->setPrintHeader(false);
					$pdf->SetAutoPageBreak(false);
					$pdf->SetCellPadding(0);

					$pdf->SetLineWidth(0.2);
					$pdf->SetMargins(7.0, 7.0, 7.0);
					$width_page = $pdf->getPageWidth() - 7;

					$pdf->SetCellPadding(0);
					$pdf->AddPage('P');

					//RELAZIONE
					$pdf->SetFont('Arial', 'B', 8.5);
					$pdf->MultiCell(0, 0, $Intestazione_Relata , 0, 'C', 0, 1);
					if($Sottointestazione_Relata!="")
						$pdf->MultiCell(0, 0, $Sottointestazione_Relata , 0, 'C', 0, 1);
					$pdf->Ln(1);
					$pdf->SetFont('Arial', '', 8.5);
					$pdf->MultiCell(0, 0, $Relata_Notifica."\n", 0, 'J', 0, 1);
					$pdf->Ln(2);

					$pdf->MultiCell(0, 0, $Relata_Terzo."\n", 0, 'J', 0, 1);
					$pdf->Ln(2);

					$pdf->firma_destra($firma_ufficiale_copia);

					$pdf->SetCellPadding(0);
					$pdf->AddPage('P');

					$nome_file = $stampa_dir."/Pignoramento_".$tipo_pigno_nome_file."_".$c."_".$salva_pigno_gen["Anno_Cronologico"]."_".$salva_pigno_gen["ID_Cronologico"]."_".$salva_pigno_gen["Data_Stampa"]."_rel_istituto_".$i.".pdf";

					if(is_file($nome_file)===false)
					{
						$pdf->Output( $nome_file , 'F');
					}
				}
			}

			//$notifica_veicolo = new notifica_atto($id_not_veicolo, $c);
			$query = "SELECT * FROM notifica_atto WHERE ID = '".$id_not_veicolo."' AND CC = '".$c."'";
			$notifica_veicolo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

			$notifica_veicolo["CC"] = $c;
			$notifica_veicolo["Atto_Notificato_ID"] = $pignoramento_ID;
			$notifica_veicolo["Tipo_Atto_Notificato"] = "pignoramento";
			$notifica_veicolo["Tipo_Notifica"] = "veicolo";
			$notifica_veicolo["Data_Notifica"] = $cls_date->GetDateDB($data_not_veicolo[$i],"IT");
			$notifica_veicolo["Stato_Notifica"] = $stato_not_veicolo[$i];
			$notifica_veicolo["Motivo_Notifica"] = $motivo_not_veicolo[$i];
			$notifica_veicolo["Modalita_Notifica"] = $modalita_not_veicolo[$i];
			$notifica_veicolo["Modalita_Stampa"] = $invio_veicolo[$i];
			$notifica_veicolo["Indirizzo_Validato"] = $ind_validato_veicolo[$i];
			$notifica_veicolo["Note_Notifica"] = $note_not_veicolo[$i];

			if($i==0)
				$notifica_veicolo["Spese_Notifica"] = $cls_math->conv_num($spese_not_veicolo);

				/*$a_paramsNotificaVeicolo = array(
						'table' => 'notifica_atto',
						'fields'=> array(
						)
				);

				foreach($notifica_veicolo as $key => $val) {

					$Type = is_numeric($val)?"int":"string";
					if($val=='null'){$Type = "int"; $val="";}

					array_push($a_paramsNotificaVeicolo['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
				}*/

				$a_paramsNotificaVeicolo = $cls_Utils->GetObjectQuery($notifica_veicolo,"notifica_atto");

			if(isset($id_notifiche_veicolo[$i]))
			{
				if($id_notifiche_veicolo[$i]!=null)
				{
					$a_paramsNotificaVeicolo['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $id_notifiche_veicolo[$i]["ID"]);

					if(!$cls_db->DbSave($a_paramsNotificaVeicolo))
					{

						$error = 1;
						$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati aggiornati correttamente";
					//$control_not_veicolo = $notifica_veicolo->Update($id_notifiche_veicolo[$i]->ID);
				}
			}
			else if($invio_veicolo[$i]!=null)
			{
				if(!$cls_db->DbSave($a_paramsNotificaVeicolo))
				{

					$error = 1;
					$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
					$cls_db->Rollback();
					header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
					die;
				}else $msg = "Dati inseriti correttamente";
				//$control_not_veicolo = $notifica_veicolo->Insert();
			}
			else
				continue;

			/*if($control_not_veicolo===false)
			{
				echo 'ERROR  Partita '.$partita_ID.': '.mysql_error();
				mysql_query('ROLLBACK');
				die;
			}*/
		}

			break;

		case "preav_fermo":

		for($i=0;$i<3;$i++)
		{
			if(isset($Preavviso_Fermo[$i]))
				$id_pigno_preav_fermo = $Preavviso_Fermo[$i]["ID"];
			else
				$id_pigno_preav_fermo = null;

			//$pignoramento_preav_fermo = new pignoramento_veicolo($id_pigno_preav_fermo, $c);
			$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$id_pigno_preav_fermo."' AND CC = '".$c."'";
			$pignoramento_preav_fermo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

			$pignoramento_preav_fermo["CC"] = $c;
			$pignoramento_preav_fermo["Pignoramento_ID"] = $pignoramento_ID;
			$pignoramento_preav_fermo["Marca_Veicolo"] = $marca_preav_fermo[$i];
			$pignoramento_preav_fermo["Modello_Veicolo"] = $modello_preav_fermo[$i];
			$pignoramento_preav_fermo["Targa_Veicolo"] = $targa_preav_fermo[$i];
			$pignoramento_preav_fermo["Tipo_Veicolo"] = $tipo_preav_fermo[$i];
			$pignoramento_preav_fermo["Data_Visura"] = $data_visura_preav_fermo[$i];
			$pignoramento_preav_fermo["Portata_Veicolo"] = $cls_math->conv_num($portata_preav_fermo[$i]);
			$pignoramento_preav_fermo["Valore_Veicolo"] = $cls_math->conv_num($valore_preav_fermo[$i]);
			$pignoramento_preav_fermo["Anno_Immatricolazione"] = $anno_immatricolazione_preav_fermo[$i];
			$pignoramento_preav_fermo["Fonte_Dati"] = $fonte_dati_preav_fermo[$i];
			$pignoramento_preav_fermo["Veicolo_ID"] = $id_veicolo_veicolo[$i];

			/*$a_paramsPreavFermo = array(
					'table' => 'pignoramento_veicolo',
					'fields'=> array(
					)
			);

			foreach($pignoramento_preav_fermo as $key => $val) {

				$Type = is_numeric($val)?"int":"string";
				if($val=='null'){$Type = "int"; $val="";}

				array_push($a_paramsPreavFermo['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
			}*/

			$a_paramsPreavFermo = $cls_Utils->GetObjectQuery($pignoramento_preav_fermo,"pignoramento_veicolo");


			if($targa_preav_fermo[$i]!=null)
			{
				if($id_pigno_preav_fermo==null)
				{
					if(!$cls_db->DbSave($a_paramsPreavFermo))
					{

						$error = 1;
						$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati inseriti correttamente";
					//$control_preav_fermo = $pignoramento_preav_fermo->Insert();
				}
				else
				{
					$a_paramsPreavFermo['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $Preavviso_Fermo[$i]["ID"]);

					if(!$cls_db->DbSave($a_paramsPreavFermo))
					{

						$error = 1;
						$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati aggiornati correttamente";

					//$control_preav_fermo = $pignoramento_preav_fermo->Update($salva_pigno_gen->Preavviso_Fermo[$i]->ID);
				}
			}
			else if($id_pigno_preav_fermo!=null)
			{
					//$query = "DELETE FROM pignoramento_veicolo WHERE ID = '".$id_pigno_preav_fermo."' AND CC = '".$c."' ";
					if(!$cls_db->Delete("pignoramento_veicolo","ID = '".$id_pigno_preav_fermo."' AND CC = '".$c."'"))
					{

						$error = 1;
						$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati eliminati correttamente";
				//$control_preav_fermo = $pignoramento_preav_fermo->Delete();
			}
			else
				continue;

			/*if( $control_preav_fermo === false )
			{
				echo 'ERROR '.$partita_ID.': '.mysql_error();
				mysql_query('ROLLBACK');
				die;
			}*/
		}

			break;

		case "fermo":

		for($i=0;$i<3;$i++)
		{
			if(isset($Fermo[$i]))
				$id_pigno_fermo = $Fermo[$i]["ID"];
			else
				$id_pigno_fermo = null;

			//$pignoramento_fermo = new pignoramento_veicolo($id_pigno_fermo, $c);
			$query = "SELECT * FROM pignoramento_veicolo WHERE ID = '".$id_pigno_fermo."' AND CC = '".$c."'";
			$pignoramento_fermo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

			$pignoramento_fermo["CC"] = $c;
			$pignoramento_fermo["Pignoramento_ID"] = $pignoramento_ID;
			$pignoramento_fermo["Marca_Veicolo"] = $marca_fermo[$i];
			$pignoramento_fermo["Modello_Veicolo"] = $modello_fermo[$i];
			$pignoramento_fermo["Targa_Veicolo"] = $targa_fermo[$i];
			$pignoramento_fermo["Tipo_Veicolo"] = $tipo_fermo[$i];
			$pignoramento_fermo["Data_Visura"] = $data_visura_fermo[$i];
			$pignoramento_fermo["Portata_Veicolo"] = $cls_math->conv_num($portata_fermo[$i]);
			$pignoramento_fermo["Valore_Veicolo"] = $cls_math->conv_num($valore_fermo[$i]);
			$pignoramento_fermo["Anno_Immatricolazione"] = $anno_immatricolazione_fermo[$i];
			$pignoramento_fermo["Fonte_Dati"] = $fonte_dati_fermo[$i];
			$pignoramento_fermo["Veicolo_ID"] = $id_veicolo_veicolo[$i];
			/*$a_paramsPignoFermo = array(
					'table' => 'pignoramento_veicolo',
					'fields'=> array(
					)
			);

			foreach($pignoramento_fermo as $key => $val) {

				$Type = is_numeric($val)?"int":"string";
				if($val=='null'){$Type = "int"; $val="";}

				array_push($a_paramsPignoFermo['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
			}*/

			$a_paramsPignoFermo = $cls_Utils->GetObjectQuery($pignoramento_fermo,"pignoramento_veicolo");

			if($targa_fermo[$i]!=null)
			{
				if($id_pigno_fermo==null)
				{
					if(!$cls_db->DbSave($a_paramsPignoFermo))
					{

						$error = 1;
						$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati inseriti correttamente";
					//$control_fermo = $pignoramento_fermo->Insert();
				}
				else
				{
					$a_paramsPignoFermo['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $Fermo[$i]["ID"]);

					if(!$cls_db->DbSave($a_paramsPignoFermo))
					{

						$error = 1;
						$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati aggiornati correttamente";
					//$control_fermo = $pignoramento_fermo->Update($salva_pigno_gen->Fermo[$i]->ID);
				}
			}
			else if($id_pigno_fermo!=null)
			{
				//$query = "DELETE FROM pignoramento_veicolo WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
				if(!$cls_db->Delete("pignoramento_veicolo","ID = '".$id_pigno_fermo."' AND CC = '".$c."'"))
				{

					$error = 1;
					$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
					$cls_db->Rollback();
					header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
					die;
				}else $msg = "Dati eliminati correttamente";
				//$control_fermo = $pignoramento_fermo->Delete();
			}
			else
				continue;

			/*if( $control_fermo === false )
			{
				echo 'ERROR '.$partita_ID.': '.mysql_error();
				mysql_query('ROLLBACK');
				die;
			}*/

		}

			break;

		case "immobiliare":

		for($i=0;$i<3;$i++)
		{
			if(isset($Immobiliare[$i]))
				$id_pigno_immobiliare = $Immobiliare[$i]["ID"];
			else
				$id_pigno_immobiliare = null;

			//$pignoramento_immobiliare = new pignoramento_immobiliare($id_pigno_immobiliare, $c);
			$query = "SELECT * FROM pignoramento_immobiliare WHERE ID = '".$id_pigno_immobiliare."' AND CC = '".$c."'";
			$pignoramento_immobiliare = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

			$pignoramento_immobiliare["CC"] = $c;
			$pignoramento_immobiliare["Pignoramento_ID"] = $pignoramento_ID;

			$pignoramento_immobiliare["Tipo_Immobiliare"] = $Tipo_Immobiliare[$i];
			$pignoramento_immobiliare["Situazione"] = $Situazione_Immobiliare[$i];
			$pignoramento_immobiliare["Foglio"] = $Foglio_Immobiliare[$i];
			$pignoramento_immobiliare["Particella"] = $Particella_Immobiliare[$i];
			$pignoramento_immobiliare["Subalterno"] = $Subalterno_Immobiliare[$i];
			$pignoramento_immobiliare["Classe"] = $Classe_Immobiliare[$i];
			$pignoramento_immobiliare["Annotazioni"] = $Annotazioni_Immobiliare[$i];

			//FABBRICATO
			$pignoramento_immobiliare["Sezione_Fabbricato"] = $Sezione_Fabbricato_Immobiliare[$i];
			$pignoramento_immobiliare["Zona_Censuaria_Fabbricato"] = $Zona_Censuaria_Fabbricato_Immobiliare[$i];
			$pignoramento_immobiliare["Categoria_Fabbricato"] = $Categoria_Fabbricato_Immobiliare[$i];
			$pignoramento_immobiliare["Consistenza_Fabbricato"] = $cls_math->conv_num($Consistenza_Fabbricato_Immobiliare[$i]);
			$pignoramento_immobiliare["Superficie_Fabbricato"] = $cls_math->conv_num($Superficie_Fabbricato_Immobiliare[$i]);
			$pignoramento_immobiliare["Rendita_Fabbricato"] = $cls_math->conv_num($Rendita_Fabbricato_Immobiliare[$i]);
			$pignoramento_immobiliare["Indirizzo_Fabbricato"] = $Indirizzo_Fabbricato_Immobiliare[$i];
			$pignoramento_immobiliare["Protocollo_Notifica_Fabbricato"] = $Protocollo_Notifica_Fabbricato_Immobiliare[$i];

			//TERRENO
			$pignoramento_immobiliare["Porzione_Terreno"] = $Porzione_Terreno_Immobiliare[$i];
			$pignoramento_immobiliare["Qualita_Terreno"] = $Qualita_Terreno_Immobiliare[$i];
			$pignoramento_immobiliare["Descrizione_Qualita_Terreno"] = $Descrizione_Qualita_Terreno_Immobiliare[$i];
			$pignoramento_immobiliare["HA_Ettari_Terreno"] = $HA_Ettari_Terreno_Immobiliare[$i];
			$pignoramento_immobiliare["A_Are_Terreno"] = $A_Are_Terreno_Immobiliare[$i];
			$pignoramento_immobiliare["C_Centiare_Terreno"] = $C_Centiare_Terreno_Immobiliare[$i];
			$pignoramento_immobiliare["Dominicale_Terreno"] = $cls_math->conv_num($Dominicale_Terreno_Immobiliare[$i]);
			$pignoramento_immobiliare["Agrario_Terreno"] = $cls_math->conv_num($Agrario_Terreno_Immobiliare[$i]);
			$pignoramento_immobiliare["Deduzioni_Terreno"] = $cls_math->conv_num($Deduzioni_Terreno_Immobiliare[$i]);

			//PROPRIETARIO
			$pignoramento_immobiliare["Parte_Proprietario"] = $Parte_Proprietario_Immobiliare[$i];
			$pignoramento_immobiliare["Totale_Proprietario"] = $Totale_Proprietario_Immobiliare[$i];


			/*$a_paramsPignoImmobiliare = array(
					'table' => 'pignoramento_immobiliare',
					'fields'=> array(
					)
			);

			foreach($pignoramento_immobiliare as $key => $val) {

				$Type = is_numeric($val)?"int":"string";
				if($val=='null'){$Type = "int"; $val="";}

				array_push($a_paramsPignoImmobiliare['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
			}*/

			$a_paramsPignoImmobiliare = $cls_Utils->GetObjectQuery($pignoramento_immobiliare,"pignoramento_immobiliare");

			if($Tipo_Immobiliare[$i]!=null && $Particella_Immobiliare[$i]!=null && $Foglio_Immobiliare!=null)
			{
				if($id_pigno_immobiliare==null)
				{
					if(!$cls_db->DbSave($a_paramsPignoImmobiliare))
					{

						$error = 1;
						$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati inseriti correttamente";
					//$control_immobiliare = $pignoramento_immobiliare->Insert();
				}
				else
				{
					$a_paramsPignoImmobiliare['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $Immobiliare[$i]["ID"]);

					if(!$cls_db->DbSave($a_paramsPignoImmobiliare))
					{

						$error = 1;
						$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
						$cls_db->Rollback();
						header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
						die;
					}else $msg = "Dati aggiornati correttamente";
					//$control_immobiliare = $pignoramento_immobiliare->Update($salva_pigno_gen->Immobiliare[$i]->ID);
				}
			}
			else if($id_pigno_immobiliare!=null)
			{
				//$query = "DELETE FROM pignoramento_immobiliare WHERE ID = '".$id_pigno_immobiliare."' AND CC = '".$c."' ";
				if(!$cls_db->Delete("pignoramento_immobiliare","ID = '".$id_pigno_immobiliare."' AND CC = '".$c."'"))
				{

					$error = 1;
					$msg = "Errore impossibile eliminare i dati. ".$cls_db->GetError();
					$cls_db->Rollback();
					header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
					die;
				}else $msg = "Dati eliminati correttamente";
				//$control_immobiliare = $pignoramento_immobiliare->Delete();
			}
			else
				continue;

			/*if( $control_immobiliare === false )
			{
				echo 'ERROR '.$partita_ID.': '.mysql_error();
				mysql_query('ROLLBACK');
				die;
			}*/

		}

			break;

			case "mobiliare":	break;
			case "beni":		break;

	}
$query = "SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[1]."' AND CC = '".$c."'";

	//SALVATAGGIO SPESE PIGNORAMENTO
	//$salva_spese_pigno = new spese_pignoramento($pignoramento_ID, $c);
	$query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$pignoramento_ID." AND CC = '".$c."'";
	$salva_spese_pigno = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	//echo "<h1>".$rimborso_totale." ".$rimborso_spesa[1]."</h1>";

	$salva_spese_pigno["CC"] = $c;
	$salva_spese_pigno["Pignoramento_ID"] = $pignoramento_ID;
	$salva_spese_pigno["Incremento_Percentuale"] = $percentuale;
	$salva_spese_pigno["Totale_Rimborso"] = str_replace(",",".",$rimborso_totale);

	$salva_spese_pigno["Tipo_Totale_1"] = $tipo_totale_spesa[1];
	$salva_spese_pigno["Spesa_1_ID"] = $spesa_ID[1];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[1]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[1], $c);
	$salva_spese_pigno["Tipo_Spesa_1"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_1"] = $extra_spesa[1];
	$salva_spese_pigno["Rimborso_1"] = $cls_math->conv_num($rimborso_spesa[1]);

	$salva_spese_pigno["Tipo_Totale_2"] = $tipo_totale_spesa[2];
	$salva_spese_pigno["Spesa_2_ID"] = $spesa_ID[2];
	$tariffa_coazione =  $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[2]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[2], $c);
	$salva_spese_pigno["Tipo_Spesa_2"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_2"] = $extra_spesa[2];
	$salva_spese_pigno["Rimborso_2"] = $cls_math->conv_num($rimborso_spesa[2]);

	$salva_spese_pigno["Tipo_Totale_3"] = $tipo_totale_spesa[3];
	$salva_spese_pigno["Spesa_3_ID"] = $spesa_ID[3];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[3]."' AND CC = '".$c."'"),"tariffe_coazione");// new tariffe_coazione($spesa_ID[3], $c);
	$salva_spese_pigno["Tipo_Spesa_3"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_3"] = $extra_spesa[3];
	$salva_spese_pigno["Rimborso_3"] = $cls_math->conv_num($rimborso_spesa[3]);

	$salva_spese_pigno["Tipo_Totale_4"] = $tipo_totale_spesa[4];
	$salva_spese_pigno["Spesa_4_ID"] = $spesa_ID[4];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[4]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[4], $c);
	$salva_spese_pigno["Tipo_Spesa_4"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_4"] = $extra_spesa[4];
	$salva_spese_pigno["Rimborso_4"] = $cls_math->conv_num($rimborso_spesa[4]);

	$salva_spese_pigno["Tipo_Totale_5"] = $tipo_totale_spesa[5];
	$salva_spese_pigno["Spesa_5_ID"] = $spesa_ID[5];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[5]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[5], $c);
	$salva_spese_pigno["Tipo_Spesa_5"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_5"] = $extra_spesa[5];
	$salva_spese_pigno["Rimborso_5"] = $cls_math->conv_num($rimborso_spesa[5]);

	$salva_spese_pigno["Tipo_Totale_6"] = $tipo_totale_spesa[6];
	$salva_spese_pigno["Spesa_6_ID"] = $spesa_ID[6];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[6]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[6], $c);
	$salva_spese_pigno["Tipo_Spesa_6"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_6"] = $extra_spesa[6];
	$salva_spese_pigno["Rimborso_6"] = $cls_math->conv_num($rimborso_spesa[6]);

	$salva_spese_pigno["Tipo_Totale_7"] = $tipo_totale_spesa[7];
	$salva_spese_pigno["Spesa_7_ID"] = $spesa_ID[7];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[7]."' AND CC = '".$c."'"),"tariffe_coazione");// new tariffe_coazione($spesa_ID[7], $c);
	$salva_spese_pigno["Tipo_Spesa_7"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_7"] = $extra_spesa[7];
	$salva_spese_pigno["Rimborso_7"] = $cls_math->conv_num($rimborso_spesa[7]);

	$salva_spese_pigno["Tipo_Totale_8"] = $tipo_totale_spesa[8];
	$salva_spese_pigno["Spesa_8_ID"] = $spesa_ID[8];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[8]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[8], $c);
	$salva_spese_pigno["Tipo_Spesa_8"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_8"] = $extra_spesa[8];
	$salva_spese_pigno["Rimborso_8"] = $cls_math->conv_num($rimborso_spesa[8]);

	$salva_spese_pigno["Tipo_Totale_9"] = $tipo_totale_spesa[9];
	$salva_spese_pigno["Spesa_9_ID"] = $spesa_ID[9];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[9]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[9], $c);
	$salva_spese_pigno["Tipo_Spesa_9"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_9"] = $extra_spesa[9];
	$salva_spese_pigno["Rimborso_9"] = $cls_math->conv_num($rimborso_spesa[9]);

	$salva_spese_pigno["Tipo_Totale_10"] = $tipo_totale_spesa[10];
	$salva_spese_pigno["Spesa_10_ID"] = $spesa_ID[10];
	$tariffa_coazione = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Tipo FROM tariffe_coazione WHERE ID = '".$spesa_ID[10]."' AND CC = '".$c."'"),"tariffe_coazione");//new tariffe_coazione($spesa_ID[10], $c);
	$salva_spese_pigno["Tipo_Spesa_10"] = $tariffa_coazione["Tipo"];
	$salva_spese_pigno["Extra_Spesa_10"] = $extra_spesa[10];
	$salva_spese_pigno["Rimborso_10"] = $cls_math->conv_num($rimborso_spesa[10]);


	/*$a_paramsSpesePigno = array(
			'table' => 'pignoramento_spese',
			'fields'=> array(
			)
	);

	foreach($salva_spese_pigno as $key => $val) {

		$Type = is_numeric($val)?"int":"string";
		if($val=='null'){$Type = "int"; $val="";}

		array_push($a_paramsSpesePigno['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
	}*/

	$a_paramsSpesePigno = $cls_Utils->GetObjectQuery($salva_spese_pigno,"pignoramento_spese");

	if($control_salvataggio == "insert")
	{
		if(!$cls_db->DbSave($a_paramsSpesePigno))
		{

			$error = 1;
			$msg = "Errore impossibile inserire i dati. ".$cls_db->GetError();
			$cls_db->Rollback();
			header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			die;
		}else $msg = "Dati inseriti correttamente";
		//$control_spese_pigno = $salva_spese_pigno->Insert();
	}
	else
	{
		$a_paramsSpesePigno['updateField'] = array('name'=>'Pignoramento_ID',  'type'=>'int',  'value'=> $pignoramento_ID);

		if(!$cls_db->DbSave($a_paramsSpesePigno))
		{

			$error = 1;
			$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
			$cls_db->Rollback();
			header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			die;
		}else $msg = "Dati aggiornati correttamente";
		//$control_spese_pigno = $salva_spese_pigno->Update( $pignoramento_ID, "Pignoramento_ID" );
	}

	/*if( $control_spese_pigno === false )
	{
		echo 'ERROR '.$partita_ID.': '.mysql_error();
		mysql_query('ROLLBACK');
		die;
	}*/

	$flag_blocco = $cls_help->getVar('flag_blocco');
	$motivo_blocco = $cls_help->getVar('motivo_blocco');
	$note_blocco = $cls_help->getVar('note_blocco');

	if($flag_blocco=="si")
	{
		$partita["Motivo_Blocco"] = $motivo_blocco;
		$partita["Note_Blocco"] = $note_blocco;
	}
	else
	{
		$flag_blocco = "";
		$partita["Motivo_Blocco"] = null;
		$partita["Note_Blocco"] = "";
	}

	$partita["Flag_Blocco_Coazione"] = $flag_blocco;

/*************************************************************************************************** AGGIORNA CLASSE PARTITA ***********************************************************************************/

/*$a_paramsPartitaTr = array(
		'table' => 'partita_tributi',
		'fields'=> array(
		)
);

foreach($partita as $key => $val) {

	$Type = is_numeric($val)?"int":"string";
	if($val=='null'){$Type = "int"; $val="";}

	array_push($a_paramsPartitaTr['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
}*/

$a_paramsPartitaTr = $cls_Utils->GetObjectQuery($partita,"partita_tributi");

$a_paramsPartitaTr['updateField'] = array('name'=>'ID',  'type'=>'int',  'value'=> $partita_ID);

if(!$cls_db->DbSave($a_paramsPartitaTr))
{

	$error = 1;
	$msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
	$cls_db->Rollback();
	header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
	die;
}else $msg = "Dati aggiornati correttamente";

	//$control_partita = $partita->Update($partita_ID);

/***************************************************************************************************************************************************************************************************************/

	/*if( $control_partita === false )
	{
		echo 'ERROR '.mysql_error();
		mysql_query('ROLLBACK');
	}

	mysql_query('COMMIT');
	echo 'OK '.$partita_ID.' '.$pignoramento_ID;*/

	$cls_db->End_Transaction();
	header("Location: pignoramento.php?partita={$partita_ID}&pignoramento={$pignoramento_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");
?>
