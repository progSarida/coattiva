<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include_once(INC . "/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_authority.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_params.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_zip.php";


if (strtolower($_SESSION['username']) == "mirkop" || strtolower($_SESSION['username']) == "michele" || strtolower($_SESSION['username']) == "robertop")
    $authFlag = 1;
else
    $authFlag = 1;// per tutti

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_params = new cls_parameters();
$procedure_id =  $cls_help->getVar('pr');
$tipo_partita = $cls_help->getVar('tipo_partita');
$tipo = $cls_help->getVar('tipo');
$c = $cls_help->getVar('c');

$denominazioneCC = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'"),"enti_gestiti");
$denominazioneCC = $denominazioneCC["Denominazione"];

$html="";
$query = "
    SELECT
    PR.Id,
    PR.CC,
    PR.Description,
    DT.TitleDescription AS DocumentsType,
    EG.Denominazione AS Denominazione_Ente,
    PS.Name as Procedure_Status,
    PS.Id as Procedure_Status_Id,
    PR.PecReceiptsFlag,
    PR.SendingPecFlag
    FROM
    procedures as PR
    JOIN enti_gestiti EG ON EG.CC = PR.CC
    JOIN document_type as DT on DT.Id = PR.Document_Type_Id
    JOIN procedure_status as PS on PS.Id = PR.Procedure_Status_Id
    Where PR.ID = $procedure_id
";

$a_procedure = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

?>

<link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css"/>
<script type="text/javascript" src="<?= DATATABLE ?>/datatables.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    var authFlag = <?= $authFlag; ?>;
    var procedure_id = <?= $procedure_id; ?>;
    var c = "<?= $c; ?>";
    var a = "<?= $a; ?>";
    var elab_cc = "<?= $a_procedure['CC']; ?>";
    var web_root = "<?php echo  WEB_ROOT ?>";
    var web_datatable = "<?php echo  DATATABLE ?>";
    var web_dteditor = "<?php echo  ELAB_DTEDITOR_WEB ?>";
    var act_file_path = "<?= STRAGIUDIZIALEWEB . "/" . $procedure_id ?>";
    var pec_file_path = "<?= EMAIL_WEB."/Stragiudiziali/" . $procedure_id ?>";
    var tipo =  "<?= $tipo; ?>";
    
    var cc = "<?= $c; ?>";
    var callPec = 1;
    var denominazioneCC = "<?= $denominazioneCC; ?>";
    function openPdf(filePath){
        $('#pdf-frame').attr('src',filePath);

    }
    function openExcel(filePath){
        window.open(filePath);

    }
    switchMenuImg("F11");
    F11_button = function(){
        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Elaborazione_Stragiudiziali.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Stragiudiziali</b>");
        $("#helpModal").modal('show');
    }
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
           
            $("#caricamento_spiners").show();
        }

        function closeSpiner(){
            //alert("close");
            $("#caricamento_spiners").hide();
        }
    </script>
    

<div class="text_center">
    <span class="titolo" style="font-size:large"><?= ucfirst($a_procedure['Description']); ?></span><br>
    <span style="font-weight: bold;">Elaborazione <?= $a_procedure['DocumentsType']; ?></span>
    <span style="font-weight: bold;">di <?= $a_procedure['Denominazione_Ente']; ?> (<?= $a_procedure['CC']; ?>)</span><br>    
    <span style="font-weight: bold;">Status Elaborazione:</span> <span class="titoletto"><?= $a_procedure['Procedure_Status']; ?></span>
    <br><br>
</div>

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

</script>
<div class="back_spiners" id="caricamento_spiners">
    <div class="d-flex align-items-center text-info" style="width: 220px;position: fixed;top:45%;left:45%;text-align: center;">
        <div style="display: inline;"><img style="width: 100%; border-radius: 100px;opacity: 0.5;" src="<?= GIFWEB ?>/loading_3.gif"></div>
        <div id="text_spiners" style="display: inline;font-size: 18px;width:100%;text-align: center;font-weight: bold;">Loading...</div>
    </div>
 </div>
 
<?php
switch($a_procedure['Procedure_Status_Id']){
    case 1:
            //Query conrollo elaborabili
            $p=$a_procedure['Id'];
            $query_elab = "Select count(*) as Cont From partita_procedure_pvt where Procedure_Id = $p and Flag_Elaboration = 1";
            $a_resultQueryElab = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_elab));
            $cont = $a_resultQueryElab["Cont"];
        ?>

    <script>
        var elaborabili = <?=$cont?>;
        switchMenuImg("F3");
        F3_button = function() {
            if(elaborabili==0)
            {
                alert("Non ci sono partite elaborabili per questo comune");
                return;
            }
            if (submit_buttons('Elabora')) {
                
                location.href ='elab_acts_stragiudiziali.php?c=<?=$c;?>&a=<?=$a;?>&proc=<?=$a_procedure['Id'];?>&tipo=<?=$tipo; ?>&tipo_partita=<?=$tipo_partita; ?>';
            }
        }
    
    </script>

    <div style="padding: 0 30px 20px 30px;">
        <table id="dt_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Partita</th>
                    <th>Tributo</th>
                    <th>Info Cartella</th>
                    <th>Anomalia</th>
                    <th>Stato</th>        
                    <th>Check</th>  
                </tr>
            </thead>
        </table>
    </div>
    <script src="<?= ELAB_STRAGIUDIZIALI_JS ?>/elabStragiudizialiStatus1.js"></script>

<?php
        break;
        case 10: // stragiudiziali create
            $html = '<tr>
                        <th colspan="3">
                            <button type="button" id="press_button" 
                                class="btn btn-primary"
                                onclick="printButton('.$procedure_id.',\''.$tipo_partita.'\',\''.$c.'\')">
                                <i class="fa fa-print" style="margin-right: 10px;"></i>PROVVISORIA
                            </button>
                        </th>
                    </tr>';  
            ?>
            <script>
                    switchMenuImg("F3");
                    F3_button = function() {
                        if (submit_buttons('Elabora')) {
                            
                            location.href ='elab_creafile_stragiudiziali.php?c=<?=$c;?>&a=<?=$a;?>&proc=<?=$a_procedure['Id'];?>&tipo=<?=$tipo ?>&tipo_partita=<?=$tipo_partita ?>';
                        }
                    }
                

                    function printButton(procedure_id,tipo_partita,c) {
                        // location.href = '../ajax/ajax_stampa_provvisoria_stragiudiziali.php?procedure_id='+procedure_id+'&c='+c+'&tipo_partita='+tipo_partita+'&tipo='+tipo;
                        // return;
                        $.ajax({
                            onLoading: startSpiners(),
                            url: '../ajax/ajax_stampa_provvisoria_stragiudiziali.php',
                            type: 'POST',
                            data: {
                                'procedure_id': procedure_id,
                                'tipo_partita':tipo_partita,
                                'c':c,
                                'tipo':tipo
                            },
                            success: function(response) {
                                var response = JSON.parse(response);
                                if (response.esito == "OK") {
                                    window.name = "Stampa";
                                    window.open(response.filePdf, "_blank");
                                } else {
                                    console.log(response);
                                    swal({
                                        title: "ERROR!",
                                        text: response.message,
                                        icon: "danger",
                                        timer: 10000,
                                        buttons: false
                                    })
                                }
                            },
                            error: function(error) {
                                console.log(error)
                            },
                            complete: function () { // Set our complete callback, adding the .hidden class and hiding the spinner.
                                closeSpiner();
                            }
                        });
                       
                    }
                </script>

                <div style="padding: 0 30px 20px 30px;">
                    <table id="dt_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                        <thead>
                        <?= $html ?>
                            <tr>
                                <th>Denominazione</th>
                                <th>Toponimo</th>
                                <th>P.IVA</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <script src="<?= ELAB_STRAGIUDIZIALI_JS ?>/elabStragiudizialiStatus10.js"></script>
        <?php
                break;

        case 20 :case 30: case 40: // stampe create
            $disabled ="";
            
            if($a_procedure["PecReceiptsFlag"]==1)
            {
                $htmlOperations = ''; 	
            }else if($a_procedure['SendingPecFlag']==1){
                $query = "SELECT COUNT(E.Id) AS MissingReceiptNumber
                FROM emails E join stragiudiziali S on E.Id = S.Email_Id
                where S.Procedure_Id = $procedure_id AND E.Delivery_Receipt is null";
                $a_count = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
            
                $htmlOperations = '<a type="button" title="Controllo ricevute PEC ('.$a_count['MissingReceiptNumber'].' da controllare)" '.$disabled.'
                style="background-color: white; border: solid 1px #2863c1; margin-right: 2px;" id="check_pec_'.$procedure_id.'" class="btn" 
                onclick="checkPec('.$procedure_id.')">
                <i class="fa-solid fa-inbox fa-lg" style="color: #2863c1;" aria-hidden="true"></i>
                </a>';
            }else{
                $htmlOperations = '<a type="button" title="Spedizione PEC" 
                style="background-color: white; border: solid 1px red; margin-right: 2px;" id="send_pec_'.$procedure_id.'" class="btn" 
                onclick="sendPec('.$procedure_id.')">
                <i class="fa-solid fa-envelope fa-lg" style="color: red;" aria-hidden="true"></i>
                </a>'; 	
            }
            $html = '<tr>
                        <th colspan="6">'.$htmlOperations.' </th>
                    </tr>';
        ?>
            <script>
                    switchMenuImg("F3");
                    F3_button = function() {
                        if (submit_buttons('Elabora')) {
                            if (confirm("Vuoi spedire PEC ?"))
                                location.href ='pec_send_stragiudiziali.php?c=<?=$c;?>&a=<?=$a;?>&proc_id=<?=$a_procedure['Id'];?>&tipo=<?= $tipo; ?>&tipo_partita=<?= $tipo_partita; ?>';
                        }
                    }
                
                </script>
        
                <div style="padding: 0 30px 20px 30px;">
                    <table id="dt_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                        <thead>
                            <?= $html ?>
                            <tr>
                                <th>Denominazione</th>
                                <th>Toponimo</th>
                                <th>P.IVA</th>
                                <th>PEC</th>
                                <th>FILE</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <script src="<?= ELAB_STRAGIUDIZIALI_JS ?>/elabStragiudizialiStatus20.js"></script>
        <?php
                break;
        
        default:
        break;
} // end switch

?>
                <div class="modal fade" id="act-pdf" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document" style="margin-left: 250px; text-align: center;">
                        <div class="modal-body">
                            <iframe id="pdf-frame" src="" style="width:1400px; height:700px;"></iframe>
                        </div>
                    </div>
                </div>
<?php

        ?>




