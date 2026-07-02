<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_paramUtils.php");
include_once(CLS."/cls_DateTime.php");


if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo_riscossione = "*****";
$nuovo_anno = $cls_help->getVar('nuovo_anno');

$anno_scelta = $cls_help->getVar('anno_scelta');

$cls_param = new cls_param();

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

if ($nuovo_anno != "") $anno_par = "";
else if ($anno_scelta == "") $anno_par = substr(date('Y-m-d'),0,4);
else $anno_par = $anno_scelta;

$a_param = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($cls_param->Get_Query_Annuali($c , $anno_par)),"parametri_annuali");

$listaParametriAnnuali = $cls_param->Cerca_Anni_Parametri_Annuali($c, $anno_par);


$par_id = $a_param["ID"];
if($par_id == null)
	$par_id = 0;



$spese_ing = "";
$data_spese_ing = new cls_DateTime($a_param["Spese_Notifica_Data"],"DB");
$nuovo_spese_ing = "";

$spese_pigno = "";
$data_spese_pigno = new cls_DateTime($a_param["Spese_Notifica_Pignoramento_Data"],"DB");
$nuovo_spese_pigno = "";

$spese_cautelari = "";
$data_spese_cautelari = new cls_DateTime($a_param["Spese_Notifica_Cautelari_Data"],"DB");
$nuovo_spese_cautelari = "";

$ric_spese = "";
$data_ric_spese = new cls_DateTime($a_param["Spese_Ricerca_Data"],"DB");
$nuovo_ric_spese = "";

$spese_post = "";
$data_spese_post = new cls_DateTime($a_param["Spese_Postali_Data"],"DB");
$nuovo_spese_post = "";

$spese_racc = "";
$data_spese_racc = new cls_DateTime($a_param["Spese_Raccomandata_Data"],"DB");
$nuovo_spese_racc = "";

$spese_post_ag = "";
$data_spese_post_ag = new cls_DateTime($a_param["Spese_Postali_AG_Data"],"DB");
$nuovo_spese_post_ag = "";

$can = "";
$data_can = new cls_DateTime($a_param["CAN_Data"],"DB");
$nuovo_can = "";

$cad = "";
$data_cad = new cls_DateTime($a_param["CAD_Data"],"DB");
$nuovo_cad = "";

$a_mani = "";
$data_a_mani = new cls_DateTime($a_param["A_Mani_Data"],"DB");
$nuovo_a_mani = "";

$a_mani_pigno = "";
$data_a_mani_pigno = new cls_DateTime($a_param["A_Mani_Pignoramento_Data"],"DB");
$nuovo_a_mani_pigno = "";

$a_mani_cautelari = "";
$data_a_mani_cautelari = new cls_DateTime($a_param["A_Mani_Cautelari_Data"],"DB");
$nuovo_a_mani_cautelari = "";

$iva = "";
$data_iva = new cls_DateTime($a_param["IVA_Data"],"DB");
$nuovo_iva = "";

$checked_preav = "";
$checked_ing = "";
$checked_sgravi = "";

$diritto_riscossione_minimo = "";
$diritto_riscossione_massimo = "";

$importo_min = 0;
$giorni_diritto = $a_param["Giorni_Diritto"];
$spese_pec = "";
$spese_pec_banca = "";

if($a_param["ID"] != null)
{
	$spese_ing = $a_param["Spese_Notifica"];

	if($data_spese_ing->GetDate()!="")
	{
		$nuovo_spese_ing = $a_param["Spese_Notifica_New"];
	}

	$spese_pigno = $a_param["Spese_Notifica_Pignoramento"];
	if($data_spese_pigno->GetDate()!="")
	{
		$nuovo_spese_pigno = $a_param["Spese_Notifica_Pignoramento_New"];
	}

	$spese_cautelari = $a_param["Spese_Notifica_Cautelari"];
	if($data_spese_cautelari->GetDate()!="")
	{
		$nuovo_spese_cautelari = $a_param["Spese_Notifica_Cautelari_New"];
	}

	$ric_spese = $a_param["Spese_Ricerca"];
	if($data_ric_spese->GetDate()!="")
	{
		$nuovo_ric_spese = $a_param["Spese_Ricerca_New"];
	}

	$spese_post = $a_param["Spese_Postali"];
	if($data_spese_post->GetDate()!="")
	{
		$nuovo_spese_post = $a_param["Spese_Postali_New"];
	}

  $spese_racc = $a_param["Spese_Raccomandata"];
  if($data_spese_racc->GetDate()!="")
  {
      $nuovo_spese_racc = $a_param["Spese_Raccomandata_New"];
  }

	$spese_post_ag = $a_param["Spese_Postali_AG"];
	if($data_spese_post_ag->GetDate()!="")
	{
		$nuovo_spese_post_ag = $a_param["Spese_Postali_AG_New"];
	}

	$can = $a_param["CAN"];
	if($data_can->GetDate()!="")
	{
		$nuovo_can = $a_param["CAN_New"];
	}

	$cad = $a_param["CAD"];
	if($data_cad->GetDate()!="")
	{
		$nuovo_cad = $a_param["CAD_New"];
	}

	$a_mani = $a_param["A_Mani"];
	if($data_a_mani->GetDate()!="")
	{
		$nuovo_a_mani = $a_param["A_Mani_New"];
	}

	$a_mani_pigno = $a_param["A_Mani_Pignoramento"];
	if($data_a_mani_pigno->GetDate()!="")
	{
		$nuovo_a_mani_pigno = $a_param["A_Mani_Pignoramento_New"];
	}
	$a_mani_cautelari = $a_param["A_Mani_Cautelari"];
	if($data_a_mani_cautelari->GetDate()!="")
	{
		$nuovo_a_mani_cautelari = $a_param["A_Mani_Cautelari_New"];
	}

	$iva = $a_param["IVA"];
	if($data_iva->GetDate()!="")
	{
		$nuovo_iva = $a_param["IVA_New"];
	}

	$maggiorazione_preavv = $a_param["Maggiorazione_Preavviso"];
	if($maggiorazione_preavv == "no")
		$checked_preav = "checked";

	$maggiorazione_ing = $a_param["Maggiorazione_Ingiunzione"];
	if($maggiorazione_ing == "no")
		$checked_ing = "checked";

	$flag_sgravi = $a_param["Flag_Sgravi_Elenco_Pagamenti"];
	if($flag_sgravi == 1)
		$checked_sgravi = "checked";

	$importo_min = $a_param["Importo_Minimo"];

	$diritto_riscossione_minimo = $a_param["Diritto_Riscossione_Minimo"];
	$diritto_riscossione_massimo = $a_param["Diritto_Riscossione_Massimo"];
	$spese_pec = $a_param["Spese_Pec"];
	$spese_pec_banca = $a_param["Spese_Pec_Banca"];


}

$optionAnni = "";
foreach ($listaParametriAnnuali[0] as $key => $valore)
{

		if ($anno_par == $valore) $selAnno = " selected ";
		else $selAnno = "";
		$optionAnni .= "<option value='$valore' $selAnno>$valore</option>\n";

}
//var_dump($optionAnni);

if ($nuovo_anno == "")
{
	$campoAnno = "<select name='campoanno' onchange='cambia_pag();'>\n" . $optionAnni . "</select>";
}
else
{
	$campoAnno = "<input type='text' class='pwidth40 text_center' name='campoanno' onchange='CambioAnno();'>";
}

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>


function CambioAnno ()
{
	var newanno = $("[name=campoanno]").val();
	if (newanno == "")
	{
		alert ("Inserire il nuovo anno di gestione");
		return false;
	}

	var errore = false;

	<?php
	foreach ($listaParametriAnnuali[0] as $key => $valore)
	{
		echo "if (errore == false && newanno == '" . $valore . "') errore = true;\n";
	}
	?>

	if (errore == true)
	{
		alert ("L'anno " + newanno + " è già inserito nel sistema");
		return false;
	}

	$("#par_id").val("");
	$("#anno_par_id").val(newanno);
	return true;
}

function cambia_giorni()
{
	$('#giorni_cambia').text($('#giorni_diritto').val());
	$('#giorni_cambia_2').text($('#giorni_diritto').val());
}

</script>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {

	 $( ".picker" ).datepicker();

	 });

</script>


<?php 	include(INC."/menu.php"); ?>

<script type="text/javascript">

//F3
switchMenuImg("F3");
F3_button = function()
{
	var control;
	if ("<?=$nuovo_anno?>" != "") control = CambioAnno ();
	else control = true;
	if (control)
	{
		control = submit_buttons('Salva');

		if(control && validateForm())
				$("#btnSub").trigger("click");
	}
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="par_annuali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
switchMenuImg("F6");
F6_button = function()
{
	var strLink = "par_annuali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>";
	strLink += "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	strLink += "&nuovo_anno=SI";
	location.href = strLink;
}

//F7
switchMenuImg("F7");
F7_button = function()
{
	var strLink;
	strLink = "par_annuali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>";
	strLink += "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	strLink += "&anno_scelta=<?php echo $listaParametriAnnuali[1]['PRECEDENTE']; ?>";
	location.href = strLink;
}

//F8
switchMenuImg("F8");
F8_button = function()
{
	var strLink;
	strLink = "par_annuali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>";
	strLink += "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	strLink += "&anno_scelta=<?php echo $listaParametriAnnuali[1]['SUCCESSIVO']; ?>";
	location.href = strLink;
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
		location.href = "par_pagamento.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/ParametriAnnuali.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Parametri Annuali</b>");
    $("#helpModal").modal('show');

}

//F12 è nel menu'






function cambia_pag()
{
	var strLink;
	strLink = "par_annuali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>";
	strLink += "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	strLink += "&anno_scelta=" + $("[name=campoanno]").val();
	location.href = strLink;
}
</script>

	<div class="row justify-content-md-center "><!--col-lg-8 col-lg-offset-2-->
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Parametri annuali -
				anno <?php echo $campoAnno; ?>
			</p>
</div>
</div>
<div class="row" style="padding-top: 1.5%;"><div class="col col-lg-10 col-lg-offset-1 resize"><p class="titolo font14 text_left"><b>Spese specifiche notifica Ingiunzione</b></p></div> </div>
<div class="row">
	<form class="form-horizontal validate" name=form_par_anno id=form_par_anno method=post action="par_annuali_salva.php">

		<input type=hidden name=invia_submit 	id=invia_submit 	value=""  			>

		<input type=hidden name=c 					value=<?php echo $c; ?> 			>
		<input type=hidden name=a 					value=<?php echo $a; ?> 			>
		<input type=hidden name=tipo_riscossione	value=<?php echo $tipo_riscossione; ?> 			>
		<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>"   	>
		<input type=hidden name=anno_par 	id=anno_par_id 	value="<?php echo $anno_par; ?>"   	>

		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Spese notifica</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input style="width: 60%;" class="text_right form-control vld_dec vld_req resize" name=spese_ing id=spese_ing_id type="text" value="<?php echo $cls_param->conv_num($spese_ing); ?>">
						</div>
						<div class="col-lg-5 resize">CONSIGLIATO<br>Spese postali + CAD</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="text_right resize form-control vld_dec" name=nuovo_spese_ing id=nuovo_spese_ing_id value="<?php echo $cls_param->conv_num($nuovo_spese_ing); ?>" >
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="text_center picker form-control validateCustom vld_Custom_date resize" name=data_spese_ing id=data_spese_ing_id value="<?php echo $data_spese_ing->GetDate("IT"); ?>">
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Consegna a mani</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input style="width: 60%;" type="text" class="form-control resize text_right vld_dec vld_req" name=a_mani id=a_mani_id value="<?php echo $cls_param->conv_num($a_mani); ?>" />
						</div>
						<div class="col-lg-5 resize">( Importo forfettario )</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec " name=nuovo_a_mani id=nuovo_a_mani_id value="<?php echo $cls_param->conv_num($nuovo_a_mani); ?>" />
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date " name=data_a_mani id=data_a_mani_id value="<?php echo $data_a_mani->GetDate("IT"); ?>" size=9>
					</div>
				</div>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 1.5%; margin-bottom: 1%;"></div>
		<div class="row"><div class="col col-lg-10 col-lg-offset-1 resize"><p class="titolo font14 text_left"><b>Spese specifiche notifica Pignoramento</b></p></div></div>

		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Spese notifica</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input type="text" style="width: 60%;" class="resize text_right form-control vld_dec vld_req " name=spese_pigno id=spese_pigno_id value="<?php echo $cls_param->conv_num($spese_pigno); ?>">
						</div>
						<div class="col-lg-5 resize">CONSIGLIATO<br>Spese postali + CAD</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec " type="text" name=nuovo_spese_pigno id=nuovo_spese_pigno_id value="<?php echo $cls_param->conv_num($nuovo_spese_pigno); ?>">
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date " name=data_spese_pigno id=data_spese_pigno_id value="<?php echo $data_spese_pigno->GetDate("IT"); ?>" >
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Consegna a mani</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=a_mani_pigno id=a_mani_pigno_id value="<?php echo $cls_param->conv_num($a_mani_pigno); ?>" type="text" >
						</div>
						<div class="col-lg-5 resize">( Importo forfettario )</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec" name=nuovo_a_mani_pigno id=nuovo_a_mani_pigno_id value="<?php echo $cls_param->conv_num($nuovo_a_mani_pigno); ?>" type="text">
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="text_center resize picker form-control validateCustom vld_Custom_date " name=data_a_mani_pigno id=data_a_mani_pigno_id value="<?php echo $data_a_mani_pigno->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 1.5%; margin-bottom: 1%;"></div>
		<div class="row"><div class="col col-lg-10 col-lg-offset-1 resize"><p class="titolo font14 text_left"><b>Spese specifiche notifica Cautelari</b></p></div></div>

		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Spese notifica</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input type="text" style="width: 60%;" class="resize text_right form-control vld_dec vld_req " name=spese_cautelari id=spese_cautelari_id value="<?php echo $cls_param->conv_num($spese_cautelari); ?>">
						</div>
						<div class="col-lg-5 resize">CONSIGLIATO<br>Spese postali + CAD</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec " type="text" name=nuovo_spese_cautelari id=nuovo_spese_cautelari_id value="<?php echo $cls_param->conv_num($nuovo_spese_cautelari); ?>">
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date " name=data_spese_cautelari id=data_spese_cautelari_id value="<?php echo $data_spese_cautelari->GetDate("IT"); ?>" >
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Consegna a mani</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=a_mani_cautelari id=a_mani_cautelari_id value="<?php echo $cls_param->conv_num($a_mani_cautelari); ?>" type="text" >
						</div>
						<div class="col-lg-5 resize">( Importo forfettario )</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec" name=nuovo_a_mani_cautelari id=nuovo_a_mani_cautelari_id value="<?php echo $cls_param->conv_num($nuovo_a_mani_cautelari); ?>" type="text">
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="text_center resize picker form-control validateCustom vld_Custom_date " name=data_a_mani_cautelari id=data_a_mani_cautelari_id value="<?php echo $data_a_mani_cautelari->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-top: 1.5%; margin-bottom: 1%;"></div>
		<div class="row" ><div class="col col-lg-10 col-lg-offset-1 resize"><p class="titolo font14 text_left"><b>Spese ad uso generale</b></p></div></div>

		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize "><b>Spese postali ord.</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input type="text" style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=spese_post id=spese_post_id value="<?php echo $cls_param->conv_num($spese_post); ?>" />
						</div>
						<div class="col-lg-5"></div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input type="text" style="width: 60%;" class="text_right form-control vld_dec resize" name=nuovo_spese_post id=nuovo_spese_post_id value="<?php echo $cls_param->conv_num($nuovo_spese_post); ?>" />
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="text_center resize picker form-control validateCustom vld_Custom_date " name=data_spese_post id=data_spese_post_id value="<?php echo $data_spese_post->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Spese raccom. ord.</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=spese_racc id=spese_racc_id value="<?php echo $cls_param->conv_num($spese_racc); ?>" />
						</div>
						<div class="col-lg-5"></div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec" name=nuovo_spese_racc id=nuovo_spese_racc_id value="<?php echo $cls_param->conv_num($nuovo_spese_racc); ?>" />
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date " name=data_spese_racc id=data_spese_racc_id value="<?php echo $data_spese_racc->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Spese raccom. A.G.</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=spese_post_ag id=spese_post_ag_id value="<?php echo $cls_param->conv_num($spese_post_ag); ?>" type="text" />
						</div>
						<div class="col-lg-5 resize">( Importo forfettario )</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="text_right form-control vld_dec resize" name=nuovo_spese_post_ag id=nuovo_spese_post_ag_id value="<?php echo $cls_param->conv_num($nuovo_spese_post_ag); ?>" type="text" />
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date " name=data_spese_post_ag id=data_spese_post_ag_id value="<?php echo $data_spese_post_ag->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
						<label class="col-lg-4 control-label resize"><b>CAD</b> (&euro;)</label>
						<div class="col-lg-3">
								<input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=cad id=cad_id value="<?php echo $cls_param->conv_num($cad); ?>" type="text" />
						</div>
						<div class="col-lg-5"></div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec" name=nuovo_cad id=nuovo_cad_id value="<?php echo $cls_param->conv_num($nuovo_cad); ?>" type="text" />
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date" name=data_cad id=data_cad_id value="<?php echo $data_cad->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-lg-center">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>CAN</b> (&euro;)</label>
			      <div class="col-lg-3">
			          <input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=can id=can_id value="<?php echo $cls_param->conv_num($can); ?>" type="text" />
						</div>
						<div class="col-lg-5"></div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_dec" name=nuovo_can id=nuovo_can_id value="<?php echo $cls_param->conv_num($nuovo_can); ?>" type="text" />
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date" name=data_can id=data_can_id value="<?php echo $data_can->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>
        <div class="row justify-content-lg-center">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize"><b>Spese PEC</b> (&euro;)</label>
                    <div class="col-lg-3">
                        <input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=spese_pec id=spese_pec value="<?php echo $cls_param->conv_num($spese_pec); ?>" type="text" />
                    </div>
                    <div class="col-lg-5"></div>
                </div>
            </div>
        </div>
		<div class="row justify-content-lg-center">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize"><b>Spese PEC - BANCHE</b> (&euro;)</label>
                    <div class="col-lg-3">
                        <input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=spese_pec_banca id=spese_pec_banca value="<?php echo $cls_param->conv_num($spese_pec_banca); ?>" type="text" />
                    </div>
                    <div class="col-lg-5"></div>
                </div>
            </div>
        </div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

		<div class="row justify-content-lg-center" style="padding-top: 1%;">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
						<label class="col-lg-4 control-label resize"><b>IVA</b> (%)</label>
						<div class="col-lg-3">
								<input style="width: 60%;" class="resize text_right form-control vld_intReq" name=iva id=iva_id value="<?php echo $iva; ?>" type="text" />
						</div>
						<div class="col-lg-5"></div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize">Nuovo</label>
					<div class="col-lg-8">
							<input style="width: 60%;" class="resize text_right form-control vld_int" name=nuovo_iva id=nuovo_iva_id value="<?php echo $nuovo_iva; ?>" type="text" />
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date" name=data_iva id=data_iva_id value="<?php echo $data_iva->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

		<div class="row justify-content-start" style="padding-top: 1%;">
			<div class="col-lg-8 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize"><p class="text_left">Giorni diritto di riscossione</p></label>
					<div class="col-lg-2">
							<input style="width: 60%;" class="resize text_right form-control vld_intReq" name=giorni_diritto id=giorni_diritto value="<?php echo $giorni_diritto; ?>" onchange="cambia_giorni();">
					</div>
					<div class="col-lg-4"><p class="text_right"></p></div>
				</div>
			</div>
		</div>
		<div class="row justify-content-start" >
			<div class="col-lg-8 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize"><p class="text_left">Diritto di riscossione min (%)</p></label>
					<div class="col-lg-2">
							<input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=diritto_minimo id=diritto_minimo value="<?php echo $cls_param->conv_num($diritto_riscossione_minimo); ?>" />
					</div>
					<div class="col-lg-4 resize"><p class="text_right">( Pagamento entro i <span id=giorni_cambia><?php echo $giorni_diritto; ?></span> giorni )</p></div>
				</div>
			</div>
		</div>
		<div class="row justify-content-start" >
			<div class="col-lg-8 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize"><p class="text_left">Diritto di riscossione max (%)</p></label>
					<div class="col-lg-2">
							<input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=diritto_massimo id=diritto_massimo value="<?php echo $cls_param->conv_num($diritto_riscossione_massimo); ?>" type="text" />
					</div>
					<div class="col-lg-4 resize"><p class="text_right">( Pagamento oltre i <span id=giorni_cambia_2><?php echo $giorni_diritto; ?></span> giorni )</p></div>
				</div>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

		<div class="row" style="padding-top: 1%;">
			<div class="col-lg-10 col-lg-offset-1">
				<div class="form-group" >
					<div class="col-lg-7 resize"><p>L'ente non procede con la riscossione coattiva per importi da recuperare inferiori ad euro </p></div>
					<div class="col-lg-2">
							<input style="width: 60%;" class="resize text_right form-control vld_dec vld_req" name=importo_min id=importo_min_id value="<?php echo $cls_param->conv_num($importo_min); ?>" type="text" />
					</div>

				</div>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

		<div class="row" style="padding-top: 1%;">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
			      <label class="col-lg-4 control-label resize"><b>Spese ricerca</b> (&euro;)</label>
			      <div class="col-lg-2">
			          <input class="resize text_right form-control vld_dec vld_req" name=ric_spese id=spese_ric_id value="<?php echo $cls_param->conv_num($ric_spese); ?>" type="text" />
						</div>
						<div class="col-lg-6 resize">(verbali originari)</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-5 control-label resize">Nuovo</label>
					<div class="col-lg-7">
							<input style="width: 93%;" class="resize text_right form-control vld_dec" placeholder="00.00" name=nuovo_ric_spese id=nuovo_spese_ric_id value="<?php echo $cls_param->conv_num($nuovo_ric_spese); ?>" type="text">
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-7 control-label resize">Valido dal</label>
					<div class="col-lg-5">
							<input class="resize text_center picker form-control validateCustom vld_Custom_date" name=data_ric_spese id=data_ric_spese_id value="<?php echo $data_ric_spese->GetDate("IT"); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

		<div class="row" style="padding-top: 1%;">
			<div class="form-group resize">
				<label class="col-lg-3 col-lg-offset-1 control-label"><p class="color_titolo font16">Maggiorazione del 10% semestrale</p></label>
      <div class="col-lg-5">
          <div class="checkbox">
              <label>
                  <input class="vld_mycheckbox" type="checkbox" name="magg_preavv" value="no" <?php echo $checked_preav; ?>> Non applicare al Preavviso di ingiunzione
              </label>
          </div>
          <div class="checkbox">
              <label>
                  <input class="vld_mycheckbox" type="checkbox" name="magg_ing" value="no" <?php echo $checked_ing; ?>> Non applicare alle Ingiunzioni di pagamento ed ai procedimenti successivi
              </label>
          </div>

      </div>
  </div>
  <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

  <div class="col-lg-offset-1 col-lg-10">
          <div class="checkbox">
              <label>
                  <b style="color: red;">NON ATTIVO</b> <input class="vld_mycheckbox" type="checkbox" name="flag_sgravi" value="1" <?php echo $checked_sgravi; ?>> <b>la selezione di questo flag, andrà ad aggiungere alla stampa dell'elenco dei pagamenti apposita sezione, all'interno della quale verranno indicate e successivamente portate in detrazione, con apposito ricalcolo dei totali le somme riscosse a titolo di procedure cautelari e/o esecutive per le partite per le quali è già stato effettuato il discarico in periodi precedenti alla presente rendicontazione</b>
              </label>
          </div>

  </div>
		</div>


		<div class="form-group">
      <button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
		</div>
	</form>
</div>



<?php include(INC."/footer.php"); ?>
