<script>
var stringaMODE = "p=0&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
var stringaMODE1 = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
var stringaMODE2 = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=PUBBLICITA";
var stringaMODE3 = "p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=PUBBLICITA";
var modifica = 0;
var controllo = "<?php if(isset( $_SESSION['control_mode'])){echo $_SESSION['control_mode'];} else {echo " ";} ?>";
var uscita_utente = '0';
var timer = "";

	$("*").on( "change" , "input, textarea" , function( event ) {

	if(modifica!=1){modifica=1;}

	var elem = $( this );
	if(elem.attr('type')!="radio")
	{
	
		nomi_classi = elem.attr('class');
		control_classe = nomi_classi.search('numero');
		
		if(control_classe != "-1")	
		{
			valore = control_numero(campo_name);
			if(valore===false)
			{
				alert("Inserire un valore numerico.");
				elem.val('');
			}
			else
				elem.val(valore);
		}
		
		elem.addClass( "sfondo_giallo", ":change" );
	}

	});

	$("*").on( "focus blur","input, textarea", function( event ) {		
	var elem = $( this );
	
	if( elem.attr('name') == 'genere' )
		$('#radioFocus').toggleClass( "focused" , elem.is( ":focus" ));
	else
	{
		elem.toggleClass( "focused", elem.is( ":focus" ) );
	}

	});

	$("*").on( "focus","input, textarea", function( event ) {		
		var elem = $( this );
		
			elem.select();		

		if( elem.attr('tabindex') != '1' )
		{
			clearTimeout(timer);
			timer = setTimeout(function() {
			      $('[tabindex=1]').focus();
			}, 60000);
		}
		else
			clearTimeout(timer);
		
		});


function menuClick (value)
{
	if (modifica==1 || uscita_utente==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
// 		if(utente_ID!=0 || modalita=="consulta")
// 		{
		$elab_atti = "/gitco2/elaborazioni/elabora_atto.php?"+stringaMODE;
		$stampa_atti = "/gitco2/stampe/stampa_atto.php?"+stringaMODE;
		$elenco_atti = "/gitco2/stampe/elenco_atto.php?"+stringaMODE;
		$procedure_controllo = "/gitco2/stampe/procedure_controllo.php?"+stringaMODE;
		$autorita = "/gitco2/parametri/ufficio_giudiziario.php?"+stringaMODE;
		$ente_esterno_menu = "/gitco2/parametri/ente_esterno.php?"+stringaMODE;
		$sede = "/gitco2/ispezioni/sede_legale.php?"+stringaMODE;
		$ufficio_comune_link = "/gitco2/parametri/ufficio_comune.php?"+stringaMODE;
		switch(value)
		{
		case '1':	 menulink = "/gitco2/anagrafe/dati_soggetto.php?"+stringaMODE3;						break;
		case '2':  	 menulink = "/gitco2/anagrafe/annotazioni.php?"+stringaMODE3;						break;		
		case '3':    menulink = "/gitco2/anagrafe/recapito.php?"+stringaMODE3;							break;
		case '4':	 menulink = "/gitco2/anagrafe/domicilio.php?"+stringaMODE3;							break;
		case '5':	 menulink = "/gitco2/anagrafe/dettagli.php?"+stringaMODE3;							break;
		case '6':	 menulink = "/gitco2/anagrafe/cambia_residenza.php?"+stringaMODE3;					break;
		case '7':	 menulink = "/gitco2/pubblicita/parametri/scadenze.php?"+stringaMODE;				break;
		case '8':	 menulink = "/gitco2/pubblicita/parametri/personalizzazione.php?"+stringaMODE;		break;
		case '9':	 menulink = "/gitco2/pubblicita/parametri/ccp.php?"+stringaMODE;					break;
		case '10':	 menulink = "/gitco2/pubblicita/parametri/maggiorazioni.php?"+stringaMODE;			break;													
		case '11':	 menulink = "/gitco2/pubblicita/parametri/lista_tariffe.php?"+stringaMODE;			break;
		case '12':	 menulink = "/gitco2/pubblicita/parametri/lista_voci_tariffe.php?"+stringaMODE;		break;
		case '13':	 menulink = "/gitco2/gestione/crea_comune.php?"+stringaMODE2;						break;
		case '14':	 menulink = "/gitco2/gestione/crea_anno.php?"+stringaMODE2;							break;
		case '15':	 menulink = "/gitco2/gestione/elimina_comune.php?"+stringaMODE2;					break;
		case '16':	 menulink = "/gitco2/gestione/elimina_anno.php?"+stringaMODE2;						break;
		case '17':	 menulink = "/gitco2/parametri/dati_ente.php?"+stringaMODE2;						break;
		case '18':	 menulink = "/gitco2/parametri/gestore.php?"+stringaMODE2;							break;
		case '19':	 menulink = "/gitco2/parametri/ufficio.php?"+stringaMODE2;							break;
		case '20':	 menulink = "/gitco2/parametri/stemma.php?"+stringaMODE2;							break;
		case '21':	 menulink = "/gitco2/pubblicita/preavvisi/lista_parametri.php?"+stringaMODE;		break;
		case '22':	 menulink = "/gitco2/pubblicita/parametri/canoni.php?"+stringaMODE;					break;
		case '23':	 menulink = "/gitco2/pubblicita/parametri/contenzioso.php?"+stringaMODE;			break;
		case '24':	 menulink = "/gitco2/pubblicita/parametri/interessi.php?"+stringaMODE;				break;
		case '25':	 menulink = "/gitco2/pubblicita/parametri/riduzioni.php?"+stringaMODE;				break;
		case '26':	 menulink = "/gitco2/pubblicita/undercostruction.php?"+stringaMODE1;				break;			
		case '27':	 menulink = "/gitco2/pubblicita/parametri/lista_strade.php?"+stringaMODE;;			break;			
		case '28':	 menulink = "/gitco2/pubblicita/impianti/posizioni.php?"+stringaMODE3;				break;
		case '29':	 menulink = "/gitco2/menu/scelta_CC_e_anno_pubblicita.php?"+stringaMODE3;			break;
		case '31':	 menulink = "";																		break;
		case '32':	 menulink = "";																		break;
		case '33':	 menulink = "";																		break;
		case '34':	 menulink = "";																		break;
		case '35':	 menulink = "";																		break;
		case '36':	 menulink = "";																		break;
		case '58':	 menulink = "";																		break;
		case '61':	 menulink = "";																		break;
		case '52':	 menulink = "";																		break;
		case '22':	 menulink = "";																		break;
		case '32':	 menulink = "";																		break;
		case '23':	 menulink = "";																		break;
		case '62':	 menulink = "";																		break;
		case '63':	 menulink = "";																		break;
		case '34':	 menulink = "";																		break;
		case '24':	 menulink = "";																		break;
		case '42':	 menulink = "";																		break;
		case '25':	 menulink = "";																		break;
		case '33':	 menulink = "";																		break;
		case '26':	 menulink = "";																		break;
		case '41':	 menulink = "";																		break;
		case '27':	 menulink = "";																		break;
		case '59':	 menulink = "";													
	
		default:	 alert ("Errore nella scelta del menu");											break;
		}
		
		top.location.href = menulink;
		
// 		}
	
	}
}

function submit_buttons (value)
{
	var ritorno = null;

	if(modalita=="consulta")
	{
		return false;
	}
	else if(modalita=="modifica")
	{
		switch(value)
		{
			case "Insert":
				
				ritorno = confirm("Si stanno inserendo nuovi dati nel database. \n\nConfermare l'operazione?");
				$('#invia_submit').val('Insert');

				break;
			case "Delete":
				
				ritorno = confirm("Si stanno eliminando i dati dal database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");	
				$('#invia_submit').val('Delete');

				break;
			case "Update":
				
				ritorno = confirm("Si stanno modificando i dati del database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
				$('#invia_submit').val('Update');

				break;
		}
		
		if(value=="Delete")
		{
			if(ritorno)
			{
				ritorno2 = confirm("Sei sicuro di voler eliminare i dati?");
				if(ritorno2)
					{return true;}
				else
					{return false;}
			}
			else
			{return false;}
		}
		else
		{	
			if(ritorno)
				{return	true;}
			else
				{return	false;}
		}
	}
}

//NUOVO UTENTE E RITORNO AL MENU
function link(value)
{
	if (modifica==1 || uscita_utente==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		switch(value)
		{
			case "menu":

				stringa = "/gitco2/menu/home.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				
			break;
				
			case "new":

			   	stringa = "dati_soggetto.php?mode=modifica&p=0&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			   		
	   		break;
			}
		top.location.href = stringa;
	}
}

</script>

<script>
//FUNZIONI PER IL BLOCCO E SBLOCCO DEL RECORD
//------------------------------------------------------------------//
   	function control_lock(ID)
   	{
   	   	
   			var idSblocco = "<?php if(isset($_SESSION['id_sblocco'])){ echo $_SESSION['id_sblocco']; } else {echo " "; }?>  ";
   			
			if(modalita=="consulta")
			{		
				if(controllo=='true')
				{
					if(idSblocco!=0)
		   	   	   	{
   						sblocco(idSblocco);
   						uscita_utente = 0;
		   	   	   	}
				}
			}
			else
			{
				if(ID!=0)
	   	   	   	{
					blocco(ID);
	   	   	   	}
			}   	   	
   	}
   	function blocco(progrUtente)
   	{   	
   	   	if(utente_ID!=0)
   	   	{
   	   	   	
   	   	$.post("ajax/ajax_anagrafe.php",
   	    	   	 
   	    	   	{ 'blocco': 'on' , 'ID': progrUtente },
   	    	   	
	   			function (value) {
					if (value == "OK")
					{
						//uscita_utente = 1;
						if(modalita=="consulta")
						{
							scelta_moda('modifica');
						}
						
					}
					else if (value == "Busy")
					{
						alert('Record in uso da un altro utente. Riprovare tra qualche minuto.');
						top.location.href = "dati_soggetto.php?mode=consulta&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
						
					}
					else
					{
						alert("Errore nella procedura di blocco!!!");
					}
				}
			);
   	   	}
   	}   	
   	function sblocco(progrUtente)
   	{
   		$.post("ajax/ajax_anagrafe.php", 
				{ 'blocco': 'off' , 'ID': progrUtente },
   				function (value) 
   				{					
					if (value == "OK")
					{
						
					}
					else if (value == "Failed")
					{
						alert("Errore in una query di sblocco dei record collegati all'utente");
					}
					else
					{
						alert("Errore nella procedura di sblocco!!!");
					}
				});

   	}

</script>

<script>
var fn = function (e)
{
	if (!e)
	{
    	e = window.event;
	}

    var keycode = e.keyCode;
    if (e.which)
        keycode = e.which;

 	//var src = e.srcElement;
  	//if (e.target)
       	//src = e.target;

     	
//CONSULTA F2
   	    if (113 == keycode)
	    {
	        // Firefox and other non IE browsers
	        if (e.preventDefault)
	        {
	            e.preventDefault();
	            e.stopPropagation();
	        }
	        // Internet Explorer
	        else if (e.keyCode)
	        {
	            e.keyCode = 0;
	            e.returnValue = false;
	            e.cancelBubble = true;
	        }		

	        if(modalita=='modifica')
	        scelta_moda('cerca');
	        else
	        blocco('<?php echo $utente->ID; ?>');

	        return false;
	    }
//SALVA F3
   	    else if (114 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		

   	     campi = controllaCampi();
 		if(campi)
 		{
   	    	control=submit_buttons('<?php echo $submit_name; ?>');
   	     	if(control)
   	        	$("#anagrafe_form").submit();
 		}
	         	
   	        return false;
   	    }
//ELIMINA F4
   	    else if (115 == keycode)
	    {
	        // Firefox and other non IE browsers
	        if (e.preventDefault)
	        {
	            e.preventDefault();
	            e.stopPropagation();
	        }
	        // Internet Explorer
	        else if (e.keyCode)
	        {
	            e.keyCode = 0;
	            e.returnValue = false;
	            e.cancelBubble = true;
	        }		

	        control = submit_buttons('Delete');
   	     	if(control)
   	        	$("#anagrafe_form").submit();

	        return false;
	    }
//ANNULLA F5
   	    else if (116 == keycode)
	    {
	        // Firefox and other non IE browsers
	        if (e.preventDefault)
	        {
	            e.preventDefault();
	            e.stopPropagation();
	        }
	        // Internet Explorer
	        else if (e.keyCode)
	        {
	            e.keyCode = 0;
	            e.returnValue = false;
	            e.cancelBubble = true;
	        }		

	        annulla();
	        	
	        return false;
	    }
//MODIFICA F6
   	    else if (117 == keycode)
	    {
	        // Firefox and other non IE browsers
	        if (e.preventDefault)
	        {
	            e.preventDefault();
	            e.stopPropagation();
	        }
	        // Internet Explorer
	        else if (e.keyCode)
	        {
	            e.keyCode = 0;
	            e.returnValue = false;
	            e.cancelBubble = true;
	        }		

	        
	        link('new');

	        return false;
	    }
//RECORD PRECEDENTE F7
		else if (118 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		

   	     	gira_utente('prev');
   	        	
   	        return false;
   	    }
//RECORD SUCCESSIVO F8
		else if (119 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		

   	     	gira_utente('next');
   	     	
   	        return false;
   	    }
//RICERCA F9
		else if (120 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		
  	     	
   	        return RicercheDaId('utente',0);
   	    }
   	    
//HELP F11
		else if (122 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		

   	     	window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');
   	     	
   	        return false;
   	    }
//HOME F12
		else if (123 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		

   	     	link('menu');
   	     	
   	        return false;
   	    }
//PAGINA GIU PagDown
		else if (34 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		

			pagina_menu(0);
   	        	
   	        return false;
   	    }
//PAGINA SU PagUp
		else if (33 == keycode)
   	    {
   	        // Firefox and other non IE browsers
   	        if (e.preventDefault)
   	        {
   	            e.preventDefault();
   	            e.stopPropagation();
   	        }
   	        // Internet Explorer
   	        else if (e.keyCode)
   	        {
   	            e.keyCode = 0;
   	            e.returnValue = false;
   	            e.cancelBubble = true;
   	        }		

   	     	pagina_menu(1);
   	     	
   	        return false;
   	    }
//ENTER
		else if (13 == keycode)
   	    {
//    	        // Firefox and other non IE browsers
//    	        if (e.preventDefault)
//    	        {
//    	            e.preventDefault();
//    	            e.stopPropagation();
//    	        }
//    	        // Internet Explorer
//    	        else if (e.keyCode)
//    	        {
//    	            e.keyCode = 0;
//    	            e.returnValue = false;
//    	            e.cancelBubble = true;
//    	        }		

			if(modalita=="modifica")
   	     		$('input:focus').dblclick();
   	     	
   	        return true;
   	    }
 
};

document.onkeydown = fn;

   		</script>  
<script>
//DIMENSIONI E GESTIONE ALERT MODALI
//------------------------------------------------------------------//
   	function Dim_Alert ( sWidth, sHeight )
   	{
		setupPagina = "dialogWidth:" + sWidth + "px";
   		setupPagina += "; dialogHeight:" + sHeight + "px";
   		setupPagina += ";dialogLeft:80px;dialogTop:80px;";

   		return setupPagina;
   	}

function control_ind()
{
if(utente_ID!=0)
{
	if($('#ID_via').val()==1)
	{
		$('#via').attr('alt',"cap");
	}
	else
	{
		$('#via').attr('alt',"via");
	}

	RicercheDaId('indirizzo_generale','0');
}
	
}
   	
   	function RicercheDaId (value, rif)
   	{
   		var valorediritorno = 0;
   		var strDim = Dim_Alert(600, 300);
   		
   		switch(value)
   		{
   			case "utente":

   				strDim = Dim_Alert(600, 300);
   				var stringa = "modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";		
   				valorediritorno = window.showModalDialog(stringa,"", strDim);

   				if(valorediritorno!=null)
   				{
   					top.location.href="dati_soggetto.php?mode=consulta&p="+valorediritorno+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				}
   				
   				break;
   			
   			case "stato":
   				if(modalita=="modifica")
   		    	{
   				strDim = Dim_Alert(600, 300);
   				var stringa = "modali/ricerca_alert_modale.php?richiesta=ricPaese";
   				valorediritorno = window.showModalDialog(stringa, "", strDim);
   				
   				if(rif==0)
   				{
   					if(valorediritorno!=null)
   	   				{
   	   	   				paese_ritorno = valorediritorno.paese;
   	   					$('#paese_nascita').val(paese_ritorno);
   	   					if(paese_ritorno!="Italia")
   	   					{
   	   						$('#comune_nascita').val(null);
   	   						$('#comune_nascita').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
   	   						$('#comune_nascita').attr('readonly',false);
   	   	   					$('#dati_sogg_prov_nasc').val(null);
  	   						$('#dati_sogg_prov_nasc').attr('disabled','disabled');
  	   						$('#CC_nascita').val(valorediritorno.CC);
   	   					}
   	   					else
   	   					{
   	   						$('#comune_nascita').val(null);
	   	   					$('#comune_nascita').attr('disabled',false);
	   	   					$('#comune_nascita').attr('readonly','readonly');
	   	   					$('#comune_nascita').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
	   						$('#dati_sogg_prov_nasc').attr('disabled',false);
   	   					}
   	   				}
   					
   				}
   				else 
   				{
   					if(valorediritorno!=null)
   	   				{
   						paese_ritorno = valorediritorno.paese;
   	   					$('#paese').val(paese_ritorno);
   						
   	   					if(paese_ritorno!="Italia")
   	   					{
   	   						$('#civico').val(null);
							$('#esponente').val(null);
							$('#interno').val(null);
							$('#dettagli').val(null);

							$('#ID_via_cap').val(1);
							$('#ID_via').val(0);
							
   	   	   					$('#scelta_indirizzo_1').hide();
   	   	   					$('#scelta_indirizzo_2').show();
   	   	   					
   	   						$('#CC').val(valorediritorno.CC);
   	   						$('#comune').val(null);
   							$('#comune').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
   							$('#comune').attr('readonly',false);
   							$('#dati_sogg_prov').val(null);
   							$('#dati_sogg_prov').attr('readonly',false);
   							$('#frazione').val(null);
   							$('#cap').val(null);
   							$('#cap').attr('readonly',false);
   							$('#via').val(null);
   							$('#via').attr('ondblclick',"RicercheDaId('via',0);");
   							$('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
   							$('#via').attr('readonly','readonly');
   							
   	   					}
   	   					else
   	   					{
   	   						$('#scelta_indirizzo_2').hide();
	   	   					$('#scelta_indirizzo_1').show();
   	   						$('#comune').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
   	   						$('#comune').val(null);
							$('#comune').attr('readonly','readonly');
							$('#frazione').val(null);
							$('#dati_sogg_prov').val(null);
							$('#dati_sogg_prov').attr('disabled',false);
							$('#cap').attr('readonly','readonly');
							$('#cap').val(null);
							$('#via').attr('ondblclick',"");
							$('#via').val(null);
							$('#via').attr('readonly','readonly');
							$('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
							$('#civico').val(null);
   							$('#esponente').val(null);
   							$('#interno').val(null);
   							$('#dettagli').val(null);
	   	   				}
   	   				}
   				}
   		    	}
   				break;

   			case "ente":
   	   			
   				if(modalita=="modifica")
   		    	{
					strDim = Dim_Alert(600, 300);
   					var stringa = "modali/ricerca_alert_modale.php?richiesta=ricComune";
   					   				
   					if(rif==0)
   					{
   						if($('#paese_nascita').val()=="Italia")
   						{
   							valorediritorno = window.showModalDialog(stringa, "", strDim);
   						
   						
   						if(valorediritorno!=null && valorediritorno!=undefined)
   						{
   							$('#comune_nascita').val(valorediritorno.comune);   						
   							$('#dati_sogg_prov_nasc').val(valorediritorno.prov_sigla);
   							$('#CC_nascita').val(valorediritorno.CC);  						
   						}
   						}
   					}
   					else 
   					{
   						if($('#paese').val()=="Italia")
   						{
   							valorediritorno = window.showModalDialog(stringa, "", strDim);
   						
   						
   						if(valorediritorno!=null && valorediritorno!=undefined)
   						{
   							$('#comune').val(valorediritorno.comune);
   							$('#dati_sogg_prov').val(valorediritorno.prov_sigla);
   							$('#CC').val(valorediritorno.CC);

   							pattern_numeri = /[^0-9]/;
   							cap_control = valorediritorno.cap;
   							
   							if(cap_control.match(pattern_numeri))
   							{
   								cap_control = cap_control.replace('x',0);
   								cap_control = cap_control.replace('x',0);
   								$('#via').val(null);
   								$('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
   								$('#via').attr('readonly','readonly');
   								$('#cap').val(cap_control);
   								$('#cap').attr('readonly','readonly');
   								$('#via').attr('ondblclick',"RicercheDaId('indirizzo_generale',0);");
   								$('#via').attr('alt',"cap");
   							}
   							else
   							{
   								$('#via').val(null);
   								$('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
   								$('#via').attr('readonly','readonly');
   								$('#cap').val(cap_control);
   								$('#cap').attr('readonly',false);
   								$('#via').attr('ondblclick',"RicercheDaId('indirizzo_generale',0);");
   								$('#via').attr('alt',"via");								
   							}

   							$('#civico').val(null);
   							$('#esponente').val(null);
   							$('#interno').val(null);
   							$('#dettagli').val(null);
   						}
   						}
   					}			
   		    	}
   				break;

   			case "indirizzo_generale":

   				if(modalita=="modifica")
   		    	{
   	   			
	   				strDim = Dim_Alert(750, 400); 				
	   				pvia = $('#via').val();
	   				pcomune = $('#comune').val();
	   				pCC = $('#CC').val();
	   				tipoRicInd = $('#via').attr('alt');

	   				var stringa = "modali/ricerca_alert_modale.php?richiesta=indirizzo_generale&via_ric="+pvia+"&pc="+pcomune+"&pCC="+pCC+"&tipoRicInd="+tipoRicInd+"&c=<?php echo $c; ?>";
	   				valorediritorno = window.showModalDialog(stringa, "", strDim);

	   				tipoRicInd = valorediritorno.tipoRic;

	   				if(tipoRicInd=="cap")
	   				{
	   					if(valorediritorno!=null && valorediritorno!=undefined)
		   				{
		   					$('#cap').val(valorediritorno.cap);
		   					$('#via').val(valorediritorno.indirizzo);
		   					$('#ID_via_cap').val(valorediritorno.ID);
		   					$('#ID_via').val(1);		   				
		   				}
	   				}
	   				else if(tipoRicInd=="via")
	   				{
	   					if(valorediritorno!=null && valorediritorno!=undefined)
		   				{
		   					$('#cap').val(valorediritorno.cap);
		   					$('#cap').attr('readonly',false);
		   					$('#via').val(valorediritorno.indirizzo);
		   					$('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco sfondo_giallo');
		   					$('#ID_via').val(valorediritorno.ID);
		   					$('#ID_via_cap').val(1);
		   					$('#civico').val(null);
							$('#esponente').val(null);
							$('#interno').val(null);
							$('#dettagli').val(null);
		   				}
		   			}
	   				else if(valorediritorno=="no_via")
	   				{
	   					$('#ID_via_cap').val(1);
	   					$('#ID_via').val(0);
	   					$('#cap').attr('readonly',false);
	   					$('#via').attr('readonly',false);
	   					$('#via').val(null);
	   					$('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
	   					$('#civico').val(null);
						$('#esponente').val(null);
						$('#interno').val(null);
						$('#dettagli').val(null);
	   					alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
	   				}			   				
				
   		    	}
   				
   	   			break;
   				
   			case "via":

   				if(modalita=="modifica")
   		    	{
   	   		    	
	   	   			if(rif==1)
	   	   			{
	   	   	   			if($('#ID_via_cap').val() == 1 && $('#via').val() != null && $('#via').val()!="")
	   	   	   			{
	   	   	   				ctrl_giallo = $('#via').hasClass('sfondo_giallo');
	
	   	   					if( ctrl_giallo == false )
	   	   					{
	 	   						$('#via').prop('readonly',false).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo'); 	   						
	 	   						alert("Ora e' possibile modificare l'indirizzo. Terminata l'operazione cliccare nuovamente sulla gomma.\n\nSi ricorda che questa funzione serve per correggere errori di battitura e non per inserire un nuovo indirizzo.");
	 	   						$('#via').focus();
	   	   					}
	   	   					else if( ctrl_giallo == true )
	   	   					{
	   	   						$('#via').prop('readonly',true).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
	   	   						alert("Operazione effettuata correttamente");
	   	   						$('#via').focus();
	   	   					}
	   	   	   			}
	   	   			}
	   	   			else
	   	   			{
	   	   	   			
		   				strDim = Dim_Alert(600, 300);
						pCC = $('#CC').val();
						pvia = $('#via').val();
		
		   				var stringa = "modali/ricerca_alert_modale.php?richiesta=ricIndirizzo&pCC="+pCC+"&via_ric="+pvia+"&c=<?php echo $c; ?>";
		   				valorediritorno = window.showModalDialog(stringa, "", strDim);
						
		   				if(valorediritorno!=null && valorediritorno!=undefined)
		   				{
		   					$('#cap').val(valorediritorno.cap);
		   					$('#via').val(valorediritorno.indirizzo);
		   					$('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco sfondo_giallo');
		   					$('#ID_via').val(valorediritorno.ID);
		   					$('#ID_via_cap').val(1);
		   				}
		   				else
		   				{
		   					$('#ID_via_cap').val(1);
		   					$('#ID_via').val(0);
		   					$('#via').attr('readonly',false);
		   					$('#via').val(null);
		   					$('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
		   					alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
		   				}
	
		   				$('#civico').val(null);
						$('#esponente').val(null);
						$('#interno').val(null);
						$('#dettagli').val(null);
	
	   	   			}
   		    	}
   			break;

   			case "cap":
   				if(modalita=="modifica")
   		    	{
	   				strDim = Dim_Alert(750, 400); 				
	   				pvia = $('#via').val();
	   				pcomune = $('#comune').val();
	   				pCC = $('#CC').val();
	   				
	   				var stringa = "modali/ricerca_alert_modale.php?richiesta=ricCap&via_ric="+pvia+"&pc="+pcomune+"&pCC="+pCC;
	   				valorediritorno = window.showModalDialog(stringa, "", strDim);
	   				
	   				if(valorediritorno!=null && valorediritorno!=undefined)
	   				{
	   					$('#cap').val(valorediritorno.cap);
	   					$('#via').val(valorediritorno.indirizzo);
	   					$('#ID_via_cap').val(valorediritorno.ID);
	   					$('#ID_via').val(1);		   				
	   				}
	   				else
	   				{
	   					RicercheDaId('via',0);
	   				}
   		    	}
   		    					
   			break;

   			case "esenzione":
   				if(modalita=="modifica")
   		    	{
   				strDim = Dim_Alert(370, 330);
   				var stringa = "modali/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_esenzione&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				valorediritorno = window.showModalDialog(stringa,"", strDim);

   				if(valorediritorno!=null)
   				{
					$('#esenzione').val(valorediritorno.descrizione);
					$('#ese').val(valorediritorno.ID);
   				}
   		    	}
   	   			break;

   			case "situazione":
   				if(modalita=="modifica")
   		    	{
   				strDim = Dim_Alert(370, 330);
   				var stringa = "modali/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_situazione&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				valorediritorno = window.showModalDialog(stringa,"", strDim);

   				if(valorediritorno!=null)
   				{
					$('#situazione').val(valorediritorno.descrizione);
					$('#sit').val(valorediritorno.ID);
   				}
   		    	}
   	   			break;

   			case "controllo":
   				if(modalita=="modifica")
   		    	{
   				strDim = Dim_Alert(370, 330);
   				var stringa = "modali/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_controllo&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				valorediritorno = window.showModalDialog(stringa,"", strDim);

   				if(valorediritorno!=null)
   				{
					$('#controllo').val(valorediritorno.descrizione);
					$('#con').val(valorediritorno.ID);
   				}
   		    	}
   	   			break;

   			case "raggr":
   				if(modalita=="modifica")
   		    	{
   					strDim = Dim_Alert(370, 330);
   					var stringa = "modali/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_raggr&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   					valorediritorno = window.showModalDialog(stringa,"", strDim);

   					if(valorediritorno!=null)
   					{
						$('#raggr').val(valorediritorno.descrizione);
						$('#rag').val(valorediritorno.ID);
   					}
   		    	}
   	   			break;

   			case "sotto_raggr":
   				if(modalita=="modifica")
   		    	{
   					strDim = Dim_Alert(370, 330);
   					var stringa = "modali/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_sotto_raggr&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   					valorediritorno = window.showModalDialog(stringa,"", strDim);

   					if(valorediritorno!=null)
   					{
						$('#sottoraggr').val(valorediritorno.descrizione);
						$('#sot').val(valorediritorno.ID);
   					}
   		    	}
   	   			break;

   		}
   	}
</script> 
<script>

function focusCampo()
{
	$("#id_cerca").focus();
	
}

</script>

<div class="mainmenu width100">
<ul class="text_left">
	<li class="li_hc"><a href="#" target="_self" >Gestione Ente</a><ul class="ul_ch">
			<li class="li_nc"><a onclick="menuClick('29');" href="#" >Selezione Ente/Anno</a></li>
         	<li class="li_nc"><a onclick="menuClick('13');" href="#" >Creazione ente</a></li>
        	<li class="li_nc"><a onclick="menuClick('14');" href="#" >Creazione anno</a></li>
         	<li class="li_nc"><a onclick="menuClick('15');" href="#" >Cancellazione ente</a></li>
         	<li class="li_nc"><a onclick="menuClick('16');" href="#" >Cancellazione anno</a></li>
    </ul></li>
	<li class="li_hc"><a href="#" target="_self" >Anagrafe</a><ul class="ul_ch">
         <li class="li_nc"><a onclick="menuClick('1');" href="#" >Dati Soggetto</a></li>
         <li class="li_nc"><a onclick="menuClick('2');" href="#" >Annotazioni</a></li>
         <li class="li_nc"><a onclick="menuClick('3');" href="#" >Recapito</a></li>
         <li class="li_nc"><a onclick="menuClick('4');" href="#" >Domicilio</a></li>
         <li class="li_nc"><a onclick="menuClick('5');" href="#" >Dettagli</a></li>
         <li class="li_nc"><a onclick="menuClick('6');" href="#" >Cambia Residenza e Storico</a></li>
    </ul></li>
    <li class="li_hc"><a href="#" target="_self" >Posizioni</a><ul class="ul_ch">
         <li><a href="#" onclick="menuClick('28');" >Impianti</a></li>
	        </ul></li>
    <li class="li_hc"><a href="#" target="_self" >Preavvisi</a><ul class="ul_ch">
         <li><a href="#" onclick="menuClick('21');" >Parametri</a></li>
	        </ul></li>
	<li class="li_hc"><a href="#" target="_self" >Pagamenti</a><ul class="ul_ch">
         <li><a href="#" onclick="menuClick('26');" >Pagamenti</a></li>
	        </ul></li>        
             
    <li class="li_hc"><a href="#" target="_self" >Parametri</a><ul class="ul_ch">
      	 <li><a href="#" target="_self" >Comune</a><ul class="ul_ch">
	    	 <li class="li_nc"><a onclick="menuClick('20');" href="#" >Stemma</a></li>
	         <li class="li_nc"><a onclick="menuClick('17');" href="#" >Dati Ente</a></li>
	         <li class="li_nc"><a onclick="menuClick('18');" href="#" >Gestore</a></li>
	         <li class="li_nc"><a onclick="menuClick('19');" href="#" >Ufficio</a></li>
	     </ul></li>
	    	<li class="li_nc"><a onclick="menuClick('27');" href="#" >Stradario</a></li>
	    	<li class="li_nc"><a onclick="menuClick('8');" href="#" >Personalizzazione</a></li>
	    	<li class="li_nc"><a onclick="menuClick('7');" href="#" >Scadenze</a></li>
			<li class="li_nc"><a onclick="menuClick('9');" href="#" >Riscossione</a></li>
			<li class="li_nc"><a onclick="menuClick('10');" href="#" >Maggiorazioni / Riduzioni</a></li>
			<li class="li_nc"><a onclick="menuClick('22');" href="#" >Aggi / Canoni</a></li>	
			       
    	 	<li><a href="#" target="_self" >Tariffe</a><ul class="ul_ch">
    		<li class="li_nc"><a onclick="menuClick('11');" href="#" >Tariffe</a></li>
    		<li class="li_nc"><a onclick="menuClick('12');" href="#" >Voci tariffarie</a></li>
    		
    	 </ul></li>
    	 	<li><a href="#" target="_self" >Contenzioso</a><ul class="ul_ch">
    	 	<li class="li_nc"><a onclick="menuClick('23');" href="#" >Contenzioso</a></li>
    	 	<li class="li_nc"><a onclick="menuClick('24');" href="#" >Interessi</a></li>
    </ul></li>
    </ul>
    </li>
    <li class="li_hc"><a href="#" target="_self" >Stampe</a><ul class="ul_ch">
         <li><a href="#" onclick="menuClick('26');" >Posizioni</a></li>
         <li><a href="#" onclick="menuClick('26');" >Preavvisi</a></li>
         <li><a href="#" onclick="menuClick('26');" >Bollettini</a></li>
         <li><a href="#" onclick="menuClick('26');" >Pagamenti</a></li>
	        </ul></li>
    
</ul>
</div>