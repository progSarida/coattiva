<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

//include_once INC . "/headerAjax.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_help.php";

$cls_help = new cls_help();
$cls_utils = new cls_Utils();

	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/classe_anni.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	include CLASSI . "/parametri.php";
	include CLASSI . "/notifiche_importate.php";
	
	if (!session_id()) session_start();*/
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

function limita_dim_immagine ($img, $maxLarghezza, $maxAltezza)  //  img deve arrivare in formato "/gitco2/stemmi/cogorno.png"
{
    $arrayDimensioni = array();
    //$imgCompleto = $_SERVER['DOCUMENT_ROOT'] . $img;
    if (file_exists($img))
    {
        $dimensioni = getimagesize($img);
        $larghezza = $dimensioni[0];
        $altezza = $dimensioni[1];

        $rapporto = $larghezza / $altezza;

        if($larghezza > $altezza)
        {
            $new_larghezza  =   $maxLarghezza;
            $new_altezza    =   $altezza*($new_larghezza/$larghezza);

            if($new_altezza>$maxAltezza)
            {
                $new_altezza    =   $maxAltezza;
                $new_larghezza  =   $larghezza*($new_altezza/$altezza);
            }
        }

        if($larghezza < $altezza)
        {
            $new_altezza    =   $maxAltezza;
            $new_larghezza  =   $larghezza*($new_altezza/$altezza);

            if($new_larghezza>$maxLarghezza)
            {
                $new_larghezza  =   $maxLarghezza;
                $new_altezza    =   $altezza*($new_larghezza/$larghezza);
            }
        }

        if($larghezza == $altezza)
        {
            if($maxLarghezza<$maxAltezza)
            {
                $new_larghezza  =   $maxLarghezza;
                $new_altezza    =   $maxLarghezza;
            }
            else
            {
                $new_larghezza  =   $maxAltezza;
                $new_altezza    =   $maxAltezza;
            }
        }

        $arrayDimensioni[0] = $new_larghezza;
        $arrayDimensioni[1] = $new_altezza;

    }
    else
    {
        echo '<script>alert ("Il file $imgCompleto non esiste (/librerie/php/file_function.php)")</script>';
        $arrayDimensioni[0] = 0;
        $arrayDimensioni[1] = 0;
    }
    return $arrayDimensioni;
}
	
	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	
	$link = $cls_help->getVar('link');
	
	$immagine = new Imagick($link);
	$d = $immagine->getImageGeometry();
	$w_img = $d['width'];
	$h_img = $d['height'];
	
	$dimensioni = limita_dim_immagine($link, 800, 500);
?>
    <script src="<?= JS ?>/bootstrapvalidator-0.5.2/vendor/jquery/jquery-1.10.2.min.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/image_magnifier.js" type="text/javascript"></script>

	<script>
	$(document).ready(function(){

	dimensiona_img_magnifier("thumbnail_image", "<?php echo $w_img; ?>" , "<?php echo $h_img; ?>" , 800, 500 );

	});

	</script>

<br>
<table width="<?php echo $dimensioni[0]; ?>" height="<?php echo $dimensioni[1]; ?>" class="text_center" border=0>
<tr>
<td valign=top>

<div id=mostra_immagine class="image-magnify" title="Clicca per allargare immagine" onclick="window.open('<?php echo SUPER_WEB_ROOT.$cls_utils->mostra_file_path($link); ?>')">
	<div class="thumbnail text_center">
		<img id="thumbnail_image" src="<?php echo "../../".$cls_utils->mostra_file_path($link); ?>">
		<div class="popup"></div>
	</div>
</div>
 
</td>
</tr>
</table>

<br>
<center><button  onclick="self.close();">Chiudi</button></center>
