
  </div>
</div>

<script type="text/javascript">

// **** Date checking ****//

function Pasqua(yyyy) {
// RITORNA DATA DELLA PASQUA fra il 1753 e il 2500
var Ap, Bp, Cp, Dp, Ep, Fp, Mp;
  if (yyyy<100) yyyy = 1900 + yyyy;
  Ap = yyyy % 19;
  Bp = yyyy % 4;
  Cp = yyyy % 7;
  Dp = (19*Ap + 24) % 30;
  Fp = 0;            // correzione per secoli
  if (yyyy<2500) Fp=3;
  if (yyyy<2300) Fp=2;
  if (yyyy<2200) Fp=1;
  if (yyyy<2100) Fp=0;
  if (yyyy<1900) Fp=6;
  if (yyyy<1800) Fp=5;
  if (yyyy<1700) Fp=4;
  Ep = (2*Bp + 4*Cp + 6*Dp + Fp + 5) % 7;
  Ep = 22 + Dp + Ep;
  Mp = 3;
  if (Ep>31) {
    Mp = 4;
    Ep = Ep - 31;
  }
  return (new Date(yyyy, Mp-1, Ep));
}
// ' ----------------------------------------------------------- 
function isFest(data) {
  console.log(data);
var s,d,p,ff,f
    // sabato
    s = (data.getDay()==6);
    // domenica
    d = (data.getDay()==0);
    // pasquetta
    pp = Pasqua(data.getFullYear());
    qq = data;
    qq.setDate(qq.getDate()-1);
    p = (date2str(qq) == date2str(pp));
    // FISSI
    ff = " 0101 0106 0425 0501 0602 0815 1101 1208 1225 1226 "
    // PATRONO
    //ff += " 1030 "
    // data in stringa
    ss = date2str(data);
    f = (ff.indexOf(ss.substr(4))>0);
    return (d || s || p || f);
}
function date2str(dd) {
    return String(dd.getFullYear()*10000 + (dd.getMonth()+1)*100 + dd.getDate())
}
// **** End Date checking ****//

var allCustom = [];
//var numberCustom = 0;

function validateForm(field, custom, msg)
{
    //alert(field.id);
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
    //InizializzaAttributi();
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
    //console.log(rec[i].checkValidity());
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

function removeClass(id,classi){
    switch(classi){
        case "vld_Custom_n": $("#"+id).removeClass("validateCustom vld_Custom_n");
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
            case "vld_CheckPrintDate": return CustDatePrinter(field,true);
            case "vld_CheckPrintDate_2": return CustDatePrinter2(field,true);
            case "vld_CheckPrintDate_3": return CustDatePrinter3(field,true);
            case "vld_CheckPrintDate_4": return CustDatePrinter4(field,true);
          }
          //field.dispatchEvent(new Event("change"));
        }
    }

  }
  return true;

}

/*function checkValidityCustom()
{
  if(field === undefined)
  {

    for(var i = 0; i< allCustom.length; i++)
    {
      var allClass = rec[i].className.split(/\s+/);
      for(var x = 0; x<allClass.length; i++)
      {
        for(var y = 0; y< allCustom.length; y++)
        {
          if()
        }
      }
      hege.concat(stale);
    }
    rec.push()
  }
}*/

function InizializzaAttributi(){

	$('.vld_Custom_n').each(function() {
    $(this).attr('pattern','[\\+\\-]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9]+');
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
    $('.vld_CheckPrintDate').each(function() {

        $(this).on("change paste input keyup ", function (){

            var result = CustDatePrinter(this);
            validateForm(this,result[0],result[1]);
        });
        //$(this).trigger('change');
    });
    $('.vld_CheckPrintDate_2').each(function() {

        $(this).on("change paste input keyup ", function (){

            var result = CustDatePrinter2(this);
            validateForm(this,result[0],result[1]);
        });
        $(this).trigger('change');
    });

    $('.vld_CheckPrintDate_3').each(function() {

        $(this).on("change paste input keyup ", function (){

            var result = CustDatePrinter3(this);
            validateForm(this,result[0],result[1]);
        });
    });
    $('.vld_CheckPrintDate_4').each(function() {

        $(this).on("change paste input keyup ", function (){

            var result = CustDatePrinter4(this);
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


function CustDatePrinter(field)
{
    var data_stampa_di_riferimento = $("#data_stampa_rif").val();//'<?= isset($data_stampa_ita)?$data_stampa_ita:null ?>';
    var datarif  = data_stampa_di_riferimento;
    var arrD = field.value.split("/");
    var data_stampa = arrD[2]+"-"+arrD[1]+"-"+arrD[0];

    var data_stampa_rif = data_stampa_di_riferimento.split("/");
    data_stampa_di_riferimento = data_stampa_rif[2]+"-"+data_stampa_rif[1]+"-"+data_stampa_rif[0];

    if((dates.compare(data_stampa,data_stampa_di_riferimento) <1) && !isFest(new Date(data_stampa)))
    {
      /*F10_button = function(){
        $("#f_rimborso_spese").submit();
      }*/
      return [true,""];
    }
    else
    {
      /*F10_button = function(){

      }*/
      return [false,"La data inserita deve essere minore o uguale a " + datarif + " ed essere un giorno lavorativo!"];
    }

}

function CustDatePrinter2(field)
{
    var anno = parseInt($("#anno").val());
    if(isNaN(anno))
        return false;
    var datarif1  = (anno+1)+"-01-1";
    var datarif2  = (anno+1)+"-01-31";
    var data_stampa = field.value;
    var dataRifIta1 = "1/01/"+(anno+1);
    var dataRifIta2 = "31/01/"+(anno+1);

    if((dates.compare(data_stampa,datarif1) > -1) && (dates.compare(data_stampa,datarif2) < 1) && !isFest(new Date(data_stampa)))
    {
        return [true,""];
    }
    else
    {
        return [false,"La data inserita deve essere compresa tra il " + dataRifIta1 + " e il " + dataRifIta2 + ", ed essere lavorativo!"];
    }

}

function CustDatePrinter3(field)
{
    anno = parseInt($('#anno_gestione').val());
    data = $('#data_stampa').val();
    a_data = data.split("-");
    msg = "La data di stampa deve essere compresa tra l'1 ed il 31 gennaio dell'anno successivo a quello di gestione";
    if((anno+1!=parseInt(a_data[0]) || a_data[1]!="01"))
        return [false,msg];
    else
        return [true,""];

}

function CustDatePrinter4(field)
{
    anno = parseInt('<?=$a?>');
    a_data = parseInt(field.value.split("-")[0]);
    msg = "L'anno deve essere uguale all'anno generale scelto!";
    if(anno !== a_data)
        return [false,msg];
    else
        return [true,""];

}

function CustDateInterest(field,flag){
    //alert(field.value);
    var CC = $("#cc").val();
    var Lockup_Type_Id = $("#blockType").val();
    var allInterest = JSON.parse(<?= isset($allInterestBlocks)?json_encode($allInterestBlocks):"";?>);
    var StartDate = $("#start_date").val();
    var EndDate = $("#end_date").val();
    console.log(allInterest);

    var arrResultFilter = [];

    var i = 0;

    for(;i<allInterest.length; i++){
        //alert(allInterest[i].Lockup_Type_Id+" "+allInterest[i].CC);
        switch(allInterest[i].Lockup_Type_Id){
            case "1":
                switch(allInterest[i].CC){
                    case "*****": arrResultFilter.push(allInterest[i]); break;
                    default: if(CC == allInterest[i].CC || CC == "*****"){ arrResultFilter.push(allInterest[i]); } break;
                }
                break;
            default:
                switch(allInterest[i].CC){
                    case "*****": if(Lockup_Type_Id == 1 || allInterest[i].Lockup_Type_Id == Lockup_Type_Id) { arrResultFilter.push(allInterest[i]); } break;
                    default: if((CC == allInterest[i].CC || CC == "*****") && (Lockup_Type_Id == 1 || allInterest[i].Lockup_Type_Id == Lockup_Type_Id)) { arrResultFilter.push(allInterest[i]); } break;
                }
                break;
        }
    }

    console.log("after");
    console.log(arrResultFilter);

    var arrD = field.value.split("/");
    var d = arrD[2]+"-"+arrD[1]+"-"+arrD[0];
    var arrStartD = StartDate.split("/");
    StartDate = arrStartD[2]+"-"+arrStartD[1]+"-"+arrStartD[0];
    var arrEndD = EndDate.split("/");
    EndDate = arrEndD[2]+"-"+arrEndD[1]+"-"+arrEndD[0];

    var x = 0;

    for(;x < arrResultFilter.length;x++){

        var ds = arrResultFilter[x].Start_Date;
        var de = arrResultFilter[x].End_Date;

        if(dates.inRange(d,ds,de)) return [false,"Periodo sovrapposto con "+arrResultFilter[x].Name];
        else{
            if(dates.compare(StartDate,ds) == -1 && dates.compare(EndDate,de) == 1)
                return [false,"Periodo sovrapposto con "+arrResultFilter[x].Name];
        }
    }

    return [true,""];
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
    allCustom[2] = "vld_CheckPrintDate";
    allCustom[3] = "vld_CheckPrintDate_2";
    allCustom[4] = "vld_CheckPrintDate_3";
    allCustom[5] = "vld_CheckPrintDate_4";
    InizializzaAttributi();
});


$('.validate').bootstrapValidator({

			message: 'This value is not valid',
			/*feedbackIcons: {
					valid: 'glyphicon glyphicon-ok',
					invalid: 'glyphicon glyphicon-remove',
					validating: 'glyphicon glyphicon-refresh'
			},*/
			//live: "disabled",
     //excluded: [':disabled'],
			fields: {
				ClassCheck: {
						selector: ".vld_mycheckbox",
						validators: {
							choice: {
										min: 0,
										message: 'Please check one checkbox'
								}
						}
				},
        ClassCheckTarCoaz: {
						selector: ".vld_mycheckboxTarCoaz",
						validators: {
							choice: {
										min: 1,
										message: 'Selezionare almeno una checkbox'
								}
						}
				},
        ClassNoCaratteriSpeciali: {
						selector: ".vld_Fraz",
						validators: {
              regexp: {
                 regexp: /^[^\x27\x28\x29\x2c\x2d\x2e\x2f\x3a\x3b\?\%&£!*=^-]+$/,
                 message: 'Caratteri speciali non ammessi'
             }
						}
				},
        ClassIBAN: {
						selector: ".vld_IBAN",
						validators: {
              regexp: {
                 regexp: /^(it|IT)[0-9]{2}[A-Za-z][0-9]{10}[0-9A-Za-z]{12}$/,
                 message: 'IBAN non corretto'
             }
						}
				},
       ClassDateDiff: {
						selector: ".vld_dateConf",
           validators:
          {
            date: {
              format: 'DD/MM/YYYY',
              message: 'Data errata'
            },
              notEmpty:
              {
                  message: 'Campo obbligatorio'
              },
              callback:
              {
                  message: 'Data non sequenziale o duplicata',
                  callback: function(value, validator, $field)
                  {

                     var flagEqual = false;
                     var value = value.split("/");
                     var dateS = document.getElementsByClassName("startDate");
                     var dateE = document.getElementsByClassName("endDate");

                     var valueObj = new Date(value[2],value[1],value[0]);

                     /*var confrontIndex = 0;
                     var flagMultiple = 0;

                      for(var i = 0;i<dateS.length; i++){
                          var confrontDate = dateS[i].value.split("/");
                          var confrontDateObj = new Date(confrontDate[2],confrontDate[1],confrontDate[0]);

                          if(valueObj.getTime() === confrontDateObj.getTime()){
                              flagMultiple++;
                              if(flagMultiple > 1) {
                                  return false;
                              }
                              confrontIndex = i;
                          }
                      }

                      if(confrontIndex > 0){
                          var confrontDateEnd = dateE[confrontIndex-1].value.split("/");
                          var confrontDateEndObj = new Date(confrontDateEnd[2],confrontDateEnd[1],confrontDateEnd[0]);
                          if(confrontDateEndObj > valueObj)
                          {
                              return false;
                          }
                          else if(confrontDateEndObj.getTime() === valueObj.getTime()) {
                              return false;
                          }
                      }*/

                     // console.log(dateS.length);

                     for(var i = 0;i<dateS.length; i++)
                     {
                       var confrontDate = dateS[i].value.split("/");
                       var confrontDateObj = new Date(confrontDate[2],confrontDate[1],confrontDate[0]);

                         if(valueObj.getTime() === confrontDateObj.getTime()){
                             for(var a = i+1; a < dateS.length; a++){

                                 var confrontDateEqual = dateS[a].value.split("/");
                                 var confrontDateObjEqual = new Date(confrontDateEqual[2],confrontDateEqual[1],confrontDateEqual[0]);

                                 if(valueObj.getTime() === confrontDateObjEqual.getTime()) {
                                     //console.log("uguali");
                                     return false;
                                 }
                             }

                             if(i>0){
                                 var confrontDateEnd = dateE[i-1].value.split("/");
                                 var confrontDateEndObj = new Date(confrontDateEnd[2],confrontDateEnd[1],confrontDateEnd[0]);
                                 //valueObj = new Date(value[2],value[1],value[0]);

                                 if(confrontDateEndObj > valueObj)
                                 {
                                     //console.log(valueObj);
                                     //console.log("end maggiore value"+" - "+(i-1)+" - "+confrontDateEnd+" - "+value);
                                     return false;
                                 }
                             }

                             return true;
                         }
                         else{
                             if(confrontDateObj > valueObj/* && !flagEqual*/){
                                 //console.log("start maggiore value");
                                 return false;
                             }


                         }



                       /*if(valueObj.getTime() === confrontDateObj.getTime() && flagEqual){ return false;}
                       if(valueObj.getTime() === confrontDateObj.getTime() && !flagEqual){
                         if(i>0){
                           var confrontDateEnd = dateE[i-1].value.split("/");
                           var confrontDateEndObj = new Date(confrontDateEnd[2],confrontDateEnd[1],confrontDateEnd[0]);
                           if(confrontDateEndObj > valueObj)
                           {
                             return false;
                           }
                         }
                         flagEqual = true;
                       }*/
                    }

                    return true;
                  }
                }
		           }
            },
           ClassDateDiffNoReq: {
    						selector: ".vld_dateConfNoReq",
               validators:
              {
                date: {
                  format: 'DD/MM/YYYY',
                  message: 'Data errata'
                },
                  callback:
                  {
                      message: 'Data non sequenziale, duplicato o non compilato',
                      callback: function(value, validator, $field)
                      {
                         var flagEqual = false;
                         var flagMultiple = 0;
                         var value_array = value.split("/");
                         var dateS = document.getElementsByClassName("startDate");
                         var dateE = document.getElementsByClassName("endDate");

                         /*console.log(dateE[dateE.length-1].style.display);
                         if($field.val() == "") {
                             for (var i = 0; i < dateE.length-2; i++) {
                                 console.log(dateE[i].value);
                                 if (dateE[i].value == "")
                                         return false;
                             }
                             return true;
                         }*/

                         var indexUltimo = 0;
                         var flagVuota = false;
                         var confrontIndex = 0;
                         var valueObj = new Date(value_array[2],value_array[1],value_array[0]);

                          /*for(var i = 0;i<dateE.length; i++){
                              var confrontDate = dateE[i].value.split("/");
                              var confrontDateObj = new Date(confrontDate[2],confrontDate[1],confrontDate[0]);

                              if(valueObj.getTime() === confrontDateObj.getTime()){
                                  flagMultiple++;
                                  if(flagMultiple > 1) {
                                      return false;
                                  }
                                  confrontIndex = i;
                              }
                          }

                          var confrontDateS = dateS[confrontIndex].value.split("/");
                          var confrontDateObjS = new Date(confrontDateS[2],confrontDateS[1],confrontDateS[0]);

                          if(confrontDateObjS > valueObj) return false;
                          else if(confrontDateObjS.getTime() === valueObj.getTime()) return false;

                          return true;*/




                         if(dateS[dateS.length-1].value === "") {indexUltimo = dateS.length-2;}
                         else {indexUltimo = dateS.length-1;}

                         var valueObj = new Date(value_array[2],value_array[1],value_array[0]);
                         for(var i = 0;i<dateE.length; i++)
                         {
                          var confrontDate = null;
                          var confrontDateObj = null;
                          if(dateE[i].value!="")
                          {
                            confrontDate = dateE[i].value.split("/");
                            confrontDateObj = new Date(confrontDate[2],confrontDate[1],confrontDate[0]);
                          }
                          else{
                            flagVuota = true;
                            confrontDateObj = "";
                          }

                           if(i===indexUltimo && dateE[i].value==="") return true;
                           if(dateE[i].value==="" && i<indexUltimo && dateE[i].value === value && dateE[i].id == $field.attr("id")) {
                              //$('.validate').bootstrapValidator('updateStatus', $("#"+dateE[dateE.length-1].id), 'NOT_VALIDATED', 'lessThan');
                             return false;
                           }

                           if(!flagVuota)
                           {
                             if(valueObj.getTime() === confrontDateObj.getTime() && flagEqual){return false;}
                             if(valueObj.getTime() === confrontDateObj.getTime() && !flagEqual){

                                 var confrontDateStart = dateS[i].value.split("/");
                                 var confrontDateStartObj = new Date(confrontDateStart[2],confrontDateStart[1],confrontDateStart[0]);
                                 if(confrontDateStartObj > valueObj || valueObj.getTime() === confrontDateStartObj.getTime())
                                 {
                                   return false;
                                 }
                               flagEqual = true;
                             }
                           }

                        }

                        return true;
                      }
                    }
    		           }
                },
				ClassDecimalReq: {
							selector: ".vld_decReq",
							validators: {
										regexp: {
                       regexp: /^[0-9]+[.,]{1}[0-9]{2}$/,
                       message: 'Valore errato'
                   },
									notEmpty: {
											message: 'Campo obbligatorio'
									}
							}
					},
					ClassDecimal: {
							selector: ".vld_dec",
							validators: {
								regexp: {
										regexp: /^[0-9]+[.,]{0,1}[0-9]{0,2}$/,
										message: 'Valore errato'
								}
							}
					},
					ClassInteriRec: {
							selector: ".vld_intReq",
							validators: {
										regexp: {
                       regexp: /^[0-9]+$/,
                       message: 'Valore errato'
                   },
									notEmpty: {
											message: 'Campo obbligatorio'
									}
							}
					},
					ClassInteri: {
							selector: ".vld_int",
							validators: {
										regexp: {
                       regexp: /^[0-9]+$/,
                       message: 'Valore errato'
                   }
							}
					},
					ClassAnno: {
							selector: ".vld_y",
							validators: {
										regexp: {
                       regexp: /^[0-9]{4}$/,
                       message: 'Valore anno errato'
                   }
							}
					},
					ClassAnno: {
							selector: ".vld_yReq",
							validators: {
										regexp: {
                       regexp: /^[0-9]{4}$/,
                       message: 'Valore anno errato'
                   },
									notEmpty: {
											message: 'Anno obbligatorio'
									}
							}
					},
					ClassDate_ITReq: {
							selector: ".vld_dateReq",
							validators: {
										date: {
											format: 'DD/MM/YYYY',
											message: 'Data errata'
                   },
									notEmpty: {
											message: 'Data obbligatoria'
									}
							}
					},
					ClassDate_IT: {
							selector: ".vld_date",
							validators: {
										date: {
											format: 'DD/MM/YYYY',
											message: 'Data errata'
                   }
							}
					},
					ClassRequired: {
							selector: ".vld_req",
							validators: {
									notEmpty: {
											message: "Campo obbligatorio"
									}
							}
					},
					ClassEMailReq: {
							selector: ".vld_emailReq",
							validators: {
               emailAddress: {
                       message: 'Mail non valida'
                   },
									notEmpty: {
											message: "Campo obbligatorio"
									}
							}
					},
					ClassEMail: {
							selector: ".vld_email",
							validators: {
               emailAddress: {
                       message: 'Mail non valida'
                   }
							}
					},
                ClassCFReq: {
                    selector: ".vld_CFReq",
                    validators: {
                        regexp: {
                            regexp: /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/,
                            message: 'C.F. errato'
                        },
                        notEmpty: {
                            message: "Campo obbligatorio"
                        }

                    }
                },
         ClassCF: {
           selector: ".vld_CF",
           validators: {
             regexp: {
                 regexp: /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/,
                 message: 'C.F. errato'
             }

           }
         },
            ClassCFPI: {
                selector: ".vld_CFPI",
                validators: {
                    regexp: {
                        regexp: /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}|[0-9]{11}$/,
                        message: 'C.F./P.I. errato'
                    }

                }
            },
         ClassSito: {
           selector: ".vld_Sito",
           validators: {
             regexp: {
                 regexp: /^[a-zA-Z0-9._-]+[.]{1}[a-zA-Z]{2,6}$/,//[.]{1}[a-zA-Z]{2}
                 message: 'Sito errato'
             }

           }
         },
         ClassPI: {
           selector: ".vld_PI",
           validators: {
             regexp: {
                 regexp: /^[0-9]{11}$/,
                 message: 'P.I. errato'
             }

           }
         },
         ClassPIReq: {
           selector: ".vld_PIReq",
           validators: {
             regexp: {
                 regexp: /^[0-9]{11}$/,
                 message: 'P.I. errato'
             },
            notEmpty: {
                message: "Campo obbligatorio"
            }

           }
         },
            ClassCFPIReq: {
                selector: ".vld_CFPIReq",
                validators: {
                    regexp: {
                        regexp: /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}|[0-9]{11}$/,
                        message: 'C.F./P.I. errato'
                    },
                    notEmpty: {
                        message: "Campo obbligatorio"
                    }

                }
            },
         ClassTel: {
           selector: ".vld_tel",
           validators: {
             regexp: {
                 regexp: /^[+]{0,1}[0-9\s/]+$/,
                 message: 'Numero telefonico errato'
             }

           }
         },
         ClassTelReq: {
           selector: ".vld_telReq",
           validators: {
             regexp: {
                 regexp: /^[+]{0,1}[0-9\s/]+$/,
                 message: 'Numero telefonico errato'
             },
            notEmpty: {
                message: "Campo obbligatorio"
            }

           }
         },
         ClassEsp: {
           selector: ".vld_esp",
           validators: {
             regexp: {
                 regexp: /^[a-zA-Z\s]+$/,
                 message: 'Valore errato'
             }

           }
         }
			}
	}).on('success.field.bv', function(e, data) {
               var $parent = data.element.parents('.form-group');

               // Remove the has-success class
               $parent.removeClass('has-success');

               // Hide the success icon
               $parent.find('.form-control-feedback[data-bv-icon-for="' + data.field + '"]').hide();
       }).on('input.bs.validator', function (e) {
 e.stopImmediatePropagation()
 });


$(".vld_dec,.vld_decReq,.vld_Custom_d").blur( function (){

  id_campo = $(this).attr('id');
  valore = control_numero(id_campo);
  if(valore)
  {
    $(this).val(valore);
  }
});




// Source: http://stackoverflow.com/questions/497790
var dates = {
    convert:function(d) {
        // Converts the date in d to a date-object. The input can be:
        //   a date object: returned without modification
        //  an array      : Interpreted as [year,month,day]. NOTE: month is 0-11.
        //   a number     : Interpreted as number of milliseconds
        //                  since 1 Jan 1970 (a timestamp)
        //   a string     : Any format supported by the javascript engine, like
        //                  "YYYY/MM/DD", "MM/DD/YYYY", "Jan 31 2009" etc.
        //  an object     : Interpreted as an object with year, month and date
        //                  attributes.  **NOTE** month is 0-11.
        return (
            d.constructor === Date ? d :
                d.constructor === Array ? new Date(d[0],d[1],d[2]) :
                    d.constructor === Number ? new Date(d) :
                        d.constructor === String ? new Date(d) :
                            typeof d === "object" ? new Date(d.year,d.month,d.date) :
                                NaN
        );
    },
    compare:function(a,b) {
        // Compare two dates (could be of any type supported by the convert
        // function above) and returns:
        //  -1 : if a < b
        //   0 : if a = b
        //   1 : if a > b
        // NaN : if a or b is an illegal date
        // NOTE: The code inside isFinite does an assignment (=).
        return (
            isFinite(a=this.convert(a).valueOf()) &&
            isFinite(b=this.convert(b).valueOf()) ?
                (a>b)-(a<b) :
                NaN
        );
    },
    inRange:function(d,start,end) {
        // Checks if date in d is between dates in start and end.
        // Returns a boolean or NaN:
        //    true  : if d is between start and end (inclusive)
        //    false : if d is before start or after end
        //    NaN   : if one or more of the dates is illegal.
        // NOTE: The code inside isFinite does an assignment (=).
        return (
            isFinite(d=this.convert(d).valueOf()) &&
            isFinite(start=this.convert(start).valueOf()) &&
            isFinite(end=this.convert(end).valueOf()) ?
                start <= d && d <= end :
                NaN
        );
    }
}
</script>
</body>
</html>
