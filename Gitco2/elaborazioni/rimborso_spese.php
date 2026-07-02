<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_paramUtils.php");
include_once CLS ."/cls_DateTime.php";

$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
//$data_stampa = $cls_help->getVar('data_stampa');

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));
$sel = "";

for($i=0; $i < count($resIngiunzioni) ; $i++) {
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "' ".$sel.">" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";
}

//TODO INSERIRE MESSAGGIO SPIEGAZIONE ELABORAZIONE ART 17
?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="rimborso_spese.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        //alert("F10");
        if(validateForm())
            ajaxCall();
            //$("#f_rimborso_spese").submit();
    }

    function recallNewYear(el){
        if(el.value.length != 4)
            return false;

        //alert("hohoho!");
        $.ajax({
            type: "POST",
            url: "ajax/gestione_data_stampa.php",
            data:
                {
                    anno : el.value,
                    data_stampa: $("#data_stampa").val()
                },
            dataType: "json",
            success: function(response){
                console.log(response);
                //alert("hohì!");
                $("#data_stampa_rif").val(response.data_ita);
                $("#data_stampa").val(response.data);
                //resetErrorOnID("data_stampa");
            },
            error: function(error){
                console.log(error)
            }
        });
    }

    $(document).ready(function(){
        recallNewYear(document.getElementById("anno_fornitura"));
        //document.getElementById("anno_fornitura").dispatchEvent(new Event("blur"));
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
    })

    function ajaxCall() {
        //alert("");
		spinner.startSpinner();
		//alert("ajax");
		//return;
        $.ajax({
            //url: "print_storico.php",
            url: $("form").attr('action'),
            //data: new FormData(document.getElementById("storico_form")),
            data: $("form").serialize(),
            dataType : 'json',
            type: 'POST',
            success: function (resp) {
                spinner.closeSpinner();
                ShowAlert(resp.error,resp.msg);
                if(resp.error == 0)
                    showFileOnModal(resp.path,"Rimborso spese",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
	}
    
</script>


<form id="f_rimborso_spese" name="rimborso_spese_form" action="print_rimborso_spese.php" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />
    <input type=hidden id="data_stampa_rif" name="data_stampa_rif" value="" /><!--<?=$cls_help->toItalianDate($data_stampa)?>-->

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampa rimborso spese</span>
        </div>
    </div>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Selezione atti</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">

        <div class="col-lg-offset-1 col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno fornitura</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="anno_fornitura" name="anno_fornitura" value="<?=$a?>" maxlength="4" onblur="recallNewYear(this);">
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printType" name="printType" tabindex=9 class="form-control resize">
                        <option value="temp">Provvisoria</option>
                        <option value="final">Definitiva</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-5 control-label resize" style="text-align: left;">Data Stampa</label>
            <div class="form-group">
                <div class="col-lg-7">
                    <input class="form-control validateCustom vld_CheckPrintDate" type="date" id="data_stampa" name="data_stampa" value="">
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="partita_da" name="partita_da" class="form-control resize">
                        <option value=""></option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Da anno rif.</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="anno_rif_da" name="anno_rif_da" value="" maxlength="4">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">A partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="partita_a" name="partita_a" class="form-control resize">
                        <option value=""></option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">A anno rif.</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="anno_rif_a" name="anno_rif_a" value="" maxlength="4">
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row" style="margin-top: 2%;">

    </div>

</form>


<?php include(INC."/footer.php"); ?>
