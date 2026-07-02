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

$inserimento = get_var('inserimento');
$p = get_var('p');
$c = get_var('c');
$a = get_var('a');

$ricorso_id = get_var('ricorso_id');
$id_udienza = get_var('id_udienza');
if($id_udienza==null)	$id_udienza = 0;

$ric = new ricorso_generale($ricorso_id, $c);
$udienze = $ric->Udienze;

$layout = "";

if(count($udienze)>0 && $inserimento == null)
{
	$post = 1;
}
else
{	
	$udi = new iter_udienze($id_udienza);
	
	$data_udi = $udi->Data_Udienza;
	$ora_udi = $udi->Ora_Udienza;
	if($ora_udi=="") $ora_udi = "00:00";
	$grado_udi = $udi->Grado;
	$tipo_udi = $udi->Tipo;
	$trattaz_udi = $udi->Trattazione;
	$esito_udi = $udi->Esito;
	
	$layout .= "<script>$('#grado').val('".$grado_udi."');</script>";
	$layout .= "<script>$('#tipo').val('".$tipo_udi."');</script>";
	$layout .= "<script>$('#trattazione').val('".$trattaz_udi."');</script>";
	$layout .= "<script>$('#esito').val('".$esito_udi."');</script>";
	
	$post = 0;
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Iter Udienze</title>
	
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

$(function() {

	$('#data_udi').datepicker();
	
	 });

$(document).ready(function(){
	
	$('#form_udienze').ajaxForm(
			
	    function(value) {
			alert(value);
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
			window.name = "Ricerca";
			window.open("iter_udienze.php?id_udienza=<?php echo $id_udienza; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ricorso_id=<?php echo $ricorso_id; ?>","Ricerca");	
			
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

	alert($('#ricorso_id').val());
	control = submit_buttons('Update');
	if(control)
	    $("#form_udienze").submit();
	    
	});

$("#delete_click").click(function salva_form() {     

	alert($('#id_udienza').val());
	control = submit_buttons('Delete');
	if(control)
    	$("#form_udienze").submit();
    
});
	
	});

function dettagli_udienza(value)
{
	window.name = "Ricerca";
	window.open("iter_udienze.php?inserimento=1&id_udienza="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ricorso_id=<?php echo $ricorso_id; ?>","Ricerca");	
}

function new_udienza()
{
	$('#id_udienza').val(0);
	$('#data_udi').val('');
	$('#ora_udi').val('00:00');
	$('#tipo').val('');
	$('#trattazione').val('');
	$('#esito').val('');
	$('#grado').val('');
}

</script>
  	  
<body class="sfondo_new_gitco" >
  
<table height=93% class="table_modale text_center pwidth680" border=0>
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth650" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Iter Udienze</font></td>
	</tr>
</table>

<br>

<?php if($post==1)
{?>

<table class="text_center pwidth650" cellspacing=0 border=0 style="border:1px solid black;">
	<tr class="text_center riga_dispari" style="height:35px;" >
		<td width=5%><br></td>
		<td width=1%><br></td>
		<td width=13% class="text_center"><b>Data</b></td>
		<td width=1%><br></td>
		<td width=8% class="text_center"><b>Ora</b></td>
		<td width=1%><br></td>
		<td width=9% class="text_center"><b>Grado</b></td>
		<td width=1%><br></td>
		<td width=21% class="text_center"><b>Tipo</b></td>
		<td width=1%><br></td>
		<td width=20% class="text_center"><b>Trattazione</b></td>
		<td width=1%><br></td>
		<td width=20% class="text_center"><b>Esito</b></td>
		<td width=1%><br></td>
	</tr>
	
<?php for($i=0;$i<count($udienze);$i++)
	{
		
		$y = $i;
		
		if ($y++ % 2)
		{	$stile_riga = 'class="riga_dispari text_center"'	;	}
		else
		{	$stile_riga = 'class="riga_pari text_center"'	;		}
		
		
		$orario = substr($udienze[$i]->Ora_Udienza , 0 ,5);
		
		switch($udienze[$i]->Grado)
		{
			case 1:	$grado = "Primo"; 	break;
			case 2: $grado = "Secondo";	break;
			case 3: $grado = "Terzo";	break;
		}
		
		switch($udienze[$i]->Tipo)
		{
			case "ric_sosp":	$tipo = "Richiesta sospensiva"; 	break;
			case "ist_rinvio":	$tipo = "Istanza di rinvio";	 	break;
			case "ist_riunif":	$tipo = "Istanza riunificazione"; 	break;
			case "disc_merito":	$tipo = "Discussione di merito"; 	break;
			case "prima_comp":	$tipo = "Prima comparizione";	 	break;
		}
		
		switch($udienze[$i]->Trattazione)
		{
			case "pubbl_udi":	$trattaz = "Pubblica udienza";		break;
			case "camera_cons":	$trattaz = "Camera di consiglio";	break;
		}
		
		switch($udienze[$i]->Esito)
		{
			case "attesa_giud":	$esito = "Attesa di giudizio";		break;
			case "rinviato":	$esito = "Rinviato";	 			break;
		}
		
		
		
	?>

	<tr <?php echo $stile_riga; ?> >
		<td width=5%>
			<input type=image src="/gitco2/immagini/select.png" 
			style=" width:25px; height:25px; border:0; " title="Dettagli Udienza" 
			onClick="dettagli_udienza(<?php echo $udienze[$i]->ID; ?>);" >
		</td>
		<td width=1%><br></td>
		<td width=13% class="text_center"><?php echo from_mysql_date($udienze[$i]->Data_Udienza); ?></td>
		<td width=1%><br></td>
		<td width=8% class="text_center"><?php echo $orario; ?></td>
		<td width=1%><br></td>
		<td width=9% class="text_center"><?php echo $grado; ?></td>
		<td width=1%><br></td>
		<td width=21% class="text_center"><?php echo $tipo; ?></td>
		<td width=1%><br></td>
		<td width=20% class="text_center"><?php echo $trattaz; ?></td>
		<td width=1%><br></td>
		<td width=20% class="text_center"><?php echo $esito; ?></td>
		<td width=1%><br></td>
	</tr>

	
<?php }?>

</table>
<?php } 
else if($post==0)
{
	
?>


<form id=form_udienze name=form_udienze action="../ricorso_salva.php" method=post>
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=p value=<?php echo $p; ?> >
<input type=hidden name=invia_submit  	id=invia_submit		value="Udienze"	>
<input type=hidden name=ctrl_submit 	id=ctrl_submit		value=""		>
<input type=hidden name=id_udienza 		id=id_udienza 	value="<?php echo $id_udienza; ?>" 	>
<input type=hidden name=id_ricorso		id=ricorso_id 	value="<?php echo $ricorso_id; ?>" 	>


<table class="text_center pwidth650" border="0">
	<tr>
		<td class=text_left>Data</td>
		<td class=text_left>
			<input name=data_udi id=data_udi 	class=text_center size=9 value="<?php echo from_mysql_date($data_udi); ?>" >
		</td>
		<td class=text_left>Orario</td>
		<td class=text_left>
			<input name=ora_udi  id=ora_udi 	class=text_center size=6 value="<?php echo substr($ora_udi,0,5); ?>" >
		</td>
	</tr>
	<tr>
		<td class=text_left>Grado</td>
		<td class=text_left>
			<select name=grado 	 id=grado	>
				<option></option>
				<option value=1 >Primo</option>
				<option value=2 >Secondo</option>
				<option value=3 >Terzo</option>
			</select>
		</td>
		<td class=text_left>Tipo udienza</td>
		<td class=text_left>
			<select name=tipo 	 id=tipo	>
				<option></option>
				<option value=ric_sosp >Richiesta sospensiva</option>
				<option value=ist_rinvio >Istanza di rinvio</option>
				<option value=ist_riunif >Istanza riunificazione</option>
				<option value=disc_merito >Discussione di merito</option>
				<option value=prima_comp >Prima comparizione</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class=text_left>Trattazione</td>
		<td class=text_left>
			<select name=trattazione id=trattazione >
				<option></option>
				<option value=pubbl_udi >Pubblica udienza</option>
				<option value=camera_cons >Camera di consiglio</option>
			</select>
		</td>
		<td class=text_left>Esito</td>
		<td class=text_left>
			<select name=esito id=esito >
				<option></option>
				<option value=attesa_giud >Attesa di giudizio</option>
				<option value=rinviato >Rinviato</option>
			</select>
		</td>
	</tr>
</table>

<br>

<table class="text_center pwidth650" border="0">
	<tr>
		<td>
			<input type=button id=submit_click name=salva value=Salva class=button_azzurro>
			<input type=button id=delete_click name=cancella value=Elimina class=button_azzurro>
			<input type=button name=nuovo  value=Nuovo  class=button_azzurro onclick="new_udienza();">
			<input type=button name=chiudi value=Chiudi class=button_azzurro onclick="self.close();">
		</td>
	</tr>
</table>
  
</form>

<?php } echo $layout; ?>

		</td>
	</tr>
</table>

</body>
</html>