<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";

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

$settore = get_var('settore');

switch ($richiesta)
{		
	case ("ricCrono"):
	
		$titolopagina = "Ricerca Cronologico";
		$linkricerca = "ricerca_alert_modale.php?richiesta=ricCrono&posted=true&c=".$c."&a=".$a;
	
		$nomecella = array();
		$nomecella[0] = "<b>Cronologico</b>";
		$nomecella[1] = "<b>Anno</b>";
	
		$cella = array();
		$cella[0] = "<input type=text class='text_right' name=crono value='' size=7 id=crono>";
		$cella[1] = "<input type=text class='text_right' name=anno value='' size=7 id=anno>";
	
		$campo = "";
		$nomecampo = "";
		$riga = "";
	
		if( $posted == true )
		{
			$id_crono = get_var('crono');
			$anno_crono  = get_var('anno');
	
			$query = "SELECT AT.*, PA.Utente_ID FROM atto AS AT, partita_tributi AS PA ";
			$query.= "WHERE AT.Partita_ID = PA.ID AND AT.CC = '".$c."' AND AT.Anno_Cronologico = '".$anno_crono."' AND AT.ID_Cronologico = '".$id_crono."' ";
			
			$resultCrono = safe_query($query);
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

					case ("ricCrono"):
	
						var idCrono = $("#crono").val();
						var annoCrono = $("#anno").val();
						
						link +="&crono="+idCrono+"&anno="+annoCrono;

					break;				
				
				}
				
				location.href = link;
				
			}

       function torna_valore(value)
       {
           try{
               window.opener.callParent(value);
           }
           catch(e){
               alert(e.description);
           }

           self.close();
       }

function partita_ogg( value1, value2, value3, value4 )
{
	ricerca_oggetto = { ID:value1, Anno:value2, Crono:value3, Utente:value4 } ;
			        
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
case ('ricCrono'):

	if (mysql_num_rows($resultCrono)==0)
	{echo"<script>alert('Atto non trovato.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td class="text_center width5"></td>
    	<td class="text_center width15"><b>Cronologico</b></td>
    	<td class="text_center width5"><br></td>
    	<td class="text_left width70"><b>Informazioni cartella</b></td>
        <td class="text_center width5"><br></td>
    </tr>
<?php 

while($Crono_trovato = mysql_fetch_array($resultCrono, MYSQL_ASSOC))
	{      	
         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="text_center width5"><input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente" 
    	onClick="partita = partita_ogg('<?php echo $Crono_trovato['ID']; ?>','<?php echo $Crono_trovato['Anno_Cronologico']; ?>','<?php echo $Crono_trovato['ID_Cronologico']; ?>','<?php echo $Crono_trovato['Utente_ID']; ?>'); torna_valore(partita);"></td>
        <td class="text_center width15"><?php echo $Crono_trovato['ID_Cronologico']."/".$Crono_trovato['Anno_Cronologico']; ?></td>
        <td class="text_center width5"><br></td>
        <td class="text_left width70"><?php echo $Crono_trovato['Info_Cartella']; ?></td>
        <td class="text_center width5"><br></td>
        
        
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