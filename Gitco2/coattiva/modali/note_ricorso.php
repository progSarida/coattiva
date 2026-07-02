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

$p = get_var('p');
$c = get_var('c');
$a = get_var('a');

$ricorso_id = get_var('ricorso_id');

$ric = new ricorso_generale($ricorso_id, $c);
$note = $ric->Note;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Note - Ricorso</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	  
<script>
function submit_buttons(value)
{
	
var ritorno = null;

	switch(value)
	{
		case "Delete":  
			ritorno = confirm("Si stanno eliminando i dati dal database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");	
			$('#ctrl_submit').val('Delete');

			break;
		case "Update": 	
			ritorno = confirm("Si stanno modificando i dati del database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
			$('#ctrl_submit').val('Update');

			break;
		}
	
		if(value=="Delete")
		{
			if(ritorno)
			{
				ritorno2 = confirm("Sei sicuro di voler eliminare i dati?");
				if(ritorno2)
					{return true;}
				else
					{return false;}
			}
			else
			{return false;}
		}
		else
		{	
			if(ritorno)
				{return	true;}
			else
				{return	false;}
		}

}

$(document).ready(function(){
	
	$('#form_note').ajaxForm(
			
	    function(value) {
			alert(value);
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
			window.name = "note";
			window.open("note_ricorso.php?ricorso_id=<?php echo $ricorso_id; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>","note");
			
		}
		else if(array_ritorno[0]=='ERROR')
		{		
			alert("Errore nel salvataggio delle rate.");
		}
		else
		{
			alert("Errore nella procedura");
		}
		
	});


$("#submit_click").click(function salva_form() { 

	control = submit_buttons('Update');
	if(control)
	    $("#form_note").submit();
	    
	});

$("#delete_click").click(function salva_form() {     

	control = submit_buttons('Delete');
	if(control)
    	$("#form_note").submit();
    
});
	
	});

</script>
  	  
<body class="sfondo_new_gitco" >
  
<table height=93% class="table_modale text_center pwidth550" border=0>
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth500" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Note</font></td>
	</tr>
</table>

<br>

<form id=form_note name=form_note action="../ricorso_salva.php" method=post>
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=p value=<?php echo $p; ?> >
<input name=invia_submit  id=invia_submit	type=hidden	value="Note">
<input name=ctrl_submit id=ctrl_submit	type=hidden	value="">
<input name=id_ricorso value="<?php echo $ricorso_id; ?>" type=hidden>

<table class="text_center pwidth500" border="0">
	<tr>
		<td class=text_center><textarea cols=55% rows=10% name=note><?php echo $note; ?></textarea></td>
	</tr>
</table>

<br>

<table class="text_center pwidth500" border="0">
	<tr>
		<td>
			<input type=button id=submit_click name=salva value=Salva class=button_azzurro>
			<input type=button id=delete_click name=cancella value=Elimina class=button_azzurro>
			<input type=button name=chiudi value=Chiudi class=button_azzurro onclick="self.close();">
		</td>
	</tr>
</table>
  
</form>

		</td>
	</tr>
</table>

</body>
</html>