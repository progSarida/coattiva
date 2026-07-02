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
$layout="";
$layout.="<script>$('.tr_tipo_banca').hide();</script>";
$layout.="<script>$('#filiale_sede').hide();</script>";
$layout.="<script>$('#avvisoBanca').hide();</script>";

switch( $tipo_sede )
{
	case "banca":
		
		$tipo_sede_completo = "Banca";
		$next_tipo = "avvocato";
		$prev_tipo = "avvocato";
		$layout.="<script>$('.tr_tipo_banca').show();</script>";
		$layout.="<script>sede_banca();</script>";
		$layout.="<script>$('#avvisoBanca').show();</script>";
		$CC_sede = "*****";
		
		break;
		
	case "avvocato":
	
		$tipo_sede_completo = "Avvocati";
		$next_tipo = "banca";
		$prev_tipo = "banca";
		$CC_sede = $c;
		
	
		break;
	
	default:
		
		$tipo_sede_completo = "NON DEFINITA";
		$next_tipo = "";
		$prev_tipo = "";
	
		break;
}

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$sede = new sede_legale( $id_sede , $CC_sede , $tipo_sede );

$sede_id = $sede->ID;
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
$tipo_banca = $sede->Tipo_Banca;

if($tipo_banca!="")
	$layout.="<script>$('#tipo_banca').val('".$tipo_banca."');sede_banca();</script>";

$id_collegamento = $sede->ID_Collegamento;
if($id_collegamento!=null)	
	$denominazione_sede = $sede->Sede_Collegamento->Denominazione." (".$sede->Sede_Collegamento->Comune.")";
else 
	$denominazione_sede = "";


if( $int==0 ) $int="";
if( $civ==0 ) $civ="";



?>

<!DOCTYPE html>
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
	campi = controllaCampi();
	if(campi)
	{
		control_salva = submit_buttons('Salva');
		if(control_salva)
				$("#form_sede").submit();
	}
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

function callParent(valorediritorno){
    switch(selectParent){
        case "comune":

            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                cap = valorediritorno.cap;
                for(var contatore=0;contatore<2;contatore++)
                {
                    cap = cap.replace("x", "0");
                }

                $('#comune_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(cap);
                $('#CC_id').val(valorediritorno.CC);
            }

            break;
        case "sede":

            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                if(selectRif=="banca")
                {
                    $('#id_collegamento').val(valorediritorno.ID);
                    $('#banca_sede').text(valorediritorno.Denominazione+" ("+valorediritorno.Comune+")");
                }
                else
                {
                    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_sede=<?php echo $tipo_sede; ?>";
                    stringa = "sede_legale.php?"+stringaPHP+"&id_sede="+valorediritorno.ID;
                    top.location.href = stringa;
                }
            }
            break;
    }

}

var selectParent = "";
var selectRif = "";
function cerca_comune()
{
    selectParent = "comune";
	strDim = Dim_Alert(600, 300);

	var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=ricComune";
		   				
	valorediritorno = window.showModalDialog(stringa, "", strDim);
}

function cerca_sede(value)
{
    selectRif = value;
    selectParent = "sede";
	strDim = Dim_Alert(600, 300);

	var stringa = "/gitco2/parametri/modali/ricerca_sede.php?richiesta=sede&a=<?php echo $a;?>&c=<?php echo $CC_sede; ?>&tipo_sede=<?php echo $tipo_sede; ?>&tipo_sede_completo=<?php echo $tipo_sede_completo; ?>";
	if(value=="banca")
		stringa+= "&tipo_banca=sede";
	
	valorediritorno = window.showModalDialog(stringa, "", strDim);
}

function controllaCampi ()
{
	var pec = $('#pec_id').val();
	obbl_pec = obbligatorio(pec,"PEC");				if( obbl_pec!=true )		return false;
	control_pec = verifica_mail(pec,"PEC");			if( control_pec!=true )		return false;

	return true;
}

function sede_banca()
{
	if($('#tipo_banca').val()=="sede")
	{
		$('#filiale_sede').hide();
		$('#id_collegamento').val('');
		$('#banca_sede').text('');
	}
	else
		$('#filiale_sede').show();
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

<input type=hidden name=invia_submit 		value=""	id=invia_submit  	>
<input type=hidden name=sede_id  			value="<?php echo $sede_id; ?>" >
<input type=hidden name=id_collegamento id=id_collegamento  	value="<?php echo $id_collegamento; ?>" >
<input type=hidden name=tipo_sede 			value="<?php echo $tipo_sede; ?>">

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $CC; ?>">


<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_left width17"><input class="sfondo_azzurro width100" type=button id=cerca_denom name=cerca_denom value="Denominazione" onclick="cerca_sede();"></td>
		<td class="text_left" colspan=5><input class="text_left width100" id=denom_id name=denom value="<?php echo $denominazione; ?>" ></td>
		<td class="text_right" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width17">Partita Iva</td>
		<td class="text_left width19"><input class="width95 text_right" maxlength=11 id=PI_id name=PI value="<?php echo $PI; ?>" ></td>
		<td class="text_center width20" colspan=2>Codice Fiscale</td>
		<td class="text_left width24" colspan=2><input class="width100 text_left" maxlength=16 id=CF_id name=CF value="<?php echo $CF; ?>" ></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
	</tr>
	<tr>	
		<td class="tr_tipo_banca text_left" colspan=8><hr></td>		
	</tr>
	<tr class="tr_tipo_banca">
		<td class="text_left width17">Tipo</td>
		<td class="text_left width19">
			<select class="text_left width100" id=tipo_banca name=tipo_banca onchange="sede_banca();">
				<option value="filiale">Filiale</option>
				<option value="sede">Sede</option>
			</select>
		</td>
		<td class="text_center width20" colspan=2><input title="Selezionare la sede a cui collegare la filiale" class="sfondo_azzurro width60" type=button id=filiale_sede name=filiale_sede value="Associa sede" onclick="cerca_sede('banca');"></td>
		<td class="text_left width44" colspan=4><span class="width100 text_left" id=banca_sede ><?php echo $denominazione_sede; ?></span></td>
	</tr>
	<tr>	
		<td class="text_left" colspan=8><hr></td>		
	</tr>	
	<tr>
		<td class="text_left width17">Comune</td>
		<td class="text_left width19"><input class="sfondo_azzurro text_left width95" readonly tabindex=1 name=comune id=comune_id value="<?php echo $com; ?>" onclick="cerca_comune('tribunale');"></td>
		<td class="text_left width10">Provincia</td>
		<td class="text_left width14"><input class="sfondo_azzurro text_left width30" readonly tabindex=2 id=prov_id name=prov value="<?php echo $prov; ?>">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CAP</td>
		<td class="text_left width10"><input class="text_center width94" tabindex=3 id=cap_id name=cap size=4 value="<?php echo $cap; ?>"></td>
		<td class="text_center width10"></td>
		<td class="text_left width20" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width17">Indirizzo</td>
		<td class="text_left width19">
        	<input id=via class="text_left width95" name=via type=text value="<?php echo $via; ?>" tabindex=5 >
        </td>
        <td class="text_left width10">Civ. 
		<input type="text" id=civico 	   class="text_right"  name="civico"  	value="<?php echo $civ; ?>"  size=2 tabindex=6></td>
		<td class="text_center width14">Esp. 
		<input type="text" id=esponente  class="text_left"   name="esponente" value="<?php echo $esp; ?>"  size=2 tabindex=7></td>
		<td class="text_center width10">Int. 	
		<input type="text" id=interno    class="text_right"  name="interno" 	value="<?php echo $int; ?>"  size=2 tabindex=8></td>
		<td class="text_center">Dettagli</td>
		<td colspan=2><input type="text" id=dettagli   class="text_left width100"   name="dettagli" 	value="<?php echo $dett; ?>" tabindex=9></td>
	</tr>
	<tr>
		<td class="text_left width17">Telefono</td>
		<td class="text_left width19"><input class="text_right width95" id=tel_id name=tel class="width100" value="<?php echo $tel; ?>" tabindex=10></td>
		<td class="text_left width10">Fax</td>
		<td class="text_left width24" colspan=2><input class="text_right" id=fax_id name=fax size=18 value="<?php echo $fax; ?>" ondblclick="controllaCampi();" tabindex=11></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
	</tr>
	<tr>
		<td class="text_left width17">Email</td>
		<td class="text_left width19"><input class="text_left width95" id=email_id name=email value="<?php echo $email; ?>" tabindex=12></td>
		<td class="text_left width10"><span title="Inserimento PEC obbligatorio">PEC *</span></td>
		<td class="text_left width24" colspan=2><input title="Inserimento PEC obbligatorio" class="text_left" id=pec_id name=PEC size=18 value="<?php echo $PEC; ?>" tabindex=13></td>
		<td class="text_center width10">Sito</td>
		<td class="text_left width20" colspan=2><input class="text_left width100" id=sito_id name=sito value="<?php echo $sito; ?>" tabindex=14></td>
	</tr>	
	<tr>
		<td class="text_left width17">Orario</td>
		<td class="text_left" colspan=7><textarea class="text_left width100" id=orario_id name=orario rows=3><?php echo $orario; ?></textarea></td>
	</tr>
	<tr>
		<td class="text_left" colspan=8><hr></td>		
	</tr>	
</table>

</form>

<span id=avvisoBanca><font>Per effettuare il collegamento tra una filiale e la sua sede e' necessario che venga inserita prima la sede.<br>Successivamente sarà possibile associarle attraverso il tasto "Associa sede" durante l'inserimento della filiale.</font></span>

<?php echo $layout; ?>


</td>
</tr>
</table>

</body>
</html>