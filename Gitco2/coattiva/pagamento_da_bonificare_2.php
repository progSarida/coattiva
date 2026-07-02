<?php

	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/classe_anni.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	include CLASSI . "/pagamenti_importati.php";
	include CLASSI . "/parametri.php";
	
	if (!session_id()) session_start();
		
	if ($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	
	$a = get_var('a');
	$c = get_var('c');
	$p = get_var('p');
	$provenienza = get_var('provenienza');
	$bonifica = get_var('bonifica');
	$telematico = get_var('telematico');
	$sceltacrono = get_var('sceltacrono');
	//$pagamentopresente_riga1 = get_var('pagamentopresente_riga1');
	//$pagamentopresente_riga2 = get_var('pagamentopresente_riga2');
	$sceglicomune = get_var('sceglicomune');
	$numeroattocoattivo = get_var('numeroattocoattivo');
	$annoattocoattivo = get_var('annoattocoattivo');
	
	$idatto = get_var('idatto');
	$partita = get_var('partita');
	$utente = get_var('utente');
	$pagante = get_var('pagante');
	$terzi = get_var('terzi');
	$data_pag = to_mysql_date(get_var('data_pag'));
	$tipo = get_var('tipo');
	$importo = get_var('importo');
	$quietanza = get_var('quietanza');
	$bollettario = get_var('bollettario');
	$num_rata = get_var('num_rata');
	$note = get_var('note');
	$img_bollettino = get_var('img_bollettino');
	$img_caricato_bollettino = get_var('img_caricato_bollettino');
	
	$autorizzazione = get_var('aut_tipo');
	
	$scrittaEsito = "";
	$azzeraCampi = false;

	$invia_submit = get_var('invia_submit');
	if ($invia_submit == "associa")
	{
		$myAtto = new atto($idatto, $sceglicomune);
		
		$myPagamentoBonificato = new pagamento(null, $myAtto->CC);
		
		$myPagamentoBonificato->Comune_ID = $myPagamentoBonificato->ProssimoComuneId($myAtto->CC);
		$myPagamentoBonificato->CC = $myAtto->CC;
		$myPagamentoBonificato->Partita_ID = $myAtto->Partita_ID;
		$myPagamentoBonificato->Atto_ID = $myAtto->ID;
		$myPagamentoBonificato->Riferimento_Atto = 1;  // non usato
		$myPagamentoBonificato->Tipo_Atto = $myAtto->Atto;
		$myPagamentoBonificato->Pagante = $pagante;
		$myPagamentoBonificato->Conto_Terzi = $terzi;  //  parametro comune
		$myPagamentoBonificato->Data_Pagamento = $data_pag;
		$myPagamentoBonificato->Data_Registrazione = date("Y-m-d");
		$myPagamentoBonificato->Modalita = $tipo;  //  ??  bolletta o C/C
		$myPagamentoBonificato->Importo = str_replace(",", ".", $importo);
		$myPagamentoBonificato->Dovuto = $myAtto->Totale_Dovuto;
		$myPagamentoBonificato->Quietanza = $quietanza;
		$myPagamentoBonificato->Bollettario = $bollettario;
		$myPagamentoBonificato->Rata = $num_rata;
		$myPagamentoBonificato->Totale_Rate = "0";  //  non usato
		$myPagamentoBonificato->Note = $note;
		$myPagamentoBonificato->Bollettino = $img_bollettino;
		$myPagamentoBonificato->Telematico = $telematico;
		$myPagamentoBonificato->Data_Travaso_A_Gitco = "0000-00-00";
		$myPagamentoBonificato->Tipo_Pagamento = "BONIFICATO";
			
		$rispInsUpd = $myPagamentoBonificato->InsertUpdatePagamento();
		
		$myPagImportato = new pagamenti_importati($bonifica);
		
		$myPagImportato->Tipo_Pagamento = $myPagImportato->TipiPagamento($myAtto->Atto, "TIPODASCRITTA");
		$myPagImportato->Riferimento_Atto = $myAtto->ID;
		$myPagImportato->Comune_Riferimento = $myAtto->CC;
		$myPagImportato->Esito = "BONIFICATO";
		
		$myPagImportato->InsertUpdatePagamImportato("UPDATE");
		
		if ($img_bollettino != "")
		{
			$cartellaDestinazione = $_SERVER['DOCUMENT_ROOT'] . "/archivio/Atti/" . $myAtto->CC . "/Pagamenti/";
			$fotoOrigine = $PathCompletoPagamentiDaBonificare . $img_bollettino;
			$fotoDestinazione = $cartellaDestinazione . $img_bollettino;
			rename ($fotoOrigine, $fotoDestinazione);
		}
		
		$scrittaEsito = "<script>alert('Importazione avvenuta con successo');</script>";
		
		$azzeraCampi = true;
	}
	else if ($invia_submit == "aggiungitelematico")
	{
		if (isset($_FILES['img_caricato_bollettino']))
		{
			$cartellaDestinazione = $_SERVER['DOCUMENT_ROOT'] . "/archivio/Importazioni_Pagamenti/DaBonificare/";
			
			if ($img_bollettino != "" && file_exists($cartellaDestinazione . $img_bollettino))
			{
				unlink ($cartellaDestinazione . $img_bollettino);
			}
			$percorso_temp = $_FILES['img_caricato_bollettino']['tmp_name'];
			$img_bollettino = $_FILES['img_caricato_bollettino']['name'];
			
			$myAtto = new atto($idatto, $sceglicomune);
			
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
						// � gi� in formato jpg nel posto giusto
					}
					else if ($estensioneFile == "TIF")
					{
						$radiceImg = substr($img_bollettino, 0, -(strlen($estensioneFile)));
						$nomeBreveJpg = strtoupper($radiceImg . "JPG");
						$nuovoNomeFileDaBonificare = $cartellaDestinazione . $nomeBreveJpg;
						
						// al passo successivo il JPG verr� portato in "/archivio/Atti/" . $myAtto->CC . "/Pagamenti/"
						$cartellaFutura = $_SERVER['DOCUMENT_ROOT'] . "/archivio/Atti/" . $myAtto->CC . "/Pagamenti/";
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
				
				
				$myPagamentoBonificato = new pagamento(null, $myAtto->CC);
				
				$myPagamentoBonificato->Comune_ID = $myPagamentoBonificato->ProssimoComuneId($myAtto->CC);
				$myPagamentoBonificato->CC = $myAtto->CC;
				$myPagamentoBonificato->Partita_ID = $myAtto->Partita_ID;
				$myPagamentoBonificato->Atto_ID = $myAtto->ID;
				$myPagamentoBonificato->Riferimento_Atto = 1;  // non usato
				$myPagamentoBonificato->Tipo_Atto = $myAtto->Atto;
				$myPagamentoBonificato->Pagante = $pagante;
				$myPagamentoBonificato->Conto_Terzi = $terzi;  //  parametro comune
				$myPagamentoBonificato->Data_Pagamento = $data_pag;
				$myPagamentoBonificato->Data_Registrazione = date("Y-m-d");
				$myPagamentoBonificato->Modalita = $tipo;  //  ??  bolletta o C/C
				$myPagamentoBonificato->Importo = str_replace(",", ".", $importo);
				$myPagamentoBonificato->Dovuto = $myAtto->Totale_Dovuto;
				$myPagamentoBonificato->Quietanza = $quietanza;
				$myPagamentoBonificato->Bollettario = $bollettario;
				$myPagamentoBonificato->Rata = $num_rata;
				$myPagamentoBonificato->Totale_Rate = "0";  //  non usato
				$myPagamentoBonificato->Note = $note;
				$myPagamentoBonificato->Bollettino = $img_bollettino;
				$myPagamentoBonificato->Telematico = $telematico;
				$myPagamentoBonificato->Data_Travaso_A_Gitco = "0000-00-00";
				$myPagamentoBonificato->Tipo_Pagamento = "BONIFICATO";
					
				//$rispInsUpd = $myPagamentoBonificato->InsertUpdatePagamento();
				
				$myPagImportato = new pagamenti_importati($bonifica);
				
				//$myPagImportato->Tipo_Pagamento = $myPagImportato->TipiPagamento($myAtto->Atto, "TIPODASCRITTA");
				//$myPagImportato->Riferimento_Atto = $myAtto->ID;
				//$myPagImportato->Comune_Riferimento = $myAtto->CC;
				$myPagImportato->Immagine_Fronte = $img_bollettino;
				//$myPagImportato->Esito = "BONIFICATO";
				
				$myPagImportato->InsertUpdatePagamImportato("UPDATE");
				
				//$scrittaEsito = "<script>alert('Aggiornamento telematico avvenuta con successo');</script>";
				$scrittaEsito = "";
			}
			else if ($img_bollettino == "")  //  immagine vuota
			{
				$myPagImportato = new pagamenti_importati($bonifica);
				$myPagImportato->Immagine_Fronte = "";
				$myPagImportato->InsertUpdatePagamImportato("UPDATE");
			}
			else
			{
				$scrittaEsito = "<script>alert('Errore upload $fotoDestinazione importazione telematico');</script>";
				
				//  devo far cos� perche se c'� un errore di importazione, mi genera errori a raffica in php
				$myPagImportato = new pagamenti_importati($bonifica);
				$myPagImportato->Immagine_Fronte = "";
				$myPagImportato->InsertUpdatePagamImportato("UPDATE");
			}
		}
		else $scrittaEsito = "<script>alert('Errore importazione telematico');</script>";
	}
	else if ($invia_submit == "elimina")
	{
		$myPagImportato = new pagamenti_importati($bonifica);
		
		$myPagImportato->Esito = "ELIMINATO";
		
		$res = $myPagImportato->InsertUpdatePagamImportato("UPDATE");
		
		//echo "<br>" . $res . "<br>";
		
		$fotoDaEliminare = $PathCompletoPagamentiDaBonificare . $img_bollettino;
		
		$scrittaEsito = "<script>alert('Eliminazione avvenuta con successo');</script>";
		unlink ($fotoDaEliminare);
		
		$azzeraCampi = true;
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
	
	$comune = new ente_gestito($c);
	$nome_comune = $comune->Nome;
	
	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];
	
	//$layout = "<script>";
	
	$anni_gestiti = new anni_gestiti($c, null);
	
	if($c==null)
		$options_anni = null;
	else
	{
		$options_anni = $anni_gestiti->Options_Anni_Veloci($c, "COATTIVA", "pagamento_da_bonificare");
	
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
	
	$myPagDaBonificare = new pagamenti_importati(NULL);
	
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
	
	$listaDaBonificare = $myPagDaBonificare->ListaPagamentiDaBonificare($sceltaLista);
	
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
	
	$myPagDaBonificare = new pagamenti_importati($visualizzo);
	
	//$array_pagamenti = array();
	//$atto = $partita->Atto;	
	$tipo_atto_rif = $myPagDaBonificare->TipiPagamento($myPagDaBonificare->Tipo_Pagamento, "SELECT");
	
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
	
	if ($totaleDaBonificare != 0)
	{
		$quietanza = substr($myPagDaBonificare->Quinto_Campo, -4, 4);
		$quintocampo = $myPagDaBonificare->Quinto_Campo;
		$bollettario = "";
		$numero_rata = "0";
		$tot_rate = "";
		$note = "";
		$importo = number_format($myPagDaBonificare->Importo_Pagato, 2, ",", "");
		$pagante = "";
		$data_pag = from_mysql_date($myPagDaBonificare->Data_Pagamento);
		$modalita = "";
		$immagine = $myPagDaBonificare->Immagine_Fronte;
		$src_completo_immagine = $PathCompletoPagamentiDaBonificare . $myPagDaBonificare->Immagine_Fronte;
		$src_immagine = $PathPagamentiDaBonificare . $myPagDaBonificare->Immagine_Fronte;
		//$src_immagick = $PathImportazioniPagamenti . "Temp/" . $myPagDaBonificare->Immagine_Fronte;
		$id_pag = $myPagDaBonificare->ID;
		
		//$layout .= "<script>$('#mostra_immagine').hide();nuovo_record();</script>";
		
		if ($immagine != "")
		{
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
		else $disabledSvuota = " disabled ";
		
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
			alert ("Errore nella variabile provenienza");
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
				
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Pagamenti - Gestione</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>
	
	<link REL=StyleSheet HREF="/gitco2/css/image_magnifier.css" TYPE="text/css" MEDIA=screen>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/image_magnifier.js"></script>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{
	if ($("#idatto").val() == "")
	{
		alert ("Non � stato associato nessun atto a questo pagamento");
		return;
	}
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
function cancella_form() 
{  
	var ret = confirm ("Attenzione: questa modifica eliminer� definitivamente questo pagamento.\nVuoi continuare?");
	if (ret == true)
	{
		$('#invia_submit').val("elimina");
		$("#form_pagamento").submit();
	}
}

//F5
function annulla()
{
	location.href="pagamento_da_bonificare.php?bonifica=<?php echo $visualizzo; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&provenienza=<?php echo $provenienza; ?>&telematico=<?php echo $telematico?>";
}

//F6
function nuovo_F6()
{
	
}

//F7-F8
function cambia_pag(value)
{
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
					if (value == "suc") prossimo = arrayBon[1];
					else prossimo = arrayBon[lunghArr-1];
				}
				else if (i == lunghArr-1)
				{
					if (value == "suc") prossimo = arrayBon[0];
					else prossimo = arrayBon[i-2];
				}
				else
				{
					if (value == "suc") prossimo = arrayBon[i+1];
					else prossimo = arrayBon[i-1];
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

//PAG GIU
function pag_prec()
{
	
}

//PAG SU
function pag_suc()
{
	
}

//F9
function ricerca_F9()
{
	/*if( modifica == 0 )
	{
		RicercheDaId('utente',0);
	}
	else
		alert("salvare i dati o annullare prima di procedere");*/

}

//F10
function stampa_F10()
{
	return true;
}

//F11-F12 sono nel menu'

</script>


<!-- ********** CALENDARIO ********** -->
<script>

<?php if ($totaleDaBonificare != 0) { ?>

$(document).ready(
	function()
	{
		cercaatto();
		
		$('#data_pag').datepicker();

		//dimensiona_img_magnifier("thumbnail_image", <?php echo $w_img; ?> , <?php echo $h_img; ?> , 175, 110 );
		dimensiona_img_magnifier("thumbnail_image", <?php echo $w_img; ?> , <?php echo $h_img; ?> , <?php echo $larghezza; ?>, <?php echo $altezza; ?> );

		$("#submit_click").click( salva_form );

		$("#delete_click").click( cancella_form );
	}
);

<?php } ?>

</script>








<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

var invio_form = "Update";

function cercaatto()
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
}

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
			alert ("Il file non � in formato JPG o TIF");
			$("#img_caricato_bollettino").replaceWith($("#img_caricato_bollettino").clone(true));
		}
		else
		{
			var ret;
			if (vecchiaImg != "") ret = confirm ("Attenzione: questa modifica sovrascriver� definitivamente l'immagine impostata precedentemente.\nVuoi continuare?");
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
	if (vecchiaImg != "") ret = confirm ("Attenzione: questa modifica canceller� definitivamente l'immagine impostata.\nVuoi continuare?");
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

</head>

<body class="sfondo_new_gitco" >  

<?php 
	echo $scrittaEsito;
	if ($totaleDaBonificare == 0) return;
?>

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left">
			<font class="comune" ><?php echo $nome_comune; ?> <?php echo $options_anni; ?></font>
		</td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>

<?php 

if ($provenienza == "TARGHEESTERE")
{
	include TARGHEESTERE . '/menu/menu_targheestere.php';
}
else if ($provenienza == "COATTIVA")
{
	include MENU . '/menu_generale.php';
}
else 
{
	alert ("Errore nella variabile provenienza");
	return;
}

?>  
                
<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover="title='Record precedente F7'" onclick="cambia_pag('prev')">
          	<img src="/gitco2/immagini/FrecciaS.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
    	</td>
        <td width=7% align="center">
            <a href="#" onMouseover="title='Record successivo F8'" onclick="cambia_pag('suc')">
            <img src="/gitco2/immagini/FrecciaD.png" width=42px height=42px border="0" alt="Utente successivo">
            </a>
        </td>
        <td width=11%></td>
        <td width=7% align="center">
          	<a href="#" onMouseover="title='Stampa'" onclick="">
          	<img src="/gitco2/immagini/printF10grey.png" width=50 height=50 border="0" ></a>
    	</td>
        <td width=3%></td>
    	<td align=center width=7% >
    			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td width=2%></td>
		<td width=7%>
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td class="text_center width5">
			<!-- <a onMouseover="title='Cerca utente/partita'" href="#" onClick="RicercheDaId('utente',0);" style="text-decoration: none;">
			<img src="/gitco2/immagini/User Folder.png" width=47 height=47 border=0>
			</a> -->
		</td>
		<td class="text_center width70">
			<font class="titolo font18">PAGAMENTO <?=$specifica?> <?=$specTelem?> DA BONIFICARE</font>
		</td>
		<td class="text_right">
			<font class="titolo font18"><?=$attualeDaBonificare?> / <?=$totaleDaBonificare?></font>
		</td>
		<td class="text_right width5">
			&nbsp;
		</td>
	</tr>
</table>

<form id=form_pagamento name=form_pagamento action="pagamento_da_bonificare.php" method=post enctype="multipart/form-data">
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >

<input name=invia_submit  id=invia_submit	type=hidden	>
<input name=bonifica  id=bonifica	type=hidden	 value="<?php echo $visualizzo; ?>">
<input name=provenienza  id=provenienza	type=hidden	 value="<?php echo $provenienza; ?>">
<input name=telematico  id=telematico	type=hidden	 value="<?php echo $telematico; ?>">




	<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left"colspan=3><hr></td>
	</tr>
	<tr class="pheight30">
		<td class="text_left width23">
			Atto di riferimento
		</td>
		<td class="text_left">
			<select tabindex=1 id="atto_rif" name="atto_rif" onchange="cercaatto();">
				<?php echo $tipo_atto_rif; ?>
			</select>
		</td>
		<td class="text_left width25">
			&nbsp;
		</td>
	</tr>
	<tr class="pheight30">
		<td class="text_left">
			Tipo cronologico:
		</td>
		<td class="text_left">
			<select tabindex=2 name="sceltacrono" id="sceltacrono" onchange="cercaatto();">
				<option value=""></option>
				<option value="O" <?=$selOrd?>>Ordinario</option>
				<option value="C" <?=$selCoa?>>Coattivo</option>
			</select>
		</td>
		<td class="text_left">
			<label id="pagamentopresente_riga1" class="color_red"><?=$pagamentopresente_riga1?></label>
		</td>
	</tr>
	<tr class="pheight30">
		<td class="text_left">
			Cronologico:
		</td>
		<td class="text_left">
			<select tabindex=3 id='sceglicomune' name='sceglicomune' size=1 onchange='cercaatto();'>
				<option value=''></option>
				<?php echo ElencoPagComuni($sceglicomune, $autorizzazione); ?>
			</select>
			<br>
			<input tabindex=4 type="text" class="text_left" name=numeroattocoattivo id=numeroattocoattivo value="<?=$numeroattocoattivo?>" size=5 onchange="cercaatto();">
			/
			<input tabindex=5 type="text" class="text_left" name=annoattocoattivo id=annoattocoattivo value="<?=$annoattocoattivo?>" size=5 onchange="cercaatto();">
		</td>
		<td class="text_left">
			<label id="pagamentopresente_riga2" class="color_red"><?=$pagamentopresente_riga2?></label>
		</td>
	</tr>
	<tr class="pheight30">
		<td class="text_left" colspan=3>
			<hr>
		</td>
	</tr>
	</table>
	
	
	<table class="table_interna text_center" border="0">
	<tr class="pheight30">
		<td class="text_left width15">
			ID atto
		</td>
		<td class="text_left width30">
			<input type="text" class="text_right sfondo_grigio pwidth50" readonly name=idatto id=idatto value="<?=$idatto?>">
		</td>
		<td class="text_left width20">
			Partita associata:
		</td>
		<td class="text_left">
			<input type="text" class="text_right sfondo_grigio pwidth50" readonly name=partita id=partita value="<?=$partita?>">
		</td>
	</tr>
	<tr class="pheight30">
		<td class="text_left">
			Utente
		</td>
		<td class="text_left">
			<input type="text" class="text_left sfondo_grigio width95" readonly name=utente id=utente value="<?=$utente?>">
		</td>
		<td class="text_left">
			Pagamento eseguito da:
		</td>
		<td class="text_left">
			<input tabindex=9 type="text" class="text_left width95" name=pagante id=pagante value="<?php echo $pagante; ?>">
		</td>
	</tr>
	<tr class="pheight30">
		<td class="text_left" colspan=4>
			<hr>
		</td>
	</tr>
	</table>
	
	
	<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width40">
			<table class="text_left width100" border="0">
			<tr class="pheight30">
				<td class="text_left width35">
					Data pagam.
				</td>
				<td class="text_left">
					<input tabindex=10 type="text" class="text_center" name=data_pag id=data_pag value="<?php echo $data_pag; ?>" size=9>
				</td>
			</tr>
			<tr class="pheight30">
				<td class="text_left">
					Modalit�
				</td>
				<td class="text_left">
					<table class="text_left width100" border="0">
					<tr class="pheight30">
						<td class="text_left width45">
							C/o terzi
						</td>
						<td class="text_left width30">
							<input tabindex=11 type=checkbox id=terzi name=terzi <?=$checkedTerzi?> value='Y'>
						</td>
						<td class="text_left">
							<select id=tipo name=tipo tabindex=12>
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
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					Importo
				</td>
				<td class="text_left">
					<input class="text_right corrige_numero" tabindex=13 type="text" id=importo name=importo value="<?php echo $importo; ?>" size=6>
				</td>
			</tr>
			<tr class="pheight30">
				<td class="text_left">
					QuintoCampo
				</td>
				<td class="text_left">
					<input class="sfondo_grigio width100" readonly type="text" name="quinto_campo" value="<?php echo $quintocampo; ?>">
				</td>
			</tr>
			<tr class="pheight30">
				<td class="text_left">
					Quietanza
				</td>
				<td class="text_left">
					<input class="text_right" tabindex=15 type="text" id=quietanza name=quietanza value="<?php echo $quietanza; ?>" size=6>
					<input class="text_left" tabindex=16 type="text" id=bollettario name=bollettario value="<?php echo $bollettario; ?>" size=6>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					N� Rata
				</td>
				<td class="text_left">
					<input class="text_right" tabindex=17 type="text" id=num_rata name=num_rata value="<?php echo $numero_rata; ?>" size=1>
					/
					<input class="text_right" tabindex=18 type="text" id=tot_rate name=tot_rate value="<?php echo $tot_rate; ?>" size=1>
				</td>
			</tr>
			<tr class="pheight30">
				<td class="text_left">
					Immagine
				</td>
				<td class="text_left">
					<input class="sfondo_grigio width100" readonly type="text" name="img_bollettino" id="img_bollettino" value="<?php echo $immagine; ?>">
				</td>
			</tr>
			
			<?php if ($telematico == "SI") { ?>
				<tr class="pheight30 text_left">
					<td class="text_left" colspan="2">
					
							<table class="width100 text_left" border="0">
							<tr>
								<td class="width22 text_left">
									<font class="font11"><i>Svuota Img</i></font>
								</td>
								<td class="width15 text_left">
									<input type="checkbox" <?=$disabledSvuota?> id="immaginevuota" value="Y" onchange="svuotaimmagine();">
								</td>
								<td class="width22 text_right">
									<font class="font11"><i>Cambia Img</i></font>
								</td>
								<td class="text_left">
									<input class="sfondo_grigio pwidth100" type="file" id="img_caricato_bollettino" name="img_caricato_bollettino" value="" onchange="ctrlImmagine();">
								</td>
							</tr>
							</table>
					</td>
				</tr>
			<?php } ?>
			</table>
		</td>
		<td>
			<div id=mostra_immagine class="image-magnify" title="Clicca per allargare immagine" onclick="window.open('<?php echo $src_immagine; ?>')">
				<div class="thumbnail text_center">
					<img id="thumbnail_image" src="<?php echo $src_immagine; ?>">
					<div class="popup"></div>
				</div>
			</div>
		</td>
	</tr>
	</table>
	
	<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width15">
			Note
		</td>
		<td class="text_left">
			<textarea class="width99" id=note tabindex=19 name=note rows=1% onblur="focus_index();"><?php echo $note; ?></textarea>
		</td>
	</tr>
</table>


</form>

</td>
</tr>
</table>

<?php //echo $layout; ?>

</body>
</html>


<?php 

function ElencoPagComuni ($comuneselected, $autorizzazione)
{
	if ($autorizzazione == 1)
		$query = "select CC, Denominazione from enti_gestiti order by Denominazione ASC";
	else if ($autorizzazione == 2)  //  utente solo di un comune
		$query = "select CC, Denominazione from enti_gestiti where CC = '$comuneselected'";

	$result = mysql_query($query);
	$num = mysql_num_rows($result);
	$stringaSelect = "";

	while ($cliente = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		if ($cliente['CC'] == $comuneselected) $selectedcom = " selected ";
		else $selectedcom = "";
			
		$stringaSelect .= "<option value='" . $cliente['CC'] . "' ";
		$stringaSelect .= $selectedcom . ">";
		$stringaSelect .= $cliente['Denominazione'] . " - " . $cliente['CC'] . " </option>\n";
	}

	return $stringaSelect;
}

?>