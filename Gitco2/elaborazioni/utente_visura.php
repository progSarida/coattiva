<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}
include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_elaborazioniUtils.php");

$cls_elab = new cls_elaborazioniUtils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$richiesta_singola = $cls_help->getVar('richiesta_singola');

$layout = "";

$ID_Atto = $cls_help->getVar('ID_Atto');
if($ID_Atto!=null && $richiesta_singola == "si")
{
    $query = "SELECT * FROM atto WHERE ID = ".$ID_Atto." AND CC = '".$c."'";
	$atto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($ID_Atto, $c);

    $query = "SELECT * FROM partita_tributi WHERE ID = '".$atto->Partita_ID."' AND CC = '".$c."'";
	$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");//new partita($atto->Partita_ID, $c);

    $query = "SELECT * FROM utente WHERE ID = '".$partita->Utente_ID."' AND CC_Comune = '".$c."'";
	$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($partita->Utente_ID, $c);
	$indirizzo = isset($utente->Residenza)?$utente->Residenza:null;

	$partita_ID = $partita->ID;
	$ID_Utente = $partita->Utente_ID;
	
	$info_utente = $cls_elab->info_utente($utente);
	$informazioni = $info_utente['riga1']."\\n".$info_utente['riga2']."\\n".$info_utente['riga3']."\\n".$info_utente['riga4']."\\n".$info_utente['riga5'];
	
	
	$layout.="<script>$('#informazioni').val(\"".$informazioni."\");</script>";
	$layout.="<script>$('#utente').val(\"".$info_utente['riga1']."\");</script>";
	$layout.="<script>$('#utente_id').val(\"".$ID_Utente."\");</script>";
	
	$cognome = $utente->Cognome.$utente->Ditta;
	$nome = $utente->Nome;
	$tipo_atto = $atto->Atto;
	$cronologico = $atto->ID_Cronologico;
	$anno = $atto->Anno_Cronologico;
	
	$info_atto = strtoupper($tipo_atto." n.".$cronologico." del ".$anno);
	$layout.="<script>$('#atto_rif').val(\"".$info_atto."\");</script>";
	
}
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
    if($('#utente_id').val()!="")
    {
        if(submit_buttons('Elabora'))
            $('#form_visura').submit();
    }
    else
        alert("Nessun utente selezionato!");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
    location.href="utente_visura.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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

function callParent(value) {

    if(value!=undefined && value!=null)
    {
        if(value.ID_Atto!=null)
        {
            $.post("ajax/ajax_cognome.php?c=<?php echo $c; ?>" ,

                { 'ajax': 'info_visura' ,
                    'ID_Partita': value.ID ,
                    'Anno_Partita': value.Anno ,
                    'ID_Atto': value.ID_Atto },

                function (value) {

                    var array_ritorno = value.split('*');

                    info_atto = array_ritorno[0];
                    id_atto = array_ritorno[1];
                    $('#atto_rif').val(array_ritorno[0]);
                    $('#atto_rif_id').val(array_ritorno[1]);

                    informazioni = array_ritorno[2]+"\n"+array_ritorno[3]+"\n"+array_ritorno[4]+"\n"+array_ritorno[5]+"\n"+array_ritorno[6];
                    $('#informazioni').val(informazioni);
                    $('#utente').val(array_ritorno[2]);
                    $('#utente_id').val(array_ritorno[7]);
                });
        }
    }
}

</script>

<!-- ********** MODALI ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

	return setupPagina;
}

function RicercheDaId (value, rif)
{
	var valorediritorno = 0;
	var strDim = Dim_Alert(600, 300);
	
	switch(value)
	{
		case "utente":

			strDim = Dim_Alert(800, 400);
			var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=ricCrono&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			window.showModalDialog(stringa,"", strDim);
			
			break;
	}
}

</script>

<!-- ********** SUBMIT ********** -->
<script>

$(document).ready(function(){

$("#submit_click").click( salva_form );

});

</script>


    <div class="row justify-content-md-center " style="margin-bottom: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Visura motorizzazione</span>
        </div>
    </div>

<form id="form_visura" name="form_visura" action="visura.php" method="post" target="visura" onSubmit="window.open('', 'visura', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')">
	
	<input name=invia_submit id=invia_submit type=hidden value="" >
	
	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">	
	<input type=hidden id=utente_id name="utente_id" value="">
	<input type=hidden id=atto_rif_id name="atto_rif_id" value="">

    <div class="row" style="margin-top: 3%;">
        <div class="col col-lg-8 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Atto di riferimento</label>
                <div class="col-lg-8">
                    <input type="text" id="atto_rif" name="atto_rif" class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly onclick="RicercheDaId('utente',0)">
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-8 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Utente</label>
                <div class="col-lg-8">
                    <input type="text" id="utente" name="utente" class="form-control resize"  style="background-color: #97CFDD; border: 2px solid black;" readonly >
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 2%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <textarea id=informazioni class="form-control resize" style="max-width: 100%; background-color: #97CFDD; border: 2px solid black;" rows=6% readonly></textarea>
            </div>
        </div>
    </div>
</form>

<script>focusIndex();</script>
<?php echo $layout; ?>

<?php include(INC."/footer.php"); ?>