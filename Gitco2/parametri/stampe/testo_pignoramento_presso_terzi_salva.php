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

$Titolo_Oggetto = get_var('titolo_oggetto');
$Sottotitolo_Oggetto = get_var('sottotitolo_oggetto');
$Ufficiale_Responsabile = get_var('ufficiale_responsabile');
$Abilitazione = get_var('abilitazione');
$Premesso = get_var('premesso');
$Premesso_Testo = get_var('premesso_testo');
$Atti_Notificati = get_var('atti_notificati');
$Terzo = get_var('terzo');
$Datore_Lavoro = get_var('datore_lavoro');
$Banca = get_var('banca');
$Ordine_Pagamento = get_var('ordine_pagamento');
$Ordina = get_var('ordina');
$Ordina_Testo = get_var('terzo');
$Termini_Pagamento = get_var('termini_pagamento');
$Estremi_Pagamento = get_var('estremi_pagamento');
$Ufficiale_Datore_Lavoro = get_var('ufficiale_datore_lavoro');
$Ufficiale_Banca = get_var('ufficiale_banca');
$Sottoposto_Pignoramento = get_var('sottoposto_pignoramento');
$Sottoposto_Pignoramento_Datore_Lavoro = get_var('sottoposto_pignoramento_datore_lavoro');
$Sottoposto_Pignoramento_Banca = get_var('sottoposto_pignoramento_banca');
$Intima = get_var('intima');
$Intima_Testo = get_var('intima_testo');
$Art56 = get_var('art56');
$Art49 = get_var('art49');
$Pagamento_Effettuato = get_var('pagamento_effettuato');
$Intestazione_Firma = get_var('intestazione_firma');
$Firma = get_var('firma');
$Luogo = get_var('luogo');
$Relazione_Notifica = get_var('relazione_notifica');
$Relazione_Pignorato = get_var('relazione_pignorato');
$Relazione_Terzo = get_var('relazione_terzo');

if ($invia == "Salva")
{

	$myParametroAtto = new testo_pignoramento_presso_terzi(NULL); 
	
	$myParametroAtto->ID = NULL;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Titolo_Oggetto = $Titolo_Oggetto;
	$myParametroAtto->Sottotitolo_Oggetto = $Sottotitolo_Oggetto;
	$myParametroAtto->Ufficiale_Responsabile = $Ufficiale_Responsabile;
	$myParametroAtto->Abilitazione = $Abilitazione;
	$myParametroAtto->Premesso = $Premesso;
	$myParametroAtto->Premesso_Testo = $Premesso_Testo;
	$myParametroAtto->Atti_Notificati = $Atti_Notificati;
	$myParametroAtto->Terzo = $Terzo;
	$myParametroAtto->Datore_Lavoro = $Datore_Lavoro;
	$myParametroAtto->Banca = $Banca;
	$myParametroAtto->Ordine_Pagamento = $Ordine_Pagamento;
	$myParametroAtto->Ordina = $Ordina;
	$myParametroAtto->Ordina_Testo = $Ordina_Testo;
	$myParametroAtto->Termini_Pagamento = $Termini_Pagamento;
	$myParametroAtto->Estremi_Pagamento = $Estremi_Pagamento;
	$myParametroAtto->Ufficiale_Datore_Lavoro = $Ufficiale_Datore_Lavoro;
	$myParametroAtto->Ufficiale_Banca = $Ufficiale_Banca;
	$myParametroAtto->Sottoposto_Pignoramento = $Sottoposto_Pignoramento;
	$myParametroAtto->Sottoposto_Pignoramento_Datore_Lavoro = $Sottoposto_Pignoramento_Datore_Lavoro;
	$myParametroAtto->Sottoposto_Pignoramento_Banca = $Sottoposto_Pignoramento_Banca;
	$myParametroAtto->Intima = $Intima;
	$myParametroAtto->Intima_Testo = $Intima_Testo;
	$myParametroAtto->Art56 = $Art56;
	$myParametroAtto->Art49 = $Art49;
	$myParametroAtto->Pagamento_Effettuato = $Pagamento_Effettuato;
	$myParametroAtto->Intestazione_Firma = $Intestazione_Firma;
	$myParametroAtto->Firma = $Firma;
	$myParametroAtto->Luogo = $Luogo;
	$myParametroAtto->Relazione_Notifica = $Relazione_Notifica;
	$myParametroAtto->Relazione_Pignorato = $Relazione_Pignorato;
	$myParametroAtto->Relazione_Terzo = $Relazione_Terzo;
	
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