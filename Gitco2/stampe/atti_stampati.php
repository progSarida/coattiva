<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$Text = urldecode($_REQUEST['cluster']);
$array_atti = json_decode($Text);

for($i=0;$i<count($array_atti);$i++)
{
	$atto[$i] = new atto($array_atti[$i], $c);
}

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$tipo_atto = get_var('tipo_atto');
$tipo_stampa = get_var('tipo_stampa');

$layout = "";

if($tipo_stampa=="DEFINITIVA")	
{
	$titolo_pag = "Gestione Stampe Definitive";
	$layout.= "<script>$('.link_rar').hide();</script>";
}
else if($tipo_stampa=="FLUSSO")	
{
	$titolo_pag = "Gestione Flussi";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Stampa atti</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>


<!-- ********** VARIABILI ********** -->
<script>

var tipo_atto = "<?php echo $tipo_atto ?>";

</script>


<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{
	return true;
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	return true;
}

//F6
function nuovo_F6()
{
	return true;
}

//F7-F8
function cambia_pag(value)
{
	return true;
}

//PAG GIU
function pag_prec()
{
	return true;
}

//PAG SU
function pag_suc()
{
	return true;
}

//F9
function ricerca_F9()
{
	return true;
}

//F10
function stampa_F10()
{
	return true;
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

//CAMBIO PAGINA
function pagina_menu (value)
{
	
	if (value == 'suc')
	{
		cambio_pagina = "<?php echo $next_page; ?>";
	}
	else if (value == 'prev')
	{
		cambio_pagina = "<?php echo $prev_page; ?>";
	}
	
	link = "stampa_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto="+cambio_pagina;
		
	top.location.href = link;
		
}

</script>

<script>
function elimina_file( value, file_1 , file_2 , index )
{
	$.ajax({  
		  type: "POST",  
		  async: false,
		  url: "ajax/ajax_stampe.php?c=<?php echo $c; ?>",  
		  data: {	
			  		ajax: "elimina_file",
			  		atto_id: value,
			  		tipo_stampa: "<?php echo $tipo_stampa; ?>",
			  		file: file_1,
			  		file_rar: file_2
				}, 
				
		  success: function(ritorno) {
				alert(ritorno);
			  	var array_ritorno = ritorno.split(' ');
			  	if(array_ritorno[0]=='OK')
				{		
					<?php 
						$Text = json_encode($array_atti);
						$RequestText = urlencode($Text);
					?>
					
					alert('File eliminato correttamente!');
					location.href = "atti_stampati.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_atto=<?php echo $tipo_atto; ?>&tipo_stampa=<?php echo $tipo_stampa; ?>&cluster=<?php echo $RequestText; ?>";
				}
				else if(array_ritorno[0]=='ERROR')
				{		
					alert("Errore nella cancellazione del file.");
					top.location.href = "ingiunzione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				}
				else if(array_ritorno[0]=='FLUSSO')
				{		
					alert("Impossibile eliminare la stampa definitiva poiche' e' gia stato elaborato il flusso "+array_ritorno[1]);
				}
		  		
		  }
	});
}

</script>
</head>
<body class="sfondo_new_gitco" >

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td class="width1"><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td class="width1"><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>

<?php include MENU . '/menu_generale.php'; ?>

<script>
blocca_modifica = 1;
</script>

<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td class="text_center width7">
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo"></a>
        </td>
		<td class="text_center width11">
          	
        </td>
		<td class="text_center width7">
          	<a href="#" id="stampa_click" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10grey.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
        </td>
		<td class="text_center width3">
          	
        </td>
		<td class="text_center width7" >
			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td class="text_center width2"></td>
		<td class="text_center width7">
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor"><?php echo $titolo_pag; ?></font></td>
	</tr>
</table>
		
<?php if(count($atto)!=0)
{?>

<table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">

<tr class="text_left riga_dispari" style="height:30px;" >
	
	<td class="width1"><br></td>
	<td class="text_left width15"><b>Atto</b></td>
	<td class="width1"><br></td>
	<td class="text_center width10"><b>Cronologico</b></td>
	<td class="width1"><br></td>
	<td class="text_center width15"><b>Data Stampa</b></td>
	<td class="width1"><br></td>
	<td class="text_center width10"><b>Totale (&euro;)</b></td>
	<td class="width1"><br></td>
	<td class="text_left width32"><b>Utente</b></td>
	<td class="width1"><br></td>
	<td class="width12"><br></td>
</tr>

<?php
$forma = new forma_giuridica();
$array_forma = $forma->array_completo();
for($i=0; $i<count($atto); $i++)
{		
	
	$file = array("","");
	$title_img_1 = "";
	$title_img_2 = "";
	
	$link = $atto[$i]->attoStampato($tipo_atto, $tipo_stampa);
		if($link==false)	continue;
		else if($link=="notFound")
		{
			$title_img_1 = "FILE MANCANTE!";
			$title_img_2 = "FILE MANCANTE!";
		}
		else
		{
			$file[0] = $link[0];
			$title_img_1 = mostra_file_path($file[0]);
			$src_img = "/Gitco2/immagini/pdfnew.png";
			if($tipo_stampa=="FLUSSO")
			{
				$file[1] = $link[1];
				$title_img_2 = mostra_file_path($file[1]);
				$src_img = "/Gitco2/immagini/txt.png";
			}
		}
	
	$partita = new partita($atto[$i]->Partita_ID, $c);
	$utente = $partita->Utente;
	$forma_descr = "";
	if($utente->Forma_Giuridica!='')
	{
		$index_value = $utente->Forma_Giuridica;
		$forma_descr = $array_forma[$index_value]['Sigla'];
	}
		
	$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
	if(strlen($nome_utente)>22)
		$nome_utente = substr($nome_utente, 0,22)."..";
	
	$y = $i;
	
	if ($y++ % 2)
		{$stile_riga = 'class="riga_dispari text_left pheight30"'	;	}
	else
		{$stile_riga = 'class="riga_pari text_left pheight30"'	;	}

?>	
		<tr <?php echo $stile_riga; ?>>
			
			<td class="width1"><br></td>
			<td class="text_left" colspan=9><font class="font12 titolo"><?php echo $atto[$i]->Info_Cartella; ?></font></td>
			<td class="width1"><br></td>
			<td class="text_center width12" id=td_1>
				<a href="#" style="text-decoration:none;">
					<img class=link_rar src="/Gitco2/immagini/rar.png" style="text-decoration:none; border:none" width="21" height="21" onclick="window.open('<?php echo mostra_file_path($file[1]); ?>')" title="<?=$title_img_2?>">
					<img src="<?php echo $src_img; ?>" style="text-decoration:none; border:none" width="21" height="21" onclick="window.open('<?php echo mostra_file_path($file[0]); ?>')" title="<?=$title_img_1?>">
				</a>				
			</td>
		</tr>
		<tr <?php echo $stile_riga; ?>>
			
			<td class="width1"><br></td>
			<td class="text_left width15"><?php echo substr($atto[$i]->Atto,0,17); if( strlen($atto[$i]->Atto) > 17 ) echo "."?></td>
			<td class="width1"><br></td>
			<td class="text_center width10"><?php echo $atto[$i]->ID_Cronologico." / ".$atto[$i]->Anno_Cronologico; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width15"><?php echo from_mysql_date($atto[$i]->Data_Stampa); ?></td>
			<td class="width1"><br></td>
			<td class="text_center width10"><?php echo conv_num(number_format($atto[$i]->Spese_Notifica + $atto[$i]->CAN + $atto[$i]->CAD + $atto[$i]->Interessi + $atto[$i]->Interessi_Precedenti + $atto[$i]->Importo,2)); ?></td>
			<td class="width1"><br></td>
			<td class="text_left width32"><?php echo $nome_utente; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width12" id=td_2>
				<input class="sfondo_red" type="button" name=elimina value="Elimina" onclick="elimina_file('<?php echo $atto[$i]->ID; ?>','<?php echo $file[0]; ?>','<?php echo $file[1]; ?>',<?php echo $i; ?>);">
			</td>
			
		</tr>
		
	
	<?php }?>
	</table>

<?php }?>






</td>
</tr>
</table>

<?php echo $layout; ?>

</body>
</html>