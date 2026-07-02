<?php

if (!session_id()) session_start();

if(!isset($_SESSION['username']))
{
    header("Location: /gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_city.php");
include_once(CLS."/cls_Utils.php");




$cls_city = new cls_city();

if($a_enteAdmin["Codice_290"]==00000)
	$a_enteAdmin["Codice_290"] = null;

$selectGeneralTax = "";
$selectDistinctTax = "";

if($a_enteAdmin["Select_Tax"]==1)
    $selectDistinctTax = "selected";
else if($a_enteAdmin["Select_Tax"]==2)
    $selectGeneralTax = "selected";

if($a_enteAdmin["Info_Interno"]==0) $a_enteAdmin["Info_Interno"]="";
if($a_enteAdmin["Info_Civico"]==0)  $a_enteAdmin["Info_Civico"]="";

$opt_Select_Tax = "<option></option><option ".$selectDistinctTax." value=1>Divise in categorie</option><option ".$selectGeneralTax." value=2>Nessuna distinzione</option>";

$lay_cap = "";
$lay_istat = "";
$lay_prov = "";
$Pro_Sigla = "";
$a_htmlData = array();

if($a_enteAdmin["Codici_Unione"]!="")
{
    $layout_comune = "<select style='background-color: rgb(153, 204, 255); border: 2px solid black;' class='form-control resize' id=comune name=comune onchange='scegli_comune();'>";
    $layout_comune .= "<option value=''></option>";

    $codici = explode("/", $a_enteAdmin["Codici_Unione"]);
    $com = array();


	for($i=0;$i<count($codici);$i++)
	{
        $a_city = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_city->getCityProvince_query($codici[$i])));
				$a_htmlData[$codici[$i]] = array(
										"istat"=>$a_city['Com_Codice'],
										"cap"=>$a_city['Com_Cap'],
										"prov"=>$a_city['Pro_Nome'],
										"sigla"=>$a_city['Pro_Sigla']
								);


		$layout_comune .= "<option value='".$codici[$i]."' ";
        if($codici[$i]==$a_enteAdmin["Info_Comune"]){
            $layout_comune .= "SELECTED ";
            $lay_cap = $a_city['Com_Cap'];
            $lay_istat = $a_city['Com_Codice'];
            $lay_prov = $a_city['Pro_Nome'];
            $Pro_Sigla = $a_city['Pro_Sigla'];
        }
        $layout_comune .= ">".$a_city['Com_Nome']."</option>";
	}
	  $layout_comune .= "</select>";
}
else
{
    $a_city = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_city->getCityProvince_query($a_enteAdmin['CC'])));
	$layout_comune = '<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=comune id=comune value="'.$a_city['Com_Nome'].'">';
	$lay_cap = $a_city['Com_Cap'];
	$lay_istat = $a_city['Com_Codice'];
	$lay_prov = $a_city['Pro_Nome'];
	$Pro_Sigla = $a_city['Pro_Sigla'];
}
?>

<script>
//F3
switchMenuImg("F3");
F3_button = function()
{
	control_salva = submit_buttons('Salva');
	if(control_salva)
			$("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "dati_ente.php?"+stringaPHP;
	   	top.location.href = stringa;
}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Dati_Ente.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Stemmi</b>");
    $("#helpModal").modal('show');

}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "stemma.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "gestore.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

function scegli_comune()
{
    value = $('#comune').val();
		//alert(value);
    $('#CC').val(value);

		var a_htmlData = <?php echo json_encode($a_htmlData); ?>;

		$('#istat_id').val(a_htmlData[value]['istat']);
    $('#cap_id').val(a_htmlData[value]['cap']);
    $('#prov_id').val(a_htmlData[value]['prov']);
    $('#pro_sigla').val(a_htmlData[value]['sigla']);

}
/*****************************STAMPA ARRAY************************************/

/*console.log(a_htmlData);
alert(a_htmlData['B067']['istat']);*/
/*****************************************************************************/
</script>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Dati Ente</span>
	</div>
</div>

<form class="form-horizontal validate" name=form_ente id=form_ente method=post action="dati_ente_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=info_id id=info_id value="<?php echo $a_enteAdmin["Info_ID"]; ?>" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC id=CC value="<?php echo $c; ?>" >
<input type=hidden name=Pro_Sigla	id=pro_sigla	value=<?php echo $Pro_Sigla; ?> >

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
					<?php echo $layout_comune; ?>
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-8">
					<input style="background-color: #97CFDD; border: 2px solid black;" class="text_left form-control resize" readonly id=prov_id name=prov value="<?php echo $lay_prov; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
			<div class="col-lg-8">
					<input style="background-color: #97CFDD; border: 2px solid black;" class="text_center form-control resize" readonly id=cap_id name=cap value="<?php echo $lay_cap; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">ISTAT</label>
			<div class="col-lg-8" >
					<input style="background-color: #97CFDD; border: 2px solid black;" class="text_center form-control resize" readonly id=istat_id name=istat size=5 value="<?php echo $lay_istat; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
					<input id=via class="form-control resize text_left vld_req" name=via type=text value="<?php echo $a_enteAdmin["Info_Via"]; ?>"  >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
					<input type="text" id=civico 	class="text_right form-control resize vld_intReq"  name="civico"	value="<?php echo $a_enteAdmin["Info_Civico"]; ?>"  >
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
					<input type="text" id=esponente  class="text_left form-control vld_esp resize" style="width: 50%;" name="esponente" value="<?php echo $a_enteAdmin["Info_Esponente"]; ?>"  >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8" >
					<input type="text" id=interno    class="text_right form-control resize vld_int"  name="interno" 	value="<?php echo $a_enteAdmin["Info_Interno"]; ?>"  >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8" >
					<input type="text" id=dettagli style="width: 100%;"  class="text_left form-control resize"   name="dettagli" 	value="<?php echo $a_enteAdmin["Info_Dettagli"]; ?>">
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva</label>
			<div class="col-lg-8">
					<input class="text_left resize form-control vld_PI" style="width: 100%;" id=PI_id name=PI value="<?php echo $a_enteAdmin["Info_PI"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Codice Fiscale</label>
			<div class="col-lg-8">
					<input class="text_left form-control resize vld_CF" style="width: 70%;" id=CF_id name=CF value="<?php echo $a_enteAdmin["Info_CF"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
					<input class="text_left resize vld_tel form-control" style="width: 100%;" id=tel_id name=tel value="<?php echo $a_enteAdmin["Info_Telefono"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
					<input class="text_right form-control resize vld_tel text_left" style="width: 70%;" id=fax_id name=fax value="<?php echo $a_enteAdmin["Info_Fax"]; ?>" ondblclick="controllaCampi();">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize " style="text-align: left;">Email</label>
			<div class="col-lg-8">
					<input class="text_left resize form-control vld_email" style="width: 100%;" id=email_id name=email size=18 value="<?php echo $a_enteAdmin["Info_Mail"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize " style="text-align: left;">PEC</label>
			<div class="col-lg-8">
					<input class="text_left form-control resize vld_email" style="width: 70%;" id=pec_id name=PEC size=18 value="<?php echo $a_enteAdmin["Info_PEC"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
					<input class="text_left form-control resize vld_Sito" style="width: 100%;" id=sito_id name=sito size=16 value="<?php echo $a_enteAdmin["Info_Sito"]; ?>" >
				</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Codice ente 290</label>
			<div class="col-lg-8">
					<input class="text_left resize form-control vld_int" style="width: 40%;" id=codice_290 name=codice_290 size=8 value="<?php echo $a_enteAdmin["Codice_290"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-7 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-8 control-label resize" style="text-align: left;">Gestione entrate in fase di elaborazione e stampa</label>
			<div class="col-lg-4">
					<select id=Select_Tax name=Select_Tax class="resize form-control vld_req" style="width: 100%;">
              <?=$opt_Select_Tax;?>
          </select>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-2 col-lg-offset-1 resize">
		<strong>Mesi inattività discarichi Art. 19</strong>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<input type="number" class="form-control resize vld_intReq" name="mesi_inattivita_sgravio" id="mesi_inattivita_sgravio"
			       value="<?php echo (int)($a_enteAdmin['Mesi_Inattivita_Sgravio'] ?? 12); ?>"
			       min="1" max="120" style="width: 80px;">
		</div>
	</div>
	<div class="col col-lg-6 resize" style="padding-top: 7px;">
		Mesi dopo i quali una posizione Informativa (I) con ricorso/crisi/rateizzazione inattivi diventa Definitiva (D).
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<?php include(INC."/footer.php"); ?>
