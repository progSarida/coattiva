<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_city.php");

if(!isset($_SESSION['username']))
{
	header("Location: /gitco2/autenticazione/accesso_negato.php");
	die;
}


$cls_city = new cls_city();

if($a_enteAdmin["Codice_290"]==00000)
	$a_enteAdmin["Codice_290"] = null;

$selectGeneralTax = "";
$selectDistinctTax = "";

if($a_enteAdmin["Select_Tax"]==1)
    $selectDistinctTax = "selected";
else if($a_enteAdmin["Select_Tax"]==2)
    $selectGeneralTax = "selected";

if($a_enteAdmin["Info_Interno"]==0) $a_enteAdmin["Info_Interno"]="";
if($a_enteAdmin["Info_Civico"]==0)  $a_enteAdmin["Info_Civico"]="";

$opt_Select_Tax = "<option></option><option ".$selectDistinctTax." value=1>Divise in categorie</option><option ".$selectGeneralTax." value=2>Nessuna distinzione</option>";

$lay_cap = "";
$lay_istat = "";
$lay_pro = "";
$Pro_Sigla = "";
$a_htmlData = array();

if($a_enteAdmin["Codici_Unione"]!="")
{
    $layout_comune = "<select id=comune name=comune onchange='scegli_comune();'>";
    $layout_comune .= "<option value=''></option>";

    $codici = explode("/", $a_enteAdmin["Codici_Unione"]);
    $com = array();

    ?>
    <!--<script>
        var istat = new Array();
        var cap = new Array();
        var prov = new Array();
        var sigla = new Array();
    </script>-->
    <?php

	for($i=0;$i<count($codici);$i++)
	{
        $a_city = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_city->getCityProvince_query($codici[$i])));

/*********************************************RIEMPE ARRAY*********************************************/
			$a_htmlData[$codici[$i]] = array(
									"istat"=>$a_city['Com_Codice'],
									"cap"=>$a_city['Com_Cap'],
									"prov"=>$a_city['Pro_Nome'],
									"sigla"=>$a_city['Pro_Sigla']
							);
/******************************************************************************************************/

        ?>
        <!--<script>

            istat['<?php //echo $codici[$i]; ?>'] = "<?php //echo $a_city['Com_Codice']; ?>";
            cap['<?php //echo $codici[$i]; ?>'] = "<?php //echo $a_city['Com_Cap']; ?>";
            prov['<?php //echo $codici[$i]; ?>'] = "<?php //echo $a_city['Pro_Nome']; ?>";
            sigla['<?php //echo $codici[$i]; ?>'] = "<?php //echo $a_city['Pro_Sigla']; ?>";
        </script>-->
        <?php

		$layout_comune .= "<option value='".$codici[$i]."' ";
        if($codici[$i]==$a_enteAdmin["Info_Comune"]){
            $layout_comune .= "SELECTED ";
            $lay_cap = $a_city['Com_Cap'];
            $lay_istat = $a_city['Com_Codice'];
            $lay_prov = $a_city['Pro_Nome'];
            $Pro_Sigla = $a_city['Pro_Sigla'];
        }
        $layout_comune .= ">".$a_city['Com_Nome']."</option>";
	}
}
else
{
    $a_city = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_city->getCityProvince_query($a_enteAdmin['CC'])));
	$layout_comune = '<input class="sfondo_azzurro" readonly name=comune id=comune value="'.$a_city['Com_Nome'].'">';
	$lay_cap = $a_city['Com_Cap'];
	$lay_istat = $a_city['Com_Codice'];
	$lay_prov = $a_city['Pro_Nome'];
	$Pro_Sigla = $a_city['Pro_Sigla'];
}
?>

<script>
//F3
switchMenuImg("F3");
F3_button = function()
{
	control_salva = submit_buttons('Salva');
	if(control_salva)
			$("#form_ente").submit();
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "dati_ente.php?"+stringaPHP;
	   	top.location.href = stringa;
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "stemma.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "gestore.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

function scegli_comune()
{
    value = $('#comune').val();
		//alert(value);
    $('#CC').val(value);

		var a_htmlData = <?php echo json_encode($a_htmlData); ?>;
    /*$('#istat_id').val(istat[value]);
    $('#cap_id').val(cap[value]);
    $('#prov_id').val(prov[value]);
    $('#pro_sigla').val(sigla[value]);*/

		$('#istat_id').val(a_htmlData[value]['istat']);
    $('#cap_id').val(a_htmlData[value]['cap']);
    $('#prov_id').val(a_htmlData[value]['prov']);
    $('#pro_sigla').val(a_htmlData[value]['sigla']);

}
/*****************************STAMPA ARRAY************************************/

/*console.log(a_htmlData);
alert(a_htmlData['B067']['istat']);*/
/*****************************************************************************/
</script>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><span class="titolo font16 under_decor">Dati Ente</span></td>
	</tr>
</table>

<form name=form_ente id=form_ente method=post action="dati_ente_salva.php">
<!-- ********************************** FORSE SI PUò ELIMINARE ******************************-->
<input type=hidden name=invia_submit id=invia_submit value="" >
<!-- ****************************************************************-->

<input type=hidden name=info_id id=info_id value="<?php echo $a_enteAdmin["Info_ID"]; ?>" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC id=CC value="<?php echo $c; ?>" >
<input type=hidden name=Pro_Sigla	id=pro_sigla	value=<?php echo $Pro_Sigla; ?> >

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr><td colspan=8><hr></td></tr>
	<tr>
		<td class="text_left width10">Comune</td>
		<td class="text_left width22"><?php echo $layout_comune; ?></td>
		<td class="text_left width15">Provincia</td>
		<td class="text_left width15"><input class="sfondo_azzurro text_left" readonly id=prov_id name=prov size=9 value="<?php echo $lay_prov; ?>"></td>
		<td class="text_left width8">CAP</td>
		<td class="text_left width10"><input class="sfondo_azzurro text_center" readonly id=cap_id name=cap size=4 value="<?php echo $lay_cap; ?>"></td>
		<td class="text_left width10">ISTAT</td>
		<td class="text_left width10"><input class="sfondo_azzurro text_center" readonly id=istat_id name=istat size=5 value="<?php echo $lay_istat; ?>"></td>
	</tr>
	<tr>
		<td class="text_left width10">Indirizzo</td>
		<td class="text_left width22">
        	<input id=via class="text_left" name=via type=text value="<?php echo $a_enteAdmin["Info_Via"]; ?>" size=18 >
        </td>
		<td class="text_left width68" colspan=6>Civ.
		&nbsp;<input type="text" id=civico 	   class="text_right"  name="civico"  	value="<?php echo $a_enteAdmin["Info_Civico"]; ?>"  size=2 >
		&nbsp;Esp.
		&nbsp;<input type="text" id=esponente  class="text_left"   name="esponente" value="<?php echo $a_enteAdmin["Info_Esponente"]; ?>"  size=2 >
		&nbsp;Int.
		&nbsp;<input type="text" id=interno    class="text_right"  name="interno" 	value="<?php echo $a_enteAdmin["Info_Interno"]; ?>"  size=2 >
		&nbsp;Dettagli
		&nbsp;<input type="text" id=dettagli   class="text_left"   name="dettagli" 	value="<?php echo $a_enteAdmin["Info_Dettagli"]; ?>"  size=20>
		</td>
	</tr>
	<tr>
		<td class="text_left width10">Partita Iva</td>
		<td class="text_left width22"><input class="text_right" maxlength=11 id=PI_id name=PI size=11 value="<?php echo $a_enteAdmin["Info_PI"]; ?>" ></td>
		<td class="text_left width15">Codice Fiscale</td>
		<td class="text_left width23" colspan=2><input class="text_left" maxlength=16 id=CF_id name=CF size=20 value="<?php echo $a_enteAdmin["Info_CF"]; ?>" ></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
	</tr>
	<tr>
		<td class="text_left width10">Telefono</td>
		<td class="text_left width22"><input class="text_right" id=tel_id name=tel size=18 value="<?php echo $a_enteAdmin["Info_Telefono"]; ?>"></td>
		<td class="text_left width15">Fax</td>
		<td class="text_left width23" colspan=2><input class="text_right" id=fax_id name=fax size=18 value="<?php echo $a_enteAdmin["Info_Fax"]; ?>" ondblclick="controllaCampi();"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
		<td class="text_left width10"></td>
	</tr>
	<tr>
		<td class="text_left width10">Email</td>
		<td class="text_left width22"><input class="text_left" id=email_id name=email size=18 value="<?php echo $a_enteAdmin["Info_Mail"]; ?>" ></td>
		<td class="text_left width15">PEC</td>
		<td class="text_left width23" colspan=2><input class="text_left" id=pec_id name=PEC size=18 value="<?php echo $a_enteAdmin["Info_PEC"]; ?>" ></td>
		<td class="text_left width10">Sito</td>
		<td class="text_left width20" colspan=2><input class="text_left" id=sito_id name=sito size=16 value="<?php echo $a_enteAdmin["Info_Sito"]; ?>" ></td>
	</tr>
	<tr><td colspan=8><hr></td></tr>
	<tr>
		<td class="text_left width32" colspan=2>Codice ente 290</td>
		<td class="text_left width15"><input class="text_left" id=codice_290 name=codice_290 size=8 value="<?php echo $a_enteAdmin["Codice_290"]; ?>" ></td>
		<td class="text_left width23" colspan=2></td>
		<td class="text_left width10"></td>
		<td class="text_left width20" colspan=2></td>
	</tr>
    <tr>
        <td class="text_left width32" colspan=3>Gestione entrate in fase di elaborazione e stampa</td>
        <td class="text_left" colspan=3><select id=Select_Tax name=Select_Tax class="width90">
                <?=$opt_Select_Tax;?>
            </select></td>
        <td class="text_left" ></td>
    </tr>
    <tr><td colspan=8><hr></td></tr>
</table>

</form>

<?php include(INC."/footer.php"); ?>
