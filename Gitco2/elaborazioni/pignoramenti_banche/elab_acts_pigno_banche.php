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
$log = new LOG();
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

    function endBar(c,a,el,flagMassivo){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Elaborazione terminata!");

       if(el !== null){

        if(flagMassivo !== undefined && flagMassivo === "si"){
            if(flagMassivo == "si")
                location.href = '<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/elab_pignoramenti_banche.php?c='+c+'&a='+a+'&el='+el;
        }
        else{
            swal({
                        title: 'ATTENZIONE',
                        text: "PROCESSO TERMINATO. STAI PER ESSERE REINDIRIZZATO ALLA PAGINA DEI RISULTATI OTTENUTI",
                        icon: 'success',
                        timer: 3000,
                        buttons: false
                    }).then((result) => {
                location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
            })
        }
        
       }else{
                
                swal({
                        title: 'ATTENZIONE',
                        text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                        icon: 'warning',
                        timer: 3000,
                        buttons: false
                    }).then((result) => {
                    
                        location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
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


$query_elaborations =   " SELECT  E.*,  DT.Description AS DocumentType " .
                        " FROM elaborations AS E " .
                        " JOIN document_type DT ON DT.Id = E.Document_Type_Id " .
                        " WHERE E.Id=" . $last_el_id;

$a_elaboration = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_elaborations));

$data_elab = $a_elaboration['Data_Elaborazione'];


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
            location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}

// QUERY V_CHECK_PIGNORAMENTI

$query_pignoramento =   " SELECT * FROM v_check_pignoramenti  " .
                " WHERE Elaboration_Id = " . $last_el_id .
                " AND flag_elaboration = 1 ";

$results = $cls_db->ExecuteQuery($query_pignoramento);
$pignoramenti = $cls_db->getResults($results);

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
            location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}


if (count($pignoramenti) > 0) {

    

        /** INIZIO TRANSAZIONE **/

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

        

        try {

            $status = ElaborationStatus::ASSEGNA_BANCHE;

            if($a_elaboration["Document_Type_Id"] == 8 && $a_elaboration["Flag_Pigno_Banca_Massivo"] == "si"){
                $query = "SELECT * FROM v_assegna_terzo_banca WHERE Elaboration_Id = ".$a_elaboration['Id'];
                $resultAllUser = $cls_db->getResults($cls_db->ExecuteQuery($query));

                $totalProgress = count($resultAllUser);
                $countProgress = 0;
                //var_dump($resultAllUser);die;
                foreach($resultAllUser as $key => $value){
                    /** RECUPERO LA REGIONE DI RESIDENZA DELL'UTENTE E TUTTE LE BANCHE CHE HANNO FILIARI IL QUELLE REGIONI SOLO COL JOIN IN MODO CHE SE NON CI SIANO FILIALI SEGNATE PER QUELLA REGIONE DI NESSUNA BANCA NON VENGA VISUALIZZATO NULLA**/
                    
                    $countProgress++;

                    flush();
                    ob_flush();
                    flush();
                    ob_flush();
                    echo "<script>updateBar(" . ceil($countProgress*100/$totalProgress) . ");</script>";
                    flush();
                    ob_flush();
                    flush();
                    ob_flush();


                    $query = "SELECT BR.banca_id 
                    FROM province_lista AS PL 
                    JOIN regioni_lista AS RL ON RL.Reg_Codice = PL.Pro_Codice_Regione
                    JOIN banca_regione AS BR ON BR.reg_codice = RL.Reg_Codice
                    JOIN banca AS BA ON BA.ID = BR.banca_id AND BA.Tipo_Banca = 'sede' AND BA.disabled != 1
                    WHERE PL.Pro_Sigla = '".$value["Provincia_Residenza_Utente"]."';";

                    $resultAllBank = $cls_db->getResults($cls_db->ExecuteQuery($query));

                    foreach($resultAllBank as $key_B => $value_B){
                        
                        $save = new stdClass();
                        $save->Elaboration_Id = $value["Elaboration_Id"];
                        $save->CC = $value["CC"];
                        $save->Utente_ID = $value["Utente_ID"];
                        $save->Terzo_ID = $value_B["banca_id"];

                        $check = $cls_db->DbSave($cls_db->GetObjectQuery("banche_pvt",$save));

                        if($check === false){
                            throw new Exception("Errore! Impossibile salvare l'assegnazione della banca all'utente!");
                        }

                        /*$queryExsist ="
                            select ID
                            from banche_pvt
                            where Elaboration_Id = $value->Elaboration_Id
                            and CC = '$value->CC'
                            and Utente_ID = $value->Utente_ID
                            and Terzo_ID = $value_B->banca_id
                            ";

                            $verify = $cls_db->getResults($cls_db->ExecuteQuery($queryExsist));

                            if(count($verify) == 0){
                                $save = new stdClass();
                                $save->Elaboration_Id = $value->Elaboration_Id;
                                $save->CC = $value->CC;
                                $save->Utente_ID = $value->Utente_ID;
                                $save->Terzo_ID = $value_B->banca_id;

                                $check = $cls_db->DbSave($cls_db->GetObjectQuery("banche_pvt",$save));

                                if($check === false){
                                    throw new Exception("Errore! Impossibile salvare l'assegnazione della banca all'utente!");
                                }
                            }
                            else{
                                $save = new stdClass();
                                $save->Elaboration_Id = $value->Elaboration_Id;
                                $save->CC = $value->CC;
                                $save->Utente_ID = $value->Utente_ID;
                                $save->Terzo_ID = $value_B->banca_id;

                                $check = $cls_db->DbSave($cls_db->GetObjectQuery("banche_pvt",$save,null,array("ID" => $verify[0]["ID"])));

                                if($check === false){
                                    throw new Exception("Errore! Impossibile aggiornare l'assegnazione della banca all'utente!");
                                }
                            }*/
                    }

                }

                $status = ElaborationStatus::RICHIESTA_INIPEC;
            }

            
            // UPDATE ELABORATIONS
            $a_dbParams = array(
                'table' => 'elaborations',
                'updateField' => array(
                    array('name' => 'Id',  'type' => 'int', 'value' => $a_elaboration['Id']),
                ),
                'fields'=> array(
                    array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => $status),
                    array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                    array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
                   
                )
            );

            $cls_db->DbSave( $a_dbParams);

            if($a_elaboration["Flag_Pigno_Banca_Massivo"] != "si")
            {
                flush();
                    ob_flush();
                    flush();
                    ob_flush();
                    echo "<script>updateBar(" . ceil(50) . ");</script>";
                    flush();
                    ob_flush();
                    flush();
                    ob_flush();
            }

            // UPDATE PARTITE_TRIBUTI

            $a_dbParams_trib = array(
                'table' => 'partita_tributi',
                'updateField' => array(
                    array('name' => 'Elaboration_Id',       'type' => 'int',        'value' => $a_elaboration['Id'], 'operator' => 'AND'),
                    array('name' => 'flag_elaboration',     'type' => 'boolean',    'value' => 0)
                ),
                'fields'=> array(
                        array(  'name' => 'Elaboration_Id',  	'type' => 'int', 'value' => NULL),
                        array(  'name' => 'Position_Status_Id',	'type' => 'int', 'value' => NULL),
                        array(  'name' => 'flag_elaboration',   'type' => 'int', 'value' => NULL),
                )
            );

            $cls_db->DbSave($a_dbParams_trib);


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

        $cls_db->End_Transaction();
    
        $storico->insRow('E', "Elaborato '".$a_elaboration['Description']."': Pignoramenti presso banca ".$ente['Denominazione']."[".$a_elaboration['CC']."]. Stato 'Assegna banca'");
    


     flush();
     ob_flush();
     echo "<script>endBar('".$c."','".$a."',".$last_el_id.",'".$a_elaboration["Flag_Pigno_Banca_Massivo"]."');</script>";
     flush();
     ob_flush();
     flush();
     ob_flush(); 
} else {
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