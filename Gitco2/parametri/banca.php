<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
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
$id_sede = $cls_help->getVar('id_sede');

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$QUERY = $cls_param->Get_Query_Banca($id_sede, "*****");

$a_param = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["query"]),"banca");
$a_paramNextS = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["next_S"]),"banca");
$a_paramPrevS = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["prev_S"]),"banca");


$id_sede = $a_param["ID"];
$denominazione = $a_param["Denominazione"];
$int = $a_param["Interno"];
$civ = $a_param["Civico"];
$forma_giuridica = $a_param["Forma_Giuridica"];
$ID_Collegamento = $a_param["ID_Collegamento"];
$selectDis = $a_param["disabled"] == 1 ? "checked" : "";


if( $int==0 ) $int="";
if( $civ==0 ) $civ="";

function options_selezione( $array )
{
	$options = "";
	for($i=0;$i<count($array);$i++)
	{
	$options.= "<option value='".$array[$i]['ID']."'>".$array[$i]['Sigla']." - ".$array[$i]['Descrizione']."</option>";
	}

	return $options;
}

$options_individuale = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["I"])));
$options_persone = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["P"])));
$options_capitale = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Cap"])));
$options_cooperativa = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Coop"])));
$options_consortile = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Cons"])));
$options_ente = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Ente"])));

if($id_sede == null) $query = "SELECT *, '' AS select_reg_bank FROM regioni_lista WHERE CAST(Reg_Codice AS SIGNED) < 21 ORDER BY Reg_Nome ASC";
else $query = "SELECT RL.*, IF(BR.banca_id IS NOT NULL AND BR.reg_codice IS NOT NULL, 'selected','') AS select_reg_bank FROM regioni_lista AS RL LEFT JOIN banca_regione AS BR ON BR.banca_id = ".$id_sede." AND BR.reg_codice = RL.Reg_Codice WHERE CAST(RL.Reg_Codice AS SIGNED) < 21 ORDER BY RL.Reg_Nome ASC";
$resultReg = $cls_db->getResults($cls_db->ExecuteQuery($query));

$optReg = "";

foreach($resultReg as $key => $value){
	$optReg .= "<option value='".$value["Reg_Codice"]."' ".$value["select_reg_bank"].">".$value["Reg_Nome"]."</option>";
}

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script type="text/javascript">

    var next_tipo = "<?php echo $a_paramNextS["ID"]; ?>";
    var prev_tipo = "<?php echo $a_paramPrevS["ID"];/*$sede->Prev_Sede;*/ ?>";

//F3
switchMenuImg("F3");
F3_button = function()
{
	/*campi = controllaCampi();

	if(campi)
	{*/
		control_salva = submit_buttons('Salva');

		if(control_salva && validateForm())
        {
            $("#btnSub").trigger("click");
        }

	//}
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
	stringa = "banca.php?"+stringaPHP;
	   	top.location.href = stringa;
}

//F6
switchMenuImg("F6");
F6_button = function()
{
    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    stringa = "banca.php?"+stringaPHP;
    top.location.href = stringa;
}

//F7
switchMenuImg("F7");
F7_button = function()
{

    location.href = "banca.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_sede="+prev_tipo;

}

//F8
switchMenuImg("F8");
F8_button = function()
{

    location.href = "banca.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_sede="+next_tipo;

}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Sedi_Banche.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Sedi Banche</b>");
    $("#helpModal").modal('show');

}

//F11-F12 sono nel menu'
</script>

<!-- Inclusione modale per ricerca comune -->
<?php include_once (ROOT."/search_modal/offcanvas/city_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca banca -->
<?php include_once (ROOT."/search_modal/offcanvas/bank_offcanvas.php"); ?>

<script>
//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

var selectRif = '';
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
            $('#citySearchModal').modal('show');
            break;
        case 'bankSearchModalH':                                        // ricerca solo sede
            // Reset campi input
            $('#bank_n').val("");
            // Gestione radio
            $('#bank_headq').prop('checked', true);
            $('#bank_branch').prop('checked', false);
            $('#bank_branch').attr("disabled", true);                   // blocca radio filiale
            // Reset spazi tabella
            $('#appendTableBank').empty();
            // Apertura modale
            selectRif = rif;
            $('#bankSearchModal').modal('show');
            break;
    }
}
// Inserimento dati selezionati
function initialId(tipo,val){
    switch (tipo){
        case 'city':
            cap = val['cap'];
            for(var contatore=0;contatore<2;contatore++)
            {
                cap = cap.replace("x", "0");
            }

            $('#comune_id').val(val["nome"]);
            $('#prov_id').val(val["prov"]);
            $('#cap_id').val(cap);
            $('#CC_id').val(val["CC_C"]);

            let event = new Event("change");
            document.getElementById("comune_id").dispatchEvent(event);
            document.getElementById("prov_id").dispatchEvent(event);
            break;
        case 'bank_headq':
        case 'bank_branch':
            if(val['Tipo_Banca'] == "sede")
            {
                id_sede = val['ID'];
            }
            else
            {
                alert("Impossibile caricare la filiale! In questa pagina e' permessa l'esclusiva gestione delle sedi.")
            }

            stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            stringa = "<?= WEB_ROOT;?>/parametri/banca.php?"+stringaPHP+"&id_sede="+id_sede;
            top.location.href = stringa;
            break;
    }
}

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}

function callParent(valorediritorno){
    //alert("callParent");
    switch(selectParent){
        case "comune":

            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                cap = valorediritorno.cap;
                for(var contatore=0;contatore<2;contatore++)
                {
                    cap = cap.replace("x", "0");
                }

                $('#comune_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(cap);
                $('#CC_id').val(valorediritorno.CC);

                let event = new Event("change");
                document.getElementById("comune_id").dispatchEvent(event);
                document.getElementById("prov_id").dispatchEvent(event);
            }

            break;
        case "banca":
            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                if(valorediritorno.Tipo_banca == "sede")
                {
                    id_sede = valorediritorno.ID;
                }
                else
                {
                    alert("Impossibile caricare la filiale! In questa pagina e' permessa l'esclusiva gestione delle sedi.")
                }

                stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                stringa = "<?= WEB_ROOT;?>/parametri/banca.php?"+stringaPHP+"&id_sede="+id_sede;
                top.location.href = stringa;
            }

            break;
    }

}



var selectParent = "";
function cerca_comune()
{
    selectParent = "comune";

	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
}

function ricerca_banca(value)
{
    selectParent = "banca";

	var stringa = "<?= WEB_ROOT; ?>/search/banche/ricerca_banche.php?richiesta=singola&a=<?php echo $a;?>&c=*****";
	if(value=="filiale")
		stringa+="&tipo=filiale";
	else if(value=="sede")
		stringa+="&tipo=sede";

		openWindowSearch(stringa,{width:1200, height:400, left:(($(window).width()/2)-600), top:(($(window).height()/2)-200)});
}

function controllaCampi ()
{
	pattern_numeri = /[^0-9]/;

	var cerca_denom = $('#cerca_denom').val();
	var pec = $('#pec_id').val();
	var comune_id = $('#comune_id').val();
	var prov_id = $('#prov_id').val();
	var cap_id = $('#cap_id').val();
	var via = $('#via').val();
	var civico = $('#civico').val();
	var partita_iva = $('#PI_id').val();
	var password = $('#pass_id').val();

	cerca_denom = obbligatorio(cerca_denom,"Denominazione");	if( cerca_denom!=true )		return false;

	control_pi = obbligatorio(partita_iva,"Partita Iva");		if( control_pi!=true )		return false;
	control_pi = partita_iva.match(pattern_numeri);
	if(control_pi != null || partita_iva.length != 11)
	{
		alert("La Partita Iva della ditta non è stata inserita correttamente.");
		return false;
	}

	comune_id = obbligatorio(comune_id,"Comune");				if( comune_id!=true )		return false;
	prov_id = obbligatorio(prov_id,"Provincia");				if( prov_id!=true )			return false;
	cap_id = obbligatorio(cap_id,"CAP");						if( cap_id!=true )			return false;
	via = obbligatorio(via,"Via");								if( via!=true )				return false;
	civico = obbligatorio(civico,"Civico");						if( civico!=true )			return false;

	password = obbligatorio(password,"Password");				if( password!=true )		return false;

	obbl_pec = obbligatorio(pec,"PEC");				if( obbl_pec!=true )		return false;
	control_pec = verifica_mail(pec,"PEC");			if( control_pec!=true )		return false;

	return true;
}

function sede_banca()
{
	if($('#tipo_banca').val()=="sede")
	{
		$('#filiale_sede').hide();
		$('#ID_Collegamento').val('');
		$('#banca_sede').text('');
	}
	else
		$('#filiale_sede').show();
}

function cambia_title(value)
{
	testo = $('#'+value+ ' option:selected').text();
	$('#'+value).attr('title',testo);
}


</script>

<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>-->

<div class="row justify-content-md-center " style="margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Sede banca</span>
	</div>
</div>

<p style="color: #0a53be;text-align: center;font-weight: bold;"><i style="color:blue;" class="fa fa-info-circle" aria-hidden="true"></i> In questa pagina è possibile inserire le varie sedi delle banche, che sono poi valide per tutti i comuni. (non solo per quello selezionato)</p>

<form class="form-horizontal validate" name=form_sede id=form_sede method=post action="banca_salva.php">

<input type=hidden name=invia_submit 		value=""	id=invia_submit  	>
<input type=hidden name=id_sede  			value="<?php echo $id_sede; ?>" >
<input type=hidden name="tipo_banca" value="sede" >
<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $a_param["CC_Sede"]; ?>">

    <!--<button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom" aria-controls="offcanvasBottom" onclick="selectParent = 'banca';">Toggle bottom offcanvas</button>

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel" style="height: 450px;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">Offcanvas bottom</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body small">
            <iframe style="width: 100%; height: 450px;" src="<?= WEB_ROOT; ?>/search/banche/ricerca_banche_mod.php?richiesta=singola&a=<?php echo $a;?>&c=*****"></iframe>
        </div>
    </div>-->


<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">SEDE</label>
			<div class="col-lg-8">
				<input class="text_left form-control resize vld_req" id=denom_id name=denom value="<?php echo $denominazione; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<div class="col-lg-12">
				<select id=forma_giuridica class="form-control resize vld_req" name=forma_giuridica onchange="cambia_title('forma_giuridica');">
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
	<div class="col col-lg-2">
		<div class="form-group">
			<div class="col-lg-8 col-lg-offset-4">
				<button class="btn btn-primary form-control resize" type=button id=cerca_banca name=cerca_banca value="Sede" onclick="/*ricerca_banca('sede');*/openOfcanvas('bankSearchModalH',0);">Sede</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva *</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_PIReq" id=PI_id name=PI value="<?php echo $a_param["Partita_Iva"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Codice Fiscale</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_CFPIReq" id=CF_id name=CF value="<?php echo $a_param["Codice_Fiscale"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-offset-1 col-lg-2">
		<p style="color:blue;font-weight:bold;">Regioni filiali/sede:</p>
	</div>
	<div class="col col-lg-4">
		<select id="all_reg" multiple="multiple" name="regioni[]">
			<!--<option value="00">Tutte le regioni</option>-->
			<?php echo $optReg; ?>
		</select>
	</div>
	<div class="col col-lg-2 col-lg-offset-1">
		<div class="form-check">
			<input class="form-check-input" type="checkbox" <?=$selectDis;?> value="1" id="disabled" name="disabled">
			<label class="form-check-label" for="disabled">
				Disabilita banca
			</label>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top:1%;"></div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
			<div class="col-lg-8">
				<input class=" text_left form-control resize validateCustom vld_Custom_r " style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly tabindex=1 name=comune id=comune_id value="<?php echo $a_param["Comune"]; ?>" ondblclick="/*cerca_comune('tribunale');*/openOfcanvas('citySearchModal',0);">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Prov. *</label>
			<div class="col-lg-8">
				<input class="form-control resize text_left validateCustom vld_Custom_r" style="width: 60%; background-color: #97CFDD; border: 2px solid black;" readonly tabindex=2 id=prov_id name=prov value="<?php echo $a_param["Provincia"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP *</label>
			<div class="col-lg-8">
				<input class="text_center form-control resize vld_req" tabindex=3 id=cap_id name=cap size=4 value="<?php echo $a_param["Cap"]; ?>">
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo *</label>
			<div class="col-lg-8">
				<input id=via class="text_left form-control resize vld_req" name=via type=text value="<?php echo $a_param["Toponimo"]; ?>" tabindex=5 >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.*</label>
			<div class="col-lg-8">
				<input type="text" id=civico class="form-control resize vld_intReq" name="civico" value="<?php echo $civ; ?>" tabindex=6>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input type="text" id=esponente class="form-control resize vld_esp" name="esponente" value="<?php echo $a_param["Esponente"]; ?>"  size=2 tabindex=7>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input type="text" id=interno class="form-control resize vld_int" name="interno" value="<?php echo $int; ?>" tabindex=8>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
				<input type="text" id=dettagli class="text_left form-control resize" name="dettagli" value="<?php echo $a_param["Dettagli"]; ?>" tabindex=9>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=tel_id name=tel value="<?php echo $a_param["Telefono"]; ?>" tabindex=10>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=fax_id name=fax value="<?php echo $a_param["Fax"]; ?>" ondblclick="controllaCampi();" tabindex=11>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_Sito" id=sito_id name=sito value="<?php echo $a_param["Sito"]; ?>" tabindex=12>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=email_id name=email value="<?php echo $a_param["Mail"]; ?>" tabindex=13>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">PEC *</label>
			<div class="col-lg-8">
				<input title="Inserimento PEC obbligatorio" class="text_left form-control resize vld_emailReq" id=pec_id name=PEC value="<?php echo $a_param["PEC"]; ?>" tabindex=14>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Password *</label>
			<div class="col-lg-8">
				<input type="password" class="text_left form-control resize vld_req" id=pass_id name=pass value="<?php echo $a_param["Password"]; ?>" tabindex=15>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Orario</label>
			<div class="col-lg-11">
				<textarea class="text_left form-control resize" style="max-width: 100%;" id=orario_id name=orario rows=3 tabindex=16><?php echo $a_param["Orario"]; ?></textarea>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>
<link rel=StyleSheet href="<?= JS; ?>/JQuery_Bootstrap_Multiselect/dist/css/bootstrap_multiselect.min.css" type="text/css">

	<script type="text/javascript" language="javascript" src="<?= JS ?>/JQuery_Bootstrap_Multiselect/dist/js/bootstrap_multiselect.min.js" ></script>

	<!--<link rel=StyleSheet href="<?= JS; ?>/bootstrap-multiselect-3/dist/css/bootstrap-multiselect.min.css" type="text/css">

	<script type="text/javascript" language="javascript" src="<?= JS ?>/bootstrap-multiselect-3/dist/js/bootstrap-multiselect.min.js" ></script>-->

<script type="text/javascript">

	$( window ).load(function() {
		$('.tr_tipo_banca').hide();
		$('#filiale_sede').hide();
		$('#avvisoBanca').hide();
		$('.tr_tipo_banca').show();
		sede_banca();
		$('#avvisoBanca').show();
		$('#forma_giuridica').val('<?php echo $forma_giuridica; ?>');
		cambia_title('forma_giuridica');

		if('<?php echo $a_param["Tipo_Banca"]; ?>'!="")
		{
			$('#tipo_banca').val('<?php echo $a_param["Tipo_Banca"]; ?>');
			sede_banca();
		}

		

	});

	$(document).ready(function(){
		$("#all_reg").multiselect({
      buttonClass: 'btn-success btn-sm form-control',
	  filterPlaceholder: 'Search',
	  //numberDisplayed: 4,
	  buttonWidth: '100%',
	  includeSelectAllOption: true,
	  //enableCollapsibleOptGroups:true,
	  buttonTextAlignment: 'center',
    //buttonClass: 'form-control'
	  //enableFiltering: true,
	  // allows HTML content

  /*enableHTML:false,

  // CSS class of the multiselect button

  buttonClass:'custom-select',

  // inherits the class of the button from the original select

  inheritClass:false,

  // width of the multiselect button

  buttonWidth:'auto',

  // container that holds both the button as well as the dropdown

  buttonContainer:'<div class="btn-group" />',

  // places the dropdown on the right side

  dropRight:false,

  // places the dropdown on the top

  dropUp:false,

  // CSS class of the selected option

  selectedClass:'active',

  // maximum height of the dropdown menu

  // if maximum height is exceeded a scrollbar will be displayed

  maxHeight:false,

  // includes Select All Option

  includeSelectAllOption:false,

  // shows the Select All Option if options are more than ...

  includeSelectAllIfMoreThan: 0,

  // Lable of Select All

  selectAllText:' Select all',

  // the select all option is added as additional option within the select

  // o distinguish this option from the original options the value used for the select all option can be configured using the selectAllValue option.

  selectAllValue:'multiselect-all',

  // control the name given to the select all option

  selectAllName:false,

  // if true, the number of selected options will be shown in parantheses when all options are seleted.

  selectAllNumber:true,

  // setting both includeSelectAllOption and enableFiltering to true, the select all option does always select only the visible option

  // with setting selectAllJustVisible to false this behavior is changed such that always all options (irrespective of whether they are visible) are selected

  selectAllJustVisible:true,

  // enables filtering

  enableFiltering:false,

  // determines if is case sensitive when filtering

  enableCaseInsensitiveFiltering:false,

  // enables full value filtering

  enableFullValueFiltering:false,

  // if true, optgroup's will be clickable, allowing to easily select multiple options belonging to the same group

  enableClickableOptGroups:false,

  // enables collapsible OptGroups

  enableCollapsibleOptGroups:false,

  // collapses all OptGroups on init

  collapseOptGroupsByDefault:false,

  // placeholder of filter filed

  filterPlaceholder: 'Search',

  // possible options: 'text', 'value', 'both'

  filterBehavior: 'text',

  // includes clear button inside the filter filed

  includeFilterClearBtn: true,

  // prevents input change event

  preventInputChangeEvent: false,

  // message to display when no option is selected

  nonSelectedText: 'None selected',

  // message to display if more than numberDisplayed options are selected

  nSelectedText: 'selected',

  // message to display if all options are selected

  allSelectedText: 'All selected',

  // determines if too many options would be displayed

  numberDisplayed: 3,

  // disables the multiselect if empty

  disableIfEmpty: false,

  // message to display if the multiselect is disabled

  disabledText: '',

  // the separator for the list of selected items for mouse-over

  delimiterText: ',',

  // includes Reset Option

  includeResetOption: false,

  // includes Rest Divider

  includeResetDivider: false,

  // lable of Reset  Option

  resetText: 'Reset',

  // indent group options

  indentGroupOptions: true,

  // possible options: 'never', 'always', 'ifPopupIsSmaller', 'ifPopupIsWider'

  widthSynchronizationMode: 'never',

  // text alignment

  buttonTextAlignment: 'center',*/

  // custom templates

  templates: {

    button: '<button type="button" class="multiselect dropdown-toggle" data-toggle="dropdown"><span class="multiselect-selected-text"></span></button>',

    Container: '<div class="multiselect-container dropdown-menu"></div>',

    filter: '<div class="multiselect-filter" style="width:100%;"><div class="input-group input-group-sm p-1"><div class="input-group-prepend"><i class="input-group-text fas fa-search"></i></div><input class="form-control multiselect-search" type="text" /></div></div>',

    filterClearBtn: '<div class="input-group-append"><button class="multiselect-clear-filter input-group-text" type="button"><i class="fas fa-times"></i></button></div>',

    option: '<button class="multiselect-option dropdown-item option_regioni" style="width: 100%;"></button>',

    divider: '<div class="dropdown-divider"></div>',

    optionGroup: '<button class="multiselect-group dropdown-item"></button>',

    resetButton: '<div class="multiselect-reset text-center p-2"><button class="btn btn-sm btn-block btn-outline-secondary"></button></div>'

  }

    });
	});
</script>


<?php include(INC."/footer.php"); ?>
