<?php
require $_SERVER['DOCUMENT_ROOT'] . "/_path.php";
include_once ROOT."/_parameter.php";

include(INC."/html_header.php");

$cls_db = new cls_db();
$cls_html = new cls_html();
$queryCities = "SELECT * FROM v_ente_gestito";

$selectCity = null;
$selectYear = $a;
if($_SESSION['CC_User']!="" && $_SESSION['CC_User']!="***+" && $_SESSION['CC_User']!="****"){
    $c = $_SESSION['CC_User'];
    $queryCities = " WHERE CC='".$_SESSION['CC_User']."' ";
}

if($c!=""){
    $queryYears = "SELECT * FROM anni_gestiti WHERE CC_Anno = '".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC";
    $a_years = $cls_db->getResults( $cls_db->SelectQuery($queryYears) );
    $optionsCityYears = $cls_html->optionsFromArray($a_years, "Anno", $selectYear );
    $selectCity = $c;
}
else
    $optionsCityYears = "";

$a_enti = $cls_db->getResults( $cls_db->SelectQuery($queryCities) );
$optionsCities = $cls_html->optionsFromArray($a_enti,"CC",$selectCity, array("Denominazione","CC") );

$layout = "<script>";
$layout.= "$('select:first').focus();";

if($c=="")
    $layout.= "$('#select_years').hide();";
if($c!="" && $a!="")
    $layout.= "$('#confirmButton').prop('disabled',false).addClass('button_azzurro');";


$layout.= "</script>";

?>


<script>

//F11
var fn = function (e)
{
	if (!e)
	{
    	e = window.event;
	}

    var keycode = e.keyCode;
    if (e.which)
        keycode = e.which;

	if (122 == keycode)
   		controlScreen();

}

document.onkeyup = fn;


$(document).ready(function(){

	controlScreen();

});

function controlScreen()
{
	if (!window.screenTop && !window.screenY) {
	    //full web browser
	    $('#verifica_full').text('');
	}
	else
	{
		$('#verifica_full').text("Se si desidera la visualizzazione a schermo intero e' necessario selezionarla prima di accedere alle pagine del programma cliccando F11. Successivamente questa funzione sara' disabilitata a favore dello strumento di aiuto per l'utente (HELP)");
	}
}

function lastTab()
{
	$('select:first').focus();
}
</script>
    <table class="table_interna text_center">

        <tr class="text_center">
            <td class="width23 pheight75">

            </td>
            <td class="text_center pheight75">
                <font class="titolo font22 under_decor">Selezione Ente/Anno</font>
            </td>
            <td class="width23 pheight75">

            </td>
        </tr>

        <tr class="text_center">
            <td class="pheight100" colspan=3>
                <select id='select_cities' onchange="changeAdminCity();">
                    <option></option>
                    <?php echo $optionsCities; ?>
                </select>
            </td>
        </tr>
        <tr class="text_center" >
            <td class="pheight100" colspan=3>
                <select id='select_years' onchange="changeAdminYear();">
                    <option></option>
                    <?php echo $optionsCityYears; ?>
                </select>
            </td>
        </tr>
        <tr class="text_center" >
            <td class="pheight100" colspan=3>
                <input type="button" id="confirmButton" value="CONFERMA" onclick="openLocation('home');" disabled>
            </td>
        </tr>
    </table>

<?php echo $layout; ?>
<?php include(INC."/footer.php"); ?>