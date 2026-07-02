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
        location.href ="<?= ELAB_ATTI_WEB ?>/mgmt_elaboration.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$Elaboration_Id;?>"+extraGet;

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


$query = "SELECT 
A.ID, A.Partita_ID, A.ID_Cronologico, A.Anno_Cronologico, A.CC, A.Data_Stampa, A.SignedPdfFlag, A.DocumentTypeId, A.Elaboration_Id,
DT.Description AS DocumentType, DT.PrefixName, DT.FolderName, U.PEC, E.Sending_Date AS Email_Sending_Date
FROM atto as A
JOIN partita_tributi P ON P.ID=A.Partita_ID
JOIN document_type DT ON DT.Id=A.DocumentTypeId
JOIN utente U ON U.ID=P.Utente_ID
LEFT JOIN emails E ON E.Id=A.Email_Id
WHERE A.Elaboration_List_Id = ".$Elaboration_List_Id." AND E.Sending_Date is null";
$a_acts = $cls_db->getResults($cls_db->ExecuteQuery($query));

if (count($a_acts) == 0){
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
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM text_parameters WHERE CC='".$a_acts[0]['CC']."' AND Form_Type_ID=41"));
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


$actsNumber = count($a_acts);
$checkFailedSending = 0;
$contPec = 1;

foreach ($a_acts as $a_act) {

    flush();ob_flush();flush();ob_flush();
    echo "<script>updateBar('".ceil($contPec*100/count($a_acts))."',".$contPec.",".count($a_acts).");</script>";
    flush();ob_flush();flush();ob_flush();
    $contPec++;

    $a_params['subject'] = $a_act['DocumentType']." n.".$a_act['ID_Cronologico'] ." ".$a_act['Anno_Cronologico']." ". $a_act['CC'] ."_A".$a_act['ID'];
    $a_params['body'] = $cls_text->html_body;
    
    $cls_mail = new cls_phpmailer($a_sender);
    $cls_mail->SMTPDebug = 0;
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
    $cls_mail->addAddress($a_act['PEC']); 

    $act_pathFolder = ATTI."/".$a_act['CC']."/".$a_act['FolderName']."/STAMPE DEFINITIVE";
    $base_name = $a_act['PrefixName']."_".$a_act['CC']."_".$a_act['Anno_Cronologico']."_".$a_act['ID_Cronologico']."_".$a_act['Data_Stampa'];
    $file = $act_pathFolder."/".$base_name."_signed.pdf";

    $cls_mail->addAttachment($file);

    if (!$cls_mail->send()) {
        if($checkFailedSending==0)
            $checkFailedSending = 1;
        var_dump($cls_mail->ErrorInfo);
    } else {
        $a_email = array(
            "CC" => $a_act['CC'],
            "Type"=>"pec",
            "Sending_Date" => date("Y-m-d"),
            "Sender" => $a_sender['Address'],
            "Recipient" => $a_act['PEC'],
            "Subject" => $a_params['subject'],
            "Body" => htmlspecialchars($a_params['body'])
        );
    
        $emailId = $cls_db->DbSave($cls_db->GetObjectQuery("emails", $a_email, $a_emailsDT));

        $query = "UPDATE atto SET Email_Id = ".$emailId." WHERE ID=".$a_act['ID'];
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
    $storico_query_2 = "SELECT * FROM document_type WHERE Id = ".$elab['Document_Type_Id'];
    $tipo_doc = $cls_db->getArrayLine($cls_db->ExecuteQuery($storico_query_2));
    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$elab['CC']."'") );

    $atto_ = "";

    switch ($elab['Document_Type_Id']){
        case 2:
            $atto_ = "Ingiunzioni";
            break;
        case 3:
            $atto_ = "Solleciti di pagamento";
            break;
        case 4:
            $atto_ = "Avvisi d'intimazione";
            break;
        case 11:
            $atto_ = "Solleciti pre ingiunzione";
            break;
        case 12:
            $atto_ = "Avvisi di messa in mora";
            break;
        default:
            break;
    }
    
    $storico->insRow('E', "Inviate PEC elemento ".$cls_help->getVar('Elaboration_List_Id')." elaborazione '".$elab['Description']."': ".$atto_." ente ".$ente['Denominazione']."[".$elab['CC']."]");
}

$extraGet = "&error=".$error."&msg=".$msg;
echo "<script>endBar('Spedizione PEC terminata!','".$extraGet."');</script>";



    

