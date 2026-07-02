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

$db = new cls_db();
$help = new cls_help();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

?>


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


    
$a_emailsDT = $cls_db->getColumnDataTypes("emails");
$query = "  SELECT * FROM user_emails WHERE MailType = 'PEC' AND User_Id= " . $_SESSION['aut_progr'];
$a_sender = $db->getArrayLine($db->ExecuteQuery($query));
if (empty($a_sender)) {
    $msg = "Invio bloccato. Parametri email mittente " . $_SESSION['username'] . " assenti. Contattare l'IT";
    $error = 1;
    $extraGet = "&error=".$error."&msg=".$msg;

    echo $msg;
    die;
}

    $a_params['subject'] = "PEC DI PROVA COATTIVA";
    $a_params['body'] = "PROVA PEC IRTEL";
    
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
    $cls_mail->addAddress($a_sender['Address']); 

    // $act_pathFolder = ATTI."/".$a_act['CC']."/".$a_act['FolderName']."/STAMPE DEFINITIVE";
    // $base_name = $a_act['PrefixName']."_".$a_act['CC']."_".$a_act['Anno_Cronologico']."_".$a_act['ID_Cronologico']."_".$a_act['Data_Stampa'];
    // $file = $act_pathFolder."/".$base_name."_signed.pdf";

    // $cls_mail->addAttachment($file);

    if (!$cls_mail->send()) {
        // if($checkFailedSending==0)
        //     $checkFailedSending = 1;
        var_dump($cls_mail->ErrorInfo);
    } else {
        echo "SPEDIZIONE EFFETTUATA CON SUCCESSO";

    }



    

