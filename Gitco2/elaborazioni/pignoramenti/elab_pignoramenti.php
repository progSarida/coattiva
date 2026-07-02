<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include(INC . "/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_elaboration.php";

include_once ELAB_PIGNORAMENTI . "/cls_PignoramentoSpese.php";
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

//SGANCIAMENTO NON PIGNORABILI
$comandoSganciamento = "Update partita_tributi
set Elaboration_ID = null,
Position_Status_ID = null,
flag_elaboration = null
Where ID in  (
select Partita_ID from v_pre_elab_pignoramenti Where Stato_Veicolo = 0
and Elaboration_ID = ".$last_el_id.")";

$cls_db->ExecuteQuery($comandoSganciamento);

// QUERY V_CHECK_PIGNORAMENTI
$query_pignoramento = "SELECT 
P.ID as Partita_ID,
P.CC,
P.Tipo AS Tipo_Riscossione,
A.Totale_Dovuto + A.Diritto_Riscossione_Massimo AS Totale_Dovuto_ATTO,
SUM(PA.Importo) AS TOTALE_PAGAMENTI,
A.Info_Cartella AS Info_Cartella,
A.ID AS Atto_ID,

IFNULL(A.Interessi_Precedenti,0) + IFNULL(A.Interessi,0) AS Atto_Interessi,
IFNULL(A.Spese_Notifica_Precedenti,0) + IFNULL(A.Spese_Notifica,0) + IFNULL(A.CAN,0) + IFNULL(A.CAD,0) AS Atto_Spese_Notifica,
IFNULL(A.Diritto_Riscossione_Massimo, 0) AS Atto_Diritto_Riscossione,
A.Data_Calcolo_Interessi AS Atto_Data_Calcolo_Interessi, 
A.Data_Decorrenza_Interessi AS Atto_Data_Decorrenza_Interessi,

V.ID AS Veicolo_ID,
V.Data_Visura,
V.SerieTarga AS Tipo_Veicolo, V.Targa AS Targa_Veicolo, V.Data_Visura, TRIM(V.Telaio) AS Telaio_Veicolo, 
TRIM(V.Fabbrica) AS Fabbrica_Veicolo, TRIM(V.Tipo) AS Modello_Veicolo, TRIM(V.Serie) AS Serie_Veicolo,
V.DataPrimaImmatricolazione AS Data_Immatricolazione,

T.Codici_Tributo, 
T.Importi_Codici_Tributo,
T.Tipo_Codice

FROM partita_tributi AS P
JOIN (
    SELECT TR.Partita_ID,
    GROUP_CONCAT(TR.Codice_Tributo SEPARATOR '*') AS Codici_Tributo, 
	GROUP_CONCAT(TR.Imposta SEPARATOR '*') AS Importi_Codici_Tributo,
	GROUP_CONCAT(CT.Tipo_Codice SEPARATOR '*') AS Tipo_Codice
    FROM tributo AS TR
	JOIN codice_tributo AS CT ON CT.Codice_Tributo = TR.Codice_Tributo
    GROUP BY TR.Partita_ID
) 
AS T ON P.ID=T.Partita_ID
JOIN utente as U ON P.Utente_ID = U.ID
JOIN veicoli V ON V.ID = (SELECT ID FROM veicoli WHERE Utente_ID=P.Utente_ID AND (StatoVeicolo is null OR StatoVeicolo='Targa Attuale') AND Telaio is not null ORDER BY DataPrimaImmatricolazione DESC LIMIT 1)
JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL)
LEFT JOIN pagamento AS PA on P.ID = PA.Partita_ID AND PA.DocumentTypeId is not null
WHERE P.Elaboration_Id = " . $last_el_id." AND P.flag_elaboration = 1
GROUP BY P.ID";

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
            location.href ="<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c="+c+"&a="+a+"&el="+el;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}


if (count($pignoramenti) > 0) {
    /**
     * ? RECUPERO DATI PER CALCOLO INTERESSE E INIZIALIZZAZIONE CLASSE ELABORAZIONE
     */
    //* QUERY LOCKUP_PERIODS
    $query_loc_per = "SELECT * FROM lockup_periods";
    $a_lockupPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query_loc_per));

    //* QUERY PERIODI INTERESSI
    $query_periods =    "SELECT * FROM interessi_tributi WHERE CC = '". $a_elaboration['CC'] ."' ORDER BY Data_Inizio";
    $a_interessiTributi = $cls_db->getResults($cls_db->ExecuteQuery($query_periods));

    $a_params = array(
        'Interessi_Tributi' => $a_interessiTributi,
        'Lockup_Periods' => $a_lockupPeriods
    );
    $cls_elab = new cls_elaboration($a_params);
    /**
     * ? FINE RECUPERO DATI PER CALCOLO INTERESSE E INIZIALIZZAZIONE CLASSE ELABORAZIONE
     */

        /** INIZIO TRANSAZIONE **/

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

        try {

            
            // UPDATE ELABORATIONS

            $a_dbParams = array(
                'table' => 'elaborations',
                'updateField' => array(
                    array('name' => 'Id',  'type' => 'int', 'value' => $a_elaboration['Id']),
                ),
                'fields'=> array(
                    array(  'name' => 'Elaboration_Status_Id',  'type' => 'int', 'value' => ElaborationStatus::SELEZIONE_VEICOLI),
                    array(  'name' => 'Update_Username',        'type' => 'string', 'value' => $_SESSION['username']),
                    array(  'name' => 'Update_Date',            'type' => 'date', 'value' => date('Y-m-d')),
                   
                )
            );

            $cls_db->DbSave( $a_dbParams);
           
           
            $pignoramentoDT = $cls_db->getColumnDataTypes("pignoramento_generale");
            $pignoramentoVeicoloDT = $cls_db->getColumnDataTypes("pignoramento_veicolo");
            cls_PignoramentoSpese::InserisciTariffeSeMancantiPerCC($cls_db,$a_elaboration["CC"]);
            cls_PignoramentoSpese::InserisciCoefficientiSeMancantiPerCC($cls_db,$a_elaboration["CC"]);

            $query_id_crono =   "   SELECT Max(ID_Cronologico) AS ID_Cronologico FROM pignoramento_generale ".
                    "   WHERE CC = '".$a_elaboration["CC"]."'".
                    "       AND Anno_Cronologico = ". date('Y');   
                    
                        
            $results_id_crono = $cls_db->ExecuteQuery($query_id_crono);
            $id_cronos = $cls_db->getArrayLine($results_id_crono);
            
            if(is_null($id_cronos['ID_Cronologico']))
            {
            
                $id_cronos['ID_Cronologico'] = 0;

            }
            
            foreach($pignoramenti as $key=>$row)
            {
                $id_cronos['ID_Cronologico']++;
                
                flush(); ob_flush();flush();ob_flush();
                echo "<script>updateBar(" . ceil($key*100/count($pignoramenti)) . ");</script>";
                flush(); ob_flush();flush();ob_flush();

    /**
     * ? CALCOLO NUOVO INTERESSE FINO A DATA ELABORAZIONE PIGNORAMENTO
     */
        $a_params = array("Tipo_Riscossione" => $row['Tipo_Riscossione']);
        $cls_elab->setParams($a_params);
        $a_totaliCodiciTributo = $cls_elab->totaliCodiciTributo(explode("*", $row['Tipo_Codice']), explode("*", $row['Importi_Codici_Tributo']));
        
        $sum_imp_tributo = $a_totaliCodiciTributo['BASE_INTERESSI'];
        $sum_imp_tributo += $row['Atto_Spese_Notifica'];
        $BasePagamento = $row['TOTALE_PAGAMENTI']-$row['Atto_Interessi']-$row['Atto_Diritto_Riscossione'];
        if($BasePagamento>0)
            $sum_imp_tributo-= $BasePagamento;

        // if ($row['Tipo_Riscossione'] == 'CDS'){
        //     $sum_imp_tributo += $a_data['Atto_Spese_Notifica'];
        //     $BasePagamento = $a_data['TOTALE_PAGAMENTI']-$a_data['Atto_Interessi']-$a_data['Atto_Diritto_Riscossione'];
        //     if($BasePagamento>0)
        //         $sum_imp_tributo-= $BasePagamento;
        // }
        // else {
        //     $totaleCheck = $a_totaliCodiciTributo['TOTALE'] + $row['Atto_Spese_Notifica'] + $row['Atto_Interessi'];
        //     if ($totaleCheck - $row['TOTALE_PAGAMENTI'] < $sum_imp_tributo)
        //         $sum_imp_tributo = $totaleCheck - $row['TOTALE_PAGAMENTI'];
        // }

        $a_params = array(
            "DocumentTypeId" => $a_elaboration['Document_Type_Id'],
            "StartDate" => $row['Atto_Data_Calcolo_Interessi'],
            "EndDate" => $a_elaboration['Data_Calcolo_Interessi'],
            "BaseAmount" => $sum_imp_tributo
        );
        
        $interessi_new = $cls_elab->calcInterests($a_params);
        $importoAtto = $row["Totale_Dovuto_ATTO"]-$row['TOTALE_PAGAMENTI'];
        $importoDovuto = $importoAtto+$interessi_new;
    /**
     * ? FINE CALCOLO NUOVO INTERESSE FINO A DATA ELABORAZIONE PIGNORAMENTO
     */

                
                $a_pigno_gen = array(
                    "CC" => $a_elaboration["CC"],
                    "Partita_ID"=>$row["Partita_ID"],
                    "Atto_ID" =>$row["Atto_ID"],
                    "Anno_Cronologico"=>date("Y"), 
                    "ID_Cronologico"=>$id_cronos['ID_Cronologico'], // da pignoramento generale il max ID presente per quell'anno per CC
                    "Elaboration_Id"=>$last_el_id,
                    "Data_Elaborazione" => date("Y-m-d"),
                    "DocumentTypeId" => $a_elaboration["Document_Type_Id"],
                    "Tipo"=>"preav_fermo",
                    "Data_Decorrenza_Interessi"=>$a_params['StartDate'],
                    "Data_Calcolo_Interessi"=>$a_params['EndDate'],
                    "Importo_Atto"=>$importoAtto,
                    "Interessi"=>$interessi_new,
                    "Importo_Dovuto"=>$importoDovuto,
                    "Spese_Notifica_Debitore"=>0,
                    "Spese_Notifica_Terzi"=>0,
                    "Totale_Spese_Notifica"=>0,
                    "Totale_Spese_Accessorie"=>0,
                    "Totale_Dovuto"=>$importoDovuto,
                );

                $pigno_id = $cls_db->DbSave($cls_db->GetObjectQuery("pignoramento_generale",$a_pigno_gen,$pignoramentoDT));

                //PIGNORAMENTO SPESE VEICOLI
                $pignospese = new cls_PignoramentoSpese($cls_db); // passare $a_elaboration["Document_Type_Id"];
                
                $pignospese($a_elaboration["CC"],$pigno_id,$a_elaboration["Document_Type_Id"],$importoDovuto);

                $modelloVeicolo = $row['Modello_Veicolo'];
                if(!is_null($row['Serie_Veicolo']))
                    $modelloVeicolo.= " ".$row['Serie_Veicolo'];
                $a_pigno_veicolo = array(
                    "CC" => $a_elaboration["CC"],
                    "Pignoramento_ID"=>$pigno_id,
                    "Veicolo_ID"=>(int)$row['Veicolo_ID'],
                    "Tipo_Veicolo"=>strtolower($row['Tipo_Veicolo']),
                    "Targa_Veicolo"=>$row['Targa_Veicolo'],
                    "Marca_Veicolo"=>$row['Fabbrica_Veicolo'],
                    "Modello_Veicolo"=>$modelloVeicolo,
                    "Data_Visura"=>$row['Data_Visura'],
                    "Anno_Immatricolazione"=>date("Y",strtotime($row['Data_Immatricolazione'])),
                    "Fonte_Dati"=>"pra"
                );

                $cls_db->DbSave($cls_db->GetObjectQuery("pignoramento_veicolo",$a_pigno_veicolo,$pignoramentoVeicoloDT));
                    
            }

          
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

        $storico->insRow('E', "Elaborazione '".$a_elaboration['Description']."': Preavvisi fermi amministrativi ".$ente['Denominazione']."[".$a_elaboration['CC']."]. Stato 'Selezione veicoli'");

        $cls_db->End_Transaction();

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