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


if (strtolower($_SESSION['username']) == "mirkop" || strtolower($_SESSION['username']) == "nicolo" || strtolower($_SESSION['username']) == "robertop")
    $authFlag = 1;
else
    $authFlag = 0;

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_params = new cls_parameters();
$elab_id =  $cls_help->getVar('el');
$query = "SELECT E.*, EG.Denominazione AS Denominazione_Ente, ES.Name AS Elaboration_Status, ";
$query.= "DT.Description AS DocumentType, DT.TitleDescription AS DocumentsType, DT.FolderName, DT.PrefixName ";
$query.= "FROM elaborations E ";
$query.= "JOIN elaboration_status ES ON ES.Id = E.Elaboration_Status_Id ";
$query.= "JOIN enti_gestiti EG ON EG.CC = E.CC ";
$query.= "JOIN document_type DT ON DT.Id = E.Document_Type_Id ";
$query.= "WHERE E.Id=".$elab_id;
$a_elab = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

if((int)$a_elab['Elaboration_Status_Id']>=4){
    $query = "SELECT E.*, TT.Name AS Tipo_Riscossione, PR.Name AS Printer, PT.Description AS PrintType, ";
    $query.= "F.FileName AS FlowFileName, F.UploadDate AS FlowUploadDate, F.CreationDate AS FlowCreationDate ";
    $query.= "FROM elaboration_lists E ";
    $query.= "JOIN tax_type TT ON TT.Id = E.TaxTypeId ";
    $query.= "JOIN printer PR ON PR.Id = E.PrinterId ";
    $query.= "JOIN print_type PT ON PT.Id = E.PrintTypeId ";
    $query.= "LEFT JOIN flows F ON F.Id = E.FlowId ";
    $query.= "WHERE E.Elaboration_Id=".$elab_id;
    $a_elab_lists = $cls_db->getResults($cls_db->ExecuteQuery($query));
}

$act_pdf = ATTI_WEB."/".$a_elab['CC']."/".$a_elab['FolderName']."/STAMPE DEFINITIVE/".$a_elab['PrefixName']."_".$a_elab['CC'];
$pec_receipt_pdf = EMAIL_WEB."/".$a_elab['CC']."/PEC/PEC_".$a_elab['PrefixName']."_".$a_elab['CC'];
$act_flow_root = ATTI."/".$a_elab['CC']."/".$a_elab['FolderName']."/FLUSSI";
$act_flow = ATTI_WEB."/".$a_elab['CC']."/".$a_elab['FolderName']."/FLUSSI";
$actionSignedFiles = WEB_ROOT."/elaborazioni/ajax/upload_signed_files.php";

$cls_file = new cls_file();
$a_files = $cls_file->getFilesFromPath(SIGNED_FILES);
$html_signedFiles = "";
foreach($a_files as $_file){
    if(strpos($_file['fileName'],"."))
        $html_signedFiles.= '<tr style="width:800px;"><td style="width:800px;padding: 5px;" class="text-center"><input type=radio name="signedZip" value="'.$_file['file'].'"> '.$_file['fileName'].'</td></tr>';
}

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
    var act_file_path = "<?= $act_pdf; ?>";
    var pec_file_path = "<?= $pec_receipt_pdf; ?>";
    var elab_cc = "<?= $a_elab['CC']; ?>";
    var web_root = "<?php echo  WEB_ROOT ?>";
    var web_datatable = "<?php echo  DATATABLE ?>";
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
    

<div class="text_center">
    <span class="titolo" style="font-size:large"><?= ucfirst($a_elab['Description']); ?></span><br>
    <span style="font-weight: bold;">Elaborazione <?= $a_elab['DocumentsType']; ?></span>
    <span style="font-weight: bold;">di <?= $a_elab['Denominazione_Ente']; ?> (<?= $a_elab['CC']; ?>)</span><br>    
    <span style="font-weight: bold;">Status Elaborazione:</span> <span class="titoletto"><?= $a_elab['Elaboration_Status_Id']; ?> - <?= $a_elab['Elaboration_Status']; ?></span>
    <br><br>
</div>

<?php
switch($a_elab['Elaboration_Status_Id']){
    case 1:
        ?>

    <script>
        switchMenuImg("F3");
        F3_button = function() {
            if (submit_buttons('Elabora')) {
                window.open('elab_acts.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$a_elab['Id'];?>&cod_cat=<?=$a_elab['CC'];?>&tipo_atto=<?=$a_elab['Document_Type_Id'];?>', 'elabora', 'width=1500,height=500,top=70,left=70,scrollbars=yes,menubar=no')
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
    <script src="<?= JS ?>/elabStatus1.js"></script>

<?php
        break;

        case 2:

            $a_printer = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM printer"));
            $a_print_type = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM print_type"));
            $a_tipo_ufficiale = array("diretta", "riscossione", "giudiziario", "procedimento");

            ?>
    <script>
        var callPec = 0;
        var a_printer = <?= json_encode($a_printer);?>;
        var a_print_type = <?= json_encode($a_print_type);?>;
        var a_tipo_ufficiale = <?= json_encode($a_tipo_ufficiale);?>;

        function inipecLink(){
            if(callPec==1)
                location.href = "<?= WEB_ROOT ?>/elaborazioni/prc_inipec.php?c=<?= $c ?>&a=<?= $a ?>&cc_el=<?= $a_elab['CC'] ?>&codcat=<?= $a_elab['CC'] ?>&el=<?= $a_elab['Id'] ?>&tipoatto=<?= $a_elab['Document_Type_Id'] ?>";
            else
                alert("Le PEC sono aggiornate!");
        }

        switchMenuImg("F3");
        F3_button = function() {
            if(callPec==0){
                if (submit_buttons('Elabora')) {
                    window.open('elab_crono_assignment.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$a_elab['Id'];?>&cod_cat=<?=$a_elab['CC'];?>&tipo_atto=<?=$a_elab['Document_Type_Id'];?>', 'elabora', 'width=1500,height=500,top=70,left=70,scrollbars=yes,menubar=no')
                }
            }
            else
                alert("Effettuare procedura Inipec prima di continuare!");
        }
    </script>
    <div style="padding: 0 30px 20px 30px;">
        <table id="dt_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Partita</th>
                    <th>Tributo</th>
                    <th>Info Cartella</th>
                    <th>Stampatore</th>
                    <th>Spedizione</th>
                    <th>Notifica</th>
                    <th>PEC</th>
                </tr>
            </thead>
        </table>
    </div>
    <script src="<?= JS ?>/elabStatus2.js"></script>
    
    <?php
            break;

        default:

        ?>

        <div class="modal fade" id="act-pdf" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="margin-left: 250px; text-align: center;">
                <div class="modal-body">
                    <iframe id="pdf-frame" src="" style="width:1400px; height:700px;"></iframe>
                </div>
            </div>
        </div>

        <?php


        $cls_params = new cls_params();
        $a_params = array(
            "authority" => $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM ufficio_giudiziario WHERE CC='".$a_elab['CC']."'"), "array", "Tipo"),
            "general" => $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM parametri_generali WHERE CC='".$a_elab['CC']."'"), "array", "Tipo_Riscossione"),
            "payment" => $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM parametri_pagamento WHERE CC='".$a_elab['CC']."'"), "array", "Tipo_Riscossione"),
            "responsible" => $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM parametri_responsabili WHERE CC='".$a_elab['CC']."'"), "array", "Tipo_Riscossione"),
            "appeal" => $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM parametri_ricorso WHERE CC='".$a_elab['CC']."'")),
            "annual" => $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM parametri_annuali WHERE CC='".$a_elab['CC']."' AND Anno=".date('Y'))),
            "interests" => $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM interessi_tributi WHERE CC='".$a_elab['CC']."' AND Data_Inizio is not null AND Data_Fine is null")),
            "text" => $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM text_parameters WHERE CC='".$a_elab['CC']."' AND Form_Type_ID='".$a_elab['Document_Type_Id']."'")),
            "subtext" => $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM subtext_parameters WHERE CC='".$a_elab['CC']."' AND Form_Type_ID='".$a_elab['Document_Type_Id']."'")),
            "subtext_general" => $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM subtext_parameters WHERE CC='*****' AND Form_Type_ID='".$a_elab['Document_Type_Id']."'"))
        );

        $html = "";
        foreach($a_elab_lists as $key=>$a_elab_list){

            $cls_params->checkParams($a_params,$a_elab_list['Tipo_Riscossione']);
            $htmlParams = "";
            $disabled = "";
            if(!empty($cls_params->a_checks['negative'])){
                $htmlParams = '
                        <button id="msg_err_button_'.$a_elab_list['ID'].'" class="btn btn-danger" data-toggle="modal" 
                        data-target="#errModal_'.$a_elab_list['ID'].'">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        </button>
                    ';
                    $disabled = "disabled";    
            }

            switch($a_elab_list['Elaboration_Status_Id']){

                case 4:
    
                    if(empty($a_elab_list["PrintDate"])){
                        if ($a_elab_list["TempFlag"] == 0)
                            $validationDisabled = "disabled";
                        else
                            $validationDisabled = "";

                        if ($a_elab_list["PrintFlag"] == 0){
                            $validationCheck = "";
                            $printTitle = "PROVVISORIA";
                        }                    
                        else{
                            $validationCheck = "checked";
                            $printTitle = "DEFINITIVA";
                        }


                        $html = '<tr>
                            <th colspan="5">
                                '.$htmlParams.'
                                <button type="button" id="press_button_'.$a_elab_list['ID'].'" 
                                    class="btn btn-primary" '.$disabled.'
                                    onclick="printButton('.$a_elab_list['ID'].')">
                                    <i class="fa fa-print" style="margin-right: 10px;"></i>'.$printTitle.'
                                </button>
                                &nbsp;&nbsp;
                                <input id="chk_press_'.$a_elab_list['ID'].'"  
                                type="checkbox" '.$validationDisabled.' '.$validationCheck.' '.$disabled.'
                                value="1" onclick="checkValidation('.$a_elab_list['ID'].')">
                                Validazione
                            </th>
                        </tr>';
                        
                    }
                    else{
                        $html = '<tr>
                                <th colspan="5">
                                    '.$htmlParams.'
                                    <span style="color:red">STAMPA DEFINITIVA '.$cls_help->toItalianDate($a_elab_list["PrintDate"]).'</span>
                                </th>
                            </tr>';
                    }
                    

                    
                    break;

                case 5:

                    if(empty($a_elab_list["FlowId"])){

                        $html = '<tr>
                            <th colspan="5">
                                '.$htmlParams.'
                                <button type="button" id="press_button_'.$a_elab_list['ID'].'" class="btn btn-primary"
                                onclick="flowCreation('.$a_elab_list['ID'].');" '.$disabled.'>
                                    <i class="fa-solid fa-list-ul" style="margin-right: 10px;"></i>CREA FLUSSO
                                </button>
                            </th>
                        </tr>';
                        
                    }
                    else{
                        $flowDownload = "";
                        if(!empty($a_elab_list['FlowFileName']) && is_file($act_flow_root.'/'.$a_elab_list['FlowFileName']))
                            $flowDownload = '<a class="btn btn-md pull-right" title="Download flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'"
                                                style="background-color: white; border: solid 1px #2863c1;" href="'. $act_flow .'/'. $a_elab_list['FlowFileName'].'" download>
                                                <i class="fa-solid fa-download fa-lg" style="color: #2863c1;" aria-hidden="true"></i>
                                            </a>';
                            
                        $uploadFile = '<a type="button" title="Upload flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'" '.$disabled.'
                            style="background-color: white; border: solid 1px red; margin-right: 2px;" id="upload_flusso_'.$a_elab_list['ID'].'" class="btn" onclick="closeFlow('.$a_elab_list['ID'].')">
                            <i class="fa-solid fa-file-upload fa-lg" style="color: red;" aria-hidden="true"></i>
                        </a>';   

                        $html = '<tr>
                                <th colspan="5">
                                    '.$htmlParams.'
                                    '.$uploadFile.'
                                    <span style="color:#2863c1; margin-right:2px;">Flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].' creato il '.$cls_help->toItalianDate($a_elab_list["FlowDate"]).'</span>
                                    '.$flowDownload.'
                                </th>
                            </tr>';
                    }

                    break;

                case 6:

                    $htmlOperations = "";
                    $flowDownload = "";
                    $flowHeaderColor = "color:#2863c1;";
                    $flowHeader = '<span style="'.$flowHeaderColor.' margin-right:2px;">
                    Flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].' -
                    Creazione: '.$cls_help->toItalianDate($a_elab_list["FlowDate"]).'</span>';

                    if(!empty($a_elab_list['FlowFileName']) && is_file($act_flow_root.'/'.$a_elab_list['FlowFileName']))
                        $flowDownload = '<a class="btn btn-md pull-right" title="Download flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'"
                                            style="background-color: white; border: solid 1px #2863c1;" href="'. $act_flow .'/'. $a_elab_list['FlowFileName'].'" download>
                                            <i class="fa-solid fa-download fa-lg" style="color: #2863c1;" aria-hidden="true"></i>
                                        </a>';

                    if($a_elab_list['Elaboration_Status_Id']<7){
                        if($a_elab_list['PrintTypeId']==4){//SPEDIZIONE PEC
                             
                            if($a_elab_list['PecReceiptsFlag']==1){
                                $htmlOperations = '<a type="button" title="Chiusura flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'" '.$disabled.'
                                style="background-color: white; border: solid 1px red; margin-right: 2px;" id="upload_flusso_'.$a_elab_list['ID'].'" class="btn" 
                                onclick="closeFlow('.$a_elab_list['ID'].')">
                                <i class="fa-solid fa-file-upload fa-lg" style="color: red;" aria-hidden="true"></i>
                                </a>';
                            }
                            else if($a_elab_list['SendingPecFlag']==1){
                                $query = "SELECT COUNT(E.Id) AS MissingReceiptNumber
                                FROM emails E JOIN atto A ON A.Email_Id=E.Id 
                                WHERE A.Elaboration_List_Id=".$a_elab_list['ID']." AND E.Delivery_Receipt is null";
                                $a_count = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

                                $htmlOperations = '<a type="button" title="Controllo ricevute PEC ('.$a_count['MissingReceiptNumber'].' da controllare)" '.$disabled.'
                                style="background-color: white; border: solid 1px #2863c1; margin-right: 2px;" id="check_pec_'.$a_elab_list['ID'].'" class="btn" 
                                onclick="checkPec('.$a_elab_list['ID'].')">
                                <i class="fa-solid fa-inbox fa-lg" style="color: #2863c1;" aria-hidden="true"></i>
                                </a>';
                            }
                            else if($a_elab_list['SignedPdfFlag']==1){
                                $htmlOperations = '<a type="button" title="Spedizione PEC pdf firmati" '.$disabled.'
                                style="background-color: white; border: solid 1px red; margin-right: 2px;" id="send_pec_'.$a_elab_list['ID'].'" class="btn" 
                                onclick="sendPec('.$a_elab_list['ID'].')">
                                <i class="fa-solid fa-envelope fa-lg" style="color: red;" aria-hidden="true"></i>
                                </a>'; 
                            }
                            else{
                                $htmlOperations = '<button class="btn" style="background-color: white; border: solid 1px #2863c1;" 
                                data-toggle="modal" '.$disabled.'
                                data-target="#uploadModal_'.$a_elab_list['ID'].'">
                                    <i class="fa-solid fa-file-import fa-lg" style="color: #2863c1;" aria-hidden="true"></i>
                                </button>';
                            }
                        }
                        else{
                            $htmlOperations = '<a type="button" title="Upload FTP flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'" '.$disabled.'
                            style="background-color: white; border: solid 1px red; margin-right: 2px;" id="upload_flusso_'.$a_elab_list['ID'].'" class="btn" 
                            onclick="closeFlow('.$a_elab_list['ID'].')">
                            <i class="fa-solid fa-file-upload fa-lg" style="color: red;" aria-hidden="true"></i>
                            </a>';  
                        }
                    }
                    else{
                        $flowHeaderColor = "color:darkgreen;";
                        $flowHeader = '<span style="'.$flowHeaderColor.' margin-right:2px;">
                        Flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'
                        - Upload: '.$cls_help->toItalianDate($a_elab_list["FlowUploadDate"]).'
                        </span>';
                    }

                    
                    $html = '<tr>
                            <th colspan="5">
                                '.$flowDownload.'
                                '.$htmlParams.'
                                '.$htmlOperations.'
                                '.$flowHeader.'
                                
                            </th>
                        </tr>';
                    

                    break;

                case 7:

                    $htmlOperations = "";
                    $flowDownload = "";
                    $flowHeaderColor = "color:#2863c1;";
                    $flowHeader = '<span style="'.$flowHeaderColor.' margin-right:2px;">
                    Flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].' -
                    Creazione: '.$cls_help->toItalianDate($a_elab_list["FlowDate"]).'</span>';

                    if(!empty($a_elab_list['FlowFileName']) && is_file($act_flow_root.'/'.$a_elab_list['FlowFileName']))
                        $flowDownload = '<a class="btn btn-md pull-right" title="Download flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'"
                                            style="background-color: white; border: solid 1px #2863c1;" href="'. $act_flow .'/'. $a_elab_list['FlowFileName'].'" download>
                                            <i class="fa-solid fa-download fa-lg" style="color: #2863c1;" aria-hidden="true"></i>
                                        </a>';
                                        
                    $flowHeaderColor = "color:darkgreen;";
                        $flowHeader = '<span style="'.$flowHeaderColor.' margin-right:2px;">
                        Flusso n. '.$a_elab_list["FlowNumber"].'/'.$a_elab_list["FlowYear"].'
                        - Upload: '.$cls_help->toItalianDate($a_elab_list["FlowUploadDate"]).'
                        </span>';

                    
                    $html = '<tr>
                            <th colspan="5">
                                '.$flowDownload.'
                                '.$htmlParams.'
                                '.$htmlOperations.'
                                '.$flowHeader.'
                                
                            </th>
                        </tr>';
                    

                    break;
            }
            

                    ?>

    <script>
        elab_list_id = <?= $a_elab_list['ID']; ?>;
    </script>
    <div id="uploadModal_<?=$a_elab_list['ID'];?>" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <form action="<?= $actionSignedFiles; ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-center">CARICA FILE FIRMATI DIGITALMENTE</h4>
                    <h5 class="modal-title text-center">Selezionare il file zip con i file pdf firmati</h4>
                    <hr>
                    <h5 class="modal-title">
                        Se il file zip non è presente eseguire le seguenti operazioni prima di procedere:<br>
                        1) Scaricare il file zip con i pdf dal link della lista in alto a destra <i class="fa-solid fa-download"></i><br>
                        2) Estrarre i file pdf e firmarli (i file firmati dovranno terminare con "_signed.pdf")<br>
                        3) Creare un nuovo file zip con i file firmati<br>
                        4) Caricare il file zip tramite protocollo FTP nella cartella designata
                    </h5>
                </div>
                <div class="modal-body ">
                        <input type=hidden name="c" value="<?=$c;?>">
                        <input type=hidden name="a" value="<?=$a;?>">
                        <input type=hidden name="Elaboration_Id" value="<?=$a_elab['Id'];?>">
                        <input type=hidden name="Elaboration_List_Id" id="Elaboration_List_Id" value="<?=$a_elab_list['ID'];?>">
                        <table>
                            <?= $html_signedFiles; ?>
                            <tr style="width:800px;">
                                <td style="width:800px;padding-top: 10px;" class="text-center"></td>
                            </tr>

                        </table>
                    
                </div>
                <div class="modal-footer">
                    <input type="submit" value="Carica" class="btn btn-primary mr-auto" name="submit">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    <div id="errModal_<?=$a_elab_list['ID'];?>" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-center">PARAMETRI ASSENTI</h4>
                </div>
                <div class="modal-body">
                    <?= $cls_params->setHtmlChecks(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                </div>
            </div>

        </div>
    </div>
    <div style="padding: 0 30px 20px 30px;">
        <table id="dt_table_<?=$a_elab_list['ID'];?>" data-id="<?= $a_elab_list['ID']; ?>" class="table table-striped table-bordered display dt_table" cellspacing="0" width="100%">
            <thead>
                <?= $html ?>
                <tr>
                    <th style="text-align: left;" colspan="5">
                        <div class="col-md-1">Riscossione:</div>
                        <div class="col-md-2 titolo"><?= strtoupper($a_elab_list["Tipo_Riscossione"]); ?></div>
                        <div class="col-md-1">Stampatore:</div>
                        <div class="col-md-2 titolo"><?= strtoupper($a_elab_list["Printer"]); ?></div>
                        <div class="col-md-1">Spedizione:</div>
                        <div class="col-md-2 titolo"><?= strtoupper($a_elab_list["PrintType"]); ?></div>
                        <div class="col-md-1">Notifica:</div>
                        <div class="col-md-2 titolo"><?= strtoupper($a_elab_list["NotificationType"]); ?></div>
                    </th>
                </tr>
                
                <tr>
                    <th>Partita</th>
                    <th>Cronologico</th>
                    <th>Anno</th>
                    <th>Info Cartella</th>
                    <th>PDF</th>
                </tr>
            </thead>
        </table>
    </div>
    <script src="<?= JS ?>/elabStatus4.js"></script>

<?php
                }

        break;
}
?>




