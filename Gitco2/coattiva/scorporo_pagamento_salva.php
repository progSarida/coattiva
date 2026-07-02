<?php

	if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";
	include_once CLS . "/cls_math.php";
	include_once CLS . "/cls_Utils.php";
	include_once(CLS."/cls_GestionePartita.php");
	include_once CLS . "/cls_storico.php";

	$storico = new storico('storicoRuolo','3');
	$cls_partita = new cls_GP();
	$cls_db = new cls_db();
	$cls_help = new cls_help();
	$cls_math = new cls_math();
	$cls_utils = new cls_Utils();

	if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
		die;
	}

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');

	$error = 0;
	$msg = "";
	$flag_upd_1 = false;
	$flag_upd_2 = false;
	$flag_ins = false;

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	$partita_ID = $cls_help->getVar('partita');
	$params_id = $cls_help->getVar('params_id');
	$id_pagamento = $cls_help->getVar('id_pagamento');
	$importo_pagato = $cls_help->getVar('importo_pagato');
	for($i=1;$i<=16;$i++){
	    $split[$i] = $cls_help->getVar('split'.$i);
    }
	$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

	// Recupero dati partita e utente
	$partita_query = "SELECT PT.Comune_ID AS Rif_P, PT.CC, T.Info_Cartella AS Info, EG.Denominazione AS Ente, ";
	$partita_query.= "IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(Cognome,''),' ',COALESCE(Nome.''))) as Utente, U.Comune_ID AS Rif_U FROM partita_tributi AS PT ";
	$partita_query.= "LEFT JOIN tributo AS T ON PT.ID = T.Partita_ID ";
	$partita_query.= "LEFT JOIN utente AS U ON PT.Utente_ID = U.ID ";
	$partita_query.= "LEFT JOIN enti_gestiti AS EG ON PT.CC = EG.CC ";
	$partita_query.= "WHERE PT.ID = ".$partita_ID;
	
	$info = $cls_db->getResults($cls_db->ExecuteQuery($partita_query));


		$query = "SELECT * FROM partita_tributi WHERE ID = '".$partita_ID."' AND CC = '".$c."'";
		$partita = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	  //$partita = new partita( $partita_ID , $c);
    if(!$partita["Split_Parameters_ID"]>0){

			$a_paramsPartita = array(
					'table' => 'partita_tributi',
					'fields'=> array(
							array(  'name' => 'Split_Parameters_ID',   'type' => 'int', 'value' => $params_id)
					),
					'updateField' => array('name' => 'ID',   'type' => 'int', 'value' => $partita_ID)
			);

			if(!$cls_db->DbSave($a_paramsPartita))
			{
				$error = 1;
				$msg = "Errore impossibile aggiornare i dati \n".$cls_db->GetError();
				$cls_db->Rollback();
			}else{
				$flag_upd_1 = true;
				//$storico->insRow('U', "Modificato scorporo pagamenti partita ".$tributo[0]["Info_Cartella"]."(".$partita['Comune_ID'].") dell'utente ".$msg_utente);
				$msg = "Dati aggiornati correttamente";
			}

			//$cls_db->End_Transaction();
			//header("Location: pagamento.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");

        //$partita["Split_Parameters_ID"] = $params_id;
        //$control_salva = $partita->Update($partita_ID);
    }
print_r($id_pagamento);
echo "<br>";
	for($i=0;$i<count($id_pagamento);$i++){



		//mysql_query('BEGIN');

		//$salva = new pagamento($id_pagamento[$i], $c);

		$query = "SELECT * FROM pagamento WHERE ID = '".$id_pagamento[$i]."' AND CC = '".$c."'";
		$result = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "pagamento");

		$result->Importo = $cls_math->conv_num($importo_pagato[$i]);
		/*$a_paramsPag = array(
				'table' => 'pagamento',
				'fields'=> array(
						array(  'name' => 'Importo',   'type' => 'int', 'value' => $cls_math->conv_num($importo_pagato[$i])),
						array(  'name' => 'Comune_ID',   'type' => 'int', 'value' => $result["Comune_ID"]),
						array(  'name' => 'Atto_ID',   'type' => 'int', 'value' => $result["Atto_ID"]),
						array(  'name' => 'DocumentTypeId',   'type' => 'int', 'value' => $result["DocumentTypeId"]),
						array(  'name' => 'DocumentTableTypeId',   'type' => 'int', 'value' => $result["DocumentTableTypeId"]),
						array(  'name' => 'Riferimento_Atto',   'type' => 'int', 'value' => $result["Riferimento_Atto"]),
						array(  'name' => 'Data_Pagamento',   'type' => 'date', 'value' => $result["Data_Pagamento"]),
						array(  'name' => 'Pagante',   'type' => 'string', 'value' => $result["Pagante"]),
						array(  'name' => 'Modalita',   'type' => 'string', 'value' => $result["Modalita"]),
						array(  'name' => 'Conto_Terzi',   'type' => 'string', 'value' => $result["Conto_Terzi"]),
						array(  'name' => 'Dovuto',   'type' => 'int', 'value' => $result["Dovuto"]),
						array(  'name' => 'Quietanza',   'type' => 'string', 'value' => $result["Quietanza"]),
						array(  'name' => 'Bollettario',   'type' => 'string', 'value' => $result["Bollettario"]),
						array(  'name' => 'Telematico',   'type' => 'string', 'value' => $result["Telematico"]),
						array(  'name' => 'Tipo_Pagamento',   'type' => 'string', 'value' => $result["Tipo_Pagamento"]),
						array(  'name' => 'Rata',   'type' => 'int', 'value' => $result["Rata"]),
						array(  'name' => 'Totale_Rate',   'type' => 'int', 'value' => $result["Totale_Rate"]),
						array(  'name' => 'Note',   'type' => 'string', 'value' => $result["Note"]),
						array(  'name' => 'Bollettino',   'type' => 'string', 'value' => $result["Bollettino"]),
						array(  'name' => 'Data_Travaso_A_Gitco',   'type' => 'date', 'value' => $result["Data_Travaso_A_Gitco"]),
						array(  'name' => 'Scorporo_Tributo',   'type' => 'int', 'value' => $result["Scorporo_Tributo"]),
						array(  'name' => 'Scorporo_Eca',   'type' => 'int', 'value' => $result["Scorporo_Eca"]),
						array(  'name' => 'Scorporo_Tributo_Provinciale',   'type' => 'int', 'value' => $result["Scorporo_Tributo_Provinciale"]),
						array(  'name' => 'Scorporo_Spese_Ricerca',   'type' => 'int', 'value' => $result["Scorporo_Spese_Ricerca"]),
						array(  'name' => 'Scorporo_Spese_Precedenti',   'type' => 'int', 'value' => $result["Scorporo_Spese_Precedenti"]),
						array(  'name' => 'Scorporo_Spese_Notifica',   'type' => 'int', 'value' => $result["Scorporo_Spese_Notifica"]),
						array(  'name' => 'Scorporo_Interessi',   'type' => 'int', 'value' => $result["Scorporo_Interessi"]),
						array(  'name' => 'Scorporo_Spese_Accessorie',   'type' => 'int', 'value' => $result["Scorporo_Spese_Accessorie"]),
						array(  'name' => 'Scorporo_Notifica_Pignoramento',   'type' => 'int', 'value' => $result["Scorporo_Notifica_Pignoramento"]),
						array(  'name' => 'Scorporo_Diritto_Riscossione',   'type' => 'int', 'value' => $result["Scorporo_Diritto_Riscossione"])
				)
		);*/

	//	$salva->Importo = conv_num($importo_pagato[$i]);
        for($y=1;$y<=16;$y++){
            if(isset($split[$y])){
                if($split[$y][$i]==null)
                    $split[$y][$i] = "0,00";
				//array_push($a_paramsPag['fields'],array(  'name' => 'Split_Payment'.$y,   'type' => 'int', 'value' => $cls_math->conv_num($split[$y][$i])));
                $key = "Split_Payment".$y;
				$result->$key = $cls_math->conv_num($split[$y][$i]);
            }
			else {
				$split[$y][$i] = "0,00";
				$key = "Split_Payment".$y;
				$result->$key = $cls_math->conv_num($split[$y][$i]);
					//array_push($a_paramsPag['fields'],array(  'name' => 'Split_Payment'.$y,   'type' => 'int', 'value' => $cls_math->conv_num($split[$y][$i])));
			}
        }
				echo "<h1>aa ".$id_pagamento[$i]."</h1>";

		if($id_pagamento[$i]>0)
		{
			//$a_paramsPag['updateField'] = array(  'name' => 'ID',   'type' => 'int', 'value' => $id_pagamento[$i]);

			if(!$cls_db->DbSave($cls_utils->GetObjectQuery((array)$result , "pagamento",array("ID" => $id_pagamento[$i]))))
			{
				$error = 1;
				$msg = "Errore impossibile aggiornare i dati \n".$cls_db->GetError();
				$cls_db->Rollback();
			}else{
				$flag_upd_2 = true;
				//$storico->insRow('U', "Modificato scorporo pagamenti partita ".$tributo[0]["Info_Cartella"]."(".$partita['Comune_ID'].") dell'utente ".$msg_utente);
				$msg = "Dati aggiornati correttamente";
			}
		//	$control_salva = $salva->Update($id_pagamento[$i],true);
		}
		else if($i==0){
			/*array_push($a_paramsPag['fields'],array(  'name' => 'Tipo_Atto',   'type' => 'string', 'value' => 'Precedenti'));
			array_push($a_paramsPag['fields'],array(  'name' => 'CC',   'type' => 'string', 'value' => $c));
			array_push($a_paramsPag['fields'],array(  'name' => 'Partita_ID',   'type' => 'int', 'value' => $partita_ID));
			array_push($a_paramsPag['fields'],array(  'name' => 'Data_Registrazione',   'type' => 'date', 'value' => date('Y-m-d')));*/
			$result->Tipo_Atto = "Precedenti";
			$result->CC = $c;
			$result->Partita_ID = $partita_ID;
			$result->Data_Registrazione = date('Y-m-d');
		//	$control_salva = $salva->Insert(true);
			if(!$cls_db->DbSave($cls_utils->GetObjectQuery((array)$result , "pagamento")))
			{
				$error = 1;
				$msg = "Errore impossibile inserire i dati \n".$cls_db->GetError();
				$cls_db->Rollback();
			}else{
				$flag_ins = true;
				$msg = "Dati inseriti correttamente";
			}
		}

		/*if(!$control_salva)
		{
			mysql_query('ROLLBACK');

			echo 'ERROR '.$partita_ID;
			die;
		}*/
	}
	if($flag_ins)
		$storico->insRow('I', "Inserito scorporo pagamenti partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
	if($flag_upd_1 || $flag_upd_2)
		$storico->insRow('U', "Modificato scorporo pagamenti partita ".$info[0]['Info']."(".$info[0]['Rif_P'].") dell'utente ".$info[0]['Utente']." (".$info[0]['Rif_U'].") per ente ".$info[0]['Ente']."[".$info[0]['CC']."]");
	$cls_db->End_Transaction();
	header("Location: scorporo_pagamento.php?partita={$partita_ID}&c={$c}&a={$a}&error={$error}&msg={$msg}");

	/*if(isset($id_pagamento[0])){
		echo 'OK '.$partita_ID;
		mysql_query('COMMIT');
	}
	else{
		echo 'ERROR '.$partita_ID;
	}

	die;*/

?>
