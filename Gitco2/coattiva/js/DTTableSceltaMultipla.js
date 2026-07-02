    $('#dt_table').DataTable( {
        dom: 'lfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_scelta_multipla.php",
            type: "POST",
            data: function(d){
                var CC=$('#Citta').val();
                let stringa = $('#Nome').val();
                d.Utente_ID = $('#Nome').val();
                d.CC = CC;
            }
            
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_scelta_atto_per_inserimento_multipli.Comune_ID" },
            { data: "v_scelta_atto_per_inserimento_multipli.Anno_Cronologico" },
            { data: "v_scelta_atto_per_inserimento_multipli.ID_Cronologico" },
            { data: "v_scelta_atto_per_inserimento_multipli.Atto" },
            { data: "v_scelta_atto_per_inserimento_multipli.Raccomandata" },
            { data: "v_scelta_atto_per_inserimento_multipli.Info_Cartella" },
            { data: null },
        ],
        columnDefs: [
            {
                searchPanes: { show: false }, width:25, targets: 0, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        return '<a href="'+web_root+'/coattiva/ingiunzione.php?partita='+row.v_scelta_atto_per_inserimento_multipli.Partita_ID+'&c='+row.v_scelta_atto_per_inserimento_multipli.CC+'"><b>'+data+'</b></a>';
                    }
            },
            {
                searchPanes: { show: false }, width:25, targets: 1
            },
            {
                searchPanes: { show: false },  width:25,targets: 2
            },
            {
                searchPanes: { show: false }, width:100, targets: 3, className: 'dt-center'
            },
            {
                searchPanes: { show: false }, width:100, targets: 4
            },
            {
                searchPanes: { show: false }, targets: 5, className: 'dt-center',
               
            },
            {
                searchPanes: { show: false },  width:25,targets: 6,
                render:function (data,type,row,meta){
                    var atto = row.v_scelta_atto_per_inserimento_multipli.Atto_ID;
                    var tipo = row.v_scelta_atto_per_inserimento_multipli.Atto;
                    return '<button class="AssegnaButton" onclick="AssegnaButtonClick('+atto+',\''+tipo+'\')" id="btnAssegna'+row.v_scelta_atto_per_inserimento_multipli.Partita_ID+'" name="btnAssegna'+row.v_scelta_atto_per_inserimento_multipli.Partita_ID+'">Assegna</button>'
                }
            }
        ]
        
    } );