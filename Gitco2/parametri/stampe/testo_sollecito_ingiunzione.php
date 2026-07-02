<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/Gitco2/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(INC."/menu.php");
include_once(CLS."/cls_testiUtils.php");

$cls_testi = new cls_testiUtils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

//$myParametroAtto = new parametri_testo_sollecito_ingiunzione(NULL);
$myId = $cls_testi->CercaParametroDataSollecito($c, date("Y-m-d"));

$query = "SELECT * FROM parametri_testo_sollecito_ingiunzione WHERE ID = '" . $myId."' ";
$myParametroAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_testo_sollecito_ingiunzione");//new parametri_testo_sollecito_ingiunzione($myId);

$oggetto = $myParametroAtto->Oggetto;
$sottotitolo = $myParametroAtto->Sottotitolo;
$primoTesto = $myParametroAtto->Primo_Testo;
$pagamento = $myParametroAtto->Pagamento;
$coazione = $myParametroAtto->Coazione;
$coazione1 = $myParametroAtto->Coazione_Caso_1;
$coazione2 = $myParametroAtto->Coazione_Caso_2;
$coazione3 = $myParametroAtto->Coazione_Caso_3;
$coazione4 = $myParametroAtto->Coazione_Caso_4;
$datiGestore = $myParametroAtto->Dati_Gestore;
$rateizzazione = $myParametroAtto->Rateizzazione;
$alternativa = $myParametroAtto->Alternativa;
$informativa = $myParametroAtto->Informativa;
$saluti = $myParametroAtto->Saluti;
$primoResp = $myParametroAtto->Primo_Responsabile;
$primaFirma = $myParametroAtto->Nome_Primo_Responsabile;
$secondoResp = $myParametroAtto->Secondo_Responsabile;
$secondaFirma = $myParametroAtto->Nome_Secondo_Responsabile;
$firmaAutografa = $myParametroAtto->Firma_Autografa;

?>
 
<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F2
function cambia_F2()
{
	return true;
}

//F3
switchMenuImg("F3");
F3_button = function()
{
    if (CheckCampiObbligatori () == true)
    {
        if (CheckTuttiCampi () == true)
        {
            control_salva = submit_buttons('Salva');
            if(control_salva)
                $("#form_testo_sollecito_ingiunzione").submit();
        }
    }
}

//F5
switchMenuImg("F5");
F5_button = function()
{
    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    stringa = "testo_sollecito_ingiunzione.php?"+stringaPHP;
    top.location.href = stringa;
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
    top.location.href = "testo_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
    top.location.href = "testo_avviso_intimazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F11-F12 sono nel menu'

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
// 	if (ParolaPresenteInStringa ("titoloIngiunzione", "{IDCRONOLOGICO}") == false) return false;
// 	if (ParolaPresenteInStringa ("titoloIngiunzione", "{ANNOCRONOLOGICO}") == false) return false;
// 	if (ParolaPresenteInStringa ("titoloIngiunzione", "{RIFERIMENTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("sottotitoloIngiunzione", "{TIPORISCOSSIONE}") == false) return false;
// 	if (ParolaPresenteInStringa ("sottotitoloIngiunzione", "{COMUNEGESTITO}") == false) return false;
// 	if (ParolaPresenteInStringa ("primoTesto", "{GESTORE}") == false) return false;
// 	if (ParolaPresenteInStringa ("premessoTesto", "{SOGGETTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTesto", "{NUMGIORNI}") == false) return false;
// 	if (ParolaPresenteInStringa ("terzoTesto", "{GESTORE}") == false) return false;
// 	if (ParolaPresenteInStringa ("ingiunge", "{NUMEROCONTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("ingiunge", "{INTESTATARIOCONTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("ingiunge", "{NUMGIORNI}") == false) return false;
// 	if (ParolaPresenteInStringa ("opposizione", "{NUMGIORNI}") == false) return false;
// 	if (ParolaPresenteInStringa ("opposizione", "{GIUDICEDIPACE}") == false) return false;
// 	if (ParolaPresenteInStringa ("provvedimento", "{NUMGIORNI}") == false) return false;
// 	if (ParolaPresenteInStringa ("primoTestoPagamento", "{NUMGIORNI}") == false) return false;
// 	if (ParolaPresenteInStringa ("primoTestoPagamento", "{NUMEROCONTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("primoTestoPagamento", "{INTESTATARIOCONTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{IDCRONOLOGICO}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{ANNOCRONOLOGICO}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{RIFERIMENTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{COMUNEGESTITO}") == false) return false;
// 	if (ParolaPresenteInStringa ("primoTestoAvvertenza", "{NUMGIORNI}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoAvvertenza", "{NUMEROFAX}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoAvvertenza", "{SOGGETTO}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoTribunale", "{DESTINATARIO}") == false) return false;
// 	if (ParolaPresenteInStringa ("secondoTestoTribunale", "{INDIRIZZODESTINATARIO}") == false) return false;
	
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
// 	if (CheckCaratteri ("titoloIngiunzione") == false) return false;
// 	if (CheckCaratteri ("sottotitoloIngiunzione") == false) return false;
// 	if (CheckCaratteri ("primoTesto") == false) return false;
// 	if (CheckCaratteri ("premessoTesto") == false) return false;
// 	if (CheckCaratteri ("secondoTesto") == false) return false;
// 	if (CheckCaratteri ("terzoTesto") == false) return false;	
// 	if (CheckCaratteri ("ingiunge") == false) return false;
// 	if (CheckCaratteri ("finalePagina1") == false) return false;
// 	if (CheckCaratteri ("primoResponsabile") == false) return false;
// 	if (CheckCaratteri ("primaFirma") == false) return false;
// 	if (CheckCaratteri ("secondoResponsabile") == false) return false;
// 	if (CheckCaratteri ("secondaFirma") == false) return false;
// 	if (CheckCaratteri ("primoTotale") == false) return false;
// 	if (CheckCaratteri ("secondoTotale") == false) return false;
// 	if (CheckCaratteri ("opposizione") == false) return false;
// 	if (CheckCaratteri ("provvedimento") == false) return false;
// 	if (CheckCaratteri ("esecutivita") == false) return false;
// 	if (CheckCaratteri ("primoTestoPagamento") == false) return false;
// 	if (CheckCaratteri ("secondoTestoPagamento") == false) return false;
// 	if (CheckCaratteri ("primoTestoAvvertenza") == false) return false;
// 	if (CheckCaratteri ("secondoTestoAvvertenza") == false) return false;
// 	if (CheckCaratteri ("terzoTestoAvvertenza") == false) return false;
// 	if (CheckCaratteri ("primoTestoTribunale") == false) return false;
// 	if (CheckCaratteri ("secondoTestoTribunale") == false) return false;
		
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
	
	$('#form_testo_sollecito_ingiunzione').ajaxForm(
			
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

	    });
    
});

</script>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Testo Sollecito Ingiunzione</font></td>
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

<form name="testo_sollecito_ingiunzione" id="form_testo_sollecito_ingiunzione" action="testo_sollecito_ingiunzione_salva.php" method="get">

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
			<div id="oggetto">Oggetto:</div>
		</td>
		<td class="width4">
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="oggetto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			{IDCRONOLOGICO}&nbsp;<br>{ANNOCRONOLOGICO}&nbsp;<br>{RIFERIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td class="width20">
			<div id="sottotitolo">Sottotitolo:</div>
		</td>
		<td class="width4">
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="sottotitolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$sottotitolo?></textarea>
		</td>
		<td>
			<font size=-2>
			    {TIPORISCOSSIONE}<br>{ENTEGESTITO}
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
			<textarea name="primoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$primoTesto?></textarea>
		</td>
		<td>
			<font size=-2>
			{INFOATTO}<br>{INFOCARTELLA}
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
			<div id="pagamento">Pagamento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="pagamento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$pagamento?></textarea>
		</td>
		<td>
			<font size=-2>
			{INTESTATARIOCONTO}<br>{NUMEROCONTO}<br>{IBAN}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="coazione">Coazione:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="coazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$coazione?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="primoCasoCoazione">Primo caso coazione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="primoCasoCoazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$coazione1?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="secondoCasoCoazione">Secondo caso coazione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="secondoCasoCoazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$coazione2?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="terzoCasoCoazione">Terzo caso coazione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="terzoCasoCoazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$coazione3?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="quartoCasoCoazione">Quarto caso coazione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="quartoCasoCoazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$coazione4?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	
	<tr>
		<td>
			<div id="datiGestore">Dati gestore:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="datiGestore" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$datiGestore?></textarea>
		</td>
		<td>
			<font size=-2>
			{FAX}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="rateizzazione">Rateizzazione:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="rateizzazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$rateizzazione?></textarea>
		</td>
		<td>
			<font size=-2>
			{SITO}<br>{FAX}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="alternativa">Alternativa:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="alternativa" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$alternativa?></textarea>
		</td>
		<td>
			<font size=-2>
			{TELEFONO}<br>{UFFICIOGESTORE}<br>{ORARIO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informativa">Informativa:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informativa" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$informativa?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="saluti">Saluti:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="saluti" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$saluti?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="primoResponsabile">Qualifica firma <br>sinistra:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="primoResponsabile" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$primoResp?></textarea>
		</td>
		<td>
			<font size=-2>
                {FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="primaFirma">Firma sinistra:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="primaFirma" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$primaFirma?></textarea>
		</td>
		<td>
			<font size=-2>
                {FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="secondoResponsabile">Qualifica firma <br>destra:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="secondoResponsabile" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$secondoResp?></textarea>
		</td>
		<td>
			<font size=-2>
                {FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="secondaFirma">Firma destra:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="secondaFirma" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$secondaFirma?></textarea>
		</td>
		<td>
			<font size=-2>
                {FUNZIONARIORESPONSABILE}<br>oppure<br>{RESPONSABILEPROCEDIMENTO}
			</font>
		</td>
	</tr>

</table>
<br>

</form>

<?php include(INC."/footer.php"); ?>