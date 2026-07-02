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
        window.close();
        window.opener.location.href ="<?= ELAB_ATTI_WEB ?>/mgmt_elaboration.php?c="+c+"&a="+a+"&el="+el;
    })
       }else{
                
                swal({
                        title: 'ATTENZIONE',
                        text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                        icon: 'warning',
                        timer: 3000,
                        buttons: false
                    }).then((result) => {
                    
                        window.close();   
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
$tipo_atto = $cls_help->getVar('tipo_atto');

// RECUPERO DATI ATTO
$a_elaboration = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT  E.*,  DT.Description AS DocumentType FROM elaborations AS E JOIN document_type DT ON DT.Id = E.Document_Type_Id WHERE E.Id=" . $last_el_id));
$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$a_elaboration['CC']."'") );

// RECUPERO ID_CRONOLOGICO

$query_id_crono =   "   SELECT Max(ID_Cronologico) AS ID_Cronologico FROM atto ".
                    "   WHERE CC = '".$cod_cat."'".
                    "       AND Anno_Cronologico = ". date('Y');   
                    
             
$results_id_crono = $cls_db->ExecuteQuery($query_id_crono);
$id_cronos = $cls_db->getArrayLine($results_id_crono);

if(is_null($id_cronos))
{
    $id_cronos['ID_Cronologico'] = 0;

}
$size = 400;

$query_atto =   "SELECT ".
                "A.ID AS ATTO_ID, ".
                "A.Partita_ID, ".
                "A.DocumentTypeId, ".
                "A.ID_Cronologico, ".
                "A.Anno_Cronologico, ".
                "A.PrinterId, ".
                "A.PrintTypeId, ".
                "A.Tipo_Ufficiale, ".
                "pt.ID, ".
                "pt.Tipo, ".
                "tt.Id AS Id_Tax ".
                "FROM atto as A ".
                "JOIN partita_tributi as pt on pt.ID = A.Partita_ID ".
                "JOIN tax_type as tt on tt.Name = pt.Tipo ".
                "WHERE A.Elaboration_Id = ".$last_el_id." AND A.archived IS NULL ".
                "ORDER BY Tipo DESC, PrinterId DESC, PrintTypeId DESC, Tipo_Ufficiale DESC";

$results = $cls_db->ExecuteQuery($query_atto);
$atti = $cls_db->getResults($results);

if (count($atti) > 0) {

    $countAllResult = count($atti);
   
    $id_cronologico = ($id_cronos['ID_Cronologico']+1);

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
                array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => 4),
                array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
            
            )
        );

        $cls_db->DbSave($a_dbParams_elab);

        $countActs = 0;
        $contList = 0;
        foreach ($atti as $key => $atto) {
            
            flush();
            ob_flush();
            flush();
            ob_flush();
            echo "<script>updateBar(" . ceil($key * 100 / $countAllResult) . ");</script>";
            flush();
            ob_flush();
            flush();
            ob_flush();
    
                
                if($atto['Id_Tax'] != $tempTipoPartita || $atto['PrinterId'] != $tempPrinterId || $atto['PrintTypeId'] != $tempPrintTypeId
                    || $atto['Tipo_Ufficiale'] !=  $tempNotificationType || $countActs>$size )
                {
                    // INSERT ELABORATION_LISTS
                    $countActs = 0;
                
                    $a_dbParams_elab_lists = array(
                        'table' => 'elaboration_lists',
                        'fields'=> array(
                            array(  'name' => 'Elaboration_Id',      'type' => 'int', 'value' => $last_el_id),
                            array(  'name' => 'Elaboration_Status_Id',      'type' => 'int', 'value' => 4),
                            array(  'name' => 'DocumentTypeId',      'type' => 'int', 'value' => $atto['DocumentTypeId']),
                            array(  'name' => 'TaxTypeId',           'type' => 'int', 'value' =>$atto['Id_Tax']),
                            array(  'name' => 'PrinterId',           'type' => 'int', 'value' =>$atto['PrinterId']),
                            array(  'name' => 'PrintTypeId ',        'type' => 'int', 'value' =>$atto['PrintTypeId']),
                            array(  'name' => 'NotificationType',    'type' => 'string', 'value'=> $atto['Tipo_Ufficiale']),
                            array(  'name' => 'CreationDate',     	 'type' => 'date', 'value' => date('Y-m-d'))

                        )
                    );

                    $tempTipoPartita = $atto['Id_Tax'];
                    $tempPrintTypeId = $atto['PrintTypeId'];
                    $tempPrinterId =  $atto['PrinterId'];
                    $tempNotificationType = $atto['Tipo_Ufficiale'];

                    $last_el_list_id = $cls_db->DbInsert($a_dbParams_elab_lists);
                    $contList++;
            }

                // UPDATE ATTO

                $query_up_par = " UPDATE atto SET  ID_Cronologico = ".$id_cronologico.", Anno_Cronologico = ". date('Y').", Elaboration_List_Id = ".$last_el_list_id."  WHERE ID =  " . $atto['ATTO_ID'] ;
                $countActs++;
                mysqli_query($cls_db->conn, $query_up_par);
                
                $id_cronologico++;     

            /** PHP PROGRESS BAR    END  */
        } //END FOREACH

        $cls_db->ExecuteQuery("UPDATE elaborations SET ListNumber=".$contList." WHERE Id=".$last_el_id);

    } 
    catch (mysqli_sql_exception $e) {
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
    $cls_db->End_Transaction();

    $atto_ = "";

    switch ($a_elaboration['Document_Type_Id']){
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
    }

    $storico->insRow('E', "Elaborato '".$a_elaboration['Description']."': ".$atto_." ".$a_enteAdmin['Denominazione']."[".$a_elaboration['CC']."]. Stato 'Assegnati dati stampa e cronologico'");

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