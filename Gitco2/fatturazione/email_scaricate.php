<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/comuni.php";
include CLASSI . "/fatture.php";
include CLASSI . "/parametri.php";

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

$questaPagina = "email_scaricate.php";

$stileriga = "sfondo_grigio";

$queryTutteEmail = "SELECT fatture_email.ID as IDFE FROM fatture_email ";
$queryTutteEmail .= " LEFT JOIN fatture_invii ";
$queryTutteEmail .= " on fatture_email.Identificativo_SDI = fatture_invii.Identificativo_SDI ";
$queryTutteEmail .= " LEFT JOIN fatture_generali ";
$queryTutteEmail .= " on fatture_generali.ID = Fattura_ID ";

$rossoNum = $rossoCom = $rossoData = $rossoSDI = $rossoTipo = "";
switch ($ordinamento)
{
	case 1: $metodoOrd = " Fat_Numero "; $rossoNum = " color_red "; break;
	case 3: $metodoOrd = " Fat_Comune $direzione, Fat_Numero "; $rossoCom = " color_red "; break;
	case 4: $metodoOrd = " Data_Ricezione $direzione, Fat_Numero "; $rossoData = " color_red "; break;
	case 5: $metodoOrd = " fatture_email.Identificativo_SDI "; $rossoSDI = " color_red "; break;
	case 6: $metodoOrd = " Tipo_Messaggio "; $rossoTipo = " color_red "; break;
	default: $metodoOrd = " Fat_Numero "; $rossoNum = " color_red "; break;
}
$queryTutteEmail .= " ORDER BY " . $metodoOrd . " " . $direzione;
//echo "<br>" . $queryTutteEmail;
$resTutteEmail = esegui_query($queryTutteEmail);
$numTutteEmail = numero_risposte_query($resTutteEmail);

$maxRighePagina = 15;
$totPagine = $numTutteEmail / $maxRighePagina;
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
<title>Email</title>

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
	$('#email_form').submit();
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
	$('#email_form').submit();
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
	$('#email_form').submit();
}

$(document).ready(function()
{
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



function apri_file(value, value2)
{
	link="/gitco2/coattiva/modali/force-download.php?file="+value+"&filename="+value2;
	
	window.open(link);
}

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
		
	<form id="email_form" name="email_form" action="<?=$questaPagina?>" method="post">
		
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
			<font class="titolo font18">EMAIL SCARICATE DA PEC</font>
		</td>
    	<td class="width15 text_center">
    		Pagina
    		<input type="text" class="width30 text_right" name="paginanumero" value="<?=$paginanumero?>" onchange="cambia_pagina();"> / <?=$totPagine?>
        </td>
	</tr>
	</table>
	
	<table class='table_interna text_center' border='0'>
	<tr class="pheight30 sfondo_new_gitco">
		<td class="width5 text_center">
			
		</td>
		<td class="width15 text_center">
			<font class="font_bold fontlink <?php echo $rossoNum ?>" onclick="Ordiniamo(1);">Numero Fattura</font>
		</td>
		<td class="width5 text_center">
			<font class="font_bold fontlink <?php echo $rossoCom ?>" onclick="Ordiniamo(3);">CC</font>
		</td>
		<td class="width15 text_center">
			<b>Esito</b>
		</td>
		<td class="width20 text_center">
			<font class="font_bold fontlink <?php echo $rossoData ?>" onclick="Ordiniamo(4);">Data Ric.</font>
		</td>
		<td class="width15 text_center">
			<font class="font_bold fontlink <?php echo $rossoSDI ?>" onclick="Ordiniamo(5);">Identificativo SDI</font>
		</td>
		<td class="width20 text_center">
			<font class="font_bold fontlink <?php echo $rossoTipo ?>" onclick="Ordiniamo(6);">Tipo</font>
		</td>
		<td class="width10 text_center">
			<b>Email</b>
		</td>
	</tr>
	
	<?php 
	
	$partenza = ($paginanumero - 1) * $maxRighePagina;
	/*if ($paginanumero == 1)*/ $ultimo = $maxRighePagina - 1;
	//else $ultimo = $maxRighePagina;
	$contoPerPagina = 0;
	$emailSalvate = array();
	while ($rigaEmail = risultati_query($resTutteEmail))
	{
		if ($contoPerPagina >= $partenza)
		{
			$emailSalvate[] = $rigaEmail['IDFE'];
		}
		if (count($emailSalvate) > $ultimo) break;
		$contoPerPagina++;
	}
	
	$destinazioneEmail = crea_dir($PathCompletoFatture . "/PEC/");
	for ($kkk = 0; $kkk < count($emailSalvate); $kkk++)
	{
		if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
		else $stileriga = "sfondo_grigio";
		
		set_time_limit(300);
		
		$salvaDbEmail = "";
		$salvaEmail = "";
		
		$num = $kkk + 1 + $partenza;
		
		$myEmail = new fatture_email($emailSalvate[$kkk]);
		
		$dataMessaggio = from_mysql_date($myEmail->Data_Ricezione);
		$identificativoID = $myEmail->Identificativo_SDI;
		$tipoMessaggio = $myEmail->Tipo_Messaggio;
		
		$esitoFattura = $myEmail->Esito;
		$numFattura = "--"; //  $myEmail->Fattura_ID;
		
		$arrayMyStato = $myEmail->StatoEmail();
		$titleEmail = $arrayMyStato['STATO_EMAIL'];
		$iconaEmail = $arrayMyStato['ICONA_EMAIL'];
		
		if ($identificativoID != "" && $tipoMessaggio != "")
		{
			$myInvio = new fatture_invii(null);
			$invioID = $myInvio->CercaInvioDaSDI($identificativoID);
			$myInvio = new fatture_invii($invioID);
			$myFattura = new fatture_generali($myInvio->Fattura_ID);
			$numFattura = $myFattura->Fat_Numero;
			$scrittaEsito = $esitoFattura;
				
			$nome_file = $myEmail->Nome_File_Email;
			$nome_completo_file = $destinazioneEmail . $nome_file;
			if (file_exists($nome_completo_file))
			{
				$tempImg = "<img href='#' src='/gitco2/immagini/email.gif' class='pwidth15 pheight15' onclick=\"apri_file('$nome_completo_file','$nome_file');\">";
			}
			else 
			{
				$tempImg = "<img href='#' src='/gitco2/immagini/rosso.png' class='pwidth15 pheight15'>";
			}
			
			if ($myFattura->ID != "")
			{
				$htmlNumFattura = $myFattura->Fat_Numero;
				$htmlComune = $myFattura->Fat_Comune;
				$htmlEsito = $esitoFattura;
				//$htmlData = from_mysql_date($myFattura->Fat_Data);
				$htmlData = from_mysql_date($dataMessaggio);
				$htmlIdentif = $identificativoID;
				$htmlTipoMsg = $tipoMessaggio;
				$htmlImg = $tempImg;
			}
			else
			{
				$htmlNumFattura = "<font color='red'>$numFattura</font>";
				$htmlComune = "";
				$htmlEsito = $esitoFattura;
				$htmlData = from_mysql_date($dataMessaggio);
				$htmlIdentif = $identificativoID;
				$htmlTipoMsg = $tipoMessaggio;
				$htmlImg = $tempImg;
			}
		}
		else if ($identificativoID == "" || $tipoMessaggio == "")
		{
			$htmlNumFattura = "<font color='red'>$numFattura</font>";
			$htmlComune = "<font color='red'>errore</font>";
			$htmlEsito = "<font color='red'>$esitoFattura;</font>";
			$htmlData = "<font color='red'>" . from_mysql_date($dataMessaggio) . "</font>";
			$htmlIdentif = "<font color='red'>$identificativoID</font>";
			$htmlTipoMsg = "<font color='red'>$tipoMessaggio</font>";
			$htmlImg = "";
			$stileriga = " sfondo_rosso ";
		}
		
		$iconaEmail = "<img href='#' src='$iconaEmail' class='pwidth15 pheight15'>";
		
		?>
		
			<tr class="pheight25 <?=$stileriga?>">
				<td class="text_center">
					<?=$num?>
				</td>
				<td class="text_center">
					<?=$htmlNumFattura?>
				</td>
				<td class="text_center">
					<?=$htmlComune?>
				</td>
				<td class="text_center">
					<label title="<?php echo $titleEmail ?>"><?php echo $iconaEmail ?></label>
				</td>
				<td class="text_center">
					<?=$htmlData?>
				</td>
				<td class="text_center">
					<?=$htmlIdentif?>
				</td>
				<td class="text_center">
					<?=$htmlTipoMsg?>
				</td>
				<td class="text_center">
					<?=$htmlImg?>
				</td>
			</tr>
		
		<?php 
		
		//break;
	}  //  fine for
	
	?>
	
	</table>
			
</form>

</td>
</tr>
</table>

</body>
</html>