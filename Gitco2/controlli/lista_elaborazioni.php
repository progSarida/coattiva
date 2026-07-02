<?php

require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC . "/header.php");
include_once(INC . "/menu.php");
include_once CLS . "/cls_help.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$last_el_id = $cls_help->getVar('el');

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$auth = $_SESSION['aut_tipo'];

?>
<style>
    td {
        text-align: center;
    }

    th {
        text-align: center;
    }
</style>

<link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css" />
<script type="text/javascript" src="<?= DATATABLE ?>/datatables.min.js"></script>

<div class="col col-md-auto text_center">
    <span class="titolo font16 under_decor">Lista Elaborazioni</span>
</div>
<div class="container" style="width:95%">

    <table id="example" class="table table-striped table-bordered wrap"
        style="border:3px solid #6D95D5; width:100%;  margin-left:3px; ">
        <thead>
            <tr>
                <th>Ente</th>
                <th>Descrizione</th>
                <th>Atto</th>
                <th>Stato</th>
                <th>Data</th>
                <th>Operatore</th>
                <th>Elaborazione</th>

            </tr>
        </thead>
        <tbody>
            <?php
            $query_elab_list = "   SELECT  el.Id AS EL_ID, " .
                "           el.Creation_Date AS DATA_CR, " .
                "           el.Update_Date AS DATA_AGG, " .
                "           el.Description AS DESCRIZIONE,  " .
                "           el_s.Name AS STATO,  " .
                "           el.CC AS CC, " .
                "           el.Creation_Username AS OPERATORE_CR,   " .
                "           el.Update_Username AS OPERATORE_AGG,    " .
                "           el.Elaboration_Status_Id,   " .
                "           el.Document_Type_Id,   " .
                "           e_g.Denominazione AS NOME_ENTE,  " .
                "           dt.Description AS TIPO_ATTO,  " .
                "           ( CASE WHEN el.Update_Date > el.Creation_Date THEN el.Update_Date ELSE el.Creation_Date END ) AS DATA_ELABORAZIONE_RECENTE " .
                "   FROM    elaborations AS el  " .
                "       JOIN elaboration_status AS el_s on el_s.Id = el.Elaboration_Status_Id  " .
                "       JOIN enti_gestiti AS e_g on e_g.CC = el.CC  " .
                "       JOIN document_type AS dt on dt.Id = el.Document_Type_Id  ";

            if (intval($auth) !== 1) {
                $query_elab_list .= " WHERE el.CC = '" . $c . "' ";
            }

            $query_elab_list .= " GROUP BY el.Id ORDER BY el.Id ASC ";

            $results = $cls_db->ExecuteQuery($query_elab_list);

            if (isset($results)) {
                $elab_lists = $cls_db->getResults($results);

                $Cancellabili = function ($elab) {
                    $stato = $elab['Elaboration_Status_Id'];
                    $doctype = $elab['Document_Type_Id'];
                    $username = $_SESSION['username'];
                    if ($username == "fabrizio")
                        return ""; //superpower for debugging
                    if ($stato == 9999)
                        return ""; //estrazioni le possono cancellare tutti
                    if ($doctype == 2) //atto
                    {
                        if ($username == "mirkop") if (($stato >= ElaborationStatus::PDF_CREATI_ATTI && $stato <= ElaborationStatus::FLUSSI_CHIUSI_ATTI))
                            return "display: none;";
                        if ($username == "robertop") if (($stato >= ElaborationStatus::PDF_CREATI_ATTI && $stato <= ElaborationStatus::FLUSSI_CHIUSI_ATTI))
                            return "display: none;";
                        if ($username == "fabrizio") if (($stato >= ElaborationStatus::PDF_CREATI_ATTI && $stato <= ElaborationStatus::FLUSSI_CHIUSI_ATTI))
                            return "display: none;";
                    }
                    if ($doctype == 22) //preavviso fermo
                    {
                        if ($username == "mirkop") if (($stato >= ElaborationStatus::PDF_CREATI && $stato <= ElaborationStatus::FLUSSI_CHIUSI))
                            return "display: none;";
                        if ($username == "robertop") if (($stato >= ElaborationStatus::PDF_CREATI && $stato <= ElaborationStatus::FLUSSI_CHIUSI))
                            return "display: none;";
                        if ($username == "fabrizio") if (($stato >= ElaborationStatus::PDF_CREATI && $stato <= ElaborationStatus::FLUSSI_CHIUSI))
                            return "display: none;";
                    }
                    if ($doctype == 7 || $doctype == 8) //pigno terzo
                    {
                        if ($username == "mirkop") if (($stato >= ElaborationStatus::PDF_CREATI && $stato <= ElaborationStatus::FLUSSI_CHIUSI))
                            return "display: none;";
                        if ($username == "robertop") if (($stato >= ElaborationStatus::PDF_CREATI && $stato <= ElaborationStatus::FLUSSI_CHIUSI))
                            return "display: none;";
                        if ($username == "fabrizio") if (($stato >= ElaborationStatus::PDF_CREATI && $stato <= ElaborationStatus::FLUSSI_CHIUSI))
                            return "display: none;";
                    }
                    $users = array("fabrizio", "robertop", "mirkop", "gianluca","michele");
                    if (in_array($username, $users))
                        return "";
                    return "display: none;";
                };

                foreach ($elab_lists as $elab) {

                    $operatore = $elab['OPERATORE_CR'];

                    $rec_date = strtotime($elab['DATA_ELABORAZIONE_RECENTE']);
                    $data_recente = date('d/m/y', $rec_date);

                    if (!is_null($elab['OPERATORE_AGG'])) {
                        $operatore = $elab['OPERATORE_AGG'];
                    }


                    $hiddenDelete = $Cancellabili($elab);

                    $classStyle = "primary";
                    if ($elab['STATO'] == "Flussi Chiusi") {
                        $classStyle = "success";
                        $elab['STATO'] = "<b style='color: darkgreen;'>" . $elab['STATO'] . "</b>";
                    }

                    ?>
                    <tr>
                        <td>
                            <?php echo $elab['CC'] . ' - ' . $elab['NOME_ENTE']; ?>
                        </td>
                        <td>
                            <?php echo $elab['DESCRIZIONE']; ?>
                        </td>
                        <td>
                            <?php echo $elab['TIPO_ATTO']; ?>
                        </td>
                        <td>
                            <?php echo $elab['STATO']; ?>
                        </td>
                        <td>
                            <?php echo $data_recente; ?>
                        </td>
                        <td>
                            <?php echo $operatore; ?>
                        </td>
                        <td>
                            <div style="margin: 0 auto; width: 200px; text-align: center;">
                                <button type="button" class="btn btn-<?= $classStyle; ?> showElab"
                                    id="<?= (string) $elab['CC'] ?>_<?= $elab['EL_ID'] ?>_<?= $elab['Document_Type_Id'] ?>_<?= $elab['Elaboration_Status_Id'] ?>"
                                    name="elab_<?= $elab['Document_Type_Id'] ?>">Visualizza</button>
                                <button type="button" style="<?php echo $hiddenDelete ?>" class="btn btn-danger deleteElab"
                                    id="<?= (string) $elab['CC'] ?>_<?= $elab['EL_ID'] ?>_<?= $elab['Document_Type_Id'] ?>"
                                    name="elab_<?= $elab['Document_Type_Id'] ?>">Elimina</button>
                            </div>
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


<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>


<script>
    $(document).ready(function () {
        $('#example').DataTable({
            buttons: [
                {
                    extend: 'searchPanes',
                    config: {
                        cascadePanes: true
                    }
                }
            ],
            dom: 'Bfrtip',
            columnDefs: [
                {
                    searchPanes: {
                        show: true
                    },
                    targets: [0, 3, 5]
                },
            ],
            searchPanes: {
                initCollapsed: true,
            },
            "language": {
                "url": "<?= DATATABLE ?>/dt_IT.json"
            },

        });
    });

    var c = '<?= $c; ?>';
    var a = '<?= $a; ?>';

    $(document).ready(function () {


        $('#example').on('click', '.showElab', function () {
            var par_String = this.id;

            var els = par_String.split('_')[1];
            var docId = parseInt(par_String.split('_')[2]);
            var stato = parseInt(par_String.split('_')[3])
            var elab_id = els.replace(/[^0-9.]/g, "");

            var cod_cat = par_String.split('_')[0];

            var par_Name = this.name;
            var tipoatto = par_Name.replace(/[^0-9.]/g, "");
            console.log(docId);
            switch (docId) {
                case 7:
                    var link = "<?= ELAB_PIGNORAMENTI_LAVORO_WEB ?>/mgmt_pignoramenti_lavoro";
                    break;
                case 8:
                    var link = "<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche";
                    break;
                case 6:
                case 22:
                    var link = "<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti";
                    break;
                case 43:
                    //if (stato == '9999')  
                    var link = "<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_estrazioni";
                    break;
                default:
                    var link = "<?= ELAB_ATTI_WEB ?>/mgmt_elaboration";
                    break;
            }

            // var link = this.id.indexOf("Pigno")>-1 ? "<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti" : "<?= ELAB_ATTI_WEB ?>/mgmt_elaboration";
            window.location.href = link + ".php?c=" + c + "&a=" + a + "&el=" + elab_id;

        });
    });

    // DELETE

    $(document).ready(function () {



        $('#example').on('click', '.deleteElab', function () {

            var par_String = this.id;

            var els = par_String.split('_')[1];
            var docId = par_String.split('_')[2];
            var link_delete = "ajax/ajax_delete_elaborazioni.php";
            if (docId == 43) link_delete = "ajax/ajax_delete_estrazione.php";

            swal({
                title: "SEI SICURO?",
                text: "Una volta eliminata l\'elaborazione non può più essere recuperata!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {

                        $.ajax({
                            type: "POST",
                            url: link_delete,
                            data: { "el": els, },
                            cache: false,
                            success: function (response) {
                                var response = JSON.parse(response);

                                if (response.esito == "OK") {
                                    swal({
                                        title: "SUCCESS!",
                                        text: response.message,
                                        icon: "success",
                                        timer: 25000,
                                        buttons: false
                                    });
                                    window.location.href = "<?= WEB_ROOT ?>/controlli/lista_elaborazioni.php?&p=&c=" + c + "&a=" + a;
                                }
                                else {

                                    swal({
                                        title: "ERROR!",
                                        text: response.message,
                                        icon: "danger",
                                        timer: 5000,
                                        buttons: false
                                    });

                                }

                            },
                            error: function (error) {
                                console.log(error)
                            }
                        });


                    } else {
                        swal("La tua elaborazione è salva!");
                    }
                });





        });
    });


</script>
<?php
include(INC . "/footer.php");
?>