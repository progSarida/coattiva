<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";
	include_once CLS . "/cls_DateTimeInLine.php";
	include_once CLS . "/cls_math.php";
	include_once CLS . "/cls_GestionePartita.php";

	$cls_db = new cls_db();
	$cls_help = new cls_help();
	$cls_date = new cls_DateTimeI("DB",false);
	$cls_math = new cls_math();
	$cls_partita = new cls_GP();

	if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	$invia = $cls_help->getVar('invia_submit');
	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');

	$partita_ID = $cls_help->getVar('partita');
	$error = 0;
	$msg = "";

	//$query = "SELECT * FROM partita_tributi WHERE ID = '".$partita_ID."' AND CC = '".$c."'";
	$partita = $cls_partita->getDataPartita( $partita_ID , $c , $a); //$cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	//$partita = new partita( $partita_ID , $c , $a );

	$atto = $partita["Atto"];
	$tributo = $partita["Tributo"];

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	switch($invia)
	{
		case "Update":

			$ing = $atto[count($atto)-1];
			$ID_ingiunzione = $ing["ID"];

			//IMPORTI
            $spese_not_prec = $ing["Spese_Notifica_Precedenti"];
            $spese_not = $cls_math->conv_num($cls_help->getVar('spese_ing'));
            $sel_spese = $cls_help->getVar('CAN_CAD');
            $spese_can_cad = $cls_math->conv_num($cls_help->getVar('can_cad'));

			$interessi = $cls_help->getVar('interessi_ing');
			$interessi = $cls_math->conv_num($interessi);
			$interessi_prec = $cls_math->conv_num($cls_help->getVar('interessi_prec_ing'));

			$pagamenti_precedenti = $cls_math->conv_num($cls_help->getVar('pagamenti_precedenti'));

            $diritto_min = $cls_math->conv_num($cls_help->getVar('diritto_min'));
            $diritto_max = $cls_math->conv_num($cls_help->getVar('diritto_max'));

            $totale_dovuto = $cls_math->conv_num($cls_help->getVar('tot_dovuto_ing'));

			$control_rate = $ing["Rate_Previste"];
			$data_stampa = $ing["Data_Stampa"];

			$protocollo = $cls_help->getVar('protocollo');
      $data_protocollo = $cls_date->GetDateDB($cls_help->getVar('data_protocollo'),"IT");

			$stato_notifica = $cls_help->getVar('stato_not');

			$indirizzo_validato = $cls_help->getVar('indirizzo_validato');
			if($indirizzo_validato!="si")	$indirizzo_validato = "no";

			$motivo_notifica = $cls_help->getVar('motivo_not');
			$modalita_notifica = $cls_help->getVar('modalita_not');
			$note_notifica = $cls_help->getVar('note_notifica');

			$rielabora_flag = $cls_help->getVar('rielabora');
			if($rielabora_flag!="si")	$rielabora_flag = "no";

			$rettifica_flag = $cls_help->getVar('rettifica');
			if($rettifica_flag!="si")	$rettifica_flag = "no";

			$tipo_ufficiale = $cls_help->getVar('tipo_ufficiale');
			$modalita_stampa = $cls_help->getVar('modalita_stampa');
            $printerId = $cls_help->getVar('PrinterId');

			$data_notifica = $cls_date->GetDateDB($cls_help->getVar('data_notifica'),"IT");
			$num_rate = $cls_help->getVar('num_rate');
			$rateizza = $cls_help->getVar('rateizza');
			$data_richiesta = $cls_help->getVar('data_richiesta');
			$tipo_tot_rate = $cls_help->getVar('importo_rateizzazione');

			if($data_notifica=="")	$data_notifica = null;


			//mysql_query('BEGIN');

			//$salva = new atto(null,$c);
			$salva = array();
			$salva["Tipo_Totale_Rate"] = null;
			$salva["Rate_Previste"] = null;
			$salva["Importi_Rate"] = null;
			$salva["Scadenze_Rate"] = null;
			$salva["ID_Richiesta_Rateizzazione"] = null;
			$salva["ID_Esito_Rateizzazione"] = null;
			$salva["ID_Bollettini_Rateizzazione"] = null;

			if($rateizza == "rateizza")
			{
				$salva["Data_Richiesta_Rate"] = $cls_date->GetDateDB($data_richiesta,"IT");
				if( $num_rate!=null && $control_rate == 0 )
				{
					$salva["Tipo_Totale_Rate"] = $tipo_tot_rate;
					if($tipo_tot_rate == 1)
						$totale_dovuto_rate = $totale_dovuto+$diritto_min;
					else
						$totale_dovuto_rate = $totale_dovuto+$diritto_max;

					$totale_dovuto_rate-= $pagamenti_precedenti;
					$cls_date->changeFormat("IT",false);
					if($cls_date->Get_DateNewFormat($data_notifica,"DB")=="")
					{
						$cls_date->changeFormat("DB",false);

						$anno = substr($cls_date->GetDateDB($data_richiesta,"IT"), 0,4);

						$query = "SELECT A_Mani, A_Mani_Data, A_Mani_New FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$anno."' AND Tipo_Riscossione = '*****'";
						$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
						$salva["A_Mani"] = $result['A_Mani'];
						if( $cls_date->GetDateDB($data_richiesta,"IT") >= $result['A_Mani_Data'] && $result['A_Mani_Data'] != null )
						{
							$salva["A_Mani"] = $result['A_Mani_New'];
						}

						//$parametro = new parametri_annuali($c, $cls_date->GetDateDB($data_richiesta,"IT") , $partita->Tipo);
						$spese_notifica_rate = $salva["A_Mani"];
						$totale_dovuto_rate += $spese_notifica_rate;
					}
					$cls_date->changeFormat("DB",false);

					$importo_interesse = round ($totale_dovuto_rate / $num_rate, 2 );
					$prima_rata = floatval( $importo_interesse + ( $totale_dovuto_rate - $importo_interesse * $num_rate ) );

                    $importi_rate = number_format($prima_rata,2,",","");
					$importo_interesse = number_format($importo_interesse,2,",","");
					for($i=1;$i<$num_rate;$i++)
						$importi_rate .= "*".$importo_interesse;

					$data_partenza = date('Y-m-d', strtotime("-1 months +10 days"));
					$scadenze = $cls_math->next_months( $data_partenza , $num_rate);

					echo "<br>data partenza ".$data_partenza." --- scadenze ".$scadenze."<br>";

					$salva["Rate_Previste"] = $num_rate;
					$salva["Importi_Rate"] = $importi_rate;
					$salva["Scadenze_Rate"] = $scadenze;

				}

			}
			else
			{
				$salva["Rate_Previste"] = "";
				$salva["Importi_Rate"] = "";
				$salva["Scadenze_Rate"] = "";
				$salva["Data_Richiesta_Rate"] = null;
				$salva["ID_Richiesta_Rateizzazione"] = 0;
				$salva["ID_Esito_Rateizzazione"] = 0;
				$salva["ID_Bollettini_Rateizzazione"] = 0;
			}

			$salva["Stato_Notifica"] = $stato_notifica;
			$salva["Indirizzo_Validato"] = $indirizzo_validato;
			$salva["Motivo_Notifica"] = $motivo_notifica;
			$salva["Modalita_Notifica"] = $modalita_notifica;
			$salva["Note_Notifica"] = $note_notifica;
			$salva["Rielabora_Flag"] = $rielabora_flag;
			$salva["Rettifica_Flag"] = $rettifica_flag;
			$salva["Protocollo"] = strtoupper($protocollo);
			$salva["Data_Protocollo"] = $data_protocollo;

			$salva["Tipo_Ufficiale"] = $tipo_ufficiale;
			$salva["Modalita_Stampa"] = $modalita_stampa;
            $printTypeId = 0;
			switch($modalita_stampa){
                case "posta":           $printTypeId=1;   break;
                case "raccomandata":    $printTypeId=2;   break;
                case "ordinaria":       $printTypeId=3;   break;
                case "mani":            $printTypeId=6;   break;
            }
			$salva["PrintTypeId"] = $printTypeId;
            $salva["PrinterId"] = $printerId;
			$salva["Stato_Stampa"] = "Da stampare";

			if($data_notifica != null )
			{
				if($data_stampa == null)
				{
					$salva["Stato_Stampa"] = "Stampato";
					$salva["Data_Stampa"] = "2014-01-01";
					$salva["Cronologico_Vecchio"] = "si";
				}
			}


			$salva["Spese_Notifica"] = $spese_not;
            $salva["Spese_Notifica_Precedenti"] = $spese_not_prec;
			$salva["Data_Notifica"] = $data_notifica;
			$salva["Interessi"] = $interessi;
            $salva["Interessi_Precedenti"] = $interessi_prec;
			$salva["Totale_Dovuto"] = $totale_dovuto;
			$salva["Diritto_Riscossione_Minimo"] = $diritto_min;
            $salva["Diritto_Riscossione_Massimo"] = $diritto_max;

			if($sel_spese == "CAN")
			{
				$salva["CAN"] = $spese_can_cad;
				$salva["CAD"] = "0.00";
			}
			else if($sel_spese == "CAD")
			{
				$salva["CAD"] = $spese_can_cad;
				$salva["CAN"] = "0.00";
			}
			else
			{
				$salva["CAD"] = "0.00";
				$salva["CAN"] = "0.00";
			}

			//echo "<h1>CAN ".$salva["CAN"]." --- CAD ".$salva["CAD"]."</h1>";
			if(!isset($salva["Cronologico_Vecchio"])) $salva["Cronologico_Vecchio"] = "";
			//if(!isset($salva["Data_Stampa"])) $salva["Data_Stampa"] = null;
			$salva["Data_Stampa"] = $data_stampa;
			if(!isset($salva["Stato_Stampa"])) $salva["Stato_Stampa"] = "";

			$a_paramsIngiunzione = array(
			    'table' => 'atto',
			    'fields'=> array(
			        array(  'name' => 'Data_Richiesta_Rate',            'type' => 'date',   'value' => $salva["Data_Richiesta_Rate"]),
			        array(  'name' => 'Tipo_Totale_Rate',               'type' => 'int',    'value' => $salva["Tipo_Totale_Rate"]),
			        array(  'name' => 'Rate_Previste',                  'type' => 'int',    'value' => $salva["Rate_Previste"]),
			        array(  'name' => 'Importi_Rate',                   'type' => 'string', 'value' => $salva["Importi_Rate"]),
			        array(  'name' => 'Scadenze_Rate',                  'type' => 'string', 'value' => $salva["Scadenze_Rate"]),
			        array(  'name' => 'ID_Richiesta_Rateizzazione',     'type' => 'int',    'value' => $salva["ID_Richiesta_Rateizzazione"]),
			        array(  'name' => 'ID_Esito_Rateizzazione',         'type' => 'int',    'value' => $salva["ID_Esito_Rateizzazione"]),
			        array(  'name' => 'ID_Bollettini_Rateizzazione',    'type' => 'int',    'value' => $salva["ID_Bollettini_Rateizzazione"]),
			        array(  'name' => 'Stato_Notifica',                 'type' => 'int',    'value' => $salva["Stato_Notifica"]),
			        array(  'name' => 'Indirizzo_Validato',             'type' => 'string', 'value' => $salva["Indirizzo_Validato"]),
			        array(  'name' => 'Motivo_Notifica',                'type' => 'int',    'value' => $salva["Motivo_Notifica"]),
			        array(  'name' => 'Modalita_Notifica',              'type' => 'int',    'value' => $salva["Modalita_Notifica"]),
			        array(  'name' => 'Note_Notifica',                  'type' => 'string', 'value' => $salva["Note_Notifica"]),
			        array(  'name' => 'Rielabora_Flag',                 'type' => 'string', 'value' => $salva["Rielabora_Flag"]),
			        array(  'name' => 'Rettifica_Flag',                 'type' => 'string', 'value' => $salva["Rettifica_Flag"]),
			        array(  'name' => 'Protocollo',                     'type' => 'string', 'value' => $salva["Protocollo"]),
			        array(  'name' => 'Data_Protocollo',                'type' => 'date',   'value' => $salva["Data_Protocollo"]),
			        array(  'name' => 'Tipo_Ufficiale',                 'type' => 'string', 'value' => $salva["Tipo_Ufficiale"]),
			        array(  'name' => 'Modalita_Stampa',                'type' => 'string', 'value' => $salva["Modalita_Stampa"]),
			        array(  'name' => 'PrintTypeId',                    'type' => 'int',    'value' => $salva["PrintTypeId"]),
			        array(  'name' => 'PrinterId',                      'type' => 'int',    'value' => $salva["PrinterId"]),
			        array(  'name' => 'Stato_Stampa',                   'type' => 'string', 'value' => $salva["Stato_Stampa"]),
			        array(  'name' => 'Data_Stampa',                    'type' => 'date',   'value' => $salva["Data_Stampa"]),
			        array(  'name' => 'Cronologico_Vecchio',            'type' => 'string', 'value' => $salva["Cronologico_Vecchio"]),
			        array(  'name' => 'Spese_Notifica',                 'type' => 'int',    'value' => $salva["Spese_Notifica"]),
			        array(  'name' => 'Spese_Notifica_Precedenti',      'type' => 'int',    'value' => $salva["Spese_Notifica_Precedenti"]),
			        array(  'name' => 'Data_Notifica',                  'type' => 'date',   'value' => $salva["Data_Notifica"]),
			        array(  'name' => 'Interessi',                      'type' => 'int',    'value' => $salva["Interessi"]),
			        array(  'name' => 'Interessi_Precedenti',           'type' => 'int',    'value' => $salva["Interessi_Precedenti"]),
			        array(  'name' => 'Totale_Dovuto',                  'type' => 'int',    'value' => $salva["Totale_Dovuto"]),
			        array(  'name' => 'Diritto_Riscossione_Minimo',     'type' => 'int',    'value' => $salva["Diritto_Riscossione_Minimo"]),
			        array(  'name' => 'Diritto_Riscossione_Massimo',    'type' => 'int',    'value' => $salva["Diritto_Riscossione_Massimo"]),
			        array(  'name' => 'CAD',                            'type' => 'int',    'value' => $salva["CAD"]),
			        array(  'name' => 'CAN',                            'type' => 'int',    'value' => $salva["CAN"])
			    ),
					'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_ingiunzione)
			);


			//$control_salva = $salva->Update($ID_ingiunzione, true);

			if(!$cls_db->DbSave($a_paramsIngiunzione) )
			{
				$cls_db->Rollback();
				$error = 1;
				$msg = "Aggiornamento non riuscito";
				$cls_db->End_Transaction();
				header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				die;
			}
			else
			{
				$partitaArray = array();
				$flag_blocco = $cls_help->getVar('flag_blocco');
				$motivo_blocco = $cls_help->getVar('motivo_blocco');
				$note_blocco = $cls_help->getVar('note_blocco');

				$flag_maggiorazione = $cls_help->getVar('flag_maggiorazione');
				$flag_diritto_riscossione = $cls_help->getVar('flag_diritto_riscossione');

				if($flag_blocco=="si")
				{
					$partitaArray["Motivo_Blocco"] = $motivo_blocco;
					$partitaArray["Note_Blocco"] = $note_blocco;
				}
				else
				{
					$flag_blocco = "";
					$partitaArray["Motivo_Blocco"] = null;
					$partitaArray["Note_Blocco"] = "";
				}

				$partitaArray["Flag_Blocco_Coazione"] = $flag_blocco;

				if($flag_maggiorazione!="si")
					$flag_maggiorazione = "";

				if($flag_diritto_riscossione!="si")
					$flag_diritto_riscossione = "";

				$partitaArray["Flag_Blocco_Maggiorazioni"] = $flag_maggiorazione;
				$partitaArray["Flag_Blocco_Diritto_Riscossione"] = $flag_diritto_riscossione;



				$a_paramsPartita = array(
				    'table' => 'partita_tributi',
				    'fields'=> array(
				        array(  'name' => 'Motivo_Blocco',                     'type' => 'int',    'value' => $partitaArray["Motivo_Blocco"]),
				        array(  'name' => 'Note_Blocco',                       'type' => 'string', 'value' => $partitaArray["Note_Blocco"]),
				        array(  'name' => 'Flag_Blocco_Coazione',              'type' => 'string', 'value' => $partitaArray["Flag_Blocco_Coazione"]),
				        array(  'name' => 'Flag_Blocco_Maggiorazioni',         'type' => 'string', 'value' => $partitaArray["Flag_Blocco_Maggiorazioni"]),
				        array(  'name' => 'Flag_Blocco_Diritto_Riscossione',   'type' => 'string', 'value' => $partitaArray["Flag_Blocco_Diritto_Riscossione"])
				    ),
						'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $partita_ID)
				);


				if(!$cls_db->DbSave($a_paramsPartita) )
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Aggiornamento non riuscito";
					$cls_db->End_Transaction();
					header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
					die;
				}else $msg = "Aggiornamento riuscito";
				//$control_partita = $partita->Update($partita_ID);

				/*if( $control_partita )
				{
					mysql_query('COMMIT');

					echo 'OK '.$partita_ID;
				}
				else
				{
					echo 'ERROR '.mysql_error();
					mysql_query('ROLLBACK');
				}*/
			}

			$cls_db->End_Transaction();
			header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				//echo 'ERROR '.mysql_error();
				//mysql_query('ROLLBACK');
				die;
		break;

		case "rate":

			$scadenza = $cls_help->getVar('scadenza');
			$importo_rata = $cls_help->getVar('importo');
			$num_rate = count($scadenza);

			$nominativo_gestore_rateizzazione = $cls_help->getVar('nome_gestore');
			$posizione_gestore_rateizzazione = $cls_help->getVar('posizione_gestore');
			$esito_richiesta = $cls_help->getVar('esito_richiesta');
			$motivazione = $cls_help->getVar('richiesta_respinta');
			$operatore = $cls_help->getVar('operatore');

			$ID = $cls_help->getVar('atto');

			$scadenze = $scadenza[0];
			$importi_rate = $importo_rata[0];
			for($i=1;$i<$num_rate;$i++)
			{
				$scadenze .= "*".$scadenza[$i];
				$importi_rate .= "*".$importo_rata[$i];
			}

			//$salva = new atto(null,$c);

			$a_paramsAtto = array(
					'table' => 'atto',
					'fields'=> array(
							array(  'name' => 'Rate_Previste',                      'type' => 'int',    'value' => $num_rate),
							array(  'name' => 'Importi_Rate',                       'type' => 'string', 'value' => $importi_rate),
							array(  'name' => 'Scadenze_Rate',                      'type' => 'string', 'value' => $scadenze),
							array(  'name' => 'Nominativo_Gestore_Rateizzazione',   'type' => 'string', 'value' => $nominativo_gestore_rateizzazione),
							array(  'name' => 'Posizione_Gestore_Rateizzazione',    'type' => 'string', 'value' => $posizione_gestore_rateizzazione),
							array(  'name' => 'Esito_Richiesta_Rateizzazione',      'type' => 'string', 'value' => $esito_richiesta),
							array(  'name' => 'Operatore_Rateizzazione',            'type' => 'string', 'value' => $operatore)
					),
					'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID)
			);

			/*$salva->Rate_Previste = $num_rate;
			$salva->Importi_Rate = $importi_rate;
			$salva->Scadenze_Rate = $scadenze;
			$salva->Nominativo_Gestore_Rateizzazione = $nominativo_gestore_rateizzazione;
			$salva->Posizione_Gestore_Rateizzazione = $posizione_gestore_rateizzazione;
			$salva->Esito_Richiesta_Rateizzazione = $esito_richiesta;*/
			if($esito_richiesta=='respinta')
				array_push($a_paramsAtto['fields'], array(  'name' => 'Motivazione_Respinta_Rateizzazione',   'type' => 'string', 'value' => $motivazione));
				//$salva->Motivazione_Respinta_Rateizzazione = $motivazione;
			//$salva->Operatore_Rateizzazione = $operatore;

			if(!$cls_db->DbSave($a_paramsAtto) )
			{
				$cls_db->Rollback();
				//$error = 1;
				//$msg = "Aggiornamento non riuscito";
				$cls_db->End_Transaction();
				//header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				echo 'ERROR '.$cls_db->GetError();
				die;
			}
			else
			{
				//$msg = "Aggiornamento riuscito";
				//header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				$cls_db->End_Transaction();
				echo 'OK '.$partita_ID;
				die;
			}

			/*mysql_query('BEGIN');

			$control_salva = $salva->Update( $ID , true );

			if( $control_salva )
			{
				mysql_query('COMMIT');

				echo 'OK '.$partita_ID;
			}
			else
			{
				echo 'ERROR '.mysql_error();
				mysql_query('ROLLBACK');
			}*/

			break;

			case "spedizione":

			$num_flusso = $cls_help->getVar('num_flusso');
			$data_spedizione = $cls_help->getVar('data_spedizione');
			$estremi_spedizione = $cls_help->getVar('estremi_spedizione');
			$estremi_ar = $cls_help->getVar('estremi_ar');
			$log = $cls_help->getVar('log');
			$scatola = $cls_help->getVar('scatola');
			$lotto = $cls_help->getVar('lotto');
			$posizione = $cls_help->getVar('posizione');
			$data_importazione = $cls_help->getVar('data_importazione');

			$ID = $cls_help->getVar('atto');

			/*$salva = new atto(null,$c);

			$salva->Num_Flusso = $num_flusso;
			$salva->Data_Spedizione = $cls_date->GetDateDB($data_spedizione,"IT");
			$salva->Estremi_Spedizione = $estremi_spedizione;
			$salva->Estremi_AR = $estremi_ar;
			$salva->Data_LOG = $cls_date->GetDateDB($log,"IT");
			$salva->Scatola = $scatola;
			$salva->Lotto = $lotto;
			$salva->Posizione = $posizione;
			$salva->Data_Importazione = $cls_date->GetDateDB($data_importazione,"IT");*/

			$a_paramsAtto = array(
					'table' => 'atto',
					'fields'=> array(
							array(  'name' => 'Num_Flusso',             'type' => 'int',    'value' => $num_flusso),
							array(  'name' => 'Data_Spedizione',        'type' => 'date',   'value' => $cls_date->GetDateDB($data_spedizione,"IT")),
							array(  'name' => 'Estremi_Spedizione',     'type' => 'string', 'value' => $estremi_spedizione),
							array(  'name' => 'Estremi_AR',             'type' => 'string', 'value' => $estremi_ar),
							array(  'name' => 'Data_LOG',               'type' => 'date',   'value' => $cls_date->GetDateDB($log,"IT")),
							array(  'name' => 'Scatola',                'type' => 'int',    'value' => $scatola),
							array(  'name' => 'Lotto',                  'type' => 'int',    'value' => $lotto),
							array(  'name' => 'Posizione',              'type' => 'int',    'value' => $posizione),
							array(  'name' => 'Data_Importazione',      'type' => 'date',   'value' => $cls_date->GetDateDB($data_importazione,"IT"))
					),
					'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID)
			);

			if(!$cls_db->DbSave($a_paramsAtto))
			{
				$cls_db->Rollback();
				$error = 1;
				$msg = "Errore impossibile aggiornare i dati";
				$cls_db->End_Transaction();
				header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				die;
			}
			else
			{
				$msg = "Dati aggiornati correttamente";
				$cls_db->End_Transaction();
				header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				die;
			}

			/*mysql_query('BEGIN');

			$control_salva = $salva->Update( $ID , true );

			if( $control_salva )
			{
				mysql_query('COMMIT');

				echo 'OK '.$partita_ID;
			}
			else
			{
				mysql_query('ROLLBACK');

				echo 'ERROR '.$partita_ID;
			}*/

			break;

			case "Delete":

				$ing = $atto[count($atto)-1];
				$ID_ingiunzione = $ing["ID"];

				if($ing["Pagamento"]!=null)
				{
					$msg = "ATTENZIONE!\n\nSono stati rilevati dei pagamenti collegati all'atto che si desidera eliminare.\nPer eliminare l'atto è necessario procedere alla cancellazione dei relativi pagamenti.\n\n";
					$error = 2;
					header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
					die;
				}
				else
				{

				//$cancella = new atto($ID_ingiunzione,$c);
				if(!$cls_db->Delete("atto","ID = ".$ID_ingiunzione))
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Errore impossibile eliminare i dati";
				}else	$msg = "Dati eliminati con successo";

				$cls_db->End_Transaction();
				header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				die;
				//mysql_query('BEGIN');
				//$control_cancella = $cancella->Delete();

				/*if( $control_cancella )
				{
					mysql_query('COMMIT');

					echo 'DELETE '.$partita_ID;
				}
				else
				{
					echo 'ERROR '.mysql_error()." ".$partita_ID;

					mysql_query('ROLLBACK');
				}*/

				}


				break;

			case "Interessi":

				$ing = $atto[count($atto)-1];
				$ID_ingiunzione = $ing["ID"];

				//$salva = new atto($ID_ingiunzione,$c);
				$query = "SELECT * FROM atto WHERE ID = ".$ID_ingiunzione." AND CC = '".$c."'";
				$salva = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");

				$Totale_Dovuto = $salva->Totale_Dovuto - $salva->Interessi - $salva->Interessi_Precedenti;
				//$salva->Interessi = 0;
				//$salva->Interessi_Precedenti = 0;

				$a_paramsIng = array(
						'table' => 'atto',
						'fields'=> array(
								array(  'name' => 'Totale_Dovuto',            'type' => 'int',   'value' => $Totale_Dovuto),
								array(  'name' => 'Interessi',                'type' => 'int',   'value' => "0"),
								array(  'name' => 'Interessi_Precedenti',     'type' => 'int',   'value' => "0")
						),
						'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_ingiunzione)
				);

				if(!$cls_db->DbSave($a_paramsIng))
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Errore impossibile aggiornare i dati";
				}else $msg = "Dati aggiornati correttamente";

				$cls_db->End_Transaction();
				header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				die;

				/*mysql_query('BEGIN');

				$control_salva = $salva->Update($ID_ingiunzione, true);

				if( $control_salva )
				{
					mysql_query('COMMIT');

					echo 'OK '.$partita_ID;
				}
				else
				{
					mysql_query('ROLLBACK');

					echo 'ERROR '.mysql_error();
				}*/

				break;

			case "Sanzione":

				$ing = $atto[count($atto)-1];
				$ID_ingiunzione = $ing["ID"];

				//$salva = new atto($ID_ingiunzione,$c);
				$query = "SELECT * FROM atto WHERE ID = ".$ID_ingiunzione." AND CC = '".$c."'";
				$salva = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");

				$Totale_Dovuto = $salva->Totale_Dovuto - $salva->Sanzione;
				//$salva->Sanzione = 0;


				$a_paramsIng = array(
						'table' => 'atto',
						'fields'=> array(
								array(  'name' => 'Totale_Dovuto',   'type' => 'int',   'value' => $Totale_Dovuto),
								array(  'name' => 'Sanzione',        'type' => 'int',   'value' => "0")
						),
						'updateField' => array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_ingiunzione)
				);

				if(!$cls_db->DbSave($a_paramsIng))
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Errore impossibile aggiornare i dati";
				}else $msg = "Dati aggiornati correttamente";

				$cls_db->End_Transaction();
				header("Location: ingiunzione.php?c={$c}&a={$a}&partita={$partita_ID}&error={$error}&msg={$msg}");
				die;

				/*mysql_query('BEGIN');

				$control_salva = $salva->Update($ID_ingiunzione, true);

				if( $control_salva )
				{
					mysql_query('COMMIT');

					echo 'OK '.$partita_ID;
				}
				else
				{
					mysql_query('ROLLBACK');

					echo 'ERROR '.mysql_error();
				}*/

				break;

	}
?>
