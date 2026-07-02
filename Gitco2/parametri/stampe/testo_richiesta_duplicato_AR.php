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

$layout = "";

if ($gestore == 0) $tipoEnte = "Gestito dal Comune di ".$nome_com;
else $tipoEnte = "Gestito da ".$comune->Gestore->Denominazione;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

//PARAMETRI TESTO
$para_testo = new testo_richiesta_duplicato_AR(NULL);
$myId = $para_testo->CercaParametroData($c, date("Y-m-d"));
$testo = new testo_richiesta_duplicato_AR($myId);

$Oggetto = stripslashes($testo->Oggetto);
$Premessa = stripslashes($testo->Premessa);
$Informazioni = stripslashes($testo->Informazioni);
$Richiesta_Duplicato = stripslashes($testo->Richiesta_Duplicato);
$Urgenza_Richiesta = stripslashes($testo->Urgenza_Richiesta);
$Contatti = stripslashes($testo->Contatti);
$Saluti = stripslashes($testo->Saluti);
$Avvertenze = stripslashes($testo->Avvertenze);
$Intestatario = stripslashes($testo->Intestatario_Firma);
$Firma = stripslashes($testo->Firma);

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
       			$("#form_testo_richiesta_duplicato_AR").submit();
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
	stringa = "testo_richiesta_duplicato_AR.php?"+stringaPHP;
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

<!-- ********** CONTROLLI, AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>

function CheckCampiObbligatori ()
 {	
	if (ParolaPresenteInStringa ("Premessa", "{COMUNEGESTITO}") == false) return false;
	if (ParolaPresenteInStringa ("Informazioni", "{INFODESTINATARIO}") == false) return false;
	
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
	if (CheckCaratteri ("Oggetto") == false) return false;
	if (CheckCaratteri ("Premessa") == false) return false;
	if (CheckCaratteri ("Informazioni") == false) return false;
	if (CheckCaratteri ("Richiesta_Duplicato") == false) return false;
	if (CheckCaratteri ("Urgenza_Richiesta") == false) return false;
	if (CheckCaratteri ("Contatti") == false) return false;	
	if (CheckCaratteri ("Saluti") == false) return false;
	if (CheckCaratteri ("Avvertenze") == false) return false;
		
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
		else if (carattere == '+' || carattere == '-' || carattere == '*' || carattere == '_') {}
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
	
	$('#form_testo_richiesta_duplicato_AR').ajaxForm(
			
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
		<td><font class="titolo font16 under_decor">Testo richiesta duplicato AR</font></td>
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

<form name="testo_richiesta_duplicato_AR" id="form_testo_richiesta_duplicato_AR" action="testo_richiesta_duplicato_AR_salva.php" method="get">

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
			<div id="Oggetto">Oggetto:</div>
		</td>
		<td class="width4">
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="Oggetto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td class="width20">
			<div id="Premessa">Premessa:</div>
		</td>
		<td class="width4">
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Premessa" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Premessa?></textarea>
		</td>
		<td>
			<font size=-2>
			{COMUNEGESTITO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Informazioni">Informazioni:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Informazioni" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Informazioni?></textarea>
		</td>
		<td>
			<font size=-2>
			{INFODESTINATARIO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Richiesta_Duplicato">Richiesta duplicato:</div>
		</td>
		<td>
			<font size=-2>CEN</font>
		</td>
		<td>
			<textarea name="Richiesta_Duplicato" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Richiesta_Duplicato?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Urgenza_Richiesta">Urgenza richiesta:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Urgenza_Richiesta" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Urgenza_Richiesta?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Contatti">Contatti:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Contatti" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Contatti?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Saluti">Saluti:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Saluti" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Saluti?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Avvertenze">Avvertenze:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Avvertenze" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Avvertenze?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Intestatario_Firma">Intestatario firma:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Intestatario_Firma" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Intestatario?></textarea>
		</td>
		<td>
			<font size=-2>
			{RESPONSABILERICHIESTA}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="Firma">Firma:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="Firma" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Firma?></textarea>
		</td>
		<td>
			<font size=-2>
			{RESPONSABILERICHIESTA}
			</font>
		</td>
	</tr>
</table>
<br>

</form>

</td>
</tr>
</table>

<?php echo $layout; ?>

</body>
</html>