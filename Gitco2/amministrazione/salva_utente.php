<?php
if (!session_id()) session_start();

if ($_SESSION['username'] == NULL) {
  header("Location:/gitco2/autenticazione/accesso_negato.php");
  die;
}

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database


include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";


$cls_db = new cls_db();
$cls_help = new cls_help();


if (!session_id()) session_start();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$username = mysqli_real_escape_string($cls_db->conn, $cls_help->getVar('username'));
$email = mysqli_real_escape_string($cls_db->conn, $cls_help->getVar('email_address'));
$pswd = mysqli_real_escape_string($cls_db->conn, $cls_help->getVar('pswd'));
$cod_cat = $cls_help->getVar('ente');
$auth = $cls_help->getVar('auth');

$error = 0;
$msg = "";

$pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";;
if (!empty($username)) {
  $query_user = "SELECT * FROM autenticazione WHERE User ='" . $username . "'";
  $a_users = $cls_db->getResults($cls_db->ExecuteQuery($query_user));

  if (count($a_users) > 0) {
    $error = 1;
    $msg = "Utente già presente";
    header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
    die;
  }
} else {
  $error = 1;
  $msg = "Inserisci un username";
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
}

if (!empty($email)) {
  $query_user = "SELECT * FROM autenticazione WHERE Mail ='" . $email . "'";
  $a_users = $cls_db->getResults($cls_db->ExecuteQuery($query_user));
  $error = 1;
  if (count($a_users) > 0) {
    $msg = "Mail già presente";
    header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
    die;
  }
}

if (!empty($email) && preg_match($pattern, trim($email)) == false) {
  $error = 1;
  $msg = "email non valida";
  var_dump($email);
  var_dump($msg);
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
}


if (empty($pswd)) {
  $error = 1;
  $msg = "Password è obbligatoria";
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
}

$password = md5($pswd);

if (empty($cod_cat)) {
  $error = 1;
  $msg = "Seleziona un ente";
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
}

if (empty($auth)) {
  $error = 1;
  $msg = "seleziona un Tipo";
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
}

if ($auth == 1 && $cod_cat != "****") {
  $error = 1;
  $msg = "ERRORE! Per questo Tipo va associato solo l\'ente \'Tutti gli enti\'";
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
}

if ($auth > 1 && $cod_cat == "****") {
  $error = 1;
  $msg = "ERRORE! Per questo Tipo non va associato l\'ente \'Tutti gli enti\'";
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
}


$a_dbParams_user = array(
  'table' => 'autenticazione',
  'fields' => array(
    array('name' => 'User',       'type' => 'string', 'value' => $username),
    array('name' => 'Password',   'type' => 'string', 'value' => $password),
    array('name' => 'Mail',        'type' => 'string', 'value' => $email),
    array('name' => 'CC_User',    'type' => 'string', 'value' => $cod_cat),
    array('name' => 'Tipo',       'type' => 'int', 'value' => $auth),
    array('name' => 'Data',       'type' => 'date', 'value' => date('Y-m-d')),
    array('name' => 'UserMoto',   'type' => 'string', 'value' => NULL),
    array('name' => 'PassMoto',   'type' => 'string', 'value' => NULL),
  )
);

$last_user_id = $cls_db->DbInsert($a_dbParams_user);

if (!$last_user_id) {
  $error = 1;
  $msg = "Errore inserimento dati non riuscito";
  header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  die;
} else {
  $error = 0;
  $msg = "Inserimento dati riuscito";
}

header("Location:crea_utente.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
