
var myGlobalTableNameCustomTable = "";
var flagOrderOfColumsInTableCustom = "ASC";

class TableGenerator {

    constructor(fields,toprint,widthColums,fontsize,idtable,backgrowndColor) {
        /** l'array con tutti i campi passati **/
        this.fields = fields;
        /** i campi da stampare **/
        this.toprint = toprint;
        /** la larghezza delle celle passata dall'utente **/
        this.widthColums = widthColums;
        /** la dimensione dei caratteri passata dall'utente **/
        this.fontSize = fontsize;
        /** l'id del div dove appendere la tabella **/
        if(idtable==undefined) this.idTable = "appendTable";
        else this.idTable = idtable;

        if(backgrowndColor != undefined) this.backgrownd = backgrowndColor;
        else this.backgrownd = "";

        myGlobalTableNameCustomTable = this.idTable;
        var myobj = document.getElementById(this.idTable);

        if (myobj.innerHTML!="") {
            myobj.innerHTML = "";
        }


        this.createHtml();
        this.Initial();
    }

    createHtml(){

        /** la creazione della drop per selezionare quanti elementi si devono visualizzare per pagina **/
        var html = '<div class="row" style="">';
        html +=         '<div class="col-lg-1"></div>';
        html +=         '<div class="col-lg-10" style="padding-top: 2%;background-color: '+this.backgrownd+'">';
        html +=             '<div class="row" style="">';
        html +=                 '<div class="col-lg-2">'
        html +=                     '<h6>Num. righe</h6>';
        html +=                 '</div>';
        html +=                 '<div class="col-lg-3">';
        html +=                     '<div class="form-group">';
        html +=                         '<select name="maxRowsForPaginateTable_'+this.idTable+'" id="maxRowsForPaginateTable_'+this.idTable+'" class="form-control form-control-sm form-select-sm" style="width: 150px;">';
        html +=                             '<option value="50000" >Mostra tutti</option>';
        html +=                             '<option value="3" >3</option>';
        html +=                             '<option value="4" >4</option>';
        html +=                             '<option value="5" selected>5</option>';
        html +=                             '<option value="10" >10</option>';
        html +=                             '<option value="15" >15</option>';
        html +=                             '<option value="20" >20</option>';
        html +=                             '<option value="50" >50</option>';
        html +=                             '<option value="75" >75</option>';
        html +=                             '<option value="100" >100</option>';
        html +=                         '</select>';
        html +=                     '</div>';
        html +=                 '</div>';
        html +=                 '<div class="col-lg-5"></div>';
        html +=                 '<div class="col-lg-2"><p id="visualizzaNumeroPagineTotali_'+this.idTable+'"></p></div>';
        html +=             '</div>';
        html +=         '</div>';
        html +=     '</div>';


        html += '<div class="row">';
        html += '<div class="col-lg-1"></div>';
        html += '<div class="col-lg-10" style="background-color: '+this.backgrownd+'">';//da aggiungere sti due div di chiusura
        html += '<table id="mytableForPaginateTable_'+this.idTable+'" class="table table-bordered table-hover table-sm">';

        /** se vengono passate delle dimensioni fissate per la larghezza delle colonne vengono inizializzate **/
        if(this.widthColums != undefined && this.fields[0] != undefined){

            html += '<colgroup>';

            for(var z=0; z < this.widthColums.length; z++)
                html += '<col style="width: '+this.widthColums[z]+';" />'

            html += '</colgroup>';
        }

        var headFontSizeStr = "";

        if(this.fontSize != undefined){
            var headFontSize = this.fontSize;
            var flagPerPx = "";

            if(this.fontSize.includes("px")) {
                headFontSize = headFontSize.replace(/px/g, "");
                flagPerPx = "px";
            }
            if(this.fontSize.includes("%")) {
                headFontSize = headFontSize.replace(/%/g, "");
                flagPerPx = "%";
            }

            headFontSize = parseInt(headFontSize);

            headFontSizeStr = " style='font-size: "+(headFontSize+2)+flagPerPx+"' ";
        }

        html +=     '<thead class="classForTheadBuilt_'+this.idTable+'">';
        html +=         '<tr '+headFontSizeStr+'>';

        /** Prende solo la prima riga per confrontarlo con toprint e stampare l'header**/
        var allFields = this.fields[0];
        /** dopo la stampa dell'header questo array sarà riempito con tutti i nomi dei campi che si volevano stampare, verrà usato poi per stampare così solo quei campi del body **/
        this.toPrintDef = [];
        /** una sorta di id che mi consente poi di tracciare ogni elemento e per esempio ordinare la tabella **/
        var countElement = 0;
        /** serve a verificare se c'è una azione globale che funzioni cliccando direttamente sulla riga e non su eventuali pulsanti inseriti, ovvio può essere solo una per cui non sarà un array, ma una stringa **/
        this.actionRow = "";

        if(allFields != undefined) {
            if (this.toprint != undefined) {
                var headerName = "";
                for (var i = 0; i < this.toprint.length; i++)
                    for (var property in allFields) {

                        if (typeof (this.toprint[i]) == "string") {
                            /** stamperà solo se trova che il "toprint" è presente nell'array con i dati passati, (property) **/
                            if (property == this.toprint[i]) {
                                this.toPrintDef.push(property);
                                html += '<th style="cursor: pointer;" onclick="sortTable(\'' + countElement + '\');"><div style="float: left;">' + property + '</div> <div style="float: right;"><i id="caret_'+ this.idTable + '_' + countElement + '" class="allCaret" aria-hidden="true"></i></div></th>';
                                countElement++;
                                break;
                            }
                        } else if (typeof (this.toprint[i]) == "object") {
                            /** Se è un oggetto dovrò confrontare con originalName che è il valora del campo a db ad esempio la chiave della array "array[0]["nome"]", (il campo "nome")**/
                            if (property == this.toprint[i].originalName) {
                                if(this.toprint[i].type == "action"){
                                    this.actionRow = property;
                                    continue;
                                }
                                else {
                                    this.toPrintDef.push(property);
                                }

                                headerName = this.toprint[i].replacedName !== undefined ? this.toprint[i].replacedName : this.toprint[i].originalName;

                                //console.log(this.toprint[i].type);
                                html += '<th style="cursor: pointer;" onclick="sortTable(\'' + countElement + '\',\'' + this.toprint[i].type + '\');"><div style="float: left;">' + headerName + '</div> <div style="float: right;"><i id="caret_'+ this.idTable + '_' + countElement + '" class="allCaret" aria-hidden="true"></i></div></th>';
                                countElement++;
                                break;
                            }
                        }
                    }
            } else {
                /** se non passo il campo "toprint" stampo tutto indistintamente**/
                for (var property in allFields) {
                    this.toPrintDef.push(property);
                    html += '<th onclick="sortTable(\'' + countElement + '\');"><div style="float: left;">' + property + '</div> <div style="float: right;"><i id="caret_'+ this.idTable + '_' + countElement + '" class="allCaret" aria-hidden="true"></i></div></th>';
                    countElement++;
                }
            }
        }
        else{
            html += '<th><b>Info</b></th>';
        }

        html += '</tr></thead><tbody id="myTbodyForPaginateTable_'+this.idTable+'">';

        if(allFields != undefined) {
            /** stampa i valori di tutti gli elementi che avevamo deciso di stampare e che sono salvati in "toPrintDef" **/
            var addedStyle = "";
            if(this.actionRow != "")
                addedStyle = "cursor: pointer;";
                //console.log(this.fields[0][this.actionRow]);
            for (var x = 0; x < this.fields.length; x++) {

                html += "<tr style='font-size: " + this.fontSize + ";" + addedStyle + "' onclick='" + this.fields[x][this.actionRow] + "'>";
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
            html += '<td><div style="text-align: center;color: #0a53be;"><b>Nessun record trovato!</b></div></td>';
            html += "</tr>";
        }
        /** i div dove si inseriranno i bottoni per girare tra le pagine **/
        html += "</tbody></table>";
        html += '<div class="pagination-container">';
        html +=    '<nav aria-label="...">';
        html +=        '<ul class="pagination btnPaginationForMyCustomTable_'+this.idTable+'"></ul>';
        html +=    '</nav>';
        html +=  '</div></div></div>';

        var node = $('#'+this.idTable).append(html);
    }

    Initial(){

        var table = "#mytableForPaginateTable_"+this.idTable;

        /** sull'onchaing della drop con selezione del numero di elementi per pagina modifica il numero degli elementi che vengono visualizzati **/
        $("#maxRowsForPaginateTable_"+this.idTable).on('change', function(){

            $('.btnPaginationForMyCustomTable_'+myGlobalTableNameCustomTable).html('');
            var trnum = 0;
            var maxRows = parseInt($(this).val());
            var totalRows = $(table+' tbody tr').length;

            /** seleziona tutti gli elementi e visualizza solo i primo x elementi (x=maxRows) **/
            $(table+' tr:gt(0)').each(function(){
                trnum++;
                if(trnum > maxRows) $(this).hide();
                if(trnum <= maxRows) $(this).show();
            });

            if(totalRows > maxRows){
                var pagenum = Math.ceil(totalRows/maxRows);
                $("#visualizzaNumeroPagineTotali_"+myGlobalTableNameCustomTable).html("<h6>Pagine totali: "+pagenum+"</h6>");
                if(pagenum > 3) {
                    $(".btnPaginationForMyCustomTable_"+myGlobalTableNameCustomTable).append('<li data-page="prec" class="page-item"><a class="page-link" > << </a></li>');
                }
                var attivazione = "";
                for (var i = 1; i <= pagenum;) {
                    if(i==1) attivazione = "active";
                    else attivazione = "";
                    $(".btnPaginationForMyCustomTable_"+myGlobalTableNameCustomTable).append('<li class="page-item '+attivazione+'" id="page_id_'+myGlobalTableNameCustomTable+'_'+i+'" data-page="' + i + '"><span class="page-link">' + i++ + '<span class="sr-only">(current)</span></span></li>').show();

                    if(i>4)
                        $("#page_id_"+myGlobalTableNameCustomTable+'_' + (i-1)).hide();

                }
                if(pagenum > 3) {
                    $(".btnPaginationForMyCustomTable_"+myGlobalTableNameCustomTable).append('<li data-page="next" class="page-item"><a class="page-link" > >> </a></li>');
                }

            }

            /** quando viene schiacciato un pulsante del numero della pagina nasconde tutti gli elementi prima e dopo quelli che devono essere visualizzati,
             * (es pag 2 e max 5 elementi per pagina, da 0 a 4 sono nascosti da 5 a 9 sono visualizzati e da nove in poi nascosti) **/
            $('.btnPaginationForMyCustomTable_'+myGlobalTableNameCustomTable+' li').on("click", function(){
                var pageNum = $(this).attr('data-page');
                var trIndex = 0;
                if(pageNum == "next"){
                    var precButton = parseInt($(".btnPaginationForMyCustomTable_"+myGlobalTableNameCustomTable+" li.active").attr('data-page'), 10);

                    if($("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton+1)).length > 0){
                        if($("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton+1)).is(':hidden')){
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton+1)).show();
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton-2)).hide();
                            $('.btnPaginationForMyCustomTable_'+myGlobalTableNameCustomTable+' li').removeClass('active');
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton+1)).addClass('active');
                        }
                        else{
                            $('.btnPaginationForMyCustomTable_'+myGlobalTableNameCustomTable+' li').removeClass('active');
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton+1)).addClass('active');
                        }
                    }else return false;

                    pageNum = precButton + 1;
                }else if(pageNum == "prec"){

                    var precButton = parseInt($(".btnPaginationForMyCustomTable_"+myGlobalTableNameCustomTable+" li.active").attr('data-page'),10);

                    if($("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton-1)).length > 0){
                        if($("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton-1)).is(':hidden')){
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton-1)).show();
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton+2)).hide();
                            $('.btnPaginationForMyCustomTable_'+myGlobalTableNameCustomTable+' li').removeClass('active');
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton-1)).addClass('active');
                        }
                        else{
                            $('.btnPaginationForMyCustomTable_'+myGlobalTableNameCustomTable+' li').removeClass('active');
                            $("#page_id_"+myGlobalTableNameCustomTable+'_'+(precButton-1)).addClass('active');
                        }
                    }else return false;

                    pageNum = precButton - 1;

                }
                else {
                    $('.btnPaginationForMyCustomTable_'+myGlobalTableNameCustomTable+' li').removeClass('active');
                    $(this).addClass('active');
                }

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
        /** assegna dei css alla tabella **/
        $(function(){
            //$('thead tr:eq(0)').prepend('<th>ID</th>');
            $('.classForTheadBuilt_'+myGlobalTableNameCustomTable+' tr:eq(0)').css("background-color","#669DD9");
            $('.classForTheadBuilt_'+myGlobalTableNameCustomTable+' tr:eq(0)').css("color","white");
            var id = 0;

            $(table+' tr:gt(0)').each(function(){
                /*id++;
                 $(this).prepend('<th>'+id+'</th>');*/
                $(this).addClass("info");
            });
        });


        document.getElementById("maxRowsForPaginateTable_"+this.idTable).dispatchEvent(new Event("change"));
    }

}

function sortTable(colums,type = null) {

    $(".allCaret").removeClass("fas fa-caret-up fa-caret-down");
    if(flagOrderOfColumsInTableCustom == "ASC") $("#caret_"+ myGlobalTableNameCustomTable + '_' + colums).addClass("fas fa-caret-up");
    else $("#caret_" + myGlobalTableNameCustomTable + '_' + colums).addClass("fas fa-caret-down");

    $('#mytableForPaginateTable_'+myGlobalTableNameCustomTable+' tr:gt(0)').each(function(){
        $(this).show();
    });
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("myTbodyForPaginateTable_"+myGlobalTableNameCustomTable);

    switching = true;
    /*Make a loop that will continue until
    no switching has been done:*/
    while (switching) {
        //start by saying: no switching is done:
        switching = false;
        rows = table.rows;

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
            if(flagOrderOfColumsInTableCustom == "ASC") {


                switch(type){
                    case "date":
                        if(x.innerHTML.includes("/")){
                            var arr1 = x.innerHTML.split("/");
                            var arr2 = y.innerHTML.split("/");
                        }
                        else if(x.innerHTML.includes("-")){
                            var arr1 = x.innerHTML.split("-");
                            var arr2 = y.innerHTML.split("-");
                        }
                        else{
                            if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                                //if so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                            break;
                        }

                        if(arr1[2].length == 4){
                            var d1 = new Date(arr1[2],arr1[1]-1,arr1[0]);
                            var d2 = new Date(arr2[2],arr2[1]-1,arr2[0]);
                        }
                        else{
                            var d1 = new Date(arr1[0],arr1[1]-1,arr1[2]);
                            var d2 = new Date(arr2[0],arr2[1]-1,arr2[2]);
                        }

                        var r1 = d1.getTime();
                        var r2 = d2.getTime();

                        if (r1 > r2) {
                            shouldSwitch = true;
                            break;
                        }
                        break;
                    case "number":
                        if(isNumeric(x.innerHTML) && isNumeric(y.innerHTML)){
                            if (parseFloat(x.innerHTML) > parseFloat(y.innerHTML)) {
                                //if so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        }
                        else{
                            if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                                //if so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        }
                        break;
                    case "string":
                    default:
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            //if so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                        break;
                }


            }
            else {

                switch(type){
                    case "date":
                        if(x.innerHTML.includes("/")){
                            var arr1 = x.innerHTML.split("/");
                            var arr2 = y.innerHTML.split("/");
                        }
                        else if(x.innerHTML.includes("-")){
                            var arr1 = x.innerHTML.split("-");
                            var arr2 = y.innerHTML.split("-");
                        }
                        else{
                            if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                                //if so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                            break;
                        }

                        if(arr1[2].length == 4){
                            var d1 = new Date(arr1[2],arr1[1]-1,arr1[0]);
                            var d2 = new Date(arr2[2],arr2[1]-1,arr2[0]);
                        }
                        else{
                            var d1 = new Date(arr1[0],arr1[1]-1,arr1[2]);
                            var d2 = new Date(arr2[0],arr2[1]-1,arr2[2]);
                        }

                        var r1 = d1.getTime();
                        var r2 = d2.getTime();

                        if (r1 < r2) {
                            shouldSwitch = true;
                            break;
                        }
                        break;
                    case "number":
                        if(isNumeric(x.innerHTML) && isNumeric(y.innerHTML)){
                            if (parseFloat(x.innerHTML) < parseFloat(y.innerHTML)) {
                                //if so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        }
                        else{
                            if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                                //if so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        }
                        break;
                    case "string":
                    default:
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            //if so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                        break;
                }

            }

            if(shouldSwitch == true)
                break;
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch
            and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }

    if(flagOrderOfColumsInTableCustom == "ASC") flagOrderOfColumsInTableCustom = "DESC";
    else flagOrderOfColumsInTableCustom = "ASC";

    document.getElementById("maxRowsForPaginateTable_"+myGlobalTableNameCustomTable).dispatchEvent(new Event("change"));
}

function isNumeric(str) {
    if (typeof str != "string") return false // we only process strings!
    return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
        !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
}
