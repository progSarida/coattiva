
function aggiungi_banca(val)
{
  //alert("val banca --> "+val);

  stringa = "";
  stringa+= "<div class='tr_banca banca_"+val+"'>";
  stringa+= "<input type=hidden name='pignorato_id_banca_"+val+"' id='pignorato_id_banca_"+val+"' value='0'>";
  stringa+= "</div>";

  stringa+= "<div class='tr_banca banca_"+val+"' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>";
  stringa+= "<div class='row tr_banca banca_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Terzo</label>";
  stringa+=	   		"<div class='col-lg-6 '>";
  stringa+=	  			"<input class='form-control resize' style='background-color: rgb(153, 204, 255); border: 2px solid black;' readonly name='pignorato_banca_"+val+"' id='pignorato_banca_"+val+"' value='' ondblclick='carica_banca("+val+");'>";
  stringa+=	  		"</div>";
  stringa+=       "<div class='col-lg-2'>";
  stringa+=         "<a onMouseover=\"title='Elimina terzo'\" href='#' style='text-decoration:none;' onClick=\"elimina_terzo('banca',"+val+");\" >";
  stringa+=            "<i class='fas fa-trash' style='color: red;'></i>";
  stringa+=          "</a>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Fonte dati</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name='fonte_banca_"+val+"' id='fonte_banca_"+val+"' value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_banca banca_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Tipo titolo</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=          "<select name=tipo_titolo_"+val+" id=tipo_titolo_"+val+" class='form-control resize'>";
	stringa+=          "<option></option><option value='conto'>Conto corrente</option><option value='libretto'>Libretto</option><option value='altro'>Altro</option></select></td>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Note</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=note_banca_"+val+" id=note_banca_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_banca banca_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Titolo</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=         "<input class='form-control resize' name=titolo_"+val+" id=titolo_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Intestatario</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=intestatario_"+val+" id=intestatario_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_banca banca_"+val+"' id='tr_banca_finale_"+val+"'>";
  stringa+= 	"<div class='col col-lg-10 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-2 control-label resize' style='text-align: left;'>Data costituz. ditta</label>";
  stringa+=	   		"<div class='col-lg-10 '>";
  stringa+=	  			"<input class='form-control resize' name=coointestatari_"+val+" id=coointestatari_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";


	if(val==0)
		$('#tr_banca_iniziale').after(stringa);
	else
  {
    //$('#tr_banca_finale_'+(val)).remove();
    $('#tr_banca_finale_'+(val-1)).after(stringa);
  }
}

function aggiungi_terzo(tipo_terzo)
{

	val=0;
	n = $('#pignorato_id_'+tipo_terzo+'_0').length;
	while(n>0)
	{
		val++;
		n = $('#pignorato_id_'+tipo_terzo+'_'+val).length;
	}

	switch(tipo_terzo)
	{
		case "banca":	aggiungi_banca(val); 
        break;
	}
}

function elimina_terzo(tipo_terzo,val,utente_id,terzo_id,elab_id,c)
{

    $.ajax({
      type: "POST",
      async: false,
      url: "ajax/ajax_cancella_banca.php",
      data: {
          utente_id : utente_id,
          terzo_id : terzo_id,
          elab_id: elab_id,
          c:c
      },
      success: function(nome) {

        $('.'+tipo_terzo+'_'+val).remove();
        $('.ctrl_terzo_'+val).remove();
      },
      error : function()
      {
        console.log('errore elimina terzo');
      }
  });


	
}

switchMenuImg("F12");
function F12_button(){ return false; }


switchMenuImg("F3");
F3_button = function(){
  elimina_vuoti();
  var elems = $("[id^=pignorato_banca_]");

  conta_terzi = elems.length;

  $("#conta_terzi").val(conta_terzi);
  $("#submitButton").trigger("click");
}


function elimina_vuoti()
{
  $("[id^=pignorato_banca_]").each(function(){
    if ($(this).val()=='')
    {
      var strname =($(this).attr('name'));
      var i = strname.split("_")[2];
      $('.banca_'+i).remove();
      $('.ctrl_terzo_'+i).remove();
    }
  });
}


