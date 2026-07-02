<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/BuildFilter.php";


$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$print = $cls_help->getVar("print");
$queryType = $cls_help->getVar("query_type");
$filter["CC"] = $CC = $cls_help->getVar("CC");
$fileType = $cls_help->getVar("file_type");
$filter["anno_flusso_da"] = $annoFlussoDa = $cls_help->getVar("anno_flusso_da");
$filter["anno_flusso_a"] = $annoFlussoA = $cls_help->getVar("anno_flusso_a");
$filter["anno_pagamento_da"] = $annoPagamentoDa = $cls_help->getVar("anno_pagamento_da");
$filter["anno_pagamento_a"] = $annoPagamentoA = $cls_help->getVar("anno_pagamento_a");
$filter["data_inserimento_da"] = $dataInserimentoDa = $cls_help->getVar("data_inserimento_da");
$filter["data_inserimento_a"] = $dataInserimentoA = $cls_help->getVar("data_inserimento_a");
$filter["range_giorni"] = $rangeGiorni = $cls_help->getVar("range_giorni");
$filter["tipo_entrata"] = $tipoEntrata = $cls_help->getVar("tipo_entrata");
$filter["anno_crono_da"] = $da_anno_crono = $cls_help->getVar("anno_cronologico_da");
$filter["anno_crono_a"] = $a_anno_crono = $cls_help->getVar("anno_cronologico_a");
$filter["id_crono_da"] = $da_id_crono = $cls_help->getVar("id_cronologico_da");
$filter["id_crono_a"] = $a_id_crono = $cls_help->getVar("id_cronologico_a");
$filter["data_udienza_da"] = $dataUdienzaDa = $cls_help->getVar("data_udienza_da");
$filter["data_udienza_a"] = $dataUdienzaA = $cls_help->getVar("data_udienza_a");
$filter["court_level"] = $gradoRicorso = $cls_help->getVar("Court_Level");
$filter["autorita"] = $autorita = $cls_help->getVar("autority_type");
$filter["partita_da"] = $da_partita = $cls_help->getVar("partita_da");
$filter["partita_a"] = $a_partita = $cls_help->getVar("partita_a");
$filter["cognome_da"] = $daco = $cls_help->getVar("from_surname");
$filter["cognome_a"] = $acog = $cls_help->getVar("to_surname");
$filter["nome_da"] = $dano = $cls_help->getVar("from_name");
$filter["nome_a"] = $anom = $cls_help->getVar("to_name");
$filter['banca'] = $id_banca = $cls_help->getVar("ID_Banca");
$filter["da_data_notifica"] = $dataNotificaDa = $cls_help->getVar("da_data_notifica");
$filter["a_data_notifica"] = $dataNotificaA = $cls_help->getVar("a_data_notifica");


$title["value"] = "";

$cls_date = new cls_DateTimeI("IT",false);
$cls_elab = new cls_elaborazioniUtils();
$cls_html = new cls_html();
$cls_utils = new cls_Utils();

$queryBanks = "SELECT * FROM banca WHERE Tipo_Banca = 'sede' ORDER BY Denominazione ASC";
$a_banks = $cls_db->getResults( $cls_db->SelectQuery($queryBanks) );
$a_selection = array("value"=>"ID","firstOpt"=>1,"selected"=>null,"text"=>array("[Denominazione]"));
$optionsBanks = $cls_html->getOptions($a_banks,$a_selection);

$queryCities = "SELECT EG.* FROM enti_gestiti EG LEFT JOIN anni_gestiti A ON A.CC_Anno=EG.CC WHERE A.ID is not null";

$selectCity = null;

if($_SESSION['CC_User']!="****" && $_SESSION['CC_User']!="***+"){
    $c = $_SESSION['CC_User'];
    $queryCities.= " AND CC='".$_SESSION['CC_User']."' ";
}

if($_SESSION['aut_tipo']>2 && $_SESSION['aut_tipo']<20){
    $queryCities.= " AND Autorizzazione=".$_SESSION['aut_tipo']." ";
}
$queryCities.= " GROUP BY EG.ID ORDER BY Denominazione";

$a_enti = $cls_db->getResults( $cls_db->SelectQuery($queryCities) );
//print_r($a_enti);
$a_selection = array("value"=>"CC","firstOpt"=>1,"selected"=>$selectCity,"text"=>array("[Denominazione]"," - ","[CC]","  ","[Descrizione]"));
$optionsCities = $cls_html->getOptions($a_enti,$a_selection);

$query = "SELECT * FROM file_types";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$optFileType = "";
for($i=0; $i < count($result); $i++){
    $optFileType .= "<option value='".$result[$i]["Id"]."'>".$result[$i]["Name"]."</option>";
}

$query = "SELECT Type, Description FROM authority_type";
$authority_res = $cls_db->getResults($cls_db->ExecuteQuery($query));

$optAuthorityType = "";
for($i=0; $i < count($authority_res); $i++){
    $optAuthorityType .= "<option value='".$authority_res[$i]["Type"]."'>".$authority_res[$i]["Description"]."</option>";
}

$arrTipoEntrata = array();
$arrTipoEntrata[] = array("id" => "CDS" ,"descr" => "CDS/AMMINISTRATIVA");
$arrTipoEntrata[] = array("id" => "IMMOBILI" ,"descr" => "IMMOBILI");
$arrTipoEntrata[] = array("id" => "IRPEF" ,"descr" => "IRPEF");
$arrTipoEntrata[] = array("id" => "OSAP" ,"descr" => "OSAP");
$arrTipoEntrata[] = array("id" => "PATRIMONIALE" ,"descr" => "PATRIMONIALE");
$arrTipoEntrata[] = array("id" => "PUBBLICITA" ,"descr" => "PUBBLICITA");
$arrTipoEntrata[] = array("id" => "RIFIUTI" ,"descr" => "RIFIUTI");

$arrTotParziale = array();
$arrTotParziale[] = array("id" => "parziale" ,"descr" => "Parziale");
$arrTotParziale[] = array("id" => "totale" ,"descr" => "Totale");

$arrGradoRicorso = array();
$arrGradoRicorso[] = array("id" => "1" ,"descr" => "1° Grado");
$arrGradoRicorso[] = array("id" => "2" ,"descr" => "2° Grado");
$arrGradoRicorso[] = array("id" => "3" ,"descr" => "3° Grado");

$arrPrinterId = array();
$arrPrinterId[] = array("id" => 1 ,"descr" => "Sarida");
$arrPrinterId[] = array("id" => 2 ,"descr" => "Mercurio service");

$arrStato = array();
$arrStato[] = array("id" => "ANNULLATO" ,"descr" => "ANNULLATO");
$arrStato[] = array("id" => "CONSEGNATO" ,"descr" => "CONSEGNATO");
$arrStato[] = array("id" => "PAGATO" ,"descr" => "PAGATO");
$arrStato[] = array("id" => "LAVORATO" ,"descr" => "LAVORATO");
$arrStato[] = array("id" => "UPLOAD" ,"descr" => "UPLOAD");
$arrStato[] = array("id" => "CREATO" ,"descr" => "CREATO");

$arrAnomalie = array();
$arrAnomalie[] = array("id" => 23 ,"descr" => "IRREPERIBILE");
$arrAnomalie[] = array("id" => 25 ,"descr" => "DECEDUTO");

$arrayFilter = array();
switch($queryType){
    case 1:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "printer", "value" => $arrPrinterId, "isDrop" => true, "isFinalOption" => false),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoEntrata", "value" => $arrTipoEntrata, "isDrop" => true, "isFinalOption" => false),
            array("name" => "annoFlusso", "value" => array($annoFlussoDa,$annoFlussoA), "isDrop" => false, "isFinalOption" => true),
            array("name" => "rangeGiorni", "value" => $rangeGiorni, "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_1.php";
        $title["value"] = "FLUSSI CHE NON HANNO RICEVUTO ESITO O ANOMALIE";
        break;
    case 2:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoEntrata", "value" => $arrTipoEntrata, "isDrop" => true, "isFinalOption" => false),
            array("name" => "annoFlusso", "value" => array($annoFlussoDa,$annoFlussoA), "isDrop" => false, "isFinalOption" => true),
            array("name" => "rangeGiorni", "value" => $rangeGiorni, "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_2.php";
        $title["value"] = "FLUSSI NON UPLOADATI CHE HANNO RICEVUTO ESITO";
        break;
    case 3:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "printer", "value" => $arrPrinterId, "isDrop" => true, "isFinalOption" => false),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoEntrata", "value" => $arrTipoEntrata, "isDrop" => true, "isFinalOption" => false),
            array("name" => "annoFlusso", "value" => array($annoFlussoDa,$annoFlussoA), "isDrop" => false, "isFinalOption" => true),
            array("name" => "rangeGiorni", "value" => $rangeGiorni, "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_7.php";
        $title["value"] = "FLUSSI CON IMPORTAZIONI DELLE NOTIFICHE PARZIALI";
        break;
    case 4:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "printer", "value" => $arrPrinterId, "isDrop" => true, "isFinalOption" => false),
            array("name" => "anomalie", "value" => $arrAnomalie, "isDrop" => true, "isFinalOption" => false),
            array("name" => "rangeGiorni", "value" => $rangeGiorni, "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_9.php";
        $title["value"] = "FLUSSI CON POSIZIONI SENZA NOTIFICHE OPPURE CON ANOMALIE";
        break;
    case 5:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "stato", "value" => $arrStato, "isDrop" => true, "isFinalOption" => false),
        );
        $action = "print_stampa_guidata_8.php";
        $title["value"] = "CONTROLLO STATUS FLUSSI";
        break;
    case 6:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true)
        );
        $action = "print_stampa_guidata_3.php";
        $title["value"] = "UTENTI DECEDUTI CHE NON HANNO INDIRIZZO DI RESIDENZA";
        break;
    case 7:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "parzTot", "value" => $arrTotParziale, "isDrop" => true, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_4.php";
        $title["value"] = "PARTITE SENZA PAGAMENTI SULL'ULTIMO ATTO E CON PAGAMENTI PRECEDENTI";
        break;
    case 8:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "rangeGiorni", "value" => $rangeGiorni, "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_5.php";
        $title["value"] = "ULTIMO ATTO SOLLECITO SENZA ATTI SUCCESSIVI DOPO X GIORNI DALLA DATA DI STAMPA";
        break;
    case 9:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "rangeGiorni", "value" => $rangeGiorni, "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_6.php";
        $title["value"] = "ATTI CON PAGAMENTI PARZIALI FERMI DA X GIORNI DALLA DATA DI PAGAMENTO";
        break;
    case 10:
        $arrayFilter = array(
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "annoPagamento", "value" => array($annoPagamentoDa,$annoPagamentoA), "isDrop" => false, "isFinalOption" => true),
            array("name" => "tipoEntrata", "value" => $arrTipoEntrata, "isDrop" => true, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_10.php";
        $title["value"] = "PAGAMENTI COMUNI DIVISI PER TIPO ENTRATA";
        break;
    case 11:
        $arrayFilter = array(
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoEntrata", "value" => $arrTipoEntrata, "isDrop" => true, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_11.php";
        $title["value"] = "PARTITE BLOCCATE SENZA MOTIVAZIONE";
        break;
    case 12:
        $arrayFilter = array(
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "dataInserimento", "value" => array($dataInserimentoDa,$dataInserimentoA), "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_12.php";
        $title["value"] = "ELENCO POSIZIONI IMPORTATE";
        break;
    case 13:
        $arrayFilter = array(
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "annoCrono", "value" => array($da_anno_crono,$a_anno_crono), "isDrop" => false, "isFinalOption" => false),
            array("name" => "idCrono", "value" => array($da_id_crono,$a_id_crono), "isDrop" => false, "isFinalOption" => false),
            array("name" => "dataUdienza", "value" => array($dataUdienzaDa,$dataUdienzaA), "isDrop" => false, "isFinalOption" => false),
            array("name" => "gradoRicorso", "value" => $arrGradoRicorso, "isDrop" => true, "isFinalOption" => false),
            array("name" => "autorita", "value" => $optAuthorityType, "isDrop" => true, "isFinalOption" => true)
        );
        $action = "print_stampa_guidata_13.php";
        $title["value"] = "ELENCO POSIZIONI CON RICORSO/I ATTIVO/I";
        break;
    case 14:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoEntrata", "value" => $arrTipoEntrata, "isDrop" => true, "isFinalOption" => false),
            array("name" => "utente_da", "value" => array($daco,$dano), "isDrop" => false, "isFinalOption" => false),
            array("name" => "utente_a", "value" => array($acog,$anom), "isDrop" => false, "isFinalOption" => false),
            array("name" => "partita", "value" => array($da_partita,$a_partita), "isDrop" => false, "isFinalOption" => false),
            array("name" => "annoCrono", "value" => array($da_anno_crono,$a_anno_crono), "isDrop" => false, "isFinalOption" => false),
            array("name" => "idCrono", "value" => array($da_id_crono,$a_id_crono), "isDrop" => false, "isFinalOption" => false),
        );
        $action = "print_stampa_guidata_14.php";
        $title["value"] = "ELENCO PARTITE SOSPESE";
        break;
    case 15:
        $arrayFilter = array(
            array("name" => "ente", "value" => $optionsCities, "isDrop" => true, "isFinalOption" => true),
            array("name" => "tipoFile", "value" => $optFileType, "isDrop" => true, "isFinalOption" => true),
            array("name" => "banca", "value" => $optionsBanks, "isDrop" => true, "isFinalOption" => true),
            array("name" => "dataNotifica", "value" => array($dataNotificaDa,$dataNotificaA), "isDrop" => false, "isFinalOption" => false)
        );
        $action = "print_stampa_guidata_15.php";
        $title["value"] = "ELENCO PIGORAMENTI PRESSO BANCA";
        break;
    default: break;
}

$htmlFilter = new BuildFilter($arrayFilter);

?>

<!-- GESTIONE MODALI -->
<!-- Inclusione modale per ricerca utente-->
<?php include_once(ROOT . "/search_modal/offcanvas/user_sosp_offcanvas.php"); ?>
<script>
    var selectRif = "";
    // Modali offcanvas
        //Apertura modale modifica campo
        function openOfcanvas(id_off,rif){
        selectRif = rif;
        // Reset campi input
        $('#user_name').val("");
        $('#user_cf').val("");

        // Reset spazi tabella
        $('#appendTableUser').empty();

        flagAQjaxReserch = true;
        switch (id_off){
            case 'userSospSearchModal':
                //Inizializzazione dati per ricerca utente
                //user_S = "u_name";
                //alert(all_city);
                $("#ins_u_cf").hide();
                $("#ins_u_name").show();
                document.getElementById('check_u_name').checked = true;
                document.getElementById('check_u_cf').checked = false;
                $('#userSospSearchModal').modal('show');
                break;
            default:
                break;
        }
        // Vecchia distinzione tra chiamate
        /*
        if (id_off=='addrSearchModal'){

        }
        //Ricerca Paese o Comune
        else {

        }
        */
    }
    // Iserimento dati da modale a pagine
    function initialId(type,val){
        switch (type){
            case 'user_sosp':
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
</script>
<!-- -->

<script>
    var queryType = "<?= $queryType; ?>";
    var titolo = "";
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="stampe_guidate.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        if($("#query_type").val() == "")
        {
            alert("Selezionare il tipo di query");
            return false;
        }
        //$("#print").val("yes");
        //$("#form_stampe_guidate").submit();
        ajaxCall();
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Stampe_Guidate.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Stampa</b>");
        $("#helpModal").modal('show');
    }

    $( document ).ready(function() {
        $("#CC").val("<?= $CC; ?>");
        $("#file_type").val("<?= $fileType; ?>");
        $("#query_type").val("<?= $queryType; ?>");
        $("#tipo_entrata").val("<?= $tipoEntrata; ?>");
        $("#Court_Level").val("<?= $gradoRicorso; ?>");
        $("#autority_type").val("<?= $autorita; ?>");
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
    });

    function recallPage(el){
        $("#print").val("");
        location.href = "stampe_guidate.php?c=<?= $c; ?>&a=<?= $a; ?>&query_type="+el.value;
    }

    function ajaxCall() {
		spinner.startSpinner();
		switch(parseInt(queryType)){
            case 1:
                titolo = "Flussi senza esito o anomali";
                break;
            case 2:
                titolo = "Flussi con esito non uploadati";
                break;
            case 3:
                titolo = "Flussi con importazioni parziali delle notifiche";
                break;
            case 4:
                titolo = "Flussi con posizioni senza notifiche o con anomalie";
                break;
            case 5:
                titolo = "Controllo status flussi";
                break;
            case 6:
                titolo = "Utenti deceduti senza indirizzo di residenza";
                break;
            case 7:
                titolo = "Partite con pagamenti eccetto sull'ultimo atto";
                break;
            case 8:
                titolo = "Ultimo atto sollecito senza atti successivi dopo "+$("#range_giorni").val()+" giorni dall adata di stampa";
                break;
            case 9:
                titolo = "Atti con pagamenti parziali fermi da "+$("#range_giorni").val()+" giorni dalla data di pagamento";
                break;
            case 10:
                titolo = "Pagamenti comuni divirsi per tipo entrata";
                break;
            case 11:
                titolo = "Partite bloccate senza motivazione";
                break;
            case 12:
                titolo = "Posizioni importate";
                break;
            case 13:
                titolo = "Posizioni con ricorso/i attivo/i";
                break;
            case 14:
                titolo = "Partite sospese";
            case 15:
                titolo = "Pignoramenti presso banca";
                break;
            default: break;
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

<form action="<?php echo $action; ?>" method="post" name="form_stampe_guidate" id="form_stampe_guidate">
    <input type=hidden name="c" value="<?php echo $c; ?>" />
    <input type=hidden name="a" value="<?php echo $a; ?>" />
    <input type=hidden name="print" id="print" value="" />
    <input type="hidden" name="title" value="<?php echo $title["value"]; ?>" />

    <div class="row justify-content-md-center" style="margin-bottom: 3%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Stampe di controllo guidate</span>
        </div>
    </div>
    <div class="row" >
        <div class="col col-lg-10 col-lg-offset-1" >
            <label class="col-lg-2 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Tipo di stampa</label>
            <div class="form-group">
                <div class="col-lg-10">
                    <select id="query_type" name="query_type" class="form-control resize vld_req" tabindex=1 onchange="recallPage(this);">
                        <option></option>
                        <option value="1">1) FLUSSI SENZA ESITO DI NOTIFICA O ANOMALIE ENTRO X GIORNI</option>
                        <option value="2">2) FLUSSI NON UPLOADATI SENZA ESITO ENTRO X GIORNI</option>
                        <option value="3">3) FLUSSI CON IMPORTAZIONI DELLE NOTIFICHE PARZIALI</option>
                        <option value="4">4) DETTAGLIO STAMPA N° 3, ELENCO POSIZIONI SENZA NOTIFICHE CON FILTRO ANOMALIE</option>
                        <option value="5">5) CONTROLLO PER STATUS FLUSSI</option>
                        <option value="6">6) UTENTI DECEDUTI CHE NON HANNO INDIRIZZO DI RESIDENZA</option>
                        <option value="7">7) PARTITE CHE HANNO L'ULTIMO ATTO SENZA PAGAMENTI E HANNO PAGAMENTI PRECEDENTI</option>
                        <option value="8">8) PARTITE CHE HANNO L'ULTIMO ATTO SOLLECITO SENZA ATTI SUCCESSIVI DOPO X GIORNI DALLA DATA DI STAMPA</option>
                        <option value="9">9) PARTITE CHE HANNO L'ULTIMO ATTO CON PAGAMENTO PARZIALE FERMO DA X GIORNI DALLA DATA DI PAGAMENTO</option>
                        <option value="10">10) PAGAMENTI COMUNI DIVISI PER TIPO ENTRATA</option>
                        <option value="11">11) PARTITE BLOCCATE SENZA MOTIVAZIONE</option>
                        <option value="12">12) ELENCO POSIZIONI IMPORTATE</option>
                        <option value="13">13) STAMPA POSIZIONI CON RICORSO/I IN ATTO</option>
                        <option value="14">14) ELENCO PARTITE SOSPESE</option>
                        <option value="15">15) ELENCO PIGNORAMENTI PRESSO BANCA</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <?php echo $htmlFilter->getHtml(); ?>
</form>

<?php include(INC."/footer.php");?>
