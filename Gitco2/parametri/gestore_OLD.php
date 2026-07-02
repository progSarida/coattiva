<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");


if(!isset($_SESSION['username']))
{
	header("Location: /gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

//$comune = new ente_gestito($c);
$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

//$gestore = $comune->Gestore;
$gestore_ID = $a_enteAdmin["Gestore_ID"];

//print_r($a_enteAdmin);

if( $gestore_ID == 0 )
{

	$layout = "<script>$('#sel_gestore').prop('checked',false);$('#sel_ente').prop('checked',true);</script>";
	$layout .= "<script>$('#gestore').hide();$('#ente_lay').show();</script>";
}
else
{
	$layout = "<script>$('#sel_ente').prop('checked',false);$('#sel_gestore').prop('checked',true);</script>";
	$layout .= "<script>$('#ente_lay').hide();$('#gestore').show();</script>";
}

$int = $a_enteAdmin["Gestore_Interno"];
$civ = $a_enteAdmin["Gestore_Civico"];

if($int==0)$int="";
if($civ==0)$civ="";



?>
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

	var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=ricComune";

	valorediritorno = window.showModalDialog(stringa, "", strDim);
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

<!--<body class="sfondo_new_gitco" >-->

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
			$("#form_gestore").submit();
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



<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0" style="margin-bottom: 2%;">
	<tr>
		<td><font class="titolo font16 under_decor">Gestore</font></td>
	</tr>
</table>

<form name=form_gestore id=form_gestore method=post action="gestore_salva.php">

<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=gestore_id  value="<?php echo $gestore_ID; ?>" >

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $a_enteAdmin["CC"]; ?>">


<!-- ******************** RADIO BUTTON ****************** -->

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_left width15"><b>Selezione:</b></td>
		<td class="text_left width21">Ente <input type="radio" id=sel_ente name="selezione" value="C" onclick="radioClick(0);"></td>
		<td class="text_left width24">Gestore <input type="radio" id=sel_gestore name="selezione" value="G" onclick="radioClick(1);"></td>
		<td class="text_left width40"></td>
	</tr>
</table>

<br>

<!--  ************************************* QUESTA APPARE SCOMPARE *************************************** -->

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0" id=ente_lay>
	<tr>
		<td class="text_left">
			<font class=color_red >L'ente � il gestore. Per verificare le informazioni accedere dal punto menu' Parametri a Dati Ente</font>
			<br><br>
			<font class=color_red >Per inserire un nuovo gestore diverso dall'ente selezionare l'apposito radiobutton sopra.</font>
		</td>
	</tr>
</table>


<!--  ************************************* QUESTA APPARE SCOMPARE *************************************** -->


<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0" id=gestore>
	<tr>
		<td class="text_left width15">Denominazione</td>
		<td class="text_left width37" colspan=2><input class="text_left" id=denom_id name=denom size=20 value="<?php echo $a_enteAdmin["Gestore_Denominazione"]; ?>" ></td>
		<td class="text_left width23" colspan=2></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Comune</td>
		<td class="text_left width22"><input class="sfondo_azzurro text_left" readonly name=comune id=comune_id value="<?php echo $a_enteAdmin["Gestore_Comune"]; ?>" size=15 onclick="cerca_comune();"></td>
		<td class="text_left width15">Provincia</td>
		<td class="text_left width15"><input class="sfondo_azzurro text_left" readonly id=prov_id name=prov size=1 value="<?php echo $a_enteAdmin["Gestore_Provincia"]; ?>"></td>
		<td class="text_left width8" >CAP</td>
		<td class="text_left width10"><input class="sfondo_azzurro text_center" readonly id=cap_id name=cap size=4 value="<?php echo $a_enteAdmin["Gestore_Cap"]; ?>"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Indirizzo</td>
		<td class="text_left width22">
        	<input id=via class="text_left" name=via type=text value="<?php echo $a_enteAdmin["Gestore_Via"]; ?>" size=18>
        </td>
		<td class="text_left width63" colspan=6>Civ.
		&nbsp;<input type="text" id=civico 	   class="text_right"  name="civico"  	value="<?php echo $civ; ?>"  size=2 >
		&nbsp;Esp.
		&nbsp;<input type="text" id=esponente  class="text_left"   name="esponente" value="<?php echo $a_enteAdmin["Gestore_Esponente"]; ?>"  size=2 >
		&nbsp;Int.
		&nbsp;<input type="text" id=interno    class="text_right"  name="interno" 	value="<?php echo $int; ?>"  size=2 >
		&nbsp;Dettagli
		&nbsp;<input type="text" id=dettagli   class="text_left"   name="dettagli" 	value="<?php echo $a_enteAdmin["Gestore_Dettagli"]; ?>"  size=20>
		</td>
	</tr>
	<tr>
		<td class="text_left width10">Partita Iva</td>
		<td class="text_left width22"><input class="text_right" maxlength=11 id=PI_id name=PI size=11 value="<?php echo $a_enteAdmin["Gestore_PI"]; ?>" ></td>
		<td class="text_left width15">Codice Fiscale</td>
		<td class="text_left width23" colspan=2><input class="text_left" maxlength=16 id=CF_id name=CF size=20 value="<?php echo $a_enteAdmin["Gestore_CF"]; ?>" ></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Telefono</td>
		<td class="text_left width22"><input class="text_right" id=tel_id name=tel size=18 value="<?php echo $a_enteAdmin["Gestore_Telefono"]; ?>"></td>
		<td class="text_left width15">Fax</td>
		<td class="text_left width23" colspan=2><input class="text_right" id=fax_id name=fax size=18 value="<?php echo $a_enteAdmin["Gestore_Fax"]; ?>"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Email</td>
		<td class="text_left width22"><input class="text_left" id=email_id name=email size=18 value="<?php echo $a_enteAdmin["Gestore_Mail"]; ?>" ></td>
		<td class="text_left width30" colspan=2>PEC
		&nbsp;&nbsp;&nbsp;&nbsp;<input class="text_left" id=pec_id name=PEC size=18 value="<?php echo $a_enteAdmin["Gestore_PEC"]; ?>" ></td>
		<td class="text_left width8">Sito</td>
		<td class="text_left width25" colspan=3><input class="text_left" id=sito_id name=sito size=16 value="<?php echo $a_enteAdmin["Gestore_Sito"]; ?>" ></td>
	</tr>
</table>


</form>


<?php echo $layout;

include(INC."/footer.php");
?>
