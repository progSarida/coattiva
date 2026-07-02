<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/classe_anni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
$cont=0;


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Importazione Excel generale 290</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
 
<script>
function inizio()
{
	$('#progressbar').progressbar({
		value: false
	});
	$( "#barlabel" ).text("Inizio importazione...");
}

function inizio2()
{
	$('#progressbar2').progressbar({
		value: false
	});
	$( "#barlabel2" ).text("Inizio importazione...");
}

function msg_bar(value)
{
	$( "#barlabel" ).text(value);
}

function msg_bar2(value)
{
	$( "#barlabel2" ).text(value);
}

function update(valore, riga)
{
	$( "#progressbar" ).progressbar({value: parseInt(valore) });
	$( "#barlabel" ).text( riga + " " + valore + "%" );
}

function update2(valore, cella)
{
	$( "#progressbar2" ).progressbar({value: parseInt(valore) });
	$( "#barlabel2" ).text( valore + "% ( Cella " + cella + " )" );
}

function nessun_risultato()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Nessun risultato trovato");
}

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);

	mostra_file();
}

function get_elapsed_time_string(total_seconds) {
	  function pretty_time_string(num) {
	    return ( num < 10 ? "0" : "" ) + num;
	  }

	  var hours = Math.floor(total_seconds / 3600);
	  total_seconds = total_seconds % 3600;

	  var minutes = Math.floor(total_seconds / 60);
	  total_seconds = total_seconds % 60;

	  var seconds = Math.floor(total_seconds);

	  // Pad the minutes and seconds with leading zeros, if required
	  hours = pretty_time_string(hours);
	  minutes = pretty_time_string(minutes);
	  seconds = pretty_time_string(seconds);

	  // Compose the string for display
	  var currentTimeString = hours + "h:" + minutes + "m:" + seconds+ "s" ;

	  return currentTimeString;
	}

var elapsed_seconds = 0;
var timer = setInterval(function() {
  elapsed_seconds = elapsed_seconds + 1;
  $('#time').text('Durata operazioni '+get_elapsed_time_string(elapsed_seconds));
}, 1000);

function cancella_timer(){	
	clearInterval(timer);
	timer = 0;
	$('#time').text('Operazioni terminate in '+get_elapsed_time_string(elapsed_seconds));
}

</script>

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

<?php include MENU . '/menu_generale.php'; ?>

<table align=center class=table_interna border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Consultazione'" href="#" onClick="" style="background:#51CB78; text-decoration: none;">
			<img src="/gitco2/immagini/F2.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="" style="background:#CA7D7D; text-decoration: none;">
			<img src="/gitco2/immagini/redF2.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovo.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick = "" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick = "" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaS.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaD.png" width=42px height=42px border="0" alt="Utente successivo"></a>
        </td>
        <td width=15%></td>
		<td align=center width=7% >
			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td width=3%></td>
		<td width=7%>
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

		<br><br>
		<font class="titolo font18 text_center">Importazione generale</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div class="table_interna text_center" id="progressbar2" style="height:55px;"><div class="text_center" id="barlabel2"></div></div>
		<br>
		<div><span id="time"></span></div>
		</td>
	</tr>
</table>
	
		</td>
	</tr>
</table>	

<?php

flush();
ob_flush();
flush();
ob_flush();
echo "<script>inizio();</script>";
flush();
ob_flush();
flush();
ob_flush();
sleep(2);

require EXCEL . '/PHPExcel.php';
require_once EXCEL . '/PHPExcel/IOFactory.php';

$percorso_file = $_FILES['file_excel']['tmp_name'];
$nome_file = $_FILES['file_excel']['name'];

$path_file = $basePath."/290/";
$file_excel = $path_file.$nome_file;
if($percorso_file != "")
{
	$v = move_uploaded_file($percorso_file, $file_excel);
}

flush();
ob_flush();
flush();
ob_flush();
echo "<script>msg_bar('Caricamento File... La procedura potrebbe richiedere alcuni minuti!');</script>";
flush();
ob_flush();
flush();
ob_flush();

ini_set('memory_limit', '-1');
ini_set('xdebug.max_nesting_level', '-1');
set_time_limit(-1);


$r = PHPExcel_CachedObjectStorageFactory::initialize(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp);
if (!$r) {
	die('Unable to set cell caching');
}

$objReader = PHPExcel_IOFactory::createReaderForFile($file_excel);
/**  Advise the Reader of which WorkSheets we want to load  **/
$objReader->setReadDataOnly(true);

$loadSheets = array('UTENTE','CARTELLA','TRIBUTI');
$objReader->setLoadSheetsOnly($loadSheets);

$control_field['UTENTE'] = 0;
$control_field['CARTELLA'] = 2;
$control_field['TRIBUTI'] = 2;

/**  Load $inputFileName to a PHPExcel Object  **/
$objPHPExcel = $objReader->load($file_excel);

$num_foglio = 0;
$array_importazione = array();
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

	$sheet = $objPHPExcel->setActiveSheetIndex($num_foglio);
	$titolo_foglio = $worksheet->getTitle();
	$riga_max = $worksheet->getHighestRow(); // e.g. 10
	$colonna_max = $worksheet->getHighestColumn(); // e.g 'F'
	$index_colonna_max = PHPExcel_Cell::columnIndexFromString($colonna_max);
	
	$intestazione = array();
	$limit_row = false;

	for ($row = 1; $row <= $riga_max; $row++) {

		echo "<script>update(".ceil($row/$riga_max*100).",'Caricamento dati... ');</script>";
		flush();
		ob_flush();
		flush();
		ob_flush();
		if($row>3){
			$controlCell = $sheet->getCellByColumnAndRow($control_field[$titolo_foglio], $row);
			if($titolo_foglio!="CARTELLA")
				$controllo = $controlCell->getValue();
			else 
				$controllo = $controlCell->getOldCalculatedValue();			
			if($controllo==null){
				echo "<script>fine('100% ".$titolo_foglio,"');</script>";
				break;
			}
		}
		
		for ($col = 0; $col < $index_colonna_max; $col++) {
			
			$cell = $sheet->getCellByColumnAndRow($col, $row);
			if($row==1)	{
				$intestazione[$col] = $cell->getValue();
			}
			
			if(isset($intestazione[$col])){
				if(substr($intestazione[$col], 0,3)=="NO_")
					continue;
			}
			
			if($row>1 && $intestazione[$col]!="") {
										
					if(PHPExcel_Shared_Date::isDateTime($cell) || $intestazione[$col]=="DATA_DECORRENZA_INTERESSI" || $intestazione[$col]=="DATA_NASCITA" || $intestazione[$col]=="DATA_SANZIONE") {
						if($cell->getValue()!=null)
							$calcValue = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($cell->getValue()));
						else 
							$calcValue = "";
					}
					else{
						if($intestazione[$col] == "UTENTE_ID" || $intestazione[$col] == "PARTITA_ID")
							$calcValue = $cell->getOldCalculatedValue();
						else 
							$calcValue = $cell->getValue();
					}
					
					$array_importazione[$titolo_foglio][$row-2][$intestazione[$col]] = $calcValue;
				}
						
			}
		}

	$num_foglio++;
}

unlink($file_excel);

foreach ($array_importazione as $key=>$new_array) {
	$k=0;
	for($i=0;$i<count($new_array);$i++){
		switch($key){
			case "UTENTE":
				$array_excel[$k] = $new_array[$i];
			
				break;
			case "CARTELLA":
				$j = 0;
				for($z=0;$z<count($array_excel);$z++){					
					if($new_array[$i]['UTENTE_ID']==$array_excel[$z]['UTENTE_ID']){
						$array_excel[$z]['PARTITA'][$j] = $new_array[$i];
						$j++;
					}
				}
				break;
			case "TRIBUTI":
				$j = 0;
				for($z=0;$z<count($array_excel);$z++){
					for($m=0;$m<count($array_excel[$z]['PARTITA']);$m++){
											
						if($new_array[$i]['PARTITA_ID']==$array_excel[$z]['PARTITA'][$m]['PARTITA_ID']){
							$array_excel[$z]['PARTITA'][$m]['TRIBUTI'][$j] = $new_array[$i];
							$j++;
						}
					}
				}
				break;
		}
		$k++;
	}
}

flush();
ob_flush();
echo "<script>inizio();</script>";
echo "<script>msg_bar('Inizio importazione...');</script>";
flush();
ob_flush();
sleep(2);

$control_salva_ruolo = 0;

$codici_tributo = new codice_tributo(null);
$array_codici = $codici_tributo->array_ordinato("Settore, Codice_Tributo");

for($k=0;$k<(count($array_excel));$k++)
{	
	flush();
	ob_flush();
	flush();
	ob_flush();
	echo "<script>update(".intval($k*100/count($array_excel)).",'');</script>";
	flush();
	ob_flush();
	flush();
	ob_flush();	
	
	mysql_query('BEGIN');
	
	if($array_excel[$k]['CODICE_CATASTALE']!=$c)
	{
		alert('Importazione arrestata! Codice catastale diverso da quello di gestione!');
		die;
	}
		
	if($array_excel[$k]['CODICE_CATASTALE']=="")
		continue;
	
	$CC_importazione = $array_excel[$k]['CODICE_CATASTALE'];
	
	if($array_excel[$k]['NATURA_GIURIDICA']!="PERSONA GIURIDICA"){
		$array_utente = decode_CF($array_excel[$k]['CODICE_FISCALE/PARTITA_IVA']);
		$array_excel[$k]['CODICE_CATASTALE_NASCITA'] = $array_utente['CC_NASCITA'];
		$array_excel[$k]['DATA_NASCITA'] = $array_utente['DATA_NASCITA'];
		$array_excel[$k]['SESSO'] = $array_utente['SESSO'];
		$array_excel[$k]['STATO_NASCITA'] = $array_utente['STATO_NASCITA'];
		$array_excel[$k]['COMUNE_NASCITA'] = $array_utente['COMUNE_NASCITA'];
	}
	else 
	{
		$array_excel[$k]['CODICE_CATASTALE_NASCITA'] = "";
		$array_excel[$k]['DATA_NASCITA'] = "";
		$array_excel[$k]['SESSO'] = "D";
		$array_excel[$k]['STATO_NASCITA'] = "";
		$array_excel[$k]['COMUNE_NASCITA'] = "";
	}
	
	$array_utente['CF_PI'] = $array_excel[$k]['CODICE_FISCALE/PARTITA_IVA'];
	$array_utente['CC_IMPORTAZIONE'] = $CC_importazione;
	$array_utente['COGNOME'] = $array_excel[$k]['COGNOME/DITTA'];
	$array_utente['NOME'] = $array_excel[$k]['NOME'];

	if(strtoupper($array_excel[$k]['STATO_RESIDENZA'])=="ITALIA" || $array_excel[$k]['STATO_RESIDENZA']=="")
	{
		$trova_cc = new comune(null);
		$array_ritorno = $trova_cc->trovaCC(strtoupper($array_excel[$k]['COMUNE_RESIDENZA/SEDE']),$array_excel[$k]['CAP_RESIDENZA']);

		if($array_excel[$k]['CODICE_CATASTALE_RESIDENZA']!="")
		{
			$array_excel[$k]['COMUNE_RESIDENZA'] = strtoupper($array_ritorno['Com_Nome']);
			$array_excel[$k]['CODICE_CATASTALE_RESIDENZA'] = $array_ritorno['Com_Codice_Catastale'];
		}
	}
	
	$utente = new utente(null,$CC_importazione);
	$omonimia = $utente->control_omonimia($array_utente);
	$ritorno_omonimia = explode(' ', $omonimia);
	
	if($ritorno_omonimia[0]=="omo")
	{
		$id_utente = $utente->IdUtenteFromIdComune($ritorno_omonimia[1], $CC_importazione);
	}
	else
	{
		$utente->Genere = $array_excel[$k]['SESSO'];
		$utente->CC_Comune = $CC_importazione;
		
		if($array_utente['SESSO']!="D")
		{
			$utente->Cognome = $array_excel[$k]['COGNOME/DITTA'];
			$utente->Nome = $array_excel[$k]['NOME'];
			$utente->Codice_Fiscale = $array_excel[$k]['CODICE_FISCALE/PARTITA_IVA'];
				
			$utente->CC_Nascita = $array_excel[$k]['CODICE_CATASTALE_NASCITA'];
			$utente->Paese_Nascita = $array_excel[$k]['STATO_NASCITA'];
			$utente->Comune_Nascita = $array_excel[$k]['COMUNE_NASCITA'];
			$utente->Data_Nascita = to_mysql_date($array_excel[$k]['DATA_NASCITA']);
				
		}
		else
		{
			$utente->Ditta = $array_excel[$k]['COGNOME/DITTA'];
			$utente->Partita_Iva = $array_excel[$k]['CODICE_FISCALE/PARTITA_IVA'];
		}
		
		$comune_id_array = select_mysql_array("MAX(Comune_ID) AS max_id", "utente", "CC_Comune = '".$CC_importazione."'");
		$comune_id = $comune_id_array[0]['max_id'] + 1;
		
		$utente->Comune_ID = $comune_id;
		
		$control_utente = $utente->Insert();
		if($control_utente===false)
		{
			mysql_query('ROLLBACK');
			continue;
		}
		else
		{
			$id_utente = mysql_insert_id();
		}
		
		if($id_utente==null)
		{		
			$toponimo = new toponimo(null, $c);
		
			$toponimo->Cap = $array_excel[$k]['CAP_RESIDENZA'];
			$toponimo->CC_Comune = $CC_importazione;
			$toponimo->CC_Toponimo = $array_excel[$k]['CODICE_CATASTALE_RESIDENZA'];
			$toponimo->Comune = $array_excel[$k]['COMUNE_RESIDENZA/SEDE'];
			$toponimo->Nome = $array_excel[$k]['INDIRIZZO_RESIDENZA'];
			$toponimo->Paese = $array_excel[$k]['STATO_RESIDENZA'];
		
			$control_toponimo = $toponimo->Insert();
			if($control_toponimo===false)
			{
				mysql_query('ROLLBACK');
				continue;
			}
			else
			{
				$toponimo_id = mysql_insert_id();
			}
		
			$indirizzo = new indirizzo(null, "res", $array_excel[$k]['CC_RESIDENZA']);
		
			$indirizzo->CC_Indirizzo = $array_excel[$k]['CODICE_CATASTALE_RESIDENZA'];
			$indirizzo->Cap = $array_excel[$k]['CAP_RESIDENZA'];
			$indirizzo->Comune = $array_excel[$k]['COMUNE_RESIDENZA/SEDE'];
			$indirizzo->Civico = $array_excel[$k]['CIVICO_RESIDENZA'];
			$indirizzo->Data_Inizio_Residenza = "1900-01-01";
			$indirizzo->Dettagli = $array_excel[$k]['DETTAGLI_RESIDENZA'];
			$indirizzo->Esponente = $array_excel[$k]['ESPONENTE_RESIDENZA'];
			$indirizzo->Interno = $array_excel[$k]['INTERNO_RESIDENZA'];
			$indirizzo->Paese = $array_excel[$k]['STATO_RESIDENZA'];
			$indirizzo->Provincia = $array_excel[$k]['SIGLA_PROVINCIA_RESIDENZA'];
			$indirizzo->Tipo = "res";
			$indirizzo->Utente_ID = $id_utente;
			$indirizzo->Via_Cap_ID = 1;
			$indirizzo->Via_ID = $toponimo_id;
		
			$control_indirizzo = $indirizzo->Insert();
			if($control_indirizzo===false)
			{
				mysql_query('ROLLBACK');
				continue;
			}
		}
	}	
	
	$array_partita = $array_excel[$k]['PARTITA'];
	$salta_posizione = 0;
	for($m=0;$m<count($array_partita);$m++){
		
		$query = "SELECT ID FROM tributo WHERE CC = '".$CC_importazione."' ";
		$query.= "AND Info_Cartella = '".$array_partita[$m]['INFORMAZIONI_CARTELLA']."' LIMIT 1";
		
		$result_cartella = single_answer_query($query);
		
		if($result_cartella!="")
			continue;
		
		$partita = new partita( null , $CC_importazione );
		$array_tributi = $array_partita[$m]['TRIBUTI'];
		$array_tipo['Tipo'] = "";
		$array_tipo['Sottotipo'] = "";
		for($j=0;$j<count($array_tributi);$j++){
			
			$explode_codice = explode(" ", $array_tributi[$j]['CODICE_TRIBUTO']);
			
			if($array_tipo['Tipo']=="")
				$array_tipo = $partita->estraiTipoPartita($explode_codice[0], $array_codici);
			else 
				break;
			
		}
		
		if($control_salva_ruolo == 0)
		{
			$ruolo_id_array = select_mysql_array("MAX(Comune_ID) AS max_id", "ruolo", "CC = '".$CC_importazione."'");
			$comune_id_ruolo = $ruolo_id_array[0]['max_id'] + 1;
		
			$ruolo = new ruolo(null, $c);
			$ruolo->CC = $CC_importazione;
			$ruolo->Comune_ID = $comune_id_ruolo;
			$ruolo->Data_Fornitura = date('Y-m-d');
			$ruolo->Data_Inserimento = date('Y-m-d');
			if($array_tipo['Sottotipo']!="")
				$descrizione_ruolo = $array_tipo['Sottotipo'];
			else 
				$descrizione_ruolo = $array_tipo['Tipo'];
			
			$ruolo->Descrizione = $descrizione_ruolo." ".$comune_id_ruolo." - ".date('d/m/Y');
			$ruolo->Progr_Fornitura = 1;
		
			$control_ruolo = $ruolo->Insert();
			if($control_ruolo===false)
			{
				mysql_query('ROLLBACK');
				alert('salva ruolo fallito');
				die;
			}
			else
			{
				$id_ruolo = mysql_insert_id();
					
				$control_salva_ruolo = 1;
			}
		}
			
		if($array_tipo['Tipo']=="")
			continue;
		
		$comune_id_partita = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$CC_importazione."'");
		
		$partita->CC = $CC_importazione;
		$partita->Comune_ID = $comune_id_partita + 1;
		$partita->Ruolo_ID = $id_ruolo;
		$partita->Anno_Riferimento = $array_partita[$m]['ANNO_RIFERIMENTO'];
		$partita->Tipo = $array_tipo['Tipo'];
		$partita->Sottotipo = $array_tipo['Sottotipo'];
		$partita->Utente_ID = $id_utente;
		
		$control_partita = $partita->Insert();
		if($control_partita===false)
		{
			mysql_query('ROLLBACK');
			continue;
		}
		else
		{
			$partita_id = mysql_insert_id();
		}
		
		for($j=0;$j<count($array_tributi);$j++){
			$tributo = new tributo(null, $CC_importazione);
			
			$tributo->CC = $CC_importazione;
			$tributo->Partita_ID = $partita_id;
			$tributo->Info_Cartella = $array_partita[$m]['INFORMAZIONI_CARTELLA'];
			$tributo->Anno_Tributo = $array_partita[$m]['ANNO_RIFERIMENTO'];
			$tributo->Codice_Tributo = $explode_codice[0];
			$tributo->Imposta = $array_tributi[$j]['IMPORTO'];
			$tributo->Data_Decorrenza_Interessi = to_mysql_date($array_partita[$m]['DATA_DECORRENZA_INTERESSI']);
			$tributo->Tipo_Info = substr($array_partita[$m]['TIPO_INFORMAZIONI'],0,1);
			$tributo->Titolo_Entrata = $array_partita[$m]['TITOLO_ENTRATA'];
			$tributo->Descrizione_Entrata = $array_partita[$m]['DESCRIZIONE_ENTRATA'];
			$tributo->Titolo_Sanzione = $array_partita[$m]['TITOLO_SANZIONE'];
			$tributo->Tipo_Sanzione = $array_partita[$m]['TIPO_SANZIONE'];
			$tributo->Data_Sanzione = to_mysql_date($array_partita[$m]['DATA_SANZIONE']);
			$tributo->Targa_Sanzione = $array_partita[$m]['TARGA_SANZIONE'];
			$tributo->Matricola = $array_partita[$m]['MATRICOLA'];
				
			$control_tributo = $tributo->Insert();
			if($control_tributo===false)
			{
				mysql_query('ROLLBACK');
				$salta_posizione = 1;
				break;
			}			
		}
		
		if($salta_posizione==1)
			continue;	
	}
	mysql_query('COMMIT');
}

echo "<script>cancella_timer(timer);</script>";
flush();
ob_flush();

flush();
ob_flush();
flush();
ob_flush();
echo "<script>fine('Importazione completata!');</script>";
flush();
ob_flush();
flush();
ob_flush();

?>

</body>
</html>