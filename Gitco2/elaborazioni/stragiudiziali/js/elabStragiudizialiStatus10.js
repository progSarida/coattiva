
    $('#dt_table').DataTable( {
        dom: 'lfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_status_20_stragiudiziali_"+tipo+".php",
            type: "POST",
            data: {
                
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_"+(tipo=="Previdenziali" ? "previdenziali":"banche")+"_stragiudiziali.Denominazione" },
            { data: "v_"+(tipo=="Previdenziali" ? "previdenziali":"banche")+"_stragiudiziali.REC_PRESSO" },
            { data: "v_"+(tipo=="Previdenziali" ? "previdenziali":"banche")+"_stragiudiziali.CF_PI" },
        ], 
        columnDefs: [
            {
                searchPanes: { show: false }, targets: 0, className: 'dt-head-center'
            },
            {
                searchPanes: { show: true }, targets: 1, className: 'dt-head-center'
            },
            {
                searchPanes: { show: true }, targets: 2, className: 'dt-head-center'
            }
        ]
    } );

    

    
