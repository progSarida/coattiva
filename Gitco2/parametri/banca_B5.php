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

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script type="text/javascript">

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

var next_tipo = "<?php echo $a_paramNextS["ID"]; ?>";
var prev_tipo = "<?php echo $a_paramPrevS["ID"];/*$sede->Prev_Sede;*/ ?>";


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "banca.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_sede="+next_tipo;
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "banca.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_sede="+prev_tipo;
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\


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

<form class="form-horizontal validate" name=form_sede id=form_sede method=post action="banca_salva.php">

<input type=hidden name=invia_submit 		value=""	id=invia_submit  	>
<input type=hidden name=id_sede  			value="<?php echo $id_sede; ?>" >
<input type=hidden name="tipo_banca" value="sede" >
<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $a_param["CC_Sede"]; ?>">

    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom" aria-controls="offcanvasBottom" onclick="selectParent = 'banca';">Toggle bottom offcanvas</button>

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel" style="height: 450px;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">Offcanvas bottom</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body small">
            <iframe style="width: 100%; height: 450px;" src="<?= WEB_ROOT; ?>/search/banche/ricerca_banche_mod.php?richiesta=singola&a=<?php echo $a;?>&c=*****"></iframe>
        </div>
    </div>


<div class="row">
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
				<button class="btn btn-primary form-control resize" type=button id=cerca_banca name=cerca_banca value="Sede" onclick="ricerca_banca('sede');">Sede</button>
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
				<input class="form-control resize vld_CFReq" id=CF_id name=CF value="<?php echo $a_param["Codice_Fiscale"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
			<div class="col-lg-8">
				<input class=" text_left form-control resize validateCustom vld_Custom_r " style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly tabindex=1 name=comune id=comune_id value="<?php echo $a_param["Comune"]; ?>" onclick="cerca_comune('tribunale');">
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
</script>


<?php include(INC."/footer.php"); ?>
