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

$pignoramento_id = get_var('pignoramento');
$id_notifica = get_var('id_notifica');

$notifica = new notifica_atto($id_notifica, $c);

$testo_riscontro = $notifica->Testo_Riscontro;
$tipo_riscontro = $notifica->Tipo_Riscontro;
$link_riscontro = $notifica->Link_Riscontro;
$mezzo_riscontro = $notifica->Mezzo_Riscontro;
$data_riscontro = from_mysql_date($notifica->Data_Riscontro);
$note_riscontro = $notifica->Note_Riscontro;
$importo_riscontro = $notifica->Importo_Riscontro;
if($importo_riscontro!=null)
	$importo_riscontro = conv_num(number_format($importo_riscontro,2));

$data_deposito = $notifica->Data_Deposito;
$stato_deposito = $notifica->Stato_Deposito;
$data_vendita = $notifica->Data_Vendita;
$stato_vendita = $notifica->Stato_Vendita;
$prezzo_vendita = $notifica->Prezzo_Vendita;
if($prezzo_vendita!=null)
	$prezzo_vendita = conv_num(number_format($prezzo_vendita,2));

if($link_riscontro !="")
	$check_file = "<input type=checkbox name=del_file value='no' checked title=\"Per cancellare il file e' necessario deselezionarlo e cliccare il tasto salva\">";
else
	$check_file = "<input type=hidden name=del_file value='no'>";

$path =  crea_dir(ATTI ."/". $c . "/Riscontri");

$path_file = mostra_file_path($path."/".$link_riscontro);

$layout = "<script>$('#tipo_riscontro').val('".$tipo_riscontro."');$('#mezzo_riscontro').val('".$mezzo_riscontro."');</script>";
$layout.= "<script>$('#stato_deposito').val('".$stato_deposito."');$('#stato_vendita').val('".$stato_vendita."');</script>";
$layout.= "<script>change_tipo();change_deposito();</script>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Riscontro IVG</title>
	
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
	
	$('#form_riscontro').ajaxForm(
			
	    function(value) {
	        var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
			self.close();
		}
		else if(array_ritorno[0]=='DELETE')
		{		
			alert("Riscontro cancellato con successo.");
			self.close();
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

	$('#form_riscontro').submit();

	});

$("#delete_click").click(function salva_form() {     

	$('#cancella').val('si');
	$('#form_riscontro').submit();

	});
	
	});

function func_numero(oggetto)
{
	elem = $(oggetto);
	
	id_campo = elem.attr('id');
	valore = control_numero(id_campo);
	if(valore===false)
	{
		alert("Inserire un valore numerico.");
		elem.val('');
	}
	else
		elem.val(valore);
}

function change_tipo()
{
	tipo = $('#tipo_riscontro').val();
	if(tipo=="Parziale" || tipo="Positivo")
	{
		$('.importo_td').show();
	}
	else
	{
		$('.importo_td').hide();
		$('#importo_riscontro').val('');
	}
}

function change_deposito()
{
	tipo = $('#stato_deposito').val();
	if(tipo=="Effettuato")
	{
		$('.tr_vendita').show();
	}
	else
	{
		$('.tr_vendita').hide();
		$('#prezzo_vendita').val('');
		$('#stato_vendita').val('');
		$('#data_vendita').val('');
	}
}
</script>
  	  
<body class="sfondo_new_gitco" >
  
<table height=93% class="table_modale text_center pwidth800" border=0>
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth750" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Riscontro IVG</font></td>
	</tr>
</table>

<br>

<form id=form_riscontro name=form_riscontro enctype="multipart/form-data" action="riscontro_ivg_salva.php" method=post>
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
<input type=hidden name=id_notifica value="<?php echo $id_notifica; ?>" >
<input type=hidden name=pignoramento value="<?php echo $pignoramento_id; ?>" >
<input type=hidden name=cancella id=cancella value="no">


<table class="text_center pwidth750" border="0">
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Mezzo riscontro:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			<select name=mezzo_riscontro id=mezzo_riscontro class="width100">
				<option></option>
				<option>PEC</option>
				<option>Posta ordinaria</option>
				<option>Raccomandata</option>
				<option>Altro</option>
			</select>
		</td>
		<td class="text_center width48" colspan=2></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Tipo riscontro:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			<select name=tipo_riscontro id=tipo_riscontro class="width100" onchange="change_tipo();">
				<option></option>
				<option>Positivo</option>
				<option>Negativo</option>
				<option>Parziale</option>
			</select>
		</td>
		<td class="text_center width48" colspan=2>
		<font class="importo_td color_titolo">Importo:</font>
		&nbsp;&nbsp;&nbsp;&nbsp;
			<input class="importo_td text_right" name=importo_riscontro id=importo_riscontro value="<?php echo $importo_riscontro; ?>" size=9 onchange="func_numero(this);">
		<font class="importo_td">&euro;</font>
		</td>
	</tr>
	
	<tr>
		<td class="text_left" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Testo riscontro:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=3>
			<textarea class="text_left" name=testo_riscontro id=testo_riscontro cols=55 rows=8><?php echo $testo_riscontro; ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Data riscontro:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			<input class="picker text_center" name=data_riscontro id=data_riscontro value="<?php echo $data_riscontro; ?>" size=9 >
		</td>
		<td class="text_center width48" colspan=2>
			<?php echo $check_file; ?>
			<a onMouseover="title='Scarica il file'" href="<?php echo $path_file; ?>" target="_blank" style="text-decoration: none;">
			<font class="font14 color_titolo"><?php echo $link_riscontro; ?></font>
			</a>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Note:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=3>
			<textarea class="text_left" name=note_riscontro id=note_riscontro cols=55 rows=3><?php echo $note_riscontro; ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Upload file:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<input name="file_riscontro" id=file_riscontro type="file" />
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo"></font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			
		</td>
		<td class="text_center width48" colspan=2></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Stato deposito:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			<select name=stato_deposito id=stato_deposito class="width100" onchange="change_deposito();">
				<option></option>
				<option>Effettuato</option>
				<option>Non effettuato</option>
			</select>
		</td>
		<td class="text_right width24">
		<font class="color_titolo tr_vendita">Data deposito:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font>
		</td>
		<td class="text_left width24">
			<input class="picker text_center tr_vendita" name=data_deposito id=data_deposito value="<?php echo from_mysql_date($data_deposito); ?>" size=9 >
		</td>
	</tr>
	<tr class="tr_vendita">
		<td class="text_right width28">
			<font class="color_titolo">Stato vendita:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			<select name=stato_vendita id=stato_vendita class="width100" onchange="change_stato();">
				<option></option>
				<option>Venduto</option>
				<option>Invenduto</option>
			</select>
		</td>
		<td class="text_right width24">
			<font class="color_titolo">Data vendita:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font>
		</td>
		<td class="text_left width24">
			<input class="picker text_center" name=data_vendita id=data_vendita value="<?php echo from_mysql_date($data_vendita); ?>" size=9 >
			
		</td>
	</tr>
	<tr class="tr_vendita">
		<td class="text_right width28">
			<font class="color_titolo">Prezzo vendita:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width20">
			<input class="text_right" name=prezzo_vendita id=prezzo_vendita value="<?php echo $prezzo_vendita; ?>" size=9 onchange="func_numero(this);">
			&euro;
		</td>
		<td class="text_right width24">
			<font class="color_titolo"></font>
		</td>
		<td class="text_left width24">
			
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=5><hr></td>
	</tr>
	
</table>

<br>

<table class="text_center pwidth750" border="0">
	<tr>
		<td>
			<input type=button id=submit_click name=salva value=Salva class=button_red>
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