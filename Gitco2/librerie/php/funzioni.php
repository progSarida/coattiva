<?php

include_once LIBRERIE . "/date_function.php";
include_once LIBRERIE . "/aiuto.php";
include_once LIBRERIE . "/db.php";
include_once LIBRERIE . "/file_function.php";
include_once LIBRERIE . "/pdf_function.php";
include_once LIBRERIE . "/controlli.php";
include_once LIBRERIE . "/scelta_anni.php";
include_once LIBRERIE . "/cifratura.php";

include_once LIBRERIE . "/connessione_db.php";
include_once CLASSI . "/db_class.php";

if(!headers_sent()){
    header("Content-Type: text/html; charset=ISO-8859-1");
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
    header('Pragma: no-cache'); // HTTP 1.0.
    header('Expires: 0'); // Proxies.

    header('X-Robots-Tag: noindex, nofollow, noarchive');
}


?>