<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/headerAjax.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include CLS . "/XLSReader/src/SimpleXLSX.php";

use Shuchkin\SimpleXLSX;


$cls_db = new cls_db();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$error = 0;
$msg = "Dati inseriti correttamente!";

?>
<script>

    function startBar(){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function updateBar(valore){
        //alert(valore);
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function noResultsBar(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function endBar(value){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( value );

    }

</script>
<?php

if (isset($_FILES['file'])) {

    if ( $xlsx = SimpleXLSX::parse($_FILES['file']['tmp_name']) ) {

        $count = 0;
        $row = $xlsx->rows();
        $total = count($row);
        foreach ($row as $r) {
            if($count > 0) {

                echo "<script>updateBar('".(100*$count/$total)."');</script>";

                $query = "SELECT Info_Cartella, Data_Decorrenza_Interessi FROM `tributo` where Partita_ID = ".$r[2];
                $resultInfo = $cls_db->getResults($cls_db->ExecuteQuery($query));
                $info = $dataInt = null;

                if(count($resultInfo)>0) {
                    $info = $resultInfo[0]["Info_Cartella"];
                    $dataInt = $resultInfo[0]["Data_Decorrenza_Interessi"];
                }

                $save = new stdClass();
                $save->ID = $r[0];
                $save->DocumentTypeId = $r[21];
                $save->Comune_ID = null;
                $save->CC = $r[19];
                $save->Partita_ID = $r[2];
                $save->Anno_Cronologico = $r[4];
                $save->ID_Cronologico = $r[3];
                $save->Atto = $r[22];
                $save->Info_Cartella = $info;
                $save->Data_Elaborazione = $r[16];
                $save->Data_Calcolo_Interessi = $r[16];
                $save->Data_Stampa = $r[16];
                $save->Stato_Stampa = "Stampato";
                $save->Data_Flusso = $r[16];
                $save->Numero_Flusso = $r[3];
                $save->Anno_Flusso = $r[4];
                $save->FlowId = $r[1];
                $save->PrintTypeId = $r[15];
                $save->PrinterId = $r[14];
                $save->Atto_Rettificato = 0;
                $save->Spese_Precedenti = $r[12]!=""?$r[12]:0.00;
                $save->Data_Decorrenza_Interessi = $dataInt;
                $save->Interessi = $r[10];
                $save->Spese_Notifica = $r[11];
                $save->Spese_Notifica_Precedenti = $r[12];
                $save->CAN = 0.00;
                $save->CAD = 0.00;
                $save->Ulteriori_Spese = 0.00;
                $save->Interessi_Precedenti = $r[13]!=""?$r[13]:0.00;
                $save->Totale_Dovuto = $r[7];
                $save->Num_Flusso = 0;
                $save->Scatola = 0;
                $save->Lotto = 0;
                $save->Posizione = 0;
                $save->Diritto_Riscossione_Minimo = $r[8];
                $save->Diritto_Riscossione_Massimo = $r[9];
                $save->SignedPdfFlag = 0;
                //var_dump($r[12]);
                //print_r($r);
                if($cls_db->DbSave($cls_db->GetObjectQuery("atto",$save))===false){
                    $error = 1;
                    $msg = "Errore, impossibile salvare la riga dell\'atto ".$r[0];
                    break;
                }
            }
            else{
                echo "<script>startBar();</script>";
            }
            $count++;
        }
    } else {
        $error = 1;
        $msg = SimpleXLSX::parseError();
    }
}
else {
    echo "<script>noResultsBar();location.href = 'carica_excel_reimportazione_atti.php?c={$c}&a={$a}&error=2&msg=File non trovato!';</script>";
    //header("Location: carica_excel_reimportazione_atti.php?c={$c}&a={$a}&error=2&msg=File non trovato!");
}

echo "<script>endBar('100');location.href = 'carica_excel_reimportazione_atti.php?c={$c}&a={$a}&error={$error}&msg={$msg}';</script>";

//header("Location: carica_excel_reimportazione_atti.php?c={$c}&a={$a}&error={$error}&msg={$msg}");

?>



<div class="row" style="margin-top: 5%;">
    <div class="col col-lg-12">
        <p style="text-align: center;font-size: 32px;font-weight: bold;color: blue;">REIMPORTAZIONE ATTI</p>
    </div>
</div>
<div class="row" style="margin-top: 25%;">
    <div class="col-lg-12">
        <div class="table_interna text_center" id="progressbar" style="height:55px;">
            <div class="text_center" id="barlabel"></div>
        </div>
    </div>
</div>
