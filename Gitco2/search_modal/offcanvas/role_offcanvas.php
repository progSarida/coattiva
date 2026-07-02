<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="roleSearchModal" tabindex="-1" aria-labelledby="roleSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="roleSearchModalLabel_nc" style="color: blue;"><b>Ricerca ruolo</b></h5>                <!-- -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" >
                <div class="row">
                    <div class="col-lg-4" >
                        <!-- Checkbox per selezione tipo ricerca -->
                        <div class="row">
                            <div class="col-lg-12" id="checkbox_c">
                                <div class="row">
                                    <div>
                                        <div class="form-group">
                                            <label class="col-lg-5" for="check_desc" id="check_desc_label">Descrizione</label>
                                            <input class="col-lg-1" id=check_desc type=radio name=tipo_r value=desc ">
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-5" for="check_year" id="check_year_label">Anno fornitura</label>
                                            <input class="col-lg-1" id=check_year type=radio name=tipo_r value=year ">
                                        </div>
                                    </div>
                                </div style="marg">
                            </div>
                        </div>
                        <!-- Form inserimento descrizione -->
                        <div id="ins_desc">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Descrizione</label>
                                    <div class="col-lg-9">
                                        <input id="desc" tabindex=6 class="form-control resize role" style= "border: 2px solid black;" placeholder="Descrizione ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxRole($('[name=tipo_r]:checked').val());">Cerca</button></div>
                            </div>
                        </div>
                        <!-- Form inserimento anno -->
                        <div id="ins_year" >
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Anno</label>
                                    <div class="col-lg-9">
                                        <input id="year" tabindex=6 class="form-control resize role" style=" border: 2px solid black;" placeholder="Anno fornitura ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="controlYear();">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableRole"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //Sceglie quale tipo di richiesta Ajax fare
    function startAjaxRole(val){
        if(val == 'desc') {
            startAjax('desc');
        }
        else{
            startAjax('year');
        }
    }

    //Controllo correttezza anno
    function controlYear(){
        // Setta l'anno corrente
        const date = new Date();
        const current_year = date.getFullYear();
        //alert(current_year);
        // Anno inserito
        var given_year = $("#year").val();
        //alert(given_year);
        // Struttura l'espressione regolare
        var expr = /^[0-9]{4}$/;
        //alert(expr);
        if(given_year == ''){
            startAjaxRole($('[name=tipo_r]:checked').val());
        } else {
            if (expr.test(given_year))  {
                if(!(given_year > "1970" && given_year <= current_year)){
                    alert("Errore nell'inserimento dell'anno.\nDeve essere compreso tra il 1970 e l'anno corrente.");
                }
                else{
                    //alert("Anno inserito correttamente");
                    startAjaxRole($('[name=tipo_r]:checked').val());
                }
            }
            else {
                alert("L'anno inserito non è conforme.\nDeve essere un numero di quattro cifre.");
            }
        }
    }

    // Gestione eventi checkbox
    $(document).ready(function (){
        $('[name="tipo_r"]').on("change", function(){
            //Ricerca per descrizione ruolo
            if($(this).val() == "desc"){
                $("#ins_year").hide();
                $("#ins_desc").show();
                //role_S = "desc";
            }
            //Ricerca per anno fornitura
            else{
                $("#ins_desc").hide();
                $("#ins_year").show();
                //role_S = "year";
            }
        })
    })

    //lancia richiesta Ajax
    $(".role").keyup(function(event) {
        if (event.keyCode === 13) {
            if ($("#year").val() != ""){
                controlYear();
            }
            else{
                startAjaxRole($('[name=tipo_r]:checked').val());
            }

        }
    });
</script>