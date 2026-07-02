<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>GITCO</title>

	<link rel=StyleSheet href="<?= CSS; ?>classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="<?= CSS; ?>jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>

	<script type="text/javascript" language="javascript" src="<?= JS ?>JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="<?= JS ?>form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="<?= JS ?>funzioni.js" ></script>
  	<script type="text/javascript" language="javascript" src="<?= JS ?>CapsLock.js" ></script>

    <script src="<?= JS ?>/bootstrap.js" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?= JS ?>jquery-ui.js" ></script>
  	<script type="text/javascript" language="javascript" src="<?= JS ?>datepicker.js" ></script>
    <script src="https://cdn.ckeditor.com/4.11.1/full-all/ckeditor.js"></script>
  	<script>
$("*").on( "change" , "input, textarea, select" , function( event ) {

    var elem = $( this );
    elem.addClass( "sfondo_giallo", ":change" );

});

  		$("*").on( "focus blur","input, textarea, select", function( event ) {
            var elem = $( this );

            elem.toggleClass( "focused", elem.is( ":focus" ) );

        });

  		$("*").on( "focus","input, textarea", function( event ) {
            var elem = $( this );

            elem.select();

        });
  	</script>

  	<script language="Javascript">
var control_press = false;

function capLock(e){

    control_press = true;

    if (!e)
    {
        e = window.event;
    }

    var keycode = e.keyCode;
    if (e.which)
        keycode = e.which;

    var sk = e.shiftKey?e.shiftKey:((keycode == 16)?true:false);


    if(((keycode >= 65 && keycode <= 90) && !sk)||((keycode >= 97 && keycode <= 122) && sk))
    {
        $('#lock_image').show();
        $(document).data("capsOn", true);
    }
    else if(keycode != 8)
    {
        $('#lock_image').hide();
        $(document).data("capsOn", false);
    }

}
</script>

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
    {
        controlScreen();
    }
    else if(20 == keycode && control_press == true)
    {
        if (!$(document).data("capsOn")) {
            $(document).data("capsOn", true);
        } else {
            $(document).data("capsOn", false);
        }

        if ($(document).data("capsOn")) {
            $('#lock_image').show();
        }
        else
        {
            $('#lock_image').hide();
        }

    }


}
document.onkeyup = fn;

$(document).ready(function(){

    $('#lock_image').hide();
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
        $('#verifica_full').text("Se si desidera la visualizzazione a schermo intero � necessario selezionarla prima di accedere alle pagine del programma cliccando F11. Successivamente questa funzione sara' disabilitata a favore dello strumento di aiuto per l'utente (HELP)");
    }
}

function lastTab()
{
    $('[name=user]').focus();
}

$(document).ready(function(){


    $("#pass_click").click(function salva_form() {

        top.location.href = "/gitco2/autenticazione/index_pass.php";
        return false;

    });

    $("#mail_click").click(function cancella_form() {

        top.location.href = "/gitco2/autenticazione/index_mail.php";
        return false;

    });

});


</script>
</head>
