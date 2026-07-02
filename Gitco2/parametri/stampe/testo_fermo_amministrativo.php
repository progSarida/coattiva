<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$gestore = $comune->Gestore_ID;

if ($gestore == 0) $tipoEnte = "Gestito dal Comune di ".$nome_com;
else $tipoEnte = "Gestito da ".$comune->Gestore->Denominazione;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$myParametroAtto = new testo_fermo_amministrativo(null);
$myId = $myParametroAtto->CercaParametroData($c, date("Y-m-d"));

$myParametroAtto = new testo_fermo_amministrativo($myId);

$Titolo_Oggetto = $myParametroAtto->Titolo_Oggetto;
$Sottotitolo_Oggetto = $myParametroAtto->Sottotitolo_Oggetto;
$Atti_Notificati = $myParametroAtto->Atti_Notificati;
$Sensi_Legge = $myParametroAtto->Sensi_Legge;

$Comunica = $myParametroAtto->Comunica;
$Comunica_Testo = $myParametroAtto->Comunica_Testo;

$Legale_Rappresentante_Comune = $myParametroAtto->Legale_Rappresentante_Comune;
$Legale_Rappresentante_Concessionario = $myParametroAtto->Legale_Rappresentante_Concessionario;

$Veicoli = $myParametroAtto->Veicoli;
$Iscrizione = $myParametroAtto->Iscrizione;
$Sanzioni = $myParametroAtto->Sanzioni;
$Cancellazione = $myParametroAtto->Cancellazione;

$Opposizione = $myParametroAtto->Opposizione;
$Opposizione_Testo = $myParametroAtto->Opposizione_Testo;

$Autotutela = $myParametroAtto->Autotutela;
$Autotutela_Testo = $myParametroAtto->Autotutela_Testo;

$Firma_Notifica = $myParametroAtto->Firma_Notifica;
$Qualifica_Firma_Notifica = $myParametroAtto->Qualifica_Firma_Notifica;


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Gestione parametri</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
 

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
	if (CheckCampiObbligatori () == true)
	{
		if (CheckTuttiCampi () == true)
		{
			control_salva = submit_buttons('Salva');
			if(control_salva)
       			$("#form_testo_fermo_amministrativo").submit();
		}
    }
}

//F4
function cancella_form() 
{
	return true;
}

//F5
function annulla() 
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "testo_fermo_amministrativo.php?"+stringaPHP;
	   	top.location.href = stringa;
}

//F6
function nuovo_F6()
{
	return true;
}

//F7-F8
function cambia_pag(value)
{
	return true;
}

//PAG GIU
function pag_prec()
{
	if( modifica == 0 )
	{
		pagina_menu('prev');
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
function pag_suc()
{
	if( modifica == 0 )
	{
		pagina_menu('next');
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F9
function ricerca_F9()
{
	return true;
}

//F10
function stampa_F10()
{
	return true;
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function pagina_menu(value)
{
	return true;
	
	switch(value)
	{
		case 'next':

			link = "testo_sollecito_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   		
   			break;

		case 'prev':

			link = "testo_preavviso_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			
			break;	
	}

	top.location.href = link;
	
}

</script>

<!-- ********** MODALI AJAX ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}

function cerca_comune()
{
	strDim = Dim_Alert(600, 300);

	var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=ricComune";
		   				
	valorediritorno = window.showModalDialog(stringa, "", strDim);

	if( valorediritorno!=null && valorediritorno!=undefined )
	{
		$('#comune_id').val(valorediritorno.comune);   						
		$('#prov_id').val(valorediritorno.prov_sigla);
		$('#cap_id').val(valorediritorno.cap);
		$('#CC_id').val(valorediritorno.CC);
	}	
}

</script>

<!-- ********** CONTROLLI, AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>

function CheckCampiObbligatori ()
 {
	if (ParolaPresenteInStringa ("sottotitolo_oggetto", "{DESCRIZIONECARTELLA}") == false) return false;
	if (ParolaPresenteInStringa ("sottotitolo_oggetto", "{ENTE}") == false) return false;

	if (ParolaPresenteInStringa ("atti_notificati", "{NOTIFICAPREAVVISO}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{CFPI}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{RESIDENZAUTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{ENTE}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{TOTALEDOVUTO}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{DATACALCOLO}") == false) return false;

	if (ParolaPresenteInStringa ("comunica_testo", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("comunica_testo", "{SEDEGESTORE}") == false) return false;

	if (ParolaPresenteInStringa ("rappresentante_concessionario", "{COMUNE}") == false) return false;
	
	if (ParolaPresenteInStringa ("iscrizione", "{CRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("iscrizione", "{GIORNIPREAVVISO}") == false) return false;
	if (ParolaPresenteInStringa ("iscrizione", "{FAXGESTORE}") == false) return false;

	if (ParolaPresenteInStringa ("cancellazione", "{TIPOGESTORE}") == false) return false;
	
	if (ParolaPresenteInStringa ("opposizione_testo", "{UTENTE}") == false) return false;

	if (ParolaPresenteInStringa ("qualifica_firma_notifica", "{FIRMAUFFICIALE}") == false) return false;
	if (ParolaPresenteInStringa ("firma_notifica", "{FIRMAUFFICIALE}") == false) return false;
	
	return true;
}

function ParolaPresenteInStringa (campo, parola)
{
	var namestr = "[name=" + campo + "]";
	var nomecampo = $(namestr);
	var idcampo = $("#" + campo);
	var testoId = idcampo.text();
	var stringa = nomecampo.val();

	var i = stringa.indexOf(parola);
	if (i == -1)
	{
		var message = "Non hai inserito il campo obbligatorio ' ";
		message += parola;
		message += " ' nel campo ' ";
		message += testoId;
		message += " '";
		message += ". Copia il campo nella lista a destra e incollalo nel testo.";
		alert (message);
		return false;
	}
	else return true;
}

function CheckTuttiCampi ()
{
	if (CheckCaratteri ("titolo_oggetto") == false) return false;
	if (CheckCaratteri ("sottotitolo_oggetto") == false) return false;

	if (CheckCaratteri ("atti_notificati") == false) return false;
	if (CheckCaratteri ("sensi_legge") == false) return false;
	
	if (CheckCaratteri ("comunica") == false) return false;
	if (CheckCaratteri ("comunica_testo") == false) return false;

	if (CheckCaratteri ("rappresentante_comune") == false) return false;
	if (CheckCaratteri ("rappresentante_concessionario") == false) return false;
		
	if (CheckCaratteri ("veicoli") == false) return false;
	if (CheckCaratteri ("iscrizione") == false) return false;
	if (CheckCaratteri ("sanzioni") == false) return false;
	if (CheckCaratteri ("cancellazione") == false) return false;

	if (CheckCaratteri ("opposizione") == false) return false;
	if (CheckCaratteri ("opposizione_testo") == false) return false;

	if (CheckCaratteri ("autotutela") == false) return false;
	if (CheckCaratteri ("autotutela_testo") == false) return false;
	
	if (CheckCaratteri ("qualifica_firma_notifica") == false) return false;
	if (CheckCaratteri ("firma_notifica") == false) return false;
		
	return true;
}

function CheckCaratteri(elem)
{
	if(typeof elem == "string")
		var name = elem;
	else
 		var name = elem.getAttribute("name");
		
	names = "[name="+name+"]";
 	var testo = $(names).val();
 	var idcampo = $("#" + name);
 	var testoId = idcampo.text();

 	var lungTesto = testo.length;
 	var testoNelCampo = "";
 	for (var i = 0; i < lungTesto; i++)
 	{
 		var mettiSINO = 1;
 		var carattere = testo.charAt(i);
 		if (carattere >= 'a' && carattere <= 'z') {}
 		else if (carattere >= 'A' && carattere <= 'Z') {}
 		else if (carattere >= '0' && carattere <= '9') {}
 		else if (carattere == " " || carattere == "'" || carattere == "/") {}
 		else if (carattere == '.' || carattere == ',' || carattere == ';' || carattere == ':') {}
 		else if (carattere == '+' || carattere == '-' || carattere == '*') {}
 		else if (carattere == '!' || carattere == '?') {}
 		else if (carattere == '<' || carattere == '>') {}
 		else if (carattere == '%' || carattere == '@' || carattere == '#') {}
 		else if (carattere == '€' || carattere == '$' || carattere == '&' || carattere == '^') {}
 		else if (carattere == '(' || carattere == ')') {}
 		else if (carattere == '{' || carattere == '}' || carattere == '[' || carattere == ']') {}
 		else
 		{
 			if (carattere == String.fromCharCode(13)) carattere = "INVIO";
 			if (carattere == String.fromCharCode(10)) carattere = "INVIO";
 			var messageError = "Hai inserito il carattere ' " + carattere + " ' nel campo '"+ testoId +"': carattere non accettato";
 			alert (messageError);
 			//return false;
 			mettiSINO = 0;
 		}
 		if (mettiSINO == 1) testoNelCampo += carattere;
 	}
 	
	$(names).val(testoNelCampo);
	
	return true;
}

</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){

	$("#submit_click").click( salva_form );
	        
    $("#delete_click").click( cancella_form );
	
	$('#form_testo_fermo_amministrativo').ajaxForm(
			
	    function(value) {
		    var array_ritorno = value.split(' ');
	        
			if(array_ritorno[0]=='SAVED')
			{		
				alert('Testo salvato correttamente!');
				annulla();
			}
			else if(array_ritorno[0]=='ERROR')
			{
				alert('Salvataggio testo fallito!');
			}

	    });
    
});
		
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
			<a onMouseover="title='Modifica'" href="#" onClick="" >
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
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
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick="">
          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick="">
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

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Testo Fermo amministrativo</font></td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font12">Intestazione</font></td>
	</tr>
</table>

<table class="table_interna text_center borderino" border="0" cellspacing="10" cellpadding="0">
	<tr >
		<td class="width20">Logo Gestore</td>
		<td class="width4"></td>
		<td class="width46 text_left">Dati Gestore</td>
		<td class="width30 text_left">Dati Ufficio</td>
	</tr>
		<tr >
		<td colspan=4><br></td>
	</tr>
	<tr >
		<td class="width20">Riferimenti</td>
		<td class="width4"></td>
		<td class="width46 text_left"></td>
		<td class="width30 text_left">Destinatario</td>
	</tr>
</table>

<br>

<form name="testo_fermo_amministrativo" id="form_testo_fermo_amministrativo" action="testo_fermo_amministrativo_salva.php" method="post">

<input name=invia_submit  id=invia_submit	type=hidden	value="" >

<input type="hidden" name="c" value="<?php echo $c?>">
<input type="hidden" name="a" value="<?php echo $a?>">

<table class="table_interna text_center" border="0">
	<tr>
		<td class="width20">
			<div id="legendaIngiunzione">
			<font size=-2 color="red">Nome Blocchi:</font>
			</div>
		</td>
		<td class="width4">
			<font size=-2 color="red">
			All.
			</font>
		</td>
		<td>
			<font size=-2 color="red">
			Testo Blocchi
			</font>
		</td>
		<td class="width20">
			<font size=-2 color="red">
			Campi OBBLIGATORI:
			</font>
		</td>
	</tr>
	<tr>
		<td colspan=4><br></td>
	</tr>
	<tr>
		<td class="width20">
			<div id="titolo_oggetto">Titolo oggetto:</div>
		</td>
		<td class="width4">
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="titolo_oggetto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Titolo_Oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="sottotitolo_oggetto">Sottotitolo oggetto:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="sottotitolo_oggetto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Sottotitolo_Oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			{DESCRIZIONECARTELLA}&nbsp;<br>{ANNOCRONOLOGICO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="atti_notificati">Atti notificati:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="atti_notificati" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Atti_Notificati?></textarea>
		</td>
		<td>
			<font size=-2>
			{NOTIFICAPREAVVISO}<br>
			{UTENTE}<br>{CFPI}<br>{RESIDENZAUTENTE}
			<br>{ENTE}<br>{TOTALEDOVUTO}<br>{DATACALCOLO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="dettaglioAtti">Dettaglio Atti notificati:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea class="sfondo_grigio text_center" name="dettaglioAtti" readonly style="width:95%" rows="1">NON EDITABILE</textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="sensi_legge">Sensi di legge:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="sensi_legge" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Sensi_Legge?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="comunica">Comunica:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="comunica" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Comunica?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="comunica_testo">Comunica testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="comunica_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Comunica_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}<br>{SEDEGESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><hr></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td>Stampato se Comune</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><hr></td>
		<td></td>
	</tr>
	<tr>
		<td>
			<div id="rappresentante_comune">Legale rappresentante:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="rappresentante_comune" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Legale_Rappresentante_Comune?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><hr></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td>Stampato se Concessionario</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><hr></td>
		<td></td>
	</tr>
	<tr>
		<td>
			<div id="rappresentante_concessionario">Legale rappresentante:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="rappresentante_concessionario" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Legale_Rappresentante_Concessionario?></textarea>
		</td>
		<td>
			<font size=-2>
			{COMUNE}
			</font>
		</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><hr></td>
		<td></td>
	</tr>
	<tr>
		<td>
			<div id="veicoli">Veicoli:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="veicoli" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Veicoli?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="dettaglioVeicoli">Dettaglio Veicoli:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea class="sfondo_grigio text_center" name="dettaglioVeicoli" readonly style="width:95%" rows="1">NON EDITABILE</textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="iscrizione">Iscrizione:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="iscrizione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Iscrizione?></textarea>
		</td>
		<td>
			<font size=-2>
			{CRONOLOGICO}<br>{GIORNIPREAVVISO}<br>{FAXGESTORE}
			</font>
		</td>
	</tr>
	
	<tr>
		<td>
			<div id="sanzioni">Sanzioni:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="sanzioni" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Sanzioni?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="cancellazione">Cancellazione:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="cancellazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Cancellazione?></textarea>
		</td>
		<td>
			<font size=-2>
			{TIPOGESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="opposizione">Opposizione:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="opposizione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Opposizione?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="opposizione_testo">Opposizione testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="opposizione_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Opposizione_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="autotutela">Autotutela:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="autotutela" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Autotutela?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="autotutela_testo">Autotutela testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="autotutela_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Autotutela_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="qualifica_firma_notifica">Qualifica firma <br>notifica:</div>
		</td>
		<td>
			<font size=-2>DES</font>
		</td>
		<td>
			<textarea name="qualifica_firma_notifica" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Qualifica_Firma_Notifica?></textarea>
		</td>
		<td>
			<font size=-2>
			{FIRMAUFFICIALE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="firma_notifica">Firma notifica:</div>
		</td>
		<td>
			<font size=-2>DES</font>
		</td>
		<td>
			<textarea name="firma_notifica" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Firma_Notifica?></textarea>
		</td>
		<td>
			<font size=-2>
			{FIRMAUFFICIALE}
			</font>
		</td>
	</tr>
	

</table>
<br>

</form>

</td>
</tr>
</table>

</body>
</html>