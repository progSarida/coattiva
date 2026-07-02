$('#dt_table').DataTable( {
    dom: 'PBlfrtip',
    processing: true,
    serverSide: true,
    searchPanes: {
        orderable: false,
        initCollapsed: true
    },
    ajax: {
        url: web_dteditor+"/ss_elab_status_assegna_banche.php",
        type: "POST",
        data: {
            Elaboration_Id: elab_id
        }
    },   
    "language": {
        "url": web_datatable+"/dt_IT.json"
    },
    columns: [
        { data: "v_assegna_terzo_banca.Utente_ID" },
        { data: "v_assegna_terzo_banca.Denominazione" },
        { data: "v_assegna_terzo_banca.CF_PI" },
        { data: "v_assegna_terzo_banca.Flag_Terzo" },
        { data: "v_assegna_terzo_banca.Flag_Terzo" }
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
                var utente_id = row.v_assegna_terzo_banca.Utente_ID; 
                
                return '<button class="AssegnaButton" onclick="AssegnaButtonClick('+utente_id+')\" id="btnAssegna'+utente_id+'" name="btnAssegna'+utente_id+'">Assegna</button>';
            }
        },
        {
            searchPanes: { show: false }, width:150, targets: 4, className: 'dt-center',
            render: function ( data, type, row, meta ) {
                var inserito =row.v_assegna_terzo_banca.Flag_Terzo == 'Presente' ? true : false;
                if (inserito)
                    return 'Presente   <i class="fa-solid fa-circle-minus fa-xl" style="color: darkgreen; margin-top: 1.2rem" title="Datore di lavoro assegnato"></i>';    
                else
                    return 'Assente     <i class="fa-solid fa-circle-minus fa-xl" style="color: darkred; margin-top: 1.2rem" title="Datore di lavoro assente"></i>';    
            }
            
        }
    ],
    buttons: [
        {
            extend: 'pdfHtml5',
            text: '<i class="fa fa-file-pdf fa-2x"  style=" color:darkred; cursor:pointer; "></i>',
            titleAttr: 'PDF',
            title: 'Lista Elaborazione Atti '+elab_cc,
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            }
        },
        {
            text: '<i class="fa fa-file-excel fa-2x"  style=" color:green; cursor:pointer; "></i> Stampa Elaborabili',
            titleAttr: 'Stampa dettagli elaborabili',

            action: function(e, dt, node, config) {

                var searchPanes = dt.context[0]._searchPanes.s.panes;
                var jsonFilters = {};
                searchPanes.forEach( searchPane => {
                    if ( searchPane.s.selections.length > 0 ) {
                        var temp_array = [];
                        searchPane.s.selections.forEach( elem => {
                            if (!temp_array.includes(elem)) 
                                temp_array.push(elem);
                        } );
                        jsonFilters[searchPane.s.name] = temp_array;
                    }
                });

                alert('Processo di "Stampa dettagli elaborabili" attivato. Al termine delle operazioni, sarà disponibile il file "Lista utenti elaborabili_' + elab_id + '.xlsx');
                
                startSpiners();

                $.ajax({
                    url: '../ajax/ajax_print_Excel.php',
                    type: 'POST',
                    data: {
                        'last_el_id': elab_id,
                        'filters': JSON.stringify(jsonFilters),
                        'elaborabili' : 1
                    },
                    cache: false,
                    async: true,
                    success: function(response) {

                        closeSpiner();

                        var response = JSON.parse(response);
                        if (response.esito == "OK") {
                            swal({
                                title: "Report eseguito!",
                                text: response.message,
                                icon: "success",
                                timer: 3000,
                                buttons: false
                            }).then(function() {
                                var link = document.createElement("a");
                                document.body.appendChild(link);
                                link.setAttribute("type", "hidden");
                                link.href = response.data;
                                link.download = response.nome_file;
                                link.click();
                                document.body.removeChild(link);
                            });
                        } else {
                            swal({
                                title: "Report fallito!",
                                text: response.message,
                                icon: "warning",
                                timer: 3000,
                                buttons: false
                            })
                        }
                    },
                    error: function(error) {
                        console.log(error);
                        closeSpiner();
                    }
                });


            }
        }
    ]
    
} );
