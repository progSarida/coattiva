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

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$para = new parametri_atto( $c );
$par_id = $para->ID;
if($par_id==null) $par_id = 0;

$layout = "";

$tipo_protocollo = "";
$fisso_protocollo = "";

if($para->ID != null)
{	
	$tipo_protocollo = $para->Tipo_Protocollo;
	$fisso_protocollo = $para->Fisso_Protocollo;
    $data_protocollo = $para->Data_Protocollo;
	if($tipo_protocollo!="fisso")	
		$layout.= "<script>$('#fisso').addClass('sfondo_grigio').prop('readonly',true);</script>";
}
else 
	$layout.= "<script>$('#fisso').addClass('sfondo_grigio').prop('readonly',true);</script>";

	$layout.= "<script>$('[name=tipo_protocollo][value=".$tipo_protocollo."]').prop('checked',true);</script>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
 
 
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
	    $("#form_par_atto").submit();
}

//F4
function cancella_form() 
{     
	control = submit_buttons('Delete');
	if(control)
	    $("#form_par_atto").submit();
}

//F5
function annulla()
{
	location.href="par_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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
	return true;
}

//PAG SU
function pag_suc()
{
	return true;
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


</script>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {
	
	 $( ".picker" ).datepicker();

	 });

function change_prot()
{
	tipo = $('[name=tipo_protocollo]:checked').val();

	if(tipo=="fisso")
		$('#fisso').removeClass('sfondo_grigio').prop('readonly',false);
	else
		$('#fisso').addClass('sfondo_grigio').prop('readonly',true).val('');
}

</script>

<script>

$(document).ready(function(){

	$("#submit_click").click( salva_form );
	
        
    $("#delete_click").click( cancella_form );
	
	$('#form_par_atto').ajaxForm(
			
	    function(value) {
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

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
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
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
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
		<td><font class="titolo font16 under_decor">Parametri atti</font></td>
	</tr>
</table>
<br>

<form name=form_par_atto id=form_par_atto method=post action="par_atto_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >

<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>"   	>

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_center" colspan=6><b>Protocollo</b></td>
	</tr>
	<tr>
		<td class="text_center" colspan=6><hr></td>
	</tr>
	<tr>
		<td class="text_left width10">Tipo</td>
		<td class="text_center width15"><input type="radio" name=tipo_protocollo value="" onclick="change_prot()" checked> Assente</td>
		<td class="text_center width15"><input type="radio" name=tipo_protocollo value="progressivo" onclick="change_prot()"> Progressivo</td>
		<td class="text_center width15"><input type="radio" name=tipo_protocollo value="fisso" onclick="change_prot()"> Fisso</td>
		<td class="text_left width20"><input name=fisso_protocollo id=fisso value="<?php echo $fisso_protocollo; ?>" size=8></td>
        <td class="text_left width25">Data &nbsp;<input class="picker text_center" name=data_protocollo value="<?php echo from_mysql_date($data_protocollo); ?>" size=9></td>
	</tr>
	<tr>
		<td class="text_center" colspan=6><hr></td>
	</tr>
</table>

</form>

</td>
</tr>
</table>

<?php echo $layout; ?>

</body>
</html>