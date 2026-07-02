<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");


//PRINT_R($a_enteAdmin);



//get_var('servizio');

//$comune = new ente_gestito($c);//caricata la classe da comuni.php (get_var('c');) --> cls_help->get_var('c');
//$codice_290 = $a_enteAdmin["Codice_290"];//eliminare tutte le variabili e usare l'array
if($a_enteAdmin["Codice_290"]==00000)
	$a_enteAdmin["Codice_290"] = null;
//$info = new gestore($a_enteAdmin['Info_ID']);//$comune->Info;
$selectGeneralTax = "";
$selectDistinctTax = "";

if($a_enteAdmin["Select_Tax"]==1)
    $selectDistinctTax = "selected";
else if($a_enteAdmin["Select_Tax"]==2)
    $selectGeneralTax = "selected";

if($a_enteAdmin["Info_Interno"]==0)$a_enteAdmin["Info_Interno"]="";
if($a_enteAdmin["Info_Civico"]==0)$a_enteAdmin["Info_Civico"]="";

$opt_Select_Tax = "<option></option><option ".$selectDistinctTax." value=1>Divise in categorie</option><option ".$selectGeneralTax." value=2>Nessuna distinzione</option>";

$nome_comune =($a_enteAdmin["Denominazione"]==NULL?"":$a_enteAdmin["Denominazione"]." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$lay_cap = "";
$lay_istat = "";
$lay_pro = "";
$Pro_Sigla = "";

if( substr($a_enteAdmin["CC"], 0,1) == 'U' || $a_enteAdmin["CC"]=="ZZZZ")
{

	$layout_comune = "<select id=comune name=comune onchange='scegli_comune();'>";
	$layout_comune .= "<option value=''></option>";

	$codici = explode("/", $a_enteAdmin["Codici_Unione"]);
	$com = array();

	?>

	<script>
	var istat = new Array();
	var cap = new Array();
	var prov = new Array();
	var sigla = new Array();
	</script>

	<?php


	for($i=0;$i<count($codici);$i++)
	{
		$query = "SELECT C.*, P.Pro_Nome, P.Pro_Sigla FROM comuni_lista as C join province_lista as P on C.Com_Codice_Provincia = P.Pro_Codice WHERE C.Com_Codice_Catastale = '".$codici[$i]."'";

		//$commandDB = new cls_db();
		//$commandDB->connect();
		$result = $cls_db->ExecuteQuery($query);

		$a_city = $cls_db->getArrayLine($result);//mysqli_fetch_array($result);
		//array_push($com,new comune($a_city['Com_Codice'],$a_city['Com_Cap'],$a_city['Pro_Nome']));//vedere $val["Com_Nome"] dovrebbe essere la provincia, forse ---> $a_enteAdmin["Info_Provincia"];
?>
		<script>
		istat['<?php echo $codici[$i]; ?>'] = "<?php echo $a_city['Com_Codice']; ?>";
		cap['<?php echo $codici[$i]; ?>'] = "<?php echo $a_city['Com_Cap']; ?>";
		prov['<?php echo $codici[$i]; ?>'] = "<?php echo $a_city['Pro_Nome']; ?>";
		sigla['<?php echo $codici[$i]; ?>'] = "<?php echo $a_city['Pro_Sigla']; ?>";
		</script>
	<?php	//$comune = new comune($codici[$i]);
		//echo "<h1>".$codici[$i]." ".$comune->Nome."</h1>";

		$layout_comune .= "<option value='".$codici[$i]."' ";
        if($codici[$i]==$a_enteAdmin["Info_Comune"])
            $layout_comune .= "SELECTED ";
        $layout_comune .= ">".$a_city['Com_Nome']."</option>";
	}

	//echo"</br></br>";
	//print_r($com);

}
else
{
	$query = "SELECT C.*, P.Pro_Nome, P.Pro_Sigla FROM comuni_lista as C join province_lista as P on C.Com_Codice_Provincia = P.Pro_Codice WHERE C.Com_Codice_Catastale = '".$a_enteAdmin['CC']."'";
	$result = $cls_db->ExecuteQuery($query);
	$a_city = $cls_db->getArrayLine($result);

	$layout_comune = '<input class="sfondo_azzurro" readonly name=comune id=comune value="'.$a_city['Com_Nome'].'">';
	$lay_cap = $a_city['Com_Cap'];
	$lay_istat = $a_city['Com_Codice'];
	$lay_prov = $a_city['Pro_Nome'];
	$Pro_Sigla = $a_city['Pro_Sigla'];
}
?>




<!-- FINO A QUI -->
<script>
$( document ).ready(function() {
	<?php if(substr($a_enteAdmin["CC"], 0,1) == 'U') : ?>
		scegli_comune();
	<?php endif; ?>
});






</script>

<script>


function scegli_comune()
{
	//alert("qui"+$('#comune').val());
	value = $('#comune').val();

	$('#CC').val(value);

	$('#istat_id').val(istat[value]);
	$('#cap_id').val(cap[value]);
	$('#prov_id').val(prov[value]);
	$('#pro_sigla').val(sigla[value]);

}

//CONTROLLO CAMPI
/*function controllaCampi ()
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

}*/

</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

/*$(document).ready(function(){

	$('#form_ente').ajaxForm(

	    function(value) {
//		    alert(value);
	        var array_ritorno = value.split(' ');

		if(array_ritorno[0]=='SAVED')
		{
			alert('Dati ente salvati correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert('Salvataggio dati ente fallito! '+array_ritorno[1]);
		}
		if(array_ritorno[0]=='DELETED')
		{
			alert('Dati ente cancellati correttamente!');
			annulla();
		}
		else if(array_ritorno[0]=='ERROR_DELETE')
		{
			alert('Cancellazione dati ente fallita! '+array_ritorno[1]);
		}

	    });*/

//});

</script>


<body class="sfondo_new_gitco" >

<?php

include(INC."/menu.php");

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

</script>



<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Dati Ente</font></td>
	</tr>
</table>

<form name=form_ente id=form_ente method=post action="dati_ente_salva.php">




<input type=hidden name=invia_submit id=invia_submit value="" >
<input type=hidden name=info_id id=info_id value="<?php echo $a_enteAdmin["Info_ID"]; ?>" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC id=CC value="<?php echo $c; ?>" >
<!-- SI PUò ELIMINARE-->
<input type=hidden name=Pro_Sigla	id=pro_sigla	value=<?php echo $Pro_Sigla; ?> >
<!-- ****** -->
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

<?php

include(INC."/footer.php");

?>
