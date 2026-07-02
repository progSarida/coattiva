<?php
define("APP" , "/gitco2.com/Gitco2");//! OCCHIO QUANDO SI CARICA SUL SERVER AGGIUNGERE /gitco2.com
define("LOCAL", explode(APP,$_SERVER['SCRIPT_NAME'])[0]);

define("SUPER_ROOT", $_SERVER['DOCUMENT_ROOT'].LOCAL);
define("SUPER_WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL);
define("CONFIG_ROOT", SUPER_ROOT."/config");
define("ROOT", SUPER_ROOT.APP);
define("WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL.APP);

//CLASSI A LIVELLO SUPERIORE
define("SUPER_CLS" ,SUPER_ROOT. "/cls");
define("PHPEXCEL" ,SUPER_CLS."/Excel/Classes/PHPExcel.php");
define("IOFACTORY" ,SUPER_CLS."/Excel/Classes/PHPExcel/IOFactory.php");
define("PHPMAILER" ,SUPER_CLS."/PHPMailer/PHPMailerAutoload.php");
define("TCPDF" ,SUPER_CLS."/tcpdf");
define("DOMPDF" ,SUPER_CLS."/dompdf");
define("FPDI" ,SUPER_CLS."/fpdi");
define("DT_EDITOR" ,SUPER_CLS."/Editor-datatable");

//ARCHIVIO
define("ARCHIVIO" ,SUPER_ROOT."/archivio");
define("IMMAGINI_NOTIFICHE" ,ARCHIVIO."/Notifiche");
define("IMP_PAGAMENTI" ,ARCHIVIO."/Importazione_Pagamenti");
define("PAGAMENTI_ESTERI" ,ARCHIVIO."/Targhe_Estere/Pagamenti");
define("ATTI" ,ARCHIVIO."/atti");
define("PIGNORAMENTI" ,ARCHIVIO."/pignoramenti");
define("DUENOVANTA", ARCHIVIO."/duenovanta");
define("FIRME" ,ARCHIVIO."/firme");
define("MODELLI" ,ARCHIVIO."/modelli");
define("XLSSGRAVI" ,ARCHIVIO."/xls_sgravi");
define("PDFSGRAVI" ,ARCHIVIO."/pdf_sgravi");
define("PROCEDURE" ,ARCHIVIO."/procedure/");
define("SGRAVI" ,ARCHIVIO."/sgravi/");
define("PARTITE" ,ARCHIVIO."/partite/");
define("STRAGIUDIZIALE" ,ARCHIVIO."/stragiudiziale");
define("EMAIL_ROOT" , ARCHIVIO."/Posta_Elettronica");
define("SIGNED_FILES" , ARCHIVIO."/signed_files");

//ARCHIVIO WEB
define("ARCHIVIO_WEB" ,SUPER_WEB_ROOT."/archivio");
define("IMMAGINI_NOTIFICHE_WEB" ,ARCHIVIO_WEB."/Notifiche");
define("ATTI_WEB" ,ARCHIVIO_WEB."/atti");
define("FIRMEWEB" ,ARCHIVIO_WEB."/firme");
define("DUENOVANTA_WEB", ARCHIVIO_WEB."/duenovanta");
define("PROCEDURE_WEB" ,ARCHIVIO_WEB."/procedure/");
define("PARTITE_WEB" ,ARCHIVIO_WEB."/partite/");
define("SGRAVI_WEB" ,ARCHIVIO_WEB."/sgravi/");
define("STRAGIUDIZIALEWEB" ,ARCHIVIO_WEB."/stragiudiziale");
define("EMAIL_WEB" , ARCHIVIO_WEB."/Posta_Elettronica");

//IMMAGINI A LIVELLO SUPERIORE WEB
define("IMG", SUPER_WEB_ROOT."/img");

//GITCO 2
define("ANAGRAFE", ROOT."/anagrafe");
define("INC", ROOT. "/inc");
define("CLS" ,ROOT. "/cls");
define("CLASSI" ,ROOT. "/classi");
define("STEMMI" ,ROOT. "/stemmi");
define("IMMAGINI" ,ROOT."/immagini");
define("GIF" ,ROOT."/GIF");

//ELABORAZIONI
define("ELABORAZIONI", ROOT."/elaborazioni");
define("ELAB_DTEDITOR", ELABORAZIONI."/dteditor");
define("ELAB_ATTI", ELABORAZIONI."/atti");
define("ELAB_PIGNORAMENTI", ELABORAZIONI."/pignoramenti");

//GITCO 2 WEB
define("JS", WEB_ROOT. "/js");
define("CSS", WEB_ROOT. "/CSS");
define("LIB" , WEB_ROOT. "/lib");
define("STEMMIWEB" ,WEB_ROOT."/stemmi");
define("IMMAGINIWEB" ,WEB_ROOT."/immagini");
define("GIFWEB" ,WEB_ROOT."/GIF");

//ELABORAZIONI WEB
define("ELABORAZIONI_WEB", WEB_ROOT."/elaborazioni");
define("ELABORAZIONI_JS", ELABORAZIONI_WEB."/js");
define("ELAB_DTEDITOR_WEB", ELABORAZIONI_WEB."/dteditor");
define("ELAB_ATTI_WEB", ELABORAZIONI_WEB."/atti");
define("ELAB_ATTI_JS", ELAB_ATTI_WEB."/js");
define("ELAB_PIGNORAMENTI_WEB", ELABORAZIONI_WEB."/pignoramenti");
define("ELAB_PIGNORAMENTI_JS", ELAB_PIGNORAMENTI_WEB."/js");

//GITCO 2 WEB ASSETS
define("ASSETS", WEB_ROOT."/assets");
define("FONTAWESOME", ASSETS."/fontawesome6");
define("DATATABLE", ASSETS."/datatable");

?>
