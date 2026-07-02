<?php
$MainPath = 'http://'.$_SERVER['HTTP_HOST'];


$_SESSION['MainMenuPath']=$MainPath;
define("PRODUCTION",0);
define("IMAGES", $MainPath."/img/");
define("INC", $MainPath."/inc/");
define("JS", $MainPath."/js/");
define("LIB", $MainPath."/lib/");
define("CSS", $MainPath."/css/");


define("DB_HOST", 'localhost');
define("DB_NAME", 'gitco2');
define("DB_USERNAME", 'root');


if(PRODUCTION){
    define("DB_PASSWORD", 'S@rida');
}else{
    define("DB_PASSWORD", 'edera');
}
