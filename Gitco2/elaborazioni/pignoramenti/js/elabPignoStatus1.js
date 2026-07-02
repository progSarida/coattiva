
    $('#dt_table').DataTable( {
        dom: 'PBlfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_status_1_preav_fermo.php",
            type: "POST",
            data: {
                Elaboration_Id: elab_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_pre_elab_acts_preav_fermo.Comune_ID" },
            { data: "v_pre_elab_acts_preav_fermo.Tipo_Riscossione" },
            { data: "v_pre_elab_acts_preav_fermo.Info_Cartella" },
            { data: "v_pre_elab_acts_preav_fermo.Anomalia_ATTO" },
            { data: "v_pre_elab_acts_preav_fermo.Position_Status" },
            { data: "v_pre_elab_acts_preav_fermo.flag_elaboration" }
        ],
        columnDefs: [
            {
                searchPanes: { show: false }, targets: 2
            },
            {
                searchPanes: { show: true }, targets: [3,4]
            },
            {
                searchPanes: { show: false }, targets: 0, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        return '<a href="'+web_root+'/coattiva/ingiunzione.php?partita='+row.v_pre_elab_acts_preav_fermo.Partita_ID+'&c='+row.v_pre_elab_acts_preav_fermo.CC+'"><b>'+data+'</b></a>';
                    }
            },
            {
                searchPanes: { show: false }, targets: 5, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        switch(parseInt(row.v_pre_elab_acts_preav_fermo.Position_Status_Id)){
                            case 1: case 2: case 3:
                            case 5: case 8: case 17:
                            case 19: case 20: case 21: case 22:
                                disabled = "";
                                break;
                            default:
                                disabled = "disabled";
                        }
                        if(data==1)
                            checked = "checked";
                        else
                            checked = "";
                        return ' <input type="checkbox" '+disabled+' '+checked+' value="1" id="flag_check_'+row.v_pre_elab_acts_preav_fermo.Partita_ID+'" name="flag_check_'+row.v_pre_elab_acts_preav_fermo.Partita_ID+'">';
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
                text: '<i class="fa fa-file-excel fa-2x"  style=" color:green; cursor:pointer; "></i>',
                titleAttr: 'Stampa dettagli',

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

                    alert('Processo di "Stampa dettagli" attivato. Al termine delle operazioni, sarà disponibile il file "Lista utenti_' + elab_id + '.xlsx');
                    console.log(JSON.stringify(jsonFilters));
                    startSpiners();

                    $.ajax({
                        url: '../ajax/ajax_print_Excel.php',
                        type: 'POST',
                        data: {
                            'last_el_id': elab_id,
                            'filters': JSON.stringify(jsonFilters)
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

    $('#dt_table').on('click', 'input[type="checkbox"]', function() {

        var par_String = this.id;
        var partita = par_String.replace(/[^0-9.]/g, "");
        var check = this.checked ? 1 : 0;

        $.ajax({
            url: '../ajax/ajax_rielaborazioni.php',
            type: 'POST',
            data: {
                'partita': partita,
                'check': check
            },
            cache: false,
            success: function(response) {

                var response = JSON.parse(response);

                if (response.esito == "OK") {
                    swal({
                        title: "UPDATE!",
                        text: response.message,
                        icon: "success",
                        timer: 3000,
                        buttons: false
                    })
                } else {

                    swal({
                        title: "ERROR!",
                        text: response.message,
                        icon: "danger",
                        timer: 3000,
                        buttons: false
                    })
                }
            },
            error: function(error) {
                console.log(error)
            }
        });
    });
