<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");


include(INC."/header.php");
include (INC."/menu.php");

include_once CLS . "/cls_Utils.php";

$cls_utils = new cls_Utils();
$cls_db = new cls_db();
//$cls_help->alert($cls_help->getVar("id_flows"));

$ID = json_decode($cls_help->getVar("id_flows"));
$stampatore = $cls_help->getVar("stampatore");

$docType = $cls_help->getVar('tipo');

$Tipo = "";
switch($docType)
{
    case "ING":
    case "AV_INT":
    case "AV_MORA":
    case "SOLL_POST":
    case "SOLL_PRE": $tipo_pagina = "atto"; $Table = "v_atti"; break;
    case "banca":
    case "lavoro":
    case "preav_fermo":
    case "veicolo": $tipo_pagina = "pigno"; $Table = "v_pignoramento"; break;
    default: break;
}

if(gettype($ID)!="array")
    $ID = array($ID);

?>

    <style>
        .tableFixHead thead th
        {
            position: sticky;
            top: 0;
            background-color: #ACB1E8;
        }
        .table thead > tr > th { border-bottom: none; }
        .table thead > tr > th { border-bottom: 1px solid black; }
        .table tbody > tr { border-right: 1px solid black; border-left: 1px solid black;}
    </style>

<?php
//var_dump(json_decode($ID));
$whereFlow = "";
$whereAtti = "";
for($x = 0; $x < count($ID); $x++) {


    if($x > 0 && $x < (count($ID)))
        $whereFlow .= " OR ";
    $whereFlow .= " Id = ".$ID[$x];

    if($x > 0 && $x < (count($ID)))
        $whereAtti .= " OR ";
    $whereAtti .= " FlowId = ".$ID[$x];




}

$query = "SELECT * FROM v_flows WHERE ".$whereFlow;
$flow = $cls_db->getResults($cls_db->ExecuteQuery($query),"object");

$query = "SELECT * FROM ".$Table." WHERE " . $whereAtti . " ORDER BY FlowId ASC";
//echo $query;
$atti = $cls_db->getResults($cls_db->ExecuteQuery($query), "object");// getResults($cls_db->ExecuteQuery($query),"object");



for($x = 0; $x < count($ID); $x++)
{
    //$cls_help->alert($flow[$x]->DocumentType);

    if($flow[$x]->DocumentType == "Ingiunzione")
    {
        $cartella = "Ingiunzioni";
        //$prefisso = "Ingiunzione_";
    }
    else if($flow[$x]->DocumentType == "Avviso di intimazione ad adempiere")
    {
        $cartella = "Avvisi_di_intimazione";
        //$prefisso = "Avviso_di_intimazione_";
    }
    else if($flow[$x]->DocumentType == "Sollecito di pagamento")
    {
        $cartella = "Solleciti";
        //$prefisso = "Sollecito_";
    }
    else if($flow[$x]->DocumentType == "Sollecito pre ingiunzione" || $flow[$x]->DocumentType=="SOLL_PRE")
    {
        $cartella = "Solleciti_Pre_Ingiunzione";
        //$prefisso = "sollecitoPreIngiunzione_";
    }
    else if($flow[$x]->DocumentType == "Avviso di messa in mora" || $flow[$x]->DocumentType=="AV_MORA")
    {
        $cartella = "Avvisi_Messa_In_Mora";
        //$prefisso = "avvisoMessaInMora_";
    }
    else if($flow[$x]->DocumentType == "Pignoramento di beni mobili registrati" || $flow[$x]->DocumentType=="veicolo")
    {
        $cartella = "Pignoramenti/Veicolo";
        //$prefisso = "PignoramentoVeicolo_";
    }
    else if($flow[$x]->DocumentType == "Pignoramento presso banca" || $flow[$x]->DocumentType=="banca")
    {
        $cartella = "Pignoramenti/Presso_Terzi/Banca";
        //$prefisso = "PignoramentoBanca_";
    }
    else if($flow[$x]->DocumentType == "Pignoramento presso datore di lavoro" || $flow[$x]->DocumentType=="lavoro")
    {
        $cartella = "Pignoramenti/Presso_Terzi/Datore_di_Lavoro";
        //$prefisso = "PignoramentoLavoro_";
    }
    else if($flow[$x]->DocumentType == "Preavviso fermo" || $flow[$x]->DocumentType=="preav_fermo")
    {
        $cartella = "Pignoramenti/Preavviso_Fermo";
        //$prefisso = "PignoramentoLavoro_";
    }

    $dir = ATTI . "/" . $c . "/" . $cartella . "/FLUSSI/" . $flow[$x]->FileName;
    //$cls_help->alert($dir);
    $dir = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($dir);
    //$cls_help->alert($dir);

     if($stampatore != 1) {?>
    <div class="row">
        <div class="col-lg-offset-4 col-lg-5">
            <a style="color: blue; font-size: 18px;" onclick="apri('<?php echo $dir; ?>');" href="#"><?= $flow[$x]->FileName ?></a>
        </div>
        <div class="col-lg-2"><img id=flusso_rar src="<?= IMMAGINIWEB; ?>/rar.png" style="text-decoration:none; border:none; " width="20" height="20" onclick="apri('<?php echo $dir; ?>');" title="Archivio RAR flusso"></div>
    </div>
    <?php } ?>

    <div class="row" style="margin-top: 2%;">
        <div class="col-lg-offset-1 col-lg-2 titolo">CC:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->CityId; ?></div>
        <div class="col-lg-2 titolo">Anno:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->Year; ?></div>
    </div>
    <div class="row">
        <div class="col-lg-offset-1 col-lg-2 titolo">Numero:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->Number; ?></div>
        <div class="col-lg-2 titolo">Stampatore:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->Printer; ?></div>
    </div>
    <div class="row">
        <div class="col-lg-offset-1 col-lg-2 titolo">Tipo Stampa:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->PrintType; ?></div>
        <div class="col-lg-2 titolo">Tipo Documento:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->DocumentType; ?></div>
    </div>
    <div class="row">
        <div class="col-lg-offset-1 col-lg-2 titolo">Data creazione:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->CreationDate; ?></div>
        <div class="col-lg-2 titolo">Nome File:</div>
        <div class="col-lg-3" style="font-weight: bold;"><?= $flow[$x]->FileName; ?></div>
    </div>

    <div class="row">
        <div class="col-lg-10 col-lg-offset-1" style="background-color: #757CFF; border: 1px solid white; margin-top: 2%;">
            <div style="float:left; color: white;">Dettaglio Flussi</div>
            <div style="float: right; color: white;"><i class="fa fa-angle-down" aria-hidden="true" style="cursor: pointer;" onclick="mostraNascondi('table_<?= $x; ?>',this);"></i></div>
        </div>
    </div>
    <div class="tableFixHead all_table" id="table_<?= $x; ?>" style="overflow-y: auto; max-height: 35vh !important; display: none;margin-top: 0;">
        <table class="table table-hover" style="width: 98%; margin-left: 1%; border-bottom: 1px solid black;">
            <colgroup>
                <col style="width: 4%;">
                <col style="width: 7%;">
                <?php if($tipo_pagina == "atto") {?>
                <col style="width: 8%;">
                <col style="width: 8%;">
                <?php } ?>
                <col style="width: 8%;">
                <col style="width: 20%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 8%;">
                <col style="width: 7%;">
            </colgroup>
            <thead style="background-color:#8C9AFF; color: white;">
                <tr>
                    <th style="font-size: 12px;" >ID</th>
                    <th style="font-size: 12px;" >Data Notifica</th>
                    <?php if($tipo_pagina == "atto") {?>
                    <th style="font-size: 12px;">Tot. minimo Dovuto</th>
                    <th style="font-size: 12px;">Tot. massimo Dovuto</th>
                    <?php } ?>
                    <th style="font-size: 12px;">Tipo Riscossione</th>
                    <th style="font-size: 12px;">Info Cartella</th>
                    <th style="font-size: 12px;">Cognome/Ditta</th>
                    <th style="font-size: 12px;">Nome</th>
                    <th style="font-size: 12px;">Info</th>
                    <th style="font-size: 12px;">Protocolo</th>
                    <th style="font-size: 12px;">Data Protocollo</th>
                </tr>
            </thead>
            <tbody class="info">

            <?php
                foreach($atti as $key => $atto){
                    if($atto->FlowId == $flow[$x]->Id )
                    {
                        $descr = "";
                        switch($tipo_pagina)
                        {
                            case "atto": $descr = $atto->Atto; break;
                            case "pigno": $descr = $atto->Nome_Pignoramento; break;
                        }

            ?>
                 <tr>
                     <td style="font-size: 12px;"><?= $atto->Atto_ID; ?></td>
                     <td style="font-size: 12px;"><?= $atto->Data_Notifica; ?></td>
                    <?php if($tipo_pagina == "atto") {?>
                     <td style="font-size: 12px;"><?= ((double) $atto->Totale_Dovuto + (double) $atto->Diritto_Riscossione_Minimo); ?></td>
                     <td style="font-size: 12px;"><?= ((double) $atto->Totale_Dovuto + (double) $atto->Diritto_Riscossione_Massimo); ?></td>
                    <?php } ?>
                     <td style="font-size: 12px;"><?= $atto->Tipo_Riscossione; ?></td>
                     <td style="font-size: 8px;"><?= $atto->Info_Cartella; ?></td>
                     <td style="font-size: 12px;"><?= $atto->Cognome_Ditta; ?></td>
                     <td style="font-size: 12px;"><?= $atto->Nome; ?></td>
                     <td style="font-size: 12px;"><?= ($descr." n° ".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico); ?></td>
                     <td style="font-size: 12px;"><?= $atto->Protocollo; ?></td>
                     <td style="font-size: 12px;"><?= $atto->Data_Protocollo; ?></td>
                 </tr>
            <?php
                    }
                }
            ?>

            </tbody>

        </table>
    </div>

    <?php if($stampatore != 1 && $flow[$x]->UploadDate == null) {?>
    <div class="row" style="margin-top: 2%;">
        <div class="col-lg-offset-1 col-lg-10">
            <button type="button" class="btn btn-primary" onclick="callUpload('<?= $c; ?>','<?= $a; ?>','<?= $flow[$x]->FileName; ?>','<?= $flow[$x]->DocumentType; ?>','<?= $ID[$x]; ?>','<?= $stampatore; ?>');">Carica file su mercurio</button>
        </div>
    </div>
<?php }
    if(count($ID) > 1 && $x < (count($ID) -1) )
    {
?>
    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%;margin-top: 2%;"></div>
<?php
    }
}?>

    <script>
        function apri(link)
        {
            window.open(link);
        }

        function callUpload(c,a,fileName,documentType,ID,stampatore)
        {
            window.location.href = "Upload_File_Mercurio.php?c="+c+"&a="+a+"&stampatore="+stampatore+"&fileName="+fileName+"&docType="+documentType+"&idFlusso="+ID;
        }

        function mostraNascondi(el,icons)
        {

            var classList = $(icons).attr('class').split(/\s+/);
            $.each(classList, function(index, item) {

                if (item === 'fa-angle-down') {
                    $(icons).removeClass( "fa-angle-down" ).addClass( "fa-angle-up" );
                }
                else if(item === 'fa-angle-up'){
                    $(icons).removeClass( "fa-angle-up" ).addClass( "fa-angle-down" );
                }
            });
            //alert(el);
            //alert($("#"+el).css("display"));
            //var node = $('#subscription_popup');

            /*if(document.getElementsById(el)[0] == undefined)
            {
                if ($(".empty_"+el).is(':visible'))
                    $(".empty_"+el).fadeOut("slow", function() {});
                else
                    $(".empty_"+el).slideDown("slow", function() {});
            }
            else
            {*/
            if ($("#"+el).is(':visible'))
                $("#"+el).fadeOut("slow", function() {});
            else
                $("#"+el).slideDown("slow", function() {});
            // }

            /*switch($("."+el).css("display"))
            {
                case "none": $("."+el).slideDown("slow", function() {});/*.css("display","block"); break;
                case "block": $("."+el).fadeOut("slow", function() {});/*.css("display","none"); break;
                default: break;
            }*/
        }

        $( document ).ready(function() {
            $(".all_table").hide();
        });
    </script>



<?php include(INC."/footer.php"); ?>