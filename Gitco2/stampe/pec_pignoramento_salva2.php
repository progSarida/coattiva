<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include CLASSI. "/parametri.php";
include CLASSI. "/ruolo.php";
include CLASSI. "/anagrafe.php";
include CLASSI. "/coazione.php";
include CLASSI. "/comuni.php";
include CLASSI. "/classe_email.php";
require_once CLASSI. "/php-imap-client-master/Imap.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$c = get_var('c');
$a = get_var('a');
$id_pignoramento = get_var('id_pignoramento');
$id_notifica = get_var('id_notifica');
$id_terzo = get_var('id_terzo');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Stampa Avviso</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

<script>
function inizio()
{
	$('#progressbar').progressbar({
		value: false
	});
	$( "#barlabel" ).text("Inizio controllo...");
}

function anomalie()
{
	$('#progressbar').progressbar({
		value: false
	});
	$( "#barlabel" ).text("Ricerca anomalie...");
}

function update(valore, mail)
{
	$( "#progressbar" ).progressbar({value: parseInt(valore) });
	$( "#barlabel" ).text( valore + "% ("+mail+")" );
}

function nessun_risultato()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Nessun risultato trovato");
}

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);
}

function gestione_email()
{
	$('#pec_form').submit();
}

</script>

</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
<tr>
<td width=1%><br></td>
<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
	<tr>
		<td valign=top>
		<br><br><br>
		<font class="titolo font18 text_center">Controllo ricezione PEC</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		<br>		
		</td>
	</tr>
</table>

<form name='pec_form' id='pec_form' method='post' action='pec_pignoramento.php'>
<input type=hidden name=pigno_val value='veicolo'>
<input type=hidden name='c' value="<?php echo $c; ?>">
<input type=hidden name='a' value="<?php echo $a; ?>">			
		

<?php
set_time_limit(100);

flush();	ob_flush();		flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();

sleep(2);

$par = new parametri_email($c, null, 'PEC');
$imap = null;
$imap = new Imap($par);
$imap->selectFolder('INBOX');

// stop on error
//if($imap->isConnected()===false)
//	continue;

flush();	ob_flush();flush();	ob_flush();

echo "<script>anomalie();</script>";

flush();	ob_flush();		flush();	ob_flush();

$ANOMALIA = imap_search($imap->imap, 'SUBJECT "ANOMALIA MESSAGGIO:"', SE_UID);
$id_pignoramento_prec = 0;
for($k=0;$k<count($id_pignoramento);$k++)
{
	set_time_limit(500);

	flush();
	ob_flush();
	flush();
	ob_flush();
	
	echo "<script>update(".ceil($k*100/count($id_pignoramento)).",'".($k+1)."/".count($id_pignoramento)."');</script>";
	
	flush();
	ob_flush();
	flush();
	ob_flush();
	
	if($id_pignoramento[$k]!=$id_pignoramento_prec)
	{
	?>
		<input type=hidden name=array_pec[] value="<?php echo $id_pignoramento[$k]; ?>">
	<?php
	}
	
	$id_pignoramento_prec = $id_pignoramento[$k];
	
	$pignoramento = new pignoramento($id_pignoramento[$k], $c);
	$notifica_singola = new notifica_atto($id_notifica[$k], $c);
	$email = $notifica_singola->Email_Object;
	
	$partita = new partita($pignoramento->Partita_ID,$c);
	
	$control_accettazione = 0;
	$control_consegna = 0;
	
	$accettazione = $email->Ricevuta_Accettazione;
	$consegna = $email->Ricevuta_Consegna;
	
	if($accettazione!="attesa" && $accettazione!="fallita")
		$control_accettazione=1;
	
	if($consegna!="attesa" && $consegna!="fallita" && $consegna!="anomalia")
		$control_consegna=1;
	
	if($control_accettazione==1 && $control_consegna==1)
		continue;
	
	$cartella_PEC = $email->cartella_PEC();
	
	$path_mail = $email->percorsoMail($c, 'PEC', $cartella_PEC, "server");
	
	$par = new parametri_email($c, $partita->Tipo, 'PEC');
	$imap = null;
	$imap = new Imap($par);
	$imap->selectFolder('INBOX');

// stop on error
//if($imap->isConnected()!==false)
//	continue;

//$SUBJECT = imap_search($imap->imap, 'SUBJECT "Pignoramento_presso_banca_A658_2016_127_2016-10-14_terzo_7_NOT7447"', SE_UID);
$SUBJECT = imap_search($imap->imap, 'SUBJECT "'.$email->Oggetto.'"', SE_UID);
//	$SUBJECT = imap_search($imap->imap, 'FROM "posta-certificata@pec.actalis.it" ON "'.date("j F Y").'"', SE_UID);
for($i=0;$i<count($SUBJECT);$i++)
{
	if($SUBJECT[$i]==0)
		continue;
//    $msgno = $SUBJECT[$i];
//    $uid = imap_uid($imap->imap, $SUBJECT[$i]);

	$msgno = imap_msgno($imap->imap, $SUBJECT[$i]);
    $uid = $SUBJECT[$i];




for($y=0;$y<count($ANOMALIA);$y++)
{
	if($consegna== "ok")
		break;
	
	if($ANOMALIA[$y]=="")
		continue;		
	
	$msgno_anomalia = imap_msgno($imap->imap, $ANOMALIA[$y]);

	$header_anomalia = imap_headerinfo($imap->imap, $msgno_anomalia);
	$body_anomalia = imap_body($imap->imap, $ANOMALIA[$y], FT_UID);
	$structure_anomalia = imap_fetchstructure($imap->imap, $ANOMALIA[$y], FT_UID);

	if(strpos($body_anomalia, $email->Oggetto) !== false)
	{
		$nome_file_anomalia = $path_mail."/ANOMALIA_".$email->Oggetto.".eml";
	
		$myfile_anomalia = fopen($nome_file_anomalia, 'w');
		$testo_anomalia = imap_fetchbody($imap->imap, $msgno_anomalia,'');
		fwrite($myfile_anomalia, $testo_anomalia);
		fclose($myfile_anomalia);
			
		$consegna = "anomalia";
		$email->Ricevuta_Consegna = $consegna;
		
		if(strtolower($par->Protocollo_Arrivo) == "imap")
			if(file_exists($nome_file_anomalia));
				imap_delete($imap->imap, $ANOMALIA[$y], FT_UID);//CANCELLA DEFINITIVO
		
		break;
	}
}

if($accettazione!= "ok" && $accettazione!= "attesa" && $accettazione!= "mancata" )
	$email->Ricevuta_Accettazione = "fallita";

if($consegna!= "ok"  && $consegna!= "attesa" && $consegna!= "mancata" && $consegna!= "anomalia" && $email->Tipo_Destinatario=="PEC")
	$email->Ricevuta_Consegna = "fallita";

$control_salva = $email->Update($email->ID);

imap_expunge($imap->imap);

flush();ob_flush();
flush();ob_flush();

if($control_salva===false)
	continue;

}
//die;
?>
	</form>
			
	<script>fine('Controllo PEC effettuato!');</script>
	<script>gestione_email('');</script>
			
<?php 

?>