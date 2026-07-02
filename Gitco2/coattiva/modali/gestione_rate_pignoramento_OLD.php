<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include INC."/headerAjax.php";
include CLS."/cls_Utils.php";
include_once CLS."/cls_CoazioneUtils.php";
include_once CLS."/cls_math.php";

/*include LIBRERIE . "/funzioni.php";

include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/anagrafe.php";
include CLASSI . "/parametri.php";*/

$cls_utils = new cls_Utils();
$cls_coaz = new cls_Coazione();
$cls_math = new cls_math();
$cls_db = new cls_db();

$p = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$layout = "";

$partita_ID = $cls_help->getVar('partita');
$pignoramento_ID = $cls_help->getVar('pignoramento');

$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_ID." AND CC = '".$c."'";
$pignoramento = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");

$query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$pignoramento_ID." AND CC = '".$c."'";
$pignoramento["Spese_Pignoramento"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pignoramento_spese");

$query = "SELECT ID FROM pignoramento_presso_terzi WHERE Pignoramento_ID = '".$pignoramento_ID."' AND CC = '".$c."' ORDER BY ID ASC";
$terzi_id = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"pignoramento_presso_terzi");

for( $i=0; $i<count($terzi_id); $i++ )
{
    $query = "SELECT * FROM pignoramento_presso_terzi WHERE ID = '".$terzi_id[$i]['ID']."' AND CC = '".$c."'";
    $pignoramento["Presso_Terzi"][$i] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pignoramento_presso_terzi");// new pignoramento_presso_terzi( $terzi_id[$i]['ID'] , $c );
}
    
    
    
//$pignoramento = new pignoramento($pignoramento_ID, $c);
$totali = $cls_coaz->gestione_totali($pignoramento);
$pignoramento = (object) $pignoramento;
//$totali = $pignoramento->Totali_Array;

$num_rate = $pignoramento->Rate_Previste;
$importo = $pignoramento->Importi_Rate;
$scadenza = $pignoramento->Scadenze_Rate;

$nome_gestore = $pignoramento->Nominativo_Gestore_Rateizzazione;
$posizione_gestore = $pignoramento->Posizione_Gestore_Rateizzazione;
$esito = $pignoramento->Esito_Richiesta_Rateizzazione;
$operatore = $pignoramento->Operatore_Rateizzazione;

$path = $cls_utils->crea_dir( $_SERVER['DOCUMENT_ROOT']."/archivio/atti/". $c . "/Documenti" );
$id_richiesta = $pignoramento->ID_Richiesta_Rateizzazione;
if($id_richiesta!=0)
{
	//$richiesta_doc = new documento($id_richiesta, $c);
    $query = "SELECT * FROM documento WHERE ID = '".$id_richiesta."' AND CC = '".$c."'";
    $richiesta_doc = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"documento");
	$file_richiesta = $cls_utils->mostra_file_path($path."/".$richiesta_doc->File);
}
else
	$file_richiesta = "";

$id_esito = $pignoramento->ID_Esito_Rateizzazione;
if($id_esito!=0)
{
    $query = "SELECT * FROM documento WHERE ID = '".$id_esito."' AND CC = '".$c."'";
    $esito_doc = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"documento");
    $file_esito = $cls_utils->mostra_file_path($path."/".$esito_doc->File);
	/*$esito_doc = new documento($id_esito, $c);
	$file_esito = mostra_file_path($path."/".$esito_doc->File);*/
}
else
	$file_esito = "";

$id_bollettini = $pignoramento->ID_Bollettini_Rateizzazione;
if($id_bollettini!=0)
{
    $query = "SELECT * FROM documento WHERE ID = '".$id_bollettini."' AND CC = '".$c."'";
    $bollettini_doc = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"documento");
    $file_bollettini = $cls_utils->mostra_file_path($path."/".$bollettini_doc->File);
	/*$bollettini_doc = new documento($id_bollettini, $c);
	$file_bollettini = mostra_file_path($path."/".$bollettini_doc->File);*/
}
else 
	$file_bollettini = "";

if($esito == "respinta")
{
	$checked_A = "";
	$checked_R = "checked";
	$motivazione = $pignoramento->Motivazione_Respinta_Rateizzazione;
}
else
{
	$checked_A = "checked";
	$checked_R = "";
	$motivazione = "";
	$layout.= "<script>$('#richiesta_respinta').prop('disabled',true).val('').addClass('sfondo_grigio');</script>";
}

$tot_rate = 0.00;
for($i=0;$i<count($importo);$i++)
{
	$tot_rate += $cls_math->conv_num($importo[$i]);
}

$tot_rate = number_format($tot_rate,2);
$tot_rate = $cls_math->conv_num($tot_rate);
$importo_bloccato = $cls_coaz->importiRiscontri((array) $pignoramento,"terzi");

$checkTot1 = number_format($cls_math->conv_num($totali["Totali_Array"][1])-$importo_bloccato,2,".","");
$checkTot2 = number_format($cls_math->conv_num($totali["Totali_Array"][2])-$importo_bloccato,2,".","");
$checkTot3 = number_format($cls_math->conv_num($totali["Totali_Array"][3])-$importo_bloccato,2,".","");
$checkTotRate = number_format($cls_math->conv_num($tot_rate),2,".","");

if( $tot_rate == $totali["Totali_Array"][1] )
	$messaggio_1 = "<b>L'importo totale delle rate coincide con il totale 1.</b>";
else if($tot_rate == $totali["Totali_Array"][2])
	$messaggio_1 = "<b>L'importo totale delle rate coincide con il totale 2.</b>";
else if($tot_rate == $totali["Totali_Array"][3])
	$messaggio_1 = "<b>L'importo totale delle rate coincide con il totale 3.</b>";
else if( $checkTotRate == $checkTot1 )
	$messaggio_1 = "<b>L'importo totale delle rate coincide con il totale 1 di ".$totali["Totali_Array"][1]." Euro meno l'importo bloccato nei riscontri di ".number_format($importo_bloccato,2,",","")." Euro.</b>";
else if( $checkTotRate == $checkTot2 )
	$messaggio_1 = "<b>L'importo totale delle rate coincide con il totale 1 di ".$totali["Totali_Array"][2]." Euro meno l'importo bloccato nei riscontri di ".number_format($importo_bloccato,2,",","")." Euro.</b>";
else if( $checkTotRate == $checkTot3 )
	$messaggio_1 = "<b>L'importo totale delle rate coincide con il totale 1 di ".$totali["Totali_Array"][3]." Euro meno l'importo bloccato nei riscontri di ".number_format($importo_bloccato,2,",","")." Euro.</b>";
else
    $messaggio_1 = "ATTENZIONE!!! ERRORE NEGLI IMPORTI";

?>

	
<script>	  
  	var modifica = 0;
	var blocca_modifica = 0;  


var tot_rate = "<?php echo $num_rate; ?>";
var file_richiesta = "<?php echo $file_richiesta; ?>";
var file_esito = "<?php echo $file_esito; ?>";
var file_bollettini = "<?php echo $file_bollettini; ?>";

$(function() {

for(var i=0;i<tot_rate;i++)
{

	$( "input#scadenza"+i ).datepicker();

}

});

$(document).ready(function(){
	
	$('#form_rate').ajaxForm(
			
	    function(value) {
			
	        var array_ritorno = value.split(' ');
	        
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
			link = "gestione_rate_pignoramento.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&pignoramento=<?php echo $pignoramento_ID; ?>&partita=<?php echo $partita_ID; ?>";
			window.name = "Rate";
			window.open(link,"Rate");
		}
		else if(array_ritorno[0]=='ERROR')
		{		
			alert("Errore nel salvataggio delle rate. "+value);
		}
		else
		{
			alert("Errore nella procedura "+value);
		}
		
	});


$("#submit_click").click(function salva_form() {     

	if($('#tot_rate').text() == $('#tot_dov').text())
	    $("#form_rate").submit();
	else
		alert("L'importo totale delle rate non coincide con l'importo dovuto!");

	});
	
	});

function calc_rate()
{
	importo_rata = 0.00;
	for(var i=0;i<tot_rate;i++)
	{
		importo_rata += parseFloat($( "input#importo"+i ).val().replace(",","."));
	}

	importo_rata = number_format(importo_rata,2,",","");


	$('#tot_rate').text(importo_rata);
}

function stampa_bollettini(value)
{
	tipo_stampa = $('#tipo_stampa').val();

	if(file_bollettini!="")
	{
		alert('File definitivo presente in archivio.');

		self.close();
		window.open(file_bollettini,"Stampa_Bollettini");
		
		return;
	}

	nominativo = $('#nome_gestore').val();
	posizione = $('#posizione_gestore').val();

	if(nominativo == "" || posizione == "")
	{
		alert("Dati gestore mancanti! Completare i dati per stampare l'esito");
		return;
	}
	
	link = "<?= WEB_ROOT; ?>/stampe/stampa_bollettini.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Pigno="+value+"&tipo_stampa="+tipo_stampa;

	self.close();
	window.open(link,"Stampa_Bollettino");
	
}

function stampa_richiesta(value)
{
	tipo_stampa = $('#tipo_stampa').val();
	
	if(file_richiesta!="")
	{
		alert('File definitivo presente in archivio.');

		self.close();
		window.open(file_richiesta,"Stampa_Richiesta");
		
		return;
	}
	
	link = "<?= WEB_ROOT; ?>/stampe/richiesta_rateizzazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Pigno="+value+"&tipo_stampa="+tipo_stampa;

	self.close();	
	window.open(link,"Stampa_Richiesta");
	
}

function stampa_esito(value)
{
	tipo_stampa = $('#tipo_stampa').val();

	if(file_esito!="")
	{
		alert('File definitivo presente in archivio.');

		self.close();
		window.open(file_esito,"Stampa_Esito");
		
		return;
	}
	
	nominativo = $('#nome_gestore').val();
	posizione = $('#posizione_gestore').val();

	if(nominativo == "" || posizione == "")
	{
		alert("Dati gestore mancanti! Completare i dati per stampare l'esito");
		return;
	}
	
	link = "<?= WEB_ROOT; ?>/stampe/esito_rateizzazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Pigno="+value+"&tipo_stampa="+tipo_stampa;

	self.close();
	window.open(link,"Stampa_Esito");
}

function control_richiesta()
{
	value = $('[name=esito_richiesta]:checked').val();
	if(value=="respinta")
	{
		$('#richiesta_respinta').prop('disabled',false).focus().removeClass('sfondo_grigio');;
	}
	else
	{
		$('#richiesta_respinta').prop('disabled',true).val('').focus().addClass('sfondo_grigio');;
	}
}

function cambio_data(value)
{
	data_current = $('#scadenza'+value).val();
	valore = parseInt(value);

	for(var i=valore+1;i<tot_rate;i++)
	{
		data_temp = creaData(data_current);
		data_new = aggiungiGiorni(data_temp, 30);
		stringa_data = stringaData(data_new);
		$('#scadenza'+i).val(stringa_data);
		data_current = stringa_data;
	}	
}

</script>
  
<table class="text_center pwidth750" border="0" cellspacing="5" cellpadding="0">
	<tr>
		<td><font class="titolo font18">Gestione Rate</font></td>
	</tr>
</table>

<br>

<form id=form_rate name=form_rate action="../pignoramento_salva.php" method=post>
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
<input type=hidden name=partita value=<?php echo $partita_ID; ?> >
<input name=invia_submit  id=invia_submit	type=hidden	value="rate">
<input type=hidden name=pignoramento value=<?php echo $pignoramento_ID; ?> >
<input type=hidden name=num_rate value=<?php echo $num_rate; ?> >  
  
<table class="text_center pwidth750" border="0">
<?php 

for($i=0;$i<count($importo);$i++)
{

?>


	<tr>
		<td class="text_left width3"><font class="titolo"><?php echo ($i+1); ?>.</font></td>
		<td class="text_left width15">Importo</td>
		<td class="text_right width12"><input class="text_right corrige_numero" type=text name=importo[] id="importo<?php echo $i; ?>" value=<?php echo $importo[$i]; ?> size=5 onchange="calc_rate();"> &euro;</td>
		<td class="text_left width15"></td>
		<td class="text_left width20" colspan=2>Data Scadenza</td>
		<td class="text_left width15"><input class="text_center" type=text name=scadenza[] id="scadenza<?php echo $i; ?>" value=<?php echo $scadenza[$i]; ?> size=9 onchange="cambio_data('<?php echo $i; ?>');"></td>
		<td class="text_left width20"></td>
	</tr>

		
<?php 

}

?>

	<tr>
		<td class="text_left" colspan=8><hr></td>
	</tr>
	<tr class="pheight30">
		<td class="text_left" colspan=2><b>Somma rate</b></td>
		<td class="text_right"><span id=tot_rate class="font_bold"><?php echo $tot_rate; ?></span> <font class="font_bold">&euro;</font></td>
		<td class="text_center"></td>
		<td class="text_center" colspan=4 rowspan=2><?php echo $messaggio_1; ?></td>
	</tr>
	<tr class="pheight30">
		<td class="text_left" colspan=2><b>Tot. rateizzazione</b></td>
		<td class="text_right"><span id=tot_dov class="font_bold"><?php echo $tot_rate; ?></span> <font class="font_bold">&euro;</font></td>
		<td class="text_center"></td>
		<td class="text_center" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left" colspan=8><hr></td>
	</tr>
</table>

<table class="text_center pwidth750" border="0">
	<tr class="pheight30">
		<td class="text_center width25"><font class="color_titolo font_bold">Selezione di stampa</font></td>
		<td class="text_center width25" colspan=2><font class="color_titolo font_bold">Richiesta rateizzazione</font></td>
		<td class="text_center width25" colspan=2><font class="color_titolo font_bold">Esito rateizzazione</font></td>
		<td class="text_center width25"><font class="color_titolo font_bold">Stampa Bollettini</font></td>
	</tr>
	<tr class="pheight30">
		<td class="text_center">
		<select class="width90" name=tipo_stampa id=tipo_stampa>
				<option value="provvisoria">Stampa provvisoria</option>
				<option value="definitiva">Stampa definitiva</option>
			</select>
		</td>
		<td class="text_center" colspan=2>
			<a id=pdf href="#" style="text-decoration:none;">
				<img id=bollettini src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none"
				width="25" height="25" onclick="stampa_richiesta('<?php echo $pignoramento_ID; ?>');" title="Stampa richiesta rateizzazione">
			</a>
		</td>
		<td class="text_center" colspan=2>
			<a id=pdf href="#" style="text-decoration:none;">
				<img id=bollettini src="<?= IMMAGINIWEB; ?>/pdfnew.png" style="text-decoration:none; border:none"
				width="25" height="25" onclick="stampa_esito('<?php echo $pignoramento_ID; ?>');" title="Stampa esito rateizzazione">
			</a>
		</td>
		<td class="text_center">
			<a id=pdf href="#" style="text-decoration:none;">
				<img id=bollettini src="<?= IMMAGINIWEB; ?>/img_bollettino.png" style="text-decoration:none; border:none"
				width="33" height="20" onclick="stampa_bollettini('<?php echo $pignoramento_ID; ?>');" title="Stampa bollettini">
			</a>
		</td>
	</tr>
	<tr>
		<td class="text_left" colspan=6><hr></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Incaricato rateizzazione</font></td>
		<td class="text_left width13">Nominativo</td>
		<td class="text_left width26" colspan=2><input name=nome_gestore id=nome_gestore value="<?php echo $nome_gestore; ?>" class="width90"></td>
		<td class="text_left width11">Posizione</td>
		<td class="text_left width25"><input name=posizione_gestore id=posizione_gestore value="<?php echo $posizione_gestore; ?>" class="width99"></td>
	</tr>
	<tr>
		<td class="text_left"><font class="color_titolo font_bold">Esito richiesta</font></td>
		<td class="text_left"><input type="radio" name=esito_richiesta value="accolta" <?php echo $checked_A; ?> onclick="control_richiesta();"> Accolta</td>
		<td class="text_left" colspan=2><input type="radio" name=esito_richiesta value="respinta" <?php echo $checked_R; ?> onclick="control_richiesta();"> Respinta</td>
		<td class="text_left" colspan=2></td>
	</tr>
	<tr class="respinta">
		<td class="text_left"><font class="color_titolo font_bold">Motivazione</font></td>
		<td class="text_left" colspan=5><input name=richiesta_respinta id=richiesta_respinta value="<?php echo $motivazione; ?>" class="width99"></td>
	</tr>
	<tr>
		<td class="text_left width64" colspan=4><font class="color_titolo font_bold">Soggetto che ha evaso la pratica di rateizzazione</font></td>
		<td class="text_left width36" colspan=2><input name=operatore id=operatore value="<?php echo $operatore; ?>" class="width99"></td>
	</tr>
	<tr>
		<td colspan=6><hr></td>
	</tr>
	<tr>
		<td colspan=6><br></td>
	</tr>
	<tr>
		<td colspan=6>
			<input type=button id=submit_click name=salva value=Salva class=button_red>
			<input type=button name=chiudi value=Chiudi class=button_azzurro onclick="self.close();">
		</td>
	</tr>
</table>
</form>

<?php echo $layout; ?>

<?php include(INC."/footer.php"); ?>
