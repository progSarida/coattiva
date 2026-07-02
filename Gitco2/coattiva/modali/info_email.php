<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_CoazioneUtils.php";
include_once CLS . "/cls_DateTimeInLine.php";

$cls_help = new cls_help();
$cls_db = new cls_db();
$coaz = new cls_Coazione();
$date = new cls_DateTimeI("IT",false);

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";*/

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

function selezionaMailPartita($c,$Partita_ID, $ordinamento='DESC')
{
    $cls_db = new cls_db();

    $stringa = "SELECT * FROM email_inviate WHERE CC='".$c."' AND Partita_ID ='".$Partita_ID."' ";
    $stringa.= "ORDER BY ID ".$ordinamento;

    return $cls_db->getResults($cls_db->ExecuteQuery($stringa));
}



// header('Content-Type: application/octet-stream');
// header('Content-Disposition: attachment;filename=\"filename.eml\"');
/*include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/anagrafe.php";
include CLASSI. "/classe_email.php";*/

$c = $cls_help->getVar('c');
$partita_ID = $cls_help->getVar('partita');
$utente_ID = $cls_help->getVar('utente');

//$email = new email_inviate(null);
if($partita_ID!=null && $utente_ID==null)
	$lista_mail = selezionaMailPartita($c, $partita_ID);
else if($utente_ID!=null && $partita_ID==null)
	$lista_mail = selezionaMailUtente($c, $utente_ID);

$cls_help->alert("Per leggere le mail inviate e le ricevute e' necessario scaricare i file sul proprio computer ed utilizzare un Client di posta per aprirli (Outlook, Mozilla Thunderbird...).");

?>
  	  
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

    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <span class="titolo font18">Gestione email</span>
        </div>
    </div>



<!--<table class="text_center pwidth750">
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
	</tr>-->

<?php
$table = array();
for($i=0;$i<count($lista_mail);$i++) {
    $query = "SELECT * FROM email_inviate WHERE ID = '" . $lista_mail[$i]['ID'] . "'";
    $mail_singola = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query), "email_inviate");
    //$mail_singola = new email_inviate($lista_mail[$i]['ID']);

    $path = $coaz->percorsi_PEC($mail_singola);

    $link_accettazione = "<a href=\"#\" onclick=\"apri_file('" . $path['accettazione']['path'] . "','" . $path['accettazione']['filename'] . "')\" title=\"" . $path['accettazione']['filename'] . "\"><span class='color_green'>Ricevuta</span></a>";
    $link_mancata_accettazione = "<a href=\"#\" onclick=\"apri_file('" . $path['mancata_accettazione']['path'] . "','" . $path['mancata_accettazione']['filename'] . "')\" title=\"" . $path['mancata_accettazione']['filename'] . "\">MANCATA</a>";
    $link_consegna = "<a href=\"#\" onclick=\"apri_file('" . $path['consegna']['path'] . "','" . $path['consegna']['filename'] . "')\" title=\"" . $path['consegna']['filename'] . "\"><span class='color_green'>Ricevuta</span></a>";
    $link_mancata_consegna = "<a href=\"#\" onclick=\"apri_file('" . $path['mancata_consegna']['path'] . "','" . $path['mancata_consegna']['filename'] . "')\" title=\"" . $path['mancata_consegna']['filename'] . "\"><span class='color_red'>MANCATA</span></a>";
    $link_anomalia = "<a href=\"#\" onclick=\"apri_file('" . $path['anomalia']['path'] . "','" . $path['anomalia']['filename'] . "')\" title=\"" . $path['anomalia']['filename'] . "\"><span class='color_red'>ANOMALIA</span></a>";

    $control_accettazione = $lista_mail[$i]['Ricevuta_Accettazione'];
    switch ($control_accettazione) {
        case "no":
            $accettazione = "Non prevista";
            break;
        case "attesa":
            $accettazione = "<a href='#' ><span class='color_yellow'>IN ATTESA</span></a>";
            break;
        case "fallita":
            $accettazione = "<a href='#' ><span class='color_red'>FALLITA</span></a>";
            break;
        case "ok":

            $accettazione = $link_accettazione;

            break;
        case "mancata":

            $consegna = $link_mancata_accettazione;

            break;
    }

    $control_consegna = $lista_mail[$i]['Ricevuta_Consegna'];
    switch ($control_consegna) {
        case "no":
            $consegna = "Non prevista";
            break;
        case "attesa":
            $consegna = "<a href='#' ><span class='color_yellow'>IN ATTESA</span></a>";
            break;
        case "fallita":
            $consegna = "<a href='#' ><span class='color_red'>FALLITA</span></a>";
            break;
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

    if ($control_accettazione == "ok" && $control_consegna == "ok")//RICEVUTA
        $verifica = "OK";
    if ($control_accettazione == "no" && $control_consegna == "no")//NON PREVISTA
        $verifica = "no";
    if ($control_accettazione == "ok" && $control_consegna == "no")//RICEVUTA
        $verifica = "OK";

    $oggetto = substr($lista_mail[$i]['Oggetto'], 0, 30) . "...";

    $table[$i]["Da"] = '<span class="font_bold" title="' . $lista_mail[$i]['Mail_Sorgente'] . '">' . $lista_mail[$i]['Tipo_Sorgente'] . '</span>';
    $table[$i]["A"] = '<span class="font_bold" title="' . $lista_mail[$i]['Mail_Destinatario'] . '">' . $lista_mail[$i]['Tipo_Destinatario'] . '</span>';
    $table[$i]["Data invio"] = $date->Get_DateNewFormat($lista_mail[$i]['Data_Invio'], "DB");
    $table[$i]["Oggetto"] = '<a href="#" onclick="apri_file(\'' . $path['inviato']['path'] . '\',\'' . $path['inviato']['filename'] . '\');" title="' . $path['inviato']['filename'] . '">' . $oggetto . '</a>';
    $table[$i]["Verifica"] = '<a onMouseover="title=\'Verifica ricevute di accettazione e consegna\'" href="#" style="text-decoration:none;" onClick="progress_attesa(\'' . $verifica . '\',\'' . $partita_ID . '\',\'' . $lista_mail[$i]['Tipo_Destinatario'] . '\',\'' . $lista_mail[$i]['Oggetto'] . '\');" ><img src="' . IMMAGINIWEB . '/email_mini.png" width=20 height=15 border=0 ></a>';
    $table[$i]["Accettazione"] = $accettazione;
    $table[$i]["Consegna"] = $consegna;
}
?>
	<!--<tr>
		<td class="text_left"><span class="font_bold" title="<?php// echo $lista_mail[$i]['Mail_Sorgente']; ?>"><?php// echo $lista_mail[$i]['Tipo_Sorgente']; ?></span></td>
		<td class="text_left"><span class="font_bold" title="<?php// echo $lista_mail[$i]['Mail_Destinatario']; ?>"><?php//echo $lista_mail[$i]['Tipo_Destinatario']; ?></span></td>
		<td class="text_center"><?php// echo from_mysql_date($lista_mail[$i]['Data_Invio']); ?></td>
		<td class="text_left"><a href="#" onclick="apri_file('<?php// echo $path['inviato']['path']; ?>','<?php// echo $path['inviato']['filename']; ?>');" title="<?php// echo $path['inviato']['filename']; ?>"><?php //echo $oggetto; ?></a></td>
		
		<td class="text_center">
			<a onMouseover="title='Verifica ricevute di accettazione e consegna'" href="#" style="text-decoration:none;" onClick="progress_attesa('<?php// echo $verifica; ?>','<?php// echo $partita_ID; ?>','<?php// echo $lista_mail[$i]['Tipo_Destinatario']; ?>','<?php// echo $lista_mail[$i]['Oggetto']; ?>');" >
				<img src="/gitco2/immagini/email_mini.png" width=20 height=15 border=0 >
			</a>
		</td>
		<td class="text_center"><?php// echo $accettazione; ?></td>
		<td class="text_center"><?php// echo $consegna; ?></td>
	</tr>-->
<?php 
//}
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="table_interna text_center" id="progress_bar" style="height:55px;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </div>
    </div>


    <div id="appendTable"></div>

    <script type="text/javascript">

        $(document).ready(function(){
            //var toprint = [{originalName: "CC", replacedName: "CC"},{originalName: "Date", replacedName: "Data"},{originalName: "ListTypeName", replacedName: "Tipo"},{originalName: "Name", replacedName: "Nome file"},{originalName: "Description", replacedName: "Descrizione"},{originalName: "File", replacedName: "File"}];
            //var widthCell = ["8%","10%","15%","25%","37%","5%"];
            var fontsize = "10px";
            var test = new TableGenerator(<?= json_encode($table)?>,undefined,undefined,fontsize);


        });


    </script>

<?php include_once INC ."/footer.php"; ?>