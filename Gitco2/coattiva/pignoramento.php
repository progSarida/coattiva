<?php

require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";




$submenuPageNo = 6;

include_once(INC . "/header.php");
include_once(CONTROLLERS."/Pignoramento.php");


$partita_ID = $cls_help->getVar('partita');
$pignoramento_ID = $cls_help->getVar('pignoramento');
//* Pignoramento ID se non trovato reindirizzamento a pagina coazione
if(empty($pignoramento_ID)){?>
    <script>
      location.href = "coazione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    </script>
<?php  
}

$pignoramentoController = new PignoramentoController($partita_ID, $pignoramento_ID);


include_once(INC . "/menu.php");
include_once(INC . "/submenu_partita.php");
// Inclusione modale per ricerca utente-partita
include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php");


?>
<!-- ********** GESTIONE MODALI OFFCANVAS ********** -->
<script>

    //Variabili
    //var role_S = "";                                                        // Tipo ricerca ruolo
    //var owner_S = "";                                                       // Tipo ricerca intestatario
    var all_city = 0;                                                       // Ricerca su tutti i comuni
    //var code_S ="";
    //var user_entry_S = "";

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
    // Inserimento dati da modale a pagine
    function initialId(type,val){
        switch (type){
            // Inserimento dati utente in 'Gitco2/coattiva/gestione_ruolo.php'
            case "user":
            case "cf":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                break;
            // Inserimento dati partita in 'Gitco2/coattiva/gestione_partita.php'
            case "info":
            case "entry":
            case "fore":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/coazione.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento'];
                break;

            default: alert("Ricerca non trovata!"); break;
        }
    }
</script>
<style>

  .ui-tabs
  {
    background: transparent;
    border: none;
  }

  .ui-tabs .ui-tabs-nav
  {
    background: transparent;
    border: none;
    border-bottom: 2px solid #6D95D5;
  }


  .ui-tabs-anchor:active, .ui-tabs-anchor:focus{
     outline:none;
  }​

</style>

<div id="tabs" style="margin-top:0rem; min-height:50rem; text-align: left;" class="table_interna text_center">
  <div class="col-sm-12" style="background-color: whitesmoke;border-bottom: 2px solid #6D95D5;">
    <?= $pignoramentoController->showIntestazione(); ?>
  </div>
  <ul>
    <li><a href="#dettaglio">DATI</a></li>
    <li><a href="#notifiche">NOTIFICHE</a></li>
    <li><a href="#spese_accessorie">SPESE ACCESSORIE</a></li>
    <li><a href="#totali" >TOTALI</a></li>
  </ul>
  <div id="dettaglio" class="active" style="color: black;">
    <?= $pignoramentoController->showInfo(); ?>
  </div>
  <div id="totali"  style="color: black;">
    <?= $pignoramentoController->showTotali(); ?>
  </div>
  <div id="notifiche" style="color: black;">
    <?= $pignoramentoController->showNotifiche(); ?>
  </div>
  <div id="spese_accessorie" style="color: black;">
    <?= $pignoramentoController->showSpeseAccessorie(); ?>
  </div>

</div>

<script>
  $( "#tabs" ).tabs();
</script>

<?php include(INC . "/footer.php"); ?>