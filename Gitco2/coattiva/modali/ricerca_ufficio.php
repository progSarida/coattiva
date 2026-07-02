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
$tipo = get_var('tipo');
$posted = get_var('posted');
$c = get_var('c');
$a = get_var('a');

switch ($richiesta)
{
    case ("generale"):

        $titolopagina = "Ricerca Utente/Partita";
        $linkricerca = "ricerca_ufficio.php?richiesta=dettaglio&c=".$c;

        $nomecella = array();
        $nomecella[0] = "<b>Giudice di pace</b>";
        $nomecella[1] = "<b>Tribunale</b>";
        $nomecella[2] = "<b>Commissione tributaria provinciale</b>";
        $nomecella[3] = "<b>Commissione tributaria regionale</b>";
        $nomecella[4] = "<b>Corte d'appello</b>";
        $nomecella[5] = "<b>Corte di cassazione</b>";

        $cella = array();
        $cella[0] = "<input type=radio name=tipo value=giudice checked>";
        $cella[1] = "<input type=radio name=tipo value=tribunale>";
        $cella[2] = "<input type=radio name=tipo value=comm_trib_prov>";
        $cella[3] = "<input type=radio name=tipo value=comm_trib_reg>";
        $cella[4] = "<input type=radio name=tipo value=appello>";
        $cella[5] = "<input type=radio name=tipo value=cassazione>";

        $campo = "";
        $nomecampo = "";
        $riga = "";


        break;
		
	case ("dettaglio"):
		switch($tipo){
            case "giudice":
                $denominazione = "Giudice di Pace";
                break;
            case "tribunale":
                $denominazione = "Tribunale";
                break;
            case "comm_trib_prov":
                $denominazione = "Commissione Tributaria Provinciale";
                break;
            case "comm_trib_reg":
                $denominazione = "Commissione Tributaria Regionale";
                break;
            case "appello":
                $denominazione = "Corte d'Appello";
                break;
            case "cassazione":
                $denominazione = "Corte di Cassazione";
                break;
            default:
                $denominazione = "??????";
        }

		 $titolopagina = "Ricerca ".$denominazione;
		  $linkricerca = "ricerca_ufficio.php?richiesta=dettaglio&tipo=".$tipo."&posted=true&c=".$c;
		       
		    $nomecella = array();
	     $nomecella[0] = "<b>Comune</b>";
	     
	            $cella = array();
		     $cella[0] = "<input id=searchedValue type=text name=searchedValue size=40>";
		     
		     $campo = "";
		     $nomecampo = "";
		     $riga = "";
		
		if( $posted == true )
		{

            $searchedValue = get_var('searchedValue');
			if($searchedValue == null)
			    $searchedValue="";
			
			$query = "SELECT * FROM ufficio_giudiziario WHERE Tipo = '".$tipo."' AND Comune LIKE '%".$searchedValue."%' AND CC='".$c."'";

			$result = safe_query($query);
			$resultRows = mysql_num_rows($result);
			
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

                    case ("generale"):

                        var tipo = $("[name=tipo]:checked").val();

                        link +="&tipo="+tipo;

                        break;

                    case ("dettaglio"):

                        var searchedValue = $("#searchedValue").val();

                        link +="&searchedValue="+searchedValue;

                    break;
				}

                location.href = link;
				
			}

function nuovo_oggetto( value1, value2 , value3 , value4, value5, value6 )
{
	ricerca_oggetto = { ID:value1, comune:value2 , CC:value3, Sez:value4, Ind:value5, Tipo:value6 } ;
						        
	return ricerca_oggetto;
}

function torna_valore(value)
{
    window.opener.callParent(value);
    try{
        opener.callParent(value);
    }
    catch(e){
        alert(e.description);
    }

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
case ("dettaglio"):

if (mysql_num_rows($result)==0)
{echo"<script>alert('".$denominazione." non trovato.'); self.close();</script>";}
else
{
   	$i = 0; // contatore : serve per identificare righe pari e righe dispari
    $denom_slash =  addslashes($denominazione);
?>

<!-- RICERCA CONTRIBUENTE -->
<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=10% align=center></td>
        <td width=20% ><b>Comune</b></td>
        <td width=10% align=center><br></td>
        <td width=10% align=left><b>Sezione</b></td>
        <td width=10% align=center><br></td>
        <td width=30% align=left><b>Indirizzo</b></td>
        <td width=10% align=center><br></td>
	</tr>
<?php while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{      	
      if ($i++ % 2)
      	{$stile_riga = 'class="riga_pari"';}
      else
      	{$stile_riga = 'class="riga_dispari"';}
      	
      	$comune_slash = addslashes($row['Comune']);
      	$sezione_slash = addslashes($row['Sezione']);
      	$indirizzo = $row['Toponimo'];
      	if($row['Civico']>0)
      	    $indirizzo.= " ".$row['Civico'];
        if($row['Esponente']!="")
            $indirizzo.= $row['Esponente'];
        if($row['Interno']>0)
            $indirizzo.= "/".$row['Interno'];
        $indirizzo_slash = addslashes($indirizzo);

?>
	<tr <?php echo $stile_riga ?>>
    	<td width=10% align=center>
    		<input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" 
    		title="Clicca qui per selezionare il tribunale" 
    		onClick="new_ogg = nuovo_oggetto('<?php echo $row['ID']; ?>','<?php echo $comune_slash; ?>','<?php echo $row['CC']; ?>','<?php echo $sezione_slash; ?>','<?php echo $indirizzo_slash; ?>','<?php echo $denom_slash; ?>'); torna_valore(new_ogg);">
    	</td>
        <td width=20% ><?php echo $row['Comune']; ?></td>
        <td width=1% align=center><br></td>
        <td width=10% align=left><?php echo $row['Sezione']; ?></td>
        <td width=10% align=center><br></td>
        <td width=30% align=left><?php echo $indirizzo; ?></td>
        <td width=10% align=center><br></td>
	</tr>
<?php } 
		}?>
</table> <?php     
     	
break;

?>
</table> <?php     
     	
break;

default:
	break;

}

}	
?>

</body>
</html>