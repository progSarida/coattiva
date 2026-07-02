<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
$submenuPageNo = 9;
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
$buildText = new BuildMotivationText($partita_ID);
$cls_Utils = new cls_Utils();

$partita = $cls_partita->getDataPartita($partita_ID, $c, $a);
$p = $cls_help->getVar("p");
$calling_page = $cls_help->getVar("calling_page");
//$note_blocco = $partita["Note_Blocco"];


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
    if($res[$i]["ID"]==$cls_help->getVar("DocumentTypeId")) $sel = "selected";
    $optTypeDoc .= "<option value='{$res[$i]["ID"]}' $sel >{$res[$i]["Description"]}</option>";
}

$query = "SELECT * FROM sgravio WHERE Tipo = 1 AND Partita_ID = ".$partita_ID;
$resSgravio = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

$visualizzaSgravio = "";
if($resSgravio["File_1"] == null) $visualizzaSgravio = "display: none;";
$File_1_Sgravio_Physic = $resSgravio["File_1"];
$File_1_Sgravio_Web = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($File_1_Sgravio_Physic);
$File_2_Sgravio_Physic = $resSgravio["File_2"];
$File_2_Sgravio_Web = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($File_2_Sgravio_Physic);



$query = "SELECT * FROM sgravio WHERE Tipo = 2 AND Partita_ID = ".$partita_ID;
$resAnnullamento = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"sgravio");

$visualizzaAnnullamento = "";
if($resAnnullamento["File_1"] == null) $visualizzaAnnullamento = "display: none;";
$File_1_Annull_Physic = $resAnnullamento["File_1"];
$File_1_Annull_Web = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($File_1_Annull_Physic);
$File_2_Annull_Physic = $resAnnullamento["File_2"];
$File_2_Annull_Web = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($File_2_Annull_Physic);

$flagRicevutaDiConsegna = true;

?>

<script type="text/javascript">
    //F3
    switchMenuImg("F3");
    F3_button = function(){

        control = submit_buttons("Insert");
        if(control && validateForm())
                $("#form_annullamento_sgravi").submit();
    }

    function openFile(path){
        window.open(path);
    }

</script>

<form id=form_annullamento_sgravi name=form_annullamento_sgravi action="annullamento_sgravi_salva.php" method=post >

    <input type=hidden name=c value=<?php echo $c; ?> >
    <input type=hidden name=a value=<?php echo $a; ?> >
    <input type=hidden name=a value=<?php echo $p; ?> >
    <input type=hidden name=partita value=<?php echo $partita_ID; ?> >

    <div class="row" style="margin-top: 2%;">
        <div class="form-group resize">
            <div class="col-lg-1"></div>
            <?php
            $cls_Utils->crea_dir(PDFSGRAVI."/".$c."/".$partita_ID);
            foreach (new DirectoryIterator(PDFSGRAVI."/".$c."/".$partita_ID) as $file) {
                if ($file->isFile()) {
                    //print $file->getFilename() . "\n";
                    $webPathFile = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($file->getPath()."/".$file->getFilename());
                    echo "<div class='col-lg-5'><label class='col-lg-10 control-label resize'>Lettera di accompagnamento</label><div class='col-lg-2'><img width='25' src='".IMG."/icon_pdf.png"."' onclick='window.open(\"".$webPathFile."\");' title='".$file->getFilename()."'></div></div>";
                }
            }

            $cls_Utils->crea_dir(XLSSGRAVI."/".$c."/".$partita_ID);
            foreach (new DirectoryIterator(XLSSGRAVI."/".$c."/".$partita_ID) as $file) {
                if ($file->isFile()) {
                    //print $file->getFilename() . "\n";
                    $webPathFile = SUPER_WEB_ROOT.$cls_Utils->mostra_file_path($file->getPath()."/".$file->getFilename());
                    echo "<div class='col-lg-5'><label class='col-lg-10 control-label resize'>Excel motivazioni</label><div class='col-lg-2'><img width='25' src='".IMG."/icon_excel.png"."' onclick='window.open(\"".$webPathFile."\");' title='".$file->getFilename()."'></div></div>";
                }
            }
            ?>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

    <div class="row" style="margin-top: 2%;">
        <div class="form-group resize">
            <div class=" col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="control-label resize col-lg-12">
                        <input type="checkbox" name=flag_maggiorazione id=flag_maggiorazione value="si" <?= $checkBloccoMaggiorazione; ?>>
                        <b>BLOCCO Maggiorazione</b>
                    </label>
                </div>
            </div>
            <div class=" col-lg-5">
                <div class="form-group">
                    <label class="control-label resize col-lg-12">
                        <input type="checkbox" name=flag_diritto_riscossione id=flag_diritto_riscossione value="si" <?= $checkBloccoDirittoRiscossione; ?>>
                        <b>BLOCCO Diritto Riscossione</b>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 1%;">
        <div class=" col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="control-label resize col-lg-12">
                    <input type="checkbox" <?= $bloccoCoazioneDisable; ?> name=flag_blocco id=flag_blocco value="si" <?= $checkBloccoCoazione; ?> onchange="callOnChangeNote();">
                    <b>BLOCCO Coazione</b>
                </label>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 1%;"></div>

    <div class="row" style="margin-top: 2%;">
        <div class=" col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Annullamento</label>
                <div class="col-lg-8">
                    <select id=flag_annullamento name=flag_annullamento class="form-control resize" onchange="checkBloccoState('flag_annullamento','flag_sgravio','flag_blocco');attiva_button(this,'btn_pdf_annul','flag_sgravio','btn_pdf_sgravi');callOnChangeNote();">
                        <option value=""></option>
                        <option value="si">Attiva</option>
                        <option value="no">Disattiva</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-lg-1">
            <span style="color: blue;"><b><?= $cls_date->Get_DateNewFormat($partita["Annullamento_Activation_Date"],"DB"); ?></b></span>
        </div>
        <?php if($resAnnullamento["File_1"]==null){?>
        <div class=" col-lg-3 col-lg-offset-1">
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
            <div class="col-lg-1"><img style="cursor: pointer;" title="<?= pathinfo($File_1_Annull_Physic,PATHINFO_FILENAME); ?>" width="25" src="<?=IMG;?>/icon_pdf.png" onclick="openFile('<?= $File_1_Annull_Web; ?>');"/></div>
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
        <div class=" col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Sgravi</label>
                <div class="col-lg-8">
                    <select id=flag_sgravio name=flag_sgravio class="form-control resize" onchange="checkBloccoState('flag_annullamento','flag_sgravio','flag_blocco');attiva_button(this,'btn_pdf_sgravi');callOnChangeNote();">
                        <option value=""></option>
                        <option value="si">Attiva</option>
                        <option value="no">Disattiva</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-lg-1">
            <span style="color: blue;"><b><?= $cls_date->Get_DateNewFormat($partita["Sgravio_Activation_Date"],"DB"); ?></b></span>
        </div>
        <?php if($resSgravio["File_1"]==null){?>
        <div class=" col-lg-3 col-lg-offset-1">
            <div class="form-group">
                <div class="col-lg-12">
                    <button type="button" id="btn_pdf_sgravi" class="btn btn-primary" onclick="stampaPdfSgravi();" style="display: none;width:100%;">Stampa Pdf</button>
                </div>
            </div>
        </div>
        <?php }?>
    </div>

    <div class="row" style="margin-top: 1%;<?=$visualizzaSgravio;?>">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="col-lg-1"><b>Utente</b></div>
            <div class="col-lg-1"><img style="cursor: pointer;" title="<?= pathinfo($File_1_Sgravio_Physic,PATHINFO_FILENAME); ?>" width="25" src="<?=IMG;?>/icon_pdf.png" onclick="openFile('<?= $File_1_Sgravio_Web; ?>');"/></div>
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
                        <button type="button" id="invio" class="btn btn-primary" onclick="SendMail('modalita_invio_sgravio_1',1,'utente');" style="width:100%;">Invio</button>
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
            <div class="col-lg-1"><?php if($resSgravio["Data_Spedizione_Ente"] == null && $resSgravio["Data_Spedizione_Utente"] == null) { ?><img style="cursor: pointer;" title="Elimina pdf" width="25" src="<?=IMG;?>/elimina_icon.png" onclick="deletePDF('<?= $partita_ID; ?>',1);"/><?php } ?></div>
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

    <div class="row" style="margin-top: 5%;">
        <div class=" col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipologia atto</label>
                <div class="col-lg-8">
                    <select id=tipo_atto name=tipo_atto class="form-control resize">
                        <?= $optTypeDoc; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class=" col-lg-4">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Motivi blocco</label>
                <div class="col-lg-8">
                    <select id=motivo_blocco name=motivo_blocco class="form-control resize" onchange="validityNote(this,'note_blocco');">
                        <option value="" ></option>
                        <?php echo $options_blocco; ?>
                    </select>
                </div>
            </div>
        </div>
        <!--<div class=" col-lg-4">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Motivi blocco</label>
                <div class="col-lg-8">
                    <select id=motivo_blocco_ann_sgra name=motivo_blocco_ann_sgra class="form-control resize" onchange="cambia_title('motivo_blocco');">
                        <option ></option>
                        <?php echo $options_blocco; ?>
                    </select>
                </div>
            </div>
        </div>-->
    </div>
    <!--<div class="row">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-1 control-label resize" style="text-align: left;">Note blocco</label>
                <div class="col-lg-11">
                    <input class="form-control resize" onchange="validityNote(this,'motivo_blocco');" name="note_blocco" id="note_blocco" value="<?php echo $partita["Note_Blocco"]; ?>" >
                </div>
            </div>
        </div>
    </div>-->
    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Dettaglio motivazione sgravio/annullamento</label>
                <div class="col-lg-8">
                    <textarea style="max-width: 100%;" class="form-control resize" onchange="validityNote(this,'motivo_blocco');" name="note_blocco" id="note_blocco" ><?php echo $partita["Note_Blocco"]; ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

    <div class="row justify-content-md-center " style="margin-bottom: 2%;margin-top: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Elenco Dettagli</span>
        </div>
    </div>

<?php



$query = "SELECT A.ID, PT.Tipo, A.Info_Cartella, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Notifica , P.Descrizione as DescrizioneModalitaNotifica , A.DocumentTypeId
FROM `atto`as A
JOIN partita_tributi as PT on PT.ID = A.Partita_ID
LEFT JOIN parametri_notifica as P on A.Modalita_Notifica = P.ID
WHERE A.Partita_ID = $partita_ID and A.CC = '$c'";
//echo $query;

$atti = $cls_db->getResults($cls_db->ExecuteQuery($query));

if(count($atti)>0) $last_act = $atti[count($atti)-1]["ID"];
else $last_act = null;

$query = "SELECT P.Anno_Cronologico, P.ID_Cronologico, N.Data_Notifica, PANO.Descrizione AS DescrizioneMotivoNotifica, PAN.Descrizione AS DescrizioneStatoNotifica, PA.Descrizione AS DescrizioneModalitaNotifica, P.DocumentTypeId, SUM(PAG.Importo) as TotalePagamenti
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

$buildText->SetAtto($atti);
//echo $buildText->GetHtml();
//$buildText->Reset();
//var_dump($pigno);
$buildText->SetPigno($pigno);
echo $buildText->GetHtml();




?>

</form>

<script type="text/javascript">

    $( document ).ready(function() {
        if('<?= $partita["Flag_Annullamento"]; ?>' == "si") {
            $("#flag_annullamento").val("si");
            $("#btn_pdf_annul").css("display","block");
        }
        else $("#flag_annullamento").val("no");
        if('<?= $partita["Flag_Sgravio"]; ?>' == "si") {
            $("#flag_sgravio").val("si");
            $("#btn_pdf_sgravi").css("display","block");
        }
        else $("#flag_sgravio").val("no");

        $("#motivo_blocco").val('<?= $partita["Motivo_Blocco"]; ?>');

        document.getElementById('note_blocco').dispatchEvent(new Event('change'));
        document.getElementById('motivo_blocco').dispatchEvent(new Event('change'));


    });

    function linkGestionePec(){
        location.href = "<?= WEB_ROOT; ?>/controlli/gestione_PEC.php?c=<?= $c;?>";
    }

    function SendMail(idMod,tipoAnnull,tipoPdf){
        var mod = $("#"+idMod).val();
        if(mod == "") {
            alert("Selezionare la modalità di invio");
            return false;
        }
        location.href = "<?= WEB_ROOT; ?>/coattiva/invio_mail_sgravi.php?modalita_invio="+mod+"&last_act=<?= $last_act;?>&a=<?= $a; ?>&c=<?= $c;?>&p=<?= $p; ?>&tipo="+tipoAnnull+"&Partita_ID=<?= $partita_ID; ?>&calling_page=<?= $calling_page;?>&tipoPdf="+tipoPdf;
    }

    function callOnChangeNote(){
        validityNote(document.getElementById('note_blocco'),'motivo_blocco');
        validityNote(document.getElementById('motivo_blocco'),'note_blocco');
    }

    function validityNote(el,id){
        //alert($('#flag_blocco').is(":checked")+" "+$("#flag_annullamento").val()+" "+$("#flag_sgravio").val());
        if(el.value!="") {
            $("#" + id).removeClass("validateCustom vld_Custom_r");
            //document.getElementById(id).dispatchEvent(new Event('change'));
            resetErrorOnID(id);
        }
        else if($('#flag_blocco').is(":checked") || $("#flag_annullamento").val()=="si" || $("#flag_sgravio").val()=="si") {
            //alert("add");
            $("#" + id).addClass("validateCustom vld_Custom_r");
        }
        else {
            //alert("remove");
            $("#" + id).removeClass("validateCustom vld_Custom_r");
            //callOnChangeNote();
        }
    }

    function checkBloccoState(id1,id2,id3)
    {
        if($("#"+id1).val() == "no")
            $("#"+id3).removeAttr("disabled");
        //else $("#"+id3).attr('disabled', 'disabled');
    }

    function stampaPdfAnnullamento()
    {
        //alert("stampa");
        if('<?= $partita["Flag_Annullamento"]; ?>' != $("#flag_annullamento").val() || '<?= $partita["Flag_Sgravio"]; ?>' != $("#flag_sgravio").val()) {
            alert("Salvare le modifiche prima di effettuare la stampa");
            return false;
        }
        location.href = "<?= WEB_ROOT; ?>/stampe/stampa_archiviazione_atto.php?stampa_select=DEFINITIVA&last_act=<?= $last_act;?>&a=<?= $a; ?>&c=<?= $c;?>&p=<?= $p; ?>&from=annull&Partita_ID=<?= $partita_ID;?>&calling_page=<?= $calling_page;?>";
    }
    function stampaPdfSgravi()
    {
        //alert("stampa");
        if('<?= $partita["Flag_Annullamento"]; ?>' != $("#flag_annullamento").val() || '<?= $partita["Flag_Sgravio"]; ?>' != $("#flag_sgravio").val()) {
            alert("Salvare le modifiche prima di effettuare la stampa");
            return false;
        }
        location.href = "<?= WEB_ROOT; ?>/stampe/stampa_archiviazione_atto.php?stampa_select=DEFINITIVA&last_act=<?= $last_act;?>&a=<?= $a; ?>&c=<?= $c;?>&p=<?= $p; ?>&from=sgravio&Partita_ID=<?= $partita_ID;?>&calling_page=<?= $calling_page;?>";
    }

    function deletePDF(Partita_ID,tipo){
        /*$.ajax({
            url: "demo_test.txt",
            type: "GET",
            data: {
                "Partita_ID": Partita_ID,
                "Tipo": tipo
            }
            success: function(result){
                $("#div1").html(result);
            }
        });*///finire


        location.href = "<?= WEB_ROOT; ?>/coattiva/elimina_pdf.php?last_act=<?= $last_act;?>&a=<?= $a; ?>&c=<?= $c;?>&p=<?= $p; ?>&tipo="+tipo+"&Partita_ID="+Partita_ID+"&calling_page=<?= $calling_page;?>";

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