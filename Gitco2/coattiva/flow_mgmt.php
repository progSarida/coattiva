<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

include_once(CLS."/cls_registry.php");
include_once(CLS."/cls_html.php");
include_once(CLS."/cls_flow.php");
include_once(CLS."/cls_Utils.php");

$cls_utils = new cls_Utils();

$activeTab         = $cls_help->getVar("activeTab");
$filterFlowPrinter = $cls_help->getVar("filterFlowPrinter");

$printer = $filterFlowPrinter == "" ? "" : $filterFlowPrinter;

// ---------- PRINTER SELECT ----------
$query_printer = "SELECT * FROM printer ORDER BY Name ASC";
$a_printer     = $cls_db->getResults($cls_db->ExecuteQuery($query_printer), "array", "Id");
$a_selection   = array("value" => "Id", "firstOpt" => 0, "selected" => $printer, "text" => array("[Name]"));
$optPrinter    = $cls_html->getOptions($a_printer, $a_selection);

// ---------- TAB ATTIVO ----------
$current2 = null;
$current1 = "current";
if ($activeTab == 2) {
    $current1 = null;
    $current2 = "current";
} else {
    if ($activeTab == null) {
        $activeTab == '1';
    }
}

// ---------- FILTRI ----------
$filterInvoice['Number'] = $cls_help->getVar("filterInvoiceNumber");
$filterInvoice['Year']   = $cls_help->getVar("filterInvoiceYear");
$filterInvoice['Date']   = $cls_help->toDbDate($cls_help->getVar("filterInvoiceDate"));

$filterFlow['Number']       = $cls_help->getVar("filterFlowNumber");
$filterFlow['Year']         = $cls_help->getVar("filterFlowYear");
$filterFlow['Status']       = $cls_help->getVar("filterFlowStatus");
$filterFlow['MissStatus']   = $cls_help->getVar("filterFlowMissStatus");
$filterFlow['StatusOfDate'] = $cls_help->getVar("filterFlowStatusOfDate");
$filterFlow['PrinterId']    = $cls_help->getVar("filterFlowPrinter");
$filterFlow['StatusDate']   = $cls_help->toDbDate($cls_help->getVar("filterFlowStatusDate"));

if ($_SESSION['aut_tipo'] != 1) {
    $filterFlow['CityId'] = $c;
    $readonlyCity         = "disabled";
    $readonlyCityClass    = "sfondo_grigio";
} else {
    $filterFlow['CityId'] = $cls_help->getVar("filterFlowCityId");
    $readonlyCity         = "";
    $readonlyCityClass    = "";
}

// ---------- COMUNI (dopo $filterFlow per evitare undefined variable) ----------
$queryCities = "SELECT EG.*
                FROM enti_gestiti EG
                LEFT JOIN anni_gestiti A ON A.CC_Anno = EG.CC
                WHERE A.ID IS NOT NULL
                GROUP BY EG.ID
                ORDER BY EG.Denominazione";
$a_enti = $cls_db->getResults($cls_db->SelectQuery($queryCities));

$a_selection_city = array(
    "value"    => "CC",
    "firstOpt" => 0,
    "selected" => $filterFlow['CityId'],
    "text"     => array("[Denominazione]", " - ", "[CC]")
);
$optCity = $cls_html->getOptions($a_enti, $a_selection_city);

// Lookup CC => Denominazione per la tabella flussi
$a_cityNames = array();
foreach ($a_enti as $ente) {
    $a_cityNames[$ente['CC']] = $ente['Denominazione'];
}

// ---------- ORDINAMENTO E PAGINAZIONE ----------
$FlowOrderName = $cls_help->getVar("FlowOrderName");
$FlowOrder     = $cls_help->getVar("FlowOrder");
if ($FlowOrder == null) $FlowOrder = 0;

$InvoiceOrderName = $cls_help->getVar("InvoiceOrderName");
$InvoiceOrder     = $cls_help->getVar("InvoiceOrder");

$flowPage    = $cls_help->getVar("flowPage");
if ($flowPage == null) $flowPage = 1;
$invoicePage = $cls_help->getVar("invoicePage");
if ($invoicePage == null) $invoicePage = 1;

// ---------- FATTURE (usa ancora cls_flow per filtro/order fatture) ----------
$cls_flow        = new cls_flow($c);
$whereInvoice    = $cls_flow->filterQuery("flow_invoices", $filterInvoice);
$getInvoiceOrder = $cls_flow->filterOrder("flow_invoices", $InvoiceOrderName, $InvoiceOrder);

$cls_html = new cls_html();

$a_invoicesSelect = $cls_db->getResults($cls_db->SelectQuery($cls_flow->getInvoicesListQuery("flow_invoices")));
$a_invoices       = $cls_db->getResults($cls_db->SelectQuery($cls_flow->getInvoicesListQuery("flow_invoices", $whereInvoice, 20, $invoicePage, $getInvoiceOrder)));
$totInvoices      = $cls_db->foundRows();

// ---------- BANCHE E TIPO ENTRATA ----------
$a_banks   = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM payment_bank"));
$a_taxType = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM tax_type"));

// ---------- NUMERO ULTIMA FATTURA ----------
$result_MaxInvoiceNumber = $cls_db->getArrayLine($cls_db->SelectQuery(
    "SELECT MAX(Number) AS Number FROM flow_invoices WHERE Year=" . date("Y")
));
$lastInvoiceNumber = 0;
if (isset($result_MaxInvoiceNumber['Number']))
    $lastInvoiceNumber = $result_MaxInvoiceNumber['Number'];

// ==========================================================================
// QUERY DIRETTA: totali per fattura calcolati in SQL
// Sostituisce $a_flowsSelect caricato intero + doppio loop PHP annidato
// Un'unica query GROUP BY restituisce spese postali e stampa per ogni fattura
// ==========================================================================
$query_totals = "
    SELECT
        fi.Id AS InvoiceId,
        SUM(
            CASE WHEN f.PostageInvoiceId = fi.Id THEN
                COALESCE(f.Zone0Postage,0) * COALESCE(f.Zone0Number,0) +
                COALESCE(f.Zone1Postage,0) * COALESCE(f.Zone1Number,0) +
                COALESCE(f.Zone2Postage,0) * COALESCE(f.Zone2Number,0) +
                COALESCE(f.Zone3Postage,0) * COALESCE(f.Zone3Number,0)
            ELSE 0 END
        ) AS TotalePostale,
        SUM(
            CASE WHEN f.PrintInvoiceId = fi.Id THEN
                COALESCE(f.PrintCost,0) * COALESCE(f.RecordsNumber,0)
            ELSE 0 END
        ) AS TotaleStampa
    FROM flow_invoices fi
    LEFT JOIN flows f ON f.PrintInvoiceId = fi.Id OR f.PostageInvoiceId = fi.Id
    GROUP BY fi.Id
";
$a_totals = $cls_db->getResults($cls_db->SelectQuery($query_totals), "array", "InvoiceId");

// ==========================================================================
// QUERY DIRETTA: flussi con almeno una fattura assegnata (dettaglio riga)
// Sostituisce il caricamento di TUTTI i flussi filtrato poi in PHP per fattura
// ==========================================================================
$query_flowsWithInvoice = "
    SELECT
        f.Id,
        f.Number,
        f.Year,
        f.CityId,
        f.PrintInvoiceId,
        f.PostageInvoiceId,
        dt.Description AS DocumentType,
        COALESCE(f.Zone0Postage,0) * COALESCE(f.Zone0Number,0) +
        COALESCE(f.Zone1Postage,0) * COALESCE(f.Zone1Number,0) +
        COALESCE(f.Zone2Postage,0) * COALESCE(f.Zone2Number,0) +
        COALESCE(f.Zone3Postage,0) * COALESCE(f.Zone3Number,0) AS TotalePostale,
        COALESCE(f.PrintCost,0) * COALESCE(f.RecordsNumber,0) AS TotaleStampa
    FROM flows f
    JOIN document_type dt ON dt.Id = f.DocumentTypeId
    WHERE f.PrintInvoiceId IS NOT NULL OR f.PostageInvoiceId IS NOT NULL
    ORDER BY f.Id
";
$a_flowsSelect = $cls_db->getResults($cls_db->SelectQuery($query_flowsWithInvoice));

// ==========================================================================
// QUERY DIRETTA: flussi senza fattura completa e gia spediti (select fattura)
// Sostituisce array_filter su tutti i flussi
// ==========================================================================
$query_optFlows = "
    SELECT f.Id, f.Number, f.Year, f.CityId, dt.Description AS DocumentType
    FROM flows f
    JOIN document_type dt ON dt.Id = f.DocumentTypeId
    WHERE f.SendDate IS NOT NULL
      AND (f.PrintInvoiceId IS NULL OR f.PostageInvoiceId IS NULL)
    ORDER BY f.Id DESC
";
$a_optionsFlows = $cls_db->getResults($cls_db->SelectQuery($query_optFlows));
$a_selection    = array("value" => "Id", "firstOpt" => 0, "selected" => null,
                        "text"  => array("n. ", "[Number]", "/", "[Year]", " ", "[DocumentType]", " ", "[CityId]"));
$opt_flows = $cls_html->getOptions($a_optionsFlows, $a_selection);

// ==========================================================================
// QUERY DIRETTA: lista flussi paginata senza passare per v_flows
// Inlinea le 3 sub-view lente (v_flows_date_notifica, v_flows_anomalia_notifica,
// v_flows_not_importate) che a loro volta usano v_atti_pigno -> catena profonda
// ==========================================================================

// WHERE dinamico dal filtro
$whereFlowParts = array("1=1");

if (!empty($filterFlow['Number']))
    $whereFlowParts[] = "f.Number = " . intval($filterFlow['Number']);
if (!empty($filterFlow['Year']))
    $whereFlowParts[] = "f.Year = " . intval($filterFlow['Year']);
if (!empty($filterFlow['PrinterId']))
    $whereFlowParts[] = "f.PrinterId = " . intval($filterFlow['PrinterId']);
if (!empty($filterFlow['CityId']))
    $whereFlowParts[] = "f.CityId = '" . preg_replace('/[^A-Z0-9]/i', '', $filterFlow['CityId']) . "'";

// Filtro Status attivo
if (!empty($filterFlow['Status'])) {
    $statusMap = array(
        1 => "f.CreationDate IS NOT NULL AND f.UploadDate IS NULL AND f.ProcessingDate IS NULL AND f.PostagePaymentDate IS NULL AND f.SendDate IS NULL AND f.CancelDate IS NULL",
        2 => "f.UploadDate IS NOT NULL AND f.ProcessingDate IS NULL AND f.PostagePaymentDate IS NULL AND f.SendDate IS NULL AND f.CancelDate IS NULL",
        3 => "f.ProcessingDate IS NOT NULL AND f.PostagePaymentDate IS NULL AND f.SendDate IS NULL AND f.CancelDate IS NULL",
        4 => "f.PostagePaymentDate IS NOT NULL AND f.SendDate IS NULL AND f.CancelDate IS NULL",
        5 => "f.SendDate IS NOT NULL AND f.CancelDate IS NULL",
        6 => "f.CancelDate IS NOT NULL"
    );
    if (isset($statusMap[intval($filterFlow['Status'])]))
        $whereFlowParts[] = $statusMap[intval($filterFlow['Status'])];
}

// Filtro Status mancante
if (!empty($filterFlow['MissStatus'])) {
    $missStatusMap = array(
        1 => "f.CreationDate IS NULL",
        2 => "f.UploadDate IS NULL",
        3 => "f.ProcessingDate IS NULL",
        4 => "f.PostagePaymentDate IS NULL",
        5 => "f.SendDate IS NULL"
    );
    if (isset($missStatusMap[intval($filterFlow['MissStatus'])]))
        $whereFlowParts[] = $missStatusMap[intval($filterFlow['MissStatus'])];
}

// Filtro Data per tipo status
if (!empty($filterFlow['StatusDate']) && !empty($filterFlow['StatusOfDate'])) {
    $dateFieldMap = array(
        1 => "f.CreationDate",
        2 => "f.UploadDate",
        3 => "f.ProcessingDate",
        4 => "f.PostagePaymentDate",
        5 => "f.SendDate",
        6 => "f.CancelDate"
    );
    if (isset($dateFieldMap[intval($filterFlow['StatusOfDate'])])) {
        $dateField        = $dateFieldMap[intval($filterFlow['StatusOfDate'])];
        $whereFlowParts[] = "DATE(" . $dateField . ") = '" . preg_replace('/[^0-9\-]/', '', $filterFlow['StatusDate']) . "'";
    }
}

$whereFlowSQL = implode(" AND ", $whereFlowParts);

// Ordinamento
$allowedFlowOrderFields = array(
    'Number'  => 'f.Number',
    'Year'    => 'f.Year',
    'CityId'  => 'f.CityId',
    'Printer' => 'p.Name',
    'printer' => 'p.Name'
);
$orderFlowSQL = "f.Id DESC";
if (!empty($FlowOrderName) && isset($allowedFlowOrderFields[$FlowOrderName])) {
    $orderFlowSQL = $allowedFlowOrderFields[$FlowOrderName] . " " . ($FlowOrder == 1 ? "DESC" : "ASC");
}

$flowOffset = ($flowPage - 1) * 20;

$queryFlow = "
    SELECT SQL_CALC_FOUND_ROWS
        f.*,
        p.Name         AS Printer,
        p.Name         AS PrinterName,
        pt.Description AS PrintType,
        dt.Description AS DocumentType,
        dt.TableTypeId AS DocumentTableTypeId,

        -- NotificationNumber: atti notificati + pignoramenti notificati del flusso
        -- Evita v_flows_date_notifica -> v_atti_pigno -> v_pignoramento (catena pesante)
        (
            SELECT COUNT(*)
            FROM atto a
            WHERE a.FlowId        = f.Id
              AND a.DocumentTypeId = f.DocumentTypeId
              AND a.CC             = f.CityId
              AND a.Data_Notifica IS NOT NULL
        ) + (
            SELECT COUNT(*)
            FROM pignoramento_generale pg
            JOIN notifica_atto na
              ON na.Atto_Notificato_ID = pg.Id
             AND na.Tipo_Notifica      = 'debitore'
             AND na.Data_Notifica     IS NOT NULL
            WHERE pg.FlowId        = f.Id
              AND pg.DocumentTypeId = f.DocumentTypeId
              AND pg.CC             = f.CityId
        ) AS NotificationNumber,

        -- AnomalyNumber: anomalie senza data notifica
        -- Evita v_flows_anomalia_notifica -> v_atti_pigno
        (
            SELECT COUNT(*)
            FROM atto a
            WHERE a.FlowId        = f.Id
              AND a.DocumentTypeId = f.DocumentTypeId
              AND a.CC             = f.CityId
              AND a.Motivo_Notifica > 0
              AND a.Data_Notifica IS NULL
        ) + (
            SELECT COUNT(*)
            FROM pignoramento_generale pg
            JOIN notifica_atto na
              ON na.Atto_Notificato_ID = pg.Id
             AND na.Tipo_Notifica      = 'debitore'
             AND na.Motivo_Notifica    > 0
             AND na.Data_Notifica     IS NULL
            WHERE pg.FlowId        = f.Id
              AND pg.DocumentTypeId = f.DocumentTypeId
              AND pg.CC             = f.CityId
        ) AS AnomalyNumber,

        -- ImportationNumber: notifiche importate per questo flusso
        -- Evita v_flows_not_importate -> v_atti_pigno
        (
            SELECT COUNT(*)
            FROM notifiche_importate ni
            WHERE ni.FlowId        = f.Id
              AND ni.DocumentTypeId = f.DocumentTypeId
              AND ni.CC_Comune      = f.CityId
        ) AS ImportationNumber

    FROM flows f
    JOIN printer       p  ON p.Id  = f.PrinterId
    JOIN print_type    pt ON pt.Id = f.PrintTypeId
    JOIN document_type dt ON dt.Id = f.DocumentTypeId
    WHERE {$whereFlowSQL}
    ORDER BY {$orderFlowSQL}
    LIMIT 20 OFFSET {$flowOffset}
";

$a_flows  = $cls_db->getResults($cls_db->SelectQuery($queryFlow));
$totFlows = $cls_db->foundRows();

?>
    <link rel=StyleSheet href="<?= CSS; ?>/tab.css" type="text/css" media=screen>
    <script>

        switchMenuImg("F3");
        F3_button = function(){
            $("#flow_form").submit();
        }
        /* GV - 03/05/2022 - START */
        switchMenuImg("F11");
        F11_button = function(){
            console.log($(this).attr('data-tab'));
            $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/FLUSSI.pdf"; ?>");
            $("#helpModalLabel").empty().append("<b>Help FLUSSI</b>");
            $("#helpModal").modal('show');
        }
        /* GV - 03/05/2022 -   END */

        switchMenuImg("F4");
        F4_button = function(){}

        function flowChange(Flow_ID){
            $("[name='FlowSave["+Flow_ID+"]'").val("y");
        }

        function invoiceChange(Invoice_ID){
            $("[name='InvoiceSave["+Invoice_ID+"]'").val("y");
        }

        function dettagli_flusso(type, row){
            if($('.'+type+'_'+row).is(":visible"))
                $('.'+type+'_'+row).hide();
            else
                $('.'+type+'_'+row).show();
        }

        function elimina_flusso(invoice_line, flow_line){
            selector = '#invoice_flow_'+invoice_line+'_'+flow_line;
            $(selector).remove();
        }

        function setFlow(invoice_line, flow_line){
            var newFlowID = $("#select_flow_"+invoice_line+"_"+flow_line).val();
            $("[name='Flow_Save["+newFlowID+"]'").val("y");
            tr = $("#invoice_flow_"+invoice_line+"_"+flow_line);
            tr.attr("id","invoice_flow_"+invoice_line+"_"+newFlowID);
            a_link = $("#a_flow_"+invoice_line+"_"+flow_line);
            a_link.attr("id","a_flow_"+invoice_line+"_"+newFlowID);
            a_link.attr("onclick","elimina_flusso("+invoice_line+","+newFlowID+")");
            $("[name='FlowPrintInvoice_ID["+invoice_line+"]["+flow_line+"]'").attr("name","FlowPrintInvoiceId["+invoice_line+"]["+newFlowID+"]");
            $("[name='FlowPostageInvoice_ID["+invoice_line+"]["+flow_line+"]'").attr("name","FlowPostageInvoiceId["+invoice_line+"]["+newFlowID+"]");
            select = $("#select_flow_"+invoice_line+"_"+flow_line);
            select.attr("onchange","setFlow("+invoice_line+","+newFlowID+")");
            select.attr("id","select_flow_"+invoice_line+"_"+newFlowID);
        }

        var inv_number = parseInt("<?php echo $lastInvoiceNumber;?>");
        var newInvoice = 0;
        function addInvoice(){
            inv_number++;
            var invoice_line = newInvoice;
            string  = "<tr class=\"riga_pari cls_invoice\" id=\"gen_invoice_"+invoice_line+"\">";
            string += "<td><input type=\"hidden\" name=\"NewInvoiceSave["+invoice_line+"]\" value=\"y\"></td>";
            string += "<td class=\"text_center\"><input class=\"width60 text_center searchInvoice\" id=\"Invoice_New_Number_"+invoice_line+"\" name=\"NewInvoiceNumber["+invoice_line+"]\" value=\""+inv_number+"\"></td>";
            string += "<td class=\"text_center\"><input class=\"width70 text_center searchInvoice\" id=\"Invoice_New_Year_"+invoice_line+"\" name=\"NewInvoiceYear["+invoice_line+"]\" value=\"<?=Date("Y");?>\"></td>";
            string += "<td class=\"text_center\"><input class=\"width85 text_center picker searchInvoice\" id=\"Invoice_New_Date_"+invoice_line+"\" name=\"NewInvoiceDate["+invoice_line+"]\" value=\"<?=Date("d/m/Y");?>\"></td>";
            string += "<td class=\"text_center\"></td><td class=\"text_center\"></td><td class=\"text_center\"></td><td></td></tr>";
            $("tr.cls_invoice:first").before(string);
            $("#Invoice_New_Date_"+invoice_line).datepicker();
            newInvoice++;
        }

        function printInvoice(id){
            window.open('<?=WEB_ROOT;?>/stampe/print_flows_invoice.php?c=<?=$c?>&a=<?=$a?>&InvoiceId='+id, 'stampa', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no');
        }

        $(document).ready(function(){
            $('ul.tabs li').click(function(){
                var tab_id = $(this).attr('data-tab');
                /* GV /05/2022 - START */
                var arr_tab_id = tab_id.split('-');
                $('#tab_attiva').val(arr_tab_id[1]);
                console.log("nr_tab: "+arr_tab_id);
                /* GV /05/2022 -  END */
                $('ul.tabs li').removeClass('current');
                $('.tab-content').removeClass('current');
                $(this).addClass('current');
                $("#"+tab_id).addClass('current');
            });
        });

        function goFilter(activeTab, page, orderName, order){
            get  = "filterInvoiceNumber="+$('[name=filterInvoiceNumber]').val()+"&filterInvoiceYear="+$('[name=filterInvoiceYear]').val()+"&";
            get += "filterInvoiceDate="+$('[name=filterInvoiceDate]').val()+"&";
            get += "filterFlowNumber="+$('[name=filterFlowNumber]').val()+"&filterFlowYear="+$('[name=filterFlowYear]').val()+"&";
            get += "filterFlowStatus="+$('[name=filterFlowStatus]').val()+"&filterFlowMissStatus="+$('[name=filterFlowMissStatus]').val()+"&";
            get += "filterFlowStatusOfDate="+$('[name=filterFlowStatusOfDate]').val()+"&filterFlowStatusDate="+$('[name=filterFlowStatusDate]').val()+"&";
            get += "filterFlowCityId="+$('[name=filterFlowCityId]').not('[type=hidden]').val()+"&";
            get += "filterFlowPrinter="+$('[name=filterFlowPrinter]').val()+"&";
            if(activeTab==1){
                if(page>0) get += "invoicePage="+page+"&";
                if(orderName!='') get += "InvoiceOrderName="+orderName+"&InvoiceOrder="+order+"&";
            } else if(activeTab==2){
                if(page>0) get += "flowPage="+page+"&";
                if(orderName!='') get += "FlowOrderName="+orderName+"&FlowOrder="+order+"&";
            }
            get += "activeTab="+activeTab;
            openLocation(pageName,get);
        }

        function callUpload(c,a,fileName,documentType,ID,stampatore,url,dataCaricamento){
            if(!fileExists(url)){alert("File da caricare inesistente"); return false;}
            if(stampatore==1){alert("Stampatore errato, (Sarida)"); return false;}
            if(dataCaricamento!="" && dataCaricamento!=null && dataCaricamento!=undefined){alert("File già inviato allo stampatore"); return false;}
            window.location.href = "<?= WEB_ROOT; ?>/stampe/Upload_File_Mercurio.php?c="+c+"&a="+a+"&stampatore="+stampatore+"&fileName="+fileName+"&docType="+documentType+"&idFlusso="+ID;
        }

        function fileExists(url){
            if(url){
                var arrPath    = url.split("/");
                if(arrPath[arrPath.length-1]=="" || arrPath[arrPath.length-1]==undefined || arrPath[arrPath.length-1]==null) return false;
                var arrFileName = arrPath[arrPath.length-1].split(".");
                if(arrFileName.length<2) return false;
                var req = new XMLHttpRequest();
                req.open('HEAD', url, false);
                req.send();
                return req.status==200;
            } else {
                return false;
            }
        }

    </script>
<br>
    <form id="flow_form" name="flow_form" action="flow_mgmt_save.php" method="post">
        <input type="hidden" name="c" value="<?=$c;?>">
        <input type="hidden" name="a" value="<?=$a;?>">
        <!-- GV - 23/05/2022 - START -->
        <input type="hidden" id="tab_attiva" name="tab_attiva" value="<?php echo $activeTab; ?>">
        <!-- GV - 23/05/2022 -   END -->

        <div class="container">

            <span class="titolo text_center font18">Gestione stampatore</span>
            <br><br>
            <div></div>

            <ul class="tabs">
                <li class="tab-link <?=$current1?>" data-tab="tab-1"><span class="titolo">FATTURE</span></li>
                <li class="tab-link <?=$current2?>" data-tab="tab-2"><span class="titolo">FLUSSI</span></li>
            </ul>

            <!-- ============================= TAB FATTURE ============================= -->
            <div id="tab-1" class="tab-content <?=$current1?>">
                <br>
                <table class="width95 text_center" border="0">
                    <tr>
                        <td class="text_left" colspan="2"><span class="titolo">Filtro fattura</span></td>
                        <td class="text_right" colspan="6">
                            <a onMouseover="title='Pagina precedente'" href="#" style="text-decoration:none;" onClick="goFilter(1,<?=($invoicePage-1)?>,'',0);">
                                <img src="<?= IMMAGINIWEB; ?>/prev.png" style="width:15px;height:15px;border:0;">
                            </a>
                            <span class="color_titolo"><b>Pagina</b></span>
                            <input name="invoicePage" class="pwidth40 text_right" onchange="goFilter(1,this.value,'',0);" value="<?=$invoicePage;?>">
                            <a onMouseover="title='Pagina successiva'" href="#" style="text-decoration:none;" onClick="goFilter(1,<?=($invoicePage+1)?>,'',0);">
                                <img src="<?= IMMAGINIWEB; ?>/next.png" style="width:15px;height:15px;border:0;">
                            </a>
                            <span class="color_titolo"><b>di <?php echo ceil($totInvoices/20);?></b></span>
                        </td>
                    </tr>
                    <tr><td class="text_left" colspan="8"><hr></td></tr>
                    <tr class="riga_pari">
                        <td class="text_center" colspan="2">Numero <input class="text_right width35" type="text" name="filterInvoiceNumber" value="<?=$filterInvoice['Number'];?>"></td>
                        <td class="text_center" colspan="2">Anno <input class="text_right width35" type="text" name="filterInvoiceYear" value="<?=$filterInvoice['Year'];?>"></td>
                        <td class="text_center">Data <input class="text_center picker width50" type="text" name="filterInvoiceDate" value="<?=$cls_help->toItalianDate($filterInvoice['Date']);?>"></td>
                        <td class="riga_dispari text_center"></td>
                        <td class="riga_dispari text_center"><input type="button" name="submitFilterInvoice" value="FILTRA" onclick="goFilter(1,0,'',0);"></td>
                        <td class="riga_dispari width6"></td>
                    </tr>
                    <tr><td class="text_left" colspan="8"><hr></td></tr>
                    <tr>
                        <td class="width5">
                            <a onMouseover="title='Aggiungi Fattura'" href="#" style="text-decoration:none;" onClick="addInvoice(<?=count($a_invoices);?>);">
                                <img src="<?= IMMAGINIWEB; ?>/plus.png" style="width:15px;height:15px;border:0;">
                            </a>
                        </td>
                        <td class="width10">
                            <a href="#" style="text-decoration:none;" onClick="goFilter(1,0,'Number',0);"><img src="<?= IMMAGINIWEB; ?>/up.png" style="width:15px;height:15px;border:0;"></a><br>
                            <span class="color_titolo"><b>Numero</b></span><br>
                            <a href="#" style="text-decoration:none;" onClick="goFilter(1,0,'Number',1);"><img src="<?= IMMAGINIWEB; ?>/down.png" style="width:15px;height:15px;border:0;"></a>
                        </td>
                        <td class="width10">
                            <a href="#" style="text-decoration:none;" onClick="goFilter(1,0,'Year',0);"><img src="<?= IMMAGINIWEB; ?>/up.png" style="width:15px;height:15px;border:0;"></a><br>
                            <span class="color_titolo"><b>Anno</b></span><br>
                            <a href="#" style="text-decoration:none;" onClick="goFilter(1,0,'Year',1);"><img src="<?= IMMAGINIWEB; ?>/down.png" style="width:15px;height:15px;border:0;"></a>
                        </td>
                        <td class="width15">
                            <a href="#" style="text-decoration:none;" onClick="goFilter(1,0,'Date',0);"><img src="<?= IMMAGINIWEB; ?>/up.png" style="width:15px;height:15px;border:0;"></a><br>
                            <span class="color_titolo"><b>Data</b></span><br>
                            <a href="#" style="text-decoration:none;" onClick="goFilter(1,0,'Date',1);"><img src="<?= IMMAGINIWEB; ?>/down.png" style="width:15px;height:15px;border:0;"></a>
                        </td>
                        <td class="width18"><span class="color_titolo"><b>Stampa e imbustamento</b></span></td>
                        <td class="width18"><span class="color_titolo"><b>Spese postali</b></span></td>
                        <td class="width18"><span class="color_titolo"><b>Totale</b></span></td>
                        <td class="width6"></td>
                    </tr>

                    <?php
                    for ($k = 0; $k < count($a_invoices); $k++) {
                        $i = $a_invoices[$k]['Id'];

                        // Totali pre-calcolati da SQL: nessun loop PHP annidato
                        $invoiceTotalPostage = isset($a_totals[$i]['TotalePostale']) ? $a_totals[$i]['TotalePostale'] : 0;
                        $invoicePrintCost    = isset($a_totals[$i]['TotaleStampa'])  ? $a_totals[$i]['TotaleStampa']  : 0;

                        // Flussi collegati a questa fattura (gia filtrati: solo quelli con fattura)
                        $filterBy       = $i;
                        $a_invoiceflows = array_values(array_filter($a_flowsSelect, function ($var) use ($filterBy) {
                            return ($var['PrintInvoiceId'] == $filterBy || $var['PostageInvoiceId'] == $filterBy);
                        }));
                        ?>
                        <tr class="riga_pari cls_invoice" id="gen_invoice_<?=$i;?>">
                            <td>
                                <a onMouseover="title='Visualizza Flussi'" href="#" style="text-decoration:none;" onClick="dettagli_flusso('invoice',<?=$i;?>)">
                                    <img src="<?= IMMAGINIWEB; ?>/select.png" style="width:15px;height:15px;border:0;">
                                </a>
                                <input type="hidden" name="InvoiceSave[<?=$i;?>]" value="n">
                            </td>
                            <td class="text_center">
                                <input class="width60 text_center searchInvoice" id="Invoice_Number_<?=$i;?>" name="InvoiceNumber[<?=$i;?>]"
                                       value="<?=$a_invoices[$k]['Number'];?>" onchange="invoiceChange(<?=$i;?>)">
                            </td>
                            <td class="text_center">
                                <input class="width70 text_center searchInvoice" id="Invoice_Year_<?=$i;?>" name="InvoiceYear[<?=$i;?>]"
                                       value="<?=$a_invoices[$k]['Year'];?>" onchange="invoiceChange(<?=$i;?>)">
                            </td>
                            <td class="text_center">
                                <input class="width85 text_center picker searchInvoice" id="Invoice_Date_<?=$i;?>" name="InvoiceDate[<?=$i;?>]"
                                       value="<?=$cls_help->toItalianDate($a_invoices[$k]['Date']);?>" onchange="invoiceChange(<?=$i;?>)">
                            </td>
                            <td class="text_right"><?=$cls_help->floatToString($invoiceTotalPostage);?> &euro;&nbsp;</td>
                            <td class="text_right"><?=$cls_help->floatToString($invoicePrintCost);?> &euro;&nbsp;</td>
                            <td class="text_right"><?=$cls_help->floatToString($invoiceTotalPostage + $invoicePrintCost);?> &euro;&nbsp;</td>
                            <td class="width6">
                                <a onMouseover="title='Stampa Fattura'" href="#" style="text-decoration:none;" onClick="printInvoice(<?=$i;?>);">
                                    <img src="<?= IMMAGINIWEB; ?>/icon_pdf.png" style="width:18px;height:18px;border:0;">
                                </a>
                            </td>
                        </tr>

                        <?php
                        foreach ($a_invoiceflows as $af) {
                            $totalPostage = ($af['PostageInvoiceId'] == $filterBy) ? $af['TotalePostale'] : 0;
                            $printCost    = ($af['PrintInvoiceId']   == $filterBy) ? $af['TotaleStampa']  : 0;
                            $y = $af['Id'];
                            ?>
                            <tr class="invoice_<?=$i;?> color_table" id="invoice_flow_<?=$i;?>_<?=$y;?>" style="display:none;">
                                <td></td>
                                <td colspan="3" class="text_left">
                                    n. <?=$af['Number']."/".$af['Year'];?> <?=$af['DocumentType'];?> <?=$af['CityId'];?>
                                </td>
                                <td class="text_right"><?=$cls_help->floatToString($totalPostage);?> &euro;&nbsp;</td>
                                <td class="text_right"><?=$cls_help->floatToString($printCost);?> &euro;&nbsp;</td>
                                <td class="text_right"><?=$cls_help->floatToString($totalPostage + $printCost);?> &euro;&nbsp;</td>
                                <td class="width6"></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
                <br>
            </div>

            <!-- ============================= TAB FLUSSI ============================= -->
            <div id="tab-2" class="tab-content <?=$current2?>">
                <br>
                <table class="width95 text_center" border="0">
                    <tr>
                        <td class="text_left" colspan="3"><span class="titolo">Filtro flusso</span></td>
                        <td class="text_right" colspan="7">
                            <a onMouseover="title='Visualizza Flussi'" href="#" style="text-decoration:none;" onClick="goFilter(2,<?=($flowPage-1)?>,'<?=$FlowOrderName;?>',<?=$FlowOrder;?>);">
                                <img src="<?= IMMAGINIWEB; ?>/prev.png" style="width:15px;height:15px;border:0;">
                            </a>
                            <span class="color_titolo"><b>Pagina</b></span>
                            <input name="flowPage" class="pwidth40 text_right" onchange="goFilter(2,this.value,'',0);" value="<?=$flowPage;?>">
                            <a onMouseover="title='Visualizza Flussi'" href="#" style="text-decoration:none;" onClick="goFilter(2,<?=($flowPage+1)?>,'<?=$FlowOrderName;?>',<?=$FlowOrder;?>);">
                                <img src="<?= IMMAGINIWEB; ?>/next.png" style="width:15px;height:15px;border:0;">
                            </a>
                            <span class="color_titolo"><b>di <?php echo ceil($totFlows/20);?></b></span>
                        </td>
                    </tr>
                    <tr><td class="text_left" colspan="10"><hr></td></tr>
                    <tr class="riga_pari">
                        <td class="text_center" colspan="2">Numero</td>
                        <td class="text_center" colspan="1"><input class="text_right pwidth90" type="text" name="filterFlowNumber" value="<?=$filterFlow['Number'];?>"></td>
                        <td colspan="1" class="text_center">Anno</td>
                        <td class="text_left" colspan="2"><input class="text_right pwidth90" type="text" name="filterFlowYear" value="<?=$filterFlow['Year'];?>"></td>
                        <td class="text_center" colspan="1">Stampatore</td>
                        <td class="text_center" colspan="2">
                            <select id="filterFlowPrinter" name="filterFlowPrinter" class="pwidth150">
                                <option></option>
                                <?= $optPrinter ?>
                            </select>
                        </td>
                        <td class="riga_dispari"><input type="button" name="submitFilterFlow" value="FILTRA" onclick="goFilter(2,0,'',0);"></td>
                    </tr>
                    <tr class="riga_pari">
                        <td></td>
                        <td class="text_left">Status attivo</td>
                        <td class="text_left">
                            <select id="filterFlowStatus" name="filterFlowStatus" class="pwidth150">
                                <option></option>
                                <option value="1">CREATO</option>
                                <option value="2">UPLOAD</option>
                                <option value="3">LAVORATO</option>
                                <option value="4">PAGATO</option>
                                <option value="5">CONSEGNATO</option>
                                <!-- GV - 29/04/2022 - START -->
                                <option value="6">ANNULLATO</option>
                                <!-- GV - 29/04/2022 -   END -->
                            </select>
                        </td>
                        <td colspan="2" class="text_left">Status mancante</td>
                        <td class="text_left" colspan="3">
                            <select id="filterFlowMissStatus" name="filterFlowMissStatus" class="pwidth150">
                                <option></option>
                                <option value="1">CREATO</option>
                                <option value="2">UPLOAD</option>
                                <option value="3">LAVORATO</option>
                                <option value="4">PAGATO</option>
                                <option value="5">CONSEGNATO</option>
                            </select>
                        </td>
                        <td></td>
                        <td class="riga_dispari"></td>
                    </tr>
                    <tr class="riga_pari">
                        <td></td>
                        <td class="text_left" colspan="2">Data
                            <select id="filterFlowStatusOfDate" name="filterFlowStatusOfDate" class="pwidth150">
                                <option></option>
                                <option value="1">Creazione</option>
                                <option value="2">Upload</option>
                                <option value="3">Fine lavorazione</option>
                                <option value="4">Pagamento</option>
                                <option value="5">Consegna</option>
                                <!-- GV - 29/04/2022 - START -->
                                <option value="6">Annullamento</option>
                                <!-- GV - 29/04/2022 -   END -->
                            </select>
                            &nbsp;&nbsp;&nbsp;&nbsp;<input name="filterFlowStatusDate" class="text_center pwidth90 picker" value="<?=$cls_help->toItalianDate($filterFlow['StatusDate']);?>">
                        </td>
                        <td colspan="2" class="text_left">Comune</td>
                        <td class="text_left" colspan="3">
                            <?php if ($_SESSION['aut_tipo'] != 1): ?>
                                <select name="filterFlowCityId" class="pwidth150 <?=$readonlyCityClass?>" disabled>
                                    <option value="<?=$filterFlow['CityId'];?>"><?=$filterFlow['CityId'];?></option>
                                </select>
                                <input type="hidden" name="filterFlowCityId" value="<?=$filterFlow['CityId'];?>">
                            <?php else: ?>
                                <select name="filterFlowCityId" class="pwidth150">
                                    <option value=""></option>
                                    <?= $optCity ?>
                                </select>
                            <?php endif; ?>
                        </td>
                        <td></td>
                        <td class="riga_dispari"></td>
                    </tr>
                    <script>
                        $('#filterFlowStatus').val("<?=$filterFlow['Status'];?>");
                        $('#filterFlowMissStatus').val("<?=$filterFlow['MissStatus'];?>");
                        $('#filterFlowStatusOfDate').val("<?=$filterFlow['StatusOfDate'];?>");
                        $('#filterFlowPrinter').val("<?=$printer;?>");
                    </script>
                    <tr><td class="text_left" colspan="10"><hr></td></tr>
                    <tr>
                        <td class="width5"></td>
                        <td class="width20" colspan="2"><span class="color_titolo"><b>Tipo atto</b></span></td>
                        <td class="width10">
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'Printer',0);"><img src="<?= IMMAGINIWEB; ?>/up.png" style="width:15px;height:15px;border:0;"></a><br>
                            <span class="color_titolo"><b>Stampatore</b></span><br>
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'printer',1);"><img src="<?= IMMAGINIWEB; ?>/down.png" style="width:15px;height:15px;border:0;"></a>
                        </td>
                        <td class="width6">
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'Year',0);"><img src="<?= IMMAGINIWEB; ?>/up.png" style="width:15px;height:15px;border:0;"></a><br>
                            <span class="color_titolo"><b>Anno</b></span><br>
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'Year',1);"><img src="<?= IMMAGINIWEB; ?>/down.png" style="width:15px;height:15px;border:0;"></a>
                        </td>
                        <td class="width6">
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'Number',0);"><img src="<?= IMMAGINIWEB; ?>/up.png" style="width:15px;height:15px;border:0;"></a><br>
                            <span class="color_titolo"><b>Numero</b></span><br>
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'Number',1);"><img src="<?= IMMAGINIWEB; ?>/down.png" style="width:15px;height:15px;border:0;"></a>
                        </td>
                        <td class="width8">
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'CityId',0);"><img src="<?= IMMAGINIWEB; ?>/up.png" style="width:15px;height:15px;border:0;"></a><br>
                            <span class="color_titolo"><b>Comune</b></span><br>
                            <a href="#" style="text-decoration:none;" onClick="goFilter(2,0,'CityId',1);"><img src="<?= IMMAGINIWEB; ?>/down.png" style="width:15px;height:15px;border:0;"></a>
                        </td>
                        <td class="width15"><span class="color_titolo"><b>Status</b></span></td>
                        <td class="width10"><span class="color_titolo"><b>Data status</b></span></td>
                        <td class="width12"><span class="color_titolo"><b>Not/Anom/Att</b></span></td>
                        <td class="width8"><span class="color_titolo"><b>Import.</b></span></td>
                    </tr>

                    <?php
                    for ($f = 0; $f < count($a_flows); $f++) {

                        // Cartella file in base al tipo documento
                        if ($a_flows[$f]["DocumentType"] == "Ingiunzione") {
                            $cartella = "Ingiunzioni";           $prefisso = "Ingiunzione_";
                        } else if ($a_flows[$f]["DocumentType"] == "Avviso di intimazione ad adempiere") {
                            $cartella = "Avvisi_di_intimazione"; $prefisso = "Avviso_di_intimazione_";
                        } else if ($a_flows[$f]["DocumentType"] == "Sollecito di pagamento") {
                            $cartella = "Solleciti";             $prefisso = "Sollecito_";
                        } else if ($a_flows[$f]["DocumentType"] == "Sollecito pre ingiunzione" || $a_flows[$f]["DocumentType"] == "SOLL_PRE") {
                            $cartella = "Solleciti_Pre_Ingiunzione"; $prefisso = "sollecitoPreIngiunzione_";
                        } else if ($a_flows[$f]["DocumentType"] == "Avviso di messa in mora" || $a_flows[$f]["DocumentType"] == "AV_MORA") {
                            $cartella = "Avvisi_Messa_In_Mora";  $prefisso = "avvisoMessaInMora_";
                        } else if ($a_flows[$f]["DocumentType"] == "Pignoramento di beni mobili registrati" || $a_flows[$f]["DocumentType"] == "veicolo") {
                            $cartella = "Pignoramenti/Veicolo";  $prefisso = "PignoramentoVeicolo_";
                        } else if ($a_flows[$f]["DocumentType"] == "Pignoramento presso banca" || $a_flows[$f]["DocumentType"] == "banca") {
                            $cartella = "Pignoramenti/Presso_Terzi/Banca";         $prefisso = "PignoramentoBanca_";
                        } else if ($a_flows[$f]["DocumentType"] == "Pignoramento presso datore di lavoro" || $a_flows[$f]["DocumentType"] == "lavoro") {
                            $cartella = "Pignoramenti/Presso_Terzi/Datore_di_Lavoro"; $prefisso = "PignoramentoLavoro_";
                        }

                        if ($a_flows[$f]["DocumentTableTypeId"] == 1) {
                            $dirFisica = ATTI . "/" . $a_flows[$f]['CityId'] . "/" . $cartella . "/FLUSSI/" . $a_flows[$f]['FileName'];
                            $dir       = SUPER_WEB_ROOT . $cls_utils->mostra_file_path($dirFisica);
                        } else {
                            $dirFisica = FLUSSI . "/" . $a_flows[$f]['Id'] . "/" . $a_flows[$f]['FileName'];
                            $dir       = FLUSSI_WEB . "/" . $a_flows[$f]['Id'] . "/" . $a_flows[$f]['FileName'];
                        }

                        $y = $a_flows[$f]['Id'];

                        $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $a_flows[$f]['PostagePaymentBankId'], "text" => array("[Name]"));
                        $opt_banks   = $cls_html->getOptions($a_banks, $a_selection);

                        $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $a_flows[$f]['PostageInvoiceId'],
                                             "text"  => array("n. ", "[Number]", " del ", "[Year]"));
                        $opt_postage = $cls_html->getOptions($a_invoicesSelect, $a_selection);

                        $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $a_flows[$f]['PrintInvoiceId'],
                                             "text"  => array("n. ", "[Number]", " del ", "[Year]"));
                        $opt_print   = $cls_html->getOptions($a_invoicesSelect, $a_selection);

                        // Stato flusso
                        if ($cls_help->toItalianDate(isset($a_flows[$f]['CancelDate']) ? $a_flows[$f]['CancelDate'] : null) != null) {
                            $step     = "<span class='color_gray'><b>ANNULLATO</b></span>";
                            $dataStep = $cls_help->toItalianDate($a_flows[$f]['CancelDate']);
                        } else if ($cls_help->toItalianDate(isset($a_flows[$f]['SendDate']) ? $a_flows[$f]['SendDate'] : null) != null) {
                            $step     = "<span class='color_green'><b>CONSEGNATO</b></span>";
                            $dataStep = $cls_help->toItalianDate($a_flows[$f]['SendDate']);
                        } else if ($cls_help->toItalianDate(isset($a_flows[$f]['PostagePaymentDate']) ? $a_flows[$f]['PostagePaymentDate'] : null) != null) {
                            $step     = "<span class='color_orange'><b>PAGATO</b></span>";
                            $dataStep = $cls_help->toItalianDate($a_flows[$f]['PostagePaymentDate']);
                        } else if ($cls_help->toItalianDate(isset($a_flows[$f]['ProcessingDate']) ? $a_flows[$f]['ProcessingDate'] : null) != null) {
                            $step     = "<span class='color_titolo'><b>LAVORATO</b></span>";
                            $dataStep = $cls_help->toItalianDate($a_flows[$f]['ProcessingDate']);
                        } else if ($cls_help->toItalianDate(isset($a_flows[$f]['UploadDate']) ? $a_flows[$f]['UploadDate'] : null) != null) {
                            $step     = "<span class='color_red'><b>UPLOAD</b></span>";
                            $dataStep = $cls_help->toItalianDate($a_flows[$f]['UploadDate']);
                        } else {
                            $step     = "<span><b>CREATO</b></span>";
                            $dataStep = $cls_help->toItalianDate(isset($a_flows[$f]['CreationDate']) ? $a_flows[$f]['CreationDate'] : null);
                        }

                        // Contatori notifiche
                        if ($a_flows[$f]['NotificationNumber'] == null) $a_flows[$f]['NotificationNumber'] = 0;
                        if ($a_flows[$f]['AnomalyNumber']      == null) $a_flows[$f]['AnomalyNumber']      = 0;
                        $okNot    = $a_flows[$f]['NotificationNumber'];
                        $noNot    = $a_flows[$f]['AnomalyNumber'];
                        $altreNot = $a_flows[$f]['RecordsNumber'] - $okNot - $noNot;
                        $notificationNumber = "<span class='color_green'>".$okNot."</span> / <span class='color_red'>".$noNot."</span> / <span class='color_titolo'>".$altreNot."</span>";

                        // Importazioni
                        if ($a_flows[$f]['ImportationNumber'] == null)
                            $importationNumber = null;
                        else if ($a_flows[$f]['ImportationNumber'] < $a_flows[$f]['RecordsNumber'])
                            $importationNumber = "<span class='color_red'>".$a_flows[$f]['ImportationNumber']."</span> / <span class='color_titolo'>".$a_flows[$f]['RecordsNumber']."</span>";
                        else
                            $importationNumber = "<span class='color_green'>".$a_flows[$f]['ImportationNumber']."</span> / <span class='color_titolo'>".$a_flows[$f]['RecordsNumber']."</span>";

                        // Etichetta comune: Denominazione - CC
                        $cityLabel = isset($a_cityNames[$a_flows[$f]['CityId']])
                            ? $a_cityNames[$a_flows[$f]['CityId']] . ' - ' . $a_flows[$f]['CityId']
                            : $a_flows[$f]['CityId'];
                        ?>

                        <tr class="riga_pari">
                            <td>
                                <a onMouseover="title='Dettagli Flusso'" href="#" style="text-decoration:none;" onClick="dettagli_flusso('flow',<?=$y;?>)">
                                    <img src="<?= IMMAGINIWEB; ?>/select.png" style="width:15px;height:15px;border:0;">
                                </a>
                            </td>
                            <td class="text_left" colspan="2"><?=$a_flows[$f]['DocumentType'];?>
                                <a href="javascript: dettaglioFlusso(<?php echo $a_flows[$f]['Id'];?>)">
                                    <img class="detais" src="<?php echo IMG; ?>/info.png" width="15" height="15" border="0">
                                </a>
                            </td>
                            <td class="text_center"><?=$a_flows[$f]['Printer'];?></td>
                            <td class="text_center"><?=$a_flows[$f]['Year'];?></td>
                            <td class="text_center"><?=$a_flows[$f]['Number'];?></td>
                            <td class="text_center"><?=$cityLabel;?></td>
                            <td class="text_center">
                                <input type="hidden" name="FlowSave[<?=$y;?>]" value="n">
                                <?=$step;?>
                            </td>
                            <td class="text_center"><?=$dataStep;?></td>
                            <td class="text_center"><?=$notificationNumber;?></td>
                            <td class="text_center"><?=$importationNumber;?></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;"><td colspan="10"><hr></td></tr>

                        <?php
                        if ($a_flows[$f]['PrinterId'] > 1) {
                            $printerLabel = "Stampatore";
                            $readonly     = "";
                            $picker       = "picker";
                        } else {
                            $printerLabel = "Flusso di archivio";
                            $readonly     = "readonly";
                            $picker       = "sfondo_grigio";
                        }
                        ?>

                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b><?=$printerLabel;?></b></span></td>
                            <td class="text_center" colspan="2"><span style="font-size:16px;"><b><?=$a_flows[$f]['PrinterName'];?></b></span></td>
                            <td class="text_center" colspan="5"></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Tipo spedizione</b></span></td>
                            <td class="text_center" colspan="2"><span style="font-size:16px;"><b><?=$a_flows[$f]['PrintType'];?></b></span></td>
                            <td class="text_center" colspan="5"></td>
                        </tr>
                        <?php if ($a_flows[$f]['TaxType'] > 0): ?>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Tipo entrata</b></span></td>
                            <td class="text_center" colspan="2"><b><?=$a_flows[$f]['TaxTypeDescription'];?></b></td>
                            <td class="text_center" colspan="5"></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($a_flows[$f]['FileName'])): ?>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Nome file</b></span></td>
                            <td class="text_center" colspan="3"><a href="<?php echo $dir; ?>"><?=$a_flows[$f]['FileName'];?></a></td>
                            <td class="text_center" colspan="4"></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (file_exists($dirFisica) && $a_flows[$f]["FileName"] != "" && $a_flows[$f]["PrinterId"] != 1 && is_null($a_flows[$f]["UploadDate"])): ?>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td colspan="3"><hr></td>
                            <td colspan="7">
                                <button type="button" onclick="callUpload('<?=$c;?>','<?=$a;?>','<?=$a_flows[$f]["FileName"];?>','<?=$a_flows[$f]["DocumentType"];?>','<?=$a_flows[$f]["Id"];?>','<?=$a_flows[$f]["PrinterId"];?>','<?=$dir;?>','<?=$a_flows[$f]["UploadDate"];?>');">Carica File</button>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Data upload</b></span></td>
                            <td class="text_center" colspan="3">
                                <input class="width50 text_center sfondo_grigio" readonly name="FlowUploadDate[<?=$y;?>]"
                                       value="<?=$cls_help->toItalianDate($a_flows[$f]['UploadDate']);?>" onchange="flowChange(<?=$y;?>)">
                            </td>
                            <td colspan="2"></td><td></td><td></td>
                        </tr>
                        <!-- GV START -->
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Data fine lavorazione</b></span></td>
                            <td class="text_center" colspan="3">
                                <input class="width50 text_center <?=$picker;?>" <?=$readonly;?> name="FlowProcessingDate[<?=$y;?>]"
                                       value="<?=$cls_help->toItalianDate($a_flows[$f]['ProcessingDate']);?>" onchange="flowChange(<?=$y;?>)">
                            </td>
                            <td></td><td></td><td></td><td></td>
                        </tr>
                        <!-- GV   END -->
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Data pagamento</b></span></td>
                            <td class="text_center" colspan="3">
                                <input class="width50 text_center picker" name="FlowPostagePaymentDate[<?=$y;?>]"
                                       value="<?=$cls_help->toItalianDate($a_flows[$f]['PostagePaymentDate']);?>" onchange="flowChange(<?=$y;?>)">
                            </td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Banca</b></span></td>
                            <td colspan="2">
                                <select name="FlowPostagePaymentBankId[<?=$y;?>]" class="width95" onchange="flowChange(<?=$y;?>)">
                                    <?=$opt_banks;?>
                                </select>
                            </td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Data consegna</b></span></td>
                            <td class="text_center" colspan="3">
                                <input class="width50 text_center picker" name="FlowSendDate[<?=$y;?>]"
                                       value="<?=$cls_help->toItalianDate($a_flows[$f]['SendDate']);?>" onchange="flowChange(<?=$y;?>)">
                            </td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Ufficio di consegna</b></span></td>
                            <td colspan="2">
                                <input class="width95 text_center picker" name="FlowShippingOffice[<?=$y;?>]"
                                       value="<?=$cls_help->toItalianDate($a_flows[$f]['ShippingOffice']);?>" onchange="flowChange(<?=$y;?>)">
                            </td>
                        </tr>
                        <!-- GV - 29/04/2022 - START -->
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Data annullamento</b></span></td>
                            <td class="text_center" colspan="3">
                                <input class="width50 text_center picker" name="FlowCancelDate[<?=$y;?>]"
                                       value="<?=$cls_help->toItalianDate($a_flows[$f]['CancelDate']);?>" onchange="flowChange(<?=$y;?>)">
                            </td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Motivazioni</b></span></td>
                            <td colspan="2">
                                <textarea class="width95 text_center" id="FlowReason[<?=$y;?>]" name="FlowReason[<?=$y;?>]"
                                          rows="5" cols="50" onchange="flowChange(<?=$y;?>)"><?=$a_flows[$f]['CancelReason'];?></textarea>
                            </td>
                        </tr>
                        <!-- GV - 29/04/2022 -   END -->

                        <?php if (!is_null($a_flows[$f]['CancelDate']) && ($_SESSION['username'] == "mirkop" || $_SESSION['username'] == "robertop")): ?>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Ripristino Atti</b></span></td>
                            <td class="text_left" colspan="4">
                                <select id="selectRipristino_<?=$y;?>">
                                    <option disabled value="1">Rimozione Flusso</option>
                                    <option disabled value="2">Rimozione Stampa e Flusso</option>
                                    <option disabled value="3">Rimozione Cronologico, Stampa e Flusso</option>
                                    <option value="4">Eliminazione Atti + Lista elaborazione collegata</option>
                                </select>
                            </td>
                            <td class="text_left" colspan="3">
                                <input type="button" id="ripristinoAtti_<?=$y;?>" class="btn btn-danger" value="ESEGUI">
                            </td>
                        </tr>
                        <script>
                            $("#ripristinoAtti_<?=$y;?>").click(function(){
                                $.ajax({
                                    type: "POST", async: false,
                                    url: "<?= WEB_ROOT; ?>/coattiva/ajax/ActsUpdate.php",
                                    data: { type: $('#selectRipristino_<?=$y;?>').val(), flowId: <?=$y;?> },
                                    dataType: "json",
                                    success: function(response){
                                        alert(response.msg);
                                        if(response.type==5)
                                            location.href = "flow_mgmt.php?c=<?=$c;?>&a=<?=$a;?>";
                                        console.log(response);
                                    },
                                    error: function(response){ console.log(response); }
                                });
                            });
                        </script>
                        <?php endif; ?>

                        <tr class="flow_<?=$y;?> color_table" style="display:none;"><td colspan="10"><hr></td></tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>SPESE POSTALI</b></span></td>
                            <td class="text_center" colspan="2">Fattura</td>
                            <td class="text_center" colspan="4">
                                <select class="width80" name="FlowPostageInvoiceId[<?=$y;?>]" onchange="flowChange(<?=$y;?>)"><?=$opt_postage;?></select>
                            </td>
                            <td class="text_center"></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Zona 0</b></span></td>
                            <td class="text_left" colspan="2">
                                <input class="pwidth50 text_right" type="text" name="FlowZone0Postage[<?=$y;?>]"
                                       value="<?=$cls_help->floatToString($a_flows[$f]['Zone0Postage']);?>" onchange="flowChange(<?=$y;?>)"> &euro;
                            </td>
                            <td class="text_center" colspan="2">Records</td>
                            <td><input class="pwidth50 text_center" type="text" name="FlowZone0Number[<?=$y;?>]" value="<?=$a_flows[$f]['Zone0Number'];?>" onchange="flowChange(<?=$y;?>)"></td>
                            <td class="text_right"><?=$cls_help->floatToString($a_flows[$f]['Zone0Postage']*$a_flows[$f]['Zone0Number']);?> &euro;&nbsp;&nbsp;</td>
                            <td></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Zona 1</b></span></td>
                            <td class="text_left" colspan="2">
                                <input class="pwidth50 text_right" type="text" name="FlowZone1Postage[<?=$y;?>]"
                                       value="<?=$cls_help->floatToString($a_flows[$f]['Zone1Postage']);?>" onchange="flowChange(<?=$y;?>)"> &euro;
                            </td>
                            <td class="text_center" colspan="2">Records</td>
                            <td><input class="pwidth50 text_center" type="text" name="FlowZone1Number[<?=$y;?>]" value="<?=$a_flows[$f]['Zone1Number'];?>" onchange="flowChange(<?=$y;?>)"></td>
                            <td class="text_right"><?=$cls_help->floatToString($a_flows[$f]['Zone1Postage']*$a_flows[$f]['Zone1Number']);?> &euro;&nbsp;&nbsp;</td>
                            <td></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Zona 2</b></span></td>
                            <td class="text_left" colspan="2">
                                <input class="pwidth50 text_right" type="text" name="FlowZone2Postage[<?=$y;?>]"
                                       value="<?=$cls_help->floatToString($a_flows[$f]['Zone2Postage']);?>" onchange="flowChange(<?=$y;?>)"> &euro;
                            </td>
                            <td class="text_center" colspan="2">Records</td>
                            <td><input class="pwidth50 text_center" type="text" name="FlowZone2Number[<?=$y;?>]" value="<?=$a_flows[$f]['Zone2Number'];?>" onchange="flowChange(<?=$y;?>)"></td>
                            <td class="text_right"><?=$cls_help->floatToString($a_flows[$f]['Zone2Postage']*$a_flows[$f]['Zone2Number']);?> &euro;&nbsp;&nbsp;</td>
                            <td></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Zona 3</b></span></td>
                            <td class="text_left" colspan="2">
                                <input class="pwidth50 text_right" type="text" name="FlowZone3Postage[<?=$y;?>]"
                                       value="<?=$cls_help->floatToString($a_flows[$f]['Zone3Postage']);?>" onchange="flowChange(<?=$y;?>)"> &euro;
                            </td>
                            <td class="text_center" colspan="2">Records</td>
                            <td><input class="pwidth50 text_center" type="text" name="FlowZone3Number[<?=$y;?>]" value="<?=$a_flows[$f]['Zone3Number'];?>" onchange="flowChange(<?=$y;?>)"></td>
                            <td class="text_right"><?=$cls_help->floatToString($a_flows[$f]['Zone3Postage']*$a_flows[$f]['Zone3Number']);?> &euro;&nbsp;&nbsp;</td>
                            <td></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;"><td colspan="10"><hr></td></tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>STAMPA E IMBUSTAMENTO</b></span></td>
                            <td class="text_center" colspan="2">Fattura</td>
                            <td class="text_center" colspan="4">
                                <select class="width80" name="FlowPrintInvoiceId[<?=$y;?>]" onchange="flowChange(<?=$y;?>)"><?=$opt_print;?></select>
                            </td>
                            <td class="text_center"></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;">
                            <td></td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Costo unitario</b></span></td>
                            <td class="text_left" colspan="2">
                                <input class="pwidth50 text_right" type="text" name="FlowPrintCost[<?=$y;?>]"
                                       value="<?=$cls_help->floatToString($a_flows[$f]['PrintCost']);?>" onchange="flowChange(<?=$y;?>)"> &euro;
                            </td>
                            <td class="text_center" colspan="2"><span class="color_titolo"><b>Totale records</b></span></td>
                            <td><input class="pwidth50 text_center" name="FlowRecordsNumber[<?=$y;?>]" value="<?=$a_flows[$f]['RecordsNumber'];?>" onchange="flowChange(<?=$y;?>)"></td>
                            <td class="text_right"><?=$cls_help->floatToString($a_flows[$f]['PrintCost']*$a_flows[$f]['RecordsNumber']);?> &euro;&nbsp;&nbsp;</td>
                            <td></td>
                        </tr>
                        <tr class="flow_<?=$y;?> color_table" style="display:none;"><td colspan="10"><hr></td></tr>

                    <?php } // end for $a_flows ?>
                </table>
                <br>
            </div>
            <br><br>
        </div>
    </form>

    <!--  GV - 08/06/2022 - START  -->
    <form id="form_details" name="form_details" action="flow_mgmt_detail.php" method="POST">
        <input type="hidden" id="c"                    name="c"                    value="<?=$c;?>">
        <input type="hidden" id="a"                    name="a"                    value="<?=$a;?>">
        <input type="hidden" id="FlowId"               name="FlowId">
        <input type="hidden" id="activeTab"            name="activeTab"            value="<?php echo $activeTab; ?>">
        <input type="hidden" id="filterInvoiceNumber"  name="filterInvoiceNumber"  value="<?php echo $filterInvoice['Number']; ?>">
        <input type="hidden" id="filterInvoiceYear"    name="filterInvoiceYear"    value="<?php echo $filterInvoice['Year']; ?>">
        <input type="hidden" id="filterInvoiceDate"    name="filterInvoiceDate"    value="<?php echo $filterInvoice['Date']; ?>">
        <input type="hidden" id="filterFlowNumber"     name="filterFlowNumber"     value="<?php echo $filterFlow['Number']; ?>">
        <input type="hidden" id="filterFlowYear"       name="filterFlowYear"       value="<?php echo $filterFlow['Year']; ?>">
        <input type="hidden" id="filterFlowStatus"     name="filterFlowStatus"     value="<?php echo $filterFlow['Status']; ?>">
        <input type="hidden" id="filterFlowPrinter"    name="filterFlowPrinter"    value="<?php echo $filterFlow['PrinterId']; ?>">
        <input type="hidden" id="filterFlowMissStatus" name="filterFlowMissStatus" value="<?php echo $filterFlow['MissStatus']; ?>">
        <input type="hidden" id="filterFlowStatusOfDate" name="filterFlowStatusOfDate" value="<?php echo $filterFlow['StatusOfDate']; ?>">
        <input type="hidden" id="filterFlowStatusDate" name="filterFlowStatusDate" value="<?php echo $cls_help->toDbDate($cls_help->getVar("filterFlowStatusDate")); ?>">
        <input type="hidden" id="filterFlowCityId"     name="filterFlowCityId"     value="<?php echo $filterFlow['CityId']; ?>">
    </form>

    <script>
        function dettaglioFlusso(fID){
            $('#FlowId').val(fID);
            $('#form_details').submit();
        }
    </script>
    <!--  GV - 08/06/2022 - END  -->

<?php include(INC."/footer.php"); ?>