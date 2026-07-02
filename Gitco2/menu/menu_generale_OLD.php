<script>
    var stringaMODE = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    var modifica = 0;
    var blocca_menu = 0;
    var blocca_modifica = 0;

    window.showModalDialog = function (arg1, arg2, arg3) {

        var w;
        var h;
        var resizable = "yes";
        var scroll = "yes";
        var status = "no";

        // get the modal specs
        var mdattrs = arg3.split(";");
        for (i = 0; i < mdattrs.length; i++) {
            var mdattr = mdattrs[i].split(":");

            var n = mdattr[0];
            var v = mdattr[1];
            if (n) {
                n = n.toLowerCase();
            }
            if (v) {
                v = v.toLowerCase();
            }

            if (n == "resizable") {
                resizable = v;
            } else if (n == "scroll") {
                scroll = v;
            } else if (n == "status") {
                status = v;
            }
        }

        var w = 700;
        var h = 500;
        var left = 100;
        var top = 100;
        var targetWin = window.open(arg1, '_blank', 'toolbar=no, location=no, directories=no, status=' + status + ', menubar=no, scrollbars=' + scroll + ', resizable=' + resizable + ', copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
        targetWin.focus();
    };

</script>

<?php include_once 'gestione_pagine.php'; ?>

<?php include_once INC.'/menu_script.php'; ?>
<script>
    function menuClick(value) {
        if (modifica == 1) {
            alert('salvare i dati o annullare prima di procedere');
        }
        else if (blocca_menu == 0) {

            menulink = menu_script(value);
            top.location.href = menulink;

        }

    }

</script>
<script>
    var fn = function (e) {
        if (!e) {
            e = window.event;
        }

        var keycode = e.keyCode;
        if (e.which)
            keycode = e.which;

        //var src = e.srcElement;
        //if (e.target)
        //src = e.target;


        //CONSULTA F2
        if (113 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            cambia_F2();

            return false;
        }
        //SALVA F3
        else if (114 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            salva_form();

            return false;
        }
        //ELIMINA F4
        else if (115 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            cancella_form();

            return false;
        }
        //ANNULLA F5
        else if (116 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            annulla();

            return false;
        }
        //MODIFICA F6
        else if (117 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            nuovo_F6();

            return false;
        }
        //RECORD PRECEDENTE F7
        else if (118 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            cambia_pag('prev');

            return false;
        }
        //RECORD SUCCESSIVO F8
        else if (119 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            cambia_pag('suc');

            return false;
        }
        //RICERCA F9
        else if (120 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            ricerca_F9();

            return false;

        }

        //STAMPA F10
        else if (121 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            stampa_F10();

            return false;

        }

        //HELP F11
        else if (122 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            if (blocca_menu == 0)
                window.open('/gitco2/help/intestazione.html', 'help', 'width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');

            return false;
        }
        //HOME F12
        else if (123 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            if (blocca_menu == 0)
                link('menu');

            return false;
        }
        //PAGINA GIU PagDown
        else if (34 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            pag_prec();

            return false;
        }
        //PAGINA SU PagUp
        else if (33 == keycode) {
            // Firefox and other non IE browsers
            if (e.preventDefault) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Internet Explorer
            else if (e.keyCode) {
                e.keyCode = 0;
                e.returnValue = false;
                e.cancelBubble = true;
            }

            pag_suc();

            return false;
        }
        //ENTER
        else if (13 == keycode) {


            return true;
        }

    };

    document.onkeydown = fn;

</script>
<script>

    //NUOVO UTENTE E RITORNO AL MENU
    function link(value) {

        switch (value) {
            case "menu":

                stringa = "/gitco2/menu/home.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";

                break;

        }

        if (modifica == 0)
            top.location.href = stringa;
        else
            alert("salvare i dati o annullare prima di procedere");
    }

    function submit_buttons(value) {
        var ritorno = null;

        switch (value) {
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

        if (value == "Delete") {
            if (ritorno) {
                ritorno2 = confirm("Sei sicuro di voler eliminare i dati?");
                if (ritorno2) {
                    return true;
                }
                else {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            if (ritorno) {
                return true;
            }
            else {
                return false;
            }
        }

    }

    function focusCampo() {
        $("#id_cerca").focus();
    }

    function focusIndex() {
        $('[tabindex=1]').focus();
    }
</script>

<?php include_once INC."/menu_div.php"; ?>