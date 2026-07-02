<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_db.php";

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$file = $cls_help->getVar('file');

$cls_db = new cls_db();

// SELECT ENTI
$queryEnti = "SELECT * FROM enti_gestiti";
$resultEnti = $cls_db->getResults($cls_db->ExecuteQuery($queryEnti));
$opzioniEnti = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona ente</option>";
foreach ($resultEnti as $key => $value){
    $selected = $value['CC'] == $c ? "selected" : "";
    $opzioniEnti .= "<option " . $selected . " value='".$value["CC"] . "'> (" . $value["CC"] . ") " . $value["Denominazione"] . "</option>";
}

// SELECT SOGGETI
$querySoggetti = "SELECT 
                        U.ID, 
                        IF(U.Genere = 'D', U.Ditta, CONCAT(U.Cognome, ' ', U.Nome)) AS user 
                    FROM partita_tributi AS PT 
                    LEFT JOIN ruolo AS R ON PT.Ruolo_ID = R.id 
                    LEFT JOIN utente AS U ON PT.Utente_ID = U.ID
                    WHERE PT.CC = '" . $c . "'
                    GROUP BY U.ID
                    ORDER BY U.Ditta, U.Cognome, U.Nome ASC";
$resultSoggetti = $cls_db->getResults($cls_db->ExecuteQuery($querySoggetti));
$opzioniSoggetti = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona soggetto</option>";
foreach ($resultSoggetti as $key => $value){
    $denominazione_breve = (mb_strlen($value["user"]) > 60) ? mb_substr($value["user"], 0, 60) . "..." : $value["user"];
    $opzioniSoggetti .= "<option value='" . $value["ID"] . "'>" . $denominazione_breve . "</option>";
}

// SELECT ANNI
$queryAnni = "SELECT ID, Anno FROM anni_gestiti WHERE CC_Anno= '" . $c . "'";
$resultAnni = $cls_db->getResults($cls_db->ExecuteQuery($queryAnni));
$opzioniAnni = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona anno</option>";
foreach ($resultAnni as $key => $value){
    $selected = $value['Anno'] == $a ? "selected" : "";
    $opzioniAnni .= "<option " . $selected . " value='" . $value["Anno"] . "'>" . $value["Anno"] . "</option>";
}

// SELECT PARTITE
$queryPartite = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resultPartite = $cls_db->getResults($cls_db->SelectQuery($queryPartite));
$opzioniPartite = "<option value=0 style='font-weight: bold;color: blue;'>Seleziona partita</option>";
foreach ($resultPartite as $key => $value){
    $opzioniPartite .= "<option value='" . $value['Comune_ID'] . "'>" . $value['Comune_ID'] . "</option>";
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
        if(validateForm())
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
        // alert($("form").serialize());
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
                    showFileOnModal(resp.path,"Estratto debito",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore! " + resp.responseText);
            }
            // error: function (jqXHR, textStatus, errorThrown) {
            //     spinner.closeSpinner();
                
            //     // Stampiamo in console tutto l'oggetto per ispezionarlo senza crash
            //     console.log("Stato Errore:", textStatus);
            //     console.log("Testo Risposta del Server:", jqXHR.responseText);
                
            //     // Mostriamo l'alert usando il '+' per unire i testi
            //     ShowAlert(1,"Si è verificato un errore! " + jqXHR.responseText);
            // }
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

<form id="storico_form" action="print_estratto_debito.php" method="post" >
<!-- <form id="storico_form" > -->
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />

    <button type="submit" style="display: none;" id="btn_sub"></button>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampa estratto conto di debito</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-3 control-label resize" style="text-align: left;">Ente</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="hidden" id="ente" name="ente" value="<?php echo $c; ?>" />
                    <select id="select_ente" name="select_ente" tabindex=9 class="form-control resize" disabled>
                        <?php echo $opzioniEnti; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <label class="col-lg-3 control-label resize" style="text-align: left;">Soggetto</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="soggetto" name="soggetto" tabindex=9 class="form-control resize">
                        <?php echo $opzioniSoggetti; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-3 control-label resize" style="text-align: left;">Da anno</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="da_anno" name="da_anno" tabindex=9 class="form-control resize">
                        <?php echo $opzioniAnni; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <label class="col-lg-3 control-label resize" style="text-align: left;">A anno</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="a_anno" name="a_anno" tabindex=9 class="form-control resize">
                        <?php echo $opzioniAnni; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-3 control-label resize" style="text-align: left;">Da partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="da_partita" name="da_partita" tabindex=9 class="form-control resize">
                        <?php echo $opzioniPartite; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <label class="col-lg-3 control-label resize" style="text-align: left;">A partita</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="a_partita" name="a_partita" tabindex=9 class="form-control resize">
                        <?php echo $opzioniPartite; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-3 control-label resize" style="text-align: left;">Da data </label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="date" class="form-control resize" id="da_data" name="da_data" value="">
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <label class="col-lg-3 control-label resize" style="text-align: left;">A data </label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="date" class="form-control resize" id="a_data" name="a_data" value="">
                </div>
            </div>
        </div>
    </div> -->
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-3 control-label resize" style="text-align: left;">Tipo stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printType" name="printType" tabindex=9 class="form-control resize validateCustom vld_Custom_r">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

</form>

<div id="appendTable"></div>

<script>
    $(document).ready(function(){
    spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");

    $("#select_ente").on("change", function(){
        var nuovoC = $(this).val();
        $("#ente").val(nuovoC); // aggiorna anche l'hidden se lo usi ancora altrove

        if (nuovoC == 0) {
            resetSelects();
            return;
        }

        $.ajax({
            url: "estratto_debito_update_select.php",
            data: { c: nuovoC },
            dataType: "json",
            type: "GET",
            success: function(resp){
                if (resp.error == 0) {
                    popolaSelect("#soggetto", resp.soggetti, "Seleziona soggetto");
                    popolaSelect("#da_anno", resp.anni, "Seleziona anno");
                    popolaSelect("#a_anno", resp.anni, "Seleziona anno");
                    popolaSelect("#da_partita", resp.partite, "Seleziona partita");
                    popolaSelect("#a_partita", resp.partite, "Seleziona partita");
                } else {
                    ShowAlert(1, resp.msg);
                }
            },
            error: function(resp){
                console.log(resp.responseText);
                ShowAlert(1, "Si è verificato un errore nel caricamento delle opzioni." + resp.responseText);
            }
        });
    });

    function popolaSelect(selector, opzioni, placeholder) {
        var $select = $(selector);
        $select.empty();
        $select.append('<option value=0 style="font-weight: bold;color: blue;">' + placeholder + '</option>');
        opzioni.forEach(function(opt){
            $select.append('<option value="' + opt.id + '">' + opt.label + '</option>');
        });
    }

    function resetSelects() {
        popolaSelect("#soggetto", [], "Seleziona soggetto");
        popolaSelect("#da_anno", [], "Seleziona anno");
        popolaSelect("#a_anno", [], "Seleziona anno");
        popolaSelect("#da_partita", [], "Seleziona partita");
        popolaSelect("#a_partita", [], "Seleziona partita");
    }
});
</script>

<?php include(INC."/footer.php"); ?>