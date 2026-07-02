<?php include_once ROOT."/search_modal/startAjax.php"; ?>

<div class="modal fade offcanvas" id="ListModal" tabindex="-1" name="list" aria-labelledby="ListLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 60% !important;height: 55vh !important;">
        <div class="modal-content" style="height: 80vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="ListLabel" style="color: blue;"><b>Lista Codici Tributo</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body col-lg-12" style="overflow: auto">
                <!-- <div class="col-lg-12"> -->
                    <div class="tableFixHead" id="appendTableList" style="table-layout: auto;">
                        <table class="table tableList table-hover" id="result_list">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Codice</th>
                                    <th scope="col">Settore</th>
                                    <th scope="col">Descrizione</th>
                                    <th scope="col">Autorità</th>
                                </tr>
                            </thead>
                            <tbody id=list style="overflow-y:auto;">

                            </tbody>
                        </table>
                    </div>
                <!-- </div> -->
            </div>
        </div>
    </div>
</div>

<style>

    .tableList { border-collapse: collapse; width: 100%; }

    .tableList th, .tableList td { background: #fff; padding: 8px 16px; }
    .tableList th {background-color: #85C0FF; color: white; border: 1px solid #ddd;}
    .tableList td {background-color: #D1E7FD; border: 1px solid #ddd;}

    .tableFixHead {overflow: auto; height: 70vh;}

    .tableFixHead thead th {position: sticky; top: 0;}

</style>