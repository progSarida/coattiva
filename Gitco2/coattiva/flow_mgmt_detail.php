<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");


include_once(CLS."/cls_registry.php");
include_once(CLS."/cls_html.php");
include_once(CLS."/cls_flow.php");



$FlowId = $cls_help->getVar("FlowId");

$activeTab =  $cls_help->getVar('activeTab');
$filterFlowNumber = $cls_help->getVar("filterFlowNumber");

$filterInvoiceNumber = $cls_help->getVar("filterInvoiceNumber");
$filterInvoiceYear = $cls_help->getVar("filterInvoiceYear");
$filterInvoiceDate = $cls_help->toDbDate($cls_help->getVar("filterInvoiceDate"));

$filterFlowYear = $cls_help->getVar('filterFlowYear');

$filterFlowStatus = $cls_help->getVar("filterFlowStatus");
$filterFlowMissStatus = $cls_help->getVar("filterFlowMissStatus");
$filterFlowStatusOfDate = $cls_help->getVar("filterFlowStatusOfDate");
$filterFlowStatusDate = $cls_help->toDbDate($cls_help->getVar("filterFlowStatusDate"));
$filterFlowCityId = $cls_help->getVar("filterFlowCityId");



$queryFlow = "SELECT STATO.Descrizione AS Stato_Not_Descrizione, MODA.Descrizione AS Modalita_Not_Descrizione, MOT.Descrizione AS Anomalia_Not_Descrizione, ";
$queryFlow.= "v_atti_pigno.*, notifiche_importate.Ms_Ric_Num, notifiche_importate.Ms_Rac_Num, utente.Cognome, utente.Nome, utente.Ditta, utente.Genere, partita_tributi.Comune_ID FROM v_atti_pigno ";
$queryFlow.= "JOIN partita_tributi ON partita_tributi.ID=v_atti_pigno.Partita_ID JOIN utente ON utente.ID=partita_tributi.Utente_ID ";
$queryFlow.= "LEFT JOIN parametri_notifica MOT ON MOT.ID=v_atti_pigno.Motivo_Notifica ";
$queryFlow.= "LEFT JOIN parametri_notifica MODA ON MODA.ID=v_atti_pigno.Modalita_Notifica ";
$queryFlow.= "LEFT JOIN parametri_notifica STATO ON STATO.ID=v_atti_pigno.Stato_Notifica ";
$queryFlow.= "LEFT JOIN notifiche_importate ON v_atti_pigno.ID=notifiche_importate.DocumentId ";
$queryFlow.= "AND v_atti_pigno.FlowId=notifiche_importate.FlowId AND v_atti_pigno.DocumentTypeId=notifiche_importate.DocumentTypeId ";
$queryFlow.= "AND v_atti_pigno.CC=notifiche_importate.CC_Comune WHERE v_atti_pigno.FlowId=".$FlowId." ORDER BY Anno_Cronologico ASC, ID_Cronologico ASC";

$a_flows = $cls_db->getResults($cls_db->SelectQuery($queryFlow));

//print_r($a_flows);

?>

    <div class="row-fluid">

<?php
    if(count($a_flows)>0){
        ?>
    <div class="col-sm-12">
    <div class="col-sm-1 BoxRowLabel">
        Flusso N.
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <?= $a_flows[0]['Numero_Flusso']; ?>
    </div>
    <div class="col-sm-1 BoxRowLabel">
        Del
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <?= $cls_help->toItalianDate($a_flows[0]['Data_Flusso']); ?>
    </div>
    <div class="col-sm-2 BoxRowLabel">
        Documento
    </div>
    <div class="col-sm-4 BoxRowCaption">
        <?= $a_flows[0]['DocumentType']; ?>
    </div>
    <hr>
    <div class="clean_row HSpace16"></div>
    <div class="table_label_small_H col-sm-1"><b>Partita ID</b></div>
    <div class="table_label_small_H col-sm-1"><b>Cronologico</b></div>
    <div class="table_label_small_H col-sm-1"><b>Data Not.</b></div>
    <div class="table_label_small_H col-sm-1"><b>Raccomandata</b></div>
    <div class="table_label_small_H col-sm-1"><b>Ric. Ritorno</b></div>
    <div class="table_label_small_H col-sm-2"><b>Esito</b></div>
    <div class="table_label_small_H col-sm-2"><b>Tipo utente</b></div>
    <div class="table_label_small_H col-sm-3"><b>Utente</b></div>
    <div class="clean_row HSpace4"></div>

    <?php
    }
for($i=0;$i<count($a_flows);$i++){

    if($a_flows[$i]['Motivo_Notifica']>0)
        $str_ReasonCSS = ' style="background-color:#87bfff;color: darkred"';
    else if($a_flows[$i]['Stato_Notifica']>0)
        $str_ReasonCSS = ' style="background-color:#87bfff; color: yellow"';
    else if($a_flows[$i]['Modalita_Notifica']>0)
        $str_ReasonCSS = ' style="background-color:#87bfff;color: green"';
    else $str_ReasonCSS = '';

    if($a_flows[$i]['Genere']=="D")
        $genere = "Societa'";
    else
        $genere = "Persona fisica";

?>
        <div class="table_caption_H col-sm-1">
            <?= $a_flows[$i]['Comune_ID']; ?>
        </div>
        <div class="table_caption_H col-sm-1">
            <?= $a_flows[$i]['ID_Cronologico']."/".$a_flows[$i]['Anno_Cronologico']; ?>
        </div>
        <div class="table_caption_H col-sm-1">
            <?= $cls_help->toItalianDate($a_flows[$i]['Data_Notifica']); ?>
        </div>
        <div class="table_caption_H col-sm-1">
            <?= $a_flows[$i]['Ms_Rac_Num']; ?>
        </div>
        <div class="table_caption_H col-sm-1">
            <?= $a_flows[$i]['Ms_Ric_Num']; ?>
        </div>
        <div class="table_caption_H col-sm-2" <?=$str_ReasonCSS;?>>
            <?= $a_flows[$i]['Modalita_Not_Descrizione']." ".$a_flows[$i]['Stato_Not_Descrizione']." ".$a_flows[$i]['Anomalia_Not_Descrizione']; ?>
        </div>
        <div class="table_caption_H col-sm-2">
            <?= $genere; ?>
        </div>
        <div class="table_caption_H col-sm-3">
            <?= $a_flows[$i]['Ditta'].$a_flows[$i]['Cognome']." ".$a_flows[$i]['Nome']; ?>
        </div>
        <div class="clean_row HSpace4"></div>
        

<?php
}

?>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    
                    <button class="btn btn-default" id="back">Indietro</button>

<!--                    <a href="--><?php //echo WEB_ROOT; ?><!--/stampe/flow_print_detail_exe.php?c=--><?//=$c;?><!--&a=--><?//=$a;?><!--&FlowId=--><?//=$FlowId;?><!--&file=pdf">-->
<!--                        <img src="--><?php //echo IMG; ?><!--/icon_pdf.png" width=33 height=33 border=0 >-->
<!--                    </a>-->
                    <a  href="<?php echo WEB_ROOT; ?>/stampe/flow_print_detail_exe.php?c=<?=$c;?>&a=<?=$a;?>&FlowId=<?=$FlowId;?>&file=xls">
                        <img title="Stampa excell" src="<?php echo IMG; ?>/icon_excel.png" width=35 height=35 border=0 >
                    </a>

                </div>    
            </div>
        </div>  


</div>
<!--  GV - 08/06/2022 - START  -->
<form id = "form_details" name="form_details" action="flow_mgmt.php" method="POST">
       
       <input type="hidden" id="c" name="c" value="<?=$c;?>">
       <input type="hidden" id="a" name="a" value="<?=$a;?>">
       
       <input type="hidden" id= "activeTab" name="activeTab" value="2<?php //echo $activeTab; ?>">

       <input type="hidden" id= "filterInvoiceNumber" name="filterInvoiceNumber" value = <?php echo $filterInvoiceNumber; ?> >
       <input type="hidden" id= "filterInvoiceYear" name="filterInvoiceYear" value = <?php echo $filterInvoiceYear; ?>>
       <input type="hidden" id= "filterInvoiceDate" name="filterInvoiceDate" value = <?php echo $filterInvoiceDate; ?>>

       <input type="hidden" id= "filterFlowNumber" name="filterFlowNumber" value = <?php echo $filterFlowNumber; ?>  >
       <input type="hidden" id= "filterFlowYear" name="filterFlowYear" value = <?php echo $filterFlowYear; ?> >
       <input type="hidden" id= "filterFlowStatus" name="filterFlowStatus" value = <?php echo $filterFlowStatus; ?>>
       <input type="hidden" id= "filterFlowMissStatus" name="filterFlowMissStatus" value = <?php echo $filterFlowMissStatus ; ?>>
       <input type="hidden" id= "filterFlowStatusOfDate" name="filterFlowStatusOfDate" value = <?php echo $filterFlowStatusOfDate; ?>>
       <input type="hidden" id= "filterFlowStatusDate" name="filterFlowStatusDate" value = <?php echo $filterFlowStatusDate; ?>>
       <input type="hidden" id= "filterFlowCityId" name="filterFlowCityId" value = <?php echo $filterFlowCityId; ?>>
    </form>

<!--  GV - 08/06/2022 - END  -->
    <script type="text/javascript">
        $('document').ready(function () {

            $('#back').click(function () {
                /**
                 * GV - 08/06/2022 START -
                 * window.location = "flow_mgmt.php?a=<?=$a;?>&c=<?=$c;?>";
                 */
                
                $('#form_details').submit();
                /**
                 * GV - 08/06/2022  END -
                 */
               
                return false;
            });

        });
    </script>
<?php


include(INC . "/footer.php");
