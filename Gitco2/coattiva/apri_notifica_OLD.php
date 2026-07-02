<?php
	
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/classe_anni.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	include CLASSI . "/parametri.php";
	include CLASSI . "/notifiche_importate.php";
	
	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	
	$a = get_var('a');
	$c = get_var('c');
	$p = get_var('p');
	
	$link = get_var('link');
	
	$immagine = new Imagick($link);
	$d = $immagine->getImageGeometry();
	$w_img = $d['width'];
	$h_img = $d['height'];
	
	$dimensioni = limita_dim_immagine(mostra_file_path($link), 800, 500);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Notifica</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>
	
	<link REL=StyleSheet HREF="/gitco2/css/image_magnifier.css" TYPE="text/css" MEDIA=screen>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/image_magnifier.js"></script>
	
	<script>
	$(document).ready(function(){

	dimensiona_img_magnifier("thumbnail_image", "<?php echo $w_img; ?>" , "<?php echo $h_img; ?>" , 800, 500 );

	});

	</script>
</head>

<body class="sfondo_new_gitco">  
<br>
<table width="<?php echo $dimensioni[0]; ?>" height="<?php echo $dimensioni[1]; ?>" class="text_center" border=0>
<tr>
<td valign=top>

<div id=mostra_immagine class="image-magnify" title="Clicca per allargare immagine" onclick="window.open('<?php echo mostra_file_path($link); ?>')">
	<div class="thumbnail text_center">
		<img id="thumbnail_image" src="<?php echo mostra_file_path($link); ?>">
		<div class="popup"></div>
	</div>
</div>
 
</td>
</tr>
</table>

<br>
<center><button  onclick="self.close();">Chiudi</button></center>


</body>
</html>