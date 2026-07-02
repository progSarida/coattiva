<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/comuni.php";
include CLASSI . "/fatture.php";
include TCPDF . "/tcpdf.php";
include TCPDF . "/fpdi.php";

if (!session_id()) session_start();

if ($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

/*if ($_SESSION['CC_User'] == "***+")
{
	echoAllGlobalVariables();
	alertAllGlobalVariables();
	//return;
}*/



$aTestiCDS = array (
	"Corrispettivo per locazione apparecchiatura elettronica di tipo fisso",
	"Corrispettivo per locazione apparecchiatura elettronica di tipo mobile",
	"Corrispettivo per gestione delle violazioni a Codice della strada",
	"Corrispettivo per gestione e riscossione delle violazioni a Codice della strada"
);
$aSocieta = array("","SARIDA","STC");

$questaPagina = "fatture.php";

$c = get_var('c');
$a = get_var('a');

$autorizzazione = get_var('aut_tipo');

$myPercorsoFatture = crea_dir($PathCompletoFatture);
$myPercorsoCortoFatture = $PathFatture;



echo $myPercorsoFatture ." <br>".$myPercorsoCortoFatture;
//$comune = new ente_gestito($c);

//$nome_comune = ($comune->Nome==NULL?"":$comune->Nome." [".$c."]");
$nome_user = "Operatore: " . $_SESSION['username'];

$limImposta = 77.47;

if(isset($_REQUEST['id'])){
$id = (is_numeric($_REQUEST['id'])) ? $_REQUEST['id'] : 1;
}else{
	$id = 1;
}
if($id==1){
	$primaRiga = "Servizio Accertamento Riscossione Imposte Diritti Accessori S.R.L.";
	$secondaRiga = "Sede Legale: Via Mons. Vattuone 9/6 - 16039 Sestri Levante (GE)";
	$terzaRiga = "Tel. 0185/450860 - Fax 0185/457447";
	$quartaRiga = "Registro imprese CCIAA Genova - C.F. - P.I. 01338160995";
	$quintaRiga = "R.E.A. 401963 - Cap. Soc. I.V. Euro 5.100.000,00";

	$ibanBGSG = "IT74G0690632230000000062107";
	$nomeBancaBGSG = "Banca Regionale Europea";
	$bancaBGSG_1 = "Banca Appoggio: " . $nomeBancaBGSG;
	$bancaBGSG_2 = "Ag. Di: Sestri Levante";
	$bancaBGSG_3 = "IT 74 CIN G ABI 06906 CAB 32230 C/C 62107";
	$bancaBGSG_4 = "IBAN: " . $ibanBGSG;
	$bicBGSG = "BREUITM1";

	$ibanBPI = "IT71I0503432230000000105796";
	$nomeBancaBPI = "Banco Popolare";
	$bancaBPI_1 = "Banca Appoggio: " . $nomeBancaBPI;
	$bancaBPI_2 = "Ag. Di: Sestri Levante";
	$bancaBPI_3 = "IT 71 CIN I ABI 05034 CAB 32230 C/C 105796";
	$bancaBPI_4 = "IBAN: " . $ibanBPI;
	$bicBPI = "BAPPIT21R90";
}else{
	$primaRiga = "STC S.r.l. - Sede Amministrativa e Domicilio Fiscale (invio e conservazione documenti)";
	$secondaRiga = "Via Costaguta n° 43/3 - 16043 Chiavari (GE) - Tel. 0185 325024 Fax 0185 325025";
	$terzaRiga = "e-mail: st-control@libero.it - C.C.I.A.A. - Cod. Fisc-. P.IVA 01704070992 - Cap. Soc. Euro 10.000,00";
	$quartaRiga = "Sede legale: via Pantalini n° 7 - 29100 - Piacenza";
	$quintaRiga = "";

	$ibanSTC = "IT18S0503432230000000375578";
	$nomeBancaSTC = "Banca Popolare società cooperativa";
	$bancaSTC_1 = "Banca Appoggio: ".$nomeBancaSTC;
	$bancaSTC_2 = "Ag. Di: Sestri Levante";
	$bancaSTC_3 = "IT 18 CIN S ABI 05034 CAB 32230 C/C 375578";
	$bancaSTC_4 = "IBAN: " . $ibanSTC;
	$bicSTC = "";
}


$testoQuadrato1 = "Imposta di Bollo";

$iban = $bic = $nomeBanca = "";
$linkPdf = $linkXml = "";

$testoSost = "Imposta di bollo assolta in modo virtuale.";
$scrittaIva = "Iva da versare a cura del cessionario o committente ai sensi dell'art.17 - ter del D.P.R. Nr 633/1972";

$societa = $aSocieta[$id];
$tiporiscossione = get_var('tiporiscossione');
$sceltacomune = get_var('sceltacomune');

$daticig = get_var('daticig');
$tipocig = get_var('tipocig');

$disableCorreggi = "";
$scrittaInviato = "";

$importo = get_var('importo');
$spese = get_var('spese');
$ordinario = get_var('ordinario');
$temporaneo = get_var('temporaneo');
$affissioni = get_var('affissioni');
$totaleimponibile = get_var('totaleimponibile');
$percentualeiva = get_var('percentualeiva');
$iva = get_var('iva');
$rimborsi = get_var('rimborsi');
$impostabollo = get_var('impostabollo');
$totalefattura = get_var('totalefattura');
$totaleadoversi = get_var('totaleadoversi');

$numerofattura = get_var('numerofattura');
$datafattura = get_var('datafattura');
$tipofattura = get_var('tipofattura');
$annofattura = get_var('annofattura');
$annobilanciofattura = get_var('annobilanciofattura');
$annocompetenzafattura = get_var('annocompetenzafattura');

$spettabile = get_var('spettabile');
$testoiva = get_var('testoiva');
$tipobanca = get_var('tipobanca');

$descrizionefattura = get_var('descrizionefattura');

$descrizione4fattura = get_var('descrizione4fattura');
$descrizionelibera = get_var('descrizionelibera');

//echo $descrizionefattura." ".$descrizione4fattura." ".$descrizionelibera;DIE;

$descrizionenota = get_var('descrizionenota');

$giorniPagamento = get_var('giorniPagamento');
$testopagamento = get_var('testopagamento');

$tipogestionecds = get_var('tipogestionecds');

$parTipo = get_var('parTipo');  //  da questa pagina
$parNumero = get_var('parNumero');
$parData = get_var('parData');

$par2Tipo = get_var('par2Tipo');  //  da questa pagina
$par2Numero = get_var('par2Numero');
$par2Data = get_var('par2Data');

$par3Tipo = get_var('par3Tipo');  //  da questa pagina
$par3Numero = get_var('par3Numero');
$par3Data = get_var('par3Data');

$periodoDa = get_var('periodoDa');
$periodoA = get_var('periodoA');

$fatturacollegata = get_var('fatturacollegata');
$datafattcollegata = get_var('datafattcollegata');
$totaleparziale = get_var('totaleparziale');

$operazione = get_var('operazione');  //  da questa pagina
$numerooperazione = get_var('numerooperazione');  //  da questa pagina




if(isset($_REQUEST['tipocompetenza'])) $competenza = get_var('tipocompetenza');
else $competenza = 0;








if($operazione=="CANCELLA"){
	$query = "DELETE  FROM fatture_generali WHERE ID = " . $numerooperazione . ";";
	mysql_query($query);
	echo '<script>
	alert ("Fattura cancellata");
	window.location="fatture.php?c='.$c.'&a='.$a.'";
	</script>';

	die;
}

$myDatiComune = new fatture_dati_sedi_comuni(null);
$myDatiComune->AutoGenerazioneTable();

$myDatiContratto = new fatture_dati_contratti(null);
$myDatiContratto->AutoGenerazioneTable();

$myDatiFattura = new fatture_dati_cig(null);
$myDatiFattura->AutoGenerazioneTable();

$myFatturaGenerale = new fatture_generali(null);
$myFatturaGenerale->AutoGenerazioneTable();



if($tiporiscossione=="PARK"){
	$impostaDaPagare = "NO";
	$totaleadoversi = $totaleimponibile;
	$totalefattura = 	$totaleimponibile + str_replace(",",".",$iva);
	$importo = $totaleimponibile;
	$ordinario = 0;


	$descrizionefattura = get_var('descrizionefattura');

	if($operazione!="CORREGGI") $operazione == "SALVA";
}






$myLavoroComune = new ente_gestito($c);
if ($myLavoroComune->ID != "")
{
	$myFattComune = new fatture_dati_sedi_comuni(null);
	$iddCom = $myDatiComune->CercaDatiComune($c);
	if ($iddCom == null)
	{
		$myFattComune->CC = $c;
		$myFattComune->Data = "0000-00-00";
		$myFattComune->Indirizzo1 = "Amministrazione Comunale di";;
		$myFattComune->Indirizzo2 = $myLavoroComune->Nome;
		$myFattComune->Indirizzo3 = $myLavoroComune->Ufficio->Toponimo;
		$myFattComune->Indirizzo4 = $myLavoroComune->Ufficio->Civico;
		$myFattComune->Indirizzo5 = $myLavoroComune->Ufficio->Cap;
		$myFattComune->Indirizzo6 = $myLavoroComune->Ufficio->Comune;
		$myFattComune->Indirizzo7 = $myLavoroComune->Ufficio->Provincia;
		$myFattComune->Cod_Fisc = $myLavoroComune->Ufficio->Partita_Iva;
		$myFattComune->P_IVA = $myLavoroComune->Ufficio->Codice_Fiscale;
		
		if ($myFattComune->CC != "") $myFattComune->InsertUpdateDati ("INSERT");
	}
}

if ($sceltacomune == "" && $myLavoroComune->ID != "")
{
	$sceltacomune = $myLavoroComune->CC;
}

if ($sceltacomune != "")
{
	$myDatiComune = new fatture_dati_sedi_comuni(null);
	$iddd = $myDatiComune->CercaDatiComune($sceltacomune);
	$myDatiComune = new fatture_dati_sedi_comuni($iddd);
	$riga1Indirizzo = $myDatiComune->Indirizzo1;
	$riga2Indirizzo = $myDatiComune->Indirizzo2;
	$riga3Indirizzo = $myDatiComune->Indirizzo3;
	$riga4Indirizzo = $myDatiComune->Indirizzo4;
	$riga5Indirizzo = $myDatiComune->Indirizzo5;
	$riga6Indirizzo = $myDatiComune->Indirizzo6;
	$riga7Indirizzo = $myDatiComune->Indirizzo7;
	$riga8Indirizzo = $myDatiComune->Cod_Fisc;
	$riga9Indirizzo = $myDatiComune->P_IVA;
}
else
{
	alert ("non so che accade");
	return;
}










if ($operazione == "SALVA")
{
	$myFatturaGenerale = new fatture_generali(null);
}
else if ($operazione == "CORREGGI")
{
	$myFatturaGenerale = new fatture_generali($numerooperazione);
}
else if ($operazione == "NOTADICREDITO")
{
	$myFatturaGenerale = new fatture_generali($numerooperazione);
}

if ($operazione == "SALVA" || $operazione == "CORREGGI")
{
	//$myFatturaGenerale->ID;
	
	$myFatturaGenerale->Fat_Societa = $societa;
	$myFatturaGenerale->Fat_Tributo = $tiporiscossione;
	$myFatturaGenerale->Fat_Comune = $sceltacomune;
	$myFatturaGenerale->Fat_Dati_Cig = $daticig;
	//$tipocig = get_var('tipocig');
	
	$myFatturaGenerale->Fat_Importo = str_replace(",", ".", $importo);
	$myFatturaGenerale->Fat_Spese = str_replace(",", ".", $spese);
	$myFatturaGenerale->Fat_Ordinario = str_replace(",", ".", $ordinario);
	$myFatturaGenerale->Fat_Temporaneo = str_replace(",", ".", $temporaneo);
	$myFatturaGenerale->Fat_Affissioni = str_replace(",", ".", $affissioni);
	//$myFatturaGenerale->Fat_Totale_Imponibile = str_replace(",", ".", $totaleimponibile);
	$myFatturaGenerale->Fat_Iva_Percentuale = str_replace(",", ".", $percentualeiva);
	$myFatturaGenerale->Fat_Iva = str_replace(",", ".", $iva);
	$myFatturaGenerale->Fat_Rimborsi = str_replace(",", ".", $rimborsi);
	$myFatturaGenerale->Fat_Bollo = str_replace(",", ".", $impostabollo);
	
	$myFatturaGenerale->Fat_Totale = str_replace(",", ".", $totalefattura);
	$myFatturaGenerale->Fat_Totale_A_Doversi = str_replace(",", ".", $totaleadoversi);
	
	$myFatturaGenerale->Fat_Numero = $numerofattura;
	$myFatturaGenerale->Fat_Data = to_mysql_date($datafattura);
	$myFatturaGenerale->Fat_Tipo = $tipofattura;
	$myFatturaGenerale->Fat_Anno = $annofattura;
	$myFatturaGenerale->Fat_Anno_Bilancio = $annobilanciofattura;
	$myFatturaGenerale->Fat_Anno_Competenza = $annocompetenzafattura;
	
	$myFatturaGenerale->Fat_Testo_Spettabile = addslashes(trim($spettabile));
	$myFatturaGenerale->Fat_Pagata = $tipobanca;
	
	if ($descrizionenota != "")
	{
		$myFatturaGenerale->Fat_Testo_Contratto = /*addslashes(*/trim($descrizionenota)/*)*/;
	}
	else 
	{
		if($tiporiscossione=="CDS"){
			if(isset($_REQUEST['TestiCDS'])){
				$TestiCDS = get_var('TestiCDS');
				$descrizionefattura = $aTestiCDS[$TestiCDS]." ".$descrizionefattura;
			}
		}
		$myFatturaGenerale->Fat_Testo_Contratto = /*addslashes(*/trim($descrizionefattura)/*)*/;
	}
	$myFatturaGenerale->Fat_Testo_Da_A_Periodo = addslashes(trim($descrizione4fattura));
	$myFatturaGenerale->Fat_Giorni_Pagamento = $giorniPagamento;



	$myFatturaGenerale->Fat_Testo_Pagamento = addslashes(trim($testopagamento));

	$myFatturaGenerale->Fat_Descrizione_Libera = addslashes(trim($descrizionelibera));


	$myFatturaGenerale->Fat_Competenza = $competenza;

	$myFatturaGenerale->Fat_Tipo_Versamento = "volontario";
	$myFatturaGenerale->Fat_Tipo_Contratto = $parTipo;
	$myFatturaGenerale->Fat_Numero_Contratto = $parNumero;
	$myFatturaGenerale->Fat_Data_Contratto = to_mysql_date($parData);
	$myFatturaGenerale->Fat_Tipo_2_Contratto = $par2Tipo;
	$myFatturaGenerale->Fat_Numero_2_Contratto = $par2Numero;
	$myFatturaGenerale->Fat_Data_2_Contratto = to_mysql_date($par2Data);
	$myFatturaGenerale->Fat_Tipo_3_Contratto = $par3Tipo;
	$myFatturaGenerale->Fat_Numero_3_Contratto = $par3Numero;
	$myFatturaGenerale->Fat_Data_3_Contratto = to_mysql_date($par3Data);
	
	
	$myFatturaGenerale->Fat_Da_Data_Periodo = to_mysql_date($periodoDa);
	$myFatturaGenerale->Fat_A_Data_Periodo = to_mysql_date($periodoA);
	
	$myFatturaGenerale->Fat_Dati_Comune = $myDatiComune->ID;
	
	$myFatturaGenerale->Fat_Collegata = $fatturacollegata;
	$myFatturaGenerale->Fat_Data_Collegata = to_mysql_date($datafattcollegata);
}

$memoSalva = "";
if ($operazione == "SALVA")
{
	//if ($_SESSION['CC_User'] == "***+") { alertAllGlobalVariables(); return; }
	$myFatturaGenerale->InsertUpdateFattura();

	$memoSalva = "MEMOSALVA";
	$operazione = "CARICANUOVA";
	$numerooperazione = $myFatturaGenerale->FatturaGiaPresente();
}
else if ($operazione == "CORREGGI")
{
	//if ($_SESSION['CC_User'] == "***+") { alertAllGlobalVariables(); return; }
	$myFatturaGenerale->InsertUpdateFattura('UPDATE');

	$memoSalva = "MEMOSALVA";
	$operazione = "CARICANUOVA";

	$percorsoPdfLink = $myPercorsoFatture . "/" . $myFatturaGenerale->Fat_Nome_File_Pdf;
	$percorsoXmlLink = $myPercorsoFatture . "/" . $myFatturaGenerale->Fat_Nome_File_Xml;
	if (file_exists($percorsoPdfLink)) unlink($percorsoPdfLink);
	if (file_exists($percorsoXmlLink)) unlink($percorsoXmlLink);
}
else if ($operazione == "NOTADICREDITO")
{
	//$operazione = "CARICANUOVA";

	/*$percorsoPdfLink = $myPercorsoFatture . "/" . $myFatturaGenerale->Fat_Nome_File_Pdf;
	$percorsoXmlLink = $myPercorsoFatture . "/" . $myFatturaGenerale->Fat_Nome_File_Xml;
	if (file_exists($percorsoPdfLink)) unlink($percorsoPdfLink);
	if (file_exists($percorsoXmlLink)) unlink($percorsoXmlLink);*/
}


$ButtonDelete = true;






$idFattura = 0;
$myFatturaNuova = new fatture_generali(null);

switch ($operazione)
{
	case "CARICANUOVA":
		$myFatturaNuova = new fatture_generali($numerooperazione);
		
		$idFattura = $myFatturaNuova->ID;
		$societa = $myFatturaNuova->Fat_Societa;
		$tiporiscossione = $myFatturaNuova->Fat_Tributo;
		$sceltacomune = $myFatturaNuova->Fat_Comune;
		$daticig = $myFatturaNuova->Fat_Dati_Cig;
		//$tipocig = get_var('tipocig');
		
		$importo = number_format($myFatturaNuova->Fat_Importo, 2, ",", "");
		$spese = number_format($myFatturaNuova->Fat_Spese, 2, ",", "");
		$ordinario = number_format($myFatturaNuova->Fat_Ordinario, 2, ",", "");
		$temporaneo = number_format($myFatturaNuova->Fat_Temporaneo, 2, ",", "");
		$affissioni = number_format($myFatturaNuova->Fat_Affissioni, 2, ",", "");
		//$myFatturaNuova->Fat_Totale_Imponibile = str_replace(",", ".", $totaleimponibile);
		$percentualeiva = number_format($myFatturaNuova->Fat_Iva_Percentuale, 2, ",", "");
		$iva = number_format($myFatturaNuova->Fat_Iva, 2, ",", "");
		$rimborsi = number_format($myFatturaNuova->Fat_Rimborsi, 2, ",", "");
		$impostabollo = number_format($myFatturaNuova->Fat_Bollo, 2, ",", "");
		
		$totaleimponibile = $myFatturaNuova->Fat_Importo + $myFatturaNuova->Fat_Spese + 
							$myFatturaNuova->Fat_Ordinario + $myFatturaNuova->Fat_Temporaneo + 
							$myFatturaNuova->Fat_Affissioni;
		$totaleimponibile = number_format($totaleimponibile, 2, ",", "");
		
		$totalefattura = number_format($myFatturaNuova->Fat_Totale, 2, ",", "");
		$totaleadoversi = number_format($myFatturaNuova->Fat_Totale_A_Doversi, 2, ",", "");
		
		$numerofattura = $myFatturaNuova->Fat_Numero;
		$datafattura = from_mysql_date($myFatturaNuova->Fat_Data);
		$tipofattura = $myFatturaNuova->Fat_Tipo;
		$annofattura = $myFatturaNuova->Fat_Anno;
		$annobilanciofattura = $myFatturaNuova->Fat_Anno_Bilancio;
		if ($annobilanciofattura == 0)
			$annobilanciofattura = "";
		$annocompetenzafattura = $myFatturaNuova->Fat_Anno_Competenza;
		if ($annocompetenzafattura == 0)
			$annocompetenzafattura = "";
		
		$spettabile = stripslashes(trim($myFatturaNuova->Fat_Testo_Spettabile));
		$tipobanca = $myFatturaNuova->Fat_Pagata;
		
		$descrizionefattura = stripslashes(trim($myFatturaNuova->Fat_Testo_Contratto));
		$descrizione4fattura = stripslashes(trim($myFatturaNuova->Fat_Testo_Da_A_Periodo));
		$descrizionenota = stripslashes(trim($myFatturaNuova->Fat_Testo_Contratto));
		$descrizionelibera = stripslashes(trim($myFatturaNuova->Fat_Descrizione_Libera));
		$competenza = $myFatturaNuova->Fat_Competenza;


		$giorniPagamento = $myFatturaNuova->Fat_Giorni_Pagamento;
		$testopagamento = stripslashes(trim($myFatturaNuova->Fat_Testo_Pagamento));
		
		//$myFatturaNuova->Fat_Tipo_Versamento = "volontario";
		$parTipo = $myFatturaNuova->Fat_Tipo_Contratto;
		$parNumero = $myFatturaNuova->Fat_Numero_Contratto;
		$parData = from_mysql_date($myFatturaNuova->Fat_Data_Contratto);
		$par2Tipo = $myFatturaNuova->Fat_Tipo_2_Contratto;
		$par2Numero = $myFatturaNuova->Fat_Numero_2_Contratto;
		$par2Data = from_mysql_date($myFatturaNuova->Fat_Data_2_Contratto);
		$par3Tipo = $myFatturaNuova->Fat_Tipo_3_Contratto;
		$par3Numero = $myFatturaNuova->Fat_Numero_3_Contratto;
		$par3Data = from_mysql_date($myFatturaNuova->Fat_Data_3_Contratto);
		
		
		$periodoDa = from_mysql_date($myFatturaNuova->Fat_Da_Data_Periodo);
		$periodoA = from_mysql_date($myFatturaNuova->Fat_A_Data_Periodo);
		
		$esplodoBarra = explode ("/", $myFatturaNuova->Fat_Numero);
		$numerosolofattura = $esplodoBarra[0];
		
		if ($memoSalva == "MEMOSALVA")
		{
			$nomeGenerico = $myFatturaNuova->Fat_Data . "_" . $myFatturaNuova->Fat_Tipo . "_" . $esplodoBarra[0] . "_" . $esplodoBarra[1] . "_" . $esplodoBarra[2] . "__" . $myFatturaNuova->ID;
			$nomePdf = $nomeGenerico . ".pdf";
			$nomeXml = $nomeGenerico . ".xml";
			
			$linkPdf = $myPercorsoCortoFatture . "/" . $nomePdf;
			$linkXml = $myPercorsoCortoFatture . "/" . $nomeXml;
		}
		else
		{
			$linkPdf = $myPercorsoCortoFatture . "/" . $myFatturaNuova->Fat_Nome_File_Pdf;
			$linkXml = $myPercorsoCortoFatture . "/" . $myFatturaNuova->Fat_Nome_File_Xml;
		}
		
		$myInvio = new fatture_invii(null);
		$idInvio = $myInvio->CercaInvioDaFattura($myFatturaNuova->ID);
		$myInvio = new fatture_invii($idInvio);




		if ($myInvio->Identificativo_SDI != "")
		{
			$ButtonDelete = false;
			$disableCorreggi = " disabled ";
			$scrittaInviato = "INVIATA IN DATA " . from_mysql_date($myInvio->Data_Invio);

		}
		
		//$myFatturaNuova->Fat_Dati_Comune = $myDatiComune->ID;
		$fatturacollegata = $myFatturaNuova->Fat_Collegata;
		$datafattcollegata = from_mysql_date($myFatturaNuova->Fat_Data_Collegata);
		//$totaleparziale = "";
		if (strpos($myFatturaNuova->Fat_Testo_Contratto, "totale") > 0) $totaleparziale = "totale";
		else if (strpos($myFatturaNuova->Fat_Testo_Contratto, "parziale") > 0) $totaleparziale = "parziale";
		break;
	case "NOTADICREDITO":
		$myFatturaNuova = new fatture_generali($numerooperazione);
		
		$idFattura = "";  //  $myFatturaNuova->ID;
		$societa = $myFatturaNuova->Fat_Societa;
		$tiporiscossione = $myFatturaNuova->Fat_Tributo;
		$sceltacomune = $myFatturaNuova->Fat_Comune;
		$daticig = $myFatturaNuova->Fat_Dati_Cig;
		//$tipocig = get_var('tipocig');
		
		$importo = number_format($myFatturaNuova->Fat_Importo, 2, ",", "");
		$spese = number_format($myFatturaNuova->Fat_Spese, 2, ",", "");
		$ordinario = number_format($myFatturaNuova->Fat_Ordinario, 2, ",", "");
		$temporaneo = number_format($myFatturaNuova->Fat_Temporaneo, 2, ",", "");
		$affissioni = number_format($myFatturaNuova->Fat_Affissioni, 2, ",", "");
		//$myFatturaNuova->Fat_Totale_Imponibile = str_replace(",", ".", $totaleimponibile);
		$percentualeiva = number_format($myFatturaNuova->Fat_Iva_Percentuale, 2, ",", "");
		$iva = number_format($myFatturaNuova->Fat_Iva, 2, ",", "");
		$rimborsi = number_format($myFatturaNuova->Fat_Rimborsi, 2, ",", "");
		$impostabollo = number_format($myFatturaNuova->Fat_Bollo, 2, ",", "");
		
		$totaleimponibile = $myFatturaNuova->Fat_Importo + $myFatturaNuova->Fat_Spese + 
							$myFatturaNuova->Fat_Ordinario + $myFatturaNuova->Fat_Temporaneo + 
							$myFatturaNuova->Fat_Affissioni;
		$totaleimponibile = number_format($totaleimponibile, 2, ",", "");
		
		$totalefattura = number_format($myFatturaNuova->Fat_Totale, 2, ",", "");
		$totaleadoversi = number_format($myFatturaNuova->Fat_Totale_A_Doversi, 2, ",", "");
		
		$numerofattura = "";  //  $myFatturaNuova->Fat_Numero;
		$datafattura = "";  //  from_mysql_date($myFatturaNuova->Fat_Data);
		$tipofattura = "notacredito";  //  $myFatturaNuova->Fat_Tipo;
		$annofattura = "";  //  $myFatturaNuova->Fat_Anno;
		$annobilanciofattura = $myFatturaNuova->Fat_Anno_Bilancio;
		if ($annobilanciofattura == 0)
			$annobilanciofattura = "";
		$annocompetenzafattura = $myFatturaNuova->Fat_Anno_Competenza;
		if ($annocompetenzafattura == 0)
			$annocompetenzafattura = "";
		
		$spettabile = stripslashes(trim($myFatturaNuova->Fat_Testo_Spettabile));
		$tipobanca = $myFatturaNuova->Fat_Pagata;
		
		$descrizionefattura = stripslashes(trim($myFatturaNuova->Fat_Testo_Contratto));
		$descrizione4fattura = stripslashes(trim($myFatturaNuova->Fat_Testo_Da_A_Periodo));
		$descrizionelibera = stripslashes(trim($myFatturaNuova->Fat_Descrizione_Libera));
		$competenza = $myFatturaNuova->$competenza;
		$descrizionenota = "";  //  stripslashes(trim($myFatturaNuova->Fat_Testo_Contratto));
		$giorniPagamento = $myFatturaNuova->Fat_Giorni_Pagamento;
		$testopagamento = stripslashes(trim($myFatturaNuova->Fat_Testo_Pagamento));
		
		//$myFatturaNuova->Fat_Tipo_Versamento = "volontario";
		$parTipo = $myFatturaNuova->Fat_Tipo_Contratto;
		$parNumero = $myFatturaNuova->Fat_Numero_Contratto;
		$parData = from_mysql_date($myFatturaNuova->Fat_Data_Contratto);
		$par2Tipo = $myFatturaNuova->Fat_Tipo_2_Contratto;
		$par2Numero = $myFatturaNuova->Fat_Numero_2_Contratto;
		$par2Data = from_mysql_date($myFatturaNuova->Fat_Data_2_Contratto);
		$par3Tipo = $myFatturaNuova->Fat_Tipo_3_Contratto;
		$par3Numero = $myFatturaNuova->Fat_Numero_3_Contratto;
		$par3Data = from_mysql_date($myFatturaNuova->Fat_Data_3_Contratto);
		
		
		$periodoDa = from_mysql_date($myFatturaNuova->Fat_Da_Data_Periodo);
		$periodoA = from_mysql_date($myFatturaNuova->Fat_A_Data_Periodo);
		
		$esplodoBarra = explode ("/", $myFatturaNuova->Fat_Numero);
		$numerosolofattura = $esplodoBarra[0];
		
		$fatturacollegata = $myFatturaNuova->Fat_Numero;
		$datafattcollegata = from_mysql_date($myFatturaNuova->Fat_Data);
		//if (strpos($myFatturaNuova->Fat_Testo_Contratto, "totale") > 0) $totaleparziale = "totale";
		//else if (strpos($myFatturaNuova->Fat_Testo_Contratto, "parziale") > 0) $totaleparziale = "parziale";
		//alert ("$fatturacollegata  e   $datafattcollegata  e   $descrizionenota");
		
		/*if ($memoSalva == "MEMOSALVA")
		{
			$nomeGenerico = $myFatturaNuova->Fat_Data . "_" . $myFatturaNuova->Fat_Tipo . "_" . $esplodoBarra[0] . "_" . $esplodoBarra[1] . "_" . $esplodoBarra[2] . "__" . $myFatturaNuova->ID;
			$nomePdf = $nomeGenerico . ".pdf";
			$nomeXml = $nomeGenerico . ".xml";
			
			$linkPdf = $myPercorsoCortoFatture . "/" . $nomePdf;
			$linkXml = $myPercorsoCortoFatture . "/" . $nomeXml;
		}
		else
		{
			$linkPdf = $myPercorsoCortoFatture . "/" . $myFatturaNuova->Fat_Nome_File_Pdf;
			$linkXml = $myPercorsoCortoFatture . "/" . $myFatturaNuova->Fat_Nome_File_Xml;
		}*/
		
		//$myFatturaNuova->Fat_Dati_Comune = $myDatiComune->ID;
		break;
}

if ($sceltacomune != "")
{
	$myDatiComune = new fatture_dati_sedi_comuni(null);
	$iddd = $myDatiComune->CercaDatiComune($sceltacomune);
	$myDatiComune = new fatture_dati_sedi_comuni($iddd);
	$riga1Indirizzo = $myDatiComune->Indirizzo1;
	$riga2Indirizzo = $myDatiComune->Indirizzo2;
	$riga3Indirizzo = $myDatiComune->Indirizzo3;
	$riga4Indirizzo = $myDatiComune->Indirizzo4;
	$riga5Indirizzo = $myDatiComune->Indirizzo5;
	$riga6Indirizzo = $myDatiComune->Indirizzo6;
	$riga7Indirizzo = $myDatiComune->Indirizzo7;
	$riga8Indirizzo = $myDatiComune->Cod_Fisc;
	$riga9Indirizzo = $myDatiComune->P_IVA;
}
else
{
	alert ("non so che accade");
	return;
}


$precedente = $myFatturaNuova->FatturaPrecedente();
$successiva = $myFatturaNuova->FatturaSuccessiva();


$imgPrecedente = "/gitco2/immagini/FrecciaSgrey.png";
if ($precedente != null) $imgPrecedente = "/gitco2/immagini/FrecciaS.png";
$imgSuccessiva = "/gitco2/immagini/FrecciaDgrey.png";
if ($successiva != null) $imgSuccessiva = "/gitco2/immagini/FrecciaD.png";




$optionComuni = $myDatiComune->ListaDatiTributoComune($myDatiComune->CC, $tiporiscossione);
$listaContratti = $myDatiContratto->ListaDatiContratti($sceltacomune, $tiporiscossione, "");
$arrayContratti = $myDatiContratto->ArrayDatiContratti($sceltacomune, $tiporiscossione);
$arrayCigs = $myDatiFattura->ListaDatiCigs($sceltacomune, $tiporiscossione);
//alert ("$sceltacomune, $tiporiscossione, $daticig");
$optionCigs = $myDatiFattura->ListaCigs($sceltacomune, $tiporiscossione, $daticig);
$listaTutteFatture = $myFatturaGenerale->ListaTutteFatture();
if ($daticig != "")
{
	$myDatiFattura = new fatture_dati_cig($daticig);
	$arrayCigs = $myDatiFattura->ListaDatiCigs($sceltacomune, $tiporiscossione);
	$optionCigs = $myDatiFattura->ListaCigs($sceltacomune, $tiporiscossione, $daticig);
	$tipocig = $myDatiFattura->Tipo_Gestione;
}

$selectSarida = $selectStc = "";
switch ($societa)
{
	case "SARIDA": $selectSarida = " selected "; break;
	case "STC": $selectStc = " selected "; break;
	default: break;
}

$optionTipiTrib = $myFatturaGenerale->OptionTipiFatture($tiporiscossione);

$htmlPICF = "";
$pdfPartIva = "";
$pdfCodFisc = "";
if ($riga8Indirizzo == $riga9Indirizzo)
{
	if ($riga8Indirizzo == "") $htmlPICF = "";
	else $htmlPICF = "C.F./P.I. " . $riga8Indirizzo;
	$pdfCodFisc = $htmlPICF;
}
else
{
	if ($riga8Indirizzo == "") $htmlPICF = "";
	else { $htmlPICF = "C.F. " . $riga8Indirizzo; $pdfCodFisc = "C.F. " . $riga8Indirizzo; }
	if ($riga9Indirizzo == "") $htmlPICF = $htmlPICF;
	else { $htmlPICF .= " / P.I. " . $riga9Indirizzo; $pdfPartIva = "P.I. " . $riga9Indirizzo; }
}
if ($pdfPartIva == "")
{
	$pdfPartIva = $pdfCodFisc;
	$pdfCodFisc = "";
}



if ($spettabile == "spettabile")  //  da questa pagina
{
	$selectSpettabile = " selected ";
	$scrittaSpettabile = "Spett.le";
}
else 
{
	$selectSpettabile = "";
	$scrittaSpettabile = "";
}

$selectFattura = $selectPreavviso = $selectReversale = $selectNota = "";
switch ($tipofattura)
{
	case "":
		$selectFattura = "";
		$codiceTipoFattura = "";
		$scrittaTipoFattura = "";
		break;
	case "Fattura":  //  da fatture cds
		$selectFattura = " selected ";
		$codiceTipoFattura = "TD01";
		$scrittaTipoFattura = "FATTURA";
		break;
	case "Reversale":  //  da fatture cds
		$selectReversale = " selected ";
		$codiceTipoFattura = "TD01";
		$scrittaTipoFattura = "REVERSALE";
		break;
	case "fattura":  //  da questa pagina
		$selectFattura = " selected ";
		$codiceTipoFattura = "TD01";
		$scrittaTipoFattura = "FATTURA";
		break;
	case "preavviso":  //  da questa pagina
		$selectPreavviso = " selected ";
		$codiceTipoFattura = "TD01";
		$scrittaTipoFattura = "PREAVVISO DI FATTURA";
		break;
	case "reversale":  //  da questa pagina
		$selectReversale = " selected ";
		$codiceTipoFattura = "TD01";
		$scrittaTipoFattura = "REVERSALE";
		break;
	case "notacredito":  //  da questa pagina
		$selectNota = " selected ";
		$codiceTipoFattura = "TD04";
		$scrittaTipoFattura = "NOTA DI CREDITO";
		break;
	default:
		alert ("tipo fattura sconosciuta: " . $tipofattura);
		return;
		break;
}

$testoCdsItaliani = "Rif. riscossioni italiane";
$testoCdsEsteri = "Rif. riscossioni estere";
$testoCdsFisso = "Locazione apparecchiatura elettronica di tipo fisso";
$testoCdsMobile = "Locazione apparecchiatura elettronica di tipo mobile";


$selectItaliani = $selectEsteri = $selectFisso = $selectMobile = "";
/*switch ($tipogestionecds)
{
	case "RISCOSSIONE": $selectItaliani = " selected "; break;
	case "ESTERO": $selectEsteri = " selected "; break;
	case "FISSO": $selectFisso = " selected "; break;
	case "MOBILE": $selectMobile = " selected "; break;
}*/
if (substr($descrizione4fattura, 0, strlen($testoCdsItaliani)) == $testoCdsItaliani) $selectItaliani = " selected ";
if (substr($descrizione4fattura, 0, strlen($testoCdsEsteri)) == $testoCdsEsteri) $selectEsteri = " selected ";
if (substr($descrizione4fattura, 0, strlen($testoCdsFisso)) == $testoCdsFisso) $selectFisso = " selected ";
if (substr($descrizione4fattura, 0, strlen($testoCdsMobile)) == $testoCdsMobile) $selectMobile = " selected ";

$options1Contratti = $myDatiContratto->ListaTipiContratto($parTipo);
$options2Contratti = $myDatiContratto->ListaTipiContratto($par2Tipo);
$options3Contratti = $myDatiContratto->ListaTipiContratto($par3Tipo);

switch ($tiporiscossione)
{
	case "PUB":
		switch ($tipocig)
		{
			//case "SERVIZIO": $tipobanca = "PAGATA"; break;
			case "PAGATA_AD_AGGIO": $tipobanca = "PAGATA"; break;
			case "PAGATA_A_CANONE": $tipobanca = "PAGATA"; break;
		}
		break;
	case "TOSAP":
		$tipobanca = "PAGATA";
		break;
	case "CDS":
		//$tipobanca = "PAGATA";
		break;
	case "TARI":
		switch ($tipocig)
		{
			//case "SERVIZIO": $tipobanca = "PAGATA"; break;
			case "PAGATA_AD_AGGIO": $tipobanca = "PAGATA"; break;
			//case "PAGATA_A_CANONE": $tipobanca = ""; break;
		}
		break;
	case "ICI":
		switch ($tipocig)
		{
			//case "SERVIZIO": $tipobanca = "PAGATA"; break;
			case "PAGATA_AD_AGGIO": $tipobanca = "PAGATA"; break;
			//case "PAGATA_A_CANONE": $tipobanca = ""; break;
		}
		break;
	case "IMU":
		//$tipobanca = "PAGATA";
		break;
	case "PARK":
		break;
	default:
		break;
}

$splitPayment = "Y";
$selectBgsg = $selectBpi = $selectPagata = "";
switch ($tipobanca)
{
	case "":
		$splitPayment = "Y";
		$scrittaTestoIva = "";
		break;
	case "BGSG":  //  da fatture cds    e   da questa pagina
		$selectBgsg = " selected ";
		$splitPayment = "Y";
		$scrittaTestoIva = $scrittaIva;
		break;
	case "BPI":  //  da fatture cds    e   da questa pagina
		$selectBpi = " selected ";
		$splitPayment = "Y";
		$scrittaTestoIva = $scrittaIva;
		break;
	case "PAGA":  //  da fatture cds
		$selectPagata = " selected ";
		$splitPayment = "N";
		$scrittaTestoIva = "";
		break;
	case "PAGATA":  //  da questa pagina
		$selectPagata = " selected ";
		$splitPayment = "N";
		$scrittaTestoIva = "";
		break;
	default:
		alert ("tipo banca sconosciuta: " . $tipobanca);
		$splitPayment = "Y";
		$scrittaTestoIva = "";
		break;
}

$CheckPagata = 0;
$mostraBancaGiusta = "";
if($id==1){
	switch ($tipobanca)
	{
		case "":
			$primaPagamento = "";
			$secondaPagamento = "";
			$terzaPagamento = "";
			$quartaPagamento = "";
			$quintaPagamento = "";
			$iban = "";
			$bic = "";
			$nomeBanca = "";
			$mostraBancaGiusta = '$("#bancabgsg").hide();' . "\n";
			$mostraBancaGiusta .= '$("#bancabpi").hide();' . "\n";
			break;
		case "BGSG":  //  da fatture cds    e   da questa pagina
			$primaPagamento = "";
			$secondaPagamento = $bancaBGSG_1;
			$terzaPagamento = $bancaBGSG_2;
			$quartaPagamento = $bancaBGSG_3;
			$quintaPagamento = $bancaBGSG_4;
			$iban = $ibanBGSG;
			$bic = $bicBGSG;
			$nomeBanca = $nomeBancaBGSG;
			$mostraBancaGiusta = '$("#bancabgsg").show();' . "\n";
			$mostraBancaGiusta .= '$("#bancabpi").hide();' . "\n";
			break;
		case "BPI":  //  da fatture cds    e   da questa pagina
			$primaPagamento = "";
			$secondaPagamento = $bancaBPI_1;
			$terzaPagamento = $bancaBPI_2;
			$quartaPagamento = $bancaBPI_3;
			$quintaPagamento = $bancaBPI_4;
			$iban = $ibanBPI;
			$bic = $bicBPI;
			$nomeBanca = $nomeBancaBPI;
			$mostraBancaGiusta = '$("#bancabgsg").hide();' . "\n";
			$mostraBancaGiusta .= '$("#bancabpi").show();' . "\n";
			break;
		case "PAGA":  //  da fatture cds
			$primaPagamento = "FATTURA PAGATA";
			$secondaPagamento = "";
			$terzaPagamento = "";
			$quartaPagamento = "";
			$quintaPagamento = "";
			$mostraBancaGiusta = '$("#bancabgsg").hide();' . "\n";
			$mostraBancaGiusta .= '$("#bancabpi").hide();' . "\n";
			$CheckPagata = 1;
			break;
		case "PAGATA":  //  da questa pagina
			$primaPagamento = "FATTURA PAGATA";
			$secondaPagamento = "";
			$terzaPagamento = "";
			$quartaPagamento = "";
			$quintaPagamento = "";
			$mostraBancaGiusta = '$("#bancabgsg").hide();' . "\n";
			$mostraBancaGiusta .= '$("#bancabpi").hide();' . "\n";
			$CheckPagata = 1;
			break;
		default:
			alert ("error banca sconosciuta: " . $tipobanca);
			return;
			break;
	}
}else{
	$primaPagamento = "";
	$secondaPagamento = $bancaSTC_1;
	$terzaPagamento = $bancaSTC_2;
	$quartaPagamento = $bancaSTC_3;
	$quintaPagamento = $bancaSTC_4;
	$iban = $ibanSTC;
	$bic = $bicSTC;
	$nomeBanca = $nomeBancaSTC;
}
$select30gg = $select60gg = $select90gg = $select120gg = "";
if ($tipobanca != "PAGATA")
{
	switch ($giorniPagamento)
	{
		case "": $mostraBancaGiusta .= '$("#modalitapagamento").hide();' . "\n"; break;
		case "0": $mostraBancaGiusta .= '$("#modalitapagamento").hide();' . "\n"; break;
		case 30: $select30gg = " selected "; $mostraBancaGiusta .= '$("#modalitapagamento").show();' . "\n"; break;
		case 60: $select60gg = " selected "; $mostraBancaGiusta .= '$("#modalitapagamento").show();' . "\n"; break;
		case 90: $select90gg = " selected "; $mostraBancaGiusta .= '$("#modalitapagamento").show();' . "\n"; break;
		case 120: $select120gg = " selected "; $mostraBancaGiusta .= '$("#modalitapagamento").show();' . "\n"; break;
	}
}
else
{
	$mostraBancaGiusta .= '$("#modalitapagamento").hide();' . "\n";
}
if($id>1){

	$mostraBancaGiusta .= '$(".tipobancaid").hide();' . "\n";

}

if($tiporiscossione=='PARK')$mostraBancaGiusta = '$("#modalitapagamento").show();';




$selectParziale = $selectTotale = "";
switch ($totaleparziale)
{
	case "": break;
	case "parziale": $selectParziale = " selected "; break;
	case "totale": $selectTotale = " selected "; break;
}




$progressivoInvio = $idFattura;
while (strlen($progressivoInvio) < 10)
{
	$progressivoInvio = "0" . $progressivoInvio;
}


$vediSalvataggio = "";

$tempDescr1 = "";
switch ($tiporiscossione)
{
	case "TOSAP":
		$tempDescr1 = "Corrispettivo per servizio accertamento e riscossione";
		$tempDescr1 .= " Tassa Occupazione Spazi e Aree Pubbliche";
		break;
	case "PUB":
		$tempDescr1 = "Corrispettivo per servizio accertamento e riscossione";
		$tempDescr1 .= " dell'imposta comunale sulla pubblicita' e diritto pubbliche affissioni";
		if ($tipocig == "SERVIZIO")
		{
			$tempDescr1 = "Per l'attivita' di supporto alla gestione diretta";
			$tempDescr1 .= " dell'imposta comunale sulla pubblicita', pubbliche affissioni";
			$tempDescr1 .= " e occupazione di spazi ed aree pubbliche";
		}
		break;
	case "CDS":
		$tempDescr1 = "";
		$tempDescr1 .= "";
		break;
	case "TARI":
		$tempDescr1 = "Corrispettivo per riscossioni TARSU/TARI";
		break;
	case "IMU":
		$tempDescr1 = "Corrispettivo per riscossioni IMU/TASI";
		break;
	case "ICI":
		$tempDescr1 = "Corrispettivo per riscossioni ICI";
		break;

	case "PARK":
		$tempDescr1 = "Corrispettivo per l'affidamento del servizio di noleggio parcometri";
		break;

}
//else alert("non è tosap e pub");


$tempDescr2 = "";
$tempDescr3 = "; come da ";
switch ($parTipo)
{
	case "CONTR": $tempDescr4 = "contratto"; break;
	case "DELGC": $tempDescr4 = "delibera G.C."; break;
	case "DELGM": $tempDescr4 = "delibera G.M."; break;
	case "DETER": $tempDescr4 = "determina"; break;
	case "CONVE": $tempDescr4 = "convenzione"; break;
	case "DISCI": $tempDescr4 = "disciplinare"; break;
	case "": $tempDescr4 = ""; break;
	default: alert ("7tipo parametro inesistente: " . $parTipo); return; break;
}
if ($parNumero != "") $tempDescr5 = " n. " . $parNumero;
else $tempDescr5 = "";
if ($parData != "") $tempDescr6 = " del " . $parData;
else $tempDescr6 = "";


switch ($par2Tipo)
{
	case "CONTR": $tempDescr7 = "contratto"; break;
	case "DELGC": $tempDescr7 = "delibera G.C."; break;
	case "DELGM": $tempDescr7 = "delibera G.M."; break;
	case "DETER": $tempDescr7 = "determina"; break;
	case "CONVE": $tempDescr7 = "convenzione"; break;
	case "DISCI": $tempDescr7 = "disciplinare"; break;
	case "": $tempDescr7 = ""; break;
	default: alert ("77tipo parametro inesistente: " . $par2Tipo); return; break;
}
if ($par2Numero != "") $tempDescr8 = " n. " . $par2Numero;
else $tempDescr8 = "";
if ($par2Data != "") $tempDescr9 = " del " . $par2Data;
else $tempDescr9 = "";


switch ($par3Tipo)
{
	case "CONTR": $tempDescr10 = "contratto"; break;
	case "DELGC": $tempDescr10 = "delibera G.C."; break;
	case "DELGM": $tempDescr10 = "delibera G.M."; break;
	case "DETER": $tempDescr10 = "determina"; break;
	case "CONVE": $tempDescr10 = "convenzione"; break;
	case "DISCI": $tempDescr10 = "disciplinare"; break;
	case "": $tempDescr10 = ""; break;
	default: alert ("777tipo parametro inesistente: " . $par3Tipo); return; break;
}
if ($par3Numero != "") $tempDescr11 = " n. " . $par3Numero;
else $tempDescr11 = "";
if ($par3Data != "") $tempDescr12 = " del " . $par3Data;
else $tempDescr12 = "";

$tempNotaCred1 = "A storno ";
$tempNotaCred2 = " totale ";
$tempNotaCred3 = " della fattura";
$tempNotaCred4 = " n. numerofattura ";
$tempNotaCred5 = " ";
$tempNotaCred6 = " del datafattura ";

$titoloFattura = strtoupper($scrittaTipoFattura) . " N. $numerofattura del $datafattura";
$optionCompetenza = 'Competenza:<select name="tipocompetenza">';
$optionCompetenza .= '<option value="0"';
if($competenza == 0) $optionCompetenza .= ' SELECTED ';
$optionCompetenza .='>ORDINARIA';
$optionCompetenza .= '<option value="1"';
if($competenza == 1) $optionCompetenza .= ' SELECTED ';
$optionCompetenza .= '>COATTIVA';
$optionCompetenza .= '</select>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Pagina Creazione Fatture</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
    
<script type="text/javascript" language="Javascript">

function cambiocomune()
{
	var strLink = "<?=$questaPagina?>?";
	strLink += "c=" + $("#sceglicomune").val();
	strLink += "&a=" + "<?php echo $a?>";
	strLink += "&id=" + "<?php echo $id?>";

	location.href = strLink;
}


//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{
	SalvaFattura();
}


//F5

function annulla ()
{
	var stringaLink = "<?=$questaPagina?>?";
	stringaLink += "c=" + "<?php echo $c?>";
	stringaLink += "&a=" + "<?php echo $a?>";
	stringaLink += "&id=" + "<?php echo $id?>";
	location.href = stringaLink;
}


//F6
function nuovo_F6()
{
	NuovaFattura();
}

//F7-F8
function cambia_pag(value)
{
	var next = "";
	if (value == "prec") next = "<?=$precedente?>";
	else if (value == "succ") next = "<?=$successiva?>";
	else return;
	if (next == "") return;
	var stringaLink = "<?=$questaPagina?>?";
	stringaLink += "c=" + "<?php echo $c?>";
	stringaLink += "&a=" + "<?php echo $a?>";
	stringaLink += "&id=" + "<?php echo $id?>";
	stringaLink += "&operazione=" + "CARICANUOVA";
	stringaLink += "&numerooperazione=" + next;
	location.href = stringaLink;
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
	CaricaNuovaFattura();
}

//F10
function stampa_F10()
{
return true;
}

function inizio()
{
	$('#progressbar').progressbar({
		value: false
	});
	$( "#barlabel" ).text("Inizio elaborazione...");
}

function update(valore)
{
	$( "#progressbar" ).progressbar({value: parseInt(valore) });
	$( "#barlabel" ).text( valore + "%" );
}

function nessun_risultato()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Nessun risultato trovato");
	//sleep(1000);
}

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
}

function CaricaNuovaFattura ()
{
	
	/*if (valorediritorno != undefined)
	{
		if (valorediritorno.Tributo == "PUB")
		{
			if (valorediritorno.Tipo_CIG == 'PAGATA_A_CANONE')
			{
				$('[name=operazione]').val("CARICANUOVA");
				$('[name=numerooperazione]').val(valorediritorno.ID);
				$('[name=formfatture]').attr("action", "stampa_fattura_pubblicita_generale_canone.php");
				$('[name=formfatture]').submit();
			}
			else if (valorediritorno.Tipo_CIG == 'PAGATA_AD_AGGIO')
			{
				$('[name=operazione]').val("CARICANUOVA");
				$('[name=numerooperazione]').val(valorediritorno.ID);
				$('[name=formfatture]').attr("action", "stampa_fattura_pubblicita_generale_aggio.php");
				$('[name=formfatture]').submit();
			}
			else if (valorediritorno.Tipo_CIG == 'SERVIZIO')
			{
				$('[name=operazione]').val("CARICANUOVA");
				$('[name=numerooperazione]').val(valorediritorno.ID);
				$('[name=formfatture]').attr("action", "stampa_fattura_pubblicita_generale_servizio.php");
				$('[name=formfatture]').submit();
			}
		}
		else if (valorediritorno.Tributo == "TOSAP")
		{
			$('[name=operazione]').val("CARICANUOVA");
			$('[name=numerooperazione]').val(valorediritorno.ID);
			$('[name=formfatture]').attr("action", "stampa_fattura_tosap_generale_canone.php");
			$('[name=formfatture]').submit();
		}
		else
		{
			$('[name=operazione]').val("CARICANUOVA");
			$('[name=numerooperazione]').val(valorediritorno.ID);
			$('[name=formfatture]').attr("action", "stampa_fattura_generale.php");
			$('[name=formfatture]').submit();
		}*/
		


	/*var strDim = GeneraAlertModale70PerCento();
	var strRiepilogo = "modali/ricerca_generica_3.php?";
	strRiepilogo += "richiesta=fatture";
	strRiepilogo += "&c=" + "<?=$c?>";
	strRiepilogo += "&a=" + "<?=$a?>";
	strRiepilogo += "&cerca=Cerca";*/

	/*if ("<?=$_SESSION['CC_User']?>" == "***+")
	{
		window.open(strRiepilogo);
		return;
	}*/
	
	/*var valorediritorno = window.showModalDialog(strRiepilogo, window, strDim);
	//alert (valorediritorno.ID + " riepilogo");
	//return;
	if (valorediritorno != undefined)
	{
		$('[name=operazione]').val("CARICANUOVA");
		$('[name=numerooperazione]').val(valorediritorno.ID);
		$('[name=fatture_form]').attr("action", "<?=$questaPagina?>");
		$('[name=fatture_form]').submit();
		return;
	}*/
	
	var stringaLink = "lista_fatture.php?";
	stringaLink += "c=" + "<?php echo $c?>";
	stringaLink += "&a=" + "<?php echo $a?>";
	stringaLink += "&id=" + "<?php echo $id?>";
	location.href = stringaLink;
}

function GeneraDimensioniFinestraAlertModale ()
{
	sWidth=0;
	sHeight=0;
	browserNome = navigator.appName;
	ind = navigator.appVersion.indexOf("MSIE");
	versione = parseFloat(navigator.appVersion.substr(ind+5));
	if (navigator.javaEnabled())
	{
	      sWidth=screen.width;
	      sHeight=screen.height-25;
	}
	if (sWidth == 0 || sHeight == 0)
	{
	    sWidth = 800;
	    sHeight = 550;
	}
	//alert (sWidth + "  " + sHeight);
	sWidth = sWidth * 70 / 100;
	sHeight = sHeight * 70 / 100;

	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += "; dialogTop:70px; dialogLeft:70px; status:yes;";

	return setupPagina;
}

function CambiaTestoIva ()
{
	var tiporiscossione = $("[name=tiporiscossione]").val();
	if (tiporiscossione != "CDS")
	{
		if ($("[name=iva]").val() == "0,00")
		{
			if ($("[name=tipocig]").val() == "PAGATA_AD_AGGIO")
			{
				$("#testoiva").val("");
			}
			else if ($("[name=tipocig]").val() == "PAGATA_A_CANONE")
			{
				$("#testoiva").val("");
			}
			else if ($("[name=tipocig]").val() == "SERVIZIO")
			{
				$("#testoiva").val("");
			}
			else 
			{
				$("#testoiva").val("SCONOSCIUTO");
			}
		}
		else if ($("[name=iva]").val() != "0,00")
		{
			if ($("[name=tipocig]").val() == "PAGATA_AD_AGGIO")
			{
				$("#testoiva").val("");
			}
			else if ($("[name=tipocig]").val() == "PAGATA_A_CANONE")
			{
				$("#testoiva").val("");
			}
			else if ($("[name=tipocig]").val() == "SERVIZIO")
			{
				$("#testoiva").val("<?=$scrittaIva?>");
			}
			else 
			{
				$("#testoiva").val("SCONOSCIUTO");
			}
		}
	}
	else if (tiporiscossione == "CDS")
	{
		if ($("[name=iva]").val() == "0,00")
		{
			$("#testoiva").val("");
		}
		else if ($("[name=iva]").val() != "0,00")
		{
			switch ($("[name=tipobanca]").val())
			{
				case "BGSG":
					$("#testoiva").val("<?=$scrittaIva?>");
					break;
				case "BPI":
					$("#testoiva").val("<?=$scrittaIva?>");
					break;
				case "PAGATA":
					$("#testoiva").val("");
					break;
				case "":
					$("#testoiva").val("");
					break;
			}
		}
	}
}

function CambiaSocieta ()
{
	var societa = $("#societa").val();

	var strLink;
	/*if ($("[name=tiporiscossione]").val() == "PUB") 
		strLink = "stampa_fattura_pubblicita_generale_canone.php";
	else if ($("[name=tiporiscossione]").val() == "TOSAP") 
		strLink = "stampa_fattura_tosap_generale_canone.php";
	else
		strLink = "stampa_fattura_generale.php";*/
	strLink = "<?=$questaPagina?>";
	strLink += "?c=" + "<?=$c?>";
	strLink += "&a=" + "<?=$a?>";
	strLink += "&id=" + societa;
	strLink += "&tipocompetenza=" + $("[name=tipocompetenza]").val();
	strLink += "&sceltacomune=" + $("[name=sceltacomune]").val();
	strLink += "&tiporiscossione=" + $("[name=tiporiscossione]").val();
	strLink += "&spettabile=" + $("[name=spettabile]").val();

	window.location = strLink;
}

function CambiaRiscossione ()
{
	var societa = $("[name=societa]").val();
	if (societa == "")
	{
		alert ("Inserire il nome della Ditta");
		return false;
	}
	var tiporiscossione = $("[name=tiporiscossione]").val();
	if (tiporiscossione == "")
	{
		alert ("Inserire il tipo di riscossione");
		return false;
	}
	
	var strLink;
	/*if ($("[name=tiporiscossione]").val() == "PUB") 
		strLink = "stampa_fattura_pubblicita_generale_canone.php";
	else if ($("[name=tiporiscossione]").val() == "TOSAP") 
		strLink = "stampa_fattura_tosap_generale_canone.php";
	else
		strLink = "stampa_fattura_generale.php";*/
	strLink = "<?=$questaPagina?>";
	strLink += "?c=" + "<?=$c?>";
	strLink += "&a=" + "<?=$a?>";
	strLink += "&id=" + "<?=$id?>";
	strLink += "&tipocompetenza=" + $("[name=tipocompetenza]").val();
	strLink += "&societa=" + $("[name=societa]").val();
	strLink += "&sceltacomune=" + $("[name=sceltacomune]").val();
	strLink += "&tiporiscossione=" + $("[name=tiporiscossione]").val();
	strLink += "&spettabile=" + $("[name=spettabile]").val();
	location.href = strLink;
}

function CambiaComune ()
{
	var societa = $("[name=societa]").val();
	if (societa == "")
	{
		alert ("Inserire il nome della Ditta");
		return false;
	}
	var tiporiscossione = $("[name=tiporiscossione]").val();
	if (tiporiscossione == "")
	{
		alert ("Inserire il tipo di riscossione");
		return false;
	}
	if ($("[name=sceltacomune]").val() == "")
	{
		alert ("Il comune è necessario");
		return false;
	}
	var strLink;
	/*if ($("[name=tiporiscossione]").val() == "PUB") 
		strLink = "stampa_fattura_pubblicita_generale_aggio.php";
	else if ($("[name=tiporiscossione]").val() == "TOSAP") 
		strLink = "stampa_fattura_tosap_generale_canone.php";
	else
		strLink = "stampa_fattura_generale.php";*/
	strLink = "<?=$questaPagina?>";
	strLink += "?c=" + "<?=$c?>";
	strLink += "&a=" + "<?=$a?>";
	strLink += "&id=" + "<?=$id?>";
	strLink += "&tipocompetenza=" + $("[name=tipocompetenza]").val();
	strLink += "&societa=" + $("[name=societa]").val();
	strLink += "&sceltacomune=" + $("[name=sceltacomune]").val();
	strLink += "&tiporiscossione=" + $("[name=tiporiscossione]").val();
	strLink += "&spettabile=" + $("[name=spettabile]").val();
	location.href = strLink;
}

function CambioCig ()
{
	var societa = $("[name=societa]").val();
	if (societa == "")
	{
		alert ("Inserire il nome della Ditta");
		return false;
	}
	var tiporiscossione = $("[name=tiporiscossione]").val();
	if (tiporiscossione == "")
	{
		alert ("Inserire il tipo di riscossione");
		return false;
	}
	var sceltacomune = $("[name=sceltacomune]").val();
	if (sceltacomune == "")
	{
		alert ("Inserire il comune");
		return false;
	}
	
	var arrayJsCigId = new Array();
	var arrayJsCigTipiGest = new Array();
	//var arrayJsCigTipiTrib = new Array();
	<?php

	for ($zzz = 0; $zzz < count($arrayCigs[0]); $zzz++)
	{
		echo "\n	arrayJsCigId[" . $zzz . "] = '" . $arrayCigs[0][$zzz] . "';";
		echo "\n	arrayJsCigTipiGest[" . $zzz . "] = '" . $arrayCigs[1][$zzz] . "';";
		//echo "\n	arrayJsCigTipiTrib[" . $zzz . "] = '" . $arrayCigs[2][$zzz] . "';";
	}
	
	?>
	
	var sceltaCig = $("[name=daticig]").val();
	if (sceltaCig == "")
	{
		alert ("Inserire il CIG");
		return false;
	}

	var strLink = "<?=$questaPagina?>";
	strLink += "?c=" + "<?=$c?>";
	strLink += "&a=" + "<?=$a?>";
	strLink += "&id=" + "<?=$id?>";
	strLink += "&tipocompetenza=" + $("[name=tipocompetenza]").val();
	strLink += "&societa=" + $("[name=societa]").val();
	strLink += "&sceltacomune=" + $("[name=sceltacomune]").val();
	strLink += "&tiporiscossione=" + $("[name=tiporiscossione]").val();
	strLink += "&spettabile=" + $("[name=spettabile]").val();
	
	var esitoCig = "";
	for (var iii = 0; iii < arrayJsCigId.length; iii++)
	{
		if (arrayJsCigId[iii] == sceltaCig)
		{
			esitoCig = arrayJsCigTipiGest[iii];

			//if (esitoCig != arrayJsCigTipiGest[iii])
			{
				//alert ("bh " + arrayJsCigTipiGest[iii]);
				strLink += "&daticig=" + arrayJsCigId[iii];
				location.href = strLink;
				return;
			}
			/*switch (arrayJsCigTipiGest[iii])
			{
				case "SERVIZIO":
					strLink = "stampa_fattura_pubblicita_generale_servizio.php" + strLink;
					strLink += "&daticig=" + arrayJsCigId[iii];
					location.href = strLink;
					return;
					break;
				case "PAGATA_AD_AGGIO":
					strLink = "stampa_fattura_pubblicita_generale_aggio.php" + strLink;
					strLink += "&daticig=" + arrayJsCigId[iii];
					location.href = strLink;
					return;
					break;
				case "PAGATA_A_CANONE":
					//strLink = "stampa_fattura_pubblicita_generale_canone.php" + strLink;
					//location.href = strLink;
					break;
			}*/
			break;
		}
	}

	if (esitoCig == "")
		alert ("Attenzione: manca il tipo di gestione!!");
	
	$("[name=tipocig]").val(esitoCig);
	
	//CambiaTestoIva ();
	CambiaTendinaPagamento();
}

function CambioTipoFatt ()
{
	var societa = $("[name=societa]").val();
	if (societa == "")
	{
		alert ("Inserire il nome della Ditta");
		return false;
	}
	var tiporiscossione = $("[name=tiporiscossione]").val();
	if (tiporiscossione == "")
	{
		alert ("Inserire il tipo di riscossione");
		return false;
	}
	var sceltacomune = $("[name=sceltacomune]").val();
	if (sceltacomune == "")
	{
		alert ("Inserire il comune");
		return false;
	}
	var daticig = $("[name=daticig]").val();
	if (daticig == "")
	{
		alert ("Inserire il cig");
		return false;
	}
	var tipofattura = $("[name=tipofattura]").val();
	if (tipofattura == "")
	{
		alert ("Inserire il Tipo Fattura");
		return false;
	}

	var strLink = "<?=$questaPagina?>";
	strLink += "?c=" + "<?=$c?>";
	strLink += "&a=" + "<?=$a?>";
	strLink += "&id=" + "<?=$id?>";
	strLink += "&tipocompetenza=" + $("[name=tipocompetenza]").val();
	strLink += "&societa=" + $("[name=societa]").val();
	strLink += "&sceltacomune=" + $("[name=sceltacomune]").val();
	strLink += "&tiporiscossione=" + $("[name=tiporiscossione]").val();
	strLink += "&daticig=" + $("[name=daticig]").val();
	strLink += "&tipofattura=" + $("[name=tipofattura]").val();
	strLink += "&spettabile=" + $("[name=spettabile]").val();
	location.href = strLink;
	return true;
}

function ControllaNumeroFattura()
{
	var societa = $("[name=societa]").val();
	if (societa == "")
	{
		alert ("Inserire il nome della Ditta");
		return false;
	}
	var tiporiscossione = $("[name=tiporiscossione]").val();
	if (tiporiscossione == "")
	{
		alert ("Inserire il tipo di riscossione");
		return false;
	}
	var sceltacomune = $("[name=sceltacomune]").val();
	if (sceltacomune == "")
	{
		alert ("Inserire il comune");
		return false;
	}
	var daticig = $("[name=daticig]").val();
	if (daticig == "")
	{
		alert ("Inserire il cig");
		return false;
	}
	var tipofattura = $("[name=tipofattura]").val();
	if (tipofattura == "")
	{
		alert ("Inserire il Tipo Fattura");
		return false;
	}
	//if (CambioTipoFatt() == false) return false;
	
	var conbarra = $("[name=numerofattura]").val();

	//var operazione = "<?=$operazione?>";
	/*if (operazione == "CARICANUOVA" && conbarra != "<?=$myFatturaNuova->Fat_Numero?>")
	{
		alert ("Impossibile cambiare numero alla fattura; è possibile solo cambiare i dati");
		$("[name=numerofattura]").val("<?=$myFatturaNuova->Fat_Numero?>");
		return false;
	}*/

	if (conbarra == "")
	{
		alert ("Il numero di fattura non è presente");
		return false;
	}
	
	var splittobarra = conbarra.split("/");
	var lengthbarre = splittobarra.length;
	if (lengthbarre != 3)
	{
		alert ("Il numero di fattura non è corretto (esempio: 234/02/2015)");
		return false;
	}

	while (splittobarra[0].length < 3)
	{
		splittobarra[0] = "0" + splittobarra[0];
	}
	var nuovonumero = splittobarra[0] + "/" + splittobarra[1] + "/" + splittobarra[2]; 
	$("[name=numerofattura]").val(nuovonumero);

	$("[name=annofattura]").val(splittobarra[(lengthbarre-1)]);
	
	var arrayJsNumFatture = new Array();
	<?php

	for ($zzz = 0; $zzz < count($listaTutteFatture); $zzz++)
	{
		echo "\n	arrayJsNumFatture[" . $zzz . "] = '" . $listaTutteFatture[$zzz] . "';";
	}
	
	?>

	<?php if ($operazione != "CARICANUOVA") { ?>
	var numfatt = $("[name=numerofattura]").val();
	for (var iii = 0; iii < arrayJsNumFatture.length; iii++)
	{
		if (arrayJsNumFatture[iii] == numfatt)
		{
			alert ("Il numero di fattura " + numfatt + " è già stato inserito nel sistema");
			return false;
		}
	}
	<?php } ?>
	return true;
}

function CheckDataFattura ()
{
	if (ControllaNumeroFattura() == false) return false;
	if ($('[name=datafattura]').val() == "")
	{
		alert ("La data di fattura non è stata inserita");
		return false;
	}
		
	return CheckScriviData($('[name=datafattura]').val(), "datafattura");
}

function CtrlBilancio ()
{
	var bilancio = $("[name=annobilanciofattura]").val();
	if (bilancio == "")
	{
		alert ("L'anno di bilancio non è stato inserito");
		return false;
	}
}
function CtrlCompetenza ()
{
	var competenza = $("[name=annocompetenzafattura]").val();
	if (competenza == "")
	{
		alert ("L'anno di competenza non è stato inserito");
		return false;
	}
}

function AggiornaTestoFattura ()
{
<?php
if ($numerooperazione>0) echo "return true;";
else{
?>


	if (CheckDataFattura() == false) return false;
	if (CtrlBilancio() == false) return false;
	if (CtrlCompetenza() == false) return false;
	
	var testo1fatt = "<?=$tempDescr1?>";
	var testo2fatt = "<?=$tempDescr2?>";
	var testo3fatt = "<?=$tempDescr3?>";
	var testo4fatt = "<?=$tempDescr4?>";
	var testo5fatt = "<?=$tempDescr5?>";
	var testo6fatt = "<?=$tempDescr6?>";
	
	var testo7fatt = "<?=$tempDescr7?>";
	var testo8fatt = "<?=$tempDescr8?>";
	var testo9fatt = "<?=$tempDescr9?>";
	
	var testo10fatt = "<?=$tempDescr10?>";
	var testo11fatt = "<?=$tempDescr11?>";
	var testo12fatt = "<?=$tempDescr12?>";

	var testo1nota = "<?=$tempNotaCred1?>";
	var testo2nota = "<?=$tempNotaCred2?>";
	var testo3nota = "<?=$tempNotaCred3?>";
	var testo4nota = "<?=$tempNotaCred4?>";
	var testo5nota = "<?=$tempNotaCred5?>";
	var testo6nota = "<?=$tempNotaCred6?>";
	
	
	var tipofattura = $("[name=tipofattura]").val();
	if (tipofattura == "notacredito")
	{
		var totaleparziale = $("[name=totaleparziale]").val();
		var fatturacollegata = $("[name=fatturacollegata]").val();
		var datafattcollegata = $("[name=datafattcollegata]").val();

		if (totaleparziale != "") testo2nota = totaleparziale;
		if (fatturacollegata != "") testo4nota = " n. " + fatturacollegata;
		if (datafattcollegata != "")
		{
			var esito = CheckScriviData(datafattcollegata, "datafattcollegata");
			if (esito == false) return false;
			datafattcollegata = $("[name=datafattcollegata]").val();
			testo6nota = " del " + datafattcollegata + ".";
		}
		if (fatturacollegata == "")
		{
			testo3nota = "";
			testo4nota = "";
			testo5nota = "";
			testo6nota = "";
		}
		
		var descrtotalenota = testo1nota + testo2nota + testo3nota + testo4nota + testo5nota + testo6nota;
		$("#descrizionenota").val(descrtotalenota);
	}
	else
	{
		var tipocontr = $("[name=parTipo]").val();
		var numerocontr = $("[name=parNumero]").val();
		var datacontr = $("[name=parData]").val();

		switch (tipocontr)
		{
			case "CONTR": testo4fatt = "contratto"; break;
			case "DELGC": testo4fatt = "delibera G.C."; break;
			case "DELGM": testo4fatt = "delibera G.M."; break;
			case "DETER": testo4fatt = "determina"; break;
			case "CONVE": testo4fatt = "convenzione"; break;
			case "DISCI": testo4fatt = "disciplinare"; break;
			case "": testo4fatt = ""; break;
			default:  ("88tipo parametro inesistente: " + tipocontr); return; break;
		}
		
		if (numerocontr != "") testo5fatt = " n. " + numerocontr;
		if (datacontr != "")
		{
			var esito = CheckScriviData(datacontr, "parData");
			if (esito == false) return false;
			datacontr = $("[name=parData]").val();
			testo6fatt = " del " + datacontr;
		}
		if (numerocontr == "")
		{
			testo3fatt = ".";
			testo4fatt = "";
			testo5fatt = "";
			testo6fatt = "";
		}

		var descrtotalefatt = testo1fatt + testo2fatt + testo3fatt + testo4fatt + testo5fatt + testo6fatt;

		var tiporiscossione = $("[name=tiporiscossione]").val();

		if (tiporiscossione == "CDS")
		{
			var tipo2contr = $("[name=par2Tipo]").val();
			var numero2contr = $("[name=par2Numero]").val();
			var data2contr = $("[name=par2Data]").val();
	
			switch (tipo2contr)
			{
				case "CONTR": testo7fatt = " e contratto"; break;
				case "DELGC": testo7fatt = " e delibera G.C."; break;
				case "DELGM": testo7fatt = " e delibera G.M."; break;
				case "DETER": testo7fatt = " e determina"; break;
				case "CONVE": testo7fatt = " e convenzione"; break;
				case "DISCI": testo7fatt = " e disciplinare"; break;
				case "": testo7fatt = ""; break;
				default:  ("888tipo parametro inesistente: " + tipo2contr); return; break;
			}
			
			if (numero2contr != "") testo8fatt = " n. " + numero2contr;
			if (data2contr != "")
			{
				var esito = CheckScriviData(data2contr, "par2Data");
				if (esito == false) return false;
				data2contr = $("[name=par2Data]").val();
				testo9fatt = " del " + data2contr;
			}
			if (numero2contr == "")
			{
				testo7fatt = "";
				testo8fatt = "";
				testo9fatt = "";
			}
	
			
			var tipo3contr = $("[name=par3Tipo]").val();
			var numero3contr = $("[name=par3Numero]").val();
			var data3contr = $("[name=par3Data]").val();
	
			switch (tipo3contr)
			{
				case "CONTR": testo10fatt = " e contratto"; break;
				case "DELGC": testo10fatt = " e delibera G.C."; break;
				case "DELGM": testo10fatt = " e delibera G.M."; break;
				case "DETER": testo10fatt = " e determina"; break;
				case "CONVE": testo10fatt = " e convenzione"; break;
				case "DISCI": testo10fatt = " e disciplinare"; break;
				case "": testo10fatt = ""; break;
				default:  ("8888tipo parametro inesistente: " + tipo3contr); return; break;
			}
			
			if (numero3contr != "") testo11fatt = " n. " + numero3contr;
			if (data3contr != "")
			{
				var esito = CheckScriviData(data3contr, "par3Data");
				if (esito == false) return false;
				data3contr = $("[name=par3Data]").val();
				testo12fatt = " del " + data3contr;
			}
			if (numero3contr == "")
			{
				testo10fatt = "";
				testo11fatt = "";
				testo12fatt = "";
			}
			
			descrtotalefatt += testo7fatt + testo8fatt + testo9fatt;
			descrtotalefatt += testo10fatt + testo11fatt + testo12fatt;
			descrtotalefatt += ".";
		}
		$("#fattura1").val(descrtotalefatt);
	}
	<?php
    }
    ?>

}
/*
function AggiornaContratto ()
{
	if (CheckDataFattura() == false) return false;
	var numerocontr = $("[name=parNumero]").val();
	var datacontr = $("[name=parData]").val();

	if (numerocontr == "")
	{
		//alert ("Il numero del contratto non è presente");
		//return false;
	}
	if (datacontr == "")
	{
		//alert ("La data del contratto non è presente");
		//return false;
	}

	var testo1contratto = "<?=$descrizione1fattura?>";
	var testo2contratto = "<?=$descrizione2fattura?>";
	var testo3_1contratto = "<?=$descrizione3_1fattura?>";
	var testo3_2contratto = "<?=$descrizione3_2fattura?>";
	var testo3_3contratto = "<?=$descrizione3_3fattura?>";
	//testocontratto = "<?=$descrizione1fattura?><?=$descrizione2fattura?><?=$descrizione3fattura?>";
	//$descrizionefattura = $descrizione1fattura . $descrizione2fattura . $descrizione3fattura;
	//$descrizioneCompletaFattura = $descrizione1fattura . $descrizione2fattura . $descrizione3fattura . " " . $descrizione4fattura;

	if (numerocontr != "")
	{
		testo3_2contratto = " n. " + numerocontr;
	}
	if (datacontr != "")
	{
		testo3_3contratto = " del " + datacontr + ".";

		var esito = CheckScriviData(datacontr, "parData");
		if (esito == false) return false;
		datacontr = esito;
	}
	if (numerocontr == "")
	{
		testo3_1contratto = "";
		testo3_2contratto = "";
		testo3_3contratto = "";
	}

	var descrtotale = testo1contratto + testo2contratto + testo3_1contratto + testo3_2contratto + testo3_3contratto;

	$("#descrizionefattura").val(descrtotale);
}
*/
function AggiornaPeriodo (value)
{
	<?php
if ($numerooperazione>0) echo "return true;";
else{
?>
	if (AggiornaTestoFattura() == false) return false;
	
	var daperiodo = $("[name=periodoDa]").val();
	var aperiodo = $("[name=periodoA]").val();
	
	var testo4_1contratto = "";

	var tiporiscossione = $("[name=tiporiscossione]").val();

	if (tiporiscossione == "CDS")
	{
		var tipogestionecds = $("[name=tipogestionecds]").val();
		switch (tipogestionecds)
		{
			case "":
				alert ("Scegliere il tipo di gestione");
				return false;
				break;
			case "RISCOSSIONE": testo4_1contratto = "<?=$testoCdsItaliani?>"; break;
			case "ESTERO": testo4_1contratto = "<?=$testoCdsEsteri?>"; break;
			case "FISSO": testo4_1contratto = "<?=$testoCdsFisso?>"; break;
			case "MOBILE": testo4_1contratto = "<?=$testoCdsMobile?>"; break;
		}
	}
	else testo4_1contratto = "Rif. riscossioni";
	
	var testo4_Tcontratto = "<?=$descrizione4fattura?>";
	
	var testo4_2contratto = "";
	var testo4_3contratto = "";

	var descrizione4fattura;

	var tipofattura = $("[name=tipofattura]").val();

	if (tipofattura != "notacredito")
	{
		if (value == 1)
		{
			var esito = CheckScriviData(daperiodo, "periodoDa");
			if (esito == false) return false;
			daperiodo = $("[name=periodoDa]").val();;
		}
		if (value == 2)
		{
			var esito = CheckScriviData(aperiodo, "periodoA");
			if (esito == false) return false;
			aperiodo = $("[name=periodoA]").val();;
		}
		
		if (daperiodo != "")
		{
			testo4_2contratto = " dal " + daperiodo;
		}
		if (aperiodo != "")
		{
			testo4_3contratto = " al " + aperiodo + ".";
		}
		if (daperiodo != "" || aperiodo != "")
		{
			descrizione4fattura = testo4_1contratto + testo4_2contratto + testo4_3contratto;
		}
		else
		{
			descrizione4fattura = testo4_Tcontratto;
		}
		$("#descrizione4fattura").val(descrizione4fattura);
	}
	return true;
<?php } ?>
}

function ControllaSomme ()
{
	if (AggiornaPeriodo(1) == false) return false;
	if (AggiornaPeriodo(2) == false) return false;

	var tiporiscossione = $("[name=tiporiscossione]").val();
	var tipocig = $("[name=tipocig]").val();
	
	var varErrore = false;

	if (tiporiscossione == "TOSAP" && tipocig == "PAGATA_A_CANONE")
	{
		var ordinario = $("#tosapcanoneordinario").val();
		var temporaneo = $("#tosapcanonetemporaneo").val();
		var impostabollo = $("#tosapcanoneimposta").val();
		var totalefattura = $("#tosapcanonetotale").val();

		var preordinario = parseFloat(ordinario.replace(",", "."));
		var pretemporaneo = parseFloat(temporaneo.replace(",", "."));
		
		//if (totalefattura == "")
		{
			if (ordinario == "") preordinario = 0;
			if (temporaneo == "") pretemporaneo = 0;
			totalefattura = preordinario + pretemporaneo;
		}
		
		ordinario = MostraNumeriCon2CifreDecimali(ordinario);
		temporaneo = MostraNumeriCon2CifreDecimali(temporaneo);
		//affissioni = MostraNumeriCon2CifreDecimali(affissioni);
		//rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		
		$("#tosapcanoneordinario").val(ordinario);
		$("#tosapcanonetemporaneo").val(temporaneo);
		$("#tosapcanoneimposta").val(impostabollo);
		$("#tosapcanonetotale").val(totalefattura);
		
		ordinario = parseFloat(ordinario.replace(",", "."));
		temporaneo = parseFloat(temporaneo.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
	
		var difTot = totalefattura - ordinario - temporaneo;// - affissioni;// - rimborsi;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagtosapcanone").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagtosapcanone").attr("title", "valore totale fattura errato (totale != ordinario+temporaneo)");
			varErrore = true;
		}
		else
		{
			$("#flagtosapcanone").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagtosapcanone").attr("title", "OK");
		}
	
		if ($("#tosapcanoneimposta").val() == "0,00") $("#marcabollo").hide();
		else $("#marcabollo").show();
	}
	else if (tiporiscossione == "PUB" && tipocig == "PAGATA_A_CANONE")
	{
		var ordinario = $("#pubcanoneordinario").val();
		var temporaneo = $("#pubcanonetemporaneo").val();
		var affissioni = $("#pubcanoneaffissioni").val();
		var impostabollo = $("#pubcanoneimposta").val();
		var totalefattura = $("#pubcanonetotale").val();

		var preordinario = parseFloat(ordinario.replace(",", "."));
		var pretemporaneo = parseFloat(temporaneo.replace(",", "."));
		var preaffissioni = parseFloat(affissioni.replace(",", "."));
		
		//if (totalefattura == "")
		{
			if (ordinario == "") preordinario = 0;
			if (temporaneo == "") pretemporaneo = 0;
			if (affissioni == "") preaffissioni = 0;
			totalefattura = preordinario + pretemporaneo + preaffissioni;
		}
		
		ordinario = MostraNumeriCon2CifreDecimali(ordinario);
		temporaneo = MostraNumeriCon2CifreDecimali(temporaneo);
		affissioni = MostraNumeriCon2CifreDecimali(affissioni);
		//rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		
		$("#pubcanoneordinario").val(ordinario);
		$("#pubcanonetemporaneo").val(temporaneo);
		$("#pubcanoneaffissioni").val(affissioni);
		$("#pubcanoneimposta").val(impostabollo);
		$("#pubcanonetotale").val(totalefattura);
		
		ordinario = parseFloat(ordinario.replace(",", "."));
		temporaneo = parseFloat(temporaneo.replace(",", "."));
		affissioni = parseFloat(affissioni.replace(",", "."));
		//rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));

		var difTot = totalefattura - ordinario - temporaneo - affissioni;// - rimborsi;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagpubcanone").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubcanone").attr("title", "valore totale fattura errato (totale != ordinario+temporaneo+affissioni)");
			varErrore = true;
		}
		else
		{
			$("#flagpubcanone").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubcanone").attr("title", "OK");
		}

		if ($("#pubcanoneimposta").val() == "0,00") $("#marcabollo").hide();
		else $("#marcabollo").show();
	}
	else if (tiporiscossione == "PUB" && tipocig == "PAGATA_AD_AGGIO")
	{
		var ordinario = $("#pubaggioordinario").val();
		var temporaneo = $("#pubaggiotemporaneo").val();
		var affissioni = $("#pubaggioaffissioni").val();
		var totaleimponibile = $("#pubaggioimponibile").val();
		var percentualeiva = $("#pubaggioperciva").val();
		var iva = $("#pubaggioiva").val();
		var totalefattura = $("#pubaggiototale").val();

		var preordinario = parseFloat(ordinario.replace(",", "."));
		var pretemporaneo = parseFloat(temporaneo.replace(",", "."));
		var preaffissioni = parseFloat(affissioni.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));
		
		//if (totaleimponibile == "")
		{
			if (ordinario == "") preordinario = 0;
			if (temporaneo == "") pretemporaneo = 0;
			if (affissioni == "") preaffissioni = 0;
			totaleimponibile = preordinario + pretemporaneo + preaffissioni;
		}
		
		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}
		
		//if (totalefattura == "")
		{
			//if (totaleimponibile == "") pretotaleimponibile = 0;
			var preiva = iva;
			totalefattura = pretotaleimponibile + preiva;
		}

		ordinario = MostraNumeriCon2CifreDecimali(ordinario);
		temporaneo = MostraNumeriCon2CifreDecimali(temporaneo);
		affissioni = MostraNumeriCon2CifreDecimali(affissioni);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);

		$("#pubaggioordinario").val(ordinario);
		$("#pubaggiotemporaneo").val(temporaneo);
		$("#pubaggioaffissioni").val(affissioni);
		$("#pubaggioimponibile").val(totaleimponibile);
		$("#pubaggioperciva").val(percentualeiva);
		$("#pubaggioiva").val(iva);
		$("#pubaggiototale").val(totalefattura);
		
		ordinario = parseFloat(ordinario.replace(",", "."));
		temporaneo = parseFloat(temporaneo.replace(",", "."));
		affissioni = parseFloat(affissioni.replace(",", "."));
		//spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		//impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		//totaleadoversi = parseFloat(totaleadoversi.replace(",", "."));

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagpubaggioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubaggioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagpubaggioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubaggioiva").attr("title", "OK");
		}
		
		var difImp = totaleimponibile - ordinario - temporaneo - affissioni;// - spese;
		if (difImp > 0.005 || difImp < -0.005)
		{
			$("#flagpubaggioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubaggioimponibile").attr("title", "valore totale imponibile errato (imponibile  diverso da  ordinario+temporaneo+affissioni)");
			varErrore = true;
		}
		else
		{
			$("#flagpubaggioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubaggioimponibile").attr("title", "OK");
		}

		var difTot = totalefattura - totaleimponibile - iva;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagpubaggiototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubaggiototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva)");
			varErrore = true;
		}
		else
		{
			$("#flagpubaggiototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubaggiototale").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "PUB" && tipocig == "SERVIZIO")
	{
		var ordinario = $("#pubservizioordinario").val();
		var temporaneo = $("#pubserviziotemporaneo").val();
		var affissioni = $("#pubservizioaffissioni").val();
		//var impostabollo = $("#pubservizioimposta").val();
		var totaleimponibile = $("#pubservizioimponibile").val();
		var percentualeiva = $("#pubservizioperciva").val();
		var iva = $("#pubservizioiva").val();
		var totalefattura = $("#pubserviziototale").val();
		var totaledoversi = $("#pubserviziodoversi").val();

		var preordinario = parseFloat(ordinario.replace(",", "."));
		var pretemporaneo = parseFloat(temporaneo.replace(",", "."));
		var preaffissioni = parseFloat(affissioni.replace(",", "."));
		
		//if (totalefattura == "")
		{
			if (ordinario == "") preordinario = 0;
			if (temporaneo == "") pretemporaneo = 0;
			if (affissioni == "") preaffissioni = 0;
			totaleimponibile = preordinario + pretemporaneo + preaffissioni;
		}
		
		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}
		
		ordinario = MostraNumeriCon2CifreDecimali(ordinario);
		temporaneo = MostraNumeriCon2CifreDecimali(temporaneo);
		affissioni = MostraNumeriCon2CifreDecimali(affissioni);
		//rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		//impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);
		
		$("#pubservizioordinario").val(ordinario);
		$("#pubserviziotemporaneo").val(temporaneo);
		$("#pubservizioaffissioni").val(affissioni);
		//$("#pubservizioimposta").val(impostabollo);
		$("#pubservizioimponibile").val(totaleimponibile);
		$("#pubservizioperciva").val(percentualeiva);
		$("#pubservizioiva").val(iva);
		$("#pubserviziototale").val(totalefattura);
		$("#pubserviziodoversi").val(totaledoversi);
		
		ordinario = parseFloat(ordinario.replace(",", "."));
		temporaneo = parseFloat(temporaneo.replace(",", "."));
		affissioni = parseFloat(affissioni.replace(",", "."));
		//rimborsi = parseFloat(rimborsi.replace(",", "."));
		//impostabollo = parseFloat(impostabollo.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - ordinario - temporaneo - affissioni;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagpubservizioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubservizioimponibile").attr("title", "valore imponibile errato (totale  diverso da  ordinario+temporaneo+affissioni)");
			varErrore = true;
		}
		else
		{
			$("#flagpubservizioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubservizioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagpubservizioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubservizioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagpubservizioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubservizioiva").attr("title", "OK");
		}

		var difTot = totalefattura - totaleimponibile - iva;// - rimborsi;// - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagpubserviziototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubserviziototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva)");
			varErrore = true;
		}
		else
		{
			$("#flagpubserviziototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubserviziototale").attr("title", "OK");
		}
		
		if ("<?=$splitPayment?>" == "Y")
		{
			var difDoversi = totalefattura - totaledoversi - iva;
			if (difDoversi > 0.005 || difDoversi < -0.005)
			{
				//alert (dif);
				$("#flagpubserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagpubserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
				varErrore = true;
			}
			else
			{
				$("#flagpubserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagpubserviziodoversi").attr("title", "OK");
			}
		}
		else
		{
			var difDoversi = totalefattura - totaledoversi;
			if (difDoversi > 0.005 || difDoversi < -0.005)
			{
				//alert (dif);
				$("#flagpubserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagpubserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
				varErrore = true;
			}
			else
			{
				$("#flagpubserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagpubserviziodoversi").attr("title", "OK");
			}
		}

		/*if ($("#pubservizioimposta").val() == "0,00") $("#marcabollo").hide();
		else $("#marcabollo").show();*/
	}
	else if (tiporiscossione == "CDS" && tipocig == "PAGATA_A_CANONE")
	{
		var importo = $("#cdscanoneimporto").val();
		var spese = $("#cdscanonespese").val();
		var totaleimponibile = $("#cdscanoneimponibile").val();
		var percentualeiva = $("#cdscanoneperciva").val();
		var iva = $("#cdscanoneiva").val();
		var rimborsi = $("#cdscanonerimborsi").val();
		var impostabollo = $("#cdscanoneimposta").val();
		var totalefattura = $("#cdscanonetotale").val();
		var totaledoversi = $("#cdscanonedoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));




			if (importo == "") preimporto = 0;
			if (spese == "") prespese = 0;
			if (rimborsi == "") {
				rimborsi = 0;
				prerimborsi = 0;
				preimpostabollo = 0;
				impostabollo = 0;

			}
			totaleimponibile = preimporto + prespese;

		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}

		/*var prerimborsi = parseFloat(rimborsi.replace(",", "."));
		var preimpostabollo = parseFloat(impostabollo.replace(",", "."));
		
		//if (totalefattura == "")
		{
			//if (totaleimponibile == "") pretotaleimponibile = 0;
			var preiva = iva;
			if (rimborsi == "") prerimborsi = 0;
			if (impostabollo == "") preimpostabollo = 0;
			totalefattura = totaleimponibile + preiva + prerimborsi + preimpostabollo;
			//totaledoversi = totalefattura;// - preiva;
		}*/
		
		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);

		if(rimborsi > <?php  echo str_replace('.',',', $limImposta)  ?>) $("#cdscanoneimposta").val('2,00');
		else $("#cdscanoneimposta").val('0,00');

		impostabollo = $("#cdscanoneimposta").val();


		$("#cdscanoneimporto").val(importo);
		$("#cdscanonespese").val(spese);
		$("#cdscanoneimponibile").val(totaleimponibile);
		$("#cdscanoneperciva").val(percentualeiva);
		$("#cdscanoneiva").val(iva);
		$("#cdscanonerimborsi").val(rimborsi);
		$("#cdscanoneimposta").val(impostabollo);
		$("#cdscanonetotale").val(totalefattura);
		$("#cdscanonedoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagcdscanoneimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdscanoneimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagcdscanoneimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdscanoneimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagcdscanoneiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdscanoneiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagcdscanoneiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdscanoneiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagcdscanoneimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdscanoneimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagcdscanoneimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdscanoneimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagcdscanoneimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdscanoneimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagcdscanoneimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdscanoneimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagcdscanonetotale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdscanonetotale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi+impostabollo)");
			varErrore = true;
		}
		else
		{
			$("#flagcdscanonetotale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdscanonetotale").attr("title", "OK");
		}

		if ("<?=$splitPayment?>" == "Y")
		{
			var difDoversi = totalefattura - totaledoversi - iva;
			if (difDoversi > 0.005 || difDoversi < -0.005)
			{
				//alert (dif);
				$("#flagcdscanonedoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdscanonedoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
				varErrore = true;
			}
			else
			{
				$("#flagcdscanonedoversi").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdscanonedoversi").attr("title", "OK");
			}
		}
		else
		{
			var difDoversi = totalefattura - totaledoversi;
			if (difDoversi > 0.005 || difDoversi < -0.005)
			{
				//alert (dif);
				$("#flagcdscanonedoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdscanonedoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
				varErrore = true;
			}
			else
			{
				$("#flagcdscanonedoversi").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdscanonedoversi").attr("title", "OK");
			}
		}
	}
	else if (tiporiscossione == "CDS" && tipocig == "PAGATA_AD_AGGIO")
	{
		var importo = $("#cdsaggioimporto").val();
		var spese = $("#cdsaggiospese").val();
		var totaleimponibile = $("#cdsaggioimponibile").val();
		var percentualeiva = $("#cdsaggioperciva").val();
		var iva = $("#cdsaggioiva").val();
		var rimborsi = $("#cdsaggiorimborsi").val();
		var impostabollo = $("#cdsaggioimposta").val();
		var totalefattura = $("#cdsaggiototale").val();
		var totaledoversi = $("#cdsaggiodoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));

			if (importo == "") preimporto = 0;
			if (rimborsi == "") {
				rimborsi = 0;
				prerimborsi = 0;
				preimpostabollo = 0;
				impostabollo = 0;

			}else{
				rimborsi = parseFloat(rimborsi.replace(",", "."));
				impostabollo = parseFloat(impostabollo.replace(",", "."));
			}




			if (spese == "") prespese = 0;
			totaleimponibile = preimporto + prespese;

			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
			var preiva = iva;





		if(rimborsi > <?php  echo $limImposta ?>) impostabollo = 2;
		else impostabollo=0;

		totalefattura = totaleimponibile + preiva + rimborsi + impostabollo;
		totaledoversi = totaleimponibile + preiva + rimborsi + impostabollo;

		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);



		$("#cdsaggioimporto").val(importo);
		$("#cdsaggiospese").val(spese);
		$("#cdsaggioimponibile").val(totaleimponibile);
		$("#cdsaggioperciva").val(percentualeiva);
		$("#cdsaggioiva").val(iva);
		$("#cdsaggiorimborsi").val(rimborsi);
		$("#cdsaggioimposta").val(impostabollo);
		$("#cdsaggiototale").val(totalefattura);
		$("#cdsaggiodoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagcdsaggioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdsaggioimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagcdsaggioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdsaggioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagcdsaggioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdsaggioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagcdsaggioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdsaggioiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagcdsaggioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdsaggioimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagcdsaggioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdsaggioimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagcdsaggioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdsaggioimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagcdsaggioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdsaggioimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagcdsaggiototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdsaggiototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi)");
			varErrore = true;
		}
		else
		{
			$("#flagcdsaggiototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdsaggiototale").attr("title", "OK");
		}

		if ($("[name=tipobanca]").val() != "")
		{
			var splitYsPayment = "Y";
			switch ($("[name=tipobanca]").val())
			{
				case "BGSG": splitYsPayment = "Y"; break;
				case "BPI": splitYsPayment = "Y"; break;
				case "PAGATA": splitYsPayment = "N"; break;
			}
			if (splitYsPayment == "Y")
			{
				var difDoversi = totalefattura - totaledoversi - iva;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagcdsaggiodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagcdsaggiodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
					varErrore = true;
				}
				else
				{
					$("#flagcdsaggiodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagcdsaggiodoversi").attr("title", "OK");
				}
			}
			else
			{
				var difDoversi = totalefattura - totaledoversi;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagcdsaggiodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagcdsaggiodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
					varErrore = true;
				}
				else
				{
					$("#flagcdsaggiodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagcdsaggiodoversi").attr("title", "OK");
				}
			}
		}
		else
		{
			$("#flagcdsaggiodoversi").attr("src", "/gitco2/immagini/puntointerrogativo.jpg");
			$("#flagcdsaggiodoversi").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "CDS" && tipocig == "SERVIZIO")
	{
		var importo = $("#cdsservizioimporto").val();
		var spese = $("#cdsserviziospese").val();
		var totaleimponibile = $("#cdsservizioimponibile").val();
		var percentualeiva = $("#cdsservizioperciva").val();
		var iva = $("#cdsservizioiva").val();
		var rimborsi = $("#cdsserviziorimborsi").val();
		var impostabollo = $("#cdsservizioimposta").val();
		var totalefattura = $("#cdsserviziototale").val();
		var totaledoversi = $("#cdsserviziodoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));

		if (importo == "") preimporto = 0;
		if (rimborsi == "") {
			rimborsi = 0;
			prerimborsi = 0;
			preimpostabollo = 0;
			impostabollo = 0;

		}else{
			rimborsi = parseFloat(rimborsi.replace(",", "."));
			impostabollo = parseFloat(impostabollo.replace(",", "."));
		}




		if (spese == "") prespese = 0;
		totaleimponibile = preimporto + prespese;

		var pretotaleimponibile = totaleimponibile;
		if (percentualeiva == "") prepercentualeiva = 0;
		iva = pretotaleimponibile * prepercentualeiva / 100;
		var preiva = iva;





		if(rimborsi > <?php  echo $limImposta ?>) impostabollo = 2;
		else impostabollo=0;

		totalefattura = totaleimponibile + preiva + rimborsi + impostabollo;
		totaledoversi = totaleimponibile + rimborsi + impostabollo;

		
		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);


		$("#cdsservizioimporto").val(importo);
		$("#cdsserviziospese").val(spese);
		$("#cdsservizioimponibile").val(totaleimponibile);
		$("#cdsservizioperciva").val(percentualeiva);
		$("#cdsservizioiva").val(iva);
		$("#cdsserviziorimborsi").val(rimborsi);
		$("#cdsservizioimposta").val(impostabollo);
		$("#cdsserviziototale").val(totalefattura);
		$("#cdsserviziodoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagcdsservizioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdsservizioimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagcdsservizioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdsservizioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagcdsservizioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdsservizioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagcdsservizioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdsservizioiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagcdsservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdsservizioimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagcdsservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdsservizioimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagcdsservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagcdsservizioimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagcdsservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagcdsservizioimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagcdsserviziototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagcdsserviziototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi)");
			varErrore = true;
		}
		else
		{
			$("#flagcdsserviziototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagcdsserviziototale").attr("title", "OK");
		}
		
		if ($("[name=tipobanca]").val() != "")
		{
			var splitYsPayment = "Y";
			switch ($("[name=tipobanca]").val())
			{
				case "BGSG": splitYsPayment = "Y"; break;
				case "BPI": splitYsPayment = "Y"; break;
				case "PAGATA": splitYsPayment = "N"; break;
			}
			if (splitYsPayment == "Y")
			{
				var difDoversi = totalefattura - totaledoversi - iva;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					$("#flagcdsserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagcdsserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
					varErrore = true;
				}
				else
				{
					$("#flagcdsserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagcdsserviziodoversi").attr("title", "OK");
				}
			}
			else
			{
				var difDoversi = totalefattura - totaledoversi;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					$("#flagcdsserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagcdsserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
					varErrore = true;
				}
				else
				{
					$("#flagcdsserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagcdsserviziodoversi").attr("title", "OK");
				}
			}
		}
		else
		{
			$("#flagcdsserviziodoversi").attr("src", "/gitco2/immagini/puntointerrogativo.jpg");
			$("#flagcdsserviziodoversi").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "TARI" && tipocig == "PAGATA_AD_AGGIO")
	{
		var importo = $("#tariaggioimporto").val();
		var spese = $("#tariaggiospese").val();
		var totaleimponibile = $("#tariaggioimponibile").val();
		var percentualeiva = $("#tariaggioperciva").val();
		var iva = $("#tariaggioiva").val();
		var rimborsi = $("#tariaggiorimborsi").val();
		var impostabollo = $("#tariaggioimposta").val();
		var totalefattura = $("#tariaggiototale").val();
		var totaledoversi = $("#tariaggiodoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));
		
		//if (totaleimponibile == "")
		{
			if (importo == "") preimporto = 0;
			if (spese == "") prespese = 0;
			totaleimponibile = preimporto + prespese;
		}
		
		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}
		
		var prerimborsi = parseFloat(rimborsi.replace(",", "."));
		var preimpostabollo = parseFloat(impostabollo.replace(",", "."));
		
		//if (totalefattura == "")
		{
			//if (totaleimponibile == "") pretotaleimponibile = 0;
			var preiva = iva;
			if (rimborsi == "") prerimborsi = 0;
			if (impostabollo == "") preimpostabollo = 0;
			totalefattura = totaleimponibile + preiva + prerimborsi + preimpostabollo;
			totaledoversi = totalefattura;// - preiva;
		}
		
		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);
		
		$("#tariaggioimporto").val(importo);
		$("#tariaggiospese").val(spese);
		$("#tariaggioimponibile").val(totaleimponibile);
		$("#tariaggioperciva").val(percentualeiva);
		$("#tariaggioiva").val(iva);
		$("#tariaggiorimborsi").val(rimborsi);
		$("#tariaggioimposta").val(impostabollo);
		$("#tariaggiototale").val(totalefattura);
		$("#tariaggiodoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagtariaggioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagtariaggioimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagtariaggioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagtariaggioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagtariaggioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagtariaggioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagtariaggioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagtariaggioiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagtariaggioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagtariaggioimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagtariaggioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagtariaggioimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagtariaggioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagtariaggioimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagtariaggioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagtariaggioimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagtariaggiototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagtariaggiototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi)");
			varErrore = true;
		}
		else
		{
			$("#flagtariaggiototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagtariaggiototale").attr("title", "OK");
		}

		if ($("[name=tipobanca]").val() != "PAGATA") $("[name=tipobanca]").val("PAGATA");

		if (1)
		{
			var splitYsPayment = "Y";
			switch ($("[name=tipobanca]").val())
			{
				case "BGSG": splitYsPayment = "Y"; break;
				case "BPI": splitYsPayment = "Y"; break;
				case "PAGATA": splitYsPayment = "N"; break;
			}
			if (splitYsPayment == "Y")
			{
				var difDoversi = totalefattura - totaledoversi - iva;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagtariaggiodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagtariaggiodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
					varErrore = true;
				}
				else
				{
					$("#flagtariaggiodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagtariaggiodoversi").attr("title", "OK");
				}
			}
			else
			{
				var difDoversi = totalefattura - totaledoversi;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagtariaggiodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagtariaggiodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
					varErrore = true;
				}
				else
				{
					$("#flagtariaggiodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagtariaggiodoversi").attr("title", "OK");
				}
			}
		}
		else
		{
			$("#flagtariaggiodoversi").attr("src", "/gitco2/immagini/puntointerrogativo.jpg");
			$("#flagtariaggiodoversi").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "TARI" && tipocig == "SERVIZIO")
	{
		var importo = $("#tariservizioimporto").val();
		var spese = $("#tariserviziospese").val();
		var totaleimponibile = $("#tariservizioimponibile").val();
		var percentualeiva = $("#tariservizioperciva").val();
		var iva = $("#tariservizioiva").val();
		var rimborsi = $("#tariserviziorimborsi").val();
		var impostabollo = $("#tariservizioimposta").val();
		var totalefattura = $("#tariserviziototale").val();
		var totaledoversi = $("#tariserviziodoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));
		
		//if (totaleimponibile == "")
		{
			if (importo == "") preimporto = 0;
			if (spese == "") prespese = 0;
			totaleimponibile = preimporto + prespese;
		}
		
		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}
		
		var prerimborsi = parseFloat(rimborsi.replace(",", "."));
		var preimpostabollo = parseFloat(impostabollo.replace(",", "."));
		
		//if (totalefattura == "")
		{
			//if (totaleimponibile == "") pretotaleimponibile = 0;
			var preiva = iva;
			if (rimborsi == "") prerimborsi = 0;
			if (impostabollo == "") preimpostabollo = 0;
			totalefattura = totaleimponibile + preiva + prerimborsi + preimpostabollo;
			totaledoversi = totalefattura - preiva;
		}
		
		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);
		
		$("#tariservizioimporto").val(importo);
		$("#tariserviziospese").val(spese);
		$("#tariservizioimponibile").val(totaleimponibile);
		$("#tariservizioperciva").val(percentualeiva);
		$("#tariservizioiva").val(iva);
		$("#tariserviziorimborsi").val(rimborsi);
		$("#tariservizioimposta").val(impostabollo);
		$("#tariserviziototale").val(totalefattura);
		$("#tariserviziodoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagtariservizioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagtariservizioimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagtariservizioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagtariservizioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagtariservizioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagtariservizioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagtariservizioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagtariservizioiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagtariservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagtariservizioimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagtariservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagtariservizioimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagtariservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagtariservizioimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagtariservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagtariservizioimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagtariserviziototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagtariserviziototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi)");
			varErrore = true;
		}
		else
		{
			$("#flagtariserviziototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagtariserviziototale").attr("title", "OK");
		}

		if ($("[name=tipobanca]").val() == "PAGATA") $("[name=tipobanca]").val("");

		if ($("[name=tipobanca]").val() != "")
		{
			var splitYsPayment = "Y";
			switch ($("[name=tipobanca]").val())
			{
				case "BGSG": splitYsPayment = "Y"; break;
				case "BPI": splitYsPayment = "Y"; break;
				case "PAGATA": splitYsPayment = "N"; break;
			}
			if (splitYsPayment == "Y")
			{
				var difDoversi = totalefattura - totaledoversi - iva;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagtariserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagtariserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
					varErrore = true;
				}
				else
				{
					$("#flagtariserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagtariserviziodoversi").attr("title", "OK");
				}
			}
			else
			{
				var difDoversi = totalefattura - totaledoversi;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagtariserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagtariserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
					varErrore = true;
				}
				else
				{
					$("#flagtariserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagtariserviziodoversi").attr("title", "OK");
				}
			}
		}
		else
		{
			$("#flagtariserviziodoversi").attr("src", "/gitco2/immagini/puntointerrogativo.jpg");
			$("#flagtariserviziodoversi").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "PARK")
	{
		var ordinario = $("#parkaggioordinario").val();
		var temporaneo = $("#parkaggiotemporaneo").val();
		var totaleimponibile = $("#parkaggioimponibile").val();
		var percentualeiva = $("#parkaggioperciva").val();
		var iva = $("#parkaggioiva").val();
		var totalefattura = $("#parkaggiototale").val();

		var preordinario = parseFloat(ordinario.replace(",", "."));
		var pretemporaneo = parseFloat(temporaneo.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));

		if (ordinario == "") preordinario = 0;
		if (temporaneo == "") pretemporaneo = 0;
		totaleimponibile = preordinario + pretemporaneo;
		var pretotaleimponibile = totaleimponibile;
		if (percentualeiva == "") prepercentualeiva = 0;
		iva = pretotaleimponibile * prepercentualeiva / 100;
		var preiva = iva;
		totalefattura = pretotaleimponibile + preiva;

		ordinario = MostraNumeriCon2CifreDecimali(ordinario);
		temporaneo = MostraNumeriCon2CifreDecimali(temporaneo);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);

		$("#parkaggioordinario").val(ordinario);
		$("#parkaggiotemporaneo").val(temporaneo);
		$("#parkaggioimponibile").val(totaleimponibile);
		$("#parkaggioperciva").val(percentualeiva);
		$("#parkaggioiva").val(iva);
		$("#parkaggiototale").val(totalefattura);
		$("#parkaggiodoversi").val(totaleimponibile);


		ordinario = parseFloat(ordinario.replace(",", "."));
		temporaneo = parseFloat(temporaneo.replace(",", "."));
		//spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		//impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		//totaleadoversi = parseFloat(totaleadoversi.replace(",", "."));

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagpubaggioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubaggioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagpubaggioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubaggioiva").attr("title", "OK");
		}

		var difImp = totaleimponibile - ordinario - temporaneo;// - spese;
		if (difImp > 0.005 || difImp < -0.005)
		{
			$("#flagpubaggioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubaggioimponibile").attr("title", "valore totale imponibile errato (imponibile  diverso da  ordinario+temporaneo)");
			varErrore = true;
		}
		else
		{
			$("#flagpubaggioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubaggioimponibile").attr("title", "OK");
		}

		var difTot = totalefattura - totaleimponibile - iva;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagpubaggiototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagpubaggiototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva)");
			varErrore = true;
		}
		else
		{
			$("#flagpubaggiototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagpubaggiototale").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "ICI" && tipocig == "PAGATA_AD_AGGIO")
	{
		var importo = $("#iciaggioimporto").val();
		var spese = $("#iciaggiospese").val();
		var totaleimponibile = $("#iciaggioimponibile").val();
		var percentualeiva = $("#iciaggioperciva").val();
		var iva = $("#iciaggioiva").val();
		var rimborsi = $("#iciaggiorimborsi").val();
		var impostabollo = $("#iciaggioimposta").val();
		var totalefattura = $("#iciaggiototale").val();
		var totaledoversi = $("#iciaggiodoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));
		
		//if (totaleimponibile == "")
		{
			if (importo == "") preimporto = 0;
			if (spese == "") prespese = 0;
			totaleimponibile = preimporto + prespese;
		}
		
		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}
		
		var prerimborsi = parseFloat(rimborsi.replace(",", "."));
		var preimpostabollo = parseFloat(impostabollo.replace(",", "."));
		
		//if (totalefattura == "")
		{
			//if (totaleimponibile == "") pretotaleimponibile = 0;
			var preiva = iva;
			if (rimborsi == "") prerimborsi = 0;
			if (impostabollo == "") preimpostabollo = 0;
			totalefattura = totaleimponibile + preiva + prerimborsi + preimpostabollo;
			totaledoversi = totalefattura;// - preiva;
		}
		
		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);
		
		$("#iciaggioimporto").val(importo);
		$("#iciaggiospese").val(spese);
		$("#iciaggioimponibile").val(totaleimponibile);
		$("#iciaggioperciva").val(percentualeiva);
		$("#iciaggioiva").val(iva);
		$("#iciaggiorimborsi").val(rimborsi);
		$("#iciaggioimposta").val(impostabollo);
		$("#iciaggiototale").val(totalefattura);
		$("#iciaggiodoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagiciaggioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagiciaggioimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagiciaggioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagiciaggioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagiciaggioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagiciaggioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagiciaggioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagiciaggioiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagiciaggioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagiciaggioimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagiciaggioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagiciaggioimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagiciaggioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagiciaggioimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagiciaggioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagiciaggioimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagiciaggiototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagiciaggiototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi)");
			varErrore = true;
		}
		else
		{
			$("#flagiciaggiototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagiciaggiototale").attr("title", "OK");
		}

		if ($("[name=tipobanca]").val() != "PAGATA") $("[name=tipobanca]").val("PAGATA");

		if (1)
		{
			var splitYsPayment = "Y";
			switch ($("[name=tipobanca]").val())
			{
				case "BGSG": splitYsPayment = "Y"; break;
				case "BPI": splitYsPayment = "Y"; break;
				case "PAGATA": splitYsPayment = "N"; break;
			}
			if (splitYsPayment == "Y")
			{
				var difDoversi = totalefattura - totaledoversi - iva;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagiciaggiodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagiciaggiodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
					varErrore = true;
				}
				else
				{
					$("#flagiciaggiodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagiciaggiodoversi").attr("title", "OK");
				}
			}
			else
			{
				var difDoversi = totalefattura - totaledoversi;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagiciaggiodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagiciaggiodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
					varErrore = true;
				}
				else
				{
					$("#flagiciaggiodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagiciaggiodoversi").attr("title", "OK");
				}
			}
		}
		else
		{
			$("#flagiciaggiodoversi").attr("src", "/gitco2/immagini/puntointerrogativo.jpg");
			$("#flagiciaggiodoversi").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "ICI" && tipocig == "SERVIZIO")
	{
		var importo = $("#iciservizioimporto").val();
		var spese = $("#iciserviziospese").val();
		var totaleimponibile = $("#iciservizioimponibile").val();
		var percentualeiva = $("#iciservizioperciva").val();
		var iva = $("#iciservizioiva").val();
		var rimborsi = $("#iciserviziorimborsi").val();
		var impostabollo = $("#iciservizioimposta").val();
		var totalefattura = $("#iciserviziototale").val();
		var totaledoversi = $("#iciserviziodoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));
		
		//if (totaleimponibile == "")
		{
			if (importo == "") preimporto = 0;
			if (spese == "") prespese = 0;
			totaleimponibile = preimporto + prespese;
		}
		
		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}
		
		var prerimborsi = parseFloat(rimborsi.replace(",", "."));
		var preimpostabollo = parseFloat(impostabollo.replace(",", "."));
		
		//if (totalefattura == "")
		{
			//if (totaleimponibile == "") pretotaleimponibile = 0;
			var preiva = iva;
			if (rimborsi == "") prerimborsi = 0;
			if (impostabollo == "") preimpostabollo = 0;
			totalefattura = totaleimponibile + preiva + prerimborsi + preimpostabollo;
			totaledoversi = totalefattura - preiva;
		}
		
		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);
		
		$("#iciservizioimporto").val(importo);
		$("#iciserviziospese").val(spese);
		$("#iciservizioimponibile").val(totaleimponibile);
		$("#iciservizioperciva").val(percentualeiva);
		$("#iciservizioiva").val(iva);
		$("#iciserviziorimborsi").val(rimborsi);
		$("#iciservizioimposta").val(impostabollo);
		$("#iciserviziototale").val(totalefattura);
		$("#iciserviziodoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagiciservizioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagiciservizioimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagiciservizioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagiciservizioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagiciservizioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagiciservizioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagiciservizioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagiciservizioiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagiciservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagiciservizioimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagiciservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagiciservizioimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagiciservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagiciservizioimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagiciservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagiciservizioimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagiciserviziototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagiciserviziototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi)");
			varErrore = true;
		}
		else
		{
			$("#flagiciserviziototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagiciserviziototale").attr("title", "OK");
		}

		if ($("[name=tipobanca]").val() == "PAGATA") $("[name=tipobanca]").val("");

		if ($("[name=tipobanca]").val() != "")
		{
			var splitYsPayment = "Y";
			switch ($("[name=tipobanca]").val())
			{
				case "BGSG": splitYsPayment = "Y"; break;
				case "BPI": splitYsPayment = "Y"; break;
				case "PAGATA": splitYsPayment = "N"; break;
			}
			if (splitYsPayment == "Y")
			{
				var difDoversi = totalefattura - totaledoversi - iva;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagiciserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagiciserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
					varErrore = true;
				}
				else
				{
					$("#flagiciserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagiciserviziodoversi").attr("title", "OK");
				}
			}
			else
			{
				var difDoversi = totalefattura - totaledoversi;
				if (difDoversi > 0.005 || difDoversi < -0.005)
				{
					//alert (dif);
					$("#flagiciserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
					$("#flagiciserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
					varErrore = true;
				}
				else
				{
					$("#flagiciserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
					$("#flagiciserviziodoversi").attr("title", "OK");
				}
			}
		}
		else
		{
			$("#flagiciserviziodoversi").attr("src", "/gitco2/immagini/puntointerrogativo.jpg");
			$("#flagiciserviziodoversi").attr("title", "OK");
		}
	}
	else if (tiporiscossione == "IMU"/* && tipocig == "SERVIZIO"*/)
	{
		var importo = $("#imuservizioimporto").val();
		var spese = $("#imuserviziospese").val();
		var totaleimponibile = $("#imuservizioimponibile").val();
		var percentualeiva = $("#imuservizioperciva").val();
		var iva = $("#imuservizioiva").val();
		var rimborsi = $("#imuserviziorimborsi").val();
		var impostabollo = $("#imuservizioimposta").val();
		var totalefattura = $("#imuserviziototale").val();
		var totaledoversi = $("#imuserviziodoversi").val();

		var preimporto = parseFloat(importo.replace(",", "."));
		var prespese = parseFloat(spese.replace(",", "."));
		var prepercentualeiva = parseFloat(percentualeiva.replace(",", "."));
		
		//if (totaleimponibile == "")
		{
			if (importo == "") preimporto = 0;
			if (spese == "") prespese = 0;
			totaleimponibile = preimporto + prespese;
		}
		
		//if (iva == "")
		{
			var pretotaleimponibile = totaleimponibile;
			if (percentualeiva == "") prepercentualeiva = 0;
			iva = pretotaleimponibile * prepercentualeiva / 100;
		}
		
		var prerimborsi = parseFloat(rimborsi.replace(",", "."));
		var preimpostabollo = parseFloat(impostabollo.replace(",", "."));
		
		//if (totalefattura == "")
		{
			//if (totaleimponibile == "") pretotaleimponibile = 0;
			var preiva = iva;
			if (rimborsi == "") prerimborsi = 0;
			if (impostabollo == "") preimpostabollo = 0;
			totalefattura = totaleimponibile + preiva + prerimborsi + preimpostabollo;
			totaledoversi = totalefattura - preiva;
		}
		
		importo = MostraNumeriCon2CifreDecimali(importo);
		spese = MostraNumeriCon2CifreDecimali(spese);
		totaleimponibile = MostraNumeriCon2CifreDecimali(totaleimponibile);
		percentualeiva = MostraNumeriCon2CifreDecimali(percentualeiva);
		iva = MostraNumeriCon2CifreDecimali(iva);
		rimborsi = MostraNumeriCon2CifreDecimali(rimborsi);
		impostabollo = MostraNumeriCon2CifreDecimali(impostabollo);
		totalefattura = MostraNumeriCon2CifreDecimali(totalefattura);
		totaledoversi = MostraNumeriCon2CifreDecimali(totaledoversi);
		
		$("#imuservizioimporto").val(importo);
		$("#imuserviziospese").val(spese);
		$("#imuservizioimponibile").val(totaleimponibile);
		$("#imuservizioperciva").val(percentualeiva);
		$("#imuservizioiva").val(iva);
		$("#imuserviziorimborsi").val(rimborsi);
		$("#imuservizioimposta").val(impostabollo);
		$("#imuserviziototale").val(totalefattura);
		$("#imuserviziodoversi").val(totaledoversi);
		
		importo = parseFloat(importo.replace(",", "."));
		spese = parseFloat(spese.replace(",", "."));
		totaleimponibile = parseFloat(totaleimponibile.replace(",", "."));
		percentualeiva = parseFloat(percentualeiva.replace(",", "."));
		iva = parseFloat(iva.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));
		totaledoversi = parseFloat(totaledoversi.replace(",", "."));

		var difImpon = totaleimponibile - importo - spese;
		if (difImpon > 0.005 || difImpon < -0.005)
		{
			$("#flagimuservizioimponibile").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagimuservizioimponibile").attr("title", "valore imponibile errato (totale  diverso da  importo+spese)");
			varErrore = true;
		}
		else
		{
			$("#flagimuservizioimponibile").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagimuservizioimponibile").attr("title", "OK");
		}

		var difIva = totaleimponibile * percentualeiva / 100 - iva;
		if (difIva > 0.005 || difIva < -0.005)
		{
			$("#flagimuservizioiva").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagimuservizioiva").attr("title", "valore iva errato");
			varErrore = true;
		}
		else
		{
			$("#flagimuservizioiva").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagimuservizioiva").attr("title", "OK");
		}

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				$("#flagimuservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagimuservizioimposta").attr("title", "valore imposta errato: bollo necessario");
				varErrore = true;
			}
			else
			{
				$("#flagimuservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagimuservizioimposta").attr("title", "OK");
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				$("#flagimuservizioimposta").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagimuservizioimposta").attr("title", "valore imposta errato: bollo non previsto");
				varErrore = true;
			}
			else
			{
				$("#flagimuservizioimposta").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagimuservizioimposta").attr("title", "OK");
			}
		}

		var difTot = totalefattura - totaleimponibile - iva - rimborsi - impostabollo;
		if (difTot > 0.005 || difTot < -0.005)
		{
			//alert (dif);
			$("#flagimuserviziototale").attr("src", "/gitco2/immagini/spuntaNO.jpg");
			$("#flagimuserviziototale").attr("title", "valore totale fattura errato (totale  diverso da  imponibile+iva+rimborsi)");
			varErrore = true;
		}
		else
		{
			$("#flagimuserviziototale").attr("src", "/gitco2/immagini/spunta.jpg");
			$("#flagimuserviziototale").attr("title", "OK");
		}

		if ("<?=$splitPayment?>" == "Y")
		{
			var difDoversi = totalefattura - totaledoversi - iva;
			if (difDoversi > 0.005 || difDoversi < -0.005)
			{
				//alert (dif);
				$("#flagimuserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagimuserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale-iva)");
				varErrore = true;
			}
			else
			{
				$("#flagimuserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagimuserviziodoversi").attr("title", "OK");
			}
		}
		else
		{
			var difDoversi = totalefattura - totaledoversi;
			if (difDoversi > 0.005 || difDoversi < -0.005)
			{
				//alert (dif);
				$("#flagimuserviziodoversi").attr("src", "/gitco2/immagini/spuntaNO.jpg");
				$("#flagimuserviziodoversi").attr("title", "valore totale doversi errato (a doversi  diverso da  totale)");
				varErrore = true;
			}
			else
			{
				$("#flagimuserviziodoversi").attr("src", "/gitco2/immagini/spunta.jpg");
				$("#flagimuserviziodoversi").attr("title", "OK");
			}
		}
	}
	
	if (varErrore == true)
	{
		return "ERROR";
	}
	else
	{
		CambiaTestoIva();
		return true;
	}

	return false;
}

function CambiaTendinaPagamento ()
{
	var tiporiscossione = $("[name=tiporiscossione]").val();
	var tipocig = $("[name=tipocig]").val();
	
	if (tiporiscossione == "TOSAP" && tipocig == "PAGATA_AD_AGGIO")
	{
		$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "TOSAP")
	{
		$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "PUB" && tipocig == "PAGATA_AD_AGGIO")
	{
		$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "PUB" && tipocig == "SERVIZIO")
	{
		//$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "PUB")
	{
		$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "CDS")
	{
		//$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "TARI" && tipocig == "PAGATA_AD_AGGIO")
	{
		$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "TARI" && tipocig == "SERVIZIO")
	{
		//$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "ICI" && tipocig == "PAGATA_AD_AGGIO")
	{
		$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "ICI" && tipocig == "SERVIZIO")
	{
		//$("[name=tipobanca]").val("PAGATA");
	}
	else if (tiporiscossione == "IMU")
	{
		//$("[name=tipobanca]").val("PAGATA");
	}
}

function CambiaBanca ()
{
	var somme = ControllaSomme();
	if (somme == false) return false;
	if (somme == "ERROR")
	{
		$("[name=tipobanca]").val("");
		alert ("C'è un errore nei calcoli");
		return false;
	}

	CambiaTendinaPagamento();
	
	switch ($("[name=tipobanca]").val())
	{
		case "BGSG":
			$("#bancabgsg").show();
			$("#bancabpi").hide();
			//$("#modalitapagamento").val("Pagamento: " + $("#giorniPagamento").val() + " gg fine mese d.f.");
			$("#modalitapagamento").show();
			break;
		case "BPI":
			$("#bancabgsg").hide();
			$("#bancabpi").show();
			//$("#modalitapagamento").val("Pagamento: " + $("#giorniPagamento").val() + " gg fine mese d.f.");
			$("#modalitapagamento").show();
			break;
		case "PAGATA":
			$("#bancabgsg").hide();
			$("#bancabpi").hide();
			$("#modalitapagamento").hide();
			break;
		case "":
			$("#bancabgsg").hide();
			$("#bancabpi").hide();
			$("#modalitapagamento").hide();
			break;
	}
	CambiaTestoIva();
}

function CambiaGiorni ()
{
	if (CambiaBanca() == false) return false;
	if ($("#giorniPagamento").val() == 0 || $("#giorniPagamento").val() == "")
		$("[name=testopagamento]").val(" ");
	else
		$("[name=testopagamento]").val("Pagamento: " + $("#giorniPagamento").val() + " gg fine mese d.f.");
	return true;
}

function CambiaSenzaBancaGiorni ()
{
	//if (CambiaBanca() == false) return false;
	if ($("#giorniPagamento").val() == 0 || $("#giorniPagamento").val() == "")
		$("[name=testopagamento]").val(" ");
	else
		$("[name=testopagamento]").val("Pagamento: " + $("#giorniPagamento").val() + " gg fine mese d.f.");
	return true;
}


function ControllaDati ()
{
	var errore = false;
	
	if (CambiaGiorni() == false) return false;

	var tiporiscossione = $("[name=tiporiscossione]").val();
	var tipocig = $("[name=tipocig]").val();
	
	if (tiporiscossione == "TOSAP" && tipocig == "PAGATA_AD_AGGIO")
	{
	}
	else if (tiporiscossione == "TOSAP" && tipocig == "PAGATA_A_CANONE")
	{
		var impostabollo = $("#tosapcanoneimposta").val();
		var totalefattura = $("#tosapcanonetotale").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));

		if (totalefattura > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con PAGATA_A_CANONE e totale maggiore di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				errore = true;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "PUB" && tipocig == "PAGATA_AD_AGGIO")
	{
		
	}
	else if (tiporiscossione == "PUB" && tipocig == "PAGATA_A_CANONE")
	{
		var impostabollo = $("#pubcanoneimposta").val();
		var totalefattura = $("#pubcanonetotale").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));

		if (totalefattura > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con PAGATA_A_CANONE e totale maggiore di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
	}
	else if (tiporiscossione == "PUB" && tipocig == "SERVIZIO")
	{
		//var impostabollo = $("#pubservizioimposta").val();
		var totalefattura = $("#pubserviziototale").val();
		//impostabollo = parseFloat(impostabollo.replace(",", "."));
		totalefattura = parseFloat(totalefattura.replace(",", "."));

		/*if (totalefattura > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con SERVIZIO e totale maggiore di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}*/
	}
	else if (tiporiscossione == "CDS" && tipocig == "PAGATA_A_CANONE")
	{
		var impostabollo = $("#cdscanoneimposta").val();
		var rimborsi = $("#cdscanonerimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con PAGATA_A_CANONE e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "CDS" && tipocig == "PAGATA_AD_AGGIO")
	{
		var impostabollo = $("#cdsaggioimposta").val();
		var rimborsi = $("#cdsaggiorimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con PAGATA_AD_AGGIO e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "CDS" && tipocig == "SERVIZIO")
	{
		var impostabollo = $("#cdsservizioimposta").val();
		var rimborsi = $("#cdsserviziorimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con SERVIZIO e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "TARI" && tipocig == "PAGATA_AD_AGGIO")
	{
		var impostabollo = $("#tariaggioimposta").val();
		var rimborsi = $("#tariaggiorimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con PAGATA_AD_AGGIO e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "TARI" && tipocig == "SERVIZIO")
	{
		var impostabollo = $("#tariservizioimposta").val();
		var rimborsi = $("#tariserviziorimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con SERVIZIO e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "PARK")
	{
	}
	else if (tiporiscossione == "ICI" && tipocig == "PAGATA_AD_AGGIO")
	{
		var impostabollo = $("#iciaggioimposta").val();
		var rimborsi = $("#iciaggiorimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con PAGATA_AD_AGGIO e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "ICI" && tipocig == "SERVIZIO")
	{
		var impostabollo = $("#iciservizioimposta").val();
		var rimborsi = $("#iciserviziorimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con SERVIZIO e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else if (tiporiscossione == "IMU"/* && tipocig == "SERVIZIO"*/)
	{
		var impostabollo = $("#imuservizioimposta").val();
		var rimborsi = $("#imuserviziorimborsi").val();
		impostabollo = parseFloat(impostabollo.replace(",", "."));
		rimborsi = parseFloat(rimborsi.replace(",", "."));

		if (rimborsi > <?=$limImposta?>) 
		{
			if ((impostabollo < 0.005) && (impostabollo > -0.005))
			{
				alert ("Con SERVIZIO e rimborsi maggiori di <?=$limImposta?> euro è impossibile avere imposta BOLLO a zero");
				return false;
			}
		}
		else
		{
			if ((impostabollo > 0.005) || (impostabollo < -0.005))
			{
				alert ("Questa fattura non prevede imposta di BOLLO");
				return false;
			}
		}
		
	}
	else 
	{
		alert ("errore nel ctrl dati");
		return false;
	}

	var tipofattura = $("[name=tipofattura]").val();
	if (tipofattura == "notacredito")
	{
		var totaleparziale = $("[name=totaleparziale]").val();
		var fatturacollegata = $("[name=fatturacollegata]").val();
		var datafattcollegata = $("[name=datafattcollegata]").val();
		if (totaleparziale == "" || fatturacollegata == "" || datafattcollegata == "")
		{
			alert ("errore nei campi della fattura collegata");
			return false;
		}
	}

	var tipobanca = $("[name=tipobanca]").val();
	if (tipobanca == "")
	{
		alert ("Inserire il tipo di pagamento");
		return false;
	}

	if (errore == true) return false;
	else return true;
}

function SalvaFattura ()
{
	if ("<?php echo $linkPdf?>" != "")
	{
		alert ("Fattura già salvata.");
		return false;
	}
	var esito = ControllaDati();
	if (esito == true)
	{
		$('[name=operazione]').val('SALVA');
		$('[name=fatture_form]').submit();
		return true;
	}
	else return false;
}

function CorreggiFattura ()
{
	var esito = ControllaDati();
	if (esito == true)
	{
		//alert ($("[name=ordinario]").val());
		//$("[name=descrizionefattura]").val("ee");
		//alert ($("[name=descrizionefattura]").val());
		//return;
		//var fff = $("[name=descrizionefattura]").val();
		//fff = fff.replace ("/", "//");
		//$("[name=descrizionefattura]").val(fff);
		$('[name=operazione]').val('CORREGGI');
		$('[name=numerooperazione]').val("<?=$idFattura?>");
		$('[name=fatture_form]').submit();
		return true;
	}
	else return false;
}




function CancellaFattura()
{
	$('[name=operazione]').val('CANCELLA');
	$('[name=numerooperazione]').val("<?=$idFattura?>");
	$('[name=fatture_form]').submit();
}

function CreaNotaCredito ()
{
	var esito = ControllaDati();
	if (esito == true)
	{
		//alert ($("[name=ordinario]").val());
		//$("[name=descrizionefattura]").val("ee");
		//alert ($("[name=descrizionefattura]").val());
		//return;
		//var fff = $("[name=descrizionefattura]").val();
		//fff = fff.replace ("/", "//");
		//$("[name=descrizionefattura]").val(fff);
		$('[name=operazione]').val('NOTADICREDITO');
		$('[name=numerooperazione]').val("<?=$idFattura?>");
		$('[name=fatture_form]').submit();
		return true;
	}
	else return false;
}

function NuovaFattura ()
{
	var strLink = "<?=$questaPagina?>";
	strLink += "?c=" + "<?=$c?>";
	strLink += "&a=" + "<?=$a?>";
	strLink += "&id=" + "<?=$id?>";
	//strLink += "&tiporiscossione=" + $("[name=tiporiscossione]").val();
	location.href = strLink;
}

function MostraNumeriCon2CifreDecimali (numero)
{
	var puntoce = false;
	var risultato = "";
	var testo = numero.toString();

	if (testo == "")
	{
		return "0,00";
	}
	
	for (var i = 0; i < testo.length; i++)
	{
		var car = testo.charAt(i);
		if (car == '.')
		{
			puntoce = true;
		}
		else if (car == ',')
		{
			testo = testo.replace(",", ".");
			puntoce = true;
		}
	}
	if (puntoce == true)
	{
		var arr_num = testo.split(".");
		var terzodecimale = arr_num[1].charAt(2);
		if (terzodecimale >= "5" && terzodecimale <= "9")
		{
			testo = arr_num[0] + "." + arr_num[1].charAt(0) + arr_num[1].charAt(1);
			var valoreaggiunto = parseFloat(testo);
			if (valoreaggiunto > 0) valoreaggiunto += 0.011; // serve 0.011 anzichè 0.01 perchè SBAGLIA ARROTONDAMENTO!!
			else if (valoreaggiunto < 0) valoreaggiunto -= 0.011;
			testo = valoreaggiunto.toString();
			arr_num = testo.split(".");
		} 

		if (arr_num[1].charAt(0) == "" || arr_num[1].charAt(1) == "")
		{
			if (arr_num[1].charAt(0) == "" && arr_num[1].charAt(1) == "")
				risultato = arr_num[0] + ",00";  // non dovrebbe mai essere qui a meno di errori!
			else if (arr_num[1].charAt(0) == "")
				risultato = arr_num[0] + ",0" + arr_num[1].charAt(1);  // caso assurdo
			else if (arr_num[1].charAt(1) == "")
				risultato = arr_num[0] + "," + arr_num[1].charAt(0) + "0";
		}
		else
			risultato = arr_num[0] + "," + arr_num[1].charAt(0) + arr_num[1].charAt(1);
	}
	else
	{
		risultato = testo + ",00";
	}
	return risultato;
}

function checkData (testo)
{
	var nan;
	if (testo == "")
	{
		alert ("La data non è inserita!");
		return "";
	}
	if ((testo.length != 10) && (testo.length != 8))
	{
		alert ("La data non è corretta!");
		return 0;
	}
	if (testo.length == 10) // può essere 12/12/2012
	{
		if (testo.charAt(2) == '/' && testo.charAt(5) == '/')
		{
			if (testo.charAt(0) >= '0' && testo.charAt(0) <= '3' &&
				testo.charAt(1) >= '0' && testo.charAt(1) <= '9' &&
				testo.charAt(3) >= '0' && testo.charAt(3) <= '1' &&
				testo.charAt(4) >= '0' && testo.charAt(4) <= '9' &&
				testo.charAt(6) >= '1' && testo.charAt(6) <= '2' &&
				testo.charAt(7) >= '0' && testo.charAt(7) <= '9' &&
				testo.charAt(8) >= '0' && testo.charAt(8) <= '9' &&
				testo.charAt(9) >= '0' && testo.charAt(9) <= '9')
			{
				nan = parseInt(testo.charAt(0) + testo.charAt(1));
				if (nan > 31) { alert ("La data non è corretta!"); return 0; }
				nan = parseInt(testo.charAt(3) + testo.charAt(4));
				if (nan > 12) { alert ("La data non è corretta!"); return 0; }
				nan = parseInt(testo.charAt(6) + testo.charAt(7) + testo.charAt(8) + testo.charAt(9));
				if ((nan < 1900) || (nan > 3000)) { alert ("La data non è corretta!"); return 0; }
				return testo;
			}
		}
		alert ("La data non è corretta!");
		return 0;
	}
	else if (testo.length == 8) // può essere 12122012
	{
		if (testo.charAt(0) >= '0' && testo.charAt(0) <= '3' &&
				testo.charAt(1) >= '0' && testo.charAt(1) <= '9' &&
				testo.charAt(2) >= '0' && testo.charAt(2) <= '1' &&
				testo.charAt(3) >= '0' && testo.charAt(3) <= '9' &&
				testo.charAt(4) >= '1' && testo.charAt(4) <= '2' &&
				testo.charAt(5) >= '0' && testo.charAt(5) <= '9' &&
				testo.charAt(6) >= '0' && testo.charAt(6) <= '9' &&
				testo.charAt(7) >= '0' && testo.charAt(7) <= '9')
		{
			nan = parseInt(testo.charAt(0) + testo.charAt(1));
			if (nan > 31) { alert ("La data non è corretta!"); return 0; }
			nan = parseInt(testo.charAt(2) + testo.charAt(3));
			if (nan > 12) { alert ("La data non è corretta!"); return 0; }
			nan = parseInt(testo.charAt(4) + testo.charAt(5) + testo.charAt(6) + testo.charAt(7));
			if ((nan < 1900) || (nan > 3000)) { alert ("La data non è corretta!"); return 0; }
			testo = testo.charAt(0) + testo.charAt(1) + '/' + testo.charAt(2) + testo.charAt(3) + '/' + testo.charAt(4) + testo.charAt(5) + testo.charAt(6) + testo.charAt(7);
			return testo;
		}
		alert ("La data non è corretta!");
		return 0;
	}
	else
	{
		alert ("La data non è corretta!");
		return 0;
	}
}


function CheckScriviData (testo, name)
{
	var nan;
	if (testo == "")
	{
		alert ("La data in " + name + " non è inserita!");
		$("[name=" + name + "]").val("");
		return false;
	}
	if ((testo.length != 10) && (testo.length != 8))
	{
		alert ("La data in " + name + " non è corretta!");
		$("[name=" + name + "]").val("");
		return false;
	}
	if (testo.length == 10) // può essere 12/12/2012
	{
		if (testo.charAt(2) == '/' && testo.charAt(5) == '/')
		{
			if (testo.charAt(0) >= '0' && testo.charAt(0) <= '3' &&
				testo.charAt(1) >= '0' && testo.charAt(1) <= '9' &&
				testo.charAt(3) >= '0' && testo.charAt(3) <= '1' &&
				testo.charAt(4) >= '0' && testo.charAt(4) <= '9' &&
				testo.charAt(6) >= '1' && testo.charAt(6) <= '2' &&
				testo.charAt(7) >= '0' && testo.charAt(7) <= '9' &&
				testo.charAt(8) >= '0' && testo.charAt(8) <= '9' &&
				testo.charAt(9) >= '0' && testo.charAt(9) <= '9')
			{
				nan = parseInt(testo.charAt(0) + testo.charAt(1));
				if (nan > 31) { alert ("La data in " + name + " non è corretta!"); return false; }
				nan = parseInt(testo.charAt(3) + testo.charAt(4));
				if (nan > 12) { alert ("La data in " + name + " non è corretta!"); return false; }
				nan = parseInt(testo.charAt(6) + testo.charAt(7) + testo.charAt(8) + testo.charAt(9));
				if ((nan < 1900) || (nan > 3000)) { alert ("La data non è corretta!"); return false; }
				$("[name=" + name + "]").val(testo);
				return true;
			}
		}
		alert ("La data in " + name + " non è corretta!");
		$("[name=" + name + "]").val("");
		return false;
	}
	else if (testo.length == 8) // può essere 12122012
	{
		if (testo.charAt(0) >= '0' && testo.charAt(0) <= '3' &&
				testo.charAt(1) >= '0' && testo.charAt(1) <= '9' &&
				testo.charAt(2) >= '0' && testo.charAt(2) <= '1' &&
				testo.charAt(3) >= '0' && testo.charAt(3) <= '9' &&
				testo.charAt(4) >= '1' && testo.charAt(4) <= '2' &&
				testo.charAt(5) >= '0' && testo.charAt(5) <= '9' &&
				testo.charAt(6) >= '0' && testo.charAt(6) <= '9' &&
				testo.charAt(7) >= '0' && testo.charAt(7) <= '9')
		{
			nan = parseInt(testo.charAt(0) + testo.charAt(1));
			if (nan > 31) { alert ("La data in " + name + " non è corretta!"); return false; }
			nan = parseInt(testo.charAt(2) + testo.charAt(3));
			if (nan > 12) { alert ("La data in " + name + " non è corretta!"); return false; }
			nan = parseInt(testo.charAt(4) + testo.charAt(5) + testo.charAt(6) + testo.charAt(7));
			if ((nan < 1900) || (nan > 3000)) { alert ("La data in " + name + " non è corretta!"); returnfalse0; }
			testo = testo.charAt(0) + testo.charAt(1) + '/' + testo.charAt(2) + testo.charAt(3) + '/' + testo.charAt(4) + testo.charAt(5) + testo.charAt(6) + testo.charAt(7);
			$("[name=" + name + "]").val(testo);
			return true;
		}
		alert ("La data in " + name + " non è corretta!");
		$("[name=" + name + "]").val("");
		return false;
	}
	else
	{
		alert ("La data in " + name + " non è corretta!");
		$("[name=" + name + "]").val("");
		return false;
	}
}

function CambiaContratto(num)
{
	var arrayJsContrId = new Array();
	//var arrayJsContrTrib = new Array();
	var arrayJsContrTipo = new Array();
	var arrayJsContrNumero = new Array();
	var arrayJsContrData = new Array();
	<?php

	for ($zzz = 0; $zzz < count($arrayContratti[0]); $zzz++)
	{
		echo "\n	arrayJsContrId[" . $zzz . "] = '" . $arrayContratti[0][$zzz] . "';";
		//echo "\n	arrayJsContrTrib[" . $zzz . "] = '" . $arrayContratti[1][$zzz] . "';";
		echo "\n	arrayJsContrTipo[" . $zzz . "] = '" . $arrayContratti[2][$zzz] . "';";
		echo "\n	arrayJsContrNumero[" . $zzz . "] = '" . $arrayContratti[3][$zzz] . "';";
		echo "\n	arrayJsContrData[" . $zzz . "] = '" . $arrayContratti[4][$zzz] . "';";
	}
	
	?>

	if (num == 1)
	{
		var sceltacontratto = $("#sceltacontratto").val();
		for (var iii = 0; iii < arrayJsContrId.length; iii++)
		{
			if (arrayJsContrId[iii] == sceltacontratto)
			{
				$("[name=parTipo]").val(arrayJsContrTipo[iii]);
				$("[name=parNumero]").val(arrayJsContrNumero[iii]);
				$("[name=parData]").val(arrayJsContrData[iii]);
				AggiornaTestoFattura();
				return;
			}
		}
	}
	else if (num == 2)
	{
		var scelta2contratto = $("#scelta2contratto").val();
		for (var iii = 0; iii < arrayJsContrId.length; iii++)
		{
			if (arrayJsContrId[iii] == scelta2contratto)
			{
				$("[name=par2Tipo]").val(arrayJsContrTipo[iii]);
				$("[name=par2Numero]").val(arrayJsContrNumero[iii]);
				$("[name=par2Data]").val(arrayJsContrData[iii]);
				AggiornaTestoFattura();
				return;
			}
		}
	}
	else if (num == 3)
	{
		var scelta3contratto = $("#scelta3contratto").val();
		for (var iii = 0; iii < arrayJsContrId.length; iii++)
		{
			if (arrayJsContrId[iii] == scelta3contratto)
			{
				$("[name=par3Tipo]").val(arrayJsContrTipo[iii]);
				$("[name=par3Numero]").val(arrayJsContrNumero[iii]);
				$("[name=par3Data]").val(arrayJsContrData[iii]);
				AggiornaTestoFattura();
				return;
			}
		}
	}
	return;
}

function ApriPdf ()
{
	window.open("<?=$linkPdf?>", 'page');
}
function ApriXml ()
{
	window.open("<?=$linkXml?>", 'page');
}


$(document).ready(function()
{
	$("#submit_click").click
	( 
			function SalvaPag ()
			{
				salva_form();
			}
	);
<?php

	if($ButtonDelete){
	echo '$("#delete_click").attr("src","../immagini/delete-iconF4.png");';
	echo '$("#delete_click").click(
		function CancellaPag ()
			{
			CancellaFattura();
			}
		);';
	}
?>
	<?php echo $mostraBancaGiusta; ?>
	CambiaSenzaBancaGiorni();
	
	if ($("[name=sceltacomune]").val() == "") $("#tuttoschermo").hide();
	else $("#tuttoschermo").show();
	
	var aumento = 1;
	
	$("[name=tiporiscossione]").attr("tabindex", aumento++);
	$("[name=sceltacomune]").attr("tabindex", aumento++);
	$("[name=daticig]").attr("tabindex", aumento++);
	$("[name=tipofattura]").attr("tabindex", aumento++);
	$("[name=numerofattura]").attr("tabindex", aumento++);
	//$("[name=annofattura]").val(splittobarra[1]);
	$('[name=datafattura]').attr("tabindex", aumento++);
	$('[name=annobilanciofattura]').attr("tabindex", aumento++);
	$('[name=annocompetenzafattura]').attr("tabindex", aumento++);

	$("[name=parTipo]").attr("tabindex", aumento++);
	$("[name=parNumero]").attr("tabindex", aumento++);
	$("[name=parData]").attr("tabindex", aumento++);

	$("[name=periodoDa]").attr("tabindex", aumento++);
	$("[name=periodoA]").attr("tabindex", aumento++);


	<?php if ($tiporiscossione == "TOSAP" && $tipocig == "PAGATA_A_CANONE") { ?>

		$("#tosapcanoneordinario").attr("tabindex", aumento++);
		$("#tosapcanonetemporaneo").attr("tabindex", aumento++);
		$("#tosapcanonetotale").attr("tabindex", aumento++);
		$("#tosapcanoneimposta").attr("tabindex", aumento++);

	<?php } ?>

	
	<?php if ($tiporiscossione == "PUB" && $tipocig == "PAGATA_A_CANONE") { ?>

		$("#pubcanoneordinario").attr("tabindex", aumento++);
		$("#pubcanonetemporaneo").attr("tabindex", aumento++);
		$("#pubcanoneaffissioni").attr("tabindex", aumento++);
		$("#pubcanonetotale").attr("tabindex", aumento++);
		$("#pubcanoneimposta").attr("tabindex", aumento++);
	
	<?php } ?>

	
	<?php if ($tiporiscossione == "PUB" && $tipocig == "PAGATA_AD_AGGIO") { ?>

		$("#pubaggioordinario").attr("tabindex", aumento++);
		$("#pubaggiotemporaneo").attr("tabindex", aumento++);
		$("#pubaggioaffissioni").attr("tabindex", aumento++);
		$("#pubaggioimponibile").attr("tabindex", aumento++);
		$("#pubaggioperciva").attr("tabindex", aumento++);
		$("#pubaggioiva").attr("tabindex", aumento++);
		$("#pubaggiototale").attr("tabindex", aumento++);
	
	<?php } ?>

	
	<?php if ($tiporiscossione == "PUB" && $tipocig == "SERVIZIO") { ?>

		$("#pubservizioordinario").attr("tabindex", aumento++);
		$("#pubservizioaffissioni").attr("tabindex", aumento++);
		$("#pubserviziotemporaneo").attr("tabindex", aumento++);
		$("#pubservizioimponibile").attr("tabindex", aumento++);
		$("#pubservizioperciva").attr("tabindex", aumento++);
		$("#pubservizioiva").attr("tabindex", aumento++);
		$("#pubserviziototale").attr("tabindex", aumento++);
		$("#pubserviziodoversi").attr("tabindex", aumento++);

	<?php } ?>

	
	<?php if ($tiporiscossione == "CDS" && $tipocig == "PAGATA_A_CANONE") { ?>

		$("#cdscanoneimporto").attr("tabindex", aumento++);
		$("#cdscanonespese").attr("tabindex", aumento++);
		$("#cdscanoneaffissioni").attr("tabindex", aumento++);
		$("#cdscanoneimponibile").attr("tabindex", aumento++);
		$("#cdscanoneperciva").attr("tabindex", aumento++);
		$("#cdscanoneiva").attr("tabindex", aumento++);
		$("#cdscanonetotale").attr("tabindex", aumento++);
		$("#cdscanonedoversi").attr("tabindex", aumento++);
		

	<?php } ?>

	
	<?php if ($tiporiscossione == "CDS" && $tipocig == "PAGATA_AD_AGGIO") { ?>

		$("#cdsaggioimporto").attr("tabindex", aumento++);
		$("#cdsaggiospese").attr("tabindex", aumento++);
		$("#cdsaggioimponibile").attr("tabindex", aumento++);
		$("#cdsaggioperciva").attr("tabindex", aumento++);
		$("#cdsaggioiva").attr("tabindex", aumento++);
		$("#cdsaggiorimborsi").attr("tabindex", aumento++);
		$("#cdsaggioimposta").attr("tabindex", aumento++);
		$("#cdsaggiototale").attr("tabindex", aumento++);
		$("#cdsaggiodoversi").attr("tabindex", aumento++);
		
	<?php } ?>

	
	<?php if ($tiporiscossione == "CDS" && $tipocig == "SERVIZIO") { ?>

		$("#cdsservizioimporto").attr("tabindex", aumento++);
		$("#cdsserviziospese").attr("tabindex", aumento++);
		$("#cdsservizioimponibile").attr("tabindex", aumento++);
		$("#cdsservizioperciva").attr("tabindex", aumento++);
		$("#cdsservizioiva").attr("tabindex", aumento++);
		$("#cdsserviziorimborsi").attr("tabindex", aumento++);
		$("#cdsservizioimposta").attr("tabindex", aumento++);
		$("#cdsserviziototale").attr("tabindex", aumento++);
		$("#cdsserviziodoversi").attr("tabindex", aumento++);
		
	<?php } ?>

	<?php if ($tiporiscossione == "TARI" && $tipocig == "PAGATA_AD_AGGIO") { ?>
	
		$("#tariaggioimporto").attr("tabindex", aumento++);
		$("#tariaggiospese").attr("tabindex", aumento++);
		$("#tariaggioimponibile").attr("tabindex", aumento++);
		$("#tariaggioperciva").attr("tabindex", aumento++);
		$("#tariaggioiva").attr("tabindex", aumento++);
		$("#tariaggiorimborsi").attr("tabindex", aumento++);
		$("#tariaggioimposta").attr("tabindex", aumento++);
		$("#tariaggiototale").attr("tabindex", aumento++);
		$("#tariaggiodoversi").attr("tabindex", aumento++);
		
	<?php } ?>

	<?php if ($tiporiscossione == "TARI" && $tipocig == "SERVIZIO") { ?>
	
		$("#tariservizioimporto").attr("tabindex", aumento++);
		$("#tariserviziospese").attr("tabindex", aumento++);
		$("#tariservizioimponibile").attr("tabindex", aumento++);
		$("#tariservizioperciva").attr("tabindex", aumento++);
		$("#tariservizioiva").attr("tabindex", aumento++);
		$("#tariserviziorimborsi").attr("tabindex", aumento++);
		$("#tariservizioimposta").attr("tabindex", aumento++);
		$("#tariserviziototale").attr("tabindex", aumento++);
		$("#tariserviziodoversi").attr("tabindex", aumento++);
		
	<?php } ?>
	<?php if ($tiporiscossione == "PARK") { ?>

	$("#parkaggioordinario").attr("tabindex", aumento++);
	$("#parkaggiotemporaneo").attr("tabindex", aumento++);
	$("#parkaggioimponibile").attr("tabindex", aumento++);
	$("#parkaggioperciva").attr("tabindex", aumento++);
	$("#parkaggioiva").attr("tabindex", aumento++);
	$("#parkaggiototale").attr("tabindex", aumento++);
	$("#parkaggiodoversi").attr("tabindex", aumento++);


	<?php } ?>
	<?php if ($tiporiscossione == "ICI" && $tipocig == "PAGATA_AD_AGGIO") { ?>
	
		$("#iciaggioimporto").attr("tabindex", aumento++);
		$("#iciaggiospese").attr("tabindex", aumento++);
		$("#iciaggioimponibile").attr("tabindex", aumento++);
		$("#iciaggioperciva").attr("tabindex", aumento++);
		$("#iciaggioiva").attr("tabindex", aumento++);
		$("#iciaggiorimborsi").attr("tabindex", aumento++);
		$("#iciaggioimposta").attr("tabindex", aumento++);
		$("#iciaggiototale").attr("tabindex", aumento++);
		$("#iciaggiodoversi").attr("tabindex", aumento++);
		
	<?php } ?>

	<?php if ($tiporiscossione == "ICI" && $tipocig == "SERVIZIO") { ?>
	
		$("#iciservizioimporto").attr("tabindex", aumento++);
		$("#iciserviziospese").attr("tabindex", aumento++);
		$("#iciservizioimponibile").attr("tabindex", aumento++);
		$("#iciservizioperciva").attr("tabindex", aumento++);
		$("#iciservizioiva").attr("tabindex", aumento++);
		$("#iciserviziorimborsi").attr("tabindex", aumento++);
		$("#iciservizioimposta").attr("tabindex", aumento++);
		$("#iciserviziototale").attr("tabindex", aumento++);
		$("#iciserviziodoversi").attr("tabindex", aumento++);
		
	<?php } ?>

	
	<?php if ($tiporiscossione == "IMU"/* && $tipocig == "SERVIZIO"*/) { ?>

		$("#imuservizioimporto").attr("tabindex", aumento++);
		$("#imuserviziospese").attr("tabindex", aumento++);
		$("#imuservizioimponibile").attr("tabindex", aumento++);
		$("#imuservizioperciva").attr("tabindex", aumento++);
		$("#imuservizioiva").attr("tabindex", aumento++);
		$("#imuserviziorimborsi").attr("tabindex", aumento++);
		$("#imuservizioimposta").attr("tabindex", aumento++);
		$("#imuserviziototale").attr("tabindex", aumento++);
		$("#imuserviziodoversi").attr("tabindex", aumento++);
		
	<?php } ?>
	
	$("[name=tipobanca]").attr("tabindex", aumento++);
});

</script>
    
</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>


<?php include FATTURAZIONE . '/menu/menu_fatturazione.php'; ?>

<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td class="text_center width7">
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="NuovaFattura();" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovo.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" id="paginasuccessiva" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick="cambia_pag('prec');"><img src="<?php echo $imgPrecedente?>" width=42px height=42px border="0" alt="Fattura precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick="cambia_pag('succ');"><img src="<?php echo $imgSuccessiva?>" width=42px height=42px border="0" alt="Fattura successiva"></a>
        </td>
		<td class="text_center width11">
          	
        </td>
		<td class="text_center width7">
          	<a href="#" id="stampa_click" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10grey.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
        </td>
		<td class="text_center width3">
          	
        </td>
		<td class="text_center width7" >
			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td class="text_center width2"></td>
		<td class="text_center width7">
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" style="border:3px solid #6D95D5;">
	<tr>
		<td class="width30 text_left">
			
		</td>
		<td class="width5 text_left">
			
		</td>
		<td>
			<font class="titolo font16 under_decor">Fatturazione</font>
		</td>
		<td class="width5 text_left">
			<input type=image src="/gitco2/immagini/openF9.png" style="width:25px; height:25px; border:0;" title="Carica Fattura" onclick="CaricaNuovaFattura();">
		</td>
		<td class="width30 text_left">
			
		</td>
	</tr>
</table>

	
<form id="fatture_form" name="fatture_form" action="<?=$questaPagina?>" method="post" target="">

	<input type="hidden" name="operazione" value="">
	<input type="hidden" name="numerooperazione" value="">
	<input type="hidden" name="c" value="<?=$c?>">
	<input type="hidden" name="a" value="<?=$a?>">
	<input type="hidden" name="id" value="<?=$id?>">

	<table class="width100">
	<tr>
		<td align="center">
			<input type="hidden" name="societa" value="SARIDA">
			<?php echo $aSocieta[$id]; ?>
<!--
			<select id="societa" onchange="CambiaSocieta();">
				<option value="1" <?=$selectSarida?>>Sarida S.r.l.</option>
				<option value="2" <?=$selectStc?>>STC S.r.l.</option>
			</select>
-->
		</td>
	</tr>
	<tr>
		<td align="center">
			<?=$primaRiga?><br>
			<?=$secondaRiga?><br>
			<?=$terzaRiga?><br>
			<?=$quartaRiga?><br>
			<?=$quintaRiga?><br>
		</td>
	</tr>
	</table>
	
	<table class="width100">
	<tr>

		<td class="text_left">
			<?=$optionCompetenza?>
		</td>


		<td class="text_left">
			Tipo di Riscossione:
			<select name="tiporiscossione" onchange="CambiaRiscossione();">
				<?=$optionTipiTrib?>
			</select>
		</td>
		<td class="text_right">
			Destinatario:
			<select name="sceltacomune" onchange="CambiaComune();">
				<?=$optionComuni?>
			</select>
		</td>
	</tr>
	</table>
	
	
	<div id="tuttoschermo">
	
		<table class="width100">
		<tr>
			<td class="text_left" colspan="2">
				CIG/CUP:
				<select name="daticig" onchange="CambioCig();">
					<?=$optionCigs?>
				</select>
			</td>
			<td class="text_left">
				<input type="hidden" name="spettabile" value="Spett.le">Spett.le
			</td>
			<td class="text_left">
				<?=$riga1Indirizzo?>
			</td>
		</tr>
		<tr>
			<td class="text_left" colspan="2">
				Tipo Gestione:
				<input type="text" readonly class="sfondo_grigio" name="tipocig" value="<?=$tipocig?>">
			</td>
			<td class="text_left">
				
			</td>
			<td class="text_left">
				<?=$riga2Indirizzo?>
			</td>
		</tr>
		<tr>
			<td class="width15 text_left">
				<div id="marcabollo">
					<!-- Marca da bollo -->
				</div>
			</td>
			<td class="width35 text_left">
				
			</td>
			<td class="width10 text_left">
				
			</td>
			<td class="width40 text_left">
				<?=$riga3Indirizzo?> <?=$riga4Indirizzo?><br>
				<?=$riga5Indirizzo?> <?=$riga6Indirizzo?> <?=$riga7Indirizzo?><br>
				<?=$htmlPICF?>
			</td>
		</tr>
		</table>
		
		<table class="width100" border=0>
		<tr>
			<td class="text_left">
				<select name="tipofattura" onchange="CambioTipoFatt();">
					<option value=""></option>
					<option value="fattura" <?=$selectFattura?>>FATTURA</option>
					<option value="preavviso" <?=$selectPreavviso?>>PREAVVISO DI FATTURA</option>
					<option value="reversale" <?=$selectReversale?>>REVERSALE</option>
					<option value="notacredito" <?=$selectNota?>>NOTA DI CREDITO</option>
				</select>
				&nbsp;&nbsp;
				N. <input type="text" class="text_right pwidth80" name="numerofattura" value="<?=$numerofattura?>" title="Mettere il numero fattura in formato xxx/xx/20xx" onchange="ControllaNumeroFattura();">
				anno <input type="text" class="text_right sfondo_grigio pwidth80" readonly class="sfondo_grigio" name="annofattura" value="<?=$annofattura?>">
				del <input type="text" class="text_right pwidth80" name="datafattura" value="<?=$datafattura?>" onchange="CheckDataFattura();">
			</td>
			<td class="text_right">
				Anno bilancio <input type="text" class="text_right pwidth80" name="annobilanciofattura" value="<?=$annobilanciofattura?>" onchange="CtrlBilancio();">
				<br>
				Anno competenza <input type="text" class="text_right pwidth80" name="annocompetenzafattura" value="<?=$annocompetenzafattura?>" onchange="CtrlCompetenza();">
			</td>
		</tr>
		</table>
		

			<table class="width100">

			<?php

			if ($tiporiscossione == "CDS") {
				if($numerooperazione>0){
					echo '<input type="text" name="descrizionefattura" id="fattura1" class=" width90" value="'.$descrizionefattura.'">';


				}else{
					echo '<tr class="pheight25">
						<td colspan="2" class="text_left">';
						echo '<select name="TestiCDS">
							<option value=""></option>';
						for($i=0; $i<count($aTestiCDS);$i++){
							echo '<option value='.$i.'>'.$aTestiCDS[$i];
						}
						echo '</select>';

					echo '<input type="text" name="descrizionefattura" id="fattura1" class="pwidth250" value="'.$descrizionefattura.'">';
				}
			echo '</td>
			</tr>
			<tr class="pheight25">
				<td colspan="2" class="text_left">
					<input type="text" name="descrizione4fattura" id="descrizione4fattura" class="width90" value="'.$descrizione4fattura.'">
				</td>
			</tr>
			<tr class="pheight25">
					<td colspan="2" class="text_left">
						<input type="text" name="descrizionelibera" id="descrizionelibera" class="width90" value="'.$descrizionelibera.'">
					</td>
				</tr>';
			 }else{
				echo '<tr class="pheight25">
					<td colspan="2" class="text_left">
						<input type="text" name="descrizionefattura" id="fattura1" class="width90" value="'.$descrizionefattura.'">
					</td>
				</tr>
				<tr class="pheight25">
					<td colspan="2" class="text_left">
						<input type="text" name="descrizione4fattura" id="descrizione4fattura" class="width90" value="'.$descrizione4fattura.'">
					</td>
				</tr>
				<tr class="pheight25">
					<td colspan="2" class="text_left">
						<input type="text" name="descrizionelibera" id="descrizionelibera" class="width90" value="'.$descrizionelibera.'">
					</td>
				</tr>';

			}?>

			<?php if ($tiporiscossione == "CDS") { ?>
			
				<tr class="pheight25">
					<td colspan="2" class="text_left">
						<select name="tipogestionecds">
							<option value=""></option>
							<option value="RISCOSSIONE" <?=$selectItaliani?>>Riscossione italiani</option>
							<option value="ESTERO" <?=$selectEsteri?>>Riscossioni esteri</option>
						</select>
					</td>
				</tr>
				
			<?php } ?>
			
			<tr class="pheight25">
				<td class="width50 text_left">
					<select name="parTipo">
						<?=$options1Contratti?>
					</select>
					Numero <input type="text" name="parNumero" class="pwidth80 text_right" value="<?=$parNumero?>" onchange="AggiornaTestoFattura();">
					Data <input type="text" name="parData" class="pwidth80 text_right" value="<?=$parData?>" onchange="AggiornaTestoFattura();">
				</td>
				<td class="width25 text_left">
					<select id="sceltacontratto" onchange="CambiaContratto(1);">
						<?=$listaContratti?>
					</select>
				</td>
			</tr>
			
			<?php if ($tiporiscossione == "CDS") { ?>
			
			<tr class="pheight25">
				<td class="width50 text_left">
					<select name="par2Tipo">
						<?=$options2Contratti?>
					</select>
					Numero <input type="text" name="par2Numero" class="pwidth80 text_right" value="<?=$par2Numero?>" onchange="AggiornaTestoFattura();">
					Data <input type="text" name="par2Data" class="pwidth80 text_right" value="<?=$par2Data?>" onchange="AggiornaTestoFattura();">
				</td>
				<td class="width25 text_left">
					<select id="scelta2contratto" onchange="CambiaContratto(2);">
						<?=$listaContratti?>
					</select>
				</td>
			</tr>
			<tr class="pheight25">
				<td class="width50 text_left">
					<select name="par3Tipo">
						<?=$options3Contratti?>
					</select>
					Numero <input type="text" name="par3Numero" class="pwidth80 text_right" value="<?=$par3Numero?>" onchange="AggiornaTestoFattura();">
					Data <input type="text" name="par3Data" class="pwidth80 text_right" value="<?=$par3Data?>" onchange="AggiornaTestoFattura();">
				</td>
				<td class="width25 text_left">
					<select id="scelta3contratto" onchange="CambiaContratto(3);">
						<?=$listaContratti?>
					</select>
				</td>
			</tr>
			
			<?php } ?>
			
			<tr class="pheight30">
				<td colspan="2" class="text_left">
					Periodo di fattura: da 
					<input type="text" name="periodoDa" class="text_right pwidth80" value="<?=$periodoDa?>" onchange="AggiornaPeriodo(1);">
					a <input type="text" class="text_right pwidth80" name="periodoA" value="<?=$periodoA?>" onchange="AggiornaPeriodo(2);">
				</td>
			</tr>
			</table>
		

		<?php if ($tipofattura == "notacredito") { ?>
			
			<table class="width100">
			<tr class="pheight25">
				<td class="text_left">
					<input type="text" readonly name="descrizionenota" id="descrizionenota" class="sfondo_grigio width90" value="<?=$descrizionenota?>">
				</td>
			</tr>
			<tr class="pheight25">
				<td class="text_left">
					<select name="totaleparziale" onchange="AggiornaTestoFattura();">
						<option value=""></option>
						<option value="parziale" <?=$selectParziale?>>parziale</option>
						<option value="totale" <?=$selectTotale?>>totale</option>
					</select>
					Numero Fattura Collegata
					<input type="text" name="fatturacollegata" class="pwidth80" value="<?=$fatturacollegata?>" onchange="AggiornaTestoFattura();">
					Data
					<input type="text" name="datafattcollegata" class="pwidth80" value="<?=$datafattcollegata?>" onchange="AggiornaTestoFattura();">
				</td>
			</tr>
			</table>
		
		<?php } ?>
		
		
		
		<?php if ($tiporiscossione == "TOSAP" && $tipocig == "PAGATA_A_CANONE") { ?>
		
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td>
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					T.O.S.A.P. Ordinaria
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="ordinario" id="tosapcanoneordinario" value="<?=$ordinario?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					T.O.S.A.P. Temporanea
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="temporaneo" id="tosapcanonetemporaneo" value="<?=$temporaneo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="tosapcanonetotale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td class="text_center">
					<a href="#"><img name="flagtotale" id="flagtosapcanone" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Imposta di bollo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="impostabollo" id="tosapcanoneimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "PUB" && $tipocig == "PAGATA_A_CANONE") { ?>
		
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Pubblicità Ordinaria
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="ordinario" id="pubcanoneordinario" value="<?=$ordinario?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Pubblicità Temporanea
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="temporaneo" id="pubcanonetemporaneo" value="<?=$temporaneo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Diritto Pubbliche Affissioni
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="affissioni" id="pubcanoneaffissioni" value="<?=$affissioni?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="pubcanonetotale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagpubcanone" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Imposta di bollo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="impostabollo" id="pubcanoneimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			</table>
			
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "PUB" && $tipocig == "PAGATA_AD_AGGIO") { ?>
		
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Pubblicità Ordinaria
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="ordinario" id="pubaggioordinario" value="<?=$ordinario?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Pubblicità Temporanea
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="temporaneo" id="pubaggiotemporaneo" value="<?=$temporaneo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Diritto Pubbliche Affissioni
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="affissioni" id="pubaggioaffissioni" value="<?=$affissioni?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="pubaggioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagpubaggioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="pubaggioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="pubaggioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagpubaggioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="pubaggiototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagpubaggiototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "PUB" && $tipocig == "SERVIZIO") { ?>
		
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Pubblicità
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="ordinario" id="pubservizioordinario" value="<?=$ordinario?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Affissioni
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="affissioni" id="pubservizioaffissioni" value="<?=$affissioni?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					T.o.s.a.p.
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="temporaneo" id="pubserviziotemporaneo" value="<?=$temporaneo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="pubservizioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagpubservizioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="pubservizioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="pubservizioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagpubservizioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="pubserviziototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagpubserviziototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="pubserviziodoversi" value="<?=$totaleadoversi?>" ></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagpubserviziodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "CDS" && $tipocig == "PAGATA_A_CANONE") { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="cdscanoneimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="cdscanonespese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="cdscanoneimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagcdscanoneimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="cdscanoneperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="cdscanoneiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagcdscanoneiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="cdscanonerimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="cdscanoneimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagcdscanoneimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="cdscanonetotale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagcdscanonetotale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="cdscanonedoversi" value="<?=$totaleadoversi?>"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagcdscanonedoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "CDS" && $tipocig == "PAGATA_AD_AGGIO") { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="cdsaggioimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="cdsaggiospese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="cdsaggioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagcdsaggioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="cdsaggioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="cdsaggioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagcdsaggioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="cdsaggiorimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="cdsaggioimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagcdsaggioimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="cdsaggiototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagcdsaggiototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="cdsaggiodoversi" value="<?=$totaleadoversi?>"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagcdsaggiodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "CDS" && $tipocig == "SERVIZIO") { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="cdsservizioimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="cdsserviziospese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="cdsservizioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagcdsservizioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="cdsservizioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="cdsservizioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagcdsservizioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="cdsserviziorimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="cdsservizioimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagcdsservizioimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="cdsserviziototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagcdsserviziototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="cdsserviziodoversi" value="<?=$totaleadoversi?>"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagcdsserviziodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "TARI" && $tipocig == "PAGATA_AD_AGGIO") { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="tariaggioimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="tariaggiospese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="tariaggioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagtariaggioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="tariaggioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="tariaggioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagtariaggioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="tariaggiorimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="tariaggioimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagtariaggioimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="tariaggiototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagtariaggiototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="tariaggiodoversi" value="<?=$totaleadoversi?>"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagtariaggiodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "TARI" && $tipocig == "SERVIZIO") { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="tariservizioimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="tariserviziospese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="tariservizioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagtariservizioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="tariservizioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="tariservizioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagtariservizioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="tariserviziorimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="tariservizioimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagtariservizioimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="tariserviziototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagtariserviziototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="tariserviziodoversi" value="<?=$totaleadoversi?>"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagtariserviziodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		<?php if ($tiporiscossione == "PARK") { ?>



			<table class="width100">
				<tr>
					<td class="width30">
					</td>
					<td class="width30">
					</td>
					<td class="text_right">
					</td>
					<td width="width3">
					</td>
				</tr>
				<tr>
					<td class="text_left">
						&nbsp;
					</td>
					<td class="text_left">
						Importo
					</td>
					<td class="text_right">
						€ <input type="text" class="text_right" name="ordinario" id="parkaggioordinario" value="<?=$importo?>" onchange="ControllaSomme();">
					</td>
					<td class="text_right">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td class="text_left">
						&nbsp;
					</td>
					<td class="text_left">
						Spese
					</td>
					<td class="text_right">
						€ <input type="text" class="text_right" name="temporaneo" id="parkaggiotemporaneo" value="<?=$temporaneo?>" onchange="ControllaSomme();">
					</td>
					<td class="text_right">

					</td>
				</tr>
				<tr>
					<td class="text_left">
						&nbsp;
					</td>
					<td class="text_left">
						<b>Totale imponibile</b>
					</td>
					<td class="text_right">
						<b>€ <input type="text" class="text_right" name="totaleimponibile" id="parkaggioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
					</td>
					<td align="center">
						<a href="#"><img name="flagimponibile" id="flagpubaggioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
					</td>
				</tr>
				<tr>
					<td class="text_left">
						&nbsp;
					</td>
					<td class="text_left">
						I.V.A. <input type="text" class="text_right" name="percentualeiva" id="parkaggioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
					</td>
					<td class="text_right">
						€ <input type="text" class="text_right" name="iva" id="parkaggioiva" value="<?=$iva?>" onchange="ControllaSomme();">
					</td>
					<td align="center">
						<a href="#"><img name="flagiva" id="flagpubaggioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
					</td>
				</tr>
				<tr>
					<td class="text_left">
						&nbsp;
					</td>
					<td class="text_left">
						<b>TOTALE FATTURA</b>
					</td>
					<td class="text_right">
						<b>€ <input type="text" class="text_right" name="totalefattura" id="parkaggiototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
					</td>
					<td align="center">
						<a href="#"><img name="flagtotale" id="flagpubaggiototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
					</td>
				</tr>
				<tr>
					<td class="text_left">
						&nbsp;
					</td>
					<td class="text_left">
						<b>TOTALE A DOVERSI</b>
					</td>
					<td class="text_right">
						<b>€ <input type="text" class="text_right" name="totalefattura" id="parkaggiodoversi" value="<?=$totaleadoversi?>"></b>
					</td>
					<td align="center">
						<a href="#"><img name="flagtotale" id="flagpubaggiototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
					</td>
				</tr>

			</table>



		<?php } ?>

		<?php if ($tiporiscossione == "ICI" && $tipocig == "PAGATA_AD_AGGIO") { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="iciaggioimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="iciaggiospese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="iciaggioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagiciaggioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="iciaggioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="iciaggioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagiciaggioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="iciaggiorimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="iciaggioimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagiciaggioimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="iciaggiototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagiciaggiototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="iciaggiodoversi" value="<?=$totaleadoversi?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagiciaggiodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "ICI" && $tipocig == "SERVIZIO") { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="iciservizioimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="iciserviziospese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="iciservizioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagiciservizioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="iciservizioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="iciservizioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagiciservizioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="iciserviziorimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="iciservizioimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagiciservizioimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="iciserviziototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagiciserviziototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="iciserviziodoversi" value="<?=$totaleadoversi?>"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagiciserviziodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		<?php if ($tiporiscossione == "IMU"/* && $tipocig == "SERVIZIO"*/) { ?>
		
		
			<table class="width100">
			<tr>
				<td class="width30">
				</td>
				<td class="width30">
				</td>
				<td class="text_right">
				</td>
				<td width="width3">
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Importo
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="importo" id="imuservizioimporto" value="<?=$importo?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Spese
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="spese" id="imuserviziospese" value="<?=$spese?>" onchange="ControllaSomme();">
				</td>
				<td class="text_right">
					
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>Totale imponibile</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleimponibile" id="imuservizioimponibile" value="<?=$totaleimponibile?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagimponibile" id="flagimuservizioimponibile" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					I.V.A. <input type="text" class="text_right" name="percentualeiva" id="imuservizioperciva" value="<?=$percentualeiva?>" size=6 onchange="ControllaSomme();"> %
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="iva" id="imuservizioiva" value="<?=$iva?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagiva" id="flagimuservizioiva" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					Rimborsi
				</td>
				<td class="text_right">
					€ <input type="text" class="text_right" name="rimborsi" id="imuserviziorimborsi" value="<?=$rimborsi?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					
				</td>
			</tr>
			<tr>
				<td align="left">
					&nbsp;
				</td>
				<td align="left">
					Imposta di bollo
				</td>
				<td align="right">
					€ <input type="text" class="text_right" name="impostabollo" id="imuservizioimposta" value="<?=$impostabollo?>" onchange="ControllaSomme();">
				</td>
				<td align="center">
					<a href="#"><img name="flagimposta" id="flagimuservizioimposta" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totalefattura" id="imuserviziototale" value="<?=$totalefattura?>" onchange="ControllaSomme();"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagtotale" id="flagimuserviziototale" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			<tr>
				<td class="text_left">
					&nbsp;
				</td>
				<td class="text_left">
					<b>TOTALE A DOVERSI</b>
				</td>
				<td class="text_right">
					<b>€ <input type="text" class="text_right" name="totaleadoversi" id="imuserviziodoversi" value="<?=$totaleadoversi?>"></b>
				</td>
				<td align="center">
					<a href="#"><img name="flagdoversi" id="flagimuserviziodoversi" border=0 width="15px" height="15px" src="/gitco2/immagini/spunta.jpg"></a>
				</td>
			</tr>
			</table>
		
		
		
		<?php } ?>
		
		
		
		<table class="width100">
		<tr height="25">
			<td align="left" class="width100">
				<input type="text" readonly class="sfondo_grigio width90" name="testoiva" id="testoiva" value="<?=$scrittaTestoIva?>">
			</td>
		</tr>
		</table>
		
		<table class="width100" height="130pt">

		<tr valign="top">
			<td align="left" colspan="2">
				<select name="tipobanca" class="tipobancaid" onchange="CambiaBanca();">
					<option value=""></option>
					<option value="PAGATA" <?=$selectPagata?>>Fattura Pagata</option>
					<option value="BGSG" <?=$selectBgsg?>>Banca Regionale Europea</option>
					<option value="BPI" <?=$selectBpi?>>Banco Popolare</option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<td align="left" width="35%">
				<div id="modalitapagamento">
					<input type="text" readonly class="sfondo_grigio width90" name="testopagamento" value="<?=$primaPagamento?>">
					<br>
					Giorni Pagamento
					<select name="giorniPagamento" id="giorniPagamento" onchange="CambiaGiorni();">
						<option value=""></option>
						<option value="30" <?=$select30gg?>>30</option>
						<option value="60" <?=$select60gg?>>60</option>
						<option value="90" <?=$select90gg?>>90</option>
						<option value="120" <?=$select120gg?>>120</option>
					</select>
				</div>
			</td>

		</tr>
		</table>
		
	</div>
		
	<table class="width100">
	
	<?php if ($linkPdf != "") { //($producipdfxml == "SI") { ?>
	
		<tr>
			<td class="text_center">
				<img src="/gitco2/immagini/pdfnew.png" class="pwidth25 pheight25" title="Vedi Fattura Pdf" onclick="ApriPdf();">
				<img src="/gitco2/immagini/iconaXml.png" class="pwidth25 pheight25" title="Vedi Fattura Xml" onclick="ApriXml();">
			</td>
			<td class="text_center">
				<input class="menu_blu" <?=' '// $disableCorreggi ?> type="button" value="Correggi Fattura" onclick="CorreggiFattura();">
			</td>
			<td class="text_center">
				<!-- <input class="menu_blu" type="submit" value="Elimina Fattura" onclick="return EliminaFattura();"> -->
				<?php echo $scrittaInviato ?>
			</td>
			<td class="text_center">
				<?php if ($tipofattura == "fattura") { ?>
					<input class="menu_blu" type="button" value="Crea Nota Credito" onclick="CreaNotaCredito();">
				<?php } ?>
			</td>
		</tr>
	
	<?php } ?>
	
	<tr>
		<td align="center" class="width25">
			<!-- <input class="menu_blu" type="submit" <?=$vediSalvataggio?> value="Salva Fattura" onclick="return SalvaFattura();"> -->
		</td>
		<td align="center" class="width25">
			<!-- <input class="menu_blu" type="submit" value="Stampa PDF" onclick="/*return StampaFattura();*/"> -->
			<!-- <input class="menu_blu" type="button" value="Nuova Fattura" onclick="NuovaFattura();"> -->
		</td>
		<td align="center" class="width25">
			
		</td>
		<td align="center" class="width25">
			<!-- <input class="menu_blu" type="button" value="Carica Fattura" onclick="CaricaNuovaFattura();"> -->
		</td>
	</tr>
	
	</table>

</form>

</td>
</tr>
</table>

</body>
</html>

<?php 

//alertAllGlobalVariables();

if ($memoSalva == "MEMOSALVA" AND $id==1)  //  settaggio
{
	switch ($tipobanca)
	{
		case "":
			alert ("error banca");
			break;
		case "BGSG":  //  da fatture cds    e   da questa pagina
			$secondaPagamento = $bancaBGSG_1;
			$terzaPagamento = $bancaBGSG_2;
			$quartaPagamento = $bancaBGSG_3;
			$quintaPagamento = $bancaBGSG_4;
			$iban = $ibanBGSG;
			$bic = $bicBGSG;
			$nomeBanca = $nomeBancaBGSG;
			break;
		case "BPI":  //  da fatture cds    e   da questa pagina
			$secondaPagamento = $bancaBPI_1;
			$terzaPagamento = $bancaBPI_2;
			$quartaPagamento = $bancaBPI_3;
			$quintaPagamento = $bancaBPI_4;
			$iban = $ibanBPI;
			$bic = $bicBPI;
			$nomeBanca = $nomeBancaBPI;
			break;
		case "PAGA":  //  da fatture cds
			$primaPagamento = "FATTURA PAGATA";
			$secondaPagamento = "";
			$terzaPagamento = "";
			$quartaPagamento = "";
			$quintaPagamento = "";
			break;
		case "PAGATA":  //  da questa pagina
			$primaPagamento = "FATTURA PAGATA";
			$secondaPagamento = "";
			$terzaPagamento = "";
			$quartaPagamento = "";
			$quintaPagamento = "";
			break;
		default:
			alert ("error banca sconosciuta: " . $tipobanca);
			return;
			break;
	}
	
	if ($tipofattura == "notacredito")
	{
		$primaPagamento = "";
		$secondaPagamento = "";
		$terzaPagamento = "";
		$quartaPagamento = "";
		$quintaPagamento = "";
	}
	
	$impostaDaPagare = "NO";
	
	switch ($tiporiscossione)
	{
		case "TOSAP":
			if ($totalefattura > $limImposta)
			{
				switch ($tipocig)
				{
					case "SERVIZIO": $impostaDaPagare = "SI"; break;
					case "PAGATA_AD_AGGIO": $impostaDaPagare = "SI"; break;
					case "PAGATA_A_CANONE": $impostaDaPagare = "SI"; break;
				}
			}
			else
			{
				switch ($tipocig)
				{
					case "SERVIZIO": $impostaDaPagare = "NO"; break;
					case "PAGATA_AD_AGGIO": $impostaDaPagare = "NO"; break;
					case "PAGATA_A_CANONE": $impostaDaPagare = "NO"; break;
				}
			}
			break;
		case "PUB":
			if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($totalefattura > $limImposta)
				{
					$impostaDaPagare = "SI";
				}
				else
				{
					$impostaDaPagare = "NO";
				}
			}
			else if ($tipocig == "SERVIZIO")
			{
				$impostaDaPagare = "NO";
			}
			else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$impostaDaPagare = "NO";
			}
			else alert ("non so pub");
			break;
		case "CDS":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($rimborsi > $limImposta)
				{
					$impostaDaPagare = "SI";
				}
				else
				{
					$impostaDaPagare = "NO";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$impostaDaPagare = "NO";
			}*/
			//else alert ("non so cds");
			break;
		case "TARI":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($rimborsi > $limImposta)
				{
					$impostaDaPagare = "SI";
				}
				else
				{
					$impostaDaPagare = "NO";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$impostaDaPagare = "NO";
			}*/
			//else alert ("non so cds");
			break;
		case "PARK":

			$impostaDaPagare = "NO";
			break;
		case "ICI":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($rimborsi > $limImposta)
				{
					$impostaDaPagare = "SI";
				}
				else
				{
					$impostaDaPagare = "NO";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$impostaDaPagare = "NO";
			}*/
			//else alert ("non so cds");
			break;
		case "IMU":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($rimborsi > $limImposta)
				{
					$impostaDaPagare = "SI";
				}
				else
				{
					$impostaDaPagare = "NO";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$impostaDaPagare = "NO";
			}*/
			//else alert ("non so cds");
			break;
		default:
			alert ("non tosap e pub; errore");
			return;
			break;
	}
	
	/*if ($tipofattura == "notacredito")
	{
		$impostaDaPagare = "NO";
	}*/
}

if ($memoSalva == "MEMOSALVA")  //  pdf
{
	$spazioTraVoci = 3;
	$aCapo = 1;
	//$totaleDaMettereInFattura = 0;  //  se non lo metto mi accorgo di errori!
	
	switch ($tiporiscossione)
	{
		case "TOSAP":
			if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($impostabollo != "0,00")
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = $testoSost;
				}
				else
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = "";
				}
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			}
			else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$tuttoTestoQuadrato1 = "";
				$bordoQuadrato = 0;
				$tuttoTestoSost = "";
			}
			else alert ("non so2 tos");
			break;
		case "PUB":
			if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($impostabollo != "0,00")
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = $testoSost;
				}
				else
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = "";
				}
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			}
			else if ($tipocig == "SERVIZIO")
			{
				if ($impostabollo != "0,00")
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = $testoSost;
				}
				else
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = "";
				}
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			}
			else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$tuttoTestoQuadrato1 = "";
				$bordoQuadrato = 0;
				$tuttoTestoSost = "";
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			}
			else alert ("non so2 pub");
			break;
		case "CDS":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($impostabollo != "0,00")
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = $testoSost;
				}
				else
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = "";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$tuttoTestoQuadrato1 = "";
				$bordoQuadrato = 0;
				$tuttoTestoSost = "";
			}*/
			//else alert ("non so2 cds");
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			break;
		case "TARI":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($impostabollo != "0,00")
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = $testoSost;
				}
				else
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = "";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$tuttoTestoQuadrato1 = "";
				$bordoQuadrato = 0;
				$tuttoTestoSost = "";
			}*/
			//else alert ("non so2 cds");
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			break;
		case "PARK":
			$tuttoTestoQuadrato1 = "";
			$bordoQuadrato = 0;
			$tuttoTestoSost = "";
			$totaleDaMettereInFattura = number_format (
				str_replace(",", ".", $totalefattura),
				2, ".", "");
			break;
		case "ICI":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($impostabollo != "0,00")
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = $testoSost;
				}
				else
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = "";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$tuttoTestoQuadrato1 = "";
				$bordoQuadrato = 0;
				$tuttoTestoSost = "";
			}*/
			//else alert ("non so2 cds");
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			break;
		case "IMU":
			//if ($tipocig == "PAGATA_A_CANONE")
			{
				if ($impostabollo != "0,00")
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = $testoSost;
				}
				else
				{
					$tuttoTestoQuadrato1 = "";
					$bordoQuadrato = 0;
					$tuttoTestoSost = "";
				}
			}
			/*else if ($tipocig == "PAGATA_AD_AGGIO")
			{
				$tuttoTestoQuadrato1 = "";
				$bordoQuadrato = 0;
				$tuttoTestoSost = "";
			}*/
			//else alert ("non so2 cds");
				$totaleDaMettereInFattura = number_format (
						str_replace(",", ".", $totalefattura),
						2, ".", "");
			break;
		default:
			alert ("non tosap e pub; errore2");
			return;
			break;
	}
	
	$pdf = new TCPDF('P','mm','A4');
	
	$pdf->setPrintHeader(false);
	
	$pdf->SetMargins(20,5,20);

	$pdf->AddPage();
	$pdf->LN(10);

	$pdf->SetFont('Helvetica','B',15);
	$pdf->Cell(0,5, $primaRiga, 0, $aCapo, 'C',0);

	$pdf->SetFont('Helvetica','',10);
	$pdf->Cell(0,5,$secondaRiga, 0, $aCapo, 'C',0);
	$pdf->Cell(0,5,$terzaRiga, 0, $aCapo, 'C',0);
	$pdf->Cell(0,5,$quartaRiga, 0, $aCapo, 'C',0);
	$pdf->Cell(0,5,$quintaRiga, 0, $aCapo, 'C',0);
	$pdf->LN(15);

	$pdf->SetFont('Helvetica','',10);
	$pdf->Cell(28,20, $tuttoTestoQuadrato1, $bordoQuadrato, 0, 'C', 0);
	$pdf->Cell(30,5,"",0,0,'L',0);

	$pdf->SetFont('Helvetica','',12);
	$pdf->Cell(32,5, 'Spett.le', 0, 0, 'L',0);
	$pdf->Cell(0,5,$riga1Indirizzo, 0, $aCapo, 'L',0);
	$pdf->Cell(90,5,"",0,0,'L',0);					$pdf->Cell(0,5,$riga2Indirizzo, 0, $aCapo, 'L',0);
	$pdf->Cell(90,5,"",0,0,'L',0);					$pdf->Cell(0,5,"$riga3Indirizzo $riga4Indirizzo", 0, $aCapo, 'L',0);
	$pdf->Cell(90,5,"",0,0,'L',0);					$pdf->Cell(0,5,"$riga5Indirizzo $riga6Indirizzo $riga7Indirizzo", 0, $aCapo, 'L',0);
	$pdf->Cell(90,5,"",0,0,'L',0);					$pdf->Cell(0,5,$pdfPartIva, 0, $aCapo, 'L',0);
	$pdf->Cell(90,5,"",0,0,'L',0);					$pdf->Cell(0,5,$pdfCodFisc, 0, $aCapo, 'L',0);

	$pdf->LN(20);


	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(170,5, $titoloFattura, 0, 0, 'L',0);
	$pdf->SetFont('Helvetica','',12);						$pdf->Cell(15,5,"", 0, $aCapo, 'R', 0);  //  segno per la piega per imbustare

	$pdf->SetFont('Helvetica','',12);
	//alert ($descrizionenota . " e " . $descrizionefattura . " eeee " . $descrizione4fattura);
	
	if ($tiporiscossione == "CDS")
	{
		if ($descrizionenota != "" && $tipofattura == "notacredito")
		{
			$pdf->MultiCell(0,5, $descrizionenota . "\n", 0, 'J', 0);
		}
		else
		{
			$pdf->MultiCell(0,5, $descrizionefattura . " " . $descrizione4fattura . " " . $descrizionelibera . "\n", 0, 'J', 0);
		}
	}
	else 
	{
		//if ($descrizionenota != "")
		{
			$pdf->MultiCell(0,5, $descrizionenota . "\n", 0, 'J', 0);
		}
		/*else
		{
			$pdf->MultiCell(0,5, $descrizionefattura . " " . $descrizione4fattura . "\n", 0, 'J', 0);
		}*/
	}


	switch ($tiporiscossione)
	{
		case "TOSAP":
			$pdf->LN(25);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "T.O.S.A.P. Ordinaria", 0, 0, 'L', 0);		$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $ordinario), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Esente iva ex art.10", 0, $aCapo, 'L', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "nn. 5 e 9 del DPR 633/1972", 0, $aCapo, 'L', 0);
			$pdf->SetFont('Helvetica','',12);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "T.O.S.A.P. Temporanea", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".",$temporaneo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Esente iva ex art.10", 0, $aCapo, 'L', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "nn. 5 e 9 del DPR 633/1972", 0, $aCapo, 'L', 0);
			$pdf->SetFont('Helvetica','',12);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
		
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".",$totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
			$pdf->LN(1);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);	
			
			$pdf->SetFont('Helvetica','',10);
			
			$pdf->LN(30);
		
			$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
		
			$pdf->LN(20);
			
			$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
			
			break;
		case "PUB":
			$pdf->LN(25);
			switch ($tipocig)
			{
				case "PAGATA_A_CANONE":
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Pubblicita' Ordinaria", 0, 0, 'L', 0);		$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $ordinario), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->SetFont('Helvetica','',10);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Esente iva ex art.10", 0, $aCapo, 'L', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "nn. 5 e 9 del DPR 633/1972", 0, $aCapo, 'L', 0);
					$pdf->SetFont('Helvetica','',12);
					$pdf->Ln($spazioTraVoci);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Pubblicita' Temporanea", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $temporaneo), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->SetFont('Helvetica','',10);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Esente iva ex art.10", 0, $aCapo, 'L', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "nn. 5 e 9 del DPR 633/1972", 0, $aCapo, 'L', 0);
					$pdf->SetFont('Helvetica','',12);
					$pdf->Ln($spazioTraVoci);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Diritto Pubbliche Affissioni", 0, 0, 'L', 0); $pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $affissioni), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->SetFont('Helvetica','',10);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Esente iva ex art.10", 0, $aCapo, 'L', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "nn. 5 e 9 del DPR 633/1972", 0, $aCapo, 'L', 0);
					$pdf->SetFont('Helvetica','',12);
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
					$pdf->LN(1);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
					
					$pdf->SetFont('Helvetica','',10);
					
					$pdf->LN(10);
					
					$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
					
					$pdf->LN(20);
					
					$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
					break;
				case "PAGATA_AD_AGGIO":
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Pubblicita' Ordinaria", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $ordinario), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Ln($spazioTraVoci);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Pubblicita' Temporanea", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $temporaneo), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Ln($spazioTraVoci);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Diritto Pubbliche Affissioni", 0, 0, 'L', 0);		$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $affissioni), 2, ",", "."), 0, $aCapo, 'R', 0);
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);									$pdf->Cell(90,5,"---------------------------", 0, $aCapo, 'L', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Totale Imponibile", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleimponibile), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Ln($spazioTraVoci);
					
					$pdf->SetX(5);
					$pdf->Cell(15,5,"",0,0,'L',0);  //  segno per la perforatrice
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "I.V.A. $percentualeiva%", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $iva), 2, ",", "."), 0, $aCapo, 'R', 0);
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
					$pdf->LN(1);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
						
					$pdf->SetFont('Helvetica','',10);
					
					$pdf->LN(10);
					
					$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
					$pdf->Cell(0,5, $scrittaTestoIva, 0, $aCapo, 'L', 0);
					
					$pdf->LN(10);
					
					$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
					break;
				case "SERVIZIO":
					$contoVuoti = 0;
					
					if ($ordinario != "0,00")
					{
						$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Pubblicita'", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $ordinario), 2, ",", "."), 0, $aCapo, 'R', 0);
						$pdf->Ln($spazioTraVoci);
					}
					else $contoVuoti ++;
					if ($affissioni != "0,00")
					{
						$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Affissioni", 0, 0, 'L', 0);		$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $affissioni), 2, ",", "."), 0, $aCapo, 'R', 0);
						$pdf->Ln($spazioTraVoci);
					}
					else $contoVuoti ++;
					if ($temporaneo != "0,00")
					{
						$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "T.O.S.A.P.", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $temporaneo), 2, ",", "."), 0, $aCapo, 'R', 0);
						$pdf->Ln($spazioTraVoci);
					}
					else $contoVuoti ++;
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);									$pdf->Cell(90,5,"---------------------------", 0, $aCapo, 'L', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Totale Imponibile", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleimponibile), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Ln($spazioTraVoci);
					
					$pdf->SetX(5);
					$pdf->Cell(15,5,"",0,0,'L',0);  //  segno per la perforatrice
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "I.V.A. $percentualeiva%", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $iva), 2, ",", "."), 0, $aCapo, 'R', 0);
					
					$pdf->SetFont('Helvetica','',12);
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);					$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,$aCapo,'L',0);
					/*$pdf->LN(1);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);*/
					
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE A DOVERSI", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleadoversi), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
					$pdf->LN(1);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
						
					$pdf->SetFont('Helvetica','',10);
					
					$ppp = 0;
					while ($ppp < $contoVuoti)
					{
						$pdf->Ln($spazioTraVoci);
						$ppp++;
					}
			
					$pdf->LN(5);
					
					$pdf->SetFont('Helvetica','I',10);
					
					$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
					$pdf->Cell(0,5, $scrittaTestoIva, 0, $aCapo, 'L', 0);
					
					$pdf->SetFont('Helvetica','',10);
					
					//$pdf->LN(10);
					
					//$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
					$pdf->Cell(0,5, "---------------------------------------------------------------------------------------------------------------------------------------", 0, $aCapo, 'L', 0);
					$pdf->Cell(20,5, $secondaPagamento, 0, $aCapo, 'L', 0);
					$pdf->Cell(20,5, $terzaPagamento, 0, $aCapo, 'L', 0);
					$pdf->Cell(20,5, $quartaPagamento, 0, $aCapo, 'L', 0);
					$pdf->Cell(20,5, $quintaPagamento, 0, $aCapo, 'L', 0);
					break;
				default:
					alert ("pdf errato aggio pub");
					return;
					break;
			}
			break;
		case "CDS":
			$pdf->LN(5);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Importo", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $importo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Spese", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $spese), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);									$pdf->Cell(90,5,"---------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Totale Imponibile", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleimponibile), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			
			$pdf->SetX(5);
			$pdf->Cell(15,5,"",0,0,'L',0);  //  segno per la perforatrice
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "I.V.A. $percentualeiva%", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $iva), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Rimborsi escl.Art.15", 0, 0, 'L', 0); $pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $rimborsi), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',12);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Imposta di bollo escl.Art.15", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $impostabollo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			$pdf->SetFont('Helvetica','',12);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);					$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			if ($tipobanca == "PAGATA")
			{
				$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
			}
			else 
			{
				$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,$aCapo,'L',0);
				$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE A DOVERSI", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleadoversi), 2, ",", "."), 0, $aCapo, 'R', 0);
				$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
			}
			
			$pdf->LN(1);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
				
			$pdf->SetFont('Helvetica','',10);
			
			$pdf->LN(5);
			
			$pdf->SetFont('Helvetica','I',10);
			
			$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, $scrittaTestoIva, 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',10);
			
			//$pdf->LN(10);
			
			$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, "---------------------------------------------------------------------------------------------------------------------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $secondaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $terzaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quartaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quintaPagamento, 0, $aCapo, 'L', 0);
			break;
		case "TARI":
			$pdf->LN(5);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Importo", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $importo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Spese", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $spese), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);									$pdf->Cell(90,5,"---------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Totale Imponibile", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleimponibile), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			
			$pdf->SetX(5);
			$pdf->Cell(15,5,"",0,0,'L',0);  //  segno per la perforatrice
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "I.V.A. $percentualeiva%", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $iva), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Rimborsi escl.Art.15", 0, 0, 'L', 0); $pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $rimborsi), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',12);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Imposta di bollo escl.Art.15", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $impostabollo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			$pdf->SetFont('Helvetica','',12);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);					$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
			
			
			switch ($tipocig)
			{
				case "PAGATA_AD_AGGIO":
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
					break;
				case "SERVIZIO":
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,$aCapo,'L',0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE A DOVERSI", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleadoversi), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
					break;
			}
			
			$pdf->LN(1);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
				
			$pdf->SetFont('Helvetica','',10);
			
			$pdf->LN(5);
			
			$pdf->SetFont('Helvetica','I',10);
			
			$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, $scrittaTestoIva, 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',10);
			
			//$pdf->LN(10);
			
			$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, "---------------------------------------------------------------------------------------------------------------------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $secondaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $terzaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quartaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quintaPagamento, 0, $aCapo, 'L', 0);
			break;
		case "PARK":
			$pdf->LN(25);

			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Importo", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $importo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Spese", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $temporaneo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Totale Imponibile", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleimponibile), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);

			$pdf->SetX(5);
			$pdf->Cell(15,5,"",0,0,'L',0);  //  segno per la perforatrice

			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "I.V.A. $percentualeiva%", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $iva), 2, ",", "."), 0, $aCapo, 'R', 0);

			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);

			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,$aCapo,'L',0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE A DOVERSI", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleadoversi), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
			$pdf->LN(1);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);

			$pdf->SetFont('Helvetica','',10);

			$pdf->LN(5);

			$pdf->SetFont('Helvetica','I',10);

			$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, $scrittaTestoIva, 0, $aCapo, 'L', 0);

			$pdf->SetFont('Helvetica','',10);

			//$pdf->LN(10);

			$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, "---------------------------------------------------------------------------------------------------------------------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $secondaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $terzaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quartaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quintaPagamento, 0, $aCapo, 'L', 0);
			break;
		case "ICI":
			$pdf->LN(5);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Importo", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $importo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Spese", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $spese), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);									$pdf->Cell(90,5,"---------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Totale Imponibile", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleimponibile), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			
			$pdf->SetX(5);
			$pdf->Cell(15,5,"",0,0,'L',0);  //  segno per la perforatrice
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "I.V.A. $percentualeiva%", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $iva), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Rimborsi escl.Art.15", 0, 0, 'L', 0); $pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $rimborsi), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',12);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Imposta di bollo escl.Art.15", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $impostabollo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			$pdf->SetFont('Helvetica','',12);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);					$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
			
			
			switch ($tipocig)
			{
				case "PAGATA_AD_AGGIO":
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
					break;
				case "SERVIZIO":
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,$aCapo,'L',0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE A DOVERSI", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleadoversi), 2, ",", "."), 0, $aCapo, 'R', 0);
					$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
					break;
			}
			
			$pdf->LN(1);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
				
			$pdf->SetFont('Helvetica','',10);
			
			$pdf->LN(5);
			
			$pdf->SetFont('Helvetica','I',10);
			
			$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, $scrittaTestoIva, 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',10);
			
			//$pdf->LN(10);
			
			$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, "---------------------------------------------------------------------------------------------------------------------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $secondaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $terzaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quartaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quintaPagamento, 0, $aCapo, 'L', 0);
			break;
		case "IMU":
			$pdf->LN(5);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Importo", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $importo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Spese", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $spese), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);									$pdf->Cell(90,5,"---------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Totale Imponibile", 0, 0, 'L', 0);				$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleimponibile), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Ln($spazioTraVoci);
			
			$pdf->SetX(5);
			$pdf->Cell(15,5,"",0,0,'L',0);  //  segno per la perforatrice
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "I.V.A. $percentualeiva%", 0, 0, 'L', 0);	$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $iva), 2, ",", "."), 0, $aCapo, 'R', 0);
			
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Rimborsi escl.Art.15", 0, 0, 'L', 0); $pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $rimborsi), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',12);
			$pdf->Ln($spazioTraVoci);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "Imposta di bollo escl.Art.15", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $impostabollo), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "ex D.P.R.633/72", 0, $aCapo, 'L', 0);
			$pdf->SetFont('Helvetica','',12);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE", 0, 0, 'L', 0);					$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totalefattura), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,$aCapo,'L',0);
			/*$pdf->LN(1);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);*/
			
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "TOTALE A DOVERSI", 0, 0, 'L', 0);			$pdf->Cell(10,5, "Euro", 0, 0, 'L', 0);		$pdf->Cell(30,5, number_format(str_replace(",", ".", $totaleadoversi), 2, ",", "."), 0, $aCapo, 'R', 0);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------",0,0,'L',0);
			$pdf->LN(1);
			$pdf->Cell(60,5,"",0,0,'L',0);	$pdf->Cell(55,5, "", 0, 0, 'L', 0);							$pdf->Cell(80,5,"---------------------------", 0, $aCapo, 'L', 0);
				
			$pdf->SetFont('Helvetica','',10);
			
			$pdf->LN(5);
			
			$pdf->SetFont('Helvetica','I',10);
			
			$pdf->Cell(0,5, $tuttoTestoSost, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, $scrittaTestoIva, 0, $aCapo, 'L', 0);
			
			$pdf->SetFont('Helvetica','',10);
			
			//$pdf->LN(10);
			
			$pdf->Cell(20,5, $primaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(0,5, "---------------------------------------------------------------------------------------------------------------------------------------", 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $secondaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $terzaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quartaPagamento, 0, $aCapo, 'L', 0);
			$pdf->Cell(20,5, $quintaPagamento, 0, $aCapo, 'L', 0);
			break;
		default:
			alert ("errore pdf risccc");
			return;
			break;
	}
	
	$rigaUfficioFine = "";
	if ($myDatiFattura->CIG != "")
	{
		$rigaUfficioFine .= " CIG " . $myDatiFattura->CIG;
	}
	if ($myDatiFattura->CUP != "")
	{
		if ($rigaUfficioFine != "") $rigaUfficioFine .= " - ";
		$rigaUfficioFine .= "CUP " . $myDatiFattura->CUP;
	}
	if ($myDatiFattura->ID_Ufficio != "")
	{
		if ($rigaUfficioFine != "") $rigaUfficioFine .= " - ";
		$rigaUfficioFine .= "Codice Univoco Ufficio " . $myDatiFattura->ID_Ufficio;
	}
	$pdf->Cell(0,5, "", 0, $aCapo, 'R', 0);
	$pdf->Cell(0,5, $rigaUfficioFine, 0, $aCapo, 'R', 0);
	if ($myDatiFattura->Riferimento_Numero != "")
	{
		$tipoRiferimento = $campoRiferimento = "";
		$explodeRif = explode ("**", $myDatiFattura->Riferimento_Numero);
		$tipoRiferimento = $explodeRif[0];
		$campoRiferimento = $explodeRif[1];
		
		$tipoDatoRiferimento = "Tipo Dato " . $tipoRiferimento . " - " . $campoRiferimento;
		$pdf->Cell(0,5, $tipoDatoRiferimento, 0, 0, 'R', 0);
	}
	
	$nomeCompletoFilePdf = $_SERVER['DOCUMENT_ROOT'] . $linkPdf;
	$pdf->Output ($nomeCompletoFilePdf, "F");
	
}
if($tipobanca=="PAGA" OR $tipobanca=="PAGATA") $descrizionefattura = "FATTURA PAGATA - ".$descrizionefattura;


if(strlen($descrizionefattura)>200) $descr = trim(substr($descrizionefattura,0,197))."...";
else $descr = $descrizionefattura;



if ($memoSalva == "MEMOSALVA")  //  xml
{
	$myAltriDati = "";
	if ($myDatiFattura->Riferimento_Numero != "")
	{
		/*$tipoRiferimento = $campoRiferimento = "";
		$explodeRif = explode ("**", $myDatiFattura->Riferimento_Numero);
		$tipoRiferimento = $explodeRif[0];
		$campoRiferimento = $explodeRif[1];*/
		
		$myAltriDati = "
				<AltriDatiGestionali>
					<TipoDato>".$tipoRiferimento."</TipoDato>
					<RiferimentoTesto>".$campoRiferimento."</RiferimentoTesto>
				</AltriDatiGestionali>";
	}


	$myXml = "<?xml version='1.0' encoding='UTF-8'?>
	<p:FatturaElettronica versione='1.1' xmlns:ds='http://www.w3.org/2000/09/xmldsig#' xmlns:p='http://www.fatturapa.gov.it/sdi/fatturapa/v1.1' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>
		<FatturaElettronicaHeader>
			<DatiTrasmissione>
				<IdTrasmittente>
					<IdPaese>IT</IdPaese>
					<IdCodice>01338160995</IdCodice>
				</IdTrasmittente>
				<ProgressivoInvio>$progressivoInvio</ProgressivoInvio>
				<FormatoTrasmissione>SDI11</FormatoTrasmissione>
				<CodiceDestinatario>" . $myDatiFattura->ID_Ufficio . "</CodiceDestinatario>
				<ContattiTrasmittente/>
			</DatiTrasmissione>
			<CedentePrestatore>
				<DatiAnagrafici>
					<IdFiscaleIVA>
						<IdPaese>IT</IdPaese>
						<IdCodice>01338160995</IdCodice>
					</IdFiscaleIVA>
					<Anagrafica>
						<Denominazione>$primaRiga</Denominazione>
					</Anagrafica>
					<RegimeFiscale>RF01</RegimeFiscale>
				</DatiAnagrafici>
				<Sede>
					<Indirizzo>VIA MONS. VATTUONE</Indirizzo>
					<NumeroCivico>9/6</NumeroCivico>
					<CAP>16039</CAP>
					<Comune>SESTRI LEVANTE</Comune>
					<Provincia>GE</Provincia>
					<Nazione>IT</Nazione>
				</Sede>
				<IscrizioneREA>
					<Ufficio>GE</Ufficio>
					<NumeroREA>401963</NumeroREA>
					<CapitaleSociale>1100000.00</CapitaleSociale>
					<StatoLiquidazione>LN</StatoLiquidazione>
				</IscrizioneREA>
			</CedentePrestatore>
			<CessionarioCommittente>
				<DatiAnagrafici>";
	if ($riga9Indirizzo != "")
	{
		$myXml .= "
					<IdFiscaleIVA>
						<IdPaese>IT</IdPaese>
						<IdCodice>$riga9Indirizzo</IdCodice>
					</IdFiscaleIVA>";
	}
	if ($riga8Indirizzo != "")
	{
		$myXml .= "
					<CodiceFiscale>$riga8Indirizzo</CodiceFiscale>";
	}
	$myXml .= "
					<Anagrafica>
						<Denominazione>" . $riga1Indirizzo . " " . $riga2Indirizzo . "</Denominazione>
					</Anagrafica>
				</DatiAnagrafici>
				<Sede>
					<Indirizzo>$riga3Indirizzo</Indirizzo>
					<NumeroCivico>$riga4Indirizzo</NumeroCivico>
					<CAP>$riga5Indirizzo</CAP>
					<Comune>$riga6Indirizzo</Comune>
					<Provincia>$riga7Indirizzo</Provincia>
					<Nazione>IT</Nazione>
				</Sede>
			</CessionarioCommittente>
		</FatturaElettronicaHeader>
		<FatturaElettronicaBody>
			<DatiGenerali>
				<DatiGeneraliDocumento>
					<Causale>".$descr."</Causale>
					<TipoDocumento>$codiceTipoFattura</TipoDocumento>
					<Divisa>EUR</Divisa>
					<Data>" . to_mysql_date($datafattura) . "</Data>
					<Numero>" . $numerofattura . "</Numero>";
	if ($impostaDaPagare == "SI")
	{
		$myXml .= "
					<DatiBollo>
						<BolloVirtuale>$impostaDaPagare</BolloVirtuale>
						<ImportoBollo>" . str_replace(",", ".", $impostabollo) . "</ImportoBollo>
					</DatiBollo>";
	}
	$myXml .= "
					<ImportoTotaleDocumento>$totaleDaMettereInFattura</ImportoTotaleDocumento>
				</DatiGeneraliDocumento>";

	if ($parNumero == "") $parNumero = " ";
	if ($parNumero != "")
	{
		$myXml .= "
				<DatiContratto>
					<IdDocumento>" . $parNumero . "</IdDocumento>
					<Data>" . to_mysql_date($parData) . "</Data>";
		if ($myDatiFattura->CUP != "")
		{
			$myXml .= "
					<CodiceCUP>" . $myDatiFattura->CUP . "</CodiceCUP>";
		}
		if ($myDatiFattura->CIG != "")
		{
			$myXml .= "
					<CodiceCIG>" . $myDatiFattura->CIG . "</CodiceCIG>";
		}
		
		$myXml .= "
				</DatiContratto>";
	}
	
	if ($par2Numero != "")
	{
		$myXml .= "
				<DatiContratto>
					<IdDocumento>" . $par2Numero . "</IdDocumento>
					<Data>" . to_mysql_date($par2Data) . "</Data>";
		/*if ($myDatiFattura->CUP != "")
		{
			$myXml .= "
					<CodiceCUP>" . $myDatiFattura->CUP . "</CodiceCUP>";
		}
		if ($myDatiFattura->CIG != "")
		{
			$myXml .= "
					<CodiceCIG>" . $myDatiFattura->CIG . "</CodiceCIG>";
		}*/
	
		$myXml .= "
				</DatiContratto>";
	}
	
	if ($par3Numero != "")
	{
		$myXml .= "
				<DatiContratto>
					<IdDocumento>" . $par3Numero . "</IdDocumento>
					<Data>" . to_mysql_date($par3Data) . "</Data>";
		/*if ($myDatiFattura->CUP != "")
		{
			$myXml .= "
					<CodiceCUP>" . $myDatiFattura->CUP . "</CodiceCUP>";
		}
		if ($myDatiFattura->CIG != "")
		{
			$myXml .= "
					<CodiceCIG>" . $myDatiFattura->CIG . "</CodiceCIG>";
		}*/
	
		$myXml .= "
				</DatiContratto>";
	}
	
	if ($tipofattura == "notacredito")
	{
		$myXml .= "
				<DatiFattureCollegate>
					<IdDocumento>$fatturacollegata</IdDocumento>
					<Data>" . to_mysql_date($datafattcollegata) . "</Data>
				</DatiFattureCollegate>";
	}
	
	$myXml .= "
			</DatiGenerali>";
	
	if ($tiporiscossione == "TOSAP" && $tipocig == "PAGATA_A_CANONE")
	{
		$totaleXML = number_format (
						str_replace(",", ".", $ordinario) +
						str_replace(",", ".", $temporaneo),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>T.O.S.A.P. Ordinaria</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $ordinario) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $ordinario) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N4</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>T.O.S.A.P. Temporanea</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $temporaneo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $temporaneo) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N4</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N4</Natura>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>0.00</Imposta>
					<RiferimentoNormativo>ART. 10 nn. 5 e 9 DEL D.P.R. NR 633/1972</RiferimentoNormativo>
				</DatiRiepilogo>
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "PUB" && $tipocig == "PAGATA_A_CANONE")
	{
		$totaleXML = number_format (
						str_replace(",", ".", $ordinario) +
						str_replace(",", ".", $temporaneo) +
						str_replace(",", ".", $affissioni),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Pubblicita' Ordinaria</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $ordinario) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $ordinario) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N4</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Pubblicita' Temporanea</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $temporaneo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $temporaneo) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N4</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>Diritto Pubbliche Affissioni</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $affissioni) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $affissioni) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N4</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N4</Natura>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>0.00</Imposta>
					<RiferimentoNormativo>ART. 10 nn. 5 e 9 DEL D.P.R. NR 633/1972</RiferimentoNormativo>
				</DatiRiepilogo>
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "PUB" && $tipocig == "PAGATA_AD_AGGIO")
	{
		$totaleXML = number_format (
						str_replace(",", ".", $ordinario) +
						str_replace(",", ".", $temporaneo) +
						str_replace(",", ".", $affissioni),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Pubblicita' Ordinaria</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $ordinario) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $ordinario) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Pubblicita' Temporanea</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $temporaneo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $temporaneo) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>Diritto Pubbliche Affissioni</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $affissioni) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $affissioni) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>" . str_replace(",", ".", $iva) . "</Imposta>
					<EsigibilitaIVA>I</EsigibilitaIVA>
				</DatiRiepilogo>
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "PUB" && $tipocig == "SERVIZIO")
	{
		$totaleXML = number_format (
						str_replace(",", ".", $ordinario) +
						str_replace(",", ".", $temporaneo) +
						str_replace(",", ".", $affissioni),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Pubblicita'</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $ordinario) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $ordinario) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Affissioni</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $affissioni) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $affissioni) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>T.O.S.A.P.</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $temporaneo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $temporaneo) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>" . str_replace(",", ".", $iva) . "</Imposta>
					<EsigibilitaIVA>S</EsigibilitaIVA>
				</DatiRiepilogo>
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "CDS"/* && $tipocig == "PAGATA_AD_AGGIO"*/)
	{
		$totaleXML = number_format (
						str_replace(",", ".", $importo) +
						str_replace(",", ".", $spese) +
						0 + //str_replace(",", ".", $iva) +
						0,//str_replace(",", ".", $rimborsi),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Importo</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $importo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $importo) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Spese</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $spese) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $spese) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>Rimborsi escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $rimborsi) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $rimborsi) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>4</NumeroLinea>
					<Descrizione>Imposta di bollo escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $impostabollo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $impostabollo) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>" . str_replace(",", ".", $iva) . "</Imposta>";
		
		if ($splitPayment == "Y")
		{
			$myXml .= "
					<EsigibilitaIVA>S</EsigibilitaIVA>";
		}
		else if ($splitPayment == "N")
		{
			$myXml .= "
					<EsigibilitaIVA>I</EsigibilitaIVA>";
		}
		$myXml .= "
				</DatiRiepilogo>";
		
		if ($rimborsi != "0,00" || $impostabollo != "0,00")
		{
			$totNonIvato = number_format (
						str_replace(",", ".", $rimborsi) +
						str_replace(",", ".", $impostabollo),
						2, ".", "");
			$myXml .= "
				<DatiRiepilogo>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					<ImponibileImporto>" . str_replace(",", ".", $totNonIvato) . "</ImponibileImporto>
					<Imposta>0.00</Imposta>
					<RiferimentoNormativo>ART. 15 ex D.P.R. NR 633/1972</RiferimentoNormativo>
				</DatiRiepilogo>";
		}
		
		$myXml .= "
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "TARI"/* && $tipocig == "PAGATA_AD_AGGIO"*/)
	{
		$totaleXML = number_format (
						str_replace(",", ".", $importo) +
						str_replace(",", ".", $spese) +
						0 + //str_replace(",", ".", $iva) +
						0,//str_replace(",", ".", $rimborsi),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Importo</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $importo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $importo) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Spese</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $spese) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $spese) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>Rimborsi escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $rimborsi) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $rimborsi) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>4</NumeroLinea>
					<Descrizione>Imposta di bollo escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $impostabollo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $impostabollo) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>" . str_replace(",", ".", $iva) . "</Imposta>";
		
		if ($splitPayment == "Y")
		{
			$myXml .= "
					<EsigibilitaIVA>S</EsigibilitaIVA>";
		}
		else if ($splitPayment == "N")
		{
			$myXml .= "
					<EsigibilitaIVA>I</EsigibilitaIVA>";
		}
		$myXml .= "
				</DatiRiepilogo>";
		
		if ($rimborsi != "0,00" || $impostabollo != "0,00")
		{
			$totNonIvato = number_format (
						str_replace(",", ".", $rimborsi) +
						str_replace(",", ".", $impostabollo),
						2, ".", "");
			$myXml .= "
				<DatiRiepilogo>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					<ImponibileImporto>" . str_replace(",", ".", $totNonIvato) . "</ImponibileImporto>
					<Imposta>0.00</Imposta>
					<RiferimentoNormativo>ART. 15 ex D.P.R. NR 633/1972</RiferimentoNormativo>
				</DatiRiepilogo>";
		}
		
		$myXml .= "
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "PARK")
	{
		$totaleXML = number_format (
			str_replace(",", ".", $importo) +
			str_replace(",", ".", $spese) +
			0 + //str_replace(",", ".", $iva) +
			0,//str_replace(",", ".", $rimborsi),
			2, ".", "");

		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Importo</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $importo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $importo) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Spese</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $spese) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $spese) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>Rimborsi escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $rimborsi) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $rimborsi) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>4</NumeroLinea>
					<Descrizione>Imposta di bollo escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $impostabollo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $impostabollo) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>" . str_replace(",", ".", $iva) . "</Imposta>";

		if ($splitPayment == "Y")
		{
			$myXml .= "
					<EsigibilitaIVA>S</EsigibilitaIVA>";
		}
		else if ($splitPayment == "N")
		{
			$myXml .= "
					<EsigibilitaIVA>I</EsigibilitaIVA>";
		}
		$myXml .= "
				</DatiRiepilogo>";

		if ($rimborsi != "0,00" || $impostabollo != "0,00")
		{
			$totNonIvato = number_format (
				str_replace(",", ".", $rimborsi) +
				str_replace(",", ".", $impostabollo),
				2, ".", "");
			$myXml .= "
				<DatiRiepilogo>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					<ImponibileImporto>" . str_replace(",", ".", $totNonIvato) . "</ImponibileImporto>
					<Imposta>0.00</Imposta>
					<RiferimentoNormativo>ART. 15 ex D.P.R. NR 633/1972</RiferimentoNormativo>
				</DatiRiepilogo>";
		}

		$myXml .= "
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "ICI"/* && $tipocig == "PAGATA_AD_AGGIO"*/)
	{
		$totaleXML = number_format (
						str_replace(",", ".", $importo) +
						str_replace(",", ".", $spese) +
						0 + //str_replace(",", ".", $iva) +
						0,//str_replace(",", ".", $rimborsi),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Importo</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $importo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $importo) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Spese</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $spese) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $spese) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>Rimborsi escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $rimborsi) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $rimborsi) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>4</NumeroLinea>
					<Descrizione>Imposta di bollo escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $impostabollo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $impostabollo) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>" . str_replace(",", ".", $iva) . "</Imposta>";
		
		if ($splitPayment == "Y")
		{
			$myXml .= "
					<EsigibilitaIVA>S</EsigibilitaIVA>";
		}
		else if ($splitPayment == "N")
		{
			$myXml .= "
					<EsigibilitaIVA>I</EsigibilitaIVA>";
		}
		$myXml .= "
				</DatiRiepilogo>";
		
		if ($rimborsi != "0,00" || $impostabollo != "0,00")
		{
			$totNonIvato = number_format (
						str_replace(",", ".", $rimborsi) +
						str_replace(",", ".", $impostabollo),
						2, ".", "");
			$myXml .= "
				<DatiRiepilogo>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					<ImponibileImporto>" . str_replace(",", ".", $totNonIvato) . "</ImponibileImporto>
					<Imposta>0.00</Imposta>
					<RiferimentoNormativo>ART. 15 ex D.P.R. NR 633/1972</RiferimentoNormativo>
				</DatiRiepilogo>";
		}
		
		$myXml .= "
			</DatiBeniServizi>";
	}
	else if ($tiporiscossione == "IMU"/* && $tipocig == "PAGATA_AD_AGGIO"*/)
	{
		$totaleXML = number_format (
						str_replace(",", ".", $importo) +
						str_replace(",", ".", $spese) +
						0 + //str_replace(",", ".", $iva) +
						0,//str_replace(",", ".", $rimborsi),
						2, ".", "");
		
		$myXml .= "
			<DatiBeniServizi>
				<DettaglioLinee>
					<NumeroLinea>1</NumeroLinea>
					<Descrizione>Importo</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $importo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $importo) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>2</NumeroLinea>
					<Descrizione>Spese</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $spese) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $spese) . "</PrezzoTotale>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>3</NumeroLinea>
					<Descrizione>Rimborsi escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $rimborsi) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $rimborsi) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DettaglioLinee>
					<NumeroLinea>4</NumeroLinea>
					<Descrizione>Imposta di bollo escl.Art.15 ex D.P.R.633/72</Descrizione>
					<PrezzoUnitario>" . str_replace(",", ".", $impostabollo) . "</PrezzoUnitario>
					<PrezzoTotale>" . str_replace(",", ".", $impostabollo) . "</PrezzoTotale>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					$myAltriDati
				</DettaglioLinee>
				<DatiRiepilogo>
					<AliquotaIVA>" . str_replace(",", ".", $percentualeiva) . "</AliquotaIVA>
					<ImponibileImporto>" . $totaleXML . "</ImponibileImporto>
					<Imposta>" . str_replace(",", ".", $iva) . "</Imposta>";
		
		if ($splitPayment == "Y")
		{
			$myXml .= "
					<EsigibilitaIVA>S</EsigibilitaIVA>";
		}
		else if ($splitPayment == "N")
		{
			$myXml .= "
					<EsigibilitaIVA>I</EsigibilitaIVA>";
		}
		$myXml .= "
				</DatiRiepilogo>";
		
		if ($rimborsi != "0,00" || $impostabollo != "0,00")
		{
			$totNonIvato = number_format (
						str_replace(",", ".", $rimborsi) +
						str_replace(",", ".", $impostabollo),
						2, ".", "");
			$myXml .= "
				<DatiRiepilogo>
					<AliquotaIVA>0.00</AliquotaIVA>
					<Natura>N1</Natura>
					<ImponibileImporto>" . str_replace(",", ".", $totNonIvato) . "</ImponibileImporto>
					<Imposta>0.00</Imposta>
					<RiferimentoNormativo>ART. 15 ex D.P.R. NR 633/1972</RiferimentoNormativo>
				</DatiRiepilogo>";
		}
		
		$myXml .= "
			</DatiBeniServizi>";
	}
	
	switch ($tipobanca)
	{
		case "BSGS":
		case "BPI":
		$secDataFattura = strtotime(to_mysql_date($datafattura));
		$secGiorniPag = $giorniPagamento * 60 * 60 * 24;
		$datascadenza = date ("Y-m-d", $secDataFattura+$secGiorniPag);
		$myXml .= "
			<DatiPagamento>
				<CondizioniPagamento>TP02</CondizioniPagamento>
				<DettaglioPagamento>
					<ModalitaPagamento>MP05</ModalitaPagamento>
					<DataRiferimentoTerminiPagamento>" . to_mysql_date($datafattura) . "</DataRiferimentoTerminiPagamento>
					<GiorniTerminiPagamento>$giorniPagamento</GiorniTerminiPagamento>
					<DataScadenzaPagamento>$datascadenza</DataScadenzaPagamento>
					<ImportoPagamento>" . str_replace(",", ".", $totaleadoversi) . "</ImportoPagamento>
					<IstitutoFinanziario>$nomeBanca</IstitutoFinanziario>
					<IBAN>$iban</IBAN>
					<BIC>$bic</BIC>";
		$myXml .= "</DettaglioPagamento>
			</DatiPagamento>";
			break;
		default:

			break;
	}
	$myXml .= "
		</FatturaElettronicaBody>
	</p:FatturaElettronica>";
	
	$carCF = Chr(13);
	$myXml = str_replace($carCF, "", $myXml);
	
	$nomeCompletoFileXml = $_SERVER['DOCUMENT_ROOT'] . $linkXml;
	$nomeFileXml = $linkXml;
	
	$myFileXML = fopen($nomeCompletoFileXml, "w");
	fwrite ($myFileXML, $myXml, strlen($myXml));
	fclose($myFileXML);
	
}

if ($memoSalva == "MEMOSALVA")  //  salvataggio
{	
	$myFatturaUpdate = new fatture_generali($idFattura);
	$myFatturaUpdate->Fat_Imposta_Da_Versare = $impostaDaPagare;
	$myFatturaUpdate->Fat_Nome_File_Pdf = $nomePdf;
	$myFatturaUpdate->Fat_Nome_File_Xml = $nomeXml;
	switch ($tipobanca)
	{
		case "PAGA":
		case "PAGATA":
			$myFatturaUpdate->Fat_Data_Accredito = $myFatturaUpdate->Fat_Data;
			$myFatturaUpdate->Fat_Accredito = $myFatturaUpdate->Fat_Totale;
			break;
		default: break;
	}
	
	$myFatturaUpdate->InsertUpdateFattura("UPDATE");
	
	alert ("Fattura salvata");
}

?>