<?php
	
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/classe_anni.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	
	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	
	$a = get_var('a');
	$c = get_var('c');
	$p = get_var('p');
	
	$partita_ID = get_var('partita');
	
	$comune = new ente_gestito($c);
	$nome_comune = $comune->Nome;

	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];
	
	$layout = "<script>";
	
	$anni_gestiti = new anni_gestiti($c, null);
	
	if($c==null)
		$options_anni = null;
	else
	{
		$options_anni = $anni_gestiti->Options_Anni_Veloci($c, "COATTIVA", "ricorso");
	
		if($a!=null)
			$layout.="$('#select_anno_veloce option[value=".$a."]').attr('selected',true);";
	}
	
	$layout.= "</script>";

	$partita = new partita($partita_ID, $c, $a);

	$ID_Partita = $partita->Comune_ID;

	$anno_riferimento = $partita->Anno_Riferimento;

	$utente_ID = $partita->Utente_ID;
	$utente = new utente($utente_ID,$c);

	$id_utente 				= 	$utente->ID;
	$genere_utente 			= 	$utente->Genere;
	$comune_id 				=	$utente->Comune_ID;
	$cognome_utente 		=	$utente->Cognome;
	$nome_utente 			=	$utente->Nome;
	$ditta					=	$utente->Ditta;
	
	$tipo = $partita->Tipo;
	$atto = $partita->Atto;

	$prev = $partita->prev;
	$next = $partita->next;
	
	$count_ric = count($partita->Ricorso);
	if($count_ric==0)	$invia_submit = "Insert";
	else				$invia_submit = "Update";
	
	if($count_ric>0)
	{
	
	$ric = $partita->Ricorso[0];
	
	$id_ricorso = $ric->ID;
	$tipo = $ric->Tipo_Ricorso;
	$data_reg = $ric->Data_Registrazione;
	$data_chiusura = $ric->Data_Chiusura;
	$ufficio_id = $ric->Ufficio->ID;
	$ufficio_sede = $ric->Ufficio->Comune;
	$ufficio_sez = $ric->Ufficio->Sezione;
	
	$sospensiva = $ric->Sospensiva;
	$esito_sosp = $ric->Esito_Sosp;
	$num_sosp = $ric->Num_Sosp;
	$data_sosp = from_mysql_date($ric->Data_Sosp);
	$data_dep_sosp = from_mysql_date($ric->Data_Dep_Sosp);
	$data_not_sosp = from_mysql_date($ric->Data_Not_Esito_Sosp);
	
	$merito = $ric->Merito;
	$esito_merito = $ric->Esito_Merito;
	$num_merito = $ric->Num_Merito;
	$data_merito = from_mysql_date($ric->Data_Merito);
	$data_dep_merito = from_mysql_date($ric->Data_Dep_Merito);
	$data_not_merito = from_mysql_date($ric->Data_Not_Esito_Merito);
	
	$data_ric_sentenza = from_mysql_date($ric->Data_Richiesta_Sentenza);
	$data_imp_sentenza = from_mysql_date($ric->Data_Impugnazione_Sentenza);
	$tot_da_pagare = conv_num($ric->Totale_Da_Pagare);
	if($tot_da_pagare == "0,00")
		$tot_da_pagare = "";
	
	$RG_pagato = $ric->RG_Pagato;
	if($RG_pagato == 'Y') $layout.="<script>$('#RG_pagato').prop('checked',true);</script>";
		
	$socc_pagata = $ric->Soccombenza_Pagata;
	if($socc_pagata == 'Y') $layout.="<script>$('#socc_pagata').prop('checked',true);</script>";
	
	$importo = conv_num($ric->Importo);
	if($importo == "0,00")
		$importo = "";
	$data_pag = from_mysql_date($ric->Data_Pagamento);
	$descr_pag = $ric->Descrizione_Pagamento;
	$note = $ric->Note;
	if($note == "")
		$class_note = "sfondo_azzurro";
	else
		$class_note = "sfondo_red";
	
	$udienza = $ric->Udienze;
	if(count($udienza)>0 && $udienza != "")
		$class_udienza = "sfondo_red";
	else
		$class_udienza = "sfondo_azzurro";
		
	
	$count_cit = count($ric->Atto_Citazione);
	if($count_cit == 1 && $tipo == "atto_citazione")
	{
		
		$cit = $ric->Atto_Citazione;
		
		$attore_id_1 = $cit->Attore_1_ID;
		if($attore_id_1 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_1."'");
			$attore_nome_1 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else 
		{
			$attore_nome_1 = "";
		}
		
		$attore_id_2 = $cit->Attore_2_ID;
		if($attore_id_2 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_2."'");
			$attore_nome_2 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else 
		{
			$attore_nome_2 = "";
		}
		
		$attore_id_3 = $cit->Attore_3_ID;
		if($attore_id_3 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_3."'");
			$attore_nome_3 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else 
		{
			$attore_nome_3 = "";
		}
		
		$attore_id_4 = $cit->Attore_4_ID;
		if($attore_id_4 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_4."'");
			$attore_nome_4 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else 
		{
			$attore_nome_4 = "";
		}
		
		$convenuto_id_1 = $cit->Convenuto_1_ID;
		if($convenuto_id_1 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_1."'");
			$convenuto_nome_1 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_1 = "";
		}
		
		$convenuto_id_2 = $cit->Convenuto_2_ID;
		if($convenuto_id_2 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_2."'");
			$convenuto_nome_2 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_2 = "";
		}
		
		$convenuto_id_3 = $cit->Convenuto_3_ID;
		if($convenuto_id_3 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_3."'");
			$convenuto_nome_3 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_3 = "";
		}
		
		$convenuto_id_4 = $cit->Convenuto_4_ID;
		if($convenuto_id_4 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_4."'");
			$convenuto_nome_4 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_4 = "";
		}
		
		$data_ruolo = from_mysql_date($cit->Data_Iscrizione_Ruolo);
		$RGN = $cit->RGN;
		$data_fasc = from_mysql_date($cit->Data_Dep_Fascicolo);
		
		$avvo_attore = $cit->Avvocato_A;
		$avvo_convenuto = $cit->Avvocato_C;
		$giudice_atto = $cit->Giudice;
		
		$data_firma_1 = from_mysql_date($cit->Data_Sottoscriz_Atto_1);
		$data_firma_2 = from_mysql_date($cit->Data_Sottoscriz_Atto_2);
		$data_firma_3 = from_mysql_date($cit->Data_Sottoscriz_Atto_3);
		$data_firma_4 = from_mysql_date($cit->Data_Sottoscriz_Atto_4);
		
		$data_notifica_1 = from_mysql_date($cit->Data_Notifica_Atto_1);
		$data_notifica_2 = from_mysql_date($cit->Data_Notifica_Atto_2);
		$data_notifica_3 = from_mysql_date($cit->Data_Notifica_Atto_3);
		$data_notifica_4 = from_mysql_date($cit->Data_Notifica_Atto_4);
		
		$data_comparsa_1 = from_mysql_date($cit->Data_Sottoscriz_Comparsa_1);
		$data_comparsa_2 = from_mysql_date($cit->Data_Sottoscriz_Comparsa_2);
		$data_comparsa_3 = from_mysql_date($cit->Data_Sottoscriz_Comparsa_3);
		$data_comparsa_4 = from_mysql_date($cit->Data_Sottoscriz_Comparsa_4);
		
		$data_depos_1 = from_mysql_date($cit->Data_Dep_Comparsa_1);
		$data_depos_2 = from_mysql_date($cit->Data_Dep_Comparsa_2);
		$data_depos_3 = from_mysql_date($cit->Data_Dep_Comparsa_3);
		$data_depos_4 = from_mysql_date($cit->Data_Dep_Comparsa_4);
		
		$data_depos_attore_1 = from_mysql_date($cit->Data_Mem_Int_A);
		$data_depos_attore_2 = from_mysql_date($cit->Data_Replica_Mem_Int_A);
		$data_depos_attore_3 = from_mysql_date($cit->Data_Mem_Istr_A);
		$data_depos_attore_4 = from_mysql_date($cit->Data_Replica_Mem_Istr_A);
		$data_depos_attore_5 = from_mysql_date($cit->Data_Comparsa_Concl_A);
		$data_depos_attore_6 = from_mysql_date($cit->Data_Note_Replica_Concl_A);
		$data_depos_attore_7 = from_mysql_date($cit->Data_Istanza_A);
		$data_depos_attore_8 = from_mysql_date($cit->Data_Memorie_A);
		
		$data_depos_conv_1 = from_mysql_date($cit->Data_Mem_Int_C);
		$data_depos_conv_2 = from_mysql_date($cit->Data_Replica_Mem_Int_C);
		$data_depos_conv_3 = from_mysql_date($cit->Data_Mem_Istr_C);
		$data_depos_conv_4 = from_mysql_date($cit->Data_Replica_Mem_Istr_C);
		$data_depos_conv_5 = from_mysql_date($cit->Data_Comparsa_Concl_C);
		$data_depos_conv_6 = from_mysql_date($cit->Data_Note_Replica_Concl_C);
		$data_depos_conv_7 = from_mysql_date($cit->Data_Istanza_C);
		$data_depos_conv_8 = from_mysql_date($cit->Data_Memorie_C);
		
	}	
		
	}
	else 
	{
		
		$id_ricorso = 0;
		$id_atto_citazione = 0;
		$tipo = "";
		$data_reg = "";
		$data_chiusura = "";
		$ufficio_id = 0;
		$ufficio_sede = "";
		$ufficio_sez = "";
		
		$sospensiva = "";
		$esito_sosp = "";
		$num_sosp = "";
		$data_sosp = "";
		$data_dep_sosp = "";
		$data_not_sosp = "";
		
		$merito = "";
		$esito_merito = "";
		$num_merito = "";
		$data_merito = "";
		$data_dep_merito = "";
		$data_not_merito = "";
		
		$data_ric_sentenza = "";
		$data_imp_sentenza = "";
		$tot_da_pagare = "";
		$RG_pagato = "";
		$socc_pagata = "";

		$importo = "";
		$data_pag = "";
		$descr_pag = "";
		$note = "";
		
		$attore_id_1 = "";
		$attore_nome_1 = "";
		$attore_id_2 = "";
		$attore_nome_2 = "";
		$attore_id_3 = "";
		$attore_nome_3 = "";
		$attore_id_4 = "";
		$attore_nome_4 = "";
		$convenuto_id_1 = "";
		$convenuto_nome_1 = "";
		$convenuto_id_2 = "";
		$convenuto_nome_2 = "";
		$convenuto_id_3 = "";
		$convenuto_nome_3 = "";
		$convenuto_id_4 = "";
		$convenuto_nome_4 = "";
		
		$data_ruolo = "";
		$RGN = "";
		$data_fasc = "";
		
		$avvo_attore = "";
		$avvo_convenuto = "";
		$giudice_atto = "";
		
		$data_firma_1 = "";
		$data_firma_2 = "";
		$data_firma_3 = "";
		$data_firma_4 = "";
		
		$data_notifica_1 = "";
		$data_notifica_2 = "";
		$data_notifica_3 = "";
		$data_notifica_4 = "";
		
		$data_comparsa_1 = "";
		$data_comparsa_2 = "";
		$data_comparsa_3 = "";
		$data_comparsa_4 = "";
		
		$data_depos_1 = "";
		$data_depos_2 = "";
		$data_depos_3 = "";
		$data_depos_4 = "";
		
		$data_depos_attore_1 = "";
		$data_depos_attore_2 = "";
		$data_depos_attore_3 = "";
		$data_depos_attore_4 = "";
		$data_depos_attore_5 = "";
		$data_depos_attore_6 = "";
		$data_depos_attore_7 = "";
		$data_depos_attore_8 = "";
		
		$data_depos_conv_1 = "";
		$data_depos_conv_2 = "";
		$data_depos_conv_3 = "";
		$data_depos_conv_4 = "";
		$data_depos_conv_5 = "";
		$data_depos_conv_6 = "";
		$data_depos_conv_7 = "";
		$data_depos_conv_8 = "";
		
		$layout .="<script>$('#note').hide();</script>";
		$layout .="<script>$('#udienze').hide();</script>";
		
	}
	
	$layout.="<script>$('#ricorso').hide();$('#atto_citazione').hide();$('#".$tipo."').show();</script>";
	$layout.="<script>$('#tipo').val('".$tipo."')</script>";
	$layout.="<script>$('#sosp').val('".$sospensiva."');$('#merito').val('".$merito."');</script>";
	$layout.="<script>$('#esito_sospensiva').val('".$esito_sosp."');$('#esito_merito').val('".$esito_merito."');</script>";
	
	$layout.="<script>$('.conv1').show();$('.conv2').hide();$('.conv3').hide();$('.conv4').hide();</script>";
	$layout.="<script>$('.atto1').show();$('.atto2').hide();$('.atto3').hide();$('.atto4').hide();</script>";
	
	$layout.= "<script>$('#stato_attore_1').hide();$('#stato_attore_2').hide();";
	$layout.= "$('#stato_attore_3').hide();$('#stato_attore_4').hide();";
	$layout.= "$('#stato_attore_5').hide();$('#stato_attore_6').hide();";
	$layout.= "$('#stato_attore_7').hide();$('#stato_attore_8').hide();</script>";
	
	$layout.= "<script>$('#stato_conv_1').hide();$('#stato_conv_2').hide();";
	$layout.= "$('#stato_conv_3').hide();$('#stato_conv_4').hide();";
	$layout.= "$('#stato_conv_5').hide();$('#stato_conv_6').hide();";
	$layout.= "$('#stato_conv_7').hide();$('#stato_conv_8').hide();</script>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Partita - Ricorsi</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

	
<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{     
	control = submit_buttons('<?php echo $invia_submit; ?>');
	if(control)
    	$("#form_ricorso").submit();
}

//F4
function cancella_form() 
{     
	control = submit_buttons('Delete');
	if(control)
		$("#form_ricorso").submit();
}

//F5
function annulla()
{
	location.href="ricorso.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
function nuovo_F6()
{
	if( modifica == 0 )
	{
		crea_partita();
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F7-F8
function cambia_pag(value)
{
	if( modifica == 0 )
	{
		if(value=="prev" || value=="suc")
		{
			if(value=="suc")
				value = "<?php echo $next; ?>";
			else
				value = "<?php echo $prev; ?>";
				
			location.href="ricorso.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		
		}
	}
	else
		alert("salvare i dati o annullare prima di procedere");
	
}

//PAG GIU
function pag_prec()
{
	if( modifica == 0 )
	{
		location.href="scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
	
}

//PAG SU
function pag_suc()
{
	if( modifica == 0 )
	{
		location.href="coazione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
	
}

//F9
function ricerca_F9()
{
	if( modifica == 0 )
	{
		RicercheDaId('utente',0);
	}
	else
		alert("salvare i dati o annullare prima di procedere");
	
}

//F10
function stampa_F10()
{
	return true;
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function ruolo (value)
{
	location.href="gestione_ruolo.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function crea_partita()
{
	top.location.href = "gestione_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>

<script>
<!-- ********** CALENDARIO ********** -->

$(document).ready(function(){

	$('.picker').datepicker();

	});

</script>

<!-- ********** MODALI ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
	{
		setupPagina = "dialogWidth:" + sWidth + "px; ";
		setupPagina += "dialogHeight:" + sHeight + "px; ";
		setupPagina += "dialogLeft:80px; dialogTop:80px;";

		return setupPagina;
	}

function callParent(valorediritorno) {
    if(valorediritorno!=null){
        switch(selectParent){
            case "utente":

                if(typeof valorediritorno !== 'string')
                    reopen('obj',valorediritorno);
                else
                    reopen('str',valorediritorno);

                break;
        }
    }
}

function reopen(type, value){
    if(type == 'obj')
        top.location.href="../ricorso.php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
    else if(type == 'str')
        top.location.href="../gestione_ruolo.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

var selectParent = "";
var selectRif = "";

function RicercheDaId (value, rif)
{
    selectParent = value;
    selectRif = rif;
		var valorediritorno = 0;
		var strDim = Dim_Alert(600, 300);
		
		switch(value)
		{
			case "utente":
	
				strDim = Dim_Alert(800, 400);
				var stringa = "modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";		
				valorediritorno = window.showModalDialog(stringa,"", strDim);

				break;

		}
}

function ricerca_ufficio()
{
	richiesta = $('[name=giurisdizione]').val();
	
	strDim = Dim_Alert(600, 300);
	var stringa = "modali/ricerca_ufficio.php?richiesta=" + richiesta + "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	valorediritorno = window.showModalDialog(stringa,"", strDim);

	if( valorediritorno != null )
	{
		$('#comune_sede').val(valorediritorno.comune);
		$('#sezione_sede').val(valorediritorno.Sez);
		$('#id_ufficio').val(valorediritorno.ID);
	}
	
}

function sede_ufficio()
{

	ufficio = $('#id_ufficio').val();
	if(ufficio == "") ufficio = 0;
	
	strDim = Dim_Alert(700, 300);
	var stringa = "modali/sede_ufficio.php?&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_ufficio=" + ufficio;
	valorediritorno = window.showModalDialog(stringa,"", strDim);

	location.href = "ricorso.php?&c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>";
	
}

function dettagli_attore()
{
	num = $('[name=scelta_attore]:checked').val();

	info_id = $('#attore_id_' + num).val();

	strDim = Dim_Alert(700, 450);
	var stringa = "modali/info_utente.php?info_id="+info_id+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";	

	valorediritorno = window.showModalDialog(stringa,"", strDim);
}

function dettagli_convenuto()
{
	num = $('[name=notif_conv]:checked').val();

	info_id = $('#convenuto_id_' + num).val();

	strDim = Dim_Alert(700, 450);
	var stringa = "modali/info_utente.php?info_id="+info_id+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

	valorediritorno = window.showModalDialog(stringa,"", strDim);
}

function noteClick()
{
	ricorso_id = $('#id_ricorso').val();
	
	strDim = Dim_Alert(600, 400);
	var stringa = "modali/note_ricorso.php?ricorso_id="+ricorso_id+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";	

	valorediritorno = window.showModalDialog(stringa,"", strDim);

	top.location.href = "ricorso.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	
	
}

function udienzeClick()
{
	ricorso_id = $('#id_ricorso').val();
	
	strDim = Dim_Alert(700, 400);
	var stringa = "modali/iter_udienze.php?ricorso_id="+ricorso_id+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";	

	valorediritorno = window.showModalDialog(stringa,"", strDim);

	top.location.href = "ricorso.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	
}

</script>

<!-- ********** AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>

function radioClick(value)
{
	switch(value)
	{
		case 1:

			$('.conv1').show();
			$('.conv2').hide();
			$('.conv3').hide();
			$('.conv4').hide();

		break;

		case 2:

			$('.conv1').hide();
			$('.conv2').show();
			$('.conv3').hide();
			$('.conv4').hide();

		break;

		case 3:

			$('.conv1').hide();
			$('.conv2').hide();
			$('.conv3').show();
			$('.conv4').hide();

		break;

		case 4:

			$('.conv1').hide();
			$('.conv2').hide();
			$('.conv3').hide();
			$('.conv4').show();

		break;

	}
}

function radioAttore(value)
{
	switch(value)
	{
		case 1:

			$('.atto1').show();
			$('.atto2').hide();
			$('.atto3').hide();
			$('.atto4').hide();

		break;

		case 2:

			$('.atto1').hide();
			$('.atto2').show();
			$('.atto3').hide();
			$('.atto4').hide();

		break;

		case 3:

			$('.atto1').hide();
			$('.atto2').hide();
			$('.atto3').show();
			$('.atto4').hide();

		break;

		case 4:

			$('.atto1').hide();
			$('.atto2').hide();
			$('.atto3').hide();
			$('.atto4').show();

		break;

	}
}

function selectClick ( tipo )
{
	if( tipo == "attore" )
	{
		value = $('[name=stato_attore]').val();

		$('#stato_attore_1').hide();
		$('#stato_attore_2').hide();
		$('#stato_attore_3').hide();
		$('#stato_attore_4').hide();
		$('#stato_attore_5').hide();
		$('#stato_attore_6').hide();
		$('#stato_attore_7').hide();
		$('#stato_attore_8').hide();
		
		switch(value)
		{
			case "int":				$('#stato_attore_1').show();	break;
			case "replica_int":		$('#stato_attore_2').show();	break;
			case "istr":			$('#stato_attore_3').show();	break;
			case "replica_istr":	$('#stato_attore_4').show();	break;
			case "comp":			$('#stato_attore_5').show();	break;
			case "note_replica":	$('#stato_attore_6').show();	break;
			case "istanza":			$('#stato_attore_7').show();	break;
			case "mem":				$('#stato_attore_8').show();	break;
		}
	}
	else if( tipo == "convenuto" )
	{		
		value = $('[name=stato_convenuto]').val();

		$('#stato_conv_1').hide();
		$('#stato_conv_2').hide();
		$('#stato_conv_3').hide();
		$('#stato_conv_4').hide();
		$('#stato_conv_5').hide();
		$('#stato_conv_6').hide();
		$('#stato_conv_7').hide();
		$('#stato_conv_8').hide();
		
		switch(value)
		{
			case "int":				$('#stato_conv_1').show();		break;
			case "replica_int":		$('#stato_conv_2').show();		break;
			case "istr":			$('#stato_conv_3').show();		break;
			case "replica_istr":	$('#stato_conv_4').show();		break;
			case "comp":			$('#stato_conv_5').show();		break;
			case "note_replica":	$('#stato_conv_6').show();		break;
			case "istanza":			$('#stato_conv_7').show();		break;
			case "mem":				$('#stato_conv_8').show();		break;	
		}
	}

	
}

function scelta_ricorso()
{
	value = $('[name=tipo]').val();

	if(value=="atto_citazione")
	{
		$('#ricorso').hide();
		$('#atto_citazione').show();
	}
	else if(value == 'ricorso')
	{
		$('#atto_citazione').hide();
		$('#ricorso').show();
	}	
}



function cambio_giuris()
{
	$('#comune_sede').val('');
	$('#sezione_sede').val('');
	$('#id_ufficio').val(0);
}

</script>


<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){
	
$('#cerca_id').ajaxForm(
			
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
	});

$('#form_ricorso').ajaxForm(
		
    function(value) {
        var array_ritorno = value.split(' ');

	if(array_ritorno[0]=='OK')
	{		
		alert('Salvataggio effettuato correttamente! partita '+array_ritorno[1]+' ricorso '+array_ritorno[2]);
		top.location.href = "ricorso.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	
});

$("#submit_click").click( salva_form );

$("#delete_click").click( cancella_form );

});

function carica_utente( num, tipo)
{
	strDim = Dim_Alert(600, 300);
	var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";	

	valorediritorno = window.showModalDialog(stringa,"", strDim);
	
if( valorediritorno != null && valorediritorno != undefined && valorediritorno != "")
{

	$.ajax({  
		  type: "POST",  
		  async: false,
		  url: "ajax/ajax_partita.php?c=<?php echo $c; ?>",  
		  data: {	
			  		ajax: "nome",
			  		ID: valorediritorno,
				}, 
				
		  success: function(value) {

		  		nome = value;
		  }
	});

	
	if(tipo == "attore")
	{
		switch(num)
		{
			case 1:

				$('#attore_1').val(nome);
				$('#attore_id_1').val(valorediritorno);

				break;

			case 2:

				$('#attore_2').val(nome);
				$('#attore_id_2').val(valorediritorno);

				break;
				
			case 3:

				$('#attore_3').val(nome);
				$('#attore_id_3').val(valorediritorno);

				break;
				
			case 4:

				$('#attore_4').val(nome);
				$('#attore_id_4').val(valorediritorno);

				break;
		}

	}
	else if(tipo == "convenuto")
	{
		switch(num)
		{
			case 1:
	
				$('#convenuto_1').val(nome);
				$('#convenuto_id_1').val(valorediritorno);
	
				break;
	
			case 2:
	
				$('#convenuto_2').val(nome);
				$('#convenuto_id_2').val(valorediritorno);
	
				break;
				
			case 3:
	
				$('#convenuto_3').val(nome);
				$('#convenuto_id_3').val(valorediritorno);
	
				break;
				
			case 4:
	
				$('#convenuto_4').val(nome);
				$('#convenuto_id_4').val(valorediritorno);
	
				break;
		}		
	}

}
else
{
	alert("Errore nel caricamento dell'utente! \n\nPer inserire un nuovo utente utilizzare l'Anagrafe\n ");
}
	
}

</script>

</head>

<body class="sfondo_new_gitco" >

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left">
			<font class="comune" ><?php echo $nome_comune; ?> <?php echo $options_anni; ?></font>
		</td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>

<?php include MENU . '/menu_generale.php'; ?>
                
<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="nuovo_F6();" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovo.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover="title='Record precedente F7'" onclick="cambia_pag('prev')">
          	<img src="/gitco2/immagini/FrecciaS.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
    	</td>
        <td width=7% align="center">
            <a href="#" onMouseover="title='Record successivo F8'" onclick="cambia_pag('suc')">
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
</table>
<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=8% class="text_center">
			<a onMouseover="title='Cerca utente/partita'" href="#" onClick="RicercheDaId('utente',0);" style="text-decoration: none;">
			<img src="/gitco2/immagini/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=15% class="text_center"><font class="titolo font18">PARTITA</font><font class="titolo font14"><br> Pag 4/7</font></td>
    	<td colspan=5 width=55% align=center>
            <em style="background-color:rgb(251,255,208);font-style : normal ;">
            <?php if($genere_utente!='D'){echo $cognome_utente." ".$nome_utente;}else{ echo $ditta; } ?> 
            </em>
        	<td class="text_left"><input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" title="Gestione Ruolo" onclick="ruolo('<?php echo $utente_ID; ?>');">
        </td>
		<td width=22% class="text_right">
		<form id=cerca_id method=post action=modali/ricerca_partita.php>
			<input type=hidden name=old_cod_contr value='<?php echo $ID_Partita; ?>'>
           	<input name=c type=hidden value='<?php echo $c; ?>'>
            <input name=a type=hidden value='<?php echo $a; ?>'>
		
			Partita ID &nbsp;
		
			<input id=id_cerca tabindex=1 class="valign_center text_right" type=text name=ric_cod_contr value='<?php echo $ID_Partita; ?>' size=3 onMouseover="title='Inserire il codice utente e premere Invio'">&nbsp;&nbsp;</form>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td class="width20"><a href="gestione_partita.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Codici tributo</font></a></td>
		<td class="width20"><a href="ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Ingiunzione</font></a></td>
		<td class="width20"><a href="pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Pagamenti</font></a></td>
		<td class="width20"><a href="scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Scorpori</font></a></td>
		<td class="width20"><font class="titoletto font16 under_decor">Ricorsi</font></td>
		<td class="width20"><a href="coazione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" style="text-decoration: none;"><font class="titolo font15"><i>Coazione</i></font> <img alt="" src="/gitco2/immagini/forward.png" style="width:12px; height:12px; border:0;"></a></td>
	</tr>
</table>
<br>

<form 	id=form_ricorso 	name=form_ricorso 	action="ricorso_salva.php" method=post			>
<input 	type=hidden 		name=c 				value=<?php echo $c; ?> 						>
<input 	type=hidden 		name=a 				value=<?php echo $a; ?> 						>
<input 	type=hidden 		name=p 				value=<?php echo $p; ?> 						>
<input 	type=hidden 		name=partita 		value=<?php echo $partita_ID; ?> 				>
<input 	id=id_ricorso 		name=id_ricorso 	value="<?php echo $id_ricorso; ?>" 	type=hidden	>
<input 	id=invia_submit		name=invia_submit  	value=""  							type=hidden	>
<input 	id=id_ufficio 		name=id_ufficio 	value="<?php echo $ufficio_id; ?>" 	type=hidden	>
<input 	id=attore_id_1		name=attore_id_1	value="<?php echo $attore_id_1; ?>"	type=hidden	>
<input 	id=attore_id_2		name=attore_id_2	value="<?php echo $attore_id_2; ?>"	type=hidden	>
<input 	id=attore_id_3		name=attore_id_3	value="<?php echo $attore_id_3; ?>"	type=hidden	>
<input 	id=attore_id_4		name=attore_id_4	value="<?php echo $attore_id_4; ?>"	type=hidden	>
<input 	id=convenuto_id_1	name=convenuto_id_1	value="<?php echo $convenuto_id_1; ?>"	type=hidden	>
<input 	id=convenuto_id_2	name=convenuto_id_2	value="<?php echo $convenuto_id_2; ?>"	type=hidden	>
<input 	id=convenuto_id_3	name=convenuto_id_3	value="<?php echo $convenuto_id_3; ?>"	type=hidden	>
<input 	id=convenuto_id_4	name=convenuto_id_4	value="<?php echo $convenuto_id_4; ?>"	type=hidden	>


<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width15" ><b>Giurisdizione:</b></td>
		<td class="text_left width20" >
			<select name=giurisdizione onchange="cambio_giuris();">
				<option value=giudice >Giudice di Pace</option>
				<option value=tribunale >Tribunale</option>	
			</select>
		</td>
		<td class="text_left width11" >
			<input type=button name=ricerca class="sfondo_azzurro" value=Ricerca onclick="ricerca_ufficio();">
		</td>
		<td class="text_center width9" >Sede di</td>
		<td class="text_left width45" colspan=2>
			&nbsp;<input type=text readonly id=comune_sede name=comune_sede value="<?php echo $ufficio_sede; ?>" size=8>&nbsp;&nbsp;&nbsp;
			Sezione &nbsp;&nbsp;<input type=text readonly id=sezione_sede name=sezione_sede value="<?php echo $ufficio_sez; ?>" size=2>&nbsp;&nbsp;&nbsp;
			<input type=button class="sfondo_azzurro" name=dettagli_sede value=Gestione onclick="sede_ufficio();">
		</td>
	</tr>
	<tr>
		<td class="text_left width15" ><b>Tipo di atto:</b></td>
		<td class="text_left width20" >
			<select id=tipo name=tipo onchange="scelta_ricorso();">
				<option value=atto_citazione >Atto di citazione</option>
				<option value=ricorso >Ricorso</option>
			</select>
		</td>
		<td class="text_left width35" colspan=3>
			Data Registrazione &nbsp;<input type=text class="text_center" readonly name=data_reg value="<?php echo from_mysql_date($data_reg); ?>" size=10>			
		</td>			
		<td class="text_right width30" >
			Data Chiusura &nbsp;<input type=text class="text_center" readonly name=data_chiusura value="<?php echo from_mysql_date($data_chiusura); ?>" size=10>
		</td>
	</tr>
	<tr>
		<td colspan=6><hr></td>
	</tr>
</table>

<table id=atto_citazione class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width20" colspan=2>
			<input type=button class="sfondo_azzurro pwidth100" name=attore 	value="Attore"	onclick="dettagli_attore();">
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=attore_1 name=attore_1 value="<?php echo $attore_nome_1; ?>" size=13 ondblclick="carica_utente(1,'attore');">
			<input type=radio name=scelta_attore value=1 onclick="radioAttore(1);" checked>
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=attore_2 name=attore_2 value="<?php echo $attore_nome_2; ?>" size=13 ondblclick="carica_utente(2,'attore');">
			<input type=radio name=scelta_attore value=2 onclick="radioAttore(2);">
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=attore_3 name=attore_3 value="<?php echo $attore_nome_3; ?>" size=13 ondblclick="carica_utente(3,'attore');">
			<input type=radio name=scelta_attore value=3 onclick="radioAttore(3);">
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=attore_4 name=attore_4 value="<?php echo $attore_nome_4; ?>" size=13 ondblclick="carica_utente(4,'attore');">
			<input type=radio name=scelta_attore value=4 onclick="radioAttore(4);">
		</td>			
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Iscritto a ruolo il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_ruolo value="<?php echo $data_ruolo; ?>" size=9>
		</td>
		<td class="text_left width20" colspan=2>R.G.N. <input type=text name=RGN value="<?php echo $RGN; ?>" size=8></td>
		<td class="text_left width40" colspan=4>Fascicolo depositato il &nbsp;&nbsp;
			<input type=text class="text_center picker" name=data_fasc value="<?php echo $data_fasc; ?>" size=9>
		</td>				
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Avvocato</td>
		<td class="text_left width20" colspan=2><input type=text name=avvo_attore value="<?php echo $avvo_attore; ?>" size=15></td>
		<td class="text_center width20" colspan=2>&nbsp;Giudice</td>
		<td class="text_left width20" colspan=2><input type=text name=giudice_atto value="<?php echo $giudice_atto; ?>" size=15></td>
		<td class="text_left width20" colspan=2></td>				
	</tr>
	<tr class=atto1>
		<td class="text_left width20" colspan=2>Atto sottoscritto il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_firma_1 value="<?php echo $data_firma_1; ?>" size=9>
		</td>
		<td class="text_center width20" colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Notificato il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_notifica_1 value="<?php echo $data_notifica_1; ?>" size=9>
		</td>
		<td class="text_left width20" colspan=2></td>
	</tr>
	<tr class=atto2>
		<td class="text_left width20" colspan=2>Atto sottoscritto il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_firma_2 value="<?php echo $data_firma_2; ?>" size=9>
		</td>
		<td class="text_center width20" colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Notificato il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_notifica_2 value="<?php echo $data_notifica_2; ?>" size=9>
		</td>
		<td class="text_left width20" colspan=2></td>
	</tr>
	<tr class=atto3 >
		<td class="text_left width20" colspan=2>Atto sottoscritto il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_firma_3 value="<?php echo $data_firma_3; ?>" size=9>
		</td>
		<td class="text_center width20" colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Notificato il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_notifica_3 value="<?php echo $data_notifica_3; ?>" size=9>
		</td>
		<td class="text_left width20" colspan=2></td>
	</tr>
	<tr class=atto4 >
		<td class="text_left width20" colspan=2>Atto sottoscritto il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_firma_4 value="<?php echo $data_firma_4; ?>" size=9>
		</td>
		<td class="text_center width20" colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Notificato il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_notifica_4 value="<?php echo $data_notifica_4; ?>" size=9>
		</td>
		<td class="text_left width20" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Stato atti processo</td>
		<td class="text_left width40" colspan=4>
			<select name=stato_attore onchange="selectClick('attore');">
				<option></option>
				<option value=int 			>Memoria Integrativa</option>
				<option value=replica_int 	>Replica Mem. Integrativa</option>
				<option value=istr 			>Memoria Istruttoria</option>
				<option value=replica_istr 	>Replica Mem. Istruttoria</option>
				<option value=comp 			>Comparsa conclus.</option>
				<option value=note_replica 	>Note di replica conclus.</option>
				<option value=istanza 		>Istanza</option>
				<option value=mem 			>Memorie</option>
			</select>
		</td>
		<td class="text_left width20" colspan=2>Data deposito
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" id=stato_attore_1 name=data_depos_attore_1 value="<?php echo $data_depos_attore_1; ?>" size=9>

			<input type=text class="text_center picker" id=stato_attore_2 name=data_depos_attore_2 value="<?php echo $data_depos_attore_2; ?>" size=9>

			<input type=text class="text_center picker" id=stato_attore_3 name=data_depos_attore_3 value="<?php echo $data_depos_attore_3; ?>" size=9>

			<input type=text class="text_center picker" id=stato_attore_4 name=data_depos_attore_4 value="<?php echo $data_depos_attore_4; ?>" size=9>

			<input type=text class="text_center picker" id=stato_attore_5 name=data_depos_attore_5 value="<?php echo $data_depos_attore_5; ?>" size=9>

			<input type=text class="text_center picker" id=stato_attore_6 name=data_depos_attore_6 value="<?php echo $data_depos_attore_6; ?>" size=9>

			<input type=text class="text_center picker" id=stato_attore_7 name=data_depos_attore_7 value="<?php echo $data_depos_attore_7; ?>" size=9>

			<input type=text class="text_center picker" id=stato_attore_8 name=data_depos_attore_8 value="<?php echo $data_depos_attore_8; ?>" size=9>
		</td>				
	</tr>	
	<tr>
		<td class="text_left width20" colspan=2>
			<input type=button class="sfondo_azzurro pwidth100" name=convenuto 	value="Convenuto"	onclick="dettagli_convenuto();"></td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=convenuto_1 name=convenuto_1 value="<?php echo $convenuto_nome_1; ?>" size=13 ondblclick="carica_utente(1,'convenuto');">
			<input type=radio name=notif_conv value=1 onclick="radioClick(1);" checked >
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=convenuto_2 name=convenuto_2 value="<?php echo $convenuto_nome_2; ?>" size=13 ondblclick="carica_utente(2,'convenuto');">
			<input type=radio name=notif_conv value=2 onclick="radioClick(2);" >
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=convenuto_3 name=convenuto_3 value="<?php echo $convenuto_nome_3; ?>" size=13 ondblclick="carica_utente(3,'convenuto');">
			<input type=radio name=notif_conv value=3 onclick="radioClick(3);" >
		</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="sfondo_azzurro" readonly id=convenuto_4 name=convenuto_4 value="<?php echo $convenuto_nome_4; ?>" size=13 ondblclick="carica_utente(4,'convenuto');">
			<input type=radio name=notif_conv value=4 onclick="radioClick(4);" >
		</td>			
	</tr>
	<tr class=conv1>
		<td class="text_left width40" colspan=4>Comparsa di Costituzione sottoscritta il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_comparsa_1 	value="<?php echo $data_comparsa_1; ?>" size=9>
		</td>
		<td class="text_left width40" colspan=4>Comparsa depositata il &nbsp;
			<input type=text class="text_center picker" name=data_depos_1 		value="<?php echo $data_depos_1; ?>" size=9>
		</td>
	</tr>
	<tr class=conv2>
		<td class="text_left width40" colspan=4>Comparsa di Costituzione sottoscritta il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_comparsa_2 	value="<?php echo $data_comparsa_2; ?>" size=9>
		</td>
		<td class="text_left width40" colspan=4>Comparsa depositata il &nbsp;
			<input type=text class="text_center picker" name=data_depos_2 		value="<?php echo $data_depos_2; ?>" size=9>
		</td>
	</tr>
	<tr class=conv3>
		<td class="text_left width40" colspan=4>Comparsa di Costituzione sottoscritta il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_comparsa_3 	value="<?php echo $data_comparsa_3; ?>" size=9>
		</td>
		<td class="text_left width40" colspan=4>Comparsa depositata il &nbsp;
			<input type=text class="text_center picker" name=data_depos_3 		value="<?php echo $data_depos_3; ?>" size=9>
		</td>
	</tr>
	<tr class=conv4>
		<td class="text_left width40" colspan=4>Comparsa di Costituzione sottoscritta il</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" name=data_comparsa_4 	value="<?php echo $data_comparsa_4; ?>" size=9>
		</td>
		<td class="text_left width40" colspan=4>Comparsa depositata il &nbsp;
			<input type=text class="text_center picker" name=data_depos_4 		value="<?php echo $data_depos_4; ?>" size=9>
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Avvocato</td>
		<td class="text_left width20" colspan=2><input type=text name=avvo_convenuto value="<?php echo $avvo_convenuto; ?>" size=15></td>
		<td class="text_left width60" colspan=6>
			<input type=button class="<?php echo $class_note; ?>" name=note id=note value="Note" onclick="noteClick();">
			<input type=button class="<?php echo $class_udienza; ?>" name=udienze id=udienze value="Iter Udienze" onclick="udienzeClick();">
		</td>				
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Stato atti processo</td>
		<td class="text_left width40" colspan=4>
			<select name=stato_convenuto onchange="selectClick('convenuto');">
				<option></option>
				<option value=int 			>Memoria Integrativa</option>
				<option value=replica_int 	>Replica Mem. Integrativa</option>
				<option value=istr 			>Memoria Istruttoria</option>
				<option value=replica_istr 	>Replica Mem. Istruttoria</option>
				<option value=comp 			>Comparsa conclus.</option>
				<option value=note_replica 	>Note di replica conclus.</option>
				<option value=istanza 		>Istanza</option>
				<option value=mem 			>Memorie</option>
			</select>
		</td>
		<td class="text_left width20" colspan=2>Data deposito</td>
		<td class="text_left width20" colspan=2>
			<input type=text class="text_center picker" id=stato_conv_1 name=data_depos_conv_1 value="<?php echo $data_depos_conv_1; ?>" size=9>

			<input type=text class="text_center picker" id=stato_conv_2 name=data_depos_conv_2 value="<?php echo $data_depos_conv_2; ?>" size=9>

			<input type=text class="text_center picker" id=stato_conv_3 name=data_depos_conv_3 value="<?php echo $data_depos_conv_3; ?>" size=9>

			<input type=text class="text_center picker" id=stato_conv_4 name=data_depos_conv_4 value="<?php echo $data_depos_conv_4; ?>" size=9>

			<input type=text class="text_center picker" id=stato_conv_5 name=data_depos_conv_5 value="<?php echo $data_depos_conv_5; ?>" size=9>

			<input type=text class="text_center picker" id=stato_conv_6 name=data_depos_conv_6 value="<?php echo $data_depos_conv_6; ?>" size=9>

			<input type=text class="text_center picker" id=stato_conv_7 name=data_depos_conv_7 value="<?php echo $data_depos_conv_7; ?>" size=9>

			<input type=text class="text_center picker" id=stato_conv_8 name=data_depos_conv_8 value="<?php echo $data_depos_conv_8; ?>" size=9>
		</td>		
	</tr>
	<tr>
		<td colspan=10><hr></td>
	</tr>
</table>

<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width20" colspan=2><b>Sospensiva</b></td>
		<td class="text_left width20" colspan=2>
			<select id=sosp name=sospensiva style="width: 120px;">
				<option></option>
				<option value=sentenza		>Sentenza</option>
				<option value=ordinanza 	>Ordinanza</option>
				<option value=decreto 		>Decreto</option>
				<option value=comunicazioni >Comunicazioni</option>
			</select>
		</td>
		<td class="text_left width40" colspan=4>
			n° <input name=num_sospensiva size=3 value="<?php echo $num_sosp; ?>" >&nbsp;
			del &nbsp;<input name=data_sospensiva class="text_center picker" size=9 value="<?php echo $data_sosp; ?>">&nbsp;
			depositata il 
		</td>
		<td class="text_left width20" colspan=2>
			<input name=data_dep_sospensiva class="text_center picker" size=9 value="<?php echo $data_dep_sosp; ?>">
		</td>				
	</tr>
	<tr>
		<td class="text_left" colspan=2>Esito</td>
		<td class="text_left" colspan=2>
			<select id=esito_sospensiva name=esito_sospensiva style="width: 120px;">
				<option></option>
				<option value=accolto		>Accolto</option>
				<option value=respinto 		>Respinto</option>
			</select>
		</td>
		<td class="text_left" colspan=4>
			notificato il &nbsp;
			<input name=data_not_sospensiva class="text_center picker" size=9 value="<?php echo $data_not_sosp; ?>" >
		</td>
		<td class="text_left" colspan=2>
			
		</td>				
	</tr>
	<tr>
		<td class="text_left" colspan=2><b>Merito</b></td>
		<td class="text_left" colspan=2>
			<select id=merito name=merito style="width: 120px;" >
				<option></option>
				<option value=sentenza		>Sentenza</option>
				<option value=ordinanza 	>Ordinanza</option>
				<option value=ingiunzione 	>Ord. Ingiunzione</option>
				<option value=decreto 		>Decreto</option>
				<option value=comunicazioni >Comunicazioni</option>
			</select>
		</td>
		<td class="text_left" colspan=4>
			n° <input name=num_merito size=3 value="<?php echo $num_merito; ?>" >&nbsp;
			del &nbsp;<input name=data_merito class="text_center picker" size=9 value="<?php echo $data_merito; ?>">&nbsp;
			depositato il 
		</td>
		<td class="text_left" colspan=2>
			<input name=data_dep_merito class="text_center picker" size=9 value="<?php echo $data_dep_merito; ?>">
		</td>				
	</tr>
	<tr>
		<td class="text_left" colspan=2>Esito</td>
		<td class="text_left" colspan=2>
			<select id=esito_merito name=esito_merito style="width: 120px;">
				<option></option>
				<option value=accolto 		>Accolto</option>
				<option value=accolto_parte >Accolto in parte</option>
				<option value=respinto 		>Respinto</option>
				<option value=inamissibile 	>Inamissibile</option>
				<option value=improcedibile >Improcedibile</option>
				<option value=min_editt 	>Minimo Edittale</option>
			</select>
		</td>
		<td class="text_left" colspan=4>
			notificato il &nbsp;
			<input name=data_not_merito class="text_center picker" size=9 value="<?php echo $data_not_merito; ?>">
		</td>
		<td class="text_left" colspan=2>
			
		</td>
	</tr>
	<tr>
		<td class="text_left width55" colspan=6><b>Sentenza:</b>
			&nbsp;richiesta il
			<input name=data_ric_sentenza class="text_center picker" size=7 value="<?php echo $data_ric_sentenza; ?>" >
			&nbsp;impugnata il
			<input name=data_app_sentenza class="text_center picker" size=7 value="<?php echo $data_imp_sentenza; ?>" >
		</td>
		<td class="text_left width45" colspan=4>
			Totale da pagare (spese incluse)&nbsp;
			<input id=tot_da_pagare name=tot_da_pagare class="text_right corrige_numero" size=7 value="<?php echo $tot_da_pagare; ?>"> &euro;
		</td>				
	</tr>
	<tr>
		<td class="text_left" colspan=6>
			RG pagato <input type="checkbox" id=RG_pagato name=RG_pagato value=Y>
			Soccombenza pagata <input type="checkbox" id=socc_pagata name=socc_pagata value=Y>
			Importo <input name=importo class="text_right" size=5 value="<?php echo $importo; ?>"> &euro;
		</td>
		<td class="text_left" colspan=4>
			Pagato il <input name=data_pag class="text_center picker" size=7 value="<?php echo $data_pag; ?>" >
			&nbsp;Descrizione <input name=descr_pag class="text_left" size=7 value="<?php echo $descr_pag; ?>" >
		</td>				
	</tr>
	<tr>
		<td colspan=10><hr></td>
	</tr>	
</table>

</form>

</td>
</tr>
</table>

<?php echo $layout; ?>

</body>
</html>