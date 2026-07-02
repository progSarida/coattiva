<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');


$serieOption = "";
$queryYear = "SELECT Anno from anni_gestiti WHERE CC_Anno = '" . $c . "' ORDER BY Anno DESC";
$resYear = $cls_db->getResults($cls_db->ExecuteQuery($queryYear));

for($i=0; $i < count($resYear) ; $i++) {
    $serieOption .= "<option value='" . $resYear[$i]['Anno'] . "'>" . $resYear[$i]['Anno'] . "</option>";
}

//TODO INSERIRE MESSAGGIO SPIEGAZIONE ELABORAZIONE ART 17
?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="filtri_conto_giudiziale.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        if(validateForm())
            ajaxCall();
            //$("#btn_sub").trigger("click");
    }

    $(document).ready(function () {
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
        var urlFile = getParameterByName("urlFile");
        if(urlFile != null && urlFile != '')
            window.open(urlFile,"_blank");
    });

    function ajaxCall() {
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
                    showFileOnModal(resp.path,"Conto giudiziale",resp.path.split('.').pop());
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


<form id="giudiziale_form" name="giudiziale_form" class="validate" action="print_conto_giudiziale.php" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />

    <button type="submit" style="display: none;" id="btn_sub"></button>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampa conto giudiziale</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">

        <div class="col-lg-offset-1 col col-lg-3">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="anno" name="anno" tabindex=9 class="form-control resize vld_req">
                        <option value="" style="font-weight: bold; color: #0a53be;">Seleziona Anno</option>
                        <?php echo $serieOption; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printType" name="printType" tabindex=9 class="form-control resize vld_req">
                        <option value="temp">Provvisoria</option>
                        <option value="final">Definitiva</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data Stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize vld_req validateCustom vld_CheckPrintDate_2" type="date" id="data_stampa" name="data_stampa" value="<?=date('Y-m-d');?>">
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <!--<div class="row">
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Numero registrazioni</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize vld_intReq" type="text" id="num_registr" name="num_registr" value="">
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Numero pagine</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize vld_intReq" type="text" id="num_page" name="num_page" value="">
                </div>
            </div>
        </div>
    </div>-->
    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <label class="col-lg-2 control-label resize" style="text-align: left;">Note</label>
            <div class="form-group">
                <div class="col-lg-10">
                    <textarea class="form-control resize" id="note" name="note" style="max-width: 100%;"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>
</form>


<?php include(INC."/footer.php"); ?>
