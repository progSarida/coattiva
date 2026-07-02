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

<title>Importazione CDS</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
 
<script>
function inizio()
{
	$('#progressbar').progressbar({
		value: false
	});
	$( "#barlabel" ).text("Inizio importazione...");
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
}

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);

	mostra_file();
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

<?php include GESTIONE . '/menu/menu_gestione.php'; ?>

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

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Importazione CDS</font></td>
	</tr>
</table>

	<br>
		<div class="table_interna text_center" id="progress_bar" style="height:55px;">
			<div class="text_center" id="barlabel"></div>
		</div>
		<br>
		<div id=importazione></div>
	<br>
	
		</td>
	</tr>
</table>	

<?php

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

$objPHPExcel = PHPExcel_IOFactory::load($file_excel);

$num_foglio = 0;
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
	$titolo_foglio[$num_foglio] = $worksheet->getTitle();
	$riga_max[$num_foglio] = $worksheet->getHighestRow(); // e.g. 10
	$colonna_max[$num_foglio] = $worksheet->getHighestColumn(); // e.g 'F'
	$index_colonna_max[$num_foglio] = PHPExcel_Cell::columnIndexFromString($colonna_max[$num_foglio]);
	$num_foglio++;
}

flush();
ob_flush();
flush();
ob_flush();
echo "<script>inizio();</script>";
sleep(2);
flush();
ob_flush();
flush();
ob_flush();

for ($foglio = 0; $foglio < count($titolo_foglio); $foglio++){
	
	if($titolo_foglio[$foglio] == "Importazione_Gitco"){
		
		for ($row = 1; $row <= $riga_max[$foglio]; ++ $row) {
		
			for ($col = 0; $col < $index_colonna_max[$foglio]; ++ $col) {
		
				$cell[$row][$col] = $worksheet->getCellByColumnAndRow($col, $row);
		
				if($row==1)	{
				  
					$intestazione[$col] = $cell[$row][$col]->getCalculatedValue();
		
				}
				else if($intestazione[$col]!="") {
			   
					$valore = $cell[$row][$col]->getCalculatedValue();
					if(PHPExcel_Shared_Date::isDateTime($cell[$row][$col])) {
						$valore = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($valore));
					}
		
					$array_importazione[$row-2][$intestazione[$col]] = $valore;
				}
		
			}
		
		}
		
	}
	
}

unlink($file_excel);

$control_salva_ruolo = 0;
for($k=0;$k<(count($array_importazione));$k++)
{
	set_time_limit(30);
	
	flush();
	ob_flush();
	flush();
	ob_flush();
	echo "<script>update(".intval($k*100/count($array_importazione)).");</script>";
	flush();
	ob_flush();
	flush();
	ob_flush();	
	
	$array_utente = array();
	$array_residenza = array();
	$array_tributo = array();
	
	if($array_importazione[$k]['CODICE_CATASTALE']!=$c)
	{
		alert('Importazione arrestata! Codice catastale diverso da quello di gestione!');
		die;
	}
		
	if($array_importazione[$k]['CODICE_CATASTALE']=="")
		continue;
	
	$CC_importazione = $array_importazione[$k]['CODICE_CATASTALE'];
	
	$array_utente['SESSO'] = strtoupper($array_importazione[$k]['NATURA_GIURIDICA']);
	
	if($array_utente['SESSO']!="PERSONA GIURIDICA")
		$array_utente = decode_CF($array_importazione[$k]['CF_PI']);
	else 
	{
		$array_utente['CC_NASCITA'] = "";
		$array_utente['DATA_NASCITA'] = "";
		$array_utente['SESSO'] = "D";
	}
	
	$array_utente['CF_PI'] = $array_importazione[$k]['CF_PI'];
	
	$array_utente['CC_IMPORTAZIONE'] = $CC_importazione;
	$array_utente['NOME'] = strtoupper($array_importazione[$k]['NOME']);
	$array_utente['COGNOME'] = strtoupper($array_importazione[$k]['COGNOME_DITTA']);
	
	
	
	$array_residenza['STATO_RESIDENZA'] = ucwords(strtolower($array_importazione[$k]['STATO_RESIDENZA']));
	$array_residenza['COMUNE_RESIDENZA'] = ucwords(strtolower($array_importazione[$k]['COMUNE_RESIDENZA']));
	$array_residenza['PROVINCIA_RESIDENZA'] = strtoupper($array_importazione[$k]['PROVINCIA_RESIDENZA']);
	$array_residenza['INDIRIZZO_RESIDENZA'] = strtoupper($array_importazione[$k]['INDIRIZZO_RESIDENZA']);
	$array_residenza['CIVICO_RESIDENZA'] = $array_importazione[$k]['CIVICO_RESIDENZA'];
	$array_residenza['ESPONENTE_RESIDENZA'] = $array_importazione[$k]['ESPONENTE_RESIDENZA'];
	$array_residenza['INTERNO_RESIDENZA'] = $array_importazione[$k]['INTERNO_RESIDENZA'];
	$array_residenza['DETTAGLI_RESIDENZA'] = strtoupper($array_importazione[$k]['DETTAGLI_RESIDENZA']);
	$array_residenza['CAP_RESIDENZA'] = $array_importazione[$k]['CAP_RESIDENZA'];
	$array_residenza['CC_RESIDENZA'] = $array_importazione[$k]['CODICE_CATASTALE_RESIDENZA'];
	
	if(ucwords($array_residenza['STATO_RESIDENZA'])=="Italia" || $array_residenza['STATO_RESIDENZA']=="")
	{
		$trova_cc = new comune(null);
		$array_ritorno = $trova_cc->trovaCC($array_residenza['COMUNE_RESIDENZA'],$array_residenza['CAP_RESIDENZA']);
							
		$array_residenza['COMUNE_RESIDENZA'] = ucwords($array_ritorno['Com_Nome']);
		$array_residenza['CC_RESIDENZA'] = $array_ritorno['Com_Codice_Catastale'];
		$array_residenza['PROVINCIA_RESIDENZA'] = $array_ritorno['Pro_Sigla'];
	}
		
	$array_tributo['TIPO_TRIBUTO'] = $array_importazione[$k]['TIPO_TRIBUTO'];
	
	if( $array_tributo['TIPO_TRIBUTO'] == "CDS" )
	{
		$array_tributo['SANZIONE'] = $array_importazione[$k]['SANZIONE'];
		$array_tributo['SPESE_NOTIFICA'] = $array_importazione[$k]['SPESE_NOTIFICA'];
		$array_tributo['MAGGIORAZIONE_INTERESSI'] = $array_importazione[$k]['MAGGIORAZIONE_INTERESSI'];
		
		$info_cartella = "SANZIONE ";
		$info_cartella.= $array_importazione[$k]['TIPO_SANZIONE']." ".$array_importazione[$k]['NUMERO_SANZIONE']. " DEL ".$array_importazione[$k]['DATA_SANZIONE'];

		$array_tributo['INFO_CARTELLA'] = $info_cartella;
		
		$array_tributo['DATA_NOTIFICA'] = $array_importazione[$k]['DATA_NOTIFICA'];
		$array_tributo['ANNO_RIFERIMENTO'] = $array_importazione[$k]['ANNO_RIFERIMENTO'];
		$array_tributo['TARGA'] = $array_importazione[$k]['TARGA'];
		$array_tributo['DATA_SANZIONE'] = $array_importazione[$k]['DATA_SANZIONE'];
		$array_tributo['NUMERO_SANZIONE'] = $array_importazione[$k]['NUMERO_SANZIONE'];
		
	}
	
	$utente = new utente(null,$CC_importazione);
	
	print_r($array_utente);
	echo "<br>**<br>";
	
	$omonimia = $utente->control_omonimia($array_utente);
	$ritorno_omonimia = explode(' ', $omonimia);

	
	if($ritorno_omonimia[0]=="omo")
	{
		$id_utente = $utente->IdUtenteFromIdComune($ritorno_omonimia[1], $CC_importazione);
		
		$updateUtente = new utente($id_utente, $CC_importazione);
		$updateUtente->Data_Nascita = to_mysql_date($array_utente['DATA_NASCITA']);
		mysql_query('BEGIN');
		
		$control_utente = $updateUtente->Update($id_utente);
		if($control_utente===false)
		{
			mysql_query('ROLLBACK');
			continue;
		}
		else 
			mysql_query('COMMIT');
	}
	else
	{
		$id_utente = null;
	}
	
	$query = "SELECT ID FROM tributo WHERE CC = '".$CC_importazione."' ";
	$query.= "AND Info_Cartella = '".$array_tributo['INFO_CARTELLA']."' LIMIT 1";
	
	$result_cartella = single_answer_query($query);
	
	if($result_cartella!="")
		continue;
	
	
	if(count($array_importazione)>0 && $control_salva_ruolo == 0)
	{
		$ruolo_id_array = select_mysql_array("MAX(Comune_ID) AS max_id", "ruolo", "CC = '".$CC_importazione."'");
		$comune_id_ruolo = $ruolo_id_array[0]['max_id'] + 1;
	
		$ruolo = new ruolo(null, $c);
		$ruolo->CC = $array_importazione[0]['CODICE_CATASTALE'];
		$ruolo->Comune_ID = $comune_id_ruolo;
		$ruolo->Data_Fornitura = date('Y-m-d');
		$ruolo->Data_Inserimento = date('Y-m-d');
		$ruolo->Descrizione = "CDS ".$comune_id_ruolo." - ".date('d/m/Y');
		$ruolo->Progr_Fornitura = 1;
	
		mysql_query('BEGIN');
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
			mysql_query('COMMIT');
			
			$control_salva_ruolo = 1;
		}
	}	
	
	if($id_utente==null)
	{
		$utente->Genere = $array_utente['SESSO'];
		$utente->CC_Comune = $array_utente['CC_IMPORTAZIONE'];
		
		if($array_utente['SESSO']!="D")
		{			
			$utente->Cognome = $array_utente['COGNOME'];
			$utente->Nome = $array_utente['NOME'];
			$utente->Codice_Fiscale = $array_utente['CF_PI'];
			
			$utente->CC_Nascita = $array_utente['CC_NASCITA'];
			$utente->Paese_Nascita = $array_utente['STATO_NASCITA'];
			$utente->Comune_Nascita = $array_utente['COMUNE_NASCITA'];
			$utente->Data_Nascita = to_mysql_date($array_utente['DATA_NASCITA']);
			
		}
		else 
		{
			$utente->Ditta = $array_utente['COGNOME'];
			for($i=strlen($array_utente['CF_PI']);$i<11;$i++)
				$array_utente['CF_PI'] = "0".$array_utente['CF_PI'];
			
			$utente->Partita_Iva = $array_utente['CF_PI'];
		}
		
		mysql_query('BEGIN');
		
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
		
		$toponimo = new toponimo(null, $c);
		
		$toponimo->Cap = $array_residenza['CAP_RESIDENZA'];
		$toponimo->CC_Comune = $CC_importazione;
		$toponimo->CC_Toponimo = $array_residenza['CC_RESIDENZA'];
		$toponimo->Comune = ucwords($array_residenza['COMUNE_RESIDENZA']);
		$toponimo->Nome = $array_residenza['INDIRIZZO_RESIDENZA'];
		$toponimo->Paese = ucwords($array_residenza['STATO_RESIDENZA']);
		
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
		
		$indirizzo = new indirizzo(null, "res", $array_residenza['CC_RESIDENZA']);
		
		$indirizzo->CC_Indirizzo = $array_residenza['CC_RESIDENZA'];
		$indirizzo->Cap = $array_residenza['CAP_RESIDENZA'];
		$indirizzo->Comune = ucwords($array_residenza['COMUNE_RESIDENZA']);
		$indirizzo->Civico = $array_residenza['CIVICO_RESIDENZA'];
		$indirizzo->Data_Inizio_Residenza = "1900-01-01";
		$indirizzo->Dettagli = $array_residenza['DETTAGLI_RESIDENZA'];
		$indirizzo->Esponente = $array_residenza['ESPONENTE_RESIDENZA'];
		$indirizzo->Interno = $array_residenza['INTERNO_RESIDENZA'];
		$indirizzo->Paese = ucwords($array_residenza['STATO_RESIDENZA']);
		$indirizzo->Provincia = $array_residenza['PROVINCIA_RESIDENZA'];
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

	
	if( $array_tributo['TIPO_TRIBUTO'] == "CDS" )
	{
		$comune_id_partita = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$CC_importazione."'");
		
		$partita = new partita( null , $CC_importazione );
		
		$partita->CC = $CC_importazione;
		$partita->Comune_ID = $comune_id_partita + 1;
		$partita->Ruolo_ID = $id_ruolo;
		$partita->Anno_Riferimento = $array_tributo['ANNO_RIFERIMENTO'];
		$partita->Tipo = "CDS";
		$partita->Sottotipo = "";
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
		
		$tributo = new tributo(null, $CC_importazione);
		
		$tributo->CC = $CC_importazione;
		$tributo->Partita_ID = $partita_id;
		$tributo->Info_Cartella = $array_tributo['INFO_CARTELLA'];
		$tributo->Anno_Tributo = $array_tributo['ANNO_RIFERIMENTO'];
		$tributo->Codice_Tributo = "5242";
		$tributo->Imposta = $array_tributo['SANZIONE'];
		$tributo->Data_Decorrenza_Interessi = date("Y-m-d" ,strtotime( $array_tributo['DATA_SANZIONE']."+2 months" ));
		$tributo->Tipo_Info = "S";
		$tributo->Titolo_Sanzione = $array_tributo['NUMERO_SANZIONE'];
		$tributo->Data_Sanzione = $array_tributo['DATA_SANZIONE'];
		$tributo->Targa_Sanzione = $array_tributo['TARGA'];
		
		$control_tributo = $tributo->Insert();
		if($control_tributo===false)
		{
			mysql_query('ROLLBACK');
			continue;
		}
		
		$query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$CC_importazione."'";
		$comune_id = single_query($query);
		
		$atto = new atto(null, $CC_importazione);
		$atto->CC = $c;
		$atto->Comune_ID = $comune_id + 1;
		$atto->Partita_ID = $partita_id;
		$atto->Atto = "Ingiunzione";
		
		$atto->Anno_Cronologico = $array_tributo['ANNO_RIFERIMENTO'];
		$atto->Cronologico_Vecchio = "si";
		$atto->ID_Cronologico = $array_tributo['NUMERO_SANZIONE'];
		
		$atto->Info_Cartella = $array_tributo['INFO_CARTELLA'];
		$atto->Stato_Stampa = "Stampato";
		$atto->Data_Stampa = substr($array_tributo['DATA_NOTIFICA'],0,4)."-01-01";
		$atto->Data_Elaborazione = date('Y-m-d');
		$atto->Importo = $array_tributo['SANZIONE'];
		$atto->Spese_Notifica = $array_tributo['SPESE_NOTIFICA'];
		$atto->Spese_Precedenti = 0.00;
		$atto->Data_Calcolo_Interessi = $array_tributo['DATA_NOTIFICA'];
		$atto->Data_Notifica = $array_tributo['DATA_NOTIFICA'];
		$atto->Data_Decorrenza_Interessi = date("Y-m-d" ,strtotime( $array_tributo['DATA_SANZIONE']."+2 months" ));
		$atto->Interessi = $array_tributo['MAGGIORAZIONE_INTERESSI'];
		$atto->Totale_Dovuto = $array_tributo['SANZIONE']+$array_tributo['SPESE_NOTIFICA']+$array_tributo['MAGGIORAZIONE_INTERESSI'];
		$atto->Modalita_Stampa = "posta";
		$atto->Tipo_Ufficiale = "diretta";
		
		$control_atto = $atto->Insert(true);
		if($control_atto===false)
		{
			mysql_query('ROLLBACK');
			continue;
		}
		
		mysql_query('COMMIT');
		$cont++;

	}
}

flush();
ob_flush();
flush();
ob_flush();
echo "<script>fine();</script>";
flush();
ob_flush();
flush();
ob_flush();

?>

</body>
</html>