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
$tipo_ufficio = "tribunale";
$ufficio_giud = "Tribunale";
$tipo_ufficio_collegato = "istituto";
$ufficio_giud_collegato = "Istituto vendite giudiziarie";

$nome_com = $a_enteAdmin["Denominazione"];
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$ComunePrincipale = "";
$ComunePrincipaleCC = "";

if($cls_help->getVar("ComuneID")!=null && $cls_help->getVar("CC")!= null)
{
	$ComunePrincipale = $cls_help->getVar("ComuneID");
	$ComunePrincipaleCC = $cls_help->getVar("CC");
}

function options_selezione( $array )
{
	$options = "";
	for($i=0;$i<count($array);$i++)
	{
		$options.= "<option value='".$array[$i]['ID']."' label=\"".$array[$i]['Descrizione']."\">".$array[$i]['Sigla']."</option>";
	}

	return $options;
}

$QUERY = $cls_param->Get_Query_Banca();

$options_individuale = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["I"])));
$options_persone = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["P"])));
$options_capitale = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Cap"])));
$options_cooperativa = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Coop"])));
$options_consortile = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Cons"])));
$options_ente = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Ente"])));

?>



<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	if($('#ID_uff').val()==0)
		control_salva = submit_buttons('Insert');
    else
    	control_salva = submit_buttons('Update');

	AddAttribute();

	if(control_salva && validateForm())
			$("#btnSub").trigger("click");
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control_salva = submit_buttons('Delete');
	if(control_salva)
			$("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "tribunale_esterno.php?"+stringaPHP;
	   	top.location.href = stringa;
}


switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Tribunale_IstitutoVenditeGiudiziarie.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Tribunale / Istituto Vendite Giudiziarie</b>");
    $("#helpModal").modal('show');

}
//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

</script>

<!-- Inclusione modale per ricerca comune -->
<?php include_once (ROOT."/search_modal/offcanvas/city_offcanvas.php"); ?>

<!-- ********** MODALI AJAX ********** -->
<script>
// Variabili
// Modali offcanvas
function openOfcanvas(type,rif){
    selectRif = '';
    switch (type){
        case 'citySearchModal':                                                 // ricerca comune
            // Settaggio tipo di inserimento
            switch (rif){
                case 0:
                    selectRif = 'comune';
                    break;
                case 1:
                    if($('#comune_id').val() == "")
                    {
                        alert("Prima e' necessario inserire il comune a cui associare il tribunale [ Primo campo centrale in alto ]!");
                        return;
                    }
                    else{
                        selectRif = 'tribunale';
                    }
                    break;
                case 2:
                    if($('#comune_id').val() == ""){
                        alert("Prima e' necessario inserire il comune a cui associare il tribunale [ Primo campo centrale in alto ]!");
                        return;
                    }
                    else if($('#comune_id').val() != "" && $('#ufficio_sede').val() == ""){
                        alert("Prima e' necessario inserire la sede 'tribunale' a cui associare la sede 'istituto'!");
                        return;
                    }
                    else{
                        selectRif = 'istituto';
                    }
                    break;
            }
            if(selectRif != ""){
                // Reset campi input
                $('#city').val("");
                // Reset spazi tabella
                $('#appendTableCity').empty();
                // Apertura modale
                $('#citySearchModal').modal('show');
            }
            break;
    }
}

// Funzione jquery che svuota tutti i campi di un form
jQuery.fn.clear = function() {
    var $form = $(this);
    $form.find('input:text, input:password, input:file, textarea').val('');
    $form.find('select option:selected').removeAttr('selected');
    $form.find('input:checkbox, input:radio').removeAttr('checked');
    return this;
};

function initialId(tipo,val){
    switch (tipo){
        case "city":
            if ( selectRif == 'comune' ){                                       // comune
                //$('#form_ufficio_giud').clear();
                //$(":input:not([type=hidden])").val('');
                $('input:not(:hidden)').val('');
                caricamento_ufficio_esistente = "0";
                $("#comune_id").val(val['nome']);
                $("#CC_id").val(val['CC_C']);

                let event = new Event("change");
                document.getElementById("comune_id").dispatchEvent(event);
                reload_ufficio(val['CC_C'],1);
            }
            else if ( selectRif == 'tribunale' ){                               // sede tribunale
                control_ufficio = reload_ufficio(val['CC_C'],2);

                if( caricamento_ufficio_esistente=="0" ){
                    cap = val['cap'];
                    for(var contatore=0;contatore<2;contatore++)
                    {
                        cap = cap.replace("x", "0");
                    }

                    $('#ufficio_sede').val(val['nome']);
                    $('#prov_id').val(val['prov']);
                    $('#cap_id').val(cap);
                    $('#CC_uff').val(val['CC_C']);

                    let event = new Event("change");
                    document.getElementById("ufficio_sede").dispatchEvent(event);
                    document.getElementById("prov_id").dispatchEvent(event);
                    document.getElementById("cap_id").dispatchEvent(event);
                }
            }
            else if ( selectRif == 'istituto' ){
                cap = val['cap'];
                for(var contatore=0;contatore<2;contatore++)
                {
                    cap = cap.replace("x", "0");
                }

                $('#ufficio_sede2').val(val['nome']);
                $('#prov_id2').val(val['prov']);
                $('#cap_id2').val(cap);
                $('#CC_uff_collegato').val(val['CC_C']);

                let event = new Event("change");
                document.getElementById("ufficio_sede2").dispatchEvent(event);
                document.getElementById("prov_id2").dispatchEvent(event);
                document.getElementById("cap_id2").dispatchEvent(event);
            }
    }
}

$( document ).ready(function() {

		if('<?php echo $ComunePrincipale;?>' != "" && '<?php echo $ComunePrincipaleCC;?>' != "")
			resetPage('<?php echo $ComunePrincipale;?>','<?php echo $ComunePrincipaleCC;?>');
});

function AddAttribute()
{
	if($("#denominazione_id2").val() != "")
	{
		$("#ufficio_sede2").removeClass("validateCustom vld_Custom_r").addClass("validateCustom vld_Custom_r");
		$("#prov_id2").removeClass("validateCustom vld_Custom_r").addClass("validateCustom vld_Custom_r");
		$("#cap_id2").removeClass("validateCustom vld_Custom_r vld_Custom_n").addClass("validateCustom vld_Custom_r vld_Custom_n");
		$("#via2").removeClass("validateCustom vld_Custom_r").addClass("validateCustom vld_Custom_r");
		$("#civico2").removeClass("validateCustom vld_Custom_r vld_Custom_n").addClass("validateCustom vld_Custom_r vld_Custom_n");

	}
	else{
		$("#ufficio_sede2").removeClass("vld_Custom_r");
		$("#ufficio_sede2").prop('required',false);

		$("#prov_id2").removeClass("vld_Custom_r");
		$("#prov_id2").prop('required',false);

		$("#cap_id2").removeClass("vld_Custom_r vld_Custom_n");
		$("#cap_id2").prop('required',false);
		$("#cap_id2").removeAttr("pattern");

		$("#via2").removeClass("vld_Custom_r");
		$("#via2").prop('required',false);

		$("#civico2").removeClass("vld_Custom_r vld_Custom_n");
		$("#civico2").prop('required',false);
		$("#civico2").removeAttr("pattern");

	}
	validateForm();
}

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}

var caricamento_ufficio_esistente = "0";

function callParent(valorediritorno){
    switch(selectParent){
        case "comune":

            if( valorediritorno!=null && valorediritorno!=undefined )
            {

                if(selectRif=="<?php echo $tipo_ufficio; ?>")
                {
                    control_ufficio = reload_ufficio(valorediritorno.CC,2);

                    if( caricamento_ufficio_esistente=="0" )
                    {
                        cap = valorediritorno.cap;
                        for(var contatore=0;contatore<2;contatore++)
                        {
                            cap = cap.replace("x", "0");
                        }

                        $('#ufficio_sede').val(valorediritorno.comune);
                        $('#prov_id').val(valorediritorno.prov_sigla);
                        $('#cap_id').val(cap);
                        $('#CC_uff').val(valorediritorno.CC);

                        let event = new Event("change");
                        document.getElementById("ufficio_sede").dispatchEvent(event);
                        document.getElementById("prov_id").dispatchEvent(event);
                        document.getElementById("cap_id").dispatchEvent(event);
                    }

                }
                else if(selectRif=="<?php echo $tipo_ufficio_collegato; ?>")
                {

                    cap = valorediritorno.cap;
                    for(var contatore=0;contatore<2;contatore++)
                    {
                        cap = cap.replace("x", "0");
                    }

                    $('#ufficio_sede2').val(valorediritorno.comune);
                    $('#prov_id2').val(valorediritorno.prov_sigla);
                    $('#cap_id2').val(cap);
                    $('#CC_uff_collegato').val(valorediritorno.CC);

                    let event = new Event("change");
                    document.getElementById("ufficio_sede2").dispatchEvent(event);
                    document.getElementById("prov_id2").dispatchEvent(event);
                    document.getElementById("cap_id2").dispatchEvent(event);

                }
                else if(selectRif=="comune")
                {
                    caricamento_ufficio_esistente = "0";
										$("#comune_id").val(valorediritorno.comune);
										$("#CC_id").val(valorediritorno.CC);

                    let event = new Event("change");
                    document.getElementById("comune_id").dispatchEvent(event);
                    reload_ufficio(valorediritorno.CC,1);
                }

            }

            break;
    }

}

function resetPage(comune,cc_id)
{
	$("#comune_id").val(comune);
	$("#CC_id").val(cc_id);
	reload_ufficio(cc_id,1);
	return;
}

var selectParent = "";
var selectRif = "";
function cerca_comune(value)
{
    selectRif = value;
    selectParent = "comune";
	if($('#comune_id').val()=="" && value!="comune")
	{
		alert("Prima e' necessario inserire il comune a cui associare il tribunale [ Primo campo centrale in alto ]!");
		return;
	}
	else if(value=="<?php echo $tipo_ufficio_collegato; ?>")
	{
		if($('#ufficio_sede').val()=="")
		{
			alert("Prima e' necessario inserire la sede '<?php echo $tipo_ufficio; ?>' a cui associare la sede '<?php echo $tipo_ufficio_collegato; ?>'!");
			return;
		}
	}

	//strDim = Dim_Alert(600, 300);

	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

 	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
}

function reload_ufficio(CC_ufficio,flag)
{
	var flagReload = "&reload=0";
	if(flag===2) flagReload = "&reload=1";

	$.ajax({
		url: '<?php echo WEB_ROOT; ?>/ajax/ajax_parametri.php?c=<?php echo $c; ?>',
		type: 'POST',
		data: 'ajax='+'uff_giudiziario'+'&CC_ufficio='+CC_ufficio+flagReload,
		dataType: 'JSON',
		success: function(response){

			if(flag===2 && response["CC_Tribunale"]!="")
				caricamento_ufficio_esistente = "1";

			$('#sezione_id').val(response["Sezione_Tribunale"]);
			$('#CC_uff').val(response["CC_Tribunale"]);
			$('#ufficio_sede').val(response["Comune_Tribunale"]);
			$('#prov_id').val(response["Provincia_Tribunale"]);
			$('#cap_id').val(response["Cap_Tribunale"]);
			$('#via').val(response["Toponimo_Tribunale"]);
			if(response["Civico_Tribunale"]=="0")
				response["Civico_Tribunale"]="";
			$('#civico').val(response["Civico_Tribunale"]);
			$('#esponente').val(response["Esponente_Tribunale"]);
			if(response["Interno_Tribunale"]=="0")
				response["Interno_Tribunale"]="";
			$('#interno').val(response["Interno_Tribunale"]);
			$('#dettagli').val(response["Dettagli_Tribunale"]);
			$('#tel_id').val(response["Telefono_Tribunale"]);
			$('#fax_id').val(response["Fax_Tribunale"]);
			$('#email_id').val(response["Mail_Tribunale"]);
			$('#pec_id').val(response["PEC_Tribunale"]);
			$('#sito_id').val(response["Sito_Tribunale"]);
			if(flag===1)
			{
				$('#ID_uff').val(response["Id_Tribunale"]);
			}


			$('#CC_uff_collegato').val(response["CC_IVG"]);
   			$('#ufficio_sede2').val(response["Comune_IVG"]);
   			$('#prov_id2').val(response["Provincia_IVG"]);
   			$('#cap_id2').val(response["Cap_IVG"]);
   			$('#via2').val(response["Toponimo_IVG"]);
   			if(response["Civico_IVG"]=="0")
   				response["Civico_IVG"]="";
   			$('#civico2').val(response["Civico_IVG"]);
   			$('#esponente2').val(response["Esponente_IVG"]);
   			if(response["Interno_IVG"]=="0")
   				response["Interno_IVG"]="";
   			$('#interno2').val(response["Interno_IVG"]);
   			$('#dettagli2').val(response["Dettagli_IVG"]);
   			$('#tel_id2').val(response["Telefono_IVG"]);
   			$('#fax_id2').val(response["Fax_IVG"]);
   			$('#email_id2').val(response["Mail_IVG"]);
   			$('#pec_id2').val(response["PEC_IVG"]);
   			$('#sito_id2').val(response["Sito_IVG"]);
   			$('#denominazione_id2').val(response["Denominazione_IVG"]);

				document.getElementById("denominazione_id2").dispatchEvent(new Event("change"));

   			$('#forma_giuridica_id2').val(response["Forma_Giuridica_IVG"]);

   			cambia_title('forma_giuridica_id2');

   			if(caricamento_ufficio_esistente=="1")
   			{
	   			alert("Dati caricati da archivio. Se necessario modificarli e salvare per rendere effettivo l'inserimento!");
	   			$('#avvisoSalva').show();
   			}
   			else
				{
					if(flag===1)
						$('#ID_uff_collegato').val(response["Id_IVG"]);
				}

		}

	});

}


function cambia_title(value)
{
	testo = $('#'+value+ ' option:selected').attr('label');
	$('#'+value).attr('title',testo);
}

</script>

<div class="row justify-content-md-center " style="margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor"><?php echo $ufficio_giud." / ".$ufficio_giud_collegato; ?></span>
	</div>
</div>

<form name=form_ufficio_giud class="form-horizontal validate" id=form_ufficio_giud method=post action="tribunale_esterno_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >
<input type=hidden name=c 	value=<?php echo $c; ?> >
<input type=hidden name=a 	value=<?php echo $a; ?> >

<input type=hidden name=CC					id=CC_id 				value="">

<input type=hidden name=CC_uff				id=CC_uff 				value="">
<input type=hidden name=ID_uff 				id=ID_uff 				value="" >
<input type=hidden name=tipo_uff 			id=tipo_uff 			value="<?php echo $tipo_ufficio; ?>">

<input type=hidden name=CC_uff_collegato 	id=CC_uff_collegato 	value="">
<input type=hidden name=ID_uff_collegato 	id=ID_uff_collegato 	value="" >
<input type=hidden name=tipo_uff_collegato 	id=tipo_uff_collegato	value="<?php echo $tipo_ufficio_collegato; ?>">

<div style="border-top: 2px solid #B0BBE8; width: 40%; margin-left: 30%;margin-bottom: 1%;"></div>

<div class="row" >
	<div class="col col-lg-4 col-lg-offset-4">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-10">
				<input class="form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" title="Seleziona un comune" readonly name=comune id=comune_id value="" ondblclick="/*cerca_comune('comune');*/openOfcanvas('citySearchModal',0);" tabindex=1>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 40%; margin-left: 30%;margin-bottom: 2%;"></div>

<div class="row justify-content-md-center " style="margin-bottom: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor"><?php echo $ufficio_giud; ?></span>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sede <?php echo $tipo_ufficio; ?></label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" title="Associa al comune l'/il <?php echo $tipo_ufficio; ?> di riferimento" readonly tabindex=2 name=ufficio_sede id=ufficio_sede value="" ondblclick="/*cerca_comune('<?php echo $tipo_ufficio; ?>');*/openOfcanvas('citySearchModal',1);">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-6 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-6">
				<input class="form-control resize validateCustom vld_Custom_r" style="background-color: #97CFDD; border: 2px solid black;" readonly tabindex=3 id=prov_id name=prov value="">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r vld_Custom_n" tabindex=3 id=cap_id name=cap value="">
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sezione</label>
			<div class="col-lg-8">
				<input class="form-control resize" id=sezione_id name=sezione value="" tabindex=5>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
				<input id=via class="form-control resize vld_req" name=via type=text value="" tabindex=6 >
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-6 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-6">
				<input type="text" id=civico class="form-control resize" name="civico" value="" tabindex=7>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input type="text" id=esponente  class="form-control resize vld_esp"   name="esponente" value=""  size=2 tabindex=8>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input type="text" id=interno    class="form-control resize vld_int"  name="interno" 	value=""  size=2 tabindex=9>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
				<input type="text" id=dettagli   class="form-control resize"   name="dettagli" 	value="" tabindex=10>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=tel_id name=tel value="" tabindex=11>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=fax_id name=fax size=18 value="" ondblclick="controllaCampi();" tabindex=12>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=email_id name=email value="" tabindex=13>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=pec_id name=PEC value="" tabindex=14>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_Sito" id=sito_id name=sito value="" tabindex=15>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 2%;"></div>

<div class="row justify-content-md-center " style="margin-bottom: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor"><?php echo $ufficio_giud_collegato; ?></span>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Denominazione</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_req" name=denominazione2 id=denominazione_id2 value="" tabindex=16 >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Forma giuridica</label>
			<div class="col-lg-8">
				<select id=forma_giuridica_id2 class="form-control resize" name=forma_giuridica2 onchange="cambia_title('forma_giuridica_id2');" tabindex=17>
					<option></option>
					<optgroup label="Impresa individuale"><?php echo $options_individuale; ?></optgroup>
					<optgroup label="Societa' di persone"><?php echo $options_persone; ?></optgroup>
					<optgroup label="Societa' di capitale"><?php echo $options_capitale; ?></optgroup>
					<optgroup label="Societa' cooperativa"><?php echo $options_cooperativa; ?></optgroup>
					<optgroup label="Societa' consortile"><?php echo $options_consortile; ?></optgroup>
					<optgroup label="Ente"><?php echo $options_ente; ?></optgroup>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sede <?php echo $tipo_ufficio_collegato; ?></label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" title="Associa al <?php echo $tipo_ufficio; ?> l'/il <?php echo $tipo_ufficio_collegato; ?> di riferimento" readonly tabindex=18 name=ufficio_sede2 id=ufficio_sede2 value="" ondblclick="/*cerca_comune('<?php echo $tipo_ufficio_collegato; ?>');*/openOfcanvas('citySearchModal',2);">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-6 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-6">
				<input class="form-control resize" style="background-color: #97CFDD; border: 2px solid black;" readonly tabindex=19 id=prov_id2 name=prov2 value="">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
			<div class="col-lg-8">
				<input class="form-control resize" tabindex=20 id=cap_id2 name=cap2 size=4 value="">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
				<input id=via2 class="form-control resize" name=via2 type=text value="" tabindex=21 >
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-6 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-6">
				<input type="text" id=civico2 class="form-control resize" name="civico2" value="" size=2 tabindex=22 onchange="validateForm(this)">
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input type="text" id=esponente2  class="form-control resize vld_esp"   name="esponente2" value=""  size=2 tabindex=23>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input type="text" id=interno2    class="form-control resize vld_int"  name="interno2" 	value=""  size=2 tabindex=24>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
				<input type="text" id=dettagli2   class="form-control resize"   name="dettagli2" 	value="" tabindex=25>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=tel_id2 name=tel2 class="width100" value="" tabindex=26>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=fax_id2 name=fax2 size=18 value="" ondblclick="controllaCampi();" tabindex=27>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=email_id2 name=email2 value="" tabindex=28>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=pec_id2 name=PEC2 size=18 value="" tabindex=29>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_Sito" id=sito_id2 name=sito2 value="" tabindex=30>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-md-center " id=avvisoSalva style="margin-top: 2%;">
	<div class="col col-md-auto text_center">
			<span style="color: red;">SALVARE I DATI PER RENDERE EFFETTIVO L'INSERIMENTO (F3) O ANNULLARE (F5)</span>
	</div>
</div>


<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>


</form>

<script type="text/javascript">

	$( window ).load(function() {
		$('#avvisoSalva').hide();

		$("#denominazione_id2").on(' change paste input keyup',function (){//propertychange click keyup
			AddAttribute();
		});
	});
</script>

<?php include(INC."/footer.php"); ?>
