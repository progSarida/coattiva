<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$tribunale = new ufficio_giudiziario(null, null);
$lista_tribunali = $tribunale->lista_tribunali('options');

$tipo_atto = get_var('tipo_atto');
$option_elenco = "<option value=\"generale\">Generale</option>";
$selectTrafficLaw="";
switch($tipo_atto)
{
    case "Ingiunzione":			$action_page = "elenco_ingiunzioni.php";
        $tipo_atto = "Ingiunzione";
        $next_page = "sollecito";
        $prev_page = "preavviso_ing";
        $option_elenco.= "<option value=spese_notifica>Distinta per spese di notifica</option>";
        $option_elenco.= "<option value=spese_postali>Distinta per spese postali</option>";
        $selectTrafficLaw = "<select class='width100' name='TrafficLaw'><option value='0'></option><option>SOLO CDS</option></select>";

        break;

    case "sollecito":			$action_page = "elenco_solleciti_ingiunzione.php";
        $tipo_atto = "Sollecito di pagamento";
        $next_page = "avv_intimazione";
        $prev_page = "Ingiunzione";
        break;

    case "avv_intimazione":		$action_page = "elenco_avvisi.php";
        $tipo_atto = "Avviso di intimazione ad adempiere";
        $next_page = "preavviso_ing";
        $prev_page = "sollecito";
        break;

    case "preavviso_ing":		$action_page = "elenco_preavvisi_ingiunzione.php";
        $tipo_atto = "Preavviso di Ingiunzione";
        $next_page = "Ingiunzione";
        $prev_page = "avv_intimazione";
        $selectedDaStampare = " selected ";
        break;
}

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = mysql_query($queryIngiunzioni);
while ($rigaIngiunzioni = mysql_fetch_assoc($resIngiunzioni))
{
    $serieOption .= "<option value='" . $rigaIngiunzioni['Comune_ID'] . "'>" . $rigaIngiunzioni['Comune_ID'] . "</option>";
}

$parametri_notifica = new parametri_notifica(null);
$parametri_notifica->array_notifica();

$options_stati = options_select_array($parametri_notifica->Stati);
$options_motivi = options_select_array($parametri_notifica->Motivi);
$options_a_mani = options_select_array($parametri_notifica->Mode_A_Mani, "Descrizione" , "Articolo");
$options_per_posta = options_select_array($parametri_notifica->Mode_Per_Posta, "Descrizione" , "Articolo");
$options_eccezionali = options_select_array($parametri_notifica->Mode_Eccezionali, "Descrizione" , "Articolo");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

    <title>Stampa atti</title>

    <link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
    <link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
    <style> .ui-datepicker { font-size:11px; } </style>

    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>


    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>


    <!-- ********** VARIABILI ********** -->
    <script>var tipo_atto = "<?php echo $tipo_atto ?>";
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

        //F2
        function cambia_F2()
        {
            return true;
        }

        //F3
        function salva_form()
        {
            return true;
        }

        //F4
        function cancella_form()
        {
            return true;
        }

        //F5
        function annulla()
        {
            location.href="elenco_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+atto_val;
        }

        //F6
        function nuovo_F6()
        {
            return true;
        }

        //F7-F8
        function cambia_pag(value)
        {
            return true;
        }

        //PAG GIU
        function pag_prec()
        {
            pagina_menu('prev');
        }

        //PAG SU
        function pag_suc()
        {
            pagina_menu('suc');
        }

        //F9
        function ricerca_F9()
        {
            return true;
        }

        //F10
        function stampa_F10()
        {
            $('#stampa_form').submit();
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
    <script>

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
                    var stringa = "/gitco2/coattiva/modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    valorediritorno = window.showModalDialog(stringa,"", strDim);

                    break;
            }
        }

    </script>

    <!-- ********** SUBMIT(stampa) ********** -->
    <script>

        $(document).ready(function(){

            $("#stampa_click").click( stampa_F10 );

        });

    </script>

<body class="sfondo_new_gitco" >

<table class="table_azzurra text_center" style="height:7%;">
    <tr>
        <td class="width1"><br></td>
        <td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
        <td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
        <td class="width1"><br></td>
    </tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
    <tr>
        <td valign=top>

            <?php include MENU . '/menu_generale.php'; ?>

            <script>
                blocca_modifica = 1;
            </script>

            <table class="table_interna text_center" border=0 cellspacing=4>
                <tr>
                    <td class="text_center width7">
                        <a onMouseover="title='Modifica'" href="#" onClick="">
                            <img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
                        </a>
                    </td>
                    <td class="text_center width7" >
                        <input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
                    </td>
                    <td class="text_center width7" >
                        <input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
                    </td>
                    <td class="text_center width7" >
                        <a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
                            <img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
                        </a>
                    </td>
                    <td class="text_center width7" >
                        <a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
                            <img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
                        </a>
                    </td>
                    <td class="text_center width7" >
                        <a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
                            <img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
                        </a>
                    </td>
                    <td class="text_center width7" >
                        <a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
                            <img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
                        </a>
                    </td>
                    <td class="text_center width7">
                        <a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente"></a>
                    </td>
                    <td class="text_center width7">
                        <a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo"></a>
                    </td>
                    <td class="text_center width11">

                    </td>
                    <td class="text_center width7">
                        <a href="#" id="stampa_click" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
                    </td>
                    <td class="text_center width3">

                    </td>
                    <td class="text_center width7" >
                        <a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
                            <img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
                        </a>
                    </td>
                    <td class="text_center width2"></td>
                    <td class="text_center width7">
                        <a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
                            <img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
                        </a>
                    </td>
                </tr>
            </table>

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
                            <input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',1);">
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
                            <input class="button_azzurro pwidth150" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',2);">
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

</body>
</html>