<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC . "/header.php");
include(INC . "/menu.php");
include_once(CLS."/cls_elaborazioniUtils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_Utils.php");
include_once CLS . "/cls_zip.php";

	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include_once LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	include CLASSI . "/parametri.php";
	include CLASSI . "/targhe_estere_utenti.php";
	include CLASSI . "/pagamenti_importati.php";*/

		
	if($_SESSION['username']==NULL)
	{
		header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
		die;
	}

	$cls_elab = new cls_elaborazioniUtils();
	$cls_date = new cls_DateTimeI("IT",false);
	$cls_utils = new cls_Utils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$provenienza = $cls_help->getVar('provenienza');  //  TARGHEESTERE o COATTIVA
$analizzatutto = $cls_help->getVar('analizzatutto');
$importatutto = $cls_help->getVar('importatutto');



$scrittaBarra_1 = "Analisi CSV completata";
$scrittaBarra_2 = "Analisi TXT completata";

$PathCompletoImportPagamenti = $_SERVER['DOCUMENT_ROOT'] . "/archivio/Importazioni_Pagamenti/";
$PathCompletoPagamentiEsteri = $_SERVER['DOCUMENT_ROOT'] . "/archivio/Targhe_Estere/Pagamenti/";
$PathImportazioniPagamenti = $_SERVER['DOCUMENT_ROOT'] . "/archivio/Importazioni_Pagamenti/";
$PathCompletoPagamentiDaBonificare = $_SERVER['DOCUMENT_ROOT'] . "/archivio/Importazioni_Pagamenti/DaBonificare/";

$cartellaCsv = $cls_utils->crea_dir($PathCompletoImportPagamenti);
$cartellaTxt = $cartellaCsv;
$cartellaPagamentiEsteri = $cls_utils->crea_dir($PathCompletoPagamentiEsteri);
$cartellaBackUp = $cls_utils->crea_dir($PathCompletoImportPagamenti . "BackUp/");
$cartellaTempFile = $cls_utils->crea_dir($PathImportazioniPagamenti . "Temp/");
$cartellaTemporanea = $cls_utils->crea_dir($PathCompletoImportPagamenti . "Temp/");
$cartellaComplDaBonificare = $cls_utils->crea_dir($PathCompletoPagamentiDaBonificare);

$archivioPath = $_SERVER['DOCUMENT_ROOT']."/archivio";


function alert($message)
{
    echo "<script>alert(\"".$message."\");</script>";
}
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F5
switchMenuImg("F5");
F5_button = function()
{
    location.href="importazione_pagamenti.php?provenienza=<?php echo $provenienza; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
    var strLink = "importazione_pagamenti.php?";
    strLink += "c=" + "<?=$c?>";
    strLink += "&a=" + "<?=$a?>";
    strLink += "&provenienza=" + "<?=$provenienza?>";
    strLink += "&analizzatutto=" + "SI";
    if ("<?=$analizzatutto?>" == "SI") strLink += "&importatutto=" + "SI";
    location.href = strLink;
}

function inizio(scritta, num)
{
	$('#progressbar_' + num).progressbar({
		value: false
	});
	$( "#barlabel_" + num ).text("Inizio " + scritta + " ...");
}

function update(valore, num)
{
	$( "#progressbar_" + num ).progressbar({value: parseInt(valore) });
	$( "#barlabel_" + num ).text( valore + "%" );
}

function nessun_risultato(num)
{
	$( "#progressbar_" + num ).progressbar({value: 100 });
	$( "#barlabel_" + num ).text("Nessun risultato trovato");
	//sleep(1000);
}

function fine(value, num)
{
	$( "#progressbar_" + num ).progressbar({value: 100 });
	$( "#barlabel_" + num ).text( value );
	
	//sleep(1000);
	
	//alert ("Importazione completata");

	//mostra_file();
	//$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Elenco' onclick='mostra_file();'>");
}
function annulla()
{
    location.href="importazione_pagamenti.php?provenienza=<?php echo $provenienza; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}
</script>


    <div class="row justify-content-md-center " style="margin-bottom: 3%; margin-top: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Importazione Pagamenti</span>
        </div>
    </div>

<?php if ($analizzatutto != "SI") { ?>

	<div class="row justify-content-md-center">
		<div class="col col-md-auto text_center">
			<div class="color_red">
				1) Mettere i 2 files ZIP nella cartella 
				<br>
				<i><?=$archivioPath."/Importazioni_Pagamenti"?></i>
				<br><br>
				La cartella viene creata automaticamente nel momento in cui si entra in questa pagina.
				<!-- <a href="file://Server/hd_server/FTP/">blabla</a> -->
				<br><br>
				2) Premere il tasto del Menu "Pagina Successiva"
			</div>
		</div>
	</div>
	
<?php } else { ?>

<div class="row pheight40">
    <div class="col-lg-12">
        <div class="table_interna text_center" id="progressbar_1" style="height:55px;">
            <div class="text_center" id="barlabel_1">
            </div>
        </div>
    </div>
</div>

    <div class="row pheight40">
        <div class="col-lg-12">
            <div class="table_interna text_center" id="progressbar_2" style="height:55px;">
                <div class="text_center" id="barlabel_2">
                </div>
            </div>
        </div>
    </div>

		<div class="row">
            <div class="col-lg-12">
			<table class="table_interna text_center" border="0">
			<tr>
				<td class="sfondo_rosso" colspan="13">
					<b>FILE CSV</b>
				</td>
			</tr>
			<tr>
				<td>
					
				</td>
				<td>
					
				</td>
				<td>
					<font class="font11 color_red">Progr</font>
				</td>
				<td>
					<font class="color_red">Comune</font>
				</td>
				<td>
					<font class="font11 color_red">N.Conto</font>
				</td>
				<td>
					<font class="font11 color_red">Fronte</font>
				</td>
				<td>
					<font class="font11 color_red">Retro</font>
				</td>
				<td>
					<font class="color_red">Data Pag.</font>
				</td>
				<td>
					<font class="color_red">Importo</font>
				</td>
				<td>
					<font class="font11 color_red">Rata</font>
				</td>
				<td>
					<font class="font11 color_red">Anno</font>
				</td>
				<td>
					<font class="font11 color_red">Tipo</font>
				</td>
				<td>
					<font class="color_red">Esito</font>
				</td>
			</tr>
	<?php 
	
	echo "<script>inizio('estrazione dati', 1);</script>";
	
	flush();
	ob_flush();
	flush();
	ob_flush();
	
	sleep (1);
	
	$arrayFileZip = array();
	//$arrayFileImmagini = array();

    //echo $cartellaCsv;
	$handle = opendir($cartellaCsv);
	while (($file = readdir($handle)) != false)
	{
		if ($file != "." && $file != ".." && $file != "Thumbs.db")  //  queste sono cartelle
		{
			if (!is_dir($cartellaCsv . $file))
			{
				$esplEstensione = explode (".", $file);
				$posizEstens = count($esplEstensione) - 1;
				$estensione = strtoupper($esplEstensione[$posizEstens]);
				if ($estensione == "ZIP")
				{
					$arrayFileZip [] = $file;
				}
			}
		}
	}
	closedir($handle);

	if(count($arrayFileZip)==0){
		alert("Nessun file presente nella cartella!");
		echo "<script>annulla();</script>";
		return;
	}
	else if (count($arrayFileZip) != 2)
	{
		alert ("ATTENZIONE! Nella cartella e' presente un solo file ZIP. Proseguendo importerai il singolo file. Se non si e' sicuri non proseguire e controllare i file nella cartella.");
	}
	
	// controllo che esista il file unzip.exe in "/www/html/"
	$comandoUnzip = $_SERVER['DOCUMENT_ROOT'] . "/unzip.exe ";

    //echo "<h1>".$comandoUnzip."</h1>";
	/*if (!file_exists($comandoUnzip))
	{
		alert ("Il file unzip.exe non e' presente nella cartella /www/html/. Impossibile procedere.");
		echo "<script>annulla();</script>";
		return;
	}*/
	
	// scompatto i 2 ZIP
	if ($importatutto != "SI")
	{
        $contatoreAFZ = count($arrayFileZip);
		for ($k = 0; $k < $contatoreAFZ; $k++)
		{
            $cls_zip = new cls_zip();
			/*$comandoUnzip = $_SERVER['DOCUMENT_ROOT'] . "/unzip.exe ";
			$comandoUnzip = str_replace("/", "\\", $comandoUnzip);
			$comandoUnzip = str_replace("Program Files (x86)", "Progra~2", $comandoUnzip);
	
			$comandoUnzip .= "\"" . $cartellaCsv . $arrayFileZip[$k] . "\" ";
			$comandoUnzip .= " -d \"" . $cartellaTemporanea . "\" ";
	
			//echo "<br>" . $comandoUnzip;
			//return;
			exec ($comandoUnzip);*/

            $checkExtraction = $cls_zip->extractZip( $cartellaCsv . $arrayFileZip[$k],$cartellaTempFile);
            if(!$checkExtraction){
                $cls_help->alert("ERRORE ESTRAZIONE");
                die;
            }

		}
	}
	
	echo "<script>inizio('elaborazione', 1);</script>";
	
	flush();
	ob_flush();
	flush();
	ob_flush();
	
	//sleep (1);
	
	
	// a questo punto dovrei avere in TEMP:
	// - 1 file CSV con la lista dei pagamenti con le immagini
	// - 1 file TXT con la lista di tutti i pagamenti (con e senza img)
	// - tutte le immagini dei pagamenti
	
	$myFileCsv = "";
	$myFileTxt = "";
	$arrayFileImmagini = array();
	
	$handle = opendir($cartellaTemporanea);
	while (($file = readdir($handle)) != false)
	{
		if ($file != "." && $file != ".." && $file != "Thumbs.db")  //  queste sono cartelle
		{
			if (!is_dir($cartellaTemporanea . $file))
			{
				$esplEstensione = explode (".", $file);
				$posizEstens = count($esplEstensione) - 1;
				$estensione = strtoupper($esplEstensione[$posizEstens]);
				if ($estensione == "CSV")
				{
					$myFileCsv = $file;
				}
				else if ($estensione == "TXT")
				{
					$myFileTxt = $file;
				}
				else if ($estensione == "TIF")
				{
					$arrayFileImmagini[] = $file;
				}
				else // controllo che non ci siano altri tipi di file ?
				{
					alert ("In TEMP e' presente il file $file con estensione non gestita");
				}
			}
		}
	}
	closedir($handle);
	
	$contenuto = array();
	$listaTuttiQuintiCampi = array();
	$contatore = 0;

	$stileriga = "sfondo_new_gitco";
	
	// leggo il file CSV per sapere quante e quali sono le immagini che mi servono
	// solo quelle inserite nel CSV verranno convertite in JPEG al 5%
	if($myFileCsv==""){
		alert("File CSV mancante!");	
	}
	else{
		
	$fp = fopen ($cartellaTemporanea . $myFileCsv, "r+");
	
	while ($fp && !feof($fp))
	{
		$temp = str_replace('"', '', fgets($fp));
		
		$esplodoPuntoVirg = explode (";", $temp);
		if ($esplodoPuntoVirg[0] != "ACCOUNTNUMBER" && $esplodoPuntoVirg[0] != "")  //  prendo le righe che iniziano col numero di conto
		{
			for ($k = 0; $k < count($esplodoPuntoVirg); $k++)
			{
				$contenuto[$contatore][$k] = trim($esplodoPuntoVirg[$k]);
			}
			$contenuto[$contatore][$k] = $myFileCsv;  //  metto in coda anche il nome del file CSV
			
			if (!IsNomeInArray($contenuto[$contatore][18], $arrayFileImmagini))
				echo "<br>Il file (fronte) " . $contenuto[$contatore][18] . " non e' nella cartella TEMP";
			
			if (!IsNomeInArray($contenuto[$contatore][19], $arrayFileImmagini))
				echo "<br>Il file (retro) " . $contenuto[$contatore][19] . " non e' nella cartella TEMP";
			
			$contatore++;
		}
	}
	fclose($fp);
	
	//return;

    $countContenuto = count($contenuto);

    //var_dump($contenuto);
	
	for ($k = 0; $k < $countContenuto; $k++)
	{
		set_time_limit(30);
		
		echo "<script>update(".ceil($k*100/count($contenuto)).", 1);</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		if ($stileriga == "sfondo_new_gitco") $stileriga = "riga_dispari";
		else $stileriga = "sfondo_new_gitco";
		
		//$contenuto[$k][0]: numero del conto corrente  ACCOUNTNUMBER
		//$contenuto[$k][1]: provincia del pagamento ???  LOCATION
		//$contenuto[$k][2]: data del caricamento (sempre vuoto?)  DATACARICAMENTO
		//$contenuto[$k][3]: data del pagamento in formato 20141028  DATAOPERAZIONE
		//$contenuto[$k][4]: codice provincia del pagamento ???  CODICEPROVINCIA
		//$contenuto[$k][5]: codice ufficio del pagamento ???  CODICEUFFICIO
		//$contenuto[$k][6]: tipo bollettino (123, 674, ecc)  TIPODOCUMENTO
		//$contenuto[$k][7]: importo pagato (moltiplicato x 100: 13,45 euro => trovo 1345)  IMPORTODOCUMENTO
		//$contenuto[$k][8]: ???  DATAPREALLIBRAMENTO
		//$contenuto[$k][9]: ??? in formato 20141028  DATAPOSTALLIBRAMENTO
		//$contenuto[$k][10]: nostro quinto campo  QUARTOCAMPO
		//$contenuto[$k][11]: ???  PROGRESSIVODIMARCAGGIO
		//$contenuto[$k][12]: ???  NCONTOTRAENTE
		//$contenuto[$k][13]: divisa (E euro)  DIVISA
		//$contenuto[$k][14]: ???  FLAGINSANABILI
		//$contenuto[$k][15]: ???  PROGRESSIVODISELEZIONE
		//$contenuto[$k][16]: ???  REPORT_VERSION
		//$contenuto[$k][17]: ???  SV
		//$contenuto[$k][18]: Immagine fronte
		//$contenuto[$k][19]: Immagine retro
		//$contenuto[$k][20]: nome del file importato (aggiunto dal programma, non c'� nel file)  // 20-12-2014
			
		$avanti = 0;
		
		$numeroTempContoCorrente = trim($contenuto[$k][$avanti++]);
		$provinciaPosta = trim($contenuto[$k][$avanti++]);
		$dataCaricamento = trim($contenuto[$k][$avanti++]);
		if ($dataCaricamento == "") $dataCaricamento = null;
		// la data di pagamento arriva in formato 20141028
		$dataPagamento = $contenuto[$k][$avanti++];
		if ($dataPagamento == "") $dataPagamento = null;
		else $dataPagamento = substr($dataPagamento, 0, 4) . "-" . substr($dataPagamento, 4, 2) . "-" . substr($dataPagamento, 6, 2);
		$codiceProvinciaPosta = $contenuto[$k][$avanti++];
		$codiceUfficioPosta = $contenuto[$k][$avanti++];
		$tipoBollettino = $contenuto[$k][$avanti++];
		// l'importo arriva 1345 cio� 13,45 �
		$importoPagato = $contenuto[$k][$avanti++] / 100;
		$dataPreallibramento = $contenuto[$k][$avanti++];
		if ($dataPreallibramento == "") $dataPreallibramento = null;
		$dataPostallibramento = $contenuto[$k][$avanti++];
		if ($dataPostallibramento == "") $dataPostallibramento = null;
		else $dataPostallibramento = substr($dataPostallibramento, 0, 4) . "-" . substr($dataPostallibramento, 4, 2) . "-" . substr($dataPostallibramento, 6, 2);
		// il quinto campo arriva come *1234567890123456*
		$quintoCampo = $contenuto[$k][$avanti++];
		if (substr($quintoCampo, 0, 1) == "*") $quintoCampo = substr($quintoCampo, 1, strlen($quintoCampo)-2);
		$progressivoMarcaggio = $contenuto[$k][$avanti++];
		$contoTraente = $contenuto[$k][$avanti++];
		$divisaPagamento = trim($contenuto[$k][$avanti++]);  //  mi arriva "E "
		$flagInsanabili = $contenuto[$k][$avanti++];
		$progressivoSelezione = $contenuto[$k][$avanti++];
		$reportVersion = $contenuto[$k][$avanti++];
		$svsvsv = $contenuto[$k][$avanti++];
		$imgFronte = strtoupper($contenuto[$k][$avanti++]);
		$imgRetro = trim(strtoupper($contenuto[$k][$avanti++]));
		$nomeFileCsv = trim(strtoupper($contenuto[$k][$avanti++]));
		
		//$myQuintoCampo = new gestione_quinto_campo();
	
		$arrayQuintoCampo = $cls_elab->estrai_quinto_campo($quintoCampo);
		
		$quintoCcComune = $arrayQuintoCampo[0];
		$quintoTipoServizio = $arrayQuintoCampo[1];
		$quintoNumeroRata = $arrayQuintoCampo[2];
		$quintoAnnoGestione = $arrayQuintoCampo[3];
		$quintoAtto = $arrayQuintoCampo[4];
		$quintoScompatCampo = $quintoCampo . " - ";
		$quintoScompatCampo .= $quintoCcComune . " - ";
		$quintoScompatCampo .= $quintoTipoServizio . " - ";
		$quintoScompatCampo .= $quintoNumeroRata . " - ";
		$quintoScompatCampo .= $quintoAnnoGestione . " - ";
		$quintoScompatCampo .= $quintoAtto;
		
		$quietanza = substr($quintoCampo, -4, 4);
		
		$listaTuttiQuintiCampi[] = $quintoCampo;
		
		$numeroContoCorrente = "";
		$trovatoNum = false;
		for ($i = 0; $i < strlen($numeroTempContoCorrente); $i++)  //  arriva 000012300 : prendo 12300
		{
			$nummm = substr($numeroTempContoCorrente, $i, 1);
			if ($trovatoNum == false && $nummm == "0")
			{
				//$numeroContoCorrente
			}
			else
			{
				$trovatoNum = true;
				$numeroContoCorrente .= $nummm;
			}
		}

		$problemaImg = 0;
		
		$tipologiaPagamento = "";
		$myProgrNot = "0";
		$cartellaPagAvvisi = "";
		$cartellaCompletaPagAvvisi = "";
		$breveTipo = "?";
		$attoCompleto = "";
		$tipoContoCorrente = "";

		if ($quintoCcComune == "")  // non ho riconosciuto il comune
		{
			$problemaImg = 1;
			$quintoTipoServizio = "";
		}

        $a_atto[0] = 0;
		$documentTypeId = intval($quintoTipoServizio);
        $documentTableTypeId = 0;

		switch ($quintoTipoServizio)  //  tipo servizio
		{
			case "00":
				$tipologiaPagamento = "VERBALE";
				$breveTipo = "ACC";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$tipoContoCorrente = "";
				break;
//			case "01":
//				$tipologiaPagamento = "SOLLECITO_CDS";
//				$breveTipo = "SOLL_CDS";
//				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
//				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
//				$tipoContoCorrente = "CDS";
//				break;

            case "11":
                $tipologiaPagamento = "SOLLECITO_PRE_INGIUNZIONE";
                $breveTipo = "SOLL_PRE";
                $cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
                $cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;

                //$myAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("SOLL_PRE", $attoCompleto, $quintoCcComune,"Atto");
                //$myAtto = new atto($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM atto WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");

                $tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 1;
                $documentTypeId = $myAtto->DocumentTypeId;

                break;

            case "12":
                $tipologiaPagamento = "AVVISO_MORA";
                $breveTipo = "AV_MORA";
                $cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
                $cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;

                //$myAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("AV_MORA", $attoCompleto, $quintoCcComune,"Atto");
                //$myAtto = new atto($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM atto WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");

                $tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 1;
                $documentTypeId = $myAtto->DocumentTypeId;
                break;

			case "02":
                $tipologiaPagamento = "INGIUNZIONE";
                $breveTipo = "ING";
                $cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
                $cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
                $attoCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("INGIUNZIONE", $attoCompleto, $quintoCcComune,"Atto");
                //$myAtto = new atto($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM atto WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");

                $tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 1;
                $documentTypeId = $myAtto->DocumentTypeId;

                break;

			case "03":
				$tipologiaPagamento = "SOLLECITO_INGIUNZIONE";
				$breveTipo = "SOL_ING";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;

                $attoCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("SOLLECITOINGIUNZIONE", $attoCompleto, $quintoCcComune,"Atto");
                //$myAtto = new atto($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM atto WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");

                $tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 1;
                $documentTypeId = $myAtto->DocumentTypeId;

				break;

			case "04":
				$tipologiaPagamento = "AVVISO_INTIMAZIONE";
				$breveTipo = "AVINT";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;
				
				//$myAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("AVVISOINTIMAZIONE", $attoCompleto, $quintoCcComune, "Atto");
                //$myAtto = new atto($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM atto WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");

                $tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 1;
                $documentTypeId = $myAtto->DocumentTypeId;
				break;

			case "05":
				$tipologiaPagamento = "SOLLECITO_AVVISO_INTIMAZIONE";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
                $documentTableTypeId = 1;
                $documentTypeId = 5;
				break;

			case "06":
				$tipologiaPagamento = "PIGNORAMENTO_VEICOLO";
				$breveTipo = "PIGNOVEIC";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;
				
				//$myAtto = new pignoramento(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("veicolo", $attoCompleto, $quintoCcComune, "Pigno");
                //$myAtto = new pignoramento($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");

                $tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 2;
                $documentTypeId = $myAtto->DocumentTypeId;

				break;
			case "07":
				$tipologiaPagamento = "PIGNORAMENTO_DATORE_LAVORO";
				$breveTipo = "PIGNOLAVORO";
				
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;
				
				//$myAtto = new pignoramento(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("terzi", $attoCompleto, $quintoCcComune,"Pigno");
                //$myAtto = new pignoramento($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");

                $tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 2;
                $documentTypeId = $myAtto->DocumentTypeId;
				break;
			case "08":
				$tipologiaPagamento = "PIGNORAMENTO_BANCA";
				$breveTipo = "PIGNOBANCA";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;
				
				//$myAtto = new pignoramento(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("terzi", $attoCompleto, $quintoCcComune,"Pigno");
				//$myAtto = new pignoramento($a_atto[0], $quintoCcComune);
                $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$a_atto[0]." AND CC = '".$quintoCcComune."'";
                $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");
				
				$tipoContoCorrente = $a_atto[1];
                $documentTableTypeId = 2;
                $documentTypeId = $myAtto->DocumentTypeId;
				break;
			default:
				//echo "<br>Il tipo del servizio $arrayQuintoCampo[1] non � gestito";
				$cartellaPagAvvisi = $PathCompletoPagamentiDaBonificare;
				$cartellaCompletaPagAvvisi = $cartellaPagAvvisi;
				//if ($problemaImg == 0)
				{
					$problemaImg = 2;  // non ho riconosciuto il tipo di servizio
				}
				break;
		}
		
		if (!$a_atto[0]>0 && $problemaImg == 0)
			$problemaImg = 11;


		if ($divisaPagamento == "E") $divisaTradotta = "&euro;";
		else $divisaTradotta = $divisaPagamento;
		
	
		//if ($cartellaPagAvvisi != "" && $arrayQuintoCampo[0] != "")  //  se ho identificato il comune
		if (1)
		{
			if ($imgFronte != "")
			{
				$esplodoXX = explode (".", $imgFronte);
				$radiceImg = $esplodoXX[0];
				$nomeBreveJpgFronte = $radiceImg . ".JPG";
				$nuovoNomeFileFronte = $cartellaCompletaPagAvvisi . $nomeBreveJpgFronte;
			}
			else 
			{
				$nomeBreveJpgFronte = $nuovoNomeFileFronte = "";
			}
				
			if ($imgRetro != "")
			{
				$esplodoXX = explode (".", $imgRetro);
				$radiceImg = $esplodoXX[0];
				$nomeBreveJpgRetro = $radiceImg . ".JPG";
				$nuovoNomeFileRetro = $cartellaCompletaPagAvvisi . $nomeBreveJpgRetro;
			}
			else 
			{
				$nomeBreveJpgRetro = $nuovoNomeFileRetro = "";
			}

            $query = "SELECT * FROM pagamenti_importati WHERE ID = 'NULL'";
			$myPagamentoImportato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");// new pagamenti_importati(NULL);

            $myPagamentoImportato->DocumentTypeId = $documentTypeId;
            $myPagamentoImportato->DocumentTableTypeId = $documentTableTypeId;
			$myPagamentoImportato->Tipo_Pagamento = $tipologiaPagamento;
			$myPagamentoImportato->Riferimento_Atto = $myProgrNot;
			$myPagamentoImportato->Comune_Riferimento = $quintoCcComune;
			$myPagamentoImportato->Conto_Corrente = $numeroContoCorrente;
			$myPagamentoImportato->Provincia_Posta = $provinciaPosta;
			$myPagamentoImportato->Data_Caricamento = $dataCaricamento;
			$myPagamentoImportato->Data_Pagamento = $dataPagamento;
			$myPagamentoImportato->Codice_Provincia_Posta = $codiceProvinciaPosta;
			$myPagamentoImportato->Codice_Ufficio_Posta = $codiceUfficioPosta;
			//$myPagamentoImportato->Codice_Txt_Composto = "";
			$myPagamentoImportato->Telematico = "N";
			$myPagamentoImportato->Tipo_Bollettino = $tipoBollettino;
			$myPagamentoImportato->Importo_Pagato = $importoPagato;
			$myPagamentoImportato->Data_Preallibramento = $dataPreallibramento;
			$myPagamentoImportato->Data_Postallibramento = $dataPostallibramento;
			$myPagamentoImportato->Quinto_Campo = $quintoCampo;
			$myPagamentoImportato->Progressivo_Marcaggio = $progressivoMarcaggio;
			$myPagamentoImportato->Conto_Traente = $contoTraente;
			$myPagamentoImportato->Divisa_Pagamento = $divisaPagamento;
			//$myPagamentoImportato->Divisa_Txt_Pagamento = "";
			$myPagamentoImportato->Flag_Insanabili = $flagInsanabili;
			$myPagamentoImportato->Progressivo_Selezione = $progressivoSelezione;
			$myPagamentoImportato->Report_Version = $reportVersion;
			$myPagamentoImportato->SV = $svsvsv;
			$myPagamentoImportato->Immagine_Fronte = $nomeBreveJpgFronte;
			$myPagamentoImportato->Immagine_Retro = $nomeBreveJpgRetro;
			//$myPagamentoImportato->Codice_Txt_Sconosciuto = "";
			//$myPagamentoImportato->Tipo_Txt_Bollettino = "";
			//$myPagamentoImportato->Sostitutivo_Txt = "";
			$myPagamentoImportato->Nome_File = $nomeFileCsv;
			
			$control_importazione = $cls_elab->PagamImportatoGiaPresente($quintoCampo,$importoPagato);
			
			if ($imgFronte != "" && file_exists($cartellaTemporanea . $imgFronte))
			{
				$myImmagineFronte = IMMAGINIWEB."/spunta.jpg";
			}
			else if ($imgFronte == "")
			{
				$myImmagineFronte = IMMAGINIWEB."/tasto_azzurro.jpg";
			}
			else
			{
				$myImmagineFronte = IMMAGINIWEB."/spuntaNO.jpg";
				if ($problemaImg == 0)
				{
					if ($control_importazione == "")
						$problemaImg = 3;
					//alert ($problemaImg . " e " . $statoDellaNotifica);
					//return;
				}
			}
				
			if ($imgRetro != "" && file_exists($cartellaTemporanea . $imgRetro))
			{
				$myImmagineRetro = IMMAGINIWEB."/spunta.jpg";
			}
			else if ($imgRetro == "")
			{
				$myImmagineRetro = IMMAGINIWEB."/tasto_azzurro.jpg";
			}
			else
			{
				$myImmagineRetro = IMMAGINIWEB."/spuntaNO.jpg";
				if ($problemaImg == 0)
				{
					if ($control_importazione == "")
						$problemaImg = 3;
					//alert ($problemaImg . " e " . $statoDellaNotifica);
					//return;
				}
			}
			
			$contoTerzi = "";
			if ($tipoContoCorrente == "")
			{
				if ($problemaImg == 0) $problemaImg = 13;
			}
			else
			{
			    $query = "SELECT * FROM parametri_pagamento WHERE CC = '".$quintoCcComune."' AND Tipo_Riscossione = '".$tipoContoCorrente."'";
				$myParametro = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_pagamento");//new parametri_pagamento($quintoCcComune, $tipoContoCorrente);
				if ($myParametro->ID == "") $problemaImg = 13;
				else $contoTerzi = $cls_elab->data_conto_terzi($dataPagamento,$myParametro->Data_Cambio_Conto, $myParametro->Conto_Terzi);
			}
			
			$daCsvBonificare = false;
			
			if ($problemaImg != 0)
			{
				$myImmagineOKNO = IMMAGINIWEB."/spuntaNO.jpg";
					
				switch ($problemaImg)
				{
					case 1:
						$esitoOKNO = "Problema: Comune Riferimento assente DA BONIFICARE";
						$myImmagineOKNO = IMMAGINIWEB."/FrecciaDx.png";
						$problemaImg = 0;
						$daCsvBonificare = true;
						//$tipologiaPagamento = "";  //  � probabile che sia stato letto casualmente una tipologia, ma il quinto campo non � leggibile
						//$myPagamentoImportato->Tipo_Pagamento = "";
						break;
					case 2:
						$esitoOKNO = "Problema: Tipo Servizio Sconosciuto DA BONIFICARE";
						$myImmagineOKNO = IMMAGINIWEB."/FrecciaDx.png";
						$problemaImg = 0;
						$daCsvBonificare = true;
						break;
					case 3:
						$esitoOKNO = "Problema: Immagini assenti";
						break;
					case 11:
						$esitoOKNO = "Problema: Atto non trovato";
						break;
					case 13:
						$esitoOKNO = "Problema: Tipo Conto Corrente non definito ".$tipoContoCorrente;
						break;
					default:
						$esitoOKNO = "Problema: sconosciuto";
						break;
				}
			}
			else
			{
				$myImmagineOKNO = IMMAGINIWEB."/spunta.jpg";
				$esitoOKNO = "OK";
			}
			
			if ($importatutto == "SI" && $problemaImg == 0)
			{
				$scrittaBarra_1 = "Importazione CSV completata";
				$erroreTrovato = false;
				
				//echo "<br><br>" . $cartellaTemporanea . $imgFronte . " ---> " . $nuovoNomeFileFronte . "<br><br>";
				//echo "<br>" . $cartellaTemporanea . $imgRetro . " ---> " . $nuovoNomeFileRetro;
				
				//da rimettere
				if ($imgFronte != "" && file_exists($cartellaTemporanea . $imgFronte))
				{
                    //echo $cartellaCompletaPagAvvisi;
					$percorsoImg = $cls_utils->crea_dir($cartellaCompletaPagAvvisi);
					
					$im = new imagick( $cartellaTemporanea . $imgFronte );
					
					$im->setImageCompression(Imagick::COMPRESSION_JPEG);
					$im->setImageCompressionQuality(5);
					$im->writeImage( $nuovoNomeFileFronte );
					
					//echo "<br>" . $tipologiaPagamento . " - " . $nuovoNomeFileFronte;
					
					unlink ($cartellaTemporanea . $imgFronte);
				}
				if ($imgRetro != "" && file_exists($cartellaTemporanea . $imgRetro))
				{
					$percorsoImg = $cls_utils->crea_dir($cartellaCompletaPagAvvisi);
					
					$im = new imagick( $cartellaTemporanea . $imgRetro );
					
					$im->setImageCompression(Imagick::COMPRESSION_JPEG);
					$im->setImageCompressionQuality(5);
					$im->writeImage( $nuovoNomeFileRetro );
					
					unlink ($cartellaTemporanea . $imgRetro);
				}
				
				if ($daCsvBonificare == false)
				{
                    $query = "SELECT * FROM pagamento WHERE ID = 'NULL' AND CC = '".$c."'";
					$myNewPagamento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamento");// new pagamento(null, $c);
					
					switch ($quintoTipoServizio)  //  tipo servizio
					{
						case "00":
							$testoDbTipoPag = "Verbale";  //  ??
							break;
						case "01":
							$testoDbTipoPag = "Sollecito";  //  ??
							break;
                        case "11":
                            $testoDbTipoPag = "Sollecito pre ingiunzione";  //  ??
                            break;
                        case "12":
                            $testoDbTipoPag = "Avviso di messa in mora";  //  ??
                            break;
						case "02":
							$testoDbTipoPag = "Ingiunzione";  //  ??
							break;
						case "03":
							$testoDbTipoPag = "Sollecito Ingiunzione";  //  ??
							break;
						case "04":
							$testoDbTipoPag = "Avviso di intimazione ad adempiere";  //  ??
							break;
						case "05":
							$testoDbTipoPag = "Sollecito avviso intimazione";  //  ??
							break;
						case "06":
							$testoDbTipoPag = "Pignoramento beni mobili registrati";  //  ??
							break;
						case "07":
							$testoDbTipoPag = "Pignoramento presso datore di lavoro";  //  ??
							break;
						case "08":
							$testoDbTipoPag = "Pignoramento presso banca";  //  ??
							break;
						default:
							$testoDbTipoPag = "";
							break;
					}

                    $prossimoComune = $cls_elab->ProssimoComuneId($quintoCcComune);
					
					$myNewPagamento->Comune_ID = $prossimoComune;
					$myNewPagamento->CC = $quintoCcComune;
					$myNewPagamento->Partita_ID = $myAtto->Partita_ID;
					$myNewPagamento->Atto_ID = $myAtto->ID;
					$myNewPagamento->Riferimento_Atto = 1;  // non usato
					$myNewPagamento->Tipo_Atto = $testoDbTipoPag;
					$myNewPagamento->Pagante = "";
					$myNewPagamento->Conto_Terzi = $contoTerzi;  //  parametro comune
					$myNewPagamento->Data_Pagamento = $dataPagamento;
					$myNewPagamento->Data_Registrazione = date("Y-m-d");
					$myNewPagamento->Modalita = "C/C";  //  ??  bolletta o C/C
					$myNewPagamento->Importo = $importoPagato;
					
					if($myAtto->Rate_Previste==0 || $myAtto->Rate_Previste==null)
						$myNewPagamento->Dovuto = $myAtto->Totale_Dovuto;
					else {
                        $importiRate = explode("*" , $myAtto->Importi_Rate);
                        $myNewPagamento->Dovuto = str_replace(",",".",$importiRate[$quintoNumeroRata - 1]);
                    }
						
					$myNewPagamento->Quietanza = $quietanza;
					$myNewPagamento->Bollettario = "";
					$myNewPagamento->Rata = $quintoNumeroRata;
					$myNewPagamento->Totale_Rate = $myAtto->Rate_Previste;  //  non usato
					$myNewPagamento->Note = "";
					$myNewPagamento->Bollettino = str_replace(".TIF", ".JPG", $imgFronte);
					$myNewPagamento->Telematico = "N";
					$myNewPagamento->Tipo_Pagamento = "AUTOMATICO";
					$myNewPagamento->Data_Travaso_A_Gitco = 'NULL';
                    $myNewPagamento->DocumentTypeId = $documentTypeId;
                    $myNewPagamento->DocumentTableTypeId = $documentTableTypeId;
					
					$rispInsUpd = $cls_elab->InsertUpdatePagamento($myNewPagamento);
					
					$myPagamentoImportato->Esito = "IMPORTATO";
				}
				else $myPagamentoImportato->Esito = "DABONIFICARE";
					
				$rispImport = $cls_elab->InsertUpdatePagamImportato($myPagamentoImportato);
			
			}  //  fine importazione definitiva CSV
			//else echo "<br><br>" . $problemaImg . "<br><br>";
			//die;
			?>
					
							<tr class="<?=$stileriga?>">
								<td>
									<font class="font11"><?=($k+1)?></font>
								</td>
								<td>
									<label class="font11" title="<?=$tipologiaPagamento?>"><?=$breveTipo?></label>
								</td>
								<td>
									<label class="font11" title="ID: <?=$myProgrNot?>"><?=$attoCompleto?></label>
								</td>
								<td>
									<?=$quintoCcComune?>
								</td>
								<td>
									<font class="font11"><i><?=$numeroContoCorrente?></i></font>
								</td>
								<td>
									<img src="<?=$myImmagineFronte?>" class="pwidth20 pheigth20" title="<?=$imgFronte?>">
								</td>
								<td>
									<img src="<?=$myImmagineRetro?>" class="pwidth20 pheigth20" title="<?=$imgRetro?>">
								</td>
								<td>
									<?=$cls_date->Get_DateNewFormat($dataPagamento,"DB");?>
								</td>
								<td class="text_right">
									<?=number_format($importoPagato, 2, ",", ".")?> &euro;
								</td>
								<td>
									<font class="font11"><?=$quintoNumeroRata?></font>
								</td>
								<td>
									<font class="font11"><?=$quintoAnnoGestione?></font>
								</td>
								<td>
									<div title="Quinto Campo: <?=$quintoCampo?>"><font class="font11 corsivo"><?=$tipoBollettino?></font></div>
								</td>
								<td>
									<img src="<?=$myImmagineOKNO?>" class="pwidth20 pheigth20" title="<?=$esitoOKNO?>">
								</td>
							</tr>
						
						<?php
						
		}  //  fine if 1
		
	}  //  fine for CSV
	
	//return;

	}//else se presente CSV
	echo "<script>fine('$scrittaBarra_1', 1);</script>";
	?>
			
				</table>
        </div>
        </div>
			<div class="row">
                <div class="col-lg-12">
				<table class="table_interna text_center" border="0">
				<tr>
					<td class="sfondo_rosso" colspan="13">
						<b>FILE TXT (in grigio le righe gia' presenti nel CSV)</b>
					</td>
				</tr>
				<tr>
					<td>
						
					</td>
					<td>
						
					</td>
					<td>
						<font class="font11 color_red">Progr</font>
					</td>
					<td>
						<font class="color_red">Comune</font>
					</td>
					<td>
						<font class="font11 color_red">N.Conto</font>
					</td>
					<td>
						<font class="font11 color_red">Fronte</font>
					</td>
					<td>
						<font class="font11 color_red">Retro</font>
					</td>
					<td>
						<font class="color_red">Data Pag.</font>
					</td>
					<td>
						<font class="color_red">Importo</font>
					</td>
					<td>
						<font class="font11 color_red">Rata</font>
					</td>
					<td>
						<font class="font11 color_red">Anno</font>
					</td>
					<td>
						<font class="font11 color_red">Tipo</font>
					</td>
					<td>
						<font class="color_red">Esito</font>
					</td>
				</tr>
				
	<?php 

	$contenutTxt = array();
	$array_presenza_file = array();
	$contatorTxt = 0;
	
	// leggo il file TXT : escludo i quinti campi gi� presenti nel CSV
	
	$numeroTxtContoCorrente = "";
	
	if($myFileTxt==""){
		alert("File TXT mancante!");
	}
	else{
	
	$fp = fopen ($cartellaTemporanea . $myFileTxt, "r+");
	
	while ($fp && !feof($fp))
	{
		$temp = str_replace('"', '', fgets($fp));
	
		$esplodoPuntoVirg = explode (";", $temp);
		//echo "<br>" . $esplodoPuntoVirg[0];
		if ($esplodoPuntoVirg[0] == "" && isset($esplodoPuntoVirg[1]) && $esplodoPuntoVirg[1] != "")  //  prendo le righe che iniziano col campo vuoto
		{
// 			if (!IsNomeInArray($esplodoPuntoVirg[5], $listaTuttiQuintiCampi))  //  prendo solo se non erano nel CSV
			if (1)
			{
				//echo " SI";
				for ($k = 0; $k < count($esplodoPuntoVirg); $k++)
				{
					$contenutTxt[$contatorTxt][$k] = trim($esplodoPuntoVirg[$k]);
					// ATTENZIONE L'ULTIMO CAMPO E' VUOTO: NON ESISTE, LO TOLGO DALL'ARRAY
				}
				$k--;
				$array_presenza_file[$contatorTxt]['FILE_TXT'] = $myFileTxt;  //  metto in coda anche il nome del file TXT
				
				if (IsNomeInArray($esplodoPuntoVirg[5], $listaTuttiQuintiCampi)){
					$array_presenza_file[$contatorTxt]['PRESENZA'] = "PRESENTE";  //  metto in coda anche se � gi� presente in CSV!!
				}
				else {
					$array_presenza_file[$contatorTxt]['PRESENZA'] = "/";  //  NON � PRESENTE
				}

				$contatorTxt++;
			}
			else
			{
				//echo "<br>" . $esplodoPuntoVirg[5] . " NO";
				//return;
			}
		}
		else if ($esplodoPuntoVirg[0] != "")  //  la prima riga contiene solo il numero di conto
		{
			$numeroTxtCCTemp = trim($esplodoPuntoVirg[0]);
			$trovatoNum = false;
			for ($i = 0; $i < strlen($numeroTxtCCTemp); $i++)  //  arriva 000012300 : prendo 12300
			{
				$nummm = substr($numeroTxtCCTemp, $i, 1);
				if ($trovatoNum == false && $nummm == "0")
				{
					//$numeroTxtContoCorrente
				}
				else 
				{
					$trovatoNum = true;
					$numeroTxtContoCorrente .= $nummm;
				}
			}
		}
	}
	fclose($fp);
	
	echo "<script>inizio('elaborazione', 2);</script>";
	
	flush();
	ob_flush();
	flush();
	ob_flush();
	
	$scrittaBarra_2 = "Analisi TXT completata: premere su Pagina Successiva";
	for ($k = 0; $k < count($contenutTxt); $k++)
	{
		set_time_limit(30);
		
		echo "<script>update(".ceil($k*100/count($contenutTxt)).", 2);</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		if ($stileriga == "sfondo_new_gitco") $stileriga = "riga_dispari";
		else $stileriga = "sfondo_new_gitco";
		
		//$contenutTxt[$k][0]: ""
		//$contenutTxt[$k][1]: data accredito (?)
		//$contenutTxt[$k][2]: data pagamento (?)
		//$contenutTxt[$k][3]: tipo bollettino (123, 674, ecc)
		//$contenutTxt[$k][4]: codice composto: 03813603 -->  038 CODICEPROVINCIA  - 136 CODICEUFFICIO  -  03 ???
		//                                                    055111 TELEMATICO!!!
		//$contenutTxt[$k][5]: nostro quinto campo
		//$contenutTxt[$k][6]: divisa (EUR euro)
		//$contenutTxt[$k][7]: importo pagato (13,45)
		//$contenutTxt[$k][8]: ???
		//$contenutTxt[$k][9]: tipo conto (??)  CC  o  DI
		//$contenutTxt[$k][10]: ??   N
		
		
		//$array_presenza_file[$k]['FILE_TXT']: nome del file importato (aggiunto dal programma, non c'� nel file)  // 20-12-2014
		//$array_presenza_file[$k]['PRESENZA']: riga del TXT gi� presente nel CSV  // 20-12-2014
			
		$avanti = 1;
		
		$dataTxtAccredito = $cls_date->GetDateDB($contenutTxt[$k][$avanti++],"IT");// to_mysql_date($contenutTxt[$k][$avanti++]);
		$dataTxtPagamento = $cls_date->GetDateDB($contenutTxt[$k][$avanti++],"IT");//to_mysql_date($contenutTxt[$k][$avanti++]);
		$tipoTxtBollettino = $contenutTxt[$k][$avanti++];
		$tempTxtCodice = $contenutTxt[$k][$avanti++];
		$codiceTxtProvinciaPosta = substr($tempTxtCodice, 0, 3);
		$codiceTxtUfficioPosta = substr($tempTxtCodice, 3, 3);
		$quintoTxtCampo = $contenutTxt[$k][$avanti++];
		//if (substr($quintoTxtCampo, 0, 1) == "*") $quintoTxtCampo = substr($quintoTxtCampo, 1, strlen($quintoTxtCampo)-3);
		$divisaTxtPagamento = trim($contenutTxt[$k][$avanti++]);
		$importoTxtPagato = trim($contenutTxt[$k][$avanti++]);
		$importoTxtPagato = str_replace(",", ".", $importoTxtPagato);
		$nonSoTxt1 = trim($contenutTxt[$k][$avanti++]);
		$tipoTxtConto = trim($contenutTxt[$k][$avanti++]);
		$sostitutivoTxt = trim($contenutTxt[$k][$avanti++]);
		
		$nomeFileTxt = trim(strtoupper($array_presenza_file[$k]['FILE_TXT']));
		$giaPresente = trim(strtoupper($array_presenza_file[$k]['PRESENZA']));

		
		//$myTxtQuintoCampo = new gestione_quinto_campo();
		
		$arrayQuintoCampo = $cls_elab->estrai_quinto_campo($quintoTxtCampo);
		
		$quintoCcComune = $arrayQuintoCampo[0];
		$quintoTipoServizio = $arrayQuintoCampo[1];
		$quintoNumeroRata = $arrayQuintoCampo[2];
		$quintoAnnoGestione = $arrayQuintoCampo[3];
		$quintoAtto = $arrayQuintoCampo[4];
		$quintoScompatCampo = $quintoTxtCampo . " - ";
		$quintoScompatCampo .= $quintoCcComune . " - ";
		$quintoScompatCampo .= $quintoTipoServizio . " - ";
		$quintoScompatCampo .= $quintoNumeroRata . " - ";
		$quintoScompatCampo .= $quintoAnnoGestione . " - ";
		$quintoScompatCampo .= $quintoAtto;
		
		$quietTxtAnza = substr($quintoTxtCampo, -4, 4);
		
		$problemaTxtImg = 0;
		
		$tipologiaPagamento = "";
		$myProgrTxtNot = "0";
		$cartellaPagAvvisi = "";
		$cartellaCompletaPagAvvisi = "";
		$breveTxtTipo = "?";
		$attoTxtCompleto = "";
		$tipoTxtContoCorrente = ""; 

		if ($giaPresente == "PRESENTE")
		{
			$stileriga = "sfondo_grigio";
			$problemaTxtImg = 12;
		}
		
		if ($quintoCcComune == "")  // non ho riconosciuto il comune
		{
			if ($problemaTxtImg == 0) $problemaTxtImg = 1;
			$quintoTipoServizio = "";
		}

        $a_atto[0] = 0;
        $documentTypeId = (int) $quintoTipoServizio;
        $documentTableTypeId = 0;
        $tipoTxtContoCorrente = null;

		switch ($quintoTipoServizio)  //  tipo servizio
		{
			case "00":
				$tipologiaPagamento = "VERBALE";
				$breveTxtTipo = "ACC";
				$tipoTxtContoCorrente = "";

				break;
			case "01":
				$tipologiaPagamento = "SOLLECITO";
				$breveTxtTipo = "SOLL";
				$tipoTxtContoCorrente = "";
				break;
            case "11":
                $tipologiaPagamento = "SOLLECITO_PRE_INGIUNZIONE";
                $breveTxtTipo = "SOLL_PRE";

                $cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
                $cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
                $attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("SOLL_PRE", $attoTxtCompleto, $quintoCcComune,"Atto");

                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM atto WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "atto");// new atto($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];

                    $documentTableTypeId = 1;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
                break;

            case "12":
                $tipologiaPagamento = "AVVISO_MORA";
                $breveTxtTipo = "AV_MORA";

                $cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
                $cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
                $attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("AV_MORA", $attoTxtCompleto, $quintoCcComune,"Atto");

                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM atto WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "atto");//new atto($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];
                    $documentTableTypeId = 1;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
                break;
			case "02":
				$tipologiaPagamento = "INGIUNZIONE";
				$breveTxtTipo = "ING";
				
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("INGIUNZIONE", $attoTxtCompleto, $quintoCcComune,"Atto");
                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM atto WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "atto");// new atto($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];
                    $documentTableTypeId = 1;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
				break;
			case "03":
				$tipologiaPagamento = "SOLLECITO_INGIUNZIONE";
				$breveTxtTipo = "SOL_ING";

                $cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
                $cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
                $attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("SOLLECITOINGIUNZIONE", $attoTxtCompleto, $quintoCcComune,"Atto");
                //var_dump($a_atto);

                //if(empty($a_atto[0]))
                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM atto WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "atto");//new atto($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];
                    $documentTableTypeId = 1;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
				break;
			case "04":
				$tipologiaPagamento = "AVVISO_INTIMAZIONE";
				$breveTxtTipo = "AVINT";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new atto(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("AVVISOINTIMAZIONE", $attoTxtCompleto, $quintoCcComune,"Atto");
                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM atto WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "atto");//new atto($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];
                    $documentTableTypeId = 1;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
				break;
			case "05":
				$tipologiaPagamento = "SOLLECITO_AVVISO_INTIMAZIONE";
				$tipoTxtContoCorrente = "";
				break;
			case "06":
				$tipologiaPagamento = "PIGNORAMENTO_VEICOLO";
				$breveTxtTipo = "PIGNOVEIC";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new pignoramento(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("veicolo", $attoTxtCompleto, $quintoCcComune,"Pigno");
                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM pignoramento_generale WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "pignoramento_generale");//new pignoramento($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];
                    $documentTableTypeId = 2;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
				break;
			case "07":
				$tipologiaPagamento = "PIGNORAMENTO_DATORE_LAVORO";
				$breveTxtTipo = "PIGNOLAVORO";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new pignoramento(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("terzi", $attoTxtCompleto, $quintoCcComune,"Pigno");
                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM pignoramento_generale WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "pignoramento_generale");//new pignoramento($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];
                    $documentTableTypeId = 2;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
				break;
				
			case "08":
				$tipologiaPagamento = "PIGNORAMENTO_BANCA";
				$breveTxtTipo = "PIGNOBANCA";
				$cartellaPagAvvisi = "/archivio/Atti/" . $quintoCcComune . "/Pagamenti/";
				$cartellaCompletaPagAvvisi = $_SERVER['DOCUMENT_ROOT'] . $cartellaPagAvvisi;
				$attoTxtCompleto = $quintoAtto . "/" . "20" . $quintoAnnoGestione;

                //$myTxtAtto = new pignoramento(null, $quintoCcComune);
                $a_atto = $cls_elab->getIDFromCrono("terzi", $attoTxtCompleto, $quintoCcComune,"Pigno");
                if($a_atto[0]!=null) {
                    $query = "SELECT * FROM pignoramento_generale WHERE ID = " . $a_atto[0] . " AND CC = '" . $quintoCcComune . "'";
                    $myTxtAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "pignoramento_generale");//new pignoramento($a_atto[0], $quintoCcComune);

                    $tipoTxtContoCorrente = $a_atto[1];
                    $documentTableTypeId = 2;
                    $documentTypeId = $myTxtAtto->DocumentTypeId;
                }
				break;

			default:
				if ($problemaTxtImg != 12)  //  prioritario pagamento gi� presente nel CSV!
				{
					$problemaTxtImg = 2;  // non ho riconosciuto il tipo di servizio
				}
				break;
		}
		
		if ($a_atto[0]==null && $problemaTxtImg == 0)
		{
			$problemaTxtImg = 11;
		}
		
		if ($divisaTxtPagamento == "EUR") $divisaTradotta = "&euro;";
		else $divisaTradotta = $divisaTxtPagamento;
		
		$telematico = "";
		$classTelem = "";
		$dbTel = "N";
		//echo "<br>" . $codiceTxtProvinciaPosta . $codiceTxtUfficioPosta;
		if ($codiceTxtProvinciaPosta . $codiceTxtUfficioPosta == "055111")
		{
			$telematico = "Telematico";
			$classTelem = "sfondo_rosso";
			$dbTel = "Y";
		}


		//if ($cartellaPagAvvisi != "" && $arrayQuintoCampo[0] != "")  //  se ho identificato il comune
		if (1)
		{
            $query = "SELECT * FROM pagamenti_importati WHERE ID = 'NULL'";
			$myPagamentoTxtImportato = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamenti_importati");// new pagamenti_importati(NULL);

            $myPagamentoTxtImportato->DocumentTypeId = $documentTypeId;
            $myPagamentoTxtImportato->DocumentTableTypeId = $documentTableTypeId;
			$myPagamentoTxtImportato->Tipo_Pagamento = $tipologiaPagamento;
			$myPagamentoTxtImportato->Riferimento_Atto = $myProgrTxtNot;
			$myPagamentoTxtImportato->Comune_Riferimento = $quintoCcComune;
			$myPagamentoTxtImportato->Conto_Corrente = $numeroTxtContoCorrente;
			//$myPagamentoTxtImportato->Provincia_Posta = "";
			$myPagamentoTxtImportato->Data_Caricamento = null;
			$myPagamentoTxtImportato->Data_Pagamento = $dataTxtPagamento;
			//$myPagamentoTxtImportato->Codice_Provincia_Posta = "";
			//$myPagamentoTxtImportato->Codice_Ufficio_Posta = "";
			$myPagamentoTxtImportato->Codice_Txt_Composto = $tempTxtCodice;
			$myPagamentoTxtImportato->Telematico = $dbTel;
			$myPagamentoTxtImportato->Tipo_Bollettino = $tipoTxtBollettino;
			$myPagamentoTxtImportato->Importo_Pagato = $importoTxtPagato;
			$myPagamentoTxtImportato->Data_Preallibramento = null;
			$myPagamentoTxtImportato->Data_Postallibramento = $dataTxtAccredito;
			$myPagamentoTxtImportato->Quinto_Campo = $quintoTxtCampo;
			//$myPagamentoTxtImportato->Progressivo_Marcaggio = "";
			//$myPagamentoTxtImportato->Conto_Traente = "";
			//$myPagamentoTxtImportato->Divisa_Pagamento = "";
			$myPagamentoTxtImportato->Divisa_Txt_Pagamento = $divisaTxtPagamento;
			//$myPagamentoTxtImportato->Flag_Insanabili = "";
			//$myPagamentoTxtImportato->Progressivo_Selezione = "";
			//$myPagamentoTxtImportato->Report_Version = "";
			//$myPagamentoTxtImportato->SV = "";
			//$myPagamentoTxtImportato->Immagine_Fronte = "";
			//$myPagamentoTxtImportato->Immagine_Retro = "";
			$myPagamentoTxtImportato->Codice_Txt_Sconosciuto = $nonSoTxt1;
			$myPagamentoTxtImportato->Tipo_Txt_Bollettino = $tipoTxtConto;
			$myPagamentoTxtImportato->Sostitutivo_Txt = $sostitutivoTxt;
			$myPagamentoTxtImportato->Nome_File = $nomeFileTxt;
				
			$control_txt_importazione = $cls_elab->PagamImportatoGiaPresente($myPagamentoTxtImportato->Quinto_Campo, $myPagamentoTxtImportato->Importo_Pagato);
				
			$myTxtImmagineFronte = IMMAGINIWEB."/tasto_azzurro.jpg";
			$myTxtImmagineRetro = IMMAGINIWEB."/tasto_azzurro.jpg";
			$imgTxtFronte = "";
			$imgTxtRetro = "";
			
			$contoTxtTerzi = "";
			if ($tipoTxtContoCorrente == "")
			{
				if ($problemaTxtImg == 0) $problemaTxtImg = 13;
			}
			else
			{
                $query = "SELECT * FROM parametri_pagamento WHERE CC = '".$quintoCcComune."' AND Tipo_Riscossione = '".$tipoTxtContoCorrente."'";
                $myParametro = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_pagamento");

				//$myParametro = new parametri_pagamento($quintoCcComune, $tipoTxtContoCorrente);
				if ($myParametro->ID == "") $problemaTxtImg = 13;
				else $contoTxtTerzi = $cls_elab->data_conto_terzi($dataTxtPagamento,$myParametro->Data_Cambio_Conto, $myParametro->Conto_Terzi);
			}
			
			$daTxtBonificare = false;
							
			if ($problemaTxtImg != 0)
			{
				$myTxtImmagineOKNO = IMMAGINIWEB."/spuntaNO.jpg";
						
				switch ($problemaTxtImg)
				{
					case 1:
						if ($telematico != "")
						{
							$esitoTxtOKNO = "Problema: Comune Riferimento assente DA BONIFICARE";
							$myTxtImmagineOKNO = IMMAGINIWEB."/FrecciaDx.png";
							$problemaTxtImg = 0;
							$daTxtBonificare = true;
						}
						else 
						{
							$esitoTxtOKNO = "Problema: Comune Riferimento assente";
							$tipologiaPagamento = "";  //  � probabile che sia stato letto casualmente una tipologia, ma il quinto campo non � leggibile
							//$myPagamentoTxtImportato->Tipo_Pagamento = "";
						}
						break;
					case 2:
						if ($telematico != "")
						{
							$esitoTxtOKNO = "Problema: Tipo Servizio Sconosciuto DA BONIFICARE";
							$myTxtImmagineOKNO = IMMAGINIWEB."/FrecciaDx.png";
							$problemaTxtImg = 0;
							$daTxtBonificare = true;
						}
						else 
						{
							$myTxtImmagineOKNO = IMMAGINIWEB."/lock.png";
							$esitoTxtOKNO = "Problema: pagamento escluso dall'importazione";
						}
						break;
					case 3:
						$esitoTxtOKNO = "Problema: Immagini assenti";
						break;
					case 11:
						$esitoTxtOKNO = "Problema: Atto non trovato";
						break;
					case 12:
						$esitoTxtOKNO = "Problema: Pagamento gia' presente nel CSV";
						break;
					case 13:
						$esitoTxtOKNO = "Problema: Tipo Conto Corrente non definito";
						break;
					default:
						$esitoTxtOKNO = "Problema: sconosciuto";
						break;
				}
			}
			else
			{
				$myTxtImmagineOKNO = IMMAGINIWEB."/spunta.jpg";
				$esitoTxtOKNO = "OK";
				//$myTxtImmagineOKNO = "/gitco2/immagini/lock.png";
				//$esitoTxtOKNO = "Problema: pagamento escluso dall'importazione";
				//$problemaTxtImg = 99;
			}
						
						
				
			
			
			if ($importatutto == "SI" && $problemaTxtImg == 0)
			{
				$scrittaBarra_2 = "Importazione TXT completata";
				$erroreTrovato = false;
				if ($daTxtBonificare == false)
				{
					if($giaPresente=="PRESENTE")
						continue;
					
					//$myNewPagamento = new pagamento(null, $c);
					
					switch ($quintoTipoServizio)  //  tipo servizio
					{
						case "00":
							$testoDbTipoPag = "Verbale";  //  ??
							break;
						case "01":
							$testoDbTipoPag = "Sollecito";  //  ??
							break;
                        case "11":
                            $testoDbTipoPag = "Sollecito pre ingiunzione";  //  ??
                            break;
                        case "12":
                            $testoDbTipoPag = "Avviso di messa in mora";  //  ??
                            break;
						case "02":
							$testoDbTipoPag = "Ingiunzione";  //  ??
							break;
						case "03":
							$testoDbTipoPag = "Sollecito di pagamento";  //  ??
							break;
						case "04":
							$testoDbTipoPag = "Avviso di intimazione ad adempiere";  //  ??
							break;
						case "05":
							$testoDbTipoPag = "Sollecito avviso intimazione";  //  ??
							break;
						case "06":
							$testoDbTipoPag = "Pignoramento beni mobili registrati";  //  ??
							break;
						case "07":
							$testoDbTipoPag = "Pignoramento presso datore di lavoro";  //  ??
							break;
						case "08":
							$testoDbTipoPag = "Pignoramento presso banca";  //  ??
							break;

						default:
							$testoDbTipoPag = "";
							break;
					}
                    $query = "SELECT * FROM pagamento WHERE ID = 'NULL' AND CC = '".$c."'";
                    $myNewPagamento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamento");

					$myNewPagamento->Comune_ID = $cls_elab->ProssimoComuneId($quintoCcComune);
					$myNewPagamento->CC = $quintoCcComune;
					$myNewPagamento->Partita_ID = $myTxtAtto->Partita_ID;
					$myNewPagamento->Atto_ID = $myTxtAtto->ID;
					$myNewPagamento->Riferimento_Atto = 1;  // non usato
					$myNewPagamento->Tipo_Atto = $testoDbTipoPag;
					$myNewPagamento->Pagante = "";
					$myNewPagamento->Conto_Terzi = $contoTxtTerzi;  //  parametro comune
					$myNewPagamento->Data_Pagamento = $dataTxtPagamento;
					$myNewPagamento->Data_Registrazione = date("Y-m-d");
					$myNewPagamento->Modalita = "C/C";  //  ??  bolletta o C/C
					$myNewPagamento->Importo = $importoTxtPagato;
                    $myNewPagamento->DocumentTypeId = $documentTypeId;
                    $myNewPagamento->DocumentTableTypeId = $documentTableTypeId;
					
					if($myTxtAtto->Rate_Previste==0 || $myTxtAtto->Rate_Previste==null)
						$myNewPagamento->Dovuto = $myTxtAtto->Totale_Dovuto;
					else {
                        $arrayImportoRate = explode("*",$myTxtAtto->Importi_Rate);
                        $myNewPagamento->Dovuto = str_replace(",",".",$arrayImportoRate[$quintoNumeroRata - 1]);
                    }
					
					$myNewPagamento->Quietanza = $quietTxtAnza;
					$myNewPagamento->Bollettario = "";
					$myNewPagamento->Rata = $quintoNumeroRata;
					$myNewPagamento->Totale_Rate = $myTxtAtto->Rate_Previste;  //  non usato
					$myNewPagamento->Note = "";
					$myNewPagamento->Bollettino = str_replace(".TIF", ".JPG", $imgTxtFronte);
					$myNewPagamento->Telematico = "N";
					$myNewPagamento->Tipo_Pagamento = "AUTOMATICO";
					$myNewPagamento->Data_Travaso_A_Gitco = 'NULL';
					
					//da rimettere
					$cls_elab->InsertUpdatePagamento($myNewPagamento);
					
					$myPagamentoTxtImportato->Esito = "IMPORTATO";
				}
				else $myPagamentoTxtImportato->Esito = "DABONIFICARE";
				
				$rispImport = $cls_elab->InsertUpdatePagamImportato($myPagamentoTxtImportato);
				
			}  //  fine importazione definitiva TXT
				
					?>
							
									<tr class="<?=$stileriga?>">
										<td>
											<font class="font11"><?=($k+1)?></font>
										</td>
										<td>
											<label class="font11" title="<?=$tipologiaPagamento?>"><?=$breveTxtTipo?></label>
										</td>
										<td>
											<label class="font11" title="ID: <?=$myProgrTxtNot?>"><?=$attoTxtCompleto?></label>
										</td>
										<td>
											<?=$quintoCcComune?>
										</td>
										<td>
											<font class="font11"><i><?=$numeroTxtContoCorrente?></i></font>
										</td>
										<td>
											<img src="<?=$myTxtImmagineFronte?>" class="pwidth20 pheigth20" title="<?=$imgTxtFronte?>">
										</td>
										<td>
											<img src="<?=$myTxtImmagineRetro?>" class="pwidth20 pheigth20" title="<?=$imgTxtRetro?>">
										</td>
										<td>
											<?= $cls_date->Get_DateNewFormat($dataTxtPagamento,"DB"); ?>
										</td>
										<td class="text_right <?=$classTelem?>">
											<div title="<?=$telematico?>">
												<?=number_format($importoTxtPagato, 2, ",", ".")?> &euro;
											</div>
										</td>
										<td>
											<font class="font11"><?=$quintoNumeroRata?></font>
										</td>
										<td>
											<font class="font11"><?=$quintoAnnoGestione?></font>
										</td>
										<td>
											<div title="Quinto Campo: <?=$quintoTxtCampo?>">
												<font class="font11 corsivo"><?=$tipoTxtBollettino?></font>
											</div>
										</td>
										<td>
											<img src="<?=$myTxtImmagineOKNO?>" class="pwidth20 pheigth20" title="<?=$esitoTxtOKNO?>">
										</td>
									</tr>
								
								<?php
								
		}  //  fine if 1
		
	}  //  fine for TXT
	
	}//else se presente TXT
	
	echo "<script>fine('$scrittaBarra_2', 2);</script>";
	
}  //  fine else analizzatutto
	
?>
			</table>
        </div>
    </div>


<?php

if ($importatutto == "SI")
{
	$backupCsv = date("Y-m-d") . "_" . $myFileCsv;
	$backupCsv = $cartellaBackUp . $backupCsv;
	
	$backupTxt = date("Y-m-d") . "_" . $myFileTxt;
	$backupTxt = $cartellaBackUp . $backupTxt;
	
	$stringaRdDir = $cartellaTemporanea;
	$stringaRdDir = str_replace("Program Files (x86)", "Progra~2", $stringaRdDir);
	$stringaRdDir = str_replace("/", "\\", $stringaRdDir); 
	$stringaRdDir = "rd " . $stringaRdDir . " /S /Q ";
	
	/*echo "<br>rename ($cartellaTemporanea $myFileCsv, $backupCsv)";
	echo "<br>rename ($cartellaTemporanea $myFileTxt, $backupTxt)";
	echo "<br>exec ($stringaRdDir)";*/
	
	//da rimettere
	if($myFileCsv!="")
		rename ($cartellaTemporanea . $myFileCsv, $backupCsv);
	if($myFileTxt!="")
		rename ($cartellaTemporanea . $myFileTxt, $backupTxt);
	exec ($stringaRdDir);
	
	
	for ($p = 0; $p < count($arrayFileZip); $p++)
	{
		//da rimettere
		unlink ($PathCompletoImportPagamenti . $arrayFileZip[$p]);
		//echo "<br>unlink (" . $PathCompletoImportPagamenti . $arrayFileZip[$p] . ")";
	}
	
	$cls_help->alert ("Importazione Terminata");
	
	echo "<script>annulla();</script>";
}

?>


<?php 

function IsNomeInArray ($img, $array)
{
	$aCapo = Chr(13).Chr(10);
	$soloLfCapo = Chr(10);
	$soloCrCapo = Chr(13);
	if ($img == "") return true;
	if ($img == " ") return true;
	if ($img == $soloLfCapo) return true;
	if ($img == $soloCrCapo) return true;
	if ($img == $aCapo) return true;
	if ($img == " " . $aCapo) return true;
	
	//echo "<br>";
	for ($i = 0; $i < count($array); $i++)
	{
		/*if ($img == "4279038136030056")
			echo "<br>----" . $img . " == " . $array[$i] . "----";*/
		
		if ($img == $array[$i])
		{
			
			/*if ($img == "4279038136030056")
				echo "  SI";*/
			return true;
		}
	}
	//echo "<br>-" . $img . "-";
	return false;
}


?>

<?php include(INC."/footer.php"); ?>
