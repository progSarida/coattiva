<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include (INC."/menu.php");

include_once CLS . "/cls_LOG.php";

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$ArrID = json_decode($cls_help->getVar("idPigno"));
$DocumentType = $cls_help->getVar("tipo");

$log = new LOG();
//var_dump($ArrID);
try{
    for($i=0; $i < count($ArrID); $i++)
    {
        $flag = false;
        $queryPigno = "SELECT P.ID, P.Anno_Cronologico, P.ID_Cronologico, D.Description
        FROM pignoramento_generale AS P 
        JOIN document_type AS D ON D.Id = P.DocumentTypeId
        WHERE P.ID = ".$ArrID[$i]->ID_Pigno;

        $pigno = $cls_db->getArrayLine($cls_db->ExecuteQuery($queryPigno));

        if(!$pigno)
            $log->error("Nessun pignoramento trovato! Problemi nel passaggio di dati con la pagina invia_PEC.php");

        $queryMail = "SELECT E.Oggetto, E.Mail_Destinatario, E.Data_Invio, E.Ricevuta_Accettazione, E.Ricevuta_Consegna
        FROM notifica_atto AS N 
        JOIN email_inviate AS E ON N.ID = E.ID_Collegato
        WHERE N.Atto_Notificato_ID = ".$ArrID[$i]->ID_Pigno;

        $mail = $cls_db->getResults($cls_db->ExecuteQuery($queryMail));

        if(!$mail)
            $log->error("Nessun mail trovata! Problemi nel passaggio di dati con la pagina invia_PEC.php");

        $result["pigno"][] = $pigno;
        $result["mail"][] = $mail;

    }
}
catch(Exception $ex)
{
    $log->error("Alla riga ".$ex->getLine().".\nCodice: ".$ex->getCode().".\nErrore: ".$ex->getMessage());
}
catch (mysqli_sql_exception $ex){
    $log->error("Alla riga ".$ex->getLine().".\nCodice: ".$ex->getCode().".\nErrore: ".$ex->getMessage());
}
catch(BadMethodCallException $ex){
    $log->error("Alla riga ".$ex->getLine().".\nCodice: ".$ex->getCode().".\nErrore: ".$ex->getMessage());
}

//var_dump($result);
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

<script>
    $( document ).ready(function() {
        $(".all_table").hide();
    });

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

        if ($("#"+el).is(':visible'))
            $("#"+el).fadeOut("slow", function() {});
        else
            $("#"+el).slideDown("slow", function() {});
    }
</script>
<div class="row">
    <div class="col-lg-10 col-lg-offset-1">
        <a type="button" class="btn btn-primary" style="width: 100%; margin-bottom: 1%;" href="<?= WEB_ROOT; ?>/controlli/controlla_mail.php?docType=<?= $DocumentType; ?>&c=<?= $c; ?>&a=<?= $a; ?>">Controlla Accettazione/Consegna</a>
    </div>
</div>
<div class="row">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="tableFixHead" style="overflow-y: auto; min-height: 65vh !important;margin-top: 0;">
            <table class="table" style="width: 98%; margin-left: 1%; border-bottom: 1px solid black;">
                <colgroup>
                    <col style="width: 10%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 60%;">
                </colgroup>
                <thead style="background-color:#8C9AFF; color: white; font-size: 12px;">
                <tr>
                    <th>ID</th>
                    <th>Crono ID</th>
                    <th>Crono Anno</th>
                    <th>Nome Pignoramento</th>
                </tr>
                </thead>
                <tbody class="info">

                <?php
                for($i = 0; $i < count($result["pigno"]); $i++){
                    ?>
                    <tr style="font-size: 10px;">
                        <td><?= $result["pigno"][$i]["ID"]; ?></td>
                        <td><?= $result["pigno"][$i]["ID_Cronologico"]; ?></td>
                        <td><?= $result["pigno"][$i]["Anno_Cronologico"]; ?></td>
                        <td><?= $result["pigno"][$i]["Description"]; ?></td>
                    </tr>
                    <tr style="background-color: #757CFF; border-right: 1px solid black; border-left: 1px solid black; border-top: 0; border-bottom: 0; width: 100%;">
                        <td colspan="4" style="margin-bottom: 0; padding-bottom: 0; border-top: 0; border-bottom: 0;">
                            <div>
                                <div style="float:left; color: white;">Dettaglio Mail inviate <?= $result["pigno"][$i]["ID_Cronologico"]."/".$result["pigno"][$i]["Anno_Cronologico"]; ?></div>
                                <div style="float: right; color: white;"><i class="fa fa-angle-down" aria-hidden="true" style="cursor: pointer;" onclick="mostraNascondi('table_<?= $i; ?>',this);"></i></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="margin-top: 0; padding-top: 0;">
                            <table class="table all_table" id="table_<?= $i; ?>">
                                <colgroup>
                                    <col style="width: 40%;">
                                    <col style="width: 30%;">
                                    <col style="width: 10%;">
                                    <col style="width: 10%;">
                                    <col style="width: 10%;">
                                </colgroup>
                                <thead style="background-color:#8C9AFF; color: white;">
                                    <tr style="font-size: 12px;">
                                        <th>Oggetto</th>
                                        <th>Mail Destinatario</th>
                                        <th>Data Invio</th>
                                        <th>Ric. Accet.</th>
                                        <th>Ric. Cons.</th>
                                    </tr>
                                </thead>
                                <tbody class="info">
                                <?php for($x = 0; $x < count($result["mail"][$i]); $x++){ ?>
                                    <tr style="font-size: 10px;">
                                        <td><?= $result["mail"][$i][$x]["Oggetto"]; ?></td>
                                        <td><?= $result["mail"][$i][$x]["Mail_Destinatario"]; ?></td>
                                        <td><?= $result["mail"][$i][$x]["Data_Invio"]; ?></td>
                                        <td><?= $result["mail"][$i][$x]["Ricevuta_Accettazione"]; ?></td>
                                        <td><?= $result["mail"][$i][$x]["Ricevuta_Consegna"]; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <?php
                }
                ?>

                </tbody>

            </table>
        </div>
    </div>
</div>

