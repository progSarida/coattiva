<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$query = "SELECT CC, Denominazione FROM enti_gestiti ORDER BY Denominazione";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));
$enti = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona ente</option>";
foreach ($result as $key => $value){
    $enti .= "<option value='".$value["CC"]."'>".$value["Denominazione"]."</option>";
}

$query_garlasco = "SELECT Comune_ID FROM `partita_tributi` WHERE CC = 'D925'";
$result_garlasco = $cls_db->getResults($cls_db->ExecuteQuery($query_garlasco));
$garlasco = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona partita</option>";
foreach ($result_garlasco as $key => $value_g){
    $garlasco .= "<option value='".$value_g["Comune_ID"]."'>".$value_g["Comune_ID"]."</option>";
}

$query_savona = "SELECT Comune_ID FROM `partita_tributi` WHERE CC = 'U003'";
$result_savona = $cls_db->getResults($cls_db->ExecuteQuery($query_savona));
$savona = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona partita</option>";
foreach ($result_savona as $key => $value_s){
    $savona .= "<option value='".$value_s["Comune_ID"]."'>".$value_s["Comune_ID"]."</option>";
}

$query_cogorno = "SELECT Comune_ID FROM `partita_tributi` WHERE CC = 'C826'";
$result_cogorno = $cls_db->getResults($cls_db->ExecuteQuery($query_cogorno));
$cogorno = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona partita</option>";
foreach ($result_cogorno as $key => $value_c){
    $cogorno .= "<option value='".$value_c["Comune_ID"]."'>".$value_c["Comune_ID"]."</option>";
}

?>

<script>
    var spinner = null;

    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="excel_posizioni.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        if($('#ente').val()=="0"){
            alert("Attenzione! Selezionare un ente su cui fare la ricerca");
            $('#ente').focus();
            return false;
        } else if(validateForm())
            ajaxCall();
            //$("#btn_sub").trigger("click");
    }
    
    $(document).ready(function(){
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
        $('#garlasco').hide();
        $('#savona').hide();
        $('#cogorno').hide();
        //if(getParameterByName("file") != null)
            //showFileOnModal(getParameterByName("file"),"Storico Azioni",getParameterByName("file").split('.').pop());
    });

    function ajaxCall() {
        //alert("ajax");
        spinner.startSpinner();
        //return;
        $.ajax({
            url: "print_excel_posizioni.php",
            data: $("form").serialize(),
            dataType : 'json',
            type: 'POST',
            success: function (resp) {
                spinner.closeSpinner();
                ShowAlert(resp.error,resp.msg);
                if(resp.error == 0)
                    showFileOnModal(resp.path,"Excel posizioni",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore! "+resp.responseText);
            }
        });
    }

    function showP(){
        if($('#ente').val() == 'D925'){
            $('#garlasco').show();
            $('#savona').hide();
            $('#cogorno').hide();
            $('#da_partita_s').val(0);
            $('#a_partita_s').val(0);
            $('#da_partita_c').val(0);
            $('#a_partita_c').val(0);
        } 
        else if ($('#ente').val() == 'U003'){
            $('#savona').show();
            $('#garlasco').hide();
            $('#cogorno').hide();
            $('#da_partita_g').val(0);
            $('#a_partita_g').val(0);
            $('#da_partita_c').val(0);
            $('#a_partita_c').val(0);
        }
        else if ($('#ente').val() == 'C826'){
            $('#cogorno').show();
            $('#garlasco').hide();
            $('#savona').hide();
            $('#da_partita_s').val(0);
            $('#a_partita_s').val(0);
            $('#da_partita_g').val(0);
            $('#a_partita_g').val(0);
        }
        else{
            $('#garlasco').hide();
            $('#savona').hide();
            $('#cogorno').hide();
            $('#da_partita_s').val(0);
            $('#a_partita_s').val(0);
            $('#da_partita_g').val(0);
            $('#a_partita_g').val(0);
            $('#da_partita_c').val(0);
            $('#a_partita_c').val(0);
        }
            
    }
</script>

<form id="excel_form" action="print_excel_posizioni.php" method="post" >
<!-- <form id="storico_form" > -->
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />

    <button type="submit" style="display: none;" id="btn_sub"></button>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Esporta posizioni</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Ente</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="ente" name="ente" tabindex=9 class="form-control resize"  onchange="showP();">
                        <?php echo $enti; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class=" col-lg-4 ">
            <div class="form-group">
                <label class="control-label resize col-lg-12">
                    <input type="checkbox" name=last id=last value="si">
                    <b>Ultimo atto/pignoramento notificato</b>
                </label>
            </div>
        </div>

    </div>
    <div class="row" id="garlasco" style="margin-top: 2%;">
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Da Partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="da_partita_g" name="da_partita_g" tabindex=9 class="form-control resize">
                        <?php echo $garlasco; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">A Partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="a_partita_g" name="a_partita_g" tabindex=9 class="form-control resize">
                        <?php echo $garlasco; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="savona" style="margin-top: 2%;">
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Da Partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="da_partita_s" name="da_partita_s" tabindex=9 class="form-control resize">
                        <?php echo $savona; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">A Partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="a_partita_s" name="a_partita_s" tabindex=9 class="form-control resize">
                        <?php echo $savona; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="cogorno" style="margin-top: 2%;">
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Da Partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="da_partita_c" name="da_partita_c" tabindex=9 class="form-control resize">
                        <?php echo $cogorno; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">A Partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="a_partita_c" name="a_partita_c" tabindex=9 class="form-control resize">
                        <?php echo $cogorno; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

</form>

<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>