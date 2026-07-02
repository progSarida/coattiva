<?php

include("../_path.php");
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(INC."/submenu_partita.php");

$urlAutoComplete = WEB_ROOT."/ajax/ajaxAutocomplete.php";
$urlSearch = WEB_ROOT."/ajax/ajaxSearch.php";

?>

<script>
    function selectBanche(data){
        $(".banca").remove();
        string = "<form id=sendBanche action=>";
        data.forEach(function(obj) {
            string+=    "\n<div class=\"row banca\" >\n" +
                        "<input type=hidden name=pecBanche[] value='"+ obj.ID +"'>"+
                        "<div class=\"col-sm-4 text_left col-xs-offset-1\"><span class=\"titolo\">" + obj.Denominazione + "</span></div>\n" +
                        "<div class=\"col-sm-4 text_left\">" + obj.PEC + "</div>\n" +
                        "<div class=\"col-sm-4\"></div>\n" +
                        "</div>\n";
        });
        string+= "</form>";
        $('#div_comune').append(string);

    }

    $( function() {
        $( "#searchComuni" ).autocomplete({
            minLength: 2,
            source: function(request, response) {
                $.ajax({
                    url: "<?=$urlAutoComplete?>",
                    dataType: "json",
                    data: {
                        term : request.term,
                        searchType : "comune"
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function( event, ui ) {
                $.ajax({
                    url: "<?=$urlSearch?>",
                    dataType: "json",
                    data: {
                        cc : ui.item.parameter,
                        searchType : "banche"
                    },
                    success: function(data) {
                        selectBanche(data);
                    }
                });
            }
        });
    } );

</script>

<br>
<div id="div_comune" class="container-fluid">

    <div class="row" >
        <div class="col-sm-2 text_left col-xs-offset-1"><span class="titolo">Cerca comune:</span></div>
        <div class="col-sm-3 text_left">
            <div class="ui-widget">
                <input id="searchComuni">
            </div>
        </div>
        <div class="col-sm-7"></div>
    </div>
    <br>
</div>

<?php include(INC."/footer.php"); ?>