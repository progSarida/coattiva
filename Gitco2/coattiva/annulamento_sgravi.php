<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php");

$page = $cls_help->getVar("pageCalled");

// Sgravi manuali Tipo=1: la pagina si carica in lettura per consultare i
// dati esistenti. Il blocco effettivo della scrittura e' in
// annullamento_sgravi_salva.php sul branch INSERT Tipo=1.
if($page!= null){
    if($page == "sgravi") $submenuPageNo = 9;
    else if($page == "annullamento") $submenuPageNo = 10;
    else if($page == "sgravi_1") $submenuPageNo = 11;
    else $submenuPageNo = 12;
}

if($page == "sgravi" || $page == "sgravi_1"){
    $visualizzaSgravioDivPrincipali = "display: block;";
}else $visualizzaSgravioDivPrincipali = "display: none;";

if($page == "annullamento" || $page == "annullamento_1"){
    $visualizzaAnnullamentoDivPrincipali = "display: block;";
}else $visualizzaAnnullamentoDivPrincipali = "display: none;";

$pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';
include(INC."/submenu_partita.php");


include(CLS."/cls_registry.php");
include_once(CLS."/cls_GestionePartita.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_math.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/BuildMotivationText.php");

$cls_partita = new cls_GP();
$cls_date = new cls_DateTimeI("IT",false);
$cls_mathF = new cls_math();
// Banner "salva lo Sgravio" sempre soppresso: invitava al salvataggio
// manuale che ora e' bloccato in annullamento_sgravi_salva.php (INSERT
// Tipo=1). Mostrarlo sarebbe fuorviante per l'operatore.
$showSgravioWarning = false;
$buildText = new BuildMotivationText($partita_ID, false, false, null, $showSgravioWarning);
$cls_Utils = new cls_Utils();

//if($cls_help->getVar("partita")===null)
//    die;

$partita = $cls_partita->getDataPartita($partita_ID, $c, $a);
$p = $cls_help->getVar("p");
$calling_page = $cls_help->getVar("calling_page");
//$note_blocco = $partita["Note_Blocco"];
//var_dump($partita);
$Data_Fornitura = isset($partita["Data_Fornitura"])?$partita["Data_Fornitura"]:"";

$query = "SELECT Nome, Cognome, Ditta, Genere FROM utente where ID = '".$partita["Utente_ID"]."'";
$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
//var_dump($utente);
$cognome_ditta = "";
$nome = "";
$genere = $utente["Genere"];
$comune_partita_id = $partita["Comune_ID"];


if($genere == "D"){
    $cognome_ditta = $utente["Ditta"];
    $nome = "";
}
else{
    $cognome_ditta = $utente["Cognome"];
    $nome = $utente["Nome"];
}

$parametri_notifica = $cls_partita->array_notifica();
$options_blocco = $cls_partita->options_select_array($parametri_notifica["BloccoCoattiva"]);

$checkBloccoCoazione = "";
$checkBloccoMaggiorazione = "";
$checkBloccoDirittoRiscossione = "";

if($partita["Flag_Blocco_Coazione"] == "si") $checkBloccoCoazione = "checked";
if($partita["Flag_Blocco_Maggiorazioni"] == "si") $checkBloccoMaggiorazione = "checked";
if($partita["Flag_Blocco_Diritto_Riscossione"] == "si") $checkBloccoDirittoRiscossione = "checked";

$bloccoCoazioneDisable = "";
if($partita["Flag_Annullamento"] == "si") $bloccoCoazioneDisable = "disabled";

$query = "SELECT ID, Description FROM document_type";
$res = $cls_db->getResults($cls_db->ExecuteQuery($query));
$optTypeDoc = "<option></option>";

for($i=0;$i<count($res); $i++){
    $sel = "";
    if($res[$i]["ID"]==/*$cls_help->getVar("DocumentTypeId")*/isset($partita["Atto"][count($partita["Atto"])-1]["DocumentTypeId"])?$partita["Atto"][count($partita["Atto"])-1]["DocumentTypeId"]:null) $sel = "selected";
    $optTypeDoc .= "<option value='{$res[$i]["ID"]}' $sel >{$res[$i]["Description"]}</option>";
}

?>

<script>
    function openOfcanvas(type,rif){
        // Reset campi input
        $('.user_entry').val("");

        // Reset spazi tabella
        $('#appendTableUserEntry').empty();

        selectRif = rif;
        switch (type){
            case 'user_entry':
                // Setta stato checkbox iniziale
                document.getElementById('check_u_n').checked = true;
                document.getElementById('check_u_c').checked = false;
                document.getElementById('check_e_cA').checked = false;
                document.getElementById('check_e_cP').checked = false;
                document.getElementById('check_e_i').checked = false;
                // Setta titolo modale iniziale
                $("#userEntrySearchModalLabel_u").show();
                $("#userEntrySearchModalLabel_e").hide();
                // Setta campo input iniziale
                $("#ins_u_n").show();
                $("#ins_u_c").hide();
                $("#ins_e_cA").hide();
                $("#ins_e_cP").hide();
                $("#ins_e_i").hide();
                // Setta tipop di ricerca iniziale
                //user_entry_S = "user_n";
                // Apre modale
                $('#userEntrySearchModal').modal('show');
                break;
        }
    }

    function initialId(type,val){
        switch (type){
            // Inserimento dati utente in 'Gitco2/coattiva/gestione_ruolo.php'
            case "user":
            case "cf":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                break;
            // Inserimento dati partita in 'Gitco2/coattiva/gestione_partita.php'
            case "info":
            case "entry":
            case "fore":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/annulamento_sgravi.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento']+"&pageCalled=<?=$cls_help->getVar("pageCalled")?>";
                break;
            default: alert("Ricerca non trovata!"); break;
        }
    }
</script>

<?php

if($partita_ID==null)
    die;

$query = "SELECT S.*, PR.Anno_Procedura
          FROM sgravio S
          LEFT JOIN procedures PR ON PR.Id = S.Procedure_Id
          WHERE S.Tipo = 1 AND S.Partita_ID = ".$partita_ID;
$resSgravio = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

$pathSgravio = SGRAVI.$resSgravio['ID'];
$pathSgravioWeb = SGRAVI_WEB.$resSgravio['ID'];

$visualizzaSgravio = "";
if($resSgravio["File_1"] == null && $resSgravio["File_2"] == null) $visualizzaSgravio = "display: none;";
$File_1_Sgravio_Physic = $pathSgravio."/".$resSgravio["File_1"];
$File_1_Sgravio_Web = $pathSgravioWeb."/".$resSgravio["File_1"];
$File_2_Sgravio_Physic = $pathSgravio."/".$resSgravio["File_2"];
$File_2_Sgravio_Web = $pathSgravioWeb."/".$resSgravio["File_2"];



$query = "SELECT * FROM sgravio WHERE Tipo = 2 AND Partita_ID = ".$partita_ID;
$resAnnullamento = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

$pathAnnullamento = SGRAVI.$resAnnullamento['ID'];
$pathAnnullamentoWeb = SGRAVI_WEB.$resAnnullamento['ID'];

$visualizzaAnnullamento = "";
if($resAnnullamento["File_1"] == null) $visualizzaAnnullamento = "display: none;";
$File_1_Annull_Physic = $pathAnnullamento."/".$resAnnullamento["File_1"];
$File_1_Annull_Web = $pathAnnullamentoWeb."/".$resAnnullamento["File_1"];
$File_2_Annull_Physic = $pathAnnullamento."/".$resAnnullamento["File_2"];
$File_2_Annull_Web = $pathAnnullamentoWeb."/".$resAnnullamento["File_2"];

$flagRicevutaDiConsegna = true;

?>

<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>

<script type="text/javascript">
    // Apertura modale

    //F3
    switchMenuImg("F3");
    F3_button = function(){

        control = submit_buttons("Insert");
        //alert(validateForm());
        if(control && validateForm())
                $("#form_annullamento_sgravi").submit();
    }

    switchMenuImg("F11");
    F11_button = function(){

        var page = "<?= $cls_help->getVar('pageCalled')?>";

        if(page == "annullamento_1" || page == "annullamento"){
            $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Annullamento.pdf"; ?>");
            $("#helpModalLabel").empty().append("<b>Help Annullamento</b>");
        }
        else {
            $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Sgravi.pdf"; ?>");
            $("#helpModalLabel").empty().append("<b>Help Discarico</b>");
        }

        $("#helpModal").modal('show');
    }

    <?php if($resSgravio["Data_Spedizione_Ente"] == null && $resSgravio["Data_Spedizione_Utente"] == null){ ?>
    //F4
    switchMenuImg("F4");
    F4_button = function(){

        control = submit_buttons("Delete");

        if(control) {

            if ("<?= $cls_help->getVar('pageCalled')?>" == "sgravi" || "<?= $cls_help->getVar('pageCalled')?>" == "sgravi_1") {
                $("#invia_submit").val("Delete_Sgravio");
                //alert("elimina sgravi" + $("#invia_submit").val());
                $("#form_annullamento_sgravi").submit();
            }

            if ("<?= $cls_help->getVar('pageCalled')?>" == "annullamento" || "<?= $cls_help->getVar('pageCalled')?>" == "annullamento_1") {
                $("#invia_submit").val("Delete_Annullamento");
                //alert("elimina annullamento" + $("#invia_submit").val());
                $("#form_annullamento_sgravi").submit();
            }
        }
        /*control = submit_buttons("Insert");
        //alert(validateForm());
        if(control && validateForm())
            $("#form_annullamento_sgravi").submit();*/
    }
    <?php } ?>

    function openFile(path){
        window.open(path);
    }

</script>

<?php
include_once(INC."/pages_authorization.php");
?>

<form id=form_annullamento_sgravi name=form_annullamento_sgravi action="annullamento_sgravi_salva.php" method=post >

    <input type=hidden name=c value=<?php echo $c; ?> >
    <input type=hidden name=a value=<?php echo $a; ?> >
    <input type=hidden name=p value=<?php echo $p; ?> >
    <input type=hidden name=invia_submit id=invia_submit value="" >
    <input type=hidden id=pageCalled name=pageCalled value='<?= $page; ?>' >
    <input type=hidden name=partita value=<?php echo $partita_ID; ?> >
    <input type=hidden name=sgravio_activation_date value=<?php echo $partita["Sgravio_Activation_Date"]; ?> >

    <div style="<?=$visualizzaAnnullamentoDivPrincipali; ?>">
    <div class="row" style="margin-top: 1%;">
        <div class=" col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Atto da archiviare</label>
                <div class="col-lg-8">
                    <select id=tipo_atto name=tipo_atto class="form-control resize">
                        <?= $optTypeDoc; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 1%;">

        <div class=" col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Motivi</label>
                <div class="col-lg-8">
                    <select id=motivo_blocco name=motivo_blocco class="form-control resize" onchange="alert_attiva_sgravio(this);attiva_button_annull(this,'btn_pdf_annul','flag_sgravio','btn_pdf_sgravi');callOnChangeNoteAnnull();">
                        <option value="0" ></option>
                        <?php echo $options_blocco; ?>
                    </select>
                </div>
            </div>
        </div>
        <!--<div class="col-lg-2">-->
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize" style="text-align: left;"><span style="color: blue;"><b>Creazione in data</b></span></label>
                    <div class="col-lg-6">
                        <input type="text" class="text_center form-control resize vld_date picker" size=9 id=annullamento_activation_date name=annullamento_activation_date value="<?php echo $cls_date->Get_DateNewFormat($partita["Annullamento_Activation_Date"],"DB"); ?>">
                    </div>
                </div>
            </div>
            <!--<span style="color: blue;"><b>Creazione in data <?= $cls_date->Get_DateNewFormat($partita["Annullamento_Activation_Date"],"DB"); ?></b></span>
        </div>-->
        <?php if($resAnnullamento["File_1"]==null){?>
            <div class=" col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <button type="button" id="btn_pdf_annul" class="btn btn-primary" onclick="stampaPdfAnnullamento();" style="display: none;width:100%;">Stampa Pdf</button>
                    </div>
                </div>
            </div>
        <?php }?>
    </div>
    <div class="row" style="margin-top: 1%;<?=$visualizzaAnnullamento;?>">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="col-lg-1"><b>Utente</b></div>
            <div class="col-lg-1"><img style="cursor: pointer;" title="<?= $File_1_Annull_Physic ?>" width="25" src="<?=IMG;?>/icon_pdf.png" onclick="openFile('<?= $File_1_Annull_Web; ?>');"/></div>
            <div class="col-lg-1"></div>
            <div class="col-lg-1">
                <span style="color: blue;"><b><?= $cls_date->Get_DateNewFormat($resAnnullamento["Data_Stampa"],"DB"); ?></b></span>
            </div>
            <?php if($resAnnullamento["Data_Spedizione_Utente"] == null) { ?>
                <div class=" col-lg-5 col-lg-offset-1">
                    <div class="form-group">
                        <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità invio</label>
                        <div class="col-lg-6">
                            <select id=modalita_invio_annull_1 name=modalita_invio_annull_1 class="form-control resize">
                                <option value=""></option>
                                <option value="PEC">PEC</option>
                                <option value="email">Mail</option>
                                <option value="posta">Posta</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class=" col-lg-2">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <button type="button" id="invio" class="btn btn-primary" onclick="SendMail('modalita_invio_annull_1',2,'utente');" style="width:100%;">Invio</button>
                        </div>
                    </div>
                </div>
            <?php } else {?>
                <div class=" col-lg-6 col-lg-offset-1">
                    <span style="color: blue;"><b>Dati spediti per <?= $resAnnullamento["Tipo_Spedizione_Utente"]; ?> il <?= $cls_date->Get_DateNewFormat($resAnnullamento["Data_Spedizione_Utente"],"DB"); ?></b></span>
                </div>
                <?php
                if($resAnnullamento["ID_Invio_Utente"]!=null){
                    $query = "SELECT Ricevuta_Consegna FROM email_inviate WHERE ID = ".$resAnnullamento["ID_Invio_Utente"];
                    $resAnnullUt = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"email_inviate");

                    switch($resAnnullUt["Ricevuta_Consegna"]){
                        case "attesa": $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkgoldenrod;"><i style="cursor: pointer;" title="ricevuta di consegna in attesa" class="far fa-pause-circle fa-2x"></i></div>'; break;
                        case "ok": echo '<div class="col-lg-1" style="color: darkgreen;"><i style="cursor: pointer;" title="ricevuta di consegna in data '.$cls_date->Get_DateNewFormat($resAnnullamento["Data_Spedizione_Utente"],"DB").'" class="fas fa-check-circle fa-2x"></i></div>'; break;
                        case "no":
                        case "fallita":
                        case "anomalia":
                        case "mancata":
                        default: $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkred;"><i style="cursor: pointer;" title="ricevuta di consegna fallita" class="fas fa-exclamation-triangle fa-2x"></i></div>'; break;
                    }
                }
            }?>
        </div>
    </div>
    <div class="row" style="margin-top: 1%;<?=$visualizzaAnnullamento;?>">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="col-lg-1"><b>Ente</b></div>
            <div class="col-lg-1"><img style="cursor: pointer;" title="<?= pathinfo($File_2_Annull_Physic,PATHINFO_FILENAME); ?>" width="25" src="<?=IMG;?>/icon_pdf.png" onclick="openFile('<?= $File_2_Annull_Web; ?>');"/></div>
            <div class="col-lg-1"><?php if($resAnnullamento["Data_Spedizione_Ente"] == null && $resAnnullamento["Data_Spedizione_Utente"] == null) { ?><img style="cursor: pointer;" title="Elimina i pdf" width="25" src="<?=IMG;?>/elimina_icon.png" onclick="deletePDF('<?= $partita_ID; ?>',2);"/><?php } ?></div>
            <div class="col-lg-1">
                <span style="color: blue;"><b><?= $cls_date->Get_DateNewFormat($resAnnullamento["Data_Stampa"],"DB"); ?></b></span>
            </div>
            <?php if($resAnnullamento["Data_Spedizione_Ente"] == null) { ?>
                <div class=" col-lg-5 col-lg-offset-1">
                    <div class="form-group">
                        <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità invio</label>
                        <div class="col-lg-6">
                            <select id=modalita_invio_annull_2 name=modalita_invio_annull_2 class="form-control resize">
                                <option value=""></option>
                                <option value="PEC">PEC</option>
                                <option value="email">Mail</option>
                                <option value="posta">Posta</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class=" col-lg-2">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <button type="button" id="invio" class="btn btn-primary" onclick="SendMail('modalita_invio_annull_2',2,'ente');" style="width:100%;">Invio</button>
                        </div>
                    </div>
                </div>
            <?php } else {?>
                <div class=" col-lg-6 col-lg-offset-1">
                    <span style="color: blue;"><b>Dati spediti per <?= $resAnnullamento["Tipo_Spedizione_Ente"]; ?> il <?= $cls_date->Get_DateNewFormat($resAnnullamento["Data_Spedizione_Ente"],"DB"); ?></b></span>
                </div>
                <?php
                if($resAnnullamento["ID_Invio_Ente"]!=null){
                    $query = "SELECT Ricevuta_Consegna FROM email_inviate WHERE ID = ".$resAnnullamento["ID_Invio_Ente"];
                    $resAnnullEn = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"email_inviate");

                    switch($resAnnullEn["Ricevuta_Consegna"]){
                        case "attesa": $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkgoldenrod;"><i style="cursor: pointer;" title="ricevuta di consegna in attesa" class="far fa-pause-circle fa-2x"></i></div>'; break;
                        case "ok": echo '<div class="col-lg-1" style="color: darkgreen;"><i style="cursor: pointer;" title="ricevuta di consegna in data '.$cls_date->Get_DateNewFormat($resAnnullamento["Data_Spedizione_Ente"],"DB").'" class="fas fa-check-circle fa-2x"></i></div>'; break;
                        case "no":
                        case "fallita":
                        case "anomalia":
                        case "mancata":
                        default: $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkred;"><i style="cursor: pointer;" title="ricevuta di consegna fallita" class="fas fa-exclamation-triangle fa-2x"></i></div>'; break;
                    }
                }
            }?>
        </div>
    </div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Motivazione</label>
                <div class="col-lg-10">
                    <textarea style="max-width: 100%;" class="form-control resize" name="note_blocco_annull" id="note_blocco_annull" ><?= isset($partita["Note_Blocco"])?$partita["Note_Blocco"]:""; ?></textarea>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;<?= $visualizzaAnnullamentoDivPrincipali; ?>"></div>

    <div style="<?= $visualizzaSgravioDivPrincipali; ?>">

        <div class="row justify-content-md-center " style="margin-bottom: 2%;margin-top: 2%;">
            <div class="col col-md-auto text_center">
                <span class="titolo font16 under_decor">Art. 19 D.Lgs. 112/99</span>
            </div>
        </div>
    <?php
    // Stato sgravio: visualizzazione informativa (no editing manuale).
    // Gli sgravi nascono solo da elaborazione massiva (vedi Gitco2/sgravi/elaborazione_sgravi.php).
    $isSgravioElaborato = (isset($partita["Flag_Sgravio"]) && $partita["Flag_Sgravio"] == "si");

    if ($isSgravioElaborato) {
        $tipoSgravio = isset($partita["Tipo_Sgravio"]) ? $partita["Tipo_Sgravio"] : "";
        switch ($tipoSgravio) {
            case "D": $tipoSgravioDesc = "Definitivo";  break;
            case "I": $tipoSgravioDesc = "Informativo"; break;
            case "P": $tipoSgravioDesc = "Pagato";      break;
            default:  $tipoSgravioDesc = "&mdash;";     break;
        }
        $dataElabFormatted = $cls_date->Get_DateNewFormat($partita["Sgravio_Activation_Date"], "DB");
        $annoProcSgravio   = isset($resSgravio["Anno_Procedura"]) ? $resSgravio["Anno_Procedura"] : null;
    } else {
        $annoFornitura = !empty($partita["Data_Fornitura"]) ? substr($partita["Data_Fornitura"], 0, 4) : "&mdash;";
    }
    ?>

    <div class="row" style="margin-top: 2%;">
        <div class="col-lg-10 col-lg-offset-1">
            <?php if ($isSgravioElaborato) { ?>
                <div class="row">
                    <div class="<?= ($resSgravio["File_1"] == null) ? 'col-lg-9' : 'col-lg-12'; ?>">
                        <span style="color: blue;">
                            <b>Discarico elaborato in data <?= $dataElabFormatted; ?> &mdash;
                            Tipo: <?= htmlspecialchars($tipoSgravio); ?> (<?= $tipoSgravioDesc; ?>)</b>
                        </span>
                    </div>
                    <?php if ($resSgravio["File_1"] == null) { ?>
                    <div class="col-lg-3">
                        <button type="button" id="btn_pdf_sgravi" class="btn btn-primary" onclick="stampaPdfSgravi();" style="width:100%;">Stampa Pdf</button>
                    </div>
                    <?php } ?>
                </div>
                <?php if ($annoProcSgravio !== null) { ?>
                <div class="row" style="margin-top: 1%;">
                    <div class="col-lg-12">
                        <span><b>Procedura:</b>
                            <a href="<?= WEB_ROOT; ?>/elaborazioni/comunicazioni.php?c=<?= urlencode($c); ?>&anno_filtro=<?= (int)$annoProcSgravio; ?>">
                                Comunicazioni Ente &mdash; anno <?= (int)$annoProcSgravio; ?>
                            </a>
                        </span>
                    </div>
                </div>
                <?php } ?>
            <?php } else { ?>
                <span style="color: gray;">
                    <b>Anno di fornitura: <?= $annoFornitura; ?></b> &mdash; Non ancora elaborata
                </span>
            <?php } ?>
        </div>
    </div>

    <div class="row" style="margin-top: 1%;<?=$visualizzaSgravio;?>">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="col-lg-1"><b>Ente</b></div>
            <div class="col-lg-1"><img style="cursor: pointer;" title="<?= pathinfo($File_1_Sgravio_Physic,PATHINFO_FILENAME); ?>" width="25" src="<?=IMG;?>/icon_excel.png" onclick="openFile('<?= $File_1_Sgravio_Web; ?>');"/></div>
            <div class="col-lg-1"></div>
            <div class="col-lg-1">
                <span style="color: blue;"><b><?= $cls_date->Get_DateNewFormat($resSgravio["Data_Stampa"],"DB"); ?></b></span>
            </div>
            <?php if($resSgravio["Data_Spedizione_Utente"] == null) { ?>
            <div class=" col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità invio</label>
                    <div class="col-lg-6">
                        <select id=modalita_invio_sgravio_1 name=modalita_invio_sgravio_1 class="form-control resize">
                            <option value=""></option>
                            <option value="PEC">PEC</option>
                            <option value="email">Mail</option>
                            <option value="posta">Posta</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class=" col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <button type="button" id="invio" class="btn btn-primary" onclick="SendMail('modalita_invio_sgravio_1',1,'ente');" style="width:100%;">Invio</button>
                    </div>
                </div>
            </div>
            <?php } else {?>
                <div class=" col-lg-6 col-lg-offset-1">
                    <span style="color: blue;"><b>Dati spediti per <?= $resSgravio["Tipo_Spedizione_Utente"]; ?> il <?= $cls_date->Get_DateNewFormat($resSgravio["Data_Spedizione_Utente"],"DB"); ?></b></span>
                </div>
                <?php
                    if($resSgravio["ID_Invio_Utente"]!=null){
                        $query = "SELECT Ricevuta_Consegna FROM email_inviate WHERE ID = ".$resSgravio["ID_Invio_Utente"];
                        $resSgrUt = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"email_inviate");

                        switch($resSgrUt["Ricevuta_Consegna"]){
                            case "attesa": $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkgoldenrod;"><i style="cursor: pointer;" title="ricevuta di consegna in attesa" class="far fa-pause-circle fa-2x"></i></div>'; break;
                            case "ok": echo '<div class="col-lg-1" style="color: darkgreen;"><i style="cursor: pointer;" title="ricevuta di consegna in data '.$cls_date->Get_DateNewFormat($resSgravio["Data_Spedizione_Utente"],"DB").'" class="fas fa-check-circle fa-2x"></i></div>'; break;
                            case "no":
                            case "fallita":
                            case "anomalia":
                            case "mancata":
                            default: $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkred;"><i style="cursor: pointer;" title="ricevuta di consegna fallita" class="fas fa-exclamation-triangle fa-2x"></i></div>'; break;
                        }
                    }
                }?>
        </div>
    </div>
    <div class="row" style="margin-top: 1%;<?=$visualizzaSgravio;?>">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="col-lg-1"><b>Ente</b></div>
            <div class="col-lg-1"><img style="cursor: pointer;" title="<?= pathinfo($File_2_Sgravio_Physic,PATHINFO_FILENAME); ?>" width="25" src="<?=IMG;?>/icon_pdf.png" onclick="openFile('<?= $File_2_Sgravio_Web; ?>');"/></div>
            <div class="col-lg-1"><?php if($resSgravio["Data_Spedizione_Ente"] == null && $resSgravio["Data_Spedizione_Utente"] == null) { ?><img style="cursor: pointer;" title="Elimina pdf e excel" width="25" src="<?=IMG;?>/elimina_icon.png" onclick="deletePDF('<?= $partita_ID; ?>',1);"/><?php } ?></div>
            <div class="col-lg-1">
                <span style="color: blue;"><b><?= $cls_date->Get_DateNewFormat($resSgravio["Data_Stampa"],"DB"); ?></b></span>
            </div>
            <?php if($resSgravio["Data_Spedizione_Ente"] == null) { ?>
            <div class=" col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità invio</label>
                    <div class="col-lg-6">
                        <select id=modalita_invio_sgravio_2 name=modalita_invio_sgravio_2 class="form-control resize">
                            <option value=""></option>
                            <option value="PEC">PEC</option>
                            <option value="email">Mail</option>
                            <option value="posta">Posta</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class=" col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <button type="button" id="invio" class="btn btn-primary" onclick="SendMail('modalita_invio_sgravio_2',1,'ente');" style="width:100%;">Invio</button>
                    </div>
                </div>
            </div>
            <?php } else {?>
            <div class=" col-lg-6 col-lg-offset-1">
                <span style="color: blue;"><b>Dati spediti per <?= $resSgravio["Tipo_Spedizione_Ente"]; ?> il <?= $cls_date->Get_DateNewFormat($resSgravio["Data_Spedizione_Ente"],"DB"); ?></b></span>
            </div>
                <?php
                if($resSgravio["ID_Invio_Ente"]!=null){
                    $query = "SELECT Ricevuta_Consegna FROM email_inviate WHERE ID = ".$resSgravio["ID_Invio_Ente"];
                    $resSgrEn = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"email_inviate");

                    switch($resSgrEn["Ricevuta_Consegna"]){
                        case "attesa": $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkgoldenrod;"><i style="cursor: pointer;" title="ricevuta di consegna in attesa" class="far fa-pause-circle fa-2x"></i></div>'; break;
                        case "ok": echo '<div class="col-lg-1" style="color: darkgreen;"><i style="cursor: pointer;" title="ricevuta di consegna in data '.$cls_date->Get_DateNewFormat($resSgravio["Data_Spedizione_Ente"],"DB").'" class="fas fa-check-circle fa-2x"></i></div>'; break;
                        case "no":
                        case "fallita":
                        case "anomalia":
                        case "mancata":
                        default: $flagRicevutaDiConsegna = false; echo '<div class="col-lg-1" style="color: darkred;"><i style="cursor: pointer;" title="ricevuta di consegna fallita" class="fas fa-exclamation-triangle fa-2x"></i></div>'; break;
                    }
                }
            }?>
        </div>
    </div>
    <?php if(($resSgravio["Tipo_Spedizione_Ente"] == "PEC" || $resSgravio["Tipo_Spedizione_Utente"] == "PEC" || $resAnnullamento["Tipo_Spedizione_Ente"] == "PEC" || $resAnnullamento["Tipo_Spedizione_Utente"] == "PEC") && !$flagRicevutaDiConsegna){?>
    <div class="row" style="margin-top: 2%;">
        <div class="col-lg-2 col-lg-offset-8">
            <div class="form-group">
                <div class="col-lg-12">
                    <button type="button" id="Gestione_PEC" class="btn btn-primary" onclick="linkGestionePec();" style="width:100%;">Gestione PEC</button>
                </div>
            </div>
        </div>
    </div>
    <?php }?>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

    <div class="row justify-content-md-center " style="margin-bottom: 2%;margin-top: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Elenco Dettagli</span>
        </div>
    </div>

<?php



$query = "SELECT A.ID, PT.Tipo, A.Info_Cartella, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Notifica , P.Descrizione as DescrizioneModalitaNotifica , A.DocumentTypeId, A.CC, I.Via_ID, I.Via_Cap_ID, U.Codice_Fiscale, U.Partita_Iva, ID.Presso, U.Data_Morte, PT.Tipo AS Entrata
FROM `atto`as A
JOIN partita_tributi as PT on PT.ID = A.Partita_ID
LEFT JOIN parametri_notifica as P on A.Modalita_Notifica = P.ID
LEFT JOIN utente as U on U.ID = PT.Utente_ID
LEFT JOIN indirizzo as I on I.Utente_ID = PT.Utente_ID and I.Tipo = 'res'
LEFT JOIN indirizzo as ID on ID.Utente_ID = PT.Utente_ID and ID.Tipo = 'rec'
WHERE A.Partita_ID = $partita_ID and A.CC = '$c' ORDER BY A.ID ASC";
//echo $query;

$atti = $cls_db->getResults($cls_db->ExecuteQuery($query));

if(count($atti)>0) $last_act = $atti[count($atti)-1]["ID"];
else $last_act = null;

$query = "SELECT P.ID, P.Anno_Cronologico, P.ID_Cronologico, N.Data_Notifica, PANO.Descrizione AS DescrizioneMotivoNotifica, PAN.Descrizione AS DescrizioneStatoNotifica, PA.Descrizione AS DescrizioneModalitaNotifica, P.DocumentTypeId, SUM(PAG.Importo) as TotalePagamenti
FROM pignoramento_generale as P
LEFT JOIN pagamento as PAG on PAG.Atto_ID = P.ID AND PAG.Partita_ID = P.Partita_ID 
LEFT JOIN notifica_atto as N on N.Atto_Notificato_ID = P.ID
LEFT Join parametri_notifica as PA on N.Modalita_Notifica = PA.ID
LEFT Join parametri_notifica as PAN on N.Stato_Notifica = PAN.ID
LEFT Join parametri_notifica as PANO on N.Motivo_Notifica = PANO.ID
where P.CC = '$c' AND P.Partita_ID = $partita_ID";

//echo $query;
$pigno = $cls_db->getResults($cls_db->ExecuteQuery($query));

$buildText->IsDebug(true);


$buildText->SetPigno($pigno);
$buildText->SetAtto($atti);


//echo $buildText->GetHtml();
//$buildText->Reset();
//var_dump($pigno);

echo $buildText->GetHtml();



?>
    </div>
</form>

<script type="text/javascript">

    $( document ).ready(function() {


        if('<?= $partita["Motivo_Blocco"]; ?>' != "0" && '<?= $partita["Motivo_Blocco"]; ?>' != "64" && '<?= $partita["Motivo_Blocco"]; ?>' != "") {
            //$("#flag_annullamento").val("si");
            $("#btn_pdf_annul").css("display","block");
        }
        //else $("#flag_annullamento").val("no");

        $("#motivo_blocco").val('<?= $partita["Motivo_Blocco"]; ?>');

        document.getElementById('note_blocco_annull').dispatchEvent(new Event('change'));
        document.getElementById('motivo_blocco').dispatchEvent(new Event('change'));

        //if('<?= $partita["Flag_Annullamento"]; ?>' == "si")
          //  $("#btn_pdf_annul").css("display","block");
    });

    function alert_attiva_sgravio(el){
        if(el.value != "0" && el.value != "" && $('#pageCalled').val()=="annullamento")
            alert("Attivando l'annullamento si attiva in automatico anche il discarico!");
    }

    function linkGestionePec(){
        location.href = "<?= WEB_ROOT; ?>/controlli/gestione_PEC.php?c=<?= $c;?>";
    }

    function SendMail(idMod,tipoAnnull,tipoPdf){
        var mod = $("#"+idMod).val();
        if(mod == "") {
            alert("Selezionare la modalità di invio");
            return false;
        }
        location.href = "<?= WEB_ROOT; ?>/coattiva/invio_mail_sgravi.php?modalita_invio="+mod+"&last_act=<?= $last_act;?>&a=<?= $a; ?>&c=<?= $c;?>&p=<?= $p; ?>&tipo="+tipoAnnull+"&Partita_ID=<?= $partita_ID; ?>&calling_page=<?= $calling_page;?>&tipoPdf="+tipoPdf+"&pageCalled=<?= $cls_help->getVar("pageCalled");?>";
    }

    function callOnChangeNoteAnnull(){

        if($("#motivo_blocco").val()==0 || $("#motivo_blocco").val()==null) $("#note_blocco_annull").removeClass("validateCustom vld_Custom_r");
        else $("#note_blocco_annull").addClass("validateCustom vld_Custom_r");

        resetErrorOnID("note_blocco_annull");
        //validityNote(document.getElementById('note_blocco'),'motivo_blocco');
        //validityNote(document.getElementById('motivo_blocco'),'note_blocco');
    }

    function checkBloccoState(id1,id2,id3)
    {
        if($("#"+id1).val() == "no")
            $("#"+id3).removeAttr("disabled");
        //else $("#"+id3).attr('disabled', 'disabled');
    }

    function stampaPdfAnnullamento()
    {
        if('<?= $partita["Motivo_Blocco"]; ?>' != $("#motivo_blocco").val()) {
            alert("Salvare le modifiche prima di effettuare la stampa");
            return false;
        }

        location.href = "<?= WEB_ROOT; ?>/elaborazioni/elenco_sgravi_annull.php?a=<?= $a; ?>&c=<?= $c;?>&cognome_ditta=<?= $cognome_ditta; ?>&nome=<?= $nome; ?>&genere=<?= $genere; ?>&comune_id=<?= $comune_partita_id; ?>&partita_id=<?= $partita_ID;?>&pageCalled=<?= $cls_help->getVar("pageCalled");?>&data_fornitura=<?= $Data_Fornitura; ?>";
    }
    function stampaPdfSgravi()
    {
        location.href = "<?= WEB_ROOT; ?>/sgravi/gestione_stampa_sgravi.php?a=<?= $a; ?>&c=<?= $c;?>&cognome_ditta=<?= $cognome_ditta; ?>&nome=<?= $nome; ?>&genere=<?= $genere; ?>&comune_id=<?= $comune_partita_id; ?>&partita_id=<?= $partita_ID;?>&pageCalled=<?= $cls_help->getVar("pageCalled");?>&data_fornitura=<?= $Data_Fornitura; ?>";
    }

    function deletePDF(Partita_ID,tipo){
        location.href = "<?= WEB_ROOT; ?>/coattiva/elimina_pdf.php?last_act=<?= $last_act;?>&a=<?= $a; ?>&c=<?= $c;?>&p=<?= $p; ?>&tipo="+tipo+"&Partita_ID="+Partita_ID+"&calling_page=<?= $calling_page;?>&pageCalled=<?= $cls_help->getVar("pageCalled");?>";
    }

    function attiva_button_annull(el ,id, id2=null,id2Btn = null){
        if(el.value != "0" && el.value != "64" && el.value != "" ){
            $("#"+id).css("display","block");
            if(id2!=null) {
                $("#" + id2).val("si");
                if(id2Btn!=null)
                    $("#" + id2Btn).css("display","block");
            }
        }
        else {
            $("#"+id).css("display","none");
        }
    }

    function attiva_button(el ,id, id2=null,id2Btn = null){
        //alert(el.value);
        if(el.value == "si"){
            $("#"+id).css("display","block");
            if(id2!=null) {
                $("#" + id2).val("si");
                if(id2Btn!=null)
                    $("#" + id2Btn).css("display","block");
            }
        }
        else {
            $("#"+id).css("display","none");
        }
    }

    function AggiornaText(el,id){
        //alert(el.value);
        $("#"+id).val(el.value);
    }
</script>

<?php include(INC."/footer.php"); ?>