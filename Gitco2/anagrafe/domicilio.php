<?php
	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";

	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";*/

    if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include(INC."/header.php");
	include_once(INC."/menu.php");
	include_once(CLS."/cls_DateTimeInLine.php");
	include_once(CLS."/cls_anagrafeUtils.php");

    $submenuPageNo = 4;
    $pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';

	$cls_date = new cls_DateTimeI("IT",false);
	$cls_anagr = new cls_anagr();


	//if (!session_id()) session_start();



	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$mode = $cls_help->getVar('mode');

	$servizio = $cls_help->getVar('servizio');

	$mode = "modifica";//ANNULLO CONSULTA

	if($mode=="consulta" || $mode==null)
	{
		$mode = "consulta";
		$disabled = " disabled ";
		$readonly = " readonly ";
		$class = " sfondo_readonly ";
		$class_ric = " sfondo_readonly ";
		$class_calcolo = " sfondo_readonly ";
	}
	else
	{
		$mode = "modifica";
		$disabled = "";
		$readonly = "";
		$class_ric = " sfondo_ricerca ";
		$class = " sfondo_bianco ";
		$class_calcolo = " sfondo_giallo ";
	}

	//$comune = new ente_gestito($c);
	$nome_comune = $a_enteAdmin["Denominazione"];// $comune->Nome;

	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];

	$QUERY = $cls_anagr->get_Query_Dati_Soggetto($p,$c);
	$anagr = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Soggetto"]),"utente");

	//echo $QUERY["Soggetto"];
	//$utente = new utente($p,$c);

	$id_utente 				= 	$anagr["ID"]; //$utente->ID;
	$genere_utente 			= 	$anagr["Genere"]; //$utente->Genere;
	$comune_id 				=	$anagr["Comune_ID"]; //$utente->Comune_ID;

	if($genere_utente!='D')
	{
		$cognome_utente 	=	$anagr["Cognome"]; //$utente->Cognome;
		$nome_utente 		=	$anagr["Nome"]; //$utente->Nome;
		$CC_nascita			=	$anagr["CC_Nascita"]; //$utente->CC_Nascita;
		$paese_nasc_utente  =	$anagr["Paese_Nascita"]; //$utente->Paese_Nascita;
		if($paese_nasc_utente==null)
		{
			$paese_nasc_utente = "Italia";
		}
		$comune_nasc_utente =	$anagr["Comune_Nascita"]; //$utente->Comune_Nascita;
	 $provincia_nasc_utente	=	$anagr["Provincia_Nascita"]; //$utente->Provincia_Nascita;
		$data_nasc_utente	=	$cls_date->Get_DateNewFormat($anagr["Data_Nascita"],"DB");// from_mysql_date($anagr["Data_Nascita"]; //$utente->Data_Nascita);
		$data_morte_utente	=	$cls_date->Get_DateNewFormat($anagr["Data_Morte"],"DB");//from_mysql_date($anagr["Data_Morte"]; //$utente->Data_Morte);
		$CF					=	$anagr["Codice_Fiscale"]; //$utente->Codice_Fiscale;
	}
	else
	{
		$ditta				=	$anagr["Ditta"]; //$utente->Ditta;
		$PI					=	$anagr["Partita_Iva"]; //$utente->Partita_Iva;
		$prec_den_ditta		=	$anagr["Prec_Denom"]; //$utente->Prec_Denom;
		$anno_cambio_ditta	=	$anagr["Anno_Cambio_Denom"]; //$utente->Anno_Cambio_Denom;
	}

	$indirizzo_dom			=	$cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY["Indirizzo_D"]));; //$utente->Domicilio;
	//print_r($QUERY["Indirizzo_D"]);

	$type_ind = gettype($indirizzo_dom);

	$ID_via_dom = 0;
	$ID_via_cap_dom = 0;

	if($type_ind == "array")
	{
		$ID_dom		 		= 	$indirizzo_dom["ID"]; //$indirizzo_dom->ID;

		$ID_via_dom			=	$indirizzo_dom["Via_ID"]; //$indirizzo_dom->Via_ID;
		$ID_via_cap_dom		=	$indirizzo_dom["Via_Cap_ID"]; //$indirizzo_dom->Via_Cap_ID;


		$CC_dom				=	$indirizzo_dom["CC_Indirizzo"]; //$indirizzo_dom->CC_Indirizzo;
		$paese_dom			=	$indirizzo_dom["Paese"]; //$indirizzo_dom->Paese;
		if($paese_dom==null)
		{
			$paese_dom = "Italia";
		}

		$QUERY_2 = $cls_anagr->get_Query_Dati_Soggetto_Via(array("ViaID" => $indirizzo_dom["Via_ID"] ,"CapID" => $indirizzo_dom["Via_Cap_ID"]),$c);
		$Via_Object = null;
		if($QUERY_2!="")
			$Via_Object = $cls_db->getArrayLine($cls_db->ExecuteQuery($QUERY_2));



		$comune_dom			=	$indirizzo_dom["Comune"]; //$indirizzo_dom->Comune;
		$provincia_dom		=	$indirizzo_dom["Provincia"]; //$indirizzo_dom->Provincia;
		$frazione_dom		=   $indirizzo_dom["Frazione"]; //$indirizzo_dom->Frazione;
		if($Via_Object == null) $toponimo_dom = "";
		else $toponimo_dom		=	$Via_Object["Nome"]; //$indirizzo_dom->Toponimo->Nome;
		$civico_dom			=	$indirizzo_dom["Civico"]; //$indirizzo_dom->Civico;
		$esponente_dom		=	$indirizzo_dom["Esponente"]; //$indirizzo_dom->Esponente;
		$CAP_dom			=	$indirizzo_dom["Cap"]; //$indirizzo_dom->Cap;
		$interno_dom		=	$indirizzo_dom["Interno"]; //$indirizzo_dom->Interno;
		$dettagli_dom		=	$indirizzo_dom["Dettagli"]; //$indirizzo_dom->Dettagli;
		$telefono_dom		=	$indirizzo_dom["Telefono"]; //$indirizzo_dom->Telefono;
		$fax_dom			=	$indirizzo_dom["Fax"]; //$indirizzo_dom->Fax;
	}
	else
	{
		$CC_dom				=	"";
		$paese_dom			=	"";
		if($paese_dom==null)
		{
			$paese_dom = "Italia";
		}

		$comune_dom			=	"";
		$provincia_dom		=	"";
		$frazione_dom		=   "";
		$toponimo_dom		=	"";
		$civico_dom			=	"";
		$esponente_dom		=	"";
		$CAP_dom			=	"";
		$interno_dom		=	"";
		$dettagli_dom		=	"";
		$telefono_dom		=	"";
		$fax_dom			=	"";
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



	if($indirizzo_dom == null)	{	$submit_name = "Insert";	}
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
/**
 * GESTIONE F2 /////////////////////////////////////////
 */

?>
<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Anagrafe - Domicilio</title>

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
stringa = "domicilio.php?"+stringaPHP;
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


        link = "recapito.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
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
			link = "dettagli.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
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
		link = "domicilio.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

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
		link = "domicilio.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Domicilio.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help ANAGRAFE Domicilio</b>");
    $("#helpModal").modal('show');

}

</script>

<script>    /* -----------  AJAX FORM SUBMIT ----------- */

$(document).ready(function(){

	$('#id_cerca').focus();

    /*	if(modalita=="modifica")
    	{


	$("#submit_click").click(function() {

		campi = controllaCampi();

		if(campi==true)
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
                    alert(value);
                    array_ritorno = value.split(' ');
                    switch(array_ritorno[0])
                    {
                    	case "Delete":
                        	if(array_ritorno[1]=='Si')
                        	{
                            	alert('Utente ID '+array_ritorno[2]+' - domicilio eliminato con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("Errore nel tentativo di eliminazione del domicilio relativo all'ID Utente "+array_ritorno[3]+".");
                        	}

							value = <?php echo $p ?>;

                    	break;

                    	case "Update":
                        	if(array_ritorno[1]=='Si')
                        	{
                            	alert('ID Utente '+array_ritorno[3]+' - domicilio aggiornato con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("Errore nel tentativo di aggiornamento del domicilio relativo all'ID Utente "+array_ritorno[3]+".");
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
                            	alert('Nuovo ID Utente '+array_ritorno[3]+' - domicilio inserito con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("ID Utente "+array_ritorno[2]+" - Errore nel tentativo di inserimento del nuovo domicilio.");
                        	}
                        	else if(array_ritorno[1]=='Via')
                        	{
                        		alert("Errore nel tentativo di inserimento del nuovo indirizzo.");
                        	}

                        	value = array_ritorno[2];

                    	break;

                    }
            top.location.href = "domicilio.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
        });
    	}*/

	$('#cerca_id').ajaxForm(
                function(value) {
                    var array_ritorno = value.split(' ');
			if(array_ritorno[0]=='NO')
			{
				alert('Codice utente non trovato!');
				top.location.href = "domicilio.php?mode=consulta&p="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
			else
			{
        		top.location.href = "domicilio.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
        });

    });

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
			top.location.href = "domicilio.php?mode=modifica&ordinamento=" + value_ord + stringaPHP;
   		}
   		else
   		{
   			if(utente_ID!=0)
   			top.location.href = "domicilio.php?mode=consulta&ordinamento=" + value_ord + stringaPHP;
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
		link = "domicilio.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;
	else if( value == 'next' )
		link = "domicilio.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

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
			link = "dettagli.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
		else if(value==0 && (modalita=="consulta" || utente_ID!=0))
		{
			link = "recapito.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
	}
}

//ANNULLA
   	function annulla()
   	{
   		stringaPHP += "&mode=consulta";
		stringa = "domicilio.php?"+stringaPHP;
   	   	top.location.href = stringa;
   	}*/
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

   	//<!-- domicilio -->
		//<!-- CONTROLLO INSERIMENTO PAESE DI domicilio -->
		var paese = $('input#paese').val();
		if ((paese == "") || (paese == "undefined"))
		{
			alert("Il campo Stato di Domicilio \xE8 obbligatorio.");
			return false;
		}
		control_paese = paese.match(pattern_nome);
		if(control_paese)
		{
		  	alert("Il campo Stato di Domicilio non puo' contenere caratteri speciali o numerici.");
		   	return false;
		}

		//<!-- CONTROLLO INSERIMENTO COMUNE DI DOMICILIO -->
		var comune = $('input#comune').val();
		if ((comune == "") || (comune == "undefined"))
		{
			alert("Il campo Comune di Domicilio \xE8 obbligatorio.");
			return false;
		}
		control_comune = comune.match(pattern_nome);
		if(control_comune)
		{
		  	alert("Il campo Comune di Domicilio non puo' contenere caratteri speciali o numerici.");
		   	return false;
		}

		var cap = $('input#cap').val();
		var frazione = $('input#frazione').val();
		if ( paese == "Italia" )
		{
			var via = $('input#via').val();
			if ((via == "") || (via == "undefined"))
			{
				alert("Il campo Indirizzo di Domicilio \xE8 obbligatorio.");
				return false;
			}

			//<!-- CONTROLLO INSERIMENTO CAP DI DOMICILIO -->
			control_cap = cap.match(pattern_numeri);
			if(control_cap)
			{
			  	alert("Il campo CAP di Domicilio puo' contenere solo caratteri numerici");
			   	return false;
			}

			//<!-- CONTROLLO INSERIMENTO INDIRIZZO DI DOMICILIO -->
			control_via = via.match(pattern_speciali);
			if(control_via)
			{
			  	alert("Il campo Indirizzo di Domicilio non puo' contenere caratteri speciali.");
			   	return false;
			}

			//<!-- CONTROLLO INSERIMENTO FRAZIONE DI DOMICILIO -->
			control_frazione = frazione.match(pattern_nome);
			if(control_frazione)
			{
			  	alert("Il campo Frazione di Domicilio non puo' contenere caratteri speciali o numerici.");
			   	return false;
			}

		}
		else
		{
			var via = $('input#via_estero').val();
			if ((via == "") || (via == "undefined"))
			{
				alert("Il campo Indirizzo di Domicilio \xE8 obbligatorio.");
				return false;
			}

			control_cap = cap.match(pattern_speciali);
			if(control_cap)
			{
			  	alert("Il campo CAP di Domicilio non puo' contenere caratteri speciali.");
			   	return false;
			}

			control_via = via.match(pattern_speciali);
			if(control_via)
			{
			  	alert("Il campo Indirizzo di Domicilio non puo' contenere caratteri speciali.");
			   	return false;
			}

			control_frazione = frazione.match(pattern_speciali);
			if(control_frazione)
			{
			  	alert("Il campo Frazione di Domicilio non puo' contenere caratteri speciali.");
			   	return false;
			}
		}

		//<!-- CONTROLLO INSERIMENTO CIVICI DI domicilio -->
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
}

   	function func_stato_estero_indirizzo(value)
   	{
		if(value=="nascondi")
   	   	{
	   		$('#comune_domicilio').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
			$('#comune_domicilio').attr('readonly',false);
			$('.provincia_dom_dati_sogg').hide();
   	   	}
   	   	else if(value=="mostra")
   	   	{
	   	   	$('#comune_domicilio').removeClass('sfondo_bianco').addClass('sfondo_ricerca');
			$('#comune_domicilio').attr('readonly',true);
			$('.provincia_dom_dati_sogg').show();
   	   	}
   	}

   	function addRemoveClass(el)
    {
        if(el.value=="Italia") $("#via_estero").removeClass( "validateCustom vld_Custom_r");
        else $("#via_estero").addClass("validateCustom vld_Custom_r");
    }
</script>

<?php

$menuPageNumber = "Pag 4/7";
$pagina = "domicilio.php";
include_once(INC."/submenu_anagrafe.php");
include_once(INC."/pages_authorization.php");
?>

<form id=anagrafe_form class="form-horizontal validate" name=domicilio action="domicilio_salva.php" method=post >

<input name=ID_via			id=ID_via		type=hidden value="<?php echo $ID_via_dom; ?>"			>
<input name=ID_via_cap		id=ID_via_cap	type=hidden value="<?php echo $ID_via_cap_dom; ?>"		>
<input name=CC_domicilio	id=CC			type=hidden value="<?php echo $CC_dom; ?>"				>
<input name=a 								type=hidden value="<?php echo $a; ?>"					>
<input name=p 								type=hidden value="<?php echo $p; ?>"					>
<input name=comune_id						type=hidden value="<?php echo $comune_id; ?>"			>
<input name=ID_dom 							type=hidden value="<?php echo isset($ID_dom)?$ID_dom:null; ?>"				>
<input name=c 								type=hidden value="<?php echo $c; ?>"					>
<input name=servizio 						type=hidden value="<?php echo $servizio; ?>"			>
<input name=invia_submit 	id=invia_submit	type=hidden	value=""									>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Domicilio</span>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Stato *</label>
			<div class="col-lg-8">
				<input id=paese tabindex=2 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=paese_domicilio type=text value="<?php echo $paese_dom; ?>" size=24 ondblClick="/*RicercheDaId('stato',1);*/openOfcanvas('stateSearchModal',1);" onchange="validateForm(this);addRemoveClass(this);" readonly>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
			<div class="col-lg-8">
				<input id=comune tabindex=3 class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=comune_domicilio type=text value="<?php echo $comune_dom; ?>" size=24 ondblClick="/*RicercheDaId('ente',1);*/openOfcanvas('citySearchModal',1);" onchange="validateForm(this);" readonly>
			</div>
		</div>
	</div>
	<div class="col col-lg-2 col-lg-offset-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Prov.</label>
			<div class="col-lg-8">
				<input id=dati_sogg_prov tabindex="4" class=" <?php echo $class; ?> provincia_dom_dati_sogg form-control resize" type=text name=provDatiSogg value="<?php echo $provincia_dom; ?>" size=2>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fraz./Circoscriz.</label>
			<div class="col-lg-8">
				<input id=frazione class=" <?php echo $class; ?> form-control resize vld_Fraz" tabindex=5 name="frazione_domicilio" type=text value="<?php echo $frazione_dom; ?>" size=24 ondblClick="RicercheDaId('frazione',0);" <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">CAP *</label>
			<div class="col-lg-8">
				<input id=cap tabindex=6 class="<?php echo $class; ?> form-control resize validateCustom vld_Custom_r vld_Custom_n" name=cap_domicilio type=text value="<?php echo $CAP_dom; ?>" onchange="validateForm(this);" size=5 readonly >
			</div>
		</div>
	</div>
</div>

<div class="row" id=scelta_indirizzo_1>
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">Indirizzo*</label>
			<div class="col-lg-1">
				<a tabindex="17" onMouseover="title='Correzione indirizzo'" href="#" onclick="/*RicercheDaId('via',1);*/insAddr();" style="text-decoration: none;">
					<i class="fas fa-edit"></i>
				</a>
			</div>
			<div class="col-lg-8">
				<input id=via tabindex="18" class=" <?php echo $class_ric; ?> form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=via_domicilio type=text value="<?php echo $toponimo_dom; ?>" size=24 readonly ondblclick="control_ind();" onchaing="validateForm(this);" >
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Civ.</label>
			<div class="col-lg-8">
				<input id=civico tabindex="19" class="form-control resize vld_int <?php echo $class; ?>" name="civico_domicilio" 		type="text" value='<?php echo $civico_dom; ?>' 		size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
			<div class="col-lg-8">
				<input id=esponente tabindex="20"	class="form-control resize vld_esp <?php echo $class; ?>" name="esponente_domicilio" 	type="text" value='<?php echo $esponente_dom; ?>' 	size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Int.</label>
			<div class="col-lg-8">
				<input id=interno tabindex="21"	class="form-control resize vld_int <?php echo $class; ?>" name="interno_domicilio" 	type="text" value='<?php echo $interno_dom; ?>' 	size=2 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
			<div class="col-lg-8">
				<input id=dettagli tabindex="22"	class=" <?php echo $class; ?> form-control resize" name="dettagli_domicilio" 	type="text" value='<?php echo $dettagli_dom; ?>' 	size=14 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
</div>

<div class="row" id=scelta_indirizzo_2>
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Indirizzo*</label>
			<div class="col-lg-11">
				<input id=via_estero tabindex="17" class=" <?php echo $class; ?> form-control resize" name=via_estera_domicilio type=text value="<?php echo $toponimo_dom; ?>" size=80 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
</div>

<div class="row" id=scelta_indirizzo_2>
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
			<div class="col-lg-8">
				<input id=tel tabindex=23 class="form-control resize vld_tel <?php echo $class; ?>" name=tel_domicilio type=text value='<?php echo $telefono_dom; ?>' size=18 <?php echo $readonly; ?>>
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
			<div class="col-lg-8">
				<input id=fax tabindex=24 class="form-control resize vld_tel <?php echo $class; ?>" name=fax_domicilio type=text value='<?php echo $fax_dom; ?>' size=18 <?php echo $readonly; ?> >
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

$('#ordinamento').val('<?= $ordinamento; ?>');

if("<?= $paese_dom; ?>"!="Italia" && "<?= $mode; ?>"=="modifica")
{
	$('#comune').addClass('sfondo_bianco').removeClass('sfondo_ricerca');
	$('#comune').attr('readonly',false);
	$('#cap').attr('readonly',false);
}

if("<?= $paese_dom; ?>"!="Italia")
{
	func_stato_estero_indirizzo('nascondi');
	$('#scelta_indirizzo_2').show();
	$('#scelta_indirizzo_1').hide();
    $("#via_estero").addClass("validateCustom vld_Custom_r");
    $('#comune').removeClass("validateCustom vld_Custom_r");
    $('#cap').removeClass("validateCustom vld_Custom_r");
    $('#comune').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
    $('#comune').attr('readonly', false);
    $('#comune').css("background-color","");
    $('#comune').css("border","");
}
else
{
	$('#scelta_indirizzo_2').hide();
	$('#scelta_indirizzo_1').show();
    $("#via_estero").removeClass( "validateCustom vld_Custom_r");
    $('#comune').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
    $('#comune').attr('readonly', 'readonly');
    $('#comune').css("background-color","rgb(153, 204, 255)");
    $('#comune').css("border","2px solid black");
}

//InizializzaAttributi();

/*if($("#paese").val()=="")
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
	$("#via").css("background-color","rgb(153, 204, 255)");
	$("#via").css("border","2px solid black");
	document.getElementById("paese").dispatchEvent(new Event("change"));
}*/

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
                top.location.href="<?= WEB_ROOT; ?>/anagrafe/domicilio.php?mode=consulta&p=" + val["ID"] + "&c=" + val["CC_Comune"] + "&a=<?php echo $a; ?>";
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

<!--</body>
</html>-->
