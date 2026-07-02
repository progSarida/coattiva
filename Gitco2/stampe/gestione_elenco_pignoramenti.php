<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once INC . "/header.php";
include_once INC . "/menu.php";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$cls_html = new cls_html();
$a_docs = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM document_type WHERE TableTypeId=2 AND EnabledHtml=1 AND Id!=43"));
$a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => null, "text" => array("[Description]"));
$optDocs = $cls_html->getOptions($a_docs, $a_selection);


$selectedDaStampare = "";

$optionsRiscossione = "
                <option value=\"\"></option>
                <option value=\"CDS\">CDS/AMMINISTRATIVA</option>
				<option value=\"IMMOBILI\">IMMOBILI</option>
				<option value=\"IRPEF\">IRPEF</option>
				<option value=\"OSAP\">OSAP</option>
				<option value=\"PATRIMONIALE\">PATRIMONIALE</option>
				<option value=\"PUBBLICITA\">PUBBLICITA'</option>
				<option value=\"RIFIUTI\">RIFIUTI</option>";


$query = "SELECT DISTINCT CC_Ufficio, Comune FROM ufficio_giudiziario WHERE Tipo = 'tribunale' ORDER BY Comune ASC";
$array_tribunali = $cls_db->getResults($cls_db->ExecuteQuery($query));

$stringa = "";
for($i=0;$i<count($array_tribunali);$i++)
{
    $stringa.= "<option value='".$array_tribunali[$i]['CC_Ufficio']."'>";
    $stringa.= $array_tribunali[$i]['Comune']." - ".$array_tribunali[$i]['CC_Ufficio'];
    $stringa.= "</option>";
}


//$tribunale = new ufficio_giudiziario(null, null);
$lista_tribunali = $stringa;//$tribunale->lista_tribunali('options');

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));//mysql_query($queryIngiunzioni);
for ($i=0; $i < count($resIngiunzioni); $i++)
{
	$serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";
}

$layout = "<script>$('#presso_terzi').hide();</script>";

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F5
switchMenuImg("F5");
F5_button = function()
{
    //location.href="stampa_pignoramento.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	location.href="gestione_elenco_pignoramenti.php?&p=&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}


//F10
switchMenuImg("F10");
F10_button = function()
{
	ajaxCall();
    // if($('#tipo_pignoramento').val()!="")
        //$('#elenco_form').submit();
    // else
    //     alert('Selezionare un tipo di pignoramento!');
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

//CAMBIO PAGINA

</script>

<!-- Inclusione modale per ricerca utente-->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>
<!-- ********** AJAX / MODALI ********** -->
<script>
//Modali offcanvas
function openOfcanvas(type,rif){
    // Reset campi input
    $('.user_entry').val("");

    // Reset spazi tabella
    $('#appendTableUserEntry').empty();

    selectRif = rif;
    switch (type){
        case 'user_entry':
            // Setta stato checkbox iniziale
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
            // Setta tipop di ricerca iniziale
            //user_entry_S = "user_n";
            // Apre modale
            if(rif == 2 && $('#daco').val() == '')
                alert("Inserire prima l'utente da cui far partire la ricerca");
            else
                $('#userEntrySearchModal').modal('show');
            break;
    }
}

function initialId(type,val){
    switch (type){
        case 'user':
        case 'cf':
        case "info":
        case "entry":
        case "fore":
            $("#genere").val(val['Genere']);                            // setta genere utente (M, F, D)
            if(selectRif == 1)                                          // "Da Cognome/Nome"
            {
                //alert("qui 1");
                if(val['Ditta_F'] != '' && val['Ditta_F'] != null){     // è una ditta
                    $('#daco').val(val['Ditta_F']);
                    $('#acog').val(val['Ditta_F']);
                    $('#dano').val('');
                    $('#anom').val('');
                } else{                                                 // è una persona
                    $('#daco').val(val['Cognome']);
                    $('#acog').val(val['Cognome']);
                    $('#dano').val(val['Nome']);
                    $('#anom').val(val['Nome']);
                }

            }
            else if(selectRif == 2)                                     // "A Cognome/Nome"
            {
                if(val['Ditta_F'] != '' && val['Ditta_F'] != null){     // è una ditta
                    $('#acog').val(val['Ditta_F']);
                    $('#anom').val('');
                } else{                                                 // è una persona
                    $('#acog').val(val['Cognome']);
                    $('#anom').val(val['Nome']);
                }
            }
            break;
        default: alert("Errore Ricerca");
    }
}


function Dim_Alert ( sWidth, sHeight )
{
setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

	return setupPagina;
}
function callParent(valorediritorno){
    switch(selectParent){
        case "utente":

            if(valorediritorno!=null)
            {
                $.post("ajax/ajax_stampe.php?c=<?php echo $c; ?>" ,

                    { 'ajax': 'nome' ,
                        'ID': valorediritorno },

                    function (value) {

                        var array_ritorno = value.split('*');

                        if(selectRif==1)
                        {
                            $('#daco').val(array_ritorno[0]);
                            $('#acog').val(array_ritorno[0]);
                        }
                        else if(selectRif==2)
                        {
                            $('#acog').val(array_ritorno[0]);
                        }

                        if(array_ritorno.length == 2)
                        {
                            if(selectRif==1)
                            {
                                $('#dano').val(array_ritorno[1]);
                                $('#anom').val(array_ritorno[1]);
                            }
                            else if(selectRif==2)
                            {
                                $('#anom').val(array_ritorno[1]);
                            }
                        }
                        else
                        {
                            if(selectRif==1)
                            {
                                $('#dano').val("");
                                $('#anom').val("");
                            }
                            else if(selectRif==2)
                            {
                                $('#anom').val("");
                            }
                        }
                    });
            }

            break;
    }

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

			strDim = Dim_Alert(800, 500);
			var stringa = "<?= WEB_ROOT; ?>/coattiva/modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			valorediritorno = window.showModalDialog(stringa,"", strDim);

			break;
	}
}

</script>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {
	
	 $( ".picker" ).datepicker();

	 });

</script>

<!-- ********** AGGIORNAMENTO PAGINA ********** -->
<script>

function insert_notif()
{
	$('#a_notif').val( $('#da_notif').val() );
}

function insert_stampa()
{
	$('#a_stampa').val( $('#da_stampa').val() );
}

function insert_elab()
{
	$('#a_elab').val( $('#da_elab').val() );
}

function insert_anno()
{
	$('#ad_anno').val( $('#da_anno').val() );
}

function insert_annoCrono()
{
	$('#to_cronoYear').val( $('#from_cronoYear').val() );
}

function primoIndex()
{
	$('[tabindex=1]').focus();
}

function avviso_posta()
{

	tipo_stampa = $("#stampa_select").val();
	
	if(tipo_stampa=="Definitiva")
		$('#p_avviso').text("L'esecuzione della stampa definitiva aggiorner� lo stato di stampa dei pignoramenti e la corrispondenza.");
	else
		$('#p_avviso').text("");

	if(tipo_stampa=="Provvisoria")
		$("#stato_stampa").val('Da stampare');
}

function control_pigno()
{
	tipo_pigno = $('#documentTypeId').val();

	if(tipo_pigno==6 && $('#anomalia').val()=="no")
		$('#ordinamento').val('tribunale');
}

function change_ordinamento()
{
	change_salta_pagina();
}

function change_salta_pagina()
{
	change_elenco();
	
	salta_pagina = $('#salta').val();

	if(salta_pagina=="tribunale")
		$('#ordinamento').val('tribunale');
}

function change_ufficiale()
{
	change_elenco();
}

function change_elenco()
{
	tipo_elenco = $('#tipo_elenco').val();

	if(tipo_elenco == "tribunale")
	{
		$('#salta').val('tribunale');
		$('#ordinamento').val('tribunale');
		$('#tipo_ufficiale').val('giudiziario');
		$('#elenco_form').attr("action","elenco_pignoramenti.php");
	}
	else if(tipo_elenco == "spese_notifica")
	{
		$('#salta').val('tribunale');
		$('#ordinamento').val('tribunale');
		$('#tipo_ufficiale').val('giudiziario');
		$('#elenco_form').attr("action","elenco_pignoramenti_spese_notifica.php");
		alert("ATTENZIONE! Le posizioni senza collegamento tra comune di residenza e tribunale non verranno visualizzate nell'elenco.");
	}
	else if(tipo_elenco == "spese_postali")
	{
		$('#salta').val('');
		$('#tipo_ufficiale').val('');
		$('#ordinamento').val('progressivo');
		$('#elenco_form').attr("action","elenco_pignoramenti_spese_postali.php");
	}
	else
	{
		$('#salta').val('');
		$('#tipo_ufficiale').val('');
		$('#ordinamento').val('progressivo');									// eliminato perchè da problemi alla select
		$('#elenco_form').attr("action","elenco_pignoramenti.php");
	}
}

function change_anomalia()
{
	if($('#tipo_elenco').val()=="tribunale")
	{
		$('#ordinamento').val('tribunale');
		$('#anomalia').val('no');
	}
	
	if($('#documentTypeId').val()==6 && $('#tipo_elenco').val()!="tribunale")
	{
		if($('#anomalia').val()!="no")
			$('#ordinamento').val('progressivo');
		else
			$('#ordinamento').val('tribunale');
	}
		
}

</script>


<!-- ********** SUBMIT(stampa) ********** -->
<script>

$(document).ready(function(){

	//$("#stampa_click").click( stampa_F10 );
	spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");

	});

	function ajaxCall() {
		spinner.startSpinner();
		//alert("ajax");
		//return;
        $.ajax({
            //url: "print_storico.php",
            url: $("form").attr('action'),
            //data: new FormData(document.getElementById("storico_form")),
            data: $("form").serialize(),
            dataType : 'json',
            type: 'POST',
            success: function (resp) {
                spinner.closeSpinner();
                ShowAlert(resp.error,resp.msg);
                if(resp.error == 0)
                    showFileOnModal(resp.path,"Pignoramenti",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //alert(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
	}

</script>

<script>
blocca_modifica = 1;
</script>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Gestione elenchi pignoramenti</font></td>
	</tr>
</table>
	
<!-- <form id="elenco_form" name="elenco_form" action="elenco_pignoramenti.php" method="post" target="elenco" onSubmit="window.open('', 'elenco', 'width=1000,height=800,top=70,left=70,scrollbars=yes,menubar=no')"> -->
<form id="elenco_form" name="elenco_form" action="elenco_pignoramenti.php">

		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">

<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=8 class="text_center"><font class="titolo font16 under_decor">Selezione</font></td>
	</tr>
	<tr>
		<td colspan=8 class="pheight5"><hr></td>
	</tr>
	<tr>
		<td class="width25 text_left">
			<input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_entry',1);">
		</td>
		<td class="width50 text_left" colspan=5>
			<input type="text" id="daco" name="daco" size=25  tabindex=3>
			<input type="text" id="dano" name="dano" size=15  tabindex=4>
		</td>
		<td class="width15 text_left">Da partita</td>
		<td class="width10 text_left">
			<select name="da_n_elenco" tabindex=7>
				<option value=""></option>
				<?php echo $serieOption ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="text_left">
			<input class="button_azzurro pwidth150" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_entry',2);">
		</td>
		<td class="text_left" colspan=5>
			<input type="text" id="acog" name="acog" size=25  tabindex=5>
			<input type="text" id="anom" name="anom" size=15  tabindex=6>
		</td>
		<td class="text_left">a partita</td>
		<td class="text_left">
			<select name="a_n_elenco" tabindex=8>
				<option value=""></option>
				<?php echo $serieOption ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Anni di riferimento</font></td>
		<td class="width10 text_center">Da anno</td>
		<td class="width10 text_left"><input type="text" class="text_right" name="da_anno" id="da_anno" value="<?php echo $a; ?>" onchange="insert_anno();" size=5  tabindex=9></td>
		<td class="width10 text_center">ad anno </td>
		<td class="width10 text_left"><input type="text" class="text_right" name="ad_anno" id="ad_anno" value="<?php echo $a; ?>" size=5  tabindex=10></td>
        <td class="width15 text_left">Tipo Riscossione</td>
        <td class="width20 text_left" colspan="2">
            <select name=taxType class="width95">
                <?= $optionsRiscossione; ?>
            </select>
        </td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Anno Cronologico</font></td>
		<td class="width10 text_center">Da anno</td>
		<td class="width10 text_left"><input type="text" class="text_right" name="from_cronoYear" id="from_cronoYear" value="" onchange="insert_annoCrono();" size=5  tabindex=11></td>
		<td class="width10 text_center">ad anno </td>
		<td class="width10 text_left"><input type="text" class="text_right" name="to_cronoYear" id="to_cronoYear" value="" size=5  tabindex=12></td>
	</tr>
	<tr>
		<td colspan=8><hr></td>
	</tr>
</table>
		
<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Tipo elenco</font></td>
		<td class="width40 text_left" colspan=4>
			<select class="width95" name=tipo_elenco id=tipo_elenco onchange="change_elenco()">
				<option value="generale" 		>Generale</option>
				<option value="tribunale"		>Distinta per tribunale</option>
				<option value="spese_notifica"	>Distinta per spese di notifica</option>
				<option value="spese_postali"	>Distinta per spese postali</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Tipo pignoramento</font></td>
		<td class="width40 text_left" colspan=4>
			<select class="width95" name=DocumentTypeId id=documentTypeId onchange="control_pigno();">
				<?= $optDocs;?>
				<!-- <option></option>
				<option value="terzi"		>Presso terzi</option>
				<option value="mobiliare"	>Mobiliare</option>
				<option value="veicolo"		>Beni mobili registrati</option>
				<option value="immobiliare"	>Immobiliare</option>
				<option value="fermo"		>Fermo amministrativo</option>
				<option value="preav_fermo"		>Preavviso Fermo amministrativo</option> -->
			</select>
		</td>
		<!-- <td class="width35 text_left" colspan=4>
			<select name=presso_terzi id=presso_terzi class="width60">
				<option></option>
				<option value="lavoro"	>Datore di lavoro</option>
				<option value="banca"	>Banca / Posta</option>
				<option value="inps"	>Istituti previdenziali</option>
				<option value="altro"	>Altri terzi</option>
			</select>
		</td> -->
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Pignoramento notificato</font></td>
		<td class="width40 text_left" colspan=4>
			<select class="width95" name=FlagNotifica id=FlagNotifica>
				<option value="0"></option>
				<option value="1">Assente</option>
				<option value="2">Presente</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan=9><hr></td>
	</tr>
	
	
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data elaborazione</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_elab" id="da_elab" value="" onchange="insert_elab();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_elab" id="a_elab" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_elab" id="data_elab" value="assente" ></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data spedizione</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_sped" id="da_sped" value="" onchange="insert_sped();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_sped" id="a_sped" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_spedizione" id="data_spedizione" value="assente" ></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Ufficiale</font></td>
		<td class="width40 text_left" colspan=4>
			<select id=tipo_ufficiale name="tipo_ufficiale" tabindex=19 class="width95" onchange="change_ufficiale()">
				<option></option>
				<option value="riscossione">Ufficiale della Riscossione</option>
				<option value="giudiziario">Ufficiale Giudiziario</option>
			</select>
		</td>
		<td class="width35 text_center" colspan=3></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data consegna all'Ufficiale</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_cons" id="da_cons" value="" onchange="insert_sped();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_cons" id="a_cons" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_consegna" id="data_consegna" value="assente" ></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data notifica debitore</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_notif" id="da_notif" value="" onchange="insert_notif();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_notif" id="a_notif" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_notif" id="data_notif" value="assente" ></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	
	
	
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data stampa</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_stampa" id="da_stampa" value="" onchange="insert_stampa();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_stampa" id="a_stampa" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2>Data assente <input type=checkbox name="data_stampa" id="data_stampa" value="assente" ></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Stato stampa</font></td>
		<td class="width40 text_left" colspan=4>
			<select name="stato_stampa" tabindex=19 class="width95">
				<option></option>
				<option id=stampa_1 <?=$selectedDaStampare?>>Da stampare</option>
				<option id=stampa_2>Stampato</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Pagamenti</font></td>
		<td class="width40 text_left" colspan=4>
			<select name="paymentStatus" class="width95">
				<option></option>                                                                                                                     
				<option value='incompleted'>Incompleti ( Nessuno + Parziali )</option>
				<option value='no'>Nessuno</option>
				<option value='partial'>Parziali</option>
				<option value='completed'>Completi</option>
				<option value='yes'>Presenti ( Qualsiasi pagamento )</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan=9><hr></td>
	</tr>
	
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Salta pagina</font></td>
		<td class="width40 text_left" colspan=4>
			<select id=salta name=salta class="width95" onchange="change_salta_pagina();">
				<option></option>
				<option value=tribunale>Ogni cambio Tribunale</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Tribunale di competenza</font></td>
		<td class="width40 text_left" colspan=4>
			<select id=tribunale name=tribunale class="width95">
				<option></option>
				<?php echo $lista_tribunali; ?>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Ordinamento</font></td>
		<td class="width40 text_left" colspan=4>
			<select id=ordinamento name=ordinamento class="width95" onchange="change_ordinamento();">
				<option selected value=progressivo>Partita</option>
				<option value=alfabetico>Alfabetico</option>
				<option value=tribunale>Tribunale</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Blocco coazione</font></td>
		<td class="width40 text_left" colspan=4>
			<select name="blocco" tabindex=19 class="width95">
				<option>No</option>
				<option>Si</option>
				<option>Entrambi</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_red font_bold">Anomalie</font></td>
		<td class="width40 text_left" colspan=4>
			<select id=anomalia name=anomalia class="width95" onchange="change_anomalia()">
				<option selected value=""></option>
				<option value="no">Nessuna</option>
				<option value="si">Solo Anomalie</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td colspan=9><hr></td>
	</tr>
	<tr>
		<td colspan=9><p id=p_avviso></p></td>
	</tr>
</table>

	<br>
	
</form>

</td>
</tr>
</table>

<?php echo $layout; ?>

<?php include_once INC . "/footer.php"; ?>