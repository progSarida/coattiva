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
		
		 $titolopagina = "Ricerca Banca";
		 $linkricerca = "ricerca_sede.php?richiesta=sede&posted=true&c=".$c."&tipo_sede_completo=".$tipo_sede_completo;
		       
		 $nomecella = array();
		 $nomecella[1] = "<b>Comune</b>";
		 $nomecella[0] = "<b>Tipo banca</b>";
	     $nomecella[2] = "<b>Denominazione</b>";
	     
         $cella = array();
	     $cella[1] = "<input id=comune_banca type=text name=comune_banca size=40>";
	     $cella[0] = "Sede <input id=tipo_banca type=radio name=tipo_banca value='sede' checked>&nbsp;&nbsp;Filiale <input id=tipo_banca type=radio name=tipo_banca value='filiale'>";
	     $cella[2] = "<input id=denominazione type=text name=denominazione size=40>";
	     
	     $campo = "";
	     $nomecampo = "";
	     $riga = "";
		
		if( $posted == true )
		{
			$denominazione = get_var('denominazione');
			$tipo_banca = get_var('tipo_banca');
			$comune_banca = get_var('comune_banca');
			
			if($denominazione == null)
				$denominazione="";
			
			$query = "SELECT * FROM banca WHERE CC = '".$c."' AND Denominazione LIKE '%".$denominazione."%' ";
			if($tipo_banca != null)
				$query.= " AND Tipo_Banca = '".$tipo_banca."'";
			if($comune_banca != null)
				$query.= " AND Comune LIKE \"%".$comune_banca."%\"";
			
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

					var denominazione = $("#denominazione").val();
					var comune_banca = $("#comune_banca").val();
					var tipo_banca = $('#tipo_banca:checked').val();

					link +="&denominazione="+denominazione;
					link +="&comune_banca="+comune_banca;
					link +="&tipo_banca="+tipo_banca;
			
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

function torna_sede( value1, value2, value3 )
{
	ricerca_oggetto = { ID:value1, ID_Collegamento:value2, Tipo_banca:value3 } ;
			        
	torna_valore(ricerca_oggetto);
}

		</script>
</head>

<body class="sfondo_new_gitco">
	<center>
	
	<h3><b><?php echo $titolopagina; ?></b></h3>

	</center>
	
<?php if ($posted == NULL) { ?>
			
			<center>
<table class="table_modale pwidth700" cellspacing="5" cellpadding="0" border="0">	

	<tr>
		<td colspan=4><br></td>
	</tr>
<?php for($k=0;$k<count($cella);$k++){?>


	<tr>
		<td class="width5"></td>
		<td class="text_left"><?php echo $nomecella[$k]; ?></td>							
		<td class="width49 text_left"><?php echo $cella[$k]; ?></td>
		<td class="width5"></td>
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
{echo"<script>alert('Banca non trovata.'); self.close();</script>";}
else
{
   	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<!-- RICERCA CONTRIBUENTE -->
<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
        <td width=40% ><b><?php echo $tipo_sede_completo; ?></b></td>
        <td width=15% align=left><b>Tipo</b></td>
        <td width=20% align=left><b>Comune</b></td>
        <td width=20% align=left><b>Partita Iva</b></td>
	</tr>
<?php while($banca = mysql_fetch_array($resultSede, MYSQL_ASSOC))
{      	
      if ($i++ % 2)
      	{$stile_riga = 'class="riga_pari"';}
      else
      	{$stile_riga = 'class="riga_dispari"';}
      	
?>
	<tr <?php echo $stile_riga ?>>
    	<td width=5% align=center>
    		<input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" 
    		title="Clicca qui per selezionare la sede legale" 
    		onClick="torna_sede('<?php echo $banca['ID']?>','<?php echo $banca['ID_Collegamento']?>','<?php echo $banca['Tipo_Banca']?>');">
    	</td>
        <td width=40% ><?php echo $banca['Denominazione']; ?></td>
        <td width=15% align=left><?php echo ucfirst($banca['Tipo_Banca']); ?></td>
        <td width=20% align=left><?php echo $banca['Comune']; ?></td>
        <td width=20% align=left><?php echo $banca['Partita_Iva']; ?></td>
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