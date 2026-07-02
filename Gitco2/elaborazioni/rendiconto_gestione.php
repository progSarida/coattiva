<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include_once(INC . "/menu.php");

$anno_gestione = $cls_help->getVar('anno_gestione');
if(empty($anno_gestione))
    $anno_gestione = $a;

$data_stampa = $cls_help->getVar('data_stampa');
if(empty($data_stampa))
    $data_stampa = ($anno_gestione+1)."-01-31";
    
$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++) {
    $sel = "";
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "' ".$sel.">" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";
}

$serieOption = "";
$queryYear = "SELECT Anno from anni_gestiti WHERE CC_Anno = '" . $c . "' ORDER BY Anno DESC";
$resYear = $cls_db->getResults($cls_db->ExecuteQuery($queryYear));
$sel = "";
for($i=0; $i < count($resYear) ; $i++) {
    if($resYear[$i]['Anno'] == $anno_gestione) $sel = "selected";
    else $sel = "";
    $serieOption .= "<option value='" . $resYear[$i]['Anno'] . "' ".$sel.">" . $resYear[$i]['Anno'] . "</option>";
}

?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="rendiconto_gestione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){

        if(validateForm())
        ajaxCall();
            //$("#f_rendiconto_gestione").submit();
    }
    
    $(document).ready(function(){
        //if(getParameterByName("file") != null)
            //showFileOnModal(getParameterByName("file"),"Storico Azioni",getParameterByName("file").split('.').pop());
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
                    showFileOnModal(resp.path,"Rendiconto gestione",resp.path.split('.').pop());
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


<form id="f_rendiconto_gestione" name="rendiconto_gestione_form" action="print_rendiconto_gestione.php" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampa rendiconto gestione</span>
        </div>
    </div>
<!-- 
    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div> -->

    <!-- <div class="row">

        <div class="col-lg-offset-1 col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno fornitura</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="anno_fornitura" name="anno_fornitura" value="<?php echo $a ?>" maxlength="4">
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
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data Stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker" type="text" id="data_stampa" name="data_stampa" value="<?=date('d/m/Y');?>">
                </div>
            </div>
        </div>
    </div> -->

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1rem; margin-top: 1rem;"></div>

    <div class="row" style="margin-top: 1%;">
        <div class="col-lg-offset-1 col col-lg-5">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno gestione</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="anno_gestione" name="anno_gestione" tabindex=9 class="form-control resize checkDataStampa">
                        <option value="" style="font-weight: bold; color: #0a53be;">Seleziona Anno</option>
                        <?php echo $serieOption; ?>
                    </select>
                </div>
            </div>
        </div>
        <!--<div class="col col-lg-2 col-lg-offset-1" style="line-height:3.5rem">
            <b>Anno gestione</b>
        </div>
        <div class="col col-lg-2">
            <input class="form-control text-right checkDataStampa" style="width: 100%;" type="number" max="<?= date('Y'); ?>" id="anno_gestione" name="anno_gestione" length="4" value="<?= $anno_gestione; ?>">
        </div>-->
        <div class="col col-lg-5">
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
    </div>
    <div class="row" style="margin-top: 1 rem;">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data Stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="date" class="form-control validateCustom vld_CheckPrintDate_3" style="width: 100%; text-align:center" id="data_stampa" name="data_stampa" value="<?= $data_stampa; ?>">
                </div>
            </div>
        </div>

    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 1rem; "></div>


</form>
<script>
    
    $( ".checkDataStampa" ).on( "change", function() {
        anno = parseInt($('#anno_gestione').val());
        data = $('#data_stampa').val();
        a_data = data.split("-");
        $('#data_stampa').val((anno+1)+"-01-"+a_data[2]);
    });
</script>

<?php include(INC."/footer.php"); ?>
