<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

$submenuPageNo = 5;

include(INC."/header.php");
include(INC."/menu.php");
include(INC."/submenu_partita.php");

include_once(CLS."/cls_registry.php");
include_once(CLS."/cls_appeal.php");
include_once(CLS."/cls_file.php");

$cls_file = new cls_file();
$appeal_id = $cls_help->getVar("Appeal_ID");

if($appeal_id==null)
    $appeal_id = 0;
$cls_appeal = new cls_appeal();
$result = $cls_db->getArrayLine($cls_db->SelectQuery($cls_appeal->getAppeal_query($appeal_id)));

if($result!=null) $a_appeal = $result;
else $a_appeal = array('');



$changeAuthority = $cls_help->getVar("changeAuthority");
$lastAppeal = $cls_help->getVar("lastAppeal");
if($lastAppeal==null)
    $lastAppeal = 0;
$a_lastAppeal = null;
//$countAppeal = count($a_appeal);
if($result==null){
    $a_lastAppeal = $cls_db->getArrayLine($cls_db->SelectQuery($cls_appeal->getAppeal_query($lastAppeal)));

    $a_appeal = $cls_db->getColumnsArray("appeal");
    $a_appeal['ID'] = $appeal_id;
    $a_appeal['Start_Date'] = date("d/m/Y");
    $a_appeal['Authority_Description'] = "";
    $a_appeal['Body_Part'] = 2;
    $a_appeal['Trespassers_Part'] = 1;

    $a_appealPart = array();
    $a_courtHearing = array();

    $a_proceedings[0] = $cls_db->getColumnsArray("appeal_proceedings_status");
    $a_proceedings[1] = $cls_db->getColumnsArray("appeal_proceedings_status");

    $a_lawyerBill[0] = $cls_db->getColumnsArray("appeal_lawyer_bill");
    $a_lawyerBill[1] = $cls_db->getColumnsArray("appeal_lawyer_bill");

    $a_appeal['Challenge_Description'] = "";

    if($a_lastAppeal!=null && $changeAuthority=="y"){
        $a_appeal['Type'] = $a_lastAppeal['Type'];
        $a_appeal['Amendment_Date'] = $cls_help->toItalianDate($a_lastAppeal['Amendment_Date']);
        $a_appeal['Notification_Date'] = $cls_help->toItalianDate($a_lastAppeal['Notification_Date']);
        $a_appeal['Registration_Date'] = $cls_help->toItalianDate($a_lastAppeal['Registration_Date']);
        $a_appeal['Dossier_Submission_Date'] = $cls_help->toItalianDate($a_lastAppeal['Dossier_Submission_Date']);
        $a_appeal['Trespassers_Part'] = $a_lastAppeal['Trespassers_Part'];
        $a_appeal['Trespassers_Lawyer'] = $a_lastAppeal['Trespassers_Lawyer'];
        $a_appeal['Trespassers_Lawyer_Bar'] = $a_lastAppeal['Trespassers_Lawyer_Bar'];
        $a_appeal['Body_Type'] = $a_lastAppeal['Body_Type'];
        $a_appeal['Body_Part'] = $a_lastAppeal['Body_Part'];
        $a_appeal['Body_Lawyer'] = $a_lastAppeal['Body_Lawyer'];
        $a_appeal['Body_Lawyer_Bar'] = $a_lastAppeal['Body_Lawyer_Bar'];
        switch($a_lastAppeal['Authority_Type']){
            case "giudice":         $a_lastAppeal['Authority_Type'] = "del Giudice di Pace"; break;
            case "tribunale":       $a_lastAppeal['Authority_Type'] = "del Tribunale";   break;
            case "comm_trib_prov":  $a_lastAppeal['Authority_Type'] = "della Commissione Tributaria Provinciale";  break;
            case "comm_trib_reg":   $a_lastAppeal['Authority_Type'] = "della Commissione Tributaria Regionale";    break;
            case "appello":         $a_lastAppeal['Authority_Type'] = "della Corte d'Appello"; break;
            case "cassazione":      $a_lastAppeal['Authority_Type'] = "della Corte di Cassazione"; break;
        }
        $a_appeal['Notes'] = "Rimessione nei termini del ricorso a causa di incompetenza ".$a_lastAppeal['Authority_Type']." di ".$a_lastAppeal['Authority_Place']." ".$a_lastAppeal['Judge'];
        $a_appealPart = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getAppealPart_query($lastAppeal)));
    }
}
else{
    $a_appealPart = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getAppealPart_query($appeal_id)));
    $a_courtHearing = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getCourtHearing_query($appeal_id)));
    $a_proceedings = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getProceedings_query($appeal_id)));
    $a_lawyerBill = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getLawyerBill_query($appeal_id)));

    $a_appeal['Start_Date'] = $cls_help->toItalianDate($a_appeal['Start_Date']);
    $a_appeal['End_Date'] = $cls_help->toItalianDate($a_appeal['End_Date']);
    switch($a_appeal['Authority_Type']){
        case "giudice":         $a_appeal['Authority_Type'] = "Giudice di Pace"; break;
        case "tribunale":       $a_appeal['Authority_Type'] = "Tribunale";   break;
        case "comm_trib_prov":  $a_appeal['Authority_Type'] = "Commissione Tributaria Provinciale";  break;
        case "comm_trib_reg":   $a_appeal['Authority_Type'] = "Commissione Tributaria Regionale";    break;
        case "appello":         $a_appeal['Authority_Type'] = "Corte d'Appello"; break;
        case "cassazione":      $a_appeal['Authority_Type'] = "Corte di Cassazione"; break;
    }
    $a_appeal['Authority_Description'] = $a_appeal['Authority_Type']." - ".$a_appeal['Authority_Place'];
    if($a_appeal['Authority_Section']!="")
        $a_appeal['Authority_Description'].= " sez. ".$a_appeal['Authority_Section'];

    $a_appeal['Amendment_Date'] = $cls_help->toItalianDate($a_appeal['Amendment_Date']);
    $a_appeal['Notification_Date'] = $cls_help->toItalianDate($a_appeal['Notification_Date']);
    $a_appeal['Registration_Date'] = $cls_help->toItalianDate($a_appeal['Registration_Date']);
    $a_appeal['Dossier_Submission_Date'] = $cls_help->toItalianDate($a_appeal['Dossier_Submission_Date']);
}

$actInput = "";
$partitaID = 0;

if($partita_ID!=null){

    $partitaID = $partita_ID;

    $query = "SELECT atto.ID, atto.Atto, atto.ID_Cronologico, atto.Anno_Cronologico, ricorso.Court_Level, ricorso.ID as Appeal_ID FROM atto ";
    $query.= "LEFT JOIN (SELECT Act_ID, MAX(ID) AS ID, MAX(Court_Level) AS Court_Level ";
    $query.= "FROM appeal GROUP BY Act_ID) as ricorso ON ricorso.Act_ID = atto.ID ";
    $query.= "WHERE Partita_ID=".$partita_ID;

    if($a_appeal['ID']>0){
        $query.= " AND atto.ID=".$a_appeal['Act_ID']." ORDER BY atto.ID DESC";
        $a_act = $cls_db->getArrayLine($cls_db->SelectQuery($query));
        $text = $a_act['Atto']." n. ".$a_act['ID_Cronologico']." del ".$a_act['Anno_Cronologico'];
        $actInput = "<input type=hidden name='Act_ID' value='".$a_appeal['Act_ID']."'><input readonly style='background-color: rgb(153, 204, 255); border: 2px solid black;' class='readonly text_left resize form-control' value='".$text."'>";
        $courtLevelInput = "<input type=hidden name='Court_Level' value='".$a_appeal['Court_Level']."'><span class='titolo'>".$a_appeal['Court_Level']."&deg;</span>";
    }
    else{
        $query.= " ORDER BY atto.ID DESC";
        $a_act = $cls_db->getResults($cls_db->SelectQuery($query));
        $a_selection = array("value"=>"ID","firstOpt"=>0,"selected"=>null,"text"=>array("[Atto]"," n. ","[ID_Cronologico]"," del ","[Anno_Cronologico]"));
        $opt_atti = $cls_html->getOptions($a_act,$a_selection);

        if($changeAuthority=="y")
            $courtLevel = $a_act[0]['Court_Level'];
        else
            $courtLevel = $a_act[0]['Court_Level']+1;

        if($a_appeal==null && $courtLevel>1 && $changeAuthority!="y"){
            $a_lastProceedings = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getProceedings_query($lastAppeal)));
            if($a_lastProceedings[1]['Number']>0){
                $a_appeal['Notes'] = "Sentenza n. ".$a_lastProceedings[1]['Number']." del ".$cls_help->toItalianDate($a_lastProceedings[1]['Date']);
                if($cls_help->toItalianDate($a_lastProceedings[1]['Sentence_Request_Date'])!=null)
                    $a_appeal['Notes'].= " richiesta il ".$cls_help->toItalianDate($a_lastProceedings[1]['Sentence_Request_Date']);
                if($cls_help->toItalianDate($a_lastProceedings[1]['Sentence_Challenge_Date'])!=null)
                    $a_appeal['Notes'].= " impugnata il ".$cls_help->toItalianDate($a_lastProceedings[1]['Sentence_Challenge_Date']);
                if($a_lastProceedings[1]['Sentence_Challenger']!=null)
                    $a_appeal['Notes'].= " da ".$a_lastProceedings[1]['Sentence_Challenger'];
            }
        }

        $actInput = "<select class='resize form-control' id=act_id name='Act_ID' onchange='Appeal.selectAct(a_act);'>".$opt_atti."</select>";
        $courtLevelInput = "<input type=hidden name='Court_Level' id=court_level value='".$courtLevel."'><span id=court_level_display class='titolo'>".$courtLevel."</span><span class='titolo'>&deg;</span>";
    }
}

$a_appealType = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getAppealType_query()));
$a_selection = array("value"=>"ID","firstOpt"=>1,"selected"=>$a_appeal['Type'], "text"=>array("[Description]"));
$opt_appealType = $cls_html->getOptions($a_appealType,$a_selection);

$a_proceedingsType = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getAppealProceedingsType_query()));
if(isset($a_proceedings[0]))
    $a_selection["selected"] = $a_proceedings[0]['Outcome'];
else
    $a_selection["selected"] = null;
$opt_sospensiva = $cls_html->getOptions($a_proceedingsType,$a_selection);
if(isset($a_proceedings[1]))
    $a_selection["selected"] = $a_proceedings[1]['Outcome'];
else
    $a_selection["selected"] = null;
$opt_merito = $cls_html->getOptions($a_proceedingsType,$a_selection);

$a_selection["firstOpt"] = 0;
$a_selection["selected"] = null;

$a_courtType = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getCourtHearingType_query()));
$opt_courtType = $cls_html->getOptions($a_courtType,$a_selection);

$a_courtDoctype = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getCourtHearingDoctype_query()));
$opt_courtDoctype = $cls_html->getOptions($a_courtDoctype,$a_selection);

$a_appealPartType = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getAppealPartType_query()));
$a_selection["selected"] = $a_appeal['Trespassers_Part'];
$opt_trespassersPart = $cls_html->getOptions($a_appealPartType,$a_selection);
$a_selection["selected"] = $a_appeal['Body_Part'];
$opt_bodyPart = $cls_html->getOptions($a_appealPartType,$a_selection);

$a_appealBodyType = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getAppealBodyType_query()));
$a_selection["selected"] = $a_appeal['Body_Type'];
$opt_bodyType = $cls_html->getOptions($a_appealBodyType,$a_selection);

$a_exemption = array(0=>array("ID"=>0,"Description"=>""),1=>array("ID"=>1,"Description"=>"Esenzione"));

$a_selection["selected"] = $a_lawyerBill[0]['VAT_Exemption'];
$opt_VAT[0] = $cls_html->getOptions($a_exemption,$a_selection);
$a_selection["selected"] = $a_lawyerBill[0]['Withholding_Tax_Exemption'];
$opt_Withholding_Tax[0] = $cls_html->getOptions($a_exemption,$a_selection);
$a_selection["selected"] = $a_lawyerBill[1]['VAT_Exemption'];
$opt_VAT[1] = $cls_html->getOptions($a_exemption,$a_selection);
$a_selection["selected"] = $a_lawyerBill[1]['Withholding_Tax_Exemption'];
$opt_Withholding_Tax[1] = $cls_html->getOptions($a_exemption,$a_selection);

$a_selection["firstOpt"] = 1;
$a_selection["selected"] = $a_lawyerBill[0]['Part'];
$opt_lawyerPart[0] = $cls_html->getOptions($a_appealPartType,$a_selection);
$a_selection["selected"] = $a_lawyerBill[1]['Part'];
$opt_lawyerPart[1] = $cls_html->getOptions($a_appealPartType,$a_selection);

$a_proceedingsPath = $cls_appeal->getProceedingStatusPath($c,$a_appeal['ID']);
$cls_file->folderCreation($a_proceedingsPath[1][0]);
$cls_file->folderCreation($a_proceedingsPath[1][1]);
$cls_file->folderCreation($a_proceedingsPath[2][0]);
$cls_file->folderCreation($a_proceedingsPath[2][1]);

$a_proceedingsFiles = array();
$a_proceedingsFiles[1][0] = $cls_file->getFilesFromPath($a_proceedingsPath[1][0]);
$a_proceedingsFiles[1][1] = $cls_file->getFilesFromPath($a_proceedingsPath[1][1]);
$a_proceedingsFiles[2][0] = $cls_file->getFilesFromPath($a_proceedingsPath[2][0]);
$a_proceedingsFiles[2][1] = $cls_file->getFilesFromPath($a_proceedingsPath[2][1]);
?>
<!-- Inclusione modale per ricerca ufficio giudiziario -->
<?php include_once (ROOT."/search_modal/offcanvas/authority_offcanvas.php"); ?>
<script>
    // Modali offcanvas
    function openOfcanvas(type,rif){
        selectRif = rif;
        switch (type){
            case 'authoritySearchModal':                                                     // ricerca autorità
                // Reset campi input
                $('#authority_c').val("");
                // Reset spazi tabella
                $('#appendTableAuthority').empty();
                // Gestione radio
                $('#judge').prop('checked', true);
                $('#court').prop('checked', false);
                $('#tax_prov').attr("checked", false);
                $('#tax_reg').prop('checked', false);
                $('#appeal').prop('checked', false);
                $('#scoi').attr("checked", false);
                // Apertura modale
                $('#authoritySearchModal').modal('show');
                break;
        }
    }

    function initialId(tipo,val){switch(tipo){
        case "authority":
            $("#authorityId").val(val['ID']);
            // Selezione tipo di ufficio giudiziario
            $stringa = '';
            switch(val['Tipo']){
                case 'giudice':
                    $stringa = "Giudice di Pace";
                    break;
                case 'tribunale':
                    $stringa = "Tribunale";
                    break;
                case 'comm_trib_prov':
                    $stringa = "Commissione Tributaria Provinciale";
                    break;
                case 'comm_trib_reg':
                    $stringa = "Commissione Tributaria Regionale";
                    break;
                case 'appello':
                    $stringa = "Corte d'Appello";
                    break;
                case 'cassazione':
                    $stringa = "Corte di Cassazione";
                    break;
                default:
                    $stringa = "??????";
            }
            $stringa+= " - "+val['Comune'];
            if(val['Sezione'] !=""){
                if(val['Sezione'] != null)
                    $stringa+= " sez. "+val['Sezione'];
            }

            $("#authorityName").val($stringa);
            break;
    }}
</script>
    <script>
        var WEB_PATH = "<?= WEB_ROOT; ?>";
        var WEB_IMG_PATH = "<?= IMMAGINIWEB; ?>";
    </script>
    <script src="<?= JS ?>/appeal.js"></script>
    <script src="<?= JS ?>/file.js"></script>
    <script>


        switchMenuImg("F3");
        F3_button = function(){
            if($("#authorityId").val()>0)
                $("#appeal_form").submit();
            else{
                alert("Inserire Autorita'!");
            }
        }

        switchMenuImg("F4");
        F4_button = function(){
            $("form#appeal_form").append('<input type="hidden" name="delete" value=1>');
            $("#appeal_form").submit();
        }

        Appeal.setParams("<?=$c;?>","<?=$a;?>");
        var opt_court = "<?=$opt_courtType;?>";
        var opt_court_doc = "<?=$opt_courtDoctype;?>";
        var a_act = <?=json_encode($a_act, JSON_FORCE_OBJECT);?>;

        function clickTab2(){
            $( ".tr_courtHearing" ).fadeToggle( "slow", function() {
                // Animation complete.
            });
        };
        function clickTab3(){
            $( ".tr_proceedingStatus" ).fadeToggle( "slow", function() {
                // Animation complete.
            });
        };
        function clickTab6(){
            
            $( ".tr_lawyerBill" ).fadeToggle( "slow", function() {
                // Animation complete.
            });
        };
        function clickTab7(){
            $( ".tr_lawyerLosing" ).fadeToggle( "slow", function() {
                // Animation complete.
            });
        };
        function clickTab8(){
            $( ".tr_judgeAmounts" ).fadeToggle( "slow", function() {
                // Animation complete.
            });
        };
    </script>
<br>

<?php
    include_once(INC."/pages_authorization.php");
?>

    <form id="appeal_form" name="appeal_form" action="appeal_save.php" method=post enctype="multipart/form-data">
        <input type="hidden" name="c" value="<?=$c;?>">
        <input type="hidden" name="a" value="<?=$a;?>">
        <input type="hidden" name="Appeal_ID" value="<?=$appeal_id;?>">
        <input type="hidden" name="Partita_ID" value="<?=$partitaID;?>">


        <div class="row">
            <div class="col col-lg-10 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize"><span class="titolo">Ricorso relativo a</span></label>
                    <div class="col-lg-6">
                        <?=$actInput;?>
                    </div>
                    <label class="col-lg-3 control-label resize"><?=$courtLevelInput;?><span class="titolo">&nbsp;&nbsp;grado</span></label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-10 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <textarea class="form-control resize" style="max-width: 100%;" name="Notes"><?php echo $a_appeal['Notes'];?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row tr_datiGenerali">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data registrazione</label>
                    <div class="col-lg-8">
                        <input name=Start_Date style="background-color: #97CFDD; border: 2px solid black;" class="form-control resize readonly text_center" readonly value="<?=$a_appeal['Start_Date']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data chiusura</label>
                    <div class="col-lg-8">
                        <input name=End_Date style="background-color: #97CFDD; border: 2px solid black;" class="form-control resize readonly text_center" readonly value="<?=$a_appeal['End_Date']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Tipo</label>
                    <div class="col-lg-8">
                        <select name="Appeal_Type" class="form-control resize">
                            <?=$opt_appealType;?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_datiGenerali">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Autorita'</label>
                    <div class="col-lg-8">
                        <input name="Authority_Name" id="authorityName" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_left" ondblclick="/*Appeal.searchAuthority();*/openOfcanvas('authoritySearchModal',0);" readonly value="<?=$a_appeal['Authority_Description'];?>">
                        <input type=hidden id="authorityId" name="Authority_ID" value="<?=$a_appeal['Authority_ID']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Giudice</label>
                    <div class="col-lg-8">
                        <input name="Judge" class="form-control resize text_left" value="<?=$a_appeal['Judge']?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_datiGenerali">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">Data sottoscrizione atto</label>
                    <div class="col-lg-6">
                        <input name="Amendment_Date" class="form-control resize text_center picker" value="<?=$a_appeal['Amendment_Date']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">Data notifica atto</label>
                    <div class="col-lg-6">
                        <input name="Notification_Date" class="form-control resize text_center picker" value="<?=$a_appeal['Notification_Date']?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_datiGenerali">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">Data iscrizione a ruolo</label>
                    <div class="col-lg-6">
                        <input name="Registration_Date" class="form-control resize text_center picker" value="<?=$a_appeal['Registration_Date']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">Data deposito</label>
                    <div class="col-lg-6">
                        <input name="Dossier_Submission_Date" class="form-control resize text_center picker" value="<?=$a_appeal['Dossier_Submission_Date']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-2 control-label resize">RG</label>
                    <div class="col-lg-10">
                        <input name="RG" class="form-control resize text_left" value="<?=$a_appeal['RG']?>">
                    </div>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <span class="titolo resize font14">Contribuenti/trasgressori</span>
                        <a onMouseover="title='Aggiungi coloro che sono rappresentati dall\'avvocato'" href="#" onClick="Appeal.addUserPart('trespassers')" style="text-decoration: none">
                            <img src="<?= IMMAGINIWEB; ?>/plus.png" width=15 height=15 border=0>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Parte</label>
                    <div class="col-lg-8">
                        <select name="Trespassers_Part" class="form-control resize"><?=$opt_trespassersPart;?></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Avvocato</label>
                    <div class="col-lg-8">
                        <input name="Trespassers_Lawyer" class="form-control resize text_left" value="<?=$a_appeal['Trespassers_Lawyer']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Foro</label>
                    <div class="col-lg-8">
                        <input name="Trespassers_Lawyer_Bar" class="form-control resize text_left" value="<?=$a_appeal['Trespassers_Lawyer_Bar']?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="tr_trespassers last_trespassers row" id="td_trespassers_begin">
            <?php
            for($i=0;$i<count($a_appealPart);$i++){
            if($i%2==0 && $i>0){
                $offset = "col-lg-offset-1";
            ?>
        <div class="tr_trespassers last_trespassers row">
            <?php
            }
            else {
                if($i==0) $offset = "col-lg-offset-1";
                else $offset = "";
            }
            ?>

            <div class="col col-lg-4 <?=$offset;?>"  id='td_trespassers_<?=($i+1)?>'>
                <input type=hidden name='id_trespassers[<?=($i+1)?>]'  id='id_trespassers_<?=($i+1)?>' value="<?=$a_appealPart[$i]['Part_ID'];?>">
                <input class='form-control resize readonly' style="background-color: rgb(153, 204, 255); border: 2px solid black;" type="text" id='trespassers_<?=($i+1)?>' name='trespassers[<?=($i+1)?>]'
                       readonly onclick='Appeal.searchUserPart("<?=($i+1)?>","trespassers");' value="<?=$a_appealPart[$i]['Part_Name'];?>"></div>
            <div class="col-lg-1 resize" id='post_td_trespassers_<?=($i+1)?>'>
                <a onMouseover="title='Elimina utente'" href='#' style='text-decoration:none;' onClick='Appeal.removeUserPart("<?=($i+1)?>","trespassers")' >
                    <img src="<?= IMMAGINIWEB; ?>/elimina_icon.png" style="width:14px; height:14px; border:0;" >
                </a>
            </div>

            <?php
            if($i>0 && $i%2!=0){
            ?>
        </div>
        <?php
        }
        }
        if((count($a_appealPart)%2!=0 && count($a_appealPart)>0) || count($a_appealPart)==0){
            ?>
            </div>
            <?php
        }
        ?>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <span class="titolo font14">Ente gestito</span>
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Parte</label>
                    <div class="col-lg-8">
                        <select name="Body_Part" class="form-control resize"><?=$opt_bodyPart;?></select>
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <div class="col-lg-8">
                        <select name="Body_Type" class="form-control resize"><?=$opt_bodyType;?></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Avvocato</label>
                    <div class="col-lg-8">
                        <input name="Body_Lawyer" class="form-control resize text_left" value="<?=$a_appeal['Body_Lawyer']?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Foro</label>
                    <div class="col-lg-8">
                        <input name="Body_Lawyer_Bar" class="form-control resize text_left" value="<?=$a_appeal['Body_Lawyer_Bar']?>">
                    </div>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <a href="#" id="clickTab2" onclick="clickTab2();"><span class="titolo font14">Udienze</span></a>
                        <a onMouseover="title='Aggiungi udienza'" href="#" onClick="$('.tr_courtHearing').show();Appeal.addCourtHearing(opt_court,opt_court_doc);" style="text-decoration: none">
                            <img src="<?= IMMAGINIWEB; ?>/plus.png" width=15 height=15 border=0>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="tr_courtHearing_begin"></div>

        <?php
        for($i=count($a_courtHearing)-1;$i>=0;$i--){
            $a_selection = array("value"=>"ID","firstOpt"=>1,"selected"=>null, "text"=>array("[Description]"));
            $a_selection['selected'] = $a_courtHearing[$i]['Type'];
            $opt_courtType = $cls_html->getOptions($a_courtType,$a_selection);
            $a_selection['selected'] = $a_courtHearing[$i]['Plaintiff_Proceedings_State'];
            $opt_plaintiff = $cls_html->getOptions($a_courtDoctype,$a_selection);
            $a_selection['selected'] = $a_courtHearing[$i]['Respondent_Proceedings_State'];
            $opt_respondent = $cls_html->getOptions($a_courtDoctype,$a_selection);

            $courtHearingPath = $cls_appeal->getCourtHearingPath($c,$a_appeal['ID'],$a_courtHearing[$i]['ID']);
            $cls_file->folderCreation($courtHearingPath['plaintiff']);
            $cls_file->folderCreation($courtHearingPath['respondent']);
            $a_plaintiffFiles = $cls_file->getFilesFromPath($courtHearingPath['plaintiff']);
            $a_respondentFiles = $cls_file->getFilesFromPath($courtHearingPath['respondent']);
            ?>

            <div class="tr_courtHearing row" id="tr_courtHearing_<?=($i+1);?>">
                <div class="col col-lg-1 col-lg-offset-1">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <span class="titolo"><?=($i+1);?></span>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize">Tipo</label>
                        <div class="col-lg-8">
                            <input type='hidden' name='Court_Hearing_ID[<?=($i+1);?>]' value='<?=$a_courtHearing[$i]['ID'];?>'>
                            <select name="Court_Hearing_Type[<?=($i+1);?>]" class="form-control resize"><?=$opt_courtType;?></select>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize">Data</label>
                        <div class="col-lg-8">
                            <input name="Court_Hearing_Date[<?=($i+1);?>]" class="form-control resize text_center picker" value='<?=$cls_help->toItalianDate($a_courtHearing[$i]['Date']);?>'>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize">Ora</label>
                        <div class="col-lg-8">
                            <input name="Court_Hearing_Time[<?=($i+1);?>]" class="form-control resize text_center" value='<?=$a_courtHearing[$i]['Time'];?>'>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tr_courtHearing row" >
                <div class="col col-lg-1 col-lg-offset-1">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <span class="titolo font12">P. attrice</span>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize">Stato atti</label>
                        <div class="col-lg-8">
                            <select name="Plaintiff_Proceedings_State[<?=($i+1);?>]" class="form-control resize"><?=$opt_plaintiff;?></select>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize">Data deposito</label>
                        <div class="col-lg-8">
                            <input name="Plaintiff_Docs_Date[<?=($i+1);?>]" class="form-control resize text_center picker" value='<?=$cls_help->toItalianDate($a_courtHearing[$i]['Plaintiff_Docs_Date']);?>'>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tr_courtHearing row" >
                <div class="col col-lg-3 col-lg-offset-2">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input type="file" style="background-color: rgb(153, 204, 255);" class="form-control resize" value="Upload atti" name="Plaintiff_Docs[<?=($i+1);?>]">
                        </div>
                    </div>
                </div>
                <div class="col col-lg-5 ">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <?=$cls_appeal->getFilesRow($a_plaintiffFiles,$_SERVER['REQUEST_URI']);?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tr_courtHearing row" >
                <div class="col col-lg-1 col-lg-offset-1">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <span class="titolo font12">P. convenuta</span>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize">Stato atti</label>
                        <div class="col-lg-8">
                            <select name="Respondent_Proceedings_State[<?=($i+1);?>]" class="form-control resize"><?=$opt_respondent;?></select>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize">Data deposito</label>
                        <div class="col-lg-8">
                            <input name="Respondent_Docs_Date[<?=($i+1);?>]" class="form-control resize text_center picker" value='<?=$cls_help->toItalianDate($a_courtHearing[$i]['Respondent_Docs_Date']);?>'>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tr_courtHearing row" >
                <div class="col col-lg-3 col-lg-offset-2">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input type="file" class="form-control resize" style="background-color: rgb(153, 204, 255);" value="Upload atti" name="Respondent_Docs[<?=($i+1);?>]">
                        </div>
                    </div>
                </div>
                <div class="col col-lg-5 ">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <?=$cls_appeal->getFilesRow($a_respondentFiles,$_SERVER['REQUEST_URI']);?>
                        </div>
                    </div>
                </div>
            </div>

            <div style="border-top: 2px solid #B0BBE8; width: 50%; margin-left: 25%;margin-bottom: 1%;margin-top: 2%;" class="tr_courtHearing"></div>

            <?php
        }
        ?>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <a href="#" id="clickTab3" onclick="clickTab3();"><span class="titolo font14">Stati del procedimento</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_proceedingStatus" style="margin-top: 2%;">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <span class="titolo font14">Sospensiva</span>
                        <input name="Proceeding_ID[1]" type=hidden value="<?=$a_proceedings[0]['ID'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Esito</label>
                    <div class="col-lg-8">
                        <select name="Outcome[1]" class="form-control resize"><?=$opt_sospensiva;?></select>
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Note</label>
                    <div class="col-lg-8">
                        <input name="Proceeding_Notes[1]" class="form-control resize text_left" value="<?=$a_proceedings[0]['Notes'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_proceedingStatus">
            <div class="col col-lg-2 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Sentenza n.</label>
                    <div class="col-lg-8">
                        <input name="Sentence_Number[1]" class="form-control resize text_right" value="<?=$a_proceedings[0]['Number'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">del</label>
                    <div class="col-lg-8">
                        <input name="Sentence_Date[1]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[0]['Date']);?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data deposito</label>
                    <div class="col-lg-8">
                        <input name="Sentence_File_Date[1]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[0]['File_Date']);?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data notifica</label>
                    <div class="col-lg-8">
                        <input name="Outcome_Notification_Date[1]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[0]['Outcome_Notification_Date']);?>">
                    </div>
                </div>
            </div>
            <input type=hidden name="Sentence_Challenge_Date[1]">
            <input type=hidden name="Sentence_Challenger[1]">
            <input type=hidden name="Sentence_Request_Date[1]">
        </div>

        <div class="row tr_proceedingStatus" >
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input type="file" class="form-control resize" style="background-color: rgb(153, 204, 255);" value="Upload documenti" name="Sentence_Docs[1]">
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <?=$cls_appeal->getFilesRow($a_proceedingsFiles[1][0],$_SERVER['REQUEST_URI']);?>
                    </div>
                </div>
            </div>
        </div>


        <div class="row tr_proceedingStatus" style="margin-top: 2%;">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <span class="titolo font14">Merito</span>
                        <input name="Proceeding_ID[2]" type=hidden value="<?=$a_proceedings[1]['ID'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Esito</label>
                    <div class="col-lg-8">
                        <select name="Outcome[2]" class="form-control resize"><?=$opt_merito;?></select>
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Note</label>
                    <div class="col-lg-8">
                        <input name="Proceeding_Notes[2]" class="form-control resize text_left" value="<?=$a_proceedings[1]['Notes'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_proceedingStatus">
            <div class="col col-lg-2 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Sentenza n.</label>
                    <div class="col-lg-8">
                        <input name="Sentence_Number[2]" class="form-control resize text_right" value="<?=$a_proceedings[1]['Number'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">del</label>
                    <div class="col-lg-8">
                        <input name="Sentence_Date[2]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[1]['Date']);?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data deposito</label>
                    <div class="col-lg-8">
                        <input name="Sentence_File_Date[2]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[1]['File_Date']);?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data notifica</label>
                    <div class="col-lg-8">
                        <input name="Outcome_Notification_Date[2]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[1]['Outcome_Notification_Date']);?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_proceedingStatus" >
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input type="file" class="form-control resize" style="background-color: rgb(153, 204, 255);" value="Upload documenti" name="Sentence_Docs[2]">
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <?=$cls_appeal->getFilesRow($a_proceedingsFiles[2][0],$_SERVER['REQUEST_URI']);?>
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <?php

                        if(isset($a_proceedings[1])){
                            if($a_proceedings[1]['Outcome']==6){//INCOMPETENTE
                                ?>
                                <input class="btn btn-primary" type="button" onclick='Appeal.changeAuthority("<?=$partitaID;?>","<?=$a_appeal['ID'];?>")' value="Cambia autorità">
                                <?php
                            }
                        }

                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_proceedingStatus" style="margin-top: 2%;">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <span class="titolo font14">Impugnativa Merito</span>
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Sentenza impugnata da</label>
                    <div class="col-lg-8">
                        <input name="Sentence_Challenger[2]" class="form-control resize text_left" value="<?=$a_proceedings[1]['Sentence_Challenger'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_proceedingStatus" style="margin-top: 2%;">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data richiesta sentenza</label>
                    <div class="col-lg-8">
                        <input name="Sentence_Request_Date[2]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[1]['Sentence_Request_Date']);?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data impugnazione sentenza</label>
                    <div class="col-lg-8">
                        <input name="Sentence_Challenge_Date[2]" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_proceedings[1]['Sentence_Challenge_Date']);?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_proceedingStatus" >
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input type="file" class="form-control resize" style="background-color: rgb(153, 204, 255);" value="Upload documenti" name="Challenge_Docs[2]">
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <?=$cls_appeal->getFilesRow($a_proceedingsFiles[2][1],$_SERVER['REQUEST_URI']);?>
                    </div>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row" >
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <a href="#" id="clickTab4" onclick="clickTab6();"><span class="titolo font14">Parcella avvocato</span></a>
                        <input name="Lawyer_Bill_ID[1]" type=hidden value="<?=$a_lawyerBill[0]['ID'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill" style="margin-top: 2%;">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Numero</label>
                    <div class="col-lg-8">
                        <input name="Bill_Number[1]" id="bill_number1" class="form-control resize text_right" value="<?=$a_lawyerBill[0]['Bill_Number'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data</label>
                    <div class="col-lg-8">
                        <input name="Bill_Date[1]" id="bill_date1" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_lawyerBill[0]['Bill_Date']);?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Avvocato</label>
                    <div class="col-lg-8">
                        <input name="Lawyer[1]" id="lawyer1" class="form-control resize text_left" value="<?=$a_lawyerBill[0]['Lawyer'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill" >
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Parte</label>
                    <div class="col-lg-8">
                        <select name="Part[1]" class="form-control resize" id="part1"><?=$opt_lawyerPart[0];?></select>
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Pagante</label>
                    <div class="col-lg-8">
                        <input name="Payer[1]" id="payer1" class="form-control resize text_left" value="<?=$a_lawyerBill[0]['Payer'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data pagamento</label>
                    <div class="col-lg-8">
                        <input name="Payment_Date[1]" id="payment_date1" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_lawyerBill[0]['Payment_Date']);?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill" >
            <div class="col col-lg-10 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-1 control-label resize">Note</label>
                    <div class="col-lg-11">
                        <input name="Lawyer_Notes[1]" id="notes1" class="form-control resize text_left" value="<?=$a_lawyerBill[0]['Notes'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill" style="margin-top: 2%;" >
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">Onorari + Diritti</label>
                    <div class="col-lg-5">
                        <input readonly id="feerights1" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Fee'] + $a_lawyerBill[0]['Rights']);?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Onorari</label>
                    <div class="col-lg-7">
                        <input name="Fee[1]" id="fee1" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(1);" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Fee'])?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Diritti</label>
                    <div class="col-lg-7">
                        <input name="Rights[1]" id="rights1" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(1);" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Rights'])?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">+ Spese generali</label>
                    <div class="col-lg-5">
                        <input readonly name="Overheads[1]" id="overheads1" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Overheads'])?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">+ Cassa avvocati</label>
                    <div class="col-lg-5">
                        <input readonly name="Lawyer_Fund[1]" id="lawyer_fund1" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Lawyer_Fund'])?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill" >
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">+ IVA</label>
                    <div class="col-lg-5">
                        <input readonly name="VAT[1]" id="VAT1" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[0]['VAT'])?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <select id="VAT_exemption1" class="form-control resize" name="VAT_Exemption[1]" onchange="Appeal.lawyerAmounts(1);"><?=$opt_VAT[0];?></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize"> + Spese vive</label>
                    <div class="col-lg-5">
                        <input readonly id="actual_costs1" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[0]['CU']+$a_lawyerBill[0]['Stamp_Duty']+$a_lawyerBill[0]['Other_Costs'])?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">C.U.</label>
                    <div class="col-lg-7">
                        <input name="CU[1]" id="cu1" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(1);" value="<?=$cls_help->floatToString($a_lawyerBill[0]['CU'])?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Marche</label>
                    <div class="col-lg-7">
                        <input name="Stamp_Duty[1]" id="stamp_duty1" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(1);" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Stamp_Duty'])?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Altro</label>
                    <div class="col-lg-7">
                        <input name="Other_Costs[1]" id="other_costs1" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(1);" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Other_Costs'])?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill" >
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">- R.a.</label>
                    <div class="col-lg-5">
                        <input readonly name="Withholding_Tax[1]" id="withholding_tax1" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Withholding_Tax'])?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <select id="withholding_tax_exemption1" class="form-control resize" name="Withholding_Tax_Exemption[1]" onchange="Appeal.lawyerAmounts(1);"><?=$opt_Withholding_Tax[0];?></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerBill">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">= Totale</label>
                    <div class="col-lg-5">
                        <input readonly name="Bill_Total[1]" id="bill_total1" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[0]['Bill_Total'])?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row" >
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <a href="#" id="clickTab4" onclick="clickTab7();"><span class="titolo font14">Soccombenza avvocato</span></a>
                        <input name="Lawyer_Bill_ID[2]" type=hidden value="<?=$a_lawyerBill[1]['ID'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing" style="margin-top: 2%;">
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Numero</label>
                    <div class="col-lg-8">
                        <input name="Bill_Number[2]" id="bill_number2" class="form-control resize text_right" value="<?=$a_lawyerBill[1]['Bill_Number'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data</label>
                    <div class="col-lg-8">
                        <input name="Bill_Date[2]" id="bill_date2" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_lawyerBill[1]['Bill_Date']);?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Avvocato</label>
                    <div class="col-lg-8">
                        <input name="Lawyer[2]" id="lawyer2" class="form-control resize text_left" value="<?=$a_lawyerBill[1]['Lawyer'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing" >
            <div class="col col-lg-3 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Parte</label>
                    <div class="col-lg-8">
                        <select name="Part[2]" class="form-control resize" id="part2"><?=$opt_lawyerPart[1];?></select>
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Pagante</label>
                    <div class="col-lg-8">
                        <input name="Payer[2]" id="payer2" class="form-control resize text_left" value="<?=$a_lawyerBill[1]['Payer'];?>">
                    </div>
                </div>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Data pagamento</label>
                    <div class="col-lg-8">
                        <input name="Payment_Date[2]" id="payment_date2" class="form-control resize text_center picker" value="<?=$cls_help->toItalianDate($a_lawyerBill[1]['Payment_Date']);?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing" >
            <div class="col col-lg-10 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-1 control-label resize">Note</label>
                    <div class="col-lg-11">
                        <input name="Lawyer_Notes[2]" id="notes2" class="form-control resize text_left" value="<?=$a_lawyerBill[1]['Notes'];?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing" style="margin-top: 2%;" >
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">Onorari + Diritti</label>
                    <div class="col-lg-5">
                        <input readonly id="feerights2" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Fee'] + $a_lawyerBill[1]['Rights']);?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Onorari</label>
                    <div class="col-lg-7">
                        <input name="Fee[2]" id="fee2" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(2);" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Fee']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Diritti</label>
                    <div class="col-lg-7">
                        <input name="Rights[2]" id="rights2" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(2);" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Fee']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">+ Spese generali</label>
                    <div class="col-lg-5">
                        <input readonly name="Overheads[2]" id="overheads2" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Overheads']);?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">+ Cassa avvocati</label>
                    <div class="col-lg-5">
                        <input readonly name="Lawyer_Fund[2]" id="lawyer_fund2" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Lawyer_Fund']);?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing" >
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">+ IVA</label>
                    <div class="col-lg-5">
                        <input readonly name="VAT[2]" id="VAT2" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[1]['VAT']);?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <select id="VAT_exemption2" class="form-control resize" name="VAT_Exemption[2]" onchange="Appeal.lawyerAmounts(2);"><?=$opt_VAT[1];?></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize"> + Spese vive</label>
                    <div class="col-lg-5">
                        <input readonly id="actual_costs2" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[1]['CU']+$a_lawyerBill[1]['Stamp_Duty']+$a_lawyerBill[1]['Other_Costs'])?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">C.U.</label>
                    <div class="col-lg-7">
                        <input name="CU[2]" id="cu2" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(2);" value="<?=$cls_help->floatToString($a_lawyerBill[1]['CU']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Marche</label>
                    <div class="col-lg-7">
                        <input name="Stamp_Duty[2]" id="stamp_duty2" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(2);" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Stamp_Duty']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-3 control-label resize">Altro</label>
                    <div class="col-lg-7">
                        <input name="Other_Costs[2]" id="other_costs2" class="form-control resize corrige_numero text_right" onchange="Appeal.lawyerAmounts(2);" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Other_Costs']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing" >
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">- R.a.</label>
                    <div class="col-lg-5">
                        <input readonly name="Withholding_Tax[2]" id="withholding_tax2" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Withholding_Tax']);?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <div class="col-lg-12">
                        <select id="withholding_tax_exemption2" class="form-control resize" name="Withholding_Tax_Exemption[2]" onchange="Appeal.lawyerAmounts(2);"><?=$opt_Withholding_Tax[1];?></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_lawyerLosing">
            <div class="col col-lg-4 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize">= Totale</label>
                    <div class="col-lg-5">
                        <input readonly name="Bill_Total[2]" id="bill_total2" style="background-color: rgb(153, 204, 255); border: 2px solid black;" class="form-control resize readonly text_right" value="<?=$cls_help->floatToString($a_lawyerBill[1]['Bill_Total']);?>">
                    </div>
                    <label class="col-lg-1 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

        <div class="row" >
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <a href="#" id="clickTab8" onclick="clickTab8();"><span class="titolo font14">Importi giudice</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tr_judgeAmounts" style="margin-top: 2%;">
            <div class="col col-lg-6 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">Totale</label>
                    <div class="col-lg-6">
                        <input name="Judge_Total" id="judge_total" class="form-control resize text_right corrige_numero" value="<?=$cls_help->floatToString($a_appeal['Total']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_judgeAmounts">
            <div class="col col-lg-6 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">di cui Importo atto</label>
                    <div class="col-lg-6">
                        <input name="Judge_Act_Amount" id="judge_act_amount" class="form-control resize text_right corrige_numero" value="<?=$cls_help->floatToString($a_appeal['Act_Amount']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_judgeAmounts">
            <div class="col col-lg-6 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">di cui Spese legali</label>
                    <div class="col-lg-6">
                        <input name="Judge_Legal_Costs" id="judge_legal_costs" class="form-control resize text_right corrige_numero" value="<?=$cls_help->floatToString($a_appeal['Legal_Costs']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>

        <div class="row tr_judgeAmounts">
            <div class="col col-lg-6 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize">di cui Spese vive</label>
                    <div class="col-lg-6">
                        <input name="Judge_Actual_Costs" id="judge_actual_costs" class="form-control resize text_right corrige_numero" value="<?=$cls_help->floatToString($a_appeal['Actual_Costs']);?>">
                    </div>
                    <label class="col-lg-2 control-label resize">€</label>
                </div>
            </div>
        </div>
    </form>


    <script>
        $( ".tr_courtHearing" ).hide();
        $( ".tr_proceedingStatus" ).hide();
        $( ".tr_lawyerBill" ).hide();
        $( ".tr_lawyerLosing" ).hide();
        $( ".tr_judgeAmounts" ).hide();
    </script>

<?php

include(INC."/footer.php");

?>