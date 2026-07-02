<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");


$today = $cls_help->toDbDate(date("d-m-Y"));
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$auth = $_SESSION['aut_tipo'];

$partita_ID = $cls_help->getVar('partita');
$tipo_atto = $cls_help->getVar('tipo_atto');
$tipo_partita = $cls_help->getVar('tipo_partita');


$disabled = "";

$query = "SELECT * FROM enti_gestiti WHERE CC='$c' ORDER by Denominazione ASC";
$e_entiGestiti = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");
$a_selection = array("value" => "CC", "firstOpt" => 0, "selected" => $c, "text" => array("[Denominazione]", " - ", "[CC]"));
$optEnte = $cls_html->getOptions($e_entiGestiti, $a_selection);


$query = "SELECT * FROM ruolo WHERE CC='".$c."' ORDER by Data_Inserimento DESC";
$a_ruoli = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");
$a_selection = array("value" => "ID", "firstOpt" => 0, "selected" => null, "text" => array("[CC]", " - ", "[Descrizione]", " - ", "[Data_Inserimento]"));
$optRuolo = $cls_html->getOptions($a_ruoli, $a_selection);

$query_tax = "SELECT * FROM tax_type ORDER by Id ASC";
$k_tax = $cls_db->getResults($cls_db->ExecuteQuery($query_tax), "array", "Id");
$k_selection = array("value" => "Name", "firstOpt" => 0, "selected" => "CDS", "text" => array("[Name]"));
$opt_tax = $cls_html->getOptions($k_tax, $k_selection);

$nome_user = "Operatore: ".$_SESSION['username'];

$checkMsg = "";

$da_anno = $a;
$ad_anno = $a;

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++)
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";
?>

    
    <!-- ********** GESTIONE LINK MENU ********** -->
    <script>

        //F5
        switchMenuImg("F5");
        F5_button = function()
        {
            location.href="start_elaboration.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto=<?php echo $tipo_atto ?>";
        }

        //F11-F12 sono nel menu'
        switchMenuImg("F11");
        F11_button = function(){
            $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Elaborazioni_Atti.pdf"; ?>");
            $("#helpModalLabel").empty().append("<b>Help Elaborazioni Atti</b>");
            $("#helpModal").modal('show');
        }

    </script>

    <!-- ********** CALENDARIO ********** -->
    <script>

        $(function() {

            $( ".picker" ).datepicker();

        });

    </script>

    <!-- ********** AGGIORNAMENTO PAGINA ********** -->
    <script>

        function insert_a_data()
        {
            $('#a_data').val( $('#da_data').val() );
        }
    </script>

    <!-- ********** MODALI ********** -->
    <script>

        function Dim_Alert ( sWidth, sHeight )
        {
            setupPagina = "dialogWidth:" + sWidth + "px";
            setupPagina += "; dialogHeight:" + sHeight + "px";
            setupPagina += ";dialogLeft:80px;dialogTop:80px;";

            return setupPagina;
        }

       
        var selectParent = "";
        var selectRif = "";
        function RicercheDaId (value, rif)
        {
            selectParent = value;
            selectRif = rif;
            var valorediritorno = 0;
            var strDim = Dim_Alert(600, 300);

            switch(value)
            {
                case "utente":

                    strDim = Dim_Alert(800, 500);
                    var stringa = "<?= WEB_ROOT; ?>/coattiva/modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    valorediritorno = window.showModalDialog(stringa,"", strDim);

                    break;
            }
        }



    </script>

    <!-- ********** SUBMIT ********** -->
    
            <div class="row justify-content-md-center " style="margin: 2%;">
                <div class="col col-md-auto text_center">
                    <span class="titolo font16 under_decor">Gestione elaborazioni</span>
                </div>
            </div>

            <form id="form_elabora" name="form_elabora" action="elab_check.php" method="post" target="elabora" onSubmit="window.open('', 'elabora', 'width=1500,height=500,top=70,left=70,scrollbars=yes,menubar=no')">
                
                <input type=hidden name="c" value="<?php echo $c ?>">
                <input type=hidden name="a" value="<?php echo $a ?>">

                <?php
                    $sel_1 = "";
                    $sel_2 = "";
                    $sel_3 = "";
                    $sel_4 = "";
                    $sel_5 = "";
                    if($tipo_atto == "Ingiunzione") $sel_1 = "selected";
                    if($tipo_atto == "avv_intimazione") $sel_2 = "selected";
                    if($tipo_atto == "sollecito_pre") $sel_3 = "selected";
                    if($tipo_atto == "avviso_mora") $sel_4 = "selected";
                    if($tipo_atto == "sollecito") $sel_5 = "selected";
                ?>

                <div class="row">
                    <div class="col col-lg-4 col-lg-offset-4">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo atto</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select class="form-control resize validateCustom vld_Custom_r" name="tipo_atto" id="tipo_atto">
                                    <option value=""></option>
                                    <option value="2" <?= $sel_1; ?> >Ingiunzione</option>
                                    <option value="4" <?= $sel_2; ?> >Avviso d'intimazione</option>
                                    <option value="11" <?= $sel_3; ?> >Sollecito pre ingiunzione</option>
                                    <option value="12" <?= $sel_4; ?> >Avviso di messa in mora</option>
                                    <option value="3" <?= $sel_5; ?> >Sollecito di pagamento</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-md-center " style="margin: 2%;">
                    <div class="col col-md-auto text_center">
                        <span class="titolo font16 under_decor">Selezione</span>
                    </div>
                </div>
                <div class="row" style="margin-top: 1%;">
                <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>
                <?php                    
                    if(intval($auth) === 1){
?>                        
                    <div class="col col-lg-4 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Ente</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                            <select class="form-select validateCustom vld_Custom_r"  id="ente" name="ente"  style="height:35px; width:75%;">
                                <option value=''>Seleziona ente </option>
                                <?= $optEnte; ?>
                            </select>
                            </div>
                        </div>
                    </div>
                    <?php                    
                   }
?>
                    <div class="col col-lg-4 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Ruolo</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select id="ruolo" name="ruolo"  style="height:35px; width:75%;">
                                    <option value=''></option>
                                    <?= $optRuolo; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col col-lg-4 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo Entrata</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select name="tipo_partita" id="tipo_partita" class="form-control resize">
                                    <option value=''></option>
                                    <?php echo $opt_tax; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4 col-lg-offset-1">
                        <label class="col-lg-12 control-label resize">Escludi prescrizione
                        &nbsp;
<i class="fa-solid fa-circle-info fa-lg" style="color: #294A9C"
title="FLAG PRESCRIZIONE

Le posizioni prescritte, rilevate nelle precedenti
elaborazioni, non verranno mostrate."
                        ></i>
                            &nbsp;&nbsp;
                            <input type="checkbox" name="Is_Expired" value="1">
                        </label>

                    </div>
                </div> <br/>
                <div class="row">
                    <div class="col col-lg-4 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Data Elaborazione</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <input type="date" name="data_elab" id="data_elab" size="9" style="width: 50%;" class="text_center validateCustom vld_Custom_r" value="<?= $today?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Data Calcolo</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <input type="date" name="data_int" id="data_int" size="9" style="width: 50%;" class="text_center validateCustom vld_Custom_r" value="<?= $today?>" required>
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
                                    <option value=""></option>
                                    <?php echo $serieOption ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-3">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Da data di notifica</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="da_data" id="da_data" value="" onchange="insert_a_data();" size=9  tabindex=13>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Da anno di riferimento</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <input type="text" class="form-control resize" id="da_anno" name="da_anno" value="" size=3  tabindex=15>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" >
                    <div class="col col-lg-3 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">A partita</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select id="a_n_elenco" name="a_n_elenco" tabindex=12 class="form-control resize">
                                    <option value=""></option>
                                    <?php echo $serieOption ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-3">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">A data di notifica</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="a_data" id="a_data" value="" size=9  tabindex=14>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Ad anno di riferimento</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <input type="text" class="form-control resize" id="ad_anno" name="ad_anno" value="" size=3 tabindex=16 onblur="focusIndex();">
                            </div>
                        </div>
                    </div>
                </div>
                <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>
                <div class="row" >
                    <div class="col col-lg-5 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Descrizione</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                               <textarea class="form-control validateCustom vld_Custom_r" id="description" name="description" rows="3" style="width: 600px;"></textarea>
                                <input type="hidden" id= "auth" name = "auth" value="<?= $auth ?>">
                            </div>
                        </div>
                    </div> 
                </div> 
                <?php include_once "defaultTipoUfficiale.php"?>
                <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>
                
                <button type="submit" class="btn btn-primary pull-right" style="margin-bottom:30px;margin-right:25px;">Pre Elaborazione</button>

            </form>

            <script src="<?=ELABORAZIONI_JS;?>\EnteRuolo.js" />

<?php include(INC."/footer.php"); ?>