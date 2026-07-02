<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_print.php");
include_once(CLS."/cls_ruolo.php");
include_once(CLS."/cls_html.php");
include_once(CLS."/cls_notParameters.php");

//print or list
$tipo_atto = $docType = $cls_help->getVar('docType');
$printType = $cls_help->getVar('printType');

//$cls_help->alert($docType);
$Tipo = "";
switch($docType)
{
    case "ING":
    case "AV_INT":
    case "AV_MORA":
    case "SOLL_POST":
    case "SOLL_PRE": $Tipo = "atto"; break;
    case "lavoro":
    case "banca":
    case "preav_fermo":
    case "veicolo": $Tipo = "pigno"; break;
    default: break;
}


$Select_Tax = $a_enteAdmin['Select_Tax'];
if($Select_Tax<1 && $printType!="list")
    $cls_help->alert("Non e' possibile procedere con le elaborazioni senza aver impostato la Gestione entrate in PARAMETRI -> ENTE -> DATI ENTE");

if($printType=="list"){
    if($Select_Tax==1){
        $optionsRiscossione = "
                <option value=\"\"></option>
                <option value=\"CDS\">CDS/AMMINISTRATIVA</option>
				<option>IMMOBILI</option>
				<option>IRPEF</option>
				<option>OSAP</option>
				<option>PATRIMONIALE</option>
				<option value=\"PUBBLICITA\">PUBBLICITA'</option>
				<option>RIFIUTI</option>";
    }
    else if($Select_Tax==2)
        $optionsRiscossione = "<option value=\"\">TUTTE</option>";
    else{
        $optionsRiscossione = "";
        $Select_Tax = 0;
    }
}
else{
    $optionsRiscossione = "
                <option value=\"CDS\">CDS/AMMINISTRATIVA</option>
				<option>IMMOBILI</option>
				<option>IRPEF</option>
				<option>OSAP</option>
				<option>PATRIMONIALE</option>
				<option value=\"PUBBLICITA\">PUBBLICITA'</option>
				<option>RIFIUTI</option>";
}


if($Select_Tax==null)
    $Select_Tax = 0;

$cls_notPar = new cls_notParameters();
$a_notParams = $cls_notPar->setNotParametersArray(
    $cls_db->getResults($cls_db->SelectQuery($cls_notPar->getParametersQuery("modalita"))),
    $cls_db->getResults($cls_db->SelectQuery($cls_notPar->getParametersQuery("stato"))),
    $cls_db->getResults($cls_db->SelectQuery($cls_notPar->getParametersQuery("motivo")))
);

$cls_html = new cls_html();
$a_selection = array("value"=>"ID","firstOpt"=>0,"selected"=>null, "text"=>array("[Descrizione]"));
$options['notificationMode'] = $cls_html->getOptions($a_notParams['mode'],$a_selection);
$options['notificationStock'] = $cls_html->getOptions($a_notParams['stock'],$a_selection);
$options['notificationAnomaly'] = $cls_html->getOptions($a_notParams['anomaly'],$a_selection);

$a_printer = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM printer"));
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>null, "text"=>array("[Name]"));
$options['PrinterId'] = $cls_html->getOptions($a_printer,$a_selection);

$a_printType = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM print_type WHERE Disabled=0"));
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>null, "text"=>array("[Description]"));
$options['PrintTypeId'] = $cls_html->getOptions($a_printType,$a_selection);

$a_printType = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM print_type WHERE Id=1 OR Id=6 AND Disabled=0"));
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>null, "text"=>array("[Description]"));
$options['PrintTypeIdAV_INT'] = $cls_html->getOptions($a_printType,$a_selection);

$a_printTypeOrdinaria = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM print_type WHERE Id=3 OR Id=6 AND Disabled=0"));
$a_selection = array("value"=>"Id","firstOpt"=>0,"selected"=>null, "text"=>array("[Description]"));
$options['PrintTypeOrdinaria'] = $cls_html->getOptions($a_printTypeOrdinaria,$a_selection);

$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($docType);
$DocumentTypeId = 0;
if(isset($cls_ruolo->a_docDetails['DocumentTypeId']))
    $DocumentTypeId = $cls_ruolo->a_docDetails['DocumentTypeId'];

if($docType == "esiti") $a_city = array("cc"=>"AUTH_".$_SESSION['aut_tipo'], "city"=>$adminCity);
else $a_city = array("cc"=>$c, "city"=>$adminCity);

//echo $printType." - ".$docType." - ".$a_city." - ".$options;

$cls_print = new cls_print($printType,$docType,$a_city,$options);

//echo $printType." ".$docType;


$a_years = $cls_db->getResults($cls_db->SelectQuery("SELECT Anno FROM anni_gestiti WHERE CC_Anno ='".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC"));
$opt_years = "";
for($i=0;$i<count($a_years);$i++)
    $opt_years.= "<option value='".$a_years[$i]['Anno']."'>".$a_years[$i]['Anno']."</option>";

$a_partite = $cls_db->getResults($cls_db->SelectQuery("SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC"));
$serieOption = "";
for($i=0;$i<count($a_partite);$i++)
    $serieOption .= "<option value='" . $a_partite[$i]['Comune_ID'] . "'>" . $a_partite[$i]['Comune_ID'] . "</option>";

if($cls_print->year_blank=="y")
    $tax_year = "";
else
    $tax_year = $a;

//$cls_help->alert($cls_print->a_type['action']);

?>

<!-- GESTIONE MODALI -->
<!-- Inclusione modale per ricerca utente-->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>
<script>
    // Modali offcanvas
    function openOfcanvas(type,rif){
        // Reset campi input
        $('.user_entry').val("");

        // Reset spazi tabella
        $('#appendTableUserEntry').empty();

        selectRif = rif;
        switch (type){
            case 'user_entry':
                // Nasconde radio
                $("#checkbox_c").hide();
                // Setta stato checkbox (fisso perchè ho nascosto i radio) per ricerca
                document.getElementById('check_u_n').checked = true;
                document.getElementById('check_u_c').checked = false;
                document.getElementById('check_e_cA').checked = false;
                document.getElementById('check_e_cP').checked = false;
                document.getElementById('check_e_i').checked = false;
                // Setta titolo modale (fisso perchè ho nascosto i radio)
                $("#userEntrySearchModalLabel_u").show();
                $("#userEntrySearchModalLabel_e").hide();
                // Setta campo input (fisso perchè ho nascosto i radio)
                $("#ins_u_n").show();
                $("#ins_u_c").hide();
                $("#ins_e_cA").hide();
                $("#ins_e_cP").hide();
                $("#ins_e_i").hide();
                // Apre modale
                if(rif == 2 && $('#from_surname').val() == '')
                    alert("Inserire prima l'utente da cui far partire la ricerca");
                else
                    $('#userEntrySearchModal').modal('show');
                break;
        }
    }
    // Iserimento dati da modale a pagine
    function initialId(type,val){
        switch (type){
            case 'user':
                if(selectRif == 1)                                          // "Da Cognome/Nome"
                {
                    //alert("qui 1");
                    if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
                        $('#from_surname').val(val['Ditta']);
                        $('#to_surname').val(val['Ditta']);
                        $('#from_name').val('');
                        $('#to_name').val('');
                    } else{                                                 // è una persona
                        $('#from_surname').val(val['Cognome']);
                        $('#to_surname').val(val['Cognome']);
                        $('#from_name').val(val['Nome']);
                        $('#to_name').val(val['Nome']);
                    }

                }
                else if(selectRif == 2)                                     // "A Cognome/Nome"
                {
                    if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
                        $('#to_surname').val(val['Ditta']);
                        $('#to_name').val('');
                    } else{                                                 // è una persona
                        $('#to_surname').val(val['Cognome']);
                        $('#to_name').val(val['Nome']);
                    }
                }
                break;
            default: alert("Errore Ricerca");
        }
    }
</script>
<!-- -->

    <script>
        var printType = "<?=$printType;?>";
        var select_tax = <?=$Select_Tax;?>;
        var type = "<?=$docType;?>";
        var titolo = "";
        //F5
        switchMenuImg("F5");
        F5_button = function(){
            location.href="gestione_stampe.php?printType=<?php echo $printType; ?>&docType=<?php echo $docType; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        }

        //F10
        switchMenuImg("F10");
        F10_button = function(){
            if($("#PrinterId").val()=="" && printType!="list")
                alert("Inserire lo stampatore!");
            else if(select_tax<1 && printType!="list")
                alert("Non e' possibile procedere con le elaborazioni senza aver impostato la Gestione entrate in PARAMETRI -> ENTE -> DATI ENTE");
            else
                ajaxCall();
                //$('#print_form').submit();
        }

        $(document).ready(function(){
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
        /*
        if(getParameterByName("file") != null)
            showFileOnModal(getParameterByName("file"),titolo,getParameterByName("file").split('.').pop());
        */
        });

        function callParent(valorediritorno){
            if(valorediritorno!=null)
            {
                $.post("<?= WEB_ROOT; ?>/ajax/ajax_stampe.php?c=<?php echo $c; ?>" ,

                    { 'ajax': 'nome' ,
                        'ID': valorediritorno },

                    function (value) {

                        var array_ritorno = value.split('*');

                        if(selectRif==1){
                            $('#from_surname').val(array_ritorno[0]);
                            $('#to_surname').val(array_ritorno[0]);
                        }
                        else if(selectRif==2){
                            $('#to_surname').val(array_ritorno[0]);
                        }

                        if(array_ritorno.length == 2){
                            if(selectRif==1){
                                $('#from_name').val(array_ritorno[1]);
                                $('#to_name').val(array_ritorno[1]);
                            }
                            else if(selectRif==2){
                                $('#to_name').val(array_ritorno[1]);
                            }
                        }
                        else{
                            if(selectRif==1){
                                $('#from_name').val("");
                                $('#to_name').val("");
                            }
                            else if(selectRif==2){
                                $('#to_name').val("");
                            }
                        }
                    }
                );
            }
        }

        function ajaxCall() {
            spinner.startSpinner();
            switch(type){
                case "esiti":
                    titolo = "Esiti notifiche";
                    break;
                case "notifiche":
                    titolo = "Cartoline notifiche";
                    break;
                case "court_hearing":
                    titolo = "Udienze";
                    break;
                case "positions":
                    titolo = "Posizioni";
                    break;
                case "last_acts":
                    titolo = "Ultimi atti";
                    break;
                case "SOLL_PRE":
                    titolo = "Solleciti pre ingiunzione";
                    break;
                case "AV_MORA":
                    titolo = "Avvisi di messa in mora";
                    break;
                case "ING":
                    titolo = "Ingiunzioni";
                    break;
                case "AV_INT":
                    titolo = "Avvisi di intimazione";
                    break;
                case "SCORPORO_ING":
                    titolo = "Dettaglio ingiunzioni";
                    break;
                case "SOLL_POST":
                    titolo = "Solleciti post ingiunzione";
                    break;
            }
            //alert("ajax");
            //return;
            $.ajax({
            //url: "print_storico.php",
            url: $("form").attr('action'),
            //data: new FormData(document.getElementById("storico_form")),
            data: $("form").serialize(),
            dataType : 'json',
            type: 'POST',
            success: function (resp) {
                spinner.closeSpinner();
                ShowAlert(resp.error,resp.msg);
                if(resp.error == 0)
                    showFileOnModal(resp.path,titolo,resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
        }

        var selectRif = "";
        function getUtente(rif){
            selectRif = rif;
            var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            window.open(stringa, "", "width=600,height=300,top=70,left=70,scrollbars=yes,menubar=no");
        }

        function leadingZero(val){
            var str = val.toString();
            if(str.length == 1)
            {
                str = '0' + str;
            }
            return str;
        }

        function changeDate(value){
            switch(value){
                case "courtHearing":
                    date1 = $("#from_courtHearingDate").val();
                    dt1   = parseInt(date1.substring(0, 2));
                    mon1  = parseInt(date1.substring(3, 5))-1;
                    yr1   = parseInt(date1.substring(6, 10));

                    futureDate = new Date(yr1, mon1, dt1);
                    futureDate.setDate(futureDate.getDate() + 60);

                    newDate = leadingZero(futureDate.getDate()) + '/' + leadingZero(futureDate.getMonth() + 1) + '/' + leadingZero(futureDate .getFullYear());
                    $("#to_courtHearingDate").val(newDate);
                    break;
            }
        }

        var action = "<?= $cls_print->a_type['action']; ?>";

        //viene assegnata alla drop name=printType in cls_print
        function changeAction(field){

            var form = document.getElementById('print_form');
            if(field.value == "crono")
            {
                form.action = "create_crono.php";
                form.target = "";
                $('#print_form').attr('onsubmit', '');
            }
            else if(field.value == "ini_pec"){
                form.action = "pec_request.php";
                form.target = "";
                $('#print_form').attr('onsubmit', '');
            }
            else{
                form.action = action;
                form.target = "stampa";
                $('#print_form').attr('onsubmit', "window.open('', 'stampa', 'width=1050,height=500,top=70,left=70,scrollbars=yes,menubar=no')");
            }

        }

        function getQueryParams(qs) {
            qs = qs.split('+').join(' ');

            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;

            while (tokens = re.exec(qs)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }

            return params;
        }


        function recallPageWithType(el){

            var queryString = getQueryParams(document.location.search);

            if(el.value != "") {
                var link = "gestione_stampe.php?";
                if (queryString.printType != undefined) {
                    link += "printType=" + queryString.printType + "&";
                }
                if (queryString.docType != undefined) {
                    link += "docType=" + el.value + "&";
                }
                if (queryString.p != undefined) {
                    link += "p=" + queryString.p + "&";
                }
                if (queryString.c != undefined) {
                    link += "c=" + queryString.c + "&";
                }
                if (queryString.a != undefined) {
                    link += "a=" + queryString.a + "&";
                }

                link = link.slice(0, -1);

                location.href = link;
                return true;
            }
            return false;
        }


    </script>

<?php

$sel_1 = "";
$sel_2 = "";
$sel_3 = "";
$sel_4 = "";
$sel_5 = "";
if($tipo_atto == "ING") $sel_1 = "selected";
if($tipo_atto == "AV_INT") $sel_2 = "selected";
if($tipo_atto == "SOLL_PRE") $sel_3 = "selected";
if($tipo_atto == "AV_MORA") $sel_4 = "selected";
if($tipo_atto == "SOLL_POST") $sel_5 = "selected";

if(($tipo_atto == "ING" || $tipo_atto == "AV_INT" || $tipo_atto == "SOLL_PRE" || $tipo_atto == "AV_MORA" || $tipo_atto == "SOLL_POST") && $printType == "html"){
?>

    <div class="row" style="margin-bottom: 3%;">
        <div class="col col-lg-4 col-lg-offset-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;color: #356bc1;">Tipo atto</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select class="resize" name=page id=page onchange="recallPageWithType(this);">
                        <option value=""></option>
                        <option value="ING" <?= $sel_1; ?> >Ingiunzione</option>
                        <option value="AV_INT" <?= $sel_2; ?> >Avviso d'intimazione</option>
                        <option value="SOLL_PRE" <?= $sel_3; ?> >Sollecito pre ingiunzione</option>
                        <option value="AV_MORA" <?= $sel_4; ?> >Avviso di messa in mora</option>
                        <option value="SOLL_POST" <?= $sel_5; ?> >Sollecito post ingiunzione</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

    <div id=titolo style="width: 100%;text-align: center;"><span class="titolo font16 under_decor"><?php echo $cls_print->a_type['title']; ?></span></div>
    
    <br>

<!--    <form id="print_form" name="print_form" action="<?php echo $cls_print->a_type['action']; ?>" method="post" target="stampa" onSubmit="window.open('', 'stampa', 'width=1200,height=400,top=100,left=100,scrollbars=yes,menubar=no')"> -->
        <form id="print_form" name="print_form" action="<?php echo $cls_print->a_type['action']; ?>" method="post">

        <input type=hidden name="c" value="<?php echo $c ?>">
        <input type=hidden name="a" value="<?php echo $a ?>">
        <input type=hidden name="docType" value="<?php echo $docType ?>">
        <input type=hidden name="DocumentTypeId" value="<?php echo $DocumentTypeId ?>">
        <input type=hidden name="type" value="<?php echo $Tipo; ?>">

        <div class="width90 text_center pheight10" style="clear:both;"><hr></div>
        <div class="width90 text_center pheight20" style="clear:both;">
            <div style="float:left;" class="width25 text_left">
                <input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*getUtente(1);*/openOfcanvas('user_entry',1);">
            </div>
            <div style="float:left;" class="width40 text_center">
                <input type="text" id="from_surname" name="from_surname" class="width55" >
                <input type="text" id="from_name" name="from_name" class="width35">
            </div>
            <div style="float:left;" class="width15 text_left">Da partita</div>
            <div style="float:left;" class="width20 text_left">
                <select name="from_taxRecord">
                    <option value=""></option>
                    <?php echo $serieOption ?>
                </select>
            </div>
        </div>
        <div class="width90 text_center pheight25" style="clear:both;">
            <div style="float:left;" class="width25 text_left">
                <input class="button_azzurro pwidth150" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*getUtente(2);*/openOfcanvas('user_entry',2);">
            </div>
            <div style="float:left;" class="width40 text_center">
                <input type="text" id="to_surname" name="to_surname" class="width55" >
                <input type="text" id="to_name" name="to_name" class="width35">
            </div>
            <div style="float:left;" class="width15 text_left">A partita</div>
            <div style="float:left;" class="width20 text_left">
                <select name="to_taxRecord">
                    <option value=""></option>
                    <?php echo $serieOption ?>
                </select>
            </div>
        </div>
        <div class="width90 text_center pheight20" style="clear:both;">
            <div style="float:left;" class="width25 text_left"><span class="color_titolo font_bold">Anno riferimento partita</span></div>
            <div style="float:left;" class="width10 text_center">Da anno</div>
            <div style="float:left;" class="width10 text_center"><input name="from_taxYear" type="text" value="<?php echo $tax_year; ?>" class="width75 text_right"></div>
            <div style="float:left;" class="width10 text_center">Ad anno</div>
            <div style="float:left;" class="width10 text_center"><input name="to_taxYear" type="text" value="<?php echo $tax_year; ?>" class="width75 text_right"></div>
            <div style="float:left;" class="width15 text_left">Tipo Entrata</div>
            <div style="float:left;" class="width20 text_left">
                <select name=taxType class="width95">
                    <?= $cls_print->tax_firstOpt; ?>
                    <?= $optionsRiscossione; ?>
                </select>
            </div>
        </div>
        <div class="width90 text_center pheight5" style="clear:both;"><br></div>
        <div class="width90 text_center pheight8" style="clear:both;"><hr></div>
        <div class="width90 text_center pheight5" style="clear:both;"><br></div>

        <?php echo $cls_print->getFilters(); ?>

        <div class="width90 text_center pheight5" style="clear:both;"><br></div>
        <div class="width90 text_center pheight8" style="clear:both;"><hr></div>

    </form>

<?php include(INC."/footer.php"); ?>