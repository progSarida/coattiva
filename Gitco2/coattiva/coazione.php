<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_GestionePartita.php");

require_once MODELS."/PartitaTributi.php";
require_once CONTROLLERS."/DatiPignoramento.php";

$submenuPageNo = "6";
include(INC."/submenu_partita.php");

$cls_db = new cls_db();
$cls_partita = new cls_GP();

$motivo_sosp = "";
$note_sosp = "";
$data_att_sosp = "";
$layout = "";

$partita_ID = $cls_help->getVar('partita');

$parametri_notifica = $cls_partita->array_notifica();
$options_sosp = $cls_partita->options_select_array($parametri_notifica["SospensioneCoattiva"]);

$ctrl_DatiPignoramento = new DatiPignoramentoController($partita_ID);
$partita = $ctrl_DatiPignoramento->a_partita;
$a_pignoramenti = PartitaTributi::getPignoramentiByPartita($partita_ID);

if(count($a_pignoramenti)>0)
	$ultimoAtto = $a_pignoramenti[count($a_pignoramenti)-1]["ID"];
else
	$ultimoAtto = null;

$infoPigno = null;
$infoBanche = null;//"<b>Banche non inserite</b>";
$infoDatoriLavoro = null;//"<b>Datori di lavoro non inseriti</b>";
$infoImmobili = null;
$status = null;
$res = null;

$query_sosp = "SELECT * FROM sospensione_atto WHERE Partita_ID = '".$partita_ID."'";
$sosp_data = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_sosp));

if($sosp_data != null){
	$note_sosp = $sosp_data["Note_Sospensione"];
	$motivo_sosp = $sosp_data["Motivo_Sospensione_ID"];
	$data_att_sosp = $sosp_data["Data_Sospensione"];
}

if(!empty($ctrl_DatiPignoramento->a_pvt_pignoramento['DocumentType']))
	$infoPigno = $ctrl_DatiPignoramento->a_pvt_pignoramento['DocumentType']." configurato";
if(!empty($ctrl_DatiPignoramento->a_pvt_datori_lavoro) && count($ctrl_DatiPignoramento->a_pvt_datori_lavoro)>0)
	$infoDatoriLavoro = "<b style='color: darkgreen;'>Datori di lavoro inseriti</b>";
if(!empty($ctrl_DatiPignoramento->a_pvt_banche) && count($ctrl_DatiPignoramento->a_pvt_banche)>0)
	$infoBanche = "<b style='color: darkgreen;'>Banche inserite</b>";
if(!empty($ctrl_DatiPignoramento->a_pvt_immobili) && count($ctrl_DatiPignoramento->a_pvt_immobili)>0)
	$infoImmobili = "<b style='color: darkgreen;'>Immobili inseriti</b>";

if(!empty($partita_ID)){
	$query = "SELECT * FROM partita_tributi WHERE ID = ".$partita_ID." AND Flag_Sospensione = 'si'";
	$res = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	if($res != null)
		$status = "PARTITA SOSPESA";
}

$partita = $cls_partita->getDataPartita($partita_ID, $c, $a);

$query_sosp = "SELECT * FROM sospensione_atto WHERE Partita_ID = '".$partita_ID."'";
$sosp_data = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_sosp));

//var_dump($sosp_data);die;

if($partita["Flag_Sospensione"]=="si")
{
    $layout.= "<script>$('#flag_sosp').prop('checked',true);</script>";
    $layout.= "<script>$('#motivo_sosp').val('".$sosp_data["Motivo_Sospensione_ID"]."');cambia_title('motivo_blocco');</script>";

}


?>
<!-- GESTIONE MODALI -->
<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>
<script>
    // Apertura modale
    function openOfcanvas(type,rif){
        // Reset campi input
        $('#desc').val("");
        $('#year').val("");
        $('#name').val("");
        $('#cf').val("");
        $('#ricDesc').val("");
        $('#ricCode').val("");
        $('.user_entry').val("");

        // Reset spazi tabella
        $('#appendTableRole').empty();
        $('#appendTableOwner').empty();
        $('#appendTableCode').empty();
        $('#appendTableUserEntry').empty();

        selectRif = rif;
        switch (type){
            case 'user_entry':
                // Setta stato checkbox iniziale
                document.getElementById('check_u_n').checked = true;
                document.getElementById('check_u_c').checked = false;
                document.getElementById('check_e_cA').checked = false;
                document.getElementById('check_e_cP').checked = false;
                document.getElementById('check_e_i').checked = false;
                // Setta titolo modale iniziale
                $("#userEntrySearchModalLabel_u").show();
                $("#userEntrySearchModalLabel_e").hide();
                // Setta campo input iniziale
                $("#ins_u_n").show();
                $("#ins_u_c").hide();
                $("#ins_e_cA").hide();
                $("#ins_e_cP").hide();
                $("#ins_e_i").hide();
                // Setta tipop di ricerca iniziale
                //user_entry_S = "user_n";
                // Apre modale
                $('#userEntrySearchModal').modal('show');
                break;
        }
    }
    // Iserimento dati da modale a pagine
    function initialId(type,val){
        switch (type){
            // Inserimento dati utente in 'Gitco2/coattiva/gestione_ruolo.php'
            case "user":
            case "cf":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                break;
            // Inserimento dati partita in 'Gitco2/coattiva/gestione_partita.php'
            case "info":
            case "entry":
            case "fore":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/coazione.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento'];
                break;

            default: alert("Ricerca non trovata!"); break;
        }
    }
</script>
<!-- ********** GESTIONE LINK MENU ********** -->
<script>
var flag_blocco = "<?php echo $partita["Flag_Blocco_Coazione"] ?>";

function gestioneDati(){
	// alert("Inserimento dati pignoramento in fase di sviluppo!");
	// return false;

	if("<?php echo $partita_ID; ?>"!= "")
	{
		if(flag_blocco=="si")
		{
			alert("Partita bloccata! Impossibile inserire nuovi dati!");
			return false;
		}
		else
		{
			top.location.href = "dati_pignoramento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&flagInsert=1";
		}
	}
	else
		alert("selezionare una partita per inserire i dati relativi al pignoramento");
}

function openModalSosp(){
	if("<?php echo $partita_ID; ?>"!= "")
	{
		if(flag_blocco=="si")
		{
			alert("Attenzione! Impossibile sospendere una partita già bloccata!");
			return false;
		}
		else
		{
			//alert("Sospendi!");
			$('#blockModal').modal('show');
		}
	}
	else
		alert("selezionare una partita per inserire i dati relativi al pignoramento");
}

function dettagli_pigno(value)
{
	if(flag_blocco=="si")
		alert('ATTENZIONE! Partita bloccata!');

	top.location.href = "pignoramento.php?partita=<?php echo $partita_ID; ?>&pignoramento="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>

<?php
include_once(INC."/pages_authorization.php");
?>

<?php 
if(count($a_pignoramenti)>0)
{?>


<table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">
    <input type=hidden name=ultimoAtto id=ultimoAtto value="<?php echo $ultimoAtto; ?>" >
    <input type=hidden name=nomePagina id=nomePagina value="coazione" >

<tr class="text_left riga_dispari" style="height:30px;" >
	<td class="width4"><br></td>
	<td class="width1"><br></td>
	<td class="text_center width8"><b>Crono.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width8"><b>Anno</b></td>
	<td class="width1"><br></td>
	<td class="text_center width25"><b>Tipologia</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Importo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Spese not.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Spese acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Totale</b></td>
	<td class="width1"><br></td>
</tr>

<?php

for($i=0; $i<count($a_pignoramenti); $i++)
{
	$y = $i;

	if ($y++ % 2)
		{$stile_riga = 'class="riga_dispari text_left"'	;	}
	else
		{$stile_riga = 'class="riga_pari text_left"'	;	}

?>

		<tr <?php echo $stile_riga; ?>>
			<td class="text_center width4">
			<input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Dettagli Pignoramento" onClick="dettagli_pigno('<?php echo $a_pignoramenti[$i]["ID"]; ?>');return false;"></td>
			<td class="width1"><br></td>
			<td class="text_center width8"><?php echo $a_pignoramenti[$i]["ID_Cronologico"]; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width8"><?php echo $a_pignoramenti[$i]["Anno_Cronologico"]; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width25"><?php echo $a_pignoramenti[$i]["DocumentType"]; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($a_pignoramenti[$i]["Importo_Dovuto"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($a_pignoramenti[$i]["Totale_Spese_Notifica"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($a_pignoramenti[$i]["Totale_Spese_Accessorie"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($a_pignoramenti[$i]["Totale_Dovuto"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
		</tr>

	<?php }?>
	</table>

	<!-- <div style = "margin-top:10px; margin-left:65px;">
		<a class=" btn btn-primary" href ="../amministrazione/lista_notifiche.php?&p=<?=$p?>&c=<?=$c?>&a=<?=$a?>"> Lista notifiche</a> 
	</div> -->
<?php }
else if(!empty($partita_ID))
{?>
	<br>
	<div style="text-align: center;"><b>Nessun Pignoramento presente in archivio</b></div>
	<br>
	<!-- <div style="text-align: center;"><b>Per crearne uno nuovo selezionare F6</b></div>
	<br>
	<div style="text-align: center;">L'inserimento di un Pignoramento Presso Terzi necessita che le anagrafiche dei terzi siano presenti nella relativa pagina.</div>
	<br>
	<div style="text-align: center;">Se si deve effettuare un pignoramento presso il datore di lavoro ma non si conosce ancora l'esatta denominazione e/o sede dello stesso ma si conosce solo la matricola INPS </div>
    <div style="text-align: center;">dell'azienda presso la quale eseguire il pignoramento è possibile registrare il pignoramento presso terzi, presso il datore di lavoro, inserendo provvisoriamente solo questo dato.</div>
    <br>
	<div style="text-align: center;">Per integrare ad una registrazione dei dati parziale e' presente una apposita stampa relativa ai pignoramenti presso il datore di lavoro inseriti senza anagrafica del terzo associata.</div> -->

<?php }

if(count($a_pignoramenti)>0)
{?>
	<br>
	<div style="text-align: center;"><input type="button" onclick="openModalSosp();" class="btn btn-primary" value="Gestione sospensione"></div>
	<br>
	<br>

<?php }

if(!empty($partita_ID)){
	?>
	<div style="text-align: center;"><b style="color: red"><?=$status; ?></b></div>
	<br>
	<br>
	<div style="text-align: center;"><input type="button" onclick="gestioneDati();" class="btn btn-primary" value="Gestione dati"></div>
	<br>
	<div style="text-align: center;"><b style="color: red"><?=$infoPigno; ?></b></div>
	<br>
	<div style="text-align: center;"><?=$infoDatoriLavoro; ?></div>
	<div style="text-align: center;"><?=$infoBanche; ?></div>
	<div style="text-align: center;"><?=$infoImmobili; ?></div>
<?php

}
?>

<?php include(INC."/footer.php"); ?>

<div class="modal fade" id="blockModal" tabindex="-1" aria-labelledby="blockModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 65% !important;height: 85vh !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockModalLabel" style="color: blue;">Sospensione partita</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2.5vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body" style="height: 72vh !important;">
                <div style="overflow-x:hidden;overflow-y: auto;max-height:70vh;">
                    <form id="sospForm" method="POST" action="">

                        <input type="hidden" id="Atto_ID" name="Atto_ID" value="<?php echo $ultimoAtto; ?>">
						<input type="hidden" id="Partita_ID" name="Partita_ID" value="<?php echo $partita_ID; ?>">
                        <input type="hidden" id="data_sosp" name="data_sosp" value="<?= $data_att_sosp; ?>" />

                        <div class="row" style="margin-top: 2%;">
                            <div class="col-lg-1 col-lg-offset-10">
                                <i title="Per generare l'atto di sospensione della partita compilare i campi qui a fianco, salvare, e quindi cliccare sul pulsante dell'archiviazione sempre qui affianco!" style="cursor: pointer;" class="fa fa-info-circle fa-2x text_info" aria-hidden="true"></i>
                            </div>
                        </div>

                        <div style="border-top: 2px solid #244EE3; width: 100%;margin-bottom: 1%;margin-top: 1%;"></div>

                        <div class="row" >
                            <div class="col-lg-1 col-lg-offset-1">
                                <a class="col-lg-12" onMouseover="title='Stampa sospensione'" style="cursor:pointer;" id="printImg" onclick="sospendi('<?php echo $ultimoAtto; ?>');" >
                                    <img src="<?= IMMAGINIWEB; ?>/sospensione.png" width=20 height=20 >
                                </a>
                            </div>
                            <div class="col-lg-8"><p style="font-weight: bold;">Stampa sospensione partita</p></div>
                            <div class="col-lg-1">
                                <img style="width: 25px;cursor:pointer;" src="<?= IMMAGINIWEB ?>/pdf_icon_3.webp" id="printShow" onclick="apriPdfArchiviazioneSingola('<?= $FileSingolaArchiviazione; ?>');" />
                            </div>
                        </div>

                        <div style="border-top: 2px solid #244EE3; width: 100%;margin-bottom: 1%;margin-top: 1%;"></div>

                        <div class="row">
                            <div class="col-lg-4 col-lg-offset-1">
                                <label class="control-label col-lg-12">
                                    <input type="checkbox" name=flag_sosp id=flag_sosp value="si">
                                    <b id="block_data_single">SOSPENSIONE Partita <?php echo " ( ".$data_att_sosp." )"; ?></b>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="col-lg-5 control-label" style="text-align: left;">Motivi sospensione partita</label>
                                    <div class="col-lg-7">
                                        <select id=motivo_sosp name=motivo_sosp class="form-control" onchange="cambia_title('motivo_sosp');">
                                            <option value=""></option>
                                            <?php echo $options_sosp; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                           
                        </div>

                        <div class="row" style="margin-top: 2%;">
                            <div class="col col-lg-10 col-lg-offset-1">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label" style="text-align: left;">Motivazione sospensione partita</label>
                                    <div class="col-lg-9">
                                        <textarea style="max-width: 100%;" class="text_left form-control" id=note_sosp name=note_sosp rows="6" ><?php echo $note_sosp; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <div class="modal-footer">
              <button style="width: 100%;" class="btn btn-primary" type="button" id="btnSave" onclick="saveForm('salva');">Salva</button>
              <div class="row" id="btnSaveDel">
                <div class="col col-lg-6"><button style="width: 100%;" class="btn btn-primary" type="button" id="btnSave" onclick="saveForm('aggiorna');">Aggiorna</button></div>
                <div class="col col-lg-6"><button style="width: 100%;" class="btn btn-danger" type="button" id="btnSave" onclick="saveForm('elimina');">Elimina</button></div>
              </div>
            </div>
        </div>
    </div>
</div>

<script>

	var elaboration_id = "<?php echo $partita['Elaboration_Id'];?>";
	var flag_sosp = "<?php echo $partita["Flag_Sospensione"];?>";

	function saveForm(mode){

		//if(elaboration_id!=""){
		//	showMessage(2,"Partita presente in elenco di elaborazione! Impossibile effettuare modifiche");
		//	return false;
		//}
		var type= "";
		switch(mode){
			case 'salva':
				type= "Insert";
				break;
			case 'aggiorna':
				type= "Update";
				break;
			case 'elimina':
				type= "Delete";
				break;
		}

		//if(!validateForm())
			//return false;

		var data_value = $("#sospForm").serialize();

		if($('#flag_sosp').is(':checked')){
			$('#motivo_sosp').addClass("validateCustom vld_Custom_r");
			$('#note_sosp').addClass("validateCustom vld_Custom_r");
		}
		else{
			$('#motivo_sosp').removeClass("validateCustom vld_Custom_r");
			$('#note_sosp').removeClass("validateCustom vld_Custom_r");
		}

		control = submit_buttons(type);

		if(control && validateForm() )
			$.ajax({
				type: "POST",
				async: true,
				url: "ajax/ajax_save_sosp_pigno.php",
				dataType: "json",
				data: data_value+"&mode="+mode,
				success: function(response) {
					if(response.error == 0){
						location.href = "<?= WEB_ROOT ?>/coattiva/coazione.php?c=<?= $c; ?>&a=<?= $a; ?>&partita=<?= $cls_help->getVar('partita'); ?>&p=<?= $cls_help->getVar('p'); ?>&msg="+response.msg+"&error="+response.error;
					}
					else{
						//showMessage(response.error,response.msg);
						location.href = "<?= WEB_ROOT ?>/coattiva/coazione.php?c=<?= $c; ?>&a=<?= $a; ?>&partita=<?= $cls_help->getVar('partita'); ?>&p=<?= $cls_help->getVar('p'); ?>&msg="+response.msg+"&error="+response.error;
					}
				},
				error: function(response){
					console.log(response);
				}
			});
	}

	function cambia_title(value)
	{
		testo = $('#'+value+ ' option:selected').text();
		$('#'+value).attr('title',testo);
	}

	$(document).ready(function(){

		if(flag_sosp=="si"){
			$("#btnSaveDel").show();
			$("#btnSave").hide();
		}
		else {
			$("#btnSaveDel").hide();
			$("#btnSave").show();
		}

	});

	function sospendi(value)
    {
        if(!$('#flag_sosp').is(":checked"))
        {
            alert("Spuntare il flag di sospensione dell'atto!");
            return false;
        }
        if("<?= $motivo_sosp; ?>" != $("#motivo_sosp").val() || "<?= $note_sosp; ?>" != $("#note_sosp").val())
        {
            alert("Prima di procedere salvare le motivazioni!");
            return false;
        }
        if($("#motivo_sosp").val() == "" && $("#note_sosp").val() == ""){
            alert("Prima di procedere specificare la motivazione della sospensione!");
            return false;
        }

        link = "<?= SUPER_WEB_ROOT; ?>/Gitco2/stampe/sospensione_pigno.php?richiesta_singola=si&c=<?php echo $c; ?>&a=<?php echo $a?>&ID_Atto="+value;
        location.href= link;
    }
	
</script>

<?php echo $layout; ?>