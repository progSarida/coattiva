<?php
/**
 * AJAX endpoint cancellazione massiva di un'elaborazione sgravi.
 *
 * Azioni (parametro `action`):
 *   - "preview": ritorna i dati per la modal di conferma. Sola lettura.
 *   - "delete":  esegue la cancellazione + ricalcolo retroattivo. Modifica DB.
 *
 * Risposta JSON:
 *   { esito: 'OK'|'KO', message: string, data?: object }
 *
 * Whitelist allineata a comunicazioni.php: mirkop, riccardo, superadmin
 * con $_SESSION['aut_tipo']=='1'.
 */

if (!session_id()) session_start();

require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once ROOT . "/sgravi/cls/Cls_Cancellazione_Sgravi.php";

header('Content-Type: application/json; charset=utf-8');

// Difesa: il flusso di cancellazione legge via Cls_Sgravio_Model (trait Db), che su
// errore DB invoca la funzione globale ErrorAlert() definita solo in cli_function.php
// (non caricato in questo contesto AJAX). Fallback JSON: l'errore torna come
// {esito:'KO'} allo swal del client invece di un fatal "Call to undefined function".
if (!function_exists('ErrorAlert')) {
    function ErrorAlert($msgType, $msgText) {
        echo json_encode(['esito' => 'KO', 'message' => $msgText]);
        die;
    }
}

$cls_db = new cls_db();
$cls_help = new cls_help();

// --- Sicurezza ---
$utentiAutorizzati = array("mirkop", "riccardo", "superadmin");
$usernameAttuale = isset($_SESSION['username']) ? strtolower($_SESSION['username']) : '';

if (!isset($_SESSION['aut_tipo']) || $_SESSION['aut_tipo'] != "1" || !in_array($usernameAttuale, $utentiAutorizzati, true)) {
    echo json_encode(['esito' => 'KO', 'message' => 'UTENTE NON AUTORIZZATO']);
    exit;
}

// --- Input ---
$proc_id = (int)$cls_help->getVar('proc_id');
$action  = $cls_help->getVar('action');
if ($action === null || $action === '') {
    $action = 'preview';
}
$cc = trim((string)$cls_help->getVar('cc'));

if ($proc_id <= 0) {
    echo json_encode(['esito' => 'KO', 'message' => 'ID elaborazione mancante.']);
    exit;
}
if ($cc === '') {
    echo json_encode(['esito' => 'KO', 'message' => 'Parametro cc (ente) mancante nella richiesta.']);
    exit;
}

try {
    $svc = new Cls_Cancellazione_Sgravi($cc);

    if ($action === 'preview') {
        $info = $svc->anteprima($proc_id);
        if ($info['anno'] === null) {
            echo json_encode([
                'esito' => 'KO',
                'message' => "Procedura $proc_id non trovata o non e' un'elaborazione discarichi del CC corrente.",
            ]);
            exit;
        }
        echo json_encode([
            'esito' => 'OK',
            'message' => 'Anteprima generata.',
            'data' => $info,
        ]);
        exit;
    }

    if ($action === 'delete') {
        $info = $svc->anteprima($proc_id);
        if ($info['anno'] === null) {
            echo json_encode([
                'esito' => 'KO',
                'message' => "Procedura $proc_id non trovata o non e' un'elaborazione discarichi del CC corrente.",
            ]);
            exit;
        }
        if (!$info['is_anno_piu_recente']) {
            echo json_encode([
                'esito' => 'KO',
                'message' => "Cancellazione bloccata: l'anno {$info['anno']} non e' il piu' recente per questo ente. Cancellare prima le elaborazioni successive.",
            ]);
            exit;
        }

        $user_id = isset($_SESSION['aut_progr']) ? (int)$_SESSION['aut_progr'] : 0;
        $r = $svc->esegui($proc_id, $user_id);
        if (!$r['ok']) {
            echo json_encode([
                'esito' => 'KO',
                'message' => $r['error'] ?? 'Errore di cancellazione',
            ]);
            exit;
        }

        $msg = sprintf(
            "Elaborazione anno %d cancellata. %d partite resettate. Ricalcolo retroattivo anno precedente: %d posizioni I rivalutate, %d convertite a D.",
            $info['anno'], $r['partite_cancellate'], $r['partite_ricalcolate'], $r['convertite_I_to_D']
        );
        echo json_encode([
            'esito' => 'OK',
            'message' => $msg,
            'data' => $r,
        ]);
        exit;
    }

    echo json_encode(['esito' => 'KO', 'message' => "Azione non riconosciuta: $action"]);
    exit;

} catch (Throwable $e) {
    echo json_encode(['esito' => 'KO', 'message' => 'Errore di sistema: ' . $e->getMessage()]);
    exit;
}
