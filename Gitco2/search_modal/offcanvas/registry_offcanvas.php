<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="registrySearchModal" tabindex="-1" aria-labelledby="registrySearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="position: fixed; bottom:0; width: 100% !important;height: 60vh !important; margin:0 auto">
        <div class="modal-content" style="height: 60vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="registrySearchModalLabel" style="color: blue;"><b>Lista Uffici Anagrafici</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" >
                <div class="row">
                    <div class="col-lg-3">
                        <div class="row">
                            <div class="form-group">
                                <label class="col-lg-4 control-label resize" style="text-align: left;">Ente</label>
                                <div class="col-lg-8">
                                    <input id=registry_n tabindex=6 class="form-control resize" style=" border: 2px solid black;" placeholder="Ente ..." name=registry type=text value="" >
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 4%;">
                            <div class="col-lg-12"><button type="button" class="btn btn-primary" style="width: 100%;" onclick="startAjax('registry')">Cerca</button></div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div id="appendTableRegistry"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("#registry_n").keyup(function(event) {
        if (event.keyCode === 13) {
            startAjax("registry");
        }
    });
</script>