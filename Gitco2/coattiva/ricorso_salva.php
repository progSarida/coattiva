<?php
	
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/ruolo.php";
	
	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	
	$invia = get_var('invia_submit');
	$a = get_var('a');
	$c = get_var('c');
	$p = get_var('p');
	
	$partita_ID = get_var('partita');
	$id_ricorso = get_var('id_ricorso');
	
	$tipo = get_var('tipo');
	$data_reg = to_mysql_date(get_var('data_reg'));
	$data_chiusura = to_mysql_date(get_var('data_chiusura'));
	$id_ufficio = get_var('id_ufficio');
	
	$sospensiva = get_var('sospensiva');
	$num_sospensiva = get_var('num_sospensiva');
	$data_sospensiva = to_mysql_date(get_var('data_sospensiva'));
	$data_dep_sospensiva = to_mysql_date(get_var('data_dep_sospensiva'));
	$esito_sospensiva = get_var('esito_sospensiva');
	$data_not_sospensiva = to_mysql_date(get_var('data_not_sospensiva'));
	
	$merito = get_var('merito');
	$num_merito = get_var('num_merito');
	$data_merito = to_mysql_date(get_var('data_merito'));
	$data_dep_merito = to_mysql_date(get_var('data_dep_merito'));
	$esito_merito = get_var('esito_merito');
	$data_not_merito = to_mysql_date(get_var('data_not_merito'));
	
	$data_ric_sentenza = to_mysql_date(get_var('data_ric_sentenza'));
	$data_app_sentenza = to_mysql_date(get_var('data_app_sentenza'));
	$tot_da_pagare = number_format(conv_num(get_var('tot_da_pagare')),2);
	$RG_pagato = get_var('RG_pagato');
	if($RG_pagato==null) $RG_pagato == 'N';
	$socc_pagata = get_var('socc_pagata');
	if($socc_pagata==null) $socc_pagata == 'N';
	$importo = number_format(conv_num(get_var('importo')),2);
	$data_pag = to_mysql_date(get_var('data_pag'));
	$descr_pag = get_var('descr_pag');
	$note = get_var('note');
	
	$attore_ID_1 = get_var('attore_id_1');
	$attore_ID_2 = get_var('attore_id_2');
	$attore_ID_3 = get_var('attore_id_3');
	$attore_ID_4 = get_var('attore_id_4');
	
	$data_ruolo = to_mysql_date(get_var('data_ruolo'));
	$RGN = get_var('RGN');
	$data_fasc = to_mysql_date(get_var('data_fasc'));
	
	$avvo_attore = get_var('avvo_attore');
	$giudice_atto = get_var('giudice_atto');
	
	$data_depos_attore_1 = to_mysql_date(get_var('data_depos_attore_1'));
	$data_depos_attore_2 = to_mysql_date(get_var('data_depos_attore_2'));
	$data_depos_attore_3 = to_mysql_date(get_var('data_depos_attore_3'));
	$data_depos_attore_4 = to_mysql_date(get_var('data_depos_attore_4'));
	$data_depos_attore_5 = to_mysql_date(get_var('data_depos_attore_5'));
	$data_depos_attore_6 = to_mysql_date(get_var('data_depos_attore_6'));
	$data_depos_attore_7 = to_mysql_date(get_var('data_depos_attore_7'));
	$data_depos_attore_8 = to_mysql_date(get_var('data_depos_attore_8'));
	
	$convenuto_ID_1 = get_var('convenuto_id_1');
	$convenuto_ID_2 = get_var('convenuto_id_2');
	$convenuto_ID_3 = get_var('convenuto_id_3');
	$convenuto_ID_4 = get_var('convenuto_id_4');
	
	$avvo_convenuto = get_var('avvo_convenuto');
	
	$data_firma_1 = to_mysql_date(get_var('data_firma_1'));
	$data_firma_2 = to_mysql_date(get_var('data_firma_2'));
	$data_firma_3 = to_mysql_date(get_var('data_firma_3'));
	$data_firma_4 = to_mysql_date(get_var('data_firma_4'));
	
	$data_notifica_1 = to_mysql_date(get_var('data_notifica_1'));
	$data_notifica_2 = to_mysql_date(get_var('data_notifica_2'));
	$data_notifica_3 = to_mysql_date(get_var('data_notifica_3'));
	$data_notifica_4 = to_mysql_date(get_var('data_notifica_4'));
	
	$data_comparsa_1 = to_mysql_date(get_var('data_comparsa_1'));
	$data_comparsa_2 = to_mysql_date(get_var('data_comparsa_2'));
	$data_comparsa_3 = to_mysql_date(get_var('data_comparsa_3'));
	$data_comparsa_4 = to_mysql_date(get_var('data_comparsa_4'));
	
	$data_depos_1 = to_mysql_date(get_var('data_depos_1'));
	$data_depos_2 = to_mysql_date(get_var('data_depos_2'));
	$data_depos_3 = to_mysql_date(get_var('data_depos_3'));
	$data_depos_4 = to_mysql_date(get_var('data_depos_4'));
	
	$data_depos_conv_1 = to_mysql_date(get_var('data_depos_conv_1'));
	$data_depos_conv_2 = to_mysql_date(get_var('data_depos_conv_2'));
	$data_depos_conv_3 = to_mysql_date(get_var('data_depos_conv_3'));
	$data_depos_conv_4 = to_mysql_date(get_var('data_depos_conv_4'));
	$data_depos_conv_5 = to_mysql_date(get_var('data_depos_conv_5'));
	$data_depos_conv_6 = to_mysql_date(get_var('data_depos_conv_6'));
	$data_depos_conv_7 = to_mysql_date(get_var('data_depos_conv_7'));
	$data_depos_conv_8 = to_mysql_date(get_var('data_depos_conv_8'));
	
	
	switch($invia)
	{
		case "Insert":
	
		$field_ricorso = array();
		$value_ricorso = array();
		
		
		$comune_id = single_answer_query("SELECT MAX(Comune_ID) FROM ricorso_generale WHERE CC = '".$c."'");
		
		
		$field_ricorso[] = "CC";							$value_ricorso[] = $c;
		$field_ricorso[] = "Comune_ID";						$value_ricorso[] = $comune_id+1;
		$field_ricorso[] = "Partita_ID";					$value_ricorso[] = $partita_ID;
		$field_ricorso[] = "Ufficio_ID";					$value_ricorso[] = $id_ufficio;
		$field_ricorso[] = "Tipo_Ricorso";					$value_ricorso[] = $tipo;
		$field_ricorso[] = "Data_Registrazione";			$value_ricorso[] = date('Y-m-d');		
		$field_ricorso[] = "Grado";							$value_ricorso[] = 1;
		
		$field_ricorso[] = "Sospensiva";					$value_ricorso[] = $sospensiva;
		$field_ricorso[] = "Num_Sosp";						$value_ricorso[] = $num_sospensiva;
		$field_ricorso[] = "Data_Sosp";						$value_ricorso[] = $data_sospensiva;
		$field_ricorso[] = "Data_Dep_Sosp";					$value_ricorso[] = $data_dep_sospensiva;
		$field_ricorso[] = "Esito_Sosp";					$value_ricorso[] = $esito_sospensiva;
		$field_ricorso[] = "Data_Not_Esito_Sosp";			$value_ricorso[] = $data_not_sospensiva;
		
		$field_ricorso[] = "Merito";						$value_ricorso[] = $merito;
		$field_ricorso[] = "Num_Merito";					$value_ricorso[] = $num_merito;
		$field_ricorso[] = "Data_Merito";					$value_ricorso[] = $data_merito;
		$field_ricorso[] = "Data_Dep_Merito";				$value_ricorso[] = $data_dep_merito;
		$field_ricorso[] = "Esito_Merito";					$value_ricorso[] = $esito_merito;
		$field_ricorso[] = "Data_Not_Esito_Merito";			$value_ricorso[] = $data_not_merito;
		
		$field_ricorso[] = "Data_Richiesta_Sentenza";		$value_ricorso[] = $data_ric_sentenza;
		$field_ricorso[] = "Data_Impugnazione_Sentenza";	$value_ricorso[] = $data_app_sentenza;
		$field_ricorso[] = "Totale_Da_Pagare";				$value_ricorso[] = $tot_da_pagare;
		$field_ricorso[] = "RG_Pagato";						$value_ricorso[] = $RG_pagato;
		$field_ricorso[] = "Soccombenza_Pagata";			$value_ricorso[] = $socc_pagata;
		$field_ricorso[] = "Importo";						$value_ricorso[] = $importo;
		$field_ricorso[] = "Data_Pagamento";				$value_ricorso[] = $data_pag;
		$field_ricorso[] = "Descrizione_Pagamento";			$value_ricorso[] = $descr_pag;
		$field_ricorso[] = "Note";							$value_ricorso[] = $note;
		
		$id_ricorso = table_insert_record('ricorso_generale', $field_ricorso, $value_ricorso);

		if($tipo=="atto_citazione" && $id_ricorso != 0)
		{
			
			$field_atto = array();
			$value_atto = array();
			
			$field_atto[] = "Ricorso_ID";					$value_atto[] = $id_ricorso;
			$field_atto[] = "Giudice";						$value_atto[] = $giudice_atto;
			
			$field_atto[] = "Data_Iscrizione_Ruolo";		$value_atto[] = $data_ruolo;
			$field_atto[] = "RGN";							$value_atto[] = $RGN;
			$field_atto[] = "Data_Dep_Fascicolo";			$value_atto[] = $data_fasc;
			
			$field_atto[] = "Attore_1_ID";					$value_atto[] = $attore_ID_1;
			$field_atto[] = "Attore_2_ID";					$value_atto[] = $attore_ID_2;
			$field_atto[] = "Attore_3_ID";					$value_atto[] = $attore_ID_3;
			$field_atto[] = "Attore_4_ID";					$value_atto[] = $attore_ID_4;
			
			$field_atto[] = "Avvocato_A";					$value_atto[] = $avvo_attore;
			
			$field_atto[] = "Data_Mem_Int_A";				$value_atto[] = $data_depos_attore_1;
			$field_atto[] = "Data_Replica_Mem_Int_A";		$value_atto[] = $data_depos_attore_2;
			$field_atto[] = "Data_Mem_Istr_A";				$value_atto[] = $data_depos_attore_3;
			$field_atto[] = "Data_Replica_Mem_Istr_A";		$value_atto[] = $data_depos_attore_4;
			$field_atto[] = "Data_Comparsa_Concl_A";		$value_atto[] = $data_depos_attore_5;
			$field_atto[] = "Data_Note_Replica_Concl_A";	$value_atto[] = $data_depos_attore_6;
			$field_atto[] = "Data_Istanza_A";				$value_atto[] = $data_depos_attore_7;
			$field_atto[] = "Data_Memorie_A";				$value_atto[] = $data_depos_attore_8;
			
			$field_atto[] = "Convenuto_1_ID";				$value_atto[] = $convenuto_ID_1;
			$field_atto[] = "Convenuto_2_ID";				$value_atto[] = $convenuto_ID_2;
			$field_atto[] = "Convenuto_3_ID";				$value_atto[] = $convenuto_ID_3;
			$field_atto[] = "Convenuto_4_ID";				$value_atto[] = $convenuto_ID_4;
			
			$field_atto[] = "Avvocato_C";					$value_atto[] = $avvo_convenuto;
			
			$field_atto[] = "Data_Mem_Int_C";				$value_atto[] = $data_depos_conv_1;
			$field_atto[] = "Data_Replica_Mem_Int_C";		$value_atto[] = $data_depos_conv_2;
			$field_atto[] = "Data_Mem_Istr_C";				$value_atto[] = $data_depos_conv_3;
			$field_atto[] = "Data_Replica_Mem_Istr_C";		$value_atto[] = $data_depos_conv_4;
			$field_atto[] = "Data_Comparsa_Concl_C";		$value_atto[] = $data_depos_conv_5;
			$field_atto[] = "Data_Note_Replica_Concl_C";	$value_atto[] = $data_depos_conv_6;
			$field_atto[] = "Data_Istanza_C";				$value_atto[] = $data_depos_conv_7;
			$field_atto[] = "Data_Memorie_C";				$value_atto[] = $data_depos_conv_8;
			
			$field_atto[] = "Data_Sottoscriz_Atto_1";		$value_atto[] = $data_firma_1;
			$field_atto[] = "Data_Sottoscriz_Atto_2";		$value_atto[] = $data_firma_2;
			$field_atto[] = "Data_Sottoscriz_Atto_3";		$value_atto[] = $data_firma_3;
			$field_atto[] = "Data_Sottoscriz_Atto_4";		$value_atto[] = $data_firma_4;
			
			$field_atto[] = "Data_Notifica_Atto_1";			$value_atto[] = $data_notifica_1;
			$field_atto[] = "Data_Notifica_Atto_2";			$value_atto[] = $data_notifica_2;
			$field_atto[] = "Data_Notifica_Atto_3";			$value_atto[] = $data_notifica_3;
			$field_atto[] = "Data_Notifica_Atto_4";			$value_atto[] = $data_notifica_4;
			
			$field_atto[] = "Data_Sottoscriz_Comparsa_1";	$value_atto[] = $data_comparsa_1;
			$field_atto[] = "Data_Sottoscriz_Comparsa_2";	$value_atto[] = $data_comparsa_2;
			$field_atto[] = "Data_Sottoscriz_Comparsa_3";	$value_atto[] = $data_comparsa_3;
			$field_atto[] = "Data_Sottoscriz_Comparsa_4";	$value_atto[] = $data_comparsa_4;
				
			$field_atto[] = "Data_Dep_Comparsa_1";			$value_atto[] = $data_depos_1;
			$field_atto[] = "Data_Dep_Comparsa_2";			$value_atto[] = $data_depos_2;
			$field_atto[] = "Data_Dep_Comparsa_3";			$value_atto[] = $data_depos_3;
			$field_atto[] = "Data_Dep_Comparsa_4";			$value_atto[] = $data_depos_4;

			$id_atto = table_insert_record( 'atto_citazione' , $field_atto , $value_atto );
					
		}
		
		if($id_ricorso != 0 && $id_atto != 0 )
			echo 'OK '.$partita_ID.' '.$id_ricorso." ".$id_atto;
		else 
			echo 'ERR_NUOVO '.$partita_ID;		
		
		break;
		
	
		case "Update":
			
		$field_ricorso = array();
		$value_ricorso = array();
			
		$field_ricorso[] = "Ufficio_ID";					$value_ricorso[] = $id_ufficio;
		
		$field_ricorso[] = "Tipo_Ricorso";					$value_ricorso[] = $tipo;
		if( $esito_merito != "" )
		{
			$field_ricorso[] = "Data_Chiusura";					$value_ricorso[] = date('Y-m-d');
		}
		else 
		{
			$field_ricorso[] = "Data_Chiusura";					$value_ricorso[] = "";
		}
			
		$field_ricorso[] = "Sospensiva";					$value_ricorso[] = $sospensiva;
		$field_ricorso[] = "Num_Sosp";						$value_ricorso[] = $num_sospensiva;
		$field_ricorso[] = "Data_Sosp";						$value_ricorso[] = $data_sospensiva;
		$field_ricorso[] = "Data_Dep_Sosp";					$value_ricorso[] = $data_dep_sospensiva;
		$field_ricorso[] = "Esito_Sosp";					$value_ricorso[] = $esito_sospensiva;
		$field_ricorso[] = "Data_Not_Esito_Sosp";			$value_ricorso[] = $data_not_sospensiva;
			
		$field_ricorso[] = "Merito";						$value_ricorso[] = $merito;
		$field_ricorso[] = "Num_Merito";					$value_ricorso[] = $num_merito;
		$field_ricorso[] = "Data_Merito";					$value_ricorso[] = $data_merito;
		$field_ricorso[] = "Data_Dep_Merito";				$value_ricorso[] = $data_dep_merito;
		$field_ricorso[] = "Esito_Merito";					$value_ricorso[] = $esito_merito;
		$field_ricorso[] = "Data_Not_Esito_Merito";			$value_ricorso[] = $data_not_merito;
		
		$field_ricorso[] = "Data_Richiesta_Sentenza";		$value_ricorso[] = $data_ric_sentenza;
		$field_ricorso[] = "Data_Impugnazione_Sentenza";	$value_ricorso[] = $data_app_sentenza;
		$field_ricorso[] = "Totale_Da_Pagare";				$value_ricorso[] = $tot_da_pagare;
		$field_ricorso[] = "RG_Pagato";						$value_ricorso[] = $RG_pagato;
		$field_ricorso[] = "Soccombenza_Pagata";			$value_ricorso[] = $socc_pagata;
		$field_ricorso[] = "Importo";						$value_ricorso[] = $importo;
		$field_ricorso[] = "Data_Pagamento";				$value_ricorso[] = $data_pag;
		$field_ricorso[] = "Descrizione_Pagamento";			$value_ricorso[] = $descr_pag;
		$field_ricorso[] = "Note";							$value_ricorso[] = $note;
		
		$field_atto = array();
		$value_atto = array();
			
		$field_atto[] = "Giudice";						$value_atto[] = $giudice_atto;
			
		$field_atto[] = "Data_Iscrizione_Ruolo";		$value_atto[] = $data_ruolo;
		$field_atto[] = "RGN";							$value_atto[] = $RGN;
		$field_atto[] = "Data_Dep_Fascicolo";			$value_atto[] = $data_fasc;
			
		$field_atto[] = "Attore_1_ID";					$value_atto[] = $attore_ID_1;
		$field_atto[] = "Attore_2_ID";					$value_atto[] = $attore_ID_2;
		$field_atto[] = "Attore_3_ID";					$value_atto[] = $attore_ID_3;
		$field_atto[] = "Attore_4_ID";					$value_atto[] = $attore_ID_4;
			
		$field_atto[] = "Avvocato_A";					$value_atto[] = $avvo_attore;
			
		$field_atto[] = "Data_Mem_Int_A";				$value_atto[] = $data_depos_attore_1;
		$field_atto[] = "Data_Replica_Mem_Int_A";		$value_atto[] = $data_depos_attore_2;
		$field_atto[] = "Data_Mem_Istr_A";				$value_atto[] = $data_depos_attore_3;
		$field_atto[] = "Data_Replica_Mem_Istr_A";		$value_atto[] = $data_depos_attore_4;
		$field_atto[] = "Data_Comparsa_Concl_A";		$value_atto[] = $data_depos_attore_5;
		$field_atto[] = "Data_Note_Replica_Concl_A";	$value_atto[] = $data_depos_attore_6;
		$field_atto[] = "Data_Istanza_A";				$value_atto[] = $data_depos_attore_7;
		$field_atto[] = "Data_Memorie_A";				$value_atto[] = $data_depos_attore_8;
			
		$field_atto[] = "Convenuto_1_ID";				$value_atto[] = $convenuto_ID_1;
		$field_atto[] = "Convenuto_2_ID";				$value_atto[] = $convenuto_ID_2;
		$field_atto[] = "Convenuto_3_ID";				$value_atto[] = $convenuto_ID_3;
		$field_atto[] = "Convenuto_4_ID";				$value_atto[] = $convenuto_ID_4;
			
		$field_atto[] = "Avvocato_C";					$value_atto[] = $avvo_convenuto;
			
		$field_atto[] = "Data_Mem_Int_C";				$value_atto[] = $data_depos_conv_1;
		$field_atto[] = "Data_Replica_Mem_Int_C";		$value_atto[] = $data_depos_conv_2;
		$field_atto[] = "Data_Mem_Istr_C";				$value_atto[] = $data_depos_conv_3;
		$field_atto[] = "Data_Replica_Mem_Istr_C";		$value_atto[] = $data_depos_conv_4;
		$field_atto[] = "Data_Comparsa_Concl_C";		$value_atto[] = $data_depos_conv_5;
		$field_atto[] = "Data_Note_Replica_Concl_C";	$value_atto[] = $data_depos_conv_6;
		$field_atto[] = "Data_Istanza_C";				$value_atto[] = $data_depos_conv_7;
		$field_atto[] = "Data_Memorie_C";				$value_atto[] = $data_depos_conv_8;
			
		$field_atto[] = "Data_Sottoscriz_Atto_1";		$value_atto[] = $data_firma_1;
		$field_atto[] = "Data_Sottoscriz_Atto_2";		$value_atto[] = $data_firma_2;
		$field_atto[] = "Data_Sottoscriz_Atto_3";		$value_atto[] = $data_firma_3;
		$field_atto[] = "Data_Sottoscriz_Atto_4";		$value_atto[] = $data_firma_4;
			
		$field_atto[] = "Data_Notifica_Atto_1";			$value_atto[] = $data_notifica_1;
		$field_atto[] = "Data_Notifica_Atto_2";			$value_atto[] = $data_notifica_2;
		$field_atto[] = "Data_Notifica_Atto_3";			$value_atto[] = $data_notifica_3;
		$field_atto[] = "Data_Notifica_Atto_4";			$value_atto[] = $data_notifica_4;
			
		$field_atto[] = "Data_Sottoscriz_Comparsa_1";	$value_atto[] = $data_comparsa_1;
		$field_atto[] = "Data_Sottoscriz_Comparsa_2";	$value_atto[] = $data_comparsa_2;
		$field_atto[] = "Data_Sottoscriz_Comparsa_3";	$value_atto[] = $data_comparsa_3;
		$field_atto[] = "Data_Sottoscriz_Comparsa_4";	$value_atto[] = $data_comparsa_4;
		
		$field_atto[] = "Data_Dep_Comparsa_1";			$value_atto[] = $data_depos_1;
		$field_atto[] = "Data_Dep_Comparsa_2";			$value_atto[] = $data_depos_2;
		$field_atto[] = "Data_Dep_Comparsa_3";			$value_atto[] = $data_depos_3;
		$field_atto[] = "Data_Dep_Comparsa_4";			$value_atto[] = $data_depos_4;
		
		mysql_query("BEGIN");
		
		$query1 = table_update_record_query( "ricorso_generale" , $field_ricorso , $value_ricorso , "ID" , $id_ricorso );
		$query2 = table_update_record_query( "atto_citazione" , $field_atto , $value_atto , "Ricorso_ID" , $id_ricorso );
		
		$control_mysql1 = mysql_query($query1);
		$control_mysql2 = mysql_query($query2);
		
		if(($control_mysql1) && ($control_mysql2))
		{
				
			mysql_query("COMMIT");
			echo 'OK '.$partita_ID.' '.$id_ricorso;
		
		}
		else
		{
				
			mysql_query("ROLLBACK");
			echo "ERRORE nella query";
				
		}
			
		break;
		
		case "Delete":
			
			if($id_ricorso!=0)
			{
				mysql_query("BEGIN");
				
				$query1 = "DELETE FROM ricorso_generale WHERE ID = '".$id_ricorso."'";
				$control_mysql1 = mysql_query($query1);
				
				$query2 = "DELETE FROM atto_citazione WHERE Ricorso_ID = '".$id_ricorso."'";
				$control_mysql2 = mysql_query($query2);
				
				if(($control_mysql1) && ($control_mysql2))
				{
					
					mysql_query("COMMIT");
					echo "CANCELLATO";
				
				}
				else 
				{
					
					mysql_query("ROLLBACK");
					echo "ERRORE nella query";
					
				}
			}
			else 
			{
				echo "ERR_CANCELLA";
			}			
						
			break;
			
			case "Note":
			
				if($id_ricorso!=0)
				{
					$ctrl_submit = get_var('ctrl_submit');
			
					if($ctrl_submit == "Update")
					{
							
						mysql_query("BEGIN");
			
						$query1 = "UPDATE ricorso_generale SET Note = '".$note."' WHERE ID = '".$id_ricorso."'";
						$control_mysql1 = mysql_query($query1);
							
						if($control_mysql1)
						{
							mysql_query("COMMIT");
							echo "OK note salvate!";
						}
						else
						{
							mysql_query("ROLLBACK");
							echo "ERRORE nella query";
						}
					}
					else if($ctrl_submit == "Delete")
					{
							
						mysql_query("BEGIN");
							
						$query1 = "UPDATE ricorso_generale SET Note = '' WHERE ID = '".$id_ricorso."'";
						$control_mysql1 = mysql_query($query1);
			
						if($control_mysql1)
						{
							mysql_query("COMMIT");
							echo "OK note cancellate!";
						}
						else
						{
							mysql_query("ROLLBACK");
							echo "ERRORE nella query";
						}
					}
				}
				else
				{
					echo "ERR_CANCELLA";
				}
			
				break;
				
				case "Udienze":
				
					$id_udienza = get_var('id_udienza');
										
					$ctrl_submit = get_var('ctrl_submit');
					
					$data_udi = get_var('data_udi');
					$ora_udi = get_var('ora_udi');
					$grado_udi = get_var('grado');
					$tipo_udi = get_var('tipo');
					$trattazione_udi = get_var('trattazione');
					$esito_udi = get_var('esito');
					
					$field_udienza = array();
					$value_udienza = array();
						
					$field_udienza[] = "Ricorso_ID";		$value_udienza[] = $id_ricorso;
					$field_udienza[] = "Data_Udienza";		$value_udienza[] = to_mysql_date($data_udi);
					$field_udienza[] = "Ora_Udienza";		$value_udienza[] = $ora_udi;
					$field_udienza[] = "Grado";				$value_udienza[] = $grado_udi;
					$field_udienza[] = "Tipo";				$value_udienza[] = $tipo_udi;
					$field_udienza[] = "Trattazione";		$value_udienza[] = $trattazione_udi;
					$field_udienza[] = "Esito";				$value_udienza[] = $esito_udi;
					
					if( $id_udienza != 0 )
					{
						
						if($ctrl_submit == "Update")
						{
							mysql_query("BEGIN");
						
							$query1 = table_update_record_query( "iter_udienze" , $field_udienza , $value_udienza , "ID" , $id_udienza );
							$control_mysql1 = mysql_query($query1);
									
							if($control_mysql1)
							{
								mysql_query("COMMIT");
								echo "OK udienza salvata!";
							}
							else
							{
								mysql_query("ROLLBACK");
								echo "ERRORE nella query";
							}
							
						}
						else if($ctrl_submit == "Delete")
						{
								
							mysql_query("BEGIN");
								
							$query1 = "DELETE FROM iter_udienze WHERE ID = '".$id_udienza."'";
							$control_mysql1 = mysql_query($query1);
				
							if($control_mysql1)
							{
								mysql_query("COMMIT");
								echo "OK udienza cancellata!";
							}
							else
							{
								mysql_query("ROLLBACK");
								echo "ERRORE nella query delete iter_udienze";
							}
						}
					
					}
					else 
					{
						mysql_query("BEGIN");
						
						$query1 = table_insert_record_query( "iter_udienze" , $field_udienza , $value_udienza );
						$control_mysql1 = mysql_query($query1);
						
						if($control_mysql1)
						{
							mysql_query("COMMIT");
							echo "OK udienza inserita!";
						}
						else
						{
							mysql_query("ROLLBACK");
							echo "ERRORE nella query nuovo";
						}
					}
				
					break;
	
	}
?>