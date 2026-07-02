<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

 //include_once(CLS."/cls_CoazioneUtils.php");
 include_once(CLS."/cls_DateTimeInLine.php");
 include_once(INC."/header.php");
 include_once(CLS."/cls_math.php");
 include_once(CLS."/cls_Utils.php");

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}



//$cls_coazione = new cls_Coazione();
$cls_date = new cls_DateTimeI("IT",false);
$cls_math = new cls_math();
$cls_Utils = new cls_utils();

$p = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$pignoramento_id = $cls_help->getVar('pignoramento');
$id_notifica = $cls_help->getVar('id_notifica');

$query = "SELECT * FROM notifica_atto WHERE ID = '".$id_notifica."' AND CC = '".$c."'";
$notifica = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"notifica_atto");

//$notifica = new notifica_atto($id_notifica, $c);

$testo_riscontro = $notifica["Testo_Riscontro"];
$tipo_riscontro = $notifica["Tipo_Riscontro"];
$link_riscontro = $notifica["Link_Riscontro"];
$mezzo_riscontro = $notifica["Mezzo_Riscontro"];
$data_riscontro = $cls_date->Get_DateNewFormat($notifica["Data_Riscontro"],"DB");
$note_riscontro = $notifica["Note_Riscontro"];
$importo_riscontro = $notifica["Importo_Riscontro"];
$numero_trattenute = $notifica["Numero_Rate"];
$periodicita_trattenute = $notifica["Periodicita_Rate"];
$valore_trattenute = $notifica["Differenza_Importo"];
$data_inizio_trattenute = $cls_date->Get_DateNewFormat($notifica["Data_Inizio_Rate"],"DB");


if($importo_riscontro!=null)
	$importo_riscontro = $cls_math->conv_num(number_format($importo_riscontro,2));


if($link_riscontro !="")
	$check_file = "<input type=checkbox name=del_file value='no' checked title=\"Per cancellare il file e' necessario deselezionarlo e cliccare il tasto salva\">";
else
	$check_file = "<input type=hidden name=del_file value='no'>";

$path =  $cls_Utils->crea_dir(ATTI ."/". $c . "/Riscontri");

$path_file = $cls_Utils->mostra_file_path($path."/".$link_riscontro);

$layout = "<script>$('#tipo_riscontro').val('".$tipo_riscontro."');$('#mezzo_riscontro').val('".$mezzo_riscontro."');</script>";
$layout.= "<script>$('#valore_trattenute').val('".$valore_trattenute."');$('#periocita').val('".$periodicita_trattenute."');</script>";
$layout.= "<script>change_tipo();</script>";

?>

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
      window.opener.callParent("");
			self.close();
		}
		else if(array_ritorno[0]=='DELETE')
		{
			alert("Riscontro cancellato con successo.");
      window.opener.callParent("");
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
	if(tipo=="Positivo")
	{
		$('.display_elem').show();
	}
	else
	{
		$('.display_elem').hide();
		$('#importo_riscontro').val('');
	}
}
</script>

<table height=93% class="table_modale text_center pwidth800" border=0>
	<tr>
		<td valign=top>

  <br>

<table class="text_center pwidth750" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Gestione riscontro datore di lavoro</font></td>
	</tr>
</table>

<br>

<form id=form_riscontro name=form_riscontro enctype="multipart/form-data" action="riscontro_lavoro_salva.php" method=post>
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
<input type=hidden name=id_notifica value="<?php echo $id_notifica; ?>" >
<input type=hidden name=pignoramento value="<?php echo $pignoramento_id; ?>" >
<input type=hidden name=cancella id=cancella value="no">


<table class="text_center pwidth750" border="0">
	<tr>
		<td class="text_right width28">
			<span class="color_titolo">Mezzo riscontro:</span>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20">
			<select name=mezzo_riscontro id=mezzo_riscontro class="width100">
				<option></option>
				<option>PEC</option>
				<option>Posta ordinaria</option>
				<option>Raccomandata</option>
				<option>Altro</option>
			</select>
		</td>
		<td class="text_right width28">
			<span class="color_titolo">Tipo riscontro:</span>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20">
			<select name=tipo_riscontro id=tipo_riscontro class="width100" onchange="change_tipo();">
				<option></option>
				<option>Positivo</option>
				<option>Negativo</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<span class="display_elem color_titolo">Valore trattenute: ( rispetto al totale dell'importo dovuto )</span>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20">
			<select name=valore_trattenute id=valore_trattenute class="width100 display_elem">
				<option>Pari</option>
				<option>Inferiore</option>
			</select>
		</td>
		<td class="text_right width28">
			<span class="color_titolo display_elem">Periodicita' trattenute:</span>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20">
			<select name=periodicita id=periodicita class="width100 display_elem">
				<option>Mensile</option>
				<option>Bimestrale</option>
				<option>Trimestrale</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<span class="display_elem color_titolo">Importo singola trattenuta:</span>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20">
			<input class="display_elem text_right" name=importo_trattenuta id=importo_trattenuta value="<?php echo $importo_riscontro; ?>" size=9 onchange="func_numero(this);">
			<span class="display_elem">&euro;</span>
		</td>
		<td class="text_right width28">
			<span class="display_elem color_titolo">Numero trattenute:</span>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20"><input class="display_elem text_right" name=numero_trattenute id=numero_trattenute value="<?php echo $numero_trattenute; ?>" size=4 onchange="func_numero(this);">
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<span class="display_elem color_titolo">Data inizio trattenute:</span>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20">
			<input class="display_elem text_center picker" name=data_inizio_trattenute id=data_inizio_trattenute value="<?php echo $data_inizio_trattenute; ?>" size=9>
		</td>
		<td class="text_right width28">
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20"></td>
	</tr>
	<tr>
		<td class="text_left" colspan=6><hr></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Testo riscontro:</font>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width70" colspan=4>
			<textarea class="text_left" name=testo_riscontro id=testo_riscontro cols=55 rows=11><?php echo $testo_riscontro; ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=6><hr></td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Data riscontro:</font>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width20">
			<input class="picker text_center" name=data_riscontro id=data_riscontro value="<?php echo $data_riscontro; ?>" size=9 >
		</td>
		<td class="text_center width50" colspan=3>
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
		<td class="text_left width2"></td>
		<td class="text_left width70" colspan=4>
			<textarea class="text_left" name=note_riscontro id=note_riscontro cols=55 rows=3><?php echo $note_riscontro; ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Upload file:</font>
		</td>
		<td class="text_left width2"></td>
		<td class="text_left width70" colspan=4>
			<input name="file_riscontro" id=file_riscontro type="file" />
		</td>
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

<?php include(INC."/footer.php"); ?>
