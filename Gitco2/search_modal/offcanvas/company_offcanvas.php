<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="companySearchModal" tabindex="-1" aria-labelledby="companySearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="companySearchModalLabel_nc" style="color: blue;"><b>Ricerca Datore di lavoro</b></h5>                <!-- -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" >
                <div class="row">
                    <div class="col-lg-4" >
                        <!-- Checkbox per selezione tipo ricerca -->
                        <div class="row">
                            <div class="col-lg-12" id="checkbox_u">
                                <div class="row">
                                    <div>
                                        <div class="form-group">
                                            <label class="col-lg-5" for="check_c_name" id="check_name_label">Nome</label>
                                            <input class="col-lg-1" id=check_c_name type=radio name=tipo_c value=c_name ">
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-5" for="check_c_cf" id="check_cf_label">P. IVA</label>
                                            <input class="col-lg-1" id=check_c_cf type=radio name=tipo_c value=c_cf ">
                                        </div>
                                    </div>
                                </div style="marg">
                            </div>
                        </div>
                        <!-- Form inserimento nome -->
                        <div id="ins_c_name">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Nome ditta</label>
                                    <div class="col-lg-9">
                                        <input id="company_name" tabindex=6 class="form-control resize user_in" style= "border: 2px solid black;" placeholder="Cognome/Nome ..." name=name type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form inserimento codice fiscale/partita IVA -->
                        <div id="ins_c_cf" >
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">P. IVA ditta</label>
                                    <div class="col-lg-9">
                                        <input id="company_cf" tabindex=6 class="form-control resize user_in" style=" border: 2px solid black;" placeholder="Cod. Fiscale/P. IVA ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Parte fissa: checkbox tutti comuni e pulsante ricerca -->
                        <div id="common">
                            <!-- Checkbox tutti comuni -->
                            <!--
                            <div class="col-lg-12" id="checkbox_c" style="margin-left: 21%">
                                <div>
                                    <input class="col-lg-1" id=all_c type=checkbox name=tutti_c value=all_c ">
                                    <label class="col-lg-11" for="all_c" id="all_label">Cerca su tutti i comuni</label>
                                </div>
                            </div>
                            -->
                            <!-- Pulsante ricerca -->
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxCompany($('[name=tipo_c]:checked').val());">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableCompany"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //
    function startAjaxCompany(val){
        if(val == 'c_name') {
            startAjax('c_name');
        }
        else{
            startAjax('c_cf');
        }
    }

    //Spunta checkbox per ricerca del nome su tutti i comuni
    $(document).ready(function (){
        $('[name="tutti_c"]').on("change", function(){
            if($("#all_c").is(":checked")){
                all_city = 'y';
            }
            else{
                all_city = 'n';
            }
        })
    })

    // Gestione eventi checkbox
    $(document).ready(function (){
        $('[name="tipo_u"]').on("change", function(){
            //Ricerca per Nome
            if($(this).val() == "u_name"){
                $("#ins_c_cf").hide();
                $("#ins_c_name").show();
            }
            //Ricerca per CF
            else{
                $("#ins_c_name").hide();
                $("#ins_c_cf").show();
            }
        })
    })

    //lancia richiesta Ajax
    $(".user_in").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjaxUser($('[name=tipo_u]:checked').val());
        }
    });
</script>