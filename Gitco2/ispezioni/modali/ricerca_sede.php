<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
  header("Location:accesso_negato.php");
  die;
}

$richiesta = get_var('richiesta');
$posted = get_var('posted');
$c = get_var('c');
$a = get_var('a');

$tipo_sede = get_var('tipo_sede');
$tipo_sede_completo = get_var('tipo_sede_completo');

switch ($richiesta)
{
		
	case ("sede"):
		
		 $titolopagina = "Ricerca ".$tipo_sede_completo;
		  $linkricerca = "ricerca_sede.php?richiesta=sede&posted=true&c=".$c."&tipo_sede=".$tipo_sede."&tipo_sede_completo=".$tipo_sede_completo;
		       
		    $nomecella = array();
	     $nomecella[0] = "<b>".$tipo_sede_completo."</b>";
	     
	            $cella = array();
		     $cella[0] = "<input id=sede type=text name=sede size=40>";
		     
		     $campo = "";
		     $nomecampo = "";
		     $riga = "";
		
		if( $posted == true )
		{
			
			$sede = get_var('sede');
			if($sede == null)$sede="";
			
			$query = "SELECT * FROM banca WHERE Tipo = '".$tipo_sede."' AND Denominazione LIKE '%".$sede."%' AND CC = '".$c."'";
			
			$resultSede = safe_query($query);
			$numero_sedi = mysql_num_rows($resultSede);
			
		}

		break;
	
	default:
		
		$titolopagina = "Nessun titolo";
		$linkricerca = "Nessun link";
		$linknuovo = "Nessun nuovo link";
		$query = "";
		
		break;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- Keep the http-equiv meta tag for IE8 -->
<meta http-equiv="X-UA-Compatible" content="IE=8" />

<title>Anagrafe</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js"></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js"></script>	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js"></script>
  	

  	<script>
  	
       var richiesta = "<?php echo $richiesta; ?>";
         

			function GeneraLinkPagina(richiesta)
			{
				var link = "<?php if(isset($linkricerca)){echo $linkricerca;}else{echo "";} ?>";

				switch (richiesta)
				{

				
					
				case ("sede"):

					var sedeRic = $("#sede").val();

					link +="&sede="+sedeRic;
			
				break;
			
				}
				
				window.name = "ricerca";
				window.open(link, "ricerca");
				
			}

function torna_valore(value)
{
	window.returnValue = value;
	self.close();
}
		</script>
</head>

<body class="sfondo_new_gitco">
	<center>
	
	<h3><b><?php echo $titolopagina; ?></b></h3>

	</center>
	
<?php if ($posted == NULL) { ?>
			
			<center>
<table class=table_modale cellspacing="5" cellpadding="0" border="0">	

	<tr>
		<td colspan=4><br></td>
	</tr>
<?php for($k=0;$k<count($cella);$k++){?>


	<tr>
		<td></td>
		<td><?php echo $nomecella[$k]; ?></td>							
		<td width=49% align=center><?php echo $cella[$k]; ?></td>
		<td></td>
	</tr>

<?php }?>
	
	<tr>
		<td colspan=4><?php echo $riga; ?></td>
	</tr>
	<tr>
		<td></td>
		<td><?php echo $nomecampo; ?></td>							
		<td width=49% align=center><?php echo $campo; ?></td>
		<td></td>
	</tr>

	<tr><td colspan=4><hr></td></tr>
	<tr>
		<td></td>	
		<td colspan=2 align="center">
		<input class="ricerca" type=submit name="cerca" value="Cerca" onClick="GeneraLinkPagina(richiesta);">
		</td>
		<td></td>
	</tr>
	<tr>
		<td colspan=2><br></td>
	</tr>
</table>
		</center>
			
<?php }	else if ($posted == TRUE){
	
switch($richiesta)
{
case ("sede"):

if ( $numero_sedi == 0)
{echo"<script>alert('Sede non trovata.'); self.close();</script>";}
else
{
   	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<!-- RICERCA CONTRIBUENTE -->
<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=10% align=center></td>
        <td width=40% ><b><?php echo $tipo_sede_completo; ?></b></td>
        <td width=10% align=center><br></td>
        <td width=30% align=left><b>Comune</b></td>
        <td width=10% align=center><br></td>
	</tr>
<?php while($sede_legale = mysql_fetch_array($resultSede, MYSQL_ASSOC))
{      	
      if ($i++ % 2)
      	{$stile_riga = 'class="riga_pari"';}
      else
      	{$stile_riga = 'class="riga_dispari"';}
      	
      	$comune_slash = addslashes($sede_legale['Denominazione']);
      	$sezione_slash = addslashes($sede_legale['Comune']);
?>
	<tr <?php echo $stile_riga ?>>
    	<td width=10% align=center>
    		<input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" 
    		title="Clicca qui per selezionare la sede legale" 
    		onClick="torna_valore('<?php echo $sede_legale['ID']; ?>');">
    	</td>
        <td width=40% ><?php echo $sede_legale['Denominazione']; ?></td>
        <td width=1% align=center><br></td>
        <td width=30% align=left><?php echo $sede_legale['Comune']; ?></td>
        <td width=10% align=center><br></td>
	</tr>
<?php } 
		}?>
</table> <?php     
     	
break;

default:
	break;

}

}	
?>

</body>
</html>