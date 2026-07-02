<?php

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";*/

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include INC."/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_date = new cls_DateTimeI("IT",false);

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

//include CLASSI . "/ruolo.php";
//include CLASSI . "/enti_esterni.php";

$p = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$mode = $cls_help->getVar('mode');

$id_doc = $cls_help->getVar('id_doc');
$id_ente = $cls_help->getVar('id_ente');

$query = "SELECT * FROM documento_ente WHERE ID = '".$id_doc."' AND CC = '".$c."'";
$param = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"documento_ente");

$nome_file =  $param["File"];

if($nome_file!="")
	$check_file = "<input type=checkbox name=del_file value='no' checked title=\"Per cancellare il file e' necessario deselezionarlo e cliccare il tasto salva\">";
else
	$check_file = "<input type=hidden name=del_file value='no'>";

$path =  ATTI ."/". $c . "/Documenti/".$nome_file;

$path_file = SUPER_WEB_ROOT.substr( $path , strpos( $path , "/archivio/" ));

$layout = "<script>$('#tipo').val('".$param["Tipo"]."')</script>";

?>


<title>Gestione documento</title>


<script>
function salva_doc_form()
{
		$('#form_doc_ente').submit();
}

function cancella_doc_form()
{
		$('#cancella').val('si');
		$('#form_doc_ente').submit();

}
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

	$('#form_doc_ente').ajaxForm(

	    function(value) {
	        var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='OK')
		{
			alert('Salvataggio effettuato correttamente!');
			window.returnValue = "OK";
			self.close();
		}
		else if(array_ritorno[0]=='DELETE')
		{
			alert("Documento cancellato con successo.");
			window.returnValue = "OK";
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


$("#submit_click").click( salva_doc_form );

$("#delete_click").click( cancella_doc_form );

	});


function apri_file(value)
{
	window.open(value);
	self.close();
}
</script>


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

<form id=form_doc_ente name=form_doc_ente method=post enctype="multipart/form-data" action="documento_ente_salva.php">
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=id_ente value="<?php echo $id_ente; ?>" >
<input type=hidden name=id_doc value="<?php echo $id_doc; ?>" >
<input type=hidden name=cancella id=cancella value="no">


<table class="text_center pwidth750" border="0">
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Documento:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<input class="text_left" name=atto id=atto value="<?php echo $param["Atto"]; ?>" size=35 >
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
			<input class="text_left" name=oggetto id=oggetto value="<?php echo $param["Oggetto"]; ?>" size=55 >
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Contenuto:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<textarea class="text_left" name=contenuto id=contenuto cols=55 rows=11><?php echo $param["Contenuto"]; ?></textarea>
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
			<input class="picker text_center" name=data_stampa id=data_stampa value="<?php echo $cls_date->Get_DateNewFormat($param["Data_Stampa"],"DB"); ?>" size=9 >
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
			<textarea class="text_left" name=info id=info cols=55 rows=3><?php echo $param["Informazioni_Aggiuntive"]; ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="text_right width28">
			<font class="color_titolo">Upload file:</font>
		</td>
		<td class="text_left width4"></td>
		<td class="text_left width68" colspan=2>
			<input name="file_doc_ente" id="file_doc_ente" type="file" >
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

<?php echo $layout; ?>
        </td>
    </tr>
</table>
</div>
</div>
</body>
</html>
