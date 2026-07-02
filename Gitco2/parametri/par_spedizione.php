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
$tipo_riscossione = get_var('tipo_riscossione');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$layout = "";

$par = new parametri_spedizione($c, $tipo_riscossione);
$invio_terzi = $par->Invio_Terzi;
$invio_pignorato = $par->Invio_Pignorato;
$invio_validazione = $par->Invio_Richieste_Validazione;
$layout.="<script>$('#terzi_invio').val('".$invio_terzi."')</script>";
$layout.="<script>$('#pignorato_invio').val('".$invio_pignorato."')</script>";
$layout.="<script>$('#validazione_invio').val('".$invio_validazione."')</script>";

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Gestione parametri</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen></link>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen></link>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
 
 
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
	control = submit_buttons('Salva');
	if(control)
	    $("#form_par_spedizione").submit();
}

//F4
function cancella_form() 
{     
	control = submit_buttons('Delete');
	if(control)
	    $("#form_par_spedizione").submit();
}

//F5
function annulla()
{
	location.href="par_spedizione.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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
	switch(value)
	{
		case 'next':

			link = "par_responsabili.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   		
   			break;

		case 'prev':

			link = "par_annuali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			
			break;	
	}

	top.location.href = link;
	
}

</script>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {
	
	 $( ".picker" ).datepicker();

	 });
	 
</script>


<script>

$(document).ready(function(){

	$("#submit_click").click( salva_form );
	
        
    $("#delete_click").click( cancella_form );
	
	$('#form_par_spedizione').ajaxForm(
			
	    function(value) {
		    alert(value);
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='SAVED')
		{		
			alert('Parametri salvati correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert('Salvataggio parametri fallito!');
		}
		if(array_ritorno[0]=='DELETED')
		{
			alert('Parametri cancellati correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR_DELETE')
		{
			alert('Cancellazione parametri fallita!');
		}

	    });
    
});


</script>

<body class="sfondo_new_gitco" >

<table class="table_azzurra text_center height7">
	<tr>
		<td class="text_left width1"><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td class="text_left width1"><br></td>
	</tr>
</table>

<table class="table_azzurra text_center height93" border=0>
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
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo"></a>
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
		<td><font class="titolo font16 under_decor">Parametri di spedizione (<?php echo $tipo_riscossione; ?>)</font></td>
	</tr>
</table>

<form name=form_par_spedizione id=form_par_spedizione method=post action="par_spedizione_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=tipo_riscossione value=<?php echo $tipo_riscossione; ?> >

<table class="table_interna text_center" border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td class="text_center" colspan=11><br></td>
	</tr>
	<tr>
		<td class="text_left width35" colspan=4><font class="color_titolo font_bold">Invio Pignoramento presso terzi</font></td>
		<td class="text_center width15" colspan=2>a pignorato</td>
		<td class="text_left width20" colspan=2>
			<select name=pignorato_invio id=pignorato_invio class=width98>
				<option></option>
				<option value="posta">Per Posta/Fax</option>
				<option value="email">Da Email</option>
				<option value="PEC">Da PEC</option>
			</select>
		</td>		
		<td class="text_center width10" colspan=1>a terzi</td>
		<td class="text_left width20" colspan=2>
			<select name=terzi_invio id=terzi_invio class=width98>
				<option></option>
				<option value="posta">Per Posta/Fax</option>
				<option value="email">Da Email</option>
				<option value="PEC">Da PEC</option>
			</select>
		</td>		
	</tr>
	<tr>
		<td class="text_left width35" colspan=4><font class="color_titolo font_bold">Invio richieste di validazione notifica<br></font></td>
		<td class="text_center width45" colspan=5>
			 ( Indirizzo, decesso, duplicato AR )
		</td>		
		<td class="text_left width20" colspan=2>
			<select name=validazione_invio id=validazione_invio class=width98>
				<option></option>
				<option value="posta">Per Posta/Fax</option>
				<option value="email">Da Email</option>
				<option value="PEC">Da PEC</option>
			</select>
		</td>		
	</tr>
	<tr>
		<td class="text_center" colspan=11><hr></td>
	</tr>
</table>

</form>
<br>
</td>
</tr>
</table>

<?php echo $layout; ?>

</body>
</html>