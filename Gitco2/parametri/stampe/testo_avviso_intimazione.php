<?php

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";*/

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include CLS."/cls_testiUtils.php";

if($_SESSION['username']==NULL)
{
    header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$cls_testi = new cls_testiUtils();

$layout = "";

//$myParametroAtto = new parametri_atto_intimazione_ingiunzione(NULL);
$myId = $cls_testi->CercaParametroDataIntimazione($c, date("Y-m-d"));

$query = "SELECT * FROM parametri_atto_intimazione_ingiunzione WHERE ID = '" . $myId."' ";
$myParametroAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_atto_intimazione_ingiunzione");//new parametri_atto_intimazione_ingiunzione($myId);

$titoloIngiunzione = $myParametroAtto->Titolo_Ingiunzione;
$sottotitoloIngiunzione = $myParametroAtto->Sottotitolo_Ingiunzione;
$primoTesto = $myParametroAtto->Primo_Testo;
$premessoTesto = $myParametroAtto->Premesso_Testo;
$secondoTesto = $myParametroAtto->Secondo_Testo;
$terzoTesto = $myParametroAtto->Terzo_Testo;
$intima = $myParametroAtto->Intima;
$intimaTesto = $myParametroAtto->Intima_Testo;
$intimaCaso1 = $myParametroAtto->Intima_Caso_1;
$intimaCaso2 = $myParametroAtto->Intima_Caso_2;
$intimaCaso3 = $myParametroAtto->Intima_Caso_3;
$intimaVersamento = $myParametroAtto->Intima_Versamento;
$infoTesto = $myParametroAtto->Info_Testo;
$finaleTesto = $myParametroAtto->Finale_Testo;
$opposizione = $myParametroAtto->Opposizione;
$opposizioneTesto = $myParametroAtto->Opposizione_Testo;
$Qualifica_Firma_1 = $myParametroAtto->Qualifica_Firma_Sinistra;
$Firma_1 = $myParametroAtto->Firma_Sinistra;
$Qualifica_Firma_2 = $myParametroAtto->Qualifica_Firma_Destra;
$Firma_2 = $myParametroAtto->Firma_Destra;

$modalitaFirma = $myParametroAtto->Modalita_Stampa_Firma;

$intRelataRiscossione = $myParametroAtto->Intestazione_Relata_Ufficiale_Riscossione;
$relataRiscossione = $myParametroAtto->Relata_Ufficiale_Riscossione;
$intRelataGiudiziario = $myParametroAtto->Intestazione_Relata_Ufficiale_Giudiziario;
$sottoIntRelataGiudiziario = $myParametroAtto->Sottointestazione_Relata_Ufficiale_Giudiziario;
$relataGiudiziario = $myParametroAtto->Relata_Ufficiale_Giudiziario;
$Firma_Notifica = $myParametroAtto->Firma_Notifica;
$Qualifica_Firma_Notifica = $myParametroAtto->Qualifica_Firma_Notifica;

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function(){
    if (CheckCampiObbligatori () == true)
    {
        if (CheckTuttiCampi () == true)
        {
            control_salva = submit_buttons('Salva');
            if(control_salva)
                $("#form_atto_intimazione_ingiunzione").submit();
        }
    }
}

//F5
switchMenuImg("F5");
F5_button = function(){
    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    stringa = "testo_avviso_intimazione.php?"+stringaPHP;
    top.location.href = stringa;
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
    if( modifica == 0 )
    {
        pagina_menu('prev');
    }
    else
        alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
    if( modifica == 0 )
    {
        pagina_menu('next');
    }
    else
        alert("salvare i dati o annullare prima di procedere");
}


//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function pagina_menu(value)
{
	switch(value)
	{
		case 'next':

			link = "testo_preavviso_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   		
   			break;

		case 'prev':

			link = "testo_sollecito_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			
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
	if (ParolaPresenteInStringa ("titoloIngiunzione", "{IDCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("titoloIngiunzione", "{ANNOCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("titoloIngiunzione", "{RIFERIMENTO}") == false) return false;

    if (ParolaPresenteInStringa ("sottotitoloIngiunzione", "{ENTEGESTITO}") == false) return false;

	if (ParolaPresenteInStringa ("primoTesto", "{TIPOENTE}") == false) return false;
	if (ParolaPresenteInStringa ("primoTesto", "{INDIRIZZOENTE}") == false) return false;

	if (ParolaPresenteInStringa ("secondoTesto", "{NOMEDESTINATARIO}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTesto", "{RESIDENZADESTINATARIO}") == false) return false;

	if (ParolaPresenteInStringa ("intimaTesto", "{NOMEDESTINATARIO}") == false) return false;
	if (ParolaPresenteInStringa ("intimaTesto", "{RESIDENZADESTINATARIO}") == false) return false;
	if (ParolaPresenteInStringa ("intimaTesto", "{IMPORTOINGSENZASPESE}") == false) return false;
	if (ParolaPresenteInStringa ("intimaTesto", "{SPESE}") == false) return false;
	if (ParolaPresenteInStringa ("intimaTesto", "{PAGAMENTI}") == false) return false;
	
	if (ParolaPresenteInStringa ("intimaCaso1", "{DOVUTOCASO1}") == false) return false;
	if (ParolaPresenteInStringa ("intimaCaso2", "{DOVUTOCASO2}") == false) return false;
	if (ParolaPresenteInStringa ("intimaCaso3", "{DOVUTOCASO3}") == false) return false;
	if (ParolaPresenteInStringa ("intimaCaso3", "{SPESEUFFICIALE}") == false) return false;
	
	if (ParolaPresenteInStringa ("intimaVersamento", "{NUMEROCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("intimaVersamento", "{INTESTATARIOCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("intimaVersamento", "{CAUSALE}") == false) return false;

	var array_parole = ['{FUNZIONARIORESPONSABILE}','{RESPONSABILEPROCEDIMENTO}'];
	
	if (ArrayInStringa ("qualifica_firma_1", array_parole) == false) return false;
	if (ArrayInStringa ("firma_1", array_parole) == false) return false;
	if (ArrayInStringa ("qualifica_firma_2", array_parole) == false) return false;
	if (ArrayInStringa ("firma_2", array_parole) == false) return false;

	if (ParolaPresenteInStringa ("UffRiscossione", "{TIPOENTE}") == false) return false;
	if (ParolaPresenteInStringa ("UffRiscossione", "{DESTINATARIO}") == false) return false;
	if (ParolaPresenteInStringa ("UffRiscossione", "{TIPOINVIO}") == false) return false;

	if (ParolaPresenteInStringa ("UffGiudiziario", "{DESTINATARIO}") == false) return false;
	if (ParolaPresenteInStringa ("UffGiudiziario", "{TIPOINVIO}") == false) return false;
	
	return true;
}

function ArrayInStringa (campo, array_parole)
{
	var namestr = "[name=" + campo + "]";
	var nomecampo = $(namestr);
	var idcampo = $("#" + campo);
	var testoId = idcampo.text();
	var stringa = nomecampo.val();

	control_parola = 0;
	for(var j=0; j < array_parole.length; j++ )
	{
		var i = stringa.indexOf(array_parole[j]);
		if (i != -1)
		{
			control_parola = 1;
			break;
		}
	}

	if(control_parola == 0)
	{
		var message = "Non hai inserito il campo obbligatorio a scelta ";
		message += "nel campo ' ";
		message += testoId;
		message += " '";
		message += ". Copia il campo nella lista a destra e incollalo nel testo.";
		alert (message);
		return false;
	}
	else
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
	if (CheckCaratteri ("titoloIngiunzione") == false) return false;
	if (CheckCaratteri ("sottotitoloIngiunzione") == false) return false;
	if (CheckCaratteri ("primoTesto") == false) return false;
	if (CheckCaratteri ("premessoTesto") == false) return false;
	if (CheckCaratteri ("secondoTesto") == false) return false;
	if (CheckCaratteri ("terzoTesto") == false) return false;
	if (CheckCaratteri ("intima") == false) return false;
	if (CheckCaratteri ("intimaTesto") == false) return false;
	if (CheckCaratteri ("intimaCaso1") == false) return false;
	if (CheckCaratteri ("intimaCaso2") == false) return false;
	if (CheckCaratteri ("intimaCaso3") == false) return false;
	if (CheckCaratteri ("intimaVersamento") == false) return false;
	if (CheckCaratteri ("infoTesto") == false) return false;
	if (CheckCaratteri ("finaleTesto") == false) return false;

	if (CheckCaratteri ("qualifica_firma_1") == false) return false;
	if (CheckCaratteri ("qualifica_firma_2") == false) return false;
	if (CheckCaratteri ("firma_1") == false) return false;
	if (CheckCaratteri ("firma_2") == false) return false;
	if (CheckCaratteri ("IntestazioneUffRiscossione") == false) return false;
	if (CheckCaratteri ("UffRiscossione") == false) return false;
	if (CheckCaratteri ("IntestazioneUffGiudiziario") == false) return false;
	if (CheckCaratteri ("SottoIntestazioneUffGiudiziario") == false) return false;
	if (CheckCaratteri ("UffGiudiziario") == false) return false;

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
		else if (carattere == ' ' || carattere == "'" || carattere == "/") {}
		else if (carattere == '.' || carattere == ',' || carattere == ';' || carattere == ':') {}
		else if (carattere == '+' || carattere == '-' || carattere == '*' || carattere == '_') {}
		else if (carattere == '!' || carattere == '?') {}
		else if (carattere == '<' || carattere == '>') {}
		else if (carattere == '%' || carattere == '@' || carattere == '#') {}
		else if (carattere == '�' || carattere == '$' || carattere == '&' || carattere == '^') {}
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
	
	/*$('#form_atto_intimazione_ingiunzione').ajaxForm(
			
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

	    });*/
    
});

</script>

<?php include $_SERVER['DOCUMENT_ROOT'].'/coattiva/Gitco2/menu/menu_generale.php'; ?>


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

<form name="stampe_notifiche" id="form_atto_intimazione_ingiunzione" action="testo_avviso_intimazione_salva.php" method="get">

<input name=invia_submit  id=invia_submit	type=hidden	value="" >

<input type="hidden" name="c" value="<?php echo $c?>">
<input type="hidden" name="a" value="<?php echo $a?>">

<table class="table_interna text_center" border="0">
<tr>
	<td class="width20">
		<div id="titoloIngiunzione">
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
<tr >
		<td colspan=4><br></td>
	</tr>
<tr>
	<td class="width20">
		<div id="titoloIngiunzione">Titolo documento:</div>
	</td>
	<td class="width4">
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="titoloIngiunzione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$titoloIngiunzione?></textarea>
	</td>
	<td class="width20">
		<font size=-2>
	{IDCRONOLOGICO}&nbsp;<br>{ANNOCRONOLOGICO}&nbsp;<br>{RIFERIMENTO}&nbsp;
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="sottotitoloIngiunzione">Sottotitolo:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="sottotitoloIngiunzione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$sottotitoloIngiunzione?></textarea>
	</td>
	<td>
		<font size=-2>
            {ENTEGESTITO}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="primoTesto">Prima parte:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="primoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$primoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		{TIPOENTE}&nbsp;<br>{INDIRIZZOENTE}&nbsp;
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="premessoTesto">Premesso:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="premessoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$premessoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="secondoTesto">Dati notifica:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="secondoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$secondoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		{NOMEDESTINATARIO}&nbsp;
		<br>{RESIDENZADESTINATARIO}		
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="terzoTesto">Articoli:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="terzoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$terzoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="intima">Intima:</div>
	</td>
	<td>
		<font size=-2>CENT</font>
	</td>
	<td>
		<textarea name="intima" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$intima?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="intimaTesto">Intima testo:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="intimaTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$intimaTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		{NOMEDESTINATARIO}&nbsp;<br>{RESIDENZADESTINATARIO}&nbsp;
		<br>{IMPORTOINGSENZASPESE}&nbsp;
		<br>{SPESE}&nbsp;<br>{PAGAMENTI}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="intimaCaso1">Caso 1:<br>Ritiro destinatario</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="intimaCaso1" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$intimaCaso1?></textarea>
	</td>
	<td>
		<font size=-2>
		{DOVUTOCASO1}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="intimaCaso2">Caso 2:<br>No ritiro destinatario</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="intimaCaso2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$intimaCaso2?></textarea>
	</td>
	<td>
		<font size=-2>
		{DOVUTOCASO2}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="intimaCaso3">Caso 3:<br>Consegna a mani</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="intimaCaso3" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$intimaCaso3?></textarea>
	</td>
	<td>
		<font size=-2>
		{DOVUTOCASO3}&nbsp;<br>{SPESEUFFICIALE}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="intimaVersamento">Versamento:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="intimaVersamento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$intimaVersamento?></textarea>
	</td>
	<td>
		<font size=-2>
		{NUMEROCONTO}&nbsp;
		<br>{INTESTATARIOCONTO}&nbsp;<br>{CAUSALE}
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="infoTesto">Informazioni:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="infoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$infoTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="finaleTesto">Articolo:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="finaleTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$finaleTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="opposizione">Opposizione:</div>
	</td>
	<td>
		<font size=-2>CEN</font>
	</td>
	<td>
		<textarea name="opposizione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$opposizione?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
<tr>
	<td>
		<div id="opposizioneTesto">Ricorso:</div>
	</td>
	<td>
		<font size=-2>GIUS</font>
	</td>
	<td>
		<textarea name="opposizioneTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$opposizioneTesto?></textarea>
	</td>
	<td>
		<font size=-2>
		</font>
	</td>
</tr>
	<tr>
		<td>
			<div id="qualifica_firma_1">Qualifica firma <br>sinistra:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="qualifica_firma_1" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Qualifica_Firma_1?></textarea>
		</td>
		<td>
			<font size=-2>
			{FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="firma_1">Firma sinistra:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="firma_1" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Firma_1?></textarea>
		</td>
		<td>
			<font size=-2>
			{FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="qualifica_firma_2">Qualifica firma <br>destra:</div>
		</td>
		<td>
			<font size=-2>DES</font>
		</td>
		<td>
			<textarea name="qualifica_firma_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Qualifica_Firma_2?></textarea>
		</td>
		<td>
			<font size=-2>
			{FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="firma_2">Firma destra:</div>
		</td>
		<td>
			<font size=-2>DES</font>
		</td>
		<td>
			<textarea name="firma_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Firma_2?></textarea>
		</td>
		<td>
			<font size=-2>
			{FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
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
	<tr><td colspan=4><hr></td></tr>
	<tr><td colspan=4>Se Ufficiale della Riscossione</td></tr>
	<tr><td colspan=4><hr></td></tr>
	<tr>
		<td>
			<div id="IntestazioneUffRiscossione">Intestazione relata Ufficiale Riscossione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="IntestazioneUffRiscossione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$intRelataRiscossione?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="UffRiscossione">Relata Ufficiale Riscossione:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="UffRiscossione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$relataRiscossione?></textarea>
		</td>
		<td>
			<font size=-2>
			{TIPOENTE}<br>
			{DESTINATARIO}<br>{TIPOINVIO}
			</font>
		</td>
	</tr>
	<tr><td colspan=4><hr></td></tr>
	<tr><td colspan=4>Se Ufficiale Giudiziario</td></tr>
	<tr><td colspan=4><hr></td></tr>
	<tr>
		<td>
			<div id="IntestazioneUffGiudiziario">Intestazione relata Ufficiale Giudiziario:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="IntestazioneUffGiudiziario" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$intRelataGiudiziario?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="SottoIntestazioneUffGiudiziario">Sottointestazione relata Ufficiale Giudiziario:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="SottoIntestazioneUffGiudiziario" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$sottoIntRelataGiudiziario?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="UffGiudiziario">Relata Ufficiale Giudiziario:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="UffGiudiziario" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$relataGiudiziario?></textarea>
		</td>
		<td>
			<font size=-2>
			{DESTINATARIO}<br>{TIPOINVIO}
			</font>
		</td>
	</tr>
	<tr><td colspan=4><hr></td></tr>
	<tr>
		<td>
			<div id="qualifica_firma_notifica">Qualifica firma <br>notifica:</div>
		</td>
		<td>
			<font size=-2>DES</font>
		</td>
		<td>
			<textarea class="sfondo_grigio text_center" name="qualifica_firma_notifica" readonly style="width:95%" rows="1">NON EDITABILE</textarea>
		</td>
		<td>
			<font size=-2>
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
			<textarea class="sfondo_grigio text_center" name="firma_notifica" readonly style="width:95%" rows="1">NON EDITABILE</textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
</table>

<input type="hidden" id="invia" name="invia" value="">

</form>

<br>

</td>
</tr>
</table>

<?php echo $layout; ?>

</body>
</html>