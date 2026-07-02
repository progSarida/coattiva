<?php 

include_once "_path.php";
$layout = "<script>$('[name=user]').focus();</script>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" id="viewport">

    <title>GITCO COATTIVA</title>
    <link rel=StyleSheet href="<?= CSS ?>/classi_semplici.css" type="text/css" media=screen>
    <link rel=StyleSheet href="<?= CSS ?>/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
    <style> .ui-datepicker { font-size:11px; } </style>

    <script src="<?= JS ?>/jquery.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/form_jquery.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/jquery.bpopup.min.js" type="text/javascript" ></script>
    <script src="<?= JS ?>/funzioni.js" type="text/javascript" ></script>

    <script src="<?= JS ?>/jquery-ui.js" type="text/javascript"></script>
    <script src="<?= JS ?>/datepicker.js" type="text/javascript"></script>

  	<script>
  	$("*").on( "change" , "input, textarea, select" , function( event ) {

  		var elem = $( this );
  		elem.addClass( "sfondo_giallo", ":change" );

  		});

  		$("*").on( "focus blur","input, textarea, select", function( event ) {
  		var elem = $( this );

  		elem.toggleClass( "focused", elem.is( ":focus" ) );

  		});

  		$("*").on( "focus","input, textarea", function( event ) {
  			var elem = $( this );

  				elem.select();

  			});
  	</script>

  	<script language="Javascript">
var control_press = false;

function capLock(e){

	control_press = true;

	if (!e)
	{
    	e = window.event;
	}

    var keycode = e.keyCode;
    if (e.which)
        keycode = e.which;

	 var sk = e.shiftKey?e.shiftKey:((keycode == 16)?true:false);


	 if(((keycode >= 65 && keycode <= 90) && !sk)||((keycode >= 97 && keycode <= 122) && sk))
	 {
	  	$('#lock_image').show();
	  	$(document).data("capsOn", true);
	 }
  	 else if(keycode != 8)
  	 {
  		 $('#lock_image').hide();
  		 $(document).data("capsOn", false);
  	 }

}
</script>

<script>

//F11
var fn = function (e)
{
	if (!e)
	{
    	e = window.event;
	}

    var keycode = e.keyCode;
    if (e.which)
        keycode = e.which;
    
if (122 == keycode)
{
    controlScreen();
}
else if(20 == keycode && control_press == true)
{
	if (!$(document).data("capsOn")) {
            $(document).data("capsOn", true);
        } else {
            $(document).data("capsOn", false);
        }

        if ($(document).data("capsOn")) {
        	$('#lock_image').show();
        }
        else
        {
        	$('#lock_image').hide();
        }

}


}
document.onkeyup = fn;

$(document).ready(function(){

	$('#lock_image').hide();
	controlScreen();
	
});

function controlScreen()
{
	if (!window.screenTop && !window.screenY) {
	    //full web browser
	    $('#verifica_full').text('');
	}
	else
	{
		$('#verifica_full').text("Se si desidera la visualizzazione a schermo intero e' necessario selezionarla prima di accedere alle pagine del programma cliccando F11. Successivamente questa funzione sara' disabilitata a favore dello strumento di aiuto per l'utente (HELP)");
	}
}

function lastTab()
{
	$('[name=user]').focus();
}

$(document).ready(function(){

    	
$("#pass_click").click(function salva_form() {            

	location.href = "<?=WEB_ROOT?>/autenticazione/index_pass.php";
	return false;
	
});
    
$("#mail_click").click(function cancella_form() {

	location.href = "<?=WEB_ROOT?>/autenticazione/index_mail.php";
	return false;
    
});

});

</script>
</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:8%;">
	<tr>
		<td width="23%" align="center">
		<img src="<?=IMG?>/sarida_logo_medium.png" title="Logo dell'Azienda" border="0">
		</td>
		<td align="center"><font class="titolo font24" >Gestione Integrata Tributi Comunali</font></td>
		<td width=23% align="center">
		<img src="<?=IMG?>/sarida_logo_medium.png" title="Logo dell'Azienda" border="0">
		</td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:22%;">
	<tr>
		<td>
		
		<img class="text_center" src="<?=IMG?>/Gitco_titolo.png" alt="GITCO" border=0>
		
		
	</td>
	</tr>
</table>
<table class="table_azzurra text_center" style="height:50%;">
	<tr>
		<td valign=top>
		
		<form name="ingresso" action="<?=WEB_ROOT?>/authentication.php" method="post">
		
		<table align=center class=table_interna border=0 cellspacing=4>
			<tr>
				<td class="width25"><br></td>
				<td class="width50" colspan=3><br></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25">
					<img src="<?=IMG?>/php_icon.png" alt="Php Mysql" width="100" height="45" border="0">
				</td>
				<td colspan=3 class="text_center"><font class="titolo font18" >AUTENTICAZIONE</font></td>
				<td class="width25">
					<img src="<?=IMG?>/Easyphp.png" alt="EasyPhp" width="55" height="55" border="0">
				</td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td colspan=3><hr></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				
				<td class="width5"></td>
				<td class="width20 text_left"><font>Nome utente</font></td>
				<td class="width25"><input class="pwidth150" type="text" tabindex=1 name="user" ></td>
				<td class="width25"></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				
				<td class="width5"><img id="lock_image" src="<?=IMG?>/lockRED.png" width="22" height="22" title="BlocMaiusc attivo"/></td>
				<td class="width20 text_left"><font>Password</font></td>
				<td class="width25"><input class="pwidth150" type="password" name="pass" tabindex=2 onkeypress="capLock(event)"></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width50" colspan=3><hr></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width50" colspan=3><br></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width50" colspan=3>
				
					<table class="width100" border="0">
					<tr>
						<td class="width25 text_center">
							<input type="image" class="pwidth50 pheight50" src="<?=IMG?>/enter.png" title="Accedi a Gitco" border="0" onClick="submit(this.form)">
						</td>
						<td class="width25 text_center">
							<input type="image" class="pwidth50 pheight50" title="Cambia Password" src="<?=IMG?>/change-password.png" onclick="location.href='<?=WEB_ROOT?>/autenticazione/index_pass.php'; return false;" />
						</td>
						<td class="width25 text_center">
		          			<input type="image" class="pwidth50 pheight50" title="Inserisci o modifica E-mail" src="<?=IMG?>/change-mail.gif" onclick="location.href='<?=WEB_ROOT?>/autenticazione/index_mail.php'; return false;" />
						</td>
						<td class="width25 text_center">
		          			<input type="image" class="pwidth30 pheight30" title="Esci dal programma" src="<?=IMG?>/exit.jpg" onclick="location.href='http://www.gitco.it'; return false;" />
						</td>
						<!-- <td>
							<label tabindex="7"></label>
						</td> -->
					</tr>
					</table>
					
				</td>
				<td class="width25"><br></td>
				
				
			</tr>
			<tr>
				<td class="text_center" colspan=5><p id=verifica_full></p></td>
			</tr>
		</table>
		
		</form>

		</td>
	</tr>
</table>

<?php echo $layout; ?>

</body>
</html>