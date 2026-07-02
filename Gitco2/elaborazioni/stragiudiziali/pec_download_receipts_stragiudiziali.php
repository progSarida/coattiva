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
include_once ELAB_STRAGIUDIZIALI . "/cls/cls_RicezionePEC.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$proc_id = $cls_help->getVar('proc');
$tipo = $cls_help->getVar('tipo');



$error = 0;
$msg = "Mail elaborate correttamente";
?>


<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Controllo ricevute PEC</span>
    </div>
</div>



<?php
include_once "bar.php";

set_time_limit(100);


flush();	ob_flush();		flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();

$callbackProgress = function($contPec,$totale)
{
    flush();ob_flush();flush();ob_flush();
    echo "<script>updateBar('".ceil($contPec*100/$totale)."',".$contPec.",".$totale.");</script>";
    flush();ob_flush();flush();ob_flush();
};

$callbackControllo = function($msg,$error){

    $print = function ($msg)
    {
        flush();ob_flush();flush();ob_flush();
        echo "<script>endBarMsg('PEC:','".$msg."');</script>";
        flush();ob_flush();flush();ob_flush();
    };

    if($error<100) $print($msg) ;
    if($error>=100) $print("<script>noResultsBar();</script>") ;
    if($error>=10)die;
};

$ricezionePEC = new RicezionePEC($cls_db);
$ricezionePEC->CC = $c;
$ricezionePEC->proc_id = $proc_id;
$ricezionePEC->tipo = $tipo;
$ricezionePEC->callbackUpdateBar = $callbackProgress;
$ricezionePEC->callbackControllo = $callbackControllo;

$ricezionePEC
    ->PrendiDatiMails()
    ->ControlloPresenzaMail()
    ->ControlloInbox()
    ->IMap()
    ->CiclaEmail()
    ->Fine()
    ->CheckScaricoRicevute();

    

$query_pro = "SELECT * FROM procedures WHERE Id = '" . $proc_id . "'" ;
$pro = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_pro));
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$pro['CC']."'") );

$tipo_ = "";
if($tipo == 'Banca')
    $tipo_ = "su banche";
else   
    $tipo_ = "su enti previdenziali";

$storico->insRow('E', "Scaricate ricevute PEC elaborazione '".$pro['Description']."': Procedure stragiudiziali ".$tipo_." ".$ente['Denominazione']."[".$pro['CC']."]");

$status = 40;
include "update_proc_status.php";

$extraGet = "&error=".$error."&msg=".$msg;
echo "<script>endBar('".$c."','".$a."',".$proc_id.",'".$tipo."');</script>";


?>

<?php include(INC."/footer.php"); ?>
