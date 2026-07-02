<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

if($_SESSION['username']=="mirkop"){
////    $a_cc = $cls_db->getResults($cls_db->ExecuteQuery("SELECT DISTINCT CC FROM enti_gestiti"));
////
////    $sqlCreate = "CREATE TEMPORARY TABLE tmptable_1 SELECT * FROM parametri_annuali WHERE ID=834";
////    $sqlUpdate = "UPDATE tmptable_1 SET ID = NULL, CC=NULL, Anno=2020";
////    $cls_db->ExecuteQuery($sqlCreate);
////    $cls_db->ExecuteQuery($sqlUpdate);
////
////    for($i=0;$i<count($a_cc);$i++){
////        if($a_cc[$i]['CC']!="C826" && $a_cc[$i]['CC']!="A699"){
////            $sqlUpdate = "UPDATE tmptable_1 SET CC='".$a_cc[$i]['CC']."'";
////            $cls_db->ExecuteQuery($sqlUpdate);
////            $cls_db->ExecuteQuery("INSERT INTO parametri_annuali SELECT * FROM tmptable_1");
////        }
////    }
////
////    $sqlDrop = "DROP TEMPORARY TABLE IF EXISTS tmptable_1";
//

$query = "SELECT pagamento.ID AS Pagamento_ID, pignoramento_generale.* ";
$query.= "FROM pagamento JOIN pignoramento_generale ON pignoramento_generale.ID=pagamento.Atto_ID ";
$query.= "WHERE pagamento.DocumentTypeTableId=2 AND pagamento.DocumentTypeId is null";

$a_pagamenti = $cls_db->getResults($cls_db->ExecuteQuery($query));

echo count($a_pagamenti);
for($i=0;$i<count($a_pagamenti);$i++){

    $query = "UPDATE pagamento SET DocumentTypeId=".$a_pagamenti[$i]['DocumentTypeId']." WHERE ID=".$a_pagamenti[$i]['Pagamento_ID'];
    echo $query."<br><br>";
    $cls_db->ExecuteQuery($query);
}

//    $query = "SELECT OLDFLOW.*, FLOW.Nome_Flusso_Rar, FLOW.Data_Travaso_Verso_Gitco FROM num_stamp_a4 AS OLDFLOW ";
//    $query.= "JOIN flussi_tabella AS FLOW ON FLOW.CC_Comune = OLDFLOW.Num_Comune AND FLOW.Tipo = OLDFLOW.Num_Tributo AND FLOW.Data_Flusso = OLDFLOW.Num_Data_Flusso ";
//    $query.= "AND FLOW.Num_Flusso = OLDFLOW.Num_Flusso AND FLOW.Anno = OLDFLOW.Num_Anno ";
//    $query.= "ORDER BY Num_Progr";
//    $a_oldFlows = $cls_db->getResults($cls_db->ExecuteQuery($query));
//    for($i=0;$i<count($a_oldFlows);$i++){
//
//        $postageInvoiceId = 0;
//        $printInvoiceId = 0;
//
//        if($a_oldFlows[$i]['Num_Anno']>=2018){
//            $a_postageInvoice = explode(" ",$a_oldFlows[$i]['Num_Fattura_Spese']);
//            $a_printInvoice = explode(" ",$a_oldFlows[$i]['Num_Fattura_Lavorazione']);
//
//
//            if($a_oldFlows[$i]['Num_Fattura_Spese']!="" && $a_oldFlows[$i]['Num_Fattura_Spese']!=null){
//                $date = explode("/",$a_postageInvoice[1]);
//                $query = "SELECT Id FROM flow_invoices_2 WHERE Number=".intval($a_postageInvoice[0])." AND Date=\"".$cls_help->toDbDate($a_postageInvoice[1])."\"";
//                $a_invoice = $cls_db->getResults($cls_db->ExecuteQuery($query));
//                if(!count($a_invoice)>0){
//                    $query = "INSERT INTO flow_invoices_2 (Number,Year,Date) VALUES (".intval($a_postageInvoice[0]).",".$date[2].",\"".$cls_help->toDbDate($a_postageInvoice[1])."\")";
//                    $cls_db->ExecuteQuery($query);
//                    $postageInvoiceId = $cls_db->lastInsertId();
//                }
//                else{
//                    $postageInvoiceId = $a_invoice[0]["Id"];
//                }
//                echo "<br> FATTURA SPESE POSTALI ".$a_postageInvoice[0]." DEL ".$a_postageInvoice[1]." ID ".$postageInvoiceId."<br><br>";
//            }
//
//            if($a_oldFlows[$i]['Num_Fattura_Lavorazione']!="" && $a_oldFlows[$i]['Num_Fattura_Lavorazione']!=null){
//                $date = explode("/",$a_printInvoice[1]);
//                $query = "SELECT Id FROM flow_invoices_2 WHERE Number=".intval($a_printInvoice[0])." AND Date=\"".$cls_help->toDbDate($a_printInvoice[1])."\"";
//                $a_invoice = $cls_db->getResults($cls_db->ExecuteQuery($query));
//                if(!count($a_invoice)>0){
//                    $query = "INSERT INTO flow_invoices_2 (Number,Year,Date) VALUES (".intval($a_printInvoice[0]).",".$date[2].",\"".$cls_help->toDbDate($a_printInvoice[1])."\")";
//                    $cls_db->ExecuteQuery($query);
//                    $printInvoiceId = $cls_db->lastInsertId();
//                }
//                else{
//                    $printInvoiceId = $a_invoice[0]["Id"];
//                }
//                echo "<br> FATTURA LAVORAZIONE ".$a_printInvoice[0]." DEL ".$a_printInvoice[1]." ID ".$printInvoiceId."<br><br>";
//            }
//        }
//
//
//        $a_insert = array();
//
//        $a_insert['CityId'] = $a_oldFlows[$i]['Num_Comune'];
//        $a_insert['Number'] = $a_oldFlows[$i]['Num_Flusso'];
//        $a_insert['Year'] = $a_oldFlows[$i]['Num_Anno'];
//        $a_insert['PrinterId'] = 1;
//        $a_insert['PrintTypeId'] = 1;
//        switch($a_oldFlows[$i]['Num_Tributo']){
//            case "ING":             $a_insert['DocumentTypeId'] = 2;   break;
//            case "AV_INT":          $a_insert['DocumentTypeId'] = 4;   break;
//            case "PIGNO_LAVORO":    $a_insert['DocumentTypeId'] = 7;   break;
//            case "PIGNO_BANCA":     $a_insert['DocumentTypeId'] = 8;   break;
//            case "SOLL_PRE":        $a_insert['DocumentTypeId'] = 11;  $a_insert['PrintTypeId'] = 3; break;
//            case "AV_MORA":         $a_insert['DocumentTypeId'] = 12;  break;
//        }
//        $a_insert['RecordsNumber'] = $a_oldFlows[$i]['Num_Numero_Stampe'];
//        $a_insert['OriginalRecordsNumber'] = $a_oldFlows[$i]['Num_Numero_Stampe'];
//        $a_insert['Zone0Number'] = $a_oldFlows[$i]['Num_Numero_Stampe'];
//        $a_insert['Zone1Number'] = 0;
//        $a_insert['Zone2Number'] = 0;
//        $a_insert['Zone3Number'] = 0;
//        $a_insert['Zone0Postage'] = $a_oldFlows[$i]['Num_Spese_Postali'];
//        $a_insert['Zone1Postage'] = 0;
//        $a_insert['Zone2Postage'] = 0;
//        $a_insert['Zone3Postage'] = 0;
//        $a_insert['PrintCost'] = $a_oldFlows[$i]['Num_Spese_Stampa'];
//        $a_insert['CreationDate'] = $a_oldFlows[$i]['Num_Data_Flusso'];
//        $a_insert['FileName'] = $a_oldFlows[$i]['Nome_Flusso_Rar'];
//        $a_insert['OldExportDate'] = $a_oldFlows[$i]['Data_Travaso_Verso_Gitco'];
//        $a_insert['PostageInvoiceId'] = $postageInvoiceId;
//        $a_insert['PrintInvoiceId'] = $printInvoiceId;
//
//        $a_bind['CityId'] = "s";
//        $a_bind['Number'] = "i";
//        $a_bind['Year'] = "i";
//        $a_bind['PrinterId'] = "i";
//        $a_bind['PrintTypeId'] = "i";
//        $a_bind['DocumentTypeId'] = "i";
//        $a_bind['RecordsNumber'] = "i";
//        $a_bind['OriginalRecordsNumber'] = "i";
//        $a_bind['Zone0Number'] = "i";
//        $a_bind['Zone1Number'] = "i";
//        $a_bind['Zone2Number'] = "i";
//        $a_bind['Zone3Number'] = "i";
//        $a_bind['Zone0Postage'] = "d";
//        $a_bind['Zone1Postage'] = "d";
//        $a_bind['Zone2Postage'] = "d";
//        $a_bind['Zone3Postage'] = "d";
//        $a_bind['PrintCost'] = "d";
//        $a_bind['CreationDate'] = "s";
//        $a_bind['FileName'] = "s";
//        $a_bind['OldExportDate'] = "s";
//        $a_bind['PostageInvoiceId'] = "i";
//        $a_bind['PrintInvoiceId'] = "i";
//
//        $checkBind = $cls_db->bindInsert("flows_2",$a_insert,$a_bind);
//        if($checkBind===false) {
//            echo "ERROR ".mysqli_error($cls_db->conn);
//            die;
//        }
//        else
//            print_r($a_insert);
//    }




}


include(INC."/footer.php");
?>