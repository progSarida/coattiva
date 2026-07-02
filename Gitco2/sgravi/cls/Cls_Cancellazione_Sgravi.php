<?php

require_once __DIR__ . "/Cls_Sgravio_Model.php";
require_once __DIR__ . "/Cls_Classificatore_Sgravi.php";
if (!class_exists('cls_db', false) && defined('CLS')) {
    require_once CLS . "/cls_db.php";
}

/**
 * Cancellazione massiva di un'elaborazione sgravi (Procedure_Type_Id=2)
 * con ricalcolo retroattivo dell'anno precedente.
 *
 * Eccezione alla regola "trait Db, no cls_db instance": questa classe usa
 * cls_db internamente perche' richiede una connessione MySQL persistente
 * per la transazione atomica (DELETE + UPDATE + ricalcolo). Il trait Db
 * apre una nuova connection ad ogni query, rompendo BEGIN/ROLLBACK.
 * Il classificatore esterno continua a usare trait Db (solo letture).
 *
 * Vincoli applicativi:
 *   - cancellabile solo l'anno piu' recente per il CC
 *     (rimuovere anni intermedi rompe la sequenzialita')
 *
 * Sequenza esegui() (in transazione unica, rollback su qualunque errore):
 *   1. DELETE sgravi_documenti collegati ai sgravio della procedura
 *   2. DELETE sgravio collegati alla procedura
 *   3. UPDATE partita_tributi: reset campi sgravio per le partite coinvolte
 *   4. DELETE procedures (record dell'elaborazione)
 *   5. Ricalcolo retroattivo: per le posizioni I dell'anno precedente,
 *      rieseguire la classificazione usando la Procedure_Date originale
 *      di quella elaborazione come data_elab. Aggiornare i record sgravio
 *      esistenti se la classificazione cambia (I->D). Non toccare D e P.
 */
class Cls_Cancellazione_Sgravi
{
    private $cc;
    /** @var cls_db|null */
    private $db;

    public function __construct(string $cc, ?cls_db $db = null)
    {
        $this->cc = $cc;
        $this->db = $db;  // se null, anteprima usa Cls_Sgravio_Model (trait Db)
    }

    public function getCC(): string { return $this->cc; }

    /**
     * Anteprima leggera senza modifiche al DB.
     *
     * @return array{
     *   procedure_id: int,
     *   anno: ?int,
     *   procedure_date: ?string,
     *   partite_totali: int,
     *   anno_precedente: ?int,
     *   procedure_id_precedente: ?int,
     *   procedure_date_precedente: ?string,
     *   partite_I_precedenti: int,
     *   is_anno_piu_recente: bool
     * }
     */
    public function anteprima(int $procedure_id): array
    {
        $proc = Cls_Sgravio_Model::getRow(sprintf(
            "SELECT Id, CC, Anno_Procedura, Procedure_Date, Procedure_Type_Id
               FROM procedures
              WHERE Id = %d AND CC = '%s' AND Procedure_Type_Id = 2
              LIMIT 1",
            $procedure_id, $this->safeCc()
        ));

        $base = array(
            'procedure_id' => $procedure_id,
            'anno' => null,
            'procedure_date' => null,
            'partite_totali' => 0,
            'anno_precedente' => null,
            'procedure_id_precedente' => null,
            'procedure_date_precedente' => null,
            'partite_I_precedenti' => 0,
            'is_anno_piu_recente' => false,
        );

        if (empty($proc)) {
            return $base;
        }

        $anno = $proc['Anno_Procedura'] !== null ? (int)$proc['Anno_Procedura'] : null;
        $base['anno'] = $anno;
        $base['procedure_date'] = $proc['Procedure_Date'];

        $cnt = Cls_Sgravio_Model::getRow(sprintf(
            "SELECT COUNT(*) AS n FROM sgravio WHERE Procedure_Id = %d AND CC = '%s' AND Tipo = 1",
            $procedure_id, $this->safeCc()
        ));
        $base['partite_totali'] = (int)($cnt['n'] ?? 0);

        // E' l'anno piu' recente per quel CC?
        $max = Cls_Sgravio_Model::getRow(sprintf(
            "SELECT MAX(Anno_Procedura) AS max_anno FROM procedures
              WHERE CC = '%s' AND Procedure_Type_Id = 2 AND Anno_Procedura IS NOT NULL",
            $this->safeCc()
        ));
        $max_anno = ($max && $max['max_anno'] !== null) ? (int)$max['max_anno'] : null;
        $base['is_anno_piu_recente'] = ($anno !== null && $max_anno !== null && $anno === $max_anno);

        // Anno precedente (su DB) e relativo procedure_id (per ricalcolo retroattivo).
        if ($anno !== null) {
            $prev = Cls_Sgravio_Model::getRow(sprintf(
                "SELECT Id, Anno_Procedura, Procedure_Date FROM procedures
                  WHERE CC = '%s' AND Procedure_Type_Id = 2 AND Anno_Procedura < %d
                  ORDER BY Anno_Procedura DESC LIMIT 1",
                $this->safeCc(), $anno
            ));
            if (!empty($prev)) {
                $base['anno_precedente'] = (int)$prev['Anno_Procedura'];
                $base['procedure_id_precedente'] = (int)$prev['Id'];
                $base['procedure_date_precedente'] = $prev['Procedure_Date'];

                $cnt_i = Cls_Sgravio_Model::getRow(sprintf(
                    "SELECT COUNT(*) AS n FROM sgravio
                      WHERE Procedure_Id = %d AND CC = '%s' AND Tipo = 1 AND Tipo_Sgravio = 'I'",
                    (int)$prev['Id'], $this->safeCc()
                ));
                $base['partite_I_precedenti'] = (int)($cnt_i['n'] ?? 0);
            }
        }

        return $base;
    }

    /**
     * Cancellazione transazionale + ricalcolo retroattivo.
     *
     * @return array{
     *   ok: bool,
     *   error: ?string,
     *   partite_cancellate: int,
     *   partite_ricalcolate: int,
     *   convertite_I_to_D: int
     * }
     */
    public function esegui(int $procedure_id, int $user_id, ?Cls_Classificatore_Sgravi $classificatore = null): array
    {
        $result = array(
            'ok' => false,
            'error' => null,
            'partite_cancellate' => 0,
            'partite_ricalcolate' => 0,
            'convertite_I_to_D' => 0,
        );

        // cls_db locale per garantire connessione persistente nella transazione.
        if ($this->db === null) {
            $this->db = new cls_db();
        }

        $info = $this->anteprima($procedure_id);
        if ($info['anno'] === null) {
            $result['error'] = "Procedura non trovata o non e' un'elaborazione sgravi del CC corrente.";
            return $result;
        }
        if (!$info['is_anno_piu_recente']) {
            $result['error'] = "Cancellabile solo l'anno piu' recente. Anno richiesto: {$info['anno']}, anno piu' recente per il CC: superiore.";
            return $result;
        }

        // Lookup partite coinvolte (per il reset partita_tributi e per file fisici).
        $sgravi = $this->db->getResults($this->db->ExecuteQuery(sprintf(
            "SELECT ID, Partita_ID FROM sgravio WHERE Procedure_Id = %d AND CC = '%s' AND Tipo = 1",
            $procedure_id, $this->safeCc()
        )));

        $sgravio_ids = array();
        $partita_ids = array();
        foreach ($sgravi as $s) {
            $sgravio_ids[] = (int)$s['ID'];
            $partita_ids[] = (int)$s['Partita_ID'];
        }

        // Disabilita autocommit sulla SOLA connessione cls_db locale.
        $this->db->conn->autocommit(false);
        try {
            // 1. DELETE sgravi_documenti.
            if (!empty($sgravio_ids)) {
                $ids_csv = implode(',', $sgravio_ids);
                $this->db->ExecuteQuery(
                    "DELETE FROM sgravi_documenti WHERE Sgravio_ID IN ($ids_csv)"
                );
            }

            // 2. DELETE sgravio.
            $this->db->ExecuteQuery(sprintf(
                "DELETE FROM sgravio WHERE Procedure_Id = %d AND CC = '%s' AND Tipo = 1",
                $procedure_id, $this->safeCc()
            ));

            // 3. Reset partita_tributi.
            if (!empty($partita_ids)) {
                $pid_csv = implode(',', $partita_ids);
                $this->db->ExecuteQuery(
                    "UPDATE partita_tributi SET
                        Flag_Sgravio = NULL,
                        Tipo_Sgravio = NULL,
                        Sgravio_Activation_Date = NULL,
                        Sgravio_Save_Activation_Date = NULL,
                        Note_Blocco_Sgravio = NULL,
                        Printed_Sgravio = 'no',
                        print_sgravio_date = NULL
                     WHERE ID IN ($pid_csv)"
                );
            }

            // 4. DELETE procedures.
            $this->db->ExecuteQuery(sprintf(
                "DELETE FROM procedures WHERE Id = %d AND CC = '%s' AND Procedure_Type_Id = 2",
                $procedure_id, $this->safeCc()
            ));

            // 5. Ricalcolo retroattivo dell'anno precedente (UPDATE su sgravio
            //    via $this->db, sulla stessa connessione transazionale).
            $ricalc = $this->ricalcoloRetroattivoAnnoPrecedente($info, $classificatore);
            $result['partite_ricalcolate'] = $ricalc['ricalcolate'];
            $result['convertite_I_to_D']  = $ricalc['convertite'];

            $this->db->conn->commit();
            $this->db->conn->autocommit(true);

            // 6. Cleanup file fisici (fuori transazione: filesystem, irreversibile).
            $this->rimuoviDirectoryProcedure($procedure_id);

            $result['ok'] = true;
            $result['partite_cancellate'] = count($partita_ids);
            return $result;

        } catch (\Throwable $e) {
            $this->db->conn->rollback();
            $this->db->conn->autocommit(true);
            $result['error'] = "Errore durante la cancellazione: " . $e->getMessage();
            return $result;
        }
    }

    /**
     * Reclassifica le posizioni I dell'anno precedente con la Procedure_Date
     * originale come data_elab. Aggiorna in-place i record sgravio collegati
     * a quella procedura. Non tocca D e P.
     *
     * @return array{ricalcolate: int, convertite: int}
     * @throws \RuntimeException se manca classificatore o parametri.
     */
    public function ricalcoloRetroattivoAnnoPrecedente(array $info, ?Cls_Classificatore_Sgravi $classificatore = null): array
    {
        if (empty($info['procedure_id_precedente']) || empty($info['procedure_date_precedente'])) {
            return array('ricalcolate' => 0, 'convertite' => 0);
        }

        if ($classificatore === null) {
            // Soglia di default: leggi da enti_gestiti (campo spostato lì,
            // migrazione 2026-05-11; in parametri_generali la colonna non esiste più).
            $row = Cls_Sgravio_Model::getRow(sprintf(
                "SELECT Mesi_Inattivita_Sgravio FROM enti_gestiti WHERE CC = '%s' LIMIT 1",
                $this->safeCc()
            ));
            $soglia = (!empty($row) && $row['Mesi_Inattivita_Sgravio'] !== null)
                ? (int)$row['Mesi_Inattivita_Sgravio'] : 12;
            $classificatore = new Cls_Classificatore_Sgravi($this->cc, $soglia);
        }

        $procedure_id_prev = (int)$info['procedure_id_precedente'];
        $data_elab_prev = $info['procedure_date_precedente'];

        // Lookup posizioni I dell'anno precedente. Usa $this->db se gia'
        // disponibile (per stare nella transazione), fallback su trait Db.
        $sql = sprintf(
            "SELECT S.ID AS sgravio_id, S.Partita_ID,
                    PG.Stato_Pignoramento, PG.Data_Stato_Pignoramento,
                    PG.Scadenze_Rate AS Scadenze_Rate_Pigno, PG.Importi_Rate AS Importi_Rate_Pigno,
                    A.Scadenze_Rate AS Scadenze_Rate_Atto, A.Importi_Rate AS Importi_Rate_Atto,
                    COALESCE(SUM(PA.Importo), 0) AS payments
               FROM sgravio S
               LEFT JOIN atto A ON A.ID = (
                  SELECT A2.ID FROM atto A2
                   WHERE A2.Partita_ID = S.Partita_ID AND A2.archived IS NULL
                     AND (A2.data_start_archived_act > '%s' OR A2.data_start_archived_act IS NULL)
                   ORDER BY A2.Data_Elaborazione DESC LIMIT 1)
               LEFT JOIN pignoramento_generale PG ON PG.ID = (
                  SELECT PG1.ID FROM pignoramento_generale PG1
                   WHERE PG1.Atto_ID = A.ID AND PG1.Data_Elaborazione >= A.Data_Elaborazione
                   ORDER BY PG1.Data_Elaborazione DESC LIMIT 1)
               LEFT JOIN pagamento PA ON PA.Partita_ID = S.Partita_ID AND PA.DocumentTypeId IS NOT NULL
              WHERE S.Procedure_Id = %d AND S.CC = '%s' AND S.Tipo = 1 AND S.Tipo_Sgravio = 'I'
              GROUP BY S.ID",
            $this->safeDate($data_elab_prev), $procedure_id_prev, $this->safeCc()
        );
        if ($this->db !== null) {
            $sgravi_I = $this->db->getResults($this->db->ExecuteQuery($sql));
        } else {
            $sgravi_I = Cls_Sgravio_Model::getRows($sql);
        }

        $convertite = 0;
        // Importo_Minimo: usa quello dell'anno corrente (parametri_annuali).
        // Se non disponibile, soglia conservativa 0 (non scattano P).
        $importo_minimo = $this->loadImportoMinimo();

        foreach ($sgravi_I as $row) {
            $partita_row = array(
                'Partita_ID' => (int)$row['Partita_ID'],
                'residual'   => 0.0,  // non rilevante: la cascata I->D non lo usa
                'payments'   => (float)$row['payments'],
                'Scadenze_Rate_Pigno' => $row['Scadenze_Rate_Pigno'] ?? '',
                'Importi_Rate_Pigno'  => $row['Importi_Rate_Pigno'] ?? '',
                'Scadenze_Rate_Atto'  => $row['Scadenze_Rate_Atto'] ?? '',
                'Importi_Rate_Atto'   => $row['Importi_Rate_Atto'] ?? '',
                'Stato_Pignoramento'      => $row['Stato_Pignoramento'] ?? null,
                'Data_Stato_Pignoramento' => $row['Data_Stato_Pignoramento'] ?? null,
            );

            $cls = $classificatore->classificaPartita($partita_row, $data_elab_prev, $importo_minimo);
            $nuovo_tipo = $cls['tipo'];
            $nuovo_info = $cls['info'];

            // Se diventa D: UPDATE sgravio + reset partita_tributi.Tipo_Sgravio
            // Se resta I: nessuna modifica (UPDATE non necessario).
            // Se diventa P: caso anomalo (la classificazione precedente era I,
            //   ora P sarebbe inverso: residual << soglia). Lo trattiamo come D
            //   per non lasciare lo stato in pignoramento ricalcolato pendente,
            //   ma di fatto con residual=0.0 nel partita_row il caso P non scatta
            //   nel classificatore (il check P richiede payments>0 E residual<min).
            if ($nuovo_tipo !== 'I') {
                $sql_upd_sg = sprintf(
                    "UPDATE sgravio SET Tipo_Sgravio = '%s', Info = '%s' WHERE ID = %d",
                    addslashes($nuovo_tipo), addslashes($nuovo_info), (int)$row['sgravio_id']
                );
                $sql_upd_pt = sprintf(
                    "UPDATE partita_tributi SET Tipo_Sgravio = '%s' WHERE ID = %d",
                    addslashes($nuovo_tipo), (int)$row['Partita_ID']
                );
                if ($this->db !== null) {
                    $this->db->ExecuteQuery($sql_upd_sg);
                    $this->db->ExecuteQuery($sql_upd_pt);
                } else {
                    Cls_Sgravio_Model::ExecuteQuery($sql_upd_sg);
                    Cls_Sgravio_Model::ExecuteQuery($sql_upd_pt);
                }
                $convertite++;
            }
        }

        return array('ricalcolate' => count($sgravi_I), 'convertite' => $convertite);
    }

    private function loadImportoMinimo(): float
    {
        $row = Cls_Sgravio_Model::getRow(sprintf(
            "SELECT Importo_Minimo FROM parametri_annuali
              WHERE CC = '%s' AND Anno = %d
              ORDER BY Anno DESC LIMIT 1",
            $this->safeCc(), (int)date('Y')
        ));
        if ($row && $row['Importo_Minimo'] !== null) {
            return (float)$row['Importo_Minimo'];
        }
        return 0.0;
    }

    private function rimuoviDirectoryProcedure(int $procedure_id): void
    {
        if (!defined('PROCEDURE')) { return; }
        $path = PROCEDURE . "/" . $procedure_id;
        if (!is_dir($path)) { return; }
        $files = glob($path . '/*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) { @unlink($file); }
            }
        }
        @rmdir($path);
    }

    private function safeCc(): string
    {
        // CC e' validato come stringa alfanumerica fino a 5 char nel codebase.
        return addslashes($this->cc);
    }

    private function safeDate(string $d): string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $d)) {
            throw new \InvalidArgumentException('Data non valida: ' . $d);
        }
        return $d;
    }
}
