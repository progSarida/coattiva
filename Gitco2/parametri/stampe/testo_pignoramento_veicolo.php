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

$myParametroAtto = new testo_pignoramento_veicolo(null);
$myId = $myParametroAtto->CercaParametroData($c, date("Y-m-d"));

$myParametroAtto = new testo_pignoramento_veicolo($myId);

//TABELLA 1

$Titolo_Oggetto = $myParametroAtto->Titolo_Oggetto;
$Sottotitolo_Oggetto = $myParametroAtto->Sottotitolo_Oggetto;
$Intestazione_Pignoramento = $myParametroAtto->Intestazione_Pignoramento;

$Ufficiale_Responsabile = $myParametroAtto->Ufficiale_Responsabile;
$Legale_Rappresentante_Comune = $myParametroAtto->Legale_Rappresentante_Comune;
$Legale_Rappresentante_Concessionario = $myParametroAtto->Legale_Rappresentante_Concessionario;

$Premesso = $myParametroAtto->Premesso;
$Atti_Notificati = $myParametroAtto->Atti_Notificati;
$Premesso_Testo = $myParametroAtto->Premesso_Testo;
$Informazioni = $myParametroAtto->Informazioni;
$Informazioni_Testo = $myParametroAtto->Informazioni_Testo;
$Informo = $myParametroAtto->Informo;
$Conto_Corrente = $myParametroAtto->Conto_Corrente;
$Informo_Testo = $myParametroAtto->Informo_Testo;
$Informo_Testo_2 = $myParametroAtto->Informo_Testo_2;
$Informo_Testo_3 = $myParametroAtto->Informo_Testo_3;
$Informo_Testo_4 = $myParametroAtto->Informo_Testo_4;

$Considerato = $myParametroAtto->Considerato;
$Ingiunzione_Fiscale = $myParametroAtto->Ingiunzione_Fiscale;
$Legislatore = $myParametroAtto->Legislatore;
$Dati_Veicolo = $myParametroAtto->Dati_Veicolo;

$Premesso_Considerato = $myParametroAtto->Premesso_Considerato;
$Opposizione_Testo = $myParametroAtto->Opposizione_Testo;
$Beni_Strumentali_Testo = $myParametroAtto->Beni_Strumentali_Testo;
$Valutazione_Strumentale = $myParametroAtto->Valutazione_Strumentale;
$Autotutela_Testo = $myParametroAtto->Autotutela_Testo;
$Recupero_Somme = $myParametroAtto->Recupero_Somme;
$Notifica_Istituto = $myParametroAtto->Notifica_Istituto;

$Luogo = $myParametroAtto->Luogo;

//TABELLA 2

$Ufficiale_Pignoramento = $myParametroAtto->Ufficiale_Pignoramento;

$Assoggetto_Pignoramento = $myParametroAtto->Assoggetto_Pignoramento;
$Assoggetto_Testo = $myParametroAtto->Assoggetto_Testo;

$Ingiungo = $myParametroAtto->Ingiungo;
$Ingiungo_Testo = $myParametroAtto->Ingiungo_Testo;

$Invito = $myParametroAtto->Invito;
$Invito_Testo = $myParametroAtto->Invito_Testo;

$Avverto = $myParametroAtto->Avverto;
$Avverto_Testo = $myParametroAtto->Avverto_Testo;

$Intimo = $myParametroAtto->Intimo;
$Intimo_Testo = $myParametroAtto->Intimo_Testo;

$Comunico = $myParametroAtto->Comunico;
$Comunico_Testo_1 = $myParametroAtto->Comunico_Testo_1;
$Comunico_Testo_2 = $myParametroAtto->Comunico_Testo_2;

$intRelataGiudiziario = $myParametroAtto->Intestazione_Relata_Ufficiale_Giudiziario;
$sottoIntRelataGiudiziario = $myParametroAtto->Sottointestazione_Relata_Ufficiale_Giudiziario;

$intRelataRiscossione = $myParametroAtto->Intestazione_Relata_Ufficiale_Riscossione;
$sottoIntRelataRiscossione = $myParametroAtto->Sottointestazione_Relata_Ufficiale_Riscossione;

$relataNotifica = $myParametroAtto->Relata_Ufficiale;

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
       			$("#form_testo_pignoramento_veicolo").submit();
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
	stringa = "testo_pignoramento_veicolo.php?"+stringaPHP;
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
	if (ParolaPresenteInStringa ("titolo_oggetto", "{TRIBUNALE}") == false) return false;
	
	if (ParolaPresenteInStringa ("intestazione_pignoramento", "{IDCRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("intestazione_pignoramento", "{ANNOCRONOLOGICO}") == false) return false;

	if (ParolaPresenteInStringa ("ufficiale_responsabile", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("ufficiale_responsabile", "{SEDEGESTORE}") == false) return false;
	
	if (ParolaPresenteInStringa ("rappresentante_comune", "{FUNZIONARIORESPONSABILE}") == false) return false;
	if (ParolaPresenteInStringa ("rappresentante_concessionario", "{ENTE}") == false) return false;	

	if (ParolaPresenteInStringa ("atti_notificati", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{CFPI}") == false) return false;
	if (ParolaPresenteInStringa ("atti_notificati", "{RESIDENZAUTENTE}") == false) return false;
	
	if (ParolaPresenteInStringa ("premesso_testo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("premesso_testo", "{ENTE}") == false) return false;
	if (ParolaPresenteInStringa ("premesso_testo", "{DATACALCOLO}") == false) return false;

	if (ParolaPresenteInStringa ("conto_corrente", "{NUMEROCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("conto_corrente", "{INTESTATARIOCONTO}") == false) return false;
	if (ParolaPresenteInStringa ("conto_corrente", "{CRONOLOGICO}") == false) return false;
	if (ParolaPresenteInStringa ("conto_corrente", "{CODICEUTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("conto_corrente", "{RIFERIMENTO}") == false) return false;
	if (ParolaPresenteInStringa ("conto_corrente", "{ENTE}") == false) return false;

	if (ParolaPresenteInStringa ("informo_testo_3", "{SPESENOTIFICA}") == false) return false;
	if (ParolaPresenteInStringa ("informo_testo_3", "{CAN}") == false) return false;
	if (ParolaPresenteInStringa ("informo_testo_3", "{CAD}") == false) return false;
	
	if (ParolaPresenteInStringa ("dati_veicolo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("dati_veicolo", "{DATAVISURA}") == false) return false;
	if (ParolaPresenteInStringa ("dati_veicolo", "{FONTEDATI}") == false) return false;
	if (ParolaPresenteInStringa ("dati_veicolo", "{TIPOVEICOLO}") == false) return false;
	if (ParolaPresenteInStringa ("dati_veicolo", "{MARCAVEICOLO}") == false) return false;
	if (ParolaPresenteInStringa ("dati_veicolo", "{MODELLOVEICOLO}") == false) return false;
	if (ParolaPresenteInStringa ("dati_veicolo", "{TARGAVEICOLO}") == false) return false;

	if (ParolaPresenteInStringa ("premesso_considerato", "{UTENTE}") == false) return false;

	if (ParolaPresenteInStringa ("beni_strumentali_testo", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("beni_strumentali_testo", "{SEDEGESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("beni_strumentali_testo", "{RECAPITIGESTORE}") == false) return false;

	if (ParolaPresenteInStringa ("valutazione_strumentale", "{SPESASTIMABENI}") == false) return false;

	if (ParolaPresenteInStringa ("autotutela_testo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("autotutela_testo", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("autotutela_testo", "{SEDEGESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("autotutela_testo", "{RECAPITIGESTORE}") == false) return false;
	
	if (ParolaPresenteInStringa ("recupero_somme", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("recupero_somme", "{FUNZIONARIORESPONSABILE}") == false) return false;
	
	if (ParolaPresenteInStringa ("notifica_istituto", "{ISTITUTOVENDITE}") == false) return false;
	if (ParolaPresenteInStringa ("notifica_istituto", "{SEDEISTITUTOVENDITE}") == false) return false;
	if (ParolaPresenteInStringa ("notifica_istituto", "{RECAPITIISTITUTO}") == false) return false;
	if (ParolaPresenteInStringa ("notifica_istituto", "{TIPOINVIO}") == false) return false;

	if (ParolaPresenteInStringa ("luogo", "{DATASTAMPA}") == false) return false;
	
	if (ParolaPresenteInStringa ("ufficiale_pignoramento", "{GESTORE}") == false) return false;
	if (ParolaPresenteInStringa ("ufficiale_pignoramento", "{UFFICIALE}") == false) return false;
	if (ParolaPresenteInStringa ("ufficiale_pignoramento", "{INGIUNZIONE}") == false) return false;

	if (ParolaPresenteInStringa ("assoggetto_testo", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("assoggetto_testo", "{DATAVISURA}") == false) return false;
	if (ParolaPresenteInStringa ("assoggetto_testo", "{FONTEDATI}") == false) return false;
	if (ParolaPresenteInStringa ("assoggetto_testo", "{TIPOVEICOLO}") == false) return false;
	if (ParolaPresenteInStringa ("assoggetto_testo", "{MARCAVEICOLO}") == false) return false;
	if (ParolaPresenteInStringa ("assoggetto_testo", "{MODELLOVEICOLO}") == false) return false;
	if (ParolaPresenteInStringa ("assoggetto_testo", "{TARGAVEICOLO}") == false) return false;

	if (ParolaPresenteInStringa ("ingiungo_testo", "{UTENTE}") == false) return false;
	
	if (ParolaPresenteInStringa ("intimo_testo", "{ISTITUTOVENDITE}") == false) return false;
	if (ParolaPresenteInStringa ("intimo_testo", "{SEDEISTITUTOVENDITE}") == false) return false;
	if (ParolaPresenteInStringa ("intimo_testo", "{RECAPITIISTITUTO}") == false) return false;

	if (ParolaPresenteInStringa ("IntestazioneUffGiudiziario", "{SEDETRIBUNALE}") == false) return false;
	if (ParolaPresenteInStringa ("RelataNotifica", "{UTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("RelataNotifica", "{RESIDENZAUTENTE}") == false) return false;
	if (ParolaPresenteInStringa ("RelataNotifica", "{UFFICIALE}") == false) return false;
	if (ParolaPresenteInStringa ("RelataNotifica", "{TIPOINVIO}") == false) return false;
	
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
	if (CheckCaratteri ("intestazione_pignoramento") == false) return false;
	if (CheckCaratteri ("ufficiale_responsabile") == false) return false;
	if (CheckCaratteri ("rappresentante_comune") == false) return false;
	if (CheckCaratteri ("rappresentante_concessionario") == false) return false;
	if (CheckCaratteri ("premesso") == false) return false;
	if (CheckCaratteri ("atti_notificati") == false) return false;
	if (CheckCaratteri ("premesso_testo") == false) return false;
	if (CheckCaratteri ("informo") == false) return false;
	if (CheckCaratteri ("conto_corrente") == false) return false;
	if (CheckCaratteri ("informo_testo") == false) return false;
	if (CheckCaratteri ("informo_testo_2") == false) return false;
	if (CheckCaratteri ("informo_testo_3") == false) return false;
	if (CheckCaratteri ("informo_testo_4") == false) return false;	
	if (CheckCaratteri ("considerato") == false) return false;
	if (CheckCaratteri ("legislatore") == false) return false;
	if (CheckCaratteri ("dati_veicolo") == false) return false;
	if (CheckCaratteri ("premesso_considerato") == false) return false;
	if (CheckCaratteri ("opposizione_testo") == false) return false;
	if (CheckCaratteri ("autotutela_testo") == false) return false;
	if (CheckCaratteri ("beni_strumentali_testo") == false) return false;
	if (CheckCaratteri ("valutazione_strumentale") == false) return false;
	if (CheckCaratteri ("recupero_somme") == false) return false;
	if (CheckCaratteri ("notifica_istituto") == false) return false;
	if (CheckCaratteri ("luogo") == false) return false;
	
	if (CheckCaratteri ("ufficiale_pignoramento") == false) return false;
	if (CheckCaratteri ("assoggetto_pignoramento") == false) return false;
	if (CheckCaratteri ("assoggetto_testo") == false) return false;
	if (CheckCaratteri ("ingiungo") == false) return false;
	if (CheckCaratteri ("ingiungo_testo") == false) return false;
	if (CheckCaratteri ("invito") == false) return false;
	if (CheckCaratteri ("invito_testo") == false) return false;
	if (CheckCaratteri ("avverto") == false) return false;
	if (CheckCaratteri ("avverto_testo") == false) return false;
	if (CheckCaratteri ("intimo") == false) return false;
	if (CheckCaratteri ("intimo_testo") == false) return false;
	if (CheckCaratteri ("comunico") == false) return false;
	if (CheckCaratteri ("comunico_testo_1") == false) return false;
	if (CheckCaratteri ("comunico_testo_2") == false) return false;
	if (CheckCaratteri ("IntestazioneUffGiudiziario") == false) return false;
	if (CheckCaratteri ("SottoIntestazioneUffGiudiziario") == false) return false;
	if (CheckCaratteri ("IntestazioneUffRiscossione") == false) return false;
	if (CheckCaratteri ("SottoIntestazioneUffRiscossione") == false) return false;
	if (CheckCaratteri ("RelataNotifica") == false) return false;
		
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

 			var mettiSINO = 0;
 			
 			var messageError = "Hai inserito il carattere ' " + carattere + " ' nel campo '"+ testoId +"': carattere non accettato";
 			alert (messageError);
 		}
 		if(mettiSINO == 1){
 	 		 testoNelCampo += carattere;
 		}
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
	
	$('#form_testo_pignoramento_veicolo').ajaxForm(
			
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

<form name="testo_pignoramento_veicolo" id="form_testo_pignoramento_veicolo" action="testo_pignoramento_veicolo_salva.php" method="post">

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
			{TRIBUNALE}
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
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intestazione_pignoramento">Intestazione pignoramento:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="intestazione_pignoramento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Intestazione_Pignoramento?></textarea>
		</td>
		<td>
			<font size=-2>
			{IDCRONOLOGICO}&nbsp;<br>{ANNOCRONOLOGICO}
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
			{GESTORE}&nbsp;<br>{SEDEGESTORE}
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
			{FUNZIONARIORESPONSABILE}
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
			{ENTE}
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
			<div id="informo">Informo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="informo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Informo?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="conto_corrente">Conto corrente:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="conto_corrente" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Conto_Corrente?></textarea>
		</td>
		<td>
			<font size=-2>
			{NUMEROCONTO}<br>{INTESTATARIOCONTO}<br>{IBAN}(facoltativo)<br>
			{CODICEUTENTE}<br>{CRONOLOGICO}<br>{RIFERIMENTO}<br>{ENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo_testo">Informo testo 1:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="informo_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Informo_Testo?></textarea>
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
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="informo_testo_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Informo_Testo_2?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo_testo_3">Informo testo 3:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="informo_testo_3" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Informo_Testo_3?></textarea>
		</td>
		<td>
			<font size=-2>
			{SPESENOTIFICA}<br>{CAN}<br>{CAD}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="informo_testo_4">Informo testo 4:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="informo_testo_4" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Informo_Testo_4?></textarea>
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
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="considerato" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Considerato?></textarea>
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
			<textarea name="ingiunzione_fiscale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Ingiunzione_Fiscale?></textarea>
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
			<textarea name="legislatore" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Legislatore?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="dati_veicolo">Dati veicolo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="dati_veicolo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Dati_Veicolo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{DATAVISURA}<br>{FONTEDATI}
			<br>{TIPOVEICOLO}<br>{MARCAVEICOLO}<br>
			{MODELLOVEICOLO}<br>{TARGAVEICOLO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="premesso_considerato">Premesso e considerato:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="premesso_considerato" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$Premesso_Considerato?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}
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
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="beni_strumentali_testo">Beni strumentali testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="beni_strumentali_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Beni_Strumentali_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}<br>{SEDEGESTORE}<br>{RECAPITIGESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="valutazione_strumentale">Valutazione strumentale:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="valutazione_strumentale" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Valutazione_Strumentale?></textarea>
		</td>
		<td>
			<font size=-2>
			{SPESASTIMABENI}
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
			<textarea name="autotutela_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Autotutela_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{GESTORE}<br>{SEDEGESTORE}<br>{RECAPITIGESTORE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="recupero_somme">Recupero somme:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="recupero_somme" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Recupero_Somme?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}<br>{FUNZIONARIORESPONSABILE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="notifica_istituto">Notifica istituto vendite:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="notifica_istituto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Notifica_Istituto?></textarea>
		</td>
		<td>
			<font size=-2>
			{ISITUTOVENDITE}<br>{SEDEISITUTOVENDITE}<br>{RECAPITIISTITUTO}<br>{TIPOINVIO}
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
			<div id="ufficiale_pignoramento">Ufficiale pignoramento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ufficiale_pignoramento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Ufficiale_Pignoramento?></textarea>
		</td>
		<td>
			<font size=-2>
			{GESTORE}<br>{UFFICIALE}<br>{INGIUNZIONE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="assoggetto_pignoramento">Assoggetto pignoramento:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="assoggetto_pignoramento" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Assoggetto_Pignoramento?></textarea>
		</td>
		<td>
			<font size=-2>

			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="assoggetto_testo">Assoggetto testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="assoggetto_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Assoggetto_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}<br>{DATAVISURA}<br>{FONTEDATI}
			<br>{TIPOVEICOLO}<br>{MARCAVEICOLO}<br>
			{MODELLOVEICOLO}<br>{TARGAVEICOLO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ingiungo">Ingiungo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="ingiungo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Ingiungo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="ingiungo_testo">Ingiungo testo:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="ingiungo_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$Ingiungo_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{UTENTE}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="invito">Invito:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="invito" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Invito?></textarea>
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
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="invito_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Invito_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="avverto">Avverto:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="avverto" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Avverto?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="avverto_testo">Avverto testo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="avverto_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Avverto_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="intimo">Intimo:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="intimo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Intimo?></textarea>
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
			<textarea name="intimo_testo" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="9"><?=$Intimo_Testo?></textarea>
		</td>
		<td>
			<font size=-2>
			{ISTITUTOVENDITE}<br>
			{SEDEISTITUTOVENDITE}<br>{RECAPITIISTITUTO}
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="comunico">Comunico:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="comunico" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="3"><?=$Comunico?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="comunico_testo_1">Comunico testo 1:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="comunico_testo_1" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Comunico_Testo_1?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="comunico_testo_2">Comunico testo 2:</div>
		</td>
		<td>
			<font size=-2>GIUS</font>
		</td>
		<td>
			<textarea name="comunico_testo_2" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="7"><?=$Comunico_Testo_2?></textarea>
		</td>
		<td>
			<font size=-2>
			</font>
		</td>
	</tr>
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
			{SEDETRIBUNALE}
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
			<div id="IntestazioneUffRiscossione">Intestazione relata Ufficiale Giudiziario:</div>
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
			<div id="SottoIntestazioneUffRiscossione">Sottointestazione relata Ufficiale Riscossione:</div>
		</td>
		<td>
			<font size=-2>CENT</font>
		</td>
		<td>
			<textarea name="SottoIntestazioneUffRiscossione" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="2"><?=$sottoIntRelataRiscossione?></textarea>
		</td>
		<td>
			<font size=-2>
			
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<div id="RelataNotifica">Relazione di notifica:</div>
		</td>
		<td>
			<font size=-2>SIN</font>
		</td>
		<td>
			<textarea name="RelataNotifica" onblur="CheckCaratteri(this);" onchange="CheckCampiObbligatori();" style="width:95%" rows="5"><?=$relataNotifica?></textarea>
		</td>
		<td>
			<font size=-2>
			{UFFICIALE}<br>
			{UTENTE}<br>{RESIDENZAUTENTE}<br>{TIPOINVIO}
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