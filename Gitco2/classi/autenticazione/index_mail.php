<?php 

	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	$layout = "<script>$('[name=user]').focus();</script>";
	
	$servizio = get_var('servizio');
	if($servizio==null)
		$link = "../../index.php";
	else if($servizio=="targheestere")
		$link = "../../index_estere.php";
	else if($servizio=="pubblicita")
		$link = "../../index_pubblicita.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>GITCO - E-mail</title>

	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>

  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

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
	var pattern_mail = /^[^\x40]{1,40}[\x40]{1}[^\x40]{1,20}[.]{1}[a-zA-Z]{1,40}$/;
	
	var mail = $('#mail').val();
	
	control_mail = mail.search(pattern_mail);
	if(control_mail)
	{
  		alert("Inserire un indirizzo email valido");
   		return false;
	}

	return true;
}


$(document).ready(function(){

	$("#submit_click").click(function salva_form() {            
		campi = controllaCampi();
		if(campi)
		{
        	$("#mail_autenticazione").submit();
        }
	});

	$('#mail_autenticazione').ajaxForm(
            function(value) {
                array_ritorno = value.split(' ');
                switch(array_ritorno[0])
                {
                	case "no":

                    	alert("Username e/o password errate!");
						
                	break;

                	case "ok":
                    	
                		alert("Email inserita con successo!");
                		
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
		<img src="/gitco2/immagini/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0">
		</td>
		<td align="center"><font class="titolo font24" >Gestione Integrata Tributi Comunali</font></td>
		<td width=23% align="center">
		<img src="/gitco2/immagini/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0">
		</td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:22%;">
	<tr>
		<td>
		
		<img class="text_center" src="/gitco2/immagini/Gitco_titolo.png" alt="GITCO" border=0>
		
		
	</td>
	</tr>
</table>
<table class="table_azzurra text_center" style="height:50%;">
	<tr>
		<td valign=top>
		<form id="mail_autenticazione" name="mail_autenticazione" action="email_utente.php" method="post">
		
		<table align=center class=table_interna border=0 cellspacing=4>
			<tr>
				<td class="width25"><br></td>
				<td colspan=2><br></td>
				<td class="width25"><br></td>
			</tr>
			<tr>
				<td class="width25">
					<img src="/gitco2/immagini/php_icon.png" alt="Php Mysql" width="100" height="45" border="0">
				</td>
				<td colspan=2 class="text_center"><font class="titolo font18" >E-MAIL</font></td>
				<td class="width25">
					<img src="/gitco2/immagini/Easyphp.png" alt="EasyPhp" width="55" height="55" border="0">
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
				<td class="width25"><font>E-mail</font></td>
				<td class="width25"><input type="text" id="mail" name="mail" size="22" tabindex=3></td>
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
					<a href="#" tabindex=4>
						<img id="submit_click" title="Inserisci Mail" src="/gitco2/immagini/enter.png" 
						style="width:50px; height:50px; border:0;">
					</a>
				</td>
				<td class="width25">
				<a href="<?php echo $link; ?>" tabindex=5 onblur="lastTab();">
					<img src="/gitco2/immagini/indietro.gif" width="70" height="30" 
					title="Torna indietro" border="0">
				</a>					
				</td>
				<td class="width25"><br><label tabindex="6"></label></td>
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
