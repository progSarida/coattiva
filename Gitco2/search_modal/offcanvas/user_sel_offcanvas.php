<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="userSelSearchModal" tabindex="-1" aria-labelledby="userSelSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="userSelSearchModalLabel_nc" style="color: blue;"><b>Ricerca Utente</b></h5>                <!-- -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" >
                <!-- Checkbox per selezione tipo ricerca -->
                <!--
                <div class="row">
                    <div class="col-lg-4" id="checkbox_u">
                        <div class="row">
                            <div>
                                <div class="form-group">
                                    <label class="col-lg-5" for="check_u_name" id="check_name_label">Cognome Nome/Ditta</label>
                                    <input class="col-lg-1" id=check_u_name type=radio name=tipo_u value=u_name ">
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-5" for="check_u_cf" id="check_cf_label">Cod. Fis./P. IVA</label>
                                    <input class="col-lg-1" id=check_u_cf type=radio name=tipo_u value=u_cf ">
                                </div>
                            </div>
                        </div style="marg">
                    </div>
                </div>
                -->
                <div class="row">
                    <div class="col-lg-4" >
                        <!-- Form inserimento Cognome/Nome ditta -->
                        <div id="ins_u_surn">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Cognome/Ditta</label>
                                    <div class="col-lg-9">
                                        <input id="sel_surn" tabindex=6 class="form-control resize user_in" style= "border: 2px solid black;" placeholder="Cognome/Ditta ..." name=name type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form inserimento Nome -->
                        <div id="ins_u_name" >
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Nome</label>
                                    <div class="col-lg-9">
                                        <input id="sel_name" tabindex=6 class="form-control resize user_in" style=" border: 2px solid black;" placeholder="Nome ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--  -->
                        <!-- Parte fissa: checkbox tutti comuni e pulsante ricerca -->
                        <!-- Checkbox tutti comuni            ELIMINATA-->
                        <!--
                            <div class="col-lg-12" id="checkbox_c" style="margin-left: 21%">
                                <div>
                                    <input class="col-lg-1" id=all_c type=checkbox name=tutti_c value=all_c ">
                                    <label class="col-lg-11" for="all_c" id="all_label">Cerca su tutti i comuni</label>
                                </div>
                            </div>
                            -->
                        <!-- Select tipo utente da cercare -->
                        <div id="sel">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Selezione</label>
                                    <div class="col-lg-9">
                                        <select name="type_sel" id="type_sel" class="form-control resize user_in" style=" border: 2px solid black;"">
                                        <option value="all">Tutti</option>
                                        <option value="person">Persona fisica</option>
                                        <option value="business">Ditta</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pulsante ricerca -->
                        <div class="row" style="margin-top: 4%;">
                            <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxUserSel($('#type_sel').val());">Cerca</button></div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableUserSel"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sceglie echiamata Ajax da fare
    function startAjaxUserSel(val){
        switch (val){
            case 'all':
                startAjax('all');
                break;
            case 'person':
                startAjax('person');
                break;
            case 'business':
                startAjax('business');
                break;
            default:
                alert("Valore select: "+$('select[name=type_sel]').val());
                break;
        }
    }

    //lancia richiesta Ajax
    $(".user_in").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjaxUserSel($('#type_sel').val());
        }
    });
</script>