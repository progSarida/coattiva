<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";

$c = get_var('c');
$a = get_var('a');
$p = get_var('p');

   if (!session_id()) session_start();

   if($_SESSION['username']==NULL)
   {
     header("Location:/gitco2/autenticazione/accesso_negato.php");
     die;
   }
   
/**
 * 		CONTROLLO PARAMETRI
 */
   
	$par_annuali = new parametri_annuali( $c, date('Y-m-d'), "CDS" );
	mysql_query('BEGIN');
	
	$control = $par_annuali->controlloParametri( $c, date('Y-m-d'), "*****" );
	if($control=="NEW")
	{
		mysql_query('COMMIT');
		alert("!!!ATTENZIONE!!! Sono stati creati automaticamente i parametri per l'anno ".date('Y').". Verificare la correttezza dei dati inseriti.");
	}
	else
		mysql_query('ROLLBACK');
	

	   
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- Keep the http-equiv meta tag for IE8 -->
<meta http-equiv="X-UA-Compatible" content="IE=8" />	
<title>GITCO - Menu Principale -</title>
<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<script type="text/javascript" src="/gitco2/librerie/js/JQuery.js"></script>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{     
	return true;
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	return true;
}

//F6
function nuovo_F6()
{
	return true;
}

//F7-F8
function cambia_pag(value)
{
	return true;	
}

//PAG GIU
function pag_prec()
{
	return true;
}

//PAG SU
function pag_suc()
{
	return true;
}

//F9
function ricerca_F9()
{
	return true;
}

//F10
function stampa_F10()
{
	return true;
}

//F11-F12 sono nel menu'

</script>

<script>

function privacy()
{
    window.open('doc_privacy.html','new_win1','width=700, height=500,top=130 left=50, scrollbars=yes,menubar=yes');
}

function chiusura()
{
	location.href="../../index.php";
}

</script>

</head>
<body class="sfondo_new_gitco">
<?php

$where = $_SESSION['aut_progr'];
$autenticazione = $_SESSION['aut_tipo'];

$comune = new ente_gestito($c);
$nome_comune = $comune->Nome;
$comune->dim_stemma_1;

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."] - ".$a);
$nome_user = "Operatore: ".$_SESSION['username'];

// $stemma_1 = $comune->Stemma_1;
// $dim_1 = $comune->dim_stemma_1;
// $stemma_2 = $comune->Stemma_2;
// $dim_2 = $comune->dim_stemma_2;

// if($dim_1[0]>=$dim_1[1])
// {
// 	$rapporto = $dim_1[1]/100;
// 	$largh_1 = 100;
// 	$altez_1 = $dim_1[1]/$rapporto;
// }
// else
// {
// 	$rapporto = $dim_1[1]/100;
// 	$altez_1 = 100;
// 	$largh_1 = $dim_1[0]/$rapporto;
// }

// if($dim_2[0]>=$dim_2[1])
// {
// 	$rapporto = $dim_2[0]/100;
// 	$largh_2 = 100;
// 	$altez_2 = $dim_2[1]/$rapporto;
// }
// else
// {
// 	$rapporto = $dim_2[1]/100;
// 	$altez_2 = 100;
// 	$largh_2 = $dim_2[0]/$rapporto;
// }


?>
<!--   <table class="table_azzurra text_center" style="height:8%;"> -->
<!-- 	<tr> -->
<!-- 		<td width="23%" align="center"> -->
<!-- 		<img src="/gitco2/immagini/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0"> -->
<!-- 		</td> -->
<!-- 		<td align="center"><font class="titolo font24" >Gestione Integrata Tributi Comunali</font></td> -->
<!-- 		<td width=23% align="center"> -->
<!-- 		<img src="/gitco2/immagini/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0"> -->
<!-- 		</td> -->
<!-- 	</tr> -->
<!-- </table> -->

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="titolo font20" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<!-- <table class="table_azzurra text_center" style="height:12%;">   -->
<!--	<tr>   -->
<!--		<td align=center width="23%"> <!-- valign=middle -->
			<!-- <img src="<?php echo $stemma_1 ?>" width="<?php echo $largh_1 ?>" height="<?php echo $altez_1 ?>" border=0> -->
<!--		</td>   -->
<!--		<td width="54%" align="center" ><font class="comune font28" ><?php echo $nome_comune ?></font></td>   -->
<!--		<td align=center width="23%"> <!-- valign=middle -->
			<!-- <img src="<?php echo $stemma_2 ?>" width="<?php echo $largh_2 ?>" height="<?php echo $altez_2 ?>" border=0> --> 
<!--		</td>   -->
<!--	</tr>   -->
<!--</table>   -->

<table class="table_azzurra text_center" style="height:93%;">
<tr style="height:85%;" valign=top>
<td>

<?php include MENU . '/menu_generale.php'; ?> 

<table class="table_interna text_center">
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="" >
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/undogrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick = "" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick = "" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick="">
          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick="">
          	<img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo">
          	</a>
        </td>
         <td width=11%></td>
        <td width=7% align="center">
          	<a href="#" onMouseover="title='Stampa'" onclick="">
          	<img src="/gitco2/immagini/printF10grey.png" width=50 height=50 border="0" ></a>
    	</td>
        <td width=3%></td>
		<td align=center width=7% >
			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td width=2%></td>
		<td width=7%>
			<a onMouseover="title='Home'" href="#" onClick="link('menu')" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

</td>
</tr>


<?php if ($_SESSION['username'] == "mirkop") { ?>

<tr>
	<td>
		<a type="button" class="button_azzurro" href="/gitco2/leggi_mail.php?c=<?php echo $c; ?>">Leggi mail</a>
	</td>
</tr>

<?php } ?>

	
<tr>
<td style="height:15%;">

<table class="width100 text_center">
	<tr>
		<td colspan=3><hr></td>
	</tr>
	<tr>
        <td class="width33">
        	<a href="mailto:webmaster@gitco.it" title="Scrivi a webmaster@sarida.it">
        		<img src="/gitco2/immagini/email.gif" width="65" height="65" border=0>
        	</a>
        </td>
        <td class="width34">
        	<a href="javascript:privacy();" title="Informativa sulla Privacy">
        		<img src="/gitco2/immagini/privacy.jpg" width="60" height="60" border=0>
        	</a>
        </td>
        <td class="width33">
        	<a href="#" onClick="chiusura();" title="Esci dal programma">
            	<img src="/gitco2/immagini/exit.jpg" width="60" height="60" border=0 alt="Clicca qui per disconnetterti">
           	</a>
        </td>
	</tr>
</table>

</td>
</tr>
</table>

</body>
</html>