<?php
/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";*/

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";

$cls_help = new cls_help();
$cls_db = new cls_db();

//if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
  header("Location:accesso_negato.php");
  die;
}

$richiesta = $cls_help->getVar('richiesta');
$posted = $cls_help->getVar('posted');
$gruppo = $cls_help->getVar('gruppo');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$layout = "<script>$('[tabindex=1]').focus();</script>";


		$titolopagina = "Lista Comuni";
		 $linkricerca = "ricerca_alert_modale.php?richiesta=ricComune&posted=true";

		   $nomecella = array();
		$nomecella[0] = "<b>Ente</b>";

			   $cella = array();
			$cella[0] = "<input class='tab' tabindex='1' type=text name=ric_comune value='' size=20 id=comune >";

			   $campo = "";
		   $nomecampo = "";
			    $riga = "";

		if( $posted == true )
		{
			$ric_comune =$cls_help->getVar('ric_comune');

      		$query = "SELECT Com_Codice_Catastale, Com_Codice_Provincia, Com_Nome, Pro_Sigla, Com_Cap ";
      		$query.= "FROM comuni_lista, province_lista	WHERE Com_Nome LIKE '%".addslashes($ric_comune)."%' ";
      		$query.= "AND Pro_Codice = Com_Codice_Provincia ORDER BY Com_Nome";


      		$resultComune = $cls_db->ExecuteQuery($query);//safe_query($query);

      		$num_comuni = $cls_db->getNumberRow($resultComune);

		}




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <base target="_self" />

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

				var comuneRic = $(":text").val();
				var italianoEstero = $(":radio:checked").val();

				link +="&ric_comune="+comuneRic;
				link +="&italiano_estero="+italianoEstero;


				self.location.href = link;

//                document.getElementById('goLocation').href = link;
//                document.getElementById('goLocation').click();

			}

			function gruppoOggetto( value1 )
			{

				ricerca_oggetto = { gruppo:value1 };

				return ricerca_oggetto;

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

function Comune( value1, value2, value3, value4 )
{
	ricerca_oggetto = { comune:value1, CC:value2, prov_sigla:value3, cap:value4 } ;

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
    <a href=?? id=?goLocation? style=?display:none;?></a>
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


	if ($num_comuni==0){	echo"<script>alert('Non � stato trovato nessun Ente simile a \"$ric_comune\".'); self.close();</script>";}
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
        while($com_trovato = mysqli_fetch_array($resultComune, MYSQL_ASSOC))
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
		onClick="comuneOgg = Comune('<?php echo $com_nome_temp; ?>','<?php echo $com_trovato['Com_Codice_Catastale']; ?>','<?php echo $com_trovato['Pro_Sigla']; ?>','<?php echo $com_trovato['Com_Cap']; ?>');torna_valore(comuneOgg);"></td>
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


}

echo $layout;

?>

</body>
</html>
