function controlla_assenti(elab_id)
{
    var ret = -1;
    $.ajax({
        type: "POST",
        async: false,
        url: "ajax/ajax_controlla_assenti.php",
        data: {
            elab_id: elab_id,
            c:c
        },
        success: function(response) {
            var response = JSON.parse(response);
             if (response.esito == "OK") {
                ret = response.numero_assenti;
             }
         
        }
    });
    return ret;
}