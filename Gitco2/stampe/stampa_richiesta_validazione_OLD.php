<?php

function estraiVariabile ( $testo , $array_variabili )
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
}

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
include CLASSI . "/notifiche_importate.php";
include CLASSI . "/classe_email.php";*/

//require EMAIL.'/PHPMailerAutoload.php';
//require_once CLASSI. "\php-imap-client-master\Imap.php";

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}



include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";
include_once CLS."/cls_GestionePartita.php";
include_once CLS."/cls_DateTimeInLine.php";
include_once CLS."/cls_Utils.php";
include_once CLS."/cls_pdf.php";
include_once CLS."/cls_ente.php";
include_once CLS."/cls_LOG.php";
include_once CLS."/cls_registry.php";
include_once CLS."/cls_parameters.php";
include_once CLS."/cls_textParametersHtml.php";
include_once CLS."/cls_phpmailer.php";

$cls_partita = new cls_GP();
$log = new LOG();
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("IT",false);
$cls_utils = new cls_Utils();
$cls_params = new cls_parameters();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$ID_Atto = $cls_help->getVar('ID_Atto');
$ID_Ufficio = $cls_help->getVar('ID_Ufficio');
$tipo_richiesta = $cls_help->getVar('tipo_richiesta');
$stampa_select = strtoupper($cls_help->getVar('stampa_select'));
$tipo_utente = $cls_help->getVar('tipo_utente');


//ENTE GESTITO
$comune = $cls_partita->getDataComune($c);//new ente_gestito($c);
$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );

$nome_com = $comune->Denominazione;

//GESTORE/UFFICIO
$gestore = $comune->Gestore;
$dati_ente = $comune->Info;
$ufficio = $comune->Ufficio;
$tipo_gestore = $gestore->Tipo;
$indirizzo_gestore = $cls_partita->righe_indirizzo($gestore);
$stemmaComune = $comune->Stemma_1;

$stemmaGestore = $gestore->Stemma;

if($tipo_gestore == "Concessionario")
{
	if($stemmaGestore!="")
		$image_file = $stemmaGestore;
	else
		$image_file = IMMAGINIWEB."/sarida_logo.png";
}
else
	$image_file = $stemmaComune;

$intest_gestore = $cls_partita->intestazione_gestore("Riscossione coattiva", $nome_com,$gestore);
$intest_ufficio = $cls_partita->intestazione_ufficio($ufficio);
$PEC_gestore = $gestore->PEC;

//ATTO
//$atto = new atto($ID_Atto, $c);
$query = "SELECT * FROM v_atti WHERE Atto_ID = ".$ID_Atto." AND CC = '".$c."'";
$atto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");
$identificativo_atto = $atto->Atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico;
$spedizione = (object) $cls_partita->info_spedizione((array) $atto);
if($spedizione!=null)
{
	$data_spedizione = $spedizione->Data_Spedizione;
	$raccomandata = $spedizione->Ms_Rac_Num;
	$avv_ricevimento = $spedizione->Ms_Ric_Num;
}
else 
{
	$data_spedizione = "";
	$raccomandata = "";
	$avv_ricevimento = "";
}

//PARTITA
//$query = "SELECT * FROM partita_tributi WHERE ID = '".$atto->Partita_ID."' AND CC = '".$c."'";
//$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");
    
//$partita = new partita($atto->Partita_ID, $c );
$ID_partita = $atto->Comune_ID;
$anno_rif = $atto->Anno_Riferimento;
$tipo_riscossione = $atto->Tipo_Riscossione;

// //PARAMETRI SPEDIZIONE
// $par_spedizione = new parametri_spedizione($c, $tipo_riscossione);
// $invio_ufficio = $par_spedizione->Invio_Richieste_Validazione;

// if($invio_ufficio=="")
// {
// 	$cls_help->alert("Modalita' di invio documenti da selezionare nei Parametri di spedizione! Impossibile procedere con la stampa.");
// 	echo "<script>window.close();</script>";
// }

//UTENTE
$query = "SELECT * FROM utente WHERE ID = '".$atto->Utente_ID."' AND CC_Comune = '".$c."'";
$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente( $partita->Utente_ID , $c );


$query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente->Forma_Giuridica."' AND CC = '".$c."'";
$utente->Forma_Giuridica_Oggetto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa");//new forma_giuridica($val['Forma_Giuridica']);
$utente->Sigla_Forma_Giuridica = $utente->Forma_Giuridica_Oggetto->Sigla;
$utente = $cls_partita->GetDataToponimo($utente);


$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$utente->Sigla_Forma_Giuridica;
$utente_id = $utente->Comune_ID;
$indirizzi_utente = $cls_partita->righe_indirizzoUtente($utente);

if($utente->Paese_Nascita!="Italia" && $utente->Paese_Nascita!="ITALIA")
	$comuneNascita = "in ".$utente->Paese_Nascita;
else
	$comuneNascita = "a ".$utente->Comune_Nascita;

if($utente->Codice_Fiscale=="")
	$cf = "sconosciuto";
else
	$cf = $utente->Codice_Fiscale;

if($utente->Azienda=="")
	$azienda = "sconosciuta";
else 
	$azienda = $utente->Azienda;

if($utente->Partita_Iva=="" || $utente->Partita_Iva=="00000000000")
	$pi = "sconosciuta";
else
	$pi = $utente->Partita_Iva;

if($utente->Genere!="D")
	$info_utente = "nato/a ".$comuneNascita." il ".$cls_date->Get_DateNewFormat($utente->Data_Nascita,"DB")." - CF. ".$cf;
else 
	$info_utente = "matricola INPS ".$azienda." - PI. ".$pi;

$query = "SELECT * FROM parametri_notifica WHERE CC = '*****' AND ID = '".$atto->Motivo_Notifica."'";
$anomalia = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_notifica");//new parametri_notifica($atto->Motivo_Notifica);


if($tipo_utente == "ditta" && $tipo_richiesta!="duplicato") {
    //$cls_help->alert("gestore --> ".$dati_ente->ID);
    $ufficio_comune = $dati_ente;
}
else if($ID_Ufficio>0)
{
    //$cls_help->alert("ufficio_comune --> ".$ID_Ufficio);
    $query = "SELECT * FROM ufficio_comune WHERE ID = " . $ID_Ufficio . " ";
    $ufficio_comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ufficio_comune");//new ufficio_comune($ID_Ufficio);   
}
else 
	$ufficio_comune = null;

$mail_ufficio = "";
$invio_ufficio = "";
//PARAMETRI UFFICIO DESTINATARIO
if($ufficio_comune!=null)
{
	
	$indirizzo_ufficio_comune = $cls_partita->righe_indirizzo($ufficio_comune);
	if($tipo_richiesta!="duplicato")	
	{
		
		if($utente->Genere != "D")
		{
			$denom_ufficio[0] = strtoupper("Comune di ".$ufficio_comune->Comune);
			$denom_ufficio[1] = strtoupper("Ufficio anagrafe");
		}
		else
		{
			$denom_ufficio[0] = strtoupper("Comune di ".$dati_ente->Comune);
			$denom_ufficio[1] = strtoupper("Ufficio gestione SIATEL");
		}
	}
	else	
	{
		$denom_ufficio[0] = strtoupper("Comune di ".$ufficio_comune->Comune);
		$denom_ufficio[1] = strtoupper("Ufficio postale");
	}

	
	if($tipo_utente!="ditta")
		$invio_ufficio = $ufficio_comune->Modalita_Invio;
	else
		$invio_ufficio = "posta";
	
	if($invio_ufficio=="PEC")
	{
		if($ufficio_comune->PEC=="")
		{
			$cls_help->alert("INVIO TRAMITE PEC: L'indirizzo PEC dell'ufficio destinatario del comune di ".$ufficio_comune->Comune." non e' stato inserito! Impossibile procedere con la stampa.");
			echo "<script>window.close();</script>";
		}
		
		//PARAMETRI EMAIL
        $query = "SELECT Indirizzo_Email AS Address, Nome_Visualizzato AS PublicName, Server_Posta_Uscita AS OutMailServer, Porta_Uscita AS OutMailPort, 
                    Protocollo_Uscita AS OutMailProtocol, Sicurezza_Connessione AS ConnectionSafety, Autenticazione_Uscita AS OutAuthentication, 
                    Nome_Utente_Uscita AS OutUsername, Password_Uscita AS OutPassword
                    FROM parametri_email WHERE CC = '".$c."' AND Tipo_Email = '".$invio_ufficio."' AND Tipo_Riscossione = 'GENERALE' LIMIT 1";
		$par_email = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_email");//new parametri_email($c, $tipo_riscossione, $invio_ufficio);

		if($par_email->Address=="")
		{
			$cls_help->alert("INVIO TRAMITE PEC: L'indirizzo PEC da cui deve essere spedita la richiesta non e' stato inserito! Impossibile procedere con la stampa.");
			echo "<script>window.close();</script>";
		}
			
		$mail_ufficio = $ufficio_comune->PEC;

	}
	else if($invio_ufficio=="email")
	{
		if($ufficio_comune->Mail=="")
		{
			$cls_help->alert("INVIO TRAMITE EMAIL: L'indirizzo eMail dell'ufficio destinatario del comune di ".$ufficio_comune->Comune." non e' stato inserito! Impossibile procedere con la stampa.");
			echo "<script>window.close();</script>";
		}
		
		//PARAMETRI EMAIL
        $query = "SELECT Indirizzo_Email AS Address, Nome_Visualizzato AS PublicName, Server_Posta_Uscita AS OutMailServer, Porta_Uscita AS OutMailPort, 
                    Protocollo_Uscita AS OutMailProtocol, Sicurezza_Connessione AS ConnectionSafety, Autenticazione_Uscita AS OutAuthentication, 
                    Nome_Utente_Uscita AS OutUsername, Password_Uscita AS OutPassword
                    FROM parametri_email WHERE CC = '".$c."' AND Tipo_Email = '".$invio_ufficio."' AND Tipo_Riscossione = 'GENERALE' LIMIT 1";
		$par_email = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_email");//new parametri_email($c, $tipo_riscossione, $invio_ufficio);
		if($par_email->Address=="")
		{
			$cls_help->alert("INVIO TRAMITE EMAIL: L'indirizzo eMail da cui deve essere spedita la richiesta non e' stato inserito! Impossibile procedere con la stampa.");
			echo "<script>window.close();</script>";
		}
		
		$mail_ufficio = $ufficio_comune->Mail;
	}
	else if($invio_ufficio=="posta")
	{
		if($ufficio_comune->Fax=="")
		{
			$cls_help->alert("INVIO TRAMITE POSTA/FAX: Il numero di Fax dell'ufficio destinatario del comune di ".$ufficio_comune->Comune." non e' stato inserito! Impossibile procedere con la stampa.");
			echo "<script>window.close();</script>";
		}
		
		$mail_ufficio = "posta";
	}
	else
	{
		$cls_help->alert("Modalita' di invio documenti da selezionare per l'ufficio destinatario! Impossibile procedere con la stampa.");
		echo "<script>window.close();</script>";
	}
	
}
else 
{
	$denom_ufficio[0] = "_____________________________";
	$indirizzo_ufficio_comune['Riga1'] = "_____________________________";
	$indirizzo_ufficio_comune['Riga2'] = "_____________________________";
	$indirizzo_ufficio_comune['Riga3'] = "_____________________________";
	$indirizzo_ufficio_comune['Riga4'] = "_____________________________";
	$mail_ufficio = "posta";
}


/**
 * PARAMETRI DI TESTO
 */
$atto_identificativo = "(".$atto->Atto." cronologico n. ".$atto->ID_Cronologico;
$atto_identificativo.= "/".$atto->Anno_Cronologico.")";

//SOSTITUZIONI
	$replace_motivazione_richiesta = "al mittente - indirizzo insufficiente e/o non piu' all'indirizzo";
	$replace_comune_gestito = $comune->Denominazione;
	$replace_info_destinatario = $nome_utente." ".$info_utente." ".$atto_identificativo;
	if($tipo_richiesta == "duplicato")
		$replace_info_destinatario = $nome_utente.", ".$indirizzi_utente['Senza_Provincia'].", ".$info_utente." ".$atto_identificativo;
	$replace_fax_gestore = $gestore->Fax;
	$replace_indirizzo_gestore = $indirizzo_gestore['Completo'];
	$replace_estremi_AR = "spedito in data ".$cls_date->Get_DateNewFormat($data_spedizione,"DB")." [Raccomandata n. ".$raccomandata." - Avviso di ricevimento n. ".$avv_ricevimento."]";

switch($tipo_richiesta)
{
	case 'indirizzo':	
		
		$tipo_file = "Richiesta_Indirizzo";
		$info_file = "Richiesta indirizzo";
		
		/*$myId = $cls_partita->CercaParametroData($c, date("Y-m-d"),"testo_richiesta_indirizzo");
		$query = "SELECT * FROM testo_richiesta_indirizzo WHERE ID = '" . $myId."'";
		$testo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"testo_richiesta_indirizzo");//new testo_richiesta_indirizzo($myId);
		
		$Motivazione = stripslashes($testo->Motivazione);
		/*************************************************************************
			//$cls_utils->SostituisciTestoTraGraffe ($Motivazione, "{MOTIVAZIONERICHIESTA}", $replace_motivazione_richiesta);
         ************************************************************************
		$Richiesta_Utente = stripslashes($testo->Richiesta_Utente);
		$Richiesta_Siatel = stripslashes($testo->Richiesta_Siatel);
		$Informativa_Richiesta = stripslashes($testo->Informativa_Richiesta);
		
		$Richiesta_Certificato = "";
		$Richiesta_Duplicato = "";
		$Urgenza_Richiesta = "";*/

        $cls_text = new cls_textParameters();
        $a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,16)));

        $cls_text->html_body = $a_text['Content'];
        $cls_text->html_replaced_body = $cls_text->html_body;
		
		break;
		
	case 'decesso'	:
		
		$tipo_file = "Richiesta_Certificato_Decesso";
		$info_file = "Richiesta certificato di decesso";
		
		/*//$para_testo = new testo_richiesta_decesso(NULL);
        $myId = $cls_partita->CercaParametroData($c, date("Y-m-d"),"testo_richiesta_decesso");
        $query = "SELECT * FROM testo_richiesta_decesso WHERE ID = '" . $myId."'";
		$testo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"testo_richiesta_decesso");//new testo_richiesta_decesso($myId);
		
		$Richiesta_Certificato = stripslashes($testo->Richiesta_Certificato);
		$Informativa_Richiesta = stripslashes($testo->Informativa_Richiesta);
		
		$Motivazione = "";
		$Richiesta_Utente = "";
		$Richiesta_Siatel = "";
		$Richiesta_Duplicato = "";
		$Urgenza_Richiesta = "";*/

        $cls_text = new cls_textParameters();
        $a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,17)));

        $cls_text->html_body = $a_text['Content'];
        $cls_text->html_replaced_body = $cls_text->html_body;
		
		break;
		
	case 'duplicato':
		
		$tipo_file = "Richiesta_Duplicato_AR";
		$info_file = "Richiesta di duplicato AR";
		
		/*//$para_testo = new testo_richiesta_duplicato_AR(NULL);
        $myId = $cls_partita->CercaParametroData($c, date("Y-m-d"),"testo_richiesta_duplicato_AR");
        $query = "SELECT * FROM testo_richiesta_duplicato_AR WHERE ID = '" . $myId."'";
		$testo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"testo_richiesta_duplicato_AR");//new testo_richiesta_duplicato_AR($myId);
		
		$Richiesta_Duplicato = stripslashes($testo->Richiesta_Duplicato);
		$Urgenza_Richiesta = stripslashes($testo->Urgenza_Richiesta);
		
		$Motivazione = "";
		$Richiesta_Utente = "";
		$Richiesta_Siatel = "";
		$Richiesta_Certificato = "";
		$Informativa_Richiesta = "";*/

        $cls_text = new cls_textParameters();
        $a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c,18)));

        $cls_text->html_body = $a_text['Content'];
        $cls_text->html_replaced_body = $cls_text->html_body;
		
		break;
		
	default:	
		
		$cls_help->alert('Errore di selezione stampa.');
		die;	
		
		break;
}

$pec_stampa = "";

if($tipo_richiesta!="duplicato")
{
    $pec_stampa = "PEC";
    if($PEC_gestore!="")	$pec_stampa .= " all'indirizzo ".$PEC_gestore;
}
else{
    if($spedizione!=null)
        $replace_estremi_AR = ", ".$replace_estremi_AR.",";
    else
        $replace_estremi_AR = "";
}



/*$Oggetto = stripslashes($testo->Oggetto);
$Premessa = stripslashes($testo->Premessa);
	$cls_utils->SostituisciTestoTraGraffe ($Premessa, "{COMUNEGESTITO}", $replace_comune_gestito);
$Informazioni = stripslashes($testo->Informazioni);
	$cls_utils->SostituisciTestoTraGraffe ($Informazioni, "{INFODESTINATARIO}", $replace_info_destinatario,'B');
$Contatti = stripslashes($testo->Contatti);
if($tipo_richiesta!="duplicato")
{
	$pec_stampa = "PEC";
	if($PEC_gestore!="")	$pec_stampa .= " all'indirizzo ".$PEC_gestore;

	$cls_utils->SostituisciTestoTraGraffe ($Contatti, "{FAXGESTORE}", $replace_fax_gestore);
	$cls_utils->SostituisciTestoTraGraffe ($Contatti, "{PECGESTORE}", $pec_stampa);
}
else
{
	$cls_utils->SostituisciTestoTraGraffe ($Contatti, "{INDIRIZZOGESTORE}", $replace_indirizzo_gestore,'B');
	if($spedizione!=null)
		$cls_utils->SostituisciTestoTraGraffe ($Informazioni, "{ESTREMIAR}", ", ".$replace_estremi_AR.",");
	else 
		$cls_utils->SostituisciTestoTraGraffe ($Informazioni, "{ESTREMIAR}", "");
}
$Saluti = stripslashes($testo->Saluti);
$Avvertenze = stripslashes($testo->Avvertenze);
$Intestatario = stripslashes($testo->Intestatario_Firma);
$Firma = stripslashes($testo->Firma);*/

$query = "SELECT * FROM parametri_responsabili WHERE CC = '".$c."' AND Tipo_Riscossione = 'GENERALE'";
$par_responsabili = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_responsabili");//new parametri_responsabili($c, $tipo_riscossione);


/*$firme_responsabili = $cls_partita->firme_responsabili($par_responsabili);
$firma_resp = $cls_partita->carica_firme($par_responsabili,"Funzionario", "Responsabile", "Ufficiale", "Responsabile_Richieste");*/

/*if($firma_resp[4]['firma']=="")
{
//$cls_help->alert("Firma del responsabile della richiesta mancante! Verificare i parametri responsabili inseriti.");
}*/



/*$array_variabili = array('{RESPONSABILERICHIESTA}');

$variabile = estraiVariabile($Intestatario, $array_variabili);
if($variabile == "{RESPONSABILERICHIESTA}")		
{
	$firma['intestazione'] = $firma_resp[4]['intestazione'];
}
else	$firma['intestazione'] = $Intestatario;

$variabile = estraiVariabile($Firma, $array_variabili);
if($variabile == "{RESPONSABILERICHIESTA}")
{
	$firma['nome'] = $firma_resp[4]['nome'];
	$firma['firma'] = $firma_resp[4]['firma'];
}
else
{
	$firma['nome'] = $Firma;
	$firma['firma'] = "";
}*/

/**
 * 	FINE PARAMETRI DI TESTO	********************************************************************
 */



/**
 ///////////////////////////////		PDF	    //////////////////////////////////
 */


$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetAutoPageBreak(false);
$pdf->SetCellPadding(0);
$pdf->AddPage('P');


if($stampa_select == "PROVVISORIA")
    $pdf->temporaryPrinting();

$pdf->SetLineWidth(0.2);
$pdf->SetMargins(7.0, 10.0, 7.0);


//////////////	CORPO Pagina 1	//////////////

/*$arrLogo = [
    "logo" => $image_file,
    "logoPath" => $image_file,
    "left" => $intest_gestore,
    "right" => $intest_ufficio
    ];*/



$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
//$managerCity = $cls_ente->getCityManager();
$pdf->setManagerHeader($cls_ente->a_header);

$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $atto->Tipo_Riscossione)));
//var_dump($a_responsibleParams);
if(!is_array($a_responsibleParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri dei responsabili assenti per ".$atto->Tipo_Riscossione."!");
    echo "<script>window.close();</script>";
}

$cls_params->setArray("responsabili",$a_responsibleParams);
$cls_params->getSignatures($cls_ente->type);

$cls_registry = new cls_registry();
//$query = $cls_registry->getVAnagrafe_query($utente->ID);

//$registry = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$pdf->setRecipientHeaderPostal($cls_registry->printHeader((array) $atto),$denom_ufficio,$indirizzo_ufficio_comune);

$cls_text->a_var = array(
    "{ManagedMunicipality}" => $replace_comune_gestito,
    "{RecipientInformation}" => $replace_info_destinatario,
    "{MotivationRequired}" => $replace_motivazione_richiesta,
    "{ManagerFax}" => $replace_fax_gestore,
    "{ManagerPec}" => $pec_stampa,
    "{SignRespRichieste}" => $cls_params->getHtmlSignature("{SignRespRichieste}"),
    "{ExtremeAr}" => $replace_estremi_AR,
    "{ManagerAddress}" => $replace_indirizzo_gestore
);

$cls_text->replaceVariables($cls_text->a_var);

$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);

//////////////	FINE CORPO Pagina 1	//////////////

$path = $cls_utils->crea_dir( ATTI ."/". $c . "/Documenti" );

$identificativo_file = $tipo_file."_".$c."_".$atto->ID_Cronologico."_".$atto->Anno_Cronologico."_".date("Y-m-d_H-i");
$nome_file = $identificativo_file.".pdf";



if($stampa_select == "PROVVISORIA")
{
	$pdf->Output( $nome_file , 'I');
	die;
}
else if($stampa_select == "DEFINITIVA")
	$pdf->Output( $path."/".$nome_file , 'F');

$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$comune_id = isset($result["Com"])?$result["Com"]:0;

$salva = new stdClass();//documento( null , $c );

$salva->CC = $c;
$salva->Comune_ID = $comune_id + 1;
$salva->File = $nome_file;
$salva->Utente_ID = $atto->Utente_ID;
$salva->Tipo = "Inviato";
$salva->Atto = $info_file;
$salva->Data_Creazione = date("Y-m-d");
$salva->Oggetto = $identificativo_file;

if($mail_ufficio!="posta" && $mail_ufficio!="")
{
	$salva->Informazioni_Aggiuntive = "Richiesta inviata tramite ".$invio_ufficio." all'indirizzo mail ".$mail_ufficio." dell'".$denom_ufficio[0];
	$salva->Contenuto = "";
}
else 
{
	$salva->Informazioni_Aggiuntive = "";
	$salva->Contenuto = "";
}

$salva->Data_Stampa = date("Y-m-d");

$mostra_file = $path."/".$nome_file;
$mostra_file_web = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($mostra_file);


$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if($mail_ufficio!="posta" && $mail_ufficio!="")
{	

	$subject = $identificativo_file;
	$body = "Richiesta allegata alla mail";
	
	set_time_limit(200);

    $cls_mail = new cls_phpmailer((array) $par_email);

    //Variabile aggiunta da me in phpMailer, (nelle versioni nuove della libreria ci dovrebbe già essere)
    $cls_mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );



    $a_params = array(
        "subject" => $subject,
        "body" => $body
    );

    $cls_mail->mailCreation($a_params);

    $nome_ufficio = $denom_ufficio[0];
    if(isset($denom_ufficio[1])) $nome_ufficio .= $denom_ufficio[1];
    /*$mail_ufficio, $denom_ufficio*/
    $cls_mail->addAddress($mail_ufficio, $nome_ufficio);

    $cls_mail->addAttachment($path."/".$nome_file);

    /*var_dump($par_email);
    echo "<br><br>";
    var_dump($a_params);
    echo "<br><br>";
    echo $path."/".$nome_file;*/

	/*$mail = new PHPMailer();
	
	$mail->creaMailCompleta($par_email, $subject, $body);
	$mail->addAddress(/*$mail_ufficio, $denom_ufficio"gianluca.virdis8901@gmail.com");
	$mail->addAttachment($path."/".$nome_file);*/

    //$log->info("Prova LOG. \nIndirizzo: ".$par_email->Address."\nOutMailServer: ".$par_email->OutMailServer."\nOutMailPort: ".$par_email->OutMailPort."\nOutMailProtocol: ".$par_email->OutMailProtocol."\nOutUsername: ".$par_email->OutUsername."\nPassword: ".$par_email->OutPassword);
    //die;

    $cls_mail->preSend();
    $message = $cls_mail->getSentMIMEMessage();




	if($cls_mail->send())
	{
	    //$log->warning("Mail inviata.");
        $log->info("Mail inviata. \nIndirizzo: ".$par_email->Address."\nOutMailServer: ".$par_email->OutMailServer."\nOutMailPort: ".$par_email->OutMailPort."\nOutMailProtocol: ".$par_email->OutMailProtocol."\nOutUsername: ".$par_email->OutUsername."\nPassword: ".$par_email->OutPassword);
        //die;

		$control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $salva,"documento"));// $salva->Insert( true );
		
		if( $control_salva )
		{
		    $log->info("Inserimento su tabella documento riuscita");
			$id_documento = $control_salva;
		
			$attoTemp["ID_Richiesta_Rateizzazione"] = $id_documento;
			$control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery($attoTemp,"atto",array("ID" => $ID_Atto)));// $atto->Update($ID_Atto);
		
			if( $control_salva )
			{
                $log->info("Aggiornamento su tabella atto riuscita");
			}
			else
			{
                $cls_db->Rollback();
                $log->error("Aggiornamento su tabella atto fallito");
				die;
			}
		
		}
		else
		{
			$cls_db->Rollback();
			$log->error("Salvataggio tabella documento fallita");
			die;
		}
		
		$salva_email = new stdClass();//new email_inviate(null);
		
		$salva_email->CC = $c;
		$salva_email->Partita_ID = $atto->Partita_ID;
		$salva_email->Utente_ID = $utente->ID;
		$salva_email->Oggetto = $identificativo_file;
		$salva_email->Mail_Sorgente = $par_email->Address;
		$salva_email->Tipo_Sorgente = $invio_ufficio;
		$salva_email->Mail_Destinatario = $mail_ufficio;
		$salva_email->Tipo_Destinatario = $invio_ufficio;
		$salva_email->Data_Invio = date('Y-m-d');
		
		if($invio_ufficio=="PEC")
		{
			$salva_email->Ricevuta_Accettazione = "attesa";
            $salva_email->Ricevuta_Consegna = "attesa";
		}
		else 
		{
			$salva_email->Ricevuta_Accettazione = "no";
			$salva_email->Ricevuta_Consegna = "no";
		}

        $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $salva_email,"email_inviate"));
		
		if( $control_salva )
		{
            $log->info("Inserimento su tabella email_inviate riuscito");
		}
		else
		{
            $cls_db->Rollback();
            $log->error("Inserimento su tabella email_inviate fallito");
            die;
		}
		
		$path_mail = $cls_utils->crea_dir(EMAIL_ROOT."/".$c."/".$invio_ufficio."/".$identificativo_file);
		
		$myfile = fopen($path_mail."/".$identificativo_file.'.eml', 'w');
		fwrite($myfile, $message);
		
		fclose($myfile);

        $log->info("Documento salvato nella corrispondenza anagrafica! Email inviata correttamente!");
		
	}
	else
    {
        $log->warning("Son qui sull'errore mail");
        $log->error("Errore, mail non inviata. \nIndirizzo: ".$par_email->Address."\nOutMailServer: ".$par_email->OutMailServer."\nOutMailPort: ".$par_email->OutMailPort."\nOutMailProtocol: ".$par_email->OutMailProtocol."\nOutUsername: ".$par_email->OutUsername."\nPassword: ".$par_email->OutPassword."\n".$cls_mail->mailboxGetErrors());
        die;
    }




}
else 
{
    $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $salva,"documento"));
	//$control_salva = $salva->Insert( true );

	if( $control_salva )
	{
        $log->info("Inserimento su tabella documento riuscita 1");

		$id_documento = $control_salva;

        $attoTemp["ID_Richiesta_Rateizzazione"] = $id_documento;
        $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery($attoTemp,"atto",array("ID" => $ID_Atto)));

		//$atto->ID_Richiesta_Rateizzazione = $id_documento;
		//$control_salva = $atto->Update($ID_Atto);

        if( $control_salva )
        {
            $log->info("Aggiornamento su tabella atto riuscita");
        }
        else
        {
            $cls_db->Rollback();
            $log->error("Aggiornamento su tabella atto fallito");
            die;
        }

	}
	else
	{
        $cls_db->Rollback();
        $log->error("Inserimento su tabella documento fallita");
		die;
	}

}

$cls_db->End_Transaction();

$cls_help->alert("Documento salvato nella corrispondenza anagrafica!");
$log->info("Documento salvato in ".$mostra_file);
?>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        // Your code to run since DOM is loaded and ready
        document.getElementById('frame').src = '<?= $mostra_file_web; ?>';
    });
</script>
<div style="width: 100%; height: 100%">
    <iframe style="width: 100%; height: 100%" id="frame" src=""></iframe>
</div>
