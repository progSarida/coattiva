<script>

//Variabili


//invio richiesta elenco paesi
function startAjax(type){
    switch (type){
        // ricerca Paese
        case 'state':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectState.php",                                  // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    state: $("#state").val()                                    // parametro ricerca inserito
                },
                success: function(response){
                    //response = JSON.parse(response)
                    //console.log(response);
                    //$("#appendTableState").html(response);          // ??????
                    //alert(response);
                    var toprint = [
                        {originalName: "CC", replacedName: "Codice Paese"},
                        {originalName: "paese", replacedName: "Nome Paese"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["25%","60%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableState";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca comune
        case 'city':
            //alert("<?= WEB_ROOT ?>/search_modal/ajax/selectCity.php");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectCity.php",                                   // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    city: $("#city").val()                                      // parametro ricerca inserito
                },
                success: function(response){
                    var toprint = [
                        {originalName: "nome", replacedName: "Comune"},
                        {originalName: "cap", replacedName: "Cap"},
                        {originalName: "prov", replacedName: "Provincia"},
                        {originalName: "CC_C", replacedName: "Cod. Com."},
                        {originalName: "CC_P", replacedName: "Cod. Prov."},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","15","15","15%","15%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableCity";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca indirizzo cappato
        case 'addr_cap':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectAddrCap.php",
                type: "POST",
                dataType: "json",
                data: {
                    city_cc: $('#CC').val(),                         // Parametro di ricerca: cod catastale comune cappato
                    addr_c : $('#addr_c').val()                      // Parametro di ricerca: inserimento utente
                },
                success: function(response) {
                    if ($.isEmptyObject(response)===true){
                        //$(".offcanvas").modal("hide"); -->  no
                        //alert errore no indirizzo
                        if(confirm("Non è stato trovato nessun indirizzo corrispondente nel comune selezionato. Procedere con ricerca generica?")){
                            //Va alla ricerca generica
                            // addr_S valorizzata per via e non cap
                            addr_S = 'via';
                            // disabilita radio cap
                            document.getElementById('check_cap').checked = false;
                            document.getElementById('check_cap').disabled = true;
                            document.getElementById('check_cap_label').style.color = "#999999";
                            // seleziona radio via
                            document.getElementById('check_gen').checked = true;
                            // scatena evento onclick sul radio via
                            // nasconde ricerca cappata -->
                            document.getElementById('addrSearchModalLabel_c').hidden = true;
                            document.getElementById('ins_addr_c').hidden = true;
                            // mostra ricerca generica -->
                            document.getElementById('addrSearchModalLabel_nc').hidden = false;
                            document.getElementById('ins_addr_nc').hidden = false;
                            // Cambio tipo di ricerca -->
                            addr_S = 'via';
                        }
                        else{
                            //Torna alla ricerca cappata;
                        }
                    }
                    else {
                        var toprint = [
                            {originalName: "nome_via", replacedName: "Indirizzo"},
                            {originalName: "civici", replacedName: "Civici"},
                            {originalName: "cap", replacedName: "CAP"},
                            {originalName: "select", replacedName: ""},
                            {originalName: "action_row", replacedName: "", type: "action"}
                        ];
                        var widthCell = ["55%", "30%", "10%", "5%"];
                        var fontsize = "12px";
                        var idTable = "appendTableAddr";
                        var test = new TableGenerator(response, toprint, widthCell, fontsize, idTable);
                    }
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca indirizzo generico
        case 'addr_gen' :
            //alert("Ricerca addr");                                  // Controllo chiamata
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectAddrGen.php",
                type: "POST",
                dataType: "json",
                data: {
                    city_cc: $('#CC').val(),                            // Parametro di ricerca: cod catastale comune
                    admin: adminCity,                                   // Ente?
                    addr_g: $('#addr_g').val()                          // Parametro di ricerca: inserimento utente
                },
                success: function(response) {
                    if ($.isEmptyObject(response)===true){
                        //$(".offcanvas").modal("hide"); -->  no
                        //alert errore no indirizzo
                        if(confirm("Non è stato trovato nessun indirizzo corrispondente nel comune selezionato. Procedere con inserimento manuale?")){
                            //Permette l'inserimento diretto
                            //Chiudo modale
                            $(".offcanvas").modal("hide");
                            //Setto i campi della pagina per l'inserimento
                            $('#ID_via_cap').val(1);
                            $('#ID_via').val(0);
                            $('#cap').attr('readonly', false);
                            $('#via').attr('readonly', false);
                            $('#via').val(null);
                            $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
                            $('#via').css("background-color","");
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);
                            alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.' +
                                '\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
                            document.getElementById("via").dispatchEvent(new Event("change"));
                            $('#via').focus();
                        }
                        else{
                            //Torna alla ricerca generica;
                        }
                    }
                    else {
                        var toprint = [
                            {originalName: "nome_via", replacedName: "Indirizzo"},
                            {originalName: "comune", replacedName: "Comune"},
                            {originalName: "cap", replacedName: "CAP"},
                            {originalName: "select", replacedName: ""},
                            {originalName: "action_row", replacedName: "", type: "action"}
                        ];
                        var widthCell = ["55%", "30%", "10%", "5%"];
                        var fontsize = "12px";
                        var idTable = "appendTableAddr";
                        var test = new TableGenerator(response, toprint, widthCell, fontsize, idTable);
                    }
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca ruolo per descrizione
        case 'desc':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectRoleDesc.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    admin: adminCity,                                           // ente
                    desc: $("#desc").val()                                      // parametro ricerca inserito
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Descrizione"},
                        {originalName: "Data_Fornitura", replacedName: "Data",type:"date"},
                        {originalName: "Ruolo", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["60%","20%","10%","10%"];
                    var fontsize = "12px";
                    var idTable = "appendTableRole";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca ruolo per anno fornitura
        case 'year':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectRoleYear.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    admin: adminCity,                                           // ente
                    year: $("#year").val()                                      // parametro ricerca inserito
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Descrizione"},
                        {originalName: "Data_Fornitura", replacedName: "Data",type:"date"},
                        {originalName: "Ruolo", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["60%","20%","10%","10%"];
                    var fontsize = "12px";
                    var idTable = "appendTableRole";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
            break;
        // ricerca intestatario per nome
        case 'name':
            //alert("NAME");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectOwnerName.php",               // url pagina che farà query
                type: "POST",                                                               // create an ajax request to display.php
                dataType: "json",                                                           // expect json to be returned
                data: {
                    name: $("#name").val(),                                                 // parametro ricerca inserito
                    admin: adminCity
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableOwner";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca intestatario per cf
        case 'cf':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectOwnerCF.php",                 // url pagina che farà query
                type: "POST",                                                               // create an ajax request to display.php
                dataType: "json",
                data: {
                    cf: $("#cf").val(),                                                     // parametro ricerca inserito
                    admin: adminCity
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableOwner";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);// expect json to be returned
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // lista codici tributo
        case 'list':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectTributeList.php",                 // url pagina che farà query
                type: "POST",                                                               // create an ajax request to display.php
                dataType: "json",                                                           // expect json to be returned
                data: {

                },
                success: function(response){
                    // controllo successo: OK
                    console.log(response);

                    //Tentativo 2: inserisce nel <body> un record ritrnato alla volta: OK

                    $.each(response, function (key, value) {
                        $('#list').append("<tr>\
										<td>"+value.Codice_Tributo+"</td>\
										<td>"+value.Settore+"</td>\
										<td>"+value.Descrizione+"</td>\
										<td>"+value.Autorita_Ricorso+"</td>\
										</tr>");
                    })
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca codice tributo per descrizione
        case 'c_desc':
            //alert("OK");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectCodeDesc.php",                // url pagina che farà query
                type: "POST",                                                               // create an ajax request to display.php
                dataType: "json",                                                           // expect json to be returned
                data: {
                    area: $('#tipo_partita').val(),                                         // settore
                    sub_area: $('#sottotipo_partita').val(),                                // sottosettore
                    desc: $('#ricDesc').val()                                               // parametro ricerca inserito
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Codice_Tributo", replacedName: "Codice"},
                        {originalName: "Settore"},
                        {originalName: "Descrizione"},
                        {originalName: "Tipo_Codice", replacedName: "Tipo Codice"},
                        {originalName: "Autorita_Ricorso", replacedName: "Autorità"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["7%","20","45","15%","13%"];
                    var fontsize = "12px";
                    var idTable = "appendTableCode";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca codice tributo per codice
        case 'code':
            //alert("OK");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectCodeNum.php",                 // url pagina che farà query
                type: "POST",                                                               // create an ajax request to display.php
                dataType: "json",                                                           // expect json to be returned
                data: {
                    area: $('#tipo_partita').val(),                                         // settore
                    sub_area: $('#sottotipo_partita').val(),                                // sottosettore
                    code: $('#ricCode').val()                                               // parametro ricerca inserito
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Codice_Tributo", replacedName: "Codice"},
                        {originalName: "Settore"},
                        {originalName: "Descrizione"},
                        {originalName: "Tipo_Codice", replacedName: "Tipo Codice"},
                        {originalName: "Autorita_Ricorso", replacedName: "Autorità"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["7%","20","45","15%","13%"];
                    var fontsize = "12px";
                    var idTable = "appendTableCode";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca utente per nome F9 dati_soggetto.php
        case 'u_name':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectUserName.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    user_name: $("#user_name").val(),                           // parametro ricerca inserito
                    admin: adminCity,
                    all_city: all_city
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUser";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca utente per cf F9 dati_soggetto.php
        case 'u_cf':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectUserCF.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    user_cf: $("#user_cf").val(),                           // parametro ricerca inserito
                    admin: adminCity,
                    all_city: all_city
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUser";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca utente per nome F9 gestione_partita.php
        case 'user_n':
            //alert("ricerca user per nome");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectUserNameJ.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    user_name: $("#u_n").val(),                           // parametro ricerca inserito
                    admin: adminCity,
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "Ins", replacedName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["15","40%","10","30%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUserEntry";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca utente per cf F9 gestione_partita.php
        case 'user_c':
            //alert("ricerca user per cf");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectUserCFJ.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    user_cf: $("#u_c").val(),                           // parametro ricerca inserito
                    admin: adminCity,
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "Ins", replacedName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["15","40%","10","30%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUserEntry";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca partita per cronologico atto F9 gestione_partita.php
        case 'entry_chronoA':
            //alert("ricerca user per cf");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectChronoEntry.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    proto: $("#e_cA_P").val(),                           // parametro ricerca inserito
                    chrono: $("#e_cA_C").val(),
                    year: $("#e_cA_Y").val(),
                    admin: adminCity,
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "Info_Cartella", replacedName: "Informazioni cartella"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["15%","80","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUserEntry";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca partita per cronologico pignoramento F9 gestione_partita.php
        case 'entry_chronoP':
            //alert("ricerca user per chrono");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectChronoFore.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    proto: $("#e_cP_P").val(),                           // parametro ricerca inserito
                    chrono: $("#e_cP_C").val(),
                    year: $("#e_cP_Y").val(),
                    admin: adminCity,
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Ins", replacedName: "Cronologico"},
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "Tipo_pignoramento", replacedName: "Tipo pignoramento"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["25%","15%","55%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUserEntry";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca partita per informazioni cartella F9 gestione_partita.php
        case 'entry_info':
            //alert("ricerca user per cf");
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectInfoEntry.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    info: $("#e_i").val(),                           // parametro ricerca inserito
                    admin: adminCity,
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "Info_Cartella", replacedName: "Informazioni cartella"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["15%","80","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUserEntry";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca sede banca
        case 'bank_headq':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectBankHeadQ.php",        // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    denominazione: $('#bank_n').val(),                          // input denominazione banca
                    comune_banca: $('#bank_c').val(),                           // input comune banca
                    cap_banca: $('#bank_cap').val(),
                    PI_CF_banca: $('#bank_PI_CF').val(),
                    disabled: $("#search_disabled_bank").is(":checked")?1:0,                            // input cap banca
                    c : '*****',                                                //
                    admin: adminCity                                            //
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Denominazione", replacedName: "Banca"},
                        {originalName: "Codice_Fiscale", replacedName: "Codice Fiscale"},
                        {originalName: "Partita_Iva", replacedName: "Partita IVA"},
                        {originalName: "Tipo_Banca", replacedName: "Tipo"},
                        {originalName: "Comune"},
                        {originalName: "Cap"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","13%","13%","10%","14%","10%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableBank";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca filiale banca
        case 'bank_branch':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectBankBranch.php",        // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    denominazione: $('#bank_n').val(),                          // input denominazione banca
                    comune_banca: $('#bank_c').val(),                           // input comune banca
                    cap_banca: $('#bank_cap').val(), 
                    PI_CF_banca: $('#bank_PI_CF').val(),
                    disabled: $("#search_disabled_bank").is(":checked")?1:0,                           // input cap banca
                    c : '*****',                                                //
                    admin: adminCity                                            //
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Denominazione", replacedName: "Banca"},
                        {originalName: "Codice_Fiscale", replacedName: "Codice Fiscale"},
                        {originalName: "Partita_Iva", replacedName: "Partita IVA"},
                        {originalName: "Tipo_Banca", replacedName: "Tipo"},
                        {originalName: "Comune"},
                        {originalName: "Cap"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","13%","13%","10%","14%","10%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableBank";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca ente previdenziale
        case 'welfare':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectWelfare.php",     // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    denominazione_ente_prev: $('#welfare_n').val(),             // input denominazione ente previdenziale
                    comune_ente_prev: $('#welfare_c').val(),                    // input comune ente previdenziale
                    cap_ente_prev: $('#welfare_cap').val(),                     // input cap ente previdenziale
                    c : '*****',                                                //
                    admin: adminCity                                            //
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Denominazione", replacedName: "Ente"},
                        {originalName: "Partita_Iva", replacedName: "Partita IVA"},
                        {originalName: "Tipo"},
                        {originalName: "Comune"},
                        {originalName: "Cap"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["45%","15%","10%","15%","10%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableWelfare";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca ufficio anagrafico
        case 'registry':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectRegistry.php",     // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    city_name: $('#registry_n').val(),                          // input comune
                    admin: adminCity                                            //
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Comune", replacedName: "Comune"},
                        {originalName: "CC", replacedName: "Codice"},
                        {originalName: "Denominazione", replacedName: "Informazioni"},
                        {originalName: "Ins", replacedName: "Indirizzo"},
                        {originalName: "Provincia", replacedName: "Provincia"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["27%","8%","15%","35%","10%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableRegistry";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca ufficio postale
        case 'mail':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectMail.php",     // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    city_name: $('#mail_n').val(),                          // input comune
                    admin: adminCity                                            //
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Comune", replacedName: "Comune"},
                        {originalName: "CC", replacedName: "Codice"},
                        {originalName: "Denominazione", replacedName: "Informazioni"},
                        {originalName: "Ins", replacedName: "Indirizzo"},
                        {originalName: "Provincia", replacedName: "Provincia"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["27%","8%","15%","35%","10%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableMail";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca autorità
        case 'judge':
        case 'court':
        case 'tax_prov':
        case 'tax_reg':
        case 'appeal':
        case 'scoi':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectAuthority.php",   // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    city_name: $('#authority_c').val(),                         // input comune
                    c: "*****",
                    type: type
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Comune"},
                        {originalName: "Sezione"},
                        {originalName: "Ins", replacedName: "Indirizzo"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","15%","50%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableAuthority";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        case 'all':
        case 'person':
        case 'business':
            var surname = '';
            var name = '';
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectUserNameSel.php", // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    surname: $('#sel_surn').val(),                              // input cognome
                    business: $('#sel_surn').val(),                             // input ditta
                    name: $('#sel_name').val(),                                 // input nome
                    admin: adminCity,                                           // codice catastale ente
                    type: type                                                  // tipo utente
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Ins", replacedName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "Comune_ID", replacedName: "ID"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["50%","15%","15%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUserSel";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca ditta per nome datore lavoro assegnazione_terzi.php
        case 'c_name':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectCompanyName.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    company_name: $("#company_name").val(),                           // parametro ricerca inserito
                    admin: adminCity,
                    all_city: all_city
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableCompany";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca ditta per cf datore lavoro assegnazione_terzi.php
        case 'c_cf':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectCompanyCF.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    company_cf: $("#company_cf").val(),                           // parametro ricerca inserito
                    admin: adminCity,
                    all_city: all_city
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableCompany";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca utente per nome sospensione
        case 'u_sosp_name':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectUserNameSosp.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    user_name: $("#user_name").val(),                           // parametro ricerca inserito
                    city: $("#CC").val()
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUser";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
        // ricerca utente per cf sospensione
        case 'u_sosp_cf':
            $.ajax({
                url: "<?= WEB_ROOT ?>/search_modal/ajax/selectUserCFSosp.php",    // url pagina che farà query
                type: "POST",                                                   // create an ajax request to display.php
                dataType: "json",                                               // expect json to be returned
                data: {
                    user_cf: $("#user_cf").val(),                           // parametro ricerca inserito
                    city: $("#CC").val()
                },
                success: function(response){
                    var toprint = [
                        {originalName: "Utente"},
                        {originalName: "Genere", replacedName: "Tipo"},
                        {originalName: "ID"},
                        {originalName: "CC_Comune", replacedName: "CC"},
                        {originalName: "CF", replacedName: "CF/P.IVA"},
                        {originalName: "select", replacedName: ""},
                        {originalName: "action_row", replacedName: "", type: "action"}
                    ];
                    var widthCell = ["35%","10","15","10%","25%","5%"];
                    var fontsize = "12px";
                    var idTable = "appendTableUser";
                    var test = new TableGenerator(response,toprint,widthCell,fontsize,idTable);
                },
                error: function(risposta){
                    alert("Si è verificato un errore: " + risposta.status + " " + risposta.statusText);
                }
            });
            break;
    }
}
</script>