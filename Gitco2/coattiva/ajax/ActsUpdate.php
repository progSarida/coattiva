<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$msgPartite = "";
$type = (int)$cls_help->getVar('type');
$flowId = (int)$cls_help->getVar('flowId');
switch($type){
    case 1:
        $queryAtti = "UPDATE atto SET Data_Flusso=null, Numero_Flusso=null, Anno_Flusso=null, FlowId=null, RemovedFlowId=".$flowId." WHERE FlowId=".$flowId;
        $msgType = "Rimozione flusso: ";
        break;
    case 2:
        $queryAtti = "UPDATE atto SET Data_Flusso=null, Numero_Flusso=null, Anno_Flusso=null, Stato_Stampa='Da stampare', Data_Stampa=null, FlowId=null, RemovedFlowId=".$flowId." WHERE FlowId=".$flowId." OR (FlowId is null AND RemovedFlowId=".$flowId.")";
        $msgType = "Rimozione flusso e stato stampa: ";
        break;
    case 3:
        $queryAtti = "UPDATE atto SET Data_Flusso=null, Numero_Flusso=null, Anno_Flusso=null, Stato_Stampa='Da stampare', Data_Stampa=null, Anno_Cronologico=null, ID_Cronologico=null, FlowId=null, RemovedFlowId=".$flowId." WHERE FlowId=".$flowId." OR (FlowId is null AND RemovedFlowId=".$flowId.")";
        $msgType = "Rimozione flusso, stato stampa e cronologico: ";
        break;
    case 4:
        $queryPartite = "UPDATE `partita_tributi` P JOIN atto A ON A.Partita_ID=P.ID
        SET P.Elaboration_Id=null, P.Position_Status_Id=null, P.flag_elaboration=null
        WHERE A.FlowId=".$flowId." AND A.Elaboration_Id=P.Elaboration_Id";
        $check = $cls_db->ExecuteQuery($queryPartite);
        $rows = $cls_db->GetAffectedRows();
        $msgPartite = " - SGANCIATE ".$rows." PARTITE (Flow Id ".$flowId.")";

        $queryAtti = "DELETE FROM atto WHERE FlowId=".$flowId." OR (FlowId is null AND RemovedFlowId=".$flowId.")";
        $msgType = "Eliminazione: ";
        break;
    case 5:
        $queryAtti = "DELETE FROM flows WHERE Id=".$flowId;
        $msgType = "";
        break;
}

$check = $cls_db->ExecuteQuery($queryAtti);
$rows = $cls_db->GetAffectedRows();
$msg = $msgType.$rows." ATTI";
if($type==5)
    $msg = $rows." FLUSSO ELIMINATO";

$msg.= $msgPartite;
$elabListRow = null;
$elabRow = null;
if($type==4){
    $queryElab = "SELECT E.Id, E.ListNumber, E.CC, E.Description, EL.FlowNumber, EL.FlowYear ";
    $queryElab.= "FROM elaboration_lists EL JOIN elaborations E ON E.Id=EL.Elaboration_Id WHERE EL.FlowId=".$flowId;
    $a_elab = $cls_db->getArrayLine($cls_db->ExecuteQuery($queryElab));

    if(!empty($a_elab['Id'])){
        $query = "DELETE FROM elaboration_lists WHERE FlowId=".$flowId;
        $check = $cls_db->ExecuteQuery($query);
        $elabListRow = $cls_db->GetAffectedRows();
        if($elabListRow>0){
            $msg.= " - ELIMINATA LISTA ELABORAZIONE LEGATA AL FLUSSO ".$a_elab['FlowNumber']." DEL ".$a_elab['FlowYear']." (Flow Id ".$flowId.")";
            $check = $cls_db->ExecuteQuery($query);
            $elabListRow = $cls_db->GetAffectedRows();

        }
            

        if($a_elab['ListNumber']==1){
            $query = "DELETE FROM elaborations WHERE Id=".$a_elab['Id'];
            $check = $cls_db->ExecuteQuery($query);
            $elabRow = $cls_db->GetAffectedRows();
            if($elabRow>0)
                $msg.= " - ELIMINATA ELABORAZIONE ".$a_elab['CC']." ".$a_elab['Description']." (Elab Id ".$a_elab['Id'].")";
        }
        else{
            $query = "UPDATE elaborations SET ListNumber=".($a_elab['ListNumber']-1)." WHERE Id=".$a_elab['Id'];
            $check = $cls_db->ExecuteQuery($query);
            $elabRow = $cls_db->GetAffectedRows();
            if($elabRow>0)
                $msg.= " - ELABORAZIONE NON ELIMINABILE ".$a_elab['CC']." ".$a_elab['Description']." (Id ".$a_elab['Id'].") PER PRESENZA DI ".$a_elab['ListNumber']." LISTE";

        }
    }
}

echo json_encode([ 'query' => $queryAtti, 'type' => $type, 'check' => $check, 'rows' => $rows, 'msg' => $msg ]);
return;
