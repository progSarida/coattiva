<?php
if (!session_id()) session_start();


include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include(INC . "/header.php");
include_once(INC . "/menu.php");
include_once(CLS . "/cls_DateTimeInLine.php");
include_once(CLS . "/cls_CoazioneUtils.php");
include_once(CLS . "/cls_GestionePartita.php");

$cls_date = new cls_DateTimeI("IT", false);
$cls_coat = new cls_Coazione();
$cls_partita = new cls_GP();


if ($_SESSION['username'] == NULL) {
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

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

$utente = $cls_coat->getDataUtente($p, $c);

$partita = $cls_coat->getDataPartita_1($p, $c);




?>

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

	//F9
	function ricerca_F9() {
		RicercheDaId('utente', 0);
	}

	//F11-F12 sono nel menu'


	//******************************\\
	//ALTRI LINK / FUNZIONI CHIAMATE\\
	function partita(value, anno) {
		location.href = "gestione_partita.php?partita=" + value + "&c=<?php echo $c; ?>&a=" + anno;
	}

	function crea_partita(value) {
		location.href = "nuova_partita.php?&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}

	function anagrafe(value) {
		if (value != 0)
			location.href = "<?= WEB_ROOT; ?>/anagrafe/dati_soggetto.php?p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		else
			alert('Nessun utente � stato selezionato!');
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
				var stringa = "modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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
			top.location.href = "../gestione_partita.php?mode=consulta&partita=" + value.ID + "&c=<?php echo $c; ?>&a=" + value.Anno;
		else if (type == 'str')
			top.location.href = "../gestione_ruolo.php?mode=consulta&p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
</script>

<!-- ********** AJAX FORM ********** -->
<script>
	$(document).ready(function() {

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

	});
</script>

<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=8% class="text_center">
			<a onMouseover="title='Cerca utente/partita'" href="#" onClick="RicercheDaId('utente',0);" style="text-decoration: none;">
				<img src="<?= IMMAGINIWEB; ?>/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=15% class="text_center">
			<font class="titolo font18">RUOLO</font>
			<font class="titolo font14"></font>
		</td>
		<td colspan=5 width=55% align=center>
			<em style="background-color:rgb(251,255,208);font-style : normal ;">
				<?php if ($utente["Genere"] != 'D') {
					echo $utente["Cognome"] . " " . $utente["Nome"];
				} else {
					echo $utente["Ditta"];
				} ?></em>
		<td class="text_left"><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Anagrafe" onclick="anagrafe('<?php echo $p; ?>');">
		</td>
		<td width=22% class="text_right">
			<form id=cerca_id method=post action=modali/ricerca_codice_result.php>
				<input name=c type=hidden value='<?php echo $c; ?>'>
				<input name=a type=hidden value='<?php echo $a; ?>'>
				Utente ID &nbsp;
				<input id=id_cerca tabindex=1 class="valign_center text_right" name=ric_cod_contr value='<?php echo $utente["Comune_ID"]; ?>' size=3 onMouseover="title='Inserire il codice utente e premere Invio'">&nbsp;&nbsp;
			</form>
		</td>
	</tr>
</table>
<br>
<?php
for ($i = 0; $i < count($partita); $i++) {
	if ($i == count($partita) - 1)	$blur = "onblur='focusIndex();'";
	else						$blur = "";

	if ($partita[$i]["Flag_Blocco_Coazione"] == "si")
		$blocco = "( COAZIONE BLOCCATA )";
	else
		$blocco = "";

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
				<div class="col-sm-5 col-md-5"><span class="color_red"><b><?php echo $tributo["Info_Cartella"]; ?></b></span></div>
				<div class="col-sm-3 col-md-3"><b>
						<font class="color_red"><?php echo $blocco; ?></font>
					</b></div>
			</div>
		</div>
		<br/>
		<?php if (isset($partita[$i]["Atto"]) && count($partita[$i]["Atto"])>0){
		
				
				$attoPrincipale = end($partita[$i]["Atto"]);
				
				
			?>
			<div class="container">
				<div class="row">
					<div class="col-sm-10 col-md-8 col-lg-10">
						<table class="table table-hover table_interna text_center" border=0 style="border:3px solid #6D95D5;">
							<thead>
								<tr>
									<th>Cronologico</th>
									<th>Tipo Atto</th>
									<th>Data Notifica</th>
									<th>Rate Previste</th>
									<th>Rate Pagate</th>
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
								echo '<tr data-toggle="collapse" data-target="#demo_'.$i.'" class="accordion-toggle">' .
									'<td >'
									.	'<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">'
									. $attoPrincipale['ID_Cronologico']. '/'.$attoPrincipale['Anno_Cronologico']
									.	'</button>'
									. '<div class="card card-body"></td>'
									. '<td >' . $attoPrincipale['Atto'] . '</td>'
									. '<td >' . $cls_date->Get_DateNewFormat($attoPrincipale['Data_Notifica'], "DB") . '</td>'
									. '<td >' . $attoPrincipale['Rate_Previste'] . '</td>'
									. '<td >' . $rataPagata . '</td>'
									. '<td >' . number_format($dovuto_table, 2, ",", ".") . '</td>'
									. '<td >' . number_format($pagato_table, 2, ",", ".") . '</td>'
									. '<td >' . number_format($residuo_table, 2, ",", ".") . '</td>'
									. '</tr>
							<tr>
								<td class="hiddenRow" colspan="8">
									<div class="accordian-body collapse" id="demo_'.$i.'"> 
									<table class="table table_interna text_center" border=0 style="border:3px solid #6D95D5;"">
									<thead>
								<tr>
									<th>Cronologico</th>
									<th>Tipo Atto</th>
									<th>Data Notifica</th>
									<th>Rate Previste</th>
									<th>Rate Pagate</th>
									<th>Dovuto</th>
									<th>Pagato</th>
									<th>Residuo</th>
								</tr>
							</thead>
 									<tbody>';
								$arrayAtti = $partita[$i]["Atto"];
								for (($j=count($arrayAtti)-2); $j>=0; $j--) {
									
									$rataPagata = isset($arrayAtti[$j]['Pagamento']) ? count($arrayAtti[$j]['Pagamento']) : 0;
									$dovuto_table = abs($arrayAtti[$j]["Totale_Dovuto"] - $cls_partita->pagamenti_precedenti($arrayAtti[$j]['ID'], $arrayAtti[$j]['Partita_ID']));
									$pagato_table = abs($cls_partita->totale_pagamenti($arrayAtti[$j]['ID'], $arrayAtti[$j]['Partita_ID'], $c));
									$residuo_table = abs($dovuto_table - $pagato_table);

									echo '<td >' . $arrayAtti[$j]['ID_Cronologico']. '/'.$arrayAtti[$j]['Anno_Cronologico'] . '</td>'
										. '<td >' .$arrayAtti[$j]['Atto'] . '</td>'
										. '<td >' . $cls_date->Get_DateNewFormat($arrayAtti[$j]['Data_Notifica'], "DB") . '</td>'
										. '<td >' . $arrayAtti[$j]['Rate_Previste']. '</td>'
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
		<?php }?>
				</table>

		</div>

		<!-- GV - 22/04/2022 -  END   -->
		<div class="container">
				<div class="row">
					<div class="col-sm-10 col-md-8 col-lg-10">
					<div class="card">
					<font class="titolo font16 under_decor valign_top"> Note</font>
  <div class="card-body">
		<table class="table table-hover table_interna text_center" border=0 style="border:3px solid #6D95D5;">
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
				//print_r($pigno);
				if ($cls_date->Get_DateNewFormat($pigno["Notifiche_Debitore"][0]["Data_Notifica"], "DB") != null)
					$notifica = "NOTIFICATO IL " . $cls_date->Get_DateNewFormat($pigno["Notifiche_Debitore"][0]["Data_Notifica"], "DB");
				else
					$notifica = "DATA DI NOTIFICA ASSENTE";
		?>
			<tr>
				<td class="text_left" colspan=5>
					<b><span class=color_titolo>
							Pignoramento <?php echo $pigno["Tipo"]; ?> <?php echo "n." . $pigno["ID_Cronologico"] . " del " . $pigno["Anno_Cronologico"] . " - " . $notifica; ?>
						</span></b>
				</td>
			</tr>
			<?php
				
				for ($k = 0; $k < count($pigno["Pagamento"]); $k++) {
					$pagamento = $pigno["Pagamento"][$k];
			?>
				<tr>
					<td class="text_left width60" colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>
							<font class=color_green>
								Pagamento del <?php echo $cls_date->Get_DateNewFormat($pagamento["Data_Pagamento"], "DB"); ?> di <?php echo number_format($pagamento["Importo"], 2, ",", "."); ?> &euro;</font>
						</b></td>
					<td class="text_right width10"></td>
					<td class="text_right" colspan=2></td>
				</tr>
			<?php 	}
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

	<?php } else {
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
	<?php } ?>
	</div>
				</div>
					</div>
				</div>
		</div>
	<br/>
		<div class="clean_row HSpace4" style="margin-left:60px ; width: 75%; min-height: 100%;"></div>
		<br/>
<?php
} ?>



<script>
	focusCampo();
</script>
<?php echo $layout; ?>
<?php include(INC . "/footer.php"); ?>