<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php");

set_time_limit(0);
ini_set('memory_limit', '-1');

include(INC . "/headerAjax.php");
//include(INC . "/menu.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";

include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_LOG.php";

include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_merge.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_Stampe.php";
include_once(CLS .  "/cls_paramUtils.php");
include_once CLS . "/cls_merge.php";

$db = new cls_db();
$help = new cls_help();
$utils = new cls_Utils();

$cls_registry = new cls_registry();
$cls_params = new cls_parameters();
$cls_st = new cls_Stampe();

$log = new LOG();

$print_type = $help->getVar("print_type");

$c = $help->getVar("cod_cat");
$a = $help->getVar("a");
$tax_type = $help->getVar("tax_type");

$form_type_id = $help->getVar("form_type_id");

$ente = $help->getVar("ente");

//$gg_da_notifica = intval($help->getVar("gg_da_notifica"));
$tipo = $help->getVar("tipo");

//var_dump(STRAGIUDIZIALEWEB);die;
?>

<script>
    function startBar() {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text("Inizio elaborazione...");
    }

    function waitBar(text) {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text(text);
    }

    function updateBar(valore) {
        //alert(valore);
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


    function endBar(c, a, proc_id, cod_cat, pathMerge, pathExcel) {

        $("#progressbar").progressbar({
            value: 100
        });
        $("#barlabel").text("Elaborazione terminata!");

        if (proc_id !== null && proc_id != "") {
            /*  swal({
                 title: 'ATTENZIONE',
                 text: "PROCESSO TERMINATO. STAI PER ESSERE REINDIRIZZATO ALLA PAGINA DEI RISULTATI OTTENUTI",
                 icon: 'success',
                 timer: 3000,
                 buttons: false
             }).then((result) => { */
            window.close();
            window.opener.location.replace("<?= WEB_ROOT ?>/elaborazioni/mgmt_stragiudiziale.php?c=" + c + "&a=" + a + "&proc_id=" + proc_id + "&cod_cat=" + c + "#example");

            //window.opener.location.replace("<?= WEB_ROOT ?>/elaborazioni/mgmt_stragiudiziale.php?c=" + c + "&a=" + a + "&proc_id=" + proc_id + "&cod_cat=" + c);
            //console.log("<?= WEB_ROOT ?>/elaborazioni/mgmt_stragiudiziale.php?c=" + c + "&a=" + a + "&proc_id=" + proc_id);
            // })
        } else {

            /*  swal({
                  title: 'ATTENZIONE',
                  text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                  icon: 'warning',
                  timer: 3000,
                  buttons: false
              }).then((result) => { */
            window.close();
            if(pathExcel != undefined)
                window.open(pathExcel,"_blank");
            if(pathMerge != undefined)
                window.open(pathMerge,"_blank");
            /*  })
        } */
        }
    }
</script>
<!-- <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> -->
<table class="table_interna text_center" style="margin-top: 25%;">
    <tr>
        <td><span class="titolo font18 text_center">Elaborazione</span></td>
    </tr>
    <tr>
        <td>
            <div class="table_interna text_center" id="progressbar" style="height:55px;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </td>
    </tr>
    <tr>
        <td><br></td>
    </tr>
    <tr>
        <td>
            <div class="table_interna text_center" id="progressbar2" style="height:55px;">
                <div class="text_center" id="barlabel2"></div>
            </div>
        </td>
    </tr>
</table>

<?php

flush();
ob_flush();
echo "<script>startBar();</script>";
flush();
ob_flush();
flush();
ob_flush();

$query_ente = " SELECT * FROM enti_gestiti WHERE CC = '" . $c . "'";
$a_enti = $db->getArrayLine($db->SelectQuery($query_ente));



/*$and_cod = "";

if ($tax_type !== 'COMPLETO') {

    $and_cod = " AND    v_c_p.Tipo_Riscossione = '" . $tax_type . "'";
}


$query_banca_utente = "SELECT   v_c_p.ID_CRONOLOGICO AS ID_CRONOLOGICO, 
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
                                v_c_p.Totale_Dovuto_ATTO AS Totale_Dovuto_ATTO, 
                                v_c_p.TOTALE_PAGAMENTI AS TOTALE_PAGAMENTI , 
                                v_c_p.Data_Notifica_Atto AS Data_Notifica_Atto , 
                                v_c_p.Tipo_Riscossione AS Tipo_Riscossione, 
                                v_c_p.Utente_Comune_ID AS Utente_Comune_ID , 
                                v_c_p.TIPO_ATTO AS TIPO_ATTO , v_c_p.CC AS CC , v_c_p.Denominazione_Ente AS Denominazione_Ente ," .
    "			( 	CASE 	" .
    "				WHEN v_c_p.TOTALE_PAGAMENTI = 0  THEN 'NON PAGATA' " .
    "				ELSE 'Parzialmente Pagata'	" .
    "				END ) AS CREDITO, " .
    "           (
                                    SELECT count(*)
                                    FROM  parametri_responsabili  as pr
                                    
                                   WHERE
                                    pr.CC = '" . $c . "'
                                    AND pr.Tipo_Riscossione = v_c_p.Tipo_Riscossione

                                ) AS Esiste_Responsabile,
                                (
                                    SELECT  Funzionario_Responsabile
                                    FROM  parametri_responsabili as pr 
                                    
                                    WHERE
                                     pr.CC = '" . $c . "'
                                     AND pr.Tipo_Riscossione = v_c_p.Tipo_Riscossione

                                ) AS Funzionario_Responsabile,
                                (
                                    SELECT 
                                    ( 	CASE 	 
                                        WHEN (Funzionario_Firma = '' AND Funzionario_Firma = 'si' ) THEN NULL
                                        ELSE Funzionario_Firma   
                                        END ) AS Funzionario_Firma
                                    FROM  parametri_responsabili  as pr                                    
                                    WHERE
                                     pr.CC = '" . $c . "'
                                     AND pr.Tipo_Riscossione = v_c_p.Tipo_Riscossione
                                ) AS Funzionario_Firma," .
                        "       b.ID AS Banca_ID, 
                                b.Denominazione AS Denominazione, 
                                b.Toponimo AS Toponimo , 
                                b.Civico AS Civico, 
                                b.Cap AS Cap , 
                                b.Provincia AS Provincia,  
                                b.Comune " .
    "FROM 	v_check_partite_stra as v_c_p " .
    "JOIN partite_check_stra AS UKS ON UKS.CC = v_c_p.CC AND UKS.Partita_ID = v_c_p.Partita_ID AND UKS.flag_check = 1 " .
    "LEFT JOIN banca AS b ON b.Tipo_Banca = 'sede' ".
    "LEFT JOIN parametri_annuali AS p_a ON p_a.Anno = YEAR(CURRENT_DATE()) AND p_a.CC = v_c_p.CC ".
    "WHERE 	v_c_p.DocumentTypeId in (2, 4)	" .
    "AND    v_c_p.Data_Notifica_Atto IS NOT NULL " .
    //"#AND    (NOW() > (SELECT P.Data_Pagamento FROM pagamento AS P WHERE P.Atto_ID = v_c_p.ID_ATTO ORDER BY P.Data_Pagamento DESC LIMIT 1) + INTERVAL " . $gg_da_notifica . " DAY) " .
    "AND    (COALESCE(v_c_p.Totale_Dovuto_ATTO_STRA,0) - COALESCE(v_c_p.TOTALE_PAGAMENTI,0) ) > p_a.Importo_Minimo " .
    "AND    v_c_p.CC = '" . $c . "'	" .
    "AND    v_c_p.Flag_Blocco_Coazione != 'si'  " .
    "AND    v_c_p.Totale_Dovuto IS NOT NULL AND v_c_p.Diritto_Riscossione_Minimo IS NOT NULL AND v_c_p.Diritto_Riscossione_Massimo IS NOT NULL " .
    "AND    v_c_p.ID_ATTO IS NOT NULL AND v_c_p.Data_Notifica_Atto IS NOT NULL  " .
    "AND    (v_c_p.Is_Expired <> 1 OR v_c_p.Is_Expired IS NULL)   " .
    "AND NOT EXISTS (	" .
    "		SELECT * " .
    "		FROM banca_utente AS b_u	" .
    "		WHERE  b_u.Utente_ID = v_c_p.Utente_ID " .
    "		AND    b_u.Banca_ID = b.ID	" .
    ") " . $and_cod;*/

//if($print_type == "temp")
//    $query_banca_utente .= " LIMIT 10 ";

$query_banca_utente = '
SELECT  PT.CC,
        PT.ID AS Partita_ID,
        A.ID_Cronologico AS ID_CRONOLOGICO,
        A.Anno_Cronologico AS ANNO_CRONOLOGICO,
        PT.Utente_ID,
        A.ID AS Atto_Last_ID,
        CONCAT(COALESCE(U.Ditta,""),IF(SRL.ID>0,CONCAT(" ",SRL.Sigla),""),COALESCE(U.Cognome,"")) AS Cognome_Ditta,
        U.Nome, 
        IF(U.Genere="D",U.Partita_Iva,U.Codice_Fiscale) AS CF_PI,
        RES.Comune AS Res_Comune, 
        IF(TOP_RES.ID=1,TOPC_RES.Odonimo,TOP_RES.Nome) AS Res_Via,
        RES.Civico AS Res_Civico,
        EG.Denominazione AS Denominazione_Ente,
        IF((A.Data_Notifica + INTERVAL 60 DAY) > PA.Data_Pagamento , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO,
        SUM(PA.Importo) AS TOTALE_PAGAMENTI,
        A.Data_Notifica AS Data_Notifica_Atto,
        PT.Tipo AS Tipo_Riscossione,
        U.Comune_ID AS Utente_Comune_ID,
        A.Atto AS TIPO_ATTO,
        ( 	
            CASE
                WHEN SUM(PA.Importo) = 0  THEN "NON PAGATA"
    			ELSE "Parzialmente Pagata"	
    		END 
    	) AS CREDITO,
        (
            SELECT count(*)
            FROM  parametri_responsabili  as pr
            WHERE pr.CC = PT.CC AND pr.Tipo_Riscossione = PT.Tipo
        ) AS Esiste_Responsabile,
        (
            SELECT  Funzionario_Responsabile
            FROM  parametri_responsabili as pr 
            WHERE pr.CC = PT.CC AND pr.Tipo_Riscossione = PT.Tipo
        ) AS Funzionario_Responsabile,
        (
            SELECT  Funzionario_Firma
            FROM  parametri_responsabili  as pr                                    
            WHERE pr.CC = PT.CC AND pr.Tipo_Riscossione = PT.Tipo
        ) AS Funzionario_Firma,
        b.ID AS Banca_ID, 
        b.Denominazione AS Denominazione, 
        b.Toponimo AS Toponimo , 
        b.Civico AS Civico, 
        b.Cap AS Cap , 
        b.Provincia AS Provincia,  
        b.Comune

FROM partita_tributi AS PT 
LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC AND p_a.Anno = YEAR(CURRENT_DATE())
JOIN partite_check_stra AS UKS ON UKS.CC = PT.CC AND UKS.Partita_ID = PT.ID AND UKS.flag_check = 1  
LEFT JOIN utente AS U ON U.ID = PT.Utente_ID
LEFT JOIN indirizzo AS RES ON RES.Utente_ID = U.ID AND RES.Tipo="res"
LEFT JOIN toponimo AS TOP_RES ON RES.Via_ID = TOP_RES.ID
LEFT JOIN toponimi_cappati AS TOPC_RES ON RES.Via_Cap_ID = TOPC_RES.ID
LEFT JOIN forma_giuridica_societa AS SRL ON SRL.ID = U.Forma_Giuridica
LEFT JOIN banca AS b ON b.Tipo_Banca = "sede"
LEFT JOIN enti_gestiti AS EG ON EG.CC = PT.CC 
LEFT JOIN atto AS A ON A.ID = ( SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11 ) 
LEFT JOIN pagamento AS PA on PT.ID = PA.Partita_ID AND PA.DocumentTypeId is not null 
LEFT JOIN pignoramento_generale AS PG ON PG.ID= (SELECT MAX(ID) FROM pignoramento_generale AS PG1 WHERE PG1.Partita_ID = PT.ID ) AND Stato_Pignoramento != "Archiviato" AND Stato_Pignoramento != "Annullato"
LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica = "debitore" AND NPG.Tipo_Atto_Notificato = "pignoramento"

WHERE PT.Is_Discharged = 0 AND A.DocumentTypeId in (2, 4)  

AND PT.CC = "' . $c . '" AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE b_u.Utente_ID = PT.Utente_ID AND b_u.Banca_ID = b.ID)
AND (
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
                WHEN NOW() > A.Data_Notifica + INTERVAL 60 DAY THEN "Attiva" COLLATE utf8mb4_unicode_ci
                WHEN NOW() < A.Data_Notifica + INTERVAL 60 DAY THEN "Attesa 60 gg" COLLATE utf8mb4_unicode_ci
                ELSE "Caso non considerato"
           END
       ) = "Attiva"
   AND (
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
        ) = "Parzialmente Pagata"
        OR 
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
        ) = "Non Pagata"
        )';

if ($tax_type !== 'COMPLETO') {

    $query_banca_utente .= ' AND PT.Tipo = "' . $tax_type . '"';
}
$query_banca_utente .= " GROUP BY PT.ID";

$a_results = $db->getResults($db->SelectQuery($query_banca_utente));

//var_dump($a_results);die;

if(count($a_results) == 0) {
    flush();
    ob_flush();
    flush();
    ob_flush();
    echo "<script>noResultsBar();setTimeout(function() {self.close();}, 4000);</script>";
    flush();
    ob_flush();
    flush();
    ob_flush();
    die;
}
//var_dump($a_results);
//die;

$a_resParams = $db->getResults(
    $db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c)),
    "array",
    "Tipo_Riscossione"
);

$a_enteAdmin = $db->getArrayLine( $db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$cls_ente = new cls_ente($a_enteAdmin);

//var_dump($cls_ente->a_ente);die;

$cls_ente->setPrintHeader();
$managerCity = $cls_ente->getCityManager();
$via = isset($cls_ente->a_ente["Gestore_Via"])?$cls_ente->a_ente["Gestore_Via"]:null;
$nr =  isset($cls_ente->a_ente["Gestore_Civico"])?$cls_ente->a_ente["Gestore_Civico"]:null;

//var_dump($via); die;
$indirizzo = "";

if (!empty($via) && !empty($nr) && !is_null($via) && !is_null($nr))
    $indirizzo = $via . "  " . $nr;
else
    $indirizzo = "VIA E CIVICI NON INSERITI";

switch ($cls_ente->type) {
    case "Gestore":
        $manager = "Concessionario " . $cls_ente->a_ente[$cls_ente->type . '_Denominazione'];
        $a_switchParams["Completo_Parziale"] = "completo";
        break;
    default:
        $manager = $cls_ente->a_ente[$cls_ente->type . '_Denominazione'];
        $a_switchParams["Completo_Parziale"] = "parziale";
}

$mergrWebPath = "";
$lastId_procedures = null;

if (count($a_results) > 0) {


    $cls_text = new cls_textParameters();
    $a_text = $db->getArrayLine($db->SelectQuery("SELECT * FROM text_parameters WHERE CC=\"".$c."\" AND Form_Type_ID=\"".$form_type_id."\""));

    $cls_text->html_body = isset($a_text['Content']) ? $a_text['Content'] : null;

    $cls_text->html_replaced_body = $cls_text->html_body;

    $index = 0;

    $msg_res_params = "";

    $a_banche = array();
    $arrPdfPathMerge = array();


    foreach ($a_results as $key => $banca) {

        $a_banche[$banca["Banca_ID"]]["Banca_ID"] =  $banca["Banca_ID"];
        $a_banche[$banca["Banca_ID"]]["Denominazione"] = $banca["Denominazione"];
        $a_banche[$banca["Banca_ID"]]["Toponimo"] = $banca["Toponimo"];
        $a_banche[$banca["Banca_ID"]]["Civico"] = $banca["Civico"];
        $a_banche[$banca["Banca_ID"]]["Cap"] =  $banca["Cap"];
        $a_banche[$banca["Banca_ID"]]["Provincia"] = $banca["Provincia"];
        $a_banche[$banca["Banca_ID"]]["Comune"] = $banca["Comune"];

        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Tipo_Riscossione"] = $banca["Tipo_Riscossione"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Esiste_Responsabile"] = $banca["Esiste_Responsabile"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Funzionario_Responsabile"] = $banca["Funzionario_Responsabile"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Funzionario_Firma"] = $banca["Funzionario_Firma"];

        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Utente_ID"] = $banca["Utente_ID"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["CC"] = $banca["CC"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Denominazione_Ente"] = $banca["Denominazione_Ente"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Atto_Last_ID"] = $banca["Atto_Last_ID"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Cognome_Ditta"] = $banca["Cognome_Ditta"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Nome"] = $banca["Nome"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["CF_PI"] = $banca["CF_PI"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Res_Comune"] = $banca["Res_Comune"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Res_Via"] = $banca["Res_Via"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Denominazione_Ente"] = $banca["Denominazione_Ente"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Res_Civico"] = $banca["Res_Civico"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["TOTALE_PAGAMENTI"] = $banca["TOTALE_PAGAMENTI"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Data_Notifica_Atto"] = $banca["Data_Notifica_Atto"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Utente_Comune_ID"] = $banca["Utente_Comune_ID"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["Tipo_Riscossione"] = $banca["Tipo_Riscossione"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["TOTALE_DOVUTI"][] = $banca["Totale_Dovuto_ATTO"];
        $a_banche[$banca["Banca_ID"]]["Tipi_Riscossione"][$banca["Tipo_Riscossione"]]["Utenti_ID"][$banca["Utente_ID"]]["TIPI_ATTO"][] = $banca["TIPO_ATTO"] . " nr " . $banca["ID_CRONOLOGICO"] . "/" . $banca["ANNO_CRONOLOGICO"];
    }

    $db->Start_Transaction();
    $db->Begin_Transaction();

    $a_tributi = array();
    foreach ($a_banche as $banca) {
        foreach ($banca['Tipi_Riscossione'] as $tipo_ris) {
            $a_tributi[] =  $tipo_ris['Tipo_Riscossione'];
        }
    }
    $a_tributi = array_unique($a_tributi);

    $path = FIRME . "/" . $c . "/";

    try {

        if($print_type != "temp") {
            //  START INSERT DATA IN PROCEDURES
            $a_procedures = array(
                'table' => 'procedures',
                'fields' => array(
                    array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => 1),
                    array('name' => 'Procedure_Status_Id', 'type' => 'int', 'value' => 1),
                    array('name' => 'CC', 'type' => 'string', 'value' => $c),
                    array('name' => 'Datetime', 'type' => 'date', 'value' => date("Y-m-d H:i:s")),
                    array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr']),
                    array('name' => 'Description', 'type' => 'string', 'value' => " Procedura stragiudiziale  del " . date("Y-m-d") . " per l'ente " . $c . " - " . $a_enti['Denominazione'] . " per il tipo riscossione " . $tax_type /*. " per nr giorni di notifica " . $gg_da_notifica*/),
                    array('name' => 'Procedure_Date', 'type' => 'date', 'value' => date("Y-m-d")),

                )
            );

            $lastId_procedures = $db->DbInsert($a_procedures);
        }
        $riscossione = $tax_type == 'COMPLETO' ? NULL : $tax_type;



        $msg_res_params = "";
        $path = FIRME . "/" . $c . "/";

        /* for ($i = 0; $i < count($a_tributi); $i++) {

            if (empty($a_resParams[$a_tributi[$i]]['Tipo_Riscossione'])) {
                $msg_res_params = "ATTENZIONE!!! Parametri responsabili assenti per " . $a_resParams[$a_tributi[$i]]['Tipo_Riscossione'] . "!";
                throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
            } else {

                if ($a_resParams[$a_tributi[$i]]['Funzionario_Testo'] != "si") {
                    if($a_resParams[$a_tributi[$i]]['Funzionario_Firma'] == ""  )
                    {
                        $msg_res_params .= "ATTENZIONE!!! Firma del Legale rappresentante assente per " . $a_tributi[$i] . "!";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
                    if (!is_file($path . $a_resParams[$a_tributi[$i]]['Funzionario_Firma'])) {
                        $msg_res_params .= "ATTENZIONE!!! ASSENZA DEL FILE Funzionario Firma ";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
               }

               if ($a_resParams[$a_tributi[$i]]['Responsabile_Testo'] != "si") {
                    if($a_resParams[$a_tributi[$i]]['Responsabile_Firma'] == ""  )
                    {
                        $msg_res_params .= "ATTENZIONE!!! Firma del Responsabile del Procedimento assente per " . $a_tributi[$i] . "!";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
                    if (!is_file($path . $a_resParams[$a_tributi[$i]]['Responsabile_Testo'])) {
                        $msg_res_params .= "ATTENZIONE!!! ASSENZA DEL FILE Responsabile del Procedimento ";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
                }
                if ($a_resParams[$a_tributi[$i]]['Ufficiale_Firma'] != "si") {
                    if($a_resParams[$a_tributi[$i]]['Ufficiale_Firma'] == ""  )
                    {
                        $msg_res_params .= "ATTENZIONE!!!Firma dell'Ufficiale della Riscossione assente per " . $a_tributi[$i] . "!";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
                    if (!is_file($path . $a_resParams[$a_tributi[$i]]['Ufficiale_Firma'])) {
                        $msg_res_params .= "ATTENZIONE!!! ASSENZA DEL FILE Ufficiale Firma ";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
                }
                if ($a_resParams[$a_tributi[$i]]['Responsabile_Richieste_Firma'] != "si") {
                    if($a_resParams[$a_tributi[$i]]['Responsabile_Richieste_Firma'] == ""  )
                    {
                        $msg_res_params .= "ATTENZIONE!!!Firma del Responsabile delle Richieste assente per " . $a_tributi[$i] . "!";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
                    if (!is_file($path . $a_resParams[$a_tributi[$i]]['Responsabile_Richieste_Firma'])) {
                        $msg_res_params .= "ATTENZIONE!!! ASSENZA DEL FILE Responsabile Richieste Firma ";
                        throw new Exception("Mancanza parametri responsabili o errore dati " . $msg_res_params);
                    }
                }
            } 
        } */

        //var_dump($a_tributi);die;

        $bloccoFirma = $cls_params->getHtmlMultiSignature("{SignLegale}", $a_tributi, $a_resParams, $cls_ente->type);

        //var_dump($a_tributi);die;

        //if($print_type != "temp") {
            $mod_pr = new PHPExcel();
            $titolo_tab = " Report procedura Stragiudiziale  Id " . $lastId_procedures . " del " . date("d/m/Y", strtotime(date("Y-m-d H:i:s"))) . " - " . $c . " - " . $a_enti['Denominazione'];
            $path_proc = $utils->crea_dir(PROCEDURE . "/" . $lastId_procedures);
            $objWriterPR = PHPExcel_IOFactory::createWriter($mod_pr, 'Excel2007');
            $filename_proc = "Report_procedura_" . $lastId_procedures . "_" . date('Y-m-d') . ".xlsx";

            $mod_pr->setActiveSheetIndex(0);
            $rowCountpro = 3;

            $mod_pr->getActiveSheet()->SetCellValue('A1', $titolo_tab)->getColumnDimension('A')->setWidth("100");
            $mod_pr->getActiveSheet()->SetCellValue('A2', "Banca Id")->getColumnDimension('A')->setWidth("60");
            $mod_pr->getActiveSheet()->SetCellValue('B2', "Banca Denominazione")->getColumnDimension('B')->setWidth("60");
            $mod_pr->getActiveSheet()->SetCellValue('C2', "Tipo Riscossione")->getColumnDimension('D')->setWidth("90");
        //}
        $countProgress = count($a_banche);
        $contatoreRecord = 0;
        $countTemp = 0;

        foreach ($a_banche as $banca) {

            if($print_type == "temp" && $countTemp > 0)
                break;

            $countTemp++;

            flush();
            ob_flush();
            flush();
            ob_flush();
            echo "<script>updateBar(" . ceil(($index * 100) / $countProgress) . ");</script>";
            flush();
            ob_flush();
            flush();
            ob_flush();
            $index++;



                $mod_pr->getActiveSheet()->SetCellValue('A' . $rowCountpro, $banca['Banca_ID']);
                $mod_pr->getActiveSheet()->SetCellValue('B' . $rowCountpro, $banca['Denominazione']);
                $mod_pr->getActiveSheet()->SetCellValue('C' . $rowCountpro, $tax_type);
                $rowCountpro++;

            if($print_type != "temp") {
                $a_stragiudiziali = array(
                    'table' => 'stragiudiziali',
                    'fields' => array(
                        array('name' => 'Procedure_Id',      'type' => 'int', 'value' => $lastId_procedures),
                        array('name' => 'Banca_ID',          'type' => 'int', 'value' => $banca["Banca_ID"]),
                        array('name' => 'CC',                'type' => 'string', 'value' => $c),
                        array('name' => 'Tipo_Riscossione',  'type' => 'string', 'value' => $riscossione),
                        array('name' => 'Print_Type_ID',     'type' => 'int', 'value' => 4),

                    )
                );

                $lastId_stragiudiziali = $db->DbInsert($a_stragiudiziali);
            }


            $denominazione = !empty($banca['Denominazione']) ? $banca['Denominazione'] : "DENOMINAZIONE ASSENTE";
            $toponimo = !empty($banca['Toponimo']) ? $banca['Toponimo'] : "TOPONIMO ASSENTE";
            $civico = !empty($banca['Civico']) ? $banca['Civico'] : "CIVICO ASSENTE";
            $cap = !empty($banca['Cap']) ? $banca['Cap'] : "CAP ASSENTE";
            $prov = !empty($banca['Provincia']) ? $banca['Provincia'] : "PROVINCIA ASSENTE";
            $comune = !empty($banca['Comune']) ? $banca['Comune'] : "COMUNE ASSENTE";
            $funz_resp = !empty($banca["Funzionario_Responsabile"]) ? $banca["Funzionario_Responsabile"] : "RESPONSABILE ASSENTE";

            $a_recipent = array(
                'denomination' => array("",$denominazione),
                'addressRow' => array($toponimo . " " . $civico , $cap . " " . $comune . " " . $prov),
            );

//var_dump($banca);die;

            $cls_text = new cls_textParameters();
            $a_subtext = $db->getResults($db->SelectQuery($cls_text->getSubParametersQuery($c,40)));
            $cls_text->html_body = isset($a_text['Content']) ? $a_text['Content'] : null;
            $cls_text->html_replaced_body = $cls_text->html_body;
            $cls_text->replaceSubtext($a_subtext,$a_switchParams);

            //var_dump($indirizzo);die;

            $cls_text->a_var = array(

                "{ENTE}" => strtoupper($cls_ente->a_ente['Info_Denominazione']),
                "{Manager}" => $manager,
                //"{concessionario}" => $manager,
                "{managerContactDetails}" => $indirizzo,
                "{days}" => 30,
                "{PEC_GESTORE}" => $cls_st->GetPecGestore($c),
                "{PARAMETRI FUNZIONARIO}" => $funz_resp,
                "{SignLegale}" =>  $bloccoFirma,
                "{Tributo}" => $a_tributi[0]
            );

            /*$cls_text->a_subtexts = (
                    "{{Concessionario}}"
            )*/

            // PDF

            $a_recipientHeader["ente"] = $cls_ente->a_ente;

            $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

            // START EXCEL

            //if($print_type != "temp") {
                $mod_st = new PHPExcel();
                $intestazione_tab = "DATI RICHIESTI DAL GESTORE";

                $mod_st->setActiveSheetIndex(0);
                $rowCount = 3;

                $configs = "SI, NO";

                $mod_st->getActiveSheet()->SetCellValue('A1', $intestazione_tab)->getColumnDimension('A')->setWidth("70");

                $mod_st->getActiveSheet()->SetCellValue("K1", "DATI LA CUI COMPILAZIONE E' RISERVATA AL TERZO")->getColumnDimension('I')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue("L1", "DESCRIZIONE DETTAGLIATA DEL RAPPORTO")->getColumnDimension('J')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A2', "UTENTE ID")->getColumnDimension('A')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('B2', "CC")->getColumnDimension('B')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('C2', "COGNOME/DENOMINAZIONE")->getColumnDimension('C')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('D2', "NOME")->getColumnDimension('D')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('E2', "CF/P.IVA")->getColumnDimension('E')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('F2', "RES./CON SEDE IN")->getColumnDimension('F')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('G2', "VIA")->getColumnDimension('G')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('H2', "TITOLO")->getColumnDimension('H')->setWidth("350");
                $mod_st->getActiveSheet()->SetCellValue('I2', "IMPORTO DEL DEBITO")->getColumnDimension('I')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue('J2', "ENTE CREDITORE")->getColumnDimension('J')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue("K2", "IL SOGGETTO RISULTA AVERE RAPPORTI CON QUESTO ENTE/ISTITUTO: " . $banca["Denominazione"])->getColumnDimension('K')->setWidth("90");
                $mod_st->getActiveSheet()->SetCellValue("L2", "INIZIO")->getColumnDimension('L')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue("M2", "FINE")->getColumnDimension('M')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue("N2", "IN ESSERE")->getColumnDimension('N')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue("O2", "DESCRIZIONE/TIPOLOGIA")->getColumnDimension('O')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue("P2", "ALTRI INTESTATARI")->getColumnDimension('P')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue("Q2", "IBAN")->getColumnDimension('Q')->setWidth("50");
                $mod_st->getActiveSheet()->SetCellValue("R2", "ALTRO IDENTIFICATIVO")->getColumnDimension('R')->setWidth("80");
                $mod_st->getActiveSheet()->SetCellValue("S2", "CAPIENZA/DISPONIBILITA'")->getColumnDimension('S')->setWidth("80");
                $mod_st->getActiveSheet()->SetCellValue("T2", "EVENTUALI LIMITI DI PIGNORABILITA' CONOSCIUTI DALL'ENTE")->getColumnDimension('T')->setWidth("100");
                $mod_st->getActiveSheet()->SetCellValue("U2", "PRECEDENTI PIGNORAMENTI/SEQUESTRI/CESSIONI -ULTERIORI INFORMAZIONI EX ART. 547 C.p.C.")->getColumnDimension('U')->setWidth("150");

                $mod_st->getActiveSheet()->getProtection()->setSheet(true);
            //}

            $pdf->setDocParams();
            $pdf->SetAutoPageBreak(true);
            $pdf->AddPage("P");
            $pdf->setManagerHeader($cls_ente->a_header);
            $pdf->setRecipientHeader($a_recipent);
            $pdf->SetMargins(7.0, 10.0, 7.0);
            $pdf->ln(0);
            $pdf->SetFont('helvetica', '', 9);

            if($print_type == "temp") {
                $pdf->temporaryPrinting();
                $pdf->SetFont('helvetica', '', 9);
            }

            $funzionari = array();

            foreach ($banca['Tipi_Riscossione'] as $tipo_ris) {



                $funzionari[] = $tipo_ris['Funzionario_Responsabile'];


                    foreach ($tipo_ris['Utenti_ID'] as $utente) {
                        $mod_st->getActiveSheet()->SetCellValue('A' . $rowCount, $utente["Utente_ID"]);
                        $mod_st->getActiveSheet()->SetCellValue('B' . $rowCount, $utente["CC"]);
                        $mod_st->getActiveSheet()->SetCellValue('C' . $rowCount, $utente["Cognome_Ditta"]);
                        $mod_st->getActiveSheet()->SetCellValue('D' . $rowCount, $utente["Nome"]);
                        $mod_st->getActiveSheet()->SetCellValue('E' . $rowCount, $utente["CF_PI"]);
                        $mod_st->getActiveSheet()->SetCellValue('F' . $rowCount, $utente["Res_Comune"]);
                        $mod_st->getActiveSheet()->SetCellValue('G' . $rowCount, $utente["Res_Via"]);

                        $concat_atto = "";

                        foreach ($utente["TIPI_ATTO"] as $doc_type) {
                            $concat_atto .= " - " . $doc_type;
                        }

                        $mod_st->getActiveSheet()->SetCellValue('H' . $rowCount, $concat_atto);

                        $tot_dov = 0;

                        foreach ($utente["TOTALE_DOVUTI"] as $tot_due) {
                            $tot_dov += $tot_due;
                        }

                        $mod_st->getActiveSheet()->SetCellValue('I' . $rowCount, $tot_dov);

                        $mod_st->getActiveSheet()->SetCellValue('J' . $rowCount, $utente["Denominazione_Ente"]);
                        $mod_st->getActiveSheet()->getStyle('K' . ($rowCount) . ':U' . ($rowCount))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                        $objValidation = $mod_st->getActiveSheet()->getCell('K' . $rowCount)->getDataValidation();
                        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                        $objValidation->setAllowBlank(false);
                        $objValidation->setShowInputMessage(true);
                        $objValidation->setShowErrorMessage(true);
                        $objValidation->setShowDropDown(true);
                        $objValidation->setErrorTitle('Input error');
                        $objValidation->setError('IL valore non è presente nella lista.');
                        $objValidation->setPromptTitle('Seleziona');
                        $objValidation->setPrompt('Seleziona un valore dalla drop-down list.');
                        $objValidation->setFormula1('"' . $configs . '"');
                        $mod_st->getActiveSheet()->SetCellValue('L' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('M' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('N' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('O' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('P' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('Q' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('R' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('S' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('T' . $rowCount, "");
                        $mod_st->getActiveSheet()->SetCellValue('U' . $rowCount, "");

                        $rowCount++;

                    if($print_type != "temp") {
                        //  END EXCEL
                        //  if ($tipo == 'final') {
                        $a_banca_utente = array(
                            'table' => 'banca_utente',
                            'fields' => array(
                                array('name' => 'Utente_ID', 'type' => 'int', 'value' => $utente["Utente_ID"]),
                                array('name' => 'Banca_ID', 'type' => 'int', 'value' => $banca["Banca_ID"]),
                                array('name' => 'Stragiudiziale_Id', 'type' => 'int', 'value' => $lastId_stragiudiziali),
                                array('name' => 'Request_Date', 'type' => 'date', 'value' => date('Y-m-d')),
                                array('name' => 'Data_Riscontro', 'type' => 'date', 'value' => NULL),
                                array('name' => 'Importo', 'type' => 'float', 'value' => NULL),
                            )
                        );

                        $lastId_bu = $db->DbInsert($a_banca_utente);

                        $a_dbParams_up_partita = array(
                            'table' => 'partita_tributi',
                            'updateField' => array(
                                array('name' => 'Utente_ID', 'type' => 'int', 'value' => $utente["Utente_ID"]),
                            ),
                            'fields' => array(
                                array('name' => 'HasBanksRequest', 'type' => 'boolean', 'value' => 1),
                                array('name' => 'BanksRequestDate', 'type' => 'date', 'value' => date('Y-m-d')),
                            )
                        );
                        $db->DbSave($a_dbParams_up_partita);
                    }
                }


                //   }
            }

            $cls_text->a_var["{PARAMETRI FUNZIONARIO}"] = implode(', ', $funzionari);
            $cls_text->replaceVariables($cls_text->a_var);

            //var_dump($cls_text->html_replaced_body);die;
            $pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);
            if($print_type == "temp") {
                $pdf->temporaryPrinting();
                $pdf->SetFont('helvetica', '', 9);
            }
            //EXCEL


                $rowCount_dic = $rowCount + 3;

                $modulo = "  \n \n \n DICHIARAZIONE SOSTITUTIVA DELL’ATTO DI NOTORIETA’ \n " .
                    "(Art.47 del D.P.R. 28 dicembre 2000, n. 445)\n \n " .
                    " Il/la sottoscritto/a _________________________________________________________________ \n " .
                    " nato/a a ______________________________________________________________(__________) il ______________________________________________________________________________ \n" .
                    "  codice fiscale ____________________________________________________________________ \n" .
                    " residente in ___________________________________________________________(__________) \n" .
                    " Via/Piazza/, ecc. ______________________________________________________n. __________ \n" .
                    " (se la dichiarazione è resa da società) in qualità di ________________________________________ \n" .
                    "  della società _____________________________________________________________________ \n" .
                    "codice fiscale ________________________________ P.IVA_______________________________ \n" .
                    " con sede legale in ______________________________________________________(__________)\n" .
                    " Via/Piazza/, ecc. ______________________________________________________n. __________ \n" .
                    " con riferimento alle suindicate richieste di dichiarazione stragiudiziale, consapevole delle sanzioni penali comminate in caso di dichiarazioni non veritiere e falsità negli atti, richiamate dall'art. 76 del D.P.R. 445 del 28 dicembre 2000, sotto la propria responsabilità, dichiara che le notizie riportate nella presente dichiarazione sono reali, ed ai sensi dell’art. 38 del D.P.R. 445/2000, allega copia di un documento di identità in corso di validità. \n" .
                    " _______________________li, ____________  ______________________________ \n" .
                    " (firma del dichiarante) ";


                $mod_st->getActiveSheet()->SetCellValue('A' . $rowCount_dic, $modulo)->getRowDimension($rowCount_dic)->setRowHeight(220);

                $mod_st->getActiveSheet()->mergeCells('A1:K1');

                $mod_st->getActiveSheet()->mergeCells('A' . ($rowCount_dic) . ':K' . ($rowCount_dic));

                $mod_st->getActiveSheet()->setTitle("COMPILAZIONE");

                $mod_st->createSheet();

                $mod_st->setActiveSheetIndex(1);

                $mod_st->getActiveSheet()->SetCellValue('A1', "DATI LA CUI COMPILAZIONE E' RISERVATA AL TERZO")->getColumnDimension('A')->setWidth("70");

                $mod_st->getActiveSheet()->mergeCells('A1:E1');

                $mod_st->getActiveSheet()->SetCellValue('A2', 'ISTRUZIONI PER LA COMPILAZIONE')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A3', 'IL SOGGETTO RISULTA AVERE RAPPORTI CON QUESTO ENTE/ISTITUTO-INDICARE SI/NO')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A4', 'INIZIO-INDICARE LA DATA DI INIZIO DEL RAPPORTO')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A5', 'FINE-INDICARE LA DATA DI FINE DEL RAPPORTO')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A6', 'IN ESSERE-INDICAE SI/NO E\' UN CAMPO ALTERNATIVO ALL\'INDICAZIONE DELLA DATA DI INIZIO/FINE DEL RAPPORTO')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A7', 'DESCRIZIONE/TIPOLOGIA-INDICARE LA DESCRIZIONE DEL RAPPORTO: ES. CONTO CORRENTE/LIBRETTO/BUONI FRUTTIFERI/PENSIONE')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A8', 'ALTRI INTESTATARI-INDICARE ALTRI INTESTATARI DEL RAPPORTO SE PRESENTI, ES. COINTESTATARIO DEL CONTO CORRENTE')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A9', 'IBAN-INDICARE GLI ESTREMI NEL CASO IN CUI IL SOGGETTO SIA  TITOLARE DI RAPPORTI CI CONTO CORRENTE. NON INDICARE NEL CASO IN CUI IL SOGGETTO SIA TITOLARE DI PENSIONE')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A10', 'ALTRO IDENTIFICATIVO-INDICARE GLI IDENTIFICATIVI DEL RAPPORTO, ES. GLI ESTREMI DEL LIBRETTO. NON INDICARE NEL CASO IN CUI IL SOGGETTO SIA TITOLARE DI PENSIONE')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A11', 'CAPIENZA/DISPONIBILITA-INDICARE LA CAPIENZA DEL CONTO/DEL LIBRETTO/DEI BUONI FRUTTIFERI O DELL\'IMPORTO DELLA PENSIONE DISPONIBILE')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A12', 'EVENTUALI LIMITI DI PIGNORABILITA\' CONOSCIUTI DALL\'ENTE-INDICARE SE CONOSCITI, COME NEL CASO DI EROGAZIONE DI PENSIONE DI INVALIDITA')->getColumnDimension('A')->setWidth("70");
                $mod_st->getActiveSheet()->SetCellValue('A13', 'PRECEDENTI PIGNORAMENTI/SEQUESTRI/CESSIONI- SPECIFICARE (EX ART. 547 C.P.C.) I SEQUESTRI, PIGNORAMENTI, CESSIONI, PRECEDENTEMENTE NOTIFICATI E CHE AVETE ACCETTATO IN MODO DA COMPRENDERE LA PRIORITA DELLA RICHIESTA')->getColumnDimension('A')->setWidth("70");

                // Rename 2nd sheet
                $mod_st->getActiveSheet()->setTitle("ISTRUZIONI");
                $objWriter = PHPExcel_IOFactory::createWriter($mod_st, 'Excel2007');

                $filename = "Elenco_Stragiudiziale_Banca_" . $c . "_" . $banca["Banca_ID"] . "_" . $tax_type . "_" . date('Y-m-d') . ".xlsx";

            if($print_type != "temp") {
                $path = $utils->crea_dir(STRAGIUDIZIALE . "/" . $lastId_stragiudiziali);
                $nameFile = "Stragiudiziale_Banca_" . $c . "_" . $banca['Banca_ID'] . "_" . $tax_type . "_" . date('Y-m-d') . ".pdf";
            }
            else{
                $path = $utils->crea_dir(STRAGIUDIZIALE . "/temp");
                $nameFile = "Stragiudiziale_Banca_" . $c . "_" . $banca['Banca_ID'] . "_" . $tax_type . "_" . date('Y-m-d') . "_".$contatoreRecord.".pdf";
                $filename = "tempBankExcell.xlsx";
            }

            $completePath = $path . "/" . $nameFile;

            $pdf->Output($completePath, "F");

            $contatoreRecord++;

            $arrPdfPathMerge[] = $completePath;
            //echo $completePath;
            //die;
            // END EXCEL
            //if($print_type != "temp")
                $objWriter->save(str_replace(__FILE__, $path . "/" . $filename, __FILE__));
            
        }

        if($print_type != "temp")
            $objWriterPR->save(str_replace(__FILE__, $path_proc . "/" . $filename_proc, __FILE__)); // SALVO EXCEL PROCEDURA
        else {
            $cls_merge = new cls_merge();
            $cls_merge->setFiles($arrPdfPathMerge);
            $cls_merge->concatFiles(false);

            $pdfPath = $utils->crea_dir(STRAGIUDIZIALE . "/temp");
            $cls_merge->Output($pdfPath . "/mergeStraPdf.pdf", "F");

            $mergrWebPath = STRAGIUDIZIALEWEB . "/temp/mergeStraPdf.pdf";
            $excelWebPath = STRAGIUDIZIALEWEB . "/temp/tempBankExcell.xlsx";
        }

        //var_dump($mergrWebPath);
        //die;

    } catch (mysqli_sql_exception $e) {
        $db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
        $help->alert("ERRORE!!!!!!!!");
        flush();
        ob_flush();
        echo "<script>endBar('" . $c . "'," . $a . ",'" . $lastId_procedures . "','" . $c . "');</script>";
        flush();
        ob_flush();
        flush();
        ob_flush();
        die;
    }

    $db->End_Transaction();
}
flush();
ob_flush();
if($print_type == "temp")
    echo "<script>endBar('" . $c . "'," . $a . ",'" . $lastId_procedures . "','" . $c . "','".$mergrWebPath."','".$excelWebPath."');</script>";
else
    echo "<script>endBar('" . $c . "'," . $a . ",'" . $lastId_procedures . "','" . $c . "');</script>";

flush();
ob_flush();
flush();
ob_flush();


?>