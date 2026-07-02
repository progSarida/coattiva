<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include(CLS."/cls_ente.php");

if(!isset($_SESSION['username']))
{
    header("Location: /gitco2/autenticazione/accesso_negato.php");
    die;
}

$tipo_riscossione = $cls_help->getVar('tipo_riscossione');


//phpinfo();
$cls_ente = new cls_ente($a_enteAdmin);
$a_img = $cls_ente->getStemmaImgArray();
if($a_img===false){
    $cls_help->alert("Default image non trovata!");
    die;
}


?>

<script>

    $(document).ready(function(){

    if("<?php echo $a_img[1]['dim']['width']; ?>">0)
        dimensiona_magnify("4", "<?php echo $a_img[1]['dim']['width']; ?>" , "<?php echo $a_img[1]['dim']['height']; ?>" , 150, 150 );
    if("<?php echo $a_img[2]['dim']['width']; ?>">0)
        dimensiona_magnify("2", "<?php echo $a_img[2]['dim']['width']; ?>" , "<?php echo $a_img[2]['dim']['height']; ?>" , 150, 150 );
    if("<?php echo $a_img[3]['dim']['width']; ?>">0)
        dimensiona_magnify("3", "<?php echo $a_img[3]['dim']['width']; ?>" , "<?php echo $a_img[3]['dim']['height']; ?>" , 150, 150 );

    });

    function DeleteGet(flagDel)
    {
        $("#flagDel").val(flagDel);
        switch(flagDel)
        {
            case 1: $("#imgName").val("<?=$a_img[1]['filename'];?>");    break;
            case 2: $("#imgName").val("<?=$a_img[2]['filename'];?>");    break;
            case 3: $("#imgName").val("<?=$a_img[3]['filename'];?>");    break;
        }

        control = submit_buttons('Delete');
        if(control){
            $("#form_stemma").submit();
        }
    }


    switchMenuImg("F3");
    F3_button = function()
    {
    control = submit_buttons('Salva');
    if(control)
        $("#btnSub").trigger("click");
    }

    switchMenuImg("F5");
    F5_button = function()
    {
        location.href="stemma.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Stemma.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Stemmi</b>");
        $("#helpModal").modal('show');

    }

    //PAG GIU
    switchMenuImg("pagedown");
    pagedown_button = function(){
    if( modifica == 0 )
    {
        location.href = "ufficio.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }
    else
        alert("salvare i dati o annullare prima di procedere");
    }

    //PAG SU
    switchMenuImg("pageup");
    pageup_button = function(){
    if( modifica == 0 )
    {
        location.href = "dati_ente.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }
    else
        alert("salvare i dati o annullare prima di procedere");
    }

</script>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Stemma</p>
	</div>
</div>

<form name=form_stemma id=form_stemma method=post action="stemma_salva.php" enctype="multipart/form-data">

<input type=hidden name=invia_submit id=invia_submit value="" >
<input type=hidden name=flagDel id=flagDel value="" >
<input type=hidden name=imgName id=imgName value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC value=<?php echo $a_enteAdmin['CC']; ?> >
<input type=hidden name=Gestore_ID value=<?php echo $a_enteAdmin['Gestore_ID']; ?> >

<div class="row">
  <div class="col col-lg-6 col-lg-offset-1">
    <div class="form-group" >
			<label class="col-lg-4 control-label resize" style="text-align: left;">Principale</label>
			<div class="col-lg-8" >
          <input class="form-control resize" type="file" name="Stemma_1" value="Carica immagine" style="width: 100%; background-color: rgb(153, 204, 255);">
			</div>
    </div>
  </div>
  <div class="col-lg-2">
    <div id=mostra_immagine class="image-magnify4 resize" title="Clicca per allargare immagine" onclick="window.open('<?php echo $a_img[1]['webpath']; ?>')">
			<div class="thumbnail4 text_center">
				<img id="thumbnail_image4" src="<?php echo $a_img[1]['webpath']; ?>" border="1>">
				<div class="popup4"></div>
			</div>
		</div>
  </div>
  <div class="col-lg-2 resize">
    <?php if($a_img[1]['flag']) : ?>
      <button type="button" class="btn btn-danger px-3" id="DeleteStemma_1"  onclick="DeleteGet(1)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>
   <?php endif; ?>
 </div>
</div>

<div class="row" style="margin-top: 1%;">
  <div class="col col-lg-6 col-lg-offset-1">
    <div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Secondario</label>
			<div class="col-lg-8">
          <input class="form-control resize" type="file" name="Stemma_2" value="Carica immagine" style="width: 100%; background-color: rgb(153, 204, 255);">
			</div>
    </div>
  </div>
      <div class="col-lg-2 ">
        <div id=mostra_immagine class="image-magnify2 resize" title="Clicca per ingrandire immagine" onclick="window.open('<?php echo $a_img[2]['webpath']; ?>')">
  				<div class="thumbnail2 text_center ">
  					<img id="thumbnail_image2" src="<?php echo $a_img[2]['webpath']; ?>" border="1">
  					<div class="popup2"></div>
  				</div>
  			</div>
      </div>
      <div class="col-lg-2 resize">
        <?php if($a_img[2]['flag']) : ?>
          <button type="button" class="btn btn-danger px-3" id="DeleteStemma_2" onclick="DeleteGet(2)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>
    		<?php endif; ?>
     </div>
		</div>
<?php if($a_enteAdmin["Gestore_ID"]!=0) :?>
  <div class="row justify-content-md-center " style="margin-top: 2%;">
  	<div class="col col-md-auto text_center">
  			<span class="color_titolo">GESTORE ( Se lo stemma del gestore non viene inserito viene utilizzato lo stemma Sarida di default )</span>
  	</div>
  </div>
  <div class="row" style="margin-top: 3%;">
    <div class="col col-lg-6 col-lg-offset-1">
      <div class="form-group" >
  			<label class="col-lg-4 control-label resize" style="text-align: left;">Principale</label>
  			<div class="col-lg-8">
            <input class="form-control resize" type="file" name="Stemma_Gestore" value="Carica immagine" style="width: 100%; background-color: rgb(153, 204, 255);">
  			</div>
      </div>
    </div>
      <div class="col-lg-2 ">
        <div id=mostra_immagine class="image-magnify3 resize" title="Clicca per ingrandire immagine" onclick="window.open('<?php echo $a_img[3]['webpath']; ?>')">
  				<div class="thumbnail3 text_center">
  					<img id="thumbnail_image3" src="<?php echo $a_img[3]['webpath']; ?>" border="1">
  					<div class="popup3"></div>
  				</div>
  			</div>
      </div>
        <div class="col-lg-2 resize">
          <?php if($a_img[3]['flag']) : ?>
            <button type="button" class="btn btn-danger px-3" id="DeleteStemma_3" onclick="DeleteGet(3)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>
      		<?php endif; ?>
       </div>
  		</div>


<?php else :?>

  <div class="row justify-content-md-center " style="margin-top: 3%;">
  	<div class="col col-md-auto text_center">
  			<span class="color_red">Non e' possibile inserire uno stemma gestore poiche' il gestore e' l'ente stesso</span>
  	</div>
  </div>

<?php endif;?>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>


<?php include(INC."/footer.php"); ?>
