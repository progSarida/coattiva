$(document).ready(function() {

    /** js per far funzionare le drop multiple della navbar **/
    $(".dropdown").on("hide.bs.dropdown", function() {
        $(this).find('.dropdown-toggle span').removeClass("caret-up");
    });

    $(".dropdown").on("show.bs.dropdown", function() {
        $(this).find('.dropdown-toggle span').addClass('caret-up');
    });

    $('.dropdown-submenu .submenu').on("click", function(e) {

        $(this).next('ul').toggle();

        /* check display for caret */
        if ( $(this).next('ul').css('display') == 'none' ) {
            $(this).find('span').removeClass("caret-up");
        }
        else {
            $(this).find('span').addClass("caret-up");
        }
        /* check display for caret || */

        e.stopPropagation();
        e.preventDefault();

    });

});