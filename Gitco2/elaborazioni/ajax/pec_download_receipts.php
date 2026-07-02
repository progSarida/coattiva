<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once INC . "/header.php";

?>
<script>
    $('#cityAdminHeader').hide();
</script>
<?php


include_once(INC . "/header.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_crypt.php");
include_once(CLS."/php-imap-client-master/Imap.php");
include_once(CLS."/cls_imap.php");

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$Elaboration_List_Id = $cls_help->getVar('Elaboration_List_Id');
$Elaboration_Id = $cls_help->getVar('Elaboration_Id');

$error = 0;
$msg = "Mail elaborate correttamente";
?>

    <script>
        function inizio()
        {
            $('#progressbar').progressbar({
                value: false
            });
            $( "#barlabel" ).text("Inizio controllo...");
        }

        function anomalie()
        {
            $('#progressbar').progressbar({
                value: false
            });
            $( "#barlabel" ).text("Ricerca anomalie...");
        }

        function update(valore)
        {
            $( "#progressbar" ).progressbar({value: parseInt(valore) });
            $( "#barlabel" ).text( valore + "%" );
        }

        function nessun_risultato()
        {
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text("Nessun risultato trovato");
        }

        function fine(value, extraGet)
        {
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text( value );

            sleep(1000);
            location.href ="<?= WEB_ROOT ?>/elaborazioni/mgmt_elaboration.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$Elaboration_Id;?>"+extraGet;
        }

        function gestione_email()
        {
            $('#pec_form').submit();
        }

    </script>

<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Controllo ricevute PEC</span>
    </div>
</div>
<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>

<?php

set_time_limit(100);


flush();	ob_flush();		flush();	ob_flush();
echo "<script>inizio();</script>";
flush();	ob_flush();		flush();	ob_flush();


$query = "SELECT E.*, A.ID AS Atto_ID, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Stampa, A.Elaboration_Id,
DT.Description AS DocumentType, DT.PrefixName, DT.FolderName
FROM emails E JOIN atto A ON A.Email_Id=E.Id 
JOIN document_type DT ON DT.Id=A.DocumentTypeId
WHERE A.Elaboration_List_Id=".$Elaboration_List_Id." AND E.Delivery_Receipt is null";
$a_emails = $cls_db->getResults($cls_db->ExecuteQuery($query));
if(count($a_emails) == 0){
    echo "<script>nessun_risultato();</script>";
    $error = 2;
    $msg = "Nessun risultato trovato";

    die;
}

$query = "  SELECT * FROM user_emails WHERE MailType = 'PEC' AND User_Id= " . $_SESSION['aut_progr'];
$a_inbox = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if (empty($a_inbox)) {
    $msg = "Parametri email " . $_SESSION['username'] . " assenti. Contattare l'IT";
    die;
}

try {
    $cls_imap = new cls_imap($a_inbox,'INBOX');
    $cls_imap->setPecPath($a_emails[0]['CC']);

    if ($cls_imap->isConnected() === false) {
        $error = 1;
        $msg = "Errore di connessione reader";
        echo $msg;
        die;
    }
}
catch(Exception $ex)
{
    $error = 1;
    $msg = "READER: ".$ex->getMessage();
    echo $msg;
}

foreach($a_emails as $x=>$a_email){

    set_time_limit(-1);

    flush();ob_flush();flush();ob_flush();
    echo "<script>update('".ceil($x*100/count($a_emails))."');</script>";
    flush();ob_flush();flush();ob_flush();

    $baseFilename = $a_email['PrefixName']."_".$a_email['CC']."_".$a_email['Anno_Cronologico']."_".$a_email['ID_Cronologico']."_".$a_email['Data_Stampa'];
    $checkMail = $cls_imap->inboxSelected('SUBJECT "'.$a_email['Subject'].'"');
    if(!$checkMail)
        continue;
    for ($i = 0; $i < $cls_imap->getEmailNumRows(); $i++) {

        $cls_imap->checkPecReceipt($i);
        $cls_imap->savePecReceipt($i, $baseFilename, true);

        if(!is_null($cls_imap->a_receipt['status'])){
            if(is_file($cls_imap->a_receipt['file'])){
                $query = "
                UPDATE emails SET
                ".ucfirst($cls_imap->a_receipt['type'])."_Receipt=".$cls_imap->a_receipt['status'].",
                ".ucfirst($cls_imap->a_receipt['type'])."_Datetime='".$cls_imap->a_receipt['date']."'
                WHERE Id=".$a_email['Id'];
                $cls_db->ExecuteQuery($query);
                if($cls_imap->a_receipt['type']=="delivery"){
                    if($cls_imap->a_receipt['status']==1)
                        $query = "UPDATE atto SET Data_Notifica='".$cls_imap->a_receipt['date']."' WHERE ID=".$a_email['Atto_ID'];
                    else if($cls_imap->a_receipt['status']==2)    
                        $query = "UPDATE atto SET Motivo_Notifica=65 WHERE ID=".$a_email['Atto_ID'];
                    $cls_db->ExecuteQuery($query);     
                }
            }
        }

    }

}
$cls_imap->delete();
$cls_imap->close();

$query = "SELECT COUNT(E.Id) AS MissingReceiptNumber
FROM emails E JOIN atto A ON A.Email_Id=E.Id 
WHERE A.Elaboration_List_Id=".$Elaboration_List_Id." AND E.Delivery_Receipt is null";
$a_count = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if($a_count['MissingReceiptNumber']==0){
    $query = "UPDATE elaboration_lists  SET PecReceiptsFlag=1 WHERE Id=".$Elaboration_List_Id;
    $cls_db->ExecuteQuery($query);     
    $msg = "Scarico ricevute Pec completo!";
    $error = 0;
}
else{
    $msg = "Scarico ricevute Pec effettuato! ".$a_count['MissingReceiptNumber']." pec ancora da verificare. Riprovare più tardi!";
    $error = 2;
}
    

$extraGet = "&error=".$error."&msg=".$msg;
echo "<script>fine('Controllo PEC effettuato!','".$extraGet."');</script>";

?>

<?php include(INC."/footer.php"); ?>
