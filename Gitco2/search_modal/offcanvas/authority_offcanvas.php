<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="authoritySearchModal" tabindex="-1" aria-labelledby="authoritySearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="authoritySearchModalLabel" style="color: blue;"><b>Ricerca autorità</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" >
                <!-- Spazio form ricerca -->
                <div class="row">
                    <div class="col-lg-4" >
                        <!--  Checkbox per selezione tipo ricerca -->
                        <div class="row">
                            <div class="col-lg-12" id="checkbox_c">
                                <div class="row">
                                    <div class="checkbox">
                                        <label class="col-lg-8" for="judge" id="check_name_label">Giudice di Pace</label>
                                        <input class="col-lg-2" id=judge type=radio name=authority value=judge ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-8" for="court" id="check_name_label">Tribunale</label>
                                        <input class="col-lg-2" id=court type=radio name=authority value=court ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-8" for="tax_prov" id="check_name_label">Commissione Tributaria Provinciale</label>
                                        <input class="col-lg-2" id=tax_prov type=radio name=authority value=tax_prov ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-8" for="tax_reg" id="check_name_label">Commissione Tributaria Regionale</label>
                                        <input class="col-lg-2" id=tax_reg type=radio name=authority value=tax_reg ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-8" for="appeal" id="check_name_label">Corte d'Appello</label>
                                        <input class="col-lg-2" id=appeal type=radio name=authority value=appeal ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-8" for="scoi" id="check_name_label">Corte di Cassazione</label>
                                        <input class="col-lg-2" id=scoi type=radio name=authority value=scoi ">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form ricerca comune -->
                        <div id="ins_u_n">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Comune</label>
                                    <div class="col-lg-9">
                                        <input id="authority_c" tabindex=6 class="form-control resize user_entry" style= "border: 2px solid black;" placeholder="Comune ..." name=name type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pulsante (sempre in vista)-->
                        <div class="row" style="margin-top: 4%;">
                            <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxAuthority($('[name=authority]:checked').val());">Cerca</button></div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableAuthority"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Seleziona tipo chiamata Ajax
    function startAjaxAuthority(val) {
        //alert("chiamata ajax generica "+val);
        switch (val) {
            case 'judge':
                startAjax('judge');
                break;
            case 'court':
                startAjax('court');
                break;
            case 'tax_prov':
                startAjax('tax_prov');
                break;
            case 'tax_reg':
                startAjax('tax_reg');
                break;
            case 'appeal':
                startAjax('appeal');
                break;
            case 'scoi':
                startAjax('scoi');
                break;
        }
    }

    // Lancia chiamata Ajax
    $(".user_entry").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjaxAuthority($('[name=authority]:checked').val());
        }
    });

    // Gestione radio                                           Inutile perchè non modificano l'offcanvas
    /*
    $(document).ready(function (){
        $('[name="authority"]').on("change", function(){
            //alert($(this).val());
            switch ($(this).val()){
                case 'user_n':
                    $("#userEntrySearchModalLabel_u").show();
                    $("#userEntrySearchModalLabel_e").hide();
                    $("#ins_u_n").show();
                    $("#ins_u_c").hide();
                    $("#ins_e_cA").hide();
                    $("#ins_e_cP").hide();
                    $("#ins_e_i").hide();
                    //user_entry_S = "user_n";
                    break;
                case 'user_c':
                    $("#userEntrySearchModalLabel_u").show();
                    $("#userEntrySearchModalLabel_e").hide();
                    $("#ins_u_n").hide();
                    $("#ins_u_c").show();
                    $("#ins_e_cA").hide();
                    $("#ins_e_cP").hide();
                    $("#ins_e_i").hide();
                    //user_entry_S = "user_c";
                    break;
                case 'entry_chronoA':
                    $("#userEntrySearchModalLabel_e").show();
                    $("#userEntrySearchModalLabel_u").hide();
                    $("#ins_u_n").hide();
                    $("#ins_u_c").hide();
                    $("#ins_e_cA").show();
                    $("#ins_e_cP").hide();
                    $("#ins_e_i").hide();
                    //user_entry_S = "entry_chronoA";
                    break;
                case 'entry_chronoP':
                    $("#userEntrySearchModalLabel_e").show();
                    $("#userEntrySearchModalLabel_u").hide();
                    $("#ins_u_n").hide();
                    $("#ins_u_c").hide();
                    $("#ins_e_cA").hide();
                    $("#ins_e_cP").show();
                    $("#ins_e_i").hide();
                    //user_entry_S = "entry_chronoP";
                    break;
                case 'entry_info':
                    $("#userEntrySearchModalLabel_e").show();
                    $("#userEntrySearchModalLabel_u").hide();
                    $("#ins_u_n").hide();
                    $("#ins_u_c").hide();
                    $("#ins_e_cA").hide();
                    $("#ins_e_cP").hide();
                    $("#ins_e_i").show();
                    //user_entry_S = "entry_info";
                    break;
            }
        })
    })
    */

</script>