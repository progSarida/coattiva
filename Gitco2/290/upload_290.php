<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(CLS . "/cls_290.php");
include_once(CLS . "/cls_file.php");
include_once(CLS . "/cls_html.php");
include_once(INC . "/header.php");
include_once(INC . "/menu.php");
/** GV - 07/04/2022 - START  
 *  $query = "SELECT * FROM imports";
    if($_SESSION['aut_tipo']>1)
        $query.= " WHERE CC='".$c."'";
    $query.= " ORDER BY Upload_Datetime DESC";
 */
$nrRecordPerPag = 25;
$indirizzo = $_SERVER['REQUEST_URI'];
if (!isset($_GET['nrPag'])) {
    unset($_SESSION['RISULTATI']);
    unset($_SESSION['FILTRI']);
    $nrPag = 1;
    $query = "SELECT * FROM imports WHERE 1 = 1 ";

    if ($_SESSION['aut_tipo'] > 1) {
        $query .= " AND CC='" . $c . "'";
    } else {
        if (isset($_POST['ente']))
        {
            $comune = $_POST['ente'];
            if ($comune !== "")
                $query .= " AND CC= '" . $comune . "' ";
        }
    }
    if (isset($_POST['inputFile'])) {
        $inputFile = trim($_POST['inputFile']);
        if ($inputFile !== "")
            $query .= " AND Filename LIKE '%" . $inputFile . "%' ";
    }
    if (isset($_POST['status'])) {
        $stato = $_POST['status'];
        if ($stato !== "")
            $query .= " AND Import_Status_Id= " . $stato . " ";
    }
    if (isset($_POST['user'])) {
        $utente = $_POST['user'];
        if ($utente !== "")
            $query .= " AND ( Upload_User_Id= " . $utente . " )";
    }
    if (isset($_POST['importUser'])) {
        $utenteImp = $_POST['importUser'];
        if ($utenteImp !== "")
            $query .= " AND ( Import_User_Id= " . $utenteImp. " )";
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

/** GV - 11/04/2022 - END */
/** GV - 08/04/2022 - START  */ 
$query = "SELECT * FROM enti_gestiti ORDER by Denominazione ASC";
$e_entiGestiti = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");

if(!isset($_SESSION['FILTRI']['ente']))
    $_SESSION['FILTRI']['ente'] = null;
$a_selection = array("value" => "CC", "firstOpt" => 0, "selected" => $_SESSION['FILTRI']['ente'], "text" => array("[Denominazione]", " - ", "[CC]"));
$optEnte = $cls_html->getOptions($e_entiGestiti, $a_selection);

/** GV - 08/04/2022 - END */
$cls_290 = new cls_290(array("CC" => $c));
$cls_file = new cls_file();
$dirPath = SUPER_WEB_ROOT . "/archivio/Modelli";
$xlsFile = $dirPath . "/MODELLO_COATTIVA.xlsx";
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>
    //F5
    switchMenuImg("F5");
    F5_button = function() {
        /* GV - 07/04/2022 - START 
        location.href = "upload_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        */
        <?php
        $url =  "upload_290.php?c=".$c."&a=".$a;
        if (isset($_POST["subFilter"])) {
            $url = $indirizzo;
        }
        ?>
        location.href = "<?php echo $url; ?>";
        /* GV - 07/04/2022 - END */        
    }

    var xlsFile = "<?php echo $xlsFile; ?>";

    function scaricaModello() {
        //window.open(xlsFile);
        showFileOnModal(xlsFile,"Modello tracciato 290",xlsFile.split('.').pop());
    }
</script>
<!-- GV - 06/04/2022 - START 
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" >-->
<!-- GV - 06/04/2022 - END   -->
    
<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font22 under_decor">Upload Tracciati 290</span>
    </div>
</div>
<form id=form_290 name=form_290 method="post" action="upload_290_exe.php" enctype="multipart/form-data">
    <input type="hidden" name="c" value="<?php echo $c ?>">
    <input type="hidden" name="a" value="<?php echo $a ?>">

    <div class="row-fluid gitco-container">
        <div class="col-lg-12 RowLabel text-center PresaVisione"></div>
        <div class="col-lg-12 RowInput RowInputHeight3 PresaVisione" style="font-size: 1.7rem;">
            La procedura di importazione del Tracciato 290 prenderà in considerazione la Data di Fornitura del ruolo in caso di assenza della Data di Decorrenza Interessi.
        </div>
        <div class="col-lg-12 RowInput RowInputHeight3 PresaVisione" style="font-size: 1.7rem;">
            Questo dato è necessario come base di calcolo per l'eventuale aggiunta di nuovi interessi.
        </div>
        <div class="col-lg-12 RowLabel text-center">
            CONFERMA PRESA VISIONE
            <input type="checkbox" id="checkBoxInteressi" value="1">
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-lg-2 RowLabel RowLabelHeight5">
            Tracciati 290
        </div>
        <div class="col-lg-8 RowInput RowInputHeight5" style="height:5rem;">
        <!-- GV - 19/04/2022 - START -->
            <div class="mb-3">
                <input name="file[]" id=file type="file" multiple value="Carica files" class="form-control form-control-lg" style="padding:6px; margin-top:9px; " />
            </div>
            <!-- GV - 19/04/2022 - START -->
        </div>
        <div class="col-lg-2 RowInput RowInputBtnHeight5 text-center">
            <input type="button" id="submitBtn" class="btn btn-gitco" style="width: 100%;  background-color: #356bc1; color:white; font-weight: bold;" value="CARICA">
        </div>

    </div>
</form>

<?php

$classFilterForm = "hidden";
if (isset($_SESSION['FILTRI']["subFilter"])) {
    $classFilterForm = "";
}

?>
<div class="clean_row HSpace4"  style='margin-top:60px;'></div>
<div class="container-fluid">
<div id="contForm" class="col-lg-12 <?php echo $classFilterForm; ?> " style="padding:5px">
<form id="filterForm" action="<?php echo $indirizzo; ?>" method="post">
			<fieldset style="border: 1px solid #ddd !important; margin: 0; padding: 10px; position: relative; border-radius:6px; background-color:#d1d8ee; padding-left:10px!important;">
                <legend style="font-size:14px; font-weight:bold; margin-bottom: 0px; width: 35%; border: 1px solid #ddd;"> Filtri:</legend>
                <div class="row align-items-center justify-content-center"" style="margin-left:40px;">
                    <div class="col-md-3">
                        <div class="form-group">
                            <select class="form-select" id="ente" name="ente" style="height:35px; width:75%;">
                                "<option value=''>Seleziona ente </option>
                                <?= $optEnte; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select class="form-select" id="status" name="status" style="height:35px; width:75%;">
                                <option value="">Seleziona lo stato</option>
                                <?php echo $optStatus; ?>+
                            </select>
                        </div>
                    </div>
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
                    <div class="col-md-11">
                        <div class="form-group">
<?php
                            $act = "";
                            if (isset($_SESSION['FILTRI']['inputFile']))
                                $act = $_SESSION['FILTRI']['inputFile'];
?>
                            <input type="input" class="form-control" id="inputFile" name="inputFile" placeholder="Compila con il nome del File"  value="<?php echo $act; ?>">
                        </div>
                    </div>
                </div>
			    <button id="subFilter" name="subFilter" type="submit" class="btn btn-primary pull-right">Filtra</button>
		    </fieldset>
	    </form>
</div>
</div>
<div class="clean_row HSpace4"></div>

<div class="row-fluid gitco-container">
    <div class="clean_row HSpace4"></div>
    <div class="col-lg-2 RowLabel RowLabelHeight4">
      <!-- GV - 06/04/2022 - START   -->
      <button id="btnFilter" type="button" class="btn btn-warning" onclick = "openFilter()"><i class="fa fa-filter" ></i>Filtri</button>
        <!-- GV - 06/04/2022 - END   -->
    </div>
    <div class="col-lg-8 RowLabel RowLabelHeight4 text-center">
        IMPORTAZIONI
    </div>
    <div class="col-lg-2  RowLabel RowLabelHeight4 text-right">
        <a href="#" onMouseover="title='Modello principale'" onclick="scaricaModello();" style="text-decoration: none;">
            <img src="<?= IMMAGINIWEB; ?>/icon-excel.png" width=30px height=30px border="0">
        </a>
    </div>
    <div class="clean_row HSpace1"></div>
    <?= $cls_290->getHtmlImportsList($a_imports, $a_usersAdmin, $a_importStatus, $a_importTypes); ?>

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
    $('#submitBtn').click(
        function() {
            if (!$("#checkBoxInteressi").is(":checked")) {
                alert("Confermare la presa visione prima di procedere con il caricamento del file.");
                return false;
            } else if ($("#file").val() == "") {
                alert('Nessun file caricato!');
                return false;
            } else {
                $('#form_290').submit();
            }

        }
    );

    $('#checkBoxInteressi').on('click', function() {
        if ($("#checkBoxInteressi").is(":checked"))
            $('.PresaVisione').hide();
        else
            $('.PresaVisione').show();
    });

    $('.290_details').on('click', function() {
        // location.href = "https://www.w3schools.com";
        location.href = "<?= WEB_ROOT ?>/290/mgmt_290.php?c=<?= $c ?>&a=<?= $a ?>&Import_Id=" + $(this).attr('id');
    });

    // GV - 06/04/2022 - START
    $("#btnFilter").on('click', function() {
            $("#contForm").toggleClass('hidden');
            $('#contForm').find(':input').each(function() {
                $(this).val('');
            });
    });
    // GV - 06/04/2022 - END
</script>

<?php include(INC . "/footer.php"); ?>