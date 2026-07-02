<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="welfareSearchModal" tabindex="-1" aria-labelledby="welfareSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="welfareSearchModalLabel" style="color: blue;"><b>Ricerca Previdenza</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" >
                <!-- Checkbox per selezione tipo ricerca -->
                <!--
                <div class="row">
                    <div class="col-lg-4" id="checkbox_c">
                        <div class="row">
                            <label class="col-lg-3" control-label resize" style="text-align: left;">Tipo banca</label>
                            <div class="col-lg-9">
                                <label class="col-lg-3" for="bank_headq" id="check_cap_label">Sede</label>
                                <input class="col-lg-1" id=bank_headq type=radio name=tipo_w value=bank_headq onclick="/*switchRic_c()*/">
                                <label class="col-lg-3" for="bank_branch" id="check_gen_label">Filiale</label>
                                <input class="col-lg-1" id=bank_branch type=radio name=tipo_w value=bank_branch onclick="/*switchRic_nc()*/">
                            </div>
                        </div style="marg">
                    </div>
                </div>
                -->
                <div class="row">
                    <div class="col-lg-4" >
                        <!-- Form inserimento dati -->
                        <div id="ins_addr_nc" style="margin-top: 5px;">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Denominazione</label>
                                    <div class="col-lg-9">
                                        <input id=welfare_n tabindex=6 class="form-control resize address" style=" border: 2px solid black;" placeholder="Denominazione ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Comune</label>
                                    <div class="col-lg-9">
                                        <input id=welfare_c tabindex=6 class="form-control resize address" style=" border: 2px solid black; margin-top: 2px;" placeholder="Comune ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">CAP</label>
                                    <div class="col-lg-9">
                                        <input id=welfare_cap tabindex=6 class="form-control resize address" style=" border: 2px solid black; margin-top: 2px;" placeholder="CAP ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjax('welfare');">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableWelfare"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Scelta caso chiamata Ajax
    /*
    function startAjaxWelfare(val){
        if(val == 'bank_headq')
            startAjax('bank_headq');
        else
            startAjax('bank_branch');
    }
    */
    // Gestione radio
    /*
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
    */
    //lancia richiesta Ajax
    $(".address").keyup(function(event) {
        if (event.keyCode === 13) {
            //alert($('[name=tipo_b]:checked').val());
            //startAjaxWelfare($('[name=tipo_w]:checked').val());
            startAjax('welfare');
        }
    });
</script>