<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="ownerSearchModal" tabindex="-1" aria-labelledby="ownerSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="ownerSearchModalLabel_nc" style="color: blue;"><b>Ricerca Intestatario</b></h5>                <!-- -->
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
                                            <label class="col-lg-5" for="check_name" id="check_name_label">Cognome Nome/Ditta</label>
                                            <input class="col-lg-1" id=check_name type=radio name=tipo_o value=name ">
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-5" for="check_cf" id="check_cf_label">Cod. Fis./P. IVA</label>
                                            <input class="col-lg-1" id=check_cf type=radio name=tipo_o value=cf ">
                                        </div>
                                    </div>
                                </div style="marg">
                            </div>
                        </div>
                        <!-- Form inserimento nome -->
                        <div id="ins_name">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Cognome/Nome</label>
                                    <div class="col-lg-9">
                                        <input id="name" tabindex=6 class="form-control resize owner" style= "border: 2px solid black;" placeholder="Cognome/Nome ..." name=name type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form inserimento codice fiscale/partita IVA -->
                        <div id="ins_cf" >
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">CF/P. IVA</label>
                                    <div class="col-lg-9">
                                        <input id="cf" tabindex=6 class="form-control resize owner" style=" border: 2px solid black;" placeholder="Cod. Fiscale/P. IVA ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Parte fissa: checkbox tutti comuni e pulsante ricerca -->
                        <div id="common">
                            <!-- Checkbox tutti comuni: ELIMINATA-->
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
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxOwner($('[name=tipo_o]:checked').val());">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableOwner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //
    function startAjaxOwner(val){
        if(val == 'name') {
            startAjax('name');
        }
        else{
            startAjax('cf');
        }
    }

    //Spunta ceckbox per ricerca del nome su tutti i comuni: ELIMINATA
    /*
    $(document).ready(function (){
        $('[name="tutti_c"]').on("change", function(){
            //Da vero a falso
            if($("#all_c").is(":checked")){
                all_city = 1;
                //$('#all_cf').prop( "checked", false );
                //alert("false");
            }
            //Da falso a vero
            else{
                all_city = 0;
                //$('#all_cf').prop( "checked", true );
                //alert("true");
            }
        })
    })

     */


    // Gestione eventi checkbox
    $(document).ready(function (){
        $('[name="tipo_o"]').on("change", function(){
            //Ricerca per Nome
            if($(this).val() == "name"){
                //owner_S = "name";
                $("#ins_cf").hide();
                $("#ins_name").show();
            }
            //Ricerca per CF
            else{
                //owner_S = "cf";
                $("#ins_name").hide();
                $("#ins_cf").show();
            }
        })
    })

    //lancia richiesta Ajax                         Perchè lancia anche startAjax('addr_gen'.....)?
    $(".owner").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjaxOwner($('[name=tipo_o]:checked').val());
        }
    });
</script>