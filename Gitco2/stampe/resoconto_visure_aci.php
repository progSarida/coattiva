<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_db.php";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$cls_db = new cls_db();

$query = "SELECT CC, Denominazione FROM enti_gestiti ORDER BY Denominazione";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$option = "<option value='' style='font-weight: bold;color: blue;'>Seleziona comune</option>";
foreach ($result as $key => $value){
    $option .= "<option value='".$value["CC"]."'>".$value["Denominazione"]."</option>";
}

?>

    <script>
        //F5
        switchMenuImg("F5");
        F5_button = function(){
            location.href="resoconto_visure_aci.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        }

        //F10
        switchMenuImg("F10");
        F10_button = function(){
            if(validateForm())
                ajaxCall();
                //$("#btn_sub").trigger("click");
        }

        function creaTabella(){
            $.ajax({
                type: "post",
                url: "ajax/get_data_aci.php",
                data: {
                    cc: $("#cc").val(),
                    da_data: $("#da_data").val(),
                    a_data: $("#a_data").val()
                },
                dataType: "json",
                success: function( data ) {
                    var toprint = [
                        {originalName: "Denominazione", replacedName: "Ente"},
                        {originalName: "DataRichiesta", replacedName: "Data", type: "date"},
                        {originalName: "IdRichiesta", replacedName: "IdRichiesta"},
                        {originalName: "Numero_Richieste", replacedName: "Numero richieste"},
                        {originalName: "Visure_Ricevute", replacedName: "Visure ricevute"}
                    ];
                    var widthCell = ["34%","21%","15%","15%","15%"];
                    var fontsize = "10px";

                    var test = new TableGenerator(data,toprint,widthCell,fontsize);
                },
                error: function() {

                }
            });
        }

        $(document).ready(function () {
            /*var urlFile = getParameterByName("urlFile");
            if(urlFile != null && urlFile != '')
                window.open(urlFile,"_blank");
            if(getParameterByName("file") != null)
                showFileOnModal(getParameterByName("file"),"Visure ACI",getParameterByName("file").split('.').pop());*/
            spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
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
                    showFileOnModal(resp.path,"Resoconto Visure ACI",resp.path.split('.').pop());
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

    <form id="resoconto_form" action="print_visure_aci.php" method="post" >
        <input type=hidden name="c" value="<?php echo $c ?>" />
        <input type=hidden name="a" value="<?php echo $a ?>" />

        <button type="submit" style="display: none;" id="btn_sub"></button>

        <div class="row justify-content-md-center " style="margin: 2%;">
            <div class="col col-md-auto text_center">
                <span class="titolo font16 under_decor">Stampa resoconto visure ACI</span>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <label class="col-lg-6 control-label resize" style="text-align: left;">Da data richiesta</label>
                <div class="form-group">
                    <div class="col-lg-6">
                        <input type="date" class="form-control resize" id="da_data" name="da_data" value="">
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <label class="col-lg-6 control-label resize" style="text-align: left;">A data richiesta</label>
                <div class="form-group">
                    <div class="col-lg-6">
                        <input type="date" class="form-control resize" id="a_data" name="a_data" value="">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <label class="col-lg-6 control-label resize" style="text-align: left;">Comune</label>
                <div class="form-group">
                    <div class="col-lg-6">
                        <select id="cc" name="cc" tabindex=9 class="form-control resize">
                            <?php echo $option; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col col-lg-5 ">
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



        <div class="row">
            <div class="col-lg-offset-9 col-lg-2">
                <button type="button" class="btn btn-primary" onclick="creaTabella();">Crea Tabella</button>
            </div>
        </div>
    </form>

    <div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>