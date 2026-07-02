<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_authority.php");

$Authority_ID = $cls_help->getVar("Authority_ID");
$cls_authority = new cls_authority();

/*if($Authority_ID == null)
    $a_authority = $cls_db->getColumnsArray("ufficio_giudiziario");
else*/
    $a_authority = $cls_db->getArrayLineNull($cls_db->SelectQuery($cls_authority->getAuthorityquery($Authority_ID)),"ufficio_giudiziario");

if($a_authority['Interno']==0)
    $a_authority['Interno'] = null;
if($a_authority['Civico']==0)
    $a_authority['Civico'] = null;

$a_authorityType = $cls_db->getResults($cls_db->SelectQuery($cls_authority->getAuthorityTypequery()));
$a_selection = array("value"=>"Type","firstOpt"=>1,"selected"=>$a_authority['Tipo'], "text"=>array("[Description]"));
$opt_authorityType = $cls_html->getOptions($a_authorityType,$a_selection);

?>

<!-- Inclusione modale per ricerca ufficio anagrafico -->
<?php include_once (ROOT."/search_modal/offcanvas/authority_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca comune -->
<?php include_once (ROOT."/search_modal/offcanvas/city_offcanvas.php"); ?>

<script>
// Modali offcanvas
function openOfcanvas(type,rif){
    selectRif = rif;
    switch (type){
        case 'citySearchModal':                                                     // ricerca comune
            // Reset campi input
            $('#city').val("");
            // Reset spazi tabella
            $('#appendTableCity').empty();
            // Apertura modale
            $('#citySearchModal').modal('show');
            break;
        case 'authoritySearchModal':                                                     // ricerca autorità
            // Reset campi input
            $('#authority_c').val("");
            // Reset spazi tabella
            $('#appendTableAuthority').empty();
            // Gestione radio
            $('#judge').prop('checked', true);
            $('#court').prop('checked', false);
            $('#tax_prov').attr("checked", false);
            $('#tax_reg').prop('checked', false);
            $('#appeal').prop('checked', false);
            $('#scoi').attr("checked", false);                   // blocca radio filiale
            // Apertura modale
            $('#authoritySearchModal').modal('show');
            break;
    }
}

function initialId(tipo,val){
    switch(tipo){
        case 'city':
            cap = val['cap'];
            for(var contatore=0;contatore<2;contatore++)
            {
                cap = cap.replace("x", "0");
            }

            $('#comune_id').val(val['nome']);
            $('#prov_id').val(val['prov']);
            $('#cap_id').val(cap);
            $('#CC_uff').val(val['CC_C']);

            let event = new Event("change");
            document.getElementById("comune_id").dispatchEvent(event);
            document.getElementById("prov_id").dispatchEvent(event);
            document.getElementById("cap_id").dispatchEvent(event);
            break;
        case "authority":
            location.href = "<?= WEB_ROOT;?>/parametri/authorityOffice.php?c=<?=$c;?>&a=<?=$a;?>&Authority_ID="+val['ID'];
            break;
    }
}

    switchMenuImg("F3");
    F3_button = function(){
        if(validateForm())
            $("#btnSub").trigger("click");
    }

    switchMenuImg("F6");
    F6_button = function(){
        location.href = "<?=WEB_ROOT;?>/parametri/authorityOffice.php?c=<?=$c;?>&a=<?=$a;?>";
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Uffici_Autorita.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Uffici</b>");
        $("#helpModal").modal('show');

    }

    var selectParent = "";
    function searchMunicipality()
    {
        selectParent = "comune";
        var link = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

        openWindowSearch(link,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
    }

    function searchAuthority(){
        selectParent = "authority";

        var link = "<?= WEB_ROOT; ?>/search/ufficio/ricerca_ufficio.php?richiesta=generale&c=*****";

        openWindowSearch(link,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
    }


    function callParent(backValue){
        switch(selectParent){
            case "comune":

                if( backValue!=null && backValue!=undefined )
                {
                    cap = backValue.cap;
                    for(var contatore=0;contatore<2;contatore++)
                    {
                        cap = cap.replace("x", "0");
                    }

                    $('#comune_id').val(backValue.comune);
                    $('#prov_id').val(backValue.prov_sigla);
                    $('#cap_id').val(cap);
                    $('#CC_uff').val(backValue.CC);

                    let event = new Event("change");
                    document.getElementById("comune_id").dispatchEvent(event);
                    document.getElementById("prov_id").dispatchEvent(event);
                    document.getElementById("cap_id").dispatchEvent(event);
                }

                break;

            case "authority":
                if( backValue!=null && backValue!=undefined ) {
                    location.href = "<?= WEB_ROOT;?>/parametri/authorityOffice.php?c=<?=$c;?>&a=<?=$a;?>&Authority_ID="+backValue.ID;
                }
                break;
        }

    }
</script>

<div class="row justify-content-md-center " style="margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Uffici autorita'</span>
	</div>
</div>

<form name=authority_form class="form-horizontal validate" id=authority_form method=post action="authorityOffice_save.php">

<input type=hidden name=Authority_ID value="<?php echo $Authority_ID; ?>" >

<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=CC_Ufficio id=CC_uff value="<?=$a_authority['CC_Ufficio'];?>" >

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize"></label>
			<div class="col-lg-8">
        <input type="button" class="form-control resize btn btn-primary" value="Ricerca" onclick="/*searchAuthority();*/openOfcanvas('authoritySearchModal',0);">
			</div>
		</div>
	</div>
	<div class="col col-lg-5">
		<div class="form-group">
			<label class="col-lg-4 control-label resize"></label>
			<div class="col-lg-8">
        <select name="Authority_Type" class="form-control resize">
            <?=$opt_authorityType;?>
        </select>
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Comune</label>
			<div class="col-lg-8">
        <input class="form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=Comune id=comune_id value="<?php echo $a_authority['Comune'];?>" ondblclick="/*searchMunicipality();*/openOfcanvas('citySearchModal',0);">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Provincia</label>
			<div class="col-lg-8">
        <input class="form-control resize validateCustom vld_Custom_r" style="background-color: #97CFDD; border: 2px solid black; width: 60%;" readonly id=prov_id name=Provincia value="<?php echo $a_authority['Provincia']; ?>">
			</div>
		</div>
	</div>
  <div class="col col-lg-2">
    <div class="form-group">
      <label class="col-lg-4 control-label resize">Cap</label>
      <div class="col-lg-8">
        <input class="form-control resize validateCustom vld_Custom_r vld_Custom_n" id=cap_id name=Cap size=4 value="<?php echo $a_authority['Cap']; ?>">
      </div>
    </div>
  </div>
  <div class="col col-lg-3">
    <div class="form-group">
      <label class="col-lg-4 control-label resize">Sezione</label>
      <div class="col-lg-8">
        <input class="form-control resize" id=sezione_id name=Sezione value="<?php echo $a_authority['Sezione']; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Indirizzo</label>
			<div class="col-lg-8">
        <input id=via class="form-control resize vld_req" name=Toponimo type=text value="<?php echo $a_authority['Toponimo']; ?>">
      </div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Civ.</label>
			<div class="col-lg-8">
        <input type="text" id=civico class="form-control resize vld_intReq" name="Civico" value="<?php echo $a_authority['Civico']; ?>">
      </div>
		</div>
	</div>
  <div class="col col-lg-1">
    <div class="form-group">
      <label class="col-lg-4 control-label resize">Esp.</label>
      <div class="col-lg-8">
        <input type="text" id=esponente  class="form-control resize vld_esp" name="Esponente" value="<?php echo $a_authority['Esponente']; ?>"  size=2>
      </div>
    </div>
  </div>
  <div class="col col-lg-1">
    <div class="form-group">
      <label class="col-lg-4 control-label resize">Int.</label>
      <div class="col-lg-8">
        <input type="text" id=interno    class="form-control resize vld_int" name="Interno" value="<?php echo $a_authority['Interno']; ?>"  size=2>
      </div>
    </div>
  </div>
  <div class="col col-lg-4">
    <div class="form-group">
      <label class="col-lg-4 control-label resize">Dettagli</label>
      <div class="col-lg-8">
        <input type="text" id=dettagli class="form-control resize" name="Dettagli" value="<?php echo $a_authority['Dettagli']; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Telefono</label>
			<div class="col-lg-8">
        <input class="form-control resize vld_tel" id=tel_id name=Telefono class="width100" value="<?php echo $a_authority['Telefono']; ?>">
      </div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Fax</label>
			<div class="col-lg-8">
        <input class="form-control resize vld_tel" id=fax_id name=Fax size=18 value="<?php echo $a_authority['Fax']; ?>" ondblclick="controllaCampi();">
      </div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Email</label>
			<div class="col-lg-8">
        <input class="form-control resize vld_email" id=email_id name=Mail value="<?php echo $a_authority['Mail']; ?>">
      </div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">PEC</label>
			<div class="col-lg-8">
        <input class="form-control resize vld_email" id=pec_id name=PEC size=18 value="<?php echo $a_authority['PEC']; ?>">
      </div>
		</div>
	</div>
  <div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize">Sito</label>
			<div class="col-lg-8">
        <input class="form-control resize vld_req" id=sito_id name=Sito value="<?php echo $a_authority['Sito']; ?>">
      </div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%;"></div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<?php

include(INC."/footer.php");

?>
