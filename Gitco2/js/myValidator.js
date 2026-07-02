var allCustom = [];
//var numberCustom = 0;

function validateForm(field, custom, msg)
{
    if(custom == undefined)
        custom = true;
    if(msg == undefined)
        msg = "";
    //alert(field+" "+custom+" "+msg);
    if(field === undefined) $(".error").remove();
    else
    {
        if(field.id != "")
        {
            $("#error_"+field.id).remove();
        }
        else{
            var parent = field.parentNode;
            var children = parent.children;

            for(var i = 0; i< children.length; i++)
            {
                var arrayClass = children[i].className.split(/\s+/);
                for(var x = 0; x<arrayClass.length; x++)
                {
                    if(arrayClass[x] === "error")
                    {
                        children[i].remove();
                    }
                }
            }
        }
    }
//alert("2");
    if(field === undefined){ InizializzaAttributi();}
    var rec = null;
//alert("3");
    if(field!==undefined) rec = [field];
    else rec=document.getElementsByClassName('validateCustom');
//alert("4");

    var flagOK = true;
    var flagReadOnly = false;
//alert("5 "+rec.length);
    for (var i = 0; i<rec.length; i++) {
        if(rec[i].readOnly == true)
        {
            rec[i].readOnly = false;
            flagReadOnly = true;
        }
//alert("6");
        if(field === undefined )
        {
            //alert("qui checkValidityCustom");
            var result = checkValidityCustom(rec[i].className.split(/\s+/),rec[i]);
            if(result[0]!= undefined) custom = result[0];
            if(result[1]!= undefined) msg = result[1];
            //alert(custom);
        }

        //console.log(rec[i].name);
        if (!rec[i].checkValidity() || !custom )
        {
            //alert("false dentro check validity "+rec[i].id);
            flagOK = false;
            var message;
            if(rec[i].validationMessage == "Compila questo campo.") message="Campo obbligatorio";
            else if(msg != "") message = msg;
            else message = rec[i].validationMessage;

            if(rec[i].id=="")
            {
                var newNode = document.createElement("span");
                newNode.innerHTML = message;
                newNode.style.color = "#B34F4F";
                newNode.class = "error";
                newNode.style.fontSize = "12px";

                var parent = rec[i].parentNode;
                parent.classList.add("has-error");
                parent.appendChild(newNode);
            }
            else{
                var parent = rec[i].parentNode;
                parent.classList.add("has-error");
                var labElem = null;
                labElem = parent.getElementsByTagName("label");

                if(labElem.length === 0)
                {
                    var grandParent = parent.parentNode;
                    var labElem = grandParent.getElementsByTagName("label");
                }

                for(var x = 0; x < labElem.length; x++)
                {
                    labElem[x].style.color = "#B34F4F";
                }

                $("#"+rec[i].id).after("<span class='error' id='error_"+rec[i].id+"' style='color: #B34F4F; font-size: 12px;'>"+message+"</span>");
            }

        }
        else{
            var parent = rec[i].parentNode;
            parent.classList.remove("has-error");

            var labElem = null;
            labElem = parent.getElementsByTagName("label");

            if(labElem.length === 0)
            {
                var grandParent = parent.parentNode;
                var labElem = grandParent.getElementsByTagName("label");
            }

            for(var x = 0; x < labElem.length; x++)
            {
                labElem[x].style.color = "black";
            }

        }
        if(flagReadOnly)
        {
            rec[i].readOnly = true;
            flagReadOnly = false;
        }
    }
    return flagOK;
}

function resetErrorOnID(id){
    var parent = document.getElementById(id).parentNode;
    parent.classList.remove("has-error");
    labElem = parent.getElementsByTagName("label");
    if(labElem.length === 0)
    {
        var grandParent = parent.parentNode;
        var labElem = grandParent.getElementsByTagName("label");
    }
    for(var x = 0; x < labElem.length; x++)
    {
        labElem[x].style.color = "";
    }
    $("#error_"+id).remove();
}

function checkValidityCustom(arrayClassi,field)
{
    for(var i = 0; i< arrayClassi.length; i++)
    {
        for(var x = 0; x < allCustom.length; x++)
        {
            if(arrayClassi[i] == allCustom[x])
            {
                switch(allCustom[x])
                {
                    case "vld_Custom_CustAnno": return CustAnno(field,true);
                    case "vld_CheckInterestDate": return CustDateInterest(field,true);
                }
                //field.dispatchEvent(new Event("change"));
            }
        }

    }
    return true;

}

function removeClass(id,classi){
    switch(classi){
        case "vld_Custom_n": $("#"+id).removeClass("validateCustom vld_Custom_n");
            $("#"+id).removeAttr("pattern");
            break;
        case "vld_Custom_nf": $("#"+id).removeClass("validateCustom vld_Custom_nf");
            $("#"+id).removeAttr("pattern");
            break;
        case "vld_Custom_d": $("#"+id).removeClass("validateCustom vld_Custom_d");
            $("#"+id).removeAttr("pattern");
            break;
        case "vld_Custom_date": $("#"+id).removeClass("validateCustom vld_Custom_date");
            $("#"+id).removeAttr("pattern");
            break;
        case "vld_Custom_r": $("#"+id).removeClass("validateCustom vld_Custom_r");
            $("#"+id).removeAttr("required");
            break;
        case "vld_Custom_anno": $("#"+id).removeClass("validateCustom vld_Custom_anno");
            $("#"+id).removeAttr("pattern");
            break;
        default: alert("Classe non valida");
            break;
    }
}

function addClass(id,classi){
    var field = document.getElementById(id);
    switch(classi){
        case "vld_Custom_n": $("#"+id).addClass("validateCustom vld_Custom_n");
            $("#"+id).attr('pattern','[\\+\\-]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9]+');
            //$("#"+id).attr('pattern','[-]{0,1}[0-9]+');
            $("#"+id).on("change paste input keyup", function() {
                validateForm(field);
            });
            break;
        case "vld_Custom_nf": $("#"+id).addClass("validateCustom vld_Custom_nf");
            //$("#"+id).attr('pattern','[-+]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9]+');
            $("#"+id).attr('pattern','[0-9]+');
            $("#"+id).on("change paste input keyup", function() {
                validateForm(field);
            });
            break;
        case "vld_Custom_d": $("#"+id).addClass("validateCustom vld_Custom_d");
            $("#"+id).attr('pattern','[\\+\\-]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9]+[\\.\\,]{0,1}[0-9]{0,2}');
            $("#"+id).on("change paste input keyup", function() {
                validateForm(field);
            });
            break;
        case "vld_Custom_date": $("#"+id).addClass("validateCustom vld_Custom_date");
            $("#"+id).attr('pattern','[0-9]{2}[-/]{1}[0-9]{2}[-/]{1}[0-9]{4}');
            $("#"+id).on("change paste input keyup", function() {
                validateForm(field);
            });
            break;
        case "vld_Custom_r": $("#"+id).addClass("validateCustom vld_Custom_r");
            $("#"+id).attr('required','required');
            $("#"+id).on("change paste input keyup", function() {
                validateForm(field);
            });
            break;
        case "vld_Custom_anno": $("#"+id).addClass("validateCustom vld_Custom_anno");
            $("#"+id).attr('pattern','[0-9]{4}$');
            $("#"+id).on("change paste input keyup", function() {
                validateForm(field);
            });
            break;
        default: alert("Classe non valida");
            break;
    }
}

function InizializzaAttributi(){

    $('.vld_Custom_n').each(function() {
        $(this).attr('pattern','[-]{0,1}[0-9]+');
        $(this).on("change paste input keyup", function() {
            validateForm(this);
        });
    });

    $('.vld_Custom_nf').each(function() {
        $(this).attr('pattern','[0-9]+');
        $(this).on("change paste input keyup", function() {
            validateForm(this);
        });
    });

    $('.vld_Custom_d').each(function() {
        $(this).attr('pattern','[\\+\\-]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9]+[\\.\\,]{0,1}[0-9]{0,2}');
        $(this).on("change paste input keyup", function() {
            validateForm(this);
        });
    });

    $('.vld_Custom_date').each(function() {
        $(this).attr('pattern','[0-9]{2}[-/]{1}[0-9]{2}[-/]{1}[0-9]{4}');
        $(this).on("change paste input keyup", function() {
            validateForm(this);
        });
    });

    $('.vld_Custom_r').each(function() {
        $(this).attr('required','required');
        $(this).on("change paste input keyup", function() {
            validateForm(this);
        });
    });

    $('.vld_Custom_anno').each(function() {
        $(this).attr('pattern','[0-9]{4}$');
        $(this).on("change paste input keyup", function() {
            validateForm(this);
        });
    });

    $('.vld_Custom_CustAnno').each(function() {

        $(this).on("change paste input keyup", function (){

            var flagValidity = true;

            control_anno = $('#anno').val();

            var regexp = /^[0-9]{4}$/;
            flagValidity = regexp.test(control_anno);

            if(flagValidity)
            {
                for(var y=0 ; y<num_anni.length;y++)
                {
                    if( num_anni[y] == control_anno )
                    {
                        flagValidity = false;
                    }
                }
            }

            if(flagValidity)
            {
                if(control_anno <= 1900)
                    flagValidity = false;
            }
            validateForm(this,flagValidity,"Anno già inserito o non valido");
        });
    });

    $('.vld_CheckInterestDate').each(function() {

        $(this).on("change paste input keyup", function (){

            var result = CustDateInterest(this);
            validateForm(this,result[0],result[1]);
        });
    });
}

function CustAnno(field,flag) {

//alert();
    var flagValidity = true;

    control_anno = $('#anno').val();

    var regexp = /^[0-9]{4}$/;
    flagValidity = regexp.test(control_anno);

    if(flagValidity)
    {
        for(var y=0 ; y<num_anni.length;y++)
        {
            if( num_anni[y] == control_anno )
            {
                flagValidity = false;
            }
        }
    }

    if(flagValidity)
    {
        if(control_anno <= 1900)
            flagValidity = false;
    }
    //validateForm(field,flagValidity,"Anno già inserito o non valido");
    var arrayRet = [flagValidity,"Anno già inserito o non valido"];
    return arrayRet;
}


function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2)
        month = '0' + month;
    if (day.length < 2)
        day = '0' + day;

    return [year, month, day].join('-');
}



$( document ).ready(function() {

    allCustom[0] = "vld_Custom_CustAnno";
    allCustom[1] = "vld_CheckInterestDate";
    InizializzaAttributi();
});