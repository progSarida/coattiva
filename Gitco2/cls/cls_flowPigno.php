<?php

include_once CLS."/cls_file.php";
include_once CLS."/cls_parameters.php";
include_once CLS."/cls_Utils.php";
include_once CLS."/cls_flow.php";

class cls_flowPigno extends cls_flow{

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
    function addFlowToFlowsTable($a_table = null, $tipo = "pigno"){

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

           // $queryAtto = "UPDATE elaboration_lists SET FlowId = ".$newID." WHERE Data_Flusso = '".$this->flowDate."' AND Anno_Flusso = ".$this->flowYear." AND Numero_Flusso = ".$this->flowNumber;
            //$this->cls_db->ExecuteQuery($queryAtto);
        }


        if($return!=null)
            return $newID;
        else
            return null;
    }
  
    function closeFile($tipo = "p"){
        $this->addFlowToFlowsTable(null,$tipo);
        $this->addRowsFromArray();
        fclose($this->file);
    }

    public function setHeader($flag = "old"){

        $this->a_header = $this->SetNewHeader();
        
        $this->file = fopen ($this->flowFile, "w+");
        $this->addHeaderFromArray();
    }
   

}

