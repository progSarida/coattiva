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

$tipo_terzo = get_var('tipo_terzo');
$tipo_terzo = "lavoro";

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$gestore = $comune->Gestore_ID;

if ($gestore == 0) $tipoEnte = "Gestito dal Comune di ".$nome_com;
else $tipoEnte = "Gestito da ".$comune->Gestore->Denominazione;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$myParametroAtto = new testo_sollecito_pignoramento_veicolo(null);
$myId = $myParametroAtto->CercaParametroData($c, date("Y-m-d"));

$myParametroAtto = new testo_sollecito_pignoramento_veicolo($myId);

$Titolo_Oggetto = $myParametroAtto->Titolo_Oggetto;
$Articolo_388 = $myParametroAtto->Articolo_388;
$Atto_Informale = $myParametroAtto->Atto_Informale;
$Importo_Dovuto = $myParametroAtto->Importo_Dovuto;
$Informazioni_Ufficio = $myParametroAtto->Informazioni_Ufficio;
$Sistema_Automatizzato = $myParametroAtto->Sistema_Automatizzato;
$Testo_Finale = $myParametroAtto->Testo_Finale;
$Testo_Principale = $myParametroAtto->Testo_Principale;


// $intRelataGiudiziario = $myParametroAtto->Intestazione_Relata_Ufficiale_Giudiziario;
// $sottoIntRelataGiudiziario = $myParametroAtto->Sottointestazione_Relata_Ufficiale_Giudiziario;

// $intRelataRiscossione = $myParametroAtto->Intestazione_Relata_Ufficiale_Riscossione;
// $sottoIntRelataRiscossione = $myParametroAtto->Sottointestazione_Relata_Ufficiale_Riscossione;

// $relataNotifica = $myParametroAtto->Relata_Ufficiale;

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
       			$("#form_testo_sollecito_pignoramento_veicolo").submit();
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
	stringa = "testo_sollecito_pignoramento_veicolo.php?"+stringaPHP;
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
	
	if (ParolaPresenteInStringa ("testo_principale", "{DATIVEICOLO}") == false) return false;
	if (ParolaPresenteInStringa ("testo_principale", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("testo_principale", "{DATANOTIFICA}") == false) return false;
	if (ParolaPresenteInStringa ("testo_principale", "{PECGESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("testo_principale", "{FAXGESTORE}") == false) return false;
		
	if (ParolaPresenteInStringa ("importo_dovuto", "{NUMEROCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("importo_dovuto", "{INTESTATARIOCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("importo_dovuto", "{IMPORTODOVUTO}") == false) return false;

	if (ParolaPresenteInStringa ("informazioni_ufficio", "{TELEFONOUFFICIO}") == false) return false;
	if (ParolaPresenteInStringa ("informazioni_ufficio", "{ENTE}") == false) return false;
	if (ParolaPresenteInStringa ("informazioni_ufficio", "{UFFICIOGESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("informazioni_ufficio", "{ORARIOUFFICIO}") == false) return false;
	
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
	if (CheckCaratteri ("testo_principale") == false) return false;
	if (CheckCaratteri ("articolo_388") == false) return false;
	if (CheckCaratteri ("importo_dovuto") == false) return false;
	if (CheckCaratteri ("informazioni_ufficio") == false) return false;
	if (CheckCaratteri ("atto_informale") == false) return false;
	if (CheckCaratteri ("testo_finale") == false) return false;
	if (CheckCaratteri ("sistema_automatizzato") == false) return false;
		
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
	
	$('#form_testo_sollecito_pignoramento_veicolo').ajaxForm(
			
	    function(value) {
		    var array_ritorno = value.split(' ');
	        
			if(array_ritorno[0]=='SAVED')
			{		
				alert('Testo salvato correttamente!');
				annulla();
			}
			else if(array_ritorno[0]=='ERROR')
			{
				alert('Salvataggio testo fallito! '+value);
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
		<td><font class="titolo font16 under_decor">Testo Pignoramento veicolo</font></td>
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

<form name="testo_sollecito_pignoramento_veicolo" id="form_testo_sollecito_pignoramento_veicolo" action="testo_sollecito_pignoramento_veicolo_salva.php" method="post">

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
			<textarea name="titolo_oggetto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Titolo_Oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			{IDCRONOLOGICO}<br>{ANNOCRONOLOGICO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="testo_principale">Testo principale:</div>
		</td>
		<td>
			<font size=-2>GIUST</font>
		</td>
		<td>
			<textarea name="testo_principale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="16"><?=$Testo_Principale?></textarea>
		</td>
		<td>
			<font size=-2>
			 {DATIVEICOLO}<br>{UTENTE}<br>{DATANOTIFICA}<br>{PECGESTORE}<br>{FAXGESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="articolo_388">Articolo 388:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="articolo_388" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$Articolo_388?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="importo_dovuto">Importo dovuto:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="importo_dovuto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Importo_Dovuto?></textarea>
		</td>
		<td>
			<font size=-2>
			{NUMEROCONTO}<br>{INTESTATARIOCONTO}<br>{IMPORTODOVUTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informazioni_ufficio">Informazioni ufficio:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informazioni_ufficio" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Informazioni_Ufficio?></textarea>
		</td>
		<td>
			<font size=-2>
			{TELEFONOUFFICIO}<br>{ENTE}<br>{UFFICIOGESTORE}<br>{ORARIOUFFICIO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="atto_informale">Atto informale:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="atto_informale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Atto_Informale?></textarea>
		</td>
		<td>
			<font size=-2></font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="testo_finale">Testo finale:</div>
		</td>
		<td>
			<font size=-2>GIUST</font>
		</td>
		<td>
			<textarea name="testo_finale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Testo_Finale?></textarea>
		</td>
		<td>
			<font size=-2></font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="sistema_automatizzato">Sistema informatizzato:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="sistema_automatizzato" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Sistema_Automatizzato?></textarea>
		</td>
		<td>
			<font size=-2></font>
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