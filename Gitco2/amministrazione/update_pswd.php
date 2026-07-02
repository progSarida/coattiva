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
$pswd = mysqli_real_escape_string($cls_db->conn, $cls_help->getVar('pswd'));

$error = 0;
$msg = "";

if (empty($username)) {
  $error = 1;
  $msg = "Il campo Email è obbligatorio";
  header("Location:reset_pswd.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  return;
}

if (empty($pswd)) {
  $error = 1;
  $msg = "Password è obbligatoria";
  header("Location:reset_pswd.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  return;
}

$password = md5($pswd);

$query_up_user = " UPDATE autenticazione SET `Password` = '" . $password . "', Data = '".date('Y-m-d')."' WHERE User =  '" . $username . "'";


if (mysqli_query($cls_db->conn, $query_up_user)) {
  $error = 0;
  $msg = "Reset password riuscito";
  header("Location:reset_pswd.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  return;
} else {
  $error = 1;
  $msg = "Reset password non riuscito";
  header("Location:reset_pswd.php?c={$c}&a={$a}&error={$error}&msg={$msg}");
  return;
}
