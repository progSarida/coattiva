<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");


if(!isset($_SESSION['username']))
{
	header("Location: ".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$cls_help = new cls_help();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$gestore_ID = $a_enteAdmin["Gestore_ID"];

$int = $a_enteAdmin["Gestore_Interno"];
$civ = $a_enteAdmin["Gestore_Civico"];

if($int==0)$int="";
if($civ==0)$civ="";



?>

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
	strDim = Dim_Alert(600, 300);

	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
}

</script>

<!-- ********** CONTROLLI, AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>

function radioClick(value)
{
	if(value==1)
	{
		$('#gestore').show();
		$('#ente_lay').hide();
	}
	else
	{
		$('#gestore').hide();
		$('#ente_lay').show();
	}
}

</script>

<?php

include(INC."/menu.php");

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>


switchMenuImg("F3");
F3_button = function()
{
	control_salva = submit_buttons('Salva');
	if(control_salva)
			$("#btnSub").trigger("click");
}

switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "gestore.php?"+stringaPHP;
	   	top.location.href = stringa;
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "dati_ente.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "ufficio.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


</script>


<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Gestore</p>
	</div>
</div>
<form class="form-horizontal validate" name=form_gestore id=form_gestore method=post action="gestore_salva.php">

<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=gestore_id  value="<?php echo $gestore_ID; ?>" >

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $a_enteAdmin["CC"]; ?>">

	<div class="row">
		<div class="col-lg-4 col-lg-offset-4">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;"><b>Selezione:</b></label>
				<div class="col-lg-8 resize">
		       <input type="radio" id=sel_ente name="selezione" value="C" onclick="radioClick(0);"> <label class="control-label">Ente</label></input>
		       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		  		 <input type="radio" id=sel_gestore name="selezione" value="G" onclick="radioClick(1);"> <label class="control-label">Gestore</label></input>
	      </div>
			</div>
		</div>
	</div>

<div class="row justify-content-md-center" id=ente_lay style="margin-top: 3%;">
	<div class="col col-md-auto text_center resize">
			<p class="color_red">L'ente è il gestore. Per verificare le informazioni accedere dal punto menù Parametri a Dati Ente</p>
		</br>
		<p class="color_red">Per inserire un nuovo gestore diverso dall'ente selezionare l'apposito radiobutton sopra.</p>
	</div>
</div>

<div id=gestore style="margin-top: 1%;">

	<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

	<div class="row" style="margin-top: 1%;">
		<div class="col col-lg-5 col-lg-offset-1" >
			<div class="form-group">
				<label class="col-lg-4 control-label resize " style="text-align: left;">Denominazione</label>
				<div class="col-lg-8">
		       <input style="width: 60%;" class="text_left form-control vld_req resize" id=denom_id name=denom value="<?php echo $a_enteAdmin["Gestore_Denominazione"]; ?>" >
	      </div>
			</div>
		</div>
	</div>

	<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

	<div class="row" style="margin-top: 1%;">
		<div class="col col-lg-3 col-lg-offset-1" >
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Comune</label>
				<div class="col-lg-8">
					 <input style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" class=" text_left form-control resize" readonly name=comune id=comune_id value="<?php echo $a_enteAdmin["Gestore_Comune"]; ?>" size=15 onclick="cerca_comune();">
	      </div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Provincia</label>
				<div class="col-lg-8 ">
					 <input style="width: 50%; background-color: #97CFDD; border: 2px solid black;" class=" text_left form-control resize" readonly id=prov_id name=prov size=1 value="<?php echo $a_enteAdmin["Gestore_Provincia"]; ?>">
	      </div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">CAP</label>
				<div class="col-lg-8">
					 <input style="width: 60%; background-color: #97CFDD; border: 2px solid black;"  class=" text_center form-control resize" readonly id=cap_id name=cap size=4 value="<?php echo $a_enteAdmin["Gestore_Cap"]; ?>">
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo</label>
				<div class="col-lg-8">
					 <input id=via class="text_left form-control vld_req resize" name=via type=text value="<?php echo $a_enteAdmin["Gestore_Via"]; ?>">
			  </div>
			</div>
		</div>
		<div class="col col-lg-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Civ.</label>
				<div class="col-lg-8">
					 <input type="text" id=civico class="text_right form-control resize vld_intReq"  name="civico"  	value="<?php echo $civ; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-2">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Esp.</label>
				<div class="col-lg-8">
					 <input type="text" id=esponente  class="text_left form-control vld_esp resize" style="width: 50%;" name="esponente" value="<?php echo $a_enteAdmin["Gestore_Esponente"]; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Int.</label>
				<div class="col-lg-8">
					 <input type="text" id=interno class="text_right form-control resize vld_int"  name="interno" 	value="<?php echo $int; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Dettagli</label>
				<div class="col-lg-8">
					 <input type="text" id=dettagli class="text_left form-control resize" name="dettagli" value="<?php echo $a_enteAdmin["Gestore_Dettagli"]; ?>">
				</div>
			</div>
		</div>
	</div>

	<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

	<div class="row" style="margin-top: 1%;">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva</label>
				<div class="col-lg-8">
					 <input style="width: 100%;" class="text_left form-control vld_PI resize" id=PI_id name=PI size=11 value="<?php echo $a_enteAdmin["Gestore_PI"]; ?>" >
			  </div>
			</div>
		</div>
		<div class="col col-lg-4">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Codice Fiscale</label>
				<div class="col-lg-8">
					 <input style="width: 85%;" class="text_left form-control vld_CF resize" id=CF_id name=CF size=20 value="<?php echo $a_enteAdmin["Gestore_CF"]; ?>" >
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
				<div class="col-lg-8">
					 <input style="width: 100%;" class="text_left form-control vld_tel resize" id=tel_id name=tel size=18 value="<?php echo $a_enteAdmin["Gestore_Telefono"]; ?>">
			  </div>
			</div>
		</div>
		<div class="col col-lg-4">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
				<div class="col-lg-8">
					 <input style="width: 85%;" class="text_left form-control vld_tel resize" id=fax_id name=fax size=18 value="<?php echo $a_enteAdmin["Gestore_Fax"]; ?>">
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
				<div class="col-lg-8">
					 <input class="text_left form-control resize vld_email" id=email_id name=email size=18 value="<?php echo $a_enteAdmin["Gestore_Mail"]; ?>" >
			  </div>
			</div>
		</div>
		<div class="col col-lg-4">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
				<div class="col-lg-8">
					 <input class="text_left form-control resize vld_email" style="width: 85%;" id=pec_id name=PEC value="<?php echo $a_enteAdmin["Gestore_PEC"]; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize">Sito</label>
				<div class="col-lg-8">
					 <input class="text_left form-control vld_Sito resize" id=sito_id name=sito size=16 value="<?php echo $a_enteAdmin["Gestore_Sito"]; ?>" >
				</div>
			</div>
		</div>
	</div>

	<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>


<script type="text/javascript">
	$( window ).load(function() {
		if("<?= $gestore_ID; ?>" == 0)
		{
			$('#sel_gestore').prop('checked',false);
			$('#sel_ente').prop('checked',true);
			$('#gestore').hide();
			$('#ente_lay').show();
		}
		else
		{
			$('#sel_ente').prop('checked',false);
			$('#sel_gestore').prop('checked',true);
			$('#ente_lay').hide();
			$('#gestore').show();
		}
	});
</script>

<?php include(INC."/footer.php"); ?>
