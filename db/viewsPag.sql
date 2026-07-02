CREATE OR REPLACE VIEW v_pagamenti_doc AS 

SELECT A.Data_Stampa AS Data_Stampa_Doc, A.Data_Notifica AS Data_Notifica_Doc, PG.* 
FROM `pagamento` PG 
JOIN atto A ON A.ID=PG.Atto_ID AND A.DocumentTypeId=PG.DocumentTypeId 

UNION 

SELECT NA.Data_Stampa AS Data_Stampa_Doc, NA.Data_Notifica AS Data_Notifica_Doc, PG.* 
FROM `pagamento` PG 
JOIN pignoramento_generale P ON P.ID=PG.Atto_ID AND P.DocumentTypeId=PG.DocumentTypeId 
JOIN notifica_atto NA ON NA.Atto_Notificato_ID=P.ID AND NA.Tipo_Notifica="debitore"
ORDER BY Data_Pagamento DESC;


CREATE OR REPLACE VIEW v_list_atto AS 
SELECT 

atto.ID AS Atto_ID, atto.ID_Cronologico, atto.Anno_Cronologico, atto.Cronologico_Vecchio, atto.Protocollo, atto.Data_Protocollo, atto.PrinterId,
atto.Atto, atto.Data_Elaborazione, atto.Data_Calcolo_Interessi, atto.Data_Stampa, atto.Stato_Stampa, atto.Data_Flusso, atto.Numero_Flusso, atto.Anno_Flusso, atto.FlowId,
atto.Data_Notifica, atto.Stato_Notifica, atto.Motivo_Notifica, atto.Modalita_Notifica, atto.Indirizzo_Validato, atto.Note_Notifica, atto.Atto_Rettificato,
atto.Rielabora_Flag, atto.Rettifica_Flag, atto.Tipo_Ufficiale, atto.Modalita_Stampa, atto.PrintTypeId,
atto.DocumentTypeId, document_type.TableTypeId as TableTypeId, document_type.Description as DocumentType,
atto.Importo, atto.Spese_Notifica_Precedenti, atto.Spese_Notifica, atto.Interessi, atto.Interessi_Precedenti, 
atto.Totale_Dovuto, atto.Diritto_Riscossione_Minimo,
atto.Diritto_Riscossione_Massimo, 
atto.Totale_Dovuto-IFNULL(SUM(PGPREC.Importo),0)+atto.Diritto_Riscossione_Minimo AS TOTALE1, 
atto.Totale_Dovuto-IFNULL(SUM(PGPREC.Importo),0)+atto.Diritto_Riscossione_Massimo AS TOTALE2,
atto.Totale_Rateizzato, 
SUM(PGDOC.Importo) as TOTALE_PAGAMENTI, PGDOC.Data_Pagamento,
atto.Sanzione, atto.Spese_Precedenti, atto.Addizionale, atto.Data_Decorrenza_Interessi, atto.CAN, atto.CAD, atto.Ulteriori_Spese, atto.Note,atto.archived,
atto.Data_Richiesta_Rate, atto.Rate_Previste, atto.Importi_Rate, atto.Scadenze_Rate, atto.Tipo_Totale_Rate, atto.Esito_Richiesta_Rateizzazione, atto.Nominativo_Gestore_Rateizzazione,
atto.Posizione_Gestore_Rateizzazione, atto.Motivazione_Respinta_Rateizzazione, atto.Elaboration_Id AS Atto_Elaboration_Id, atto.Elaboration_List_Id  AS Atto_Elaboration_List_Id,

v_partita.*

FROM atto
JOIN document_type ON document_type.Id = atto.DocumentTypeId
JOIN v_partita ON v_partita.Partita_ID = atto.Partita_ID
LEFT JOIN v_pagamenti_doc PGPREC ON PGPREC.Partita_ID=atto.Partita_ID AND PGPREC.Data_Stampa_Doc<atto.Data_Stampa 
LEFT JOIN v_pagamenti_doc PGDOC ON PGDOC.Partita_ID=atto.Partita_ID AND PGDOC.Data_Stampa_Doc=atto.Data_Stampa 
GROUP BY atto.ID;

CREATE OR REPLACE VIEW `v_list_pignoramento` AS 
SELECT
`PG`.`ID` AS Pignoramento_ID,
`PG`.`Anno_Cronologico`,`PG`.`Anno_Flusso`,`PG`.`Atto_ID`,`PG`.`CC`,`PG`.`Comune_Banca`,`PG`.`Data_Consegna`,
`PG`.`Data_Elaborazione`,`PG`.`Data_Flusso`,`PG`.`Data_Iscrizione_Fermo`,null as Data_Protocollo,`PG`.`Data_Richiesta_Rate`,
`PG`.`Data_Spedizione`,`PG`.`Data_Stampa`,`PG`.`Data_Stato_Pignoramento`, PG.`Esito_Richiesta_Rateizzazione`,
`PG`.`DocumentTypeId`, document_type.TableTypeId as TableTypeId, document_type.Description as DocumentType,
`PG`.`Fase`,`PG`.`FlowId`,`PG`.`ID`,`PG`.`ID_Bollettini_Rateizzazione`,`PG`.`ID_Cronologico`,`PG`.`ID_Esito_Rateizzazione`,`PG`.`ID_Richiesta_Rateizzazione`,
`PG`.`Importi_Rate`,`PG`.`Importo_Dovuto`,`PG`.`Motivazione_Respinta_Rateizzazione`,`PG`.`Nominativo_Gestore_Rateizzazione`,`PG`.`Note`,
`PG`.`Numero_Flusso`,`PG`.`Operatore_Rateizzazione`,`PG`.`Partita_ID`,`PG`.`Posizione_Gestore_Rateizzazione`,`PG`.`PrinterId`,
`PG`.`Protocollo`,`PG`.`Rate_Previste`,`PG`.`Scadenze_Rate`,`PG`.`Spese_Notifica_Debitore`,`PG`.`Spese_Notifica_Terzi`,`PG`.`Stato_Pignoramento`,
`PG`.`Stato_Stampa`,`PG`.`Tipo`,`PG`.`Tipo_Protocollo`,`PG`.`Tipo_Terzi`,`PG`.`Tipo_Totale_Rate`,`PG`.`Tipo_Ufficiale`,
`PG`.`Totale_Dovuto`,`PG`.`Totale_Spese_Accessorie`,`PG`.`Totale_Spese_Notifica`,`PG`.`Elaboration_Id`,

SUM(PGDOC.Importo) as TOTALE_PAGAMENTI, PGDOC.Data_Pagamento,

`notifica_atto`.`Data_Notifica` AS `Data_Notifica`,`notifica_atto`.`Stato_Notifica` AS `Stato_Notifica`,
`notifica_atto`.`Indirizzo_Validato` AS `Indirizzo_Validato`,`notifica_atto`.`Motivo_Notifica` AS `Motivo_Notifica`,
`notifica_atto`.`Modalita_Notifica` AS `Modalita_Notifica`,`notifica_atto`.`Note_Notifica` AS `Note_Notifica`,
`notifica_atto`.`Spese_Notifica` AS `Spese_Notifica`,`notifica_atto`.`CAN` AS `CAN`,`notifica_atto`.`CAD` AS `CAD`,
`notifica_atto`.`Modalita_Stampa` AS `Modalita_Stampa`,`notifica_atto`.`ID` AS `Notifica_ID`, `notifica_atto`.`PrintTypeId` AS `PrintTypeId`,
`document_type`.`Description` AS `Nome_Pignoramento`, A.Elaboration_List_Id,

PRT.Res_ID, PRT.Res_Via, PRT.Res_Presso, PRT.Comune_ID,PRT.Res_CC, PRT.Res_Comune, PRT.Res_Provincia, PRT.Res_Paese, PRT.Res_Frazione,
PRT.Res_Civico, PRT.Res_Esponente, PRT.Res_Interno, PRT.Res_Dettagli, PRT.Res_Cap,PRT.Rec_ID, PRT.Rec_Via, PRT.Rec_Presso,
PRT.Rec_CC, PRT.Rec_Comune, PRT.Rec_Provincia, PRT.Rec_Paese, PRT.Rec_Frazione,PRT.Rec_Civico, PRT.Rec_Esponente, PRT.Rec_Interno,
PRT.Rec_Dettagli, PRT.Rec_Cap,PRT.Dom_ID, PRT.Dom_Via, PRT.Dom_Presso,PRT.Dom_CC, PRT.Dom_Comune, PRT.Dom_Provincia, PRT.Dom_Paese, PRT.Dom_Frazione,
PRT.Dom_Civico, PRT.Dom_Esponente, PRT.Dom_Interno, PRT.Dom_Dettagli, PRT.Dom_Cap,PRT.Ruolo_ID,PRT.Anno_Riferimento,
PRT.Tipo_Riscossione, PRT.`Sottotipo_Riscossione`,PRT.Flag_Blocco_Coazione, PRT.Note_Blocco, PRT.Motivo_Blocco, PRT.Utente_ID_Partita,
PRT.Info_Cartella,PRT.Codici_Tributo, PRT.Importi_Codici_Tributo,PRT.Testi_Codici, PRT.Codici_Scorporo,PRT.Categorie_Scorporo,
PRT.Atto_Last_ID,PRT.Pignoramento_Last_ID,
PRT.Discharge_Date, PRT.Is_Discharged,
PRT.Extraction_Date, PRT.Is_Extracted,
PRT.Denominazione_Ente,

/*dati utente*/
PRT.Utente_ID, PRT.Utente_Comune_ID,PRT.Genere, PRT.Cognome_Ditta,PRT.Nome, PRT.CF_PI,PRT.Data_Nascita, PRT.Comune_Nascita, PRT.Paese_Nascita,
PRT.CC_Nascita, PRT.Utente_Cellulare, PRT.Utente_Email, PRT.Utente_PEC,

PRT.Comune_ID AS Partita_Comune_ID

from `pignoramento_generale` AS `PG`
LEFT JOIN atto as A on PG.Atto_ID = A.ID
JOIN document_type ON document_type.Id = PG.DocumentTypeId
JOIN `v_partita` AS PRT ON `PRT`.`Partita_ID` = `PG`.`Partita_ID`
LEFT JOIN `notifica_atto` on `notifica_atto`.`Atto_Notificato_ID` = `PG`.`ID` and `notifica_atto`.`CC` = `PG`.`CC` and
`notifica_atto`.`Tipo_Atto_Notificato` = 'pignoramento' and `notifica_atto`.`Tipo_Notifica` = 'debitore'
LEFT JOIN v_pagamenti_doc PGDOC ON PGDOC.Atto_ID=PG.ID AND PGDOC.DocumentTypeId=PG.DocumentTypeId
GROUP BY PG.ID
order by `notifica_atto`.`ID`;



CREATE OR REPLACE VIEW `v_list_docs` AS 
SELECT 

acts.*, DT.Description AS DocumentType, DT.TableTypeId, RL.Data_Fornitura,

PT.Comune_ID, PT.Tipo AS Tipo_Riscossione, PT.Sottotipo AS Sottotipo_Riscossione, PT.Flag_Blocco_Coazione, PT.Anno_Riferimento,
TR.Info_Cartella, 

UT.Comune_ID AS Utente_Comune_ID,
UT.Genere, if(UT.Genere="D",CONCAT(UT.Ditta,IF(SRL.ID>0,CONCAT(" ",SRL.Sigla),"")),UT.Cognome) AS Cognome_Ditta, 
if(UT.Genere="D",UT.Partita_Iva,UT.Codice_Fiscale) AS CF_PI,
UT.Cognome, UT.Nome, UT.Ditta,
UT.Data_Nascita, UT.Comune_Nascita, UT.Paese_Nascita, UT.Data_Morte, UT.CC_Nascita, 
UT.Cellulare AS Utente_Cellulare, UT.Mail AS Utente_Email, UT.PEC AS Utente_PEC , UT.InipecLoaded AS InipecLoaded

FROM

(
SELECT
DOC.ID AS Doc_ID, DOC.DocumentTypeId, DOC.CC,

DOC.ID_Cronologico, DOC.Anno_Cronologico, 
DOC.Protocollo, DOC.Tipo_Protocollo, DOC.Data_Protocollo,
DOC.Partita_ID,


DOC.Data_Elaborazione, DOC.Data_Calcolo_Interessi, DOC.Data_Decorrenza_Interessi, 
DOC.Data_Stampa, DOC.Stato_Stampa, 
DOC.Data_Flusso, DOC.Numero_Flusso, DOC.Anno_Flusso, DOC.FlowId,

DOC.Totale_Dovuto-IFNULL(SUM(PGPREC.Importo),0)+DOC.Diritto_Riscossione_Minimo AS TOTALE1, 
DOC.Totale_Dovuto-IFNULL(SUM(PGPREC.Importo),0)+DOC.Diritto_Riscossione_Massimo AS TOTALE2,
0.00 AS TOTALE3,
DOC.Totale_Rateizzato, 
SUM(PGDOC.Importo) as TOTALE_PAGAMENTI, PGDOC.Data_Pagamento,

DOC.Data_Richiesta_Rate, DOC.Rate_Previste, DOC.Importi_Rate, DOC.Scadenze_Rate, DOC.Tipo_Totale_Rate, 

DOC.Elaboration_Id AS Atto_Elaboration_Id, 
DOC.Elaboration_List_Id  AS Atto_Elaboration_List_Id,

DOC.PrinterId, DOC.Tipo_Ufficiale, DOC.PrintTypeId, DOC.Modalita_Stampa, 
DOC.Data_Notifica, DOC.Stato_Notifica, DOC.Motivo_Notifica, DOC.Modalita_Notifica, 
DOC.Indirizzo_Validato, DOC.Note_Notifica

FROM atto AS DOC
LEFT JOIN v_pagamenti_doc PGPREC ON PGPREC.Partita_ID=DOC.Partita_ID AND PGPREC.Data_Stampa_Doc<DOC.Data_Stampa 
LEFT JOIN v_pagamenti_doc PGDOC ON PGDOC.Partita_ID=DOC.Partita_ID AND PGDOC.Data_Stampa_Doc=DOC.Data_Stampa 
GROUP BY DOC.ID HAVING MAX(DOC.Data_Elaborazione)

UNION

SELECT

DOC.ID AS Doc_ID, DOC.DocumentTypeId, DOC.CC,

DOC.ID_Cronologico, DOC.Anno_Cronologico, 
DOC.Protocollo, DOC.Tipo_Protocollo, DOC.Data_Protocollo, 
DOC.Partita_ID,

DOC.Data_Elaborazione, DOC.Data_Calcolo_Interessi, DOC.Data_Decorrenza_Interessi, 
DOC.Data_Stampa, DOC.Stato_Stampa, 
DOC.Data_Flusso, DOC.Numero_Flusso, DOC.Anno_Flusso, DOC.FlowId,

DOC.Importo_Dovuto + DOC.Totale_Spese_Notifica + DOC.Spese_Accessorie_1 AS TOTALE1,
IF(DOC.Spese_Accessorie_2>0,DOC.Importo_Dovuto + DOC.Totale_Spese_Notifica + DOC.Spese_Accessorie_2,0) AS TOTALE2,
IF(DOC.Spese_Accessorie_3>0,DOC.Importo_Dovuto + DOC.Totale_Spese_Notifica + DOC.Spese_Accessorie_3,0) AS TOTALE3,
0 AS Totale_Rateizzato, 
SUM(PGDOC.Importo) as TOTALE_PAGAMENTI, PGDOC.Data_Pagamento,

DOC.Data_Richiesta_Rate, DOC.Rate_Previste, DOC.Importi_Rate, DOC.Scadenze_Rate, DOC.Tipo_Totale_Rate, 

DOC.Elaboration_Id AS Elaboration_Id,
NA.Elaboration_List_Id  AS Atto_Elaboration_List_Id,

NA.PrintTypeId, NA.Tipo_Ufficiale, EL.PrinterId, NA.Modalita_Stampa,
NA.Data_Notifica, NA.Stato_Notifica, NA.Motivo_Notifica, NA.Modalita_Notifica, 
NA.Indirizzo_Validato, NA.Note_Notifica

FROM pignoramento_generale AS DOC
JOIN pignoramento_spese AS PS ON PS.Pignoramento_ID=DOC.ID
LEFT JOIN v_pagamenti_doc PGDOC ON PGDOC.DocumentTypeId=DOC.DocumentTypeId AND PGDOC.Atto_ID=DOC.ID
LEFT JOIN notifica_atto AS NA ON NA.Atto_Notificato_ID=DOC.ID AND NA.Tipo_Notifica="debitore"
LEFT JOIN elaboration_lists AS EL ON EL.Id = NA.Elaboration_List_Id
GROUP BY DOC.ID HAVING MAX(DOC.Data_Elaborazione)
) AS acts
JOIN document_type DT ON DT.Id = acts.DocumentTypeId
JOIN partita_tributi PT ON PT.ID = acts.Partita_ID
JOIN ruolo RL ON RL.ID = PT.Ruolo_ID
JOIN tributo TR ON TR.Partita_ID=PT.ID
JOIN utente UT ON UT.ID=PT.Utente_ID
LEFT JOIN forma_giuridica_societa AS SRL ON SRL.ID = UT.Forma_Giuridica
GROUP BY acts.Doc_ID, acts.DocumentTypeId;


CREATE OR REPLACE VIEW v_last_docs_notificati AS 
SELECT
A.ID AS DocId, A.DocumentTypeId, A.Partita_ID,
A.Rate_Previste, A.Data_Richiesta_Rate,
A.Data_Notifica, CONCAT(A.ID_Cronologico,"/",A.Anno_Cronologico) AS Cronologico, 
A.Totale_Dovuto + IFNULL(A.Diritto_Riscossione_Massimo,0) AS Dovuto, SUM(P.Importo) AS Pagato,
A.PrintTypeId,
ModNOT.Descrizione AS Modalita, 
StaNOT.Descrizione AS Giacenza, 
MotNOT.Descrizione AS Anomalia,
AP.Start_Date AS Data_Registrazione_Ricorso, AP.End_Date AS Data_Chiusura_Ricorso
FROM atto A 

LEFT JOIN appeal AS AP ON A.ID = AP.Act_ID
LEFT JOIN pagamento P ON P.Partita_ID=A.Partita_ID AND P.DocumentTypeId IS NOT NULL
LEFT JOIN parametri_notifica ModNOT ON ModNOT.ID = A.Modalita_Notifica
LEFT JOIN parametri_notifica StaNOT ON StaNOT.ID = A.Stato_Notifica
LEFT JOIN parametri_notifica MotNOT ON MotNOT.ID = A.Motivo_Notifica

WHERE A.ID = ( 

    SELECT AN.ID FROM atto AN
    LEFT JOIN notifiche_importate NI ON NI.DocumentId=AN.ID
    WHERE AN.Data_Notifica IS NOT NULL AND A.Partita_ID = AN.Partita_ID 
    AND 
    ( 
        (
            NI.Immagine_Fronte!="" AND NI.Immagine_Fronte IS NOT NULL AND
            ( 
                ( 
                    AN.Stato_Notifica=28
                    AND NI.CAD_Fronte!="" AND NI.CAD_Fronte IS NOT NULL 
                )
                OR  
                (
                    AN.Stato_Notifica!=28
                )
            )      
        )

        OR 

        AN.Email_Id IS NOT NULL
    )
            
    HAVING MAX(AN.Data_Notifica)

)
GROUP BY A.Partita_ID

UNION

SELECT
PG.ID AS DocId, PG.DocumentTypeId, PG.Partita_ID,
PG.Rate_Previste, PG.Data_Richiesta_Rate,
NPG.Data_Notifica, CONCAT(PG.ID_Cronologico,"/",PG.Anno_Cronologico) AS Cronologico, 
PG.Totale_Dovuto AS Dovuto, SUM(P.Importo) AS Pagato,
NPG.PrintTypeId,
ModNOT.Descrizione AS Modalita, 
StaNOT.Descrizione AS Giacenza, 
MotNOT.Descrizione AS Anomalia,
NULL AS Data_Registrazione_Ricorso, NULL AS Data_Chiusura_Ricorso

FROM pignoramento_generale PG 

JOIN notifica_atto NPG ON NPG.Atto_Notificato_ID=PG.ID AND NPG.Tipo_Notifica="debitore" AND NPG.Tipo_Atto_Notificato="pignoramento"
LEFT JOIN pagamento P ON P.Partita_ID = PG.Partita_ID AND P.DocumentTypeId = PG.DocumentTypeId AND P.Atto_ID=PG.ID
LEFT JOIN parametri_notifica ModNOT ON ModNOT.ID = NPG.Modalita_Notifica
LEFT JOIN parametri_notifica StaNOT ON StaNOT.ID = NPG.Stato_Notifica
LEFT JOIN parametri_notifica MotNOT ON MotNOT.ID = NPG.Motivo_Notifica

WHERE PG.ID = ( 
    SELECT PN.ID FROM pignoramento_generale PN
    LEFT JOIN notifiche_importate NI ON NI.DocumentId=PN.ID
    WHERE PG.Partita_ID = PN.Partita_ID 
    AND 
    ( 
        (
            NI.Immagine_Fronte!="" AND NI.Immagine_Fronte IS NOT NULL AND
            ( 
                ( 
                    NPG.Stato_Notifica=28
                    AND NI.CAD_Fronte!="" AND NI.CAD_Fronte IS NOT NULL 
                )
                OR  
                (
                    NPG.Stato_Notifica!=28
                )
            )      
        )

        OR 

        NPG.Email_Id IS NOT NULL
    )

    HAVING MAX(NPG.Data_Notifica)
)
GROUP BY PG.Partita_ID;
