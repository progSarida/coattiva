    $('#dt_table').DataTable( {
        dom: 'Blfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_pigno_status_8.php",
            type: "POST",
            data: {
                Elaboration_Id: elab_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_pre_elab_pignoramenti.Comune_ID" },
            { data: "v_pre_elab_pignoramenti.Tipo_Riscossione" },
            { data: "v_pre_elab_pignoramenti.Info_Cartella" },
            { data: "v_pre_elab_pignoramenti.Data_Acquisizione" },
            { data: "v_pre_elab_pignoramenti.Stato_Veicolo" }
        ],
        columnDefs: [
            {
                searchPanes: { show: false }, width:30, targets: 0, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        return '<a href="'+web_root+'/coattiva/ingiunzione.php?partita='+row.v_pre_elab_pignoramenti.Partita_ID+'&c='+row.v_pre_elab_pignoramenti.CC+'"><b>'+data+'</b></a>';
                    }
            },
            {
                searchPanes: { show: false }, width:100, targets: 1
            },
            {
                searchPanes: { show: false },  targets: 2
            },
            {
                searchPanes: { show: false }, width:150, targets: 3, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                    let options = { day: '2-digit', month: '2-digit', year: 'numeric' };
                    
                    if(row.v_pre_elab_pignoramenti.Data_Acquisizione != null)
                        return new Date(row.v_pre_elab_pignoramenti.Data_Acquisizione).toLocaleDateString('it-it', options)
                    else
                        return null;
                }
            },
            {
                searchPanes: { show: false }, width:150, targets: 4, className: 'dt-center',
                render: function ( data, type, row, meta ) { 
                    if(data!=1)
                        return "NON PIGNORABILE";
                    else
                        return "PIGNORABILE";
                }
            }
        ],

        buttons: [
            {
                text: '<i class="fa-solid fa-car-side"  style=" color:#1c6ee8; cursor:pointer; "></i>',
                titleAttr: 'Visure ACI',
                action: function ( e, dt, button, config ) {
                    ScaricaVisureACI();
                  }   
            }
        ]
        
    } );
