<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_paramUtils.php");

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

if(substr($c,0,1)=="U")
    $enteSpese = "della ".$a_enteAdmin["Denominazione"];
else
    $enteSpese = "del Comune di ".$a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$layout="";


$cls_param = new cls_param();
$a_param = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_Gen($c , $tipo_riscossione)));

$par_id = $a_param['ID'];
if($par_id==null) $par_id = 0;

$layout.="<script>updateInputs('".$a_param['Spese_Anticipate']."','".$a_param['Testo_Spese_Anticipate']."','".$a_param['SMA']."','".$a_param['Intestatario_SMA']."','".$a_param['Numero_SMA']."')</script>";
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

    function updateInputs(spese,testo_spese,SMA,intestatario_SMA,numero_SMA){

        $('#spese_anticipate').val(spese);

        if(spese=="y"){
            $('#testo_spese').prop('readonly',false).toggleClass("readonly").val(testo_spese);
						$('#testo_spese').addClass("validateCustom vld_Custom_r");
        }else
				{
					$('#testo_spese').removeClass("validateCustom vld_Custom_r");
				}

        $('#SMA').val(SMA);

        if(SMA=="y"){
            $('#intestatario_SMA').prop('readonly',false).toggleClass("readonly").val(intestatario_SMA);
            $('#numero_SMA').prop('readonly',false).toggleClass("readonly").val(numero_SMA);
						$('#intestatario_SMA').addClass("validateCustom vld_Custom_r");
						$('#numero_SMA').addClass("validateCustom vld_Custom_r");
        }else{
					$('#intestatario_SMA').removeClass("validateCustom vld_Custom_r");
					$('#numero_SMA').removeClass("validateCustom vld_Custom_r");
				}
    }

    function changeSMA(){
        SMA = $('#SMA').val();
        if(SMA=="y"){
            $('#intestatario_SMA').prop('readonly',false).removeClass("readonly");
            $('#numero_SMA').prop('readonly',false).removeClass("readonly");

						$('#intestatario_SMA').addClass("validateCustom vld_Custom_r");
						$('#numero_SMA').addClass("validateCustom vld_Custom_r");

        }
        else{
            $('#intestatario_SMA').prop('readonly',true).addClass("readonly").val("");
            $('#numero_SMA').prop('readonly',true).addClass("readonly").val("");

						$('#intestatario_SMA').removeClass("validateCustom vld_Custom_r");
						$('#numero_SMA').removeClass("validateCustom vld_Custom_r");

						validateForm();
        }
    }

    function changeSpese(){
        spese_anticipate = $('#spese_anticipate').val();
        if(spese_anticipate=="y"){
            $('#testo_spese').prop('readonly',false).removeClass("readonly");
						$('#testo_spese').addClass("validateCustom vld_Custom_r");
        }
        else{
            $('#testo_spese').prop('readonly',true).addClass("readonly").val("");
						$('#testo_spese').removeClass("validateCustom vld_Custom_r");

						validateForm();
        }
    }

</script>

	<?php

	include(INC."/menu.php");

	?>

<script type="text/javascript">


//F3
switchMenuImg("F3");
F3_button = function()
{
	control = submit_buttons('Salva');
	if(control)
	{
		if(validateForm())
			$("#btnSub").trigger("click");
	}

}



//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="par_generali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_responsabili.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "par_email.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'

</script>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Parametri generali (<?php echo $titolo_riscossione; ?>)</p>
	</div>
</div>

<form class="form-horizontal validate" name=form_par_generali id=form_par_generali method=post action="par_generali_salva.php" enctype="multipart/form-data">

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=tipo_riscossione value=<?php echo $tipo_riscossione; ?> >
<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>"   	>


<div class="row">
	<div class="col-lg-3 col-lg-offset-1">
		<span class="titolo resize">DISTINTA SMA</span>
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<select class="form-control vld_req resize" id="SMA" name="SMA" onchange="changeSMA();" style="width: 10%;">
					<option value="n">No</option>
					<option value="y">Si</option>
			</select>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Intestatario
	</div>
	<div class="col-lg-2">
		<div class="form-group">
			<input class="readonly width80 form-control resize " name="intestatario_SMA" id="intestatario_SMA" readonly>
		</div>
	</div>
	<div class="col-lg-1 text_center resize">
		Numero
	</div>
	<div class="col-lg-4">
		<div class="form-group ">
		  <input class="readonly width80 form-control resize" name="numero_SMA" id="numero_SMA" readonly>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-3 col-lg-offset-1 resize">
		<span class="titolo">RIFATTURAZIONE SPESE</span>
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<select class="form-control vld_req resize" id="spese_anticipate" name="spese_anticipate" onchange="changeSpese();" style="width: 10%;">
					<option value="n">No</option>
					<option value="y">Si</option>
			</select>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Testo
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea class="readonly form-control resize" name="testo_spese" id="testo_spese" style="max-width: 100%;" readonly></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-10 col-lg-offset-1 text_left resize"><span class="titolo">RESTITUZIONE MOD.23L - RACCOMANDATA A.G.</span></div>
</div>
<div class="row" style="padding-top: 2%;">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Soggetto mittente
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'NOME GESTORE - GESTIONE:'" class="form-control vld_req resize" name="restituzione[1]" id="restituzione1" style="max-width: 100%;"><?php echo $a_param['Restituzione1']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Ente gestito
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'COMUNE DI NOME ENTE'" class="form-control vld_req resize" name="restituzione[2]" id="restituzione2" style="max-width: 100%;"><?php echo $a_param['Restituzione2']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Recapito - Soggetto
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'C/O MERCURIO SERVICES S.R.L.'" class="form-control vld_req resize" name="restituzione[3]" id="restituzione3" style="max-width: 100%;"><?php echo $a_param['Restituzione3']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Recapito - Indirizzo
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'VIA DELLA CASA BUIA 4-4/G'" class="form-control vld_req resize" name="restituzione[4]" id="restituzione4" style="max-width: 100%;"><?php echo $a_param['Restituzione4']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Recapito - CAP Comune Provincia
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. '40129 BOLOGNA BO'" class="form-control vld_req resize" name="restituzione[5]" id="restituzione5" style="max-width: 100%;"><?php echo $a_param['Restituzione5']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-10 col-lg-offset-1 text_left resize"><span class="titolo">RESTITUZIONE MOD.23O - RACCOMANDATA</span></div>
</div>
<div class="row" style="padding-top: 2%;">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Soggetto mittente
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'NOME GESTORE - GESTIONE:'" class="form-control vld_req resize" name="restituzione_Mod23O[1]" id="restituzione_Mod23O_1" style="max-width: 100%;"><?php echo $a_param['Restituzione1_Mod23O']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Ente gestito
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'COMUNE DI NOME ENTE'" class="form-control vld_req resize" name="restituzione_Mod23O[2]" id="restituzione_Mod23O_2" style="max-width: 100%;"><?php echo $a_param['Restituzione2_Mod23O']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Recapito - Soggetto
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'C/O MERCURIO SERVICES S.R.L.'" class="form-control vld_req resize" name="restituzione_Mod23O[3]" id="restituzione_Mod23O_3" style="max-width: 100%;"><?php echo $a_param['Restituzione3_Mod23O']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Recapito - Indirizzo
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. 'VIA DELLA CASA BUIA 4-4/G'" class="form-control vld_req resize" name="restituzione_Mod23O[4]" id="restituzione_Mod23O_4" style="max-width: 100%;"><?php echo $a_param['Restituzione4_Mod23O']; ?></textarea>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-2 col-lg-offset-2 resize">
		Recapito - CAP Comune Provincia
	</div>
	<div class="col-lg-7">
		<div class="form-group ">
			<textarea placeholder="es. '40129 BOLOGNA BO'" class="form-control vld_req resize" name="restituzione_Mod23O[5]" id="restituzione_Mod23O_5" style="max-width: 100%;"><?php echo $a_param['Restituzione5_Mod23O']; ?></textarea>
		</div>
	</div>
</div>
<div class="form-group">
		<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>
<?php
	echo $layout;
?>


<?php include(INC."/footer.php"); ?>
