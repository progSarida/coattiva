<?php

header('X-Robots-Tag: noindex, nofollow, noarchive');
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

$_printType = $cls_help->getVar('printType');
$_docType = $cls_help->getVar('docType');

if($c==NULL) {
    header("Location:".WEB_ROOT."/menu/scelta_CC_e_anno.php");
    die;
}

$thisPage = basename($_SERVER['SCRIPT_FILENAME']);
$pageName = explode(".",$thisPage)[0];

$cls_db = new cls_db();
$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$adminCity = $a_enteAdmin['Denominazione']." [".$a_enteAdmin['CC']."]";
$adminCityName = $a_enteAdmin['Denominazione'];

$a_years = $cls_db->getResults( $cls_db->SelectQuery("SELECT * FROM anni_gestiti WHERE CC_Anno = '".$a_enteAdmin['CC']."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC") );

$cls_html = new cls_html();
$adminCityYears = $cls_html->optionsFromArray($a_years,"Anno" );

$userName = "Operatore: ".$_SESSION['username'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" id="viewport">

    <title>GITCO COATTIVA</title>
    <link rel=StyleSheet href="<?= CSS ?>/classi_semplici.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style> .ui-datepicker { font-size:11px; } </style>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="<?= JS ?>/form_jquery.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/jquery.bpopup.min.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/funzioni.js" type="text/javascript" ></script>

    <script src="<?= JS ?>/bootstrap.js" type="text/javascript"></script>
    <script src="<?= JS ?>/jquery-ui.js" type="text/javascript"></script>
    <script src="<?= JS ?>/datepicker.js" type="text/javascript"></script>
    <script src="<?= JS ?>/bootstrapValidator.js" type="text/javascript"></script>
    <script src="https://cdn.ckeditor.com/4.11.1/full-all/ckeditor.js"></script>

    <script>
        var adminYear = "<?php echo $a; ?>";
        var adminCity = "<?php echo $c; ?>";
        var _printType = "<?php echo $_printType; ?>";
        var _docType = "<?php echo $_docType; ?>";
        var getString = "c="+adminCity+"&a="+adminYear;

        var adminCityName = "<?php echo $a_enteAdmin['Denominazione']; ?>";
        var thisPage = "<?php echo $thisPage; ?>";
        var pageName = "<?php echo $pageName; ?>";

        function setAdminYear(){
            if(adminCity!="" && adminYear!="")
                $('#select_adminCityYear option[value='+adminYear+']').attr('selected',true);
        }

        $(function() {  $( ".picker" ).datepicker();    });

        function changeAdminYear(){
            year = $("#select_adminCityYear").val();
            strLink = thisPage+"?";
            strLink += "c=" + adminCity;
            strLink += "&a=" + year;
            if(_printType!="")
                strLink += "&printType=" + _printType;
            if(_docType!="")
                strLink += "&docType=" + _docType;

            if(year.length!=4)
                alert("Selezionare l'anno!");
            else if(year.length==4)
                location.href = strLink;
        }

        function openLocation(pageName, get, overrideGetString ){
            if(get === undefined) {
                get = null
            }
            if(overrideGetString === undefined) {
                overrideGetString = null
            }

            string = pageName+".php?";
            if(overrideGetString==null)
                overrideGetString = getString;

            string+= overrideGetString;


            if(get!=null)
                string+= "&"+get;

            location.href = string;
        }

        window.showModalDialog = function (arg1, arg2, arg3) {

            var w;
            var h;
            var resizable = "yes";
            var scroll = "yes";
            var status = "no";

            // get the modal specs
            var mdattrs = arg3.split(";");
            for (i = 0; i < mdattrs.length; i++) {
                var mdattr = mdattrs[i].split(":");

                var n = mdattr[0];
                var v = mdattr[1];
                if (n) { n = n.toLowerCase(); }
                if (v) { v = v.toLowerCase(); }

                if (n == "resizable") {
                    resizable = v;
                } else if (n == "scroll") {
                    scroll = v;
                } else if (n == "status") {
                    status = v;
                }
            }

            var w = 700;
            var h = 500;
            var left = 100;
            var top = 100;
            var targetWin = window.open(arg1, '_blank', 'toolbar=no, location=no, directories=no, status=' + status + ', menubar=no, scrollbars=' + scroll + ', resizable=' + resizable + ', copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
            targetWin.focus();
        };

        $(function() {

            $( ".picker" ).datepicker();

        });
    </script>
    <style>
        .table_azzurra{
            width:80%;
        }
        .table_interna{
            width:78%;
        }
    </style>
</head>
<br>
<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
    <tr>
        <td class="width1"><br></td>
        <td class="text_left">
            <span class="comune" ><?php echo $adminCity; ?></span>
            <select id="select_adminCityYear" onchange='changeAdminYear();'>
                <?php echo $adminCityYears; ?>
            </select>
        </td>
        <td class="text_right"><span class="user" ><?php echo $userName; ?></span></td>
        <td class="width1"><br></td>
    </tr>
</table>
<script>setAdminYear();</script>
<table class="table_azzurra text_center" style="height:93%;">
    <tr>
        <td valign=top>




