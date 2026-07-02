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


?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Elaborazione atti</title>

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
    ritorno_mail = confirm("Si sta procedendo con la verifica delle ricevute.\n\nConfermare l'operazione?");

    if(!ritorno_mail)
        return false;
    else
		$('#form_ricevute').submit();
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	location.href="elabora_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+atto_val;
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
	pagina_menu('prev');
}

//PAG SU
function pag_suc()
{
	pagina_menu('suc');
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
//CAMBIO PAGINA
function pagina_menu (value)
{	
	return true;
}

</script>

<!-- ********** SUBMIT ********** -->
<script>

$(document).ready(function(){

    $('#form_ricevute').ajaxForm(

        function(value) {
            alert(value);
//            var array_ritorno = value.split(' ');
//            if(array_ritorno[0]=='NO')
//            {
//                alert('Codice partita non trovato!');
//                top.location.href = "ingiunzione.php?partita="+value+"&c=<?php //echo $c; ?>//&a=<?php //echo $a; ?>//";
//            }
//            else
//            {
//                top.location.href = "ingiunzione.php?partita="+value+"&c=<?php //echo $c; ?>//&a="+array_ritorno[1];
//            }
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
blocca_modifica = 1;
</script>

<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td class="text_center width7">
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<input id="submit_click" type="image" title="Elabora" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
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
          	<a href="#" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10grey.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
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
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Gestione ricevute PEC</font></td>
	</tr>
</table>

<form id="form_ricevute" name="form_ricevute" action="controlla_mail.php" method="post">
	
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">	

<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=6 class="text_center"><font class="titolo font16 under_decor">Selezione</font></td>
	</tr>
	<tr>
		<td colspan=6 ><hr></td>
	</tr>
    <tr>
        <td class="text_left" colspan=2>Tipo Entrata</td>
        <td class="text_left" colspan=4>
            <select name=tipo_partita >
                <option></option>
                <option value="CDS">CDS/AMMINISTRATIVA</option>
                <option>IMMOBILI</option>
                <option>IRPEF</option>
                <option>OSAP</option>
                <option>PATRIMONIALE</option>
                <option value="PUBBLICITA">PUBBLICITA'</option>
                <option>RIFIUTI</option>
            </select>
        </td>
    </tr>
	<tr>
		<td colspan=6 ><hr></td>
	</tr>
</table>

</form>

</td>
</tr>
</table>

</body>
</html>