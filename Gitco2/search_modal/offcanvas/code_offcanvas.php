<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="codeSearchModal" tabindex="-1" aria-labelledby="ownerSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="ownerSearchModalLabel_nc" style="color: blue;"><b>Ricerca Codice Tributo</b></h5>                <!-- -->
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
                                            <label class="col-lg-5" for="check_desc_code" id="check_name_label">Descrizione</label>
                                            <input class="col-lg-1" id=check_desc_code type=radio name=tipo_c value=c_desc ">
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-5" for="check_code" id="check_cf_label">Codice Tributo</label>
                                            <input class="col-lg-1" id=check_code type=radio name=tipo_c value=code ">
                                        </div>
                                    </div>
                                </div style="marg">
                            </div>
                        </div>
                        <!-- Form inserimento descrizione -->
                        <div id="ins_desc_c">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Descrizione</label>
                                    <div class="col-lg-9">
                                        <input id="ricDesc" tabindex=6 class="form-control resize code" style= "border: 2px solid black;" placeholder="Descrizione ..." name=name type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form inserimento codice tributo -->
                        <div id="ins_code" >
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Codice</label>
                                    <div class="col-lg-9">
                                        <input id="ricCode" tabindex=6 class="form-control resize code" style=" border: 2px solid black;" placeholder="Codice ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Parte fissa: checkbox tutti comuni e pulsante ricerca -->
                        <div id="common">
                            <!-- Pulsante ricerca -->
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxCode($('[name=tipo_c]:checked').val());">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableCode"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //
    function startAjaxCode(val){
        if(val == 'c_desc') {
            startAjax('c_desc');
        }
        else{
            startAjax('code');
        }
    }

    // Gestione eventi checkbox
    $(document).ready(function (){
        $('[name="tipo_c"]').on("change", function(){
            //Ricerca per Nome
            if($(this).val() == "c_desc"){
                //code_S = "c_desc";
                $("#ins_code").hide();
                $("#ins_desc_c").show();
            }
            //Ricerca per CF
            else{
                //code_S = "code";
                $("#ins_desc_c").hide();
                $("#ins_code").show();
            }
        })
    })

    //lancia richiesta Ajax
    $(".code").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjaxCode($('[name=tipo_c]:checked').val());
        }
    });
</script>