<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");


if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";

$comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");// new ente_gestito($c);
$nome_comune = $comune["Denominazione"];

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$ruolo = array();
$query = "SELECT * FROM ruolo WHERE CC ='".$c."' ORDER BY Data_Inserimento DESC LIMIT 5";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));// safe_query($query);
//$num_ruoli = $cls_db->getNumberRow($result);// mysql_num_rows($result);

//echo "<h1>Numero righe --> ".count($result)."</h1>";

for($y=0;$y<count($result); $y++)
{
	//$val = $result[$y];
    $query = "SELECT * FROM ruolo WHERE ID = '".$result[$y]['ID']."' AND CC = '".$c."'";
	$ruolo[$y] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo");// new ruolo($result[$y]['ID'],$c,$a);
}

flush();
ob_flush();

?>



<script>

function canc_ruolo(ruolo)
{
	$.ajax({
        url: "ajax/ajax_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>",
        data: { ajax: "bonifica" , id_ruolo: ruolo },
        type: "POST",
        async: false,
        success: function(value) {
            
			alert(value);
            location.href = "preimportazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        }
    });
			
}


</script>


<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font22 under_decor">Bonifica Ruolo</span>
    </div>
</div>
<div class="row" style="margin-top: 5%;">
    <div class="col-lg-10 col-lg-offset-1" style="color: red;">
        <br>IMPORTANTE<br><br>
        Nel caso di interruzioni durante la procedura di importazione � necessario controllare se � stato creato un Ruolo parzialmente.
        In tale caso si deve provvedere alla cancellazione del ruolo stesso.<br><br>
        ESEGUIRE LE SEGUENTI OPERAZIONI:<br>
        - Cancellare il ruolo con la data e l'ora corrispondenti a quelle del ruolo parziale inserito.<br>
        - Successivamente ritentare l'inserimento tornando alla pagina di preinserimento del 290.<br><br>
        Di seguito sono elencati gli ultimi ruoli creati per data decrescente:<br>
    </div>
</div>

<div class="row" style="margin-top: 5%;">
    <div class="col-lg-10 col-lg-offset-1">
        <table class="table_interna text_center">
        <?php
            for($y=0;$y<count($ruolo);$y++)
            { ?>
                <tr>
                    <td class="text_left width30">Ruolo <?php echo $ruolo[$y]["ID"]; ?>: <?php echo $ruolo[$y]["Data_Inserimento"]; ?></td>
                    <td class="text_left"><input type=button class="btn btn-danger" value="Cancella Ruolo" onclick="canc_ruolo(<?php echo $ruolo[$y]["ID"]; ?>);"></td>
                </tr>

        <?php } ?>
        </table>
    </div>
</div>

<?php include(INC."/footer.php"); ?>