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

//alertAllGlobalVariables();
//return;

$c = get_var('c');
$a = get_var('a');

//$tipo = get_var('tipo');  //  STAMPA o ELENCO
$tipo = "";

$questaPagina = "pagina_stampa_fatture.php";

$autorizzazione = get_var('aut_tipo');

$comune = new ente_gestito($c);

$nome_comune = ($comune->Nome==NULL?"":$comune->Nome." [".$c."]");
$nome_user = "Operatore: " . $_SESSION['username'];

/*$serieOption = "";
$queryPagamenti = "SELECT DISTINCT Pag_Registro FROM targhe_estere_pagamenti ";
$queryPagamenti .= " WHERE Pag_Comune_CC = '$c' AND ";
$queryPagamenti .= " Pag_Notifica != 0 ";
$queryPagamenti .= " ORDER BY Pag_Registro ";
$resPagam = esegui_query($queryPagamenti);
while ($rigaPagam = risultati_query($resPagam))
{
	$serieOption .= "<option value='" . $rigaPagam['Pag_Registro'] . "'>" . $rigaPagam['Pag_Registro'] . "</option>\n";
}*/

$myFattura = new fatture_generali(null);
$optionsFatture = $myFattura->OptionTipiFatture("");
$optionsGestione = $myFattura->OptionTipiGestione("");

$queryComuni = "SELECT DISTINCT Fat_Comune, Indirizzo2 FROM fatture_generali, fatture_dati_sedi_comuni ";
$queryComuni .= " WHERE Fat_Comune = CC ";
$queryComuni .= " ORDER BY Fat_Comune ";
$resComuni = esegui_query($queryComuni);
$optionsComuni = "<option value=''></option>\n";
while ($rigaCom = risultati_query($resComuni))
{
	$optionsComuni .= "<option value='" . $rigaCom['Fat_Comune'] . "'>" . $rigaCom['Indirizzo2'] . "</option>\n";
}

$dataOdierna = date("d/m/Y");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Pagina Elenco Fatture</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:stampa11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
    
<script type="text/javascript" language="Javascript">

function cambiocomune()
{
	var strLink = "<?=$questaPagina?>";
	strLink += "?c=" + $("#sceglicomune").val();
	strLink += "&a=" + "<?php echo $a?>";
	strLink += "&tipo=" + "<?php echo $tipo?>";

	location.href = strLink;
}

function checkData (testo)
{
	var nan;
	if (testo == "") return testo;
		
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

function ctrlData(campo)
{
	var area = $("#"+campo);
	var ret = checkData(area.val());
	if (ret != "0") 
		area.val(ret);
	else
		area.val("");
}

function ctrlAnno (id)
{
	var campo = $("#" + id).val();
	if (campo.length != 4 && campo.length != 0)
	{
		alert ("Inserire un anno valido nel campo '" + id + "'");
		return false;
	}
	if (isNaN(campo))
	{
		alert ("Inserire un anno valido nel campo '" + id + "'");
		return false;
	}
	return true;
}

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

function annulla ()
{
	var stringaLink = "<?=$questaPagina?>";
	stringaLink += "?c=" + "<?php echo $c?>";
	stringaLink += "&a=" + "<?php echo $a?>";
	stringaLink += "&tipo=" + "<?php echo $tipo?>";
	location.href = stringaLink;
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

}

//F9
function ricerca_F9()
{
	
}

//F10
function stampa_F10()
{
	StampaPagina();
}

function StampaPagina()
{
	var tipofattura = $("#tipofattura").val();
	switch (tipofattura)
	{
	case "CDS": $("#stampa_form").attr("action", "elenco_fatture_cds_tari_ici_imu.php"); break;
	case "PUB": $("#stampa_form").attr("action", "elenco_fatture_pub_tosap.php"); break;
	case "TOSAP": $("#stampa_form").attr("action", "elenco_fatture_pub_tosap.php"); break;
	case "PARK": $("#stampa_form").attr("action", "elenco_fatture_cds_tari_ici_imu.php"); break;
	case "TARI": $("#stampa_form").attr("action", "elenco_fatture_cds_tari_ici_imu.php"); break;
	case "ICI": $("#stampa_form").attr("action", "elenco_fatture_cds_tari_ici_imu.php"); break;
	case "IMU": $("#stampa_form").attr("action", "elenco_fatture_cds_tari_ici_imu.php"); break;
	default: alert ("Scegliere il tipo di fattura"); return; break;
	}
	var tipogestione = $("#tipogestione").val();
	if (tipogestione == "")
	{
		alert ("Scegliere il tipo di gestione");
		return;
	}
	$("#stampa_form").attr("target", "stampa");
	$("#stampa_form").attr("onSubmit", "window.open('', 'stampa', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no');");
	
	if (ctrlAnno("dacompetenza") == false) return false;
	if (ctrlAnno("acompetenza") == false) return false;
	if (ctrlAnno("dabilancio") == false) return false;
	if (ctrlAnno("abilancio") == false) return false;

	if (
		($("#dacompetenza").val() != "" && $("#acompetenza").val() == "") ||
		($("#dacompetenza").val() == "" && $("#acompetenza").val() != "")
		)
	{
		alert ("Entrambi gli anni di competenza vanno inseriti");
		return false;
	}
	if (
		($("#dabilancio").val() != "" && $("#abilancio").val() == "") ||
		($("#dabilancio").val() == "" && $("#abilancio").val() != "")
		)
	{
		alert ("Entrambi gli anni di bilancio vanno inseriti");
		return false;
	}
	
	if (ctrlData("dafatturazione") == false) return false;
	if (ctrlData("afatturazione") == false) return false;
	if (ctrlData("dariscossione") == false) return false;
	if (ctrlData("ariscossione") == false) return false;
	
	if (
		($("#dafatturazione").val() != "" && $("#afatturazione").val() == "") ||
		($("#dafatturazione").val() == "" && $("#afatturazione").val() != "")
		)
	{
		alert ("Entrambi la date di fatturazione vanno inserite");
		return false;
	}
	if (
		($("#dariscossione").val() != "" && $("#ariscossione").val() == "") ||
		($("#dariscossione").val() == "" && $("#ariscossione").val() != "")
		)
	{
		alert ("Entrambi la date di riscossione vanno inserite");
		return false;
	}
	
	$('#stampa_form').submit();
}

$(document).ready(function()
{
	$("#stampa_click").click( StampaPagina );

});

$(function()
{
	$("#dafatturazione").datepicker();
	$("#afatturazione").datepicker();
	$("#dariscossione").datepicker();
	$("#ariscossione").datepicker();
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
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo"></a>
        </td>
		<td class="text_center width11">
          	
        </td>
		<td class="text_center width7">
          	<a href="#" id="stampa_click" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
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

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Stampa Dettaglio Fatture</font></td>
	</tr>
</table>

<br>
	
<form id="stampa_form" name="stampa_form" method="post" target="stampa">
		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">

	<table class="table_interna text_center" border="0">
	<tr class="pheight25">
		<td class="width12 text_left">
			Tipo Fattura:
		</td>
		<td class="width25 text_left">
			<select name="tipofattura" id="tipofattura" class="width90">
				<?php echo $optionsFatture ?>
			</select>
		</td>
		<td class="width15 text_left">
			<select name="tipogestione" id="tipogestione" class="width90">
				<?php echo $optionsGestione ?>
				<option value='ALTRO' >CANONE + AGGIO</option>
			</select>
		</td>
		<td class="width13 text_left">
			
		</td>
		<td class="width10 text_left">
			Comune:
		</td>
		<td class="width25 text_left">
			<select name="sceltacomuni" id="sceltacomuni" class="width90">
				<?php echo $optionsComuni ?>
			</select>
		</td>
	</tr>
	</table>

	<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=6 ><hr></td>
	</tr>
	</table>

	<table class="table_interna text_center" border="0">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="text_center"><font class="titolo font16 under_decor">Selezioni</font></td>
	</tr>
	</table>

	<table class="table_interna text_center" border="0">
	<tr class="pheight25">
		<td class="width20 text_left"></td>
		<td class="width20 text_left">Anno di competenza</td>
		<td class="width5 text_left">da:</td>
		<td class="width20 text_left"><input type="text" class="text_center pwidth80" name="dacompetenza" id="dacompetenza" value="" onchange="ctrlAnno(id)" tabindex=2></td>
		<td class="width5 text_left">a:</td>
		<td class="width30 text_left"><input type="text" class="text_center pwidth80" name="acompetenza" id="acompetenza" value="" onchange="ctrlAnno(id)" tabindex=3></td>
	</tr>
	<tr class="pheight25">
		<td class="text_left"></td>
		<td class="text_left">Anno di bilancio</td>
		<td class="text_left">da:</td>
		<td class="text_left"><input type="text" class="text_center pwidth80" name="dabilancio" id="dabilancio" value="" onchange="ctrlAnno(id)" tabindex=4></td>
		<td class="text_left">a:</td>
		<td class="text_left"><input type="text" class="text_center pwidth80" name="abilancio" id="abilancio" value="" onchange="ctrlAnno(id)" tabindex=5></td>
	</tr>
	<tr class="pheight25">
		<td class="text_left"></td>
		<td class="text_left">Data fatturazione</td>
		<td class="text_left">da:</td>
		<td class="text_left"><input type="text" class="text_center picker pwidth80" name="dafatturazione" id="dafatturazione" value="" onchange="ctrlData(id)" tabindex=6></td>
		<td class="text_left">a:</td>
		<td class="text_left"><input type="text" class="text_center picker pwidth80" name="afatturazione" id="afatturazione" value="" onchange="ctrlData(id)" tabindex=7></td>
	</tr>
	<tr class="pheight25">
		<td class="text_left"></td>
		<td class="text_left">Data riscossione</td>
		<td class="text_left">da:</td>
		<td class="text_left"><input type="text" class="text_center picker pwidth80" name="dariscossione" id="dariscossione" value="" onchange="ctrlData(id)" tabindex=8></td>
		<td class="text_left">a:</td>
		<td class="text_left"><input type="text" class="text_center picker pwidth80" name="ariscossione" id="ariscossione" value="" onchange="ctrlData(id)" tabindex=9></td>
	</tr>
	</table>

	<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=6 ><hr></td>
	</tr>
	</table>
		
	<br>

	<table class="table_interna text_center" border="0">
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="text_center"><font class="titolo font16 under_decor">Ordinamento</font></td>
		<td class="text_center"><font class="titolo font16 under_decor">File di uscita</font></td>
	</tr>
	<tr class="pheight25">
		<td class="width50">
			<select name="ordinestampa">
				<option value="NUMERO">Numero Fattura</option>
				<option value="COMUNE">Comune</option>
			</select>
		</td>
		<td>
			<select name="tipofile">
				<option value="PDF">PDF</option>
				<option value="CSV">CSV</option>
			</select>
		</td>
	</tr>
	</table>
	
</form>

</td>
</tr>
</table>

</body>
</html>