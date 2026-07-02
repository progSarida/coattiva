<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

ini_set('memory_limit', '-1');

include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_Utils.php";
//include_once CLS . "/cls_GestionePartita.php";
//include_once(CLS . "/cls_CoazioneUtils.php");

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_utils = new cls_Utils();
//$cls_partita = new cls_GP();
//$cls_coazione = new cls_Coazione();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$cc = $cls_help->getVar("ente");
$last = $cls_help->getVar("last");

$da_g = $cls_help->getVar("da_partita_g");
$a_g = $cls_help->getVar("a_partita_g");
$da_s = $cls_help->getVar("da_partita_s");
$a_s = $cls_help->getVar("a_partita_s");
$da_c = $cls_help->getVar("da_partita_c");
$a_c = $cls_help->getVar("a_partita_c");

//var_dump($da_g);die;

$view = "";
$where_da = "";
$where_a = "";
$name_da = "";
$name_a = "";

if($da_g != 0){
    $where_da = " AND PT.Comune_ID >= ".$da_g." ";
    $name_da = $da_g;
}
else if($da_s != 0){
    $where_da = " AND PT.Comune_ID >= ".$da_s." ";
    $name_da = $da_s;
}
else if($da_c != 0){
    $where_da = " AND PT.Comune_ID >= ".$da_c." ";
    $name_da = $da_c;
}
    
if($a_g != 0){
    $where_a = " AND PT.Comune_ID <= ".$a_g." ";
    $name_a = $a_g;
}
else if($a_s != 0){
    $where_a = " AND PT.Comune_ID <= ".$a_s." ";
    $name_a = $a_s;
}
else if($a_c != 0){
    $where_a = " AND PT.Comune_ID <= ".$a_c." ";
    $name_a = $a_c;
}

    
//echo $view;die;

$_SESSION['progress'] = "0.00";
session_write_close();                                                                             

// intestazione file excel
$dataExcel[] = array(
                "<b>Ente</b>",
                "<b>CC</b>",
                "<b>Denominazione</b>",
                "<b>Comune_ID</b>",
                "<b>Partita_ID</b>",
                "<b>Genere</b>",
                "<b>Data di nascita</b>",
                "<b>Comune di nascita</b>",
                "<b>Provincia di nascita</b>",
                "<b>Paese di nascita</b>",
                "<b>Codice fiscale</b>",
                "<b>Partita IVA</b>",
                "<b>PEC</b>",
                "<b>Indirizzo residenza</b>",
                "<b>Comune residenza</b>",
                "<b>Paese residenza</b>",
                "<b>Tipo entrata</b>",
                "<b>Anno riferimento</b>",
                "<b>Codice tributo</b>",
                "<b>Testo tributo</b>",
                "<b>Imposta tributo</b>",
                "<b>Info cartella</b>",
                "<b>Cronologico</b>",
                "<b>Tipo atto</b>",
                "<b>Data notifica</b>",
                "<b>Tipo di Invio</b>",
                "<b>Data decorrenza interesi</b>",
                "<b>Tot. Tributi</b>",
                "<b>Dovuto</b>",
                "<b>Pagato</b>",
                "<b>Residuo</b>",
                "<b>Totale 1</b>",
                "<b>Totale 2</b>",
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
                "<b>Ricorso</b>",
                "<b>Data registrazione</b>",
                "<b>Data chiusura</b>",
                "<b>Rettificato</b>",
                "<b>Rielaborato</b>",
                "<b>Data stampa (ingiuntiva)</b>",
                "<b>Interessi (ingiuntiva)</b>",
                "<b>Interessi codici tributo (ingiuntiva)</b>",
                "<b>Interessi precedenti (ingiuntiva)</b>",
                "<b>Spese notifica (ingiuntiva)</b>",
                "<b>Spese notifica precedenti (ingiuntiva)</b>",
                "<b>Spese ulteriori (ingiuntiva)</b>",
                "<b>Spese precedenti (ingiuntiva)</b>",
                "<b>Spese notifica pignoramento (ingiuntiva)</b>",
                "<b>Spese accessorie pignoramento (ingiuntiva)</b>",
                "<b>Data stampa (coattiva)</b>",
                "<b>Interessi (coattiva)</b>",
                "<b>Spese notifica debitore (coattiva)</b>",
                "<b>Spese notifica terzi (coattiva)</b>",
                "<b>Totale spese notifica (coattiva)</b>",
                "<b>Totale spese accessorie (coattiva)</b>"
            );

//var_dump($c);die;

if($cc == 'D925' || $cc == 'U003' || $cc == 'C826'){
    //query per eccezioni
    $view = "";
    //$ente = $cls_db->getResults($cls_db->ExecuteQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$cc."'"))[0]["Denominazione"];
    //$view = str_replace(" ","_","v_partite_".strtolower($ente));
    switch($cc){
        case 'D925':
            $view = "v_partite_garlasco";
            break;
        case 'U003':
            $view = "v_partite_provincia_di_savona";
            break;
        case 'C826'.
            $view = "v_partite_cogorno";
            break;
    }

    //echo $view." - ";echo $where_da." - "; echo $where_a;die;

    $query = "(select 
                PT.ID as partita_id, PT.Comune_ID as comune_id, PT.Tipo as tipo_entrata, PT.Anno_Riferimento as anno_riferimento, PT.Flag_Sospensione as flag_sosp, SA.Data_Sospensione as data_sosp, 
                PT.Flag_Blocco_Coazione as flag_arch, PT.Data_Attivazione_Flag_Blocco_Coazione as data_arch, A.ID as id, A.DocumentTypeId as document_type_id, A.Data_Notifica as data_notifica, 
                A.Totale_Dovuto as dovuto, A.Rate_Previste as rate, A.Data_Richiesta_Rate as data_richiesta_rate, concat(A.ID_Cronologico,'/',A.Anno_Cronologico) as cronologico, 
                IF(A.Rielabora_Flag = 'si','SI','NO') as rielaborato, IF(A.Rettifica_Flag = 'si','SI','NO') as rettificato, U.Genere as genere, coalesce(nullif(U.Ditta,''),concat(U.Cognome,' ',U.Nome)) as denominazione, 
                U.Data_Nascita as data_nascita, U.Comune_Nascita as comune_nascita, U.Provincia_Nascita as provincia_nascita, U.Paese_Nascita as paese_nascita, U.PEC as pec, U.Codice_Fiscale as codice_fiscale, 
                U.Partita_IVA as partita_iva, U.Data_Morte as data_morte, EG.Denominazione as ente, T.Info_Cartella as info_cartella, T.Codice_Tributo as codice_tributo, T.Imposta as imposta_tributo, 
                T.Data_Decorrenza_Interessi as data_decorrenza_interessi, DT.Description as tipo_atto, IF(DT.TableTypeId IS NULL,8,DT.TableTypeId) as tipo, PRT.Description as tipo_invio,  
                (A.Totale_Dovuto - coalesce((select sum(P1.Importo) from pagamento as P1 where P1.Atto_ID = A.ID or (P1.Data_Pagamento<A.Data_Notifica and P1.Partita_ID = PT.ID and P1.DocumentTypeId IN (SELECT Id FROM document_type AS DT1 where DT1.TableTypeId = 1))),0)) as residuo, 
                (select sum(P2.Importo) from pagamento as P2 where PT.ID = P2.Partita_ID and P2.Atto_ID = A.ID) as pagato, PN.Descrizione as modalita, PN2.Descrizione as giacenza, PN3.Descrizione as anomalia, 
                AP.Start_Date as data_registrazione, AP.End_Date as data_chiusura, (select sum(T2.Imposta) from tributo as T2 where T.Partita_ID = T2.Partita_ID) as tot_tributi, CT.Testo_Codice as testo_codice, 
                IF(I.Via_ID=1,TC.Odonimo,TP.Nome) as indirizzo_residenza, IF(I.Via_ID=1,TC.Comune,TP.Comune) AS comune_residenza, IF(I.Via_ID=1,'Italia',TP.Paese) AS paese_residenza, 
                A.Data_Stampa as data_stampa_ing, A.Interessi as interessi_ing, A.Interessi_Codici_Tributo as interessi_codici_ing, A.Interessi_Precedenti as interessi_prec_ing, 
                A.Spese_Notifica as spese_not_ing, A.Spese_Notifica_Precedenti as spese_not_prec_ing, A.Ulteriori_Spese as spese_ulteriori_ing, A.Spese_Precedenti as spese_prec_ing, 
                A.Spese_Notifica_Pignoramento as spese_not_pigno_ing, A.Spese_Accessorie_Pignoramento as spese_acc_pigno_ing, '' as data_stampa_coa, '' as interessi_coa, '' as spese_not_deb_coa, 
                '' as spese_not_terzi_coa, '' as spese_not_tot_coa, '' as spese_acc_tot_coa, A.Diritto_Riscossione_Minimo as minimo, A.Diritto_Riscossione_Massimo as massimo 
                from ".$view." as PT 
                left join enti_gestiti as EG on PT.CC = EG.CC 
                left join tributo as T on PT.ID = T.Partita_ID  
                left join codice_tributo as CT on CT.Codice_Tributo = T.Codice_Tributo 
                left join indirizzo as I on I.Utente_ID = PT.Utente_ID 
                left join toponimo as TP on I.Via_ID = TP.ID 
                left join toponimi_cappati as TC on I.Via_Cap_ID = TC.ID ";

    if($last == 'si')
        $query.= "left join atto as A on PT.ID = A.Partita_ID ";
    else
        $query.= "left join atto as A on PT.ID = A.Partita_ID ";
                
    $query.= "left join document_type as DT on DT.Id = A.DocumentTypeId
                left join parametri_notifica as PN on PN.ID = A.Modalita_Notifica 
                left join parametri_notifica as PN2 on PN2.ID = A.Stato_Notifica 
                left join parametri_notifica as PN3 on PN3.ID = A.Motivo_Notifica 
                left join utente as U on U.ID = PT.Utente_ID 
                left join sospensione_atto as SA on A.ID = SA.ID_Atto_Pigno 
                left join appeal as AP on A.ID = AP.Act_ID 
                left join print_type AS PRT on PRT.Id = A.PrintTypeId 
                where PT.CC = '".$cc."' ".$where_da.$where_a." ";

if($last == 'si')
    $query.= "and A.Data_Notifica IS NOT NULL and A.Data_Notifica = (SELECT MAX(A2.Data_Notifica) FROM atto as A2 where PT.ID = A2.Partita_ID ) group by id ";

$query.= " )
            union all
            (select 
                PT.ID as partita_id, PT.Comune_ID as comune_id, PT.Tipo as tipo_entrata, PT.Anno_Riferimento as anno_riferimento, PT.Flag_Sospensione as flag_sosp, SA.Data_Sospensione as data_sosp, 
                PT.Flag_Blocco_Coazione as flag_arch, PT.Data_Attivazione_Flag_Blocco_Coazione as data_arch, PG.ID as id, PG.DocumentTypeId as document_type_id, NA.Data_Notifica as data_notifica, 
                PG.Totale_Dovuto as dovuto, PG.Rate_Previste as rate, PG.Data_Richiesta_Rate as data_richiesta_rate, concat(PG.ID_Cronologico,'/',PG.Anno_Cronologico) as cronologico, 
                ' ' as rielaborato, ' ' as rettificato, U.Genere as genere, coalesce(nullif(U.Ditta,''), concat(U.Cognome,' ',U.Nome)) as denominazione, 
                U.Data_Nascita as data_nascita, U.Comune_Nascita as comune_nascita, U.Provincia_Nascita as provincia_nascita, U.Paese_Nascita as paese_nascita, U.PEC as pec, U.Codice_Fiscale as codice_fiscale, 
                U.Partita_IVA as partita_iva, U.Data_Morte as data_morte, EG.Denominazione as ente, T.Info_Cartella as info_cartella, T.Codice_Tributo as codice_tributo, T.Imposta as imposta_tributo, 
                T.Data_Decorrenza_Interessi as data_decorrenza_interessi, DT.Description as tipo_atto, IF(DT.TableTypeId IS NULL,9,DT.TableTypeId) as tipo, PRT.Description as tipo_invio, 
                (PG.Totale_Dovuto - coalesce((select sum(P1.Importo) from pagamento as P1 where P1.Atto_ID = PG.ID or (P1.Data_Pagamento<NA.Data_Notifica and P1.Partita_ID = PT.ID and P1.DocumentTypeId IN (SELECT Id FROM document_type AS DT1 where DT1.TableTypeId = 2))),0)) as residuo, 
                (select sum(P2.Importo) from pagamento as P2 where PT.ID = P2.Partita_ID and P2.Atto_ID = PG.ID) as pagato, PN.Descrizione as modalita, PN2.Descrizione as giacenza, PN3.Descrizione as anomalia, 
                AP.Start_Date as data_registrazione, AP.End_Date as data_chiusura, (select sum(T2.Imposta) from tributo as T2 where T.Partita_ID = T2.Partita_ID) as tot_tributi, CT.Testo_Codice as testo_codice, 
                IF(I.Via_ID=1,TC.Odonimo,TP.Nome) as indirizzo_residenza, IF(I.Via_ID=1,TC.Comune,TP.Comune) AS comune_residenza, IF(I.Via_ID=1,'Italia',TP.Paese) AS paese_residenza, 
                '' as data_stampa_ing, '' as interessi_ing, '' as interessi_codici_ing, '' as interessi_prec_ing, 
                '' as spese_not_ing, '' as spese_not_prec_ing, '' as spese_ulteriori_ing, '' as spese_prec_ing, 
                '' as spese_not_pigno_ing, '' as spese_acc_pigno_ing, PG.Data_Stampa as data_stampa_coa, PG.Interessi as interessi_coa, PG.Spese_Notifica_Debitore as spese_not_deb_coa, 
                PG.Spese_Notifica_Terzi as spese_not_terzi_coa, PG.Totale_Spese_Notifica as spese_not_tot_coa, PG.Totale_Spese_Accessorie as spese_acc_tot_coa, 
                0 as minimo, 0 as massimo 
                from ".$view." as PT 
                left join enti_gestiti as EG on PT.CC = EG.CC 
                left join tributo as T on PT.ID = T.Partita_ID 
                left join codice_tributo as CT on CT.Codice_Tributo = T.Codice_Tributo 
                left join pignoramento_generale as PG on PT.ID = PG.Partita_ID 
                left join indirizzo as I on I.Utente_ID = PT.Utente_ID 
                left join toponimo as TP on I.Via_ID = TP.ID 
                left join toponimi_cappati as TC on I.Via_Cap_ID = TC.ID 
                left join document_type as DT on DT.Id = PG.DocumentTypeId ";

if($last == 'si')
    $query.= "join notifica_atto as NA on PG.ID = NA.Atto_Notificato_ID and NA.Tipo_Notifica = 'debitore' ";
else
    $query.= "left join notifica_atto as NA on PG.ID = NA.Atto_Notificato_ID and NA.Tipo_Notifica = 'debitore' ";

$query.= "left join parametri_notifica as PN on PN.ID = NA.Modalita_Notifica 
                left join parametri_notifica as PN2 on PN2.ID = NA.Stato_Notifica 
                left join parametri_notifica as PN3 on PN3.ID = NA.Motivo_Notifica 
                left join utente as U on U.ID = PT.Utente_ID 
                left join sospensione_atto as SA on PG.ID = SA.ID_Atto_Pigno 
                left join appeal as AP on PG.ID = AP.Act_ID 
                left join print_type AS PRT on PRT.Id = NA.PrintTypeId 
                where PT.CC = '".$cc."' ".$where_da.$where_a." ";

if($last == 'si')
    $query.= "AND NA.Data_Notifica IS NOT NULL  and NA.Data_Notifica = (SELECT MAX(NA2.Data_Notifica) FROM pignoramento_generale AS PG2 left join notifica_atto as NA2 on PG2.ID = NA2.Atto_Notificato_ID where PT.ID = PG2.Partita_ID ) 
            group by id ";

$query.= " )
            order by partita_id, tipo, id";
}
else{
// query generale
$query = "(select 
            PT.ID as partita_id, PT.Comune_ID as comune_id, PT.Tipo as tipo_entrata, PT.Anno_Riferimento as anno_riferimento, PT.Flag_Sospensione as flag_sosp, SA.Data_Sospensione as data_sosp, 
            PT.Flag_Blocco_Coazione as flag_arch, PT.Data_Attivazione_Flag_Blocco_Coazione as data_arch, A.ID as id, A.DocumentTypeId as document_type_id, A.Data_Notifica as data_notifica, 
            A.Totale_Dovuto as dovuto, A.Rate_Previste as rate, A.Data_Richiesta_Rate as data_richiesta_rate, concat(A.ID_Cronologico,'/',A.Anno_Cronologico) as cronologico, 
            IF(A.Rielabora_Flag = 'si','SI','NO') as rielaborato, IF(A.Rettifica_Flag = 'si','SI','NO') as rettificato, U.Genere as genere, coalesce(nullif(U.Ditta,''),concat(U.Cognome,' ',U.Nome)) as denominazione, 
            U.Data_Nascita as data_nascita, U.Comune_Nascita as comune_nascita, U.Provincia_Nascita as provincia_nascita, U.Paese_Nascita as paese_nascita, U.PEC as pec, U.Codice_Fiscale as codice_fiscale, 
            U.Partita_IVA as partita_iva, U.Data_Morte as data_morte, EG.Denominazione as ente, T.Info_Cartella as info_cartella, T.Codice_Tributo as codice_tributo, T.Imposta as imposta_tributo, 
            T.Data_Decorrenza_Interessi as data_decorrenza_interessi, DT.Description as tipo_atto, IF(DT.TableTypeId IS NULL,8,DT.TableTypeId) as tipo, PRT.Description as tipo_invio,  
            (A.Totale_Dovuto - coalesce((select sum(P1.Importo) from pagamento as P1 where (P1.Atto_ID = A.ID and P1.DocumentTypeId IN (SELECT Id FROM document_type AS DT1 where DT1.TableTypeId = 1)) or (P1.Data_Pagamento<A.Data_Notifica and P1.Partita_ID = PT.ID and P1.DocumentTypeId IN (SELECT Id FROM document_type AS DT1 where DT1.TableTypeId = 1))),0)) as residuo, 
            (select sum(P2.Importo) from pagamento as P2 where PT.ID = P2.Partita_ID and P2.Atto_ID = A.ID) as pagato, PN.Descrizione as modalita, PN2.Descrizione as giacenza, PN3.Descrizione as anomalia, 
            AP.Start_Date as data_registrazione, AP.End_Date as data_chiusura, (select sum(T2.Imposta) from tributo as T2 where T.Partita_ID = T2.Partita_ID) as tot_tributi, CT.Testo_Codice as testo_codice, 
            IF(I.Via_ID=1,TC.Odonimo,TP.Nome) as indirizzo_residenza, IF(I.Via_ID=1,TC.Comune,TP.Comune) AS comune_residenza, IF(I.Via_ID=1,'Italia',TP.Paese) AS paese_residenza, 
            A.Data_Stampa as data_stampa_ing, A.Interessi as interessi_ing, A.Interessi_Codici_Tributo as interessi_codici_ing, A.Interessi_Precedenti as interessi_prec_ing, 
            A.Spese_Notifica as spese_not_ing, A.Spese_Notifica_Precedenti as spese_not_prec_ing, A.Ulteriori_Spese as spese_ulteriori_ing, A.Spese_Precedenti as spese_prec_ing, 
            A.Spese_Notifica_Pignoramento as spese_not_pigno_ing, A.Spese_Accessorie_Pignoramento as spese_acc_pigno_ing, '' as data_stampa_coa, '' as interessi_coa, '' as spese_not_deb_coa, 
            '' as spese_not_terzi_coa, '' as spese_not_tot_coa, '' as spese_acc_tot_coa, A.Diritto_Riscossione_Minimo as minimo, A.Diritto_Riscossione_Massimo as massimo 
            from partita_tributi as PT 
            left join enti_gestiti as EG on PT.CC = EG.CC 
            left join tributo as T on PT.ID = T.Partita_ID  
            left join codice_tributo as CT on CT.Codice_Tributo = T.Codice_Tributo 
            left join indirizzo as I on I.Utente_ID = PT.Utente_ID 
            left join toponimo as TP on I.Via_ID = TP.ID 
            left join toponimi_cappati as TC on I.Via_Cap_ID = TC.ID 
            left join atto as A on PT.ID = A.Partita_ID 
            left join document_type as DT on DT.Id = A.DocumentTypeId
            left join parametri_notifica as PN on PN.ID = A.Modalita_Notifica 
            left join parametri_notifica as PN2 on PN2.ID = A.Stato_Notifica 
            left join parametri_notifica as PN3 on PN3.ID = A.Motivo_Notifica 
            left join utente as U on U.ID = PT.Utente_ID 
            left join sospensione_atto as SA on A.ID = SA.ID_Atto_Pigno 
            left join appeal as AP on A.ID = AP.Act_ID 
            left join print_type AS PRT on PRT.Id = A.PrintTypeId 
            where PT.CC = '".$cc."' ";

if($last == 'si')
    $query.= "and A.Data_Notifica IS NOT NULL and A.Data_Notifica = (SELECT MAX(A2.Data_Notifica) FROM atto as A2 where PT.ID = A2.Partita_ID ) group by id ";

$query.= " )
            union all
         (select 
            PT.ID as partita_id, PT.Comune_ID as comune_id, PT.Tipo as tipo_entrata, PT.Anno_Riferimento as anno_riferimento, PT.Flag_Sospensione as flag_sosp, SA.Data_Sospensione as data_sosp, 
            PT.Flag_Blocco_Coazione as flag_arch, PT.Data_Attivazione_Flag_Blocco_Coazione as data_arch, PG.ID as id, PG.DocumentTypeId as document_type_id, NA.Data_Notifica as data_notifica, 
            PG.Totale_Dovuto as dovuto, PG.Rate_Previste as rate, PG.Data_Richiesta_Rate as data_richiesta_rate, concat(PG.ID_Cronologico,'/',PG.Anno_Cronologico) as cronologico, 
            ' ' as rielaborato, ' ' as rettificato, U.Genere as genere, coalesce(nullif(U.Ditta,''), concat(U.Cognome,' ',U.Nome)) as denominazione, 
            U.Data_Nascita as data_nascita, U.Comune_Nascita as comune_nascita, U.Provincia_Nascita as provincia_nascita, U.Paese_Nascita as paese_nascita, U.PEC as pec, U.Codice_Fiscale as codice_fiscale, 
            U.Partita_IVA as partita_iva, U.Data_Morte as data_morte, EG.Denominazione as ente, T.Info_Cartella as info_cartella, T.Codice_Tributo as codice_tributo, T.Imposta as imposta_tributo, 
            T.Data_Decorrenza_Interessi as data_decorrenza_interessi, DT.Description as tipo_atto, IF(DT.TableTypeId IS NULL,9,DT.TableTypeId) as tipo, PRT.Description as tipo_invio, 
            (PG.Totale_Dovuto - coalesce((select sum(P1.Importo) from pagamento as P1 where (P1.Atto_ID = PG.ID and P1.DocumentTypeId IN (SELECT Id FROM document_type AS DT1 where DT1.TableTypeId = 2)) or (P1.Data_Pagamento<NA.Data_Notifica and P1.Partita_ID = PT.ID and P1.DocumentTypeId IN (SELECT Id FROM document_type AS DT1 where DT1.TableTypeId = 2))),0)) as residuo, 
            (select sum(P2.Importo) from pagamento as P2 where PT.ID = P2.Partita_ID and P2.Atto_ID = PG.ID) as pagato, PN.Descrizione as modalita, PN2.Descrizione as giacenza, PN3.Descrizione as anomalia, 
            AP.Start_Date as data_registrazione, AP.End_Date as data_chiusura, (select sum(T2.Imposta) from tributo as T2 where T.Partita_ID = T2.Partita_ID) as tot_tributi, CT.Testo_Codice as testo_codice, 
            IF(I.Via_ID=1,TC.Odonimo,TP.Nome) as indirizzo_residenza, IF(I.Via_ID=1,TC.Comune,TP.Comune) AS comune_residenza, IF(I.Via_ID=1,'Italia',TP.Paese) AS paese_residenza, 
            '' as data_stampa_ing, '' as interessi_ing, '' as interessi_codici_ing, '' as interessi_prec_ing, 
            '' as spese_not_ing, '' as spese_not_prec_ing, '' as spese_ulteriori_ing, '' as spese_prec_ing, 
            '' as spese_not_pigno_ing, '' as spese_acc_pigno_ing, PG.Data_Stampa as data_stampa_coa, PG.Interessi as interessi_coa, PG.Spese_Notifica_Debitore as spese_not_deb_coa, 
            PG.Spese_Notifica_Terzi as spese_not_terzi_coa, PG.Totale_Spese_Notifica as spese_not_tot_coa, PG.Totale_Spese_Accessorie as spese_acc_tot_coa, 
            0 as minimo, 0 as massimo 
            from partita_tributi as PT 
            left join enti_gestiti as EG on PT.CC = EG.CC 
            left join tributo as T on PT.ID = T.Partita_ID 
            left join codice_tributo as CT on CT.Codice_Tributo = T.Codice_Tributo 
            left join pignoramento_generale as PG on PT.ID = PG.Partita_ID 
            left join indirizzo as I on I.Utente_ID = PT.Utente_ID 
            left join toponimo as TP on I.Via_ID = TP.ID 
            left join toponimi_cappati as TC on I.Via_Cap_ID = TC.ID 
            left join document_type as DT on DT.Id = PG.DocumentTypeId 
            left join notifica_atto as NA on PG.ID = NA.Atto_Notificato_ID and NA.Tipo_Notifica = 'debitore' 
            left join parametri_notifica as PN on PN.ID = NA.Modalita_Notifica 
            left join parametri_notifica as PN2 on PN2.ID = NA.Stato_Notifica 
            left join parametri_notifica as PN3 on PN3.ID = NA.Motivo_Notifica 
            left join utente as U on U.ID = PT.Utente_ID 
            left join sospensione_atto as SA on PG.ID = SA.ID_Atto_Pigno 
            left join appeal as AP on PG.ID = AP.Act_ID 
            left join print_type AS PRT on PRT.Id = NA.PrintTypeId 
            where PT.CC = '".$cc."' ";

if($last == 'si')
    $query.= "and  NA.Data_Notifica IS NOT NULL  and NA.Data_Notifica = (SELECT MAX(NA2.Data_Notifica) FROM pignoramento_generale AS PG2 left join notifica_atto as NA2 on PG2.ID = NA2.Atto_Notificato_ID where PT.ID = PG2.Partita_ID ) 
            group by id ";

$query.= " )
            order by partita_id, tipo, id";
}
//echo $view;die;

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$count = count($result);

//var_dump($count);die;

if($count == 0){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = "100";
    session_write_close();

    echo json_encode([
        "error" => 2,
        "msg" => "Nessun risultato trovato!"
    ]);
    
    die;
}

for($i=0; $i < $count; $i++){

    if(session_status() == PHP_SESSION_NONE)session_start();
    $_SESSION['progress'] = number_format(($i*100)/$count ,2);
    session_write_close();

    if($result[$i]['rate'] == 0)
        $rateizzato = "No";
    else
        $rateizzato = "Si";
    if($result[$i]['flag_sosp'] == null)
        $sospeso = "No";
    else
        $sospeso = "Si";
    if($result[$i]['flag_arch'] == null)
        $archiviato = "No";
    else
        $archiviato = "Si";
    if($result[$i]['data_registrazione'] == null)
        $ricorso = "No";
    else
        $ricorso = "Si";

    $pagato = $result[$i]['dovuto'] - $result[$i]['residuo'];

    $dataExcel[] = array(
        $result[$i]['ente'],
        $cc,
        $result[$i]['denominazione'],
        $result[$i]['comune_id'],
        $result[$i]['partita_id'],
        $result[$i]['genere'],
        $result[$i]['data_nascita'],
        $result[$i]['comune_nascita'],
        $result[$i]['provincia_nascita'],
        $result[$i]['paese_nascita'],
        $result[$i]['codice_fiscale'],
        $result[$i]['partita_iva'],
        $result[$i]['pec'],
        $result[$i]['indirizzo_residenza'],
        $result[$i]['comune_residenza'],
        $result[$i]['paese_residenza'],
        $result[$i]['tipo_entrata'],
        $result[$i]['anno_riferimento'],
        $result[$i]['codice_tributo'],
        $result[$i]['testo_codice'],
        $result[$i]['imposta_tributo'],
        $result[$i]['info_cartella'],
        $result[$i]['cronologico'],
        $result[$i]['tipo_atto'],
        $result[$i]['data_notifica'],
        $result[$i]['tipo_invio'],
        $result[$i]['data_decorrenza_interessi'],
        $result[$i]['tot_tributi'],
        $result[$i]['dovuto'],
        $pagato,
        $result[$i]['residuo'],
        $result[$i]['dovuto'] + $result[$i]['minimo'],
        $result[$i]['dovuto'] + $result[$i]['massimo'],
        $result[$i]['modalita'],
        $result[$i]['giacenza'],
        $result[$i]['anomalia'],
        $result[$i]['data_morte'],
        $rateizzato,
        $result[$i]['data_richiesta_rate'],
        $sospeso,
        $result[$i]['data_sosp'],
        $archiviato,
        $result[$i]['data_arch'],
        $ricorso,
        $result[$i]['data_registrazione'],
        $result[$i]['data_chiusura'],
        $result[$i]['rettificato'],
        $result[$i]['rielaborato'],
        $result[$i]['data_stampa_ing'],
        $result[$i]['interessi_ing'],
        $result[$i]['interessi_codici_ing'],
        $result[$i]['interessi_prec_ing'],
        $result[$i]['spese_not_ing'],
        $result[$i]['spese_not_prec_ing'],
        $result[$i]['spese_ulteriori_ing'],
        $result[$i]['spese_prec_ing'],
        $result[$i]['spese_not_pigno_ing'],
        $result[$i]['spese_acc_pigno_ing'],
        $result[$i]['data_stampa_coa'],
        $result[$i]['interessi_coa'],
        $result[$i]['spese_not_deb_coa'],
        $result[$i]['spese_not_terzi_coa'],
        $result[$i]['spese_not_tot_coa'],
        $result[$i]['spese_acc_tot_coa']
    );

    //$_SESSION['progress'] = number_format(($i*100)/$count ,0);
}

$pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
if($last == 'si')
    $nameFILE = "Esportazione_".$result[0]['ente']."_ultimi notificati";
else
    $nameFILE = "Esportazione_".$result[0]['ente']."_completa";
if($name_da != ""){
    $nameFILE .= "_da-".$name_da;
}
if($name_a != ""){
    $nameFILE .= "_a-".$name_a;
}
$nameFILE .= ".xlsx";
$pathFILE .= "/".$nameFILE;

//echo $nameFILE;die;

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