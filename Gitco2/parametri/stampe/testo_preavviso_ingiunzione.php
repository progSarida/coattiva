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

$myParametroPreavviso = new parametri_testo_preavviso_ingiunzione(NULL);
$myId = $myParametroPreavviso->CercaParametroData($c, date("Y-m-d"));

$myParametroPreavviso = new parametri_testo_preavviso_ingiunzione($myId);

$oggettoPreavviso = $myParametroPreavviso->Oggetto_Preavviso_Ingiunzione;
$primoTesto = $myParametroPreavviso->Primo_Testo;
$secondoTesto = $myParametroPreavviso->Secondo_Testo;
$terzoTesto = $myParametroPreavviso->Terzo_Testo;
$sommaTesto = $myParametroPreavviso->Intro_Somma_Testo;
$terzoTesto = $myParametroPreavviso->Terzo_Testo;
$quartoTesto = $myParametroPreavviso->Quarto_Testo;
$salutiTesto = $myParametroPreavviso->Saluti_Testo;
$ufficialeRiscossione = $myParametroPreavviso->Ufficiale_Riscossione;
$nomeUfficialeRisc = $myParametroPreavviso->Nome_Ufficiale_Riscossione;
$ufficialeRiscossione2 = $myParametroPreavviso->Ufficiale_Riscossione_2;
$nomeUfficialeRisc2 = $myParametroPreavviso->Nome_Ufficiale_Riscossione_2;
$modalitaFirma = $myParametroPreavviso->Stampa_Firma;

$info_1_Titolo = $myParametroPreavviso->Info_1_Titolo;
$info_1_Testo = $myParametroPreavviso->Info_1_Testo;

$CDS_Titolo = $myParametroPreavviso->CDS_Titolo;
$CDS_Testo_1 = $myParametroPreavviso->CDS_Testo_1;
$CDS_Testo_2 = $myParametroPreavviso->CDS_Testo_2;
$CDS_Testo_3 = $myParametroPreavviso->CDS_Testo_3;

$tributo_titolo = $myParametroPreavviso->Tributo_Titolo;
$tributo_testo = $myParametroPreavviso->Tributo_Testo;

$info_2_Titolo = $myParametroPreavviso->Info_2_Titolo;
$info_2_Testo = $myParametroPreavviso->Info_2_Testo;
$info_3_Titolo = $myParametroPreavviso->Info_3_Titolo;
$info_3_Testo = $myParametroPreavviso->Info_3_Testo;

$avviso_Titolo = $myParametroPreavviso->Avviso_Titolo;
$esito_1_Testo = $myParametroPreavviso->Esito_1_Testo;
$caso_A_Testo = $myParametroPreavviso->Caso_A_Testo;
$caso_B_Testo = $myParametroPreavviso->Caso_B_Testo;
$caso_C_Testo = $myParametroPreavviso->Caso_C_Testo;
$caso_D_Testo = $myParametroPreavviso->Caso_D_Testo;
$caso_E_Testo = $myParametroPreavviso->Caso_E_Testo;
$esito_2_Testo = $myParametroPreavviso->Esito_2_Testo;
$esito_3_Testo = $myParametroPreavviso->Esito_3_Testo;
$esito_4_Testo = $myParametroPreavviso->Esito_4_Testo;

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
       			$("#form_preavviso_ingiunzione").submit();
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
	stringa = "testo_preavviso_ingiunzione.php?"+stringaPHP;
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

			link = "testo_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   		
   			break;

		case 'prev':

			link = "testo_avviso_intimazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			
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
	if (ParolaPresenteInStringa ("oggettoPreavviso", "{ENTE}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTesto", "{SALDO}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTesto", "{GIORNI}") == false) return false;
	if (ParolaPresenteInStringa ("terzoTesto", "{NUMEROCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("terzoTesto", "{INTESTATARIOCONTO}") == false) return false;

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
	if (CheckCaratteri ("oggettoPreavviso") == false) return false;
	if (CheckCaratteri ("primoTesto") == false) return false;
	if (CheckCaratteri ("secondoTesto") == false) return false;
	if (CheckCaratteri ("sommaTesto") == false) return false;
	if (CheckCaratteri ("terzoTesto") == false) return false;
	if (CheckCaratteri ("quartoTesto") == false) return false;
	if (CheckCaratteri ("salutiTesto") == false) return false;
	if (CheckCaratteri ("ufficialeRiscossione") == false) return false;
	if (CheckCaratteri ("nomeUfficialeRisc") == false) return false;
	if (CheckCaratteri ("ufficialeRiscossione2") == false) return false;
	if (CheckCaratteri ("nomeUfficialeRisc2") == false) return false;
	if (CheckCaratteri ("modalitaFirma") == false) return false;

	if (CheckCaratteri ("info_1_Titolo") == false) return false;
	if (CheckCaratteri ("info_1_Testo") == false) return false;

	if (CheckCaratteri ("CDS_Titolo") == false) return false;
	if (CheckCaratteri ("CDS_Testo_1") == false) return false;
	if (CheckCaratteri ("CDS_Testo_2") == false) return false;
	if (CheckCaratteri ("CDS_Testo_3") == false) return false;
	if (CheckCaratteri ("Tributo_Titolo") == false) return false;
	if (CheckCaratteri ("Tributo_Testo") == false) return false;
	
	if (CheckCaratteri ("info_2_Titolo") == false) return false;
	if (CheckCaratteri ("info_2_Testo") == false) return false;

	if (CheckCaratteri ("avviso_Titolo") == false) return false;
	if (CheckCaratteri ("esito_1_Testo") == false) return false;
	if (CheckCaratteri ("caso_A_Testo") == false) return false;
	if (CheckCaratteri ("caso_B_Testo") == false) return false;
	if (CheckCaratteri ("caso_C_Testo") == false) return false;
	if (CheckCaratteri ("caso_D_Testo") == false) return false;
	if (CheckCaratteri ("esito_2_Testo") == false) return false;
	if (CheckCaratteri ("esito_3_Testo") == false) return false;
	if (CheckCaratteri ("esito_4_Testo") == false) return false;

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

function radioClick(value)
{
	if(value==1)
	{
		$('#gestore').show();
		$('#ente_lay').hide();
	}
	else
	{
		$('#gestore').hide();
		$('#ente_lay').show();
	}
}

</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){

	$("#submit_click").click( salva_form );
	
        
    $("#delete_click").click( cancella_form );
	
	$('#form_preavviso_ingiunzione').ajaxForm(
	    function(value) {
			var array_ritorno = value.split(' ');
	        
			if(array_ritorno[0]=='SAVED')
			{		
				alert('Testo salvato correttamente!');
			}
			else if(array_ritorno[0]=='ERROR')
			{
				alert('Salvataggio testo fallito!');
			}
			else 
			{
				alert('Salvataggio fallito: errore:  ' + value);
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
		<td><font class="titolo font16 under_decor">Testo Preavviso di Ingiunzione</font></td>
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

<form name="stampe_notifiche" id="form_preavviso_ingiunzione" action="testo_preavviso_ingiunzione_salva.php" method="get">

<input name=invia_submit  id=invia_submit	type=hidden	value="" >

<input type="hidden" name="c" value="<?php echo $c?>">
<input type="hidden" name="a" value="<?php echo $a?>">

<table class="table_interna text_center" border="0">
<tr>
	<td class="width20">
		<div>
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
		<div id="oggettoPreavviso">Oggetto Preavviso:</div>
	</td>
	<td class="width4">
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="oggettoPreavviso" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$oggettoPreavviso?></textarea>
	</td>
	<td class="width20">
		<font size=-2>
			{ENTE}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="primoTesto">Primo testo:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="primoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$primoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="secondoTesto">Secondo testo:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="secondoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$secondoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		{SALDO}<br>
		{GIORNI}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="sommaTesto">Titolo Importi:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="sommaTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$sommaTesto?></textarea>
	</td>
	<td>
		<font size=-2>
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
		<div id="terzoTesto">Testo Finale 1:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="terzoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$terzoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
			{NUMEROCONTO}<br>{INTESTATARIOCONTO}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="quartoTesto">Testo Finale 2:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="quartoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$quartoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="salutiTesto">Saluti:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="salutiTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$salutiTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="ufficialeRiscossione">Titolo Soggetto 1:</div>
	</td>
	<td>
		<font size=-2>DES</font>
	</td>
	<td>
		<textarea name="ufficialeRiscossione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$ufficialeRiscossione?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="nomeUfficialeRisc">Nome Soggetto 1:</div>
	</td>
	<td>
		<font size=-2>DES</font>
	</td>
	<td>
		<textarea name="nomeUfficialeRisc" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$nomeUfficialeRisc?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="ufficialeRiscossione2">Titolo Soggetto 2:</div>
	</td>
	<td>
		<font size=-2>SIN</font>
	</td>
	<td>
		<textarea name="ufficialeRiscossione2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$ufficialeRiscossione2?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="nomeUfficialeRisc2">Nome Soggetto 2:</div>
	</td>
	<td>
		<font size=-2>SIN</font>
	</td>
	<td>
		<textarea name="nomeUfficialeRisc2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$nomeUfficialeRisc2?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="nomeUfficialeRisc">Modalita' di stampa e firma:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="modalitaFirma" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$modalitaFirma?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td colspan="4">
		<hr>
	</td>
</tr>
<tr>
	<td>
		<div id="info_1_Titolo">Informazioni 1:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="info_1_Titolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$info_1_Titolo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="info_1_Testo"></div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="info_1_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$info_1_Testo?></textarea>
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
	<td>Stampato se CDS</td>
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
		<div id="CDS_Titolo">Titolo CDS:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="CDS_Titolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$CDS_Titolo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="CDS_Testo_1"></div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="CDS_Testo_1" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$CDS_Testo_1?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="CDS_Testo_2"></div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="CDS_Testo_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$CDS_Testo_2?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="CDS_Testo_3"></div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="CDS_Testo_3" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$CDS_Testo_3?></textarea>
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
	<td>Stampato se tributo</td>
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
		<div id="Tributo_Titolo">Titolo tributo:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="Tributo_Titolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$tributo_titolo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="Tributo_Testo"></div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="Tributo_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$tributo_testo?></textarea>
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
	<td>
		<div id="info_2_Titolo">Informazioni 2:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="info_2_Titolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$info_2_Titolo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="info_2_Testo"></div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="info_2_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$info_2_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="info_3_Titolo">Informazioni 3:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="info_3_Titolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$info_3_Titolo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="info_3_Testo"></div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="info_3_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$info_3_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="avviso_Titolo">Avviso:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="avviso_Titolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$avviso_Titolo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="esito_1_Testo">Esito 1:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="esito_1_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$esito_1_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="caso_A_Testo">Caso A:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="caso_A_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$caso_A_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="caso_B_Testo">Caso B:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="caso_B_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$caso_B_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="caso_C_Testo">Caso C:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="caso_C_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$caso_C_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="caso_D_Testo">Caso D:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="caso_D_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$caso_D_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="esito_2_Testo">Esito 2:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="esito_2_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$esito_2_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="esito_3_Testo">Esito 3:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="esito_3_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$esito_3_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="esito_4_Testo">Esito 4:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="esito_4_Testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$esito_4_Testo?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
</table>

</form>

<br>

</td>
</tr>
</table>

</body>
</html>