<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/cls_pdf2.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_GestionePartita.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_LOG.php";

$cls_db      = new cls_db();
$cls_help    = new cls_help();
$cls_date    = new cls_DateTimeI("IT");
$cls_elab    = new cls_elaborazioniUtils();
$cls_utils   = new cls_Utils();
$cls_partita = new cls_GP();
$log         = new LOG();

$log->info("=== INIZIO print_estratto.php ===");

$c    = $cls_help->getVar("c");
$a    = $cls_help->getVar("a");
$file = "";

if (session_status() == PHP_SESSION_NONE) session_start();
$_SESSION['progress'] = "0.00";
session_write_close();

$cc_ente     = $cls_help->getVar("ente");
$soggetto_id = $cls_help->getVar("soggetto");
$da_anno     = $cls_help->getVar("da_anno");
$a_anno      = $cls_help->getVar("a_anno");
$da_partita  = $cls_help->getVar("da_partita");
$a_partita   = $cls_help->getVar("a_partita");
$da_data     = $cls_help->getVar("da_data") . " 00:00:00";
$a_data      = $cls_help->getVar("a_data")  . " 23:59:59";
$printType   = $cls_help->getVar("printType");
// if ($printType != 'excel') $printType = 'pdf';

$filter = array();

try {

    // ===================== QUERY / FILTRI =====================
    $query = "SELECT * FROM partita_tributi WHERE CC = '" . $cc_ente . "'";

    if ($cc_ente != 0) {
        $ente_r = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT CC, Denominazione FROM enti_gestiti WHERE CC = " . $cc_ente));
        $filter["ente"] = $ente_r[0]['Denominazione'] ?? '';
    }
    if ($soggetto_id != 0) {
        $utente_filtro = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT ID, IF(Genere='D', Ditta, CONCAT(Cognome,' ',Nome)) AS Utente FROM utente WHERE ID = " . $soggetto_id));
        $filter["utente"] = $utente_filtro[0]['Utente'] ?? '';
        $query .= " AND Utente_ID = " . $soggetto_id;
    }
    if ($da_anno != 0) { $filter["da_anno"] = $da_anno; $query .= " AND Anno_Riferimento >= " . $da_anno; }
    if ($a_anno  != 0) { $filter["a_anno"]  = $a_anno;  $query .= " AND Anno_Riferimento <= " . $a_anno;  }
    if ($da_partita != "0") { $filter["da_partita"] = $da_partita; $query .= " AND Comune_ID >= " . $da_partita; }
    if ($a_partita  != "0") { $filter["a_partita"]  = $a_partita;  $query .= " AND Comune_ID <= " . $a_partita;  }
    $query .= " ORDER BY Comune_ID ASC";

    $partite = $cls_db->getResults($cls_db->ExecuteQuery($query));
    $count   = count($partite);

    if ($count == 0) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['progress'] = "100";
        session_write_close();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["error" => 2, "msg" => "Nessun risultato trovato!"]);
        exit;
    }

    $log->info("Partite trovate: " . $count);

    $enteInfo = $cls_db->getArrayLineNull(
        $cls_db->ExecuteQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '" . $cc_ente . "'"),
        "enti_gestiti"
    );
    $nomeEnteIntestazione = $enteInfo['Denominazione'] ?? "";

    $statiNotifica   = $cls_db->getResults($cls_db->ExecuteQuery("SELECT ID, Descrizione FROM parametri_notifica"));
    $a_statiNotifica = array();
    foreach ($statiNotifica as $el) $a_statiNotifica[$el['ID']] = $el['Descrizione'];

    // ===================== SETUP PDF =====================
    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(false);

    $styleSpesso       = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0,     'color' => array(0,0,0));
    $styleTratteggiato = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '2,1', 'color' => array(0,0,0));
    $styleRetta        = array('dash' => '0');

    $pdf->AddPage('L');

    $larghezza_pag = $pdf->getPageWidth();   // es. 297mm in landscape A4
    $altezza_pag   = $pdf->getPageHeight();  // es. 210mm in landscape A4
    $margine_top   = 10;
    $margine_fondo = 15;  // mm liberi in fondo: sotto questa soglia si cambia pagina

    // larghezze colonne
    $array_width_utente  = array(100, 60, ($larghezza_pag - 160 - 20));
    $array_align_utente  = array("L", "L", "L");
    $array_width_partita = array($larghezza_pag - 20);
    $array_align_partita = array("L");
    $w_num = 10; 
    $w_tributo = 20; 
    $w_anno = 15; 
    $w_carico = 25;
    $w_rest    = $larghezza_pag - 20 - $w_num - $w_tributo - $w_anno - $w_carico;
    $array_width_atti  = array($w_num, $w_tributo, $w_anno, $w_carico, $w_rest * 0.55, $w_rest * 0.45);
    $array_align_atti  = array("C", "L", "L", "R", "L", "L");
    $array_intestaz_atti = array("#", "Tributo", "Anno", "Carico", "Descrizione", "Notifica");
    $array_width_tot   = array(45, 28);
    $array_align_tot   = array("R", "R");

    // altezze approssimative di ogni blocco (mm) — usate solo per decidere i salti pagina
    $h_ente     = 10;  // intestazione ente (Cell + Ln(5))
    $h_utente   = 7;   // riga utente
    $h_partita  = 6;   // riga info partita
    $h_intcol   = 6;   // intestazione colonne atti
    $h_riga     = 5;   // singola riga atto
    $h_totale   = 6;   // riga totale partita
    // spazio minimo per aprire un nuovo blocco partita: ente+utente+partita+colonne+1 riga+totale
    $h_min_apertura = $h_ente + $h_utente + $h_partita + $h_intcol + $h_riga + $h_totale;
    // spazio minimo per aggiungere una riga atto: la riga stessa + riga totale
    $h_min_riga     = $h_riga + $h_totale;

    // ===================== EXCEL intestazione (Classico) =====================
    $dataExcel = array();
    $dataExcel[] = array(
        "<b>#</b>","<b>Comune ID</b>","<b>Soggetto</b>","<b>CF/P.IVA</b>","<b>Indirizzo</b>",
        "<b>Anno Rif.</b>","<b>Tipo</b>","<b>Info Cartella</b>","<b>Carico</b>",
        "<b>Descrizione</b>","<b>Modalità e Stato Notifica</b>","<b>Data Notifica</b>"
    );
    // ===================== EXCEL intestazione (Simile a PDF) =====================
    $dataExcelPDF = array();
    $dataExcelPDF[] = array(
        "<b>Soggetto</b>","<b>CF/P.IVA</b>","<b>Indirizzo</b>"
    );
    $dataExcelPDF[] = array(
        "<b>Comune ID</b>","<b>Anno Rif.</b>","<b>Tipo</b>","<b>Info Cartella</b>"
    );
    $dataExcelPDF[] = array(
        "<b>Tributo</b>","<b>Descrizione</b>","<b>Carico</b>","<b>Modalità e Stato Notifica</b>","<b>Data Notifica</b>"
    );
    $dataExcelPDF[] = array();
    $totale_carico_complessivo = 0;

    // ---- stampa intestazione ente sulla prima pagina ----
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 5, $nomeEnteIntestazione, 0, 0, "L");
    $pdf->Cell(0, 5, date("d/m/Y"), 0, 1, "R");
    $pdf->Ln(3);

    // ===================== CICLO PARTITE =====================
    $i = -1;
    foreach ($partite as $key => $p) {
        $i++;
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['progress'] = number_format(($i * 100) / $count, 2);
        session_write_close();

        $partita = $cls_partita->getDataPartita($p['ID'], $c, $a);

        // dati utente
        $utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery(
            "SELECT COALESCE(NULLIF(U.Ditta,''),CONCAT(U.Cognome,' ',U.Nome)) AS Utente,
                    COALESCE(NULLIF(U.Partita_Iva,''),U.Codice_Fiscale) AS CF,
                    COALESCE(CONCAT(T.Nome,' ',A.Civico,', ',A.Cap,' ',A.Comune,' (',A.Provincia,')'),'') AS Indirizzo
             FROM utente U
             LEFT JOIN indirizzo A ON A.Utente_ID = U.ID AND A.Tipo = 'res'
             LEFT JOIN toponimo T ON T.ID = A.Via_ID
             WHERE U.ID = " . intval($partita['Utente_ID'])
        ), "utente");

        $nomeUtente    = strtoupper((string)($utente['Utente']   ?? ''));
        $cfUtente      = (string)($utente['CF']       ?? '');
        $indirizzoUtente = (string)($utente['Indirizzo'] ?? '');

        $infoPartitaTesto  = "Partita N. " . $partita['Comune_ID'];
        $infoPartitaTesto .= "   -   Anno Riferimento " . $partita['Anno_Riferimento'];
        $infoPartitaTesto .= "   -   Tipo " . $partita['Tipo'];
        if (!empty($partita['Tributo'][0]['Info_Cartella']))
            $infoPartitaTesto .= "   -   Cartella: " . $partita['Tributo'][0]['Info_Cartella'];

        // ---- costruzione lista righe atti ----
        $attiLista = array();
        $j = 1;

        // tributi
        $tributi = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT T.Codice_Tributo, T.Imposta, T.Info_Cartella, PT.Anno_Riferimento
             FROM partita_tributi PT
             JOIN tributo T ON PT.ID = T.Partita_ID
             WHERE PT.CC = '" . $cc_ente . "' AND PT.Comune_ID = " . intval($partita['Comune_ID'])
        ));
        foreach ($tributi as $t) {
            $attiLista[] = array(
                'num'            => $j,
                'codice'         => (string)($t['Codice_Tributo'] ?? ''),
                'anno'           => (string)($t['Anno_Riferimento'] ?? ''),
                'carico'         => (float)($t['Imposta'] ?? 0),
                'descrizione'    => (string)($t['Info_Cartella'] ?? ''),
                'notifica'       => '',
                'data_notifica'  => '');
            $j++;
        }

        // atti ingiuntivi
        $atti = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT A.ID_Cronologico, A.Anno_Cronologico, A.Interessi, A.Spese_Notifica,
                    A.Data_Notifica, A.Modalita_Notifica, A.Stato_Notifica,
                    DT.Description, PT.Anno_Riferimento
             FROM partita_tributi PT
             JOIN atto A ON PT.ID = A.Partita_ID
             JOIN document_type DT ON DT.Id = A.DocumentTypeId
             WHERE PT.CC = '" . $cc_ente . "' AND PT.Comune_ID = " . intval($partita['Comune_ID'])
        ));
        foreach ($atti as $t) {
            $mod  = $a_statiNotifica[$t['Modalita_Notifica']] ?? '';
            $sta  = $a_statiNotifica[$t['Stato_Notifica']]    ?? '';
            $not  = trim($mod . ($mod && $sta ? ' - ' : '') . $sta);
            $dataFmt = $cls_date->Get_DateNewFormat($t['Data_Notifica'], "DB");
            $crono   = $t['Description'] . ' cron. ' . $t['ID_Cronologico'] . '/' . $t['Anno_Cronologico'];
            if ($dataFmt) $crono .= ' not. il ' . $dataFmt;
            // if (floatval($t['Spese_Notifica']) != 0)
                $attiLista[] = array(
                    'num'            => $j,
                    'codice'         => 'NOPR',
                    'anno'           => (string)($t['Anno_Riferimento'] ?? ''),
                    'carico'         => (float)($t['Spese_Notifica'] ?? 0),
                    'descrizione'    => 'Sp.Not. - ' . $crono,
                    'notifica'       => $not,
                    'data_notifica'  => $dataFmt
                );
            $j++;
            // if (floatval($t['Interessi']) != 0)
                $attiLista[] = array(
                    'num'            => $j,
                    'codice'         => 'ULIT',
                    'anno'           => (string)($t['Anno_Riferimento'] ?? ''),
                    'carico'         => (float)($t['Interessi'] ?? 0),
                    'descrizione'    => 'Ult.Inters. - ' . $crono,
                    'notifica'       => $not,
                    'data_notifica'  => $dataFmt
                );
            $j++;
        }

        // atti coattivi
        $pignoramenti = $cls_db->getResults($cls_db->ExecuteQuery(
            "SELECT 
            PT.Anno_Riferimento as PT_Anno_Riferimento,
            PG.ID_Cronologico as PG_ID_Cronologico, PG.Anno_Cronologico as PG_Anno_Cronologico, PG.Interessi as PG_Interessi, 
            PG.Totale_Spese_Accessorie as PG_Totale_Spese_Accessorie, PG.DocumentTypeId as PG_DocumentTypeId, 
            DT.Description ,
            NA.Modalita_Notifica as NA_Modalita_Notifica, NA.Stato_Notifica as NA_Stato_Notifica, NA.Motivo_Notifica as NA_Motivo_Notifica, 
            NA.Spese_Notifica as NA_Spese_Notifica, NA.Data_Notifica as NA_Data_Notifica
            FROM notifica_atto as NA 
            LEFT JOIN pignoramento_generale PG ON PG.ID=NA.Atto_Notificato_ID 
            JOIN partita_tributi as PT on PT.ID = PG.Partita_ID
            JOIN document_type as DT on DT.Id = PG.DocumentTypeId
            LEFT JOIN pignoramento_presso_terzi PPT ON PPT.Pignoramento_ID = NA.Atto_Notificato_ID 
            WHERE PT.CC = '" . $cc_ente . "' AND PT.Comune_ID = " . intval($partita['Comune_ID']) . " AND Tipo_Atto_Notificato = 'pignoramento'
            GROUP BY NA.ID 
            ORDER BY CASE WHEN NA.Tipo_Notifica = 'debitore' THEN 1 ELSE 2 END, NA.Tipo_Notifica ASC, NA.ID_Collegamento ASC"
        ));
        foreach ($pignoramenti as $pigno) {
            $mod  = $a_statiNotifica[$pigno['NA_Modalita_Notifica']] ?? '';
            $sta  = $a_statiNotifica[$pigno['NA_Stato_Notifica']]    ?? '';
            $not  = trim($mod . ($mod && $sta ? ' - ' : '') . $sta);
            $dataFmt = $cls_date->Get_DateNewFormat($pigno['NA_Data_Notifica'], "DB");
            $crono   = $pigno['Description'] . ' cron. ' . $pigno['PG_ID_Cronologico'] . '/' . $pigno['PG_Anno_Cronologico'];
            if ($dataFmt) $crono .= ' not. il ' . $dataFmt;
            // if (floatval($pigno['NA_Spese_Notifica']) != 0)
                $attiLista[] = array(
                    'num'            => $j,
                    'codice'         => 'NOTP',
                    'anno'           => (string)($pigno['PT_Anno_Riferimento'] ?? ''),
                    'carico'         => (float)($pigno['NA_Spese_Notifica'] ?? 0),
                    'descrizione'    => 'Sp.Not.Coatt. - ' . $crono,
                    'notifica'       => $not,
                    'data_notifica'  => $dataFmt);
            $j++;
        }
        if(count($pignoramenti) > 0){
            $pigno = $pignoramenti[0];
            $mod  = $a_statiNotifica[$pigno['NA_Modalita_Notifica']] ?? '';
            $sta  = $a_statiNotifica[$pigno['NA_Stato_Notifica']]    ?? '';
            $not  = trim($mod . ($mod && $sta ? ' - ' : '') . $sta);
            $dataFmt = $cls_date->Get_DateNewFormat($pigno['NA_Data_Notifica'], "DB");
            $crono   = $pignoramenti[0]['Description'] . ' cron. ' . $pignoramenti[0]['PG_ID_Cronologico'] . '/' . $pignoramenti[0]['PG_Anno_Cronologico'];
            $dataFmt = $cls_date->Get_DateNewFormat($pignoramenti[0]['NA_Data_Notifica'], "DB");
            // if (floatval($pigno['PG_Totale_Spese_Accessorie']) != 0)
                $attiLista[] = array(
                    'num'            => $j,
                    'codice'         => 'SPPR',
                    'anno'           => (string)($pignoramenti[0]['PT_Anno_Riferimento'] ?? ''),
                    'carico'         => (float)($pignoramenti[0]['PG_Totale_Spese_Accessorie'] ?? 0),
                    'descrizione'    => 'Sp.Acc.Coatt. - ' . $crono,
                    'notifica'       => '',
                    'data_notifica'  => $dataFmt);
            $j++;
            // if (floatval($pigno['PG_Interessi']) != 0)
                $attiLista[] = array(
                    'num'            => $j,
                    'codice'         => 'ULIT',
                    'anno'           => (string)($pignoramenti[0]['PT_Anno_Riferimento'] ?? ''),
                    'carico'         => (float)($pignoramenti[0]['PG_Interessi'] ?? 0),
                    'descrizione'    => 'Ult.Inters. - ' . $crono,
                    'notifica'       => '',
                    'data_notifica'  => $dataFmt);
            $j++;
        }

        // ===================== DISEGNO PDF =====================
        // Il meccanismo è:
        // 1. Prima di aprire l'header di una partita: controllo se c'è spazio minimo (h_min_apertura).
        //    Se no → nuova pagina. Non usiamo pdf_check: l'altezza esatta non si conosce a priori
        //    perché dipende dal numero di atti; ci interessa solo garantire che header + 1 riga ci stiano.
        // 2. Prima di ogni singola riga atto: controllo se c'è spazio per la riga + riga totale.
        //    Se no → nuova pagina, si ristampa l'header della partita (con "(continua)"),
        //    e SI CONTINUA il foreach degli atti dal punto in cui eravamo.
        // 3. Prima della riga totale: stesso controllo, solo per la riga totale.
        //
        // IMPORTANTE: non si usano closure per evitare problemi di cattura del cursore Y di cls_pdf2.
        //             L'header viene inlinato dove serve, con $pdf->SetY() esplicito dopo AddPage.

        // --- helper inline: stampa intestazione ente su nuova pagina ---
        // (usato 3 volte: apertura partita, continuazione atti, totale)
        // non è una closure: viene copiato dove serve per evitare il problema di cattura

        // APERTURA BLOCCO PARTITA
        // Se lo spazio residuo non basta nemmeno per l'header minimo → nuova pagina
        if ($pdf->GetY() > ($altezza_pag - $margine_fondo - $h_min_apertura)) {
            $pdf->AddPage('L');
            $pdf->SetY($margine_top);  // riposiziona esplicitamente dopo AddPage
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 5, $nomeEnteIntestazione, 0, 0, "L");
            $pdf->Cell(0, 5, date("d/m/Y"), 0, 1, "R");
            $pdf->Ln(3);
        } else {
            $pdf->Ln(3); // spaziatura tra partite
        }

        // Header utente
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setCellPaddings(2, 1, 2, 0);
        $pdf->setRow(
            array($nomeUtente, $cfUtente, $indirizzoUtente),
            "up", $styleSpesso, $array_align_utente, 0, $array_width_utente
        );

        // Info partita
        $pdf->SetFont('Arial', '', 9);
        $pdf->setCellPaddings(2, 0, 2, 0);
        $pdf->setRow(
            array($infoPartitaTesto),
            "no", $styleRetta, $array_align_partita, 0, $array_width_partita
        );

        // Intestazione colonne atti
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->setRow(
            $array_intestaz_atti,
            "down", $styleTratteggiato, $array_align_atti, 0, $array_width_atti
        );

        // RIGHE ATTI — una per una con controllo salto pagina
        $totCaricoPartita  = 0;
        $primaRigaPartita  = true;   // usato per sapere se siamo in continuazione

        foreach ($attiLista as $atto) {
            $carico = floatval($atto['carico']);
            $totCaricoPartita += $carico;

            // Controllo spazio: serve posto per questa riga + la riga totale
            if ($pdf->GetY() > ($altezza_pag - $margine_fondo - $h_min_riga)) {

                // SALTO PAGINA INTERNO ALLA PARTITA
                $pdf->AddPage('L');
                $pdf->SetY($margine_top);  // riposiziona esplicitamente

                // intestazione ente
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(0, 5, $nomeEnteIntestazione, 0, 0, "L");
                $pdf->Cell(0, 5, date("d/m/Y"), 0, 1, "R");
                $pdf->Ln(3);

                // header utente con "(continua)"
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->setCellPaddings(2, 1, 2, 0);
                $pdf->setRow(
                    array($nomeUtente . ' (continua)', $cfUtente, $indirizzoUtente),
                    "up", $styleSpesso, $array_align_utente, 0, $array_width_utente
                );

                // info partita
                $pdf->SetFont('Arial', '', 9);
                $pdf->setCellPaddings(2, 0.5, 2, 0.5);
                $pdf->setRow(
                    array($infoPartitaTesto),
                    "no", $styleRetta, $array_align_partita, 0, $array_width_partita
                );

                // intestazione colonne atti
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->setRow(
                    $array_intestaz_atti,
                    "down", $styleTratteggiato, $array_align_atti, 0, $array_width_atti
                );
            }

            // Disegna la riga atto corrente
            $pdf->SetFont('Arial', '', 8);
            $pdf->setCellPaddings(2, 0.5, 2, 0.5);
            $pdf->setRow(
                array(
                    (string)$atto['num'],
                    (string)$atto['codice'],
                    (string)$atto['anno'],
                    number_format($carico, 2, ',', '.'),
                    (string)$atto['descrizione'],
                    (string)$atto['notifica'],
                ),
                "no", $styleRetta, $array_align_atti, 0, $array_width_atti
            );

            // Excel Classico
            $dataExcel[] = array(
                (string)$atto['num'],
                $atto['num'] == 1 ? $partita['Comune_ID'] : '',
                $atto['num'] == 1 ? $nomeUtente : '',
                $atto['num'] == 1 ? $cfUtente : '',
                $atto['num'] == 1 ? $indirizzoUtente : '',
                $atto['num'] == 1 ? $partita['Anno_Riferimento'] : '',
                $atto['num'] == 1 ? $partita['Tipo'] : '',
                $atto['num'] == 1 ? $partita['Tributo'][0]['Info_Cartella'] ?? '' : '',
                number_format($carico, 2, ',', '.'),
                (string)$atto['descrizione'],
                (string)$atto['notifica'],
                (string)$atto['data_notifica'],
            );
            // Excel PDF-simile
            if ($atto['num'] == 1){
                $dataExcelPDF[] = array(
                    $nomeUtente, 
                    $cfUtente, 
                    $indirizzoUtente
                );
                $dataExcelPDF[] = array(
                    (string)$partita['Comune_ID'], 
                    (string)$partita['Anno_Riferimento'], 
                    $partita['Tipo'], 
                    $partita['Tributo'][0]['Info_Cartella'] ?? ''
                );
            }
            $dataExcelPDF[] = array(
                (string)$atto['codice'],
                number_format($carico, 2, ',', '.'),
                (string)$atto['descrizione'],
                (string)$atto['notifica']
            );
        } // fine foreach attiLista

        // RIGA TOTALE PARTITA
        // Controllo spazio: se non c'è posto nemmeno per il totale → nuova pagina
        if ($pdf->GetY() > ($altezza_pag - $margine_fondo - $h_totale)) {
            $pdf->AddPage('L');
            $pdf->SetY($margine_top);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 5, $nomeEnteIntestazione, 0, 0, "L");
            $pdf->Cell(0, 5, date("d/m/Y"), 0, 1, "R");
            $pdf->Ln(3);
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->setCellPaddings(2, 0, 2, 0);
        $pdf->setRow(
            array("TOTALE PARTITA", number_format($totCaricoPartita, 2, ',', '.') . " €"),
            "no", $styleRetta, $array_align_tot, 0, $array_width_tot
        );

        $totale_carico_complessivo += $totCaricoPartita;

        $dataExcel[] = array("","","","","","","","TOTALE PARTITA", number_format($totCaricoPartita,2,',','.'),"","","");
        $dataExcel[] = array();

        $dataExcelPDF[] = array("TOTALE PARTITA", number_format($totCaricoPartita,2,',','.'),"","");
        $dataExcelPDF[] = array();

    } // fine foreach partite

    // ===================== FRONTESPIZIO =====================
    $numPagineContenuto = $pdf->PageNo();

    $pdf->AddPage('L');
    $pdf->SetY($margine_top);
    $pdf->setCellPaddings(2, 0, 2, 1);

    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 0, strtoupper("STAMPA ESTRATTO CONTO DI DEBITO " . strtoupper($nomeEnteIntestazione)), "B", 1, 'L', 0, '', 0, false, 'T', 'M');
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 0, "Stampato in data " . date("d/m/Y"), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Ln(5);

    $a_filters = $cls_elab->getFiltersDescription($filter);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 0, "Filtri applicati", "B", 1, 'L');
    $pdf->Ln(2);
    if (empty($a_filters)) {
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 6, "Nessun filtro applicato", 0, 1, 'L');
    } else {
        foreach ($a_filters as $f) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(60, 6, $f['label'], 0, 0, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 6, $f['value'], 0, 1, 'L');
        }
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 0, "Riepilogo", "B", 1, 'L');
    $pdf->Ln(2);

    foreach (array(
        array("label" => "Numero pagine",  "value" => $numPagineContenuto + 1),
        array("label" => "Numero partite", "value" => $count),
        array("label" => "Totale dovuto",  "value" => number_format($totale_carico_complessivo, 2, ',', '.') . " €"),
    ) as $r) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 6, $r['label'], 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, $r['value'], 0, 1, 'L');
    }

    $pdf->movePage($pdf->PageNo(), 1);

    // ===================== SALVATAGGIO =====================
    $pathFILE = $cls_utils->crea_dir(SUPER_ROOT . "/archivio/temp");
    $suffisso = $cc_ente . "_" . date("Ymd_His");

    if ($printType == 'excel') {
        $nameFILE = "Estratto_Conto_Debito_" . $suffisso . ".xlsx";
        $pathFILE .= "/" . $nameFILE;
        if (count($dataExcel) > 1)
            SimpleXLSXGen::fromArray($dataExcel)
                ->setDefaultFont('Courier New')
                ->setDefaultFontSize(10)
                ->saveAs($pathFILE);
    } else if ($printType == 'mix'){
        $nameFILE = "Estratto_Conto_Debito_" . $suffisso . ".xlsx";
        $pathFILE .= "/" . $nameFILE;
        if (count($dataExcelPDF) > 1)
            SimpleXLSXGen::fromArray($dataExcelPDF)
                ->setDefaultFont('Courier New')
                ->setDefaultFontSize(10)
                ->saveAs($pathFILE);
    } else {
        $nameFILE = "Estratto_Conto_Debito_" . $suffisso . ".pdf";
        $pathFILE .= "/" . $nameFILE;
        $pdf->Output($pathFILE, 'F');
    }

    $file = SUPER_WEB_ROOT . "/archivio/temp/" . $nameFILE;

    if (session_status() == PHP_SESSION_NONE) session_start();
    $_SESSION['progress'] = "100";
    session_write_close();

    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "path"  => (string)$file,
        "error" => 0,
        "msg"   => "File stampato correttamente!"
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    $log->error("ERRORE riga " . $e->getLine() . ": " . $e->getMessage());
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["path" => "", "error" => 2, "msg" => "Errore interno: " . $e->getMessage()]);
    exit;
}
?>