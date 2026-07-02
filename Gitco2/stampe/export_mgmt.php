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
    $enti .= "<option value='".$value["CC"]."'>".$value["Denominazione"]." - ".$value["CC"]."</option>";
}

$a_entrate = $cls_db->getResults($cls_db->ExecuteQuery("SELECT DISTINCT Tipo FROM partita_tributi WHERE Tipo!='' ORDER BY Tipo ASC"));
$a_selection = array("value" => "Tipo", "selected"=>null, "firstOpt" => 1, "text"=>array("[Tipo]"));
$opt_entrate = $cls_html->getOptions($a_entrate, $a_selection);

?>

<script>
    var spinner = null;

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
    });

    function ajaxCall() {
        //alert("ajax");
        spinner.startSpinner();
        //return;
        $.ajax({
            url: "export_positions.php",
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
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
    }
</script>

<form id="excel_form" action="export_positions.php" method="post" >
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
                    <select id="ente" name="ente" class="form-control resize">
                        <?php echo $enti; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Tipo entrata</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="tipo_entrata" name="tipo_entrata" class="form-control resize">
                        <?php echo $opt_entrate; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Utente deceduto</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="decesso" name="decesso" class="form-control resize">
                        <option></option>
                        <option value="y">Si</option>
                        <option selected value="n">No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Posizione archiviata</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="archiviato" name="archiviato" class="form-control resize">
                        <option></option>
                        <option value="y">Si</option>
                        <option selected value="n">No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize " style="text-align: left;">Ricorso presente</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="ricorso" name="ricorso" class="form-control resize">
                        <option></option>
                        <option value="y">Si</option>
                        <option selected value="n">No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

</form>

<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>