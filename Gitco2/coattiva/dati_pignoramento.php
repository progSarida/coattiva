<?php

require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

$submenuPageNo = 6;

include_once(INC . "/header.php");
include_once(CONTROLLERS."/DatiPignoramento.php");


$partita_ID = $cls_help->getVar('partita');

$ctrl_DatiPigno = new DatiPignoramentoController($partita_ID);


include_once(INC . "/menu.php");
include_once(INC . "/submenu_partita.php");


?>

<!-- Inclusione modale per ricerca banca -->
<?php include_once (ROOT."/search_modal/offcanvas/bank_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca datore di lavoro -->
<?php include_once (ROOT."/search_modal/offcanvas/company_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>

<script>
//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

var selectRif = '';
// Modali offcanvas
// Apertura modale
function openOfcanvas(type,rif){
    switch (type){
    case 'user_entry':
        // Reset campi input
        $('.user_entry').val("");

        // Reset spazi tabella
        $('#appendTableUserEntry').empty();

        selectRif = rif;
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
    case 'bankSearchModalH':                                        // ricerca solo sede
        // Reset campi input
        $('#bank_n').val("");
        // Gestione radio
        $('#bank_headq').prop('checked', true);
        $('#bank_branch').prop('checked', false);
        $('#bank_branch').attr("disabled", true);                   // blocca radio filiale
        // Reset spazi tabella
        $('#appendTableBank').empty();
        // Apertura modale
        selectRif = rif;
        $('#bankSearchModal').modal('show');
        break;
      case 'companySearchModal':
          // Reset campi input
        $('#company_name').val("");
        $('#company_cf').val("");
        // Reset spazi tabella
        $('#appendTableCompany').empty();
        // Apertura modale
        selectRif = rif;
        //Inizializzazione dati per ricerca ditta
        all_city = 'n';
        $("#ins_c_cf").hide();
        $("#ins_c_name").show();
        document.getElementById('check_c_name').checked = true;
        document.getElementById('check_c_cf').checked = false;
        $('#companySearchModal').modal('show');
        break;
    }
}
// Inserimento dati selezionati da modale
function initialId(tipo,val){
  switch (tipo){
    // Inserimento dati utente in 'Gitco2/coattiva/gestione_ruolo.php'
    case "user":
    case "cf":
        top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
      break;
    // Inserimento dati partita in 'Gitco2/coattiva/gestione_partita.php'
    case "info":
    case "entry":
    case "fore":
        top.location.href="<?= WEB_ROOT; ?>/coattiva/dati_pignoramento.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento']+"&pageCalled=<?=$cls_help->getVar("pageCalled")?>";
      break;
    // Inserimento dati banca
    case 'bank_headq':
    case 'bank_branch':
      if(val['Password']!="")
      {
        if(val['Tipo_Banca'] == "sede")
          $('#Banca_ID_'+selectRif).val(val['ID']);
        else
          $('#Banca_ID_'+selectRif).val(val['ID_Collegamento']);
        
        $('#Banca_'+selectRif).val(val['Denominazione']);
      }
      else
          alert("Filiale "+val['Denominazione']+" [ID "+val['ID']+"] sprovvista di password!");
      break;
    // Inserimento dati datore di lavoro
    case 'company_n':
    case 'company_cf':
      if ($.isEmptyObject(val)===false){
        $('#DatoreLavoro_'+selectRif).val(val['Utente']);
        $('#DatoreLavoro_ID_'+selectRif).val(val['ID']);
      }
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


  .ui-tabs-anchor:active, .ui-tabs-anchor:focus
  {
     outline:none;
  }​

</style>


<div id="tabs" style="margin-top:0rem; min-height:50rem; text-align: left;" class="table_interna text_center">
  <div class="col-sm-12 text-center" style="background-color: whitesmoke;border-bottom: 2px solid #6D95D5;">
      <b>GESTIONE DATI PIGNORAMENTO</b>
  </div>
  <ul>
    <li><a href="#documentType">TIPO DOCUMENTO</a></li>
    <li><a href="#datore_lavoro">DATORI DI LAVORO</a></li>
    <li><a href="#banca">BANCHE</a></li>
    <li><a href="#immobile">IMMOBILI</a></li>
  </ul>
  <div id="documentType" class="active" style="color: black;">
    <?= $ctrl_DatiPigno->showDocumentTypePignoramento(); ?>
  </div>
  <div id="datore_lavoro" class="active" style="color: black;">
    <?= $ctrl_DatiPigno->showDatoriLavoro(); ?>
  </div>
  <div id="banca"  style="color: black;">
    <?= $ctrl_DatiPigno->showBanche(); ?>
  </div>
  <div id="immobile"  style="color: black;">
    <?= $ctrl_DatiPigno->showImmobili(); ?>
  </div>
</div>

<script>
function callParent(valorediritorno){
    switch(selectParent){
      
        case "carica_utente":

            if(valorediritorno.p == "<?= $ctrl_DatiPigno->Utente_ID; ?>")
            {
                alert("Impossibile caricare il pignorato come terzo!");
                $('#DatoreLavoro_'+num).val('');
                $('#DatoreLavoro_ID_'+num).val('0');


                return;
            }

            if( valorediritorno.p != null && valorediritorno.p != undefined && valorediritorno.p != "")
            {
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "ajax/ajax_partita.php?c=<?= $c; ?>",
                    data: {
                        ajax: "nome",
                        ID: valorediritorno.p,
                    },
                    success: function(nome) {

                        $('#DatoreLavoro_'+num).val(nome);
                        $('#DatoreLavoro_ID_'+num).val(valorediritorno.p);
                        
                    }
                });

            }
            else
            {
                alert("Errore nel caricamento dell'utente! \n\nPer inserire un nuovo utente utilizzare l'Anagrafe\n ");
                $('#DatoreLavoro_'+num).val('');
                $('#DatoreLavoro_ID_'+num).val('0');

            }

            break;

            case "caricaBanca":

            if( valorediritorno.ID != null && valorediritorno.ID != undefined && valorediritorno.ID != "")
            {
                if(valorediritorno.Password!="")
                {

                    if(valorediritorno.Tipo_banca == "sede")
                        $('#Banca_ID_'+num).val(valorediritorno.ID);
                    else if(valorediritorno.Tipo_banca == "filiale")
                        $('#Banca_ID_'+num).val(valorediritorno.ID_Collegamento);

                    $('#Banca_'+num).val(valorediritorno.Denominazione);

                }
                else
                    alert("Filiale "+valorediritorno.Denominazione+" [ID "+valorediritorno.ID+"] sprovvista di password!");

            }

            break;
    }
  } 
  
  $( "#tabs" ).tabs();
</script>




<?php include(INC . "/footer.php"); ?>