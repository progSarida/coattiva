<?php 

	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";*/

	require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


    include(CLS."/cls_help.php");
    //include_once(INC."/menu.php");

    $cls_help = new cls_help();
	
	$layout = "<script>$('[name=user]').focus();</script>";
	
	$servizio = $cls_help->getVar('servizio');

    //$cls_help->alert($servizio);
	if($servizio==null)
		$link = SUPER_WEB_ROOT."/index.php";
	else if($servizio=="targheestere")
		$link = SUPER_WEB_ROOT."/index_estere.php";
	else if($servizio=="pubblicita")
		$link = SUPER_WEB_ROOT."/index_pubblicita.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

    <title>GITCO - Password</title>

    <link rel=StyleSheet href="<?= CSS; ?>/classi_semplici.css" type="text/css" media=screen>

    <script type="text/javascript" language="javascript" src="<?= JS; ?>/JQuery.js" ></script>
    <script type="text/javascript" language="javascript" src="<?= JS; ?>/form_jquery.js" ></script>
    <script type="text/javascript" language="javascript" src="<?= JS; ?>/funzioni.js" ></script>

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
  	
 <script>
 
function controllaCampi()
{
	var pattern_pass = /[^A-Za-z0-9]/;
	
	var pass = $('#new_pass').val();
	var pass2 = $('#new_pass_2').val();
	
	control_pass = pass.match(pattern_pass);
	
	if(control_pass)
	{
  		alert("La Password pu� contenere solo caratteri numerici e alfabetici.");
   		return false;
	}
	if(pass2 != pass)
	{
		alert("La Password di conferma non coincide con la Nuova Password.");
   		return false;
	}

	return true;
}


$(document).ready(function(){

	$("#submit_click").click(function salva_form() {            
		campi = controllaCampi();
		if(campi)
		{
        	$("#pass_autenticazione").submit();
        }
	});

	$('#pass_autenticazione').ajaxForm(
            function(value) {
                array_ritorno = value.split(' ');
                switch(array_ritorno[0])
                {
                	case "no":

                    	alert("Username e/o password errate!");
						
                	break;

                	case "ok":
                    	
                		alert("Nuova Password inserita con successo!");
                		top.location.href = "<?php echo $link; ?>";
                	break;

                	case "fail":
                    	
						alert("Contattare l'amministratore di sistema!");
                		
                	break;

                }
    });
});

function lastTab()
{
	$('[name=user]').focus();
}


 </script>
  	
  
</head>

<body class="sfondo_new_gitco"> 

<table class="table_azzurra text_center" style="height:8%;">
	<tr>
		<td width="23%" align="center">
		<img src="<?= IMMAGINIWEB; ?>/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0">
		</td>
		<td align="center"><font class="titolo font24" >Gestione Integrata Tributi Comunali</font></td>
		<td width=23% align="center">
		<img src="<?= IMMAGINIWEB; ?>/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0">
		</td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:22%;">
	<tr>
		<td>
		
		<img class="text_center" src="<?= IMMAGINIWEB; ?>/Gitco_titolo.png" alt="GITCO" border=0>
		
		
	</td>
	</tr>
</table>
<table class="table_azzurra text_center" style="height:50%;">
	<tr>
		<td valign=top>
		<form id="pass_autenticazione" name="pass_autenticazione" action="pass_utente.php" method="post">
		
		<table align=center class=table_interna border=0 cellspacing=4>
			<tr>
				<td class="width25"><br></td>
				<td colspan=2><br></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25">
					<img src="<?= IMMAGINIWEB; ?>/php_icon.png" alt="Php Mysql" width="100" height="45" border="0">
				</td>
				<td colspan=2 class="text_center"><font class="titolo font18" >PASSWORD</font></td>
				<td class="width25">
					<img src="<?= IMMAGINIWEB; ?>/Easyphp.png" alt="EasyPhp" width="55" height="55" border="0">
				</td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td colspan=2><hr></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width25"><font>Nome utente</font></td>
				<td class="width25"><input type="text" 	tabindex=1	name="user" size="22"></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width25"><font>Password</font></td>
				<td class="width25"><input type="password" tabindex=2	name="pass" size="22"></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td colspan=2><hr></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width25"><font>Nuova Password</font></td>
				<td class="width25"><input type="password" id="new_pass" tabindex=3 name="new_pass" size="22"></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width25"><font>Conferma Password</font></td>
				<td class="width25"><input type="password" id="new_pass_2" tabindex=4 name="new_pass_2" size="22"></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width50" colspan=2><br></td>
				<td class="width25"><br></td>
			</tr>
            <tr>
				<td class="width25"><br></td>
				<td class="width25">
					<a href="#" tabindex=5>
						<img id="submit_click" title="Inserisci Password" src="<?= IMMAGINIWEB; ?>/enter.png"
						style="width:50px; height:50px; border:0;">
					</a>
				</td>
				<td class="width25">
					<a href="<?php echo $link; ?>" tabindex=6 onblur="lastTab();">
						<img src="<?= IMMAGINIWEB; ?>/indietro.gif" width="70" height="30"
						title="Torna indietro" border="0">
					</a>					
				</td>
				<td class="width25"><br><label tabindex="7"></label></td>
			</tr>
			<tr>
				<td class="width25"><br></td>
				<td class="width50" colspan=2><br></td>
				<td class="width25"><br></td>
			</tr>
		</table>
</form>
		</td>
	</tr>
</table>

<?php echo $layout; ?>

</body>
</html>