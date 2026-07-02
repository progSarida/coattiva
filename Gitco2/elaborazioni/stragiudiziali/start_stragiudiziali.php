<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");


$cls_html = new cls_html();
$today = $cls_help->toDbDate(date("d-m-Y"));
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$auth = $_SESSION['aut_tipo'];

$partita_ID = $cls_help->getVar('partita');
$tipo_atto = $cls_help->getVar('tipo_atto');
//$tipo_partita = $cls_help->getVar('tipo_partita');


$disabled = "";

$query = "SELECT * FROM enti_gestiti where CC='$c' ORDER by Denominazione ASC";
$e_entiGestiti = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");
$a_selection = array("value" => "CC", "firstOpt" => 0, "selected" => $c, "text" => array("[Denominazione]", " - ", "[CC]"));
$optEnte = $cls_html->getOptions($e_entiGestiti, $a_selection);


$a_docTypes = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM document_type WHERE  Id in (40) ORDER BY TableTypeId ASC"), "array", "Id");
$a_selection = array("value" => "Id", "firstOpt" => 0, "selected" => null, "text" => array("[Description]"));
$optDocs = $cls_html->getOptions($a_docTypes, $a_selection);

$optDocs ="<option value='Banca'>Banca</option><option value='Previdenziali'>Previdenziali</option>";


$query = "SELECT * FROM ruolo WHERE CC='".$c."' ORDER by Data_Inserimento DESC";
$a_ruoli = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");
$a_selection = array("value" => "ID", "firstOpt" => 0, "selected" => null, "text" => array("[CC]", " - ", "[Descrizione]", " - ", "[Data_Inserimento]"));
$optRuolo = $cls_html->getOptions($a_ruoli, $a_selection);

$query_tax = "SELECT * FROM tax_type ORDER by Id ASC";
$k_tax = $cls_db->getResults($cls_db->ExecuteQuery($query_tax), "array", "Id");
$k_selection = array("value" => "Name", "firstOpt" => 0, "selected" => null, "text" => array("[Name]"));
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


    <script>

        //F5
        switchMenuImg("F5");
        F5_button = function()
        {
            location.href="start_pregiudiziali.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto=<?php echo $tipo_atto ?>";
        }
        switchMenuImg("F11");
        F11_button = function(){
                $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Elaborazione_Stragiudiziali.pdf"; ?>");
                $("#helpModalLabel").empty().append("<b>Help Stragiudiziali</b>");
                $("#helpModal").modal('show');
        }
        $(function() {

            $( ".picker" ).datepicker();

        });

    </script>

    <!-- ********** SUBMIT ********** -->
    
            <div class="row justify-content-md-center " style="margin: 2%;">
                <div class="col col-md-auto text_center">
                    <span class="titolo font16 under_decor">Elaborazione procedure stragiudiziali Art. 75 bis D.P.R. 602/1973</span>
                </div>
            </div>
            

            <form id="form_elabora" name="form_elabora" action="elab_check_stragiudiziali.php" method="post" >
                
                <input type=hidden name="c" value="<?php echo $c ?>">
                <input type=hidden name="a" value="<?php echo $a ?>">
                <input type=hidden name="p" value="<?php echo $p ?>">

                <div class="row">
                    <div class="col col-lg-4 col-lg-offset-4">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select class="form-control resize validateCustom vld_Custom_r" name="Tipo" id="Tipo" tabindex=1>
                                    <?= $optDocs; ?>
                                </select>
                            </div>
                        </div>
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
                            <select class="form-select validateCustom vld_Custom_r"  tabindex=2 id="ente" name="ente" style="height:35px; width:75%;">
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
                                <select id="ruolo" name="ruolo"  tabindex=3 style="height:35px; width:75%;">
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
                                <select name="tipo_partita" tabindex=4 id="tipo_partita" class="form-control resize">
                                    <option value=''></option>
                                    <?php echo $opt_tax; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div> <br/>
                <div class="row">
                    <div class="col col-lg-4 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Data Elaborazione</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <input type="date" name="data_elab" id="data_elab" tabindex=5 size="9" style="width: 50%;" class="text_center validateCustom vld_Custom_r" value="<?= $today?>" required>
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
                                <select id="da_n_elenco" name="da_n_elenco" tabindex=6 class="form-control resize">
                                    <option value=""></option>
                                    <?php echo $serieOption ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4">
                        <label class="col-lg-6 control-label resize" style="text-align: left;">Da data di notifica</label>
                        <div class="form-group">
                            <div class="col-lg-6">
                                <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="da_data" id="da_data" value="" onchange="insert_a_data();" size=9  tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4 ">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Da anno</label>
                        <div class="form-group">
                            <div class="col-lg-4">
                                <input type="text" class="form-control resize" id="da_anno" name="da_anno" value="" size=3  tabindex=8>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" >
                    <div class="col col-lg-3 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">A partita</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select id="a_n_elenco" name="a_n_elenco" tabindex=9 class="form-control resize">
                                    <option value=""></option>
                                    <?php echo $serieOption ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4">
                        <label class="col-lg-6 control-label resize" style="text-align: left;">A data di notifica</label>
                        <div class="form-group">
                            <div class="col-lg-6">
                                <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="a_data" id="a_data" value="" size=9  tabindex=10>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" >
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Ad anno </label>
                        <div class="form-group">
                            <div class="col-lg-4">
                                <input type="text" class="form-control resize" id="ad_anno" name="ad_anno" value="" size=3 tabindex=11 onblur="focusIndex();">
                            </div>
                        </div>
                    </div>
                </div>
                <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>
                <div class="row" >
                    <div class="col col-lg-12 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Descrizione</label>
                        <div class="form-group">
                            <div class="col-lg-10">
                               <textarea class="form-control validateCustom vld_Custom_r" tabindex=12 id="description" name="description" rows="3" style="width:100%;"></textarea>
                                <input type="hidden" id= "auth" name = "auth" value="<?= $auth ?>">
                            </div>
                        </div>
                    </div> 
                </div> 
                <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>
                
                <button type="submit" tabindex=13 class="btn btn-primary pull-right" style="margin-bottom:30px;margin-right:25px;">Pre Elaborazione</button>

            </form>

<script src="<?=ELABORAZIONI_JS;?>\EnteRuolo.js" />


<?php include(INC."/footer.php"); ?>