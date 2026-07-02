<?php

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI. "/parametri.php";
include TCPDF . "/tcpdf.php";*/

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once(CLS."/cls_GestionePartita.php");

$cls_partita = new cls_GP();

if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$richiesta_singola = $cls_help->getVar('richiesta_singola');

$layout = "";

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");

$query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
$comune->Info = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");


$dati_ente = $comune->Info;
$DE_ID_ufficio = $dati_ente->ID;
$DE_Comune_ufficio = $dati_ente->Comune;
$DE_tipo_ufficio = "Ufficio SIATEL";
$DE_toponimo_ufficio = $dati_ente->Toponimo;
$DE_civico_ufficio = $dati_ente->Civico;
$DE_esponente_ufficio = $dati_ente->Esponente;
$DE_indirizzo_ufficio = $DE_toponimo_ufficio." ".$DE_civico_ufficio.$DE_esponente_ufficio;

$control_mail = "";
$ID_Atto = $cls_help->getVar('ID_Atto');
if($ID_Atto!=null && $richiesta_singola == "si")
{
    $query = "SELECT * FROM atto WHERE ID = ".$ID_Atto." AND CC = '".$c."'";
	$atto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($ID_Atto, $c);

    $query = "SELECT * FROM partita_tributi WHERE ID = '".$atto->Partita_ID."' AND CC = '".$c."'";
	$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");// new partita($atto->Partita_ID, $c);

    $query = "SELECT * FROM utente WHERE ID = '".$partita->Utente_ID."' AND CC_Comune = '".$c."'";
	$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($partita->Utente_ID, $c);

    $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$partita->Utente_ID."' AND Tipo = 'res'";
    $indirizzo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");//$utente->Residenza;
	
	$partita_ID = $partita->ID;
	$ID_Utente = $partita->Utente_ID;
	$cognome = $utente->Cognome.$utente->Ditta;
	$nome = $utente->Nome;
	$cronologico = $atto->ID_Cronologico;
	$anno = $atto->Anno_Cronologico;
	
	if($utente->Ditta!="")	$tipo_utente = "ditta";
	else
		$tipo_utente = "persona";
	
	if($tipo_utente!="ditta")
	{
		//$ufficio = new ufficio_comune(null);
		$id_uff = explode(" ",$cls_partita->trova_ufficio($indirizzo->CC_Indirizzo, 'uff_anagrafico'));
		
		if($id_uff[0]!="ID")
        {
            $query = "SELECT * FROM ufficio_comune WHERE 1 = 2 ";
            $ufficio = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ufficio_comune");

            $ID_ufficio = $ufficio->ID;
            $Comune_ufficio = null;
            $tipo_ufficio = "";
            $toponimo_ufficio = $ufficio->Toponimo;
            $civico_ufficio = $ufficio->Civico;
            $esponente_ufficio = $ufficio->Esponente;

            $cls_help->alert('Selezionare un ufficio. Sono presenti '.$id_uff[0].' uffici anagrafici registrati per il comune di '.$indirizzo->Comune.'.');
        }
		else 
		{
            $query = "SELECT * FROM ufficio_comune WHERE ID = '" . $id_uff[1] . "' ";
			$ufficio = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ufficio_comune");//new ufficio_comune($id_uff[1]);

            $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$ufficio->CC."'";
			$com_residenza = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"comuni_lista");//new comune($ufficio->CC);
					
			$ID_ufficio = $ufficio->ID;
			$Comune_ufficio = $com_residenza->Com_Nome;
			$tipo_ufficio = "Ufficio anagrafico";
			$toponimo_ufficio = $ufficio->Toponimo;
			$civico_ufficio = $ufficio->Civico;
			$esponente_ufficio = $ufficio->Esponente;

            if($ufficio->Modalita_Invio!="" && $ufficio->Modalita_Invio!="posta")
                $control_mail = "La procedura di invio email potrebbe durare alcuni minuti. Confermare ed attendere il tempo necessario.";
		}
	}
	else 
	{			
		$ID_ufficio = $DE_ID_ufficio;
		$Comune_ufficio = $DE_Comune_ufficio;
		$tipo_ufficio = "Ufficio SIATEL";
		$toponimo_ufficio = $DE_toponimo_ufficio;
		$civico_ufficio = $DE_civico_ufficio;
		$esponente_ufficio = $DE_esponente_ufficio;
	}
	
	$indirizzo_ufficio = $toponimo_ufficio." ".$civico_ufficio.$esponente_ufficio;
	
	$layout = "<script>";
	$layout.= "$('#ID_Ufficio').val('".$ID_ufficio."');";
	$layout.= "$('#comune_id').val(\"".$Comune_ufficio."\");";
	$layout.= "$('#font_ufficio').text(\"".$tipo_ufficio."\");";
	$layout.= "$('#indirizzo_id').val(\"".$indirizzo_ufficio."\");";
	$layout.= "</script>";
}
else 
{
	$partita_ID = "";
	$ID_Utente = "";
	$cognome = "";
	$nome = "";
	$cronologico = "";
	$anno = "";
	$ID_ufficio = "";
	$Comune_ufficio = "";
	$tipo_ufficio = "Ufficio anagrafico";
	$toponimo_ufficio = "";
	$civico_ufficio = "";
	$esponente_ufficio = "";
	$indirizzo_ufficio = "";
	$layout.= "<script>$('#ritorno_ruolo').hide()</script>";
}

?>

<title>Richieste validazione notifica</title>

<style> .ui-datepicker { font-size:11px; } </style>


<!-- ********** GESTIONE LINK MENU ********** -->
<script>
var control_mail = "<?php echo $control_mail; ?>";


//F5
switchMenuImg("F5");
F5_button = function(){
    location.href="richiesta_validazione_notifica.php?richiesta_singola=<?= $richiesta_singola; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Atto=<?= $ID_Atto; ?>";
}

//F10
switchMenuImg("F10");
F10_button = function(){
    if($('#stampa_select').val()=="Definitiva"){

        if($('#cronologico').val()=="0" || $('#cronologico').val()==""){
            alert("Cronologico assente!");
            return false;
        }

        if($('#anno').val()=="0" || $('#anno').val()==""){
            alert("Anno cronologico assente!");
            return false;
        }

        if(control_mail!="")
            alert(control_mail);
    }

    if($('#indirizzo_id').val()==""){

        var r = confirm("Attenzione! L'ufficio non e' stato inserito. La stampa verra' effettuata con indirizzo destinatario manuale. Vuoi continuare?");
        if (!r) {

            return false;

        }
    }

    ajaxCall();
    //$('#richiesta_validazione_form').submit();
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\


</script>


<!-- ********** AJAX / MODALI ********** -->
<!-- Inclusione modale per ricerca -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>
<?php include_once(ROOT . "/search_modal/offcanvas/registry_offcanvas.php"); ?>
<script>
//Modali offcavnvas
function openOfcanvas(type,rif){// Reset campi input
    $('.user_entry').val("");

    // Reset spazi tabella
    $('#appendTableUserEntry').empty();

    selectRif = rif;
    switch (type){
        case 'registry':
            // Reset campi input
            $('#registry_n').val("");
            // Reset spazi tabella
            $('#appendTableRegistry').empty();
            // Apertura modale
            $('#registrySearchModal').modal('show');
            break;
        case 'user_entry':
            // Setta stato checkbox iniziale
            document.getElementById('check_u_n').checked = false;
            document.getElementById('check_u_c').checked = false;
            document.getElementById('check_e_cA').checked = true;
            document.getElementById('check_e_cP').checked = false;
            document.getElementById('check_e_i').checked = false;
            //Nascondo checkbox
            $("#checkbox_c").hide();
            // Setta titolo modale iniziale
            $("#userEntrySearchModalLabel_u").hide();
            $("#userEntrySearchModalLabel_e").show();
            // Setta campo input iniziale
            $("#ins_u_n").hide();
            $("#ins_u_c").hide();
            $("#ins_e_cA").show();
            $("#e_cA_P_l").hide();                                        // nascondo input protocollo
            $("#e_cA_P").hide();                                        // nascondo input protocollo
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
    switch(type){
        case 'registry':
            $('#ID_Ufficio').val(val['ID']);
            $('#comune_id').val(val['Com_Nome']);

            $('#indirizzo_id').val(val['Ins']);
            break;
        case 'entry':
            //Dati atto
            $('#ID_Atto').val(val['ID_Atto']);
            $('#cronologico').val(val['ID_Crono']);
            $('#anno').val(val['Anno_Crono']);
            //Dati utente
            $('#ID_Utente').val(val['ID_Utente']);
            if(val['Ditta'] != ''){
                if(val['Ditta'] != null){
                    $('#cognome').val(val['Ditta']);
                    $('#nome').val('');
                    $('#tipo_utente').val('ditta');
                }
            }else{
                $('#cognome').val(val['Cognome']);
                $('#nome').val(val['Nome']);
                $('#tipo_utente').val('persona');
            }

            cambio_richiesta();
            break;
        case '':
            break;
    }
}

var ID_UFFICIO = "<?php echo $ID_ufficio; ?>";
var COMUNE_UFFICIO = "<?php echo $Comune_ufficio; ?>";
var INDIRIZZO_UFFICIO = "<?php echo $indirizzo_ufficio; ?>";
var tipo_ufficio = "uff_anagrafico";

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

                $('#ID_Utente').val(valorediritorno.Utente);
                $('#ID_Atto').val('');
                $('#cronologico').val('');
                $('#anno').val('');

                $.post("<?= WEB_ROOT; ?>/ajax/ajax_stampe.php?c=<?php echo $c; ?>" ,

                    { 'ajax': 'nome' ,
                        'ID': valorediritorno },

                     function (value) {

                        var array_ritorno = value.split('*');

                        $('#cognome').val(array_ritorno[0]);

                        if(array_ritorno.length == 2)
                        {
                            $('#tipo_utente').val('persona');
                            $('#nome').val(array_ritorno[1]);
                        }
                        else
                        {
                            $('#tipo_utente').val('ditta');
                            $('#nome').val("");
                        }

                    });
            }

            break;
        case "atto":
            if(valorediritorno!=null)
            {
                ID_UFFICIO = "";
                COMUNE_UFFICIO = "";
                INDIRIZZO_UFFICIO = "";

                $('#ID_Atto').val(valorediritorno.ID);
                $('#cronologico').val(valorediritorno.Crono);
                $('#anno').val(valorediritorno.Anno);
                $('#ID_Utente').val(valorediritorno.Utente);

                //alert($('#ID_Atto').val());
                //alert($('#cronologico').val());
                //alert($('#anno').val());
                //alert($('#ID_Utente').val());

                $.post("<?= WEB_ROOT; ?>/ajax/ajax_stampe.php?c=<?php echo $c; ?>" ,

                    { 'ajax': 'nome' ,
                        'ID': valorediritorno.Utente },
                    function (value) {

                        array_ritorno = value.split('*');

                        $('#cognome').val(array_ritorno[0]);

                        if(array_ritorno.length == 2)
                        {
                            $('#tipo_utente').val('persona');
                            $('#nome').val(array_ritorno[1]);
                        }
                        else
                        {
                            $('#tipo_utente').val('ditta');
                            $('#nome').val("");
                        }

                        cambio_richiesta();

                    }

                );
            }
            break;

        case "comune":
            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                $('#ID_Ufficio').val(valorediritorno.ID);
                $('#comune_id').val(valorediritorno.nome_CC);

                $('#indirizzo_id').val(valorediritorno.toponimo+" "+valorediritorno.civico+valorediritorno.esponente);

            }
            else
            {
                $('#ID_Ufficio').val('');
                $('#comune_id').val('');
                $('#indirizzo_id').val('');
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
	/*var valorediritorno = 0;
	var strDim = Dim_Alert(600, 300);*/
	
	switch(value)
	{
		case "utente":

            var DimensionPos = {
                width: 800,
                height: 500,
                left: (screen.width/2-400),
                top: (screen.height/2-250)
            };
			//strDim = Dim_Alert(800, 500);
			var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			//valorediritorno = window.showModalDialog(stringa,"", strDim);
            openWindowSearch(stringa,DimensionPos);
			
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

function CercaAtto()
{
    selectParent = "atto";
	//strDim = Dim_Alert(800, 400);
    var DimensionPos = {
        width: 800,
        height: 500,
        left: (screen.width/2-400),
        top: (screen.height/2-250)
    };
	var stringa = "<?= WEB_ROOT; ?>/search/stampe/ricerca_alert_modale.php?richiesta=ricCrono&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	//valorediritorno = window.showModalDialog(stringa,"", strDim);
    openWindowSearch(stringa,DimensionPos);
}

function cerca_comune()
{
    selectParent = "comune";
    //alert(tipo_ufficio+" --- "+$('#tipo_utente').val());
	if( tipo_ufficio != "uff_postale" && $('#tipo_utente').val()=="ditta") {
        alert("L'ufficio è "+tipo_ufficio+" e l'utente è una ditta per cui la ricerca non può essere abilitata!");
        return;
    }

		
	//strDim = Dim_Alert(800, 500);
    var DimensionPos = {
        width: 800,
        height: 500,
        left: (screen.width/2-400),
        top: (screen.height/2-250)
    };

	var stringa = "<?= WEB_ROOT; ?>/search/parametri/ricerca_alert_modale.php?richiesta=ricUfficio&tipo_ufficio="+tipo_ufficio;
		   				
	//valorediritorno = window.showModalDialog(stringa, "", strDim);
    openWindowSearch(stringa,DimensionPos);
}


function cambio_richiesta()
{
	tipo_richiesta = $('#tipo_richiesta').val();
	if(tipo_richiesta == 'duplicato')
	{
		tipo_ufficio = "uff_postale";
		$('#font_ufficio').text('Ufficio Postale');
		$('#ID_Ufficio').val('');
		$('#comune_id').val('');
		$('#indirizzo_id').val('');
	}
	else if(tipo_richiesta == 'indirizzo')
	{
		tipo_ufficio = "uff_anagrafico";
		
		if($('#tipo_utente').val()=="persona")
		{
			
			$('#font_ufficio').text('Ufficio Anagrafico');

			$('#ID_Ufficio').val(ID_UFFICIO);
			$('#comune_id').val(COMUNE_UFFICIO);
			$('#indirizzo_id').val(INDIRIZZO_UFFICIO);
		}
		else
		{
			$('#font_ufficio').text('Ufficio SIATEL');

			$('#ID_Ufficio').val('<?php echo $DE_ID_ufficio; ?>');
			$('#comune_id').val('<?php echo $DE_Comune_ufficio; ?>');
			$('#indirizzo_id').val('<?php echo $DE_indirizzo_ufficio; ?>');
		}
		
		
	}
	else
	{
		tipo_ufficio = "uff_anagrafico";
		if($('#tipo_utente').val()=="persona")
		{			
			$('#font_ufficio').text('Ufficio Anagrafico');

			$('#ID_Ufficio').val(ID_UFFICIO);
			$('#comune_id').val(COMUNE_UFFICIO);
			$('#indirizzo_id').val(INDIRIZZO_UFFICIO);
		}
		else
		{
			$('#tipo_richiesta').val('indirizzo');
			cambio_richiesta();
		}
		

	}

		
}

var richiesta_singola = "<?php echo $richiesta_singola; ?>";
function ritorno_atto()
{
	if(richiesta_singola=="si")
	{
		link = "<?= WEB_ROOT; ?>/coattiva/ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		location.href= link;
	}
}

</script>


<!-- ********** SUBMIT(stampa) ********** -->
<script>

$(document).ready(function(){
    spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");
	//$("#stampa_click").click( stampa_F10 );
    //if(getParameterByName("file") != null)
            //showFileOnModal(getParameterByName("file"),"Storico Azioni",getParameterByName("file").split('.').pop());
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
                    showFileOnModal(resp.path,"Richiesta validazione notifica",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                spinner.closeSpinner();
                //console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
	}


blocca_modifica = 1;
</script>



<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <p class="titolo font16 under_decor">Gestione richieste relative al perfezionamento della notifica</p>
    </div>
</div>
	
<form id="richiesta_validazione_form" name="richiesta_validazione_form" action="stampa_richiesta_validazione.php" method="post" target="stampa" onSubmit="window.open('', 'stampa', 'width=800,height=500,top=70,left=70,scrollbars=yes,menubar=no')">
		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">
	<input type=hidden name="ID_Atto" id="ID_Atto" value="<?php echo $ID_Atto; ?>">
	<input type=hidden name="ID_Utente" id="ID_Utente" value="<?php echo $ID_Utente; ?>">
	<input type=hidden name="ID_Ufficio" id="ID_Ufficio" value="">
	<input type=hidden name="tipo_utente" id="tipo_utente" value="<?php echo $tipo_utente; ?>">

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di richiesta</label>
                <div class="col-lg-8">
                    <select class="form-control resize" name="tipo_richiesta" id=tipo_richiesta tabindex=1 onchange="cambio_richiesta();">
                        <option value="indirizzo">Indirizzo</option>
                        <option value="decesso">Certificato di decesso</option>
                        <option value="duplicato">Duplicato AR</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di stampa</label>
                <div class="col-lg-8">
                    <select class="form-control resize" name="stampa_select" id="stampa_select" tabindex=2>
                        <option>Provvisoria</option>
                        <option>Definitiva</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-md-center" style="margin-top: 1%;">
        <div class="col col-md-auto text_center">
            <p class="titolo font16 under_decor">Selezione</p>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group">
                <div class="col-lg-12">
                    <input class="btn btn-primary form-control resize" type="button" value="Atto di riferimento" title="Cerca atto relativo alla richiesta" onclick="/*CercaAtto('');*/openOfcanvas('user_entry',0);">
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Cronologico</label>
                <div class="col-lg-8">
                    <input title="Cronologico dell'atto" readonly class="form-control resize" style="background-color: #97CFDD; border: 2px solid black;" type="text" id="cronologico" name="cronologico" value="<?php echo $cronologico; ?>" tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Anno</label>
                <div class="col-lg-8">
                    <input title="Anno dell'atto" readonly class="form-control resize" style="background-color: #97CFDD; border: 2px solid black;" type="text" id="anno" name="anno" value="<?php echo $anno; ?>" tabindex=6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-6 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;"><span class="color_titolo font_bold">Nominativo utente/ditta</span></label>
                <div class="col-lg-8">
                    <input title="Cognome/Denominazione" class="form-control resize" style="background-color: #97CFDD; border: 2px solid black;" type="text" id="cognome" name="cognome" value="<?php echo $cognome; ?>" readonly tabindex=3>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <div class="col-lg-12">
                    <input title="Nome" class="form-control resize" style="background-color: #97CFDD; border: 2px solid black;" type="text" id="nome" name="nome" value="<?php echo $nome; ?>" readonly tabindex=4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-6 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;"><span class="color_titolo font_bold">Ufficio anagrafico</span></label>
                <div class="col-lg-8">
                    <input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" title="Clicca sul campo 'Ufficio' per effettuare la ricerca dell'ufficio di interesse" readonly name=comune id=comune_id value="" ondblclick="/*cerca_comune();*/openOfcanvas('registry',0);">
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;"><span class="color_titolo font_bold">Indirizzo</span></label>
                <div class="col-lg-8">
                    <input class="form-control resize" title="Indirizzo dell'ufficio" style="background-color: #97CFDD; border: 2px solid black;" id=indirizzo_id name=indirizzo readonly value="" >
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 1%;"></div>

    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <p class="titoletto">ATTENZIONE! Selezionando Tipo di stampa 'Definitiva' la richiesta sara' salvata nella Corrispondenza in Anagrafe. Non inserendo l'Ufficio anagrafico/postale il destinatario sara' lasciato in bianco con conseguente compilazione manuale dell'indirizzo di destinazione.</p>
        </div>
    </div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group">
                <div class="col-lg-12">
                    <input id=ritorno_ruolo class="btn btn-primary form-control resize" type="button" value="Torna al Ruolo" title="Torna al Ruolo" onclick="ritorno_atto();" tabindex=17>
                </div>
            </div>
        </div>
    </div>
	
</form>

<?php echo $layout; ?>

<?php include(INC."/footer.php"); ?>