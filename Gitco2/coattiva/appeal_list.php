<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

$submenuPageNo = 5;
$pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';


include(INC."/header.php");
include(INC."/menu.php");
include(INC."/submenu_partita.php");

include(CLS."/cls_registry.php");
include(CLS."/cls_appeal.php");

$linkF6 = "";

if($partita_ID!=null){
    //$cls_help->alert($partita_ID);
    $cls_appeal = new cls_appeal();
    $a_appeals = $cls_db->getResults($cls_db->SelectQuery($cls_appeal->getAllAppeal_query($partita_ID)));
    $linkF6 = WEB_ROOT."/coattiva/appeal.php?c=".$c."&a=".$a."&partita=".$partita_ID;
    if(count($a_appeals)>0)
        $linkF6.="&lastAppeal=".$a_appeals[count($a_appeals)-1]['ID'];
}
else
    $a_appeals = array();

?>

    <!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>

    <!-- ********** GESTIONE MODALI OFFCANVAS ********** -->
    <script>
        // Apertura modale
        function openOfcanvas(type,rif){
            // Reset campi input
            $('.user_entry').val("");

            // Reset spazi tabella
            $('#appendTableUserEntry').empty();

            selectRif = rif;
            switch (type){
                case 'user_entry':
                    // Setta stato checkbox iniziale
                    document.getElementById('check_u_n').checked = true;
                    document.getElementById('check_u_c').checked = false;
                    document.getElementById('check_e_cA').checked = false;
                    document.getElementById('check_e_cP').checked = false;
                    document.getElementById('check_e_i').checked = false;
                    // Setta titolo modale iniziale
                    $("#userEntrySearchModalLabel_u").show();
                    $("#userEntrySearchModalLabel_e").hide();
                    // Setta campo input iniziale
                    $("#ins_u_n").show();
                    $("#ins_u_c").hide();
                    $("#ins_e_cA").hide();
                    $("#ins_e_cP").hide();
                    $("#ins_e_i").hide();
                    // Setta tipop di ricerca iniziale
                    //user_entry_S = "user_n";
                    // Apre modale
                    $('#userEntrySearchModal').modal('show');
                    break;
            }
        }
        // Iserimento dati da modale a pagine
        function initialId(type,val){
            switch (type){
                case "user":
                case "cf":
                    top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    break;
                // Inserimento dati partita in 'Gitco2/coattiva/gestione_partita.php'
                case "info":
                case "entry":
                case "fore":
                    top.location.href="<?= WEB_ROOT; ?>/coattiva/appeal_list.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento'];
                    break;

                default: alert("Ricerca non trovata!"); break;
            }
        }
    </script>

<script>
    //F5
    switchMenuImg("F6");
    F6_button = function(){
        top.location.href = "<?php echo $linkF6;?>";
    }

</script>

<?php
include_once(INC."/pages_authorization.php");
?>

<?php if(count($a_appeals)>0)
{?>

    <table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">

        <tr class="text_left riga_dispari" style="height:30px;" >
            <td class="width5"></td>
            <td class="text_center width7"><b>Grado</b></td>
            <td class="text_center width30"><b>Atto</b></td>
            <td class="text_center width14"><b>Data reg.</b></td>
            <td class="text_center width14"><b>Data chius.</b></td>
            <td class="text_center width30"><b>Autorita'</b></td>
        </tr>

<?php

    for($i=0; $i<count($a_appeals); $i++)
    {
        $y = $i;

        if ($y++ % 2)
            $stile_riga = 'class="riga_dispari text_left"';
        else
            $stile_riga = 'class="riga_pari text_left"';

        switch($a_appeals[$i]['Authority_Type']){
            case "giudice":         $a_appeals[$i]['Authority_Type'] = "Giudice di Pace"; break;
            case "tribunale":       $a_appeals[$i]['Authority_Type'] = "Tribunale";   break;
            case "comm_trib_prov":  $a_appeals[$i]['Authority_Type'] = "Commissione Tributaria Provinciale";  break;
            case "comm_trib_reg":   $a_appeals[$i]['Authority_Type'] = "Commissione Tributaria Regionale";    break;
            case "appello":         $a_appeals[$i]['Authority_Type'] = "Corte d'Appello"; break;
            case "cassazione":      $a_appeals[$i]['Authority_Type'] = "Corte di Cassazione"; break;
        }
        $a_appeals[$i]['Authority_Description'] = $a_appeals[$i]['Authority_Type'];

        $link = WEB_ROOT."/coattiva/appeal.php?c=".$c."&a=".$a."&partita=".$partita_ID."&Appeal_ID=".$a_appeals[$i]['ID'];

        ?>

        <tr <?php echo $stile_riga; ?>>
            <td class="text_center">
                <a onMouseover="title='Dettagli ricorso'" href="#" style="text-decoration:none;" onClick="location.href='<?=$link;?>'" >
                    <img src="<?=IMG."/select_arrow.png";?>" style="width:25px; height:25px; border:0;" >
                </a>
            </td>
            <td class="text_center"><?php echo $a_appeals[$i]['Court_Level']; ?>&deg;</td>
            <td class="text_center"><?php echo $a_appeals[$i]['Atto']." n. ".$a_appeals[$i]['ID_Cronologico']." del ".$a_appeals[$i]['Anno_Cronologico'];?></td>
            <td class="text_center"><?php echo $cls_help->toItalianDate($a_appeals[$i]['Start_Date']); ?></td>
            <td class="text_center"><?php echo $cls_help->toItalianDate($a_appeals[$i]['End_Date']); ?></td>
            <td class="text_center"><?php echo $a_appeals[$i]['Authority_Description'] ?></td>
        </tr>
        <tr <?php echo $stile_riga; ?>>
            <td class="text_left"></td>
            <td class="text_center" colspan="5">
                <span class="color_titolo"><?php echo $a_appeals[$i]['Notes'];?></span>
            </td>
        </tr>
        <tr <?php echo $stile_riga; ?>>
            <td class="text_left"></td>
            <td class="text_center" colspan="5">
            </td>
        </tr>

        <?php }?>
    </table>

<?php }
else{
    ?>
    <div class="row justify-content-md-center " style="margin-top: 3%;">
    	<div class="col col-md-auto text_center">
    			<span class="titolo text_center" >Nessun ricorso presente!</span>
    	</div>
    </div>

<?php
}

include(INC."/footer.php");
