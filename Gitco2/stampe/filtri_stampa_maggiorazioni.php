<?php

require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";


include(INC."/header.php");
include(INC."/menu.php");

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

?>
<script>
    //F8
    switchMenuImg("F10");
    F10_button = function()
    {
        //$("#form_magg").submit();
        ajaxCall();
    }

    $(document).ready(function(){
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
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
                    showFileOnModal(resp.path,"Maggiorazioni",resp.path.split('.').pop());
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

<form action="stampa_maggiorazioni_exe.php" method="post" id="form_magg">

    <input type="hidden" value="<?= $c; ?>" name="c">
    <input type="hidden" value="<?= $a; ?>" name="a">

    <div class="row justify-content-md-center" style="margin-bottom: 3%;margin-top: 1%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampa maggiorazioni</span>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Importo da</label>
                <div class="col-lg-8">
                    <input class="text_left form-control resize" tabindex=1 name=importo_da id=importo_da value="">
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Importo a</label>
                <div class="col-lg-8">
                    <input class="text_left form-control resize" tabindex=2 name=importo_a id=importo_a value="">
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 1.5%;">
        <div class="col col-lg-2 col-lg-offset-3">
            <p style="font-weight: bold;color: blue;text-align: left;font-size: 15px;">Tipo file:</p>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <select id="tipo_file" name="tipo_file" class="form-control resize" >
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>
        </div>
    </div>
</form>