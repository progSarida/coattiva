<?php

include_once CLS."/cls_file.php";
include_once CLS."/cls_parameters.php";
include_once CLS."/cls_Utils.php";

class cls_flow{

    public $cls_db;
    public $cc;
    public $tableName;
    public $IDFlusso;

    public $file;

    public $a_docDetails;
    public $flowType;
    public $printType;
    public $docType;

    public $a_flowAttachment;
    public $flowName;
    public $flowArchiveName;
    public $flowDir;
    public $flowExt;
    public $flowArchiveExt;
    public $flowFile;
    public $flowArchiveFile;


    public $flowNumber;
    public $flowYear;
    public $flowDate;
    public $flowTime;


    public $a_textParams;
    public $cls_parameters;

    public $a_typeHeader;

    public $flowRowsNumber;
    public $flowRow=0;
    public $a_header;
    public $a_row;
    public $a_newRow;
    public $a_rows;

    public $a_generalHeader;
    public $a_DocHeader;
    public $a_mod23Header;
    public $a_postalHeader;
    public $a_notificationHeader;
    public $a_managerHeader;
    public $a_recipientHeader;
    public $a_amountsHeader;
    public $a_textHeader;

    public $a_generalRow;
    public $a_docRow;
    public $a_mod23Row;
    public $a_postalRow;
    public $a_notificationRow;
    public $a_managerRow;
    public $a_recipientRow;
    public $a_amountsRow;
    public $a_textRow;
    public $a_textParameters = array();
    public $header = array();
    public $a_newHeader = array();
    public $DocumentTypeId;

    public $array;

    public function __construct($cc, $a_docDetails=null, $rowsNumber=null, $a_textParameters=null, $dir=null, $date=null){
        $this->cls_db = new cls_db();
        $this->cc = $cc;
        $this->flowExt = "txt";
        $this->flowArchiveExt = "zip";
        $this->IDFlusso = null;

        $this->flowYear = date("Y");
        $this->flowTime = date("H-i-s");

        if($rowsNumber!=null)
            $this->flowRowsNumber = $rowsNumber;

        if($a_textParameters!=null)
            $this->a_textParameters = $a_textParameters;

        $this->cls_parameters = new cls_parameters();
        if($dir!=null)
            $this->flowDir = $dir;
        if($a_docDetails!=null && is_array($a_docDetails)){
            $this->setFlowType($a_docDetails, $date);
        }
    }

    public function setArray($key, $array){
        $this->array[$key] = $array;
    }

    public function getFlowListQuery($table, $where=null, $limit=0, $page=1, $order=null){
        $query = "SELECT SQL_CALC_FOUND_ROWS ".$table.".*, document_type.Description AS DocumentType, printer.Name AS PrinterName, print_type.Description AS PrintType, tax_type.Description AS TaxTypeDescription,";
        $query.= "PRINT_INVOICE.Number AS PrintInvoiceNumber, PRINT_INVOICE.Year AS PrintInvoiceYear, PRINT_INVOICE.Date AS PrintInvoiceDate, ";
        $query.= "POSTAGE_INVOICE.Number AS PostageInvoiceNumber, POSTAGE_INVOICE.Year AS PostageInvoiceYear, POSTAGE_INVOICE.Date AS PostageInvoiceDate ";
        $query.= "FROM ".$table." ";
        $query.= "JOIN document_type ON document_type.Id=".$table.".DocumentTypeId ";
        $query.= "JOIN printer ON printer.Id=".$table.".PrinterId ";
        $query.= "JOIN print_type ON print_type.Id=".$table.".PrintTypeId ";
        $query.= "LEFT JOIN tax_type ON tax_type.Id=".$table.".TaxType ";
        $query.= "LEFT JOIN flow_invoices AS PRINT_INVOICE ON PRINT_INVOICE.Id=".$table.".PrintInvoiceId ";
        $query.= "LEFT JOIN flow_invoices AS POSTAGE_INVOICE ON POSTAGE_INVOICE.Id=".$table.".PostageInvoiceId ";
        if($where!=null)
            $query.= "WHERE ".$where." ";
        $query.= "ORDER BY";
        if($order!=null)
            $query.= " ".$order." ";
        else
            $query.= " ".$table.".Year DESC, ".$table.".Number DESC ";
        if($limit>0){
            if($page==1){
                $query.= "LIMIT ".$limit;
            }
            else if($page>1){
                $query.= "LIMIT ".$limit*($page-1).", ".$limit;
            }
        }

        return $query;
    }

    public function getDocsFromFlow($DocTypeId, $FlowId){
        if($DocTypeId<6 && $DocTypeId>8){
            $query = "SELECT NOTIF.FlowId, NOTIF.CC_Comune AS CC, NOTIF.Lotto AS Not_Lotto, NOTIF.Scatola AS Not_Scatola, NOTIF.Lotto AS Not_Posizione, ";
            $query.= "NOTIF.Data_Notifica AS Not_Data_Notifica, NOTIF.Data_Spedizione AS Not_Data_Spedizione, ";
            $query.= "NOTIF.Tipo_Notifica AS Not_Tipo_Notifica, NOTIF.Stato_Notifica AS Not_Stato_Notifica, ";
            $query.= "NOTIF.Ms_Rac_Num AS Not_Numero_Raccomandata, NOTIF.Log_Modificato_Data AS Not_Data_Log, NOTIF.Operatore AS Not_Operatore, ";
            $query.= "NOTIF.Data_Importazione AS Not_Data_Importazione, NOTIF.Immagine_Fronte AS Not_Front_Image, NOTIF.Immagine_Retro AS Not_Rear_Image, ";
            $query.= "NOTIF.DocumentId, atto.*, SUM(pagamento.Importo) AS Totale_Pagamenti ";
            $query.= "FROM atto ";
            $query.= "LEFT JOIN notifiche_importate AS NOTIF ON atto.ID=NOTIF.DocumentId AND atto.CC=NOTIF.CC_Comune AND atto.FlowId=NOTIF.FlowId ";
            $query.= "AND NOTIF.DocumentTypeId!=6 AND NOTIF.DocumentTypeId!=7 AND NOTIF.DocumentTypeId!=8 ";
            $query.= "LEFT JOIN pagamento ON pagamento.Partita_ID=atto.Partita_ID AND pagamento.Atto_ID=atto.ID AND pagamento.Tipo_Atto NOT LIKE \"Pignoramento%\" ";
            $query.= "WHERE Atto.FlowId=".$FlowId." GROUP BY Atto.ID";
        }
        else{
            $query = "SELECT NOTIF.FlowId, NOTIF.CC_Comune AS CC, NOTIF.Lotto AS Not_Lotto, NOTIF.Scatola AS Not_Scatola, NOTIF.Lotto AS Not_Posizione, ";
            $query.= "NOTIF.Data_Notifica AS Not_Data_Notifica, NOTIF.Data_Spedizione AS Not_Data_Spedizione, ";
            $query.= "NOTIF.Tipo_Notifica AS Not_Tipo_Notifica, NOTIF.Stato_Notifica AS Not_Stato_Notifica, ";
            $query.= "NOTIF.Ms_Rac_Num AS Not_Numero_Raccomandata, NOTIF.Log_Modificato_Data AS Not_Data_Log, NOTIF.Operatore AS Not_Operatore, ";
            $query.= "NOTIF.Data_Importazione AS Not_Data_Importazione, NOTIF.Immagine_Fronte AS Not_Front_Image, NOTIF.Immagine_Retro AS Not_Rear_Image, ";
            $query.= "NOTIF.DocumentId, PIGNO.*, SUM(pagamento.Importo) AS Totale_Pagamenti ";
            $query.= "FROM pignoramento_generale AS PIGNO ";
            $query.= "LEFT JOIN notifiche_importate AS NOTIF ON PIGNO.ID=NOTIF.DocumentId AND PIGNO.CC=NOTIF.CC_Comune AND PIGNO.FlowId=NOTIF.FlowId ";
            $query.= "AND (NOTIF.DocumentTypeId=6 || NOTIF.DocumentTypeId=7 || NOTIF.DocumentTypeId=8)";
            $query.= "LEFT JOIN pagamento ON pagamento.Partita_ID=PIGNO.Partita_ID AND pagamento.Atto_ID=PIGNO.ID AND pagamento.Tipo_Atto LIKE \"Pignoramento%\" ";
            $query.= "WHERE PIGNO.FlowId=".$FlowId." GROUP BY PIGNO.ID";
        }

        return $query;

    }

    public function getInvoicesListQuery($table, $where=null, $limit=0, $page=1, $order=null){
        $query = "SELECT SQL_CALC_FOUND_ROWS ".$table.".*, 0.00 AS PrintAmount, 0.00 AS PostageAmount ";
        $query.= "FROM ".$table." ";
        $query.= "LEFT JOIN flows AS PRINT_FLOWS ON PRINT_FLOWS.PrintInvoiceId = flow_invoices.Id ";
        $query.= "LEFT JOIN flows AS POSTAGE_FLOWS ON POSTAGE_FLOWS.PostageInvoiceId = flow_invoices.Id ";
        if($where!=null)
            $query.= "WHERE ".$where." ";
        $query.= "GROUP BY ".$table.".ID ";
        $query.= "ORDER BY";
        if($order!=null)
            $query.= " ".$order." ";
        else
            $query.= " ".$table.".Year DESC, ".$table.".Number DESC ";
        if($limit>0) {
            if ($page == 1) {
                $query .= "LIMIT " . $limit;
            } else if ($page > 1) {
                $query .= "LIMIT " . $limit * ($page - 1) . ", " . $limit;
            }
        }

//        echo $query."<br><br>";
        return $query;
    }

    public function getLastFlow_query(){
        return "SELECT max(Number) AS flowNumber FROM flows WHERE Year = ".$this->flowYear;
    }

    public function arrayStatus(){
        $a_status = null;
        $a_status[1] = "CreationDate";
        $a_status[2] = "UploadDate";
        $a_status[3] = "ProcessingDate";
        $a_status[4] = "PostagePaymentDate";
        $a_status[5] = "SendDate";
        $a_status[6] = "CancelDate";

        return $a_status;
    }

    public function filterStatus($table,$status){
        $where = "( ".$table.".".$this->array['status'][$status]." is not null ";
        $status++;
        for($i=$status;$i<=count($this->array['status']);$i++){
            $where.= "AND ".$table.".".$this->array['status'][$i]." is null ";
        }
        $where.= ")";
        return $where;
    }

    public function filterQuery($table, $filter)
    {
        $this->setArray("status", $this->arrayStatus());
        $whereFlow = "";
        if (isset($filter['Number'])) {
            if ($filter['Number'] != null)
                $whereFlow .= $table . ".Number=" . $filter['Number'];
        }
        if (isset($filter['Year'])) {
            if ($filter['Year'] != null) {
                if ($whereFlow != "")
                    $whereFlow .= " AND ";
                $whereFlow .= $table . ".Year=" . $filter['Year'];
            }
        }
        if (isset($filter['Date'])){
            if ($filter['Date'] != null) {
                if ($whereFlow != "")
                    $whereFlow .= " AND ";
                $whereFlow .= $table . ".Date=\"" . $filter['Date'] . "\"";
            }
        }

        if (isset($filter['CityId'])) {
            if ($filter['CityId'] != null) {
                if ($whereFlow != "")
                    $whereFlow .= " AND ";
                $whereFlow .= $table . ".CityId=\"" . $filter['CityId'] . "\"";
            }
        }

        if (isset($filter['PrinterId'])) {
            if ($filter['PrinterId'] != null) {
                if ($whereFlow != "")
                    $whereFlow .= " AND ";
                $whereFlow .= $table . ".PrinterId=\"" . $filter['PrinterId'] . "\"";
            }
        }

        if (isset($filter['Status'])) {
            if ($filter['Status'] != null) {
                if ($whereFlow != "")
                    $whereFlow .= " AND ";
                $whereFlow .= $this->filterStatus($table, $filter['Status']);
            }
        }
        if (isset($filter['MissStatus'])) {
            if ($filter['MissStatus'] != null) {
                if ($whereFlow != "")
                    $whereFlow .= " AND ";
                $whereFlow .= $table . "." . $this->array['status'][$filter['MissStatus']] . " is null";
            }
        }
        if (isset($filter['StatusOfDate'])) {
            if ($filter['StatusOfDate'] != null && $filter['StatusDate'] != null) {
                if ($whereFlow != "")
                    $whereFlow .= " AND ";
                $whereFlow .= $table . "." . $this->array['status'][$filter['StatusOfDate']] . "=\"" . $filter['StatusDate'] . "\"";
            }
        }

        return $whereFlow;
    }

    public function filterOrder($table, $orderName, $order){
        $returnOrder = null;
        if($order==0)
            $order = "ASC";
        else
            $order = "DESC";

        switch($orderName){
            case "CityId":
                $returnOrder.= $table.".CityId ".$order.", ".$table.".Year DESC, ".$table.".Number DESC";
                break;
            case "Number":
                $returnOrder.= $table.".Year DESC, ".$table.".Number ".$order;
                break;
            case "Year":
                $returnOrder.= $table.".Year ".$order.", ".$table.".Number DESC";
                break;
            case "Date":
                $returnOrder.= $table.".DATE ".$order.", ".$table.".Year DESC, ".$table.".Number DESC";
                break;
        }
        return $returnOrder;
    }

    public function setFlowType(array $a_docDetails, $date=null){
        $this->flowType = $a_docDetails['type'];
        $this->a_docDetails = $a_docDetails;
        $this->tableName = "atto";
        $this->docType = $this->a_docDetails['type'];
        switch($a_docDetails['PrintTypeId']){
            case 1:
                $this->printType = "Raccomandata AG";
                break;
            case 2:
                $this->printType = "Raccomandata AR";
                break;
            case 3:
                $this->printType = "Posta ordinaria";
                break;

        }

        $lastFlow = $this->cls_db->getArrayLine($this->cls_db->SelectQuery($this->getLastFlow_query()));
        $this->flowNumber = $lastFlow['flowNumber']+1;

        if($this->flowDir!="")
            $this->setFlowName($date);
    }

    public function setFlowName($date=null){
        if($date==null)
            $this->flowDate = date("Y-m-d");
        else
            $this->flowDate = $date;

        $this->flowName = "flusso_".$this->a_docDetails['finalFileName']."_".$this->cc;
        $this->flowName.= "_".$this->flowYear."_".$this->flowNumber;

        $this->flowArchiveName = $this->flowName."_".$this->flowRowsNumber.".".$this->flowArchiveExt;

        $this->flowName.= "_".$this->flowDate."_".$this->flowTime.".".$this->flowExt;

        $this->flowFile = $this->flowDir."/".$this->flowName;
        $this->flowArchiveFile = $this->flowDir."/".$this->flowArchiveName;
    }

    public function setHeader($flag = "old"){

        if($flag == "old")
        {
            $this->a_header = array_merge(
                $this->setGeneralHeader(),
                $this->setDoc(),
                $this->setMod23(),
                $this->setPostalBill(),
                $this->setNotification(),
                $this->setManager(),
                $this->setRecipient(),
                $this->setAmounts(),
                $this->setTextHeader()
            );
        }
        else if($flag == "new")
            $this->a_header = $this->SetNewHeader();
        
        $this->file = fopen ($this->flowFile, "w+");
        $this->addHeaderFromArray();
    }

    public function SetNewHeader($a_line = null)
    {
        if(is_null($a_line)){
            $this->a_newHeader = array(
                "DocumentId",
                "TableId",
                "CC",
                "FlowId",
                "FlowName",
                "DocumentTypeId",
                "DocumentType",
                "Partita_ID",
                "Utente_ID",
                "Id_Cronologico",
                "Anno_Cronologico",

                "PrinterId",
                "PrintTypeId",
                "PrintType",
                "NotificationId",
                "CF_PI",
                "Destinatario",
                "AddressName",
                "AddressCap",
                "AddressCity",
                "AddressProvince",
                "AddressCountry",
                "FileName",
                "Ente",
                "SMA_Intestatario",
                "SMA_Numero",
                "SMA_TestoSpese",
                "SMA_Restituzione1",
                "SMA_Restituzione2",
                "SMA_Restituzione3",
                "SMA_Restituzione4",
                "SMA_Restituzione5"
            );

            return $this->a_newHeader;
        }
        else if(is_array($a_line)){
            $this->DocumentTypeId = $a_line['DocumentTypeId'];
            $this->a_newRow = array();
            foreach($this->a_header as $key=>$value){
                if($value=="NotificationId" || $value=="FlowId" || $value=="FlowName")
                    $this->a_newRow[$key] = isset($a_line[$value])?$a_line[$value]:null;
                else
                    $this->a_newRow[$key] = $a_line[$value];
            }
            return $this->a_newRow;
        }
    }

    
    public function setHeaderByArray(array $a_header){
        $this->a_header = $a_header;

        $this->file = fopen ($this->flowFile, "w+");
        $this->addHeaderFromArray();
    }

    public function setRecapHeader(){

        $this->header = array(

            "NOME_FLUSSO",
            "TIPOLOGIA_STAMPA",
            "TIPOLOGIA_ATTO",

            "COLORE_STAMPA",

            "CODICE_ENTE",
            "DENOMINAZIONE_ENTE",

            "SPESE_ANTICIPATE",

            "INTESTATARIO_SMA",
            "NUMERO_SMA",

            "MOD23_SOGGETTO_MITTENTE",
            "MOD23_ENTE_GESTITO",
            "MOD23_RECAPITO_SOGGETTO",
            "MOD23_INDIRIZZO_SOGGETTO",
            "MOD23_CITTA_SOGGETTO",

            "ID",
            "TABLE_NAME",

            "DESTINATARIO",
            "DESTINATARIO_INDIRIZZO",
            "DESTINATARIO_CAP",
            "DESTINATARIO_COMUNE",
            "DESTINATARIO_PROVINCIA",
            "DESTINATARIO_STATO"

        );
    }

    public function setFlowRow($flag = "old"){
        if($flag == "old")
        {
            $this->a_row = array_merge(
                $this->a_generalRow,
                $this->a_docRow,
                $this->a_mod23Row,
                $this->a_postalRow,
                $this->a_notificationRow,
                $this->a_managerRow,
                $this->a_recipientRow,
                $this->a_amountsRow,
                $this->a_textRow
            );
        }else
            $this->a_row = $this->a_newRow;
            $this->a_rows[] = $this->a_row;

//        $this->addRowFromArray();
    }

    function addHeaderFromArray(){

        for ($i = 0; $i < count($this->a_header); $i++){
            fwrite ($this->file, $this->a_header[$i] . Chr(9));  //  TAB
        }
        fwrite ($this->file, Chr(13) . Chr(10));  //  fine riga
    }

    function addRowsFromArray(){
        foreach ($this->a_rows as $key=>$a_row){
            $this->flowRow++;
            if(count($a_row)==count($this->a_header)){
                for ($i = 0; $i < count($a_row); $i++){
                    if($this->a_header[$i]=="FlowId")
                        $a_row[$i] = $this->IDFlusso;
                    if($this->a_header[$i]=="FlowName")
                        $a_row[$i] = $this->flowArchiveName;
                    fwrite ($this->file, $a_row[$i] . Chr(9));  //  TAB
                }
                fwrite ($this->file, Chr(13) . Chr(10));  //  fine riga

            }
            else{
                echo "ATTENZIONE: Il numero di campi dell'intestazione del flusso non coincide con il numero di campi della riga ".$this->flowRow;
                print_r($this->a_header);
                print_r($a_row);
                die;
            }
        }


    }

    function addRowFromArray(){
        $this->flowRow++;
        if(count($this->a_row)==count($this->a_header)){
            for ($i = 0; $i < count($this->a_row); $i++){
                fwrite ($this->file, $this->a_row[$i] . Chr(9));  //  TAB
            }
            fwrite ($this->file, Chr(13) . Chr(10));  //  fine riga

        }
        else{
            echo "ATTENZIONE: Il numero di campi dell'intestazione del flusso non coincide con il numero di campi della riga ".$this->flowRow;
            print_r($this->a_header);
            print_r($this->a_row);
            die;
        }

    }

    /******************************************** qui *************************************************************/

    function closeFile($tipo = "atto"){
        $this->addFlowToFlowsTable(null,$tipo);
        $this->addRowsFromArray();
        fclose($this->file);
    }

    function addFlowToFlowsTable($a_table = null, $tipo = "atto"){

        $query = "SELECT * FROM printer_charge WHERE PrinterId = ".$this->a_docDetails["PrinterId"]." AND MailTypeId = ".$this->a_docDetails["PrintTypeId"];
        $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"printer_charge");

        $a_flowsData = array(
            "CityId"=>$this->cc,
            "Year"=>$this->flowYear,
            "Number"=>$this->flowNumber,
            "OriginalRecordsNumber"=>$this->flowRowsNumber,
            "RecordsNumber"=>$this->flowRowsNumber,
            "Zone0Number"=>$this->flowRowsNumber,
            "Zone1Number"=>0,
            "Zone2Number"=>0,
            "Zone3Number"=>0,
            "Zone0Postage"=>$result["Zone0Postage"],
            "Zone1Postage"=>$result["Zone1Postage"],
            "Zone2Postage"=>$result["Zone2Postage"],
            "Zone3Postage"=>$result["Zone3Postage"],
            "PrintCost" => $result["PrintCost"],
            "CreationDate"=>$this->flowDate,
            "FileName"=>$this->flowArchiveName,
            "Operatore"=>$_SESSION['username'],
            "PrinterId"=> (int) $this->a_docDetails["PrinterId"],
            "PrintTypeId"=> (int) $this->a_docDetails["PrintTypeId"]
        );

        if($a_table!=null){
            if(isset($a_table['PrinterId']))
                if($a_table['PrinterId']==1){
                    $a_flowsData["FileName"] = "";
                    $a_flowsData["UploadDate"] = $this->flowDate;
                    $a_flowsData["ProcessingDate"] = $this->flowDate;
                }


            $a_flowsTable = array_merge($a_flowsData,$a_table);
        }
        else
            $a_flowsTable = $a_flowsData;

        $a_flowsTable['DocumentTypeId'] = $this->DocumentTypeId;

        $cls_utils = new cls_Utils();

        $query = "SELECT Id FROM flows WHERE DocumentTypeId = " . $a_flowsTable['DocumentTypeId'] . " ";
        $query .= "AND CityId = '" . $a_flowsTable['CityId'] . "' AND Year = " . $a_flowsTable['Year'] . " ";
        $query .= "AND Number = " . $a_flowsTable['Number'] . " ";

        $a_tableFlow = $this->cls_db->getArrayLine($this->cls_db->SelectQuery($query));

        if(!empty($a_tableFlow['Id'])){

            $this->cls_db->DbSave($cls_utils->GetObjectQuery($a_flowsTable,"flows", array("Id"=>$a_tableFlow['Id'])));
            $return = null;

            $this->IDFlusso = $a_tableFlow['Id'];
        }
        else{

            $newID = $this->cls_db->DbSave($cls_utils->GetObjectQuery($a_flowsTable,"flows"));
            $return = 1;

            $this->IDFlusso = $newID;

            switch($tipo)
            {
                case "atto": $Table = "atto"; break;
                case "pigno": $Table = "notifica_atto"; break;
                default: $Table = null; break;
            }

            $queryAtto = "UPDATE ".$Table." SET FlowId = ".$newID." WHERE Data_Flusso = '".$this->flowDate."' AND Anno_Flusso = ".$this->flowYear." AND Numero_Flusso = ".$this->flowNumber;
            $this->cls_db->ExecuteQuery($queryAtto);
        }


        if($return!=null)
            return $newID;
        else
            return null;
    }

    function addFlowToFlussiTabella(){

        $a_flussiTabella = array(
            "Tipo"=>$this->flowType,
            "CC_Comune"=>$this->cc,
            "Anno"=>$this->flowYear,
            "Num_Flusso"=>$this->flowNumber,
            "Num_Righe"=>$this->flowRowsNumber,
            "Data_Flusso"=>$this->flowDate,
            "Nome_Flusso"=>$this->flowName,
            "Nome_Flusso_Rar"=>$this->flowArchiveName,
            "Data_Travaso_Verso_Gitco"=>null
        );

        $query = "SELECT ID FROM flussi_tabella WHERE Tipo = '" . $a_flussiTabella['Tipo'] . "' ";
        $query .= "AND CC_Comune = '" . $a_flussiTabella['CC_Comune'] . "' AND Anno = '" . $a_flussiTabella['Anno'] . "' ";
        $query .= "AND Num_Flusso = '" . $a_flussiTabella['Num_Flusso'] . "' ";

        $a_tableFlow = $this->cls_db->getArrayLine($this->cls_db->SelectQuery($query));
        if($a_tableFlow['ID']>0){
            $query = "UPDATE flussi_tabella SET ";
            foreach($a_flussiTabella as $field=>$value){
                if($field!="Anno" && $field!="Num_Flusso" && $field!="Num_Righe")
                    $value = "\"".$value."\"";

                $query.= $field." = ".$value;
                if($query!="UPDATE flussi_tabella SET " && $field!="Data_Travaso_Verso_Gitco")
                    $query.= ", ";
            }
            $query.= " WHERE ID=".$a_tableFlow['ID'];
        }
        else{
            $query = "INSERT INTO flussi_tabella ";
            $fields = "(";
            $values = "VALUES (";
            foreach($a_flussiTabella as $field=>$value){
                if($field!="Anno" && $field!="Num_Flusso" && $field!="Num_Righe")
                    $value = "\"".$value."\"";

                $fields.= $field;
                $values.= $value;
                if($field!="Data_Travaso_Verso_Gitco"){
                    $fields.= ", ";
                    $values.= ", ";
                }
                else{
                    $fields.= ") ";
                    $values.= ") ";
                }
            }
            $query.= $fields.$values;
        }
        $this->cls_db->ExecuteQuery($query);
    }

    public function setTextHeader(){
        $this->a_textHeader = array();
        for($i=1;$i<=count($this->a_textParameters);$i++){
            $value = "page".$this->a_textParameters[$i]['page']."_field".$i;
            if(substr($this->a_textParameters[$i]['field'],0,5)=="firma"){
                $this->a_textHeader[] = $value."_signatureHeader";
                $this->a_textHeader[] = $value."_signatureName";
                $this->a_textHeader[] = $value."_signatureFile";
                $this->a_textHeader[] = $value."_signatureText";
            }
            else
                $this->a_textHeader[] = $value;
        }

        return $this->a_textHeader;
    }

    public function setTextRow(array $a_text, array $a_signature){
        $this->a_textRow = array();
        for($i=1;$i<=count($this->a_textParameters);$i++){
            $text = $a_text['field'.$i];
            $condition = substr($this->a_textParameters[$i]['field'],0,5);

            if($condition=="firma"){
                $signature = $this->cls_parameters->getSelectedSignature($text,$a_signature);

                $this->a_textRow[] = $signature['header'];
                $this->a_textRow[] = $signature['name'];
                if($signature['type']=="file"){
                    if($this->flowRow==0)
                        $this->a_flowAttachment[] = $signature['filePath'];

                    $this->a_textRow[] = $signature['file'];
                    $this->a_textRow[] = "";
                }
                else{
                    $this->a_textRow[] = "";
                    $this->a_textRow[] = $signature['text'];
                }
            }
            else
                $this->a_textRow[] = $text;
        }
    }

    public function setDoc($a_doc=null){
        if($a_doc==null){
            $this->a_DocHeader = array(
                "ID_Cronologico",
                "Anno_Cronologico",
                "docID"
            );
            return $this->a_DocHeader;
        }
        else if(is_array($a_doc)){
            $this->a_docRow = array();
            $this->a_docRow[0] = $a_doc['ID_Cronologico'];
            $this->a_docRow[1] = $a_doc['Anno_Cronologico'];
            $this->a_docRow[2] = $a_doc['ID'];

            return $this->a_docRow;
        }
    }

    public function setGeneralHeader(){
        $this->a_generalHeader = array(
            "NOME_FLUSSO",
            "CODICE_CATASTALE",
            "TIPOLOGIA_STAMPA",
            "TIPOLOGIA_ATTO"
        );

        return $this->a_generalHeader;
    }

    public function setGeneralRow(){
        $this->a_generalRow = array();
        $this->a_generalRow[0] = $this->flowArchiveName;
        $this->a_generalRow[1] = $this->cc;
        $this->a_generalRow[2] = $this->printType;
        $this->a_generalRow[3] = $this->docType;

        return $this->a_generalRow;
    }

    public function setManager($a_manager=null){
        if($a_manager==null){
            $this->a_managerHeader = array(
                "logo",
                "leftHeader1",
                "leftHeader2",
                "leftHeader3",
                "leftHeader4",
                "leftHeader5",
                "leftHeader6",
                "leftHeader7",
                "rightHeader1",
                "rightHeader2",
                "rightHeader3",
                "rightHeader4",
                "rightHeader5",
                "rightHeader6",
                "rightHeader7"
            );

            return $this->a_managerHeader;
        }
        else if(is_array($a_manager)){
            $this->a_managerRow = array();

            $this->a_flowAttachment[] = $a_manager['logoPath'];
            $expLogo = explode("/",$a_manager['logo']);
            $this->a_managerRow[0] = $expLogo[count($expLogo)-1];

            for($i=0;$i<7;$i++){
                if(isset($a_manager['left'][$i]))
                    $value = $a_manager['left'][$i];
                else
                    $value = "";
                $this->a_managerRow[$i+1] = $value;
            }

            for($i=0;$i<7;$i++){
                if(isset($a_manager['right'][$i]))
                    $value = $a_manager['right'][$i];
                else
                    $value = "";
                $this->a_managerRow[$i+8] = $value;
            }

            return $this->a_managerRow;
        }
    }


    public function setMod23($a_mod23 = null, $printTypeId=null){
        if($a_mod23==null){
            $this->a_mod23Header = array(
                "Spese_Anticipate",
                "Intestatario_SMA",
                "Numero_SMA",
                "Mod23_Tipo",
                "Mod23_Soggetto_Mittente",
                "Mod23_Ente_Gestito",
                "Mod23_Recapito_Soggetto",
                "Mod23_Indirizzo_Soggetto",
                "Mod23_Citta_Soggetto"
            );

            return $this->a_mod23Header;
        }
        else if(is_array($a_mod23)){
            $this->a_mod23Row = array(
                $a_mod23['Testo_Spese_Anticipate'],
                $a_mod23['Intestatario_SMA'],
                $a_mod23['Numero_SMA']
            );

            if($printTypeId==2){
                $this->a_mod23Row[] = "23O";
                $this->a_mod23Row[] = $a_mod23['Restituzione1_Mod23O'];
                $this->a_mod23Row[] = $a_mod23['Restituzione2_Mod23O'];
                $this->a_mod23Row[] = $a_mod23['Restituzione3_Mod23O'];
                $this->a_mod23Row[] = $a_mod23['Restituzione4_Mod23O'];
                $this->a_mod23Row[] = $a_mod23['Restituzione5_Mod23O'];
            }
            else if($printTypeId==1){
                $this->a_mod23Row[] = "23L";
                $this->a_mod23Row[] = $a_mod23['Restituzione1'];
                $this->a_mod23Row[] = $a_mod23['Restituzione2'];
                $this->a_mod23Row[] = $a_mod23['Restituzione3'];
                $this->a_mod23Row[] = $a_mod23['Restituzione4'];
                $this->a_mod23Row[] = $a_mod23['Restituzione5'];
            }
            else{
                $this->a_mod23Row[] = "";
                $this->a_mod23Row[] = "";
                $this->a_mod23Row[] = "";
                $this->a_mod23Row[] = "";
                $this->a_mod23Row[] = "";
                $this->a_mod23Row[] = "";
            }

            return $this->a_mod23Row;
        }
    }

    public function setPostalBill($a_postal = null){
        if($a_postal==null){
            $this->a_postalHeader = array(
                "logo_Postal1",
                "td_Postal1",
                "authorization_Postal1",
                "accountNumber_Postal1",
                "accountHolder_Postal1",
                "iban_Postal1",
                "amount_Postal1",
                "literalAmount_Postal1",
                "recipientRow1_Postal1",
                "recipientRow2_Postal1",
                "recipientRow3_Postal1",
                "causalRow1_Postal1",
                "causalRow2_Postal1",
                "clientCode_Postal1",
                "barCode_clientCode_Postal1",
                "barCode_amount_Postal1",
                "barCode_accountNumber_Postal1",
                "barCode_td_Postal1",

                "logo_Postal2",
                "td_Postal2",
                "authorization_Postal2",
                "accountNumber_Postal2",
                "accountHolder_Postal2",
                "iban_Postal2",
                "amount_Postal2",
                "literalAmount_Postal2",
                "recipientRow1_Postal2",
                "recipientRow2_Postal2",
                "recipientRow3_Postal2",
                "causalRow1_Postal2",
                "causalRow2_Postal2",
                "clientCode_Postal2",
                "barCode_clientCode_Postal2",
                "barCode_amount_Postal2",
                "barCode_accountNumber_Postal2",
                "barCode_td_Postal2"
            );

            return $this->a_postalHeader;
        }
        else if(is_array($a_postal)){
            $postalArray = array();
            for($i=1;$i<=2;$i++){
                if($a_postal[$i]['authorization']!=false || $a_postal[$i]['td']=="123"){
                    if($a_postal[$i]['td']=="896"){
                        $amount = $a_postal[$i]['amount'];
                        $literalAmount = "";
                    }
                    else if($a_postal[$i]['checkAmount']=="si"){
                        $amount = $a_postal[$i]['amount'];
                        $literalAmount = $a_postal[$i]['literalAmount'];
                    }
                    else{
                        $amount = "";
                        $literalAmount = "";
                    }

                    if($this->flowRow==0)
                        $this->a_flowAttachment[] = $a_postal[$i]['logo']['rootPath'];
                    $exp_logo = explode("/",$a_postal[$i]['logo']['rootPath']);
                    $postalArray[$i] = array(
                        $exp_logo[count($exp_logo)-1],
                        $a_postal[$i]['td'],
                        $a_postal[$i]['authorization'],
                        $a_postal[$i]['accountNumber'],
                        $a_postal[$i]['accountHolder'],
                        $a_postal[$i]['iban'],
                        $amount,
                        $literalAmount,
                        $a_postal[$i]['recipientRow1'],
                        $a_postal[$i]['recipientRow2'],
                        $a_postal[$i]['recipientRow3'],
                        $a_postal[$i]['causalRow1'],
                        $a_postal[$i]['causalRow2'],
                        $a_postal[$i]['clientCode'],
                        $a_postal[$i]['barCode_clientCode'],
                        $a_postal[$i]['barCode_amount'],
                        $a_postal[$i]['barCode_accountNumber'],
                        $a_postal[$i]['barCode_td']
                    );
                }
                else{
                    for($y=0;$y<18;$y++)
                        $postalArray[$i][] = "";
                }
            }

            $this->a_postalRow = array_merge($postalArray[1],$postalArray[2]);

            return $this->a_postalRow;
        }
    }

    public function setRecipient($a_recipient = null){
        if($a_recipient==null){
            $this->a_recipientHeader = array(
                "refHeader1",
                "refHeader2",
                "refHeader3",
                "refHeader4",
                "refHeader5",
                "refHeader6",

                "placeDate",
                "recipientHeader1",
                "recipientHeader2",
                "recipientHeader3",
                "recipientHeader4",
                "recipientHeader5"
            );

            return $this->a_recipientHeader;

        }
        else if(is_array($a_recipient)){
            $this->a_recipientRow = array(
                $a_recipient["references"][0],
                $a_recipient["references"][1],
                $a_recipient["references"][2],
                $a_recipient["references"][3],
                "",
                "",
                $a_recipient["placeDate"]
            );

            for($i=0;$i<count($a_recipient['denomination']);$i++)
                $this->a_recipientRow[] = $a_recipient['denomination'][$i];

            for($i=0;$i<count($a_recipient['addressRow']);$i++)
                $this->a_recipientRow[] = $a_recipient['addressRow'][$i];

            for($i=count($this->a_recipientRow);$i<count($this->a_recipientHeader);$i++)
                $this->a_recipientRow[] = "";

            return $this->a_recipientRow;
        }
    }

    public function setNotification($a_text = null, $a_signature = null, $collectorType=null){
        if($a_signature==null && $a_text==null){
            $this->a_notificationHeader = array(
                "notificationHeader",
                "notificationSubheader",
                "notificationText",

                "notification_signatureHeader",
                "notification_signatureName",
                "notification_signatureFile",
                "notification_signatureText"
            );

            return $this->a_notificationHeader;

        }
        else if(is_array($a_signature) && is_array($a_text)){
            $this->a_notificationRow = array();
            switch($collectorType){
                case "diretta":
                    $notificationType = "direct";
                    break;
                case "riscossione":
                    $notificationType = "collector";
                    break;
                case "giudiziario":
                    $notificationType = "bailiff";
                    break;
                default:
                    $notificationType = "";
                    break;
            }

            if($a_text[$notificationType.'_header']!=""){
                $this->a_notificationRow[] = strtoupper($a_text[$notificationType.'_header']);
                $this->a_notificationRow[] = $a_text[$notificationType.'_subheader'];
                $this->a_notificationRow[] = $a_text[$notificationType.'_text'];
            }
            else{
                $this->a_notificationRow[] = "";
                $this->a_notificationRow[] = "";
                $this->a_notificationRow[] = "";
            }

            $signature = $this->cls_parameters->getSignatureByOfficial($collectorType,$a_signature);

            $this->a_notificationRow[] = $signature['header'];
            $this->a_notificationRow[] = $signature['name'];
            if($signature['type']=="file"){
                if($this->flowRow==0)
                    $this->a_flowAttachment[] = $signature['filePath'];

                $this->a_notificationRow[] = $signature['file'];
                $this->a_notificationRow[] = "";
            }
            else{
                $this->a_notificationRow[] = "";
                $this->a_notificationRow[] = $signature['text'];
            }

            return $this->a_notificationRow;
        }
    }

    public function setAmounts($a_amounts=null){
        if($a_amounts==null){
            $this->a_amountsHeader = array();
            for($i=1;$i<=10;$i++){
                $this->a_amountsHeader[] = "textAmount".$i;
                $this->a_amountsHeader[] = "operatorAmount".$i;
                $this->a_amountsHeader[] = "amount".$i;
            }

            for($i=1;$i<=3;$i++){
                $this->a_amountsHeader[] = "textTotalAmount".$i;
                $this->a_amountsHeader[] = "operatorTotalAmount".$i;
                $this->a_amountsHeader[] = "totalAmount".$i;
            }

            return $this->a_amountsHeader;
        }
        else if(is_array($a_amounts)){
            $this->a_amountsRow = array();
            for($i=0;$i<count($a_amounts['single']);$i++){
                $this->a_amountsRow[] = $a_amounts['single'][$i]['label'];
                $this->a_amountsRow[] = $a_amounts['single'][$i]['operator'];
                $this->a_amountsRow[] = $a_amounts['single'][$i]['amount'];
            }
            for($i=count($a_amounts['single']);$i<10;$i++){
                $this->a_amountsRow[] = "";
                $this->a_amountsRow[] = "";
                $this->a_amountsRow[] = "";
            }

            for($i=0;$i<count($a_amounts['total']);$i++){
                $this->a_amountsRow[] = $a_amounts['total'][$i]['label'];
                $this->a_amountsRow[] = $a_amounts['total'][$i]['operator'];
                $this->a_amountsRow[] = $a_amounts['total'][$i]['amount'];
            }
            for($i=count($a_amounts['total']);$i<3;$i++){
                $this->a_amountsRow[] = "";
                $this->a_amountsRow[] = "";
                $this->a_amountsRow[] = "";
            }

            return $this->a_amountsRow;
        }
    }

    public function cleanAttachments(){
        $a_attachment = array();
        for($i=0;$i<count($this->a_flowAttachment);$i++){
            $check = 0;
            for($y=0;$y<count($a_attachment);$y++){
                if($a_attachment[$y]==$this->a_flowAttachment[$i]){
                    $check=1;
                    break;
                }
            }

            if($check==0)
                $a_attachment[] = $this->a_flowAttachment[$i];

        }

        $this->a_flowAttachment = $a_attachment;
    }

    public function getFlowName ($a_params){
        //$a_params['flowYear']
        //$a_params['flowNumber']
        //$a_params['flowCC']
        //$a_params['docType']
        //
        $dir = "";
        $checkDoc = 0;
        switch ($a_params['docType']) {
            case "Preavviso di pagamento";
                $dir = "/Preavvisi";
                break;
            case "Ingiunzione";
                $dir = "/Ingiunzioni";
                break;
            case "Avviso di intimazione ad adempiere";
                $dir = "/Avvisi_di_intimazione";
                break;
            case "Sollecito di pagamento";
                $dir = "/Solleciti";
                break;
            case "Sollecito pre ingiunzione";
                $dir = "/Solleciti_pre_ingiunzione";
                break;
            case "Avviso di messa in mora";
                $dir = "/Avvisi_Messa_In_Mora";
                break;
            default:
                $expDoc = explode(" ",$a_params['docType']);
                if($expDoc[0]=="Pignoramento"){
                    $checkDoc = 1;
                    $dir = "/Pignoramenti";
                    switch ($expDoc[1]) {
                        case "veicolo":
                            $dir .= "/Veicolo";
                            break;
                        case "terzi":
                            $dir .= "/Presso_Terzi";
                            switch ($expDoc[2]) {
                                case "banca":
                                    $dir .= "/Banca";
                                    break;
                                case "lavoro":
                                    $dir .= "/Datore_di_Lavoro";
                                    break;
                                case "inps":
                                    $dir .= "/Inps";
                                    break;
                            }
                            break;
                    }
                }

                break;
        }

        $flowName = "";
        $a_check = array();
        $cls_file = new cls_file();
        $flowDir = $cls_file->folderCreation(SUPER_ROOT . "/archivio/Atti/" . $a_params['flowCC'] . $dir . "/FLUSSI");
        $handle = opendir($flowDir);
        while (($link = readdir($handle)) !== false) {
            if ($link != "." && $link != ".." && $link != "thumbs.db" && $link != "ELIMINATI") {
                $explodePunto = explode(".", $link);
                $a_check['extension'] = $explodePunto[1];

                $explode = explode("_", $explodePunto[0]);
                if($checkDoc==1){
                    $a_check['flowCC'] = $explode[3];
                    $a_check['flowYear'] = $explode[4];
                    $a_check['flowNumber'] = $explode[5];
                }
                else{
                    $a_check['flowCC'] = $explode[2];
                    $a_check['flowYear'] = $explode[3];
                    $a_check['flowNumber'] = $explode[4];
                }

                if (strtoupper($a_check['extension']) == "RAR" &&
                    $a_params['flowCC'] == $a_check['flowCC'] &&
                    $a_params['flowYear'] == $a_check['flowYear'] &&
                    $a_params['flowNumber'] == $a_check['flowNumber'])
                    $flowName = $link;
            }
        }

        closedir($handle);

//        if($flowName==""){
//            echo $flowDir."<br><br>";
//            print_r($a_params);
//            print_r($a_check);
//        }

        return $flowName;
    }

}

