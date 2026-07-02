<?php
	
	if (!session_id()) session_start();

    include_once($_SESSION['_path']);
    include_once(ROOT."/_parameter.php");

    include(INC . "/header.php");
    include(INC . "/menu.php");
    include_once(CLS."/cls_elaborazioniUtils.php");
    include_once(CLS."/cls_DateTimeInLine.php");
    include_once(CLS."/cls_Utils.php");
    include_once(CLS."/cls_html.php");
	include_once CLS . "/cls_storico.php";													

	$storico = new storico('storicoElaborazioni','5');	

    $PathPagamentiDaBonificare = "/coattiva/archivio/Importazioni_Pagamenti/DaBonificare/";
    $PathPagamentiDaBonificareTemp = "/coattiva/archivio/Importazioni_Pagamenti/Temp/";
    $PathCompletoPagamentiDaBonificare = $_SERVER['DOCUMENT_ROOT'] . $PathPagamentiDaBonificare;

	if ($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	function alert($message)
    {
        echo "<script>alert('".$message."');</script>";
    }


    $cls_elab = new cls_elaborazioniUtils();
    $cls_date = new cls_DateTimeI("DB",false);
    $cls_utils = new cls_Utils();
    $cls_html = new cls_html();
	
	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$provenienza = $cls_help->getVar('provenienza');
	$bonifica = $cls_help->getVar('bonifica');
	$telematico = $cls_help->getVar('telematico');
	$sceltacrono = $cls_help->getVar('sceltacrono');
	//$pagamentopresente_riga1 = get_var('pagamentopresente_riga1');
	//$pagamentopresente_riga2 = get_var('pagamentopresente_riga2');
	$sceglicomune = $cls_help->getVar('sceglicomune');
	$numeroattocoattivo = $cls_help->getVar('numeroattocoattivo');
	$annoattocoattivo = $cls_help->getVar('annoattocoattivo');
	
	$attorif = $cls_help->getVar('atto_rif');
    $TypeId = $cls_help->getVar('TypeId');

	$idatto = $cls_help->getVar('idatto');
	$partita = $cls_help->getVar('partita');
	$utente = $cls_help->getVar('utente');
	$pagante = $cls_help->getVar('pagante');
	$terzi = $cls_help->getVar('terzi');
	$data_pag = $cls_date->GetDateDB($cls_help->getVar('data_pag'),"IT");// to_mysql_date($cls_help->getVar('data_pag'));
	$tipo = $cls_help->getVar('tipo');
	//alert($tipo);
	$importo = $cls_help->getVar('importo');
	$quietanza = $cls_help->getVar('quietanza');
	$bollettario = $cls_help->getVar('bollettario');
	$num_rata = $cls_help->getVar('num_rata');
	$note = $cls_help->getVar('note');
	$img_bollettino = $cls_help->getVar('img_bollettino');
	$img_caricato_bollettino = $cls_help->getVar('img_caricato_bollettino');
	
	$autorizzazione = $_SESSION["aut_tipo"];// $cls_help->getVar('aut_tipo');

	//echo "<h1>tipo --> ".$autorizzazione."</h1>";
	
	$scrittaEsito = "";
	$azzeraCampi = false;
    $flagImg = false;

    //die;


	$invia_submit = $cls_help->getVar('invia_submit');
	if ($invia_submit == "associa")
	{
		if(strpos($attorif, "PIGNORAMENTO")!==false)
		{
            $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$idatto." AND CC = '".$sceglicomune."'";
			$myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");// new pignoramento($idatto, $sceglicomune);
			$tipo_atto = $myAtto->tipo_pignoramento($myAtto->Tipo,$myAtto->Tipo_Terzi);
		}
		else 
		{
            $query = "SELECT * FROM atto WHERE ID = '".$idatto."' AND CC = '".$sceglicomune."'";
            $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");
			//$myAtto = new atto($idatto, $sceglicomune);
			$tipo_atto = $myAtto->Atto;
		}

        $query = "SELECT * FROM pagamento WHERE ID = 'NULL' AND CC = '".$myAtto->CC."'";
		$myPagamentoBonificato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamento");//new pagamento(null, $myAtto->CC);

        //print_r($myPagamentoBonificato);
        //if($myAtto->Partita_ID==null) echo "null";

        //echo "<h1>1</h1>";
		$myPagamentoBonificato->Comune_ID = $cls_elab->ProssimoComuneId($myAtto->CC);
		$myPagamentoBonificato->CC = $myAtto->CC;
		$myPagamentoBonificato->Partita_ID = $myAtto->Partita_ID;
		$myPagamentoBonificato->Atto_ID = $myAtto->ID;
		$myPagamentoBonificato->Riferimento_Atto = 1;  // non usato
		$myPagamentoBonificato->Tipo_Atto = $tipo_atto;
		$myPagamentoBonificato->Pagante = $pagante;
		$myPagamentoBonificato->Conto_Terzi = $terzi;  //  parametro comune
		$myPagamentoBonificato->Data_Pagamento = $data_pag;
		$myPagamentoBonificato->Data_Registrazione = date("Y-m-d");
		$myPagamentoBonificato->Modalita = $tipo;  //  ??  bolletta o C/C
		$myPagamentoBonificato->Importo = str_replace(",", ".", $importo);
		
		if($myAtto->Rate_Previste==0 || $myAtto->Rate_Previste==null)
			$myPagamentoBonificato->Dovuto = $myAtto->Totale_Dovuto;
		else
			$myPagamentoBonificato->Dovuto = $myAtto->Importi_Rate[$num_rata-1];

		$myPagamentoBonificato->Quietanza = $quietanza;
		$myPagamentoBonificato->Bollettario = $bollettario;
		$myPagamentoBonificato->Rata = $num_rata=="XXX"?null:$num_rata;
		$myPagamentoBonificato->Totale_Rate = $myAtto->Rate_Previste;  //  non usato
		$myPagamentoBonificato->Note = $note;
		$myPagamentoBonificato->Bollettino = $img_bollettino;
		$myPagamentoBonificato->Telematico = $telematico;
		$myPagamentoBonificato->Data_Travaso_A_Gitco = null;
		$myPagamentoBonificato->Tipo_Pagamento = "BONIFICATO";
        $myPagamentoBonificato->DocumentTypeId = $TypeId;
        $myPagamentoBonificato->DocumentTableTypeId = $cls_help->getVar("TableTypeId");
        $myPagamentoBonificato->Pagante = $pagante;

        $myPagamentoBonificato->ID = $cls_elab->PagamentoGiaPresente($myPagamentoBonificato);


        $myPagamentoBonificato= (array) $myPagamentoBonificato;
        //$pagVal->Comune_ID = $this->ProssimoComuneId($pagVal->CC);
        if ($myPagamentoBonificato["ID"] == NULL)
        {
            $cls_db->DbSave($cls_utils->GetObjectQuery($myPagamentoBonificato,"pagamento"));
        }
        else
        {
            $cls_db->DbSave($cls_utils->GetObjectQuery($myPagamentoBonificato,"pagamento", array("ID" => $myPagamentoBonificato["ID"])));
        }

        $query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $bonifica . "'";
		$myPagImportato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");// new pagamenti_importati($bonifica);
		
		$myPagImportato->Tipo_Pagamento = $cls_elab->TipiPagamento($tipo_atto, "TIPODASCRITTA");
		$myPagImportato->Riferimento_Atto = $myAtto->ID;
		$myPagImportato->Comune_Riferimento = $myAtto->CC;
		$myPagImportato->Esito = "BONIFICATO";
        $myPagImportato->Data_Importazione = date('Y-m-d');
        $myPagImportato->Operatore = $_SESSION['username'];

        $queryCerca = "SELECT ID FROM pagamenti_importati WHERE Quinto_Campo = '" . $myPagImportato->Quinto_Campo . "' AND Importo_Pagato = '" . $myPagImportato->Importo_Pagato . "' ";
        $ID = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati")["ID"];

        $myPagImportato = (array) $myPagImportato;
        if($ID != null)
        {
            $cls_db->DbSave($cls_utils->GetObjectQuery($myPagImportato,"pagamenti_importati ", array("ID" => $myPagImportato["ID"])));
        }
        else
        {
            $cls_db->DbSave($cls_utils->GetObjectQuery($myPagImportato,"pagamenti_importati "));
        }

		//$cls_elab->InsertUpdatePagamImportato($myPagImportato,"UPDATE");

		if ($img_bollettino != "")
		{
			$cartellaDestinazione = $cls_utils->crea_dir(SUPER_ROOT . "/archivio/Atti/" . $myAtto->CC . "/Pagamenti/");
			$fotoOrigine = $PathCompletoPagamentiDaBonificare . $img_bollettino;
			$fotoDestinazione = $cartellaDestinazione . $img_bollettino;

			if(file_exists($fotoOrigine))
			    rename ($fotoOrigine, $fotoDestinazione);
		}
		
		$scrittaEsito = "<script>alert('Importazione avvenuta con successo');</script>";
		
		$azzeraCampi = true;

		$storico->insRow('I', "Bonificato pagamento ".$numeroattocoattivo."/".$annoattocoattivo." per ente ".$sceglicomune."[".$c."]");
	}
	else if ($invia_submit == "aggiungitelematico")
	{
		if (isset($_FILES['img_caricato_bollettino']))
		{
			$cartellaDestinazione = $cls_utils->crea_dir($_SERVER['DOCUMENT_ROOT'] . "/archivio/Importazioni_Pagamenti/DaBonificare/");
			
			if ($img_bollettino != "" && file_exists($cartellaDestinazione . $img_bollettino))
			{
				unlink ($cartellaDestinazione . $img_bollettino);
			}
			
			$percorso_temp = $_FILES['img_caricato_bollettino']['tmp_name'];
			$img_bollettino = $_FILES['img_caricato_bollettino']['name'];
			
			if(strpos($attorif, "PIGNORAMENTO")!==false)
			{
                $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$idatto." AND CC = '".$sceglicomune."'";
				$myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");// new pignoramento($idatto, $sceglicomune);
				$tipo_atto = $cls_elab->tipo_pignoramento($myAtto->Tipo,$myAtto->Tipo_Terzi);
			}
			else
			{
                $query = "SELECT * FROM atto WHERE ID = '".$idatto."' AND CC = '".$sceglicomune."'";
				$myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($idatto, $sceglicomune);
				$tipo_atto = $myAtto->Atto;
			}
			
			$fotoDestinazione = strtoupper($cartellaDestinazione . $img_bollettino);
			
			//alertAllGlobalVariables();
			
			if ($img_bollettino != "" && move_uploaded_file($percorso_temp, $fotoDestinazione))
			{
				if (file_exists($fotoDestinazione))
				{
					$nuovoNomeFileDaBonificare = $fotoDestinazione;
					$esplodoEstens = explode (".", $img_bollettino);
					$estensioneFile = strtoupper($esplodoEstens[count($esplodoEstens)-1]);
					
					if ($estensioneFile == "JPG")
					{
						// è già in formato jpg nel posto giusto
					}
					else if ($estensioneFile == "TIF")
					{
						$radiceImg = substr($img_bollettino, 0, -(strlen($estensioneFile)));
						$nomeBreveJpg = strtoupper($radiceImg . "JPG");
						$nuovoNomeFileDaBonificare = $cartellaDestinazione . $nomeBreveJpg;
						
						// al passo successivo il JPG verrà portato in "/archivio/Atti/" . $myAtto->CC . "/Pagamenti/"
						$cartellaFutura = $cls_utils->crea_dir($_SERVER['DOCUMENT_ROOT'] . "/archivio/Atti/" . $myAtto->CC . "/Pagamenti/");
						$fotoFuturaDestinazione = $cartellaFutura . $nomeBreveJpg;
						
						if (file_exists($nuovoNomeFileDaBonificare))  //  il nome JPG c'� gi� tra quelli da bonificare
						{
							echo <<< FILEESISTENTE
							
								alert ("Attenzione il file $nuovoNomeFileDaBonificare � gi� presente nella cartella $cartellaDestinazione: dare un nome diverso all'immagine TIF di origine");
								history.back();
							
FILEESISTENTE;
							return;
						}
						else if (file_exists($fotoFuturaDestinazione))  //  il nome JPG c'� gi� tra i pagamenti del comune!!
						{
							echo <<< FILEFUTUROESISTENTE
							
								alert ("Attenzione il file $fotoFuturaDestinazione � gi� presente nella cartella $cartellaFutura: dare un nome diverso all'immagine TIF di origine");
								history.back();
							
FILEFUTUROESISTENTE;
							return;
						}
						else  // converto il TIF in JPG
						{
							$im = new imagick( $fotoDestinazione );
								
							$im->setImageCompression(Imagick::COMPRESSION_JPEG);
							$im->setImageCompressionQuality(5);
							$im->writeImage( $nuovoNomeFileDaBonificare );
								
							unlink ($fotoDestinazione);
						}
					}
					else  //  di qua non pu� passara perch� il JAVASCRIPT lo blocca prima
					{
						echo <<< ESTENSIONEERRATA
							
							alert ("Estensione errata: " . $estensioneFile);
							history.back();
							
ESTENSIONEERRATA;
						return;
					}
				}

                $query = "SELECT * FROM pagamento WHERE ID = 'NULL' AND CC = '".$myAtto->CC."'";
				$myPagamentoBonificato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamento");// new pagamento(null, $myAtto->CC);
				
				$myPagamentoBonificato->Comune_ID = $cls_elab->ProssimoComuneId($myAtto->CC);
				$myPagamentoBonificato->CC = $myAtto->CC;
				$myPagamentoBonificato->Partita_ID = $myAtto->Partita_ID;
				$myPagamentoBonificato->Atto_ID = $myAtto->ID;
				$myPagamentoBonificato->Riferimento_Atto = 1;  // non usato
				$myPagamentoBonificato->Tipo_Atto = $tipo_atto;
				$myPagamentoBonificato->Pagante = $pagante;
				$myPagamentoBonificato->Conto_Terzi = $terzi;  //  parametro comune
				$myPagamentoBonificato->Data_Pagamento = $data_pag;
				$myPagamentoBonificato->Data_Registrazione = date("Y-m-d");
				$myPagamentoBonificato->Modalita = $tipo;  //  ??  bolletta o C/C
				$myPagamentoBonificato->Importo = str_replace(",", ".", $importo);
				
				if($myAtto->Rate_Previste==0 || $myAtto->Rate_Previste==null)
					$myPagamentoBonificato->Dovuto = $myAtto->Totale_Dovuto;
				else
					$myPagamentoBonificato->Dovuto = $myAtto->Importi_Rate[$num_rata-1];
				
				$myPagamentoBonificato->Quietanza = $quietanza;
				$myPagamentoBonificato->Bollettario = $bollettario;
				$myPagamentoBonificato->Rata = $num_rata;
				$myPagamentoBonificato->Totale_Rate = $myAtto->Rate_Previste;  //  non usato
				$myPagamentoBonificato->Note = $note;
				$myPagamentoBonificato->Bollettino = $img_bollettino;
				$myPagamentoBonificato->Telematico = $telematico;
				$myPagamentoBonificato->Data_Travaso_A_Gitco = null;
				$myPagamentoBonificato->Tipo_Pagamento = "BONIFICATO";
                $myPagamentoBonificato->DocumentTypeId = $TypeId;
                $myPagamentoBonificato->DocumentTableTypeId = $cls_help->getVar("TableTypeId");
                $myPagamentoBonificato->Pagante = $pagante;
					
				//$rispInsUpd = $myPagamentoBonificato->InsertUpdatePagamento();
                //$cls_elab->InsertUpdatePagamento($myPagamentoBonificato);
                $myPagamentoBonificato->ID = $cls_elab->PagamentoGiaPresente($myPagamentoBonificato);


                $myPagamentoBonificato= (array) $myPagamentoBonificato;
                //$pagVal->Comune_ID = $this->ProssimoComuneId($pagVal->CC);
                if ($myPagamentoBonificato["ID"] == NULL)
                {
                    $cls_db->DbSave($cls_utils->GetObjectQuery($myPagamentoBonificato,"pagamento"));
                }
                else
                {
                    $cls_db->DbSave($cls_utils->GetObjectQuery($myPagamentoBonificato,"pagamento", array("ID" => $myPagamentoBonificato["ID"])));
                }

/****************************************************
                $myPagamentoBonificato= (array) $myPagamentoBonificato;
                //$pagVal->Comune_ID = $this->ProssimoComuneId($pagVal->CC);
                if ($myPagamentoBonificato["ID"] == NULL)
                {
                    $cls_db->DbSave($cls_utils->GetObjectQuery($myPagamentoBonificato,"pagamento"));
                }
                else
                {
                    $cls_db->DbSave($cls_utils->GetObjectQuery($myPagamentoBonificato,"pagamento", array("ID" => $myPagamentoBonificato["ID"])));
                }
***********************************************************/
                $query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $bonifica . "'";
				$myPagImportato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");// new pagamenti_importati($bonifica);
				
				//$myPagImportato->Tipo_Pagamento = $myPagImportato->TipiPagamento($myAtto->Atto, "TIPODASCRITTA");
				//$myPagImportato->Riferimento_Atto = $myAtto->ID;
				//$myPagImportato->Comune_Riferimento = $myAtto->CC;
				$myPagImportato->Immagine_Fronte = $img_bollettino;
				//$myPagImportato->Esito = "BONIFICATO";

                $myPagImportato = (array) $myPagImportato;

                $cls_db->DbSave($cls_utils->GetObjectQuery($myPagImportato,"pagamenti_importati ", array("ID" => $myPagImportato["ID"])));
				
				//$scrittaEsito = "<script>alert('Aggiornamento telematico avvenuta con successo');</script>";
				$scrittaEsito = "";

				$storico->insRow('U', "Modificato pagamento ".$numeroattocoattivo."/".$annoattocoattivo." per ente ".$sceglicomune."[".$c."]. Inserito telematico");
			}
			else if ($img_bollettino == "")  //  immagine vuota
			{
                $query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $bonifica . "'";
				$myPagImportato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");//  new pagamenti_importati($bonifica);
				$myPagImportato->Immagine_Fronte = "";

                $myPagImportato = (array) $myPagImportato;

                $cls_db->DbSave($cls_utils->GetObjectQuery($myPagImportato,"pagamenti_importati ", array("ID" => $myPagImportato["ID"])));
				//$cls_elab->InsertUpdatePagamImportato($myPagImportato,"UPDATE");
			}
			else
			{
				$scrittaEsito = "<script>alert('Errore upload $fotoDestinazione importazione telematico');</script>";
				
				//  devo far cos� perche se c'è un errore di importazione, mi genera errori a raffica in php
                $query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $bonifica . "'";
				$myPagImportato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");//new pagamenti_importati($bonifica);
				$myPagImportato->Immagine_Fronte = "";
                //$cls_elab->InsertUpdatePagamImportato($myPagImportato,"UPDATE");

                $myPagImportato = (array) $myPagImportato;

                $cls_db->DbSave($cls_utils->GetObjectQuery($myPagImportato,"pagamenti_importati ", array("ID" => $myPagImportato["ID"])));
			}
		}
		else $scrittaEsito = "<script>alert('Errore importazione telematico');</script>";
	}
	else if ($invia_submit == "elimina")
	{
        $query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $bonifica . "'";
		$myPagImportato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");//new pagamenti_importati($bonifica);
		
		$myPagImportato->Esito = "ELIMINATO";
		
		//$res = $cls_elab->InsertUpdatePagamImportato($myPagImportato,"UPDATE");
        $myPagImportato = (array) $myPagImportato;

        $cls_db->DbSave($cls_utils->GetObjectQuery($myPagImportato,"pagamenti_importati ", array("ID" => $myPagImportato["ID"])));
		
		//echo "<br>" . $res . "<br>";
		
		$fotoDaEliminare = $PathCompletoPagamentiDaBonificare . $img_bollettino;
		
		$scrittaEsito = "<script>alert('Eliminazione avvenuta con successo');</script>";
		unlink ($fotoDaEliminare);
		
		$azzeraCampi = true;

		$storico->insRow('D', "Eliminato pagamento ".$numeroattocoattivo."/".$annoattocoattivo." per ente ".$sceglicomune."[".$c."]");
	}
	//$partita_ID = get_var('partita');
	
	$pagamentopresente_riga1 = "";
	$pagamentopresente_riga2 = "";
	
	if ($azzeraCampi == true)  //  se ho bonificato o eliminato azzero i campi che arrivano in get/post
	{
		$sceltacrono = "";
		$pagamentopresente_riga1 = "";
		$pagamentopresente_riga2 = "";
		//$sceglicomune = "";  //  metto il precedente (se sono fortunato � quello giusto)
		$numeroattocoattivo = "";
		$annoattocoattivo = "";
		
		$idatto = "";
		$partita = "";
		$utente = "";
		$pagante = "";
	}
	
	//$layout = "<script>";

	//$anni_gestiti = new anni_gestiti($c, null);
	
	if($c==null)
		$options_anni = null;
	else
	{
		$options_anni = $cls_elab->Options_Anni_Veloci($c, "COATTIVA", "pagamento_da_bonificare");
	
		/*if($a!=null)
			$layout.="$('#select_anno_veloce option[value=".$a."]').attr('selected',true);";*/
	}
	
	//$layout.= "</script>";
		
	//$layout.= "<script>$('[tabindex=1]').focus();</script>";
	
	/*$partita = new partita($partita_ID, $c, $a);

	$ID_Partita = $partita->Comune_ID;
	
	$anno_riferimento = $partita->Anno_Riferimento;
		
	$utente_ID = $partita->Utente_ID;
	$utente = new utente($utente_ID,$c);
	
	$id_utente 				= 	$utente->ID;
	$genere_utente 			= 	$utente->Genere;
	$comune_id 				=	$utente->Comune_ID;
	$cognome_utente 		=	$utente->Cognome;
	$nome_utente 			=	$utente->Nome;
	$ditta					=	$utente->Ditta;*/
	
	//$percorso_dir = ATTI ."/". $c . "/Pagamenti/";
	//$src_dir = "/archivio/Atti/".$c."/Pagamenti/";
	
	/*$quietanza = "";
	$bollettario = "";
	$numero_rata = "";
	$tot_rate = "";
	$note = "";
	$importo = "";
	$pagante = "";
	$data_pag = "";
	$modalita = "";
	$immagine = "";
	$src_immagine = "";
	$w_img = "";
	$h_img = "";
	$layout .= "<script>$('#mostra_immagine').hide();</script>";*/
	//alert($sceltacrono);
	$selOrd = $selCoa = "";
	if ($sceltacrono == "O")
	{
		$selOrd = " selected ";
	}
	else if ($sceltacrono == "C")
	{
		$selCoa = " selected ";
	}
	
	$id_pag = 0;
	
	//$myPagDaBonificare = new pagamenti_importati(NULL);
	
	if ($telematico == "SI")
	{
		$specTelem = "TELEMATICO";
		$sceltaLista = "Y";
	}
	else if ($telematico == "NO")
	{
		$specTelem = "NON TELEMATICO";
		$sceltaLista = "N";
	}
	else
	{
		$specTelem = "???";
		$sceltaLista = "N";
	}
	
	$listaDaBonificare = $cls_elab->ListaPagamentiDaBonificare($sceltaLista);

	if (count($listaDaBonificare) != 0)
	{
		$visualizzo = $listaDaBonificare[0];
		$testoJs = "";
		$attualeDaBonificare = 1;
		for ($i = 0; $i < count($listaDaBonificare); $i++)
		{
			if ($bonifica == $listaDaBonificare[$i])
			{
				$visualizzo = $listaDaBonificare[$i];
				$attualeDaBonificare = $i + 1;
				//break;
			}
			$testoJs .= "arrayBon[i++] = " . $listaDaBonificare[$i] . ";\n";
		}
		$totaleDaBonificare = count($listaDaBonificare);
	}
	else 
	{
		$visualizzo = null;
		$totaleDaBonificare = 0;
	}

    $query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $visualizzo . "'";
	$myPagDaBonificare = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");// new pagamenti_importati($visualizzo);

    $a_act = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM document_type WHERE CollectionTypeId = 1"));
    $a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>null,"text"=>array("[Description]"));
    $tipo_atto_rif = $cls_html->getOptions($a_act,$a_selection);

    //echo $tipo_atto_rif;

    /************************************       TableTypeId          ******************/




	//$array_pagamenti = array();
	//$atto = $partita->Atto;

	//$tipo_atto_rif = $cls_elab->TipiPagamento($myPagDaBonificare->Tipo_Pagamento, "SELECT", $attorif);

	/*$somma_pagamenti = array();
	if( $atto != null)
	{
		$num_rate = $atto[count($atto)-1]->Rate_Previste;
		
		$control_selected = 0;
		for($i=count($atto)-1;$i>=0;$i--)
		{
			$somma_pagamenti[] = $atto[count($atto)-1-$i]->totale_pagamenti();
			$selected = "";
						
			if($atto[$i]->Rate_Previste != 0)
				$ctrl_rata[$atto[$i]->ID] = "si";
			else
				$ctrl_rata[$atto[$i]->ID] = "no";
			
			$pagamento_ogg = $atto[$i]->Pagamento;
			for($y=count($pagamento_ogg)-1;$y>=0;$y--)
			{
				$pagamento_ogg[$y]->crono_atto();
				$array_pagamenti[] = $pagamento_ogg[$y];
				
				if($y == count($pagamento_ogg)-1 && $control_selected == 0)
				{
					$selected = "selected ";
					$control_selected = 1;
				}
			}
			
			$mostra_atto = $atto[$i]->Atto." n. ".$atto[$i]->ID_Cronologico." del ".$atto[$i]->Anno_Cronologico;
			
			if($atto[$i]->ID_Cronologico!=0)
				$atto_rif .= "<option ".$selected." value='".$atto[$i]->ID."'>".$mostra_atto."</option>";
		}
		
		$num_pagamenti = count($array_pagamenti);
		if( $num_pagamenti >0)
			$pag = $array_pagamenti[0];		
		
		$pagamento = $array_pagamenti;
		if(isset($pag))
		{
			if($num_rate!=0)
				$layout .= "<script>$('#num_rata').prop('disabled',false);</script>";
					
			$pagante = $pag->Pagante;
			$data_pag = from_mysql_date($pag->Data_Pagamento);
			$modalita = $pag->Modalita;
			switch($modalita)
			{
				case "Bancomat":
		
					$layout .= "<script>$('#tipo_1').attr('selected','selected');</script>";
		
					break;
						
				case "Bolletta":
		
					$layout .= "<script>$('#tipo_2').attr('selected','selected');</script>";
						
					break;
		
				case "C/C":
		
					$layout .= "<script>$('#tipo_3').attr('selected','selected');</script>";
		
					break;
		
				case "Contanti":
		
					$layout .= "<script>$('#tipo_4').attr('selected','selected');</script>";
		
					break;
		
				case "Assegno":
		
					$layout .= "<script>$('#tipo_5').attr('selected','selected');</script>";
		
					break;
		
				case "POS":
		
					$layout .= "<script>$('#tipo_6').attr('selected','selected');</script>";
		
					break;
		
				case "Vaglia":
		
					$layout .= "<script>$('#tipo_7').attr('selected','selected');</script>";
		
					break;
		
				case "BPL":
		
					$layout .= "<script>$('#tipo_8').attr('selected','selected');</script>";
		
					break;
		
				case "BGSG":
		
					$layout .= "<script>$('#tipo_9').attr('selected','selected');</script>";
		
					break;
			}
			$importo = conv_num( number_format($pag->Importo,2));
			if($pag->Conto_Terzi == "Y")
			{
				$layout .= "<script>$('#terzi').prop('checked',true);</script>";
			}
		
			$quietanza = $pag->Quietanza;
			$bollettario = $pag->Bollettario;
			$numero_rata = $pag->Rata;
			$tot_rate = $pag->Totale_Rate;
			$note = $pag->Note;
			
			if($pag->Bollettino!="")
			{
				$src_immagine = $src_dir.$pag->Bollettino;
				$percorso_immagine = $percorso_dir.$pag->Bollettino;

				$immagine = new Imagick($percorso_immagine);
				$d = $immagine->getImageGeometry();
				$w_img = $d['width'];
				$h_img = $d['height'];
				
				$layout .= "<script>$('#mostra_immagine').show();</script>";
			}

			
			$id_pag = $pag->ID;
		
			$layout .= "<script>$('#invia_submit').val('Update');</script>";
		}
		else
		{
			$layout .= "<script>$('#invia_submit').val('Insert');</script>";
		}
		
	}
	else
	{
		$atto_rif = "<option value='0'>Nessun atto presente in lista</option>";
		$pagamento = null;
		$ctrl_rata[0] = null;
	}*/
	
	//$nuovo_pag = get_var('nuovo_pag');
	//if($nuovo_pag == true)

	$w_img = "0";
	$h_img = "0";
	$rapporto = 1;
	$larghezza = 1;
	$altezza = $larghezza / $rapporto;
	$specifica = "";
	//echo "<h1>totale --> ".$totaleDaBonificare."</h1>";
	if ($totaleDaBonificare != 0)
	{
        $cls_date->changeFormat("IT",false);
		$quietanza = substr($myPagDaBonificare->Quinto_Campo, -4, 4);
		$quintocampo = $myPagDaBonificare->Quinto_Campo;
		$bollettario = "";
		$numero_rata = "0";
		$tot_rate = "";
		$note = "";
		$importo = number_format($myPagDaBonificare->Importo_Pagato, 2, ",", "");
		$pagante = "";
		$data_pag = $cls_date->Get_DateNewFormat($myPagDaBonificare->Data_Pagamento,"DB"); //from_mysql_date($myPagDaBonificare->Data_Pagamento);
		$modalita = "";
		$immagine = $myPagDaBonificare->Immagine_Fronte;
		$src_completo_immagine = $PathCompletoPagamentiDaBonificare . $myPagDaBonificare->Immagine_Fronte;
		$src_immagine = $PathPagamentiDaBonificare . $myPagDaBonificare->Immagine_Fronte;
		//$src_immagick = $PathImportazioniPagamenti . "Temp/" . $myPagDaBonificare->Immagine_Fronte;
		$id_pag = $myPagDaBonificare->ID;
		
		//$layout .= "<script>$('#mostra_immagine').hide();nuovo_record();</script>";

        //$cls_help->alert($_SERVER['DOCUMENT_ROOT'].$PathPagamentiDaBonificareTemp.$immagine);
        //$cls_help->alert($src_immagine);
        /*if($immagine != "" && file_exists($_SERVER['DOCUMENT_ROOT'].$PathPagamentiDaBonificareTemp.$immagine)===true ){
            //$PathPagamentiDaBonificare = "/archivio/Importazioni_Pagamenti/DaBonificare/";
            //$PathCompletoPagamentiDaBonificare = $_SERVER['DOCUMENT_ROOT'] . $PathPagamentiDaBonificare;
            $flagImg = true;
            $pathWebImgBollettino = SUPER_WEB_ROOT . $PathPagamentiDaBonificareTemp . $immagine;
            //$cls_help->alert($pathWebImgBollettino);
        }*/
        $pathCompletoMyImage = $_SERVER['DOCUMENT_ROOT'].$PathPagamentiDaBonificare.$immagine;
        $fileNameWithoutExtention = pathinfo($pathCompletoMyImage,PATHINFO_FILENAME);
        $pathCompleteMyImageWithExt = $_SERVER['DOCUMENT_ROOT'].$PathPagamentiDaBonificare.$fileNameWithoutExtention.".tif";
		$PathViewImageFile = null;
        if($immagine != "" && file_exists($pathCompletoMyImage)===true ) {
            //$cls_help->alert($pathCompleteMyImageWithExt);
            $im = new imagick($pathCompletoMyImage);

            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            $im->setImageCompressionQuality(5);
            $im->writeImage($PathCompletoPagamentiDaBonificare.$fileNameWithoutExtention.".JPG");

            $PathViewImageFile = $PathPagamentiDaBonificare.$fileNameWithoutExtention.".JPG";
        }
		
		if ($immagine != "" && file_exists($src_completo_immagine)===true )
		{
            //$flagImg = true;
            //$pathWebImgBollettino = SUPER_WEB_ROOT . $PathPagamentiDaBonificare . $myPagDaBonificare->Immagine_Fronte;
			
			$d = getimagesize($src_completo_immagine);
			$w_img = $d[0];
			$h_img = $d[1];
			
			$rapporto = $w_img / $h_img;
			$larghezza = 410;
			$altezza = $larghezza / $rapporto;
						
			if ($altezza > 250)
			{
				$altezza = 250;
				$larghezza = $altezza * $rapporto;
			}
			
			$disabledSvuota = "";
		}
		else 
			$disabledSvuota = " disabled ";
		
		$tipoContoCorrente = "";
		if ($provenienza == "TARGHEESTERE")
		{
			$tipoContoCorrente = "CDSESTERO";
			$specifica = "ESTERO";
		}
		else if ($provenienza == "COATTIVA")
		{
			$tipoContoCorrente = "CDS";
			$specifica = "COATTIVO";
		}
		else 
		{
			$cls_help->alert ("Errore nella variabile provenienza");
			return;
		}
		
		$checkedTerzi = "";
	}
	else
	{
		$scrittaEsito = "<script>\n";
		$scrittaEsito .= "alert('Non ci sono pagamenti da bonificare');\n";
		$scrittaEsito .= "location.href = 'importazione_pagamenti.php?c=$c&a=$a&provenienza=$provenienza&telematico=$telematico'";
		$scrittaEsito .= "</script>\n";
		
		$specTelem = "";
		$immagine = "";
	}
	
	//$prev = $partita->prev;
	//$next = $partita->next;
	
	//Anno <?php ElencoAnni("TARGHEESTERE", $c, $a);</font></td>


function ElencoPagComuni ($comuneselected, $autorizzazione)
{
    $cls_db = new cls_db();
    if ($autorizzazione == 1)
        $query = "select CC, Denominazione from enti_gestiti order by Denominazione ASC";
    else //  utente solo di un comune
        $query = "select CC, Denominazione from enti_gestiti where CC = '$comuneselected'";

    $result = $cls_db->getResults($cls_db->ExecuteQuery($query));// mysql_query($query);
    $stringaSelect = "";

    for ($i = 0; $i < count($result); $i++)
    {
        if ($result[$i]['CC'] == $comuneselected) $selectedcom = " selected ";
        else $selectedcom = "";

        $stringaSelect .= "<option value='" . $result[$i]['CC'] . "' ";
        $stringaSelect .= $selectedcom . ">";
        $stringaSelect .= $result[$i]['Denominazione'] . " - " . $result[$i]['CC'] . " </option>\n";
    }

    return $stringaSelect;
}
//print_r($a_act);

?>

<!--<link REL=StyleSheet HREF="../CSS/image_magnifier.css" TYPE="text/css" MEDIA=screen>
<script type="text/javascript" language="javascript" src="../librerie/js/image_magnifier.js"></script>-->


<!-- ********** GESTIONE LINK MENU ********** -->
<script>

    var precSucc = "";

    var a_act = <?php echo json_encode($a_act); ?>;

    function changeTableTypeId()
    {
        $("#TypeId").val($("#atto_rif").val());
        for(var i = 0; i< a_act.length; i++)
        {
            if(a_act[i].Id == $("#atto_rif").val() )
            {
                $("#TableTypeId").val(a_act[i].TableTypeId);
                break;
            }
        }
        console.log($("#TableTypeId").val());
    }
//F3
switchMenuImg("F3");
F3_button = function(){
    /*if ($("#idatto").val() == "")
    {
        alert ("Non è stato associato nessun atto a questo pagamento");
        return;
    }*/
    var uscita;
    var ret = true;
    var conferma = true;
    var nuovaImg = $("#img_caricato_bollettino").val();  //  sfoglia immagine
    if (nuovaImg != "")
    {
        if ("<?=$immagine?>" == "")
        {
            uscita = "aggiungitelematico";
            conferma = false;
        }
        else uscita = "associa";
    }
    else uscita = "associa";

    $('#invia_submit').val(uscita);

    if (conferma == true)
    {
        ret = confirm ("Attenzione: questo pagamento verr� bonificato definitivamente.\nVuoi continuare?");
    }
    if (ret == true)
    {
        $("#form_pagamento").submit();
    }
}

//F4
switchMenuImg("F4");
F4_button = function(){
    var ret = confirm ("Attenzione: questa modifica eliminer� definitivamente questo pagamento.\nVuoi continuare?");
    if (ret == true)
    {
        $('#invia_submit').val("elimina");
        $("#form_pagamento").submit();
    }
}

//F5
switchMenuImg("F5");
F5_button = function(){
    location.href="pagamento_da_bonificare.php?bonifica=<?php echo $visualizzo; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&provenienza=<?php echo $provenienza; ?>&telematico=<?php echo $telematico?>";
}

//F7
switchMenuImg("F7");
F7_button = function(){
    if( modifica == 0 )
    {
        var arrayBon = new Array();
        var i = 0;

        <?php echo $testoJs; ?>

        var prossimo = <?php echo $visualizzo; ?>;
        //alert ("prima  " + prossimo);

        var lunghArr = arrayBon.length;

        for (i = 0; i < lunghArr; i++)
        {
            if (arrayBon[i] == prossimo)
            {
                if (i == 0)
                {
                    prossimo = arrayBon[lunghArr-1];
                }
                else if (i == lunghArr-1)
                {
                    prossimo = arrayBon[i-2];
                }
                else
                {
                    prossimo = arrayBon[i-1];
                }
                break;
            }
        }

        //alert ("dopo  " + prossimo);
        location.href="pagamento_da_bonificare.php?bonifica="+prossimo+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&provenienza=<?php echo $provenienza; ?>&telematico=<?php echo $telematico?>";
    }
    else
        alert("salvare i dati o annullare prima di procedere");
}

//F8
switchMenuImg("F8");
F8_button = function(){
    if( modifica == 0 )
    {
        var arrayBon = new Array();
        var i = 0;

        <?php echo $testoJs; ?>

        var prossimo = <?php echo $visualizzo; ?>;
        //alert ("prima  " + prossimo);

        var lunghArr = arrayBon.length;

        for (i = 0; i < lunghArr; i++)
        {
            if (arrayBon[i] == prossimo)
            {
                if (i == 0)
                {
                    prossimo = arrayBon[1];
                }
                else if (i == lunghArr-1)
                {
                    prossimo = arrayBon[0];
                }
                else
                {
                    prossimo = arrayBon[i+1];
                }
                break;
            }
        }

        //alert ("dopo  " + prossimo);
        location.href="pagamento_da_bonificare.php?bonifica="+prossimo+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&provenienza=<?php echo $provenienza; ?>&telematico=<?php echo $telematico?>";
    }
    else
        alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'

</script>


<!-- ********** CALENDARIO ********** -->
<script>

<?php if ($totaleDaBonificare != 0) { ?>

$(document).ready(
	function()
	{
        ricerca(false);
		
		$('#data_pag').datepicker();


        dimensiona_magnify("3", 500, 400 , 500, 400 );
		//dimensiona_img_magnifier("thumbnail_image", <?php echo $w_img; ?> , <?php echo $h_img; ?> , 175, 110 );
		//dimensiona_img_magnifier("thumbnail_image", <?php echo $w_img; ?> , <?php echo $h_img; ?> , <?php echo $larghezza; ?>, <?php echo $altezza; ?> );
        dimensiona_magnify("2", <?php echo $w_img; ?> , <?php echo $h_img; ?> , <?php echo $larghezza; ?>, <?php echo $altezza; ?> );

		$("#submit_click").click( salva_form );

		$("#delete_click").click( cancella_form );
	}
);

<?php } ?>

</script>








<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

var invio_form = "Update";

function ricerca(flag)
{

    if(flag)
    {
        $("#atto_rif").addClass( "validateCustom vld_Custom_r" );
        $("#sceglicomune").addClass( "validateCustom vld_Custom_r" );
        $("#numeroattocoattivo").addClass( "validateCustom vld_Custom_r vld_Custom_n" );
        $("#annoattocoattivo").addClass( "validateCustom vld_Custom_r vld_Custom_n" );

        if(!validateForm()) return false;
    }

    var selectTipo = $("#TableTypeId").val();
    var docTypeId = $("#atto_rif").val();
    var selectComune = $("#sceglicomune").val();
    var selectCrono = $("#numeroattocoattivo").val();
    var selectAnno = $("#annoattocoattivo").val();
    var selectRiscossione = "<?=$tipoContoCorrente?>";
    var selectDataPag = "<?=$myPagDaBonificare->Data_Pagamento?>";

    //qui metti la validazione

    $.ajax({
        url: "../search/coattiva/ricerca_pagamento.php",
        dataType: "json",
        data: {
            tipo: selectTipo,
            comune: selectComune,
            crono: selectCrono,
            anno: selectAnno,
            tiporiscossione: selectRiscossione,
            datapagamento: selectDataPag,
            docTypeId: docTypeId

        },
        success: function(response) {
//fai cose con response
            //alert(response.CC);
            $("#atto_rif").removeClass( "validateCustom vld_Custom_r" );
            $("#sceglicomune").removeClass( "validateCustom vld_Custom_r" );
            $("#numeroattocoattivo").removeClass( "validateCustom vld_Custom_r vld_Custom_n" );
            $("#annoattocoattivo").removeClass( "validateCustom vld_Custom_r vld_Custom_n" );

            if (response == "")
            {
                alert ("Attenzione: nessun cronologico corrisponde alla ricerca!");
                $("#idatto").val("");
                $("#partita").val("");
                $("#atto_rif").val("");
                $("#tot_rate").val("");
                $("#utente").val("");
                $("#terzi").prop("checked", false);
                $("#num_rata").removeClass("sfondo_rosso");
                $("#pagamentopresente_riga1").text("");
                $("#pagamentopresente_riga2").text("");
                $("#num_rata").val("0");
            }
            else {
                var presentePag = response.pagPresente;

                $("#idatto").val(response.ID);  //  ID

                $("#partita").val(response.Partita_ID);

                $("#atto_rif").val(response.tipopag);
                $("#tot_rate").val(response.Rate_Previste);
                var NumeroTemp = response.NumPagamenti;
                var ContoTerzi = response.contoTerzi;  //  Y o N
                $("#utente").val(response.utente);  //  Cognome Nome

                // in base AL PARAMETRO CONTO TERZI!!!
                if (ContoTerzi == "Y") $("#terzi").prop("checked", true);
                else $("#terzi").prop("checked", false);

                var splitPresenza = presentePag.split("_");
                var NumeroPagamEffettuati, ScrittaRata;
                if (splitPresenza[0] == "GIAPRESENTE")  //  significa che ci sono pagamenti gi� effettuati!
                {
                    //NumeroPagamEffettuati = parseInt(splitPresenza[1]) + 1;
                    NumeroPagamEffettuati = parseInt(NumeroTemp);
                    NumeroPagamEffettuati += 1;
                    ScrittaRata = NumeroPagamEffettuati.toString();
                    $("#num_rata").addClass("sfondo_rosso");
                    $("#pagamentopresente_riga1").text("ALTRE RATE");
                    $("#pagamentopresente_riga2").text("GIA' INSERITE");
                }
                else if (splitPresenza[0] == "NONPRESENTE")
                {
                    NumeroPagamEffettuati = parseInt(NumeroTemp);
                    NumeroPagamEffettuati += 1;
                    ScrittaRata = NumeroPagamEffettuati.toString();
                    $("#num_rata").removeClass("sfondo_rosso");
                    $("#pagamentopresente_riga1").text("");
                    $("#pagamentopresente_riga2").text("");
                }
                else
                {
                    ScrittaRata = "XXX";
                    $("#num_rata").addClass("sfondo_rosso");
                    $("#pagamentopresente_riga1").text("RATA");
                    $("#pagamentopresente_riga2").text("SCONOSCIUTA");
                }
                $("#num_rata").val(ScrittaRata);
            }

        }
    });
}

/*function cercaatto()
{
	var selectTipo = $("#atto_rif").val();
	var selectOrdCoa = $("#sceltacrono").val();
	var selectComune = $("#sceglicomune").val();
	var selectCrono = $("#numeroattocoattivo").val();
	var selectAnno = $("#annoattocoattivo").val();
	var selectRiscossione = "<?=$tipoContoCorrente?>";
	var selectDataPag = "<?=$myPagDaBonificare->Data_Pagamento?>";
	
	if (selectOrdCoa == "C")
	{
		if (selectTipo == "" || selectOrdCoa == "" || selectComune == "" || selectCrono == "" || selectAnno == "") return;
	}
	else if (selectOrdCoa == "O")
	{
		if (selectOrdCoa == "" || selectComune == "" || selectCrono == "" || selectAnno == "") return;
	}
	else return;

	$.ajax({  
		  type: "POST",  
		  async: false,
		  url: "ajax/ajax_ricerca.php?",  
		  data: {	
			  		tipo: selectTipo,
			  		ordcoa: selectOrdCoa,
			  		comune: selectComune,
			  		crono: selectCrono,
			  		anno: selectAnno,
			  		tiporiscossione: selectRiscossione,
			  		datapagamento: selectDataPag
				}, 
				
		  success: function(cronologico)
		  {
				if (cronologico == "")
				{
					alert ("Attenzione: nessun cronologico corrisponde alla ricerca!");
					$("#idatto").val("");
					$("#partita").val("");
					$("#atto_rif").val("");
					$("#tot_rate").val("");
					$("#utente").val("");
					$("#terzi").prop("checked", false);
					$("#num_rata").removeClass("sfondo_rosso");
					$("#pagamentopresente_riga1").text("");
					$("#pagamentopresente_riga2").text("");
					$("#num_rata").val("0");
				}
				else
				{
					var conto = 0;
					//alert (cronologico);
					var splitAsterischi = cronologico.split ("**");

					//GIAPRESENTE_1**5279**C826**394**82**2014**Avviso di intimazione ad adempiere**
					//AVVISO_INTIMAZIONE_CDS**NUMERO VERBALE 3036/2007 DEL 29/10/2007 NOTIFICATO IN DATA 01/03/2008**
					//425.98**8**2**Y**BRICHETTO ANDREA

					var presentePag = splitAsterischi[conto++];  //  arriva NONPRESENTE_0 o GIAPRESENTE_(nrata)
					$("#idatto").val(splitAsterischi[conto++]);  //  ID
					conto++;//var ComuneTrovato = splitAsterischi[conto++];  //  CC_Comune
					$("#partita").val(splitAsterischi[conto++]);  //  Partita_ID
					conto++;//$("#idatto").val(splitAsterischi[3]);  //  ID_Cronologico
					conto++;//$("#idatto").val(splitAsterischi[4]);  //  Anno_Cronologico
					conto++;//$("#idatto").val(splitAsterischi[5]);  //  Atto (scritta   es. "Avviso di intimazione ad adempiere")
					$("#atto_rif").val(splitAsterischi[conto++]);  //  Atto (tipo  es. AVVISO_INTIMAZIONE_CDS)
					conto++;//$("#idatto").val(splitAsterischi[7]);  //  Info_Cartella
					conto++;//$("#idatto").val(splitAsterischi[8]);  //  Totale_Dovuto
					$("#tot_rate").val(splitAsterischi[conto++]);  //  Rate_Previste
					var NumeroTemp = splitAsterischi[conto++];  //  Numero Pagamenti Effettuati
					var ContoTerzi = splitAsterischi[conto++];  //  Y o N
					$("#utente").val(splitAsterischi[conto++]);  //  Cognome Nome

					// in base AL PARAMETRO CONTO TERZI!!!
					if (ContoTerzi == "Y") $("#terzi").prop("checked", true);
					else $("#terzi").prop("checked", false);

					var splitPresenza = presentePag.split("_");
					var NumeroPagamEffettuati, ScrittaRata;
					if (splitPresenza[0] == "GIAPRESENTE")  //  significa che ci sono pagamenti gi� effettuati!
					{
						//NumeroPagamEffettuati = parseInt(splitPresenza[1]) + 1;
						NumeroPagamEffettuati = parseInt(NumeroTemp);
						NumeroPagamEffettuati += 1;
						ScrittaRata = NumeroPagamEffettuati.toString();
						$("#num_rata").addClass("sfondo_rosso");
						$("#pagamentopresente_riga1").text("ALTRE RATE");
						$("#pagamentopresente_riga2").text("GIA' INSERITE");
					}
					else if (splitPresenza[0] == "NONPRESENTE")
					{
						NumeroPagamEffettuati = parseInt(NumeroTemp);
						NumeroPagamEffettuati += 1;
						ScrittaRata = NumeroPagamEffettuati.toString();
						$("#num_rata").removeClass("sfondo_rosso");
						$("#pagamentopresente_riga1").text("");
						$("#pagamentopresente_riga2").text("");
					}
					else
					{
						ScrittaRata = "XXX";
						$("#num_rata").addClass("sfondo_rosso");
						$("#pagamentopresente_riga1").text("RATA");
						$("#pagamentopresente_riga2").text("SCONOSCIUTA");
					}
					$("#num_rata").val(ScrittaRata);
				}
		  }
	});
}*/

function ctrlImmagine ()
{
	var vecchiaImg = $("#img_bollettino").val();  //  sfoglia immagine
	var nuovaImg = $("#img_caricato_bollettino").val();  //  sfoglia immagine
	if (nuovaImg != "")
	{
		var lungh = nuovaImg.length;
		var ultime3 = nuovaImg.substr(lungh-3, 3);
		ultime3 = ultime3.toUpperCase();
		if (ultime3 != "JPG" && ultime3 != "TIF")
		{
			alert ("Il file non è in formato JPG o TIF");
			$("#img_caricato_bollettino").replaceWith($("#img_caricato_bollettino").clone(true));
		}
		else
		{
			var ret;
			if (vecchiaImg != "") ret = confirm ("Attenzione: questa modifica sovrascriverà definitivamente l'immagine impostata precedentemente.\nVuoi continuare?");
			else ret = true;
			if (ret == true)
			{
				var uscita = "aggiungitelematico";
				$('#invia_submit').val(uscita);
				$("#form_pagamento").submit();
			}
		}
	}
}

function svuotaimmagine ()
{
	var vecchiaImg = $("#img_bollettino").val();  //  sfoglia immagine
	var ret;
	if (vecchiaImg != "") ret = confirm ("Attenzione: questa modifica cancellerà definitivamente l'immagine impostata.\nVuoi continuare?");
	else ret = false;
	$("#immaginevuota").attr("checked", false);
	$("#immaginevuota").attr("disabled", true);
	if (ret == true)
	{
		var uscita = "aggiungitelematico";
		$('#invia_submit').val(uscita);
		$("#form_pagamento").submit();
	}
}

function focus_index()
{
	$('[tabindex=1]').focus();
}

</script>

<?php 
	echo $scrittaEsito;
	if ($totaleDaBonificare == 0) return;
?>

    <div class="row justify-content-md-center " style="margin-bottom: 3%; margin-top: 2%;">
        <div class="col col-lg-10 col-lg-offset-1 text_center" style="border:3px solid #6D95D5;">
            <div style="float: left;">
                <span class="titolo font18">PAGAMENTO <?=$specifica?> <?=$specTelem?> DA BONIFICARE</span>
            </div>
            <div style="float: right;">
                <span class="titolo font18"><?=$attualeDaBonificare?> / <?=$totaleDaBonificare?></span>
            </div>
        </div>
    </div>

<form id=form_pagamento name=form_pagamento action="pagamento_da_bonificare.php" method=post enctype="multipart/form-data">
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >

<input name=invia_submit  id=invia_submit	type=hidden	>
<input name=bonifica  id=bonifica	type=hidden	 value="<?php echo $visualizzo; ?>">
<input name=provenienza  id=provenienza	type=hidden	 value="<?php echo $provenienza; ?>">
<input name=telematico  id=telematico	type=hidden	 value="<?php echo $telematico; ?>">
<input name=TableTypeId  id=TableTypeId	type=hidden	 value="<?php echo $cls_help->getVar("TableTypeId"); ?>">
<input name=TypeId  id=TypeId	type=hidden	 value="<?php echo $TypeId; ?>">

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Atto di riferimento</label>
                <div class="col-lg-8">
                    <select class="form-control resize" tabindex=1 id="atto_rif" name="atto_rif" onchange="changeTableTypeId();">
                        <?php echo $tipo_atto_rif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!--<div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo cronologico:</label>
                <div class="col-lg-8">
                    <select class="form-control resize" tabindex=2 name="sceltacrono" id="sceltacrono" onchange="cercaatto();">
                        <option value=""></option>
                        <option value="O" <?=$selOrd?>>Ordinario</option>
                        <option value="C" <?=$selCoa?>>Coattivo</option>
                    </select>
                </div>
            </div>
        </div>
        <label class="col-lg-5 control-label resize" id="pagamentopresente_riga1" style="text-align: left; color: red;"><?=$pagamentopresente_riga1?></label>
    </div>-->

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Cronologico:</label>
                <div class="col-lg-8">
                    <select tabindex=3 id='sceglicomune' name='sceglicomune' class="form-control resize" size=1 >
                        <option value=''></option>
                        <?php echo ElencoPagComuni($sceglicomune, $autorizzazione); ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="form-group">
                <div class="col-lg-5">
                    <input tabindex=4 type="text" class="form-control resize" name=numeroattocoattivo id=numeroattocoattivo value="<?=$numeroattocoattivo?>" size=5 >
                </div>
                <label class="col-lg-1 control-label resize">/</label>
                <div class="col-lg-5">
                    <input tabindex=5 type="text" class="form-control resize" name=annoattocoattivo id=annoattocoattivo value="<?=$annoattocoattivo?>" size=5 >
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-offset-1 col-lg-2">
            <button type="button" class="btn btn-primary" onclick="ricerca(true);">Ricerca pagamento</button>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 2%; margin-bottom: 2%;"></div>

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">ID atto</label>
                <div class="col-lg-8">
                    <input type="text" class="text_right form-control resize pwidth50" style="width: 30%;" readonly name=idatto id=idatto value="<?=$idatto?>">
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Partita associata:</label>
                <div class="col-lg-8">
                    <input type="text" class="text_right form-control resize pwidth50" style="width: 30%;" readonly name=partita id=partita value="<?=$partita?>">
                </div>
            </div>
        </div>
    </div>

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Utente</label>
                <div class="col-lg-8">
                    <input type="text" class="text_left form-control resize" readonly name=utente id=utente value="<?=$utente?>">
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Pagamento eseguito da:</label>
                <div class="col-lg-8">
                    <input tabindex=9 type="text" class="form-control resize" name=pagante id=pagante value="<?php echo $pagante; ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Data pagam.</label>
                <div class="col-lg-8">
                    <input tabindex=10 type="text" class="form-control resize" name=data_pag id=data_pag value="<?php echo $data_pag; ?>" size=9>
                </div>
            </div>
        </div>
    </div>

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Modalità</label>
                <label class="col-lg-4 control-label resize" style="text-align: left;">C/o terzi</label>
                <div class="col-lg-4">
                    <input tabindex=11 type=checkbox id=terzi class="resize" name=terzi <?=$checkedTerzi?> value='Y'>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <div class="form-group">
                <select id=tipo name=tipo tabindex=12 class="form-control resize">
                    <option></option>
                    <option id=tipo_1>Bancomat</option>
                    <option id=tipo_2>Bolletta</option>
                    <option id=tipo_3 selected>C/C</option>
                    <option id=tipo_4>Contanti</option>
                    <option id=tipo_5>Assegno</option>
                    <option id=tipo_6>POS</option>
                    <option id=tipo_7>Vaglia</option>
                    <option id=tipo_8>BPL</option>
                    <option id=tipo_9>BGSG</option>
                </select>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 2%; margin-bottom: 2%;"></div>

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Importo</label>
                <div class="col-lg-8">
                    <input class="text_right corrige_numero resize form-control" style="width: 50%;" tabindex=13 type="text" id=importo name=importo value="<?php echo $importo; ?>" size=6>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">QuintoCampo</label>
                <div class="col-lg-8">
                    <input class="resize form-control" readonly type="text" name="quinto_campo" value="<?php echo $quintocampo; ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Quietanza</label>
                    <div class="col-lg-4"><input class="text_right resize form-control" tabindex=15 type="text" id=quietanza name=quietanza value="<?php echo $quietanza; ?>" size=6></div>
                    <!--<div class="col-lg-1"></div>-->
                    <div class="col-lg-4"><input class="text_left resize form-control" tabindex=16 type="text" id=bollettario name=bollettario value="<?php echo $bollettario; ?>" size=6></div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">N° Rata</label>
                    <div class="col-lg-3"><input class="text_right resize form-control" tabindex=17 type="text" id=num_rata name=num_rata value="<?php echo $numero_rata; ?>" size=1></div>
                    <div class="col-lg-1">/</div>
                    <div class="col-lg-3"><input class="text_right resize form-control" tabindex=18 type="text" id=tot_rate name=tot_rate value="<?php echo $tot_rate; ?>" size=1></div>
            </div>
        </div>
    </div>

    <div class="row" >
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Immagine</label>
                <div class="col-lg-8">
                    <input class="resize form-control" readonly type="text" name="img_bollettino" id="img_bollettino" value="<?php echo $immagine; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 3%;">
        <div class="col-lg-6 col-lg-offset-3">
            <div id=mostra_immagine class="image-magnify3" title="Clicca per allargare immagine" onclick="window.open('<?php echo $PathViewImageFile; ?>')">
                <div class="thumbnail3">
                    <img id="thumbnail_image3" src="<?php echo $PathViewImageFile; ?>">
                    <div class="popup3"></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($telematico == "SI") { ?>

        <div class="row" >
            <div class="col col-lg-10 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize" style="text-align: left;">Svuota Img</label>
                    <div class="col-lg-1">
                        <input type="checkbox" <?=$disabledSvuota?>  class="resize" id="immaginevuota" value="Y" onchange="svuotaimmagine();">
                    </div>
                    <label class="col-lg-3 control-label resize" style="text-align: left;">Cambia Img</label>
                    <div class="col-lg-5">
                        <input class="resize form-control pwidth100" type="file" id="img_caricato_bollettino" name="img_caricato_bollettino" value="" onchange="ctrlImmagine();">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-lg-offset-3">
                <div id=mostra_immagine class="image-magnify2" title="Clicca per allargare immagine" onclick="window.open('<?php echo $src_immagine; ?>')">
                    <div class="thumbnail2">
                        <img id="thumbnail_image2" src="<?php echo $src_immagine; ?>">
                        <div class="popup2"></div>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 2%; margin-bottom: 2%;"></div>

    <div class="row" >
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label for="note" class="resize">Note</label>
                <textarea class="form-control resize" style="max-width: 100%;" id=note tabindex=19 name=note rows=1% onblur="focus_index();"><?php echo $note; ?></textarea>
            </div>
        </div>
    </div>


</form>

<?php include(INC."/footer.php"); ?>


