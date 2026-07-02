<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = mysql_query($queryIngiunzioni);
while ($rigaIngiunzioni = mysql_fetch_assoc($resIngiunzioni))
{
	$serieOption .= "<option value='" . $rigaIngiunzioni['Comune_ID'] . "'>" . $rigaIngiunzioni['Comune_ID'] . "</option>";
}

$parametri_notifica = new parametri_notifica(null);
$parametri_notifica->array_notifica();

$options_stati = options_select_array($parametri_notifica->Stati);
$options_motivi = options_select_array($parametri_notifica->Motivi);
$options_a_mani = options_select_array($parametri_notifica->Mode_A_Mani, "Descrizione" , "Articolo");
$options_per_posta = options_select_array($parametri_notifica->Mode_Per_Posta, "Descrizione" , "Articolo");
$options_eccezionali = options_select_array($parametri_notifica->Mode_Eccezionali, "Descrizione" , "Articolo");

$a_years = select_mysql_array( "Anno" , "anni_gestiti" , "CC_Anno ='".$c."' AND Gestione_Coattiva = 'Y'", "Anno" );
$opt_years = "";
for($i=0;$i<count($a_years);$i++)
    $opt_years.= "<option value='".$a_years[$i]['Anno']."'>".$a_years[$i]['Anno']."</option>";





?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Gestione posizioni</title>

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
	location.href="gestione_elenco_posizioni.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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
	$('#elenco_form').submit();
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

//CAMBIO PAGINA


</script>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {
	
	 $( ".picker" ).datepicker();

	 });

</script>

<!-- ********** AGGIORNAMENTO PAGINA ********** -->
<script>

function insert_anno()
{
	$('#ad_anno').val( $('#da_anno').val() );
}

function primoIndex()
{
	$('[tabindex=1]').focus();
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
		<td><font class="titolo font16 under_decor">Gestione posizioni</font></td>
	</tr>
</table>
	
<form id="elenco_form" name="elenco_form" action="elenco_posizioni.php" method="post" target="elenco" onSubmit="window.open('', 'elenco', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')">
		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">
	
<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=4 class="pheight5"><hr></td>
	</tr>
	<tr>
		<td class="text_left width15">Tipo di elenco</td>
		<td class="text_left width40">
			<select class="width30" name="tipo_elenco" id=tipo_elenco tabindex=1>
				<option value="pdf">PDF</option>
				<option value="excel">EXCEL</option>
			</select>
		</td>
		<td class="width20 text_left"></td>
		<td class="width25 text_left"></td>
	</tr>
	<tr>
		<td colspan=4 class="pheight5"><hr></td>
	</tr>
</table>

<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=4 class="text_center"><font class="titolo font16 under_decor">Selezione filtri</font></td>
	</tr>
	<tr>
		<td colspan=4 class="pheight5"><hr></td>
	</tr>
	<tr>
		<td class="width25 text_left">
			<input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',1);">
		</td>
		<td class="width50 text_left">
			<input type="text" id="daco" name="daco" size=25  tabindex=2>
			<input type="text" id="dano" name="dano" size=15  tabindex=3>
		</td>
		<td class="width15 text_left">Da partita</td>
		<td class="width10 text_left">
			<select name="da_n_elenco" tabindex=6>
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
			<input type="text" id="acog" name="acog" size=25  tabindex=4>
			<input type="text" id="anom" name="anom" size=15  tabindex=5>
		</td>
		<td class="text_left">a partita</td>
		<td class="text_left">
			<select name="a_n_elenco" tabindex=7>
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
		<td class="width10 text_left">
            <select name="da_anno" id="da_anno" tabindex=8>
                <?php echo $opt_years; ?>
            </select>
            <script>
                $('#da_anno ').val('<?php echo $a; ?>');
            </script>
		<td class="width10 text_center">ad anno </td>
		<td class="width10 text_left">
            <select name="ad_anno" id="ad_anno" tabindex=9>
                <?php echo $opt_years; ?>
            </select>
            <script>
                $('#ad_anno ').val('<?php echo $a; ?>');
//                $('#ad_anno option:last').prop('selected', true);
            </script>
		<td class="width35 text_left" colspan=4>
			Tipo Entrata&nbsp;
			<select name=tipo_partita class="width50">
				<option></option>
				<option>CDS</option>
				<option>IMMOBILI</option>
				<option>IRPEF</option>
				<option>OSAP</option>
				<option>PATRIMONIALE</option>
				<option value="PUBBLICITA">PUBBLICITA'</option>
				<option>RIFIUTI</option>			
			</select>
		</td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Posizione</font></td>
		<td class="width40 text_left" colspan=4>
			<select name="posizione" tabindex=19 class="width100">
				<option value=""></option>
				<option value="vuota">Da elaborare</option>
				<option value="da assegnare">Ultimo atto senza cronologico</option>
				<option value="assegnato">Ultimo atto con cronologico</option>
				<option value="da stampare">Ultimo atto da stampare</option>
				<option value="stampato">Ultimo atto stampato</option>
				<option value="da notificare">Ultimo atto da notificare</option>
				<option value="notificato">Ultimo atto notificato</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Pignoramento</font></td>
		<td class="width40 text_center" colspan=4>
			<select name="pignoramento" tabindex=18 class="width100">
				<option value=""></option>
				<option value="no">Non presente</option>
				<option value="si">Presente</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Stato pagamenti</font></td>
		<td class="width40 text_center" colspan=4>
			<select name="pagamento" tabindex=18 class="width100">
				<option></option>
				<option>Nessuno</option>
				<option>Parziale</option>
				<option value="Nessuno_Parziale">Nessuno e parziale</option>
				<option>Totale</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
    <tr>
        <td class="text_left width25"><font class="color_titolo font_bold">Rateizzazione</font></td>
        <td class="width40 text_center" colspan=4>
            <select name="rateizzazione" tabindex=18 class="width100">
                <option></option>
                <option value="y">Presente</option>
                <option value="n">Non presente</option>
            </select>
        </td>
        <td class="width35 text_left" colspan=4></td>
    </tr>
    <tr>
        <td class="text_left width25"><font class="color_titolo font_bold">Stato rateizzazione</font></td>
        <td class="width40 text_center" colspan=4>
            <select name="stato_rateizzazione" tabindex=18 class="width100">
                <option></option>
                <option value="ongoing">In corso</option>
                <option value="completed">Completa</option>
                <option value="expired">Scaduta</option>
            </select>
        </td>
        <td class="width35 text_left" colspan=4></td>
    </tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Blocco coazione</font></td>
		<td class="width40 text_left" colspan=4>
			<select name="blocco" tabindex=19 class="width100">
				<option>No</option>
				<option>Si</option>
				<option>Entrambi</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Ordinamento</font></td>
		<td class="width40 text_center" colspan=4>
			<select name=ordinamento class="width100" tabindex=10>
				<option value=progressivo>Partita</option>
				<option value=info>Informazioni cartella</option>
				<option value=alfabetico>Alfabetico</option>
				<option value=verbale>Numero verbale ( solo CDS )</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
	<tr>
		<td colspan=9><hr></td>
	</tr>
</table>
		
	<br>
	
</form>

</td>
</tr>
</table>

</body>
</html>