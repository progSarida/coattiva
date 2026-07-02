<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include_once(INC . "/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_params.php";


if (strtolower($_SESSION['username']) == "mirkop" || strtolower($_SESSION['username']) == "robertop" || strtolower($_SESSION['username']) == "fabrizio")
    $authFlag = 1;
else
    $authFlag = 0;// per tutti

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_params = new cls_parameters();
$elab_id =  $cls_help->getVar('el');
$query = "SELECT E.*, EG.Denominazione AS Denominazione_Ente, ES.Name AS Elaboration_Status, ";
$query.= "DT.Description AS DocumentType,DT.ID as DocumentTypeId, DT.TitleDescription AS DocumentsType, DT.FolderName, DT.PrefixName ";
$query.= "FROM elaborations E ";
$query.= "JOIN elaboration_status ES ON ES.Id = E.Elaboration_Status_Id ";
$query.= "JOIN enti_gestiti EG ON EG.CC = E.CC ";
$query.= "JOIN document_type DT ON DT.Id = E.Document_Type_Id ";
$query.= "WHERE E.Id=".$elab_id;
$a_elab = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

?>

<link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css"/>
<script type="text/javascript" src="<?= DATATABLE ?>/datatables.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    var authFlag = <?= $authFlag; ?>;
    var elab_id = <?= $elab_id; ?>;
    var elab_list_id = null;
    var c = "<?= $c; ?>";
    var a = "<?= $a; ?>";
    var elab_cc = "<?= $a_elab['CC']; ?>";
    var web_root = "<?php echo  WEB_ROOT ?>";
    var web_datatable = "<?php echo  DATATABLE ?>";
    var web_dteditor = "<?php echo  ELAB_DTEDITOR_WEB ?>";
</script>
<style>
        .back_spiners {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            background: rgba(0,0,0,0.80);
            z-index: 10000;
        }
    </style>

    <script>
        function startSpiners(){
            //alert("show");
            //$("#caricamento_spiners").css("display","block");
            $("#caricamento_spiners").show();
        }

        function closeSpiner(){
            //alert("close");
            $("#caricamento_spiners").hide();
        }
    </script>
<div class="back_spiners" id="caricamento_spiners">
    <div class="d-flex align-items-center text-info" style="width: 220px;position: fixed;top:45%;left:45%;text-align: center;">
        <div style="display: inline;"><img style="width: 100%; border-radius: 100px;opacity: 0.5;" src="<?= GIFWEB ?>/loading_3.gif"></div>
        <div id="text_spiners" style="display: inline;font-size: 18px;width:100%;text-align: center;font-weight: bold;">Loading...</div>
    </div>
</div>

<div class="text_center">
    <span class="titolo" style="font-size:large"><?= ucfirst($a_elab['Description']); ?></span><br>
    <span style="font-weight: bold;">Elaborazione <?= $a_elab['DocumentsType']; ?></span>
    <span style="font-weight: bold;">di <?= $a_elab['Denominazione_Ente']; ?> (<?= $a_elab['CC']; ?>)</span><br>    
    <span style="font-weight: bold;">Status Elaborazione:</span> <span class="titoletto"><?= $a_elab['Elaboration_Status']; ?></span>
    <br><br>
</div>

<?php
switch($a_elab['Elaboration_Status_Id']){
    case 9999:
        ?>

    <div style="padding: 0 30px 20px 30px;">
        <table id="dt_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Partita</th>
                    <th>Tributo</th>
                    <th>Info Cartella</th>
                    <th>Stato</th>        
                    <th>Check</th>  
                </tr>
            </thead>
        </table>
    </div>
    <script src="<?= ELAB_PIGNORAMENTI_JS ?>/elabPignoStatusEstrazione.js"></script>
    <script src="<?= ELAB_PIGNORAMENTI_JS ?>/cancellazioneElaborazioneEstrazione.js"></script>    
<?php
        
                break;
        }
        ?>




