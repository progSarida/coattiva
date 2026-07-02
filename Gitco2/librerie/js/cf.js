function strrpos (haystack, needle, offset) {

    // POSIZIONE SOTTOSTRINGA
    // *     example 1: strrpos('Kevin van Zonneveld', 'e');
    // *     returns 1: 16
    // *     example 2: strrpos('somepage.com', '.', false);
    // *     returns 2: 8
    // *     example 3: strrpos('baa', 'a', 3);
    // *     returns 3: false
    // *     example 4: strrpos('baa', 'a', 2);
    // *     returns 4: 2
    var i = -1;
    if (offset) {
        i = (haystack + '').slice(offset).lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
        // while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
        if (i !== -1) {
            i += offset;
        }
    } else {
        i = (haystack + '').lastIndexOf(needle);
    }
    return i >= 0 ? i : false;
}

//DECODIFICA CODICE FISCALE
function decode_CF( CF )
{
    var array_CF = new Array();

    var alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    var numeri = "0123456789";
    var alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
    var numeri_disp = "10   2 3 4   5 6 7 8 9";
    var lettere_omocodia = "LMNPQRSTUV";
    var lettere_mesi = "ABCDEHLMPRST";
    var checkOmocodia = 0;

    var cognome = CF.substr(0, 3);
    array_CF['cognome'] = cognome;

    var nome = CF.substr(3, 3);
    array_CF['nome'] = nome;

    var annoStr = CF.substr(6, 2);
    var anno = "";
    for(var i=0;i<annoStr.length;i++){
        var element = annoStr.substr(i,1);
        if (element.match(/[0-9]/)!=null){
            anno+= element;
        }
        else{
            checkOmocodia = 1;
            anno+= strrpos(lettere_omocodia, element);
        }
    }
    var anno_int = parseInt(anno);

    var mese = CF.substr(8, 1);
    mese = String(lettere_mesi.indexOf(mese)+1);
    var mese_int = parseInt(mese);

    var giornoStr = CF.substr(9, 2);
    var giorno = "";
    for(var i=0;i<giornoStr.length;i++){
        element = giornoStr.substr(i,1);
        if (element.match(/[0-9]/)!=null){
            giorno+= element;
        }
        else{
            checkOmocodia = 1;
            giorno+= strrpos(lettere_omocodia, element);
        }
    }
    var giorno_int =  parseInt(giorno);
    var controllo = CF.substr(15, 1);

    if(giorno_int > 40)
    {
        array_CF['sesso'] = "F";
        giorno = String(giorno_int - 40);
    }
    else
    {
        array_CF['sesso'] = "M";
    }

    if(giorno.length<2)		array_CF['giorno'] = "0"+giorno;
    else					array_CF['giorno'] = giorno;

    if(mese.length<2)		array_CF['mese'] = "0"+mese;
    else					array_CF['mese'] = mese;

    var data_odierna = new Date();
    var anno_odierno = String(data_odierna.getFullYear());
    var pref_anno = anno_odierno.substr(0,2);
    var pref_anno_int = parseInt(pref_anno);
    var post_anno = anno_odierno.substr(2,2);
    var post_anno_int = parseInt(post_anno);

    if(anno - post_anno_int >= -5 )
        pref_anno = String( pref_anno_int - 1 );

    array_CF['anno'] = pref_anno + anno;
    array_CF['data'] = array_CF['giorno']+"/"+array_CF['mese']+"/"+array_CF['anno'];

    var ccStr = CF.substr(12,3);
    var CC = CF.substr(11,1);
    for(var i=0;i<ccStr.length;i++){
        element = ccStr.substr(i,1);
        if (element.match(/[0-9]/)!=null){
            CC+= element;
        }
        else{
            checkOmocodia = 1;
            CC+= strrpos(lettere_omocodia, element);
        }
    }

    array_CF['CC'] = CC;

    $.ajax({
        type: "POST",
        async: false,
        url: "ajax/ajax_anagrafe.php",
        data: {
            CC_CF: array_CF['CC'],
            inutile: "inutile"
        },

        success: function(value) {
            array_ritorno = value.split('**');
            array_CF['stato'] = array_ritorno[0];
            array_CF['comune'] = array_ritorno[1];
        }
    });

    array_CF['omocodia'] = checkOmocodia;

    var sommaCod = 0;
    for(var i=0;i<CF.length-1;i++){
        char = CF.substr(i,1);
        if((i%2)==0)
            sommaCod+= strrpos(numeri_disp,char) + strrpos(alfabeto_disp,char);
        else
            sommaCod+= strrpos(numeri,char) + strrpos(alfabeto,char);
    }

    array_CF['codiceControllo'] = alfabeto.substr((sommaCod%26),1);
    if(array_CF['codiceControllo']!=CF.substr(15,1)){
        alert("ATTENZIONE! Carattere di controllo finale del Codice Fiscale errato!");
        array_CF['CF'] = null;
    }
    else
        array_CF['CF'] = CF;
    return array_CF;
}