<?php

ini_set('display_errors',"1");
date_default_timezone_set("Europe/Rome");
session_start();
define("DEBUG" ,true);
define("PROD" ,true);

if(PROD===false)
    define("LOCAL" ,"/coattiva");
else
    define("LOCAL" ,"");
define("SITE" , "/Gitco2");

define("SUPER_ROOT", $_SERVER['DOCUMENT_ROOT'].LOCAL);
define("ROOT", __DIR__);

define("SUPER_WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL);
define("WEB_ROOT", 'http://'.$_SERVER['HTTP_HOST'].LOCAL.SITE);

define("DUENOVANTA", $_SERVER['DOCUMENT_ROOT']."/archivio/duenovanta");

//PERCORSI FISICI
define("INC", ROOT. "/inc");
define("CLS" ,ROOT. "/cls");
define("CLASSI" ,ROOT. "/classi");

define("SUPER_CLS" ,SUPER_ROOT. "/cls");
define("PHPEXCEL" ,SUPER_ROOT. "/cls/Excel/Classes/PHPExcel.php");
define("PHPMAILER" ,SUPER_ROOT. "/cls/PHPMailer/PHPMailerAutoload.php");

//PERCORSI WEB
define("IMG", SUPER_WEB_ROOT."/img");
define("JS", SUPER_WEB_ROOT. "/js");
define("CSS", SUPER_WEB_ROOT. "/css");
define("LIB" , SUPER_WEB_ROOT. "/lib");
	
?>