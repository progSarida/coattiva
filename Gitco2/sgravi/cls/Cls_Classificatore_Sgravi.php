<?php

require_once __DIR__ . "/Cls_Sgravio_Model.php";

/**
 * Classifica le partite_tributi ai fini sgravio (Art. 19 D.Lgs. 112/1999).
 *
 * Tutti i metodi as-of accettano $data_elab esplicita (formato Y-m-d):
 * la classificazione filtra ricorsi/crisi/udienze/pagamenti/notifiche/rate
 * con condizioni `<= $data_elab`. La logica I->D a cascata (Fase 5):
 *   - Lv1 (ricorso/crisi aperto a data_elab): ultima udienza > soglia mesi
 *     -- in mancanza di udienze, MAX(Start_Date) ricorsi/crisi aperti.
 *   - Lv2 (nessun ricorso/crisi aperto): MAX(ultimo pagamento, ultima
 *     notifica atto) > soglia mesi.
 *
 * Stato_Pignoramento "Annullato"/"Archiviato" è neutralizzato solo se
 * Data_Stato_Pignoramento e' <= data_elab (as-of). Se data NULL, il
 * pignoramento e' considerato attivo a data_elab.
 */
class Cls_Classificatore_Sgravi
{
    private $cc;
    private $mesi_inattivita_soglia;

    public function __construct(string $cc, int $mesi_inattivita_soglia = 12)
    {
        $this->cc = $cc;
        $this->mesi_inattivita_soglia = $mesi_inattivita_soglia;
    }

    public function getCC(): string { return $this->cc; }
    public function getMesiSoglia(): int { return $this->mesi_inattivita_soglia; }

    // -----------------------------------------------------------------
    // Orchestratore principale
    // -----------------------------------------------------------------

    /**
     * Classifica una partita_tributi.
     *
     * @param array $partitaRow riga dalla query principale di
     *   elaborazione_sgravi.php. Campi richiesti:
     *     - Partita_ID (int)
     *     - residual (float)            importo residuo dopo pagamenti
     *     - payments (float)            totale pagamenti
     *     - Scadenze_Rate_Pigno, Importi_Rate_Pigno (longtext, '*')
     *     - Scadenze_Rate_Atto, Importi_Rate_Atto (longtext, '*')
     *     - Stato_Pignoramento (string|null), Data_Stato_Pignoramento (date|null)
     * @param string $data_elab Y-m-d
     * @param float $importo_minimo soglia per classificazione P
     * @return array{tipo: string, info: string} tipo in {D,I,P}
     */
    public function classificaPartita(array $partitaRow, string $data_elab, float $importo_minimo): array
    {
        $partita_id = (int)$partitaRow['Partita_ID'];
        $residual = isset($partitaRow['residual']) ? (float)$partitaRow['residual'] : 0.0;
        $payments = isset($partitaRow['payments']) ? (float)$partitaRow['payments'] : 0.0;

        $tipo = null;
        $info = '';

        // 1) P -- residuo sotto soglia con pagamenti.
        if ($residual < $importo_minimo && $payments > 0) {
            return ['tipo' => 'P', 'info' => 'Pagato. '];
        }

        // 2) Rate (priorita' pignoramento se attivo a data_elab, fallback atto).
        $rate = $this->valutaRateizzazione($partitaRow, $data_elab);
        if ($rate['stato'] === 'scaduta') {
            $tipo = 'D';
            $info .= 'Rateizzazione scaduta. ';
        } elseif ($rate['stato'] === 'attiva') {
            $tipo = 'I';
            $info .= 'Rateizzazione in corso. ';
        }

        // 3) Ricorso / crisi a data_elab.
        $ricorsi_aperti = $this->ricorsiApertiADataElab($partita_id, $data_elab);
        $crisi_aperte   = $this->crisiAperteADataElab($partita_id, $data_elab);
        $had_ricorso_chiuso = $this->ricorsiChiusiEntroDataElab($partita_id, $data_elab);
        $had_crisi_chiusa   = $this->crisiChiuseEntroDataElab($partita_id, $data_elab);

        $motivo_I_ricorso_o_crisi = false;

        if (!empty($ricorsi_aperti)) {
            $tipo = 'I';
            $info .= 'Ricorso aperto. ';
            $motivo_I_ricorso_o_crisi = true;
        } elseif ($had_ricorso_chiuso) {
            if ($tipo === null) { $tipo = 'D'; }
            $info .= 'Ricorso chiuso. ';
        } elseif (!empty($crisi_aperte)) {
            $tipo = 'I';
            $info .= 'Crisi aperta. ';
            $motivo_I_ricorso_o_crisi = true;
        } elseif ($had_crisi_chiusa) {
            if ($tipo === null) { $tipo = 'D'; }
            $info .= 'Crisi chiusa. ';
        } else {
            if ($tipo === null) { $tipo = 'D'; }
        }

        // 4) Cascata I -> D (Fase 5)
        if ($tipo === 'I') {
            if ($motivo_I_ricorso_o_crisi) {
                if ($this->livello1_inattivitaRicorsoOCrisiAperto($partita_id, $data_elab)) {
                    $tipo = 'D';
                    $info .= '[Convertito I->D: inattivita Lv1] ';
                }
            } else {
                // I per rateizzazione in corso senza ricorso/crisi aperto
                if ($this->livello2_inattivitaSenzaRicorso($partita_id, $data_elab)) {
                    $tipo = 'D';
                    $info .= '[Convertito I->D: inattivita Lv2] ';
                }
            }
        }

        return ['tipo' => $tipo, 'info' => $info];
    }

    // -----------------------------------------------------------------
    // Cascata I -> D
    // -----------------------------------------------------------------

    public function livello1_inattivitaRicorsoOCrisiAperto(int $partita_id, string $data_elab): bool
    {
        $ultima_udienza = $this->ultimaUdienzaADataElab($partita_id, $data_elab);
        if ($ultima_udienza !== null) {
            return $this->diffMesi($ultima_udienza, $data_elab) >= $this->mesi_inattivita_soglia;
        }

        $max_start = $this->maxStartRicorsiCrisiApertiADataElab($partita_id, $data_elab);
        if ($max_start !== null) {
            return $this->diffMesi($max_start, $data_elab) >= $this->mesi_inattivita_soglia;
        }

        return false;
    }

    public function livello2_inattivitaSenzaRicorso(int $partita_id, string $data_elab): bool
    {
        $ultimo_pagamento = $this->ultimoPagamentoADataElab($partita_id, $data_elab);
        $ultima_notifica  = $this->ultimaNotificaAttoADataElab($partita_id, $data_elab);

        $candidati = array_filter(
            [$ultimo_pagamento, $ultima_notifica],
            function ($d) { return !empty($d); }
        );
        // Mancanza di evidenza != evidenza di inattivita'. Se non abbiamo
        // ne pagamenti ne notifiche entro data_elab, non convertiamo.
        if (empty($candidati)) {
            return false;
        }
        $max = max($candidati);
        return $this->diffMesi($max, $data_elab) >= $this->mesi_inattivita_soglia;
    }

    // -----------------------------------------------------------------
    // Query as-of data_elab
    // -----------------------------------------------------------------

    /** Ricorsi (appeal) aperti a data_elab: Start_Date <= data_elab AND (End_Date IS NULL OR End_Date > data_elab). */
    public function ricorsiApertiADataElab(int $partita_id, string $data_elab): array
    {
        $sql = sprintf(
            "SELECT ID, Start_Date, End_Date FROM appeal
              WHERE Partita_ID = %d
                AND Start_Date IS NOT NULL
                AND Start_Date <= '%s'
                AND (End_Date IS NULL OR End_Date > '%s')
              ORDER BY Start_Date DESC",
            $partita_id, $this->safeDate($data_elab), $this->safeDate($data_elab)
        );
        return Cls_Sgravio_Model::getRows($sql);
    }

    /** Ricorsi chiusi entro data_elab: End_Date NOT NULL AND End_Date <= data_elab. */
    public function ricorsiChiusiEntroDataElab(int $partita_id, string $data_elab): bool
    {
        $sql = sprintf(
            "SELECT 1 FROM appeal
              WHERE Partita_ID = %d
                AND End_Date IS NOT NULL
                AND End_Date <= '%s'
              LIMIT 1",
            $partita_id, $this->safeDate($data_elab)
        );
        $row = Cls_Sgravio_Model::getRow($sql);
        return !empty($row);
    }

    /** Crisi (crisis_tools) aperte a data_elab. */
    public function crisiAperteADataElab(int $partita_id, string $data_elab): array
    {
        $sql = sprintf(
            "SELECT ID, Start_Date, End_Date FROM crisis_tools
              WHERE Partita_ID = %d
                AND Start_Date IS NOT NULL
                AND Start_Date <= '%s'
                AND (End_Date IS NULL OR End_Date > '%s')
              ORDER BY Start_Date DESC",
            $partita_id, $this->safeDate($data_elab), $this->safeDate($data_elab)
        );
        return Cls_Sgravio_Model::getRows($sql);
    }

    /** Crisi chiuse entro data_elab. */
    public function crisiChiuseEntroDataElab(int $partita_id, string $data_elab): bool
    {
        $sql = sprintf(
            "SELECT 1 FROM crisis_tools
              WHERE Partita_ID = %d
                AND End_Date IS NOT NULL
                AND End_Date <= '%s'
              LIMIT 1",
            $partita_id, $this->safeDate($data_elab)
        );
        $row = Cls_Sgravio_Model::getRow($sql);
        return !empty($row);
    }

    /** Ultima udienza (qualunque crisi della partita) Date<=data_elab, ORDER BY Date DESC, Time DESC. */
    public function ultimaUdienzaADataElab(int $partita_id, string $data_elab): ?string
    {
        $sql = sprintf(
            "SELECT Date FROM crisis_tools_court_hearing
              WHERE Partita_ID = %d
                AND Date IS NOT NULL
                AND Date <= '%s'
              ORDER BY Date DESC, Time DESC
              LIMIT 1",
            $partita_id, $this->safeDate($data_elab)
        );
        $row = Cls_Sgravio_Model::getRow($sql);
        return $row['Date'] ?? null;
    }

    /** MAX(Start_Date) tra ricorsi e crisi aperti a data_elab. Usato come fallback Lv1 quando non ci sono udienze. */
    public function maxStartRicorsiCrisiApertiADataElab(int $partita_id, string $data_elab): ?string
    {
        $d = $this->safeDate($data_elab);
        $sql = sprintf(
            "SELECT MAX(Start_Date) AS max_start FROM (
                SELECT Start_Date FROM appeal
                  WHERE Partita_ID = %d
                    AND Start_Date IS NOT NULL
                    AND Start_Date <= '%s'
                    AND (End_Date IS NULL OR End_Date > '%s')
                UNION ALL
                SELECT Start_Date FROM crisis_tools
                  WHERE Partita_ID = %d
                    AND Start_Date IS NOT NULL
                    AND Start_Date <= '%s'
                    AND (End_Date IS NULL OR End_Date > '%s')
              ) AS u",
            $partita_id, $d, $d, $partita_id, $d, $d
        );
        $row = Cls_Sgravio_Model::getRow($sql);
        return $row['max_start'] ?? null;
    }

    /** Ultimo pagamento (data) <= data_elab. */
    public function ultimoPagamentoADataElab(int $partita_id, string $data_elab): ?string
    {
        $sql = sprintf(
            "SELECT MAX(Data_Pagamento) AS max_d FROM pagamento
              WHERE Partita_ID = %d
                AND Data_Pagamento IS NOT NULL
                AND Data_Pagamento <= '%s'",
            $partita_id, $this->safeDate($data_elab)
        );
        $row = Cls_Sgravio_Model::getRow($sql);
        return $row['max_d'] ?? null;
    }

    /** Ultima Data_Notifica atto <= data_elab (ignora atti senza Data_Notifica). */
    public function ultimaNotificaAttoADataElab(int $partita_id, string $data_elab): ?string
    {
        $sql = sprintf(
            "SELECT MAX(Data_Notifica) AS max_d FROM atto
              WHERE Partita_ID = %d
                AND Data_Notifica IS NOT NULL
                AND Data_Notifica <= '%s'
                AND archived IS NULL",
            $partita_id, $this->safeDate($data_elab)
        );
        $row = Cls_Sgravio_Model::getRow($sql);
        return $row['max_d'] ?? null;
    }

    // -----------------------------------------------------------------
    // Rate
    // -----------------------------------------------------------------

    /**
     * Valuta lo stato della rateizzazione a data_elab.
     * Priorita' al pignoramento se ATTIVO a data_elab (Stato non
     * "Annullato"/"Archiviato" oppure Data_Stato_Pignoramento > data_elab).
     *
     * @return array{stato: string, fonte: string, data_scadenza_mancata: ?string}
     *   stato in {attiva, scaduta, assente}; fonte in {pignoramento, atto, ''}
     */
    public function valutaRateizzazione(array $partitaRow, string $data_elab): array
    {
        $pigno_attivo = $this->pignoramentoAttivoADataElab(
            $partitaRow['Stato_Pignoramento'] ?? null,
            $partitaRow['Data_Stato_Pignoramento'] ?? null,
            $data_elab
        );

        $totale_pagamenti = isset($partitaRow['payments']) ? (float)$partitaRow['payments'] : 0.0;

        if ($pigno_attivo && !empty($partitaRow['Scadenze_Rate_Pigno'])) {
            $r = $this->scadenzeRateAttiveADataElab(
                $partitaRow['Scadenze_Rate_Pigno'],
                $partitaRow['Importi_Rate_Pigno'] ?? '',
                $totale_pagamenti,
                $data_elab
            );
            if ($r['stato'] !== 'assente') {
                return ['stato' => $r['stato'], 'fonte' => 'pignoramento', 'data_scadenza_mancata' => $r['data_scadenza_mancata']];
            }
        }

        if (!empty($partitaRow['Scadenze_Rate_Atto'])) {
            $r = $this->scadenzeRateAttiveADataElab(
                $partitaRow['Scadenze_Rate_Atto'],
                $partitaRow['Importi_Rate_Atto'] ?? '',
                $totale_pagamenti,
                $data_elab
            );
            if ($r['stato'] !== 'assente') {
                return ['stato' => $r['stato'], 'fonte' => 'atto', 'data_scadenza_mancata' => $r['data_scadenza_mancata']];
            }
        }

        return ['stato' => 'assente', 'fonte' => '', 'data_scadenza_mancata' => null];
    }

    /**
     * Stato del pignoramento as-of: "Annullato"/"Archiviato" rendono inattivo
     * SOLO se Data_Stato_Pignoramento <= data_elab. Se Data_Stato_Pignoramento
     * NULL ma stato annullato, conservativamente lo consideriamo NON attivo
     * (coerente con cls_elaboration::checkPignoramentoAnnullato).
     */
    public function pignoramentoAttivoADataElab(?string $stato, ?string $data_stato, string $data_elab): bool
    {
        if (empty($stato)) { return true; }
        $is_terminale = (strpos($stato, 'Annullato') !== false) || (strpos($stato, 'Archiviato') !== false);
        if (!$is_terminale) { return true; }
        if ($data_stato === null) { return false; }
        return $data_stato > $data_elab;
    }

    /**
     * Replica (con as-of $data_elab) la logica originale di
     * elaborazione_sgravi.php:782-826. L'array $scadenze e' separato da '*'
     * con formato d/m/Y. Le prime 1-2 posizioni possono essere meta-info,
     * la prima scadenza pagabile sta in posizione +2 rispetto all'indice di
     * Importi_Rate (offset preservato dalla logica originale).
     *
     * @return array{stato: string, data_scadenza_mancata: ?string}
     *   stato in {attiva, scaduta, assente}.
     */
    public function scadenzeRateAttiveADataElab(string $scadenze_str, string $importi_str, float $totale_pagamenti, string $data_elab): array
    {
        if (trim($scadenze_str) === '' || trim($importi_str) === '') {
            return ['stato' => 'assente', 'data_scadenza_mancata' => null];
        }

        $scadenze = explode('*', $scadenze_str);
        $importi  = explode('*', $importi_str);

        $somma_rate = 0.0;
        foreach ($importi as $key => $amount) {
            $somma_rate += (float)str_replace(',', '.', $amount);
            if ($somma_rate <= $totale_pagamenti) {
                continue;
            }

            // Replica esatta dell'offset originale (+2, +1, 0) sulla scadenza.
            if (!empty($scadenze[$key + 2])) {
                $raw = $scadenze[$key + 2];
            } elseif (!empty($scadenze[$key + 1])) {
                $raw = $scadenze[$key + 1];
            } elseif (!empty($scadenze[$key])) {
                $raw = $scadenze[$key];
            } else {
                continue;
            }

            $data_scad = $this->parseDataItaToDb($raw);
            if ($data_scad === null) {
                continue;
            }

            $stato = ($data_scad < $data_elab) ? 'scaduta' : 'attiva';
            return ['stato' => $stato, 'data_scadenza_mancata' => $data_scad];
        }

        // Nessuna rata che faccia sforare i pagamenti => piano coperto.
        return ['stato' => 'assente', 'data_scadenza_mancata' => null];
    }

    // -----------------------------------------------------------------
    // Utility
    // -----------------------------------------------------------------

    /** Differenza in mesi via DateInterval (months + years*12). */
    public function diffMesi(string $data_da, string $data_a): int
    {
        $da = new DateTime($data_da);
        $a  = new DateTime($data_a);
        $i  = $da->diff($a);
        $months = $i->m + $i->y * 12;
        return $i->invert ? -$months : $months;
    }

    /** Converte 'd/m/Y' -> 'Y-m-d'. Restituisce null se non parsabile. */
    public function parseDataItaToDb(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') { return null; }
        $parts = explode('/', $raw);
        if (count($parts) !== 3) { return null; }
        if (!checkdate((int)$parts[1], (int)$parts[0], (int)$parts[2])) { return null; }
        return sprintf('%04d-%02d-%02d', (int)$parts[2], (int)$parts[1], (int)$parts[0]);
    }

    /** Validazione minima Y-m-d per evitare SQL injection via interpolazione. */
    private function safeDate(string $d): string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            throw new InvalidArgumentException('data_elab non valida: ' . $d);
        }
        return $d;
    }
}
