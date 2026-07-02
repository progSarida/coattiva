<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

$_SESSION['Popup']=1;
include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_InserimentoNelDB.php";

include_once ELAB_PIGNORAMENTI_BANCA_CLS . "/cls_PignoramentoBanche.php";

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
<script src="<?= ELAB_PIGNORAMENTI_BANCA_JS ?>/assegnazione_banche.js"></script>
<script>
var num = "";
var tipo = "";
function carica_banca(numero)
{
    //alert("window.open");
    selectParent = "banca";
    num = numero;
    
	  var link = "<?= WEB_ROOT; ?>/search/banche/ricerca_banche.php?richiesta=assegnaBancaPigno&c=*****&a=<?php echo $a; ?>";
    var width = window.screen.width * 0.58;
    var height = window.screen.height * 0.625;
    var left = (window.screen.width/2)-(width/2);
    var top = (window.screen.height/2)-(height/2);
    openWindowSearch(link,{width:width, height:height, left:(window.screen.width/2)-(width/2), top:(window.screen.height/2)-(height/2)});
	
}
function callParent(valorediritorno) {

    switch(selectParent){
    

        case "banca":

                if( valorediritorno.ID != null && valorediritorno.ID != undefined && valorediritorno.ID != "")
                {
                    if(valorediritorno.Password!="")
                    {
                        // if($('#spese_not_terzo_'+num).length==0)
                        //     aggiungi_notifica_terzo(num);

                        if(valorediritorno.Tipo_banca == "sede")
                            $('#pignorato_id_banca_'+num).val(valorediritorno.ID);
                        else if(valorediritorno.Tipo_banca == "filiale")
                            $('#pignorato_id_banca_'+num).val(valorediritorno.ID_Collegamento);

                        $('#pignorato_banca_'+num).val(valorediritorno.Denominazione);
    
                    }
                    else
                        alert("Filiale "+valorediritorno.Denominazione+" [ID "+valorediritorno.ID+"] sprovvista di password!");

                }

                break;
    }
}

</script>
<form id=form_pignoramento name=form_pignoramento class="form-horizontal validate" action="assegnazione_banche_salva.php" method=post>
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
            <option value="banca"	>Banca / Posta</option>
  				</select>
        </div>
      </div>
    </div>
  </div>
  <div class="row tr_banca" id="tr_banca_iniziale" style="margin-top: 2%;">
    <div class="col col-lg-2 col-lg-offset-1" >
        <div class="form-group">
        <div class="col-lg-12">
            <input type=button onclick="aggiungi_terzo('banca');" class="btn btn-primary form-control resize" value="Nuovo Terzo">
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
  $AssegnazioneTerzi = new AssegnazioneBanchePvt($cls_db);
  $a_terzi = $AssegnazioneTerzi->PrendiTerzi($utente_id,$elab_id);
  $count_terzi = count($a_terzi);
  for($i=0;$i<$count_terzi;$i++)
  {
    
    $AssegnazioneTerzi->Leggi($a_terzi[$i]["ID"]);
    $Nome = $AssegnazioneTerzi->PrendiDenominazione();
    $tipo = $AssegnazioneTerzi->Tipo_Titolo_Banca;
   
  ?>

  
<div class="tr_banca banca_<?php echo $i; ?>">
  <input type=hidden name="pignorato_id_banca_<?php echo $i; ?>"  id="pignorato_id_banca_<?php echo $i; ?>" value="<?php echo isset($AssegnazioneTerzi->Terzo_ID)?$AssegnazioneTerzi->Terzo_ID:null; ?>">
</div>

<div class='tr_banca banca_<?php echo $i; ?>' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>

<div class="row banca_<?php echo $i; ?> tr_banca" style="margin-top: 2%;">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 resize control-label" style="text-align: left;">Terzo</label>
      <div class="col-lg-6">
        <input class="form-control resize" style="width: 100%; background-color: rgb(153, 204, 255); border: 2px solid black;" readonly name=pignorato_banca_<?php echo $i; ?> id=pignorato_banca_<?php echo $i; ?> value="<?php echo isset($Nome) ? $Nome : null; ?>"  ondblclick="carica_banca(<?php echo $i; ?>);">
      </div>
      <div class="col-lg-2">
        <a onMouseover="title='Elimina terzo'" href='#' style='text-decoration:none;' onClick="elimina_terzo('banca',<?php echo $i; ?>,<?php echo $utente_id; ?>,<?php echo $AssegnazioneTerzi->Terzo_ID; ?>,<?php echo $elab_id; ?>,'<?php echo $c; ?>');"  >
			      <i class="fa fa-trash" style="color: red;" aria-hidden="true"></i>
				</a>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Fonte dati</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=fonte_banca_<?php echo $i; ?> id=fonte_banca_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Fonte_Dati)?$AssegnazioneTerzi->Fonte_Dati:null; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row banca_<?php echo $i; ?> tr_banca">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Tipo titolo</label>
      <div class="col-lg-8">
        <select name=tipo_titolo_<?php echo $i; ?> id=tipo_titolo_<?php echo $i; ?> class="form-control resize" onchange="scelta_titolo(<?php echo $i; ?>);">
          <option></option>
          <option <?= $tipo=="conto" ?  'selected="selected"' : '' ?> value="conto"		>Conto corrente</option>
          <option <?= $tipo=="libretto" ?  'selected="selected"' : '' ?> value="libretto"		>Libretto</option>
          <option <?= $tipo=="altro" ?  'selected="selected"' : '' ?> value="altro"		>Altro</option>
        </select>
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Note</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=note_banca_<?php echo $i; ?> id=note_banca_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Note)?$AssegnazioneTerzi->Note:null; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row banca_<?php echo $i; ?> tr_banca">
  <div class="col col-lg-5 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Titolo</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=titolo_<?php echo $i; ?> id=titolo_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Titolo_Banca)?$AssegnazioneTerzi->Titolo_Banca:null; ?>">
      </div>
    </div>
  </div>
  <div class="col col-lg-5" >
    <div class="form-group">
      <label class="col-lg-4 control-label resize " style="text-align: left;">Intestatario</label>
      <div class="col-lg-8">
        <input class="form-control resize" name=intestatario_<?php echo $i; ?> id=intestatario_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Intestatario_Banca)?$AssegnazioneTerzi->Intestatario_Banca:null; ?>">
      </div>
    </div>
  </div>
</div>

<div class="row banca_<?php echo $i; ?> tr_banca" id='tr_banca_finale_<?php echo $i; ?>'>
  <div class="col col-lg-10 col-lg-offset-1" >
    <div class="form-group">
      <label class="col-lg-2 control-label resize " style="text-align: left;">Coointestatari (*)</label>
      <div class="col-lg-10">
        <input class="form-control resize" name=coointestatari_<?php echo $i; ?> id=coointestatari_<?php echo $i; ?> value="<?php echo isset($AssegnazioneTerzi->Coointestatari_banca)?$AssegnazioneTerzi->Coointestatari_banca:null; ?>">
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