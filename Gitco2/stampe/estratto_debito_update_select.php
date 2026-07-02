<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";

header('Content-Type: application/json');

$cls_help    = new cls_help();
$cls_db = new cls_db();

$c = $cls_help->getVar('c');

if (empty($c)) {
    echo json_encode(['error' => 1, 'msg' => 'Ente non valido']);
    exit;
}

// SOGGETTI
$querySoggetti = "SELECT 
                        U.ID, 
                        IF(U.Genere = 'D', U.Ditta, CONCAT(U.Cognome, ' ', U.Nome)) AS user 
                    FROM partita_tributi AS PT 
                    LEFT JOIN ruolo AS R ON PT.Ruolo_ID = R.id 
                    LEFT JOIN utente AS U ON PT.Utente_ID = U.ID
                    WHERE PT.CC = '" . $c . "'
                    GROUP BY U.ID
                    ORDER BY U.Ditta, U.Cognome, U.Nome ASC";
$resultSoggetti = $cls_db->getResults($cls_db->ExecuteQuery($querySoggetti));
$soggetti = [];
foreach ($resultSoggetti as $value) {
    $denominazione_breve = (mb_strlen($value["user"]) > 60) ? mb_substr($value["user"], 0, 60) . "..." : $value["user"];
    $soggetti[] = ['id' => $value["ID"], 'label' => $denominazione_breve];
}

// ANNI
$queryAnni = "SELECT ID, Anno FROM anni_gestiti WHERE CC_Anno = '" . $c . "'";
$resultAnni = $cls_db->getResults($cls_db->ExecuteQuery($queryAnni));
$anni = [];
foreach ($resultAnni as $value) {
    $anni[] = ['id' => $value["Anno"], 'label' => $value["Anno"]];
}

// PARTITE
$queryPartite = "SELECT Comune_ID FROM partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resultPartite = $cls_db->getResults($cls_db->SelectQuery($queryPartite));
$partite = [];
foreach ($resultPartite as $value) {
    $partite[] = ['id' => $value['Comune_ID'], 'label' => $value['Comune_ID']];
}

echo json_encode([
    'error' => 0,
    'soggetti' => $soggetti,
    'anni' => $anni,
    'partite' => $partite,
]);