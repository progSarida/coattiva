    $('#dt_table').DataTable( {
        dom: 'lfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_status_assegna_lavoro.php",
            type: "POST",
            data: {
                Elaboration_Id: elab_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_assegna_terzo_lavoro.Utente_ID" },
            { data: "v_assegna_terzo_lavoro.Denominazione" },
            { data: "v_assegna_terzo_lavoro.CF_PI" },
            { data: "v_assegna_terzo_lavoro.Flag_Terzo" },
            { data: "v_assegna_terzo_lavoro.Flag_Terzo" }
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
            },
            {
                searchPanes: { show: false }, width:150, targets: 3, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                    var utente_id = row.v_assegna_terzo_lavoro.Utente_ID; 
                    
                    return '<button class="AssegnaButton" onclick="AssegnaButtonClick('+utente_id+')\" id="btnAssegna'+utente_id+'" name="btnAssegna'+utente_id+'">Assegna</button>';
                }
            },
            {
                searchPanes: { show: false }, width:150, targets: 4, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                    var inserito =row.v_assegna_terzo_lavoro.Flag_Terzo == 'Presente' ? true : false;
                    if (inserito)
                        return 'Presente   <i class="fa-solid fa-circle-minus fa-xl" style="color: darkgreen; margin-top: 1.2rem" title="Datore di lavoro assegnato"></i>';    
                    else
                        return 'Assente     <i class="fa-solid fa-circle-minus fa-xl" style="color: darkred; margin-top: 1.2rem" title="Datore di lavoro assente"></i>';    
                }
                
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
