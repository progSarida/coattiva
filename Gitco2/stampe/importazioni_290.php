<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once(INC . "/header.php");
include_once(INC . "/menu.php");
include_once(CLS . "/cls_290.php");
include_once(CLS . "/cls_file.php");
include_once(CLS . "/cls_html.php");
include_once(CLS . "/cls_help.php");

$cls_help = new cls_help();
$cls_290 = new cls_290(array("CC" => $c));

$c = $cls_help->getVar("c");
//$search = $cls_help->getVar("search");

$nrRecordPerPag = 25;
$indirizzo = $_SERVER['REQUEST_URI'];
if (!isset($_GET['nrPag'])) {
    unset($_SESSION['RISULTATI']);
    unset($_SESSION['FILTRI']);
    $nrPag = 1;
    $query = "SELECT * FROM imports WHERE 1 = 1 AND CC = '".$c."'";

    if (isset($_POST['status'])) {
        $stato = $_POST['status'];
        if ($stato !== "")
            $query .= " AND Import_Status_Id= " . $stato . " ";
    }

    if (isset($_POST['inputFile'])) {
        $inputFile = trim($_POST['inputFile']);
        if ($inputFile !== "")
            $query .= " AND Filename LIKE '%" . $inputFile . "%' ";
    }

    $query .= " ORDER BY Upload_Datetime DESC";

    $a_imports = $cls_db->getResults($cls_db->ExecuteQuery($query));

    $_SESSION['RISULTATI'] = $a_imports;

    if (isset($_POST["subFilter"])) {
        $_SESSION['FILTRI'] = $_POST;
    }
} else {

    list($baseUrl, $urlQuery) = explode('?', $indirizzo, 2);
    parse_str($urlQuery, $urlQueryArr);
    unset($urlQueryArr['nrPag']);

    $indirizzo = $baseUrl;
    if(count($urlQueryArr))
        $indirizzo = $indirizzo.'?'.http_build_query($urlQueryArr);

    $nrPag = intval($_GET['nrPag']);
    $a_imports = $_SESSION['RISULTATI'];
}

$a_imports = array_slice($a_imports, ($nrPag - 1) * $nrRecordPerPag, $nrRecordPerPag);

$cls_html = new cls_html();

/** GV - 07/04/2022 - END */
$query = "SELECT * FROM import_status";
$a_importStatus = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "Id");
if(!isset($_SESSION['FILTRI']['status']))
    $_SESSION['FILTRI']['status'] = null;
$a_selection = array("value" => "Id", "firstOpt" => 0, "selected" => $_SESSION['FILTRI']['status'], "text" => array("[Name]"));
$optStatus = $cls_html->getOptions($a_importStatus, $a_selection);

$query = "SELECT * FROM import_types";
$a_importTypes = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "Id");
/** GV - 11/04/2022 - START  */
$query = "SELECT DISTINCT auth.* FROM autenticazione as auth JOIN imports AS imp ON auth.ID = imp.Upload_User_Id ORDER BY auth.User";
$a_uploadUser = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");
if(!isset($_SESSION['FILTRI']['user']))
    $_SESSION['FILTRI']['user'] = null;
$a_selection = array("value" => "ID", "firstOpt" => 0, "selected" => $_SESSION['FILTRI']['user'], "text" => array("[User]"));
$optUploadUser = $cls_html->getOptions($a_uploadUser, $a_selection);

$query = "SELECT DISTINCT auth.* FROM autenticazione as auth JOIN imports AS imp ON auth.ID = imp.Import_User_Id ORDER BY auth.User";
$a_importUser = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");
if(!isset($_SESSION['FILTRI']['importUser']))
    $_SESSION['FILTRI']['importUser'] = null;
$a_selection = array("value" => "ID", "firstOpt" => 0, "selected" => $_SESSION['FILTRI']['importUser'], "text" => array("[User]"));
$optImportUser = $cls_html->getOptions($a_importUser, $a_selection);

?>

<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font22 under_decor">Stampa importazione Minuta di ruolo</span>
    </div>
</div>
<div class="clean_row HSpace4"  style='margin-top:60px;'></div>
<div class="container-fluid">
<div id="contForm" class="col-lg-12 <?php echo $classFilterForm; ?> " style="padding:5px">
<form id="filterForm" action="<?php echo $indirizzo; ?>" method="post">
			<fieldset style="border: 1px solid #ddd !important; margin: 0; padding: 10px; position: relative; border-radius:6px; background-color:#d1d8ee; padding-left:10px!important;">
                <!-- <legend style="font-size:14px; font-weight:bold; margin-bottom: 0px; width: 35%; border: 1px solid #ddd;"> Filtri:</legend> -->
                <div class="row align-items-center justify-content-center" style="margin-left:10px;">
                <!--
                    <div class="col-md-3">
                        <div class="form-group">
                            <select class="form-select" id="ente" name="ente" style="height:35px; width:75%;">
                                "<option value=''>Seleziona ente </option>
                                <?= $optEnte; ?>
                            </select>
                        </div>
                    </div> -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <select class="form-select" id="status" name="status" style="height:32px; width:75%;">
                                <option value="">Seleziona lo stato</option>
                                <?php echo $optStatus; ?>+
                            </select>
                        </div>
                    </div>
                <!--    
                    <div class="col-xs-3">
                        <div class="form-group">
                            <select class="form-select" id="user" name="user" style="height:38px; width:75%; ">
                                <option value="">Seleziona utente che carica </option>
                                <?php  echo $optUploadUser; ?>+
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-3">
                        <div class="form-group">
                            <select class='form-select' id='importUser' name='importUser' style="height:35px; width:75%;">
                                <option value=''>Seleziona utente che importa</option>
                                <?php  echo $optImportUser; ?>+
                            </select>
                        </div>
                    </div>
                -->
                    <div class="col-md-8">
                        <div class="form-group">
<?php
                            $act = "";
                            if (isset($_POST['inputFile']))
                                $act = $_POST['inputFile'];
?>
                            <input type="input" class="form-control" id="inputFile" name="inputFile" placeholder="Compila con il nome del File"  value="<?php echo $act; ?>">
                        </div>
                    </div>
                    <button id="subFilter" name="subFilter" type="submit" class="btn btn-primary ">Filtra</button>
                </div>
                <div class="row align-items-right justify-content-center" style="margin-left:10px;">
                    <div class="col-md-6"></div>
                    <label class="col-lg-2 control-label resize" style="text-align: left;">Tipo stampa provvisoria</label>
                    <div class="form-group col-lg-3">
                        <select id="printType" name="printType" tabindex=9 class="form-control resize validateCustom vld_Custom_r">
                            <option value="pdf">PDF</option>
                            <option value="excell">Excel</option>
                        </select>
                    </div>
                </div>
		    </fieldset>
	    </form>
</div>
</div>
<div class="clean_row HSpace4"></div>

<div class="row-fluid gitco-container">
    <div class="clean_row HSpace4"></div>
    
    <div class="col-lg-2 RowLabel RowLabelHeight4">
      <!-- GV - 06/04/2022 - START   -->
      <!-- <button id="btnFilter" type="button" class="btn btn-warning" onclick = "openFilter()"><i class="fa fa-filter" ></i>Filtri</button> -->
      <!-- GV - 06/04/2022 - END   -->
    </div>

    <div class="col-lg-8 RowLabel RowLabelHeight4 text-center">
        IMPORTAZIONI
    </div>
    <div class="col-lg-2  RowLabel RowLabelHeight4 text-right">
        <!-- 
        <a href="#" onMouseover="title='Modello principale'" onclick="scaricaModello();" style="text-decoration: none;">
            <img src="<?= IMMAGINIWEB; ?>/icon-excel.png" width=30px height=30px border="0">
        </a>
        -->
    </div>
    <div class="clean_row HSpace1"></div>
    <div id="listTable"  style='margin-bottom:10px;'>
        <?= $cls_290->getHtmlPrintImportsList($a_imports, $a_usersAdmin, $a_importStatus, $a_importTypes); ?>
    </div>

</div>

<?php
$total_pages = ceil(count($_SESSION['RISULTATI']) / $nrRecordPerPag);
if ($total_pages > 1) {
    $sepPar = "?";
    if ($_GET) {
        $sepPar = "&";
    }

?>
    <nav>
        <ul class="pagination  justify-content-center">
            <li class=" page-item <?php if ($nrPag == 1) {
                echo 'disabled';
            } ?>"><a class="page-link" href="<?php echo $indirizzo . $sepPar; ?>nrPag=1">Prima</a></li>
            <li class="page-item <?php if ($nrPag <= 1) {
                echo 'disabled';
            } ?>">
            <a class="page-link" href="<?php if ($nrPag <= 1) {
                echo '#';
            } else {
                echo $indirizzo . $sepPar . "nrPag=" . ($nrPag - 1);
            } ?>">Prec</a>
            </li>
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
            ?>
                <li class="page-item  <?php if ($nrPag == $i) {
                    echo 'disabled';
                } ?>"><a class="page-link" href="<?php echo $indirizzo . $sepPar; ?>nrPag=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php
            }
            ?>

            <li class="page-item  <?php if ($nrPag >= $total_pages) {
                    echo 'disabled';
                } ?>">
                <a class="page-link" href="<?php if ($nrPag >= $total_pages) {
                    echo '#';
                } else {
                    echo $indirizzo . $sepPar . "nrPag=" . ($nrPag + 1);
                } ?>">Succ</a>
            </li>
            <li class="page-item <?php if ($nrPag == $total_pages) {
                echo 'disabled';
            } ?>"><a class="page-link" href="<?php echo $indirizzo . $sepPar; ?>nrPag=<?php echo $total_pages; ?>">Ultima</a></li>
        </ul>
    </nav>
<?php
}
?>
<script>
    //F5
    switchMenuImg("F5");
    F5_button = function() {
        /* GV - 07/04/2022 - START 
        location.href = "upload_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        */
        <?php
            $url =  "importazioni_290.php?c=".$c."&a=".$a;
        ?>
        location.href = "<?php echo $url; ?>";
        /* GV - 07/04/2022 - END */        
    }

    $(document).ready(function(){
        spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
    });

    function print_(val,id){
        location.href = "<?= WEB_ROOT ?>/290/mgmt_290.php?c=<?= $c ?>&a=<?= $a ?>&Import_Id=" + id;
    }

    function print(val,id){
        if(val == 'def'){
            if(confirm("Stai avviando la stampa definitiva dell'importazione! Continuare?")){
                spinner.startSpinner();
                $.ajax({
                    url: "print_importazione_290.php",
                    data: {
                        id: id,
                        c: '<?php echo $c; ?>',
                        printType: $("#printType").val(),
                        type: val
                    },
                    dataType : 'json',
                    type: 'POST',
                    success: function (resp) {
                        spinner.closeSpinner();
                        //ShowAlert(resp.error,resp.msg);
                        //location.reload();
                        showFileOnModal(resp.path,"Stampa importazione Minuta di ruolo",resp.path.split('.').pop(),"<?=STAMPE_WEB?>/importazioni_290.php?&p=&c=<?=$c?>&a=<?=$a?>&error="+resp.error+"&msg="+resp.msg);
                        //location.assign("<?=STAMPE_WEB?>/importazioni_290.php?&p=&c=<?=$c?>&a=<?=$a?>&error="+resp.error+"&msg="+resp.msg);
                    },
                    error:function(resp){
                        spinner.closeSpinner();
                        //console.log(resp.responseText);
                        ShowAlert(1,"Si è verificato un errore!");
                    }
                });
            }
        }
        else {
            spinner.startSpinner();
            $.ajax({
                url: "print_importazione_290.php",
                data: {
                    id: id,
                    c: '<?php echo $c; ?>',
                    printType: $("#printType").val(),
                    type: val
                },
                dataType : 'json',
                type: 'POST',
                success: function (resp) {
                    spinner.closeSpinner();
                    ShowAlert(resp.error,resp.msg);
                    if(resp.error == '0')
                        showFileOnModal(resp.path,"Stampa importazione Minuta di ruolo",resp.path.split('.').pop());
                },
                error:function(resp)
                {
                    spinner.closeSpinner();
                    //console.log(resp.responseText);
                    ShowAlert(1,"Si è verificato un errore!");
                }
            });
        }
    }
</script>