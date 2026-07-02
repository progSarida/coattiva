<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/DataTable_Serverside/ssp_DataTable.php");


$cls_help = new cls_help();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

//var_dump($a);die;
// DB table to use
//$table = 'v_check_partite';
$table = "partita_tributi";
// Table's primary key
//$primaryKey = 'Partita_ID';
$primaryKey = 'ID';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'PT.ID', 'dt' => 0, 'field' => 'ID'),
    array( 'db' => 'PT.CC', 'dt' => 1, 'field' => 'CC' ),
    array( 'db' => 'U.Comune_ID',  'dt' => 2, 'field' => 'Comune_ID' ),
    array( 'db' => 'U.Ditta', 'dt' => 3, 'field' => 'Ditta' ),
    array( 'db' => 'U.Cognome', 'dt' => 4, 'field' => 'Cognome' ),
    array( 'db' => 'U.Nome', 'dt' => 5, 'field' => 'Nome' ),
    array( 'db' => 'U.Codice_Fiscale', 'dt' => 6, 'field' => 'CF' , 'as' => 'CF'),
    array( 'db' => 'U.Partita_Iva', 'dt' => 7, 'field' => 'PI', 'as' => 'PI' ),
    array( 'db' => 'U.ID', 'dt' => 8, 'field' => 'Utente_ID' , 'as' => 'Utente_ID'),
    array( 'db' => 'A.Data_Notifica', 'dt' => 9, 'field' => 'Data_Notifica_Atto' , 'as' => 'Data_Notifica_Atto'),
    array( 'db' => ' SUM(PA.Importo)', 'dt' => 10, 'field' => 'Importo', 'as' => 'Importo' ),
    array( 'db' => ' EG.Denominazione', 'dt' => 11, 'field' => 'Denominazione' ),
    array( 'db' => ' PT.Flag_Blocco_Coazione', 'dt' => 12, 'field' => 'Flag_Blocco_Coazione', 'as' => 'Flag_Blocco_Coazione' ),
    array( 'db' => ' A.Diritto_Riscossione_Minimo', 'dt' => 13, 'field' => 'Diritto_Riscossione_Minimo', 'as' => 'Diritto_Riscossione_Minimo' ),
    array( 'db' => ' A.Diritto_Riscossione_Massimo', 'dt' => 14, 'field' => 'Diritto_Riscossione_Massimo', 'as' => 'Diritto_Riscossione_Massimo' ),
    array( 'db' => ' A.ID', 'dt' => 16, 'field' => 'Atto_ID', 'as' => 'Atto_ID' ),
    array( 'db' => ' A.Data_Notifica + INTERVAL 60 DAY', 'dt' => 17, 'field' => 'Data_not_60', 'as' => 'Data_not_60' ),
    array( 'db' => ' (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1)', 'dt' => 18, 'field' => 'Data_Last_Pag','as' => 'Data_Last_Pag' ),
    array( 'db' => ' A.Totale_Dovuto', 'dt' => 19, 'field' => 'Totale_Dovuto' ),
    array( 'db' => ' p_a.Importo_Minimo', 'dt' => 20, 'field' => 'Importo_Minimo' ),
    array( 'db' => ' (A.Data_Notifica + INTERVAL 60 DAY)', 'dt' => 21, 'field' => 'Data_Notifica_60', "as" => "Data_Notifica_60" ),
    array( 'db' => ' (A.Data_Notifica + INTERVAL 5 YEAR)', 'dt' => 22, 'field' => 'Data_Notifica_5', "as" => "Data_Notifica_5" ),
    array( 'db' => ' (A.Data_Notifica + INTERVAL 1 YEAR)', 'dt' => 23, 'field' => 'Data_Notifica_1', "as" => "Data_Notifica_1" ),
    array( 'db' => ' UKS.user_id', 'dt' => 24, 'field' => 'user_check', "as" => "user_check" ),
    array( 'db' => '( 	CASE
                            WHEN A.ID IS NULL OR A.Data_Notifica IS NULL  THEN "Nessun atto presente" 
                			WHEN PT.Flag_Blocco_Coazione = "si" THEN "Atto bloccato"
    				        WHEN (SELECT SUM(PA1.Importo) FROM pagamento AS PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null) = 0  THEN "NON Pagata"
                			WHEN IF((IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1)  , (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo) , (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) - (SELECT SUM(PA1.Importo) FROM pagamento AS PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null) ) < p_a.Importo_Minimo, 1,2) = 1  THEN "Pagata Completamente"
                            ELSE "Parzialmente Pagata"
                        END )', 'dt' => 25, 'field' => 'ESITO', "as" => "ESITO" ),
    array( 'db' => '( 	CASE
            				WHEN NOW() > A.Data_Notifica + INTERVAL 5 YEAR THEN "Prescritta"
            				WHEN NOW() > A.Data_Notifica + INTERVAL 1 YEAR THEN "Scaduta"
                            WHEN NOW() > A.Data_Notifica + INTERVAL 60 DAY THEN "Attiva"
            				WHEN NOW() < A.Data_Notifica + INTERVAL 60 DAY THEN "Attesa 60 gg"
                            ELSE "Caso non considerato"
            			END )', 'dt' => 26, 'field' => 'STATO', "as" => "STATO" ),
    array( 'db' => 'CONCAT(COALESCE(U.Codice_Fiscale,""),COALESCE(U.Partita_Iva,""))', 'dt' => 27, 'field' => 'CF_PI' , 'as' => 'CF_PI'),
    array( 'db' => 'CONCAT(COALESCE(U.Cognome,""),COALESCE(U.Ditta,""))', 'dt' => 28, 'field' => 'Cognome_Ditta', 'as' => 'Cognome_Ditta' ),

    /*array(
        'db'        => 'salary',
        'dt'        => 5,
        'formatter' => function( $d, $row ) {
            var_dump($row);die;
            return '$'.number_format($d);
        }
    )*/
);

/*$joinQuery = "FROM 	v_check_partite as v_c_p LEFT JOIN parametri_annuali AS p_a ON p_a.CC = v_c_p.CC";
$extraWhere = "v_c_p.DocumentTypeId in (2, 4) AND v_c_p.Data_Notifica_Atto IS NOT NULL 
AND (NOW() > (
    SELECT P.Data_Pagamento 
    FROM pagamento AS P 
    WHERE P.Atto_ID = v_c_p.ID_ATTO 
    ORDER BY P.Data_Pagamento DESC LIMIT 1) + INTERVAL 60 DAY) 
    AND (v_c_p.Totale_Dovuto_ATTO - v_c_p.TOTALE_PAGAMENTI ) > 0 
    AND v_c_p.CC = '" . $c . "' 
    AND p_a.Anno = '" . date('Y') . "' 
    AND p_a.CC = v_c_p.CC 
    AND v_c_p.Totale_Dovuto_ATTO > p_a.Importo_Minimo 
    AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE  b_u.Utente_ID = v_c_p.Utente_ID )";*/

$joinQuery = "FROM partita_tributi AS PT
LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC AND p_a.Anno = ".$a."
LEFT JOIN utente AS U ON U.ID = PT.Utente_ID
LEFT JOIN enti_gestiti AS EG ON EG.CC = PT.CC
LEFT JOIN user_check_stra AS UKS ON UKS.CC = PT.CC AND UKS.user_id = U.ID
LEFT JOIN atto AS A ON A.ID = (
            SELECT MAX(ID) 
            FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11
        )
LEFT JOIN pagamento AS PA on PT.ID = PA.Partita_ID AND PA.DocumentTypeId is not null";
$extraWhere = "PT.Is_Discharged = 0 AND A.DocumentTypeId in (2, 4) AND A.Data_Notifica IS NOT NULL 
AND (NOW() > (
    SELECT P.Data_Pagamento 
    FROM pagamento AS P 
    WHERE P.Atto_ID = A.ID 
    ORDER BY P.Data_Pagamento DESC LIMIT 1) + INTERVAL 60 DAY) 
    AND (
    IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1) 
       , (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo) 
       , (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) - (select SUM(PA1.Importo)  from pagamento as PA1 where PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ) ) > p_a.Importo_Minimo  
    AND p_a.CC = PT.CC 
    AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE  b_u.Utente_ID = PT.Utente_ID )";

$groupBy = "PT.ID";
// SQL server connection information
$sql_details = array(
    'user' => DB_USERNAME,
    'pass' => DB_PASSWORD,
    'db'   => DB_NAME,
    'host' => DB_HOST
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

//var_dump($_GET);die;

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy )
);



/** QUERY ORIGINALE

 * SELECT PT.ID AS Partita_ID, U.ID AS Utente_ID, PT.CC AS CC, CONCAT(COALESCE(U.Cognome,''),COALESCE(U.Ditta,''),' ',COALESCE(U.Nome,'')) AS Ditta_Persona, CONCAT(COALESCE(U.Codice_Fiscale,''),COALESCE(U.Partita_Iva,'')) AS CF_PI, U.Comune_ID AS Utente_Comune_ID, EG.Denominazione AS Denominazione_Ente ,SUM(PA.Importo) AS TOTALE_PAGAMENTI ,
IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1)  , (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo) , (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) AS Totale_Dovuto_ATTO,
A.Data_Notifica AS Data_Notifica_Atto,
p_a.Importo_Minimo,
PT.Flag_Blocco_Coazione

FROM partita_tributi AS PT
LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC
LEFT JOIN utente AS U ON U.ID = PT.Utente_ID
LEFT JOIN enti_gestiti AS EG ON EG.CC = PT.CC
LEFT JOIN atto AS A ON A.ID = (
SELECT MAX(ID)
FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11
)
LEFT JOIN pagamento AS PA on PT.ID = PA.Partita_ID AND PA.DocumentTypeId is not null
WHERE PT.Is_Discharged = 0
GROUP BY PT.ID LIMIT 0,10;

 **/