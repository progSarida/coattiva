<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include CLASSI . "/ruolo.php";
include CLASSI . "/anagrafe.php";

$p = get_var('p');
$c = get_var('c');
$a = get_var('a');

$id_ufficio = get_var('ID_ufficio');
if($id_ufficio==null)	$id_ufficio = 0;

if($id_ufficio == 0)	$invia = "Insert";
else					$invia = "Update";

$ufficio = new ufficio_giudiziario($id_ufficio);

$layout = "";

$tipo = $ufficio->Tipo;
if($tipo == "Tribunale")
	$layout = "<script>$('#giuris').val('tribunale');</script>";
else if($tipo == "Giudice di Pace")
	$layout = "<script>$('#giuris').val('giudice');</script>";

$sezione = $ufficio->Sezione;
$comune = $ufficio->Comune;
$CC = $ufficio->CC;
$provincia = $ufficio->Provincia;
$cap = $ufficio->Cap;
$toponimo = $ufficio->Toponimo;
$interno = $ufficio->Interno;
$esp = $ufficio->Esponente;
$civico = $ufficio->Civico;
$dettagli = $ufficio->Dettagli;
$tel = $ufficio->Telefono;
$fax = $ufficio->Fax;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Ufficio giudiziario</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	  
<script>

$(document).ready(function(){
	
	$('#form_info').ajaxForm(
			
	    function(value) {
		    alert(value);
		    
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
		}
		else if(array_ritorno[0]=='FAIL')
		{		
			alert("Errore nel salvataggio dell'ufficio giudiziario!");
		}
		else
		{
			alert("Errore nella procedura");
		}
		
	});


$("#submit_click").click(function salva_form() {     

	    $("#form_info").submit();

	});
	
	});

function new_ufficio()
{
	
	$('#ID').val(0);
	$('#sezione').val('');
	$('#comune').val('');
	$('#prov').val('');
	$('#CC').val('');
	$('#via').val('');
	$('#interno').val('');
	$('#civico').val('');
	$('#esponente').val('');
	$('#dettagli').val('');
	$('#tel').val('');
	$('#fax').val('');
	$('#invia_submit').val('Insert');
	
}

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px; ";
	setupPagina += "dialogHeight:" + sHeight + "px; ";
	setupPagina += "dialogLeft:80px; dialogTop:80px;";

	return setupPagina;
}

function RicercheDaId (value)
	{
		var valorediritorno = 0;
		var strDim = Dim_Alert(600, 300);
		
		switch(value)
		{
			
			case "ente":

				strDim = Dim_Alert(600, 300);
				var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=ricComune";
					   				
				valorediritorno = window.showModalDialog(stringa, "", strDim);
						
				if(valorediritorno!=null && valorediritorno!=undefined)
				{
					$('#comune').val(valorediritorno.comune);   						
					$('#prov').val(valorediritorno.prov_sigla);
					$('#CC').val(valorediritorno.CC);  			

					pattern_numeri = /[^0-9]/;
					cap_control = valorediritorno.cap;
						
						if(cap_control.match(pattern_numeri))
						{
							cap_control = cap_control.replace('x',0);
							cap_control = cap_control.replace('x',0);
							$('#cap').val(cap_control);
						}
						else
						{
							$('#cap').val(cap_control);  								
						}

						$('#via').val(null);
						$('#civico').val(null);
						$('#esponente').val(null);
						$('#interno').val(null);
						$('#dettagli').val(null);			
				}

			break;

		}	
	}
</script> 
  	  
<body class="sfondo_new_gitco" >
  
<table height=93% class="table_modale text_center pwidth550" border=0>
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth550" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Ufficio giudiziario</font></td>
	</tr>
</table>

<br>

<form id=form_info name=form_info action="sede_ufficio_salva.php" method=post>
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=p value=<?php echo $p; ?> >
<input name=invia_submit  id=invia_submit	type=hidden	value="<?php echo $invia; ?>">
<input type=hidden name=CC id=CC value="<?php echo $CC; ?>">
<input type=hidden name=ID id=ID value="<?php echo $id_ufficio; ?>">

<table align=center class="text_center pwidth500" border="0" cellspacing="3" cellpadding="0">
	<tr>
		<td class=text_left width=20%>Giurisdizione *</td>
		<td class=text_left width=35%>
			<select id=giuris name=giurisdizione onchange="">
				<option value=giudice >Giudice di Pace</option>
				<option value=tribunale >Tribunale</option>	
			</select>
		</td>
		<td class=text_left>Sezione </td>
		<td class=text_left colspan=3><input type=text id=sezione name=sezione size=3 value='<?php echo $sezione; ?>'>&nbsp;</td>
	</tr>
	<tr>
		<td class=text_left width=20%>Sede di *</td>
		<td class=text_left width=35%><input id=comune tabindex="13" class="sfondo_azzurro" name=comune type=text value="<?php echo $comune; ?>" size=18 ondblClick="RicercheDaId('ente');" readonly></td>
		<td class=text_left >Prov.</td>
		<td class=text_left colspan=3><input id=prov tabindex="14" class=" sfondo_azzurro" type=text name=prov value="<?php echo $provincia; ?>" size=2 readonly></td>	
	</tr>
	<tr>
       <td class=text_left width=17%>Indirizzo</td>
		<td class=text_left width=35%>
        	<input id=via tabindex="17" class="text_left" name=via type=text value="<?php echo $toponimo; ?>" size=18 >
        </td>
		<td class=text_left width=14%>CAP</td>
		<td class=text_left width=19%><input id=cap tabindex="16" class="text_right sfondo_azzurro" name=cap type=text value="<?php echo $cap; ?>" size=5 readonly ></td>
		<td colspan=2></td>
	</tr>
	<tr>
		<td class=text_left colspan=2>Civ. 
		<input id=civico tabindex="18" class="text_right" name="civico" 		type="text" value='<?php echo $civico; ?>' 		size=2 >
		 &nbsp;&nbsp;Esp. 
		<input id=esponente tabindex="19"	class="text_left" name="esponente" 	type="text" value='<?php echo $esp; ?>' 	size=2 >
		 &nbsp;&nbsp;Int. 
		<input id=interno tabindex="20"	class="text_right" name="interno" 	type="text" value='<?php echo $interno; ?>' 	size=2 >
		</td>
		<td class=text_left >Dettagli</td>
		<td class=text_left colspan=3>
		<input id=dettagli tabindex="21"	class="text_left" name="dettagli" 	type="text" value='<?php echo $dettagli; ?>' 	size=18 >
	</tr>
	<tr>
		<td class=text_left width=20%>Telefono</td>
	    <td class=text_left width=35%><input id=tel tabindex="22" class="text_right" name=tel type=text value='<?php echo $tel; ?>' size=18 ></td>
	    <td class=text_left width=14%>Fax</td>
	    <td class=text_left colspan=3><input id=fax tabindex="23"class="text_right" name=fax type=text value='<?php echo $fax; ?>' size=18 ></td>
	</tr>
</table>

<br>

<table class="text_center pwidth500" border="0">
	<tr>
		<td>
			<input type=button id=submit_click name=salva value=Salva class=button_red>
			<input type=button name=nuovo value=Nuovo class=button_azzurro onclick="new_ufficio();">
			<input type=button name=chiudi value=Chiudi class=button_azzurro onclick="self.close();">
			
		</td>
	</tr>
</table>
  
</form>

		</td>
	</tr>
</table>

<?php echo $layout; ?>

</body>
</html>