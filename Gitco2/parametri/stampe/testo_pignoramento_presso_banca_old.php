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

$myParametroAtto = new testo_pignoramento_presso_banca(null);
$myId = $myParametroAtto->CercaParametroData($c, date("Y-m-d"));

$myParametroAtto = new testo_pignoramento_presso_banca($myId);

$Titolo_Oggetto = $myParametroAtto->Titolo_Oggetto;
$Sottotitolo_Oggetto = $myParametroAtto->Sottotitolo_Oggetto;
$Ufficiale_Responsabile = $myParametroAtto->Ufficiale_Responsabile;
$Abilitazione = $myParametroAtto->Abilitazione;
$Premesso = $myParametroAtto->Premesso;
$Premesso_Testo = $myParametroAtto->Premesso_Testo;
$Atti_Notificati = $myParametroAtto->Atti_Notificati;
$Terzo = $myParametroAtto->Terzo;
$Banca = $myParametroAtto->Banca;
$Ordine_Pagamento = $myParametroAtto->Ordine_Pagamento;
$Ordina = $myParametroAtto->Ordina;
$Ordina_Testo = $myParametroAtto->Ordina_Testo;
$Termini_Pagamento = $myParametroAtto->Termini_Pagamento;
$Estremi_Pagamento = $myParametroAtto->Estremi_Pagamento;
$Ufficiale_Banca = $myParametroAtto->Ufficiale_Banca;
$Sottoposto_Pignoramento = $myParametroAtto->Sottoposto_Pignoramento;
$Sottoposto_Pignoramento_Banca = $myParametroAtto->Sottoposto_Pignoramento_Banca;
$Intima = $myParametroAtto->Intima;
$Intima_Testo = $myParametroAtto->Intima_Testo;
$Art56 = $myParametroAtto->Art56;
$Art49 = $myParametroAtto->Art49;
$Pagamento_Effettuato = $myParametroAtto->Pagamento_Effettuato;
$Intestazione_Firma = $myParametroAtto->Intestazione_Firma;
$Firma = $myParametroAtto->Firma;
$Luogo = $myParametroAtto->Luogo;
$Relazione_Notifica = $myParametroAtto->Relazione_Notifica;
$Relazione_Pignorato = $myParametroAtto->Relazione_Pignorato;
$Relazione_Terzo = $myParametroAtto->Relazione_Terzo;

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
       			$("#form_testo_pignoramento_presso_banca").submit();
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
	stringa = "testo_pignoramento_presso_banca.php?"+stringaPHP;
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
	if (ParolaPresenteInStringa ("titolo_oggetto", "{IDCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("titolo_oggetto", "{ANNOCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("titolo_oggetto", "{RIFERIMENTO}") == false) return false;

	
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
	if (CheckCaratteri ("ufficiale_responsabile") == false) return false;
	if (CheckCaratteri ("abilitazione") == false) return false;
	if (CheckCaratteri ("premesso") == false) return false;
	if (CheckCaratteri ("premesso_testo") == false) return false;
	if (CheckCaratteri ("atti_notificati") == false) return false;
	if (CheckCaratteri ("terzo") == false) return false;
	if (CheckCaratteri ("datore_lavoro") == false) return false;
	if (CheckCaratteri ("banca") == false) return false;
	if (CheckCaratteri ("ordine_pagamento") == false) return false;
	if (CheckCaratteri ("ordina") == false) return false;
	if (CheckCaratteri ("ordina_testo") == false) return false;
	if (CheckCaratteri ("termini_pagamento") == false) return false;
	if (CheckCaratteri ("estremi_pagamento") == false) return false;
	if (CheckCaratteri ("datore_lavoro") == false) return false;
	if (CheckCaratteri ("banca") == false) return false;
	if (CheckCaratteri ("sottoposto_pignoramento") == false) return false;
	if (CheckCaratteri ("sottoposto_pignoramento_testo") == false) return false;
	if (CheckCaratteri ("intima") == false) return false;
	if (CheckCaratteri ("intima_testo") == false) return false;
	if (CheckCaratteri ("art56") == false) return false;
	if (CheckCaratteri ("art49") == false) return false;
	if (CheckCaratteri ("pagamento_effettuato") == false) return false;
	if (CheckCaratteri ("intestazione_firma") == false) return false;
	if (CheckCaratteri ("firma") == false) return false;
	if (CheckCaratteri ("luogo") == false) return false;
	if (CheckCaratteri ("relazione_notifica") == false) return false;
	if (CheckCaratteri ("relazione_pignorato") == false) return false;
	if (CheckCaratteri ("relazione_terzo") == false) return false;
		
	return true;
}

function CheckCaratteri(names)
{
// 	var name = "[name="+names+"]";
// 	var testo = $(name).val();
// 	var idcampo = $("#" + names);
// 	var testoId = idcampo.text();

// 	var lungTesto = testo.length;
// 	var testoNelCampo = "";
// 	for (var i = 0; i < lungTesto; i++)
// 	{
// 		var mettiSINO = 1;
// 		var carattere = testo.charAt(i);
// 		if (carattere >= 'a' && carattere <= 'z') {}
// 		else if (carattere >= 'A' && carattere <= 'Z') {}
// 		else if (carattere >= '0' && carattere <= '9') {}
// 		else if (carattere == " " || carattere == "'" || carattere == "/") {}
// 		else if (carattere == '.' || carattere == ',' || carattere == ';' || carattere == ':') {}
// 		else if (carattere == '+' || carattere == '-' || carattere == '*') {}
// 		else if (carattere == '!' || carattere == '?') {}
// 		else if (carattere == '<' || carattere == '>') {}
// 		else if (carattere == '%' || carattere == '@' || carattere == '#') {}
// 		else if (carattere == '€' || carattere == '$' || carattere == '&' || carattere == '^') {}
// 		else if (carattere == '(' || carattere == ')') {}
// 		else if (carattere == '{' || carattere == '}' || carattere == '[' || carattere == ']') {}
// 		else
// 		{
// 			if (carattere == String.fromCharCode(13)) carattere = "INVIO";
// 			if (carattere == String.fromCharCode(10)) carattere = "INVIO";
// 			var messageError = "Hai inserito il carattere ' " + carattere + " ' nel campo '"+ testoId +"': carattere non accettato";
// 			alert (messageError);
// 			//return false;
// 			mettiSINO = 0;
// 		}
// 		if (mettiSINO == 1) testoNelCampo += carattere;
// 	}
	//$(name).val(testoNelCampo);
	return true;
}

</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){

	$("#submit_click").click( salva_form );
	        
    $("#delete_click").click( cancella_form );
	
	$('#form_testo_pignoramento_presso_banca').ajaxForm(
			
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
		<td><font class="titolo font16 under_decor">Testo Pignoramento presso banca</font></td>
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

<form name="testo_pignoramento_presso_banca" id="form_testo_pignoramento_presso_banca" action="testo_pignoramento_presso_banca_salva.php" method="post">

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
			<textarea name="titolo_oggetto" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Titolo_Oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			{IDCRONOLOGICO}&nbsp;<br>{ANNOCRONOLOGICO}&nbsp;<br>{RIFERIMENTO}
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
			<textarea name="sottotitolo_oggetto" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Sottotitolo_Oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ufficiale_responsabile">Ufficiale responsabile:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ufficiale_responsabile" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Ufficiale_Responsabile?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}&nbsp;<br>{SEDEGESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ufficiale_responsabile">Abilitazione:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="abilitazione" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Abilitazione?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="premesso">Premesso:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="premesso" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Premesso?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="premesso_testo">Premesso testo:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="premesso_testo" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Premesso_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}&nbsp;<br>{CFPI}&nbsp;<br>{RESIDENZAUTENTE}&nbsp;<br>{IMPORTODOVUTO}
			&nbsp;<br>{TOTALEDOVUTO}&nbsp;<br>{DATACALCOLO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="dettaglioImporti">Dettaglio Importi:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea class="sfondo_grigio text_center" name="dettaglioImporti" readonly style="width:95%" rows="1">NON EDITABILE</textarea>
		</td>
		<td>
			<font size=-2>
			
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
			<textarea name="atti_notificati" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Atti_Notificati?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="terzo">Terzo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="terzo" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Terzo?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERZI}&nbsp;<br>{UTENTE}&nbsp;<br>
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
			<div id="banca">Banca:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="banca" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Banca?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}&nbsp;<br>{UTENTE}&nbsp;<br>{TERZI}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ordine_pagamento">Ordine pagamento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ordine_pagamento" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Ordine_Pagamento?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ordina">Ordina:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ordina" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Ordina?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ordina_testo">Ordina testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ordina_testo" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Ordina_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERZI}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="termini_pagamento">Termini pagamento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="termini_pagamento" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Termini_Pagamento?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="estremi_pagamento">Estremi pagamento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="estremi_pagamento" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Estremi_Pagamento?></textarea>
		</td>
		<td>
			<font size=-2>
			{NUMEROCONTO}&nbsp;<br>{INTESTATARIOCONTO}&nbsp;<br>{IDCRONOLOGICO}&nbsp;<br>
			{ANNOCRONOLOGICO}&nbsp;<br>{RIFERIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ufficiale_banca">Ufficiale Banca:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ufficiale_banca" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Ufficiale_Banca?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="sottoposto_pignoramento">Sottoposto pignoramento:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="sottoposto_pignoramento" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Sottoposto_Pignoramento?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="sottoposto_pignoramento_banca">Sottoposto pignoramento banca:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="sottoposto_pignoramento_banca" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$Sottoposto_Pignoramento_Banca?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}&nbsp;<br>{SEDEGESTORE}&nbsp;<br>{TERZI}&nbsp;<br>{TOTALEDOVUTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intima">Intima:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="intima" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Intima?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intima_testo">Intima testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="intima_testo" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Intima_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERZI}&nbsp;<br>{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="art56">Articolo 56:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="art56" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Art56?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="art49">Articolo 49:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="art49" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Art49?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="pagamento_effettuato">Pagamento effettuato:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="pagamento_effettuato" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Pagamento_Effettuato?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intestazione_firma">Intestazione firma:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="intestazione_firma" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Intestazione_Firma?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="firma">Firma:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="firma" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Firma?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="luogo">Luogo:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="luogo" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Luogo?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="relazione_notifica">Relazione notifica:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="relazione_notifica" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Relazione_Notifica?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="relazione_pignorato">Relazione pignorato:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="relazione_pignorato" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Relazione_Pignorato?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}&nbsp;<br>{UTENTE}&nbsp;<br>{RESIDENZAUTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="relazione_terzo">Relazione terzo:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="relazione_terzo" onkeyup="CheckCaratteri(name);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$Relazione_Terzo?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}&nbsp;<br>{TERZO}&nbsp;<br>{SEDETERZO}&nbsp;<br>{UFFICIOPOSTALE}
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