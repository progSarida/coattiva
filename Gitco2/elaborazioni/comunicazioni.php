<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_DateTimeInLine.php");
include_once CLS."/cls_file.php";
include_once CLS."/cls_html.php";

// Recupero parametri (da GET o POST)
$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$ProcedureTypeId = $cls_help->getVar("procedureTypeId");
$annoFiltro = $cls_help->getVar("anno_filtro");

// Gestione permessi e utenti autorizzati
$utentiAutorizzati = array("mirkop", "riccardo", "superadmin");
$usernameAttuale = strtolower($_SESSION['username']);
$authFlag = (isset($_SESSION['aut_tipo']) && $_SESSION['aut_tipo'] == "1" && in_array($usernameAttuale, $utentiAutorizzati)) ? 1 : 0;

$cls_date = new cls_DateTimeI("IT",false);
$cls_file = new cls_file();

// Query per i tipi di procedura (per le select)
$query = "SELECT * FROM procedure_types WHERE Flag_Comunicazioni=1";
$a_procedureTypes = $cls_db->getResults($cls_db->ExecuteQuery($query));
$cls_html = new cls_html();
$a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $ProcedureTypeId, "text" => array("[Name]"));
$opt_procedureTypes = $cls_html->getOptions($a_procedureTypes,$a_selection);

// Anni distinti per il filtro (solo procedure che valorizzano Anno_Procedura).
$queryAnni = "SELECT DISTINCT Anno_Procedura FROM procedures
               WHERE CC = '".$c."' AND Anno_Procedura IS NOT NULL
               ORDER BY Anno_Procedura DESC";
$a_anni = $cls_db->getResults($cls_db->ExecuteQuery($queryAnni));

// Query principale per la tabella
$query = "SELECT P.*,P.Id as Id, PT.Name as Procedure_Type, A.User as Username FROM procedures P JOIN autenticazione A ON A.ID=P.User_Id JOIN procedure_types PT ON PT.Id=P.Procedure_Type_Id WHERE P.CC = '".$c."' AND PT.Flag_Comunicazioni=1 ";
if(!empty($ProcedureTypeId)) $query .= " AND Procedure_Type_ID = ".$ProcedureTypeId." ";
if($annoFiltro !== null && $annoFiltro !== "") $query .= " AND P.Anno_Procedura = ".(int)$annoFiltro." ";
$query .= "ORDER BY Datetime DESC";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$count = count($result);
$object_table = array();

for($i=0; $i < $count; $i++){
    $object_table[$i]["Procedure_Type"] = $result[$i]["Procedure_Type"];
    $object_table[$i]["Procedure_Date"] = $cls_date->Get_DateNewFormat($result[$i]["Procedure_Date"],"DB");
    $object_table[$i]["Anno"] = ($result[$i]["Anno_Procedura"] !== null && $result[$i]["Anno_Procedura"] !== "")
        ? $result[$i]["Anno_Procedura"] : "&mdash;";
    $object_table[$i]["Descrizione"] = $result[$i]["Description"];
    $object_table[$i]["Id"] = $result[$i]["Id"];
    $object_table[$i]["Username"] = $result[$i]["Username"];

    // Gestione visualizzazione File
    if(is_dir(PROCEDURE.$result[$i]['Id'])) {
        $a_files = $cls_file->getFilesFromPath(PROCEDURE . $result[$i]['Id'], PROCEDURE_WEB . $result[$i]['Id']);
        $htmlFile = "";
        foreach ($a_files as $a_file) {
            $htmlFile .= "<img src='" . $a_file['icon'] . "' width=25 style='cursor: pointer; margin-right:5px;' title='" . $a_file['fileName'] . "' onclick='showF(\"" . $a_file['fileWeb'] . "\");'>";
        }
        $object_table[$i]["Files"] = $htmlFile;
    } else {
        $object_table[$i]["Files"] = "<img src='" . IMG . "/icon_unknown.png' width=25 style='cursor: pointer; margin-right:5px;' title='File non ancora caricato' >";
    }

    // Colonna Elimina (solo per autorizzati) - Usando IMMAGINIWEB e elimina_icon.png
    $object_table[$i]["Elimina"] = "";
    if ($authFlag == 1) {
        if ($result[$i]["Procedure_Type_Id"] == 5) {
            $object_table[$i]["Elimina"] = "<img src='".IMMAGINIWEB."/elimina_icon.png' width=20 style='cursor: pointer;' title='Elimina Art.17' onclick='EliminaArt17(\"".$result[$i]["Id"]."\",\"".$c."\",\"".$result[$i]["Anno_Riferimento"]."\")'>";
        } else if ($result[$i]["Procedure_Type_Id"] == 2) {
            $object_table[$i]["Elimina"] = "<img src='".IMMAGINIWEB."/elimina_icon.png' width=20 style='cursor: pointer;' title='Elimina Elaborazione Discarichi' onclick='EliminaElaborazioneSgravi(\"".$result[$i]["Id"]."\")'>";
        }
    }
}
?>

<div class="back_spiners" id="caricamento_spiners">
    <div class="d-flex align-items-center text-info" style="width: 220px;position: fixed;top:45%;left:45%;text-align: center;">
        <div><img style="width: 100%; border-radius: 100px;opacity: 0.5;" src="<?= GIFWEB ?>/loading_3.gif"></div>
        <div id="text_spiners" style="font-size: 18px;width:100%;text-align: center;font-weight: bold; padding-left:10px;">Loading...</div>
    </div>
</div>

<style>
    .back_spiners { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100%; background: rgba(0,0,0,0.80); z-index: 10000; }
    .under_decor { text-decoration: underline; font-weight: bold; }
</style>

<script>
    function showF(path){ showFileOnModal(path,"File PDF",path.split('.').pop()); }
    function startSpiners(){ $("#caricamento_spiners").show(); }
    function closeSpiner(){ $("#caricamento_spiners").hide(); }
</script>

<form method=post action="comunicazioni.php">
    <input type="hidden" name="c" value="<?= $c; ?>">
    <input type="hidden" name="a" value="<?= $a; ?>">
    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center"><span class="titolo font16 under_decor">PROCEDURE</span></div>
    </div>
    <div class="row" style="margin-top: 2%;">
        <div class="col col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize">Tipo Procedura</label>
                <div class="col-lg-8"><select name="procedureTypeId" id="procedureTypeId" class="form-control"><?= $opt_procedureTypes; ?></select></div>
            </div>
        </div>
        <div class="col col-lg-3">
            <div class="form-group">
                <label class="col-lg-4 control-label resize">Anno</label>
                <div class="col-lg-8">
                    <select name="anno_filtro" id="anno_filtro" class="form-control">
                        <option value="">Tutti</option>
                        <?php foreach ($a_anni as $row_anno): $val = (int)$row_anno['Anno_Procedura']; ?>
                            <option value="<?= $val ?>" <?= ((string)$annoFiltro === (string)$val) ? 'selected' : '' ?>><?= $val ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-2"><button type="submit" class="btn btn-primary" name="filtro" >Filtra</button></div>
    </div>
</form>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%; margin-top: 1%;"></div>

<div class="row">
    <div class="col-lg-offset-1 col-lg-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="checkManual" onchange="showHideDiv(this,'divManual');">
            <label class="form-check-label" for="checkManual">Inserimento manuale</label>
        </div>
    </div>
</div>

<div style="display: none;" id="divManual">
    <form action="save_procedure_manualy.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="c" value="<?=$c?>">
        <input type="hidden" name="file_name" id="file_name">
        <div class="row">
            <div class="col-lg-offset-1 col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label">Tipo</label>
                    <div class="col-lg-8"><select name="procedureTypeId" class="form-control"><?= $opt_procedureTypes; ?></select></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" name="sovrascrivi" id="sovr">
                    <label class="form-check-label" for="sovr">Sovrascrivi esistente</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label">Scegli File</label>
                    <div class="col-lg-8"><input type="file" class="form-control" id="file_choice" name="file_choice" /></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label">Anno Rif.</label>
                    <div class="col-lg-8"><input type="text" class="form-control" name="anno" maxlength="4" placeholder="AAAA"></div>
                </div>
            </div>
            <div class="col-lg-2"><button type="button" class="btn btn-primary" onclick="if(confirm('Confermi il caricamento?')) $(this).closest('form').submit();">Carica</button></div>
        </div>
    </form>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%; margin-top: 1%;"></div>

<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>

<script type="text/javascript">
    $('#file_choice').change(function(){ $('#file_name').val($(this).val()); });
    function showHideDiv(el,id){ if($("#"+id).is(":visible")) $("#"+id).hide(); else $("#"+id).show(); }

    $(document).ready(function(){
        var toprint = [
            {originalName: "Procedure_Type", replacedName: "Tipo procedura"},
            {originalName: "Procedure_Date", replacedName: "Data", type: "date"},
            {originalName: "Anno", replacedName: "Anno"},
            {originalName: "Descrizione", replacedName: "Descrizione"},
            {originalName: "Username", replacedName: "Operatore"},
            {originalName: "Files", replacedName: "Files"}
            <?php if ($authFlag == 1) { ?> ,{originalName: "Elimina", replacedName: "Elimina"} <?php } ?>
        ];
        var widthCell = <?= ($authFlag == 1) ? '["16%","9%","6%","36%","12%","9%","12%"]' : '["18%","10%","7%","40%","13%","12%"]' ?>;

        new TableGenerator(<?= json_encode($object_table)?>, toprint, widthCell, "10px");
    });

    function smartReload() {
        var comune = "<?= $c ?>";
        var anno = "<?= $a ?>";
        window.location.href = "comunicazioni.php?c=" + comune + "&a=" + anno;
    }

    function EliminaArt17(id, comune, anno) {
        swal({ title: "ATTENZIONE", text: "Verranno resettati i pignoramenti associati all'Art.17. Continuare?", icon: "warning", buttons: true, dangerMode: true })
        .then((willDelete) => {
            if (willDelete) {
                startSpiners();
                $.ajax({
                    type: "POST",
                    url: "<?= WEB_ROOT ?>/controlli/ajax/ajax_delete_procedure_art17.php",
                    data: { "proc_id": id, "comune": comune, "anno_riferimento": anno },
                    success: function(r){ closeSpiner(); smartReload(); }
                });
            }
        });
    }

    function EliminaElaborazioneSgravi(id) {
        // Step 1 — anteprima (sola lettura): mostra impatto della cancellazione.
        startSpiners();
        $.ajax({
            type: "POST",
            url: "<?= WEB_ROOT ?>/controlli/ajax/ajax_delete_elaborazione_sgravi.php",
            data: { "proc_id": id, "action": "preview", "cc": "<?= $c ?>" },
            dataType: "json",
            success: function(r) {
                closeSpiner();
                if (!r || r.esito !== 'OK') {
                    swal({ title: "Errore", text: (r && r.message) || "Anteprima non disponibile.", icon: "error" });
                    return;
                }
                var d = r.data;
                if (!d.is_anno_piu_recente) {
                    swal({
                        title: "Cancellazione bloccata",
                        text: "L'anno " + d.anno + " non e' il piu' recente per questo ente. Cancellare prima le elaborazioni piu' recenti.",
                        icon: "warning"
                    });
                    return;
                }
                var msg = "Anno: " + d.anno
                    + "\nPartite coinvolte: " + d.partite_totali;
                if (d.anno_precedente !== null) {
                    msg += "\n\nVerra' eseguito il ricalcolo retroattivo dell'anno "
                        + d.anno_precedente
                        + " (data_elab originale: " + (d.procedure_date_precedente || '') + ")."
                        + "\nPosizioni I dell'anno precedente da rivalutare: " + d.partite_I_precedenti + ".";
                } else {
                    msg += "\n\nNessun anno precedente: nessun ricalcolo retroattivo.";
                }
                msg += "\n\nL'operazione e' irreversibile. Continuare?";

                swal({
                    title: "Confermi cancellazione?",
                    text: msg,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true
                }).then(function(willDelete) {
                    if (!willDelete) return;
                    startSpiners();
                    $.ajax({
                        type: "POST",
                        url: "<?= WEB_ROOT ?>/controlli/ajax/ajax_delete_elaborazione_sgravi.php",
                        data: { "proc_id": id, "action": "delete", "cc": "<?= $c ?>" },
                        dataType: "json",
                        success: function(r2) {
                            closeSpiner();
                            if (r2 && r2.esito === 'OK') {
                                swal({ title: "Cancellazione completata", text: r2.message, icon: "success" })
                                    .then(function(){ smartReload(); });
                            } else {
                                swal({ title: "Errore", text: (r2 && r2.message) || "Cancellazione fallita.", icon: "error" });
                            }
                        },
                        error: function() {
                            closeSpiner();
                            swal({ title: "Errore", text: "Comunicazione fallita.", icon: "error" });
                        }
                    });
                });
            },
            error: function() {
                closeSpiner();
                swal({ title: "Errore", text: "Anteprima fallita.", icon: "error" });
            }
        });
    }
</script>