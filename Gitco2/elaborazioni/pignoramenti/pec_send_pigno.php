<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once INC . "/header.php";

?>
<script>
    $('#cityAdminHeader').hide();
</script>
<?php

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_phpmailer.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$db = new cls_db();
$help = new cls_help();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$terzo = $cls_help->getVar('terzo');
$lavoro= false; $banca=false;
if($terzo=="lavoro") { $lavoro = true;}
if($terzo=="banca") { $banca = true;}

$Elaboration_List_Id = (int)$cls_help->getVar('Elaboration_List_Id');
$Elaboration_Id = $cls_help->getVar('Elaboration_Id');
?>
<script>
    function startBar(){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio spedizione PEC...");
    }

    function waitBar(text){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text(text);
    }

    function updateBar(perc, n_pec, totale_pec){
        //alert(valore);
        $( "#progressbar" ).progressbar({value: parseInt(perc) });
        $( "#barlabel" ).text( n_pec + "/"+totale_pec );
    }

    function noResultsBar(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function endBar(value, extraGet) {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text(value);
        sleep(1000);
        
        
        <?php if($lavoro)
        {
            ?>
            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_LAVORO_WEB ?>/mgmt_pignoramenti_lavoro.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$Elaboration_Id;?>"+extraGet;
            <?php
        }
        else if($banca)
        {
            ?>
            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti_banca.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$Elaboration_Id;?>"+extraGet;
			<?php
        }
        else
        {
            ?>
            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$Elaboration_Id;?>"+extraGet;
			<?php
        }
        ?>

    }
    
</script>

<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Spedizione PEC</span>
    </div>
</div>
<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>

<?php
set_time_limit(-1);


flush();	ob_flush();		flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();


$query = "SELECT PG.ID as PignoID,
NA.ID, PG.Partita_ID, PG.ID_Cronologico, PG.Anno_Cronologico, PG.CC, NA.SignedPdfFlag, PG.DocumentTypeId, PG.Elaboration_Id,
DT.Description AS DocumentType, DT.PrefixName, DT.FolderName, U.PEC, E.Sending_Date AS Email_Sending_Date
FROM notifica_atto as NA
JOIN pignoramento_generale as PG on NA.Atto_Notificato_ID=PG.ID
JOIN partita_tributi P ON P.ID=PG.Partita_ID
JOIN document_type DT ON DT.Id=PG.DocumentTypeId
JOIN utente U ON U.ID=P.Utente_ID
LEFT JOIN emails E ON E.Id=NA.Email_Id
WHERE NA.Elaboration_List_Id = ".$Elaboration_List_Id." AND E.Sending_Date is null";

if ($banca)
{
    $query = "SELECT PG.ID as PignoID,
            NA.ID, PG.Partita_ID, PG.ID_Cronologico, PG.Anno_Cronologico, PG.CC, NA.SignedPdfFlag, PG.DocumentTypeId, PG.Elaboration_Id,
            DT.Description AS DocumentType, DT.PrefixName, DT.FolderName, U.PEC, E.Sending_Date AS Email_Sending_Date
            FROM notifica_atto as NA
            JOIN pignoramento_generale as PG on NA.Atto_Notificato_ID=PG.ID
            JOIN partita_tributi P ON P.ID=PG.Partita_ID
            JOIN document_type DT ON DT.Id=PG.DocumentTypeId
            JOIN utente U ON U.ID=P.Utente_ID
            LEFT JOIN emails E ON E.Id=NA.Email_Id
            WHERE NA.Elaboration_List_Id = $Elaboration_List_Id 
            AND NA.Tipo_Notifica = 'debitore'
            AND E.Sending_Date is null
            union
            SELECT PG.ID as PignoID,
            NA.ID, PG.Partita_ID, PG.ID_Cronologico, PG.Anno_Cronologico, PG.CC, NA.SignedPdfFlag, PG.DocumentTypeId, PG.Elaboration_Id,
            DT.Description AS DocumentType, DT.PrefixName, DT.FolderName, U.PEC, E.Sending_Date AS Email_Sending_Date
            FROM notifica_atto as NA
            JOIN pignoramento_generale as PG on NA.Atto_Notificato_ID=PG.ID
            JOIN partita_tributi P ON P.ID=PG.Partita_ID
            JOIN document_type DT ON DT.Id=PG.DocumentTypeId
            JOIN banca U ON U.ID=NA.Utente_ID
            LEFT JOIN emails E ON E.Id=NA.Email_Id
            WHERE NA.Elaboration_List_Id = $Elaboration_List_Id 
            AND NA.Tipo_Notifica = 'banca'
            AND E.Sending_Date is null
    ";
}
$a_notifiche_atti = $cls_db->getResults($cls_db->ExecuteQuery($query));

if (count($a_notifiche_atti) == 0){
    $msg = "Invio bloccato. Dati assenti!";
    $error = 1;
    $extraGet = "&error=".$error."&msg=".$msg;
    flush();ob_flush();flush();ob_flush();
    echo "<script>endBar('Spedizione PEC interrotta!','".$extraGet."');</script>";
    flush();ob_flush();flush();ob_flush();
    die;
}
    
$a_emailsDT = $cls_db->getColumnDataTypes("emails");
$query = "  SELECT * FROM user_emails WHERE MailType = 'PEC' AND User_Id= " . $_SESSION['aut_progr'];

$a_sender = $db->getArrayLine($db->ExecuteQuery($query));
if (empty($a_sender)) {
    $msg = "Invio bloccato. Parametri email mittente " . $_SESSION['username'] . " assenti. Contattare l'IT";
    $error = 1;
    $extraGet = "&error=".$error."&msg=".$msg;
    flush();ob_flush();flush();ob_flush();
    echo "<script>endBar('Spedizione PEC interrotta!','".$extraGet."');</script>";
    flush();ob_flush();flush();ob_flush();
    die;
}

$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM text_parameters WHERE CC='".$a_notifiche_atti[0]['CC']."' AND Form_Type_ID=41"));
if (empty($a_text)) {
    $msg = "Invio bloccato. Clausola di riservatezza assente. Salvare testo personalizzato per il comune.";
    $error = 1;
    $extraGet = "&error=".$error."&msg=".$msg;
    flush();ob_flush();flush();ob_flush();
    echo "<script>endBar('Spedizione PEC interrotta!','".$extraGet."');</script>";
    flush();ob_flush();flush();ob_flush();
    die;
}
$cls_text->setHtmlBody($a_text['Content']);


$actsNumber = count($a_notifiche_atti);
$checkFailedSending = 0;
$contPec = 1;

foreach ($a_notifiche_atti as $a_notifica_atto) {

    
    flush();ob_flush();flush();ob_flush();
    echo "<script>updateBar('".ceil($contPec*100/count($a_notifiche_atti))."',".$contPec.",".count($a_notifiche_atti).");</script>";
    flush();ob_flush();flush();ob_flush();
    $contPec++;

    $a_params['subject'] = $a_notifica_atto['DocumentType']." n.".$a_notifica_atto['ID_Cronologico'] ." ".$a_notifica_atto['Anno_Cronologico']." ". $a_notifica_atto['CC'] ."_N".$a_notifica_atto['ID'];
    $a_params['body'] = $cls_text->html_body;
    
    $cls_mail = new cls_phpmailer($a_sender);
    $cls_mail->SMTPDebug = 0; //2 debug
    $cls_mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $cls_mail->mailCreation($a_params);
    $cls_mail->setFrom($a_sender['Address']);
    $cls_mail->isHTML(true);
    $cls_mail->addAddress($a_notifica_atto['PEC']); 

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
        $path_completo_Relata_Signed = PIGNORAMENTI."/".$path.$filename_Relata_Signed;
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

   
    //relata firmata + Copia
    $a_result = $crea_file_name($a_notifica_atto);

    $file = $a_result["PathCompleto_Relata_Signed"];
    $cls_mail->addAttachment($file);
    $file_copia = $a_result["PathCompleto"];
    $cls_mail->addAttachment($file_copia);
    
    if (!$cls_mail->send()) {
        if($checkFailedSending==0)
            $checkFailedSending = 1;
        var_dump($cls_mail->ErrorInfo);
    } else {
        $a_email = array(
            "CC" => $a_notifica_atto['CC'],
            "Type"=>"pec",
            "Sending_Date" => date("Y-m-d"),
            "Sender" => $a_sender['Address'],
            "Recipient" => $a_notifica_atto['PEC'],
            "Subject" => $a_params['subject'],
            "Body" => htmlspecialchars($a_params['body'])
        );
    
        $emailId = $cls_db->DbSave($cls_db->GetObjectQuery("emails", $a_email, $a_emailsDT));

        $query = "UPDATE notifica_atto SET Email_Id = ".$emailId." WHERE ID=".$a_notifica_atto['ID'];
        $cls_db->ExecuteQuery($query);

    }
}

if($checkFailedSending==0){
    $query = "UPDATE elaboration_lists SET SendingPecFlag=1 WHERE ID=".$Elaboration_List_Id;
    $cls_db->ExecuteQuery($query);
    $msg = "INVIO COMPLETO";
    $error = 0;
}
else{
    $msg = "INVIO INCOMPLETO";
    $error = 1;
}

if($error == 0){
    $storico_query_1 = "SELECT * FROM elaborations WHERE Id = ".$Elaboration_Id;
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
    
    $storico->insRow('E', "Inviate PEC elemento ".$cls_help->getVar('Elaboration_List_Id')." elaborazione '".$elab['Description']."': ".$atto_." ".$ente['Denominazione']."[".$elab['CC']."]");
}


$extraGet = "&error=".$error."&msg=".$msg;
echo "<script>endBar('Spedizione PEC terminata!','".$extraGet."');</script>";



    

