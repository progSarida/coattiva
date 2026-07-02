<?php
	/*equire $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";

	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";*/

    if (!session_id()) session_start();

include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include(INC."/header.php");
	include_once(INC."/menu.php");
	include_once(CLS."/cls_DateTimeInLine.php");
	include_once(CLS."/cls_anagrafeUtils.php");

	$cls_date = new cls_DateTimeI("IT",false);
	$cls_anagr = new cls_anagr();


	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$mode = $cls_help->getVar('mode');
	$servizio = $cls_help->getVar('servizio');
	$sceltaLayout = "";

	$mode = "modifica";//ANNULLO CONSULTA

	if($mode=="consulta" || $mode==null)
	{
		$mode = "consulta";
		$readonly = " readonly ";
		$class = " sfondo_readonly ";
		$class_ric = " sfondo_readonly ";
	}
	else
	{
		$mode = "modifica";
		$readonly = "";
		$class_ric = " sfondo_ricerca ";
		$class = " sfondo_bianco ";
	}

	//$comune = new ente_gestito($c);
	$nome_comune = $a_enteAdmin["Denominazione"];//$comune->Nome;

	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];

	$QUERY = $cls_anagr->get_Query_Dati_Soggetto($p,$c);
	$anagr = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Soggetto"]),"utente");

	//$utente = new utente($p,$c);

	$id_utente 				= 	$anagr["ID"];//$utente->ID;
	$genere_utente 			= 	$anagr["Genere"];//$utente->Genere;
	$comune_id 				= 	$anagr["Comune_ID"];//$utente->Comune_ID;

	if($genere_utente!='D')
	{
		$cognome_utente 	=	$anagr["Cognome"];//$utente->Cognome;
		$nome_utente 		=	$anagr["Nome"];//$utente->Nome;
		$CC_nascita			=	$anagr["CC_Nascita"];//$utente->CC_Nascita;
		$paese_nasc_utente  =	$anagr["Paese_Nascita"];//$utente->Paese_Nascita;
		if($paese_nasc_utente==null)
		{
			$paese_nasc_utente = "Italia";
		}
		$comune_nasc_utente =	$anagr["Comune_Nascita"];//$utente->Comune_Nascita;
	 $provincia_nasc_utente	=	$anagr["Provincia_Nascita"];//$utente->Provincia_Nascita;
		$data_nasc_utente	= $cls_date->Get_DateNewFormat($anagr["Data_Nascita"],"DB");//	from_mysql_date($utente->Data_Nascita);
		$data_morte_utente	=	$cls_date->Get_DateNewFormat($anagr["Data_Morte"],"DB");//from_mysql_date($utente->Data_Morte);
		$CF					=	$anagr["Codice_Fiscale"];//$utente->Codice_Fiscale;
	}
	else
	{
		$ditta				=	$anagr["Ditta"];//$utente->Ditta;
		$PI					=	$anagr["Partita_Iva"];//$utente->Partita_Iva;
		$prec_den_ditta		=	$anagr["Prec_Denom"];//$utente->Prec_Denom;
		$anno_cambio_ditta	=	$anagr["Anno_Cambio_Denom"];//$utente->Anno_Cambio_Denom;
	}

	//echo "<h1>query 1: ".$QUERY["Indirizzo_R"]."</h1>";

	$indirizzo_res			=	$cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Indirizzo_R"]),"indirizzo");//$utente->Residenza;

	$type_ind = gettype($indirizzo_res);

	if($type_ind != "NULL")
	{
		$ID_res		 		= 	$indirizzo_res["ID"];//$indirizzo_res->ID;
		$ID_via_res			=	$indirizzo_res["Via_ID"];//$indirizzo_res->Via_ID;
		$ID_via_cap_res		=	$indirizzo_res["Via_Cap_ID"];//$indirizzo_res->Via_Cap_ID;
	}

		$CC_res				=	$indirizzo_res["CC_Indirizzo"];//$indirizzo_res->CC_Indirizzo;
		$paese_res			=	$indirizzo_res["Paese"];//$indirizzo_res->Paese;
		if($paese_res==null)
		{
			$paese_res = "Italia";
		}

		if($paese_res!="Italia")
		{
			$sceltaLayout.= "<script>func_stato_estero_indirizzo('nascondi');</script>";
			$sceltaLayout.= "<script>$('#scelta_indirizzo_2').show();$('#scelta_indirizzo_1').hide();</script>";
		}
		else
		{
			$sceltaLayout.= "<script>$('#scelta_indirizzo_2').hide();$('#scelta_indirizzo_1').show();</script>";
		}

		//echo "<h1>ID VIA: ".$indirizzo_res["Via_ID"]." --- ".$indirizzo_res["Via_Cap_ID"]."</h1>";

		$QUERY_2 = $cls_anagr->get_Query_Dati_Soggetto_Via(array("ViaID" => $indirizzo_res["Via_ID"] ,"CapID" => $indirizzo_res["Via_Cap_ID"]),$c);
		$Via_Object = null;
		if($QUERY_2!="")
			$Via_Object = $cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY_2));

			//echo "<h1>Via: ".$Via_Object["Nome"]." ----- query: ".$QUERY_2."</h1>";

		$comune_res			=	$indirizzo_res["Comune"];//$indirizzo_res->Comune;
		$provincia_res		=	$indirizzo_res["Provincia"];//$indirizzo_res->Provincia;
		$frazione_res		=   $indirizzo_res["Frazione"];//$indirizzo_res->Frazione;
		if($Via_Object == null) $toponimo_res = "";
		else $toponimo_res		=	$Via_Object["Nome"];
		//$toponimo_res		=	$indirizzo_res["ID"];//$indirizzo_res->Toponimo->Nome;
		$civico_res			=	$indirizzo_res["Civico"];//$indirizzo_res->Civico;
		$esponente_res		=	$indirizzo_res["Esponente"];//$indirizzo_res->Esponente;
		$CAP_res			=	$indirizzo_res["Cap"];//$indirizzo_res->Cap;
		$interno_res		=	$indirizzo_res["Interno"];//$indirizzo_res->Interno;
		$dettagli_res		=	$indirizzo_res["Dettagli"];//$indirizzo_res->Dettagli;
		$telefono_res		=	$indirizzo_res["Telefono"];//$indirizzo_res->Telefono;
		$fax_res			=	$indirizzo_res["Fax"];//$indirizzo_res->Fax;
	$data_inizio_res_utente = $cls_date->Get_DateNewFormat($indirizzo_res["Data_Inizio_Residenza"], "DB");//	from_mysql_date($indirizzo_res->Data_Inizio_Residenza);

	$ID_PAGE = $cls_anagr->get_ID_Move_Page($p,$a,$c,$anagr["Cognome"],$anagr["Ditta"],$anagr["ID"]);

	$pnext = $ID_PAGE["next"];//$utente->next;
	$pprev = $ID_PAGE["prev"];//$utente->prev;
	$next_alfa = $ID_PAGE["next_alfa"];//$utente->next_alfa;
	$prev_alfa = $ID_PAGE["prev_alfa"];//$utente->prev_alfa;

	/*$pnext = $utente->next;
	$pprev = $utente->prev;
	$next_alfa = $utente->next_alfa;
	$prev_alfa = $utente->prev_alfa;*/

	$ordinamento = $cls_help->getVar('ordinamento');
	if($ordinamento=='')	$ordinamento="ID";
	$sceltaLayout.= "<script>$('#ordinamento').val('".$ordinamento."');</script>";

	if( $ordinamento == "Nome" )
	{
		$prev_current = $prev_alfa;
		$next_current = $next_alfa;
	}
	else
	{
		$prev_current = $pprev;
		$next_current = $pnext;
	}

	if ($pnext==null) 	$pnext = 0;
	if ($pprev==null) 	$pprev = 0;
	if ($p==null)		$p=0;

	$submit_name = "Update";

	$storico = $cls_anagr->Get_Storico_Residenza($p,$c);//new storico_residenza($p,$c);

	$num_residenze = isset($storico["Num_Storico"])?$storico["Num_Storico"]:null;//$storico->Num_Storico;

//echo "<h1>Paese ".$storico["Paese"][0]."</h1>";
/**
 * GESTIONE F2 /////////////////////////////////////////
 */
	if($mode == "consulta")
	{
		if($p!=0)
		{
			$F2_path = "/gitco2/immagini/redF2.png";
			$F2_click = "blocco('".$anagr["ID"]."')";
			$F2_title = "Modifica";
		}
		else
		{
			$F2_path = "/gitco2/immagini/redF2grey.png";
			$F2_click = "";
			$F2_title = "Modifica";
		}
	}
	else
	{
		$F2_path = "/gitco2/immagini/F2.png";
		$F2_click = "scelta_moda('cerca');";
		$F2_title = "Consultazione";

		/////////////////////////////////////////
		$F2_path = "/gitco2/immagini/F2grey.png";
		$F2_click = "";
		$F2_title = "";
		/////////////////////////////////////////
	}
/**
 * GESTIONE F2 /////////////////////////////////////////
 */
?>
<?php
$menuPageNumber = "Pag 6/7";
$pagina = "cambia_residenza.php";
include_once(INC."/submenu_anagrafe.php");
?>

<script>   /* -----------  VARIABILI JAVASCRIPT E SELEZIONI LAYOUT ----------- */
var stringaPHP = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
var modalita = '<?php echo $mode; ?>';
var uscita_utente = '0';
var utente_ID = '<?php echo $anagr["ID"]; ?>';
</script>

<script>    /* -----------  AJAX FORM SUBMIT ----------- */

$(function() {
	 $( "input#data_res" ).datepicker();
	 });

$(document).ready(function(){

    if($("#paese").val()=="Italia")
    {
        $("#via_estero").removeClass( "validateCustom vld_Custom_r");
        $("#comune").addClass("validateCustom vld_Custom_r");
        $("#cap").addClass("validateCustom vld_Custom_r");
        $('#comune').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
        $('#comune').attr('readonly', 'readonly');
        $('#comune').css("background-color","rgb(153, 204, 255)");
        $('#comune').css("border","2px solid black");
    }
    else {
        $("#via_estero").addClass("validateCustom vld_Custom_r");
        $("#comune").removeClass( "validateCustom vld_Custom_r");
        $("#cap").removeClass( "validateCustom vld_Custom_r");
        $('#comune').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
        $('#comune').attr('readonly', false);
        $('#comune').css("background-color","");
        $('#comune').css("border","");
    }

	$('#id_cerca').focus();

	$('#cerca_id').ajaxForm(
                function(value) {
                    var array_ritorno = value.split(' ');
			if(array_ritorno[0]=='NO')
			{
				alert('Codice utente non trovato!');
				top.location.href = "cambia_residenza.php?mode=consulta&p="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
			else
			{
        		top.location.href = "cambia_residenza.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
        });

    });

//F3
switchMenuImg("F3");
F3_button = function()
{
	control=submit_buttons('<?php echo $submit_name; ?>');
	if(control && validateForm())
			$("#btnSub").trigger("click");
}

//F4
/*switchMenuImg("F4");
F4_button = function()
{
	control=submit_buttons('Delete');
	if(control)
			$("#anagrafe_form").submit();
}*/

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP += "&mode=consulta";
    stringa = "cambia_residenza.php?"+stringaPHP;
    top.location.href = stringa;
}

//F6
switchMenuImg("F6");
F6_button = function()
{
    stringa = "dati_soggetto.php?mode=modifica&p=0&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    top.location.href = stringa;
}



//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();

		if(modalita=="consulta" || utente_ID!=0)
		{
			link = "dettagli.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
	}
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();

		if(modalita=="consulta" || utente_ID!=0)
		{
			link = "Veicoli.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
	}
}

//F7
switchMenuImg("F7");
F7_button = function()
{
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();
		link = "cambia_residenza.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}

//F8
switchMenuImg("F8");
F8_button = function()
{
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();
		link = "cambia_residenza.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}

//F11
switchMenuImg("F11");
F11_button = function()
{
	window.open('<?= WEB_ROOT; ?>/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');
}

</script>

<script>

//SCELTA MODALITA' LETTURA O SCRITTURA
function scelta_moda(value)
{
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();

		if(value=="modifica")
   		{
   	   		if(utente_ID!=0)
			top.location.href = "cambia_residenza.php?mode=modifica&ordinamento=" + value_ord + stringaPHP;
   		}
   		else
   		{
   			if(utente_ID!=0)
   			top.location.href = "cambia_residenza.php?mode=consulta&ordinamento=" + value_ord + stringaPHP;
   		}
   	}
}

var prev_utente = "<?php echo $prev_current; ?>";
var next_utente = "<?php echo $next_current; ?>";

function ordinamento ()
{
   	value = $('#ordinamento').val();

   	if(value=="ID")
   	{
   	   	prev_utente = "<?php echo $pprev; ?>";
   		next_utente = "<?php echo $pnext; ?>";
   	}
   	else if(value=="Nome")
   	{
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
		link = "cambia_residenza.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;
	else if( value == 'next' )
		link = "cambia_residenza.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}*/

//CAMBIO PAGINA
function pagina_menu (value)
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
			link = "dati_soggetto.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
		else if(value==0 && (modalita=="consulta" || utente_ID!=0))
		{
			link = "dettagli.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
	}
}
//ANNULLA
   	function annulla()
   	{
   		stringaPHP += "&mode=consulta";
		stringa = "cambia_residenza.php?"+stringaPHP;
   	   	top.location.href = stringa;
   	}
</script>

<script>

var paese_storico = new Array();
var comune_storico = new Array();
var prov_storico = new Array();
var frazione_storico = new Array();
var CAP_storico = new Array();
var indirizzo_storico = new Array();
var civico_storico = new Array();
var esponente_storico = new Array();
var interno_storico = new Array();
var dettagli_storico = new Array();
var telefono_storico = new Array();
var fax_storico = new Array();
var data_storico = new Array();

<?php
for($y=0; $y<$num_residenze; $y++)
{
?>
	paese_storico[<?php echo $y; ?>] = "<?php echo $storico["Paese"][$y]; ?>";
	comune_storico[<?php echo $y; ?>] = "<?php echo $storico["Comune"][$y]; ?>";
	prov_storico[<?php echo $y; ?>] = "<?php echo $storico["Provincia"][$y]; ?>";
	frazione_storico[<?php echo $y; ?>] = "<?php echo $storico["Frazione"][$y]; ?>";
	CAP_storico[<?php echo $y; ?>] = "<?php echo $storico["Cap"][$y]; ?>";
	indirizzo_storico[<?php echo $y; ?>] = "<?php echo $storico["Toponimo"][$y]["Nome"]; ?>";
	civico_storico[<?php echo $y; ?>] = "<?php echo $storico["Civico"][$y]; ?>";
	esponente_storico[<?php echo $y; ?>] = "<?php echo $storico["Esponente"][$y]; ?>";
	interno_storico[<?php echo $y; ?>] = "<?php echo $storico["Interno"][$y]; ?>";
	dettagli_storico[<?php echo $y; ?>] = "<?php echo $storico["Dettagli"][$y]; ?>";
	telefono_storico[<?php echo $y; ?>] = "<?php echo $storico["Telefono"][$y]; ?>";
	fax_storico[<?php echo $y; ?>] = "<?php echo $storico["Fax"][$y]; ?>";
	data_storico[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($storico["Data_Inizio"][$y],"DB"); ?>";
<?php
}
?>

function dettagli_res(value)
{
	//if(modalita=="consulta")
	//{
		ctrl_paese = $('input#paese').val(paese_storico[value]);
		if( paese_storico[value] != "Italia")
		{
			$('#scelta_indirizzo_2').show();
			$('#scelta_indirizzo_1').hide();
			$('input#via_estero').val(indirizzo_storico[value]);
		}
		else
		{
			$('#scelta_indirizzo_2').hide();
			$('#scelta_indirizzo_1').show();
			$('input#via').val(indirizzo_storico[value]);
			$('input#civico').val(civico_storico[value]);
			$('input#interno').val(interno_storico[value]);
			$('input#esponente').val(esponente_storico[value]);
		}

		$('input#comune').val(comune_storico[value]);
		$('#via').attr('ondblclick', "RicercheDaId('indirizzo_generale',0);");

		$('input#dati_sogg_prov').val(prov_storico[value]);
		$('input#frazione').val(frazione_storico[value]);
		$('input#cap').val(CAP_storico[value]);



		$('input#dettagli').val(dettagli_storico[value]);

		$('input#tel').val(telefono_storico[value]);
		$('input#fax').val(fax_storico[value]);

		$('input#data_res').val(data_storico[value]);
/*}
	else
	{
		alert('Per i Dettagli delle residenze dello storico tornare alla modalit� "Consultazione".');
	}*/
}
</script>

<script>

//CONTROLLO CAMPI
   	function controllaCampi (value)
   	{
   		pattern_speciali = /[^A-Za-z0-9\x20\x27\x28\x29\x2c\x2d\x2e\x2f\x3a\x3b]/;
   		pattern_data = /[^0-9\x2F]/;
   		pattern_mail = /^[^\x40]{1,40}[\x40]{1}[^\x40]{1,20}[.]{1}[a-zA-Z]{1,40}$/;
   		pattern_ditta = /[^A-Za-z0-9 .\x27\x28\x29\x2d]/;
   		pattern_nome = /[^A-Za-z .\x27\x28\x29\x2d]/;
   		pattern_numeri = /[^0-9]/;
   		pattern_interno = /[^0-9a-zA-Z\x2F]/;
   		pattern_cf = /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/;

   		//<!-- RESIDENZA -->
		//<!-- CONTROLLO INSERIMENTO PAESE DI RESIDENZA -->
		var paese = $('input#paese').val();
		if ((paese == "") || (paese == "undefined"))
		{
			alert("Il campo Stato di Residenza \xE8 obbligatorio.");
			return false;
		}
		control_paese = paese.match(pattern_nome);
		if(control_paese)
		{
		  	alert("Il campo Stato di Residenza non puo' contenere caratteri speciali o numerici.");
		   	return false;
		}

		//<!-- CONTROLLO INSERIMENTO COMUNE DI RESIDENZA -->
		var comune = $('input#comune').val();
		if ((comune == "") || (comune == "undefined"))
		{
			alert("Il campo Comune di Residenza \xE8 obbligatorio.");
			return false;
		}
		control_comune = comune.match(pattern_nome);
		if(control_comune)
		{
		  	alert("Il campo Comune di Residenza non puo' contenere caratteri speciali o numerici.");
		   	return false;
		}

		var cap = $('input#cap').val();
		var frazione = $('input#frazione').val();
		if ( paese == "Italia" )
		{
			var via = $('input#via').val();
			if ((via == "") || (via == "undefined"))
			{
				alert("Il campo Indirizzo di Residenza \xE8 obbligatorio.");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO CAP DI RESIDENZA -->
			control_cap = cap.match(pattern_numeri);
			if(control_cap)
			{
			  	alert("Il campo CAP di Residenza puo' contenere solo caratteri numerici");
			   	return false;
			}

			//<!-- CONTROLLO INSERIMENTO INDIRIZZO DI RESIDENZA -->
			control_via = via.match(pattern_speciali);
			if(control_via)
			{
			  	alert("Il campo Indirizzo di Residenza non puo' contenere caratteri speciali.");
			   	return false;
			}

			//<!-- CONTROLLO INSERIMENTO FRAZIONE DI RESIDENZA -->
			control_frazione = frazione.match(pattern_nome);
			if(control_frazione)
			{
			  	alert("Il campo Frazione di Residenza non puo' contenere caratteri speciali o numerici.");
			   	return false;
			}

		}
		else
		{
			var via = $('input#via_estero').val();
			if ((via == "") || (via == "undefined"))
			{
				alert("Il campo Indirizzo di Residenza \xE8 obbligatorio.");
				return false;
			}

			control_cap = cap.match(pattern_speciali);
			if(control_cap)
			{
			  	alert("Il campo CAP di Residenza non puo' contenere caratteri speciali.");
			   	return false;
			}

			control_via = via.match(pattern_speciali);
			if(control_via)
			{
			  	alert("Il campo Indirizzo di Residenza non puo' contenere caratteri speciali.");
			   	return false;
			}

			control_frazione = frazione.match(pattern_speciali);
			if(control_frazione)
			{
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

		if(control_civico)
		{
		  	alert("Il campo Civico puo' contenere solo caratteri numerici");
		   	return false;
		}
		if(control_interno)
		{
		  	alert("Il campo Interno non puo' contenere caratteri speciali");
		   	return false;
		}
		if(control_esponente)
		{
		  	alert("Il campo Esponente puo' contenere solo caratteri alfanumerici");
		   	return false;
		}
		if(control_dettagli)
		{
		  	alert("Il campo Dettagli non puo' contenere caratteri speciali");
		   	return false;
		}

		//<!-- CONTROLLO INSERIMENTO TELEFONO E FAX -->
		var telefono = $('input#tel').val();
		var fax = $('input#fax').val();

		control_telefono = telefono.match(pattern_numeri);
		control_fax = fax.match(pattern_numeri);

		if(control_telefono)
		{
		  	alert("Il campo Telefono puo' contenere solo caratteri numerici");
		   	return false;
		}
		if(control_fax)
		{
		  	alert("Il campo Fax puo' contenere solo caratteri numerici");
		   	return false;
		}

		//<!-- CONTROLLO DATA INIZIO RESIDENZA -->
		var data_res = $('input#data_res').val();
		data_res = controlla_data_campo(data_res, "Controllare la data di residenza", 1);

		if(data_res!=false)
			{$('input#data_res').val(data_res);}
		else
			{return false;}

		return true;
}

   	function func_stato_estero_indirizzo(value)
   	{
   	   	if(value=="nascondi")
   	   	{
	   		$('#comune_residenza').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
			$('#comune_residenza').attr('readonly',false);
			$('.provincia_res_dati_sogg').hide();
   	   	}
   	   	else if(value=="mostra")
   	   	{
	   	   	$('#comune_residenza').removeClass('sfondo_bianco').addClass('sfondo_ricerca');
			$('#comune_residenza').attr('readonly',true);
			$('.provincia_res_dati_sogg').show();
   	   	}

   	}
</script>

<script>

if(utente_ID=="")
{
	if(prev_utente!="0")
   		$('#F7').attr("onMouseover","title='Ultimo record F7'");

	if(next_utente!="0")
		$('#F8').attr("onMouseover","title='Primo record F8'");
}
else
{
	if(prev_utente=="" && next_utente!="")
	{
   		$('#F7').attr("onMouseover","title='Nessun record F7 (Primo record selezionato)'");
		$('#F8').attr("onMouseover","title='Record successivo F8 (Primo record selezionato)'");
	}

	if(next_utente=="" && prev_utente!="")
	{
		$('#F7').attr("onMouseover","title='Record precedente F7 (Ultimo record selezionato)'");
		$('#F8').attr("onMouseover","title='Nessun record F8 (Ultimo record selezionato)'");
	}
}

</script>

<div class="row justify-content-md-center " style="margin-top: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Cambia Residenza</span>
	</div>
</div>

<form id=anagrafe_form class="form-horizontal validate" name=cambia_residenza action="cambia_residenza_salva.php" method=post >

<input name=ID_via			id=ID_via		type=hidden value="<?php echo $ID_via_res; ?>"			>
<input name=ID_via_cap		id=ID_via_cap	type=hidden value="<?php echo $ID_via_cap_res; ?>"		>
<input name=CC_residenza	id=CC			type=hidden value="<?php echo $CC_res; ?>"				>
<input name=a 								type=hidden value="<?php echo $a; ?>"					>
<input name=p 								type=hidden value="<?php echo $p; ?>"					>
<input name=comune_id						type=hidden value="<?php echo $comune_id; ?>"			>
<input name=ID_res 							type=hidden value="<?php echo $ID_res; ?>"				>
<input name=c 								type=hidden value="<?php echo $c; ?>"					>
<input name=servizio						type=hidden value="<?php echo $servizio; ?>"			>
<input name=invia_submit 	id=invia_submit	type=hidden	value=""									>


<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Stato *</label>
			<div class="col-lg-8">
				<input id=paese tabindex=2 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=paese_residenza type=text value="<?= $paese_res; ?>" size=24 ondblClick="RicercheDaId('stato',1);" readonly>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
			<div class="col-lg-8">
				<input id=comune tabindex=3 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=comune_residenza type=text value="<?= $comune_res; ?>" size=24 ondblClick="RicercheDaId('ente',1);" readonly>
			</div>
		</div>
	</div>
	<div class="col col-lg-2 col-lg-offset-2">
		<div class="form-group">
			<label class="col-lg-6 control-label resize" style="text-align: center;">Prov.</label>
			<div class="col-lg-6">
				<input id=dati_sogg_prov tabindex="7" class=" <?php echo $class; ?> provincia_res_dati_sogg form-control resize vld_esp" type=text name=provDatiSogg value="<?= $provincia_res; ?>" size=2>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fraz./Circoscriz.</label>
			<div class="col-lg-8">
				<input id=frazione tabindex=5 class=" <?php echo $class; ?> form-control resize vld_esp" name="frazione_residenza" type=text value="<?= $frazione_res; ?>" size=24 ondblClick="RicercheDaId('frazione',0);" <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Cap *</label>
			<div class="col-lg-8">
				<input id=cap tabindex=6 class="form-control resize validateCustom vld_Custom_r vld_Custom_n <?php echo $class; ?>" style="width: 55%;" name=cap_residenza type=text value="<?= $CAP_res; ?>" size=5 readonly >
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
				<a tabindex="17" onMouseover="title='Correzione indirizzo'" href="#" onclick="RicercheDaId('via',1);" style="text-decoration: none;">
					<i class="fas fa-edit"></i>
				</a>

			</div>
			<div class="col-lg-8">
				<input id=via tabindex=8 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=via_residenza type=text value="<?= $toponimo_res; ?>" size=24 readonly ondblclick="control_ind();">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
				<input id=civico tabindex=9 class="form-control resize vld_int <?php echo $class; ?>" name="civico_residenza" style="width: 70%;"	type="text" value='<?= $civico_res; ?>' size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input tabindex=10 id=esponente 	class="form-control resize vld_esp <?php echo $class; ?>" name="esponente_residenza" 	type="text" value='<?= $esponente_res; ?>' 	size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input id=interno tabindex=11 class="form-control resize vld_int <?php echo $class; ?>" name="interno_residenza" 	type="text" value='<?= $interno_res; ?>' 	size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
				<input id=dettagli tabindex="12"	class=" <?php echo $class; ?> form-control resize" name="dettagli_residenza" 	type="text" value='<?= $dettagli_res; ?>' 	size=14 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
</div>

<div class="row" id=scelta_indirizzo_2>
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Indirizzo *</label>
			<div class="col-lg-11">
				<input id=via_estero tabindex="9" class=" <?php echo $class; ?> form-control resize vld_req" name=via_estera_residenza type=text value="<?= $toponimo_res; ?>" size=80 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input tabindex=13 id=tel class="form-control resize vld_tel <?php echo $class; ?>" name=tel_residenza type=text value='<?= $telefono_res; ?>' size=18 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input id=fax tabindex=14 class="form-control resize vld_tel <?php echo $class; ?>" name=fax_residenza type=text value='<?= $fax_res; ?>' size=18 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-6 control-label resize" style="text-align: left;">Data Inizio Res. *</label>
			<div class="col-lg-6">
				<input id=data_res tabindex=15 class="form-control resize vld_dateReq <?php echo $class; ?>" name=data_res type=text value='<?= $data_inizio_res_utente; ?>' size=9 ondblClick="controllaCampi(2);" <?php echo $readonly; ?> onblur="focusCampo();">
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<br>

<style>
	.tableFixHead thead th
	{
		position: sticky;
		top: 0;
		background-color: #ACB1E8;
	}
	.table thead > tr > th { border-bottom: none; }
	.table thead > tr > th { border-bottom: 1px solid black; }
	/*.table tbody > tr > td { rgb(153, 204, 255) background-color: rgb(153, 204, 255); }*/
</style>

<div class="row justify-content-md-center " style="margin-bottom: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Storico</span>
	</div>
</div>
<div class="tableFixHead" style="overflow-y: auto; max-height: 30vh !important; width: 80%; margin-left: 10%; display: block;">
<table class="table table-hover" cellspacing=0 border=0 style="border:1px solid black;">
	<thead>
		<tr class="text_left" style="height:35px; border: 1px solid black;" >
			<th class="width2"><br></th>
			<th class="width5"><br></th>
			<th class="width1"><br></th>
			<th class="width8"><b>Paese</b></th>
			<th class="width1"><br></th>
			<th class="width14"><b>Comune</b></th>
			<th class="width1"><br></th>
			<th class="width20"><b>Indirizzo</b></th>
			<th class="width1"><br></th>
			<th class="width8 text_center"><b>Civ.</b></th>
			<th class="width1"><br></th>
			<th class="width10"><b>Data Inizio</b></th>
			<th class="width1"><br></th>
			<th class="width10"><b>Data Fine</b></th>
			<th class="width1"><br></th>
		</tr>
	</thead>
	<tbody style="">
		<tr class="sfondo_giallo text_left">
			<td class="width2 fix"><br></td>
			<td class="width5 fix text_center">RES</td>
			<td class="width1 fix "><br></td>
			<td class="width8 fix"><?php echo $paese_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width14 fix"><?php echo $comune_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width20 fix"><?php echo $toponimo_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width8 fix text_center"><?php echo $civico_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width10 fix"><?php echo $data_inizio_res_utente; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width10 fix">Ad oggi</td>
			<td class="width1 fix"><br></td>
		</tr>
<?php

if($num_residenze!=0)
{

for($i=0; $i<$num_residenze; $i++)
{
	$color = "#A5BDF0";//A0B7E8  B0C9FF
	if($i%2==0) $color = "#A1C8FF";
	?>

		<tr style="background-color: <?= $color; ?>;">
			<td class="width2"><br></td>
			<td class="width5 text_center">
			<input type=image src="<?= IMMAGINIWEB; ?>/select.png"
			style="width:25px; height:25px; border:0;"
			title="Dettagli Residenza" onClick="dettagli_res('<?php echo $i; ?>');"></td>
			<td class="width1"><br></td>
			<td class="width8"><?php echo $storico["Paese"][$i]; ?></td>
			<td class="width1"><br></td>
			<td class="width14"><?php echo $storico["Comune"][$i]; ?></td>
			<td class="width1"><br></td>
			<td class="width20"><?php echo $storico["Toponimo"][$i]["Nome"]; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width8"><?php echo $storico["Civico"][$i]; ?></td>
			<td class="width1"><br></td>
			<td class="width10"><?php echo $cls_date->Get_DateNewFormat($storico["Data_Inizio"][$i],"DB"); ?></td>
			<td class="width1"><br></td>
			<td class="width10"><?php echo $cls_date->Get_DateNewFormat($storico["Data_Fine"][$i],"DB"); ?></td>
			<td class="width1"><br></td>
		</tr>

	<?php }?>
	</tbody>
</table>
</div>
<?php
}

?>

<?php echo $sceltaLayout; ?>

<?php include(INC."/footer.php"); ?>
