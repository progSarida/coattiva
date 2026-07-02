
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
            { data: "v_"+(tipo=="Previdenziali" ? "previdenziali":"banche")+"_stragiudiziali.PEC" },
            { data: null }
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
            },
            
            {
                searchPanes: { show: false }, targets: 3, className: 'dt-center'
            },
            {
                searchPanes: { show: false }, width: 60, searchable: false, orderable: false, targets: 4, className: 'dt-center',
                render: function ( data, type, row, meta ) {
                        if(tipo=="Previdenziali")
                        {
                            rowID = row.v_previdenziali_stragiudiziali.ID;
                        }
                        else
                        {
                            rowID = row.v_banche_stragiudiziali.ID;
                        }
                        //fileXls = act_file_path+"/Elenco_Stragiudiziale_Banca_" + cc+"_"+ row.v_banche_stragiudiziali.ID+".xlsx";
                        fileXls = act_file_path+"/Elenco_Stragiudiziale_"+tipo+"_" +denominazioneCC+"["+ cc+"]_"+
                        rowID+".xlsx";
						filePdf = act_file_path+ 
						"/Stragiudiziale_"+tipo+"_" +denominazioneCC+"["+
                        cc+"]_"+
                        rowID+".pdf";
                        //"Stragiudiziale_Banca_" . $this->CC . "_" . $banca_id ;
                        filesubstring = "Stragiudiziale_"+tipo+"_"+cc+"_"+rowID;
                        deliveryReceipt = pec_file_path+"/PEC_"+filesubstring+"__CONSEGNA.eml";
                        nodeliveryReceipt = pec_file_path+"/PEC_"+filesubstring+"__AVVISODIMANCATACONSEGNA.eml";
                        returnHtml = "";
                        if(fileExists(fileXls)){
                            returnHtml += '<i title="Elenco Stragiudiziali" class="fa-solid fa-file-excel fa-lg" style="color:green; cursor:pointer;" aria-hidden="true" data-toggle="modal" data-target="#act-pdf" onclick="openExcel(\''+fileXls+'\');"></i>';
                            }
                        returnHtml +="<span>  </span>";
						if(fileExists(filePdf)){
                            returnHtml += '<i title="Stragiudiziale '+tipo+'" class="fa-solid fa-file-pdf fa-lg" style="color:darkred; cursor:pointer;" aria-hidden="true" data-toggle="modal" data-target="#act-pdf" onclick="openPdf(\''+filePdf+'\');"></i>';
                            
                        }
                        returnHtml +="<span>  </span>";
                        if(fileExists(deliveryReceipt))
                            returnHtml += '<a title="Ricevuta di consegna" href="'+deliveryReceipt+'" class="fa-solid fa-check fa-lg" style="color:darkgreen; cursor:pointer; margin-left: 5px;" download></a>';
                        else if(fileExists(nodeliveryReceipt))
                            returnHtml += '<a title="Ricevuta di mancata consegna" href="'+nodeliveryReceipt+'" class="fa-solid fa-exclamation fa-lg" style="color:darkred; cursor:pointer; margin-left: 5px;" download></a>';
        
                        

                        return returnHtml;
                    }
            },
        ]
    } );

    $(document).ready(function() {
        $('#dt_table').on('change', '.AjaxUpdate', function() {
            updateNotificaAttoField($(this).data('notifica'), $(this).data('field'), $(this).val());
        });
    });

    function _checkPec(CF_PI, REC_PRESSO, PEC, INIPEC_DATE){
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

    function updateNotificaAttoField(notifica, field, value) {
        $.ajax({
            url: '../ajax/ajax_update_notifica_atto.php',
            type: 'POST',
            data: {
                'notificaAtto': notifica,
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

    function sendPec(proc_id) {
        if (confirm("Vuoi spedire PEC ?"))
        {
            location.href = web_root+"/elaborazioni/stragiudiziali/pec_send_stragiudiziali.php?c="+c+"&a="+a+"&proc_id="+proc_id+"&tipo="+tipo;            
        }
        return;
        if (authFlag == 0) {
            alert("Spedizione pec non autorizzata!");
            return true;
        } else {
            location.href = web_root+"/elaborazioni/stragiudiziali/pec_send_stragiudiziali.php?c="+c+"&a="+a+"&proc_id="+proc_id+"&tipo="+tipo;
        }
    }
    function checkPec(proc_id) {
        if (confirm("Vuoi scaricare ricevute PEC ?"))
        {
            location.href = web_root+"/elaborazioni/stragiudiziali/pec_download_receipts_stragiudiziali.php?c="+c+"&a="+a+"&proc="+proc_id+"&tipo="+tipo;
        }
        return;
        if (authFlag == 0) {
            alert("Spedizione pec non autorizzata!");
            return true;
        } else {
            location.href = web_root+"/elaborazioni/stragiudiziali/pec_download_receipts_stragiudiziali.php?c="+c+"&a="+a+"&proc="+proc_id+"&tipo="+tipo;
        }
    }
    function fileExists(url) {
        var req = new XMLHttpRequest();
        req.open('HEAD', url, false);
        req.send();
        return req.status !== 404;
    }
