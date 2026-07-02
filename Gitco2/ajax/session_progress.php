<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";
//var_dump($_SESSION['progress']);
//session_start();
echo json_encode([$_SESSION['progress']]);