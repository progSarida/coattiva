<?php
include("../_path.php");
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

$listType = $cls_help->getVar('listType');
switch($listType){
    case "esiti":
        $a_list = array("title"=>"Gestione esiti","action"=>"elenco_esiti.php");
        break;
    case "dettaglio_partita":
        $a_list = array("title"=>"Elenco dettaglio partita","action"=>"list_dettaglioPartita.php");
        break;
}

$cls_db = new cls_db();

$a_years = $cls_db->getResults($cls_db->SelectQuery("SELECT Anno FROM anni_gestiti WHERE CC_Anno ='".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC"));
$opt_years = "";
for($i=0;$i<count($a_years);$i++)
    $opt_years.= "<option value='".$a_years[$i]['Anno']."'>".$a_years[$i]['Anno']."</option>";

$a_partite = $cls_db->getResults($cls_db->SelectQuery("SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC"));
$serieOption = "";
for($i=0;$i<count($a_partite);$i++)
    $serieOption .= "<option value='" . $a_partite[$i]['Comune_ID'] . "'>" . $a_partite[$i]['Comune_ID'] . "</option>";

if($_SESSION['CC_User']=="****" || $_SESSION['CC_User']=="***+")
    $citySelect = "<select name='city' class='width55'><option value='".$c."'>".$adminCity."</option><option value=''>Tutti</optionvalue></select>";
else
    $citySelect = "<select name='city' class='width55' disabled><option value='".$c."'>".$adminCity."</option></select>";

?>

<script>

    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="gestione_elenchi.php?listType=<?php echo $listType; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F5
    switchMenuImg("F10");
    F10_button = function(){
        $('#elenco_form').submit();
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
			var stringa = "/gitco2/coattiva/modali/ricerca_alert_modale.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			valorediritorno = window.showModalDialog(stringa,"", strDim);
			
			break;
	}
}

</script>


<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><span class="titolo font16 under_decor"><?php echo $a_list['title']; ?></span></td>
	</tr>
</table>
	
<form id="elenco_form" name="elenco_form" action="<?php echo $a_list['action']; ?>" method="post" target="elenco" onSubmit="window.open('', 'elenco', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')">

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
        <td class="width25 text_left"><font class="color_titolo font_bold">Tipo di elenco</font></td>
        <td colspan="3" class="text_left">
            <select class="width55" name="fileType" id=fileType>
                <option value="pdf">PDF</option>
                <option value="excel">EXCEL</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="width25 text_left"><font class="color_titolo font_bold">Ente</font></td>
        <td colspan="3" class="text_left"><?php echo $citySelect; ?></td>
    </tr>
    <tr>
        <td colspan=4 class="pheight5"><hr></td>
    </tr>
	<tr>
		<td class="width25 text_left">
			<input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',1);">
		</td>
		<td class="width50 text_left">
			<input type="text" id="daco" name="from_surname" size=25 >
			<input type="text" id="dano" name="from_name" size=15>
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
			<input type="text" id="acog" name="to_surname" size=25>
			<input type="text" id="anom" name="to_name" size=15>
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
        <td class="width10 text_left"><input name="from_taxYear" type="text" value="<?php echo $a; ?>" class="width90"></td>
		<td class="width10 text_center">ad anno </td>
        <td class="width10 text_left"><input name="to_taxYear" type="text" value="<?php echo $a; ?>" class="width90"></td>
		<td class="width35 text_left" colspan=4>
			Tipo Entrata&nbsp;
			<select name=taxType class="width50">
				<option value=''>Tutte</option>
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
		<td class="text_left width25"><font class="color_titolo font_bold">Blocco coazione</font></td>
		<td class="width40 text_left" colspan=4>
			<select name="taxStopFlag" class="width100">
				<option value="no">No</option>
				<option value="si">Si</option>
				<option value="">Entrambi</option>
			</select>
		</td>
		<td class="width35 text_left" colspan=4></td>
	</tr>
    <tr>
        <td class="text_left width25"><font class="color_titolo font_bold">Notifiche</font></td>
        <td class="width40 text_left" colspan=4>
            <select name="notification" class="width100">
                <option value="y">Presenti</option>
                <option value="n">Mancanti</option>
                <option value="">Tutte</option>
            </select>
        </td>
        <td class="width35 text_left" colspan=4></td>
    </tr>
	<tr>
		<td class="text_left width25"><font class="color_titolo font_bold">Ordinamento</font></td>
		<td class="width40 text_center" colspan=4>
			<select name="sort" class="width100" >
				<option value="partita">Partita</option>
				<option value="utente">Alfabetico</option>
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

<?php include(INC."/footer.php"); ?>