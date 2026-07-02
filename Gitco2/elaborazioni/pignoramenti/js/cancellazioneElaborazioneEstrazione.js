function CancellazioneElaborazione(els,c,a)
{
    $.ajax({
        type: "POST",
        url: "../../controlli/ajax/ajax_delete_estrazione.php",
        data: { "el" : els,},
        cache: false,
        success: function(response){        
        var response = JSON.parse(response);

        if(response.esito == "OK")
        {
            swal({
                    title: "SUCCESS!",
                    text:  response.message,
                    icon: "success",
                    timer: 25000,
                    buttons: false
                });
                window.location.href ="start_pignoramento.php?p=&c="+c+"&a="+a;
        }
        else{
                
            swal({
                    title: "ERROR!",
                    text:  response.message,
                    icon: "danger",
                    timer: 5000,
                    buttons: false
                });
               
        }

        },
        error: function(error){
        console.log(error)
        }        
            });
}

switchMenuImg("F4");
F4_button = function() {
    if (confirm('Cancellare Elaborazione?'))
        CancellazioneElaborazione(elab_id,c,a)
    
}