<script>
var stringaMODE = "&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

	$("*").on( "change" , "input, textarea, select" , function( event ) {

	var elem = $( this );
	elem.addClass( "sfondo_giallo", ":change" );

	});

	$("*").on( "focus blur","input, textarea, select", function( event ) {
	var elem = $( this );

	elem.toggleClass( "focused", elem.is( ":focus" ) );

	});


function menuClick (value)
{
    menulink = menu_script(value);
    top.location.href = menulink;

}

//NUOVO UTENTE E RITORNO AL MENU
function link(value)
{

	switch(value)
	{
		case "menu":

			stringa = "/gitco2/menu/home.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				
		break;
				
	}
			
	top.location.href = stringa;

}


</script>

<?php include_once INC.'/menu_script.php'; ?>

<script>

function focusCampo()
{
	$("#id_cerca").focus();
}

</script>

<?php include_once INC."/menu_div.php"; ?>