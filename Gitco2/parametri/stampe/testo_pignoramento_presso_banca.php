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

$myParametroAtto = new testo_pignoramento_presso_banca(null);
$myId = $myParametroAtto->CercaParametroData($c, date("Y-m-d"));

$myParametroAtto = new testo_pignoramento_presso_banca($myId);

//TABELLA 1

$Titolo_Oggetto = $myParametroAtto->Titolo_Oggetto;
$Sottotitolo_Oggetto = $myParametroAtto->Sottotitolo_Oggetto;
$Ufficiale_Responsabile = $myParametroAtto->Ufficiale_Responsabile;
$Abilitazione = $myParametroAtto->Abilitazione;

$Premesso = $myParametroAtto->Premesso;
$Premesso_Testo = $myParametroAtto->Premesso_Testo;

$Atti_Notificati = $myParametroAtto->Atti_Notificati;

$Modalita_Pagamento = $myParametroAtto->Modalita_Pagamento;
$Modalita_Pagamento_Testo = $myParametroAtto->Modalita_Pagamento_Testo;

$Informazioni = $myParametroAtto->Informazioni;
$Informazioni_Testo = $myParametroAtto->Informazioni_Testo;

$Visto = $myParametroAtto->Visto;
$Ingiunzione_Fiscale = $myParametroAtto->Ingiunzione_Fiscale;
$Legislatore = $myParametroAtto->Legislatore;

$Considerato = $myParametroAtto->Considerato;
$Terzo = $myParametroAtto->Terzo;
$Somme_Dovute = $myParametroAtto->Somme_Dovute;
$Ordine_Pagamento = $myParametroAtto->Ordine_Pagamento;

$Opposizione = $myParametroAtto->Opposizione;
$Opposizione_Testo = $myParametroAtto->Opposizione_Testo;
$Autotutela = $myParametroAtto->Autotutela;
$Autotutela_Testo = $myParametroAtto->Autotutela_Testo;
$Luogo = $myParametroAtto->Luogo;
$Intestazione_Firma_Sinistra = $myParametroAtto->Intestazione_Firma_Sinistra;
$Firma_Sinistra = $myParametroAtto->Firma_Sinistra;
$Intestazione_Firma_Destra = $myParametroAtto->Intestazione_Firma_Destra;
$Firma_Destra = $myParametroAtto->Firma_Destra;

//TABELLA 2
$Ufficiale_Pignoramento = $myParametroAtto->Ufficiale_Pignoramento;
$Assoggetto_Pignoramento = $myParametroAtto->Assoggetto_Pignoramento;
$Assoggetto_Pignoramento_Testo = $myParametroAtto->Assoggetto_Pignoramento_Testo;

$Ordina = $myParametroAtto->Ordina;
$Ordina_Testo = $myParametroAtto->Ordina_Testo;

$Informo = $myParametroAtto->Informo;
$Informo_Testo = $myParametroAtto->Informo_Testo;
$Informo_Notifica = $myParametroAtto->Informo_Notifica;
$Intimo = $myParametroAtto->Intimo;
$Intimo_Testo = $myParametroAtto->Intimo_Testo;
$Informo_2 = $myParametroAtto->Informo_2;
$Informo_Testo_2 = $myParametroAtto->Informo_Testo_2;
$Invito = $myParametroAtto->Invito;
$Invito_Testo = $myParametroAtto->Invito_Testo;
$Invito_Testo_2 = $myParametroAtto->Invito_Testo_2;
$Notifica_Pignoramento = $myParametroAtto->Notifica_Pignoramento;

$Intestazione_Relata_Ufficiale_Giudiziario = $myParametroAtto->Intestazione_Relata_Ufficiale_Giudiziario;
$Sottointestazione_Relata_Ufficiale_Giudiziario = $myParametroAtto->Sottointestazione_Relata_Ufficiale_Giudiziario;

$Intestazione_Relata_Ufficiale_Riscossione = $myParametroAtto->Intestazione_Relata_Ufficiale_Riscossione;
$Sottointestazione_Relata_Ufficiale_Riscossione = $myParametroAtto->Sottointestazione_Relata_Ufficiale_Riscossione;

$Relata_Notifica = $myParametroAtto->Relata_Notifica;
$Relata_Debitore = $myParametroAtto->Relata_Debitore;
$Relata_Terzo = $myParametroAtto->Relata_Terzo;

$Intestazione_Firma_Notifica = $myParametroAtto->Intestazione_Firma_Notifica;
$Firma_Notifica = $myParametroAtto->Firma_Notifica;

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

	if (ParolaPresenteInStringa ("sottotitolo_oggetto", "{ATTO}") == false) return false;
	if (ParolaPresenteInStringa ("sottotitolo_oggetto", "{INFOCARTELLA}") == false) return false;

	if (ParolaPresenteInStringa ("ufficiale_responsabile", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("ufficiale_responsabile", "{SEDEGESTORE}") == false) return false;

	if (ParolaPresenteInStringa ("atti_notificati", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{CFPI}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{RESIDENZAUTENTE}") == false) return false;

	if (ParolaPresenteInStringa ("premesso_testo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("premesso_testo", "{ENTE}") == false) return false;
	if (ParolaPresenteInStringa ("premesso_testo", "{DATACALCOLO}") == false) return false;
	
	if (ParolaPresenteInStringa ("modalita_pagamento_testo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("modalita_pagamento_testo", "{NUMEROCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("modalita_pagamento_testo", "{INTESTATARIOCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("modalita_pagamento_testo", "{CODICEUTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("modalita_pagamento_testo", "{CRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("modalita_pagamento_testo", "{RIFERIMENTO}") == false) return false;
	if (ParolaPresenteInStringa ("modalita_pagamento_testo", "{ENTE}") == false) return false;

	if (ParolaPresenteInStringa ("terzo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("terzo", "{TERZI}") == false) return false;
		
	if (ParolaPresenteInStringa ("somme_dovute", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("somme_dovute", "{FUNZIONARIORESPONSABILE}") == false) return false;
	if (ParolaPresenteInStringa ("somme_dovute", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("somme_dovute", "{TERZI}") == false) return false;
	
	if (ParolaPresenteInStringa ("luogo", "{DATASTAMPA}") == false) return false;
	
	if (ParolaPresenteInStringa ("intestazione_firma_sinistra", "{FIRMASINISTRA}") == false) return false;
	if (ParolaPresenteInStringa ("firma_sinistra", "{FIRMASINISTRA}") == false) return false;
	if (ParolaPresenteInStringa ("intestazione_firma_destra", "{FIRMADESTRA}") == false) return false;
	if (ParolaPresenteInStringa ("firma_destra", "{FIRMADESTRA}") == false) return false;

	if (ParolaPresenteInStringa ("ufficiale_pignoramento", "{UFFICIALE}") == false) return false;
	
	if (ParolaPresenteInStringa ("assoggetto_pignoramento_testo", "{TERZI}") == false) return false;
	if (ParolaPresenteInStringa ("assoggetto_pignoramento_testo", "{UTENTE}") == false) return false;

	if (ParolaPresenteInStringa ("ordina_testo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("ordina_testo", "{UTENTE2}") == false) return false;
	if (ParolaPresenteInStringa ("ordina_testo", "{NUMEROCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("ordina_testo", "{INTESTATARIOCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("ordina_testo", "{CODICEUTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("ordina_testo", "{CRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("ordina_testo", "{RIFERIMENTO}") == false) return false;
	if (ParolaPresenteInStringa ("ordina_testo", "{ENTE}") == false) return false;
	
	if (ParolaPresenteInStringa ("informo_testo", "{UTENTE}") == false) return false;
	
	if (ParolaPresenteInStringa ("informo_notifica", "{SPESENOTIFICA}") == false) return false;
	if (ParolaPresenteInStringa ("informo_notifica", "{SPESEATTIGIUDIZIARI}") == false) return false;
	if (ParolaPresenteInStringa ("informo_notifica", "{CAN}") == false) return false;
	if (ParolaPresenteInStringa ("informo_notifica", "{CAD}") == false) return false;

	if (ParolaPresenteInStringa ("intimo_testo", "{UTENTE}") == false) return false;
	
	if (ParolaPresenteInStringa ("invito_testo", "{PECGESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("invito_testo", "{UTENTE}") == false) return false;

	if (ParolaPresenteInStringa ("intestazione_relata_uff_giudiziario", "{TRIBUNALE}") == false) return false;

	if (ParolaPresenteInStringa ("relata_notifica", "{UFFICIALE}") == false) return false;
	if (ParolaPresenteInStringa ("relata_notifica", "{NOTIFICATO}") == false) return false;
	
	if (ParolaPresenteInStringa ("relata_debitore", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("relata_debitore", "{RESIDENZAUTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("relata_debitore", "{TIPOINVIO}") == false) return false;
	
	if (ParolaPresenteInStringa ("relata_terzo", "{TERZO}") == false) return false;
	if (ParolaPresenteInStringa ("relata_terzo", "{SEDETERZO}") == false) return false;
	if (ParolaPresenteInStringa ("relata_terzo", "{TIPOINVIO}") == false) return false;
	
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
	//TABELLA 1
	if (CheckCaratteri ("titolo_oggetto") == false) return false;
	if (CheckCaratteri ("sottotitolo_oggetto") == false) return false;
	if (CheckCaratteri ("ufficiale_responsabile") == false) return false;
	if (CheckCaratteri ("abilitazione") == false) return false;
	if (CheckCaratteri ("premesso") == false) return false;
	if (CheckCaratteri ("premesso_testo") == false) return false;
	if (CheckCaratteri ("informazioni") == false) return false;
	if (CheckCaratteri ("informazioni_testo") == false) return false;
	if (CheckCaratteri ("modalita_pagamento") == false) return false;
	if (CheckCaratteri ("modalita_pagamento_testo") == false) return false;
	if (CheckCaratteri ("visto") == false) return false;
	if (CheckCaratteri ("ingiunzione_fiscale") == false) return false;
	if (CheckCaratteri ("legislatore") == false) return false;
	if (CheckCaratteri ("considerato") == false) return false;
	if (CheckCaratteri ("terzo") == false) return false;
	if (CheckCaratteri ("somme_dovute") == false) return false;
	if (CheckCaratteri ("ordine_pagamento") == false) return false;
	if (CheckCaratteri ("opposizione") == false) return false;
	if (CheckCaratteri ("opposizione_testo") == false) return false;
	if (CheckCaratteri ("autotutela") == false) return false;
	if (CheckCaratteri ("autotutela_testo") == false) return false;
	if (CheckCaratteri ("luogo") == false) return false;
	if (CheckCaratteri ("intestazione_firma_sinistra") == false) return false;
	if (CheckCaratteri ("firma_sinistra") == false) return false;
	if (CheckCaratteri ("intestazione_firma_destra") == false) return false;
	if (CheckCaratteri ("firma_destra") == false) return false;
		
	//TABELLA 2
		
	if (CheckCaratteri ("ufficiale_pignoramento") == false) return false;
	if (CheckCaratteri ("assoggetto_pignoramento") == false) return false;
	if (CheckCaratteri ("assoggetto_pignoramento_testo") == false) return false;
	if (CheckCaratteri ("ordina") == false) return false;
	if (CheckCaratteri ("ordina_testo") == false) return false;
	if (CheckCaratteri ("informo") == false) return false;
	if (CheckCaratteri ("informo_testo") == false) return false;
	if (CheckCaratteri ("informo_notifica") == false) return false;
	if (CheckCaratteri ("intimo") == false) return false;
	if (CheckCaratteri ("intimo_testo") == false) return false;
	if (CheckCaratteri ("informo_2") == false) return false;
	if (CheckCaratteri ("informo_testo_2") == false) return false;
	if (CheckCaratteri ("invito") == false) return false;
	if (CheckCaratteri ("invito_testo") == false) return false;
	if (CheckCaratteri ("invito_testo_2") == false) return false;
	if (CheckCaratteri ("notifica_pignoramento") == false) return false;
	if (CheckCaratteri ("intestazione_relata_uff_giudiziario") == false) return false;
	if (CheckCaratteri ("sottointestazione_relata_uff_giudiziario") == false) return false;
	if (CheckCaratteri ("intestazione_relata_uff_riscossione") == false) return false;
	if (CheckCaratteri ("sottointestazione_relata_uff_riscossione") == false) return false;
	if (CheckCaratteri ("relata_notifica") == false) return false;
	if (CheckCaratteri ("relata_debitore") == false) return false;
	if (CheckCaratteri ("relata_terzo") == false) return false;
	
	
		
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
			<textarea name="titolo_oggetto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Titolo_Oggetto?></textarea>
		</td>
		<td>
			<font size=-2>
			{IDCRONOLOGICO}<br>{ANNOCRONOLOGICO}
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
			{ATTO}<br>{INFOCARTELLA}
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
			<textarea name="ufficiale_responsabile" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Ufficiale_Responsabile?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}<br>{SEDEGESTORE}
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
			<textarea name="abilitazione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Abilitazione?></textarea>
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
			<textarea name="premesso" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Premesso?></textarea>
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
			<textarea name="atti_notificati" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Atti_Notificati?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{CFPI}<br>{RESIDENZAUTENTE}
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
			<div id="premesso_testo">Premesso testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="premesso_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Premesso_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{ENTE}<br>{DATACALCOLO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="dettaglioImporti">Dettaglio Importi:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
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
			<div id="informazioni">Informazioni:</div>
		</td>
		<td>
			<font size=-2>CEN</font>
		</td>
		<td>
			<textarea name="informazioni" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Informazioni?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informazioni_testo">Informazioni testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informazioni_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Informazioni_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="modalita_pagamento">Modalita' pagamento:</div>
		</td>
		<td>
			<font size=-2>CEN</font>
		</td>
		<td>
			<textarea name="modalita_pagamento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Modalita_Pagamento?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="modalita_pagamento_testo">Modalita' pagamento testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="modalita_pagamento_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$Modalita_Pagamento_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{NUMEROCONTO}<br>{INTESTATARIOCONTO}<br>{IBAN}<br>{CODICEUTENTE}<br>{CRONOLOGICO}<br>{RIFERIMENTO}<br>{ENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="visto">Visto:</div>
		</td>
		<td>
			<font size=-2>CEN</font>
		</td>
		<td>
			<textarea name="visto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Visto?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ingiunzione_fiscale">Ingiunzione fiscale:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ingiunzione_fiscale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Ingiunzione_Fiscale?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="legislatore">Legislatore:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="legislatore" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Legislatore?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="considerato">Considerato:</div>
		</td>
		<td>
			<font size=-2>CEN</font>
		</td>
		<td>
			<textarea name="considerato" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Considerato?></textarea>
		</td>
		<td>
			<font size=-2>
			
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
			<textarea name="terzo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="4"><?=$Terzo?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERZI}<br>{UTENTE}<br>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="somme_dovute">Somme dovute:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="somme_dovute" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Somme_Dovute?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}<br>{FUNZIONARIORESPONSABILE}<br>{UTENTE}<br>{TERZI}
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
			<textarea name="ordine_pagamento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Ordine_Pagamento?></textarea>
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
			<textarea name="opposizione_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Opposizione_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="autotutela">Autotutela:</div>
		</td>
		<td>
			<font size=-2>CEN</font>
		</td>
		<td>
			<textarea name="autotutela" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Autotutela?></textarea>
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
			<div id="luogo">Luogo:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="luogo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Luogo?></textarea>
		</td>
		<td>
			<font size=-2>
			{DATASTAMPA}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intestazione_firma_sinistra">Intestazione firma sinistra:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="intestazione_firma_sinistra" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Intestazione_Firma_Sinistra?></textarea>
		</td>
		<td>
			<font size=-2>
			{FIRMASINISTRA}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="firma_sinistra">Firma sinistra:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="firma_sinistra" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Firma_Sinistra?></textarea>
		</td>
		<td>
			<font size=-2>
			{FIRMASINISTRA}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intestazione_firma_destra">Intestazione firma destra:</div>
		</td>
		<td>
			<font size=-2>DES</font>
		</td>
		<td>
			<textarea name="intestazione_firma_destra" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Intestazione_Firma_Destra?></textarea>
		</td>
		<td>
			<font size=-2>
			{FIRMADESTRA}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="firma_destra">Firma destra:</div>
		</td>
		<td>
			<font size=-2>DES</font>
		</td>
		<td>
			<textarea name="firma_destra" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Firma_Destra?></textarea>
		</td>
		<td>
			<font size=-2>
			{FIRMADESTRA}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ufficiale_pignoramento">Ufficiale pignoramento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ufficiale_pignoramento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$Ufficiale_Pignoramento?></textarea>
		</td>
		<td>
			<font size=-2>
			{UFFICIALE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="assoggetto_pignoramento">Assoggetto pignoramento:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="assoggetto_pignoramento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Assoggetto_Pignoramento?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="assoggetto_pignoramento_testo">Assoggetto pignoramento testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="assoggetto_pignoramento_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="8"><?=$Assoggetto_Pignoramento_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERZI}<br>{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ordina">Ordina:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="ordina" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Ordina?></textarea>
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
			<textarea name="ordina_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Ordina_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{UTENTE2}<br>{NUMEROCONTO}<br>{INTESTATARIOCONTO}<br>{IBAN}<br>{CODICEUTENTE}<br>{CRONOLOGICO}<br>{RIFERIMENTO}<br>{ENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo">Informo:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="informo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Informo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo_testo">Informo testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informo_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Informo_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo_notifica">Informo notifica:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informo_notifica" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Informo_Notifica;?></textarea>
		</td>
		<td>
			<font size=-2>
			{SPESENOTIFICA}<br>{SPESEATTIGIUDIZIARI}<br>{CAN}<br>{CAD}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intimo">Intimo:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="intimo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Intimo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intimo_testo">Intimo testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="intimo_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Intimo_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo_2">Informo 2:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="informo_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Informo_2?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo_testo_2">Informo testo 2:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informo_testo_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Informo_Testo_2?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="invito">Invito:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="invito" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Invito?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="invito_testo">Invito testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="invito_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Invito_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{PECGESTORE}<br>{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="invito_testo_2">Invito testo 2:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="invito_testo_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="6"><?=$Invito_Testo_2?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="notifica_pignoramento">Notifica pignoramento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="notifica_pignoramento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Notifica_Pignoramento?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intestazione_relata_uff_giudiziario">Intestazione relata ufficiale giudiziario:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="intestazione_relata_uff_giudiziario" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Intestazione_Relata_Ufficiale_Giudiziario?></textarea>
		</td>
		<td>
			<font size=-2>
			{TRIBUNALE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="sottointestazione_relata_uff_giudiziario">Sottointestazione relata ufficiale giudiziario:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="sottointestazione_relata_uff_giudiziario" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Sottointestazione_Relata_Ufficiale_Giudiziario?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intestazione_relata_uff_riscossione">Intestazione relata ufficiale riscossione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="intestazione_relata_uff_riscossione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Intestazione_Relata_Ufficiale_Riscossione?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="sottointestazione_relata_uff_riscossione">Sottointestazione relata ufficiale riscossione:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="sottointestazione_relata_uff_riscossione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="1"><?=$Sottointestazione_Relata_Ufficiale_Riscossione?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="relata_notifica">Relata notifica:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="relata_notifica" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Relata_Notifica?></textarea>
		</td>
		<td>
			<font size=-2>
			{UFFICIALE}<br>{NOTIFICATO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="relata_debitore">Relata debitore:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="relata_debitore" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Relata_Debitore?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{RESIDENZAUTENTE}<br>{TIPOINVIO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="relata_terzo">Relata terzo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="relata_terzo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Relata_Terzo?></textarea>
		</td>
		<td>
			<font size=-2>
			{TERZO}<br>{SEDETERZO}<br>{TIPOINVIO}
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

</td>
</tr>
</table>

</body>
</html>