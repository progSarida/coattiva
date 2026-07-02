<?php

require $_SERVER['DOCUMENT_ROOT'] . "/gitco2/_path.php";
include_once ROOT."/_parameter.php";

include(INC."/html_header.php");
include CLS."/cls_ente.php";

$_SESSION['aut_tipo'];
$_SESSION['CC_User'];

$cls_ente = new cls_ente();
$cls_db = new cls_db();
$cls_html = new cls_html();

$a_cities = $cls_db->getResults($cls_db->SelectQuery($cls_ente->getCityList_query($_SESSION['aut_tipo'],$_SESSION['CC_User'])));
$citiesOpt = $cls_ente->getCityList_options($a_cities);

$options_comuni = "Seleziona Ente:<br>".$citiesOpt;

$layout = "<script>";
$layout.= "$('select:first').focus();";

if($c==null)
	$options_anni = null;
else {
	$layout.="$('#select_comune option[value=".$c."]').attr('selected',true);";

    $a_cityYear = $cls_db->getResults($cls_db->SelectQuery($cls_ente->getCityYearList_query($c)));
    $yearsOpt = $cls_ente->getCityYearList_options($a_cityYear);
	$options_anni = "Seleziona Anno:<br>".$yearsOpt;
	
	if($a!=null)
		$layout.="$('#select_anno option[value=".$a."]').attr('selected',true);";
}
  
$layout.= "</script>";

?>

 <script>
  	$("*").on( "change" , "input, textarea, select" , function( event ) {

  		var elem = $( this );
  		elem.addClass( "sfondo_giallo", ":change" );

  		});

  		$("*").on( "focus blur","input, textarea, select", function( event ) {
  		var elem = $( this );

  		elem.toggleClass( "focused", elem.is( ":focus" ) );

  		});
  	</script>
  	
<script>
    function changeCity(){
        c = $("#select_comune").val();
        a = $("#select_anno").val();
        strLink = "scelta_CC_e_anno.php?";
        strLink += "c=" + c;
        strLink += "&a=" + a;
        location.href = strLink;
    }

    function goHome(){
        c = $("#select_comune").val();
        a = $("#select_anno").val();
        strLink = "home.php?";
        strLink += "c=" + c;
        strLink += "&a=" + a;

        if(a.length!=4)
        {
            alert("Selezionare l'anno!");
        }
        else if(a.length==4)
        {
            location.href = strLink;
        }
    }

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

<table class="table_interna">
	<tr class="text_center">
		<td class="width23 pheight75">
			<a href="#" onMouseover="title='Help'" onClick="javascript:window.open('/gitco2/help/selezione_comuni_anni.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes')">
			<img src="/gitco2/immagini/Help Blue.png" width="65" height="65" border="0"></a>
		</td>
		<td class="text_center pheight75">
			<font class="titolo font22 under_decor">Selezione Ente/Anno</font>
		</td>
		<td class="width23 pheight75">
			<a onMouseover="title='Home Page Gitco'" href="home.php?c=<?php echo $c?>&a=<?php echo $a?>" target="_top">
			<img src="/gitco2/immagini/home2.png" width="60" height="60" border="0" alt="Torna al menu generale"></a>
		</td>
	</tr>
	<tr class="text_center">
		<td class="pheight100" colspan=3>
		<?php echo $options_comuni; ?>
		</td>
	</tr>
	<tr class="text_center" >
		<td class="pheight100" colspan=3>
		<?php echo $options_anni;	?>
		</td>
	</tr>
	<tr class="text_center">
		<td class="pheight100" colspan=3>

<?php if($c != null ){?> 
			
		<input class="sfondo_azzurro_button " type="button" tabindex=3 onblur="lastTab();" value="Conferma" onclick="goHome();">
				
<?php  }?>
			
		</td>
	</tr>
	<tr  class="text_center valign_bottom">
		<td colspan=3><br>

			<p id=verifica_full></p>

		</td>
	</tr>

</table>

<?php echo $layout; ?>
		
<?php include INC."/footer.php";