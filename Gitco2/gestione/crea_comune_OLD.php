<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(INC."/menu.php");

$servizio = $cls_help->getVar('servizio');

//$comune = new ente_gestito($c);
$nome_comune = $a_enteAdmin["Denominazione"];//$comune->Nome;

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

?>
<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	if($('[name=comune_1]').val()!="")
	{
		control = submit_buttons('Salva');
		if(control)
			$('#btnSub').trigger("click");
	}
}


//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="crea_comune.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){

	link = "elimina_anno.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
	top.location.href = link;
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){

	link = "crea_anno.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
	top.location.href = link;
}

//F11-F12 sono nel menu'

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

function callParent(valorediritorno) {
    if(valorediritorno!=null && valorediritorno!=undefined){
        switch(valueRicerca)
        {
            case 1:

                $('#comune_1').val(valorediritorno.comune);
                $('#CC_1').val(valorediritorno.CC);

                break;

            case 2:

                $('#comune_2').val(valorediritorno.comune);
                $('#CC_2').val(valorediritorno.CC);

                break;

            case 3:

                $('#comune_3').val(valorediritorno.comune);
                $('#CC_3').val(valorediritorno.CC);

                break;

            case 4:

                $('#comune_4').val(valorediritorno.comune);
                $('#CC_4').val(valorediritorno.CC);

                break;

            case 5:

                $('#comune_5').val(valorediritorno.comune);
                $('#CC_5').val(valorediritorno.CC);

                break;
        }

    }
}

var valueRicerca = "";
function ricerca_comune(value)
{
    valueRicerca = value;
	//strDim = Dim_Alert(600, 300);
	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

	openWindowSearch(stringa,{width:600, height:300, left:(($(window).width()/2)-300), top:(($(window).height()/2)-150)});
	//valorediritorno = window.showModalDialog(stringa, "", strDim);
}

function cambio_enti()
{
	$('.ente_5').hide();

	switch($('#num_enti').val())
	{
		case "2":		$('.ente_2').show();	break;
		case "3":		$('.ente_3').show();	break;
		case "4":		$('.ente_4').show();	break;
		case "5":		$('.ente_5').show();	break;
	}
}

</script>

<div class="row justify-content-md-center " style="margin-top: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Creazione ente</span>
	</div>
</div>


<form name=form_crea class="form-horizontal validate" id=form_crea method=post action="crea_comune_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=CC_1 id=CC_1 value="" >
<input type=hidden name=CC_2 id=CC_2 value="" >
<input type=hidden name=CC_3 id=CC_3 value="" >
<input type=hidden name=CC_4 id=CC_4 value="" >
<input type=hidden name=CC_5 id=CC_5 value="" >
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=servizio value=<?php echo $servizio; ?> >

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Selezione n. comuni</label>
			<div class="col-lg-8">
				<select name=num_enti id=num_enti onchange="cambio_enti();" class="form-control resize" tabindex=1>
					<option>1</option>
					<option>2</option>
					<option>3</option>
					<option>4</option>
					<option>5</option>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="row ente_2 ente_3 ente_4 ente_5">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Denominazione</label>
			<div class="col-lg-8">
				<input class="form-control resize" type=text name=denominazione id=denominazione class="form-control resize" size=30 tabindex=2>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text name=comune_1 readonly id=comune_1 size=20 ondblclick="ricerca_comune(1);" tabindex=3>
			</div>
		</div>
	</div>
</div>

<div class="row ente_2 ente_3 ente_4 ente_5">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text name=comune_2 readonly id=comune_2 size=20 ondblclick="ricerca_comune(2);" tabindex=4>
			</div>
		</div>
	</div>
</div>

<div class="row ente_3 ente_4 ente_5">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text name=comune_3 readonly id=comune_3 size=20 ondblclick="ricerca_comune(3);" tabindex=5>
			</div>
		</div>
	</div>
</div>

<div class="row ente_4 ente_5">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text name=comune_4 readonly id=comune_4 size=20 ondblclick="ricerca_comune(4);" tabindex=6>
			</div>
		</div>
	</div>
</div>

<div class="row ente_5">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text name=comune_5 readonly id=comune_5 size=20 ondblclick="ricerca_comune(5);" tabindex=7>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Descrizione</label>
			<div class="col-lg-8">
				<input type=text class="form-control resize" name=descrizione id=descrizione size=30 value="" tabindex=8 onblur="focusIndex();">
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<script>
$( document ).ready(function() {

	$('.ente_5').hide();
	$('[tabindex=1]').focus();

});
</script>

<?php include(INC."/footer.php"); ?>
