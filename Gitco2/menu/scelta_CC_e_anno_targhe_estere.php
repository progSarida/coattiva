<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

  if (!session_id()) session_start();
  
  if ($_SESSION['username'] == NULL)
  {
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
  }
  
$autorizzazione = get_var('aut_tipo');
$autoriz_progr = get_var('aut_progr');

$c = get_var('c');
$a = get_var('a');

$tiporicerca = get_var('cerco');  // "comune" o "anno"

if ($c == null)
{
	$control = "comune";
}
else 
{
	$control = "anno";
}

if($tiporicerca== null) $tiporicerca="comune";

$query = "SELECT CC_User FROM autenticazione WHERE ID = $autoriz_progr";
$comune_autorizzazione = single_answer_query($query);

if ($comune_autorizzazione == "****" || $comune_autorizzazione == "***+" || 
	$comune_autorizzazione == "XXXX" || $comune_autorizzazione == "YYYY")
	$tiporicerca = "comune";
else 
{
	$c = $comune_autorizzazione;
	$tiporicerca = "anno";
}
  
function ElencoQuiComuni ($tiporicerca, $comuneselected, $autorizzazione)
{
	//alert ($tiporicerca . " e " . $comuneselected . " e " . $autorizzazione);
	if ($tiporicerca == "comune")
	{
		if ($autorizzazione == 1)
		{
			$query = "select DISTINCT CC, Denominazione from enti_gestiti, anni_gestiti 
					WHERE CC_Anno = CC AND Gestione_Targhe_Estere = 'Y'
					order by Denominazione ASC";
		}
		else
		{
			$query = "select DISTINCT CC, Denominazione from enti_gestiti, anni_gestiti 
					where CC_Anno = CC AND Gestione_Targhe_Estere = 'Y'
					order by Denominazione ASC";
		}
		
		$result = safe_query($query);
		$num = mysql_num_rows($result);
		echo <<< INIZIOLISTACOMUNI
		<select id="selectcomune" size=1 onchange="cambiocomune()">
			<option value=''>Selezionare un Ente</option>
			<option value=''>-----------------------------------------------------</option>
		
INIZIOLISTACOMUNI;
		
		while ($cliente = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			if ($cliente['CC'] == $comuneselected) $selectedcom = " selected ";
			else $selectedcom = "";
			
			echo	"<option value=". $cliente['CC'] ." ".$selectedcom; 
			echo    " title=\"Nome del Comune - Codice Catastale (Progressivo del Comune)\">";
			echo	$cliente['Denominazione']." - ".$cliente['CC']." </option>\n";

		}

echo 	"</select>";

	}
	else
	{
		$query = "select Com_Nome from comuni_lista where Com_Codice_Catastale='$comuneselected'";
		$result = single_answer_query($query);

echo	"<strong><p class=\"sel_com\">Ente selezionato&nbsp;:&nbsp;&nbsp;<b>".$result."<b></strong></p>";
echo	"<input id=selectcomune type=hidden name=c value=".$comuneselected.">";
		
	}
}

function ElencoQuiAnni ($tiporicerca, $comuneselected, $annoselected)
{
		
	if ($comuneselected == 'YYYY' || $comuneselected == 'XXXX')
	{
		// non so cosa deve fare		
	}
	else
	{
		// amministratori e tutti quanti i comuni
		$tempcomune = get_var('c');
		if ($tempcomune != $comuneselected)
		{
			$_SESSION['c'] = $comuneselected;
		}
		
		$query ="select Anno from anni_gestiti where CC_Anno ='$comuneselected' AND Gestione_Targhe_Estere = 'Y' order by Anno DESC";

		$result = safe_query($query);
		$res = mysql_num_rows($result);
		
		if ($res == NULL)
		{
		    echo ("Non ci sono anni gestiti per l'Ente selezionato. Inserire un anno.");
		}
		else
		{
			echo <<< LISTAANNI
				<select id="selectanno" size=1>
						<option value=''>Anno</option>
						<option value=''>---------------</option>\n
			
LISTAANNI;
			
			while ($year = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				if ($year['Anno'] == $annoselected) $selectedyear = " selected ";
				else $selectedyear = "";

				echo "<option ".$selectedyear." value=\"".$year['Anno']."\">".$year['Anno']."</option>\n";

			}
			
			echo "</select>";

		}
	}
	return;
}


  
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Selezione Comune/Anno</title>
	
	<LINK REL=StyleSheet HREF="/gitco2/CSS/classi_semplici.css" TYPE="text/css" MEDIA=screen>
	
	<script type="text/javascript" src="/gitco2/librerie/js/funzioni.js"></script>

	<script type="text/javascript" src="/gitco2/librerie/js/JQuery.js"></script>
	
<script>

function cambiocomune ()
{
	var c = $("#selectcomune").val();
	var a = $("#selectanno").val();
	var cerco = "anno";
	strLink = "scelta_CC_e_anno_targhe_estere.php?";
	strLink += "c=" + c;
	strLink += "&a=" + a;
	strLink += "&cerco=" + cerco;
	location.href = strLink;
}

function confermascelte()
{
	var c = $("#selectcomune").val();
	var a = $("#selectanno").val();
	strLink = "home_targhe_estere.php?";
	strLink += "c=" + c;
	strLink += "&a=" + a;
	
	if(a.length!=4)
	{
		alert("Selezionare l'anno!");
	}
	else if(a.length==4)
	{
		location.href = strLink;
	}
}

</script>
</head>
<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:8%;">
	<tr>
		<td class="text_center width15">
		<img src="/gitco2/immagini/sarida_2.gif" alt="Logo dell'Azienda" width="100" height="45" border="0">
		</td>
		<td class="text_center"><font class="titolo font24" >Gestione Integrata Tributi Comunali</font></td>
		<td class="text_center width15">
		<img src="/gitco2/immagini/sarida_2.gif" alt="Logo dell'Azienda" width="100" height="45" border="0">
		</td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:92%;">
	<tr height=6%>
		<td align=left width=15%>
			<a href="#" onMouseover="title='Help'" onClick="javascript:window.open('/gitco2/help/selezione_comuni_anni.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes')">
			<img src="/gitco2/immagini/Help Blue.png" width="65" height="65" border="0"></a>
		</td>
		<td align="center">
			<font class="titolo font22 under_decor">Selezione Ente/Anno</font>
		</td>
		<td align=right width=15%>
			<a onMouseover="title='Home Page Gitco'" href="home_targhe_estere.php?c=<?php echo $c?>&a=<?php echo $a?>" target="_top">
			<img src="/gitco2/immagini/home2.png" width="60" height="60" border="0" alt="Torna al menu generale"></a>
		</td>
	</tr>
	<tr>
		<td align="center" colspan=3>
				Seleziona Ente:<br>
		<?php
		ElencoQuiComuni($tiporicerca, $c, $autorizzazione);
		?>
		</td>
	</tr>
	<tr>
		<td align="center" colspan=3>
				Seleziona Anno:<br>
		<?php
		ElencoQuiAnni($tiporicerca, $c, $a);
		?>
		</td>
	</tr>
	<tr>
		<td align="center" colspan=3><br>

			<?php if($control == "anno" )
			{?> 
			
				<input class="sfondo_azzurro_button " type="button" value="Conferma" onclick="confermascelte();"> 
				
			<?php  }?>
			
		</td>
	</tr>
</table>
		
</body>
</html>