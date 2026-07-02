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
$aggiunta_pigno = "";
//ATTO
if($ID_Atto!=null)
{
	$atto = new atto($ID_Atto, $c);
	switch($atto->Atto)
	{
		case "Avviso di intimazione ad adempiere": $tipo_atto = "Avv. Intimazione"; break;
		default: $tipo_atto = $atto->Atto;		break;
	}
	$tot_dovuto = $atto->Totale_Dovuto;
}
else 
{
	$ID_Atto = get_var('ID_Pigno');
	$atto = new pignoramento($ID_Atto, $c);
	$atto->gestione_totali();
	$totali_array = $atto->Totali_Array;
	switch($atto->Tipo_Totale_Rate)
	{
		case "1":	$tot_dovuto = conv_num($totali_array[1]); break;
		case "2":	$tot_dovuto = conv_num($totali_array[2]); break;
		case "3":	$tot_dovuto = conv_num($totali_array[3]); break;	
	}
	
	$aggiunta_pigno = " pignoramento";
	switch($atto->Tipo)
	{
		case "veicolo": $tipo_atto = "Pignoramento beni mobili registrati"; break;
		case "terzi": 	
			switch($atto->Tipo_Terzi)
			{
				case "lavoro": $tipo_atto = "Pignoramento presso datore di lavoro"; break;
				case "banca":  $tipo_atto = "Pignoramento presso banca"; break;
			}
				break;
			
		default: $tipo_atto = $atto->Tipo;		break;
	}
}

$partita_id = $atto->Partita_ID;
$crono = $atto->ID_Cronologico;
$anno_crono = $atto->Anno_Cronologico;




$num_rate = $atto->Rate_Previste;
$importo = $atto->Importi_Rate;
$scadenza = $atto->Scadenze_Rate;

//PARTITA
$partita = new partita($partita_id, $c );
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
$par_pagamento = new parametri_pagamento( $c, $settore);
$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
$iban = $par_pagamento->IBAN;	//IBAN

$autorizzazione_1 = $par_pagamento->testo_autorizzazione(1);//AUTORIZZAZIONE BOLLETTINO 1
$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
$ctrl_importo_1 = $par_pagamento->Importo_1;

//RIGA 1 CAUSALE BOLLETTINO
$riga1causale = $tipo_atto." n.".$crono." del ".$anno_crono." Rif.".$rif_atto;

$NW = new numero_letterale();
for($i=0;$i<count($importo);$i++)
{
	$quinto_campo[] = $atto->quinto_campo($i+1);
	
	$importo_letterale[] = $NW->converti_numero_bollettino(str_replace( "," , "." , $importo[$i] ));
	$riga2causale[] = "SCADENZA PAGAMENTO RATA ".($i+1)." ENTRO IL ".from_mysql_date($scadenza[$i]);
}

//PARAMETRI TESTO
$para_testo = new testo_richiesta_rateizzazione(NULL);
$myId = $para_testo->CercaParametroData($c, date("Y-m-d"));
$testo = new testo_richiesta_rateizzazione($myId);

$Oggetto = strtoupper(stripslashes($testo->Oggetto));
$sottoscritto = stripslashes($testo->Sottoscritto);
$testoAtto = stripslashes($testo->Atto_Testo);
$chiedo = stripslashes($testo->Chiedo);
$chiedoTesto = stripslashes($testo->Chiedo_Testo);
$condizioni_disagiate = stripslashes($testo->Condizioni_Disagiate);
$condizione_1 = stripslashes($testo->Condizione_1);
$condizione_2 = stripslashes($testo->Condizione_2);
$condizione_3 = stripslashes($testo->Condizione_3);
$condizione_4 = stripslashes($testo->Condizione_4);
$condizione_5 = stripslashes($testo->Condizione_5);

if($utente->Paese_Nascita!="Italia" && $utente->Paese_Nascita!="ITALIA")
	$comuneNascita = "in ".$utente->Paese_Nascita;
else
	$comuneNascita = "a ".$utente->Comune_Nascita;
	

SostituisciTestoTraGraffe ($sottoscritto, "{NOMEUTENTE}", $nome_utente);
SostituisciTestoTraGraffe ($sottoscritto, "{COMUNENASCITA}", $comuneNascita );
SostituisciTestoTraGraffe ($sottoscritto, "{DATANASCITA}", from_mysql_date($utente->Data_Nascita));
SostituisciTestoTraGraffe ($sottoscritto, "{INDIRIZZOUTENTE}", $indirizzo_senza_provincia);

$identificativo_atto = $tipo_atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." Rif.".$rif_atto;

SostituisciTestoTraGraffe ($testoAtto, "{ATTO}", $identificativo_atto);
SostituisciTestoTraGraffe ($testoAtto, "{INFOCARTELLA}", $partita->Tributo[0]->Info_Cartella );

SostituisciTestoTraGraffe ($chiedoTesto, "{DOVUTO}", conv_num(number_format($tot_dovuto,2))." Euro");
SostituisciTestoTraGraffe ($chiedoTesto, "{NUMERORATE}", $atto->Rate_Previste );


/**
 ///////////////////////////////		PDF	    //////////////////////////////////
 */

$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
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
$pdf->destinatario_intestazione_pdf($utente_id, $c, strtoupper($gestore->Denominazione), $ID_partita, $anno_rif, $indirizzo_gestore, "" );
$pdf->oggetto_pdf($Oggetto, "", "");

//SOTTOSCRITTO
$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->ln(5);

$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 0, $sottoscritto."\n" , 0, 'J', 0, 1);
$pdf->ln(5);
$pdf->MultiCell(0, 0, $testoAtto."\n" , 0, 'J', 0, 1);
$pdf->ln(5);
$pdf->SetFont('Arial', 'B', 9);
$pdf->MultiCell(0, 0, $chiedo , 0, 'C', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 0, $chiedoTesto."\n" , 0, 'J', 0, 1);
$pdf->ln(10);
$pdf->MultiCell(98, 0, "LUOGO E DATA" , 0, 'C', 0, 0);
$pdf->MultiCell(98, 0, "IN FEDE" , 0, 'C', 0, 1);
$pdf->ln(2);
$pdf->MultiCell(98, 0, "_______________, li _________" , 0, 'C', 0, 0);
$pdf->MultiCell(98, 0, "____________________" , 0, 'C', 0, 1);
$pdf->ln(10);
$pdf->MultiCell(0, 0, $condizioni_disagiate."\n" , 0, 'J', 0, 1);
$pdf->ln(2);
$pdf->MultiCell(0, 0, "1) ".$condizione_1 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "2) ".$condizione_2 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "3) ".$condizione_3 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "4) ".$condizione_4 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "5) ".$condizione_5 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "6) ________________________ " , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "7) ________________________ " , 0, 'L', 0, 1);


//////////////	FINE CORPO Pagina 1	//////////////

$path = crea_dir( ATTI ."/". $c . "/Documenti" );
$nome_file = "Richiesta_rateizzazione_".$c."_".$crono."_".$anno_crono."_".date("Y-m-d_H-i").".pdf";

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
$salva->Tipo = "Ricevuto";
$salva->Atto = "Richiesta di rateizzazione".$aggiunta_pigno;
$salva->Data_Creazione = date("Y-m-d");
$salva->Informazioni_Aggiuntive = "Richiesta di rateizzazione ".$tipo_atto;
$salva->Oggetto = "";
$salva->Contenuto = "";
$salva->Data_Stampa = date("Y-m-d");

mysql_query('BEGIN');

$control_salva = $salva->Insert( true );

if( $control_salva )
{
	$id_documento = mysql_insert_id();
	
	$atto->ID_Richiesta_Rateizzazione = $id_documento;
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