<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_zip.php");
include_once(CLS."/cls_file.php");
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_zip = new cls_zip();
$cls_file = new cls_file();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$Elaboration_List_Id = (int)$cls_help->getVar('Elaboration_List_Id');
$Elaboration_Id = (int)$cls_help->getVar('Elaboration_Id');

$zip = $cls_help->getVar('signedZip');
if(!is_file($zip)){
    $msg = "Nessun file zip caricato";
    header("Location: ".WEB_ROOT."/elaborazioni/mgmt_elaboration.php?c=".$c."&a=".$a."&el=".$Elaboration_Id."&error=1&msg=".$msg);

    die;
}
    

$cls_zip->extractZip($zip,SIGNED_FILES."/temp");
$a_signedFiles = $cls_file->getFilesFromPath(SIGNED_FILES."/temp");

$query = "SELECT 
A.ID, A.Partita_ID, A.ID_Cronologico, A.Anno_Cronologico, A.CC, A.Data_Stampa, A.SignedPdfFlag, DT.PrefixName, DT.FolderName
FROM atto as A
JOIN document_type DT ON DT.Id=A.DocumentTypeId
WHERE A.Elaboration_List_Id = ".$Elaboration_List_Id;
$a_acts = $cls_db->getResults($cls_db->ExecuteQuery($query));
$act_pathFolder = "";
$filesMsg = "";
foreach($a_acts as $key=>$a_act){
    if($key==0)
        $act_pathFolder = ATTI."/".$a_act['CC']."/".$a_act['FolderName']."/STAMPE DEFINITIVE";
    
    $base_name = $a_act['PrefixName']."_".$a_act['CC']."_".$a_act['Anno_Cronologico']."_".$a_act['ID_Cronologico']."_".$a_act['Data_Stampa'];
    $file = $act_pathFolder."/".$base_name.".pdf";
    $signedFileName = $base_name."_signed.pdf";
    $signedFile = $act_pathFolder."/".$base_name."_signed.pdf";
    if(is_file($file)){
        $signedKey = array_search($signedFileName, array_column($a_signedFiles, 'fileName'));
        if($signedKey!==false && is_file($a_signedFiles[$signedKey]['file'])){
            if(copy($a_signedFiles[$signedKey]['file'], $signedFile)){
                unlink($a_signedFiles[$signedKey]['file']);
                if(empty($a_act['SignedPdfFlag'])){
                    $cls_db->ExecuteQuery("UPDATE atto SET SignedPdfFlag = 1 WHERE ID = ".$a_act['ID']);
                }
            }
            else if(empty($a_act['SignedPdfFlag']))
                $filesMsg.= " - Errore copia ".$a_act['ID_Cronologico']."/".$a_act['Anno_Cronologico'];
        }
        else if(empty($a_act['SignedPdfFlag']))
            $filesMsg.= " - Assenza pdf firmato ".$a_act['ID_Cronologico']."/".$a_act['Anno_Cronologico'];
    }
    else if(empty($a_act['SignedPdfFlag']))
        $filesMsg.= " - Assenza pdf originale ".$a_act['ID_Cronologico']."/".$a_act['Anno_Cronologico'];
}

if($filesMsg==""){
    unlink($zip);
    $msg = "Caricamento file firmati eseguito con successo!";
    $error = 0;
}
else{
    $msg = "Problemi nel caricamento:";
    $error = 0;
}

$query = "SELECT COUNT(*) AS NotSignedPdf FROM atto WHERE Elaboration_List_Id = ".$Elaboration_List_Id." AND SignedPdfFlag=0";
$a_count = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if($a_count['NotSignedPdf']==0)
    $cls_db->ExecuteQuery("UPDATE elaboration_lists SET SignedPdfFlag = 1 WHERE ID = ".$Elaboration_List_Id);

header("Location: ".WEB_ROOT."/elaborazioni/mgmt_elaboration.php?c=".$c."&a=".$a."&el=".$Elaboration_Id."&error=1&msg=".$msg.$filesMsg);

    

