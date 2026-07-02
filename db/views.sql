
-- 
-- VIEWS COATTIVA
--


-- v_ente_gestito - enti_gestiti con gestore ( INFO [ente] - GEST [concessionario] - UFF [ufficio] )
-- i campi del gestore sono costruiti con {TIPO GESTORE}_{FIELD} es. Info_Denominazione

CREATE OR REPLACE VIEW v_ente_gestito AS 
SELECT 

ENTE.*,

INFO.Tipo AS Info_Tipo, INFO.Denominazione AS Info_Denominazione, INFO.Codice_Fiscale AS Info_CF, INFO.Partita_Iva AS Info_PI,
INFO.Paese AS Info_Paese, INFO.Comune AS Info_Comune, INFO.Provincia AS Info_Provincia, INFO.Frazione AS Info_Frazione,
INFO.Toponimo AS Info_Via, INFO.Civico AS Info_Civico, INFO.Esponente AS Info_Esponente, INFO.Interno AS Info_Interno,
INFO.Dettagli AS Info_Dettagli, INFO.Cap AS Info_Cap, INFO.Telefono AS Info_Telefono, INFO.Fax AS Info_Fax, INFO.Mail AS Info_Mail, INFO.PEC AS Info_PEC,
INFO.Sito AS Info_Sito, INFO.Orario AS Info_Orario, INFO.Stemma AS Info_Stemma,
INFO.Intestatario AS Info_Intestatario,INFO.Conto_Corrente AS Info_Conto_Corrente,INFO.IBAN as Info_IBAN,
INFO.Scadenza_Giorno AS Info_Scadenza_Giorno,INFO.Scadenza_Mese AS Info_Scadenza_Mese,INFO.File_Firma AS Info_File_Firma,

GEST.Tipo AS Gestore_Tipo, GEST.Denominazione AS Gestore_Denominazione, GEST.Codice_Fiscale AS Gestore_CF, GEST.Partita_Iva AS Gestore_PI,
GEST.Paese AS Gestore_Paese, GEST.Comune AS Gestore_Comune, GEST.Provincia AS Gestore_Provincia, GEST.Frazione AS Gestore_Frazione,
GEST.Toponimo AS Gestore_Via, GEST.Civico AS Gestore_Civico, GEST.Esponente AS Gestore_Esponente, GEST.Interno AS Gestore_Interno,
GEST.Dettagli AS Gestore_Dettagli, GEST.Cap AS Gestore_Cap, GEST.Telefono AS Gestore_Telefono, GEST.Fax AS Gestore_Fax, GEST.Mail AS Gestore_Mail, GEST.PEC AS Gestore_PEC,
GEST.Sito AS Gestore_Sito, GEST.Orario AS Gestore_Orario, GEST.Stemma AS Gestore_Stemma,GEST.Abilitazione as Gestore_Abilitazione,
GEST.Intestatario AS Gestore_Intestatario,GEST.Conto_Corrente AS Gestore_Conto_Corrente,GEST.IBAN as Gestore_IBAN,
GEST.Scadenza_Giorno AS Gestore_Scadenza_Giorno,GEST.Scadenza_Mese AS Gestore_Scadenza_Mese,GEST.File_Firma AS Gestore_File_Firma,

UFF.Tipo AS Ufficio_Tipo, UFF.Denominazione AS Ufficio_Denominazione, UFF.Codice_Fiscale AS Ufficio_CF, UFF.Partita_Iva AS Ufficio_PI,
UFF.Paese AS Ufficio_Paese, UFF.Comune AS Ufficio_Comune, UFF.Provincia AS Ufficio_Provincia, UFF.Frazione AS Ufficio_Frazione,
UFF.Toponimo AS Ufficio_Via, UFF.Civico AS Ufficio_Civico, UFF.Esponente AS Ufficio_Esponente, UFF.Interno AS Ufficio_Interno,
UFF.Dettagli AS Ufficio_Dettagli, UFF.Cap AS Ufficio_Cap, UFF.Telefono AS Ufficio_Telefono, UFF.Fax AS Ufficio_Fax, UFF.Mail AS Ufficio_Mail, UFF.PEC AS Ufficio_PEC,
UFF.Sito AS Ufficio_Sito, UFF.Orario AS Ufficio_Orario, UFF.Stemma AS Ufficio_Stemma,
UFF.Intestatario AS Ufficio_Intestatario,UFF.Conto_Corrente AS Ufficio_Conto_Corrente,UFF.IBAN as Ufficio_IBAN,
UFF.Scadenza_Giorno AS Ufficio_Scadenza_Giorno,UFF.Scadenza_Mese AS Ufficio_Scadenza_Mese,UFF.File_Firma AS Ufficio_File_Firma


FROM enti_gestiti AS ENTE
LEFT JOIN gestore AS INFO ON INFO.ID=ENTE.Info_ID
LEFT JOIN gestore AS GEST ON GEST.ID=ENTE.Gestore_ID
LEFT JOIN gestore AS UFF ON UFF.ID=ENTE.Ufficio_ID;


-- v_utente - Utente con forma_giuridica_societa

CREATE OR REPLACE VIEW v_utente AS 

SELECT 

utente.ID AS Utente_ID, utente.Comune_ID AS Utente_Comune_ID, utente.CC_Comune AS CC, 
utente.Genere, if(utente.Genere="D",CONCAT(utente.Ditta,IF(SRL.ID>0,CONCAT(" ",SRL.Sigla),"")),utente.Cognome) AS Cognome_Ditta, 
utente.Nome, if(utente.Genere="D",utente.Partita_Iva,utente.Codice_Fiscale) AS CF_PI,utente.Cognome,utente.Ditta,
utente.Data_Nascita, utente.Comune_Nascita, utente.Provincia_Nascita, utente.Paese_Nascita, utente.Data_Morte,
utente.CC_Nascita, utente.Cellulare AS Utente_Cellulare, utente.Mail AS Utente_Email, utente.PEC AS Utente_PEC , utente.InipecLoaded AS InipecLoaded

FROM utente
LEFT JOIN forma_giuridica_societa AS SRL ON SRL.ID = utente.Forma_Giuridica
ORDER BY utente.ID;


-- v_anagrafe - Utente con indirizzo residenza, recapito e domicilio
-- VIEWS v_utente

CREATE OR REPLACE VIEW v_anagrafe AS 
SELECT 

v_utente.*,

RES.ID AS Res_ID, if(TOP_RES.ID=1,TOPC_RES.Odonimo,TOP_RES.Nome) AS Res_Via, RES.Presso AS Res_Presso,
RES.CC_Indirizzo AS Res_CC, RES.Comune AS Res_Comune, RES.Provincia AS Res_Provincia, RES.Paese AS Res_Paese, RES.Frazione AS Res_Frazione,
RES.Civico AS Res_Civico, RES.Esponente AS Res_Esponente, RES.Interno AS Res_Interno, RES.Dettagli AS Res_Dettagli, RES.Cap AS Res_Cap,

REC.ID AS Rec_ID, if(TOP_REC.ID=1,TOPC_REC.Odonimo,TOP_REC.Nome) AS Rec_Via, REC.Presso AS Rec_Presso,
REC.CC_Indirizzo AS Rec_CC, REC.Comune AS Rec_Comune, REC.Provincia AS Rec_Provincia, REC.Paese AS Rec_Paese, REC.Frazione AS Rec_Frazione,
REC.Civico AS Rec_Civico, REC.Esponente AS Rec_Esponente, REC.Interno AS Rec_Interno, REC.Dettagli AS Rec_Dettagli, REC.Cap AS Rec_Cap,

DOM.ID AS Dom_ID, if(TOP_DOM.ID=1,TOPC_DOM.Odonimo,TOP_DOM.Nome) AS Dom_Via, DOM.Presso AS Dom_Presso,
DOM.CC_Indirizzo AS Dom_CC, DOM.Comune AS Dom_Comune, DOM.Provincia AS Dom_Provincia, DOM.Paese AS Dom_Paese, DOM.Frazione AS Dom_Frazione,
DOM.Civico AS Dom_Civico, DOM.Esponente AS Dom_Esponente, DOM.Interno AS Dom_Interno, DOM.Dettagli AS Dom_Dettagli, DOM.Cap AS Dom_Cap

FROM v_utente
-- GV 17/06/2022 START  
-- JOIN indirizzo AS RES ON RES.Utente_ID = v_utente.Utente_ID AND RES.Tipo="res"
LEFT JOIN indirizzo AS RES ON RES.Utente_ID = v_utente.Utente_ID AND RES.Tipo="res"
-- GV 17/06/2022   END 
LEFT JOIN toponimo AS TOP_RES ON RES.Via_ID = TOP_RES.ID
LEFT JOIN toponimi_cappati AS TOPC_RES ON RES.Via_Cap_ID = TOPC_RES.ID
LEFT JOIN indirizzo AS REC ON REC.Utente_ID = v_utente.Utente_ID AND REC.Tipo="rec"
LEFT JOIN toponimo AS TOP_REC ON REC.Via_ID = TOP_REC.ID
LEFT JOIN toponimi_cappati AS TOPC_REC ON REC.Via_Cap_ID = TOPC_REC.ID
LEFT JOIN indirizzo AS DOM ON DOM.Utente_ID = v_utente.Utente_ID AND DOM.Tipo="dom"
LEFT JOIN toponimo AS TOP_DOM ON DOM.Via_ID = TOP_DOM.ID
LEFT JOIN toponimi_cappati AS TOPC_DOM ON DOM.Via_Cap_ID = TOPC_DOM.ID;



-- v_partita_info - partita contabile con record completo di partita_tributi e Info_Cartella presa da tributo

CREATE OR REPLACE VIEW `v_partita_info` AS 
SELECT 

`par`.`ID` AS `Partita_ID`, `par`.`Ruolo_ID` AS `Ruolo_ID`, `par`.`Comune_ID` AS `Comune_ID`, `par`.`Anno_Riferimento` AS `Anno_Riferimento`, 
`par`.`Tipo` AS `Tipo_Riscossione`, `par`.`Sottotipo` AS `Sottotipo_Riscossione`, `par`.`Flag_Blocco_Coazione` AS `Flag_Blocco_Coazione`, 
`par`.`Note_Blocco` AS `Note_Blocco`, `par`.`Motivo_Blocco` AS `Motivo_Blocco`, `par`.`Utente_ID` AS `Utente_ID`,
`par`.`Discharge_Date` AS `Discharge_Date`,`par`.`Is_Discharged` AS `Is_Discharged`,
`par`.`Extraction_Date` AS `Extraction_Date`,`par`.`Is_Extracted` AS `Is_Extracted`,
`tributo`.`Info_Cartella` AS `Info_Cartella` 

FROM `partita_tributi` `par` 
join `tributo` on `tributo`.`Partita_ID` = `par`.`ID`
GROUP BY `par`.`ID` ;



-- v_dettagliopartita - atto unito a pignoramento_generale con partita_tributi, utente e pagamento group by progressivo atto e pignoramento

CREATE OR REPLACE VIEW v_dettaglio_partita AS 
SELECT 

DOC.ID AS Doc_ID, DOC.Atto AS Tipo_Doc, DOC.CC, DOC.ID_Cronologico, DOC.Anno_Cronologico, DOC.Info_Cartella,
DOC.Numero_Flusso, DOC.Anno_Flusso, DOC.Data_Stampa, DOC.Data_Flusso, DOC.Partita_ID, 
1 as orderType,

SUM(pagamento.Importo) AS Totale_Pagato,
DOC.Totale_Dovuto, DOC.Importo AS Importo_Ruolo, DOC.Spese_Notifica AS Spese_Atto, DOC.Spese_Notifica_Precedenti AS Spese_Precedenti_Atto, 
DOC.Interessi AS Interessi, DOC.Interessi_Precedenti AS Interessi_Precedenti, 0.00 AS Spese_Pignoramento, 0.00 AS Spese_Accessorie,

partita_tributi.Comune_ID, partita_tributi.Utente_ID, partita_tributi.Tipo AS Tipo_Riscossione, partita_tributi.Anno_Riferimento,
partita_tributi.Flag_Blocco_Coazione, partita_tributi.Discharge_Date AS Discharge_Date, partita_tributi.Is_Discharged AS Is_Discharged,
partita_tributi.Extraction_Date AS Extraction_Date, partita_tributi.Is_Extracted AS Is_Extracted,

if(utente.Ditta="",utente.Cognome,utente.Ditta) AS Cognome_Ditta, utente.Nome,if(utente.Genere="D",utente.Partita_Iva,utente.Codice_Fiscale) AS CF_PI

FROM atto AS DOC
JOIN partita_tributi ON partita_tributi.ID = DOC.Partita_ID
JOIN utente ON partita_tributi.Utente_ID = utente.ID
LEFT JOIN pagamento ON pagamento.Atto_ID = DOC.ID AND pagamento.Partita_ID=partita_tributi.ID 
AND pagamento.Tipo_Atto = DOC.Atto AND pagamento.CC=DOC.CC
WHERE DOC.CC!="ZZZZ"
GROUP BY DOC.ID

UNION

SELECT 

DOC.ID AS Doc_ID, CONCAT("Pignoramento ",DOC.Tipo," ",DOC.Tipo_Terzi) AS Tipo_Doc, DOC.CC, DOC.ID_Cronologico, DOC.Anno_Cronologico, atto.Info_Cartella,
DOC.Numero_Flusso, DOC.Anno_Flusso, DOC.Data_Stampa, DOC.Data_Flusso, DOC.Partita_ID,
2 as orderType,

SUM(pagamento.Importo) AS Totale_Pagato,
DOC.Totale_Dovuto, DOC.Importo_Dovuto AS Importo_Ruolo, atto.Spese_Notifica AS Spese_Atto, atto.Spese_Notifica_Precedenti AS Spese_Precedenti_Atto, 
atto.Interessi AS Interessi, atto.Interessi_Precedenti AS Interessi_Precedenti, DOC.Totale_Spese_Notifica AS Spese_Pignoramento, DOC.Totale_Spese_Accessorie AS Spese_Accessorie,

partita_tributi.Comune_ID, partita_tributi.Utente_ID, partita_tributi.Tipo AS Tipo_Riscossione,  partita_tributi.Anno_Riferimento,
partita_tributi.Flag_Blocco_Coazione, partita_tributi.Discharge_Date AS Discharge_Date, partita_tributi.Is_Discharged AS Is_Discharged,
partita_tributi.Extraction_Date AS Extraction_Date, partita_tributi.Is_Extracted AS Is_Extracted,

if(utente.Ditta="",utente.Cognome,utente.Ditta) AS Cognome_Ditta, utente.Nome,if(utente.Genere="D",utente.Partita_Iva,utente.Codice_Fiscale) AS CF_PI

FROM pignoramento_generale AS DOC
LEFT JOIN atto ON DOC.Atto_ID = atto.ID
JOIN partita_tributi ON partita_tributi.ID = DOC.Partita_ID
JOIN utente ON partita_tributi.Utente_ID = utente.ID
LEFT JOIN pagamento ON pagamento.Atto_ID = DOC.ID AND pagamento.Partita_ID=partita_tributi.ID 
AND SUBSTR(pagamento.Tipo_Atto FROM 1 FOR 12) = "Pignoramento" AND pagamento.CC=DOC.CC
WHERE DOC.CC!="ZZZZ"
GROUP BY DOC.ID

ORDER BY Partita_ID, orderType, Doc_ID;



-- v_max_atto - Atto con progressivo più alto group by per partita

CREATE OR REPLACE VIEW v_max_atto AS 
SELECT 
`Partita_ID`, MAX(ID) AS ID 
FROM atto WHERE DocumentTypeId!=3 AND DocumentTypeId!=11
GROUP BY `Partita_ID`;



-- v_max_pigno - Pignoramento con progressivo più alto group by per partita

CREATE OR REPLACE VIEW v_max_pigno AS 
SELECT 
`Partita_ID`, MAX(ID) AS ID 
FROM pignoramento_generale 
GROUP BY `Partita_ID` ;



-- v_partita_tributi - partita_tributi con record tributo raccolti con GROUP_CONCAT e progressivo ultimo atto e progressivo ultimo pignoramento
-- VIEWS v_max_atto e v_max_pigno

CREATE OR REPLACE VIEW v_partita_tributi AS 
SELECT

PAR.ID AS Partita_ID, PAR.Ruolo_ID AS Ruolo_ID, PAR.Comune_ID AS Comune_ID,PAR.Anno_Riferimento AS Anno_Riferimento, 
PAR.Tipo AS Tipo_Riscossione, PAR.`Sottotipo` AS `Sottotipo_Riscossione`,
PAR.Flag_Blocco_Coazione AS Flag_Blocco_Coazione, PAR.Flag_Blocco_Diritto_Riscossione AS Flag_Blocco_Diritto_Riscossione,
PAR.Note_Blocco, PAR.Motivo_Blocco, PAR.Utente_ID AS Utente_ID_Partita,
PAR.Discharge_Date AS Discharge_Date, PAR.Is_Discharged AS Is_Discharged,
PAR.Extraction_Date AS Extraction_Date, PAR.Is_Extracted AS Is_Extracted,
PAR.Extraction_ID AS Extraction_ID, PAR.Flag_Sgravio, PAR.Sgravio_Activation_Date, PAR.Note_Blocco_Sgravio as Note_Sgravio,
PAR.Flag_Sospensione, PAR.Flag_Coobbligati,
-- GV 28/06/2022  START 
PAR.Elaboration_Id AS Elaboration_Id,
PAR.Position_Status_Id AS Position_Status_Id,
PAR.flag_elaboration  AS flag_elaboration,
-- GV 28/06/2022    END 
RUO.Data_Fornitura,
ENT.Denominazione AS Denominazione_Ente,
PAR.Is_Expired,

tributo.Info_Cartella, tributo.Data_Decorrenza_Interessi as Partita_Data_Decorrenza,
GROUP_CONCAT(tributo.Codice_Tributo SEPARATOR '*') AS Codici_Tributo, GROUP_CONCAT(tributo.Imposta SEPARATOR '*') AS Importi_Codici_Tributo,
GROUP_CONCAT(codice_tributo.Testo_Codice SEPARATOR '*') AS Testi_Codici, GROUP_CONCAT(codice_tributo.Codice_Scorporo SEPARATOR '*') AS Codici_Scorporo,
GROUP_CONCAT(codice_tributo.Tipo_Codice SEPARATOR '*') AS Tipo_Codice, 
GROUP_CONCAT(split_payment.category SEPARATOR '*') AS Categorie_Scorporo, GROUP_CONCAT(tributo.Anno_Tributo SEPARATOR '*') AS Anni_Tributo,

A.ID AS Atto_Last_ID, 
PIG.ID AS Pignoramento_Last_ID

FROM partita_tributi AS PAR
JOIN ruolo AS RUO ON RUO.ID=PAR.Ruolo_ID
JOIN enti_gestiti as ENT ON ENT.CC=PAR.CC
JOIN tributo ON tributo.Partita_ID = PAR.ID
JOIN codice_tributo ON codice_tributo.Codice_Tributo = tributo.Codice_Tributo
LEFT JOIN split_payment ON split_payment.ID = codice_tributo.Codice_Scorporo
LEFT JOIN v_max_atto AS A
ON PAR.ID = A.`Partita_Id`
LEFT JOIN v_max_pigno AS PIG
ON PAR.ID = PIG.`Partita_Id`
GROUP BY PAR.ID;



-- v_partita - partita_tributi con utente e indirizzo
-- VIEWS v_anagrafe e v_partita_tributi

CREATE OR REPLACE VIEW v_partita AS 
SELECT 

PAR.*, 

ANAGRAFE.*

FROM v_anagrafe AS ANAGRAFE
JOIN v_partita_tributi AS PAR ON PAR.Utente_ID_Partita = ANAGRAFE.Utente_ID
GROUP BY PAR.Partita_ID;



-- v_atti - atto con partita tributi, utente e indirizzo (v_partita) e con pagamenti
-- VIEWS v_partita
--
-- TODO - CONTROLLARE LA TABLE pagamento PER SISTEMARE DocumentTableTypeId e DocumentTypeId

CREATE OR REPLACE VIEW v_atti AS 
SELECT 

atto.ID AS Atto_ID, atto.ID_Cronologico, atto.Anno_Cronologico, atto.Cronologico_Vecchio, atto.Protocollo, atto.Data_Protocollo, atto.PrinterId,
atto.Atto, atto.Data_Elaborazione, atto.Data_Calcolo_Interessi, atto.Data_Stampa, atto.Stato_Stampa, atto.Data_Flusso, atto.Numero_Flusso, atto.Anno_Flusso, atto.FlowId,
atto.Data_Notifica, atto.Stato_Notifica, atto.Motivo_Notifica, atto.Modalita_Notifica, atto.Indirizzo_Validato, atto.Note_Notifica, atto.Atto_Rettificato,
atto.Rielabora_Flag, atto.Rettifica_Flag, atto.Tipo_Ufficiale, atto.Modalita_Stampa, atto.PrintTypeId,
atto.DocumentTypeId, document_type.TableTypeId as TableTypeId, document_type.Description as DocumentType,
atto.Importo, atto.Spese_Notifica_Precedenti, atto.Spese_Notifica, atto.Interessi, atto.Interessi_Precedenti, atto.Totale_Dovuto, atto.Diritto_Riscossione_Minimo,
atto.Diritto_Riscossione_Massimo, atto.Sanzione, atto.Spese_Precedenti, atto.Addizionale, atto.Data_Decorrenza_Interessi, atto.CAN, atto.CAD, atto.Ulteriori_Spese, atto.Note,
atto.Data_Richiesta_Rate, atto.Rate_Previste, atto.Importi_Rate, atto.Scadenze_Rate, atto.Tipo_Totale_Rate, atto.Esito_Richiesta_Rateizzazione, atto.Nominativo_Gestore_Rateizzazione,
atto.Posizione_Gestore_Rateizzazione, atto.Motivazione_Respinta_Rateizzazione, atto.Elaboration_Id AS Atto_Elaboration_Id, atto.Elaboration_List_Id  AS Atto_Elaboration_List_Id,
atto.Spese_Notifica_Pignoramento, atto.Spese_Accessorie_Pignoramento,atto.archived,


SUM(PAG_ATTO.Importo) AS Totale_Pagato, GROUP_CONCAT(PAG_ATTO.Importo SEPARATOR '*') AS Importi_Atti_Pagati, GROUP_CONCAT(PAG_ATTO.Atto_ID SEPARATOR '*') AS ID_Atti_Pagati,
GROUP_CONCAT(PAG_ATTO.Data_Pagamento SEPARATOR '*') AS Date_Pagamenti,

v_partita.*

FROM atto
JOIN document_type ON document_type.Id = atto.DocumentTypeId
JOIN v_partita ON v_partita.Partita_ID = atto.Partita_ID
LEFT JOIN pagamento AS PAG_ATTO ON PAG_ATTO.Partita_ID = atto.Partita_ID AND PAG_ATTO.DocumentTableTypeId = 1 AND PAG_ATTO.Atto_ID>0 AND PAG_ATTO.Atto_ID<=atto.ID 
GROUP BY atto.ID;


-- v_pignoramento - pignoramento_generale con notifica_atto

CREATE OR REPLACE VIEW `v_pignoramento` AS 
SELECT

`PG`.`Anno_Cronologico`,`PG`.`Anno_Flusso`,`PG`.`Atto_ID`,`PG`.`CC`,`PG`.`Comune_Banca`,`PG`.`Data_Consegna`,
`PG`.`Data_Elaborazione`,`PG`.`Data_Flusso`,`PG`.`Data_Iscrizione_Fermo`,null as Data_Protocollo,`PG`.`Data_Richiesta_Rate`,
`PG`.`Data_Spedizione`,`PG`.`Data_Stampa`,`PG`.`Data_Stato_Pignoramento`, PG.`Esito_Richiesta_Rateizzazione`,
`PG`.`DocumentTypeId`, document_type.TableTypeId as TableTypeId, document_type.Description as DocumentType,
`PG`.`Fase`,`PG`.`FlowId`,`PG`.`ID`,`PG`.`ID_Bollettini_Rateizzazione`,`PG`.`ID_Cronologico`,`PG`.`ID_Esito_Rateizzazione`,`PG`.`ID_Richiesta_Rateizzazione`,
`PG`.`Importi_Rate`,`PG`.`Importo_Dovuto`,`PG`.`Motivazione_Respinta_Rateizzazione`,`PG`.`Nominativo_Gestore_Rateizzazione`,`PG`.`Note`,
`PG`.`Numero_Flusso`,`PG`.`Operatore_Rateizzazione`,`PG`.`Partita_ID`,`PG`.`Pignoramento_ID`,`PG`.`Posizione_Gestore_Rateizzazione`,`PG`.`PrinterId`,
`PG`.`Protocollo`,`PG`.`Rate_Previste`,`PG`.`Scadenze_Rate`,`PG`.`Spese_Notifica_Debitore`,`PG`.`Spese_Notifica_Terzi`,`PG`.`Stato_Pignoramento`,
`PG`.`Stato_Stampa`,`PG`.`Tipo`,`PG`.`Tipo_Protocollo`,`PG`.`Tipo_Terzi`,`PG`.`Tipo_Totale_Rate`,`PG`.`Tipo_Ufficiale`,
`PG`.`Totale_Dovuto`,`PG`.`Totale_Spese_Accessorie`,`PG`.`Totale_Spese_Notifica`,`PG`.`Elaboration_Id`,

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
PRT.CC_Nascita, PRT.Utente_Cellulare, PRT.Utente_Email, PRT.Utente_PEC

from `pignoramento_generale` AS `PG`
LEFT JOIN atto as A on PG.Atto_ID = A.ID
JOIN document_type ON document_type.Id = PG.DocumentTypeId
JOIN `v_partita` AS PRT ON `PRT`.`Partita_ID` = `PG`.`Partita_ID`
left join `notifica_atto` on `notifica_atto`.`Atto_Notificato_ID` = `PG`.`ID` and `notifica_atto`.`CC` = `PG`.`CC` and
`notifica_atto`.`Tipo_Atto_Notificato` = 'pignoramento' and `notifica_atto`.`Tipo_Notifica` = 'debitore'
order by `notifica_atto`.`ID`;



-- v_atti_pigno - union di atto e v_pignoramento
-- VIEWS v_pignoramento

CREATE OR REPLACE VIEW v_atti_pigno AS 

SELECT 

`atto`.`ID` AS `ID`, `atto`.`DocumentTypeId`, `document_type`.TableTypeId AS TableTypeId, `document_type`.Description as DocumentType,
`atto`.`CC` AS `CC`, `atto`.`Partita_ID` AS `Partita_ID`, `atto`.`Tipo_Protocollo` AS `Tipo_Protocollo`,
`atto`.`Protocollo` AS `Protocollo`, `atto`.`Anno_Cronologico` AS `Anno_Cronologico`, `atto`.`ID_Cronologico` AS `ID_Cronologico`,
`atto`.`Data_Elaborazione` AS `Data_Elaborazione`, `atto`.`Data_Stampa` AS `Data_Stampa`, `atto`.`Stato_Stampa` AS `Stato_Stampa`,
`atto`.`Data_Flusso` AS `Data_Flusso`, `atto`.`Numero_Flusso` AS `Numero_Flusso`, `atto`.`Anno_Flusso` AS `Anno_Flusso`,
`atto`.`FlowId` AS `FlowId`, `atto`.`Tipo_Ufficiale` AS `Tipo_Ufficiale`, `atto`.`PrinterId` AS `PrinterId`, `atto`.`Totale_Dovuto` AS `Totale_Dovuto`,
`atto`.`Note` AS `Note`, `atto`.`Data_Notifica` AS `Data_Notifica`, `atto`.`Stato_Notifica` AS `Stato_Notifica`, `atto`.`Indirizzo_Validato` AS `Indirizzo_Validato`,
`atto`.`Motivo_Notifica` AS `Motivo_Notifica`, `atto`.`Modalita_Notifica` AS `Modalita_Notifica`, `atto`.`Note_Notifica` AS `Note_Notifica`,
`atto`.`Spese_Notifica` AS `Spese_Notifica`, `atto`.`CAN` AS `CAN`,`atto`.`CAD` AS `CAD`, `atto`.`Modalita_Stampa` AS `Modalita_Stampa`,
`atto`.Cronologico_Vecchio as Cronologico_Vecchio,

NULL AS `Notifica_ID`
FROM `atto`
JOIN document_type ON document_type.Id = atto.DocumentTypeId
UNION 

SELECT 

`v_pignoramento`.`ID` AS `ID`,
`v_pignoramento`.`DocumentTypeId`,  v_pignoramento.TableTypeId, v_pignoramento.DocumentType, `v_pignoramento`.`CC` AS `CC`,
`v_pignoramento`.`Partita_ID` AS `Partita_ID`,`v_pignoramento`.`Tipo_Protocollo` AS `Tipo_Protocollo`,`v_pignoramento`.`Protocollo` AS `Protocollo`,
`v_pignoramento`.`Anno_Cronologico` AS `Anno_Cronologico`,`v_pignoramento`.`ID_Cronologico` AS `ID_Cronologico`,`v_pignoramento`.
`Data_Elaborazione` AS `Data_Elaborazione`,`v_pignoramento`.`Data_Stampa` AS `Data_Stampa`,`v_pignoramento`.`Stato_Stampa` AS `Stato_Stampa`,
`v_pignoramento`.`Data_Flusso` AS `Data_Flusso`,`v_pignoramento`.`Numero_Flusso` AS `Numero_Flusso`,`v_pignoramento`.`Anno_Flusso` AS `Anno_Flusso`,
`v_pignoramento`.`FlowId` AS `FlowId`,`v_pignoramento`.`Tipo_Ufficiale` AS `Tipo_Ufficiale`,`v_pignoramento`.`PrinterId` AS `PrinterId`,
`v_pignoramento`.`Totale_Dovuto` AS `Totale_Dovuto`,`v_pignoramento`.`Note` AS `Note`,`v_pignoramento`.`Data_Notifica` AS `Data_Notifica`,
`v_pignoramento`.`Stato_Notifica` AS `Stato_Notifica`,`v_pignoramento`.`Indirizzo_Validato` AS `Indirizzo_Validato`,
`v_pignoramento`.`Motivo_Notifica` AS `Motivo_Notifica`,`v_pignoramento`.`Modalita_Notifica` AS `Modalita_Notifica`,
`v_pignoramento`.`Note_Notifica` AS `Note_Notifica`,`v_pignoramento`.`Spese_Notifica` AS `Spese_Notifica`,
`v_pignoramento`.`CAN` AS `CAN`,`v_pignoramento`.`CAD` AS `CAD`,`v_pignoramento`.`Modalita_Stampa` AS `Modalita_Stampa`,
'' as Cronologico_Vecchio,
`v_pignoramento`.`Notifica_ID` AS `Notifica_ID` 
from `v_pignoramento`;


-- v_pagamenti_partita - pagamento GROUP BY Partita_ID con Tipo_Atto diverso da "Precedenti"

CREATE OR REPLACE VIEW v_pagamenti_partita AS 
SELECT 

Partita_ID, CC, SUM(pagamento.Importo) AS Totale_Pagamenti 

FROM pagamento 
WHERE Tipo_Atto!="Precedenti"
GROUP BY Partita_ID;


-- v_notifiche - v_atti_pigno con v_partita_info, v_utente, notifiche_importate`e pagamento
-- VIEWS v_atti_pigno, v_utente e v_partita_info

CREATE OR REPLACE VIEW v_notifiche AS
SELECT 

v_atti_pigno.*,

v_partita_info.Comune_ID,
v_partita_info.Flag_Blocco_Coazione, v_partita_info.Tipo_Riscossione, v_partita_info.Anno_Riferimento, v_partita_info.Info_Cartella, v_partita_info.Utente_ID,
v_partita_info.`Discharge_Date` AS `Discharge_Date`,v_partita_info.`Is_Discharged` AS `Is_Discharged`,
v_partita_info.Extraction_Date, v_partita_info.Is_Extracted,

v_utente.Cognome_Ditta, v_utente.Nome, v_utente.CF_PI,

`notif`.`ID` AS `Not_Importata_ID`,`notif`.`Lotto` AS `Not_Lotto`,`notif`.`Scatola` AS `Not_Scatola`,
`notif`.`Lotto` AS `Not_Posizione`,`notif`.`Data_Notifica` AS `Not_Data_Notifica`,`notif`.`Data_Spedizione` AS `Not_Data_Spedizione`,
`notif`.`Tipo_Notifica` AS `Not_Tipo_Notifica`,`notif`.`Stato_Notifica` AS `Not_Stato_Notifica`,`notif`.`Ms_Rac_Num` AS `Not_Numero_Raccomandata`,
`notif`.`Log_Modificato_Data` AS `Not_Data_Log`,`notif`.`Operatore` AS `Not_Operatore`,`notif`.`Data_Importazione` AS `Not_Data_Importazione`,
`notif`.`Immagine_Fronte` AS `Not_Front_Image`,`notif`.`Immagine_Retro` AS `Not_Rear_Image`,

SUM(pagamento.Importo) AS Pagamenti_Atto

FROM `v_atti_pigno`
JOIN v_partita_info ON v_partita_info.Partita_ID=v_atti_pigno.Partita_ID
JOIN v_utente on v_utente.Utente_ID=v_partita_info.Utente_ID
LEFT JOIN `notifiche_importate` `notif` on `notif`.`DocumentTypeId` = `v_atti_pigno`.`DocumentTypeId` and `notif`.`DocumentId` = `v_atti_pigno`.`ID` and `notif`.`CC_Comune` = `v_atti_pigno`.`CC`
LEFT JOIN pagamento ON pagamento.Atto_ID=v_atti_pigno.ID AND pagamento.DocumentTypeId = v_atti_pigno.DocumentTypeId
GROUP BY v_atti_pigno.ID
ORDER BY Partita_ID ASC, Data_Stampa DESC;

-- v_pigno_spese - pignoramento generale e pignoramento spese
CREATE OR REPLACE VIEW v_pigno_spese AS
SELECT PS.ID AS Pignoramento_Spese_ID,
    --    IF(PS.Tipo_Totale_1=1,PS.Rimborso_1,0.00)+IF(PS.Tipo_Totale_2=1,PS.Rimborso_2,0.00)+IF(PS.Tipo_Totale_3=1,PS.Rimborso_3,0.00)
    --        +IF(PS.Tipo_Totale_4=1,PS.Rimborso_4,0.00)+IF(PS.Tipo_Totale_5=1,PS.Rimborso_5,0.00)+IF(PS.Tipo_Totale_6=1,PS.Rimborso_6,0.00)
    --        +IF(PS.Tipo_Totale_7=1,PS.Rimborso_7,0.00)+IF(PS.Tipo_Totale_8=1,PS.Rimborso_8,0.00)+IF(PS.Tipo_Totale_9=1,PS.Rimborso_9,0.00)
    --        +IF(PS.Tipo_Totale_10=1,PS.Rimborso_10,0.00) AS Spese_Accessorie_1,
    --    IF(PS.Tipo_Totale_1=2,PS.Rimborso_1,0.00)+IF(PS.Tipo_Totale_2=2,PS.Rimborso_2,0.00)+IF(PS.Tipo_Totale_3=2,PS.Rimborso_3,0.00)
    --        +IF(PS.Tipo_Totale_4=2,PS.Rimborso_4,0.00)+IF(PS.Tipo_Totale_5=2,PS.Rimborso_5,0.00)+IF(PS.Tipo_Totale_6=2,PS.Rimborso_6,0.00)
    --        +IF(PS.Tipo_Totale_7=2,PS.Rimborso_7,0.00)+IF(PS.Tipo_Totale_8=2,PS.Rimborso_8,0.00)+IF(PS.Tipo_Totale_9=2,PS.Rimborso_9,0.00)
    --        +IF(PS.Tipo_Totale_10=2,PS.Rimborso_10,0.00) AS Spese_Accessorie_2,
    --    IF(PS.Tipo_Totale_1=3,PS.Rimborso_1,0.00)+IF(PS.Tipo_Totale_2=3,PS.Rimborso_2,0.00)+IF(PS.Tipo_Totale_3=3,PS.Rimborso_3,0.00)
    --        +IF(PS.Tipo_Totale_4=3,PS.Rimborso_4,0.00)+IF(PS.Tipo_Totale_5=3,PS.Rimborso_5,0.00)+IF(PS.Tipo_Totale_6=3,PS.Rimborso_6,0.00)
    --        +IF(PS.Tipo_Totale_7=3,PS.Rimborso_7,0.00)+IF(PS.Tipo_Totale_8=3,PS.Rimborso_8,0.00)+IF(PS.Tipo_Totale_9=3,PS.Rimborso_9,0.00)
    --        +IF(PS.Tipo_Totale_10=3,PS.Rimborso_10,0.00) AS Spese_Accessorie_3,
       P.*
FROM pignoramento_generale P
JOIN pignoramento_spese PS ON P.ID = PS.Pignoramento_ID ORDER BY P.ID;

-- v_positions - v_partita con v_pagamenti_partita, atto, pignoramento_generale e notifica_atto
-- VIEWS v_partita e v_pagamenti_partita

CREATE OR REPLACE VIEW v_positions AS 
SELECT 

PP.Totale_Pagamenti, IF(PG.ID>0,PG.Totale_Dovuto, A.Totale_Dovuto+A.Diritto_Riscossione_Massimo) AS Totale_Dovuto,
-- PIGNORAMENTO
PG.ID AS Pignoramento_ID, PG.DocumentTypeId AS DocumentTypeId_Pignoramento,
PG.Tipo AS Tipo_Pignoramento, PG.ID_Cronologico AS ID_Cronologico_Pignoramento, PG.Anno_Cronologico AS Anno_Cronologico_Pignoramento,
PG.Data_Elaborazione AS Data_Elaborazione_Pignoramento, PG.Data_Stampa AS Data_Stampa_Pignoramento, PG.Stato_Stampa AS Stato_Stampa_Pignoramento,

PG.Rate_Previste AS Rate_Previste_Pignoramento , PG.Data_Richiesta_Rate AS Data_Richiesta_Rate_Pignoramento,
PG.Importi_Rate AS Importi_Rate_Pignoramento, PG.Scadenze_Rate AS Scadenze_Rate_Pignoramento,
PG.Importo_Dovuto+PG.Totale_Spese_Notifica+PG.Spese_Accessorie_1 AS Totale_1_Pignoramento,
PG.Importo_Dovuto+PG.Totale_Spese_Notifica+PG.Spese_Accessorie_1+PG.Spese_Accessorie_2 AS Totale_2_Pignoramento,
PG.Importo_Dovuto+PG.Totale_Spese_Notifica+PG.Spese_Accessorie_1+PG.Spese_Accessorie_2+PG.Spese_Accessorie_3 AS Totale_3_Pignoramento,
NOT_PG.Data_Notifica AS Data_Notifica_Pignoramento, NOT_PG.Stato_Notifica AS Stato_Notifica_Pignoramento,
       NOT_PG.Indirizzo_Validato AS Indirizzo_Validato_Pignoramento,
NOT_PG.Motivo_Notifica AS Motivo_Notifica_Pignoramento, NOT_PG.Modalita_Notifica AS Modalita_Notifica_Pignoramento,
-- ATTO
MAX(A.ID) AS Atto_ID, A.DocumentTypeId AS DocumentTypeId,
A.Atto, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Stampa, A.Data_Elaborazione, A.Stato_Stampa, A.Cronologico_Vecchio,
A.Rate_Previste, A.Data_Richiesta_Rate, A.Importi_Rate, A.Scadenze_Rate,
A.Data_Notifica, A.Stato_Notifica, A.Motivo_Notifica, A.Modalita_Notifica, A.Indirizzo_Validato,
A.Totale_Dovuto+A.Diritto_Riscossione_Minimo AS Totale_1,
A.Totale_Dovuto+A.Diritto_Riscossione_Massimo as Totale_2,A.PrinterId,


v_partita.*

FROM v_partita
LEFT JOIN v_pagamenti_partita AS PP ON PP.Partita_ID = v_partita.Partita_ID
LEFT JOIN atto AS A ON A.ID = v_partita.Atto_Last_ID
LEFT JOIN v_pigno_spese AS PG ON PG.ID = v_partita.Pignoramento_Last_ID
LEFT JOIN notifica_atto AS NOT_PG ON NOT_PG.Atto_Notificato_ID = PG.ID AND NOT_PG.Tipo_Atto_Notificato LIKE "Pignoramento%" AND NOT_PG.Tipo_Notifica="debitore"
GROUP BY v_partita.Partita_ID
ORDER BY v_partita.Partita_ID ASC, A.ID DESC, PG.ID DESC;


-- v_appeal - v_atti con appeal, appeal_type e ufficio_giudiziario
-- VIEWS v_atti

CREATE OR REPLACE VIEW v_appeal AS 
SELECT 

appeal.ID AS Appeal_ID, appeal.Act_ID, appeal.Court_Level, appeal.Start_Date, appeal.End_Date, appeal.Type AS Appeal_Type_ID, 
appeal.Amendment_Date, appeal.Judge, appeal.Notification_Date, appeal.RG, appeal.Registration_Date, appeal.Dossier_Submission_Date, 
appeal.Body_Type, appeal.Body_Part, appeal.Trespassers_Part, appeal.Body_Lawyer, appeal.Body_Lawyer_Bar, appeal.Trespassers_Lawyer, 
appeal.Trespassers_Lawyer_Bar, appeal.Total, appeal.Act_Amount, appeal.Legal_Costs, appeal.Actual_Costs,

appeal_type.Description AS Appeal_Type, 

authority.ID AS Authority_ID, authority.Tipo AS Authority_Type, authority.Comune AS Authority_City, authority.Sezione AS Authority_Section,

v_atti.*

FROM v_atti
JOIN appeal ON v_atti.Partita_ID = appeal.Partita_ID AND v_atti.Atto_ID = appeal.Act_ID
LEFT JOIN appeal_type ON appeal_type.ID=appeal.Type
JOIN ufficio_giudiziario AS authority ON authority.ID=appeal.Authority_ID;



-- v_court_hearing - v_appeal con appeal_court_hearing e court_hearing_doctype
-- VIEWS v_appeal

CREATE OR REPLACE VIEW v_court_hearing AS 
SELECT 

CH.ID AS Court_Hearing_ID, CH.Date AS Court_Hearing_Date, CH.Time AS Court_Hearing_Time, CH.Type AS Court_Hearing_Type_ID, 
CH.Plaintiff_Proceedings_State AS Court_Hearing_Plaintiff_Type_ID, P_TYPE.Description AS Court_Hearing_Plaintiff_Type, CH.Plaintiff_Docs_Date AS Court_Hearing_Plaintiff_Docs_Date,
CH.Respondent_Proceedings_State AS Court_Hearing_Respondent_Type_ID, R_TYPE.Description AS Court_Hearing_Respondent_Type, CH.Respondent_Docs_Date AS Court_Hearing_Respondent_Docs_Date,

CHTYPE.Description AS Court_Hearing_Type,

v_appeal.* 

FROM v_appeal 
JOIN appeal_court_hearing AS CH ON CH.Appeal_ID=v_appeal.Appeal_ID 
LEFT JOIN court_hearing_type AS CHTYPE ON CHTYPE.ID=CH.Type
LEFT JOIN court_hearing_doctype AS P_TYPE ON P_TYPE.ID=CH.Plaintiff_Proceedings_State
LEFT JOIN court_hearing_doctype AS R_TYPE ON R_TYPE.ID=CH.Respondent_Proceedings_State;



-- v_flows_not_importate

CREATE OR REPLACE VIEW v_flows_not_importate AS 

SELECT 

flows.Id, count(notifiche_importate.ID) AS ImportationNumber

FROM flows
JOIN notifiche_importate ON flows.Id=notifiche_importate.FlowId AND flows.DocumentTypeId=notifiche_importate.DocumentTypeId AND flows.CityId=notifiche_importate.CC_Comune
GROUP BY flows.Id;


-- v_flows_not_importate

CREATE OR REPLACE VIEW v_flows_date_notifica AS 
SELECT 

flows.Id, count(v_atti_pigno.Data_Notifica) AS NotificationNumber

FROM flows
JOIN v_atti_pigno ON flows.Id=v_atti_pigno.FlowId AND flows.DocumentTypeId=v_atti_pigno.DocumentTypeId AND flows.CityId=v_atti_pigno.CC
WHERE v_atti_pigno.Data_Notifica is not null
GROUP BY flows.Id;


-- v_flows_anomalia_notifica

CREATE OR REPLACE VIEW v_flows_anomalia_notifica AS 

SELECT 

flows.Id, count(v_atti_pigno.ID) AS AnomalyNumber

FROM flows
JOIN v_atti_pigno ON flows.Id=v_atti_pigno.FlowId AND flows.DocumentTypeId=v_atti_pigno.DocumentTypeId AND flows.CityId=v_atti_pigno.CC
WHERE v_atti_pigno.Motivo_Notifica>0 AND (v_atti_pigno.Data_Notifica is null)
GROUP BY flows.Id;

-- v_flows

CREATE OR REPLACE VIEW v_flows AS 
SELECT 

p.Name AS Printer, pt.Description AS PrintType, dt.Description AS DocumentType, dt.TableTypeId AS DocumentTableTypeId,
flows.*, v_flows_date_notifica.NotificationNumber, v_flows_anomalia_notifica.AnomalyNumber, v_flows_not_importate.ImportationNumber 

FROM flows
JOIN printer as p ON p.Id = flows.PrinterId
JOIN print_type as pt ON pt.Id = flows.PrintTypeId
JOIN document_type AS dt ON dt.Id = flows.DocumentTypeId
LEFT JOIN v_flows_date_notifica ON v_flows_date_notifica.Id = flows.Id
LEFT JOIN v_flows_anomalia_notifica ON v_flows_anomalia_notifica.Id = flows.Id
LEFT JOIN v_flows_not_importate ON v_flows_not_importate.Id = flows.Id;


-- v_tribunale_ivg

CREATE OR REPLACE VIEW v_tribunale_ivg AS
SELECT 
`t`.`CC` AS `CC`,
`t`.`ID` AS `Id_Tribunale`, `t`.`CC_Ufficio` AS `CC_Tribunale`, `t`.`Sezione` AS `Sezione_Tribunale`, `t`.`Comune` AS `Comune_Tribunale`, `t`.`Provincia` AS `Provincia_Tribunale`, 
`t`.`Cap` AS `Cap_Tribunale`, `t`.`Toponimo` AS `Toponimo_Tribunale`, `t`.`Civico` AS `Civico_Tribunale`, `t`.`Esponente` AS `Esponente_Tribunale`, `t`.`Interno` AS `Interno_Tribunale`,
`t`.`Dettagli` AS `Dettagli_Tribunale`, `t`.`Telefono` AS `Telefono_Tribunale`, `t`.`Fax` AS `Fax_Tribunale`, `t`.`Mail` AS `Mail_Tribunale`, `t`.`PEC` AS `PEC_Tribunale`, `t`.`Sito` AS `Sito_Tribunale`, 
 
`ivg`.`ID` AS `Id_IVG`, `ivg`.`CC_Ufficio` AS `CC_IVG`, `ivg`.`Comune` AS `Comune_IVG`, `ivg`.`Provincia` AS `Provincia_IVG`, `ivg`.`Cap` AS `Cap_IVG`, 
`ivg`.`Toponimo` AS `Toponimo_IVG`, `ivg`.`Civico` AS `Civico_IVG`, `ivg`.`Esponente` AS `Esponente_IVG`, `ivg`.`Interno` AS `Interno_IVG`, `ivg`.`Dettagli` AS `Dettagli_IVG`, 
`ivg`.`Telefono` AS `Telefono_IVG`, `ivg`.`Fax` AS `Fax_IVG`, `ivg`.`Mail` AS `Mail_IVG`, `ivg`.`PEC` AS `PEC_IVG`, `ivg`.`Sito` AS `Sito_IVG`, `ivg`.`Denominazione` AS `Denominazione_IVG`, 
`ivg`.`Forma_Giuridica` AS `Forma_giuridica_IVG`
 
FROM `ufficio_giudiziario` `t`
LEFT JOIN `ufficio_giudiziario` `ivg` ON `t`.`CC_Ufficio` = `ivg`.`CC` AND `ivg`.`Tipo` = 'istituto'
WHERE `t`.`Tipo` = 'tribunale';


-- GV 14/06/2022  START 
-- v_discarichi_partite

CREATE OR REPLACE VIEW v_discarichi_partite AS
	SELECT P.* , 
            	A.Data_Notifica AS Data_Notifica , 
            	A.ID_Cronologico AS ID_CRONOLOGICO,  
            	A.Anno_Cronologico AS ANNO_CRONOLOGICO, 
            	A.Atto AS TIPO_ATTO,  
            	A.Data_Elaborazione AS Data_Elaborazione,  
            	PG.ID_Cronologico AS ID_CRONOLOGICO_PG,  
            	PG.Anno_Cronologico AS ANNO_CRONOLOGICO_PG,
                PG.Totale_Spese_Notifica  AS Totale_Spese_Notifica,  
            	PG.Totale_Spese_Accessorie  AS Totale_Spese_Accessorie,
            	DPG.Description AS PIGNORAMENTO,  
            	NPG.Data_Notifica AS DATA_NOTIFICA_PG,  
            	NPG.Data_Elaborazione AS Data_Elaborazione_Pignoramento, 
            	dis_ext.ID AS ID_ESTRAZIONE, 
            	dis_ext.Date AS DATA_ESTRAZIONE, 
            	dis_ext.Positions_Number AS POSITIONS_NUMBER,        
            	dis_ext.Username AS Operatore  
    FROM v_partita AS P 
        LEFT JOIN atto AS A ON A.ID=P.Atto_Last_ID  
        LEFT JOIN pignoramento_generale AS PG ON PG.ID=P.Pignoramento_Last_ID  
        LEFT JOIN document_type AS DPG ON PG.DocumentTypeId = DPG.Id  
        LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'   
        LEFT JOIN discharge_extractions AS dis_ext ON dis_ext.ID = P.Extraction_ID
    WHERE P.Is_Discharged = 1;


-- GV 14/06/2022    END 	

-- GV 28/06/2022  START 
-- v_check_partite

CREATE OR REPLACE VIEW v_check_partite AS
SELECT 
    P.*,
    U.*,

    UAN.Data_Notifica AS Last_Data_Notifica_Atto ,

    A.ID AS ID_ATTO,
    A.Tipo_Ufficiale AS Tipo_Ufficiale,
    A.PrinterId AS PrinterId,
    A.PrintTypeId AS PrintTypeId,
    A.DocumentTypeId,
    A.Data_Notifica AS Data_Notifica_Atto ,
    A.ID_Cronologico AS ID_CRONOLOGICO,
    A.Anno_Cronologico AS ANNO_CRONOLOGICO,
    A.Atto AS TIPO_ATTO,
    A.Spese_Notifica AS  Spese_Notifica,
    A.Interessi_Precedenti AS Interessi_Precedenti,
    A.Rielabora_Flag AS  Rielabora_Flag,
    A.Rettifica_Flag AS  Rettifica_Flag,
    A.Data_Elaborazione AS Data_Elaborazione_ATTO,
    A.Data_Flusso AS Data_Flusso_ATTO,
    A.Motivo_Notifica AS Motivo_Notifica_ATTO,
    PN.Descrizione AS Anomalia_ATTO,
    A.Stato_Notifica AS Stato_Notifica_ATTO,
    A.Indirizzo_Validato AS Indirizzo_Validato_ATTO,
    NI.Immagine_Fronte AS Notifica_Fronte_ATTO,
    NI.Immagine_Retro AS Notifica_Retro_ATTO,
    NI.CAD_Fronte AS CAD_Fronte_ATTO,
    NI.CAD_Retro AS CAD_Retro_ATTO,
    A.Modalita_Notifica AS Modalita_Notifica_ATTO,
    A.Data_Stampa AS Data_Stampa_ATTO,
    A.Data_Decorrenza_Interessi AS Data_Decorrenza_Interessi_ATTO,
    A.Data_Calcolo_Interessi AS Data_Calcolo_Interessi_ATTO,
    A.Totale_Dovuto + A.Diritto_Riscossione_Minimo AS Totale_1_ATTO,
    A.Totale_Dovuto + A.Diritto_Riscossione_Massimo AS Totale_2_ATTO,
    A.Totale_Dovuto + A.Diritto_Riscossione_Massimo AS Totale_Dovuto_ATTO,
    A.Scadenze_Rate,
    A.Importi_Rate,
    A.Rate_Previste,
    A.Tipo_Totale_Rate,
    A.Totale_Rateizzato,
    A.Esito_Richiesta_Rateizzazione,
    A.Interessi_Precedenti AS Interessi_Precedenti_ATTO,
    A.Interessi AS Interessi_ATTO,
    A.Diritto_Riscossione_Massimo AS Diritto_Riscossione_ATTO,
    A.Spese_Notifica_Precedenti AS Spese_Notifica_Precedenti_ATTO,
    A.Spese_Notifica AS Spese_Notifica_ATTO,
    A.CAN AS CAN_ATTO,
    A.CAD AS CAD_ATTO,
    A.Spese_Notifica_Pignoramento AS Spese_Notifica_Pignoramento_ATTO,
    A.Spese_Accessorie_Pignoramento AS Spese_Accessorie_Pignoramento_ATTO,
    A.archived,

    A1.Data_Notifica AS Data_Notifica_Primo_Atto,

    PG.DocumentTypeId as DocumentTypeId_PG,
    PG.ID_Cronologico AS ID_CRONOLOGICO_PG,
    PG.Anno_Cronologico AS ANNO_CRONOLOGICO_PG,
    NPG.Data_Stampa AS Data_Stampa_PG,
    PG.Stato_Pignoramento,
    PG.Importo_Atto AS Importo_Atto_PG,
    PG.Interessi AS Interessi_PG,
    PG.Importo_Dovuto AS Importo_Dovuto_PG,
    PG.Spese_Notifica_Debitore AS Spese_Notifica_Debitore_PG,
    PG.Spese_Notifica_Terzi AS Spese_Notifica_Terzi_PG,
    PG.Totale_Spese_Notifica AS Totale_Spese_Notifica_PG,
    PG.Totale_Spese_Accessorie AS Totale_Spese_Accessorie_PG,
    PG.Spese_Accessorie_1 AS Spese_Accessorie_1_PG,
    PG.Spese_Accessorie_2 AS Spese_Accessorie_2_PG,
    PG.Spese_Accessorie_3 AS Spese_Accessorie_3_PG,
    PG.Importo_Dovuto + PG.Totale_Spese_Notifica + PG.Spese_Accessorie_1 AS Totale_1_PG,
    PG.Importo_Dovuto + PG.Totale_Spese_Notifica + PG.Spese_Accessorie_2 AS Totale_2_PG,
    PG.Importo_Dovuto + PG.Totale_Spese_Notifica + PG.Spese_Accessorie_3 AS Totale_3_PG,
    PG.Totale_Dovuto AS Totale_Dovuto_PG,
    PG.Rate_Previste AS Rate_Previste_PG,
    PG.Scadenze_Rate AS Scadenze_Rate_PG,
    PG.Importi_Rate AS Importi_Rate_PG,
    PG.Tipo_Totale_Rate AS Tipo_Totale_Rate_PG,

    NPG.Motivo_Notifica AS Motivo_Notifica_PG,
    PNP.Descrizione AS Anomalia_PG,
    NPG.Stato_Notifica AS Stato_Notifica_PG,
    NPG.Indirizzo_Validato AS Indirizzo_Validato_PG,
    NIP.Immagine_Fronte AS Notifica_Fronte_PG,
    NIP.Immagine_Retro AS Notifica_Retro_PG,
    NIP.CAD_Fronte AS CAD_Fronte_PG,
    NIP.CAD_Retro AS CAD_Retro_PG,

    IFNULL(EL.FlowDate,PG.Data_Flusso) AS Data_Flusso_PG,

    DPG.Description AS PIGNORAMENTO,
    NPG.Data_Notifica AS DATA_NOTIFICA_PG,
    PG.Data_Elaborazione AS Data_Elaborazione_Pignoramento,

    SUM(PA.Importo) AS TOTALE_PAGAMENTI, PA.Data_Pagamento AS Data_Pagamento_ATTO,
    SUM(PP.Importo) AS TOTALE_PAGAMENTI_PG, PP.Data_Pagamento AS Data_Pagamento_PG,

    PS.Name AS PS_NOME, PS.Description AS PS_DESCRIZIONE,
    AP.ID AS APPEAL_ID

    FROM v_partita_tributi AS P
    JOIN v_anagrafe AS U ON U.Utente_ID=P.Utente_ID_Partita
    LEFT JOIN atto AS A ON A.ID=P.Atto_Last_ID
    LEFT JOIN atto AS A1 ON A1.ID=(SELECT ID FROM atto WHERE Partita_ID=P.Partita_ID AND Data_Notifica is not null ORDER BY ID ASC LIMIT 1)
    LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
    LEFT JOIN atto as UAN ON UAN.ID=(SELECT ID FROM atto WHERE Partita_ID=P.Partita_ID AND Data_Notifica is not null ORDER BY ID DESC LIMIT 1)
    LEFT JOIN notifiche_importate AS NI on A.ID = NI.DocumentId AND A.DocumentTypeId=NI.DocumentTypeId
    LEFT JOIN pignoramento_generale AS PG ON PG.Atto_ID=A.ID
    LEFT JOIN document_type AS DPG ON PG.DocumentTypeId = DPG.Id
    LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'
    LEFT JOIN elaboration_lists AS EL ON EL.ID=NPG.Elaboration_List_Id
    LEFT JOIN notifiche_importate AS NIP on PG.ID = NIP.DocumentId AND PG.DocumentTypeId=NIP.DocumentTypeId
    LEFT JOIN parametri_notifica AS PNP ON PNP.ID=NPG.Motivo_Notifica
    LEFT JOIN pagamento AS PA on P.Partita_ID = PA.Partita_ID AND PA.DocumentTypeId is not null 
    AND PA.DocumentTypeId!=IFNULL(PG.DocumentTypeId,0) AND PA.Atto_ID!=IFNULL(PG.ID,0)
    LEFT JOIN pagamento AS PP on P.Partita_ID = PP.Partita_ID 
    AND PP.DocumentTypeId = PG.DocumentTypeId AND PP.Atto_ID=PG.ID
    LEFT JOIN position_status AS PS on PS.Id = P.Position_Status_Id
    LEFT JOIN appeal AS AP on AP.Partita_ID = P.Partita_ID
WHERE P.Is_Discharged = 0
GROUP BY P.Partita_ID;

-- GV 28/06/2022    END
CREATE OR REPLACE VIEW v_check_partite_estrazione AS
SELECT P.* ,
    A.ID AS ID_ATTO,
    A.Tipo_Ufficiale AS Tipo_Ufficiale,
    A.PrinterId AS PrinterId,
    A.PrintTypeId AS PrintTypeId,
    A.DocumentTypeId,
    A.Data_Notifica AS Data_Notifica_Atto ,
    UAN.Data_Notifica AS Last_Data_Notifica_Atto ,
    A.ID_Cronologico AS ID_CRONOLOGICO,
    A.Anno_Cronologico AS ANNO_CRONOLOGICO,
    A.Atto AS TIPO_ATTO,
    A.Spese_Notifica AS  Spese_Notifica,
    A.Interessi_Precedenti AS Interessi_Precedenti,
    A.Rielabora_Flag AS  Rielabora_Flag,
    A.Rettifica_Flag AS  Rettifica_Flag,

    A.Data_Elaborazione AS Data_Elaborazione_ATTO,
    A.Data_Flusso AS Data_Flusso_ATTO,
    A.Motivo_Notifica AS Motivo_Notifica_ATTO,
    PN.Descrizione AS Anomalia_ATTO,
    A.Stato_Notifica AS Stato_Notifica_ATTO,
    A.Indirizzo_Validato AS Indirizzo_Validato_ATTO,
    NI.Immagine_Fronte AS Notifica_Fronte_ATTO,
    NI.Immagine_Retro AS Notifica_Retro_ATTO,
    NI.CAD_Fronte AS CAD_Fronte_ATTO,
    NI.CAD_Retro AS CAD_Retro_ATTO,
    A.Modalita_Notifica AS Modalita_Notifica_ATTO,
    A.Data_Stampa AS Data_Stampa_ATTO,
    A.Data_Decorrenza_Interessi AS Data_Decorrenza_Interessi_ATTO,
    A.Data_Calcolo_Interessi AS Data_Calcolo_Interessi_ATTO ,
    IF((A.Data_Notifica + INTERVAL 60 DAY) > PA.Data_Pagamento , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO_STRA,
    IF((A.Data_Notifica + INTERVAL 60 DAY) > PA.Data_Pagamento , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO,
    A.Scadenze_Rate,
    A.Importi_Rate,
    A.Rate_Previste,
    A.Totale_Rateizzato,
    A.Esito_Richiesta_Rateizzazione,
    A.Interessi_Precedenti AS Interessi_Precedenti_ATTO,
    A.Interessi AS Interessi_ATTO,
    A.Diritto_Riscossione_Massimo AS Diritto_Riscossione_ATTO,
    A.Spese_Notifica_Precedenti AS Spese_Notifica_Precedenti_ATTO,
    A.Spese_Notifica AS Spese_Notifica_ATTO,
    A.CAN AS CAN_ATTO,
    A.CAD AS CAD_ATTO,
    PG.DocumentTypeId as DocumentTypeId_PG,
    PG.ID_Cronologico AS ID_CRONOLOGICO_PG,
    PG.Anno_Cronologico AS ANNO_CRONOLOGICO_PG,
    PG.Data_Stampa AS Data_Stampa_PG,
    PG.Stato_Pignoramento,
    PG.Totale_Dovuto AS Totale_Dovuto_PG,
    DPG.Description AS PIGNORAMENTO,
    NPG.Data_Notifica AS DATA_NOTIFICA_PG,
    PG.Data_Elaborazione AS Data_Elaborazione_Pignoramento,
    SUM(PA.Importo) AS TOTALE_PAGAMENTI,

    PS.Name AS PS_NOME, PS.Description AS PS_DESCRIZIONE,
    AP.ID AS APPEAL_ID,
	ES.Elaboration_ID as Elaboration_Estrazione_ID
FROM v_partita AS P
		JOIN estrazione_pvt as ES on P.Partita_ID = ES.Partita_ID
        LEFT JOIN atto AS A ON A.ID=P.Atto_Last_ID
        LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
        LEFT JOIN atto as UAN ON UAN.ID=(SELECT ID FROM atto WHERE Partita_ID=P.Partita_ID AND Data_Notifica is not null ORDER BY ID DESC LIMIT 1)
        LEFT JOIN notifiche_importate AS NI on A.ID = NI.DocumentId AND A.DocumentTypeId=NI.DocumentTypeId
        LEFT JOIN pignoramento_generale AS PG ON PG.ID=P.Pignoramento_Last_ID
        LEFT JOIN document_type AS DPG ON PG.DocumentTypeId = DPG.Id
        LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'
        LEFT JOIN pagamento AS PA on P.Partita_ID = PA.Partita_ID AND PA.DocumentTypeId is not null

        LEFT JOIN position_status AS PS on PS.Id = P.Position_Status_Id
        LEFT JOIN appeal AS AP on AP.Partita_ID = P.Partita_ID
WHERE P.Is_Discharged = 0
GROUP BY P.Partita_ID; 

CREATE OR REPLACE VIEW v_check_partite_stra AS
SELECT P.* ,
       A.ID AS ID_ATTO,
       A.Tipo_Ufficiale AS Tipo_Ufficiale,
       A.PrinterId AS PrinterId,
       A.PrintTypeId AS PrintTypeId,
       A.DocumentTypeId,
       A.Data_Notifica AS Data_Notifica_Atto ,
       UAN.Data_Notifica AS Last_Data_Notifica_Atto ,
       A.ID_Cronologico AS ID_CRONOLOGICO,
       A.Anno_Cronologico AS ANNO_CRONOLOGICO,
       A.Atto AS TIPO_ATTO,
       A.Spese_Notifica AS  Spese_Notifica,
       A.Interessi_Precedenti AS Interessi_Precedenti,
       A.Rielabora_Flag AS  Rielabora_Flag,
       A.Rettifica_Flag AS  Rettifica_Flag,

       A.Data_Elaborazione AS Data_Elaborazione_ATTO,
       A.Data_Flusso AS Data_Flusso_ATTO,
       A.Motivo_Notifica AS Motivo_Notifica_ATTO,
       PN.Descrizione AS Anomalia_ATTO,
       A.Stato_Notifica AS Stato_Notifica_ATTO,
       A.Modalita_Notifica AS Modalita_Notifica_ATTO,
       A.Data_Stampa AS Data_Stampa_ATTO,
       A.Data_Decorrenza_Interessi AS Data_Decorrenza_Interessi_ATTO,
       A.Data_Calcolo_Interessi AS Data_Calcolo_Interessi_ATTO ,

       IF((A.Data_Notifica + INTERVAL 60 DAY) > PA.Data_Pagamento , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO_STRA,
       (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) AS Totale_Dovuto_ATTO,
       A.Scadenze_Rate,
       A.Importi_Rate,
       A.Totale_Dovuto,
       A.Diritto_Riscossione_Minimo,
       A.Diritto_Riscossione_Massimo,
       A.Rate_Previste,
       A.Totale_Rateizzato,
       A.Esito_Richiesta_Rateizzazione,
       A.Interessi_Precedenti AS Interessi_Precedenti_ATTO,
       A.Interessi AS Interessi_ATTO,
       A.Spese_Notifica_Precedenti AS Spese_Notifica_Precedenti_ATTO,
       A.Spese_Notifica AS Spese_Notifica_ATTO,
       A.CAN AS CAN_ATTO,
       A.CAD AS CAD_ATTO,
       PG.DocumentTypeId as DocumentTypeId_PG,
       PG.ID_Cronologico AS ID_CRONOLOGICO_PG,
       PG.Anno_Cronologico AS ANNO_CRONOLOGICO_PG,
       PG.Data_Stampa AS Data_Stampa_PG,
       PG.Stato_Pignoramento,
       PG.Totale_Dovuto AS Totale_Dovuto_PG,
       DPG.Description AS PIGNORAMENTO,
       NPG.Data_Notifica AS DATA_NOTIFICA_PG,
       PG.Data_Elaborazione AS Data_Elaborazione_Pignoramento,
       SUM(PA.Importo) AS TOTALE_PAGAMENTI,

       PS.Name AS PS_NOME, PS.Description AS PS_DESCRIZIONE,
       AP.ID AS APPEAL_ID
FROM v_partita AS P
         LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.Partita_ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL)
         LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
         LEFT JOIN atto as UAN ON UAN.ID=(SELECT ID FROM atto WHERE Partita_ID=P.Partita_ID AND Data_Notifica is not null ORDER BY ID DESC LIMIT 1)
    LEFT JOIN pignoramento_generale AS PG ON PG.ID=P.Pignoramento_Last_ID
    LEFT JOIN document_type AS DPG ON PG.DocumentTypeId = DPG.Id
    LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'
    LEFT JOIN pagamento AS PA on P.Partita_ID = PA.Partita_ID AND PA.DocumentTypeId is not null

    LEFT JOIN position_status AS PS on PS.Id = P.Position_Status_Id
    LEFT JOIN appeal AS AP on AP.Partita_ID = P.Partita_ID
WHERE P.Is_Discharged = 0
GROUP BY P.Partita_ID;




CREATE OR REPLACE VIEW V_stragiudiziali AS
SELECT PT.ID,
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

       IF(UCS.user_id IS NULL, "NON Selezionata" COLLATE utf8mb4_unicode_ci, "Selezionata" COLLATE utf8mb4_unicode_ci) AS user_check,

       (A.Data_Notifica + INTERVAL 60 DAY) AS Data_Notifica_60,
       IF( NPG.Data_Notifica IS NULL, A.Data_Notifica + INTERVAL 5 YEAR, IF(NPG.Data_Notifica > A.Data_Notifica, NPG.Data_Notifica, A.Data_Notifica) + INTERVAL 5 YEAR) AS Data_Notifica_5,
       (A.Data_Notifica + INTERVAL 1 YEAR) AS Data_Notifica_1,
       CONCAT(COALESCE(U.Cognome,""),COALESCE(U.Ditta,"")) AS Cognome_Ditta,
       CONCAT(COALESCE(U.Codice_Fiscale,""),COALESCE(U.Partita_Iva,"")) AS CF_PI,
        (
            CASE
                 WHEN A.ID IS NULL OR A.Data_Notifica IS NULL  THEN "Nessun atto presente" COLLATE utf8mb4_unicode_ci
                 WHEN PT.Flag_Blocco_Coazione = "si" THEN "Atto bloccato" COLLATE utf8mb4_unicode_ci
                 WHEN (SELECT SUM(PA1.Importo) FROM pagamento AS PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null) = 0  THEN "NON Pagata" COLLATE utf8mb4_unicode_ci
                 WHEN IF((IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1)  , (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo) , (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) - (SELECT SUM(PA1.Importo) FROM pagamento AS PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null) ) < p_a.Importo_Minimo, 1,2) = 1  THEN "Pagata Completamente" COLLATE utf8mb4_unicode_ci
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
    LEFT JOIN atto AS A ON A.ID = (SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = PT.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11)
    LEFT JOIN utente AS U ON U.ID = PT.Utente_ID
    LEFT JOIN enti_gestiti as EG ON EG.CC = PT.CC
    LEFT JOIN parametri_annuali AS p_a ON p_a.CC = PT.CC AND p_a.Anno = YEAR(CURRENT_DATE())
    LEFT JOIN user_check_stra AS UCS ON UCS.CC = PT.CC AND UCS.user_id = U.ID AND UCS.flag_check = 1
    LEFT JOIN pignoramento_generale AS PG ON PG.ID= (SELECT MAX(ID) FROM pignoramento_generale AS PG1 WHERE PG1.Partita_ID = PT.ID ) AND Stato_Pignoramento != 'Archiviato' AND Stato_Pignoramento != 'Annullato'
    LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica = 'debitore' AND NPG.Tipo_Atto_Notificato = 'pignoramento'

    WHERE PT.Is_Discharged = 0 AND NOT EXISTS (SELECT * FROM banca_utente AS b_u WHERE  b_u.Utente_ID = PT.Utente_ID ) AND (PT.Is_Expired <> 1 OR PT.Is_Expired IS NULL) AND A.DocumentTypeId in (2, 4)
    #GROUP BY PT.ID;

    #AND A.Data_Notifica IS NOT NULL
    #AND (NOW() > (
    #            SELECT PA.Data_Pagamento
    #            FROM pagamento AS PA
    #            WHERE PA.Atto_ID = A.ID
    #            ORDER BY PA.Data_Pagamento DESC LIMIT 1
    #        ) + INTERVAL 60 DAY
    #    )
    #AND (
    #IF((A.Data_Notifica + INTERVAL 60 DAY) > (SELECT PA1.Data_Pagamento FROM pagamento as PA1 WHERE PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null ORDER BY ID DESC LIMIT 1)
    #, (A.Totale_Dovuto + A.Diritto_Riscossione_Minimo)
    #, (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) ) - (select SUM(PA1.Importo)  from pagamento as PA1 where PT.ID = PA1.Partita_ID AND PA1.DocumentTypeId is not null )
    #)  > p_a.Importo_Minimo



GROUP BY PT.ID;

CREATE OR REPLACE VIEW `v_indirizzo_con_via`  AS
select
       `VIA`.`ID` AS `ID`,
       `VIA`.`Tipo` AS `Tipo`,
       `VIA`.`Utente_ID` AS `Utente_ID`,
       `VIA`.`Via_ID` AS `Via_ID`,
       `VIA`.`Via_Cap_ID` AS `Via_Cap_ID`,
       `VIA`.`CC_Indirizzo` AS `CC_Indirizzo`,
       `VIA`.`Paese` AS `Paese`,`VIA`.`Comune` AS `Comune`,
       `VIA`.`Provincia` AS `Provincia`,
       `VIA`.`Frazione` AS `Frazione`,
       `VIA`.`Civico` AS `Civico`,
       `VIA`.`Esponente` AS `Esponente`,
       `VIA`.`Interno` AS `Interno`,
       `VIA`.`Dettagli` AS `Dettagli`,
       `VIA`.`Cap` AS `Cap`,
       `VIA`.`Telefono` AS `Telefono`,
       `VIA`.`Fax` AS `Fax`,
       `VIA`.`Presso` AS `Presso`,
       `VIA`.`Data_Inizio_Residenza` AS `Data_Inizio_Residenza`,
       `VIA`.`Lock_Time` AS `Lock_Time`,
       `VIA`.`Lock_User` AS `Lock_User`,
       `VIA`.`Data_Conferma_Indirizzo` AS `Data_Conferma_Indirizzo`,ifnull(`T`.`Nome`,`TC`.`Odonimo`) AS `indirizzo`
from (
      (
          (`indirizzo` `VIA`
              join `utente` `U` on((`VIA`.`Utente_ID` = `U`.`ID`)))
              left join `toponimo` `T` on(((`T`.`ID` = `VIA`.`Via_ID`) and (`VIA`.`Via_ID` > 1)
                  )
                  )
          )
              left join `toponimi_cappati` `TC` on(((`TC`.`ID` = `VIA`.`Via_Cap_ID`) and (`VIA`.`Via_Cap_ID` > 1)))) ;

-- 2022-12-22 GF


-- 2022-12-22 GF 2023-01-05 MP
CREATE OR REPLACE VIEW v_check_pignoramenti AS
SELECT 

P.ID as Partita_ID,
P.Comune_ID,
P.CC,
P.Ruolo_ID,
P.Tipo as Tipo_Riscossione,
P.Sottotipo as Sottotipo_Riscossione,
P.Anno_Riferimento,
P.Flag_Blocco_Coazione,
P.Flag_Blocco_Maggiorazioni,
P.Flag_Blocco_Diritto_Riscossione,
P.Is_Expired,
P.Elaboration_Id,
P.Position_Status_Id,
P.flag_elaboration,
P.Flag_Coobbligati,

IF((A.Data_Notifica + INTERVAL 60 DAY) > PA.Data_Pagamento , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO_STRA,

SUM(PA.Importo) AS TOTALE_PAGAMENTI,

A.ID as Atto_ID,
A.DocumentTypeId,
A.Rate_Previste,
A.Rettifica_Flag AS Rettifica_Flag,
A.Rielabora_Flag AS Rielabora_Flag,
A.Info_Cartella AS Info_Cartella,
A.Data_Notifica AS Data_Notifica_Atto ,
A.Modalita_Notifica as Modalita_Notifica_Atto,
A.Stato_Notifica AS Stato_Notifica_Atto,
A.Indirizzo_Validato AS Indirizzo_Validato_Atto,
A.Motivo_Notifica AS Motivo_Notifica_Atto,
(A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) AS Totale_Dovuto_ATTO,

UA.ID as LastAtto_ID,
UA.DocumentTypeId as DocumentTypeId_LastAtto,
UA.Rettifica_Flag AS Rettifica_Flag_LastAtto,
UA.Rielabora_Flag AS Rielabora_Flag_LastAtto,
UA.Data_Notifica AS Data_Notifica_LastAtto ,
UA.Modalita_Notifica as Modalita_Notifica_LastAtto,
UA.Stato_Notifica AS Stato_Notifica_LastAtto,
UA.Indirizzo_Validato AS Indirizzo_Validato_LastAtto,
UA.Motivo_Notifica AS Motivo_Notifica_LastAtto,
UA.Rate_Previste AS Rate_Previste_LastAtto,

A1.Data_Notifica AS Data_Notifica_Primo_Atto,

AP.ID AS APPEAL_ID,

NI.Immagine_Fronte AS Notifica_Fronte_Atto,
NI.Immagine_Retro AS Notifica_Retro_Atto,
NI.CAD_Fronte AS CAD_Fronte_Atto,
NI.CAD_Retro AS CAD_Retro_Atto,

NPG.Data_Notifica AS DATA_NOTIFICA_PG,

PG.DocumentTypeId as DocumentTypeId_PG,
PG.Stato_Pignoramento,

U.Data_Morte,
U.Genere AS Genere_Utente,

I.ID as Rec_ID,
I.Presso as Rec_Presso,
T.Partita_Data_Decorrenza 

FROM partita_tributi AS P
JOIN utente as U ON P.Utente_ID = U.ID
JOIN (
    SELECT TR.Partita_ID,TR.Data_Decorrenza_Interessi as Partita_Data_Decorrenza
    FROM tributo AS TR
    GROUP BY TR.Partita_ID
) 
AS T ON P.ID=T.Partita_ID
LEFT JOIN indirizzo as I on I.Utente_ID = U.ID AND I.Tipo = 'rec'
LEFT JOIN atto as UA ON UA.ID=(SELECT MAX(ID) FROM atto WHERE Partita_ID=P.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11 ORDER BY ID DESC LIMIT 1)
LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL)
LEFT JOIN atto AS A1 ON A1.ID=(SELECT ID FROM atto WHERE Partita_ID=P.ID AND Data_Notifica is not null ORDER BY ID ASC LIMIT 1)
LEFT JOIN notifiche_importate AS NI on A.ID = NI.DocumentId AND A.DocumentTypeId=NI.DocumentTypeId
LEFT JOIN pignoramento_generale as PG ON PG.ID =(SELECT MAX(ID) FROM pignoramento_generale AS PG2 
WHERE PG2.Partita_ID=P.ID AND PG2.Stato_Pignoramento!="Annullato" AND PG2.Stato_Pignoramento!="Archiviato")
LEFT JOIN appeal AS AP on AP.ID = P.ID
LEFT JOIN pagamento AS PA on P.ID = PA.Partita_ID AND PA.DocumentTypeId is not null
LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'

GROUP BY P.ID;

CREATE OR REPLACE VIEW `v_pignoramento_stampa` AS 
select 
PG.Importo_Atto,
PG.Interessi,
PG.Importo_Dovuto,
PG.Totale_Spese_Notifica,
PG.ID,
PT.*,
PG.ID_Cronologico,
PG.Anno_Cronologico,
NA.ID as Notifica_ID,
PG.Protocollo as Protocollo,
PG.Data_Protocollo as Anno_Protocollo,
IV.Comune as Nome_Comune,
concat(U.Cognome, " ", U.Nome) as User,
U.Codice_Fiscale,U.Partita_Iva,
concat(IV.Indirizzo,",",IV.Civico," - ", IV.CAP, " " , IV.Comune) as Indirizzo,
PV.Veicolo_ID,
PV.Data_Visura,
PV.Marca_Veicolo,
PV.Modello_Veicolo,
PV.Targa_Veicolo,
PV.Tipo_Veicolo,
PV.Fonte_Dati,
NA.Elaboration_List_Id,
PG.Data_Elaborazione,
PG.PrinterId,
DT.Description as Nome_Pignoramento,
DT.Description as DocumentType,
DT.PrefixName as PrefixName,
DT.TableTypeId AS TableTypeId,
A.Atto_Rettificato,
NA.Spese_Notifica
from notifica_atto as NA
 join pignoramento_generale as PG on NA.Atto_Notificato_ID = PG.ID
 join atto as A on PG.Atto_ID=A.ID
join v_partita as PT on PG.Partita_ID = PT.Partita_ID
join utente as U on PT.Utente_ID=U.ID
join v_indirizzo_con_via as IV on U.ID = IV.Utente_ID 
join pignoramento_veicolo as PV on PV.Pignoramento_ID = PG.ID
JOIN document_type as DT ON DT.Id = PG.DocumentTypeId;

CREATE OR REPLACE VIEW v_manage_acts_pignoramenti_flusso AS
SELECT 
P.*,
NA.ID, 
PG.ID as PignoID,
NA.Tipo_Notifica,
PG.ID_Cronologico, 
PG.Anno_Cronologico, 
NA.Stato_Stampa, 
NA.Tipo_Ufficiale, 
NA.Data_Stampa, 
NA.Elaboration_List_Id,
NA.Printer_Id,
NA.PrintTypeId,
NA.SignedPdfFlag,
DT.PrefixName,
DT.ID as DocumentTypeId,
DT.Description as DocumentType
FROM  notifica_atto as NA
JOIN pignoramento_generale as PG ON NA.Atto_Notificato_ID=PG.ID
JOIN v_partita AS P ON P.Partita_ID=PG.Partita_ID
JOIN document_type as DT ON DT.Id = PG.DocumentTypeId
ORDER BY PG.Elaboration_Id, NA.Elaboration_List_Id;

CREATE OR REPLACE VIEW v_check_stragiudiziali AS
SELECT 
P.ID as Partita_ID,
P.Comune_ID,
P.CC,
P.Ruolo_ID,
P.Tipo as Tipo_Riscossione,
P.Sottotipo as Sottotipo_Riscossione,
P.Anno_Riferimento,
P.Flag_Blocco_Coazione,
P.Flag_Blocco_Maggiorazioni,
P.Flag_Blocco_Diritto_Riscossione,
P.Is_Expired,
P.Elaboration_Id,
P.Position_Status_Id,
PPP.flag_elaboration,
PPP.Procedure_Id,
A.ID as Atto_ID,
IF((A.Data_Notifica + INTERVAL 60 DAY) > PA.Data_Pagamento , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO_STRA,
    (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) AS Totale_Dovuto_ATTO,
SUM(PA.Importo) AS TOTALE_PAGAMENTI,
A.DocumentTypeId,
A.Rate_Previste,
A.Rettifica_Flag AS  Rettifica_Flag,
A.Rielabora_Flag AS  Rielabora_Flag,
A.Info_Cartella AS Info_Cartella,
AP.ID AS APPEAL_ID,
A.Data_Notifica AS Data_Notifica_Atto ,
A.Modalita_Notifica as Modalita_Notifica_Atto,
A.Stato_Notifica AS Stato_Notifica_Atto,
A.Indirizzo_Validato AS Indirizzo_Validato_Atto,
NI.Immagine_Fronte AS Notifica_Fronte_Atto,
NI.Immagine_Retro AS Notifica_Retro_Atto,
NI.CAD_Fronte AS CAD_Fronte_Atto,
NI.CAD_Retro AS CAD_Retro_Atto,
UAN.Data_Notifica AS Last_Data_Notifica_Atto,
NPG.Data_Notifica AS DATA_NOTIFICA_PG,
PG.DocumentTypeId as DocumentTypeId_PG,
PG.Stato_Pignoramento,
U.Data_Morte,
I.ID as Rec_ID,
I.Presso as Rec_Presso,
T.Data_Decorrenza_Interessi as Partita_Data_Decorrenza
FROM partita_tributi AS P
JOIN utente as U ON P.Utente_ID = U.ID
JOIN tributo as T ON P.ID = T.Partita_ID
LEFT JOIN indirizzo as I on I.Utente_ID = U.ID AND I.Tipo = 'rec'
LEFT JOIN atto as UAN ON UAN.ID=(SELECT ID FROM atto WHERE Partita_ID=P.ID AND Data_Notifica is not null ORDER BY ID DESC LIMIT 1)
LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL)
LEFT JOIN notifiche_importate AS NI on A.ID = NI.DocumentId AND A.DocumentTypeId=NI.DocumentTypeId
LEFT JOIN pignoramento_generale as PG ON PG.ID =(SELECT MAX(ID) FROM pignoramento_generale AS PG2 WHERE PG2.Stato_Pignoramento!="Annullato" AND PG2.Stato_Pignoramento!="Archiviato")
LEFT JOIN appeal AS AP on AP.ID = P.ID
LEFT JOIN pagamento AS PA on P.ID = PA.Partita_ID AND PA.DocumentTypeId is not null
LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'
left join partita_procedure_pvt PPP on P.ID = PPP.Partita_Id and PPP.Partita_Id is null
GROUP BY P.ID;



CREATE OR REPLACE VIEW v_stragiudiziali_banche_prendi_excel AS
SELECT 
P.Utente_ID,
A.ID_Cronologico,
A.Anno_Cronologico,
A.Atto AS TIPO_ATTO,
(A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) AS Totale_Dovuto_ATTO
FROM partita_tributi AS P
JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL)
GROUP BY P.ID;

CREATE OR REPLACE VIEW v_check_stragiudiziali_previdenziali AS
SELECT 
P.ID as Partita_ID,
P.Comune_ID,
P.CC,
P.Ruolo_ID,
P.Tipo as Tipo_Riscossione,
P.Sottotipo as Sottotipo_Riscossione,
P.Anno_Riferimento,
P.Flag_Blocco_Coazione,
P.Flag_Blocco_Maggiorazioni,
P.Flag_Blocco_Diritto_Riscossione,
P.Is_Expired,
P.Elaboration_Id,
P.Position_Status_Id,
PPP.flag_elaboration,
PPP.Procedure_Id,
A.ID as Atto_ID,
IF((A.Data_Notifica + INTERVAL 60 DAY) > PA.Data_Pagamento , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Minimo,0)) , (COALESCE(A.Totale_Dovuto,0) + COALESCE(A.Diritto_Riscossione_Massimo,0)) ) AS Totale_Dovuto_ATTO_STRA,
    (A.Totale_Dovuto + A.Diritto_Riscossione_Massimo) AS Totale_Dovuto_ATTO,
SUM(PA.Importo) AS TOTALE_PAGAMENTI,
A.DocumentTypeId,
A.Rate_Previste,
A.Rettifica_Flag AS  Rettifica_Flag,
A.Rielabora_Flag AS  Rielabora_Flag,
A.Info_Cartella AS Info_Cartella,
AP.ID AS APPEAL_ID,
A.Data_Notifica AS Data_Notifica_Atto ,
A.Modalita_Notifica as Modalita_Notifica_Atto,
A.Stato_Notifica AS Stato_Notifica_Atto,
A.Indirizzo_Validato AS Indirizzo_Validato_Atto,
NI.Immagine_Fronte AS Notifica_Fronte_Atto,
NI.Immagine_Retro AS Notifica_Retro_Atto,
NI.CAD_Fronte AS CAD_Fronte_Atto,
NI.CAD_Retro AS CAD_Retro_Atto,
UAN.Data_Notifica AS Last_Data_Notifica_Atto,
NPG.Data_Notifica AS DATA_NOTIFICA_PG,
PG.DocumentTypeId as DocumentTypeId_PG,
PG.Stato_Pignoramento,
U.Data_Morte,
I.ID as Rec_ID,
I.Presso as Rec_Presso,
T.Data_Decorrenza_Interessi as Partita_Data_Decorrenza
FROM partita_tributi AS P
JOIN utente as U ON P.Utente_ID = U.ID and Cognome is not null
   and  year(Data_Nascita)<1965
   and Data_Morte is null
JOIN tributo as T ON P.ID = T.Partita_ID
LEFT JOIN indirizzo as I on I.Utente_ID = U.ID AND I.Tipo = 'rec'
LEFT JOIN atto as UAN ON UAN.ID=(SELECT ID FROM atto WHERE Partita_ID=P.ID AND Data_Notifica is not null ORDER BY ID DESC LIMIT 1)
LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL)
LEFT JOIN notifiche_importate AS NI on A.ID = NI.DocumentId AND A.DocumentTypeId=NI.DocumentTypeId
LEFT JOIN pignoramento_generale as PG ON PG.ID =(SELECT MAX(ID) FROM pignoramento_generale AS PG2 WHERE PG2.Stato_Pignoramento!="Annullato" AND PG2.Stato_Pignoramento!="Archiviato")
LEFT JOIN appeal AS AP on AP.ID = P.ID
LEFT JOIN pagamento AS PA on P.ID = PA.Partita_ID AND PA.DocumentTypeId is not null
LEFT JOIN notifica_atto AS NPG ON NPG.Atto_Notificato_ID = PG.ID AND NPG.Tipo_Notifica='debitore'
left join partita_procedure_pvt PPP on P.ID = PPP.Partita_Id and PPP.Partita_Id is null
GROUP BY P.ID;

CREATE OR REPLACE VIEW `v_pignoramento_terzi_stampa` AS 
select 
NA.Tipo_Notifica,
IF(PT.Dom_ID is not null, 
   CONCAT(PT.Dom_Via,", ",PT.Dom_Civico," ",PT.Dom_Cap," ",PT.Dom_Comune),
   IF(PT.Rec_ID is not null, 
      CONCAT(PT.Rec_Via,", ",PT.Rec_Civico," ",PT.Rec_Cap," ",PT.Rec_Comune),
      IF(PT.Res_ID is not null, 
         CONCAT(PT.Res_Via,", ",PT.Res_Civico," ",PT.Res_Cap," ",PT.Res_Comune),
         null))) AS Indirizzo_Debitore,
PG.Importo_Dovuto,
PG.Totale_Spese_Notifica,
PG.ID,
PT.*,
PG.ID_Cronologico,
PG.Anno_Cronologico,
NA.ID as Notifica_ID,
PG.Protocollo as Protocollo,
PG.Data_Protocollo as Anno_Protocollo,
concat(PT.Cognome_Ditta, " ", PT.Nome) as User,
NA.Elaboration_List_Id,
NA.Utente_ID as Utente_Notifica_ID,
PG.Data_Elaborazione,
PG.PrinterId,
DT.Description as Nome_Pignoramento,
DT.Description as DocumentType,
DT.PrefixName as PrefixName,
DT.TableTypeId AS TableTypeId,
A.Atto_Rettificato,
PG.Data_Calcolo_Interessi,
PG.Data_Decorrenza_Interessi,
PG.Interessi,
PG.Importo_Atto
from notifica_atto as NA
join pignoramento_generale as PG on NA.Atto_Notificato_ID = PG.ID
join atto as A on PG.Atto_ID=A.ID
join v_partita as PT on PG.Partita_ID = PT.Partita_ID
JOIN document_type as DT ON DT.Id = PG.DocumentTypeId
group by Notifica_ID;


CREATE OR REPLACE VIEW `v_pignoramento_banche_stampa` AS 
select 
NA.Tipo_Notifica,
IF(PT.Dom_ID is not null, 
   CONCAT(PT.Dom_Via,", ",PT.Dom_Civico," ",PT.Dom_Cap," ",PT.Dom_Comune),
   IF(PT.Rec_ID is not null, 
      CONCAT(PT.Rec_Via,", ",PT.Rec_Civico," ",PT.Rec_Cap," ",PT.Rec_Comune),
      IF(PT.Res_ID is not null, 
         CONCAT(PT.Res_Via,", ",PT.Res_Civico," ",PT.Res_Cap," ",PT.Res_Comune),
         null))) AS Indirizzo_Debitore,
PG.Importo_Dovuto,
PG.Totale_Spese_Notifica,
PG.ID,
PT.*,
PG.ID_Cronologico,
PG.Anno_Cronologico,
NA.ID as Notifica_ID,
PG.Protocollo as Protocollo,
PG.Data_Protocollo as Anno_Protocollo,
concat(PT.Cognome_Ditta, " ", PT.Nome) as User,
NA.Elaboration_List_Id,
NA.Utente_ID as Utente_Notifica_ID,
PG.Data_Elaborazione,
PG.PrinterId,
DT.Description as Nome_Pignoramento,
DT.Description as DocumentType,
DT.PrefixName as PrefixName,
DT.TableTypeId AS TableTypeId,
A.Atto_Rettificato,
PG.Data_Calcolo_Interessi,
PG.Data_Decorrenza_Interessi,
PG.Interessi,
PG.Importo_Atto,
BA.ID AS Banca_ID,
BA.Denominazione AS Banca_Denominazione,
BA.PEC AS Banca_PEC,
IF(ISNULL(BA.ID),concat(PT.Cognome_Ditta, " ", PT.Nome),BA.Denominazione) AS Recipient_Denominazione,
IF(ISNULL(BA.ID),PT.Utente_PEC,BA.PEC) AS Recipient_PEC
from notifica_atto as NA
join pignoramento_generale as PG on NA.Atto_Notificato_ID = PG.ID
join atto as A on PG.Atto_ID=A.ID
join v_partita as PT on PG.Partita_ID = PT.Partita_ID
JOIN document_type as DT ON DT.Id = PG.DocumentTypeId
LEFT JOIN banca as BA ON BA.Id=NA.Utente_ID
group by Notifica_ID;