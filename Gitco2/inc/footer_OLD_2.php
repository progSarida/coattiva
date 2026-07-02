
  </div>
</div>

<script type="text/javascript">

var allCustom = [];
//var numberCustom = 0;

function validateForm(field, custom = true, msg = "")
{
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
      if(result[1]!= undefined) msg =result[1];
      //alert(custom);
    }

console.log(rec[i].name);
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
    $(this).attr('pattern','[-+]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9]+');
    $(this).on("change paste input keyup", function() {
          validateForm(this);
    });
	});

	$('.vld_Custom_d').each(function() {
			$(this).attr('pattern','[-+]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9.]+[.,]{0,1}[0-9]{0,2}');
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
			//$(this).attr('required','required');
      //var presenza = false;
    /*  for(var i = 0; i< allCustom.length; i++)
      {
        if(allCustom[i]=="vld_Custom_CustAnno")
        {
          presenza = true;
          break;
        }
      }*/
      allCustom[0] = "vld_Custom_CustAnno";
      //allCustom.push("vld_Custom_CustAnno");

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

$( document ).ready(function() {
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

                     for(var i = 0;i<dateS.length; i++)
                     {
                       var confrontDate = dateS[i].value.split("/");
                       var confrontDateObj = new Date(confrontDate[2],confrontDate[1],confrontDate[0]);

                       if(confrontDateObj > valueObj && !flagEqual){
                         return false;
                       }

                       if(valueObj.getTime() === confrontDateObj.getTime() && flagEqual){ return false;}
                       if(valueObj.getTime() === confrontDateObj.getTime() && !flagEqual){
                         if(i>0){
                           confrontDateEnd = dateE[i-1].value.split("/");
                           confrontDateEndObj = new Date(confrontDateEnd[2],confrontDateEnd[1],confrontDateEnd[0]);
                           if(confrontDateEndObj > valueObj)
                           {
                             return false;
                           }
                         }
                         flagEqual = true;
                       }
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
                      message: 'Data non sequenziale o duplicata',
                      callback: function(value, validator, $field)
                      {
                         var flagEqual = false;
                         var value_array = value.split("/");
                         var dateS = document.getElementsByClassName("startDate");
                         var dateE = document.getElementsByClassName("endDate");
                         var indexUltimo = 0;
                         var flagVuota = false;

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
            ClassCF: {
                selector: ".vld_CFPI",
                validators: {
                    regexp: {
                        regexp: /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}|[0-9]{11}$/,
                        message: 'C.F. errato'
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

</script>
</body>
</html>
