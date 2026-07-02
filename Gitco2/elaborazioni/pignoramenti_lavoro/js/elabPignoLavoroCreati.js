    $('#dt_table').DataTable( {
        dom: 'lfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_pignoramenti_lavoro.php",
            type: "POST",
            data: {
                Elaboration_Id: elab_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_pignoramenti_lavoro.Utente_ID" },
            { data: "v_pignoramenti_lavoro.Denominazione" },
            { data: "v_pignoramenti_lavoro.CF_PI" },
        ],
        columnDefs: [
            {
                searchPanes: { show: false }, width:30, targets: 0, className: 'dt-center'
                // render: function ( data, type, row, meta ) {
                //         return '<a href="'+web_root+'/coattiva/ingiunzione.php?partita='+row.v_pre_elab_pignoramenti.Partita_ID+'&c='+row.v_pre_elab_pignoramenti.CC+'"><b>'+data+'</b></a>';
                //     }
            },
            {
                searchPanes: { show: false }, targets: 1
            },
            {
                searchPanes: { show: false },  width:100, targets: 2
            }
            
        ]

        // buttons: [
        //     {
        //         text: '<i class="fa-solid fa-car-side"  style=" color:#1c6ee8; cursor:pointer; "></i>',
        //         titleAttr: 'Visure ACI',
        //         action: function ( e, dt, button, config ) {
        //             ScaricaVisureACI();
        //           }   
        //     }
        // ]
        
    } );
