<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

// header('Content-Type: application/octet-stream');
// header('Content-Disposition: attachment;filename=\"filename.eml\"');
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/anagrafe.php";
include CLASSI. "/classe_email.php";

$c = get_var('c');
$partita_ID = get_var('partita');
$utente_ID = get_var('utente');

$email = new email_inviate(null);
if($partita_ID!=null && $utente_ID==null)
	$lista_mail = $email->selezionaMailPartita($c, $partita_ID);
else if($utente_ID!=null && $partita_ID==null)
	$lista_mail = $email->selezionaMailUtente($c, $utente_ID);

alert("Per leggere le mail inviate e le ricevute e' necessario scaricare i file sul proprio computer ed utilizzare un Client di posta per aprirli (Outlook, Mozilla Thunderbird...).");

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Email - Gestione</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen></link>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen></link>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	  
<script>

$(function() {

	$( ".picker" ).datepicker();

});

function progress_attesa(verifica,partita,tipo_mail,oggetto)
{
	if(verifica=="forse")
	{
		ritorno_mail = confirm("Si sta procedendo con la verifica delle ricevute.\n\nConfermare l'operazione?");

		if(!ritorno_mail)
			return false;
	}
	else if(verifica=="no")
	{
		alert("Non sono previste ricevute di conferma. Non e' possibile effettuare la verifica.");
		return false;
	}
	else if(verifica=="OK")
	{
		alert("La verifica e' gia' stata effettuata correttamente!");
		return false;
	}
	
	alert("La procedura potrebbe durare diversi minuti se la casella di posta elettronica risultasse molto piena.\n\nAl termine della stessa verra' mostrato un messaggio con l'esito.\n\nConfermare ed attendere...");
	
	$('#progress_bar').progressbar({value: false});
	$( "#barlabel" ).text("Verifica ricezione email. ATTENDERE PREGO...");
	
	setTimeout(function(){
		lista_mail(partita,tipo_mail,oggetto);
		}, 1000);
		
}

function lista_mail(partita,tipo_mail,oggetto)
{

	$.ajax({
			type: "POST",  
			async: false,
			url: "verifica_mail.php?c=<?php echo $c; ?>" ,
   	   	 
			data: {	
				'partita': 	 	partita,
				'tipo_mail': 	tipo_mail,
				'oggetto': 	 	oggetto
			},				
    	   	
			success: function (value) {

	   			var array_ritorno = value.split(' ');

	   			if(array_ritorno == "OK")
	   			{
		   			alert('Verifica effettuata correttamente!');
		   			reload();
	   			}
	   			else if(array_ritorno == "NO")
	   			{
	   				alert("Messaggio non trovato!");
	   				reload();
	   			}
	   			else
	   			{
	   				alert('Errore nel tentativo di verifica! '+value);
	   				reload();
	   			}
   			
   			
		}
	});
}

function reload()
{
	link="info_email.php?c=<?php echo $c?>&partita=<?php echo $partita_ID; ?>&utente=<?php echo $utente_ID; ?>";
	window.name = "ricerca";
	window.open(link, "ricerca");
}

function apri_file(value, value2)
{
	link="force-download.php?file="+value+"&filename="+value2;
	
	window.open(link);
}
		
</script>

<body class="sfondo_new_gitco" >
  
<table class="table_modale text_center pwidth800 height93" >
	<tr>
		<td valign=top>  
  
  <br>
  
<table class="text_center pwidth750" >
	<tr>
		<td><font class="titolo font18">Gestione email</font></td>
	</tr>
</table>

<br>

<table class="text_center pwidth750">
	<tr>
		<td class="text_left" colspan=7><hr></td>
	</tr>
	<tr>
		<td class="text_left width7"><font class="color_titolo">Da</font></td>
		<td class="text_left width7"><font class="color_titolo">A</font></td>
		<td class="text_center width12"><font class="color_titolo">Data invio</font></td>
		<td class="text_left width35"><font class="color_titolo">Oggetto</font></td>
		<td class="text_center width9"><font class="color_titolo">Verifica</font></td>
		<td class="text_center width15"><font class="color_titolo">Accettazione</font></td>
		<td class="text_center width15"><font class="color_titolo">Consegna</font></td>
	</tr>
	<tr>
		<td class="text_left" colspan=7><hr></td>
	</tr>

<?php 

for($i=0;$i<count($lista_mail);$i++)
{
	$mail_singola = new email_inviate($lista_mail[$i]['ID']);
	
	$path = $mail_singola->percorsi_PEC();
	
	$link_accettazione = "<a href=\"#\" onclick=\"apri_file('".$path['accettazione']['path']."','".$path['accettazione']['filename']."')\" title=\"".$path['accettazione']['filename']."\"><span class='color_green'>Ricevuta</span></a>";
	$link_mancata_accettazione = "<a href=\"#\" onclick=\"apri_file('".$path['mancata_accettazione']['path']."','".$path['mancata_accettazione']['filename']."')\" title=\"".$path['mancata_accettazione']['filename']."\">MANCATA</a>";
	$link_consegna = "<a href=\"#\" onclick=\"apri_file('".$path['consegna']['path']."','".$path['consegna']['filename']."')\" title=\"".$path['consegna']['filename']."\"><span class='color_green'>Ricevuta</span></a>";
	$link_mancata_consegna = "<a href=\"#\" onclick=\"apri_file('".$path['mancata_consegna']['path']."','".$path['mancata_consegna']['filename']."')\" title=\"".$path['mancata_consegna']['filename']."\"><span class='color_red'>MANCATA</span></a>";
	$link_anomalia = "<a href=\"#\" onclick=\"apri_file('".$path['anomalia']['path']."','".$path['anomalia']['filename']."')\" title=\"".$path['anomalia']['filename']."\"><span class='color_red'>ANOMALIA</span></a>";
		
	$control_accettazione = $lista_mail[$i]['Ricevuta_Accettazione'];
	switch($control_accettazione)
	{
		case "no": 			$accettazione = "Non prevista";											break;
		case "attesa": 		$accettazione = "<a href='#' ><span class='color_yellow'>IN ATTESA</span></a>";	break;
		case "fallita": 	$accettazione = "<a href='#' ><span class='color_red'>FALLITA</span></a>";		break;
		case "ok":			
			
			$accettazione = $link_accettazione;			
		
			break;
		case "mancata":		
			
			$consegna = $link_mancata_accettazione;			
		
			break;
	}
	
	$control_consegna = $lista_mail[$i]['Ricevuta_Consegna'];
	switch($control_consegna)
	{
		case "no": 			$consegna = "Non prevista";												break;
		case "attesa": 		$consegna = "<a href='#' ><span class='color_yellow'>IN ATTESA</span></a>";		break;
		case "fallita": 	$consegna = "<a href='#' ><span class='color_red'>FALLITA</span></a>";			break;
		case "ok":			
			
			$consegna = $link_consegna;			
		
			break;
		case "mancata":		
			
			$consegna = $link_mancata_consegna;			
		
			break;
		case "anomalia":	
			
			$consegna = $link_anomalia;			
		
			break;
	}
	
	$verifica = "forse";
	
	if($control_accettazione=="ok" && $control_consegna=="ok")//RICEVUTA
		$verifica = "OK";
	if($control_accettazione=="no" && $control_consegna=="no")//NON PREVISTA
		$verifica = "no";
	if($control_accettazione=="ok" && $control_consegna=="no")//RICEVUTA
		$verifica = "OK";
	
	$oggetto = substr($lista_mail[$i]['Oggetto'],0,30)."...";
			
?>
	<tr>
		<td class="text_left"><span class="font_bold" title="<?php echo $lista_mail[$i]['Mail_Sorgente']; ?>"><?php echo $lista_mail[$i]['Tipo_Sorgente']; ?></span></td>
		<td class="text_left"><span class="font_bold" title="<?php echo $lista_mail[$i]['Mail_Destinatario']; ?>"><?php echo $lista_mail[$i]['Tipo_Destinatario']; ?></span></td>
		<td class="text_center"><?php echo from_mysql_date($lista_mail[$i]['Data_Invio']); ?></td>
		<td class="text_left"><a href="#" onclick="apri_file('<?php echo $path['inviato']['path']; ?>','<?php echo $path['inviato']['filename']; ?>');" title="<?php echo $path['inviato']['filename']; ?>"><?php echo $oggetto; ?></a></td>
		
		<td class="text_center">
			<a onMouseover="title='Verifica ricevute di accettazione e consegna'" href="#" style="text-decoration:none;" onClick="progress_attesa('<?php echo $verifica; ?>','<?php echo $partita_ID; ?>','<?php echo $lista_mail[$i]['Tipo_Destinatario']; ?>','<?php echo $lista_mail[$i]['Oggetto']; ?>');" >
				<img src="/gitco2/immagini/email_mini.png" width=20 height=15 border=0 >
			</a>
		</td>
		<td class="text_center"><?php echo $accettazione; ?></td>
		<td class="text_center"><?php echo $consegna; ?></td>
	</tr>
<?php 
}
?>
	
</table>
<br>
		<div class="table_interna text_center" id="progress_bar" style="height:55px;">
			<div class="text_center" id="barlabel"></div>
		</div>



		</td>
	</tr>
</table>

</body>
</html>