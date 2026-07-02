<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_paramUtils.php");
include_once(CLS."/cls_DateTimeInLine.php");

$cls_param = new cls_param();
$cls_date = new cls_DateTimeI("IT");

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$tipo_ente = $cls_help->getVar('tipo_ente');
$progr = $cls_help->getVar('progr');

if($progr == null) $progr = 0;

switch($tipo_ente)
{
	case "previdenza":
		{
			$categoria_ente_esterno = "Istituti previdenziali";
			$next_ente = "previdenza";
			$prev_ente = "previdenza";
		}
		break;

	default:
		{
			$categoria_ente_esterno = "";
			$next_ente = "";
			$prev_ente = "";
		}
		break;
}

$nome_com = $a_enteAdmin["Denominazione"];
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$QUERY = $cls_param->Get_Query_EnteEs('*****',$tipo_ente,"",$progr);

$a_param = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["EnteEG"]),"enti_esterni");

//echo $QUERY["EnteE"];
//var_dump($a_param);
$ID_ente = $a_param["ID"];
$int = $a_param["Interno"];
$civ = $a_param["Civico"];

if($int==0)$int="";
if($civ==0)$civ="";

$QUERY = $cls_param->Get_Query_EnteEs('*****',$tipo_ente, $ID_ente,$progr);
$array_doc = array();

if($ID_ente != "")
	$array_doc = $cls_db->getResults($cls_db->ExecuteQuery($QUERY["Doc"]));

$prec = $cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY["Ente_prec"]));

if($prec == null) $prec = 0;
else $prec = $prec["progressivo"];

$next = $cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY["Ente_next"]));

if($next == null) $next = 0;
else $next = $next["progressivo"];


//echo $QUERY["Ente_prec"]." <br><br> ".$QUERY["Ente_next"];
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	if($('#ID_ente').val()==0)
		control_salva = submit_buttons('Insert');
    else
    	control_salva = submit_buttons('Update');

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
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=<?php echo $tipo_ente; ?>";
	stringa = "ente_esterno.php?"+stringaPHP;
	   	top.location.href = stringa;
}

//F6
switchMenuImg("F6");
F6_button = function()
{
    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=<?php echo $tipo_ente; ?>&progr=0";
    stringa = "ente_esterno.php?"+stringaPHP;
    top.location.href = stringa;
}

//F7
switchMenuImg("F7");
F7_button = function()
{

    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=<?php echo $tipo_ente; ?>&progr=<?= $prec; ?>";
    stringa = "ente_esterno.php?"+stringaPHP;
    top.location.href = stringa;

}

//F8
switchMenuImg("F8");
F8_button = function()
{

    stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=<?php echo $tipo_ente; ?>&progr=<?= $next; ?>";
    stringa = "ente_esterno.php?"+stringaPHP;
    top.location.href = stringa;

}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/previdenza.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Sede previdenza</b>");
    $("#helpModal").modal('show');

}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "ente_esterno.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=<?php echo $prev_ente; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "ente_esterno.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=<?php echo $next_ente; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'
</script>

<!-- ********** CONTROLLI, AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>
//CONTROLLO CAMPI
function controllaCampi ()
{
	pattern_data = /[^0-9\x2F]/;
	pattern_ditta = /[^A-Za-z0-9 ,;:.\x27]/;
	pattern_nome = /[^A-Za-z \x27]/;
	pattern_numeri = /[^0-9]/;
	pattern_interno = /[^0-9a-zA-Z\x2F]/;
	pattern_cf = /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/;

	var comune = $('#comune').val();
	var CDF = $('input#CF_id').val().toUpperCase();
	var PI = $('input#PI_id').val();
	var mail = $('#email_id').val();
	var pec = $('#pec_id').val();
	var via = $('#via').val().toUpperCase();
	var civico = $('#civico').val();
	var interno = $('#interno').val();
	var esp = $('#esponente').val();
	var dett = $('#dettagli').val();
	var tel = $('input#tel_id').val();
	var fax = $('input#fax_id').val();

//	CAMPI OBBLIGATORI

	obbl_comune = obbligatorio( comune , "Comune" );		if( obbl_comune!=true )		return false;
	obbl_via = obbligatorio( via , "Indirizzo" );			if( obbl_via!=true )		return false;

//	CONTROLLI

	control_cf = verifica_cf( CDF );						if( control_cf )$('input#CF_id').val(CDF);

	control_pi = verifica_pi( PI );

	control_mail = verifica_mail(mail);						if( control_mail!=true )	return false;

	control_pec = verifica_mail(pec,"PEC");					if( control_pec!=true )		return false;

	control_via = verifica_testo(via, "Indirizzo" );		if( control_via )			$('#via').val(via);
															else						return false;

	control_civ = verifica_numero(civico, "Civico");		if( control_civ!=true )		return false;
	control_esp = verifica_alfanum(esp, "Esponente");		if( control_esp!=true )		return false;
	control_int = verifica_numero(interno,"Interno");		if( control_int!=true )		return false;
	control_dett = verifica_alfanum(dett, "Dettagli");		if( control_dett!=true )	return false;

	control_tel = verifica_numero(tel, "Telefono");			if( control_tel!=true )		return false;
	control_fax = verifica_numero(fax, "Fax");				if( control_fax!=true )		return false;

	alert('fine controlli');

	return true;

	}
</script>

<!-- Inclusione modale per ricerca comune -->
<?php include_once (ROOT."/search_modal/offcanvas/city_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca ente previdenziale -->
<?php include_once (ROOT."/search_modal/offcanvas/welfare_offcanvas.php"); ?>

<!-- ********** MODALI AJAX ********** -->
<script>

// Modali offcanvas
function openOfcanvas(type,rif){
    switch (type){
        case 'citySearchModal':                                     // ricerca comune
            // Reset campi input
            $('#city').val("");
            // Reset spazi tabella
            $('#appendTableCity').empty();
            // Apertura modale
            selectRif = rif;
            $('#citySearchModal').modal('show');
            break;
        case 'welfareSearchModal':                                     // ricerca ente previdenza
            // Reset campi input
            $('#welfare_n').val("");
            $('#welfare_c').val("");
            $('#welfare_cap').val("");
            // Reset spazi tabella
            $('#appendTableWelfare').empty();
            // Apertura modale
            selectRif = rif;
            $('#welfareSearchModal').modal('show');
            break;
    }
}
function initialId(tipo,val){
    switch (tipo){
        case "city":
            $('#comune_id').val(val['nome']);
            $('#prov_id').val(val['prov']);
            $('#cap_id').val(val['cap']);
            $('#CC_ente').val(val['CC_C']);

            let event = new Event("change");
            document.getElementById("comune_id").dispatchEvent(event);
            document.getElementById("prov_id").dispatchEvent(event);
            document.getElementById("cap_id").dispatchEvent(event);
            break;
        case "welfare":
            var prog = val['progressivo'];
            stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=previdenza";
            stringa = "<?= WEB_ROOT;?>/parametri/ente_esterno.php?"+stringaPHP+"&progr="+prog;
            top.location.href = stringa;
            break;
        case "doc":                                                                                 // ??
            if( valorediritorno!=null && valorediritorno!=undefined )
                annulla();
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
    switch(selectParent){
        case "comune":

            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                $('#comune_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(valorediritorno.cap);
                $('#CC_ente').val(valorediritorno.CC);

                let event = new Event("change");
                document.getElementById("comune_id").dispatchEvent(event);
                document.getElementById("prov_id").dispatchEvent(event);
                document.getElementById("cap_id").dispatchEvent(event);
            }

            break;
		case "previdenza":
            if( valorediritorno!=null && valorediritorno!=undefined )
            {
				var prog=valorediritorno.prog;
                stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ente=previdenza";
                stringa = "<?= WEB_ROOT;?>/parametri/ente_esterno.php?"+stringaPHP+"&progr="+prog;
                top.location.href = stringa;
            }

            break;
        case "doc":
            if( valorediritorno!=null && valorediritorno!=undefined )
                annulla();

            break;
    }

}

var selectParent = "";

function ricerca_ente(typeEnte)
{
	switch(typeEnte)
	{
		case "previdenza" : 
				selectParent = "previdenza";
				//coattiva\Gitco2\parametri\modali\ricerca_ente_esterno.php
				var stringa = "<?= WEB_ROOT; ?>/parametri/modali/ricerca_ente_esterno.php?richiesta=previdenza&a=<?php echo $a;?>&c=*****";
				openWindowSearch(stringa,{width:1200, height:400, left:(($(window).width()/2)-600), top:(($(window).height()/2)-200)});
				break;
	}
    
}

function cerca_comune()
{
    selectParent = "comune";

	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
}


function carica_doc(value)
{
    selectParent = "doc";

	stringa = "<?= WEB_ROOT; ?>/search/documento/documento_ente.php?c=<?php echo $c?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&id_doc="+value+"&id_ente=<?php echo $ID_ente; ?>";

	openWindowSearch(stringa,{width:1200, height:600, left:(($(window).width()/2)-600), top:(($(window).height()/2)-300)});

}

</script>

	<div class="row justify-content-md-center " style="margin-bottom: 2%;">
		<div class="col col-md-auto text_center">
				<span class="titolo font16 under_decor"><?php echo $categoria_ente_esterno; ?></span>
		</div>
	</div>

<form name=form_ente_esterno class="form-horizontal validate" id=form_ente_esterno method=post action="ente_esterno_salva.php">

<input type=hidden name=ID_ente id=ID_ente value="<?php echo $ID_ente; ?>" >
<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=progr value=<?php echo $progr; ?> >
<input type=hidden name=CC_ente id=CC_ente value="<?php echo $a_param["CC_Ente"]; ?>" >
<input type=hidden name=tipo_ente id=tipo_ente value="<?php echo $tipo_ente; ?>" >


<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Denominazione</label>
			<div class="col-lg-8">
				<input class="text_left form-control resize vld_req" tabindex=1 name=denominazione id=denom_id value="<?php echo $a_param["Denominazione"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Partita IVA</label>
			<div class="col-lg-7">
				<input class="text_right form-control resize vld_PIReq" tabindex=2 name=PI id=PI_id value="<?php echo $a_param["Partita_Iva"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-2" >
		<div class="form-group">
			<div class="col-lg-14 ">
				<button class="btn btn-primary form-control resize" tabindex=2  type=button id=cerca_ente name=cerca_ente value="ente" onclick="/*ricerca_ente('previdenza');*/openOfcanvas('welfareSearchModal',0);">Ricerca sedi enti previdenziali</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly tabindex=3 name=comune id=comune_id value="<?php echo $a_param["Comune"]; ?>" ondblclick="/*cerca_comune();*/openOfcanvas('citySearchModal',0);">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r" style="background-color: #97CFDD; border: 2px solid black;" readonly tabindex=4 id=prov_id name=prov value="<?php echo $a_param["Provincia"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
			<div class="col-lg-8">
				<input class="form-control resize validateCustom vld_Custom_r"   tabindex=5 id=cap_id name=cap size=4 value="<?php echo $a_param["Cap"]; ?>">
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
				<input id=via class="form-control resize vld_req" name=via type=text value="<?php echo $a_param["Toponimo"]; ?>" tabindex=6 >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
				<input type="text" id=civico class="form-control resize vld_intReq" name="civico"	value="<?php echo $civ; ?>" tabindex=7>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input type="text" id=esponente  class="form-control resize vld_esp"   name="esponente" value="<?php echo $a_param["Esponente"]; ?>"  size=2 tabindex=8>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input type="text" id=interno class="form-control resize vld_intReq" name="interno" value="<?php echo $int; ?>" tabindex=9>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
				<input type="text" id=dettagli   class="form-control resize"   name="dettagli" 	value="<?php echo $a_param["Dettagli"]; ?>" tabindex=10>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=tel_id name=tel value="<?php echo $a_param["Telefono"]; ?>" tabindex=11>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=fax_id name=fax value="<?php echo $a_param["Fax"]; ?>" ondblclick="controllaCampi();" tabindex=12>
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
			<label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=pec_id name=PEC size=18 value="<?php echo $a_param["PEC"]; ?>" tabindex=14>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_Sito" id=sito_id name=sito value="<?php echo $a_param["Sito"]; ?>" tabindex=15>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Annotazioni</label>
			<div class="col-lg-11">
				<textarea class="form-control resize" style="max-width: 100%;" rows=3 tabindex=16 name=note id=note_id ><?php echo $a_param["Note"]; ?></textarea>
			</div>
		</div>
	</div>
</div>
<!-- <div class="row">
    <div class="col-lg-1"></div>
    <div class="col-lg-10">
        <i style="float: right;color: green;cursor: pointer;" title="Carica File" class="fa fa-upload fa-lg" aria-hidden="true" onclick="carica_doc(0);"></i>
    </div>

</div> -->

<!-- <div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<table class="table table-hover" cellspacing="4" cellpadding="0" border="0">
		<thead style="border-bottom: 2px solid #6963FF;">
			<tr >
				<td class="text_center width3"></td>
				<td class="text_left width24"><font class="color_titolo"><b>Documento</b></font></td>
				<td class="text_left width5"><font class="color_titolo"><b>ID</b></font></td>
				<td class="text_left width9"><font class="color_titolo"><b>Partita</b></font></td>
				<td class="text_left width9"><font class="color_titolo"><b>Tipo</b></font></td>
				<td class="text_center width12"><font class="color_titolo"><b>Data stampa</b></font></td>
				<td class="text_left width38"><font class="color_titolo"><b>Informazioni</b></font></td>
			</tr>
		</thead>
		<tbody>
		<?php

		// for($i=0;$i<count($array_doc);$i++)
		// {
		// 	$path =  ATTI ."/". $c . "/Documenti/".$array_doc[$i]['File'];
		// 	$path_file = substr( $path , strpos( $path , "/archivio/" )); //mostra_file_path($path);
		// 	?>
		// 		<tr class="info">
		// 			<td class="text_center">
		// 				<a href="#" style="text-decoration:none;">
		// 					<img src="<?= IMMAGINIWEB; ?>/select.png" style="text-decoration:none; border:none" width="15" height="15"
		// 						onclick="carica_doc('<?php echo $array_doc[$i]['ID']; ?>');" title='Modifica documento'>
		// 				</a>
		// 			</td>
		// 			<td class="text_left"><?php echo $array_doc[$i]['Atto']; ?></td>
		// 			<td class="text_left"><?php echo $array_doc[$i]['Comune_ID']; ?></td>
		// 			<td class="text_left">Assente</td>
		// 			<td class="text_left"><?php echo $array_doc[$i]['Tipo']; ?></td>
		// 			<td class="text_center"><?php echo $cls_date->Get_DateNewFormat($array_doc[$i]['Data_Stampa']); ?></td>
		// 			<td class="text_left"><?php echo $array_doc[$i]['Oggetto']; ?></td>
		// 		</tr>
		// <?php
		// }
		?>
	</tbody>
	</table>

	</div>
</div> -->

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<?php include(INC."/footer.php"); ?>
