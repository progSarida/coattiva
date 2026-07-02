<div class="modal fade offcanvas" id="addrSearchModal" tabindex="-1" aria-labelledby="addrSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 55vh !important; margin:0 auto">
        <div class="modal-content" style="height: 55vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="addrSearchModalLabel_c" style="color: blue;"><b>Ricerca indirizzo cappato</b></h5>
                <h5 class="modal-title" id="addrSearchModalLabel_nc" style="color: blue;"><b>Ricerca indirizzo</b></h5>                <!-- -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" >
                <!-- Checkbox per selezione tipo ricerca -->
                <div class="row">
                    <div class="col-lg-3" id="checkbox_c">
                        <div class="row">
                            <div>
                                <div class="form-group">
                                    <label class="col-lg-5" for="check_cap" id="check_cap_label">Indirizzo cappato</label>
                                    <input class="col-lg-1" id=check_cap type=radio name=tipo value=ricCap onclick="/*switchRic_c()*/">
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-5" for="check_gen" id="check_gen_label">Indirizzo generico</label>
                                    <input class="col-lg-1" id=check_gen type=radio name=tipo value=ricIndirizzo onclick="/*switchRic_nc()*/">
                                </div>
                            </div>
                        </div style="marg">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3" >
                        <!-- Form inserimento indirizzo cappato -->
                        <div id="ins_addr_c">
                                <div class="row" style="margin-bottom: 10px;">
                                    <div class="form-group">
                                        <div class="col-lg-12">
                                            <!-- inserire in value il comune -->
                                            <input id=comune_c type=text class="form-control resize" style= "background-color: #99CCFF; border: 2px solid #000000;" name=comune value="" size=40 readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label resize" style="text-align: left;">Indirizzo</label>
                                        <div class="col-lg-9">
                                            <input id=addr_c tabindex=6 class="form-control resize address" style= "border: 2px solid black;" placeholder="Indirizzo ..." name=city type=text value="" >
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top: 4%;">
                                    <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxAddr()">Cerca</button></div>
                                </div>
                        </div>
                        <!-- Form inserimento indirizzo non cappato -->
                        <div id="ins_addr_nc" style="margin-top: 44px;">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label resize" style="text-align: left;">Indirizzo</label>
                                    <div class="col-lg-9">
                                        <input id=addr_g tabindex=6 class="form-control resize address" style=" border: 2px solid black;" placeholder="Indirizzo ..." name=city type=text value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 4%;">
                                <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjaxAddr()">Cerca</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div id="appendTableAddr"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //mostra ricerca cappata
    /*function switchRic_c() {
        <!-- nasconde ricerca generica -->
        document.getElementById('addrSearchModalLabel_nc').hidden = true;
        document.getElementById('ins_addr_nc').hidden = true;
        $('#comune_c').val(addr_c);
        <!-- mostra ricerca cappata -->
        document.getElementById('addrSearchModalLabel_c').hidden = false;
        document.getElementById('ins_addr_c').hidden = false;
        addr_S = 'cap';
    }*/
    //mostra ricerca generica
    /*function switchRic_nc() {
        <!-- nasconde ricerca cappata -->
        document.getElementById('addrSearchModalLabel_c').hidden = true;
        document.getElementById('ins_addr_c').hidden = true;
        <!-- mostra ricerca generica -->
        document.getElementById('addrSearchModalLabel_nc').hidden = false;
        document.getElementById('ins_addr_nc').hidden = false;
        addr_S = 'via';
    }*/
    //
    function startAjaxAddr(){
        if(addr_S == 'cap')
            startAjax('addr_cap');
        else
            startAjax('addr_gen');
    }

    $(document).ready(function (){
        $('[name="tipo"]').on("change", function(){
            //alert($(this).val());
            if($(this).val() == "ricCap"){
                <!-- nasconde ricerca generica -->
                //document.getElementById('addrSearchModalLabel_nc').hidden = true;
                //document.getElementById('ins_addr_nc').hidden = true;
                $('#comune_c').val(addr_c);
                $("#addrSearchModalLabel_nc").hide();
                $("#ins_addr_nc").hide();
                <!-- mostra ricerca cappata -->
                //document.getElementById('addrSearchModalLabel_c').hidden = false;
                //document.getElementById('ins_addr_c').hidden = false;
                $("#addrSearchModalLabel_c").show();
                $("#ins_addr_c").show();
                addr_S = 'cap';
            }
            else{
                <!-- nasconde ricerca cappata -->
                //document.getElementById('addrSearchModalLabel_c').hidden = true;
                //document.getElementById('ins_addr_c').hidden = true;
                $("#addrSearchModalLabel_c").hide();
                $("#ins_addr_c").hide();
                <!-- mostra ricerca generica -->
                //document.getElementById('addrSearchModalLabel_nc').hidden = false;
                //document.getElementById('ins_addr_nc').hidden = false;
                $("#addrSearchModalLabel_nc").show();
                $("#ins_addr_nc").show();
                addr_S = 'via';
            }
        })
    })

    //lancia richiesta Ajax
    $(".address").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjaxAddr();
        }
    });
</script>