<?php

if (!session_id()) session_start();


include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include(INC . "/header.php");
include_once ( CLS."/cls_crypt.php" );

$cls_crypt = new cls_crypt();
?>

    <script src="<?= JS; ?>/myValidator.js"></script>

    <link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css"/>
    <script type="text/javascript" src="<?= DATATABLE ?>/datatables.js"></script>
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/1.2.2/css/searchPanes.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.1/css/select.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap.min.css">-->

<style>
    .back_spiners {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: rgba(0,0,0,0.80);
        z-index: 10000;
    }
</style>


<?php
include_once(INC . "/menu.php");
include_once CLS . "/cls_help.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

if ($_SESSION['username'] == NULL) {
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$proc_id =  $cls_help->getVar('proc_id');
$cod_cat =  $cls_help->getVar('cod_cat');
$auth =  $_SESSION['aut_tipo'];

?>
<style>
    td {
        text-align: center;
    }

    th {
        text-align: center;
    }
</style>

<div class="col col-md-auto text_center" style="margin-bottom:25px;">
    <span class="titolo font16 under_decor">Lista stragiudiziali per la procedura Id <?= $proc_id ?> per l'ente <?= $cod_cat ?> </span>
</div>
<div class="container">
    <?php
    $query_stragiudiziali =  "   SELECT s.*, b.ID AS Banca_ID, b.PEC,  p.Procedure_Date AS Procedure_Date,  b.Denominazione AS Denominazione, e_g.Denominazione AS Nome_Ente   " .
        "    FROM   stragiudiziali AS s  " .
        "       JOIN enti_gestiti AS e_g on  e_g.CC = s.CC " .
        "       JOIN banca AS b on  b.ID = s.Banca_Id " .
        "       JOIN procedures as p on p.Id = s.Procedure_Id " .
        "    WHERE s.Procedure_Id = " . $proc_id . " AND  s.CC ='" . $cod_cat . "' AND Data_Spedizione IS NULL";

    $results = $cls_db->ExecuteQuery($query_stragiudiziali);

    if (isset($results)) {
        $stragiudiziale_list = $cls_db->getResults($results);
        $xlsFile = "";
        $li = "";
        $hidden = "none";


        $pre_pec = array();
        $data_sped = array();
        foreach ($stragiudiziale_list as $pec) {
            if (isset($pec['PEC']) && !empty($pec['PEC'])) {
                $pre_pec[] = $pec['PEC'];
                $data_sped[] = $pec['Data_Spedizione'];
            } else {
                $li .= "<li>" . $pec['Denominazione'] . "</li>";
                $hidden = "";
            }
        }
        $disabled = "";
        switch (true) {

            case (count($pre_pec) !== count($stragiudiziale_list)):
                $disabled = "disabled";
                break;

            case (count($data_sped) == 0):
                $disabled = "disabled";
                break;
        }
        
    ?>

        <div class="back_spiners" id="caricamento_spiners">
            <div class="d-flex align-items-center text-info" style="width: 220px;position: fixed;top:45%;left:45%;text-align: center;">
                <div style="display: inline;"><img style="width: 100%; border-radius: 100px;opacity: 0.5;" src="<?= GIFWEB ?>/loading_3.gif"></div>
                <div id="text_spiners" style="display: inline;font-size: 18px;width:100%;text-align: center;font-weight: bold;">Loading...</div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">
                <a style="display: inline-block; margin-bottom:5px;" href="<?= WEB_ROOT ?>/controlli/lista_procedure.php?c=<?= $c ?>&a=<?= $a ?>" class="btn btn-primary" role="button">Lista Procedure</a>
                <button type="button" id="invio_pec_<?= $proc_id ?>" onclick="sendEmail(<?= $proc_id ?>,'<?= $cod_cat ?>')" class="btn btn-primary" style="display: inline-block; margin-bottom:5px;" <?= $disabled ?>>INVIO PEC</button>
                <?php // if ($disabled == 'disabled') {
                ?>                
                <button type="button" id="check_pec_<?= $proc_id ?>" onclick="checkPec(<?= $proc_id ?>,'<?= $cod_cat ?>')" class="btn btn-primary" style="display: inline-block; margin-bottom:5px;">CONTROLLO PEC</button>
                <?php // }?>
                <button id="msg_err_button" class="btn btn-danger  pull-right " style="display: inline-block; display: <?= $hidden ?>;" data-toggle="modal" data-target="#errModal">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp; &nbsp; ERROR
                </button>
            </div>
            <div id="errModal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">ATTENZIONE!!!!!!! Non sono presenti le pec per le seguenti banche: </h4>
                        </div>
                        <div class="modal-body">
                            <ol>
                                <?php
                                echo $li;
                                ?>
                            </ol>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 1%; margin-bottom: 1%;">
            <div class="col col-lg-12">
                <div class="form-group">
                    <label  class="col-lg-2 control-label resize" style="text-align: left;">Corpo mail</label>
                    <div class="col-lg-10">
                        <textarea style="max-width: 100%;" class="text_left form-control resize" id=body_id name=body ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <table id="example" class="table table-striped table-bordered wrap" style="border:3px solid #6D95D5; width:100%;  margin-left:3px; ">
            <thead>
                <tr>
                    <th>Ente</th>
                    <th>Banca</th>
                    <th>Stragiudiziale PDF</th>
                    <th>Stragiudiziale EXCEL</th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($stragiudiziale_list as $stragiudiziale) {

                    $type_tax = !is_null($stragiudiziale['Tipo_Riscossione']) ? $stragiudiziale['Tipo_Riscossione'] : "COMPLETO";

                    $path =  SUPER_WEB_ROOT . "/archivio/stragiudiziale/" . $stragiudiziale['Id'];

                    $nameFile = "Stragiudiziale_Banca_" . $cod_cat . "_" . $stragiudiziale['Banca_ID'] . "_" . $type_tax . "_" . $stragiudiziale['Procedure_Date'] . ".pdf";
                    $completePath = $path . "/" . $nameFile;

                    $nameFilexls = "Elenco_Stragiudiziale_Banca_" . $cod_cat . "_" . $stragiudiziale['Banca_ID'] . "_" . $type_tax . "_" . $stragiudiziale['Procedure_Date'] . ".xlsx";
                    $xlsFile = $path . "/" . $nameFilexls;

                ?>

                    <tr>
                        <td><?php echo $stragiudiziale['CC'] . " - " . $stragiudiziale['Nome_Ente']; ?></td>
                        <td><?php echo $stragiudiziale['Denominazione'] ?></td>
                        <td class="text-center">
                            <i class="fas fa-file-pdf fa-2x " style=" color:darkred; cursor:pointer; " aria-hidden="true" data-toggle="modal" data-target="#stragiudiziale_pdf_<?= $stragiudiziale['Banca_ID'] ?>"></i>
                            <div class="modal fade" id="stragiudiziale_pdf_<?= $stragiudiziale['Banca_ID'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document" style="text-align: center;">
                                    <div class="modal-body">
                                        <iframe src="<?php echo $completePath; ?>" type="application/pdf" style="  margin-left: -250px; width:1000px; height:600px;"></iframe>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="<?= $xlsFile ?>" onMouseover="title='Modello principale'" style="text-decoration: none;" download="<?= $nameFilexls ?>">
                                <img src="<?= IMMAGINIWEB; ?>/icon-excel.png" width=30px height=30px>
                            </a>
                        </td>
                    </tr>
                <?php
                }
            } // if (isset($results))
            else {
                ?>
                <tr>
                    <td colspan="6">
                        Non Sono presenti dati
                    </td>
                </tr>
            </tbody>
        </table>
        
    <?php
                return;
            }
    ?>
    </tbody>
    </table>
</div>

<!--<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/searchpanes/1.2.2/js/dataTables.searchPanes.min.js"></script>
<script src="https://cdn.datatables.net/searchpanes/1.2.2/js/searchPanes.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>-->

<script>
    function startSpiners(){
        //alert("show");
        //$("#caricamento_spiners").css("display","block");
        $("#caricamento_spiners").show();
    }

    function closeSpiner(){
        //alert("close");
        $("#caricamento_spiners").hide();
    }
</script>



<script>
    var table;
    $(document).ready(function() {

        $('#example').DataTable({
            "dom": 'lfrtip',
            "language": {
                "url": "<?=DATATABLE?>/dt_IT.json"
            },
            "order": [
                [0, "desc"]
            ],
            //or asc 

        });
    });
</script>
<script>
    function sendEmail(id_proc, cod_cat) {

        $("#body_id").addClass("validateCustom vld_Custom_r");

        if(validateForm()) {
            startSpiners();

            $.ajax({
                url: 'ajax/ajax_Send_Email.php',
                type: 'POST',
                data: {
                    'id_proc': id_proc,
                    'cod_cat': cod_cat,
                    'body': $("#body_id").val()
                },
                async: true,

                success: function (response) {

                    closeSpiner();

                    var risposta = JSON.parse(response);

                    if (risposta.esito == "OK") {
                        swal({
                            title: "UPDATE!",
                            text: risposta.message,
                            icon: "success",
                            timer: 3000,
                            buttons: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        swal({
                            title: "ERROR!",
                            text: risposta.message,
                            icon: 'warning',
                            timer: 3000,
                            buttons: false
                        })
                    }
                },
                error: function (error) {

                    closeSpiner();
                    console.log(error)
                }
            });
        }


    }
</script>
<script>
    function checkPec(id_proc, cod_cat){

        if(confirm("Prima di scaricare le pec controllare che abbiano un esito e che siano state ricevute!\nPassaggio eseguito?")) {

            var paginaRicevute = "<?= WEB_ROOT; ?>/controlli/controlla_pec.php?c=<?php echo $cod_cat;?>&id_proc=<?php echo $proc_id;?>";
            window.open(paginaRicevute, 'ricevute', 'width=1500,height=500,top=70,left=70,scrollbars=yes,menubar=no');
        }
    }
</script>


<?php
include(INC . "/footer.php");
?>