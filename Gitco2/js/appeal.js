

var Appeal =  Appeal || {};
Appeal.setParams = function (cc,a){
    "use strict";
    this.cc = cc;
    this.a = a;
};

Appeal.selectAct = function(){
    "use strict";
    var ID = $('#act_id').val();

    var count = 0;
    for(var prop in a_act) {
        if(a_act.hasOwnProperty(prop))
            ++count;
    }

    for(var i=0;i<count;i++){
        if( ID==a_act[i]['ID'] ){
            var court_level = parseInt(a_act[i]['Court_Level']);
            if(isNaN(court_level))
                court_level = 0;
            $('#court_level').val((court_level+1));
            $('#court_level_display').text((court_level+1));
        }
    }
}

Appeal.getCourtHearingDOM = function(count, opt_court, opt_doctype){
    "use strict";

/*var html = "<tr class=\"tr_courtHearing\" id=\"tr_courtHearing_"+count+"\">";
    html+= "<td class=\"text_center\" ><span class=\"titolo\">"+count+"</span></td><td class=\"text_left\" >Tipo</td>";
    html+= "<td class=\"text_left\" colspan=\"2\" ><input type='hidden' name='Court_Hearing_ID["+count+"]' value='0'>";
    html+= "<select name=\"Court_Hearing_Type["+count+"]\" class=\"width95\"><option></option>"+opt_court+"</select></td>";
    html+= "<td class=\"text_left\">Data</td><td class=\"text_left\"><input name=\"Court_Hearing_Date["+count+"]\" class=\"width90 text_center picker\"></td>";
    html+= "<td class=\"text_left\">Ora</td><td class=\"text_left\"><input name=\"Court_Hearing_Time["+count+"]\" class=\"width70 text_center\"></td><td class=\"text_left\" ></td></tr>";
    html+= "<tr class=\"tr_courtHearing\"><td class=\"text_left\"><span class=\"titolo font12\">P. attrice</span></td><td class=\"text_left\" >Stato atti</td>";
    html+= "<td class=\"text_left\" colspan=\"2\"><select name=\"Plaintiff_Proceedings_State["+count+"]\" class=\"width95\"><option></option>"+opt_doctype+"</select></td>";
    html+= "<td class=\"text_left\" colspan=\"2\">Data deposito</td><td class=\"text_left\"><input name=\"Plaintiff_Docs_Date["+count+"]\" class=\"width90 text_center picker\"></td></tr>";
    html+= "<tr><td></td><td class=\"text_left\" colspan=\"3\"><input type=\"file\" class=\"button_azzurro width95\" value=\"Upload atti\" name=\"Plaintiff_Docs["+count+"]\"></td></tr>";
    html+= "<tr class=\"tr_courtHearing\"><td class=\"text_left\"><span class=\"titolo font12\">P. convenuta</span></td><td class=\"text_left\" >Stato atti</td>";
    html+= "<td class=\"text_left\" colspan=\"2\"><select name=\"Respondent_Proceedings_State["+count+"]\" class=\"width95\"><option></option>"+opt_doctype+"</select></td>";
    html+= "<td class=\"text_left\" colspan=\"2\">Data deposito</td><td class=\"text_left\"><input name=\"Respondent_Docs_Date["+count+"]\" class=\"width90 text_center picker\"></td></tr>";
    html+= "<tr><td></td><td class=\"text_left\" colspan=\"3\"><input type=\"file\" class=\"button_azzurro width95\" value=\"Upload atti\" name=\"Respondent_Docs["+count+"]\"></td></tr><tr class=\"tr_courtHearing\"><td colspan='9'><hr></td></tr>";*/

    opt_court = opt_court.replaceAll("'", "\"");
    opt_doctype = opt_doctype.replaceAll("'", "\"");

    var html = '<div class="tr_courtHearing row" id="tr_courtHearing_'+count+'">';
    html+=    '<div class="col col-lg-1 col-lg-offset-1">';
    html+=        '<div class="form-group">';
    html+=            '<div class="col-lg-12">';
    html+=                '<span class="titolo">'+count+'</span>';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+=    '<div class="col col-lg-3">';
    html+=        '<div class="form-group">';
    html+=            '<label class="col-lg-4 control-label resize">Tipo</label>';
    html+=            '<div class="col-lg-8">';
    html+=                "<input type='hidden' name='Court_Hearing_ID["+count+"]' value=''>";
    html+=                    '<select name="Court_Hearing_Type['+count+']" class="form-control resize"><option></option>'+opt_court+'</select>';
    html+=             '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+=    '<div class="col col-lg-3">';
    html+=        '<div class="form-group">';
    html+=            '<label class="col-lg-4 control-label resize">Data</label>';
    html+=            '<div class="col-lg-8">';
    html+=                '<input name="Court_Hearing_Date['+count+']" class="form-control resize text_center picker" value="">';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+=    '<div class="col col-lg-3">';
    html+=        '<div class="form-group">';
    html+=            '<label class="col-lg-4 control-label resize">Ora</label>';
    html+=            '<div class="col-lg-8">';
    html+=                '<input name="Court_Hearing_Time['+count+']" class="form-control resize text_center" value="">';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+='</div>';

    html+='<div class="tr_courtHearing row">';
    html+=    '<div class="col col-lg-1 col-lg-offset-1">';
    html+=        '<div class="form-group">';
    html+=            '<div class="col-lg-12">';
    html+=                '<span class="titolo font12">P. attrice</span>';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+=    '<div class="col col-lg-3">';
    html+=        '<div class="form-group">';
    html+=            '<label class="col-lg-4 control-label resize">Stato atti</label>';
    html+=            '<div class="col-lg-8">';
    html+=                '<select name="Plaintiff_Proceedings_State['+count+']" class="form-control resize"><option></option>'+opt_doctype+'</select>';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+=    '<div class="col col-lg-3">';
    html+=        '<div class="form-group">';
    html+=            '<label class="col-lg-4 control-label resize">Data deposito</label>';
    html+=            '<div class="col-lg-8">';
    html+=                '<input name="Plaintiff_Docs_Date['+count+']" class="form-control resize text_center picker" value="">';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+='</div>';

    html+='<div class="tr_courtHearing row">';
    html+=    '<div class="col col-lg-3 col-lg-offset-2">';
    html+=        '<div class="form-group">';
    html+=            '<div class="col-lg-12">';
    html+=                '<input type="file" style="background-color: rgb(153, 204, 255);" class="form-control resize" value="Upload atti" name="Plaintiff_Docs['+count+']">';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';

    html+='</div>';

    html+='<div class="tr_courtHearing row">';
    html+=    '<div class="col col-lg-1 col-lg-offset-1">';
    html+=        '<div class="form-group">';
    html+=            '<div class="col-lg-12">';
    html+=                '<span class="titolo font12">P. convenuta</span>';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+=    '<div class="col col-lg-3">';
    html+=        '<div class="form-group">';
    html+=            '<label class="col-lg-4 control-label resize">Stato atti</label>';
    html+=            '<div class="col-lg-8">';
    html+=                '<select name="Respondent_Proceedings_State['+count+']" class="form-control resize"><option></option>'+opt_doctype+'</select>';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+=    '<div class="col col-lg-3">';
    html+=        '<div class="form-group">';
    html+=            '<label class="col-lg-4 control-label resize">Data deposito</label>';
    html+=            '<div class="col-lg-8">';
    html+=                '<input name="Respondent_Docs_Date['+count+']" class="form-control resize text_center picker" value="">';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+='</div>';

    html+='<div class="tr_courtHearing row">';
    html+=    '<div class="col col-lg-3 col-lg-offset-2">';
    html+=        '<div class="form-group">';
    html+=            '<div class="col-lg-12">';
    html+=                '<input type="file" class="form-control resize" style="background-color: rgb(153, 204, 255);" value="Upload atti" name="Respondent_Docs['+count+']">';
    html+=            '</div>';
    html+=        '</div>';
    html+=    '</div>';
    html+='</div>';

    html+='<div style="border-top: 2px solid #B0BBE8; width: 50%; margin-left: 25%;margin-bottom: 1%;margin-top: 2%;" class="tr_courtHearing"></div>';


    return html;
    
};

Appeal.getUserPartDOM = function(count, type){
    "use strict";
    var offset = "";
    if(count%2!=0) offset = "col-lg-offset-1";

    var html = "<div class='col col-lg-4 "+offset+"' id='td_"+type+"_"+count+"'>";
    html+= "<input type=hidden name='id_"+type+"["+count+"]'  id='id_"+type+"_"+count+"'>";
    html+= "<input class='form-control resize readonly' style=\"background-color: rgb(153, 204, 255); border: 2px solid black;\" type=\"text\" id='"+type+"_"+count+"' name='"+type+"["+count+"]' readonly ondblclick='Appeal.searchUserPart(\""+count+"\",\""+type+"\")'></div>";
    html+= "<div class=\"col-lg-1 resize\" id='post_td_"+type+"_"+count+"'>";
    html+= "<a onMouseover=\"title='Elimina utente'\" href='#' style='text-decoration:none;' onClick=\"Appeal.removeUserPart('"+count+"','"+type+"')\" >";
    html+= "<img src='"+WEB_IMG_PATH+"/elimina_icon.png\"' style=\"width:14px; height:14px; border:0;\" >";
    html+= "</a></div>";
    return html;

};

Appeal.addUserPart = function(type){
    "use strict";
    var count=1;
    var n = $('#td_'+type+'_'+count).length;
    while(n>0){
        count++;
        n = $('#td_'+type+'_'+count).length;
    }

    if(count==1)
        var selector = "td_"+type+"_begin";
    else
        var selector = "post_td_"+type+"_"+(count-1);


    if(count>1 && (count-1)%2==0){
        $('.last_'+type+':last').after("<div class=\"row tr_"+type+" last_"+type+"\">"+this.getUserPartDOM(count,type));
    }
    else
        $('#'+selector).after(this.getUserPartDOM(count,type));

};

Appeal.addCourtHearing = function(opt_court, opt_doctype){
    "use strict";
    var count=1;
    var n = $('#tr_courtHearing_'+count).length;
    while(n>0){
        count++;
        n = $('#tr_courtHearing_'+count).length;
    }

    $('#tr_courtHearing_begin').after(this.getCourtHearingDOM(count,opt_court,opt_doctype));
    $(".picker").datepicker();
};

function callParent(backValue){
    "use strict";
    if(backValue!=null && backValue!=undefined && backValue!=""){

        switch(Appeal.searchType){
            case "userPart":
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "ajax/ajax_partita.php?c="+Appeal.cc,
                    data: {
                        ajax: "nome",
                        ID: backValue.p,
                    },

                    success: function(value) {

                        Appeal.userName = value;
                        Appeal.userID = backValue.p;
                        Appeal.loadUserPart();
                    }
                });

                break;

            case "authority":

                Appeal.loadAuthority(backValue);

                break;
        }


    }
}

Appeal.searchUserPart = function (count, type) {
    "use strict";

    this.userCount = count;
    this.userType = type;

    this.searchType = "userPart";

    var link = WEB_PATH+"/search/comuni/ricerca_alert_modale.php?richiesta=generale&c="+this.cc;
    window.open(link, "Cerca utente", "width=600,height=300");
}

Appeal.loadUserPart = function(){
    $("#id_"+this.userType+"_"+this.userCount).val(this.userID);
    $("#"+this.userType+"_"+this.userCount).val(this.userName);
}

Appeal.loadAuthority = function(backValue){
    $("#authorityId").val(backValue.ID);
    var stringa = backValue.Tipo+" - "+backValue.comune;
    if(backValue.Sez!="")
        stringa+= " sez. "+backValue.Sez;
    $("#authorityName").val(stringa);
}

Appeal.removeUserPart = function (count, type)
{
    if(count%2!=0)
    {
        var element = document.getElementById('td_'+type+'_'+(parseInt(count, 10)+1));
        if(element != null)
        {
            element.classList.add("col-lg-offset-6");
        }
    }
    $('#td_'+type+'_'+count).remove();
    $('#post_td_'+type+'_'+count).remove();
}

Appeal.searchAuthority = function (){
    "use strict";
    this.searchType = "authority";

    var link = WEB_PATH+"/search/ufficio/ricerca_ufficio.php?richiesta=generale&c=*****";
    window.open(link, "Cerca autorita'", "width=800,height=500");
}

Appeal.changeAuthority = function(partita_ID, appealId){
    "use strict";
    location.href = WEB_PATH+"/coattiva/appeal.php?changeAuthority=y&c="+this.cc+"&a="+this.a+"&partita="+partita_ID+"&lastAppeal="+appealId;
}

Appeal.lawyerAmounts = function(id){
    "use strict";

    var fee = $('#fee'+id).val();
    var rights = $('#rights'+id).val();
    var cu = $('#cu'+id).val();
    var stamp_duty = $('#stamp_duty'+id).val();
    var other_costs = $('#other_costs'+id).val();
    if(fee!="")
        fee = parseFloat( fee.replace(",",".") );
    else
        fee = 0.00;
    if(rights!="")
        rights = parseFloat( rights.replace(",",".") );
    else
        rights = 0.00;
    if(cu!="")
        cu = parseFloat( cu.replace(",",".") );
    else
        cu = 0.00;
    if(stamp_duty!="")
        stamp_duty = parseFloat( stamp_duty.replace(",",".") );
    else
        stamp_duty = 0.00;
    if(other_costs!="")
        other_costs = parseFloat( other_costs.replace(",",".") );
    else
        other_costs = 0.00;

    var feerights = fee+rights;
    feerights = Math.round(feerights*100)/100;
    var overheads = Math.round(feerights*15)/100;
    var feerightsover = feerights+overheads;
    var withholding_tax = Math.round(feerightsover*20)/100;
    if($('#withholding_tax_exemption'+id).val()==1)
        withholding_tax = 0.00;

    var lawyer_fund = Math.round(feerightsover*4)/100;
    var partial = feerightsover+lawyer_fund;
    var VAT = Math.round(partial*22)/100;

    if($('#VAT_exemption'+id).val()==1)
        VAT = 0.00;

    var actual_costs = cu+stamp_duty+other_costs;
    var bill_total = partial+VAT+actual_costs-withholding_tax;
    bill_total = Math.round(bill_total*100)/100;

    $('#feerights'+id).val(Appeal.italianFormat(feerights));
    $('#overheads'+id).val(Appeal.italianFormat(overheads));
    $('#lawyer_fund'+id).val(Appeal.italianFormat(lawyer_fund));
    $('#VAT'+id).val(Appeal.italianFormat(VAT));
    $('#actual_costs'+id).val(Appeal.italianFormat(actual_costs));
    $('#cu'+id).val(Appeal.italianFormat(cu));
    $('#stamp_duty'+id).val(Appeal.italianFormat(stamp_duty));
    $('#other_costs'+id).val(Appeal.italianFormat(other_costs));
    $('#withholding_tax'+id).val(Appeal.italianFormat(withholding_tax));
    $('#bill_total'+id).val(Appeal.italianFormat(bill_total));

}

Appeal.italianFormat = function (num) {
    "use strict";
    return (
        num
            .toFixed(2) // always two decimal digits
            .replace('.', ',') // replace decimal point character with ,
            .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
    ) // use . as a separator
}


