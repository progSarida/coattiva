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

$cap_banca = $cls_help->getVar('cap_banca');
$CC_banca = $cls_help->getVar('CC_banca');

$ID_sede = array();
switch ($richiesta)
{

	case ("singola"):

		$tipo = $cls_help->getVar('tipo');
		$denominazione = $cls_help->getVar('denominazione');
		$checked_sede = "checked";
		$checked_filiale = "";
		if($tipo=="filiale")
		{
			$checked_filiale = "checked";
			$checked_sede = "disabled";
		}
		else if($tipo=="sede")
		{
			$checked_sede = "checked";
			$checked_filiale = "disabled";
		}

		$titolopagina = "Ricerca Banca";
		$linkricerca = "ricerca_banche_mod.php?richiesta=singola&posted=true&c=".$c;

		$nomecella = array();

		$nomecella[0] = "<b>Tipo banca</b>";
	    $nomecella[1] = "<b>Denominazione</b>";
	    $nomecella[2] = "<b>Comune</b>";
	    $nomecella[3] = "<b>CAP</b>";

        $cella = array();

	    $cella[0] = "Sede <input id=tipo_banca type=radio name=tipo_banca value='sede' ".$checked_sede.">&nbsp;&nbsp;Filiale <input id=tipo_banca type=radio name=tipo_banca value='filiale' ".$checked_filiale.">";
	    $cella[1] = "<input id=denominazione type=text name=denominazione value='".$denominazione."' size=40>";
	    $cella[2] = "<input id=comune_banca type=text name=comune_banca size=40>";
	    $cella[3] = "<input id=cap_banca type=text name=cap_banca size=40>";

	    $campo = "";
	    $nomecampo = "";
	    $riga = "";

		if( $posted == true )
		{
			$denominazione = $cls_help->getVar('denominazione');
			$tipo_banca = $cls_help->getVar('tipo_banca');
			$comune_banca = $cls_help->getVar('comune_banca');

			if($denominazione == null)
				$denominazione="";

			$query = "SELECT * FROM banca WHERE CC = '".$c."' AND Denominazione LIKE '%".$denominazione."%' ";
			if($tipo_banca != null)
				$query.= " AND Tipo_Banca = '".$tipo_banca."'";
			if($comune_banca != null)
				$query.= " AND Comune LIKE \"%".$comune_banca."%\"";
			if($cap_banca != null)
				$query.= " AND Cap LIKE \"%".$cap_banca."%\"";

			$resultSede = $cls_db->ExecuteQuery($query);
			$numero_sedi = $cls_db->getNumberRow($resultSede);

		}

		break;

	case ("comuneBanca"):


		$titolopagina = "Lista Comuni";
		$linkricerca = "ricerca_banche_mod.php?richiesta=comuneBanca&posted=true&a=".$a."&c=".$c;

		$nomecella = array();
		$nomecella[0] = "<b>Comune</b>";
		$nomecella[1] = "<b>CAP</b>";

		$cella = array();
		$cella[0] = "<input class='tab' tabindex='1' type=text name=ric_comune value='".$Comune_nome."' size=20 id=ric_comune >";
		$cella[1] = "<input class='tab' tabindex='1' type=text name=cap_banca value='".$cap_banca."' size=20 id=cap_banca >";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		if( $posted == true )
		{
			$ric_comune = $cls_help->getVar('ric_comune');

			$query = "SELECT Com_Codice_Catastale, Com_Codice_Provincia, Com_Nome, Pro_Sigla, Com_Cap ";
			$query.= "FROM comuni_lista, province_lista	WHERE Com_Nome LIKE '%".addslashes($ric_comune)."%' ";
			$query.= "AND Pro_Codice = Com_Codice_Provincia ORDER BY Com_Nome";

			$resultComune = $cls_db->ExecuteQuery($query);

			$num_comuni = $cls_db->getNumberRow($resultComune);

		}
		else
		{
			echo "<script>alert('La ricerca automatica delle filiali avviene su un determinato comune. Se il CAP e' inserito la ricerca sara' limitata alla zona del comune a cui si riferisce il CAP.');</script>";
		}

		break;

	case ("sedeFiliale"):

		$titolopagina = "";
		$linkricerca = "";

		$nomecella = array();
		$nomecella[0] = "";

		$cella = array();
		$cella[0] = "";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		$array_sedi = $cls_param->trovaSedeDaFiliale($CC_banca, $cap_banca);
    //print_r($array_sedi);

		for($i=0;$i<count($array_sedi);$i++)
		{
      $query = "SELECT * FROM banca WHERE ID = '" . $array_sedi[$i]['ID_Collegamento'] . "' AND CC = '*****'";
      $param = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

			$ID_sede[$i] = $param["ID"];
			$Denom_sede[$i] = $param["Denominazione"];
			$Pass_sede[$i] = $param["Password"];
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

					case ("singola"):

						var denominazione = $("#denominazione").val();
						var comune_banca = $("#comune_banca").val();
						var tipo_banca = $('#tipo_banca:checked').val();
						var cap_banca = $('#cap_banca').val();

						link +="&denominazione="+denominazione;
						link +="&comune_banca="+comune_banca;
						link +="&tipo_banca="+tipo_banca;
						link +="&cap_banca="+cap_banca;

					break;

					case ("comuneBanca"):

						var comuneRic = $("#ric_comune").val();
						var italianoEstero = $(":radio:checked").val();
						var cap_banca = $('#cap_banca').val();

						link +="&ric_comune="+comuneRic;
						link +="&italiano_estero="+italianoEstero;
						link +="&cap_banca="+cap_banca;

					break;

				}

                location.href = link;

			}

       function torna_valore(value)
       {
           console.log(value);
           //alert("tornavalore ricbanca");
         
           try{
               window.top.callParent(value);
           }
           catch(e){
               alert(e.description);
           }

           self.close();
       }

function cerca_filiali(codice_catastale, comune_nome, cap_banca)
{
	link_banche = "ricerca_banche_mod.php?richiesta=sedeFiliale&CC_banca="+codice_catastale+"&Comune_nome="+comune_nome+"&a=<?php echo $a?>&c=<?php echo $c ?>&cap_banca="+cap_banca;
	location.href = link_banche;
}

if(richiesta == "sedeFiliale")
{

<?php
	$sedi_oggetto = "CC_ricerca:\"".$CC_banca."\", Comune_ricerca:\"".$Comune_nome."\", Cap_ricerca:\"".$cap_banca."\"";
	for($i=0;$i<count($ID_sede);$i++)
	{
		$sedi_oggetto.= ", ID_".$i.":".$ID_sede[$i].", denominazione_".$i.":\"".$Denom_sede[$i]."\", password_".$i.":\"".$Pass_sede[$i]."\"";
	}

?>
	sede_oggetto = {<?php echo $sedi_oggetto; ?>};

	torna_valore(sede_oggetto);
}

function torna_banca( value1, value2, value3, value4, value5 )
{
    //alert("function torna banca pag ricerca banche mod");
	ricerca_oggetto = { ID:value1, ID_Collegamento:value2, Tipo_banca:value3 , Denominazione:value4, Password:value5} ;

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
case ("singola"):

if ( $numero_sedi == 0)
{echo"<script>alert('Banca non trovata.'); self.close();</script>";}
else
{
   	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<!-- RICERCA CONTRIBUENTE -->
<table class="table_modale pwidth750" align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
        <td width=40% ><b>Banca</b></td>
        <td width=15% align=left><b>Partita Iva</b></td>
        <td width=10% align=left><b>Tipo</b></td>
        <td width=20% align=left><b>Comune</b></td>
        <td width=10% align=left><b>Cap</b></td>
	</tr>
<?php while($banca = mysqli_fetch_array($resultSede, MYSQLI_ASSOC))
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
    		onClick="torna_banca('<?php echo $banca['ID']?>','<?php echo $banca['ID_Collegamento']?>','<?php echo $banca['Tipo_Banca']?>','<?php echo addslashes($banca['Denominazione']); ?>','<?php echo $banca['Password']?>');">
    	</td>
        <td width=40% ><?php echo $banca['Denominazione']; ?></td>
        <td width=15% align=left><?php echo $banca['Partita_Iva']; ?></td>
         <td width=10% align=left><?php echo ucfirst($banca['Tipo_Banca']); ?></td>
        <td width=20% align=left><?php echo $banca['Comune']; ?></td>
        <td width=10% align=left><?php echo $banca['Cap']; ?></td>
	</tr>
<?php }
		}?>
</table> <?php

break;

case('comuneBanca'):

	if ($num_comuni==0){	echo"<script>alert('Non � stato trovato nessun Ente simile a \"$ric_comune\".'); self.close();</script>";}
	else
	{
		if($cap_banca!="")
		{
			$cerca_cap = "Il CAP ".$cap_banca." verra' verificato dopo la scelta del comune.<br>";
			$cerca_cap.= "La filiale delle POSTE ITALIANE verra' caricata indipendentemente dalla scelta del CAP.";
		}
		else
			$cerca_cap = "";

		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		$j = 0;
?>

<table align=center cellspacing=0 border=0>
	<?php if($cap_banca!="")
	{?>

	<tr class = "riga_pari text_center" style="height:35px;" >
		<td colspan=8><?php echo $cerca_cap; ?></td>
	</tr>
	<tr class = riga_pari>
		<td colspan=8><hr></td>
	</tr>

	<?php }?>

	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=40%><b>Comune</b></td>
		<td width=5% align=center><br></td>
		<td width=10% align=center><b>Provincia</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>Cap</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>Codice</b></td>

	</tr>

<?php
        while($com_trovato = mysqli_fetch_array($resultComune, MYSQLI_ASSOC))
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
		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il comune"
		onClick="cerca_filiali('<?php echo $com_trovato['Com_Codice_Catastale']?>','<?php echo $com_nome_temp; ?>', '<?php echo $cap_banca; ?>');"></td>
        <td width=40%><?php echo $com_trovato['Com_Nome']; ?></td>
		<td width=5% align=center><br></td>
		<td width=10% align=center><?php echo $com_trovato['Pro_Sigla']; ?></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><?php echo $com_trovato['Com_Cap']; ?></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><?php echo $com_trovato['Com_Codice_Catastale']; ?></td>
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
?>

</body>
</html>
