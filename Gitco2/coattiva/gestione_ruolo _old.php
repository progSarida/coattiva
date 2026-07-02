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
			<a onMouseover="title='Cerca utente/partita'" href="#" onClick="RicercheDaId('utente',0);" style="text-decoration: none;">
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
	function ricerca_F9() {
		RicercheDaId('utente', 0);
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
<script>
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

	$layout = "<script>";

	//$anni_gestiti = new anni_gestiti($c, null);

	if ($c == null)
		$options_anni = null;
	else {
		$options_anni = $cls_coat->Options_Anni_Veloci($c, "COATTIVA", "gestione_ruolo");

		if ($a != null)
			$layout .= "$('#select_anno_veloce option[value=" . $a . "]').attr('selected',true);";
	}

	$layout .= "</script>";

	//$utente = new utente($p, $c);




	$partita = $cls_coat->getDataPartita_1($p, $c);


	$query = "SELECT * FROM partita_tributi WHERE Utente_ID = '" . $p . "' AND CC = '" . $c . "' AND Is_Discharged != 0";
	$partita_bloccata = $cls_db->getResults($cls_db->ExecuteQuery($query));

?>


	<div id="sez_utente">

		<?php
		/* UTENTE SEZ. 1 */
		for ($x = 0; $x < count($partita_bloccata); $x++) {
		?>
			<div class="container">
				<div class="row" style="margin-left: 10px; margin-right:5px">
					<div class="col-sm-3 col-md-3">
						<font class="titolo font16 under_decor valign_top">
							Partita <?php echo $partita_bloccata[$x]["Comune_ID"]; ?> - <?php echo $partita_bloccata[$x]["Tipo"] . " " . $partita_bloccata[$x]["Sottotipo"] ?>
						</font>
					</div>
					<div class="col-sm-9 col-md-9">
						<b>
							<font class="color_red">Partita discaricata: D.L. 22 marzo 2021 n. 41 - Decreto MEF 14 luglio 2021.</font>
						</b>
					</div>
				</div>
			</div>
		<?php
		}
		?>

		<?php
		/* UTENTE SEZ. 2 */
		for ($i = 0; $i < count($partita); $i++) {
			if ($i == count($partita) - 1)	$blur = "onblur='focusIndex();'";
			else						$blur = "";

			if ($partita[$i]["Flag_Blocco_Coazione"] == "si") {
				$blocco = "( COAZIONE BLOCCATA )";
				$note_blocco = $partita[$i]["Note_Blocco"];
				$motivo_blocco = $partita[$i]["Motivo_Blocco"];

				if (!is_null($motivo_blocco) && ltrim($motivo_blocco, '0') !== '') {

					$causa_blocco = "SELECT  Descrizione FROM parametri_notifica where ID = " . intval($motivo_blocco);
					$motivazioni_blocco = $cls_db->getResults($cls_db->ExecuteQuery($causa_blocco));

					$motivazione_bloccata = $motivazioni_blocco[0]['Descrizione'];
				} else {
					$motivazione_bloccata = "";
				}
			} else {
				$note_blocco = "";
				$blocco = "";
				$motivazione_bloccata = "";
			}

			if (isset($partita[$i]["Tributo"][0])) {

				$tributo = $partita[$i]["Tributo"][0];

		?>
				<!-- GV - 22/04/2022 -  START -->

				<div class="container">
					<div class="row" style="margin-left: 10px; margin-right:5px">
						<div class="col-sm-3 col-md-3">
							<a tabindex=<?php echo $i + 2; ?> <?php echo $blur; ?> href="#" onMouseover="title='Seleziona la partita'" onclick="partita('<?php echo $partita[$i]["ID"]; ?>','<?php echo $partita[$i]["Anno_Riferimento"]; ?>')" style="text-decoration: none;">
								<img src="<?= IMMAGINIWEB; ?>/select.png" width=25px height=25px border="0">
								<b>
									<font class="titolo font16 under_decor valign_top">
										Partita <?php echo $partita[$i]["Comune_ID"]; ?> - <?php echo $partita[$i]["Tipo"] . " " . $partita[$i]["Sottotipo"] ?>
									</font>
								</b>
							</a>
						</div>
						<div class="col-sm-8 col-md-8 pull-right">
							<span class="color_black"><b><?php echo $tributo["Info_Cartella"]; ?></b></span>
						</div>
					</div>
					<div class="row" style="margin-left: 10px; margin-right:5px">
						<div class="col-sm-6 col-md-6 pull-right"><b id=motivazioniBlocco style="cursor: pointer;" title="<?php echo $note_blocco; ?>">
								<?php if ($partita[$i]["Flag_Blocco_Coazione"] == "si") {
								?>
									<img id="info" src="<?= IMMAGINIWEB; ?>/info.png" width=15px height=15px>
								<?php } ?>
								&nbsp;
								<font class="color_red"><?php echo $blocco; ?></font>
								<span class="color_red" style="font-size: 20px; margin-left:10px;"><b><?php echo $motivazione_bloccata; ?></b></span>
							</b>
						</div>
					</div>
				</div>

				<br />
				<?php
				if (isset($partita[$i]["Atto"]) && count($partita[$i]["Atto"]) > 0) {

					$attoPrincipale = end($partita[$i]["Atto"]);
				?>
					<div class="container">
						<div class="row">
							<div class="col-sm-10 col-md-8 col-lg-10">
								<table class="table table-hover table_interna text_center" border=0 style="border:3px solid #6D95D5; width:96%; margin-left:27px;">
									<thead>
										<tr>
											<th>Cronologico</th>
											<th>Tipo Atto</th>
											<th>Data Notifica</th>
											<th>Rate Previste</th>
											<th>Numero Pagamenti</th>
											<th>Dovuto</th>
											<th>Pagato</th>
											<th>Residuo</th>
										</tr>
									</thead>
									<tbody>
										<?php

										$rataPagata = isset($attoPrincipale['Pagamento']) ? count($attoPrincipale['Pagamento']) : 0;
										$dovuto_table = $attoPrincipale["Totale_Dovuto"] - $cls_partita->pagamenti_precedenti($attoPrincipale['ID'], $attoPrincipale['Partita_ID']);
										$pagato_table = $cls_partita->totale_pagamenti($attoPrincipale['ID'], $attoPrincipale['Partita_ID'], $c);
										$residuo_table = $dovuto_table - $pagato_table;
										echo '<tr data-toggle="collapse" data-target="#demo_' . $i . '" class="accordion-toggle">' .
											'<td >'
											.	'<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">'
											. $attoPrincipale['ID_Cronologico'] . '/' . $attoPrincipale['Anno_Cronologico']
											.	'</button>'
											. '<div class="card card-body">'
											. '</td>'
											. '<td >' . $attoPrincipale['Atto'] . '</td>'
											. '<td >' . $cls_date->Get_DateNewFormat($attoPrincipale['Data_Notifica'], "DB") . '</td>'
											. '<td >' . $attoPrincipale['Rate_Previste'] . '</td>'
											. '<td >' . $rataPagata . '</td>'
											. '<td >' . number_format($dovuto_table, 2, ",", ".") . '</td>'
											. '<td >' . number_format($pagato_table, 2, ",", ".") . '</td>'
											. '<td >' . number_format($residuo_table, 2, ",", ".") . '</td>'
											. '</tr>';
										?>
										<tr>
											<td class="hiddenRow" colspan="8">
												<div class="accordian-body collapse" id="demo_<?php echo $i ?>">
													<table class="table table_interna text_center" border=0 style="border:3px solid #6D95D5;">
														<thead>
															<tr>
																<th>Cronologico</th>
																<th>Tipo Atto</th>
																<th>Data Notifica</th>
																<th>Rate Previste</th>
																<th>Numero Pagamenti</th>
																<th>Dovuto</th>
																<th>Pagato</th>
																<th>Residuo</th>
															</tr>
														</thead>
														<tbody>
															<?php
															$arrayAtti = $partita[$i]["Atto"];
															for (($j = count($arrayAtti) - 2); $j >= 0; $j--) {

																$rataPagata = isset($arrayAtti[$j]['Pagamento']) ? count($arrayAtti[$j]['Pagamento']) : 0;
																$dovuto_table = abs($arrayAtti[$j]["Totale_Dovuto"] - $cls_partita->pagamenti_precedenti($arrayAtti[$j]['ID'], $arrayAtti[$j]['Partita_ID']));
																$pagato_table = abs($cls_partita->totale_pagamenti($arrayAtti[$j]['ID'], $arrayAtti[$j]['Partita_ID'], $c));
																$residuo_table = abs($dovuto_table - $pagato_table);
																echo '<tr>';
																echo '<td >' . $arrayAtti[$j]['ID_Cronologico'] . '/' . $arrayAtti[$j]['Anno_Cronologico'] . '</td>'
																	. '<td >' . $arrayAtti[$j]['Atto'] . '</td>'
																	. '<td >' . $cls_date->Get_DateNewFormat($arrayAtti[$j]['Data_Notifica'], "DB") . '</td>'
																	. '<td >' . $arrayAtti[$j]['Rate_Previste'] . '</td>'
																	. '<td >' . $rataPagata . '</td>'
																	. '<td >' . number_format($dovuto_table, 2, ",", ".") . '</td>'
																	. '<td >' . number_format($pagato_table, 2, ",", ".") . '</td>'
																	. '<td >' . number_format($residuo_table, 2, ",", ".") . '</td>'
																	. '</tr>';
															}

															?>
														</tbody>
													</table>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php
				} // if (isset($partita[$i]["Atto"]) && count($partita[$i]["Atto"]) > 0)

				?>
				<!-- GV - 22/04/2022 -  END   -->

				<div class="container">
					<div class="row">
						<div class="col-sm-10 col-md-8 col-lg-10">
							<div class="card">
								<font class="titolo font16 under_decor valign_top" style="margin-left:27px;"> Pignoramenti</font>
								<div class="card-body">
									<table class="table table-hover table_interna text_center" border=0 style="border:3px solid #6D95D5; width:96%; margin-left:27px;">
										<thead>
											<tr>
												<th>Cronologico</th>
												<th>Tipo</th>
												<th>Data Notifica</th>
												<th>Data Pagamento</th>
												<th>Rate Previste</th>
												<th>Numero Pagamenti</th>
												<th>Dovuto</th>
												<th>Pagato</th>
												<th>Residuo</th>
												<th>Importo</th>
											</tr>
										</thead>

										<?php
										if (isset($partita[$i]["Atto"]))
											if (isset($partita[$i]["Atto"]))
												for ($y = 0; $y < count($partita[$i]["Atto"]); $y++) {
													$atto = $partita[$i]["Atto"][$y];
													$query = "SELECT appeal.*, ESITO.Outcome, ESITO_TYPE.Description AS Merito, ESITO.Number AS Numero_Merito, ESITO.Date AS Data_Merito ";
													$query .= "FROM appeal LEFT JOIN appeal_proceedings_status AS ESITO ON ESITO.Appeal_ID=appeal.ID AND ESITO.Type=2 ";
													$query .= "LEFT JOIN appeal_proceedings_type AS ESITO_TYPE ON ESITO_TYPE.ID=ESITO.Outcome WHERE appeal.Act_ID = '" . $atto["ID"] . "' AND appeal.CC = '" . $c . "'";
													$a_appeal =  $cls_db->getResults($cls_db->ExecuteQuery($query)); //mysql_array($query);
													if ($cls_date->Get_DateNewFormat($atto["Data_Notifica"], "DB") != null)
														$notifica = "NOTIFICATO IL " . $cls_date->Get_DateNewFormat($atto["Data_Notifica"], "DB");
													else
														$notifica = "DATA DI NOTIFICA ASSENTE";

										?>


											<?php
													if (isset($atto["Pagamento"]))
														for ($k = 0; $k < count($atto["Pagamento"]); $k++) {
															$pagamento = $atto["Pagamento"][$k];
														}

													for ($k = 0; $k < count($a_appeal); $k++) {
											?>
												<tr>
													<td class="text_left width60" colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
														<b><span class=color_titolo>
																Ricorso di <?= $a_appeal[$k]['Court_Level']; ?>&deg; grado
																<?php
																if ($a_appeal[$k]['Merito'] != "") {
																?>
																	<span class="yellow"><?= strtoupper($a_appeal[$k]['Merito']); ?></span>
																<?php
																}
																?>
																registrato il <?= $cls_date->Get_DateNewFormat($a_appeal[$k]['Start_Date'], "DB"); ?>
															</span></b>
													</td>
													<td class="text_right width10"></td>
													<td class="text_right" colspan=2></td>
												</tr>
											<?php
													}

													if ($y == count($partita[$i]["Atto"]) - 1) {
											?>
												<!-- tr>
						<td colspan=5>
							<hr>
						</td>
					</tr -->
										<?php }
												}
										?>

										<?php
										if (isset($partita[$i]["Pignoramento"]))
											for ($y = 0; $y < count($partita[$i]["Pignoramento"]); $y++) {
												$pigno = $partita[$i]["Pignoramento"][$y];
												
												$rataPagataPigno = isset($pigno['Pagamento']) ? count($pigno['Pagamento']) : 0;
												$dovuto_table_Pigno = $pigno["Totale_Dovuto"];
												$residuo_table_Pigno = $pigno["Importo_Dovuto"];
												$pagato_table_Pigno = $dovuto_table_Pigno - $residuo_table_Pigno;
												
												/* if (!is_null($attoDelPigno))
												{

													$rataPagataPigno = isset($attoDelPigno['Pagamento']) ? count($attoDelPigno['Pagamento']) : 0;
													$dovuto_table_Pigno = abs($attoDelPigno["Totale_Dovuto"] - $cls_partita->pagamenti_precedenti($attoDelPigno['ID'], $attoDelPigno['Partita_ID']));
													$pagato_table_Pigno = abs($cls_partita->totale_pagamenti($attoDelPigno['ID'], $attoDelPigno['Partita_ID'], $c));
													$residuo_table_Pigno = abs($dovuto_table - $pagato_table);
												} */


												//print_r($pigno);
												// if ($cls_date->Get_DateNewFormat($pigno["Notifiche_Debitore"][0]["Data_Notifica"], "DB") != null) {
												// 	$notifica = "NOTIFICATO IL " . $cls_date->Get_DateNewFormat($pigno["Notifiche_Debitore"][0]["Data_Notifica"], "DB");
												// 	$notifica_2 = $cls_date->Get_DateNewFormat($pigno["Notifiche_Debitore"][0]["Data_Notifica"], "DB");
												// } else {
												// 	$notifica = "DATA DI NOTIFICA ASSENTE";
												// 	$notifica_2 = "DATA DI NOTIFICA ASSENTE";
												// }
												$notifica = "";
												$notifica_2 = "";
										?>
											<tr>
												<?php
												echo '<tr data-toggle="collapse" data-target="#demo_p_' . $i . '_' . $y . '" class="accordion-toggle" style="text-align: left; vertical-align: middle; ">' .
													'<td >'
													.	'<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">'
													. $pigno["ID_Cronologico"] . '/' . $pigno["Anno_Cronologico"]
													.	'</button>'
													. '<div class="card card-body">'
													. '</td>'
													. '<td >' . $pigno["Tipo"] . '</td>'
													. '<td >' . $notifica_2 . '</td>';
												if (count($pigno["Pagamento"]) > 0) {
													echo '<td >' . $cls_date->Get_DateNewFormat(end($pigno["Pagamento"])["Data_Pagamento"], "DB") . '</td>';
												} else {
													echo '<td > </td>';
												}

												echo '<td >' . $pigno["Rate_Previste"] . '</td>';
												echo '<td >' . $rataPagataPigno . '</td>';
												echo '<td >' . $dovuto_table_Pigno . '</td>';
												echo '<td >' . $pagato_table_Pigno . '</td>';
												echo '<td >' . $residuo_table_Pigno . '</td>';



												if (count($pigno["Pagamento"]) > 0) {
													echo  '<td >' . number_format(end($pigno["Pagamento"])["Importo"], 2, ",", ".") . ' &euro;</td>';
												} else {
													echo '<td > </td>';
												}
												echo '</tr>';
												?>
											</tr>
											<tr>
												<td class="hiddenRow" colspan="8">
													<div class="accordian-body collapse" id="demo_p_<?php echo $i . '_' . $y ?>">
														<table class="table table_interna text_center" border=0 style="border:3px solid #6D95D5;">
															<thead>
																<tr>
																<th>Cronologico</th>
																<th>Tipo</th>
																<th>Data Notifica</th>
																<th>Data Pagamento</th>
																<th>Rate Previste</th>
																<th>Rate Pagate</th>
																<th>Dovuto</th>
																<th>Pagato</th>
																<th>Residuo</th>
																<th>Importo</th>
																</tr>
															</thead>
															<tbody>
																<?php
																for ($k = count($pigno["Pagamento"]) - 2; $k >= 0; $k--) {
																	$pagamento = $pigno["Pagamento"][$k];
																	$data_pagamento = $cls_date->Get_DateNewFormat($pagamento["Data_Pagamento"], "DB");
																	$importo_pagamento = number_format($pagamento["Importo"], 2, ",", ".");
																	$rataPagataPigno = 0;
																	$dovuto_table_Pigno = $pigno["Totale_Dovuto"];
																	$residuo_table_Pigno = $pigno["Importo_Dovuto"];
																	$pagato_table_Pigno = $dovuto_table_Pigno - $residuo_table_Pigno;



																?>
																	<tr style="text-align: left; vertical-align: middle; ">
																		<td><?= $pigno["ID_Cronologico"] . '/' . $pigno["Anno_Cronologico"] ?></td>
																		<td><?= $pigno["Tipo"] ?></td>
																		<td><?= $notifica_2 ?></td>		
																		<td><?= $data_pagamento ?></td>
																		<td><?= $pigno["Rate_Previste"] ?></td>
																		<td><?= $rataPagataPigno ?></td>
																		<td><?= $dovuto_table_Pigno ?></td>
																		<td><?= $pagato_table_Pigno ?></td>
																		<td><?= $residuo_table_Pigno ?></td>
																		



																		<td><?= $importo_pagamento ?> &euro;</td>
																	</tr>
																<?php

																}
																?>
															</tbody>
														</table>
													</div>
												</td>
											</tr>
											<?php
												if ($y == count($partita[$i]["Pignoramento"]) - 1) {
											?>
												<!-- tr>
					<td colspan=5>
						<hr>
					</td>
				</tr -->
										<?php }
											}
										?>

									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

			<?php
			} // if (isset($partita[$i]["Tributo"][0]))
			else {
				if ($partita[$i]["Cancellazione"] == "si")
					$cancellazione = "PARTITA ANNULLATA IN FASE DI DATA ENTRY";
				else
					$cancellazione = "PARTITA VUOTA";
			?>
				<table class="table table-hover table_interna text_center" border=0 style="border:3px solid #6D95D5;">
					<tr>
						<td colspan=5>
							<hr>
						</td>
					</tr>
					<tr>
						<td class="text_left" colspan=5>
							<font class="color_red font16 font_bold"><?php echo $cancellazione; ?></font>
						</td>
					</tr>
					<tr>
						<td colspan=5>
							<hr>
						</td>
					</tr>
				</table>
			<?php
			}
			?>

			<br />
			<div class="clean_row HSpace4" style="margin-left:60px ; width: 75%; min-height: 100%;"></div>
			<br />
		<?php
		} //for ($i = 0; $i < count($partita); $i++)


		?>
	</div> <!-- sez_utente -->
	<?php echo $layout;  ?>
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