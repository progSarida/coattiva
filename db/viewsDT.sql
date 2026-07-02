
# fase pre elaborazione

CREATE OR REPLACE VIEW v_pre_elab_acts AS
SELECT 
P.ID AS Partita_ID, P.Comune_ID , P.Tipo AS Tipo_Riscossione, P.CC, T.Info_Cartella, P.Elaboration_Id, P.Position_Status_Id, P.flag_elaboration,
IFNULL(PN.Descrizione, "") AS Anomalia_ATTO,
PS.Name AS Position_Status
FROM partita_tributi AS P
JOIN tributo T on T.Partita_ID=P.ID
JOIN position_status AS PS on PS.Id = P.Position_Status_Id
LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 
AND (A2.Data_Notifica IS NOT NULL OR A2.Motivo_Notifica is not null) AND A2.archived IS NULL)
LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
GROUP BY P.ID;

CREATE OR REPLACE VIEW v_pre_elab_acts_preav_fermo AS
SELECT 
P.ID AS Partita_ID, P.Comune_ID, P.Tipo AS Tipo_Riscossione, 
P.CC, T.Info_Cartella, P.Elaboration_Id, P.Position_Status_Id, P.flag_elaboration,
IFNULL(PN.Descrizione, "") AS Anomalia_ATTO,
PS.Name AS Position_Status
FROM partita_tributi AS P
JOIN tributo T on T.Partita_ID=P.ID
JOIN position_status AS PS on PS.Id = P.Position_Status_Id
LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto WHERE Partita_ID=P.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11 AND archived IS NULL ORDER BY ID DESC LIMIT 1)
LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
GROUP BY P.ID;

CREATE OR REPLACE VIEW v_estrazioni_elaborabili AS
SELECT 
P.ID AS Partita_ID, P.Comune_ID, P.Tipo AS Tipo_Riscossione, 
P.CC, T.Info_Cartella, ES.Elaboration_Id, ES.Position_Status_Id,
PS.Name AS Position_Status, 1 as flag_elaboration
FROM partita_tributi AS P
JOIN estrazione_pvt AS ES on P.ID = ES.Partita_ID
JOIN position_status AS PS on PS.Id = ES.Position_Status_Id
JOIN tributo T on T.Partita_ID=P.ID
GROUP BY P.ID;

CREATE OR REPLACE VIEW v_pre_elab_acts_lavoro AS
SELECT 
P.ID AS Partita_ID, P.Comune_ID , P.Tipo AS Tipo_Riscossione, P.CC, T.Info_Cartella, P.Elaboration_Id, P.Position_Status_Id, P.flag_elaboration,
IFNULL(PN.Descrizione, "") AS Anomalia_ATTO,
PS.Name AS Position_Status
FROM partita_tributi AS P
JOIN tributo T on T.Partita_ID=P.ID
JOIN position_status AS PS on PS.Id = P.Position_Status_Id
LEFT JOIN utente as U on U.ID = P.Utente_ID
LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto WHERE Partita_ID=P.ID AND DocumentTypeId!=3 AND DocumentTypeId!=11 AND archived IS NULL ORDER BY ID DESC LIMIT 1)
#LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 
#AND (A2.Data_Notifica IS NOT NULL OR A2.Motivo_Notifica is not null))
LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
WHERE U.Genere<>"D"
GROUP BY P.ID;

# fase elaborazione

CREATE OR REPLACE VIEW v_elab_acts AS
SELECT 
A.Partita_ID, P.Comune_ID , P.Tipo AS Tipo_Riscossione, P.CC, A.Info_Cartella,
A.Elaboration_Id, A.Elaboration_List_Id, A.Tipo_Ufficiale, A.PrinterId, PR.Name as Printer, A.PrintTypeId, PT.Description as PrintType,
U.InipecLoaded, U.PEC, if(U.Genere="D",U.Partita_Iva,U.Codice_Fiscale) AS CF_PI, REC.Presso AS REC_PRESSO
FROM atto AS A
JOIN partita_tributi AS P ON P.ID=A.Partita_ID
JOIN printer AS PR ON PR.Id=A.PrinterId
JOIN print_type AS PT ON PT.Id=A.PrintTypeId
JOIN utente AS U ON U.ID=P.Utente_ID
LEFT JOIN indirizzo REC ON REC.Utente_ID=U.ID AND REC.Tipo="rec"
WHERE A.archived IS NULL;


# fasi successive per gestione stampa flussi e spedizioni

CREATE OR REPLACE VIEW v_manage_acts AS
SELECT 
A.ID, A.Partita_ID, A.ID_Cronologico, A.Anno_Cronologico, A.Info_Cartella, A.FlowId, A.PrinterId, A.PrintTypeId, A.Stato_Stampa, 
A.Tipo_Ufficiale, A.DocumentTypeId, A.Data_Stampa, A.Elaboration_List_Id,
P.Comune_ID as Comune_ID, P.Tipo AS Tipo_Riscossione, 
PR.Name AS Printer, 
PT.Description AS PrintType, 
F.FileName AS FlowFileName, F.Year AS FlowYear, F.Number AS FlowNumber, F.CreationDate AS FlowCreationDate
FROM atto as A
JOIN partita_tributi as P on P.ID = A.Partita_ID
JOIN printer as PR on PR.Id = A.PrinterId
JOIN print_type as PT on PT.Id =  A.PrintTypeId
LEFT JOIN flows as F on F.ID = A.FlowId   
WHERE A.archived IS NULL
ORDER BY A.Elaboration_Id, A.Elaboration_List_Id;




CREATE OR REPLACE VIEW v_pre_elab_pignoramenti AS
SELECT 
P.ID AS Partita_ID, 
P.Comune_ID , 
P.Utente_ID,
P.Tipo AS Tipo_Riscossione, 
P.CC, 
T.Info_Cartella, 
P.Elaboration_Id, 
P.Position_Status_Id, 
P.flag_elaboration,
PS.Name AS Position_Status,
V.Data_Visura AS Data_Acquisizione,
MAX(case 
	WHEN V.StatoVeicolo="Radiazione" OR V.StatoVeicolo="Perdita Possesso" OR ISNULL (V.ID) THEN 0
    ELSE 1
end ) AS Stato_Veicolo
FROM partita_tributi AS P
JOIN tributo T on T.Partita_ID=P.ID
JOIN position_status AS PS on PS.Id = P.Position_Status_Id
LEFT JOIN veicoli as V  ON P.Utente_ID=V.Utente_ID 
GROUP BY P.ID, V.Utente_ID;


# fase elaborazione pignoramenti


CREATE OR REPLACE VIEW v_elab_pignoramenti AS
SELECT
PV.Pignoramento_ID,
I.Utente_ID,
PV.Veicolo_ID, PV.ID AS Pignoramento_Veicolo_ID,
PG.Partita_ID, P.Comune_ID , P.Tipo AS Tipo_Riscossione, P.CC,
T.Info_Cartella,
PG.Elaboration_Id,
CL.Com_Nome as Comune_Residenza,
GROUP_CONCAT(DISTINCT V.ID ORDER BY V.DataPrimaImmatricolazione DESC SEPARATOR '*') AS ID_Veicoli,
GROUP_CONCAT(DISTINCT V.Targa ORDER BY V.DataPrimaImmatricolazione DESC SEPARATOR '*') AS Targhe_Veicoli,
GROUP_CONCAT(DISTINCT CONCAT(TRIM(V.ClasseVeicolo)," - ", TRIM(V.Telaio),
IF(ISNULL(V.Fabbrica), "", CONCAT(" - ",TRIM(V.Fabbrica))),IF(ISNULL(V.Tipo), "", CONCAT(" ",TRIM(V.Tipo)))) ORDER BY V.DataPrimaImmatricolazione DESC SEPARATOR '*') AS Modelli_Veicoli,
GROUP_CONCAT(DISTINCT TRIM(V.DataPrimaImmatricolazione) ORDER BY V.DataPrimaImmatricolazione DESC SEPARATOR '*' ) AS Data_Immatricolazione,
TR.CC_Ufficio as Tribunale,
IVG.CC_Ufficio as IVG
FROM pignoramento_generale as PG
JOIN pignoramento_veicolo as PV ON PV.Pignoramento_ID=PG.ID
JOIN partita_tributi as P on P.ID = PG.Partita_ID
JOIN veicoli V ON V.Utente_ID = P.Utente_ID AND (StatoVeicolo is null OR StatoVeicolo='Targa Attuale') AND Telaio is not null
JOIN tributo as T ON T.ID=(SELECT ID FROM tributo WHERE Partita_ID=P.ID GROUP BY Partita_ID)
JOIN indirizzo as I ON P.Utente_ID=I.Utente_ID
LEFT JOIN ufficio_giudiziario as TR ON I.CC_Indirizzo=TR.CC AND TR.Tipo = 'tribunale'
LEFT JOIN ufficio_giudiziario as IVG ON TR.CC_Ufficio=IVG.CC AND IVG.Tipo = 'istituto'
JOIN comuni_lista as CL on CL.Com_Codice_Catastale=I.CC_Indirizzo
GROUP BY PG.ID;



# fase elaborazione

CREATE OR REPLACE VIEW v_elab_acts_pignoramenti AS
SELECT 
PG.Partita_ID, 
PG.ID as Pignoramento_ID,
P.Comune_ID , 
NA.ID Notifica_ID,
P.Tipo AS Tipo_Riscossione, 
P.CC, 
T.Info_Cartella,
PG.Elaboration_Id, 
NA.Printer_Id, 
NA.Tipo_Ufficiale, 
PR.Name as Printer, 
CASE NA.Tipo_Notifica
	WHEN 'veicolo' THEN 'IVG'
	WHEN 'debitore' THEN 'debitore'
	ELSE ''
END as Tipo_Notifica,
NA.PrintTypeId,
PT.Description as PrintType,
U.InipecLoaded, 
U.PEC, if(U.Genere="D",U.Partita_Iva,U.Codice_Fiscale) AS CF_PI, REC.Presso AS REC_PRESSO
FROM pignoramento_generale AS PG
JOIN notifica_atto as NA ON NA.Atto_Notificato_ID=PG.ID
JOIN partita_tributi AS P ON P.ID=PG.Partita_ID
left JOIN printer AS PR ON PR.Id=NA.Printer_Id
JOIN print_type AS PT ON PT.Id=NA.PrintTypeId
JOIN tributo as T ON T.ID=(SELECT ID FROM tributo WHERE Partita_ID=P.ID GROUP BY Partita_ID)
JOIN utente AS U ON U.ID=P.Utente_ID
LEFT JOIN indirizzo REC ON REC.Utente_ID=U.ID AND REC.Tipo="rec"
#group by PG.Partita_ID,NA.Tipo_Notifica; #non fa uscire risultati

CREATE OR REPLACE VIEW v_manage_acts_pignoramenti AS
SELECT 
NA.ID, 
PG.ID as PignoID,
PG.CC,
PG.Partita_ID, 
P.Comune_ID,
NA.Tipo_Notifica,
PG.ID_Cronologico, 
PG.Anno_Cronologico, 
T.Info_Cartella, 
NA.Stato_Stampa, 
NA.Tipo_Ufficiale, 
NA.Data_Stampa, 
NA.Elaboration_List_Id,
DT.PrefixName
FROM  notifica_atto as NA
JOIN pignoramento_generale as PG ON NA.Atto_Notificato_ID=PG.ID
JOIN partita_tributi AS P ON P.ID=PG.Partita_ID
JOIN tributo as T ON T.ID=(SELECT ID FROM tributo WHERE Partita_ID=P.ID GROUP BY Partita_ID)
JOIN document_type as DT ON DT.Id = PG.DocumentTypeId
ORDER BY PG.Elaboration_Id, NA.Elaboration_List_Id;

CREATE OR REPLACE VIEW v_scelta_atto_per_inserimento_multipli AS
SELECT  
A.Stato_Notifica,
PT.ID as Partita_ID,
A.CC,
A.Comune_ID,
A.Anno_Cronologico,
A.ID_Cronologico,
A.Atto,
A.Info_Cartella,
A.ID as Atto_ID,
U.Cognome,
U.Nome,
U.Ditta,
U.ID as Utente_ID,
NI.Ms_Rac_Num as Raccomandata
from atto as A
join partita_tributi as PT on A.Partita_ID = PT.ID
join utente as U on U.ID = PT.Utente_ID
left join notifiche_importate as NI on NI.DocumentId = A.ID and NI.DocumentTypeId = A.DocumentTypeId
Where A.Modalita_Notifica in (11,12)  #and A.Stato_Notifica <> 28 
;

CREATE OR REPLACE VIEW v_pre_elab_acts_stragiudiziali AS
SELECT 
P.ID AS Partita_ID, 
P.Comune_ID ,
P.Tipo AS Tipo_Riscossione, 
P.CC, 
T.Info_Cartella, 
PVT.Procedure_Id, 
PVT.Position_Status_Id, 
PVT.Flag_Elaboration,
IFNULL(PN.Descrizione, "") AS Anomalia_ATTO,
PS.Name AS Position_Status
FROM partita_tributi AS P
join partita_procedure_pvt as PVT on P.ID = PVT.Partita_Id
JOIN tributo T on T.Partita_ID=P.ID
JOIN position_status AS PS on PS.Id = PVT.Position_Status_Id
LEFT JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 
AND (A2.Data_Notifica IS NOT NULL OR A2.Motivo_Notifica is not null))
LEFT JOIN parametri_notifica AS PN ON PN.ID=A.Motivo_Notifica
GROUP BY P.ID,PVT.Procedure_Id;

CREATE OR REPLACE VIEW v_banche_stragiudiziali AS
SELECT 
ID,
Denominazione,
Partita_IVA as CF_PI,
CURDATE() as IniPecLoaded,
CONCAT(Comune," ",Toponimo," Civ. ",Civico) as REC_PRESSO,
PEC
FROM banca
where Tipo_Banca = "sede" and PEC is not null;

CREATE OR REPLACE VIEW v_previdenziali_stragiudiziali AS
SELECT 
ID,
Denominazione,
Partita_IVA as CF_PI,
CURDATE() as IniPecLoaded,
CONCAT(Comune," ",Toponimo," Civ. ",Civico) as REC_PRESSO,
PEC
FROM enti_esterni
where tipo = "previdenza" and PEC is not null;

CREATE OR REPLACE VIEW v_assegna_terzo_lavoro AS
SELECT 
`U`.`Utente_ID` AS `id`,
`U`.`Utente_ID` AS `Utente_ID`,
concat(U.Cognome_Ditta,' ',U.Nome) as Denominazione,
U.CF_PI,
PT.Elaboration_Id,
PT.flag_elaboration,
PT.CC,
if(TRZ.Terzo_ID is null, "Assente" COLLATE utf8mb4_unicode_ci,"Presente" COLLATE utf8mb4_unicode_ci) as Flag_Terzo
from partita_tributi as PT
join v_anagrafe as U on PT.Utente_ID = U.Utente_ID
left join terzo_pvt as TRZ on TRZ.Utente_ID = U.Utente_ID
where U.Data_Morte is null  and PT.flag_elaboration = 1
and U.Genere <> "D"
group by Denominazione;

CREATE OR REPLACE VIEW v_assegna_terzo_banca AS
SELECT 
U.Utente_ID AS id,
PT.Utente_ID AS Utente_ID,
concat(U.Cognome_Ditta,' ',U.Nome) as Denominazione,
U.CF_PI,
U.Res_Provincia AS Provincia_Residenza_Utente,
PT.Elaboration_Id,
PT.flag_elaboration,
PT.CC,
if(TRZ.Terzo_ID is null, "Assente" COLLATE utf8mb4_unicode_ci,"Presente" COLLATE utf8mb4_unicode_ci) as Flag_Terzo
from partita_tributi as PT
join v_anagrafe as U on PT.Utente_ID = U.Utente_ID
left join banche_pvt as TRZ on TRZ.Utente_ID = U.Utente_ID
where U.Data_Morte is null  and PT.flag_elaboration = 1
group by Denominazione;

CREATE OR REPLACE VIEW v_pignoramenti_lavoro AS
select 
`U`.`Utente_ID` AS `id`,
`U`.`Utente_ID` AS `Utente_ID`,
concat(U.Cognome_Ditta,' ',U.Nome) as Denominazione,
U.CF_PI,
PT.Elaboration_Id,
PT.CC
from pignoramento_generale as PG
join pignoramento_presso_terzi as PPT on PG.ID = PPT.Pignoramento_ID
join  partita_tributi as PT on PT.ID = Partita_ID
join v_anagrafe as U on PT.Utente_ID = U.Utente_ID
where U.Genere <> "D"
group by Denominazione;

CREATE OR REPLACE VIEW v_elab_acts_pignoramenti_lavoro AS
SELECT 
PG.Partita_ID, 
PG.ID as Pignoramento_ID,
PG.Elaboration_ID,
P.Comune_ID , 
NA.ID Notifica_ID,
P.Tipo AS Tipo_Riscossione, 
P.CC, 
T.Info_Cartella,
NA.Printer_Id, 
NA.Tipo_Ufficiale, 
PR.Name as Printer, 
NA.Tipo_Notifica,
NA.PrintTypeId,
PT.Description as PrintType,
U.InipecLoaded, 
U.PEC, if(U.Genere="D",U.Partita_Iva,U.Codice_Fiscale) AS CF_PI, REC.Presso AS REC_PRESSO
FROM pignoramento_generale AS PG
JOIN notifica_atto as NA ON NA.Atto_Notificato_ID=PG.ID
JOIN utente AS U ON U.ID=NA.Utente_ID
JOIN partita_tributi AS P ON P.ID=PG.Partita_ID
left JOIN printer AS PR ON PR.Id=NA.Printer_Id
JOIN print_type AS PT ON PT.Id=NA.PrintTypeId
JOIN tributo as T ON T.ID=(SELECT ID FROM tributo WHERE Partita_ID=P.ID GROUP BY Partita_ID)
LEFT JOIN indirizzo REC ON REC.Utente_ID=NA.Utente_ID AND REC.Tipo="rec";

CREATE OR REPLACE VIEW v_elab_acts_pignoramenti_banca AS
SELECT * from v_elab_acts_pignoramenti_lavoro WHERE Tipo_Notifica<>"banca";