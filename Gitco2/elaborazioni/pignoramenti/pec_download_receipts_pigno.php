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
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$terzo = $cls_help->getVar('terzo');
$lavoro= false; $banca=false;
if($terzo=="lavoro") { $lavoro = true;}
if($terzo=="banca") { $banca = true;}

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


$query = "SELECT E.*, NA.ID AS Notifica_Atto_ID, PG.ID_Cronologico, 
PG.Anno_Cronologico, PG.Elaboration_Id,EL.PrintDate as Data_Stampa ,
DT.Description AS DocumentType, DT.PrefixName, DT.FolderName FROM emails E 
JOIN notifica_atto NA ON NA.Email_Id=E.Id JOIN pignoramento_generale as PG 
on NA.Atto_Notificato_ID = PG.ID JOIN document_type DT ON DT.Id=PG.DocumentTypeId 
JOIN elaboration_lists as EL 
on EL.ID = NA.Elaboration_List_Id 
WHERE NA.Elaboration_List_Id=".$Elaboration_List_Id." AND E.Delivery_Receipt is null";

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

    $baseFilename = $a_email['PrefixName']."_".$a_email['CC']."_".$a_email['Anno_Cronologico']."_".$a_email['ID_Cronologico']."_".$a_email['Notifica_Atto_ID'];
    
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
                        $query = "UPDATE notifica_atto SET Data_Notifica='".$cls_imap->a_receipt['date']."' WHERE ID=".$a_email['Notifica_Atto_ID'];
                    else if($cls_imap->a_receipt['status']==2)    
                        $query = "UPDATE notifica_atto SET Motivo_Notifica=65 WHERE ID=".$a_email['Notifica_Atto_ID'];
                    $cls_db->ExecuteQuery($query);     
                }
            }
        }

    }

}
$cls_imap->delete();
$cls_imap->close();

$query = "SELECT COUNT(E.Id) AS MissingReceiptNumber
FROM emails E JOIN notifica_atto NA ON NA.Email_Id=E.Id 
WHERE NA.Elaboration_List_Id=".$Elaboration_List_Id." AND E.Delivery_Receipt is null";
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

if($error == 0){
    $storico_query_1 = "SELECT * FROM elaborations WHERE Id = ".$Elaboration_Id;
    $elab = $cls_db->getArrayLine($cls_db->ExecuteQuery($storico_query_1));
    $storico_query_2 = "SELECT * FROM document_type WHERE Id = ".$elab['Document_Type_Id'];
    $tipo_doc = $cls_db->getArrayLine($cls_db->ExecuteQuery($storico_query_2));
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
    
    $storico->insRow('E', "Scaricate ricevute PEC elemento ".$cls_help->getVar('Elaboration_List_Id')." elaborazione '".$elab['Description']."': ".$atto_." ".$ente['Denominazione']."[".$elab['CC']."]");
}
    

$extraGet = "&error=".$error."&msg=".$msg;
echo "<script>fine('Controllo PEC effettuato!','".$extraGet."');</script>";

?>

<?php include(INC."/footer.php"); ?>
