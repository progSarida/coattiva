<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include(INC . "/header.php");
include_once(INC . "/menu.php");
include_once(CLS . "/cls_DateTimeInLine.php");
include_once(CLS . "/cls_anagrafeUtils.php");


$cls_date = new cls_DateTimeI("IT", false);
$cls_anagr = new cls_anagr();
//if (!session_id()) session_start();


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$mode = $cls_help->getVar('mode');
$servizio = $cls_help->getVar('servizio');
$sceltaLayout = "";

$mode = "modifica"; //ANNULLO CONSULTA

if ($mode == "consulta" || $mode == null) {
	$mode = "consulta";
	$disabled = " disabled ";
	$readonly = " readonly ";
	$class = " sfondo_readonly ";
	$class_ric = " sfondo_readonly ";
	$class_calcolo = " sfondo_readonly ";
} else {
	$mode = "modifica";
	$class_ric = " sfondo_ricerca ";
	$class = " sfondo_bianco ";
	$class_calcolo = " sfondo_verde ";
	$disabled = "";
	$readonly = "";
}


//$comune = new ente_gestito($c);
$nome_comune = $a_enteAdmin["Denominazione"];

$nome_comune = ($nome_comune == NULL ? "" : $nome_comune . " [" . $c . "]");
$nome_user = "Operatore: " . $_SESSION['username'];

if ($p == 0) {
	$submit_name = "Insert";
} else {
	$submit_name = "Update";
}


$QUERY = $cls_anagr->get_Query_Dati_Soggetto($p, $c);
$anagr = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Soggetto"]), "utente");
$pec_inipec	=	isset($anagr["InipecLoaded"]) ? $anagr["InipecLoaded"] : ""; //$utente->PEC;


//print_r($anagr);

//$QUERY_3
//print_r($anagr);
//$utente = new utente($p,$c);

//$id_utente 				= 	$utente->ID;
$genere_utente 			= isset($anagr["Genere"]) ? $anagr["Genere"] : ""; //	$utente->Genere;

$titolo_residenza = "RESIDENZA";

if ($genere_utente == "D")
	$titolo_residenza = "SEDE";

$comune_id 				=	isset($anagr["Comune_ID"]) ? $anagr["Comune_ID"] : ""; //$utente->Comune_ID;


$cognome_utente 	=	isset($anagr["Cognome"]) ? $anagr["Cognome"] : ""; //$utente->Cognome;
$nome_utente 		=	isset($anagr["Nome"]) ? $anagr["Nome"] : ""; //$utente->Nome;
$CC_nascita			=	isset($anagr["CC_Nascita"]) ? $anagr["CC_Nascita"] : ""; //$utente->CC_Nascita;
$paese_nasc_utente  =	isset($anagr["Paese_Nascita"]) ? $anagr["Paese_Nascita"] : ""; //$utente->Paese_Nascita;
if ($paese_nasc_utente == null) {
	$paese_nasc_utente = "Italia";
}

if ($paese_nasc_utente != "Italia")
	$sceltaLayout .= "<script>func_stato_estero();</script>";

$comune_nasc_utente =	isset($anagr["Comune_Nascita"]) ? $anagr["Comune_Nascita"] : ""; //$utente->Comune_Nascita;
$provincia_nasc_utente	=	isset($anagr["Provincia_Nascita"]) ? $anagr["Provincia_Nascita"] : ""; //$utente->Provincia_Nascita;
$data_nasc_utente	=	$cls_date->Get_DateNewFormat(isset($anagr["Data_Nascita"]) ? $anagr["Data_Nascita"] : "0000-00-00", "DB"); //from_mysql_date($utente->Data_Nascita);
$data_morte_utente	=	$cls_date->Get_DateNewFormat(isset($anagr["Data_Morte"]) ? $anagr["Data_Morte"] : "0000-00-00", "DB"); //from_mysql_date($utente->Data_Morte);

$chk_cfF = "";
$chk_cfM = "checked";
if ($genere_utente == "D") {
	$CF_ditta			=	isset($anagr["Codice_Fiscale"]) ? $anagr["Codice_Fiscale"] : ""; //$utente->Codice_Fiscale;
	$CF					=	"";
	/*if($CF_ditta!=""){
			echo "<h1>CF_Ditta</h1>";
			echo "<script type='text/javascript'>alert('".$CF_ditta."'); Decode_CF('".$CF_ditta."');</script>";

    }*/
} else {
	$CF_ditta	=	"";
	$CF	=		isset($anagr["Codice_Fiscale"]) ? $anagr["Codice_Fiscale"] : ""; //$utente->Codice_Fiscale;
}



$ditta				=	isset($anagr["Ditta"]) ? $anagr["Ditta"] : ""; //$utente->Ditta;
$forma_ditta 		=	isset($anagr["Forma_Giuridica"]) ? $anagr["Forma_Giuridica"] : ""; //$utente->Forma_Giuridica;

if ($genere_utente == "D")
	$sceltaLayout .= "<script>$('#forma_giuridica').val('" . $forma_ditta . "');cambia_title('forma_giuridica');</script>";
else
	$sceltaLayout .= "<script>$('#forma_giuridica_persona').val('" . $forma_ditta . "');changeFormaPersona();</script>";

if ($genere_utente == "D")
	if ($forma_ditta >= 20 && $forma_ditta <= 24)
		$sceltaLayout .= "<script>$('.cf_mask').show();</script>";

if ($genere_utente == "D") {
	$PI					=	isset($anagr["Partita_Iva"]) ? $anagr["Partita_Iva"] : ""; //$utente->Partita_Iva;
	$PI_persona = "";
} else {
	$PI					=	"";
	$PI_persona = isset($anagr["Partita_Iva"]) ? $anagr["Partita_Iva"] : ""; //$utente->Partita_Iva;
}

$prec_den_ditta		=	isset($anagr["Prec_Denom"]) ? $anagr["Prec_Denom"] : ""; //$utente->Prec_Denom;
$anno_cambio_ditta	=	isset($anagr["Anno_Cambio_Denom"]) ? $anagr["Anno_Cambio_Denom"] : ""; //$utente->Anno_Cambio_Denom;
if ($anno_cambio_ditta == 0)
	$anno_cambio_ditta = "";
$azienda			=	isset($anagr["Azienda"]) ? $anagr["Azienda"] : ""; //$utente->Azienda;
/****************************************** DA VEDERE *****************************************************************************/
//print_r($utente->righe_indirizzo());
/**************************************************************************************************************************************/
$note_utente			=	isset($anagr["Note"]) ? $anagr["Note"] : ""; //$utente->Note;
$cell_utente			=	isset($anagr["Cellulare"]) ? $anagr["Cellulare"] : ""; //$utente->Cellulare;
$mail_utente			=	isset($anagr["Mail"]) ? $anagr["Mail"] : ""; //$utente->Mail;
$pec_utente				=	isset($anagr["PEC"]) ? $anagr["PEC"] : ""; //$utente->PEC;

$data_registrazione		= $cls_date->Get_DateNewFormat(isset($anagr["Data_Registrazione"]) ? $anagr["Data_Registrazione"] : "0000-00-00", "DB"); //from_mysql_date($utente->Data_Registrazione);
if ($data_registrazione == "") {
	$data_registrazione = date('d/m/Y');
}


$readonly_cap = "readonly";
$indirizzo_res			=	$cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY["Indirizzo_R"])); //$utente->Residenza;

$ID_via = 0;
$ID_via_cap = 0;
$ID_res = 0;
//var_dump($QUERY["Indirizzo_R"]);die;
if ($indirizzo_res != null) {
	$ID_res		 		= 	$indirizzo_res["ID"]; //$indirizzo_res->ID;
	if ($p == 0) {
		$ID_via				=	1;
		$ID_via_cap			=	1;
	} else {
		$ID_via				=	$indirizzo_res["Via_ID"]; //$indirizzo_res->Via_ID;
		$ID_via_cap			=	$indirizzo_res["Via_Cap_ID"]; //$indirizzo_res->Via_Cap_ID;
		if ($ID_via_cap == 1)	$readonly_cap = "";
	}

	$CC_res				=	$indirizzo_res["CC_Indirizzo"]; //$indirizzo_res->CC_Indirizzo;
	$paese_res			=	$indirizzo_res["Paese"]; //$indirizzo_res->Paese;

	if ($paese_res == null) {
		$paese_res = "Italia";
	}

	$QUERY_2 = $cls_anagr->get_Query_Dati_Soggetto_Via(array("ViaID" => $indirizzo_res["Via_ID"], "CapID" => $indirizzo_res["Via_Cap_ID"]), $c);
	$Via_Object = null;
	if ($QUERY_2 != "")
		$Via_Object = $cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY_2));

	//echo "<h3>".$QUERY_2."</h3>";

	if ($paese_res != "Italia")
		$sceltaLayout .= "<script>func_stato_estero_indirizzo('nascondi');</script>";

	$comune_res			=	$indirizzo_res["Comune"]; //$indirizzo_res->Comune;
	$provincia_res		=	$indirizzo_res["Provincia"]; //$indirizzo_res->Provincia;
	$frazione_res		=  $indirizzo_res["Frazione"]; // $indirizzo_res->Frazione;

	/******************************************************************************************************************************************************************************/
	if ($Via_Object == null) $toponimo_res = "";
	else $toponimo_res		=	$Via_Object["Nome"]; //$indirizzo_res->Toponimo->Nome;
	//echo "<h1>Estera ---> ".$toponimo_res." --- Database: ".$Via_Object["Nome"]."</h1>";
	/******************************************************************************************************************************************************************************/

	$civico_res			=	$indirizzo_res["Civico"]; //$indirizzo_res->Civico;
	if ($civico_res == 0)	$civico_res = null;
	$esponente_res		=	$indirizzo_res["Esponente"]; //$indirizzo_res->Esponente;
	$CAP_res			=	$indirizzo_res["Cap"]; //$indirizzo_res->Cap;

	$interno_res		=	$indirizzo_res["Interno"]; //$indirizzo_res->Interno;
	if ($interno_res == 0)	$interno_res = null;
	$dettagli_res		=	$indirizzo_res["Dettagli"]; //$indirizzo_res->Dettagli;
	$telefono_res		=	$indirizzo_res["Telefono"]; //$indirizzo_res->Telefono;
	$fax_res			=	$indirizzo_res["Fax"]; //$indirizzo_res->Fax;
	$data_inizio_res_utente = $cls_date->Get_DateNewFormat($indirizzo_res["Data_Inizio_Residenza"], "DB"); //	from_mysql_date($indirizzo_res->Data_Inizio_Residenza);

} else {

	$CC_res				=	""; //$indirizzo_res->CC_Indirizzo;
	$paese_res			=	""; //$indirizzo_res->Paese;
	$comune_res			=	""; //$indirizzo_res->Comune;
	$provincia_res		=	""; //$indirizzo_res->Provincia;
	$frazione_res		= ""; // $indirizzo_res->Frazione;
	$toponimo_res = "";
	$civico_res			=	""; //$indirizzo_res->Civico;
	$esponente_res		=	""; //$indirizzo_res->Esponente;
	$CAP_res			=	""; //$indirizzo_res->Cap;
	$interno_res		=	""; //$indirizzo_res->Interno;
	$dettagli_res		=	""; //$indirizzo_res->Dettagli;
	$telefono_res		=	""; //$indirizzo_res->Telefono;
	$fax_res			=	""; //$indirizzo_res->Fax;
	$data_inizio_res_utente = ""; //	from_mysql_date($indirizzo_res->Data_Inizio_Residenza);
}

$cognome = isset($anagr["Cognome"]) ? $anagr["Cognome"] : "";
$dittaAnagr = isset($anagr["Ditta"]) ? $anagr["Ditta"] : "";
$idAnagr = isset($anagr["ID"]) ? $anagr["ID"] : "";

$ID_PAGE = $cls_anagr->get_ID_Move_Page($p, $a, $c, $cognome, $dittaAnagr, $idAnagr);

$pnext = $ID_PAGE["next"]; //$utente->next;
$pprev = $ID_PAGE["prev"]; //$utente->prev;
$next_alfa = $ID_PAGE["next_alfa"]; //$utente->next_alfa;
$prev_alfa = $ID_PAGE["prev_alfa"]; //$utente->prev_alfa;

$ordinamento = $cls_help->getVar('ordinamento');
if ($ordinamento == '')	$ordinamento = "ID";
$sceltaLayout .= "<script>$('#ordinamento').val('" . $ordinamento . "');</script>";

if ($ordinamento == "Nome") {
	$prev_current = $prev_alfa;
	$next_current = $next_alfa;
} else {
	$prev_current = $pprev;
	$next_current = $pnext;
}

if ($pnext == null) 	$pnext = 0;
if ($pprev == null) 	$pprev = 0;
if ($p == null)		$p = 0;

$chkF = "";
$chkM = "";
$chkD = "";

//RADIOBUTTON MASCHIO FEMMINA DITTA
if ($genere_utente != "D") {
	if ($genere_utente == 'F') {
		$chkF = "checked";
	} else if ($genere_utente == 'M') {
		$chkM = "checked";
	}

	$sceltaLayout .= "<script>$('#tab_soggetto').show();$('#tab_ditta').hide();</script>";
} else if ($genere_utente == "D") {
	$chkD = "checked tabindex=2";

	$sceltaLayout .= "<script>$('#tab_soggetto').hide();$('#tab_ditta').show();</script>";
}

if ($paese_res != "Italia" && $mode == "modifica") {
	$sceltaLayout .= "<script>$('#comune').addClass('sfondo_bianco').removeClass('sfondo_ricerca');</script>";
	$sceltaLayout .= "<script>$('#comune').attr('readonly',false);</script>";
	$sceltaLayout .= "<script>$('#cap').attr('readonly',false);</script>";
}



if ($data_inizio_res_utente == "") {
	$data_inizio_res_utente = "01/01/1900";
}

/**
 * GESTIONE F2 /////////////////////////////////////////
 */
if ($mode == "consulta") {
	if ($p != 0) {
		$F2_path = IMMAGINIWEB . "/redF2.png";
		$F2_click = "blocco('" . $anagr["ID"] . "');";
		$F2_title = "Modifica";
	} else {
		$F2_path = IMMAGINIWEB . "/redF2grey.png";
		$F2_click = "";
		$F2_title = "Modifica";
	}
} else {
	$F2_path = IMMAGINIWEB . "/F2.png";
	$F2_click = "scelta_moda('cerca');";
	$F2_title = "Consultazione";

	/////////////////////////////////////////
	$F2_path = IMMAGINIWEB . "/F2grey.png";
	$F2_click = "";
	$F2_title = "";
	/////////////////////////////////////////
}
/**
 * GESTIONE F2 /////////////////////////////////////////
 */
/******************************************** NON LO USA *******************************************************/
//FORME GIURIDICHE
//	$forma_giuridica = new forma_giuridica();
//	$forma_giuridica->array_forma();

function options_selezione($array)
{
	$options = "";
	for ($i = 0; $i < count($array); $i++) {
		$options .= "<option value='" . $array[$i]['ID'] . "'>" . $array[$i]['Sigla'] . " - " . $array[$i]['Descrizione'] . "</option>";
	}

	return $options;
}
$ARRAY = $cls_anagr->Get_Array_Forma_Giuridica();

//print_r($ARRAY);
$options_libero = options_selezione($ARRAY["LiberoProfessionista"]); //options_selezione($forma_giuridica->LiberoProfessionista);
$options_individuale = options_selezione($ARRAY["Individuale"]); //options_selezione($forma_giuridica->Individuale);
$options_persone = options_selezione($ARRAY["Persone"]); //options_selezione($forma_giuridica->Persone);
$options_capitale = options_selezione($ARRAY["Capitale"]); //options_selezione($forma_giuridica->Capitale);
$options_cooperativa = options_selezione($ARRAY["Cooperativa"]); //options_selezione($forma_giuridica->Cooperativa);
$options_consortile = options_selezione($ARRAY["Consortile"]); //options_selezione($forma_giuridica->Consortile);
$options_ente = options_selezione($ARRAY["Ente"]); //options_selezione($forma_giuridica->Ente);

?>
<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<meta http-equiv="X-UA-Compatible" content="IE=8" />

<title>Anagrafe - Dati soggetto</title>

	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>

	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>-->

<script>
	/* -----------  VARIABILI JAVASCRIPT E SELEZIONI LAYOUT ----------- */
	var stringaPHP = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
	var modalita = '<?php echo $mode; ?>';
	var utente_ID = "<?php echo $anagr['ID']; ?>";
	var comune_ID = "<?php echo $anagr['Comune_ID']; ?>";


	function doc_utente() {

		//alert('NON DISPONIBILE. Gestione corrispondenza in costruzione...');

		//strDim = Dim_Alert(1200, 800);

		var stringa = "<?= WEB_ROOT ?>/search/posta/posta.php?p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&mode=" + modalita;
		//window.showModalDialog(stringa,"", strDim);
		openWindowSearch(stringa, {
			width: 1200,
			height: 800,
			left: (($(window).width() / 2) - 600),
			top: (($(window).height() / 2) - 400)
		});

	}

	function radioClicked(value) {
		if ($('#cognome_utente').val() != "")
			testo = $('#cognome_utente').val();
		else
			testo = $('#ditta').val();

		switch (value) {
			case ('D'):
				$('#tab_soggetto').hide();
				$('#tab_ditta').show();
				$('#titolo_residenza').text('SEDE');
				$('#cognome_utente').val('');
				$('#ditta').val(testo);
				$('#cognome_utente').removeClass("validateCustom vld_Custom_r");
				$('#nome_utente').removeClass("validateCustom vld_Custom_r");
				$('#paese_nascita').removeClass("validateCustom vld_Custom_r");
				//$('#comune_nascita').removeClass("validateCustom vld_Custom_r");
				break;

			case ('M'):
				$('#tab_soggetto').show();
				$('#tab_ditta').hide();
				$('#titolo_residenza').text('RESIDENZA');
				$('#cognome_utente').val(testo);
				$('#ditta').val('');
				$('#cognome_utente').addClass("validateCustom vld_Custom_r");
				$('#nome_utente').addClass("validateCustom vld_Custom_r");
				$('#paese_nascita').addClass("validateCustom vld_Custom_r");
				//$('#comune_nascita').addClass("validateCustom vld_Custom_r");

				break;

			case ('F'):
				$('#tab_soggetto').show();
				$('#tab_ditta').hide();
				$('#titolo_residenza').text('RESIDENZA');
				$('#cognome_utente').val(testo);
				$('#ditta').val('');
				$('#cognome_utente').addClass("validateCustom vld_Custom_r");
				$('#nome_utente').addClass("validateCustom vld_Custom_r");
				$('#paese_nascita').addClass("validateCustom vld_Custom_r");
				//$('#comune_nascita').addClass("validateCustom vld_Custom_r");

				break;
		}
	}


	//SCELTA MODALITA' LETTURA O SCRITTURA
	function scelta_moda(value) {
		if (modifica == 1) {
			alert('salvare i dati o annullare prima di procedere');
		} else {
			value_ord = $('#ordinamento').val();

			if (value == "modifica") {
				if (utente_ID != 0)
					top.location.href = "dati_soggetto.php?mode=modifica&ordinamento=" + value_ord + stringaPHP;
			} else {
				if (utente_ID != 0)
					top.location.href = "dati_soggetto.php?mode=consulta&ordinamento=" + value_ord + stringaPHP;
			}
		}
	}

	var prev_utente = "<?php echo $prev_current; ?>";
	var next_utente = "<?php echo $next_current; ?>";

	function ordinamento() {
		value = $('#ordinamento').val();

		if (value == "ID") {
			prev_utente = "<?php echo $pprev; ?>";
			next_utente = "<?php echo $pnext; ?>";
		} else if (value == "Nome") {
			prev_utente = "<?php echo $prev_alfa; ?>";
			next_utente = "<?php echo $next_alfa; ?>";
		}

	}

	//CAMBIO UTENTE
	/*function gira_utente (value)
   	{

   		if (modifica==1)
   		{
   			alert('salvare i dati o annullare prima di procedere');
   		}
   		else
   		{
   			value_ord = $('#ordinamento').val();

			if( value == 'prev' )
				link = "dati_soggetto.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;
			else if( value == 'next' )
				link = "dati_soggetto.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

   			top.location.href = link;
   		}
   	}*/

	//CAMBIO PAGINA
	/*function pagina_menu (value)
	{
		if (modifica==1)
		{
			alert('salvare i dati o annullare prima di procedere');
		}
		else
		{
			value_ord = $('#ordinamento').val();

			if(value==1 && (modalita=="consulta" || utente_ID!=0))
			{
				link = "annotazioni.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
				top.location.href = link;
			}
			else if(value==0 && (modalita=="consulta" || utente_ID!=0))
			{
				link = "cambia_residenza.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
				top.location.href = link;
			}
		}
	}*/

	//ANNULLA
	/*	function annulla()
   	{
   		stringaPHP += "&mode=consulta";
		stringa = "dati_soggetto.php?"+stringaPHP;
   	   	top.location.href = stringa;
   	}*/
</script>

<script>
	//F3
	switchMenuImg("F3");
	F3_button = function() {
		//alert();
		control_salva = submit_buttons('Salva');
		//	alert(control_salva);
		if (control_salva && validateForm())
			$("#btnSub").trigger("click");
	}

	//F4
	switchMenuImg("F4");
	F4_button = function() {
		control_salva = submit_buttons('Delete');
		//	alert(control_salva);
		if (control_salva)
			$("#btnSub").trigger("click");
	}

	//F5
	switchMenuImg("F5");
	F5_button = function() {
		stringaPHP += "&mode=consulta";
		stringa = "dati_soggetto.php?" + stringaPHP;
		top.location.href = stringa;
	}

	//F6
	switchMenuImg("F6");
	F6_button = function() {
		stringa = "dati_soggetto.php?mode=modifica&p=0&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		top.location.href = stringa;
	}



	//PAG GIU
	switchMenuImg("pagedown");
	pagedown_button = function() {
		if (modifica == 1) {
			alert('salvare i dati o annullare prima di procedere');
		} else {
			value_ord = $('#ordinamento').val();

			link = "Veicoli.php?mode=<?php echo $mode; ?>&ordinamento=" + value_ord + stringaPHP;
			top.location.href = link;
		}
	}

	//PAG SU
	switchMenuImg("pageup");
	pageup_button = function() {
		if (modifica == 1) {
			alert('salvare i dati o annullare prima di procedere');
		} else {
			value_ord = $('#ordinamento').val();

			link = "annotazioni.php?mode=<?php echo $mode; ?>&ordinamento=" + value_ord + stringaPHP;
			top.location.href = link;
		}
	}

	//F7
	switchMenuImg("F7");
	F7_button = function() {
		if (modifica == 1) {
			alert('salvare i dati o annullare prima di procedere');
		} else {
			value_ord = $('#ordinamento').val();
			link = "dati_soggetto.php?mode=consulta&p=" + prev_utente + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento=" + value_ord;

			top.location.href = link;
		}
	}

	//F8
	switchMenuImg("F8");
	F8_button = function() {
		if (modifica == 1) {
			alert('salvare i dati o annullare prima di procedere');
		} else {
			value_ord = $('#ordinamento').val();
			link = "dati_soggetto.php?mode=consulta&p=" + next_utente + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento=" + value_ord;

			top.location.href = link;
		}
	}

	switchMenuImg("F11");
	F11_button = function() {

		$("#frameHelp").attr("src", "<?= SUPER_WEB_ROOT . "/archivio/help/DatiSoggetto.pdf"; ?>");
		$("#helpModalLabel").empty().append("<b>Help ANAGRAFE Dati soggetto</b>");
		$("#helpModal").modal('show');

	}

	//CONTROLLI CAMPI E CALCOLO CODICE FISCALE
	//------------------------------------------------------------------//

	//CALCOLO CF
	function calcoloCF(value) {
		if (modalita == "modifica") {

			if (value == 0) {
				//<!-- CONTROLLO INSERIMENTO E CALCOLO CODICE FISCALE -->
				var cognome = $('input#cognome_utente').val();
				var nome = $('input#nome_utente').val();
				var radioVal = $('input[name=genere]:checked').val();
				var data_nasc = $('input#data_nascita').val();
				var codice_catastale = $('#CC_nascita').val();
				var paese_nascita = $('input#paese_nascita').val();
			} else {
				var cognome = $('input#cognome_cf').val();
				var nome = $('input#nome_cf').val();
				var radioVal = $('input[name=genere_cf]:checked').val();
				var data_nasc = $('input#data_cf').val();
				var codice_catastale = $('#CC_cf').val();
				var paese_nascita = $('input#paese_cf').val();
			}



			if (radioVal != 'D') {
				if (codice_catastale.length != 4) {
					if (paese_nascita == "Italia") {
						alert('Inserire il comune di nascita per eseguire il calcolo del Codice Fiscale.');
						return false;
					} else {
						alert('Inserire il paese di nascita corretto per eseguire il calcolo del Codice Fiscale.');
						return false;
					}
				}

				if (data_nasc == null || data_nasc == "" || data_nasc == undefined) {
					alert('Inserire la data di nascita per eseguire il calcolo del Codice Fiscale.');
					return false;
				}

				if ((data_nasc != null && data_nasc != "" && data_nasc != undefined) && codice_catastale.length == 4) {
					var cf = "";
					try {
						var arrayData = data_nasc.split("/");
						cf = new CodiceFiscale({
							name: nome,
							surname: cognome,
							gender: radioVal,
							day: arrayData[0],
							month: arrayData[1],
							year: arrayData[2],
							birthplace: codice_catastale
						});
						//console.log(cf);
						//alert(cf);
					} catch (e) {
						alert(e);
					}

					//Cod = compute_CF ( cognome , nome , radioVal , data_nasc , codice_catastale );
					if (value == 0) {
						$('input#CF').val(cf);
					} else {
						$('input#CF_ditta').val(cf);
					}
				}
			}
		}
	}

	function check_omonimia() {
		var cognome = $('input#cognome_utente').val();
		var nome = $('input#nome_utente').val();
		var radioVal = $('input[name=genere]:checked').val();
		var data_nasc = $('input#data_nascita').val();
		var codice_catastale = $('#CC_nascita').val();
		var ditta = $('input#ditta').val();
		var PI = $('input#PI').val();
		var CDF = $('input#CF').val();
		var CC_comune = "<?php echo $c; ?>";

		if (cognome == "") {
			cognome = null;
		};
		if (nome == "") {
			nome = null;
		};
		if (radioVal == "") {
			radioVal = null;
		};
		if (data_nasc == "") {
			data_nasc = null;
		};
		if (codice_catastale == "") {
			codice_catastale = null;
		};
		if (CDF == "") {
			CDF = null;
		};
		if (ditta == "") {
			ditta = null;
		};
		if (PI == "") {
			PI = null;
		};

		$.ajax({
			type: "POST",
			async: false,
			url: "ajax/ajax_anagrafe.php",
			data: {
				ID: utente_ID,
				cognome: cognome,
				nome: nome,
				genere: radioVal,
				data: data_nasc,
				cc: codice_catastale,
				cc_com: CC_comune,
				ditta: ditta,
				PI: PI,
				CF: CDF
			},

			success: function(value) {
				array_ritorno = value.split(' ');
				switch (array_ritorno[0]) {
					case "no":

						//alert("Non sono stati riscontrati casi di omonimia");

						break;

					case "dubbi":

						num = array_ritorno.length;
						if (num == 2) {
							if (array_ritorno[1] == comune_ID) {
								//alert("Non sono stati riscontrati casi di omonimia");
							} else {
								alert("Parziale omonimia con ID " + array_ritorno[1]);
							}
						} else {
							var utenti = "Parziale omonimia con ";
							for (var j = 1; j < num; j++) {
								if (array_ritorno[j] != comune_ID) {
									utenti = utenti + "ID " + array_ritorno[j] + " - ";
								}
							}

							utenti = utenti.substring(0, utenti.length - 3);
							//alert(utenti);
						}
						break;

					case "omo":

						numTot = array_ritorno.length;

						var kVal = 0;
						for (var k = 0; k < numTot; k++) {
							if (array_ritorno[k] == "/") {
								kVal = k;
							}
						}

						if (kVal == 0) {
							num = numTot;
						} else {
							num = kVal;
						}

						if (num == 2) {
							if (array_ritorno[1] == comune_ID) {
								//alert("Non sono stati riscontrati casi di omonimia");
							} else {
								alert("Omonimia con ID " + array_ritorno[1]);
							}
						} else {
							utenti = "Omonimia con ";

							for (var j = 1; j < num; j++) {
								if (array_ritorno[j] != comune_ID) {
									utenti = utenti + "ID " + array_ritorno[j] + " - ";
								}
							}

							utenti = utenti.substring(0, utenti.length - 3);
							//alert(utenti);
						}

						if (kVal != 0) {
							num = numTot;
							if (num == 3 + kVal) {
								if (array_ritorno[2 + kVal] == comune_ID) {
									//alert("Non sono stati riscontrati casi di parziale omonimia");
								} else {
									alert("Parziale omonimia con ID " + array_ritorno[2 + kVal]);
								}
							} else {
								var utenti = "Parziale omonimia con ";
								for (var j = kVal + 2; j < num; j++) {
									if (array_ritorno[j] != comune_ID) {
										utenti = utenti + "ID " + array_ritorno[j] + " - ";
									}
								}

								utenti = utenti.substring(0, utenti.length - 3);
								//alert(utenti);
							}
						}
						break;
				}
			}
		});

	}

	function Decode_CF(val_CF) {
		try {
			var cf = new CodiceFiscale(val_CF);

			if (cf.birthplace.prov != "EE") {
				$('#paese_cf').val("Italia");
				$('#CC_cf').val(cf.birthplace.cc);
				$('#comune_cf').val(cf.birthplace.nome);
			} else {
				$('#paese_cf').val(cf.birthplace.nome);
				$('#CC_cf').val(cf.birthplace.cc);
			}

			var day = cf.day;
			if (day < 10) day = "0" + day;
			var month = cf.month;
			if (month < 10) month = "0" + month;

			$('#data_cf').val(day + "/" + month + "/" + cf.year);


			if (cf.gender == "M") $("#radioM_cf").attr("checked", true);
			if (cf.gender == "F") $("#radioF_cf").attr("checked", true);

		} catch (error) {
			alert(error);
		}
	}

	function checkCf() {

		pattern_data = /[^0-9\x2F]/;
		pattern_nome = /[^A-Za-z .\x27\x28\x29\x2d]/;

		//<!-- CONTROLLO INSERIMENTO COGNOME -->
		var cognome = $('input#cognome_cf').val();

		control_cognome = cognome.match(pattern_nome);


		if ((cognome == "") || (cognome == "undefined")) {
			alert("Il campo Cognome \xE8 necessario per il calcolo del CF.");
			return false;
		} else if (control_cognome) {
			alert("Il campo Cognome non puo' contenere caratteri speciali");
			return false;
		}

		cognome = (cognome).toUpperCase();
		$('input#cognome_cf').val(cognome);


		//  <!-- CONTROLLO INSERIMENTO NOME -->
		var nome = $('input#nome_cf').val();

		control_nome = nome.match(pattern_nome);
		if ((nome == "") || (nome == "undefined")) {
			alert("Il campo Nome \xE8 necessario per il calcolo del CF.");
			return false;
		} else if (control_nome) {
			alert("Il campo Nome non puo' contenere caratteri speciali o numerici");
			return false;
		}

		nome = (nome).toUpperCase();
		$('input#nome_cf').val(nome);


		// <!-- CONTROLLO DATA NASCITA -->
		var data_nasc = $('input#data_cf').val();
		control_data_nasc = data_nasc.match(pattern_data);
		if (control_data_nasc) {
			alert("Il campo Data di nascita puo' contenere solo caratteri numerici ed il carattere '/' di separazione");
			return false;
		}

		if (data_nasc != "" && data_nasc != null && data_nasc != undefined) {
			data_nasc = controlla_data_campo(data_nasc, "Controllare la data di nascita", 1);
			if (data_nasc != false) $('input#data_cf').val(data_nasc);
			else {
				return false;
			}
		}

		//CALCOLO CF DOPO I CONTROLLI NECESSARI ED ESCO DALLA FUNZIONE

		calcoloCF(1);

	}

	//CONTROLLO CAMPI
	function controllaCampi(value) {
		pattern_speciali = /[^A-Za-z0-9\x20\x27\x28\x29\x2c\x2d\x2e\x2f\x3a\x3b]/;
		pattern_data = /[^0-9\x2F]/;
		pattern_mail = /^[^\x40]{1,40}[\x40]{1}[^\x40]{1,20}[.]{1}[a-zA-Z]{1,40}$/;
		pattern_ditta = /[^A-Za-z0-9 .\x27\x28\x29\x2d]/;
		pattern_nome = /[^A-Za-z .\x27\x28\x29\x2d]/;
		pattern_numeri = /[^0-9]/;
		pattern_interno = /[^0-9a-zA-Z\x2F]/;
		pattern_cf = /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/;

		//<!-- CONTROLLO SELEZIONE RADIOBUTTON -->

		var radioVal = $('input[name=genere]:checked').val();
		if (radioVal != 'M' && radioVal != 'F' && radioVal != 'D') {
			alert('Non \xE8 stato specificato il tipo di utente: Maschio (M), Femmina (F), oppure Ditta (D).');
			return false;
		}


		if (radioVal != 'D') {
			//<!-- CONTROLLO INSERIMENTO COGNOME -->
			var cognome = $('input#cognome_utente').val();

			control_cognome = cognome.match(pattern_nome);


			if ((cognome == "") || (cognome == "undefined")) {
				alert("Il campo Cognome \xE8 obbligatorio.");
				return false;
			} else if (control_cognome) {
				alert("Il campo Cognome non puo' contenere caratteri speciali");
				return false;
			}

			cognome = (cognome).toUpperCase();
			$('input#cognome_utente').val(cognome);

			//<!-- CONTROLLO INSERIMENTO NOME -->
			var nome = $('input#nome_utente').val();

			control_nome = nome.match(pattern_nome);
			if ((nome == "") || (nome == "undefined")) {
				alert("Il campo Nome \xE8 obbligatorio.");
				return false;
			} else if (control_nome) {
				alert("Il campo Nome non puo' contenere caratteri speciali o numerici");
				return false;
			}

			nome = (nome).toUpperCase();
			$('input#nome_utente').val(nome);

			//<!-- CONTROLLO INSERIMENTO PAESE DI NASCITA -->
			var paese_nascita = $('input#paese_nascita').val();
			control_paese_nascita = paese_nascita.match(pattern_nome);
			if (control_paese_nascita) {
				alert("Il campo Stato di Nascita non puo' contenere caratteri speciali o numerici.");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO COMUNE DI NASCITA -->
			var comune_nascita = $('input#comune_nascita').val();
			control_comune_nascita = comune_nascita.match(pattern_nome);
			if (control_comune_nascita) {
				alert("Il campo Comune di Nascita non puo' contenere caratteri speciali o numerici.");
				return false;
			}

			//<!-- CONTROLLO DATA NASCITA -->
			var data_nasc = $('input#data_nascita').val();
			control_data_nasc = data_nasc.match(pattern_data);
			if (control_data_nasc) {
				alert("Il campo Data di nascita puo' contenere solo caratteri numerici ed il carattere '/' di separazione");
				return false;
			}

			if (data_nasc != "" && data_nasc != null && data_nasc != undefined) {
				data_nasc = controlla_data_campo(data_nasc, "Controllare la data di nascita", 1);
				if (data_nasc != false) $('input#data_nascita').val(data_nasc);
				else {
					return false;
				}
			}

			//<!-- CONTROLLO DATA MORTE -->
			var data_morte = $('input#data_morte').val();
			control_data_morte = data_morte.match(pattern_data);
			if (control_data_morte) {
				alert("Il campo Data di morte puo' contenere solo caratteri numerici ed il carattere '/' di separazione");
				return false;
			}
			if (data_morte != "" && data_morte != null && data_morte != undefined) {
				data_morte = controlla_data_campo(data_morte, "Controllare la data di morte", 1);
				if (data_morte != false) $('input#data_morte').val(data_morte);
				else return false;
				if (data_nasc == "" || data_nasc == null || data_nasc == undefined) {
					alert("Controllare la data di nascita");
					return false;
				}
			}
		}

		//CALCOLO CF DOPO I CONTROLLI NECESSARI ED ESCO DALLA FUNZIONE
		if (value == 1) {
			calcoloCF(0);
			return;
		}


		if (radioVal != 'D') {

			//<!-- CONTROLLO CODICE FISCALE -->
			var CDF = $('input#CF').val();

			control_cf = CDF.search(pattern_cf);
			if (control_cf == true || CDF.length != 16) {
				alert("Il Codice Fiscale non \xE8 stato inserito correttamente.");
			}
		} else {
			//<!-- CONTROLLO INSERIMENTO DITTA -->
			var ditta = $('input#ditta').val();

			control_ditta = ditta.match(pattern_ditta);


			if ((ditta == "") || (ditta == "undefined")) {
				alert("Il campo Ditta � obbligatorio.");
				return false;
			} else if (control_ditta) {
				alert("Il campo Ditta non puo' contenere caratteri speciali");
				return false;
			}

			ditta = (ditta).toUpperCase();
			$('input#ditta').val(ditta);

			//<!-- CONTROLLO PARTITA IVA -->
			var PI = $('input#PI').val();
			if (radioVal == 'D') {
				control_pi = PI.match(pattern_numeri);

				if (control_pi != null || PI.length != 11) {
					alert("La Partita Iva della ditta non � stata inserita correttamente.");
				}
			}

			//<!-- CONTROLLO INSERIMENTO PRECEDENTE DENOMINAZIONE -->
			var prec_den = $('input#prec_den').val();
			control_prec_den = prec_den.match(pattern_ditta);
			if (control_prec_den) {
				alert("Il campo Precedente denominazione non puo' contenere caratteri speciali.");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO ANNO CAMBIO -->
			var anno_cambio = $('input#anno_cambio').val();
			control_anno_cambio = anno_cambio.match(pattern_numeri);
			if (control_anno_cambio) {
				alert("Il campo Cambio anno puo' contenere solo caratteri numerici");
				return false;
			}
			if ((anno_cambio.length != 4 || anno_cambio <= 1900) && anno_cambio != null && anno_cambio != "" && anno_cambio != undefined) {
				alert("Il campo Cambio anno deve essere di 4 cifre e superiore all'anno 1900");
				return false;
			}
		}

		check_omonimia();

		if (value == 5) {
			return;
		}

		//<!-- CONTATTI -->
		//<!-- CONTROLLO INSERIMENTO CELLULARE -->
		var cell = $('#cell_utente').val();
		var mail = $('#mail_utente').val();

		control_cell = cell.match(pattern_numeri);


		if (control_cell) {
			alert("Il campo Cellulare puo' contenere solo caratteri numerici");
			return false;
		}
		if (mail != null && mail != undefined && mail != "") {
			control_mail = mail.search(pattern_mail);
			if (control_mail) {
				alert("Inserire un indirizzo email valido");
				return false;
			}
		}

		//<!-- RESIDENZA -->
		//<!-- CONTROLLO INSERIMENTO PAESE DI RESIDENZA -->
		var paese = $('input#paese').val();
		if ((paese == "") || (paese == "undefined")) {
			alert("Il campo Stato di Residenza \xE8 obbligatorio.");
			return false;
		}
		control_paese = paese.match(pattern_nome);
		if (control_paese) {
			if (paese != "Italia") {
				alert("Il campo Stato di Residenza non puo' contenere caratteri speciali o numerici.");
				return false;
			}
		}

		//<!-- CONTROLLO INSERIMENTO COMUNE DI RESIDENZA -->
		var comune = $('input#comune').val();
		if ((comune == "") || (comune == "undefined")) {
			alert("Il campo Comune di Residenza \xE8 obbligatorio.");
			return false;
		}
		control_comune = comune.match(pattern_nome);
		if (control_comune) {
			alert("Il campo Comune di Residenza non puo' contenere caratteri speciali o numerici.");
			return false;
		}

		var cap = $('input#cap').val();
		if ((cap == "") || (cap == "undefined")) {
			alert("Il campo CAP \xE8 obbligatorio.");
			return false;
		}
		var frazione = $('input#frazione').val();
		if (paese == "Italia") {
			var via = $('input#via').val();
			if ((via == "") || (via == "undefined")) {
				alert("Il campo Indirizzo di Residenza \xE8 obbligatorio.");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO CAP DI RESIDENZA -->
			control_cap = cap.match(pattern_numeri);
			if (control_cap) {
				alert("Il campo CAP di Residenza puo' contenere solo caratteri numerici");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO INDIRIZZO DI RESIDENZA -->
			control_via = via.match(pattern_speciali);
			if (control_via) {
				alert("Il campo Indirizzo di Residenza non puo' contenere caratteri speciali o numerici.");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO FRAZIONE DI RESIDENZA -->
			control_frazione = frazione.match(pattern_nome);
			if (control_frazione) {
				alert("Il campo Frazione di Residenza non puo' contenere caratteri speciali o numerici.");
				return false;
			}

		} else {
			var via = $('input#via_estero').val();
			if ((via == "") || (via == "undefined")) {
				alert("Il campo Indirizzo di Residenza \xE8 obbligatorio.");
				return false;
			}

			control_cap = cap.match(pattern_speciali);
			if (control_cap) {
				alert("Il campo CAP di Residenza non puo' contenere caratteri speciali.");
				return false;
			}

			control_via = via.match(pattern_speciali);
			if (control_via) {
				alert("Il campo Indirizzo di Residenza non puo' contenere caratteri speciali.");
				return false;
			}

			control_frazione = frazione.match(pattern_speciali);
			if (control_frazione) {
				alert("Il campo Frazione di Residenza non puo' contenere caratteri speciali.");
				return false;
			}
		}


		//<!-- CONTROLLO INSERIMENTO CIVICI DI RESIDENZA -->
		var civico = $('input#civico').val();
		var interno = $('input#interno').val();
		var esponente = $('input#esponente').val();
		var dettagli = $('input#dettagli').val();

		control_civico = civico.match(pattern_numeri);
		control_interno = interno.match(pattern_interno);
		control_esponente = esponente.match(pattern_ditta);
		control_dettagli = dettagli.match(pattern_ditta);

		if (control_civico) {
			alert("Il campo Civico puo' contenere solo caratteri numerici");
			return false;
		}
		if (control_interno) {
			alert("Il campo Interno non puo' contenere caratteri speciali");
			return false;
		}
		if (control_esponente) {
			alert("Il campo Esponente puo' contenere solo caratteri alfanumerici");
			return false;
		}
		if (control_dettagli) {
			alert("Il campo Dettagli non puo' contenere caratteri speciali");
			return false;
		}

		//<!-- CONTROLLO INSERIMENTO TELEFONO E FAX -->
		var telefono = $('input#tel').val();
		var fax = $('input#fax').val();

		control_telefono = telefono.match(pattern_numeri);
		control_fax = fax.match(pattern_numeri);

		if (control_telefono) {
			alert("Il campo Telefono puo' contenere solo caratteri numerici");
			return false;
		}
		if (control_fax) {
			alert("Il campo Fax puo' contenere solo caratteri numerici");
			return false;
		}

		//<!-- CONTROLLO DATA INIZIO RESIDENZA -->
		var data_res = $('input#data_res').val();
		data_res = controlla_data_campo(data_res, "Controllare la data di residenza", 1);

		if (data_res != false) {
			$('input#data_res').val(data_res);
		} else {
			return false;
		}

		return true;
	}

	function cambia_title(value) {
		testo = $('#' + value + ' option:selected').text();
		$('#' + value).attr('title', testo);
	}

	function decodifica() {
		CF_inserito = $('#CF').val();
		try {
			var cf = new CodiceFiscale(CF_inserito);

			$('[name=genere][value=' + cf.gender + ']').prop('checked', true);
			$('#CC_nascita').val(cf.birthplace.cc);

			if (cf.birthplace.prov != "EE") {
				$('#paese_nascita').val("Italia");
				document.getElementById("paese_nascita").dispatchEvent(new Event("change"));
				$('#comune_nascita').val(cf.birthplace.nome);
				$('#dati_sogg_prov_nasc').val(cf.birthplace.prov);
			} else {
				$('#paese_nascita').val(cf.birthplace.nome);
				document.getElementById("paese_nascita").dispatchEvent(new Event("change"));
			}

			var day = cf.day;
			if (day < 10) day = "0" + day;
			var month = cf.month;
			if (month < 10) month = "0" + month;

			$('#data_nascita').val(day + "/" + month + "/" + cf.year);
			$('#cognome_utente').val("");
			$('#nome_utente').val("");
		} catch (e) {
			alert(e);
		}


	}

	function settaInput(val) {
		switch (val) {
			case 1: {
				if ($('#paese_nascita').val() != "Italia" && $('#paese_nascita').val() != "") {
					$('#comune_nascita').css("background-color", "");
					$('#comune_nascita').css("border", "");
					$('#comune_nascita').attr("readonly", false);
					$('#label_prov_1').hide();
					//$("#comune_nascita").removeClass("validateCustom vld_Custom_r");
				} else {
					$('#comune_nascita').css("background-color", "rgb(153, 204, 255)");
					$('#comune_nascita').css("border", "2px solid black");
					$('#comune_nascita').attr("readonly", true);
					$('#label_prov_1').show();
					//$("#comune_nascita").addClass("validateCustom vld_Custom_r");
				}
				break;
			}
			case 2: {
				if ($('#paese').val() != "Italia" && $('#paese').val() != "") {
					$('#comune').css("background-color", "");
					$('#comune').css("border", "");
					$('#comune').attr("readonly", false);
					$('#label_prov_2').hide();
					$('#comune').removeClass("validateCustom vld_Custom_r");
					$('#cap').removeClass("validateCustom vld_Custom_r");
				} else {
					$('#comune').css("background-color", "rgb(153, 204, 255)");
					$('#comune').css("border", "2px solid black");
					$('#comune').attr("readonly", true);
					$('#label_prov_2').show();
					$('#comune').addClass("validateCustom vld_Custom_r");
					$('#cap').addClass("validateCustom vld_Custom_r");
				}
				validateForm(document.getElementById("paese"));
				break;
			}
			case 3: {
				if ($('#paese_cf').val() != "Italia" && $('#paese').val() != "") {
					$('#comune_cf').css("background-color", "");
					$('#comune_cf').css("border", "");
					$('#comune_cf').attr("readonly", false);

					//$('#label_prov_2').hide();
				} else {
					$('#comune_cf').css("background-color", "rgb(153, 204, 255)");
					$('#comune_cf').css("border", "2px solid black");
					$('#comune_cf').attr("readonly", true);

					//$('#label_prov_2').show();
				}
				//validateForm(document.getElementById("comune"));
				break;
			}
		}

	}

	function resettaValidazione() {
		validateForm(document.getElementById("comune"));
	}

	function func_stato_estero() {
		$('#comune_nascita').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
		$('#comune_nascita').attr('readonly', false);
		$('.provincia_dati_sogg').hide();
	}

	function func_stato_estero_indirizzo(value) {
		if (value == "nascondi") {
			$('#comune_residenza').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
			$('#comune_residenza').attr('readonly', false);
			$('.provincia_res_dati_sogg').hide();
		} else if (value == "mostra") {
			$('#comune_residenza').removeClass('sfondo_bianco').addClass('sfondo_ricerca');
			$('#comune_residenza').attr('readonly', true);
			$('.provincia_res_dati_sogg').show();
		}

	}

	function changeFormaGiuridica() {
		value = $('#forma_giuridica :selected').parent().attr('label');
		cambia_title('forma_giuridica');
		if (value == "Impresa individuale") {
			$('.cf_mask').show();
		} else {
			$('.cf_mask').hide();
		}
	}

	function changeFormaPersona() {
		value = $('#forma_giuridica_persona').val();
		cambia_title('forma_giuridica_persona');
		if (value > 0) {
			$('#PI_persona').prop("readonly", false).removeClass("sfondo_grigio");
		} else {
			$('#PI_persona').val("").addClass("sfondo_grigio").prop("readonly", true);
		}
	}
</script>
<!--</head>

<body class="sfondo_new_gitco" onload="control_lock('<?php echo $utente->ID; ?>');" >

<table align=center class="table_azzurra" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% align=center class="table_azzurra" border=0>
<tr>
<td valign=top>-->



<?php

/************************************************************* DA CONTROLLARE ****************************************************************************/



//echo "<h1>jhksdbfvljhfgsdv".$servizio."</h1>";
//$servizio = "TARGHEESTERE";
/*switch ($servizio)
{
	case "COATTIVA":
		include ANAGRAFE . '/menu/menu_anagrafe.php';
		break;
	case "TARGHEESTERE":
		include TARGHEESTERE . '/menu/menu_targheestere.php';
		break;
	case "PUBBLICITA":
		include PUBBLICITA . '/menu/menu_pubblicita.php';
		break;
	default:
		include ANAGRAFE . '/menu/menu_anagrafe.php';
		break;
}*/

//include WEB_ROOT.'/anagrafe/menu/menu_anagrafe.php';//e' l'unico che serve

/**************************************************ANAGRAFE DA LEVARE (prenedere RicercheDaId();)*************************************************************/

//include ANAGRAFE . '/menu/menu_anagrafe.php';

/********************************************************************************************************************************/
//echo "<h1>avaderfasde".$F2_click."</h1>";
?>

<!--<table align=center class=table_interna border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='<?php echo $F2_title; ?>'" href="#" onclick="<?php echo $F2_click; ?>" >
			<img src="<?php echo $F2_path; ?>" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="<?php if ($mode == "consulta") echo "/gitco2/immagini/Save-iconF3grey.png";
																		else echo "/gitco2/immagini/Save-iconF3.png"; ?>" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="<?php if ($mode == "consulta") echo "/gitco2/immagini/delete-iconF4grey.png";
																		else echo "/gitco2/immagini/delete-iconF4.png"; ?>" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onclick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Nuovo Record'" href="#" onclick="link('new');" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovo.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pagina_menu(0);" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pagina_menu(1);" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a id=F7 href="#" onMouseover="title='Record precedente F7'" onclick="gira_utente('prev');">
          	<img src="/gitco2/immagini/FrecciaS.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
		</td>
        <td width=7% align="center">
          	<a id=F8 href="#" onMouseover="title='Record successivo F8'" onclick="gira_utente('next');">
          	<img src="/gitco2/immagini/FrecciaD.png" width=42px height=42px border="0" alt="Utente successivo">
          	</a>
        </td>
        <td width=11%></td>
        <td width=7% align="center">
          	<a href="#" onMouseover="title='Stampa'" onclick="">
          	<img src="/gitco2/immagini/printF10grey.png" width=50 height=50 border="0" ></a>
    	</td>
        <td width=3%></td>
    	<td align=center width=7% >
    		<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td width=2%></td>
		<td width=7%>
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>-->

<script>
	/*function callParent(valorediritorno) {

        switch(selectParent){

            case "utente":
                if(valorediritorno!=null && valorediritorno!=undefined)
                    top.location.href="<?= WEB_ROOT; ?>/anagrafe/dati_soggetto.php?mode=consulta&p="+valorediritorno.p+"&c="+valorediritorno.c+"&a=<?php echo $a; ?>";

                break;

            case "stato":
                if(valorediritorno!=null && valorediritorno!=undefined) {

                    if (selectRif == 0) {

                        paese_ritorno = valorediritorno.paese;
                        $('#paese_nascita').val(paese_ritorno);
												alert("parent "+$('#paese_nascita').val());
												document.getElementById("paese_nascita").dispatchEvent(new Event("change"));
                        if (paese_ritorno != "Italia") {
                            $('#comune_nascita').val(null);
                            $('#comune_nascita').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
                            $('#comune_nascita').attr('readonly', false);
                            $('#dati_sogg_prov_nasc').val(null);
                            $('#dati_sogg_prov_nasc').attr('disabled', 'disabled');
                            $('#CC_nascita').val(valorediritorno.CC);
                            $('.provincia_dati_sogg').hide();
                        }
                        else {
                            $('#comune_nascita').val(null);
                            $('#comune_nascita').attr('disabled', false);
                            $('#comune_nascita').attr('readonly', 'readonly');
                            $('#comune_nascita').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
                            $('#dati_sogg_prov_nasc').attr('disabled', false);
                            $('.provincia_dati_sogg').show();
                        }
                    }
                    else if (selectRif == 2) {
											//alert("callParent stato 2");
                        paese_ritorno = valorediritorno.paese;
                        $('#paese_cf').val(paese_ritorno);

												document.getElementById("paese_cf").dispatchEvent(new Event("change"));

                        if (paese_ritorno != "Italia") {
                            $('#comune_cf').val(null);
                            $('#comune_cf').removeClass('sfondo_ricerca').addClass('sfondo_grigio');
                            $('#comune_cf').attr('readonly', false);
                            $('#CC_cf').val(valorediritorno.CC);
                        }
                        else {
                            $('#comune_cf').val(null);
                            $('#comune_cf').attr('disabled', false);
                            $('#comune_cf').attr('readonly', 'readonly');
                            $('#comune_cf').addClass('sfondo_ricerca').removeClass('sfondo_grigio');
                        }
                    }
                    else {
                        paese_ritorno = valorediritorno.paese;
                        $('#paese').val(paese_ritorno);

												document.getElementById("paese").dispatchEvent(new Event("change"));

                        if (paese_ritorno != "Italia") {
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);

                            $('#ID_via_cap').val(1);
                            $('#ID_via').val(0);

                            $('#scelta_indirizzo_1').hide();
                            $('#scelta_indirizzo_2').show();

                            $('#CC').val(valorediritorno.CC);
                            $('#comune').val(null);
                            $('#comune').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
                            $('#comune').attr('readonly', false);
                            $('#dati_sogg_prov').val(null);
                            $('#dati_sogg_prov').attr('readonly', false);
                            $('#frazione').val(null);
                            $('#cap').val(null);
                            $('#cap').attr('readonly', false);
                            $('#via').val(null);
                            $('#via').attr('ondblclick', "RicercheDaId('via',0);");
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            func_stato_estero_indirizzo('nascondi');

                        }
                        else {
                            $('#scelta_indirizzo_2').hide();
                            $('#scelta_indirizzo_1').show();
                            $('#comune').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
                            $('#comune').val(null);
                            $('#comune').attr('readonly', 'readonly');
                            $('#frazione').val(null);
                            $('#dati_sogg_prov').val(null);
                            $('#dati_sogg_prov').attr('disabled', false);
                            $('#cap').attr('readonly', 'readonly');
                            $('#cap').val(null);
                            $('#via').attr('ondblclick', "");
                            $('#via').val(null);
                            $('#via').attr('readonly', 'readonly');
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);
                            func_stato_estero_indirizzo('mostra');
                        }
                    }
                }
                break;

            case "ente":
                if (selectRif == 0) {

                    if (valorediritorno != null && valorediritorno != undefined) {
                        $('#comune_nascita').val(valorediritorno.comune);
                        $('#dati_sogg_prov_nasc').val(valorediritorno.prov_sigla);
                        $('#CC_nascita').val(valorediritorno.CC);
                    }
                }
                else if(selectRif == 2){
                    if (valorediritorno != null && valorediritorno != undefined) {
                        $('#comune_cf').val(valorediritorno.comune);
                        $('#CC_cf').val(valorediritorno.CC);
                    }
                }
                else {


                    if (valorediritorno != null && valorediritorno != undefined) {
											$('#ID_via_cap').val("");
											$('#ID_via').val("");
                        $('#comune').val(valorediritorno.comune);

												document.getElementById("comune").dispatchEvent(new Event("change"));

                        $('#dati_sogg_prov').val(valorediritorno.prov_sigla);
                        $('#CC').val(valorediritorno.CC);

                        pattern_numeri = /[^0-9]/;
                        cap_control = valorediritorno.cap;

                        if (cap_control.match(pattern_numeri)) {
                            cap_control = cap_control.replace('x', 0);
                            cap_control = cap_control.replace('x', 0);
                            $('#via').val(null);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            $('#cap').val(cap_control);
                            $('#cap').attr('readonly', 'readonly');
                            $('#via').attr('ondblclick', "RicercheDaId('indirizzo_generale',0);");
                            $('#via').attr('alt', "cap");
                        }
                        else {
                            $('#via').val(null);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            $('#cap').val(cap_control);
                            $('#cap').attr('readonly', false);
                            $('#via').attr('ondblclick', "RicercheDaId('indirizzo_generale',0);");
                            $('#via').attr('alt', "via");
                        }

                        $('#civico').val(null);
                        $('#esponente').val(null);
                        $('#interno').val(null);
                        $('#dettagli').val(null);
                    }
                }
                break;

            case "indirizzo_generale":
                if(valorediritorno!=null && valorediritorno!=undefined) {

                    tipoRicInd = valorediritorno.tipoRic;

                    if (tipoRicInd == "cap") {
                        if (valorediritorno != null && valorediritorno != undefined) {
                            $('#cap').val(valorediritorno.cap);
                            $('#via').val(valorediritorno.indirizzo);
                            $('#ID_via_cap').val(valorediritorno.ID);
                            $('#ID_via').val(1);
                        }
                    }
                    else if (tipoRicInd == "via") {
                        if (valorediritorno != null && valorediritorno != undefined) {
                            $('#cap').val(valorediritorno.cap);
                            $('#cap').attr('readonly', false);
                            $('#via').val(valorediritorno.indirizzo);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco sfondo_giallo');
                            $('#ID_via').val(valorediritorno.ID);
                            $('#ID_via_cap').val(1);
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);
                        }
                    }
                    else if (valorediritorno == "no_via") {
                        $('#ID_via_cap').val(1);
                        $('#ID_via').val(0);
                        $('#cap').attr('readonly', false);
                        $('#via').attr('readonly', false);
                        $('#via').val(null);
                        $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
                        $('#civico').val(null);
                        $('#esponente').val(null);
                        $('#interno').val(null);
                        $('#dettagli').val(null);
                        alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
                    }
                }
                break;
            case "via":

                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#cap').val(valorediritorno.cap);
                    $('#via').val(valorediritorno.indirizzo);
                    $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco sfondo_giallo');
                    $('#ID_via').val(valorediritorno.ID);
                    $('#ID_via_cap').val(1);
                }
                else
                {
                    $('#ID_via_cap').val(1);
                    $('#ID_via').val(0);
                    $('#via').attr('readonly',false);
                    $('#via').val(null);
                    $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
                    alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
                }

                $('#civico').val(null);
                $('#esponente').val(null);
                $('#interno').val(null);
                $('#dettagli').val(null);

                break;
            case "cap":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#cap').val(valorediritorno.cap);
                    $('#via').val(valorediritorno.indirizzo);
                    $('#ID_via_cap').val(valorediritorno.ID);
                    $('#ID_via').val(1);
                }
                else
                {
                    RicercheDaId('via',0);
                }
                break;
            case "esenzione":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#esenzione').val(valorediritorno.descrizione);
                    $('#ese').val(valorediritorno.ID);
                }
                break;
            case "situazione":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#situazione').val(valorediritorno.descrizione);
                    $('#sit').val(valorediritorno.ID);
                }
                break;
            case "controllo":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#controllo').val(valorediritorno.descrizione);
                    $('#con').val(valorediritorno.ID);
                }
                break;
            case "raggr":

                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#raggr').val(valorediritorno.descrizione);
                    $('#rag').val(valorediritorno.ID);
                }
                break;
            case "sotto_raggr":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#sottoraggr').val(valorediritorno.descrizione);
                    $('#sot').val(valorediritorno.ID);
                }
                break;

        }
}*/
    //Variabili per funzione RicercheDaId()
	var selectParent = "";
	var selectRif = "";
    //Variabili per passare i dati di residenza alla modale
    var state_ = "";                                                // paese
    var addr_S = "";                                                // tipo ricerca
    var addr_c = "";                                                // nome comune
    var addr = "";                                                  // input indirizzo
    var addr_CC = "";                                               // cod. catastale comune

    //Gestione del pulasnte di inserimento manuale indirizzo
    function insAddr(){
        //Contiene ID di comune cappato
        if ($("#ID_via_cap").val() > 1) {
            alert("Hai selezionato un indirizzo cappato, e quindi non è possibile abilitare la scrittura.");
        }
        //Settatto a 1 perchè comune non cappato
        else if ($("#ID_via_cap").val() == 1) {
            ctrl_giallo = $('#via').hasClass('sfondo_giallo');
            // cosa segnifica la classe 'sfondo_giallo'?
            if (ctrl_giallo == false) {
                $('#via').prop('readonly', false).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
                $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
                $('#via').css("background-color","");
                alert("Ora e' possibile modificare l'indirizzo. Terminata l'operazione cliccare nuovamente sulla gomma.\n\nSi ricorda che questa funzione serve per correggere errori di battitura e non per inserire un nuovo indirizzo.");
                $('#via').focus();
            }
            else if (ctrl_giallo == true) {
                //Controllo campo riempito
                if($('#via').val() != ""){
                    $('#via').prop('readonly', true).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
                    alert("Operazione effettuata correttamente");
                    $('#via').css("background-color", "rgb(153, 204, 255)");
                    //$('#via').focus();
                } else {
                    alert("Inserire indirizzo corretto");
                }

            }
        }
        //Tentativo di inserimento manuale senza aver fatto ricerca
        else {
            alert("Prima di inserire manualmente l'indirizzo effettuare la ricerca");
        }
    }


    //Apertura modale modifica campo
    function openOfcanvas(id_off,rif){
        //alert($('#via').attr('alt'));
        //alert(adminCity);
        //controllo se ricerca indirizzo
        flagAQjaxReserch = true;
        if (id_off=='addrSearchModal'){
            //Inizializzazione dati per ricerca indirizzo
            addr_S = $('#via').attr('alt');                         // tipo ricerca
            addr_c = $('#comune').val();                            // nome comune
            addr = $('#via').val();                                 // indirizzo
            addr_CC = $('#CC').val();                               // cod. catastale comune
            <!-- Visualizzazione tipo di ricerca -->
            //Controllo tipo di ricerca indirizzo
            $('#addr_c').val(addr) ;
            $('#addr_g').val(addr) ;
            //Comune cappato
            if(addr_S == 'cap'){
                document.getElementById('addrSearchModalLabel_nc').hidden = true;
                document.getElementById('ins_addr_nc').hidden = true;
                $('#comune_c').val(addr_c);
                document.getElementById('check_cap').checked = true;
                document.getElementById('check_gen').checked = false;
                <!-- Resetta gli hidden se si cambia due volte città di cui una è cappata e l'altra no -->
                document.getElementById('checkbox_c').hidden = false;
                document.getElementById('addrSearchModalLabel_c').hidden = false;
                document.getElementById('ins_addr_c').hidden = false;
                $('#'+id_off).modal('show');
                selectRif = rif;
            }
            //Comune non cappato
            else if(addr_S == 'via'){
                document.getElementById('addrSearchModalLabel_c').hidden = true;
                document.getElementById('checkbox_c').hidden = true;
                document.getElementById('ins_addr_c').hidden = true;
                <!-- Resetta gli hidden se si cambia due volte città di cui una è cappata e l'altra no -->
                document.getElementById('addrSearchModalLabel_nc').hidden = false;
                document.getElementById('ins_addr_nc').hidden = false;
                $('#'+id_off).modal('show');
                selectRif = rif;
            }
            //Nessun comune selezionato => $('#via').attr('alt') = " "
            else
            {
                alert("Prima di cercare l'indirizzo svolgere la ricerca del comune");
            }
        }
        //Ricerca Paese o Comune
        else {
            state_ = $('#paese').val();                             // Paese
            $('#state').val(state_) ;                               // Carico paese nel campo di ricerca della modale
            addr_c = $('#comune').val();                            // Comune
            $('#city').val(addr_c) ;                                // Carico comune nel campo di ricerca della modale
            $('#'+id_off).modal('show');
            selectRif = rif;
        }
    }

    //invio richiesta elenco paesi
    function startAjax(type){
        switch (type){
            // ricerca Paese
            case 'state':
                $.ajax({
                    url: "./ajax/selectState.php",                                  // url pagina che farà query
                    type: "POST",                                                   // create an ajax request to display.php
                    dataType: "json",                                               // expect json to be returned
                    data: {
                        state: $("#state").val()                                    // parametro ricerca inserito
                    },
                    success: function(response){
                        //response = JSON.parse(response)
                        //console.log(response);
                        //$("#appendTableState").html(response);          // ??????
                        //alert(response);
                        var toprint = [
                            {originalName: "CC", replacedName: "Codice Paese"},
                            {originalName: "paese", replacedName: "Nome Paese"},
                            {originalName: "select", replacedName: ""},
                            {originalName: "action_row", replacedName: "", type: "action"}
                        ];
                        var widthCell = ["25%","60%","5%"];
                        var fontsize = "12px";
                        var idTable = "appendTableState";
                        var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                    },
                    error: function(risposta){
                        alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                    }
                });
                break;
            // ricerca comune
            case 'city':
                $.ajax({
                    url: "./ajax/selectCity.php",                                   // url pagina che farà query
                    type: "POST",                                                   // create an ajax request to display.php
                    dataType: "json",                                               // expect json to be returned
                    data: {
                        city: $("#city").val()                                      // parametro ricerca inserito
                    },
                    success: function(response){
                        var toprint = [
                            {originalName: "nome", replacedName: "Comune"},
                            {originalName: "cap", replacedName: "Cap"},
                            {originalName: "prov", replacedName: "Provincia"},
                            {originalName: "CC_C", replacedName: "Cod. Com."},
                            {originalName: "CC_P", replacedName: "Cod. Prov."},
                            {originalName: "select", replacedName: ""},
                            {originalName: "action_row", replacedName: "", type: "action"}
                        ];
                        var widthCell = ["35%","15","15","15%","15%","5%"];
                        var fontsize = "12px";
                        var idTable = "appendTableCity";
                        var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                    },
                    error: function(risposta){
                        alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                    }
                });
                break;
            // ricerca indirizzo cappato
            case 'addr_cap':
                $.ajax({
                    url: "./ajax/selectAddrCap.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        city_cc: $('#CC').val(),                         // Parametro di ricerca: cod catastale comune cappato
                        addr_c : $('#addr_c').val()                      // Parametro di ricerca: inserimento utente
                    },
                    success: function(response) {
                        if ($.isEmptyObject(response)===true){
                            //$(".offcanvas").modal("hide"); -->  no
                            //alert errore no indirizzo
                            if(confirm("Non è stato trovato nessun indirizzo corrispondente nel comune selezionato. Procedere con ricerca generica?")){
                                //Va alla ricerca generica
                                // addr_S valorizzata per via e non cap
                                addr_S = 'via';
                                // disabilita radio cap
                                document.getElementById('check_cap').checked = false;
                                document.getElementById('check_cap').disabled = true;
                                document.getElementById('check_cap_label').style.color = "#999999";
                                // seleziona radio via
                                document.getElementById('check_gen').checked = true;
                                // scatena evento onclick sul radio via
                                <!-- nasconde ricerca cappata -->
                                document.getElementById('addrSearchModalLabel_c').hidden = true;
                                document.getElementById('ins_addr_c').hidden = true;
                                <!-- mostra ricerca generica -->
                                document.getElementById('addrSearchModalLabel_nc').hidden = false;
                                document.getElementById('ins_addr_nc').hidden = false;
                                <!-- Cambio tipo di ricerca -->
                                addr_S = 'via';
                            }
                            else{
                                //Torna alla ricerca cappata;
                            }
                        }
                        else {
                            var toprint = [
                                {originalName: "nome_via", replacedName: "Indirizzo"},
                                {originalName: "civici", replacedName: "Civici"},
                                {originalName: "cap", replacedName: "CAP"},
                                {originalName: "select", replacedName: ""},
                                {originalName: "action_row", replacedName: "", type: "action"}
                            ];
                            var widthCell = ["55%", "30%", "10%", "5%"];
                            var fontsize = "12px";
                            var idTable = "appendTableAddr";
                            var test = new TableGenerator(response, toprint, widthCell, fontsize, idTable);
                        }
                    },
                    error: function(risposta){
                        alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                    }
                });
                break;
            // ricerca indirizzo generico
            case 'addr_gen' :
                //alert("Ricerca generale");                                  // Controllo
                $.ajax({
                    url: "./ajax/selectAddrGen.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        city_cc: $('#CC').val(),                            // Parametro di ricerca: cod catastale comune
                        admin: adminCity,                                   // Ente?
                        addr_g: $('#addr_g').val()                          // Parametro di ricerca: inserimento utente
                    },
                    success: function(response) {
                        if ($.isEmptyObject(response)===true){
                            //$(".offcanvas").modal("hide"); -->  no
                            //alert errore no indirizzo
                            if(confirm("Non è stato trovato nessun indirizzo corrispondente nel comune selezionato. Procedere con inserimento manuale?")){
                                //Permette l'inserimento diretto
                                //Chiudo modale
                                $(".offcanvas").modal("hide");
                                //Setto i campi della pagina per l'inserimento
                                $('#ID_via_cap').val(1);
                                $('#ID_via').val(0);
                                $('#cap').attr('readonly', false);
                                $('#via').attr('readonly', false);
                                $('#via').val(null);
                                $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
                                $('#via').css("background-color","");
                                $('#civico').val(null);
                                $('#esponente').val(null);
                                $('#interno').val(null);
                                $('#dettagli').val(null);
                                alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.' +
                                        '\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
                                document.getElementById("via").dispatchEvent(new Event("change"));
                                $('#via').focus();
                            }
                            else{
                                //Torna alla ricerca generica;
                            }
                        }
                        else {
                            var toprint = [
                                {originalName: "nome_via", replacedName: "Indirizzo"},
                                {originalName: "comune", replacedName: "Comune"},
                                {originalName: "cap", replacedName: "CAP"},
                                {originalName: "select", replacedName: ""},
                                {originalName: "action_row", replacedName: "", type: "action"}
                            ];
                            var widthCell = ["55%", "30%", "10%", "5%"];
                            var fontsize = "12px";
                            var idTable = "appendTableAddr";
                            var test = new TableGenerator(response, toprint, widthCell, fontsize, idTable);
                        }
                    },
                    error: function(risposta){
                        alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                    }
                });
                break;
        }
    }
    // Sostituzione da modale
    function initialId(tipo,val){
        //alert("initial --> "+tipo);
        flagAQjaxReserch = false;
        switch(tipo)
        {
            // Sostituzione paese
            case "state":
                //console.log(val);
                //$("#via_completa"+endIdAccDic).val(val["DUG_Odonimo"]+" "+val["DUF_Odonimo"]);
                //$("#paese_nascita").val(val["paese"]);
                //$("#CC_nascita").val(val["CC"]);
                //console.log(val);
                //alert(val.length);
                if($.isEmptyObject(val)===false)
                {
                    if (selectRif == 0) {

                        paese_ritorno = val["paese"];
                        $('#paese_nascita').val(paese_ritorno);
                        //alert("parent "+$('#paese_nascita').val());
                        document.getElementById("paese_nascita").dispatchEvent(new Event("change"));
                        if (paese_ritorno != "Italia") {
                            $('#comune_nascita').val(null);
                            $('#comune_nascita').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
                            $('#comune_nascita').attr('readonly', false);
                            $('#dati_sogg_prov_nasc').val(null);
                            $('#dati_sogg_prov_nasc').attr('disabled', 'disabled');
                            $('#CC_nascita').val(val["CC"]);
                            $('.provincia_dati_sogg').hide();
                        }
                        else {
                            $('#comune_nascita').val(null);
                            $('#comune_nascita').attr('disabled', false);
                            $('#comune_nascita').attr('readonly', 'readonly');
                            $('#comune_nascita').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
                            $('#dati_sogg_prov_nasc').attr('disabled', false);
                            $('.provincia_dati_sogg').show();
                        }
                    }
                    else if (selectRif == 2) {
                        paese_ritorno = val["paese"];
                        $('#paese_cf').val(paese_ritorno);

                        document.getElementById("paese_cf").dispatchEvent(new Event("change"));

                        if (paese_ritorno != "Italia") {
                            $('#comune_cf').val(null);
                            $('#comune_cf').removeClass('sfondo_ricerca').addClass('sfondo_grigio');
                            $('#comune_cf').attr('readonly', false);
                            $('#CC_cf').val(val["CC"]);
                        }
                        else {
                            $('#comune_cf').val(null);
                            $('#comune_cf').attr('disabled', false);
                            $('#comune_cf').attr('readonly', 'readonly');
                            $('#comune_cf').addClass('sfondo_ricerca').removeClass('sfondo_grigio');
                        }
                    }
                    else {
                        paese_ritorno = val["paese"];
                        $('#paese').val(paese_ritorno);

                        document.getElementById("paese").dispatchEvent(new Event("change"));

                        if (paese_ritorno != "Italia") {
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);

                            $('#ID_via_cap').val(1);
                            $('#ID_via').val(0);

                            $("#comune").removeClass( "validateCustom vld_Custom_r");
                            $("#cap").removeClass( "validateCustom vld_Custom_r");

                            $('#scelta_indirizzo_1').hide();
                            $('#scelta_indirizzo_2').show();
                            $("#via").removeClass("validateCustom vld_Custom_r");
                            $("#via_estero").addClass("validateCustom vld_Custom_r");

                            $('#CC').val(val["CC"]);

                            $('#comune').val(null);
                            $('#comune').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
                            $('#comune').attr('readonly', false);
                            $('#comune').css("background-color","");
                            $('#comune').css("border","");
                            /*$('#comune').addClass("validateCustom vld_Custom_r");

                            var arrayClass = document.getElementById("comune").className.split(/\s+/);
                            var allClass = "";
                            for(var x = 0; x<arrayClass.length; x++)
                            {
                                allClass = allClass + " " + arrayClass[x];
                            }
                            alert(allClass);*/
                            $('#dati_sogg_prov').val(null);
                            $('#dati_sogg_prov').attr('readonly', false);
                            $('#frazione').val(null);
                            $('#cap').val(null);
                            $('#cap').attr('readonly', false);
                            $('#via').val(null);
                            $('#via').attr('ondblclick', "/*RicercheDaId('via',0);*/openOfcanvas('addrSearchModal',0);");
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            //func_stato_estero_indirizzo('nascondi');

                        }
                        else {
                            $("#comune").addClass("validateCustom vld_Custom_r");
                            $("#cap").addClass("validateCustom vld_Custom_r");
                            $('#scelta_indirizzo_2').hide();
                            $('#scelta_indirizzo_1').show();
                            $("#via_estero").removeClass("validateCustom vld_Custom_r");
                            $("#via").addClass("validateCustom vld_Custom_r");

                            $('#comune').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
                            $('#comune').val(null);
                            $('#comune').attr('readonly', 'readonly');
                            $('#comune').css("background-color","rgb(153, 204, 255)");
                            $('#comune').css("border","2px solid black");
                            //$('#comune')

                            $('#frazione').val(null);
                            $('#dati_sogg_prov').val(null);
                            $('#dati_sogg_prov').attr('disabled', false);
                            $('#cap').attr('readonly', 'readonly');
                            $('#cap').val(null);
                            $('#via').attr('ondblclick', "");
                            $('#via').val(null);
                            $('#via').attr('readonly', 'readonly');
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);
                            //func_stato_estero_indirizzo('mostra');
                        }
                    }
                }
                break;
            // Sostituzione comune
            case "city":
                //alert("menu ente");

                if (selectRif == 0) {

                    if ($.isEmptyObject(val)===false) {
                        $('#comune_nascita').val(val["nome"]);
                        $('#dati_sogg_prov_nasc').val(val["prov"]);
                        $('#CC_nascita').val(val["CC_C"]);
                        //alert("1 ente call");
                        document.getElementById("comune_nascita").dispatchEvent(new Event("change"));
                    }
                }
                else if(selectRif == 2){
                    if ($.isEmptyObject(val)===false) {
                        $('#comune_cf').val(val["nome"]);
                        $('#CC_cf').val(val["CC_C"]);
                        //alert("2 ente call");
                    }
                }
                else {

                    //alert("menu ente 1");
                    if ($.isEmptyObject(val)===false) {
                        $('#ID_via_cap').val("");
                        $('#ID_via').val("");
                        $('#comune').val(val["nome"]);

                        document.getElementById("comune").dispatchEvent(new Event("change"));

                        $('#dati_sogg_prov').val(val["prov"]);
                        $('#CC').val(val["CC_C"]);

                        pattern_numeri = /[^0-9]/;
                        cap_control = val["cap"];

                        if (cap_control.match(pattern_numeri)) {
                            cap_control = cap_control.replace('x', 0);
                            cap_control = cap_control.replace('x', 0);
                            $('#via').val(null);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            $('#via').css("background-color","rgb(153, 204, 255)");
                            $('#via').css("border","2px solid black");
                            $('#cap').val(cap_control);
                            document.getElementById("cap").dispatchEvent(new Event("change"));
                            $('#cap').attr('readonly', 'readonly');
                            $('#via').attr('ondblclick', "/*RicercheDaId('indirizzo_generale',0);*/openOfcanvas('addrSearchModal',0);");
                            $('#via').attr('alt', "cap");
                        }
                        else {
                            $('#via').val(null);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            $('#cap').val(cap_control);
                            document.getElementById("cap").dispatchEvent(new Event("change"));
                            $('#cap').attr('readonly', false);
                            $('#via').attr('ondblclick', "/*RicercheDaId('indirizzo_generale',0);*/openOfcanvas('addrSearchModal',0);");
                            $('#via').attr('alt', "via");
                        }

                        $('#civico').val(null);
                        $('#esponente').val(null);
                        $('#interno').val(null);
                        $('#dettagli').val(null);
                    }
                }
                break;
            // Sostituzione indirizzo cappato
            case "addr_cap":
                // Non controllo se val è vuoto perchè non può esserlo: abbiamo controllo su risultato vuoto indirizi cappati che nel caso manda a Ricerca generica
                $('#cap').val(val["cap"]);
                $('#via').val(val["nome_via"]);
                $('#ID_via_cap').val(val["id"]);
                $('#ID_via').val(1);

                document.getElementById("via").dispatchEvent(new Event("change"));

                break;
            // Sostituzione indirizzo
            case "addr_gen":
                $('#cap').val(val["cap"]);
                $('#via').val(val["nome_via"]);
                $('#ID_via_cap').val(1);
                $('#ID_via').val(val["id"]);
                $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco sfondo_giallo');
                $('#civico').val(null);
                $('#esponente').val(null);
                $('#interno').val(null);
                $('#dettagli').val(null);

                document.getElementById("via").dispatchEvent(new Event("change"));

                break;

            default: alert("Ricerca non trovata!"); break;
        }

    }

	function RicercheDaId(value, rif) {
		selectParent = value;
		selectRif = rif;
		var valorediritorno = 0;
		//var strDim = Dim_Alert(600, 300);

		switch (value) {
			case "utente":

				//strDim = Dim_Alert(600, 300);
				var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				//valorediritorno = window.showModalDialog(stringa,"", strDim);

				openWindowSearch(stringa, {
					width: 600,
					height: 300,
					left: (($(window).width() / 2) - 300),
					top: (($(window).height() / 2) - 150)
				});

				break;

			case "stato":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(600, 300);
					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricPaese";
					//valorediritorno = window.showModalDialog(stringa, "", strDim);

					openWindowSearch(stringa, {
						width: 600,
						height: 300,
						left: (($(window).width() / 2) - 300),
						top: (($(window).height() / 2) - 150)
					});

				}
				break;

			case "ente":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(600, 300);
					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

					if (($('#paese_nascita').val() == "Italia" && rif == 0) || ($('#paese').val() == "Italia" && rif == 1) || ($('#paese_cf').val() == "Italia" && rif == 2)) {
						//valorediritorno = window.showModalDialog(stringa, "", strDim);

						openWindowSearch(stringa, {
							width: 600,
							height: 300,
							left: (($(window).width() / 2) - 300),
							top: (($(window).height() / 2) - 150)
						});
					}
				}
				break;

			case "indirizzo_generale":
				if (modalita == "modifica") {

					//strDim = Dim_Alert(750, 400);
					pvia = $('#via').val();
					pcomune = $('#comune').val();
					pCC = $('#CC').val();
					tipoRicInd = $('#via').attr('alt');

					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=indirizzo_generale&via_ric=" + pvia + "&pc=" + pcomune + "&pCC=" + pCC + "&tipoRicInd=" + tipoRicInd + "&c=<?php echo $c; ?>";
					//valorediritorno = window.showModalDialog(stringa, "", strDim);

					openWindowSearch(stringa, {
						width: 750,
						height: 400,
						left: (($(window).width() / 2) - 375),
						top: (($(window).height() / 2) - 200)
					});

				}

				break;

			case "via":

				if (modalita == "modifica") {

					if (rif == 1) {
						//	if($('#ID_via_cap').val() == 1 && $('#via').val() != null && $('#via').val()!="")
						//{
						if ($("#ID_via_cap").val() > 1) {
							alert("Hai selezionato un indirizzo cappato, e quindi non è possibile abilitare la scrittura.");
						} else if ($("#ID_via_cap").val() == 1) {
							ctrl_giallo = $('#via').hasClass('sfondo_giallo');

							if (ctrl_giallo == false) {
								$('#via').prop('readonly', false).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
                                $('#via').css("background-color","");
								alert("Ora e' possibile modificare l'indirizzo. Terminata l'operazione cliccare nuovamente sulla gomma." +
                                    "\n\nSi ricorda che questa funzione serve per correggere errori di battitura e non per inserire un nuovo indirizzo.");
								$('#via').focus();
							} else if (ctrl_giallo == true) {
								$('#via').prop('readonly', true).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
								alert("Operazione effettuata correttamente");
							}
						} else {
							alert("Prima di inserire manualmente l'indirizzo effettuare la ricerca");
						}

						//	}
					} else {

						//strDim = Dim_Alert(600, 300);
						pCC = $('#CC').val();
						pvia = $('#via').val();

						var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricIndirizzo&pCC=" + pCC + "&via_ric=" + pvia + "&c=<?php echo $c; ?>";
						//valorediritorno = window.showModalDialog(stringa, "", strDim);

						openWindowSearch(stringa, {
							width: 600,
							height: 300,
							left: (($(window).width() / 2) - 300),
							top: (($(window).height() / 2) - 150)
						});

					}
				}
				break;

			case "cap":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(750, 400);
					pvia = $('#via').val();
					pcomune = $('#comune').val();
					pCC = $('#CC').val();

					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricCap&via_ric=" + pvia + "&pc=" + pcomune + "&pCC=" + pCC;
					//valorediritorno = window.showModalDialog(stringa, "", strDim);

					openWindowSearch(stringa, {
						width: 750,
						height: 400,
						left: (($(window).width() / 2) - 375),
						top: (($(window).height() / 2) - 200)
					});
				}

				break;

			case "esenzione":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(370, 330);
					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_esenzione&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
					//valorediritorno = window.showModalDialog(stringa,"", strDim);

					openWindowSearch(stringa, {
						width: 370,
						height: 330,
						left: (($(window).width() / 2) - 185),
						top: (($(window).height() / 2) - 165)
					});
				}
				break;

			case "situazione":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(370, 330);
					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_situazione&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
					//valorediritorno = window.showModalDialog(stringa,"", strDim);

					openWindowSearch(stringa, {
						width: 370,
						height: 330,
						left: (($(window).width() / 2) - 185),
						top: (($(window).height() / 2) - 165)
					});

				}
				break;

			case "controllo":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(370, 330);
					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_controllo&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
					//valorediritorno = window.showModalDialog(stringa,"", strDim);

					openWindowSearch(stringa, {
						width: 370,
						height: 330,
						left: (($(window).width() / 2) - 185),
						top: (($(window).height() / 2) - 165)
					});
				}
				break;

			case "raggr":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(370, 330);
					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_raggr&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
					//valorediritorno = window.showModalDialog(stringa,"", strDim);

					openWindowSearch(stringa, {
						width: 370,
						height: 330,
						left: (($(window).width() / 2) - 185),
						top: (($(window).height() / 2) - 165)
					});
				}
				break;

			case "sotto_raggr":
				if (modalita == "modifica") {
					//strDim = Dim_Alert(370, 330);
					var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_sotto_raggr&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
					//valorediritorno = window.showModalDialog(stringa,"", strDim);

					openWindowSearch(stringa, {
						width: 370,
						height: 330,
						left: (($(window).width() / 2) - 185),
						top: (($(window).height() / 2) - 165)
					});
				}
				break;

		}
	}
</script>


<script>
	if (utente_ID == "") {
		if (prev_utente != "0")
			$('#F7').attr("onMouseover", "title='Ultimo record F7'");

		if (next_utente != "0")
			$('#F8').attr("onMouseover", "title='Primo record F8'");
	} else {
		if (prev_utente == "" && next_utente != "") {
			$('#F7').attr("onMouseover", "title='Nessun record F7 (Primo record selezionato)'");
			$('#F8').attr("onMouseover", "title='Record successivo F8 (Primo record selezionato)'");
		}

		if (next_utente == "" && prev_utente != "") {
			$('#F7').attr("onMouseover", "title='Record precedente F7 (Ultimo record selezionato)'");
			$('#F8').attr("onMouseover", "title='Nessun record F8 (Ultimo record selezionato)'");
		}
	}
</script>

<?php
$menuPageNumber = "Pag 1/7";
$pagina = "dati_soggetto.php";
$submenuPageNo = 1;
$pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';
include_once(INC . "/submenu_anagrafe.php");
include_once(INC . "/pages_authorization.php");
?>




<form id=anagrafe_form class="form-horizontal validate" name=dati_soggetto action="dati_soggetto_salva.php" method=post>

	<input name=ID_via id=ID_via type=hidden value="<?php echo $ID_via; ?>">
	<input name=ID_via_cap id=ID_via_cap type=hidden value="<?php echo $ID_via_cap; ?>">
	<input name=ID_res type=hidden value="<?php echo $ID_res; ?>">
	<input name=data_registrazione type=hidden value="<?php echo $data_registrazione; ?>">
	<input name=CC_nascita id=CC_nascita type=hidden value="<?php echo $CC_nascita; ?>">
	<input name=CC_residenza id=CC type=hidden value="<?php echo $CC_res; ?>">
	<input name=a type=hidden value="<?php echo $a; ?>">
	<input name=p type=hidden value="<?php echo $p; ?>">
	<input name=comune_id type=hidden value="<?php echo $comune_id; ?>">
	<input name=c type=hidden value="<?php echo $c; ?>">
	<input name=servizio type=hidden value="<?php echo $servizio; ?>">
	<input name=invia_submit id=invia_submit type=hidden value="">
	<input id=CC_cf type=hidden>
	<div id=content>

		<div class="row justify-content-md-center ">
			<div class="col col-md-auto text_center">
				<span class="titolo font16 under_decor">Dati Soggetto</span>
			</div>
		</div>

		<div class="row">
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
					<div class="col-lg-12 resize">
						<input id=radioM type=radio name=genere value=M onclick="radioClicked('M');" tabindex=2 <?php echo $chkM; ?> <?php echo $disabled; ?> checked>Maschio
						<input id=radioF type=radio name=genere value=F onclick="radioClicked('F');" tabindex=2 <?php echo $chkF; ?> <?php echo $disabled; ?>>Femmina
						<input id=radioD type=radio name=genere value=D onclick="radioClicked('D');" tabindex=2 <?php echo $chkD; ?> <?php echo $disabled; ?>>Ditta &nbsp; *
					</div>
				</div>
			</div>
			<div class="col col-lg-5 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-6 control-label resize" style="text-align: left;">Data Registrazione</label>
					<div class="col-lg-6 resize">
						<b><?php echo $data_registrazione; ?></b>
					</div>
				</div>
			</div>
		</div>
		<div>
			<div id=tab_soggetto>
				<div style="border-top: 2px solid #B0BBE8; width: 30%; margin-left: 35%;margin-bottom: 1%;"></div>

				<div class="row justify-content-md-center ">
					<div class="col col-md-auto text_center">
						<span>
							<p class="sezioni_tab">DATI UTENTE</p>
						</span>
					</div>
				</div>

				<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

				<div class="row">
					<div class="col col-lg-3 col-lg-offset-1">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Cognome *</label>
							<div class="col-lg-8">
								<input tabindex=3 id=cognome_utente class=" <?php echo $class; ?> form-control resize validateCustom vld_Custom_r" name=cognome_utente type=text value="<?php echo $cognome_utente; ?>" size=24 border=1 <?php echo $readonly; ?>>
							</div>
						</div>
					</div>
					<div class="col col-lg-3">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Nome *</label>
							<div class="col-lg-8">
								<input tabindex=4 id=nome_utente class=" <?php echo $class; ?> form-control resize validateCustom vld_Custom_r" name=nome_utente type=text value="<?php echo $nome_utente; ?>" size=24 border=1 <?php echo $readonly; ?>>
							</div>
						</div>
					</div>
					<div class="col col-lg-4">
						<div class="form-group">
							<label class="col-lg-5 control-label resize" style="text-align: left;">Impresa Individuale</label>
							<div class="col-lg-7">
								<select id=forma_giuridica_persona name=forma_giuridica_persona tabindex="4" class="form-control resize" onchange="changeFormaPersona();">
									<option></option>
									<?php echo $options_libero; ?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-lg-3 col-lg-offset-1">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Stato Nascita</label>
							<div class="col-lg-8">
								<input id=paese_nascita tabindex=5 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=paese_nascita type=text value="<?php echo $paese_nasc_utente; ?>" size=24 ondblClick="/*RicercheDaId('stato',0);*/openOfcanvas('stateSearchModal',0);" onchange="settaInput(1);" readonly>
							</div>
						</div>
					</div>
                    <div class="col col-lg-3">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Comune Nascita</label>
							<div class="col-lg-8">
								<input id=comune_nascita tabindex=6 class=" <?php echo $class_ric; ?> form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=comune_nascita type=text value="<?php echo $comune_nasc_utente; ?>" size=24 ondblClick="/*RicercheDaId('ente',0);*/openOfcanvas('citySearchModal',0);" readonly>
							</div>
						</div>
					</div>
					<div class="col col-lg-2">
						<div class="form-group">
							<label id="label_prov_1" class="col-lg-4 control-label resize" style="text-align: left;">Prov.</label>
							<div class="col-lg-8">
								<input id=dati_sogg_prov_nasc tabindex="7" class=" <?php echo $class; ?> provincia_dati_sogg form-control resize vld_esp" type=text name=provNascDatiSogg value="<?php echo $provincia_nasc_utente; ?>" size=2>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-lg-3 col-lg-offset-1">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Data Nascita</label>
							<div class="col-lg-8">
								<input id=data_nascita tabindex=8 class="text_center <?php echo $class; ?> form-control resize vld_date" name=data_nascita type=text value='<?php echo $data_nasc_utente; ?>' size=9 <?php echo $readonly; ?>>
							</div>
						</div>
					</div>
					<div class="col col-lg-3">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Data Morte</label>
							<div class="col-lg-8">
								<input id=data_morte tabindex=9 class="text_center <?php echo $class; ?> form-control resize vld_date" name=data_morte type=text value='<?php echo $data_morte_utente; ?>' size=9 <?php echo $readonly; ?>>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-lg-3 col-lg-offset-1">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Codice Fiscale</label>
							<div class="col-lg-8">
								<input tabindex=10 id=CF type="text" class=" <?php echo $class_calcolo; ?> form-control resize vld_CF" name="CF" value="<?php echo $CF; ?>" size=24 <?php echo $readonly; ?>>
							</div>
						</div>
					</div>
					<div class="col col-lg-1">
						<div class="form-group">
							<div class="col-lg-12">
								<input class="btn btn-primary resize" type=button name="calcola" value="Calcola CF" onclick="controllaCampi(1);">
							</div>
						</div>
					</div>
					<div class="col col-lg-1">
						<div class="form-group">
							<div class="col-lg-6">
								<input class="btn btn-primary resize" type=button name="decode" value="Decodifica CF" onclick="decodifica();">
							</div>
						</div>
					</div>
					<div class="col col-lg-1 col-lg-offset-4">
						<div class="form-group">
							<div class="col-lg-12">
								<input class="btn btn-primary resize" type=button name="omonimia" value="Check" onclick="controllaCampi(5);">
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-lg-3 col-lg-offset-1">
						<div class="form-group">
							<label class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva</label>
							<div class="col-lg-8">
								<input id=PI_persona type="text" class="form-control resize vld_PI" name="PI_persona" value="<?php echo $PI_persona; ?>" size=24>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id=tab_ditta>

			<div style="border-top: 2px solid #B0BBE8; width: 30%; margin-left: 35%;margin-bottom: 1%;"></div>

			<div class="row justify-content-md-center ">
				<div class="col col-md-auto text_center">
					<span>
						<p class="sezioni_tab">DATI DITTA</p>
					</span>
				</div>
			</div>

			<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

			<div class="row">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Ditta *</label>
						<div class="col-lg-8">
							<input id=ditta tabindex=3 name=ditta class=" <?php echo $class; ?> form-control resize vld_req" type=text value="<?php echo $ditta; ?>" size=24 ondblclick="RicercheDaId('contr',0);" <?php echo $readonly; ?>>
						</div>
					</div>
				</div>
				<div class="col col-lg-5 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Forma giuridica</label>
						<div class="col-lg-8">
							<select id=forma_giuridica tabindex="4" class="form-control resize" name=forma_giuridica onchange="changeFormaGiuridica();">
								<option></option>
								<optgroup label="Impresa individuale"><?php echo $options_individuale; ?></optgroup>
								<optgroup label="Societa' di persone"><?php echo $options_persone; ?></optgroup>
								<optgroup label="Societa' di capitale"><?php echo $options_capitale; ?></optgroup>
								<optgroup label="Societa' cooperativa"><?php echo $options_cooperativa; ?></optgroup>
								<optgroup label="Societa' consortile"><?php echo $options_consortile; ?></optgroup>
								<optgroup label="Ente"><?php echo $options_ente; ?></optgroup>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva</label>
						<div class="col-lg-8">
							<input maxlength="11" tabindex=5 id=PI type="text" name="PI" class=" <?php echo $class; ?> form-control resize vld_PI" value="<?php echo $PI; ?>" size=24 ondblClick="RicercheDaId('CF',0);" <?php echo $readonly; ?>>
						</div>
					</div>
				</div>
				<div class="col col-lg-5 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Azienda (INPS)</label>
						<div class="col-lg-8">
							<input maxlength="10" tabindex=5 id=azienda type="text" name="azienda" class=" <?php echo $class; ?> form-control resize" value="<?php echo $azienda; ?>" size=24 ondblClick="" <?php echo $readonly; ?>>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Prec. denomin.</label>
						<div class="col-lg-8">
							<input id=prec_den tabindex=6 type="text" class=" <?php echo $class; ?> form-control resize" name="prec_den" value="<?php echo $prec_den_ditta; ?>" size="24" <?php echo $readonly; ?>>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Anno Cambio</label>
						<div class="col-lg-8">
							<input id=anno_cambio tabindex=7 class="form-control resize vld_int <?php echo $class; ?>" type="text" name="anno_cambio" value="<?php echo $anno_cambio_ditta; ?>" size="4" <?php echo $readonly; ?>>
						</div>
					</div>
				</div>
				<div class="col col-lg-1 col-lg-offset-6">
					<div class="form-group">
						<div class="col-lg-12">
							<input class="btn btn-primary resize" type=button name="omonimia2" value="Check" onclick="controllaCampi(5);">
						</div>
					</div>
				</div>
			</div>

			<div class="cf_mask" style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; display: none;"></div>

			<div class="row cf_mask" style="display: none;">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Cognome</label>
						<div class="col-lg-8">
							<input id=cognome_cf name=cognome_cf type=text size=24 border=1 class="form-control resize" value="<?php echo $cognome_utente; ?>">
						</div>
					</div>
				</div>
				<div class="col col-lg-3">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Nome</label>
						<div class="col-lg-8">
							<input tabindex=4 id=nome_cf name=nome_cf type=text size=24 border=1 class="form-control resize" value="<?php echo $nome_utente; ?>">
						</div>
					</div>
				</div>
				<div class="col col-lg-2 col-lg-offset-2">
					<div class="form-group">
						<div class="col-lg-12 resize">
							<input id=radioM_cf type=radio name="genere_cf" <?= $chk_cfM; ?> value=M>M
							<input id=radioF_cf type=radio name="genere_cf" <?= $chk_cfF; ?> value=F>F
						</div>
					</div>
				</div>
			</div>


			<div class="row cf_mask" style="display: none;">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Stato Nascita</label>
						<div class="col-lg-8">
							<input id=paese_cf name="paese_cf" class="form-control resize <?php echo $class_ric; ?>" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text size=24 value="Italia" ondblClick="/*RicercheDaId('stato',2);*/openOfcanvas('stateSearchModal',2);" onchange="settaInput(3);" readonly>
						</div>
					</div>
				</div>
				<div class="col col-lg-3">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Comune Nascita</label>
						<div class="col-lg-8">
							<input id=comune_cf name="comune_cf" class="form-control resize <?php echo $class_ric; ?>" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text size=24 ondblClick="/*RicercheDaId('ente',2);*/openOfcanvas('citySearchModal',2);" readonly>
						</div>
					</div>
				</div>
			</div>

			<div class="row cf_mask" style="display: none;">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Data Nascita</label>
						<div class="col-lg-8">
							<input id=data_cf name="data_cf" class="text_center form-control resize vld_date" type=text size=9>
						</div>
					</div>
				</div>
				<div class="col col-lg-2 col-lg-offset-1">
					<div class="form-group">
						<div class="col-lg-12">
							<input class="btn btn-primary" type=button value="Calcola CF" onclick="checkCf();">
						</div>
					</div>
				</div>
			</div>

			<div class="row cf_mask" style="display: none;">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Codice Fiscale</label>
						<div class="col-lg-8">
							<input id=CF_ditta type="text" class=" <?php echo $class_calcolo; ?> form-control resize vld_CF" name="CF_ditta" value="<?php echo $CF_ditta; ?>" size=24 <?php echo $readonly; ?>>
						</div>
					</div>
				</div>
			</div>

		</div>
		<!-- GV 2022/10/25 START	-->
		<?php
				$query_storico_pec = " SELECT * FROM storico_pec WHERE Utente_ID = " . $p;
				$results = $cls_db->ExecuteQuery($query_storico_pec);
				$storico_pec = $cls_db->getResults($results);
				?>

				 <div class="row">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Cellulare</label>
						<div class="col-lg-8">
							<input id=cell_utente tabindex="25" class="form-control resize vld_tel <?php echo $class; ?> " name=cell_utente type=text value="<?php echo $cell_utente; ?>" size=18 <?php echo $readonly; ?>>
						</div>
					</div>
				</div>
				<div class="col col-lg-3">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
						<div class="col-lg-8">
							<input id=mail_utente tabindex="26" class=" <?php echo $class; ?> form-control resize vld_email" name=mail_utente type=text value="<?php echo $mail_utente; ?>" size=24 <?php echo $readonly; ?>>
						</div>
					</div>
				</div>

			</div>
			<div class="row">
				<div class="col col-lg-3 col-lg-offset-1">
					<div class="form-group">
						<label class="col-lg-4 control-label resize" style="text-align: left;">PEC <?php echo  date("d/m/Y", strtotime($pec_inipec)); ?></label>
						<div class="col-lg-8">
							<input id=pec_utente tabindex="28" class=" <?php echo $class; ?> form-control resize vld_email" name=pec_utente type=text value="<?php echo $pec_utente; ?>" size=24 <?php echo $readonly; ?>>
							<input type="hidden" id="pec_old_utente" name="pec_old_utente" value=<?= $pec_utente ?>>
						</div>
					</div>
				</div>
				<div class="col col-lg-5 col-lg-offset-1">
					<a href="#demo" class="btn btn-primary" data-toggle="collapse">STORICO PEC</a>
					<div id="demo" class="collapse">
						<table class="table table_interna table-hover text_center" style="border:3px solid #6D95D5; display:block; overflow-y: scroll;">
							<thead>
								<tr>
									<th>PEC PRECEDENTE</th>
									<th>DATA AGGIORNAMENTO</th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($storico_pec as $pec_precedente) {
								?>
									<tr>
										<td><?= $pec_precedente['Pec'] ?></td>
										<td><?= date("d/m/Y", strtotime($pec_precedente['Data_Cambio'])) ?></td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				<!-- GV 2022/10/25   END	-->
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 2%;"></div>

		<div class="row justify-content-md-center ">
			<div class="col col-md-auto text_center">
				<span>
					<p class="sezioni_tab" id="titolo_residenza"><?php echo $titolo_residenza; ?></p>
				</span>
			</div>
		</div>

		<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

		<div class="row">
			<div class="col col-lg-3 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Stato *</label>
					<div class="col-lg-8">
						<input id=paese tabindex="12" class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=paese_residenza type=text value="<?php echo $paese_res; ?>" size=24 ondblClick="/*RicercheDaId('stato',1);*/openOfcanvas('stateSearchModal',1);" onchange="settaInput(2);" readonly>
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
					<div class="col-lg-8">
						<input id=comune tabindex="13" class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=comune_residenza type=text value="<?php echo $comune_res; ?>" size=24 ondblClick="/*RicercheDaId('ente',1);*/openOfcanvas('citySearchModal',1)" onchange="resettaValidazione();" readonly>
					</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label id="label_prov_2" class="col-lg-4 control-label resize" style="text-align: left;">Prov.</label>
					<div class="col-lg-8">
						<input id=dati_sogg_prov tabindex="7" class=" <?php echo $class; ?> provincia_res_dati_sogg form-control resize vld_esp" type=text name=provDatiSogg value="<?php echo $provincia_res; ?>" size=2>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col col-lg-3 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Fraz./Circoscriz.</label>
					<div class="col-lg-8">
						<input id=frazione tabindex="15" class=" <?php echo $class; ?> form-control resize vld_Fraz" name="frazione_residenza" type=text value="<?php echo $frazione_res; ?>" size=24 ondblClick="RicercheDaId('frazione',0);" <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Cap *</label>
					<div class="col-lg-8">
						<input id=cap tabindex="16" class="<?php echo $class; ?> form-control resize validateCustom vld_Custom_r" name=cap_residenza type=text value="<?php echo $CAP_res; ?>" size=8 <?php echo $readonly_cap; ?>>
					</div>
				</div>
			</div>
		</div>

		<div class="row" id=scelta_indirizzo_1>
			<div class="col col-lg-3 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-3 control-label resize" style="text-align: left;"> Indirizzo*</label>
					<div class="col-lg-1 resize">
						<!--<a tabindex="17" onMouseover="title='Correzione indirizzo'" href="#" onclick="RicercheDaId('via',1);" style="text-decoration: none;">
					<img src="/gitco2/immagini/gomma.png" width=27 height=18 border=0>
				</a>-->
						<a tabindex="17" onMouseover="title='Correzione indirizzo'" href="#" onclick="/*RicercheDaId('via',1);*/insAddr();" style="text-decoration: none;">
							<i class="fas fa-edit"></i>
						</a>

					</div>
					<div class="col-lg-8">
                        <!-- Funzione richiamata da double click in submenu_anagrafe.php -->
						<input id=via tabindex="18" class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=via_residenza type=text value="<?php echo $toponimo_res; ?>" alt=" " size=24 readonly onchange="forzaValidazione(this);" ondblclick="control_ind()">
					</div>
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
					<div class="col-lg-8">
						<input id=civico tabindex="19" class="form-control resize vld_int <?php echo $class; ?>" name="civico_residenza" style="width: 70%;" type="text" value='<?php echo $civico_res; ?>' size=2 <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
			<div class="col col-lg-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
					<div class="col-lg-8">
						<input id=esponente tabindex="20" class="form-control resize vld_esp <?php echo $class; ?>" name="esponente_residenza" type="text" value='<?php echo $esponente_res; ?>' size=2 <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
			<div class="col col-lg-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
					<div class="col-lg-8">
						<input id=interno tabindex="21" class="form-control resize vld_int <?php echo $class; ?>" name="interno_residenza" type="text" value='<?php echo $interno_res; ?>' size=2 <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
					<div class="col-lg-8">
						<input id=dettagli tabindex="22" class=" <?php echo $class; ?> form-control resize" name="dettagli_residenza" type="text" value='<?php echo $dettagli_res; ?>' size=14 <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
		</div>

		<div class="row" id=scelta_indirizzo_2>
			<div class="col col-lg-10 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-1 control-label resize" style="text-align: left;">Indirizzo *</label>
					<div class="col-lg-11">
						<input id=via_estero tabindex="17" class=" <?php echo $class; ?> form-control resize" name=via_estera_residenza type=text value="<?php echo $toponimo_res; ?>" size=80 <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col col-lg-3 col-lg-offset-1">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
					<div class="col-lg-8">
						<input id=tel tabindex="23" class="form-control resize vld_tel <?php echo $class; ?>" name=tel_residenza type=text value='<?php echo $telefono_res; ?>' size=18 <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="form-group">
					<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
					<div class="col-lg-8">
						<input id=fax tabindex="24" class="form-control resize vld_tel <?php echo $class; ?>" name=fax_residenza type=text value='<?php echo $fax_res; ?>' size=18 <?php echo $readonly; ?>>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col col-lg-4 col-lg-offset-1">
				<div class="form-group">
					<div class="col-lg-8">
						<input type=button tabindex="29" name=corrispondenza value=Corrispondenza class="btn btn-primary pwidth120 form-control resize" onclick="doc_utente();">
					</div>
				</div>
			</div>
		</div>

		<div class="form-group">
			<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
		</div>

</form>
</div>

</td>
</tr>
</table>

<button type="button" id="btnState" style="display: none;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasState"></button>

<?php include ("offcanvas/state_offcanvas.php"); ?>
<?php include ("offcanvas/city_offcanvas.php"); ?>
<?php include ("offcanvas/addr_offcanvas.php"); ?>

<?php echo $sceltaLayout; ?>

<script>

    function forzaValidazione(field)
    {
        //alert("forzaValidazione()"+field.id);
        validateForm(field);
    }

    var flagAQjaxReserch = false;
    var startRecalPartitaID = false;

	$(document).ready(function() {

        $("#id_cerca").keydown(function(event) {
            if(event.keyCode == 13)
                startRecalPartitaID = true;
        });

        $(window).keyup(function(event){
            if(event.keyCode == 13 && !flagAQjaxReserch && !startRecalPartitaID) {
                event.preventDefault();
                control_salva = submit_buttons('Salva');
                //	alert(control_salva);
                if (control_salva && validateForm())
                    $("#btnSub").trigger("click");
            }

            startRecalPartitaID = false;
        });

		switch ($('input[name=genere]:checked').val()) {
			case ('D'):

				$('#cognome_utente').removeClass("validateCustom vld_Custom_r");
				$('#nome_utente').removeClass("validateCustom vld_Custom_r");
				$('#paese_nascita').removeClass("validateCustom vld_Custom_r");
				//$('#comune_nascita').removeClass("validateCustom vld_Custom_r");
				break;

			case ('M'):

			case ('F'):

				$('#cognome_utente').addClass("validateCustom vld_Custom_r");
				$('#nome_utente').addClass("validateCustom vld_Custom_r");
				$('#paese_nascita').addClass("validateCustom vld_Custom_r");
				//$('#comune_nascita').addClass("validateCustom vld_Custom_r");
				break;
		}

		if ($('#paese_nascita').val() != "Italia" && $('#paese_nascita').val() != "") {
			$('#comune_nascita').css("background-color", "");
			$('#comune_nascita').css("border", "");
			$('#comune_nascita').attr("readonly", false);
			$('#label_prov_1').hide();
			//$("#comune_nascita").removeClass("validateCustom vld_Custom_r");
		} else {
			$('#comune_nascita').css("background-color", "rgb(153, 204, 255)");
			$('#comune_nascita').css("border", "2px solid black");
			$('#comune_nascita').attr("readonly", true);
			$('#label_prov_1').show();
			//$("#comune_nascita").addClass("validateCustom vld_Custom_r");
		}

		if ("<?= $genere_utente; ?>" == "D") {
			if ("<?= $CF_ditta; ?>" != "")
				Decode_CF("<?= $CF_ditta; ?>");
		}

		if ($("#paese").val() == "") {
			$("#paese").val("Italia");
			$('#scelta_indirizzo_2').hide();
			$('#scelta_indirizzo_1').show();
			$("#via_estero").removeClass("validateCustom vld_Custom_r");
			$("#via").addClass("validateCustom vld_Custom_r");
			document.getElementById("paese").dispatchEvent(new Event("change"));
		} else {
			if ($("#paese").val() == "Italia") {
				$('#scelta_indirizzo_2').hide();
				$('#scelta_indirizzo_1').show();
				$("#via_estero").removeClass("validateCustom vld_Custom_r");
				$("#via").addClass("validateCustom vld_Custom_r");
				//$('#comune').addClass("validateCustom vld_Custom_r");
				//$('#cap').addClass("validateCustom vld_Custom_r");
			} else {
				$('#scelta_indirizzo_2').show();
				$('#scelta_indirizzo_1').hide();
				$("#via").removeClass("validateCustom vld_Custom_r");
				$("#via_estero").addClass("validateCustom vld_Custom_r");
				$('#comune').removeClass("validateCustom vld_Custom_r");
				$('#cap').removeClass("validateCustom vld_Custom_r");
			}
			$("#via").css("background-color", "rgb(153, 204, 255)");
			$("#via").css("border", "2px solid black");
			document.getElementById("paese").dispatchEvent(new Event("change"));
		}

		if (modalita == "modifica") {
			$(function() {
				$("input#data_nascita").datepicker();
				$("input#data_cf").datepicker();
			});
			$(function() {
				$("input#data_morte").datepicker();
			});
			$(function() {
				$("input#data_res").datepicker();
			});
		}

		$('#id_cerca').focus();

		$('#cerca_id').ajaxForm(
			function(value) {
				var array_ritorno = value.split(' ');
				if (array_ritorno[0] == 'NO') {
					alert('Codice utente non trovato!');
					top.location.href = "dati_soggetto.php?mode=consulta&p=" + array_ritorno[1] + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
				} else {
					top.location.href = "dati_soggetto.php?mode=consulta&p=" + value + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
				}
			});

	});
</script>

<?php include(INC . "/footer.php"); ?>
<!--</body>
</html>-->