<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

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

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

//$cls_partita = new cls_GP();
$cls_date = new cls_DateTimeI("IT",false);
$cls_check = new cls_check();
$cls_Utils = new cls_Utils();
$cls_elab = new cls_elaborazioniUtils();
$cls_elaboration = new cls_elaboration();
//AGGIUNGERE SANZIONE DA INGIUNZIONE


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');


$data_elab_visual = date('d/m/Y');
$disabled = "";

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++)
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";

$a_parAnnuali = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM parametri_annuali WHERE CC='".$c."' AND Anno=".date('Y')),"array","Anno");
if(!empty($a_parAnnuali[date('Y')]))
    $a_params['Parametri_Annuali'] = $a_parAnnuali[date('Y')];
else{
    echo "PARAMETRI ANNO ".date('Y')." ASSENTI!";
    die;
}
$cls_elaboration->setParams($a_params);

/*$queryYears = "SELECT * FROM anni_gestiti WHERE CC_Anno = '".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC";
$a_years = $cls_db->getResults( $cls_db->SelectQuery($queryYears) );*/


/*if($cls_help->getVar("anno_elab")==$annoIniz) $selectYear = "selected";
else $selectYear = "";*/

if($cls_help->getVar("data_elab")==NULL) $data_elab_form = date("d/m/Y");
else $data_elab_form = $cls_help->getVar("data_elab");

$dataTemp = new cls_DateTime($data_elab_form,"IT",false);

$dataTemp->AddYear("-2");
$annoIniz = $dataTemp->GetYear();

/*$dropAnni = "<option></option>";
$dropAnni .= "<option value='".$annoIniz."' ".$selectYear." >".$annoIniz."</option>";

for($i=0; $i<20; $i++){
    $annoIniz--;
    if($cls_help->getVar("anno_elab")==$annoIniz) $selectYear = "selected";
    else $selectYear = "";
    $dropAnni .= "<option value='".$annoIniz."' ".$selectYear.">".$annoIniz."</option>";
}*/
$rielabora = $cls_help->getVar("rielabora");

if($rielabora == "si") $rielaboraFlag = "checked";
else $rielaboraFlag = "";


//$parametri_notifica = $cls_partita->array_notifica();
//$options_blocco = $cls_partita->options_select_array($parametri_notifica["BloccoCoattiva"]);

?>
<script src="<?= JS ?>/myValidator.js" type="text/javascript"></script>
<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_sel_offcanvas.php"); ?>
<script>
    //Modali offcanvas
    function openOfcanvas(type,rif){
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
                if(rif == 2 && $('#daco').val() == '')
                    alert("Inserire prima l'utente da cui far partire la ricerca");
                else
                    $('#userSelSearchModal').modal('show');
        }
    }
    function initialId(type,val){
        switch (type){
            case 'user_sel':
                if(selectRif == 1)                                          // "Da Cognome/Nome"
                {
                    //alert("qui 1");
                    if(val['Ditta'] != '' && val['Ditta'] != null){     // è una ditta
                        $('#daco').val(val['Ditta']);
                        $('#acog').val(val['Ditta']);
                        $('#dano').val('');
                        $('#anom').val('');
                    } else{                                                 // è una persona
                        $('#daco').val(val['Cognome']);
                        $('#acog').val(val['Cognome']);
                        $('#dano').val(val['Nome']);
                        $('#anom').val(val['Nome']);
                    }

                }
                else if(selectRif == 2)                                     // "A Cognome/Nome"
                {
                    if(val['Ditta'] != '' && val['Ditta'] != null){     // è una ditta
                        $('#acog').val(val['Ditta']);
                        $('#anom').val('');
                    } else{                                                 // è una persona
                        $('#acog').val(val['Cognome']);
                        $('#anom').val(val['Nome']);
                    }
                }
                break;
            default: alert("Errore Ricerca");
        }
    }
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        $("#iniziaSgravio").val("no");
        location.href="sgravio_automatico.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        $("#iniziaSgravio").val("si");
        if(validateForm())
            $("#visura_form").submit();
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Elaborazione_Sgravio_Automatico.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Elaborazione Sgravi automatici</b>");
        $("#helpModal").modal('show');
    }

    $( document ).ready(function() {
        $("#tipo_partita").val('<?= $cls_help->getVar("tipo_partita")?>');
        $("#da_n_elenco").val('<?= $cls_help->getVar("da_n_elenco")?>');
        $("#a_n_elenco").val('<?= $cls_help->getVar("a_n_elenco")?>');
    });

    function callParent(valorediritorno){
        switch(selectParent){
            case "utente":

                if(valorediritorno!=null)
                {
                    $.post("ajax/ajax_cognome.php?c=<?php echo $c; ?>" ,

                        { 'ajax': 'nome' ,
                            'ID': valorediritorno },

                        function (value) {

                            var array_ritorno = value.split('*');

                            console.log(array_ritorno);

                            if(selectRif==1)
                            {
                                $('#daco').val(array_ritorno[0]);
                                $('#acog').val(array_ritorno[0]);
                            }
                            else if(selectRif==2)
                            {
                                $('#acog').val(array_ritorno[0]);
                            }

                            if(array_ritorno.length == 3)
                            {
                                if(selectRif==1)
                                {
                                    $('#dano').val(array_ritorno[1]);
                                    $('#anom').val(array_ritorno[1]);
                                    $("#genere_da").val(array_ritorno[2]);
                                    $("#genere_a").val(array_ritorno[2]);
                                }
                                else if(selectRif==2)
                                {
                                    $('#anom').val(array_ritorno[1]);
                                    $("#genere_a").val(array_ritorno[2]);
                                }
                            }
                            else
                            {
                                if(array_ritorno.length == 2) {
                                    if(selectRif==1)
                                    {
                                        $("#genere_a").val(array_ritorno[1]);
                                        $("#genere_da").val(array_ritorno[1]);
                                    }
                                    else if(selectRif==2)
                                    {
                                        $("#genere_a").val(array_ritorno[1]);
                                    }
                                }
                                else $("#genere").val("");

                                if(selectRif==1)
                                {
                                    $('#dano').val("");
                                    $('#anom').val("");
                                }
                                else if(selectRif==2)
                                {
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
    function RicercheDaId (value, rif)
    {
        selectParent = value;
        selectRif = rif;
        var valorediritorno = 0;
        //var strDim = Dim_Alert(600, 300);

        switch(value)
        {
            case "utente":

                //strDim = Dim_Alert(800, 500);
                var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale_sel.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                //valorediritorno = window.showModalDialog(stringa,"", strDim);
                openWindowSearch(stringa,{width:800, height:500, left:(($(window).width()/2)-400), top:(($(window).height()/2)-250)});

                break;
        }
    }

    function setDataElab(el,id){
        $("#"+id).val("30/03/"+el.value);
    }
    function setAnnoElab(el,id){
        $("#"+id).val(el.value.substring(6) - 2);
    }

    function startBar(){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function updateBar(valore){
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function noResultsBar(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function endBar(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Elaborazione terminata!");
    }

</script>



<form id="visura_form" name="visura_form" action="sgravio_automatico.php" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />
    <input type=hidden name="genere_da" id="genere_da" value="" />
    <input type=hidden name="genere_a" id="genere_a" value="" />
    <input type=hidden name="iniziaSgravio" id="iniziaSgravio" value="" />

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Crea sgravio automatico (Art. 19 D.Lgs. 112/99)</span>
        </div>
    </div>

    <div class="row" style="display: none;" id="div_report">
        <div class="col-lg-2 col-lg-offset-1"><b>Report PDF</b></div>
        <div class="col-lg-1"><a id="report_pdf" href="#" target="_blank"><img width="25" src="<?= IMMAGINIWEB; ?>\icon_pdf.png"></a></div>
        <div class="col-lg-3"></div>
        <div class="col-lg-2"><b>Report EXCEL</b></div>
        <div class="col-lg-1"><a id="report_excel" href="#"><img width="25" src="<?= IMMAGINIWEB; ?>\icon_excel.png"></a></div>
    </div>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Selezione atti</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data elaborazione (presa in considerazione per il calcolo dello sgravio)</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker validateCustom vld_Custom_r" type="text" id="data_elab" name="data_elab" value="<?= $data_elab_form; ?>" onchange="setAnnoElab(this,'anno_elab');" tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno elaborazione</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input readonly class="form-control resize" type="text" id="anno_elab" name="anno_elab" value="<?= $annoIniz; ?>"  tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="si" id="rielabora_id" name="rielabora" <?= $rielaboraFlag; ?> >
                <label class="form-check-label" for="rielabora_id">
                    Rielabora
                </label>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize " type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_sel',1);" tabindex=4>
            </div>
        </div>
        <div class="col col-lg-2" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input  readonly class="form-control resize" type="text" id="daco" name="daco" value="<?= $cls_help->getVar("daco"); ?>"  tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input readonly class="form-control resize" type="text" id="dano" name="dano" value="<?= $cls_help->getVar("dano"); ?>" tabindex=6>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo Entrata</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select name=tipo_partita id=tipo_partita class="form-control resize">
                        <option value=""></option>
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
                <input class="btn btn-primary form-control resize" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_sel',2);" tabindex=7>
            </div>
        </div>
        <div class="col col-lg-2" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input readonly class="form-control resize" type="text" id="acog" name="acog" value="<?= $cls_help->getVar("acog"); ?>"  tabindex=7>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input readonly class="form-control resize" type="text" id="anom" name="anom" value="<?= $cls_help->getVar("anom"); ?>"  tabindex=9>
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
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da anno di riferimento</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <input type="text" class="form-control resize" id="da_anno" name="da_anno" value="<?= $cls_help->getVar("da_anno"); ?>" size=3  tabindex=15>
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
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Ad anno di riferimento</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <input type="text" class="form-control resize" id="ad_anno" name="ad_anno" value="<?= $cls_help->getVar("ad_anno"); ?>" size=3 tabindex=16 onblur="focusIndex();">
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbar" style="height:55px;width:100%;"><div class="text_center" id="barlabel"></div></div>
        </div>
    </div>

</form>
<?php

if($cls_help->getVar('iniziaSgravio')=="si") {

    flush();	ob_flush();
    echo "<script>startBar();</script>";
    flush();	ob_flush();		flush();	ob_flush();

    $dataSgravio = new cls_DateTime(date("d/m/Y"),"IT",false);
    $dataElaborazioneMassima = new cls_DateTime($cls_help->getVar("data_elab"),"IT",false);
//    if($dataSgravio->CompareDate("IT",">",$dataElaborazioneMassima->GetDate())){
//        $cls_help->alert($dataSgravio->GetDate()." > ".$dataElaborazioneMassima->GetDate());
//        $cls_help->alert("Data di elaborazione già superata");
//    }

    $elab_date_2years = new cls_DateTime($dataElaborazioneMassima->GetDateDB(),"DB",false);
    $elaboration_date = new cls_DateTime($dataElaborazioneMassima->GetDateDB(),"DB",false);
    $elab_date_5years = new cls_DateTime($dataElaborazioneMassima->GetDateDB(),"DB",false);
    $elab_date_2years->AddYear("-2");
    $elab_date_5years->AddYear("-5");

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
    $filter["da_anno_riferimento"] = $cls_help->getVar("da_anno");
    $filter["a_anno_riferimento"] = $cls_help->getVar("ad_anno");
    $filter["data_elaborazione"] = $elaboration_date->GetDate("IT");

    $filtriDescrizione = "Elaborazione sgravio con filtri: ";
    $filtriDescrizione.= "Data fornitura compresa tra il ".$elab_date_2years->GetDate("IT")." e il ".$elaboration_date->GetDate("IT");

    $query = "SELECT * FROM v_check_partite WHERE CC = '" . $c . "' ";//AND DocumentTypeId is not null
    $query.= "AND Data_Fornitura > '".$elab_date_5years->GetDateDB()."' AND Data_Fornitura < '".$elab_date_2years->GetDateDB()."' ";
    if($rielabora!="si")
        $query.= "AND (Flag_Sgravio!='si' OR Flag_Sgravio is null)";

    if($dacognome != null){
        $strCompareDa = addslashes($dacognome)." ".addslashes($danome);
        $strCompareA = addslashes($acognome)." ".addslashes($anome);

        $query .= " AND ( CONCAT(COALESCE(Ditta,''),COALESCE(Cognome,''),' ',COALESCE(Nome,'')) >= '".$strCompareDa."' AND CONCAT(COALESCE(Ditta,''),COALESCE(Cognome,''),' ',COALESCE(Nome,'')) <= '".$strCompareA."' ) ";
    }
    //echo $query;

//    if ($genere_da != "D" && $genere_a != "D") {
//        if ($dacognome != null) {
//
//            $query .= " AND ( ( U.Cognome > '" . addslashes($dacognome) . "' ) ";
//            $query .= "AND ( U.Cognome < '" . addslashes($acognome) . "' ) ";
//            $query .= "OR ( U.Cognome = '" . addslashes($dacognome) . "' ";
//            if ($danome != null) {
//                $query .= "AND U.Nome >= '" . addslashes($danome) . "' ";
//            }
//
//            $query .= ") OR ( U.Cognome = '" . addslashes($acognome) . "' ";
//            if ($anome != null) {
//                $query .= "AND U.Nome <= '" . addslashes($anome) . "' ";
//            }
//            $query .= ") ) ";
//
//            $filtriDescrizione .= " Da cognome/nome ".addslashes($dacognome)." ".addslashes($danome)." a cognome/nome ".addslashes($acognome)." ".addslashes($anome).", ";
//        }
//
//    }
//    else if($genere_da == "D" && $genere_a == "D") {
//        if ($dacognome != null)
//            $query .= " AND ( U.Ditta >= '" . addslashes($dacognome) . "' ";
//        if ($acognome != null) $query .= " AND U.Ditta <= '" . addslashes($acognome) . "' ) ";
//        else $query .= ") ";
//
//        $filtriDescrizione .= " Da ditta " . addslashes($dacognome) . " a ditta " . addslashes($acognome).", ";
//    }
//    else{
//        if(($genere_da == "D" || $genere_da == "M" || $genere_da == "F") && $genere_a == null){
//            if($genere_da == "D") {
//                $query .= " AND U.Ditta >= '" . addslashes($dacognome) . "' ";
//                $filtriDescrizione .= " Da ditta " . addslashes($dacognome).", ";
//            }
//            else {
//                $query .= " AND U.Cognome >= '" . addslashes($dacognome) . "' ";
//                $filtriDescrizione .= " Da cognome " . addslashes($dacognome).", ";
//            }
//        }
//        else if(($genere_a == "D" || $genere_a == "M" || $genere_a == "F") && $genere_da == null){
//            if($genere_a == "D") {
//                $query .= " AND U.Ditta <= '" . addslashes($acognome) . "' ";
//                $filtriDescrizione .= " A ditta " . addslashes($acognome).", ";
//            }
//            else {
//                $query .= " AND U.Cognome <= '" . addslashes($acognome) . "' ";
//                $filtriDescrizione .= " A cognome " . addslashes($acognome).", ";
//            }
//        }
//        else {
//            $cls_help->alert("Non selezionare ditte e utenti insieme nella selezione utenti!! (Da Cognome/Nome - A Cognome/Nome). Selezionare entrambe imprese o entrambi utenti!");
//            flush();	ob_flush();
//            echo "<script>endBar();</script>";
//            flush();	ob_flush();		flush();	ob_flush();
//            die;
//        }
//    }

    if ($cls_help->getVar("tipo_partita") != null) {
        $query .= " AND Tipo_Riscossione = '" . $cls_help->getVar("tipo_partita") . "' ";

        $filtriDescrizione .= " - Tipo partita: ".$cls_help->getVar("tipo_partita");
    }
    if ($daNEl != null) {
        $query .= " AND Comune_ID >= " . $daNEl;

        $filtriDescrizione .= " - Da partita ".$daNEl;
    }
    if ($aNEl != null) {
        $query .= " AND Comune_ID <= " . $aNEl;

        $filtriDescrizione .= " a partita ".$aNEl;
    }
    if ($cls_help->getVar("da_data") != null) {
        $query .= " AND Data_Notifica_Atto >= '" . $cls_date->GetDateDB($cls_help->getVar("da_data"), "IT") . "' OR Data_Notifica_Atto IS NULL ";

        $filtriDescrizione .= " - Da data notifica ".$cls_help->getVar("da_data");
    }
    if ($cls_help->getVar("a_data") != null) {
        $query .= " AND Data_Notifica_Atto <= '" . $cls_date->GetDateDB($cls_help->getVar("a_data"), "IT") . "' OR Data_Notifica_Atto IS NULL";

        $filtriDescrizione .= " a data notifica ".$cls_help->getVar("a_data");
    }
    if ($cls_help->getVar("da_anno") != null) {
        $query .= " AND Anno_Riferimento >= '" . $cls_help->getVar("da_anno") . "'";

        $filtriDescrizione .= " - Da anno riferimento ".$cls_help->getVar("da_anno");
    }
    if ($cls_help->getVar("ad_anno") != null) {
        $query .= " AND Anno_Riferimento <= '" . $cls_help->getVar("ad_anno") . "'";

        $filtriDescrizione .= " ad anno riferimento ".$cls_help->getVar("ad_anno");
    }
    $query .= " GROUP BY Partita_ID ORDER BY Comune_ID ";

    //echo $query;

    $result = $cls_db->getResults($cls_db->ExecuteQuery($query));


    $pdf = new cls_pdf("L", "mm", "A4", true, 'UTF-8', false);
    $pdf->setHeaderTitle("");

    $a_headerPage[0] = array("Partita ID","Data Fornitura","Info","Codice Tributo","Totale Preso in carico.","Totale residuo","Esito","Data elaborazione");

    $pdf->setArray($a_headerPage,"a_headerPage");
    $percent = 100/10*($pdf->getPageWidth()-20)/100;
    $a_width = array( $percent , $percent , $percent*2 , $percent*2 , $percent , $percent , $percent, $percent );
    $a_align = array( "R" , "L" , "L" , "L" , "R" , "R" , "L", "L" );
    $pdf->setArray($a_width,"a_width");
    $pdf->setArray($a_align,"a_align");
    $pdf->setHeaderPage();
    $pdf->addLines();

    $dataExcel[] = array("<b>Partita ID</b>","<b>Data Fornitura</b>","<b>Info</b>","<b>Codice Tributo</b>","<b>Totale Preso in carico</b>","<b>Totale residuo</b>","<b>Esito</b>","<b>Data elaborazione</b>");

    $total_1 = 0;
    $total_2 = 0;
    $total_all_1 = 0;
    $total_all_2 = 0;
    $countAllResult = count($result);
    $contSgravi = 0;
    for ($i = 0; $i < $countAllResult; $i++) {

        flush();	ob_flush();		flush();	ob_flush();
        echo "<script>updateBar(".ceil($i*100/$countAllResult).");</script>";
        flush();	ob_flush();		flush();	ob_flush();

        $totale = $cls_elaboration->getResidual($result[$i]);
        $data_Fornitura = new cls_DateTime($result[$i]['Data_Fornitura'],"DB",false);

        if($totale<=0 && $result[$i]["ID_ATTO"] != null){
            $totale = 0;
            $displayTot = null;
            $sgravioStatus = "SALDATO";
        }
        else{
            $displayTot = number_format($totale,2,",",".");
            if($result[$i]["Flag_Sgravio"] == "si" && $rielabora=="si")
                $sgravioStatus = "RIELABORATO";
            else
                $sgravioStatus = "SGRAVIO";
        }



        $queryTributi = "SELECT Anno_Tributo, Codice_Tributo, Imposta, 
                                IF(Tipo_Sanzione = 'VE','Verbale',
                                    IF(Tipo_Sanzione = 'AC','Accertamento',
                                        IF(Tipo_Sanzione = 'OR','Ordinanza',
                                            IF(Tipo_Sanzione = 'IN','Ingiunzione',
                                                IF(Tipo_Sanzione = 'DM','Decreto Ministeriale','')
                                                )
                                            )
                                        )
                                    ) AS Tipo_Sanzione FROM `tributo` where Partita_ID = ".$result[$i]["Partita_ID"];

        $resultTributi = $cls_db->getResults($cls_db->ExecuteQuery($queryTributi));


        $countTrib = count($resultTributi);
        $descrCodTrib = "";
        //$insertedHeader = array();
        $totaleTributi = 0;
        for($z = 0; $z < $countTrib; $z++){
            //$insertedHeader[] = "Totale Tributo ".($z+1);
            $descrCodTrib .= " - Codice Tributo: ".$resultTributi[$z]["Codice_Tributo"].", Anno: ".$resultTributi[$z]["Anno_Tributo"].", Tipo atto: ".$resultTributi[$z]["Tipo_Sanzione"].", Importo: ".$resultTributi[$z]["Imposta"]."\n";
            $totaleTributi += (double) $resultTributi[$z]["Imposta"];
        }

        $total_1 += $totaleTributi;
        $total_all_1 += $totaleTributi;
        if($result[$i]["ID_ATTO"] == null) {
            $total_2 += $totaleTributi;
            $total_all_2 += $totaleTributi;
            $displayTot = number_format($totaleTributi, 2, ",", ".");
        }
        else {
            $total_2 += $totale;
            $total_all_2 += $totale;
        }

//echo $total_2." --- ".$totale."\n";

        $a_value[0] = array(
            $result[$i]["Comune_ID"],
            $data_Fornitura->GetDate("IT"),
            $result[$i]["Info_Cartella"],
            $descrCodTrib,
            number_format($totaleTributi,2,",","."),
            $displayTot,
            $sgravioStatus,
            $elaboration_date->GetDate("IT")
        );

        $a_total[0] = array(
              "","","","",number_format($total_1,2,",","."),number_format($total_2,2,",","."),"",""
        );

        //$inserted[] = number_format($totaleTributi,2,",","");
        //$insertedHeader[] = "Totale";
        //array_splice( $header, 3, 0, $insertedHeader );
        //array_splice( $a_value[0], 3, 0, $inserted );
        //$dataExcel[] = $header;
        $dataExcel[] = $a_value[0];

        if($totale > 0 ){
            $contSgravi++;
            if($contSgravi==1){
                $a_dbParams = array(
                    'table' => 'procedures',
                    'fields'=> array(
                        array(  'name' => 'Procedure_Type_Id',      'type' => 'int',        'value' => 2                           ),
                        array(  'name' => 'Datetime',               'type' => 'date',       'value' => date('Y-m-d H:i:s')  ),
                        array(  'name' => 'Procedure_Date',         'type' => 'date',       'value' => $cls_date->GetDateDB($cls_help->getVar("data_elab"), "IT") ),
                        array(  'name' => 'CC',                     'type' => 'string',     'value' => $c                          ),
                        array(  'name' => 'User_Id',                'type' => 'int',        'value' => $_SESSION['aut_progr']      ),
                        array(  'name' => 'Description',            'type' => 'string',     'value' => $filtriDescrizione          ),
                    )
                );
                $procedure_id = $cls_db->DbSave( $a_dbParams);
            }

            //SE RIELABORO SETTO IL FLAG Is_Canceled per indicare che è stato rielaborato
            if($result[$i]["Flag_Sgravio"] == "si" && $rielabora == "si"){
                $query = "DELETE FROM sgravio WHERE Partita_ID=".$result[$i]["Partita_ID"]." AND Tipo=1";
                $cls_db->ExecuteQuery($query);
            }
            $a_dbParams = array(
                'table' => 'sgravio',
                'fields'=> array(
                    array(  'name' => 'Procedure_Id',           'type' => 'int',        'value' => $procedure_id               ),
                    array(  'name' => 'Partita_ID',             'type' => 'int',        'value' => $result[$i]["Partita_ID"]   ),
                    array(  'name' => 'CC',                     'type' => 'string',     'value' => $c                          ),
                    array(  'name' => 'Tipo',                   'type' => 'int',        'value' => 1                           ),
                )
            );
            $sgravio_id = $cls_db->DbSave($a_dbParams);

            if($result[$i]["Flag_Sgravio"] == "si" && $rielabora == "si"){
                $query = "DELETE FROM sgravi_documenti WHERE Partita_ID = ".$result[$i]["Partita_ID"];
                $cls_db->ExecuteQuery($query);
            }

            $buildText = new BuildMotivationText($result[$i]["Partita_ID"],false,true, $sgravio_id);

            $queryAtti = "SELECT A.ID, PT.Tipo, A.Info_Cartella, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Notifica , 
                    P.Descrizione as DescrizioneModalitaNotifica , A.DocumentTypeId, A.CC,
                    SUM(PAG.Importo) as TotalePagamenti
                    FROM `atto`as A
                    JOIN partita_tributi as PT on PT.ID = A.Partita_ID
                    LEFT JOIN pagamento as PAG on PAG.Atto_ID = A.ID AND PAG.Partita_ID = A.Partita_ID    
                    LEFT JOIN parametri_notifica as P on A.Modalita_Notifica = P.ID
                    WHERE A.Partita_ID = ".$result[$i]['Partita_ID']." and A.CC = '".$c."'
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
                    where P.CC = '".$c."' AND P.Partita_ID = ".$result[$i]['Partita_ID']."
                    GROUP BY P.ID";
            $pigno = $cls_db->getResults($cls_db->ExecuteQuery($queryPigno));

            $buildText->IsDebug(true);
            $buildText->SetPigno($pigno);
            $buildText->SetAtto($atti);

            $buildText->SaveAllOnDB();

            $save = array();
            $save["Flag_Sgravio"] = "si";
            $save["Sgravio_Activation_Date"] = $cls_date->GetDateDB($cls_help->getVar("data_elab"), "IT");// date("Y-m-d");
            $save["Sgravio_Save_Activation_Date"] = date("Y-m-d");

            $arrWhere = array("ID" => $result[$i]["Partita_ID"]);

            $a_paramsSgraviDoc = $cls_Utils->GetObjectQuery($save, "partita_tributi", $arrWhere);
            if (!$cls_db->DbSave($a_paramsSgraviDoc)) {

                $error = 1;
                $msg = "Errore impossibile aggiornare i dati. " . $cls_db->GetError();
                $cls_db->Rollback();
                header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
                die;
            }
            else $msg = "Dati aggiornati correttamente";
        }

        $force = false;
        if($i == $countAllResult - 1)
            $force = true;

        $flag = $pdf->setRowPageTotal($a_value, 8, 10, 50,$a_total,$force);
        if(!$force)
            $pdf->addLines("dash");

        if($flag){
            $total_2 = 0;
            $total_1 = 0;
        }
    }

    if($contSgravi == 0) {
        flush();	ob_flush();		flush();	ob_flush();
        echo "<script>noResultsBar();</script>";
        flush();	ob_flush();		flush();	ob_flush();
        die;
    }

    $path = $cls_Utils->crea_dir(PROCEDURE.$procedure_id);

    $FinalFileName = "Elaborazione_Sgravi_".$procedure_id."_".$dataElaborazioneMassima->GetDateDB();

    $a_mainPageParams = array("title" => strtoupper($a_enteAdmin['Denominazione']), "subtitle" => "ESITI ELABORAZIONE SGRAVI AUTOMATICI");
    $pdf->setMainPageParams($a_mainPageParams);
    $a_filters = $cls_elab->getFiltersDescription($filter);
    if($rielabora == "si") {
        $last_i = count($a_filters);
        $a_filters[$last_i]["label"] = "RIELABORA";
        $a_filters[$last_i]["value"] = "SI";
    }
    $recap[0]['label'] = "NUMERO PAGINE";
    $recap[0]['value'] = $pdf->getPage() + 1;
    $recap[1]['label'] = "NUMERO ATTI";
    $recap[1]['value'] = count($result);
    $recap[2]['label'] = "TOTALE PRESO IN CARICO";
    $recap[2]['value'] = number_format($total_all_1,2,",",".");
    $recap[3]['label'] = "TOTALE RESIDUO";
    $recap[3]['value'] = number_format($total_all_2,2,",",".");
    $pdf->setMainPage($a_filters, $recap);

    $pdf->Output( $path."/".$FinalFileName.".pdf" , 'F');

    if(count($dataExcel) > 1)
        SimpleXLSXGen::fromArray( $dataExcel )
            ->setDefaultFont( 'Courier New' )
            ->setDefaultFontSize( 14 )
            ->saveAs($path."/".$FinalFileName.".xlsx");

    $webPath = PROCEDURE_WEB.$procedure_id."/".$FinalFileName;

    flush();	ob_flush();
    echo "<script>endBar();</script>";
    flush();	ob_flush();		flush();	ob_flush();
?>
    <script>
        $('#div_report').show();$('#report_pdf').attr('href','<?=$webPath?>.pdf');
        $('#report_excel').attr('href','<?=$webPath?>.xlsx');
    </script>";
<?php
}
    ?>

    <script type="text/javascript">

        $(document).ready(function(){
            /*$("input").keydown(function(){
                $("input").css("background-color", "yellow");
            });*/
            $("#data_elab").keyup(function(){
                var str = $("#data_elab").val();
                if(str.length == 8 && !str.includes("/")){
                    var day = str.substring(0, 2);
                    var month = str.substring(2, 4);
                    var year = str.substring(4);

                    $("#data_elab").val(day+"/"+month+"/"+year);
                }
            });
        });
    </script>
