<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include_once(INC . "/menu.php");
include_once(CLS . "/cls_DateTimeInLine.php");
include_once(CLS . "/cls_CoazioneUtils.php");
include_once(CLS . "/cls_GestionePartita.php");

$cls_date = new cls_DateTimeI("IT", false);
$cls_coat = new cls_Coazione();
$cls_partita = new cls_GP();




$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$aut = $_SESSION['aut_tipo'];

$tskill = $cls_help->getVar('tskill');

$tskill = is_null($tskill) ? '1' : $tskill;
$utente = $cls_coat->getDataUtente($p, $c);


?>

<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=10% class="text_center">
			<a onMouseover="title='Cerca utente/partita'" href="#" onClick="/*RicercheDaId('utente',0);*/ricerca_F9()" style="text-decoration: none;">
				<img src="<?= IMMAGINIWEB; ?>/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=10% class="text_center">
			<div class="form-group">
				<select id="tskill" name="tskill" style="margin-top:15px;">
					<option value="1">UTENTE </option>
					<option value="2">FLUSSO </option>
				</select>
			</div>

		</td>
		<td id="kindDescription" width="80%"></td>
	</tr>
</table>
<!-- ********** GESTIONE LINK MENU ********** -->
<script>
	//F5
	switchMenuImg("F5");
	F5_button = function() {
		location.href = "gestione_ruolo.php?p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}

	//F6
	switchMenuImg("F6");
	F6_button = function() {
		crea_partita();
	}
	<?php
	//F7
	if ($tskill !== "2") {
	?>
		//F7
		switchMenuImg("F7");
		F7_button = function() {
			value = "<?php echo $utente["prev"]; ?>";
			location.href = "gestione_ruolo.php?p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		}

		//F8
		switchMenuImg("F8");
		F8_button = function() {
			value = "<?php echo $utente["next"]; ?>";
			location.href = "gestione_ruolo.php?p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		}
	<?php
	}
	?>
	//F9
    F9_button = function() {
        ricerca_F9();
    }
    /// ?????????????????????????????
	function ricerca_F9() {
		/*RicercheDaId('utente', 0);*/
        openOfcanvas('user_entry',0);
	}

	//F11-F12 sono nel menu'

	/* GV - 03/05/2022 - START */
	switchMenuImg("F11");
	F11_button = function() {


		$("#frameHelp").attr("src", "<?= SUPER_WEB_ROOT . "/archivio/help/RiorganizzazionePagina.pdf"; ?>");
		$("#helpModalLabel").empty().append("<b>Help GESTIONE RUOLO</b>");
		$("#helpModal").modal('show');

	}
	/* GV - 03/05/2022 -   END */


	//******************************\\
	//ALTRI LINK / FUNZIONI CHIAMATE\\
	function partita(value, anno) {
		//alert(value+" "+anno);
		location.href = "gestione_partita.php?partita=" + value + "&c=<?php echo $c; ?>&a=" + anno;
	}

	function crea_partita(value) {
		location.href = "nuova_partita.php?&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}

	function anagrafe(value) {
		if (value != 0)
			location.href = "<?= WEB_ROOT; ?>/anagrafe/dati_soggetto.php?p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		else
			alert('Nessun utente è stato selezionato!');
	}
</script>

<!-- ********** MODALI ********** -->
<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>
<script>
    // Modali offcanvas
    function openOfcanvas(type,rif){
        // Reset campi input
        $('.user_entry').val("");

        // Reset spazi tabella
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

	function Dim_Alert(sWidth, sHeight) {
		setupPagina = "dialogWidth:" + sWidth + "px";
		setupPagina += "; dialogHeight:" + sHeight + "px";
		setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
	}

	var valorediritorno = null;

	function RicercheDaId(value, rif) {
		var valorediritorno = 0;
		var strDim = Dim_Alert(600, 300);

		switch (value) {
			case "utente":

				strDim = Dim_Alert(800, 400);
				var stringa = "<?= WEB_ROOT ?>/search/coattiva/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				valorediritorno = window.showModalDialog(stringa, "", strDim);

				break;
		}
	}

	function callParent(result) {
		//Code
		if (typeof result !== 'string' && result != null)
			reopen('obj', result);
		else if (result != null)
			reopen('str', result);
	}

	function reopen(type, value) {
		if (type == 'obj')
			top.location.href = "<?= WEB_ROOT ?>/coattiva/gestione_partita.php?mode=consulta&partita=" + value.ID + "&c=<?php echo $c; ?>&a=" + value.Anno;
		else if (type == 'str')
			top.location.href = "<?= WEB_ROOT ?>/coattiva/gestione_ruolo.php?mode=consulta&p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
</script>
<br>
<?php

if ($tskill === '1') // UTENTE
{

	$query = "SELECT * FROM parametri_annuali WHERE CC='".$c."' ORDER BY Anno DESC LIMIT 1";
	$a_par_annuali = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));


	$query = "SELECT P.*, T.Info_Cartella, PN.Descrizione AS Motivo_Blocco_Descrizione, E.Document_Type_Id FROM partita_tributi P ".
	"LEFT JOIN tributo T ON T.Partita_ID=P.ID ".
	"LEFT JOIN parametri_notifica PN ON PN.ID=P.Motivo_Blocco ".
	"LEFT JOIN elaborations E ON E.ID=P.Elaboration_Id ".
	"WHERE P.Utente_ID = '".$p."' AND P.CC = '".$c."' GROUP BY P.ID";
	$a_partite = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");

	?>
	<div id="sez_utente" style="padding-left:7.5rem;padding-right:7.5rem; margin-bottom:3rem;">

		<?php 
		foreach($a_partite as $partitaId=>$a_partita){



			if ($a_partita["Flag_Blocco_Coazione"] == "si"){
				$fontColor = "color: red;";
				$bgColor = "background-color: red;";
			}
			else{
				$fontColor = "color: #356bc1;";
				$bgColor = "background-color: #356bc1;";
			}

?>
			<div class="divPartita" style="padding:2rem;">
				<div class="row">
					<div class="col-sm-2 col-md-2">
						<button class="btn" onMouseover="title='Seleziona la partita'" onclick="partita('<?php echo $a_partita['ID']; ?>','<?php echo $a_partita['Anno_Riferimento']; ?>')" 
						style="text-decoration: none;color: white; <?=$bgColor;?>">
							<b >
								Partita <?= $a_partita["Comune_ID"]; ?>
							</b>
						</button>
					</div>
					<div class="col-sm-7 col-md-7" style="line-height:3.4rem">
						<span style="<?=$fontColor?> font-size:1.65rem"><b><?= $a_partita["Tipo"] ?> <?= $a_partita["Sottotipo"] ?> <?= $a_partita["Info_Cartella"]; ?></b></span>
					</div>
					<div class="col-sm-3 col-md-3 text-right"  style="line-height:3.4rem">
						<?php if($a_partita["Flag_Blocco_Coazione"] == "si"){
							?>
							<b style="<?=$fontColor?>">BLOCCATA</b>
							<b>Motivazione: </b> <b style="<?=$fontColor?>"><?= $a_partita['Motivo_Blocco_Descrizione']; ?></b>
							<img id="info" src="<?= IMMAGINIWEB; ?>/info.png" title="<?= $a_partita['Note_Blocco']; ?>" width=15px height=15px>
							<?php
						}
						else if(!empty($a_partita["Elaboration_Id"])){
							$listElaboration = "";
							switch($a_partita['Document_Type_Id']){
								case 7:
									$listElaboration = ELAB_PIGNORAMENTI_LAVORO_WEB."/mgmt_pignoramenti_lavoro";
									break;
								case 6:
								case 22:
									$listElaboration = ELAB_PIGNORAMENTI_WEB."/mgmt_pignoramenti";
								default:    
									$listElaboration = ELAB_ATTI_WEB."/mgmt_elaboration";
									break;
							}
							$listElaboration.= ".php?c=".$c."&a=".$a_partita['Anno_Riferimento']."&el=".$a_partita['Elaboration_Id'];
							?>
							<a class="btn" href="<?=$listElaboration;?>" onMouseover="title='Vai alla elaborazione'" style="background-color:dodgerblue; color:white" ><b>ELENCO ELABORAZIONE</b></a>	
							<?php
						}
						?> 
					</div>
					
				</div>
			</div>

			<style>
				.divAtti          { overflow: auto; max-height: 230px;}
				.divAtti thead th { position: sticky; top: 0; z-index: 1; }

				/* Just common table stuff. Really. */
				table  { border-collapse: collapse; width: 100%; }
				th, td { padding: 8px 16px; }
				th     { background:#6D95D5; }
			</style>

			<div class="divAtti">
				<table class="table" border=1>
					<thead>
						<tr style="background-color:#6D95D5; color:white">
							<th class="text_center" style="width:11rem">Cronologico</th>
							<th>Tipo Documento</th>
							<th class="text_center" style="width:9rem">Elaborazione</th>
							<th class="text_center" style="width:11rem">Data Stampa</th>
							<th class="text_center" style="width:11rem">Data Notifica</th>
							<th class="text_center" style="width:9rem">Totale 1</th>
							<th class="text_center" style="width:9rem">Totale 2</th>
							<th class="text_center" style="width:9rem">Totale 3</th>
							<th class="text_center" style="width:9rem">Rate</th>
							<th class="text_center" style="width:9rem">Pagamenti</th>
							<th class="text_center" style="width:9rem">Pagato</th>
							<th class="text_center" style="width:9rem">Ricorso</th>
						</tr>
					</thead>
					<tbody>
					<?php

			$a_pignoDocId = [6,7,8,22];

			$query = "SELECT DOC.*, R.ID AS Appeal_ID, R.Court_Level AS Appeal_Court_Level, R.Judge as Appeal_Judge, R.Start_Date as Appeal_Start_Date, AUT.Description AS Authority_Description, ".
			"IFNULL(SUM(P.Importo),0) AS Totale_Pagamenti, COUNT(P.ID) AS Numero_Pagamenti, IFNULL(SUM(PV.Importo),0) AS Vecchi_Pagamenti FROM ".
			"( SELECT D.ID, D.Elaboration_Id, null AS Atto_Collegato_ID, null AS Atto_Collegato_Crono, D.DocumentTypeId, D.Partita_ID, ".
			"D.ID_Cronologico, D.Anno_Cronologico, D.Data_Notifica, D.Totale_Dovuto, D.Totale_Rateizzato, IFNULL(D.Rate_Previste,0) AS Rate_Previste, D.Data_Stampa, ".
			"D.Diritto_Riscossione_Minimo, D.Diritto_Riscossione_Massimo, ".
			"D.Data_Elaborazione, DT.Description AS DocumentType FROM atto D JOIN document_type DT ON DT.Id=D.DocumentTypeId ".
			"UNION ".
			"SELECT D.ID, D.Elaboration_Id, D.Atto_ID AS Atto_Collegato_ID, CONCAT(AC.Atto,' ',AC.ID_Cronologico,'/',AC.Anno_Cronologico) AS Atto_Collegato_Crono, ". 
			"D.DocumentTypeId, D.Partita_ID, D.ID_Cronologico, D.Anno_Cronologico, NA.Data_Notifica, D.Totale_Dovuto, 0 AS Totale_Rateizzato, ".
			"IFNULL(D.Rate_Previste,0) AS Rate_Previste, NA.Data_Stampa, ". 
			"0 AS Diritto_Riscossione_Minimo, 0 AS Diritto_Riscossione_Massimo, ".
			"D.Data_Elaborazione, DT.Description AS DocumentType ". 
			"FROM pignoramento_generale D JOIN atto AC ON AC.ID=D.Atto_ID JOIN document_type DT ON DT.Id=D.DocumentTypeId ". 
			"JOIN notifica_atto NA ON NA.Atto_Notificato_ID=D.ID AND NA.Tipo_Notifica='debitore' ) AS DOC ".
			"LEFT JOIN pagamento P ON P.Atto_ID=DOC.ID AND P.DocumentTypeId=DOC.DocumentTypeId ".
			"LEFT JOIN pagamento PV ON PV.Partita_ID=DOC.Partita_ID AND PV.Atto_ID!=DOC.ID AND PV.Data_Pagamento<DOC.Data_Stampa ".
			"LEFT JOIN appeal R ON R.Act_ID=DOC.ID AND DOC.DocumentTypeId IN (2,4) ".
			"LEFT JOIN ufficio_giudiziario AU ON AU.ID=R.Authority_ID ".
			"LEFT JOIN authority_type AUT ON AUT.Type =AU.Tipo ".
			"WHERE DOC.Partita_ID=".$a_partita['ID']." GROUP BY DOC.ID ORDER BY DOC.Data_Elaborazione DESC";

			$a_docs = $cls_db->getResults($cls_db->ExecuteQuery($query));
			foreach($a_docs as $docKey=>$a_doc){
				$a_totale = array(1=>0,2=>0,3=>0);
				$a_dovuto = array(1=>"",2=>"",3=>"");
				$linkRicorso = WEB_ROOT."/coattiva/appeal_list.php?partita=".$a_doc['Partita_ID']."&c=".$c."&a=".$a_partita['Anno_Riferimento'];
				$linkElaboration = "";
				switch($a_doc['DocumentTypeId']){
					case 7:
						$linkElaboration = ELAB_PIGNORAMENTI_LAVORO_WEB."/mgmt_pignoramenti_lavoro";
						break;
					case 6:
					case 22:
						$linkElaboration = ELAB_PIGNORAMENTI_WEB."/mgmt_pignoramenti";
						break;
					default:    
						$linkElaboration = ELAB_ATTI_WEB."/mgmt_elaboration";
						break;
				}
				$linkElaboration.= ".php?c=".$c."&a=".$a_partita['Anno_Riferimento']."&el=".$a_doc['Elaboration_Id'];

				if(in_array($a_doc['DocumentTypeId'], $a_pignoDocId)){
					$link = WEB_ROOT."/coattiva/coazione.php?partita=".$a_doc['Partita_ID']."&c=".$c."&a=".$a_partita['Anno_Riferimento'];
					$query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID=".$a_doc["ID"];
					$a_pignoSpese = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
					for($i=1;$i<=10;$i++){
						if(!empty($a_pignoSpese['Rimborso_'.$i]) && !empty($a_pignoSpese['Tipo_Totale_'.$i]))
							$a_totale[(int)$a_pignoSpese['Tipo_Totale_'.$i]]+=(float)$a_pignoSpese['Rimborso_'.$i];
					}

					$Totale_Senza_Spese = $a_doc["Totale_Dovuto"]-$a_totale[1]-$a_totale[2]-$a_totale[3];

					$a_dovuto[1] = number_format($Totale_Senza_Spese+$a_totale[1], 2, ",", ".")." &euro;";
					if(!empty($a_totale[2]))
						$a_dovuto[2] = number_format($Totale_Senza_Spese+$a_totale[1]+$a_totale[2], 2, ",", ".")." &euro;";
					if(!empty($a_totale[3]))
						$a_dovuto[3] = number_format($Totale_Senza_Spese+$a_totale[1]+$a_totale[2]+$a_totale[3], 2, ",", ".")." &euro;";

					$maxDovuto = $a_doc["Totale_Dovuto"];
				}
				else{
					$link = WEB_ROOT."/coattiva/ingiunzione.php?partita=".$a_doc['Partita_ID']."&c=".$c."&a=".$a_partita['Anno_Riferimento'];
					$a_totale[1] = $a_doc["Totale_Dovuto"]+$a_doc["Diritto_Riscossione_Minimo"]-$a_doc["Vecchi_Pagamenti"];
					$a_totale[2] = $a_doc["Totale_Dovuto"]+$a_doc["Diritto_Riscossione_Massimo"]-$a_doc["Vecchi_Pagamenti"];
					$a_dovuto[1] = number_format($a_totale[1], 2, ",", ".")." &euro;";
					$a_dovuto[2] = number_format($a_totale[2], 2, ",", ".")." &euro;";

					$maxDovuto = $a_totale[2];
				}


				$elaborationId = "";
				if($a_doc['Elaboration_Id']>0)
					$elaborationId = "<a style='cursor:pointer;' href=\"".$linkElaboration."\">".$a_doc['Elaboration_Id']."</a>";

				$crono = "<a style='color:red; cursor:pointer;' href=\"".$link."\">ASSENTE</a>";
				if($a_doc['ID_Cronologico']>0)
					$crono = "<a style='cursor:pointer;' href=\"".$link."\">".$a_doc['Anno_Cronologico']." / ".$a_doc['ID_Cronologico']."</a>";

				$pagamento = "";
				if($a_doc["Totale_Pagamenti"]>0)
					$pagamento = "<span style='color:darkgreen;'>".number_format($a_doc["Totale_Pagamenti"], 2, ",", ".")." &euro;</span>";
				$numPagamenti = "";
				if($a_doc["Numero_Pagamenti"]>0)
					$numPagamenti = "<span style='color:darkgreen;'>".$a_doc['Numero_Pagamenti']."</span>";

				$ratePreviste = "";
				if($a_doc['Rate_Previste']>0)
					$ratePreviste = $a_doc['Rate_Previste'];
				
				$data_stampa = $cls_date->Get_DateNewFormat($a_doc['Data_Stampa'], "DB");
				if(empty($a_doc["Data_Stampa"]))
					$data_stampa = "<span style='color:red'>ASSENTE</span>";

				$bg_row = "";
				if($maxDovuto<=$a_doc["Totale_Pagamenti"]+$a_par_annuali['Importo_Minimo']){
					$bg_row = "background-color: lightgreen;";
				}
				else if(empty($a_doc["Data_Stampa"]))
					$bg_row = "background-color: yellow;";

				$strRicorso = "";
				if(!empty($a_doc["Appeal_ID"])){
					$appealInfo = "Registrato il ".$cls_date->Get_DateNewFormat($a_doc['Appeal_Start_Date'], "DB")."\\n".$a_doc["Authority_Description"].": ".$a_doc["Appeal_Judge"];
					$strRicorso = "<a style='cursor:pointer;' href=\"".$linkRicorso."\" onMouseover=\"title='".$appealInfo."'\">Grado ".$a_doc["Appeal_Court_Level"]."</a>";
				}
				
					
				?>
				<tr style="<?=$bg_row;?>">
					<td class="text_left"><b><?= $crono; ?></b></td>
					<td><?=$a_doc['DocumentType'];?></td>
					<td class="text_center"><b><?=$elaborationId;?></b></td>
					<td class="text_center"><b><?=$data_stampa;?></b></td>
					<td class="text_center"><b><?=$cls_date->Get_DateNewFormat($a_doc['Data_Notifica'], "DB");?></b></td>
					<td class="text_right"><b><?=$a_dovuto[1];?></b></td>
					<td class="text_right"><b><?=$a_dovuto[2];?></b></td>
					<td class="text_right"><b><?=$a_dovuto[3];?></b></td>
					<td class="text_center"><b><?=$ratePreviste;?></b></td>
					<td class="text_center"><b><?=$numPagamenti;?></b></td>
					<td class="text_right"><b><?=$pagamento;?></b></td>
					<td class="text_center"><b><?=$strRicorso;?></b></td>
					</tr>
					<?php
			}
?>
					</tbody>
				</table>
			</div>


		<?php
		}
		?>


		



		

	</div>
<?php



} //if (is_null($tskill) || $tskill==='1')
else { //FLUSSO

?>
	<!-- Inserzione tabella flusso -->
	<div class="container">
		<div class="row">
			<div class="col-sm-11 col-md-8 col-lg-12">
				<div id="table_flow">
					<?php

					if (isset($_SESSION['flussi_arr'])) {
						$flussi_arr = $_SESSION['flussi_arr'];
					?>
						<?php
						$class = "btn btn-success";
						foreach ($flussi_arr as $Flusso) {
							foreach ($Flusso['document_type'] as $tipo) {
								if ($cls_help->toItalianDate(isset($Flusso['CancelDate']) ? $Flusso['CancelDate'] : null) != null) {

									if ($tipo['TableTypeId'] == 2) {
										$class = "btn btn-succes";
										$step = "<span class='color_green'><b>PIGNORAMENTO</b></span>";
										$descrizione =  $tipo['Description'];
									} else {
										$class = "btn btn-danger";
										$step = "<span class='color_red'><b>ANNULLATO</b></span>";
										$descrizione =  $tipo['Description'];
									}
								} else if ($cls_help->toItalianDate(isset($Flusso['SendDate']) ? $Flusso['SendDate'] : null) != null) {
									if ($tipo['TableTypeId'] == 2) {
										$class = "btn btn-success";
										$step = "<span class='color_green'><b>PIGNORAMENTO</b></span>";
										$descrizione =  $tipo['Description'];
									} else {
										$class = "btn btn-primary";
										$step = "<span class='color_black'><b>CONSEGNATO</b></span>";
										$descrizione =  $tipo['Description'];
									}
								} else if ($cls_help->toItalianDate(isset($Flusso['PostagePaymentDate']) ? $Flusso['PostagePaymentDate'] : null) != null) {
									if ($tipo['TableTypeId'] == 2) {
										$class = "btn btn-success";
										$step = "<span class='color_green'><b>PIGNORAMENTO</b></span>";
										$descrizione =  $tipo['Description'];
									} else {
										$class = "btn btn-primary";
										$step = "<span class='color_black'><b>PAGATO</b></span>";
										$descrizione =  $tipo['Description'];
									}
								} else if ($cls_help->toItalianDate(isset($Flusso['ProcessingDate']) ? $Flusso['ProcessingDate'] : null) != null) {
									if ($tipo['TableTypeId'] == 2) {
										$class = "btn btn-succes";
										$step = "<span class='color_green'><b>PIGNORAMENTO</b></span>";
										$descrizione =  $tipo['Description'];
									} else {
										$class = "btn btn-primary";
										$step = "<span class='color_black'><b>LAVORATO</b></span>";
										$descrizione =  $tipo['Description'];
									}
								} else if ($cls_help->toItalianDate(isset($Flusso['UploadDate']) ? $Flusso['UploadDate'] : null) != null) {
									if ($tipo['TableTypeId'] == 2) {
										$class = "btn btn-success";
										$step = "<span class='color_green'><b>PIGNORAMENTO</b></span>";
										$descrizione =  $tipo['Description'];
									} else {
										$class = "btn btn-primary";
										$step = "<span class='color_black'><b>UPLOAD</b></span>";
										$descrizione =  $tipo['Description'];
									}
								} else {
									if ($tipo['TableTypeId'] == 2) {
										$class = "btn btn-success";
										$step = "<span class='color_green'><b>PIGNORAMENTO</b></span>";
										$descrizione =  $tipo['Description'];
									} else {
										$class = "btn btn-primary";
										$step = "<span><b>CREATO</b></span>";
										$descrizione =  $tipo['Description'];
									}
								}
							}
						?>
							<div class='container'>
								<button type='button' class="<?php echo $class ?>" style='margin-left:20px;' data-toggle='collapse' data-target='#demo_<?php echo $Flusso["flowId"] ?>'>Flusso: <?php echo $Flusso["Num_Flusso"] ?> / <?php echo $Flusso["Anno_Flusso"] ?> - <?php echo $Flusso["CC"] ?></button> &nbsp;&nbsp;<?php echo $step . "&nbsp;&nbsp;<span><b>" . $descrizione . "</b></span>"; ?>
								<div id='demo_<?php echo $Flusso["flowId"] ?>' class='collapse'>
									<table id='flows' class='table table_interna text_center' border='0' style='border:3px solid #6D95D5; width:95%;  margin-left:18px; margin-top:10px;'>
										<thead>
											<tr>
												<th>Link</th>
												<th>Partita ID</th>
												<th>Cronologico</th>
												<th>Tipo Atto</th>
												<th>Data Notifica </th>
												<th>Esito</th>
												<th>Tipo Utente</th>
												<th>Utente</th>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach ($Flusso["Utenti"] as $record) {

												echo "<tr>";

												echo 	"<td>"
													.   "	<a id='partita' onMouseover=\"title='Dettagli Notifica'\" href ='"
													. WEB_ROOT . "/coattiva/ingiunzione.php?partita=" . $record["Partita_ID"] . "&c=" . $Flusso["CC"] . "&pageCalled=' style='text-decoration:none;' >" .
													"		<img src='" . IMMAGINIWEB . "/select.png' style='width:25px; height:25px; border:0;' >" .
													"	</a>" .
													"</td>";

												echo 	"<td>" . $record["Com_ID"] . "</td>";

												echo 	"<td>" . $record["Cronologico"] . "</td>";

												echo 	"<td>" . $record["Atto"] . "</td>";

												$dataNotifica = "";
												if (!is_null($record["Data_Notifica"])) {

													$tempDate = date_create($record["Data_Notifica"]);
													$dataNotifica = date_format($tempDate, 'd/m/Y');
												} else {
													$dataNotifica = "ASSENTE";
												}

												echo 	"<td>" . $dataNotifica . "</td>";

												echo 	"<td>" . $record["Modalita_Not_Descrizione"] . " " . $record["Stato_Not_Descrizione"] . " " . $record["Anomalia_Not_Descrizione"] . "</td>";

												$genere =  $record["Genere"];
												$individuo = $record["Nome_Cognome"];
												if ($genere !== 'D') {
													$genere = "Persona fisica";
												} else {
													$genere = "Ditta";
													$individuo = $record["Ditta"];
												}
												echo 	"<td>" . $genere . "</td>";

												echo 	"<td>" . $individuo . "</td>";

												echo 	"</tr>";
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
							<br />
							<div class="clean_row HSpace4" style="margin-left:35px ; width: 95%; min-height: 100%;"></div>
							<br />
						<?php
						}
						//} // if (!is_null($fId))
						?>
				</div>
			</div>
		</div>
	</div>
<?php

					} // if (isset($_SESSION['flussi_arr']))
?>
</div>
</div>
</div>
</div>
<?php
}
?>
<!-- GV - START -->
<script>
	var provenienza = '<?php echo $tskill ?>';
	$('#tskill').change(function() {

		var id = $(this).val();


		var url = "<?php echo IMMAGINIWEB; ?>";
		var p = "<?php echo $p; ?>";
		var c = "<?php echo $c; ?>";
		var cID = "<?php echo $utente["Comune_ID"]; ?>";
		var strUtente = "<?php
							if ($utente["Genere"] != 'D') {
								echo $utente["Cognome"] . " " . $utente["Nome"];
							} else {
								echo $utente["Ditta"];
							}
							?>";
		var rad = "/select.png";
		var path = url.concat(rad);

		if (id == '1') { // UTENTE

			if (checkMenuImg('F7') !== 1) {
				switchMenuImg('F7');
				switchMenuImg('F8');
			}

			$("#table_flow").html('');

			$("#kindDescription").html("");
			var valueIdCerca = '';
			var printUtente = '';
			var valueP = '';
			if (provenienza === '1') {
				valueIdCerca = cID;
				printUtente = strUtente;
				valueP = p;
			}
			$("#kindDescription").html(
				'<div class="container-fluid">' +
				'<div class="row row-no-gutters">' +
				'<div class="col-sm-5" style="padding:4px;">' +
				'<em style="background-color:rgb(251,255,208); font-style : normal ;">' +
				printUtente +
				'</em>' +
				'</div>' +
				'<div  class="col-sm-4 text_left" >' +
				'<a onMouseover="title=\'Gestione Ruolo\'" href="#" style="text-decoration:none;display: inline;" onclick="anagrafe(' + valueP + ');" ><img src="' + path + '" style="width:25px; height:25px; border:0;" ></a><p style="font-weight: bold;display: inline;">Vai alla pagina dell\'Anagrafe</p>' +
				'</div>' +
				'<div class="col-sm-3 text_right">' +
				'<form id = "cerca_id" method = "post" action = "modali/ricerca_codice_result.php" >' +
				'<input name = "c" type = "hidden" value="' + c + '">' +
				'<input name = "a" type = "hidden" value="<?php echo $a; ?>">' +
				'Utente ID ' +
				'<input id="id_cerca" tabindex="1" class="valign_center text_right" name="ric_cod_contr" value="' + valueIdCerca + '" size="3" onMouseover="title=\'Inserire il codice utente e premere Invio\'">  ' +
				'</form>' +
				'</div>' +
				'</div>' +
				'</div>'
			);
			setAjaxForm();
		} else // FLUSSSO
		{

			if (checkMenuImg('F7') === 1) {
				switchMenuImg('F7');
				switchMenuImg('F8');
			}


			<?php
			$num_flusso = $cls_help->getVar('nf');
			$anno_flusso = $cls_help->getVar('af');
			$stato = $cls_help->getVar('status');
			$cod_catastale = $cls_help->getVar('cod_catastale');



			?>

			var num_flusso = '';
			var anno_flusso = '';
			var stato = "";
			var cod_catastale = "";
			var auth = "<?php echo $aut; ?>";
			console.log("auth: " + auth);
			var disable_i = "";


			if (auth > 1) {
				disable_i = "disabled";
			}



			if (provenienza === '2') {

				num_flusso = '<?php echo $num_flusso ?>';
				anno_flusso = '<?php echo $anno_flusso ?>';
				stato = '<?php echo $stato ?>';
				cod_catastale = '<?php echo $cod_catastale ?>';



			}



			$("#sez_utente").html("");

			var t = ($('#tskill').val());

			$("#kindDescription").html("");
			$("#kindDescription").html(
				'<div class="container" style= "width:100%">' +
				'<div class="row row-no-gutters" >' +
				'<div class="col-sm-2 text_center">' +
				'<em id="etichetta_flusso" style="background-color:rgb(251,255,208); font-style : normal ;">FLUSSO</em>' +
				'</div>' +
				'<div class="col-sm-10 text_right">' +
				'<form id = "cerca_flusso" method = "post" action = "ajax/ajax_flussi.php" >' +
				'<input id="c"  name="c" type = "hidden" value="' + c + '">' +
				'<input id="aut"  name="aut" type = "hidden" value="' + <?php echo $aut; ?> + '">' +
				'NR FLUSSO: ' +
				'<input id="num_flusso" tabindex="1" class="valign_center text_right" name="num_flusso" size="3" onMouseover="title=\'Inserire numero del flusso, anno del flusso  e premere Invio\'" value="' + num_flusso + '">   ' +
				' &nbsp;&nbsp; ANNO FLUSSO: ' +
				'<input id="anno_flusso" tabindex="2" class="valign_center text_right" name="anno_flusso"  size="3"  onMouseover="title=\'Inserire numero del flusso, anno del flusso  e premere Invio\'" value="' + anno_flusso + '">  ' +
				' &nbsp;&nbsp; COD CATASTALE:' +
				'<input id="cod_comune" tabindex="3" class="valign_center text_right " name="cod_comune"  size="3"  onMouseover="title=\'Inserire numero del flusso, anno del flusso , codice catastale  e premere Invio\'" ' + disable_i + ' value="' + cod_catastale + '">  ' +
				' &nbsp;&nbsp; STATUS: ' +
				'<select id="flowStatus" name="flowStatus" class="pwidth150">' +
				'<!-- <?php echo $stato; ?>-->' +
				'<option></option>' +
				'<option value="1" <?php if ($stato == '1') {echo "selected";} else {echo "";} ?> >CREATO</option>' +
				'<option value="2" <?php if ($stato == '2') {echo "selected";} else {echo "";} ?> >UPLOAD</option>' +
				'<option value="3" <?php if ($stato == '3') {echo "selected";} else {echo "";} ?> >LAVORATO</option>' +
				'<option value="4" <?php if ($stato == '4') {echo "selected";} else {echo "";} ?> >PAGATO</option>' +
				'<option value="5" <?php if ($stato == '5') {echo "selected";} else {echo "";} ?> >CONSEGNATO</option>' +
				'<option value="6" <?php if ($stato == '6') {echo "selected";} else {echo "";} ?> >ANNULLATO</option>' +
				'</select>' +
				'</form>' +
				'</div>' +
				'</div>' +
				'</div>'
			);

			setAjaxFlow();
		}
		provenienza = id;
	});
</script>

<!-- GV - END -->
<script>
	$("#id_cerca").focus();
	$(document).ready(function() {



		$('#tskill').val(<?php echo $tskill ?>);
		$('#tskill').change();
	});
</script>
<!-- ********** AJAX FORM ********** -->
<script>
	function setAjaxForm() {

		$('#cerca_id').ajaxForm(

			function(value) {

				var array_ritorno = value.split(' ');

				if (array_ritorno[0] == 'NO') {
					alert('Codice utente non trovato!');
					top.location.href = "gestione_ruolo.php?mode=consulta&p=" + array_ritorno[1] + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				} else {
					top.location.href = "gestione_ruolo.php?mode=consulta&p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				}
			});
	}
</script>
<script>
	function setAjaxFlow() {
		var t = ($('#tskill').val());

		var data_result = [];

		$('#cerca_flusso').ajaxForm(

			function(value) {

				data_result = $.parseJSON(value);
				console.log(data_result);

				var message = data_result.message;

				if (message !== 'OK') {
					if (message === 'KO_DATI_NON_VALIDI') {
						alert('Numero flusso e/o Anno flusso non validi');
					}
					if (message === 'KO_OPERAZIONE_NON_VALIDA') {
						alert('OPERAZIONE NON VALIDA: PER UTILIZZARE IL FILTRO STATUS È OBBLIGATORIO RIEMPIRE ALMENO UN ALTRO CAMPO');
					}
					if (message === 'KO_DATI_INESISTENTI')
						alert('Dati non forniti');

					if (message === 'KO_FLUSSI_NON_TROVATI')
						alert('Flussi non trovato');

					top.location.href = "gestione_ruolo.php?mode=consulta&p=&c=<?php echo $c; ?>&a=<?php echo $a; ?>&tskill=2";

				} else {

					var nf = data_result.nf;
					var af = data_result.af;
					var status = data_result.status;
					var cod_catastale = data_result.cod_catastale;


					top.location.href = "gestione_ruolo.php?mode=consulta&p=&c=<?php echo $c; ?>&a=<?php echo $a; ?>&tskill=2&nf=" + nf + "&af=" + af + "&status=" + status + "&cod_catastale=" + cod_catastale;

				}

			});

		$('#cerca_flusso').on('keypress', function(e) {
			if (e.which == 13) {
				$('#cerca_flusso').submit();
			}
		});

	}
</script>




<?php include(INC . "/footer.php"); ?>