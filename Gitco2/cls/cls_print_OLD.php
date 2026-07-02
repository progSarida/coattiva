<?php

include_once CLS."/cls_help.php";
//include_once CLASSI."/parametri.php";

class cls_print{

    public $a_type = array();
    public $a_filters = array();
    public $citySelect = null;
    public $tax_firstOpt = null;
    public $year_blank = "n";
    public $options = null;
    public $cls_help = null;

    public function __construct($printType, $type, $a_city = null, $options = null){
        $this->setPrintParams($printType, $type);
        if($a_city!=null){
            if(($_SESSION['CC_User']=="****" || $_SESSION['CC_User']=="***+") && $_SESSION['aut_tipo']==1)
                $this->citySelect = "<option value='".$a_city['cc']."'>".$a_city['city']."</option><option>Tutti</option>";
            else
                $this->citySelect = "<option value='".$a_city['cc']."'>".$a_city['city']."</option>";
        }
        if(is_array($options)){
            foreach($options as $key=>$value){
                $this->setOptions($key, $value);
            }
        }
        $this->cls_help = new cls_help();

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
                        $this->a_filters = array("fileType","PrinterId","city","lastAct","actType","flow","flowNumber","flowDate",
                            "notificationDate","importNotification","notificationAndAnomaly",
                            "notificationMode","notificationStock","notificationAnomaly",
                            "payment","taxStopFlag","flow_sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        $this->year_blank = "y";
                        break;
                    case "dettaglio_partita":
                        $this->a_type = array("title"=>"Elenco dettaglio partita","action"=>"list_dettaglioPartita.php");
                        $this->a_filters = array("fileType","taxStopFlag","sort");
                        break;

                    case "court_hearing":
                        $this->a_type = array("title"=>"Elenco udienze","action"=>"list_courtHearing.php");
                        $this->a_filters = array("fileType","city","courtHearingDate","taxStopFlag","courtHearing_sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        $this->year_blank = "y";
                        break;

                    case "positions":
                        $this->a_type = array("title"=>"Elenco posizioni","action"=>"list_positions.php");
                        $this->a_filters = array("fileType","city","elaborationStatusAtto","printStatusAtto",
                            "elaborationStatusPignoramento","printStatusPignoramento",
                            "paymentStatus","instalmentAtto","instalmentStatusAtto",
                            "instalmentPignoramento","instalmentStatusPignoramento", "taxStopFlag","sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        $this->year_blank = "y";
                        break;

                    case "SOLL_PRE":
                        $this->a_type = array("title"=>"Elenco Solleciti pre ingiunzione","action"=>"list_atto.php");
                        $this->a_filters = array("fileType","PrinterId","printStatus","PrintTypeOrdinaria","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","sort");
                        break;
                    case "AV_MORA":
                        $this->a_type = array("title"=>"Elenco Avvisi di messa in mora","action"=>"list_atto.php");
                        $this->a_filters = array("fileType","PrinterId","printStatus","PrintTypeId","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","sort");
                        break;
                    case "ING":
                        $this->a_type = array("title"=>"Elenco Ingiunzioni","action"=>"list_atto.php");
                        $this->a_filters = array(
                            "fileType", "PrinterId", "printStatus", "TrafficLaw", "PrintTypeId", "officialType",
                            "elaborationDate", "printDate", "notificationDate",
                            "notificationMode", "notificationStock", "notificationAnomaly",
                            "flowDate", "flowNumber",
                            "instalmentAtto","paymentStatus",
                            "taxStopFlag","sort"
                        );
                        break;

                    case "AV_INT":
                        $this->a_type = array("title"=>"Elenco Avvisi di Intimazione ad Adempiere","action"=>"list_atto.php");
                        $this->a_filters = array(
                            "fileType", "PrinterId", "printStatus", "PrintTypeId", "officialType",
                            "elaborationDate", "printDate", "notificationDate",
                            "notificationMode", "notificationStock", "notificationAnomaly",
                            "flowDate", "flowNumber",
                            "instalmentAtto","paymentStatus",
                            "taxStopFlag","sort"
                        );
                        break;

                    case "SCORPORO_ING":
                        $this->a_type = array("title"=>"Elenco dettaglio Ingiunzioni","action"=>"list_atto_scorporo.php");
                        $this->a_filters = array("fileType","PrinterId","printStatus","PrintTypeId","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","taxStopFlag","sort");
                        $this->tax_firstOpt = "<option value=''>Tutte</option>";
                        break;
                    case "SOLL_POST":
                        $this->a_type = array("title"=>"Elenco Solleciti post ingiunzione","action"=>"list_atto.php");
                        $this->a_filters = array("fileType","PrinterId","printStatus","PrintTypeOrdinaria","officialType","elaborationDate",
                            "printDate","notificationDate","flowDate","flowNumber","sort");
                        break;
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
        $a_filterParams['secondInput'] = "";
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
                                            </select>";
                break;

            case "finalDate":
                $a_filterParams['title'] = "Data di stampa definitiva";
                $a_filterParams['input'] = "<input name=\"finalDate\" class=\"text_center width30\" value=\"".date("d/m/Y")."\">";

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
                                                <option value='y'>Presente</option>
                                                <option value='n'>Assente</option>
                                                <option value=''>Tutti</option>
                                            </select>";

                break;

            case "printType":
                $a_filterParams['title'] = "Tipo di stampa";
                $a_filterParams['input'] = "<select name=\"printType\" class=\"width95\" onchange=\"changeAction(this);\">
                                                <option value='temp'>Provvisoria</option>
                                                <option value='crono'>Assegnamento cronologici</option>
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
                                                <option selected value='toPrint'>Da stampare</option>
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

            case "notificationStock":
                $a_filterParams['title'] = "Stato giacenza";
                $a_filterParams['input'] = "<select name=\"notificationStock\" class=\"width95\">";
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

            case "notificationAnomalyAtto":
                $a_filterParams['title'] = "Anomalia atto";
                $a_filterParams['input'] = "<select name=\"notificationAnomalyAtto\" class=\"width95\">
                                                <option></option>
                                                <option value='y'>Presente</option>
                                                <option value='n'>Assente</option>
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
            case "notificationAndAnomaly":
                $a_filterParams['title'] = "Notifiche o anomalie";
                $a_filterParams['input'] = "<select name=\"notificationAndAnomaly\" class=\"width95\">
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
                $a_filterParams['input'].= "&nbsp;&nbsp;al <input id=\"to_debtorNotification_Date\" name=\"to_courtHearingDate\" class=\"text_center width30 picker\">";
                $a_filterParams['secondInput'] = "Data assente <input type=checkbox name=\"no_debtorNotification_Date\" value=\"y\" >";
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

                    $where.= " ( Data_Notifica is null OR Data_Notifica='' ) ";
                }
                else if($filter['debitorNotificatonDateN']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " ( Data_Notifica is not null AND Data_Notifica!='' ) ";
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

                    $where.= " ( Data_Spedizione is null OR Data_Spedizione='' ) ";
                }
                else if($filter['shipmentDateN']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " ( Data_Spedizione is not null AND Data_Spedizione!='' ) ";
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

                    $where.= " ( Data_Consegna is null OR Data_Consegna='' ) ";
                }
                else if($filter['deliveryDateN']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " ( Data_Consegna is not null AND Data_Consegna!='' ) ";
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

        //GIACENZA
        if (isset($filter['notificationStock'])) {
            if($filter['notificationStock']!="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['notificationStock']=="y")
                    $where .= " Stato_Notifica>0 ";
                else if($filter['notificationStock']=="n")
                    $where .= " (Stato_Notifica=\"\" OR Stato_Notifica is null) ";
                else
                    $where .= " Stato_Notifica=".$filter['notificationMode']." ";
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
                else
                    $where .= " Motivo_Notifica=".$filter['notificationMode']." ";
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
                    $where .= " Flag_Blocco_Coazione!= \"si\" ";
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
                        $where .= " Totale_Pagamenti < Totale_Dovuto ";
                        break;
                    case "no":
                        $where .= " Totale_Pagamenti is null ";
                        break;
                    case "partial":
                        $where .= " Totale_Pagamenti < Totale_Dovuto AND Totale_Pagamenti is not null ";
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
                        if($filter['printStatus']=="toPrint")$where .= " ( Data_Stampa is null ) ";
                        else if($filter['printStatus']=="printed") $where .= " ( Data_Stampa is not null ) ";
                        else $where .= " 1 = 1 ";
                        break;
                    case "pec":
                        if($filter['printStatus']=="toPrint")$where .= " ( Data_Stampa is null ) ";
                        else if($filter['printStatus']=="printed") $where .= " ( Data_Stampa is not null ) ";
                        else $where .= " 1 = 1 ";
                        break;
                    case "crono":
                        if($tipo == "atto") $where .= " (Anno_Cronologico IS NULL OR Anno_Cronologico = 0) AND (ID_Cronologico IS NULL OR ID_Cronologico = 0) AND (Cronologico_Vecchio != 'si' OR Cronologico_Vecchio IS NULL) ";
                        else if($tipo == "pigno") $where .= " (Anno_Cronologico IS NULL OR Anno_Cronologico = 0) AND (ID_Cronologico IS NULL OR ID_Cronologico = 0) ";
                        break;


                    case "final":
                        if($filter['printStatus']=="toPrint"){
                            $where .= " ( Data_Stampa is null ) ";
                            $where .= " AND Anno_Cronologico>0 AND ID_Cronologico>0 ";
                        }
                        else if($filter['printStatus']=="printed") $where .= " ( Data_Stampa is not null ) ";
                        else $where .= " 1 = 1 ";
                        break;
                    case "flow":
                        if($filter['printStatus']=="toPrint"){
                            $where .= " (  Data_Stampa is not null ) ";
                            $where .= " AND ( Numero_Flusso=0 OR Numero_Flusso is null ) ";
                        }
                        else if($filter['printStatus']=="printed"){
                            $where .= " ( Data_Stampa is not null ) ";
                            $where .= " AND Numero_Flusso>0 ";
                        }else $where .= " 1 = 1 ";
                        break;
                }
            }
        }

        if (isset($filter['PrintTypeId'])) {
            if ($filter['PrintTypeId'] !="") {
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
            if ($filter['officialType'] !="") {
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

                    $where.= " ( Data_Elaborazione is null OR Data_Elaborazione='' ) ";
                }
                else if($filter['no_elaborationDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " ( Data_Elaborazione is not null AND Data_Elaborazione!='' ) ";
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

                    $where.= " ( Data_Stampa is null OR Data_Stampa='' ) ";
                }
                else if($filter['no_printDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " ( Data_Stampa is not null AND Data_Stampa!='' ) ";
                }
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

                    $where.= " ( Data_Notifica is null OR Data_Notifica='' ) ";
                }
                else if($filter['exist_notificationDate']=="y"){
                    if ($where != "")
                        $where .= "AND ";

                    $where.= " ( Data_Notifica is not null AND Data_Notifica!='' ) ";
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

                    $where .= " ( Data_Flusso is null OR Data_Flusso='' ) ";
                } else if ($filter['exist_flowDate'] == "y") {
                    if ($where != "")
                        $where .= "AND ";

                    $where .= " ( Data_Flusso is not null AND Data_Flusso!='' ) ";
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
                    $where.= " ( Court_Hearing_Date is null OR Court_Hearing_Date='' ) ";
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
                $a_return[$i]['value'] = $filter['city'];

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

            if($filter['exist_notificationDate']=="y"){
                $a_return[$i]['label'] = "DATA DI NOTIFICA";
                $a_return[$i]['value'] = "PRESENTE";
                $i++;
            }
            else if($filter['exist_notificationDate']=="n"){
                $a_return[$i]['label'] = "DATA DI NOTIFICA";
                $a_return[$i]['value'] = "ASSENTE";

                $i++;
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
            if($filter['exist_flowDate']=="y"){
                $a_return[$i]['label'] = "DATA DEL FLUSSO";
                $a_return[$i]['value'] = "PRESENTE";
                $i++;
            }
            else if($filter['exist_flowDate']=="n"){
                $a_return[$i]['label'] = "DATA DEL FLUSSO";
                $a_return[$i]['value'] = "ASSENTE";

                $i++;
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
                $a_return[$i]['label'] = "TIPOLOGIA FLUSSO";

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

