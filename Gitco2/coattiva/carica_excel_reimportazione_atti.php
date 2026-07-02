<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include_once(INC . "/menu.php");

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

?>

<form action="leggi_excell_reimportazione_atti.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="c" value="<?= $c; ?>">
    <input type="hidden" name="a" value="<?= $a; ?>">

    <div class="row">
        <div class="col-lg-offset-10 col-lg-1">
            <img src="<?= IMMAGINIWEB ?>/excell_icon.png" onclick="stampaModello();" style="width: 65px;cursor: pointer;" title="Modello excel per l'importazione">
        </div>
    </div>

    <div class="row" style="margin-top: 3%;">
        <div class="col-lg-offset-1 col-lg-5">
            <div class="mb-3">
                <label for="formFile" class="form-label">Carica il file excel necessario per l'importazione (formati consentiti .xlsx .xls)</label>
                <input class="form-control" type="file" name="file">
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 1.5%;">
        <div class="col-lg-offset-1 col-lg-5">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Avvia</button>
        </div>
    </div>

</form>

<script>
    function stampaModello(){
        $.ajax('ajax/print_model_reimportation.php',
        [

        ])

        $.ajax({
            url: "ajax/print_model_reimportation.php",
            method: "GET",
            dataType: "json",
            success: function(result){
                if(result.status == "s"){
                    if(result.urlFile != "")
                        //window.open(result.urlFile,"_blank");
                        showFileOnModal(result.urlFile,"Modello Excel reimportazione atti",result.urlFile.split('.').pop());
                    else
                        ShowAlert(result.status,result.response);
                }
                else{
                    ShowAlert("2","Percorso file non restituito!");
                }
            },
            error: function(result){
                ShowAlert("1","Errore sconosciuto!");
            }
        });
    }
</script>
