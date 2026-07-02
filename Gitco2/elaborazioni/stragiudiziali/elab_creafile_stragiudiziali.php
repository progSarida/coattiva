<?php

ini_set("memory_limit",'512M');
set_time_limit(-1);

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(ROOT . "/_parameter.php"); //dati database
include(INC."/header.php");
include(INC."/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_Stampe.php";

include_once ELAB_STRAGIUDIZIALI . "/cls/cls_GeneraDocumenti.php";
//include_once "bar.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

?>


<script>
    function startBarUtente() {
    $('#progressbarUtente').progressbar({
        value: false
    });
    $("#barlabelUtente").text("Excel : Inizio elaborazione...");
}

function updateBarUtente(valore) {
    $("#progressbarUtente").progressbar({
        value: parseInt(valore)
    });
    $("#barlabelUtente").text("Utenti:"+valore + "%");
}

function startBarBanca() {
    $('#progressbarBanca').progressbar({
        value: false
    });
    $("#barlabelBanca").text("Pdf : Inizio elaborazione...");
}

function updateBarBanca(valore) {
    $("#progressbarBanca").progressbar({
        value: parseInt(valore)
    });
    $("#barlabelBanca").text("Banche:"+valore + "%");
}

function endBar(c,a,el,tipo,tipo_partita=''){
    
    $( "#progressbarBanca" ).progressbar({value: 100 });
    $( "#barlabelBanca" ).text("Elaborazione terminata!");

   if(el !== null){
    swal({
                    title: 'ATTENZIONE',
                    text: "PROCESSO TERMINATO. STAI PER ESSERE REINDIRIZZATO ALLA PAGINA DEI RISULTATI OTTENUTI",
                    icon: 'success',
                    timer: 3000,
                    buttons: false
                }).then((result) => {
    location.href ="<?= ELAB_STRAGIUDIZIALI_WEB ?>/mgmt_stragiudiziali.php?c="+c+"&a="+a+"&pr="+el+"&tipo="+tipo+"&tipo_partita="+tipo_partita;
})
   }else{
            
            swal({
                    title: 'ATTENZIONE',
                    text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                    icon: 'warning',
                    timer: 3000,
                    buttons: false
                }).then((result) => {
                
                    location.href ="<?= ELAB_STRAGIUDIZIALI_WEB ?>/mgmt_stragiudiziali.php?c="+c+"&a="+a+"&proc="+el+"&tipo="+tipo+"&tipo_partita="+tipo_partita;
                })
        }
}
</script>

<!-- HTML PROGRESS BAR  START -->
<body class="sfondo_new_gitco">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbarUtente" style="height:55px;width:100%;"><div class="text_center" id="barlabelUtente"></div></div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbarBanca" style="height:55px;width:100%;"><div class="text_center" id="barlabelBanca"></div></div>
        </div>
    </div>
    <br/><br/>
    <br/><br/>
    <div class="row">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor" style="color:red;">Non chiudere la finestra prima del termine della procedura</span>
        </div> 
    </div>
</body>
<!-- HTML PROGRESS BAR    END -->  
<!-- JS PROGRESS BAR    END -->   
<?php
/** PHP PROGRESS BAR  START  */
flush();	ob_flush();
echo "<script>startBarBanca();</script>";
flush();	ob_flush();		flush();	ob_flush();

flush();	ob_flush();
echo "<script>startBarUtente();</script>";
flush();	ob_flush();		flush();	ob_flush();

/** PHP PROGRESS BAR    END  */
set_time_limit(-1);

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$proc_id = $cls_help->getVar('proc');

$tipo_partita = $cls_help->getVar('tipo_partita');
$tipo = $cls_help->getVar('tipo');



$data_elab = $cls_help->toDbDate($cls_help->getVar('data_elab'));

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

$ProgressioneBanca = function ($i,$count)
{
    flush();	ob_flush();		flush();	ob_flush();
    echo "<script>updateBarBanca(".ceil($i*100/$count).");</script>";
    flush();	ob_flush();		flush();	ob_flush();
};

$ProgressioneUtente = function ($i,$count)
{
    flush();	ob_flush();		flush();	ob_flush();
    echo "<script>updateBarUtente(".ceil($i*100/$count).");</script>";
    flush();	ob_flush();		flush();	ob_flush();
};
try 
{
    //elaborazione
    $genera_documenti = new GeneraDocumenti($cls_db);
    $genera_documenti->callbackBanca = $ProgressioneBanca;
    $genera_documenti->callbackUtente = $ProgressioneUtente;
    $genera_documenti
        ->Inizializzazione($proc_id,$tipo,$tipo_partita,$c,40)
        ->Genera("");
 
    unset($genera_documenti);
    
    
} 
catch (Exception $e) {
    $cls_db->Rollback();
    $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
    $cls_help->alert("ERRORE!!!!!!!!");
    die;
    return;
}

$status=20;
include "update_proc_status.php";

$query_pro = "SELECT * FROM procedures WHERE Id = '" . $proc_id . "'" ;
$pro = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_pro));
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$pro['CC']."'") );

$tipo_ = "";
if($tipo == 'Banca')
    $tipo_ = "su banche";
else   
    $tipo_ = "su enti previdenziali";

$storico->insRow('E', "Creati file Elaborazione ".$pro['description'].": Procedure stragiudiziali ".$tipo_." ".$ente['Denominazione']."[".$pro['CC']."]");

$cls_db->End_Transaction();

flush();	ob_flush();
echo "<script>endBar('".$c."','".$a."',".$proc_id.",'".$tipo."','".$tipo_partita."');
</script>";
flush();	ob_flush();		flush();	ob_flush();
die;