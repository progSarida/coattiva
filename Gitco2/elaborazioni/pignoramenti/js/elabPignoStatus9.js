    $('#dt_table').DataTable( {
        dom: 'Blfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_pigno_status_9.php",
            type: "POST",
            data: {
                Elaboration_Id: elab_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_elab_pignoramenti.Comune_ID" },
            { data: "v_elab_pignoramenti.Tipo_Riscossione" },
            { data: "v_elab_pignoramenti.Info_Cartella" },
            { data: "v_elab_pignoramenti.Veicolo_ID" },
            { data: "v_elab_pignoramenti.Comune_Residenza" },
            { data: "v_elab_pignoramenti.Tribunale" },
            //{ data: "v_elab_pignoramenti.IVG" }
        ],
        columnDefs: [
            {
                searchPanes: { show: false }, width:30, targets: 0, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        return '<a href="'+web_root+'/coattiva/coazione.php?partita='+row.v_elab_pignoramenti.Partita_ID+'&c='+row.v_elab_pignoramenti.CC+'"><b>'+data+'</b></a>';
                    }
            },
            {
                searchPanes: { show: false }, width:100, targets: 1
            },
            {
                searchPanes: { show: false },  targets: 2
            },
            {
                searchPanes: { show: false }, width:250, targets: 3, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                    let a_id = row.v_elab_pignoramenti.ID_Veicoli.split("*");
                    let a_targa = row.v_elab_pignoramenti.Targhe_Veicoli.split("*");
                    let a_modello = row.v_elab_pignoramenti.Modelli_Veicoli.split("*");
                    let a_data = row.v_elab_pignoramenti.Data_Immatricolazione.split("*");

                    
                    let seldisabled = a_id.length >1 ? '' : ' disabled="true" ';
                    let select = '<select class="AjaxUpdate"'+seldisabled+' style="width:240px;" data-cc="'+row.v_elab_pignoramenti.CC+'" data-pignoramento="'+row.v_elab_pignoramenti.Pignoramento_ID+'" data-pignoramentoveicolo="'+row.v_elab_pignoramenti.Pignoramento_Veicolo_ID+'">';
                    for(let i = 0; i < a_id.length; i++) {
                        if(a_id[i]==data){
                            selected = "selected";
                        }                            
                        else
                            selected = "";

                            

                        select+='<option value="'+a_id[i]+'" '+selected+'>'+a_targa[i]+' - '+a_modello[i]+' - '+a_data[i]+'</option>';
                    }
                    select+= '</select>';
                    //
                    select+='<a href="'+web_root+'/anagrafe/Veicoli.php?p='+row.v_elab_pignoramenti.Utente_ID+'&c='+row.v_elab_pignoramenti.CC+'"><b> Anagrafica </b></a>';
                    
                    
                    return select;
                }
            },
            {
                searchPanes: { show: false },  targets: 4, width:150
            },
            {
                searchPanes: { show: false }, width:150, targets: 5, className: 'dt-center',
                render: function ( data, type, row, meta ) { 
                    var trib = row.v_elab_pignoramenti.Tribunale;
                    var IVG = row.v_elab_pignoramenti.IVG;
                    var strTrib=strIVG="Presente";
                    if (trib==null) {strTrib="Assente";CheckTribunali++;};
                    if (IVG==null) {strIVG="Assente";CheckIVG++;};
                    return strTrib+"/"+strIVG;
                }
            }
        ],

        buttons: [
            // {
            //     text: '<i class="fa-solid fa-car-side"  style=" color:#1c6ee8; cursor:pointer; "></i>',
            //     titleAttr: 'Visure ACI',
            //     action: function ( e, dt, button, config ) {
            //         ScaricaVisureACI();
            //       }   
            // }
        ]
        
    } );

    $(document).ready(function() {
        $('#dt_table').on('change', '.AjaxUpdate', function() {
            updateVeicolo($(this).data('cc'), $(this).data('pignoramento'), $(this).data('pignoramentoveicolo'), $(this).val());
        });
    });

    function updateVeicolo(cc, pigno_id, pigno_veicolo_id, value) {
        $.ajax({
            url: '../ajax/ajax_update_veicolo.php',
            type: 'POST',
            data: {
                'CC': cc,
                'Pignoramento_ID': pigno_id,
                'Pignoramento_Veicolo_ID': pigno_veicolo_id,
                'Veicolo_ID': value
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
    }
