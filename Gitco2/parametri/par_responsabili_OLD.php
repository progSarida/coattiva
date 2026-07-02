<?php
include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_paramUtils.php");

$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');

if($tipo_riscossione=="CDS")
	$titolo_riscossione = $tipo_riscossione."/AMMINISTRATIVA";
else
	$titolo_riscossione = $tipo_riscossione;

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$a_para_Resp = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_Resp($c,$tipo_riscossione)));

$par_id = $a_para_Resp["ID"];
if($par_id==null) $par_id = 0;

$w_img6= 0;
$h_img6 = 0;
$border[1] = 0;
$w_img2 = 0;
$h_img2 = 0;
$border[2] = 0;
$w_img3 = 0;
$h_img3 = 0;
$border[3] = 0;
$w_img4 = 0;
$h_img4 = 0;
$border[4] = 0;

$firma_funz_resp = $cls_param->firmaSingola("Funzionario_Responsabile",$a_para_Resp);
$firma_resp_proc = $cls_param->firmaSingola("Responsabile_Procedimento",$a_para_Resp);
$firma_uff_risc  = $cls_param->firmaSingola("Ufficiale_Riscossione",$a_para_Resp);
$firma_resp_rich = $cls_param->firmaSingola("Responsabile_Richieste",$a_para_Resp);
$firma_legal_rap = $cls_param->firmaSingola("Legale_Rappresentante",$a_para_Resp);

if($par_id != null)
{

	if($firma_funz_resp['path_firma']!="" && $a_para_Resp["Funzionario_Testo"]!="si" && is_file($firma_funz_resp['path_firma']))
	{
		$img_funzionario = new Imagick($firma_funz_resp['path_firma']);
		$d = $img_funzionario->getImageGeometry();
		$w_img6 = $d['width'];
		$h_img6 = $d['height'];
		$border[1] = 1;
	}

	if($firma_resp_proc['path_firma']!="" && $a_para_Resp["Responsabile_Testo"]!="si" && is_file($firma_resp_proc['path_firma']))
	{
		$img_responsabile = new Imagick($firma_resp_proc['path_firma']);
		$d2 = $img_responsabile->getImageGeometry();
		$w_img2 = $d2['width'];
		$h_img2 = $d2['height'];
		$border[2] = 1;
	}

	if($firma_uff_risc['path_firma']!="" && $a_para_Resp["Ufficiale_Testo"]!="si" && is_file($firma_uff_risc['path_firma']))
	{
		$img_ufficiale = new Imagick($firma_uff_risc['path_firma']);
		$d3 = $img_ufficiale->getImageGeometry();
		$w_img3 = $d3['width'];
		$h_img3 = $d3['height'];
		$border[3] = 1;
	}

	if($firma_resp_rich['path_firma']!="" && $a_para_Resp["Responsabile_Richieste_Testo"]!="si" && is_file($firma_resp_rich['path_firma']))
	{
		$img_resp = new Imagick($firma_resp_rich['path_firma']);
		$d4 = $img_resp->getImageGeometry();
		$w_img4 = $d4['width'];
		$h_img4 = $d4['height'];
		$border[4] = 1;
	}
}

?>

<script>

$(document).ready(function(){

	dimensiona_magnify("6", "<?php echo $w_img6; ?>" , "<?php echo $h_img6; ?>" , 150, 100 );
	dimensiona_magnify("2", "<?php echo $w_img2; ?>" , "<?php echo $h_img2; ?>" , 150, 100 );
	dimensiona_magnify("3", "<?php echo $w_img3; ?>" , "<?php echo $h_img3; ?>" , 150, 100 );
	dimensiona_magnify("4", "<?php echo $w_img4; ?>" , "<?php echo $h_img4; ?>" , 150, 100 );

	$("#submit_click").click( salva_form );


    $("#delete_click").click( cancella_form );

	$('#form_par_responsabili').ajaxForm(

	    function(value) {
	        var array_ritorno = value.split(' ');

		if(array_ritorno[0]=='SAVED')
		{
			alert('Parametri salvati correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert('Salvataggio parametri fallito! '+value);
		}
		else if(array_ritorno[0]=='DELETED')
		{
			alert('Parametri cancellati correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR_DELETE')
		{
			alert('Cancellazione parametri fallita! '+array_ritorno[1]);
		}
		else
		{
			alert(value);
		}
	    });

});

function cambia_tipo(){
	if($('#scelta_firma_funzionario').val()=="firma"){
		$('#manuale_funzionario').show();

	}
	else{
		$('#manuale_funzionario').hide();

	}

	if($('#scelta_firma_responsabile').val()=="firma"){
		$('#manuale_responsabile').show();

	}
	else{
		$('#manuale_responsabile').hide();

	}

	if($('#scelta_firma_ufficiale').val()=="firma"){
		$('#manuale_ufficiale').show();

	}
	else{
		$('#manuale_ufficiale').hide();

	}

	if($('#scelta_firma_responsabile_richieste').val()=="firma"){
		$('#manuale_responsabile_richieste').show();

	}
	else{
		$('#manuale_responsabile_richieste').hide();

	}
}

function ConfermaDeleteFirme(){
	ritorno = confirm("Si stanno eliminando i dati dal database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");

	var querystring= "";
	if($('#CheckRic').is(":checked")) querystring += "&ResponsRic = si";
	if($('#CheckUffic').is(":checked")) querystring += "&Ufficiale = si";
	if($('#CheckRespons').is(":checked")) querystring += "&Responsabile = si";
	if($('#CheckFunz').is(":checked")) querystring += "&Funzionario = si";

	if(ritorno){
		location.href = "par_responsabili_salva.php?EliminaFirme = true"+querystring+"&a=<?php echo $a; ?>&tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>";
	}
}
</script>

<?php include(INC."/menu.php"); ?>



<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	control = submit_buttons('Salva');
	if(control)
	    $("#form_par_responsabili").submit();
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control = submit_buttons('Delete');
	if(control)
	    $("#form_par_responsabili").submit();
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="par_responsabili.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_pagamento.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href =  "par_generali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'

</script>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Parametri responsabili (<?php echo $titolo_riscossione; ?>)</p>
	</div>
</div>

<form class="form-horizontal validate" name=form_par_responsabili id=form_par_responsabili method=post action="par_responsabili_salva.php" enctype="multipart/form-data">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> />
<input type=hidden name=a value=<?php echo $a; ?> />
<input type=hidden name=tipo_riscossione value=<?php echo $tipo_riscossione; ?> />

<input type=hidden name=funzionario_firma value=<?php echo $a_para_Resp["Funzionario_Firma"]; ?> />
<input type=hidden name=responsabile_firma value=<?php echo $a_para_Resp["Responsabile_Firma"]; ?> />
<input type=hidden name=ufficiale_firma value=<?php echo $a_para_Resp["Ufficiale_Firma"]; ?> />
<input type=hidden name=richieste_firma value=<?php echo $a_para_Resp["Responsabile_Richieste_Firma"]; ?> />

<input type=hidden name=legale_rappresentante value=<?php echo $a_para_Resp["Legale_Rappresentante"]; ?> />
<input type=hidden name=legale_rappresentante_telefono value=<?php echo $a_para_Resp["Legale_Rappresentante_Telefono"]; ?> />
<input type=hidden name=legale_rappresentante_firma value=<?php echo $a_para_Resp["Legale_Rappresentante_Firma"]; ?> />
<input type=hidden name=legale_rappresentante_testo value=<?php echo $a_para_Resp["Legale_Rappresentante_Testo"]; ?> />

<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>" />

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p><b>Responsabili</b></p>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">TESTO SOSTITUTIVO</label>
			<div class="col-lg-9" id="TestoSostitutivoContainer">
					<textarea name=testo_sostitutivo class="form-control resize" id="testoSostitutivo"><?php echo $a_para_Resp["Testo_Sostitutivo"]; ?></textarea>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;"><span id="testo_funz_legale">Funzionario responsabile</span></label>
			<div class="col-lg-6 col-lg-offset-1">
					<input class="form-control resize" name=funzionario id=funzionario value="<?php echo $a_para_Resp["Funzionario_Responsabile"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-2 control-label resize">Telefono</label>
			<div class="col-lg-9 col-lg-offset-1">
					<input name=tel_funzionario style="width: 80%;" class="form-control resize text_right int" id=tel_funzionario value="<?php echo $a_para_Resp["Funzionario_Telefono"]; ?>" size=15>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Tipo di firma</label>
			<div class="col-lg-6 col-lg-offset-1">
					<select id="scelta_firma_funzionario" class="form-control resize" name="scelta_firma_funzionario" onchange="cambia_tipo();">
						<option value="firma">Firma digitale</option>
						<option value="testo">Testo sostitutivo</option>
					</select>
			</div>
		</div>
	</div>
</div>
<div class="row" id="manuale_funzionario">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">FIRMA DIGITALE</label>
			<div class="col-lg-5">
				<input class="button_azzurro resize" style="width: 100%;" type="file" name="funzionario_firma" value="Carica immagine">
			</div>
			<div class="col-lg-4">
				<div id=mostra_immagine6 class="image-magnify6" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_funz_resp['firma']; ?>')">
					<div class="thumbnail6 text_center">
						<img id="thumbnail_image6" src="<?php echo $firma_funz_resp['firma']; ?>" border="<?php echo $border[1]; ?>">
						<div class="popup6"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<colgroup>
		<col class="width20">
    <col class="width50">
		<col class="width7">
    <col class="width23">
	</colgroup>
	<!--<tr>
		<td class="text_center" colspan=4><b>Responsabili</b></td>
	</tr>
	<tr>
		<td class="text_center" colspan=4><hr></td>
	</tr>
    <tr style="border-top: 2px solid #B0BBE8; border-bottom: 2px solid #A7B6D6;">
        <td class="text_left"><span>TESTO SOSTITUTIVO</span></td>
        <td class="text_left" colspan="3" >
					<textarea name=testo_sostitutivo rows="2" cols="60" id="testoSostitutivo" style="margin-top: 20px; margin-bottom: 20px;"><?php echo $a_para_Resp["Testo_Sostitutivo"]; ?></textarea>
				</td>
		</tr>
    <tr>
        <td class="text_center" colspan=4><hr></td>
    </tr>
	<tr >
		<td class="text_left"><span id="testo_funz_legale">Funzionario responsabile</span></td>
		<td class="text_left"><input class="width54" name=funzionario id=funzionario value="<?php echo $a_para_Resp["Funzionario_Responsabile"]; ?>" size=30></td>
		<td class="text_right">Telefono</td>
		<td class="text_center"><input name=tel_funzionario id=tel_funzionario value="<?php echo $a_para_Resp["Funzionario_Telefono"]; ?>" size=15></td>
	</tr>
	<tr >
		<td class="text_left">
			Tipo di firma
		</td>
		<td class="text_left"><select id="scelta_firma_funzionario" name="scelta_firma_funzionario" class="width54" onchange="cambia_tipo();">
				<option value="firma">Firma digitale</option>
				<option value="testo">Testo sostitutivo</option>
			</select>
		</td>
		<td class="text_left" colspan=2>
		</td>
	</tr>
	<tr id="manuale_funzionario" >
		<td class="text_left">
			FIRMA DIGITALE
		</td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="funzionario_firma" value="Carica immagine"></td>
		<td></td>
		<td class="text_center">
			<div id=mostra_immagine6 class="image-magnify6" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_funz_resp['firma']; ?>')">
				<div class="thumbnail6 text_center">
					<img id="thumbnail_image6" src="<?php echo $firma_funz_resp['firma']; ?>" border="<?php echo $border[1]; ?>">
					<div class="popup6"></div>
				</div>
			</div>
		</td>
	</tr>-->
	<tr style="border-bottom: 2px solid #B0BBE8;">
		<td class="text_center" colspan=4><hr></td>
	</tr>
	<tr>
		<td class="text_center" colspan=4><hr></td>
	</tr>
	<tr>
		<td class="text_left">Responsabile del procedimento</td>
		<td class="text_left"><input class="width54" name=responsabile id=responsabile value="<?php echo $a_para_Resp["Responsabile_Procedimento"]; ?>" size=30></td>
		<td class="text_right">Telefono</td>
		<td class="text_center"><input name=tel_responsabile id=tel_responsabile value="<?php echo $a_para_Resp["Responsabile_Telefono"]; ?>" size=15></td>
	</tr>
	<tr >
		<td class="text_left">
			Tipo di firma
		</td>
		<td class="text_left"><select id="scelta_firma_responsabile" name="scelta_firma_responsabile" class="width54" onchange="cambia_tipo();">
				<option value="firma">Firma digitale</option>
				<option value="testo">Testo sostitutivo</option>
			</select>
		</td>
		<td class="text_left" colspan=2>
		</td>
	</tr>
	<tr id="manuale_responsabile">
		<td class="text_left">FIRMA DIGITALE</td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="responsabile_firma" value="Carica immagine"></td>
		<td></td>
		<td class="text_center">
			<div id=mostra_immagine2 class="image-magnify2" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_resp_proc['firma']; ?>')">
				<div class="thumbnail2 text_center">
					<img id="thumbnail_image2" src="<?php echo $firma_resp_proc['firma']; ?>" border="<?php echo $border[2]; ?>">
					<div class="popup2"></div>
				</div>
			</div>
		</td>
	</tr>
	<tr style="border-bottom: 2px solid #B0BBE8;">
		<td class="text_center" colspan=4><hr></td>
	</tr>
	<tr>
		<td class="text_center" colspan=4><hr></td>
	</tr>
	<tr>
		<td class="text_left">Ufficiale della Riscossione</td>
		<td class="text_left"><input class="width54" name=ufficiale id=ufficiale value="<?php echo $a_para_Resp["Ufficiale_Riscossione"]; ?>" size=30></td>
		<td class="text_right">Telefono</td>
		<td class="text_center"><input name=tel_ufficiale id=tel_ufficiale value="<?php echo $a_para_Resp["Ufficiale_Telefono"]; ?>" size=15></td>
	</tr>
	<tr>
		<td class="text_left">
			Tipo di firma
		</td>
		<td class="text_left"><select id="scelta_firma_ufficiale" name="scelta_firma_ufficiale" class="width54" onchange="cambia_tipo();">
				<option value="firma">Firma digitale</option>
				<option value="testo">Testo sostitutivo</option>
			</select>
		</td>
		<td class="text_left" colspan=2>
		</td>
	</tr>
	<tr id="manuale_ufficiale">
		<td class="text_left">FIRMA DIGITALE</td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="ufficiale_firma" value="Carica immagine"></td>
		<td></td>
		<td class="text_center">
			<div id=mostra_immagine3 class="image-magnify3" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_uff_risc['firma']; ?>')">
				<div class="thumbnail3 text_center">
					<img id="thumbnail_image3" src="<?php echo $firma_uff_risc['firma']; ?>" border="<?php echo $border[3]; ?>">
					<div class="popup3"></div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="text_center" colspan=4 style="border-bottom: 2px solid #B0BBE8;"><hr></td>
	</tr>
	<tr>
		<td class="text_center" colspan=4><hr></td>
	</tr>
	<tr>
		<td class="text_left">Responsabile delle richieste</td>
		<td class="text_left"><input class="width54" name=richieste id=richieste value="<?php echo $a_para_Resp["Responsabile_Richieste"]; ?>" size=30></td>
		<td class="text_right">Telefono</td>
		<td class="text_center"><input name=tel_richieste id=tel_richieste value="<?php echo $a_para_Resp["Responsabile_Richieste_Telefono"]; ?>" size=15></td>
	</tr>
	<tr>
		<td class="text_left">
			Tipo di firma
		</td>
		<td class="text_left"><select id="scelta_firma_responsabile_richieste" name="scelta_firma_responsabile_richieste" class="width54" onchange="cambia_tipo();">
				<option value="firma">Firma digitale</option>
				<option value="testo">Testo sostitutivo</option>
			</select>
		</td>
		<td class="text_left" colspan=2>
		</td>
	</tr>
	<tr id="manuale_responsabile_richieste">
		<td class="text_left">FIRMA DIGITALE</td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="richieste_firma" value="Carica immagine"></td>
		<td></td>
		<td class="text_center">
			<div id=mostra_immagine4 class="image-magnify4" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_resp_rich['firma']; ?>')">
				<div class="thumbnail4 text_center">
					<img id="thumbnail_image4" src="<?php echo $firma_resp_rich['firma']; ?>" border="<?php echo $border[4]; ?>">
					<div class="popup4"></div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="text_center" colspan=4><hr></td>
	</tr>
</table>

<br>

</form>

<script type="text/javascript">

	$( window ).load(function() {
		if("<?php echo $a_enteAdmin["Gestore_Tipo"]; ?>" == "Concessionario")
			$('#testo_funz_legale').text('Legale rappresentante');

		if("<?php echo $a_para_Resp["Funzionario_Testo"]; ?>"!="si") $('#scelta_firma_funzionario').val('firma');
		else $('#scelta_firma_funzionario').val('testo');

		if("<?php echo $a_para_Resp["Responsabile_Testo"]; ?>"!="si") $('#scelta_firma_responsabile').val('firma');
		else $('#scelta_firma_responsabile').val('testo');

		if("<?php echo $a_para_Resp["Ufficiale_Testo"]; ?>"!="si") $('#scelta_firma_ufficiale').val('firma');
		else $('#scelta_firma_ufficiale').val('testo');

		if("<?php echo $a_para_Resp["Responsabile_Richieste_Testo"]; ?>"!="si") $('#scelta_firma_responsabile_richieste').val('firma');
		else $('#scelta_firma_responsabile_richieste').val('testo');

		$("#testoSostitutivo").css("max-width",$("#TestoSostitutivoContainer").css("width"));

		cambia_tipo();
	});
</script>

<?php include(INC."/footer.php"); ?>
