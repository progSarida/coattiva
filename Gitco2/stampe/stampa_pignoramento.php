<?php
include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_html.php";
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_db = new cls_db();
$a_printer = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM printer"));
$cls_html = new cls_html();
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>null, "text"=>array("[Name]"));
$optPrinter = $cls_html->getOptions($a_printer,$a_selection);

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$Select_Tax = $comune->Select_Tax;
if($Select_Tax<1)
    alert("Non e' possibile procedere con le elaborazioni senza aver impostato la Gestione entrate in PARAMETRI -> ENTE -> DATI ENTE");

if($Select_Tax==1){
    $optionsRiscossione = "<option value=\"CDS\">CDS/AMMINISTRATIVA</option>
				<option>IMMOBILI</option>
				<option>IRPEF</option>
				<option>OSAP</option>
				<option>PATRIMONIALE</option>
				<option value=\"PUBBLICITA\">PUBBLICITA'</option>
				<option>RIFIUTI</option>";
}
else if($Select_Tax==2)
    $optionsRiscossione = "<option value=\"\">TUTTE</option>";
else{
    $optionsRiscossione = "";
    $Select_Tax = 0;
}

$layout = "";
$selectedDaStampare = "";
$option_pignoramento = "";

$tipo_pignoramento = get_var('tipo_pignoramento');
switch($tipo_pignoramento)
{
	case "lavoro":				$action_page = "pignoramento_presso_lavoro.php";
								$tipo_pigno_visual = "presso datore di lavoro";
								$next_page = "banca";
								$prev_page = "veicolo";
								$option_pignoramento = "<option value=pec>Spedizione PEC</option>";
                                $option_pignoramento.= "<option value=flusso>Flusso</option>";
								
								break;
								
	case "banca":				$action_page = "pignoramento_presso_banca.php";
								$tipo_pigno_visual = "presso banca";
								$next_page = "veicolo";
								$prev_page = "lavoro";
								$option_pignoramento = "<option value=pec>Spedizione PEC</option>";
								$option_pignoramento.= "<option value=flusso>Flusso</option>";
	
								break;
								
	case "veicolo":				$action_page = "pignoramento_veicolo.php";
								$tipo_pigno_visual = "veicolo";
								$next_page = "lavoro";
								$prev_page = "banca";
								$layout.= "<script>$('#ordinamento').val('tribunale');</script>";
								$option_pignoramento = "<option value=pec>Spedizione PEC</option>";
								$option_pignoramento.= "<option value=flusso>Flusso</option>";
								
								break;
								
	default:
								
								break;
}

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = mysql_query($queryIngiunzioni);
while ($rigaIngiunzioni = mysql_fetch_assoc($resIngiunzioni))
{
	$serieOption .= "<option value='" . $rigaIngiunzioni['Comune_ID'] . "'>" . $rigaIngiunzioni['Comune_ID'] . "</option>";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Stampa atti</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>


<!-- ********** VARIABILI ********** -->
<script>

var pigno_val = "<?php echo $tipo_pignoramento ?>";
var select_tax = <?=$Select_Tax;?>;
</script>


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
	return true;
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	location.href="stampa_pignoramento.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_pignoramento="+pigno_val;
}

//F6
function nuovo_F6()
{
	return true;
}

//F7-F8
function cambia_pag(value)
{
	return true;
}

//PAG GIU
function pag_prec()
{
	pagina_menu('prev');
}

//PAG SU
function pag_suc()
{
	pagina_menu('suc');
}

//F9
function ricerca_F9()
{
	return true;
}

//F10
function stampa_F10()
{
    if($("#PrinterId").val()=="")
        alert("Inserire lo stampatore!");
    else if(select_tax<1)
        alert("Non e' possibile procedere con le elaborazioni senza aver impostato la Gestione entrate in PARAMETRI -> ENTE -> DATI ENTE");
    else
        $('#stampa_form').submit();
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

//CAMBIO PAGINA
function pagina_menu (value)
{
	
	if (value == 'suc')
	{
		cambio_pagina = "<?php echo $next_page; ?>";
	}
	else if (value == 'prev')
	{
		cambio_pagina = "<?php echo $prev_page; ?>";
	}
	
	link = "stampa_pignoramento.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo_pignoramento="+cambio_pagina;
		
	top.location.href = link;
		
}

</script>


<!-- ********** AJAX / MODALI ********** -->
<script>

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
			var stringa = "/gitco2/coattiva/modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";		
			valorediritorno = window.showModalDialog(stringa,"", strDim);
			
			break;
	}
}

function control_stampa_select()
{
	select_stampa = $('#stampa_select').val();
	if(select_stampa == "pec")
	{
		$('#stato_stampa').val('Stampato');
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

</script>


<!-- ********** SUBMIT(stampa) ********** -->
<script>

$(document).ready(function(){

	$("#stampa_click").click( stampa_F10 );

	});

</script>

<body class="sfondo_new_gitco" >

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td class="width1"><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td class="width1"><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>

<?php include MENU . '/menu_generale.php'; ?>

<script>
blocca_modifica = 1;
</script>

<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td class="text_center width7">
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo"></a>
        </td>
		<td class="text_center width11">
          	
        </td>
		<td class="text_center width7">
          	<a href="#" id="stampa_click" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
        </td>
		<td class="text_center width3">
          	
        </td>
		<td class="text_center width7" >
			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td class="text_center width2"></td>
		<td class="text_center width7">
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Gestione stampe</font></td>
	</tr>
</table>
	
<form id="stampa_form" name="stampa_form" action="<?php echo $action_page; ?>" method="post" target="stampa" onSubmit="window.open('', 'stampa', 'width=1000,height=800,top=70,left=70,scrollbars=yes,menubar=no')">
		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">
	
<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width15">Pignoramento</td>
		<td class="text_left width40">
			<input name=tipo_atto readonly class="text_left sfondo_ricerca" value="<?php echo $tipo_pigno_visual; ?>" size=35 tabindex=1>
		</td>
		<td class="width20 text_left">Tipo di stampa</td>
		<td class="width25 text_left">
			<select name="stampa_select" id=stampa_select onchange="control_stampa_select();" tabindex=2>
				<option value="cronologici">Assegnamento cronologici</option>
				<option>Provvisoria</option>
				<option>Definitiva</option>
				<?php echo $option_pignoramento; ?>
			</select>
		</td>
	</tr>
	<tr class="display_element">
		<td class="text_left" colspan=2>
			
		</td>
		<td class="text_left width20">Data di stampa</td>
		<td class="text_left width25">
			<input type="text" class="text_center picker" name="data_definitiva" id="data_definitiva" value="<?php echo date('d/m/Y'); ?>" onchange="insert_elab();" size=9>
		</td>
	</tr>
	<tr>
		<td colspan=4><hr></td>
	</tr>
</table>

<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=4 class="text_center"><font class="titolo font16 under_decor">Selezione</font></td>
	</tr>
	<tr>
		<td colspan=4 class="pheight5"></td>
	</tr>
	<tr>
		<td class="width25 text_left">
			<input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',1);">
		</td>
		<td class="width50 text_left">
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
			<input class="button_azzurro pwidth150" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',2);">
		</td>
		<td class="text_left">
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
		<td colspan=4><hr></td>
	</tr>
</table>
		
<table class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Anni di riferimento</font></td>
		<td class="width10 text_center">Da anno</td>
		<td class="width10 text_left"><input type="text" class="text_right" name="da_anno" id="da_anno" value="<?php echo $a; ?>" onchange="insert_anno();" size=5  tabindex=9></td>
		<td class="width10 text_center">ad anno </td>
		<td class="width10 text_left"><input type="text" class="text_right" name="ad_anno" id="ad_anno" value="<?php echo $a; ?>" size=5  tabindex=10></td>
		<td class="width35 text_right" colspan=4>
            <font class="color_titolo font_bold">Tipo Entrata</font>
            <select name=tipo_partita class="width62">
                <?=$optionsRiscossione;?>
            </select>
        </td>
	</tr>
    <tr>
        <td class="text_left width25"><font class="color_titolo font_bold">Stampatore</font></td>
        <td class="width40 text_center" colspan=4>
            <select name="PrinterId" id="PrinterId" class="width100">
                <?=$optPrinter;?>
            </select>
        </td>
        <td class="width35 text_left" colspan=4></td>
    </tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data elaborazione</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_elab" id="da_elab" value="" onchange="insert_elab();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_elab" id="a_elab" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data notifica debitore</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_notif" id="da_notif" value="" onchange="insert_notif();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_notif" id="a_notif" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data spedizione</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_sped" id="da_sped" value="" onchange="insert_sped();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_sped" id="a_sped" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data consegna</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_cons" id="da_cons" value="" onchange="insert_sped();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_cons" id="a_cons" value="" size=9  tabindex=12></td>
		<td class="width10 text_center" colspan=3><font class="color_titolo font_bold">consegnato ad&nbsp;</font>
			<select name="consegnato_a" tabindex=19 class="width58">
				<option></option>
				<option>Ufficiale riscossioni</option>
				<option>Ufficiale giudiziario</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Data stampa</font></td>
		<td class="width20 text_center" colspan=2>Da &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="da_stampa" id="da_stampa" value="" onchange="insert_stampa();" size=9  tabindex=11></td>
		<td class="width20 text_center" colspan=2>a &nbsp;&nbsp;&nbsp;<input type="text" class="text_center picker" name="a_stampa" id="a_stampa" value="" size=9  tabindex=12></td>
		<td class="width20 text_center" colspan=2></td>
		<td class="width15 text_left" colspan=2></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Stato stampa</font></td>
		<td class="width40 text_center" colspan=4>
			<select id="stato_stampa" name="stato_stampa" tabindex=19 class="width100">
				<option id=stampa_1 <?=$selectedDaStampare?>>Da stampare</option>
				<option id=stampa_2>Stampato</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Ordinamento</font></td>
		<td class="width40 text_center" colspan=4>
			<select id=ordinamento name=ordinamento class="width100">
				<option value=progressivo>Progressivo</option>
				<option value=info>Informazioni cartella</option>
				<option value=alfabetico>Alfabetico</option>
				<option value=verbale>Numero verbale ( solo CDS )</option>
				<option value=tribunale>Tribunale</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td colspan=9><hr></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Sel. stampe definitive</font></td>
		<td class="width20 text_left" colspan=2><input type="checkbox" name="sel_originale" id="sel_originale" value="si" checked> ORIGINALE</td>
		<td class="width20 text_left" colspan=2><input type="checkbox" name="sel_debitore" id="sel_debitore" value="si" checked> DEBITORE</td>
		<td class="width20 text_left" colspan=4>
			<input type="checkbox" name="sel_terzi" id="sel_terzi" value="si" checked> TERZI/IVG
			&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="sel_bollettino" id="sel_bollettino" value="si" checked> BOLLETTINO
		</td>
	</tr>
	<tr>
		<td colspan=9>Elementi da visualizzare in fase di "Stampa Definitiva".<br>Con STATO STAMPA "Da stampare" verranno creati tutti i file anche se non selezionati per la visualizzazione.</td>
	</tr>
	<tr>
		<td colspan=9><hr></td>
	</tr>
	<tr>
		<td colspan=9><p id=p_avviso></p></td>
	</tr>
</table>

<?php echo $layout; ?>
	<br>
	
</form>

</td>
</tr>
</table>

</body>
</html>