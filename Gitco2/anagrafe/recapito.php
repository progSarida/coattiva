<?php
	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
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

    $submenuPageNo = 3;
    $pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';

	$cls_date = new cls_DateTimeI("IT",false);
	$cls_anagr = new cls_anagr();

	//if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	$mode = $cls_help->getVar('mode');
	//$sceltaLayout = "";
	$servizio = $cls_help->getVar('servizio');

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

	$QUERY = $cls_anagr->get_Query_Dati_Soggetto($p,$c);
	$anagr = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Soggetto"]),"utente");

	//$utente = new utente($p,$c);

	$id_utente 				= 	$anagr["ID"];//$utente->ID;
	$genere_utente 			= 	$anagr["Genere"];//$utente->Genere;
	$comune_id 				=	$anagr["Comune_ID"];//$utente->Comune_ID;
    $data_morte_utente = "";

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
		$data_nasc_utente	=	$cls_date->Get_DateNewFormat($anagr["Data_Nascita"],"DB");// from_mysql_date($utente->Data_Nascita);
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

	$indirizzo_rec =	$cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY["Indirizzo_Rec"]));//$utente->Recapito;

	//echo $QUERY["Indirizzo_Rec"]."<br>";
	$type_ind = gettype($indirizzo_rec);

	$ID_via_rec = 0;
	$ID_via_cap_rec = 0;

	//echo $type_ind."<br>";
    $ID_rec = 0;

	if($type_ind == "array")
	{
		$ID_rec		 		= 	$indirizzo_rec["ID"];
		$ID_via_rec			=	$indirizzo_rec["Via_ID"];//$indirizzo_rec->Via_ID;
		$ID_via_cap_rec		=	$indirizzo_rec["Via_Cap_ID"];//$indirizzo_rec->Via_Cap_ID;


		$CC_rec				=	$indirizzo_rec["CC_Indirizzo"];//$indirizzo_rec->CC_Indirizzo;
		$presso_rec			=	$indirizzo_rec["Presso"];//$indirizzo_rec->Presso;
		$paese_rec			=	$indirizzo_rec["Paese"];//$indirizzo_rec->Paese;

		if($paese_rec==null)
		{
			$paese_rec = "Italia";
		}
		//if($paese_rec!="Italia")
			//$sceltaLayout.= "<script>func_stato_estero_indirizzo('nascondi');</script>";

		//	echo "via ".$indirizzo_rec["Via_ID"]." cap ".$indirizzo_rec["Via_Cap_ID"];
		$QUERY_2 = $cls_anagr->get_Query_Dati_Soggetto_Via(array("ViaID" => $indirizzo_rec["Via_ID"] ,"CapID" => $indirizzo_rec["Via_Cap_ID"]),$c);
		$Via_Object = null;
		if($QUERY_2!="")
			$Via_Object = $cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY_2));

		//	echo "query 2: ".$QUERY_2."<br>";

		$comune_rec			=	$indirizzo_rec["Comune"];//$indirizzo_rec->Comune;
		$provincia_rec		=	$indirizzo_rec["Provincia"];//$indirizzo_rec->Provincia;
		$frazione_rec		=   $indirizzo_rec["Frazione"];//$indirizzo_rec->Frazione;
		$toponimo_rec		=	$Via_Object["Nome"];//$indirizzo_rec->Toponimo->Nome;
		$civico_rec			=	$indirizzo_rec["Civico"];//$indirizzo_rec->Civico;
		$esponente_rec		=	$indirizzo_rec["Esponente"];//$indirizzo_rec->Esponente;
		$CAP_rec			=	$indirizzo_rec["Cap"];//$indirizzo_rec->Cap;
		$interno_rec		=	$indirizzo_rec["Interno"];//$indirizzo_rec->Interno;
		$dettagli_rec		=	$indirizzo_rec["Dettagli"];//$indirizzo_rec->Dettagli;
		$telefono_rec		=	$indirizzo_rec["Telefono"];//$indirizzo_rec->Telefono;
		$fax_rec			=	$indirizzo_rec["Fax"];//$indirizzo_rec->Fax;

	}
	else
	{
		$presso_rec			= 	"";
		$CC_rec				=	"";
		$paese_rec			=	"";
		if($paese_rec==null)
		{
			$paese_rec = "Italia";
		}

		$comune_rec			=	"";
		$provincia_rec		=	"";
		$frazione_rec		=   "";
		$toponimo_rec		=	"";
		$civico_rec			=	"";
		$esponente_rec		=	"";
		$CAP_rec			=	"";
		$interno_rec		=	"";
		$dettagli_rec		=	"";
		$telefono_rec		=	"";
		$fax_rec			=	"";
	}

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
	//$sceltaLayout.= "<script>$('#ordinamento').val('".$ordinamento."');</script>";

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

	/*if($paese_rec!="Italia" && $mode=="modifica")
	{
		$sceltaLayout.= "<script>$('#comune').addClass('sfondo_bianco').removeClass('sfondo_ricerca');</script>";
		$sceltaLayout.= "<script>$('#comune').attr('readonly',false);</script>";
		$sceltaLayout.= "<script>$('#cap').attr('readonly',false);</script>";
	}

	if($paese_rec!="Italia")
	{
		$sceltaLayout.= "<script>$('#scelta_indirizzo_2').show();$('#scelta_indirizzo_1').hide();</script>";
	}
	else
	{
		$sceltaLayout.= "<script>$('#scelta_indirizzo_2').hide();$('#scelta_indirizzo_1').show();</script>";
	}*/

	if($indirizzo_rec == null)	{	$submit_name = "Insert";	}
	else						{	$submit_name = "Update";	}

/**
 * GESTIONE F2 /////////////////////////////////////////
 */
	if($mode == "consulta")
	{
		if($p!=0)
		{
			$F2_path = "/gitco2/immagini/redF2.png";
			$F2_click = "blocco('".$utente->ID."')";
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

	$F2_click = "";

    $query = "SELECT ID, name FROM user_rec_type ORDER BY ID ASC";
    $result = $cls_db->getResults($cls_db->ExecuteQuery($query));

    $options_rec_type = "";
    for($i=0; $i < count($result); $i++) {
        $sel = "";

        if($indirizzo_rec != null){
            if($indirizzo_rec["user_rec_type_id"] == $result[$i]["ID"])
                $sel = "selected";
        }
        $options_rec_type .= "<option value='".$result[$i]["ID"]."'  ".$sel.">".$result[$i]["name"]."</option>";
    }
/**
 * GESTIONE F2 /////////////////////////////////////////
 */
?>
<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
Keep the http-equiv meta tag for IE8
<meta http-equiv="X-UA-Compatible" content="IE=8" />

<title>Anagrafe - Recapito</title>

	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>

	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>-->

<script>   /* -----------  VARIABILI JAVASCRIPT E SELEZIONI LAYOUT ----------- */
var stringaPHP = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
var modalita = '<?php echo $mode; ?>';
var uscita_utente = '0';
var utente_ID = '<?php echo $anagr["ID"]; ?>';
</script>

<script>    /* -----------  AJAX FORM SUBMIT ----------- */

$(document).ready(function(){

	$("#id_cerca").focus();

	/*if(modalita=="modifica")
	{


	$("#submit_click").click(function() {
		campi = controllaCampi();
		if(campi)
		{
    		control=submit_buttons('<?php echo $submit_name; ?>');
     		if(control)
        		$("#anagrafe_form").submit();
		}
        });

    $("#delete_click").click(function() {
    	control=submit_buttons('Delete');
      	if(control)
        	$("#anagrafe_form").submit();
         });

    $('#anagrafe_form').ajaxForm(

                function(value) {
                    array_ritorno = value.split(' ');
                    switch(array_ritorno[0])
                    {
                    	case "Delete":
                        	if(array_ritorno[1]=='Si')
                        	{
                            	alert('ID Utente '+array_ritorno[2]+' - recapito eliminato con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("Errore nel tentativo di eliminazione del recapito relativo all'ID Utente "+array_ritorno[3]+".");
                        	}

							value = <?php echo $p ?>;

                    	break;

                    	case "Update":
                        	if(array_ritorno[1]=='Si')
                        	{
                            	alert('ID Utente '+array_ritorno[3]+' - recapito aggiornato con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("Errore nel tentativo di aggiornamento del recapito relativo all'ID Utente "+array_ritorno[3]+".");
                        	}
                        	else if(array_ritorno[1]=='Via')
                        	{
                        		alert("Errore nel tentativo di aggiornamento dell'indirizzo.");
                        	}

                        	value = array_ritorno[2];

                    	break;

                    	case "Insert":
                        	if(array_ritorno[1]=='Si')
                        	{
                            	alert('Nuovo ID Utente '+array_ritorno[3]+' - recapito inserito con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("ID Utente "+array_ritorno[3]+" - Errore nel tentativo di inserimento del nuovo recapito.");
                        	}
                        	else if(array_ritorno[1]=='Via')
                        	{
                        		alert("Errore nel tentativo di inserimento del nuovo indirizzo.");
                        	}

                        	value = array_ritorno[2];

                    	break;

                    }
            top.location.href = "recapito.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
        });
	}*/

	$('#cerca_id').ajaxForm(
                function(value) {
                    var array_ritorno = value.split(' ');
			if(array_ritorno[0]=='NO')
			{
				alert('Codice utente non trovato!');
				top.location.href = "recapito.php?mode=consulta&p="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
			else
			{
        		top.location.href = "recapito.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
        });

    });

</script>
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	control=submit_buttons('<?php echo $submit_name; ?>');
	if(control && validateForm())
			$("#btnSub").trigger("click");
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control=submit_buttons('Delete');
		if(control)
			$("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP += "&mode=consulta";
stringa = "recapito.php?"+stringaPHP;
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

        link = "annotazioni.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
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
			link = "domicilio.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
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

		link = "recapito.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

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

		link = "recapito.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Recapito.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help ANAGRAFE Recapito</b>");
    $("#helpModal").modal('show');

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
			top.location.href = "recapito.php?mode=modifica&ordinamento=" + value_ord + stringaPHP;
   		}
   		else
   		{
   			if(utente_ID!=0)
   			top.location.href = "recapito.php?mode=consulta&ordinamento=" + value_ord + stringaPHP;
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
		link = "recapito.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;
	else if( value == 'next' )
		link = "recapito.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

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
			link = "domicilio.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
		else if(value==0 && (modalita=="consulta" || utente_ID!=0))
		{
			link = "annotazioni.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
	}
}*/

//ANNULLA
   	/*function annulla()
   	{
   		stringaPHP += "&mode=consulta";
		stringa = "recapito.php?"+stringaPHP;
   	   	top.location.href = stringa;
   	}*/
</script>

<script>

//CONTROLLO CAMPI
   	/*function controllaCampi (value)
   	{
   		pattern_speciali = /[^A-Za-z0-9\x20\x27\x28\x29\x2c\x2d\x2e\x2f\x3a\x3b]/;
   		pattern_data = /[^0-9\x2F]/;
   		pattern_mail = /^[^\x40]{1,40}[\x40]{1}[^\x40]{1,20}[.]{1}[a-zA-Z]{1,40}$/;
   		pattern_ditta = /[^A-Za-z0-9 .\x27\x28\x29\x2d]/;
   		pattern_nome = /[^A-Za-z .\x27\x28\x29\x2d]/;
   		pattern_numeri = /[^0-9]/;
   		pattern_interno = /[^0-9a-zA-Z\x2F]/;
   		pattern_cf = /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/;

   		//<!-- RECAPITO -->

   		//<!-- CONTROLLO INSERIMENTO PRESSO -->
			var presso = $('input#presso').val();

			control_presso = presso.match(pattern_nome);


			if ((presso == "") || (presso == "undefined"))
			{
				alert("Il campo Presso \xE8 obbligatorio.");
				return false;
			}
			else if(control_presso)
			{
		  		alert("Il campo Presso non puo' contenere caratteri speciali");
		   		return false;
			}


		//<!-- CONTROLLO INSERIMENTO PAESE DI RECAPITO -->
		var paese = $('input#paese').val();
		if ((paese == "") || (paese == "undefined"))
		{
			alert("Il campo Stato di Recapito \xE8 obbligatorio.");
			return false;
		}
		control_paese = paese.match(pattern_nome);
		if(control_paese)
		{
		  	alert("Il campo Stato di Recapito non puo' contenere caratteri speciali o numerici.");
		   	return false;
		}

		//<!-- CONTROLLO INSERIMENTO COMUNE DI RECAPITO -->
		var comune = $('input#comune').val();
		if ((comune == "") || (comune == "undefined"))
		{
			alert("Il campo Comune di Recapito \xE8 obbligatorio.");
			return false;
		}
		control_comune = comune.match(pattern_nome);
		if(control_comune)
		{
		  	alert("Il campo Comune di Recapito non puo' contenere caratteri speciali o numerici.");
		   	return false;
		}

		var cap = $('input#cap').val();
		var frazione = $('input#frazione').val();
		if ( paese == "Italia" )
		{
			var via = $('input#via').val();
			if ((via == "") || (via == "undefined"))
			{
				alert("Il campo Indirizzo di Recapito \xE8 obbligatorio.");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO CAP DI RECAPITO -->
			control_cap = cap.match(pattern_numeri);
			if(control_cap)
			{
			  	alert("Il campo CAP di Recapito puo' contenere solo caratteri numerici");
			   	return false;
			}

			//<!-- CONTROLLO INSERIMENTO INDIRIZZO DI RECAPITO -->
			control_via = via.match(pattern_speciali);
			if(control_via)
			{
			  	alert("Il campo Indirizzo di Recapito non puo' contenere caratteri speciali.");
			   	return false;
			}

			//<!-- CONTROLLO INSERIMENTO FRAZIONE DI RECAPITO -->
			control_frazione = frazione.match(pattern_nome);
			if(control_frazione)
			{
			  	alert("Il campo Frazione di Recapito non puo' contenere caratteri speciali o numerici.");
			   	return false;
			}

		}
		else
		{
			var via = $('input#via_estero').val();
			if ((via == "") || (via == "undefined"))
			{
				alert("Il campo Indirizzo di Recapito \xE8 obbligatorio.");
				return false;
			}

			control_cap = cap.match(pattern_speciali);
			if(control_cap)
			{
			  	alert("Il campo CAP di Recapito non puo' contenere caratteri speciali.");
			   	return false;
			}

			control_via = via.match(pattern_speciali);
			if(control_via)
			{
			  	alert("Il campo Indirizzo di Recapito non puo' contenere caratteri speciali.");
			   	return false;
			}

			control_frazione = frazione.match(pattern_speciali);
			if(control_frazione)
			{
			  	alert("Il campo Frazione di Recapito non puo' contenere caratteri speciali.");
			   	return false;
			}
		}

		//<!-- CONTROLLO INSERIMENTO CIVICI DI RECAPITO -->
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

		return true;
}*/

	/*function func_stato_estero_indirizzo(value)
   	{
		if(value=="nascondi")
   	   	{
	   		$('#comune_recapito').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
			$('#comune_recapito').attr('readonly',false);
			$('.provincia_rec_dati_sogg').hide();
   	   	}
   	   	else if(value=="mostra")
   	   	{
	   	   	$('#comune_recapito').removeClass('sfondo_bianco').addClass('sfondo_ricerca');
			$('#comune_recapito').attr('readonly',true);
			$('.provincia_rec_dati_sogg').show();
   	   	}
   	}*/
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


function settaInput()
{
	if($('#paese').val()!="Italia" && $('#paese').val()!="")
	{
		$('#comune').css("background-color","");
		$('#comune').css("border","");
		$('#comune').attr("readonly",false);
		$('.provincia_rec_dati_sogg').hide();
        $("#via_estero").addClass("validateCustom vld_Custom_r");
        $('#comune').removeClass("validateCustom vld_Custom_r");
        $('#cap').removeClass("validateCustom vld_Custom_r");
	}
	else {
		$('#comune').css("background-color","rgb(153, 204, 255)");
		$('#comune').css("border","2px solid black");
		$('#comune').attr("readonly",true);
		$('.provincia_rec_dati_sogg').show();
        $("#via_estero").removeClass("validateCustom vld_Custom_r");
        $('#comune').addClass("validateCustom vld_Custom_r");
        $('#cap').addClass("validateCustom vld_Custom_r");
	}
	validateForm(document.getElementById("paese"));
}

function forzaValidazione(field)
{
	//alert("forzaValidazione()"+field.id);
	validateForm(field);
}

function checkDataMorte(el){
    if(el.value == "1"){
        if("<?= $data_morte_utente; ?>" == "") {
            viewModalAlert(2,"La data di morte dell'utente, in dati soggetto, non è stata inserita!");
            el.value = "";
        }
    }
}

</script>

<?php
$menuPageNumber = "Pag 3/7";
$pagina = "recapito.php";
include_once(INC."/submenu_anagrafe.php");
include_once(INC."/pages_authorization.php");
?>

<div class="row justify-content-md-center " style="margin-top: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Recapito</span>
	</div>
</div>

<form id=anagrafe_form class="form-horizontal validate" name=recapito action="recapito_salva.php" method=post >

<input name=ID_via			id=ID_via		type=hidden value="<?php echo $ID_via_rec; ?>"			>
<input name=ID_via_cap		id=ID_via_cap	type=hidden value="<?php echo $ID_via_cap_rec; ?>"		>
<input name=CC_recapito		id=CC	type=hidden value="<?php echo $CC_rec; ?>"				>
<input name=a 								type=hidden value="<?php echo $a; ?>"					>
<input name=p 								type=hidden value="<?php echo $p; ?>"					>
<input name=comune_id						type=hidden value="<?php echo $comune_id; ?>"			>
<input name=ID_rec 							type=hidden value="<?php echo $ID_rec; ?>"				>
<input name=c 								type=hidden value="<?php echo $c; ?>"					>
<input name=invia_submit 	id=invia_submit	type=hidden	value=""									>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Presso *</label>
			<div class="col-lg-8">
				<input id=presso tabindex=2 class=" <?php echo $class; ?> form-control resize vld_req" name=presso_recapito type=text value="<?php echo $presso_rec; ?>" size=24 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
    <div class="col col-lg-3">
        <div class="form-group">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Tipologia</label>
            <div class="col-lg-8">
                <select id=rec_type name=rec_type class="form-control resize validateCustom vld_Custom_r" onchange="checkDataMorte(this);">
                    <option></option>
                    <?php echo $options_rec_type; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Stato *</label>
			<div class="col-lg-8">
				<input id=paese tabindex=3 style="background-color: rgb(153, 204, 255); border: 2px solid black;" class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" name=paese_recapito type=text value="<?php echo $paese_rec; ?>" size=24 onchange="settaInput();forzaValidazione(this);" ondblClick="/*RicercheDaId('stato',1);*/openOfcanvas('stateSearchModal',1);" readonly>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
			<div class="col-lg-8">
				<input tabindex=4 id=comune style="background-color: rgb(153, 204, 255); border: 2px solid black;" class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" name=comune_recapito type=text value="<?php echo $comune_rec; ?>" size=24 onchange="forzaValidazione(this);" ondblClick="/*RicercheDaId('ente',1);*/openOfcanvas('citySearchModal',1);" readonly>
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group provincia_rec_dati_sogg">
			<label class="col-lg-4 control-label resize " style="text-align: left;">Prov.</label>
			<div class="col-lg-8">
				<input id=dati_sogg_prov tabindex="5" class=" <?php echo $class; ?>  form-control resize" type=text name=provDatiSogg value="<?php echo $provincia_rec; ?>" size=2>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fraz./Circoscriz.</label>
			<div class="col-lg-8">
				<input id=frazione tabindex=6 class=" <?php echo $class; ?> form-control resize vld_Fraz" name="frazione_recapito" type=text value="<?php echo $frazione_rec; ?>" size=24 ondblClick="RicercheDaId('frazione',0);" <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP *</label>
			<div class="col-lg-8">
				<input id=cap tabindex=7 class="<?php echo $class; ?> form-control resize validateForm vld_Custom_r vld_Custom_n" name=cap_recapito type=text value="<?php echo $CAP_rec; ?>" size=5 readonly >
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
				<input id=via tabindex=9 class=" <?php echo $class_ric; ?> form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=via_recapito type=text value="<?php echo $toponimo_rec; ?>" size=24 readonly onchange="forzaValidazione(this);" ondblClick="control_ind();">
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
				<input id=civico tabindex=10 class="form-control resize vld_int <?php echo $class; ?>" name="civico_recapito" style="width: 70%;"	type="text" value='<?php echo $civico_rec; ?>' 		size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input tabindex=11 id=esponente 	class="form-control resize vld_esp <?php echo $class; ?>" name="esponente_recapito" 	type="text" value='<?php echo $esponente_rec; ?>' 	size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input id=interno tabindex=12 class="form-control resize vld_int <?php echo $class; ?>" name="interno_recapito" 	type="text" value='<?php echo $interno_rec; ?>' 	size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
				<input id=dettagli tabindex="13"	class=" <?php echo $class; ?> form-control resize" name="dettagli_recapito" 	type="text" value='<?php echo $dettagli_rec; ?>' 	size=14 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
</div>

<div class="row" id=scelta_indirizzo_2>
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Indirizzo *</label>
			<div class="col-lg-11">
				<input id=via_estero tabindex="9" class=" <?php echo $class; ?> form-control resize" name=via_estera_recapito type=text value="<?php echo $toponimo_rec; ?>" size=80 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input id=tel tabindex=15 class="form-control resize vld_tel <?php echo $class; ?>" name=tel_recapito type=text value='<?php echo $telefono_rec; ?>' size=18 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input id=fax tabindex=16 class="form-control resize vld_tel <?php echo $class; ?>" name=fax_recapito type=text value='<?php echo $fax_rec; ?>' size=18 <?php echo $readonly; ?> onblur="focusCampo()">
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<script>
$( document ).ready(function() {

settaInput();

$('#ordinamento').val("<?= $ordinamento; ?>");

if("<?= $paese_rec; ?>"!="Italia" && "<?= $mode; ?>" =="modifica")
{
	$('#comune').addClass('sfondo_bianco').removeClass('sfondo_ricerca');
	$('#comune').attr('readonly',false);
	$('#cap').attr('readonly',false);
}

if($("#paese").val()=="")
{
		$("#paese").val("Italia");
		$('#scelta_indirizzo_2').hide();
		$('#scelta_indirizzo_1').show();
		$("#via_estero").removeClass("validateCustom vld_Custom_r");
		$("#via").addClass("validateCustom vld_Custom_r");
		document.getElementById("paese").dispatchEvent(new Event("change"));
}
else{
	if($("#paese").val()=="Italia")
	{
		$('#scelta_indirizzo_2').hide();
		$('#scelta_indirizzo_1').show();
		$("#via_estero").removeClass("validateCustom vld_Custom_r");
		$("#via").addClass("validateCustom vld_Custom_r");
	}
	else {
		$('#scelta_indirizzo_2').show();
		$('#scelta_indirizzo_1').hide();
		$("#via").removeClass("validateCustom vld_Custom_r");
		$("#via_estero").addClass("validateCustom vld_Custom_r");
	}
	document.getElementById("paese").dispatchEvent(new Event("change"));
}

});
</script>

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
                top.location.href="<?= WEB_ROOT; ?>/anagrafe/recapito.php?mode=consulta&p=" + val["ID"] + "&c=" + val["CC_Comune"] + "&a=<?php echo $a; ?>";
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
