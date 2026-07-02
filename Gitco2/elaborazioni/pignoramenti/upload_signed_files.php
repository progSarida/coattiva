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
    header("Location: ".ELAB_PIGNORAMENTI_WEB."/mgmt_pignoramenti.php?c=".$c."&a=".$a."&el=".$Elaboration_Id."&error=1&msg=".$msg);

    die;
}
    

$crea_file_name=function($a_results,$suffix="Copia") use($c){

    $pignoId = $a_results["PignoID"];

    if( is_dir( PIGNORAMENTI."/".$pignoId ) == false )
    {
        mkdir(PIGNORAMENTI."/".$pignoId);
    }
    $prefix=$a_results["PrefixName"];
    $cc=$c;
    $anno= $a_results["Anno_Cronologico"];
    $id=$a_results["ID_Cronologico"];
    $notifica_id=$a_results["ID"];

    $path=$pignoId."/";
    $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_".$suffix.".pdf";
    $filename_Relata=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata".".pdf";
    $filename_Relata_Signed=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata_signed".".pdf";
    $path_completo =  PIGNORAMENTI."/".$path.$filename;
    $path_completo_Relata =  PIGNORAMENTI."/".$path.$filename_Relata;
    $path_completo_Relata_Signed = SIGNED_FILES."/temp/".$filename_Relata_Signed;
    $result=array();
    $result["Path"] = PIGNORAMENTI."/".$path;
    $result["PathCompleto"] = $path_completo;
    $result["PathCompleto_Relata"] = $path_completo_Relata;
    $result["PathCompleto_Relata_Signed"] = $path_completo_Relata_Signed;
    $result["FileName"] = $filename;
    $result["FileName_Relata"] = $filename_Relata;
    $result["FileName_Relata_Signed"] = $filename_Relata_Signed;
    $result["FileName_Relata_Signed_Destinazione"] = $result["Path"] ."/".$result["FileName_Relata_Signed"];
    return $result;
};

$cls_zip->extractZip($zip,SIGNED_FILES."/temp");
$a_signedFiles = $cls_file->getFilesFromPath(SIGNED_FILES."/temp");
$query = "   SELECT * FROM v_manage_acts_pignoramenti_flusso " .
                "   WHERE Elaboration_List_Id = ".$Elaboration_List_Id. " AND SignedPdfFlag=0" .
                "   AND   CC = '" . $c . "' ORDER BY Anno_Cronologico ASC, ID_Cronologico ASC";

$a_acts = $cls_db->getResults($cls_db->ExecuteQuery($query));

$act_pathFolder = "";
$filesMsg = "";
foreach($a_acts as $key=>$a_act){
    $a_fileName = $crea_file_name($a_act);
    
    if($key==0)
        $act_pathFolder = $a_fileName["Path"];
    
    $file = $a_fileName["PathCompleto_Relata"];
    $signedFileName = $a_fileName["FileName_Relata_Signed"];
    $signedFile = $a_fileName["FileName_Relata_Signed_Destinazione"];
    
    if(is_file($file)){
        $signedKey = array_search($signedFileName, array_column($a_signedFiles, 'fileName'));
        if($signedKey!==false && is_file($a_signedFiles[$signedKey]['file'])){
            if(copy($a_signedFiles[$signedKey]['file'], $signedFile)){
                unlink($a_signedFiles[$signedKey]['file']);
                if(empty($a_act['SignedPdfFlag'])){
                    $cls_db->ExecuteQuery("UPDATE notifica_atto SET SignedPdfFlag = 1 WHERE ID = ".$a_act['ID']);
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

$query = "SELECT COUNT(*) AS NotSignedPdf FROM notifica_atto WHERE Elaboration_List_Id = ".$Elaboration_List_Id." AND SignedPdfFlag=0";
$a_count = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if($a_count['NotSignedPdf']==0)
    $cls_db->ExecuteQuery("UPDATE elaboration_lists SET SignedPdfFlag = 1 WHERE ID = ".$Elaboration_List_Id);

header("Location: ".ELAB_PIGNORAMENTI_WEB."/mgmt_pignoramenti.php?c=".$c."&a=".$a."&el=".$Elaboration_Id."&error=1&msg=".$msg.$filesMsg);

    

