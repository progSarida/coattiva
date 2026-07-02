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
include_once ELAB_STRAGIUDIZIALI . "/cls/cls_InvioPEC.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$db = new cls_db();
$help = new cls_help();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$proc_id = $cls_help->getVar('proc_id');
$tipo = $cls_help->getVar('tipo');




?>
<script>
    var c = '<?= $c?>';
    var a = <?= $a?>;
    var pr = <?= $proc_id?>;
    var tipo = '<?= $tipo?>';

</script>




<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Spedizione PEC</span>
    </div>
</div> 

<?php
include_once "bar.php";
set_time_limit(-1);


flush();	ob_flush();		flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();

$callback = function($msg)
{
    flush();ob_flush();flush();ob_flush();
    echo "<script>endBarErr('Spedizione PEC interrotta!','".$msg."');</script>";
    flush();ob_flush();flush();ob_flush();
    die;
};

$callbackProgress = function($contPec,$totale)
{
    flush();ob_flush();flush();ob_flush();
    echo "<script>updateBar('".ceil($contPec*100/$totale)."',".$contPec.",".$totale.");</script>";
    flush();ob_flush();flush();ob_flush();
};

$invioPEC = new InvioPEC($cls_db);
$invioPEC->CC = $c;
$invioPEC->proc_id = $proc_id;
$invioPEC->tipo = $tipo;
$invioPEC->callbackControllo = $callback;
$invioPEC->callbackUpdateBar = $callbackProgress;

$errori = $invioPEC
    ->PrendiDati()
    ->ControlloPresenzaDati()
    ->ControlloEmailMittente()
    ->ControllaClausolaRiservatezza()
    ->CiclaMail()
    ->Errori();

    

if ($errori["flag"]==0)
{
    
    $msg="OK!";

    $query_pro = "SELECT * FROM procedures WHERE Id = '" . $proc_id . "'" ;
    $pro = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_pro));
    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$pro['CC']."'") );

    $tipo_ = "";
    if($tipo == 'Banca')
        $tipo_ = "su banche";
    else   
        $tipo_ = "su enti previdenziali";

    $storico->insRow('E', "Inviate PEC elaborazione '".$pro['Description']."': Procedure stragiudiziali ".$tipo_." ".$ente['Denominazione']."[".$pro['CC']."]");
   
    $status = 30;
    include "update_proc_status.php";
    
}

else
{
    $lista = $errori["lista"];
    $msg = "Errori\n";
    foreach ($lista as $item)
    {
        $msg.=$item."\n";
    }
}


echo "<script>endBar('".$c."','".$a."',".$proc_id.",'".$tipo."');</script>";



    

