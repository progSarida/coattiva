<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC . "/header.php");
include(INC . "/menu.php");

include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_check.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_elaboration.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/BuildMotivationText.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once(CLS . "/cls_GestionePartita.php");
include_once CLS . "/cls_storico.php";
include_once ROOT . "/sgravi/cls/Cls_Classificatore_Sgravi.php";

define('DATA_STOP_COATTIVA', '31/10/2023');
// TEMPORANEO — rimuovere quando la coattiva riprende. Per disattivare: impostare a null.
// Quando attiva: per le annualità 2022 e 2023 fissa la data di elaborazione al 31/10/2023
// (readonly) e attiva automaticamente l'Informativa Cessione; dal 2024 in poi blocca.

//$cls_partita = new cls_GP();
$cls_date = new cls_DateTimeI("IT", false);
$cls_check = new cls_check();
$cls_Utils = new cls_Utils();
$cls_elab = new cls_elaborazioniUtils();
$cls_elaboration = new cls_elaboration();
$storico = new storico('storicoElaborazioni', '5');
//AGGIUNGERE SANZIONE DA INGIUNZIONE

if ($cls_help->getVar('iniziaSgravio') == "si") {

    set_time_limit(-1);

    // $_SESSION['progress'] = "0.00";
    // session_write_close();

    // echo "<script>spinner = new mySpinner('spinner_page','".AJAXWEB."/session_progress.php');spinner.startSpinner();</script>";
}

$storico_msg = "Elaborazione massiva discarichi";
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '" . $c . "'"));
$nome_ente = $ente['Denominazione'];

$data_elab_visual = date('d/m/Y');
$disabled = "";

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for ($i = 0; $i < count($resIngiunzioni); $i++)
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";

$a_parAnnuali = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM parametri_annuali WHERE CC='" . $c . "' AND Anno=" . date('Y')), "array", "Anno");
if (!empty($a_parAnnuali[date('Y')]))
    $a_params['Parametri_Annuali'] = $a_parAnnuali[date('Y')];
else {
    echo "PARAMETRI ANNO " . date('Y') . " ASSENTI!";
    die;
}
$cls_elaboration->setParams($a_params);

// Riepilogo sequenzialita' anno per la UI: ultimo anno elaborato per CC.
$rs_ultimo = $cls_db->getResults($cls_db->ExecuteQuery(
    "SELECT MAX(Anno_Procedura) AS max_anno FROM procedures
      WHERE CC = '" . $c . "' AND Procedure_Type_Id = 2 AND Anno_Procedura IS NOT NULL"
));
$ultimo_anno_elaborato = (!empty($rs_ultimo) && $rs_ultimo[0]['max_anno'] !== null)
    ? (int)$rs_ultimo[0]['max_anno'] : null;
$prossimo_anno_elaborabile = $ultimo_anno_elaborato !== null
    ? $ultimo_anno_elaborato + 1 : null;

// Primo anno suggerito (usato solo quando non c'e' elaborazione precedente):
// MIN(YEAR(ruolo.Data_Fornitura)) sulle partite di questo CC.
$rs_primo = $cls_db->getResults($cls_db->ExecuteQuery(
    "SELECT MIN(YEAR(r.Data_Fornitura)) AS primo_anno
       FROM partita_tributi pt
       JOIN ruolo r ON r.ID = pt.Ruolo_ID
      WHERE pt.CC = '" . $c . "'
        AND r.Data_Fornitura IS NOT NULL"
));
$primo_anno_suggerito = (!empty($rs_primo) && $rs_primo[0]['primo_anno'] !== null)
    ? (int)$rs_primo[0]['primo_anno'] : null;

// Blocco elaborazione: nessuna elaborazione precedente E nessun ruolo con Data_Fornitura.
$blocca_elaborazione = ($ultimo_anno_elaborato === null && $primo_anno_suggerito === null);

// Stop coattiva: annata che il form sta per elaborare (da sequenzialita', non da data-2)
// e stato dello stop. Riusati da UI e processing nella stessa request.
$anno_target = ($ultimo_anno_elaborato !== null) ? $prossimo_anno_elaborabile : $primo_anno_suggerito;
$stop_coattiva_attivo   = (DATA_STOP_COATTIVA !== null && $anno_target !== null && $anno_target >= 2022 && $anno_target <= 2023);
$stop_coattiva_bloccato = (DATA_STOP_COATTIVA !== null && $anno_target !== null && $anno_target >= 2024);

if ($cls_help->getVar("data_elab") != NULL) {
    // L'utente ha gia' valorizzato data_elab (anche via POST): vince quello.
    $data_elab_form = $cls_help->getVar("data_elab");
} elseif ($ultimo_anno_elaborato === null && $primo_anno_suggerito !== null) {
    // Caso 2: primo accesso + dati presenti -> precompila a 31/03/(primo_anno + 2)
    // cosi' anno_elab (= data_elab - 2) coincide con $primo_anno_suggerito.
    $data_elab_form = "31/03/" . ($primo_anno_suggerito + 2);
} elseif ($prossimo_anno_elaborabile !== null) {
    // Caso 1: esiste gia' una elaborazione -> precompila a 31/03/(prossimo + 2)
    // cosi' anno_elab (= data_elab - 2) coincide con MAX(Anno_Procedura) + 1.
    $data_elab_form = "31/03/" . ($prossimo_anno_elaborabile + 2);
} else {
    // Fallback: oggi (Caso 3 blocca_elaborazione, submit comunque bloccato lato JS).
    $data_elab_form = date("d/m/Y");
}

// Stop coattiva: per le annate 2022/2023 la data e' fissata al 31/10/2023 (campo readonly).
if ($stop_coattiva_attivo) $data_elab_form = DATA_STOP_COATTIVA;

$dataTemp = new cls_DateTime($data_elab_form, "IT", false);

$dataTemp->AddYear("-2");
$annoIniz = $dataTemp->GetYear();

// Stop coattiva: mostra l'annata reale (2022/2023), non data-2 (che darebbe 2021).
if ($stop_coattiva_attivo) $annoIniz = $anno_target;

$extra_informativa = $cls_help->getVar("extra_informativa");

if ($extra_informativa == "si") $extraFlag = "checked";
else $extraFlag = "";

// Stop coattiva: Informativa Cessione forzata (UI + valore consumato in fase di stampa).
if ($stop_coattiva_attivo) { $extra_informativa = "si"; $extraFlag = "checked"; }

// Filtro multiselezione D/I/P: array di tipi da mostrare in stampa.
// - primo caricamento (no submit): default ['D','I'] (P deselezionato)
// - submit con tutto deselezionato: fallback "mostra tutto" (D+I+P)
$showPrintRaw = $cls_help->getVar("showPrint");
if ($showPrintRaw === null) {
    $showPrintDefault = array('D', 'I');
    $showPrint = $showPrintDefault;
} else {
    $showPrint = is_array($showPrintRaw) ? $showPrintRaw : array($showPrintRaw);
    $showPrint = array_values(array_intersect($showPrint, array('D', 'I', 'P')));
    if (empty($showPrint)) {
        $showPrint = array('D', 'I', 'P');
    }
}

?>
<script>
    // Esposto da PHP: blocca avvio elaborazione se l'ente non ha forniture caricate.
    var BLOCCA_ELABORAZIONE = <?= $blocca_elaborazione ? 'true' : 'false' ?>;
    // Esposto da PHP: blocca avvio se l'annata e' >= 2024 (coattiva sospesa dal 2024).
    var STOP_COATTIVA_BLOCCATO = <?= $stop_coattiva_bloccato ? 'true' : 'false' ?>;
</script>
<script src="<?= JS ?>/myValidator.js" type="text/javascript"></script>
<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_sel_offcanvas.php"); ?>
<script>
    //Modali offcanvas
    function openOfcanvas(type, rif) {
        // Reset campi input
        $('#sel_surn').val("");
        $('#sel_name').val("");
        $('#type_sel').val("all");

        // Reset spazi tabella
        $('#appendTableUserSel').empty();

        selectRif = rif;
        switch (type) {
            case 'user_sel':
                // Apre modale
                if (rif == 2 && $('#daco').val() == '')
                    alert("Inserire prima l'utente da cui far partire la ricerca");
                else
                    $('#userSelSearchModal').modal('show');
        }
    }

    function initialId(type, val) {
        switch (type) {
            case 'user_sel':
                if (selectRif == 1) // "Da Cognome/Nome"
                {
                    //alert("qui 1");
                    if (val['Ditta'] != '' && val['Ditta'] != null) { // è una ditta
                        $('#daco').val(val['Ditta']);
                        $('#acog').val(val['Ditta']);
                        $('#dano').val('');
                        $('#anom').val('');
                    } else { // è una persona
                        $('#daco').val(val['Cognome']);
                        $('#acog').val(val['Cognome']);
                        $('#dano').val(val['Nome']);
                        $('#anom').val(val['Nome']);
                    }

                } else if (selectRif == 2) // "A Cognome/Nome"
                {
                    if (val['Ditta'] != '' && val['Ditta'] != null) { // è una ditta
                        $('#acog').val(val['Ditta']);
                        $('#anom').val('');
                    } else { // è una persona
                        $('#acog').val(val['Cognome']);
                        $('#anom').val(val['Nome']);
                    }
                }
                break;
            default:
                alert("Errore Ricerca");
        }
    }
    //F5
    switchMenuImg("F5");
    F5_button = function() {
        $("#iniziaSgravio").val("no");
        location.href = "elaborazione_sgravi.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function() {
        if (BLOCCA_ELABORAZIONE) {
            alert("Nessuna fornitura presente per questo ente. Caricare i ruoli prima di elaborare i discarichi.");
            return;
        }
        if (STOP_COATTIVA_BLOCCATO) {
            alert("Dal 2024 la riscossione coattiva è sospesa (limite 31/10/2023). Annualità non elaborabile.");
            return;
        }
        $("#iniziaSgravio").val("si");
        if (validateForm())
            $("#sgravio_form").submit();
    }

    switchMenuImg("F11");
    F11_button = function() {

        $("#frameHelp").attr("src", "<?= SUPER_WEB_ROOT . "/archivio/help/Manuale_Operatore_Discarichi.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Manuale operatore — Discarichi Art. 19</b>");
        $("#helpModal").modal('show');
    }

    $(document).ready(function() {
        $("#tipo_partita").val('<?= $cls_help->getVar("tipo_partita") ?>');
        $("#da_n_elenco").val('<?= $cls_help->getVar("da_n_elenco") ?>');
        $("#a_n_elenco").val('<?= $cls_help->getVar("a_n_elenco") ?>');
        $("#data_elab").keyup(function() {
            var str = $("#data_elab").val();
            if (str.length == 8 && !str.includes("/")) {
                var day = str.substring(0, 2);
                var month = str.substring(2, 4);
                var year = str.substring(4);

                $("#data_elab").val(day + "/" + month + "/" + year);
            }
        });
    });

    function callParent(valorediritorno) {
        switch (selectParent) {
            case "utente":

                if (valorediritorno != null) {
                    $.post("ajax/ajax_cognome.php?c=<?php echo $c; ?>",

                        {
                            'ajax': 'nome',
                            'ID': valorediritorno
                        },

                        function(value) {

                            var array_ritorno = value.split('*');

                            console.log(array_ritorno);

                            if (selectRif == 1) {
                                $('#daco').val(array_ritorno[0]);
                                $('#acog').val(array_ritorno[0]);
                            } else if (selectRif == 2) {
                                $('#acog').val(array_ritorno[0]);
                            }

                            if (array_ritorno.length == 3) {
                                if (selectRif == 1) {
                                    $('#dano').val(array_ritorno[1]);
                                    $('#anom').val(array_ritorno[1]);
                                    $("#genere_da").val(array_ritorno[2]);
                                    $("#genere_a").val(array_ritorno[2]);
                                } else if (selectRif == 2) {
                                    $('#anom').val(array_ritorno[1]);
                                    $("#genere_a").val(array_ritorno[2]);
                                }
                            } else {
                                if (array_ritorno.length == 2) {
                                    if (selectRif == 1) {
                                        $("#genere_a").val(array_ritorno[1]);
                                        $("#genere_da").val(array_ritorno[1]);
                                    } else if (selectRif == 2) {
                                        $("#genere_a").val(array_ritorno[1]);
                                    }
                                } else $("#genere").val("");

                                if (selectRif == 1) {
                                    $('#dano').val("");
                                    $('#anom').val("");
                                } else if (selectRif == 2) {
                                    $('#anom').val("");
                                }
                            }
                        });
                }

                break;
        }

    }

    var selectParent = "";
    var selectRif = "";

    function RicercheDaId(value, rif) {
        selectParent = value;
        selectRif = rif;
        var valorediritorno = 0;
        //var strDim = Dim_Alert(600, 300);

        switch (value) {
            case "utente":

                //strDim = Dim_Alert(800, 500);
                var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale_sel.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                //valorediritorno = window.showModalDialog(stringa,"", strDim);
                openWindowSearch(stringa, {
                    width: 800,
                    height: 500,
                    left: (($(window).width() / 2) - 400),
                    top: (($(window).height() / 2) - 250)
                });

                break;
        }
    }

    function setDataElab(el, id) {
        $("#" + id).val("30/03/" + el.value);
    }

    function setAnnoElab(el, id) {
        $("#" + id).val(el.value.substring(6) - 2);
    }

    function startBar() {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text("Inizio elaborazione...");
    }

    function updateBar(valore) {
        $("#progressbar").progressbar({
            value: parseInt(valore)
        });
        $("#barlabel").text(valore + "%");
    }

    function noResultsBar(msg) {
        $("#progressbar").progressbar({ value: 100 });
        $("#progressbar .ui-progressbar-value").css("background", "#e8a020");
        $("#barlabel").text(msg || "Nessun risultato trovato");
    }

    function endBar() {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text("Elaborazione terminata!");
    }
</script>



<form id="sgravio_form" name="sgravio_form" action="elaborazione_sgravi.php" method="post">
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />
    <input type=hidden name="genere_da" id="genere_da" value="" />
    <input type=hidden name="genere_a" id="genere_a" value="" />
    <input type=hidden name="iniziaSgravio" id="iniziaSgravio" value="" />

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Elaborazione discarichi (Art. 19 D.Lgs. 112/99)</span>
        </div>
    </div>

    <div class="row" style="display: none;" id="div_report">
        <div class="col-lg-2 col-lg-offset-1"><b>Report PDF</b></div>
        <div class="col-lg-1"><a id="report_pdf" href="#" /*target="_blank" * /><img width="25" src="<?= IMMAGINIWEB; ?>\icon_pdf.png" onclick="downloadPdf();"></a></div>
        <div class="col-lg-3"></div>
        <div class="col-lg-2"><b>Report EXCEL</b></div>
        <div class="col-lg-1"><a id="report_excel" href="#"><img width="25" src="<?= IMMAGINIWEB; ?>\icon_excel.png" onclick="downloadXlsx();"></a></div>
    </div>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Selezione atti</span>
        </div>
    </div>

    <?php
    // Banner sequenzialita' anno - 3 stati con formato uniforme.
    // Caso 1: esiste elaborazione precedente (sfondo blu).
    // Caso 2: primo accesso + dati presenti (sfondo blu).
    // Caso 3: primo accesso + nessun dato (sfondo rosso, blocca elaborazione).
    if ($blocca_elaborazione) {
        $banner_bg     = '#FBE9E7';
        $banner_border = '#C62828';
        $banner_label  = 'nessuna';
        $banner_anno   = '&mdash; (nessuna fornitura presente)';
        $banner_extra  = '<br><b style="color:#C62828;">&#9888; Caricare i ruoli prima di elaborare i discarichi.</b>';
    } elseif ($ultimo_anno_elaborato === null) {
        // Caso 2
        $banner_bg     = '#EEF2FF';
        $banner_border = '#4F6FBF';
        $banner_label  = 'nessuna';
        $banner_anno   = '<span style="color:#4F6FBF;"><b>' . $primo_anno_suggerito . '</b></span> (primo anno con forniture caricate)';
        $banner_extra  = '<br>Data elaborazione precompilata al <b>31/03/' . ($primo_anno_suggerito + 2) . '</b>.';
    } else {
        // Caso 1
        $banner_bg     = '#EEF2FF';
        $banner_border = '#4F6FBF';
        $banner_label  = '<span style="color:#4F6FBF;"><b>' . $ultimo_anno_elaborato . '</b></span>';
        $banner_anno   = '<span style="color:#4F6FBF;"><b>' . $prossimo_anno_elaborabile . '</b></span> (richiesto per la stampa definitiva)';
        $banner_extra  = '<br>Data elaborazione precompilata al <b>31/03/' . ($prossimo_anno_elaborabile + 2) . '</b>.';
    }

    // Stop coattiva: messaggio aggiuntivo (annate 2022/2023) o blocco (>= 2024).
    if ($stop_coattiva_attivo) {
        $banner_extra .= '<br><b style="color:#4F6FBF;">&#9888; Data limite coattiva attiva: '
            . DATA_STOP_COATTIVA . ' &mdash; Informativa Cessione attivata automaticamente per le annualita\' 2022 e 2023.</b>';
    } elseif ($stop_coattiva_bloccato) {
        $banner_bg     = '#FBE9E7';
        $banner_border = '#C62828';
        $banner_extra  = '<br><b style="color:#C62828;">&#9888; Annualita\' ' . $anno_target
            . ' non elaborabile: riscossione coattiva sospesa dal 2024 (limite ' . DATA_STOP_COATTIVA . ').</b>';
    }
    ?>
    <div class="row" style="margin: 1% 5%;">
        <div class="col col-lg-12" style="background-color:<?= $banner_bg ?>; border-left:4px solid <?= $banner_border ?>; padding:8px 12px;">
            <b>Sequenzialita' anno</b><br>
            &bull; Ultima annualita' elaborata: <?= $banner_label ?><br>
            &bull; Anno da elaborare: <?= $banner_anno ?>
            <?= $banner_extra ?>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data elaborazione (presa in considerazione per il calcolo del discarico)</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize <?= $stop_coattiva_attivo ? '' : 'picker'; ?> validateCustom vld_Custom_r" type="text" id="data_elab" name="data_elab" value="<?= $data_elab_form; ?>" <?= $stop_coattiva_attivo ? 'readonly' : 'onchange="setAnnoElab(this,\'anno_elab\');"'; ?> tabindex=5>
                </div>
            </div>
            <?php if ($stop_coattiva_attivo): ?>
                <div class="col-lg-12" style="padding-left:0;">
                    <small style="color:#4F6FBF;">Data fissata al <b><?= DATA_STOP_COATTIVA ?></b> (limite coattiva). Informativa Cessione attivata automaticamente per l'annualita' <?= $anno_target ?>.</small>
                </div>
            <?php endif; ?>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno elaborazione</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input readonly class="form-control resize" type="text" id="anno_elab" name="anno_elab" value="<?= $annoIniz; ?>" tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="si" id="extra_id" name="extra_informativa" <?= $extraFlag; ?>>
                <label class="form-check-label" for="extra_id">
                    Informativa Cessione
                </label>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize " type="button" value="Da debitore" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_sel',1);" tabindex=4>
            </div>
        </div>
        <div class="col col-lg-2" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input readonly class="form-control resize" type="text" id="daco" name="daco" placeholder="Tutti" value="<?= $cls_help->getVar("daco"); ?>" tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input readonly class="form-control resize" type="text" id="dano" name="dano" placeholder="Tutti" value="<?= $cls_help->getVar("dano"); ?>" tabindex=6>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo Entrata</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select name=tipo_partita id=tipo_partita class="form-control resize">
                        <option value="">Tutte</option>
                        <option value="CDS">CDS/AMMINISTRATIVA</option>
                        <option value="IMMOBILI">IMMOBILI</option>
                        <option value="IRPEF">IRPEF</option>
                        <option value="OSAP">OSAP</option>
                        <option value="PATRIMONIALE">PATRIMONIALE</option>
                        <option value="PUBBLICITA">PUBBLICITA'</option>
                        <option value="RIFIUTI">RIFIUTI</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize" type="button" value="A debitore" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_sel',2);" tabindex=7>
            </div>
        </div>
        <div class="col col-lg-2" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input readonly class="form-control resize" type="text" id="acog" name="acog" placeholder="Tutti" value="<?= $cls_help->getVar("acog"); ?>" tabindex=7>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input readonly class="form-control resize" type="text" id="anom" name="anom" placeholder="Tutti" value="<?= $cls_help->getVar("anom"); ?>" tabindex=9>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="da_n_elenco" name="da_n_elenco" tabindex=11 class="form-control resize">
                        <option value="">Tutte</option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Mostra in stampa</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="showPrint_D" name="showPrint[]" value="D" <?= in_array('D', $showPrint) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="showPrint_D"><b>D</b> &mdash; Definitive</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="showPrint_I" name="showPrint[]" value="I" <?= in_array('I', $showPrint) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="showPrint_I"><b>I</b> &mdash; Informative</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="showPrint_P" name="showPrint[]" value="P" <?= in_array('P', $showPrint) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="showPrint_P"><b>P</b> &mdash; Pagate</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">A partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="a_n_elenco" name="a_n_elenco" tabindex=12 class="form-control resize">
                        <option value="">Tutte</option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo stampa</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="printType" name="printType" tabindex=12 class="form-control resize validateCustom vld_Custom_r">
                        <option <?= $cls_help->getVar("printType") == "temp" ? "selected" : "" ?> value="temp">Temporanea</option>
                        <option <?= $cls_help->getVar("printType") == "def" ? "selected" : "" ?> value="def">Definitiva</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbar" style="height:55px;width:100%;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </div>
    </div>

    <div class="row col col-lg-10 col-lg-offset-1 " style="margin-top: 1%;">
        <div id="progressbar" style="height:45px;">
            <div class="text_center" id="barlabel"></div>
        </div>
    </div>
</form>
<?php

if ($cls_help->getVar('iniziaSgravio') == "si") {

    // Guard cintura+bretelle: rifiuta call diretti se l'ente non ha forniture.
    if ($blocca_elaborazione) {
        echo "<script>alert('Nessuna fornitura presente per questo ente. Caricare i ruoli prima di elaborare i discarichi.');</script>";
        die();
    }

    flush();
    ob_flush();
    echo "<script>startBar();</script>";
    flush();
    ob_flush();
    flush();
    ob_flush();
    // $_SESSION['progress'] = "0.00";
    // session_write_close();

    // echo "<script>spinner = new mySpinner('spinner_page','".AJAXWEB."/session_progress.php');spinner.startSpinner();</script>";

    $dataSgravio = new cls_DateTime(date("d/m/Y"), "IT", false);
    $elaboration_date = new cls_DateTime($cls_help->getVar("data_elab"), "IT", false);
    $dataFornitura = new cls_DateTime($elaboration_date->GetDateDB(), "DB", false);
    $dataFornitura->AddYear("-2");
    $annoFornitura = $dataFornitura->getYear();

    // === STOP COATTIVA: override annata/as-of (vedi costante DATA_STOP_COATTIVA) ===
    // Deve stare PRIMA di $data_fornitura_max e del pre-check sequenzialita'.
    if (DATA_STOP_COATTIVA !== null && $anno_target !== null) {
        if ($anno_target >= 2024) {
            $m = "Annualita' " . $anno_target . " non elaborabile: la riscossione coattiva e' sospesa "
               . "dal 2024 (limite ultimo " . DATA_STOP_COATTIVA . ").";
            echo "<script>noResultsBar(" . json_encode($m) . "); alert(" . json_encode($m) . ");</script>";
            die;
        }
        if ($anno_target >= 2022) {              // 2022 o 2023
            $annoFornitura     = $anno_target;                                    // annata autoritativa (NON data-2)
            $elaboration_date  = new cls_DateTime(DATA_STOP_COATTIVA, "IT", false); // as-of congelato allo stop
            $extra_informativa = "si";                                            // Informativa Cessione forzata
        }
    }

    $data_fornitura_max = $annoFornitura."-12-31";
    $data_fornitura_max_IT = "31/12/".$annoFornitura;

    // Stop coattiva: l'annualita' 2023 si ferma al 31/10/2023 -> escludi forniture successive
    // allo stop (in teoria assenti, ma lo gestiamo difensivamente). Per il 2022 e' un no-op.
    if ($stop_coattiva_attivo) {
        $stop_db = $elaboration_date->GetDateDB();   // 2023-10-31
        if ($data_fornitura_max > $stop_db) {        // confronto lessicografico Y-m-d
            $data_fornitura_max    = $stop_db;                          // 2023 -> 2023-10-31
            $data_fornitura_max_IT = $elaboration_date->GetDate("IT");  // 31/10/2023
        }
    }

    // Classificatore I/D/P -- carica soglia mesi inattivita' per ente.
    $row_pg = $cls_db->getResults($cls_db->ExecuteQuery(
        "SELECT Mesi_Inattivita_Sgravio FROM enti_gestiti WHERE CC = '" . $c . "' LIMIT 1"
    ));
    $mesi_inattivita_soglia = !empty($row_pg) ? (int)$row_pg[0]['Mesi_Inattivita_Sgravio'] : 12;
    $classificatore = new Cls_Classificatore_Sgravi($c, $mesi_inattivita_soglia);
    $data_elab_db = $elaboration_date->GetDateDB();

    $printType = $cls_help->getVar("printType");

    // Pre-check sequenzialita' anno (solo per stampa definitiva).
    // L'elaborazione di un anno richiede che sia gia' stata fatta quella
    // dell'anno precedente. La cancellazione (Fase 6) e' l'unico modo per
    // tornare indietro: scende il MAX e gli anni successivi tornano possibili.
    if ($printType == "def") {
        $rs_max = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT MAX(Anno_Procedura) AS max_anno FROM procedures
              WHERE CC = '" . $c . "' AND Procedure_Type_Id = 2 AND Anno_Procedura IS NOT NULL"
        ));
        $max_anno_elab = (!empty($rs_max) && $rs_max[0]['max_anno'] !== null)
            ? (int)$rs_max[0]['max_anno'] : null;
        $anno_richiesto = (int)$annoFornitura;

        if ($max_anno_elab !== null) {
            $atteso = $max_anno_elab + 1;
            if ($anno_richiesto > $atteso) {
                $msg = sprintf(
                    "Sequenzialita' anno violata. Ultimo anno elaborato: %d. Prossimo da elaborare: %d. L'anno richiesto (%d) richiede prima l'elaborazione dell'anno %d.",
                    $max_anno_elab, $atteso, $anno_richiesto, $atteso
                );
                echo "<script>$('#barlabel').text(" . json_encode($msg) . "); alert(" . json_encode($msg) . ");</script>";
                die;
            }
            if ($anno_richiesto < $atteso) {
                $msg = sprintf(
                    "Anno %d gia' coperto da elaborazione successiva (max anno: %d). Cancellare le elaborazioni piu' recenti per rielaborare anni precedenti.",
                    $anno_richiesto, $max_anno_elab
                );
                echo "<script>$('#barlabel').text(" . json_encode($msg) . "); alert(" . json_encode($msg) . ");</script>";
                die;
            }
            // anno_richiesto == atteso: caso atteso, OK. Anche anno_richiesto
            // == max_anno_elab ricadrebbe nel < atteso e bloccherebbe;
            // l'UNIQUE su (CC, Procedure_Type_Id, Anno_Procedura) blocca
            // comunque a livello DB l'inserimento di un anno gia' presente.
        }
        // Se max_anno_elab e' NULL (mai elaborato), qualunque anno e' valido.
    }

    $filter = array();

    $filter["genere_da"] = $genere_da = $cls_help->getVar('genere_da');
    $filter["genere_a"] = $genere_a = $cls_help->getVar('genere_a');
    $filter["daco"] = $dacognome = $cls_help->getVar('daco');
    $filter["acog"] = $acognome = $cls_help->getVar('acog');
    $filter["dano"] = $danome = $cls_help->getVar('dano');
    $filter["anom"] = $anome = $cls_help->getVar('anom');
    $filter["da_partita"] = $daNEl = $cls_help->getVar("da_n_elenco");
    $filter["a_partita"] = $aNEl = $cls_help->getVar("a_n_elenco");
    $filter["tipo_partita"] = $cls_help->getVar("tipo_partita");
    $filter["da_data_notifica"] = $cls_help->getVar("da_data");
    $filter["a_data_notifica"] = $cls_help->getVar("a_data");
    $filter["data_elaborazione"] = $elaboration_date->GetDate("IT");
    

    $filtriDescrizione = "Elaborazione discarico con filtri: ";
    $filtriDescrizione .= "Data fornitura fino al " . $data_fornitura_max_IT;


    $query = "SELECT P.* ,
    A.ID AS ID_ATTO,
    PG.ID AS ID_PIGNO,
    A.Tipo_Ufficiale AS Tipo_Ufficiale,
    A.PrinterId AS PrinterId,
    A.PrintTypeId AS PrintTypeId,
    A.DocumentTypeId,
    PG.DocumentTypeId AS DocumentTypeIdPigno,
    A.Data_Notifica AS Data_Notifica_Atto ,
    UAN.Data_Notifica AS Last_Data_Notifica_Atto ,
    A.ID_Cronologico AS ID_CRONOLOGICO,
    A.Anno_Cronologico AS ANNO_CRONOLOGICO,
    A.Atto AS TIPO_ATTO,
    A.Spese_Notifica AS  Spese_Notifica,
    A.Interessi_Precedenti AS Interessi_Precedenti,
    A.Rielabora_Flag AS  Rielabora_Flag,
    A.Rettifica_Flag AS  Rettifica_Flag,
    A.Data_Richiesta_Rate AS Data_Richiesta_Rate_Atto,

    A.Data_Elaborazione AS Data_Elaborazione_ATTO,
    A.Data_Flusso AS Data_Flusso_ATTO,
    A.Motivo_Notifica AS Motivo_Notifica_ATTO,
    PN.Descrizione AS Anomalia_ATTO,
    A.Stato_Notifica AS Stato_Notifica_ATTO,
    A.Indirizzo_Validato AS Indirizzo_Validato_ATTO,
    NI.Immagine_Fronte AS Notifica_Fronte_ATTO,
    NI.Immagine_Retro AS Notifica_Retro_ATTO,
    NI.CAD_Fronte AS CAD_Fronte_ATTO,
    NI.CAD_Retro AS CAD_Retro_ATTO,
    A.Modalita_Notifica AS Modalita_Notifica_ATTO,
    A.Data_Stampa AS Data_Stampa_ATTO,
    A.Data_Decorrenza_Interessi AS Data_Decorrenza_Interessi_ATTO,
    A.Data_Calcolo_Interessi AS Data_Calcolo_Interessi_ATTO ,
    IF((A.Data_Notifica + INTERVAL 60 DAY) > IF( PA.Data_Pagamento IS NULL , '" . $elaboration_date->GetDateDB() . "', PA.Data_Pagamento), (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO_STRA,
    IF((A.Data_Notifica + INTERVAL 60 DAY) > IF( PA.Data_Pagamento IS NULL , '" . $elaboration_date->GetDateDB() . "', PA.Data_Pagamento) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO,
    A.Scadenze_Rate AS Scadenze_Rate_Atto,
    A.Importi_Rate AS Importi_Rate_Atto,
    A.Rate_Previste AS Rate_Previste_Atto,
    PG.Scadenze_Rate AS Scadenze_Rate_Pigno,
    PG.Importi_Rate AS Importi_Rate_Pigno,
    PG.Rate_Previste AS Rate_Previste_Pigno,
    A.Totale_Rateizzato AS Totale_Rateizzato_Atto,
    A.Esito_Richiesta_Rateizzazione,
    A.Interessi_Precedenti AS Interessi_Precedenti_ATTO,
    A.Interessi AS Interessi_ATTO,
    COALESCE(A.Diritto_Riscossione_Massimo,0) AS Diritto_Riscossione_Massimo_ATTO,
    COALESCE(A.Diritto_Riscossione_Minimo,0) AS Diritto_Riscossione_Minimo_ATTO,
    A.Spese_Notifica_Precedenti AS Spese_Notifica_Precedenti_ATTO,
    A.Spese_Notifica AS Spese_Notifica_ATTO,
    A.CAN AS CAN_ATTO,
    A.CAD AS CAD_ATTO,
    PG.DocumentTypeId as DocumentTypeId_PG,
    PG.ID_Cronologico AS ID_CRONOLOGICO_PG,
    PG.Anno_Cronologico AS ANNO_CRONOLOGICO_PG,
    PG.Data_Stampa AS Data_Stampa_PG,
    PG.Stato_Pignoramento,
    PG.Data_Stato_Pignoramento,
    COALESCE(PG.Totale_Dovuto,0) AS Totale_Dovuto_PG,
    COALESCE(A.Totale_Dovuto,0) AS Totale_Dovuto_Atto_Iniziale,
    DPG.Description AS PIGNORAMENTO,
    NPG.Data_Notifica AS DATA_NOTIFICA_PG,
    PG.Data_Elaborazione AS Data_Elaborazione_Pignoramento,
    SUM(PA.Importo) AS TOTALE_PAGAMENTI,
    PG.Data_Richiesta_Rate AS Data_Richiesta_Rate_Pigno,

    '" . $elaboration_date->GetDateDB() . "' AS Data_Sgravio,

    PS.Name AS PS_NOME, PS.Description AS PS_DESCRIZIONE,
    AP.ID AS APPEAL_ID, AP.End_Date as APPEAL_End_Date,
    CT.ID AS CRISIS_ID, CT.End_Date as CRISIS_End_Date
    FROM v_partita AS P
        LEFT JOIN atto AS A ON A.ID=(SELECT A2.ID FROM atto as A2 WHERE A2.Partita_ID = P.Partita_ID AND A2.archived IS NULL AND ( A2.data_start_archived_act > '" . $elaboration_date->GetDateDB() . "' OR A2.data_start_archived_act IS NULL ) ORDER BY A2.Data_Elaborazione DESC LIMIT 1)
        LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
        LEFT JOIN atto as UAN ON UAN.ID=(SELECT A1.ID FROM atto AS A1 WHERE A1.Partita_ID=P.Partita_ID AND A1.Data_Notifica is not null AND A1.archived IS NULL AND ( A1.data_start_archived_act > '" . $elaboration_date->GetDateDB() . "' OR A1.data_start_archived_act IS NULL ) ORDER BY A1.Data_Notifica DESC LIMIT 1)
        LEFT JOIN notifiche_importate AS NI on A.ID = NI.DocumentId AND A.DocumentTypeId=NI.DocumentTypeId
        LEFT JOIN pignoramento_generale AS PG ON PG.ID=(SELECT PG1.ID FROM pignoramento_generale AS PG1 WHERE A.ID = PG1.Atto_ID AND PG1.Data_Elaborazione >= A.Data_Elaborazione ORDER BY PG1.Data_Elaborazione DESC LIMIT 1)  
        LEFT JOIN document_type AS DPG ON PG.DocumentTypeId = DPG.Id
        LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'
        LEFT JOIN pagamento AS PA on P.Partita_ID = PA.Partita_ID AND PA.DocumentTypeId is not null

        LEFT JOIN position_status AS PS on PS.Id = P.Position_Status_Id
        LEFT JOIN appeal AS AP on AP.Partita_ID = P.Partita_ID
        LEFT JOIN crisis_tools AS CT on CT.Partita_ID = P.Partita_ID
    WHERE P.Is_Discharged = 0 AND P.CC = '" . $c . "' 
    AND P.Data_Fornitura <= '" . $data_fornitura_max . "' ";

    if ($dacognome != null) {
        $strCompareDa = addslashes($dacognome) . " " . addslashes($danome);
        $strCompareA = addslashes($acognome) . " " . addslashes($anome);

        $query .= " AND ( CONCAT(COALESCE(P.Ditta,''),COALESCE(P.Cognome,''),' ',COALESCE(P.Nome,'')) >= '" . $strCompareDa . "' AND CONCAT(COALESCE(P.Ditta,''),COALESCE(P.Cognome,''),' ',COALESCE(P.Nome,'')) <= '" . $strCompareA . "' ) ";

        $storico_msg .= " dal contribuente " . $dacognome . " " . $danome . " al contribuente " . $acognome . " " . $anome;
    }

    if ($cls_help->getVar("tipo_partita") != null) {
        $query .= " AND Tipo_Riscossione = '" . $cls_help->getVar("tipo_partita") . "' ";

        $filtriDescrizione .= " - Tipo partita: " . $cls_help->getVar("tipo_partita");

        $storico_msg = " per le entrate di tipo " . $cls_help->getVar("tipo_partita");
    }
    if ($daNEl != null) {
        $query .= " AND P.Comune_ID >= " . $daNEl;

        $filtriDescrizione .= " - Da partita " . $daNEl;

        $storico_msg .= " dalla partita " . $daNEl;
    }
    if ($aNEl != null) {
        $query .= " AND P.Comune_ID <= " . $aNEl;

        $filtriDescrizione .= " a partita " . $aNEl;

        $storico_msg .= " alla partita " . $aNEl;
    }
    if ($cls_help->getVar("da_data") != null) {
        $query .= " AND Data_Notifica_Atto >= '" . $cls_date->GetDateDB($cls_help->getVar("da_data"), "IT") . "' OR Data_Notifica_Atto IS NULL ";

        $filtriDescrizione .= " - Da data notifica " . $cls_help->getVar("da_data");

        $storico_msg .= " dalla data " . $cls_date->GetDateDB($cls_help->getVar("da_data"), "IT");
    }
    if ($cls_help->getVar("a_data") != null) {
        $query .= " AND Data_Notifica_Atto <= '" . $cls_date->GetDateDB($cls_help->getVar("a_data"), "IT") . "' OR Data_Notifica_Atto IS NULL";

        $filtriDescrizione .= " a data notifica " . $cls_help->getVar("a_data");

        $storico_msg .= " alla data " . $cls_date->GetDateDB($cls_help->getVar("a_data"), "IT");
    }
    // $queryPerimetro = base + filtri utente, SENZA filtro classificazione Tipo_Sgravio.
    // Serve per distinguere "nessuna partita nel perimetro" (Mig.3) da "tutte già D/P" (Mig.4).
    $queryPerimetro = $query . " GROUP BY Partita_ID ";

    // La query principale elabora solo le partite non ancora classificate (o informative).
    $query .= "AND (P.Tipo_Sgravio IS NULL OR P.Tipo_Sgravio='I') ";
    $query .= " GROUP BY Partita_ID ORDER BY Comune_ID ";


    //echo $query;

    $result = $cls_db->getResults($cls_db->ExecuteQuery($query));

    // --- Gestione zero risultati (Migliorie 3 e 4) ---
    if (count($result) == 0) {
        $rsPerim = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT COUNT(*) AS n FROM (" . $queryPerimetro . ") tprobe"
        ));
        $countPerimetro = (!empty($rsPerim) && isset($rsPerim[0]['n'])) ? (int)$rsPerim[0]['n'] : 0;

        if ($countPerimetro == 0) {
            // MIGLIORIA 3: nessuna partita nel perimetro.
            // Con filtri attivi lo 0 puo' dipendere dai filtri: NON e' indicatore di
            // annualita' vuota a prescindere -> non registriamo nulla.
            $filtri_attivi = ($dacognome != null || $acognome != null || $danome != null || $anome != null
                || $daNEl != null || $aNEl != null || $cls_help->getVar("tipo_partita") != null
                || $cls_help->getVar("da_data") != null || $cls_help->getVar("a_data") != null);

            if ($filtri_attivi) {
                // Comportamento storico: lo 0 con filtri non certifica nulla.
                $mEmpty = "Nessuna partita trovata per i filtri selezionati.";
            } elseif ($printType == "def") {
                // Annualita' vuota su run FULL-ENTE: registra la procedura per far avanzare
                // la sequenzialita' anno, cosi' l'operatore puo' procedere con l'anno
                // successivo (dove magari sono stati importati dati nuovi). Nessun file generato.
                $a_dbParams = array(
                    'table' => 'procedures',
                    'fields' => array(
                        array('name' => 'Procedure_Type_Id', 'type' => 'int',    'value' => 2),
                        array('name' => 'Datetime',          'type' => 'date',   'value' => date('Y-m-d H:i:s')),
                        array('name' => 'Procedure_Date',    'type' => 'date',   'value' => $data_elab_db),
                        array('name' => 'CC',                'type' => 'string', 'value' => $c),
                        array('name' => 'User_Id',           'type' => 'int',    'value' => $_SESSION['aut_progr']),
                        array('name' => 'Description',       'type' => 'string', 'value' => $filtriDescrizione . " - Annualita' vuota: nessuna partita nel perimetro"),
                        array('name' => 'Anno_Procedura',    'type' => 'int',    'value' => (int)$annoFornitura),
                    )
                );
                $cls_db->DbSave($a_dbParams);
                $mEmpty = "Annualita' " . (int)$annoFornitura . " vuota: nessuna partita da elaborare. "
                        . "Elaborazione registrata: puoi procedere con l'anno successivo.";
            } else {
                // Temp full-ente: solo messaggio dedicato, nessuna registrazione.
                $mEmpty = "Annualita' " . (int)$annoFornitura . " vuota: nessuna partita. "
                        . "Usa la definitiva (senza filtri) per registrare e avanzare la sequenza.";
            }
            echo "<script>noResultsBar(" . json_encode($mEmpty) . "); alert(" . json_encode($mEmpty) . ");</script>";
            die;
        }

        // MIGLIORIA 4: partite nel perimetro ma tutte già classificate D o P.
        if ($printType == "def") {
            // Registrare l'elaborazione: certifica che per quell'anno non ci sono nuove
            // posizioni da comunicare all'ente. Anno_Procedura valorizzato; la nota va in
            // Description (colonna Tipo_Elaborazione è stata droppata, migrazione 2026-05-06).
            $a_dbParams = array(
                'table' => 'procedures',
                'fields' => array(
                    array('name' => 'Procedure_Type_Id', 'type' => 'int',    'value' => 2),
                    array('name' => 'Datetime',          'type' => 'date',   'value' => date('Y-m-d H:i:s')),
                    array('name' => 'Procedure_Date',    'type' => 'date',   'value' => $cls_date->GetDateDB($cls_help->getVar("data_elab"), "IT")),
                    array('name' => 'CC',                'type' => 'string', 'value' => $c),
                    array('name' => 'User_Id',           'type' => 'int',    'value' => $_SESSION['aut_progr']),
                    array('name' => 'Description',       'type' => 'string', 'value' => $filtriDescrizione . " - Nessuna partita da elaborare - tutte le posizioni gia' classificate"),
                    array('name' => 'Anno_Procedura',    'type' => 'int',    'value' => (int)$annoFornitura),
                )
            );
            $cls_db->DbSave($a_dbParams);
            $m4 = "Elaborazione registrata per l'anno " . (int)$annoFornitura . ". Nessuna partita da elaborare: tutte le posizioni eleggibili sono gia' state classificate come Definitive o Pagate.";
        } else {
            $m4 = "Nessuna partita da elaborare: tutte le posizioni eleggibili sono gia' state classificate come Definitive o Pagate.";
        }
        echo "<script>noResultsBar(" . json_encode($m4) . "); alert(" . json_encode($m4) . ");</script>";
        die;
    }
    // --- Fine gestione zero risultati ---

    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("");

    $a_headerPage[0] = array("Partita ID / Obbligato", "Data Fornitura", "Info", "Codice Tributo", "Presa in carico.", "Dovuto", "Pagamento", "Residuo", "Tipo / Info");

    $pdf->setArray($a_headerPage, "a_headerPage");
    $percent = 100 / 11 * ($pdf->getPageWidth() - 20) / 100;
    $a_width = array($percent, $percent, $percent * 2, $percent * 2, $percent, $percent, $percent, $percent, $percent);
    $a_align = array("R", "L", "L", "L", "R", "R", "L", "L", "L");
    $pdf->setArray($a_width, "a_width");
    $pdf->setArray($a_align, "a_align");
    $pdf->setHeaderPage();
    $pdf->addLines();

    if ($printType == "temp")
        $pdf->temporaryPrinting();

    $dataExcel[] = array("<b>Partita ID</b>", "<b>Data Fornitura</b>", "<b>Obbligato</b>", "<b>Info</b>", "<b>Codice Tributo</b>", "<b>Totale Preso in carico</b>",  "<b>Totale dovuto</b>", "<b>Pagamento</b>", "<b>Totale residuo</b>", "<b>Tipo / Info</b>", "<b>Motivazione</b>");

    // Mappa indice riga $dataExcel -> Partita_ID, per popolare la colonna
    // Motivazione (concatenazione di sgravi_documenti.Text) dopo il loop (solo def):
    // a quel punto i record sgravi_documenti sono stati salvati da
    // BuildMotivationText e una sola query GROUP_CONCAT li recupera tutti.
    $indici_motivazioni = array();

    // In modalità temp: mappa DocumentTypeId -> Description per assemblare
    // la colonna Motivazione al volo (BuildMotivationText con flagSave=false).
    $dtMap = array();
    if ($printType == "temp") {
        $rs_dt = $cls_db->getResults($cls_db->ExecuteQuery("SELECT Id, Description FROM document_type"));
        foreach ($rs_dt as $r_dt) $dtMap[(int)$r_dt['Id']] = $r_dt['Description'];
    }

    $totale_per_pagina_presa_in_carico = 0;
    $totale_per_pagina_residuo = 0;
    $totale_per_pagina_pagamenti = 0;
    $totale_per_pagina_dovuto = 0;
    $totale_presa_in_carico = 0;
    $totale_residuo = 0;
    $totale_pagamenti = 0;
    $totale_dovuto = 0;
    $countAllResult = count($result);
    $contSgravi = 0;
    $countPos = 0;

    for ($i = 0; $i < $countAllResult; $i++) {

        flush();
        ob_flush();
        flush();
        ob_flush();
        echo "<script>updateBar(" . ceil($i * 100 / $countAllResult) . ");</script>";
        flush();
        ob_flush();
        flush();
        ob_flush();

        $a_tributi = $cls_elaboration->getTributi($result[$i]["Partita_ID"]);
        // var_dump($a_tributi['total']);
        // echo "<br><br>";
        if ($a_tributi['total'] <= 0)
            continue;

        $a_amounts = $cls_elaboration->getAmounts($result[$i], $a_tributi['total']);

        $countPos++;

        // Classificazione I/D/P delegata a Cls_Classificatore_Sgravi.
        // I filtri as-of $data_elab_db (ricorsi/crisi/udienze/pagamenti/notifiche/rate)
        // e la cascata I->D Lv1/Lv2 sono incapsulate nella classe.
        $partita_row = array_merge($result[$i], array(
            'residual' => (float)$a_amounts['residual'],
            'payments' => (float)$a_amounts['payments'],
        ));
        $classificazione = $classificatore->classificaPartita(
            $partita_row,
            $data_elab_db,
            (float)$a_params['Parametri_Annuali']['Importo_Minimo']
        );
        $tipo_sgravio = $classificazione['tipo'];
        $info_sgravio = $classificazione['info'];

        $data_Fornitura = new cls_DateTime($result[$i]['Data_Fornitura'], "DB", false);



        $obbligato = $result[$i]["Cognome_Ditta"]." ".$result[$i]["Nome"];
        if(!empty($info_sgravio))
            $info_sgravio = " / ".$info_sgravio;
        if(in_array($tipo_sgravio, $showPrint, true)){
            $totale_per_pagina_presa_in_carico += $a_tributi['total'];
            $totale_presa_in_carico += $a_tributi['total'];
            $totale_per_pagina_residuo += $a_amounts["residual"];
            $totale_residuo += $a_amounts["residual"];
            $totale_per_pagina_dovuto += $a_amounts["total"];
            $totale_dovuto += $a_amounts["total"];
            $totale_pagamenti += $a_amounts["payments"];
            $totale_per_pagina_pagamenti += $a_amounts["payments"];

            $a_value[0] = array(
                $result[$i]["Comune_ID"]."\n".$obbligato,
                $data_Fornitura->GetDate("IT"),
                $result[$i]["Info_Cartella"],
                $a_tributi['description'],
                number_format($a_tributi['total'], 2, ",", "."),
                number_format($a_amounts['total'], 2, ",", "."),
                number_format($a_amounts['payments'], 2, ",", "."),
                number_format($a_amounts['residual'], 2, ",", "."),
                $tipo_sgravio.$info_sgravio
            );

            $a_total[0] = array(
                "",
                "",
                "",
                "",
                number_format($totale_per_pagina_presa_in_carico, 2, ",", "."),
                number_format($totale_per_pagina_dovuto, 2, ",", "."),
                number_format($totale_per_pagina_pagamenti, 2, ",", "."),
                number_format($totale_per_pagina_residuo, 2, ",", "."),
                ""
            );
            $dataExcel[] = array(
                $result[$i]["Comune_ID"],
                $data_Fornitura->GetDate("IT"),
                $obbligato,
                $result[$i]["Info_Cartella"],
                $a_tributi['description'],
                number_format($a_tributi['total'], 2, ",", "."),
                number_format($a_amounts['total'], 2, ",", "."),
                number_format($a_amounts['payments'], 2, ",", "."),
                number_format($a_amounts['residual'], 2, ",", "."),
                $tipo_sgravio.$info_sgravio,
                ""  // Motivazione: popolata dopo il loop (def) o inline qui sotto (temp)
            );
            $indici_motivazioni[count($dataExcel) - 1] = (int)$result[$i]["Partita_ID"];

            // Modalità provvisoria: calcola motivazione al volo con BuildMotivationText
            // (flagSave=false) senza toccare sgravio né sgravi_documenti.
            if ($printType == "temp" && $tipo_sgravio != "P") {
                $queryAttiT = "SELECT A.ID, PT.Tipo, A.Info_Cartella, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Notifica ,
                        P.Descrizione as DescrizioneModalitaNotifica , A.DocumentTypeId, A.CC,
                        SUM(PAG.Importo) as TotalePagamenti
                        FROM `atto`as A
                        JOIN partita_tributi as PT on PT.ID = A.Partita_ID
                        LEFT JOIN pagamento as PAG on PAG.Atto_ID = A.ID AND PAG.Partita_ID = A.Partita_ID
                        LEFT JOIN parametri_notifica as P on A.Modalita_Notifica = P.ID
                        WHERE A.Partita_ID = " . $result[$i]['Partita_ID'] . " and A.CC = '" . $c . "'
                        GROUP BY A.ID";
                $queryPignoT = "SELECT P.ID, P.Anno_Cronologico, P.ID_Cronologico, N.Data_Notifica, PANO.Descrizione AS DescrizioneMotivoNotifica,
                        PAN.Descrizione AS DescrizioneStatoNotifica, PA.Descrizione AS DescrizioneModalitaNotifica, P.DocumentTypeId,
                        SUM(PAG.Importo) as TotalePagamenti
                        FROM pignoramento_generale as P
                        LEFT JOIN pagamento as PAG on PAG.Atto_ID = P.ID AND PAG.Partita_ID = P.Partita_ID
                        LEFT JOIN notifica_atto as N on N.Atto_Notificato_ID = P.ID AND N.Tipo_Notifica='debitore'
                        LEFT Join parametri_notifica as PA on N.Modalita_Notifica = PA.ID
                        LEFT Join parametri_notifica as PAN on N.Stato_Notifica = PAN.ID
                        LEFT Join parametri_notifica as PANO on N.Motivo_Notifica = PANO.ID
                        where P.CC = '" . $c . "' AND P.Partita_ID = " . $result[$i]['Partita_ID'] . "
                        GROUP BY P.ID";
                $attiT  = $cls_db->getResults($cls_db->ExecuteQuery($queryAttiT));
                $pignoT = $cls_db->getResults($cls_db->ExecuteQuery($queryPignoT));
                $btTmp = new BuildMotivationText($result[$i]["Partita_ID"], false, false, null, false);
                $btTmp->SetPigno($pignoT);
                $btTmp->SetAtto($attiT);
                $parts = array();
                foreach ($btTmp->GetMotivazioniArray() as $m) {
                    $desc = isset($dtMap[(int)$m['DocumentTypeId']]) ? $dtMap[(int)$m['DocumentTypeId']] : '(tipo sconosciuto)';
                    $parts[] = $desc . ': ' . $m['Text'];
                }
                $dataExcel[count($dataExcel) - 1][10] = implode(' | ', $parts);
            }
        }

        $contSgravi++;

        if ($printType == "def") {

            if ($contSgravi == 1) {
                $a_dbParams = array(
                    'table' => 'procedures',
                    'fields' => array(
                        array('name' => 'Procedure_Type_Id',      'type' => 'int',        'value' => 2),
                        array('name' => 'Datetime',               'type' => 'date',       'value' => date('Y-m-d H:i:s')),
                        array('name' => 'Procedure_Date',         'type' => 'date',       'value' => $cls_date->GetDateDB($cls_help->getVar("data_elab"), "IT")),
                        array('name' => 'CC',                     'type' => 'string',     'value' => $c),
                        array('name' => 'User_Id',                'type' => 'int',        'value' => $_SESSION['aut_progr']),
                        array('name' => 'Description',            'type' => 'string',     'value' => $filtriDescrizione),
                        array('name' => 'Anno_Procedura',         'type' => 'int',        'value' => (int)$annoFornitura),
                    )
                );
                $procedure_id = $cls_db->DbSave($a_dbParams);
            }

            if ($tipo_sgravio != "P") {

                // Vincolo applicativo (UNIQUE Partita_ID+Tipo rimandato al porting):
                // se esiste gia' un sgravio Tipo=1 per la partita -> UPDATE,
                // altrimenti INSERT. Le motivazioni vengono rigenerate.
                $partita_id_int = (int)$result[$i]["Partita_ID"];
                $existing = $cls_db->getResults($cls_db->ExecuteQuery(
                    "SELECT ID FROM sgravio WHERE Partita_ID = " . $partita_id_int . " AND Tipo = 1 LIMIT 1"
                ));

                if (!empty($existing)) {
                    $sgravio_id = (int)$existing[0]['ID'];
                    $cls_db->ExecuteQuery(
                        "UPDATE sgravio SET
                            Procedure_Id = " . (int)$procedure_id . ",
                            Tipo_Sgravio = '" . addslashes($tipo_sgravio) . "',
                            Info = '" . addslashes($info_sgravio) . "'
                          WHERE ID = " . $sgravio_id
                    );
                    $cls_db->ExecuteQuery(
                        "DELETE FROM sgravi_documenti WHERE Sgravio_ID = " . $sgravio_id
                    );
                } else {
                    $a_dbParams = array(
                        'table' => 'sgravio',
                        'fields' => array(
                            array('name' => 'Procedure_Id',           'type' => 'int',        'value' => $procedure_id),
                            array('name' => 'Partita_ID',             'type' => 'int',        'value' => $partita_id_int),
                            array('name' => 'CC',                     'type' => 'string',     'value' => $c),
                            array('name' => 'Tipo',                   'type' => 'int',        'value' => 1),
                            array('name' => 'Tipo_Sgravio',           'type' => 'string',     'value' => $tipo_sgravio),
                            array('name' => 'Info',                   'type' => 'string',     'value' => $info_sgravio),
                        )
                    );
                    $sgravio_id = $cls_db->DbSave($a_dbParams);
                }

                $buildText = new BuildMotivationText($result[$i]["Partita_ID"], false, true, $sgravio_id);

                $queryAtti = "SELECT A.ID, PT.Tipo, A.Info_Cartella, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Notifica , 
                        P.Descrizione as DescrizioneModalitaNotifica , A.DocumentTypeId, A.CC,
                        SUM(PAG.Importo) as TotalePagamenti
                        FROM `atto`as A
                        JOIN partita_tributi as PT on PT.ID = A.Partita_ID
                        LEFT JOIN pagamento as PAG on PAG.Atto_ID = A.ID AND PAG.Partita_ID = A.Partita_ID    
                        LEFT JOIN parametri_notifica as P on A.Modalita_Notifica = P.ID
                        WHERE A.Partita_ID = " . $result[$i]['Partita_ID'] . " and A.CC = '" . $c . "'
                        GROUP BY A.ID";
                $atti = $cls_db->getResults($cls_db->ExecuteQuery($queryAtti));

                $queryPigno = "SELECT P.ID, P.Anno_Cronologico, P.ID_Cronologico, N.Data_Notifica, PANO.Descrizione AS DescrizioneMotivoNotifica, 
                        PAN.Descrizione AS DescrizioneStatoNotifica, PA.Descrizione AS DescrizioneModalitaNotifica, P.DocumentTypeId, 
                        SUM(PAG.Importo) as TotalePagamenti
                        FROM pignoramento_generale as P
                        LEFT JOIN pagamento as PAG on PAG.Atto_ID = P.ID AND PAG.Partita_ID = P.Partita_ID
                        LEFT JOIN notifica_atto as N on N.Atto_Notificato_ID = P.ID AND N.Tipo_Notifica='debitore'
                        LEFT Join parametri_notifica as PA on N.Modalita_Notifica = PA.ID
                        LEFT Join parametri_notifica as PAN on N.Stato_Notifica = PAN.ID
                        LEFT Join parametri_notifica as PANO on N.Motivo_Notifica = PANO.ID
                        where P.CC = '" . $c . "' AND P.Partita_ID = " . $result[$i]['Partita_ID'] . "
                        GROUP BY P.ID";
                $pigno = $cls_db->getResults($cls_db->ExecuteQuery($queryPigno));

                $buildText->IsDebug(true);
                //if(count($pigno) > 0)
                $buildText->SetPigno($pigno);
                //if(count($atti) > 0)
                $buildText->SetAtto($atti);


                $buildText->SaveAllOnDB();
            }


            $save = array();
            $save["Tipo_Sgravio"] = $tipo_sgravio;
            $save["Flag_Sgravio"] = "si";
            $save["Sgravio_Activation_Date"] = $cls_date->GetDateDB($cls_help->getVar("data_elab"), "IT"); // date("Y-m-d");
            $save["Sgravio_Save_Activation_Date"] = date("Y-m-d");


            $arrWhere = array("ID" => $result[$i]["Partita_ID"]);

            $a_paramsSgraviDoc = $cls_Utils->GetObjectQuery($save, "partita_tributi", $arrWhere);
            if (!$cls_db->DbSave($a_paramsSgraviDoc)) {

                $error = 1;
                $msg = "Errore impossibile aggiornare i dati. " . $cls_db->GetError();
                $cls_db->Rollback();
                header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
                die;
            } else $msg = "Dati aggiornati correttamente";
        }


        $force = false;
        if ($i == $countAllResult - 1)
            $force = true;

        if(in_array($tipo_sgravio, $showPrint, true)){
            $flag = $pdf->setRowPageTotal($a_value, 8, 10, 50, $a_total, $force, $printType);
            if (!$force)
                $pdf->addLines("dash");
            if ($flag) {
                $totale_per_pagina_dovuto = 0;
                $totale_per_pagina_residuo = 0;
                $totale_per_pagina_presa_in_carico = 0;
                $totale_per_pagina_pagamenti = 0;
            }
        }

    }

    if ($contSgravi == 0) {

        echo "<script>noResultsBar();</script>";
        die;
    }

    if ($printType == "def") {
        $path = $cls_Utils->crea_dir(PROCEDURE . $procedure_id);
        $FinalFileName = "Elaborazione_Sgravi_" . $procedure_id . "_" . $elaboration_date->GetDateDB();
    } else {
        $path = $cls_Utils->crea_dir(ARCHIVIO . "/temp");
        $FinalFileName = "Elaborazione_Sgravi_temp_" . $elaboration_date->GetDateDB();
    }

    $a_mainPageParams = array("title" => strtoupper($a_enteAdmin['Denominazione']), "subtitle" => "ESITI ELABORAZIONE DISCARICHI AUTOMATICI");
    $pdf->setMainPageParams($a_mainPageParams);

    $a_filters = $cls_elab->getFiltersDescription($filter);

    // Ordine: Data elaborazione (indice 0 da getFiltersDescription), poi Anno, Note, resto dei filtri, Legenda
    // Inserisce ANNO ELABORAZIONE e NOTE ANNO dopo la DATA ELABORAZIONE (posizione 1)
    array_splice($a_filters, 1, 0, array(
        array(
            "label" => "ANNO ELABORAZIONE",
            "value" => $annoFornitura
        ),
        array(
            "label" => "NOTE ANNO ELAB.",
            "value" => "Include annualita' precedenti non ancora scaricate (ricorso aperto, crisi aperta e/o altro)"
        )
    ));

    // Legenda colonna Tipo/Info su riga singola — sempre in fondo
    $last_i = count($a_filters);
    $a_filters[$last_i]["label"] = "LEGENDA";
    $a_filters[$last_i]["value"] = "D = Discarico definitivo  |  I = Discarico informativo  |  P = Posizione pagata (residuo inf. importo minimo)";
    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO ATTI";
    $recap[1]['value'] = $countPos;
    $recap[2]['label'] = "TOTALE PRESO IN CARICO";
    $recap[2]['value'] = number_format($totale_presa_in_carico, 2, ",", ".");
    $recap[3]['label'] = "TOTALE DOVUTO";
    $recap[3]['value'] = number_format($totale_dovuto, 2, ",", ".");
    $recap[4]['label'] = "TOTALE PAGAMENTI";
    $recap[4]['value'] = number_format($totale_pagamenti, 2, ",", ".");
    $recap[5]['label'] = "TOTALE RESIDUO";
    $recap[5]['value'] = number_format($totale_residuo, 2, ",", ".");

    if ($extra_informativa == "si"){
        $recap[6]['label'] = "INFORMATIVA";
        $recap[6]['value'] = "CESSIONE DEL RAMO D'AZIENZA";
    }

    $pdf->setMainPage($a_filters, $recap, null, $printType);

    $pdf->Output($path . "/" . $FinalFileName . ".pdf", 'F');

    // Popola la colonna Motivazione nelle righe Excel (post-loop, una query, solo def):
    // in temp la colonna è già stata compilata al volo durante il loop.
    // JOIN su sgravio.ID = sgravi_documenti.Sgravio_ID (path piu' diretto via PK).
    if ($printType == "def" && !empty($indici_motivazioni)) {
        $partita_ids_csv = implode(',', array_unique(array_map('intval', $indici_motivazioni)));
        $rs_mot = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT S.Partita_ID,
                    GROUP_CONCAT(CONCAT(COALESCE(DT.Description, '(tipo sconosciuto)'), ': ', SD.Text) ORDER BY SD.ID SEPARATOR ' | ') AS motivazione
               FROM sgravio S
               LEFT JOIN sgravi_documenti SD ON SD.Sgravio_ID = S.ID
               LEFT JOIN document_type DT ON DT.Id = SD.DocumentTypeId
              WHERE S.Tipo = 1 AND S.CC = '" . $c . "' AND S.Partita_ID IN (" . $partita_ids_csv . ")
              GROUP BY S.Partita_ID"
        ));
        $motivazioni_per_partita = array();
        foreach ($rs_mot as $r_mot) {
            $motivazioni_per_partita[(int)$r_mot['Partita_ID']] = $r_mot['motivazione'] !== null ? $r_mot['motivazione'] : '';
        }
        foreach ($indici_motivazioni as $excel_idx => $pid) {
            $dataExcel[$excel_idx][10] = isset($motivazioni_per_partita[$pid]) ? $motivazioni_per_partita[$pid] : '';
        }
    }

    if (count($dataExcel) > 1)
        SimpleXLSXGen::fromArray($dataExcel)
            ->setDefaultFont('Courier New')
            ->setDefaultFontSize(14)
            ->saveAs($path . "/" . $FinalFileName . ".xlsx");

    if ($printType == "def") $webPath = PROCEDURE_WEB . $procedure_id . "/" . $FinalFileName;
    else $webPath = ARCHIVIO_WEB . "/temp/" . $FinalFileName;

    $storico->insRow('E', $storico_msg . " per ente " . $nome_ente . "[" . $c . "]");
    echo "<script>endBar('Elaborazione completata','');</script>";

?>
    <script>
        var webPathPdf = "<?= $webPath ?>" + ".pdf";
        var webPathXlsx = "<?= $webPath ?>" + ".xlsx";
        var titolo = "Elaborazione massiva discarichi";
        $('#div_report').show();
        //$('#report_pdf').attr('href','<?= $webPath ?>.pdf');
        //$('#report_excel').attr('href','<?= $webPath ?>.xlsx');

        function downloadPdf() {
            showFileOnModal(webPathPdf, titolo, webPathPdf.split('.').pop());
        }

        function downloadXlsx() {
            showFileOnModal(webPathXlsx, titolo, webPathXlsx.split('.').pop());
        }
    </script>";
<?php
}
?>

<script type="text/javascript">
    $(document).ready(function() {
        /*$("input").keydown(function(){
            $("input").css("background-color", "yellow");
        });*/
    });
</script>