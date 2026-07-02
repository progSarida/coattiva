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

$pathFolder = $_SERVER['DOCUMENT_ROOT']."/archivio/Importazioni_Excel/".$c."/";
crea_dir($pathFolder);
$nomeFile = $pathFolder.$c."_ScartiImportazione_".date('Y-m-d_H-i-s').".xls";

$vedi_file = mostra_file_path($nomeFile);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Importazione TSRSU</title>

    <link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
    <link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
    <style> .ui-datepicker { font-size:11px; } </style>


    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
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

function fine()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( "100%" );

	sleep(1000);
}

function mostra_file()
{
    window.name = "Stampa";
    window.open('<?php echo $vedi_file; ?>',"Stampa");
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

        <br><br><br>
        <font class="titolo font18 text_center">Importazione da Modello Excel</font>

        <br><br>

        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>

        <br>

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
$conta_righe = 0;
for ($foglio = 0; $foglio < count($titolo_foglio); $foglio++){
	
	if($titolo_foglio[$foglio] == "IMPORTAZIONE_GITCO"){

		for ($row = 1; $row <= $riga_max[$foglio]; ++ $row) {

            $checkValue = $worksheet->getCellByColumnAndRow(0, $row)->getCalculatedValue();
		    if(trim($checkValue)!=""){

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

                        $array_importazione[$conta_righe][$intestazione[$col]] = trim($valore);
                    }

                }

                if($row!=1)
                    $conta_righe++;
            }

		}
		
	}
	
}

unlink($file_excel);
$a_scarti = array();
$control_salva_ruolo = 0;
for($k=0;$k<$conta_righe;$k++)
{
	set_time_limit(40);

	echo "<script>update(".ceil(($k * 100) / $conta_righe).");</script>";

	flush();
	ob_flush();
	flush();
	ob_flush();
    flush();
    ob_flush();
	
	$a_utente = array();
    $row = $array_importazione[$k];

    //FINE FILE
    if($row['CODICE_CATASTALE_COMUNE']=="")
        break;

	if($row['CODICE_CATASTALE_COMUNE']!=$c) {
		alert('Importazione arrestata! Codice catastale diverso da quello di gestione!');
		die;
	}

	$tipoRiscossione = "";
	$sottotipoRiscossione = "";
    $dataDecorrenza = "";
    $tipoInfoTributo = "E";
    $row['DATA_NOTIFICA'] = to_mysql_date($row['DATA_NOTIFICA']);
    if($row['DATA_NOTIFICA']!="")
        $dataDecorrenza = date("Y-m-d" ,strtotime( $row['DATA_NOTIFICA']."+1 month" ));

	$row['ERRORE_SCARTO'] = "";
	switch ($row['TIPO_RISCOSSIONE']){
        case "TARES / TARI":
            $tipoRiscossione = "RIFIUTI";
            $sottotipoRiscossione = "TARES";

            break;
        case "TSRSU":
            $tipoRiscossione = "RIFIUTI";
            $sottotipoRiscossione = "TSRSU";
            break;
        case "IMU":
            $tipoRiscossione = "IMMOBILI";
            $sottotipoRiscossione = "IMU";
            break;
        case "ICI":
            $tipoRiscossione = "IMMOBILI";
            $sottotipoRiscossione = "ICI";
            break;
        case "CDS / AMMINISTRATIVA":
            $tipoRiscossione = "CDS";
            $sottotipoRiscossione = "";
            $tipoInfoTributo = "S";
            if($row['DATA_NOTIFICA']!="")
                $dataDecorrenza = date("Y-m-d" ,strtotime( $row['DATA_NOTIFICA']."+2 month" ));
            break;
        case "IRPEF":
            $tipoRiscossione = "IRPEF";
            $sottotipoRiscossione = "";
            break;
        case "OSAP":
            $tipoRiscossione = "OSAP";
            $sottotipoRiscossione = "";
            break;
        case "PATRIMONIALE":
            $tipoRiscossione = "PATRIMONIALE";
            $sottotipoRiscossione = "";
            break;
        case "PUBBLICITA":
            $tipoRiscossione = "PUBBLICITA";
            $sottotipoRiscossione = "";
            break;
        default:
            $row['ERRORE_SCARTO'] = "TIPO RISCOSSIONE SCONOSCIUTA";
            $a_scarti[] = $row;
            continue;
            break;
    }

    $typeCF = 0;
	if(trim($row['COGNOME'])!="" && trim($row['NOME'])!="" && trim($row['CODICE_FISCALE'])!=""){

        $a_utente = decode_CF($row['CODICE_FISCALE']);
        
        $a_utente['NOME'] = strtoupper(trim($row['NOME']));
        $a_utente['COGNOME'] = strtoupper(trim($row['COGNOME']));
        $a_utente['CODICE_FISCALE'] = trim($row['CODICE_FISCALE']);
        $a_utente['CF_PI'] = $a_utente['CODICE_FISCALE'];

        $a_utente['SOCIETA'] = "";
        $a_utente['PARTITA_IVA'] = "";
        
        $a_utente['DEFUNTO'] = $row['DEFUNTO'];
        $typeCF = 1;
    }        
	else if($row['SOCIETA']!="" && $row['PARTITA_IVA']!=""){

        $a_utente['NOME'] = "";
        $a_utente['COGNOME'] = "";
        $a_utente['CODICE_FISCALE'] = "";

        $a_utente['SOCIETA'] = strtoupper(trim($row['SOCIETA']));
        $a_utente['PARTITA_IVA'] = trim($row['PARTITA_IVA']);
        $a_utente['CF_PI'] = $a_utente['PARTITA_IVA'];

        $a_utente['DEFUNTO'] = "";
        
        $a_utente['CC_NASCITA'] = "";
        $a_utente['DATA_NASCITA'] = "";
        $a_utente['SESSO'] = "D";
        $typeCF = 2;
        
    }
    else{
        $row['ERRORE_SCARTO'] = "ANAGRAFICA ERRATA";
        $a_scarti[] = $row;
        continue;
    }

    $a_utente['CC_IMPORTAZIONE'] = $row['CODICE_CATASTALE_COMUNE'];

    $controlCF_PI = check_CFPI($a_utente['CF_PI'],$typeCF);
	if($controlCF_PI!==true){
        $row['ERRORE_SCARTO'] = strtoupper($controlCF_PI);
        $a_scarti[] = $row;
        continue;
    }

    $row['PRESSO'] = trim($row['PRESSO']);

    //INDIRIZZO DESTINATARIO
    $row['PAESE_DESTINATARIO'] = strtoupper($row['PAESE_DESTINATARIO']);
    $row['COMUNE_DESTINATARIO'] = strtoupper($row['COMUNE_DESTINATARIO']);
    $row['VIA_DESTINATARIO'] = strtoupper($row['VIA_DESTINATARIO']);
    $row['DETTAGLI_DESTINATARIO'] = strtoupper($row['DETTAGLI_DESTINATARIO']);

    if($row['PAESE_DESTINATARIO']=="ITALIA" || $row['PAESE_DESTINATARIO']=="")
    {
        $trova_cc = new comune($row['CODICE_CATASTALE_DESTINATARIO']);

        if($row['CODICE_CATASTALE_DESTINATARIO']=="")
        {
            $array_ritorno = $trova_cc->trovaCC($row['COMUNE_DESTINATARIO'],$row['CAP_DESTINATARIO']);
            $row['COMUNE_DESTINATARIO'] = strtoupper($array_ritorno['Com_Nome']);
            $row['CODICE_CATASTALE_DESTINATARIO'] = $array_ritorno['Com_Codice_Catastale'];
            $row['PROVINCIA_DESTINATARIO'] = $array_ritorno['Pro_Sigla'];
            $row['CAP_DESTINATARIO'] = $array_ritorno['Com_Cap'];
        }
        else{
            $row['COMUNE_DESTINATARIO'] = strtoupper($trova_cc->Nome);
            $row['PROVINCIA_DESTINATARIO'] = $trova_cc->Pro_Sigla;
            $row['CAP_DESTINATARIO'] = $trova_cc->Cap;
        }
    }
    else{
        $trova_stato = new stato_estero($row['CODICE_CATASTALE_DESTINATARIO']);

        if($row['CODICE_CATASTALE_DESTINATARIO']=="")
        {
            $array_ritorno = $trova_stato->trovaCC($row['PAESE_DESTINATARIO']);
            $row['CODICE_CATASTALE_DESTINATARIO'] = $array_ritorno['CC_Paese_Estero'];
        }
        else{
            $row['PAESE_DESTINATARIO'] = strtoupper($trova_stato->Nome);
        }
    }

    if($row['CODICE_CATASTALE_DESTINATARIO']==""){
        $row['ERRORE_SCARTO'] = "PROBLEMI NEL RICONOSCIMENTO DEL COMUNE/PAESE DEL DESTINATARIO";
        $a_scarti[] = $row;
        continue;
    }

    $utente = new utente(null,$row['CODICE_CATASTALE_COMUNE']);
    echo "CONTROLLO OMONIMIA <br>";
    print_r($utente);
    $omonimia = $utente->control_omonimia($a_utente);
    $exp_omonimia = explode(' ', $omonimia);

    if($exp_omonimia[0]=="omo") {
        $id_utente = $utente->IdUtenteFromIdComune($exp_omonimia[1], $row['CODICE_CATASTALE_COMUNE']);

        $updateUtente = new utente($id_utente, $row['CODICE_CATASTALE_COMUNE']);
        $updateUtente->Data_Nascita = to_mysql_date($a_utente['DATA_NASCITA']);
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
        $id_utente = null;

    if($row['DEFUNTO']!="")
        $row['INFORMAZIONI_CARTELLA'].= " - ".$row['DEFUNTO'];

    $query = "SELECT ID FROM tributo WHERE CC = '".$row['CODICE_CATASTALE_COMUNE']."' ";
    $query.= "AND Info_Cartella = '".$row['INFORMAZIONI_CARTELLA']."' LIMIT 1";

    $result_cartella = single_answer_query($query);

    //PARTITA ESISTE GIA!
    if($result_cartella!=""){
        $row['ERRORE_SCARTO'] = "PARTITA CONTABILE GIA' PRESENTE IN ARCHIVIO";
        $a_scarti[] = $row;
        continue;
    }

    if($row['DATA_NOTIFICA']==""){
        $row['ERRORE_SCARTO'] = "DATA DI NOTIFICA ASSENTE";
        $a_scarti[] = $row;
        continue;
    }

    $totaleDovuto = $row['IMPORTO']+$row['SPESE']+$row['SANZIONE']+$row['INTERESSI']-$row['PAGAMENTO'];
    if( $totaleDovuto <=0 && $totaleDovuto!=$row['DOVUTO_TOTALE']){
        $row['ERRORE_SCARTO'] = "ERRORE NEGLI IMPORTI";
        $a_scarti[] = $row;
        continue;
    }

	if($control_salva_ruolo == 0) {
		$ruolo_id_array = select_mysql_array("MAX(Comune_ID) AS max_id", "ruolo", "CC = '".$row['CODICE_CATASTALE_COMUNE']."'");
		$comune_id_ruolo = $ruolo_id_array[0]['max_id'] + 1;
	
		$ruolo = new ruolo(null, $c);
		$ruolo->CC = $row['CODICE_CATASTALE_COMUNE'];
		$ruolo->Comune_ID = $comune_id_ruolo;
		$ruolo->Data_Fornitura = date('Y-m-d');
		$ruolo->Data_Inserimento = date('Y-m-d');
		$ruolo->Descrizione = $row['TIPO_RISCOSSIONE']." - Ruolo n.".$comune_id_ruolo." - ".date('d/m/Y');
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
		$utente->Genere = $a_utente['SESSO'];
		$utente->CC_Comune = $row['CODICE_CATASTALE_COMUNE'];
		
		if($a_utente['SESSO']!="D")
		{			
			$utente->Cognome = $a_utente['COGNOME'];
			$utente->Nome = $a_utente['NOME'];
			$utente->Codice_Fiscale = $a_utente['CODICE_FISCALE'];
			
			$utente->CC_Nascita = $a_utente['CC_NASCITA'];
			$utente->Paese_Nascita = $a_utente['STATO_NASCITA'];
			$utente->Comune_Nascita = $a_utente['COMUNE_NASCITA'];
			$utente->Data_Nascita = to_mysql_date($a_utente['DATA_NASCITA']);
			
		}
		else 
		{
			$utente->Ditta = $a_utente['SOCIETA'];
			$utente->Partita_Iva = $a_utente['PARTITA_IVA'];
		}
		
		mysql_query('BEGIN');
		
		$comune_id_array = select_mysql_array("MAX(Comune_ID) AS max_id", "utente", "CC_Comune = '".$row['CODICE_CATASTALE_COMUNE']."'");
		$comune_id = $comune_id_array[0]['max_id'] + 1;
		
		$utente->Comune_ID = $comune_id;
		
		$control_utente = $utente->Insert();
		if($control_utente===false)
		{
			mysql_query('ROLLBACK');
			continue;
		}
		else
			$id_utente = mysql_insert_id();
		
		$toponimo = new toponimo(null, $c);
		
		$toponimo->Cap = $row['CAP_DESTINATARIO'];
		$toponimo->CC_Comune = $row['CODICE_CATASTALE_COMUNE'];
		$toponimo->CC_Toponimo = $row['CODICE_CATASTALE_DESTINATARIO'];
		$toponimo->Comune = $row['COMUNE_DESTINATARIO'];
		$toponimo->Nome = $row['VIA_DESTINATARIO'];
		$toponimo->Paese = $row['PAESE_DESTINATARIO'];
		
		$control_toponimo = $toponimo->Insert();
		if($control_toponimo===false)
		{
			mysql_query('ROLLBACK');
            $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DELL'INDIRIZZO NELLA TABELLA DEI TOPONIMI";
            $a_scarti[] = $row;
            continue;
		}
		else
			$toponimo_id = mysql_insert_id();

		
		if($row['PRESSO'] != "")
		{
			$indirizzo = new indirizzo(null, "rec", $row['CODICE_CATASTALE_DESTINATARIO']);
		
			$indirizzo->Presso = $row['PRESSO'];
			$indirizzo->CC_Indirizzo = $row['CODICE_CATASTALE_DESTINATARIO'];
			$indirizzo->Cap = $row['CAP_DESTINATARIO'];
			$indirizzo->Comune = $row['COMUNE_DESTINATARIO'];
			$indirizzo->Civico = $row['CIVICO_DESTINATARIO'];
			$indirizzo->Data_Inizio_Residenza = "1900-01-01";
			$indirizzo->Dettagli = $row['DETTAGLI_DESTINATARIO'];
			$indirizzo->Esponente = $row['ESPONENTE_DESTINATARIO'];
			$indirizzo->Interno = $row['INTERNO_DESTINATARIO'];
			$indirizzo->Paese = $row['PAESE_DESTINATARIO'];
			$indirizzo->Provincia = $row['PROVINCIA_DESTINATARIO'];
			$indirizzo->Tipo = "rec";
			$indirizzo->Utente_ID = $id_utente;
			$indirizzo->Via_Cap_ID = 1;
			$indirizzo->Via_ID = $toponimo_id;
		

		}
		else{
            $indirizzo = new indirizzo(null, "res", $row['CODICE_CATASTALE_DESTINATARIO']);

            $indirizzo->CC_Indirizzo = $row['CODICE_CATASTALE_DESTINATARIO'];
            $indirizzo->Cap = $row['CAP_DESTINATARIO'];
            $indirizzo->Comune = $row['COMUNE_DESTINATARIO'];
            $indirizzo->Civico = $row['CIVICO_DESTINATARIO'];
            $indirizzo->Data_Inizio_Residenza = "1900-01-01";
            $indirizzo->Dettagli = $row['DETTAGLI_DESTINATARIO'];
            $indirizzo->Esponente = $row['ESPONENTE_DESTINATARIO'];
            $indirizzo->Interno = $row['INTERNO_DESTINATARIO'];
            $indirizzo->Paese = $row['PAESE_DESTINATARIO'];
            $indirizzo->Provincia = $row['PROVINCIA_DESTINATARIO'];
            $indirizzo->Tipo = "res";
            $indirizzo->Utente_ID = $id_utente;
            $indirizzo->Via_Cap_ID = 1;
            $indirizzo->Via_ID = $toponimo_id;

        }

        $control_indirizzo = $indirizzo->Insert();
        if($control_indirizzo===false)
        {
            mysql_query('ROLLBACK');
            $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DELL'INDIRIZZO DEL DESTINATARIO";
            $a_scarti[] = $row;
            continue;
        }
	}

    $comune_id_partita = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$row['CODICE_CATASTALE_COMUNE']."'");

    $partita = new partita( null , $row['CODICE_CATASTALE_COMUNE'] );

    $partita->CC = $row['CODICE_CATASTALE_COMUNE'];
    $partita->Comune_ID = $comune_id_partita + 1;
    $partita->Ruolo_ID = $id_ruolo;
    $partita->Anno_Riferimento = $row['ANNO_RIFERIMENTO'];
    $partita->Tipo = $tipoRiscossione;
    $partita->Sottotipo = $sottotipoRiscossione;
    $partita->Utente_ID = $id_utente;

    $control_partita = $partita->Insert();
    if($control_partita===false)
    {
        mysql_query('ROLLBACK');
        $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DELLA PARTITA";
        $a_scarti[] = $row;
        continue;
    }
    else
    {
        $partita_id = mysql_insert_id();
    }

    $saltaRecord = 0;
    for($j=0;$j<6;$j++)
    {
        $codiceTributo = "";
        $importoTributo = 0;
        switch($j){
            case 0:
                $importoTributo = $row['IMPORTO'];
                $codiceTributo = "9990";
                break;
            case 1:
                $importoTributo = $row['SPESE'];
                $codiceTributo = "9999";
                break;
            case 2:
                $importoTributo = $row['SANZIONE'];
                $codiceTributo = "9992";
                break;
            case 3:
                $importoTributo = $row['INTERESSI'];
                $codiceTributo = "9994";
                break;
            case 4:
                $importoTributo = $row['ADDIZIONALE'];
                $codiceTributo = "9993";
                break;
            case 5:
                $importoTributo = $row['PAGAMENTO'];
                $codiceTributo = "6666";
                break;
            default:
                continue;
                break;
        }

        if($importoTributo==0)
            continue;

        $tributo = new tributo(null, $row['CODICE_CATASTALE_COMUNE']);

        $tributo->CC = $row['CODICE_CATASTALE_COMUNE'];
        $tributo->Partita_ID = $partita_id;
        $tributo->Info_Cartella = $row['INFORMAZIONI_CARTELLA'];
        $tributo->Anno_Tributo = $row['ANNO_RIFERIMENTO'];
        $tributo->Codice_Tributo = $codiceTributo;
        $tributo->Imposta = $importoTributo;
        $tributo->Data_Decorrenza_Interessi = $dataDecorrenza;
        $tributo->Tipo_Info = $tipoInfoTributo;
        $tributo->Titolo_Entrata = "";

        $control_tributo = $tributo->Insert();
        if($control_tributo===false)
        {
            mysql_query('ROLLBACK');
            $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DEL CODICE TRIBUTO DELLA PARTITA";
            $a_scarti[] = $row;
            $saltaRecord = 1;
            break;
        }
    }

    if($saltaRecord==1){
        mysql_query('ROLLBACK');
        continue;
    }
    else{
        mysql_query('COMMIT');
    }
}

echo "<script>fine();</script>";

flush();
ob_flush();
flush();
ob_flush();
flush();
ob_flush();
flush();
ob_flush();

if(count($a_scarti)>0){
    $objPHPExcelScarti = new PHPExcel();
    $objPHPExcelScarti->getProperties()
        ->setCreator("Sarida")
        ->setLastModifiedBy($_SESSION['username'])
        ->setTitle("Scarti importazione Gitco")
        ->setSubject("Scarti")
        ->setDescription("Record scartati a causa di errori di compilazione");
    $objPHPExcelScarti->setActiveSheetIndex(0);

    $col = 0;
    foreach(array_keys($a_scarti[0]) as $key) {
        $objPHPExcelScarti->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $key);
        $col++;
    }

    $row = 2; // 1-based index
    for($i=0;$i<count($a_scarti);$i++) {
        $col = 0;
        foreach($a_scarti[$i] as $key=>$value) {
            $objPHPExcelScarti->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
            $col++;
        }
        $row++;
    }


    $writer = PHPExcel_IOFactory::createWriter($objPHPExcelScarti, 'Excel5');

    $writer->save($nomeFile);

    if(is_file($nomeFile)){
        echo "<script>mostra_file();</script>";
    }
    else{
        alert("Nessuno scarto effettuato!");
    }
}




?>
    </table>
</td>
</tr>
</table>
</body>
</html>