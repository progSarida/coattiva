<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="userSospSearchModal" tabindex="-1" aria-labelledby="userSospSearchModal" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="userSospSearchModal_nc" style="color: blue;"><b>Ricerca Utente</b></h5>                <!-- -->
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
                                            <label class="col-lg-5" for="check_u_name" id="check_name_label">Cognome Nome/Ditta</label>
                                            <input class="col-lg-1" id=check_u_name type=radio name=tipo_u value=u_sosp_name ">
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-5" for="check_u_cf" id="check_cf_label">Cod. Fis./P. IVA</label>
                                            <input class="col-lg-1" id=check_u_cf type=radio name=tipo_u value=u_sosp_cf ">
                                        </div>
                                    </div>
                                </div style="marg">
                            </div>
                        </div>
                        <!-- Form inserimento nome -->
                        <div id="ins_u_name">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Cognome/Nome</label>
                                    <div class="col-lg-9">
                                        <input id="user_name" tabindex=6 class="form-control resize user_in" style= "border: 2px solid black;" placeholder="Cognome/Nome ..." name=name type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form inserimento codice fiscale/partita IVA -->
                        <div id="ins_u_cf" >
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">CF/P. IVA</label>
                                    <div class="col-lg-9">
                                        <input id="user_cf" tabindex=6 class="form-control resize user_in" style=" border: 2px solid black;" placeholder="Cod. Fiscale/P. IVA ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Parte fissa: checkbox tutti comuni e pulsante ricerca -->
                        <div id="common">
                            <!-- Pulsante ricerca -->
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxUser($('[name=tipo_u]:checked').val());">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableUser"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //
    function startAjaxUser(val){
        if(val == 'u_sosp_name') {
            //alert("User Name");
            startAjax('u_sosp_name');
        }
        else{
            //alert("User CF");
            startAjax('u_sosp_cf');
        }
    }

    // Gestione eventi checkbox
    $(document).ready(function (){
        $('[name="tipo_u"]').on("change", function(){
            //Ricerca per Nome
            if($(this).val() == "u_name"){
                //user_S = "u_name";
                //alert(user_S);
                $("#ins_u_cf").hide();
                $("#ins_u_name").show();
            }
            //Ricerca per CF
            else{
                //user_S = "u_cf";
                //alert(user_S);
                $("#ins_u_name").hide();
                $("#ins_u_cf").show();
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