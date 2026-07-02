
    $('#dt_table_'+elab_list_id).DataTable( {
        dom: 'lfrtip',
        processing: true,
        serverSide: true,
        searchPanes: {
            orderable: false,
            initCollapsed: true
        },
        ajax: {
            url: web_dteditor+"/ss_elab_status_11.php",
            type: "POST",
            data: {
                Elaboration_List_Id: elab_list_id
            }
        },   
        "language": {
            "url": web_datatable+"/dt_IT.json"
        },
        columns: [
            { data: "v_manage_acts_pignoramenti.Comune_ID" },
            { data: "v_manage_acts_pignoramenti.ID_Cronologico" },
            { data: "v_manage_acts_pignoramenti.Anno_Cronologico" },
            { data: "v_manage_acts_pignoramenti.Tipo_Notifica" },
            { data: "v_manage_acts_pignoramenti.Info_Cartella" },
            { data: null },
            
        ],
        columnDefs: [
            {
                searchPanes: { show: false }, targets: 4, className: 'dt-head-center'
            },
            {
                searchPanes: { show: false }, width: 100, targets: 1, className: 'dt-center'
            },
            {
                searchPanes: { show: false }, width: 100, targets: 2, className: 'dt-center'
            },
            {
                searchPanes: { show: false }, width: 100, targets: 0, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        return '<a href="'+web_root+'/coattiva/coazione.php?partita='+row.v_manage_acts_pignoramenti.Partita_ID+'&c='+elab_cc+'"><b>'+data+'</b></a>';
                    }
            },
            {
                searchPanes: { show: false }, width: 60, searchable: false, orderable: false, targets: 5, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        filesubstring = 
                        row.v_manage_acts_pignoramenti.PrefixName+"_"+
                        row.v_manage_acts_pignoramenti.CC+"_"+
                        row.v_manage_acts_pignoramenti.Anno_Cronologico+"_"
                        +row.v_manage_acts_pignoramenti.ID_Cronologico+"_"+row.v_manage_acts_pignoramenti.ID;
                        if(row.v_manage_acts_pignoramenti.Tipo_Notifica=="terzi")
                            tipo = "_terzo";
                        else
                            tipo = "_"+row.v_manage_acts_pignoramenti.Tipo_Notifica;

                        filesubstringRelata = row.v_manage_acts_pignoramenti.PignoID + "/" +filesubstring + "_Relata"+tipo;
                        filesubstringCopia = row.v_manage_acts_pignoramenti.PignoID + "/" +filesubstring+"_Copia"+tipo;
                        
                        
                        filePath = act_file_path+"/"+filesubstringCopia+".pdf";
                        relatafilePath = act_file_path+"/"+filesubstringRelata+".pdf";
                        signedFilePath = act_file_path+"/"+filesubstringCopia+"_signed.pdf";
                        relatasignedFilePath = act_file_path+"/"+filesubstringRelata+"_signed.pdf";
                        deliveryReceipt = pec_file_path+"/PEC_"+filesubstring+"__CONSEGNA.eml";
                        nodeliveryReceipt = pec_file_path+"/PEC_"+filesubstring+"__AVVISODIMANCATACONSEGNA.eml";
                        crono = row.v_manage_acts_pignoramenti.ID_Cronologico+'/'+row.v_manage_acts_pignoramenti.Anno_Cronologico;
                        
                        returnHtml = "";
                        if(fileExists(signedFilePath)){
                            returnHtml += '<i title="PDF firmato '+crono+' '+row.v_manage_acts_pignoramenti.Tipo_Notifica+'" class="fa-solid fa-file-signature fa-lg" style="color:darkred; cursor:pointer;" aria-hidden="true" data-toggle="modal" data-target="#act-pdf" onclick="openPdf(\''+signedFilePath+'\');"></i>';
                            
                        }
                        else if(fileExists(filePath)){
                            returnHtml += '<i title="PDF '+crono+' '+row.v_manage_acts_pignoramenti.Tipo_Notifica+'" class="fas fa-file-pdf fa-lg" style=" color:darkred; cursor:pointer;" aria-hidden="true" data-toggle="modal" data-target="#act-pdf" onclick="openPdf(\''+filePath+'\');"></i>';
                        }
                        returnHtml +="<span> </span>";
                        if(fileExists(relatasignedFilePath)){
                            returnHtml += '<i title="PDF Relata firmata '+crono+' '+row.v_manage_acts_pignoramenti.Tipo_Notifica+'" class="fa-solid fa-file-signature fa-lg" style="color:darkred; cursor:pointer;" aria-hidden="true" data-toggle="modal" data-target="#act-pdf" onclick="openPdf(\''+relatasignedFilePath+'\');"></i>';
                            if(fileExists(deliveryReceipt))
                                returnHtml += '<a title="Ricevuta di consegna '+crono+' '+row.v_manage_acts_pignoramenti.Tipo_Notifica+'" href="'+deliveryReceipt+'" class="fa-solid fa-check fa-lg" style="color:darkgreen; cursor:pointer; margin-left: 5px;" download></a>';
                            else if(fileExists(nodeliveryReceipt))
                                returnHtml += '<a title="Ricevuta di mancata consegna '+crono+' '+row.v_manage_acts_pignoramenti.Tipo_Notifica+'" href="'+nodeliveryReceipt+'" class="fa-solid fa-exclamation fa-lg" style="color:darkred; cursor:pointer; margin-left: 5px;" download></a>';
        
                        }
                        else if(fileExists(relatafilePath)){
                            returnHtml += '<i title="PDF Relata '+crono+' '+row.v_manage_acts_pignoramenti.Tipo_Notifica+'" class="fas fa-file-pdf fa-lg" style=" color:darkred; cursor:pointer;" aria-hidden="true" data-toggle="modal" data-target="#act-pdf" onclick="openPdf(\''+relatafilePath+'\');"></i>';
                        }

                        return returnHtml;
                    }
            },
        ]
    } );

    function checkValidation(el_list_Id) {
        let checked = 0;
        let printLabel = "PROVVISORIA";
        let printType = "temp";
        if ($('#chk_press_' + el_list_Id).is(":checked")) {
            checked = 1;
            printLabel = "DEFINITIVA";
            printType = "final";
        }

        $.ajax({
            url: '../ajax/ajax_update_PrintFlag.php',
            type: 'POST',
            data: {
                'el_list_Id': el_list_Id,
                'checked': checked
            },
            cache: false,
            async: false,
            success: function(response) {
                console.log(response);
                var response = JSON.parse(response);

                if (response.esito == "OK") {
                    swal({
                        title: "UPDATE!",
                        text: response.message,
                        icon: "success",
                        timer: 3000,
                        buttons: false
                    }).then(function() {
                        $('#press_button_' + el_list_Id).html("<i class='fa fa-print' style='margin-right: 10px;'></i>" + printLabel);
                        $('#printType_' + el_list_Id).val(printType);
                    });
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

    function printButton(el_list_Id) {
        if (authFlag == 0) {
            if ($('#chk_press_' + el_list_Id).is(":checked")) {
                alert("Stampa definitiva non autorizzata!");
                return true;
            }
        }
        if (authFlag != 1) 
        {
                alert("Stampa definitiva non autorizzata!");
                return true;
        }
        $.ajax({
            url: '../ajax/ajax_update_TempFlag.php',
            type: 'POST',
            data: {
                'el_list_Id': el_list_Id,
            },
            cache: false,
            async: false,
            success: function(response) {
                var response = JSON.parse(response);
                if (response.esito == "OK") {
                    location.href = web_root+"/elaborazioni/pignoramenti_lavoro/elab_printing_pignoramento_lavoro.php?c="+c+"&a="+a+"&el_list_id="+el_list_Id;
                } else {
                    console.log(response);
                    swal({
                        title: "ERROR!",
                        text: response.message,
                        icon: "danger",
                        timer: 10000,
                        buttons: false
                    })
                }
            },
            error: function(error) {
                console.log(error)
            }
        });

    }

    function openPdf(filePath){
        $('#pdf-frame').attr('src',filePath);

    }

    function flowCreation(el_list_Id) {
        if (authFlag == 0) {
            alert("Creazione flusso non autorizzata!");
            return true;
        } else {
            location.href = web_root+"/elaborazioni/pignoramenti/elab_pigno_flows.php?c="+c+"&a="+a+"&el_list_id="+el_list_Id+"&terzo=lavoro";
        }
    }

    function sendPec(el_list_Id) {
        if (authFlag == 0) {
            alert("Spedizione pec non autorizzata!");
            return true;
        } else {
            location.href = web_root+"/elaborazioni/pignoramenti/pec_send_pigno.php?c="+c+"&a="+a+"&Elaboration_Id="+elab_id+"&Elaboration_List_Id="+el_list_Id+"&terzo=lavoro";
        }
    }

    function checkPec(el_list_Id) {
        if (authFlag == 0) {
            alert("Spedizione pec non autorizzata!");
            return true;
        } else {
            location.href = web_root+"/elaborazioni/pignoramenti/pec_download_receipts_pigno.php?c="+c+"&a="+a+"&Elaboration_Id="+elab_id+"&Elaboration_List_Id="+el_list_Id+"&terzo=lavoro";
        }
    }

    function closeFlow(el_list_Id) {
        if (authFlag == 0) {
            alert("Chiusura flusso non autorizzata!");
            return true;
        }

        $.ajax({
            url: web_root+"/elaborazioni/pignoramenti/elab_closing_flow_pignoramento.php?c="+c+"&a=",
            type: 'POST',
            data: {
                'el_list_Id': el_list_Id,
            },
            cache: false,

            success: function(response) {
                console.log(response);
                var response = JSON.parse(response);

                if (response.esito == "OK") {
                    swal({
                        title: "UPDATE!",
                        text: response.message,
                        icon: "success",
                        timer: 3000,
                        buttons: false
                    }).then(function() {
                        location.reload();
                    });
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

    function fileExists(url) {
        var req = new XMLHttpRequest();
        req.open('HEAD', url, false);
        req.send();
        return req.status !== 404;
    }
