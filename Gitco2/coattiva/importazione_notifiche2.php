<?php
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	include CLASSI . "/parametri.php";
	include CLASSI . "/targhe_estere_utenti.php";
	
	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	
set_time_limit(200);

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$provenienza = get_var('provenienza');  //  TARGHEESTERE o COATTIVA
$analizzatutto = get_var('analizzatutto');
$importatutto = get_var('importatutto');


$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$scrittaBarra = "Elaborazione completata";

$cartellaCsv = crea_dir($PathCompletoImportNotifiche);
$cartellaNotifiche = crea_dir($PathCompletoImmaginiNotifiche);
$cartellaBackUp = crea_dir($PathCompletoImmaginiNotifiche . "BackUp/");

$allaFineSpostaCsv = null;
	
if ($analizzatutto == "SI")
{
	$arrayFileCsv = array();
	$arrayFileImmagini = array();
	
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
				if ($estensione == "TXT" || $estensione == "CSV")
				{
					$arrayFileCsv [] = $file;
				}
			}
			else 
			{
				$cartellaImmagini = $cartellaCsv . $file . "/";
				$handle2 = opendir($cartellaImmagini);
				while (($file = readdir($handle2)) != false)
				{
					if ($file != "." && $file != ".." && $file != "Thumbs.db")  //  queste sono cartelle
					{
						if (!is_dir($cartellaImmagini . $file))
						{
							$arrayFileImmagini [] = $cartellaImmagini . $file;
						}
					}
				}
				closedir($handle2);
			}
		}
	}
	closedir($handle);
	
	if (count($arrayFileCsv) == 0)
	{
		alert ("Nessun file TXT o CSV � stato trovato");
		echo "<script>history.back();</script>";
		return;
	}
	
	$contenuto = array();
	$contatore = 0;
	
	for ($p = 0; $p < count($arrayFileCsv); $p++)
	{
		$fp = fopen ($cartellaCsv . $arrayFileCsv[$p], "r+");
		
		while ($fp && !feof($fp))
		{
			$temp = str_replace('"', '', fgets($fp));
			
			$esplodoPuntoVirg = explode (";", $temp);
			if (strlen($esplodoPuntoVirg[0]) == 4)  //  le prime 4 cifre devono essere il codic catastale!
			{
				for ($k = 0; $k < count($esplodoPuntoVirg); $k++)
				{
					$contenuto[$contatore][$k] = $esplodoPuntoVirg[$k];
				}
				$contenuto[$contatore][$k] = $arrayFileCsv[$p];  //  metto in coda anche il nome del file CSV
				$contatore++;
			}
		}
		
		fclose($fp);
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Importazione Notifiche</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>
	
	<link REL=StyleSheet HREF="/gitco2/css/image_magnifier.css" TYPE="text/css" MEDIA=screen>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/image_magnifier.js"></script>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{  
	
}

//F4
function cancella_form() 
{     
	
}

//F5
function annulla()
{
	location.href="importazione_notifiche.php?provenienza=<?php echo $provenienza; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
function nuovo_F6()
{
	
}

//F7-F8
function cambia_pag(value)
{
	
}

//PAG GIU
function pag_prec()
{
	
}

//PAG SU
function pag_suc()
{
	var strLink = "importazione_notifiche.php?";
	strLink += "c=" + "<?=$c?>";
	strLink += "&a=" + "<?=$a?>";
	strLink += "&provenienza=" + "<?=$provenienza?>";
	strLink += "&analizzatutto=" + "SI";
	if ("<?=$analizzatutto?>" == "SI") strLink += "&importatutto=" + "SI";
	location.href = strLink;
}

//F9
function ricerca_F9()
{
	
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
	
	//sleep(1000);
	
	//alert ("Importazione completata");

	//mostra_file();
	//$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Elenco' onclick='mostra_file();'>");
}
</script>

</head>

<body class="sfondo_new_gitco" >  

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
	<td valign=top>

<?php 

if ($provenienza == "TARGHEESTERE")
{
	include TARGHEESTERE . '/menu/menu_targheestere.php';
}
else if ($provenienza == "COATTIVA")
{
	include MENU . '/menu_generale.php';
}
else 
{
	alert ("Errore nella variabile provenienza");
	return;
}

?> 
                
		<table class="table_interna text_center" border=0 cellspacing=4>
			<tr>
				<td align=center width=7%>
					<a onMouseover="title='Modifica'" href="#" onClick="">
					<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
					</a>
				</td>
				<td align=center width=7% >
					<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
				</td>
				<td align=center width=7% >
					<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
				</td>
				<td align=center width=7% >
					<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
					<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
					</a>
				</td>
				<td align=center width=7% >
					<a onMouseover="title='Nuovo Record'" href="#" onClick="nuovo_F6();" style="text-decoration: none;">
					<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
					</a>
				</td>
				
				<td align=center width=7% >
					<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
					<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
					</a>
				</td>
				<td align=center width=7% >
					<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
					<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
					</a>
				</td>
				<td width=7% align="center">
		          	<a href="#" onMouseover="title='Record precedente F7'" onclick="cambia_pag('prev')">
		          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
		          	</a>
		    	</td>
		        <td width=7% align="center">
		            <a href="#" onMouseover="title='Record successivo F8'" onclick="cambia_pag('suc')">
		            <img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo">
		            </a>
		        </td>
		        <td width=11%></td>
		        <td width=7% align="center">
		          	<a href="#" onMouseover="title='Stampa'" onclick="">
		          	<img src="/gitco2/immagini/printF10grey.png" width=50 height=50 border="0" ></a>
		    	</td>
		        <td width=3%></td>
		    	<td align=center width=7% >
		    			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
					<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
					</a>
				</td>
				<td width=2%></td>
				<td width=7%>
					<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
					<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
					</a>
				</td>
			</tr>
		</table>
		
		<table class="table_interna text_center" border="0">
		<tr class="pheight40">
			<td valign=top>
				<br>
				<font class="titolo font18 text_center">Importazione Notifiche</font>
			</td>
		</tr>
		</table>
		
	</td>
</tr>


<?php if ($analizzatutto != "SI") { ?>

	<tr>
		<td>
			<div class="color_red">
				1) Mettere il file TXT o CSV nella cartella
				<br>
				<i>\\serverback\EasyPHP-DevServer-14.1VC11\data\localweb<?=str_replace("/", "\\", $PathImportazioniNotifiche)?></i>
				<br>
				<br>
				2) Mettere le immagini in una cartella dentro a  <i><?=$PathImportazioniNotifiche?></i>
				<br>
				Esempio:
				<br>
				<?=$PathImportazioniNotifiche?>IMG/781822104871_F.jpg,
				<br>
				<?=$PathImportazioniNotifiche?>IMG/781822104871_R.jpg,
				<br>
				<?=$PathImportazioniNotifiche?>IMG/781822104906_F.jpg, ...
				<br>
				<br>
				3) Premere il tasto del Menu "Pagina Successiva"
			</div>
		</td>
	</tr>
	
<?php } else { ?>
	<tr class="pheight40">
		<td valign=top>
			<div class="table_interna text_center" id="progressbar" style="height:55px;">
				<div class="text_center" id="barlabel">
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<table class="table_interna text_center" border="0">
			<tr>
				<td>
					
				</td>
				<td>
					
				</td>
				<td>
					<font class="color_red">Comune</font>
				</td>
				<td>
					<font class="color_red">Verbale</font>
				</td>
				<td>
					<font class="font11 color_red ">Fronte</font>
				</td>
				<td>
					<font class="font11 color_red">Retro</font>
				</td>
				<td>
					<font class="font11 color_red">Data</font>
				</td>
				<td>
					<font class="font11 color_red">Tipo</font>
				</td>
				<td>
					<font class="font11 color_red">Stato</font>
				</td>
				<td>
					<font class="color_red">Esito</font>
				</td>
			</tr>
	<?php 
	
	echo "<script>inizio();</script>";
	
	flush();
	ob_flush();
	flush();
	ob_flush();
	
	sleep (1);
	
	$stileriga = "sfondo_grigio";
	
	for ($k = 0; $k < count($contenuto); $k++)
	{
		echo "<script>update(".ceil($k*100/count($contenuto)).");</script>";
		
		set_time_limit(100);
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
		else $stileriga = "sfondo_grigio";
		
		//$contenuto[$k][0]: codice catastale del comune
		//$contenuto[$k][1]: numero del verbale fatto in questo modo: MUMERO CRONOLOGICO/ANNO
		//$contenuto[$k][2]: nome del trasgressore e codice contribuente tra parentesi (il codice � il Con_Progr della tabella contribuente)
		//$contenuto[$k][3]: progressivo della notifica (per i flussi fino a fine ottobre questo campo � vuoto e bisogna recuperarlo via codice)
		//$contenuto[$k][4]: numero del flusso di riferimento costituito dal codice catastale del comune e dal numero progressivo del flusso separati da un underscore
		//$contenuto[$k][5]: data della notifica da trasformare nel formato per il db cio� AAAA-MM-GG
		//$contenuto[$k][6]: numero della notifica da inserire nel campo Not_Estremi_Notifica
		//$contenuto[$k][7]: tipo della notifica; da questo campo noi ricaviamo se la notifica � semplice o se � stato emesso un cad la codifica � la seguente:
		//			  01 AR (notifica semplice) -> lascio il campo Not_Numero_Notifica come �
		//			  02 AR con CAD emesso (notifica semplice con emesso il CAD) -> Inserisco nel campo Not_Numero_Notifica il numero 2
		//			  03 CAD (notifica doppia cio� � il CAD) -> Inserisco nel campo Not_Numero_Notifica il numero 2
		//			  04 il piego � stato restituito dopo 6 mesi -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 5) inoltre inserisco che � stato emesso un CAD
		//			  05 incompleto/incoerente -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 9)
		//			  06 AR con CAN emesso (notifica semplice con emesso il CAN) -> Inserisco nel campo Not_Numero_Notifica il numero 3  //08/06/2010
		//$contenuto[$k][8]: stato della notifica; da questo campo dobbiamo ricavare lo stato della notifica e la codicfica � la seguente:
		//			  01 Piego non ritirato nel termine di 10 giorni -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 4) inoltre inserisco che � stato emesso un CAD
		//			  02 Rifiutato -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 6)
		//			  03 Indirizzo inesatto -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 7)
		//			  04 Indirizzo insufficiente -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 9)
		//			  05 Indirizzo inesistente -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 10)
		//			  06 Civico inesistente -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 11)
		//			  07 Irreperibile -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 12)
		//			  08 Sconosciuto -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 13)
		//			  09 Deceduto -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 14)
		//			  10 Trasferito -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 15)
		//			  11 Non notificato -> Inserisco nel campo dello stato della notifica il progressivo relativo a tale stato (CODICE: 3)
		//$contenuto[$k][9]: campo delle note da inserire nel campo Not_Note
		//$contenuto[$k][10]: campo relativo a immagine della cartolina AR
		//$contenuto[$k][11]: campo relativo a immagine della cartolina AR retro
		//$contenuto[$k][12]: campo relativo al Log_Modificato, ci servira per sapere se i dati che ci vengono restituiti sono stati eventualmente modificati, quindi la data di Log_Modificatosera salvata nel posto della data di registrazione della notifica al posto della data in cui si fa effettivamente l'importazione ((Not_Data_Registrazione)
		//$contenuto[$k][13]: campo relativo agli Estremmi della spedizione
		//$contenuto[$k][14]: campo relativo alla data della spedizione
		//$contenuto[$k][15]: campo relativo alla Scatola	//NEW MODIFY 06-04-2011
		//$contenuto[$k][16]: campo relativo al Lotto		//NEW MODIFY 06-04-2011
		//$contenuto[$k][17]: campo relativo alla Posizione	//NEW MODIFY 06-04-2011
		//$contenuto[$k][18]: nome del file importato (aggiunto dal programma, non c'� nel file)  // 20-12-2014
			
		$avanti = 0;
		
		$ccComune = trim($contenuto[$k][$avanti++]);
		$numeroCronologico = trim($contenuto[$k][$avanti++]);
		$trasgressoreCompleto = trim($contenuto[$k][$avanti++]);
		$progressivoNotifica = $contenuto[$k][$avanti++];
		$numeroFlusso = $contenuto[$k][$avanti++];
		$dataDellaNotifica = $contenuto[$k][$avanti++];
		$numeroDellaNotifica = $contenuto[$k][$avanti++];
		$tipoDellaNotifica = $contenuto[$k][$avanti++];
		$statoDellaNotifica = $contenuto[$k][$avanti++];
		$noteNotifica = $contenuto[$k][$avanti++];
		$imgAR = $contenuto[$k][$avanti++];
		$imgRetroAR = $contenuto[$k][$avanti++];
		$logModif = $contenuto[$k][$avanti++];
		$estrSpedizione = $contenuto[$k][$avanti++];
		$dataSpedizione = $contenuto[$k][$avanti++];
		$scatolaSpedizione = $contenuto[$k][$avanti++];
		$lottoSpedizione = $contenuto[$k][$avanti++];
		$posizioneSpedizione = $contenuto[$k][$avanti++];
		$tipologiaStampa = strtoupper($contenuto[$k][$avanti++]);
		$tipologiaAtto = trim(strtoupper($contenuto[$k][$avanti++]));
		$nomeFileCsv = trim(strtoupper($contenuto[$k][$avanti++]));
	
		$codiceTipoNot = substr($tipoDellaNotifica, 0, 2);
		$codiceStatoNot = substr($statoDellaNotifica, 0, 2);
		
		$esitoTipo = "-";
		$esitoStato = "-";
		
		$myProgrNot = "";
		
		if ($tipologiaAtto == "VERBALIESTERI" || $tipologiaAtto == "AVVISOINTIMAZIONE" || $tipologiaAtto == "INGIUNZIONE")
		{
			switch ($tipologiaAtto)
			{
				case "VERBALIESTERI":
					$breveTipo = "V_ES";
					break;
				case "AVVISOINTIMAZIONE":
					$breveTipo = "A_IN";
					break;
				case "INGIUNZIONE":
					$breveTipo = "ING";
					break;
				default:
					$breveTipo = "?";
					alert ("tipo errato");
					break;
			}
			
			
			$problemaImg = 0;
			$accettoSenzaImg = false;
			switch ($codiceStatoNot)  //  per questi � AMMESSA la MANCANZA di IMMAGINI
			{
				case "03": // indirizzo inesatto
				case "04": // indirizzo insufficiente
				case "05": // indirizzo inesistente
				case "07": // irreperibile
				case "08": // sconosciuto
				case "09": // deceduto
				case "10": // trasferito
					$accettoSenzaImg = true;
					break;
			}
						
			$percorsoImg = crea_dir($cartellaNotifiche . $ccComune . "/");
			$esplodoXX = explode (".", $imgAR);
			$radiceImg = $esplodoXX[0];
			$nuovoNomeFileFronte = $percorsoImg . $radiceImg . ".jpg";
			
			$esplodoXX = explode (".", $imgRetroAR);
			$radiceImg = $esplodoXX[0];
			$nuovoNomeFileRetro = $percorsoImg . $radiceImg . ".jpg";
			
// 			$possoComprimere = true;
// 			if (file_exists($nuovoNomeFileFronte))
// 			{
// 				$possoComprimere = false;
// 				$problemaImg = 8;
// 			}
// 			else if (file_exists($nuovoNomeFileRetro))
// 			{
// 				$possoComprimere = false;
// 				$problemaImg = 9;
// 			}
			
			if ($dataDellaNotifica == "")
			{
				if ($accettoSenzaImg == false) $problemaImg = 4;
			}
			
			if ($ccComune == "")
			{
				$problemaImg = 1;
			}
			else if ($numeroCronologico == "")
			{
				$problemaImg = 2;
			}
			else 
			{
				$esplodoBarre = explode("/", $numeroCronologico);
				$progressivoVerbale = $esplodoBarre[0];
				$annoVerbale = $esplodoBarre[1];
				switch ($tipologiaAtto)
				{
					case "VERBALIESTERI":
						$myNotifica = new targhe_estere_notifiche(NULL);
						$myProgrNot = $myNotifica->NotificaCoincTrasgDaVerbale($progressivoVerbale, $annoVerbale, $ccComune);
						alert ("verbale estero" . $myProgrNot);
						return;
						if ($myProgrNot == NULL)
						{
							$problemaImg = 5;
						}
						$myEsito = new targhe_estere_tipi_ricezione(null);
						$esitoTipo = $myEsito->CercaTipiImportati($codiceTipoNot);
						$esitoStato = $myEsito->CercaStatiImportati($codiceStatoNot);
						if ($esitoTipo == NULL)
						{
							$problemaImg = 6;
						}
						else if ($esitoStato == NULL)
						{
							$problemaImg = 7;
						}
						
					break;
						
					case "INGIUNZIONE":
							
						$atto = new atto(null, $ccComune);
						$myProgrNot = $atto->cercaIDdaCrono($tipologiaAtto, $numeroCronologico, $ccComune);
						
						unset($atto);	
						
						if($myProgrNot==null)
							$problemaImg = 10;
						
						$para_notifica = new parametri_notifica(null);
						$para_notifica->array_notifica();
						$statiMercurio = $para_notifica->cerca_stati_mercurio( $codiceTipoNot, $codiceStatoNot);						
						
						if ($statiMercurio['Tipo'] == 'ERRORE')
						{
							$problemaImg = 6;
						}
						else if ($statiMercurio['Stato']== 'ERRORE')
						{
							$problemaImg = 7;
						}
							
					break;
					
					case "AVVISOINTIMAZIONE":

						$atto = new atto(null, $ccComune);
						$myProgrNot = $atto->cercaIDdaCrono($tipologiaAtto, $numeroCronologico, $ccComune);

						unset($atto);	
						
						if($myProgrNot==null)
							$problemaImg = 11;
						
						$para_notifica = new parametri_notifica(null);
						$para_notifica->array_notifica();
						$statiMercurio = $para_notifica->cerca_stati_mercurio( $codiceTipoNot, $codiceStatoNot);						
						
						if ($statiMercurio['Tipo'] == 'ERRORE')
						{
							$problemaImg = 6;
						}
						else if ($statiMercurio['Stato']== 'ERRORE')
						{
							$problemaImg = 7;
						}
						
					break;				
					
				}
			}
			
			$esplodoSpazio = explode (" ", $logModif);
			
			$myImportazione = new notifiche_importate(null);
			$myImportazione->Tipo_Spedizione = addslashes($tipologiaStampa);
			$myImportazione->Tipo_Atto = addslashes($tipologiaAtto);
			$myImportazione->Riferimento = $myProgrNot;
			$myImportazione->CC_Comune = $ccComune;
			$myImportazione->Num_Viol = $numeroCronologico;
			$myImportazione->Rec_Nome = addslashes($trasgressoreCompleto);
			$myImportazione->Progressivo_Notifica = addslashes($progressivoNotifica);
			$myImportazione->Ms_Lotto = $numeroFlusso;
			$myImportazione->Data_Notifica = to_mysql_date($dataDellaNotifica);
			$myImportazione->Ms_Ric_Num = $numeroDellaNotifica;
			$myImportazione->Tipo_Notifica = addslashes($tipoDellaNotifica);
			$myImportazione->Stato_Notifica = addslashes($statoDellaNotifica);
			$myImportazione->Note = addslashes($noteNotifica);
			$myImportazione->Immagine_Fronte = $imgAR;
			$myImportazione->Immagine_Retro = $imgRetroAR;
			$myImportazione->Log_Modificato_Data = to_mysql_date($esplodoSpazio[0]);
			$myImportazione->Log_Modificato_Ora = $esplodoSpazio[1];
			$myImportazione->Ms_Rac_Num = $estrSpedizione;
			$myImportazione->Data_Spedizione = to_mysql_date($dataSpedizione);
			$myImportazione->Scatola = $scatolaSpedizione;
			$myImportazione->Lotto = $lottoSpedizione;
			$myImportazione->Posizione = $posizioneSpedizione;
			$myImportazione->Nome_File = $nomeFileCsv;
			//$rispImport = $myImportazione->InsertUpdateNotifImportata();
			//return;
			
			$control_importazione = $myImportazione->NotifImportataGiaPresente();
			if ($control_importazione != "")
				$accettoSenzaImg = true;
			if (file_exists($cartellaImmagini . $imgAR))
			{
				$myImmagineFronte = "/gitco2/immagini/spunta.jpg";
			}
			else
			{
				$myImmagineFronte = "/gitco2/immagini/spuntaNO.jpg";
				if ($accettoSenzaImg == false && $problemaImg==0)
				{
					if($control_importazione=="")
						$problemaImg = 3;
					//alert ($problemaImg . " e " . $statoDellaNotifica);
					//return;
				}
			}
				
			if (file_exists($cartellaImmagini . $imgRetroAR))
			{
				$myImmagineRetro = "/gitco2/immagini/spunta.jpg";
			}
			else
			{
				$myImmagineRetro = "/gitco2/immagini/spuntaNO.jpg";
				if ($accettoSenzaImg == false && $problemaImg==0) 
					if($myImportazione->NotifImportataGiaPresente()=="")
						$problemaImg = 3;
			}
			
			if ($problemaImg != 0)
			{
				$myImmagineOKNO = "/gitco2/immagini/spuntaNO.jpg";
			
				switch ($problemaImg)
				{
					case 1:
						$esitoOKNO = "Problema: Comune Riferimento assente";
						break;
					case 2:
						$esitoOKNO = "Problema: Numero Verbale assente";
						break;
					case 3:
						$esitoOKNO = "Problema: Immagini assenti";
						break;
					case 4:
						$esitoOKNO = "Problema: Data Notifica assente";
						break;
					case 5:
						$esitoOKNO = "Problema: Verbale Targa Estera non trovato";
						break;
					case 6:
						$esitoOKNO = "Problema: Tipo Esito Notifica sconosciuto";
						break;
					case 7:
						$esitoOKNO = "Problema: Stato Esito Notifica sconosciuto";
						break;
					case 8:
						$esitoOKNO = "Problema: Immagine Fronte gi� importata";
						break;
					case 9:
						$esitoOKNO = "Problema: Immagine Retro gi� importata";
						break;
					case 10:
						$esitoOKNO = "Problema: Ingiunzione non trovata";
						break;
					case 11:
						$esitoOKNO = "Problema: Avviso di intimazione ad adempiere non trovato";
						break;
					default:
						$esitoOKNO = "Problema: sconosciuto";
						break;
				}
			}
			else
			{
				$myImmagineOKNO = "/gitco2/immagini/spunta.jpg";
				$esitoOKNO = "OK";
			}
			
			if ($importatutto == "SI" && $problemaImg == 0)
			{
				$scrittaBarra = "Importazione completata";
				$erroreTrovato = false;
				
				switch ($tipologiaAtto)
				{
					case "VERBALIESTERI":
						
						$myImportazione->Riferimento = $myProgrNot;
						$rispImport = $myImportazione->InsertUpdateNotifImportata();
						
						if ($rispImport == "UPDATE_OK" || $rispImport == "INSERT_OK")
						{
							$myNotifica = new targhe_estere_notifiche($myProgrNot);
							$myNotifica->Data_Notifica = to_mysql_date($dataDellaNotifica);
							$myNotifica->Esito_Notifica = $esitoTipo;
							$myNotifica->Esito_Stato_Notifica = $esitoStato;
							$myNotifica->InsertUpdateNotifica();
						}
						else $erroreTrovato = true;
						
						$control_fronte = 0;
						$control_retro = 0;
						if (file_exists($cartellaImmagini . $imgAR))
						{
							//echo "<br>" . $cartellaImmagini . $imgAR;
							$im = new imagick( $cartellaImmagini . $imgAR );

						    $im->setImageCompression(Imagick::COMPRESSION_JPEG);
						    $im->setImageCompressionQuality(1);
						    $im->writeImage( $nuovoNomeFileFronte );
						    
						    $control_fronte = 1;						   
						}
						else if ($accettoSenzaImg == false) $erroreTrovato = true;
						
						if (file_exists($cartellaImmagini . $imgRetroAR))
						{
						    //echo "<br>       --  " . $cartellaImmagini . $imgRetroAR;
							$im = new imagick( $cartellaImmagini . $imgRetroAR );
						    
						    $im->setImageCompression(Imagick::COMPRESSION_JPEG);
						    $im->setImageCompressionQuality(1);
						    $im->writeImage( $nuovoNomeFileRetro );
						    
						    $control_retro = 1;						    
						}
						 else if ($accettoSenzaImg == false) $erroreTrovato = true;
						//return;
						
						if ($erroreTrovato == false)
					    {		
					    	if($control_fronte == 1)
					    		unlink($cartellaImmagini . $imgAR);
					    	
					    	if($control_retro == 1)
					    		unlink($cartellaImmagini . $imgRetroAR);
					    	
					        if ($allaFineSpostaCsv == "") 
					        	$allaFineSpostaCsv = "SI";
					    }
					    else $allaFineSpostaCsv = "NO";
						
						break;
						
					case "AVVISOINTIMAZIONE":
					case "INGIUNZIONE":
						
						$myImportazione->Riferimento = $myProgrNot;
						$rispImport = $myImportazione->InsertUpdateNotifImportata();
						
						if ($rispImport == "UPDATE_OK" || $rispImport == "INSERT_OK")
						{							
							$myNotifica = new atto($myProgrNot,$ccComune);
														
							$myNotifica->Data_Notifica = to_mysql_date($dataDellaNotifica);
							$myNotifica->inserisciNotifica($statiMercurio);				
							$myNotifica->Update($myProgrNot);
						}
						else $erroreTrovato = true;
						
						$control_fronte = 0;
						$control_retro = 0;
						if (file_exists($cartellaImmagini . $imgAR))
						{
							//echo "<br>" . $cartellaImmagini . $imgAR;
							$im = new imagick( $cartellaImmagini . $imgAR );

						    $im->setImageCompression(Imagick::COMPRESSION_JPEG);
						    $im->setImageCompressionQuality(1);
						    $im->writeImage( $nuovoNomeFileFronte );
						    
						    $control_fronte = 1;						   
						}
						else if ($accettoSenzaImg == false) $erroreTrovato = true;
						
						if (file_exists($cartellaImmagini . $imgRetroAR))
						{
						    //echo "<br>       --  " . $cartellaImmagini . $imgRetroAR;
							$im = new imagick( $cartellaImmagini . $imgRetroAR );
						    
						    $im->setImageCompression(Imagick::COMPRESSION_JPEG);
						    $im->setImageCompressionQuality(1);
						    $im->writeImage( $nuovoNomeFileRetro );
						    
						    $control_retro = 1;						    
						}
						 else if ($accettoSenzaImg == false) $erroreTrovato = true;
						//return;
						
						if ($erroreTrovato == false)
					    {		
					    	if($control_fronte == 1)
					    		unlink($cartellaImmagini . $imgAR);
					    	
					    	if($control_retro == 1)
					    		unlink($cartellaImmagini . $imgRetroAR);
					    	
					        if ($allaFineSpostaCsv == "") 
					        	$allaFineSpostaCsv = "SI";
					    }
					    else $allaFineSpostaCsv = "NO";
						
						break;
				}
				
			}
			else 
			{
				$allaFineSpostaCsv = "NO";
			}
			
			?>
		
				<tr class="<?=$stileriga?>">
					<td>
						<font class="font11"><?=($k+1)?></font>
					</td>
					<td>
						<label class="font11" title="<?=$tipologiaAtto?>"><?=$breveTipo?></label>
					</td>
					<td>
						<font class="font11"><i><?=$ccComune?></i></font>
					</td>
					<td>
						<?=$numeroCronologico?>
					</td>
					<td>
						<img src="<?=$myImmagineFronte?>" class="pwidth20 pheigth20" title="<?=$imgAR?>">
					</td>
					<td>
						<img src="<?=$myImmagineRetro?>" class="pwidth20 pheigth20" title="<?=$imgRetroAR?>">
					</td>
					<td>
						<font class="font11"><?=$dataDellaNotifica?></font>
					</td>
					<td>
						<font class="font11"><?=$tipoDellaNotifica?> <?=$esitoTipo?></font>
					</td>
					<td>
						<font class="font11"><?=$statoDellaNotifica?> <?=$esitoStato?></font>
					</td>
					<td>
						<img src="<?=$myImmagineOKNO?>" class="pwidth20 pheigth20" title="<?=$esitoOKNO?>">
					</td>
				</tr>
			
			<?php
		} 
	}
	
	
	?>
			</table>
		</td>
	</tr>

<?php } ?>

</table>


</body>
<?php

echo "<script>fine('$scrittaBarra');</script>";

if ($allaFineSpostaCsv == "SI")
{
	for ($p = 0; $p < count($arrayFileCsv); $p++)
	{
		rename ($cartellaCsv . $arrayFileCsv[$p], $cartellaBackUp . $arrayFileCsv[$p]);
	}

	$stringaRdDir = $cartellaImmagini;
	$stringaRdDir = str_replace("Program Files (x86)", "Progra~2", $stringaRdDir);
	$stringaRdDir = str_replace("/", "\\", $stringaRdDir);
	$stringaRdDir = "rd " . $stringaRdDir . " /S /Q ";
	//echo "<br>exec ($stringaRdDir)";

	//da rimettere
	exec ($stringaRdDir);  //  cancello la cartella TEMP
}


?>

</html>