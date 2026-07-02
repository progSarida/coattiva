<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC . "/header.php");
include(INC . "/menu.php");

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$a_act = $cls_db->getResults($cls_db->SelectQuery($queryIngiunzioni));
$a_selection = array("value"=>"Comune_ID","firstOpt"=>1,"selected"=>null,"text"=>array("[Comune_ID]"));
$serieOption = $cls_html->getOptions($a_act,$a_selection);

/*$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = mysql_query($queryIngiunzioni);
while ($rigaIngiunzioni = mysql_fetch_assoc($resIngiunzioni))
    $serieOption .= "<option value='" . $rigaIngiunzioni['Comune_ID'] . "'>" . $rigaIngiunzioni['Comune_ID'] . "</option>";*/


?>


    <!-- ********** GESTIONE LINK MENU ********** -->
    <script>

        //F5
        switchMenuImg("F5");
        F5_button = function()
        {
            //location.href="gestione_elenco_posizioni.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            location.href="gestione_PEC.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        }

        //F10
        switchMenuImg("F10");
        F10_button = function()
        {
            $('#elenco_form').submit();
        }

        //F11-F12 sono nel menu'


    <!-- ********** CALENDARIO ********** -->

        $(function() {

            $( ".picker" ).datepicker();

        });

    </script>

    <!-- ********** AGGIORNAMENTO PAGINA ********** -->
    <script>
        function insert_anno()
        {
            $('#ad_anno').val( $('#da_anno').val() );
        }

        function primoIndex()
        {
            $('[tabindex=1]').focus();
        }
    </script>

    <!-- Inclusione modale per ricerca utente/partita -->
    <?php include_once (ROOT."/search_modal/offcanvas/user_entry_offcanvas.php"); ?>

    <!-- ********** AJAX / MODALI ********** -->
    <script>
        // Variabili
        var selectRif = '';
        // Modali offcanvas
        function openOfcanvas(type,rif){
            // Reset campi input
            $('#desc').val("");
            $('#year').val("");
            $('#name').val("");
            $('#cf').val("");
            $('#ricDesc').val("");
            $('#ricCode').val("");
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
            //console.log(val);
            //alert(selectRif);
            switch (type){
                case 'user':
                case 'cf':
                case "info":
                case "entry":
                case "fore":
                    //alert(val['Ins']);

                    if(selectRif == 1)                                          // "Da Cognome/Nome"
                    {
                        //alert("qui 1");
                        if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
                            $('#daco').val(val['Ditta']);
                            $('#acog').val(val['Ditta']);
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
                        } else{                                                 // è una persona
                            $('#acog').val(val['Cognome']);
                            $('#anom').val(val['Nome']);
                        }
                    }
                    break;
            }
        }

        var rif = -1;

        function Dim_Alert ( sWidth, sHeight )
        {
            setupPagina = "dialogWidth:" + sWidth + "px";
            setupPagina += "; dialogHeight:" + sHeight + "px";
            setupPagina += ";dialogLeft:80px;dialogTop:80px;";

            return setupPagina;
        }

        function callParent(value)
        {
            if(value!=null)
            {
                $.post("<?= WEB_ROOT; ?>/search/stampe/ajax_stampe.php?c=<?php echo $c; ?>" ,

                    { 'ajax': 'nome' ,
                        'ID': value },

                    function (value) {

                        var array_ritorno = value.split('*');
                        console.log(array_ritorno);
                        if(rif==1)
                        {
                            $('#daco').val(array_ritorno[0]);
                            $('#acog').val(array_ritorno[0]);
                        }
                        else if(rif==2)
                        {
                            $('#acog').val(array_ritorno[0]);
                        }

                        if(array_ritorno.length == 2)
                        {
                            if(rif==1)
                            {
                                $('#dano').val(array_ritorno[1]);
                                $('#anom').val(array_ritorno[1]);
                            }
                            else if(rif==2)
                            {
                                $('#anom').val(array_ritorno[1]);
                            }
                        }
                        else
                        {
                            if(rif==1)
                            {
                                $('#dano').val("");
                                $('#anom').val("");
                            }
                            else if(rif==2)
                            {
                                $('#anom').val("");
                            }
                        }
                    });
            }
        }

        function RicercheDaId (value, riff)
        {
            var valorediritorno = 0;
            var strDim = Dim_Alert(600, 300);

            rif = riff;
            switch(value)
            {
                case "utente":

                    strDim = Dim_Alert(800, 500);
                    var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    window.showModalDialog(stringa,"", strDim);

                    break;
            }
        }

        function controlloRicevute(){
            var paginaRicevute = "controlla_mail.php?c=<?php echo $c;?>";
            window.open(paginaRicevute, 'ricevute', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no');
            alert('stop');
        }

    </script>

    <!-- ********** SUBMIT(stampa) ********** -->
    <script>

        $(document).ready(function(){

            //$("#stampa_click").click( stampa_F10 );

        });

        blocca_modifica = 1;
    </script>

    <form id="elenco_form" name="elenco_form" action="elenco_PEC.php" method="post" target="elenco" onSubmit="window.open('', 'elenco', 'width=900,height=800,top=70,left=70,scrollbars=yes,menubar=no')">

        <input type=hidden name="c" value="<?php echo $c ?>">
        <input type=hidden name="a" value="<?php echo $a ?>">

        <div class="row justify-content-md-center ">
            <div class="col col-md-auto text_center">
                <span class="titolo font16 under_decor">Gestione PEC</span>
            </div>
        </div>

        <div class="row" style="margin-top: 2%;">
            <div class="col col-lg-4 col-lg-offset-4">
                <div class="form-group">
                    <input class="ctn btn-primary form-control resize" type="button" value="SCARICA RICEVUTE" title="SCARICA RICEVUTE" onclick="controlloRicevute();">
                </div>
            </div>
        </div>

        <div class="row justify-content-md-center " style="margin-top: 2%;">
            <div class="col col-md-auto text_center">
                <span class="titolo font16 under_decor">Selezione filtri di stampa</span>
            </div>
        </div>

        <div class="row" style="margin-top: 2%;">
            <div class="col col-lg-2 col-lg-offset-1">
                <div class="form-group">
                    <input class="btn btn-primary resize" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_entry',1);">
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <!--<label class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>-->
                    <div class="col-lg-12">
                        <input type="text" id="daco" name="daco" size=25 class="form-control resize" tabindex=2>
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <!--<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>-->
                    <div class="col-lg-12">
                        <input type="text" id="dano" name="dano" class="form-control resize" size=15 tabindex=3>
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize" style="text-align: left;">Da partita</label>
                    <div class="col-lg-8" >
                        <select name="da_n_elenco" class="form-control resize" tabindex=6>
                            <option value=""></option>
                            <?php echo $serieOption ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-2 col-lg-offset-1">
                <div class="form-group">
                    <input class="btn btn-primary resize" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_entry',2);">
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <!--<label class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>-->
                    <div class="col-lg-12">
                        <input type="text" id="acog" name="acog" size=25 class="form-control resize" tabindex=4>
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <!--<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>-->
                    <div class="col-lg-12">
                        <input type="text" id="anom" name="anom" size=15 class="form-control resize" tabindex=5>
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize" style="text-align: left;">A partita</label>
                    <div class="col-lg-8" >
                        <select name="a_n_elenco" class="form-control resize" tabindex=7>
                            <option value=""></option>
                            <?php echo $serieOption ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%; margin-top: 1%;"></div>

        <div class="row">
            <div class="col col-lg-3 col-lg-offset-1">
                <!--<label class="col-lg-4 control-label resize" style="text-align: left;">Provincia</label>-->
                <div class="col-lg-12" style="text-align: left;">
                    <span class="resize color_titolo font_bold" >Anni di riferimento</span>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-5 control-label resize" style="text-align: left;">Da anno</label>
                    <div class="col-lg-7">
                        <input type="text" class="form-control resize" name="da_anno" id="da_anno" value="<?php echo $a; ?>" onchange="insert_anno();" size=5  tabindex=8>
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-5 control-label resize" style="text-align: left;">Ad anno</label>
                    <div class="col-lg-7">
                        <input type="text" class="form-control resize" name="ad_anno" id="ad_anno" value="<?php echo $a; ?>" size=5  tabindex=9>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 2%;">
            <div class="col col-lg-8 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize color_titolo font_bold" style="text-align: left;">Tipo Entrata</label>
                    <div class="col-lg-8" >
                        <select name=tipo_partita class="form-control resize">
                            <option></option>
                            <option value="CDS">CDS/AMMINISTRATIVA</option>
                            <option>IMMOBILI</option>
                            <option>IRPEF</option>
                            <option>OSAP</option>
                            <option>PATRIMONIALE</option>
                            <option value="PUBBLICITA">PUBBLICITA'</option>
                            <option>RIFIUTI</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-8 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize color_titolo font_bold" style="text-align: left;">Stato Accettazione</label>
                    <div class="col-lg-8" >
                        <select name=accettazione class="form-control resize" tabindex=10>
                            <option></option>
                            <option value="attesa">Attesa</option>
                            <option value="ok">Ricevuta</option>
                            <option value="fallita">Fallita</option>
                            <option value="mancata">Mancata</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-8 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize color_titolo font_bold" style="text-align: left;">Stato Consegna</label>
                    <div class="col-lg-8" >
                        <select name=consegna class="form-control resize" tabindex=10>
                            <option></option>
                            <option value="attesa">Attesa</option>
                            <option value="ok">Ricevuta</option>
                            <option value="fallita">Fallita</option>
                            <option value="mancata">Mancata</option>
                            <option value="anomalia">Anomalia</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-8 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize color_titolo font_bold" style="text-align: left;">Ordinamento</label>
                    <div class="col-lg-8" >
                        <select name=ordinamento class="form-control resize" tabindex=10>
                            <option></option>
                            <option value=partita>Partita</option>
                            <option value=utente>Alfabetico</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?php include(INC."/footer.php"); ?>