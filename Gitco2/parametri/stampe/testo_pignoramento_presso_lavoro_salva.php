<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');

$invia = get_var('invia_submit');

//TABELLA 1

$Titolo_Oggetto = get_var('titolo_oggetto');
$Sottotitolo_Oggetto = get_var('sottotitolo_oggetto');
$Ufficiale_Responsabile = get_var('ufficiale_responsabile');
$Abilitazione = get_var('abilitazione');

$Premesso = get_var('premesso');
$Atti_Notificati = get_var('atti_notificati');

$Premesso_Testo = get_var('premesso_testo');
$Informazioni = get_var('informazioni');
$Informazioni_Testo = get_var('informazioni_testo');

$Modalita_Pagamento = get_var('modalita_pagamento');
$Modalita_Pagamento_Testo = get_var('modalita_pagamento_testo');

$Visto = get_var('visto');
$Ingiunzione_Fiscale = get_var('ingiunzione_fiscale');
$Legislatore = get_var('legislatore');

$Considerato = get_var('considerato');

$Terzo = get_var('terzo');
$Somme_Dovute = get_var('somme_dovute');
$Ordine_Pagamento = get_var('ordine_pagamento');

$Opposizione = get_var('opposizione');
$Opposizione_Testo = get_var('opposizione_testo');
$Autotutela = get_var('autotutela');
$Autotutela_Testo = get_var('autotutela_testo');
$Luogo = get_var('luogo');
$Intestazione_Firma_Sinistra = get_var('intestazione_firma_sinistra');
$Firma_Sinistra = get_var('firma_sinistra');
$Intestazione_Firma_Destra = get_var('intestazione_firma_destra');
$Firma_Destra = get_var('firma_destra');

//TABELLA 2

$Ufficiale_Pignoramento = get_var('ufficiale_pignoramento');
$Assoggetto_Pignoramento = get_var('assoggetto_pignoramento');
$Assoggetto_Pignoramento_Testo = get_var('assoggetto_pignoramento_testo');
$Ordina = get_var('ordina');
$Ordina_Testo = get_var('ordina_testo');

$Informo = get_var('informo');
$Informo_Testo = get_var('informo_testo');
$Informo_Notifica = get_var('informo_notifica');
$Intimo = get_var('intimo');
$Intimo_Testo = get_var('intimo_testo');
$Informo_2 = get_var('informo_2');
$Informo_Testo_2 = get_var('informo_testo_2');
$Invito = get_var('invito');
$Invito_Testo = get_var('invito_testo');
$Notifica_Pignoramento = get_var('notifica_pignoramento');

$Intestazione_Relata_Ufficiale_Giudiziario = get_var('intestazione_relata_uff_giudiziario');
$Sottointestazione_Relata_Ufficiale_Giudiziario = get_var('sottointestazione_relata_uff_giudiziario');

$Intestazione_Relata_Ufficiale_Riscossione = get_var('intestazione_relata_uff_riscossione');
$Sottointestazione_Relata_Ufficiale_Riscossione = get_var('sottointestazione_relata_uff_riscossione');

$Relata_Notifica = get_var('relata_notifica');
$Relata_Debitore = get_var('relata_debitore');
$Relata_Terzo = get_var('relata_terzo');

if ($invia == "Salva")
{

	$myParametroAtto = new testo_pignoramento_presso_lavoro(NULL); 
	
	//TABELLA 1
	
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	
	$myParametroAtto->Titolo_Oggetto = $Titolo_Oggetto;
	$myParametroAtto->Sottotitolo_Oggetto = $Sottotitolo_Oggetto;
	$myParametroAtto->Ufficiale_Responsabile = $Ufficiale_Responsabile;
	$myParametroAtto->Abilitazione = $Abilitazione;
	
	$myParametroAtto->Premesso = $Premesso;
	$myParametroAtto->Atti_Notificati = $Atti_Notificati;
	$myParametroAtto->Premesso_Testo = $Premesso_Testo;	
	
	$myParametroAtto->Modalita_Pagamento = $Modalita_Pagamento;
	$myParametroAtto->Modalita_Pagamento_Testo = $Modalita_Pagamento_Testo;
	
	$myParametroAtto->Informazioni = $Informazioni;
	$myParametroAtto->Informazioni_Testo = $Informazioni_Testo;
	
	$myParametroAtto->Visto = $Visto;
	$myParametroAtto->Ingiunzione_Fiscale = $Ingiunzione_Fiscale;
	$myParametroAtto->Legislatore = $Legislatore;
	
	$myParametroAtto->Considerato = $Considerato;
	$myParametroAtto->Terzo = $Terzo;
	$myParametroAtto->Somme_Dovute = $Somme_Dovute;
	$myParametroAtto->Ordine_Pagamento = $Ordine_Pagamento;
	
	$myParametroAtto->Opposizione = $Opposizione;
	$myParametroAtto->Opposizione_Testo = $Opposizione_Testo;
	$myParametroAtto->Autotutela = $Autotutela;
	$myParametroAtto->Autotutela_Testo = $Autotutela_Testo;
	$myParametroAtto->Luogo = $Luogo;
	$myParametroAtto->Intestazione_Firma_Sinistra = $Intestazione_Firma_Sinistra;
	$myParametroAtto->Firma_Sinistra = $Firma_Sinistra;
	$myParametroAtto->Intestazione_Firma_Destra = $Intestazione_Firma_Destra;
	$myParametroAtto->Firma_Destra = $Firma_Destra;
	
	//TABELLA 2
		
	$myParametroAtto->Ufficiale_Pignoramento = $Ufficiale_Pignoramento;
	$myParametroAtto->Assoggetto_Pignoramento = $Assoggetto_Pignoramento;
	$myParametroAtto->Assoggetto_Pignoramento_Testo = $Assoggetto_Pignoramento_Testo;
	
	$myParametroAtto->Ordina = $Ordina;
	$myParametroAtto->Ordina_Testo = $Ordina_Testo;
	
	$myParametroAtto->Informo = $Informo;
	$myParametroAtto->Informo_Testo = $Informo_Testo;
	$myParametroAtto->Informo_Notifica = $Informo_Notifica;
	$myParametroAtto->Intimo = $Intimo;
	$myParametroAtto->Intimo_Testo = $Intimo_Testo;
	$myParametroAtto->Informo_2 = $Informo_2;
	$myParametroAtto->Informo_Testo_2 = $Informo_Testo_2;
	$myParametroAtto->Invito = $Invito;
	$myParametroAtto->Invito_Testo = $Invito_Testo;
	$myParametroAtto->Notifica_Pignoramento = $Notifica_Pignoramento;
	
	$myParametroAtto->Intestazione_Relata_Ufficiale_Giudiziario = $Intestazione_Relata_Ufficiale_Giudiziario;
	$myParametroAtto->Sottointestazione_Relata_Ufficiale_Giudiziario = $Sottointestazione_Relata_Ufficiale_Giudiziario;
	
	$myParametroAtto->Intestazione_Relata_Ufficiale_Riscossione = $Intestazione_Relata_Ufficiale_Riscossione;
	$myParametroAtto->Sottointestazione_Relata_Ufficiale_Riscossione = $Sottointestazione_Relata_Ufficiale_Riscossione;
	
	$myParametroAtto->Relata_Notifica = $Relata_Notifica;
	$myParametroAtto->Relata_Debitore = $Relata_Debitore;
	$myParametroAtto->Relata_Terzo = $Relata_Terzo;
	
	mysql_query('BEGIN');
	
	
	$risultato = $myParametroAtto->InsertOrUpdatesParametroAtto(true);
	
	if ($risultato)
	{
		mysql_query('COMMIT');
		echo "SAVED";
	}
	else 
	{
		echo "ERROR ".mysql_error();
		
		mysql_query('ROLLBACK');
		
		
	}
}
else echo "ambaraba";
?>