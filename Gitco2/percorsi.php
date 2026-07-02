<?php

/****************************************************************************/
/* Elenco di percorsi dell'applicazione.                                    */
/* -------------------------------------                                    */
/* Il seguente file � da includere in ogni script e definisce tutti i       */
/* percorsi necessari per l'applicazione.                                   */
/* In questo modo l'applicazione diventa portabile semplicemente            */
/* modificando questo file.                                                 */
/****************************************************************************/

$PATH_SISTEMA = str_replace("Program Files (x86)", "progra~2", $_SERVER['DOCUMENT_ROOT']);

if (!defined('ALBANY'))
    define("ALBANY" ,false);

if(ALBANY===false){
    if (!defined('LOCAL'))
        define("LOCAL" ,"");
}
else{
    if (!defined('LOCAL'))
        define("LOCAL" ,"/coattiva");
}
if (!defined('SITE'))
    define("SITE" , "/gitco2.com/Gitco2");
if (!defined('SUPER_ROOT'))
    define("SUPER_ROOT", $_SERVER['DOCUMENT_ROOT'].LOCAL);
if (!defined('ROOT'))
    define("ROOT", __DIR__);

if (!defined('SUPER_WEB_ROOT'))
    define("SUPER_WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL);
if (!defined('WEB_ROOT'))
    define("WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL.SITE);

$basePath = $_SERVER['DOCUMENT_ROOT']."/Gitco2";
$archivioPath = $_SERVER['DOCUMENT_ROOT']."/archivio";

$pathwebroot = 'http://'.$_SERVER['HTTP_HOST']."/Gitco2";


$attiPath = $archivioPath."/atti";
$Path290 = $archivioPath."/duenovanta";
$PathVerbaliEsteri = "/archivio/Targhe_Estere/Verbali/";
$PathVerbaliComuneEsteri = "/archivio/Targhe_Estere/Verbali/PerComune/";
$PathVerbaliContravEsteri = "/archivio/Targhe_Estere/Verbali/ProvContravventori/";
$PathVerbaliDistinte = "/archivio/Targhe_Estere/Verbali/Distinte/";
$PathSollecitiEsteri = "/archivio/Targhe_Estere/Solleciti/";
$PathSollecitiComuneEsteri = "/archivio/Targhe_Estere/Solleciti/PerComune/";
$PathSollecitiContravEsteri = "/archivio/Targhe_Estere/Solleciti/ProvContravventori/";
$PathSollecitiDistinte = "/archivio/Targhe_Estere/Solleciti/Distinte/";
$PathRichiesteEstere = "/archivio/Targhe_Estere/Richieste/";
$PathElenchiEsteri = "/archivio/Targhe_Estere/Elenchi/";
$PathFlussiEsteri = "/archivio/Targhe_Estere/Flussi/";
$PathCartolineEstere = "/archivio/Targhe_Estere/Cartoline/";
$PathPagamentiEsteri = "/archivio/Targhe_Estere/Pagamenti/";
$PathCompletoVerbaliEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathVerbaliEsteri;
$PathCompletoVerbaliComuneEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathVerbaliComuneEsteri;
$PathCompletoVerbaliContravEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathVerbaliContravEsteri;
$PathCompletoVerbaliDistinte = $_SERVER['DOCUMENT_ROOT'] . $PathVerbaliDistinte;
$PathCompletoSollecitiEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathSollecitiEsteri;
$PathCompletoSollecitiComuneEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathSollecitiComuneEsteri;
$PathCompletoSollecitiContravEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathSollecitiContravEsteri;
$PathCompletoSollecitiDistinte = $_SERVER['DOCUMENT_ROOT'] . $PathSollecitiDistinte;
$PathCompletoRichiesteEstere = $_SERVER['DOCUMENT_ROOT'] . $PathRichiesteEstere;
$PathCompletoElenchiEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathElenchiEsteri;
$PathCompletoFlussiEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathFlussiEsteri;
$PathCompletoCartolineEstere = $_SERVER['DOCUMENT_ROOT'] . $PathCartolineEstere;
$PathCompletoPagamentiEsteri = $_SERVER['DOCUMENT_ROOT'] . $PathPagamentiEsteri;
$PathMAILER = $basePath."/classi/PHPMailer";

$PathImportazioniNotifiche = "/archivio/Importazioni_Notifiche/";
$PathCompletoImportNotifiche = $PATH_SISTEMA . $PathImportazioniNotifiche;
$PathImmaginiNotifiche = "/archivio/Notifiche/";
$PathCompletoImmaginiNotifiche = $PATH_SISTEMA . $PathImmaginiNotifiche;
$PathImportazioniEstereNotifiche = "/archivio/Targhe_Estere/Importazioni_Notifiche/";
$PathCompletoImportEstereNotifiche = $_SERVER['DOCUMENT_ROOT'] . $PathImportazioniEstereNotifiche;
$PathImmaginiEstereNotifiche = "/archivio/Targhe_Estere/Notifiche/";
$PathCompletoImmaginiEstereNotifiche = $_SERVER['DOCUMENT_ROOT'] . $PathImmaginiEstereNotifiche;

$PathImportazioniPagamenti = "/archivio/Importazioni_Pagamenti/";
$PathCompletoImportPagamenti = $_SERVER['DOCUMENT_ROOT'] . $PathImportazioniPagamenti;

$PathPagamentiDaBonificare = "/archivio/Importazioni_Pagamenti/DaBonificare/";
$PathCompletoPagamentiDaBonificare = $_SERVER['DOCUMENT_ROOT'] . $PathPagamentiDaBonificare;

$PathFotoEstereTarghe = "/FotoTargheEstere";
$PathDocsEstereTarghe = "/DocsTargheEstere";

$pathModelli = $archivioPath."/Modelli";

$PathFatture = "/archivio/Fatture";
$PathCompletoFatture = $_SERVER['DOCUMENT_ROOT'] . $PathFatture;

// Cartella dei file di libreria
if (! defined("CLS") )
    if ( ! define ("CLS" , $basePath . "/cls") )
        die ("define costante CLS fallita");

if (! defined("LIBRERIE") )
    if ( ! define ("LIBRERIE" , $basePath . "/librerie/php") )
        die ("define costante LIBRERIA fallita");

// Cartella dei file di definizione delle classi
if (! defined("CLASSI") )
    if ( ! define ("CLASSI" , $basePath . "/classi") )
        die ("Define costante CLASSI fallita");

// Immagini usate nel sito
if (! defined("IMMAGINI") )
    if ( ! define ("IMMAGINI" , $basePath . "/immagini") )
        die ("Define costante IMMAGINI fallita");

// Cartella della libreria FPDF
if (! defined("FPDF") )
    if ( ! define ("FPDF" , $basePath . "/fpdf") )
        die ("Define costante FPDF fallita");

// Cartella anagrafe
if (! defined("ANAGRAFE") )
    if ( ! define ("ANAGRAFE" , $basePath . "/anagrafe") )
        die ("Define costante ANAGRAFE fallita");

// Cartella INC
if (! defined("INC") )
    if ( ! define ("INC" , $basePath . "/inc") )
        die ("Define costante INC fallita");

// Cartella ruolo
if (! defined("RUOLO") )
    if ( ! define ("RUOLO" , $basePath . "/coattiva") )
        die ("Define costante ANAGRAFE fallita");

// Cartella gestione ente
if (! defined("GESTIONE") )
    if ( ! define ("GESTIONE" , $basePath . "/gestione") )
        die ("Define costante GESTIONE fallita");

// Cartella stemmi
if (! defined("STEMMI") )
    if ( ! define ("STEMMI" , $basePath . "/stemmi") )
        die ("Define costante STEMMI fallita");

// Cartella Excel
if (! defined("EXCEL") )
    if ( ! define ("EXCEL" , $basePath . "/excel/Classes") )
        die ("Define costante EXCEL fallita");

// Cartella Tcpdf
if (! defined("TCPDF") )
    if ( ! define ("TCPDF" , $basePath . "/tcpdf") )
        die ("Define costante TCPDF fallita");

// Cartella Tcpdf
if (! defined("FPDI") )
    if ( ! define ("FPDI" , $basePath . "/classi/fpdi") )
        die ("Define costante FPDI fallita");

// Cartella dei file di libreria
if (! defined("ATTI") )
    if ( ! define ("ATTI" , $attiPath ) )
        die ("define costante ATTI fallita");

// Cartella dei file di libreria
if (! defined("FIRME") )
    if ( ! define ("FIRME" , $archivioPath."/firme" ) )
        die ("define costante FIRME fallita");

// Cartella dei file di libreria
if (! defined("DUENOVANTA") )
    if ( ! define ("DUENOVANTA" , $Path290 ) )
        die ("define costante 290 fallita");

// Cartella targhe estere
if (! defined("TARGHEESTERE") )
    if ( ! define ("TARGHEESTERE" , $basePath . "/targheestere") )
        die ("Define costante TARGHEESTERE fallita");

// Cartella pubblicita
if (! defined("PUBBLICITA") )
    if ( ! define ("PUBBLICITA" , $basePath . "/pubblicita") )
        die ("Define costante PUBBLICITA fallita");

// Cartella pubblicita temporanea
if (! defined("PUBBLICITA_TEMP") )
    if ( ! define ("PUBBLICITA_TEMP" , $basePath . "/pubblicita_temp") )
        die ("Define costante PUBBLICITA_TEMP fallita");

// Cartella fatturazione
if (! defined("FATTURAZIONE") )
    if ( ! define ("FATTURAZIONE" , $basePath . "/fatturazione") )
        die ("Define costante FATTURAZIONE fallita");

// Cartella ruolo
if (! defined("MENU") )
    if ( ! define ("MENU" , $basePath . "/menu") )
        die ("Define costante MENU fallita");

// MAIL
if (! defined("EMAIL") )
    if ( ! define ("EMAIL" , $PathMAILER) )
        die ("Define costante MENU fallita");

// MAIL
if (! defined("MODELLI") )
    if ( ! define ("MODELLI" , $pathModelli) )
        die ("Define costante MODELLI fallita");

?>
