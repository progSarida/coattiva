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
$mode = get_var('mode');

$id_doc = get_var('id_doc');

$documento = new documento($id_doc, $c);
$documento_atto = $documento->Atto;
$oggetto = $documento->Oggetto;
$data_stampa = $documento->Data_Stampa;
$data_creazione = $documento->Data_Creazione;
$tipo = $documento->Tipo;
$info_aggiuntive = $documento->Informazioni_Aggiuntive;
$contenuto = $documento->Contenuto;
$nome_file = $documento->File;

if($nome_file!="")
	$check_file = "<input type=checkbox name=del_file value='no' checked title=\"Per cancellare il file e' necessario deselezionarlo e cliccare il tasto salva\">";
else 
	$check_file = "<input type=hidden name=del_file value='no'>";

$path =  ATTI ."/". $c . "/Documenti/".$nome_file;
$path_file = mostra_file_path($path);

$layout = "<script>$('#tipo').val('".$tipo."')</script>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Inserimento manuale documento</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	  
<script>

var fn = function (e)
{
	if (!e)
	{
    	e = window.event;
	}

    var keycode = e.keyCode;
    if (e.which)
        keycode = e.which;

	//var src = e.srcElement;
	//if (e.target)
	//src = e.target;
	     	
//ESC
    if (27 == keycode)
    {
       // Firefox and other non IE browsers
       if (e.preventDefault)
       {
           e.preventDefault();
           e.stopPropagation();
       }
       // Internet Explorer
       else if (e.keyCode)
       {
           e.keyCode = 0;
           e.returnValue = false;
           e.cancelBubble = true;
       }		

       self.close();

       return false;
   }	    
};

document.onkeydown = fn;

$(function() {

	$( ".picker" ).datepicker();

});

$(document).ready(function(){
	
	$('#form_doc').ajaxForm(
			
	    function(value) {
	        var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
			
			link="posta.php?c=<?php echo $c?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&mode=<?php echo $mode; ?>";
			
			window.name = "ricerca";
			window.open(link, "ricerca");
		}
		else if(array_ritorno[0]=='DELETE')
		{		
			alert("Documento cancellato con successo.");
			link="posta.php?c=<?php echo $c?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&mode=<?php echo $mode; ?>";
			
			window.name = "ricerca";
			window.open(link, "ricerca");
		}
		else if(array_ritorno[0]=='ERROR')
		{		
			alert("Errore: "+array_ritorno[1]);
		}
		else
		{
			alert("Errore nella procedura");
			alert(value);
		}
		
	});


$("#submit_click").click(function salva_form() {     

	$('#form_doc').submit();

	});

$("#delete_click").click(function salva_form() {     

	$('#cancella').val('si');
	$('#form_doc').submit();

	});
	
	});

</script>
  	  
<body class="sfondo_new_gitco" >
  
<table height=93% class="table_modale text_center pwidth800" border=0>
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth750" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Inserimento manuale documento</font></td>
	</tr>
</table>

<br>

<form id=form_doc name=form_doc enctype="multipart/form-data" action="documento_salva.php" method=post>
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
<input type=hidden name=id_doc value="<?php echo $id_doc; ?>" >
<input type=hidden name=cancella id=cancella value="no">


<table class="text_center pwidth750" border="0">
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Documento:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<input class="text_left" name=atto id=atto value="<?php echo $documento_atto; ?>" size=35 >
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Tipo:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<select name=tipo id=tipo >
				<option>Inviato</option>
				<option>Ricevuto</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=4><hr></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Oggetto:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<input class="text_left" name=oggetto id=oggetto value="<?php echo $oggetto; ?>" size=55 >
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Contenuto:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<textarea class="text_left" name=contenuto id=contenuto cols=55 rows=11><?php echo $contenuto; ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=4><hr></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Data stampa:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			<input class="picker text_center" name=data_stampa id=data_stampa value="<?php echo from_mysql_date($data_stampa); ?>" size=9 >
		</td>
		<td class="text_center width48">
			<?php echo $check_file; ?>
			<a onMouseover="title='Scarica il file'" href="<?php echo $path_file; ?>" target="_blank" style="text-decoration: none;">
			<font class="font14 color_titolo"><?php echo $nome_file; ?></font>
			</a>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Info aggiuntive:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<textarea class="text_left" name=info id=info cols=55 rows=3><?php echo $info_aggiuntive; ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Upload file:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<input name="file_doc" id=file_doc type="file" />
		</td>
	</tr>
	
</table>

<br>

<table class="text_center pwidth750" border="0">
	<tr>
		<td>
			<input type=button id=submit_click name=salva value=Salva class=button_red>
<?php if($id_doc != 0){	?>
			<input type=button id=delete_click name=elimina value=Elimina class=button_red>
<?php } ?>			
			<input type=button name=chiudi value=Chiudi class=button_azzurro onclick="self.close();">
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