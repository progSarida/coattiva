<?php
	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	header("Content-Type: text/html; charset=utf-8");

	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";*/

    if (!session_id()) session_start();

    include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include(INC."/header.php");
	include_once(INC."/menu.php");
	include_once(CLS."/cls_DateTimeInLine.php");
	include_once(CLS."/cls_anagrafeUtils.php");

    $submenuPageNo = 2;
    $pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';

	$cls_date = new cls_DateTimeI("IT",false);
	$cls_anagr = new cls_anagr();

	//if (!session_id()) session_start();

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
	}
	else
	{
		$mode = "modifica";
		$readonly = "";
		$class = " sfondo_bianco ";
	}


	//$comune = new ente_gestito($c);
	$nome_comune = $a_enteAdmin["Denominazione"];//$comune->Nome;

	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];

	$submit_name = "Update";

	$QUERY = $cls_anagr->get_Query_Dati_Soggetto($p,$c);
	$anagr = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Soggetto"]),"utente");
//echo $QUERY["Soggetto"];

	//$utente = new utente($p,$c);

	$id_utente 				= 	$anagr["ID"];//$utente->ID;
	$genere_utente 			= 	$anagr["Genere"];//$utente->Genere;
	$comune_id 				=	$anagr["Comune_ID"];//$utente->Comune_ID;

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

	$note_utente			=	$anagr["Note"];//$utente->Note;
	$cell_utente			=	$anagr["Cellulare"];//$utente->Cellulare;
	$mail_utente			=	$anagr["Mail"];//$utente->Mail;
	$data_registrazione		=	$cls_date->Get_DateNewFormat($anagr["Data_Registrazione"],"DB");//from_mysql_date($utente->Data_Registrazione);
	if($data_registrazione==null)
	{
		$data_registrazione = date('d/m/Y');
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

<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<!-- Keep the http-equiv meta tag for IE8
<meta http-equiv="X-UA-Compatible" content="IE=8" />

<title>Anagrafe - Annotazioni</title>

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

	$('#id_cerca').focus();

	/*if(modalita=="modifica")
	{



	$("#submit_click").click(function salva_form() {
    	control=submit_buttons('Update');
     	if(control)
        	$("#anagrafe_form").submit();
        });

    $("#delete_click").click(function cancella_form() {
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
                            	alert('ID Utente '+array_ritorno[2]+' - note eliminate con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("Errore nel tentativo di eliminazione delle note realtive all'ID Utente "+array_ritorno[2]+".");
                        	}

							value = <?php echo $p ?>;

                    	break;

                    	case "Update":
                        	if(array_ritorno[1]=='Si')
                        	{
                            	alert('ID Utente '+array_ritorno[3]+' - note aggiornate con successo!');
                        	}
                        	else if(array_ritorno[1]=='No')
                        	{
                        		alert("Errore nel tentativo di aggiornamento note dell'ID Utente "+array_ritorno[2]+".");
                        	}

                        	value = array_ritorno[2];

                    	break;

                    }
            top.location.href = "annotazioni.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
        });
	}*/

	$('#cerca_id').ajaxForm(
                function(value) {
                    var array_ritorno = value.split(' ');
			if(array_ritorno[0]=='NO')
			{
				alert('Codice utente non trovato!');
				top.location.href = "annotazioni.php?mode=consulta&p="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
			else
			{
        		top.location.href = "annotazioni.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
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
				top.location.href = "annotazioni.php?mode=modifica&ordinamento=" + value_ord + stringaPHP;
	   		}
	   		else
	   		{
	   			if(utente_ID!=0)
	   			top.location.href = "annotazioni.php?mode=consulta&ordinamento=" + value_ord + stringaPHP;
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
/*	function gira_utente (value)
	{

		if (modifica==1)
		{
			alert('salvare i dati o annullare prima di procedere');
		}
		else
		{
			value_ord = $('#ordinamento').val();

		if( value == 'prev' )
			link = "annotazioni.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;
		else if( value == 'next' )
			link = "annotazioni.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

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
				link = "recapito.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
				top.location.href = link;
			}
			else if(value==0 && (modalita=="consulta" || utente_ID!=0))
			{
				link = "dati_soggetto.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
				top.location.href = link;
			}
		}
	}*/

//ANNULLA
   	/*function annulla()
   	{
   		stringaPHP += "&mode=consulta";
		stringa = "annotazioni.php?"+stringaPHP;
   	   	top.location.href = stringa;
   	}

   	function controllaCampi()
   	{
   	   	return true;
   	}*/
</script>

<script>
//Apertura modale modifica campo
function openOfcanvas(id_off,rif){
    // Reset campi input
    $('#user_name').val("");
    $('#user_cf').val("");

    // Reset spazi tabella
    $('#appendTableUser').empty();

    flagAQjaxReserch = true;
    switch (id_off){
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
    }
}

// Sostituzione da modale
function initialId(tipo,val){
    //alert("initial --> "+tipo);
    flagAQjaxReserch = false;
    switch(tipo)
    {
        // Sostituzione dati utente
        case "user":
        case "cf":
            top.location.href="<?= WEB_ROOT; ?>/anagrafe/annotazioni.php?mode=consulta&p=" + val["ID"] + "&c=" + val["CC_Comune"] + "&a=<?php echo $a; ?>";
            break;

        default: alert("Ricerca non trovata!"); break;
    }

}


//F3
switchMenuImg("F3");
F3_button = function()
{
	control=submit_buttons('Update');
 	if(control)
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
	stringa = "annotazioni.php?"+stringaPHP;
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

			link = "dati_soggetto.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;

	}
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function()
{
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();

		if(modalita=="consulta" || utente_ID!=0)
		{
			link = "recapito.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
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
		link = "annotazioni.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

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
		link = "annotazioni.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Annotazioni.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help ANAGRAFE Annotazioni</b>");
    $("#helpModal").modal('show');

}

</script>

<!--</head>

<body>

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
<td valign=top>




<table align=center class=table_interna border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='<?php echo $F2_title; ?>'" href="#" onclick="<?php echo $F2_click; ?>" >
			<img src="<?php echo $F2_path; ?>" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="<?php if($mode=="consulta") echo "/gitco2/immagini/Save-iconF3grey.png"; else echo "/gitco2/immagini/Save-iconF3.png"; ?>" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="<?php if($mode=="consulta") echo "/gitco2/immagini/delete-iconF4grey.png"; else echo "/gitco2/immagini/delete-iconF4.png"; ?>" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Nuovo Record'" href="#" onClick="link('new');" style="text-decoration: none;">
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
          	<a id=F7 href="#" onMouseover=" title='Record precedente F7' " onclick="gira_utente('prev');">
          	<img src="/gitco2/immagini/FrecciaS.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
		</td>
		<td width=7% align="center">
          	<a id=F8 href="#" onMouseover=" title='Record successivo F8' " onclick="gira_utente('next');">
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



<!--<table align=center class=table_interna border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=8% class="text_center">
			<a onMouseover="title='Cerca utente'" href="#" onclick="RicercheDaId('utente',0);" style="text-decoration: none;">
			<img src="/gitco2/immagini/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7%>
		<?php if($mode=="consulta"){ if($p!=0) {?>

			<img src="/gitco2/immagini/semaforoRosso.png" width=50 height=50 border=0>

		<?php } else {?>

			<img src="/gitco2/immagini/semaforoSpento.png" width=50 height=50 border=0>

		<?php } }
		else if($mode=="modifica"){ if($p!=0){?>

			<img src="/gitco2/immagini/semaforoVerde.png" width=50 height=50 border=0>

		<?php } else {?>

			<img src="/gitco2/immagini/semaforoGiallo.png" width=50 height=50 border=0>

		<?php } }?>
		</td>
		<td width=15% class="text_center"><font class="titolo font18">ANAGRAFE</font><font class="titolo font14"> Pag 2/6</font></td>
    	<td width=34% class="text_left">
            <em style="font-style : normal ;">
            <?php if($genere_utente!='D'){echo $cognome_utente." ".$nome_utente;}else{ echo $ditta; } ?></em>
        </td>
        <td width=14% class="text_center">
        	<font class="color_titolo font16">Ordinamento</font>
        	<select id=ordinamento name=ordinamento onchange="ordinamento();"><option value=ID>ID utente</option><option value=Nome>Alfabetico</option></select>
        </td>
        <td class="text_left width4"><input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" title="Gestione Ruolo" onclick="ruolo('<?php echo $p; ?>');">
        <td width=18% align=right>
		<form id=cerca_id method=post action=modali/ricerca_codice_result.php>
			<input type=hidden name=old_cod_contr value='<?php echo $comune_id; ?>'>
           	<input name=c type=hidden value='<?php echo $c; ?>'>
            <input name=a type=hidden value='<?php echo $a; ?>'>
		Utente ID &nbsp;
		<input id=id_cerca tabindex=1 class="valign_center text_right" type=text name=ric_cod_contr value='<?php echo $comune_id; ?>' size=3 onMouseover="title='Inserire il codice utente e premere Invio'">&nbsp;&nbsp;</form>

		</td>
</tr>
</table>-->
<?php
$menuPageNumber = "Pag 2/7";
$pagina = "annotazioni.php";
include_once(INC."/submenu_anagrafe.php");
include_once(INC."/pages_authorization.php");
?>

<form id=anagrafe_form name=note method=post class="form-horizontal validate" action="annotazioni_salva.php">

<input name=c type=hidden value=<?php echo $c; ?>>
<input name=a type=hidden value=<?php echo $a; ?>>
<input name=p type=hidden value=<?php echo $p; ?>>
<input name=servizio type=hidden value=<?php echo $servizio; ?>>
<input name=comune_id						type=hidden value="<?php echo $comune_id; ?>"			>
<input name=invia_submit id=invia_submit	type=hidden	value=""	>


<div class="row justify-content-md-center " style="margin-top: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Annotazioni</span>
	</div>
</div>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<div class="col-lg-12">
				<textarea tabindex=2 id=annotazioni name=annotazioni style="max-width: 100%; width: 100%;" class=" <?php echo $class; ?> form-control resize" rows=12% <?php echo $readonly; ?> onblur="focusCampo();"><?php echo $note_utente; ?></textarea>
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>
</form>

<!-- Inclusione modali -->
<?php include_once (ROOT."/search_modal/offcanvas/user_offcanvas.php"); ?>
<?php //include_once (ROOT."/search_modal/startAjax.php"); ?>

<script>

$( document ).ready(function() {

	$('#ordinamento').val('<?= $ordinamento; ?>');

});
</script>

<?php include(INC."/footer.php"); ?>

<!--</body>
</html>-->
