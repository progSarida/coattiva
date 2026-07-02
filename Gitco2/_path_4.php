<?php
$PATH_SISTEMA = str_replace("Program Files (x86)", "progra~2", $_SERVER['DOCUMENT_ROOT']);

$PathImmaginiNotifiche = "/archivio/Notifiche/";
$PathCompletoImmaginiNotifiche = $PATH_SISTEMA . $PathImmaginiNotifiche;

ini_set('display_errors',"1");
date_default_timezone_set("Europe/Rome");

if (!session_id()) session_start();
if(isset($_SESSION['username'])) {
    if($_SESSION['username']==null){
        header("Location:/accessDenied.php");
        die;
    }
}




if (!defined('DEBUG'))
    define("DEBUG" ,true);
if (!defined('PROD'))
    define("PROD" ,false);

if(PROD===false){
    if (!defined('LOCAL'))
        define("LOCAL" ,"/coattiva");
}
else{
    if (!defined('LOCAL'))
        define("LOCAL" ,"");
}

if (!defined('SITE'))
    define("SITE" , "/Gitco2");
if (!defined('SUPER_ROOT'))
    define("SUPER_ROOT", $_SERVER['DOCUMENT_ROOT'].LOCAL);
if (!defined('ROOT'))
    define("ROOT", __DIR__);

if (!defined('SUPER_WEB_ROOT'))
    define("SUPER_WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL);
if (!defined('WEB_ROOT'))
    define("WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL.SITE);

//PERCORSI FISICI
if (!defined('ANAGRAFE'))
    define("ANAGRAFE", $_SERVER['DOCUMENT_ROOT']."/Gitco2/anagrafe");
if (!defined('STRAGIUDIZIALE'))
    define("STRAGIUDIZIALE" ,SUPER_ROOT."/archivio/stragiudiziale");        

if (!defined('INC'))
    define("INC", ROOT. "/inc");

if (!defined('CLS'))
    define("CLS" ,ROOT. "/cls");
if (!defined('CLASSI'))
    define("CLASSI" ,ROOT. "/classi");
if (!defined('SUPER_CLS'))
    define("SUPER_CLS" ,SUPER_ROOT. "/cls");

if (!defined('STEMMI'))
    define("STEMMI" ,ROOT. "/stemmi");

if (!defined('PHPEXCEL'))
    define("PHPEXCEL" ,SUPER_CLS."/Excel/Classes/PHPExcel.php");
if (!defined('IOFACTORY'))
    define("IOFACTORY" ,SUPER_CLS."/Excel/Classes/PHPExcel/IOFactory.php");
if (!defined('PHPMAILER'))
    define("PHPMAILER" ,SUPER_CLS."/PHPMailer/PHPMailerAutoload.php");
if (!defined('TCPDF'))
    define("TCPDF" ,SUPER_CLS."/tcpdf");
if (!defined('TCPDF'))
    define("DOMPDF" ,SUPER_CLS."/dompdf");
if (!defined('FPDI'))
    define("FPDI" ,SUPER_CLS."/fpdi");

if (!defined('IMMAGINI_NOTIFICHE'))
    define("IMMAGINI_NOTIFICHE" ,SUPER_ROOT."/archivio/Notifiche");
if (!defined('IMMAGINI_NOTIFICHE_WEB'))
    define("IMMAGINI_NOTIFICHE_WEB" ,SUPER_WEB_ROOT."/archivio/Notifiche");
if (!defined('ARCHIVIO'))
    define("ARCHIVIO" ,SUPER_ROOT."/archivio");
if (!defined('PAGAMENTI_ESTERI'))
    define("PAGAMENTI_ESTERI" ,SUPER_ROOT."/archivio/Targhe_Estere/Pagamenti");
if (!defined('ATTI'))
    define("ATTI" ,SUPER_ROOT."/archivio/atti");
if (!defined('DUENOVANTA'))
    define("DUENOVANTA", SUPER_ROOT."/archivio/duenovanta");
if (!defined('FIRME'))
    define("FIRME" ,SUPER_ROOT."/archivio/firme");
if (!defined('MODELLI'))
    define("MODELLI" ,SUPER_ROOT."/archivio/modelli");
if (!defined('XLSSGRAVI'))
    define("XLSSGRAVI" ,SUPER_ROOT."/archivio/xls_sgravi");
if (!defined('PDFSGRAVI'))
    define("PDFSGRAVI" ,SUPER_ROOT."/archivio/pdf_sgravi");
if (!defined('PROCEDURE'))
    define("PROCEDURE" ,SUPER_ROOT."/archivio/procedure/");
if (!defined('SGRAVI'))
    define("SGRAVI" ,SUPER_ROOT."/archivio/sgravi/");
if (!defined('PARTITE'))
    define("PARTITE" ,SUPER_ROOT."/archivio/partite/");
if (!defined('STEMMI'))
    define("STEMMI" ,ROOT."/stemmi");
if (!defined('IMMAGINI'))
    define("IMMAGINI" ,ROOT."/immagini");
if (!defined('GIF'))
    define("GIF" ,ROOT."/GIF");

if (!defined('EMAIL_ROOT'))
    define("EMAIL_ROOT" , SUPER_ROOT."/archivio/Posta_Elettronica");

//PERCORSI WEB
if (!defined('ATTI_WEB'))
    define("ATTI_WEB" ,SUPER_WEB_ROOT."/archivio/atti");
if (!defined('IMG'))
    define("IMG", SUPER_WEB_ROOT."/img");
if (!defined('IMMAGINIWEB'))
    define("IMMAGINIWEB" ,WEB_ROOT."/immagini");
if (!defined('GIFWEB'))
    define("GIFWEB" ,WEB_ROOT."/GIF");
if (!defined('JS'))
    define("JS", WEB_ROOT. "/js");
if (!defined('CSS'))
    define("CSS", WEB_ROOT. "/CSS");
if (!defined('LIB'))
    define("LIB" , WEB_ROOT. "/lib");
if (!defined('STEMMIWEB'))
    define("STEMMIWEB" ,WEB_ROOT."/stemmi");
if (!defined('FIRMEWEB'))
    define("FIRMEWEB" ,SUPER_WEB_ROOT."/archivio/firme");
if (!defined('DUENOVANTA_WEB'))
    define("DUENOVANTA_WEB", SUPER_WEB_ROOT."/archivio/duenovanta");
if (!defined('PROCEDURE_WEB'))
    define("PROCEDURE_WEB" ,SUPER_WEB_ROOT."/archivio/procedure/");
if (!defined('PARTITE_WEB'))
    define("PARTITE_WEB" ,SUPER_WEB_ROOT."/archivio/partite/");
if (!defined('SGRAVI_WEB'))
    define("SGRAVI_WEB" ,SUPER_WEB_ROOT."/archivio/sgravi/");

//ASSETS
if (!defined('ASSETS'))
    define("ASSETS", WEB_ROOT."/assets");
if (!defined('FONTAWESOME'))
    define("FONTAWESOME", ASSETS."/fontawesome6");
if (!defined('DATATABLE'))
    define("DATATABLE", ASSETS."/datatable");

?>
