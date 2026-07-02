<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";

include(CLS."/cls_ftp.php");
include(CLS."/cls_phpmailer.php");
include_once CLS . "/cls_ElaborationStatus.php";
include_once CLS . "/cls_storico.php";													


define('FTP_HOST', 'ftp.mercurioservice.it');
define('FTP_USER', 'sarida');
define('FTP_PASS', '1ftp4sarida');

try{

$ftp = new cls_ftp(FTP_HOST, FTP_USER, FTP_PASS,true);
$storico = new storico('storicoElaborazioni','5');
$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();


$el_list_id = intval($cls_help->getVar("el_list_Id"));


if(is_null($el_list_id)){
        echo json_encode(['esito' => 'KO', 'message' => 'Elaboration_List_Id INESISTENTE']);
	return;
}

$query_elab_list =  "   SELECT    el_l.*, ".
                    "           f.FileName, f.CityId,     ".
                    "           doc_t.FolderName, doc_t.Description AS DocumentType   ".
                    "   FROM elaboration_lists AS el_l    ".
                    "       JOIN flows as f on f.Id = el_l.FlowId  ".
                    "       JOIN document_type as doc_t on doc_t.Id = el_l.DocumentTypeId  ".
                    "   WHERE el_l.Id = ".$el_list_id; 
$results_elab_list = $cls_db->ExecuteQuery($query_elab_list);
$elab_list = $cls_db->getArrayLine($results_elab_list);

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$elab_list['CityId']."'") );
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";
$adminCityName = $a_enteAdmin['Denominazione'];

$aggiorna_elaboration=function() use($el_list_id,$cls_db,$elab_list){
        $a_dbParams_elab_list = array(
            'table' => 'elaboration_lists',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' =>$el_list_id),
            ),
            'fields'=> array(
                array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => ElaborationStatus::FLUSSI_CHIUSI),
                array(  'name' => 'FlowDate',   'type' => 'date', 'value' => date('Y-m-d')),
            )
        );
        $cls_db->DbSave($a_dbParams_elab_list);

        $contList = $cls_db->getNumberRow($cls_db->ExecuteQuery("SELECT Id FROM elaboration_lists WHERE Elaboration_Id=".$elab_list['Elaboration_Id']." AND Elaboration_Status_Id = ".ElaborationStatus::FLUSSI_CHIUSI.""));

        $query_elab = " SELECT * FROM  elaborations WHERE Id =" . $elab_list['Elaboration_Id'];
        $results_elab = $cls_db->ExecuteQuery($query_elab);
        $elabs = $cls_db->getArrayLine($results_elab);


        $a_dbParams_elab = array(
            'table' => 'elaborations',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $elab_list['Elaboration_Id']),
            ),
            'fields'=> array(
                array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),

            )
        );

        if((int)$elabs['ListNumber']==(int)$contList)
            $a_dbParams_elab['fields'][] = array(  'name' => 'Elaboration_Status_Id', 'type' => 'int', 'value' => ElaborationStatus::FLUSSI_CHIUSI);

        $cls_db->DbSave($a_dbParams_elab);

        $query = "UPDATE flows SET UploadDate = '".date("Y-m-d")."' WHERE Id = ".$elab_list['FlowId'];
        $result = $cls_db->ExecuteQuery($query);
        return $result;
};


    switch ($elab_list['PrinterId']) {
        case 1:
            
            $result = $aggiorna_elaboration();

        break;
    
        default:

            $a_sender = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM user_emails WHERE User_Id= ".$_SESSION['aut_progr']));//1
            // $a_sender = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM user_emails WHERE User_Id=1" ));//1 //GRF
            
            if(is_null($a_sender)){
                $msg = "PARAMETRI EMAIL MITTENTE ".$_SESSION['username']." ASSENTI. CONTATTARE IT PER AGGIUNGERE I DATI";
                //echo "<script>window.location = document.referrer + '&error=1&msg={$msg}';</script>";
                throw new Exception($msg);
                die;
            }

            $a_recipient = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM printer WHERE ID=".$elab_list['PrinterId']));
            // $a_recipient['Email'] = "fabrizio.g@ovunque-si.it"; //GRF
            if(empty($a_recipient['Email'])){
                $msg = "EMAIL STAMPATORE ASSENTE. CONTATTARE IT PER AGGIUNGERE I DATI";
                //echo "<script>window.location = document.referrer + '&error=1&msg={$msg}';</script>";
                throw new Exception($msg);
                die;
            }

            if($a_enteAdmin['Gestore_ID']>0)
                $enteEmail = $a_enteAdmin['Gestore_Mail'];
            else if($a_enteAdmin['Info_ID']>0)
                $enteEmail = $a_enteAdmin['Info_Mail'];
            if(empty($enteEmail)){
                $msg = "EMAIL GESTORE/ENTE ASSENTE. INSERIRE I DATI NEI PARAMETRI.";
                //echo "<script>window.location = document.referrer + '&error=1&msg={$msg}';</script>";
                throw new Exception($msg);
                die;
            } //GRF

   
            $cls_mail = new cls_phpmailer($a_sender);
    
           $cls_mail->SMTPDebug = 0;
            //BLOCCATO INIO EMAIL
            $cls_mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '".$elab_list["CityId"]."'";
            $comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"enti_gestiti")["Denominazione"];
            $a_params = array(
                "subject" => "UPLOAD FLUSSO N.".$elab_list["FlowNumber"]."/".$elab_list["FlowYear"]." - ".$elab_list["CityId"],
                "body" =>
                    "Email automatica: flusso  N. ".$elab_list["FlowNumber"]."/".$elab_list["FlowYear"]." (".$elab_list['DocumentType'].") di ".$comune." - ".$elab_list["CityId"]." 
                    caricato su Area FTP Mercurio Service nella cartella UPLOAD_FLUSSI"
            );

            $cls_mail->mailCreation($a_params);
            $cls_mail->addAddress($a_recipient['Email']);
            $cls_mail->addAddress($enteEmail); //GRF
            $cls_mail->addAddress($a_sender['Address']);
    
            $file = FLUSSI ."/".$elab_list["FlowId"] ."/". $elab_list['FileName'];
            $flag = $ftp->loadFile($file,"/UPLOAD_FLUSSI/".$elab_list['FileName']);
            // $flag = $ftp->loadFile($file,"/TEST_FLUSSI/".$elab_list['FileName']); //GRF
    
            $msg = "File caricati correttamente";
            $error = 0;
//        $flag = true;
    
        if($flag)
        {
            
            $result = $aggiorna_elaboration();

            if($result)
            {
               if(!$cls_mail->send()){
                    $error = 2;
                    $msg = "File caricato, ma errore di spedizione delle mail";
                    throw new Exception("File caricato, ma errore di spedizione delle mail");
               }
            }
            else{
                $error = 2;
                $msg = "File caricato, ma aggiornamento data di caricamento non salvata";
                throw new Exception("File caricato, ma aggiornamento data di caricamento non salvata");
            }
        }
        else{
            $msg="Errore file non caricato";
            $error = 1;
    
            throw new Exception("Errore file non caricato");
        }

        break;
    }

    $a_dbParams = array(
       'table' => 'partita_tributi',
       'updateField' => array(
           array('name' => 'Elaboration_Id',       'type' => 'int',        'value' => $elab_list['Elaboration_Id']),
       ),
       'fields'=> array(
           array(  'name' => 'Elaboration_Id',  	'type' => 'int', 'value' => NULL),
           array(  'name' => 'Position_Status_Id',	'type' => 'int', 'value' => NULL),
           array(  'name' => 'flag_elaboration',   'type' => 'int', 'value' => NULL),
       )
    );

    $cls_db->DbSave($a_dbParams);

    $storico_query_1 = "SELECT * FROM elaborations WHERE Id = ".$elab_list['Elaboration_Id'];
    $elab = $cls_db->getArrayLine($cls_db->ExecuteQuery($storico_query_1));
    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$elab['CC']."'") );

    $atto_ = "";

    //if($terzo=="lavoro") { $atto_ = "Pignoramenti presso datore di lavoro";}
    //if($terzo=="banca") { $atto_ = "Pignoramenti presso banca";}

    switch($elab['Document_Type_Id']){
        case 7:
            $atto_ = "Pignoramenti presso datore di lavoro";
            break;
        case 8:
            $atto_ = "Pignoramenti presso banca";
            break;
        case 22:
            $atto_ = "Preavvisi fermi amministrativi";
            break;
        default:
            break;
    }

    $storico->insRow('E', "Inviato flusso numero ".$elab_list['FlowNumber']." elemento ".$cls_help->getVar("el_list_Id")." elaborazione '".$elab['Description']."': ".$atto_." ".$ente['Denominazione']."[".$elab['CC']."]");
}
catch(Exception $e)
{
   /*  if($error == 0) $error = 1;
    echo "<script>window.location = document.referrer + '&error={$error}&msg={$ex->getMessage()}';</script>"; */

    if(!empty($cls_db)) $cls_db->Rollback();
    $errmsg = "Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage();
    if(!empty($log)) $log->error($errmsg);

    echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE : '.$errmsg]);
    return;
}



echo json_encode( ['esito' => 'OK','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO È ANDATA A BUON FINE']);
    return;

?>