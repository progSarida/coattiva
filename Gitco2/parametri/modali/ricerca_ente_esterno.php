<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_paramUtils.php";

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
  header("Location:accesso_negato.php");
  die;
}

$richiesta = $cls_help->getVar('richiesta');
$posted = $cls_help->getVar('posted');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$Comune_nome = $cls_help->getVar('Comune_nome');

$cap = $cls_help->getVar('cap');
$CC_Ente = $cls_help->getVar('CC_Ente');

$ID_sede = array();
switch ($richiesta)
{
	case ("previdenza"):
			$denominazione = $cls_help->getVar('denominazione');
			$titolopagina = "Ricerca prevdenza";
			$linkricerca = "ricerca_ente_esterno.php?richiesta=previdenza&posted=true&c=".$c;
			$nomecella[0] = "<b>Denominazione</b>";
			$nomecella[1] = "<b>Comune</b>";
			$nomecella[2] = "<b>CAP</b>";
	
			$cella = array();

			$cella[0] = "<input id=denominazione type=text name=denominazione value='".$denominazione."' size=40>";
			$cella[1] = "<input id=comune_ente type=text name=comune_ente size=40>";
			$cella[2] = "<input id=cap type=text name=cap size=40>";

			$campo = "";
			$nomecampo = "";
			$riga = "";
			if( $posted == true )
			{
				$denominazione = $cls_help->getVar('denominazione');
				$tipo_ente = $cls_help->getVar('tipo_ente');
				$comune_ente = $cls_help->getVar('comune_ente');

				if($denominazione == null)
					$denominazione="";

				$query = "SELECT * FROM enti_esterni WHERE  CC = '*****' AND Denominazione LIKE '%".$denominazione."%' ";
				if($tipo_ente != null)
					$query.= " AND Tipo = '".$tipo_ente."'";
				if($comune_ente != null)
					$query.= " AND Comune LIKE \"%".$comune_ente."%\"";
				if($cap != null)
					$query.= " AND Cap LIKE \"%".$cap."%\"";

				
				$resultSede = $cls_db->ExecuteQuery($query);
				$numero_sedi = $cls_db->getNumberRow($resultSede);

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


  	<script>

       var richiesta = "<?php echo $richiesta; ?>";


		function GeneraLinkPagina(richiesta)
		{
			var link = "<?php if(isset($linkricerca)){echo $linkricerca;}else{echo "";} ?>";

			switch (richiesta)
			{

				case ("previdenza"):

					var denominazione = $("#denominazione").val();
					var comune_ente = $("#comune_ente").val();
					var tipo_ente = "previdenza";
					var cap = $('#cap').val();

					link +="&denominazione="+denominazione;
					link +="&comune_ente="+comune_ente;
					link +="&tipo_ente="+tipo_ente;
					link +="&cap="+cap;

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





function torna_ente(value1,value2)
{
	ricerca_oggetto = {prog:value1,Denominazione:value2};
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
<table class="table_modale pwidth750" cellspacing="5" cellpadding="0" border="0">

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
case ("previdenza"):

if ( $numero_sedi == 0)
{echo"<script>alert('Ente non trovato.'); self.close();</script>";}
else
{
   	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<!-- RICERCA CONTRIBUENTE -->
<table class="table_modale pwidth750" align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
        <td width=40% ><b>Ente</b></td>
        <td width=15% align=left><b>Partita Iva</b></td>
        <td width=10% align=left><b>Tipo</b></td>
        <td width=20% align=left><b>Comune</b></td>
        <td width=10% align=left><b>Cap</b></td>
	</tr>
<?php while($ente = mysqli_fetch_array($resultSede, MYSQLI_ASSOC))
{
      if ($i++ % 2)
      	{$stile_riga = 'class="riga_pari"';}
      else
      	{$stile_riga = 'class="riga_dispari"';}

?>
	<tr <?php echo $stile_riga ?>>
    	<td width=5% align=center>
    		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;"
    		title="Clicca qui per selezionare la banca"
    		onClick="torna_ente('<?php echo $ente['progressivo']?>','<?php echo $ente['Denominazione']?>');">
    	</td>
        <td width=40% ><?php echo $ente['Denominazione']; ?></td>
        <td width=15% align=left><?php echo $ente['Partita_Iva']; ?></td>
         <td width=10% align=left><?php echo ucfirst($ente['Tipo']); ?></td>
        <td width=20% align=left><?php echo $ente['Comune']; ?></td>
        <td width=10% align=left><?php echo $ente['Cap']; ?></td>
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
