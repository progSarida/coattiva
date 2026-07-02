<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_print.php");
include_once(CLS."/cls_ruolo.php");
include_once(CLS."/cls_html.php");
//include_once(CLS."/cls_notParameters.php");

//print or list
$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");
$page = $cls_help->getVar("page");

//$AnnoDa = $cls_help->getVar("annoDa");
//$AnnoA = $cls_help->getVar("annoA");

if($page == "visura"){
  $title = "Visura Massiva";
  $action = "elaborazione_visura_massiva.php";
  $buttonText = "Visura veicoli";
}else if($page == "visualizza"){
  $title = "Elenco veicoli";
  $action = "elenco_visura_massiva.php";
  $buttonText = "Veicoli utenti";
}

$data_elab_visual = date('d/m/Y');
$disabled = "";

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++)
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";


//$cls_help->alert($a_enteAdmin["Select_Tax"]);
?>

<!-- Inclusione modale per ricerca utente-partita -->
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
                    if(rif == 2 && $('#daco').val() == '')
                        alert("Inserire prima l'utente da cui far partire la ricerca");
                    else
                        $('#userEntrySearchModal').modal('show');
                    break;
            }
        }

        function initialId(type,val){
            switch (type){
                case 'user':
                case 'cf':
                case "info":
                case "entry":
                case "fore":
                    /*
                    $("#genere").val(val['Genere']);                            // setta genere utente (M, F, D)
                    if(lock == 'N')                                             // se non è già stato lockato
                        lock = val['Genere'];                                   // blocca la ricerca del secondo input sul tipo del primo
                     */
                    if(selectRif == 1)                                          // "Da Cognome/Nome"
                    {
                        //alert("qui 1");
                        if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
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
                        if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
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
            location.href="visura_massiva.php?printType=procedure&c=<?php echo $c; ?>&a=<?php echo $a; ?>&page=<?= $page; ?>";
        }

        //F10
        switchMenuImg("F10");
        F10_button = function(){
            $('#btnSub').trigger("click");
        }

        switchMenuImg("F11");
        F11_button = function(){

            $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/VisuraVeicoliMassiva.pdf"; ?>");
            $("#helpModalLabel").empty().append("<b>Help Visura veicoli</b>");
            $("#helpModal").modal('show');

        }

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
                                    }
                                    else if(selectRif==2)
                                    {
                                        $('#anom').val(array_ritorno[1]);
                                    }

                                    $("#genere").val(array_ritorno[2]);
                                }
                                else
                                {
                                    if(array_ritorno.length == 2) $("#genere").val(array_ritorno[1]);
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
                    var stringa = "<?= WEB_ROOT; ?>/coattiva/modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    //valorediritorno = window.showModalDialog(stringa,"", strDim);
                    openWindowSearch(stringa,{width:800, height:500, left:(($(window).width()/2)-400), top:(($(window).height()/2)-250)});

                    break;
            }
        }

    </script>


    <div style="width: 100%;text-align: center;"><span class="titolo font16 under_decor"><?= $title; ?></span></div>
    <br>

    <form id="visura_form" name="visura_form" action="<?= $action; ?>" method="post" target="elabora" onSubmit="window.open('', 'elabora', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no');">
        <input type=hidden name="c" value="<?php echo $c ?>">
        <input type=hidden name="a" value="<?php echo $a ?>">
        <input type=hidden name="genere" id="genere" value="">


        <div class="row">
            <div class="col col-lg-4 col-lg-offset-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di atto</label>
                    <div class="col-lg-8">
                        <input name=tipo_atto readonly style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="text_left form-control resize" value="Visura ACI-PRA" size=35 tabindex=1>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-md-center " style="margin: 2%;">
            <div class="col col-md-auto text_center">
                <p>
                  <b>ATTENZIONE:</b> La richiesta che viene inviata all'ACI-PRA è relativa alle sole ingiunzioni di pagamento o avvisi di intimazione ad adempiere regolarmente notificati rispettivamente da oltre 30 giorni e da meno di 365 (variabile in base ai parametri impostati) e da oltre 5 e da meno di 180 giorni (variabile in base ai parametri impostati), non pagati per un importo superiore a quello indicato nell'apposito campo parametrico relativo all'importo minimo annuale al di sotto del quale l'Ente non procede con la riscossione coattiva.Si rende pertanto necessario, prima di effettuare la richiesta, di inserire tutte le date e le relate di notifica e/o acquisire i relativi file e gestire la validazione degli indirizzi "[ ] Flag indirizzo validato", per tutte quelle le posizioni che presentano nella pagina della notifica la situazione "Emesso CAD e piego non ritirato dopo 10 giorni", nonchè effettuare la stampa delle ingiunzioni di pagamento e/o degli avvisi di intimazione ad adempiere emessi da oltre 30 giorni e risultanti non ancora notificati, al fine di porli in notifica e farli rientrare nel ciclo di acquisizione dei dati dei veicoli intestati ai debitori.
                </p>
            </div>
        </div>
        <div class="row justify-content-md-center " style="margin: 2%;">
            <div class="col col-md-auto text_center">
                <span class="titolo font16 under_decor">Selezione</span>
            </div>
        </div>
        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize" style="text-align: left;">Data elaborazione</label>
                    <div class="col-lg-8">
                        <input name="data_elab" id="data_elab" size=9 style="width: 50%;" class="text_center picker form-control resize" value="<?php echo $data_elab_visual; ?>" tabindex=2>
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <div class="form-group">
                    <label class="col-lg-5 control-label resize" style="text-align: left;">Giorni minimi nuova visura</label>
                    <div class="col-lg-7">
                        <input name="min_day" id="min_day" size=9 style="width: 50%;" class="text_center form-control resize" value="0" tabindex=2>
                    </div>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

        <div class="row">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <input class="btn btn-primary form-control resize pwidth150 " style="width: 65%;" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_entry',1);lock='N';" tabindex=4>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input class="form-control resize" type="text" id="daco" name="daco" size=25  tabindex=5>
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input class="form-control resize" type="text" id="dano" name="dano" size=15  tabindex=6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <input class="btn btn-primary form-control resize pwidth150" style="width: 65%;" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_entry',2);" tabindex=7>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input class="form-control resize" type="text" id="acog" name="acog" size=25  tabindex=7>
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input class="form-control resize" type="text" id="anom" name="anom" size=15  tabindex=9>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo Entrata</label>
                <div class="form-group">
                    <div class="col-lg-8">
                        <select name=tipo_partita id=tipo_partita class="form-control resize" tabindex=9>
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
            <div class="col col-lg-5 col-lg-offset-1">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di Spedizione</label>
                <div class="form-group">
                    <div class="col-lg-8">
                        <select name=modalita_stampa id=modalita_stampa class="form-control resize" tabindex=9>
                            <option value=""></option>
                            <option value="posta">Raccomandata A.G.</option>
                            <option value="mani">A mani</option>
                            <option value="ordinaria" checked>Posta ordinaria</option>
                        </select>
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
                <label class="col-lg-6 control-label resize" style="text-align: left;">Da data di notifica</label>
                <div class="form-group">
                    <div class="col-lg-6">
                        <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="da_data" id="da_data" value="" onchange="insert_a_data();" size=9  tabindex=13>
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Da anno</label>
                <div class="form-group">
                    <div class="col-lg-4">
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
            <div class="col col-lg-4">
                <label class="col-lg-6 control-label resize" style="text-align: left;">A data di notifica</label>
                <div class="form-group">
                    <div class="col-lg-6">
                        <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="a_data" id="a_data" value="" size=9  tabindex=14>
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Ad anno</label>
                <div class="form-group">
                    <div class="col-lg-4">
                        <input type="text" class="form-control resize" id="ad_anno" name="ad_anno" value="" size=3 tabindex=16 onblur="focusIndex();">
                    </div>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

        <table class="table_interna text_center" border="0">

            <tr>
                <td colspan=6><hr></td>
            </tr>
            <?php if($_SESSION['username']=="mirkop"){
                ?>
                <tr>
                    <td colspan=6>
                        <select name="ingiunzione290">
                            <option></option>
                            <option value="y">ELABORA INGIUNZIONI GIA USCITE</option>
                        </select>
                    </td>
                </tr>
                <?php
            }?>

        </table>


        <div class="row" style="padding-bottom: 2%;">
            <div class="col col-lg-2 col-lg-offset-1">
                <div class="form-group">
                    <button class="btn btn-primary form-control resize" type=submit id=esegui_visura name=esegui_visura tabindex=1><?= $buttonText; ?></button>
                </div>
            </div>
        </div>
        <button style="display: none;" type="submit" id="btnSub"></button>
    </form>

<?php include(INC."/footer.php"); ?>
