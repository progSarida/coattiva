<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

$_SESSION['Popup']=1;
include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_InserimentoNelDB.php";

include_once ELAB_PIGNORAMENTI_LAVORO_CLS . "/cls_PignoramentoLavoro.php";

$utente_id = $cls_help->getVar('utente_id');
$elab_id = $cls_help->getVar('el');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$salvato = $cls_help->getVar('salvato');

if(isset($salvato))
  if($salvato)
  {
    ?>
    <script>
        window.opener.location.reload();
        window.close();
    </script>
    <?php
    die;
  }
?>
<script src="<?= ELAB_PIGNORAMENTI_LAVORO_JS ?>/assegnazione_terzi.js"></script>
<script>
var num = "";
var tipo = "";
function carica_utente(numero, tipoTerzo)
{

    selectParent = "carica_utente";
    num = numero;
    tipo = tipoTerzo;
    
    var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ditta&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    
    var width = window.screen.width * 0.58;
    var height = window.screen.height * 0.625;
    var left = (window.screen.width/2)-(width/2);
    var top = (window.screen.height/2)-(height/2);
    
    openWindowSearch(stringa,{width:width, height:height, left:(window.screen.width/2)-(width/2), top:(window.screen.height/2)-(height/2)});

}
function callParent(valorediritorno) {

switch(selectParent){
  

    case "carica_utente":

        if(valorediritorno.p == "<?php echo $utente_id; ?>")
        {
            alert("Impossibile caricare il pignorato come terzo!");
            $('#pignorato_'+tipo+'_'+num).val('');
            $('#pignorato_id_'+tipo+'_'+num).val('0');
            $('#denom_terzo_'+num).text('');

            if($('#spese_not_terzo_'+num).length>0)
            {
                $('#spese_not_terzo_'+num).val('');
                $('#scelta_can_cad_terzo_'+num).val('');
                //update_notifica_terzi();
            }
            return;
        }

        if( valorediritorno.p != null && valorediritorno.p != undefined && valorediritorno.p != "")
        {
            $.ajax({
                type: "POST",
                async: false,
                url: "ajax/ajax_partita.php?c=<?php echo $c; ?>",
                data: {
                    ajax: "nome",
                    ID: valorediritorno.p,
                },
                success: function(nome) {

                    if($('#spese_not_terzo_'+num).length==0)
                        //aggiungi_notifica_terzo(num);
                    console.log(nome);
                    $('#pignorato_'+tipo+'_'+num).val(nome);
                    $('#pignorato_id_'+tipo+'_'+num).val(valorediritorno.p);
                    
                }
            });

        }
        else
        {
            alert("Errore nel caricamento dell'utente! \n\nPer inserire un nuovo utente utilizzare l'Anagrafe\n ");
            $('#pignorato_'+tipo+'_'+num).val('');
            $('#pignorato_id_'+tipo+'_'+num).val('0');
            $('#denom_terzo_'+num).text('');

            if($('#spese_not_terzo_'+num).length>0)
            {
                $('#spese_not_terzo_'+num).val('');
                $('#scelta_can_cad_terzo_'+num).val('');
                //update_notifica_terzi();
            }
        }

        break;
}
}

</script>

<!-- Inclusione modali -->
<?php include_once (ROOT."/search_modal/offcanvas/company_offcanvas.php"); ?>
<?php //include_once (ROOT."/search_modal/startAjax.php"); ?>

<script>
var selectRif = "";
var all_city = "n"; 
//Apertura modale modifica campo
function openOfcanvas(id_off,rif){
  selectRif = rif;
    // Reset campi input
    $('#company_name').val("");
    $('#company_cf').val("");

    // Reset spazi tabella
    $('#appendTableCompany').empty();

    flagAQjaxReserch = true;
    switch (id_off){
        case 'companySearchModal':
            //Inizializzazione dati per ricerca ditta
            all_city = 'n';
            $("#ins_c_cf").hide();
            $("#ins_c_name").show();
            document.getElementById('check_c_name').checked = true;
            document.getElementById('check_c_cf').checked = false;
            $('#companySearchModal').modal('show');
            break;
        default:
            break;
    }
}

function initialId(tipo,val){
        //alert("initial --> "+tipo);
        flagAQjaxReserch = false;
        switch(tipo)
        {
            // Inserimento dati da modale
            case 'company_n':
            case 'company_cf':
              if ($.isEmptyObject(val)===false){
                if($('#spese_not_terzo_'+selectRif).length==0)
                  $('#pignorato_lavoro_'+selectRif).val(val['Utente']);
                  $('#pignorato_id_lavoro_'+selectRif).val(val['ID']);
              }
              break;
            default: alert("Ricerca non trovata!"); break;
        }

    }

</script>


<form id=form_pignoramento name=form_pignoramento class="form-horizontal validate" action="assegnazione_terzi_salva.php" method=post>
<input name=invia_submit  id=invia_submit	type=hidden	value="Salva" >
<input type=hidden name=utente_id value="<?php echo $utente_id; ?>" >
<input type=hidden name=elab_id value="<?php echo $elab_id; ?>" >
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=conta_terzi id=conta_terzi >

<div class="row tr_terzi">
    <div class="col col-lg-5 col-lg-offset-1" >
      <div class="form-group">
        <label class="col-lg-4 control-label resize " style="text-align: left;">Presso</label>
        <div class="col-lg-8">
          <select name=presso_terzi id=presso_terzi class="form-control resize" onchange="">
  					<!-- <option></option> -->
  					<option value="lavoro"	>Datore di lavoro</option>
  					<!-- <option value="banca"	>Banca / Posta</option>
  					<option value="inps"	>Istituti previdenziali</option>
  					<option value="altro"	>Altri terzi</option> -->
  				</select>
        </div>
      </div>
    </div>
  </div>
  <div class="row tr_lavoro" id="tr_lavoro_iniziale" style="margin-top: 2%;">
    <div class="col col-lg-2 col-lg-offset-1" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Presso</label>-->
        <div class="col-lg-12">
          <input type=button onclick="aggiungi_terzo('lavoro');" class="btn btn-primary form-control resize" value="Nuovo Terzo">
        </div>
      </div>
    </div>
    <div class="col col-lg-3" >
      <div class="form-group">
        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Ric. banche</label>-->
        <div class="col-lg-12">
          <p class="resize" >Clicca per poter inserire un nuovo terzo da pignorare</p>
        </div>
      </div>
    </div>
  </div>

  <?php //class=tr_lavoro
  $AssegnazioneTerzi = new AssegnazioneTerzoPvt($cls_db);
  $a_terzi = $AssegnazioneTerzi->PrendiTerzi($utente_id,$elab_id);
  $count_terzi = count($a_terzi);
  for($i=0;$i<$count_terzi;$i++)
  {
    
    $AssegnazioneTerzi->Leggi($a_terzi[$i]["ID"]);
    $Nome = $AssegnazioneTerzi->PrendiNomeCognome();
    $tipo = $AssegnazioneTerzi->Tipo_Contratto_Lavoro;
    
    //Tipo_contratto ?
   
  ?>
<!-- DA QUI  CLASS tr_lavoro -->
<div class="tr_lavoro lavoro_<?php echo $i; ?>">
  <input type=hidden name="pignorato_id_lavoro_<?php echo $i; ?>" id="pignorato_id_lavoro_<?php echo $i; ?>"	value="<?php echo isset($AssegnazioneTerzi->Terzo_ID)?$AssegnazioneTerzi->Terzo_ID:null; ?>" 	>
</div>

<div class='tr_lavoro lavoro_<?php echo $i; ?>' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> " style="margin-top: 2%;">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Terzo</label>
      <div class="col-lg-8">
        <input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=pignorato_lavoro_<?php echo $i; ?> id=pignorato_lavoro_<?php echo $i; ?> value="<?php echo isset($Nome) ? $Nome : null; ?>" ondblclick="/*carica_utente( <?php echo $i; ?> , 'lavoro');*/openOfcanvas('companySearchModal',<?php echo $i; ?>);">
      </div>
    </div>
  </div>
  <div class="col col-lg-1" >
    <div class="form-group">
      <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Ric. banche</label>-->
      <div class="col-lg-12">
        <a onMouseover="title='Elimina terzo'" href='#' style='text-decoration:none;' onClick="elimina_terzo('lavoro',<?php echo $i; ?>,<?php echo $utente_id; ?>,<?php echo $AssegnazioneTerzi->Terzo_ID; ?>,<?php echo $elab_id; ?>,'<?php echo $c; ?>');" >
				<i class="fa fa-trash" style="color: red;" aria-hidden="true"></i>
				</a>
      </div>
    </div>
  </div>
</div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> ">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Azienda</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=azienda_lavoro_<?php echo $i; ?> id=azienda_lavoro_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Azienda)?$AssegnazioneTerzi->Azienda:null; ?>" >
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=fonte_lavoro_<?php echo $i; ?> id=fonte_lavoro_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Fonte_Dati)?$AssegnazioneTerzi->Fonte_Dati:null; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> ">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo contratto</label>
      <div class="col-lg-8">
        <select name=tipo_contratto_<?php echo $i; ?> id="tipo_contratto_<?php echo $i; ?>" class="form-control resize" onchange="scelta_contratto(<?php echo $i; ?>);">
					<option></option>
					<option <?= $tipo=="titolare" ?  'selected="selected"' : '' ?> value="titolare"		>Titolare</option>
					<option <?= $tipo=="accessorio" ?  'selected="selected"' : '' ?> value="accessorio"		>Accessorio</option>
					<option <?= $tipo=="apprendistato" ?  'selected="selected"' : '' ?> value="apprendistato"	>Apprendistato</option>
					<option <?= $tipo=="chiamata" ?  'selected="selected"' : '' ?> value="chiamata"		>Chiamata</option>
					<option <?= $tipo=="collaborazione" ?  'selected="selected"' : '' ?> value="collaborazione"	>Collaborazione</option>
					<option <?= $tipo=="determinato" ?  'selected="selected"' : '' ?> value="determinato"		>Determinato</option>
					<option <?= $tipo=="indeterminato" ?  'selected="selected"' : '' ?> value="indeterminato"	>Indeterminato</option>
					<option <?= $tipo=="inserimento" ?  'selected="selected"' : '' ?> value="inserimento"		>Inserimento</option>
					<option <?= $tipo=="interinale" ?  'selected="selected"' : '' ?> value="interinale"		>Interinale</option>
					<option <?= $tipo=="occasionale" ?  'selected="selected"' : '' ?> value="occasionale"		>Occasionale</option>
					<option <?= $tipo=="progetto" ?  'selected="selected"' : '' ?> value="progetto"		>Progetto</option>
					<option <?= $tipo=="ripartito" ?  'selected="selected"' : '' ?> value="ripartito"		>Ripartito</option>
					<option <?= $tipo=="somministrazione" ?  'selected="selected"' : '' ?> value="somministrazione">Somministrazione</option>
					<option <?= $tipo=="parziale" ?  'selected="selected"' : '' ?> value="parziale"		>Tempo parziale</option>
					<option <?= $tipo=="altro" ?  'selected="selected"' : '' ?> value="altro"			>Altro</option>
				</select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Note</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=note_lavoro_<?php echo $i; ?> id=note_lavoro_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Note)?$AssegnazioneTerzi->Note:null; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row tr_lavoro lavoro_<?php echo $i; ?> " id='tr_lavoro_finale_<?php echo $i; ?>'>
  <div class="col col-lg-4 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Data costituz. ditta</label>
      <div class="col-lg-8">
        <input class="picker text_center form-control resize validateCustom vld_Custom_date" style="width: 50%;" name=data_costituzione_<?php echo $i; ?> id=data_costituzione_<?php echo $i; ?> value="<?php echo $AssegnazioneTerzi->Data_Costituzione_Ditta_Lavoro; ?>" size=10>
      </div>
    </div>
  </div>
  <div class="col col-lg-3" >
    <div class="form-group">
      <label class="col-lg-6 control-label resize " style="text-align: left;">Data ditta operativa</label>
      <div class="col-lg-6">
        <input class="picker text_center form-control resize validateCustom vld_Custom_date" name=data_operativa_<?php echo $i; ?> id=data_operativa_<?php echo $i; ?> value="<?php echo $AssegnazioneTerzi->Data_Ditta_Operativa_Lavoro; ?>" size=10>
      </div>
    </div>
  </div>
  <div class="col col-lg-3" >
    <div class="form-group">
      <label class="col-lg-6 control-label resize " style="text-align: left;">Data dipendenze</label>
      <div class="col-lg-6">
        <input class="picker text_center form-control resize validateCustom vld_Custom_date" name=data_dipendenze_<?php echo $i; ?> id=data_dipendenze_<?php echo $i; ?> value="<?php echo $AssegnazioneTerzi->Data_Dipendenze_Lavoro; ?>" size=10>
      </div>
    </div>
  </div>
</div>

<?php
}
?>
<div class="form-group">
	<button type="submit" id="submitButton" class="btn btn-primary" style="display: none;" value="Submit"></button>
</div>
</form>
<?php include(INC."/footer.php"); ?>