<?php
if (!session_id()) session_start();


include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_paramUtils.php");

$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo = $cls_help->getVar('tipo');
$ID_ufficio = $cls_help->getVar('ID_ufficio');

$reload = $cls_help->getVar('reload');

if($tipo == "uff_anagrafico")
{
	$tipo_uff = "Ufficio anagrafico";
	$tipo_prev = "uff_postale";
	$tipo_next = "uff_postale";
}
else if($tipo == "uff_postale")
{
	$tipo_uff = "Ufficio postale";
	$tipo_prev = "uff_anagrafico";
	$tipo_next = "uff_anagrafico";
}
else
{
	$tipo_uff = "Sconosciuto";
	$tipo_prev = "uff_anagrafico";
	$tipo_next = "uff_anagrafico";
}

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
?>



<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	control_salva = submit_buttons('Salva');
	if(control_salva && validateForm())
			$("#btnSub").trigger("click");
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control_salva = submit_buttons('Delete');
	if(control_salva)
	{
		$("#btnSub").trigger("click");
	}

}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo=<?php echo $tipo; ?>";
	stringa = "ufficio_comune.php?"+stringaPHP;
	   	top.location.href = stringa;
}

//F6
switchMenuImg("F6");
F6_button = function()
{
    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo=<?php echo $tipo; ?>&ID_ufficio=nuovo";
    stringa = "ufficio_comune.php?"+stringaPHP;
    top.location.href = stringa;
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 	{
	 	location.href = "dati_ente.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $tipo_prev; ?>";
 	}
 	else
	 	alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href =  "ufficio.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo='<?php echo $tipo_next;?>'";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


//F12 solo nel menu'
switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Uffici_Anagrafici.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Uffici Anagrafici</b>");
    $("#helpModal").modal('show');

}


</script>

<!-- Inclusione modale per ufficio anagrafico -->
<?php include_once (ROOT."/search_modal/offcanvas/registry_offcanvas.php"); ?>
<!-- Inclusione modale per ufficio anagrafico -->
<?php include_once (ROOT."/search_modal/offcanvas/mail_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca sede -->
<?php include_once (ROOT."/search_modal/offcanvas/city_offcanvas.php"); ?>

<!-- ********** MODALI AJAX ********** -->
<script>
var selectRif = "";
// Modali offcanvas
function openOfcanvas(type,rif){
    selectRif = rif;
    var off = "<?php echo $cls_help->getVar('tipo');?>";
    switch (type){
        case 'citySearchModal':                                                     // ricerca comune
            // Reset campi input
            $('#city').val("");
            // Reset spazi tabella
            $('#appendTableCity').empty();
            // Apertura modale
            $('#citySearchModal').modal('show');
            break;
        case 'SearchModal':
            if(off == 'uff_anagrafico'){                                          // ricerca ufficio anagrafico
                // Reset campi input
                $('#registry_n').val("");
                // Reset spazi tabella
                $('#appendTableRegistry').empty();
                // Apertura modale
                $('#registrySearchModal').modal('show');
            }
            else if(off == 'uff_postale'){                                        // ricerca ufficio postale
                // Reset campi input
                $('#mail_n').val("");
                // Reset spazi tabella
                $('#appendTableMail').empty();
                // Apertura modale
                $('#mailSearchModal').modal('show');
            }
            break;
    }
}
function initialId(tipo,val){
    switch (tipo){
        case 'registry':
            $('#comune_id').val(val['Com_Nome']);
            $('#CC_id').val(val['CC']);

            var tipojs = "uff_anagrafico";
            //var tipojs = "<?php echo $cls_help->getVar('tipo');?>";               // eliminato perchè qui è sempre initialId dell'anagrafico

            reload_ufficio(val['CC'],tipojs);
            break;
        case 'mail':
            $('#comune_id').val(val['Com_Nome']);
            $('#CC_id').val(val['CC']);

            var tipojs = "uff_postale";
            //var tipojs = "<?php echo $cls_help->getVar('tipo');?>";               // eliminato perchè qui è sempre initialId del postale

            reload_ufficio(val['CC'],tipojs);
            break;
        case 'city':
            if(selectRif == 0){
                $('#comune_sede_id').val(val['nome']);
                $('#prov_id').val(val['prov']);
                $('#cap_id').val(val['cap']);
                $('#CC_sede_id').val(val['CC_C']);
            }else{
                $('#comune_id').val(val['nome']);
                $('#CC_id').val(val['CC_C']);
            }
            break;

    }
}
/*function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}*/

function callParent(valorediritorno) {

    switch(selectParent){
        case "sede":
            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                $('#comune_sede_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(valorediritorno.cap);
                $('#CC_sede_id').val(valorediritorno.CC);
            }
            break;
        case "comune":
            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                $('#comune_id').val(valorediritorno.nome_CC);
                $('#CC_id').val(valorediritorno.CC);

								var tipojs = "<?php echo $cls_help->getVar('tipo');?>";


								reload_ufficio(valorediritorno.CC,tipojs);
            }
            break;
        case "nuovo":
            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                $('#comune_id').val(valorediritorno.comune);
                $('#CC_id').val(valorediritorno.CC);
            }
            break;
    }
}

var selectParent = "";

function cerca_nuovo()
{
    selectParent = "nuovo";
    //strDim = Dim_Alert(600, 300);

    var stringa = "<?=WEB_ROOT;?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

    openWindowSearch(stringa,{width:600, height:300, left:(($(window).width()/2)-300), top:(($(window).height()/2)-150)});
    //valorediritorno = window.showModalDialog(stringa, "", strDim);
}

function cerca_sede()
{
    selectParent = "sede";

	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
}

var id_ufficio_reload = '<?php echo $reload; ?>';
function cerca_comune()
{
    selectParent = "comune";

	var stringa = "<?= WEB_ROOT; ?>/search/ufficio/ricerca_alert_modale.php?richiesta=ricUfficio&tipo_ufficio=<?php echo $tipo; ?>";

	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
}



function cambia_valori(value)
{
	$('#ID_ufficio').val(value.ID);
	$('#CC_id').val(value.CC);
	$('#comune_id').val(value.nome_CC);
	$('#denom_id').val(value.denominazione);

	$('#CC_sede_id').val(value.CC_sede);
	$('#comune_sede_id').val(value.nome_CC_sede);
	$('#prov_id').val(value.provincia);
	$('#cap_id').val(value.cap);
	$('#via').val(value.toponimo);
	if(value.civico=="0")
		value.civico="";
	$('#civico').val(value.civico);
	$('#esponente').val(value.esponente);
	if(value.interno=="0")
		value.interno="";
	$('#interno').val(value.interno);
	$('#dettagli').val(value.dettagli);
	$('#PI_id').val(value.PI);
	$('#tel_id').val(value.tel);
	$('#fax_id').val(value.fax);
	$('#email_id').val(value.mail);
	$('#pec_id').val(value.PEC);
	$('#sito_id').val(value.sito);
	$('#orario_id').val(value.orario);
	$('#modalita_invio').val(value.invio);
}

function reload_ufficio(CC_comune,tipo)
{
	var idCC = "";
	if(CC_comune!==undefined && CC_comune!==null && tipo!==undefined && tipo!==null) idCC = "&CC_Comune="+CC_comune+"&tipo="+tipo;
	else idCC = "&ID=<?php echo $reload; ?>";

	$.ajax({
		url: '<?= WEB_ROOT; ?>/ajax/ajax_ufficio_anagrafico.php?c=<?php echo $c; ?>',
		type: 'POST',
		data: 'ajax=ufficio'+idCC,
		dataType: 'JSON',
		success: function(response){

			if(response["CC"]!="")
				$('#CC_id').val(response["CC"]);
			if(response["Com_Nome"]!="")
				$('#comune_id').val(response["Com_Nome"]);


			$('#denom_id').val(response["Denominazione"]);
			$('#CC_sede_id').val(response["CC_Comune"]);
			$('#comune_sede_id').val(response["Comune"]);
			$('#prov_id').val(response["Provincia"]);
			$('#cap_id').val(response["Cap"]);
			$('#via').val(response["Toponimo"]);
			if(response["Civico"]=="0")
				response["Civico"]="";
			$('#civico').val(response["Civico"]);
			$('#esponente').val(response["Esponente"]);
			if(response["Interno"]=="0")
				response["Interno"]="";
			$('#interno').val(response["Interno"]);
			$('#dettagli').val(response["Dettagli"]);
			$('#PI_id').val(response["Partita_Iva"]);
			$('#tel_id').val(response["Telefono"]);
			$('#fax_id').val(response["Fax"]);
			$('#email_id').val(response["Mail"]);
			$('#pec_id').val(response["PEC"]);
			$('#sito_id').val(response["Sito"]);
			$('#orario_id').val(response["Orario"]);

			if(response["ID"]!="") $('#ID_ufficio').val(response["ID"]);
			else $('#ID_ufficio').val("-1");
			$('#modalita_invio').val(response["Modalita_Invio"]);

			validateForm();
		}

	});

}

function apri_stampe(value)
{
	window.open('<?= WEB_ROOT; ?>/stampe/conferma_indirizzo.php?ID_Atto=153&tipo_richiesta='+value+'&a=<?php echo $a; ?>&c=<?php echo $c; ?>');
}

</script>


<script>
function control_mail()
{
	invio = $('#modalita_invio').val();
	mail = $('#email_id').val();
	PEC = $('#pec_id').val();
	fax = $('#fax_id').val();

	if(invio=="email")
		if(mail=="")
		{
			$('#modalita_invio').val("");
			alert("Attenzione! Email non inserita, impossibile selezionare questa modalita' di invio");
		}

	if(invio=="PEC")
		if(PEC=="")
		{
			$('#modalita_invio').val("");
			alert("Attenzione! PEC non inserita, impossibile selezionare questa modalita' di invio");
		}

	if(invio=="posta")
		if(fax=="")
		{
			$('#modalita_invio').val("");
			alert("Attenzione! Fax non inserito, impossibile selezionare questa modalita' di invio");
		}
}

</script>


<div class="row justify-content-md-center " style="margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor"><?php echo $tipo_uff; ?></span>
	</div>
</div>

<form name=form_ufficio_comune class="form-horizontal validate" id=form_ufficio_comune method=post action="ufficio_comune_salva.php">

<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>

<input type=hidden name=c 			value=<?php echo $c; ?> >
<input type=hidden name=a 			value=<?php echo $a; ?> >
<input type=hidden name=CC			id=CC_id value=""		>
<input type=hidden name=CC_sede		id=CC_sede_id value=""	>
<input type=hidden name=ID_ufficio	id=ID_ufficio value=""	>
<input type=hidden name=tipo		id=tipo value="<?php echo $tipo; ?>"	>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r" title="Clicca sul campo 'Comune' per effettuare la ricerca dell'ufficio di interesse" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=comune id=comune_id value="" ondblclick="/*cerca_comune();*/openOfcanvas('SearchModal',0);">
			</div>
		</div>
	</div>
	<div class="col col-lg-7">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Denominazione</label>
			<div class="col-lg-10">
				<input class="form-control resize vld_req" id=denom_id name=denom value="" >
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sede ufficio</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r" title="Clicca sul campo 'Sede ufficio' per inserire il comune sede dell'ufficio" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=comune_sede id=comune_sede_id value="" ondblclick="/*cerca_sede();*/openOfcanvas('citySearchModal',0);">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r" style="background-color: #97CFDD; border: 2px solid black; width: 50%;" readonly id=prov_id name=prov value="">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Cap</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r" style="background-color: #97CFDD; border: 2px solid black;" readonly name=cap id=cap_id value="">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
				<input id=via class="form-control resize vld_req" name=via type=text value="">
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
				<input type="text" id=civico 	   class="form-control resize vld_intReq"  name="civico"  	value=""  >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input type="text" id=esponente  class="form-control resize vld_esp"   name="esponente" value=""  >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input type="text" id=interno    class="form-control resize vld_int"  name="interno" 	value=""  >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-9">
				<input type="text" id=dettagli   class="form-control resize "   name="dettagli" 	value="">
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
				<input class="form-control resize vld_PI" id=PI_id name=PI value="" >
			</div>
		</div>
	</div>
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=tel_id name=tel value="">
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=fax_id name=fax value="">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=email_id name=email value="" >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=pec_id name=PEC value="" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_Sito" id=sito_id name=sito value="" >
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Orario</label>
			<div class="col-lg-11">
				<textarea class="form-control resize vld_req" style="max-width: 100%;" id=orario_id name=orario rows=3></textarea>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Modalita' invio</label>
			<div class="col-lg-8">
				<select name=modalita_invio id=modalita_invio class="form-control resize" onchange="control_mail();">
					<option></option>
					<option value="posta">Per Posta/Fax</option>
					<option value=email>Ad Email</option>
					<option value=PEC>A PEC</option>
				</select>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" style="display: none;" value="Submit">submit</button>
</div>

</form>

<script type="text/javascript">
	$( window ).load(function() {

		var reload = "<?php echo $reload; ?>";

		if(reload!=null)
			reload_ufficio();
        if("<?=$ID_ufficio ?>" =="nuovo")
            $('#comune_id').attr("onclick","/*cerca_nuovo()*/;openOfcanvas('citySearchModal',1);");
	});
</script>


<?php include(INC."/footer.php"); ?>
