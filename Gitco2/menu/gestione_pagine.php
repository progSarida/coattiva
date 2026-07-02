<script>

	$("*").on( "change" , "input, textarea, select" , function( event ) {

		var elem = $( this );

        campo_name = elem.attr('name');

        if(campo_name!="ordinamento" && blocca_modifica == 0 && modifica!=1) {
            modifica=1;
        }

        if(elem.hasClass( "corrige_numero" )) {
            id_campo = elem.attr('id');
            valore = control_numero(id_campo);
            if(valore===false)
            {
                alert("Inserire un valore numerico.");
                elem.val('');
            }
            else
                elem.val(valore);
        }

        if(blocca_modifica == 0)
            elem.addClass( "sfondo_giallo", ":change" );

	});

    $("*").on( "focus blur","input, textarea",
            function( event ) {   $( this ).toggleClass( "focused", $( this ).is( ":focus" ) );  }
        );
	$("*").on( "focus","input, textarea",
            function( event ) {   $( this ).select(); }
        );

</script>