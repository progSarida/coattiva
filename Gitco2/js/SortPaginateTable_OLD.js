class TableGenerator {
    constructor(fields,toprint,widthColums,fontsize) {
        this.fields = fields;
        this.toprint = toprint;
        this.widthColums = widthColums;
        this.fontSize = fontsize;
        //alert(this.fields[0].Name);
        //console.log(this.fields);
        this.createHtml();
        this.Initial();
    }

    createHtml(){
        var html = '<div class="row" style="margin-top: 2%;">';
        html +=     '<div class="col-lg-2 col-lg-offset-1">'
        html +=     '<h4>Num. righe</h4>';
        html +=    '</div>';
        html += '<div class="col-lg-3">';
        html +=    '<div class="form-group">';
        html +=         '<select name="maxRowsForPaginateTable" id="maxRowsForPaginateTable" class="form-control" style="width: 150px;">';
        html +=             '<option value="50000" >Show all</option>';
        html +=             '<option value="5" selected>5</option>';
        html +=             '<option value="10" >10</option>';
        html +=             '<option value="15" >15</option>';
        html +=             '<option value="20" >20</option>';
        html +=             '<option value="50" >50</option>';
        html +=             '<option value="75" >75</option>';
        html +=             '<option value="100" >100</option>';
        html +=         '</select>';
        html +=        '</div>';
        html +=     '</div>';
        html += '</div>';

        html += '<div class="row">';
        html += '<div class="col-lg-10 col-lg-offset-1">';//da aggiungere sti due div di chiusura
        html += '<table id="mytableForPaginateTable" class="table table-bordered table-hover">';

        if(this.widthColums != undefined && this.fields[0] != undefined){
            //console.log(this.widthColums);
            html += '<colgroup>';

            for(var z=0; z < this.widthColums.length; z++)
                html += '<col style="width: '+this.widthColums[z]+';" />'

            html += '</colgroup>';
        }
        html +=     '<thead>';
        html +=         '<tr>';

        var allFields = this.fields[0];
        this.toPrintDef = [];
        var countElement = 0;


            //console.log( this.fields[0] ); // Outputs: foo, fiz or fiz, foo
            //const test = property;
        if(allFields != undefined) {
            if (this.toprint != undefined) {
                for (var i = 0; i < this.toprint.length; i++)
                    for (var property in allFields) {
                        //console.log(typeof(this.toprint[i]));
                        if (typeof (this.toprint[i]) == "string") {
                            if (property == this.toprint[i]) {
                                this.toPrintDef.push(property);
                                html += '<th style="cursor: pointer;" onclick="sortTable(\'' + countElement + '\');"><div style="float: left;">' + property + '</div> <div style="float: right;"><i id="caret_' + countElement + '" class="allCaret" aria-hidden="true"></i></div></th>';
                                countElement++;
                                break;
                            }
                        } else if (typeof (this.toprint[i]) == "object") {
                            if (property == this.toprint[i].originalName) {
                                this.toPrintDef.push(property);
                                html += '<th style="cursor: pointer;" onclick="sortTable(\'' + countElement + '\');"><div style="float: left;">' + this.toprint[i].replacedName + '</div> <div style="float: right;"><i id="caret_' + countElement + '" class="allCaret" aria-hidden="true"></i></div></th>';
                                countElement++;
                                break;
                            }
                        }
                    }
            } else {
                for (var property in allFields) {
                    this.toPrintDef.push(property);
                    html += '<th onclick="sortTable(\'' + countElement + '\');"><div style="float: left;">' + property + '</div> <div style="float: right;"><i id="caret_' + countElement + '" class="allCaret" aria-hidden="true"></i></div></th>';
                    countElement++;
                }
            }
        }
        else{
            html += '<th><b>Info</b></th>';
        }
            //alert(this.fields[0][property]);
        //}
        console.log( this.fields.length );

        html += '</tr></thead><tbody id="myTbodyForPaginateTable">';

        if(allFields != undefined) {
            for (var x = 0; x < this.fields.length; x++) {
                html += "<tr style='font-size: " + this.fontSize + ";'>";
                for (var y = 0; y < this.toPrintDef.length; y++) {
                    var element = this.fields[x][this.toPrintDef[y]];
                    if (element == null) element = "";
                    html += "<td style='word-break: break-all;'>" + element + "</td>";
                }
                html += "</tr>";
            }
        }
        else {
            html += "<tr>";
            html += '<td><div style="text-align: center;color: #0a53be;"><b>Array vuoto, nessun record trovato!</b></div></td>';
            html += "</tr>";
        }
        html += "</tbody></table>";
        html += '<div class="pagination-container">';
        html +=    '<nav>';
        html +=        '<ul class="pagination"></ul>';
        html +=    '</nav>';
        html +=  '</div></div></div>';

        var node = $('#appendTable').after(html);
    }

    Initial(){
        var table = "#mytableForPaginateTable";
        $("#maxRowsForPaginateTable").on('change', function(){
            $('.pagination').html('');
            var trnum = 0;
            var maxRows = parseInt($(this).val());
            var totalRows = $(table+' tbody tr').length;

            $(table+' tr:gt(0)').each(function(){
                trnum++;
                if(trnum > maxRows) $(this).hide();
                if(trnum <= maxRows) $(this).show();
            });

            if(totalRows > maxRows){
                var pagenum = Math.ceil(totalRows/maxRows);
                for(var i=1;i<=pagenum;)
                {
                    $(".pagination").append('<li data-page="'+i+'">\<span>'+i++ +'<span class="sr-only">(current)</span></span>\</li>').show();

                }
            }
            $('.pagination li:first-child').addClass('active');
            $('.pagination li').on("click", function(){
                var pageNum = $(this).attr('data-page');
                var trIndex = 0;
                $('.pagination li').removeClass('active');
                $(this).addClass('active');
                $(table+' tr:gt(0)').each(function (){
                    trIndex++;
                    if(trIndex > (maxRows*pageNum) || trIndex <= ((maxRows*pageNum)-maxRows)){
                        $(this).hide();
                    }else {
                        $(this).show();
                    }
                });
            });
        });
        $(function(){
            //$('thead tr:eq(0)').prepend('<th>ID</th>');
            $('thead tr:eq(0)').css("background-color","#669DD9");
            $('thead tr:eq(0)').css("color","white");
            var id = 0;

            $(table+' tr:gt(0)').each(function(){
                /*id++;
                 $(this).prepend('<th>'+id+'</th>');*/
                $(this).addClass("info");
            });
        });


        document.getElementById("maxRowsForPaginateTable").dispatchEvent(new Event("change"));
    }

}

var flagorder = "ASC";

function sortTable(colums) {

    $(".allCaret").removeClass("fa fa-caret-up fa-caret-down");
    if(flagorder == "ASC") $("#caret_"+colums).addClass("fa fa-caret-up");
    else $("#caret_"+colums).addClass("fa fa-caret-down");

    $('#mytableForPaginateTable tr:gt(0)').each(function(){
        $(this).show();
    });
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("myTbodyForPaginateTable");
    //console.log(table);
    switching = true;
    /*Make a loop that will continue until
    no switching has been done:*/
    while (switching) {
        //start by saying: no switching is done:
        switching = false;
        rows = table.rows;
        //console.log(rows);
        /*Loop through all table rows (except the
        first, which contains table headers):*/
        for (i = 0; i < (rows.length - 1); i++) {
            //start by saying there should be no switching:
            shouldSwitch = false;
            /*Get the two elements you want to compare,
            one from current row and one from the next:*/
            x = rows[i].getElementsByTagName("TD")[colums];
            y = rows[i + 1].getElementsByTagName("TD")[colums];
            //check if the two rows should switch place:
            if(flagorder == "ASC") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            }
            else {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch
            and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }

    if(flagorder == "ASC") flagorder = "DESC";
    else flagorder = "ASC";

    document.getElementById("maxRowsForPaginateTable").dispatchEvent(new Event("change"));
}