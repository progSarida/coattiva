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
include CLASSI. "/notifiche_importate.php";

$p = get_var('p');
$c = get_var('c');
$a = get_var('a');

$partita_ID = get_var('partita');
$atto_ID = get_var('atto');

$atto = new atto($atto_ID, $c);
$spedizione = $atto->info_spedizione();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Spedizione - Gestione</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	  
<script>

$(function() {

	$( ".picker" ).datepicker();

});

$(document).ready(function(){
	
	$('#form_spedizione').ajaxForm(
			
	    function(value) {
			alert(value);
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
		}
		else if(array_ritorno[0]=='ERROR')
		{		
			alert("Errore nel salvataggio.");
		}
		else
		{
			alert("Errore nella procedura");
		}
		
	});


$("#submit_click").click(function salva_form() {     

	$('#form_spedizione').submit();

	});
	
	});

</script>
  	  
<body class="sfondo_new_gitco" >
  
<table height=93% class="table_modale text_center pwidth550" border=0>
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth450" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Informazioni Spedizione</font></td>
	</tr>
</table>

<br>

<table class="text_center pwidth450" border="0">
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Numero flusso:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_left width100" name=num_flusso id=num_flusso readonly value="<?php echo $spedizione->Ms_Lotto; ?>" >
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=3><hr></td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Data spedizione:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_center" name=data_spedizione id=data_spedizione readonly value="<?php echo from_mysql_date($spedizione->Data_Spedizione); ?>" size=10 >
		</td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Avviso di ricevimento numero:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_left" name=estremi_spedizione id=estremi_spedizione readonly value="<?php echo $spedizione->Ms_Ric_Num; ?>" size=15 >
		</td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Raccomandata numero:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_left" name=estremi_ar id=estremi_ar readonly value="<?php echo $spedizione->Ms_Rac_Num; ?>" size=15 >
		</td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">LOG modificato il:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_center" name=log id=log readonly value="<?php echo from_mysql_date($spedizione->Log_Modificato_Data); ?>" size=10 >
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=3><hr></td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Scatola:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_right" name=scatola id=scatola readonly value="<?php echo $spedizione->Scatola; ?>" size=2 >
		</td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Lotto:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_right" name=lotto id=lotto readonly value="<?php echo $spedizione->Lotto; ?>" size=2 >
		</td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Posizione:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_right" name=posizione id=posizione readonly value="<?php echo $spedizione->Posizione; ?>" size=2 >
		</td>
	</tr>
	<tr>
		<td class="text_right width48">
			<font class="color_titolo">Dati importati il:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width48">
			<input class="sfondo_ricerca text_center" name=data_importazione id=data_importazione readonly value="<?php echo from_mysql_date($spedizione->Data_Importazione); ?>" size=10 >
		</td>
	</tr>
</table>

<br>

		</td>
	</tr>
</table>

</body>
</html>