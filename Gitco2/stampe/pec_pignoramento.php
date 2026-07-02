<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/parametri.php";

include CLASSI . "/classe_email.php";

require EMAIL.'/PHPMailerAutoload.php';
require_once CLASSI. "\php-imap-client-master\Imap.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}


$a = get_var('a');
$c = get_var('c');
$p = get_var('p');
$tipo_controllo = get_var('tipo_controllo');
$tipo_pigno = get_var('pigno_val');
if($tipo_controllo==null)
	$array_PEC_inviate = get_var('array_pec');
else{
	$query = "SELECT DISTINCT NOTIF.atto_notificato_ID, EMAIL.ricevuta_accettazione, EMAIL.ricevuta_consegna, PG.CC FROM notifica_atto AS NOTIF ";
	$query.= "JOIN pignoramento_generale AS PG ON PG.ID = NOTIF.atto_notificato_ID AND PG.CC='".$c."' ";
	$query.= "JOIN pignoramento_presso_terzi AS PIGN ON PIGN.ID = NOTIF.ID_Collegamento ";
	
	if($tipo_pigno=="veicolo")
		$query.= "AND PIGN.Tipo='".$tipo_pigno."' ";
	else
		$query.= "AND PIGN.Tipo_Terzi='".$tipo_pigno."' ";
	
	$query.= "JOIN email_inviate AS EMAIL ON PG.partita_ID = EMAIL.partita_ID AND Table_Collegata = 'notifica_atto' AND EMAIL.ID_Collegato = NOTIF.ID AND PG.CC=EMAIL.CC ";
	$query.= "WHERE NOTIF.tipo_atto_notificato = 'pignoramento' AND NOTIF.tipo_notifica='terzi' AND EMAIL.ricevuta_accettazione = 'ATTESA' ORDER BY NOTIF.atto_notificato_ID";
	$array_PEC = mysql_array($query);
	$array_PEC_inviate = array();
	for($i=0;$i<count($array_PEC);$i++){
		$array_PEC_inviate[] = $array_PEC[$i]['atto_notificato_ID'];
	}
}


$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$layout = "<script>$('#progressbar').hide();</script>";

set_time_limit(100);

$pigno_pec = array();
$array_notifiche = array();

$stringa_reload = "";
for($i=0;$i<count($array_PEC_inviate);$i++)
{
	$stringa_reload.= "array_pec[]=".$array_PEC_inviate[$i]."&";
	$pigno_pec[$i] = new pignoramento($array_PEC_inviate[$i], $c);
}

$num_minuti = count($array_PEC_inviate);
$titolo_pag = "PEC Pignoramenti";
$num_notifica = 0;
for($i=0; $i<count($pigno_pec); $i++)
{
	for($y=0;$y<count($pigno_pec[$i]->Notifiche_Debitore);$y++)
	{
		$notifica_debitore = $pigno_pec[$i]->Notifiche_Debitore[$y];
		if($notifica_debitore->Modalita_Stampa!="pec")
			continue;
		else
		{
			$array_notifiche[$num_notifica]['notifica'] = $notifica_debitore->ID;
			$array_notifiche[$num_notifica]['pignoramento'] = $pigno_pec[$i]->ID;
			$array_notifiche[$num_notifica]['terzo'] = "";
			
			$num_notifica++;
		}
	}

	if($pigno_pec[$i]->Tipo =="veicolo")
	{
		for($y=0;$y<count($pigno_pec[$i]->Notifica_Istituto);$y++)
		{
			$notifica_istituto = $pigno_pec[$i]->Notifica_Istituto[$y];
			if($notifica_istituto->Modalita_Stampa!="pec")
				continue;
			else
			{
				$array_notifiche[$num_notifica]['notifica'] = $notifica_istituto->ID;
				$array_notifiche[$num_notifica]['pignoramento'] = $pigno_pec[$i]->ID;
				$array_notifiche[$num_notifica]['terzo'] = "";
				
				$num_notifica++;
			}
		}
	}
	else if($pigno_pec[$i]->Tipo == "terzi")
	{
		for($k=0;$k<count($pigno_pec[$i]->Presso_Terzi);$k++)
		{
			$terzo = $pigno_pec[$i]->Presso_Terzi;
			for($y=0;$y<count($terzo[$k]->Notifiche_Terzo);$y++)
			{
				$notifica_terzo = $terzo[$k]->Notifiche_Terzo[$y];
				if($notifica_terzo->Modalita_Stampa!="pec")
					continue;
				else
				{
					$array_notifiche[$num_notifica]['notifica'] = $notifica_terzo->ID;
					$array_notifiche[$num_notifica]['pignoramento'] = $pigno_pec[$i]->ID;
					$array_notifiche[$num_notifica]['terzo'] = $terzo[$k]->ID;
					
					$num_notifica++;
				}
			}
		}
	}
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>PEC pignoramenti</title>

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

var tipo_pigno = "<?php echo $tipo_pigno ?>";
var pigno_val = null;
if( tipo_pigno == "veicolo")
{
	pigno_val = "veicolo";
}

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
	
	ritorno = confirm("Controllo di avvenuta ricezione e consegna delle PEC inviate.\n\nConfermare l'operazione?");
	if(ritorno)
	{
		$('#form_pec').submit();
	}
	else
		return false;
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{

	link = "/gitco2/stampe/pec_pignoramento.php?pigno_val=<?php echo $tipo_pigno; ?>&<?php echo $stringa_reload; ?>";
	link+= "c=<?php echo $c; ?>";
	
	window.name = "pec";
	window.open(link, "pec");
	
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


$(document).ready(function(){
	
// 	$('#form_pec').ajaxForm(
						
// 	    function(value) {
// 	        var array_ritorno = value.split(' ');
// 		if(array_ritorno[0]=='fine')
// 		{		
// 			alert('Controllo PEC effettuato correttamente!');
// 			annulla();
// 		}
		
// 	});

$("#submit_click").click( salva_form );
	
	});

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
var blocca_menu = 1;
</script>

<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td class="text_center width7">
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undogrey.png" width=47 height=47 border=0>
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
			<a onMouseover="title='Home'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/homegrey.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor"><?php echo $titolo_pag; ?></font></td>
	</tr>
</table>
		
<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		<br>
<form id=form_pec name=form_pec action="pec_pignoramento_salva.php" method=post>
<input name=invia_submit  id=invia_submit	type=hidden	value="" >

<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
		
<?php if(count($array_notifiche)!=0)
{
	?>

<table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">

<tr class="text_left riga_dispari" style="height:30px;" >
	
	<td class="width1"><br></td>
	<td class="text_left width30"><b>Pignoramento</b></td>
	<td class="width1"><br></td>
	<td class="text_left width20"><b>Destinatario</b></td>
	<td class="width1"><br></td>
	<td class="width15 text_center"><b>Invio</b></td>
	<td class="width1"><br></td>
	<td class="width15 text_center"><b>Accettazione</b></td>
	<td class="width1"><br></td>
	<td class="width15 text_center"><b>Consegna</b></td>
</tr>

<?php
$forma = new forma_giuridica();
$array_forma = $forma->array_completo();

for($i=0; $i<count($array_notifiche); $i++)
{		
	set_time_limit(100);
	
	$pignoramento = new pignoramento($array_notifiche[$i]['pignoramento'], $c);
	$notifica_singola = new notifica_atto($array_notifiche[$i]['notifica'], $c);
	$email = $notifica_singola->Email_Object;
	
	$partita = new partita($pignoramento->Partita_ID, $c);
	$atto = new atto($pignoramento->Atto_ID, $c);
	$desc_atto = "PARTITA N.".$partita->Comune_ID."/".$partita->Anno_Riferimento;
	$desc_atto.= " - Riferito ad: ".$atto->Atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico;
	
	$forma_descr = "";
	$utente = $partita->Utente;
	if($utente->Forma_Giuridica!='')
	{
		$index_value = $utente->Forma_Giuridica;
		$forma_descr = $array_forma[$index_value]['Sigla'];
	}
		
	$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
	$denom_destinatario = "";
	if($notifica_singola->Tipo_Notifica == "veicolo")
	{
		$desc_pignoramento = "Beni mobili registrati";
		$notifiche = $pignoramento->Notifica_Istituto;
	
		$tribunale = new ufficio_giudiziario($utente->Residenza->CC_Indirizzo, "tribunale");
		$istituto_vendite = new ufficio_giudiziario($tribunale->CC_Ufficio, "istituto");
		
		$denom_destinatario = $istituto_vendite->Denominazione." ".$istituto_vendite->Sigla_Forma_Giuridica;
		$desc_pignoramento = "Pignoramento beni mobili registrati ".$pignoramento->ID_Cronologico."/".$pignoramento->Anno_Cronologico;
	}
	else if($notifica_singola->Tipo_Notifica=="terzi")
	{
		$terzo_singolo = new pignoramento_presso_terzi($array_notifiche[$i]['terzo'], $c);
		$dati_terzo = $terzo_singolo->Dati_Terzo;
		
		if($terzo_singolo->Tipo_Terzi!="banca")
			$denom_destinatario = "(".$dati_terzo->Comune_ID.") ".$dati_terzo->Cognome . $dati_terzo->Ditta ." ". $dati_terzo->Nome;
		else
			$denom_destinatario = $dati_terzo->Denominazione;
		
		$desc_pignoramento = "Pignoramento presso terzi ".$pignoramento->ID_Cronologico."/".$pignoramento->Anno_Cronologico;
	}
	else if($notifica_singola->Tipo_Notifica=="debitore")
	{
		$denom_destinatario = "(".$utente->Comune_ID.") ".$utente->Cognome . $utente->Ditta ." ". $utente->Nome;
	}
	
	if($pignoramento->Tipo == "terzi")
	{
		if($pignoramento->Tipo_Terzi == "lavoro")
			$tipo_pignoramento = "Pignoramento presso datore di lavoro";
		else if($pignoramento->Tipo_Terzi == "banca")
			$tipo_pignoramento = "Pignoramento presso banca";
		else if($pignoramento->Tipo_Terzi == "inps")
			$tipo_pignoramento = "Pignoramento presso inps";
		else
			$tipo_pignoramento = "Pignoramento presso altro";
	}
	else if($pignoramento->Tipo == "veicolo")
		$tipo_pignoramento = "Pignoramento beni mobili registrati";
				
	$desc_pignoramento = $tipo_pignoramento." ".$pignoramento->ID_Cronologico."/".$pignoramento->Anno_Cronologico;
		
	
	$y = $i;
	
	if ($y++ % 2)
		{$stile_riga = 'class="riga_dispari text_left pheight30"'	;	}
	else
		{$stile_riga = 'class="riga_pari text_left pheight30"'	;	}
		
		flush(); ob_flush();
	
	if($email!=null)
	{
		$invio = from_mysql_date($email->Data_Invio);
		
		switch($email->Ricevuta_Accettazione)
		{
			case "no": 			$accettazione = "Non prevista";											break;
			case "attesa": 		$accettazione = "<b><font class='color_yellow'>In attesa</font></b>";	break;
			case "fallita": 	$accettazione = "<b><font class='color_red'>Fallita</font></b>";		break;
			case "ok":			$accettazione = "<b><font class='color_green'>Ricevuta</font></b>";		break;
			case "mancata":		$accettazione = "<b><font class='color_red'>Mancata</font></b>";		break;
		}
		
		switch($email->Ricevuta_Consegna)
		{
			case "no": 			$consegna = "Non prevista";												break;
			case "attesa": 		$consegna = "<b><font class='color_yellow'>In attesa</font></b>";		break;
			case "fallita": 	$consegna = "<b><font class='color_red'>Fallita</font></b>";			break;
			case "ok":			$consegna = "<b><font class='color_green'>Ricevuta</font></b>";			break;
			case "mancata":		$consegna = "<b><font class='color_red'>Mancata</font></b>";			break;
			case "anomalia":	$consegna = "<b><font class='color_red'>Anomalia</font></b>";			break;
		}
	}
	else 
	{
		$invio = "<b>Fallito!</b>";
		$accettazione = "";
		$consegna = "";
	}
	
	
?>	
		
		<tr <?php echo $stile_riga; ?>>
			
			<td class="width1">
				<input type=hidden name="id_pignoramento[]" value="<?php echo $pignoramento->ID; ?>" >
				<input type=hidden name="id_notifica[]" value="<?php echo $notifica_singola->ID; ?>" >
				<input type=hidden name="id_terzo[]" value="<?php echo $array_notifiche[$i]['terzo']; ?>" >
			</td>
			<td class="text_left width30"><?php echo $desc_pignoramento; ?></td>
			<td class="width1"><br></td>
			<td class="text_left width20"><?php echo $denom_destinatario; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width15"><?php echo $invio; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width15"><?php echo $accettazione; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width15" ><?php echo $consegna; ?></td>
			
		</tr>
		<tr <?php echo $stile_riga; ?>>
			
			<td class="width1"><br></td>
			<td class="text_left" colspan=7><font class="font14 titolo"><?php echo $desc_atto; ?></font></td>
			<td class="width1"><br></td>
			<td class="text_center width15">
			</td>
		</tr>

<?php }?>

	</table>

<?php }?>

</form>

<?php echo $layout; ?>

<br>
</td>
</tr>
</table>

</body>
</html>