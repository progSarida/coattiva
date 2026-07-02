// Gestione AJAX Ruolo in base all'EMTE comune a start_elaboration e start_pignoramento
// GF 2023-01-02

$(document).ready(function() {
    $('#ente').on('change', function (e) {
        var optionSelected = $("option:selected", this);
        var valueSelected = this.value;
        $.ajax({

            url: '../ajax/ajax_nr_partita_tributi.php',
            type: 'POST',
            data: {
                    'cod_cat': valueSelected,                    
                },
            cache: false,
            success: function(response) {

                var response = JSON.parse(response);
                $('#da_n_elenco').html("");
                $('#a_n_elenco').html("");

                if (response.esito == "OK") {
                    
                    $('#da_n_elenco').append("<option></option>");
                    response.message.forEach(function(partita) {
                    $('#da_n_elenco').append("<option value='"+partita.Comune_ID+"'>"+partita.Comune_ID+"</option>");
                    });

                    $('#a_n_elenco').append("<option></option>");
                    response.message.forEach(function(partita) {
                    $('#a_n_elenco').append("<option value='"+partita.Comune_ID+"'>"+partita.Comune_ID+"</option>");
                    });

                } else {
                    console.log(response.message);
                }
            },
            error: function(error) {
                console.log(error)
            }

        });
        $.ajax({

            url: '../ajax/ajax_ruolo.php',
            type: 'POST',
            data: {
                'cod_cat': valueSelected,                    
            },
            cache: false,
            success: function(response) {

            var response = JSON.parse(response);
            $('#ruolo').html("");

            if (response.esito == "OK") {
                
                $('#ruolo').append("<option></option>");
                response.message.forEach(function(ruolo) {
                    var result=ruolo.CC + "-" + ruolo.Descrizione + "-" + ruolo.Data_Inserimento;
                $('#ruolo').append("<option value='"+ruolo.ID+"'>"+result+"</option>");
                });


            } else {
                console.log(response.message);
            }
            },
            error: function(error) {
            console.log(error)
            }

            });

    });
});