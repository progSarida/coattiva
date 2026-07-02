<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include(INC . "/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_LOG.php";
include_once ELAB_PIGNORAMENTI . "/cls_PignoramentoNotificaAtto.php";
include_once ELAB_PIGNORAMENTI_LAVORO_CLS . "/cls_DefaultTipoUfficiale.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();
?>

<!-- JS SWEETALERT  START --> . "


<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<!-- JS sweetalert    END -->
<!-- JS PROGRESS BAR  START -->

<script>
    function startBar() {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text("Inizio elaborazione...");
    }

    function updateBar(valore) {
        $("#progressbar").progressbar({
            value: parseInt(valore)
        });
        $("#barlabel").text(valore + "%");
    }

    function noResultsBar() {
        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text("Nessun risultato trovato");
    }

    function endBar(c,a,el){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Elaborazione terminata!");

       if(el !== null){
        swal({
                        title: 'ATTENZIONE',
                        text: "PROCESSO TERMINATO. STAI PER ESSERE REINDIRIZZATO ALLA PAGINA DEI RISULTATI OTTENUTI",
                        icon: 'success',
                        timer: 3000,
                        buttons: false
                    }).then((result) => {
        
        location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c="+c+"&a="+a+"&el="+el;
    })
       }else{
                
                swal({
                        title: 'ATTENZIONE',
                        text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                        icon: 'warning',
                        timer: 3000,
                        buttons: false
                    }).then((result) => {
                    
                        location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c="+c+"&a="+a+"&el="+el;
                    })
            }
    }
    
</script>
<!-- HTML PROGRESS BAR  START -->

<body class="sfondo_new_gitco">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbar" style="height:55px;width:100%;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </div>
    </div>
</body>
<!-- HTML PROGRESS BAR    END -->
<!-- JS PROGRESS BAR    END -->
<?php
/** PHP PROGRESS BAR  START  */
flush();
ob_flush();
echo "<script>startBar();</script>";
flush();
ob_flush();
flush();
ob_flush();

/** PHP PROGRESS BAR    END  */

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$last_el_id = $cls_help->getVar('el');

$query_elaborations =   " SELECT  E.*,  DT.Description AS DocumentType " .
                        " FROM elaborations AS E " .
                        " JOIN document_type DT ON DT.Id = E.Document_Type_Id " .
                        " WHERE E.Id=" . $last_el_id;

$a_elaboration = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_elaborations));


if (is_null($a_elaboration)) {
?>

    <script>
        swal({
                title: 'ERRORE',
                text: "MANCANZA DI ELABORAZIONI",
                icon: 'danger',
                timer: 5000,
                buttons: false
        }).then((result) => {
            location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c="+c+"&a="+a+"&el="+el;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}



// QUERY PARAMETRI ANNUALI

$query_par_y =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $a_elaboration['CC'] . "' AND Anno=" . date('Y');

$log->info( $query_par_y);

$params_arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par_y));

if (is_null($params_arr)) {
   
?>
    <script>
        swal({
            title: 'ERRORE',
            text: "MANCANZA PARAMETRI ANNUALI ",
            icon: 'danger',
            timer: 5000,
            buttons: false
        }).then((result) => {
            location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c="+c+"&a="+a+"&el="+el;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}

    /** INIZIO TRANSAZIONE **/

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    try {

        //creazione atti di notifica
        //
        $a_tipo_ufficiale = $cls_db->getArrayLine($cls_db->ExecuteQuery(DefaultTipoUfficiale::ReadQuery($last_el_id)));
        $attinotifica = new cls_PignoramentoNotificaAtto($cls_db);
        $attinotifica->a_TipoUfficiale =$a_tipo_ufficiale; 
        $attinotifica($params_arr,$a_elaboration["CC"],$a_elaboration['Id']);

         // UPDATE ELABORATIONS

         $a_dbParams = array(
            'table' => 'elaborations',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $a_elaboration['Id']),
            ),
            'fields'=> array(
                array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => ElaborationStatus::RICHIESTA_INIPEC),
                array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
                
            )
        );

        $cls_db->DbSave( $a_dbParams);

        
        flush(); ob_flush();flush();ob_flush();
        echo "<script>updateBar(" . ceil(100) . ");</script>";
        flush(); ob_flush();flush();ob_flush();      

    } catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
        $cls_help->alert("ERRORE!!!!!!!!");
        flush();
        ob_flush();
        echo "<script>endBar('".$c."','".$a."',".$last_el_id.");</script>";
        flush();
        ob_flush();
        flush();
        ob_flush();
        die;
        return;
    }

    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$a_elaboration['CC']."'") );

    $storico->insRow('E', "Elaborazione '".$a_elaboration['Description']."': Preavvisi fermi amministrativi ".$ente['Denominazione']."[".$a_elaboration['CC']."]. Stato 'Richiesta INIPEC'");


    $cls_db->End_Transaction();


    flush();
    ob_flush();
    echo "<script>endBar('".$c."','".$a."',".$last_el_id.");</script>";
    flush();
    ob_flush();
    flush();
    ob_flush(); 




?>