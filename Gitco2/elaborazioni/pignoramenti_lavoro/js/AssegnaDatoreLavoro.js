function AssegnaButtonClick(utente_id)
{
    var width = window.screen.width * 0.87;
    var height = window.screen.height * 0.96;
    var left = (window.screen.width/2)-(width/2);
    var top = (window.screen.height/2)-(height/2);

    var stile = 'width='+width+',height='+height+',top='+top+',left='+left+',scrollbars=yes, menubar=yes';
    window.open('assegnazione_terzi.php?utente_id='+utente_id+'&c='+c+'&a='+a+'&el='+elab_id,'Assegna Terzi',stile);
}