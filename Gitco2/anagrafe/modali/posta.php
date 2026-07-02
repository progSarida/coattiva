<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";

$p = get_var('p');
$c = get_var('c');
$a = get_var('a');
$mode = get_var('mode');

function RecursiveWrite($array) {
	foreach ($array as $vals) {
		echo $vals['comment_content'] . "\n";
		RecursiveWrite($vals['child']);
	}
}

$partite = new partite_utente($p, $c);
$atti = $partite->Atto;
$anni_rif = $partite->Anno_Riferimento;


$documento = new documento(null, $c);
$array_doc = $documento->Docs_ID($p);


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Corrispondenza - Gestione</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	  
<script>

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


function inserisci_manuale()
{
	if( "modifica" ==  "<?php echo $mode; ?>")
	{
		link="documento_manuale.php?c=<?php echo $c?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&mode=<?php echo $mode; ?>";
		window.name = "ricerca";
		window.open(link, "ricerca");
	}
}

function carica_doc(value)
{
	if( "modifica" ==  "<?php echo $mode; ?>")
	{
		link="documento_manuale.php?c=<?php echo $c?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&mode=<?php echo $mode; ?>&id_doc="+value;
		window.name = "ricerca";
		window.open(link, "ricerca");
	}
}

function apri_pdf(value)
{
	window.open(value);
	self.close();
}

</script>
  	  
<body class="sfondo_new_gitco" >

<table height=93% class="table_modale text_center pwidth1100" border=0>
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth1050" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Corrispondenza</font></td>
	</tr>
</table>
<br>

<table class="text_center pwidth1050" border="0">
	<tr>
		<td class="text_center width3"></td>
		<td class="text_left width18"><font class="color_titolo">Documento</font></td>
		<td class="text_left width9"><font class="color_titolo">Cronologico</font></td>
		<td class="text_left width8"><font class="color_titolo">Partita</font></td>
		<td class="text_left width6"><font class="color_titolo">Tipo</font></td>
		<td class="text_center width9"><font class="color_titolo">Data stampa</font></td>
		<td class="text_left width47"><font class="color_titolo">Informazioni</font></td>
	</tr>
	<tr>
		<td colspan=7><hr></td>
	</tr>
	
<?php 

for($i=count($atti)-1;$i>=0;$i--)
{
	for($k=count($atti[$i])-1;$k>=0;$k--)
	{
		
		$pdf = $atti[$i][$k]->attoStampato($atti[$i][$k]->Atto, "DEFINITIVA");
		if($pdf==false)	continue;
		else if($pdf=="notFound")
		{
			$title_img_1 = "FILE MANCANTE!";
			$src_img = "";
		}
		else
		{
			$file[0] = $pdf[0];
			$title_img_1 = mostra_file_path($file[0]);
			$src_img = "/Gitco2/immagini/pdfnew.png";
		}
		
		if($atti[$i][$k]->Stato_Stampa == "Stampato")
		{
			?>
		<tr>
<?php if($src_img!=""){ ?>
			<td class="text_center">
				<a href="#" style="text-decoration:none;">
					<img src="<?php echo $src_img; ?>" style="text-decoration:none; border:none" width="15" height="15" onclick="apri_pdf('<?php echo mostra_file_path($file[0]); ?>')" title="<?=$title_img_1?>">
				</a>				
			</td>
<?php }else{ ?>
			<td class="text_center"></td>
<?php }?>
			
			<td class="text_left"><?php echo substr($atti[$i][$k]->Atto,0,21); ?></td>
			<td class="text_left"><?php echo $atti[$i][$k]->ID_Cronologico."/".$atti[$i][$k]->Anno_Cronologico; ?></td>
			<td class="text_left"><?php echo $partite->Comune_ID[$i]."/".$anni_rif[$i]; ?></td>
			<td class="text_left">Inviato</td>
			<td class="text_center"><?php echo from_mysql_date($atti[$i][$k]->Data_Stampa); ?></td>
			<td class="text_left"><?php echo substr($atti[$i][$k]->Info_Cartella,0,52); ?>...</td>
		</tr>
			
<?php 
		}
	}
}

for($i=0;$i<count($array_doc);$i++)
{?>
		<tr>
			<td class="text_center">
				<a href="#" style="text-decoration:none;">
					<img src="/gitco2/immagini/select.png" style="text-decoration:none; border:none" width="15" height="15" onclick="carica_doc('<?php echo $array_doc[$i]['ID']; ?>'); " title='Modifica documento'>
				</a>				
			</td>
			<td class="text_left"><?php echo $array_doc[$i]['Atto']; ?></td>
			<td class="text_left"><?php echo $array_doc[$i]['Comune_ID']; ?></td>
			<td class="text_left">Assente</td>
			<td class="text_left"><?php echo $array_doc[$i]['Tipo']; ?></td>
			<td class="text_center"><?php echo from_mysql_date($array_doc[$i]['Data_Stampa']); ?></td>
			<td class="text_left"><?php echo $array_doc[$i]['Oggetto']; ?></td>
		</tr>
<?php 
}
?>
	
</table>
<br>

<table class="text_center pwidth750" border="0">
	<tr>
		<td>
			<input type=button name=inserisci value=Inserisci class=button_azzurro onclick="inserisci_manuale();">
			<input type=button name=chiudi value=Chiudi class=button_azzurro onclick="self.close();">
		</td>
	</tr>
</table>

		</td>
	</tr>
</table>

</body>
</html>