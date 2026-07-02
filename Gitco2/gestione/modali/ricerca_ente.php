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
$gruppo = get_var('gruppo');
$c = get_var('c');
$a = get_var('a');

switch ($richiesta)
{
			
	case ("ricComune"):
		
		$titolopagina = "Lista Enti";
		 $linkricerca = "ricerca_ente.php?richiesta=ricComune&posted=true";
			
		   $nomecella = array();
		$nomecella[0] = "<b>Ente</b>";
			
			   $cella = array();
			$cella[0] = "<input type=text name=ric_comune value='' size=20 id=comune";
			    
			   $campo = "";
		   $nomecampo = "";
			    $riga = "";
		
		if( $posted == true )
		{
			$ric_comune =get_var('ric_comune');

      		$query = "SELECT CC, Denominazione, Codici_Unione, Descrizione ";
      		$query.= "FROM enti_gestiti	WHERE Denominazione LIKE '".$ric_comune."%' ";
      		$query.= "ORDER BY Denominazione";

      			
      		$resultComune = safe_query($query);
      			
      		$num_comuni = mysql_num_rows($resultComune);
				
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
          var gruppo = "<?php echo $gruppo; ?>";

			function GeneraLinkPagina(richiesta)
			{
				var link = "<?php if(isset($linkricerca)){echo $linkricerca;}else{echo "";} ?>";

				switch (richiesta)
				{

				case ("ricComune"):

					var comuneRic = $(":text").val();
					var italianoEstero = $(":radio:checked").val();
					
					link +="&ric_comune="+comuneRic;
					link +="&italiano_estero="+italianoEstero;

				break;
				
				}
				
				window.name = "ricerca";
				window.open(link, "ricerca");
				
			}

			function gruppoOggetto( value1 )
			{

				ricerca_oggetto = { gruppo:value1 };
			
				return ricerca_oggetto;

			}

function cerca_via()
{

	window.name = "ricerca";
	window.open(link, "ricerca");
}
			
function torna_valore(value)
{
	window.returnValue = value;
	self.close();
}

function Comune( value1, value2, value3, value4 )
{
	ricerca_oggetto = { comune:value1, CC:value2, prov_sigla:value3, cap:value4 } ;
			        
    return ricerca_oggetto;
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

case('ricComune'):
	
	if ($num_comuni==0){	echo"<script>alert('Non è stato trovato nessun Ente simile a \"$ric_comune\".'); self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=50%><b>Comune</b></td>
		<td width=5% align=center><br></td>
		<td width=10% align=center><b>Codice</b></td>
		<td width=5% align=center><br></td>
		<td width=20% align=center><b>Codici Unione</b></td>
		<td width=5% align=center><br></td>
	</tr>

<?php
        while($com_trovato = mysql_fetch_array($resultComune, MYSQL_ASSOC))
        {
        	$com_nome_temp = addslashes($com_trovato['Denominazione']);
            if ($i++ % 2)
            {$stile_riga = 'class="riga_pari"';}
			else
			{$stile_riga = 'class="riga_dispari"';}
?>			

	<tr <?php echo $stile_riga; ?>>
		<td width=5% align=center><input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il comune" 
		onClick="comuneOgg = Comune('<?php echo $com_nome_temp; ?>','<?php echo $com_trovato['CC']; ?>','','');torna_valore(comuneOgg);"></td>
        <td width=50%><?php echo $com_trovato['Denominazione']; ?></td>
		<td width=5% align=center><br></td>
		<td width=10% align=center><?php echo $com_trovato['CC']; ?></td>
		<td width=5% align=center><br></td>
		<td width=20% align=center><?php echo $com_trovato['Codici_Unione']; ?></td>
		<td width=5% align=center><br></td>		
	</tr>

<?php }?>
</table>
<?php }
		
	
break;     

default:
break;
}

}	
?>

</body>
</html>