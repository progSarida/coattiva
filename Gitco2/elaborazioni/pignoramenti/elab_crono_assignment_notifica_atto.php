<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include(INC . "/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_elaboration.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_db = new cls_db();
$cls_help = new cls_help();

$terzo = $cls_help->getVar('terzo');
$lavoro= false; $banca=false;
if($terzo=="lavoro") { $lavoro = true;}
if($terzo=="banca") { $banca = true;}

?>

<!-- JS SWEETALERT  START -->

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

        <?php if($lavoro)
        {
            ?>
            location.href ="<?= ELAB_PIGNORAMENTI_LAVORO_WEB ?>/mgmt_pignoramenti_lavoro.php?c="+c+"&a="+a+"&el="+el;
            <?php
        }
        else if($banca)
        {
            ?>
            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
			<?php
        }
        else
        {
            ?>
            location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c="+c+"&a="+a+"&el="+el;
			<?php
        }
        ?>
    })
       }else{
                
                swal({
                        title: 'ATTENZIONE',
                        text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                        icon: 'warning',
                        timer: 3000,
                        buttons: false
                    }).then((result) => {
                    
                        <?php if($lavoro)
                        {
                            ?>
                            location.href ="<?= ELAB_PIGNORAMENTI_LAVORO_WEB ?>/mgmt_pignoramenti_lavoro.php?c="+c+"&a="+a+"&el="+el;
                            <?php
                        }
                        else if($banca)
                        {
                            ?>
                            window.opener.location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
                            <?php
                        }
                        else
                        {
                            ?>
                            location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c="+c+"&a="+a+"&el="+el;
                            <?php
                        }
                        ?>
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
$p = $cls_help->getVar('p');

$last_el_id = $cls_help->getVar('el');
$cod_cat = $cls_help->getVar('cod_cat');
$tipo_atto = $cls_help->getVar('tipo_atto'); //no usato


$size = 400;

$query_notifica_atto =   " SELECT ".
" NA.ID AS notifica_atto_ID, ".
" PG.Partita_ID, ".
" PG.DocumentTypeId, ".
" PG.ID_Cronologico, ".
" PG.Anno_Cronologico, ".
" NA.Printer_Id as PrinterId, ".
" NA.PrintTypeId, ".
" NA.Tipo_Notifica, ".
" NA.Tipo_Ufficiale, ".
" pt.ID, ".
" pt.Tipo, ".
" tt.Id AS Id_Tax ".
" FROM notifica_atto as NA ".
" JOIN pignoramento_generale as PG ON PG.ID = NA.Atto_Notificato_ID".
" JOIN partita_tributi as pt on pt.ID = PG.Partita_ID ".
" JOIN tax_type as tt on tt.Name = pt.Tipo ".
" WHERE PG.Elaboration_Id =  ".$last_el_id." ".
" ORDER BY Tipo DESC, PrinterId DESC, PrintTypeId DESC, Tipo_Ufficiale DESC";

$results = $cls_db->ExecuteQuery($query_notifica_atto);
$notifica_atti = $cls_db->getResults($results);

if (count($notifica_atti) > 0) {

    $countAllResult = count($notifica_atti);
   

    $tempTipoPartita = null;
    $tempPrintTypeId = null;
    $tempPrinterId = null;
    $tempNotificationType = null;
    $last_el_list_id = 0;

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    try { 
        
         // UPDATE ELABORATIONS

         $a_dbParams_elab = array(
            'table' => 'elaborations',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $last_el_id),
            ),
            'fields'=> array(
                array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => ElaborationStatus::ASSEGNATI_DATI),
                array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
            
            )
        );

        $cls_db->DbSave($a_dbParams_elab);

    $countActs = 0;
    $contList = 0;
    foreach ($notifica_atti as $key => $notifica_atto) {
         
        flush();
        ob_flush();
        flush();
        ob_flush();
        echo "<script>updateBar(" . ceil($key * 100 / $countAllResult) . ");</script>";
        flush();
        ob_flush();
        flush();
        ob_flush();
   
            
            if($notifica_atto['Id_Tax'] != $tempTipoPartita || $notifica_atto['PrinterId'] != $tempPrinterId || $notifica_atto['PrintTypeId'] != $tempPrintTypeId
                || $notifica_atto['Tipo_Ufficiale'] !=  $tempNotificationType
                //|| $notifica_atto['Tipo_Notifica'] !=  $tempNotificationTypeDebitore
                 || $countActs>$size )
            {
                // INSERT ELABORATION_LISTS
                $countActs = 0;
            
                $a_dbParams_elab_lists = array(
                    'table' => 'elaboration_lists',
                    'fields'=> array(
                        array(  'name' => 'Elaboration_Id',      'type' => 'int', 'value' => $last_el_id),
                        array(  'name' => 'Elaboration_Status_Id',      'type' => 'int', 'value' => ElaborationStatus::ASSEGNATI_DATI),
                        array(  'name' => 'DocumentTypeId',      'type' => 'int', 'value' => $notifica_atto['DocumentTypeId']),
                        array(  'name' => 'TaxTypeId',           'type' => 'int', 'value' =>$notifica_atto['Id_Tax']),
                        array(  'name' => 'PrinterId',           'type' => 'int', 'value' =>$notifica_atto['PrinterId']),
                        array(  'name' => 'PrintTypeId ',        'type' => 'int', 'value' =>$notifica_atto['PrintTypeId']),
                        array(  'name' => 'NotificationType',    'type' => 'string', 'value'=> $notifica_atto['Tipo_Ufficiale']),
                        array(  'name' => 'CreationDate',     	 'type' => 'date', 'value' => date('Y-m-d'))

                    )
                );

                $tempTipoPartita = $notifica_atto['Id_Tax'];
                $tempPrintTypeId = $notifica_atto['PrintTypeId'];
                $tempPrinterId =  $notifica_atto['PrinterId'];
                $tempNotificationType = $notifica_atto['Tipo_Ufficiale'];
                $tempNotificationTypeDebitore = $notifica_atto['Tipo_Notifica'];

                $last_el_list_id = $cls_db->DbInsert($a_dbParams_elab_lists);
                $contList++;
           }

            // UPDATE ATTO

            $query_up_par = " UPDATE notifica_atto SET 
             Elaboration_List_Id = ".$last_el_list_id."  WHERE ID =  " . $notifica_atto['notifica_atto_ID'] ;
            $countActs++;
            mysqli_query($cls_db->conn, $query_up_par);
               
              

        /** PHP PROGRESS BAR    END  */
    } //END FOREACH

    $cls_db->ExecuteQuery("UPDATE elaborations SET ListNumber=".$contList." WHERE Id=".$last_el_id);

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

$atto_ = "";
$query_el = "SELECT * FROM `elaborations` WHERE Id = ".$last_el_id;
$elab = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_el));
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$cod_cat."'") );
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

$cls_db->End_Transaction();

$storico->insRow('E', "Elaborato '".$elab['Description']."': ".$atto_." ".$ente['Denominazione']."[".$cod_cat."]. Stato 'Assegnati dati stampa e cronologico'");

flush();
ob_flush();
echo "<script>endBar('".$c."','".$a."',".$last_el_id.");</script>";
flush();
ob_flush();
flush();
ob_flush();

} else { // END  } //END FOREACH 
  flush();
    ob_flush();
    echo "<script>endBar('".$c."','".$a."',".$last_el_id.");</script>";
    flush();
    ob_flush();
    flush();
    ob_flush(); 
    /** PHP PROGRESS BAR    END  */
}


?>