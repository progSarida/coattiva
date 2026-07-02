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

if ($_SESSION['CC_User'] == "***+")
{
	//alertAllGlobalVariables();
	//return;
}

$c = get_var('c');
$a = get_var('a');

$autorizzazione = get_var('aut_tipo');

$comune = new ente_gestito($c);

$nome_comune = ($comune->Nome==NULL?"":$comune->Nome." [".$c."]");
$nome_user = "Operatore: " . $_SESSION['username'];

$sceltatabelle = get_var('sceltatabelle');
switch ($sceltatabelle)
{
	case "fatture_dati_cig":
		$tabella = "fatture_dati_cig";
		$modalita = "";
		break;
	case "fatture_dati_contratti":
		$tabella = "fatture_dati_contratti";
		$modalita = "";
		break;
	case "fatture_dati_sedi_comuni":
		$tabella = "fatture_dati_sedi_comuni";
		$modalita = "";
		break;
	default:
		$tabella = "fatture_dati_cig";
		$modalita = "";
		break;
}

$hiddenid = get_var('hiddenid');
$salvadato = get_var('salvadato');
if ($salvadato == "SI")
{
	//echoAllGlobalVariables();
	
	switch ($tabella)
	{
		case "fatture_dati_cig":
			$myID = get_var ("ID");
			$daSalvare = new fatture_dati_cig($myID);
			$daSalvare->Tipo_Gestione = get_var ("Tipo_Gestione");
			$daSalvare->Tipo_Tributo = get_var ("Tipo_Tributo");
			$daSalvare->Comune = get_var ("Comune");
			$daSalvare->ID_Ufficio = get_var ("ID_Ufficio");
			$daSalvare->Nome_Ufficio = get_var ("Nome_Ufficio");
			$daSalvare->CIG = get_var ("CIG");
			$daSalvare->CUP = get_var ("CUP");
			$daSalvare->Riferimento_Numero = get_var ("Riferimento_Numero");
			if ($daSalvare->Comune != "")
			{
				if ($daSalvare->ID == null) $daSalvare->InsertUpdateCig("INSERT");
				else $daSalvare->InsertUpdateCig("UPDATE");
			}
			break;
		case "fatture_dati_contratti":
			$myID = get_var ("ID");
			$daSalvare = new fatture_dati_contratti($myID);
			$daSalvare->CC = get_var ("CC");
			$daSalvare->Tributo = get_var ("Tributo");
			$daSalvare->Tipo = get_var ("Tipo");
			$daSalvare->Numero = get_var ("Numero");
			$daSalvare->Data_Contratto = to_mysql_date(get_var ("Data_Contratto"));
			if ($daSalvare->CC != "")
			{
				if ($daSalvare->ID == null) $daSalvare->InsertUpdateContratto("INSERT");
				else $daSalvare->InsertUpdateContratto("UPDATE");
			}
			break;
		case "fatture_dati_sedi_comuni":
			$myID = get_var ("ID");
			$daSalvare = new fatture_dati_sedi_comuni($myID);
			$daSalvare->CC = get_var ("CC");
			$daSalvare->Indirizzo1 = get_var ("Indirizzo1");
			$daSalvare->Indirizzo2 = get_var ("Indirizzo2");
			$daSalvare->Indirizzo3 = get_var ("Indirizzo3");
			$daSalvare->Indirizzo4 = get_var ("Indirizzo4");
			$daSalvare->Indirizzo5 = get_var ("Indirizzo5");
			$daSalvare->Indirizzo6 = get_var ("Indirizzo6");
			$daSalvare->Indirizzo7 = get_var ("Indirizzo7");
			$daSalvare->Cod_Fisc = get_var ("Cod_Fisc");
			$daSalvare->P_IVA = get_var ("P_IVA");
			if ($daSalvare->CC != "")
			{
				if ($daSalvare->ID == null) $daSalvare->InsertUpdateDati("INSERT");
				else $daSalvare->InsertUpdateDati("UPDATE");
			}
			break;
		default:
			alert ($tabella);
			break;
	}
	
}

if ($hiddenid != "")
{
	$srcSalva = "/gitco2/immagini/Save-iconF3.png";
}
else 
{
	$srcSalva = "/gitco2/immagini/Save-iconF3grey.png";
}
/*else
{
	switch ($tabella)
	{
		case "fatture_dati_cig":
			$daHidden = new fatture_dati_cig(null);
			break;
		case "fatture_dati_contratti":
			$daHidden = new fatture_dati_contratti(null);
			break;
		case "fatture_dati_sedi_comuni":
			$daHidden = new fatture_dati_sedi_comuni(null);
			break;
		default:
			alert ($tabella);
			break;
	}
}*/

/*$modalita = get_var('modalita');
if ($modalita == "") $modalita = "tipo_importato";*/

$questaPagina = "parametri_tabelle.php";

//$codice = get_var('codice');  //  da questa pagina
//$progressivo = get_var('progressivo');  //  da questa pagina


/*if ($codice != "" && $progressivo != "")
{
	for ($j = 0; $j < count($progressivo); $j++)
	{
		//if ($codice[$j] != "")
		{
			$myCodice = new targhe_estere_codici_ente_comune(null);
			$temp = $myCodice->CodiceGiaPresente($c, $progressivo[$j]);
			$myCodice = new targhe_estere_codici_ente_comune($temp);
			
			$esegui = false;
			// se non c'è già codice   OPPURE  il codice salvato è diverso
			if ($myCodice->ID == "" && $codice[$j] != "") $esegui = true;
			if ($myCodice->ID != "" && $codice[$j] != $myCodice->Codice) $esegui = true;
			
			if ($esegui == true)
			{
				//$myCodice->Data_Validita = 
				$myCodice->Ente = $progressivo[$j];
				$myCodice->Comune = $c;
				$myCodice->Codice = $codice[$j];
				$myCodice->InsertUpdateCodiceComune();
			}
		}
	}
}*/

$stileriga = "sfondo_grigio";


$selCIG = $selRicTipo = $selRicStato = $selContratti = $selSedi = "";
$selAnnull = $selCodici = "";
switch ($tabella)
{
	case "fatture_dati_cig":
		$queryVaria = "SELECT ID ";
		$queryVaria .= " FROM fatture_dati_cig ";
		$queryVaria .= " ORDER BY Comune ";
		$selRic = " selected ";
		break;
	case "fatture_dati_contratti":
		$queryVaria = "SELECT ID ";
		$queryVaria .= " FROM fatture_dati_contratti ";
		$queryVaria .= " ORDER BY CC ";
		$selContratti = " selected ";
		break;
	case "fatture_dati_sedi_comuni":
		$queryVaria = "SELECT ID ";
		$queryVaria .= " FROM fatture_dati_sedi_comuni ";
		$queryVaria .= " ORDER BY CC ";
		$selSedi = " selected ";
		break;
	default:
		alert ($tabella);
		break;
}

$resVaria = esegui_query($queryVaria);





?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Parametri</title>

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
	strLink += "&tabella=" + "<?php echo $tabella?>";
	strLink += "&modalita=" + "<?php echo $modalita?>";

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
	stringaLink += "&sceltatabelle=" + "<?php echo $sceltatabelle?>";
	stringaLink += "&tabella=" + "<?php echo $tabella?>";
	stringaLink += "&modalita=" + "<?php echo $modalita?>";
	location.href = stringaLink;
}


//F6
function nuovo_F6()
{
	$("[name=hiddenid]").val("NUOVO");
	$('#tabelle_form').submit();
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
	return true;
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

function ControllaRiferimento ()
{
	var rif = $("[name=Riferimento_Numero]").val();
	var splitRif = rif.split("**");
	if (rif == "")
	{
		// va bene vuoto
	}
	else if (splitRif.length == 2)
	{
		if (splitRif[0].length > 10)
		{
			alert ("Il tipo di Riferimento deve essere al massimo di 10 caratteri");
			return false;
		}
	}
	else
	{
		alert ("Il campo Riferimento deve avere formato IMPSPESA**123 (con due asterischi tra tipo e numero)");
		return false;
	}
	return true;
}

function SalvataggioDato ()
{
	if ($("#submit_click").attr("src") == "/gitco2/immagini/Save-iconF3grey.png") return false;

	<?php if ($tabella == "fatture_dati_cig") { ?>

	var esitorif = ControllaRiferimento();
	if (esitorif == false) return;
	
	<?php } ?>
	
	$("[name=salvadato]").val("SI");
	$('#tabelle_form').submit();
}

function CambiaTabella ()
{
	$('#tabelle_form').submit();
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

function SceltaID (valoreid)
{
	$("[name=hiddenid]").val(valoreid);
	return true;  //  fa submit da solo
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
			<input id="submit_click" type="image" title="Salva" src="<?=$srcSalva?>" style="width:47px; height:47px; border:0;" />
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
			<a onMouseover="title='Nuovo Record'" href="#" onClick="nuovo_F6();" style="text-decoration: none;">
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
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick="cambia_pag('prev');"><img src="/gitco2/immagini/FrecciaS.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick="cambia_pag('suc');"><img src="/gitco2/immagini/FrecciaD.png" width=42px height=42px border="0" alt="Utente successivo"></a>
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
		
	<form id="tabelle_form" name="tabelle_form" action="<?=$questaPagina?>" method="post">
		
	<input type="hidden" name="c" value="<?php echo $c ?>">
	<input type="hidden" name="a" value="<?php echo $a ?>">
	<input type="hidden" name="salvadato" value="">
	<input type="hidden" name="hiddenid" value="">
						
	
		
 	<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
	<tr class="pheight50">
		<td class="width5 text_center">
		</td>
		<td class="width90 text_center">
			<font class="titolo font18">PARAMETRI</font>
		</td>
    	<td class="width5 text_center">
        </td>
	</tr>
	</table>
	
	<table class='table_interna text_center' border='0'>
	<tr class="pheight30 sfondo_new_gitco">
		<td class="width60">
			<b>Tabella: <?=$tabella?></b>
			<input type="hidden" name="tabella" value="<?=$tabella?>">
		</td>
		<td class="width10 text_right">
			Scelta:
		</td>
		<td class="width30 text_left">
			<select name="sceltatabelle" onchange="CambiaTabella();">
				<option value="fatture_dati_cig" <?=$selCIG?>>Dati CIG</option>
				<option value="fatture_dati_contratti" <?=$selContratti?>>Contratti</option>
				<option value="fatture_dati_sedi_comuni" <?=$selSedi?>>Sedi Comuni</option>
			</select>
		</td>
	</tr>
	<tr class="pheight20">
		<td colspan="3">
		</td>
	</tr>
	</table>
			
		<?php if ($tabella == "fatture_dati_cig") { ?>
		
			<?php if ($hiddenid != "") { ?>
			
				<table class='table_interna text_center' border='0'>
					
				<?php 
				
				$myTab = new fatture_dati_cig($hiddenid);
				
				foreach ($myTab as $key => $value)
				{
					if ($key != "Data")
					{
						if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
						else $stileriga = "sfondo_grigio";
						
						if ($key == "ID") { $grigio = " class='width90 text_center sfondo_grigio' "; $readd = " readonly "; }
						else { $grigio = " class='width90 text_center' "; $readd = ""; }
						if ($key == "Tipo_Gestione")
						{
							$selectCanone = $selectAggio = $selectServizio = "";
							if ($value == "PAGATA_A_CANONE") $selectCanone = " selected ";
							else if ($value == "PAGATA_AD_AGGIO") $selectAggio = " selected ";
							else if ($value == "SERVIZIO") $selectServizio = " selected ";
							$aggiungi = "<select name='$key'>
											<option></option>
											<option $selectCanone>PAGATA_A_CANONE</option>
											<option $selectAggio>PAGATA_AD_AGGIO</option>
											<option $selectServizio>SERVIZIO</option>
										</select>";
						}
						else if ($key == "Tipo_Tributo")
						{
							$fattura = new fatture_generali(null);
							$optionTipiTrib = $fattura->OptionTipiFatture($value);
							$aggiungi = "<select name='$key'>
											$optionTipiTrib
										</select>";
						}
						else 
						{
							$aggiungi = "<input type='text' $grigio name='$key' $readd value=\"$value\">";
						}
						
						?>
						
							<tr class="pheight23 <?=$stileriga?>">
								<td class="width30">
									<?=$key?>
								</td>
								<td>
									<?=$aggiungi?>
								</td>
							</tr>
						
						<?php 
					}
				}
				
				?>
				
				</table>
				
			<?php } ?>
 
			<table class='table_interna text_center' border='0'>
			
			<tr class="sfondo_new_gitco">
				<td class="width6">
					<b>ID</b>
				</td>
				<td class="width16">
					<b>Gestione</b>
				</td>
				<td class="width8">
					<b>Tributo</b>
				</td>
				<td class="width8">
					<b>Comune</b>
				</td>
				<td class="width10">
					<b>ID Ufficio</b>
				</td>
				<td class="width20">
					<b>Ufficio</b>
				</td>
				<td class="width10">
					<b>CIG</b>
				</td>
				<td class="width10">
					<b>CUP</b>
				</td>
				<td class="width12">
					<b>Riferimento</b>
				</td>
			</tr>
			
			<?php

			$stileriga = "sfondo_grigio";
			
			$i = 0;
			while ($rigaTab = risultati_query($resVaria))
			{
				$myTab = new fatture_dati_cig($rigaTab['ID']);
				
				$myComune = $myTab->NomeComuneDaComuneCig($myTab->Comune);
				
				?>
				
				<tr class="pheight20 <?=$stileriga?>">
					<td class="font11">
						<input type="image" src="/gitco2/immagini/plus.png" class="pwidth20 pheight20" name="sceltaid" onclick="SceltaID(<?=$myTab->ID?>);">
					</td>
					<td class="font11">
						<?=$myTab->Tipo_Gestione?>
					</td>
					<td class="font11">
						<?=$myTab->Tipo_Tributo?>
					</td>
					<td class="font11">
						<font title="<?=$myComune?>"><?=$myTab->Comune?></font>
					</td>
					<td class="font11">
						<?=$myTab->ID_Ufficio?>
					</td>
					<td class="font11">
						<?=$myTab->Nome_Ufficio?>
					</td>
					<td class="font11">
						<?=$myTab->CIG?>
					</td>
					<td class="font11">
						<?=$myTab->CUP?>
					</td>
					<td class="font11">
						<?=$myTab->Riferimento_Numero?>
					</td>
				</tr>
				
				<?php 
				$i ++;

				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
			}
			
			?>
			
			</table>
			
		<?php } ?>
		
		<?php if ($tabella == "fatture_dati_contratti") { ?>
		
			<?php if ($hiddenid != "") { ?>
			
				<table class='table_interna text_center' border='0'>
					
				<?php 
				
				$myTab = new fatture_dati_contratti($hiddenid);
				
				foreach ($myTab as $key => $value)
				{
					if ($key != "Data_Validita")
					{
						if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
						else $stileriga = "sfondo_grigio";
						
						if ($key == "ID") { $grigio = " class='width90 text_center sfondo_grigio' "; $readd = " readonly "; }
						else { $grigio = " class='width90 text_center' "; $readd = ""; }
						if ($key == "Indirizzo1")
						{
							if ($value == "Amministrazione Comunale di") $selectInd1 = " selected ";
							else $selectInd1 = "";
							$aggiungi = "<select name='$key'>
											<option></option>
											<option $selectInd1 >Amministrazione Comunale di</option>
										</select>";
						}
						else if ($key == "Tributo")
						{
							$fattura = new fatture_generali(null);
							$optionTipiTrib = $fattura->OptionTipiFatture($value);
							$aggiungi = "<select name='$key'>
											$optionTipiTrib
										</select>";
						}
						else if ($key == "Tipo")
						{
							$optionContrt = $myTab->ListaTipiContratto($value);
							$aggiungi = "<select name='$key'>
											$optionContrt
										</select>";
						}
						else if ($key == "Data_Contratto")
						{
							$value = from_mysql_date($value);
							$aggiungi = "<input type='text' $grigio name='$key' id='$key' $readd value='$value' onchange='ctrlCampoData(id);'>";
						}
						else 
						{
							$aggiungi = "<input type='text' $grigio name='$key' $readd value=\"$value\">";
						}
						
						?>
						
							<tr class="pheight23 <?=$stileriga?>">
								<td class="width30">
									<?=$key?>
								</td>
								<td>
									<?=$aggiungi?>
								</td>
							</tr>
						
						<?php 
					}
				}
				
				?>
				
				</table>
				
			<?php } ?>
 
			<table class='table_interna text_center' border='0'>
			
			<tr class="sfondo_new_gitco">
				<td class="width10">
					<b>ID</b>
				</td>
				<td class="width10">
					<b>Comune</b>
				</td>
				<td class="width25">
					<b>Comune</b>
				</td>
				<td class="width15">
					<b>Tributo</b>
				</td>
				<td class="width10">
					<b>Tipo</b>
				</td>
				<td class="width15">
					<b>Numero</b>
				</td>
				<td class="width15">
					<b>Data</b>
				</td>
			</tr>
			
			<?php

			$stileriga = "sfondo_grigio";
			
			$i = 0;
			while ($rigaTab = risultati_query($resVaria))
			{
				$myTab = new fatture_dati_contratti($rigaTab['ID']);
				
				$myComune = $myTab->NomeComuneDaCCcontratti($myTab->CC);
				
				?>
				
				<tr class="pheight20 <?=$stileriga?>">
					<td class="font11">
						<input type="image" src="/gitco2/immagini/plus.png" class="pwidth20 pheight20" name="sceltaid" onclick="SceltaID(<?=$myTab->ID?>);">
					</td>
					<td class="font11">
						<?=$myTab->CC?>
					</td>
					<td class="font11">
						<?=$myComune?>
					</td>
					<td class="font11">
						<?=$myTab->Tributo?>
					</td>
					<td class="font11">
						<?=$myTab->Tipo?>
					</td>
					<td class="font11">
						<?=$myTab->Numero?>
					</td>
					<td class="font11">
						<?=from_mysql_date($myTab->Data_Contratto)?>
					</td>
				</tr>
				
				<?php 
				$i ++;

				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
			}
			
			?>
			
			</table>
			
		<?php } ?>
		
		<?php if ($tabella == "fatture_dati_sedi_comuni") { ?>
		
			<?php if ($hiddenid != "") { ?>
			
				<table class='table_interna text_center' border='0'>
					
				<?php 
				
				$myTab = new fatture_dati_sedi_comuni($hiddenid);
				
				foreach ($myTab as $key => $value)
				{
					if ($key != "Data")
					{
						if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
						else $stileriga = "sfondo_grigio";
						
						if ($key == "ID") { $grigio = " class='width90 text_center sfondo_grigio' "; $readd = " readonly "; }
						else { $grigio = " class='width90 text_center' "; $readd = ""; }
						if ($key == "Indirizzo1")
						{
							if ($value == "Amministrazione Comunale di") $selectInd1 = " selected ";
							else $selectInd1 = "";
							$aggiungi = "<select name='$key'>
											<option></option>
											<option $selectInd1 >Amministrazione Comunale di</option>
										</select>";
						}
						/*else if ($key == "Data")
						{
							$value = from_mysql_date($value);
							$aggiungi = "<input type='text' $grigio name='$key' id='$key' $readd value='$value' onchange='ctrlCampoData(id);'>";
						}*/
						else 
						{
							$aggiungi = "<input type='text' $grigio name='$key' $readd value=\"$value\">";
						}
						
						?>
						
							<tr class="pheight23 <?=$stileriga?>">
								<td class="width30">
									<?=$key?>
								</td>
								<td>
									<?=$aggiungi?>
								</td>
							</tr>
						
						<?php 
					}
				}
				
				?>
				
				</table>
				
			<?php } ?>
 
			<table class='table_interna text_center' border='0'>
			
			<tr class="sfondo_new_gitco">
				<td class="width5">
					<b>ID</b>
				</td>
				<td class="width5">
					<b>Comune</b>
				</td>
				<td class="width10">
					<b>Indirizzo1</b>
				</td>
				<td class="width10">
					<b>Indirizzo2</b>
				</td>
				<td class="width10">
					<b>Indirizzo3</b>
				</td>
				<td class="width10">
					<b>Indirizzo4</b>
				</td>
				<td class="width10">
					<b>Indirizzo5</b>
				</td>
				<td class="width10">
					<b>Indirizzo6</b>
				</td>
				<td class="width10">
					<b>Indirizzo7</b>
				</td>
				<td class="width10">
					<b>CodFisc</b>
				</td>
				<td class="width10">
					<b>P.Iva</b>
				</td>
			</tr>
			
			<?php

			$stileriga = "sfondo_grigio";
			
			$i = 0;
			while ($rigaTab = risultati_query($resVaria))
			{
				$myTab = new fatture_dati_sedi_comuni($rigaTab['ID']);
				
				?>
				
				<tr class="pheight20 <?=$stileriga?>">
					<td class="font11">
						<input type="image" src="/gitco2/immagini/plus.png" class="pwidth20 pheight20" name="sceltaid" onclick="SceltaID(<?=$myTab->ID?>);">
					</td>
					<td class="font11">
						<?=$myTab->CC?>
					</td>
					<td class="font11">
						<?=$myTab->Indirizzo1?>
					</td>
					<td class="font11">
						<?=$myTab->Indirizzo2?>
					</td>
					<td class="font11">
						<?=$myTab->Indirizzo3?>
					</td>
					<td class="font11">
						<?=$myTab->Indirizzo4?>
					</td>
					<td class="font11">
						<?=$myTab->Indirizzo5?>
					</td>
					<td class="font11">
						<?=$myTab->Indirizzo6?>
					</td>
					<td class="font11">
						<?=$myTab->Indirizzo7?>
					</td>
					<td class="font11">
						<?=$myTab->Cod_Fisc?>
					</td>
					<td class="font11">
						<?=$myTab->P_IVA?>
					</td>
				</tr>
				
				<?php 
				$i ++;

				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
			}
			
			?>
			
			</table>
			
		<?php } ?>
			
</form>

</td>
</tr>
</table>

</body>
</html>