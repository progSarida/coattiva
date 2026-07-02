<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
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

// $Text = urldecode($_REQUEST['cluster']);
// $array_cronologici = json_decode($Text);
$array_cronologici = get_var('array_crono');
$tipo_atto = get_var('atto_val');

set_time_limit(100);

$trova_id = new atto($array_cronologici[0], $c);

$atto = array();
for($i=0;$i<count($array_cronologici);$i++)
{	
	$atto[$i] = new atto($array_cronologici[$i], $c);
}


$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$crono = $trova_id->ultimo_id(date('Y'));

$titolo_pag = "Cronologici atti";
if($tipo_atto=="Ingiunzione")
	$titolo_pag = "Cronologici ingiunzioni";
else if($tipo_atto=="avvisoIntimazione")
	$titolo_pag = "Cronologici avvisi di intimazione ad adempiere";
else if($tipo_atto=="SOLL_PRE")
    $titolo_pag = "Cronologici solleciti pre ingiunzione";
else if($tipo_atto=="AV_MORA")
    $titolo_pag = "Cronologici solleciti pre ingiunzione";


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Stampa atti</title>

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
	control = submit_buttons('Update');
	if(control)
		$('#form_cronologici').submit();
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	return true;
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

function control_crono()
{
	ultimo_crono = parseInt('<?php echo $crono; ?>');
	ultimo_proto = $("#numeroParProtocollo").val();
    data_proto = $("#dataParProtocollo").val();
    tipoProto = $("#tipoParProtocollo").val();

	for(var j=0;j<<?php echo count($array_cronologici); ?>;j++)
	{
		if($('#escludi_'+j).prop('checked')==true)
		{
			$('#proto_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
            $('#dataProto_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
			$('#crono_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
			continue;
		}
		
		$('#crono_'+j).prop('readonly', false).val(ultimo_crono).removeClass('sfondo_grigio');
		ultimo_crono++;

		if(tipoProto!="" && data_proto!="" && ultimo_proto!=""){

            $('#proto_'+j).val(ultimo_proto).prop('readonly', false).removeClass('sfondo_grigio');
            $('#dataProto_'+j).val(data_proto).prop('readonly', false).removeClass('sfondo_grigio');

            if(tipoProto=="progr")
                ultimo_proto++;
        }
        else{
            $('#proto_'+j).val("").prop('readonly', false).removeClass('sfondo_grigio');
            $('#dataProto_'+j).val("").prop('readonly', false).removeClass('sfondo_grigio');
        }
	}
}

$( function() {

    $( ".picker" ).datepicker();

} );

function checkProto(){
    tipoProto = $("#tipoParProtocollo").val();
    switch(tipoProto){

        case "progr":
            $('.rowProto').show();
            $("#dataParProtocollo").val("");
            $("#numeroParProtocollo").val("");
            $("#testoProtocollo").text("A partire dal Protocollo numero");
            break;
        case "fisso":
            $('.rowProto').show();
            $("#dataParProtocollo").val("");
            $("#numeroParProtocollo").val("");
            $("#testoProtocollo").text("Numero di Protocollo fisso");
            break;
        default:
            $("#dataParProtocollo").val("");
            $("#numeroParProtocollo").val("");
            $("#testoProtocollo").text("");
            $('.rowProto').hide();
            break;
    }
}

$(document).ready(function(){

    alert("La pagina e' stata caricata!");
    $("#tipoParProtocollo").prop("disabled",false);

	$('#form_cronologici').ajaxForm(
						
	    function(value) {
	    	window.name = "cronologici";
	        var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
			
			window.close(link, "cronologici");
		}
		else if(array_ritorno[0]=='ERROR')
		{		
			alert("Errore: "+array_ritorno[1]);
			window.close(link, "cronologici");
		}
		else if(array_ritorno[0]=='NO')
		{		
			alert("Nessun cronologico assegnato!");
			window.close(link, "cronologici");
		}
		else
		{
			alert("Errore nella procedura");
			window.close(link, "cronologici");
		}
		
	});

$("#submit_click").click( salva_form );


	});

</script>

</head>
<body class="sfondo_new_gitco" >

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td class="width1"><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td class="width1"><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>

<?php include MENU . '/menu_generale.php'; ?>

<script>
var blocca_menu = 1;
</script>

<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td class="text_center width7">
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undogrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo"></a>
        </td>
		<td class="text_center width11">
          	
        </td>
		<td class="text_center width7">
          	<a href="#" id="stampa_click" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10grey.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
        </td>
		<td class="text_center width3">
          	
        </td>
		<td class="text_center width7" >
			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td class="text_center width2"></td>
		<td class="text_center width7">
			<a onMouseover="title='Home'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/homegrey.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor"><?php echo $titolo_pag; ?></font></td>
	</tr>
</table>
<table class="table_interna text_center" border="0" cellspacing=0 >
    <tr>
        <td class="text_left width35"><span class="titolo font15">PROTOCOLLO</span></td>
        <td class="text_left width25">
            <select id="tipoParProtocollo" name="tipoParProtocollo" onchange="checkProto();control_crono();" disabled>
                <option value="">Assente</option>
                <option value="progr">Progressivo</option>
                <option value="fisso">Fisso</option>
            </select>
        </td>
        <td class="text_left width40">

        </td>
    </tr>
    <tr class="rowProto" style="display:none;">
        <td class="text_left">Data Protocollo</td>
        <td class="text_left">
            <input class="text_center picker" onchange="control_crono();" id="dataParProtocollo" name="dataParProtocollo" value="" size="6">
        </td>
        <td class="text_left"></td>
    </tr>
    <tr class="rowProto" style="display:none;">
        <td class="text_left"><span id="testoProtocollo"></span></td>
        <td class="text_left">
            <input class="text_right" onchange="control_crono();" id="numeroParProtocollo" name="numeroParProtocollo" value="" size="3">
        </td>
        <td class="text_left"></td>
    </tr>
</table>
<br>
<form id=form_cronologici name=form_cronologici action="cronologici_salva.php" method=post>
<input name=invia_submit  id=invia_submit	type=hidden	value="" >

<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
		
<?php if(count($atto)!=0)
{?>


<table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">
<tr class="text_left riga_dispari" style="height:30px;" >
	
	<td class="width1"><br></td>
	<td class="text_left width20"><b>Atto</b></td>
	<td class="width1"><br></td>
	<td class="text_center width10"><b>Totale (&euro;)</b></td>
	<td class="width1"><br></td>
	<td class="text_left width20"><b>Utente</b></td>
	<td class="width1"><br></td>
	<td class="width25 text_center"><b>Prot. / Data</b></td>
	<td class="width1"><br></td>
	<td class="width20 text_center"><b>Crono / Anno</b></td>
</tr>

<?php
$forma = new forma_giuridica();
$array_forma = $forma->array_completo();

for($i=0; $i<count($atto); $i++)
{		
	$partita = new partita($atto[$i]->Partita_ID, $c);
	$utente = $partita->Utente;
	$forma_descr = "";	
	
	if($utente->Forma_Giuridica!='')
	{
		$index_value = $utente->Forma_Giuridica;
		if(isset($array_forma[$index_value]['Sigla']))
		    $forma_descr = $array_forma[$index_value]['Sigla'];
	}

	if($atto[$i]->Atto=="Avviso di intimazione ad adempiere")
	    $nomeAtto = "Avviso";
	else
        $nomeAtto = $atto[$i]->Atto;

	$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
	
	$y = $i;
	
	if ($y++ % 2)
		{$stile_riga = 'class="riga_dispari text_left pheight30"'	;	}
	else
		{$stile_riga = 'class="riga_pari text_left pheight30"'	;	}
		
		flush(); ob_flush();
?>
		<tr <?php echo $stile_riga; ?>>
			<td class="width1"><input type=hidden id="id_<?php echo $i; ?>" name="id[]" value="<?php echo $atto[$i]->ID; ?>" ></td>
			<td class="text_left width20"><?php echo $nomeAtto; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width10"><?php echo conv_num(number_format($atto[$i]->Totale_Dovuto,2)); ?></td>
			<td class="width1"><br></td>
			<td class="text_left width20"><?php echo $nome_utente; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width25" >
				<input id="proto_<?php echo $i; ?>" name="proto[<?php echo $i; ?>]"  value="" size=3>
                /
                <input id="dataProto_<?php echo $i; ?>" name="dataProto[<?php echo $i; ?>]"  value="" size=6>
			</td>
			<td class="width1"><br></td>
			<td class="text_center width20" >
                <input class="text_right" id="crono_<?php echo $i; ?>" name="crono[<?php echo $i; ?>]"  value="<?php echo $crono; ?>" size=3>
                /
                <input class="text_right sfondo_readonly" id="anno_<?php echo $i; ?>" name="anno[<?php echo $i; ?>]" value="<?php echo date('Y'); ?>" size=3 readonly>
			</td>
		</tr>
		<tr <?php echo $stile_riga; ?>>
			<td class="width1"><br></td>
			<td class="text_left" colspan=7><font class="font14 titolo"><?php echo $atto[$i]->Info_Cartella; ?></font></td>
			<td class="width1"><br></td>
			<td class="text_center width20">
			<input type="checkbox" id="escludi_<?php echo $i; ?>" name="escludi[]" value=si onclick="control_crono();" > <font class="font14 titolo">ESCLUDI ATTO</font>
			</td>
		</tr>
	
	<?php $crono++;}?>
	</table>

<?php }?>

</form>

<br>
</td>
</tr>
</table>

</body>
</html>