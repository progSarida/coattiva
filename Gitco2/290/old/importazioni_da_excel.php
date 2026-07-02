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

$tipo_importazione = get_var('tipo_importazione');
switch($tipo_importazione)
{
    case "generale":
        $action = "importazione_generale.php";
        break;

	default:
		$action = "";
		break;
}

$comune = new ente_gestito($c);
$codice_290 = $comune->Codice_290;
$nome_comune = $comune->Nome;

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Ruolo Coattivo - Tracciato 290</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>var modifica = 0;
var operatore = "<?php echo $_SESSION['username']; ?>";

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{     
	return true;
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	location.href = "preimportazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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

</script>	

<script>
function abilitaconferma()
{
	if ($("#tastosfoglia").val() != "")
		$("#tastoconferma").attr("disabled", false);

	else if ($("#tastosfoglia").val() == "")
		$("#tastoconferma").attr("disabled", "disabled");
}


</script>

</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
	<tr>
		<td valign=top>
	
	<?php include MENU . '/menu_generale.php'; ?>      
          
<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undogrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="nuovo_F6();" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover="title='Record precedente F7'" onclick="cambia_pag('prev')">
          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
    	</td>
        <td width=7% align="center">
            <a href="#" onMouseover="title='Record successivo F8'" onclick="cambia_pag('suc')">
            <img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo">
            </a>
        </td>
        <td width=11%></td>
        <td width=7% align="center">
          	<a href="#" onMouseover="title='Stampa'" onclick="">
          	<img src="/gitco2/immagini/printF10grey.png" width=50px height=50px border="0" ></a>
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
<br>
<table class="table_interna text_center">
	<tr>
		<td align=left width=15%>
			</td>
		<td align="center">
			<font class="titolo font22 under_decor">Importazione Excel <?php echo $tipo_importazione; ?></font>
		</td>
		<td align=right width=15%>
		
		</td>
	</tr>
	<tr>
		<td colspan=3 class="text_left">
			<font class="color_red">
			IMPORTANTE<br><br>
				Prima di procedere con l'importazione del file Excel è necessario verificare bene i dati e che il file sia costruito come da modello.<br>
				L'importazione una volta ultimata è definitiva e non si può annullare.<br><br>
				L'eventuale uscita dalla pagina durante l'importazione,
				potrebbe causare errori o dati incompleti.<br>									
			</font>		
		</td>
	</tr>
	</table>
		
	<br>
		<font class="titolo font16">Carica File</font>
		<br>
		<form id=form_importazione name=form_importazione method="post" action="<?php echo $action; ?>" enctype="multipart/form-data">
			<input type="hidden" name="c" value="<?php echo $c?>">
			<input type="hidden" name="a" value="<?php echo $a?>">
			<input type="hidden" name="submit_file" value="1">

			<input type="file" accept=".xlsx,.xls" size="50" name="file_excel" id="tastosfoglia" onchange="abilitaconferma();">
			<br>
			<br>
			<input type="submit" disabled size="10" id="tastoconferma" value="Conferma">
		</form>
		
		<br>
				
		</td>
	</tr>
</table>

</body>
</html>