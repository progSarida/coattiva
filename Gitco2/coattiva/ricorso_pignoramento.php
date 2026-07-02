<?php

    if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");

	include(INC."/header.php");
	include(INC."/menu.php");
	//include(INC."/submenu_partita.php");
	$submenuPageNo = 8;
    $pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';
	include(INC."/submenu_partita.php");

	include(CLS."/cls_registry.php");
	include_once(CLS."/cls_CoazioneUtils.php");
	include_once(CLS."/cls_DateTimeInLine.php");
	include_once(CLS."/cls_math.php");

	$cls_coaz = new cls_Coazione();
	$cls_date = new cls_DateTimeI("IT",false);
	$cls_math = new cls_math();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');

	$partita_ID = $cls_help->getVar('partita');

	$layout = "<script>";

	//$anni_gestiti = new anni_gestiti($c, null);

	if($c==null)
		$options_anni = null;
	else
	{
		$options_anni = $cls_coaz->Options_Anni_Veloci($c, "COATTIVA", "ricorso");

		if($a!=null)
			$layout.="$('#select_anno_veloce option[value=".$a."]').attr('selected',true);";
	}

	$layout.= "</script>";

	$partita = $cls_coaz->GetDataPartita($partita_ID, $c, $a);// new partita($partita_ID, $c, $a);

	$ID_Partita = $partita["Comune_ID"];

	$anno_riferimento = $partita["Anno_Riferimento"];

	$utente_ID = $partita["Utente_ID"];

	$query = "SELECT * FROM utente WHERE ID = '".$utente_ID."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
	$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
	//$utente = new utente($utente_ID,$c);

	$id_utente 				= 	$utente["ID"];
	$genere_utente 			= 	$utente["Genere"];
	$comune_id 				=	$utente["Comune_ID"];
	$cognome_utente 		=	$utente["Cognome"];
	$nome_utente 			=	$utente["Nome"];
	$ditta					=	$utente["Ditta"];

	$tipo = $partita["Tipo"];
	$atto = $partita["Atto"];

	$prev = $partita["prev"];
	$next = $partita["next"];

	$count_ric = count($partita["Ricorso"]);
	if($count_ric==0)	$invia_submit = "Insert";
	else				$invia_submit = "Update";


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


	if($count_ric>0)
	{

	$ric = $partita["Ricorso"][0];

	$id_ricorso = $ric["ID"];
	$tipo = $ric["Tipo_Ricorso"];
	$data_reg = $ric["Data_Registrazione"];
	$data_chiusura = $ric["Data_Chiusura"];
	$ufficio_id = $ric["Ufficio"]["ID"];
	$ufficio_sede = $ric["Ufficio"]["Comune"];
	$ufficio_sez = $ric["Ufficio"]["Sezione"];

	$sospensiva = $ric["Sospensiva"];
	$esito_sosp = $ric["Esito_Sosp"];
	$num_sosp = $ric["Num_Sosp"];
	$data_sosp = $cls_date->Get_DateNewFormat($ric["Data_Sosp"],"DB");
	$data_dep_sosp = $cls_date->Get_DateNewFormat($ric["Data_Dep_Sosp"],"DB");
	$data_not_sosp = $cls_date->Get_DateNewFormat($ric["Data_Not_Esito_Sosp"],"DB");

	$merito = $ric["Merito"];
	$esito_merito = $ric["Esito_Merito"];
	$num_merito = $ric["Num_Merito"];
	$data_merito = $cls_date->Get_DateNewFormat($ric["Data_Merito"],"DB");
	$data_dep_merito = $cls_date->Get_DateNewFormat($ric["Data_Dep_Merito"],"DB");
	$data_not_merito = $cls_date->Get_DateNewFormat($ric["Data_Not_Esito_Merito"],"DB");

	$data_ric_sentenza = $cls_date->Get_DateNewFormat($ric["Data_Richiesta_Sentenza"],"DB");
	$data_imp_sentenza = $cls_date->Get_DateNewFormat($ric["Data_Impugnazione_Sentenza"],"DB");
	$tot_da_pagare = $cls_math->conv_num($ric["Totale_Da_Pagare"]);
	if($tot_da_pagare == "0,00")
		$tot_da_pagare = "";

	$RG_pagato = $ric["RG_Pagato"];
	if($RG_pagato == 'Y') $layout.="<script>$('#RG_pagato').prop('checked',true);</script>";

	$socc_pagata = $ric["Soccombenza_Pagata"];
	if($socc_pagata == 'Y') $layout.="<script>$('#socc_pagata').prop('checked',true);</script>";

	$importo = $cls_math->conv_num($ric["Importo"]);
	if($importo == "0,00")
		$importo = "";
	$data_pag = $cls_date->Get_DateNewFormat($ric["Data_Pagamento"],"DB");
	$descr_pag = $ric["Descrizione_Pagamento"];
	$note = $ric["Note"];
	if($note == "")
		$class_note = "sfondo_azzurro";
	else
		$class_note = "sfondo_red";

	$udienza = $ric["Udienze"];
	if(count($udienza)>0 && $udienza != "")
		$class_udienza = "sfondo_red";
	else
		$class_udienza = "sfondo_azzurro";


	$count_cit = count($ric["Atto_Citazione"]);
	if($count_cit == 1 && $tipo == "atto_citazione")
	{

		$cit = $ric["Atto_Citazione"];

		$attore_id_1 = $cit["Attore_1_ID"];
		if($attore_id_1 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_1."'");
			$attore_nome_1 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else
		{
			$attore_nome_1 = "";
		}

		$attore_id_2 = $cit["Attore_2_ID"];
		if($attore_id_2 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_2."'");
			$attore_nome_2 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else
		{
			$attore_nome_2 = "";
		}

		$attore_id_3 = $cit["Attore_3_ID"];
		if($attore_id_3 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_3."'");
			$attore_nome_3 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else
		{
			$attore_nome_3 = "";
		}

		$attore_id_4 = $cit["Attore_4_ID"];
		if($attore_id_4 != 0)
		{
			$attore_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$attore_id_4."'");
			$attore_nome_4 = $attore_array[0]['Cognome']." ".$attore_array[0]['Nome'];
		}
		else
		{
			$attore_nome_4 = "";
		}

		$convenuto_id_1 = $cit["Convenuto_1_ID"];
		if($convenuto_id_1 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_1."'");
			$convenuto_nome_1 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_1 = "";
		}

		$convenuto_id_2 = $cit["Convenuto_2_ID"];
		if($convenuto_id_2 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_2."'");
			$convenuto_nome_2 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_2 = "";
		}

		$convenuto_id_3 = $cit["Convenuto_3_ID"];
		if($convenuto_id_3 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_3."'");
			$convenuto_nome_3 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_3 = "";
		}

		$convenuto_id_4 = $cit["Convenuto_4_ID"];
		if($convenuto_id_4 != 0)
		{
			$convenuto_array = select_mysql_array("Nome , Cognome", "utente", "ID = '".$convenuto_id_4."'");
			$convenuto_nome_4 = $convenuto_array[0]['Cognome']." ".$convenuto_array[0]['Nome'];
		}
		else
		{
			$convenuto_nome_4 = "";
		}

		$data_ruolo = $cls_date->Get_DateNewFormat($cit["Data_Iscrizione_Ruolo"],"DB");
		$RGN = $cit["RGN"];
		$data_fasc = $cls_date->Get_DateNewFormat($cit["Data_Dep_Fascicolo"],"DB");

		$avvo_attore = $cit["Avvocato_A"];
		$avvo_convenuto = $cit["Avvocato_C"];
		$giudice_atto = $cit["Giudice"];

		$data_firma_1 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Atto_1"],"DB");
		$data_firma_2 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Atto_2"],"DB");
		$data_firma_3 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Atto_3"],"DB");
		$data_firma_4 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Atto_4"],"DB");

		$data_notifica_1 = $cls_date->Get_DateNewFormat($cit["Data_Notifica_Atto_1"],"DB");
		$data_notifica_2 = $cls_date->Get_DateNewFormat($cit["Data_Notifica_Atto_2"],"DB");
		$data_notifica_3 = $cls_date->Get_DateNewFormat($cit["Data_Notifica_Atto_3"],"DB");
		$data_notifica_4 = $cls_date->Get_DateNewFormat($cit["Data_Notifica_Atto_4"],"DB");

		$data_comparsa_1 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Comparsa_1"],"DB");
		$data_comparsa_2 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Comparsa_2"],"DB");
		$data_comparsa_3 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Comparsa_3"],"DB");
		$data_comparsa_4 = $cls_date->Get_DateNewFormat($cit["Data_Sottoscriz_Comparsa_4"],"DB");

		$data_depos_1 = $cls_date->Get_DateNewFormat($cit["Data_Dep_Comparsa_1"],"DB");
		$data_depos_2 = $cls_date->Get_DateNewFormat($cit["Data_Dep_Comparsa_2"],"DB");
		$data_depos_3 = $cls_date->Get_DateNewFormat($cit["Data_Dep_Comparsa_3"],"DB");
		$data_depos_4 = $cls_date->Get_DateNewFormat($cit["Data_Dep_Comparsa_4"],"DB");

		$data_depos_attore_1 = $cls_date->Get_DateNewFormat($cit["Data_Mem_Int_A"],"DB");
		$data_depos_attore_2 = $cls_date->Get_DateNewFormat($cit["Data_Replica_Mem_Int_A"],"DB");
		$data_depos_attore_3 = $cls_date->Get_DateNewFormat($cit["Data_Mem_Istr_A"],"DB");
		$data_depos_attore_4 = $cls_date->Get_DateNewFormat($cit["Data_Replica_Mem_Istr_A"],"DB");
		$data_depos_attore_5 = $cls_date->Get_DateNewFormat($cit["Data_Comparsa_Concl_A"],"DB");
		$data_depos_attore_6 = $cls_date->Get_DateNewFormat($cit["Data_Note_Replica_Concl_A"],"DB");
		$data_depos_attore_7 = $cls_date->Get_DateNewFormat($cit["Data_Istanza_A"],"DB");
		$data_depos_attore_8 = $cls_date->Get_DateNewFormat($cit["Data_Memorie_A"],"DB");

		$data_depos_conv_1 = $cls_date->Get_DateNewFormat($cit["Data_Mem_Int_C"],"DB");
		$data_depos_conv_2 = $cls_date->Get_DateNewFormat($cit["Data_Replica_Mem_Int_C"],"DB");
		$data_depos_conv_3 = $cls_date->Get_DateNewFormat($cit["Data_Mem_Istr_C"],"DB");
		$data_depos_conv_4 = $cls_date->Get_DateNewFormat($cit["Data_Replica_Mem_Istr_C"],"DB");
		$data_depos_conv_5 = $cls_date->Get_DateNewFormat($cit["Data_Comparsa_Concl_C"],"DB");
		$data_depos_conv_6 = $cls_date->Get_DateNewFormat($cit["Data_Note_Replica_Concl_C"],"DB");
		$data_depos_conv_7 = $cls_date->Get_DateNewFormat($cit["Data_Istanza_C"],"DB");
		$data_depos_conv_8 = $cls_date->Get_DateNewFormat($cit["Data_Memorie_C"],"DB");

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
	location.href="ricorso_pignoramento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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

			location.href="ricorso_pignoramento.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

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
		location.href="pagamento_pignoramento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");

}

//PAG SU
function pag_suc()
{
	if( modifica == 0 )
	{
		location.href="gestione_partita.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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
	top.location.href = "nuova_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>

<script>
<!-- ********** CALENDARIO ********** -->

$(document).ready(function(){

	$('.picker').datepicker();

	});

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
            // Inserimento dati partita in 'Gitco2/coattiva/ricorso_pignoramento.php'
            case "info":
            case "entry":
            case "fore":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/ricorso_pignoramento.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento'];
                break;

            default: alert("Ricerca non trovata!"); break;
        }
    }

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
        top.location.href="../ricorso_pignoramento.php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
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

	location.href = "ricorso_pignoramento.php?&c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>";

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
			top.location.href = "coazione.php?partita="+array_ritorno[0]+"&c=<?php echo $c; ?>&a="+array_ritorno[1];
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

<form 	id=form_ricorso 	name=form_ricorso 	action="" method=post			>
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
			Data Registrazione &nbsp;<input type=text class="text_center" readonly name=data_reg value="<?php echo $cls_date->Get_DateNewFormat($data_reg,"DB"); ?>" size=10>
		</td>
		<td class="text_right width30" >
			Data Chiusura &nbsp;<input type=text class="text_center" readonly name=data_chiusura value="<?php echo $cls_date->Get_DateNewFormat($data_chiusura,"DB"); ?>" size=10>
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
			n� <input name=num_sospensiva size=3 value="<?php echo $num_sosp; ?>" >&nbsp;
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
			n� <input name=num_merito size=3 value="<?php echo $num_merito; ?>" >&nbsp;
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



<?php echo $layout; ?>
<?php include(INC."/footer.php"); ?>
