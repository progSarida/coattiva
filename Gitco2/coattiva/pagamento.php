<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php");

include(INC . "/header.php");
include(INC . "/menu.php");
$submenuPageNo = 3;
$pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';
include(INC . "/submenu_partita.php");

include(CLS . "/cls_registry.php");
include_once(CLS . "/cls_GestionePartita.php");
include_once(CLS . "/cls_DateTimeInLine.php");
include_once(CLS . "/cls_math.php");

$cls_partita = new cls_GP();
$cls_date = new cls_DateTimeI("IT", false);
$cls_mathF = new cls_math();

if (!session_id()) session_start();

if ($_SESSION['username'] == NULL) {
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$partita_ID = $cls_help->getVar('partita');

/*$comune = new ente_gestito($c);
	$nome_comune = $comune->Nome;

	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];*/

$layout = "<script>";

//$anni_gestiti = new anni_gestiti($c, null);

if ($c == null)
	$options_anni = null;
else {
	$options_anni = $cls_partita->Options_Anni_Veloci($c, "COATTIVA", "pagamento");

	if ($a != null)
		$layout .= "$('#select_anno_veloce option[value=" . $a . "]').attr('selected',true);";
}

$layout .= "</script>";

$layout .= "<script>$('[tabindex=1]').focus();</script>";

$partita = $cls_partita->getDataPartita($partita_ID, $c, $a); // new partita($partita_ID, $c, $a);

$query = "SELECT * FROM parametri_pagamento WHERE CC = '" . $c . "' AND Tipo_Riscossione = '" . $partita["Tipo"] . "'";
$parametri_pagamento = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "parametri_pagamento"); // new parametri_pagamento($c, $partita->Tipo);
$conto_terzi_CC = $parametri_pagamento["Conto_Terzi"];

$ID_Partita = $partita["Comune_ID"];

$anno_riferimento = $partita["Anno_Riferimento"];

$utente_ID = $partita["Utente_ID"];
$query = "SELECT * FROM utente WHERE ID = '" . $utente_ID . "' AND CC_Comune = '" . $c . "' LOCK IN SHARE MODE";

$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "utente"); //new utente($utente_ID,$c);

$id_utente 				= 	$utente["ID"];
$genere_utente 			= 	$utente["Genere"];
$comune_id 				=	$utente["Comune_ID"];
$cognome_utente 		=	$utente["Cognome"];
$nome_utente 			=	$utente["Nome"];
$ditta					=	$utente["Ditta"];

$percorso_dir = ATTI . "/" . $c . "/Pagamenti/";
$src_dir = ATTI_WEB . "/" . $c . "/Pagamenti/";



$quietanza = "";
$bollettario = "";
$numero_rata = "";
$tot_rate = "";
$note = "";
$importo = "";
$pagante = "";
$data_pag = "";
$modalita = "";
$immagine = "";
$src_immagine = "";
$src_immagine_2 = "";
$w_img = "";
$h_img = "";
$layout .= "<script>$('#mostra_immagine').hide();</script>";

$id_pag = 0;

$array_pagamenti = array();
$atto = isset($partita["Atto"]) ? $partita["Atto"] : null;
$atto_rif = "<option value='0'></option>";

$somma_pagamenti = array();

if ($atto != null) {
	$num_rate = $atto[count($atto) - 1]["Rate_Previste"];

	$control_selected = 0;
	for ($i = count($atto) - 1; $i >= 0; $i--) {
		$somma_pagamenti[] = $cls_partita->totale_pagamenti($atto[count($atto) - 1 - $i]["ID"], $atto[count($atto) - 1 - $i]["Partita_ID"], $c); // $atto[count($atto)-1-$i]->totale_pagamenti();
		$selected = "";

		$ctrl_rata[$atto[$i]["ID"]] = "si";
		// 			if($atto[$i]->Rate_Previste != 0)
		// 				$ctrl_rata[$atto[$i]->ID] = "si";
		// 			else
		// 				$ctrl_rata[$atto[$i]->ID] = "no";

		$mostra_atto = $atto[$i]["Atto"] . " n. " . $atto[$i]["ID_Cronologico"] . " del " . $atto[$i]["Anno_Cronologico"];

		if ($atto[$i]["ID_Cronologico"] != 0) {
			$atto_rif .= "<option " . $selected . " value='" . $atto[$i]["ID"] . "'>" . $mostra_atto . "</option>";
			
		}
	}


	$pagamento_ogg = $partita["Pagamento"];
	// echo "<h1>num: ".count($pagamento_ogg)."</h1><br>"; print_r($pagamento_ogg);
	//echo "<h1>qui 0</h1>";
	/*foreach ($pagamento_ogg as $key => $a_pagamento) {
                if ($a_pagamento['Tipo_Atto'] === "Precedenti") {
                    unset($pagamento_ogg[$key]);
                }
            }
        $pagamento_ogg = array_values($pagamento_ogg);*/
	//var_dump($pagamento_ogg);

	for ($y = count($pagamento_ogg) - 1; $y >= 0; $y--) {
		//$pagamento_ogg[$y]->crono_atto();
		//echo "<br><h1>Y -> ".$y."aa --> ".$pagamento_ogg[$y]["Atto_ID"]."</h1>";
		if (isset($pagamento_ogg[$y]["Atto_ID"])) {
			$pagamento_ogg[$y]["Cronologico_Atto"] = $cls_partita->crono_atto($pagamento_ogg[$y]);
			$array_pagamenti[] = $pagamento_ogg[$y];

			if ($y == count($pagamento_ogg) - 1 && $control_selected == 0) {
				$selected = "selected ";
				$control_selected = 1;
			}
		}
	}

	$num_pagamenti = count($array_pagamenti);
	if ($num_pagamenti > 0)
		$pag = $array_pagamenti[count($array_pagamenti) - 1];
	//echo "<h1>qui 1</h1>";
	$pagamento = $array_pagamenti;


	if (isset($pag)) {
		// 			if($num_rate!=0)
		// 				$layout .= "<script>$('#num_rata').prop('disabled',false);</script>";
		$layout .= "<script>$('#atto_rif').val(" . $pag["Atto_ID"] . ");</script>";
		$atto_rif_2 = $pag["Atto_ID"];
		$pagante = $pag["Pagante"];
		$data_pag = $cls_date->Get_DateNewFormat($pag["Data_Pagamento"], "DB");
		$telematico = $pag["Telematico"];
		$telematico_2 = "";
		if ($telematico == "Y" || $telematico == "S" || $telematico == "SI") {
			$layout .= "<script>$('#telematico').val('SI');</script>";
			$telematico_2 = "SI";
		} else if ($telematico == "N" || $telematico == "NO") {
			$layout .= "<script>$('#telematico').val('NO');</script>";
			$telematico_2 = "NO";
		}


		$modalita = $pag["Modalita"];
		$tipo_g = "";
		switch ($modalita) {
			case "Bancomat":

				$layout .= "<script>$('#tipo_1').attr('selected','selected');</script>";
				$tipo_g = "Bancomat";
				break;

			case "Bolletta":

				$layout .= "<script>$('#tipo_2').attr('selected','selected');</script>";
				$tipo_g = "Bolletta";
				break;

			case "C/C":

				$layout .= "<script>$('#tipo_3').attr('selected','selected');</script>";
				$tipo_g = "C/C";
				break;

			case "Contanti":

				$layout .= "<script>$('#tipo_4').attr('selected','selected');</script>";
				$tipo_g = "Contanti";
				break;

			case "Assegno":

				$layout .= "<script>$('#tipo_5').attr('selected','selected');</script>";
				$tipo_g = "Assegno";
				break;

			case "POS":

				$layout .= "<script>$('#tipo_6').attr('selected','selected');</script>";
				$tipo_g = "POS";
				break;

			case "Vaglia":

				$layout .= "<script>$('#tipo_7').attr('selected','selected');</script>";
				$tipo_g = "Vaglia";
				break;

			case "BPL":

				$layout .= "<script>$('#tipo_8').attr('selected','selected');</script>";
				$tipo_g = "BPL";
				break;

			case "BGSG":

				$layout .= "<script>$('#tipo_9').attr('selected','selected');</script>";
				$tipo_g = "BGSG";
				break;

			case "PAGOPA":

				$layout .= "<script>$('#tipo_10').attr('selected','selected');</script>";
				$tipo_g = "PAGOPA";
				break;
		}
		//$cls_help->alert($pag["Importo"]);
		$importo = number_format($pag["Importo"], 2, ",", ".");
		//$cls_help->alert($importo);
		$terzi_2 = "";
		if ($pag["Conto_Terzi"] == "Y") {
			$layout .= "<script>$('#terzi').prop('checked',true);</script>";
			$terzi_2 = "Y";
		}

		$quietanza = $pag["Quietanza"];
		$bollettario = $pag["Bollettario"];
		$numero_rata = $pag["Rata"];
		$tot_rate = $pag["Totale_Rate"];
		$note = $pag["Note"];

		if ($pag["Bollettino"] != "") {
			$src_immagine = $src_dir . $pag["Bollettino"];
			$src_immagine_2 = $pag["Bollettino"];
			//$cls_help->alert($src_immagine);
			$percorso_immagine = $percorso_dir . $pag["Bollettino"];

			$immagine = new Imagick($percorso_immagine);
			$d = $immagine->getImageGeometry();
			$w_img = $d['width'];
			$h_img = $d['height'];

			$layout .= "<script>$('#mostra_immagine').show();</script>";
		}


		$id_pag = $pag["ID"];

		$layout .= "<script>$('#invia_submit').val('Update');</script>";
	} else {
		$layout .= "<script>$('#invia_submit').val('Insert');nuovo_record();</script>";
	}
} else {
	$atto_rif = "<option value='0'>Nessun atto presente in lista</option>";
	$pagamento = null;
	$ctrl_rata[0] = null;

	$layout .= "<script>$('#invia_submit').val('Insert');nuovo_record();</script>";
}

$nuovo_pag = $cls_help->getVar('nuovo_pag');
if ($nuovo_pag == true) {
	$quietanza = "";
	$bollettario = "";
	$numero_rata = "";
	$tot_rate = "";
	$note = "";
	$importo = "";
	$pagante = "";
	$data_pag = "";
	$modalita = "";
	$immagine = "";
	$src_immagine = "";
	$id_pag = 0;

	$layout .= "<script>$('#mostra_immagine').hide();nuovo_record();</script>";
}

$prev = $partita["prev"];
$next = $partita["next"];

//echo "<h1>".$src_immagine."</h1>";
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>
	//F2
	function cambia_F2() {
		return true;
	}


	//F3
	switchMenuImg("F3");
	F3_button = function() {
		//alert($('#invia_submit').val());
		if (verificaPath()) {
			if (confirm("Nome file già esistente, sei sicuro di volerlo sovrascrivere?")) {
				control = submit_buttons($('#invia_submit').val());
				if (control) {
					$("#img_bollettino_2").val("Y");
					$("#btnSub").trigger("click");
				}
			}
		} else {
			control = submit_buttons($('#invia_submit').val());
			if (control) {
				var fileName = "";
				if(document.getElementById('immaginePag').value != "") 
					fileName = document.getElementById('immaginePag').files[0].name;
					
				if (fileName != "") {
					$("#img_bollettino_2").val("Y");
				} else {
					$("#img_bollettino_2").val("N");
				}
				$("#btnSub").trigger("click");
			}
		}

	}

	//F4
	switchMenuImg("F4");
	F4_button = function() {
		control = submit_buttons('Delete');
		if (control)
			$("#btnSub").trigger("click");
	}

	//F5
	switchMenuImg("F5");
	F5_button = function() {
		location.href = "pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}

	//F6
	switchMenuImg("F6");
	F6_button = function() {
		if (modifica == 0) {
			location.href = "pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&nuovo_pag=true";
		} else
			alert("salvare i dati o annullare prima di procedere");
	}

	//F7
	//switchMenuImg("F7");
	F7_button = function() {
		if (modifica == 0) {
			value = "<?php echo $prev; ?>";
			location.href = "pagamento.php?partita=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		} else
			alert("salvare i dati o annullare prima di procedere");
	}

	//F8
	//switchMenuImg("F8");
	F8_button = function() {
		if (modifica == 0) {
			value = "<?php echo $next; ?>";
			location.href = "pagamento.php?partita=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		} else
			alert("salvare i dati o annullare prima di procedere");;
	}

	//PAG GIU
	//switchMenuImg("pagedown");
	pagedown_button = function() {
		if (modifica == 0) {
			location.href = "ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		} else
			alert("salvare i dati o annullare prima di procedere");
		//location.href="ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}

	//PAG SU
	//switchMenuImg("pageup");
	pageup_button = function() {
		if (modifica == 0) {
			location.href = "scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		} else
			alert("salvare i dati o annullare prima di procedere");
	}

	//F9
	function ricerca_F9() {

		if (modifica == 0) {
			RicercheDaId('utente', 0);
            openOfcanvas('user_entry',0);
		} else
			alert("salvare i dati o annullare prima di procedere");

	}


	//F11-F12 sono nel menu'


	//******************************\\
	//ALTRI LINK / FUNZIONI CHIAMATE\\
	function ruolo(value) {
		location.href = "gestione_ruolo.php?p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}

	function crea_partita() {
		top.location.href = "nuova_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
</script>


<!-- ********** CALENDARIO ********** -->
<script>
	$(document).ready(function() {

		$('#data_pag').datepicker();

	});
</script>


<!-- ********** ARRAY PHP ********** -->
<script>
	var conto_terzi_CC = "<?php echo $conto_terzi_CC; ?>";


	var id_pagamento = new Array();
	var data_pagamento = new Array();
	var pagante = new Array();
	var tipo = new Array();
	var telematico = new Array();
	var importo = new Array();
	var quietanza = new Array();
	var bollettario = new Array();
	var rata = new Array();
	var terzi = new Array();
	var note = new Array();
	var bollettino = new Array();
	var boll_w = new Array();
	var boll_h = new Array();
	var cronologico_atto = new Array();
	var id_atto = new Array();

	<?php

	$count = isset($pagamento) ? count($pagamento) : 0;

	for ($y = 0; $y < $count; $y++) {

		$w_img_cur = 0;
		$h_img_cur = 0;
		if ($pagamento[$y]["Bollettino"] != "") {
			$immagine_current = new Imagick($percorso_dir . $pagamento[$y]["Bollettino"]);
			$dim_img_cur = $immagine_current->getImageGeometry();
			$w_img_cur = $dim_img_cur['width'];
			$h_img_cur = $dim_img_cur['height'];
		}
	?>
		id_pagamento[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["ID"]; ?>";
		data_pagamento[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($pagamento[$y]["Data_Pagamento"], "DB"); ?>";
		pagante[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Pagante"]; ?>";
		tipo[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Modalita"]; ?>";
		<?php
		if ($pagamento[$y]["Telematico"] == "Y" || $pagamento[$y]["Telematico"] == "S" || $pagamento[$y]["Telematico"] == "SI")
			$telematico_js = "SI";
		else
			$telematico_js = "NO";
		?>
		telematico[<?php echo $y; ?>] = "<?php echo $telematico_js; ?>";
		importo[<?php echo $y; ?>] = "<?php echo number_format($pagamento[$y]["Importo"], 2, ",", "."); ?>";
		quietanza[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Quietanza"]; ?>";
		bollettario[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Bollettario"]; ?>";
		rata[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Rata"]; ?>";
		terzi[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Conto_Terzi"]; ?>";
		note[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Note"]; ?>";
		bollettino[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Bollettino"]; ?>";
		boll_w[<?php echo $y; ?>] = "<?php echo $w_img_cur ?>";
		boll_h[<?php echo $y; ?>] = "<?php echo $h_img_cur ?>";
		cronologico_atto[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Cronologico_Atto"]; ?>";
		id_atto[<?php echo $y; ?>] = "<?php echo $pagamento[$y]["Atto_ID"]; ?>";

	<?php

	}
	?>

	var ctrl_rata = new Array();

	<?php
	for ($y = 0; $y < count($ctrl_rata); $y++) {
		$chiavi_rata = array_keys($ctrl_rata);
	?>

		ctrl_rata[<?php echo $chiavi_rata[$y]; ?>] = "<?php echo $ctrl_rata[$chiavi_rata[$y]]; ?>";

	<?php
	}
	?>
</script>


<!-- ********** AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>
	function dettagli_pag(value) {
		$(".allRow").css("color", "");
		$("#riga_" + value).css("color", "red");

		$('#id_pagamento').val(id_pagamento[value]);

		$('#data_pag').val(data_pagamento[value]);
		$('#data_pag_2').val(data_pagamento[value]);

		$('#pagante').val(pagante[value]);
		$('#pagante_2').val(pagante[value]);

		$('#telematico').val(telematico[value]);
		$('#telematico_2').val(telematico[value]);

		$('#tipo').val(tipo[value]);
		$('#tipo_g').val(tipo[value]);

		$('#importo').val(importo[value]);
		$('#importo_2').val(importo[value]);

		$('#quietanza').val(quietanza[value]);
		$('#quietanza_2').val(quietanza[value]);

		$('#bollettario').val(bollettario[value]);
		$('#bollettario_2').val(bollettario[value]);


		if (terzi[value] == "Y")
		{
			$('#terzi').prop('checked', true);
			$('#terzi_2').val("Y");
		}
		else
		{
			$('#terzi').prop('checked', false);
			$('#terzi_2').val("");
		}


		$('#note').val(note[value]);
		$('#note_2').val(note[value]);

		$('#invia_submit').val('Update');

		$('#num_rata').val(rata[value]);
		$('#num_rata_2').val(rata[value]);

		if (rata[value] == "Unica") $('#num_rata').prop('disabled', true);
		else $('#num_rata').prop('disabled', false);
		
		
		$('#atto_rif').val(id_atto[value]);
		$('#atto_rif_2').val(id_atto[value]);

		$("#img_bollettino_2").val(bollettino[value]);

		if (bollettino[value] != "") {
			$('#mostra_immagine').show();
			$('#mostra_immagine').attr('onclick', "window.open('<?php echo $src_dir ?>" + bollettino[value] + "')");
			$('#thumbnail_image2').attr('src', "<?php echo $src_dir ?>" + bollettino[value]);
			$('#thumbnail_image2').ready(dimensiona_magnify("2", boll_w[value], boll_h[value], 175, 110));
		} else {
			$('#mostra_immagine').hide();
		}
	}

	function verificaPath() {
		// Open a log file
		/*var myLog = new File([""],"<?= $percorso_dir; ?>"+bollettino[indiceAttuale]);

// See if the file exists
    if(myLog.exists()){
        alert('The file exists');
    }else{
        alert('The file does not exist');
    }*/
		if (document.getElementById('immaginePag').value != "") var fileName = document.getElementById('immaginePag').files[0].name;
		else return false;
		if (fileName == "") return false;

		var splitFile = fileName.split('.');
		var fileName = "";
		if (splitFile.length > 2) {
			for (var i = 0; i < splitFile.length - 1; i++)
				fileName += splitFile[i] + ".";
			fileName += "jpg";
		} else fileName = splitFile[0] + ".jpg";

		var xhr = new XMLHttpRequest();
		xhr.open('HEAD', "<?php echo $src_dir ?>" + fileName, false);
		xhr.send();

		if (xhr.status == "404") {
			return false;
		} else {
			return true;
		}
	}

	function nuovo_record() {
		$('#id_pagamento').val('');
		$('#data_pag').val('');
		$('#pagante').val('');
		$('#tipo').val('');
		$('#importo').val('');
		$('#quietanza').val('');
		$('#bollettario').val('');
		$('#num_rata').val('');

		$('#note').val('');
		$('#invia_submit').val('Insert');

		if (conto_terzi_CC == "si")
			$('#terzi').prop('checked', true);
		else
			$('#terzi').prop('checked', false);

		$('#atto_rif').val(0);

	}

	function change_atto() {
		atto_selezionato = $('#atto_rif').val();
		if (ctrl_rata[atto_selezionato] == "si") {
			$('#num_rata').prop('disabled', false);
			$('#num_rata').val('');
		} else {
			$('#num_rata').prop('disabled', true);
			$('#num_rata').val('Unica');
		}
	}

	function focus_index() {
		$('[tabindex=1]').focus();
	}
</script>


<!-- ********** MODALI ********** -->

<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>

<script>
    // Apertura modale di ricerca utente/partita su F9
    function openOfcanvas(type,rif){
        // Reset campi input
        $('.user_entry').val("");
        // Reset input non necessari
        /*
        $('#desc').val("");
        $('#year').val("");
        $('#name').val("");
        $('#cf').val("");
        $('#ricDesc').val("");
        $('#ricCode').val("");
        */

        // Reset spazi tabella
        $('#appendTableUserEntry').empty();
        // Reset spazi tabella non necessari
        /*
        $('#appendTableRole').empty();
        $('#appendTableOwner').empty();
        $('#appendTableCode').empty();
        */

        selectRif = rif;
        switch (type){
            case 'user_entry':
                // Setta stato checkbox iniziale => tipo di ricerca iniziale
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
            // case non utilizzati
            /*
            case 'role':
                if(numero_atti>0)
                {
                    alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
                    return false;
                }
                //role_S = "desc";
                $("#ins_year").hide();
                $("#ins_desc").show();
                document.getElementById('check_desc').checked = true;
                document.getElementById('check_year').checked = false;
                $('#roleSearchModal').modal('show');
                break;
            case 'owner':
                //owner_S = "name";
                $("#ins_cf").hide();
                $("#ins_name").show();
                document.getElementById('check_name').checked = true;
                document.getElementById('check_cf').checked = false;
                $('#ownerSearchModal').modal('show');
                break;
            case 'list':
                $('#ListModal').modal('show');
                startAjax('list');
                break;
            case 'code':
                //code_S = "c_desc";
                $("#ins_code").hide();
                $("#ins_desc_c").show();
                document.getElementById('check_desc_code').checked = true;
                document.getElementById('check_code').checked = false;
                $('#codeSearchModal').modal('show');
                break;
            */
        }
    }

    function initialId(type,val){
        switch (type){
            // case non utilizzati
            /*
            // Inserimento Ruolo
            case "role_d":
            case "role_y":
                $('#ruolo').val(val["ID"]);
                $('#ruolo_desc').val(val["Descrizione"]);
                document.getElementById("ruolo_desc").dispatchEvent(new Event("change"));
                break;
            // Inserimento Intestatario
            case "owner_n":
            case "owner_cf":
                $('#utente').val(val["ID"]);
                $('#utente_nome').val(val["Ins"]);
                document.getElementById("utente_nome").dispatchEvent(new Event("change"));
                break;
            // Inserimento Codice Tributo
            case "code_d":
            case "code_n":
                //console.log(val);
                if(selectRif!="new")
                {
                    //alert(selectRif);
                    $('#cod_tributo_'+selectRif).val(val["Codice_Tributo"]);
                    $('#tipo_trib_'+selectRif).text(val["Descrizione"]);
                    // document.getElementById('#cod_tributo_'+selectRif).dispatchEvent(new Event("change"));
                    // document.getElementById('#cod_tributo_'+selectRif).dispatchEvent(new Event("change"));
                }
                else
                {
                    $('#tipo_trib_new').text(val["Descrizione"]);
                    $('#cod_tributo_new').val(val["Codice_Tributo"]);
                    //document.getElementById("cod_tributo_new").dispatchEvent(new Event("change"));
                }
                break;
            */
            // Inserimento dati utente in 'Gitco2/coattiva/gestione_ruolo.php'
            case "user":
            case "cf":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                break;
            // Inserimento dati partita in 'Gitco2/coattiva/pagamento.php'
            case "info":
            case "entry":
            case "fore":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/pagamento.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento'];
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

	function callParent(valorediritorno) {
		if (valorediritorno != null) {
			switch (selectParent) {
				case "utente":

					if (typeof valorediritorno !== 'string')
						reopen('obj', valorediritorno);
					else
						reopen('str', valorediritorno);

					break;
			}
		}
	}

	function reopen(type, value) {
		if (type == 'obj')
			top.location.href = "../pagamento.php?mode=consulta&partita=" + value.ID + "&c=<?php echo $c; ?>&a=" + value.Anno;
		else if (type == 'str')
			top.location.href = "../gestione_ruolo.php?mode=consulta&p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}

	var selectParent = "";
	var selectRif = "";

	function RicercheDaId(value, rif) {
		selectParent = value;
		selectRif = rif;
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

</script>


<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>
	var invio_form = "Update";

	$(document).ready(function() {

		dimensiona_magnify("2", "<?php echo $w_img; ?>", "<?php echo $h_img; ?>", 175, 110);


		/*$('#cerca_id').ajaxForm(

			        function(value) {
			            var array_ritorno = value.split(' ');
				if(array_ritorno[0]=='NO')
				{
					alert('Codice partita non trovato!');
		            annulla();
				}
				else
				{
					top.location.href = "gestione_partita.php?partita="+array_ritorno[0]+"&c=<?php echo $c; ?>&a="+array_ritorno[1];
				}
			});*/

		/*$('#form_pagamento').ajaxForm(

		    function(value) {
		        var array_ritorno = value.split(' ');

			if(array_ritorno[0]=='OK')
			{
				alert('Salvataggio effettuato correttamente!');
				top.location.href = "pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			}
			else if(array_ritorno[0]=='ERROR')
			{
				alert("Errore nel salvataggio dell'ingiunzione.");
				top.location.href = "pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			}
			else if(array_ritorno[0]=='DELETE')
			{
				alert("Pagamento eliminato correttamente.");
				top.location.href = "pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			}
			else
				alert(value);

		});*/


	});
</script>

<?php include_once(INC . "/pages_authorization.php");?>

<form id=form_pagamento class="form-horizontal validate" name=form_pagamento action="pagamento_salva.php" method=post enctype="multipart/form-data">
	<input type=hidden name=c value=<?php echo $c; ?>>
	<input type=hidden name=a value=<?php echo $a; ?>>
	<input type=hidden name=p value=<?php echo $p; ?>>
	<input type=hidden name=partita value=<?php echo $partita_ID; ?>>
	<input name=invia_submit id=invia_submit type=hidden>
	<input name=id_pagamento id=id_pagamento type=hidden value="<?php echo $id_pag; ?>">
	<input type=hidden name="num_pagamento">

	<?php if (isset($pagamento)) { ?>

		<div style="overflow-y: auto; max-height: 15vh !important;">
			<table class="text_center table_interna" cellspacing=0 border=0 style="border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">
				<thead>
					<tr class="text_left riga_dispari" style="height:30px;">
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width4"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="text_center width6"><b>Rata</b></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="text_center width14"><b>Cronologico</b></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="text_left width21"><b>Atto</b></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="text_center width15"><b>Tot. dovuto (&euro;)</b></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="text_center width18"><b>Data Pagamento</b></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="text_center width18"><b>Data registrazione</b></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="text_center width15"><b>Pagato (&euro;)</b></td>
						<td style="position: sticky; top: 0; background-color: #6B8BFF;" class="width1"><br></td>
					</tr>
					<thead>
					<tbody>
						<?php

						for ($i = count($pagamento) - 1; $i >= 0; $i--) {
							$y = count($pagamento) - 1 - $i;

							if ($y++ % 2) {
								$stile_riga = 'class="riga_dispari text_left allRow"';
							} else {
								$stile_riga = 'class="riga_pari text_left allRow"';
							}

							if ($pagamento[$i]["Totale_Rate"] == 0)
								$rata_pag = $pagamento[$i]["Rata"];
							else
								$rata_pag = $pagamento[$i]["Rata"] . " / " . $pagamento[$i]["Totale_Rate"];

							//$cls_help->alert($pagamento[$i]["Dovuto"]);
							if ($pagamento[$i]["Dovuto"] != null)
								$dovuto = number_format($pagamento[$i]["Dovuto"], 2, ",", ".");
							else
								$dovuto = "";

							//$cls_help->alert($dovuto);
						?>


							<tr <?php echo $stile_riga; ?> id="riga_<?= $i; ?>" style="<?= $i == count($pagamento) - 1 ? "color: red;" : ""; ?>">
								<td class="width4">
									<a onMouseover="title='Dettagli Pagamento'" href="#" onclick="dettagli_pag('<?php echo $i; ?>');" style="text-decoration: none;">
										<img src="<?= IMMAGINIWEB; ?>/select.png" width=25 height=25 border=0>
									</a>
								</td>
								<td class="width1"><br></td>
								<td class="text_center"><?php echo $rata_pag; ?></td>
								<td><br></td>
								<td class="text_center"><?php echo $pagamento[$i]["Cronologico_Atto"]; ?></td>
								<td><br></td>
								<td class="text_left"><?php echo substr($pagamento[$i]["Tipo_Atto"], 0, 21); ?></td>
								<td><br></td>
								<td class="text_center"><?php echo $dovuto; ?></td>
								<td><br></td>
								<td class="text_center"><?php echo $cls_date->Get_DateNewFormat($pagamento[$i]["Data_Pagamento"], "DB"); ?></td>
								<td><br></td>
								<td class="text_center"><?php echo $cls_date->Get_DateNewFormat($pagamento[$i]["Data_Registrazione"], "DB"); ?></td>
								<td><br></td>
								<td class="text_center"><?php echo number_format($pagamento[$i]["Importo"], 2, ",", "."); ?></td>
								<td><br></td>
							</tr>

						<?php } ?>
					</tbody>
			</table>
		</div>
	<?php
	}
	?>
	<div class="row" style="margin-top: 2%;">
		<div class="col-lg-8 col-lg-offset-1">

			<div class="row">
				<div class="col col-lg-12">
					<div class="form-group">
						<label class="col-lg-3 control-label resize" style="text-align: left;">Atto di riferimento</label>
						<div class="col-lg-9">
							<select class="form-control resize vld_req" tabindex=2 id=atto_rif name=atto_rif onchange="change_atto();">
								<?php echo $atto_rif; ?>
							</select>
							<input type="hidden" name=atto_rif_2 id=atto_rif_2 value="<?php echo $atto_rif_2; ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="row" style="margin-top: 1%;">
				<div class="col col-lg-6">
					<div class="form-group">
						<label class="col-lg-6 control-label resize" style="text-align: left;">Pagamento eseguito da:</label>
						<div class="col-lg-6">
							<input tabindex=3 type="text" class="form-control resize" name=pagante id=pagante value="<?php echo $pagante; ?>" size=25>
							<input type="hidden" name=pagante_2 id=pagante_2 value="<?php echo $pagante; ?>">
						</div>
					</div>
				</div>
				<div class="col col-lg-6">
					<div class="form-group">
						<label class="col-lg-5 control-label resize" style="text-align: left;">Data pagamento</label>
						<div class="col-lg-7">
							<input tabindex=4 type="text" class="form-control resize" name=data_pag id=data_pag value="<?php echo $data_pag; ?>" size=9>
							<input type="hidden" name=data_pag_2 id=data_pag_2 value="<?php echo $data_pag; ?>">
						</div>
					</div>
				</div>
			</div>



			<div class="row" style="margin-top: 1%;">
				<div class="col col-lg-6">
					<div class="form-group">
						<div class=" col-lg-6">
							<label>
								Modalita' &nbsp; C/o terzi &nbsp;<input tabindex=5 type=checkbox class=" resize" id=terzi name=terzi value='Y'>
								<input type="hidden" name="terzi_2" id="terzi_2" value="<?php echo $terzi_2; ?>">
							</label>
						</div>
						<!--<label class="col-lg-2 control-label resize" style="text-align: left;">Modalita' &nbsp; C/o terzi</label>
					<div class="col-lg-2"><input tabindex=5 type=checkbox class="form-control resize" id=terzi name=terzi value='Y'></div>-->
						<div class="col-lg-6">
							<select id=tipo name=tipo class="form-control resize" tabindex=6>
								<option></option>
								<option id=tipo_1>Bancomat</option>
								<option id=tipo_2>Bolletta</option>
								<option id=tipo_3>C/C</option>
								<option id=tipo_4>Contanti</option>
								<option id=tipo_5>Assegno</option>
								<option id=tipo_6>POS</option>
								<option id=tipo_7>Vaglia</option>
								<option id=tipo_8>BPL</option>
								<option id=tipo_9>BGSG</option>
								<option id=tipo_10>PAGOPA</option>
							</select>
							<input type="hidden" name=tipo_g id=tipo_g value="<?php echo $tipo_g ?>">
						</div>
					</div>
				</div>
				<div class="col col-lg-4">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Importo</label>
						<div class="col-lg-8">
							<input class="form-control resize corrige_numero vld_dec" style="width: 65%;" tabindex=7 type="text" id=importo name=importo value="<?php echo $importo; ?>" size=6>
							<input class="form-control resize corrige_numero vld_dec" style="width: 65%;" tabindex=7 type="hidden" id=importo_2 name=importo_2 value="<?php echo $importo; ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="row" style="margin-top: 1%;">
				<div class="col col-lg-6">
					<div class="form-group">
						<label class="col-lg-6 control-label resize" style="text-align: left;">Quietanza</label>
						<div class="col-lg-6">
							<div class="col-lg-6" style="margin-right: 0; padding-right: 0;margin-left: 0; padding-left: 0;"> <input class="form-control resize" tabindex=8 type="text" id=quietanza name=quietanza value="<?php echo $quietanza; ?>" size=10> </div>
							<div class="col-lg-6" style="margin-right: 0; padding-right: 0;margin-left: 0; padding-left: 0;"> <input type="hidden" id=quietanza_2 name=quietanza_2 value="<?php echo $quietanza; ?>"> </div>
							<div class="col-lg-6" style="margin-left: 0; padding-left: 0;margin-right: 0; padding-right: 0;"> <input class="form-control resize" tabindex=9 type="text" id=bollettario name=bollettario value="<?php echo $bollettario; ?>" size=7> </div>
							<div class="col-lg-6" style="margin-left: 0; padding-left: 0;margin-right: 0; padding-right: 0;"> <input type="hidden" id=bollettario_2 name=bollettario_2 value="<?php echo $bollettario; ?>"> </div>
						</div>
					</div>
				</div>
				<div class="col col-lg-4">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">N. Rata</label>
						<div class="col-lg-8">
							<input class="form-control resize" tabindex=10 type="text" id=num_rata name=num_rata style="width: 65%;" value="<?php echo $numero_rata; ?>" size=6>
							<input type="hidden" id=num_rata_2 name=num_rata_2 value="<?php echo $numero_rata; ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="row" style="margin-top: 1%;">
				<div class="col col-lg-6">
					<div class="form-group">
						<label class="col-lg-6 control-label resize" style="text-align: left;">Telematico</label>
						<div class="col-lg-6">
							<select id=telematico class="form-control resize" name=telematico tabindex=11 style="width: 70%;">
								<option>NO</option>
								<option>SI</option>
							</select>
							<input type="hidden" id="telematico_2" name="telematico_2" value="<?php echo $telematico_2; ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="row" style="margin-top: 1%;">
				<div class="col col-lg-12">
					<div class="form-group">
						<label class="col-lg-3 control-label resize" style="text-align: left;">Note</label>
						<div class="col-lg-9">
							<textarea class="form-control resize" style="max-width: 100%;" id=note tabindex=12 name=note rows=1% onblur="focus_index();"><?php echo $note; ?></textarea>
							<input type="hidden" id="note_2" name="note_2" value="<?php echo $note; ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="row" style="margin-bottom: 3%;margin-top: 1%;">
				<div class="col col-lg-12">
					<div class="form-group">
						<label class="col-lg-3 control-label resize" style="text-align: left;">Carica immagine</label>
						<div class="col-lg-9">
							<input class="form-control resize" type="file" id="immaginePag" name="img_bollettino" style="width: 100%; background-color: rgb(153, 204, 255);" value="Carica immagine">
							<input type="hidden" id="img_bollettino_2" name="img_bollettino_2" value="<?php echo $src_immagine_2; ?>">
						</div>
					</div>
				</div>
			</div>

		</div>


		<div class="col-lg-2">

			<div class="row" style="margin-top: 62%;">
				<div class="col-lg-12">
					<div id=mostra_immagine class="image-magnify2" title="Clicca per allargare immagine" onclick="window.open('<?php echo $src_immagine; ?>')">
						<!-- QUESTO DA TENERE FORSE SI PIUò FARE FLOAT RIGHT E LE ROW FLOAT LEFT -->
						<div class="thumbnail2 text_center">
							<img id="thumbnail_image2" src="<?php echo $src_immagine; ?>">
							<div class="popup2"></div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>







	<?php
	if (isset($atto))
		for ($i = 0; $i < count($atto); $i++) {
			$crono_atto = "[ " . $atto[$i]["Atto"] . " n. " . $atto[$i]["ID_Cronologico"] . " del " . $atto[$i]["Anno_Cronologico"] . " ]";
			$pagato_atto = "Pagamento di " . number_format($somma_pagamenti[$i], 2, ",", ".") . " &euro; su " . number_format($atto[$i]["Totale_Dovuto"], 2, ",", ".") . " &euro;";
			if ($i == 0) {
	?>
			<table class="table_interna text_center" border="0">
			<?php }
			if ($atto[$i]["ID_Cronologico"] != 0) { ?>

				<div class="row">
					<div class="col col-lg-3 col-lg-offset-1">
						<div class="form-group">
							<label class="col-lg-6 control-label resize" style="text-align: left;">
								<font class="color_titolo font_bold">Pagamento di </font>
							</label>
							<div class="col-lg-6">
								<input class="form-control resize readonly" style="text-align: right;border: 2px solid black; background-color: rgb(153, 204, 255);" value="<?php echo number_format($somma_pagamenti[$i], 2, ",", "."); ?>" size=5 readonly>
							</div>
						</div>
					</div>
					<div class="col col-lg-1">
						<div class="form-group">
							<!--<label class="col-lg-4 control-label resize" style="text-align: left;">Note</label>-->
							<div class="col-lg-12">
								<p style=" text-align: center;">&euro;&nbsp;<font class="color_titolo font_bold"> su</font>
								</p>
							</div>
						</div>
					</div>
					<div class="col col-lg-2">
						<div class="form-group">
							<!--<label class="col-lg-4 control-label resize" style="text-align: left;">Note</label>-->
							<div class="col-lg-12">
								<input class="form-control resize readonly" style="text-align: right;width: 70%; border: 2px solid black; background-color: rgb(153, 204, 255);" value="<?php echo number_format($atto[$i]["Totale_Dovuto"], 2, ",", "."); ?>" size=5 readonly>
							</div>
						</div>
					</div>
					<div class="col col-lg-4">
						<div class="form-group">
							<!--<label class="col-lg-4 control-label resize" style="text-align: left;">Note</label>-->
							<div class="col-lg-12">
								<b style="font-size: 90%; ">&euro; <font class="color_titolo font_bold"><?php echo $crono_atto; ?></font></b>
							</div>
						</div>
					</div>
				</div>

				<!--<tr>
		<td class="text_left" colspan=2>
			<font class="color_titolo font_bold">Pagamento di </font>
			<input class="text_right readonly" value="<?php echo number_format($somma_pagamenti[$i], 2, ",", "."); ?>" size=5 readonly> &euro;
			<font class="color_titolo font_bold">su </font>
			<input class="text_right readonly" value="<?php echo number_format($atto[$i]["Totale_Dovuto"], 2, ",", "."); ?>" size=5 readonly> &euro;
			<font class="color_titolo font_bold"><?php echo $crono_atto; ?></font>
		</td>
	</tr>-->
		<?php
			}
		} ?>





		<!--<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left">Atto di riferimento</td>
		<td class="text_left" colspan=3>
			<select class=width100 tabindex=2 id=atto_rif name=atto_rif onchange="change_atto();">
				<?php echo $atto_rif; ?>
			</select>
		</td>
		<td class="text_left width25"></td>
	</tr>
	<tr>
		<td class="text_left width23" >Pagamento eseguito da:</td>
		<td class="text_left width25"><input tabindex=3 type="text" class="text_left" name=pagante id=pagante value="<?php echo $pagante; ?>" size=25></td>
		<td class="text_left width16">Data pagamento</td>
		<td class="text_left width11"><input tabindex=4 type="text" class="text_center" name=data_pag id=data_pag value="<?php echo $data_pag; ?>" size=9></td>
		<td class="text_left width25" rowspan=5>
		<div id=mostra_immagine class="image-magnify" title="Clicca per allargare immagine" onclick="window.open('<?php echo $src_immagine; ?>')">
				<div class="thumbnail text_center">
					<img id="thumbnail_image" src="<?php echo $src_immagine; ?>">
					<div class="popup"></div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="text_left">Modalita' &nbsp; C/o terzi &nbsp;<input tabindex=5 type=checkbox id=terzi name=terzi value='Y'></td>
		<td class="text_left">
			<select id=tipo name=tipo tabindex=6>
				<option></option>
				<option id=tipo_1>Bancomat</option>
				<option id=tipo_2>Bolletta</option>
				<option id=tipo_3>C/C</option>
				<option id=tipo_4>Contanti</option>
				<option id=tipo_5>Assegno</option>
				<option id=tipo_6>POS</option>
				<option id=tipo_7>Vaglia</option>
				<option id=tipo_8>BPL</option>
				<option id=tipo_9>BGSG</option>
			</select>
		</td>
		<td class="text_left">Importo</td>
		<td class="text_left">
			<input class="text_right corrige_numero" tabindex=7 type="text" id=importo name=importo value="<?php echo $importo; ?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="text_left">Quietanza</td>
		<td class="text_left">
			<input class="text_right" tabindex=8 type="text" id=quietanza name=quietanza value="<?php echo $quietanza; ?>" size=10>
			<input class="text_left" tabindex=9 type="text" id=bollettario name=bollettario value="<?php echo $bollettario; ?>" size=7>
		</td>
		<td class="text_left">N. Rata</td>
		<td class="text_left"><input class="text_right" tabindex=10 type="text" id=num_rata name=num_rata value="<?php echo $numero_rata; ?>" size=6></td>
	</tr>
	<tr>
		<td class="text_left">Telematico</td>
		<td class="text_left">
			<select id=telematico name=telematico tabindex=11>
				<option >NO</option>
				<option >SI</option>
			</select>
		</td>
		<td class="text_left"></td>
		<td class="text_left"></td>
	</tr>
	<tr>
		<td class="text_left">Note</td>
		<td class="text_left" colspan=3><textarea class="width99" id=note tabindex=12 name=note rows=1% onblur="focus_index();"><?php echo $note; ?></textarea></td>
	</tr>
	<tr>
		<td class="text_left">Carica immagine</td>
		<td class="text_left" colspan=3><input class="button_azzurro width100" type="file" name="img_bollettino" value="Carica immagine"></td>
	</tr>
	<tr>
		<td class="text_left" colspan=5><hr></td>
	</tr>
</table>-->

		<?php
		/*for($i=0; $i<count($atto); $i++)
{
	$crono_atto = "[ ".$atto[$i]["Atto"]." n. ".$atto[$i]["ID_Cronologico"]." del ".$atto[$i]["Anno_Cronologico"]." ]";
	$pagato_atto = "Pagamento di ".$cls_mathF->conv_num(number_format($somma_pagamenti[$i],2))." &euro; su ".$cls_mathF->conv_num(number_format($atto[$i]["Totale_Dovuto"],2))." &euro;";
if($i==0){
?>
<table class="table_interna text_center" border="0">
<?php }
if($atto[$i]["ID_Cronologico"]!=0){?>
	<tr>
		<td class="text_left" colspan=2>
			<font class="color_titolo font_bold">Pagamento di </font>
			<input class="text_right readonly" value="<?php echo $cls_mathF->conv_num(number_format($somma_pagamenti[$i],2)); ?>" size=5 readonly> &euro;
			<font class="color_titolo font_bold">su </font>
			<input class="text_right readonly" value="<?php echo $cls_mathF->conv_num(number_format($atto[$i]["Totale_Dovuto"],2)); ?>" size=5 readonly> &euro;
			<font class="color_titolo font_bold"><?php echo $crono_atto; ?></font>
		</td>
	</tr>
<?php
}
if($i == count($atto)-1){?>
	<tr>
		<td colspan=2><hr></td>
	</tr>
	</table>
<?php }
} */ ?>

		<div class="form-group">
			<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
		</div>

</form>

<!--</td>
</tr>
</table>-->

<?php echo $layout; ?>

<?php include(INC . "/footer.php"); ?>
<!--</body>
</html>-->