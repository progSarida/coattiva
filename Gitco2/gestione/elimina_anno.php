<?php

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";*/
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(INC."/menu.php");
/*include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_anagrafeUtils.php");

$cls_date = new cls_DateTimeI("IT",false);
$cls_anagr = new cls_anagr();

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');*/
$servizio = $cls_help->getVar('servizio');
if($servizio==null)
	$servizio_gestito = "Gestione_Coattiva";
else if($servizio=="TARGHEESTERE")
	$servizio_gestito = "Gestione_Targhe_Estere";
else if($servizio=="PUBBLICITA")
	$servizio_gestito = "Gestione_Pubblicita";

//$comune = new ente_gestito($c);
$nome_com = $a_enteAdmin["Denominazione"];//$comune->Nome;

//$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
//$nome_user = "Operatore: ".$_SESSION['username'];
$query = "SELECT Anno FROM anni_gestiti WHERE CC_Anno = '".$c."' and $servizio_gestito = 'Y' ORDER BY Anno DESC";
$anni_gestiti = $cls_db->getResults($cls_db->ExecuteQuery($query));//select_mysql_array("Anno" , "anni_gestiti" , "CC_Anno = '".$c."' and $servizio_gestito = 'Y'", "Anno", "DESC");
$year_now = substr(date('Y-m-d'),0,4);


?>

<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Gestione Anno</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>-->


<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F4
switchMenuImg("F4");
F4_button = function()
{
	control=submit_buttons('Delete');
		if(control)
			$("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="elimina_anno.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){

	link = "elimina_comune.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
	top.location.href = link;
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){

	link = "crea_comune.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
	top.location.href = link;
}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/EliminaAnno.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Elimina anno</b>");
    $("#helpModal").modal('show');

}

//F11-F12 sono nel menu'

</script>

<!--<body class="sfondo_new_gitco" >

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
<td valign=top>-->

<?php
/*switch ($servizio)
{
	case "COATTIVA":
		include MENU . '/menu_generale.php';
		break;
	case "TARGHEESTERE":
		include TARGHEESTERE . '/menu/menu_targheestere.php';
		break;
	case "PUBBLICITA":
		include PUBBLICITA . '/menu/menu_pubblicita.php';
		break;
	default:
		include MENU . '/menu_generale.php';
		break;
}*/
?>

<!--<table align=center class=table_interna border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="" >
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
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
</table>-->

<div class="row justify-content-md-center " style="margin-top: 1%; margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Cancellazione anno</span>
	</div>
</div>

<form name=form_crea class="form-horizontal validate" id=form_crea method=post action="crea_anno_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC_ente id=CC_ente value="<?php echo $c; ?>" >
<input type=hidden name=servizio id=servizio value="<?php echo $servizio; ?>" >

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Ente</label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text name=ente readonly id=ente size=25 value="<?php echo $nome_com; ?>" tabindex=1>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Selezione anno</label>
			<div class="col-lg-8">
				<select class="form-control resize" name=anno id=anno tabindex=2>
					<?php
					echo "<option selected>" . $anni_gestiti[0]['Anno'] . "</option>";
					for($i=1;$i<count($anni_gestiti);$i++)
					{
						echo "<option>" .  $anni_gestiti[$i]['Anno'] . "</option>";
					}

					?>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>


<?php include(INC."/footer.php"); ?>
