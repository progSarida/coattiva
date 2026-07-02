<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="bankSearchModal" tabindex="-1" aria-labelledby="bankSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="bankSearchModalLabel" style="color: blue;"><b>Ricerca Banca</b></h5>
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
                                    <label class="col-lg-3" control-label resize" style="text-align: left;">Tipo banca</label>
                                    <div class="col-lg-9">
                                        <label class="col-lg-3" for="bank_headq" id="check_cap_label">Sede</label>
                                        <input class="col-lg-1" id=bank_headq type=radio name=tipo_b value=bank_headq onclick="/*switchRic_c()*/">
                                        <label class="col-lg-3" for="bank_branch" id="check_gen_label">Filiale</label>
                                        <input class="col-lg-1" id=bank_branch type=radio name=tipo_b value=bank_branch onclick="/*switchRic_nc()*/">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form inserimento dati -->
                        <div id="ins_addr_nc" style="margin-top: 5px;">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Denominazione</label>
                                    <div class="col-lg-9">
                                        <input id=bank_n tabindex=6 class="form-control resize address" style=" border: 2px solid black;" placeholder="Denominazione ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Comune</label>
                                    <div class="col-lg-9">
                                        <input id=bank_c tabindex=6 class="form-control resize address" style=" border: 2px solid black; margin-top: 2px;" placeholder="Comune ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">CAP</label>
                                    <div class="col-lg-9">
                                        <input id=bank_cap tabindex=6 class="form-control resize address" style=" border: 2px solid black; margin-top: 2px;" placeholder="CAP ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">PI/CF</label>
                                    <div class="col-lg-9">
                                        <input id=bank_PI_CF tabindex=6 class="form-control resize address" style=" border: 2px solid black; margin-top: 2px;" placeholder="CF/PI ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 2%;">
                                <div class="col col-lg-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="search_disabled_bank" name="search_disabled_bank">
                                        <label class="form-check-label" for="search_disabled_bank">
                                            Cerca anche le banche/filiali disabilitate
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxBank($('[name=tipo_b]:checked').val())">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableBank"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //
    function startAjaxBank(val){
        if(val == 'bank_headq')
            startAjax('bank_headq');
        else
            startAjax('bank_branch');
    }
    // Gestione radio
    $(document).ready(function (){
        $('[name="tipo_b"]').on("change", function(){
            //alert($(this).val());
            if($(this).val() == "bank_headq"){
                //alert("sede");
            }
            else{
                //alert("filiale");
            }
        })
    })

    //lancia richiesta Ajax
    $(".address").keyup(function(event) {
        if (event.keyCode === 13) {
            //alert($('[name=tipo_b]:checked').val());
            startAjaxBank($('[name=tipo_b]:checked').val());
        }
    });
</script>