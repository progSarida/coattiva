<?php

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";*/

/*if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}*/

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");

if(!isset($_SESSION['username']))
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

//$comune = new ente_gestito($c);
$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

//$gestore = $comune->Ufficio;
$ufficio_ID = $a_enteAdmin["Gestore_ID"];


$int = $a_enteAdmin["Gestore_Interno"];//$gestore->Interno;
$civ = $a_enteAdmin["Gestore_Civico"];//$gestore->Civico;

if( $ufficio_ID == 0 )
{

	$layout = "<script>$('#sel_gestore').prop('checked',false);$('#sel_ente').prop('checked',true);</script>";
}
else
{
	if($int==0)$int="";
	if($civ==0)$civ="";

	$layout = "<script>$('#sel_ente').prop('checked',false);$('#sel_gestore').prop('checked',true);</script>";
}

//$denominazione = $gestore->Denominazione;
//$CC = $gestore->CC;
//$email = $gestore->Mail;
//$PEC = $gestore->PEC;
//$sito = $gestore->Sito;
//$via = $gestore->Toponimo;
//$int = $gestore->Interno;
//$esp = $gestore->Esponente;
//$civ = $gestore->Civico;
//$dett = $gestore->Dettagli;
//$tel = $gestore->Telefono;
//$fax = $gestore->Fax;
//$com = $gestore->Comune;
//$prov = $gestore->Provincia;
//$cap = $gestore->Cap;
//$orario = $gestore->Orario;// $a_enteAdmin["Gestore_Orario"];

//print_r($a_enteAdmin);
//$denominazione = $a_enteAdmin["Gestore_Denominazione"];//$gestore->Denominazione;
//$PI = $a_enteAdmin["Gestore_PI"];//$gestore->Partita_Iva;
//$CF = $a_enteAdmin["Gestore_CF"];//$gestore->Codice_Fiscale;
//$CC = $a_enteAdmin["CC"];//$gestore->CC;
//$email = $a_enteAdmin["Gestore_Mail"];//$gestore->Mail;
//$PEC = $a_enteAdmin["Gestore_PEC"];//$gestore->PEC;
//$sito = $a_enteAdmin["Gestore_Sito"];//$gestore->Sito;
//$via = $a_enteAdmin["Gestore_Via"];//$gestore->Toponimo;

//$esp = $a_enteAdmin["Gestore_Esponente"];//$gestore->Esponente;
//$dett = $a_enteAdmin["Gestore_Dettagli"];//$gestore->Dettagli;
//$tel = $a_enteAdmin["Gestore_Telefono"];//$gestore->Telefono;
//$fax = $a_enteAdmin["Gestore_Fax"];//$gestore->Fax;
//$com = $a_enteAdmin["Gestore_Comune"];//$gestore->Comune;
//$prov = $a_enteAdmin["Gestore_Provincia"];//$gestore->Provincia;
//$cap = $a_enteAdmin["Gestore_Cap"];//$gestore->Cap;



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

<!-- ********** MODALI AJAX ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}

function callParent(valorediritorno){
    switch(selectParent){
        case "comune":


            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                $('#comune_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(valorediritorno.cap);
                $('#CC_id').val(valorediritorno.CC);
            }

            break;
    }

}

var selectParent = "";
function cerca_comune()
{
    selectParent = "comune";
	strDim = Dim_Alert(600, 300);

	var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=ricComune";

	valorediritorno = window.showModalDialog(stringa, "", strDim);

}

</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

/*$(document).ready(function(){

	$("#submit_click").click( salva_form );

    $("#delete_click").click( cancella_form );

	$('#form_ufficio').ajaxForm(

	    function(value) {
	        var array_ritorno = value.split(' ');

		if(array_ritorno[0]=='SAVED')
		{
			alert('Ufficio salvato correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert('Salvataggio ufficio fallito!');
		}
		if(array_ritorno[0]=='DELETED')
		{
			alert('Ufficio cancellato correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR_DELETE')
		{
			alert('Cancellazione ufficio fallita!');
		}

	    });

});*/

</script>

<body class="sfondo_new_gitco" >

<!--<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="comune" ><?php //echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php //echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>-->

<?php
/*switch ($servizio)
{
	case "COATTIVA":
		include MENU . '/menu_generale.php';
		break;
	case "TARGHEESTERE":
		include TARGHEESTERE . '/menu/menu_targheestere.php';
		break;
	case "PUBBLICITA":
		include PUBBLICITA . '/menu/menu_pubblicita.php';
		break;
	default:
		include MENU . '/menu_generale.php';
		break;
}*/
	include(INC."/menu.php");
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>


//F3
switchMenuImg("F3");
F3_button = function()
{
	control_salva = submit_buttons('Salva');
	if(control_salva)
			$("#form_ufficio").submit();
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "ufficio.php?"+stringaPHP;
	   	top.location.href = stringa;
}


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "gestore.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "stemma.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


</script>
<!--<table align=center class=table_interna border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4.png" style="width:47px; height:47px; border:0;" />
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
		<td><font class="titolo font16 under_decor">Ufficio</font></td>
	</tr>
</table>

<form name=form_ufficio id=form_ufficio method=post action="ufficio_salva.php">

<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=ufficio_id  value="<?php echo $ufficio_ID; ?>" >

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $a_enteAdmin["CC"]; ?>">


<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_left width15">Denominazione</td>
		<td class="text_left" colspan=7><input class="text_left" id=denom_id name=denom size=50 value="<?php echo $a_enteAdmin["Gestore_Denominazione"]; ?>" ></td>

	</tr>
	<tr>
		<td class="text_left width15">Comune</td>
		<td class="text_left width22"><input class="sfondo_azzurro text_left" readonly name=comune id=comune_id value="<?php echo $a_enteAdmin["Gestore_Comune"]; ?>" size=15 onclick="cerca_comune();"></td>
		<td class="text_left width15">Provincia</td>
		<td class="text_left width15"><input class="sfondo_azzurro text_left" readonly id=prov_id name=prov size=1 value="<?php echo $a_enteAdmin["Gestore_Provincia"]; ?>"></td>
		<td class="text_left width8" >CAP</td>
		<td class="text_left width10"><input class="sfondo_azzurro text_center" readonly id=cap_id name=cap size=4 value="<?php echo $a_enteAdmin["Gestore_Cap"]; ?>"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Indirizzo</td>
		<td class="text_left width22">
        	<input id=via class="text_left" name=via type=text value="<?php echo $a_enteAdmin["Gestore_Via"]; ?>" size=18>
        </td>
		<td class="text_left width63" colspan=6>Civ.
		&nbsp;<input type="text" id=civico 	   class="text_right"  name="civico"  	value="<?php echo $civ; ?>"  size=2 >
		&nbsp;Esp.
		&nbsp;<input type="text" id=esponente  class="text_left"   name="esponente" value="<?php echo $a_enteAdmin["Gestore_Esponente"]; ?>"  size=2 >
		&nbsp;Int.
		&nbsp;<input type="text" id=interno    class="text_right"  name="interno" 	value="<?php echo $int; ?>"  size=2 >
		&nbsp;Dettagli
		&nbsp;<input type="text" id=dettagli   class="text_left"   name="dettagli" 	value="<?php echo $a_enteAdmin["Gestore_Dettagli"]; ?>"  size=20>
		</td>
	</tr>
	<tr>
		<td class="text_left width15">Telefono</td>
		<td class="text_left width22"><input class="text_right" id=tel_id name=tel size=18 value="<?php echo $a_enteAdmin["Gestore_Telefono"]; ?>"></td>
		<td class="text_left width15">Fax</td>
		<td class="text_left width23" colspan=2><input class="text_right" id=fax_id name=fax size=18 value="<?php echo $a_enteAdmin["Gestore_Fax"]; ?>"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Email</td>
		<td class="text_left width22"><input class="text_left" id=email_id name=email size=18 value="<?php echo $a_enteAdmin["Gestore_Mail"]; ?>" ></td>
		<td class="text_left width30" colspan=2>PEC
		&nbsp;&nbsp;&nbsp;&nbsp;<input class="text_left" id=pec_id name=PEC size=18 value="<?php echo $a_enteAdmin["Gestore_PEC"]; ?>" ></td>
		<td class="text_left width8">Sito</td>
		<td class="text_left width25" colspan=3><input class="text_left" id=sito_id name=sito size=16 value="<?php echo $a_enteAdmin["Gestore_Sito"]; ?>" ></td>
	</tr>
	<tr>
		<td class="text_left width15">Orario</td>
		<td class="text_left" colspan=7><textarea class="text_left" id=orario_id name=orario rows=3 cols=65><?php echo $a_enteAdmin["Gestore_Orario"]; ?></textarea></td>
	</tr>
</table>

</form>

<?php
echo $layout;
include(INC."/footer.php");
?>
<!--</td>
</tr>
</table>



</body>
</html>-->
