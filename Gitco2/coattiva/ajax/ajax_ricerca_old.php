<?php
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
		
	include CLASSI . "/comuni.php";
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/290.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	include CLASSI . "/pagamenti_importati.php";
	include CLASSI . "/parametri.php";

if (!session_id()) session_start();

//alertAllGlobalVariables();
$tipo = get_var ('tipo');
$ordcoa = get_var ('ordcoa');
$comune = get_var ('comune');
$crono = get_var ('crono');
$anno = get_var ('anno');
$tiporiscossione = get_var ('tiporiscossione');
$datapagamento = get_var ('datapagamento');

$myPagImp = new pagamenti_importati(null);
$scritta = $myPagImp->TipiPagamento($tipo, "SCRITTADATIPO");

$myID = "";
$risposta = "";

if ($ordcoa == "C")  //  ricerca coattiva
{
	$queryAtto = "SELECT ID FROM atto ";
	$queryAtto .= "WHERE Atto = '" . $scritta . "' AND ";
	$queryAtto .= "CC = '" . $comune . "' AND ";
	$queryAtto .= "ID_Cronologico = '" . $crono . "' AND ";
	$queryAtto .= "Anno_Cronologico = '" . $anno . "' ";
	$resAtto = mysql_query($queryAtto);
	
	if (mysql_num_rows($resAtto) == "")  $myID = "";
	else
	{
		$rigaAtto = mysql_fetch_assoc($resAtto);
		$myID = $rigaAtto['ID'];
	}
}
else if ($ordcoa == "O")  //  ricerca ordinaria
{
	$attoCompleto = $crono . "/" . $anno;
	$myAtto = new atto(null, $comune);
	$myID = $myAtto->ultimoAttoPartita($attoCompleto, $comune, "si");
}

if ($myID != "")
{
	$myAtto = new atto($myID, $comune);
	$myPartita = new partita($myAtto->Partita_ID, $comune);
	
	$tipopag = $myPagImp->TipiPagamento($myAtto->Atto, "TIPODASCRITTA");
	//alert ($tipopag . " e " . $selected);
	
	$pagPresente = "NONPRESENTE_0";
	/*for ($i = 0; $i < count($myAtto->Pagamento); $i++)
	{
		$myPagamento = new pagamento($myAtto->Pagamento[$i]->ID, $myAtto->CC);
		if ($myPagamento->PagamentoGiaPresente() != null)
		{
			$pagPresente = "GIAPRESENTE" . "_" . $myPagamento->Rata;
			break;
		}
	}*/
	if (count($myAtto->Pagamento) > 0) $pagPresente = "GIAPRESENTE" . "_" . count($myAtto->Pagamento);

	$myParametro = new parametri_pagamento($myAtto->CC, $tiporiscossione);
	$contoTerzi = $myParametro->data_conto_terzi($datapagamento);
	
	if ($myPartita->Utente->Cognome != "")
	{
		$utente = $myPartita->Utente->Cognome . " " . $myPartita->Utente->Nome;
	}
	else 
	{
		$utente = $myPartita->Utente->Ditta;
	}
	
	$risposta = $pagPresente . "**" .
			$myAtto->ID . "**" .
			$myAtto->CC . "**" .
			$myAtto->Partita_ID . "**" .
			$myAtto->ID_Cronologico . "**" .
			$myAtto->Anno_Cronologico . "**" .
			$myAtto->Atto . "**" .
			$tipopag . "**" .
			$myAtto->Info_Cartella . "**" .
			$myAtto->Totale_Dovuto . "**" .
			$myAtto->Rate_Previste . "**" .
			count($myAtto->Pagamento) . "**" .
			$contoTerzi . "**" .
			$utente;// . "**" .
			//$myAtto->Totale_Dovuto . "**" .
			//$myAtto->Importi_Rate . "**" .
			//$myAtto->Scadenze_Rate;
}

echo $risposta;

?>