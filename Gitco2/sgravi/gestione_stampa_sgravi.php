<?php

require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_paramUtils.php");
include_once CLS ."/cls_DateTime.php";

$cls_param = new cls_param();

$cognome_ditta = $cls_help->getVar("cognome_ditta");
$nome = $cls_help->getVar("nome");
$genere = $cls_help->getVar("genere");
$comune_id = $cls_help->getVar("comune_id");
$partita_id = $cls_help->getVar("partita_id");
$pageCalled = $cls_help->getVar("pageCalled");
$dataFor = $cls_help->getVar("data_fornitura");

//$cls_help->alert($nome);

if($dataFor != null) {
    $dateComplete = new cls_DateTime($dataFor,"DB");
    $dataFor = $dateComplete->GetYear();
}


$selectSgravio = "";
$selectAnnull = "";

if($pageCalled == "sgravi" || $pageCalled == "sgravi_1") $selectSgravio = "selected";
else if($pageCalled == "annullamento" || $pageCalled == "annullamento_1") $selectAnnull = "selected";

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++) {
    $sel = "";
    if($comune_id == $resIngiunzioni[$i]['Comune_ID']) $sel = "selected";
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "' ".$sel.">" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";
}


/*foreach (new DirectoryIterator(XLSSGRAVI."/".$c) as $file) {
    if ($file->isFile()) {
        //print $file->getFilename() . "\n";
        echo "<option value='".$file->getFilename()."'>".$file->getFilename()."</option>";
    }
}*/

?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="elenco_sgravi.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        var GD = $("#genere_da").val();
        var GA = $("#genere_a").val();

        if($("#genere_da").val()!=""||$("#genere_a").val()!="")
        {
            if(($("#genere_da").val()=="D" && ($("#genere_a").val()=="F" || $("#genere_a").val()=="M")) || ($("#genere_a").val()=="D" && ($("#genere_da").val()=="F" || $("#genere_da").val()=="M"))){
                alert("Non selezionare ditte e utenti insieme nella selezione utenti!! (Da Cognome/Nome - A Cognome/Nome). Selezionare entrambe imprese o entrambi utenti!");
                return false;
            }
        }
        if($("#sgravio_annull").val()==""){
            alert("Selezionare discarico o annullamento!");
            return false;
        }
        if(validateForm())
            ajaxCall();
            //$("#sgravio_annull_form").submit();
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Manuale_Operatore_Discarichi.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Manuale operatore — Discarichi Art. 19</b>");
        $("#helpModal").modal('show');
    }

    function callBack(){
        location.href = "<?= WEB_ROOT; ?>/coattiva/annulamento_sgravi.php?c=<?= $c; ?>&a=<?= $a; ?>&partita=<?= $partita_id; ?>&pageCalled=<?= $cls_help->getVar('page_called'); ?>"
    }

    $(document).ready(function(){
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
        //if(getParameterByName("file") != null)
            //showFileOnModal(getParameterByName("file"),"Storico Azioni",getParameterByName("file").split('.').pop());
    });

    function ajaxCall() {
		spinner.startSpinner();
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
                    showFileOnModal(resp.path,"Discarichi",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
	}
</script>

<form id="sgravi_form" name="sgravi_form" action="stampa_sgravi.php" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />
    <input type=hidden name="partita_id" value="<?php echo $partita_id ?>" />
    <input type=hidden name="genere_da" id="genere_da" value="<?= $genere; ?>" />
    <input type=hidden name="genere_a" id="genere_a" value="<?= $genere; ?>" />
    <input type=hidden name="page_called" id="page_called" value="<?= $pageCalled; ?>" />

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampa discarichi (Art. 19 D.Lgs. 112/99)</span>
        </div>
    </div>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Selezione atti</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-4 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno di fornitura da</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="ruolo_da" name="ruolo_da" value="<?= $dataFor; ?>" maxlength="4" tabindex=7>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-3 control-label resize" style="text-align: left;">a</label>
            <div class="form-group">
                <div class="col-lg-9">
                    <input class="form-control resize" type="text" id="ruolo_a" name="ruolo_a" value="<?= $dataFor; ?>" maxlength="4" tabindex=8>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize" type="button" value="Da Debitore" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_sel',1);" tabindex=4>
            </div>
        </div>
        <div class="col col-lg-3" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input readonly class="form-control resize" type="text" id="daco" name="daco" value="<?= $cls_help->getVar("daco")!=null?$cls_help->getVar("daco"):$cognome_ditta; ?>"  tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input readonly class="form-control resize" type="text" id="dano" name="dano" value="<?= $cls_help->getVar("dano")!=null?$cls_help->getVar("dano"):$nome; ?>" tabindex=6>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize" type="button" value="A Debitore" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_sel',2);" tabindex=7>
            </div>
        </div>
        <div class="col col-lg-3" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input readonly class="form-control resize" type="text" id="acog" name="acog" value="<?= $cls_help->getVar("acog")!=null?$cls_help->getVar("acog"):$cognome_ditta; ?>"  tabindex=7>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input readonly class="form-control resize" type="text" id="anom" name="anom" value="<?= $cls_help->getVar("anom")!=null?$cls_help->getVar("anom"):$nome; ?>"  tabindex=9>
                </div>
            </div>
        </div>
        <!--<div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data annullamento da</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker" type="text" id="annull_da" name="annull_da" value=""  tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-3 control-label resize" style="text-align: left;">a</label>
            <div class="form-group">
                <div class="col-lg-9">
                    <input class="form-control resize picker" type="text" id="annull_a" name="annull_a" value=""  tabindex=6>
                </div>
            </div>
        </div>-->
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="partita_da" name="partita_da" tabindex=11 class="form-control resize">
                        <option value=""></option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <!--<div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Da data notifica</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker" type="text" id="notifica_da" name="notifica_da" value=""  tabindex=5>
                </div>
            </div>
        </div>-->
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Da anno rif.</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="anno_rif_da" name="anno_rif_da" value="" maxlength="4" tabindex=5>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">A partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="partita_a" name="partita_a" tabindex=11 class="form-control resize">
                        <option value=""></option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <!--<div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">A data notifica</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker" type="text" id="notifica_a" name="notifica_a" value=""  tabindex=5>
                </div>
            </div>
        </div>-->
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">A anno rif.</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="anno_rif_a" name="anno_rif_a" value="" maxlength="4" tabindex=5>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row" style="margin-top: 2%;">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printType" name="printType" tabindex=9 class="form-control resize">
                        <option value="temp">Provvisoria</option>
                        <option value="final">Definitiva</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Stato stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printed" name="printed" tabindex=10 class="form-control resize">
                        <option value="no">Da stampare</option>
                        <option value="si">Stampati</option>
                        <option value="all">Tutti</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data Stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker validateCustom vld_Custom_r" type="text" id="data_stampa" name="data_stampa" value="" tabindex=5>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 2%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Descrizione Stampa</label>
                <div class="col-lg-10">
                    <textarea class="form-control resize" tabindex=11 id=note style="max-width: 100%;" name=note placeholder="es: Estrazione da partita 5 a 102 ..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <?php if($cls_help->getVar("visualizzaBtnRet") == "si" && $cls_help->getVar('page_called') != null){ ?>
            <div class="row">
                <div class="col-lg-offset-1 col-lg-2">
                    <button type="button" class="btn btn-primary" onclick="callBack();">Torna a Discarico/Annull</button>
                </div>
            </div>
    <!--<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>-->
    <?php }?>

</form>

<!-- Inclusione modale per ricerca utente-->
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
    // Iserimento dati da modale a pagine
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

    function callParent(valorediritorno){
        switch(selectParent){
            case "utente":

                if(valorediritorno!=null)
                {
                    $.post("<?=AJAXWEB;?>/ajax_cognome.php?c=<?php echo $c; ?>" ,

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
</script>

<?php include(INC."/footer.php"); ?>
