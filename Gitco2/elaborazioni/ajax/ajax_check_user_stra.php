<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");

$cls_help = new cls_help();
$cls_db = new cls_db();

$c = $cls_help->getVar("c");


/*$query = 'SELECT PT.CC, U.ID AS Utente_ID
FROM partita_tributi AS PT 
LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC AND p_a.Anno = YEAR(CURRENT_DATE()) 
LEFT JOIN utente AS U ON U.ID = PT.Utente_ID 
LEFT JOIN enti_gestiti AS EG ON EG.CC = PT.CC 
LEFT JOIN atto AS A ON A.ID = ( SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11 ) 
LEFT JOIN pagamento AS PA on PT.ID = PA.Partita_ID AND PA.DocumentTypeId is not null 
LEFT JOIN pignoramento_generale AS PG ON PG.ID= (SELECT MAX(ID) FROM pignoramento_generale AS PG1 WHERE PG1.Partita_ID = PT.ID ) AND Stato_Pignoramento != "Archiviato" AND Stato_Pignoramento != "Annullato"
LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica = "debitore" AND NPG.Tipo_Atto_Notificato = "pignoramento"
WHERE PT.Is_Discharged = 0 AND A.DocumentTypeId in (2, 4) AND A.Data_Notifica IS NOT NULL AND (NOW() > ( SELECT P.Data_Pagamento FROM pagamento AS P WHERE P.Atto_ID = A.ID ORDER BY P.Data_Pagamento DESC LIMIT 1) + INTERVAL 60 DAY) 
AND ( IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1) , (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo) , (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) - (select SUM(PA1.Importo) from pagamento as PA1 where PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ) ) > p_a.Importo_Minimo AND PT.CC = "'.$c.'" AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE b_u.Utente_ID = PT.Utente_ID )
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
                WHEN NOW() < A.Data_Notifica + INTERVAL 60 DAY THEN "Attesa 60 gg" COLLATE utf8mb4_unicode_ci
                ELSE "Caso non considerato"
           END
       ) = "Attiva"
GROUP BY PT.ID';*/

$query = 'SELECT PT.CC, PT.ID AS Partita_ID
FROM partita_tributi AS PT 
LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC AND p_a.Anno = YEAR(CURRENT_DATE()) 
LEFT JOIN utente AS U ON U.ID = PT.Utente_ID 
LEFT JOIN enti_gestiti AS EG ON EG.CC = PT.CC 
LEFT JOIN atto AS A ON A.ID = ( SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11 ) 
LEFT JOIN pagamento AS PA on PT.ID = PA.Partita_ID AND PA.DocumentTypeId is not null 
LEFT JOIN pignoramento_generale AS PG ON PG.ID= (SELECT MAX(ID) FROM pignoramento_generale AS PG1 WHERE PG1.Partita_ID = PT.ID ) AND Stato_Pignoramento != "Archiviato" AND Stato_Pignoramento != "Annullato"
LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica = "debitore" AND NPG.Tipo_Atto_Notificato = "pignoramento"

WHERE PT.Is_Discharged = 0 AND A.DocumentTypeId in (2, 4)  

AND PT.CC = "'.$c.'" AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE b_u.Utente_ID = PT.Utente_ID )
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
        )
GROUP BY PT.ID';

/*$query = 'SELECT PT.CC, U.ID AS Utente_ID
FROM partita_tributi AS PT 
LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC AND p_a.Anno = "'.date("Y").'" 
LEFT JOIN utente AS U ON U.ID = PT.Utente_ID 
LEFT JOIN enti_gestiti AS EG ON EG.CC = PT.CC 
LEFT JOIN atto AS A ON A.ID = ( SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11 ) 
LEFT JOIN pagamento AS PA on PT.ID = PA.Partita_ID AND PA.DocumentTypeId is not null 
LEFT JOIN pignoramento_generale AS PG ON PG.ID= (SELECT MAX(ID) FROM pignoramento_generale AS PG1 WHERE PG1.Partita_ID = PT.ID ) AND Stato_Pignoramento != "Archiviato" AND Stato_Pignoramento != "Annullato"
LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica = "debitore" AND NPG.Tipo_Atto_Notificato = "pignoramento"
WHERE PT.Is_Discharged = 0 AND A.DocumentTypeId in (2, 4)
AND ( IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1) , (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo) , (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) - (select SUM(PA1.Importo) from pagamento as PA1 where PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ) ) > p_a.Importo_Minimo AND PT.CC = "'.$c.'" AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE b_u.Utente_ID = PT.Utente_ID )
AND ((
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
                ELSE "Caso non considerato"
           END
       ) = "Attiva" AND PT.Flag_Blocco_Coazione != "si")
GROUP BY PT.ID';*/

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));



$query_insert = "INSERT INTO partite_check_stra (Partita_ID, CC, flag_check) VALUES ";

$count = count($result);
$countInsert = 0;
for($i = 0; $i < $count; $i++){

    $query = "SELECT * FROM partite_check_stra WHERE CC = '".$result[$i]["CC"]."' AND Partita_ID = ".$result[$i]["Partita_ID"];
    $user = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

    if($user == null){
        $countInsert++;
        //if($i == $count - 1) $query_insert .= " (".$result[$i]["Utente_ID"]." ,'".$result[$i]["CC"]."',1);";
         $query_insert .= " (".$result[$i]["Partita_ID"]." ,'".$result[$i]["CC"]."',1),";
    }
}
//echo $query_insert; die;
if($countInsert>0){

    $query_insert[strlen($query_insert)-1] = ";";

    $check = $cls_db->ExecuteQuery($query_insert);

    if(!$check) {
        echo "ERROR";
        die;
    }
}



echo "OK";

