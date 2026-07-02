<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/comuni.php";
include CLASSI . "/targhe_estere.php";
include CLASSI . "/targhe_estere_utenti.php";
include CLASSI . "/targhe_estere_pagamenti.php";
include CLASSI . "/flussi.php";
include TCPDF . "/tcpdf.php";
include TCPDF . "/fpdi.php";

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
$p = get_var('p');

$provenienza = get_var('provenienza');

$autorizzazione = get_var('aut_tipo');

$tot_cbox = get_var('tot_cbox');
$cbox = get_var('cbox');

$cartellaFlussi = $PathCompletoFlussiEsteri . "Definitivi/";



$generaFile = false;
for ($r = 0; $r < $tot_cbox; $r++)
{
	if (!isset($cbox[$r])) $cbox[$r] = "";
	else
	{
		$generaFile = true;
	}
}

if ($generaFile == true)
{
	$myName = "NotificheImportate_" . date("Y-m-d") . "_" . date("H-i-s") . ".csv";
	$nomeFile = $cartellaFlussi . $myName;
	$stringa = "";
	for ($r = 0; $r < $tot_cbox; $r++)
	{
		if ($cbox[$r] != "")
		{
			$myTempFlusso = new flussi_tabella($cbox[$r]);
			$stringa .= $myTempFlusso->ID . ";";

			$tipo = $myTempFlusso->Tipo;
			$tipo = str_replace("ING", "R_CDS",$tipo);

			$stringa .= $tipo . ";";
			$stringa .= $myTempFlusso->CC_Comune . ";";
			$stringa .= $myTempFlusso->Anno . ";";
			$stringa .= $myTempFlusso->Num_Flusso . ";";
			$stringa .= $myTempFlusso->Num_Righe . ";";
			$stringa .= $myTempFlusso->Data_Flusso . ";";
			$stringa .= $myTempFlusso->Nome_Flusso . ";";
			$stringa .= $myTempFlusso->Nome_Flusso_Rar . ";\r\n";
		}
	}
	if ($stringa != "")
	{
		$intest = "Cod_ID;";
		$intest .= "Tipo;";
		$intest .= "CC_Comune;";
		$intest .= "Anno;";
		$intest .= "Num_Flusso;";
		$intest .= "Num_Righe;";
		$intest .= "Data_Flusso;";
		$intest .= "Nome_Flusso;";
		$intest .= "Nome_Flusso_Rar;" . "\r\n";
		$myFile = fopen($nomeFile, "w");
		fwrite($myFile, $intest);
		fwrite($myFile, $stringa);
		fclose($myFile);
		//echo "<br>" . $stringa;
		
		for ($r = 0; $r < $tot_cbox; $r++)
		{
			if ($cbox[$r] != "")
			{
				$myTemp2Flusso = new flussi_tabella($cbox[$r]);
				$myTemp2Flusso->Data_Travaso_Verso_Gitco = date("Y-m-d");
				$myTemp2Flusso->InsertUpdateFlussoTab("UPDATE");
				//alert ("non ce update");
			}
		}
	}
}
   
$comune = new ente_gestito($c);

$nome_comune = ($comune->Nome==NULL?"":$comune->Nome." [".$c."]");
$nome_user = "Operatore: " . $_SESSION['username'];

$questaPagina = "travaso_flussi_su_gitco.php";

$queryFlussi = "SELECT ID FROM flussi_tabella ";
$queryFlussi .= "WHERE Data_Travaso_Verso_Gitco = '0000-00-00' ";
$queryFlussi .= "ORDER BY Data_Flusso ASC";

$resultFlussi = esegui_query($queryFlussi);
$numeroFlussi = numero_risposte_query($resultFlussi);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Pagina Travaso Flussi</title>

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
	strLink += "&provenienza=" + "<?php echo $provenienza?>";

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

//F5

function annulla ()
{
	var stringaLink = "<?=$questaPagina?>?";
	stringaLink += "c=" + "<?php echo $c?>";
	stringaLink += "&a=" + "<?php echo $a?>";
	stringaLink += "&provenienza=" + "<?php echo $provenienza?>";
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
	$("[name=fluxpag]").submit();
}

//F9
function ricerca_F9()
{
	
}

//F10
function stampa_F10()
{

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
}

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
}
</script>
    
</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="comune" ><?php echo ElencoEsteriComuni($c, $a, $autorizzazione); ?> Anno <?=$a?></font></td>
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
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc()" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
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

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td>
			<div class="table_interna text_center" id="progressbar" style="height:55px;">
			<div class="text_center" id="barlabel"></div></div>
		</td>
	</tr>
	<tr>
		<td>
			<font class="titolo font16 under_decor">Travaso Flussi</font>
		</td>
	</tr>
</table>





<form name="fluxpag" method="get" action="<?=$questaPagina?>">
	
	<input type=hidden name="c" value="<?php echo $c; ?>">
	<input type=hidden name="a" value="<?php echo $a; ?>">
	<input type=hidden name="provenienza" value="<?php echo $provenienza; ?>">
	<input type=hidden name="tot_cbox" value="<?php echo $numeroFlussi; ?>">

	<table class="table_interna text_center" border="0">
	<tr class="pheight25 sfondo_new_gitco">
		<td class="width8 text_center"><b>ID</b></td>
		<td class="width12 text_center"><b>Tipo</b></td>
		<td class="width10 text_center"><b>Comune</b></td>
		<td class="width10 text_center"><b>Anno</b></td>
		<td class="width10 text_center"><b>Numero</b></td>
		<td class="width10 text_center"><b>Righe</b></td>
		<td class="width30 text_center"><b>Data Creazione</b></td>
		<td class="width10 text_center"></td>
	</tr>


<?php 

if ($numeroFlussi != 0)
{
	$stileriga = "riga_dispari";
	$s = 0;
	
	while ($rigaFlusso = risultati_query($resultFlussi))
	{
		if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
		else $stileriga = "sfondo_grigio";
		
		$myFlusso = new flussi_tabella($rigaFlusso['ID']);
		
?>

	<tr class="pheight25 <?=$stileriga?>">
		<td class="text_center"><?=$myFlusso->ID?></td>
		<td class="text_center"><?=$myFlusso->Tipo?></td>
		<td class="text_center"><?=$myFlusso->CC_Comune?></td>
		<td class="text_center"><?=$myFlusso->Anno?></td>
		<td class="text_center"><?=$myFlusso->Num_Flusso?></td>
		<td class="text_center"><?=$myFlusso->Num_Righe?></td>
		<td class="text_center"><?=from_mysql_date($myFlusso->Data_Flusso)?></td>
		<td class="text_center">
			<input type="checkbox" name="cbox[<?=$s?>]" value="<?=$myFlusso->ID?>" checked>
		</td>
	</tr>
	
<?php

		$s++;
	}
}
else
{
	echo <<< NIENTE
		<tr class="pheight25">
			<td colspan="8">
				<font color='red' size='+2'>
					Non ci sono flussi 
					<br>
					da inviare a Gitco
				</font>
			</td>
		<tr>
NIENTE;
}

?>

	</table>

</form>

</td>
</tr>
</table>

</body>
</html>
