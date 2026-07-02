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
$Intestazione_Pignoramento = get_var('intestazione_pignoramento');
$Ufficiale_Responsabile = get_var('ufficiale_responsabile');

$Legale_Rappresentante_Comune = get_var('rappresentante_comune');
$Legale_Rappresentante_Concessionario = get_var('rappresentante_concessionario');

$Premesso = get_var('premesso');
$Premesso_Testo = get_var('premesso_testo');
$Atti_Notificati = get_var('atti_notificati');

$Informazioni = get_var('informazioni');
$Informazioni_Testo = get_var('informazioni_testo');
$Informo = get_var('informo');
$Conto_Corrente = get_var('conto_corrente');
$Informo_Testo = get_var('informo_testo');
$Informo_Testo_2 = get_var('informo_testo_2');
$Informo_Testo_3 = get_var('informo_testo_3');
$Informo_Testo_4 = get_var('informo_testo_4');

$Considerato = get_var('considerato');
$Ingiunzione_Fiscale = get_var('ingiunzione_fiscale');
$Legislatore = get_var('legislatore');
$Dati_Veicolo = get_var('dati_veicolo');

$Premesso_Considerato = get_var('premesso_considerato');
$Opposizione_Testo = get_var('opposizione_testo');;
$Autotutela_Testo = get_var('autotutela_testo');
$Beni_Strumentali_Testo = get_var('beni_strumentali_testo');
$Valutazione_Strumentale = get_var('valutazione_strumentale');
$Recupero_Somme = get_var('recupero_somme');
$Notifica_Istituto = get_var('notifica_istituto');

$Luogo = get_var('luogo');


$Ufficiale_Pignoramento = get_var('ufficiale_pignoramento');

$Assoggetto_Pignoramento = get_var('assoggetto_pignoramento');
$Assoggetto_Testo = get_var('assoggetto_testo');

$Ingiungo = get_var('ingiungo');
$Ingiungo_Testo = get_var('ingiungo_testo');

$Invito = get_var('invito');
$Invito_Testo = get_var('invito_testo');

$Avverto = get_var('avverto');
$Avverto_Testo = get_var('avverto_testo');

$Intimo = get_var('intimo');
$Intimo_Testo = get_var('intimo_testo');

$Comunico = get_var('comunico');
$Comunico_Testo_1 = get_var('comunico_testo_1');
$Comunico_Testo_2 = get_var('comunico_testo_2');

$IntestazioneUffGiudiziario = get_var('IntestazioneUffGiudiziario');
$SottoIntestazioneUffGiudiziario = get_var('SottoIntestazioneUffGiudiziario');
$IntestazioneUffRiscossione = get_var('IntestazioneUffRiscossione');
$SottoIntestazioneUffRiscossione = get_var('SottoIntestazioneUffRiscossione');
$RelataNotifica = get_var('RelataNotifica');


if ($invia == "Salva")
{

	$myParametroAtto = new testo_pignoramento_veicolo(NULL); 
	
	$myParametroAtto->ID = NULL;
	$myParametroAtto->CC = $c;
	$myParametroAtto->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroAtto->Titolo_Oggetto = $Titolo_Oggetto;
	$myParametroAtto->Sottotitolo_Oggetto = $Sottotitolo_Oggetto;
	$myParametroAtto->Intestazione_Pignoramento = $Intestazione_Pignoramento;
	
	$myParametroAtto->Ufficiale_Responsabile = $Ufficiale_Responsabile;
	
	$myParametroAtto->Legale_Rappresentante_Comune = $Legale_Rappresentante_Comune;
	$myParametroAtto->Legale_Rappresentante_Concessionario = $Legale_Rappresentante_Concessionario;
	
	$myParametroAtto->Premesso = $Premesso;
	$myParametroAtto->Atti_Notificati = $Atti_Notificati;
	$myParametroAtto->Premesso_Testo = $Premesso_Testo;
	
	$myParametroAtto->Informazioni = $Informazioni;
	$myParametroAtto->Informazioni_Testo = $Informazioni_Testo;
	$myParametroAtto->Informo = $Informo;
	$myParametroAtto->Conto_Corrente = $Conto_Corrente;
	$myParametroAtto->Informo_Testo = $Informo_Testo;
	$myParametroAtto->Informo_Testo_2 = $Informo_Testo_2;
	$myParametroAtto->Informo_Testo_3 = $Informo_Testo_3;
	$myParametroAtto->Informo_Testo_4 = $Informo_Testo_4;
		
	$myParametroAtto->Considerato = $Considerato;
	$myParametroAtto->Ingiunzione_Fiscale = $Ingiunzione_Fiscale;
	$myParametroAtto->Legislatore = $Legislatore;
	$myParametroAtto->Dati_Veicolo = $Dati_Veicolo;
	
	$myParametroAtto->Premesso_Considerato = $Premesso_Considerato;
	$myParametroAtto->Opposizione_Testo = $Opposizione_Testo;
	$myParametroAtto->Autotutela_Testo = $Autotutela_Testo;
	$myParametroAtto->Beni_Strumentali_Testo = $Beni_Strumentali_Testo;
	$myParametroAtto->Valutazione_Strumentale = $Valutazione_Strumentale;
	$myParametroAtto->Recupero_Somme = $Recupero_Somme;
	$myParametroAtto->Notifica_Istituto = $Notifica_Istituto;
	
	$myParametroAtto->Luogo = $Luogo;
	
	
	$myParametroAtto->Ufficiale_Pignoramento = $Ufficiale_Pignoramento;
	
	$myParametroAtto->Assoggetto_Pignoramento = $Assoggetto_Pignoramento;
	$myParametroAtto->Assoggetto_Testo = $Assoggetto_Testo;
	
	$myParametroAtto->Ingiungo = $Ingiungo;
	$myParametroAtto->Ingiungo_Testo = $Ingiungo_Testo;
	
	$myParametroAtto->Invito = $Invito;
	$myParametroAtto->Invito_Testo = $Invito_Testo;
		
	$myParametroAtto->Avverto = $Avverto;
	$myParametroAtto->Avverto_Testo = $Avverto_Testo;
	
	$myParametroAtto->Intimo = $Intimo;
	$myParametroAtto->Intimo_Testo = $Intimo_Testo;
	
	$myParametroAtto->Comunico = $Comunico;
	$myParametroAtto->Comunico_Testo_1 = $Comunico_Testo_1;
	$myParametroAtto->Comunico_Testo_2 = $Comunico_Testo_2;
	
	$myParametroAtto->Intestazione_Relata_Ufficiale_Giudiziario = $IntestazioneUffGiudiziario;
	$myParametroAtto->SottoIntestazione_Relata_Ufficiale_Giudiziario = $SottoIntestazioneUffGiudiziario;
	
	$myParametroAtto->Intestazione_Relata_Ufficiale_Riscossione = $IntestazioneUffRiscossione;
	$myParametroAtto->SottoIntestazione_Relata_Ufficiale_Riscossione = $SottoIntestazioneUffRiscossione;
	
	$myParametroAtto->Relata_Ufficiale = $RelataNotifica;
	
	mysql_query('BEGIN');	
	
	$risultato = $myParametroAtto->InsertOrUpdatesParametroAtto();
	
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
else 
	echo "ambaraba";

?>