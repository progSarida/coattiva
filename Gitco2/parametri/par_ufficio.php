<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");

if(!isset($_SESSION['username']))
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');

$query = "SELECT * FROM parametri_ufficio WHERE CC = '" . $c . "' AND Tipo_Riscossione = '".$tipo_riscossione."' ";
$result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"parametri_ufficio");

// Inclusione modale per ricerca comune
include_once(ROOT . "/search_modal/offcanvas/city_offcanvas.php");

?>


<!-- ********** MODALI AJAX ********** -->
<script>
// Modali offcanvas
// Apertura modale
function openOfcanvas(type){
    switch (type){
        case 'citySearchModal':
            // Reset campi input
            $('#city').val("");
            // Reset spazi tabella
            $('#appendTableCity').empty();

            $('#'+type).modal('show');
            break;
    }
}
// Inserimento dati selezionati
function initialId(tipo,val){
    switch (tipo){
		case 'city':
			case 0:
                $('#comune_id').val(val["nome"]);
                $('#prov_id').val(val["prov"]);
                $('#cap_id').val(val["cap"]);
                break;
		default: break;
    }
}

</script>

<body class="sfondo_new_gitco" >

<?php
	include(INC."/menu.php");
?>

<!-- ********** GESTIONE LINK MENU ********** -->
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
	stringa = "ufficio.php?"+stringaPHP;
	   	top.location.href = stringa;
}

switchMenuImg("F11");
F11_button = function(){

    //$("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Ufficio.pdf"; ?>");
    //$("#helpModalLabel").empty().append("<b>Help Ufficio</b>");
    //$("#helpModal").modal('show');

}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
    {
        location.href = "par_generali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&p=&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }
    else
	    alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "par_generali.php?par_responsabili=<?php echo $tipo_riscossione; ?>&p=&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


</script>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Ufficio</p>
	</div>
</div>

<form class="form-horizontal validate" name=form_ufficio id=form_ufficio method=post action="par_ufficio_salva.php">

<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=ufficio_id  value="<?= $result["ID"]; ?>" >
<input type=hidden name=tipo_riscossione 		value=<?php echo $tipo_riscossione; ?> >

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Denominazione</label>
			<div class="col-lg-8">
					<input style="width: 80%;" class="text_left form-control vld_req resize" id=denom_id name=denom size=50 value="<?php echo $result["Denominazione"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
					<input style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" class="sfondo_azzurro text_left form-control resize" readonly name=comune id=comune_id value="<?php echo $result["Comune"]; ?>" size=15 ondblclick="openOfcanvas('citySearchModal');">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-8">
					<input style="width: 50%; background-color: #97CFDD; border: 2px solid black;" class="sfondo_azzurro text_left form-control resize" readonly id=prov_id name=prov value="<?php echo $result["Provincia"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
			<div class="col-lg-8">
					<input style="width: 80%; background-color: #97CFDD; border: 2px solid black;" class="sfondo_azzurro text_center form-control resize" readonly id=cap_id name=cap size=4 value="<?php echo $result["Cap"]; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
					<input style="width: 100%;" id=via class="text_left form-control vld_req resize" name=via type=text value="<?php echo $result["Via"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=civico class="text_right form-control resize vld_intReq"  name="civico"  	value="<?php echo $result["Civico"]; ?>"  size=2 >
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=esponente  class="text_left form-control vld_esp resize"   name="esponente" value="<?php echo $result["Esponente"]; ?>"  size=2 >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=interno class="text_right form-control resize vld_int"  name="interno" 	value="<?php echo $result["Interno"]; ?>"  size=2 >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=dettagli   class="text_left form-control resize"   name="dettagli" 	value="<?php echo $result["Dettagli"]; ?>"  size=20>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
					<input style="width: 100%;" class="text_left form-control vld_tel resize" id=tel_id name=tel value="<?php echo $result["Telefono"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
					<input style="width: 100%;" class="text_left form-control vld_tel resize" id=fax_id name=fax value="<?php echo $result["Fax"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva</label>
			<div class="col-lg-8">
					<input style="width: 100%;" class="text_left form-control vld_tel resize" id=PI_id name=PI value="<?php echo $result["Partita_Iva"]; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input style="width: 100%;" class="text_left form-control resize vld_email" id=email_id name=email size=18 value="<?php echo $result["Mail"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
			<div class="col-lg-8">
				<input style="width: 100%;" class="text_left form-control resize vld_email" id=pec_id name=PEC size=18 value="<?php echo $result["PEC"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input style="width: 100%;" class="text_left form-control vld_Sito resize" id=sito_id name=sito size=16 value="<?php echo $result["Sito"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-1 control-label resize" style="text-align: left;">Orario</label>
			<div class="col-lg-11">
				<textarea style="max-width: 100%;" class="text_left form-control resize" id=orario_id name=orario ><?php echo $result["Orario"]; ?></textarea>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%;"></div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>


<?php include(INC."/footer.php"); ?>
