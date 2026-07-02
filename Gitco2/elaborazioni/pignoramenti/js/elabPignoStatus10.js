    disPEC = 1;

    $('#dt_table').DataTable( {
        dom: 'PBlfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_pigno_status_10.php",
            type: "POST",
            data: {
                Elaboration_Id: elab_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_elab_acts_pignoramenti.Comune_ID" },
            { data: "v_elab_acts_pignoramenti.Tipo_Riscossione" },
            { data: "v_elab_acts_pignoramenti.Info_Cartella" },
            { data: "v_elab_acts_pignoramenti.Tipo_Notifica" },
            { data: "v_elab_acts_pignoramenti.Printer" },
            { data: "v_elab_acts_pignoramenti.PrintType" },
            { data: "v_elab_acts_pignoramenti.Tipo_Ufficiale" },
            { data: "v_elab_acts_pignoramenti.PEC" }
        ],
        buttons: [
            {
                text: '<i class="fa-regular fa-envelope fa-2x"  style=" color:#1c6ee8; cursor:pointer; "></i>',
                titleAttr: 'Procedura INIPEC',
                action: function ( e, dt, button, config ) {
                    inipecLink();
                  }   
            },
            {
                text: '<p id="MsgPec" style="color:red;">Disabilita INIPEC </p>',
                titleAttr: 'Disabilita controllo INIPEC',
                action: function ( e, dt, button, config ) {
                    //<input type="checkbox" id="DisabilitaINIPEC" value="Abilita/Disabilita INIPEC">
                    
                    if(disPEC == 0)
                    {
                        $('#MsgPec').text('Disabilita INIPEC')
                        $('#MsgPec').css({"color":"red"});
                        disPEC = 1;
                        callPec = 1;

                    }
                    else
                    {
                        $('#MsgPec').text('Abilita INIPEC')
                        $('#MsgPec').css({"color":"green"});
                        disPEC = 0;
                        callPec = 0;
                    } 
                    console.log(callPec);
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
                searchPanes: { show: true }, targets: 3, className: 'dt-head-center'
            },
            {
                searchPanes: { show: false }, targets: 0, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        return '<a href="'+web_root+'/coattiva/coazione.php?partita='+row.v_elab_acts_pignoramenti.Partita_ID+'&c='+row.v_elab_acts_pignoramenti.CC+'"><b>'+row.v_elab_acts_pignoramenti.Comune_ID+'</b></a>';
                    }
            },
            {
                searchPanes: { show: true }, targets: 4, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        let select = '<select class="AjaxUpdate" data-pignoramento_id="'+row.v_elab_acts_pignoramenti.Pignoramento_ID+'" data-cc="'+row.v_elab_acts_pignoramenti.CC+'"  data-field="Printer_Id" data-notifica="'+row.v_elab_acts_pignoramenti.Notifica_ID+'">';
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
                searchPanes: { show: true }, targets: 5, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        let select = '<select class="AjaxUpdate"  data-pignoramento_id="'+row.v_elab_acts_pignoramenti.Pignoramento_ID+'" data-cc="'+row.v_elab_acts_pignoramenti.CC+'" data-field="PrintTypeId" data-notifica="'+row.v_elab_acts_pignoramenti.Notifica_ID+'">';
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
                searchPanes: { show: true }, targets: 6, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        let select = '<select class="AjaxUpdate"  data-pignoramento_id="'+row.v_elab_acts_pignoramenti.Pignoramento_ID+'" data-cc="'+row.v_elab_acts_pignoramenti.CC+'" data-field="Tipo_Ufficiale" data-notifica="'+row.v_elab_acts_pignoramenti.Notifica_ID+'">';
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
                searchPanes: { show: false }, targets: 7, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        a_check = checkPec(row.v_elab_acts_pignoramenti.CF_PI, row.v_elab_acts_pignoramenti.REC_PRESSO, row.v_elab_acts_pignoramenti.PEC, row.v_elab_acts_pignoramenti.InipecLoaded);
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
            updateNotificaAttoField($(this).data('notifica'), $(this).data('field'), $(this).val(),$(this).data('cc'),$(this).data('pignoramento_id'));
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

    function updateNotificaAttoField(notifica, field, value,cc,pignoramento_id) {
        $.ajax({
            url: '../ajax/ajax_update_notifica_atto.php',
            type: 'POST',
            data: {
                'notificaAtto': notifica,
                'field': field,
                'value': value,
                'cc' : cc,
                'pignoramento_id' : pignoramento_id
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

    
