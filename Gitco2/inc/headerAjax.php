<?php

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_html.php";
include_once CLS . "/cls_help.php";

$cls_help = new cls_help();
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$thisPage = basename($_SERVER['SCRIPT_FILENAME']);
$pageName = explode(".",$thisPage)[0];

//$userName = "Operatore: ".$_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" id="viewport">

    <title>GITCO COATTIVA</title>
    <link rel=StyleSheet href="<?= CSS ?>/classi_semplici.css" type="text/css" media=screen>
    <link rel="stylesheet" href="<?= JS ?>/bootstrapvalidator-0.5.2/vendor/bootstrap/css/bootstrap.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= CSS ?>/bootstrap-theme.css" type="text/css" media="all" />
    <link rel=StyleSheet href="<?= CSS ?>/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/FontAwesome/css/fontawesome.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/FontAwesome/css/brands.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/FontAwesome/css/solid.css" type="text/css" media=screen>
    <link rel="stylesheet" href="<?= JS ?>/bootstrapvalidator-0.5.2/dist/css/bootstrapValidator.css" type="text/css" media="all" />
    <style> .ui-datepicker { font-size:11px; } </style>

    <script src="<?= JS ?>/bootstrapvalidator-0.5.2/vendor/jquery/jquery-1.10.2.min.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/form_jquery.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/jquery.bpopup.min.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/funzioni.js" type="text/javascript" ></script>

    <script src="<?= JS ?>/bootstrapvalidator-0.5.2/vendor/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?= JS ?>/jquery-ui.js" type="text/javascript"></script>
    <script src="<?= JS ?>/datepicker.js" type="text/javascript"></script>
    <script src="<?= JS ?>/image_magnifier.js" type="text/javascript"></script>
    <script src="https://cdn.ckeditor.com/4.11.1/full-all/ckeditor.js"></script>
    <script src="<?= JS ?>/jquery.validate.js" type="text/javascript"></script>
    <script src="<?= JS ?>/bootstrapvalidator-0.5.2/dist/js/bootstrapValidator.js" type="text/javascript"></script>
    <script src="<?= JS ?>/it_IT.js" type="text/javascript"></script>
    <script src="<?= JS ?>/SortPaginateTable.js" type="text/javascript"></script>


    <script>
        function openWindowSearch(linkToOpen,a_dimensions){
            if(a_dimensions==undefined)
                var a_dimensions = {width:700, height:500, left:200, top:200};

            //alert(linkToOpen);
            var targetWin = window.open(linkToOpen, '_blank', 'toolbar=no, location=no, directories=no, status=no, menubar=no, ' +
                'scrollbars=yes, resizable=yes, copyhistory=no, width=' + a_dimensions['width'] + ', height=' + a_dimensions['height'] + ', top=' + a_dimensions['top'] + ', left=' + a_dimensions['left']);
            targetWin.focus();
        }

        $(function() {  $( ".picker" ).datepicker();    });
    </script>
</head>
<br>
<body class="sfondo_new_gitco">

<div class="container-fluid" style="width: 75%; min-height: 100%;">
    <div class="row justify-content-lg-center table_azzurra_bootstrap " style="min-height: 90vh !important;">




