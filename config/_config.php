<?php

ini_set('display_errors',"1");
date_default_timezone_set("Europe/Rome");

header('X-Robots-Tag: noindex, nofollow, noarchive');
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

require "_db.php";
require "_server.php";
require "_paths.php";
require "_sessions.php";