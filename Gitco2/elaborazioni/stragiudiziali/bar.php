<?php ?>
<!-- JS SWEETALERT  START -->

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<!-- JS sweetalert    END -->
<script>
function startBar() {
    $('#progressbar').progressbar({
        value: false
    });
    $("#barlabel").text("Inizio elaborazione...");
}

function updateBar(valore) {
    $("#progressbar").progressbar({
        value: parseInt(valore)
    });
    $("#barlabel").text(valore + "%");
}

function noResultsBar() {
    $("#progressbar").progressbar({
        value: 100
    });
    $("#barlabel").text("Nessun risultato trovato");
}
function endBarMsg(titolo,msg)
{
    $( "#progressbar" ).progressbar({value: 100 });
    $( "#barlabel" ).text(titolo + ":" + msg);
}
function endBarErr(titolo,msg)
{
    $( "#progressbar" ).progressbar({value: 100 });
    $( "#barlabel" ).css({"background-color":"red","color":"yellow"})
    $( "#barlabel" ).text(titolo + ":" + msg);
    $( "#spanTermine").html('<a href="#"><-Torna Indietro!</a>');
    $("#spanTermine").click( function() {
        window.history.back();
    });
}
function endBar(c,a,el,tipo='Banca',tipo_partita=''){
    
    $( "#progressbar" ).progressbar({value: 100 });
    $( "#barlabel" ).text("Elaborazione terminata!");

   if(el !== null){
    swal({
                    title: 'ATTENZIONE',
                    text: "PROCESSO TERMINATO. STAI PER ESSERE REINDIRIZZATO ALLA PAGINA DEI RISULTATI OTTENUTI",
                    icon: 'success',
                    timer: 3000,
                    buttons: false
                }).then((result) => {
    location.href ="<?= ELAB_STRAGIUDIZIALI_WEB ?>/mgmt_stragiudiziali.php?c="+c+"&a="+a+"&pr="+el+"&tipo="+tipo+"&tipo_partita="+tipo_partita;
})
   }else{
            
            swal({
                    title: 'ATTENZIONE',
                    text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                    icon: 'warning',
                    timer: 3000,
                    buttons: false
                }).then((result) => {
                
                    location.href ="<?= ELAB_STRAGIUDIZIALI_WEB ?>/mgmt_stragiudiziali.php?c="+c+"&a="+a+"&proc="+el+"&tipo="+tipo+"&tipo_partita="+tipo_partita;
                })
        }
}

</script>
<!-- HTML PROGRESS BAR  START -->
<body class="sfondo_new_gitco">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbar" style="height:55px;width:100%;"><div class="text_center" id="barlabel"></div></div>
        </div>
    </div>
    <br/><br/>
    <div class="row">
        <div class="col col-md-auto text_center">
            <span id="spanTermine" class="titolo font16 under_decor" style="color:red;">Non chiudere la finestra prima del termine della procedura</span>
        </div> 
    </div>
</body>
<!-- HTML PROGRESS BAR    END -->  
<?php ?>
