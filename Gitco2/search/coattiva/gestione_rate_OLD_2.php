<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_GestionePartita.php";
include_once CLS . "/cls_Utils.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_partita = new cls_GP();
$cls_Utils = new cls_Utils();

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

/*include CLASSI . "/ruolo.php";
include CLASSI . "/anagrafe.php";
include CLASSI . "/parametri.php";*/


$p = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$layout = "";

$partita_ID = $cls_help->getVar('partita');
$atto_ID = $cls_help->getVar('atto');

$query = "SELECT * FROM atto WHERE ID = ".$atto_ID." AND CC = '".$c."'";
$atto = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

//$atto = new atto($atto_ID, $c);

$totale_dovuto = $atto["Totale_Dovuto"] - $cls_partita->pagamenti_precedenti($atto["ID"], $atto["Partita_ID"]);//  $atto->pagamenti_precedenti();
if($atto["Tipo_Totale_Rate"]==1)
    $totale_dovuto+= $atto["Diritto_Riscossione_Minimo"];
else
    $totale_dovuto+= $atto["Diritto_Riscossione_Massimo"];


$num_rate = $atto["Rate_Previste"];
$importo = explode("*",$atto['Importi_Rate']);//$atto["Importi_Rate"];
$scadenza = explode("*",$atto['Scadenze_Rate']);

$nome_gestore = $atto["Nominativo_Gestore_Rateizzazione"];
$posizione_gestore = $atto["Posizione_Gestore_Rateizzazione"];
$esito = $atto["Esito_Richiesta_Rateizzazione"];
$operatore = $atto["Operatore_Rateizzazione"];

$path = $cls_Utils->crea_dir( ATTI ."/". $c . "/Documenti" );
$id_richiesta = $atto["ID_Richiesta_Rateizzazione"];
if($id_richiesta!=0)
{
	//$richiesta_doc = new documento($id_richiesta, $c);
	$query = "SELECT * FROM documento WHERE ID = '".$id_richiesta."' AND CC = '".$c."'";
	$richiesta_doc = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	$file_richiesta = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($path."/".$richiesta_doc["File"]);
}
else
	$file_richiesta = "";

$id_esito = $atto["ID_Esito_Rateizzazione"];

if($id_esito!=0)
{
	//$esito_doc = new documento($id_esito, $c);
	$query = "SELECT * FROM documento WHERE ID = '".$id_esito."' AND CC = '".$c."'";
	$esito_doc = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	$file_esito = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($path."/".$esito_doc["File"]);
    //echo "<h1>".$file_esito."</h1>";
}
else
	$file_esito = "";

$id_bollettini = $atto["ID_Bollettini_Rateizzazione"];
if($id_bollettini!=0)
{
	//$bollettini_doc = new documento($id_bollettini, $c);
	$query = "SELECT * FROM documento WHERE ID = '".$id_bollettini."' AND CC = '".$c."'";
	$bollettini_doc = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	$file_bollettini = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($path."/".$bollettini_doc["File"]);
}
else
	$file_bollettini = "";

if($esito == "respinta")
{
	$checked_A = "";
	$checked_R = "checked";
	$motivazione = $atto["Motivazione_Respinta_Rateizzazione"];
}
else
{
	$checked_A = "checked";
	$checked_R = "";
	$motivazione = "";
	$layout.= "<script>$('#richiesta_respinta').prop('disabled',true).val('').addClass('sfondo_grigio');</script>";
}


$tot_rate = 0.00;

for($i=0;$i<count($importo);$i++)
{
	$tot_rate += str_replace( "," , "." , $importo[$i] );
}

$tot_rate = number_format($tot_rate,2,",","");
$totale_dovuto = number_format($totale_dovuto,2,",","");

if( $tot_rate == $totale_dovuto )
	$messaggio_1 = "<b>L'importo totale delle rate coincide con il totale dovuto dell'atto.</b>";
else if($tot_rate > $totale_dovuto)
	$messaggio_1 = "<b>L'importo totale delle rate supera il totale dovuto dell'atto.<br>Informazioni sulla notifica incomplete al momento della rateizzazione.<br>Probabile maggiorazione dell'importo con la MAX spesa di notifica.</b>";
else if($tot_rate < $totale_dovuto)
	$messaggio_1 = "<b>L'importo totale delle rate e' minore del totale dovuto dell'atto.</b>";



?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Rate - Gestione</title>

	<link rel=StyleSheet href="<?= CSS; ?>/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="<?= CSS; ?>/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>

	<script type="text/javascript" language="javascript" src="<?= JS; ?>/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="<?= JS; ?>/form_jquery.js" ></script>
	<script type="text/javascript" language="javascript" src="<?= JS; ?>/jquery.bpopup.min.js" ></script>
	<script type="text/javascript" language="javascript" src="<?= JS; ?>/funzioni.js" ></script>

  	<script type="text/javascript" language="javascript" src="<?= JS; ?>/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="<?= JS; ?>/datepicker.js" ></script>


<script>
  	var modifica = 0;
	var blocca_modifica = 0;
</script>

<script>

	$("*").on( "change" , "input, textarea, select" , function( event ) {

		var elem = $( this );

        campo_name = elem.attr('name');

        if(campo_name!="ordinamento" && blocca_modifica == 0 && modifica!=1) {
            modifica=1;
        }

        if(elem.hasClass( "corrige_numero" )) {
            id_campo = elem.attr('id');
            valore = control_numero(id_campo);
            if(valore===false)
            {
                alert("Inserire un valore numerico.");
                elem.val('');
            }
            else
                elem.val(valore);
        }

        if(blocca_modifica == 0)
            elem.addClass( "sfondo_giallo", ":change" );

	});

    $("*").on( "focus blur","input, textarea",
            function( event ) {   $( this ).toggleClass( "focused", $( this ).is( ":focus" ) );  }
        );
	$("*").on( "focus","input, textarea",
            function( event ) {   $( this ).select(); }
        );

</script>


<?php // include MENU . "/gestione_pagine.php";?>

<script>

var tot_rate = "<?php echo $num_rate; ?>";
var file_richiesta = "<?php echo $file_richiesta; ?>";
var file_esito = "<?php echo $file_esito; ?>";
var file_bollettini = "<?php echo $file_bollettini; ?>";

$(function() {

for(var i=0;i<tot_rate;i++)
{

	$( "input#scadenza"+i ).datepicker();

}

});

$(document).ready(function(){

	$('#form_rate').ajaxForm(

	    function(value) {

	        var array_ritorno = value.split(' ');

		if(array_ritorno[0]=='OK')
		{
			alert('Salvataggio effettuato correttamente!');
			link = "gestione_rate.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&atto=<?php echo $atto_ID; ?>&partita=<?php echo $partita_ID; ?>";
			window.name = "Rate";
			window.open(link,"Rate");
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert("Errore nel salvataggio delle rate. "+value);
		}
		else
		{
			alert("Errore nella procedura "+value);
		}

	});


$("#submit_click").click(function salva_form() {

	if($('#tot_rate').text() == $('#tot_dov').text())
	    $("#form_rate").submit();
	else
		alert("L'importo totale delle rate non coincide con l'importo dovuto!");

	});

	});

function calc_rate ()
{
	importo_rata = 0.00;
	for(var i=0;i<tot_rate;i++)
	{
		importo_rata += parseFloat($( "input#importo"+i ).val().replace(",","."));
	}

	importo_rata = number_format(importo_rata,2,",","");

	$('#tot_rate').text(importo_rata);
}

function stampa_bollettini(value)
{
	tipo_stampa = $('#tipo_stampa').val();

	if(file_bollettini!="")
	{
		alert('File definitivo presente in archivio.');


		window.open(file_bollettini,"Stampa_Bollettini");
        self.close();

		return;
	}

	nominativo = $('#nome_gestore').val();
	posizione = $('#posizione_gestore').val();

	if(nominativo == "" || posizione == "")
	{
		alert("Campi nominativo o posizione mancanti! Completare i dati per stampare l'esito");
		return;
	}

	link = "<?= WEB_ROOT; ?>/search/coattiva/stampa_bollettini.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Atto="+value+"&tipo_stampa="+tipo_stampa;


	window.open(link,"Stampa_Bollettino");
    self.close();

}

function stampa_richiesta(value)
{
	tipo_stampa = $('#tipo_stampa').val();

	if(file_richiesta!="")
	{
		alert('File definitivo presente in archivio.');


		window.open(file_richiesta,"Stampa_Richiesta");
        self.close();

		return;
	}

	link = "<?= WEB_ROOT; ?>/search/coattiva/richiesta_rateizzazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Atto="+value+"&tipo_stampa="+tipo_stampa;


	window.open(link,"Stampa_Richiesta");
    self.close();

}

function stampa_esito(value)
{
	tipo_stampa = $('#tipo_stampa').val();

	if(file_esito!="")
	{
		alert('File definitivo presente in archivio.');


		window.open(file_esito,"Stampa_Esito");
        self.close();

		return;
	}

	nominativo = $('#nome_gestore').val();
	posizione = $('#posizione_gestore').val();

	if(nominativo == "" || posizione == "")
	{
		alert("Dati gestore mancanti! Completare i dati per stampare l'esito");
		return;
	}

	link = "<?= WEB_ROOT; ?>/search/coattiva/esito_rateizzazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Atto="+value+"&tipo_stampa="+tipo_stampa;


	window.open(link,"Stampa_Esito");
    self.close();
}

function control_richiesta()
{
	value = $('[name=esito_richiesta]:checked').val();
	if(value=="respinta")
	{
		$('#richiesta_respinta').prop('disabled',false).focus().removeClass('sfondo_grigio');;
	}
	else
	{
		$('#richiesta_respinta').prop('disabled',true).val('').focus().addClass('sfondo_grigio');;
	}
}

function cambio_data(value)
{
	data_current = $('#scadenza'+value).val();
	valore = parseInt(value);

	for(var i=valore+1;i<tot_rate;i++)
	{
		data_temp = creaData(data_current);
		data_new = aggiungiGiorni(data_temp, 30);
		stringa_data = stringaData(data_new);
		$('#scadenza'+i).val(stringa_data);
		data_current = stringa_data;
	}
}

</script>

<body class="sfondo_new_gitco" >

<table class="table_modale text_center pwidth800 height93" border=0>
	<tr>
		<td valign=top>

  <br>

<table class="text_center pwidth750" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Gestione Rate</font></td>
	</tr>
</table>

<br>

<form id=form_rate name=form_rate action="<?= WEB_ROOT; ?>/coattiva/ingiunzione_salva.php" method=post>
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
<input type=hidden name=partita value=<?php echo $partita_ID; ?> >
<input name=invia_submit  id=invia_submit	type=hidden	value="rate">
<input type=hidden name=atto value=<?php echo $atto_ID; ?> >
<input type=hidden name=num_rate value=<?php echo $num_rate; ?> >

<table class="text_center pwidth750" border="0">
<?php

for($i=0;$i<count($importo);$i++)
{

?>


	<tr>
		<td class="text_left width3"><font class="titolo"><?php echo ($i+1); ?>.</font></td>
		<td class="text_left width15">Importo</td>
		<td class="text_right width12"><input class="text_right corrige_numero" type=text name=importo[] id="importo<?php echo $i; ?>" value=<?php echo $importo[$i]; ?> size=5 onchange="calc_rate();"> &euro;</td>
		<td class="text_left width15"></td>
		<td class="text_left width20" colspan=2>Data Scadenza</td>
		<td class="text_left width15"><input class="text_center" type=text name=scadenza[] id="scadenza<?php echo $i; ?>" value=<?php echo $scadenza[$i]; ?> size=9 onchange="cambio_data('<?php echo $i; ?>');"></td>
		<td class="text_left width20"></td>
	</tr>


<?php

}

?>

	<tr>
		<td class="text_left" colspan=8><hr></td>
	</tr>
	<tr class="pheight30">
		<td class="text_left" colspan=2><b>Somma rate</b></td>
		<td class="text_right"><span id=tot_rate class="font_bold"><?php echo $tot_rate; ?></span> <font class="font_bold">&euro;</font></td>
		<td class="text_center" colspan=5 rowspan=2><?php echo $messaggio_1; ?></td>
	</tr>
	<tr class="pheight30">
		<td class="text_left" colspan=2><b>Tot. rateizzazione</b></td>
		<td class="text_right"><span id=tot_dov class="font_bold"><?php echo $tot_rate; ?></span> <font class="font_bold">&euro;</font></td>
		<td class="text_center"></td>
	</tr>
	<tr>
		<td class="text_left" colspan=8><hr></td>
	</tr>
</table>

<table class="text_center pwidth750" border="0">
	<tr class="pheight30">
		<td class="text_center width25"><font class="color_titolo font_bold">Selezione di stampa</font></td>
		<td class="text_center width25" colspan=2><font class="color_titolo font_bold">Richiesta rateizzazione</font></td>
		<td class="text_center width25" colspan=2><font class="color_titolo font_bold">Esito rateizzazione</font></td>
		<td class="text_center width25"><font class="color_titolo font_bold">Stampa Bollettini</font></td>
	</tr>
	<tr class="pheight30">
		<td class="text_center">
		<select class="width90" name=tipo_stampa id=tipo_stampa>
				<option value="provvisoria">Stampa provvisoria</option>
				<option value="definitiva">Stampa definitiva</option>
			</select>
		</td>
		<td class="text_center" colspan=2>
			<a id=pdf href="#" style="text-decoration:none;">
				<img id=bollettini src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none"
				width="25" height="25" onclick="stampa_richiesta('<?php echo $atto_ID; ?>');" title="Stampa richiesta rateizzazione">
			</a>
		</td>
		<td class="text_center" colspan=2>
			<a id=pdf href="#" style="text-decoration:none;">
				<img id=bollettini src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none"
				width="25" height="25" onclick="stampa_esito('<?php echo $atto_ID; ?>');" title="Stampa esito rateizzazione">
			</a>
		</td>
		<td class="text_center">
			<a id=pdf href="#" style="text-decoration:none;">
				<img id=bollettini src="<?= IMMAGINIWEB; ?>/img_bollettino.png" style="text-decoration:none; border:none"
				width="33" height="20" onclick="stampa_bollettini('<?php echo $atto_ID; ?>');" title="Stampa bollettini">
			</a>
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=6><hr></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Incaricato rateizzazione</font></td>
		<td class="text_left width13">Nominativo</td>
		<td class="text_left width26" colspan=2><input name=nome_gestore id=nome_gestore value="<?php echo $nome_gestore; ?>" class="width90"></td>
		<td class="text_left width11">Posizione</td>
		<td class="text_left width25"><input name=posizione_gestore id=posizione_gestore value="<?php echo $posizione_gestore; ?>" class="width99"></td>
	</tr>
	<tr>
		<td class="text_left"><font class="color_titolo font_bold">Esito richiesta</font></td>
		<td class="text_left"><input type="radio" name=esito_richiesta value="accolta" <?php echo $checked_A; ?> onclick="control_richiesta();"> Accolta</td>
		<td class="text_left" colspan=2><input type="radio" name=esito_richiesta value="respinta" <?php echo $checked_R; ?> onclick="control_richiesta();"> Respinta</td>
		<td class="text_left" colspan=2></td>
	</tr>
	<tr class="respinta">
		<td class="text_left"><font class="color_titolo font_bold">Motivazione</font></td>
		<td class="text_left" colspan=5><input name=richiesta_respinta id=richiesta_respinta value="<?php echo $motivazione; ?>" class="width99"></td>
	</tr>
	<tr>
		<td class="text_left width64" colspan=4><font class="color_titolo font_bold">Soggetto che ha evaso la pratica di rateizzazione</font></td>
		<td class="text_left width36" colspan=2><input name=operatore id=operatore value="<?php echo $operatore; ?>" class="width99"></td>
	</tr>
	<tr>
		<td colspan=6><hr></td>
	</tr>
	<tr>
		<td colspan=6><br></td>
	</tr>
	<tr>
		<td colspan=6>
			<input type=button id=submit_click name=salva value=Salva class=button_red>
			<input type=button name=chiudi value=Chiudi class=button_azzurro onclick="self.close();">
		</td>
	</tr>
</table>
</form>

		</td>
	</tr>
</table>

<?php echo $layout; ?>

</body>
</html>
