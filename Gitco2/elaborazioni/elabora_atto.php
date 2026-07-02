<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_DateTime.php");
//include_once(CLS."/cls_DateTimeInLine.php");

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

//$cls_db = new cls_db();
$a_printer = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM printer"));
$cls_html = new cls_html();
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>null, "text"=>array("[Name]"));
$optPrinter = $cls_html->getOptions($a_printer,$a_selection);

//$parametri = new parametri_annuali($c, date('Y-m-d'), "CDS");

$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".date('Y')."' AND Tipo_Riscossione = '*****'";
$parametri = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_annuali");

if($parametri->ID == null) {
    $cls_help->alert("l'anno " . date('Y') . " non è presente nei parametri annuali!");
    die;
}

//var_dump($parametri);
if($parametri->Anno!=date('Y'))
    //alert("ATTENZIONE! I parametri annuali sono assenti o non aggiornati per l'anno corrente. Si prega di aggiornare i dati prima di procedere con l'elaborazione.");

$layout = "";
$richiesta_singola = $cls_help->getVar('richiesta_singola');
$partita_ID = $cls_help->getVar('partita');
$tipo_atto = $cls_help->getVar('tipo_atto');
$tipo_partita = $cls_help->getVar('tipo_partita');


if($richiesta_singola=="si")
{
    $partita = $cls_help->getVar('partita');
    $layout = "<script>$('#da_n_elenco').val('".$partita."')</script>";
    $layout.= "<script>$('#a_n_elenco').val('".$partita."')</script>";

    $anno_rif = $cls_help->getVar('anno_rif');

    $layout.= "<script>$('#da_anno').val('".$anno_rif."')</script>";
    $layout.= "<script>$('#ad_anno').val('".$anno_rif."')</script>";

    //$utente = new utente($p, $c);
    $query = "SELECT * FROM utente WHERE ID = '".$p."' AND CC_Comune = '".$c."'";
    $utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");

    if($utente->Genere!="D")
    {
        $layout.= "<script>$('#daco').val('".$utente->Cognome."')</script>";
        $layout.= "<script>$('#dano').val('".$utente->Nome."')</script>";
        $layout.= "<script>$('#acog').val('".$utente->Cognome."')</script>";
        $layout.= "<script>$('#anom').val('".$utente->Nome."')</script>";
    }
    else
    {
        $layout.= "<script>$('#daco').val('".$utente->Ditta."')</script>";
        $layout.= "<script>$('#acog').val('".$utente->Ditta."')</script>";
    }

    $layout.= "<script>$('#tipo_partita').val('".$tipo_partita."')</script>";

}
else
{
    $layout="";
    $layout.= "<script>$('#ritorno_ruolo').hide()</script>";
}


$date = new cls_DateTime(date('Y-m-d'),"DB");

$data = $date->AddMonth(2);

$data_calcolo = $date->GetDate("IT");
//echo "<h1>".$data_calcolo."</h1>";
/*
 * $date = next_months(date('Y-m-d'), 2);
 * $date = explode('*',$date);
 * $data_calcolo = $date[1];
 * */


$disabled = "";
$disable_avviso = "";
$data_calcolo_visual = "";
$data_elab_visual = date('d/m/Y');
$testo_avviso = "";
$html_sollecito = "";



$testo_validazione = "<br>L'atto verra' elaborato se:<br><br>";
$testo_validazione.= "- l'atto precedente e' scaduto;<br>";
$testo_validazione.= "- l'atto precedente ha uno stato di giacenza inserito ed il Flag 'Indirizzo validato' selezionato;<br>";
$testo_validazione.= "- l'atto precedente ha il Flag 'Rielabora' selezionato;<br>";
$optionsModalita = "";
$nome_atto_completo = $tipo_atto;

//echo "<h1>Tipo atto --> ".$tipo_atto."</h1>";
$testo_visual = "";

switch($tipo_atto)
{
    case "Ingiunzione":

        $disabled = " disabled ";
        $data_calcolo_visual = 	$data_calcolo;
        $action_page = "elaborazione_ingiunzioni.php";
        $testo_validazione.= "- non vi sono atti precedenti;<br>";
        $testo_visual = $testo_validazione;
        if($richiesta_singola=="si")
            $html_sollecito = "";
        else
            $html_sollecito = "<input type=checkbox name=prima_ingiunzione value=si tabindex=3 checked> Elabora solo se non sono ancora uscite Ingiunzioni";
        $optionsModalita = "<option value=\"posta\">Raccomandata A.G.</option><option value=\"mani\">A mani</option>";

        break;

    case "IRTEL":

        $disabled = " disabled ";
        $data_calcolo_visual = 	"09/10/2018";
        $data_elab_visual = $data_calcolo_visual;
        $action_page = "elaborazione_ingiunzioni_irtel.php";
        $testo_validazione.= "- non vi sono atti precedenti;<br>";
        $testo_visual = $testo_validazione;
        if($richiesta_singola=="si")
            $html_sollecito = "";
        else
            $html_sollecito = "<input type=checkbox name=prima_ingiunzione value=si tabindex=3 checked> Elabora solo se non sono ancora uscite Ingiunzioni";

        break;

    case "avv_intimazione":

        $nome_atto_completo = "Avviso di intimazione ad adempiere";
        $disable_avviso = " disabled ";

        $testo_avviso = "Per elaborare gli avvisi e' necessario che sia passato almeno un anno dalla data di notifica dell'ingiunzione";
        $testo_visual = $testo_avviso."<br>".$testo_validazione;
        $action_page = "elaborazione_avvisi_intimazione.php";
        $optionsModalita = "<option value=\"posta\">Raccomandata A.G.</option><option value=\"mani\">A mani</option>";
        break;

    case "sollecito":

        $nome_atto_completo = "Sollecito di pagamento";
        $disable_avviso = " disabled ";
        $testo_avviso = "Si possono elaborare i solleciti solo dopo la notifica dell'Ingiunzione e se non e' ancora stato emesso un Avviso di intimazione ad adempiere";
        $testo_visual = $testo_avviso;
        $action_page = "elaborazione_solleciti_ingiunzione.php";
        $html_sollecito = "<input type=checkbox name=primo_sollecito value=si tabindex=3> Elabora solo se non sono ancora usciti Solleciti";
        $optionsModalita = "<option value=\"ordinaria\" checked>Posta ordinaria</option>";
        break;

    case "sollecito_pre":
        $optionsRiscossione = "<option value=\"CDS\">CDS/AMMINISTRATIVA</option>
				<option>PATRIMONIALE</option>
				<option>RIFIUTI</option>";

        //echo $optionsRiscossione;
        $nome_atto_completo = "Sollecito pre ingiunzione";
        $disabled = " disabled ";
        $disable_avviso = " disabled ";
        $testo_avviso = "Si possono elaborare i solleciti pre ingiunzione solo se<br>non sono ancora stati emessi Avvisi di intimazione ad adempiere ed Ingiunzioni";
        $testo_visual = $testo_avviso;
        $action_page = "elab_solleciti_pre_ingiunzione.php";
        $html_sollecito = "<input type=checkbox name=primo_sollecito value=si tabindex=3 checked> Elabora solo se non sono ancora usciti Solleciti";
        $optionsModalita = "<option value=\"ordinaria\" checked>Posta ordinaria</option>";
        break;

    case "avviso_mora":
        $optionsRiscossione = "<option>PATRIMONIALE</option>
				<option>RIFIUTI</option>";
        $nome_atto_completo = "Avviso di messa in mora";
        $disabled = " disabled ";
        $data_calcolo_visual = 	$data_calcolo;
        $testo_avviso = "Si possono elaborare gli avvisi di messa in mora solo se<br>non sono ancora stati emessi Avvisi di intimazione ad adempiere ed Ingiunzioni";
        $testo_visual = $testo_avviso;
        $action_page = "elab_avvisi_messa_in_mora.php";
        $html_sollecito = "<input type=checkbox name=primo_avviso value=si tabindex=3 checked > Elabora solo se non sono ancora usciti Avvisi";
        $optionsModalita = "<option value=\"posta\" checked>Raccomandata A.G.</option><option value=\"raccomandata\" selected>Raccomandata</option>";
        break;
}

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");

if( $comune->Gestore_ID != 0 )
{
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");// new gestore($val['Gestore_ID']);
}
else
{
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Info_ID']);
}


//var_dump($comune);
$Select_Tax = $comune->Select_Tax;
if($Select_Tax<1) //echo "<br>< di uno";
    $cls_help->alert("Non e' possibile procedere con le elaborazioni senza aver impostato la Gestione entrate in PARAMETRI -> ENTE -> DATI ENTE");

    //echo "<h1>Tax --> ".$Select_Tax."</h1>";
if($Select_Tax==1){
    $optionsRiscossione = "<option value=\"CDS\">CDS/AMMINISTRATIVA</option>
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


$nome_com = $comune->Denominazione;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$checkMsg = "";

$gestore = $comune->Gestore;
if($gestore->Tipo!="Concessionario")
    $checkMsg = "!!! ATTENZIONE !!! Il diritto di riscossione non verra' calcolato in questa elaborazione poiche' la gestione e' effettuata direttamente dall'ente.";


$da_anno = $a;
$ad_anno = $a;

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++)
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";

/*$resIngiunzioni = mysql_query($queryIngiunzioni);
while ($rigaIngiunzioni = mysql_fetch_assoc($resIngiunzioni))
{
    $serieOption .= "<option value='" . $rigaIngiunzioni['Comune_ID'] . "'>" . $rigaIngiunzioni['Comune_ID'] . "</option>";
}*/

?>


    <!-- ********** VARIABILI ********** -->
    <script>
        var elab_singola = "<?php echo $richiesta_singola; ?>";

        var select_tax = <?=$Select_Tax;?>;
    </script>

    <!-- ********** GESTIONE LINK MENU ********** -->
    <script>

        //F3
        switchMenuImg("F3");
        F3_button = function()
        {
            if("<?php echo $tipo_atto; ?>" == "generico")
                alert("Selezionare il tipo di atto!");
            if("<?php echo $checkMsg; ?>" != "")
                alert("<?php echo $checkMsg; ?>");

            if($("#PrinterId").val()!=""){
                if(select_tax<1){
                    alert("Non e' possibile procedere con le elaborazioni senza aver impostato la Gestione entrate in PARAMETRI -> ENTE -> DATI ENTE");
                }
                else{
                    if(submit_buttons('Elabora'))
                        $('#form_elabora').submit();
                }
            }
            else
            {
                alert("Selezionare lo stampatore!");
            }
        }

        //F5
        switchMenuImg("F5");
        F5_button = function()
        {
            location.href="elabora_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto=<?php echo $tipo_atto ?>";
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

        //F11-F12 sono nel menu'
        switchMenuImg("F11");
        F11_button = function(){
            $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Elaborazioni_Atti.pdf"; ?>");
            $("#helpModalLabel").empty().append("<b>Help Elaborazioni Atti</b>");
            $("#helpModal").modal('show');
        }

        //******************************\\
        //ALTRI LINK / FUNZIONI CHIAMATE\\
        //CAMBIO PAGINA
        function pagina_menu (value)
        {
            switch("<?php echo $tipo_atto; ?>"){
                case "sollecito_pre":
                    atto_next = "Ingiunzione";
                    atto_prev = "sollecito";
                    break;
                case "Ingiunzione":
                    atto_next = "avv_intimazione";
                    atto_prev = "sollecito_pre";
                    break;
                case "avv_intimazione":
                    atto_next = "sollecito";
                    atto_prev = "Ingiunzione";
                    break;
                case "sollecito":
                    atto_next = "sollecito_pre";
                    atto_prev = "avv_intimazione";
                    break;
            }

            if(value=='suc')
            {
                link = "elabora_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+atto_next;
            }
            else if(value=='prev')
            {
                link = "elabora_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+atto_prev;
            }

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

        function insert_a_data()
        {
            $('#a_data').val( $('#da_data').val() );
        }

        function ritorno_atto()
        {
            if(elab_singola=="si")
            {
                link = "<?= WEB_ROOT; ?>/coattiva/ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>";
                location.href= link;
            }
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

    <!-- ********** SUBMIT ********** -->
    <script>

        $(document).ready(function(){


            $("#submit_click").click( salva_form );


        });

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
                var link = "elabora_atto.php?";
                if (queryString.richiesta_singola != undefined) {
                    link += "richiesta_singola=" + queryString.richiesta_singola + "&";
                }
                if (queryString.tipo_atto != undefined) {
                    link += "tipo_atto=" + el.value + "&";
                }
                if (queryString.partita_ID != undefined) {
                    link += "partita_ID=" + queryString.partita_ID + "&";
                }
                if (queryString.partita != undefined) {
                    link += "partita=" + queryString.partita + "&";
                }
                if (queryString.tipo_partita != undefined) {
                    link += "tipo_partita=" + queryString.tipo_partita + "&";
                }
                if (queryString.anno_rif != undefined) {
                    link += "anno_rif=" + queryString.anno_rif + "&";
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


            <div class="row justify-content-md-center " style="margin: 2%;">
                <div class="col col-md-auto text_center">
                    <span class="titolo font16 under_decor">Gestione elaborazioni</span>
                </div>
            </div>

            <form id="form_elabora" name="form_elabora" action="<?php echo $action_page; ?>" method="post" target="elabora" onSubmit="window.open('', 'elabora', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')">

                <input name=invia_submit id=invia_submit type=hidden value="" >

                <input type=hidden name="c" value="<?php echo $c ?>">
                <input type=hidden name="a" value="<?php echo $a ?>">
                <input type=hidden name="partita" value="<?php echo $partita_ID ?>">

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
                                <select class="form-control resize" name=page id=page onchange="recallPageWithType(this);">
                                    <option value=""></option>
                                    <option value="Ingiunzione" <?= $sel_1; ?> >Ingiunzione</option>
                                    <option value="avv_intimazione" <?= $sel_2; ?> >Avviso d'intimazione</option>
                                    <option value="sollecito_pre" <?= $sel_3; ?> >Sollecito pre ingiunzione</option>
                                    <option value="avviso_mora" <?= $sel_4; ?> >Avviso di messa in mora</option>
                                    <option value="sollecito" <?= $sel_5; ?> >Sollecito di pagamento</option>
                                </select>
                            </div>
                        </div>
                        <!--<div class="form-group">
                            <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di atto</label>
                            <div class="col-lg-8">
                                <input name=tipo_atto readonly style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="text_left form-control resize" value="<?php echo $nome_atto_completo; ?>" size=35 tabindex=1>
                            </div>
                        </div>-->
                    </div>
                </div>
                <div class="row justify-content-md-center " style="margin: 2%;">
                    <div class="col col-md-auto text_center">
                        <span class="titolo font16 under_decor">Selezione</span>
                    </div>
                </div>

                <!--<div class="row" >
                    <div class="col-lg-offset-1 col-lg-5">

                    </div>
                </div>-->
                <div class="row" style="margin-top: 1%;">
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
                            <div class="col-lg-12">
                                <?php echo $html_sollecito; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

                <div class="row">
                    <div class="col col-lg-3 col-lg-offset-1">
                        <div class="form-group">
                            <input class="btn btn-primary form-control resize pwidth150 " style="width: 65%;" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',1);" tabindex=4>
                        </div>
                    </div>
                    <div class="col col-lg-3">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input readonly class="form-control resize" type="text" id="daco" name="daco" size=25  tabindex=5>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-2">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input readonly class="form-control resize" type="text" id="dano" name="dano" size=15  tabindex=6>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-2">
                        <div class="col-lg-12">Data calcolo interessi</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-3 col-lg-offset-1">
                        <div class="form-group">
                            <input class="btn btn-primary form-control resize pwidth150" style="width: 65%;" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',2);" tabindex=7>
                        </div>
                    </div>
                    <div class="col col-lg-3">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input readonly class="form-control resize" type="text" id="acog" name="acog" size=25  tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-2">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input readonly class="form-control resize" type="text" id="anom" name="anom" size=15  tabindex=9>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-2">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input name="data_calcolo" id="data_calcolo" size=9  <?php echo $disable_avviso; ?> class="form-control resize picker" value="<?php echo $data_calcolo_visual; ?>" tabindex=10>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-5 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Stampatore</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select class="form-control resize" name=PrinterId id=PrinterId>
                                    <?php echo $optPrinter; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-5 col-lg-offset-1">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo Entrata</label>
                        <div class="form-group">
                            <div class="col-lg-8">
                                <select name=tipo_partita id=tipo_partita class="form-control resize">
                                    <?php echo $optionsRiscossione; ?>
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
                                <select name=modalita_stampa id=modalita_stampa class="form-control resize">
                                    <?php echo $optionsModalita; ?>
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
                                <input type="text" class="form-control resize" id="da_anno" name="da_anno" value="<?php echo $da_anno?>" size=3  tabindex=15>
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
                                <input type="text" class="form-control resize" id="ad_anno" name="ad_anno" value="<?php echo $ad_anno?>" size=3 tabindex=16 onblur="focusIndex();">
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

                <div class="row">
                    <div class="col-lg-offset-1 col-lg-10">
                        <p class="resize" style="color: red;"><?php echo $testo_visual; ?></p>
                    </div>
                </div>

                <div class="row" style="padding-bottom: 2%;">
                    <div class="col-lg-offset-1 col-lg-2">
                        <input id=ritorno_ruolo class="btn btn-primary pwidth150" type="button" value="Torna Gestione Partita" title="Torna alla gestione partita" onclick="ritorno_atto();" tabindex=17>
                    </div>
                </div>


            </form>

<script>focusIndex();</script>
<?php echo $layout; ?>

<?php include(INC."/footer.php"); ?>