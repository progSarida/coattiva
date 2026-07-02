<?php

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";*/
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
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

/*$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$gestore = $comune->Gestore_ID;*/

$layout = "";

/*if ($gestore == 0) $tipoEnte = "Gestito dal Comune di ".$nome_com;
else $tipoEnte = "Gestito da ".$comune->Gestore->Denominazione;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];*/

//$myParametroAtto = new parametri_testo_ingiunzione(NULL);
$myId = $cls_testi->CercaParametroData($c, date("Y-m-d"));

$query = "SELECT * FROM parametri_testo_ingiunzione WHERE ID = '" . $myId."' ";
$myParametroAtto = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"parametri_testo_ingiunzione");

//$myParametroAtto = new parametri_testo_ingiunzione($myId);
//print_r($myParametroAtto);

$titoloIngiunzione = $myParametroAtto["Titolo_Ingiunzione"];
$sottotitoloIngiunzione = $myParametroAtto["Sottotitolo_Ingiunzione"];
$primoTesto = $myParametroAtto["Primo_Testo"];
$premessoTitolo = $myParametroAtto["Premesso"];
$premessoTesto = $myParametroAtto["Premesso_Testo"];
$secondoTesto = $myParametroAtto["Secondo_Testo"];
$terzoTesto = $myParametroAtto["Terzo_Testo"];
$Ingiunge = $myParametroAtto["Ingiunge"];
$ingiungeTesto = $myParametroAtto["Ingiunge_Testo"];
$finalePagina1 = $myParametroAtto["Finale_Pagina_1"];
$Qualifica_Firma_1 = $myParametroAtto["Qualifica_Firma_Sinistra"];
$Firma_1 = $myParametroAtto["Firma_Sinistra"];
$Qualifica_Firma_2 = $myParametroAtto["Qualifica_Firma_Destra"];
$Firma_2 = $myParametroAtto["Firma_Destra"];

$Informazioni = $myParametroAtto["Informazioni"];
$informazioniTesto = $myParametroAtto["Informazioni_Testo"];

$totaleComplex1 = $myParametroAtto["Totale_1"];
$testoTotaleComplex1 = $myParametroAtto["Testo_Totale_1"];
$totaleComplex2 = $myParametroAtto["Totale_2"];
$testoTotaleComplex2 = $myParametroAtto["Testo_Totale_2"];
$TotComplessivo = $myParametroAtto["Totale_Complessivo"];
$testoTotComplessivo = $myParametroAtto["Totale_Complessivo_Testo"];
$Diritto_Riscossione = $myParametroAtto["Diritto_Riscossione"];
$Diritto_Riscossione_Testo = $myParametroAtto["Diritto_Riscossione_Testo"];

$opposizioneTitolo = $myParametroAtto["Opposizione"];
$creditiTributari = $myParametroAtto["Crediti_Tributari"];
$creditiNonTributari = $myParametroAtto["Crediti_Non_Tributari"];
$provvedimentoTitolo = $myParametroAtto["Provvedimento"];
$esecutivitaTitolo = $myParametroAtto["Esecutivita"];
$testOpposizione = $myParametroAtto["Opposizione_Testo"];
$testoProvvedimento = $myParametroAtto["Provvedimento_Testo"];
$testoEsecutivita = $myParametroAtto["Esecutivita_Testo"];
$primoTestoPagamento = $myParametroAtto["Pagamento_Primo_Testo"];
$secondoTestoPagamento = $myParametroAtto["Pagamento_Secondo_Testo"];
$primoTestoAvvertenza = $myParametroAtto["Avvertenza_Primo_Testo"];
$secondoTestoAvvertenza = $myParametroAtto["Avvertenza_Secondo_Testo"];
$terzoTestoAvvertenza = $myParametroAtto["Avvertenza_Terzo_Testo"];

$primoTestoTribunale = $myParametroAtto["Tribunale_Primo_Testo"];

$relazioneTestoPosta = $myParametroAtto["Relazione_Testo_Posta"];
$relazioneTestoMani = $myParametroAtto["Relazione_Testo_Mani"];
$relazioneTestoPEC = $myParametroAtto["Relazione_Testo_PEC"];

$intRiscossioneDiretta = $myParametroAtto["Intestazione_Riscossione_Diretta"];
$relataRiscossioneDiretta = $myParametroAtto["Riscossione_Diretta"];

$intRelataRiscossione = $myParametroAtto["Intestazione_Relata_Ufficiale_Riscossione"];
$relataRiscossione = $myParametroAtto["Relata_Ufficiale_Riscossione"];
$intRelataGiudiziario = $myParametroAtto["Intestazione_Relata_Ufficiale_Giudiziario"];
$sottoIntRelataGiudiziario = $myParametroAtto["Sottointestazione_Relata_Ufficiale_Giudiziario"];
$relataGiudiziario = $myParametroAtto["Relata_Ufficiale_Giudiziario"];
$Firma_Notifica = $myParametroAtto["Firma_Notifica"];
$Qualifica_Firma_Notifica = $myParametroAtto["Qualifica_Firma_Notifica"];

?>

<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>-->


<!-- ********** GESTIONE LINK MENU ********** -->
<script>

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
       			$("#form_testo_ingiunzione").submit();
		}
  }
}


//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "testo_ingiunzione.php?"+stringaPHP;
	   	top.location.href = stringa;
}


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
	{
		link = "testo_preavviso_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		top.location.href = link;
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		link = "testo_sollecito_ingiunzione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		top.location.href = link;
	}
	else
		alert("salvare i dati o annullare prima di procedere");
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
	if (ParolaPresenteInStringa ("titoloIngiunzione", "{INGIUNZIONE}") == false) return false;
	if (ParolaPresenteInStringa ("titoloIngiunzione", "{IDCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("titoloIngiunzione", "{ANNOCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("titoloIngiunzione", "{RIFERIMENTO}") == false) return false;
	if (ParolaPresenteInStringa ("sottotitoloIngiunzione", "{TIPORISCOSSIONE}") == false) return false;
	if (ParolaPresenteInStringa ("sottotitoloIngiunzione", "{ENTEGESTITO}") == false) return false;
	if (ParolaPresenteInStringa ("primoTesto", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("premessoTesto", "{SOGGETTO}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTesto", "{NUMGIORNI}") == false) return false;
	if (ParolaPresenteInStringa ("terzoTesto", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("ingiungeTesto", "{SOGGETTO}") == false) return false;
	// if (ParolaPresenteInStringa ("ingiungeTesto", "{NUMEROCONTO}") == false) return false;
	// if (ParolaPresenteInStringa ("ingiungeTesto", "{INTESTATARIOCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("ingiungeTesto", "{NUMGIORNI}") == false) return false;
	if (ParolaPresenteInStringa ("ingiungeTesto", "{DATANOTIFICA}") == false) return false;

	var array_parole = ['{FUNZIONARIORESPONSABILE}','{RESPONSABILEPROCEDIMENTO}'];

	if (ArrayInStringa ("qualifica_firma_1", array_parole) == false) return false;
	if (ArrayInStringa ("firma_1", array_parole) == false) return false;
	if (ArrayInStringa ("qualifica_firma_2", array_parole) == false) return false;
	if (ArrayInStringa ("firma_2", array_parole) == false) return false;

	if (ParolaPresenteInStringa ("testoPrimoTotale", "{GIORNIDIRITTO}") == false) return false;
	if (ParolaPresenteInStringa ("testoSecondoTotale", "{GIORNIDIRITTO}") == false) return false;
	if (ParolaPresenteInStringa ("totaleComplessivoTesto", "{SPESENOTIFICA}") == false) return false;
	if (ParolaPresenteInStringa ("totaleComplessivoTesto", "{SPESEATTIGIUDIZIARI}") == false) return false;

	// if (ParolaPresenteInStringa ("totaleComplessivoTesto", "{CAD}") == false) return false;
	if (ParolaPresenteInStringa ("dirittoRiscossioneTesto", "{GIORNIDIRITTO}") == false) return false;
	if (ParolaPresenteInStringa ("dirittoRiscossioneTesto", "{DIRITTOMINIMO}") == false) return false;
	if (ParolaPresenteInStringa ("dirittoRiscossioneTesto", "{DIRITTOMASSIMO}") == false) return false;

//	if (ParolaPresenteInStringa ("creditiTributari", "{TERMINICTP}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiTributari", "{CTP}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiTributari", "{SEDECTP}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiTributari", "{RECAPITICTP}") == false) return false;
//
//	if (ParolaPresenteInStringa ("creditiNonTributari", "{TERMINIGIUSTORD}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiNonTributari", "{TRIBUNALE}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiNonTributari", "{SEDETRIBUNALE}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiNonTributari", "{RECAPITITRIBUNALE}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiNonTributari", "{GDP}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiNonTributari", "{SEDEGDP}") == false) return false;
//	if (ParolaPresenteInStringa ("creditiNonTributari", "{RECAPITIGDP}") == false) return false;

	if (ParolaPresenteInStringa ("provvedimentoTesto", "{NUMGIORNI}") == false) return false;
	if (ParolaPresenteInStringa ("primoTestoPagamento", "{TERMINIINGIUNZIONE}") == false) return false;
	// if (ParolaPresenteInStringa ("primoTestoPagamento", "{NUMEROCONTO}") == false) return false;
	// if (ParolaPresenteInStringa ("primoTestoPagamento", "{INTESTATARIOCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{IDCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{ANNOCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{RIFERIMENTO}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTestoPagamento", "{COMUNEGESTITO}") == false) return false;
	if (ParolaPresenteInStringa ("primoTestoAvvertenza", "{NUMGIORNI}") == false) return false;
//	if (ParolaPresenteInStringa ("secondoTestoAvvertenza", "{NUMEROFAX}") == false) return false;
	if (ParolaPresenteInStringa ("secondoTestoAvvertenza", "{SOGGETTO}") == false) return false;

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
	for(var j=0; j< array_parole.length; j++ )
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
	if (CheckCaratteri ("ingiunge") == false) return false;
	if (CheckCaratteri ("ingiungeTesto") == false) return false;
	if (CheckCaratteri ("finalePagina1") == false) return false;
	if (CheckCaratteri ("primoTotale") == false) return false;
	if (CheckCaratteri ("testoPrimoTotale") == false) return false;
	if (CheckCaratteri ("secondoTotale") == false) return false;
	if (CheckCaratteri ("testoSecondoTotale") == false) return false;
	if (CheckCaratteri ("totaleComplessivo") == false) return false;
	if (CheckCaratteri ("totaleComplessivoTesto") == false) return false;
	if (CheckCaratteri ("dirittoRiscossione") == false) return false;
	if (CheckCaratteri ("dirittoRiscossioneTesto") == false) return false;
	if (CheckCaratteri ("opposizioneTesto") == false) return false;
	if (CheckCaratteri ("creditiTributari") == false) return false;
	if (CheckCaratteri ("creditiNonTributari") == false) return false;
	if (CheckCaratteri ("informazioni") == false) return false;
	if (CheckCaratteri ("informazioniTesto") == false) return false;

	if (CheckCaratteri ("provvedimentoTesto") == false) return false;
	if (CheckCaratteri ("esecutivitaTesto") == false) return false;
	if (CheckCaratteri ("primoTestoPagamento") == false) return false;
	if (CheckCaratteri ("secondoTestoPagamento") == false) return false;
	if (CheckCaratteri ("primoTestoAvvertenza") == false) return false;
	if (CheckCaratteri ("secondoTestoAvvertenza") == false) return false;
	if (CheckCaratteri ("terzoTestoAvvertenza") == false) return false;

	if (CheckCaratteri ("IntestazioneUffRiscossione") == false) return false;
	if (CheckCaratteri ("UffRiscossione") == false) return false;
	if (CheckCaratteri ("IntestazioneUffGiudiziario") == false) return false;
	if (CheckCaratteri ("SottoIntestazioneUffGiudiziario") == false) return false;
	if (CheckCaratteri ("UffGiudiziario") == false) return false;

	if (CheckCaratteri ("qualifica_firma_1") == false) return false;
	if (CheckCaratteri ("qualifica_firma_2") == false) return false;
	if (CheckCaratteri ("firma_1") == false) return false;
	if (CheckCaratteri ("firma_2") == false) return false;

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

	$('#form_testo_ingiunzione').ajaxForm(

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

<!--<body class="sfondo_new_gitco" >

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
<td valign=top>-->

<?php // include MENU . '/menu_generale.php'; ?>
<!--
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
</table>-->

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Testo Ingiunzione</font></td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font12">Intestazione</font></td>
	</tr>
</table>

<table class="table_interna text_center borderino" border="0" cellspacing="5" cellpadding="0">
	<tr >
		<td class="width20">Logo Gestore</td>
		<td class="width4"></td>
		<td class="width46 text_left">Dati Gestore</td>
		<td class="width30 text_left">Dati Ufficio</td>
	</tr>
    <tr>
		<td colspan=4><br></td>
	</tr>
	<tr >
		<td class="width20">Riferimenti<br>Protocollo</td>
		<td class="width4"></td>
		<td class="width46 text_left"></td>
		<td class="width30 text_left">Destinatario</td>
	</tr>
</table>

<br>

<form name="testo_ingiunzione" id="form_testo_ingiunzione" action="testo_ingiunzione_salva.php" method="post">

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
			<div id="titoloIngiunzione">Titolo documento:</div>
		</td>
		<td class="width4">
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="titoloIngiunzione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$titoloIngiunzione?></textarea>
		</td>
		<td>
			<font size=-2>
			{INGIUNZIONE}<br>{IDCRONOLOGICO}&nbsp;<br>{ANNOCRONOLOGICO}&nbsp;<br>{RIFERIMENTO}
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
			<textarea name="sottotitoloIngiunzione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$sottotitoloIngiunzione?></textarea>
		</td>
		<td>
			<font size=-2>
			{TIPORISCOSSIONE}&nbsp;<br>{ENTEGESTITO}
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
			{GESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="premessoTitolo">Premesso:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="premessoTitolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$premessoTitolo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="premessoTesto">Premesso testo:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="premessoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$premessoTesto?></textarea>
		</td>
		<td>
			<font size=-2>
			{SOGGETTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="secondoTesto">Seconda parte:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="secondoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$secondoTesto?></textarea>
		</td>
		<td>
			<font size=-2>
			{NUMGIORNI}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="terzoTesto">Terza parte:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="terzoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$terzoTesto?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ingiunge">Ingiunge:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="ingiunge" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Ingiunge?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ingiungeTesto">Ingiunge testo:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="ingiungeTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$ingiungeTesto?></textarea>
		</td>
		<td>
			<font size=-2>
			{SOGGETTO}<br>{IBAN}<br>{NUMEROCONTO}<br>{INTESTATARIOCONTO}<br>{NUMGIORNI}<br>{DATANOTIFICA}
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
			<div id="finalePagina1">Finale prima pagina:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="finalePagina1" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$finalePagina1?></textarea>
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
			<div id="informazioni">Informazioni:</div>
		</td>
		<td>
			<font size=-2>CEN</font>
		</td>
		<td>
			<textarea name="informazioni" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Informazioni?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informazioniTesto">Informazioni testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informazioniTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$informazioniTesto?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td colspan=4 class="text_center"><hr></td>
	</tr>
	<tr><td colspan=4>I totali sottoindicati vengono stampati solo se nei parametri e' stata impostata l'applicazione del diritto di riscossione previsto dall'art. 17 comma 3 del D.lgs. 112/99</td></tr>
	<tr><td colspan=4><hr></td></tr>
	<tr>
		<td>
			<div id="primoTotale">Primo totale complessivo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="primoTotale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$totaleComplex1?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="testoPrimoTotale">Testo primo totale complessivo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="testoPrimoTotale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$testoTotaleComplex1?></textarea>
		</td>
		<td>
			<font size=-2>
			{GIORNIDIRITTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="secondoTotale">Secondo totale complessivo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="secondoTotale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$totaleComplex2?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="testoSecondoTotale">Testo secondo totale complessivo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="testoSecondoTotale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$testoTotaleComplex2?></textarea>
		</td>
		<td>
			<font size=-2>
			{GIORNIDIRITTO}
			</font>
		</td>
	</tr>
	<tr><td colspan=4><hr></td></tr>
	<tr>
		<td>
			<div id="totaleComplessivo">Totale complessivo:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="totaleComplessivo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$TotComplessivo?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="totaleComplessivoTesto">Totale complessivo testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="totaleComplessivoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$testoTotComplessivo?></textarea>
		</td>
		<td>
			<font size=-2>
			{SPESENOTIFICA}<br>{SPESEATTIGIUDIZIARI}<br>{CAD}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="dirittoRiscossione">Diritto riscossione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="dirittoRiscossione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Diritto_Riscossione?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="dirittoRiscossioneTesto">Diritto riscossione testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="dirittoRiscossioneTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Diritto_Riscossione_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{GIORNIDIRITTO}<br>{DIRITTOMINIMO}<br>{GIORNIDIRITTO}<br>{DIRITTOMASSIMO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="opposizioneTitolo">Opposizione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="opposizioneTitolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$opposizioneTitolo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="opposizioneTesto">Opposizione testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="opposizioneTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$testOpposizione?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="creditiTributari">Crediti di natura tributaria:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="creditiTributari" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$creditiTributari?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERMINICTP}<br>{CTP}<br>{SEDECTP}<br>{RECAPITICTP}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="creditiNonTributari">Crediti di natura non tributaria:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="creditiNonTributari" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$creditiNonTributari?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERMINIGIUSTORD}<br>{TRIBUNALE}<br>{SEDETRIBUNALE}<br>{RECAPITITRIBUNALE}<br>
			{GDP}<br>{SEDEGDP}<br>{RECAPITIGDP}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="provvedimentoTitolo">Riesame del provvedimento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="provvedimentoTitolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$provvedimentoTitolo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="provvedimentoTesto">Riesame del provvedimento testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="provvedimentoTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$testoProvvedimento?></textarea>
		</td>
		<td>
			<font size=-2>
			{NUMGIORNI}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="esecutivitaTitolo">Esecutivita':</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="esecutivitaTitolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$esecutivitaTitolo?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="esecutivitaTesto">Esecutivita' testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="esecutivitaTesto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$testoEsecutivita?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="primoTestoPagamento">Primo testo pagamento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="primoTestoPagamento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$primoTestoPagamento?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERMINIINGIUNZIONE}<br>{IBAN}<br>{NUMEROCONTO}&nbsp;<br>{INTESTATARIOCONTO}<br>{CODICEUTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="secondoTestoPagamento">Secondo testo pagamento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="secondoTestoPagamento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$secondoTestoPagamento?></textarea>
		</td>
		<td>
			<font size=-2>
                {TIPOTRIBUTO}<br>{IDCRONOLOGICO}&nbsp;<br>{ANNOCRONOLOGICO}&nbsp;<br>{RIFERIMENTO}&nbsp;<br>{COMUNEGESTITO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="primoTestoAvvertenza">Primo testo avvertenza:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="primoTestoAvvertenza" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$primoTestoAvvertenza?></textarea>
		</td>
		<td>
			<font size=-2>
			{NUMGIORNI}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="secondoTestoAvvertenza">Secondo testo avvertenza:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="secondoTestoAvvertenza" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$secondoTestoAvvertenza?></textarea>
		</td>
		<td>
			<font size=-2>
			{NUMEROFAX}&nbsp;<br>{SOGGETTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="terzoTestoAvvertenza">Terzo testo avvertenza:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="terzoTestoAvvertenza" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$terzoTestoAvvertenza?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr><td colspan=4><hr></td></tr>
    <tr><td colspan=4>Se Riscossione Diretta</td></tr>
    <tr><td colspan=4><hr></td></tr>
    <tr>
        <td>
            <div id="IntestazioneRiscossioneDiretta">Intestazione riscossione diretta:</div>
        </td>
        <td>
            <font size=-2>CENT</font>
        </td>
        <td>
            <textarea name="IntestazioneRiscossioneDiretta" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$intRiscossioneDiretta?></textarea>
        </td>
        <td>
            <font size=-2>

            </font>
        </td>
    </tr>
    <tr>
        <td>
            <div id="RiscossioneDiretta">Relata riscossione diretta:</div>
        </td>
        <td>
            <font size=-2>GIUS</font>
        </td>
        <td>
            <textarea name="RiscossioneDiretta" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$relataRiscossioneDiretta?></textarea>
        </td>
        <td>
            <font size=-2>
                {SOGGETTO}
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
			<font size=-2>GIUS</font>
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
			<font size=-2>GIUS</font>
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
<br>

</form>



<?php echo $layout; ?>

<?php include(INC."/footer.php"); ?>
