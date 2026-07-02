<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include_once (CLS."/cls_file.php");

include_once CLS . "/cls_storico.php";													// inclusione classe

$storico = new storico('storicoControlliGestione','6');

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

/**
 *  GV - 23/05/2022 START
 */
    $filterInvoiceNumber =	$cls_help->getVar("filterInvoiceNumber");	
    $filterInvoiceYear	 =	$cls_help->getVar("filterInvoiceYear");
    $filterInvoiceDate	=	$cls_help->getVar("filterInvoiceDate");
    $filterFlowNumber	=	$cls_help->getVar("filterFlowNumber");
    $filterFlowYear		=	$cls_help->getVar("filterFlowYear");
    $filterFlowStatus   =	$cls_help->getVar("filterFlowStatus");
    $filterFlowMissStatus   =	$cls_help->getVar("filterFlowMissStatus");
    $filterFlowStatusOfDate	= $cls_help->getVar("filterFlowStatusOfDate");
    $filterFlowStatusDate	= $cls_help->getVar("NewInvoiceSave");
   
    $filterFlowCityId = $cls_help->getVar("filterFlowCityId");	
    
    $filteFlowrPrinter =	$cls_help->getVar("filterFlowPrinter");

    $activeTab  = $cls_help->getVar("tab_attiva");	
  
  /**
  * GV - 23/05/2022 END 
  */

$a_newInvoice['Save'] = $cls_help->getVar("NewInvoiceSave")==null?array():$cls_help->getVar("NewInvoiceSave");
$a_newInvoice['Number'] = $cls_help->getVar("NewInvoiceNumber")==null?array():$cls_help->getVar("NewInvoiceNumber");
$a_newInvoice['Year'] = $cls_help->getVar("NewInvoiceYear")==null?array():$cls_help->getVar("NewInvoiceYear");
$a_newInvoice['Date'] = $cls_help->getVar("NewInvoiceDate")==null?array():$cls_help->getVar("NewInvoiceDate");

$a_invoice['Save'] = $cls_help->getVar("InvoiceSave");
$a_invoice['Number'] = $cls_help->getVar("InvoiceNumber");
$a_invoice['Year'] = $cls_help->getVar("InvoiceYear");
$a_invoice['Date'] = $cls_help->getVar("InvoiceDate");

$a_flow['Save'] = $cls_help->getVar("FlowSave");
$a_flow['PrintInvoiceId'] = $cls_help->getVar("FlowPrintInvoiceId");
$a_flow['PostageInvoiceId'] = $cls_help->getVar("FlowPostageInvoiceId");
$a_flow['SendDate'] = $cls_help->getVar("FlowSendDate");
$a_flow['UploadDate'] = $cls_help->getVar("FlowUploadDate");
$a_flow['CancelDate'] = $cls_help->getVar("FlowCancelDate");
$a_flow['CancelReason'] = $cls_help->getVar("FlowReason");
$a_flow['PostagePaymentBankId'] = $cls_help->getVar("FlowPostagePaymentBankId");
$a_flow['PostagePaymentDate'] = $cls_help->getVar("FlowPostagePaymentDate");
$a_flow['ProcessingDate'] = $cls_help->getVar("FlowProcessingDate");
$a_flow['ShippingOffice'] = $cls_help->getVar("FlowShippingOffice");
$a_flow['Zone0Number'] = $cls_help->getVar("FlowZone0Number");
$a_flow['Zone0Postage'] = $cls_help->getVar("FlowZone0Postage");
$a_flow['Zone1Number'] = $cls_help->getVar("FlowZone1Number");
$a_flow['Zone1Postage'] = $cls_help->getVar("FlowZone1Postage");
$a_flow['Zone2Number'] = $cls_help->getVar("FlowZone2Number");
$a_flow['Zone2Postage'] = $cls_help->getVar("FlowZone2Postage");
$a_flow['Zone3Number'] = $cls_help->getVar("FlowZone3Number");
$a_flow['Zone3Postage'] = $cls_help->getVar("FlowZone3Postage");
$a_flow['PrintCost'] = $cls_help->getVar("FlowPrintCost");
$a_flow['RecordsNumber'] = $cls_help->getVar("FlowRecordsNumber");

if(count($a_newInvoice['Save'])>0){
    $a_bind = array(
        "Number"=>"i",
        "Year"=>"i",
        "Date"=>"s"
    );
    foreach($a_newInvoice['Save'] as $id=>$val){
        if($val=="y"){
            $a_insert = array(
                "Number"=>$a_newInvoice['Number'][$id],
                "Year"=>$a_newInvoice['Year'][$id],
                "Date"=>$cls_help->toDbDate($a_newInvoice['Date'][$id])
            );

            $checkBind = $cls_db->bindInsert("flow_invoices",$a_insert,$a_bind);
            if($checkBind===false) {
                echo "ERROR ".mysqli_error($cls_db->conn);
                die;
            }

        }
    }
    $storico->insRow('I', "Aggiunta fattura per ente ".$nome_ente."[".$c."]");
}

if(count($a_invoice['Save'])>0){
    $a_bind = array(
        "Number"=>"i",
        "Year"=>"i",
        "Date"=>"s"
    );
    foreach($a_invoice['Save'] as $id=>$val){
        if($val=="y"){
            $a_update = array(
                "Number"=>$a_invoice['Number'][$id],
                "Year"=>$a_invoice['Year'][$id],
                "Date"=>$cls_help->toDbDate($a_invoice['Date'][$id])
            );

            $filter = "WHERE Id=".$id;

            $checkBind = $cls_db->bindUpdate("flow_invoices",$a_update, $a_bind, $filter);

            if($checkBind===false) {
                echo "ERROR ".mysqli_error($cls_db->conn);
                die;
            }
        }
    }
    $storico->insRow('U', "Modificato elenco fatture per ente ".$nome_ente."[".$c."]");
}

if(count($a_flow['Save'])>0){

    $a_bind = array(
        "PrintInvoiceId"=>"i",
        "PostageInvoiceId"=>"i",
        "SendDate"=>"s",
        "UploadDate"=>"s",
        "CancelDate"=>"s",
        "CancelReason"=>"s",
        "PostagePaymentBankId"=>"i",
        "PostagePaymentDate"=>"s",
        "Zone0Number"=>"i",
        "Zone0Postage"=>"d",
        "Zone1Number"=>"i",
        "Zone1Postage"=>"d",
        "Zone2Number"=>"i",
        "Zone2Postage"=>"d",
        "Zone3Number"=>"i",
        "Zone3Postage"=>"d",
        "PrintCost"=>"d",
        "RecordsNumber"=>"i",
        "ProcessingDate"=>"s",
        "ShippingOffice"=>"s"
    );

    foreach($a_flow['Save'] as $id=>$val){
        if($val=="y"){
            $a_update = array(
                "PrintInvoiceId"=>$a_flow['PrintInvoiceId'][$id],
                "PostageInvoiceId"=>$a_flow['PostageInvoiceId'][$id],
                "SendDate"=>$cls_help->toDbDate($a_flow['SendDate'][$id]),
                "UploadDate"=>$cls_help->toDbDate($a_flow['UploadDate'][$id]),
                "CancelDate"=>$cls_help->toDbDate($a_flow['CancelDate'][$id]),
                "CancelReason"=>$a_flow['CancelReason'][$id],
                "PostagePaymentBankId"=>$a_flow['PostagePaymentBankId'][$id],
                "PostagePaymentDate"=>$cls_help->toDbDate($a_flow['PostagePaymentDate'][$id]),
                "Zone0Number"=>$a_flow['Zone0Number'][$id],
                "Zone0Postage"=>$cls_help->stringToFloat($a_flow['Zone0Postage'][$id]),
                "Zone1Number"=>$a_flow['Zone1Number'][$id],
                "Zone1Postage"=>$cls_help->stringToFloat($a_flow['Zone1Postage'][$id]),
                "Zone2Number"=>$a_flow['Zone2Number'][$id],
                "Zone2Postage"=>$cls_help->stringToFloat($a_flow['Zone2Postage'][$id]),
                "Zone3Number"=>$a_flow['Zone3Number'][$id],
                "Zone3Postage"=>$cls_help->stringToFloat($a_flow['Zone3Postage'][$id]),
                "PrintCost"=>$cls_help->stringToFloat($a_flow['PrintCost'][$id]),
                "RecordsNumber"=>$a_flow['RecordsNumber'][$id],
                "ProcessingDate"=>$cls_help->toDbDate($a_flow['ProcessingDate'][$id]),
                "ShippingOffice"=>$a_flow['ShippingOffice'][$id]
            );

//            var_dump($a_update);


            $filter = "WHERE Id=".$id;

            $checkBind = $cls_db->bindUpdate("flows",$a_update, $a_bind, $filter);
            if($checkBind===false) {
                echo "ERROR ".mysqli_error($cls_db->conn);
                die;
            }
        }
    }
    $storico->insRow('U', "Modificato elenco flussi per ente ".$nome_ente."[".$c."]");
}


$save_alert = "Salvataggio avvenuto con successo!";

echo "<script>location.href = '".WEB_ROOT."/coattiva/flow_mgmt.php?c=".$_POST['c']."&a=".$_POST['a']."&error=0&msg=".$save_alert."&filterInvoiceNumber=".$filterInvoiceNumber."&filterInvoiceYear=".$filterInvoiceYear."&filterInvoiceDate=".$filterInvoiceDate."&filterFlowNumber=".$filterFlowNumber."&filterFlowYear=".$filterFlowYear."&filterFlowStatus=".$filterFlowStatus."&filterFlowMissStatus=".$filterFlowMissStatus."&filterFlowStatusOfDate=".$filterFlowStatusOfDate."&filterFlowStatusDate=".$filterFlowStatusDate."&filterFlowCityId=".$filterFlowCityId ."&filterFlowPrinter=".$filteFlowrPrinter ."&activeTab=".$activeTab."'</script>";

?>

<script>
    //alert("<?=$save_alert;?>");
    /**
     *  GV _ 23/05/2022 START 
     * location.href = "<?=WEB_ROOT."/coattiva/flow_mgmt.php?c=".$_POST['c']."&a=".$_POST['a'];?>";
     */
    //var href_tab = "<?=WEB_ROOT."/coattiva/flow_mgmt.php?c=".$_POST['c']."&a=".$_POST['a']."&error=0&msg=".$save_alert."&filterInvoiceNumber=".$filterInvoiceNumber."&filterInvoiceYear=".$filterInvoiceYear."&filterInvoiceDate=".$filterInvoiceDate."&filterFlowNumber=".$filterFlowNumber."&filterFlowYear=".$filterFlowYear."&filterFlowStatus=".$filterFlowStatus."&filterFlowMissStatus=".$filterFlowMissStatus."&filterFlowStatusOfDate=".$filterFlowStatusOfDate."&filterFlowStatusDate=".$filterFlowStatusDate."&filterFlowCityId=".$filterFlowCityId ."&activeTab=".$activeTab;?>";


    //location.href = href_tab;

    /**
     * GV - 23/05/2022   END  
     */ 
    
</script>
