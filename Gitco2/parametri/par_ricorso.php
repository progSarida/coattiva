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

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];


$a_param = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($cls_param->Get_Query_Ricorso($c)),"parametri_ricorso");

$par_id = $a_param["ID"];
if($par_id==null) $par_id = 0;

$termini_cgt = "";
$termini_gius_ord = "";

if($a_param["ID"] != null)
{
    $termini_cgt = $a_param["Termini_Corte_Giustizia_Tributaria"];
	$termini_gius_ord = $a_param["Termini_Giustizia_Ordinaria"];
}

?>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {

	 $( ".picker" ).datepicker();

	 });

function change_prot()
{
	tipo = $('[name=tipo_protocollo]:checked').val();

	if(tipo=="fisso")
		$('#fisso').removeClass('sfondo_grigio').prop('readonly',false);
	else
		$('#fisso').addClass('sfondo_grigio').prop('readonly',true).val('');
}

</script>

<?php

include(INC."/menu.php");

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	control = submit_buttons('Salva');
	if(control)
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
	location.href="par_ricorso.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_annuali.php?tipo_riscossione=*****&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "par_scorpori.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}
//F12 sono nel menu'
switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Parametri_Ricorso.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Parametri Ricorso</b>");
    $("#helpModal").modal('show');
}

</script>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Parametri ricorsi</p>
	</div>
</div>

<form class="form-horizontal validate" name=form_par_ricorso id=form_par_ricorso method=post action="par_ricorso_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >

<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>"   	>

<div class="row">
	<div class="col col-lg-10 col-lg-offset-1 resize"><p class="titolo font14"><b>Termini per presentare ricorso</b></p></div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Giustizia Ordinaria</label>
			<div class="col-lg-3 ">
				<input class="form-control vld_intReq resize" style="width: 30%" name=termini_giust_ord value="<?php echo $termini_gius_ord; ?>" >
			</div>
			<div class="col-lg-4 resize">giorni dalla data di notifica dell'atto </div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Corte di giustizia tributaria di I grado</label>
			<div class="col-lg-3 ">
				<input class="form-control vld_intReq resize" style="width: 30%" name=termini_cgt value="<?php echo $termini_cgt; ?>" >
			</div>
			<div class="col-lg-4 resize">giorni dalla data di notifica dell'atto </div>
		</div>
	</div>
</div>

<div class="form-group">
		<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<?php include(INC."/footer.php"); ?>
