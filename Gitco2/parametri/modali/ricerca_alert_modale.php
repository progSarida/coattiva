<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";

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

$layout = "<script>$('[tabindex=1]').focus();</script>";

switch ($richiesta)
{		
	case ("ricUfficio"):
		
		$tipo_ufficio = get_var('tipo_ufficio');
		if($tipo_ufficio=="uff_anagrafico")
			$titolopagina = "Lista Uffici Anagrafici";
		else if($tipo_ufficio=="uff_postale")
			$titolopagina = "Lista Uffici Postali";
		
		$linkricerca = "ricerca_alert_modale.php?richiesta=ricUfficio&tipo_ufficio=".$tipo_ufficio."&posted=true";
			
		   $nomecella = array();
		$nomecella[0] = "<b>Ente</b>";
			
			   $cella = array();
			$cella[0] = "<input class='tab' tabindex='1' type=text name=ric_comune value='' size=20 id=comune >";
			    
			   $campo = "";
		   $nomecampo = "";
			    $riga = "";
		
		if( $posted == true )
		{
			$ric_comune = get_var('ric_comune');

      		$query = "SELECT * ";
      		$query.= "FROM ufficio_comune WHERE Comune LIKE '%".addslashes($ric_comune)."%' AND Tipo = '".$tipo_ufficio."'";
      		$query.= "ORDER BY Comune";

      			
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
	
		case ("ricUfficio"):

			var comuneRic = $(":text").val();
			var italianoEstero = $(":radio:checked").val();
			
			link +="&ric_comune="+comuneRic;
			link +="&italiano_estero="+italianoEstero;

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

function Comune( value1, value2, value3, value4, value5, value6, value7, value8, value9, value10, value11, value12, value13, value14, value15, value16, value17, value18, value19, value20, value21 )
{
	ricerca_oggetto = { CC:value1, nome_CC:value2, denominazione:value3, CC_sede:value4, nome_CC_sede:value5, provincia:value6, cap:value7, toponimo:value8, civico:value9, esponente:value10, interno:value11, dettagli:value12, PI:value13, tel:value14, fax:value15, mail:value16, PEC:value17, sito:value18, orario:value19, ID:value20, invio:value21 } ;
      
    return ricerca_oggetto;
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

case('ricUfficio'):
	
	if ($num_comuni==0){	echo"<script>alert('Non è stato trovato nessun ufficio relativo alla ricerca effettuata.'); self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		$j = 0;
?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=15%><b>Comune</b></td>
		<td width=3% align=center><br></td>
		<td width=10% align=center><b>Codice</b></td>
		<td width=3% align=center><br></td>
		<td width=20%><b>Informazioni</b></td>
		<td width=3% align=center><br></td>
		<td width=20%><b>Indirizzo</b></td>
		<td width=3% align=center><br></td>
		<td width=10% align=center><b>Provincia</b></td>
		<td width=3% align=center><br></td>
	</tr>

<?php
        while($com_trovato = mysql_fetch_array($resultComune, MYSQL_ASSOC))
        {
        	$add_tag = "";
        	$CC_ente = $com_trovato['CC'];
        	$comune_ente = new comune($CC_ente);
        	$nome_ente = $comune_ente->Nome;
        	$nome_ente_temp = addslashes($comune_ente->Nome);
        	$com_nome_temp = addslashes($com_trovato['Comune']);
			$com_indirizzo_temp = addslashes($com_trovato['Toponimo']);
        	
            if ($i++ % 2)
            	{$stile_riga = 'class="riga_pari"';}
			else
				{$stile_riga = 'class="riga_dispari"';}
			
			if($j==0)
				$add_tag = "tabindex=1";
			else if($j == $num_comuni-1)
				$add_tag = "onblur=\"blurLast();\"";
			
			$indirizzo_ufficio = $com_trovato['Toponimo']." ".$com_trovato['Civico'];
			if($com_trovato['Esponente']!="")	$indirizzo_ufficio.= $com_trovato['Esponente'];
			if($com_trovato['Interno']!="")		$indirizzo_ufficio.= "/".$com_trovato['Interno'];
?>			
			

	<tr <?php echo $stile_riga; ?>>
		<td width=5% align=center>
		<input title="Clicca qui per inserire il comune" type=image src="/gitco2/immagini/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;"
	onClick="comuneOgg = Comune('<?php echo $com_trovato['CC']; ?>','<?php echo $nome_ente_temp; ?>','<?php echo $com_trovato['Denominazione']; ?>','<?php echo $com_trovato['CC_Comune']; ?>','<?php echo $com_nome_temp; ?>','<?php echo $com_trovato['Provincia']; ?>','<?php echo $com_trovato['Cap']; ?>','<?php echo $com_indirizzo_temp; ?>','<?php echo $com_trovato['Civico']; ?>','<?php echo $com_trovato['Esponente']; ?>','<?php echo $com_trovato['Interno']; ?>','<?php echo $com_trovato['Dettagli']; ?>','<?php echo $com_trovato['Partita_Iva']; ?>','<?php echo $com_trovato['Telefono']; ?>','<?php echo $com_trovato['Fax']; ?>','<?php echo $com_trovato['Mail']; ?>','<?php echo $com_trovato['PEC']; ?>','<?php echo $com_trovato['Sito']; ?>','<?php echo $com_trovato['Orario']; ?>','<?php echo $com_trovato['ID']; ?>','<?php echo $com_trovato['Modalita_Invio']?>');torna_valore(comuneOgg);">
		</td>
               
        <td width=15%><?php echo $com_trovato['Comune']; ?></td>
		<td width=3% align=center><br></td>
		<td width=10% align=center><?php echo $com_trovato['CC']; ?></td>
		<td width=3% align=center><br></td>
		<td width=20%><?php echo $com_trovato['Denominazione']; ?></td>
		<td width=3% align=center><br></td>
		<td width=20%><?php echo $indirizzo_ufficio; ?></td>
		<td width=3% align=center><br></td>
		<td width=10% align=center><?php echo $com_trovato['Provincia']; ?></td>
		<td width=3% align=center><br></td>
		
	</tr>

<?php
$j++;
        }?>
</table>
<?php }
		
	
break;     

default:
break;
}

}

echo $layout;

?>

</body>
</html>