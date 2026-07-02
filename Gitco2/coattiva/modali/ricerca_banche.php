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

$layout = "<script>$('[tabindex=1]').focus();</script>";
$ID_sede = array(null,null,null);
$Denom_sede = array(null,null,null);
$CC_banca = get_var('CC_banca');
$Comune_nome = get_var('Comune_nome');

switch ($richiesta)
{
		
	case ("comuneBanca"):
		
		$titolopagina = "Lista Comuni";
		 $linkricerca = "ricerca_banche.php?richiesta=comuneBanca&posted=true&a=".$a."&c=".$c;
			
		   $nomecella = array();
		$nomecella[0] = "<b>Comune</b>";
			
			   $cella = array();
			$cella[0] = "<input class='tab' tabindex='1' type=text name=ric_comune value='".$Comune_nome."' size=20 id=comune >";
			    
			   $campo = "";
		   $nomecampo = "";
			    $riga = "";
		
		if( $posted == true )
		{
			$ric_comune = get_var('ric_comune');

      		$query = "SELECT Com_Codice_Catastale, Com_Codice_Provincia, Com_Nome, Pro_Sigla, Com_Cap ";
      		$query.= "FROM comuni_lista, province_lista	WHERE Com_Nome LIKE '%".addslashes($ric_comune)."%' ";
      		$query.= "AND Pro_Codice = Com_Codice_Provincia ORDER BY Com_Nome";

      			
      		$resultComune = safe_query($query);
      			
      		$num_comuni = mysql_num_rows($resultComune);
				
		}
		
		break;
		
	case ("ricBanca"):
	
		include_once CLASSI . "/comuni.php";
		
		$titolopagina = "Lista Banche";
		$linkricerca = "ricerca_banche.php?richiesta=ricBanca&posted=true&a=".$a."&c=".$c;
			
		$nomecella = array();
		$nomecella[0] = "<b>Banca</b>";
			
		$cella = array();
		$cella[0] = "<input class='tab' tabindex='1' type=text name=ric_banca value='' size=20 id=comune >";
		 
		$campo = "";
		$nomecampo = "";
		$riga = "";
	
		if( $posted == true )
		{
			$ric_banca = get_var('ric_banca');
			
			$query = "SELECT * FROM banca WHERE Denominazione LIKE '%".$ric_banca."%' AND CC = '*****' AND Tipo_Banca = 'filiale'";
				
			$resultSede = safe_query($query);
			$numero_sedi = mysql_num_rows($resultSede);
	
		}
	
		break;
	
	case ("sedeFiliale"):
		
		include_once CLASSI . "/comuni.php";
		
		$titolopagina = "";
		$linkricerca = "";
			
		$nomecella = array();
		$nomecella[0] = "";
			
		$cella = array();
		$cella[0] = "";
		 
		$campo = "";
		$nomecampo = "";
		$riga = "";
		
		$banca = new banca(null, "*****");
		$array_sedi = $banca->trovaSedeDaFiliale($CC_banca);
		
		for($i=0;$i<3;$i++)
		{
			if($i<count($array_sedi))
			{
				$sede = new banca($array_sedi[$i]['ID_Collegamento'], "*****");
				$ID_sede[$i] = $sede->ID;
				$Denom_sede[$i] = $sede->Denominazione;
			}
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

<title>Ricerca banche</title>
	
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
			case ("comuneBanca"):

				var comuneRic = $(":text").val();
				var italianoEstero = $(":radio:checked").val();
				
				link +="&ric_comune="+comuneRic;
				link +="&italiano_estero="+italianoEstero;

			break;
			
			case ("ricBanca"):

				var sedeRic = $("#sede").val();

				link +="&sede="+sedeRic;
		
			break;
		}
		
		window.name = "ricerca";
		window.open(link, "ricerca");
		
	}

	function cerca_filiali(codice_catastale, comune_nome)
	{
		link_banche = "ricerca_banche.php?richiesta=sedeFiliale&CC_banca="+codice_catastale+"&Comune_nome="+comune_nome+"&a=<?php echo $a?>&c=<?php echo $c ?>";
		window.name = "ricerca";
		window.open(link_banche, "ricerca");
	}
				
	function torna_valore(value)
	{
		window.returnValue = value;
		self.close();
	}
	
	function banche_sedi( ID_sede, Denom_sede, ID_sede2, Denom_sede2, ID_sede3, Denom_sede3,CC_ricerca, Comune_ricerca )
	{
		sede_oggetto = { ID_1:ID_sede, denominazione_1:Denom_sede, ID_2:ID_sede2, denominazione_2:Denom_sede2, ID_3:ID_sede3, denominazione_3:Denom_sede3, CC_ricerca:CC_ricerca, Comune_ricerca:Comune_ricerca } ;
				        
	    return sede_oggetto;
	}

	function torna_sede( value1, value2, value3 )
	{
		ricerca_oggetto = { ID:value1, Denominazione:value2, Comune:value3 } ;
				        
		torna_valore(ricerca_oggetto);
	}

	if(richiesta == "sedeFiliale")
	{
		sedi_oggetto = banche_sedi("<?php echo $ID_sede[0]; ?>","<?php echo $Denom_sede[0]; ?>", "<?php echo $ID_sede[1]; ?>","<?php echo $Denom_sede[1]; ?>", "<?php echo $ID_sede[2]; ?>","<?php echo $Denom_sede[2]; ?>", "<?php echo $CC_banca; ?>", "<?php echo $Comune_nome; ?>");
		torna_valore(sedi_oggetto);
	}
	
	function blurLast()
	{
		$('[tabindex=1]').focus();
	}
	
	var fn = function (e)
	{
		if (!e)
		{
	    	e = window.event;
		}
	
	    var keycode = e.keyCode;
	    if (e.which)
	        keycode = e.which;
	
		//var src = e.srcElement;
		//if (e.target)
		//src = e.target;
		     	
	//ESC
	    if (27 == keycode)
	    {
	       // Firefox and other non IE browsers
	       if (e.preventDefault)
	       {
	           e.preventDefault();
	           e.stopPropagation();
	       }
	       // Internet Explorer
	       else if (e.keyCode)
	       {
	           e.keyCode = 0;
	           e.returnValue = false;
	           e.cancelBubble = true;
	       }		
	
	       self.close();
	
	       return false;
	   }	    
	};
	
	document.onkeydown = fn;
	
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
		<input tabindex=2 class="ricerca" type=submit name="cerca" value="Cerca" onClick="GeneraLinkPagina(richiesta);">
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
case('comuneBanca'):
	
	if ($num_comuni==0){	echo"<script>alert('Non è stato trovato nessun Ente simile a \"$ric_comune\".'); self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		$j = 0;
?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=50%><b>Comune</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>Provincia</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>Codice</b></td>
		<td width=5% align=center><br></td>
	</tr>

<?php
        while($com_trovato = mysql_fetch_array($resultComune, MYSQL_ASSOC))
        {
        	$add_tag = "";
        	$com_nome_temp = addslashes($com_trovato['Com_Nome']);
            if ($i++ % 2)
            {$stile_riga = 'class="riga_pari"';}
			else
			{$stile_riga = 'class="riga_dispari"';}
			if($j==0)
				$add_tag = "tabindex=1";
			else if($j == $num_comuni-1)
				$add_tag = "onblur=\"blurLast();\"";
?>			
			

	<tr <?php echo $stile_riga; ?>>
		<td width=5% align=center>
		<input type=image src="/gitco2/immagini/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il comune" 
		onClick="cerca_filiali('<?php echo $com_trovato['Com_Codice_Catastale']?>','<?php echo $com_nome_temp; ?>');"></td>
        <td width=50%><?php echo $com_trovato['Com_Nome']; ?></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><?php echo $com_trovato['Pro_Sigla']; ?></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><?php echo $com_trovato['Com_Codice_Catastale']; ?></td>
		<td width=5% align=center><br></td>
	</tr>

<?php
$j++;
        }?>
</table>
<?php }
		
	
break;     

case ("ricBanca"):

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
        <td width=35% ><b>Banca filiale</b></td>
        <td width=20% align=left><b>Comune</b></td>
        <td width=35% align=left><b>Sede collegata</b></td>        
        <td width=5% align=left><br></td>
	</tr>
<?php while($banca_filiale = mysql_fetch_array($resultSede, MYSQL_ASSOC))
{      	
      if ($i++ % 2)
      	{$stile_riga = 'class="riga_pari"';}
      else
      	{$stile_riga = 'class="riga_dispari"';}
      	
      	$comune_slash = addslashes($banca_filiale['Denominazione']);
      	$sezione_slash = addslashes($banca_filiale['Comune']);
      	
      	$sede_collegata = new banca($banca_filiale['ID_Collegamento'], "*****");
?>
	<tr <?php echo $stile_riga ?>>
    	<td width=5% align=center>
    		<input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" 
    		title="Clicca qui per selezionare la banca" 
    		onClick="torna_sede('<?php echo $sede_collegata->ID; ?>','<?php echo $sede_collegata->Denominazione; ?>','<?php echo $sede_collegata->Comune; ?>');">
    	</td>
        <td width=35 ><?php echo $banca_filiale['Denominazione']; ?></td>
        <td width=20% align=left><?php echo $banca_filiale['Comune']; ?></td>
        <td width=35% align=left><?php echo $sede_collegata->Denominazione." (".$sede_collegata->Comune.")"; ?></td>
        <td width=5% align=left><br></td>
	</tr>
<?php } 
		}?>
</table> <?php     
     	
break;

default:
break;
}

}

echo $layout;

?>

</body>
</html>