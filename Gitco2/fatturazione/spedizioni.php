<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/comuni.php";
include CLASSI . "/fatture.php";

if (!session_id()) session_start();

if ($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

/*if ($_SESSION['CC_User'] == "***+")
{
	alertAllGlobalVariables();
	return;
}*/

$c = get_var('c');
$a = get_var('a');

$autorizzazione = get_var('aut_tipo');

$paginanumero = get_var('paginanumero');
$ordinamento = get_var('ordinamento');
$direzione = get_var('direzione');
$salvataggio = get_var('salvataggio');

$nome_user = "Operatore: " . $_SESSION['username'];

$myInvio = new fatture_invii(null);
$myInvio->AutoGenerazioneTable();
$myEmail = new fatture_email(null);
$myEmail->AutoGenerazioneTable();

$identificativosdi = get_var('identificativosdi');  //  è array
$datasdi = get_var('datasdi');  //  è array
$idfattura = get_var('idfattura');  //  è array

$alertSalva = "";


$gestione = "";
$fattura = "";
$comune = "";
$annofattura = "";
$annobilancio = "";
$annocompetenza = "";
$competenza = "";


if(isset($_REQUEST['FiltroGestione'])) $gestione = get_var('FiltroGestione');
if(isset($_REQUEST['FiltroFattura'])) $fattura = get_var('FiltroFattura');
if(isset($_REQUEST['FiltroComune'])) $comune = get_var('FiltroComune');
if(isset($_REQUEST['FiltroAnnoFattura'])) $annofattura = get_var('FiltroAnnoFattura');
if(isset($_REQUEST['FiltroAnnoBilancio'])) $annobilancio = get_var('FiltroAnnoBilancio');
if(isset($_REQUEST['FiltroAnnoCompetenza'])) $annocompetenza = get_var('FiltroAnnoCompetenza');
if(isset($_REQUEST['FiltroCompetenza'])) $competenza = get_var('FiltroCompetenza');




$FiltroComune = "";
$FiltroGestione = "";
$FiltroFattura = "";
$FiltroAnnoFattura = "";
$FiltroAnnoBilancio = "";
$FiltroAnnoCompetenza = "";
$FiltroCompetenza = "";

$FiltroCompetenza = 'Competenza: <select name="FiltroCompetenza" style="font-size:10px;">';
$FiltroCompetenza .= '<option value="">';
$FiltroCompetenza .= '<option value=0';
if($competenza == '0') $FiltroCompetenza .= ' SELECTED ';
$FiltroCompetenza .= '>ORDINARIA';
$FiltroCompetenza .= '<option value=1';
if($competenza == '1') $FiltroCompetenza .= ' SELECTED ';
$FiltroCompetenza .= '>COATTIVA';
$FiltroCompetenza .= '</select>';


$sql = "SELECT DISTINCT Fat_Anno FROM fatture_generali ORDER BY Fat_Anno DESC ;";
$rs = mysql_query($sql);


$FiltroAnnoFattura = 'Anno fattura: <select name="FiltroAnnoFattura" style="font-size:10px;">';
$FiltroAnnoFattura .= '<option value="">';
while ($row = mysql_fetch_array($rs)){
	$FiltroAnnoFattura .= '<option value="'.$row['Fat_Anno'].'"';
	if($annofattura == $row['Fat_Anno']) $FiltroAnnoFattura .= ' SELECTED ';
	$FiltroAnnoFattura .= '>'.$row['Fat_Anno'];
}
$FiltroAnnoFattura .= "</select>";



$sql = "SELECT DISTINCT Fat_Anno_Bilancio FROM fatture_generali ORDER BY Fat_Anno_Bilancio DESC ;";
$rs = mysql_query($sql);

$FiltroAnnoBilancio = 'Anno bilancio: <select name="FiltroAnnoBilancio" style="font-size:10px;">';
$FiltroAnnoBilancio .= '<option value="">';
while ($row = mysql_fetch_array($rs)){
	$FiltroAnnoBilancio .= '<option value="'.$row['Fat_Anno_Bilancio'].'"';
	if($annobilancio == $row['Fat_Anno_Bilancio']) $FiltroAnnoBilancio .= ' SELECTED ';
	$FiltroAnnoBilancio .= '>'.$row['Fat_Anno_Bilancio'];
}
$FiltroAnnoBilancio .= "</select>";



$sql = "SELECT DISTINCT Fat_Anno_Competenza FROM fatture_generali ORDER BY Fat_Anno_Competenza DESC ;";
$rs = mysql_query($sql);

$FiltroAnnoCompetenza = 'Anno competenza: <select name="FiltroAnnoCompetenza" style="font-size:10px;">';
$FiltroAnnoCompetenza .= '<option value="">';
while ($row = mysql_fetch_array($rs)){
	$FiltroAnnoCompetenza .= '<option value="'.$row['Fat_Anno_Competenza'].'"';
	if($annocompetenza == $row['Fat_Anno_Competenza']) $FiltroAnnoCompetenza .= ' SELECTED ';
	$FiltroAnnoCompetenza .= '>'.$row['Fat_Anno_Competenza'];
}
$FiltroAnnoCompetenza .= "</select>";



$sql = "SELECT DISTINCT CL.Com_Codice_Catastale, CL.Com_Nome FROM comuni_lista CL INNER JOIN fatture_generali FG ON FG.Fat_Comune=CL.Com_Codice_Catastale ORDER BY CL.Com_Nome;";
$rs = mysql_query($sql);

$FiltroComune = 'Comune: <select name="FiltroComune" style="font-size:10px;">';
$FiltroComune .= '<option value="">';
while ($row = mysql_fetch_array($rs)){
	$FiltroComune .= '<option value="'.$row['Com_Codice_Catastale'].'"';
	if($comune == $row['Com_Codice_Catastale']) $FiltroComune .= ' SELECTED ';
	$FiltroComune .= '>'.$row['Com_Nome'];
}
$FiltroComune .= "</select>";


$FiltroGestione = 'Gestione: <select name="FiltroGestione" style="font-size:10px;">';
$FiltroGestione .= '<option value="">';
$FiltroGestione .= '<option value="CDS"';
if($gestione == 'CDS') $FiltroGestione .= ' SELECTED ';
$FiltroGestione .='>CDS';
$FiltroGestione .= '<option value="IMU"';
if($gestione == 'IMU') $FiltroGestione .= ' SELECTED ';
$FiltroGestione .= '>IMU';
$FiltroGestione .= '<option value="PARK"';
if($gestione == 'PARK') $FiltroGestione .= ' SELECTED ';
$FiltroGestione .= '>PARCHEGGI';
$FiltroGestione .= '<option value="PUB"';
if($gestione == 'PUB') $FiltroGestione .= ' SELECTED ';
$FiltroGestione .= '>PUBBLICITA';
$FiltroGestione .= '<option value="TOSAP"';
if($gestione == 'TOSAP') $FiltroGestione .= ' SELECTED ';
$FiltroGestione .= '>TOSAP';

$FiltroGestione .= '</select>';



$FiltroFattura = 'Fattura: <input type="text" name="FiltroFattura" value="'.$fattura.'" style="font-size:10px; width:50px;" />';


if (($identificativosdi != "" || $datasdi != "") && $salvataggio == "SI")
{
	//alertAllGlobalVariables();
	for ($zzz = 0; $zzz < count($identificativosdi); $zzz++)
	{
		$idInvio = $myInvio->CercaInvioDaFattura($idfattura[$zzz]);
		$myInvio = new fatture_invii($idInvio);
		$myInvio->Fattura_ID = $idfattura[$zzz];
		$myInvio->Identificativo_SDI = $identificativosdi[$zzz];
		$myInvio->Data_Invio = to_mysql_date($datasdi[$zzz]);
		if ($identificativosdi[$zzz] == "" && $datasdi[$zzz] == "") $myInvio->InsertUpdateInvio("UPDATE");
		else $myInvio->InsertUpdateInvio();
		$alertSalva = "<script>alert('Aggiornamento avvenuto con successo');</script>";
	}
}

$questaPagina = "spedizioni.php";

$stileriga = "sfondo_grigio";

$queryTutteFatture = "SELECT fatture_generali.ID as IDFG FROM fatture_generali LEFT JOIN fatture_invii ";
$queryTutteFatture .= " on Fattura_ID = fatture_generali.ID WHERE 1=1 ";

if(strlen(trim($gestione))>0){
	$queryTutteFatture .= "AND Fat_Tributo='$gestione' ";
}
if(strlen(trim($fattura))>0){
	$queryTutteFatture .= "AND Fat_Numero LIKE '%$fattura%' ";

}
if(strlen(trim($comune))>0){
	$queryTutteFatture .= "AND Fat_Comune='$comune' ";
}
if(strlen(trim($annofattura))>0){
	$queryTutteFatture .= "AND Fat_Anno='$annofattura' ";
}
if(strlen(trim($annobilancio))>0){
	$queryTutteFatture .= "AND Fat_Anno_Bilancio='$annobilancio' ";
}
if(strlen(trim($annocompetenza))>0){
	$queryTutteFatture .= "AND Fat_Anno_Competenza='$annocompetenza' ";
}
if(strlen(trim($competenza))>0){
	$queryTutteFatture .= "AND Fat_Competenza=$competenza ";
}

$rossoNum = $rossoCom = $rossoData = $rossoSDI = $rossoInvio = "";
switch ($ordinamento)
{
	case 1: $metodoOrd = " Fat_Numero "; $rossoNum = " color_red "; break;
	case 2: $metodoOrd = " Fat_Comune $direzione, Fat_Numero "; $rossoCom = " color_red "; break;
	case 3: $metodoOrd = " Fat_Comune $direzione, Fat_Numero "; $rossoCom = " color_red "; break;
	case 4: $metodoOrd = " Fat_Data $direzione, Fat_Numero "; $rossoData = " color_red "; break;
	case 5: $metodoOrd = " Identificativo_SDI "; $rossoSDI = " color_red "; break;
	case 6: $metodoOrd = " Data_Invio $direzione, Fat_Numero "; $rossoInvio = " color_red "; break;
	case 7: $metodoOrd = " Esito "; break;
	default: $metodoOrd = " Fat_Numero "; $rossoNum = " color_red "; break;
}
$queryTutteFatture .= " ORDER BY " . $metodoOrd . " " . $direzione;
//echo "<br>" . $queryTutteFatture;
$resTutteFatture = esegui_query($queryTutteFatture);
$numTutteFatture = numero_risposte_query($resTutteFatture);

$maxRighePagina = 15;
$totPagine = $numTutteFatture / $maxRighePagina;
$explodePunti = explode (".", $totPagine);
if (count($explodePunti) == 1) $totPagine = $totPagine;
else $totPagine = $explodePunti[0] + 1;

if ($paginanumero == "") $paginanumero = 1;
if ($paginanumero == 1)
{
	$imgPrecedente = "/gitco2/immagini/FrecciaSgrey.png";
}
else
{
	$imgPrecedente = "/gitco2/immagini/FrecciaS.png";
}
if ($paginanumero >= $totPagine)
{
	$paginanumero = $totPagine;
	$imgSuccessiva = "/gitco2/immagini/FrecciaDgrey.png";
}
else
{
	$imgSuccessiva = "/gitco2/immagini/FrecciaD.png";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Spedizioni</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
    
<script type="text/javascript" language="Javascript">

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{
	SalvataggioDato();
}

//F4
function cancella_form() 
{     

}

// F5 
function annulla ()
{
	var stringaLink = "<?=$questaPagina?>?";
	stringaLink += "c=" + "<?php echo $c?>";
	stringaLink += "&a=" + "<?php echo $a?>";
	location.href = stringaLink;
}


//F6
function nuovo_F6()
{
	
}

//F7-F8
function cambia_pag (value)
{
	var paginaattuale = <?=$paginanumero?>;
	if (value == "prec")
	{
		paginaattuale --;
		if ("<?=$imgPrecedente?>" == "/gitco2/immagini/FrecciaSgrey.png") return;
	}
	else if (value == "suc")
	{
		paginaattuale ++;
		if ("<?=$imgSuccessiva?>" == "/gitco2/immagini/FrecciaDgrey.png") return;
	}
	$("[name=paginanumero]").val(paginaattuale.toString());
	$("[name=salvataggio]").val("");
	$('#invii_form').submit();
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
	
}

//F10
function stampa_F10()
{
	return true;
}

function cambia_pagina ()
{
	var campo = $("[name=paginanumero]").val();
	if (campo > <?=$totPagine?>) campo = <?=$totPagine?>;
	$("[name=paginanumero]").val(campo);
	$("[name=salvataggio]").val("");
	$('#invii_form').submit();
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

function ctrlCampoData(campo)
{
	var area = $("#"+campo);
	var ret = checkData(area.val());
	
	if (ret != "0") area.val(ret);
	else area.val("");
}

function checkData (testo)
{
	var nan;
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

function Ordiniamo (value)
{
	var direz = "<?php echo $direzione?>";
	var ordinamento = "<?php echo $ordinamento?>";
	if (ordinamento == value)
	{
		if (direz == "") direz = "ASC";
		else if (direz == "ASC") direz = "DESC";
		else if (direz == "DESC") direz = "ASC";
	}
	else direz = "ASC";
	$("[name=ordinamento]").val(value);
	$("[name=direzione]").val(direz);
	$("[name=salvataggio]").val("");
	$('#invii_form').submit();
}

$(document).ready(function()
{
	var creacampo = "";
	for (var ppp = 0; ppp < <?=$maxRighePagina?>; ppp++)
	{
		creacampo = "datasdi_" + ppp;
		if ( $("#"+creacampo).val() == undefined ) break;
		else $("#"+creacampo).datepicker();
	}

	$("#submit_click").click
			( 
					function SalvaPag ()
					{
						SalvataggioDato();
					}
			);
});

function SalvataggioDato ()
{
	if ($("#submit_click").attr("src") == "/gitco2/immagini/Save-iconF3grey.png") return false;

	var uscita = true;
	
	var salvacampo = "";
	for (var ppp = 0; ppp < <?=$maxRighePagina?>; ppp++)
	{
		salvacampo = "identificativosdi_" + ppp;
		if ( $("#"+salvacampo).val() == undefined ) break;
		else uscita = checkdoppio(ppp, false);
		if (uscita == false) break;
	}

	if (uscita == false)
	{
		//alert ("Il campo 'identificativo' e il campo 'data' devono essere compilati.");
	}
	else
	{
		$("[name=salvataggio]").val("SI");
		$('#invii_form').submit();
	}
}


function checkdoppio (numero, stocompilando)
{
	var identcampo = "";
	var datacampo = "";
	var uscita = true;
	
	identcampo = "identificativosdi_" + numero;
	datacampo = "datasdi_" + numero;
	if ( /*$("#"+identcampo).val() != "" &&*/ $("#"+datacampo).val() != "" )
	{
		$("#submit_click").attr("src", "/gitco2/immagini/Save-iconF3.png");
	}
	else if ( $("#"+identcampo).val() == "" && $("#"+datacampo).val() == "" ) {}
	else if ( $("#"+identcampo).val() == "" && $("#"+datacampo).val() != "" ) {}
	else if ( stocompilando == true && $("#"+identcampo).val() != "" && $("#"+datacampo).val() == "" ) {}
	else
	{
		uscita = false;
		alert ("Il campo 'identificativo' e il campo 'data' devono essere compilati.");
	}
	return uscita;
}

function CheckCaratteri(names)
{
	var esitoCheck = true;
	//var name = "[name="+names+"]";
	var idcampo = $("[name=" + names + "]");
	//alert ("[name=" + names + "]");
	var testoId = idcampo.val();
	/*alert (testoId);
	if (testoId == "")
	{
		testoId = idcampo.val();  //  in IE si usa TEXT, in Firefox si usa VAL...
	}
	alert (testoId);*/

	var lungTesto = testoId.length;
	var testoNelCampo = "";
	for (var i = 0; i < lungTesto; i++)
	{
		var carattere = CtrlLettereTesto(testoId.charAt(i), false);
		if (carattere == "") esitoCheck = false;
		else testoNelCampo += carattere;
	}
	idcampo.val(testoNelCampo);  //  in IE si usa TEXT, in Firefox si usa VAL...
	//idcampo.text(testoNelCampo);
	return esitoCheck;
}

</script>
    
</head>

<body class="sfondo_new_gitco">

<?php echo $alertSalva; ?>

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
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
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
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick="cambia_pag('prec');"><img src="<?php echo $imgPrecedente ?>" width=42px height=42px border="0" alt="Pagina precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick="cambia_pag('suc');"><img src="<?php echo $imgSuccessiva ?>" width=42px height=42px border="0" alt="Pagina successivo"></a>
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
		
	<form id="invii_form" name="invii_form" action="<?=$questaPagina?>" method="post">
		
	<input type="hidden" name="c" value="<?php echo $c ?>">
	<input type="hidden" name="a" value="<?php echo $a ?>">
	<input type="hidden" name="ordinamento" value="<?php echo $ordinamento ?>">
	<input type="hidden" name="direzione" value="">
	<input type="hidden" name="salvataggio" value="">
	
		
 	<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
		<tr class="pheight50">
			<td class="width15 text_center">
			</td>
			<td class="width70 text_center">
				<font class="titolo font18">INVII FATTURE ELETTRONICHE</font>
			</td>
			<td class="width15 text_center">
				Pagina
				<input type="text" class="width15 text_right" name="paginanumero" value="<?=$paginanumero?>" onchange="cambia_pagina();"> / <?=$totPagine?>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text_center">
				<?php echo $FiltroAnnoFattura." ".$FiltroAnnoBilancio." ".$FiltroAnnoCompetenza." ".$FiltroFattura ?>
			</td>
			<td rowspan="2">
				<input type="submit" value="FILTRA">
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text_center">
				<?php echo $FiltroCompetenza." ".$FiltroComune." ".$FiltroGestione ?>
			</td>
		</tr>
	</table>
	
	<table class='table_interna text_center' border='0'>
	<tr class="pheight30 sfondo_new_gitco">
		<td class="width20 text_center">
			<font class="font_bold fontlink <?php echo $rossoNum ?>" onclick="Ordiniamo(1);">Numero Fattura</font> 
		</td>
		<td class="width5 text_center">
			<font class="font_bold fontlink <?php echo $rossoCom ?>" onclick="Ordiniamo(3);">CC</font>
		</td>
		<td class="width15 text_center">
			<font class="font_bold fontlink <?php echo $rossoCom ?>" onclick="Ordiniamo(3);">Comune</font>
		</td>
		<td class="width15 text_center">
			<font class="font_bold fontlink <?php echo $rossoData ?>" onclick="Ordiniamo(4);">Data Fattura</font>
		</td>
		<td class="width18 text_center">
			<font class="font_bold fontlink <?php echo $rossoSDI ?>" onclick="Ordiniamo(5);">Identificativo SDI</font>
		</td>
		<td class="width5 text_center">
			<font class="font_bold fontlink <?php echo $rossoInvio ?>" onclick="Ordiniamo(6);">Data Invio</font>
		</td>
		<td class="width20 text_center">
			<font class="font_bold">Esito</font>
		</td>
		<td class="width5 text_center">
			
		</td>
	</tr>
	
	<?php 
	
	$partenza = ($paginanumero - 1) * $maxRighePagina;
	/*if ($paginanumero == 1)*/ $ultimo = $maxRighePagina - 1;
	//else $ultimo = $maxRighePagina;
	$contoPerPagina = 0;
	$arrayFatture = array();
	while ($rigaFattura = risultati_query($resTutteFatture))
	{
		if ($contoPerPagina >= $partenza)
		{
			$arrayFatture[] = $rigaFattura['IDFG'];
		}
		if (count($arrayFatture) > $ultimo) break;
		$contoPerPagina++;
	}
	
	$myDatiComune = new fatture_dati_sedi_comuni(null);
	for ($kkk = 0; $kkk < count($arrayFatture); $kkk++)
	{
		if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
		else $stileriga = "sfondo_grigio";
		
		$myFattura = new fatture_generali($arrayFatture[$kkk]);
		
		$primaLettera = strtoupper(substr($myFattura->Fat_Tipo, 0, 1));
		
		$idInvio = $myInvio->CercaInvioDaFattura($myFattura->ID);
		$myInvio = new fatture_invii($idInvio);

		$iddd = $myDatiComune->CercaDatiComune($myFattura->Fat_Comune);
		$myDatiComune = new fatture_dati_sedi_comuni($iddd);
		
		$arrayEmail = $myEmail->CercaListaEmailDaSDI($myInvio->Identificativo_SDI);
		$esitoEmail = $arrayEmail[0];
		$iconaEmail = $arrayEmail[1];
		//$statoEmail = $arrayEmail[2];
		
		?>
		
		<tr class="pheight25 <?=$stileriga?>">
			<td class="text_center">
				<input type="hidden" name="idfattura[<?=$kkk?>]" value="<?=$myFattura->ID?>">
				<?php echo $myFattura->Fat_Numero?> (<b><?php echo $primaLettera?></b>)
			</td>
			<td class="text_center font11">
				<?php echo $myFattura->Fat_Comune?>
			</td>
			<td class="text_center">
				<?php echo $myDatiComune->Indirizzo2?>
			</td>
			<td class="text_center">
				<?php echo from_mysql_date($myFattura->Fat_Data)?>
			</td>
			<td class="text_center">
				<input type="text" class="text_left" name="identificativosdi[<?=$kkk?>]" id="identificativosdi_<?=$kkk?>" value="<?=$myInvio->Identificativo_SDI?>" onchange="checkdoppio(<?=$kkk?>, true);">
			</td>
			<td class="text_center">
				<input type="text" class="text_center" name="datasdi[<?=$kkk?>]" id="datasdi_<?=$kkk?>" value="<?=from_mysql_date($myInvio->Data_Invio)?>" onchange="ctrlCampoData(id);checkdoppio(<?=$kkk?>, false);">
			</td>
			<td class="text_center">
				<select class="sfondo_grigio width90">
					<?php echo $esitoEmail ?>
				</select>
			</td>
			<td class="text_center">
				<img src="<?php echo $iconaEmail ?>" class="pwidth20 pwidth20">
			</td>
		</tr>
		
		<?php 
	}
	
	?>
	
	</table>
			
</form>

</td>
</tr>
</table>

</body>
</html>