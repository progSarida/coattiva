function aggiungi_lavoro(val)
{
  //alert("function aggiungi_lavoro");
  //alert("val lavoro --> "+val);
	stringa = "";
	stringa+= "<div class='tr_lavoro lavoro_"+val+"'>";
	stringa+= "<input type=hidden name='pignorato_id_lavoro_"+val+"' id='pignorato_id_lavoro_"+val+"'	value='0' 	>";
	stringa+= "</div>";

  stringa+= "<div class='tr_lavoro lavoro_"+val+"' style='border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-top: 2%; margin-bottom: 2%;'></div>";
  stringa+= "<div class='row tr_lavoro lavoro_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Terzo</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' style='background-color: rgb(153, 204, 255); border: 2px solid black;' readonly name=pignorato_lavoro_"+val+" id=pignorato_lavoro_"+val+" value='' ondblclick=\"/*carica_utente( "+val+" , 'lavoro');*/openOfcanvas('companySearchModal',"+val+");\">";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+=   "<div class='col col-lg-1'>";
  stringa+=     "<div class='form-group'>"
  stringa+=       "<div class='col-lg-12'>";
  stringa+=         "<a onMouseover=\"title='Elimina terzo'\" href='#' style='text-decoration:none;' onClick=\"elimina_terzo('lavoro',"+val+");\" >";
	stringa+=            "<i class='fas fa-trash' style='color: red;'></i>";
	stringa+=          "</a>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_lavoro lavoro_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Azienda</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=azienda_lavoro_"+val+" id=azienda_lavoro_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Fonte dati</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=fonte_lavoro_"+val+" id=fonte_lavoro_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_lavoro lavoro_"+val+"'>";
  stringa+= 	"<div class='col col-lg-5 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Tipo contratto</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=         "<select name=tipo_contratto_"+val+" id='tipo_contratto_"+val+"' class='form-control resize' onchange='scelta_contratto("+val+");'>";
	stringa+=           "<option></option><option value='titolare'>Titolare</option><option value='accessorio'>Accessorio</option>";
	stringa+=           "<option value='apprendistato'>Apprendistato</option><option value='chiamata'>Chiamata</option>";
	stringa+=           "<option value='collaborazione'>Collaborazione</option><option value='determinato'>Determinato</option>";
	stringa+=           "<option value='indeterminato'>Indeterminato</option><option value='inserimento'>Inserimento</option>";
	stringa+=           "<option value='interinale'>Interinale</option><option value='occasionale'>Occasionale</option>";
	stringa+=           "<option value='progetto'>Progetto</option><option value='ripartito'>Ripartito</option>";
	stringa+=           "<option value='somministrazione'>Somministrazione</option><option value='parziale'>Tempo parziale</option>";
	stringa+=           "<option value='altro'>Altro</option></select>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-5'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Note</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='form-control resize' name=note_lavoro_"+val+" id=note_lavoro_"+val+" value=''>";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";

  stringa+= "<div class='row tr_lavoro lavoro_"+val+"' id='tr_lavoro_finale_"+val+"'>";
  stringa+= 	"<div class='col col-lg-4 col-lg-offset-1'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-4 control-label resize' style='text-align: left;'>Data costituz. ditta</label>";
  stringa+=	   		"<div class='col-lg-8 '>";
  stringa+=	  			"<input class='picker text_center form-control resize validateCustom vld_Custom_date' style='width: 50%;' name=data_costituzione_"+val+" id=data_costituzione_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-3'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-6 control-label resize' style='text-align: left;'>Data ditta operativa</label>";
  stringa+=	   		"<div class='col-lg-6'>";
  stringa+=	  			"<input class='picker text_center form-control resize validateCustom vld_Custom_date' name=data_operativa_"+val+" id=data_operativa_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= 	"<div class='col col-lg-3'>";
  stringa+=	  	"<div class='form-group'>";
  stringa+= 			"<label class='col-lg-6 control-label resize' style='text-align: left;'>Data dipendenze</label>";
  stringa+=	   		"<div class='col-lg-6 '>";
  stringa+=	  			"<input class='picker text_center form-control resize validateCustom vld_Custom_date' name=data_dipendenze_"+val+" id=data_dipendenze_"+val+" value='' >";
  stringa+=	  		"</div>";
  stringa+= 		"</div>";
  stringa+= 	"</div>";
  stringa+= "</div>";


	if(val==0)
  {
    $('#tr_lavoro_iniziale').after(stringa);
    //InizializzaAttributi();
  }
	else
  {
    //$('#tr_lavoro_finale_'+(val)).remove();
    $('#tr_lavoro_finale_'+(val-1)).after(stringa);
    //InizializzaAttributi();
  }
  InizializzaAttributi();

}

function aggiungi_terzo(tipo_terzo)
{
  //return false;
  //alert("function aggiungi terzo");
  //
	// if($('#data_stampa').val()!="")
	// {
	// 	alert("Stampa definitiva gia effettuata!");
	// 	return false;
	// }

	val=0;
	n = $('#pignorato_id_'+tipo_terzo+'_0').length;
	while(n>0)
	{
		val++;
		n = $('#pignorato_id_'+tipo_terzo+'_'+val).length;
	}

	switch(tipo_terzo)
	{
		//case "banca":	aggiungi_banca(val); aggiungi_notifica_terzo(val);	break;
		case "lavoro":	aggiungi_lavoro(val); //aggiungi_notifica_terzo(val);	
        break;
	}
}

function elimina_terzo(tipo_terzo,val,utente_id,terzo_id,elab_id,c)
{
    $.ajax({
      type: "POST",
      async: false,
      url: "ajax/ajax_cancella_terzo.php",
      data: {
          utente_id : utente_id,
          terzo_id : terzo_id,
          elab_id: elab_id,
          c:c
      },
      success: function(nome) {

        $('.'+tipo_terzo+'_'+val).remove();
        $('.ctrl_terzo_'+val).remove();
      }
  });


	
}


switchMenuImg("F12");
function F12_button(){ return false; }

switchMenuImg("F3");
F3_button = function(){
  elimina_vuoti();
  var elems = $("[id^=pignorato_lavoro_]");

  conta_terzi = elems.length;

  $("#conta_terzi").val(conta_terzi);
  $("#submitButton").trigger("click");
}


function elimina_vuoti()
{
  $("[id^=pignorato_lavoro_]").each(function(){
    if ($(this).val()=='')
    {
      var strname =($(this).attr('name'));
      var i = strname.split("_")[2];
      $('.lavoro_'+i).remove();
      $('.ctrl_terzo_'+i).remove();
    }
  });
}


