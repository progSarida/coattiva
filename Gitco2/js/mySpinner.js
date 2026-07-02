class mySpinner {

    constructor(id_spinner,route) {
        this.flagStop = false;
        this.idSpinner = id_spinner;
        this.route = route;

        //alert( this.idSpinner+" - "+this.route);

        var html = "";

        html += '<div class="back_spinner" id="caricamento_spinner">' +
            '                <div class="d-flex align-items-center text-info" style="width: 250px;position: fixed;top:49%;left:45%;">' +
            '                    <div style="float:left;font-size: 20px;color:white;"><i class="fas fa-spinner fa-pulse "></i></div>&#160;&#160;&#160;&#160;' +
            '                    <div id="text_spinner" style="font-weight: bold; float:left;color:white;margin-left: 20px;">Loading... 0.00%</div>' +
            '                </div>' +
            '            </div>';

        html += '<style>' +
            '        .back_spinner {' +
            '            display: none;' +
            '            position: fixed;' +
            '            top: 0;' +
            '            left: 0;' +
            '            right: 0;' +
            '            bottom: 0;' +
            '            width: 100%;' +
            '            background: rgba(0,0,0,0.75);' +
            '            z-index: 10000;' +
            '        }' +
            '        #text_spinner {'+
            '            font-size: 20px;'+
            '        }' +
            '    </style>';

        $("#"+this.idSpinner).append(html);

    }

    async startSpinner(){

        $("#caricamento_spinner").show();
        this.flagStop = false;

        this.updateSpinner(this);

    }

    async updateSpinner(element){

        var obj = element;

        $.getJSON(obj.route, function(data) {
            $("#text_spinner").html("Loading... "+data[0]+"%");
        });

        if(!obj.flagStop)
            setTimeout(obj.updateSpinner.bind(null, element), 500);
    }

    async closeSpinner(){
        //alert("close");
        this.flagStop = true;
        $("#caricamento_spinner").hide();
    }
}
