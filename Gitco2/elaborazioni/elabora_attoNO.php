<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include(CLS."/cls_registry.php");

$cls_db = new cls_db();

$a_years = $cls_db->getResults($cls_db->SelectQuery("SELECT Anno FROM anni_gestiti WHERE CC_Anno ='".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC"));
$opt_years = "";
for($i=0;$i<count($a_years);$i++)
    $opt_years.= "<option value='".$a_years[$i]['Anno']."'>".$a_years[$i]['Anno']."</option>";

$a_partite = $cls_db->getResults($cls_db->SelectQuery("SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC"));
$serieOption = "";
for($i=0;$i<count($a_partite);$i++)
    $serieOption .= "<option value='" . $a_partite[$i]['Comune_ID'] . "'>" . $a_partite[$i]['Comune_ID'] . "</option>";


$richiesta_singola = $cls_help->getVar('richiesta_singola');
$partita_ID = $cls_help->getVar('partita_ID');
$tipo_atto = $cls_help->getVar("tipo_atto");
$layout = "";
if($richiesta_singola=="si"){
    $partita = get_var('partita');
    $layout = "<script>$('#from_taxRecord').val('".$partita."')</script>";
    $layout.= "<script>$('#to_taxRecord').val('".$partita."')</script>";

    $anno_rif = get_var('anno_rif');

    $layout.= "<script>$('#from_taxYear').val('".$anno_rif."')</script>";
    $layout.= "<script>$('#to_taxYear').val('".$anno_rif."')</script>";

    $cls_registry = new cls_registry();
    $utente = $cls_db->getObjectLine($cls_db->SelectQuery($cls_registry->getRecord_query($p)));

    if($utente->Genere!="D"){
        $layout.= "<script>$('#from_surname').val('".$utente->Cognome."')</script>";
        $layout.= "<script>$('#from_name').val('".$utente->Nome."')</script>";
        $layout.= "<script>$('#to_surname').val('".$utente->Cognome."')</script>";
        $layout.= "<script>$('#to_name').val('".$utente->Nome."')</script>";
    }
    else{
        $layout.= "<script>$('#from_surname').val('".$utente->Ditta."')</script>";
        $layout.= "<script>$('#to_surname').val('".$utente->Ditta."')</script>";
    }

}
else{
    $layout.= "<script>$('#ritorno_ruolo').hide()</script>";
}

switch($tipo_atto)
{
    case "Ingiunzione":
        $disabled = " disabled ";
        $data_calcolo_visual = 	$data_calcolo;
        $action_page = "elaborazione_ingiunzioni.php";
        $testo_validazione.= "- non vi sono atti precedenti;<br>";
        $testo_visual = $testo_validazione;
        if($richiesta_singola=="si")
            $html_sollecito = "";
        else
            $html_sollecito = "<input type=checkbox name=prima_ingiunzione value=si tabindex=3 checked> Elabora solo se non sono ancora uscite Ingiunzioni";

        break;

    case "avv_intimazione":	$tipo_atto = "Avviso di intimazione ad adempiere";
        $disable_avviso = " disabled ";

        $testo_avviso = "Per elaborare gli avvisi e' necessario che sia passato almeno un anno dalla data di notifica dell'ingiunzione";
        $testo_visual = $testo_avviso."<br>".$testo_validazione;
        $action_page = "elaborazione_avvisi_intimazione.php";

        break;

    case "sollecito":
        $tipo_atto = "Sollecito di pagamento";
        $disable_avviso = " disabled ";
        $testo_avviso = "Si possono elaborare i solleciti solo dopo la notifica dell'Ingiunzione e se non e' ancora stato emesso un Avviso di intimazione ad adempiere";
        $testo_visual = $testo_avviso;
        $action_page = "elaborazione_solleciti_ingiunzione.php";
        $html_sollecito = "<input type=checkbox name=primo_sollecito value=si tabindex=3> Elabora solo se non sono ancora usciti Solleciti";

        break;

    case "preavviso":
        $tipo_atto = "Sollecito di pagamento pre ingiunzione";
        $disable_avviso = " disabled ";
        $testo_avviso = "Si possono elaborare i solleciti di pagamento pre ingiunzione solo se non sono ancora stati elaborati atti successivi";
        $testo_visual = $testo_avviso;
        $action_page = "elaborazione_solleciti_pre_ingiunzione.php";
        $html_sollecito = "<input type=checkbox name=primo_sollecito value=si tabindex=3> Elabora solo se non sono ancora usciti Solleciti";

        break;
}
?>

<script>

    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="elabora_atto.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F5
    switchMenuImg("F3");
    F3_button = function(){
        $('#elaborazione_form').submit();
    }

    function Dim_Alert ( sWidth, sHeight ){
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
                    $.post("ajax/ajax_cognome.php?c=<?php echo $c; ?>" ,

                        { 'ajax': 'nome' ,
                            'ID': valorediritorno },

                        function (value) {

                            var array_ritorno = value.split('*');

                            if(selectRif==1)
                            {
                                $('#from_surname').val(array_ritorno[0]);
                                $('#to_surname').val(array_ritorno[0]);
                            }
                            else if(selectRif==2)
                            {
                                $('#to_surname').val(array_ritorno[0]);
                            }

                            if(array_ritorno.length == 2)
                            {
                                if(selectRif==1)
                                {
                                    $('#from_name').val(array_ritorno[1]);
                                    $('#to_name').val(array_ritorno[1]);
                                }
                                else if(selectRif==2)
                                {
                                    $('#to_name').val(array_ritorno[1]);
                                }
                            }
                            else
                            {
                                if(selectRif==1)
                                {
                                    $('#from_name').val("");
                                    $('#to_name').val("");
                                }
                                else if(selectRif==2)
                                {
                                    $('#to_name').val("");
                                }
                            }
                        });
                }

                break;
        }

    }

    var selectParent = "";
    var selectRif = "";
    function RicercheDaId (value, rif){
    selectParent = value;
    selectRif = rif;
	var valorediritorno = 0;
	var strDim = Dim_Alert(600, 300);

	switch(value)
	{
		case "utente":

			strDim = Dim_Alert(800, 500);
			var stringa = "<?= WEB_ROOT; ?>/coattiva/modali/ricerca_alert_modale.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			valorediritorno = window.showModalDialog(stringa,"", strDim);

			break;
	}
}

</script>


<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><span class="titolo font16 under_decor">Elaborazione <?php echo $tipo_atto; ?></span></td>
	</tr>
</table>

<form id="elaborazione_form" name="elaborazione_form" action="<?php echo $action_page; ?>" method="post" target="elaborazione" onSubmit="window.open('', 'elaborazione', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')">

	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">


<table class="table_interna text_center" border="0">
	<tr>
		<td colspan=4 class="text_center"><font class="titolo font16 under_decor">Selezione filtri</font></td>
	</tr>
	<tr>
		<td colspan=4 class="pheight5"><hr></td>
	</tr>
    <tr>
        <td class="width25 text_left"><span class="color_titolo font_bold">Data elaborazione</span></td>
        <td colspan="3" class="text_left">
            <select name=""
        </td>
    </tr>
    <tr>
        <td colspan=4 class="pheight5"><hr></td>
    </tr>
    <tr>
        <td class="width25 text_left">
            <input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',1);">
        </td>
        <td class="width50 text_left">
            <input type="text" id="from_surname" name="from_surname" size=25 >
            <input type="text" id="from_name" name="from_name" size=15>
        </td>
        <td class="width15 text_left">Da partita</td>
        <td class="width10 text_left">
            <select name="from_taxRecord">
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
            <input type="text" id="to_surname" name="to_surname" size=25>
            <input type="text" id="to_name" name="to_name" size=15>
        </td>
        <td class="text_left">a partita</td>
        <td class="text_left">
            <select name="to_taxRecord">
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
            <select name="from_taxYear" class="width90">
                <?php $opt_years; ?>
            </select>
        </td>
        <td class="width10 text_center">ad anno </td>
        <td class="width10 text_left">
            <select name="to_taxYear" class="width90">
                <?php $opt_years; ?>
            </select>
        </td>
        <td class="width35 text_left" colspan=4>
            Tipo Entrata&nbsp;
            <select name=taxType class="width50">
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
        <td colspan=9><hr></td>
    </tr>
</table>

<br>

</form>

<?php include(INC."/footer.php"); ?>