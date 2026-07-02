$(document).ready(function (){
    // Get the modal
    var modal = document.getElementById("myModalAlert");

    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("closeAlert")[0];

    // When the user clicks the button, open the modal
    /*btn.onclick = function() {
        modal.style.display = "block";
    }*/

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});

function viewModalAlert(style,text){
    $("#myModalAlert").css("display","block");

    switch(style){
        case 1:
            $(".modal-content-alert").css("background-image","linear-gradient(to right, #C4FFC9 , #87DEA0)");
            $(".closeAlert").css("background-color","#97E8A0");
            $(".containerTextAlert").css("color", "#5B8F61");
            $(".containerTextAlert").empty().text(text);
            break;
        case 2:
            $(".modal-content-alert").css("background-image","linear-gradient(to right, #EEFFBA , #DAFF7D)");
            $(".closeAlert").css("background-color","#D1E390");
            $(".containerTextAlert").css("color", "#99A669");
            $(".containerTextAlert").empty().text(text);
            break;
        case 3:
            $(".modal-content-alert").css("background-image","linear-gradient(to right, #FFB1AD , #FF9187)");
            $(".closeAlert").css("background-color","#E68B83");
            $(".containerTextAlert").css("color", "#B3665F");
            $(".containerTextAlert").empty().text(text);
            break;
    }
}