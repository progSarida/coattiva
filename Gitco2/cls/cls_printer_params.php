<?php

class cls_printer_params{
    public function getPrinterChargeQuery($printerId, $mailTypeId, $dbDate=null){

        $query = "SELECT * FROM printer_charge ";
        $query.= "WHERE PrinterId = ".$printerId." AND MailTypeId = ".$mailTypeId;
        if($dbDate==null)
            $query.= " AND ToDate is null";
        else
            $query.= " AND FromDate>=\"".$dbDate."\" AND ToDate<=\"".$dbDate."\"";

        return $query;
    }

    public function getPrintTypes($Id){
        $query = "SELECT * FROM print_type WHERE Id=".$Id;
        return $query;
    }



}

?>