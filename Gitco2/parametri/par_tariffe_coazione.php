<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC. "/header.php");
include(INC. "/menu.php");
include_once(CLS."/cls_paramUtils.php");

$cls_param = new cls_param();

$id_tariffa = $cls_help->getVar('id_tariffa');
include(CONTROLLERS. "/TariffeCoazione.php");

$TariffeCoazione = new TariffeCoazioneController($c, $id_tariffa);
$a_tariffa = $TariffeCoazione->getTariff();
$a_pignoLocked = $TariffeCoazione->checkTariffInPignoramento();

if($id_tariffa==0)
{
	$tipo_descrizione = "";
	$desc_descrizione = "";
	$coefficiente_descrizione = "";
	$portata_descrizione = "";
	$importo = "";
	$importo_fisso = "";
	$durata_fisso = "";
}
else
{
	$tipo_descrizione = $a_tariffa['Tipo'];
	$desc_descrizione = $a_tariffa['Descrizione'];
	$coefficiente_descrizione = $a_tariffa['Coefficiente'];
	$portata_descrizione = $a_tariffa['Deposito_Portata'];
	$importo = $cls_param->conv_num(number_format($a_tariffa['Importo'],2));;
	$importo_fisso = $cls_param->conv_num(number_format($a_tariffa['Importo_Fisso'],2));
	$durata_fisso = $a_tariffa['Km_Giorni_Importo_Fisso'];
	if($importo_fisso=="0,00")
	{
		$importo_fisso = "";
		$durata_fisso = "";
	}
}

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

	var id_tariffa = "<?php echo $id_tariffa; ?>";

//F3
switchMenuImg("F3");
F3_button = function()
{
		var control = false;

		if(id_tariffa == "0"){ control = submit_buttons('Insert');}
		else { control = submit_buttons('Update'); }

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
	location.href="par_tariffe_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_tariffa=<?php echo $id_tariffa; ?>";
}

//F6
switchMenuImg("F6");
F6_button = function()
{
	location.href="par_tariffe_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_tariffa=0";
}

//F11-F12 sono nel menu'
//F7
switchMenuImg("F7");
F7_button = function()
{
	if("<?= $TariffeCoazione->navigation['prev']; ?>" != "")
    	location.href="par_tariffe_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_tariffa=<?= $TariffeCoazione->navigation['prev']; ?>";
	else
		alert("Non ci sono tariffe precedenti per questo comune");
}
//F8
switchMenuImg("F8");
F8_button = function()
{
	if("<?= $TariffeCoazione->navigation['next']; ?>" != "")
    	location.href="par_tariffe_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_tariffa=<?= $TariffeCoazione->navigation['next']; ?>";
	else
		alert("Non ci sono tariffe successive per questo comune");
}


function cambio_tariffa()
{
	tipo = $('#tipo_tariffa').val();

	if(tipo=="A GIORNO" || tipo=="A KM")
	{
		$('.td_importo_fisso').show();
		$('.td_no_fisso').hide();
	}
	else
	{
		$('.td_no_fisso').show();
		$('.td_importo_fisso').hide();
	}

}

</script>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {

	 $( ".picker" ).datepicker();

	 });

</script>


<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Gestione tariffa</p>
	</div>
</div>


<form name=form_par_tariffe class="form-horizontal validate" id=form_par_tariffe method=post action="par_tariffe_coazione_salva.php">

<input type=hidden name=invia_submit 	id=invia_submit 	value=""  			>

<input type=hidden name=c 					value=<?php echo $c; ?> 			>
<input type=hidden name=a 					value=<?php echo $a; ?> 			>
<input type=hidden name=tariffa_id 	id=tariffa_id 	value="<?php echo $id_tariffa; ?>"   	>


<div class="row" style="padding-left: 8rem;padding-right:8rem;margin-top: 1rem;">
	<div class="col col-lg-12">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Descrizione</label>
			<div class="col-lg-10 col-lg-offset-1">
				<input class="form-control vld_req resize" id=descrizione_tariffa name=descrizione_tariffa value="<?php echo $desc_descrizione; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row" style="padding-left: 8rem;padding-right:8rem;">
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Tipo tariffa</label>
			<div class="col-lg-7 ">
				<select id=tipo_tariffa name=tipo_tariffa class="form-control vld_req resize" onchange="cambio_tariffa();">
					<option></option>
					<option>UNA TANTUM</option>
					<option>A GIORNO</option>
					<option>A KM</option>
				</select>
			</div>
		</div>
	</div>
	<div class="col col-lg-8 ">
		<div class="form-group">
			<label class="col-lg-3 control-label resize text_left">Specifiche (es. peso veicolo)</label>
			<div class="col-lg-9">
					<input class="form-control resize" id=specifiche_tariffa name=specifiche_tariffa value="<?php echo $portata_descrizione; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row" style="padding-left: 8rem;padding-right:8rem;">
<hr style="border:1px solid #B0BBE8">
	<div class="col col-lg-12">
	<div class="form-group">
          <div class="checkbox">
              <label>
                  <input type=checkbox class=" resize" id=coefficiente name=coefficiente value="si"> Spesa soggetta a coefficiente di applicazione ( incremento percentuale basato sul credito )
              </label>
          </div>

      </div>
	</div>
  </div>

	<div class="row" style="padding-left: 8rem;padding-right:8rem;">
		<div class="col col-lg-4 td_importo_fisso">
			<div class="form-group">
				<label class="col-lg-8 control-label resize color_titolo font16" style="text-align: left;">Importo fisso di &euro;</label>
				<div class="col-lg-4">
						<input class="form-control vld_dec resize" id=importo_fisso name=importo_fisso value="<?php echo $importo_fisso; ?>" size=7 >
				</div>
			</div>
		</div>
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-8 control-label resize color_titolo font16" style="text-align: left;">Importo di &euro;</label>
                <div class="col-lg-4 ">
                    <input class="form-control resize vld_decReq" id=importo_tariffa name=importo_tariffa value="<?php echo $importo; ?>" size=7>
                </div>
            </div>
        </div>
		<div class="col col-lg-4 td_importo_fisso">
			<div class="form-group">
				<label class="col-lg-8 control-label resize text_left">per i primi (gg/km)</label>
				<div class="col-lg-4">
						<input class="form-control vld_int resize" id=durata_fisso name=durata_fisso value="<?php echo $durata_fisso; ?>" size=4 >
				</div>
			</div>
		</div>
	</div>

	<div class="row" style="padding-left: 8rem;padding-right:8rem;">
	<hr style="border:1px solid #B0BBE8">
		<div class="form-group">
	      <label class="col-lg-12 control-label" style="text-align: left;">Pignoramenti associabili</label>
	 		<?php

	 		foreach($TariffeCoazione->getDocumentTypes() as $docId => $docType){
				$checkDoc = array_search($docId,$a_tariffa['DocumentList']);
				$readonly = "";
				if($checkDoc!==false){
					$checked = "checked";
					if(array_search($docId,$a_pignoLocked))
						$readonly = "onclick=\"alert('Tariffa registrata su questa tipologia di pignoramento! Rimozione disabilitata');return false;\"";
				}
				else
					$checked = "";
				?>
				<div class="col-lg-3">
	          		<div class="checkbox">
						<label>
							<input type="checkbox" <?= $readonly; ?> name="DefaultJSON[<?=$docId;?>][DocumentType]" value="<?=$docId;?>" <?=$checked;?>> <?= $docType['Description']; ?>
						</label>
	          		</div>
				</div>
				<div class="col-lg-1">

					<select name="DefaultJSON[<?=$docId;?>][Tipototale]">
						<option></option>
						<?php 
						$selectTotal = null;
						foreach($a_tariffa['Default'] as $default){
							if($docId == $default->DocumentType){
								$selectTotal = $default->Tipototale;
								break;
							}
						}

						for($i=1;$i<=3;$i++){
							if($i==$selectTotal)
								$selected = "selected";
							else
								$selected = "";
							?>
							<option value="<?=$i;?>" <?=$selected;?>>Totale <?=$i;?></option>
							<?php

						}
						?>
					</select>
				</div>
	          	
				<?php
			}

			?>
	    </div>
	</div>

	<div class="form-group">
		<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
	</div>


</form>



<script type="text/javascript">
	$( window ).load(function() {

		$('#tipo_tariffa').val('<?php echo $tipo_descrizione; ?>');
		$('#new_importo').hide();

		if("<?php echo $coefficiente_descrizione; ?>"=="si")
		{
			$('#coefficiente').prop('checked',true);
		}

		if(id_tariffa == "0")
		{
			$("#importo_tariffa").addClass("validateCustom vld_Custom_r vld_Custom_d");
		}

		cambio_tariffa();

	});
</script>

<?php include(INC."/footer.php"); ?>
