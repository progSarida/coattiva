<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
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

//echo WEB_ROOT."</br>";

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
    //echo $firma_resp_proc['path_firma'];
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

class Signature {
    constructor(LG, RP, UR, RR) {
        this.legaleRappresentante = LG;
        this.responsabileProcedimento = RP;
        this.ufficialeRiscossione = UR;
        this.responsabileRichieste = RR;
    }
}

var PathFile = new Signature('<?php echo $firma_funz_resp['firma']; ?>','<?php echo $firma_resp_proc['firma']; ?>','<?php echo $firma_uff_risc['firma']; ?>','<?php echo $firma_resp_rich['firma']; ?>');

$(document).ready(function(){

	dimensiona_magnify("6", "<?php echo $w_img6; ?>" , "<?php echo $h_img6; ?>" , 150, 100 );
	dimensiona_magnify("2", "<?php echo $w_img2; ?>" , "<?php echo $h_img2; ?>" , 150, 100 );
	dimensiona_magnify("3", "<?php echo $w_img3; ?>" , "<?php echo $h_img3; ?>" , 150, 100 );
	dimensiona_magnify("4", "<?php echo $w_img4; ?>" , "<?php echo $h_img4; ?>" , 150, 100 );

});

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

function fileExists(url) {
    if(url){
        var req = new XMLHttpRequest();
        req.open('HEAD', url, false);
        req.send();
        return req.status==200;
    } else {
        return false;
    }
}


function cambia_tipo(){


	if($('#scelta_firma_funzionario').val()=="firma"){
		$('#manuale_funzionario').show();
		if($("#funzionario").val()!="" && !fileExists(PathFile.legaleRappresentante)) $("#funzionario_firma").addClass("validateCustom vld_Custom_r");
		else $("#funzionario_firma").removeClass("validateCustom vld_Custom_r");
	}
	else{
		$('#manuale_funzionario').hide();
		$("#funzionario_firma").removeClass("validateCustom vld_Custom_r");
	}

	if($('#scelta_firma_responsabile').val()=="firma"){
		$('#manuale_responsabile').show();
		if($("#responsabile").val()!="" && !fileExists(PathFile.responsabileProcedimento)) $("#responsabile_firma").addClass("validateCustom vld_Custom_r");
		else $("#responsabile_firma").removeClass("validateCustom vld_Custom_r");
	}
	else{
		$('#manuale_responsabile').hide();
		$("#responsabile_firma").removeClass("validateCustom vld_Custom_r");
	}

	if($('#scelta_firma_ufficiale').val()=="firma"){
		$('#manuale_ufficiale').show();
		if($("#ufficiale").val()!="" && !fileExists(PathFile.ufficialeRiscossione)) $("#ufficiale_firma").addClass("validateCustom vld_Custom_r");
		else $("#ufficiale_firma").removeClass("validateCustom vld_Custom_r");
	}
	else{
		$('#manuale_ufficiale').hide();
		$("#ufficiale_firma").removeClass("validateCustom vld_Custom_r");
	}

	if($('#scelta_firma_responsabile_richieste').val()=="firma"){
		$('#manuale_responsabile_richieste').show();
		if($("#richieste").val()!="" && !fileExists(PathFile.responsabileRichieste)) $("#richieste_firma").addClass("validateCustom vld_Custom_r");
		else $("#richieste_firma").removeClass("validateCustom vld_Custom_r");
	}
	else{
		$('#manuale_responsabile_richieste').hide();
		$("#richieste_firma").removeClass("validateCustom vld_Custom_r");
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
	cambia_tipo();
	control = submit_buttons('Salva');
	if(control && validateForm())
	    $("#btnSub").trigger("click");
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control = submit_buttons('Delete');
	if(control)
	    $("#btnSub").trigger("click");
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
					<textarea name=testo_sostitutivo class="form-control vld_req resize" id="testoSostitutivo"><?php echo $a_para_Resp["Testo_Sostitutivo"]; ?></textarea>
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
					<input name=tel_funzionario style="width: 80%;" class="form-control resize vld_tel text_right int" id=tel_funzionario value="<?php echo $a_para_Resp["Funzionario_Telefono"]; ?>" >
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Tipo di firma</label>
			<div class="col-lg-6 col-lg-offset-1">
					<select id="scelta_firma_funzionario" class="form-control req resize" name="scelta_firma_funzionario" onchange="cambia_tipo();">
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
				<input class="resize form-control" style="width: 100%; background-color: rgb(153, 204, 255);" type="file" name="funzionario_firma" id="funzionario_firma" value="Carica immagine">
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

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Responsabile del procedimento</label>
			<div class="col-lg-6 col-lg-offset-1">
					<input class="form-control resize" name=responsabile id=responsabile value="<?php echo $a_para_Resp["Responsabile_Procedimento"]; ?>" size=30>
			</div>
		</div>
	</div>
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-2 control-label resize">Telefono</label>
			<div class="col-lg-9 col-lg-offset-1">
					<input name=tel_responsabile id=tel_responsabile style="width: 80%;" class="form-control resize text_right vld_tel int" value="<?php echo $a_para_Resp["Responsabile_Telefono"]; ?>">
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Tipo di firma</label>
			<div class="col-lg-6 col-lg-offset-1">
				<select id="scelta_firma_responsabile" name="scelta_firma_responsabile" class="form-control req resize" onchange="cambia_tipo();">
					<option value="firma">Firma digitale</option>
					<option value="testo">Testo sostitutivo</option>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="row" id="manuale_responsabile">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">FIRMA DIGITALE</label>
			<div class="col-lg-5">
				<input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255);" type="file" name="responsabile_firma" id="responsabile_firma" value="Carica immagine">
			</div>
			<div class="col-lg-4">
				<div id=mostra_immagine2 class="image-magnify2" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_resp_proc['firma']; ?>')">
					<div class="thumbnail2 text_center">
						<img id="thumbnail_image2" src="<?php echo $firma_resp_proc['firma']; ?>" border="<?php echo $border[2]; ?>">
						<div class="popup2"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Ufficiale della Riscossione</label>
			<div class="col-lg-6 col-lg-offset-1">
					<input class="form-control resize" name=ufficiale id=ufficiale value="<?php echo $a_para_Resp["Ufficiale_Riscossione"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-2 control-label resize">Telefono</label>
			<div class="col-lg-9 col-lg-offset-1">
					<input name=tel_ufficiale id=tel_ufficiale style="width: 80%;" class="form-control resize text_right vld_tel int" value="<?php echo $a_para_Resp["Ufficiale_Telefono"]; ?>">
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Tipo di firma</label>
			<div class="col-lg-6 col-lg-offset-1">
				<select id="scelta_firma_ufficiale" name="scelta_firma_ufficiale" class="form-control req resize" onchange="cambia_tipo();">
					<option value="firma">Firma digitale</option>
					<option value="testo">Testo sostitutivo</option>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="row" id="manuale_ufficiale">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">FIRMA DIGITALE</label>
			<div class="col-lg-5">
				<input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255);" type="file" name="ufficiale_firma" id="ufficiale_firma" value="Carica immagine">
			</div>
			<div class="col-lg-4">
				<div id=mostra_immagine3 class="image-magnify3" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_uff_risc['firma']; ?>')">
					<div class="thumbnail3 text_center">
						<img id="thumbnail_image3" src="<?php echo $firma_uff_risc['firma']; ?>" border="<?php echo $border[3]; ?>">
						<div class="popup3"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Responsabile delle richieste</label>
			<div class="col-lg-6 col-lg-offset-1">
					<input class="form-control resize" name=richieste id=richieste value="<?php echo $a_para_Resp["Responsabile_Richieste"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-2 control-label resize">Telefono</label>
			<div class="col-lg-9 col-lg-offset-1">
					<input name=tel_richieste id=tel_richieste style="width: 80%;" class="form-control resize text_right vld_tel int" value="<?php echo $a_para_Resp["Responsabile_Richieste_Telefono"]; ?>" size=15>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Tipo di firma</label>
			<div class="col-lg-6 col-lg-offset-1">
				<select id="scelta_firma_responsabile_richieste" name="scelta_firma_responsabile_richieste" class="form-control req resize" onchange="cambia_tipo();">
					<option value="firma">Firma digitale</option>
					<option value="testo">Testo sostitutivo</option>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="row" id="manuale_responsabile_richieste">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">FIRMA DIGITALE</label>
			<div class="col-lg-5">
				<input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255);" type="file" name="richieste_firma" id="richieste_firma" value="Carica immagine">
			</div>
			<div class="col-lg-4">
				<div id=mostra_immagine4 class="image-magnify4" title="Clicca per allargare immagine" onclick="window.open('<?php echo $firma_resp_rich['firma']; ?>')">
					<div class="thumbnail4 text_center">
						<img id="thumbnail_image4" src="<?php echo $firma_resp_rich['firma']; ?>" border="<?php echo $border[4]; ?>">
						<div class="popup4"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

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
