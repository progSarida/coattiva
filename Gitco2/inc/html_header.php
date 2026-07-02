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

    <script>
        var adminYear = "<?php echo $a; ?>";
        var adminCity = "<?php echo $c; ?>";
        var taxPayer = "<?php echo $p; ?>";
        var getString = "c="+adminCity+"&a="+adminYear+"&p="+taxPayer;
        var thisPage = "<?php echo $thisPage; ?>";
        var pageName = "<?php echo $pageName; ?>";

        function openLocation(pageName, get, overrideGetString ){

            string = pageName+".php?";
            if(overrideGetString==undefined)
            overrideGetString = getString;

            string+= overrideGetString;

            if(get!=undefined)
            string+= "&"+get;

            location.href = string;
        }

        function changeAdminYear(){
            year = $("#select_years").val();
            strLink = thisPage+"?";
            strLink += "c=" + adminCity;
            strLink += "&a=" + year;

            location.href = strLink;
        }

        function changeAdminCity(){
            city = $("#select_cities").val();
            strLink = thisPage+"?";
            strLink += "c=" + city;
            strLink += "&a=" + adminYear;

            location.href = strLink;
        }
    </script>
</head>
<br>

<body class="sfondo_new_gitco">
<table class="table_azzurra text_center" style="height:8%;">
    <tr>
        <td width="23%" align="center">
            <img src="<?= IMG ?>/sarida_logo_medium.png" title="Logo dell'Azienda" border="0">
        </td>
        <td align="center"><font class="titolo font24" >Gestione Integrata Tributi Comunali</font></td>
        <td width=23% align="center">
            <img src="<?= IMG ?>/sarida_logo_medium.png" title="Logo dell'Azienda" border="0">
        </td>
    </tr>
</table>

<table class="table_azzurra text_center" style="height:92%;">
    <tr>
        <td valign=top>




