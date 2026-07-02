<?php
/*if (!extension_loaded('imagick')){
    echo 'imagick not installed';
}

phpinfo();*/

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";*/

//if (!session_id()) session_start();

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

//print_r($a_enteAdmin);

/*$a = get_var('a');
$c = get_var('c');
$p = get_var('p');*///GIà NELL'HEADER
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');
//$servizio = $cls_help->getVar('servizio');//PENSO NON SI USI PIù

//$comune = new ente_gestito($c);
$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

//$ente = new ente_gestito($c);

$stemma_1 = $a_enteAdmin["Stemma_1"];//$ente->Stemma_1;
$stemma_2 = $a_enteAdmin["Stemma_2"];//$ente->Stemma_2;
$path_1 = STEMMI."/".$a_enteAdmin["CC"]."/".$stemma_1;//$ente->Path_Stemma_1;//
$path_2 = STEMMI."/".$a_enteAdmin["CC"]."/".$stemma_2;//$ente->Path_Stemma_2;
$stemma_principale = ($stemma_1 != "")? "si" : "no";//$ente->Stemma_Principale;
$stemma_secondario = ($stemma_2 != "")? "si" : "no";//$ente->Stemma_Secondario;
//echo "<h1>".$path_1."</h1>";


//$gestore = $ente->Gestore;
$stemma_3 = $a_enteAdmin["Gestore_Stemma"];//$gestore->Stemma;  --- AVEVO USATO STEMMA_3 MA PENSO SIA GIUSTO COSì
$path_3 = STEMMI."/".$a_enteAdmin["CC"]."/".$stemma_3;//$gestore->Path_Stemma;

$border;
$w_img4 = 0;
$h_img4 = 0;
$w_img2 = 0;
$h_img2 = 0;
$w_img3 = 0;
$h_img3 = 0;
$flag1 = true;
$flag2 = true;
$flag3 = true;
// $ufficio = $ente->Ufficio;
// $stemma_4 = $ufficio->Stemma;
// $path_4 = $ufficio->Path_Stemma;
//echo "<h1> </br>STEMMA ---- ".$stemma_1."</h1></br>";
if($stemma_1!="" && $stemma_principale == "si")
{
	$stemma_1 = "/gitco2/stemmi/".$a_enteAdmin["CC"]."/".$stemma_1;
	$img_stemma_1 = new Imagick($path_1);
	$d = $img_stemma_1->getImageGeometry();
	$w_img4 = $d['width'];
	$h_img4 = $d['height'];
	$border[1] = 1;
}
else
{
	$stemma_1 = "/gitco2/stemmi/default/Not_Found.png";
	$img_stemma_1 = new Imagick(STEMMI."/default/Not_Found.png");
	$d = $img_stemma_1->getImageGeometry();
	$w_img4 = $d['width'];
	$h_img4 = $d['height'];
	$border[1] = 1;
	$flag1 = false;
}

if($stemma_2!=""  && $stemma_secondario == "si")
{
  //echo "<h1>".$path_2."</h1>";
	$stemma_2 = "/gitco2/stemmi/".$a_enteAdmin["CC"]."/".$stemma_2;
	$img_stemma_2 = new Imagick($path_2);
	$d = $img_stemma_2->getImageGeometry();
	$w_img2 = $d['width'];
	$h_img2 = $d['height'];
	$border[2] = 1;
}
else
{
	$stemma_2 = "/gitco2/stemmi/default/Not_Found.png";
	$img_stemma_2 = new Imagick(STEMMI."/default/Not_Found.png");
	$d = $img_stemma_2->getImageGeometry();
	$w_img2 = $d['width'];
	$h_img2 = $d['height'];
	$border[2] = 1;
	$flag2 = false;
}

//echo "<h1>".$path_3." --- ".$a_enteAdmin["Gestore_ID"]."</h1>";
if($stemma_3!="" && $a_enteAdmin["Gestore_ID"]!=0)
{
	//echo "<h1>".$path_3." ---- ".$stemma_3."</h1>";
	$stemma_3 = "/gitco2/stemmi/".$a_enteAdmin["CC"]."/".$stemma_3;
	$img_stemma_3 = new Imagick($path_3);
	$d = $img_stemma_3->getImageGeometry();
	$w_img3 = $d['width'];
	$h_img3 = $d['height'];
	$border[3] = 1;
}
else
{
	$stemma_3 = "/gitco2/stemmi/default/Not_Found.png";
	$img_stemma_3 = new Imagick(STEMMI."/default/Not_Found.png");
	$d = $img_stemma_3->getImageGeometry();
	$w_img3 = $d['width'];
	$h_img3 = $d['height'];
	$border[3] = 1;
	$flag3 = false;
}


?>


<!-- ********** GESTIONE LINK MENU ********** -->


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/image_magnifier.js"></script>

<script>

$(document).ready(function(){

    //alert("<?php //echo $w_img; ?>");
    if("<?php echo $w_img4; ?>">0)
	    dimensiona_magnify("4", "<?php echo $w_img4; ?>" , "<?php echo $h_img4; ?>" , 150, 150 );
    if("<?php echo $w_img2; ?>">0)
        dimensiona_magnify("2", "<?php echo $w_img2; ?>" , "<?php echo $h_img2; ?>" , 150, 150 );
    if("<?php echo $w_img3; ?>">0)
        dimensiona_magnify("3", "<?php echo $w_img3; ?>" , "<?php echo $h_img3; ?>" , 150, 150 );

});


</script>

<!--<body class="sfondo_new_gitco" >-->

  <?php

  include(INC."/menu.php");

  ?>

  <script type="text/javascript">
    function DeleteGet(flagDel)
    {
      //alert('delete'+flagDel);
      ritorno = confirm("Si stanno eliminando i dati dal database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");

      if(ritorno)
      {
          ritorno2 = confirm("Sei sicuro di voler eliminare i dati?");
          if(ritorno2)
          {
            switch(flagDel)
            {
              case 1: location.href = "stemma_salva.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&invia_submit=Delete&flagDel="+flagDel+"&pathImg=<?php echo str_replace('\\',"\\\\",$path_1); ?>";
                break;
              case 2: location.href = "stemma_salva.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&invia_submit=Delete&flagDel="+flagDel+"&pathImg=<?php echo str_replace('\\',"\\\\",$path_2); ?>";
                break;
              case 3: location.href = "stemma_salva.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&invia_submit=Delete&flagDel="+flagDel+"&pathImg=<?php echo str_replace('\\',"\\\\",$path_3); ?>&Gestore_ID=<?php echo $a_enteAdmin['Gestore_ID']; ?>";
                break;
            }

          }
      }
    }
  </script>

  <script>

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

  //F11-F12 sono nel menu'
  </script>
<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Stemma</font></td>
	</tr>
</table>

<form name=form_stemma id=form_stemma method=post action="stemma_salva.php" enctype="multipart/form-data">

<input type=hidden name=invia_submit id=invia_submit value="" >

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
		<td class="text_center" colspan=5><span class="color_titolo">ENTE</span></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left "><b>Principale</b></td>
		<td class="text_left "><input class="button_azzurro width100" type="file" name="stemma_1" value="Carica immagine"></td>
		<td class="text_left" colspan=2 >
			<div id=mostra_immagine class="image-magnify4" title="Clicca per allargare immagine" onclick="window.open('<?php echo $stemma_1; ?>')">
				<div class="thumbnail4 text_center">
					<img id="thumbnail_image4" src="<?php echo $stemma_1; ?>" border="<?php echo $border[1]; ?>">
					<div class="popup4"></div>
				</div>
			</div>
		</td>
		<?php if($flag1) : ?>
    <td>
      <button type="button" class="btn btn-danger px-3" id="DeliteStemma_1" onclick="DeleteGet(1)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>
    </td>
	<?php endif; ?>
	</tr>
	<tr>
		<td class="text_center" colspan=4><br></td>
	</tr>
	<tr>
		<td class="text_left"><b>Secondario</b></td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="stemma_2" value="Carica immagine"></td>
		<td class="text_left" colspan=2>
			<div id=mostra_immagine class="image-magnify2" title="Clicca per ingrandire immagine" onclick="window.open('<?php echo $stemma_2; ?>')">
				<div class="thumbnail2 text_center">
					<img id="thumbnail_image2" src="<?php echo $stemma_2; ?>" border="<?php echo $border[2]; ?>">
					<div class="popup2"></div>
				</div>
			</div>
		</td>
		<?php if($flag2) : ?>
    <td>
      <button type="button" class="btn btn-danger px-3" id="DeliteStemma_2" onclick="DeleteGet(2)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>
    </td>
		<?php endif; ?>
	</tr>
	<tr>
		<td class="text_center" colspan=4><hr></td>
	</tr>
	<?php
	if($a_enteAdmin["Gestore_ID"]!=0){?>
	<tr>
		<td class="text_center" colspan=4><span class="color_titolo">GESTORE ( Se lo stemma del gestore non viene inserito viene utilizzato lo stemma Sarida di default )</span></td>
	</tr>
	<tr>
		<td class="text_center" colspan=4><hr></td>
	</tr>

	<tr>
		<td class="text_left"><b>Principale</b></td>
		<td class="text_left"><input class="button_azzurro width100" type="file" name="stemma_3" value="Carica immagine"></td>
		<td class="text_left" colspan=2>
			<div id=mostra_immagine class="image-magnify3" title="Clicca per ingrandire immagine" onclick="window.open('<?php echo $stemma_3; ?>')">
				<div class="thumbnail3 text_center">
					<img id="thumbnail_image3" src="<?php echo $stemma_3; ?>" border="<?php echo $border[3]; ?>">
					<div class="popup3"></div>
				</div>
			</div>
		</td>
		<?php if($flag3) : ?>
    <td>
      <button type="button" class="btn btn-danger px-3" id="DeliteStemma_3" onclick="DeleteGet(3)"><i class="fa fa-times-circle" aria-hidden="true"></i> Elimina</button>
    </td>
		<?php endif; ?>
	</tr>
	<tr>
	</tr>
	<?php }
	else
	{?>
		<tr>
		<td class="text_center" colspan=4><span class="color_red">Non e' possibile inserire uno stemma gestore poiche' il gestore e' l'ente stesso</span></td>
		</tr>
		<tr>
			<td class="text_center" colspan=4><hr></td>
		</tr>
	<?php }?>
</table>


</form>

<?php include(INC."/footer.php"); ?>
