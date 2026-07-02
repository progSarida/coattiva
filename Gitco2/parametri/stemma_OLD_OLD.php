<?php
include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include(CLS."/cls_ente.php");

$tipo_riscossione = $cls_help->getVar('tipo_riscossione');

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
        $("#form_stemma").submit();
    }

    switchMenuImg("F5");
    F5_button = function()
    {
        location.href="stemma.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><span class="titolo font16 under_decor">Stemma</span></td>
	</tr>
</table>

<form name=form_stemma id=form_stemma method=post action="stemma_salva.php" enctype="multipart/form-data">

<input type=hidden name=invia_submit id=invia_submit value="" >
<input type=hidden name=flagDel id=flagDel value="" >
<input type=hidden name=imgName id=imgName value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC value=<?php echo $a_enteAdmin['CC']; ?> >
<input type=hidden name=Gestore_ID value=<?php echo $a_enteAdmin['Gestore_ID']; ?> >

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<colgroup>
		<col class="width15">
		<col class="width50">
		<col class="width14">
        <col class="width13">
        <col class="width8">
    </colgroup>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left "><b>Principale</b></td>
		<td class="text_left "><input class="button_azzurro width100" type="file" name="Stemma_1" value="Carica immagine"></td>
		<td class="text_left" colspan=2 >
			<div id=mostra_immagine class="image-magnify4" title="Clicca per allargare immagine" onclick="window.open('<?php echo $a_img[1]['webpath']; ?>')">
				<div class="thumbnail4 text_center">
					<img id="thumbnail_image4" src="<?php echo $a_img[1]['webpath']; ?>" border="1>">
					<div class="popup4"></div>
				</div>
			</div>
		</td>
        <td>
		<?php if($a_img[1]['flag']) : ?>

      <button type="button" class="btn btn-danger px-3" id="DeleteStemma_1" onclick="DeleteGet(1)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>

	<?php endif; ?>
        </td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><br></td>
	</tr>
	<tr>
		<td class="text_left"><b>Secondario</b></td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="Stemma_2" value="Carica immagine"></td>
		<td class="text_left" colspan=2>
			<div id=mostra_immagine class="image-magnify2" title="Clicca per ingrandire immagine" onclick="window.open('<?php echo $a_img[2]['webpath']; ?>')">
				<div class="thumbnail2 text_center">
					<img id="thumbnail_image2" src="<?php echo $a_img[2]['webpath']; ?>" border="1">
					<div class="popup2"></div>
				</div>
			</div>
		</td>
        <td>
		<?php if($a_img[2]['flag']) : ?>

      <button type="button" class="btn btn-danger px-3" id="DeleteStemma_2" onclick="DeleteGet(2)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>

		<?php endif; ?>
        </td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<?php
	if($a_enteAdmin["Gestore_ID"]!=0){?>
	<tr>
		<td class="text_center" colspan=5><span class="color_titolo">GESTORE ( Se lo stemma del gestore non viene inserito viene utilizzato lo stemma Sarida di default )</span></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>

	<tr>
		<td class="text_left"><b>Principale</b></td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="Stemma_Gestore" value="Carica immagine"></td>
		<td class="text_left" colspan=2>
			<div id=mostra_immagine class="image-magnify3" title="Clicca per ingrandire immagine" onclick="window.open('<?php echo $a_img[3]['webpath']; ?>')">
				<div class="thumbnail3 text_center">
					<img id="thumbnail_image3" src="<?php echo $a_img[3]['webpath']; ?>" border="1">
					<div class="popup3"></div>
				</div>
			</div>
		</td>
        <td>
		<?php if($a_img[3]['flag']) : ?>

      <button type="button" class="btn btn-danger px-3" id="DeleteStemma_3" onclick="DeleteGet(3)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>
		<?php endif; ?>
        <td>
	</tr>
	<tr>
	</tr>
	<?php }
	else
	{?>
		<tr>
		<td class="text_center" colspan=5><span class="color_red">Non e' possibile inserire uno stemma gestore poiche' il gestore e' l'ente stesso</span></td>
		</tr>
		<tr>
			<td class="text_center" colspan=5><hr></td>
		</tr>
	<?php }?>
</table>


</form>

<?php include(INC."/footer.php"); ?>
