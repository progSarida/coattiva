<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");

if(!isset($_SESSION['username']))
{
    header("Location: ".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

/*include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI. "/parametri.php";*/
//include TCPDF . "/tcpdf.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$richiesta_singola = $cls_help->getVar('richiesta_singola');

$control_mail = "";
$ID_Atto = $cls_help->getVar('ID_Atto');

if($ID_Atto!=null && $richiesta_singola == "si")
{
    $query = "SELECT * FROM atto WHERE ID = ".$ID_Atto." AND CC = '".$c."'";
	$atto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($ID_Atto, $c);
    $query = "SELECT * FROM partita_tributi WHERE ID = '".$atto->Partita_ID."' AND CC = '".$c."'";
	$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");//new partita($atto->Partita_ID, $c);
    $query = "SELECT * FROM utente WHERE ID = '".$partita->Utente_ID."' AND CC_Comune = '".$c."'";
	$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($partita->Utente_ID, $c);
    $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'res'";
	$indirizzo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");//$utente->Residenza;new indirizzo( $progr , 'res' , $c );
	
	$partita_ID = $partita->ID;
	$ID_Utente = $partita->Utente_ID;
	$cognome = $utente->Cognome.$utente->Ditta;
	$nome = $utente->Nome;
	$cronologico = $atto->ID_Cronologico;
	$anno = $atto->Anno_Cronologico;
}
else 
{
	$partita_ID = "";
	$ID_Utente = "";
	$cognome = "";
	$nome = "";
	$cronologico = "";
}

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>
var control_mail = "<?php echo $control_mail; ?>";

//F5
switchMenuImg("F5");
F5_button = function(){
    location.href="archiviazione_atto.php?richiesta_singola=<?= $richiesta_singola; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ID_Atto=<?php echo $ID_Atto; ?>";
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
    }


    $('#archiviazione_form').submit();
}


//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

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

function callParent(valorediritorno)
{
    if(valorediritorno!=null)
    {
        ID_UFFICIO = "";
        COMUNE_UFFICIO = "";
        INDIRIZZO_UFFICIO = "";

        $('#ID_Atto').val(valorediritorno.ID);
        $('#cronologico').val(valorediritorno.Crono);
        $('#anno').val(valorediritorno.Anno);
        $('#ID_Utente').val(valorediritorno.Utente);

        $.post("<?= WEB_ROOT; ?>/search/stampe/ajax_stampe.php?c=<?php echo $c; ?>" ,

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
}

function CercaAtto()
{
    var stringa = "<?= WEB_ROOT; ?>/search/stampe/ricerca_alert_modale.php?richiesta=ricCrono&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    openWindowSearch(stringa,{width:800, height:400, left:(($(window).width()/2)-400), top:(($(window).height()/2)-200)});
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


<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <p class="titolo font16 under_decor">Gestione archiviazione atti</p>
    </div>
</div>
	
<form id="archiviazione_form" name="archiviazione_form" action="stampa_archiviazione_atto.php" method="post" target="stampa" onSubmit="window.open('', 'stampa', 'width=800,height=500,top=70,left=70,scrollbars=yes,menubar=no')">
		
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">
	<input type=hidden name="ID_Atto" id="ID_Atto" value="<?php echo $ID_Atto; ?>">
	<input type=hidden name="ID_Utente" id="ID_Utente" value="<?php echo $ID_Utente; ?>">

    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di stampa</label>
                <div class="col-lg-8">
                    <select class="form-control resize" name="stampa_select" id="stampa_select" tabindex=2>
                        <option value="PROVVISORIA">Provvisoria</option>
                        <option value="DEFINITIVA">Definitiva</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group">
                <div class="col-lg-12">
                    <input class="btn btn-primary form-control resize" type="button" value="Atto di riferimento" title="Cerca atto relativo alla richiesta" onclick="CercaAtto('');">
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

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 1%;"></div>

    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <p class="titoletto">ATTENZIONE! Selezionando Tipo di stampa 'Definitiva' l'archiviazione sara' salvata nella Corrispondenza in Anagrafe.</p>
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

<script>
    /*$( document ).ready(function() {
        $('#ritorno_ruolo').hide();
    });*/
</script>

<?php include(INC."/footer.php"); ?>