<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if ($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');

$invia = get_var('invia_submit');

$oggettoPreavviso = get_var('oggettoPreavviso');
$primoTesto = get_var('primoTesto');
$secondoTesto = get_var('secondoTesto');
$sommaTesto = get_var('sommaTesto');
$terzoTesto = get_var('terzoTesto');
$quartoTesto = get_var('quartoTesto');
$salutiTesto = get_var('salutiTesto');
$ufficialeRiscossione = get_var('ufficialeRiscossione');
$nomeUfficialeRisc = get_var('nomeUfficialeRisc');
$ufficialeRiscossione2 = get_var('ufficialeRiscossione2');
$nomeUfficialeRisc2 = get_var('nomeUfficialeRisc2');
$modalitaFirma = get_var('modalitaFirma');

$info_1_Titolo = get_var('info_1_Titolo');
$info_1_Testo = get_var('info_1_Testo');

$CDS_Titolo = get_var('CDS_Titolo');
$CDS_Testo_1 = get_var('CDS_Testo_1');
$CDS_Testo_2 = get_var('CDS_Testo_2');
$CDS_Testo_3 = get_var('CDS_Testo_3');

$Tributo_Titolo = get_var('Tributo_Titolo');
$Tributo_Testo = get_var('Tributo_Testo');

$info_2_Titolo = get_var('info_2_Titolo');
$info_2_Testo = get_var('info_2_Testo');
$info_3_Titolo = get_var('info_3_Titolo');
$info_3_Testo = get_var('info_3_Testo');

$avviso_Titolo = get_var('avviso_Titolo');
$esito_1_Titolo = get_var('esito_1_Titolo');
$esito_1_Testo = get_var('esito_1_Testo');
$caso_A_Testo = get_var('caso_A_Testo');
$caso_B_Testo = get_var('caso_B_Testo');
$caso_C_Testo = get_var('caso_C_Testo');
$caso_D_Testo = get_var('caso_D_Testo');
$esito_2_Titolo = get_var('esito_2_Titolo');
$esito_2_Testo = get_var('esito_2_Testo');
$esito_3_Titolo = get_var('esito_3_Titolo');
$esito_3_Testo = get_var('esito_3_Testo');
$esito_4_Titolo = get_var('esito_4_Titolo');
$esito_4_Testo = get_var('esito_4_Testo');



if ($invia == "Salva")
{
	$myParametroPreavviso = new parametri_testo_preavviso_ingiunzione(NULL); 
	
	$myParametroPreavviso->ID = NULL;
	$myParametroPreavviso->CC = $c;
	$myParametroPreavviso->Data_Creazione_Parametri = date("Y-m-d");
	$myParametroPreavviso->Oggetto_Preavviso_Ingiunzione = $oggettoPreavviso;
	$myParametroPreavviso->Primo_Testo = $primoTesto;
	$myParametroPreavviso->Secondo_Testo = $secondoTesto;
	$myParametroPreavviso->Intro_Somma_Testo = $sommaTesto;
	$myParametroPreavviso->Terzo_Testo = $terzoTesto;
	$myParametroPreavviso->Quarto_Testo = $quartoTesto;
	$myParametroPreavviso->Saluti_Testo = $salutiTesto;
	$myParametroPreavviso->Ufficiale_Riscossione = $ufficialeRiscossione;
	$myParametroPreavviso->Nome_Ufficiale_Riscossione = $nomeUfficialeRisc;
	$myParametroPreavviso->Ufficiale_Riscossione_2 = $ufficialeRiscossione2;
	$myParametroPreavviso->Nome_Ufficiale_Riscossione_2 = $nomeUfficialeRisc2;
	$myParametroPreavviso->Stampa_Firma = $modalitaFirma;
	
	$myParametroPreavviso->Info_1_Titolo = $info_1_Titolo;
	$myParametroPreavviso->Info_1_Testo = $info_1_Testo;
	
	$myParametroPreavviso->CDS_Titolo = $CDS_Titolo;
	$myParametroPreavviso->CDS_Testo_1 = $CDS_Testo_1;
	$myParametroPreavviso->CDS_Testo_2 = $CDS_Testo_2;
	$myParametroPreavviso->CDS_Testo_3 = $CDS_Testo_3;
	
	$myParametroPreavviso->Tributo_Titolo = $Tributo_Titolo;
	$myParametroPreavviso->Tributo_Testo = $Tributo_Testo;	
	
	$myParametroPreavviso->Info_2_Titolo = $info_2_Titolo;
	$myParametroPreavviso->Info_2_Testo = $info_2_Testo;
	$myParametroPreavviso->Info_3_Titolo = $info_3_Titolo;
	$myParametroPreavviso->Info_3_Testo = $info_3_Testo;

	$myParametroPreavviso->Avviso_Titolo = $avviso_Titolo;
	$myParametroPreavviso->Esito_1_Titolo = $esito_1_Titolo;
	$myParametroPreavviso->Esito_1_Testo = $esito_1_Testo;
	$myParametroPreavviso->Caso_A_Testo = $caso_A_Testo;
	$myParametroPreavviso->Caso_B_Testo = $caso_B_Testo;
	$myParametroPreavviso->Caso_C_Testo = $caso_C_Testo;
	$myParametroPreavviso->Caso_D_Testo = $caso_D_Testo;
	$myParametroPreavviso->Esito_2_Titolo = $esito_2_Titolo;
	$myParametroPreavviso->Esito_2_Testo = $esito_2_Testo;
	$myParametroPreavviso->Esito_3_Titolo = $esito_3_Titolo;
	$myParametroPreavviso->Esito_3_Testo = $esito_3_Testo;
	$myParametroPreavviso->Esito_4_Titolo = $esito_4_Titolo;
	$myParametroPreavviso->Esito_4_Testo = $esito_4_Testo;

	mysql_query('BEGIN');
		
	$risultato = $myParametroPreavviso->InsertOrUpdateParametroPreavviso(true);
	
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