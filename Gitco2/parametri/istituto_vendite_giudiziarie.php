<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');
$tipo_ufficio = "istituto";
$ufficio_giud = "Istituti Vendite Giudiziarie";


$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];



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
	if($('#ID_uff').val()==0)
		control_salva = submit_buttons('Insert');
    else
    	control_salva = submit_buttons('Update');
    
	if(control_salva)
			$("#form_istituto").submit();
}

//F4
function cancella_form() 
{
	control_salva = submit_buttons('Delete');
	if(control_salva)
			$("#form_istituto").submit();
}

//F5
function annulla() 
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "istituto_vendite_giudiziarie.php?"+stringaPHP;
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

                if(selectRif=="tribunale")
                {

                    cap = valorediritorno.cap;
                    for(var contatore=0;contatore<2;contatore++)
                    {
                        cap = cap.replace("x", "0");
                    }

                    $('#ufficio_sede').val(valorediritorno.comune);
                    $('#prov_id').val(valorediritorno.prov_sigla);
                    $('#cap_id').val(cap);
                    $('#CC_uff').val(valorediritorno.CC);

                }
                else if(selectRif=="comune")
                {
                    reload_istituto(valorediritorno.CC);
                }

            }

            break;
    }

}

var selectParent = "";
var selectRif = "";
function cerca_comune(value)
{
    selectRif = value;
    selectParent = "comune";
	strDim = Dim_Alert(600, 300);

	var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=ricComune";
		   				
	valorediritorno = window.showModalDialog(stringa, "", strDim);
}

function reload_istituto(CC_ufficio)
{

	$.post("ajax/ajax_parametri.php?c=<?php echo $c; ?>" ,
    	   	 
    	   	{ 'ajax': 'uff_giudiziario' ,
	   		  'tipo_ufficio': '<?php echo $tipo_ufficio; ?>',
   			  'CC_ufficio': CC_ufficio },
    	   	
			function (value) {

   			var array_ritorno = value.split('**');

   			$('#CC_id').val(array_ritorno[0]);
   			$('#comune_id').val(array_ritorno[1]);
   			$('#CC_uff').val(array_ritorno[3]);
   			$('#ufficio_sede').val(array_ritorno[4]);
   			$('#prov_id').val(array_ritorno[5]);
   			$('#cap_id').val(array_ritorno[6]);
   			$('#via').val(array_ritorno[7]);
   			if(array_ritorno[8]=="0")
   				array_ritorno[8]="";
   			$('#civico').val(array_ritorno[8]);
   			$('#esponente').val(array_ritorno[9]);
   			if(array_ritorno[10]=="0")
   				array_ritorno[10]="";
   			$('#interno').val(array_ritorno[10]);
   			$('#dettagli').val(array_ritorno[11]);
   			$('#tel_id').val(array_ritorno[12]);
   			$('#fax_id').val(array_ritorno[13]);
   			$('#email_id').val(array_ritorno[14]);
   			$('#pec_id').val(array_ritorno[15]);
   			$('#sito_id').val(array_ritorno[16]);
   			$('#ID_uff').val(array_ritorno[17]);
   			
   			
		});
}

</script>


<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){

	$("#submit_click").click( salva_form );
	        
    $("#delete_click").click( cancella_form );
	
	$('#form_istituto').ajaxForm(
			
	    function(value) {
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='SAVED')
		{		
			alert('Istituto vendite giudiziarie salvato correttamente!');
			$('*').removeClass("sfondo_giallo");
			modifica = 0;
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert('Salvataggio Istituto vendite giudiziarie fallito!');
		}
		if(array_ritorno[0]=='DELETE')
		{
			alert('Istituto vendite giudiziarie cancellato correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR_DELETE')
		{
			alert('Cancellazione Istituto vendite giudiziarie fallita!');
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
			<a onMouseover="title='Pagina precedente'" href="#" onclick = "pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick = "pag_suc();" style="text-decoration: none;">
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

<form name=form_istituto id=form_istituto method=post action="istituto_vendite_giudiziarie_salva.php">

<input type=hidden name=ID_uff id=ID_uff value="" >
<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c 							value=<?php echo $c; ?> >
<input type=hidden name=a 							value=<?php echo $a; ?> >
<input type=hidden name=CC			id=CC_id 		value="">
<input type=hidden name=CC_uff 		id=CC_uff 		value="">
<input type=hidden name=tipo_uff 	id=tipo_uff 	value="<?php echo $tipo_ufficio; ?>">

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor"><?php echo $ufficio_giud; ?></font></td>
	</tr>
</table>
<br>

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_left width15">Comune</td>
		<td class="text_left width31" colspan=3><input class="sfondo_azzurro text_left width100" title="Seleziona un comune" readonly name=comune id=comune_id value="" onclick="cerca_comune('comune');"></td>
		<td class="text_center" colspan=5></td>
	</tr>
	<tr><td colspan=9><hr></td></tr>
	<tr>
		<td class="text_left width15">Sede istituto</td>
		<td class="text_left width19"><input class="sfondo_azzurro text_left width100" title="Associa al comune l'Istituto vendite giudiziarie di riferimento" readonly tabindex=1 name=ufficio_sede id=ufficio_sede value="" onclick="cerca_comune('tribunale');"></td>
		<td class="text_left width2"></td>
		<td class="text_left width10">Provincia</td>
		<td class="text_left width14"><input class="sfondo_azzurro text_left width30" readonly tabindex=2 id=prov_id name=prov value="">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CAP</td>
		<td class="text_left width10"><input class="sfondo_azzurro text_center width80" readonly tabindex=3 id=cap_id name=cap size=4 value=""></td>
		<td class="text_center width10"></td>
		<td class="text_left width20" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width15">Indirizzo</td>
		<td class="text_left width19">
        	<input id=via class="text_left width100" name=via type=text value="" tabindex=5 >
        </td>
        <td class="text_left width2"></td>
        <td class="text_left width10">Civ. 
		&nbsp;<input type="text" id=civico 	   class="text_right"  name="civico"  	value=""  size=2 tabindex=6></td>
		<td class="text_center width14">Esp. 
		&nbsp;<input type="text" id=esponente  class="text_left"   name="esponente" value=""  size=2 tabindex=7></td>
		<td class="text_center width10">Int. 	
		&nbsp;<input type="text" id=interno    class="text_right"  name="interno" 	value=""  size=2 tabindex=8></td>
		<td class="text_center">Dettagli</td>
		<td colspan=2><input type="text" id=dettagli   class="text_left width100"   name="dettagli" 	value="" tabindex=9></td>
	</tr>
	<tr>
		<td class="text_left width15">Telefono</td>
		<td class="text_left width19"><input class="text_right width100" id=tel_id name=tel class="width100" value="" tabindex=10></td>
		<td class="text_left width2"></td>
		<td class="text_left width10">Fax</td>
		<td class="text_left width24" colspan=2><input class="text_right" id=fax_id name=fax size=18 value="" ondblclick="controllaCampi();" tabindex=11></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
	</tr>
	<tr>
		<td class="text_left width15">Email</td>
		<td class="text_left width19"><input class="text_left width100" id=email_id name=email value="" tabindex=12></td>
		<td class="text_left width2"></td>
		<td class="text_left width10">PEC</td>
		<td class="text_left width24" colspan=2><input class="text_left" id=pec_id name=PEC size=18 value="" tabindex=13></td>
		<td class="text_center width10">Sito</td>
		<td class="text_left width20" colspan=2><input class="text_left width100" id=sito_id name=sito value="" tabindex=14></td>
	</tr>	
	<tr><td colspan=9><hr></td></tr>
</table>

</form>

</td>
</tr>
</table>

</body>
</html>