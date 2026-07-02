

var File =  File || {};

File.ajaxRemove = function(page, url, filePath){
    "use strict";
    $.ajax({
        url: url,
        data: {'file' : filePath },
        dataType: 'json',
        success: function (response) {
            if( response.status === true ) {
                alert('File eliminato!');
                location.reload();
            }
            else alert('Errore nella cancellazione del file!');


        }
    });
}
