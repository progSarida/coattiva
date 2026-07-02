<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/flussi.php";
include CLASSI . "/pdf_con_bollettino.php";
include CLASSI . "/numero_letterale.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$ID_Atto = get_var('ID_Atto');
$stampa_select = strtoupper(get_var('tipo_stampa'));

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
$stemmaComune = $comune->Stemma_1;


$gestore = $comune->Gestore;
$tipo_gestore = $gestore->Tipo;

$indirizzo_gestore = $gestore->righe_indirizzo();

if($tipo_gestore == "Concessionario")
	$image_file = "/gitco2/immagini/sarida_logo.png";
else
	$image_file = $stemmaComune;

$intest_gestore = $gestore->intestazione_gestore("Riscossione coattiva", $nome_com);

$ufficio = $comune->Ufficio;
$intest_ufficio = $ufficio->intestazione_ufficio();

//ATTO
$atto = new atto($ID_Atto, $c);
switch($atto->Atto)
{
	case "Avviso di intimazione ad adempiere": $tipo_atto = "Avv. Intimazione"; break;
	default: $tipo_atto = $atto->Atto;		break;
}

//PARTITA
$partita = new partita($atto->Partita_ID, $c );
$ID_partita = $partita->Comune_ID;
$anno_rif = $partita->Anno_Riferimento;
$settore = $partita->Tipo;
$rif_atto = $ID_partita."/".$anno_rif;

//UTENTE
$utente = new utente( $partita->Utente_ID , $c );
$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$utente->Sigla_Forma_Giuridica;
$utente_id = $utente->Comune_ID;
$indirizzo_destinatario = $utente->righe_indirizzo();
$indirizzo_completo = $indirizzo_destinatario['Completo'];
$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];

//PARAMETRI PAGAMENTO
$par_pagamento = new parametri_pagamento( $c, $settore );
$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
$iban = $par_pagamento->IBAN;	//IBAN

$autorizzazione_1 = $par_pagamento->testo_autorizzazione(1);//AUTORIZZAZIONE BOLLETTINO 1
$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
$ctrl_importo_1 = $par_pagamento->Importo_1;


//ATTO
$tot_dovuto = $atto->Totale_Dovuto;
$data_richiesta = from_mysql_date($atto->Data_Richiesta_Rate);
$num_rate = $atto->Rate_Previste;
$importo = $atto->Importi_Rate;
$scadenza = $atto->Scadenze_Rate;


//RIGA 1 CAUSALE BOLLETTINO
$riga1causale = $tipo_atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." Rif.".$rif_atto;

$NW = new numero_letterale();
for($i=0;$i<count($importo);$i++)
{
	$quinto_campo[] = $atto->quinto_campo($i+1);
	$importo_letterale[] = $NW->converti_numero_bollettino(str_replace( "," , "." , $importo[$i] ));
	$riga2causale[] = "SCADENZA PAGAMENTO RATA ".($i+1)." ENTRO IL ".from_mysql_date($scadenza[$i]);
}

//PARAMETRI TESTO
$para_testo = new testo_esito_rateizzazione(NULL);
$myId = $para_testo->CercaParametroData($c, date("Y-m-d"));
$testo = new testo_esito_rateizzazione($myId);

$Oggetto = strtoupper(stripslashes($testo->Oggetto));
$richiesta = stripslashes($testo->Richiesta);
$richiesta_negata = stripslashes($testo->Richiesta_Negata);
$richiesta_accolta = stripslashes($testo->Richiesta_Accolta);
$testo_accolta = stripslashes($testo->Testo_Richiesta_Accolta);
$firma = stripslashes($testo->Firma_Incaricato);
if($firma=="incaricato")	$firmatario = $atto->Nominativo_Gestore_Rateizzazione;
else						$firmatario = "________________";

if($utente->Paese_Nascita!="Italia" && $utente->Paese_Nascita!="ITALIA")
	$comuneNascita = "in ".$utente->Paese_Nascita;
else
	$comuneNascita = "a ".$utente->Comune_Nascita;

$identificativo_atto = $atto->Atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." Rif.".$rif_atto;

SostituisciTestoTraGraffe ($richiesta, "{DATARICHIESTA}", $data_richiesta,'B');
SostituisciTestoTraGraffe ($richiesta, "{NOMEUTENTE}", $nome_utente,'B');
SostituisciTestoTraGraffe ($richiesta, "{COMUNENASCITA}", $comuneNascita );
SostituisciTestoTraGraffe ($richiesta, "{DATANASCITA}", from_mysql_date($utente->Data_Nascita));
SostituisciTestoTraGraffe ($richiesta, "{INDIRIZZOUTENTE}", $indirizzo_senza_provincia);
SostituisciTestoTraGraffe ($richiesta, "{ATTO}", $identificativo_atto,'B');

SostituisciTestoTraGraffe ($richiesta_negata, "{NOMEGESTORE}", $atto->Nominativo_Gestore_Rateizzazione,'B');
SostituisciTestoTraGraffe ($richiesta_negata, "{POSIZIONEGESTORE}", $atto->Posizione_Gestore_Rateizzazione,'B');
SostituisciTestoTraGraffe ($richiesta_negata, "{MOTIVAZIONE}", $atto->Motivazione_Respinta_Rateizzazione,'B');

SostituisciTestoTraGraffe ($richiesta_accolta, "{NOMEGESTORE}", $atto->Nominativo_Gestore_Rateizzazione,'B');
SostituisciTestoTraGraffe ($richiesta_accolta, "{POSIZIONEGESTORE}", $atto->Posizione_Gestore_Rateizzazione,'B');
SostituisciTestoTraGraffe ($richiesta_accolta, "{NUMERORATE}", $num_rate);

/**
 ///////////////////////////////		PDF	    //////////////////////////////////
 */

$pdf = new clsPdf_("P", "mm", "A4", true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetAutoPageBreak(false);
$pdf->SetCellPadding(0);
$pdf->AddPage('P');

if($stampa_select == "PROVVISORIA")
	$pdf->stampa_provvisoria();

$pdf->SetLineWidth(0.2);
$pdf->SetMargins(7.0, 10.0, 7.0);


//////////////	CORPO Pagina 1	//////////////

$pdf->intestazione_pdf($tipo_gestore, $image_file, $intest_gestore, $intest_ufficio);
$pdf->destinatario_intestazione_pdf($utente_id, $c, $nome_utente, $ID_partita, $anno_rif, $indirizzo_destinatario, "" );
$pdf->oggetto_pdf($Oggetto, "", "");

//SOTTOSCRITTO
$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->ln(5);

$pdf->SetFont('Arial', '', 9);
$pdf->writeHTMLCell(0, 0, '', '', $richiesta."\n",0,1,false,true,'J');
$pdf->ln(5);

if($atto->Esito_Richiesta_Rateizzazione=="respinta")
{
	$pdf->writeHTMLCell(0, 0, '', '', $richiesta_negata."\n",0,1,false,true,'J');
}
else
{
	$pdf->writeHTMLCell(0, 0, '', '', $richiesta_accolta."\n",0,1,false,true,'J');

$pdf->ln(5);
for($i=0;$i<count($importo);$i++)
{
	$pdf->SetFont('Arial', 'B', 9);
	$pdf->ln(1);
	$pdf->MultiCell(5, 0, "-" , 0, 'C', 0, 0);
	$pdf->MultiCell(15, 0, "Rata ".($i+1) , 0, 'L', 0, 0);
	$pdf->MultiCell(30, 0, $importo[$i]." Euro" , 0, 'L', 0, 0);
	$pdf->MultiCell(20, 0, "Scadenza" , 0, 'L', 0, 0);
	$pdf->MultiCell(55, 0, $scadenza[$i] , 0, 'L', 0, 1);
}
$pdf->SetFont('Arial', '', 9);
$pdf->ln(5);
$pdf->MultiCell(0, 0, $testo_accolta , 0, 'L', 0, 1);

}

$pdf->ln(10);
$pdf->MultiCell(98, 0, "LUOGO E DATA" , 0, 'C', 0, 0);
$pdf->MultiCell(98, 0, "FIRMA" , 0, 'C', 0, 1);
$pdf->ln(1);
$pdf->MultiCell(98, 0, "_______________, li _________" , 0, 'C', 0, 0);
$pdf->MultiCell(98, 0, $firmatario , 0, 'C', 0, 1);



//////////////	FINE CORPO Pagina 1	//////////////

$path = crea_dir( ATTI ."/". $c . "/Documenti" );
$nome_file = "Esito_rateizzazione_".$c."_".$atto->ID_Cronologico."_".$atto->Anno_Cronologico."_".date("Y-m-d_H-i").".pdf";

if($stampa_select == "PROVVISORIA")
{
	$pdf->Output( $nome_file , 'I');
	die;
}
else if($stampa_select == "DEFINITIVA")
	$pdf->Output( $path."/".$nome_file , 'F');

$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
$comune_id = single_query($query);

$salva = new documento( null , $c );

$salva->CC = $c;
$salva->Comune_ID = $comune_id + 1;
$salva->File = $nome_file;
$salva->Utente_ID = $partita->Utente_ID;
$salva->Tipo = "Inviato";
$salva->Atto = "Esito richiesta di rateizzazione";
$salva->Data_Creazione = date("Y-m-d");
$salva->Informazioni_Aggiuntive = "";
$salva->Oggetto = "";
$salva->Contenuto = "";
$salva->Data_Stampa = date("Y-m-d");

mysql_query('BEGIN');

$control_salva = $salva->Insert( true );

if( $control_salva )
{
	$id_documento = mysql_insert_id();

	$atto->ID_Esito_Rateizzazione = $id_documento;
	$control_salva = $atto->Update($ID_Atto);

	if( $control_salva )
	{
		mysql_query('COMMIT');
		$pdf->Output( $path."/".$nome_file );
	}
	else
	{
		echo 'ERROR '.mysql_error();
		mysql_query('ROLLBACK');
	}

}
else
{
	echo 'ERROR '.mysql_error();
	mysql_query('ROLLBACK');
}
	
?>