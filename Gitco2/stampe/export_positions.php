<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_Utils.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$request = array(
    "CC"=>$cls_help->getVar("ente"),
    "tipo_entrata"=>$cls_help->getVar("tipo_entrata"),
    "ricorso"=>$cls_help->getVar("ricorso"),
    "archiviato"=>$cls_help->getVar("archiviato"),
    "decesso"=>$cls_help->getVar("decesso"),
);

$_SESSION['progress'] = "0.00";
session_write_close();                                                                             

//? VISTA v_last_docs_notificati in db/viewsPag.sql

$query = 'SELECT
    EG.Denominazione as Ente, 

    COALESCE(NULLIF(U.Ditta,""),concat(U.Cognome," ",U.Nome)) as Denominazione_Utente, U.Genere, U.Data_Morte,

    PT.ID, PT.CC, PT.Comune_ID, PT.Anno_Riferimento, PT.Tipo as Tipo_Entrata,
    PT.Flag_Sospensione, NULLIF(PT.Flag_Blocco_Coazione,"") as Flag_Archiviazione,
    PT.Data_Attivazione_Flag_Blocco_Coazione AS Data_Archiviazione, 
    PT.Is_Discharged AS Flag_Discarico, PT.Discharge_Date AS Data_Discarico,

    IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Data_Stampa,PG.Data_Stampa) AS Print_Date,

    IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Interessi,"") AS Int_ING, IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Interessi_Codici_Tributo,"") AS Int_CT_ING, 
    IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Interessi_Precedenti,"") AS Int_Prec_ING,
    IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Spese_Notifica,"") AS Spese_Not_ING, IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Spese_Notifica_Precedenti,"") AS Spese_Not_Prec_ING,
    IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Ulteriori_Spese,"") AS Spese_Ult_ING, IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Spese_Precedenti,"") AS Spese_Prec_ING,
    IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Spese_Notifica_Pignoramento,"") AS Spese_Not_Pigno_ING, IF(DOC.DocumentTypeId IN (2,3,4,11,12),A.Spese_Accessorie_Pignoramento,"") AS Spese_Acc_Pigno_ING,

    IF(DOC.DocumentTypeId IN (2,3,4,11,12),"",PG.Interessi) AS Int_PIGNO, 
    IF(DOC.DocumentTypeId IN (2,3,4,11,12),"",PG.Spese_Notifica_Debitore) AS Spese_Not_Deb_PIGNO, IF(DOC.DocumentTypeId IN (2,3,4,11,12),"",PG.Spese_Notifica_Terzi) AS Spese_Not_Ter_PIGNO, 
    IF(DOC.DocumentTypeId IN (2,3,4,11,12),"",PG.Totale_Spese_Notifica) AS Spese_Not_Tot_PIGNO, IF(DOC.DocumentTypeId IN (2,3,4,11,12),"",PG.Totale_Spese_Accessorie) AS Spese_Acc_Tot_PIGNO,

    IF(U.Genere="D",COALESCE(U.Partita_Iva,COALESCE(U.Codice_Fiscale,"")),COALESCE(U.Codice_Fiscale,"")) AS CF,
    U.Paese_Nascita, COALESCE(U.Data_Nascita,"") AS Data_N, U.PEC, U.Comune_Nascita, U.Provincia_Nascita,

    IF(I.Via_ID=1,TC.Odonimo,TP.Nome) as Res_Ind, IF(I.Via_ID=1,TC.Comune,TP.Comune) AS Res_Com,  IF(I.Via_ID=1,"Italia",TP.Paese) AS Res_Pae,

    SG.ID AS Sgravio_ID, SG.Data_Stampa AS Data_Stampa_Sgravio,
    PT.Flag_Sgravio, PT.Sgravio_Activation_Date AS Data_Elaborazione_Sgravio,

    T.Info_Cartella, T.Data_Decorrenza_Interessi,
    TP.Totale_Codici_Tributo-IFNULL(TN.Totale_Codici_Tributo,0) AS Carico_Iniziale,

    DOC.*,
    UDOC.Ultimo_Dovuto,
    UDOC.Ultimo_Dovuto-IFNULL(DOC.Pagato,0) AS Ultimo_Residuo
    
    FROM partita_tributi PT

    JOIN indirizzo I ON I.Utente_ID = PT.Utente_ID
    JOIN toponimo TP ON I.Via_ID = TP.ID
    JOIN toponimi_cappati TC ON I.Via_Cap_ID = TC.ID

    JOIN enti_gestiti EG ON PT.CC = EG.CC
    JOIN (
    	SELECT * FROM tributo WHERE Data_Decorrenza_Interessi IS NOT NULL GROUP BY Partita_ID   
    ) 
    AS T ON T.Partita_ID=PT.ID
    JOIN (
        SELECT SUM(Imposta) AS Totale_Codici_Tributo, Partita_ID FROM tributo WHERE Codice_Tributo NOT IN ("6666","6668","S_02") GROUP BY Partita_ID
    ) 
    AS TP ON TP.Partita_ID=PT.ID
    LEFT JOIN (
        SELECT SUM(Imposta) AS Totale_Codici_Tributo, Partita_ID FROM tributo WHERE Codice_Tributo IN ("6666","6668","S_02") GROUP BY Partita_ID
    ) 
    AS TN ON TN.Partita_ID=PT.ID
    JOIN utente as U ON U.ID = PT.Utente_ID
    LEFT JOIN sgravio SG ON SG.Partita_ID=PT.ID
    LEFT JOIN
    (
        SELECT MAX(Ultimo_Dovuto) AS Ultimo_Dovuto, Partita_ID FROM
        (
            SELECT MAX(Totale_Dovuto+Diritto_Riscossione_Massimo) AS Ultimo_Dovuto, Partita_ID FROM atto GROUP BY Partita_ID
            UNION
            SELECT MAX(Totale_Dovuto) as Ultimo_Dovuto, Partita_ID FROM pignoramento_generale GROUP BY Partita_ID
        ) AS UA
	    GROUP BY Partita_ID
    )
    AS UDOC ON UDOC.Partita_ID = PT.ID
    LEFT JOIN 
    (
        SELECT 
        DOCS.*, DOCS.Dovuto - IFNULL(DOCS.Pagato,0) AS Residuo, DT.Description AS DocumentType, 
        SA.Data_Sospensione,
        PRT.Description AS Tipo_Spedizione
        
        FROM v_last_docs_notificati DOCS
        JOIN    
        (
            SELECT Partita_ID, MAX(Data_Notifica) AS Max_Data_Notifica FROM v_last_docs_notificati GROUP BY Partita_ID
        ) 
        AS MAXDOCS
        ON DOCS.Partita_ID=MAXDOCS.Partita_ID AND DOCS.Data_Notifica = MAXDOCS.Max_Data_Notifica

        JOIN document_type DT ON DT.Id = DOCS.DocumentTypeId
        LEFT JOIN sospensione_atto as SA ON DOCS.DocId = SA.ID_Atto_Pigno AND SA.Tipo_Atto=DOCS.DocumentTypeId
        LEFT JOIN print_type AS PRT ON PRT.Id = DOCS.PrintTypeId
    ) 
    AS DOC ON DOC.Partita_ID=PT.ID

    LEFT JOIN atto A ON DOC.DocId = A.ID AND DOC.DocumentTypeId IN (2,3,4,11,12)
    LEFT JOIN pignoramento_generale PG ON DOC.DocId = PG.ID AND DOC.DocumentTypeId NOT IN (2,3,4,11,12)

    WHERE PT.CC = "'.$request['CC'].'" ';

if($request['tipo_entrata']!="")
    $query.= 'AND PT.Tipo = "'.$request['tipo_entrata'].'" ';

if($request['ricorso']=="y")
    $query.= 'AND Data_Registrazione_Ricorso IS NOT NULL ';
else if($request['ricorso']=="n")
    $query.= 'AND Data_Registrazione_Ricorso IS NULL ';

if($request['archiviato']=="y")
    $query.= 'AND PT.Flag_Blocco_Coazione = "si" ';
else if($request['archiviato']=="n")
    $query.= 'AND (PT.Flag_Blocco_Coazione!= "si" OR PT.Flag_Blocco_Coazione IS NULL) ';

if($request['decesso']=="y")
    $query.= 'AND Data_Morte IS NOT NULL ';
else if($request['decesso']=="n")
    $query.= 'AND Data_Morte IS NULL ';

//$query.= 'GROUP BY PT.ID';



$a_export = $cls_db->getResults($cls_db->ExecuteQuery($query));
$rowsNumber = count($a_export);
if($rowsNumber == 0){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();

    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
    
    die;
}

//? INTESTAZIONE EXCEL 
$dataExcel[] = array(
    "<b>Ente</b>",
    "<b>CC</b>",
    "<b>Comune_ID</b>",
    "<b>Partita_ID</b>",
    "<b>Denominazione</b>",
    "<b>Genere</b>",
    "<b>Tipo entrata</b>",
    "<b>Anno riferimento</b>",
    "<b>Info cartella</b>",
    "<b>Data decorrenza interesi</b>",
    "<b>Tot. Tributi</b>",
    "<b>Cronologico</b>",
    "<b>Tipo atto</b>",
    "<b>Data notifica</b>",
    "<b>Tipo di Spedizione</b>",
    "<b>Ultimo dovuto</b>",
    "<b>Ultimo residuo</b>",
    "<b>Dovuto</b>",
    "<b>Pagato</b>",
    "<b>Residuo</b>",
    "<b>Modalità</b>",
    "<b>Giacenza</b>",
    "<b>Anomalia</b>",
    "<b>Data morte</b>",
    "<b>Rateizzazione</b>",
    "<b>Data richiesta rateizzazione</b>",
    "<b>Sospeso</b>",
    "<b>Data sospensione</b>",
    "<b>Archiviato</b>",
    "<b>Data archiviazione</b>",
    "<b>Discarico Art.19</b>",
    "<b>Data elaborazione discarico Art.19</b>",
    "<b>Data stampa discarico Art.19</b>",
    "<b>Discarico</b>",
    "<b>Data discarico</b>",
    "<b>Ricorso</b>",
    "<b>Data registrazione</b>",
    "<b>Data chiusura</b>",
    "<b>CF / P. IVA</b>",
    "<b>Paese di nascita</b>",
    "<b>Comune di nascita</b>",
    "<b>Provincia di nascita</b>",
    "<b>Data di nascita</b>",
    "<b>PEC</b>",
    "<b>Indirizzo residenza / sede</b>",
    "<b>Comune residenza / sede</b>",
    "<b>Paese residenza / sede</b>",
    "<b>Data stampa</b>",
    "<b>Interessi (ingiuntiva)</b>",
    "<b>Interessi codici tributo (ingiuntiva)</b>",
    "<b>Interessi precedenti (ingiuntiva)</b>",
    "<b>Spese notifica (ingiuntiva)</b>",
    "<b>Spese notifica precedenti (ingiuntiva)</b>",
    "<b>Ulteriori spese (ingiuntiva)</b>",
    "<b>Spese precedenti (ingiuntiva)</b>",
    "<b>Spese notifica pignoramento (ingiuntiva)</b>",
    "<b>Spese accessorie pignoramento (ingiuntiva)</b>",
    "<b>Interessi (coattiva)</b>",
    "<b>Spese notifica debitore (coattiva)</b>",
    "<b>Spese notifica terzi (coattiva)</b>",
    "<b>Totale spese notifica (coattiva)</b>",
    "<b>Totale spese accessorie (coattiva)</b>"
);

foreach($a_export as $key=>$row){

    // inserire ricerca atti ingiuntivi precedenti
    // se si tratta di atto coattivo aggiungere ricerca atti coattivi precedenti
    // inserire aggiunta dati atti precedenti a $row

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($key*100)/$rowsNumber ,2);
    session_write_close();

    if(empty($row['Rate_Previste']))
        $rateizzato = "No";
    else
        $rateizzato = "Si";

    if(empty($row['Flag_Sospensione']))
        $sospeso = "No";
    else
        $sospeso = "Si";

    if(empty($row['Flag_Archiviazione']))
        $archiviato = "No";
    else
        $archiviato = "Si";

    if(empty($row['Data_Registrazione_Ricorso']))
        $ricorso = "No";
    else
        $ricorso = "Si";

    if(empty($row['Sgravio_ID']))
        $sgravio = "No";
    else
        $sgravio = "Si"; 

    if(empty($row['Flag_Discarico']))
        $discarico = "No";
    else
        $discarico = "Si";

    $dataExcel[] = array(
        $row['Ente'],
        $row['CC'],
        $row['Comune_ID'],
        $row['ID'],
        $row['Denominazione_Utente'],
        $row['Genere'],
        $row['Tipo_Entrata'],
        $row['Anno_Riferimento'],
        $row['Info_Cartella'],
        $row['Data_Decorrenza_Interessi'],
        $row['Carico_Iniziale'],
        $row['Cronologico'],
        $row['DocumentType'],
        $row['Data_Notifica'],
        $row['Tipo_Spedizione'],
        $row['Ultimo_Dovuto'],
        $row['Ultimo_Residuo'],
        $row['Dovuto'],
        $row['Pagato'],
        $row['Residuo'],
        $row['Modalita'],
        $row['Giacenza'],
        $row['Anomalia'],
        $row['Data_Morte'],
        $rateizzato,
        $row['Data_Richiesta_Rate'],
        $sospeso,
        $row['Data_Sospensione'],
        $archiviato,
        $row['Data_Archiviazione'],
        $sgravio,
        $row['Data_Elaborazione_Sgravio'],
        $row['Data_Stampa_Sgravio'],
        $discarico,
        $row['Data_Discarico'],
        $ricorso,
        $row['Data_Registrazione_Ricorso'],
        $row['Data_Chiusura_Ricorso'],
        $row['CF'],
        $row['Paese_Nascita'],
        $row['Comune_Nascita'],
        $row['Provincia_Nascita'],
        $row['Data_N'],
        $row['PEC'],
        $row['Res_Ind'],
        $row['Res_Com'],
        $row['Res_Pae'],
        $row['Print_Date'],
        $row['Int_ING'],
        $row['Int_CT_ING'],
        $row['Int_Prec_ING'],
        $row['Spese_Not_ING'],
        $row['Spese_Not_Prec_ING'],
        $row['Spese_Ult_ING'],
        $row['Spese_Prec_ING'],
        $row['Spese_Not_Pigno_ING'],
        $row['Spese_Acc_Pigno_ING'],
        $row['Int_PIGNO'],
        $row['Spese_Not_Deb_PIGNO'],
        $row['Spese_Not_Ter_PIGNO'],
        $row['Spese_Not_Tot_PIGNO'],
        $row['Spese_Acc_Tot_PIGNO'],
    );
}

$pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
$nameFILE = "Esportazione.xlsx";
$pathFILE .= "/".$nameFILE;

SimpleXLSXGen::fromArray($dataExcel)
    ->setDefaultFont('Courier New')
    ->setDefaultFontSize(14)
    ->saveAs($pathFILE);

$pathWEBFILE = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

$file = SUPER_WEB_ROOT."/archivio/temp/".$nameFILE;

if(session_status() == PHP_SESSION_NONE)session_start();

header_remove('Set-Cookie');

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);


?>