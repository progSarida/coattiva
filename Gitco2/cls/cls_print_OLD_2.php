<?php

include_once CLS."/cls_help.php";
include_once CLS."/cls_db.php";
include_once CLS."/cls_html.php";
//include_once CLASSI."/parametri.php";

class cls_print{

    public $a_type = array();
    public $a_filters = array();
    public $citySelect = null;
    public $tax_firstOpt = null;
    public $year_blank = "n";
    public $options = null;
    public $cls_help = null;
    public $cls_db = null;
    public $cls_html = null;
    public $a_enti = null;

    public function __construct($printType, $type, $a_city = null, $options = null){
        $this->cls_help = new cls_help();
        $this->cls_db = new cls_db();
        $this->cls_html = new cls_html();

        $this->setPrintParams($printType, $type);
        $this->getCities($a_city);
        if(is_array($options)){
            foreach($options as $key=>$value){
                $this->setOptions($key, $value);
            }
        }

    }

    public function getCities($a_city){
        if($a_city!=null) {
            if (($_SESSION['CC_User'] == "****" || $_SESSION['CC_User'] == "***+")){
                if($_SESSION['aut_tipo']==1)
                    $this->citySelect = "<option value='AUTH_1'>Tutti</option>
                        <option value='AUTH_3'>Lista IRTEL</option>
                        <option value='AUTH_4'>Lista Comunità Montana Peligna</option>
                        <option value='AUTH_5'>Lista Albuzzano - Borgo San Siro - Garlasco</option>";
                else
                    $this->citySelect = "<option value='AUTH_".$_SESSION['aut_tipo']."'>Tutti</option>";
            }

            $queryCities = "SELECT * FROM enti_gestiti";
            if ($_SESSION['CC_User'] != "****" && $_SESSION['CC_User'] != "***+")
                $queryCities .= " WHERE CC='" . $_SESSION['CC_User'] . "' ";
            else if ($_SESSION['aut_tipo'] > 2 && $_SESSION['aut_tipo'] < 20)
                $queryCities .= " WHERE Autorizzazione=" . $_SESSION['aut_tipo'] . " ";
            $queryCities .= " ORDER BY Denominazione";
            $this->a_enti = $this->cls_db->getResults($this->cls_db->SelectQuery($queryCities));
 
            $a_selection = array("value" => "CC", "firstOpt" => 0, "selected" => $a_city['cc'], "text" => array("[Denominazione]", " - ", "[CC]"));
            $this->citySelect.= $this->cls_html->getOptions($this->a_enti, $a_selection);
        }
    }

    public function setOptions($key, $value){
        $this->options[$key] = $value;
    }

    public function setAction($newAction){
        $this->a_type["action"] = $newAction;
    }

    public function setPrintParams($printType, $type){
        switch($printType){
            case "list":
                switch($type){
                    case "esiti":
                        $this->a_type = array("title"=>"Gestione esiti","action"=>"elenco_esiti.php");
                        $this->a_filters = array("fileType","PrinterId","city","lastAct","actType","notificationImg","flow","flowNumber","flowDate",
                            "notificationDate","importNotification","notificationAndAnomaly",
                            "notificationMode","notificationStock","notificationAnomaly",
                            "payment","taxStopFlag","dischargeFlag","dischargeDate","flow_sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        $this->year_blank = "y";
                        break;
                    case "notifiche":
                        $this->a_type = array("title"=>"Gestione notifiche","action"=>"elenco_notifiche.php");
                        $this->a_filters = array("PrinterId","city","lastAct","actType","notificationImg","flow","flowNumber","flowDate",
                            "notificationDate","importNotification","notificationAndAnomaly",
                            "notificationMode","notificationStock","notificationAnomaly",
                            "payment","taxStopFlag","dischargeFlag","dischargeDate","flow_sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        $this->year_blank = "y";
                        break;
                    case "dettaglio_partita":
                        $this->a_type = array("title"=>"Elenco dettaglio partita","action"=>"list_dettaglioPartita.php");
                        $this->a_filters = array("fileType","taxStopFlag","dischargeFlag","dischargeDate","sort");
                        break;

                    case "court_hearing":
                        $this->a_type = array("title"=>"Elenco udienze","action"=>"list_courtHearing.php");
                        $this->a_filters = array("fileType","city","courtHearingDate","taxStopFlag","dischargeFlag","dischargeDate","courtHearing_sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        $this->year_blank = "y";
                        break;

                    case "positions":
                        $this->a_type = array("title"=>"Elenco posizioni","action"=>"list_positions.php");
                        $this->a_filters = array("city","fileType","positionNotificationLimit","expiredPosition","PrinterId","chron", "paymentStatus",
                            "elaborationStatusAtto","printStatusAtto",
                            "instalmentAtto","instalmentStatusAtto", "actNotificationDate",
                            "actNotificationMode","actNotificationStock","actNotificationAnomaly", "actNotificationAndAnomaly",
                            "elaborationStatusPignoramento","printStatusPignoramento",
                            "instalmentPignoramento","instalmentStatusPignoramento", "pignoNotificationLimit", "pignoNotificationDate",
                            "pignoNotificationMode","pignoNotificationStock","pignoNotificationAnomaly","pignoNotificationAndAnomaly",
                            "taxStopFlag","dischargeFlag","dischargeDate","sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        $this->year_blank = "y";
                        break;

                    case "SOLL_PRE":
                        $this->a_type = array("title"=>"Elenco Solleciti pre ingiunzione","action"=>"list_atto.php");
                        $this->a_filters = array("city","fileType","PrinterId","printStatus","PrintTypeOrdinaria","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","taxStopFlag","dischargeFlag","dischargeDate","sort");
                        break;
                    case "AV_MORA":
                        $this->a_type = array("title"=>"Elenco Avvisi di messa in mora","action"=>"list_atto.php");
                        $this->a_filters = array("city","fileType","PrinterId","printStatus","PrintTypeId","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","taxStopFlag","dischargeFlag","dischargeDate","sort");
                        break;
                    case "ING":
                        $this->a_type = array("title"=>"Elenco Ingiunzioni","action"=>"list_atto.php");
                        $this->a_filters = array(
                            "city", "fileType", "PrinterId", "printStatus", "TrafficLaw", "PrintTypeId", "officialType",
                            "elaborationDate", "printDate", "notificationDate",
                            "notificationMode", "notificationStock", "notificationAnomaly",
                            "flowDate", "flowNumber",
                            "instalmentAtto","paymentStatus",
                            "taxStopFlag","dischargeFlag","dischargeDate","sort"
                        );
                        break;

                    case "AV_INT":
                        $this->a_type = array("title"=>"Elenco Avvisi di Intimazione ad Adempiere","action"=>"list_atto.php");
                        $this->a_filters = array(
                            "city","fileType", "PrinterId", "printStatus", "PrintTypeId", "officialType",
                            "elaborationDate", "printDate", "notificationDate",
                            "notificationMode", "notificationStock", "notificationAnomaly",
                            "flowDate", "flowNumber",
                            "instalmentAtto","paymentStatus",
                            "taxStopFlag","dischargeFlag","dischargeDate","sort"
                        );
                        break;

                    case "SCORPORO_ING":
                        $this->a_type = array("title"=>"Elenco dettaglio Ingiunzioni","action"=>"list_atto_scorporo.php");
                        $this->a_filters = array("fileType","PrinterId","printStatus","PrintTypeId","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","taxStopFlag","dischargeFlag","dischargeDate","sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        break;
                    case "SOLL_POST":
                        $this->a_type = array("title"=>"Elenco Solleciti post ingiunzione","action"=>"list_atto.php");
                        $this->a_filters = array("city","fileType","PrinterId","printStatus","PrintTypeOrdinaria","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","taxStopFlag","dischargeFlag","dischargeDate","sort");
                        break;
                    /** GV - 08/06/2022 - START */
                    case "discharge":
                        $this->a_type = array("title"=>"Elenco discarico","action"=>"elenco_discarico.php");
                        $this->a_filters = array("fileType", "city","elaborationListDate","dischargeDate","extractionDate", "sort");
                        break;
                    /** GV - 08/06/2022 -   END */    
                }
                break;
            case "print":
                switch($type){
                    case "SOLL_PRE":
                        $this->a_type = array("title"=>"Stampa Solleciti pre ingiunzione", "action"=>"print_atto.php");
                        $this->a_filters = array("finalDate","PrinterId","printTypeFlow","printStatus","PrintTypeOrdinaria","officialType","elaborationDate",
                            "printDate","notificationDate","sort");
                        break;
                    case "AV_MORA":
                        $this->a_type = array("title"=>"Stampa Avvisi di messa in mora","action"=>"print_atto.php");
                        $this->a_filters = array("finalDate","PrinterId","printTypeFlow","printStatus","PrintTypeId","officialType","elaborationDate",
                            "printDate","notificationDate","sort");
                        break;
                }
                break;

            case "elab":
                switch($type){
                    case "discharge":
                        $this->a_type = array("title"=>"Elaborazione discarico", "action"=>"elab_discharge.php");
                        $this->a_filters = array("elabType","finalElabDate","dischargeLimitDate", "fileType","sort");
                        break;

                    case "extraction":
                        $this->a_type = array("title"=>"Elaborazione estrazione discarico", "action"=>"elab_extraction.php");
                        $this->a_filters = array("elabType","finalElabDate","sort");
                        break;
                        
                        
                }
                break;

            case "html":
                switch($type){
                    //nuove stampe con htmlTipo di stampa
                    case "ING":
                        $this->a_type = array("title"=>"Stampa Ingiunzioni","action"=>"print_atto_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printType","printStatusNoBlank","PrintTypeId","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","sort");
                        break;
                    case "AV_INT":
                        $this->a_type = array("title"=>"Stampa Avvisi di Intimazione ad Adempiere","action"=>"print_atto_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printType","printStatusNoBlank","PrintTypeIdAV_INT","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","sort");
                        break;
                    case "AV_MORA":
                        $this->a_type = array("title"=>"Stampa Avvisi di messa in mora","action"=>"print_atto_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printTypeFlow","printStatus","PrintTypeId","officialType","elaborationDate",
                            "printDate","notificationDate","sort");
                        break;
                    case "SOLL_POST":
                        $this->a_type = array("title"=>"Elenco Solleciti post ingiunzione","action"=>"print_atto_html.php");
                        $this->a_filters = array("finalDate","fileType","PrinterId","printTypeFlow","printStatus","PrintTypeOrdinaria","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","sort");
                        break;
                    case "SOLL_PRE":
                        $this->a_type = array("title"=>"Elenco Solleciti pre ingiunzione","action"=>"print_atto_html.php");
                        $this->a_filters = array("finalDate","fileType","PrinterId","printTypeFlow","printStatus","PrintTypeOrdinaria","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","sort");
                        break;
                    case "veicolo":
                        $this->a_type = array("title"=>"Pignoramento veicoli","action"=>"print_pigno_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printType","printStatusNoBlank","PrintTypeIdAV_INT","debtorNotificationDate",
                            "dateShipment","deliveryDate","elaborationDate","printDate","flowNumber","sort","deliveredTo");
                        break;
                    case "lavoro":
                        $this->a_type = array("title"=>"Pignoramento presso datore di lavoro","action"=>"print_pigno_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printType","printStatusNoBlank","PrintTypeIdAV_INT","debtorNotificationDate",
                            "dateShipment","deliveryDate","elaborationDate","printDate","flowNumber","sort","deliveredTo");
                        break;
                    case "banca":
                        $this->a_type = array("title"=>"Pignoramento presso banca","action"=>"print_pigno_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printType","printStatusNoBlank","PrintTypeId","debtorNotificationDate",
                            "dateShipment","deliveryDate","elaborationDate","printDate","flowNumber","sort","deliveredTo");
                        break;
                    case "fermo":
                        $this->a_type = array("title"=>"Pignoramento fermo","action"=>"print_pigno_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printType","printStatusNoBlank","PrintTypeId","debtorNotificationDate",
                            "dateShipment","deliveryDate","elaborationDate","printDate","flowNumber","sort","deliveredTo");
                        break;
                    case "preav_fermo":
                        $this->a_type = array("title"=>"Pignoramento preavviso fermo","action"=>"print_pigno_html.php");
                        $this->a_filters = array("finalDate","PrinterId","printType","printStatusNoBlank","PrintTypeId","debtorNotificationDate",
                            "dateShipment","deliveryDate","elaborationDate","printDate","flowNumber","sort","deliveredTo");
                        break;
                }
        }
    }

    public function setFilter($filter){
        $a_filterParams = array("title"=>"","input"=>"","secondInput"=>"");
        switch($filter){
            case "PrinterId":
                $a_filterParams['title'] = "Stampatore";
                $a_filterParams['input'] = "<select class=\"width95\" id=\"PrinterId\" name=\"PrinterId\">
                                                ".$this->options['PrinterId']."
                                            </select>";
                break;

            case "PrintTypeId":
                $a_filterParams['title'] = "Tipo di spedizione";
                $a_filterParams['input'] = "<select class=\"width95\" id=\"PrintTypeId\" name=\"PrintTypeId\">
                                                ".$this->options['PrintTypeId']."
                                            </select>";
                break;
            case "PrintTypeIdAV_INT":
                $a_filterParams['title'] = "Tipo di spedizione";
                $a_filterParams['input'] = "<select class=\"width95\" id=\"PrintTypeId\" name=\"PrintTypeId\">
                                                ".$this->options['PrintTypeIdAV_INT']."
                                            </select>";
                break;
            case "TrafficLaw":
                $a_filterParams['title'] = "Importati da Gitco CDS";
                $a_filterParams['input'] = "<select class=\"width95\" id=\"TrafficLaw\" name=\"TrafficLaw\">
                                                <option></option>
                                                <option value=\"1\">SOLO Gitco CDS</option>
                                            </select>";
                break;

            case "PrintTypeOrdinaria":
                $a_filterParams['title'] = "Tipo di spedizione";
                $a_filterParams['input'] = "<select class=\"width95\" id=\"PrintTypeId\" name=\"PrintTypeId\">
                                                ".$this->options['PrintTypeOrdinaria']."
                                            </select>";
                break;

            case "city":
                $a_filterParams['title'] = "Ente";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"city\" id=fileType>
                                                ".$this->citySelect."
                                            </select>";
                break;
            case "fileType":
                $a_filterParams['title'] = "Tipo di file";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"fileType\" id=fileType>
                                                <option value=\"pdf\">PDF</option>
                                                <option value=\"excel\">EXCEL</option>
                                            </select>";
                break;

            case "actType":
                $a_filterParams['title'] = "Tipologia atti";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"actType\" id=actType>
                                                <optgroup label=\"Selezioni multiple\">
                                                    <option value=\"AG\">Tutti gli Atti Giudiziari</option>
                                                    <option value=\"PIG\">Pignoramenti</option>
                                                    <option value=\"AG_NO_PIG\">Atti Giudiziari esclusi i Pignoramenti</option>
                                                    <option value=\"NO_AG\">Posta ordinaria</option>
                                                </optgroup>
                                                <optgroup label=\"Selezioni singole\">
                                                    <option value=\"11\">Solleciti pre Ingiunzione</option>
                                                    <option value=\"2\">Ingiunzioni</option>
                                                    <option value=\"3\">Solleciti post Ingiunzione</option>
                                                    <option value=\"4\">Avvisi di intimazione</option>
                                                    <option value=\"12\">Avvisi di messa in mora</option>                                                
                                                    <option value=\"8\">Pignoramenti presso banca</option>
                                                    <option value=\"7\">Pignoramenti presso datore di lavoro</option>
                                                    <option value=\"6\">Pignoramenti del veicolo</option>
                                                </optgroup>
                                            </select>";
                break;

            case "lastAct":
                $a_filterParams['title'] = "Atti";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"lastAct\" id=lastAct>
                                                <option value=\"\">Tutti</option>                                            
                                                <option value=\"last\">Ultimo atto stampato</option>
                                            </select>";
                break;

            /*case "sendTypeIng":
                $a_filterParams['title'] = "Tipo di spedizione";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"sendType\" id=sendType>
                                                <option value=\"posta\" checked>Raccomandata A.G.</option>                                            
                                                <option value=\"mani\">A mani</option>
                                            </select>";
                break;*/

            case "chron":
                $a_filterParams['title'] = "Cronologico Assente/Presente";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"chron\" id=chron>
                                                <option value=''></option>
                                                <option value=\"si\">Cronologico presente</option>                                            
                                                <option value=\"no\">Cronologico assente</option>
                                            </select>";
                break;

            case "sendTypeIng":
                $a_filterParams['title'] = "Tipo di spedizione";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"sendType\" id=sendType>
                                                <option value=\"posta\" checked>Raccomandata A.G.</option>                                            
                                                <option value=\"mani\">A mani</option>
                                            </select>";
                break;

            case "sendTypeAvv":
                $a_filterParams['title'] = "Tipo di spedizione";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"sendType\" id=sendType>
                                                <option value=\"raccomandata\">Raccomandata</option>
                                                <option value=\"posta\" checked>Raccomandata A.G.</option>                                            
                                                <option value=\"ordinaria\">Posta ordinaria</option>
                                            </select>";
                break;

            case "sendTypeSoll":
                $a_filterParams['title'] = "Tipo di spedizione";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"sendType\" id=sendType>
                                                <option value=\"ordinaria\" checked>Posta ordinaria</option>
                                            </select>";
                break;

            case "officialType":
                $a_filterParams['title'] = "Tipo di riscossione";
                $a_filterParams['input'] = "<select class=\"width95\" name=\"officialType\" id=officialType>
                                                <option value=\"diretta\" checked>Diretta</option>
                                                <option value=\"riscossione\">Ufficiale della riscossione</option>
                                                <option value=\"giudiziario\">Ufficiale giudiziario</option>
                                                <option value=\"tutti\">Tutti</option>
                                            </select>";
                break;

            case "finalDate":
                $a_filterParams['title'] = "Data di stampa definitiva";
                $a_filterParams['input'] = "<input name=\"finalDate\" class=\"text_center width30\" value=\"".date("d/m/Y")."\">";

                break;

            case "finalElabDate":
                $a_filterParams['title'] = "Data di elaborazione";
                $a_filterParams['input'] = "<input name=\"finalElabDate\" class=\"text_center width30\" value=\"".date("d/m/Y")."\">";

                break;

            case "dischargeLimitDate":
                $a_filterParams['title'] = "Data limite del discarico";
                $a_filterParams['input'] = "<input name=\"dischargeLimitDate\" class=\"text_center width30\" value=\"31/12/2010\">";
                $a_filterParams['secondInput'] = "Indica la data al di sotto della quale interviene l'elaborazione";

                break;

            case "elaborationDate":
                $a_filterParams['title'] = "Data di elaborazione";
                $a_filterParams['input'] = "Dal <input name=\"from_elaborationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_elaborationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"no_elaborationDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            /** GV - 08/06/2022 - START */
            case "elaborationListDate":
                $a_filterParams['title'] = "Data di elaborazione Atto";
                $a_filterParams['input'] = "Dal <input name=\"from_elaborationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_elaborationDate\" class=\"text_center width30 picker\">";
               
                break;	
            /** GV - 08/06/2022 -   END */

            case "printDate":
                $a_filterParams['title'] = "Data di stampa";
                $a_filterParams['input'] = "Dal <input name=\"from_printDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_printDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"no_printDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            case "notificationDate":
                $a_filterParams['title'] = "Data di notifica";
                $a_filterParams['input'] = "Dal <input name=\"from_notificationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_notificationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"exist_notificationDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            case "importNotification":
                $a_filterParams['title'] = "Importazione notifica";
                $a_filterParams['input'] = "<select name=\"importNotification\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Presente</option>
                                                <option value='n'>Assente</option>
                                            </select>";
                break;

            case "flowDate":
                $a_filterParams['title'] = "Data del flusso";
                $a_filterParams['input'] = "Dal <input name=\"from_flowDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_flowDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"exist_flowDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            case "flowNumber":
                $a_filterParams['title'] = "Flusso";
                $a_filterParams['input'] = "Numero <input name=\"flowNumber\" class=\"text_right width13\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Anno <input name=\"flowYear\" class=\"text_right width15\">";

                break;

            case "flow":
                $a_filterParams['title'] = "Flusso";
                $a_filterParams['input'] = "<select name=\"flow\" class=\"width95\">       
                                                <option value=''>Tutti</option>                                       
                                                <option value='y'>Presente</option>
                                                <option value='n'>Assente</option>
                                            </select>";

                break;

            case "notificationImg":
                $a_filterParams['title'] = "Cartoline";
                $a_filterParams['input'] = "<select name=\"notificationImg\" class=\"width95\">       
                                                <option value='all'>Tutti</option>     
                                                <option value='y'>File Presenti</option>
                                                <option value='n'>File Assenti completi</option>
                                                <option value='db'>File Assenti ma registrati nel DB</option>                             
                                            </select>";

                break;

            case "printType":
                $a_filterParams['title'] = "Tipo di stampa";
                $a_filterParams['input'] = "<select name=\"printType\" class=\"width95\" onchange=\"changeAction(this);\">
                                                <option value='temp'>Provvisoria</option>
                                                <option value='crono'>Assegnamento cronologici</option>
                                                <option value='ini_pec'>Richiesta iniPEC</option>
                                                <option value='final'>Definitiva</option>
                                                <option value='flow'>Flusso</option>
                                                <option value='pec'>Invia PEC</option>
                                            </select>";
                break;
            case "printStatus":
                $a_filterParams['title'] = "Stato stampa";
                $a_filterParams['input'] = "<select name=\"printStatus\" class=\"width95\">
                                                <option selected value='toPrint'>Da stampare</option>
                                                <option value='printed'>Stampato</option>
                                                <option value=''>Tutti</option>
                                            </select>";
                break;

            case "printStatusNoBlank":
                $a_filterParams['title'] = "Stato stampa";
                $a_filterParams['input'] = "<select name=\"printStatus\" class=\"width95\">
                                                <option selected value='toPrint'>Da stampare</option>
                                                <option value='printed'>Stampato</option>
                                            </select>";
                break;

            case "printStatusAtto":
                $a_filterParams['title'] = "Stato stampa atto";
                $a_filterParams['input'] = "<select name=\"printStatusAtto\" class=\"width95\">
                                                <option></option>
                                                <option value='toPrint'>Da stampare</option>
                                                <option value='printed'>Stampato</option>
                                            </select>";
                break;

            case "printStatusPignoramento":
                $a_filterParams['title'] = "Stato stampa pigno.";
                $a_filterParams['input'] = "<select name=\"printStatusPignoramento\" class=\"width95\">
                                                <option></option>
                                                <option value='toPrint'>Da stampare</option>
                                                <option value='printed'>Stampato</option>
                                            </select>";
                break;

            case "elaborationStatusAtto":
                $a_filterParams['title'] = "Elaborazione atto";
                $a_filterParams['input'] = "<select name=\"elaborationStatusAtto\" class=\"width95\">
                                                <option></option>
                                                <option value='toCreate'>Da elaborare</option>
                                                <option value='created'>Elaborato</option>
                                            </select>";
                break;

            case "elaborationStatusPignoramento":
                $a_filterParams['title'] = "Elaborazione pigno.";
                $a_filterParams['input'] = "<select name=\"elaborationStatusPignoramento\" class=\"width95\">
                                                <option></option>
                                                <option value='toCreate'>Da elaborare</option>
                                                <option value='created'>Elaborato</option>
                                            </select>";
                break;

            case "notificationStatusAtto":
                $a_filterParams['title'] = "Notifica atto";
                $a_filterParams['input'] = "<select name=\"notificationStatus\" class=\"width95\">
                                                <option></option>
                                                <option value='toNotificate'>Assente</option>
                                                <option value='notificated'>Presente</option>
                                            </select>";
                break;

            case "notificationStockAtto":
                $a_filterParams['title'] = "Giacenza/Anomalia";
                $a_filterParams['input'] = "<select name=\"notificationStockAtto\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Presente</option>
                                                <option value='n'>Assente</option>
                                            </select>";
                break;

            case "notificationMode":
                $a_filterParams['title'] = "Modalita' notifica";
                $a_filterParams['input'] = "<select name=\"notificationMode\" class=\"width95\">";
                $a_filterParams['input'].= "<option></option><option value='y'>Presente</option><option value='n'>Assente</option><optgroup label='Selezioni singole'>";
                $a_filterParams['input'].= $this->options['notificationMode'];
                $a_filterParams['input'].= "</optgroup></select>";
                break;

            case "actNotificationMode":
                $a_filterParams['title'] = "Modalita' notifica atto";
                $a_filterParams['input'] = "<select name=\"notificationMode\" class=\"width95\">";
                $a_filterParams['input'].= "<option></option><option value='y'>Presente</option><option value='n'>Assente</option><optgroup label='Selezioni singole'>";
                $a_filterParams['input'].= $this->options['notificationMode'];
                $a_filterParams['input'].= "</optgroup></select>";
                break;

            case "pignoNotificationMode":
                $a_filterParams['title'] = "Modalita' notifica pignoramento";
                $a_filterParams['input'] = "<select name=\"pignoNotificationMode\" class=\"width95\">";
                $a_filterParams['input'].= "<option></option><option value='y'>Presente</option><option value='n'>Assente</option><optgroup label='Selezioni singole'>";
                $a_filterParams['input'].= $this->options['notificationMode'];
                $a_filterParams['input'].= "</optgroup></select>";
                break;

            case "notificationStock":
                $a_filterParams['title'] = "Stato giacenza";
                $a_filterParams['input'] = "<select name=\"notificationStock\" class=\"width95\">";
                $a_filterParams['input'].= "<option></option><option value='y'>Presente</option><option value='n'>Assente</option><optgroup label='Selezioni singole'>";
                $a_filterParams['input'].= $this->options['notificationStock'];
                $a_filterParams['input'].= "</optgroup></select>";
                break;

            case "actNotificationStock":
                $a_filterParams['title'] = "Stato giacenza atto";
                $a_filterParams['input'] = "<select name=\"notificationStock\" class=\"width95\">";
                $a_filterParams['input'].= "<option></option><option value='y'>Presente</option><option value='n'>Assente</option><optgroup label='Selezioni singole'>";
                $a_filterParams['input'].= $this->options['notificationStock'];
                $a_filterParams['input'].= "</optgroup></select>";
                break;

            case "pignoNotificationStock":
                $a_filterParams['title'] = "Stato giacenza pignoramento";
                $a_filterParams['input'] = "<select name=\"pignoNotificationStock\" class=\"width95\">";
                $a_filterParams['input'].= "<option></option><option value='y'>Presente</option><option value='n'>Assente</option><optgroup label='Selezioni singole'>";
                $a_filterParams['input'].= $this->options['notificationStock'];
                $a_filterParams['input'].= "</optgroup></select>";
                break;


            case "notificationAnomaly":
                $a_filterParams['title'] = "Anomalia notifica";
                $a_filterParams['input'] = "<select name=\"notificationAnomaly\" class=\"width95\">";
                $a_filterParams['input'].= "<option></option><option value='y'>Presente</option><option value='n'>Assente</option><optgroup label='Selezioni singole'>";
                $a_filterParams['input'].= $this->options['notificationAnomaly'];
                $a_filterParams['input'].= "</optgroup></select>";
                break;

            case "actNotificationAnomaly":
                $a_filterParams['title'] = "Anomalia notifica atto";
                $a_filterParams['input'] = "<select name=\"notificationAnomaly\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Presente</option>
                                                <option value='n'>Assente</option>
                                            </select>";
                break;

            case "pignoNotificationAnomaly":
                $a_filterParams['title'] = "Anomalia notifica pignoramento";
                $a_filterParams['input'] = "<select name=\"pignoNotificationAnomaly\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Presente</option>
                                                <option value='n'>Assente</option>
                                            </select>";
                break;

            case "positionNotificationLimit":
                $a_filterParams['title'] = "Posizioni";
                $a_filterParams['input'] = "<select name=\"positionNotificationLimit\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Attive</option>
                                                <option value='n'>Scadute</option>                                                
                                            </select>";
                break;
           
            case "expiredPosition":
                $a_filterParams['title'] = "Posizioni prescritte
                <i class='fa-solid fa-circle-info fa-lg' style='color: #294A9C'
                title='Le posizioni prescritte possono essere recuperate solo nel caso in cui sia stata effettuata una pre-elaborazione completa del comune di interesse.'></i>&nbsp;&nbsp";
                $a_filterParams['input'] = "<select name=\"expiredPosition\" class=\"width95\">
                                                <option></option>
                                                <option value='1'>SI</option>
                                                <option value='0'>NO</option>                                                
                                            </select>";
                break;

            case "actNotificationLimit":
                $a_filterParams['title'] = "Scadenza atto";
                $a_filterParams['input'] = "<select name=\"notificationLimit\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Scaduto</option>
                                                <option value='n'>Attivo</option>
                                            </select>";
                break;

            case "pignoNotificationLimit":
                $a_filterParams['title'] = "Scadenza pignoramento";
                $a_filterParams['input'] = "<select name=\"pignoNotificationLimit\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Scaduto</option>
                                                <option value='n'>Attivo</option>
                                            </select>";
                break;

            case "notificationStatusPignoramento":
                $a_filterParams['title'] = "Notifica pigno.";
                $a_filterParams['input'] = "<select name=\"notificationStatusPignoramento\" class=\"width95\">
                                                <option></option>
                                                <option value='toNotificate'>Assente</option>
                                                <option value='notificated'>Presente</option>
                                            </select>";
                break;

            case "paymentStatus":
                $a_filterParams['title'] = "Pagamenti";
                $a_filterParams['input'] = "<select name=\"paymentStatus\" class=\"width95\">
                                                <option></option>                                                                                                                     
                                                <option value='incompleted'>Incompleti ( Nessuno + Parziali )</option>
                                                <option value='no'>Nessuno</option>
                                                <option value='partial'>Parziali</option>
                                                <option value='completed'>Completi</option>
                                                <option value='yes'>Presenti ( Qualsiasi pagamento )</option>
                                            </select>";
                break;

            case "payment":
                $a_filterParams['title'] = "Pagamenti";
                $a_filterParams['input'] = "<select name=\"payment\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Presenti</option>
                                                <option value='n'>Assenti</option>
                                            </select>";
                break;

            case "instalmentAtto":
                $a_filterParams['title'] = "Rateizzazione atto";
                $a_filterParams['input'] = "<select name=\"instalmentAtto\" class=\"width95\">
                                                <option></option>
                                                <option value='no'>Nessuna</option>
                                                <option value='yes'>Presente</option>
                                            </select>";
                break;

            case "instalmentStatusAtto":
                $a_filterParams['title'] = "Stato rateizzazione atto";
                $a_filterParams['input'] = "<select name=\"instalmentStatusAtto\" class=\"width95\">
                                                <option></option>
                                                <option value='ongoing'>In corso</option>
                                                <option value='expired'>Scaduta</option>
                                                <option value='completed'>Completata</option>
                                            </select>";
                break;

            case "instalmentPignoramento":
                $a_filterParams['title'] = "Rateizzazione pigno.";
                $a_filterParams['input'] = "<select name=\"instalmentPignoramento\" class=\"width95\">
                                                <option></option>
                                                <option value='no'>Nessuna</option>
                                                <option value='yes'>Presente</option>
                                            </select>";
                break;

            case "instalmentStatusPignoramento":
                $a_filterParams['title'] = "Stato rateizzazione pigno.";
                $a_filterParams['input'] = "<select name=\"instalmentStatusPignoramento\" class=\"width95\">
                                                <option></option>
                                                <option value='ongoing'>In corso</option>
                                                <option value='expired'>Scaduta</option>
                                                <option value='completed'>Completata</option>
                                            </select>";
                break;

            case "elabType":
                $a_filterParams['title'] = "Tipo di elaborazione";
                $a_filterParams['input'] = "<select id=\"elabType\" name=\"elabType\" class=\"width95\">
                                                <option value='temp'>Provvisoria</option>
                                                <option value='final'>Definitiva</option>
                                            </select>";
                $a_filterParams['secondInput'] = "L'elaborazione provvisoria non causerà modifiche agli archivi";
                break;

            case "printTypeTemp":
                $a_filterParams['title'] = "Tipo di stampa";
                $a_filterParams['input'] = "<select id=\"printType\" name=\"printType\" class=\"width95\">
                                                <option value='temp'>Provvisoria</option>
                                            </select>";
                break;
            case "printTypeFlow":
                $a_filterParams['title'] = "Tipo di stampa";
                $a_filterParams['input'] = "<select id=\"printType\" name=\"printType\" class=\"width95\" onchange=\"changeAction(this);\">
                                                <option value='temp'>Provvisoria</option>
                                                <option value='crono'>Assegnamento cronologici</option>
                                                <option value='final'>Definitiva</option>
                                                <option value='flow'>Flusso</option>
                                            </select>";
                break;
            case "taxStopFlag":
                $a_filterParams['title'] = "Blocco coazione";
                $a_filterParams['input'] = "<select name=\"taxStopFlag\" class=\"width95\">
                                                <option value=\"no\">No</option>
                                                <option value=\"si\">Si</option>
                                                <option value=\"\">Entrambi</option>
                                            </select>";
                break;

            case "dischargeFlag":
                $a_filterParams['title'] = "Discarico";
                $a_filterParams['input'] = "<select name=\"dischargeFlag\" class=\"width95\">
                                                <option value=\"0\">No</option>
                                                <option value=\"1\">Si</option>
                                                <option value=\"\">Entrambi</option>
                                            </select>";
                break;

            case "extractionFlag":
                $a_filterParams['title'] = "Estrazione discarico";
                $a_filterParams['input'] = "<select name=\"extractionFlag\" class=\"width95\">
                                                <option value=\"0\">No</option>
                                                <option value=\"1\">Si</option>
                                                <option value=\"\">Entrambi</option>
                                            </select>";
                break;

            case "dischargeDate":
                $a_filterParams['title'] = "Data di discarico";
                $a_filterParams['input'] = "Dal <input name=\"from_dischargeDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_dischargeDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"exist_dischargeDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            case "extractionDate":
                $a_filterParams['title'] = "Data di estrazione del discarico";
                $a_filterParams['input'] = "Dal <input name=\"from_extractionDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_extractionDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"exist_extractionDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            case "notificationAndAnomaly":
                $a_filterParams['title'] = "Notifiche o anomalie";
                $a_filterParams['input'] = "<select name=\"notificationAndAnomaly\" class=\"width95\">
                                                <option value=\"\"></option>
                                                <option value=\"y\">Presenti</option>
                                                <option value=\"n\">Mancanti</option>
                                            </select>";
                break;

            case "actNotificationAndAnomaly":
                $a_filterParams['title'] = "Notifiche o anomalie atto";
                $a_filterParams['input'] = "<select name=\"notificationAndAnomaly\" class=\"width95\">
                                                <option value=\"\"></option>
                                                <option value=\"y\">Presenti</option>
                                                <option value=\"n\">Mancanti</option>
                                            </select>";
                break;

            case "pignoNotificationAndAnomaly":
                $a_filterParams['title'] = "Notifiche o anomalie pignoramento";
                $a_filterParams['input'] = "<select name=\"pignoNotificationAndAnomaly\" class=\"width95\">
                                                <option value=\"\"></option>
                                                <option value=\"y\">Presenti</option>
                                                <option value=\"n\">Mancanti</option>
                                            </select>";
                break;

            case "sort":
                $a_filterParams['title'] = "Ordinamento";
                $a_filterParams['input'] = "<select name=\"sort\" class=\"width95\" >
                                                <option value=\"partita\">Partita</option>
                                                <option value=\"crono\">Cronologico</option>
                                                <option value=\"utente\">Alfabetico</option>
                                            </select>";
                break;

            case "sort2":
                $a_filterParams['title'] = "Ordinamento";
                $a_filterParams['input'] = "<select name=\"sort\" class=\"width95\" >
                                                <option value=\"partita\">Partita</option>
                                                <option value=\"crono\">Cronologico</option>
                                                <option value=\"flusso\">Flusso</option>
                                            </select>";
                break;

            case "flow_sort":
                $a_filterParams['title'] = "Ordinamento";
                $a_filterParams['input'] = "<select name=\"sort\" class=\"width95\" >
                                                <option value=\"partita\">Partita</option>
                                                <option value=\"crono\">Cronologico</option>
                                                <option value=\"utente\">Alfabetico</option>
                                                <option value=\"flusso\">Flusso</option>
                                            </select>";
                break;

            case "courtHearing_sort":
                $a_filterParams['title'] = "Ordinamento";
                $a_filterParams['input'] = "<select name=\"sort\" class=\"width95\" >
                                                <option value=\"courtHearingDate\">Data udienza</option>
                                                <option value=\"partita\">Partita</option>
                                                <option value=\"utente\">Alfabetico</option>
                                            </select>";
                break;

            case "deliveredTo":
                $a_filterParams['title'] = "Consegnato ad";
                $a_filterParams['input'] = "<select name=\"delivered\" class=\"width95\" >
                                                <option value=\"riscossione\">Ufficiale riscossioni</option>
                                                <option value=\"giudiziario\">Ufficiale giudiziario</option>
                                            </select>";
                break;

            case "courtHearingDate":
                $a_filterParams['title'] = "Data udienza";
                $a_filterParams['input'] = "Dal <input id=\"from_courtHearingDate\" name=\"from_courtHearingDate\" class=\"text_center width30 picker\" onchange=\"changeDate('courtHearing')\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input id=\"to_courtHearingDate\" name=\"to_courtHearingDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data assente <input type=checkbox name=\"no_courtHearingDate\" value=\"y\" >";
                break;

            case "debtorNotificationDate":
                $a_filterParams['title'] = "Data notifica debitore";
                $a_filterParams['input'] = "Dal <input id=\"from_debtorNotification_Date\" name=\"from_debtorNotification_Date\" class=\"text_center width30 picker\" onchange=\"changeDate('courtHearing')\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input id=\"to_debtorNotification_Date\" name=\"to_debtorNotification_Date\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data assente <input type=checkbox name=\"no_debtorNotification_Date\" value=\"y\" >";
                break;

            case "actNotificationDate":
                $a_filterParams['title'] = "Data di notifica atto";
                $a_filterParams['input'] = "Dal <input name=\"from_notificationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_notificationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"exist_notificationDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            case "pignoNotificationDate":

                $a_filterParams['title'] = "Data di notifica pignoramento";
                $a_filterParams['input'] = "Dal <input name=\"from_pignoNotificationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input name=\"to_pignoNotificationDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data <select name=\"exist_pignoNotificationDate\">
                                                            <option value=''></option>
                                                            <option value='y'>Presente</option>
                                                            <option value='n'>Assente</option>
                                                        </select>";
                break;

            case "dateShipment":
                $a_filterParams['title'] = "Data di spedizione";
                $a_filterParams['input'] = "Dal <input id=\"from_shipment_date\" name=\"from_shipment_date\" class=\"text_center width30 picker\" onchange=\"changeDate('courtHearing')\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input id=\"to_shipment_date\" name=\"to_courtHearingDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data assente <input type=checkbox name=\"no_shipment_date\" value=\"y\" >";
                break;

            case "deliveryDate":
                $a_filterParams['title'] = "Data di consegna";
                $a_filterParams['input'] = "Dal <input id=\"from_deliveryDate\" name=\"from_deliveryDate\" class=\"text_center width30 picker\" onchange=\"changeDate('courtHearing')\">";
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input id=\"to_deliveryDate\" name=\"to_courtHearingDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data assente <input type=checkbox name=\"no_deliveryDate\" value=\"y\" >";
                break;
        }
        return $a_filterParams;
    }

    public function getFilters(){
        $html = "";
        for($i=0;$i<count($this->a_filters);$i++){
            $html.= $this->addFilterDiv($this->setFilter($this->a_filters[$i]));
        }
        return $html;
    }

    public function addFilterDiv($a_params){
        $div = "<div class=\"width90 text_center\" style=\"clear:both;height:23px;\">";
        $div.= "    <div style=\"float:left;\" class=\"width25 text_left\"><span class=\"color_titolo font_bold\">".$a_params['title']."</span></div>";
        $div.= "    <div style=\"float:left;\" class=\"width40 text_center\">".$a_params['input']."</div>";
        $div.= "    <div style=\"float:left;\" class=\"width35 text_center\">".$a_params['secondInput']."</div>";
        $div.= "</div>";
        return $div;
    }

    function getWhereFromFilters($filter, $table=null, $tipo = "atto")
    {

        $cls_help = new cls_help();

        $where = "";
        $table1 = "";
        $table2 = "";

        if($tipo == "pigno")
        {
            $table1 = "v_pignoramento.";
            $table2 = "v_pignoramento.";
        }

        //TIPO UFFICIALE
        if (isset($filter['delivered'])) {
            if($filter['delivered']!=""){
                if ($where != "")
                    $where .= "AND ";

                $where .= "Tipo_Ufficiale = '" . $filter['delivered'] . "' ";
            }
        }

        //DATA NOTIFICA DEBITORE
        if (isset($filter['debitorNotificatonDateF'])) {
            if($this->cls_help->toDbDate($filter['debitorNotificatonDateF'])!=null) {
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Notifica >= '" . $this->cls_help->toDbDate($filter['debitorNotificatonDateF']) . "' ";
                if ($this->cls_help->toDbDate($filter['debitorNotificatonDateT']) != null)
                    $where .= "AND Data_Notifica <= '" . $this->cls_help->toDbDate($filter['debitorNotificatonDateT']) . "' ";
                $where .= ") ";
            }

            if(isset($filter['debitorNotificatonDateN'])){
                if($filter['debitorNotificatonDateN']!="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Notifica is null ";
                }
                else if($filter['debitorNotificatonDateN']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Notifica is not null ";
                }
            }
        }

        //DATA SPEDIZIONE
        if (isset($filter['shipmentDateF'])) {
            if($this->cls_help->toDbDate($filter['shipmentDateF'])!=null) {
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Spedizione >= '" . $this->cls_help->toDbDate($filter['shipmentDateF']) . "' ";
                if ($this->cls_help->toDbDate($filter['shipmentDateT']) != null)
                    $where .= "AND Data_Spedizione <= '" . $this->cls_help->toDbDate($filter['shipmentDateT']) . "' ";
                $where .= ") ";
            }

            if(isset($filter['shipmentDateN'])){
                if($filter['shipmentDateN']!="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Spedizione is null ";
                }
                else if($filter['shipmentDateN']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Spedizione is not null ";
                }
            }
        }

        if(isset($filter['city'])){
            if($filter['city']!=""){
                if ($where != "")
                    $where .= " AND ";

                if(strpos($filter['city'],"AUTH_")===false)
                    $where.= " CC = '".$filter['city']."' ";
                else if($this->a_enti!=null && count($this->a_enti)>0){
                    $a_auth = explode("_",$filter['city']);
                    $whereCC = null;
                    foreach ($this->a_enti as $key=>$a_ente){
                        if(($a_auth[1]==$a_ente['Autorizzazione'] || $a_auth[1]==1)){
                            if($whereCC!=null)
                                $whereCC.= ", ";
                            $whereCC.= "'".$a_ente['CC']."'";
                        }
                    }
                    if($whereCC!=null)
                        $where.= " CC IN ( ".$whereCC. " ) ";

                }
                else{
                    $where.= " ERRORE ";
                }
            }
        }
        
        //DATA CONSEGNA
        if (isset($filter['deliveryDateF'])) {
            if($this->cls_help->toDbDate($filter['deliveryDateF'])!=null) {
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Consegna >= '" . $this->cls_help->toDbDate($filter['deliveryDateF']) . "' ";
                if ($this->cls_help->toDbDate($filter['deliveryDateT']) != null)
                    $where .= "AND Data_Consegna <= '" . $this->cls_help->toDbDate($filter['deliveryDateT']) . "' ";
                $where .= ") ";
            }

            if(isset($filter['deliveryDateN'])){
                if($filter['deliveryDateN']!="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Consegna is null ";
                }
                else if($filter['deliveryDateN']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Consegna is not null ";
                }
            }
        }

        //STAMPATORE
        if (isset($filter['PrinterId'])) {
            if($filter['PrinterId']>0){
                if ($where != "")
                    $where .= "AND ";

                $where .= "PrinterId = " . $filter['PrinterId'] . " ";
            }
        }

        //PRESENZA CRONOLOGICO
        if (isset($filter['chron'])) {
            if($filter['chron']!=""){
                if ($where != "")
                    $where .= "AND ";

                if($filter['chron'] == "si") $where .= "(Data_Elaborazione IS NOT NULL AND (ID_Cronologico IS NOT NULL OR ID_Cronologico > 0) AND (Anno_Cronologico IS NOT NULL OR Anno_Cronologico > 0 )) ";
                else $where .= "(Data_Elaborazione IS NOT NULL AND ( ID_Cronologico IS NULL OR ID_Cronologico = 0 )AND ( Anno_Cronologico IS NULL OR Anno_Cronologico = 0)) ";
            }
        }

        //NUMERO PARTITE
        if (isset($filter['from_taxRecord'])) {
            if($filter['from_taxRecord']>0){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( ".$table2."Comune_ID >= " . $filter['from_taxRecord'] . " ";
                if ($filter['to_taxRecord'] > 0)
                    $where .= "AND ".$table2."Comune_ID <= " . $filter['to_taxRecord'] . " ";
                $where .= ") ";
            }
        }

        //ANNO PARTITE
        if (isset($filter['from_taxYear'])) {
            if($filter['from_taxYear']>0){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Anno_Riferimento >= " . $filter['from_taxYear'] . " ";
                if ($filter['to_taxYear'] > 0)
                    $where .= "AND Anno_Riferimento <= " . $filter['to_taxYear'] . " ";
                $where .= ") ";
            }
        }

        if (isset($filter['taxType'])) {
            if ($filter['taxType'] !="") {
                if ($where != "")
                    $where .= "AND ";
                $where .= " Tipo_Riscossione = \"" . $filter['taxType'] . "\" ";

            }
        }

        //ESITI
        if (isset($filter['notificationAndAnomaly'])) {
            if ($filter['notificationAndAnomaly'] !="") {
                if ($where != "")
                    $where .= "AND ";


                if($filter['notificationAndAnomaly']=="y"){
                    $where .= " (  Data_Notifica is not null  OR Motivo_Notifica>0 ) ";
                }
                else
                    $where .= " ( Data_Notifica is null AND Motivo_Notifica=0 ) ";


            }
        }

        //ESITI PIGNORAMENTI
        if (isset($filter['pignoNotificationAndAnomaly'])) {
            if ($filter['pignoNotificationAndAnomaly'] !="") {
                if ($where != "")
                    $where .= "AND ";


                if($filter['pignoNotificationAndAnomaly']=="y"){
                    $where .= " (  Data_Notifica_Pignoramento is not null OR Motivo_Notifica_Pignoramento>0 ) ";
                }
                else
                    $where .= " ( Data_Notifica_Pignoramento is null AND Motivo_Notifica_Pignoramento=0 ) ";


            }
        }

        //TIPOLOGIA FLUSSO
        if (isset($filter['actType'])) {
            if ($filter['actType'] !="") {
                if ($where != "")
                    $where .= " AND ";

                switch($filter['actType']){
                    case "AG":
                        $where .= " DocumentTypeId!=3 AND DocumentTypeId!=11 ";
                        break;
                    case "NO_AG":
                        $where .= " (DocumentTypeId=3 OR DocumentTypeId=11) ";
                        break;
                    case "PIG":
                        $where .= " DocumentTableTypeId=2 ";
                        break;
                    case "AG_NO_PIG":
                        $where .= " DocumentTypeId!=3 AND DocumentTypeId!=11 AND DocumentTableTypeId=1 ";
                        break;
                    default:
                        $where .= " DocumentTypeId=".$filter['actType']." ";

                        break;
                }

            }
        }

        //GIACENZA
        if (isset($filter['notificationStockAtto'])) {
            if($filter['notificationStockAtto']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['notificationStockAtto']=="y")
                    $where .= " Stato_Notifica>0 ";
                else
                    $where .= " (Stato_Notifica=\"\" OR Stato_Notifica is null) ";
            }
        }

        //MODALITA
        if (isset($filter['notificationMode'])) {
            if($filter['notificationMode']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['notificationMode']=="y")
                    $where .= " Modalita_Notifica>0 ";
                else if($filter['notificationMode']=="n")
                    $where .= " (Modalita_Notifica=\"\" OR Modalita_Notifica is null) ";
                else
                    $where .= " Modalita_Notifica=".$filter['notificationMode']." ";
            }
        }

        //MODALITA PIGNORAMENTO
        if (isset($filter['pignoNotificationMode'])) {
            if($filter['pignoNotificationMode']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['pignoNotificationMode']=="y")
                    $where .= " Modalita_Notifica_Pignoramento>0 ";
                else if($filter['pignoNotificationMode']=="n")
                    $where .= " (Modalita_Notifica_Pignoramento=\"\" OR Modalita_Notifica_Pignoramento is null) ";
            }
        }

        //GIACENZA
        if (isset($filter['notificationStock'])) {
            if($filter['notificationStock']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['notificationStock']=="y")
                    $where .= " Stato_Notifica>0 ";
                else if($filter['notificationStock']=="n")
                    $where .= " (Stato_Notifica=\"\" OR Stato_Notifica is null) ";
            }
        }

        //GIACENZA PIGNORAMENTO
        if (isset($filter['pignoNotificationStock'])) {
            if($filter['pignoNotificationStock']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['pignoNotificationStock']=="y")
                    $where .= " Stato_Notifica_Pignoramento>0 ";
                else if($filter['pignoNotificationStock']=="n")
                    $where .= " (Stato_Notifica_Pignoramento=\"\" OR Stato_Notifica_Pignoramento is null) ";
            }
        }

        //ANOMALIA
        if (isset($filter['notificationAnomaly'])) {
            if($filter['notificationAnomaly']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['notificationAnomaly']=="y")
                    $where .= " Motivo_Notifica>0 ";
                else if($filter['notificationAnomaly']=="n")
                    $where .= " (Motivo_Notifica=\"\" OR Motivo_Notifica is null) ";
            }
        }

        //ANOMALIA
        if (isset($filter['pignoNotificationAnomaly'])) {
            if($filter['pignoNotificationAnomaly']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['pignoNotificationAnomaly']=="y")
                    $where .= " Motivo_Notifica_Pignoramento>0 ";
                else if($filter['pignoNotificationAnomaly']=="n")
                    $where .= " (Motivo_Notifica_Pignoramento=\"\" OR Motivo_Notifica_Pignoramento is null) ";
            }
        }

//        //ANOMALIA
//        if (isset($filter['notificationAnomalyAtto'])) {
//            if ($filter['notificationAnomalyAtto']!="") {
//                if ($where != "")
//                    $where .= "AND ";
//
//                if($filter['notificationAnomalyAtto']=="y")
//                    $where .= " Motivo_Notifica_Debitore>0 ";
//                else
//                    $where .= " (Motivo_Notifica_Debitore=0 OR Motivo_Notifica_Debitore is null) ";
//            }
//        }

        if (isset($filter['taxStopFlag'])) {
            if($filter['taxStopFlag']!=""){
                if ($where != "")
                    $where .= "AND ";

                if($filter['taxStopFlag']=="si")
                    $where .= " Flag_Blocco_Coazione = \"si\" ";
                else
                    $where .= " (Flag_Blocco_Coazione!= \"si\" OR Flag_Blocco_Coazione IS NULL) ";
            }
        }

        //DENOMINAZIONE UTENTI
        if (isset($filter['from_surname'])) {
            if($filter['from_surname']!=""){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( ((Cognome_Ditta = \"" . $filter['from_surname'] . "\" ";
                if ($filter['from_name'] != "")
                    $where .= "AND Nome >=\"" . $filter['from_name'] . "\"";

                $where .= ") OR Cognome_Ditta > \"" . $filter['from_surname'] . "\" ) ";

                if ($filter['to_surname'] != "") {
                    $where .= "AND ((Cognome_Ditta = \"" . $filter['to_surname'] . "\" ";
                    if ($filter['from_name'] != "")
                        $where .= "AND Nome <=\"" . $filter['to_name'] . "\"";

                    $where .= ") OR Cognome_Ditta < \"" . $filter['to_surname'] . "\" ) ";
                }

                $where .= " ) ";
            }
        }

        //STATO DI STAMPA
        if (isset($filter['printStatus'])) {
            if ($filter['printStatus'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['printStatus']){
                    case "toPrint":
                        $status = "Da stampare";
                        break;
                    case "printed":
                        $status = "Stampato";
                        break;
                }

                if($filter['printType']=="flow")
                    $status = "Stampato";

                $where .= " Stato_Stampa = \"" . $status . "\" ";
                if($filter['printStatus']=="toPrint" && $tipo == "atto")
                    $where .= " AND ( (Rettifica_Flag!='si' OR Rettifica_Flag IS NULL )AND (Rielabora_Flag!='si' OR Rielabora_Flag IS NULL )) ";
            }
        }

        //STATO DI ELABORAZIONE
        if (isset($filter['elaborationStatusAtto'])) {
            if ($filter['elaborationStatusAtto'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['elaborationStatusAtto']){
                    case "created":
                        $where .= " Data_Elaborazione is not null ";
                        break;
                    case "toCreate":
                        $where .= " Data_Elaborazione is null ";
                        break;
                }


            }
        }

        //STATO DI ELABORAZIONE
        if (isset($filter['elaborationStatusPignoramento'])) {
            if ($filter['elaborationStatusPignoramento'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['elaborationStatusPignoramento']){
                    case "created":
                        $where .= " Data_Elaborazione_Pignoramento is not null ";
                        break;
                    case "toCreate":
                        $where .= " Data_Elaborazione_Pignoramento is null ";
                        break;
                }


            }
        }

        //STATO DI STAMPA
        if (isset($filter['printStatusAtto'])) {
            if ($filter['printStatusAtto'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['printStatusAtto']){
                    case "toPrint":
                        $status = "Da stampare";
                        break;
                    case "printed":
                        $status = "Stampato";
                        break;
                }

                $where .= " Stato_Stampa = \"" . $status . "\" ";
            }
        }

        //STATO DI STAMPA
        if (isset($filter['printStatusPignoramento'])) {
            if ($filter['printStatusPignoramento'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['printStatusPignoramento']){
                    case "toPrint":
                        $status = "Da stampare";
                        break;
                    case "printed":
                        $status = "Stampato";
                        break;
                }

                $where .= " Stato_Stampa_Pignoramento = \"" . $status . "\" ";
            }
        }

        //Stato Pagamenti
        if (isset($filter['paymentStatus'])) {
            if ($filter['paymentStatus'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['paymentStatus']){
                    case "incompleted":
                        $where .= " ((Totale_Pagamenti < Totale_Dovuto) || (Totale_Pagamenti is null AND Totale_Dovuto is not null AND Totale_Dovuto > 0)) ";
                        break;
                    case "no":
                        $where .= " Totale_Pagamenti is null ";
                        break;
                    case "partial":
                        $where .= " IF(Totale_Pagamenti is not null, Totale_Pagamenti, 0) < Totale_Dovuto AND Totale_Pagamenti is not null ";
                        break;
                    case "completed":
                        $where .= " Totale_Pagamenti >= Totale_Dovuto ";
                        break;
                    case "yes":
                        $where .= " Totale_Pagamenti > 0 ";
                        break;
                }
            }
        }

        //Stato Pagamenti
        if (isset($filter['payment'])) {
            if ($filter['payment'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['payment']){
                    case "n":
                        $where .= " (Pagamenti_Atto is null OR Pagamenti_Atto = 0) ";
                        break;
                    case "y":
                        $where .= " Pagamenti_Atto > 0 ";
                        break;
                }
            }
        }

        //Rateizzazione atto
        if (isset($filter['instalmentAtto'])) {
            if ($filter['instalmentAtto'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['instalmentAtto']){
                    case "yes":
                        $where .= " Rate_previste > 0 ";
                        break;
                    case "no":
                        $where .= " Rate_previste = 0 ";
                        break;
                }
            }
        }

        //Rateizzazione atto
        if (isset($filter['instalmentPignoramento'])) {
            if ($filter['instalmentPignoramento'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['instalmentPignoramento']){
                    case "yes":
                        $where .= " Rate_previste_Pignoramento > 0 ";
                        break;
                    case "no":
                        $where .= " Rate_previste_Pignoramento = 0 ";
                        break;
                }
            }
        }

        //TIPO DI STAMPA
        if (isset($filter['printType'])) {
            if ($filter['printType'] !="") {
                if ($where != "")
                    $where .= "AND ";

                switch($filter['printType']){
                    case "temp":
                        if($filter['printStatus']=="toPrint")$where .= " Data_Stampa is null ";
                        else if($filter['printStatus']=="printed") $where .= " Data_Stampa is not null ";
                        else $where .= " 1 = 1 ";
                        break;
                    case "pec":
                        if($filter['printStatus']=="toPrint")$where .= " Data_Stampa is null ";
                        else if($filter['printStatus']=="printed") $where .= " Data_Stampa is not null ";
                        else $where .= " 1 = 1 ";
                        break;
                    case "crono":
                        if($tipo == "atto") $where .= " (Anno_Cronologico IS NULL OR Anno_Cronologico = 0) AND (ID_Cronologico IS NULL OR ID_Cronologico = 0) AND (Cronologico_Vecchio != 'si' OR Cronologico_Vecchio IS NULL) ";
                        else if($tipo == "pigno") $where .= " (Anno_Cronologico IS NULL OR Anno_Cronologico = 0) AND (ID_Cronologico IS NULL OR ID_Cronologico = 0) ";
                        break;


                    case "final":
                        if($filter['printStatus']=="toPrint"){
                            $where .= " Data_Stampa is null ";
                            $where .= " AND Anno_Cronologico>0 AND ID_Cronologico>0 ";
                        }
                        else if($filter['printStatus']=="printed") $where .= " Data_Stampa is not null ";
                        else $where .= " 1 = 1 ";
                        break;
                    case "flow":
                        if($filter['printStatus']=="toPrint"){
                            $where .= " Data_Stampa is not null ";
                            $where .= " AND ( Numero_Flusso=0 OR Numero_Flusso is null ) ";
                        }
                        else if($filter['printStatus']=="printed"){
                            $where .= " Data_Stampa is not null ";
                            $where .= " AND Numero_Flusso>0 ";
                        }else $where .= " 1 = 1 ";
                        break;
                }
            }
        }

        if (isset($filter['PrintTypeId'])) {
            if ($filter['PrintTypeId'] != "" && $filter['PrintTypeId'] != 7) {
                if ($where != "")
                    $where .= "AND ";

                $where .= " PrintTypeId = " . $filter['PrintTypeId'] . " ";
            }
        }

        if (isset($filter['TrafficLaw'])) {
            if ($filter['TrafficLaw'] !="") {
                if ($where != "")
                    $where .= "AND ";

                $where .= " Info_Cartella LIKE \"V. Cron. %\" ";
            }
        }

        if (isset($filter['sendType'])) {
            if ($filter['sendType'] !="") {
                if ($where != "")
                    $where .= "AND ";

                $where .= " Modalita_Stampa = \"" . $filter['sendType'] . "\" ";
            }
        }

        if (isset($filter['officialType'])) {
            if ($filter['officialType'] != "" && $filter['officialType'] != "tutti") {
                if ($where != "")
                    $where .= "AND ";

                $where .= " Tipo_Ufficiale = \"" . $filter['officialType'] . "\" ";
            }
        }

        //DATA ELABORAZIONE
        if (isset($filter['from_elaborationDate'])) {
            if($this->cls_help->toDbDate($filter['from_elaborationDate'])!=null) {
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Elaborazione >= '" . $this->cls_help->toDbDate($filter['from_elaborationDate']) . "' ";
                if ($this->cls_help->toDbDate($filter['to_elaborationDate']) != null)
                    $where .= "AND Data_Elaborazione <= '" . $this->cls_help->toDbDate($filter['to_elaborationDate']) . "' ";
                $where .= ") ";
            }

            if(isset($filter['no_elaborationDate'])){
                if($filter['no_elaborationDate']=="n"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Elaborazione is null ";
                }
                else if($filter['no_elaborationDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Elaborazione is not null ";
                }
            }
        }

        //DATA STAMPA
        if (isset($filter['from_printDate'])) {
            if($this->cls_help->toDbDate($filter['from_printDate'])!=null) {
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Stampa >= '" . $this->cls_help->toDbDate($filter['from_printDate']) . "' ";
                if ($this->cls_help->toDbDate($filter['to_printDate']) != null)
                    $where .= "AND Data_Stampa <= '" . $this->cls_help->toDbDate($filter['to_printDate']) . "' ";
                $where .= ") ";
            }

            if(isset($filter['no_printDate'])){
                if($filter['no_printDate']=="n"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Stampa is null ";
                }
                else if($filter['no_printDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Stampa is not null ";
                }
            }
        }

        //POSIZIONI
        if (isset($filter['positionNotificationLimit'])) {

            if($filter['positionNotificationLimit']=="n"){
                if ($where != "")
                    $where .= "AND ";

//                $where.= " ( ( Data_Notifica is null ";
//                $where.= " AND Data_Notifica_Pignoramento is null ) OR ";
                $where.= " ( ( Data_Notifica is not null AND DATEDIFF('".date('Y-m-d')."',Data_Notifica) > 2367) ";
                $where.= " OR ( Data_Notifica_Pignoramento is not null  AND DATEDIFF('".date('Y-m-d')."',Data_Notifica_Pignoramento) > 2367) ) ";
            }
            else if($filter['positionNotificationLimit']=="y"){
                if ($where != "")
                    $where .= "AND ";

                $where.= " ( ( Data_Notifica is not null AND DATEDIFF('".date('Y-m-d')."',Data_Notifica) < 2367) ";
                $where.= " OR ( Data_Notifica_Pignoramento is not null  AND DATEDIFF('".date('Y-m-d')."',Data_Notifica_Pignoramento) < 2367) ) ";
            }

        }

        // EXPIRED POSITIONS
        if (isset($filter['expiredPosition'])) {
            if($filter['expiredPosition']!=""){
                if ($where != "")
                    $where .= "AND ";

                if($filter['expiredPosition']==0)
                    $where .= " Is_Expired = 0 ";
                else
                    $where .= " Is_Expired = 1 ";
            }
        }

        //DATA NOTIFICA
        if (isset($filter['from_notificationDate'])) {
            if($this->cls_help->toDbDate($filter['from_notificationDate'])!=null){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Notifica >= '" . $this->cls_help->toDbDate($filter['from_notificationDate']) . "' ";
                if ($this->cls_help->toDbDate($filter['to_notificationDate']) != null)
                    $where .= "AND Data_Notifica <= '" . $this->cls_help->toDbDate($filter['to_notificationDate']) . "' ";
                $where .= ") ";

            }
            if(isset($filter['exist_notificationDate'])){
                if($filter['exist_notificationDate']=="n"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Notifica is null ";
                }
                else if($filter['exist_notificationDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Notifica is not null ";
                }
            }
        }

        //DATA NOTIFICA
        if (isset($filter['from_pignoNotificationDate'])) {
            if($this->cls_help->toDbDate($filter['from_pignoNotificationDate'])!=null){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Notifica_Pignoramento >= '" . $this->cls_help->toDbDate($filter['from_pignoNotificationDate']) . "' ";
                if ($this->cls_help->toDbDate($filter['to_pignoNotificationDate']) != null)
                    $where .= "AND Data_Notifica_Pignoramento <= '" . $this->cls_help->toDbDate($filter['to_pignoNotificationDate']) . "' ";
                $where .= ") ";

            }
            if(isset($filter['exist_pignoNotificationDate'])){
                if($filter['exist_pignoNotificationDate']=="n"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Notifica_Pignoramento is null ";
                }
                else if($filter['exist_pignoNotificationDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Data_Notifica_Pignoramento is not null ";
                }
            }
        }

        //DISCARICO
        if (isset($filter['dischargeFlag'])) {
            if($filter['dischargeFlag']!=""){
                if ($where != "")
                    $where .= "AND ";

                if($filter['dischargeFlag']==0)
                    $where .= " Is_Discharged = 0 ";
                else
                    $where .= " Is_Discharged = 1 ";
            }
        }

        //ESTRAZIONE DISCARICO
        if (isset($filter['extractionFlag'])) {
            if($filter['extractionFlag']!=""){
                if ($where != "")
                    $where .= "AND ";

                if($filter['extractionFlag']==0)
                    $where .= " Is_Extracted = 0 ";
                else
                    $where .= " Is_Extracted = 1 ";
            }
        }

        //DATA DISCARICO
        if (isset($filter['from_dischargeDate'])) {
            if($this->cls_help->toDbDate($filter['from_dischargeDate'])!=null){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Discharge_Date >= '" . $this->cls_help->toDbDate($filter['from_dischargeDate']) . "' ";
                if ($this->cls_help->toDbDate($filter['to_dischargeDate']) != null)
                    $where .= "AND Discharge_Date <= '" . $this->cls_help->toDbDate($filter['to_dischargeDate']) . "' ";
                $where .= ") ";

            }
            if(isset($filter['exist_dischargeDate'])){
                echo $filter['exist_dischargeDate'];
                
                if($filter['exist_dischargeDate']=="n"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Discharge_Date is null ";
                }
                else if($filter['exist_dischargeDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Discharge_Date is not null ";
                }
            }
        }

        //DATA ESTRAZIONE DISCARICO
        if (isset($filter['from_extractionDate'])) {
            if($this->cls_help->toDbDate($filter['from_extractionDate'])!=null){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Extraction_Date >= '" . $this->cls_help->toDbDate($filter['from_extractionDate']) . "' ";
                if ($this->cls_help->toDbDate($filter['to_extractionDate']) != null)
                    $where .= "AND Extraction_Date <= '" . $this->cls_help->toDbDate($filter['to_extractionDate']) . "' ";
                $where .= ") ";

            }
            if(isset($filter['exist_extractionDate'])){
                if($filter['exist_extractionDate']=="n"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Extraction_Date is null ";
                }
                else if($filter['exist_extractionDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " Extraction_Date is not null ";
                }
            }
        }

        //DATA Flusso
        if (isset($filter['from_flowDate'])) {
            if($this->cls_help->toDbDate($filter['from_flowDate'])!=null){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Data_Flusso >= '" . $this->cls_help->toDbDate($filter['from_flowDate']) . "' ";
                if ($this->cls_help->toDbDate($filter['to_flowDate']) != null)
                    $where .= "AND Data_Flusso <= '" . $this->cls_help->toDbDate($filter['to_flowDate']) . "' ";
                $where .= ") ";
            }

            if(isset($filter['exist_flowDate'])) {
                if ($filter['exist_flowDate'] == "n") {
                    if ($where != "")
                        $where .= "AND ";

                    $where .= " Data_Flusso is null ";
                } else if ($filter['exist_flowDate'] == "y") {
                    if ($where != "")
                        $where .= "AND ";

                    $where .= " Data_Flusso is not null ";
                }
            }

        }

        //Numero Flusso
        if (isset($filter['flowNumber'])) {
            if($filter['flowNumber']!=null){
                if ($where != "")
                    $where .= "AND ";

                $where .= " Numero_Flusso = " . $filter['flowNumber'] . " ";
            }
        }

        //Numero Flusso
        if (isset($filter['importNotification'])) {
            if($filter['importNotification']!=null){
                if ($where != "")
                    $where .= "AND ";

                if($filter['importNotification']=="y")
                    $where .= " Not_Importata_ID is not null ";
                else if($filter['importNotification']=="n")
                    $where .= " Not_Importata_ID is null ";
            }
        }


        //Numero Flusso
        if (isset($filter['flow'])) {
            if($filter['flow']!=null){
                if ($where != "")
                    $where .= "AND ";

                if($filter['flow']=="y")
                    $where .= " Numero_Flusso>0 ";
                else if($filter['flow']=="n")
                    $where .= " (Numero_Flusso=0 OR Numero_Flusso is null) ";
            }
        }

        //Numero Flusso
        if (isset($filter['flowYear'])) {
            if($filter['flowYear']!=null){
                if ($where != "")
                    $where .= "AND ";

                $where .= " Anno_Flusso = " . $filter['flowYear'] . " ";
            }
        }

        //DATA UDIENZA
        if (isset($filter['from_courtHearingDate'])) {
            if($this->cls_help->toDbDate($filter['from_courtHearingDate'])!=null){
                if ($where != "")
                    $where .= "AND ";

                if($filter['no_courtHearingDate']=="y"){
                    $where.= " Court_Hearing_Date is null ";
                }
                else{
                    $where .= "( Court_Hearing_Date >= '" . $this->cls_help->toDbDate($filter['from_courtHearingDate']) . "' ";
                    if ($this->cls_help->toDbDate($filter['to_courtHearingDate']) != null)
                        $where .= "AND Court_Hearing_Date <= '" . $this->cls_help->toDbDate($filter['to_courtHearingDate']) . "' ";
                    $where .= ") ";
                }
            }
        }

        return $where;
    }

    function getOrder($sort,$type="atto")    {

        $order = "";
        $specificTable = $specificTable2 = "";
        if($type== "pigno")
        {
            $specificTable = "v_pignoramento.";
            $specificTable2 = "v_pignoramento.";
        }


        switch($sort){
            case "crono":
                $order = $specificTable."CC ASC, ".$specificTable."Anno_Cronologico ASC, ".$specificTable."ID_Cronologico ASC, ".$specificTable2."Comune_ID ASC";
                break;
            case "partita":
                $order = $specificTable."CC ASC, ".$specificTable2."Comune_ID ASC";
                break;
            case "utente":
                $order = $specificTable."CC ASC, ".$specificTable2."Cognome_Ditta ASC, ".$specificTable2."Nome ASC";
                break;
            case "courtHearingDate":
                $order = $specificTable."CC ASC, Court_Hearing_Date ASC, Court_Hearing_Time ASC";
                break;
            case "flusso":
                $order = $specificTable."CC ASC, ".$specificTable."Anno_Flusso ASC, ".$specificTable."Numero_Flusso ASC";
                break;
        }
        return $order;
    }

    public function getFiltersDescription($filter){
        $a_return = array();
        $i=0;
        if(isset($filter['city'])){
            if($filter['city']!=""){
                $a_return[$i]['label'] = "ENTE";
                if(strpos($filter['city'],"AUTH_")===false)
                    $a_return[$i]['value'] = $filter['city'];
                else{
                    $a_auth = explode("_",$filter['city']);
                    switch($a_auth[1]){
                        case 1:
                            $a_return[$i]['value'] = "TUTTI";
                            break;
                        case 3:
                            $a_return[$i]['value'] = "IRTEL";
                            break;
                        case 4:
                            $a_return[$i]['value'] = "COMUNITA' MONTANA PELIGNA";
                            break;
                        case 5:
                            $a_return[$i]['value'] = "ALBUZZANO - BORGO SAN SIRO - GARLASCO";
                            break;
                        default:
                            $a_return[$i]['value'] = "LISTA";
                    }
                }


                $i++;
            }
        }

        if(isset($filter['from_surname'])){
            if($filter['from_surname']!=""){
                $a_return[$i]['label'] = "UTENTE";
                $a_return[$i]['value'] = "Da ".$filter['from_surname'];
                if(isset($filter['from_name']))
                    if($filter['from_name']!="")
                        $a_return[$i]['value'].= " ".$filter['from_name'];
                if(isset($filter['to_surname']))
                    if($filter['to_surname']!="")
                        $a_return[$i]['value'].= " a ".$filter['to_surname'];
                if(isset($filter['to_name']))
                    if($filter['to_name']!="")
                        $a_return[$i]['value'].= " ".$filter['to_name'];

                $i++;
            }
        }

        if(isset($filter['from_taxRecord'])){
            if($filter['from_taxRecord']!=""){
                $a_return[$i]['label'] = "PARTITE";
                $a_return[$i]['value'] = "Da ".$filter['from_taxRecord'];
                if(isset($filter['to_taxRecord']))
                    if($filter['to_taxRecord']!="")
                        $a_return[$i]['value'].= " a ".$filter['to_taxRecord'];

                $i++;
            }
        }

        if(isset($filter['from_taxYear'])){
            if($filter['from_taxYear']!=""){
                $a_return[$i]['label'] = "ANNI";
                $a_return[$i]['value'] = "Dal ".$filter['from_taxYear'];
                if(isset($filter['to_taxYear']))
                    if($filter['to_taxYear']!="")
                        $a_return[$i]['value'].= " al ".$filter['to_taxYear'];

                $i++;
            }
        }

        if(isset($filter['taxType'])){
            if($filter['taxType']!=""){
                $a_return[$i]['label'] = "TIPO RISCOSSIONE";
                $a_return[$i]['value'] = $filter['taxType'];

                $i++;
            }
        }

        if(isset($filter['from_elaborationDate'])){
            if($filter['no_elaborationDate']!="y"){
                if($this->cls_help->toDbDate($filter['from_elaborationDate'])!=null){
                    $a_return[$i]['label'] = "DATA DI ELABORAZIONE";

                    $a_return[$i]['value'] = "DAL ".$this->cls_help->toItalianDate($filter['from_elaborationDate']);
                    if($filter['to_elaborationDate']!=null)
                        $a_return[$i]['value'].= " AL ".$filter['to_elaborationDate'];
                    $i++;
                }
            }
            else{
                $a_return[$i]['label'] = "DATA DI ELABORAZIONE";
                $a_return[$i]['value'] = "ASSENTE";

                $i++;
            }
        }

        if(isset($filter['from_printDate'])){
            if($filter['no_printDate']!="y"){
                if($this->cls_help->toDbDate($filter['from_printDate'])!=null){
                    $a_return[$i]['label'] = "DATA DI STAMPA";

                    $a_return[$i]['value'] = "DAL ".$this->cls_help->toItalianDate($filter['from_printDate']);
                    if($filter['to_printDate']!=null)
                        $a_return[$i]['value'].= " AL ".$filter['to_printDate'];
                    $i++;
                }
            }
            else{
                $a_return[$i]['label'] = "DATA DI ELABORAZIONE";
                $a_return[$i]['value'] = "ASSENTE";

                $i++;
            }
        }

        if(isset($filter['from_notificationDate'])){

            if($this->cls_help->toDbDate($filter['from_notificationDate'])!=null){
                $a_return[$i]['label'] = "DATA DI NOTIFICA";

                $a_return[$i]['value'] = "DAL ".$this->cls_help->toItalianDate($filter['from_notificationDate']);
                if($filter['to_notificationDate']!=null)
                    $a_return[$i]['value'].= " AL ".$filter['to_notificationDate'];
                $i++;
            }

            if(isset($filter['exist_notificationDate'])) {
                if ($filter['exist_notificationDate'] == "y") {
                    $a_return[$i]['label'] = "DATA DI NOTIFICA";
                    $a_return[$i]['value'] = "PRESENTE";
                    $i++;
                } else if ($filter['exist_notificationDate'] == "n") {
                    $a_return[$i]['label'] = "DATA DI NOTIFICA";
                    $a_return[$i]['value'] = "ASSENTE";

                    $i++;
                }
            }
        }

        if(isset($filter['from_pignoNotificationDate'])){

            if($this->cls_help->toDbDate($filter['from_pignoNotificationDate'])!=null){
                $a_return[$i]['label'] = "DATA DI NOTIFICA PIGNORAMENTO";

                $a_return[$i]['value'] = "DAL ".$this->cls_help->toItalianDate($filter['from_pignoNotificationDate']);
                if($filter['to_pignoNotificationDate']!=null)
                    $a_return[$i]['value'].= " AL ".$filter['to_pignoNotificationDate'];
                $i++;
            }

            if(isset($filter['exist_pignoNotificationDate'])) {
                if ($filter['exist_pignoNotificationDate'] == "y") {
                    $a_return[$i]['label'] = "DATA DI NOTIFICA PIGNORAMENTO";
                    $a_return[$i]['value'] = "PRESENTE";
                    $i++;
                } else if ($filter['exist_pignoNotificationDate'] == "n") {
                    $a_return[$i]['label'] = "DATA DI NOTIFICA PIGNORAMENTO";
                    $a_return[$i]['value'] = "ASSENTE";

                    $i++;
                }
            }
        }

        if(isset($filter['from_dischargeDate'])){

            if($this->cls_help->toDbDate($filter['from_dischargeDate'])!=null){
                $a_return[$i]['label'] = "DATA DI DISCARICO";

                $a_return[$i]['value'] = "DAL ".$this->cls_help->toItalianDate($filter['from_dischargeDate']);
                if($filter['to_dischargeDate']!=null)
                    $a_return[$i]['value'].= " AL ".$filter['to_dischargeDate'];
                $i++;
            }

            if(isset($filter['exist_dischargeDate'])) {
                if ($filter['exist_dischargeDate'] == "y") {
                    $a_return[$i]['label'] = "DATA DI DISCARICO";
                    $a_return[$i]['value'] = "PRESENTE";
                    $i++;
                } else if ($filter['exist_dischargeDate'] == "n") {
                    $a_return[$i]['label'] = "DATA DI DISCARICO";
                    $a_return[$i]['value'] = "ASSENTE";

                    $i++;
                }
            }
        }

        if(isset($filter['from_flowDate'])){

            if($this->cls_help->toDbDate($filter['from_flowDate'])!=null){
                $a_return[$i]['label'] = "DATA DEL FLUSSO";

                $a_return[$i]['value'] = "DAL ".$this->cls_help->toItalianDate($filter['from_flowDate']);
                if($filter['to_flowDate']!=null)
                    $a_return[$i]['value'].= " AL ".$filter['to_flowDate'];
                $i++;
            }

            if(isset($filter['exist_flowDate'])) {
                if ($filter['exist_flowDate'] == "y") {
                    $a_return[$i]['label'] = "DATA DEL FLUSSO";
                    $a_return[$i]['value'] = "PRESENTE";
                    $i++;
                } else if ($filter['exist_flowDate'] == "n") {
                    $a_return[$i]['label'] = "DATA DEL FLUSSO";
                    $a_return[$i]['value'] = "ASSENTE";
                    $i++;
                }
            }
        }

        if(isset($filter['flowNumber'])){
            if($filter['flowNumber']!=""){
                $a_return[$i]['label'] = "FLUSSO";

                $a_return[$i]['value'] = "NUMERO ". $filter['flowNumber'];
                if($filter['flowYear']!="")
                    $a_return[$i]['value'].= " DEL ". $filter['flowYear'];


                $i++;
            }
        }

        if(isset($filter['flow'])){
            if($filter['flow']!=""){
                $a_return[$i]['label'] = "FLUSSO";

                if($filter['flow']=="y")
                    $a_return[$i]['value'] = "PRESENTE";
                else if($filter['flow']=="n")
                    $a_return[$i]['value'] = "ASSENTE";


                $i++;
            }
        }

        if(isset($filter['importNotification'])){
            if($filter['importNotification']!=""){
                $a_return[$i]['label'] = "IMPORTAZIONE NOTIFICA";

                if($filter['importNotification']=="y")
                    $a_return[$i]['value'] = "PRESENTE";
                else if($filter['importNotification']=="n")
                    $a_return[$i]['value'] = "ASSENTE";


                $i++;
            }
        }

        if(isset($filter['positionNotificationLimit'])){
            if($filter['positionNotificationLimit']!=""){
                $a_return[$i]['label'] = "POSIZIONI";

                switch($filter['positionNotificationLimit']){
                    case "y":
                        $a_return[$i]['value'] = "ATTIVE";
                        break;
                    case "n":
                        $a_return[$i]['value'] = "SCADUTE";
                        break;
                }


                $i++;
            }
        }

        if(isset($filter['notificationImg'])){
            if($filter['notificationImg']!=""){
                $a_return[$i]['label'] = "CARTOLINE";

                switch($filter['notificationImg']){
                    case "y":
                        $a_return[$i]['value'] = "FILE PRESENTI";
                        break;
                    case "n":
                        $a_return[$i]['value'] = "FILE ASSENTI ";
                        break;
                    case "db":
                        $a_return[$i]['value'] = "FILE ASSENTI REGISTRATI NEL DB";
                        break;
                    case "all":
                        $a_return[$i]['value'] = "TUTTE";
                        break;
                }


                $i++;
            }
        }

        if(isset($filter['PrintTypeId'])){
            if($filter['PrintTypeId']!=""){
                $a_return[$i]['label'] = "TIPO DI SPEDIZIONE";

                switch($filter['PrintTypeId']){
                    case 1:
                        $a_return[$i]['value'] = "RACCOMANDATA AG";
                        break;
                    case 2:
                        $a_return[$i]['value'] = "RACCOMANDATA AR";
                        break;
                    case 3:
                        $a_return[$i]['value'] = "POSTA ORDINARIA";
                        break;
                    case 6:
                        $a_return[$i]['value'] = "A MANI";
                        break;
                }


                $i++;
            }
        }

        if(isset($filter['lastAct'])){
            if($filter['lastAct']!=""){
                $a_return[$i]['label'] = "ATTI";

                if($filter['lastAct']=="last")
                    $a_return[$i]['value'] = "ULTIMO ATTO STAMPATO PER PARTITA CONTABILE";

                $i++;
            }
        }

        if(isset($filter['actType'])){
            if($filter['actType']!=""){
                $a_return[$i]['label'] = "TIPOLOGIA ATTI";

                switch($filter['actType']){
                    case "AG":
                        $a_return[$i]['value'] = "Tutti gli Atti giudiziari";
                        break;
                    case "NO_AG":
                        $a_return[$i]['value'] = "Posta ordinaria";
                        break;
                    case "PIG":
                        $a_return[$i]['value'] = "Pignoramenti";
                        break;
                    case "AG_NO_PIG":
                        $a_return[$i]['value'] = "Atti giudiziari esclusi i Pignoramenti";
                        break;
                    case "SOLL_PRE":
                        $a_return[$i]['value'] = "Solleciti pre Ingiunzione";
                        break;
                    case "ING":
                        $a_return[$i]['value'] = "Ingiunzioni";
                        break;
                    case "SOLL_POST":
                        $a_return[$i]['value'] = "Solleciti post Ingiunzione";
                        break;
                    case "AVV_INT":
                        $a_return[$i]['value'] = "Avvisi di Intimazione ad Adempiere";
                        break;
                    case "AVV_MORA":
                        $a_return[$i]['value'] = "Avvisi di Messa in Mora";
                        break;
                    case "PIG_BANCA":
                        $a_return[$i]['value'] = "Pignoramenti presso Banca";
                        break;
                    case "PIG_LAVORO":
                        $a_return[$i]['value'] = "Pignoramenti presso Datore di lavoro";
                        break;
                    case "PIG_VEICOLO":
                        $a_return[$i]['value'] = "Pignoramenti del veicolo";
                        break;
                }

                $i++;
            }
        }

        if(isset($filter['TrafficLaw'])){
            if($filter['TrafficLaw']!=""){
                $a_return[$i]['label'] = "IMPORTATI DA";

                switch($filter['TrafficLaw']){
                    case "1":    $a_return[$i]['value'] = "SOLO GITCO CDS";    break;
                }

                $i++;
            }
        }

        if(isset($filter['elaborationStatusAtto'])){
            if($filter['elaborationStatusAtto']!=""){
                $a_return[$i]['label'] = "ELABORAZIONE ATTO";

                switch($filter['elaborationStatusAtto']){
                    case "toCreate":    $a_return[$i]['value'] = "DA ELABORARE";    break;
                    case "created":    $a_return[$i]['value'] = "ELABORATO";    break;
                }

                $i++;
            }
        }

        if(isset($filter['PrinterId'])){
            if($filter['PrinterId']!=""){
                $a_return[$i]['label'] = "STAMPATORE";

                switch($filter['PrinterId']){
                    case 1:    $a_return[$i]['value'] = "SARIDA UFFICIO";    break;
                    case 2:    $a_return[$i]['value'] = "MERCURIO SERVICE";    break;
                }

                $i++;
            }
        }

        if(isset($filter['printStatusAtto'])){
            if($filter['printStatusAtto']!=""){
                $a_return[$i]['label'] = "STAMPA ATTO";

                switch($filter['printStatusAtto']){
                    case "toPrint":    $a_return[$i]['value'] = "DA STAMPARE";    break;
                    case "printed":    $a_return[$i]['value'] = "STAMPATO";    break;
                }

                $i++;
            }
        }

        if(isset($filter['elaborationStatusPignoramento'])){
            if($filter['elaborationStatusPignoramento']!=""){
                $a_return[$i]['label'] = "ELABORAZIONE PIGNO.";

                switch($filter['elaborationStatusPignoramento']){
                    case "toCreate":    $a_return[$i]['value'] = "DA ELABORARE";    break;
                    case "created":    $a_return[$i]['value'] = "ELABORATO";    break;
                }

                $i++;
            }
        }

        if(isset($filter['printStatusPignoramento'])){
            if($filter['printStatusPignoramento']!=""){
                $a_return[$i]['label'] = "STAMPA PIGNO.";

                switch($filter['printStatusPignoramento']){
                    case "toPrint":    $a_return[$i]['value'] = "DA STAMPARE";    break;
                    case "printed":    $a_return[$i]['value'] = "STAMPATO";    break;
                }

                $i++;
            }
        }

        if(isset($filter['paymentStatus'])){
            if($filter['paymentStatus']!=""){
                $a_return[$i]['label'] = "PAGAMENTI";

                switch($filter['paymentStatus']){
                    case "incompleted":     $a_return[$i]['value'] = "INCOMPLETI ( NESSUNO + PARZIALI )";    break;
                    case "partial":         $a_return[$i]['value'] = "PARZIALI";    break;
                    case "completed":       $a_return[$i]['value'] = "COMPLETI";    break;
                    case "no":              $a_return[$i]['value'] = "NESSUNO";    break;
                    case "yes":              $a_return[$i]['value'] = "PRESENTI ( QUALSIASI PAGAMENTO )";    break;
                }

                $i++;
            }
        }

        if(isset($filter['payment'])){
            if($filter['payment']!=""){
                $a_return[$i]['label'] = "PAGAMENTI";

                switch($filter['payment']){
                    case "n":           $a_return[$i]['value'] = "ASSENTI";    break;
                    case "y":           $a_return[$i]['value'] = "PRESENTI";    break;
                }

                $i++;
            }
        }

        if(isset($filter['instalmentAtto'])){
            if($filter['instalmentAtto']!=""){
                $a_return[$i]['label'] = "RATEIZ. ATTO";

                switch($filter['instalmentAtto']){
                    case "yes":    $a_return[$i]['value'] = "PRESENTE";    break;
                    case "no":    $a_return[$i]['value'] = "ASSENTE";    break;
                }

                $i++;
            }
        }

        if(isset($filter['instalmentStatusAtto'])){
            if($filter['instalmentStatusAtto']!=""){
                $a_return[$i]['label'] = "STATO RATEIZZAZIONE ATTO";

                switch($filter['instalmentStatusAtto']){
                    case "ongoing":         $a_return[$i]['value'] = "IN CORSO";    break;
                    case "expired":         $a_return[$i]['value'] = "SCADUTA";     break;
                    case "completed":       $a_return[$i]['value'] = "COMPLETATA";  break;
                }

                $i++;
            }
        }

        if(isset($filter['instalmentPignoramento'])){
            if($filter['instalmentPignoramento']!=""){
                $a_return[$i]['label'] = "RATEIZZAZIONE PIGNO.";

                switch($filter['instalmentPignoramento']){
                    case "yes":    $a_return[$i]['value'] = "PRESENTE";    break;
                    case "no":    $a_return[$i]['value'] = "ASSENTE";    break;
                }

                $i++;
            }
        }

        if(isset($filter['instalmentStatusPignoramento'])){
            if($filter['instalmentStatusPignoramento']!=""){
                $a_return[$i]['label'] = "STATO RATEIZZAZIONE PIGNO.";

                switch($filter['instalmentStatusPignoramento']){
                    case "ongoing":         $a_return[$i]['value'] = "IN CORSO";    break;
                    case "expired":         $a_return[$i]['value'] = "SCADUTA";     break;
                    case "completed":       $a_return[$i]['value'] = "COMPLETATA";  break;
                }

                $i++;
            }
        }

        if(isset($filter['notificationAndAnomaly'])){
            if($filter['notificationAndAnomaly']!=""){
                $a_return[$i]['label'] = "NOTIFICHE";

                if($filter['notificationAndAnomaly']=="y")
                    $notification = "PRESENTI";
                else
                    $notification = "ASSENTI";
                $a_return[$i]['value'] = $notification;

                $i++;
            }
        }

        if(isset($filter['pignoNotificationAndAnomaly'])){
            if($filter['pignoNotificationAndAnomaly']!=""){
                $a_return[$i]['label'] = "NOTIFICHE PIGNORAMENTO";

                if($filter['pignoNotificationAndAnomaly']=="y")
                    $notification = "PRESENTI";
                else
                    $notification = "ASSENTI";
                $a_return[$i]['value'] = $notification;

                $i++;
            }
        }

        if(isset($filter['notificationStockAtto'])){
            if($filter['notificationStockAtto']!=""){
                $a_return[$i]['label'] = "GIACENZA/ANOMALIA";

                if($filter['notificationStockAtto']=="y")
                    $notification = "PRESENTE";
                else if($filter['notificationStockAtto']=="n")
                    $notification = "ASSENTE";

                $a_return[$i]['value'] = $notification;

                $i++;
            }
        }

        if(isset($filter['notificationMode'])){
            if($filter['notificationMode']!=""){
                $a_return[$i]['label'] = "MODALITA' NOT.";

                if($filter['notificationMode']=="y")
                    $notification = "PRESENTE";
                else if($filter['notificationMode']=="n")
                    $notification = "ASSENTE";
                else
                    $notification = "";

                $a_return[$i]['value'] = $notification;

                $i++;
            }
        }

        if(isset($filter['notificationStock'])){
            if($filter['notificationStock']!=""){
                $a_return[$i]['label'] = "MODALITA' NOT.";

                if($filter['notificationStock']=="y")
                    $notification = "PRESENTE";
                else if($filter['notificationStock']=="n")
                    $notification = "ASSENTE";
                else
                    $notification = "";

                $a_return[$i]['value'] = $notification;

                $i++;
            }
        }

        if(isset($filter['notificationAnomaly'])){
            if($filter['notificationAnomaly']!=""){
                $a_return[$i]['label'] = "MODALITA' NOT.";

                if($filter['notificationAnomaly']=="y")
                    $notification = "PRESENTE";
                else if($filter['notificationAnomaly']=="n")
                    $notification = "ASSENTE";
                else
                    $notification = "";

                $a_return[$i]['value'] = $notification;

                $i++;
            }
        }

//        if(isset($filter['notificationAnomalyAtto'])){
//            if($filter['notificationAnomalyAtto']!=""){
//                $a_return[$i]['label'] = "GIACENZA ATTO";
//
//                if($filter['notificationAnomalyAtto']=="y")
//                    $notification = "PRESENTE";
//                else if($filter['notificationAnomalyAtto']=="n")
//                    $notification = "ASSENTE";
//
//                $a_return[$i]['value'] = $notification;
//
//                $i++;
//            }
//        }

        if(isset($filter['taxStopFlag'])){
            if($filter['taxStopFlag']!=""){
                $a_return[$i]['label'] = "BLOCCO RISCOSSIONE";
                $a_return[$i]['value'] = strtoupper($filter['taxStopFlag']);

                $i++;
            }
        }

        if(isset($filter['dischargeFlag'])){
            if($filter['dischargeFlag']!=""){
                $a_return[$i]['label'] = "DISCARICO";
                if($filter['dischargeFlag']=="0")
                    $a_return[$i]['value'] = "NO";
                else if($filter['dischargeFlag']=="1")
                    $a_return[$i]['value'] = "SI";

                $i++;
            }
        }

        if(isset($filter['extractionFlag'])){
            if($filter['extractionFlag']!=""){
                $a_return[$i]['label'] = "ESTRAZIONE DISCARICO";
                if($filter['extractionFlag']=="0")
                    $a_return[$i]['value'] = "NO";
                else if($filter['extractionFlag']=="1")
                    $a_return[$i]['value'] = "SI";

                $i++;
            }
        }

        if(isset($filter['sort'])){
            if($filter['sort']!=""){
                $a_return[$i]['label'] = "ORDINAMENTO";
                switch($filter['sort']){
                    case "crono":
                        $filter['sort'] = "Cronologico";
                        break;
                    case "partita":
                        $filter['sort'] = "Partita";
                        break;
                    case "utente":
                        $filter['sort'] = "Alfabetico";
                        break;
                    case "courtHearingDate":
                        $filter['sort'] = "Data udienza";
                        break;
                }
                $a_return[$i]['value'] = strtoupper($filter['sort']);

                $i++;
            }
        }

        if(isset($filter['from_courtHearingDate'])){
            if($filter['no_flowDate']!="y"){
                if($this->cls_help->toDbDate($filter['from_courtHearingDate'])!=null){
                    $a_return[$i]['label'] = "DATA UDIENZA";

                    $a_return[$i]['value'] = "DAL ".$this->cls_help->toItalianDate($filter['from_courtHearingDate']);
                    if($filter['to_courtHearingDate']!=null)
                        $a_return[$i]['value'].= " AL ".$filter['to_courtHearingDate'];
                    $i++;
                }
            }
            else{
                $a_return[$i]['label'] = "DATA UDIENZA";
                $a_return[$i]['value'] = "ASSENTE";

                $i++;
            }
        }
        return $a_return;
    }
}

