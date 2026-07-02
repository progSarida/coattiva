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

$a_usersAdmin = $cls_db->getResults( $cls_db->SelectQuery("SELECT * FROM autenticazione"),"array","ID" );
$a_years = $cls_db->getResults( $cls_db->SelectQuery("SELECT * FROM anni_gestiti WHERE CC_Anno = '".$a_enteAdmin['CC']."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC") );

$cls_html = new cls_html();
$adminCityYears = $cls_html->optionsFromArray($a_years,"Anno" );

$userName = "Operatore: ".$_SESSION['username'];

if (!session_id()) session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" id="viewport">

    <title>GITCO COATTIVA</title>
    <link rel=StyleSheet href="<?= CSS ?>/classi_semplici.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/gitco_styles.css" type="text/css">
    <link rel="stylesheet" href="<?= JS ?>/bootstrapvalidator-0.5.2/vendor/bootstrap/css/bootstrap.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= CSS ?>/bootstrap-theme.css" type="text/css" media="all" />
    <link rel=StyleSheet href="<?= CSS ?>/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/FontAwesome/css/all.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/FontAwesome/css/brands.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/FontAwesome/css/solid.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/image_magnifier.css" type="text/css" media=screen>
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
    <!--Basta creare un div (row->col-10 ofset-1) con id="appendTable"  e richiamare la classe TableGenerator(array con dati(jeson-encode),array campi da stampare(facoltativo, li stampa tutti));-->
    <script src="<?= JS ?>/SortPaginateTable.js" type="text/javascript"></script>
    <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>-->

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
              $('#select_adminCityYear').val(adminYear);
        }

        $(function() {  $( ".picker" ).datepicker();    });

        function changeAdminYear(){
            year = $("#select_adminCityYear").val();
            strLink = thisPage+"?";
            strLink += "c=" + adminCity;
            strLink += "&a=" + year;
            strLink += "&pageCalled=<?= $cls_help->getVar('pageCalled'); ?>";
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

        function openWindowSearch(linkToOpen,a_dimensions){
            //alert(a_dimensions.width);
            if(a_dimensions==undefined)
                var a_dimensions = {width:700, height:500, left:200, top:200};

            //alert(linkToOpen);
            var targetWin = window.open(linkToOpen, '_blank', 'toolbar=no, location=no, directories=no, status=no, menubar=no, ' +
                'scrollbars=yes, resizable=yes, copyhistory=no, width=' + a_dimensions.width + ', height=' + a_dimensions.height + ', top=' + a_dimensions.top + ', left=' + a_dimensions.left);
            targetWin.focus();
        }
    </script>
</head>
<br>
<body class="sfondo_new_gitco">

<div class="container-fluid" style="width: 85%; min-height: 100%;">

  <div class="row justify-content-lg-center table_azzurra_bootstrap" >
      <div class="col col-lg-6" style="padding-top: 1%; padding-bottom: 1%;">
        <span class="comune" ><?php echo $adminCity; ?></span>
        <select id="select_adminCityYear" onchange='changeAdminYear();'>
            <?php echo $adminCityYears; ?>
        </select>
      </div>
      <div class="col-lg-6 text_right " style="padding-top: 1%; padding-bottom: 1%;">
        <span class="user" ><?php echo $userName; ?></span>
      </div>
  </div>

  <script>setAdminYear();</script>
  <div class="row justify-content-lg-center table_azzurra_bootstrap " style="min-height: 90vh !important;">
