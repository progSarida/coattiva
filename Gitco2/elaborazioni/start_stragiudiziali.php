<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include(INC . "/header.php");
?>
<!--<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.0.2/css/searchPanes.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.1/css/select.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.4.0/css/select.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.dataTables.min.css">-->
    <link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css"/>
    <script type="text/javascript" src="<?= DATATABLE ?>/datatables.js"></script>

<style>
   /* div.dataTables_filter,
    div.dataTables_length {
        display: inline-block;
        margin-left: 2em;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        margin-right: 3.5em;
        display: inline-block;
        width: auto;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        min-width: 0em;
        padding: 0em;
        margin-left: -5px;


    }

    .scrollable {
        height: 600px;
        overflow-y: auto;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding : 0px;
    margin-left: 5px;
   
    display: inline;
    border: 0px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    border: 0px;
}*/
   
</style>


<!--<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>-->

<?php
include_once(INC . "/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_elaboration.php";
include_once CLS . "/cls_DateTime.php";

$db = new cls_db();
$cls_help = new cls_help();



if ($_SESSION['username'] == NULL) {
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$flag = 1;
if (intval($_SESSION['aut_tipo']) !== 1){
    $cod_cat= $c;
}

$form_type_id = 40;

/** QUERY ENTI  */

$tax_type = "SELECT * FROM tax_type ORDER BY Name";
$a_tax = $db->getResults($cls_db->SelectQuery($tax_type));
$a_selection = array("value" => "Name", "firstOpt" => 0, "selected" => '', "text" => array("[Description]"));
$optionsTaxes = $cls_html->getOptions($a_tax, $a_selection);

/**	QUERY UTENTE */

$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = ".date("Y");
$controlParam = $db->getArrayLine($db->ExecuteQuery($query));

if($controlParam == null) $CC = "";
else $CC = $c;

/** QUERY BLOCCHI PRESCIZIONE ES COVID **/

$query = "SELECT * FROM lockup_periods WHERE CC = '*****' OR CC = '".$c."'";
$blocchi = $cls_db->getResults($cls_db->ExecuteQuery($query));

$locup_params["Lockup_Periods"] = $blocchi;
$block_elab = new cls_elaboration($locup_params);

/*$query_banca_utente = "SELECT   v_c_p.ID_CRONOLOGICO AS ID_CRONOLOGICO,
                                v_c_p.ANNO_CRONOLOGICO AS ANNO_CRONOLOGICO , 
                                v_c_p.Utente_ID AS Utente_ID,  
                                v_c_p.Atto_Last_ID AS Atto_Last_ID, 
                                v_c_p.Cognome_Ditta AS Cognome_Ditta , 
                                v_c_p.Nome AS Nome, 
                                v_c_p.CF_PI AS CF_PI , 
                                v_c_p.Res_Comune AS Res_Comune, 
                                v_c_p.Res_Via AS Res_Via , 
                                v_c_p.Denominazione_Ente AS Denominazione_Ente, 
                                v_c_p.Res_Civico AS Res_Civico ,  
                                v_c_p.TOTALE_PAGAMENTI AS TOTALE_PAGAMENTI ,
                                v_c_p.Totale_Dovuto_ATTO AS TOTALE_DOVUTO , 
                                v_c_p.Tipo_Riscossione AS Tipo_Riscossione, 
                                v_c_p.Utente_Comune_ID AS Utente_Comune_ID , 
                                v_c_p.TIPO_ATTO AS TIPO_ATTO , 
                                v_c_p.CC AS CC ,
                                v_c_p.Denominazione_Ente AS Denominazione_Ente ,
                                v_c_p.Partita_ID ," .
    "			( 	CASE 	" .
    "				WHEN v_c_p.Atto_Last_ID IS NULL OR v_c_p.Data_Notifica_Atto IS NULL  THEN 'Nessun atto presente' " .
    "				WHEN v_c_p.Flag_Blocco_Coazione = 'si' THEN 'Atto bloccato' " .
    "				WHEN v_c_p.TOTALE_PAGAMENTI = 0  THEN 'NON Pagata' " .
    "				WHEN IF((v_c_p.Totale_Dovuto_ATTO - v_c_p.TOTALE_PAGAMENTI) < p_a.Importo_Minimo, 1,2) = 1  THEN 'Pagata Completamente' ".
    "               ELSE 'Parzialmente Pagata'	" .
    "				END ) AS ESITO, " .
    "			( 	CASE 	" .
    "				WHEN NOW() > v_c_p.Data_Notifica_Atto + INTERVAL 60 DAY THEN 'Attiva' " .
    "				WHEN NOW() > v_c_p.Data_Notifica_Atto + INTERVAL 5 YEAR THEN 'Prescritta' " .
    "				WHEN NOW() > v_c_p.Data_Notifica_Atto + INTERVAL 1 YEAR THEN 'Scaduta' " .
    "				WHEN NOW() < v_c_p.Data_Notifica_Atto + INTERVAL 60 DAY THEN 'Attesa 60 gg' ".
    "               ELSE 'Caso non considerato'	" .//Flag_Blocco_Coazione
    "				END ) AS STATO " .
    "FROM 	v_check_partite as v_c_p, parametri_annuali AS p_a  " .
    "WHERE 	v_c_p.DocumentTypeId in (2, 4)	" .
    "AND    v_c_p.Data_Notifica_Atto IS NOT NULL " .
    "AND    (NOW() > (SELECT P.Data_Pagamento FROM pagamento AS P WHERE P.Atto_ID = v_c_p.ID_ATTO ORDER BY P.Data_Pagamento DESC LIMIT 1) + INTERVAL 60 DAY) " .
    "AND    (v_c_p.Totale_Dovuto_ATTO - v_c_p.TOTALE_PAGAMENTI ) > 0 " .
    "AND    v_c_p.CC = '" . $c . "'	" .
    "AND    p_a.Anno = '" . date('Y') . "'	" .
    "AND    p_a.CC = v_c_p.CC " .
    "AND    v_c_p.Totale_Dovuto_ATTO > p_a.Importo_Minimo  " .
    "AND NOT EXISTS (	" .
    "		SELECT * " .
    "		FROM banca_utente AS b_u	" .
    "		WHERE  b_u.Utente_ID = v_c_p.Utente_ID " .
    ") ";*/


//$a_results = $db->getResults($db->SelectQuery($query_banca_utente));

//var_dump($a_results); die;

//$cls_ente = new cls_ente($a_enteAdmin);

$query = 'SELECT PT.ID,
       PT.ID AS Partita_ID,
       PT.CC,
       PT.Tipo AS Tipo_Riscossione,

       EG.Denominazione,

       p_a.Anno,
       p_a.Importo_Minimo,

       U.Comune_ID,
       U.ID AS Utente_ID,
       U.Nome,

       A.Data_Notifica AS Data_Notifica_Atto ,
       #NPG.Data_Notifica AS Data_Notifica_Pigno ,
       IF( NPG.Data_Notifica IS NULL, A.Data_Notifica, IF(NPG.Data_Notifica > A.Data_Notifica, NPG.Data_Notifica, A.Data_Notifica)) AS Data_Notifica_Atto_Pigno_5,
       A.ID AS Atto_ID ,
       A.DocumentTypeId,
       A.Totale_Dovuto,
       A.Diritto_Riscossione_Minimo,
       A.Diritto_Riscossione_Massimo,

       IF(UCS.Partita_ID IS NULL, "NON Selezionata" COLLATE utf8mb4_unicode_ci, "Selezionata" COLLATE utf8mb4_unicode_ci) AS user_check,

       (A.Data_Notifica + INTERVAL 60 DAY) AS Data_Notifica_60,
       IF( NPG.Data_Notifica IS NULL, A.Data_Notifica + INTERVAL 5 YEAR, IF(NPG.Data_Notifica > A.Data_Notifica, NPG.Data_Notifica, A.Data_Notifica) + INTERVAL 5 YEAR) AS Data_Notifica_5,
       (A.Data_Notifica + INTERVAL 1 YEAR) AS Data_Notifica_1,
       CONCAT(COALESCE(U.Cognome,""),COALESCE(U.Ditta,"")) AS Cognome_Ditta,
       CONCAT(COALESCE(U.Codice_Fiscale,""),COALESCE(U.Partita_Iva,"")) AS CF_PI,
        (
            CASE
                 WHEN A.ID IS NULL OR A.Data_Notifica IS NULL  THEN "Nessun atto presente" COLLATE utf8mb4_unicode_ci
                 WHEN PT.Flag_Blocco_Coazione = "si" THEN "Atto bloccato" COLLATE utf8mb4_unicode_ci
                 WHEN (A.Totale_Dovuto IS NULL OR A.Diritto_Riscossione_Minimo IS NULL OR A.Diritto_Riscossione_Massimo IS NULL) THEN "Totale o riscossione non inserito" COLLATE utf8mb4_unicode_ci
                 WHEN (SELECT SUM(PA1.Importo) FROM pagamento AS PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null) = 0  THEN "NON Pagata" COLLATE utf8mb4_unicode_ci
                 WHEN IF((IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1)  , (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo) , (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) - (SELECT SUM(PA1.Importo) FROM pagamento AS PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null) ) < p_a.Importo_Minimo, 1,2) = 1  THEN "Pagata Completamente" COLLATE utf8mb4_unicode_ci
                 WHEN (SELECT SUM(PA1.Importo) FROM pagamento AS PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null) IS NULL THEN "Non Pagata" COLLATE utf8mb4_unicode_ci
                 ELSE "Parzialmente Pagata" COLLATE utf8mb4_unicode_ci
            END
        ) AS ESITO,
       (
           CASE
                WHEN NOW() > IF( NPG.Data_Notifica IS NULL,
                    (
                        A.Data_Notifica + INTERVAL
                        (
                            SELECT SUM(DATEDIFF(IF(NOW() < L.End_Date, NOW(), L.End_Date), IF(A.Data_Notifica < L.Start_Date, L.Start_Date, A.Data_Notifica )))
                            FROM lockup_periods AS L
                            WHERE (L.CC = PT.CC OR L.CC = "*****") AND L.End_Date > A.Data_Notifica AND L.Start_Date < NOW() AND IF(PT.Tipo = "CDS",L.Lockup_Type_Id != 3,L.Lockup_Type_Id != 2)
                        ) DAY
                    ) + INTERVAL 5 YEAR
                    , IF(
                        NPG.Data_Notifica > A.Data_Notifica,
                            NPG.Data_Notifica + INTERVAL
                            (
                                SELECT SUM(DATEDIFF(IF(NOW() < L.End_Date, NOW(), L.End_Date), IF(NPG.Data_Notifica < L.Start_Date, L.Start_Date, NPG.Data_Notifica )))
                                FROM lockup_periods AS L
                                WHERE (L.CC = PT.CC OR L.CC = "*****") AND L.End_Date > NPG.Data_Notifica AND L.Start_Date < NOW() AND IF(PT.Tipo = "CDS",L.Lockup_Type_Id != 3,L.Lockup_Type_Id != 2)
                            ) DAY,
                            A.Data_Notifica + INTERVAL
                            (
                                SELECT SUM(DATEDIFF(IF(NOW() < L.End_Date, NOW(), L.End_Date), IF(A.Data_Notifica < L.Start_Date, L.Start_Date, A.Data_Notifica )))
                                FROM lockup_periods AS L
                                WHERE (L.CC = PT.CC OR L.CC = "*****") AND L.End_Date > A.Data_Notifica AND L.Start_Date < NOW() AND IF(PT.Tipo = "CDS",L.Lockup_Type_Id != 3,L.Lockup_Type_Id != 2)
                            ) DAY
                        ) + INTERVAL 5 YEAR) THEN "Prescritta" COLLATE utf8mb4_unicode_ci
                WHEN NOW() >(
                                A.Data_Notifica + INTERVAL
                                (
                                    SELECT SUM(DATEDIFF(IF(NOW() < L.End_Date, NOW(), L.End_Date), IF(A.Data_Notifica < L.Start_Date, L.Start_Date, A.Data_Notifica )))
                                    FROM lockup_periods AS L
                                    WHERE (L.CC = PT.CC OR L.CC = "*****") AND L.End_Date > A.Data_Notifica AND L.Start_Date < NOW() AND IF(PT.Tipo = "CDS",L.Lockup_Type_Id != 3,L.Lockup_Type_Id != 2)
                                ) DAY
                            ) + INTERVAL 1 YEAR THEN "Scaduta" COLLATE utf8mb4_unicode_ci
                WHEN NOW() > A.Data_Notifica + INTERVAL 60 DAY THEN "Attiva" COLLATE utf8mb4_unicode_ci
                WHEN NOW() < A.Data_Notifica + INTERVAL 60 DAY THEN "Attesa 60 gg" COLLATE utf8mb4_unicode_ci
                ELSE "Non valida"
           END
       ) AS STATO

FROM partita_tributi AS PT
    LEFT JOIN atto AS A ON A.ID = IF((SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL) IS NULL, (SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11) , (SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL))
    LEFT JOIN utente AS U ON U.ID = PT.Utente_ID
    LEFT JOIN enti_gestiti as EG ON EG.CC = PT.CC
    LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC AND p_a.Anno = YEAR(CURRENT_DATE())
    LEFT JOIN partite_check_stra AS UCS ON UCS.CC = PT.CC AND UCS.Partita_ID = PT.ID AND UCS.flag_check = 1
    LEFT JOIN pignoramento_generale AS PG ON PG.ID= (SELECT MAX(ID) FROM pignoramento_generale AS PG1 WHERE PG1.Partita_ID = PT.ID ) AND Stato_Pignoramento != "Archiviato" AND Stato_Pignoramento != "Annullato"
    LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica = "debitore" AND NPG.Tipo_Atto_Notificato = "pignoramento"

    WHERE PT.Is_Discharged = 0 AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE  b_u.Utente_ID = PT.Utente_ID ) 
        AND (PT.Is_Expired <> 1 OR PT.Is_Expired IS NULL) AND A.DocumentTypeId in (2, 4) AND PT.CC = "'.$c.'" 
    GROUP BY PT.ID;';


$result = $db->getResults($db->ExecuteQuery($query));

?>

<div class="container">
    <div style="height: auto; display: flex; justify-content: center;">
        <div class="col">
            <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#demo">STRAGIUDIZIALE</button>
            <div id="demo" class="collapse">
                <div class="card card-body" style="width: 40rem;">
                    <form id="form_stragiudiziale" action="elab_printing_stragiudiziale.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&form_type_id=<?= $form_type_id ?>" method="post" target="elabora" ><!--onSubmit="window.open('', 'elabora', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')"-->
                        <br/>
                        <div class="form-group">
                            <label for="tax_type">ENTRATA</label>
                            <select id="tax_type" name="tax_type" class="form-control resize validateCustom vld_Custom_r" tabindex=6>
                                
                            <?php 
                            if($flag = 1)
                            {
                                echo "<option value='COMPLETO' selected>TUTTI</option>";
                            }
                            echo $optionsTaxes;
                            ?>
                            </select>
                        </div>
                        <br/>
                        <div class="form-group">
                            <label for="print_type">TIPO STAMPA</label>
                            <select id="print_type" name="print_type" class="form-control resize validateCustom vld_Custom_r" tabindex=7>
                                <option value='temp' selected>PROVVISORIA</option>
                                <option value='final'>DEFINITIVA</option>
                            </select>
                        </div>
                    
                            <input type="hidden" id="cod_cat" name="cod_cat" value="<?php echo $c ?>">

                        <!-- <div class="form-group">
                            <label for="gg_da_notifica">GIORNI DALLA NOTIFICA</label>
                            <input type="text" id="gg_da_notifica" name="gg_da_notifica" class="form-control validateCustom vld_Custom_r" aria-label="Small" aria-describedby="inputGroup-sizing-sm" required>
                        </div> -->
                        <div class="btn-group">
                            <button id="stragiudiziale_final" type="button" class="btn btn-primary" onclick="pressButtonStragiudiziale('final')">Elabora</button>
                             <!--<button id="stragiudiziale_temp" type="button" class="btn btn-info"  onclick="pressButtonStragiudiziale('temp')">Stragiudiziale Temporanea</button> -->
                        </div>                     
                            <input type="hidden" id="tipo" name="tipo" value="temp">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="width:95%">
        <table id="tableUserCheck" class="table table-striped table-bordered wrap display" class="table table-striped table-bordered wrap" style="border:3px solid #6D95D5; width:100%; ">
            <thead>
                <tr>
                    <th>Link</th>
                    <th>CC</th>
                    <th>DENOMINAZIONE</th>
                    <th>Utente ID</th>
                    <th>COGNOME/DITTA</th>
                    <th>NOME</th>
                    <th>CF/PI</th>
                    <th>ESITO</th>
                    <th>STATO</th>
                    <th></th>
                    <th>CHECK</th>
                </tr>
            </thead>

            <?php




            $count = count($result);

            for($i=0; $i < $count; $i++){

                ?>

                <tr>
                    <td>
                        <a id="partita" onMouseover="title=\'Dettagli Notifica\'" href="<?php echo  WEB_ROOT ?>/coattiva/ingiunzione.php?partita=<?= $result[$i]["Partita_ID"] ?>&c=<?= $result[$i]["CC"] ?>&pageCalled=" style="text-decoration:none;">'<img src="<?php echo IMMAGINIWEB ?>/select.png" style="width:25px; height:25px; border:0;"></a>
                    </td>
                    <td><?= $result[$i]["CC"] ?></td>
                    <td><?= $result[$i]["Denominazione"] ?></td>
                    <td><?= $result[$i]["Comune_ID"] ?></td>
                    <td><?= $result[$i]["Cognome_Ditta"] ?></td>
                    <td><?= $result[$i]["Nome"] ?></td>
                    <td><?= $result[$i]["CF_PI"] ?></td>
                    <td><?= $result[$i]["ESITO"] ?></td>
                    <td><?= $result[$i]["STATO"] ?></td>

                    <?php

                        $params["Tipo_Riscossione"] = $result[$i]["Tipo_Riscossione"];
                        $block_elab->setParams($params);

                        $addingDay = $block_elab->calcBlockDays([
                                "StartDate" => $result[$i]["Data_Notifica_Atto_Pigno_5"],
                                "EndDate" => date("Y-m-d")
                        ]);

                        $date_1 = new cls_DateTime($result[$i]["Data_Notifica_Atto_Pigno_5"],"DB",false);
                        $date_1->AddDay($addingDay+(365*5));

                        $addingDay = $block_elab->calcBlockDays([
                            "StartDate" => $result[$i]["Data_Notifica_1"],
                            "EndDate" => date("Y-m-d")
                        ]);

                        $date_2 = new cls_DateTime($result[$i]["Data_Notifica_1"],"DB",false);
                        $date_2->AddDay($addingDay+365);

                        $disabled = "";
                        $check = "";

                        if($result[$i]["user_check"] == "Selezionata")
                            $check = "checked";

                        if($date_1->CompareDate("DB","<",date("Y-m-d"))){
                            $disabled = "disabled";
                        }
                        else if($date_2->CompareDate("DB","<",date("Y-m-d"))){
                            $disabled = "disabled";
                        }

                        if($result[$i]["Data_Notifica_Atto"] == null || $result[$i]["Atto_ID"] == null || $result[$i]["ESITO"] == "Pagata Completamente" || $result[$i]["ESITO"] == "Nessun atto presente" || $result[$i]["ESITO"] == "Atto bloccato" || $result[$i]["ESITO"] == "Totale o riscossione non inserito")
                            $disabled = "disabled";

                        $checkbox =  "<input type='checkbox' id='check_sel_".$result[$i]["Partita_ID"]."' value='".$result[$i]["Partita_ID"]."' ".$check." ".$disabled." onchange='updateChecked(this);'>";

                    ?>
                    <td><?= $checkbox ?></td>
                    <td><?= $result[$i]["user_check"] ?></td>
                </tr>

            <?php
            }
            ?>

        </table>
</div>


<script>
    var editor;
    var arrayBlocchi = '<?= json_encode($blocchi); ?>';

    intervalloGiorni = null;

    $(document).ready(function() {
        intervalloGiorni = new PeriodsBlock(arrayBlocchi);

        $.ajax({
            type: "POST",
            async: false,
            url: "ajax/ajax_check_user_stra.php",
            data: {
                c: "<?= $c; ?>"
            },
            success: function(value) {

                if("<?= $CC; ?>" == ""){
                    ShowAlert("2","Compilare i parametri annuali, (In particolare l'importo minimo), per l'anno <?= $a; ?>");
                    //return;
                }

                $('#tableUserCheck').DataTable( {
                    /*ajax: {
                        url: "ajax/search_panels_compile.php",
                        type: "POST",
                        data: {
                            c: '<?= $CC; ?>',
                        }
                    },*/
                    dom: 'Bfrtip',//'PBflrtip',//'PBlfrtip',//Bfrtip
                    /*searchPanes: {
                        initCollapsed: true,
                        //show: true,
                        //columns: [ 7, 8, 10 ]
                    },*/
                    order: [[3, 'asc']],
                    columnDefs:[
                        {
                            searchPanes:{
                                show: false,
                            },
                            targets: [0,1,2,3,4,5,6,9],
                        },
                        {
                            searchPanes:{
                                show: true,
                                initCollapsed: false
                            },
                            targets: [ 7, 8, 10 ],
                        },
                        {
                            searchable: false,
                            orderable: false,
                            targets: [0,9]
                        },
                        {
                            orderable: false,
                            targets: [10]
                        },
                        {
                            visible: false,
                            target: 10
                        }
                    ],
                    buttons: [
                        {
                            extend: 'searchPanes',
                            config: {
                                cascadePanes: true
                            },
                            //text: '<img alt="Search" title="Cerca" src="<?php echo IMMAGINIWEB.'/search_3_icon.png'; ?>" width=36 style="margin:0;padding:0;"  />',
                        },
                        {
                            "extend": 'excel',
                            "text": '<img alt="Print Excell" title="Stampa Excel" src="<?php echo IMMAGINIWEB.'/excel_2_icon.png'; ?>" width=36 style="margin:0;padding:0;"  />',//'<img src="<?php IMMAGINI.'/excell_icon.png'; ?>"></img>',
                            "titleAttr": 'Excel',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4, 5, 6, 7, 8 ]
                            },
                            //"action": newexportaction
                        },
                        {
                            "extend": 'pdf',
                            "text": '<img alt="Print PDF" title="Stampa PDF" src="<?php echo IMMAGINIWEB.'/PDF_icon.png'; ?>" width=36 style="margin:0;padding:0;"  />',//'<button class="btn"><i class="fa fa-file-excel-o" style="color: green;"></i>  PDF</button>',
                            "titleAttr": 'PDF',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4, 5, 6, 7, 8 ]
                            },
                            //"action": newexportaction
                        },
                        {
                            "extend": 'copy',
                            "text": '<img alt="Copy raw" title="Copia righe" src="<?php echo IMMAGINIWEB.'/copy_icon.png'; ?>" width=36 style="margin:0;padding:0;"  />',//'<button class="btn"><i class="fa fa-file-excel-o" style="color: green;"></i>  PDF</button>',
                            "titleAttr": 'COPY',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4, 5, 6, 7, 8 ]
                            },
                            //"action": newexportaction
                        }
                        //'copy',
                        //'csv',
                        //'excel',
                        //'pdf',
                        //'print'
                    ],
                    //serverSide: true,
                    "language": {
                        "url": "<?=DATATABLE?>/dt_IT.json",
                        searchPanes: {
                            collapse: {
                                0: '<div style="height: 46px;"><i class="fa fa-filter fa-lg" style="position: relative;top:37%;" ></i><div>',
                                _: '<div style="height: 46px;"><i class="fa fa-filter fa-lg" style="position: relative;top:37%;"></i> (%d) <div>'
                            }
                        }
                    },
                    processing: true,
                    /*fnInitComplete: function(oSettings, json) {
                        //console.log(this.api().searchPanes.options);
                    },*/
                    /*columns: [
                        { data: "V_stragiudiziali.Partita_ID", render: function ( data, type, row ) {
                                var str = '<a id="partita" onMouseover="title=\'Dettagli Notifica\'" href="<?php echo  WEB_ROOT ?>/coattiva/ingiunzione.php?partita='+data+'&c='+row.V_stragiudiziali.CC+'&pageCalled=" style="text-decoration:none;">';
                                str +=    '<img src="<?php echo IMMAGINIWEB ?>/select.png" style="width:25px; height:25px; border:0;"></a>';

                                return str;
                            }
                        },
                        { data: "V_stragiudiziali.CC" },
                        { data: "V_stragiudiziali.Denominazione" },
                        { data: "V_stragiudiziali.Comune_ID" },
                        { data: "V_stragiudiziali.Cognome_Ditta" },
                        { data: "V_stragiudiziali.Nome" },
                        { data: "V_stragiudiziali.CF_PI" },
                        { data: "V_stragiudiziali.ESITO" },
                        { data: "V_stragiudiziali.STATO" },
                        {
                            data: "V_stragiudiziali.Utente_ID", render: function ( data, type, row )
                            {
                                //console.log(row);

                                //console.log(arrayBlocchi);
                                intervalloGiorni.entrata = row.V_stragiudiziali.Tipo_Riscossione;
                                //console.log("start out --> "+row.V_stragiudiziali.Data_Notifica_Atto_Pigno_5);

                                intervalloGiorni.startDate = row.V_stragiudiziali.Data_Notifica_Atto_Pigno_5;
                                intervalloGiorni.Start();
                                //console.log("Iniziale --> "+row.V_stragiudiziali.Data_Notifica_Atto_Pigno_5+"\nIntervallo --> "+intervalloGiorni.intervall+"\nFinale --> "+intervalloGiorni.addDays(intervalloGiorni.intervall + (356 * 5)));
                                var d2 = new Date(intervalloGiorni.addDays(intervalloGiorni.intervall + (356 * 5)));

                                intervalloGiorni.startDate = row.V_stragiudiziali.Data_Notifica_1;
                                intervalloGiorni.Start();
                                var d3 = new Date(intervalloGiorni.addDays(intervalloGiorni.intervall + 356));

                                var d1 = new Date(row.V_stragiudiziali.Data_Notifica_60);
                                var d4 = new Date( new Date().toJSON().slice(0,10));

                                var check = "";
                                var disabled = "";
                                // Insert Checkbox

                                if(row.V_stragiudiziali.user_check == "Selezionata")
                                    check = "checked";
                                //else {
                                    if (d4 > d2) {
                                        disabled = "disabled";
                                        //check = "";
                                    } else if (d4 > d3) {
                                        disabled = "disabled";
                                        //check = "";
                                    } else if (d4 > d1) {
                                        disabled = "";
                                    } else if (d4 < d1) {
                                        disabled = "";
                                    } else disabled = "";
                                //}

                                if(row.V_stragiudiziali.Data_Notifica_Atto == null || row.V_stragiudiziali.Atto_ID == null || row.V_stragiudiziali.ESITO == "Pagata Completamente")
                                    check = "disabled";
                                return "<input type='checkbox' class='check_sel_"+data+"' value='"+data+"' "+check+" "+disabled+" onchange='updateChecked(this,\"check_sel_"+data+"\");'>";
                            }
                        },
                        { data: "V_stragiudiziali.user_check" }
                    ]*/
                } );
            }
        });

    });

</script>
    <script src="<?= JS; ?>/sweetalert/sweetalert.min.js"></script>
    <script src="<?= JS; ?>/BlockPeriodCalculator.js"></script>
<script>

    function newexportaction(e, dt, button, config) {
        var self = this;
        var oldStart = dt.settings()[0]._iDisplayStart;
        dt.one('preXhr', function (e, s, data) {
            // Just this once, load all data from the server...
            data.start = 0;
            data.length = 2147483647;
            dt.one('preDraw', function (e, settings) {
                // Call the original action function
                if (button[0].className.indexOf('buttons-copy') >= 0) {
                    $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                    $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                        $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                    $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                        $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                    $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                        $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-print') >= 0) {
                    $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                }
                dt.one('preXhr', function (e, s, data) {
                    // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                    // Set the property to what it was before exporting.
                    settings._iDisplayStart = oldStart;
                    data.start = oldStart;
                });
                // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                setTimeout(dt.ajax.reload, 0);
                // Prevent rendering of the full data to the DOM
                return false;
            });
        });
        // Requery the server with the new one-time export settings
        dt.ajax.reload();
    }

    function updateChecked(el){

        var action = "";
        if($(el).is(":checked")) action = "TRUE";
        else action = "FALSE";

        $.ajax({
            type: "POST",
            url: "ajax/ajax_updateInTime_user_stra.php",
            data: {
                "CC" : "<?= $c; ?>",
                "partita_id": $(el).val(),
                "action": action
            },
            cache: false,
            success: function(response){
                var response = JSON.parse(response);

                //console.log(response);
                if(response.esito == "OK")
                {
                    swal({
                        title: "OK!",
                        text:  response.message,
                        icon: "success",
                        timer: 25000,
                        buttons: false
                    });

                }
                else{

                    swal({
                        title: "ERRORE!",
                        text:  response.message,
                        icon: "danger",
                        timer: 5000,
                        buttons: false
                    });

                }

            },
            error: function(error){
                console.log(error)
            }
        });
    }

    function pressButtonStragiudiziale(tipo) {

            $('#form_stragiudiziale').submit();

    }

    /*$('#tableUserCheck').DataTable({
                    processing: true,
                    serverSide: true,
                    //ordering: true,
                    //searching: true,
                    pagingType: 'full_numbers',
                    dom: 'Bfrtip',
                    "language": {
                    "url": "<?=DATATABLE?>/dt_IT.json"
                    },
                    buttons: [
                        {
                            extend: 'searchPanes',
                            config: {
                                cascadePanes: true
                            }
                        }
                    ],
                    ajax: {
                        url: 'ajax/get_data_table_user.php',
                        data: {
                            c: '<?= $CC; ?>',
                            a: '<?= $a; ?>',
                        }
                    },
                    columns: [
                        { data: null, render: function ( data, type, row ) {
                                // Combine the first and last names into a single table field

                                var str = '<a id="partita" onMouseover="title=\'Dettagli Notifica\'" href="<?php echo  WEB_ROOT ?>/coattiva/ingiunzione.php?partita='+data[0]+'&c='+data[1]+'&pageCalled=" style="text-decoration:none;">';
                                str +=    '<img src="<?php echo IMMAGINIWEB ?>/select.png" style="width:25px; height:25px; border:0;"></a>';
                                //console.log(data);
                                return str;
                            }
                        },
                        { data: "1" },
                        { data: "11" },
                        { data: "2" },
                        { data: "28" },
                        { data: "5" },
                        { data: "27" },
                        { data: "25" },
                        { data: "26" },
                        {
                            data: null, render: function ( data, type, row )
                            {

                                var d1 = new Date(data["21"]);
                                var d2 = new Date(data["22"]);
                                var d3 = new Date(data["23"]);
                                var d4 = new Date( new Date().toJSON().slice(0,10));

                                var check = "";
                                // Insert Checkbox

                                if(data["24"] != null) check = "checked";
                                else {
                                    if (d4 > d2) {
                                        check = "";
                                    } else if (d4 > d3) {
                                        check = "";
                                    } else if (d4 > d1) {
                                        check = "checked";
                                    } else if (d4 < d1) {
                                        check = "";
                                    } else check = "";
                                }

                                if(data["9"] == null || data["16"] == null)
                                    check = "disabled";
                                return "<input type='checkbox' id='check_sel_"+data["8"]+"' value='"+data["8"]+"' "+check+" onchange='updateChecked(this);'>";
                            }
                        },
                    ],
                    searchPanes: {
                        initCollapsed: true,
                        columns: [ 4, 7, 8 ]
                    },
                    fnInitComplete: function(oSettings, json) {
                        this.api().searchPanes.rebuildPane();
                    },
                    columnDefs: [
                        {
                            searchPanes: {
                                show: false
                            },
                            targets: [0,1,2,3,5,6,9],
                        },
                        {
                            searchPanes: {
                                show: true,
                            },
                            targets: [4,7,8],
                        },
                        {
                            targets: [0,9],
                            ordering: false,
                            searching: false
                        }

                    ],
                });*/
</script>


<?php

include(INC . "/footer.php");
?>