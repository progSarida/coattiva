<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="userEntrySearchModal" tabindex="-1" aria-labelledby="userEntrySearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="userEntrySearchModalLabel_u" style="color: blue;"><b>Ricerca Utente</b></h5>
                <h5 class="modal-title" id="userEntrySearchModalLabel_e" style="color: blue;"><b>Ricerca Partita</b></h5>                <!-- -->
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
                                        <label class="col-lg-5" for="check_u_n" id="check_label_u_n"><b>Utente</b> - Cognome Nome/Ditta</label>
                                        <input class="col-lg-1" id=check_u_n type=radio name=tipo_u_e value=user_n ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-5" for="check_u_c" id="check_label_u_c"><b>Utente</b> - Codice Fiscale/Partita Iva</label>
                                        <input class="col-lg-1" id=check_u_c type=radio name=tipo_u_e value=user_c ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-5" for="check_e_cA" id="check_label_e_cA"><b>Partita</b> - Cronologico atto</label>
                                        <input class="col-lg-1" id=check_e_cA type=radio name=tipo_u_e value=entry_chronoA ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-5" for="check_e_cP" id="check_label_e_cP"><b>Partita</b> - Cronologico pignoramento</label>
                                        <input class="col-lg-1" id=check_e_cP type=radio name=tipo_u_e value=entry_chronoP ">
                                    </div>
                                    <div class="checkbox">
                                        <label class="col-lg-5" for="check_e_i" id="check_label_e_i"><b>Partita</b> - Informazioni cartella</label>
                                        <input class="col-lg-1" id=check_e_i type=radio name=tipo_u_e value=entry_info ">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form ricerca utente per nome -->
                        <div id="ins_u_n">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Cognome/Nome</label>
                                    <div class="col-lg-9">
                                        <input id="u_n" tabindex=6 class="form-control resize user_entry" style= "border: 2px solid black;" placeholder="Cognome/Nome ..." name=name type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form ricerca utente per cf/pi -->
                        <div id="ins_u_c" >
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">CF/P. IVA</label>
                                    <div class="col-lg-9">
                                        <input id="u_c" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;" placeholder="Cod. Fiscale/P. IVA ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form ricerca cronologico -->
                        <div id="ins_e_cA">
                            <div class="row">
                                <div class="form-group">
                                    <label id="e_cA_P_l" class="col-lg-3 control-label resize" style="text-align: left;">Protocollo</label>
                                    <div class="col-lg-9">
                                        <input id="e_cA_P" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;" placeholder="Protocollo ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Cronologico</label>
                                    <div class="col-lg-9">
                                        <input id="e_cA_C" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;margin-top: 1px;" placeholder="Cronologico ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Anno</label>
                                    <div class="col-lg-9">
                                        <input id="e_cA_Y" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;margin-top: 1px;" placeholder="Anno ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form ricerca pignoramento -->
                        <div id="ins_e_cP">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Protocollo</label>
                                    <div class="col-lg-9">
                                        <input id="e_cP_P" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;" placeholder="Protocollo ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Cronologico</label>
                                    <div class="col-lg-9">
                                        <input id="e_cP_C" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;margin-top: 1px;" placeholder="Cronologico ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Anno</label>
                                    <div class="col-lg-9">
                                        <input id="e_cP_Y" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;margin-top: 1px;" placeholder="Anno ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form ricerca informazioni cartella -->
                        <div id="ins_e_i">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Informazioni cartella</label>
                                    <div class="col-lg-9">
                                        <input id="e_i" tabindex=6 class="form-control resize user_entry" style=" border: 2px solid black;" placeholder="Info ..." name=cf type=text value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pulsante (sempre in vista)-->
                        <div class="row" style="margin-top: 4%;">
                            <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxUserEntry($('[name=tipo_u_e]:checked').val());">Cerca</button></div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="appendTableUserEntry"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //Variabile che su visura_massiva.php blocca la ricerca del secondo input
    //var lock = 'N';
    $(document).ready(function (){
        $('[name="tipo_u_e"]').on("change", function(){
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
    // Seleziona tipo chiamata Ajax
    function startAjaxUserEntry(val){
        //alert("chiamata ajax generica "+val);
        switch (val){
            case 'user_n':
                startAjax('user_n');
                break;
            case 'user_c':
                startAjax('user_c');
                break;
            case 'entry_chronoA':
                startAjax('entry_chronoA');
                break;
            case 'entry_chronoP':
                startAjax('entry_chronoP');
                break;
            case 'entry_info':
                startAjax('entry_info');
                break;
        }
    }
    // Lancia chiamata Ajax
    $(".user_entry").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjaxUserEntry($('[name=tipo_u_e]:checked').val());
        }
    });
</script>