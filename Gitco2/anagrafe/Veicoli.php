<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include_once(INC."/menu.php");


include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_anagrafeUtils.php");
include_once(CLS."/cls_Soap_Visure.php");
include_once CLS . "/cls_Utils.php";

$submenuPageNo = 7;
$pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';

$cls_date = new cls_DateTimeI("IT",false);
$cls_anagr = new cls_anagr();
$cls_utils = new cls_Utils();

if($_SESSION['username']==NULL)
{
    header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$mode = $cls_help->getVar('mode');
$servizio = $cls_help->getVar('servizio');

$mode = "modifica";//ANNULLO CONSULTA

if($mode=="consulta" || $mode==null) $mode = "consulta";
else $mode = "modifica";


function initRequest($cls_db,$cls_utils,$c,$elaboration_id = null){

    $query = "SELECT MAX(IdRichiesta) AS ID FROM request_visures_aci WHERE CC = '".$c."' AND year = '".date("Y")."'";
    $IdReq = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

    if($IdReq == null) $IdReq = 1;
    else $IdReq = $IdReq["ID"] + 1;

    $save = new stdClass();
    $save->year = date("Y");
    $save->CC = $c;
    $save->date = date("Y-m-d");
    $save->IdRichiesta = $IdReq;
    //$save->number_request = $richiesteInviate;
    //$save->received_request = $richiesteRicevute;
    $save->elaboration_id = $elaboration_id;

    return $cls_db->DbSave($cls_utils->GetObjectQuery((array) $save,"request_visures_aci"));
}


$QUERY = $cls_anagr->get_Query_Dati_Soggetto($p,$c);
$anagr = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Soggetto"]),"utente");

//var_dump($anagr);

$genere_utente 		= isset($anagr["Genere"])?$anagr["Genere"]:"";
$cognome_utente 	=	isset($anagr["Cognome"])?$anagr["Cognome"]:"";
$nome_utente 		=	isset($anagr["Nome"])?$anagr["Nome"]:"";
$comune_id 			=	isset($anagr["Comune_ID"])?$anagr["Comune_ID"]:"";
$ditta				=	isset($anagr["Ditta"])?$anagr["Ditta"]:"";




$cognome = isset($anagr["Cognome"])?$anagr["Cognome"]:"";
$dittaAnagr = isset($anagr["Ditta"])?$anagr["Ditta"]:"";
$idAnagr = isset($anagr["ID"])?$anagr["ID"]:"";

$ID_PAGE = $cls_anagr->get_ID_Move_Page($p,$a,$c,$cognome,$dittaAnagr,$idAnagr);

$pnext = $ID_PAGE["next"];//$utente->next;
$pprev = $ID_PAGE["prev"];//$utente->prev;
$next_alfa = $ID_PAGE["next_alfa"];//$utente->next_alfa;
$prev_alfa = $ID_PAGE["prev_alfa"];//$utente->prev_alfa;

$ordinamento = $cls_help->getVar('ordinamento');
if($ordinamento=='')	$ordinamento="ID";

if( $ordinamento == "Nome" )
{
    $prev_current = $prev_alfa;
    $next_current = $next_alfa;
}
else
{
    $prev_current = $pprev;
    $next_current = $pnext;
}

if ($pnext==null) 	$pnext = 0;
if ($pprev==null) 	$pprev = 0;
if ($p==null)		$p=0;


$Visura_DB = isset($_GET["Visura"])?$_GET["Visura"]:"no";
$utente = $anagr["Ditta"]!=""?$anagr["Ditta"]:$anagr["Cognome"]." ".$anagr["Nome"];

//$cls_help->alert($Visura_DB);
$messageSoap = "";
$recall = false;
$VpnCheck = false;
require CONFIG_ROOT."/_aciServer.php";
if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)!==false)
    $VpnCheck = true;

$result = array();
    
if($p!="")
{
    if($Visura_DB=="no"){
        $query = "SELECT * FROM veicoli where CC_Comune = '".$c."' AND Utente_ID = ".$p." AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE CC_Comune = '".$c."' AND Utente_ID = ".$p.") AND ProgressivoVisura = (SELECT MAX(ProgressivoVisura) FROM veicoli WHERE CC_Comune = '".$c."' AND Utente_ID = ".$p." AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE CC_Comune = '".$c."' AND Utente_ID = ".$p."))";
        //echo $query;
        $result = $cls_db->getResults($cls_db->ExecuteQuery($query));

        if(count($result) == 0)
            $messageSoap = "Nessun risultato trovato";

    }else if($Visura_DB == "si"){

        $query = "SELECT MAX(Data_Visura) as Data FROM veicoli WHERE CC_Comune = '".$c."' AND Utente_ID = ".$p;
        //echo $query;
        $data = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

        $dataResult = isset($data["Data"])?$data["Data"]:"";

        if($dataResult < date("Y-m-d")){
            if($VpnCheck===false){
                shell_exec(ACIVPN_CMD);
                sleep(2);
                if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)!==false)
                    $VpnCheck = true;
            }
            else
                $VpnCheck = true;

            if($VpnCheck){

                //$richiesteInviate = 0;
                //$richiesteRicevute = 0;
                $id_request = null;

                $soap = new SoapCMN();
                if($anagr["Codice_Fiscale"] != "")
                {
                    $resultSoap = $soap->SearchForCF($anagr["Codice_Fiscale"]);//"VRDGLC89A16D969M","TGLFNC75M23B963F"
                    $id_request = initRequest($cls_db,$cls_utils,$c);
                    //$richiesteInviate++;
                }
                else if($anagr["Partita_Iva"] != ""){
                    $resultSoap = $soap->SearchForPI($anagr["Partita_Iva"]);//"01338160995"
                    $id_request = initRequest($cls_db,$cls_utils,$c);
                    //$richiesteInviate++;
    
                }else{
                    if($anagr["Ditta"] != "") $Tipo_Persona = "Giuridica";
                    else $Tipo_Persona = "Fisica";
    
                    $resultSoap = $soap->SearchForNominative($Tipo_Persona,$anagr["Cognome"],$anagr["Nome"]);
                    $id_request = initRequest($cls_db,$cls_utils,$c);
                    //$richiesteInviate++;
                }
    
                //var_dump($resultSoap);
    
                if(gettype($resultSoap) == "integer") {
                    if($resultSoap == 0)
                        $messageSoap = "Prima connettersi alla VPN";
                }
                else if(gettype($resultSoap) != "object") {
                    //var_dump($resultSoap);
                    $messageSoap = "Errore, impossibile effettuare la visura";
                }
    
    
                //$result = $soap->SearchForNominative("Giuridica","VIRDIS","GIANLUCA");
                //$result = $soap->SearchForPI("01338160995");
    
                //var_dump($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo->Soggetto->Nome);
/*
 * INSERT INTO table_name (column1, column2, column3, ...)
VALUES (value1, value2, value3, ...);
*/
                if($id_request != null) {
                    $queryInsert = "INSERT INTO request_visures_aci_detail (request_id,user_id,targa,tipo_veicolo,classe_veicolo) VALUES ";
                }
    
                $result = array();
                $count = 0;
                //var_dump($resultSoap);
                //var_dump($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo);
                if(!empty($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo) && gettype($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo)=="array") {

                    foreach ($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo as $value) {

                        //$richiesteRicevute++;
                        if ($count == 0)
                            $queryInsert .= " (" . $id_request . ", " . $idAnagr . ", '" . $value->Targa . "', '" . str_replace("'","\'",$value->DatiTecnici->Tipo) . "', '" . str_replace("'","\'",$value->DatiTecnici->ClasseVeicolo) . "' ) ";
                        else
                            $queryInsert .= ", (" . $id_request . ", " . $idAnagr . ", '" . $value->Targa . "', '" . str_replace("'","\'",$value->DatiTecnici->Tipo) . "', '" . str_replace("'","\'",$value->DatiTecnici->ClasseVeicolo) . "' ) ";

                        $result[$count]["Data_Visura"] = date("Y-m-d");
    
                        $result[$count]["ProgressivoLista"] = $value->ProgressivoLista;
                        $result[$count]["ProvinciaCompetenza"] = $value->ProvinciaCompetenza;
                        $result[$count]["Targa"] = $value->Targa;
                        $result[$count]["SerieTarga"] = $value->SerieTarga;
                        $result[$count]["StatoVeicolo"] = $value->StatoVeicolo;
                        $result[$count]["Causale"] = $value->Causale;
                        $result[$count]["FlagGiuridico"] = $value->FlagGiuridico;
                        $result[$count]["DataPrimaImmatricolazione"] = $value->DataPrimaImmatricolazione;
                        $result[$count]["CodiceUltimaFormalita"] = $value->CodiceUltimaFormalita;
                        $result[$count]["DescrizioneUltimaFormalita"] = $value->DescrizioneUltimaFormalita;
                        $result[$count]["DataUltimaFormalita"] = $value->DataUltimaFormalita;
    
                        $result[$count]["Telaio"] = $value->DatiTecnici->Telaio;
                        $result[$count]["Fabbrica"] = $value->DatiTecnici->Fabbrica;
                        $result[$count]["Tipo"] = $value->DatiTecnici->Tipo;
                        $result[$count]["Serie"] = $value->DatiTecnici->Serie;
                        $result[$count]["ClasseVeicolo"] = $value->DatiTecnici->ClasseVeicolo;
    
                        $result[$count]["Cognome"] = $value->Soggetto->Cognome;
                        $result[$count]["Nome"] = $value->Soggetto->Nome;
                        $result[$count]["DataNascita"] = $resultSoap->DatiRichiesta->DataNascita;
                        //$result[$count]["ComuneNascita"] = $value->Soggetto->ComuneNascita;//
                        $result[$count]["CodiceFiscale"] = $value->Soggetto->CodiceFiscale;
                        $result[$count]["PartitaIva"] = $value->Soggetto->PartitaIva;
                        $result[$count]["ProvinciaResidenza"] = $value->Soggetto->ProvinciaResidenza;
                        $result[$count]["CodiceRuoloSoggetto"] = $value->Soggetto->CodiceRuoloSoggetto;
                        $result[$count]["DescrizioneRuoloSoggetto"] = $value->Soggetto->DescrizioneRuoloSoggetto;
                        $result[$count]["DataRiferimentoRuoloSoggetto"] = $value->Soggetto->DataRiferimentoRuoloSoggetto;
    
                        $count++;
                        //die;
                    }
                }
                else if(isset($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo)){
                    //var_dump($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo);

                    foreach ($resultSoap->DatiRisposta->ElencoVeicoli as $value) {

                        //$richiesteRicevute++;
                        if ($count == 0)
                            $queryInsert .= " (" . $id_request . ", " . $idAnagr . ", '" . $value->Targa . "', '" . str_replace("'","\'",$value->DatiTecnici->Tipo) . "', '" . str_replace("'","\'",$value->DatiTecnici->ClasseVeicolo) . "' ) ";
                        else
                            $queryInsert .= ", (" . $id_request . ", " . $idAnagr . ", '" . $value->Targa . "', '" . str_replace("'","\'",$value->DatiTecnici->Tipo) . "', '" . str_replace("'","\'",$value->DatiTecnici->ClasseVeicolo) . "' ) ";

                        $result[$count]["Data_Visura"] = date("Y-m-d");
    
                        $result[$count]["ProgressivoLista"] = $value->ProgressivoLista;
                        $result[$count]["ProvinciaCompetenza"] = $value->ProvinciaCompetenza;
                        $result[$count]["Targa"] = $value->Targa;
                        $result[$count]["SerieTarga"] = $value->SerieTarga;
                        $result[$count]["StatoVeicolo"] = $value->StatoVeicolo;
                        $result[$count]["Causale"] = $value->Causale;
                        $result[$count]["FlagGiuridico"] = $value->FlagGiuridico;
                        $result[$count]["DataPrimaImmatricolazione"] = $value->DataPrimaImmatricolazione;
                        $result[$count]["CodiceUltimaFormalita"] = $value->CodiceUltimaFormalita;
                        $result[$count]["DescrizioneUltimaFormalita"] = $value->DescrizioneUltimaFormalita;
                        $result[$count]["DataUltimaFormalita"] = $value->DataUltimaFormalita;
    
                        $result[$count]["Telaio"] = $value->DatiTecnici->Telaio;
                        $result[$count]["Fabbrica"] = $value->DatiTecnici->Fabbrica;
                        $result[$count]["Tipo"] = $value->DatiTecnici->Tipo;
                        $result[$count]["Serie"] = $value->DatiTecnici->Serie;
                        $result[$count]["ClasseVeicolo"] = $value->DatiTecnici->ClasseVeicolo;
    
                        $result[$count]["Cognome"] = $value->Soggetto->Cognome;
                        $result[$count]["Nome"] = $value->Soggetto->Nome;
                        $result[$count]["DataNascita"] = $resultSoap->DatiRichiesta->DataNascita;
                        //$result[$count]["ComuneNascita"] = $value->Soggetto->ComuneNascita;//
                        $result[$count]["CodiceFiscale"] = $value->Soggetto->CodiceFiscale;
                        $result[$count]["PartitaIva"] = $value->Soggetto->PartitaIva;
                        $result[$count]["ProvinciaResidenza"] = $value->Soggetto->ProvinciaResidenza;
                        $result[$count]["CodiceRuoloSoggetto"] = $value->Soggetto->CodiceRuoloSoggetto;
                        $result[$count]["DescrizioneRuoloSoggetto"] = $value->Soggetto->DescrizioneRuoloSoggetto;
                        $result[$count]["DataRiferimentoRuoloSoggetto"] = $value->Soggetto->DataRiferimentoRuoloSoggetto;
    
                        $count++;
                        //die;
                    }
                }else {
                    $queryInsert .= " (".$id_request.", ".$idAnagr.", NULL, NULL, NULL ) ";

                    if($messageSoap == "")
                        $messageSoap = "Nessun risultato trovato";
                }

                $cls_db->ExecuteQuery($queryInsert);
            }
        }
        else
        {
            $cls_help->alert("Visura già effettuata oggi");
            //header("Location: Veicoli.php?Visura=no&mode={$mode}&p={$p}&c={$c}&a={$a}&servizio={$servizio}");
            $recall = true;
        }


    }else {
        echo "errore";
    }
}else{
    $result = array();
    $messageSoap = "Nessun risultato trovato";
}

if($VpnCheck)
    $vpnMessage = "VPN CONNESSA";
else
    $vpnMessage = "VPN NON CONNESSA";

?>


<script>   /* -----------  VARIABILI JAVASCRIPT E SELEZIONI LAYOUT ----------- */
    var stringaPHP = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
    var modalita = '<?php echo $mode; ?>';
    var utente_ID = "<?php echo $anagr['ID']; ?>";
    var comune_ID = "<?php echo $anagr['Comune_ID']; ?>";



    var prev_utente = "<?php echo $prev_current; ?>";
    var next_utente = "<?php echo $next_current; ?>";

    function ordinamento ()
    {
        value = $('#ordinamento').val();

        if(value=="ID")
        {
            prev_utente = "<?php echo $pprev; ?>";
            next_utente = "<?php echo $pnext; ?>";
        }
        else if(value=="Nome")
        {
            prev_utente = "<?php echo $prev_alfa; ?>";
            next_utente = "<?php echo $next_alfa; ?>";
        }

    }


    //F3
    switchMenuImg("F3");
    F3_button = function()
    {
        //alert();
        if(getParameterByName("Visura") != "si")
        {
            alert("Prima effettuare la visura");
            return false;
        }
        control_salva = submit_buttons('Salva');
//	alert(control_salva);
        if(control_salva)
            $("#btnSub").trigger("click");
    }

    //F4
    /*switchMenuImg("F4");
    F4_button = function()
    {
        control_salva = submit_buttons('Delete');

        if(control_salva)
            $("#btnSub").trigger("click");
    }*/

    //F5
    switchMenuImg("F5");
    F5_button = function()
    {
        stringaPHP += "&mode=consulta";
        stringa = "Veicoli.php?"+stringaPHP+"&Visura=no";
        top.location.href = stringa;
    }

    //F6
    /*switchMenuImg("F6");
    F6_button = function()
    {
        stringa = "Veicoli.php?mode=modifica&p=0&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        top.location.href = stringa;
    }*/



    //PAG GIU
    switchMenuImg("pagedown");
    pagedown_button = function(){
        if (modifica==1)
        {
            alert('salvare i dati o annullare prima di procedere');
        }
        else
        {
            value_ord = $('#ordinamento').val();

            link = "cambia_residenza.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
            top.location.href = link;
        }
    }

    //PAG SU
    switchMenuImg("pageup");
    pageup_button = function(){
        if (modifica==1)
        {
            alert('salvare i dati o annullare prima di procedere');
        }
        else
        {
            value_ord = $('#ordinamento').val();

            link = "dati_soggetto.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
            top.location.href = link;
        }
    }

    //F7
    switchMenuImg("F7");
    F7_button = function()
    {
        if (modifica==1)
        {
            alert('salvare i dati o annullare prima di procedere');
        }
        else
        {
            value_ord = $('#ordinamento').val();
            link = "Veicoli.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

            top.location.href = link;
        }
    }

    //F8
    switchMenuImg("F8");
    F8_button = function()
    {
        if (modifica==1)
        {
            alert('salvare i dati o annullare prima di procedere');
        }
        else
        {
            value_ord = $('#ordinamento').val();
            link = "Veicoli.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

            top.location.href = link;
        }
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/VisuraVeicoli.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help ANAGRAFE Visura veicoli</b>");
        $("#helpModal").modal('show');

    }

    function simple_ajax()
    {

        $.ajax({
            type: "POST",
            async: false,
            url: "ajax/ajax_anagrafe.php",
            dataType: 'JSON',
            data: {
                ID: "",
                cognome: "",
            },

            success: function(value) {

            }
        });

    }

    $( document ).ready(function() {

        if("<?= $recall; ?>" == true)
            Recall("no");

        if(utente_ID=="")
        {
            if(prev_utente!="0")
                $('#F7').attr("onMouseover","title='Ultimo record F7'");

            if(next_utente!="0")
                $('#F8').attr("onMouseover","title='Primo record F8'");
        }
        else
        {
            if(prev_utente=="" && next_utente!="")
            {
                $('#F7').attr("onMouseover","title='Nessun record F7 (Primo record selezionato)'");
                $('#F8').attr("onMouseover","title='Record successivo F8 (Primo record selezionato)'");
            }

            if(next_utente=="" && prev_utente!="")
            {
                $('#F7').attr("onMouseover","title='Record precedente F7 (Ultimo record selezionato)'");
                $('#F8').attr("onMouseover","title='Nessun record F8 (Ultimo record selezionato)'");
            }
        }
    });

    function mostraNascondi(el,icons)
    {
        /*if(typeof(icons) == "object") objectIcons = $(icons);
        else */
        var objectIcons = $("#"+icons);
        var classList = objectIcons.attr('class').split(/\s+/);
        $.each(classList, function(index, item) {

            if (item === 'fa-angle-down') {
                objectIcons.removeClass( "fa-angle-down" ).addClass( "fa-angle-up" );
            }
            else if(item === 'fa-angle-up'){
                objectIcons.removeClass( "fa-angle-up" ).addClass( "fa-angle-down" );
            }
        });
        //alert($("#"+el).css("display"));
        //var node = $('#subscription_popup');

       /* if(document.getElementsByClassName(el)[0] == undefined)
        {
            if ($(".empty").is(':visible'))
                $(".empty").fadeOut("slow", function() {});
            else
                $(".empty").slideDown("slow", function() {});
        }
        else
        {*/
            if ($("."+el).is(':visible'))
                $("."+el).fadeOut("slow", function() {});
            else
                $("."+el).slideDown("slow", function() {});
      //  }
    }

    function Recall(visura = "si")
    {
        var path = window.location.pathname;
        var page = path.split("/").pop();

        var mode = getParameterByName("mode");
        var p = getParameterByName("p");
        var c = getParameterByName("c");
        var a = getParameterByName("a");
        var servizio = getParameterByName("servizio");
        var ordinamento = getParameterByName("ordinamento");

        window.location.href = page + "?Visura="+visura+"&mode="+mode+"&p="+p+"&c="+c+"&a="+a+"&servizio="+servizio+"&ordinamento="+ordinamento;
    }

    function getParameterByName(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }
</script>

<?php

$menuPageNumber = "Pag 7/7";
$pagina = "Veicoli.php";
include_once(INC."/submenu_anagrafe.php");
include_once(INC."/pages_authorization.php");
?>

<form id=veicoli_form class="form-horizontal validate" name=veicoli action="veicoli_salva.php" method=post>

    <div class="row">
    <div class="col col-lg-10 col-lg-offset-1 color_titolo" style="text-align: center;"><b><?= $vpnMessage; ?></b></div><br><br>
            <?php
            if($Visura_DB == "no" && $_SESSION['aut_tipo']!=20){
                    ?>
                    <div class="form-group" style="text-align: center;">
                        <button type="button" id="btnVisura" class="btn btn-primary" onclick="Recall('si');">Esegui Visura</button>
                    </div>
            <?php } ?>
        <div class="col col-lg-10 col-lg-offset-1">
            <?php
            if(!$recall)
                if(count($result)==0){?>
            <div class="row" style="overflow-y: visible; overflow-x: hidden; height: 50vh !important; width: 100%;">

                    <div class="row" style="background-color: #0a53be; border: 1px solid white;cursor: pointer;" onclick="mostraNascondi('empty','empty_id');">
                        <div class="col-12">
                            <div style="float:left; color: white;margin-left: 2%;">Veicolo</div>
                            <div style="float: right; color: white;margin-right: 2%;"><i class="fa fa-angle-down" id="empty_id" aria-hidden="true" ></i></div>
                        </div>
                    </div>
                    <div class="empty" style="display: none;"><p style="color:red; text-align: center; background-color: #ADCDFF;"><b><?= $messageSoap; ?></b></p></div>

            </div>
            <?php } else { ?>
            <div class="row" style="overflow-y: visible; overflow-x: hidden; height: 50vh !important; width: 100%;">
            
            <?php
            if(!$recall){
                for($i=0; $i<count($result); $i++){

                    $CF_PI = "";
                    if($result[$i]["CodiceFiscale"]==null) $CF_PI = "PI: ".$result[$i]["PartitaIva"];
                    else $CF_PI = "CF: ".$result[$i]["CodiceFiscale"];

                    $bg = "";
                    $bg1 = "";
                    if(trim($result[$i]["StatoVeicolo"]," ")=="TargaAttuale" || trim($result[$i]["StatoVeicolo"]," ")==null) {

                        $bg = "#0a53be";
                        $bg1 = "#ADCDFF";
                    }
                    else{
                        $bg1 = "#ff8a88";
                        $bg = "#f12321";

                        $flagAltriBlu = false;
                        for($x=$i; $x<count($result);$x++){
                            if(trim($result[$x]["StatoVeicolo"]," ")=="TargaAttuale" || trim($result[$x]["StatoVeicolo"]," ")==null){
                                $flagAltriBlu = true;
                                break;
                            }
                        }
                        if($flagAltriBlu){
                            $arrTemp = $result[$i];
                            array_splice($result, $i, 1);
                            array_push($result,$arrTemp);
                            $i--;
                            continue;
                        }
                    }
                ?>

                <input name=Data_Visura id=Data_Visura type=hidden value="<?php echo $result[$i]["Data_Visura"]; ?>"/>
                <input name=Utente_ID id=Utente_ID type=hidden value="<?php echo $p; ?>"/>
                <input name=c id=c type=hidden value="<?php echo $c; ?>"/>
                <input name=a id=a type=hidden value="<?php echo $a; ?>"/>
                <input name=mode id=mode type=hidden value="<?php echo $mode; ?>"/>
                <input name=servizio id=servizio type=hidden value="<?php echo $servizio; ?>"/>

                <input name=ProgressivoLista[] id=ProgressivoLista type=hidden value="<?php echo $result[$i]["ProgressivoLista"]; ?>"/>
                <input name=ProvinciaCompetenza[] id=ProvinciaCompetenza type=hidden value="<?php echo $result[$i]["ProvinciaCompetenza"]; ?>"/>
                <input name=Targa[] id=Targa type=hidden value="<?php echo $result[$i]["Targa"]; ?>"/>
                <input name=SerieTarga[] id=SerieTarga type=hidden value="<?php echo $result[$i]["SerieTarga"]; ?>"/>
                <input name=StatoVeicolo[] id=StatoVeicolo type=hidden value="<?php echo $result[$i]["StatoVeicolo"]; ?>"/>
                <input name=Causale[] id=Causale type=hidden value="<?php echo $result[$i]["Causale"]; ?>"/>
                <input name=FlagGiuridico[] id=FlagGiuridico type=hidden value="<?php echo $result[$i]["FlagGiuridico"]; ?>"/>
                <input name=DataPrimaImmatricolazione[] id=DataPrimaImmatricolazione type=hidden value="<?php echo str_replace("+"," ",$result[$i]["DataPrimaImmatricolazione"]).":00"; ?>"/>
                <input name=CodiceUltimaFormalita[] id=CodiceUltimaFormalita type=hidden value="<?php echo $result[$i]["CodiceUltimaFormalita"]; ?>"/>
                <input name=DescrizioneUltimaFormalita[] id=DescrizioneUltimaFormalita type=hidden value="<?php echo $result[$i]["DescrizioneUltimaFormalita"]; ?>"/>
                <input name=DataUltimaFormalita[] id=DataUltimaFormalita type=hidden value="<?php echo str_replace("+"," ",$result[$i]["DataUltimaFormalita"]).":00"; ?>"/>

                <input name=Telaio[] id=Telaio type=hidden value="<?php echo $result[$i]["Telaio"]; ?>"/>
                <input name=Fabbrica[] id=Fabbrica type=hidden value="<?php echo $result[$i]["Fabbrica"]; ?>"/>
                <input name=Tipo[] id=Tipo type=hidden value="<?php echo $result[$i]["Tipo"]; ?>"/>
                <input name=Serie[] id=Serie type=hidden value="<?php echo $result[$i]["Serie"]; ?>"/>
                <input name=ClasseVeicolo[] id=ClasseVeicolo type=hidden value="<?php echo $result[$i]["ClasseVeicolo"]; ?>"/>

                <input name=Cognome[] id=Cognome type=hidden value="<?php echo $result[$i]["Cognome"]; ?>"/>
                <input name=Nome[] id=Nome type=hidden value="<?php echo $result[$i]["Nome"]; ?>"/>
                <input name=DataNascita[] id=DataNascita type=hidden value="<?php echo $result[$i]["DataNascita"]; ?>"/>
                <input name=CodiceFiscale[] id=CodiceFiscale type=hidden value="<?php echo $result[$i]["CodiceFiscale"]; ?>"/>
                <input name=PartitaIva[] id=PartitaIva type=hidden value="<?php echo $result[$i]["PartitaIva"]; ?>"/>
                <input name=ProvinciaResidenza[] id=ProvinciaResidenza type=hidden value="<?php echo $result[$i]["ProvinciaResidenza"]; ?>"/>
                <input name=CodiceRuoloSoggetto[] id=CodiceRuoloSoggetto type=hidden value="<?php echo $result[$i]["CodiceRuoloSoggetto"]; ?>"/>
                <input name=DescrizioneRuoloSoggetto[] id=DescrizioneRuoloSoggetto type=hidden value="<?php echo $result[$i]["DescrizioneRuoloSoggetto"]; ?>"/>
                <input name=DataRiferimentoRuoloSoggetto[] id=DataRiferimentoRuoloSoggetto type=hidden value="<?php echo $result[$i]["DataRiferimentoRuoloSoggetto"]; ?>"/>

                <div class="row" style="background-color: <?= $bg; ?>; border: 1px solid white;cursor: pointer;" onclick="mostraNascondi('showHide_<?= $i; ?>','bar_<?= $i; ?>');">
                    <div class="col-12" >
                        <div style="float:left; color: white;margin-left: 2%;"><?= $result[$i]["SerieTarga"]; ?></div>
                        <div style="float: right; color: white;margin-right: 2%;"><i class="fa fa-angle-down" id="bar_<?= $i; ?>" aria-hidden="true" ></i></div>
                    </div>
                </div>

                <div class="row historyDiv showHide_<?= $i; ?>" style="background-color: <?= $bg1; ?>; border: 1px solid white; display: none;">
                    <div class="col-lg-12">
                        <div class="row" style="width:100%;">
                            <div class="col-12" style="color: #0a53be;">
                                <div style="float:right;margin-right: 2%;">Data visura: <?= $cls_date->Get_DateNewFormat($result[$i]["Data_Visura"],"DB"); ?> </div>
                                <div style="float: left;margin-left: 4%;">Utente: <?= $utente; ?> </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">Targa: <?= $result[$i]["Targa"]; ?></div>
                            <div class="col-lg-3">Serie targa: <?= $result[$i]["SerieTarga"]; ?></div>
                            <div class="col-lg-3">Provincia di competenza: <?= $result[$i]["ProvinciaCompetenza"]; ?></div>
                            <div class="col-lg-3">Data immatricolazione: <?= $cls_date->Get_DateNewFormat(str_replace("+"," ",$result[$i]["DataPrimaImmatricolazione"]).":00","DB"); ?></div>
                        </div>
                        <div style="border-top: 1px solid white; width: 99.8%; margin-left: 0.1%;"></div>
                        <div class="row">
                            <div class="col-lg-4">Telaio: <?= $result[$i]["Telaio"]; ?></div>
                            <div class="col-lg-4">Fabbrica: <?= $result[$i]["Fabbrica"]; ?></div>
                            <div class="col-lg-4">Tipo: <?= $result[$i]["Tipo"]; ?></div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">Classe veicolo: <?= $result[$i]["ClasseVeicolo"]; ?></div>
                            <div class="col-lg-4">Stato veicolo: <?= $result[$i]["StatoVeicolo"]; ?></div>
                            <div class="col-lg-4">Causale: <?= $result[$i]["Causale"]; ?></div>
                        </div>
                        <div style="border-top: 1px solid white; width: 99.8%; margin-left: 0.1%;"></div>
                    </div>
                </div>
            <?php  }} ?>
            </div>
                <?php }
                 ?>
        </div>
    </div>



    <div class="form-group">
        <button type="submit" id="btnSub" class="btn btn-primary" name="" style="display: none;" value="Submit"></button>
    </div>
</form>

<?php include(INC."/footer.php"); ?>
