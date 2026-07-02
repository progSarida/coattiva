<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_authority.php");

if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}



$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo_ufficio = $cls_help->getVar('tipo_ufficio');

switch($tipo_ufficio)
{
	case "tribunale":
		{
			$ufficio_giud = "Tribunale";
			$next_uff = "giudice";
			$prev_uff = "cassazione";
		}
		break;
	case "giudice":
		{
			$ufficio_giud = "Giudice di pace";
			$next_uff = "appello";
			$prev_uff = "tribunale";
		}
		break;
	case "appello":
		{
			$ufficio_giud = "Corte d'appello";
			$next_uff = "comm_trib_prov";
			$prev_uff = "giudice";
		}
		break;
	case "comm_trib_prov":
		{
			$ufficio_giud = "Commissione tributaria provinciale";
			$next_uff = "comm_trib_reg";
			$prev_uff = "appello";
		}
		break;
	case "comm_trib_reg":
		{
			$ufficio_giud = "Commissione tributaria regionale";
			$next_uff = "cassazione";
			$prev_uff = "comm_trib_prov";
		}
		break;
	case "cassazione":
		{
			$ufficio_giud = "Corte di cassazione";
			$next_uff = "tribunale";
			$prev_uff = "comm_trib_reg";
		}
		break;
	default:
		{
			$ufficio_giud = "Tribunale";
			$next_uff = "giudice";
			$prev_uff = "cassazione";
		}
		break;
}


$nome_com = $a_enteAdmin["Denominazione"];
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];



/************************************MODIFICA**********************************************************************/
$cls_authority = new cls_authority();
$a_comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery( $cls_authority->getRecordsquery($tipo_ufficio,$c)),"ufficio_giudiziario");


	$int = $a_comune["Interno"];
	$civ = $a_comune["Civico"];

	if($int==0)$int="";
	if($civ==0)$civ="";



?>


<!-- ********** GESTIONE LINK MENU ********** -->
<?php

include(INC."/menu.php");

?>
<script>


//F3
switchMenuImg("F3");
F3_button = function()
{
	if($('#ID_uff').val()==0)
		control_salva = submit_buttons('Insert');
    else
    	control_salva = submit_buttons('Update');

	if(control_salva)
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
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ufficio=<?php echo $tipo_ufficio; ?>";
	stringa = "ufficio_giudiziario.php?"+stringaPHP;
	   	top.location.href = stringa;
}


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
	{
		location.href = "ufficio_giudiziario.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ufficio=<?php echo $prev_uff; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "ufficio_giudiziario.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_ufficio=<?php echo $next_uff; ?>";
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

<!-- ********** MODALI AJAX ********** -->
<script>

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
                cap = valorediritorno.cap;
                for(var contatore=0;contatore<2;contatore++)
                {
                    cap = cap.replace("x", "0");
                }

                $('#comune_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(cap);
                $('#CC_uff').val(valorediritorno.CC);
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

</script>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor"><?php echo $ufficio_giud; ?></p>
	</div>
</div>

<form class="form-horizontal validate" name=form_ufficio_giud id=form_ufficio_giud method=post action="ufficio_giudiziario_salva.php">

<input type=hidden name=ID_uff id=ID_uff value="<?php echo $a_comune["ID"]; ?>" >
<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC_uff id=CC_uff value="<?php echo $a_comune["CC_Ufficio"]; ?>" >
<input type=hidden name=tipo_uff id=tipo_uff value="<?php echo $tipo_ufficio; ?>" >


<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
					<input class="text_left form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly tabindex=1 name=comune id=comune_id value="<?php echo $a_comune["Comune"]; ?>" onclick="cerca_comune();">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>
			<div class="col-lg-8">
					<input style="background-color: #97CFDD; border: 2px solid black; width: 50%;" class="form-control resize text_left" readonly tabindex=2 id=prov_id name=prov value="<?php echo $a_comune["Provincia"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
			<div class="col-lg-8">
					<input class="text_center form-control resize vld_intReq" style="width: 80%;" tabindex=3 id=cap_id name=cap value="<?php echo $a_comune["Cap"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sezione</label>
			<div class="col-lg-8" >
					<input class="text_left width100 form-control resize vld_req" id=sezione_id name=sezione value="<?php echo $a_comune["Sezione"]; ?>" tabindex=4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
			<div class="col-lg-8">
					<input id=via class="text_left form-control resize vld_req" name=via type=text value="<?php echo $a_comune["Toponimo"]; ?>" tabindex=5 >
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
					<input type="text" id=civico class="form-control resize vld_intReq" style="width: 100%;" name="civico"	value="<?php echo $civ; ?>" tabindex=6>
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
					<input type="text" id=esponente  class="text_left form-control resize vld_esp" style="width: 60%;"  name="esponente" value="<?php echo $a_comune["Esponente"]; ?>"  size=2 tabindex=7>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8" >
				<input type="text" id=interno class="form-control resize vld_intReq"  name="interno" 	value="<?php echo $int; ?>"  size=2 tabindex=8>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8" >
				<input type="text" id=dettagli   class="text_left form-control resize"   name="dettagli" 	value="<?php echo $a_comune["Dettagli"]; ?>" tabindex=9>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=tel_id name=tel class="width100" value="<?php echo $a_comune["Telefono"]; ?>" tabindex=10>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" id=fax_id name=fax size=18 value="<?php echo $a_comune["Fax"]; ?>" ondblclick="controllaCampi();" tabindex=11>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=email_id name=email value="<?php echo $a_comune["Mail"]; ?>" tabindex=12>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" id=pec_id name=PEC size=18 value="<?php echo $a_comune["PEC"]; ?>" tabindex=13>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_Sito" id=sito_id name=sito value="<?php echo $a_comune["Sito"]; ?>" tabindex=14>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

<div class="row justify-content-md-center " style="margin-top: 0.5%;">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Responsabili</p>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 0.5%;"></div>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-6 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Descrizione</label>
			<div class="col-lg-10">
				<input class="form-control resize" name=desc_resp_1 id=desc_resp_id_1 value="<?php echo $a_comune["Responsabile_1"]; ?>" tabindex=15>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Nome</label>
			<div class="col-lg-8">
				<input class="form-control resize" name=resp_1 id=resp_id_1 value="<?php echo $a_comune["Nome_Responsabile_1"]; ?>" tabindex=16>
			</div>
		</div>
	</div>
</div>

<div class="row" >
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" name=tel_resp_1 id=tel_resp_id_1 value="<?php echo $a_comune["Telefono_Responsabile_1"]; ?>" tabindex=17>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" name=fax_resp_1 id=fax_resp_id_1 value="<?php echo $a_comune["Fax_Responsabile_1"]; ?>" tabindex=18>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" name=mail_resp_1 id=mail_id_1 value="<?php echo $a_comune["Mail_Responsabile_1"]; ?>" tabindex=19>
			</div>
		</div>
	</div>
</div>

<div class="row" style="margin-top: 1%;">
	<div class="col col-lg-6 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Descrizione</label>
			<div class="col-lg-10">
				<input class="form-control resize" name=desc_resp_2 id=desc_resp_id_2 value="<?php echo $a_comune["Responsabile_2"]; ?>" tabindex=20>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Nome</label>
			<div class="col-lg-8">
				<input class="form-control resize" name=resp_2 id=resp_id_2 value="<?php echo $a_comune["Nome_Responsabile_2"]; ?>" tabindex=21>
			</div>
		</div>
	</div>
</div>

<div class="row" >
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" name=tel_resp_2 id=tel_resp_id_2 value="<?php echo $a_comune["Telefono_Responsabile_2"]; ?>"  tabindex=22>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" name=fax_resp_2 id=fax_resp_id_2 value="<?php echo $a_comune["Fax_Responsabile_2"]; ?>" tabindex=23>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" name=mail_resp_2 id=mail_id_2 value="<?php echo $a_comune["Mail_Responsabile_2"]; ?>" tabindex=24>
			</div>
		</div>
	</div>
</div>

<div class="row" style="margin-top: 1%;">
	<div class="col col-lg-6 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Descrizione</label>
			<div class="col-lg-10">
				<input class="form-control resize" name=desc_resp_3 id=desc_resp_id_3 value="<?php echo $a_comune["Responsabile_3"]; ?>" tabindex=25>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Nome</label>
			<div class="col-lg-8">
				<input class="form-control resize" name=resp_3 id=resp_id_3 value="<?php echo $a_comune["Nome_Responsabile_3"]; ?>" tabindex=26>
			</div>
		</div>
	</div>
</div>

<div class="row" >
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" name=tel_resp_3 id=tel_resp_id_3 value="<?php echo $a_comune["Telefono_Responsabile_3"]; ?>" tabindex=27>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_tel" name=fax_resp_3 id=fax_resp_id_3 value="<?php echo $a_comune["Fax_Responsabile_3"]; ?>" tabindex=28>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_email" name=mail_resp_3 id=mail_id_3 value="<?php echo $a_comune["Mail_Responsabile_3"]; ?>" tabindex=29>
			</div>
		</div>
	</div>
</div>

<div class="form-group">
		<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<?php include(INC."/footer.php"); ?>
