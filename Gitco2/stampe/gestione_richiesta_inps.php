<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

//include_once($_SESSION['_path']);
//include_once(ROOT."/_parameter.php");

include(INC . "/header.php");
include(INC . "/menu.php");

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include TCPDF . "/tcpdf.php";*/

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->ExecuteQuery($queryIngiunzioni);
while ($rigaIngiunzioni = mysqli_fetch_assoc($resIngiunzioni))
{
	$serieOption .= "<option value='" . $rigaIngiunzioni['Comune_ID'] . "'>" . $rigaIngiunzioni['Comune_ID'] . "</option>";
}

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

    //F5
    switchMenuImg("F5");
    F5_button = function()
    {
        location.href="gestione_richiesta_inps.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function()
    {
        ajaxCall();
        //$('#documento_form').submit();
    }

    //F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\


</script>


<!-- ********** AJAX / MODALI ********** -->
<!-- Inclusione modale per ricerca utente-->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>
<script>
// Modali offcanvas
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
// Iserimento dati da modale a pagine
function initialId(type,val){
    switch (type){
        case 'user':
        case 'cf':
        case "info":
        case "entry":
        case "fore":
            /*
            $("#genere").val(val['Genere']);                            // setta genere utente (M, F, D)
            if(lock == 'N')                                             // se non è già stato lockato
                lock = val['Genere'];                                   // blocca la ricerca del secondo input sul tipo del primo
             */
            if(selectRif == 1)                                          // "Da Cognome/Nome"
            {
                //alert("qui 1");
                if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
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
                if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
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
                $.post("<?= WEB_ROOT; ?>/search/stampe/ajax_stampe.php?c=<?php echo $c; ?>" ,

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

            //strDim = Dim_Alert(800, 500);
            var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            openWindowSearch(stringa,{width:800, height:400, left:(($(window).width()/2)-400), top:(($(window).height()/2)-200)});
            //valorediritorno = window.showModalDialog(stringa,"", strDim);

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

function insert_anno()
{
	$('#ad_anno').val( $('#da_anno').val() );
}

function primoIndex()
{
	$('[tabindex=1]').focus();
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
                    showFileOnModal(resp.path,"Codici INPS",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //console.log(resp.responseText);
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
		<td><font class="titolo font16 under_decor">Gestione documenti</font></td>
	</tr>
</table>
	
<!-- <form id="documento_form" name="documento_form" action="richiesta_matricole_inps.php" method="post" target="stampa" onSubmit="window.open('', 'stampa', 'width=1000,height=800,top=70,left=70,scrollbars=yes,menubar=no')"> -->
<form id="documento_form" name="documento_form" action="richiesta_matricole_inps.php">
		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">
	
<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=4 class="pheight5"></td>
	</tr>
	<tr>
		<td class="text_left width15">Documento</td>
		<td class="text_left width40">
			<input name=tipo_atto readonly class="text_left sfondo_ricerca" value="Richiesta codici INPS" size=35 tabindex=1>
		</td>
		<td class="width20 text_left">Tipo di stampa</td>
		<td class="width25 text_left">
			<select name="stampa_select" id=stampa_select tabindex=2>
				<option>Provvisoria</option>
				<option>Definitiva</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan=4 class="pheight5"></td>
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
			<input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_entry',1);">
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
			<input class="button_azzurro pwidth150" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_entry',2);">
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
		<td colspan=6><font class="color_titolo font_bold">Anni di riferimento</font></td>
	</tr>
	<tr>
			<td class="width15 text_left"></td>
		<td class="width20 text_center">Da anno</td>
		<td class="width15 text_left"><input type="text" class="text_right" name="da_anno" id="da_anno" value="" onchange="insert_anno();" size=5  tabindex=9></td>
		<td class="width15 text_center">ad anno</td>
		<td class="width20 text_left"><input type="text" class="text_right" name="ad_anno" id="ad_anno" value="" size=5  tabindex=10></td>

		<td class="width15 text_left"></td>
	</tr>
	<tr>
		<td colspan=6 ><hr></td>
	</tr>
</table>

<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=6><font class="color_titolo font_bold">Ordinamento</font></td>
	</tr>
	<tr>
		<td class="width100 text_center" colspan=6>
			<input type=radio name=ordinamento value=progressivo checked> Progressivo
			<input type=radio name=ordinamento value=info> Informazioni cartella			
			<input type=radio name=ordinamento value=alfabetico> Alfabetico
			<input type=radio name=ordinamento value=verbale> Numero verbale ( solo CDS )
		</td>
	</tr>
	<tr>
		<td colspan=6 ><hr></td>
	</tr>
</table>
		
	<br>
	
</form>

<?php include(INC."/footer.php"); ?>