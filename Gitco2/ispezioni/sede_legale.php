<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');
$tipo_sede = get_var('tipo_sede');
$id_sede = get_var('id_sede');

switch( $tipo_sede )
{
	case "lavoro":
		
		$tipo_sede_completo = "Datore di lavoro";
		$next_tipo = "banca";
		$prev_tipo = "altro";
		
		break;
	
	case "banca":
		
		$tipo_sede_completo = "Banca / Posta";
		$next_tipo = "inps";
		$prev_tipo = "lavoro";
	
		break;

	case "inps":
		
		$tipo_sede_completo = "INPS";
		$next_tipo = "altro";
		$prev_tipo = "banca";
	
		break;
		
	case "altro":
		
		$tipo_sede_completo = "Altro";
		$next_tipo = "lavoro";
		$prev_tipo = "inps";
	
		break;
}

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$sede = new sede_legale( $id_sede , $c , $tipo_sede );
$sede_id = $sede->ID;

$denominazione = "";
$PI = "";
$CF = "";
$CC = "";
$email = "";
$PEC = "";
$sito = "";
$via = "";
$int = "";
$esp = "";
$civ = "";
$dett = "";
$tel = "";
$fax = "";
$com = "";
$prov = "";
$cap = "";
$orario = "";

if( $sede_id != 0 )
{
	$denominazione = $sede->Denominazione;
	$CC = $sede->CC_Sede;
	$email = $sede->Mail;
	$PEC = $sede->PEC;
	$PI = $sede->Partita_Iva;
	$CF = $sede->Codice_Fiscale;
	$sito = $sede->Sito;
	$via = $sede->Toponimo;
	$int = $sede->Interno;
	$esp = $sede->Esponente;
	$civ = $sede->Civico;
	$dett = $sede->Dettagli;
	$tel = $sede->Telefono;
	$fax = $sede->Fax;
	$com = $sede->Comune;
	$prov = $sede->Provincia;
	$cap = $sede->Cap;
	$orario = $sede->Orario;
	
	
	if( $int==0 ) $int="";
	if( $civ==0 ) $civ="";
	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Sede legale</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
 
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
	control_salva = submit_buttons('Salva');
	if(control_salva)
			$("#form_sede").submit();
}

//F4
function cancella_form() 
{
	control_salva = submit_buttons('Delete');
	if(control_salva)
			$("#form_sede").submit();
}

//F5
function annulla() 
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_sede=<?php echo $tipo_sede; ?>";
	stringa = "sede_legale.php?"+stringaPHP;
	   	top.location.href = stringa;
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
var next_tipo = "<?php echo $next_tipo; ?>";
var prev_tipo = "<?php echo $prev_tipo; ?>";

function pagina_menu(value)
{
	switch(value)
	{
		case 'next':

			link = "sede_legale.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_sede="+next_tipo;
   		
   			break;

		case 'prev':

			link = "sede_legale.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_sede="+prev_tipo;
			
			break;	
	}

	top.location.href = link;
	
}

</script>

<!-- ********** MODALI AJAX ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}

function cerca_comune()
{
	strDim = Dim_Alert(600, 300);

	var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=ricComune";
		   				
	valorediritorno = window.showModalDialog(stringa, "", strDim);

	if( valorediritorno!=null && valorediritorno!=undefined )
	{
		$('#comune_id').val(valorediritorno.comune);   						
		$('#prov_id').val(valorediritorno.prov_sigla);
		$('#cap_id').val(valorediritorno.cap);
		$('#CC_id').val(valorediritorno.CC);
	}	
}

function cerca_sede()
{
	strDim = Dim_Alert(600, 300);

	var stringa = "/gitco2/ispezioni/modali/ricerca_sede.php?richiesta=sede&a=<?php echo $a;?>&c=<?php echo $c; ?>&tipo_sede=<?php echo $tipo_sede; ?>&tipo_sede_completo=<?php echo $tipo_sede_completo; ?>";
		   				
	valorediritorno = window.showModalDialog(stringa, "", strDim);

	if( valorediritorno!=null && valorediritorno!=undefined )
	{
		stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_sede=<?php echo $tipo_sede; ?>";
		stringa = "sede_legale.php?"+stringaPHP+"&id_sede="+valorediritorno;
		   	top.location.href = stringa;
	}	
}

</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){

	$("#submit_click").click( salva_form );	
        
    $("#delete_click").click( cancella_form );
	
	$('#form_sede').ajaxForm(
			
	    function(value) {
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='SAVED')
		{		
			alert('Sede salvata correttamente!');
			stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_sede=<?php echo $tipo_sede; ?>";
			stringa = "sede_legale.php?"+stringaPHP+"&id_sede="+array_ritorno[1];
			   	top.location.href = stringa;
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert('Salvataggio sede fallito!');
		}
		if(array_ritorno[0]=='DELETED')
		{
			alert('Sede cancellata correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR_DELETE')
		{
			alert('Cancellazione sede fallita!');
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
			<a onMouseover="title='Modifica'" href="#" onClick="">
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
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick="">
          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick="">
          	<img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo">
          	</a>
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
		<td><font class="titolo font16 under_decor"><?php echo $tipo_sede_completo; ?></font></td>
	</tr>
</table>

<form name=form_sede id=form_sede method=post action="sede_legale_salva.php">

<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=sede_id  value="<?php echo $sede_id; ?>" >
<input type=hidden name=tipo_sede value="<?php echo $tipo_sede; ?>">

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $CC; ?>">


<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_left width15">Denominazione</td>
		<td class="text_left" colspan=4><input class="text_left width95" id=denom_id name=denom value="<?php echo $denominazione; ?>" ></td>
		<td class="text_left" colspan=3><input class="sfondo_azzurro" type=button id=cerca_denom name=cerca_denom value="Ricerca" onclick="cerca_sede();"></td>
	</tr>
	<tr>
		<td class="text_left width10">Partita Iva</td>
		<td class="text_left width22"><input class="text_right" maxlength=11 id=PI_id name=PI size=11 value="<?php echo $PI; ?>" ></td>
		<td class="text_left width15">Codice Fiscale</td>
		<td class="text_left width23" colspan=2><input class="text_left" maxlength=16 id=CF_id name=CF size=20 value="<?php echo $CF; ?>" ></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left" colspan=8><hr></td>		
	</tr>
	<tr>
		<td class="text_left width15">Comune</td>
		<td class="text_left width22"><input class="sfondo_azzurro text_left" readonly name=comune id=comune_id value="<?php echo $com; ?>" size=15 onclick="cerca_comune();"></td>
		<td class="text_left width15">Provincia</td>
		<td class="text_left width15"><input class="sfondo_azzurro text_left" readonly id=prov_id name=prov size=1 value="<?php echo $prov; ?>"></td>
		<td class="text_left width8" >CAP</td>
		<td class="text_left width10"><input class="sfondo_azzurro text_center" readonly id=cap_id name=cap size=4 value="<?php echo $cap; ?>"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Indirizzo</td>
		<td class="text_left width22">
        	<input id=via class="text_left" name=via type=text value="<?php echo $via; ?>" size=18>
        </td>
		<td class="text_left width63" colspan=6>Civ. 
		&nbsp;<input type="text" id=civico 	   class="text_right"  name="civico"  	value="<?php echo $civ; ?>"  size=2 >
		&nbsp;Esp. 
		&nbsp;<input type="text" id=esponente  class="text_left"   name="esponente" value="<?php echo $esp; ?>"  size=2 >
		&nbsp;Int. 	
		&nbsp;<input type="text" id=interno    class="text_right"  name="interno" 	value="<?php echo $int; ?>"  size=2 >
		&nbsp;Dettagli
		&nbsp;<input type="text" id=dettagli   class="text_left"   name="dettagli" 	value="<?php echo $dett; ?>"  size=20>
		</td>
	</tr>	
	<tr>
		<td class="text_left width15">Telefono</td>
		<td class="text_left width22"><input class="text_right" id=tel_id name=tel size=18 value="<?php echo $tel; ?>"></td>
		<td class="text_left width15">Fax</td>
		<td class="text_left width23" colspan=2><input class="text_right" id=fax_id name=fax size=18 value="<?php echo $fax; ?>"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Email</td>
		<td class="text_left width22"><input class="text_left" id=email_id name=email size=18 value="<?php echo $email; ?>" ></td>
		<td class="text_left width30" colspan=2>PEC
		&nbsp;&nbsp;&nbsp;&nbsp;<input class="text_left" id=pec_id name=PEC size=18 value="<?php echo $PEC; ?>" ></td>
		<td class="text_left width8">Sito</td>
		<td class="text_left width25" colspan=3><input class="text_left" id=sito_id name=sito size=16 value="<?php echo $sito; ?>" ></td>
	</tr>
	<tr>
		<td class="text_left width15">Orario</td>
		<td class="text_left" colspan=7><textarea class="text_left" id=orario_id name=orario rows=3 cols=65><?php echo $orario; ?></textarea></td>
	</tr>
</table>

</form>

</td>
</tr>
</table>

</body>
</html>