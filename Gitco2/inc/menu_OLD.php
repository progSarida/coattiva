<script>
    var stringaMODE = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    var modifica = 0;
    var blocca_menu = 0;
    var blocca_modifica = 0;

    function openNewWindow(link, dim){
        stringa = 'toolbar=no, location=no, directories=no, menubar=no, copyhistory=no, ';
        stringa+= 'width=' + dim['width'] + ', height=' + dim['height'] + ', top=' + dim['top'] + ', left=' + dim['left'];

        var targetWin = window.open(link,'_blank', stringa);
        targetWin.focus();
    }

    function submit_buttons (value)
    {
        var ritorno = null;

        switch(value)
        {
            case "Insert":
                ritorno = confirm("Si stanno inserendo nuovi dati nel database.\n\nConfermare l'operazione?");
                $('#invia_submit').val('Insert');

                break;
            case "Delete":
                ritorno = confirm("Si stanno eliminando i dati dal database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
                $('#invia_submit').val('Delete');

                break;
            case "Update":
                ritorno = confirm("Si stanno modificando i dati del database.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
                $('#invia_submit').val('Update');

                break;

            case "Salva":
                ritorno = confirm("Si stanno salvando nuovi dati nel database.\n\nConfermare l'operazione?");
                $('#invia_submit').val('Salva');

                break;

            case "Elabora":
                ritorno = confirm("Si stanno elaborando nuovi dati per l'inserimento nel database.\n\nConfermare l'operazione?");
                $('#invia_submit').val('Elabora');

                break;
        }

        if(value=="Delete")
        {
            if(ritorno)
            {
                ritorno2 = confirm("Sei sicuro di voler eliminare i dati?");
                if(ritorno2)
                {return true;}
                else
                {return false;}
            }
            else
            {return false;}
        }
        else
        {
            if(ritorno)
            {return	true;}
            else
            {return	false;}
        }

    }


    //STAMPE partono da 1000
    function menuClick (value) {
        if (modifica == 1) {
            alert('salvare i dati o annullare prima di procedere');
        }
        else if (blocca_menu == 0) {

            menulink = menu_script(value);
            top.location.href = menulink;

        }

    }
</script>

<?php include_once INC.'/menu_script.php'; ?>
<?php include_once INC."/menu_div.php"; ?>

<script>
    function checkModify(){
        if(modifica!=0){
            alert("salvare i dati o annullare prima di procedere");
            return false;
        }
        else
            return true;
    }
    //F2
    function F2_button(){ return false; }
    //F3
    function F3_button(){ return false; }
    //F4
    function F4_button(){ return false; }
    //F5
    function F5_button(){ return false; }
    //F6
    function F6_button(){ return false; }
    //F7
    function F7_button(value){ return false; }
    //F8
    function F8_button(value){ return false; }
    //PAG GIU
    function pagedown_button(){ return false; }
    //PAG SU
    function pageup_button(){ return false; }
    //F9
    function F9_button(){ return false; }
    //F10
    function F10_button(){ return false; }
    //F11
    function F11_button(){ return false; }
    //F12
    function F12_button(){
        top.location.href = "<?php echo SITE; ?>/home.php?&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        return false;
    }

    $("*").on( "change" , "input, textarea, select" , function( event ) {

        var elem = $( this );

        campo_name = elem.attr('name');

        if(campo_name!="ordinamento" && blocca_modifica == 0 && modifica!=1) {
            modifica=1;
        }

        if(elem.hasClass('corrige_numero')) {
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

    var fn = function (e)
    {
        if(!e)  e = window.event;

        if (e.which)    var keycode = e.which;
        else            var keycode = e.keyCode;

        if( (keycode>=113 && keycode<=123) || keycode==33 || keycode==34 ){
            // Firefox and other non IE browsers
            if (e.preventDefault){
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode){
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }
        }

        switch(keycode){
            case 33:    return pageup_button();    break;
            case 34:    return pagedown_button();  break;
            case 113:   return F2_button();         break;
            case 114:   return F3_button();         break;
            case 115:   return F4_button();         break;
            case 116:   return F5_button();         break;
            case 117:   return F6_button();         break;
            case 118:   return F7_button();         break;
            case 119:   return F8_button();         break;
            case 120:   return F9_button();         break;
            case 121:   return F10_button();        break;
            case 122:   return F11_button();        break;
            case 123:   return F12_button();        break;
        }
    };

    document.onkeydown = fn;

    function switchMenuImg(button){
        selector = $('#'+button+'_img');
        var src = selector.attr("src");
        var a_src = src.split("_");
        if(a_src[1]=="on.png")
            src = button+"_off.png";
        else if(a_src[1]=="off.png")
            src = button+"_on.png";

        selector.attr("src","<?php echo IMG; ?>"+"/"+src);
    }
</script>

<table class="table_interna text_center">
    <tr>
        <td class="width7 text_center">
            <a onMouseover="title='Modifica'" href="#" onClick="F2_button();" >
                <img id="F2_img" src="<?php echo IMG; ?>/F2_off.png" width=45 height=45 border=0>
            </a>
        </td>
        <td class="width7 text_center">
            <a onMouseover="title='Salva'" href="#" onClick="F3_button();" style="text-decoration: none;">
                <img id="F3_img" src="<?php echo IMG; ?>/F3_off.png" width=47 height=47 border=0>
            </a>
        </td>
        <td class="width7 text_center">
            <a onMouseover="title='Elimina'" href="#" onClick="F4_button();" style="text-decoration: none;">
                <img id="F4_img" src="<?php echo IMG; ?>/F4_off.png" width=47 height=47 border=0>
            </a>
        </td>
        <td class="width7 text_center">
            <a onMouseover="title='Annulla'" href="#" onClick="F5_button();" style="text-decoration: none;">
                <img id="F5_img" src="<?php echo IMG; ?>/F5_off.png" width=47 height=47 border=0>
            </a>
        </td>
        <td class="width7 text_center">
            <a onMouseover="title='Nuovo Record'" href="#" onClick="F6_button();" style="text-decoration: none;">
                <img id="F6_img" src="<?php echo IMG; ?>/F6_off.png" width=45 height=45 border=0>
            </a>
        </td>
        <td class="width7 text_center">
            <a onMouseover="title='Pagina precedente'" href="#" onclick = "pagedown_button();" style="text-decoration: none;">
                <img id="pagedown_img" src="<?php echo IMG; ?>/pagedown_off.png" width=47 height=47 border=0>
            </a>
        </td>
        <td class="width7 text_center">
            <a onMouseover="title='Pagina successiva'" href="#" onclick = "pageup_button();" style="text-decoration: none;">
                <img id="pageup_img" src="<?php echo IMG; ?>/pageup_off.png" width=47 height=47 border=0>
            </a>
        </td>
        <td class="width7 text_center">
            <a href="#" onMouseover=" title='Record precedente F7' " onclick="F7_button();">
                <img id="F7_img" src="<?php echo IMG; ?>/F7_off.png" width=42px height=42px border="0" alt="Utente precedente">
            </a>
        </td>
        <td class="width7 text_center">
            <a href="#" onMouseover=" title='Record successivo F8' " onclick="F8_button();">
                <img id="F8_img" src="<?php echo IMG; ?>/F8_off.png" width=42px height=42px border="0" alt="Utente successivo">
            </a>
        </td>
        <td class="width11"></td>
        <td class="width7 text_center">
            <a href="#" onMouseover="title='Stampa'" onclick="F10_button();">
                <img id="F10_img" src="<?php echo IMG; ?>/F10_off.png" width=50 height=50 border="0" ></a>
        </td>
        <td class="width3"></td>
        <td class="width7 text_center">
            <a onMouseover="title='Help'" href="#" onClick="F11_button();" style="text-decoration: none;">
                <img id="F11_img" src="<?php echo IMG; ?>/F11_off.png" width=50 height=50 border=0>
            </a>
        </td>
        <td class="width2"></td>
        <td class="width7 text_center">
            <a onMouseover="title='Home'" href="#" onClick="F12_button();" style="text-decoration: none;">
                <img id="F12_img" src="<?php echo IMG; ?>/F12_on.png" width=60 height=50 border=0>
            </a>
        </td>
    </tr>
</table>


<br>
<?php
	$dimensioneAllert = array("x" => 95, "Y" => 4);
	$messaggio = isset($_GET['msg'])? $_GET['msg'] : null;
	$codiceTipoAlert = isset($_GET['error'])? $_GET['error'] : null;
	MostaAlert($messaggio,$codiceTipoAlert,$dimensioneAllert);
?>
