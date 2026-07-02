<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS . "/cls_CoazioneUtils.php";



if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$coaz = new cls_Coazione();

$query = "SELECT DISTINCT CC_Ufficio, Comune FROM ufficio_giudiziario WHERE Tipo = 'tribunale' ORDER BY Comune ASC";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$stringa = "";
for($i=0;$i<count($result);$i++)
{
    $stringa.= "<option value='".$result[$i]['CC_Ufficio']."'>";
    $stringa.= $result[$i]['Comune']." - ".$result[$i]['CC_Ufficio'];
    $stringa.= "</option>";
}

$lista_tribunali = $stringa;

//$tribunale = new ufficio_giudiziario(null, null);
//$lista_tribunali = $tribunale->lista_tribunali('options');

$tipo_atto = $cls_help->getVar('tipo_atto');
$option_elenco = "<option value=\"generale\">Generale</option>";
$selectTrafficLaw="";
switch($tipo_atto)
{
    case "Ingiunzione":			
        $action_page = "elenco_ingiunzioni.php";
        $tipo_atto = "Ingiunzione";
        $next_page = "sollecito";
        $prev_page = "preavviso_ing";
        $option_elenco.= "<option value=spese_notifica>Distinta per spese di notifica</option>";
        $option_elenco.= "<option value=spese_postali>Distinta per spese postali</option>";
        $selectTrafficLaw = "<select class='width100' name='TrafficLaw'><option value='0'></option><option>SOLO CDS</option></select>";

        break;

    case "sollecito":			
        $action_page = "elenco_solleciti_ingiunzione.php";
        $tipo_atto = "Sollecito di pagamento";
        $next_page = "avv_intimazione";
        $prev_page = "Ingiunzione";
        break;

    case "avv_intimazione":		
        $action_page = "elenco_avvisi.php";
        $tipo_atto = "Avviso di intimazione ad adempiere";
        $next_page = "preavviso_ing";
        $prev_page = "sollecito";
        break;

    case "preavviso_ing":		
        $action_page = "elenco_preavvisi_ingiunzione.php";
        $tipo_atto = "Preavviso di Ingiunzione";
        $next_page = "Ingiunzione";
        $prev_page = "avv_intimazione";
        $selectedDaStampare = " selected ";
        break;
}

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));
for ($i=0; $i < count($resIngiunzioni); $i++)
{
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";
}

$parametri_notifica = (object) $coaz->array_notifica();//new parametri_notifica(null);
//$parametri_notifica->array_notifica();

$options_stati = options_select_array($parametri_notifica->Stati);
$options_motivi = options_select_array($parametri_notifica->Motivi);
$options_a_mani = options_select_array($parametri_notifica->Mode_A_Mani, "Descrizione" , "Articolo");
$options_per_posta = options_select_array($parametri_notifica->Mode_Per_Posta, "Descrizione" , "Articolo");
$options_eccezionali = options_select_array($parametri_notifica->Mode_Eccezionali, "Descrizione" , "Articolo");


function options_select_array ( $array , $campo = "Descrizione" , $campo_trailer = null )
{
    $options = "";
    for($i=0;$i<count($array);$i++)
    {
        $options.= "<option value='".$array[$i]['ID']."'>".$array[$i][$campo];

        if($campo_trailer!=null)
            $options.= " - ".$array[$i][$campo_trailer];

        $options.= "</option>";
    }

    return $options;
}

?>


    <!-- ********** VARIABILI ********** -->
    <script>
        var tipo_atto = "<?php echo $tipo_atto ?>";
        var atto_val = null;
        var atto_link = null;
        if( tipo_atto == "Ingiunzione")
        {
            atto_val = tipo_atto;
            atto_link = "ingiunzioni";

        }
        else if( tipo_atto == "Sollecito di pagamento")
        {
            atto_val = "sollecito";
            atto_link = "solleciti_ingiunzione";
        }
        else if( tipo_atto == "Avviso di intimazione ad adempiere")
        {
            atto_val = "avv_intimazione";
            atto_link = "avvisi";
        }
        else if( tipo_atto == "Preavviso di Ingiunzione")
        {
            atto_val = "preavviso_ing";
            atto_link = "preavvisi_ingiunzione";
        }

    </script>

    <!-- ********** GESTIONE LINK MENU ********** -->
    <script>

        //F5
        switchMenuImg("F5");
        F5_button = function()
        {
            location.href="elenco_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+atto_val;
        }

        //PAG GIU
        switchMenuImg("pagedown");
        pagedown_button = function(){
            pagina_menu('prev');
        }

        //PAG SU
        switchMenuImg("pageup");
        pageup_button = function(){
            pagina_menu('suc');
        }

        //F10
        switchMenuImg("F10");
        F10_button = function(){
            ajaxCall();
            //$('#stampa_form').submit();
        }

        //F11-F12 sono nel menu'


        //******************************\\
        //ALTRI LINK / FUNZIONI CHIAMATE\\

        //CAMBIO PAGINA
        function pagina_menu (value)
        {

            if (value == 'suc')
            {
                cambio_pagina = "<?php echo $next_page; ?>";
            }
            else if (value == 'prev')
            {
                cambio_pagina = "<?php echo $prev_page; ?>";
            }

            link = "elenco_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+cambio_pagina;

            top.location.href = link;

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

        function insert_notif()
        {
            $('#a_notif').val( $('#da_notif').val() );
        }

        function insert_stampa()
        {
            $('#a_stampa').val( $('#da_stampa').val() );
        }

        function insert_elab()
        {
            $('#a_elab').val( $('#da_elab').val() );
        }

        function insert_anno()
        {
            $('#ad_anno').val( $('#da_anno').val() );
        }

        function control_indirizzo(value)
        {
            giacenza = $('#giacenza').val();
            ind_validato = $('#indirizzo_validato').val();

            if(value==1)
            {
                if(giacenza == "Nessuno")	$('#indirizzo_validato').val('');
                if(giacenza == "")			$('#indirizzo_validato').val('');
            }
            else if(value==2)
            {
                if(ind_validato != "" && giacenza == "" ) $('#giacenza').val('Tutti');
            }

        }

        function primoIndex()
        {
            $('[=1]').focus();
        }

        function change_elenco()
        {
            tipo_elenco = $('#tipo_elenco').val();

            if(tipo_elenco == "tribunale")
            {
                $('#salta').val('tribunale');
                $('#ordinamento').val('tribunale');
                $('#tipo_ufficiale').val('giudiziario');
                $('#stampa_form').attr("action","elenco_"+atto_link+".php");
            }
            else if(tipo_elenco == "spese_notifica")
            {
                $('#salta').val('tribunale');
                $('#ordinamento').val('tribunale');
                $('#tipo_ufficiale').val('giudiziario');
                $('#stampa_form').attr("action","elenco_"+atto_link+"_spese_notifica.php");
                alert("ATTENZIONE! Le posizioni senza collegamento tra comune di residenza e tribunale non verranno visualizzate nell'elenco");
            }
            else if(tipo_elenco == "spese_postali")
            {
                $('#salta').val('');
                $('#tipo_ufficiale').val('');
                $('#ordinamento').val('progressivo');
                $('#stampa_form').attr("action","elenco_"+atto_link+"_spese_postali.php");
            }
            else
            {
                $('#stampa_form').attr("action","elenco_"+atto_link+".php");
                $('#salta').val('');
            }
        }

        function change_ordinamento()
        {
            change_salta_pagina();
        }

        function change_salta_pagina()
        {
            change_elenco();

            salta_pagina = $('#salta').val();

            if(salta_pagina=="tribunale")
                $('#ordinamento').val('tribunale');
        }

        function change_ufficiale()
        {
            change_elenco();
        }
    </script>

    <!-- ********** AJAX / MODALI ********** -->

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
        // Iserimento dati da modale a pagine
        function initialId(type,val){
            switch (type){
                case 'user':
                case 'cf':
                case "info":
                case "entry":
                case "fore":
                    $("#genere").val(val['Genere']);                            // setta genere utente (M, F, D)
                    if(selectRif == 1)                                          // "Da Cognome/Nome"
                    {
                        //alert("qui 1");
                        if(val['Ditta_F'] != '' && val['Ditta_F'] != null){     // è una ditta
                            $('#daco').val(val['Ditta_F']);
                            $('#acog').val(val['Ditta_F']);
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
                        if(val['Ditta_F'] != '' && val['Ditta_F'] != null){     // è una ditta
                            $('#acog').val(val['Ditta_F']);
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
        function Dim_Alert ( sWidth, sHeight )
        {
            setupPagina = "dialogWidth:" + sWidth + "px";
            setupPagina += "; dialogHeight:" + sHeight + "px";
            setupPagina += ";dialogLeft:80px;dialogTop:80px;";

            return setupPagina;
        }

        function callParent(valorediritorno){
            switch(selectParent){
                case "utente":

                    if(valorediritorno!=null)
                    {
                        $.post("ajax/ajax_stampe.php?c=<?php echo $c; ?>" ,

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

                                if(array_ritorno.length == 2)
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
                                }
                                else
                                {
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

    <!-- ********** SUBMIT(stampa) ********** -->
    <script>
        var tipo_atto = "<?=$tipo_atto;?>";
        var titolo = "";

        $(document).ready(function(){
            //$("#stampa_click").click( stampa_F10 );
            spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
        });

        function ajaxCall() {
		spinner.startSpinner();
        switch(tipo_atto)
        {
            case "Ingiunzione":			
                titolo = "Ingiunzioni";
                break;
            case "sollecito":			
                titolo = "Solleciti di pagamento";
                break;
            case "avv_intimazione":		
                titolo = "Avvisi di intimazione ad adempiere";
                break;
            case "preavviso_ing":		
                titolo = "Preavvisi di ingiunzione";
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

    </script>

            <table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
                <tr>
                    <td><font class="titolo font16 under_decor">Gestione stampe</font></td>
                </tr>
            </table>

            <form id="stampa_form" name="stampa_form" action="<?php echo $action_page; ?>" method="post" target="stampa" onSubmit="window.open('', 'stampa', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')">

                <input type=hidden name="c" value="<?php echo $c ?>">
                <input type=hidden name="a" value="<?php echo $a ?>">

                <table class="table_interna text_center" border="0">
                    <tr>
                        <td colspan=4 class="pheight5"></td>
                    </tr>
                    <tr>
                        <td class="text_left width15">Tipo di atto</td>
                        <td class="text_left width40">
                            <input name=tipo_atto readonly class="text_left sfondo_ricerca" value="<?php echo $tipo_atto; ?>" size=35 >
                        </td>
                        <td class="width20 text_left">Tipo di stampa</td>
                        <td class="width25 text_left"><input name=tipo_stampa readonly class="text_left sfondo_ricerca" value="Elenco" size=15 ></td>
                    </tr>
                    <tr>
                        <td colspan=4 class="pheight5"></td>
                    </tr>
                </table>

                <table class="table_interna text_center" border="0">
                    <tr>
                        <td colspan=4 class="text_center"><font class="titolo font16 under_decor">Selezione</font></td>
                    </tr>
                    <tr>
                        <td colspan=4 class="pheight5"><hr></td>
                    </tr>
                    <tr>
                        <td class="width25 text_left">
                            <input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_entry',1);">
                        </td>
                        <td class="width50 text_left">
                            <input type="text" id="daco" name="daco" size=25  >
                            <input type="text" id="dano" name="dano" size=15  >
                        </td>
                        <td class="width15 text_left">Da partita</td>
                        <td class="width10 text_left">
                            <select name="da_n_elenco" >
                                <option value=""></option>
                                <?php echo $serieOption ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="text_left">
                            <input class="button_azzurro pwidth150" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_entry',2);">
                        </td>
                        <td class="text_left">
                            <input type="text" id="acog" name="acog" size=25  >
                            <input type="text" id="anom" name="anom" size=15  >
                        </td>
                        <td class="text_left">a partita</td>
                        <td class="text_left">
                            <select name="a_n_elenco" >
                                <option value=""></option>
                                <?php echo $serieOption ?>
                            </select>
                        </td>
                    </tr>

                </table>

                <table class="table_interna text_center" border="0">
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Anni di riferimento</font></td>
                        <td class="width10 text_center">Da anno</td>
                        <td class="width10 text_left"><input type="text" class="text_right" name="da_anno" id="da_anno" value="<?php echo $a; ?>" onchange="insert_anno();" size=5  ></td>
                        <td class="width10 text_center">ad anno </td>
                        <td class="width10 text_left"><input type="text" class="text_right" name="ad_anno" id="ad_anno" value="<?php echo $a; ?>" size=5  ></td>
                        <td class="width35 text_right" colspan=4>
                            Tipo Entrata&nbsp;
                            <select name=tipo_partita class="width50">
                                <option></option>
                                <option>CDS</option>
                                <option>IMMOBILI</option>
                                <option>IRPEF</option>
                                <option>OSAP</option>
                                <option>PATRIMONIALE</option>
                                <option value="PUBBLICITA">PUBBLICITA'</option>
                                <option>RIFIUTI</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=9><hr></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Tipo elenco</font></td>
                        <td class="width40 text_left" colspan=4>
                            <select class="width95" name=tipo_elenco id=tipo_elenco onchange="change_elenco()">
                                <?php echo $option_elenco; ?>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4>
                            <input type=checkbox name="ultimo_atto" value="si" checked>Stampare solo ultimo atto per partita</input>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=9><hr></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Data elaborazione</font></td>
                        <td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_elab" id="da_elab" value="" onchange="insert_elab();" size=9  ></td>
                        <td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_elab" id="a_elab" value="" size=9  ></td>
                        <td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_elab" id="data_elab" value="assente" ></td>
                        <td class="width15 text_left" colspan=2></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Data stampa</font></td>
                        <td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_stampa" id="da_stampa" value="" onchange="insert_stampa();" size=9  ></td>
                        <td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_stampa" id="a_stampa" value="" size=9  ></td>
                        <td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_stampa" id="data_stampa" value="assente" ></td>
                        <td class="width15 text_left" colspan=2></td>
                    </tr>
                    <?php
                    if($selectTrafficLaw!=""){
                        ?>

                        <tr>
                            <td class="text_left width25"><font class="color_titolo font_bold">Importate da CDS Gitco</font></td>
                            <td class="width40 text_center" colspan=4>
                                <?= $selectTrafficLaw; ?>
                            </td>
                            <td class="width35 text_left" colspan=4></td>
                        </tr>

                        <?php
                    }?>

                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Stato stampa</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="stato_stampa" class="width100">
                                <option ></option>
                                <option id=stampa_1>Da stampare</option>
                                <option id=stampa_2>Stampato</option>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Ufficiale</font></td>
                        <td class="width40 text_left" colspan=4>
                            <select id=tipo_ufficiale name="tipo_ufficiale" class="width100" onchange="change_ufficiale()">
                                <option></option>
                                <option value="riscossione">Ufficiale della Riscossione</option>
                                <option value="giudiziario">Ufficiale Giudiziario</option>
                            </select>
                        </td>
                        <td class="width35 text_center" colspan=3></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Data notifica</font></td>
                        <td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_notif" id="da_notif" value="" onchange="insert_notif();" size=9  ></td>
                        <td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_notif" id="a_notif" value="" size=9  ></td>
                        <td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_notif" id="data_notif" value="assente" ></td>
                        <td class="width15 text_left" colspan=2></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Modalita' notifica</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="modalita" class="width100">
                                <option></option>
                                <option>Nessuna</option>
                                <option>Tutte</option>
                                <optgroup label="Tramite soggetto preposto"><?php echo $options_a_mani; ?></optgroup>
                                <optgroup label="Per posta"><?php echo $options_per_posta; ?></optgroup>
                                <optgroup label="Eccezionali"><?php echo $options_eccezionali; ?></optgroup>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Stato giacenza</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="giacenza" id="giacenza" class="width100" onchange="control_indirizzo('1');">
                                <option></option>
                                <option>Nessuno</option>
                                <option>Tutti</option>
                                <?php echo $options_stati; ?>
                            </select>
                        </td>
                        <td class="width10 text_right"><font class="color_titolo font_bold">Indirizzo</font></td>
                        <td class="width20 text_center" colspan=3>
                            <select id="indirizzo_validato" name="indirizzo_validato" class="width100" onchange="control_indirizzo('2');">
                                <option></option>
                                <option value="attesa">In attesa di validazione</option>
                                <option>Validato</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Anomalie notifica</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="anomalie" class="width100">
                                <option></option>
                                <option>Nessuna</option>
                                <option>Tutte</option>
                                <?php echo $options_motivi; ?>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Stato pagamenti</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="pagamento" class="width100">
                                <option></option>
                                <option>Nessuno</option>
                                <option>Parziale</option>
                                <option value="Nessuno_Parziale">Nessuno e parziale</option>
                                <option>Totale</option>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Rateizzazione</font></td>
                        <td class="width20 text_center" colspan=2>
                            <select name="rateizzazione" class="width100">
                                <option></option>
                                <option>Si</option>
                                <option>No</option>
                            </select>
                        </td>
                        <td class="text_left" colspan=6></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Flag rielaborazione</font></td>
                        <td class="width20 text_center" colspan=2>
                            <select name="rielaborazione" class="width100">
                                <option></option>
                                <option>Si</option>
                                <option>No</option>
                            </select>
                        </td>
                        <td colspan=6></td>
                    </tr>
                    <tr>
                        <td colspan=9><hr></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Salta pagina</font></td>
                        <td class="width40 text_left" colspan=4>
                            <select id=salta name=salta class="width100" onchange="change_salta_pagina();">
                                <option></option>
                                <option value=tribunale>Ogni cambio Tribunale</option>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Tribunale di competenza</font></td>
                        <td class="width40 text_left" colspan=4>
                            <select id=tribunale name=tribunale class="width100">
                                <option></option>
                                <?php echo $lista_tribunali; ?>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Ordinamento</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select id=ordinamento name=ordinamento class="width100" onchange="change_ordinamento()">
                                <option value=progressivo>Partita</option>
                                <option value=info>Informazioni cartella</option>
                                <option value=alfabetico>Alfabetico</option>
                                <option value=verbale>Numero verbale ( solo CDS )</option>
                                <option value=tribunale>Tribunale</option>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Blocco atto singolo</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="blockSingleAct" class="width100">
                                <option value="no">No</option>
                                <option value="si">Si</option>
                                <option value="">Entrambi</option>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Blocco coazione</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="blocco" class="width100">
                                <option>No</option>
                                <option>Si</option>
                                <option>Entrambi</option>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>
                    <tr>
                        <td class="text_left width25"><font class="color_titolo font_bold">Discarico</font></td>
                        <td class="width40 text_center" colspan=4>
                            <select name="dischargeFlag" class="width100">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                                <option value="">Entrambi</option>
                            </select>
                        </td>
                        <td class="width35 text_left" colspan=4></td>
                    </tr>

                    <tr>
                        <td colspan=9><hr></td>
                    </tr>
                    <tr>
                        <td colspan=9 class="text_left"></td>
                    </tr>
                </table>

                <br>

            </form>

        </td>
    </tr>
</table>

<?php include_once INC."/footer.php"; ?>