<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS . "/cls_Stampe.php";

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$p = $cls_help->getVar('p');

$cls_stampe = new cls_Stampe();

$tipo = $cls_help->getVar('tipo');  //  STAMPA o ELENCO

$questaPagina = "pagina_stampa_pagamenti.php";

//$myPagamento = new pagamento(null, $c);

/*if ($tipo == "STAMPA")
{
	$action = "stampa_richieste_dati.php";
	$scrittaRistampa = "Mostra richieste dati gia stampate";
	$scrittaOnSubmit = "window.open('', 'stampa', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no');";
}
else*/ if ($tipo == "ELENCO")
{
	$action = "elenco_pagamenti.php";
	//$scrittaRistampa = "Elenco richieste dati gia stampate";
	$scrittaOnSubmit = "window.open('', 'stampa', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no');";
}
else { $cls_help->alert ("La scelta '$tipo' non e' ELENCO"); return; }

$autorizzazione = $cls_help->getVar('aut_tipo');

$serieOption = "";
/*$queryPagamenti = "SELECT DISTINCT Pag_Registro FROM targhe_estere_pagamenti ";
$queryPagamenti .= " WHERE Pag_Comune_CC = '$c' AND ";
$queryPagamenti .= " Pag_Notifica != 0 ";
$queryPagamenti .= " ORDER BY Pag_Registro ";
$resPagam = esegui_query($queryPagamenti);
while ($rigaPagam = risultati_query($resPagam))
{
	$serieOption .= "<option value='" . $rigaPagam['Pag_Registro'] . "'>" . $rigaPagam['Pag_Registro'] . "</option>\n";
}*/

$dataOdierna = date("d/m/Y");

$queryAnniGestiti = "SELECT Anno FROM anni_gestiti WHERE CC_Anno = '$c' ";
$queryAnniGestiti .= " AND Gestione_Coattiva = 'Y' order by Anno DESC";
$resAnniGestiti = $cls_db->getResults($cls_db->ExecuteQuery($queryAnniGestiti));

$listaAnni = array();
$listaSpeseRicerca = array();
$selectAnniGestiti = "";
for ($i=0; $i < count($resAnniGestiti); $i++)
{   /** ????????????????????????? '*****' non ci va 'CDS'???????????????????????? **/
	$listaAnni[] = $resAnniGestiti[$i]['Anno'];
    $query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$resAnniGestiti[$i]['Anno']."' AND Tipo_Riscossione = '*****'";
	$parametri_annuale = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_annuali");// new gestione_parametri_annuali($c, $resAnniGestiti[$i]['Anno'], "CDS");
	$listaSpeseRicerca[] = $parametri_annuale->Spese_Ricerca;
	$selectAnniGestiti .= "<option value='" . $resAnniGestiti[$i]['Anno'] . "'>" . $resAnniGestiti[$i]['Anno'] . "</option>\n";
}

$queryTributi = "SELECT DISTINCT Tipo, Sottotipo FROM partita_tributi WHERE CC = '$c' ORDER BY Tipo ASC";
$resTributi = $cls_db->getResults($cls_db->ExecuteQuery($queryTributi));//esegui_query($queryTributi);
$checkTributi = "";
$cont=0;
for($i=0; $i < count($resTributi); $i++)
{
    $display = $resTributi[$i]['Tipo'];
    if($resTributi[$i]['Sottotipo']!="")
        $display.= " - ".$resTributi[$i]['Sottotipo'];
    $checkTributi .= "<input type='checkbox' name='tipoTributo[]' value='" . $resTributi[$i]['Tipo'] . "-".$resTributi[$i]['Sottotipo']."' checked> " . $display . "&nbsp;&nbsp;&nbsp;";
    $cont++;
}

?>
    
<script type="text/javascript" language="Javascript">

function cambiocomune()
{
	var strLink = "<?=$questaPagina?>";
	strLink += "?c=" + $("#sceglicomune").val();
	strLink += "&a=" + "<?php echo $a?>";
	strLink += "&tipo=" + "<?php echo $tipo?>";

	location.href = strLink;
}

function checkData (testo)
{
	var nan;
	if (testo == "") return testo;
		
	if ((testo.length != 10) && (testo.length != 8))
	{
		alert ("La data non e' corretta!");
		return 0;
	}
	if (testo.length == 10) // puo' essere 12/12/2012
	{
		if (testo.charAt(2) == '/' && testo.charAt(5) == '/')
		{
			if (testo.charAt(0) >= '0' && testo.charAt(0) <= '3' &&
				testo.charAt(1) >= '0' && testo.charAt(1) <= '9' &&
				testo.charAt(3) >= '0' && testo.charAt(3) <= '1' &&
				testo.charAt(4) >= '0' && testo.charAt(4) <= '9' &&
				testo.charAt(6) >= '1' && testo.charAt(6) <= '2' &&
				testo.charAt(7) >= '0' && testo.charAt(7) <= '9' &&
				testo.charAt(8) >= '0' && testo.charAt(8) <= '9' &&
				testo.charAt(9) >= '0' && testo.charAt(9) <= '9')
			{
				nan = parseInt(testo.charAt(0) + testo.charAt(1));
				if (nan > 31) { alert ("La data non e' corretta!"); return 0; }
				nan = parseInt(testo.charAt(3) + testo.charAt(4));
				if (nan > 12) { alert ("La data non e' corretta!"); return 0; }
				nan = parseInt(testo.charAt(6) + testo.charAt(7) + testo.charAt(8) + testo.charAt(9));
				if ((nan < 1900) || (nan > 3000)) { alert ("La data non e' corretta!"); return 0; }
				return testo;
			}
		}
		alert ("La data non e' corretta!");
		return 0;
	}
	else if (testo.length == 8) // puo' essere 12122012
	{
		if (testo.charAt(0) >= '0' && testo.charAt(0) <= '3' &&
				testo.charAt(1) >= '0' && testo.charAt(1) <= '9' &&
				testo.charAt(2) >= '0' && testo.charAt(2) <= '1' &&
				testo.charAt(3) >= '0' && testo.charAt(3) <= '9' &&
				testo.charAt(4) >= '1' && testo.charAt(4) <= '2' &&
				testo.charAt(5) >= '0' && testo.charAt(5) <= '9' &&
				testo.charAt(6) >= '0' && testo.charAt(6) <= '9' &&
				testo.charAt(7) >= '0' && testo.charAt(7) <= '9')
		{
			nan = parseInt(testo.charAt(0) + testo.charAt(1));
			if (nan > 31) { alert ("La data non e' corretta!"); return 0; }
			nan = parseInt(testo.charAt(2) + testo.charAt(3));
			if (nan > 12) { alert ("La data non e' corretta!"); return 0; }
			nan = parseInt(testo.charAt(4) + testo.charAt(5) + testo.charAt(6) + testo.charAt(7));
			if ((nan < 1900) || (nan > 3000)) { alert ("La data non e' corretta!"); return 0; }
			testo = testo.charAt(0) + testo.charAt(1) + '/' + testo.charAt(2) + testo.charAt(3) + '/' + testo.charAt(4) + testo.charAt(5) + testo.charAt(6) + testo.charAt(7);
			return testo;
		}
		alert ("La data non e' corretta!");
		return 0;
	}
	else
	{
		alert ("La data non e' corretta!");
		return 0;
	}
}

function ctrlData(campo)
{
	var area = $("#"+campo);
	var ret = checkData(area.val());
	if (ret != "0") 
		area.val(ret);
	else
		area.val("");
}

function CtrlAnnoNum ()
{
	var daverbale = $("#da_n_elenco").val();
	var averbale = $("#a_n_elenco").val();
	var daanno = $("#da_anno").val();
	var adanno = $("#a_anno").val();

	var dadataavv = $("#da_avviso").val();
	var adataavv = $("#a_avviso").val();
	var dadatapag = $("#da_pagamento").val();
	var adatapag = $("#a_pagamento").val();
	var dadatareg = $("#da_registrazione").val();
	var adatareg = $("#a_registrazione").val();

	var danumverbale = parseInt(daverbale);
	var anumverbale = parseInt(averbale);

	if ((daverbale != "" && averbale == "") ||
			(daverbale == "" && averbale != ""))
	{
		alert ("Inserire entrambi i numeri di partita");
		return false;
	}

	if ((daanno != "" && adanno == "") ||
			(daanno == "" && adanno != ""))
	{
		alert ("Inserire entrambi gli anni di gestione");
		return false;
	}

	if (danumverbale > anumverbale)
	{
		alert ("I verbali non sono ordinati");
		return false;
	}
	
	if (daverbale != "" && (daanno != adanno))
	{
		alert ("Inserire o il limite sul verbale o il limite sull'anno");
		return false;
	}

	if ((dadataavv != "" && adataavv == "") ||
			(dadataavv == "" && adataavv != ""))
	{
		alert ("Inserire entrambe le date di infrazione");
		return false;
	}
	if ((dadatapag != "" && adatapag == "") ||
			(dadatapag == "" && adatapag != ""))
	{
		alert ("Inserire entrambe le date di pagamento");
		return false;
	}
	if ((dadatareg != "" && adatareg == "") ||
			(dadatareg == "" && adatareg != ""))
	{
		alert ("Inserire entrambe le date di registrazione");
		return false;
	}
	
	return true;
}

function CaricaActionForm ()
{
    <?php
    /*if ($tipo == "STAMPA")
    {
        echo <<< DEF
            if ($("[name=giastampate]").prop("checked") == true)
            {
                $("#stampa_form").attr("target", "");
                $("#stampa_form").attr("onSubmit", "");
                $("#stampa_form").attr("action", "/gitco2/targheestere/elaborazioni/richieste_estere_precedentemente_stampate.php");
            }
            else
            {
                $("#stampa_form").attr("target", "stampa");
                $("#stampa_form").attr("onSubmit", "$scrittaOnSubmit");
                $("#stampa_form").attr("action", "$action");
            }
DEF;
    }
    else*/ if ($tipo == "ELENCO")
    {
        echo <<< DEF
            $("#stampa_form").attr("target", "stampa");
            $("#stampa_form").attr("onSubmit", "$scrittaOnSubmit");
            $("#stampa_form").attr("action", "$action");
DEF;
    }
    ?>

}

//F5
switchMenuImg("F5");
F5_button = function()
{
    var stringaLink = "<?=$questaPagina?>";
    stringaLink += "?c=" + "<?php echo $c?>";
    stringaLink += "&a=" + "<?php echo $a?>";
    stringaLink += "&tipo=" + "<?php echo $tipo?>";
    location.href = stringaLink;
}

//F10
switchMenuImg("F10");
F10_button = function()
{
    var genDa = $("#genere_da").val();
    var genA = $("#genere_a").val()
    if(genDa != "" && genA != ""){
        if((genDa == "D" && genA != "D") || (genDa != "D" && genA == "D"))
        {
            alert("Nella ricerca Cognome/Ditta - Ditta inserire entrambe ditte o entrambe persone fisiche!");
            return false;
        }
    }
	ajaxCall();
    //StampaPagina();
}

function StampaPagina()
{
	if (CtrlAnnoNum() == false) return;
	if (AnnoGestione() == false) return;
	
	CaricaActionForm();
	
	/*if ($("#stampa_select").val() == "Definitiva")
	{
		if (($("#da_stampa").val() != "") || ($("#a_stampa").val() != ""))
		{
			alert ("Con la stampa definitiva, non si possono selezionare limiti di date di stampa");
			return;
		}
		$('#stampa_form').submit();
		//annulla();  //  ricarico pagina dopo stampe definitive
	}
	else*/
		$('#stampa_form').submit();
}

$(document).ready(function()
{
	//$("#stampa_click").click( StampaPagina );
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
                    showFileOnModal(resp.path,"Pagamenti",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //alert(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
	}

$(function()
{
	$("#da_avviso").datepicker();
	$("#da_pagamento").datepicker();
	$("#da_registrazione").datepicker();
    $("#da_infrazione").datepicker();
	$("#a_avviso").datepicker();
	$("#a_pagamento").datepicker();
	$("#a_registrazione").datepicker();
    $("#a_infrazione").datepicker();
});

function AnnoGestione ()
{
	var daanno = $("#da_anno").val();
	var adanno = $("#a_anno").val();
	var arrayAnni = new Array();
	var arraySpeseRic = new Array();

	<?php 
	for ($ppp = 0; $ppp < count($listaAnni); $ppp++)
	{
		echo "\narrayAnni[" . $ppp . "] = " . $listaAnni[$ppp] . ";";
		if ($listaSpeseRicerca[$ppp] != 0)
			echo "\narraySpeseRic[" . $ppp . "] = " . $listaSpeseRicerca[$ppp] . ";";
		else
			echo "\narraySpeseRic[" . $ppp . "] = 0;";
	}
	?>

	var testoFisso = "\nLa stampa potrebbe non essere corretta.";
	var anno1esiste = false;
	var anno2esiste = false;
	for (var i = 0; i < arrayAnni.length; i++)
	{
		if (arrayAnni[i] == daanno) anno1esiste = true;
		if (arrayAnni[i] == adanno) anno2esiste = true;
		if (arraySpeseRic[i] == 0 && arrayAnni[i] == daanno)
		{
			alert ("Per l'anno " + arrayAnni[i] + " non e' presente il parametro annuale di spese di ricerca" + testoFisso);
			//return false;
		}
		if (arraySpeseRic[i] == 0 && arrayAnni[i] == adanno)
		{
			alert ("Per l'anno " + arrayAnni[i] + " non e' presente il parametro annuale di spese di ricerca" + testoFisso);
			//return false;
		}

		if (anno1esiste == true && anno2esiste == true)
			break;
	}

	/*if (anno1esiste == false && anno2esiste == false)
	{
		alert ("Gli anni " + daanno + " e " + adanno + " non sono gestiti in questo Comune" + testoFisso);
		return false;
	}
	else if (anno1esiste == false)
	{
		alert ("L'anno " + daanno + " non e' gestito in questo Comune" + testoFisso);
		return false;
	}
	else if (anno2esiste == false)
	{
		alert ("L'anno " + adanno + " non e' gestito in questo Comune" + testoFisso);
		return false;
	}
	else return true;*/

    return true;
}
</script>
<!-- GESTIONE MODALI -->
<!-- Inclusione modale per ricerca utente-->
<?php include_once(ROOT . "/search_modal/offcanvas/user_sel_offcanvas.php"); ?>
<script>
// Modali offcanvas
function openOfcanvas(type,rif){
    // Reset campi input
    $('.sel_surn').val("");
    $('.sel_name').val("");
    $('#type_sel').val("all");

    // Reset spazi tabella
    $('#appendTableUserSel').empty();

    selectRif = rif;
    switch (type) {
        case 'user_sel':
            // Apre modale
            if(rif == 2 && $('#daco').val() == '')
                alert("Inserire prima l'utente da cui far partire la ricerca");
            else
                $('#userSelSearchModal').modal('show');
    }
}

// Iserimento dati da modale a pagine
function initialId(type,val){
    switch (type){
        case 'user_sel':
            $("#genere").val(val['Genere']);                            // setta genere utente (M, F, D)
            if(selectRif == 1)                                          // "Da Cognome/Nome"
            {
                //alert("qui 1");
                if(val['Ditta'] != '' && val['Ditta'] != null){     // è una ditta
                    $('#daco').val(val['Ditta']);
                    $('#acog').val(val['Ditta']);
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
                if(val['Ditta'] != '' && val['Ditta'] != null){     // è una ditta
                    $('#acog').val(val['Ditta']);
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

var selectParent = "";
var selectRif = "";
function RicercheDaId (value, rif)
{
    selectParent = value;
    selectRif = rif;
    var valorediritorno = 0;
    //var strDim = Dim_Alert(600, 300);

    switch(value)
    {
        case "utente":

            //strDim = Dim_Alert(800, 500);
            var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale_sel.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            //valorediritorno = window.showModalDialog(stringa,"", strDim);
            openWindowSearch(stringa,{width:800, height:500, left:(($(window).width()/2)-400), top:(($(window).height()/2)-250)});

            break;
    }
}


function callParent(valorediritorno){
    switch(selectParent){
        case "utente":

            if(valorediritorno!=null)
            {
                $.post("ajax/ajax_cognome.php?c=<?php echo $c; ?>" ,

                    { 'ajax': 'nome' ,
                        'ID': valorediritorno },

                    function (value) {

                        var array_ritorno = value.split('*');

                        console.log(array_ritorno);

                        if(selectRif==1)
                        {
                            $('#daco').val(array_ritorno[0]);
                            $('#acog').val(array_ritorno[0]);
                        }
                        else if(selectRif==2)
                        {
                            $('#acog').val(array_ritorno[0]);
                        }

                        if(array_ritorno.length == 3)
                        {
                            if(selectRif==1)
                            {
                                $('#dano').val(array_ritorno[1]);
                                $('#anom').val(array_ritorno[1]);
                                $("#genere_da").val(array_ritorno[2]);
                                $("#genere_a").val(array_ritorno[2]);
                            }
                            else if(selectRif==2)
                            {
                                $('#anom').val(array_ritorno[1]);
                                $("#genere_a").val(array_ritorno[2]);
                            }
                        }
                        else
                        {
                            if(array_ritorno.length == 2) {
                                if(selectRif==1)
                                {
                                    $("#genere_a").val(array_ritorno[1]);
                                    $("#genere_da").val(array_ritorno[1]);
                                }
                                else if(selectRif==2)
                                {
                                    $("#genere_a").val(array_ritorno[1]);
                                }
                            }
                            else $("#genere").val("");

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

</script>
    


<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor"><?=ucfirst(strtolower($tipo))?> Pagamenti</font></td>
	</tr>
</table>
	
<!-- <form id="stampa_form" name="stampa_form" action="<?=$action?>" method="post" target="stampa" onSubmit="<?=$scrittaOnSubmit?>"> -->
<form id="stampa_form" name="stampa_form" action="<?=$action?>">
		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">
    <input type=hidden name="genere_da" id="genere_da" value="" />
    <input type=hidden name="genere_a" id="genere_a" value="" />

<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=4 class="pheight5"></td>
	</tr>
	<tr class="pheight25">
		<td class="width20 text_left">
			Da partita
		</td>
		<td class="width25 text_left">
			<!-- <select name="da_n_elenco" id="da_n_elenco">
				<option value=""></option>
				<?php echo $serieOption ?>
			</select> -->
			<input type="text" class="width20 text_right" name="da_n_elenco" id="da_n_elenco">
		</td>
		<td class="width30 text_left">
			Da anno gestione verbali originari
		</td>
		<td class="width25 text_left">
			<!-- <input type="text" class="text_center" name="da_anno" id="da_anno" value="<?=$a?>" size=8 tabindex=5 onchange="AnnoGestione();"> -->
			<select name="da_anno" id="da_anno" tabindex=5 onchange="AnnoGestione();">
                <option value=""></option>
				<?=$selectAnniGestiti?>
			</select>
		</td>
	</tr>
	<tr class="pheight25">
		<td class="text_left">
			a partita
		</td>
		<td class="text_left">
			<!-- <select name="a_n_elenco" id="a_n_elenco">
				<option value=""></option>
				<?php echo $serieOption ?>
			</select> -->
			<input type="text" class="width20 text_right" name="a_n_elenco" id="a_n_elenco">
		</td>
		<td class="width20 text_left">
			Ad anno gestione verbali originari
		</td>
		<td class="width25 text_left">
			<!-- <input type="text" class="text_center" name="a_anno" id="a_anno" value="<?=$a?>" size=8 tabindex=6 onchange="AnnoGestione();"> -->
			<select name="a_anno" id="a_anno" tabindex=6 onchange="AnnoGestione();">
                <option value=""></option>
				<?=$selectAnniGestiti?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan=4><hr></td>
	</tr>
</table>

<table class="table_interna text_center" border="0">
    <tr>
        <td class="width10">
            <input class="resize width100" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_sel',1);" tabindex=4>
        </td>
        <td class="width30">
            <input class="resize width100" type="text" id="daco" name="daco" value="<?= $cls_help->getVar("daco"); ?>"  tabindex=5>
        </td>
        <td class="width20">
            <input class="resize width100" type="text" id="dano" name="dano" value="<?= $cls_help->getVar("dano"); ?>" tabindex=6>
        </td>
        <td class="width40"></td>
    </tr>
    <tr>
        <td class="width10">
            <input class="resize width100" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_sel',2);" tabindex=7>
        </td>
        <td class="width30">
            <input class="width100 resize" type="text" id="acog" name="acog" value="<?= $cls_help->getVar("acog"); ?>"  tabindex=7>
        </td>
        <td class="width20">
            <input class="width100 resize" type="text" id="anom" name="anom" value="<?= $cls_help->getVar("anom"); ?>"  tabindex=9>
        </td>
        <td class="width40"></td>
    </tr>
</table>

<table class="table_interna text_center" border="0" style="margin-top: 2%;">
	<tr>
		<td colspan=6 class="text_center"><font class="titolo font16 under_decor">Selezioni</font></td>
	</tr>
	
	<tr class="pheight25">
		<td class="width20 text_left">Conto terzi</td>
		<td class="width16 text_left">
            <select name="contoTerzi">
                <option></option>
                <option value="Y">SI</option>
                <option value="N">NO</option>
            </select>
        </td>
		<td class="width20 text_left"></td>
		<td class="width16 text_left"></td>
		<td class="width18 text_left">Tipo Importazione</td>
		<td class="width10 text_left">
			<select name="selecttipopagamento">
				<?php echo $cls_stampe->ListaTipiPagamento();?>
			</select>
		</td>
	</tr>
    <tr class="pheight25">
        <td class="width20 text_left">Tipo Pagamento</td>
        <td class="width16 text_left">
            <select name="selectmodalitapagamento">
                <?php echo $cls_stampe->ListaModalitaPagamento();?>
            </select></td>
        <td class="width20 text_left"></td>
        <td class="width16 text_left"></td>
        <td class="width18 text_left"></td>
        <td class="width10 text_left">

        </td>
    </tr>
    <tr>
        <td colspan=6 ><hr></td>
    </tr>
    <tr>
        <td colspan=6 ><?php echo $checkTributi; ?></td>
    </tr>
	<tr>
		<td colspan=6 ><hr></td>
	</tr>
	<tr>
		<td colspan=6></td>
	</tr>
</table>

<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=5 class="text_center"><span class="titolo font16 under_decor">Date</span></td>
	</tr>
    <tr class="pheight25">
        <td class="width20 text_left"><span class="titolo font14">Data pagamento</span></td>
        <td class="width10 text_center">dal</td>
        <td class="width15 text_left"><input class="text_center picker" name="da_pagamento" id="da_pagamento" value="" size=10 onchange="ctrlData(id)" tabindex=9></td>
        <td class="width10 text_center">al</td>
        <td class="width45 text_left"><input class="text_center picker" name="a_pagamento" id="a_pagamento" value="" size=10 onchange="ctrlData(id)" tabindex=10></td>
    </tr>
    <tr class="pheight25">
        <td class="width20 text_left"><span class="titolo font14">Data registrazione</span></td>
        <td class="width10 text_center">dal</td>
        <td class="width15 text_left"><input class="text_center picker" name="da_registrazione" id="da_registrazione" value="" size=10 onchange="ctrlData(id)" tabindex=11></td>
        <td class="width10 text_center">al</td>
        <td class="width45 text_left"><input class="text_center picker" name="a_registrazione" id="a_registrazione" value="" size=10 onchange="ctrlData(id)" tabindex=12></td>
    </tr>
	<tr>
		<td colspan=5><hr></td>
	</tr>
    <tr>
        <td colspan=5 class="text_center">
            ATTENZIONE!!!<br>
            Il filtro data infrazione puo' essere utilizzato solo per il CDS importato da Gitco Gestione Ordinaria.
            <br>E' necessario selezionare solo il tributo CDS escludendo gli altri altrimenti la selezione verra' ignorata.
        </td>
    </tr>
    <tr>
        <td colspan=5><hr></td>
    </tr>
    <tr class="pheight25">
        <td class="width20 text_left"><span class="titolo font14">Data Infrazione</span></td>
        <td class="width10 text_center">dal</td>
        <td class="width15 text_left"><input class="text_center picker" name="da_infrazione" id="da_infrazione" value="" size=10 onchange="ctrlData(id)" tabindex=13></td>
        <td class="width10 text_center">al</td>
        <td class="width45 text_left"><input class="text_center picker" name="a_infrazione" id="a_infrazione" value="" size=10 onchange="ctrlData(id)" tabindex=10></td>
    </tr>
    <tr>
        <td colspan=5><hr></td>
    </tr>
</table>
	<br>
<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_center"><font class="titolo font16 under_decor">Ordinamento</font></td>
		<td class="text_center"><font class="titolo font16 under_decor">File di uscita</font></td>
	</tr>
	<tr class="pheight25">
		<td class="width50">
			<select name="ordinestampa">
				<option value="DATAPAG">Data pagamento</option>
				<!-- <option value="CRONO">Cronologico verbale originario</option>
				<option value="DATAINFR">Data infrazione</option> -->
				<option value="PARTITA">Numero partita</option>
			</select>
		</td>
		<td>
			<select name="tipofile">
				<option value="PDF">PDF</option>
				<option value="CSV">CSV</option>
			</select>
		</td>
	</tr>
</table>
	
</form>

</td>
</tr>
</table>

<?php include_once INC . "/footer.php"; ?>