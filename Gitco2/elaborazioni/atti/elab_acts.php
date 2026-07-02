<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/headerAjax.php");
//include(INC . "/menu.php");
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
    <div class="row" style="margin-top: 7%;">
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
$tipo_atto = $cls_help->getVar('tipo_atto');
$cod_cat = $cls_help->getVar('cod_cat');

$printer_id = 0;
$print_type_id = 0;
$tipo_ufficiale = "";
$modalita_stampa = "";


$query_elaborations =   " SELECT  E.*,  DT.Description AS DocumentType " .
                        " FROM elaborations AS E " .
                        " JOIN document_type DT ON DT.Id = E.Document_Type_Id " .
                        " WHERE E.Id=" . $last_el_id;

$a_elaboration = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_elaborations));
$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$a_elaboration['CC']."'") );
$data_elab = $a_elaboration['Data_Elaborazione'];
$data_calc_int = $a_elaboration['Data_Calcolo_Interessi'];

$queryDefaultUffStamp = "select DefaultPecTipoUfficiale,DefaultRaccomandataTipoUfficiale,DefaultPecTipoStampa,DefaultRaccomandataTipoStampa 
        from elaborations where Id = $last_el_id";

$resultDefaultUffStamp = $cls_db->getArrayLine($cls_db->ExecuteQuery($queryDefaultUffStamp));


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
            window.close();
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}



// QUERY LOCKUP_PERIODS

$query_loc_per = "SELECT * FROM lockup_periods";
$a_lockupPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query_loc_per));


// QUERY V_CHECK_PARTITE

$query_atto =   " SELECT * FROM v_check_partite  " .
                " WHERE Elaboration_Id = " . $last_el_id .
                " AND flag_elaboration = 1 AND archived IS NULL ";

$results = $cls_db->ExecuteQuery($query_atto);
$atti = $cls_db->getResults($results);


// QUERY PERIODI INTERESSI

$query_periods =    "	SELECT * " .
                    "   FROM interessi_tributi " .
                    "   WHERE  CC = '" . $a_elaboration['CC'] . "'" .
                    "  	ORDER BY Data_Inizio	";

$a_interessiTributi = $cls_db->getResults($cls_db->ExecuteQuery($query_periods));

// QUERY PARAMETRI ANNUALI

$query_par_y =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $a_elaboration['CC'] . "' AND Anno=" . date('Y');

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
            window.close();
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}


if (count($atti) > 0) {

    $countAllResult = count($atti);

    $a_params = array(
        'Interessi_Tributi' => $a_interessiTributi,
        'Lockup_Periods' => $a_lockupPeriods
    );
    $cls_elab = new cls_elaboration($a_params);

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

        $a_params = array(
            "Tipo_Riscossione" => $atto['Tipo_Riscossione'],
        );
        $cls_elab->setParams($a_params);
        
        $cod_tributo = explode("*", $atto['Tipo_Codice']);
        $imp_tributo = explode("*", $atto['Importi_Codici_Tributo']);
        $diritto_risc_min = 0;
        $diritto_risc_max = 0;
        $spese_not_pigno = 0;
        $spese_acc_pigno = 0;
        $atto_rettificato = 0;
        if (!is_null($atto['Atto_Last_ID'])) {

            
            //ULTIMO ATTO PRESENTE

            $interessi_prec = $atto['Interessi_Precedenti_ATTO'] + $atto['Interessi_ATTO'] + $atto['Interessi_PG'];
            $spese_not_precedenti =  $atto['Spese_Notifica_Precedenti_ATTO'] + $atto['Spese_Notifica_ATTO'] + $atto['CAN_ATTO'] +  $atto['CAD_ATTO'];

            $spese_not_pigno = $atto['Spese_Notifica_Pignoramento_ATTO'];
            if(!empty($atto['Totale_Spese_Notifica_PG']))
                $spese_not_pigno+= $atto['Totale_Spese_Notifica_PG'];

            $spese_acc_pigno = $atto['Spese_Accessorie_Pignoramento_ATTO'];
            if(!empty($atto['Totale_Spese_Accessorie_PG']))
                $spese_acc_pigno+= $atto['Totale_Spese_Accessorie_PG'];

            $diritto_risc_precedente = $atto['Diritto_Riscossione_ATTO'];
            $totale_pagamenti = $atto['TOTALE_PAGAMENTI']+$atto['TOTALE_PAGAMENTI_PG'];
            switch ($a_elaboration['Document_Type_Id']) {
                case 2:
                    $data_inizio = $atto['Data_Calcolo_Interessi_ATTO'];
                    break;
                case 12:
                    $data_inizio = $atto['Data_Calcolo_Interessi_ATTO'];
                    break;
                case 4:
                case 11:
                case 3:

                    $data_inizio = $atto['Data_Decorrenza_Interessi_ATTO'];
                    $data_calc_int = $atto['Data_Calcolo_Interessi_ATTO'];

                    break;
            }

            if (intval($atto['Position_Status_Id']) == 3){
                $printer_id = 1;
                $print_type_id = 3;
                $modalita_stampa = "ordinaria";
                $tipo_ufficiale = "diretta";
            }

            if (intval($atto['Position_Status_Id']) == 3 && $a_elaboration['Document_Type_Id'] == 2) {
                $atto_rettificato = 1;
                
                //RETTIFICA

                $query_act = "  SELECT * FROM atto " .
                             "  WHERE Partita_ID = " . $atto['Partita_ID'] .
                             "  ORDER BY ID DESC    ";

                $results_act = $cls_db->ExecuteQuery($query_act);

                $acts = $cls_db->getResults($results_act);

                if (count($acts) == 1 && $acts[0]['DocumentTypeId'] == 2) {

                    $interessi_prec = 0;
                    $spese_not_precedenti = 0;
                    $data_db = $atto['Partita_Data_Decorrenza'];
                } else {
                    $atto_pre_rettifica = array();
                    
                    foreach ($acts as $key => $act) {
                       
                        if ($act['Rettifica_Flag'] == "si" && isset($act[$key + 1]) && $act[$key + 1]['DocumentTypeId'] !== 3 && $act[$key + 1]['DocumentTypeId'] !== 11) {

                            $atto_pre_rettifica = $act;                           
                            break;
                        }
                    }
                   
                    $data_db = strtotime($atto_pre_rettifica['Data_Calcolo_Interessi']);
                    
                    $interessi_prec = $atto_pre_rettifica['Interessi_Precedenti'] + $atto_pre_rettifica['Interessi'];
                    
                    $spese_not_precedenti = $atto_pre_rettifica['Spese_Notifica_Precedenti'] + $atto_pre_rettifica['Spese_Notifica'] + $atto_pre_rettifica['CAN'] + $atto_pre_rettifica['CAD'];
                    $diritto_risc_precedente = $atto_pre_rettifica['Diritto_Riscossione_Massimo'];
               
                }
            }
        } else {

            // PARTITA SENZA ATTO
            $interessi_prec = 0;
            $spese_not_precedenti = 0;
            $diritto_risc_precedente = 0;
            $spese_not_pigno = 0;
            $spese_acc_pigno = 0;
            $totale_pagamenti = 0;

            $data_inizio = $atto['Partita_Data_Decorrenza'];
        }
        
        $a_totaliCodiciTributo = $cls_elab->totaliCodiciTributo($cod_tributo, $imp_tributo);

        $imp_codici_tot = $a_totaliCodiciTributo['TOTALE'];
        $sum_imp_tributo = $a_totaliCodiciTributo['BASE_INTERESSI'];
        $sum_imp_tributo += $spese_not_precedenti + $spese_not_pigno;//? + $spese_acc_pigno;
        $BasePagamento = $totale_pagamenti-$interessi_prec-$diritto_risc_precedente;
        if($BasePagamento>0)
            $sum_imp_tributo-= $BasePagamento;

        // $sum_imp_tributo = $a_totaliCodiciTributo['BASE_INTERESSI'];

        // if ($atto['Tipo_Riscossione'] == 'CDS')
        //     $sum_imp_tributo += $spese_not_precedenti - $atto['TOTALE_PAGAMENTI'];
        // else {
        //     $totaleCheck = $imp_codici_tot + $spese_not_precedenti + $interessi_prec;



        //     if ($totaleCheck - $atto['TOTALE_PAGAMENTI'] < $sum_imp_tributo)
        //         $sum_imp_tributo = $totaleCheck - $atto['TOTALE_PAGAMENTI'];
        // }

        $a_params = array(
            "DocumentTypeId" => $a_elaboration['Document_Type_Id'],
            "StartDate" => $data_inizio,
            "EndDate" => $data_calc_int,
            "BaseAmount" => $sum_imp_tributo
        );

        $interessi_new = 0.00;
        // if($atto['CC'] != 'C559')
            $interessi_new = $cls_elab->calcInterests($a_params);
      
    /*    if($atto['Partita_ID'] == 55024){
           //$interessi_new = $cls_elab->calcInterests($a_params);
           // var_dump('interessi_new: '.$interessi_new);
           die;
        }else{continue;}
   */

        switch ($tipo_atto) {
            case 2:
            case 4:
                $printer_id = 2;
                $print_type_id = 1;
                $modalita_stampa = "posta";
                $tipo_ufficiale = "diretta";
                $spe_not = number_format($params_arr['Spese_Notifica'], 2, ".", "");
                break;
            case 12:
                $printer_id = 2;
                $print_type_id = 2;
                $modalita_stampa = "raccomandata";
                $tipo_ufficiale = "diretta";
                $spe_not = number_format($params_arr['Spese_Raccomandata'], 2, ".", "");
                break;
            case 3:
            case 11:
                $printer_id = 2;
                $print_type_id = 3;
                $modalita_stampa = "ordinaria";
                $tipo_ufficiale = "diretta";
                $spe_not = number_format($params_arr['Spese_Postali'], 2, ".", "");
                break;
        }
        
        
        if(empty($atto['Rec_Presso'])){
            if(!empty($atto['Utente_PEC']) && !empty($atto['InipecLoaded'])){
                $date1 = date_create(date('Y-m-d'));
                if(!is_null($atto['InipecLoaded'])) {

                    $date2 = date_create($atto['InipecLoaded']);
                    $diff = date_diff($date1,$date2);
                    $days = (int)$diff->format("%a");

                    if($days<=15){
                        $printer_id = 1;
                        $print_type_id = $resultDefaultUffStamp["DefaultPecTipoStampa"];
                        $modalita_stampa = "pec";
                        $tipo_ufficiale = $resultDefaultUffStamp["DefaultPecTipoUfficiale"];
                        $spe_not = number_format($params_arr['Spese_Pec'], 2, ".", "");
                    }
                }
                else{
                    $tipo_ufficiale = $resultDefaultUffStamp["DefaultRaccomandataTipoUfficiale"];
                    $print_type_id = $resultDefaultUffStamp["DefaultRaccomandataTipoStampa"];
                }
            }
            else{
                $tipo_ufficiale = $resultDefaultUffStamp["DefaultRaccomandataTipoUfficiale"];
                $print_type_id = $resultDefaultUffStamp["DefaultRaccomandataTipoStampa"];
            }
        }

        
        $totale_dovuto =  $imp_codici_tot + $spe_not + $spese_not_precedenti + $interessi_new + $interessi_prec + $spese_not_pigno + $spese_acc_pigno;
        $totale_dovuto_perc_risc =  $imp_codici_tot + $interessi_new + $interessi_prec;

        if (($atto['Flag_Blocco_Diritto_Riscossione'] != "si" || empty($atto['Flag_Blocco_Diritto_Riscossione'])) && $a_enteAdmin['Gestore_Tipo'] == "Concessionario") {

            // $importo_calcolo_diritto = $totale_dovuto - $atto['TOTALE_PAGAMENTI'];
            $importo_calcolo_diritto = $totale_dovuto_perc_risc - $atto['TOTALE_PAGAMENTI'];

            $diritto_risc_min = $importo_calcolo_diritto * $params_arr['Diritto_Riscossione_Minimo'] / 100;
            $diritto_risc_max = $importo_calcolo_diritto * $params_arr['Diritto_Riscossione_Massimo'] / 100;
        }

        if($a_elaboration['Document_Type_Id']==12 || $a_elaboration['Document_Type_Id']==3 || $a_elaboration['Document_Type_Id']==11){
            $diritto_risc_min = 0.00;
            $diritto_risc_max = 0.00;
        }

        /** */


        /** INIZIO TRANSAZIONE **/

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

        $can_atto = (!is_null($atto['CAN_ATTO'])) ? $atto['CAN_ATTO'] : 0.00;
        $cad_atto = (!is_null($atto['CAD_ATTO'])) ? $atto['CAD_ATTO'] : 0.00;

        $diritto_risc_min = (!is_null($diritto_risc_min)) ? $diritto_risc_min : 0.00;
        $diritto_risc_max = (!is_null($diritto_risc_max)) ? $diritto_risc_max : 0.00;

        

        

        try {

            $a_dbParams = array(
                'table' => 'atto',
                'fields'=> array(
                    array(  'name' => 'DocumentTypeId',             'type' => 'int', 'value' => $a_elaboration['Document_Type_Id']),
                    array(  'name' => 'PrintTypeId',                'type' => 'int', 'value' =>  $print_type_id),
                    array(  'name' => 'PrinterId',                  'type' => 'int', 'value' => $printer_id),
                    array(  'name' => 'CC',                         'type' => 'string', 'value' => $atto['CC']),
                    array(  'name' => 'Partita_ID',                 'type' => 'int', 'value' => $atto['Partita_ID']),
                    array(  'name' => 'Atto',                       'type' => 'string', 'value' => $a_elaboration['DocumentType']),
                    array(  'name' => 'Info_Cartella',              'type' => 'string', 'value' => $atto['Info_Cartella']),
                    array(  'name' => 'Modalita_Stampa',            'type' => 'string', 'value' => $modalita_stampa),
                    array(  'name' => 'Tipo_Ufficiale',            'type' => 'string', 'value' => $tipo_ufficiale),
                    array(  'name' => 'Stato_Stampa',               'type' => 'string', 'value' => 'Da stampare'),
                    array(  'name' => 'Data_Elaborazione',          'type' => 'date', 'value' => $data_elab),
                    array(  'name' => 'Data_Calcolo_Interessi',     'type' => 'date', 'value' => $data_calc_int),
                    array(  'name' => 'Data_Decorrenza_Interessi',  'type' => 'date', 'value' => $data_inizio),
                    array(  'name' => 'Interessi',                  'type' => 'float', 'value' => $interessi_new),
                    array(  'name' => 'Spese_Notifica',             'type' => 'float', 'value' => $spe_not),
                    array(  'name' => 'Spese_Notifica_Precedenti',  'type' => 'float', 'value' => $spese_not_precedenti),
                    array(  'name' => 'Spese_Notifica_Pignoramento',  'type' => 'float', 'value' => $spese_not_pigno),
                    array(  'name' => 'Spese_Accessorie_Pignoramento',  'type' => 'float', 'value' => $spese_acc_pigno),
                    array(  'name' => 'CAN',                        'type' => 'float', 'value' => $can_atto),
                    array(  'name' => 'CAD',                        'type' => 'float', 'value' => $cad_atto),
                    array(  'name' => 'Interessi_Precedenti',       'type' => 'float', 'value' => $interessi_prec),
                    array(  'name' => 'Totale_Dovuto',              'type' => 'float', 'value' => $totale_dovuto),
                    array(  'name' => 'Diritto_Riscossione_Minimo', 'type' => 'float', 'value' => $diritto_risc_min),
                    array(  'name' => 'Diritto_Riscossione_Massimo','type' => 'float', 'value' => $diritto_risc_max),
                    array(  'name' => 'Elaboration_Id',             'type' => 'int',   'value' => $a_elaboration['Id']),
                    array(  'name' => 'Atto_Rettificato',           'type' => 'int',   'value' => $atto_rettificato)
                )
            );


            // if(!empty($atto['Totale_Spese_Notifica_PG'])){
            //     var_dump("Partita", $atto['Comune_ID'], $atto['Partita_ID']);

            //     var_dump($a_dbParams);

            //     var_dump("Spese not pigno", $atto['Totale_Spese_Notifica_PG']);
            //     var_dump("Spese acc pigno", $atto['Totale_Spese_Accessorie_PG']);
            //     var_dump("SET Spese not pigno", $spese_not_pigno);
            //     var_dump("SET Spese acc pigno", $spese_acc_pigno);
            // }

            // continue;

            
            $lastId = $cls_db->DbInsert($a_dbParams);

            // UPDATE ELABORATIONS

            $a_dbParams = array(
                'table' => 'elaborations',
                'updateField' => array(
                    array('name' => 'Id',  'type' => 'int', 'value' => $a_elaboration['Id']),
                ),
                'fields'=> array(
                    array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => 2),
                    array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                    array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
                   
                )
            );

            $cls_db->DbSave( $a_dbParams);

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
        $cls_db->End_Transaction();

        /* flush();
        ob_flush();
        echo "<script>endBar('".$c."','".$a."',".$last_el_id.",".$tipo_atto.",'".$cod_cat."');</script>";
        flush();
        ob_flush();
        flush();
        ob_flush(); */

        /** PHP PROGRESS BAR    END  */
    }
     //END FOREACH 
// die;
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
        default:
            break;
    }
     
     $storico->insRow('E', "Elaborato '".$a_elaboration['Description']."': ".$atto_." ".$a_enteAdmin['Denominazione']."[".$a_elaboration['CC']."]. Stato 'Elaborato'");

     flush();
     ob_flush();
     echo "<script>endBar('".$c."','".$a."',".$last_el_id.");</script>";
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