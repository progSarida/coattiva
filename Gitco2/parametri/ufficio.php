<?php
//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include_once(ROOT."/_parameter.php");//dati database
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


$ufficio_ID = $a_enteAdmin["Ufficio_ID"];
//var_dump($a_enteAdmin);

$CC_Comune = $a_enteAdmin["Ufficio_CC_Comune"];
$CC_Comune_SO = $a_enteAdmin["Ufficio_CC_Comune_SO"];

if($CC_Comune == null){
	$query_cc_comune = "SELECT CL.Com_Codice_Catastale FROM comuni_lista AS CL LEFT JOIN province_lista AS PL ON PL.Pro_Codice = CL.Com_Codice_Provincia WHERE CL.Com_Nome = '".$a_enteAdmin["Ufficio_Comune"]."' AND PL.Pro_Sigla = '".$a_enteAdmin["Ufficio_Provincia"]."'";
	$SearchCC = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_cc_comune));
	$CC_Comune = $SearchCC !== null ? $SearchCC["Com_Codice_Catastale"] : null;
}

if($CC_Comune_SO == null){
	$query_cc_comune = "SELECT CL.Com_Codice_Catastale FROM comuni_lista AS CL LEFT JOIN province_lista AS PL ON PL.Pro_Codice = CL.Com_Codice_Provincia WHERE CL.Com_Nome = '".$a_enteAdmin["Ufficio_Comune_SO"]."' AND PL.Pro_Sigla = '".$a_enteAdmin["Ufficio_Provincia_SO"]."'";
	$SearchCC = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_cc_comune));
	$CC_Comune_SO = $SearchCC !== null ? $SearchCC["Com_Codice_Catastale"] : null;
}


$query_flag = "SELECT Com_Cap FROM comuni_lista WHERE Com_Codice_Catastale = '".$CC_Comune."'";
$res_flag_C_NC = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query_flag),"comuni_lista");
$flag_C_NC = $res_flag_C_NC["Com_Cap"];

$query_flag = "SELECT Com_Cap FROM comuni_lista WHERE Com_Codice_Catastale = '".$CC_Comune_SO."'";
$flag_C_NC_SO = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query_flag),"comuni_lista");
$flag_C_NC_SO = $flag_C_NC_SO["Com_Cap"];


$cappato_si_no = strpos($flag_C_NC,"xx") ? 1 : 0;
$cappato_si_no_so = strpos($flag_C_NC_SO,"xx") ? 1 : 0;

$int = $a_enteAdmin["Ufficio_Interno"];
$civ = $a_enteAdmin["Ufficio_Civico"];

if( $ufficio_ID != 0 )
{
	if($int==0)$int="";
	if($civ==0)$civ="";
}

$int_so = $a_enteAdmin["Ufficio_Interno_SO"];
$civ_so = $a_enteAdmin["Ufficio_Civico_SO"];

if( $ufficio_ID != 0 )
{
	if($int_so==0)$int_so="";
	if($civ_so==0)$civ_so="";
}
$disabilita = "";
$sfondo = "";
if($cappato_si_no == 1){
	$disabilita = "readonly";
	$sfondo = "background-color: rgb(153, 204, 255); border: 2px solid black;";
}
$disabilita_so = "";
$sfondo_so = "";
if($cappato_si_no_so == 1){
	$disabilita_so = "readonly";
	$sfondo_so = "background-color: rgb(153, 204, 255); border: 2px solid black;";
}

// Inclusione modale per ricerca comune
include_once(ROOT . "/search_modal/offcanvas/city_offcanvas.php");
include_once(ROOT . "/search_modal/offcanvas/addr_offcanvas.php");
?>


<!-- ********** MODALI AJAX ********** -->
<script>
var selectRif = '';
var addr_c = '';                            // nome comune
var addr = '';
var field_only_for_office = '';
// Modali offcanvas
// Apertura modale
function openOfcanvas(type,rif){
    switch (type){
        case 'citySearchModal':
            // Reset campi input
            $('#city').val("");
            // Reset spazi tabella
            $('#appendTableCity').empty();
            // Apertura modale
            selectRif = rif;
            $('#'+type).modal('show');
            break;
		case 'addrSearchModal':

			selectRif = rif;

			$('#addr_c').val("");
        	$('#addr_g').val("");

			$('#appendTableAddr').empty();

			var flag_cappato = null;
			if(rif == 0){
				addr_c = $('#comune').val();                            // nome comune
				addr = $('#via').val();
				
				$('#addr_c').val(addr) ;
				$('#addr_g').val(addr) ;

				$("#cc_research_addr").val($('#CC_UFF').val());
				flag_cappato = $("#flag_cappato").val();
			}
			else {
				addr_c = $('#comune_so').val();                            // nome comune
				addr = $('#via_so').val();
				
				$('#addr_c').val(addr) ;
				$('#addr_g').val(addr) ;

				$("#cc_research_addr").val($('#CC_SO').val());
				flag_cappato = $("#flag_cappato_so").val();
				field_only_for_office = '_so';
			}

			if(flag_cappato == "1"){
				document.getElementById('addrSearchModalLabel_nc').hidden = true;
				document.getElementById('ins_addr_nc').hidden = true;
				$('#comune_c').val(addr_c);
				document.getElementById('check_cap').checked = true;
				document.getElementById('check_gen').checked = false;
				// Resetta gli hidden se si cambia due volte città di cui una è cappata e l'altra no 
				document.getElementById('checkbox_c').hidden = false;
				document.getElementById('addrSearchModalLabel_c').hidden = false;
				document.getElementById('ins_addr_c').hidden = false;
			}
			else{
				document.getElementById('addrSearchModalLabel_c').hidden = true;
				document.getElementById('checkbox_c').hidden = true;
				document.getElementById('ins_addr_c').hidden = true;
				// Resetta gli hidden se si cambia due volte città di cui una è cappata e l'altra no
				document.getElementById('addrSearchModalLabel_nc').hidden = false;
				document.getElementById('ins_addr_nc').hidden = false;
			}

			$('#'+type).modal('show');
            break;
    }
}
// Inserimento dati selezionati
function initialId(tipo,val){
    switch (tipo){
		case 'city':
			switch(selectRif){
				case 0:
					$('#comune_id').val(val["nome"]);
					$('#prov_id').val(val["prov"]);
					$('#cap_id').val(val["cap"]);
					$('#CC_UFF').val(val["CC_C"]);

					if(val["cap"].includes("xx")/* && $("#flag_cappato").val() !== 1*/){
						$("#via").prop("readonly",true);
						$("#via").css("background-color","rgb(153, 204, 255)");
						$("#via").css("border","2px solid black");

						$("#cap_id").prop("readonly",true);
						$("#cap_id").css("background-color","rgb(153, 204, 255)");
						$("#cap_id").css("border","2px solid black");

						$("#flag_cappato").val("1");
					}
					else{
						$("#via").prop("readonly",false);
						$("#via").css({"background-color" : '',"border" : ''});

						$("#cap_id").prop("readonly",true);
						$("#cap_id").css("background-color","rgb(153, 204, 255)");
						$("#cap_id").css("border","2px solid black");

						$("#flag_cappato").val("0");
					}
					break;
				case 1:
					$('#comune_so_id').val(val["nome"]);
					$('#prov_so_id').val(val["prov"]);
					$('#cap_so_id').val(val["cap"]);
					$('#CC_SO').val(val["CC_C"]);

					if(val["cap"].includes("xx")/* && $("#flag_cappato").val() !== 1*/){
						$("#via_so").prop("readonly",true);
						$("#via_so").css("background-color","rgb(153, 204, 255)");
						$("#via_so").css("border","2px solid black");

						$("#cap_so_id").prop("readonly",true);
						$("#cap_so_id").css("background-color","rgb(153, 204, 255)");
						$("#cap_so_id").css("border","2px solid black");

						$("#flag_cappato_so").val("1");
					}
					else{
						$("#via_so").prop("readonly",false);
						$("#via_so").css({"background-color" : '',"border" : ''});

						$("#cap_so_id").prop("readonly",true);
						$("#cap_so_id").css("background-color","rgb(153, 204, 255)");
						$("#cap_so_id").css("border","2px solid black");

						$("#flag_cappato_so").val("0");
					}
					break;
				default: break;
			}
		case "addr_cap":
			// Non controllo se val è vuoto perchè non può esserlo: abbiamo controllo su risultato vuoto indirizi cappati che nel caso manda a Ricerca generica
			
			switch(selectRif){
				case 0:
				case '0':
					console.log(val);
					$('#cap_id').val(val["cap"]);
					$('#via').val(val["nome_via"]);
					break;
				case 1:
				case '1':
					$('#cap_so_id').val(val["cap"]);
					$('#via_so').val(val["nome_via"]);
					break;
			}

			break;
		// Sostituzione indirizzo
		case "addr_gen":

			switch(selectRif){
				case 0:
				case '0':
					$('#cap').val(val["cap"]);
					$('#via').val(val["nome_via"]);
					break;
				case 1:
				case '1':
					$('#cap_so_id').val(val["cap"]);
					$('#via_so').val(val["nome_via"]);
					break;
			}

                break;
		default: break;
    }
}

function enabledStreetCap(endId){
	if($("#flag_cappato").val() == "1"){
		$("#via"+endId).prop("readonly",false);
		$("#cap"+endId+"_id").prop("readonly",false);
		$("#via"+endId).css({"background-color" : '',"border" : ''});
		$("#cap"+endId+"_id").css({"background-color"  : '',"border"  : ''});
	}
	else ShowAlert(2,"Comune non cappato, impossibile abilitare i campi!");
}

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}

function callParent(valorediritorno){
    switch(selectParent){
        case "comune":


            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                $('#comune_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(valorediritorno.cap);
                $('#CC_id').val(valorediritorno.CC);
            }

            break;
    }

}

var selectParent = "";
function cerca_comune()
{
    selectParent = "comune";
	//strDim = Dim_Alert(600, 300);

	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});

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

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Ufficio.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Ufficio</b>");
    $("#helpModal").modal('show');

}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "gestore.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "stemma.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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

<form class="form-horizontal validate" name=form_ufficio id=form_ufficio method=post action="ufficio_salva.php">

<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=ufficio_id  value="<?php echo $ufficio_ID; ?>" >

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $a_enteAdmin["CC"]; ?>">
<input type=hidden name=CC_UFF	id=CC_UFF value="<?=$CC_Comune?>">
<input type=hidden name=CC_SO	id=CC_SO value="<?=$CC_Comune_SO?>">
<input type=hidden name=flag_cappato id=flag_cappato value="<?=$cappato_si_no?>" >
<input type=hidden name=flag_cappato_so id=flag_cappato_so value="<?=$cappato_si_no_so?>" >



<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Denominazione</label>
			<div class="col-lg-8">
					<input style="width: 80%;" class="text_left form-control vld_req resize" id=denom_id name=denom size=50 value="<?php echo $a_enteAdmin["Ufficio_Denominazione"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
					<input style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;"  class="sfondo_azzurro text_left form-control resize" readonly name=comune id=comune_id value="<?php echo $a_enteAdmin["Ufficio_Comune"]; ?>" size=15 ondblclick="/*cerca_comune();*/openOfcanvas('citySearchModal',0);">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-8">
					<input style="width: 50%; background-color: #97CFDD; border: 2px solid black;" class="sfondo_azzurro text_left form-control resize" readonly id=prov_id name=prov value="<?php echo $a_enteAdmin["Ufficio_Provincia"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
			<div class="col-lg-8">
					<input style="width: 80%; background-color: #97CFDD; border: 2px solid black;" class="sfondo_azzurro text_center form-control resize" id=cap_id name=cap size=4 value="<?php echo $a_enteAdmin["Ufficio_Cap"]; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
					<!-- <input style="width: 100%;<?= $sfondo; ?>" id=via class="text_left form-control vld_req resize" onchange="enabledStreetCap('');" ondblclick="openOfcanvas('addrSearchModal',0);" <?= $disabilita;?> name=via type=text value="<?php echo $a_enteAdmin["Ufficio_Via"]; ?>"> -->
					<input style="width: 100%;<?= $sfondo; ?>" id=via class="text_left form-control vld_req resize" onchange="enabledStreetCap('');" ondblclick="openOfcanvas('addrSearchModal',0);" name=via type=text value="<?php echo $a_enteAdmin["Ufficio_Via"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=civico class="text_right form-control resize vld_intReq"  name="civico"  	value="<?php echo $civ; ?>"  size=2 >
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=esponente  class="text_left form-control vld_esp resize"   name="esponente" value="<?php echo $a_enteAdmin["Ufficio_Esponente"]; ?>"  size=2 >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=interno class="text_right form-control resize vld_int"  name="interno" 	value="<?php echo $int; ?>"  size=2 >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
					<input style="width: 100%;" type="text" id=dettagli   class="text_left form-control resize"   name="dettagli" 	value="<?php echo $a_enteAdmin["Ufficio_Dettagli"]; ?>"  size=20>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

	<div class="row" style="margin-top: 1%;">
		<div class="col col-md-auto text_center">
				<p class="titolo font16">Dati indirizzo sede operativa</p>
		</div>
	</div>

	<div class="row" style="margin-top: 1%;">
		<div class="col col-lg-3 col-lg-offset-1" >
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
				<div class="col-lg-8">
					 <input style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" class=" text_left form-control resize" readonly name=comune_so id=comune_so_id value="<?php echo $a_enteAdmin["Ufficio_Comune_SO"]; ?>" size=15 ondblclick="/*cerca_comune();*/openOfcanvas('citySearchModal',1);">
	      </div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Provincia</label>
				<div class="col-lg-8 ">
					 <input style="width: 50%; background-color: #97CFDD; border: 2px solid black;" class=" text_left form-control resize" readonly id=prov_so_id name=prov_so size=1 value="<?php echo $a_enteAdmin["Ufficio_Provincia_SO"]; ?>">
	      </div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">CAP</label>
				<div class="col-lg-8">
					 <input style="width: 60%; background-color: #97CFDD; border: 2px solid black;"  class=" text_center form-control resize" readonly id=cap_so_id name=cap_so size=4 value="<?php echo $a_enteAdmin["Ufficio_Cap_SO"]; ?>">
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
				<div class="col-lg-8">
					 <input id=via_so style="<?=$sfondo_so?>" onchange="enabledStreetCap('_so');" class="text_left form-control vld_req resize" <?= $disabilita_so;?> name=via_so type=text ondblclick="openOfcanvas('addrSearchModal',1);" value="<?php echo $a_enteAdmin["Ufficio_Via_SO"]; ?>">
			  </div>
			</div>
		</div>
		<div class="col col-lg-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Civ.</label>
				<div class="col-lg-8">
					 <input type="text" id=civico class="text_right form-control resize vld_intReq"  name="civico_so"  	value="<?php echo $civ_so; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-2">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Esp.</label>
				<div class="col-lg-8">
					 <input type="text" id=esponente  class="text_left form-control vld_esp resize" style="width: 50%;" name="esponente_so" value="<?php echo $a_enteAdmin["Ufficio_Esponente_SO"]; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Int.</label>
				<div class="col-lg-8">
					 <input type="text" id=interno class="text_right form-control resize vld_int"  name="interno_so" 	value="<?php echo $int_so; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Dettagli</label>
				<div class="col-lg-8">
					 <input type="text" id=dettagli class="text_left form-control resize" name="dettagli_so" value="<?php echo $a_enteAdmin["Ufficio_Dettagli_SO"]; ?>">
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
					<input style="width: 100%;" class="text_left form-control vld_tel resize" id=tel_id name=tel value="<?php echo $a_enteAdmin["Ufficio_Telefono"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
					<input style="width: 100%;" class="text_left form-control vld_tel resize" id=fax_id name=fax value="<?php echo $a_enteAdmin["Ufficio_Fax"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva</label>
			<div class="col-lg-8">
					<input style="width: 100%;" class="text_left form-control vld_tel resize" id=PI_id name=PI value="<?php echo $a_enteAdmin["Ufficio_PI"]; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input style="width: 100%;" class="text_left form-control resize vld_email" id=email_id name=email size=18 value="<?php echo $a_enteAdmin["Ufficio_Mail"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
			<div class="col-lg-8">
				<input style="width: 100%;" class="text_left form-control resize vld_email" id=pec_id name=PEC size=18 value="<?php echo $a_enteAdmin["Ufficio_PEC"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label  class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input style="width: 100%;" class="text_left form-control vld_Sito resize" id=sito_id name=sito size=16 value="<?php echo $a_enteAdmin["Ufficio_Sito"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label  class="col-lg-1 control-label resize" style="text-align: left;">Orario</label>
			<div class="col-lg-11">
				<textarea style="max-width: 100%;" class="text_left form-control resize" id=orario_id name=orario ><?php echo $a_enteAdmin["Ufficio_Orario"]; ?></textarea>
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
