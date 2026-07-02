<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

  if (!session_id()) session_start();

if ($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}


$c = get_var('c');
$a = get_var('a');


$strLink = "fatture.php?c=".$c."&a=".$a;


?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Selezione Azienda </title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>

	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>



</head>
<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:8%;">
	<tr>
		<td width="23%" align="center">
		<img src="/gitco2/immagini/sarida_logo_medium.png" title="Logo dell'Azienda" border="0">
		</td>
		<td align="center"><font class="titolo font24" >Gestione Contabilità</font></td>
		<td width=23% align="center">
		<img src="/gitco2/immagini/sarida_logo_medium.png" title="Logo dell'Azienda" border="0">
		</td>
	</tr>
</table>

<table class="table_azzurra text_center"">
	<tr class="text_center">
		<td >
			<font class="titolo font22 under_decor">Selezione Azienda </font>
		</td>
	</tr>
	<tr>
		<td style="height:200px;">
				Selezione Azienda:<br>
		<?php

		$query = "select Id_Azienda, Nome_Azienda from prima_nota.aziende order by Nome_Azienda ASC";

		$result = safe_query($query);
		$num = mysql_num_rows($result);
		echo '<select id="selectazienda">
			<option value="">Selezionare Azienda</option>';

		while ($azienda = mysql_fetch_array($result))
		{

			echo	"<option value=". $azienda['Id_Azienda'].">".$azienda['Nome_Azienda']." - ".$azienda['Id_Azienda']." </option>";

		}

echo 	"</select>";
		?>
		</td>
		</tr>
<tr>
		<td style="height:300px;">&nbsp;</td>
	</tr>
</table>

<script>


	$('#selectazienda').change(function(){
		$(window.location).attr('href', '<?php echo $strLink; ?>&id='+$(this).val());

	});
</script>
</body>
</html>