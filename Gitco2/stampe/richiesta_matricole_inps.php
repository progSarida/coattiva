<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

/*function estraiVariabile ( $testo , $array_variabili )
{
    $posto = false;
    for($i=0;$i<count($array_variabili);$i++)
    {
        $posto = strpos( $testo, $array_variabili[$i]);

        if($posto !== false)
        {
            $variabile = $array_variabili[$i];
            break;
        }
    }

    if($posto===false)
        $variabile = "{VARMANUALE}";

    return $variabile;
}*/


/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/flussi.php";
include CLASSI . "/pdf_con_bollettino.php";
include CLASSI . "/numero_letterale.php";
include CLASSI . "/enti_esterni.php";*/

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include_once(ROOT."/_parameter.php");

//include(INC . "/header.php");
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_Utils.php";
//include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_textParametersHtml.php";
//include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_Stampe.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_stp = new cls_Stampe();
$cls_utils = new cls_Utils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$ID_Atto = $cls_help->getVar('ID_Atto');
$stampa_select = strtoupper($cls_help->getVar('stampa_select'));

$_SESSION['progress'] = "0.00";
session_write_close();

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");//new ente_gestito($c);

if( $comune->Gestore_ID != 0 ) {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Gestore_ID']);
}
else {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Info_ID']);
}

$query = "SELECT * FROM gestore WHERE ID = '" . $comune->Ufficio_ID . "'";
$comune->Ufficio = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");

$nome_com = $comune->Denominazione;
/*$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];*/
$stemmaComune = $comune->Stemma_1;

$daco  = strtoupper($cls_help->getVar('daco'));
$anom  = strtoupper($cls_help->getVar('anom'));

$dano  = strtoupper($cls_help->getVar('dano'));
$acog  = strtoupper($cls_help->getVar('acog'));

$da_partita  = $cls_help->getVar('da_n_elenco');
$a_partita  = $cls_help->getVar('a_n_elenco');

$da_anno = $cls_help->getVar('da_anno');
$ad_anno = $cls_help->getVar('ad_anno');

$ordinamento = $cls_help->getVar('ordinamento');

/**		SELEZIONE UTENTI 			*/
$query_utente = $cls_stp->da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = $cls_db->getResults($cls_db->ExecuteQuery($query_utente));//mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
$where_anno = null;
if( $da_anno != null && $ad_anno != null )
	$where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."' ";

$query_partita = $cls_stp->da_a_partita( $c , $da_partita , $a_partita , $where_anno );
$array_partite = $cls_db->getResults($cls_db->ExecuteQuery($query_partita));//mysql_array( $query_partita );

$query_pignoramento = $cls_stp->select_pignoramento_presso_terzi( $c , null, $ordinamento );

/***************** *************************************************************************************************************************/
$query_pignoramento = "SELECT DISTINCT TERZI.Azienda, TERZI.Terzo_ID, GEN.Partita_ID FROM pignoramento_presso_terzi AS TERZI, pignoramento_generale AS GEN, tributo, partita_tributi WHERE partita_tributi.ID = GEN.partita_ID AND TERZI.Pignoramento_ID = GEN.ID AND GEN.Tipo = 'terzi' AND tributo.Partita_ID = GEN.Partita_ID AND TERZI.Azienda!='' AND TERZI.Terzo_ID = 0 ORDER BY partita_tributi.Comune_ID ASC, TERZI.ID ASC";
//echo $query_pignoramento;
/*************************************************************************** ******************************************************************************************************/
$array_codici = $cls_db->getResults($cls_db->ExecuteQuery($query_pignoramento));//mysql_array($query_pignoramento);

$num_codici = count($array_codici);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

sleep(1);

//$cls_help->alert($num_codici);

$array_codici_INPS = Array();
$cont_result = 0;
for( $l=0; $l < $num_codici; $l++ )//FOR CODICI
{
	//set_time_limit(30);

	if(session_status() == PHP_SESSION_NONE)session_start();
	$_SESSION['progress'] = number_format(($l*100)/$num_codici ,2);
	session_write_close();

	for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
	{		
		//if( $array_codici[$l]['Partita_ID'] == $array_partite[$k]['ID'] )//IF CODICE<->PARTITA
		//{
			
			for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
			{
				
				//if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF UTENTE<->PARTITA
				//{
					//set_time_limit(30);
					
					$control_codice = 0;
					foreach ($array_codici_INPS as $value_codice)
					{
						if($value_codice == $array_codici[$l]['Azienda'])
							$control_codice = 1;
					}
					
					if($control_codice == 0)
					{
						$array_codici_INPS[] = $array_codici[$l]['Azienda'];
						$cont_result++;
					}

				//}//CHIUSURA IF UTENTE<->PARTITA

			}//CHIUSURA FOR UTENTI
		
		//}//CHIUSURA IF CODICE<->PARTITA

	}//CHIUSURA PARTITE
		
}//CHIUSURA CODICI

if($cont_result==0)
{
	if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();
	
    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
	
    die;
}
else 
{
	
	$gestore = $comune->Gestore;
	$tipo_gestore = $gestore->Tipo;
	
	$indirizzo_gestore = $cls_stp->righe_indirizzo($gestore);
	
	if($tipo_gestore == "Concessionario")
	{
		$image_file = WEB_ROOT."/immagini/sarida_logo.png";
		$entrate = "delle entrate per conto del <b>Comune di $comune->Denominazione</b>";
		$dovuto = "al <b>Comune di $comune->Denominazione</b>";
	}
	else
	{
		$image_file = $stemmaComune;
		$entrate = "delle proprie entrate";
		$dovuto = "";
	}
	
	$intest_gestore = $cls_stp->intestazione_gestore("Riscossione coattiva", $nome_com, $gestore);
	
	$ufficio = $comune->Ufficio;
	$intest_ufficio = $cls_stp->intestazione_ufficio($ufficio);
	
	//UTENTE
    $query = "SELECT * FROM enti_esterni WHERE CC = '" . $c . "' AND Tipo = 'INPS'";
	$inps = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_esterni");//new enti_esterni($c, "INPS");
	$nome_utente = strtoupper($inps->Denominazione);
	$inps_ID = $inps->ID;
	$indirizzo_destinatario = $cls_stp->righe_indirizzo_enti_esterni($inps);
	$indirizzo_completo = $indirizzo_destinatario['Completo'];
	$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];
	
	//PARAMETRI
    $query = "SELECT * FROM parametri_responsabili WHERE CC = '".$c."' AND Tipo_Riscossione = 'CDS'";
	$par_responsabili = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_responsabili");//new parametri_responsabili($c, "CDS");
	$firma_resp = $cls_stp->carica_firme("Funzionario", "Responsabile", "Ufficiale",null,$par_responsabili);
	
	
	//TESTO
    $a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
    $cls_ente = new cls_ente($a_enteAdmin);
    $cls_ente->setPrintHeader();

    $cls_text = new cls_textParameters();
    $a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,19)));
	//var_dump($a_text);die;
    $cls_text->html_body = $a_text['Content'];
    $cls_text->html_replaced_body = $cls_text->html_body;

    $cls_text->a_var = array(
        "{COMUNEGESTORE}" => $gestore->Comune,
        "{DATASTAMPA}" => date('d/m/Y'),
        "{GESTORE}" => $intest_gestore['Riga1'], //grassetto
        "{INDIRIZZOGESTORE}" => $indirizzo_gestore['Completo'],//grassetto
        "{ENTRATE}" => $entrate,
        "{DOVUTO}" => $dovuto,
        "{PECGESTORE}" => $gestore->PEC,//grassetto
        /*"{INTESTAZIONERESPONSABILE}" => $a_recipientHeader['recipient'],
        "{Motivation}" => $parametri_notifica->Descrizione,
        "{Notes}" => $atto->Note_Blocco,
        "{ActsNotified}" => $cls_st->tutti_gli_atti_notificati($atto->Partita_ID),
        "{SignLegale}" => $cls_params->getHtmlSignature("{SignLegale}"),
        "{SignRespProcedimento}" => $cls_params->getHtmlSignature("{SignRespProcedimento}")*/
    );

    $cls_text->replaceVariables($cls_text->a_var);

    /*$para_testo = new testo_richiesta_matricole(NULL);
    $myId = $para_testo->CercaParametroData($c, date("Y-m-d"));
    $testo = new testo_richiesta_matricole($myId);

    $Luogo_Data = stripslashes($testo->Luogo_Data);
    $Oggetto = strtoupper(stripslashes($testo->Oggetto));
    $Descrizione =  stripslashes($testo->Descrizione);
    $Richiesta =  stripslashes($testo->Richiesta);
    $Legge =  stripslashes($testo->Legge);
    $PEC =  stripslashes($testo->PEC);
    $Saluti =  stripslashes($testo->Saluti);
    $Intestazione_Firma = stripslashes($testo->Intestazione_Firma);
    $Firma = stripslashes($testo->Firma);

    //SOSTITUZIONI VARIABILI
    SostituisciTestoTraGraffe ($Luogo_Data, "{COMUNEGESTORE}", $gestore->Comune );
    SostituisciTestoTraGraffe ($Luogo_Data, "{DATASTAMPA}", date('d/m/Y') );
    SostituisciTestoTraGraffe ($Descrizione, "{GESTORE}", $intest_gestore['Riga1'],'B');
    SostituisciTestoTraGraffe ($Descrizione, "{INDIRIZZOGESTORE}", $indirizzo_gestore['Completo'],'B');
    SostituisciTestoTraGraffe ($Descrizione, "{ENTRATE}", $entrate);
    SostituisciTestoTraGraffe ($Richiesta, "{DOVUTO}", $dovuto);
    SostituisciTestoTraGraffe ($PEC, "{PECGESTORE}", $gestore->PEC ,'B');

    $array_variabili = array('{FUNZIONARIORESPONSABILE}','{RESPONSABILEPROCEDIMENTO}');
    $variabile = estraiVariabile($Intestazione_Firma, $array_variabili);
    if($variabile == "{VARMANUALE}")						$firma_testo['intestazione'] = $Intestazione_Firma;
    else if($variabile == "{FUNZIONARIORESPONSABILE}")		$firma_testo['intestazione'] = $firma_resp[1]['intestazione'];
    else if($variabile == "{RESPONSABILEPROCEDIMENTO}")		$firma_testo['intestazione'] = $firma_resp[2]['intestazione'];

    $variabile = estraiVariabile($Firma, $array_variabili);
    if($variabile == "{VARMANUALE}")
    {
        $firma_testo['nome'] = $Firma;
        $firma_testo['firma'] = "";
    }
    else if($variabile == "{FUNZIONARIORESPONSABILE}")
    {
        $firma_testo['nome'] = $firma_resp[1]['nome'];
        $firma_testo['firma'] = $firma_resp[1]['firma'];
    }
    else if($variabile == "{RESPONSABILEPROCEDIMENTO}")
    {
        $firma_testo['nome'] = $firma_resp[2]['nome'];
        $firma_testo['firma'] = $firma_resp[2]['firma'];
    }*/
	
	/**
	 ///////////////////////////////		PDF	    //////////////////////////////////
	 **/

	$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

    $pdf->setDocParams();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage("P");
    if($stampa_select == "PROVVISORIA")
        $pdf->temporaryPrinting();
    $pdf->setManagerHeader($cls_ente->a_header);
    //$pdf->setRecipientHeader($a_recipientHeader);
    $pdf->SetMargins(7.0, 10.0, 7.0);
    $pdf->ln(0);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);


	/*$pdf->setPrintHeader(false);
	$pdf->SetAutoPageBreak(false);
	$pdf->SetCellPadding(0);
		
	//////////////	CORPO Pagina 1	//////////////
	$pdf->AddPage('P');
	
	if($stampa_select == "PROVVISORIA")
		$pdf->stampa_provvisoria();
	
	$pdf->SetLineWidth(0.2);
	$pdf->SetMargins(7.0, 10.0, 7.0);
	
	$pdf->intestazione_pdf($tipo_gestore, $image_file, $intest_gestore, $intest_ufficio);
	$pdf->destinatario_intestazione_pdf($inps_ID, $c, $nome_utente, "", "", $indirizzo_destinatario, $Luogo_Data , "ENTE" );
	$pdf->oggetto_pdf($Oggetto, "", "");
	
	//SOTTOSCRITTO
	$pdf->SetMargins(7.0, 10.0, 7.0);
	$pdf->ln(5);
	
	$pdf->SetFont('Arial', '', 9);
	$pdf->writeHTMLCell(0, 0, '', '', $Descrizione."\n",0,1,false,true,'J');
	$pdf->ln(5);
	$pdf->writeHTMLCell(0, 0, '', '', $Richiesta."\n",0,1,false,true,'J');
	$pdf->ln(5);
	$pdf->writeHTMLCell(0, 0, '', '', $Legge."\n",0,1,false,true,'J');
	$pdf->ln(5);
	$pdf->writeHTMLCell(0, 0, '', '', $PEC."\n",0,1,false,true,'J');
	$pdf->ln(5);
	$pdf->writeHTMLCell(0, 0, '', '', $Saluti."\n",0,1,false,true,'J');
	
	$pdf->ln(10);
	$pdf->firma_destra($firma_testo);
	
		
	//////////////	FINE CORPO Pagina 1	//////////////
	
	//////////////	CORPO Pagina 2	//////////////
	$pdf->AddPage('P');
	
	if($stampa_select == "PROVVISORIA")
		$pdf->stampa_provvisoria();
	
	$pdf->SetMargins(7.0, 10.0, 7.0);
	
	$pdf->SetFont('Arial', 'B', 9);
	
	$pdf->MultiCell(0, 0, "Elenco matricole INPS" , 0, 'C', 0, 0);
	
	$pdf->ln(10);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();
	$altezza_pag = $pdf->getPageHeight();

	
	$pdf->SetAutoPageBreak(false);
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');
	
	$array_width = array();
	$array_intestaz_1 = array();
	$array_intestaz_2 = array();
	
	$array_width[] = 22;						$array_intestaz_1[] = "Matricola";				$array_intestaz_2[] = "";
	$array_width[] = 48;						$array_intestaz_1[] = "Denominazione";			$array_intestaz_2[] = "";
	$array_width[] = 23;						$array_intestaz_1[] = "PI";						$array_intestaz_2[] = "";
	$array_width[] = 44;						$array_intestaz_1[] = "Indirizzo";				$array_intestaz_2[] = "";
	$array_width[] = 11;						$array_intestaz_1[] = "Civ";					$array_intestaz_2[] = "";
	$array_width[] = 11;						$array_intestaz_1[] = "Esp";					$array_intestaz_2[] = "";
	$array_width[] = 11;						$array_intestaz_1[] = "Int";					$array_intestaz_2[] = "";
	$array_width[] = ($larghezza_pag-170-14);	$array_intestaz_1[] = "Dettagli";				$array_intestaz_2[] = "";
	
	$pdf->setCellPaddings(2,1,2,1);
	$y1_vert = crea_riga( $pdf , $array_width , $array_intestaz_1 , "up_down" , $styleRetta );
	
	$pdf->SetFont('Arial', 'B', 8);
	
	for($i=0; $i<count($array_codici_INPS);$i++)
	{
	
	$array_value_1 = array();
	$array_value_2 = array();
		
	$array_value_1[] = $array_codici_INPS[$i];
	$array_value_1[] = "";
	$array_value_1[] = "";
	$array_value_1[] = "";
	$array_value_1[] = "";
	$array_value_1[] = "";
	$array_value_1[] = "";
	$array_value_1[] = "";
	
	$array_align = array("L","L","L","L","L","L","L","L");
		
	$pdf->setCellPaddings(2,2,2,2);
	$y = crea_riga($pdf , $array_width, $array_value_1 , "down" , $styleDash , $array_align );
	
	if( $y > $altezza_pag - 20)
	{
		$y2_vert = $pdf->getY();
		
		crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
		
		$pdf->AddPage('P');
	
		if($stampa_select == "PROVVISORIA")
		$pdf->stampa_provvisoria();
	
		$pdf->SetMargins(7.0, 10.0, 7.0);
		
		$pdf->SetFont('Arial', 'B', 9);
		
		$pdf->MultiCell(0, 0, "Elenco matricole INPS" , 0, 'C', 0, 0);
		
		$pdf->ln(10);
		
		$dim_pag = $pdf->getPageDimensions();
		$larghezza_pag = $pdf->getPageWidth();
		$altezza_pag = $pdf->getPageHeight();
		
		$pdf->SetAutoPageBreak(false);
		
		$pdf->setCellPaddings(2,1,2,1);
		$y1_vert = crea_riga( $pdf , $array_width , $array_intestaz_1 , "up_down" , $styleRetta );
		
		$pdf->SetFont('Arial', 'B', 8);
	}
	
	}
	
	$y2_vert = $pdf->getY();
	
	crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);*/
	
	//////////////	FINE CORPO Pagina 2	//////////////
		
	$path = $cls_utils->crea_dir( ATTI ."/". $c . "/Documenti" );
	$nome_file = "Richiesta_Codici_INPS_".$c."_".date("Y-m-d_H-i").".pdf";
	$file = "";
	
	if($stampa_select == "PROVVISORIA")
	{		
		$path_temp = $cls_utils->crea_dir( ATTI ."/". $c . "/Documenti/temp" );
        $cls_utils->cancella_files($path_temp, 0);

		$pdf->Output($path_temp."/".$nome_file , 'F');
		
		$file = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($path_temp."/".$nome_file);

	}
	else if($stampa_select == "DEFINITIVA"){
		$pdf->Output( $path."/".$nome_file , 'F');
		$file = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($path."/".$nome_file);
	}
		
	if(session_status() == PHP_SESSION_NONE)session_start();
	
	echo json_encode([
		"path" => $file,
		"error" => 0,
		"msg" => "File stampato correttamente!"
	]);


	
}