<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_db.php";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$file = $cls_help->getVar('file');

$cls_db = new cls_db();

$query = "SELECT id, name FROM history_pages";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$ambiti = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona ambito</option>";
foreach ($result as $key => $value){
    $ambiti .= "<option value='".$value["id"]."'>".$value["name"]."</option>";
}

$query = "SELECT action, name FROM history_actions";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$azioni = "<option value='_' style='font-weight: bold;color: blue;'>Seleziona azione</option>";
foreach ($result as $key => $value){
    $azioni .= "<option value='".$value["action"]."'>".$value["name"]."</option>";
}

$query = "SELECT ID, user FROM autenticazione";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$utenti= "<option value=0 style='font-weight: bold;color: blue;'>Seleziona utente</option>";
foreach ($result as $key => $value){
    $utenti .= "<option value='".$value["ID"]."'>".$value["user"]."</option>";
}

?>

<script>
    var spinner = null;

    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="storico_azioni.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        if($('#ambito').val()=="0"){
            alert("Attenzione! Selezionare un ambito su cui fare la ricerca");
            $('#ambito').focus();
            return false;
        } else if(validateForm())
            ajaxCall();
            //$("#btn_sub").trigger("click");
    }
    
    $(document).ready(function(){
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
        //if(getParameterByName("file") != null)
            //showFileOnModal(getParameterByName("file"),"Storico Azioni",getParameterByName("file").split('.').pop());
    });

    function ajaxCall() {
        //alert("ajax");
        spinner.startSpinner();
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
                    showFileOnModal(resp.path,"Storico Azioni",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
    }
/*
    $("#storico_form").submit(function(e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = $(this);
        var actionUrl = form.attr('action');

        $.ajax({
            type: "POST",
            url: actionUrl,
            data: form.serialize(), // serializes the form's elements.
            dataType: "json",
            success: function(data)
            {
                //var data = JSON.parse(resp);
                alert("fatto"); // show response from the php script.
            },
            error:function()
            {
                alert("errore");
            }
    });

    });
*/
    
</script>

<form id="storico_form" action="print_storico.php" method="post" >
<!-- <form id="storico_form" > -->
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />

    <button type="submit" style="display: none;" id="btn_sub"></button>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampa storico azioni</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Ambito</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="ambito" name="ambito" tabindex=9 class="form-control resize">
                        <?php echo $ambiti; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5 ">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="text_left width100 form-control resize" id="anno" name="anno" ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Azione</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="azione" name="azione" tabindex=9 class="form-control resize">
                        <?php echo $azioni; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Utente</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="utente" name="utente" tabindex=9 class="form-control resize">
                        <?php echo $utenti; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Da data </label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="date" class="form-control resize" id="da_data" name="da_data" value="">
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <label class="col-lg-6 control-label resize" style="text-align: left;">A data </label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="date" class="form-control resize" id="a_data" name="a_data" value="">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Tipo stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printType" name="printType" tabindex=9 class="form-control resize validateCustom vld_Custom_r">
                        <option value="pdf">PDF</option>
                        <option value="excell">Excel</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

</form>

<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>