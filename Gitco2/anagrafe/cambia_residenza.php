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

    $submenuPageNo = 6;
    $pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';

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

    if($p != null) {
        $queryAtto = "SELECT A.ID
                    FROM utente AS U 
                    LEFT JOIN partita_tributi AS PT on PT.Utente_ID = U.ID
                    LEFT JOIN atto AS A on A.Partita_ID = PT.ID
                    WHERE U.ID = " . $p . " AND U.CC_Comune = '" . $c . "' AND A.Data_Stampa IS NOT NULL
                    ORDER BY A.ID DESC LIMIT 1";
        $resultAtto = $cls_db->getArrayLine($cls_db->ExecuteQuery($queryAtto));
    }
    else $resultAtto = null;
//var_dump($resultAtto);
//echo $queryAtto;
  //  $cls_help->alert($resultAtto["ID"]);

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
        $data_conferma_indirizzo = $cls_date->Get_DateNewFormat($indirizzo_res["Data_Conferma_Indirizzo"],"DB");
//$cls_help->alert($indirizzo_res["Data_Inizio_Residenza"]);
    if($indirizzo_res["Data_Inizio_Residenza"] != '1900-01-01')
	    $data_inizio_res_utente = $cls_date->Get_DateNewFormat($indirizzo_res["Data_Inizio_Residenza"], "DB");//	from_mysql_date($indirizzo_res->Data_Inizio_Residenza);
   // else $data_inizio_res_utente = date("d/m/Y");
	else $data_inizio_res_utente ="1900/01/01";
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

//$cls_help->alert($storico["Data_Inizio"][0]." P=".$p." c=".$c);

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


			link = "dettagli.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;

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

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/CambiaResidenza.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help ANAGRAFE Cambio residenza</b>");
    $("#helpModal").modal('show');

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
var data_ultima_modifica = new Array();

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
    data_ultima_modifica[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($storico["Data_Ultima_Modifica"][$y],"DB"); ?>";
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

        $("input#data_conf").val(data_ultima_modifica[value]);
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

function doc_utente()
{
    var stringa = "<?= WEB_ROOT?>/search/posta/posta.php?p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&mode=modifica";
    openWindowSearch(stringa,{width:1200, height:800, left:(($(window).width()/2)-600), top:(($(window).height()/2)-400)});
}

function stampa_richiesta(value)
{
    link = "<?= SUPER_WEB_ROOT; ?>/Gitco2/stampe/richiesta_validazione_notifica.php?richiesta_singola=si&c=<?php echo $c; ?>&a=<?php echo $a?>&ID_Atto="+value;
    location.href= link;
}

function SalvaDataConferma(){

    if($("#data_conf").val() == ""){
        alert("Prima inserire la data di conferma dell'indirizzo!");
        return false;
    }
    if(utente_ID == "" || utente_ID == 0){
        alert("Selezionare un utente!");
        return false;
    }

    $.ajax({
        url : "ajax/save_data_conferma.php",
        type: "GET",
        data: {
            data_conferma: $("#data_conf").val(),
            utente_id: utente_ID,
            tipo: "res"
        },
        success : function (data,stato) {
            //alert(data);
            //alert(stato);
            if(stato=="success" && data == "OK") {
                alert("Dati salvati correttamente!");
                $("#data_conf_table").text($("#data_conf").val());
            }
            else
                alert(stato);
            //$("#risultati").html(data);
            //$("#statoChiamata").text(stato);
        },
        error : function (richiesta,stato,errori) {
            alert("E' evvenuto un errore. Lo stato della chiamata: "+stato);
        }
    });
}

</script>

<style>
    .iconelink{
        color:darkgreen;
    }
    .iconelink:hover{
        color:forestgreen;
    }
</style>

<div class="row justify-content-md-center " style="margin-top: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Cambia Residenza</span>
	</div>
</div>

<?php
$menuPageNumber = "Pag 6/7";
$pagina = "cambia_residenza.php";
include_once(INC."/submenu_anagrafe.php");
include_once(INC."/pages_authorization.php");

if($resultAtto != null) $activate = "onclick=\"stampa_richiesta('". $resultAtto["ID"]."');\"";
else $activate = "onclick=\"alert('Atto non trovato');\"";;

?>

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
<input name=data_ultima_modifica id=data_ultima_modifica	type=hidden value="<?php echo $servizio; ?>">
<input name=invia_submit 	id=invia_submit	type=hidden	value=""									>

<div class="row" style="margin-top: 1%;">
    <div class="col col-lg-offset-1 col-lg-10">
        <div style="float: right;"><i onclick="doc_utente();" style="cursor: pointer;" title="Corrispondenza" class="iconelink fa fa-envelope fa-2x" aria-hidden="true"></i></div>
        <div style="float: right;margin-right: 2%;"><i <?= $activate; ?> style="cursor: pointer;" title="Richiesta validazione notifica" class="iconelink fa fa-comment fa-2x" aria-hidden="true"></i></div>
    </div>
</div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 2%;"></div>

<div class="row" >
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Stato *</label>
			<div class="col-lg-8">
				<input id=paese tabindex=2 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=paese_residenza type=text value="<?= $paese_res; ?>" size=24 ondblClick="/*RicercheDaId('stato',1);*/openOfcanvas('stateSearchModal',1);" readonly>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
			<div class="col-lg-8">
				<input id=comune tabindex=3 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=comune_residenza type=text value="<?= $comune_res; ?>" size=24 ondblClick="/*RicercheDaId('ente',1);*/openOfcanvas('citySearchModal',1);" readonly>
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
				<a tabindex="17" onMouseover="title='Correzione indirizzo'" href="#" onclick="/*RicercheDaId('via',1);*/insAddr();" style="text-decoration: none;">
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

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

    <div class="row">
        <div class="col col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-6 control-label resize" style="text-align: left;">Data conferma indirizzo</label>
                <div class="col-lg-6">
					<!-- GV 20/06/2022  START 
						<input id=data_conf tabindex=15 class="form-control resize vld_date picker <?php // echo $class; ?>" name=data_conf type=text value='<?= $data_conferma_indirizzo; ?>' size=9 <?php // echo $readonly; ?>
					-->
                    <input id=data_conf tabindex=15 class="form-control validateCustom vld_Custom_r resize vld_date picker <?php echo $class; ?>" name=data_conf type=text value='<?= $data_conferma_indirizzo; ?>' size=9 <?php echo $readonly; ?> >
					<!-- GV 20/06/2022    END -->
				</div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-8">
                    <input type=button tabindex="29" name=save_conf_date value="Salva data conf." class="btn btn-primary pwidth120 form-control resize" onclick="SalvaDataConferma();" >
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

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
			<th class="width4"><br></th>
			<th class="width1"><br></th>
			<th class="width7"><b>Paese</b></th>
			<th class="width1"><br></th>
			<th class="width13"><b>Comune</b></th>
			<th class="width1"><br></th>
			<th class="width15"><b>Indirizzo</b></th>
			<th class="width1"><br></th>
			<th class="width8 text_center"><b>Civ.</b></th>
			<th class="width1"><br></th>
			<th class="width9"><b>Data Inizio</b></th>
			<th class="width1"><br></th>
			<th class="width9"><b>Data Fine</b></th>
			<th class="width1"><br></th>
            <th class="width9"><b>Ultima mod.</b></th>
            <th class="width1"><br></th>
		</tr>
	</thead>
	<tbody style="">
		<tr class="sfondo_giallo text_left">
			<td class="width2 fix"><br></td>
			<td class="width4 fix text_center">RES</td>
			<td class="width1 fix "><br></td>
			<td class="width7 fix"><?php echo $paese_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width13 fix"><?php echo $comune_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width15 fix"><?php echo $toponimo_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width8 fix text_center"><?php echo $civico_res; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width9 fix"><?php echo $data_inizio_res_utente; ?></td>
			<td class="width1 fix"><br></td>
			<td class="width9 fix">Ad oggi</td>
			<td class="width1 fix"><br></td>
            <th class="width9"><p id="data_conf_table"><?= $data_conferma_indirizzo; ?></p></th>
            <th class="width1"><br></th>
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
			<td class="width4 text_center">
			<input type=image src="<?= IMMAGINIWEB; ?>/select.png"
			style="width:25px; height:25px; border:0;"
			title="Dettagli Residenza" onClick="dettagli_res('<?php echo $i; ?>');"></td>
			<td class="width1"><br></td>
			<td class="width7"><?php echo $storico["Paese"][$i]; ?></td>
			<td class="width1"><br></td>
			<td class="width13"><?php echo $storico["Comune"][$i]; ?></td>
			<td class="width1"><br></td>
			<td class="width15"><?php echo $storico["Toponimo"][$i]!=null?$storico["Toponimo"][$i]["Nome"]:""; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width8"><?php echo $storico["Civico"][$i]; ?></td>
			<td class="width1"><br></td>
			<td class="width9"><?php echo $cls_date->Get_DateNewFormat($storico["Data_Inizio"][$i],"DB"); ?></td>
			<td class="width1"><br></td>
			<td class="width9"><?php echo $cls_date->Get_DateNewFormat($storico["Data_Fine"][$i],"DB"); ?></td>
			<td class="width1"><br></td>
            <th class="width9"><?php echo $cls_date->Get_DateNewFormat($storico["Data_Ultima_Modifica"][$i],"DB"); ?></th>
            <th class="width1"><br></th>
		</tr>

	<?php }?>
	</tbody>
</table>
</div>
<?php
}

?>

<?php echo $sceltaLayout; ?>

<!-- Inclusione modali -->
<?php include_once (ROOT."/search_modal/offcanvas/state_offcanvas.php"); ?>
<?php include_once (ROOT."/search_modal/offcanvas/city_offcanvas.php"); ?>
<?php include_once (ROOT."/search_modal/offcanvas/addr_offcanvas.php"); ?>
<?php include_once (ROOT."/search_modal/offcanvas/user_offcanvas.php"); ?>
<?php //include_once (ROOT."/search_modal/startAjax.php"); ?>

<script>
    //Apertura modale modifica campo
    function openOfcanvas(id_off,rif){
        // Reset campi input
        $('#user_name').val("");
        $('#user_cf').val("");
        $('#state').val("");
        $('#city').val("");
        $('#addr_c').val("");
        $('#addr_g').val("");

        // Reset spazi tabella
        $('#appendTableUser').empty();
        $('#appendTableState').empty();
        $('#appendTableCity').empty();
        $('#appendTableAddr').empty();

        flagAQjaxReserch = true;
        switch (id_off){
            case 'addrSearchModal':
                //Inizializzazione dati per ricerca indirizzo
                addr_S = $('#via').attr('alt');                         // tipo ricerca
                addr_c = $('#comune').val();                            // nome comune
                addr = $('#via').val();                                 // indirizzo
                addr_CC = $('#CC').val();                               // cod. catastale comune
                // Visualizzazione tipo di ricerca -->
                // Controllo tipo di ricerca indirizzo
                $('#addr_c').val(addr) ;
                $('#addr_g').val(addr) ;
                //Comune cappato
                if(addr_S == 'cap'){
                    document.getElementById('addrSearchModalLabel_nc').hidden = true;
                    document.getElementById('ins_addr_nc').hidden = true;
                    $('#comune_c').val(addr_c);
                    document.getElementById('check_cap').checked = true;
                    document.getElementById('check_gen').checked = false;
                    // Resetta gli hidden se si cambia due volte città di cui una è cappata e l'altra no -->
                    document.getElementById('checkbox_c').hidden = false;
                    document.getElementById('addrSearchModalLabel_c').hidden = false;
                    document.getElementById('ins_addr_c').hidden = false;
                    $('#'+id_off).modal('show');
                    selectRif = rif;
                }
                // Comune non cappato
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
                else{
                    alert("Prima di cercare l'indirizzo svolgere la ricerca del comune");
                }
                break;
            case 'userSearchModal':
                //Inizializzazione dati per ricerca utente
                //user_S = "u_name";
                //alert(all_city);
                all_city = 'n';
                $("#ins_u_cf").hide();
                $("#ins_u_name").show();
                document.getElementById('check_u_name').checked = true;
                document.getElementById('check_u_cf').checked = false;
                $('#userSearchModal').modal('show');
                break;
            default:
                //Ricerca di Paese o Comune
                state_ = $('#paese').val();                             // Paese
                $('#state').val(state_) ;                               // Carico paese nel campo di ricerca della modale
                addr_c = $('#comune').val();                            // Comune
                $('#city').val(addr_c) ;                                // Carico comune nel campo di ricerca della modale
                $('#'+id_off).modal('show');
                selectRif = rif;
                break;
        }
        // Vecchia distinzione tra chiamate
        /*
        if (id_off=='addrSearchModal'){

        }
        //Ricerca Paese o Comune
        else {

        }
        */
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
            // Sostituzione dati utente
            case "user":
            case "cf":
                top.location.href="<?= WEB_ROOT; ?>/anagrafe/cambia_residenza.php?mode=consulta&p=" + val["ID"] + "&c=" + val["CC_Comune"] + "&a=<?php echo $a; ?>";
                break;

            default: alert("Ricerca non trovata!"); break;
        }

    }

    function insAddr()
    {
        if ($("#ID_via_cap").val() > 1)
        {
            alert("Hai selezionato un indirizzo cappato, e quindi non è possibile abilitare la scrittura.");
        }
        else if ($("#ID_via_cap").val() == 1)
        {
            ctrl_giallo = $('#via').hasClass('sfondo_giallo');

            if (ctrl_giallo == false)
            {
                $('#via').prop('readonly', false).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
                $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
                $('#via').css("background-color","");
                alert("Ora e' possibile modificare l'indirizzo. Terminata l'operazione cliccare nuovamente sulla gomma.\n\nSi ricorda che questa funzione serve per correggere errori di battitura e non per inserire un nuovo indirizzo.");
                $('#via').focus();
            }
            else if (ctrl_giallo == true)
            {
                if($('#via').val() != "")
                {
                    $('#via').prop('readonly', true).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
                    $('#via').css("background-color", "rgb(153, 204, 255)");
                    alert("Operazione effettuata correttamente");
                    //$('#via').focus();
                }
                else
                {
                    alert("Inserire indirizzo corretto");
                }
            }
        }
        else
        {
            alert("Prima di inserire manualmente l'indirizzo effettuare la ricerca");
        }
    }
</script>

<?php include(INC."/footer.php"); ?>
