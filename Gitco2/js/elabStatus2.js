
    $('#dt_table').DataTable( {
        dom: 'PBlfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: "ajax/ss_elab_status_2.php",
            type: "POST",
            data: {
                Elaboration_Id: elab_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_elab_acts.Comune_ID" },
            { data: "v_elab_acts.Tipo_Riscossione" },
            { data: "v_elab_acts.Info_Cartella" },
            { data: "v_elab_acts.Printer" },
            { data: "v_elab_acts.PrintType" },
            { data: "v_elab_acts.Tipo_Ufficiale" },
            { data: "v_elab_acts.PEC" }
        ],
        buttons: [
            {
                text: '<i class="fa-regular fa-envelope fa-2x"  style=" color:#1c6ee8; cursor:pointer; "></i>',
                titleAttr: 'Procedura INIPEC',
                action: function ( e, dt, button, config ) {
                    inipecLink();
                  }   
            }],
        columnDefs: [
            {
                searchPanes: { show: false }, targets: 2, className: 'dt-head-center'
            },
            {
                searchPanes: { show: true }, targets: 1, className: 'dt-head-center'
            },
            {
                searchPanes: { show: false }, targets: 0, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        return '<a href="'+web_root+'/coattiva/ingiunzione.php?partita='+row.v_elab_acts.Partita_ID+'&c='+row.v_elab_acts.CC+'"><b>'+data+'</b></a>';
                    }
            },
            {
                searchPanes: { show: true }, targets: 3, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        let select = '<select class="AjaxUpdate" data-field="PrinterId" data-partita="'+row.v_elab_acts.Partita_ID+'">';
                        for(let i = 0; i < a_printer.length; i++) {
                            if(a_printer[i].Name==data)
                                selected = "selected";
                            else
                                selected = "";
                            select+='<option value="'+a_printer[i].Id+'" '+selected+'>'+a_printer[i].Name+'</option>';
                        }
                        select+= '</select>';
                        return select;
                    }
            },
            {
                searchPanes: { show: true }, targets: 4, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        let select = '<select class="AjaxUpdate" data-field="PrintTypeId" data-partita="'+row.v_elab_acts.Partita_ID+'">';
                        for(let i = 0; i < a_print_type.length; i++) {
                            if(a_print_type[i].Description==data)
                                selected = "selected";
                            else
                                selected = "";
                            select+='<option value="'+a_print_type[i].Id+'" '+selected+'>'+a_print_type[i].Description+'</option>';
                        }
                        select+= '</select>';
                        return select;
                    }
            },
            {
                searchPanes: { show: true }, targets: 5, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        let select = '<select class="AjaxUpdate" data-field="Tipo_Ufficiale" data-partita="'+row.v_elab_acts.Partita_ID+'">';
                        for(let i = 0; i < a_tipo_ufficiale.length; i++) {
                            if(a_tipo_ufficiale[i]==data)
                                selected = "selected";
                            else
                                selected = "";
                            select+='<option '+selected+'>'+a_tipo_ufficiale[i]+'</option>';
                        }
                        select+= '</select>';
                        return select;
                    }
            },
            {
                searchPanes: { show: false }, targets: 6, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        a_check = checkPec(row.v_elab_acts.CF_PI, row.v_elab_acts.REC_PRESSO, row.v_elab_acts.PEC, row.v_elab_acts.InipecLoaded);
                        if(callPec==0 && a_check[0]==1){
                            callPec = 1;
                            console.log(callPec);
                        }
                            
                        return a_check[1];
                    }
            },
        ]
    } );

    $(document).ready(function() {
        $('#dt_table').on('change', '.AjaxUpdate', function() {
            updateAttoField($(this).data('partita'), $(this).data('field'), $(this).val());
        });
    });

    function checkPec(CF_PI, REC_PRESSO, PEC, INIPEC_DATE){
        let a_return = [0,''];
        if(!(CF_PI && CF_PI!="00000000000")){
            a_return[1] = '<i class="fa-solid fa-id-card fa-xl" style="color: #2863c1; margin-top: 1.2rem" title="CF - PI Assente"></i>';
            return a_return;
        }
            
        if(REC_PRESSO){
            a_return[1] = '<i class="fa-solid fa-location-dot fa-xl" style="color: #2863c1; margin-top: 1.2rem" title="Recapito presso '+REC_PRESSO+'"></i>';
            return a_return;
        }
        
        if(INIPEC_DATE){
            let ymd = INIPEC_DATE.split('-');
            let inipecDate = new Date(ymd[0], ymd[1] - 1, ymd[2]);
            let today = new Date();
            days = Math.round((today - inipecDate) / (1000 * 60 * 60 * 24))
            if(days<15)
                checkExpired = 0;
            else
                checkExpired = 1;

            if(PEC){
                if(checkExpired==0)
                    a_return[1] = '<i class="fa-solid fa-envelope fa-xl" style="color: darkgreen; margin-top: 1.2rem" title="'+PEC+' - Pec verificata"></i>';
                else if(checkExpired==1){
                    a_return[1] = '<i class="fa-solid fa-envelope fa-xl" style="color: darkred; margin-top: 1.2rem" title="'+PEC+' - Pec aggiornata '+days+' giorni fa"></i>';
                    a_return[0] = 1;
                }
            }
            else{
                if(checkExpired==0)
                    a_return[1] = '<i class="fa-solid fa-circle-minus fa-xl" style="color: darkgreen; margin-top: 1.2rem" title="Pec controllata '+days+' giorni fa"></i>';
                else{
                    a_return[1] = '<i class="fa-solid fa-circle-xmark fa-xl" style="color: darkred; margin-top: 1.2rem" title="Pec controllata '+days+' giorni fa"></i>';
                    a_return[0] = 1;
                }
            }
        }
        else{
            a_return[0] = 1;
            if(PEC)
                a_return[1] = '<i class="fa-solid fa-envelope fa-xl" style="color: darkred; margin-top: 1.2rem" title="'+PEC+' - Pec da verificare"></i>';
            else
                a_return[1] = '<i class="fa-solid fa-circle-xmark fa-xl" style="color: darkred; margin-top: 1.2rem" title="Pec da acquisire"></i>';
        }

        return a_return;
    }

    function updateAttoField(partita, field, value) {
        $.ajax({
            url: 'ajax/ajax_update_act.php',
            type: 'POST',
            data: {
                'partita': partita,
                'field': field,
                'value': value
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

    
